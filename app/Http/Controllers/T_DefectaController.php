<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterApotek;
use App\MasterObat;
use App\DefectaOutlet;
use App\MasterSatuan;
use App\MasterSuplier;
use App\MasterStatusOrder;
use App\TransaksiPenjualanDetail;
use App\TransaksiPembelianDetail;
use App\TransaksiTODetail;
use App\HistoriStok;
use App\MasterSettingSuplier;
use Maatwebsite\Excel\Facades\Excel;
use GuzzleHttp\Client;

use App\Exports\AnalisaPembelianExport;

use App;
use Cache;
use Datatables;
use DB;
use Auth;
use ZipArchive;
class T_DefectaController extends Controller
{
    protected static $expiredAt = 6 * 60 * 60;

    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 
        =======================================================================================
    */
    public function index()
    {
        //echo "sementara ditutup, sampai so selesai";exit();
        return view('defecta.index');
    }


    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 
        =======================================================================================
    */
    public function list_defecta(Request $request)
    {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        DB::statement(DB::raw('set @rownum = 0'));
        $data = DefectaOutlet::select([
                DB::raw('@rownum  := @rownum  + 1 AS no'),
                'tb_defecta_outlet.*',
                'b.nama',
                'b.barcode'
        ])
        ->leftjoin('tb_m_obat as b', 'b.id', '=', 'tb_defecta_outlet.id_obat')
        ->where(function($query) use($request){
            $query->where('tb_defecta_outlet.is_deleted','=','0');
            //$query->where('tb_defecta_outlet.id_apotek',session('id_apotek_active'));
        });

        $btn_set = '';
        if ($request->input('s_is_kirim')=='1') {
            $data->where('tb_defecta_outlet.is_kirim', 1);
            $btn_set = '
                <button type="submit" class="btn btn-warning w-md m-b-5 pull-right animated fadeInLeft" onclick="send_multi_defecta(0)"><i class="fa fa-fw fa-undo"></i> UnSend defecta</button>';
           
        }
        else if ($request->input('s_is_kirim')=='2') {
            $data->where('tb_defecta_outlet.is_kirim', '!=', 1);
            $btn_set = '
                <button type="submit" class="btn btn-primary w-md m-b-5 pull-right animated fadeInLeft" onclick="send_multi_defecta(1)"><i class="fa fa-fw fa-plus"></i> Buat SP</button>';
        }
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request){
            $query->where(function($query) use($request){
                $query->orwhere('nama','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('barcode','LIKE','%'.$request->get('search')['value'].'%');
                query->orwhere('b.sku','LIKE','%'.$request->get('search')['value'].'%');
            });
        })   
        ->addColumn('checkList', function ($data) {
            if($data->id_status == 0) {
                return '<input type="checkbox" name="check_list" data-id="'.$data->id.'" data-id_apotek="'.$data->id_apotek.'" data-id_suplier="'.$data->id_suplier_order.'" value="'.$data->id.'"/>';
            }
        })
        ->editcolumn('total_stok', function($data) use($request, $inisial){
            return $data->total_stok;
        })
        ->editcolumn('total_buffer', function($data) use($request){
            return $data->total_buffer;
        })
        ->editcolumn('forcasting', function($data) use ($apotek){
            return $data->forcasting;
        })
        ->editcolumn('status', function($data) use ($apotek){
            $statusOrder = MasterStatusOrder::find($data->id_status);
            $str =  '<span class="badge badge-'.$statusOrder->class.'"><i class="fa '.$statusOrder->icon.'"></i> '.$statusOrder->nama.'</span>';

            return $str;
        })
        ->editcolumn('id_suplier_order', function($data) use($request, $inisial){
            return $data->id_suplier_order;
        })
        ->editcolumn('jumlah_diajukan', function($data) use($request){
           // $x = $this->getJumlahMargin($data, $apotek, $tgl_awal, $tgl_akhir, $inisial);
            return $data->jumlah_diajukan.' '.$data->obat->satuan->satuan;
        })
        ->editcolumn('margin', function($data) use($request){
            //$x = $this->getJumlahMargin($data, $apotek, $tgl_awal, $tgl_akhir, $inisial);
            return 'Rp '.number_format($data->margin,0);
        })
        ->editcolumn('total', function($data) use($request){
            $total = $data->jumlah_diajukan * $data->harga_beli;
            return 'Rp '.number_format($total,0);
        })
        ->addcolumn('action', function($data) use ($apotek){
           // $d_ = DefectaOutlet::where('id_stok_harga', $data->id)->where('id_apotek', $apotek->id)->first();
            $btn = '<div class="btn-group">';
            if ($data->is_kirim == 0){
                if($data->is_disabled == 1) {
                    $btn .= '<span class="text-info"><i class="fa fa-fw fa-info"></i>obat tidak aktif</span>';
                } else {
                    $btn .= '<span class="btn btn-primary btn-sm" onClick="send_defecta('.$data->id.', 1)" data-toggle="tooltip" data-placement="top" title="Kirim defecta ke purchasing"><i class="fa fa-paper-plane"></i></span>';
                }
            } else {
                if($data->id_status == 0) {
                     $btn .= '<span class="btn btn-warning btn-sm" onClick="send_defecta('.$data->id.', 0)" data-toggle="tooltip" data-placement="top" title="Batal kirim defecta ke purchasing"><i class="fa fa-undo"></i></span>';
                } else {
                    $btn .= '<span class="text-info"><i class="fa fa-fw fa-info"></i>sudah diproses oleh purchasing</span>';
                }
            }
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['checkList', 'total_stok', 'total_buffer', 'forcasting', 'action', 'DT_RowIndex', 'status', 'id_satuan', 'jumlah_penjualan', 'total'])
        ->addIndexColumn()
        ->with([
                'btn_set' => $btn_set,
            ])
        ->make(true);  
    }

    public function send_defecta(Request $request)
    {
        $i = 0;
        foreach ($request->input('id_defecta') as $key => $value) {
            DB::table('tb_defecta_outlet')->where('id', $value)->update(['is_kirim'=> $request->input('act')]);
            $i++;
        }


        if($i> 0){
            return response()->json(array(
                'submit' => 1,
                'success' => 'Kirim data berhasil dilakukan',
            ));
        }
        else{
            return response()->json(array(
                'submit' => 0,
                'error' => 'Kirim data gagal dilakukan'
            ));
        }
    }

    public function create()
    {

    }

    public function store(Request $request) {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $data_ = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $request->id_obat)->first();
        $obat = MasterObat::find($request->id_obat);
        $defecta = new DefectaOutlet;

        if(!empty($defecta)) {
        } else {
            $defecta = new DefectaOutlet;
        }

        $defecta->fill($request->except('_token'));
        $defecta->id_obat = $request->id_obat;
        $defecta->id_suplier = $request->id_suplier_order;
        $defecta->id_suplier_order = $request->id_suplier_order;
        $defecta->id_apotek_transfer = $request->id_apotek_transfer;
        $defecta->total_stok = $request->stok;
        $defecta->total_buffer = $request->buffer;
        $defecta->forcasting = $request->forcasting;
        $defecta->id_apotek = $request->id_apotek;
        $defecta->jumlah_order = $defecta->jumlah_diajukan;
        $defecta->created_at = date('Y-m-d H:i:s');
        $defecta->created_by = Auth::id();
        $defecta->is_add_manual = 1;

