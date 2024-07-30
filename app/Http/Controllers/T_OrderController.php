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
use App\TransaksiOrder;
use App\TransaksiOrderDetail;
use App\JenisSP;
use App\User;
use Spipu\Html2Pdf\Html2Pdf;

use App;
use Datatables;
use DB;
use Auth;
use Hash;
use Crypt;
use Storage;
use App\Traits\DynamicConnectionTrait;

class T_OrderController extends Controller
{
    use DynamicConnectionTrait;
    public function index() {
       // echo "sementara ditutup, sampai so selesai";exit();
        $cek2_ = session('apotek_order_aktif');
        if($cek2_ == null) {
            session(['apotek_order_aktif'=> '']);
        }

        $cek_ = session('suplier_order_aktif');
        if($cek_ == null) {
            session(['suplier_order_aktif'=> '']);
        }

        $cek3_ = session('status_order_aktif');
        if($cek3_ == null) {
            session(['status_order_aktif'=> 0]);
        }

        $apoteks = MasterApotek::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama_panjang', 'id');
        $apoteks->prepend('-- Pilih Apotek --','');

        $id_suplier = DefectaOutlet::on($this->getConnectionName())->select(['id_suplier_order'])->where('id_apotek', session('id_apotek_active'))->where('id_process', session('status_order_aktif'))->where('is_deleted', 0)->where('id_status', 1)->get(); // ->where('id_status', 1)
        $id_suplier->push('155');
        //dd($status_order_aktif);

        $supliers = MasterSuplier::on($this->getConnectionName())->whereIn('id', $id_suplier)->where('is_deleted', 0)->pluck('nama', 'id');
        $supliers->prepend('-- Pilih Suplier --','');

       

        $apotek_order_aktif = session('apotek_order_aktif');
        $suplier_order_aktif = session('suplier_order_aktif');
        $status_order_aktif = session('status_order_aktif');
        return view('order.index')->with(compact('apoteks', 'supliers', 'apotek_order_aktif', 'suplier_order_aktif', 'status_order_aktif'));
    }

