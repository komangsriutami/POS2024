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

class InputAsetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('input_aset.index');
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
                $btn .= '<a href="'.url('/input_aset/'.$data->id.'/edit').'" title="Edit Data" class="btn btn-info btn-sm"><span data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i> Edit</span></a>';
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

        return view('input_aset.create')->with(compact('aset', 'detail_asets', 'apotek', 'inisial', 'apoteks', 'var'));
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
                return view('input_aset.create')->with(compact('aset', 'apoteks', 'detail_asets', 'var', 'apotek', 'inisial'))->withErrors($validator);
            }else{
                $aset->save_from_array($detail_asets,1);
                DB::commit();
                session()->flash('success', 'Sukses menyimpan data!');
                return redirect('input_aset');
            }
        }catch(\Exception $e){
            DB::rollback();
            session()->flash('error', 'Error!');
            return redirect('input_aset');
        }
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

        return view('input_aset.edit')->with(compact('aset', 'detail_asets', 'apotek', 'inisial', 'apoteks', 'var'));
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
                return view('input_aset.edit')->with(compact('aset', 'apoteks', 'detail_asets', 'var', 'apotek', 'inisial'))->withErrors($validator);
            }else{
                $aset->save_from_array($detail_asets, 2);
                DB::commit();
                session()->flash('success', 'Sukses menyimpan data!');
                return redirect('input_aset');
            }
        }catch(\Exception $e){
            DB::rollback();
            session()->flash('error', 'Error!');
            return redirect('input_aset');
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
            foreach ($detail_asets as $key => $detail) {
                $detail = DetailInputAset::find($detail->id);
                $detail->is_deleted = 1;
                $detail->deleted_at= date('Y-m-d H:i:s');
                $detail->deleted_by = Auth::user()->id;
                $detail->save();
            }
            $aset->save();
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
        return view('input_aset._dialog_open_aset')->with(compact('kode_aset'));
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
        return view('input_aset._form_edit_detail')->with(compact('detail', 'no'));
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
