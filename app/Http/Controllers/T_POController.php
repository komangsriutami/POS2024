<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\TransaksiPO;
use App\TransaksiPODetail;
use App\MasterObat;
use App\MasterApotek;
use App\HistoriStok;
use App\MasterStokHarga;
use App\s;
use App\User;
use App;
use Datatables;
use DB;
use Auth;
use Excel;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Traits\DynamicConnectionTrait;

class T_POController extends Controller
{
    use DynamicConnectionTrait;
    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 07/11/2020
        =======================================================================================
    */
    public function index()
    {
        return view('obat_operasional.index');
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 07/11/2020
        =======================================================================================
    */
    public function list_obat_operasional(Request $request)
    {
        $apotek = MasterApotek::on($this->getConnectionName())->find(session('id_apotek_active'));
        $apoteker = User::on($this->getConnectionName())->find($apotek->id_apoteker);
        $id_user = Auth::user()->id;

        $hak_akses = 0;
        if($apoteker->id == $id_user) {
            $hak_akses = 1;
        }

        if($id_user == 1 || $id_user == 2 || $id_user == 16) {
            $hak_akses = 1;
        }

        $tanggal = date('Y-m-d');
        DB::connection($this->getConnection())->statement(DB::raw('set @rownum = 0'));
        $data = TransaksiPO::select([
                DB::raw('@rownum  := @rownum  + 1 AS no'),
	            'tb_nota_po.*', 
        ])
        ->where(function($query) use($request, $tanggal){
            $query->where('tb_nota_po.is_deleted','=','0');
            $query->where('tb_nota_po.id_apotek_nota','=',session('id_apotek_active'));
            $query->where('tb_nota_po.id','LIKE',($request->id > 0 ? $request->id : '%'.$request->id.'%'));
            if($request->tgl_awal != "") {
                $tgl_awal       = date('Y-m-d H:i:s',strtotime($request->tgl_awal));
                $query->whereDate('tb_nota_po.created_at','>=', $tgl_awal);
            }

            if($request->tgl_akhir != "") {
                $tgl_akhir      = date('Y-m-d H:i:s',strtotime($request->tgl_akhir));
                $query->whereDate('tb_nota_po.created_at','<=', $tgl_akhir);
            }
        });
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request){
            $query->where(function($query) use($request){
            });
        })
        ->editcolumn('created_by', function($data) {
            return '<small>'.$data->created_oleh->nama.'</small>';
        }) 
        ->editcolumn('is_sign', function($data){
            if($data->is_sign == 0) {
                return '<span class="label label-danger" data-toggle="tooltip" data-placement="top" title="Nota belum dicek atau dittd" style="font-size:8pt;color:#e91e63;">Belum diTTD</span>';
            } else {
                return '<span class="label label-success" data-toggle="tooltip" data-placement="top" title="Nota belum dicek atau dittd" style="font-size:8pt;color:#009688;"></i> TTD by <span class="text-warning">'.$data->sign_by.'</span></span>';
            }
        })  
        ->addcolumn('action', function($data) use($hak_akses){
            $btn = '<div class="btn-group">';
             $btn .= '<a href="'.url('/obat_operasional/cetak_nota/'.$data->id).'" title="Cetak Nota" target="_blank"  class="btn btn-default btn-sm"><span data-toggle="tooltip" data-placement="top" title="Cetak Nota"><i class="fa fa-print"></i> Cetak</span></a>';
            $btn .= '<a href="'.url('/obat_operasional/'.$data->id.'/edit').'" title="Edit Data" class="btn btn-info btn-sm"><span data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i> Edit</span></a>';

            if($data->tgl_nota > '2022-10-30') {
                if($data->is_sign == 1) {
                    if($hak_akses == 1) {
                        $btn .= '<span class="btn btn-primary btn-sm" onClick="batal_sign('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Batalkan sign ini"><i class="fa fa-unlock"></i>Batal Sign</span>';
                        $btn .= '<span class="btn btn-danger btn-sm" onClick="delete_obat_operasional('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i> Hapus</span>';
                    }
                } else {
                    $btn .= '<span class="btn btn-warning btn-sm" onClick="sign('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Tandai faktur ini sudah dicek dan ttd"><i class="fa fa-signature"></i> Sign</span>';
                    $btn .= '<span class="btn btn-danger btn-sm" onClick="delete_obat_operasional('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i> Hapus</span>';
                }
            }

            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['action', 'is_sign'])
        ->addIndexColumn()
        ->make(true);  
    }

    public function create() {
        $apotek = MasterApotek::on($this->getConnectionName())->find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $tanggal = date('Y-m-d');
        $obat_operasional = new TransaksiPO;
        $obat_operasional->setDynamicConnection();
        $detail_obat_operasionals = new TransaksiPODetail;
        $detail_obat_operasionals->setDynamicConnection();
        $var = 1;
        return view('obat_operasional.create')->with(compact('obat_operasional', 'detail_obat_operasionals', 'var', 'apotek', 'inisial'));
    }

    public function store(Request $request) {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        DB::connection($this->getConnectionName())->beginTransaction();  
        try{
            $obat_operasional = new TransaksiPO;
            $obat_operasional->setDynamicConnection();
            $obat_operasional->fill($request->except('_token'));
            $obat_operasional->id_apotek_nota = session('id_apotek_active');
            $obat_operasional->tgl_nota = date('Y-m-d');
            $detail_obat_operasionals = collect();

            $apotek = MasterApotek::on($this->getConnectionName())->find(session('id_apotek_active'));
            $inisial = strtolower($apotek->nama_singkat);
            $tanggal = date('Y-m-d');

            $validator = $obat_operasional->validate();
            if($validator->fails()){
                $var = 0;
                DB::connection($this->getConnectionName())->rollback();
                echo json_encode(array('status' => 0));
            }else{
                $obat_operasional->save_from_array($detail_obat_operasionals,1);
                DB::connection($this->getConnectionName())->commit();
                echo json_encode(array('status' => 1, 'id' => $obat_operasional->id));
            } 
        }catch(\Exception $e){
            DB::connection($this->getConnectionName())->rollback();
            echo json_encode(array('status' => 0, 'message' => $e->getMessage()));
        }
    }

