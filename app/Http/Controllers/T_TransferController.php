<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterApotek;
use App\MasterObat;
use App\DefectaOutlet;
use App\MasterSatuan;
use App\MasterSuplier;
use App\MasterStatusOrder;
use App\RbacMenu;
use App\TransaksiTransfer;
use App\TransaksiTransferDetail;
use App\User;
use Spipu\Html2Pdf\Html2Pdf;

use App;
use Datatables;
use DB;
use Auth;
use Hash;
use Crypt;
class T_TransferController extends Controller
{
    public function index() {
        $apoteks = MasterApotek::where('is_deleted', 0)->pluck('nama_panjang', 'id');
        $apoteks->prepend('-- Pilih Apotek --','');

        $id_apotek_transfer = DefectaOutlet::select(['id_apotek_transfer'])->where('id_process', 0)->where('id_status', 2)->get();

        $apotek_transfers = MasterApotek::whereIn('id', $id_apotek_transfer)->where('is_deleted', 0)->pluck('nama_panjang', 'id');
        $apotek_transfers->prepend('-- Pilih Apotek Tujuan --','');

        $cek2_ = session('apotek_transfer_aktif');
        if($cek2_ == null) {
            session(['apotek_transfer_aktif'=> session('id_apotek_active')]);
        }

        $cek_ = session('apotektrans_transfer_aktif');
        if($cek_ == null) {
            session(['apotektrans_transfer_aktif'=> '']);
        }

        $cek3_ = session('status_transfer_aktif');
        if($cek3_ == null) {
            session(['status_transfer_aktif'=> 0]);
        }

        $apotek_transfer_aktif = session('apotek_transfer_aktif');
        $apotektrans_transfer_aktif = session('apotektrans_transfer_aktif');
        $status_status_aktif = session('status_status_aktif');
        return view('transfer.index')->with(compact('apoteks', 'apotek_transfers', 'apotek_transfer_aktif', 'apotektrans_transfer_aktif', 'status_status_aktif'));
    }

    public function create() {
        $transfer = new TransaksiTransfer;
        $apotek_transfers = MasterApotek::whereNotIn('id', [session('id_apotek_active')])->where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $apoteks = MasterApotek::whereIn('id', [session('id_apotek_active')])->where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $tanggal = date('Y-m-d');
        $var = 1;

        $satuans      = MasterSatuan::where('is_deleted', 0)->whereIn('id', [1,2,3,4,5])->pluck('satuan', 'id');
        $satuans->prepend('-- Pilih Satuan --','');

        $detail_transfers = collect();

        $defectas = collect();

        return view('transfer.create')->with(compact('transfer', 'apotek_transfers','apoteks', 'tanggal', 'var', 'satuans', 'detail_transfers', 'defectas'));
    }

    public function store(Request $request) {
        $transfer = new TransaksiTransfer;
        $transfer->fill($request->except('_token'));
        $transfer->id_apotek_transfer = $request->id_apotek_transfer;
        $transfer->id_apotek = $request->id_apotek;
        $detail_transfers = $request->detail_transfer;
        $validator = $transfer->validate();

        if($validator->fails()){
            session()->flash('error', 'Gagal menyimpan data transfer!');
            return redirect('transfer/data_transfer`');
        }else{
            $transfer->save_from_array($detail_transfers,1);
            session()->flash('success', 'Sukses menyimpan data transfer!');
            return redirect('transfer/data_transfer');
        }
    }

    public function show($id) {

    }

    public function edit($id) {
        $transfer = TransaksiTransfer::find($id);
        $apotek_transfers = MasterApotek::whereIn('id', [$transfer->id_apotek_transfer])->where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $apoteks = MasterApotek::whereIn('id', [$transfer->id_apotek])->where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $tanggal = date('Y-m-d');
        $var = 2;

        $satuans      = MasterSatuan::where('is_deleted', 0)->whereIn('id', [1,2,3,4,5])->pluck('satuan', 'id');
        $satuans->prepend('-- Pilih Satuan --','');

        $detail_transfers = TransaksiTransferDetail::where('id_nota', $transfer->id)->where('is_deleted', 0)->get();
        $defectas = collect();

        return view('transfer.edit')->with(compact('transfer', 'apotek_transfers','apoteks', 'tanggal', 'var', 'satuans', 'detail_transfers', 'defectas'));
    }