        $validator = $defecta->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            if($defecta->save()) {
                DB::table('tb_m_stok_harga_'.$inisial)->where('id', $request->id_stok_harga)->update(['is_defecta'=> 1]);
                echo json_encode(array('status' => 1));
            } else {
                echo json_encode(array('status' => 0));
            }
            
        }
    }

   /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 22/02/2020
        =======================================================================================
    */
    public function show($id)
    {
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 22/02/2020
        =======================================================================================
    */
    public function edit($id)
    {
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 22/02/2020
        =======================================================================================
    */
    public function update(Request $request, $id)
    {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $data_ = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $request->id_obat)->first();
        $obat = MasterObat::find($request->id_obat);
        $defecta = DefectaOutlet::where('is_deleted', 0)
                    ->where('id_obat', $request->id_obat)
                    ->where('id_apotek', $apotek->id)
                    ->where('id_status', '!=', $request->id_status)
                    ->where('id_process', '!=', 1)
                    ->first();

        if(!empty($defecta)) {
        } else {
            $defecta = new DefectaOutlet;
        }

        $defecta->fill($request->except('_token'));
        $defecta->id_obat = $request->id_obat;
        $defecta->id_suplier = $request->id_suplier_order;
        $defecta->id_suplier_order = $request->id_suplier_order;
        $defecta->id_apotek_transfer = $request->id_apotek_transfer;
        $defecta->total_stok = $request->stok;
        $defecta->total_buffer = $request->buffer;
        $defecta->forcasting = $request->forcasting;
        $defecta->id_apotek = $request->id_apotek;
        $defecta->jumlah_order = $defecta->jumlah_diajukan;
        $defecta->created_at = date('Y-m-d H:i:s');
        $defecta->created_by = Auth::id();

        $validator = $defecta->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            if($defecta->save()) {
                DB::table('tb_m_stok_harga_'.$inisial)->where('id', $request->id_stok_harga)->update(['is_defecta'=> 1]);
                echo json_encode(array('status' => 1));
            } else {
                echo json_encode(array('status' => 0));
            }
            
        }
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 22/02/2020
        =======================================================================================
    */
    public function destroy($id)
    {
        $defecta = DefectaOutlet::find($id);
        if($defecta->delete()){
            echo 1;
        }else{
            echo 0;
        }
    }

    public function input() {
        ini_set('memory_limit', '-1'); 
        $active_defecta = session('active_defecta');
        if(empty($active_defecta)) {
            $active_defecta = null;
        }

        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $data_ = DB::table('tb_m_stok_harga_'.$inisial)->first();
        $last_hitung = date('d-m-Y H:i:s'); //, strtotime($data_->last_hitung)
        //echo "asdasdsa";exit();
        return view('defecta.input')->with(compact('last_hitung'));
    }

    public function list_defecta_input(Request $request) {
        ini_set('memory_limit', '-1'); 
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);

        if($request->tanggal != "") {
            $split                      = explode("-", $request->tanggal);
            $tgl_awal       = date('Y-m-d H:i:s',strtotime($split[0]));
            $tgl_akhir      = date('Y-m-d H:i:s',strtotime($split[1]));
        } else {
            $tgl_awal = date('Y-m-d').' 00:00:00';
            $tgl_akhir = date('Y-m-d').' 23:59:59';
        }

        DB::statement(DB::raw('set @rownum = 0'));
        if($request->id_order_by == 4 || $request->id_order_by == 5) {
            $in_penjualan =  TransaksiPenjualanDetail::select([ 'tb_detail_nota_penjualan.id_obat'])
                            ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')
                            ->whereDate('b.created_at','>=', $tgl_awal)
                            ->whereDate('b.created_at','<=', $tgl_akhir)
                            ->where('b.id_apotek_nota','=',$apotek->id)
                            ->where('b.is_deleted', 0)
                            ->groupBy('tb_detail_nota_penjualan.id_obat')->get();
                            

            //$data = collect();
            $data = DB::table('tb_m_stok_harga_'.$inisial)
                                ->select([
                                    DB::raw('@rownum  := @rownum  + 1 AS no'),
                                    'tb_m_stok_harga_'.$inisial.'.id',
                                    'tb_m_stok_harga_'.$inisial.'.harga_beli_ppn',
                                    'tb_m_stok_harga_'.$inisial.'.stok_akhir',
                                    'tb_m_stok_harga_'.$inisial.'.nama',
                                    'tb_m_stok_harga_'.$inisial.'.barcode',
                                    'c.satuan',
                                    DB::raw('0 as jumlah_pemakaian'),
                                    DB::raw('0 as jumlah_jual'),
                                    DB::raw('0 as margin')
                                ])
            ->join('tb_m_obat as d','d.id','=','tb_m_stok_harga_'.$inisial.'.id_obat')
            ->join('tb_m_satuan as c','c.id','=','d.id_satuan')
            ->where(function($query) use($request, $inisial, $in_penjualan){
                $query->where('tb_m_stok_harga_'.$inisial.'.is_disabled','=','0');
                $query->where('tb_m_stok_harga_'.$inisial.'.is_defecta','=','0');
                $query->whereNotIn('tb_m_stok_harga_'.$inisial.'.id_obat',$in_penjualan);
            })
            ->groupBy('tb_m_stok_harga_'.$inisial.'.id');
            if($request->id_order_by == 4) {
                $data = $data->orderByRaw("tb_m_stok_harga_".$inisial.".stok_akhir ASC, tb_m_stok_harga_".$inisial.".id ASC");
                //$data = $data->orderBy('tb_m_stok_harga_'.$inisial.'.stok_akhir', 'ASC');
            } else if($request->id_order_by == 5) {
                $data = $data->orderByRaw("tb_m_stok_harga_".$inisial.".stok_akhir ASC, tb_m_stok_harga_".$inisial.".id DESC");
                //$data = $data->orderBy('tb_m_stok_harga_'.$inisial.'.stok_akhir', 'DESC');
            } 
            /*$x = $data->get();
            dd($x);*/
        } else {
            $data = DB::table('tb_m_stok_harga_'.$inisial)
                                ->select([
                                    DB::raw('@rownum  := @rownum  + 1 AS no'),
                                    'tb_m_stok_harga_'.$inisial.'.id',
                                    'tb_m_stok_harga_'.$inisial.'.harga_beli_ppn',
                                    'tb_m_stok_harga_'.$inisial.'.stok_akhir',
                                    'tb_m_stok_harga_'.$inisial.'.nama',
                                    'tb_m_stok_harga_'.$inisial.'.barcode',
                                    'c.satuan',
                                    DB::raw('SUM(a.jumlah) as jumlah_pemakaian'),
                                    DB::raw('IF(SUM(a.jumlah) >=1, SUM((a.jumlah * a.harga_jual)-a.diskon), 0) as omzet'),
                                    DB::raw('IF(SUM(a.jumlah) >=1, SUM(a.jumlah * (a.harga_jual-tb_m_stok_harga_'.$inisial.'.harga_beli_ppn)), 0) as margin')
                                    //DB::raw('0 as jumlah_jual'),
                                    //DB::raw('0 as margin')
                                ])
            ->join('tb_m_obat as d','d.id','=','tb_m_stok_harga_'.$inisial.'.id_obat')
            ->join('tb_m_satuan as c','c.id','=','d.id_satuan')
            ->leftjoin('tb_detail_nota_penjualan as a', 'a.id_obat', '=', 'tb_m_stok_harga_'.$inisial.'.id_obat')
            ->leftjoin('tb_nota_penjualan as b', 'b.id', '=', 'a.id_nota')
            ->where(function($query) use($request, $inisial, $apotek, $tgl_awal, $tgl_akhir){
                $query->where('tb_m_stok_harga_'.$inisial.'.is_disabled','=','0');
                $query->where('tb_m_stok_harga_'.$inisial.'.is_defecta','=','0');
                $query->whereDate('b.created_at','>=', $tgl_awal);
                $query->whereDate('b.created_at','<=', $tgl_akhir);
                $query->where('b.id_apotek_nota','=',$apotek->id);
                $query->where('b.is_deleted', 0);
               // $query->where(DB::raw('SUM(a.jumlah)'), '>', 0);
            })
            ->groupBy('tb_m_stok_harga_'.$inisial.'.id');

            if($request->id_order_by == 1) {
                $data = $data->orderByRaw("tb_m_stok_harga_".$inisial.".stok_akhir ASC, tb_m_stok_harga_".$inisial.".id ASC");
                //$data = $data->orderBy('tb_m_stok_harga_'.$inisial.'.stok_akhir', 'ASC');
            } else if($request->id_order_by == 2) {
                $data = $data->orderByRaw("SUM(a.jumlah) DESC, tb_m_stok_harga_".$inisial.".id ASC");
                //$data = $data->orderBy(DB::raw('SUM(a.jumlah)'), 'DESC');
            } else if($request->id_order_by == 3) {
                //$data = $data->orderByRaw("IF(SUM(a.jumlah) >=1, SUM(a.jumlah * (a.harga_jual-tb_m_stok_harga_'.$inisial.'.harga_beli_ppn)), 0) DESC, tb_m_stok_harga_".$inisial.".id ASC");
                $data = $data->orderBy(DB::raw('IF(SUM(a.jumlah) >=1, SUM(a.jumlah * (a.harga_jual-tb_m_stok_harga_'.$inisial.'.harga_beli_ppn)), 0)'), 'DESC');
            }
        }
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request){
            $query->where(function($query) use($request){
                $query->orwhere('d.nama','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('d.barcode','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('d.sku','LIKE','%'.$request->get('search')['value'].'%');
            });
        })   
        ->editcolumn('id_satuan', function($data) use($request, $inisial){
            return $data->satuan;
        })
        ->editcolumn('total_stok', function($data) use($request, $inisial){
            return $data->stok_akhir;
        })
        ->editcolumn('jumlah_jual', function($data) use($request, $apotek, $tgl_awal, $tgl_akhir, $inisial){
           // $x = $this->getJumlahMargin($data, $apotek, $tgl_awal, $tgl_akhir, $inisial);
            return $data->jumlah_pemakaian;
        })
        ->editcolumn('margin', function($data) use($request, $apotek, $tgl_awal, $tgl_akhir, $inisial){
            //$x = $this->getJumlahMargin($data, $apotek, $tgl_awal, $tgl_akhir, $inisial);
            return 'Rp '.number_format($data->margin,0);
        })
        ->addcolumn('action', function($data) use ($apotek){
            $btn = '<div class="btn-group">';
            $btn .= '<span class="btn btn-primary btn-sm" onClick="add_defecta('.$data->id.', 0, '.$data->jumlah_pemakaian.', '.$data->margin.')" data-toggle="tooltip" data-placement="top" title="Tambahkan ke defecta"><i class="fa fa-plus"></i></span>';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['total_stok', 'jumlah_jual', 'margin', 'action', 'DT_RowIndex'])
        ->addIndexColumn()
        ->make(true);  
    }

    public function getJumlahMargin($data, $apotek, $tgl_awal, $tgl_akhir, $inisial) {
        $get = TransaksiPenjualanDetail::select(
                                DB::raw('@rownum  := @rownum  + 1 AS no'),
                                'tb_detail_nota_penjualan.id_obat',
                                DB::raw('SUM(tb_detail_nota_penjualan.jumlah) as jumlah_pemakaian'),
                                DB::raw('SUM((tb_detail_nota_penjualan.jumlah * tb_detail_nota_penjualan.harga_jual)-tb_detail_nota_penjualan.diskon) as omzet'),
                                DB::raw('SUM(tb_detail_nota_penjualan.jumlah * (tb_detail_nota_penjualan.harga_jual-c.harga_beli_ppn)) as margin')
                            )
                            ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')
                            ->leftjoin('tb_m_stok_harga_'.$inisial.' as c', 'c.id_obat', '=', 'tb_detail_nota_penjualan.id_obat')
                            ->whereDate('b.created_at','>=', $tgl_awal)
                            ->whereDate('b.created_at','<=', $tgl_akhir)
                            ->where('b.id_apotek_nota','=',$apotek->id)
                            ->where('tb_detail_nota_penjualan.id_obat','=',$data->id_obat)
                            ->where('b.is_deleted', 0)
                            ->first();
        return $get;
    }

    public function hitung() {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);

        $j = 0;
        $obats = DB::table('tb_m_stok_harga_'.$inisial)->limit(50)->get();
        foreach ($obats as $key => $obj) {
            $total_buffer = 0;
            $y1 = 0; 
            $y2 = 0;
            $y3 = 0;
            for ($i=1; $i <=3 ; $i++) { 
                $data_ = DB::table('tb_detail_nota_penjualan')
                ->select([
                            DB::raw('SUM(tb_detail_nota_penjualan.jumlah) AS jumlah')
                            ])
                ->leftJoin('tb_nota_penjualan','tb_nota_penjualan.id','=','tb_detail_nota_penjualan.id_nota')
                ->where(function ($query) use ($apotek, $i, $obj) {
                    $bulan_aktif = date('m') - $i;
                    $query->whereRaw('tb_detail_nota_penjualan.is_deleted = 0');
                    $query->whereRaw('tb_detail_nota_penjualan.id_obat = '.$obj->id_obat.'');
                    $query->whereRaw('tb_nota_penjualan.id_apotek_nota = '.$apotek->id.'');
                    $query->whereRaw('MONTH(tb_detail_nota_penjualan.created_at) ='.$bulan_aktif.'');
                })
                ->first();

                if($i==1) {
                    if($data_->jumlah != '' OR $data_->jumlah != null) {
                        $total_buffer = $data_->jumlah;
                    }
                }

                if($data_->jumlah != '' OR $data_->jumlah != null) {
                    if($i == 1) {
                        $y1 = $data_->jumlah;
                    } else if($i == 2) {
                        $y2 = $data_->jumlah;
                    } else if($i == 3) {
                        $y3 = $data_->jumlah;
                    }
                }
            }

            $x  = 3; //jumlah periode bulan yang digunakan
            $x1 = 1; $x2 = 2; $x3 = 3;

            $jum_x = 6; // jumlah dari bulan1 = 1, bulan2 = 2, bulan3 = 3
            $jum_x_kuadrat = 14; // jumlah x kuadrat dati tiap bulan
            $x_rata_rata = $jum_x/ $x;

            $jum_y = $y1 + $y2 + $y3; // ini jumlah penjualan selama 3 bulan terakhir
            $y_rata_rata =  $jum_y/$x;

            $jum_x_y = ($x1 * $y1) + ($x2 * $y2) + ($x3 * $y3); // jumlah pengalian diantara x dan y

            $b = ($jum_x_y - ($x * ($x_rata_rata * $y_rata_rata))) / ($jum_x_kuadrat - ($x * ($x_rata_rata * $x_rata_rata)));
            $a = $y_rata_rata - ($b * $x_rata_rata); // ini tuntuk mencari nilai dari a
            $y = $a + $b * 4; // a + bx;
            $abc = ceil($y);

            DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $obj->id_obat)->update(['total_buffer'=> $total_buffer, 'forcasting'=>$abc, 'last_hitung' => date('Y-m-d H:i:s')]);
            $j++;
        }

        session()->flash('success', 'Data yang dihitung sebanyak '.$j.' data!');
        return redirect('defecta');
    }

    public function add_defecta(Request $request){
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $id_stok_harga = $request->id_stok_harga;
        $id_defecta = $request->id_defecta;
        $data_ = DB::table('tb_m_stok_harga_'.$inisial)->where('id', $id_stok_harga)->first();
        $obat = MasterObat::find($data_->id_obat);
        $jumlah_pemakaian = $request->jumlah;
        $margin = $request->margin;
        //dd($jumlah_pemakaian);
        if(!empty($id_defecta)) {
            $defecta = DefectaOutlet::where('id', $id_keputusan_order)->first();
        } else {
            $defecta = new DefectaOutlet;
        }

        $supliers      = MasterSuplier::where('is_deleted', 0)->pluck('nama', 'id');
        $supliers->prepend('-- Pilih Suplier --','');
    
        return view('defecta._form_defecta')->with(compact('defecta', 'apotek', 'obat', 'data_', 'supliers', 'jumlah_pemakaian', 'margin'));
    }

    // START PURCHASING
    public function data_masuk() {
        $statuss = MasterStatusOrder::pluck('nama', 'id');
        $statuss->prepend('-- Pilih Status --','');

        $apoteks = MasterApotek::where('is_deleted', 0)->pluck('nama_panjang', 'id');
        $apoteks->prepend('-- Pilih Apotek --','');
        $cek_ = session('status_purchasing_aktif');
        $cek2_ = session('apotek_purchasing_aktif');
        $cek3_ = session('proses_purchasing_aktif');
        $process = collect([
            ['id' => 0, 'nama' => 'Belum ada proses'], 
            ['id' => 1, 'nama' => 'Sedang di proses'], 
            ['id' => 2, 'nama' => 'Proses selesai']
        ])->pluck('nama', 'id');
        $process->prepend('-- Pilih Proses --','');

        if($cek_ == null) {
            session(['status_purchasing_aktif'=> 0]);
        }

        if($cek2_ == null) {
            session(['apotek_purchasing_aktif'=> '']);
        }

        if($cek3_ == null) {
            session(['proses_purchasing_aktif'=> 0]);
        }

        $apotek_purchasing_aktif = session('apotek_purchasing_aktif');
        $status_purchasing_aktif = session('status_purchasing_aktif');
        $proses_purchasing_aktif = session('proses_purchasing_aktif');
        return view('defecta.data_masuk')->with(compact('statuss', 'apoteks', 'apotek_purchasing_aktif', 'status_purchasing_aktif', 'process', 'proses_purchasing_aktif'));
    }

    public function list_defecta_masuk(Request $request)
    {
        $id_apotek = session('apotek_purchasing_aktif');
        $id_status = session('status_purchasing_aktif');
        $id_proses = session('proses_purchasing_aktif');
        $apoteks = MasterApotek::where('is_deleted', 0)->whereNotIn('id', [$id_apotek])->get();

        DB::statement(DB::raw('set @rownum = 0'));
        $data = DefectaOutlet::select([
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
        ->where(function($query) use($request, $id_apotek, $id_status, $id_proses){
            $query->where('tb_defecta_outlet.is_deleted','=','0');
            if($id_apotek != '') {
                $query->where('tb_defecta_outlet.id_apotek','=', $id_apotek);
            }

            if($id_status != '') {
                $query->where('tb_defecta_outlet.id_status','=', $id_status);
            }
            
            if($id_proses != '') {
                $query->where('tb_defecta_outlet.id_process','=', $id_proses);
            }
        });

        $btn_set = '';
        if ($request->input('id_status')=='0') {
            $data->where('tb_defecta_outlet.id_status', $id_status);
            $btn_set .= '
                <button type="submit" class="btn btn-info w-md m-b-5 pull-right animated fadeInLeft" onclick="set_multi_status_defecta(1)"><i class="fa fa-fw fa-shopping-cart"></i> Order</button>';
            $btn_set .= '
                <button type="submit" class="btn btn-primary w-md m-b-5 pull-right animated fadeInLeft" onclick="set_multi_status_defecta(2)"><i class="fa fa-fw fa-exchange-alt"></i> Transfer</button>';
            $btn_set .= '
                <button type="submit" class="btn btn-danger w-md m-b-5 pull-right animated fadeInLeft" onclick="set_multi_status_defecta(3)"><i class="fa fa-fw fa-times"></i> Tolak</button>';
        } else if ($request->input('id_status')=='1') {
            $data->where('tb_defecta_outlet.id_status', $id_status);
            $btn_set = '
                <button type="submit" class="btn btn-danger w-md m-b-5 pull-right animated fadeInLeft" onclick="set_multi_status_defecta_draft(0)"><i class="fa fa-fw fa-undo"></i> Batal Order</button>';
        } else if ($request->input('id_status')=='2') {
            $data->where('tb_defecta_outlet.id_status', $id_status);
            $btn_set = '
                <button type="submit" class="btn btn-danger w-md m-b-5 pull-right animated fadeInLeft" onclick="set_multi_status_defecta_draft(0)"><i class="fa fa-fw fa-undo"></i> Batal Transfer</button>';
        } else if ($request->input('id_status')=='3') {
            $data->where('tb_defecta_outlet.id_status', $id_status);
            $btn_set = '
                <button type="submit" class="btn btn-danger w-md m-b-5 pull-right animated fadeInLeft" onclick="set_multi_status_defecta_draft(0)"><i class="fa fa-fw fa-undo"></i> Batal Tolak</button>';
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
                return '<input type="checkbox" name="check_list" data-id="'.$data->id.'" data-id_apotek="'.$data->id_apotek.'" value="'.$data->id.'"/>';
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
        ->editcolumn('status', function($data) {
            $statusOrder = MasterStatusOrder::find($data->id_status);
            $str =  '<span class="badge badge-'.$statusOrder->class.'"><i class="fa '.$statusOrder->icon.'"></i> '.$statusOrder->nama.'</span>';

            return $str;
        })
        ->editcolumn('id_satuan', function($data) use($request){
            return $data->obat->satuan->satuan;
        })
        ->editcolumn('jumlah_penjualan', function($data) use($request){
           // $x = $this->getJumlahMargin($data, $apotek, $tgl_awal, $tgl_akhir, $inisial);
            if($data->jumlah_penjualan == null) {
                $data->jumlah_penjualan = 0;
            }
            return $data->jumlah_penjualan;
        })
        ->editcolumn('margin', function($data) use($request){
            //$x = $this->getJumlahMargin($data, $apotek, $tgl_awal, $tgl_akhir, $inisial);
            return 'Rp '.number_format($data->margin,0);
        })
        ->editcolumn('apotek', function($data) {
            $status = '<small style="font-size:8pt;" class="badge bg-'.$data->class.'"><i class="fa '.$data->icon.'"></i> '.$data->status.'</small>';
            return $data->nama_singkat.'<br>'.$status;
        })
        ->editcolumn('suplier', function($data) {
            $cek_ = $data->data_pembelians;
            $jum = count($cek_);
            if($jum > 0) {
                $suplier = '';
                $i = 0;
                
                foreach ($cek_ as $key => $value) {
                    $i++;
                    $suplier .= $i.'. '.$value->nama;
                    if($i != $jum) {
                        $suplier .= '<br>';
                    }
                }
            } else {
                $suplier = '<small style="font-size:9pt;" class="text-red"><cite>Tidak ditemukan record pembelian</cite></small>';
            }
            return '<small style="font-size:9pt;">'.$suplier.'</small>';
        })
        ->editcolumn('nama', function($data) use($apoteks){
            $info = '<small>';
            $i = 0;
            foreach($apoteks as $obj) {
                $i++;
                $inisial = strtolower($obj->nama_singkat);
                $cek_ = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $data->id_obat)->first();
                $info .= $obj->nama_singkat.' : '.$cek_->stok_akhir;
                if($i != count($apoteks)) {
                    $info .= ' | ';
                }
            }
            $info .= '</small>';
            return '<b>'.$data->nama.'</b><br>'.$info;
        })
        ->addcolumn('action', function($data) {
           // $d_ = DefectaOutlet::where('id_stok_harga', $data->id)->where('id_apotek', $apotek->id)->first();
            $btn = '<div class="btn-group">';
            if ($data->id_status == 0){
                $btn .= '<span class="btn btn-info btn-sm" onClick="set_status_defecta('.$data->id.', '.$data->id_apotek.', 1)" data-toggle="tooltip" data-placement="top" title="Order">Order</span>';
                $btn .= '<span class="btn btn-primary btn-sm" onClick="set_status_defecta('.$data->id.', '.$data->id_apotek.', 2)" data-toggle="tooltip" data-placement="top" title="Transfer">Transfer</span>';
            } else if($data->id_status == 1){
                $btn .= '<span class="btn btn-danger btn-sm" onClick="set_status_defecta('.$data->id.', '.$data->id_apotek.', 0)" data-toggle="tooltip" data-placement="top" title="Batal Order">Batal Order</span>';
            } else if($data->id_status == 2) {
                $btn .= '<span class="btn btn-danger btn-sm" onClick="set_status_defecta('.$data->id.', '.$data->id_apotek.', 0)" data-toggle="tooltip" data-placement="top" title="Batal Transfer">Batal Transfer</span>';
            } else if($data->id_status == 3) {
                $btn .= '<span class="btn btn-danger btn-sm" onClick="set_status_defecta('.$data->id.', '.$data->id_apotek.', 0)" data-toggle="tooltip" data-placement="top" title="Batal Tolak">Batal Tolak</span>';
            } else {
                $btn .= '<span class="text-info"><i class="fa fa-fw fa-info"></i>-</span>';
            }
            $btn .='</div>';
            return $btn;
        })    
        ->setRowClass(function ($data) use ($apoteks) {
            $ada_ = 0;
            $i = 0;
            foreach($apoteks as $obj) {
                $i++;
                $inisial = strtolower($obj->nama_singkat);
                $cek_ = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $data->id_obat)->first();
                if($cek_->stok_akhir > $data->total_stok) {
                    $ada_ = 1;
                }
            }

            if($ada_ == 1){
                return 'bg-secondary disabled color-palette';
            } else {
                return '';
            }
        })  
        ->rawColumns(['checkList', 'total_stok', 'total_buffer', 'forcasting', 'action', 'DT_RowIndex', 'status', 'apotek', 'nama', 'suplier', 'id_satuan', 'jumlah_penjualan', 'margin'])
        ->addIndexColumn()
        ->with([
                'btn_set' => $btn_set,
            ])
        ->make(true);  
    }

    public function set_apotek_purchasing_aktif(Request $request) {
        session(['apotek_purchasing_aktif'=>$request->id_apotek]);
        echo 1;
    }

    public function set_status_purchasing_aktif(Request $request) {
        session(['status_purchasing_aktif'=>$request->id_status]);
        echo 1;
    }

    public function set_proses_purchasing_aktif(Request $request) {
        session(['proses_purchasing_aktif'=>$request->id_proses]);
        echo 1;
    }

    public function set_status_defecta(Request $request){
        $act = $request->input('act');
        $id_apotek = $request->input('id_apotek');
        $id_defecta = $request->input('id_defecta');

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

        $status = MasterStatusOrder::find($act);

        if($act == 0) {
            return view('konfirmasi_defecta._form_konfirmasi_draft')->with(compact('defectas', 'status'));
        } else if($act == 1) {
            $supliers = MasterSuplier::where('is_deleted', 0)->pluck('nama', 'id');
            $supliers->prepend('-- Pilih Suplier --','');

            return view('konfirmasi_defecta._form_konfirmasi_order')->with(compact('defectas', 'status', 'supliers'));
        } else if($act == 2){

            $apoteks = MasterApotek::whereNotIn('id', [session('id_apotek_active')])->where('is_deleted', 0)->pluck('nama_panjang', 'id');
            $apoteks->prepend('-- Pilih Apotek --','');
            return view('konfirmasi_defecta._form_konfirmasi_transfer')->with(compact('defectas', 'status', 'apoteks'));
        } else if($act == 3) {
            return view('konfirmasi_defecta._form_konfirmasi_tolak')->with(compact('defectas', 'status'));
        } else {
            echo "action yang anda pilih tidak sesuai.";
        }
    }

    public function set_status_defecta_back(Request $request)
    {
        $i = 0;
        foreach ($request->input('id_defecta') as $key => $value) {
            DB::table('tb_defecta_outlet')->where('id', $value)->update(['id_status'=> $request->input('act')]);
            $i++;
        }

        if($i> 0){
            if($request->input('act') == 0) {
                $message = 'obat yang telah dipilih berhasil dikembali ke status draft (belum ada keputusan).';
            } else if($request->input('act') == 1) {
                $message = 'obat yang telah dipilih berhasil diset ke status order.';
            } else if($request->input('act') == 2) {
                $message = 'obat yang telah dipilih berhasil diset ke status transfer.';
            } else if($request->input('act') == 3) {
                $message = 'obat yang telah dipilih berhasil diset ke status tolak.';
            }
            return response()->json(array(
                'submit' => 1,
                'message' => $message,
            ));
        }
        else{
            return response()->json(array(
                'submit' => 0,
                'message' => 'Setting status gagal'
            ));
        }
    }

    public function konfirmasi_order(Request $request) {
        $defectas = $request->defecta;
        $i = 0;
        foreach ($defectas as $key => $val) {
            DB::table('tb_defecta_outlet')->where('id', $val)->update([
                'id_suplier_order'=> $request->id_suplier_order, 
                'id_status' => $request->id_status, 
                'last_update_status' => date('Y-m-d H:i:s')
            ]);
            $i++;
        }

        if($i > 0) {
            return response()->json(array(
                'submit' => 1,
            ));
        }
        else{
            return response()->json(array(
                'submit' => 0,
            ));
        }
    }

    public function konfirmasi_transfer(Request $request) {
        $defectas = $request->defecta;
        $i = 0;
        foreach ($defectas as $key => $val) {
            DB::table('tb_defecta_outlet')->where('id', $val)->update([
                'id_apotek_transfer'=> $request->id_apotek_transfer, 
                'id_status' => $request->id_status, 
                'last_update_status' => date('Y-m-d H:i:s')
            ]);
            $i++;
        }

        if($i > 0) {
            return response()->json(array(
                'submit' => 1,
            ));
        }
        else{
            return response()->json(array(
                'submit' => 0,
            ));
        }
    }

    public function konfirmasi_tolak(Request $request) {
        $defectas = $request->defecta;
        $i = 0;
        foreach ($defectas as $key => $val) {
            DB::table('tb_defecta_outlet')->where('id', $val)->update([
                'alasan_tolak'=> $request->alasan_tolak, 
                'id_status' => $request->id_status, 
                'last_update_status' => date('Y-m-d H:i:s')
            ]);
            $i++;
        }

        if($i > 0) {
            return response()->json(array(
                'submit' => 1,
            ));
        }
        else{
            return response()->json(array(
                'submit' => 0,
            ));
        }
    }

    public function konfirmasi_draft(Request $request) {
        $defectas = $request->id_defecta;
        $i = 0;
        foreach ($defectas as $key => $val) {
            DB::table('tb_defecta_outlet')->where('id', $val)->update([
                'id_status' => $request->act, 
                'last_update_status' => date('Y-m-d H:i:s')
            ]);
            $i++;
        }

        if($i > 0) {
            return response()->json(array(
                'submit' => 1,
            ));
        }
        else{
            return response()->json(array(
                'submit' => 0,
            ));
        }
    }

    public function getAnalisaPembelian() {
        //echo "sementara ditutup, sampai so selesai";exit();
        $first_day = date('Y-m-d');
        //return view('page_not_maintenance');
        return view('defecta.analisa_pembelian')->with(compact('first_day'));
    }

    /*
        =======================================================================================
        For     : get amount of each category's
        Author  : Anang Bagus Prakoso
        Date    : 26/06/2023
        =======================================================================================
    */
    public function getAmountProduct(Request $request) {
        //echo "sementara ditutup, sampai so selesai";exit();
        ini_set('memory_limit', '-1');
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $cached_resume = Cache::get('analisa_pembelian_'.$request->referensi.'_'.Auth::user()->id.'_resume_'.$apotek->id);
        if($cached_resume == null){
            $this->getDataAnalisaPembelian($request);
            $data = Cache::get('analisa_pembelian_'.$request->referensi.'_'.Auth::user()->id.'_list_data_'.$apotek->id);
    
            $client = new Client(['base_uri' => env('API_ADDRESS', 'http://127.0.0.1:5000/api/')]);
    
            $queryString = json_encode([
                'data' => $data
            ]);
    
            $apiResponse = $client->get('count_status', [
                'json' => $queryString
            ]);
    
            $response = json_decode($apiResponse->getBody());
            $collection = collect($response);
            foreach ($collection as $data) {
                if ($data->status === 'Overstok') {
                    $overstok = $data->count;
                }
                if ($data->status === 'Understok') {
                    $understok = $data->count;
                }
                if ($data->status === 'Potensial Loss') {
                    $potensialLoss = $data->count;
                }
                if ($data->status === 'Stock Off') {
                    $stockOff = $data->count;
                }
                if ($data->status === 'Dead Stok') {
                    $deadStok = $data->count;
                }
                if ($data->status === 'Stok On Hand') {
                    $stokOnHand = $data->count;
                }
            }
    
            $vars = ['overstok', 'understok', 'potensialLoss', 'stockOff', 'deadStok', 'stokOnHand'];
            $datas = (object) [];
    
            foreach ($vars as $var) {
                $datas->$var = isset($$var) ? $$var : 0;
            }
    
            Cache::forget('analisa_pembelian_'.$request->referensi.'_'.Auth::user()->id.'_resume_'.$apotek->id);
            Cache::put('analisa_pembelian_'.$request->referensi.'_'.Auth::user()->id.'_resume_'.$apotek->id, $datas, self::$expiredAt);
        }
        else{
            $datas = $cached_resume;
        }

        return $datas;
    }

    public function getDataAnalisaPembelian(Request $request) {
        set_time_limit(0);
        ini_set('memory_limit', '-1'); 
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $cached_data = Cache::get('analisa_pembelian_'.$request->referensi.'_'.Auth::user()->id.'_list_data_'.$apotek->id);
        if($cached_data != null){
            $nama = $request->nama;
            $status = '';
            if($request->status == 1){
                $status = 'Overstok';
            }
            elseif($request->status == 2){
                $status = 'Understok';
            }
            elseif($request->status == 3){
                $status = 'Potensial Loss';
            }
            elseif($request->status == 4){
                $status = 'Stock Off';
            }
            elseif($request->status == 5){
                $status = 'Dead Stok';
            }
            elseif($request->status == 6){
                $status = 'Stok On Hand';
            }
            
            if($request->status or $request->nama){
                $collection = $cached_data->filter(function ($obat) use($status, $nama){
                    $result = true;
                    if($status != ''){
                        if($obat->status != $status){
                            $result = false;
                        }
                    }
                    if(!is_null($nama)){
                        if(stripos($obat->nama_obat, $nama) === false){
                            $result = false;
                        }
                    }
                    return $result;
                });
            }
            else{
                $collection = $cached_data;
            }
        }
        else{
            $inisial = strtolower($apotek->nama_singkat);
    
            DB::statement(DB::raw('set @rownum = 0'));
            $sub2 = TransaksiPenjualanDetail::select([
                'a.id',
                DB::raw('SUM(tb_detail_nota_penjualan.jumlah) as terjual'),
                DB::raw('(TRUNCATE
                    ((CASE
                        WHEN "'.$request->referensi.'" = "" THEN 0
                        WHEN "'.$request->referensi.'" = 1 THEN (SUM(tb_detail_nota_penjualan.jumlah)/1)
                        WHEN "'.$request->referensi.'" = 2 THEN (SUM(tb_detail_nota_penjualan.jumlah)/3)
                        WHEN "'.$request->referensi.'" = 3 THEN (SUM(tb_detail_nota_penjualan.jumlah)/6)
                        WHEN "'.$request->referensi.'" = 4 THEN (SUM(tb_detail_nota_penjualan.jumlah)/12)
                        ELSE 0
                    END), 0) + 1) AS kebutuhan'),
            ])
            ->leftJoin('tb_m_obat as a', 'a.id', '=', 'tb_detail_nota_penjualan.id_obat')
            ->leftjoin('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')
            ->where(function($query) use($request, $apotek){
                $query->where('a.is_deleted', 0);
                $query->where('b.is_deleted', 0);
                $query->where('tb_detail_nota_penjualan.is_deleted', 0);
    
                $tgl_akhir = date('Y-m-d');
                if($request->referensi !="") {
                    if($request->referensi == 1) {
                        $tgl_awal = date('Y-m-01');
                    } else if($request->referensi == 2) {
                        $tgl_awal = date('Y-m-01', strtotime("-2 month"));
                    } else if($request->referensi == 3) {
                        $tgl_awal = date('Y-m-01', strtotime("-5 month"));
                    } else if($request->referensi == 4) {
                        $tgl_awal = date('Y-m-01', strtotime("-11 month"));
                    } 
                }
    
                $query->where('b.id_apotek_nota','=',$apotek->id);
                $query->whereDate('b.tgl_nota','>=', $tgl_awal);
                $query->whereDate('b.tgl_nota','<=', $tgl_akhir);
    
            })
            ->groupBy('a.id');
    
            $sub3 = DB::table( 'tb_m_obat as a' )
                ->select([
                    DB::raw('@rownum := @rownum + 1 AS no'),
                    'a.nama as nama_obat',
                    'a.id as id_obat',
                    'a.id_satuan',
                    'a.id_produsen',
                    'b.satuan',
                    'c.nama as produsen',
                    'd.stok_akhir as stok_obat',
                    'd.harga_beli_ppn',
                    'sub2.terjual as terjual',
                    DB::raw('IFNULL(sub2.kebutuhan, 0) as kebutuhan'),
                ])
                ->leftjoin('tb_m_satuan as b','b.id','=','a.id_satuan')
                ->leftjoin('tb_m_produsen as c','c.id','=','a.id_produsen')
                ->leftjoin('tb_m_stok_harga_'.$inisial.' as d', 'd.id_obat', '=', 'a.id')
                ->leftjoin(DB::raw("({$sub2->toSql()}) as sub2"), 'sub2.id', '=', 'a.id')
                ->mergeBindings($sub2->getQuery())
                ->where('a.is_deleted', 0)
                ->where('b.is_deleted', 0)
                ->where('c.is_deleted', 0)
                ->where('d.is_deleted', 0);
    
            $data = DB::table( DB::raw("({$sub3->toSql()}) as sub3") )
                ->mergeBindings($sub3) 
                ->select(['sub3.*']);
    
            // ambil data obat yg di defecta
            $data_defecta = DefectaOutlet::select('id_obat')
                ->where('is_deleted', 0)
                ->where('id_apotek', $apotek->id)
                ->whereNotIn('id_status', [1,2])
                ->where('id_process', '=', 0);
            
            // ambil data penjualan per bulan
            $query_histori_penjualan = TransaksiPenjualanDetail::select([
                'a.id',
                DB::raw('SUM(tb_detail_nota_penjualan.jumlah) as terjual'),
            ])
            ->leftJoin('tb_m_obat as a', 'a.id', '=', 'tb_detail_nota_penjualan.id_obat')
            ->leftjoin('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')
            ->where(function($query) use($apotek){
                $query->where('a.is_deleted', 0);
                $query->where('b.is_deleted', 0);
                $query->where('tb_detail_nota_penjualan.is_deleted', 0);
                $query->where('b.id_apotek_nota', '=', $apotek->id);
            })
            ->groupBy('a.id');
    
            // ambil data stok akhir per bulan
            $query_histori_stok = DB::table('tb_histori_stok_'.$inisial)
                ->select([
                    'id_obat',
                    DB::raw('cast(stok_awal as SIGNED) as stok_awal'),
                    'created_at'
                ])
                ->groupBy('id_obat');
    
            // ambil data pembelian per bulan
            $query_histori_pembelian = TransaksiPembelianDetail::select([
                'a.id',
                DB::raw('SUM(tb_detail_nota_pembelian.jumlah) as pembelian'),
            ])
            ->leftJoin('tb_m_obat as a', 'a.id', '=', 'tb_detail_nota_pembelian.id_obat')
            ->leftjoin('tb_nota_pembelian as b','b.id','=','tb_detail_nota_pembelian.id_nota')
            ->where(function($query) use($apotek){
                $query->where('a.is_deleted', 0);
                $query->where('b.is_deleted', 0);
                $query->where('tb_detail_nota_pembelian.is_deleted', 0);
                $query->where('b.id_apotek_nota', '=', $apotek->id);
            })
            ->groupBy('a.id');
    
            $query_histori_transfer_keluar = TransaksiTODetail::select([
                'a.id',
                DB::raw('SUM(tb_detail_nota_transfer_outlet.jumlah) as total_transfer'),
            ])
            ->leftjoin('tb_m_obat as a', 'a.id', '=', 'tb_detail_nota_transfer_outlet.id_obat')
            ->leftjoin('tb_nota_transfer_outlet as b', 'b.id', '=', 'tb_detail_nota_transfer_outlet.id_nota')
            ->where(function($query) use($apotek){
                $query->where('a.is_deleted', 0);
                $query->where('b.is_deleted', 0);
                $query->where('tb_detail_nota_transfer_outlet.is_deleted', 0);
                $query->where('b.id_apotek_nota', '=', $apotek->id);
            })
            ->groupBy('a.id');
    
            $query_histori_transfer_masuk = TransaksiTODetail::select([
                'a.id',
                DB::raw('SUM(tb_detail_nota_transfer_outlet.jumlah) as total_transfer'),
            ])
            ->leftjoin('tb_m_obat as a', 'a.id', '=', 'tb_detail_nota_transfer_outlet.id_obat')
            ->leftjoin('tb_nota_transfer_outlet as b', 'b.id', '=', 'tb_detail_nota_transfer_outlet.id_nota')
            ->where(function($query) use($apotek){
                $query->where('a.is_deleted', 0);
                $query->where('b.is_deleted', 0);
                $query->where('tb_detail_nota_transfer_outlet.is_deleted', 0);
                $query->where('b.id_apotek_tujuan', '=', $apotek->id);
            })
            ->groupBy('a.id');
    
            for ($i=0; $i<12; $i++){
                $j = $i + 1;
                // cloning query
                $histori_penjualan[$i] = clone $query_histori_penjualan;
                $histori_stok[$i] = clone $query_histori_stok;
                $histori_pembelian[$i] = clone $query_histori_pembelian;
                $histori_transfer_keluar[$i] = clone $query_histori_transfer_keluar;
                $histori_transfer_masuk[$i] = clone $query_histori_transfer_masuk;
                
                // set date
                $histori_penjualan[$i] = $histori_penjualan[$i]->where(function($query) use($j){
                    $query->whereDate('b.tgl_nota', '>=', date('Y-m-01', strtotime("-{$j} month")));
                    $query->whereDate('b.tgl_nota', '<=', date('Y-m-t', strtotime("-{$j} month")));
                });
                if($i == 0){
                    $histori_stok[$i] = $histori_stok[$i]->where(function($query) use($i){
                        $query->whereDate('created_at', '>=', date('Y-m-01'));
                        $query->whereDate('created_at', '<=', date('Y-m-d'));
                    });
                }
                else{
                    $histori_stok[$i] = $histori_stok[$i]->where(function($query) use($i){
                        $query->whereDate('created_at', '>=', date('Y-m-01', strtotime("-{$i} month")));
                        $query->whereDate('created_at', '<=', date('Y-m-t', strtotime("-{$i} month")));
                    });
                }
                $histori_pembelian[$i] = $histori_pembelian[$i]->where(function($query) use($j){
                    $query->whereDate('b.tgl_nota', '>=', date('Y-m-01', strtotime("-{$j} month")));
                    $query->whereDate('b.tgl_nota', '<=', date('Y-m-t', strtotime("-{$j} month")));
                });
                $histori_transfer_keluar[$i] = $histori_transfer_keluar[$i]->where(function($query) use($j){
                    $query->whereDate('b.tgl_nota', '>=', date('Y-m-01', strtotime("-{$j} month")));
                    $query->whereDate('b.tgl_nota', '<=', date('Y-m-t', strtotime("-{$j} month")));
                });
                $histori_transfer_masuk[$i] = $histori_transfer_masuk[$i]->where(function($query) use($j){
                    $query->whereDate('b.tgl_nota', '>=', date('Y-m-01', strtotime("-{$j} month")));
                    $query->whereDate('b.tgl_nota', '<=', date('Y-m-t', strtotime("-{$j} month")));
                });
    
                // merge dengan tb obat
                $histori_penjualan[$i] = DB::table( 'tb_m_obat as a' )
                ->select([
                    'a.id as id_obat',
                    'sub2.terjual as terjual'
                ])
                ->leftjoin('tb_m_satuan as b','b.id','=','a.id_satuan')
                ->leftjoin('tb_m_produsen as c','c.id','=','a.id_produsen')
                ->leftjoin('tb_m_stok_harga_'.$inisial.' as d', 'd.id_obat', '=', 'a.id')
                ->leftjoin(DB::raw("({$histori_penjualan[$i]->toSql()}) as sub2"), 'sub2.id', '=', 'a.id')
                ->mergeBindings($histori_penjualan[$i]->getQuery())
                ->where('a.is_deleted', 0)
                ->where('b.is_deleted', 0)
                ->where('c.is_deleted', 0)
                ->where('d.is_deleted', 0)
                ->get();
    
                $histori_stok[$i] = DB::table('tb_m_obat as a')
                ->select([
                    'a.id as id_obat',
                    'sub2.stok_awal as stok_akhir',
                    'sub2.created_at'
                ])
                ->leftjoin('tb_m_satuan as b','b.id','=','a.id_satuan')
                ->leftjoin('tb_m_produsen as c','c.id','=','a.id_produsen')
                ->leftjoin('tb_m_stok_harga_'.$inisial.' as d', 'd.id_obat', '=', 'a.id')
                ->leftJoinSub($histori_stok[$i], 'sub2', function($join) {
                    $join->on('sub2.id_obat', '=', 'a.id');
                })
                ->where('a.is_deleted', 0)
                ->where('b.is_deleted', 0)
                ->where('c.is_deleted', 0)
                ->where('d.is_deleted', 0)
                ->get();
    
                $histori_pembelian[$i] = DB::table('tb_m_obat as a')
                ->select([
                    'a.id as id_obat',
                    'sub2.pembelian as pembelian'
                ])
                ->leftjoin('tb_m_satuan as b','b.id','=','a.id_satuan')
                ->leftjoin('tb_m_produsen as c','c.id','=','a.id_produsen')
                ->leftjoin('tb_m_stok_harga_'.$inisial.' as d', 'd.id_obat', '=', 'a.id')
                ->leftjoin(DB::raw("({$histori_pembelian[$i]->toSql()}) as sub2"), 'sub2.id', '=', 'a.id')
                ->mergeBindings($histori_pembelian[$i]->getQuery())
                ->where('a.is_deleted', 0)
                ->where('b.is_deleted', 0)
                ->where('c.is_deleted', 0)
                ->where('d.is_deleted', 0)
                ->get();
    
                $histori_transfer_keluar[$i] = DB::table('tb_m_obat as a')
                ->select([
                    'a.id as id_obat',
                    'sub2.total_transfer as total_transfer'
                ])
                ->leftjoin('tb_m_satuan as b','b.id','=','a.id_satuan')
                ->leftjoin('tb_m_produsen as c','c.id','=','a.id_produsen')
                ->leftjoin('tb_m_stok_harga_'.$inisial.' as d', 'd.id_obat', '=', 'a.id')
                ->leftjoin(DB::raw("({$histori_transfer_keluar[$i]->toSql()}) as sub2"), 'sub2.id', '=', 'a.id')
                ->mergeBindings($histori_transfer_keluar[$i]->getQuery())
                ->where('a.is_deleted', 0)
                ->where('b.is_deleted', 0)
                ->where('c.is_deleted', 0)
                ->where('d.is_deleted', 0)
                ->get();
    
                $histori_transfer_masuk[$i] = DB::table('tb_m_obat as a')
                ->select([
                    'a.id as id_obat',
                    'sub2.total_transfer as total_transfer'
                ])
                ->leftjoin('tb_m_satuan as b','b.id','=','a.id_satuan')
                ->leftjoin('tb_m_produsen as c','c.id','=','a.id_produsen')
                ->leftjoin('tb_m_stok_harga_'.$inisial.' as d', 'd.id_obat', '=', 'a.id')
                ->leftjoin(DB::raw("({$histori_transfer_masuk[$i]->toSql()}) as sub2"), 'sub2.id', '=', 'a.id')
                ->mergeBindings($histori_transfer_masuk[$i]->getQuery())
                ->where('a.is_deleted', 0)
                ->where('b.is_deleted', 0)
                ->where('c.is_deleted', 0)
                ->where('d.is_deleted', 0)
                ->get();
            }
    
            $client = new Client(['base_uri' => env('API_ADDRESS', 'http://127.0.0.1:5000/api/')]);
    
            $queryString = json_encode([
                'data' => $data->get(),
                'data_defecta' => $data_defecta->get(),
                'histori_penjualan' => $histori_penjualan,
                'histori_stok' => $histori_stok,
                'histori_pembelian' => $histori_pembelian,
                'histori_transfer_keluar' => $histori_transfer_keluar,
                'histori_transfer_masuk' => $histori_transfer_masuk,
                'status' => $request->status,
                'nama' => $request->nama
            ]);
    
            $apiResponse = $client->get('data', [
                'json' => $queryString
            ]);
    
            $response = json_decode($apiResponse->getBody());
            $list_data = collect($response[1]);
            $collection = collect($response[0]);
    
            Cache::forget('analisa_pembelian_'.$request->referensi.'_'.Auth::user()->id.'_list_data_'.$apotek->id);
            Cache::put('analisa_pembelian_'.$request->referensi.'_'.Auth::user()->id.'_list_data_'.$apotek->id, $list_data, self::$expiredAt);
        }

        $datatables = Datatables::of($collection->slice($request->start, $request->length));
        return $datatables
        ->editcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
                $btn .= '<span class="btn btn-info btn-sm" onClick="add_keranjang('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Add Keranjang Defecta">[order]</span>';
                $btn .= '<span class="btn btn-info btn-sm" onClick="add_keranjang_transfer('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Add Keranjang Permintaan Transfer">[transfer]</span>';
            $btn .='</div>';
            return $btn;
        })    
        ->addColumn('sedang_dipesan', function($data) {
            $cek = DefectaOutlet::where('is_deleted', 0)
                        ->where('id_obat', $data->id)
                        ->where('id_apotek', session('id_apotek_active'))
                        ->whereIn('id_status', [1,2])
                        ->where('id_process', '=', 0)
                        ->first();

            $status = '<span class="label label-danger" data-toggle="tooltip" data-placement="top" title="Belum masuk defecta" style="font-size:8pt;color:#e91e63;"><i class="fa fa-window-close" style="font-size:24px" aria-hidden="true"></i></span>';
            if(!empty($cek)) {
                $status = '<span class="label label-success" data-toggle="tooltip" data-placement="top" title="Sudah masuk defecta" style="font-size:8pt;color:#009688;"><i class="fa fa-check-circle" style="font-size:24px" aria-hidden="true"></i></span>';
            } 

            return $status; 
        })    
        ->setRowClass(function ($data) {
            if($data->status == "Overstok") {
                return 'bg-maroon color-palette';
            } else if($data->status == "Understok") {
                return 'bg-warning color-palette';
            } else if($data->status == "Potensial Loss") {
                return 'bg-danger color-palette';
            } else if($data->status == "Dead Stok") {
                return 'bg-gray color-palette';
            } else {
                return '';
            }
        })  
        ->rawColumns(['sedang_dipesan', 'action'])
        ->addIndexColumn()
        ->setTotalRecords($collection->count())
        ->skipPaging()
        ->make(true);  
    }

    public function clear_cache() {
        ini_set('memory_limit', '-1');
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $value = [null, 1, 2, 3, 4];
        try {
            for($i = 0; $i < count($value); $i++){
                Cache::forget('analisa_pembelian_'.$value[$i].'_'.Auth::user()->id.'_resume_'.$apotek->id);
                Cache::forget('analisa_pembelian_'.$value[$i].'_'.Auth::user()->id.'_list_data_'.$apotek->id);
            }
            return response()->json(['message' => 'success clear cache']);
        } catch (Exception $e) {
            return response()->json(['error' => $e], 500);
        }
    }

    public function add_keranjang(Request $request){
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $data_ = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $request->id_obat)->first();
        $obat = MasterObat::find($request->id_obat);
        $defecta = DefectaOutlet::where('is_deleted', 0)
                    ->where('id_obat', $request->id_obat)
                    ->where('id_apotek', $apotek->id)
                    ->where('id_status', '=', 1)
                    ->where('id_process', '!=', 1)
                    ->first();

        if(!empty($defecta)) {
        } else {
            $defecta = new DefectaOutlet;
        }


        $setting_suplier = MasterSettingSuplier::select(['id_suplier'])->where('is_deleted', 0)->where('id_obat', $obat->id)->orderby('level', 'asc')->get();
      
        if(!empty($setting_suplier)) {
            $suplier = new MasterSuplier;
            $setting_suplier->push('155');
            $supliers      = MasterSuplier::whereIn('id', $setting_suplier)->where('is_deleted', 0)->pluck('nama', 'id');
            $supliers->prepend('-- Pilih Suplier --','');
        } else {
            $suplier = new MasterSuplier;
            $supliers = MasterSuplier::where('is_deleted', 0)->pluck('nama', 'id');
            $supliers->prepend('-- Pilih Suplier --','');
        }

        $satuans      = MasterSatuan::where('is_deleted', 0)->pluck('satuan', 'id');
        $satuans->prepend('-- Pilih Satuan --','');


        $histori_stok = HistoriStok::select([DB::raw('SUM(sisa_stok) as jum_sisa_stok'), DB::raw('SUM(sisa_stok*hb_ppn) as total')])
                        ->where('id_obat', $obat->id)
                        ->whereIn('id_jenis_transaksi', [2,3,11,9])
                        ->where('sisa_stok', '>', 0)
                        ->orderBy('id', 'ASC')
                        ->first();

        $last = HistoriStok::select(['*'])
                        ->where('id_obat', $obat->id)
                        ->whereIn('id_jenis_transaksi', [2,3,11,9])
                        ->where('sisa_stok', '>', 0)
                        ->orderBy('id', 'DESC')
                        ->first();

        $suplier_ref = DB::table('tb_detail_nota_pembelian as a')
        ->select(['a.*', 'b.id_suplier'])
        ->join('tb_nota_pembelian as b', 'b.id', '=', 'a.id_nota')
        ->where('a.is_deleted', 0)
        ->where('b.is_deleted', 0)
        ->where('b.id_apotek_nota', session('id_apotek_active'))
        ->where('a.id_obat', $request->id_obat)
        ->groupBy('b.id_suplier')
        ->get();

        $referensi = [];
        $referensis = [];
        if(count($suplier_ref) > 0) {
            foreach($suplier_ref as $obj) {
                $getData = DB::table('tb_detail_nota_pembelian as a')
                ->select(['a.*', 'c.nama as nama', 'b.tgl_nota'])
                ->join('tb_nota_pembelian as b', 'b.id', '=', 'a.id_nota')
                ->join('tb_m_suplier as c', 'c.id','=', 'b.id_suplier')
                ->where('a.is_deleted', 0)
                ->where('b.is_deleted', 0)
                ->where('b.id_apotek_nota', session('id_apotek_active'))
                ->where('a.id_obat', $request->id_obat)
                ->where('b.id_suplier', $obj->id_suplier)
                ->orderBy('b.tgl_nota', 'DESC')
                ->first();

                array_push($referensi, $getData);
            }
        } else {
            $suplier_ref = DB::table('tb_detail_nota_pembelian as a')
            ->select(['a.*', 'b.id_suplier'])
            ->join('tb_nota_pembelian as b', 'b.id', '=', 'a.id_nota')
            ->where('a.is_deleted', 0)
            ->where('b.is_deleted', 0)
            ->where('a.id_obat', $request->id_obat)
            ->groupBy('b.id_suplier')
            ->get();

            foreach($suplier_ref as $obj) {
                $getData = DB::table('tb_detail_nota_pembelian as a')
                ->select(['a.*', 'c.nama as nama', 'b.tgl_nota'])
                ->join('tb_nota_pembelian as b', 'b.id', '=', 'a.id_nota')
                ->join('tb_m_suplier as c', 'c.id','=', 'b.id_suplier')
                ->where('a.is_deleted', 0)
                ->where('b.is_deleted', 0)
                ->where('a.id_obat', $request->id_obat)
                ->where('b.id_suplier', $obj->id_suplier)
                ->orderBy('b.tgl_nota', 'DESC')
                ->first();

                array_push($referensis, $getData);
            }
        }
    
        return view('homepage._form_add_defecta')->with(compact('defecta', 'apotek', 'obat', 'data_', 'suplier', 'satuans', 'last', 'supliers', 'referensi', 'referensis'));
    }

    public function add_keranjang_manual(Request $request){
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $defecta = new DefectaOutlet;

        $satuans      = MasterSatuan::where('is_deleted', 0)->pluck('satuan', 'id');
        $satuans->prepend('-- Pilih Satuan --','');

        $obats      = MasterObat::where('is_deleted', 0)->pluck('nama', 'id');
        $obats->prepend('-- Pilih Obat --','');

        return view('homepage._form_add_defecta_manual')->with(compact('defecta', 'apotek', 'satuans', 'obats'));
    }

    public function load_konten(Request $request) {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);

        $id_obat = $request->id_obat;
        $id_defecta = $request->id_defecta;
        if(is_null($id_defecta) OR $id_defecta == "") {
            $defecta = new DefectaOutlet;
        } else {
            $defecta = DefectaOutlet::find($id_defecta);
        }

        $setting_suplier = MasterSettingSuplier::select(['id_suplier'])->where('is_deleted', 0)->where('id_obat', $id_obat)->orderby('level', 'asc')->get();

        if(count($setting_suplier) > 0) {
            $suplier = new MasterSuplier;
            $setting_suplier->push('155');
            $supliers      = MasterSuplier::whereIn('id', $setting_suplier)->where('is_deleted', 0)->get();
        } else {
            $suplier = new MasterSuplier;
            $supliers = MasterSuplier::where('is_deleted', 0)->get();
        }

        $html_sup = '<label>Pilih Suplier</label>';
        $html_sup .= '<select id="id_suplier_order" name="id_suplier_order" class="form-control input_select required">';
        $html_sup .= '<option value="">- Pilih Suplier -</option>';
        foreach($supliers as $obj) {
            $html_sup .= '<option value="'.$obj->id.'">'.$obj->nama.'</option>';
        }
        $html_sup .= "</select>";

        $histori_stok = HistoriStok::select([DB::raw('SUM(sisa_stok) as jum_sisa_stok'), DB::raw('SUM(sisa_stok*hb_ppn) as total')])
                        ->where('id_obat', $id_obat)
                        ->whereIn('id_jenis_transaksi', [2,3,11,9])
                        ->where('sisa_stok', '>', 0)
                        ->orderBy('id', 'ASC')
                        ->first();

        $last = HistoriStok::select(['*'])
                        ->where('id_obat', $id_obat)
                        ->whereIn('id_jenis_transaksi', [2,3,11,9])
                        ->where('sisa_stok', '>', 0)
                        ->orderBy('id', 'DESC')
                        ->first();

        /*$latestNotes = DB::table('tb_nota_pembelian as a')
            ->select('a.id', DB::raw('CAST(a.id_suplier as unsigned) as id_suplier'), DB::raw('MAX(a.tgl_nota) as latest_date'))
            ->where('a.id_apotek_nota', $apotek->id)
            ->where('a.is_deleted', 0)
            ->groupBy('a.id_suplier')
            ->orderBy('id_suplier');
        
        $referensi = DB::table('tb_detail_nota_pembelian as a')
            ->select(
                'd.nama',
                DB::raw('CAST(b.id as unsigned) as id'),
                'b.tgl_nota',
                DB::raw('CAST(b.id_suplier as unsigned) as id_suplier'),
                DB::raw('CAST(a.id_obat as unsigned) as id_obat'),
                'harga_beli_ppn',
                'a.created_at'
            )
            ->join('tb_nota_pembelian as b', 'b.id', '=', 'a.id_nota')
            ->joinSub($latestNotes, 'c', function ($join) {
                $join->on('c.id_suplier', '=', 'b.id_suplier')
                    ->on('c.latest_date', '=', 'b.tgl_nota');
            })
            ->join('tb_m_suplier as d', 'd.id', '=', 'b.id_suplier')
            ->where('b.id_apotek_nota', $apotek->id)
            ->where('a.is_deleted', 0)
            ->where('b.is_deleted', 0)
            ->where('a.id_obat', $id_obat)
            ->get();

        
        if(count($referensi) != 0) {               
            $prevSuplierId = null;

            foreach($referensi as $data) {
                if($prevSuplierId !== $data->id_suplier) {
                    $latestCreatedAt = null;

                    foreach($referensi as $innerData) {
                        if($innerData->id_suplier === $data->id_suplier && (!$latestCreatedAt || $innerData->created_at > $latestCreatedAt)) {
                            $latestCreatedAt = $innerData->created_at;
                        }
                    }

                    
                    $html_ref .= '<label>'.$loop->iteration.'. '.$data->nama.':</label> Rp. '.intval($data->harga_beli_ppn).'<br>';
                    $prevSuplierId = $data->id_suplier;
                }
            }
        } else {
            $html_ref .='Belum ada rekaman pembelian';
        }

        */
        $html_ref = '';
        $referensis = DB::table('tb_detail_nota_pembelian as a')
                        ->select(['a.*', 'b.id_suplier'])
                        ->join('tb_nota_pembelian as b', 'b.id', '=', 'a.id_nota')
                        ->where('a.is_deleted', 0)
                        ->where('b.is_deleted', 0)
                        ->where('b.id_apotek_nota', session('id_apotek_active'))
                        ->where('a.id_obat', $id_obat)
                        ->groupBy('b.id_suplier')
                        ->get();

        if(count($referensis) > 0) {
            $i = 0;
            foreach($referensis as $obj) {
                $getData = DB::table('tb_detail_nota_pembelian as a')
                            ->select(['a.*', 'c.nama as suplier'])
                            ->join('tb_nota_pembelian as b', 'b.id', '=', 'a.id_nota')
                            ->join('tb_m_suplier as c', 'c.id','=', 'b.id_suplier')
                            ->where('a.is_deleted', 0)
                            ->where('b.is_deleted', 0)
                            ->where('b.id_apotek_nota', session('id_apotek_active'))
                            ->where('a.id_obat', $id_obat)
                            ->where('b.id_suplier', $obj->id_suplier)
                            ->orderBy('b.tgl_nota', 'DESC')
                            ->first();
                $i++;

                $html_ref .= '<label>'.$i.'. '.$getData->suplier.':</label> Rp. '.intval($getData->harga_beli_ppn).'<br>';
            }
        } else {
            $html_ref .='<span class="text-danger"><b>Belum ada rekaman pembelian</b></span><br>';
            $referensis = DB::table('tb_detail_nota_pembelian as a')
                        ->select(['a.*', 'b.id_suplier'])
                        ->join('tb_nota_pembelian as b', 'b.id', '=', 'a.id_nota')
                        ->where('a.is_deleted', 0)
                        ->where('b.is_deleted', 0)
                        ->where('a.id_obat', $id_obat)
                        ->groupBy('b.id_suplier')
                        ->get();

            $i = 0;
            foreach($referensis as $obj) {
                $getData = DB::table('tb_detail_nota_pembelian as a')
                            ->select(['a.*', 'c.nama as suplier'])
                            ->join('tb_nota_pembelian as b', 'b.id', '=', 'a.id_nota')
                            ->join('tb_m_suplier as c', 'c.id','=', 'b.id_suplier')
                            ->where('a.is_deleted', 0)
                            ->where('b.is_deleted', 0)
                            ->where('a.id_obat', $id_obat)
                            ->where('b.id_suplier', $obj->id_suplier)
                            ->orderBy('b.tgl_nota', 'DESC')
                            ->first();
                $i++;

                $html_ref .='<span class="text-info"><b>Pembelian Outlet Lain</b></span><br>';
                $html_ref .= '<label>'.$i.'. '.$getData->suplier.':</label> Rp. '.intval($getData->harga_beli_ppn).'<br>';
            }
        }
        
        return response()->json(array(
                'submit' => 1,
                'div_suplier' => $html_sup,
                'div_referensi' => $html_ref
            ));
    }

    public function add_keranjang_transfer(Request $request){
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $data_ = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $request->id_obat)->first();
        $obat = MasterObat::find($request->id_obat);
        $defecta = DefectaOutlet::where('is_deleted', 0)
                    ->where('id_obat', $request->id_obat)
                    ->where('id_apotek', $apotek->id)
                    ->where('id_status', '!=', 2)
                    ->where('id_process', '!=', 1)
                    ->first();

        if(!empty($defecta)) {
        } else {
            $defecta = new DefectaOutlet;
        }

        $apoteks      = MasterApotek::where('is_deleted', 0)->whereNotIn('id',[session('id_apotek_active')])->pluck('nama_singkat', 'id');

        $info = '';
        $i = 0;
        foreach($apoteks as $obj) {
            $i++;
            $inisial = strtolower($obj);
            $cek_ = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $request->id_obat)->first();
            $info .= $obj.' : '.$cek_->stok_akhir;
            if($i != count($apoteks)) {
                $info .= ' | ';
            }
        }
        $apoteks->prepend('-- Pilih Apotek --','');

        $satuans      = MasterSatuan::where('is_deleted', 0)->pluck('satuan', 'id');
        $satuans->prepend('-- Pilih Satuan --','');


        $histori_stok = HistoriStok::select([DB::raw('SUM(sisa_stok) as jum_sisa_stok'), DB::raw('SUM(sisa_stok*hb_ppn) as total')])
                        ->where('id_obat', $obat->id)
                        ->whereIn('id_jenis_transaksi', [2,3,11,9])
                        ->where('sisa_stok', '>', 0)
                        ->orderBy('id', 'ASC')
                        ->first();

        $last = HistoriStok::select(['*'])
                        ->where('id_obat', $obat->id)
                        ->whereIn('id_jenis_transaksi', [2,3,11,9])
                        ->where('sisa_stok', '>', 0)
                        ->orderBy('id', 'DESC')
                        ->first();
    
        return view('homepage._form_add_defecta_transfer')->with(compact('defecta', 'apotek', 'obat', 'data_', 'satuans', 'last', 'apoteks', 'info'));
    }

    public function add_keranjang_transfer_manual(Request $request){
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $defecta = new DefectaOutlet;

        $apoteks      = MasterApotek::where('is_deleted', 0)->whereNotIn('id',[session('id_apotek_active')])->pluck('nama_singkat', 'id');
        $apoteks->prepend('-- Pilih Apotek --','');

        $satuans      = MasterSatuan::where('is_deleted', 0)->pluck('satuan', 'id');
        $satuans->prepend('-- Pilih Satuan --','');

        $obats      = MasterObat::where('is_deleted', 0)->pluck('nama', 'id');
        $obats->prepend('-- Pilih Obat --','');
    
        return view('homepage._form_add_defecta_transfer_manual')->with(compact('defecta', 'apotek', 'satuans', 'apoteks', 'obats'));
    }

    public function load_konten_transfer(Request $request) {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);

        $id_obat = $request->id_obat;
        $id_defecta = $request->id_defecta;
        if(is_null($id_defecta) OR $id_defecta == "") {
            $defecta = new DefectaOutlet;
        } else {
            $defecta = DefectaOutlet::find($id_defecta);
        }

        $apoteks      = MasterApotek::where('is_deleted', 0)->whereNotIn('id',[session('id_apotek_active')])->get();
        $info = '';
        $i = 0;
        foreach($apoteks as $obj) {
            $i++;
            $inisialx = strtolower($obj->nama_singkat);
            $cek_ = DB::table('tb_m_stok_harga_'.$inisialx)->where('id_obat', $request->id_obat)->first();
            $info .= $obj->nama_singkat.' : '.$cek_->stok_akhir;
            if($i != count($apoteks)) {
                $info .= ' | ';
            }
        }

        $html = '<small>'.$info.'</small>';
        
        return response()->json(array(
                'submit' => 1,
                'div_info' => $html
            ));
    }

    public function export_analisa_pembelian(Request $request) 
    {
        ini_set('memory_limit', '-1');
        $id_apotek = session('id_apotek_active');
        $apotek = MasterApotek::find($id_apotek);
        $inisial = strtolower($apotek->nama_singkat);
        $now = date('YmdHis');
        $referensi = $request->referensi;

        return (new AnalisaPembelianExport($referensi, $id_apotek, $inisial))->download('Analisa_Pembelian_'.$inisial.'_'.$now.'.xlsx');
    }

    public function export_analisa_pembelian_all(Request $request) 
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        $id_apotek = session('id_apotek_active');
        $apoteks = MasterApotek::select(
            'id',
            'nama_singkat'
        )
        ->whereRaw('is_deleted = 0')
        ->get();
        $now = date('YmdHis');
        $referensi = $request->referensi;

        $zip = new ZipArchive();
        $zipFileName = 'Analisa_Pembelian_All_Outlet_'.$now.'.zip';

        if ($zip->open($zipFileName, ZipArchive::CREATE) === true) {
            foreach($apoteks as $apotek){
                $export = new AnalisaPembelianExport($referensi, $apotek->id, strtolower($apotek->nama_singkat));
                $fileName = 'Analisa_Pembelian_'.strtolower($apotek->nama_singkat).'_'.$now.'.xlsx';
                $path = Excel::store($export, $fileName);
                $zip->addFile(storage_path('app/'.$fileName), $fileName);
            }
            
            $zip->close();
            
            foreach($apoteks as $apotek){
                $fileName = 'Analisa_Pembelian_'.strtolower($apotek->nama_singkat).'_'.$now.'.xlsx';
                unlink(storage_path('app/'.$fileName));
            }

            return response()->download($zipFileName)->deleteFileAfterSend();
        } else {
            return 'Failed to create ZIP file.';
        }

        // return (new AnalisaPembelianExport($referensi, $id_apotek, $inisial))->download('Analisa_Pembelian_'.$inisial.'_'.$now.'.xlsx');
    }
}