    public function edit($id) {
        $obat_operasional = TransaksiPO::on($this->getConnectionName())->find($id);
        $apotek = MasterApotek::on($this->getConnectionName())->find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $tanggal = date('Y-m-d');

        $detail_obat_operasionals = $obat_operasional->detail_obat_operasional;

        $var = 0;
        return view('obat_operasional.edit')->with(compact('obat_operasional', 'detail_obat_operasionals', 'var', 'apotek', 'inisial'));
    }

    public function show($id) {

    }

    public function update(Request $request, $id) {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
    	DB::connection($this->getConnectionName())->beginTransaction();  
        try{
	        $obat_operasional = TransaksiPO::on($this->getConnectionName())->find($id);
	        $obat_operasional->fill($request->except('_token'));
	        $detail_obat_operasionals = collect();

	        $apotek = MasterApotek::on($this->getConnectionName())->find(session('id_apotek_active'));
	        $inisial = strtolower($apotek->nama_singkat);
	        $tanggal = date('Y-m-d');

	        $validator = $obat_operasional->validate();
	        if($validator->fails()){
	            $var = 1;
                DB::connection($this->getConnectionName())->rollback();
	            echo json_encode(array('status' => 0));
	        }else{
	            $obat_operasional->save_from_array($detail_obat_operasionals,1);
                DB::connection($this->getConnectionName())->commit();
	            echo json_encode(array('status' => 1, 'id' => $obat_operasional->id));
	        }
	   	}catch(\Exception $e){
            DB::connection($this->getConnectionName())->rollback();
            echo json_encode(array('status' => 0, 'message' => $e->getMessage()));
        }
    }

    public function destroy_($id) {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        DB::connection($this->getConnectionName())->beginTransaction();  
        try{
            $apotek = MasterApotek::on($this->getConnectionName())->find(session('id_apotek_active'));
            $inisial = strtolower($apotek->nama_singkat);
            $to = TransaksiPO::on($this->getConnectionName())->find($id);
            $to->is_deleted = 1;
            $to->deleted_at = date('Y-m-d H:i:s');
            $to->deleted_by = Auth::user()->id;
            $to->grand_total = 0;
            
            $detail_obat_operasionals = TransaksiPODetail::on($this->getConnectionName())->where('id_nota', $to->id)->get();
            foreach ($detail_obat_operasionals as $key => $val) {
                $detail_obat_operasional = TransaksiPODetail::on($this->getConnectionName())->find($val->id);
                $detail_obat_operasional->is_deleted = 1;
                $detail_obat_operasional->deleted_at = date('Y-m-d H:i:s');
                $detail_obat_operasional->deleted_by = Auth::user()->id;
                $detail_obat_operasional->save();

                $stok_before = DB::connection($this->getConnectionName())->table('tb_m_stok_harga_'.$inisial)->where('id_obat', $detail_obat_operasional->id_obat)->first();
                $selisih = $detail_obat_operasional->jumlah;

                $id_jenis_transaksi = 21;
                $stok_now = $stok_before->stok_akhir+$selisih;
                # update ke table stok harga
                DB::connection($this->getConnectionName())->table('tb_m_stok_harga_'.$inisial)->where('id_obat', $detail_obat_operasional->id_obat)->update(['stok_awal'=> $stok_before->stok_akhir, 'stok_akhir'=> $stok_now, 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => Auth::user()->id]);

                # create histori
                DB::connection($this->getConnectionName())->table('tb_histori_stok_'.$inisial)->insert([
                    'id_obat' => $detail_obat_operasional->id_obat,
                    'jumlah' => $selisih,
                    'stok_awal' => $stok_before->stok_akhir,
                    'stok_akhir' => $stok_now,
                    'id_jenis_transaksi' => $id_jenis_transaksi, 
                    'id_transaksi' => $detail_obat_operasional->id,
                    'batch' => null,
                    'ed' => null,
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => Auth::user()->id
                ]);
            }

            if($to->save()){
                DB::connection($this->getConnectionName())->commit();
                echo 1;
            }else{
                echo 0;
            }
        }catch(\Exception $e){
            DB::connection($this->getConnectionName())->rollback();
            session()->flash('error', 'Error!');
            return redirect('obat_operasional');
        }
    }

    public function find_ketentuan_keyboard(){
        return view('obat_operasional._form_ketentuan_keyboard');
    }

    public function edit_detail(Request $request){
        $id = $request->id;
        $no = $request->no;
        $detail = TransaksiPODetail::on($this->getConnectionName())->find($id);
        return view('obat_operasional._form_edit_detail')->with(compact('detail', 'no'));
    }