    public function update(Request $request, $id) {
        $transfer = TransaksiTransfer::find($id);
        $transfer->fill($request->except('_token'));
        $detail_transfers = $request->detail_transfer;
        $validator = $transfer->validate();
        if($validator->fails()){
            session()->flash('error', 'Gagal menyimpan data transfer!');
            return redirect('transfer/data_transfer');
        }else{
            $transfer->save_from_array($detail_transfers,2);
            session()->flash('success', 'Sukses menyimpan data transfer!');
            return redirect('transfer/data_transfer');
        }
    }

    public function destroy($id) {
        $tf = TransaksiTransfer::find($id);
        $tf->is_deleted = 1;
        $tf->deleted_at = date('Y-m-d H:i:s');
        $tf->deleted_by = Auth::user()->id;

        $detail_tfs = $tf->detail_transfer;

        foreach ($detail_tfs as $key => $val) {
            $val->is_deleted = 1;
            $val->deleted_at = date('Y-m-d H:i:s');
            $val->deleted_by = Auth::user()->id;
            $val->save();

            $defecta = DefectaOutlet::find($val->id_defecta);
            $defecta->id_process = 0;
            $defecta->updated_at = date('Y-m-d H:i:s');
            $defecta->updated_by = Auth::id();
            $defecta->save();
        }

        if($tf->save()){
            echo 1;
        }else{
            echo 0;
        }
    }

    public function set_apotek_transfer_aktif(Request $request) {
        session(['apotek_transfer_aktif'=> $request->id_apotek]);
        echo $request->id_apotek;
    }

    public function set_apotektrans_transfer_aktif(Request $request) {
        session(['apotektrans_transfer_aktif'=> $request->id_apotek_transfer]);
        echo $request->id_apotek_transfer;
    }

    public function set_status_transfer_aktif(Request $request) {
        session(['status_transfer_aktif'=> $request->id_status]);
        echo $request->id_status;
    }

