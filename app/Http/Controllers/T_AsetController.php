<?php

namespace App\Http\Controllers;

use App\InputAset;
use Illuminate\Http\Request;
use App\DetailInputAset;
use App\DistribusiAset;
use App\MasterGroupApotek;
use App\MasterApotek;
use App\MasterAset;
use Validator;
use App;
use Datatables;
use DB;
use Auth;
use Seatreserved;

class T_AsetController extends Controller
{
    protected $flag_trx = 2;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('manajemen_aset.index');
    }

    public function list_data_aset(Request $request)
    {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::statement(DB::raw('set @rownum = 0'));
        $data = InputAset::select([DB::raw('@rownum  := @rownum  + 1 AS no'), 'tb_transaksi_aset.*'])
            ->where(function ($query) use ($request) {
                $query->orwhere('tb_transaksi_aset.is_deleted', '=', '0');
                $query->where('tb_transaksi_aset.no_transaksi','LIKE',($request->no_transaksi > 0 ? $request->no_transaksi : '%'.$request->no_transaksi.'%'));
                $query->where('tb_transaksi_aset.keterangan','LIKE',($request->keterangan > 0 ? $request->keterangan : '%'.$request->keterangan.'%'));

                if($request->tgl_awal != "") {
                    $tgl_awal       = date('Y-m-d H:i:s',strtotime($request->tgl_awal));
                    $query->whereDate('tb_transaksi_aset.tgl_transaksi','>=', $tgl_awal);
                }

                if($request->tgl_akhir != "") {
                    $tgl_akhir      = date('Y-m-d H:i:s',strtotime($request->tgl_akhir));
                    $query->whereDate('tb_transaksi_aset.tgl_transaksi','<=', $tgl_akhir);
                }
            });

        $datatables = Datatables::of($data);
        return $datatables
            ->filter(function ($query) use ($request, $order_column, $order_dir) {
                $query->where(function ($query) use ($request) {
                    $query->where('no_transaksi', 'LIKE', '%' . $request->get('search')['value'] . '%');
                });
            })
            ->addcolumn('action', function ($data) {
                $btn = '<div class="btn-group">';
                $btn .= '<a href="'.url('/manajemen_aset/'.$data->id.'/edit').'" title="Edit Data" class="btn btn-info btn-sm"><span data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i> Edit</span></a>';
                $btn .= '<span class="btn btn-danger btn-sm" onClick="delete_data('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i> Hapus</span>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->addIndexColumn()
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $apoteks = MasterApotek::whereNotIn('id', [$apotek->id])->where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $tanggal = date('Y-m-d');
        $var = 1;
        $aset = new InputAset();
        $detail_asets = new DetailInputAset();

        return view('manajemen_aset.create')->with(compact('aset', 'detail_asets', 'apotek', 'inisial', 'apoteks', 'var'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        DB::beginTransaction(); 
        try{
            $aset = new InputAset;
            $aset->fill($request->except('_token'));

            $detail_asets = $request->detail_aset;
            $jum = count($detail_asets);
            $tanggal = date('Y-m-d');
            $apotek = MasterApotek::find(session('id_apotek_active'));
            $inisial = strtolower($apotek->nama_singkat);
            $validator = $aset->validate();
            if($validator->fails() AND $jum > 0){
                $var = 1;
                return view('manajemen_aset.create')->with(compact('aset', 'apoteks', 'detail_asets', 'var', 'apotek', 'inisial'))->withErrors($validator);
            }else{
                $aset->save_from_array($detail_asets,1);

                // ---- save jurnal ---- //
                $statusjurnalumum = 0;
                $jurnal_umum = new JurnalUmum;
                $jurnal_umum->id_apotek = $aset->id_apotek;
                $jurnal_umum->flag_trx = $this->flag_trx;
                $jurnal_umum->kode_referensi = $aset->id;
                $jurnal_umum->no_transaksi = $aset->no_transaksi;
                $jurnal_umum->tgl_transaksi = $aset->tgl_transaksi;
                $jurnal_umum->tag = 'aset';
                $jurnal_umum->memo = $aset->keterangan;
                $jurnal_umum->created_at = $aset->created_at;
                $jurnal_umum->created_by = $aset->created_by;
                if($jurnal_umum->save()){ 
                    $statusjurnalumum = 1;
                }

                // save detail
                $subtotal = 0;
                $details = $aset->detail_aset;
                foreach ($details as $key => $detail) {
                    $statusdetiljurnal[$detail->id] = 0;
                    if($statusjurnalumum){
                        // insert detil jurnal 
                        $detiljurnal = new JurnalUmumDetail;
                        $detiljurnal->id_jurnal = $jurnal_umum->id; 
                        $detiljurnal->id_kode_akun = $detail->id_kode_akun; 
                        $detiljurnal->flag_trx = $this->flag_trx; 
                        $detiljurnal->kode_referensi = $detail->id; 
                        $detiljurnal->deskripsi = $detail->merk; 
                        $detiljurnal->kredit = $detail->total_nilai;
                        $detiljurnal->created_by = Auth::user()->id;
                        $detiljurnal->created_at = Date("Y-m-d H:i:s");
                        if($detiljurnal->save()){ $statusdetiljurnal[$detail->id] = 1; };
                    }
                    $subtotal += $detail->total_nilai;
                }
                $ppn_potong = 0;
                $total_kredit = $subtotal+$ppn_potong;

                if($statusjurnalumum){
                    // save total ke jurnal //
                    $jurnal_umum->total_kredit = $total_kredit;
                    $jurnal_umum->total_debit = $total_kredit;
                    $jurnal_umum->save();
                }

                DB::commit();
                session()->flash('success', 'Sukses menyimpan data!');
                return redirect('manajemen_aset');
            }
        }catch(\Exception $e){
            DB::rollback();
            session()->flash('error', 'Error!');
            return redirect('manajemen_aset');
        }
    }

    public function show($id) {
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\InputAset  $inputAset
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $apoteks = MasterApotek::whereNotIn('id', [$apotek->id])->where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $tanggal = date('Y-m-d');
        $var = 0;
        $aset = InputAset::find($id);
        $detail_asets = $aset->detail_transfer_outlet;

        return view('manajemen_aset.edit')->with(compact('aset', 'detail_asets', 'apotek', 'inisial', 'apoteks', 'var'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\InputAset  $inputAset
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction(); 
        try{
            $aset = InputAset::find($id);
            $aset->fill($request->except('_token'));

            $detail_asets = $request->detail_aset;
            $jum = count($detail_asets);
            $tanggal = date('Y-m-d');
            $apotek = MasterApotek::find(session('id_apotek_active'));
            $inisial = strtolower($apotek->nama_singkat);
            $validator = $aset->validate();
            if($validator->fails() AND $jum > 0){
                $var = 0;
                return view('manajemen_aset.edit')->with(compact('aset', 'apoteks', 'detail_asets', 'var', 'apotek', 'inisial'))->withErrors($validator);
            }else{
                $aset->save_from_array($detail_asets, 2);
                
                // check jurnal umum //
                $check_jurnal_umum = JurnalUmum::where("flag_trx",$this->flag_trx)->where("kode_referensi",$aset->id)
                                    ->whereNull('deleted_by')->first();
                if(!empty($check_jurnal_umum)){
                    $jurnal_umum = JurnalUmum::find($check_jurnal_umum->id);    
                    $jurnal_umum->updated_by = Auth::user()->id;
                    $jurnal_umum->updated_at = Date("Y-m-d H:i:s");
                } else {
                    $jurnal_umum = new JurnalUmum; 
                    $jurnal_umum->created_by = Auth::user()->id;
                    $jurnal_umum->created_at = Date("Y-m-d H:i:s");
                }

                $statusjurnalumum = 0;
                $jurnal_umum->id_apotek = $aset->id_apotek;
                $jurnal_umum->flag_trx = $this->flag_trx;
                $jurnal_umum->kode_referensi = $aset->id;
                $jurnal_umum->no_transaksi = $aset->no_transaksi;
                $jurnal_umum->tgl_transaksi = $aset->tgl_transaksi;
                $jurnal_umum->tag = 'aset';
                $jurnal_umum->memo = $aset->keterangan;
                if($jurnal_umum->save()){ 
                    $statusjurnalumum = 1;
                }

                // save detail
                $subtotal = 0;
                $details = $aset->detail_aset;
                foreach ($details as $key => $detail) {
                    $statusdetiljurnal[$detail->id] = 0;
                    if($statusjurnalumum){
                        // cek detil jurnal sudah ada atau tidak //
                        $check_detil_jurnal = JurnalUmumDetail::where('flag_trx',$this->flag_trx)
                                            ->where("kode_referensi",$detail->id)
                                            ->whereNull('deleted_by')
                                            ->first();

                        if(empty($check_detil_jurnal)){
                            $detiljurnal = new JurnalUmumDetail;
                            $detiljurnal->created_by = Auth::user()->id;
                            $detiljurnal->created_at = Date("Y-m-d H:i:s");
                        } else {
                            $detiljurnal = JurnalUmumDetail::find($check_detil_jurnal->id);
                            if(empty($detiljurnal)){
                                $detiljurnal = new JurnalUmumDetail;
                                $detiljurnal->created_by = Auth::user()->id;
                                $detiljurnal->created_at = Date("Y-m-d H:i:s");
                            } else {
                                $detiljurnal->updated_by = Auth::user()->id;
                                $detiljurnal->updated_at = Date("Y-m-d H:i:s");
                            }
                        }
                        
                        $detiljurnal->id_jurnal = $jurnal_umum->id; 
                        $detiljurnal->id_kode_akun = $detail->id_kode_akun; 
                        $detiljurnal->flag_trx = $this->flag_trx; 
                        $detiljurnal->kode_referensi = $detail->id; 
                        $detiljurnal->deskripsi = $detail->merk; 
                        $detiljurnal->kredit = $detail->total_nilai;
                        if($detiljurnal->save()){ $statusdetiljurnal[$detail->id] = 1; };
                    }
                    $subtotal += $detail->total_nilai;
                }
                $ppn_potong = 0;
                $total_kredit = $subtotal+$ppn_potong;

                if($statusjurnalumum){
                    // save total ke jurnal //
                    $jurnal_umum->total_kredit = $total_kredit;
                    $jurnal_umum->total_debit = $total_kredit;
                    $jurnal_umum->save();
                }

                DB::commit();
                session()->flash('success', 'Sukses menyimpan data!');
                return redirect('manajemen_aset');
            }
        }catch(\Exception $e){
            DB::rollback();
            session()->flash('error', 'Error!');
            return redirect('manajemen_aset');
        }
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\InputAset  $inputAset
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $success = true;
        DB::beginTransaction();
        try{
            $aset = InputAset::find($id);
            $aset->is_deleted = 1;
            $aset->deleted_at = date('Y-m-d H:i:s');
            $aset->deleted_by = Auth::user()->id;

            $detail_asets = $aset->detail_aset;
            $i = 0;
            foreach ($detail_asets as $key => $detail) {
                $detail = DetailInputAset::find($detail->id);
                $detail->is_deleted = 1;
                $detail->deleted_at= date('Y-m-d H:i:s');
                $detail->deleted_by = Auth::user()->id;
                $detail->save();
                $i++;
            }

            if($i > 0){
                if($aset->save()) {
                    $jurnal_umum = JurnalUmum::where("flag_trx",$this->flag_trx)->where("kode_referensi",$aset->id)
                                ->update([
                                    "deleted_at" => date('Y-m-d H:i:s'),
                                    "deleted_by" => Auth::user()->id
                                ]);
                    DB::commit();
                }
            }
        }catch(\Exception $e){
            DB::rollback();
            $success = false;
        }

        if($success){
            echo 1;
        } else {
            echo 0;
        }
    }

    public function cari_aset(Request $request) {
        $aset = MasterAset::where('kode_aset', $request->kode_aset)->first();
        $cek_ = 0;
        
        if(!empty($aset)) {
            $cek_ = 1;
        }

        $data = array('aset'=> $aset, 'is_data' => $cek_);
        return json_encode($data);
    }

    public function cari_aset_dialog(Request $request) {
        $aset = MasterAset::find($request->id_aset);

        return json_encode($aset);
    }

    public function open_data_aset(Request $request) {
        $kode_aset = $request->kode_aset;
        return view('manajemen_aset._dialog_open_aset')->with(compact('kode_aset'));
    }

    public function get_data_aset(Request $request)
    {
        $kode_aset = $request->kode_aset;
        DB::statement(DB::raw('set @rownum = 0'));
        $data = MasterAset::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_m_aset.*'])
        ->where(function($query) use($request){
            $query->where('tb_m_aset.is_deleted','=','0');
        });
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request, $kode_aset){
            $query->where(function($query) use($request, $kode_aset){
                $query->orwhere('nama','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('kode_aset','LIKE','%'.$request->get('search')['value'].'%');
            });
        })   
        ->editcolumn('id_jenis_aset', function($data){
            $jenis_aset = "";
            if($data->id_jenis_aset == 1) {
                $jenis_aset = "Aset Tetap";
            } else {
                $jenis_aset = "Aset Tak Berwujud";
            }
            return $jenis_aset; 
        }) 
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .= '<span class="btn btn-primary" onClick="add_item_dialog('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Tambah Item"><i class="fa fa-plus"></i></span>';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['id_jenis_aset', 'action'])
        ->addIndexColumn()
        ->make(true);  
    }


    public function edit_detail(Request $request){
        $id = $request->id;
        $no = $request->no;
        $detail = DetailInputAset::find($id);
        return view('manajemen_aset._form_edit_detail')->with(compact('detail', 'no'));
    }

    public function hapus_detail($id) {
        $success = true;
        DB::beginTransaction();
        try{
            $detail = DetailInputAset::find($id);
            $detail->is_deleted = 1;
            $detail->deleted_at= date('Y-m-d H:i:s');
            $detail->deleted_by = Auth::user()->id;
            $detail->save();
            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
            $success = false;
        }

        if($success){
            echo 1;
        } else {
            echo 0;
        }
    }
}