    public function hapus_detail($id) {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        DB::connection($this->getConnectionName())->beginTransaction();  
        try{
            $detail_obat_operasional = TransaksiPODetail::on($this->getConnectionName())->find($id);
            $detail_obat_operasional->is_deleted = 1;
            $detail_obat_operasional->deleted_at= date('Y-m-d H:i:s');
            $detail_obat_operasional->deleted_by = Auth::user()->id;
            $detail_obat_operasional->save();

            $apotek = MasterApotek::on($this->getConnectionName())->find(session('id_apotek_active'));
            $inisial = strtolower($apotek->nama_singkat);
            $stok_before = DB::connection($this->getConnectionName())->table('tb_m_stok_harga_'.$inisial)->where('id_obat', $detail_obat_operasional->id_obat)->first();
            $selisih = $detail_obat_operasional->jumlah;

            $id_jenis_transaksi = 21;
            $stok_now = $stok_before->stok_akhir+$selisih;
           
            # update ke table stok harga
            DB::connection($this->getConnectionName())->table('tb_m_stok_harga_'.$inisial)->where('id_obat', $detail_obat_operasional->id_obat)->update(['stok_awal'=> $stok_before->stok_akhir, 'stok_akhir'=> $stok_now, 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => Auth::user()->id]);

            # create histori
            DB::connection($this->getConnectionName())->table('tb_histori_stok_'.$inisial)->insert([
                'id_obat' => $detail_obat_operasional->id_obat,
                'jumlah' => $selisih,
                'stok_awal' => $stok_before->stok_akhir,
                'stok_akhir' => $stok_now,
                'id_jenis_transaksi' => $id_jenis_transaksi, 
                'id_transaksi' => $detail_obat_operasional->id,
                'batch' => null,
                'ed' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => Auth::user()->id
            ]);

            $total = TransaksiPODetail::select([
                                DB::raw('SUM(total) as total_all')
                                ])
                                ->where('id', '!=', $detail_obat_operasional->id)
                                ->where('id_nota', $detail_obat_operasional->id_nota)
                                ->where('is_deleted', 0)
                                ->first();

            $y = 0;
            if($total->total_all == 0 OR $total->total_all == '') {
                $y = 0;
            } else {
                $y = $total->total_all;
            }

            $obat_operasional = TransaksiPO::on($this->getConnectionName())->find($detail_obat_operasional->id_nota);
            if($y == 0) {
                $obat_operasional->grand_total = $y;
                $obat_operasional->is_deleted = 1;
                $obat_operasional->deleted_at= date('Y-m-d H:i:s');
                $obat_operasional->deleted_by = Auth::user()->id;
            }   

            if($obat_operasional->save()){
                DB::connection($this->getConnectionName())->commit();
                echo 1;
            }else{
                echo 0;
            }
        }catch(\Exception $e){
            DB::connection($this->getConnectionName())->rollback();
            session()->flash('error', 'Error!');
            return redirect('obat_operasional');
        }
    }

    public function cetak_nota(Request $request)
    {   
        $obat_operasional = TransaksiPO::on($this->getConnectionName())->where('id', $request->id)->first();
        $detail_obat_operasionals = TransaksiPODetail::select(['tb_detail_nota_po.*'])
                                               ->where('tb_detail_nota_po.id_nota', $obat_operasional->id)
                                               ->get();
        $apotek = MasterApotek::on($this->getConnectionName())->find($obat_operasional->id_apotek_nota);

        $id_printer_active = session('id_printer_active');
        if(is_null($id_printer_active)) {
            session(['id_printer_active' => $apotek->id_printer]);
            $id_printer_active = session('id_printer_active');
        }

        if($id_printer_active == 1) {
            return view('obat_operasional._form_cetak_nota')->with(compact('obat_operasional', 'detail_obat_operasionals', 'apotek'));
        } else {
            return view('obat_operasional._form_cetak_nota2')->with(compact('obat_operasional', 'detail_obat_operasionals', 'apotek'));
        }
    } 

    public function load_data_nota_print($id) {
        $no = 0;

        $nota = TransaksiPO::on($this->getConnectionName())->find($id);
        $detail_obat_operasionals = TransaksiPODetail::on($this->getConnectionName())->where('id_nota', $nota->id)->get();
        $apotek = MasterApotek::on($this->getConnectionName())->find(session('id_apotek_active'));
	    $inisial = strtolower($apotek->nama_singkat);
        $nama_apotek = strtoupper($apotek->nama_panjang);
        $nama_apotek_singkat = strtoupper($apotek->nama_singkat);

        $a = str_pad("",40," ", STR_PAD_LEFT)."\n".
             str_pad("APOTEK BWF-".$nama_apotek, 40," ", STR_PAD_BOTH)."\n".
             str_pad($apotek->alamat, 40," ", STR_PAD_BOTH)."\n".
             str_pad("Telp. ". $apotek->telepon, 40," ", STR_PAD_BOTH);
        $a = $a."\n".
        "----------------------------------------\n".
        "No. Nota  : ".$nama_apotek_singkat."-".$nota['id']."\n".
        "Tanggal   : ".date_format($nota['created_at'],'d-m-Y H:i:s')."\n".
        "Kasir     : ".$nota->created_oleh->nama."\n".
        "Keterangan: ".$nota->keterangan."\n".
        "----------------------------------------\n";
/*
        $b=$b."\n".
        "       Kasir,                ".$nota->dokter->nama.",       \n".
        "                                        \n".
        "                                        \n".
        "                                        \n".
        "(-----------------)  (-----------------)\n";*/

        
        $total_belanja = 0;
        foreach ($detail_obat_operasionals as $key => $val) {
            $no++;
            $total_belanja = $total_belanja + $val->total;
            
            $a=$a.
                str_pad($no.".".$val->obat->nama, 40," ", STR_PAD_RIGHT)."\n ".                 
                //str_pad(" (diskon ".number_format($diskon, 0, '.', ',')."%)",11," ", STR_PAD_LEFT)."\n ".
                str_pad(number_format($val->harga_jual, 0, '.', ','), 7," ", STR_PAD_LEFT).
                str_pad(" x ",3," ", STR_PAD_LEFT).
                str_pad(number_format($val->jumlah, 0, '.', ','),9," ", STR_PAD_RIGHT).
                str_pad("= ",3," ", STR_PAD_LEFT).str_pad("Rp ". number_format($val->total, 0, '.', ','),10," ", STR_PAD_LEFT)."\n";

        }

        $a=$a.
            "----------------------------------------\n".
            "Total     : Rp ".number_format($total_belanja,0,',',',')."\n".
            "----------------------------------------\n";
        /*$a=$a.$b."\n".
            "----------------------------------------\n";*/
        $a=$a.str_pad("~ Selamat bekerja ~", 40," ", STR_PAD_BOTH);
        $a=$a."\n".
            "----------------------------------------\n";


        $b=$a.str_pad("",40," ", STR_PAD_LEFT)."\n"."\n".str_pad("",40," ", STR_PAD_LEFT)."\n".str_pad("",40," ", STR_PAD_LEFT)."\n".str_pad("",40," ", STR_PAD_LEFT)."\n".str_pad("",40," ", STR_PAD_LEFT)."\n".str_pad("",40," ", STR_PAD_LEFT)."\n".str_pad("",40," ", STR_PAD_LEFT)."\n".str_pad("",40," ", STR_PAD_LEFT);       
            
            print_r($b) ;
    }