    public function list_transfer(Request $request)
    {
        $id_apotek = session('apotek_transfer_aktif');
        $id_apotek_transfer = session('apotektrans_transfer_aktif');
        $id_process = session('status_transfer_aktif');

        DB::statement(DB::raw('set @rownum = 0'));
        $data = DefectaOutlet::select([
                DB::raw('@rownum  := @rownum  + 1 AS no'),
                'tb_defecta_outlet.*',
                'b.nama',
                'b.barcode',
                'c.nama_singkat',
                'd.nama_singkat as apotek_transfer'
        ])
        ->leftjoin('tb_m_obat as b', 'b.id', '=', 'tb_defecta_outlet.id_obat')
        ->leftJoin('tb_m_apotek as c', 'c.id', '=', 'tb_defecta_outlet.id_apotek')
        ->leftJoin('tb_m_apotek as d', 'd.id', '=', 'tb_defecta_outlet.id_apotek_transfer')
        ->where(function($query) use($request, $id_apotek, $id_apotek_transfer, $id_process){
            $query->where('tb_defecta_outlet.is_deleted','=','0');
            $query->where('tb_defecta_outlet.id_status','=', 2);
            if($id_apotek != '') {
                $query->where('tb_defecta_outlet.id_apotek','=', $id_apotek);
            }
            if($id_apotek_transfer != '') {
                $query->where('tb_defecta_outlet.id_apotek_transfer','=', $id_apotek_transfer);
            }

            $query->where('tb_defecta_outlet.id_process', $id_process);
        });

        $btn_set_transfer = ''; // 0 = belum ada proses, 1 = proses, 2 = complete

        if ($id_process=='0') {
            $btn_set_transfer .= '
                <button type="submit" class="btn btn-info w-md m-b-5 pull-right animated fadeInLeft" onclick="set_nota_transfer()"><i class="fa fa-fw fa-plus"></i> Nota Transfer</a>';
        } else if ($id_process=='1') {
            $btn_set_transfer .= '
                <a class="btn btn-secondary w-md m-b-5 pull-right animated fadeInLeft text-white" style="text-decoration: none;" href="'.url('/transfer/data_transfer').'"><i class="fa fa-fw fa-list"></i> List Data Transfer</a>';
        } else if ($id_process=='2') {
            $btn_set_transfer .= '
                <a class="btn btn-secondary w-md m-b-5 pull-right animated fadeInLeft text-white" style="text-decoration: none;" href="'.url('/transfer/data_transfer').'"><i class="fa fa-fw fa-list"></i> List Data Transfer</a>';
        } 

        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request){
            $query->where(function($query) use($request){
                $query->orwhere('b.nama','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('b.barcode','LIKE','%'.$request->get('search')['value'].'%');
            });
        })   
        ->addColumn('checkList', function ($data) {
            return '<input type="checkbox" name="check_list" data-id="'.$data->id.'" data-id_apotek="'.$data->id_apotek.'" data-id_apotek_transfer="'.$data->id_apotek_transfer.'" value="'.$data->id.'"/>';
        })
        ->editcolumn('total_stok', function($data) use($request){
            return $data->total_stok;
        })
        ->editcolumn('total_buffer', function($data) use($request){
            return $data->total_buffer;
        })
        ->editcolumn('forcasting', function($data) {
            return $data->forcasting;
        })
        ->editcolumn('apotek', function($data) {
            if($data->id_process == 0) {
                $status = '<small style="font-size:8pt;" class="badge bg-secondary"><i class="fa fa-question"></i></small>';
            } else if($data->id_process == 1) {
                $status = '<small style="font-size:8pt;" class="badge bg-info">Proses</small>';
            } else {
                $status = '<small style="font-size:8pt;" class="badge bg-primary">Complete</small>';
            }
            return $data->nama_singkat.'<br>'.$status;
    
        })
        ->editcolumn('id_apotek_transfer', function($data) {
            return $data->apotek_transfer;
        })
        ->addcolumn('action', function($data) {
            $btn = '';
            if($data->id_process == 0) {
                $btn .= '<span class="btn btn-danger btn-sm" onClick="delete_item('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-trash-alt"></i></span>';
            }

            return $btn;
        })
        ->rawColumns(['checkList', 'total_stok', 'total_buffer', 'forcasting', 'id_apotek_transfer', 'apotek', 'action'])
        ->addIndexColumn()
        ->with([
                'btn_set_transfer' => $btn_set_transfer,
            ])
        ->make(true);  
    }

    public function set_nota_transfer(Request $request){
        $id_apotek = explode(",", $request->input('id_apotek'));
        $id_apotek_transfer = explode(",", $request->input('id_apotek_transfer'));
        $id_defecta = explode(",", $request->input('id_defecta'));

        $apotek_transfers = MasterApotek::whereIn('id', [$request->input('id_apotek_transfer')])->where('is_deleted', 0)->pluck('nama_panjang', 'id');
        $jum_ = count($apotek_transfers);
        if($jum_ > 1) {
            session()->flash('error', 'Data yang dipilih terdiri dari '.$jum_.' apotek tujuan, pastikan data yang dipilih dari apotek tujuan yang sama!');
            return redirect('transfer');
        }

        $defectas = DefectaOutlet::select([
                DB::raw('@rownum  := @rownum  + 1 AS no'),
                'tb_defecta_outlet.*',
                'b.nama',
                'b.barcode',
                'c.nama_singkat',
                'd.nama as status',
                'd.icon',
                'd.class'
        ])
        ->leftjoin('tb_m_obat as b', 'b.id', '=', 'tb_defecta_outlet.id_obat')
        ->leftJoin('tb_m_apotek as c', 'c.id', '=', 'tb_defecta_outlet.id_apotek')
        ->leftjoin('tb_m_status_order as d', 'd.id', '=', 'tb_defecta_outlet.id_status')
        ->where(function($query) use($request, $id_defecta){
            $query->where('tb_defecta_outlet.is_deleted','=','0');
            $query->whereIn('tb_defecta_outlet.id', $id_defecta);
        })->get();

        $apoteks = MasterApotek::whereIn('id', $id_apotek)->where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $transfer = new TransaksiTransfer;
        $detail_transfers = new TransaksiTransferDetail;
        $tanggal = date('Y-m-d');
        $var = 1;

        $satuans      = MasterSatuan::where('is_deleted', 0)->whereIn('id', [1,2,3,4,5])->pluck('satuan', 'id');
        $satuans->prepend('-- Pilih Satuan --','');

        return view('transfer.create')->with(compact('defectas', 'apotek_transfers', 'transfer', 'detail_transfers', 'tanggal', 'var', 'apoteks', 'satuans'));
    }

    public function cari_obat(Request $request) {
        $obat = MasterObat::where('barcode', $request->barcode)->first();

        $cek_ = 0;
        
        if(!empty($obat)) {
            $cek_ = 1;
        } 

        $data = array('is_data' => $cek_, 'obat'=> $obat);
        return json_encode($data);
    }

    public function open_data_obat(Request $request) {
        $id_apotek = $request->id_apotek;
        return view('order._dialog_open_obat')->with(compact('id_apotek'));
    }

    public function list_data_obat(Request $request)
    {
        $apotek = MasterApotek::find($request->id_apotek);
        $inisial = strtolower($apotek->nama_singkat);

        DB::statement(DB::raw('set @rownum = 0'));
        $data = DB::table('tb_m_stok_harga_'.$inisial.' as a')
        ->select([
                DB::raw('@rownum  := @rownum  + 1 AS no'),
                'a.*',
                'b.nama',
                'b.barcode',
        ])
        ->leftjoin('tb_m_obat as b', 'b.id', '=', 'a.id_obat')
        ->where(function($query) use($request){
            $query->where('b.is_deleted','=','0');
        });
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request){
            $query->where(function($query) use($request){
                $query->orwhere('b.nama','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('b.barcode','LIKE','%'.$request->get('search')['value'].'%');
            });
        })   
        ->editcolumn('stok_akhir', function($data){
            return $data->stok_akhir; 
        }) 
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .= '<span class="btn btn-primary" onClick="add_item_dialog('.$data->id_obat.')" data-toggle="tooltip" data-placement="top" title="Tambah Item"><i class="fa fa-plus"></i></span>';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['stok_akhir', 'action'])
        ->addIndexColumn()
        ->make(true);  
    }

    public function cari_obat_dialog(Request $request) {
        $obat = MasterObat::find($request->id_obat);

        return json_encode($obat);
    }

    public function edit_detail(Request $request){
        $id = $request->id;
        $no = $request->no;
        $defecta = DefectaOutlet::select(['tb_defecta_outlet.*', 'a.nama'])
                        ->leftjoin('tb_m_obat as a', 'a.id', 'tb_defecta_outlet.id_obat')
                        ->where('tb_defecta_outlet.id', $id)
                        ->first();
        if(is_null($defecta->jumlah_penjualan)) {
            $defecta->jumlah_penjualan = 0;
            $defecta->margin = 0;
        }
        $apotek = MasterApotek::find($defecta->id_apotek);
        return view('transfer._form_edit_detail')->with(compact('defecta', 'no', 'apotek'));
    }

    public function update_defecta(Request $request, $id) {
        $defecta = DefectaOutlet::find($id);
        $defecta->jumlah_order = $request->jumlah_order;
        $defecta->komentar = $request->komentar;

        if($defecta->save()){

            return response()->json(array(
                'submit' => 1,
                'success' => 'Kirim data berhasil dilakukan'
            ));
        }
        else{
            return response()->json(array(
                'submit' => 0,
                'error' => 'Kirim data gagal dilakukan'
            ));
        }
    }

    public function data_transfer() {
        $apoteks = MasterApotek::where('is_deleted', 0)->pluck('nama_panjang', 'id');
        $apoteks->prepend('-- Pilih Apotek --','');

        $apoteks_tujuans = MasterApotek::where('is_deleted', 0)->pluck('nama_panjang', 'id');
        $apoteks_tujuans->prepend('-- Pilih Apotek Tujuan --','');

        return view('transfer.data_transfer')->with(compact('apoteks', 'apoteks_tujuans'));
    }

    public function list_data_transfer(Request $request) {
        DB::statement(DB::raw('set @rownum = 0'));
        $data = TransaksiTransfer::select([
                DB::raw('@rownum  := @rownum  + 1 AS no'),
                'tb_nota_transfer.*',
                'a.nama_panjang as apotek',
                'b.nama_panjang as apotek_tujuan'
        ])
        ->leftJoin('tb_m_apotek as a', 'a.id', '=', 'tb_nota_transfer.id_apotek')
        ->leftJoin('tb_m_apotek as b', 'b.id', '=', 'tb_nota_transfer.id_apotek_transfer')
        ->where(function($query) use($request){
            $query->where('tb_nota_transfer.is_deleted','=','0');
            if($request->notif != true){
                $query->where('tb_nota_transfer.id_apotek','=', session('id_apotek_active'));
                if($request->id_apotek_transfer != '') {
                    $query->where('tb_nota_transfer.id_apotek_transfer','=', $request->id_apotek_transfer);
                }
            }
        });

        if($request->notif == true){
            $notified_menu = RbacMenu::select(
                'rbac_menu.id',
                'rbac_menu.nama_panjang'
            )
            ->whereIn('rbac_menu.id', array(62, 152));

            return (object) [
                'data' => $data->get(),
                'notified_menu' => $notified_menu->get(),
                'id_apotek' => session('id_apotek_active')
            ];            
        };

        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request){
            $query->where(function($query) use($request){
                $query->orwhere('a.nama_singkat','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('b.nama_singkat','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->editColumn('id_apotek', function ($data) {
            return $data->apotek;
        })
        ->editColumn('id_apotek_transfer', function ($data) {
            return $data->apotek_tujuan;
        })
        ->editColumn('is_status', function ($data) {
            $str = '<span class="label label-danger" data-toggle="tooltip" data-placement="top" title="Belum Dikonfirmasi" style="font-size:8pt;color:#e91e63;">Belum Dikonfirmasi</span>';
            if($data->is_status == 1) {
                $jumlah_all = count($data->detail_transfer);
                $jumlah_sudah = count($data->detail_transfer_sudah);
                $str = '<span class="label label-success" data-toggle="tooltip" data-placement="top" title="Sudah Dikonfirmasi" style="font-size:8pt;color:#009688;"></i> Sudah Dikonfirmasi</span>';
                $str .= '<br> | <span class="label label-warning" data-toggle="tooltip" data-placement="top" title="Sudah Dikonfirmasi" style="font-size:8pt;">'.$jumlah_sudah.'/'.$jumlah_all.' item sudah terkonfirmasi</span>';
            } else {
                $jumlah_all = count($data->detail_transfer);
                $jumlah_sudah = count($data->detail_transfer_sudah);

                if($jumlah_sudah > 0) {
                    $str = '<span class="label label-warning" data-toggle="tooltip" data-placement="top" title="Belum Dikonfirmasi" style="font-size:8pt;color:#FBC02D;">Belum Dikonfirmasi Keseluruhan</span>';
                    $str .= '<br> | <span class="label label-warning" data-toggle="tooltip" data-placement="top" title="Sudah Dikonfirmasi" style="font-size:8pt;">'.$jumlah_sudah.'/'.$jumlah_all.' item sudah terkonfirmasi</span>';
                }
            }
            return $str;
        })
        ->addColumn('checkList', function ($data) {
            return '<input type="checkbox" name="check_list" data-id="'.$data->id.'" value="'.$data->id.'"/>';
        })
        ->editcolumn('is_sign', function($data){
            if($data->is_sign == 0) {
                return '<span class="label label-danger" data-toggle="tooltip" data-placement="top" title="Nota belum dicek atau dittd" style="font-size:8pt;color:#e91e63;">Belum diTTD</span>';
            } else {
                return '<span class="label label-success" data-toggle="tooltip" data-placement="top" title="Nota belum dicek atau dittd" style="font-size:8pt;color:#009688;"></i> TTD by <span class="text-warning">'.$data->getSign->nama.'</span></span>';
            }
        })
        ->addcolumn('action', function($data) {
            $id = encrypt($data->id);
            $btn = '<div class="btn-group">';
            if($data->is_sign == 0) {
                $btn .= '<a href="'.url('/transfer/'.$data->id.'/edit').'" title="Edit Data" class="btn btn-primary btn-sm"><span data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i> Edit</span></a>';
                $btn .= '<span class="btn btn-danger btn-sm" onClick="delete_transfer('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-trash-alt"></i> Hapus</span>';

                if(session('id_role_active') == 4 OR session('id_role_active') == 1) {
                    $btn .= '<span class="btn btn-warning btn-sm" onClick="sign('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Tandai nota ini sudah dicek dan ttd"><i class="fa fa-signature"></i> Sign</span>';
                }
            }

            if($data->is_sign == 1) {
                $btn .= '<a href="'.url('/transfer/export/'.$id).'" title="Cetak PDF" class="btn btn-secondary btn-sm"><span data-toggle="tooltip" data-placement="top" title="Cetak PDF"><i class="fa fa-file-pdf"></i> Cetak</span></a>';
            }
            $btn .='</div>';
            return $btn;
        })   
        ->rawColumns(['checkList', 'DT_RowIndex', 'id_apotek', 'action', 'id_apotek_transfer', 'is_status', 'is_sign'])
        ->addIndexColumn()
        ->make(true);  
    }


    public function konfirmasi() {
        $apoteks = MasterApotek::where('is_deleted', 0)->pluck('nama_panjang', 'id');
        $apoteks->prepend('-- Pilih Apotek --','');

        $apoteks_tujuans = MasterApotek::whereNotIn('id', [session('id_apotek_active')])->where('is_deleted', 0)->pluck('nama_panjang', 'id');
        $apoteks_tujuans->prepend('-- Pilih Apotek Asal Permintaan TF --','');

        return view('transfer.konfirmasi')->with(compact('apoteks', 'apoteks_tujuans'));
    }

    public function list_konfirmasi(Request $request) {
        DB::statement(DB::raw('set @rownum = 0'));
        $data = TransaksiTransfer::select([
                DB::raw('@rownum  := @rownum  + 1 AS no'),
                'tb_nota_transfer.*',
                'a.nama_panjang as apotek',
                'b.nama_panjang as apotek_tujuan'
        ])
        ->leftJoin('tb_m_apotek as a', 'a.id', '=', 'tb_nota_transfer.id_apotek')
        ->leftJoin('tb_m_apotek as b', 'b.id', '=', 'tb_nota_transfer.id_apotek_transfer')
        ->where(function($query) use($request){
            $query->where('tb_nota_transfer.is_deleted','=','0');
            if($request->notif != true){
                $query->where('tb_nota_transfer.id_apotek_transfer','=', session('id_apotek_active'));
                if($request->id_apotek_transfer != '') {
                    $query->where('tb_nota_transfer.id_apotek','=', $request->id_apotek_transfer);
                }
            }
        });

        if($request->notif == true){
            return (object) [
                'data' => $data->get(),
                'id_apotek' => session('id_apotek_active')
            ];            
        };

        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request){
            $query->where(function($query) use($request){
                $query->orwhere('a.nama_singkat','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('b.nama_singkat','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->editColumn('id_apotek', function ($data) {
            return $data->apotek;
        })
        ->editColumn('id_apotek_transfer', function ($data) {
            return $data->apotek_tujuan;
        })
        ->editColumn('is_status', function ($data) {
            $str = '<span class="label label-danger" data-toggle="tooltip" data-placement="top" title="Belum Dikonfirmasi" style="font-size:8pt;color:#e91e63;">Belum Dikonfirmasi</span>';
            if($data->is_status == 1) {
                $jumlah_all = count($data->detail_transfer);
                $jumlah_sudah = count($data->detail_transfer_sudah);
                $str = '<span class="label label-success" data-toggle="tooltip" data-placement="top" title="Sudah Dikonfirmasi" style="font-size:8pt;color:#009688;"></i> Sudah Dikonfirmasi</span>';
                $str .= '<br> | <span class="label label-warning" data-toggle="tooltip" data-placement="top" title="Sudah Dikonfirmasi" style="font-size:8pt;">'.$jumlah_sudah.'/'.$jumlah_all.' item sudah terkonfirmasi</span>';
            } else {
                $jumlah_all = count($data->detail_transfer);
                $jumlah_sudah = count($data->detail_transfer_sudah);

                if($jumlah_sudah > 0) {
                    $str = '<span class="label label-warning" data-toggle="tooltip" data-placement="top" title="Belum Dikonfirmasi" style="font-size:8pt;color:#FBC02D;">Belum Dikonfirmasi Keseluruhan</span>';
                    $str .= '<br> | <span class="label label-warning" data-toggle="tooltip" data-placement="top" title="Sudah Dikonfirmasi" style="font-size:8pt;">'.$jumlah_sudah.'/'.$jumlah_all.' item sudah terkonfirmasi</span>';
                }
            }
            return $str;
        })
        ->addColumn('checkList', function ($data) {
            return '<input type="checkbox" name="check_list" data-id="'.$data->id.'" value="'.$data->id.'"/>';
        })
        ->addcolumn('action', function($data) {
            $id = encrypt($data->id);
            $btn = '<div class="btn-group">';
            $btn .= '<a href="'.url('/transfer_outlet/konfirmasi_transfer/'.$id).'" title="Konfirmasi Barang Datang" class="btn btn-info btn-sm"><span data-toggle="tooltip" data-placement="top" title="Konfirmasi Barang Datang"><i class="fa fa-check"></i> Konfirmasi</span></a>';
            $btn .= '<a href="'.url('/transfer/export/'.$id).'" title="Cetak PDF" class="btn btn-secondary btn-sm"><span data-toggle="tooltip" data-placement="top" title="Cetak PDF"><i class="fa fa-file-pdf"></i> PDF</span></a>';
            $btn .='</div>';
            return $btn;
        })   
        ->rawColumns(['checkList', 'DT_RowIndex', 'id_apotek', 'action', 'id_apotek_transfer', 'is_status'])
        ->addIndexColumn()
        ->make(true);  
    }

    public function edit_transfer(Request $request){
        $id = $request->id;
        $no = $request->no;
        $detail = TransaksiTransferDetail::select(['tb_detail_nota_transfer.*', 'a.nama'])
                        ->leftjoin('tb_m_obat as a', 'a.id', 'tb_detail_nota_transfer.id_obat')
                        ->where('tb_detail_nota_transfer.id', $id)
                        ->first();
        $transfer = TransaksiTransfer::find($detail->id_nota);
        $apotek = MasterApotek::find($transfer->id_apotek);
        return view('transfer._form_edit_transfer')->with(compact('detail', 'no', 'apotek'));
    }

    public function update_transfer_detail(Request $request, $id) {
        $detail = TransaksiTransferDetail::find($id);
        $detail->jumlah = $request->jumlah;
        $detail->keterangan = $request->keterangan;

        if($detail->save()){
            return response()->json(array(
                'submit' => 1,
                'success' => 'Data berhasil disimpan'
            ));
        }
        else{
            return response()->json(array(
                'submit' => 0,
                'error' => 'Data gagal disimpan'
            ));
        }
    }

    public function GetExportPdf($id) {
        $id = decrypt($id);
        $outlet = MasterApotek::find(session('id_apotek_active'));
        $apoteker = User::find($outlet->id_apoteker);
        $transfer = TransaksiTransfer::find($id);
        $apotek_tujuan = MasterApotek::find($transfer->id_apotek_transfer);
        $detail_transfers = TransaksiTransferDetail::where('id_nota', $transfer->id)->where('is_deleted', 0)->get();

        $fileTTD = '';
        if(!is_null($apoteker->file)) {
            $split_co = explode('.' , $apoteker->file);
            $ext_co = end($split_co);
            $mime = 'image/'.$ext_co;
            $ttd = DB::table('tb_users_ttd')->where('id_user', $apoteker->id)->first();
            $fileTTD = '<img src="data:'.$mime.';base64, '.$ttd->image.'" width="50" height="50">';
            //echo '<img src="data:image/jpg;base64, '.$ttd->image.'" width="50" height="50"></a>';exit();
        }
        
        # sp biasa
        $judul = "SURAT PERMINTAAN TRANSFER";
        $opening = '
                    <p class="f-small title-margin">Yang bertanda tangan di bawah ini,</p>
                    <p class="f-small title-margin">nama &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;  &nbsp;  : &nbsp; '.$apoteker->nama.'</p>
                    <p class="f-small title-margin">jabatan &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; : &nbsp; Apoteker Penanggung jawab Apotek</p>
                    <p></p>
                    <p class="f-small title-margin">mengajukan permintaan transfer obat kepada,</p>

                    <p class="f-small title-margin">nama outlet &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; : &nbsp; '.$apotek_tujuan->nama_singkat.'</p>
                    <p class="f-small title-margin">alamat &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; : &nbsp; '.$apotek_tujuan->alamat.'</p>
                    <p class="f-small title-margin">telepon &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; : &nbsp; '.$apotek_tujuan->telepon.'</p>
                    <p></p>

                    <p class="f-small title-margin">dengan daftar obat sebagai berikut,</p>
                    <p></p>
                    ';
        $tables = '<table style="width: 100%;">
                    <tr>
                    <td style="width:5%;text-align:center;"><b>No</b></td>
                    <td style="width:75%;text-align:center;"><b>Nama Obat</b></td>
                    <td style="width:10%;text-align:center;"><b>Jumlah</b></td>
                    <td style="width:10%;text-align:center;"><b>Satuan</b></td>
                </tr>';

        $i = 0;
        foreach($detail_transfers as $data) {
            $i++;
            $tables .= '
                    <tr>
                    <td style="width:5%;text-align:center;">'.$i.'</td>
                    <td style="width:75%;text-align:left;">'.$data->obat->nama.'</td>
                    <td style="width:10%;text-align:center;">'.$data->jumlah.'</td>
                    <td style="width:10%;text-align:left;">'.$data->satuan->satuan.'</td>
                </tr>';
        }


        $closing = '
                    <p></p>
                    <p></p>
                    <p></p>
                    ';

        $tables .= '</table>';


        $ttd = '<table style="width: 100%;">
                    <tr>
                        <td style="width:50%;border:none;">
                            <p class="f-small title-margin text-center">Penerima Pesanan </p>
                            <p></p>
                            <p></p>
                            <p></p>
                            <p class="f-small title-margin text-center">...............................................</p>
                        </td>
                        <td style="width:50%;border:none;">
                            <p class="f-small title-margin text-center">'.$outlet->kota.', '.date('d/m/Y', strtotime($transfer->tgl_nota)).'</p>';
                            if(!is_null($apoteker->file)) {
                                $ttd .= '<p class="text-center">'.$fileTTD.'</p>';
                            } else {
                                $ttd .= '
                                        <p></p>
                                        <p></p>
                                        <p></p>';
                            }

                            $ttd .= '<p class="f-small title-margin text-center">Apt. '.$apoteker->nama.'</p>
                        </td>
                    </tr>
                </table>';
        
         // font-family: courier; 
        $data_ = '<style type="text/css">
                    table { 
                        text-align: justify; 
                        border-collapse: collapse;
                    }
                    table td, th { 
                        width: 15mm; 
                        border: 1px solid #ddd;
                        padding: 5px;
                        font-size:8pt;
                    }
                    .no-border{
                        border: none;
                    }
                    .h-bold{
                        font-weight: bold;
                    }
                    .f-large{
                        font-size: 18px;
                    }
                    .f-medium{
                        font-size: 15px;
                    }
                    .f-small{
                        font-size: 12px;
                    }
                    .f-hsmall{
                        font-size: 10px;
                    }
                    .f-xsmall{
                        font-size: 8px;
                    }
                    .title-margin{
                        margin-top: 5px;
                        margin-bottom: 0px;
                    }
                    .text-center{
                        text-align: center;
                    }
                    </style>';
        $data_ .= '
                    <p class="h-bold f-medium title-margin" style="padding-bottom:8px;">APOTEK BWF '.strtoupper($outlet->nama_panjang).'</p>
                    <p class="f-hsmall title-margin">'.$outlet->alamat.'</p>
                    <p class="f-hsmall title-margin">Telp. '.$outlet->telepon.'</p>
                    <p class="f-hsmall title-margin">Apoteker : '.$apoteker->nama.'</p>
                    <hr>
                    <p class="f-medium h-bold title-margin text-center">'.$judul.'</p>
                    <p class="f-medium h-bold title-margin text-center" style="margin:0px;padding-bottom: 40px;">ID.Nota : '.$transfer->id.'</p>';

        $data_ .= $opening;
        $data_ .= $tables;
        $data_ .= $closing;
        $data_ .= $ttd;
        
        $html2pdf = new Html2Pdf('P','A4');
        $html2pdf->writeHTML($data_);
        $html2pdf->output();
    }

    public function send_sign(Request $request)
    {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $user = User::find($apotek->id_apoteker);
        if($request->password != 'false' OR $request->password != false) {
            $pass = $request->password;
            if (Hash::check($pass, $user->password)) {
                $data_ = TransaksiTransfer::find($request->id);
                $data_->is_sign = 1;
                $data_->sign_by = Auth::id();
                $data_->sign_at = date('Y-m-d H:i:s');

                if($data_->save()){
                    return array('status' => 1, 'message' => 'Data permintaan Transfer sudah di tanda tangani');
                }else{
                    return array('status' => 0, 'message' => 'Gagal menyiman data sign');
                }
            }else{
                return array('status' => 0, 'message' => 'Password yang anda masukan tidak sesuai');
            }
        } else {
            return array('status' => 0, 'message' => 'Password yang anda masukan kosong');
        }
    } 
}