    public function list_order(Request $request)
    {
        $id_apotek = session('id_apotek_active');
        //$id_apotek = session('apotek_order_aktif');
        $id_suplier = session('suplier_order_aktif');
        $id_process = session('status_order_aktif');

        DB::connection($this->getConnection())->statement(DB::raw('set @rownum = 0'));
        $data = DefectaOutlet::on($this->getConnectionName())->select([
                DB::raw('@rownum  := @rownum  + 1 AS no'),
                'tb_defecta_outlet.*',
                'b.nama',
                'b.barcode',
                'b.sku',
                'c.nama_singkat',
                'd.nama as suplier'
        ])
        ->leftjoin('tb_m_obat as b', 'b.id', '=', 'tb_defecta_outlet.id_obat')
        ->leftJoin('tb_m_apotek as c', 'c.id', '=', 'tb_defecta_outlet.id_apotek')
        ->leftJoin('tb_m_suplier as d', 'd.id', '=', 'tb_defecta_outlet.id_suplier_order')
        ->where(function($query) use($request, $id_apotek, $id_suplier){
            $query->where('tb_defecta_outlet.is_deleted','=','0');
            $query->where('tb_defecta_outlet.id_status','=', 1);
            $query->where('tb_defecta_outlet.id_apotek','=', session('id_apotek_active'));
           /* if($id_apotek != '') {
                $query->where('tb_defecta_outlet.id_apotek','=', $id_apotek);
            }*/
            if($id_suplier != '') {
                $query->where('tb_defecta_outlet.id_suplier_order','=', $id_suplier);
            }
        });

        $btn_set = ''; // 0 = belum ada proses, 1 = proses, 2 = complete

        if ($id_process=='0') {
            $data->where('tb_defecta_outlet.id_process', $id_process);
            $btn_set .= '
                <button type="submit" class="btn btn-info w-md m-b-5 pull-right animated fadeInLeft" onclick="set_nota_order()"><i class="fa fa-fw fa-plus"></i> Nota Order</a>';
        } else if ($id_process=='1') {
            $data->where('tb_defecta_outlet.id_process', $id_process);
            $btn_set .= '
                <a class="btn btn-secondary w-md m-b-5 pull-right animated fadeInLeft text-white" style="text-decoration: none;" href="'.url('/order/data_order').'"><i class="fa fa-fw fa-list"></i> List Data Order</a>';
        } else if ($id_process=='2') {
            $data->where('tb_defecta_outlet.id_process', $id_process);
            $btn_set .= '
                <a class="btn btn-secondary w-md m-b-5 pull-right animated fadeInLeft text-white" style="text-decoration: none;" href="'.url('/order/data_order').'"><i class="fa fa-fw fa-list"></i> List Data Order</a>';
        } 

        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request){
            $query->where(function($query) use($request){
                $query->orwhere('b.nama','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('b.barcode','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('b.sku','LIKE','%'.$request->get('search')['value'].'%');
            });
        })   
        ->addColumn('checkList', function ($data) {
            if($data->id_process == 0) {
                return '<input type="checkbox" name="check_list" data-id="'.$data->id.'" data-id_apotek="'.$data->id_apotek.'" data-id_suplier="'.$data->id_suplier_order.'" value="'.$data->id.'"/>';
            }
        })
        ->editcolumn('total_stok', function($data) use($request){
            $dt = DB::connection($this->getConnectionDefault())->table('tb_m_stok_harga_'.session('nama_apotek_singkat_active'))->where('id_obat', $data->id_obat)->first();
            return $dt->stok_akhir;
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
        ->editcolumn('id_suplier_order', function($data) {
            return $data->suplier;
        })
        ->addcolumn('action', function($data) {
            $btn = '';
            if($data->id_process == 0) {
                $btn .= '<span class="btn btn-danger btn-sm" onClick="delete_item('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-trash-alt"></i></span>';
            }

            return $btn;
        })
        ->rawColumns(['checkList', 'total_stok', 'total_buffer', 'forcasting', 'id_suplier_order', 'DT_RowIndex', 'apotek', 'action'])
        ->addIndexColumn()
        ->with([
                'btn_set' => $btn_set,
            ])
        ->make(true);  
    }

    public function create() {
        $order = new TransaksiOrder;
        $order->setDynamicConnection();
        $supliers = MasterSuplier::on($this->getConnectionName())->whereIn('id', [$order->id_suplier, 155])->where('is_deleted', 0)->pluck('nama', 'id');
        $apoteks = MasterApotek::on($this->getConnectionName())->whereIn('id', [$order->id_apotek])->where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $tanggal = date('Y-m-d');
        $var = 2;

        $jenisSP = JenisSP::on($this->getConnectionName())->pluck('jenis', 'id');
        $jenisSP->prepend('-- Pilih Jenis SP --','');

        $satuans = MasterSatuan::on($this->getConnectionName())->pluck('satuan', 'id');
        $satuans->prepend('-- Pilih Satuan --','');

        $detail_orders = collect();

        return view('order.edit')->with(compact('order', 'supliers','apoteks', 'tanggal', 'var', 'jenisSP', 'detail_orders', 'satuans'));
    }

    public function store(Request $request) {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        $order = new TransaksiOrder;
        $order->setDynamicConnection();
        $order->fill($request->except('_token'));
        $generateNomor =  $this->generateNomor();
        $generateNomor = json_decode($generateNomor);
        $order->nomor = $generateNomor->nomor;
        $order->kode = $generateNomor->kode;
        $order->id_suplier = $request->id_suplier;
        $order->id_apotek = $request->id_apotek;
        $detail_orders = $request->detail_order;
        $validator = $order->validate();
        if($validator->fails()){
            $id_apotek = explode(",", $request->input('id_apotek'));
            $id_suplier = explode(",", $request->input('id_suplier'));
            $id_defecta = explode(",", $request->input('id_defecta'));

            $jenisSP = JenisSP::on($this->getConnectionName())->pluck('jenis', 'id');
            $jenisSP->prepend('-- Pilih Jenis SP --','');

            $id_suplier[] = 155;
            $supliers = MasterSuplier::on($this->getConnectionName())->whereIn('id', $id_suplier)->where('is_deleted', 0)->pluck('nama', 'id');
            $jum_suplier = count($supliers);
            if($jum_suplier > 1) {
                session()->flash('error', 'Data yang dipilih terdiri dari '.$jum_suplier.' suplier, pastikan data yang dipilih dari suplier yang sama!');
                return redirect('order');
            }

            $defectas = DefectaOutlet::on($this->getConnectionName())->select([
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

            $apoteks = MasterApotek::on($this->getConnectionName())->whereIn('id', $id_apotek)->where('is_deleted', 0)->pluck('nama_singkat', 'id');
            $order = new TransaksiOrder;
            $order->setDynamicConnection();
            $detail_orders = new TransaksiOrderDetail;
            $detail_orders->setDynamicConnection();
            $tanggal = date('Y-m-d');
            $var = 1;

            session()->flash('error', 'Gagal menyimpan data order, pastikan semua data pada form telah terisi!');
            return view('order.create')->with(compact('defectas', 'supliers', 'order', 'detail_orders', 'tanggal', 'var', 'apoteks', 'jenisSP'));
        }else{
            $order->save_from_array($detail_orders,1);
            session()->flash('success', 'Sukses menyimpan data order!');
            return redirect('order/data_order');
        }
    }

    public function show($id) {

    }

    public function edit($id) {
        $order = TransaksiOrder::on($this->getConnectionName())->find($id);
        $supliers = MasterSuplier::on($this->getConnectionName())->whereIn('id', [$order->id_suplier, 155])->where('is_deleted', 0)->pluck('nama', 'id');
        $apoteks = MasterApotek::on($this->getConnectionName())->whereIn('id', [$order->id_apotek])->where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $tanggal = date('Y-m-d');
        $var = 2;

        $jenisSP = JenisSP::on($this->getConnectionName())->pluck('jenis', 'id');
        $jenisSP->prepend('-- Pilih Jenis SP --','');

        $satuans = MasterSatuan::on($this->getConnectionName())->pluck('satuan', 'id');
        $satuans->prepend('-- Pilih Satuan --','');

        $detail_orders = TransaksiOrderDetail::on($this->getConnectionName())->where('id_nota', $order->id)->where('is_deleted', 0)->get();

        return view('order.edit')->with(compact('order', 'supliers','apoteks', 'tanggal', 'var', 'jenisSP', 'detail_orders', 'satuans'));
    }

    public function update(Request $request, $id) {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        $order = TransaksiOrder::on($this->getConnectionName())->find($id);
        $order->fill($request->except('_token'));
        if(is_null($order->kode) || $order->kode == "") {
            $generateNomor =  $this->generateNomor();
            $generateNomor = json_decode($generateNomor);
            $order->nomor = $generateNomor->nomor;
            $order->kode = $generateNomor->kode;
        }
        $detail_orders = $request->detail_order;

        $validator = $order->validate();
        if($validator->fails()){
            session()->flash('error', 'Gagal menyimpan data order!');
            return redirect('order/'.$order->id.'/edit');
        }else{
            $order->save_from_array($detail_orders,2);
            session()->flash('success', 'Sukses menyimpan data order!');
            return redirect('order/'.$order->id.'/edit');
        }
    }

    public function destroy($id)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        $order = TransaksiOrder::on($this->getConnectionName())->find($id);
        $order->is_deleted = 1;
        $order->deleted_at = date('Y-m-d H:i:s');
        $order->deleted_by = Auth::user()->id;

        $detail_orders = $order->detail_order;

        foreach ($detail_orders as $key => $val) {
            $val->is_deleted = 1;
            $val->deleted_at = date('Y-m-d H:i:s');
            $val->deleted_by = Auth::user()->id;
            $val->save();

            $defecta = DefectaOutlet::on($this->getConnectionName())->find($val->id_defecta);
            $defecta->id_process = 0;
            $defecta->updated_at = date('Y-m-d H:i:s');
            $defecta->updated_by = Auth::id();
            $defecta->save();
        }

        if($order->save()){
            echo 1;
        }else{
            echo 0;
        }
    }

    public function set_apotek_order_aktif(Request $request) {
        session(['apotek_order_aktif'=> $request->id_apotek]);
        echo $request->id_apotek;
    }

    public function set_suplier_order_aktif(Request $request) {
        session(['suplier_order_aktif'=> $request->id_suplier]);
        echo $request->id_suplier;
    }

    public function set_status_order_aktif(Request $request) {
        session(['status_order_aktif'=> $request->id_status]);
        echo $request->id_status;
    }

    public function list_order_defecta(Request $request)
    {
        $id_apotek = session('apotek_order_aktif');

        DB::connection($this->getConnection())->statement(DB::raw('set @rownum = 0'));
        $data = DefectaOutlet::on($this->getConnectionName())->select([
                DB::raw('@rownum  := @rownum  + 1 AS no'),
                'tb_defecta_outlet.*',
                'b.nama',
                'b.barcode',
                'c.nama_singkat',
        ])
        ->leftjoin('tb_m_obat as b', 'b.id', '=', 'tb_defecta_outlet.id_obat')
        ->leftJoin('tb_m_apotek as c', 'c.id', '=', 'tb_defecta_outlet.id_apotek')
        ->where(function($query) use($request, $id_apotek){
            $query->where('tb_defecta_outlet.is_deleted','=','0');
            $query->where('tb_defecta_outlet.id_status','=', 1);
            if($id_apotek != '') {
                $query->where('tb_defecta_outlet.id_apotek','=', $id_apotek);
            }
        });

        $btn_set = '';
        if ($request->input('id_process')=='0') {
            $data->where('tb_defecta_outlet.id_process', $id_process);
            $btn_set .= '
                <button type="submit" class="btn btn-info w-md m-b-5 pull-right animated fadeInLeft" onclick="set_multi_status_defecta(1)"><i class="fa fa-fw fa-shopping-cart"></i> Order</button>';
            $btn_set .= '
                <button type="submit" class="btn btn-secondary w-md m-b-5 pull-right animated fadeInLeft" onclick="set_multi_status_defecta(2)"><i class="fa fa-fw fa-exchange-alt"></i> Transfer</button>';
        } else if ($request->input('id_process')=='1') {
            $data->where('tb_defecta_outlet.id_process', $id_process);
            $btn_set = '
                <button type="submit" class="btn btn-danger w-md m-b-5 pull-right animated fadeInLeft" onclick="set_multi_status_defecta(0)"><i class="fa fa-fw fa-undo"></i> Batal Order</button>';
        } else if ($request->input('id_process')=='2') {
            $data->where('tb_defecta_outlet.id_process', $id_process);
            $btn_set = '
                <button type="submit" class="btn btn-danger w-md m-b-5 pull-right animated fadeInLeft" onclick="set_multi_status_defecta(0)"><i class="fa fa-fw fa-undo"></i> Batal Transfer</button>';
        } 

        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request){
            $query->where(function($query) use($request){
                $query->orwhere('b.nama','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('b.barcode','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('b.sku','LIKE','%'.$request->get('search')['value'].'%');
            });
        })   
        ->addColumn('checkList', function ($data) {
            if($data->id_status == 0) {
                return '<input type="checkbox" name="check_list" data-id="'.$data->id.'" value="'.$data->id.'"/>';
            }
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
            return $data->nama_singkat;
        })
        ->editcolumn('status', function($data) {
            return '<span class="badge bg-"><i class="fa "></i> Process</span>';
        })
        ->addcolumn('action', function($data) {
           // $d_ = DefectaOutlet::on($this->getConnectionName())->where('id_stok_harga', $data->id)->where('id_apotek', $apotek->id)->first();
            $btn = '<div class="btn-group">';
            if ($data->id_status == 0){
                $btn .= '<span class="btn btn-info btn-sm" onClick="set_status_defecta('.$data->id.', 1)" data-toggle="tooltip" data-placement="top" title="Order">Order</span>';
                $btn .= '<span class="btn btn-secondary btn-sm" onClick="set_status_defecta('.$data->id.', 2)" data-toggle="tooltip" data-placement="top" title="Transfer">Transfer</span>';
            } else if($data->id_status == 1){
                $btn .= '<span class="btn btn-danger btn-sm" onClick="set_status_defecta('.$data->id.', 0)" data-toggle="tooltip" data-placement="top" title="Batal Order">Batal Order</span>';
            } else if($data->id_status == 2) {
                $btn .= '<span class="btn btn-danger btn-sm" onClick="set_status_defecta('.$data->id.', 0)" data-toggle="tooltip" data-placement="top" title="Batal Transfer">Batal Transfer</span>';
            } else if($data->id_status == 3) {
                $btn .= '<span class="btn btn-danger btn-sm" onClick="set_status_defecta('.$data->id.', 0)" data-toggle="tooltip" data-placement="top" title="Batal Tolak">Batal Tolak</span>';
            } else {
                $btn .= '<span class="text-info"><i class="fa fa-fw fa-info"></i>-</span>';
            }
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['checkList', 'total_stok', 'total_buffer', 'forcasting', 'action', 'DT_RowIndex', 'status', 'apotek'])
        ->addIndexColumn()
        ->with([
                'btn_set' => $btn_set,
            ])
        ->make(true);  
    }

    public function set_nota_order(Request $request){
        $id_apotek = explode(",", $request->input('id_apotek'));
        $id_suplier = explode(",", $request->input('id_suplier'));
        $id_defecta = explode(",", $request->input('id_defecta'));

        $jenisSP = JenisSP::on($this->getConnectionName())->pluck('jenis', 'id');
        $jenisSP->prepend('-- Pilih Jenis SP --','');

        $id_suplier[] = 155;
        $supliers = MasterSuplier::on($this->getConnectionName())->whereIn('id', $id_suplier)->where('is_deleted', 0)->pluck('nama', 'id');
        /*$jum_suplier = count($supliers);
        if($jum_suplier > 1) {
            session()->flash('error', 'Data yang dipilih terdiri dari '.$jum_suplier.' suplier, pastikan data yang dipilih dari suplier yang sama!');
            return redirect('order');
        }*/

        $defectas = DefectaOutlet::on($this->getConnectionName())->select([
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

        $apoteks = MasterApotek::on($this->getConnectionName())->whereIn('id', $id_apotek)->where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $order = new TransaksiOrder;
        $order->setDynamicConnection();
        $detail_orders = new TransaksiOrderDetail;
        $detail_orders->setDynamicConnection();
        $tanggal = date('Y-m-d');
        $var = 1;

        $satuans      = MasterSatuan::on($this->getConnectionName())->where('is_deleted', 0)->pluck('satuan', 'id');
        $satuans->prepend('-- Pilih Satuan --','');

        return view('order.create')->with(compact('defectas', 'supliers', 'order', 'detail_orders', 'tanggal', 'var', 'apoteks', 'jenisSP', 'satuans'));
    }

    public function cari_obat(Request $request) {
        $obat = MasterObat::on($this->getConnectionName())->where('barcode', $request->barcode)->first();

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
        $apotek = MasterApotek::on($this->getConnectionName())->find($request->id_apotek);
        $inisial = strtolower($apotek->nama_singkat);

        DB::connection($this->getConnection())->statement(DB::raw('set @rownum = 0'));
        $data = DB::connection($this->getConnectionDefault())->table('tb_m_stok_harga_'.$inisial.' as a')
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
                $query->orwhere('b.sku','LIKE','%'.$request->get('search')['value'].'%');
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
        $obat = MasterObat::on($this->getConnectionName())->find($request->id_obat);

        return json_encode($obat);
    }

    public function edit_detail(Request $request){
        $id = $request->id;
        $no = $request->no;
        $defecta = DefectaOutlet::on($this->getConnectionName())->select(['tb_defecta_outlet.*', 'a.nama'])
                        ->leftjoin('tb_m_obat as a', 'a.id', 'tb_defecta_outlet.id_obat')
                        ->where('tb_defecta_outlet.id', $id)
                        ->first();
        if(is_null($defecta->jumlah_penjualan)) {
            $defecta->jumlah_penjualan = 0;
            $defecta->margin = 0;
        }
        $apotek = MasterApotek::on($this->getConnectionName())->find($defecta->id_apotek);
        return view('order._form_edit_detail')->with(compact('defecta', 'no', 'apotek'));
    }

    public function update_defecta(Request $request, $id) {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        $defecta = DefectaOutlet::on($this->getConnectionName())->find($id);
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

    public function data_order() {
        $apoteks = MasterApotek::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama_panjang', 'id');
        $apoteks->prepend('-- Pilih Apotek --','');

        $id_suplier = DefectaOutlet::on($this->getConnectionName())->select(['id_suplier_order'])->where('id_apotek', session('id_apotek_active'))->where('is_deleted', 0)->get(); // ->where('id_status', 1)
        $id_suplier->push('155');
        $supliers = MasterSuplier::on($this->getConnectionName())->whereIn('id', $id_suplier)->where('is_deleted', 0)->pluck('nama', 'id');
        $supliers->prepend('-- Pilih Suplier --','');

        $jenisSPs = JenisSP::on($this->getConnectionName())->pluck('jenis', 'id');
        $jenisSPs->prepend('-- Pilih Jenis SP --','');

        return view('order.data_order')->with(compact('apoteks', 'supliers', 'jenisSPs'));
    }

    public function list_data_order(Request $request) {
        DB::connection($this->getConnection())->statement(DB::raw('set @rownum = 0'));
        $data = TransaksiOrder::on($this->getConnectionName())->select([
                DB::raw('@rownum  := @rownum  + 1 AS no'),
                'tb_nota_order.*',
                'a.nama_panjang as apotek',
                'b.nama as suplier'
        ])
        ->leftJoin('tb_m_apotek as a', 'a.id', '=', 'tb_nota_order.id_apotek')
        ->leftJoin('tb_m_suplier as b', 'b.id', '=', 'tb_nota_order.id_suplier')
        ->where(function($query) use($request){
            $query->where('tb_nota_order.is_deleted','=','0');
            $query->where('tb_nota_order.id_apotek','=', session('id_apotek_active'));
            if($request->id_suplier != '') {
                $query->where('tb_nota_order.id_suplier','=', $request->id_suplier);
            }

            if($request->id_jenis != '') {
                $query->where('tb_nota_order.id_jenis','=', $request->id_jenis);
            }

            if($request->kode != '' OR !is_null($request->kode)) {
                $query->where('tb_nota_order.kode', 'LIKE', '%'.$request->kode.'%');
            }

            if($request->tanggal != "") {
                $split                      = explode("-", $request->tanggal);
                $tgl_awal       = date('Y-m-d',strtotime($split[0]));
                $tgl_akhir      = date('Y-m-d',strtotime($split[1]));
                $query->whereDate('tb_nota_order.tgl_nota','>=', $tgl_awal);
                $query->whereDate('tb_nota_order.tgl_nota','<=', $tgl_akhir);
            } else {
                $request->tanggal = date('m/01/Y').' - '.date('m/d/Y');
                $split                      = explode("-", $request->tanggal);
                $tgl_awal       = date('Y-m-d',strtotime($split[0]));
                $tgl_akhir      = date('Y-m-d',strtotime($split[1]));
                $query->whereDate('tb_nota_order.tgl_nota','>=', $tgl_awal);
                $query->whereDate('tb_nota_order.tgl_nota','<=', $tgl_akhir);
            }
        });

        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request){
            $query->where(function($query) use($request){
                $query->orwhere('a.nama_singkat','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('b.nama','LIKE','%'.$request->get('search')['value'].'%');
            });
        })   
        ->addColumn('checkList', function ($data) {
            return '<input type="checkbox" name="check_list" data-id="'.$data->id.'" value="'.$data->id.'"/>';
        })
        ->editColumn('kode', function ($data) {
            $str = '<span class"text-danger">(belum ada nomor)</span>';
            if($data->kode != "" OR !is_null($data->kode)) {
                $str = $data->kode;
            }
            return $str;
        })
        ->editColumn('is_status', function ($data) {
            $str = '<span class="label label-danger" data-toggle="tooltip" data-placement="top" title="Belum Dikonfirmasi" style="font-size:8pt;color:#e91e63;">Belum Dikonfirmasi</span>';
            if($data->is_status == 1) {
                $jumlah_all = count($data->detail_order);
                $jumlah_sudah = count($data->detail_order_sudah);
                $str = '<span class="label label-success" data-toggle="tooltip" data-placement="top" title="Sudah Dikonfirmasi" style="font-size:8pt;color:#009688;"></i> Sudah Dikonfirmasi</span>';
                $str .= '<br> | <span class="label label-warning" data-toggle="tooltip" data-placement="top" title="Sudah Dikonfirmasi" style="font-size:8pt;">'.$jumlah_sudah.'/'.$jumlah_all.' item sudah terkonfirmasi</span>';
            } else {
                $jumlah_all = count($data->detail_order);
                $jumlah_sudah = count($data->detail_order_sudah);

                if($jumlah_sudah > 0) {
                    $str = '<span class="label label-warning" data-toggle="tooltip" data-placement="top" title="Belum Dikonfirmasi" style="font-size:8pt;color:#FBC02D;">Belum Dikonfirmasi Keseluruhan</span>';
                    $str .= '<br> | <span class="label label-warning" data-toggle="tooltip" data-placement="top" title="Sudah Dikonfirmasi" style="font-size:8pt;">'.$jumlah_sudah.'/'.$jumlah_all.' item sudah terkonfirmasi</span>';
                }
            }
            return $str;
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
            $jumlah_sudah = count($data->detail_order_sudah);
            $btn = '<div class="btn-group">';
            if($data->is_sign == 0) {
                $btn .= '<a href="'.url('/order/'.$data->id.'/edit').'" title="Edit Data" class="btn btn-primary btn-sm"><span data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i> Edit</span></a>';
                $btn .= '<span class="btn btn-danger btn-sm" onClick="delete_order('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-trash-alt"></i> Hapus</span>';

                if(session('id_role_active') == 4 OR session('id_role_active') == 1) {
                    $btn .= '<span class="btn btn-warning btn-sm" onClick="sign('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Tandai SP ini sudah dicek dan ttd"><i class="fa fa-signature"></i> Sign</span>';
                }
            }

            if($data->is_sign == 1) {
                if(session('id_role_active') == 4 OR session('id_role_active') == 1) {
                    $btn .= '<span class="btn btn-danger btn-sm" onClick="unsign('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Unsign"><i class="fa fa-unlock"></i> UnSign</span>';
                }

                $btn .= '<a href="'.url('/pembelian/konfirmasi_barang_datang/'.$id).'" title="Konfirmasi Barang Datang" class="btn btn-info btn-sm"><span data-toggle="tooltip" data-placement="top" title="Konfirmasi Barang Datang"><i class="fa fa-check"></i> Konfirmasi</span></a>';
                /*$btn .= '<a href="'.url('/order/cetakSP/'.$id).'" title="Cetak" class="btn btn-warning btn-sm"><span data-toggle="tooltip" data-placement="top" title="Cetak"><i class="fa fa-print"></i> Cetak</span></a>';*/

                
                $btn .= '<a href="'.url('/order/exportSPA4/'.$id).'" title="Cetak PDF A4" class="btn btn-secondary btn-sm"><span data-toggle="tooltip" data-placement="top" title="Cetak PDF A4"><i class="fa fa-file-pdf"></i> Cetak A4</span></a>';

                $btn .= '<a href="'.url('/order/exportSPA5/'.$id).'" title="Cetak PDF A5" class="btn btn-secondary btn-sm"><span data-toggle="tooltip" data-placement="top" title="Cetak PDF A5"><i class="fa fa-file-pdf"></i> Cetak A5</span></a>';
            }
            $btn .='</div>';
            return $btn;
        })   
        ->rawColumns(['checkList', 'DT_RowIndex', 'apotek', 'action', 'is_status', 'kode', 'is_sign'])
        ->addIndexColumn()
        ->make(true);  
    }

    public function edit_order(Request $request){
        $id = $request->id;
        $no = $request->no;
        $detail = TransaksiOrderDetail::on($this->getConnectionName())->select(['tb_detail_nota_order.*', 'a.nama'])
                        ->leftjoin('tb_m_obat as a', 'a.id', 'tb_detail_nota_order.id_obat')
                        ->where('tb_detail_nota_order.id', $id)
                        ->first();
        $order = TransaksiOrder::on($this->getConnectionName())->find($detail->id_nota);
        $apotek = MasterApotek::on($this->getConnectionName())->find($order->id_apotek);
        return view('order._form_edit_order')->with(compact('detail', 'no', 'apotek'));
    }

    public function update_order_detail(Request $request, $id) {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        $detail = TransaksiOrderDetail::on($this->getConnectionName())->find($id);
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

    public function generateNomor()
    {
        $apotek = MasterApotek::on($this->getConnectionName())->find(session('id_apotek_active'));
        $last = TransaksiOrder::on($this->getConnectionName())->select(DB::raw('MAX(nomor) as max'))->whereYear('tgl_nota', date('Y'))->where('is_deleted', 0)->first();
        $now = $last->max+1;
        if(empty($last)){
            $nourut = '0001';
        } else {
            $strlen = 5;
            $sisa_nol = $strlen - strlen($now);
            $nol = str_repeat(0,$sisa_nol);
            if(strlen($now) < $strlen){
                $nourut = $nol.$now;
            } else {
                $nourut = $now;
            }
        }

        return json_encode(array('kode' => $apotek->kode_apotek.'.'.date('Y').'.'.date('m').'.'.$nourut, 'nomor' => $now));
    }

    public function GetExportPdfSPA4($id) {
        $id = decrypt($id);
        $outlet = MasterApotek::on($this->getConnectionName())->find(session('id_apotek_active'));
        $apoteker = User::on($this->getConnectionName())->find($outlet->id_apoteker);
        $order = TransaksiOrder::on($this->getConnectionName())->find($id);
        $suplier = MasterSuplier::on($this->getConnectionName())->find($order->id_suplier);
        $detail_orders = TransaksiOrderDetail::on($this->getConnectionName())->where('id_nota', $order->id)->where('is_deleted', 0)->get();

        //$order->file = null;
        $fileTTD = '';
        if(!is_null($apoteker->file)) {
            /*$split_co = explode('.' , $apoteker->file);
            $ext_co = end($split_co);
            $mime = 'image/'.$ext_co;
            $ttd = DB::connection($this->getConnectionName())->table('tb_users_ttd')->where('id_user', $apoteker->id)->first();
            $fileTTD = '<img src="data:'.$mime.';base64, '.$ttd->image.'" width="50" height="50">';*/
            //echo '<img src="data:image/jpg;base64, '.$ttd->image.'" width="50" height="50"></a>';exit();
        }


        if($apoteker->nosipa == '' AND is_null($apoteker->nosipa)) {
            $apoteker->nosipa = '<span><i>(nomor sipa belum diset)</i></span>';
        }

        if($order->id_jenis == 1 OR $order->id_jenis > 5) {
            # sp biasa
            $judul = "SURAT PESANAN OBAT";
            $opening = '
                        <p class="f-small title-margin">Yang bertanda tangan di bawah ini,</p>
                        <p class="f-small title-margin">nama &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;  &nbsp;  : &nbsp; '.$apoteker->nama.'</p>
                        <p class="f-small title-margin">jabatan &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; : &nbsp; Apoteker Penanggung jawab Apotek</p>
                        <p class="f-small title-margin">nomor SIPA &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; : &nbsp; '.$apoteker->nosipa.'</p>
                        <p></p>
                        <p></p>
                        <p class="f-small title-margin">mengajukan pesanan obat kepada,</p>

                        <p class="f-small title-margin">nama distributor &nbsp; &nbsp; : &nbsp; '.$suplier->nama.'</p>
                        <p class="f-small title-margin">alamat &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; : &nbsp; '.$suplier->alamat.'</p>
                        <p class="f-small title-margin">telepon &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; : &nbsp; '.$suplier->telp.'</p>
                        <p></p>

                        <p class="f-small title-margin">dengan daftar obat yang dipesan sebagai berikut,</p>
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
            foreach($detail_orders as $data) {
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
                        <p class="f-small title-margin">obat tersebut akan digunakan untuk,</p>
                        <p class="f-small title-margin">nama sarana &nbsp; &nbsp; &nbsp; &nbsp;  &nbsp;  : &nbsp; Apotek BWF '.$outlet->nama_panjang.'</p>
                        <p class="f-small title-margin">alamat sarana &nbsp; &nbsp; &nbsp; &nbsp; : &nbsp; '.$outlet->alamat.'</p>
                        <p class="f-small title-margin">nomor SIA &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; : &nbsp; '.$outlet->nosia.'</p>
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
                                <p class="f-small title-margin text-center">'.$outlet->kota.', '.date('d/m/Y', strtotime($order->tgl_nota)).'</p>';
                                if(!is_null($apoteker->file)) {
                                    //$ttd .= '<p class="text-center">'.$fileTTD.'</p>';
                                    $ttd .= '
                                            <p></p>
                                            <p></p>
                                            <p></p>';
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
        } else if($order->id_jenis == 2) {
            # sp Prekursor
            $judul = "SURAT PESANAN OBAT MENGANDUNG PREKURSOR FARMASI";
            $opening = '
                        <p class="f-small title-margin">Yang bertanda tangan di bawah ini,</p>
                        <p class="f-small title-margin">nama &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;  &nbsp;  : &nbsp; '.$apoteker->nama.'</p>
                        <p class="f-small title-margin">jabatan &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; : &nbsp; Apoteker Penanggung jawab Apotek</p>
                        <p class="f-small title-margin">nomor SIPA &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; : &nbsp; '.$apoteker->nosipa.'</p>
                        <p></p>
                        <p></p>
                        <p class="f-small title-margin">mengajukan pesanan obat mengandung Prekursor Farmasi kepada,</p>

                        <p class="f-small title-margin">nama distributor &nbsp; &nbsp; : &nbsp; '.$suplier->nama.'</p>
                        <p class="f-small title-margin">alamat &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; : &nbsp; '.$suplier->alamat.'</p>
                        <p class="f-small title-margin">telepon &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; : &nbsp; '.$suplier->telp.'</p>
                        <p></p>

                        <p class="f-small title-margin">jenis obat mengandung Prekursor Farmasi yang dipesan sebagai berikut,</p>
                        <p></p>
                        ';
            $tables = '<table style="width: 100%;">
                        <tr>
                        <td style="width:5%;text-align:center;"><b>No</b></td>
                        <td style="width:35%;text-align:center;"><b>Nama Obat mengandung Prekursor Farmasi</b></td>
                        <td style="width:20%;text-align:center;"><b>Zat Aktif Prekursor Farmasi</b></td>
                        <td style="width:20%;text-align:center;"><b>Bentuk dan Kekuatan Sediaan</b></td>
                        <td style="width:10%;text-align:center;"><b>Jumlah</b></td>
                        <td style="width:10%;text-align:center;"><b>Satuan</b></td>
                    </tr>';

            $i = 0;
            foreach($detail_orders as $data) {
                $i++;
                $tables .= '
                        <tr>
                        <td style="width:5%;text-align:center;">'.$i.'</td>
                        <td style="width:35%;text-align:left;">'.$data->obat->nama.'</td>
                        <td style="width:20%;text-align:left;">'.$data->obat->zat_aktif.'</td>
                        <td style="width:20%;text-align:left;">'.$data->obat->bentuk_kekuatan_sediaan.'</td>
                        <td style="width:10%;text-align:center;">'.$data->jumlah.'</td>
                        <td style="width:10%;text-align:left;">'.$data->satuan->satuan.'</td>
                    </tr>';
            }


            $closing = '
                        <p></p>
                        <p class="f-small title-margin">obat yang mengandung Prekursor Farmasi tersebut akan digunakan untuk memenuhi kebutuhan, </p>
                        <p class="f-small title-margin">nama sarana &nbsp; &nbsp; &nbsp; &nbsp;  &nbsp;  : &nbsp; Apotek BWF '.$outlet->nama_panjang.'</p>
                        <p class="f-small title-margin">alamat sarana &nbsp; &nbsp; &nbsp; &nbsp; : &nbsp; '.$outlet->alamat.'</p>
                        <p class="f-small title-margin">nomor SIA &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; : &nbsp; '.$outlet->nosia.'</p>
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
                                <p class="f-small title-margin text-center">'.$outlet->kota.', '.date('d/m/Y', strtotime($order->tgl_nota)).'</p>';
                                if(!is_null($order->file)) {
                                    $ttd .= $fileTTD;
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
        } else if($order->id_jenis == 3) {
            # OOT
            $judul = "SURAT PESANAN OBAT-OBAT TERTENTU FARMASI";
            $opening = '
                        <p class="f-small title-margin">Yang bertanda tangan di bawah ini,</p>
                        <p class="f-small title-margin">nama &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;  &nbsp;  : &nbsp; '.$apoteker->nama.'</p>
                        <p class="f-small title-margin">jabatan &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; : &nbsp; Apoteker Penanggung jawab Apotek</p>
                        <p class="f-small title-margin">nomor SIPA &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; : &nbsp; '.$apoteker->nosipa.'</p>
                        <p></p>
                        <p></p>
                        <p class="f-small title-margin">mengajukan pesanan obat mengandung Obat-Obat Tertentu (OOT) Farmasi kepada,</p>

                        <p class="f-small title-margin">nama distributor &nbsp; &nbsp; : &nbsp; '.$suplier->nama.'</p>
                        <p class="f-small title-margin">alamat &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; : &nbsp; '.$suplier->alamat.'</p>
                        <p class="f-small title-margin">telepon &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; : &nbsp; '.$suplier->telp.'</p>
                        <p></p>

                        <p class="f-small title-margin">jenis obat mengandung Obat-Obat Tertentu (OOT) Farmasi yang dipesan sebagai berikut,</p>
                        <p></p>
                        ';
            $tables = '<table style="width: 100%;">
                        <tr>
                        <td style="width:5%;text-align:center;"><b>No</b></td>
                        <td style="width:35%;text-align:center;"><b>Nama Obat mengandung OOT Farmasi</b></td>
                        <td style="width:20%;text-align:center;"><b>Zat Aktif OOT Farmasi</b></td>
                        <td style="width:20%;text-align:center;"><b>Bentuk dan Kekuatan Sediaan</b></td>
                        <td style="width:10%;text-align:center;"><b>Jumlah</b></td>
                        <td style="width:10%;text-align:center;"><b>Satuan</b></td>
                    </tr>';

            $i = 0;
            foreach($detail_orders as $data) {
                $i++;
                $tables .= '
                        <tr>
                        <td style="width:5%;text-align:center;">'.$i.'</td>
                        <td style="width:35%;text-align:left;">'.$data->obat->nama.'</td>
                        <td style="width:20%;text-align:left;">'.$data->obat->zat_aktif.'</td>
                        <td style="width:20%;text-align:left;">'.$data->obat->bentuk_kekuatan_sediaan.'</td>
                        <td style="width:10%;text-align:center;">'.$data->jumlah.'</td>
                        <td style="width:10%;text-align:left;">'.$data->satuan->satuan.'</td>
                    </tr>';
            }


            $closing = '
                        <p></p>
                        <p class="f-small title-margin">obat yang mengandung Obat-Obat Tertentu (OOT) Farmasi tersebut akan digunakan untuk memenuhi kebutuhan, </p>
                        <p class="f-small title-margin">nama sarana &nbsp; &nbsp; &nbsp; &nbsp;  &nbsp;  : &nbsp; Apotek BWF '.$outlet->nama_panjang.'</p>
                        <p class="f-small title-margin">alamat sarana &nbsp; &nbsp; &nbsp; &nbsp; : &nbsp; '.$outlet->alamat.'</p>
                        <p class="f-small title-margin">nomor SIA &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; : &nbsp; '.$outlet->nosia.'</p>
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
                                <p class="f-small title-margin text-center">'.$outlet->kota.', '.date('d/m/Y', strtotime($order->tgl_nota)).'</p>';
                                if(!is_null($order->file)) {
                                    $ttd .= $fileTTD;
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
        } else if($order->id_jenis == 4) {
            # sp Psikotropika
            $judul = "SURAT PESANAN PSIKOTROPIKA";
            $opening = '
                        <p class="f-small title-margin">Yang bertanda tangan di bawah ini,</p>
                        <p class="f-small title-margin">nama &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;  &nbsp;  : &nbsp; '.$apoteker->nama.'</p>
                        <p class="f-small title-margin">jabatan &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; : &nbsp; Apoteker Penanggung jawab Apotek</p>
                        <p class="f-small title-margin">nomor SIPA &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; : &nbsp; '.$apoteker->nosipa.'</p>
                        <p></p>
                        <p></p>
                        <p class="f-small title-margin">mengajukan pesanan Psikotropika kepada,</p>

                        <p class="f-small title-margin">nama distributor &nbsp; &nbsp; : &nbsp; '.$suplier->nama.'</p>
                        <p class="f-small title-margin">alamat &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; : &nbsp; '.$suplier->alamat.'</p>
                        <p class="f-small title-margin">telepon &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; : &nbsp; '.$suplier->telp.'</p>
                        <p></p>

                        <p class="f-small title-margin">jenis Psikotropika yang dipesan sebagai berikut,</p>
                        <p></p>
                        ';
            $tables = '<table style="width: 100%;">
                        <tr>
                        <td style="width:5%;text-align:center;"><b>No</b></td>
                        <td style="width:35%;text-align:center;"><b>Nama Obat mengandung Psikotropika</b></td>
                        <td style="width:20%;text-align:center;"><b>Zat Aktif Psikotropika</b></td>
                        <td style="width:20%;text-align:center;"><b>Bentuk dan Kekuatan Sediaan</b></td>
                        <td style="width:10%;text-align:center;"><b>Jumlah</b></td>
                        <td style="width:10%;text-align:center;"><b>Satuan</b></td>
                    </tr>';

            $i = 0;
            foreach($detail_orders as $data) {
                $i++;
                $tables .= '
                        <tr>
                        <td style="width:5%;text-align:center;">'.$i.'</td>
                        <td style="width:35%;text-align:left;">'.$data->obat->nama.'</td>
                        <td style="width:20%;text-align:left;">'.$data->obat->zat_aktif.'</td>
                        <td style="width:20%;text-align:left;">'.$data->obat->bentuk_kekuatan_sediaan.'</td>
                        <td style="width:10%;text-align:center;">'.$data->jumlah.'</td>
                        <td style="width:10%;text-align:left;">'.$data->satuan->satuan.'</td>
                    </tr>';
            }


            $closing = '
                        <p></p>
                        <p class="f-small title-margin">obat yang mengandung Psikotropika tersebut akan digunakan untuk memenuhi kebutuhan, </p>
                        <p class="f-small title-margin">nama sarana &nbsp; &nbsp; &nbsp; &nbsp;  &nbsp;  : &nbsp; Apotek BWF '.$outlet->nama_panjang.'</p>
                        <p class="f-small title-margin">alamat sarana &nbsp; &nbsp; &nbsp; &nbsp; : &nbsp; '.$outlet->alamat.'</p>
                        <p class="f-small title-margin">nomor SIA &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; : &nbsp; '.$outlet->nosia.'</p>
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
                                <p class="f-small title-margin text-center">'.$outlet->kota.', '.date('d/m/Y', strtotime($order->tgl_nota)).'</p>';
                                if(!is_null($order->file)) {
                                    $ttd .= $fileTTD;
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
        } else if($order->id_jenis == 5) {
            # sp Narkotika
            $judul = "SURAT PESANAN NARKOTIKA";
            $opening = '
                        <p class="f-small title-margin">Yang bertanda tangan di bawah ini,</p>
                        <p class="f-small title-margin">nama &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;  &nbsp;  : &nbsp; '.$apoteker->nama.'</p>
                        <p class="f-small title-margin">jabatan &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; : &nbsp; Apoteker Penanggung jawab Apotek</p>
                        <p class="f-small title-margin">nomor SIPA &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; : &nbsp; '.$apoteker->nosipa.'</p>
                        <p></p>
                        <p></p>
                        <p class="f-small title-margin">mengajukan pesanan Narkotika kepada,</p>

                        <p class="f-small title-margin">nama distributor &nbsp; &nbsp; : &nbsp; '.$suplier->nama.'</p>
                        <p class="f-small title-margin">alamat &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; : &nbsp; '.$suplier->alamat.'</p>
                        <p class="f-small title-margin">telepon &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; : &nbsp; '.$suplier->telp.'</p>
                        <p></p>

                        <p class="f-small title-margin">jenis Narkotika yang dipesan sebagai berikut,</p>
                        <p></p>
                        ';
            $tables = '<table style="width: 100%;">
                        <tr>
                        <td style="width:5%;text-align:center;"><b>No</b></td>
                        <td style="width:35%;text-align:center;"><b>Nama Obat mengandung Narkotika</b></td>
                        <td style="width:20%;text-align:center;"><b>Zat Aktif Narkotika</b></td>
                        <td style="width:20%;text-align:center;"><b>Bentuk dan Kekuatan Sediaan</b></td>
                        <td style="width:10%;text-align:center;"><b>Jumlah</b></td>
                        <td style="width:10%;text-align:center;"><b>Satuan</b></td>
                    </tr>';

            $i = 0;
            foreach($detail_orders as $data) {
                $i++;
                $tables .= '
                        <tr>
                        <td style="width:5%;text-align:center;">'.$i.'</td>
                        <td style="width:35%;text-align:left;">'.$data->obat->nama.'</td>
                        <td style="width:20%;text-align:left;">'.$data->obat->zat_aktif.'</td>
                        <td style="width:20%;text-align:left;">'.$data->obat->bentuk_kekuatan_sediaan.'</td>
                        <td style="width:10%;text-align:center;">'.$data->jumlah.'</td>
                        <td style="width:10%;text-align:left;">'.$data->satuan->satuan.'</td>
                    </tr>';
            }


            $closing = '
                        <p></p>
                        <p class="f-small title-margin">obat yang mengandung Narkotika tersebut akan digunakan untuk memenuhi kebutuhan, </p>
                        <p class="f-small title-margin">nama sarana &nbsp; &nbsp; &nbsp; &nbsp;  &nbsp;  : &nbsp; Apotek BWF '.$outlet->nama_panjang.'</p>
                        <p class="f-small title-margin">alamat sarana &nbsp; &nbsp; &nbsp; &nbsp; : &nbsp; '.$outlet->alamat.'</p>
                        <p class="f-small title-margin">nomor SIA &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; : &nbsp; '.$outlet->nosia.'</p>
                        <p></p>
                        <p></p>
                        <p></p>
                        ';

            $tables .= '</table>';


            $ttd = '<table style="width: 100%;">
                        <tr>
                            <td style="width:50%;border:none;">
                                <p class="f-small title-margin text-center">Penerima Pesanan </p>
                                <img src="{{ url("fileaccess") }}/{{$id_en}}/{{ $jenis_encrypt }}/{{ $filename }}" class="img-circle elevation-2" alt="TTD">
                                <p class="f-small title-margin text-center">...............................................</p>
                            </td>
                            <td style="width:50%;border:none;">
                                <p class="f-small title-margin text-center">'.$outlet->kota.', '.date('d/m/Y', strtotime($order->tgl_nota)).'</p>';
                                if(!is_null($order->file)) {
                                    $ttd .= $fileTTD;
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
        } 

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
                    <p class="f-medium h-bold title-margin text-center" style="margin:0px;padding-bottom: 40px;">No.SP : '.$order->kode.'</p>';

        $data_ .= $opening;
        $data_ .= $tables;
        $data_ .= $closing;
        $data_ .= $ttd;
        
        $html2pdf = new Html2Pdf('P','A4');
        $html2pdf->writeHTML($data_);
        $html2pdf->output();
    }

    public function GetExportPdfSPA5($id) {
        $id = decrypt($id);
        $outlet = MasterApotek::on($this->getConnectionName())->find(session('id_apotek_active'));
        $apoteker = User::on($this->getConnectionName())->find($outlet->id_apoteker);
        $order = TransaksiOrder::on($this->getConnectionName())->find($id);
        $suplier = MasterSuplier::on($this->getConnectionName())->find($order->id_suplier);
        $detail_orders = TransaksiOrderDetail::on($this->getConnectionName())->where('id_nota', $order->id)->where('is_deleted', 0)->get();

        //$order->file = null;
        $fileTTD = '';
        if(!is_null($apoteker->file)) {
            /*$split_co = explode('.' , $apoteker->file);
            $ext_co = end($split_co);
            $mime = 'image/'.$ext_co;
            $ttd = DB::connection($this->getConnectionName())->table('tb_users_ttd')->where('id_user', $apoteker->id)->first();
            $fileTTD = '<img src="data:'.$mime.';base64, '.$ttd->image.'" width="50" height="50">';*/
            //echo '<img src="data:image/jpg;base64, '.$ttd->image.'" width="50" height="50"></a>';exit();
        }

        $path = asset('assets/dist/img/logo.png');//'myfolder/myimage.png';
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $logo = file_get_contents($path);
        $base64 = '<img src="data:image/' . $type . ';base64,' . base64_encode($logo).'">';


        if($apoteker->nosipa == '' AND is_null($apoteker->nosipa)) {
            $apoteker->nosipa = '<span><i>(nomor sipa belum diset)</i></span>';
        }

        if($order->id_jenis == 1 OR $order->id_jenis > 5) {
            # sp biasa
            $judul = "SURAT PESANAN / ORDER";

            $tables = '<table style="width: 100%;">
                        <tr>
                        <td style="width:5%;text-align:center;"><b>No</b></td>
                        <td style="width:50%;text-align:center;"><b>Nama Obat</b></td>
                        <td style="width:10%;text-align:center;"><b>Jumlah</b></td>
                        <td style="width:10%;text-align:center;"><b>Satuan</b></td>
                        <td style="width:25%;text-align:center;"><b>Keterangan</b></td>
                    </tr>';

            $i = 0;
            foreach($detail_orders as $data) {
                $i++;
                $tables .= '
                        <tr>
                        <td style="width:5%;text-align:center;">'.$i.'</td>
                        <td style="width:50%;text-align:left;">'.$data->obat->nama.'</td>
                        <td style="width:10%;text-align:center;">'.$data->jumlah.'</td>
                        <td style="width:10%;text-align:left;">'.$data->satuan->satuan.'</td>
                        <td style="width:25%;text-align:left;"></td>
                    </tr>';
            }

            $tables .= '</table>';
        } else if($order->id_jenis == 2) {
            # sp Prekursor
            $judul = "SURAT PESANAN OBAT MENGANDUNG PREKURSOR FARMASI";
            $tables = '<table style="width: 100%;">
                        <tr>
                        <td style="width:5%;text-align:center;"><b>No</b></td>
                        <td style="width:35%;text-align:center;"><b>Nama Obat mengandung Prekursor Farmasi</b></td>
                        <td style="width:15%;text-align:center;"><b>Zat Aktif Prekursor Farmasi</b></td>
                        <td style="width:15%;text-align:center;"><b>Bentuk dan Kekuatan Sediaan</b></td>
                        <td style="width:10%;text-align:center;"><b>Jumlah</b></td>
                        <td style="width:10%;text-align:center;"><b>Satuan</b></td>
                        <td style="width:10%;text-align:center;"><b>Ket.</b></td>
                    </tr>';

            $i = 0;
            foreach($detail_orders as $data) {
                $i++;
                $tables .= '
                        <tr>
                        <td style="width:5%;text-align:center;">'.$i.'</td>
                        <td style="width:35%;text-align:left;">'.$data->obat->nama.'</td>
                        <td style="width:20%;text-align:left;">'.$data->obat->zat_aktif.'</td>
                        <td style="width:20%;text-align:left;">'.$data->obat->bentuk_kekuatan_sediaan.'</td>
                        <td style="width:10%;text-align:center;">'.$data->jumlah.'</td>
                        <td style="width:10%;text-align:left;">'.$data->satuan->satuan.'</td>
                        <td style="width:10%;text-align:left;"></td>
                    </tr>';
            }

            $tables .= '</table>';
        } else if($order->id_jenis == 3) {
            # OOT
            $judul = "SURAT PESANAN OBAT-OBAT TERTENTU FARMASI";

            $tables = '<table style="width: 100%;">
                        <tr>
                        <td style="width:5%;text-align:center;"><b>No</b></td>
                        <td style="width:35%;text-align:center;"><b>Nama Obat mengandung OOT Farmasi</b></td>
                        <td style="width:15%;text-align:center;"><b>Zat Aktif OOT Farmasi</b></td>
                        <td style="width:15%;text-align:center;"><b>Bentuk dan Kekuatan Sediaan</b></td>
                        <td style="width:10%;text-align:center;"><b>Jumlah</b></td>
                        <td style="width:10%;text-align:center;"><b>Satuan</b></td>
                        <td style="width:10%;text-align:center;"><b>Ket.</b></td>
                    </tr>';

            $i = 0;
            foreach($detail_orders as $data) {
                $i++;
                $tables .= '
                        <tr>
                        <td style="width:5%;text-align:center;">'.$i.'</td>
                        <td style="width:35%;text-align:left;">'.$data->obat->nama.'</td>
                        <td style="width:20%;text-align:left;">'.$data->obat->zat_aktif.'</td>
                        <td style="width:20%;text-align:left;">'.$data->obat->bentuk_kekuatan_sediaan.'</td>
                        <td style="width:10%;text-align:center;">'.$data->jumlah.'</td>
                        <td style="width:10%;text-align:left;">'.$data->satuan->satuan.'</td>
                        <td style="width:10%;text-align:left;"></td>
                    </tr>';
            }

            $tables .= '</table>';
        } else if($order->id_jenis == 4) {
            # sp Psikotropika
            $judul = "SURAT PESANAN PSIKOTROPIKA";
            $tables = '<table style="width: 100%;">
                        <tr>
                        <td style="width:5%;text-align:center;"><b>No</b></td>
                        <td style="width:35%;text-align:center;"><b>Nama Obat mengandung Psikotropika</b></td>
                        <td style="width:15%;text-align:center;"><b>Zat Aktif Psikotropika</b></td>
                        <td style="width:15%;text-align:center;"><b>Bentuk dan Kekuatan Sediaan</b></td>
                        <td style="width:10%;text-align:center;"><b>Jumlah</b></td>
                        <td style="width:10%;text-align:center;"><b>Satuan</b></td>
                        <td style="width:10%;text-align:center;"><b>Ket.</b></td>
                    </tr>';

            $i = 0;
            foreach($detail_orders as $data) {
                $i++;
                $tables .= '
                        <tr>
                        <td style="width:5%;text-align:center;">'.$i.'</td>
                        <td style="width:35%;text-align:left;">'.$data->obat->nama.'</td>
                        <td style="width:20%;text-align:left;">'.$data->obat->zat_aktif.'</td>
                        <td style="width:20%;text-align:left;">'.$data->obat->bentuk_kekuatan_sediaan.'</td>
                        <td style="width:10%;text-align:center;">'.$data->jumlah.'</td>
                        <td style="width:10%;text-align:left;">'.$data->satuan->satuan.'</td>
                        <td style="width:10%;text-align:left;"></td>
                    </tr>';
            }

            $tables .= '</table>';
        } else if($order->id_jenis == 5) {
            # sp Narkotika
            $judul = "SURAT PESANAN NARKOTIKA";
            $tables = '<table style="width: 100%;">
                        <tr>
                        <td style="width:5%;text-align:center;"><b>No</b></td>
                        <td style="width:35%;text-align:center;"><b>Nama Obat mengandung Narkotika</b></td>
                        <td style="width:15%;text-align:center;"><b>Zat Aktif Narkotika</b></td>
                        <td style="width:15%;text-align:center;"><b>Bentuk dan Kekuatan Sediaan</b></td>
                        <td style="width:10%;text-align:center;"><b>Jumlah</b></td>
                        <td style="width:10%;text-align:center;"><b>Satuan</b></td>
                        <td style="width:10%;text-align:center;"><b>Ket.</b></td>
                    </tr>';

            $i = 0;
            foreach($detail_orders as $data) {
                $i++;
                $tables .= '
                        <tr>
                        <td style="width:5%;text-align:center;">'.$i.'</td>
                        <td style="width:35%;text-align:left;">'.$data->obat->nama.'</td>
                        <td style="width:20%;text-align:left;">'.$data->obat->zat_aktif.'</td>
                        <td style="width:20%;text-align:left;">'.$data->obat->bentuk_kekuatan_sediaan.'</td>
                        <td style="width:10%;text-align:center;">'.$data->jumlah.'</td>
                        <td style="width:10%;text-align:left;">'.$data->satuan->satuan.'</td>
                        <td style="width:10%;text-align:left;"></td>
                    </tr>';
            }

            $tables .= '</table>';
        } 

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
                        font-size: 16px;
                    }
                    .f-small{
                        font-size: 12px;
                    }
                    .f-hsmall{
                        font-size: 10px;
                    }
                    .f-xsmall{
                        font-size: 8px;
                        margin-left: 105px;
                    }
                    .title-margin{
                        margin-top: 5px;
                        margin-bottom: 0px;
                    }

                    .title-margin-left{
                        margin-left: 10px;
                    }

                    .text-center{
                        text-align: center;
                    }

                    #logo-ap{
                        position: absolute;
                        margin-top: 50px;
                        margin-left: 0px;
                    }

                    #logo-ap img{
                        width: 100px;
                        height: auto;
                    }
                    </style>';


        $data_ .= '<table>
                        <tr>
                            <td style="width:60%;border-color:#FFFFFF;" colspan="3">
                                <div id="logo-ap">'.$base64.'</div>
                                <p class="f-medium title-margin text-center h-bold" style="padding-bottom:3px;font-size:16pt;margin-left: 50px;">Apotek BWF</p>
                                <p class="f-hsmall title-margin text-center" style="margin-left: 50px;">'.$outlet->kode_apotek.' '.ucfirst($outlet->nama_panjang).'</p>
                                <p class="f-hsmall title-margin"></p>
                                <p class="f-hsmall title-margin"></p>
                                <p class="f-xsmall title-margin">SIA'.$outlet->nosia.'</p>
                                <p class="f-xsmall title-margin">'.$outlet->alamat.'</p>
                                <p class="f-xsmall title-margin">Telp. '.$outlet->telepon.'</p>
                                <p class="f-xsmall title-margin">APA : '.$apoteker->nama.'</p>
                                <p class="f-xsmall title-margin">SIPA : '.$apoteker->nosipa.'</p>
                            </td>
                            <td style="width:40%;border-color:#FFFFFF;text-align:left;" colspan="2">
                                <p class="f-hsmall title-margin title-margin-left">'.$outlet->kota.', '.date('d/m/Y', strtotime($order->tgl_nota)).'</p>
                                <p class="f-hsmall title-margin title-margin-left">Kepada Yth. &nbsp;'.$suplier->nama.'</p>
                                <p class="f-hsmall title-margin title-margin-left">di &nbsp;'.$suplier->alamat.'</p>
                                <p class="f-hsmall title-margin title-margin-left">............................................................</p>
                                <p class="f-hsmall title-margin title-margin-left">............................................................</p>
                                <p class="f-hsmall title-margin title-margin-left">&nbsp;</p>
                                <p class="f-hsmall title-margin title-margin-left">&nbsp;</p>
                                <p class="f-hsmall title-margin title-margin-left">&nbsp;</p>
                            </td>
                        </tr>
                    </table>';

        $data_ .= '<table>
                        <tr>
                            <td style="width:60%;border-color:#FFFFFF;" colspan="3">
                                
                                <p class="f-medium title-margin">'.$judul.'</p>
                            </td>
                            <td style="width:40%;border-color:#FFFFFF;text-align:left;" colspan="2">
                                <p class="f-hsmall title-margin title-margin-left">No.SP : '.$order->kode.'</p>
                            </td>
                        </tr>
                    </table>';

        $data_ .= $tables;

        $ttd = '<p></p>
                    <table style="width: 100%;">
                        <tr>
                            <td style="width:30%;border:none;">
                                <p class="f-small title-margin text-center"></p>
                                <p></p>
                                <p></p>
                                <p></p>
                                <p class="f-small title-margin text-center"></p>
                            </td>
                            <td style="width:70%;border:none;text-center">
                                <p class="f-small title-margin text-center">Pemesan,</p>';
                                if(!is_null($apoteker->file)) {
                                    //$ttd .= '<p class="text-center">'.$fileTTD.'</p>';
                                    $ttd .= '
                                            <p></p>
                                            <p></p>
                                            <p></p>';
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

        $data_ .= $ttd;

        
        $html2pdf = new Html2Pdf('P','A5');
        $html2pdf->writeHTML($data_);
        $html2pdf->output();
    }

    public function create_manual() {
        $order = new TransaksiOrder;
        $order->setDynamicConnection();
        $supliers = MasterSuplier::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama', 'id');
        $apoteks = MasterApotek::on($this->getConnectionName())->where('id', session('id_apotek_active'))->where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $tanggal = date('Y-m-d');
        $var = 2;

        $jenisSP = JenisSP::on($this->getConnectionName())->pluck('jenis', 'id');
        $jenisSP->prepend('-- Pilih Jenis SP --','');

        $satuans = MasterSatuan::on($this->getConnectionName())->pluck('satuan', 'id');
        $satuans->prepend('-- Pilih Satuan --','');

        $detail_orders = collect();

        $defectas = collect();

        return view('order.create')->with(compact('order', 'supliers','apoteks', 'tanggal', 'var', 'jenisSP', 'detail_orders', 'satuans', 'defectas'));
    }

    public function send_sign(Request $request)
    {
        $apotek = MasterApotek::on($this->getConnectionName())->find(session('id_apotek_active'));
        $user = User::on($this->getConnectionName())->find($apotek->id_apoteker);
        if($request->password != 'false' OR $request->password != false) {
            $pass = $request->password;
            if (Hash::check($pass, $user->password)) {
                $data_ = TransaksiOrder::on($this->getConnectionName())->find($request->id);
                $data_->is_sign = 1;
                $data_->sign_by = Auth::id();
                $data_->sign_at = date('Y-m-d H:i:s');

                if($data_->save()){
                    return array('status' => 1, 'message' => 'Data SP sudah di tanda tangani');
                }else{
                    return array('status' => 0, 'message' => 'Gagal menyiman data sign');
                }
            }else{
                //dd($request->password);
                return array('status' => 0, 'message' => 'Password yang anda masukan tidak sesuai');
            }
        } else {
            return array('status' => 0, 'message' => 'Password yang anda masukan kosong');
        }
    } 

    public function send_unsign(Request $request)
    {
        $apotek = MasterApotek::on($this->getConnectionName())->find(session('id_apotek_active'));
        $user = User::on($this->getConnectionName())->find($apotek->id_apoteker);
        if($request->password != 'false' OR $request->password != false) {
            $pass = $request->password;
            if (Hash::check($pass, $user->password)) {
                $data_ = TransaksiOrder::on($this->getConnectionName())->find($request->id);
                $data_->is_sign = 0;
                $data_->sign_by = null;
                $data_->sign_at = null;

                if($data_->save()){
                    return array('status' => 1, 'message' => 'Tanda tangan SP sudah di batalkan');
                }else{
                    return array('status' => 0, 'message' => 'Gagal menyiman data');
                }
            }else{
                //dd($request->password);
                return array('status' => 0, 'message' => 'Password yang anda masukan tidak sesuai');
            }
        } else {
            return array('status' => 0, 'message' => 'Password yang anda masukan kosong');
        }
    } 
}