    public function load_page_print_nota($id) {
        $no = 0;

        $nota = TransaksiPO::on($this->getConnectionName())->find($id);
        $apotek = MasterApotek::on($this->getConnectionName())->find($nota->id_apotek_nota);
        $detail_pos = TransaksiPODetail::on($this->getConnectionName())->where('id_nota', $nota->id)->where('is_deleted', 0)->get();
        $nama_apotek = strtoupper($apotek->nama_panjang);
        $nama_apotek_singkat = strtoupper($apotek->nama_singkat);


        $a = '
                <!DOCTYPE html>
                <html lang="en">
                    <style rel="stylesheet">
                       @font-face {
                            font-family: "arial_monospaced_mt";
                            src: url('.url('assets/dist/font/arial_monospaced_mt.ttf').') format("truetype");
                            font-weight: normal;
                            font-style: normal;

                        }

                        * {
                            font-size: 11px;
                            font-family: "arial_monospaced_mt";
                            margin-left:  1px;
                            margin-right: 1px;
                            margin-top: 0px;
                            margin-bottom: 0px;
                        }

                        td,
                        th,
                        tr,
                        table {
                            /*border-top: 1px solid black;*/
                            border-collapse: collapse;
                        }

                        .centered {
                            text-align: center;
                            align-content: center;
                        }

                        .ticket {
                            width: 200px;
                            max-width: 200px;
                            background-color: white !important;
                        }

                        @media print {
                            .hidden-print,
                            .hidden-print * {
                                display: none !important;
                            }
                        }

                        .btn-sm {
                            padding: .25rem .5rem;
                            font-size: .875rem;
                            line-height: 1.5;
                            border-radius: .2rem;
                        }

                        .btn-info {
                            color: #fff;
                            background-color: #17a2b8;
                            border-color: #17a2b8;
                            box-shadow: none;
                        }
                        .btn {
                            display: inline-block;
                            font-weight: 400;
                            color: #212529;
                            text-align: center;
                            vertical-align: middle;
                            cursor: pointer;
                            -webkit-user-select: none;
                            -moz-user-select: none;
                            -ms-user-select: none;
                            user-select: none;
                            background-color: transparent;
                            border: 1px solid transparent;
                            padding: .375rem .75rem;
                            font-size: 1rem;
                            line-height: 1.5;
                            border-radius: .25rem;
                            transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;
                        }

                    </style>
                    <body>
                        <!--  -->
                        <button id="btnPrint" class="hidden-print btn btn-sm btn-info" style="margin:0;color: #fff;background-color: #17a2b8;border-color: #17a2b8;box-shadow: none; font-size:10pt;">Print Nota | Ctrl+P</button>
                        <br>
                        <br>
                        <br>
                        <div class="ticket">
                            <input type="hidden" name="id" id="id" value="'.$nota->id.'">
                            <table width="100%">';

        $a .= ' <tr>
                    <td style="text-align: center;" colspan="2">APOTEK BWF-'.$nama_apotek.'</td>
                </tr>
                <tr>
                    <td style="text-align: center;" colspan="2">'.$apotek->alamat.'</td>
                </tr>
                <tr>
                    <td style="text-align: center;" colspan="2">Telp. '.$apotek->telepon.'</td>
                </tr>
                <tr>
                    <td colspan="2">------------------------------</td>
                </tr>';
             
        $tgl_nota = Carbon::parse($nota->created_at)->format('d-m-Y H:i:s');
        
        $a .= ' <tr>
                    <td colspan="2">No. Nota : '.$nota->id.'</td>
                </tr>
                <tr>
                    <td colspan="2">Tanggal  &nbsp;: '.$tgl_nota.'</td>
                </tr>
                <tr>
                    <td colspan="2">Kasir  &nbsp;&nbsp;&nbsp;: '.$nota->created_oleh->nama.'</td>
                </tr>
                <tr>
                    <td colspan="2">Keterangan  : '.$nota->keterangan.'</td>
                </tr>
                <tr>
                    <td colspan="2">------------------------------</td>
                </tr>';
        
        if($nota->is_kredit == 1) {
            $vendor = MasterVendor::on($this->getConnectionName())->find($nota->id_vendor);
            $a .= ' 
                <tr>
                    <td colspan="2">------------------------------</td>
                </tr>
                <tr>
                    <td colspan="2">Penjualan Melalui &nbsp;: '.$vendor->nama.'</td>
                </tr>
                <tr>
                    <td colspan="2">------------------------------</td>
                </tr>';
        }

        $total_belanja = 0;
        foreach ($detail_pos as $key => $val) {
            $no++;

            $total_1 = ($val->jumlah) * $val->harga_jual;
            $total_2 = $total_1;
            $total_belanja = $total_belanja + $total_2;
            $harga_jual = number_format($val->harga_jual,0,',',',');
            $total_2 = number_format($total_2,0,',',',');

            $a .= ' 
            <tr>
                <td colspan="2">'.$no.'.'.$val->obat->nama.'</td>
            </tr>
            <tr>
                <td colspan="2">&nbsp;&nbsp;'.$harga_jual.'x'.number_format($val->jumlah, 0, '.', ',').' = '.'Rp'. $total_2.'</td>
            </tr>';
        }
        $a .= ' <tr>
                    <td colspan="2">------------------------------</td>
                </tr>';

        
        $grand_total_format = number_format($total_belanja,0,',',',');

        $a .= ' 
                 <tr>
                    <td colspan="2">Grand Total : Rp '.$grand_total_format.'</td>
                </tr>';
        
        $a .= ' <tr>
                    <td colspan="2">------------------------------</td>
                </tr>';
    

        $a .= '
                <tr>
                    <td colspan="2" align="center"> ~ Selamat bekerja ~ </td>
                </tr>
                 <tr>
                    <td colspan="2">------------------------------</td>
                </tr>
                ';

        $a .= '</table>';
        $a .= ' </div>
            </body>
        </html>';
        $html=$a;

        print_r($html);
        exit();

        return $html;
    }

    public function pencarian_obat() {
        return view('obat_operasional.pencarian_obat');
    }

    public function list_pencarian_obat(Request $request) {
        DB::connection($this->getConnection())->statement(DB::raw('set @rownum = 0'));
        $data = TransaksiPODetail::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_detail_nota_po.*', 'a.nama'])
        ->join('tb_m_obat as a', 'a.id', 'tb_detail_nota_po.id_obat')
        ->join('tb_nota_po as b', 'b.id', 'tb_detail_nota_po.id_nota')
        ->where(function($query) use($request){
            $query->where('tb_detail_nota_po.is_deleted','=','0');
            $query->where('b.id_apotek_nota','=',session('id_apotek_active'));
        })
        ->orderBy('b.id', 'DESC');
        
        $datatables = Datatables::of($data);
        return $datatables  
        ->filter(function($query) use($request){
            $query->where(function($query) use($request){
                $query->orwhere('a.nama','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('a.barcode','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('a.sku','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->editcolumn('created_at', function($data) use($request){
            return Carbon::parse($data->created_at)->format('d/m/Y H:i:s');
        })
        ->editcolumn('created_by', function($data) use($request){
            return $data->created_oleh->nama;
        })
        ->editcolumn('id_obat', function($data) {
            $info = '<small>Keterangan : '.$data->keterangan.'</small>';
            return $data->nama.'<br>'.$info;
        })  
        ->editcolumn('total', function($data) {
            $str_ = '';
            $str_ = $data->jumlah.' X Rp '.number_format($data->harga_jual, 2).' = Rp '.number_format($data->total, 2);
            return $str_;
        })    
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['action', 'id_obat'])
        ->addIndexColumn()
        ->make(true);  
    }

    public function export(Request $request) 
    {
        $rekaps = TransaksiPO::select([
                                    DB::raw('@rownum  := @rownum  + 1 AS no'),
                                    'tb_nota_po.*'
                                ])
                                ->where(function($query) use($request){
                                    $query->where('tb_nota_po.is_deleted','=','0');
                                    $query->where('tb_nota_po.id_apotek_nota','=',session('id_apotek_active'));
                                    $query->where('tb_nota_po.id','LIKE',($request->id > 0 ? $request->id : '%'.$request->id.'%'));
                                    $query->where('tb_nota_po.keterangan','LIKE',($request->keterangan > 0 ? $request->keterangan : '%'.$request->keterangan.'%'));
                                    if (!empty($request->tgl_awal) && !empty($request->tgl_akhir)) {
                                        $query->where('tb_nota_po.created_at','>=', $request->tgl_awal);
                                        $query->where('tb_nota_po.created_at','<=', $request->tgl_akhir);
                                    }
                                })
                                ->groupBy('tb_nota_po.id')
                                ->get();


                $collection = collect();
                $no = 0;
                $total_excel=0;
                foreach($rekaps as $rekap) {
                    $no++;
                    $collection[] = array(
                        $no,
                        $rekap->created_at,
                        $rekap->created_oleh->nama,
                        $rekap->grand_total,
                        "Rp ".number_format($rekap->grand_total,2),
                        $rekap->keterangan
                    );
                }

        return Excel::download(new class($collection) implements FromCollection, WithHeadings, WithColumnWidths, WithStyles {

                    public function __construct($collection)
                    {
                        $this->collection = $collection;
                    }

                    public function headings(): array
                    {
                        return ['No', 'Tanggal', 'Dokter', 'Total', 'Total (Rp)', 'Keterangan'];
                    } 

                    /*public function columnFormats(): array
                    {
                        return [
                            'B' => NumberFormat::FORMAT_DATE_DDMMYYYY,
                            'C' => NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE,
                        ];
                    }*/

                    public function columnWidths(): array
                    {
                        return [
                            'A' => 8,
                            'B' => 20,
                            'C' => 30,
                            'D' => 18,
                            'E' => 18,
                            'F' => 50,            
                        ];
                    }

                    public function styles(Worksheet $sheet)
                    {
                        return [
                            1    => ['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'A'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'B'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            //'C'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            //'D'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'E'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                        ];
                    }


                    public function collection()
                    {
                        return $this->collection;
                    }
        },"Rekap Obat Operasional.xlsx");
    }

    public function list_detail_po(Request $request) {
        //dd($request->id);
        # get total to
        $id = $request->id;
        $is_access = 1;
        $total_po = 0;
        $po = collect();
        $counter = 0;
        if(is_null($id)) {
            
        } else {
            $po = TransaksiPO::on($this->getConnectionName())->find($id);
    
            $total_po = $po->detail_po_total[0]->total;
            if($total_po == "" || $total_po == null) {
                $total_po = 0;
            }

            $counter = count($po->detail_po);
        }

        DB::connection($this->getConnection())->statement(DB::raw('set @rownum = 0'));
        $data = TransaksiPODetail::select([
                DB::raw('@rownum  := @rownum  + 1 AS no'),
                'tb_detail_nota_po.*', 
        ])
        ->where(function($query) use($request){
            $query->where('tb_detail_nota_po.is_deleted','=','0');
            if(is_null($request->id)) {
                $query->where('tb_detail_nota_po.id_nota','=',0);
            } else {
                $query->where('tb_detail_nota_po.id_nota','=',$request->id);
            }
            
        })
        ->orderBy('tb_detail_nota_po.id', 'ASC');
        
        $datatables = Datatables::of($data);
        return $datatables
        /*->filter(function($query) use($request){
            $query->where(function($query) use($request){
                $query->orwhere('tb_detail_nota_penjualan.id','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  */
        ->editcolumn('action', function($data) use($request, $is_access, $po){
            $btn ='';
            if($is_access == 1) {
                $btn .= '<span class="btn btn-danger btn-xs" onClick="delete_item('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-trash"></i></span>';
            }

            return $btn;
        })
        ->editcolumn('nama_barang', function($data) use($request){
            $nama = '';
            $obat = $data->obat;
            $nama = $obat->nama;
            return $nama;
        })
        ->editcolumn('harga_jual', function($data) use($request){
            return $data->harga_jual;
           // return "Rp ".number_format($data->harga_jual,0);
        })   
        ->editcolumn('hb_ppn', function($data) use($request){
            return $data->hb_ppn;
           // return "Rp ".number_format($data->harga_jual,0);
        })    
        ->editcolumn('total', function($data) use($request){
            $total_penjualan = ($data->jumlah * $data->harga_jual);

            //return "Rp ".number_format($total_penjualan,0);
            return $total_penjualan;
        })  
        ->addcolumn('action', function($data){
            $btn = '<div class="btn-group">';
            $btn .='</div>';
            return $btn;
        }) 
        ->with([
            "po" => $po,
            "total_po" => $total_po,
            "total_po_format" => "Rp ".number_format($total_po,0),
            "counter" => $counter
        ])   
        ->rawColumns(['action', 'nama_barang', 'harga_jual', 'total', 'hb_ppn'])
        ->addIndexColumn()
        ->make(true);  
    }

    public function AddItem(Request $request) {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        DB::connection($this->getConnectionName())->beginTransaction();  
        try{
            $po = new TransaksiPO;
            $po->setDynamicConnection();
            $po->fill($request->except('_token'));
          
            $detail_pos = array();
            $detail_pos[] = array(
                'id' => null,
                'id_obat' => $request->id_obat, 
                'harga_jual' => $request->harga_jual,
                'hb_ppn' => $request->hb_ppn,
                'jumlah' => $request->jumlah
            );

            //dd($detail_penjualans);exit();

            $tanggal = date('Y-m-d');

            $apotek = MasterApotek::on($this->getConnectionName())->find(session('id_apotek_active'));
            $inisial = strtolower($apotek->nama_singkat);

            $result = $po->save_from_array($detail_pos, 1);
            if($result['status']) {
                DB::connection($this->getConnectionName())->commit();
                echo json_encode(array('status' => 1, 'id' => $po->id, 'message' => $result['message']));
            } else {
                DB::connection($this->getConnectionName())->rollback();
                echo json_encode(array('status' => 0, 'message' => 'Error, silakan cek kembali data yang diinputkan'));
            }
        }catch(\Exception $e){
            DB::connection($this->getConnectionName())->rollback();
            echo json_encode(array('status' => 0, 'message' => $e->getMessage()));
        }
    }

    public function UpdateItem(Request $request) {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        DB::connection($this->getConnectionName())->beginTransaction();  
        try{
            $id = $request->id;
            $po = TransaksiPO::on($this->getConnectionName())->find($id);
            if($po->is_deleted != 1) {
                $po->fill($request->except('_token'));

                $detail_pos = array();
                $detail_pos[] = array(
                    'id' => null,
                    'id_obat' => $request->id_obat, 
                    'harga_jual' => $request->harga_jual,
                    'hb_ppn' => $request->hb_ppn,
                    'jumlah' => $request->jumlah
                );

                //dd($detail_penjualans);
                $tanggal = date('Y-m-d');

                $apotek = MasterApotek::on($this->getConnectionName())->find(session('id_apotek_active'));
                $inisial = strtolower($apotek->nama_singkat);
                
                $result = $po->save_from_array($detail_pos, 2);
                if($result['status']) {
                    DB::connection($this->getConnectionName())->commit();
                    echo json_encode(array('status' => 1, 'id' => $po->id, 'message' => $result['message']));
                } else {
                    DB::connection($this->getConnectionName())->rollback();
                    echo json_encode(array('status' => 0, 'message' => 'Error, silakan cek kembali data yang diinputkan'));
                }
            } else {
                DB::connection($this->getConnectionName())->rollback();
                echo json_encode(array('status' => 0, 'message' => 'Error, nota ini sudah dihapus, silakan tambah nota baru'));
            }
        }catch(\Exception $e){
            DB::connection($this->getConnectionName())->rollback();
            echo json_encode(array('status' => 0));
        }
    }

    public function DeleteItem(Request $request, $id) {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        # yang bisa didelete adalah | yang belum dikonfirm
        DB::connection($this->getConnectionName())->beginTransaction();  
        try{
            $detail_po = TransaksiPODetail::on($this->getConnectionName())->find($id);
            $detail_po->is_deleted = 1;
            $detail_po->deleted_at = date('Y-m-d H:i:s');
            $detail_po->deleted_by = Auth::user()->id;
           
            # crete histori stok barang
            $apotek = MasterApotek::on($this->getConnectionName())->find(session('id_apotek_active'));
            $inisial = strtolower($apotek->nama_singkat);
            $stok_before = DB::connection($this->getConnectionName())->table('tb_m_stok_harga_'.$inisial)->where('id_obat', $detail_po->id_obat)->first(); 
            $stok_now = $stok_before->stok_akhir+$detail_po->jumlah;

            # update ke table stok harga
            $stok_harga = MasterStokHarga::on($this->getConnectionName())->where('id_obat', $detail_po->id_obat)->first();
            $stok_harga->stok_awal = $stok_before->stok_akhir;
            $stok_harga->stok_akhir = $stok_now;
            $stok_harga->updated_at = date('Y-m-d H:i:s'); 
            $stok_harga->updated_by = Auth::user()->id;
            if($stok_harga->save()) {
            } else {
                DB::connection($this->getConnectionName())->rollback();
                echo json_encode(array('status' => 0));
            }

            # create histori
            $histori_stok = HistoriStok::on($this->getConnectionName())->where('id_obat', $detail_po->id_obat)->where('jumlah', $detail_po->jumlah)->where('id_jenis_transaksi', 21)->where('id_transaksi', $detail_po->id)->first();
            if(empty($histori_stok)) {
                $histori_stok = new HistoriStok;
                $histori_stok->setDynamicConnection();
            }
            $histori_stok->id_obat = $detail_po->id_obat;
            $histori_stok->jumlah = $detail_po->jumlah;
            $histori_stok->stok_awal = $stok_before->stok_akhir;
            $histori_stok->stok_akhir = $stok_now;
            $histori_stok->id_jenis_transaksi = 21; //hapus to penjualan operasional
            $histori_stok->id_transaksi = $detail_po->id;
            $histori_stok->batch = null;
            $histori_stok->ed = null;
            $histori_stok->sisa_stok = null;
            $histori_stok->hb_ppn = $detail_po->hb_ppn;
            $histori_stok->created_at = date('Y-m-d H:i:s');
            $histori_stok->created_by = Auth::user()->id;
            if($histori_stok->save()) {
            } else {
                DB::connection($this->getConnectionName())->rollback();
                echo json_encode(array('status' => 0));
            }

            # update stok aktif 
            $histori_stok_details = json_decode($detail_po->id_histori_stok_detail);
            if(count($histori_stok_details) == 0) {
                DB::connection($this->getConnectionName())->rollback();
                echo json_encode(array('status' => 0));
            } else {
                foreach ($histori_stok_details as $y => $hist) {
                    $cekHistori = HistoriStok::on($this->getConnectionName())->find($hist->id_histori_stok);
                    $keterangan = $cekHistori->keterangan.', Hapus PO pada IDdet.'.$detail_po->id.' sejumlah '.$hist->jumlah;
                    $cekHistori->sisa_stok = $cekHistori->sisa_stok + $hist->jumlah;
                    $cekHistori->keterangan = $keterangan;
                    if($cekHistori->save()) {
                    } else {
                        DB::connection($this->getConnectionName())->rollback();
                        echo json_encode(array('status' => 0));
                    }
                }
            }
            
            if($detail_po->save()) {
                # cek apakah masih ada item pada nota yang sama
                $jum_details = TransaksiPODetail::on($this->getConnectionName())->where('is_deleted', 0)->where('id_nota', $detail_po->id_nota)->count();
                $is_sisa = 1;
                if($jum_details == 0) {
                    $po = TransaksiPO::on($this->getConnectionName())->find($detail_po->id_nota);
                    $po->is_deleted = 1;
                    $po->deleted_at = date('Y-m-d H:i:s');
                    $po->deleted_by = Auth::user()->id;
                    if($po->save()) {
                    } else {
                        DB::connection($this->getConnectionName())->rollback();
                        echo json_encode(array('status' => 0));
                    }

                    $is_sisa = 0;
                }

                DB::connection($this->getConnectionName())->commit();
                echo json_encode(array('status' => 1, 'is_sisa' => $is_sisa));
            } else {
                DB::connection($this->getConnectionName())->rollback();
                echo json_encode(array('status' => 0));
            }
        }catch(\Exception $e){
            DB::connection($this->getConnectionName())->rollback();
            echo json_encode(array('status' => 0));
        }
    }

    public function destroy($id) {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        DB::connection($this->getConnectionName())->beginTransaction();  
        try{
            $apotek = MasterApotek::on($this->getConnectionName())->find(session('id_apotek_active'));
            $inisial = strtolower($apotek->nama_singkat);
            $po = TransaksiPO::on($this->getConnectionName())->find($id);
            $po->is_deleted = 1;
            $po->deleted_at = date('Y-m-d H:i:s');
            $po->deleted_by = Auth::user()->id;

            $detail_pos = TransaksiPODetail::on($this->getConnectionName())->where('id_nota', $po->id)->get();
            foreach ($detail_pos as $key => $detail_po) {
                $detail_po->is_deleted = 1;
                $detail_po->deleted_at = date('Y-m-d H:i:s');
                $detail_po->deleted_by = Auth::user()->id;
               
                # crete histori stok barang
                $apotek = MasterApotek::on($this->getConnectionName())->find(session('id_apotek_active'));
                $inisial = strtolower($apotek->nama_singkat);
                $stok_before = DB::connection($this->getConnectionName())->table('tb_m_stok_harga_'.$inisial)->where('id_obat', $detail_po->id_obat)->first(); 
                $stok_now = $stok_before->stok_akhir+$detail_po->jumlah;

                # update ke table stok harga
                $stok_harga = MasterStokHarga::on($this->getConnectionName())->where('id_obat', $detail_po->id_obat)->first();
                $stok_harga->stok_awal = $stok_before->stok_akhir;
                $stok_harga->stok_akhir = $stok_now;
                $stok_harga->updated_at = date('Y-m-d H:i:s'); 
                $stok_harga->updated_by = Auth::user()->id;
                if($stok_harga->save()) {
                } else {
                    DB::connection($this->getConnectionName())->rollback();
                    echo json_encode(array('status' => 0));
                }

                # create histori
                $histori_stok = HistoriStok::on($this->getConnectionName())->where('id_obat', $detail_po->id_obat)->where('jumlah', $detail_po->jumlah)->where('id_jenis_transaksi', 21)->where('id_transaksi', $detail_po->id)->first();
                if(empty($histori_stok)) {
                    $histori_stok = new HistoriStok;
                    $histori_stok->setDynamicConnection();
                }
                $histori_stok->id_obat = $detail_po->id_obat;
                $histori_stok->jumlah = $detail_po->jumlah;
                $histori_stok->stok_awal = $stok_before->stok_akhir;
                $histori_stok->stok_akhir = $stok_now;
                $histori_stok->id_jenis_transaksi = 21; //hapus to penjualan operasional
                $histori_stok->id_transaksi = $detail_po->id;
                $histori_stok->batch = null;
                $histori_stok->ed = null;
                $histori_stok->sisa_stok = null;
                $histori_stok->hb_ppn = $detail_po->hb_ppn;
                $histori_stok->created_at = date('Y-m-d H:i:s');
                $histori_stok->created_by = Auth::user()->id;
                if($histori_stok->save()) {
                } else {
                    DB::connection($this->getConnectionName())->rollback();
                    echo json_encode(array('status' => 0));
                }

                # update stok aktif 
                $histori_stok_details = json_decode($detail_po->id_histori_stok_detail);
                if(count($histori_stok_details) == 0) {
                    DB::connection($this->getConnectionName())->rollback();
                    echo json_encode(array('status' => 0));
                } else {
                    foreach ($histori_stok_details as $y => $hist) {
                        $cekHistori = HistoriStok::on($this->getConnectionName())->find($hist->id_histori_stok);
                        $keterangan = $cekHistori->keterangan.', Hapus PO pada IDdet.'.$detail_po->id.' sejumlah '.$hist->jumlah;
                        $cekHistori->sisa_stok = $cekHistori->sisa_stok + $hist->jumlah;
                        $cekHistori->keterangan = $keterangan;
                        if($cekHistori->save()) {
                        } else {
                            DB::connection($this->getConnectionName())->rollback();
                        }
                    }
                }

                if($detail_po->save()) {
                } else {
                    DB::connection($this->getConnectionName())->rollback();
                }
            }
            
            if($po->save()){
                echo 1;
                DB::connection($this->getConnectionName())->commit();
            }else{
                echo 0;
                DB::connection($this->getConnectionName())->rollback();
            }
        }catch(\Exception $e){
            DB::connection($this->getConnectionName())->rollback();
            session()->flash('error', 'Error!');
            return redirect('obat_operasional');
        }
    }

    public function getHbppn(Request $request) {
        $id = $request->id;
        $id_obat = $request->id_obat;
        $jumlah = $request->jumlah;

        $kurangStok = $this->kurangStok($id_obat, $jumlah);
        if($kurangStok['status'] == 0) {
            $rsp = array('status' => 0, 'message' => 'Stok yang tersedia tidak mencukupi');
            return $rsp;
        } else {
            $rsp = array('status' => 1, 'message' => 'Hbppn berhasil didapatkan', 'hb_ppn' => $kurangStok['hb_ppn']);
            return $rsp;
        }
    }

    public function kurangStok($id_obat, $jumlah) {
        $inisial = strtolower(session('nama_apotek_singkat_active'));
        $cekHistori = DB::connection($this->getConnectionName())->table('tb_histori_stok_'.$inisial)
                            ->where('id_obat', $id_obat)
                            ->whereIn('id_jenis_transaksi', [2,3,11,9])
                            ->where('sisa_stok', '>', 0)
                            ->orderBy('id', 'ASC')
                            ->first();

        $array_id_histori_stok = array();
        $array_id_histori_stok_detail = array();
        $hb_ppn = 0;

        if(!is_null($cekHistori)) {
            if($cekHistori->sisa_stok >= $jumlah) {
                # kosongkan sisa stok histori sebelumnya 
                $array_id_histori_stok[] = $cekHistori->id;
                $array_id_histori_stok_detail[] = array('id_histori_stok' => $cekHistori->id, 'jumlah' => $jumlah);
                $hb_ppn = $cekHistori->hb_ppn;
            } else {
                # jika jumlahnya tidak sama maka
                $selisih = $jumlah - $cekHistori->sisa_stok;

                # update jumlah selisih ke histori yang ada stok sebelumnya
                $i = $jumlah;
                $total  = 0;
                $array_id_histori_stok_tota = array();
                while($i >= 1) {
                    # cari histori berikutnya yg bisa dikurangi
                    $cekHistoriLanj = DB::connection($this->getConnectionName())->table('tb_histori_stok_'.$inisial)
                            ->where('id_obat', $id_obat)
                            ->whereIn('id_jenis_transaksi', [2,3,11,9])
                            ->where('sisa_stok', '>', 0)
                            ->whereNotIn('id', $array_id_histori_stok)
                            ->orderBy('id', 'ASC')
                            ->first();

                    if($cekHistoriLanj->sisa_stok >= $i) {
                        # update selisih jika stok melebihi jumlah
                        $sisa = $cekHistoriLanj->sisa_stok - $i;
                        $array_id_histori_stok_detail[] = array('id_histori_stok' => $cekHistoriLanj->id, 'jumlah' => $i);
                        $total = $total + ($cekHistoriLanj->hb_ppn * $i);
                        $array_id_histori_stok_tota[] = array('total'=>$total, 'hb_ppn' => $cekHistoriLanj->hb_ppn, 'sisa_stok' => $i);
                        $i = 0;
                    } else {
                        # update selisih jika stok kurang dari jumlah
                        $sisa = $i - $cekHistoriLanj->sisa_stok;
                        $i = $sisa;
                        $array_id_histori_stok_detail[] = array('id_histori_stok' => $cekHistoriLanj->id, 'jumlah' => $cekHistoriLanj->sisa_stok);
                        $total = $total + ($cekHistoriLanj->hb_ppn * $cekHistoriLanj->sisa_stok);
                        $array_id_histori_stok_tota[] = array('total'=>$total, 'hb_ppn' => $cekHistoriLanj->hb_ppn, 'sisa_stok' => $cekHistoriLanj->sisa_stok);
                    }
                    
                    $array_id_histori_stok[] = $cekHistoriLanj->id;
                }

                $hb_ppn = $total/$jumlah;
                $hb_ppn = ceil($hb_ppn);
               // dd($hb_ppn);
            } 

            //exit();
            
            $rsp = array('status' => 1, 'array_id_histori_stok' => json_encode($array_id_histori_stok), 'array_id_histori_stok_detail' => json_encode($array_id_histori_stok_detail), 'hb_ppn' => $hb_ppn);
            return $rsp;
        } else {
            $rsp = array('status' => 0, 'array_id_histori_stok' => null, 'array_id_histori_stok_detail' => null, 'hb_ppn' => null);
            return $rsp;
        }
    }

    public function informasi(){
        return view('obat_operasional.informasi');
    }

    public function send_sign(Request $request)
    {
        $po = TransaksiPO::on($this->getConnectionName())->find($request->id);
        $po->is_sign = 1;
        $po->sign_by = $request->sign_by;
        $po->sign_at = date('Y-m-d H:i:s');

        if($po->save()){
            echo 1;
        }else{
            echo 0;
        }
    } 

    public function batal_sign(Request $request)
    {
        $po = TransaksiPO::on($this->getConnectionName())->find($request->id);
        $po->is_sign = 0;
        $po->sign_by = null;
        $po->sign_at = null;
        $po->updated_by = Auth::user()->id;
        $po->updated_at = date('Y-m-d H:i:s');

        if($po->save()){
            echo 1;
        }else{
            echo 0;
        }
    } 
}
