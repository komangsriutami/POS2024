<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterApotek;
use App\JurnalUmum;
use App\JurnalUmumDetail;
use App\JurnalUmumBukti;
use App\MasterKodeAkun;
use App\MasterKategoriAkun;
use App\MasterJenisTransaksi;
use App\ReloadDataStatus;
use App\ReloadDataStatusDetail;
use App\TransaksiPenjualan;
use App\ReturPenjualan;

use App\Exports\JurnalUmumTemplateExportSheet;
use App\Exports\JurnalUmumTemplateExport;
use App\Exports\JurnalUmumKeterangan;
use App\Imports\JurnalUmumImport;
use App\Imports\JurnalUmumImportSheet;

use Auth;
use App;
use Datatables;
use DB;
use View;
use Crypt;
use File;
use Input;

use Excel;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Traits\DynamicConnectionTrait;

class JurnalUmumController extends Controller
{
    use DynamicConnectionTrait;
    /*
        =======================================================================================
        For     : Halaman utama jurnal umum
        Author  : Citra
        Date    : 09/09/2021
        =======================================================================================
    */
    public function index()
    {
        return view('jurnal_umum.index');
    }


    /*
        =======================================================================================
        For     : Menampilkan list data pada index
        Author  : Citra
        Date    : 09/09/2021
        =======================================================================================
    */
    public function list_jurnalumum(Request $request)
    {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::connection($this->getConnection())->statement(DB::raw('set @rownum = 0'));
        $data = MasterKodeAkun::select([
            DB::raw('@rownum  := @rownum  + 1 AS no'),
            DB::raw('tb_m_kode_akun.id AS id_akun'),
            DB::raw('tb_m_kode_akun.kode AS kode_akun'),
            DB::raw('tb_m_kode_akun.nama AS nama_akun'),
            DB::raw('k.nama AS kategori_akun')
        ])
        ->leftjoin('tb_m_kategori_akun as k','tb_m_kode_akun.id_kategori_akun','k.id')
        // ->where('id_apotek',session('id_apotek_active'))
        ->where(function($query) use($request){
            $query->where('tb_m_kode_akun.is_deleted','=','0');
        })
        ;
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request,$order_column,$order_dir){
            // $query->where(function($query) use($request){
                $query->where('tb_m_kode_akun.nama','LIKE','%'.$request->get('search')['value'].'%');
                $query->Orwhere('tb_m_kode_akun.kode','LIKE','%'.$request->get('search')['value'].'%');
            // });
        })  
        ->editcolumn('nama_akun', function($data){
            return '<a href="'.url("jurnalumum/showakun/".Crypt::encrypt($data->id_akun)).'">'.$data->nama_akun.'</a>'; 
        }) 
        ->addcolumn('pajak', function($data){
            return ''; 
        })  
        ->addcolumn('saldo', function($data){
            $getdebit = JurnalUmumDetail::select(DB::RAW("SUM(debit) as total_debit"))
                    ->join("tb_jurnal_umum as j","j.id","id_jurnal")
                    ->whereRaw("id_kode_akun = '".$data->id_akun."'")
                    ->whereNull("tb_jurnal_umum_detail.deleted_by")
                    ->whereNull("j.deleted_by")
                    ->where("j.id_apotek",session('id_apotek_active'))
                    ->first();
            // dd($getdebit);
            if(empty($getdebit)){ $total_debit = 0; } else { $total_debit = $getdebit->total_debit ; }

            $getkredit = JurnalUmumDetail::select(DB::RAW("SUM(kredit) as total_kredit"))
                    ->join("tb_jurnal_umum as j","j.id","id_jurnal")
                    ->where("id_kode_akun",$data->id_akun)
                    ->whereNull("tb_jurnal_umum_detail.deleted_by")
                    ->whereNull("j.deleted_by")
                    ->where("j.id_apotek",session('id_apotek_active'))
                    ->first();
            if(empty($getkredit)){ $total_kredit = 0; } else { $total_kredit = $getkredit->total_kredit ; }

            $saldo = $total_debit - $total_kredit;
            return 'Rp. '.number_format($saldo);

        }) 
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .= '<span class="btn btn-primary" onClick="edit_data('.$data->id_akun.')" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span>';
            $btn .= '<a href="'.url("jurnalumum/showakun/".Crypt::encrypt($data->id_akun)).'" class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="Lihat Detail Jurnal"><i class="fa fa-list"></i></a>';
            $btn .= '<span class="btn btn-danger" onClick="delete_kode_akuntansi('.$data->id_akun.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['nama_akun','action'])
        ->addIndexColumn()
        ->make(true);  
    }

    /*
        =======================================================================================
        For     : Menampilkan form tambah jurnal
        Author  : Citra
        Date    : 09/09/2021
        =======================================================================================
    */
    public function create()
    {
        $jurnal_umum = new JurnalUmum;
        $jurnal_umum->setDynamicConnection();
        $jenistransaksi = MasterJenisTransaksi::get();
        return view('jurnal_umum.create')->with(compact('jurnal_umum','jenistransaksi'));
    }


    /*
        =======================================================================================
        For     : Menambah Form Input Detail jurnal umum
        Author  : Citra
        Date    : 10/09/2021
        =======================================================================================
    */
    public function addDetail(Request $request)
    {
        // dd($request->input());
        $kode_akun= MasterKodeAkun::select('id',DB::RAW('CONCAT(kode,\' - \',nama) as nama_akun'))->where('is_deleted', 0)->pluck('nama_akun', 'id');
        $kode_akun->prepend('-- Pilih Akun --','');

        $detailjurnal = new JurnalUmumDetail;
        $detailjurnal->setDynamicConnection();
        $count = $request->count;
        $form_detail = View::make('jurnal_umum._form_detail',compact('kode_akun','detailjurnal','count'))->render();
        $status = 1;

        return json_encode(compact('status','form_detail'));
    }


    /*
        =======================================================================================
        For     : Menambah Form Input File lampiran jurnal umum
        Author  : Citra
        Date    : 16/09/2021
        =======================================================================================
    */
    public function addfile(Request $request)
    {
        // dd($request->input());
        $filebukti = new JurnalUmumBukti;
        $filebukti->setDynamicConnection();
        $count = $request->count;
        $form_detail = View::make('jurnal_umum._form_file',compact('filebukti','count'))->render();
        $status = 1;

        return json_encode(compact('status','form_detail'));
    }


    /*
        =======================================================================================
        For     : untuk membuka file blob
        Author  : Citra
        Date    : 20/09/2021
        =======================================================================================
    */
    public function viewFile($id)
    {
        // dd($request->input());
        $id = Crypt::decrypt($id);
        $filebukti = JurnalUmumBukti::on($this->getConnectionName())->find($id);
        // dd($filebukti);
        if(!empty($filebukti)){
            $result['mime'] = $filebukti->type_file;
            $result['file'] = $filebukti->file;
            // dd($result);
            return json_encode($result);
        
        } else {
            echo "File tidak ditemukan";
        }
    }



    /*
        =======================================================================================
        For     : Menyimpan data jurnal ke db
        Author  : Citra
        Date    : 10/09/2021
        =======================================================================================
    */
    public function store(Request $request)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        DB::connection($this->getConnectionName())->beginTransaction();  
        try{
            // dd($request->buktifile);

            $jurnal_umum = new JurnalUmum;
            $jurnal_umum->setDynamicConnection();
            $jurnal_umum->no_transaksi = $request->no_transaksi;
            $jurnal_umum->tgl_transaksi = $request->tgl_transaksi;
            $jurnal_umum->memo = $request->memo;
            // $jurnal_umum->id_jenis_transaksi = $request->id_jenis_transaksi;
            $jurnal_umum->tag = $request->tag;
            // $jurnal_umum->kode_referensi = $request->kode_referensi;
            $validator = $jurnal_umum->validate();
            if(!$validator->fails()){
                $jurnal_umum->id_apotek = session('id_apotek_active');
                $jurnal_umum->created_by = Auth::user()->id;

                if($jurnal_umum->save()){
                    // echo "sukses";


                    // simpan detail //
                    if(isset($request->id_kode_akun)){
                        foreach ($request->id_kode_akun as $key => $kode) {
                            if(!is_null($kode)){
                                $detail = new JurnalUmumDetail;
                                $detail->setDynamicConnection();
                                $detail->id_jurnal = $jurnal_umum->id;
                                $detail->id_kode_akun = $kode;
                                $detail->deskripsi = $request->deskripsi[$key];
                                $detail->kredit = $request->kredit[$key];
                                $detail->debit = $request->debit[$key];
                                $detail->created_by = Auth::user()->id;
                                $detail->save();
                            }
                        }
                    }



                    // simpan bukti //
                    $errorfile = 0;
                    $errorMessages = "";
                    if(isset($request->buktifile)){
                        if(count($request->buktifile)){
                            foreach ($request->buktifile as $key => $bukti) {
                                if($bukti->getMimeType() == "application/pdf" || $bukti->getMimeType() == "image/jpeg" || $bukti->getMimeType() == "image/jpg" || $bukti->getMimeType() == "image/png"){
                                    if(!empty($bukti)){
                                        $mime = $bukti->getMimeType();

                                        $buktijurnal = new JurnalUmumBukti;
                                        $buktijurnal->setDynamicConnection();

                                        $nama_file = $bukti->getClientOriginalName();
                                        $split = explode('.', $nama_file);
                                        $ext = end($split);
                                        $file_name = md5($split[0] . "-" . Date("Y-m-d H:i:s"));
                                        // $logo = $request->img;
                                        $destination_path = public_path('temp\\');
                                        $destination_filename = $file_name . "." . $ext;
                                        // dd($destination_path);
                                        $path = $destination_path.$destination_filename;

                                        $bukti->move($destination_path, $destination_filename);
                                        $buktijurnal->file = $file_name . "." . $ext;

                                        // dd($bukti);

                                        if($mime == "application/pdf"){
                                            $fp = fopen($path,'r');
                                            $content = fread($fp, filesize($destination_path.$destination_filename));
                                            $content = addslashes($content);
                                            fclose($fp);
                                        } else {
                                            $content = file_get_contents($path);
                                            $content = base64_encode($content);
                                        }

                                        $buktijurnal->id_jurnal = $jurnal_umum->id;
                                        $buktijurnal->type_file = $mime;
                                        $buktijurnal->file = $content;
                                        $buktijurnal->keterangan = $request->keterangan[$key];
                                        $buktijurnal->created_by = Auth::user()->id;
                                        $buktijurnal->save();


                                        if (File::exists($path)) {
                                            unlink($path);
                                        }
                                    }
                                } else {
                                    $errorfile++;
                                }
                            }
                        }
                    }

                    if($errorfile > 0){ $errorMessages = "terdapat "+$errorfile+" file bukti dengan ekstensi yang tidak sesuai"; }

                    DB::connection($this->getConnectionName())->commit();
                    echo json_encode(array("status" => 1,"errorMessages" => $errorMessages, "url" => url('jurnalumum')));


                } else {
                    echo json_encode(array("status" => 2));
                }
            } else {
                echo json_encode(array("status" => 2));
            }
        } catch(\Exception $e){
            DB::connection($this->getConnectionName())->rollback();
            echo json_encode(array('status' => 2));
        }
        

        // $jurnal_umum->fill($request->except('_token'));


        /*$validator = $jurnal_umum->validate_kode_akun();
        if($validator->fails()){
            return view('jurnal_umum.create')->with(compact('jurnal_umum', 'kode_akuns', 'kode_sub_akuns'))->withErrors($validator);
        }else{
            $kartu->save_plus();
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('jurnal_umum');
        }*/
    }

    /*
        =======================================================================================
        For     : Menampilkan detil transaksi per kode akun
        Author  : Citra
        Date    : 16/09/2021
        =======================================================================================
    */
    public function showakun($id)
    {
        $idakun = Crypt::decrypt($id);
        $akun = MasterKodeAkun::on($this->getConnectionName())->find($idakun);
        if(!empty($akun)){
            return view('jurnal_umum.showakun')->with(compact("id","akun"));
        } else {
            echo "Kode akun tidak ditemukan";
        }
    }


    /*
        =======================================================================================
        For     : Menampilkan list detail jurnal per akun
        Author  : Citra
        Date    : 20/09/2021
        =======================================================================================
    */
    public function list_detail_jurnalumum($id, Request $request)
    {
        $id = Crypt::decrypt($id);

        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::connection($this->getConnection())->statement(DB::raw('set @rownum = 0'));
        $datadetail = JurnalUmumDetail::select(
                    "tb_jurnal_umum_detail.id",
                    "id_jurnal",
                    "id_kode_akun",
                    "id_jenis_transaksi",
                    "akun.nama as nama_akun",
                    "deskripsi",
                    "debit",
                    "kredit",
                    "tb_jurnal_umum_detail.kode_referensi",
                    "j.no_transaksi",
                    "j.tgl_transaksi",
                    "j.is_tutup_buku",
                    "j.memo",
                    "ap.nama_panjang"
                )
                ->leftjoin("tb_m_kode_akun as akun","akun.id","tb_jurnal_umum_detail.id_kode_akun")
                ->leftjoin("tb_jurnal_umum as j","j.id","tb_jurnal_umum_detail.id_jurnal")
                ->leftjoin("tb_m_apotek as ap","ap.id","j.id_apotek")
                ->where("id_kode_akun",$id)
                ->where("is_penyesuaian",0)
                ->where("j.id_apotek",session('id_apotek_active'))
                ->whereNull("tb_jurnal_umum_detail.deleted_by")
                ->whereNull("j.deleted_by")
                ->orderBy("j.tgl_transaksi");


        $dt = DB::connection($this->getConnectionName())->table(DB::raw('(SELECT @saldo := 0) AS var_saldo, (SELECT @no := 0) AS var_no'))
                    ->select('detail.*')
                    ->crossjoin(DB::raw("({$datadetail->toSql()}) as detail"))
                    ->orderBy("detail.tgl_transaksi");
        // dd($dt->toSql());


        $queryakhir = DB::connection($this->getConnectionName())->table(DB::raw("({$dt->toSql()}) as a"))
                    ->select(
                        '*',
                        DB::raw('@saldo := (@saldo+IFNULL(debit,0)-IFNULL(kredit,0)) as saldo'),
                        DB::raw('(@no := @no+1) as no_urut')
                    );

        
        $datatables = Datatables::of($queryakhir);
        return $datatables
        ->filter(function($query) use($request,$order_column,$order_dir){
            // dd($order_column);
            if($order_column == "tgl_transaksi"){
                $query->where('tgl_transaksi','LIKE','%'.$request->get('search')['value'].'%');
            }

            

            // }
            // $query->where(function($query) use($request){
            //     
            //     $query->Orwhere('akun.kode','LIKE','%'.$request->get('search')['value'].'%');
            // });
        })   
        ->editcolumn('debit', function($data){ return 'Rp. '.number_format($data->debit); })  
        ->editcolumn('kredit', function($data){ return 'Rp. '.number_format($data->kredit); })  
        ->editcolumn('no_transaksi', function($data){ 
            if(is_null($data->id_jenis_transaksi)){
                return "Jurnal Umum #".$data->no_transaksi.'<br><small class="text-muted">'.$data->deskripsi.'. '.$data->memo.'</small>';
            } else {
                $jns = JurnalUmumDetail::on($this->getConnectionName())->find($data->id);
                return $jns->jenis_transaksi->nama.' #'.$data->no_transaksi.'<br><small class="text-muted">'.$jns->jenis_transaksi->nama.' '.$data->kode_referensi.'</small>';
            }
        })
        ->editcolumn('tgl_transaksi', function($data){ 
            return Date("d-m-Y", strtotime($data->tgl_transaksi)); 
        })  
        ->editcolumn('is_tutup_buku', function($data){ 
            if($data->is_tutup_buku){
                return '<small class="text-success"><i class="fa fa-check"> Tutup Buku</i></small>';
            } else {
                return '<small class="text-muted"><i class="fa fa-clock"> Belum Tutup Buku</i></small>';
            }
        })  
        ->addcolumn('saldo', function($data){
            // dd($data->tgl_transaksi);
            $saldo = 0;

            /*$getdebit = JurnalUmumDetail::select(DB::RAW("SUM(debit) as total_debit"))
                    ->join("tb_jurnal_umum as j","j.id","id_jurnal")
                    ->whereRaw("j.tgl_transaksi <= '".$data->tgl_transaksi."'")
                    ->whereRaw("id_kode_akun = '".$data->id_kode_akun."'")
                    ->whereRaw("j.id_apotek = '".session("id_apotek_active")."'")
                    ->whereNull("tb_jurnal_umum_detail.deleted_by")
                    ->whereNull("j.deleted_by")
                    ->orderBy("j.tgl_transaksi")
                    ->groupBy("tb_jurnal_umum_detail.kode_referensi")
                    ->groupBy("tb_jurnal_umum_detail.debit")
                    ->first();
            // dd($getdebit);
            if(is_null($getdebit)){ $total_debit = 0; } else { $total_debit = $getdebit->total_debit ; }

            $getkredit = JurnalUmumDetail::select(DB::RAW("SUM(kredit) as total_kredit"))
                    ->join("tb_jurnal_umum as j","j.id","id_jurnal")
                    ->where("j.tgl_transaksi","<=",$data->tgl_transaksi)
                    ->where("id_kode_akun",$data->id_kode_akun)
                    ->whereRaw("j.id_apotek = '".session("id_apotek_active")."'")
                    ->whereNull("tb_jurnal_umum_detail.deleted_by")
                    ->whereNull("j.deleted_by")
                    ->orderBy("j.tgl_transaksi","desc")
                    ->groupBy("tb_jurnal_umum_detail.kode_referensi")
                    ->groupBy("tb_jurnal_umum_detail.id")
                    ->first();
            // dd($getkredit);
            if(is_null($getkredit)){ $total_kredit = 0; } else { $total_kredit = $getkredit->total_kredit ; }

            $saldo = $total_debit - $total_kredit;*/




            return 'Rp. '.number_format($data->saldo);
        }) 
        ->addcolumn('action', function($data) {
            if(!$data->is_tutup_buku){
                $btn = '<div class="btn-group">';
                $btn .= '<a href="'.url("jurnalumum/".Crypt::encrypt($data->id_jurnal)).'" class="btn btn-default" data-toggle="tooltip" data-placement="top" title="View Data"><i class="fa fa-search"></i></a>';
                $btn .= '<a href="'.url("jurnalumum/".Crypt::encrypt($data->id_jurnal)).'/edit" class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></a>';
                $btn .= '<span class="btn btn-danger" onClick="delete_detail(\''.Crypt::encrypt($data->id).'\')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
                $btn .='</div>';
                return $btn;
            }
        })    
        ->rawColumns(['action','is_tutup_buku','no_transaksi'])
        ->addIndexColumn()
        ->make(true);  
    }


    /*
        =======================================================================================
        For     : Menampilkan detil transaksi per kode akun
        Author  : Citra
        Date    : 16/09/2021
        =======================================================================================
    */
    public function show($id)
    {
        $idjurnal = Crypt::decrypt($id);
        $jurnal_umum = JurnalUmum::on($this->getConnectionName())->find($idjurnal);
        if(!empty($jurnal_umum)){
            return view('jurnal_umum.showDetail')->with(compact("jurnal_umum"));
        } else {
            echo "jurnal tidak ditemukan";
        }
    }



    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function edit($id)
    {
        $idjurnal = Crypt::decrypt($id);
        $jurnal_umum = JurnalUmum::on($this->getConnectionName())->find($idjurnal);
        if(!empty($jurnal_umum)){
            $kode_akun= MasterKodeAkun::select('id',DB::RAW('CONCAT(kode,\' - \',nama) as nama_akun'))->where('is_deleted', 0)->pluck('nama_akun', 'id');
            $kode_akun->prepend('-- Pilih Akun --','');

            return view('jurnal_umum.edit')->with(compact("jurnal_umum","kode_akun"));
        } else {
            echo "jurnal tidak ditemukan";
        }
    }

    /*
        =======================================================================================
        For     : Update jurnal umum
        Author  : Ayu Citra
        Date    : 20/09/2021
        =======================================================================================
    */
    public function update(Request $request, $id)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        DB::connection($this->getConnectionName())->beginTransaction();  
        try{
            // dd($request->input());
            $id = Crypt::decrypt($id);
            $jurnal_umum = JurnalUmum::on($this->getConnectionName())->find($id);
            $jurnal_umum->no_transaksi = $request->no_transaksi;
            $jurnal_umum->tgl_transaksi = $request->tgl_transaksi;
            $jurnal_umum->memo = $request->memo;
            // $jurnal_umum->id_jenis_transaksi = $request->id_jenis_transaksi;
            $jurnal_umum->tag = $request->tag;
            // $jurnal_umum->kode_referensi = $request->kode_referensi;
            $validator = $jurnal_umum->validate();
            if(!$validator->fails()){
                $jurnal_umum->updated_by = Auth::user()->id;
                $jurnal_umum->updated_at = Date("Y-m-d H:i:s");

                if($jurnal_umum->save()){
                    // echo "sukses";


                    // simpan detail //
                    $total_debit = 0;
                    $total_kredit = 0;
                    $listakun = array();
                    if(isset($request->id_kode_akun)){
                        foreach ($request->id_kode_akun as $key => $kode) {
                            if(!is_null($kode)){

                                if($request->iddetail[$key] == ""){
                                    $detail = new JurnalUmumDetail;
                                    $detail->setDynamicConnection();
                                    $detail->created_by = Auth::user()->id;
                                } else {
                                    $id = Crypt::decrypt($request->iddetail[$key]);
                                    $detail = JurnalUmumDetail::on($this->getConnectionName())->find($id);
                                    // dd($detail);
                                    $detail->updated_by = Auth::user()->id;
                                }
                                
                                $detail->id_jurnal = $jurnal_umum->id;
                                $detail->id_kode_akun = $kode;
                                $detail->deskripsi = $request->deskripsi[$key];
                                $detail->kredit = $request->kredit[$key];
                                $detail->debit = $request->debit[$key];
                                $total_debit = $total_debit + $detail->debit;
                                $total_kredit = $total_kredit + $detail->kredit;
                                $detail->save();

                                $listakun[] = $detail->id;
                            }
                        }

                        /* --- Hapus detail yang tidak ada dalam list --- */
                        $del = JurnalUmumDetail::on($this->getConnectionName())->where(function($query) use ($listakun,$jurnal_umum){
                            if(count($listakun)){
                                $query->whereNotIn("id",$listakun);
                                $query->where("id_jurnal",$jurnal_umum->id); // SRI | sepertinya selain akun yang diupdate kehapus semua kalau tanpa ini
                            } else {
                                $query->where("id_jurnal",$jurnal_umum->id);
                            }
                        }) ->update([
                            "deleted_by" => Auth::user()->id,
                            "deleted_at" => Date("Y-m-d H:i:s")
                        ]);
                    }

                    $jurnal_umum->total_debit = $total_debit;
                    $jurnal_umum->total_kredit = $total_kredit;
                    $jurnal_umum->save();



                    // simpan bukti //
                    $errorfile = 0;
                    $errorMessages = "";
                    $listbukti = array();

                    // dd($request->buktifile);

                    if(isset($request->idbukti)){
                        if(count($request->idbukti)){

                            foreach ($request->idbukti as $key => $bukti) {
                                
                                if($request->idbukti[$key] == ""){
                                    $bukti = $request->buktifile[$key];
                                    // dd($bukti);

                                    // $bukti->getMimeType() == "application/pdf" || 
                                    if($bukti->getMimeType() == "image/jpeg" || $bukti->getMimeType() == "image/jpg" || $bukti->getMimeType() == "image/png"){
                                        if(!empty($bukti)){
                                            $mime = $bukti->getMimeType();
                                            
                                            // dd($mime);
                                            $buktijurnal = new JurnalUmumBukti;
                                            $buktijurnal->setDynamicConnection();

                                            $nama_file = $bukti->getClientOriginalName();
                                            $split = explode('.', $nama_file);
                                            $ext = end($split);
                                            $file_name = md5($split[0] . "-" . Date("Y-m-d H:i:s"));
                                            // $logo = $request->img;
                                            $destination_path = public_path('temp\\');
                                            $destination_filename = $file_name . "." . $ext;
                                            // dd($destination_path);
                                            $path = $destination_path.$destination_filename;

                                            $bukti->move($destination_path, $destination_filename);
                                            $buktijurnal->file = $file_name . "." . $ext;

                                            // dd($bukti);

                                            if($mime == "application/pdf"){
                                                $fp = fopen($path,'r');
                                                $content = fread($fp, filesize($destination_path.$destination_filename));
                                                $content = addslashes($content);
                                                fclose($fp);
                                            } else {
                                                $content = file_get_contents($path);
                                                // $content = base64_encode($content);
                                            }

                                            $buktijurnal->type_file = $mime;
                                            $buktijurnal->file = base64_encode($content);
                                            $buktijurnal->created_by = Auth::user()->id;
                                            $buktijurnal->id_jurnal = $jurnal_umum->id;
                                            $buktijurnal->keterangan = $request->keterangan[$key];
                                            $buktijurnal->save();

                                            $listbukti[] = $buktijurnal->id;

                                            if(isset($path)){
                                                if (File::exists($path)) {
                                                    unlink($path);
                                                }
                                            }
                                        }
                                    } else {
                                        $errorfile++;
                                    }
                                
                                } else {
                                    $buktijurnal = JurnalUmumBukti::on($this->getConnectionName())->find(Crypt::decrypt($request->idbukti[$key]));
                                    $buktijurnal->updated_by = Auth::user()->id;
                                    $buktijurnal->updated_at = Date("Y-m-d H:i:s");
                                    $buktijurnal->keterangan = $request->keterangan[$key];
                                    $buktijurnal->save();
                                    $listbukti[] = $buktijurnal->id;
                                }


                            }
                        }
                    }

                    // dd($listbukti);

                    /* --- Hapus detail yang tidak ada dalam list --- */
                    $del = JurnalUmumBukti::on($this->getConnectionName())->where(function($query) use ($listbukti,$jurnal_umum){
                        if(count($listbukti)){
                            $query->whereNotIn("id",$listbukti);
                            $query->where("id_jurnal",$jurnal_umum->id); // SRI | sepertinya selain akun yang diupdate kehapus semua kalau tanpa ini
                        } else {
                            $query->where("id_jurnal",$jurnal_umum->id);
                        }
                    }) ->update([
                        "deleted_by" => Auth::user()->id,
                        "deleted_at" => Date("Y-m-d H:i:s")
                    ]);

                    if($errorfile > 0){ $errorMessages = "terdapat ".$errorfile." file bukti dengan ekstensi yang tidak sesuai"; }

                    DB::connection($this->getConnectionName())->commit();
                    echo json_encode(array("status" => 1,"errorMessages" => $errorMessages, "url" => 'jurnalumum'));


                } else {
                    echo json_encode(array("status" => 2));
                }
            } else {
                echo json_encode(array("status" => 2,"error"=>$validator->getMessage()));
            }
        } catch(\Exception $e){
            DB::connection($this->getConnectionName())->rollback();
            echo json_encode(array("status" => 2,"error"=>$e->getMessage()));
        }  
    }

    /*
        =======================================================================================
        For     : hapus detail jurnal
        Author  : Ayu Citra
        Date    : 21/09/2021
        =======================================================================================
    */
    public function destroy($id)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        $id = Crypt::decrypt($id);
        // dd($id);
        $detail = JurnalUmumDetail::on($this->getConnectionName())->find($id);
        $detail->deleted_at = date('Y-m-d H:i:s');
        $detail->deleted_by = Auth::user()->id;
        if($detail->save()){
            echo 1;
        }else{
            echo 0;
        }
    }

    /*
        =======================================================================================
        For     : hapus jurnal
        Author  : Ayu Citra
        Date    : 29/09/2021
        =======================================================================================
    */
    public function destroyJurnal($id)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        $id = Crypt::decrypt($id);
        // dd($id);
        $detail = JurnalUmum::on($this->getConnectionName())->find($id);
        $detail->deleted_at = date('Y-m-d H:i:s');
        $detail->deleted_by = Auth::user()->id;
        if($detail->save()){
            echo 1;
        }else{
            echo 0;
        }
    }



    /*
        =======================================================================================
        For     : Menampilkan halaman stok awal
        Author  : Citra
        Date    : 14/09/2021
        =======================================================================================
    */
    public function saldoawal()
    {
        $kategoriakun = MasterKategoriAkun::on($this->getConnectionName())->where('is_deleted', 0)->orderBy('nama')->get();
        // dd($kategoriakun);
        $jurnal_umum = new JurnalUmum;
        $jurnal_umum->setDynamicConnection();
        return view('jurnal_umum.saldoawal')->with(compact("kategoriakun","jurnal_umum"));
    }



    /*
        =======================================================================================
        For     : Menampilkan form stok awal
        Author  : Citra
        Date    : 14/09/2021
        =======================================================================================
    */
    public function getakun($id)
    {
        $id_kategori = Crypt::decrypt($id);
        $kategori = MasterKategoriAkun::on($this->getConnectionName())->find($id_kategori);
        $kode_akun = MasterKodeAkun::on($this->getConnectionName())->where("id_kategori_akun",$id_kategori)->whereNull("deleted_by")->get();

        $jurnal = JurnalUmum::on($this->getConnectionName())->where("is_saldoawal",1)->first();
        $detail = array();
        if(!is_null($jurnal)){
            if(!is_null($jurnal->detailjurnal)){
                $detail = $jurnal->detailjurnal->pluck('debit','id_kode_akun');
            }
        }

        $form = View::make('jurnal_umum.saldoawal_form',compact('kode_akun','kategori','detail'))->render();
        $status = 1;

        return json_encode(compact('status','form'));
    }



    /*
        =======================================================================================
        For     : Proses simpan stok awal
        Author  : Citra
        Date    : 14/09/2021
        =======================================================================================
    */
    public function saldoawalset(Request $request)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        // dd($request->input());
        if(isset($request->saldoawal)){
            $jurnal = JurnalUmum::on($this->getConnectionName())->where('is_saldoawal',1)->where('id_apotek',session('id_apotek_active'))->first();
            if(empty($jurnal)){
                $jurnal = new JurnalUmum;
                $jurnal->setDynamicConnection();
                $jurnal->tgl_transaksi = Date("Y-m-d");            
                $jurnal->created_by = Auth::user()->id;
                $jurnal->id_apotek = session('id_apotek_active');
                $jurnal->is_saldoawal = 1;
            } else {
                $jurnal = JurnalUmum::on($this->getConnectionName())->find($jurnal->id);       
                $jurnal->updated_by = Auth::user()->id;
            }

            $jurnal->save();

            if(count($request->saldoawal)){
                foreach ($request->saldoawal as $key => $value) {
                    $cekdetil = JurnalUmumDetail::on($this->getConnectionName())->where("id_jurnal",$jurnal->id)
                            ->where("id_kode_akun",$key)->first();
                    if(empty($cekdetil)){
                        $detil = new JurnalUmumDetail;
                        $detil->setDynamicConnection();
                        $detil->created_by = Auth::user()->id;
                    } else {
                        $detil = JurnalUmumDetail::on($this->getConnectionName())->find($cekdetil->id);                        
                        $detil->updated_by = Auth::user()->id;
                    }

                    $detil->id_jurnal = $jurnal->id;
                    $detil->id_kode_akun = $key;
                    $detil->deskripsi = "Stok Awal";
                    $detil->debit = $value;
                    $detil->save();
                }
            }

            echo json_encode(array("status"=>1));

        } else {
            echo json_encode(array("status"=>2, "error"=>"Data stok awal belum lengkap"));
        }





    }




    /*
        =======================================================================================
        For     : Proses tutup buku
        Author  : Citra
        Date    : 20/09/2021
        =======================================================================================
    */
    public function tutupbuku()
    {
        $tutupbuku = JurnalUmum::on($this->getConnectionName())->where("is_tutup_buku",0)
                    ->whereNull("deleted_by")
                    ->update([
                        "is_tutup_buku" => 1,
                        "tutup_buku_by" => Auth::user()->id,
                        "tutup_buku_at" => Date("Y-m-d H:i:s")
                    ]);
        if($tutupbuku){
            echo json_encode(array("status" => 1));
        } else {
            echo json_encode(array("status" => 2,"errorMessages" => "terjadi kesalahan. tidak dapat melanjutkan proses tutup buku"));
        }
    }




    /*
        =======================================================================================
        For     : Download template import xls
        Author  : Citra
        Date    : 22/09/2021
        =======================================================================================
    */
    public function gettemplate()
    {
        /*return Excel::download(new class() implements WithHeadings, WithColumnWidths, WithTitle {

            public function __construct()
            {
                
            }

            public function headings(): array
            {
                return ['No. Transaksi(*)', 'Tanggal Transaksi(*)', 'ID Jenis Transaksi', 'Kode Referensi / Kontak','tag', 'Memo', 'Kode Akun(*)', 'Deskripsi(*)', 'Kredit (tanpa titik)', 'Debit (tanpa titik)'];
            }

            public function columnWidths(): array
            {
                return [
                    'A' => 15,
                    'B' => 20,
                    'C' => 20,
                    'D' => 30,
                    'E' => 30,
                    'F' => 20,
                    'G' => 20,        
                    'H' => 20,        
                    'I' => 20,        
                    'J' => 20        
                ];
            }

            
            public function title(): string
            {
                return 'Template Import';
            }

        },"Template Import Jurnal Umum.xlsx");*/



        return (new JurnalUmumTemplateExportSheet())->download('Template Import Jurnal Umum.xlsx');




    }



    /*
        =======================================================================================
        For     : Import xls form
        Author  : Citra
        Date    : 22/09/2021
        =======================================================================================
    */
    public function ImportJurnal()
    {
        return view('jurnal_umum._import_data');
    }



    /*
        =======================================================================================
        For     : Import xls prosess
        Author  : Citra
        Date    : 22/09/2021
        =======================================================================================
    */
    public function import_jurnal_from_excel(Request $request)
    {
        // dd($request->import_file);
        if(Input::hasFile('import_file')){
            $path = Input::file('import_file')->getRealPath();

            $importxls = new JurnalUmumImportSheet;
            $import = Excel::import($importxls, $path);
            // dd($importxls->importstatus[0]->importstatus);

            // return status //
            if(isset($importxls->importstatus[0]->importstatus['jurnalimport_ok'])){
                $status = $importxls->importstatus[0]->importstatus;
            } else if(isset($importxls->importstatus[1]->importstatus['jurnalimport_ok'])){
                $status = $importxls->importstatus[1]->importstatus;
            }

            $keterangan = "Berhasil import : ".$status['jurnalimport_ok']." Baris <br> Gagal import : ".$status['jurnalimport_error']." Baris <br> Baris yang sama : ".$status['duplicatedata']." Baris";

            $status = array("status"=>1,"keterangan"=>$keterangan);
            
            echo json_encode($status);
        }
    }



    /*
        =======================================================================================
        For     : Print PDF
        Author  : Citra
        Date    : 29/09/2021
        =======================================================================================
    */
    public function Printpdf($id)
    {
        $id = Crypt::decrypt($id);
        $jurnal_umum = JurnalUmum::on($this->getConnectionName())->find($id);
        if(!empty($jurnal_umum)){

            
            
        } else {
            echo "Jurnal tidak ditemukan";
        }
    }





    /*
        =======================================================================================
        For     : Halaman Index Reload Data
        Author  : Citra
        Date    : 22/10/2021
        =======================================================================================
    */
    public function ReloadDataIndex()
    {
        $listreload = ReloadDataStatus::on($this->getConnectionName())->where('is_deleted',0)->get();
        $apotek = MasterApotek::on($this->getConnectionName())->find(session("id_apotek_active"));

        $tgl_nota = Date('Y-m-d');
        // $tgl_nota = '2021-07-15';

        return view('jurnal_umum.reloaddata_index')->with(compact('listreload','apotek','tgl_nota'));
    }



    /*
        =======================================================================================
        For     : Proses Reload Data
        Author  : Citra
        Date    : 22/10/2021
        =======================================================================================
    */
    public function ReloadDataProcess(Request $request)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        // dd($request->input());
        $id_apotek = session('id_apotek_active');
        $tgl_nota = Date('Y-m-d');
        // $tgl_nota = '2021-07-15';

        $id = Crypt::decrypt($request->i);

        try {


            /* case nya id di tb_reloaddata */
            switch ($id) {
                
                case '1':
                    // echo "--- Reload Piutang Usaha ---";
                    // testing data tgl : 2021-08-07
                    // $tgl_nota = '2021-08-07';  
                    $status = $this->reloadpiutangtransaksi($id,$id_apotek,$tgl_nota);
                break;
                
                case '2':
                    // echo "--- Reload Piutang Belum Ditagih ---";
                    // testing data tgl : 2021-07-15
                    // $tgl_nota = '2021-07-15';  
                    $status = $this->reloadpiutangbelumditagih($id,$id_apotek,$tgl_nota);
                break;
                
                case '3':
                    // echo "--- Reload Piutang Antar Outlet ---";
                    // testing data tgl : 2021-07-15
                    $tgl_nota = '2021-07-15'; 
                    $status = $this->reloadpiutangantaroutlet($id,$id_apotek,$tgl_nota);
                break;
                
                case '4':
                    // echo "--- Reload Persediaan Barang ---";
                    $status = $this->reloadpersediaanbarang($id,$id_apotek,$tgl_nota);
                break;
                
                case '5':
                    // echo "--- Reload Hutang Usaha ---";
                    // testing data tgl : 2021-08-10
                    // $tgl_nota = '2021-08-10';
                    $status = $this->reloadhutangusaha($id,$id_apotek,$tgl_nota);
                break;
                
                case '6':
                    // echo "--- Reload Hutang Belum Ditagih ---";
                    // testing data tgl : 2021-08-13
                    // $tgl_nota = '2021-08-13';
                    $status = $this->reloadhutangusahabelumditagih($id,$id_apotek,$tgl_nota);
                break;
                
                case '7':
                    // echo "--- Reload Hutang Belum Ditagih ---";
                    // testing data tgl : 2021-08-13
                    // $tgl_nota = '2021-08-13';
                    $status = $this->reloadhutangantaroutlet($id,$id_apotek,$tgl_nota);
                break;
                
                case '8':
                    // echo "--- Reload Penjualan ---";
                    // testing data tgl : 2021-08-13
                    // $tgl_nota = '2021-08-13';
                    $status = $this->reloadpenjualan($id,$id_apotek,$tgl_nota);
                break;
                
                case '9':
                    // echo "--- Reload Diskon Penjualan ---";
                    // ------------ SKIP ----------------------- //
                    $status = $this->reloaddiskonpenjualan($id,$id_apotek,$tgl_nota);
                break;
                
                case '10':
                    // echo "--- Reload Retur Penjualan ---";
                    // testing data tgl : 2021-07-01
                    $tgl_nota = '2021-07-01';
                    $status = $this->reloadreturpenjualan($id,$id_apotek,$tgl_nota);
                break;
                
                case '11':
                    // echo "--- Reload Harga Pokok Penjualan ---";
                    // ------------ SKIP ----------------------- //
                    $status = $this->reloadhargapokokpenjualan($id,$id_apotek,$tgl_nota);
                break;
                
                case '12':
                    // echo "--- Reload Pembelian ---";
                    // ------------ SKIP ----------------------- //
                    $status = $this->reloadpembelian($id,$id_apotek,$tgl_nota);
                break;
                
                case '13':
                    // echo "--- Reload Retur Pembelian ---";
                    // testing data tgl : 2021-07-24
                    $tgl_nota = '2021-07-24';
                    $status = $this->reloadreturpembelian($id,$id_apotek,$tgl_nota);
                break;
                
                /*case '14':
                    // echo "--- Reload Pemusnahan Obat ---";
                    $status = $this->reloadpemusnahanobat($id,$id_apotek,$tgl_nota);
                break;*/

                default:
                    $status = array("status"=> 2, "keterangan"=> "Modul belum ready");
                break;
            }

            echo json_encode($status);

        } catch (Exception $e) {
            DB::connection($this->getConnectionName())->rollback();
            echo json_encode(array("status" => 2, "message" => "Error :: ".$e->getMessage()));
        }

    }


    /*
        =======================================================================================
        For     : Fungsi untuk cek reload status
        Author  : Citra
        Date    : 23/10/2021
        =======================================================================================
    */
    protected function CheckReloadStatus($var_reloadstatus)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        /* 
        * 1. cek apakah ada data reload status. cek : id_apotek, tglreload, id_reloaddata 
        * 2. kalau tidak ada new $dtreloadstatus.
        * 3. return $dtreloadstatus.
        */

        // dd($var_reloadstatus);
        $CheckReloadStatus = ReloadDataStatusDetail::on($this->getConnectionName())->where($var_reloadstatus)->first();
        // dd($CheckReloadStatus);
        if(is_null($CheckReloadStatus)){
            
            $dtreloadstatus = new ReloadDataStatusDetail;
            $dtreloadstatus->setDynamicConnection();
            $dtreloadstatus->id_apotek = $var_reloadstatus['id_apotek'] ;
            $dtreloadstatus->tglreload = $var_reloadstatus['tglreload'] ;
            $dtreloadstatus->id_reloaddata = $var_reloadstatus['id_reloaddata'] ;

            if(isset($var_reloadstatus['jenis'])){ $dtreloadstatus->jenis = $var_reloadstatus['jenis'] ; }
            

        } else {
            $dtreloadstatus = ReloadDataStatusDetail::on($this->getConnectionName())->find($CheckReloadStatus->id);
        }

        $dtreloadstatus->updated_by = Auth::user()->id;
        $dtreloadstatus->save();        

        return $dtreloadstatus;

    }


    /*
        =======================================================================================
        For     : Fungsi save data yang di load ke jurnal
        Author  : Citra
        Date    : 23/10/2021
        =======================================================================================
    */
    public function saveLoadData($param_jurnal,$param_detail)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        try {
            

            if(count($param_detail)){

                DB::connection($this->getConnectionName())->beginTransaction(); 

                $savejurnal = new JurnalUmum;
                $savejurnal->setDynamicConnection();
                $status = $savejurnal->saveLoadDataToJurnal($param_jurnal,$param_detail);
                // dd($status);

                DB::connection($this->getConnectionName())->commit();

            } else {
                $status = array("status" => 2, "keterangan" => "data detail kosong");
            }


            return $status;
           
        } catch (Exception $e) {
            DB::connection($this->getConnectionName())->rollback();
            echo json_encode(array("status" => 2, "message" => "Error :: ".$e->getMessage()));
        }
    }



    /*
        =======================================================================================
        For     : Proses Reload Data Piutang Usaha
        Author  : Citra
        Date    : 22/10/2021
        =======================================================================================
    */
    public function reloadpiutangtransaksi($id,$id_apotek,$tgl_nota)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        # ----- PIUTANG USAHA -------------------------------- #
        # masuk ke akun id : 7 => 1-10100 - Piutang Usaha (debit) 
        # masuk ke akun id : 81 => 4-40100 - Diskon Penjualan (debit - dikurangi) 
        # masuk ke akun id : 17 => 1-10200 - Persediaan Barang (kredit)
        # -----------------------------------------------------#

        $id_jenis_transaksi = null;

        /* --- query get piutang --- */
        $getData = DB::connection($this->getConnectionName())->table("tb_detail_nota_penjualan as a")
                    ->selectRaw("
                        SUM(a.jumlah * a.harga_jual) as total,
                        SUM((b.diskon_persen/100)*(a.jumlah * a.harga_jual)) as diskon
                    ")
                    ->join("tb_nota_penjualan as b","b.id","a.id_nota")
                    ->whereRaw("a.is_deleted = 0")
                    ->whereRaw("a.is_cn = 0")
                    ->whereRaw("b.is_deleted = 0")
                    ->whereRaw("b.is_kredit = 1")
                    ->whereRaw("b.is_lunas_pembayaran_kredit = 0")
                    ->whereRaw("b.send_invoice = 1")
                    ->whereRaw("b.id_apotek_nota = '".$id_apotek."'")
                    ->whereRaw("b.tgl_nota = '".$tgl_nota."'")
                    ->get();
        // dd($getData);

        // **** cek reload status **** //
        $var_reloadstatus = array("id_apotek"=>$id_apotek, "tglreload"=>$tgl_nota, "id_reloaddata"=>$id);
        $CheckReloadStatus = $this->CheckReloadStatus($var_reloadstatus);
        // dd($CheckReloadStatus);
        // **** cek reload status **** //


        /* **** Save to jurnal ***** 
        * param_jurnal format :
        * array(
        *   "is_reloaded" => "",
        *   "kode_referensi" => "",
        *   "tgl_transaksi" => ""
        * )
        */
        $param_jurnal = array(
            "kode_referensi" => $CheckReloadStatus->id,
            "tgl_transaksi" => $tgl_nota,
            "id_apotek" => $id_apotek
        );
                
        /*
        * param_detail format :
        * array(
        *   "id_jurnal" => "",
        *   "id_jenis_transaksi" => "",
        *   "kode_referensi" => "",
        *   "id_kode_akun" => "",
        *   "kredit" => ""/ "debit" => ""
        *   "is_reloaded" => "",
        * )
        */
        $param_detail = array();
        foreach ($getData as $key => $value) {
            if($value->total > 0){
                $param_detail[] = array(
                    "id_jenis_transaksi" => $id_jenis_transaksi,
                    "kode_referensi" => $CheckReloadStatus->id,
                    "id_kode_akun" => 7,
                    "debit" => $value->total
                );

                $param_detail[] = array(
                    "id_jenis_transaksi" => $id_jenis_transaksi,
                    "kode_referensi" => $CheckReloadStatus->id,
                    "id_kode_akun" => 81,
                    "is_dikurang" => 1,
                    "debit" => $value->diskon
                );

                $param_detail[] = array(
                    "id_jenis_transaksi" => $id_jenis_transaksi,
                    "kode_referensi" => $CheckReloadStatus->id,
                    "id_kode_akun" => 17,
                    "kredit" => ($value->total-$value->diskon)
                );
            } 
        }


        if(count($param_detail)){
            $status = $this->saveLoadData($param_jurnal,$param_detail);
            $status['keterangan'] = "total : Rp. ".number_format($value->total-$value->diskon);
        } else {
            $status = array("status" => 3, "keterangan" => "Tidak dilakukan reload data karena total piutang usaha Rp. 0");
        }
        
        // dd($status);

        /**** Update reloaddata status ****/
        $updatestatus = ReloadDataStatusDetail::on($this->getConnectionName())->find($CheckReloadStatus->id);
        $updatestatus->status = $status['status'];
        $updatestatus->keterangan = $status['keterangan'];
        $updatestatus->tglreload = $tgl_nota;
        $updatestatus->updated_by = Auth::user()->id;
        $updatestatus->save();

        return $status;
        // dd($status);
    }




    /*
        =======================================================================================
        For     : Proses Reload Piutang belum ditagih
        Author  : Citra
        Date    : 25/10/2021
        =======================================================================================
    */
    public function reloadpiutangbelumditagih($id,$id_apotek,$tgl_nota)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        # ----- PIUTANG BELUM DITAGIH ------------------------- #
        # masuk ke akun id : 8 => 1-10101 - Piutang Belum Ditagih (debit)
        # masuk ke akun id : 81 => 4-40100 - Diskon Penjualan (kredit - dikurangi) 
        # masuk ke akun id : 79 => 4-40000 - Penjualan (kredit)
        # masuk ke jenis transaksi id : 1 => Penjualan, jenis => "belum ditagih"
        # ----------------------------------------------------- #

        // $id_kode_akun = 8;
        $id_jenis_transaksi = null;


        $getData = DB::connection($this->getConnectionName())->table("tb_detail_nota_penjualan as a")
                    ->selectRaw("
                        SUM(a.jumlah * a.harga_jual) as total,
                        SUM((b.diskon_persen/100)*(a.jumlah * a.harga_jual)) as diskon
                    ")
                    ->join("tb_nota_penjualan as b","b.id","a.id_nota")
                    ->whereRaw("a.is_deleted = 0")
                    ->whereRaw("a.is_cn = 0")
                    ->whereRaw("b.is_deleted = 0")
                    ->whereRaw("b.is_kredit = 1")
                    ->whereRaw("b.is_lunas_pembayaran_kredit = 0")
                    ->whereRaw("b.send_invoice = 0")
                    ->whereRaw("b.id_apotek_nota = '".$id_apotek."'")
                    ->whereRaw("b.tgl_nota = '".$tgl_nota."'")
                    ->get();
        // dd($getData);

        // **** cek reload status **** //
        $var_reloadstatus = array("id_apotek"=>$id_apotek, "tglreload"=>$tgl_nota, "id_reloaddata"=>$id, "jenis"=>"belum ditagih");
        $CheckReloadStatus = $this->CheckReloadStatus($var_reloadstatus);
        // dd($CheckReloadStatus);
        // **** cek reload status **** //


        /* **** Save to jurnal ***** 
        * param_jurnal format :
        * array(
        *   "is_reloaded" => "",
        *   "kode_referensi" => "",
        *   "tgl_transaksi" => ""
        * )
        */
        $param_jurnal = array(
            "kode_referensi" => $CheckReloadStatus->id,
            "tgl_transaksi" => $tgl_nota,
            "id_apotek" => $id_apotek
        );
                
        /*
        * param_detail format :
        * array(
        *   "id_jurnal" => "",
        *   "id_jenis_transaksi" => "",
        *   "kode_referensi" => "",
        *   "id_kode_akun" => "",
        *   "kredit" => ""/ "debit" => ""
        *   "is_reloaded" => "",
        * )
        */
        $param_detail = array();
        foreach ($getData as $key => $value) {
            if($value->total > 0){
                $param_detail[] = array(
                    "id_jenis_transaksi" => $id_jenis_transaksi,
                    "kode_referensi" => $CheckReloadStatus->id,
                    "id_kode_akun" => 8,
                    "debit" => ($value->total-$value->diskon)
                );

                $param_detail[] = array(
                    "id_jenis_transaksi" => $id_jenis_transaksi,
                    "kode_referensi" => $CheckReloadStatus->id,
                    "id_kode_akun" => 79,
                    "kredit" => $value->total
                );

                $param_detail[] = array(
                    "id_jenis_transaksi" => $id_jenis_transaksi,
                    "kode_referensi" => $CheckReloadStatus->id,
                    "id_kode_akun" => 81,
                    "is_dikurang" => 1,
                    "kredit" => $value->diskon
                );
            }
        }

        if(count($param_detail)){
            $status = $this->saveLoadData($param_jurnal,$param_detail);            
            $status['keterangan'] = "total : Rp. ".number_format($value->total-$value->diskon);
        } else {
            $status = array("status" => 3, "keterangan" => "Tidak dilakukan reload data karena total piutang belum ditagih Rp. 0");
        }
        
        // dd($status);

        /**** Update reloaddata status ****/
        $updatestatus = ReloadDataStatusDetail::on($this->getConnectionName())->find($CheckReloadStatus->id);
        $updatestatus->status = $status['status'];
        $updatestatus->keterangan = $status['keterangan'];
        $updatestatus->tglreload = $tgl_nota;
        $updatestatus->updated_by = Auth::user()->id;
        $updatestatus->save();

        return $status;
        // dd($status);
    }




    /*
        =======================================================================================
        For     : Proses Reload Piutang Antar Outlet
        Author  : Citra
        Date    : 28/10/2021
        =======================================================================================
    */
    public function reloadpiutangantaroutlet($id,$id_apotek,$tgl_nota)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        # ----- PIUTANG ANTAR OUTLET ------------------------- #
        # masuk ke akun id : 10 s/d 15 => 1-10103 s/d 1-10108 - Piutang Antar-Outlet [x] (debit)
        # masuk ke akun id : 17 => 1-10200 - Persediaan Barang (kredit)
        # ----------------------------------------------------- #

        $id_kode_akun = 8;
        $id_jenis_transaksi = 1;

        # --- select akun piutang per outlet --- #
        $getakunoutlet = MasterKodeAkun::whereRaw('nama LIKE \'%piutang antar%\'')
                        ->whereRaw('id_relasi_apotek != '.$id_apotek)->get();
        // dd($getakunoutlet);

        if(!empty($getakunoutlet)){
            foreach ($getakunoutlet as $key => $value) {

                // **** cek reload status **** //
                $var_reloadstatus = array("id_apotek"=>$id_apotek, "tglreload"=>$tgl_nota, "id_reloaddata"=>$id, "jenis"=>$value->nama);
                $CheckReloadStatus = $this->CheckReloadStatus($var_reloadstatus);
                // dd($CheckReloadStatus);
                // **** cek reload status **** //

                /* **** Save to jurnal ***** 
                * param_jurnal format :
                * array(
                *   "is_reloaded" => "",
                *   "kode_referensi" => "",
                *   "tgl_transaksi" => ""
                * )
                */
                $param_jurnal = array(
                    "kode_referensi" => $CheckReloadStatus->id,
                    "tgl_transaksi" => $tgl_nota,
                    "id_apotek" => $id_apotek
                );
                
                $getData = DB::connection($this->getConnectionName())->table("tb_detail_nota_transfer_outlet as a")
                    ->selectRaw("SUM(a.jumlah * a.harga_outlet) as total")
                    ->join("tb_nota_transfer_outlet as b","b.id","a.id_nota")
                    ->whereRaw("a.is_deleted = 0")
                    ->whereRaw("b.is_deleted = 0")
                    ->whereRaw("b.id_apotek_nota = '".$id_apotek."'")
                    ->whereRaw("b.id_apotek_tujuan = '".$value->id_relasi_apotek."'")
                    ->whereRaw("b.tgl_nota = '".$tgl_nota."'")
                    ->get();
                // dd($getData);


                /*
                * param_detail format :
                * array(
                *   "id_jurnal" => "",
                *   "id_jenis_transaksi" => "",
                *   "kode_referensi" => "",
                *   "id_kode_akun" => "",
                *   "kredit" => ""/ "debit" => ""
                *   "is_reloaded" => "",
                * )
                */
                $param_detail = array();

                if($getData->count()){
                    foreach ($getData as $key => $detail) {
                        if($detail->total > 0){

                            $param_detail[] = array(
                                "id_jenis_transaksi" => $id_jenis_transaksi,
                                "kode_referensi" => $CheckReloadStatus->id,
                                "id_kode_akun" => $value->id,
                                "debit" => $detail->total
                            ); 

                            $param_detail[] = array(
                                "id_jenis_transaksi" => $id_jenis_transaksi,
                                "kode_referensi" => $CheckReloadStatus->id,
                                "id_kode_akun" => 17,
                                "kredit" => $detail->total
                            ); 

                        }
                    }
                }


                if(count($param_detail)){
                    $status = $this->saveLoadData($param_jurnal,$param_detail);
                    $status['keterangan'] = "total : Rp. ".number_format($detail->total);
                    // dd($status);
                } else {
                    $status = array("status" => 3, "keterangan" => "Tidak dilakukan reload data karena total piutang outlet Rp. 0");
                }


                /**** Update reloaddata status ****/
                $updatestatus = ReloadDataStatusDetail::on($this->getConnectionName())->find($CheckReloadStatus->id);
                $updatestatus->status = $status['status'];
                $updatestatus->keterangan = $status['keterangan'];
                $updatestatus->tglreload = $tgl_nota;
                $updatestatus->updated_by = Auth::user()->id;
                $updatestatus->save();


            }
        }

        return $status;
    }



    /*
        =======================================================================================
        For     : Proses Reload Persediaan Barang
        Author  : Citra
        Date    : 26/10/2021
        =======================================================================================
    */
    public function reloadpersediaanbarang($id,$id_apotek,$tgl_nota)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        # ----- PERSEDIAAN BARANG ----------------------------- #
        # id akun 84 : 5-50000 Harga Pokok Penjualan (debit)
        # id akun 17 : 1-10200 Persediaan Barang (kredit)
        # ----------------------------------------------------- #

        $id_jenis_transaksi = null;

        $apotek = MasterApotek::on($this->getConnectionName())->find($id_apotek);
        if(is_null($apotek)){

            echo json_encode(array("status"=>2, "errorMessages" => "Data Apotek tidak ditemukan."));

        } else {

           $getData = DB::connection($this->getConnectionName())->table("tb_m_stok_harga_".$apotek->nama_singkat)
                    ->selectRaw("SUM((stok_akhir * harga_beli_ppn)) AS total")
                    ->whereRaw("is_deleted = 0")
                    ->get(); 
            // dd($getData);


            // **** cek reload status **** //
            $var_reloadstatus = array("id_apotek"=>$id_apotek, "tglreload"=>$tgl_nota, "id_reloaddata"=>$id);
            $CheckReloadStatus = $this->CheckReloadStatus($var_reloadstatus);
            // dd($CheckReloadStatus);
            // **** cek reload status **** //


            /* **** Save to jurnal ***** 
            * param_jurnal format :
            * array(
            *   "is_reloaded" => "",
            *   "kode_referensi" => "",
            *   "tgl_transaksi" => ""
            * )
            */
            $param_jurnal = array(
                "kode_referensi" => $CheckReloadStatus->id,
                "tgl_transaksi" => $tgl_nota,
                "id_apotek" => $id_apotek
            );
                    
            /*
            * param_detail format :
            * array(
            *   "id_jurnal" => "",
            *   "id_jenis_transaksi" => "",
            *   "kode_referensi" => "",
            *   "id_kode_akun" => "",
            *   "kredit" => ""/ "debit" => ""
            *   "is_reloaded" => "",
            * )
            */

            $param_detail = array();
            foreach ($getData as $key => $value) {
                if($value->total > 0){
                # --- Persediaan Barang (kredit) --- #
                    $param_detail[] = array(
                        "id_jenis_transaksi" => $id_jenis_transaksi,
                        "kode_referensi" => $CheckReloadStatus->id,
                        "id_kode_akun" => 17,
                        "kredit" => $value->total
                    );

                    # --- Harga Pokok Penjualan (debit) --- #
                    $param_detail[] = array(
                        "id_jenis_transaksi" => $id_jenis_transaksi,
                        "kode_referensi" => $CheckReloadStatus->id,
                        "id_kode_akun" => 84,
                        "debit" => $value->total
                    );

                }
            }

            if(count($param_detail)){
                $status = $this->saveLoadData($param_jurnal,$param_detail);
                $status['keterangan'] = "total : Rp. ".number_format($value->total);
            } else {
                $status = array("status" => 3, "keterangan" => "Tidak dilakukan reload data karena total persediaan barang ditagih Rp. 0");
            }

            // dd($status);

            /**** Update reloaddata status ****/
            $updatestatus = ReloadDataStatusDetail::on($this->getConnectionName())->find($CheckReloadStatus->id);
            $updatestatus->status = $status['status'];
            $updatestatus->keterangan = $status['keterangan'];
            $updatestatus->tglreload = $tgl_nota;
            $updatestatus->updated_by = Auth::user()->id;
            $updatestatus->save();

            return $status;

        }

        
        
    }




    /*
        =======================================================================================
        For     : Proses Reload Hutang Usaha
        Author  : Citra
        Date    : 26/10/2021
        =======================================================================================
    */
    public function reloadhutangusaha($id,$id_apotek,$tgl_nota)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        # ----- HUTANG USAHA --------------------------------------------------- #
        # akun id : 46, 2-20100 Hutang Usaha (kredit) => total
        # akun id : 24, 1-10500 PPN Masukan (kredit) => ppn
        # akun id : 85, 5-50100 Diskon Pembelian (kredit) => diskon
        # akun id : 17, 1-10200 Persediaan Barang (debit) => total - diskon + ppn
        # ----------------------------------------------------------------------- #

        $id_jenis_transaksi = null;

        $getData = DB::connection($this->getConnectionName())->table("tb_detail_nota_pembelian as a")
                    ->selectRaw("SUM(a.jumlah * a.harga_beli) AS total")
                    ->selectRaw("SUM((b.ppn / 100) * (a.jumlah * a.harga_beli - (b.diskon1 + b.diskon2))) AS ppn_masukan ")
                    ->selectRaw("SUM(b.diskon1 + b.diskon2) AS diskon ")
                    ->join("tb_nota_pembelian as b","b.id","a.id_nota")
                    ->whereRaw("a.is_deleted = 0")
                    ->whereRaw("b.id_apotek_nota = '".$id_apotek."'")
                    ->whereRaw("b.tgl_nota = '".$tgl_nota."'")
                    ->whereRaw("b.id_jenis_pembelian = 2")
                    ->whereRaw("b.is_tanda_terima  = 1")
                    ->whereRaw("b.is_lunas  = 0")
                    ->get();
        // dd($getData);

        // **** cek reload status **** //
        $var_reloadstatus = array("id_apotek"=>$id_apotek, "tglreload"=>$tgl_nota, "id_reloaddata"=>$id);
        $CheckReloadStatus = $this->CheckReloadStatus($var_reloadstatus);
        // dd($CheckReloadStatus);
        // **** cek reload status **** //

        /* **** Save to jurnal ***** 
        * param_jurnal format :
        * array(
        *   "is_reloaded" => "",
        *   "kode_referensi" => "",
        *   "tgl_transaksi" => ""
        * )
        */
        $param_jurnal = array(
            "kode_referensi" => $CheckReloadStatus->id,
            "tgl_transaksi" => $tgl_nota,
            "id_apotek" => $id_apotek
        );
                
        /*
        * param_detail format :
        * array(
        *   "id_jurnal" => "",
        *   "id_jenis_transaksi" => "",
        *   "kode_referensi" => "",
        *   "id_kode_akun" => "",
        *   "kredit" => ""/ "debit" => ""
        *   "is_reloaded" => "",
        * )
        */
        $param_detail = array();
        foreach ($getData as $key => $value) {
            if($value->total > 0){

                $param_detail[] = array(
                    "id_jenis_transaksi" => $id_jenis_transaksi,
                    "kode_referensi" => $CheckReloadStatus->id,
                    "id_kode_akun" => 46,
                    "kredit" => $value->total
                ); 

                $param_detail[] = array(
                    "id_jenis_transaksi" => $id_jenis_transaksi,
                    "kode_referensi" => $CheckReloadStatus->id,
                    "id_kode_akun" => 24,
                    "kredit" => $value->ppn_masukan
                ); 

                $param_detail[] = array(
                    "id_jenis_transaksi" => $id_jenis_transaksi,
                    "kode_referensi" => $CheckReloadStatus->id,
                    "id_kode_akun" => 85,
                    "is_dikurang" => 1,
                    "kredit" => $value->diskon
                ); 

                $param_detail[] = array(
                    "id_jenis_transaksi" => $id_jenis_transaksi,
                    "kode_referensi" => $CheckReloadStatus->id,
                    "id_kode_akun" => 17,
                    "debit" => ($value->total-$value->diskon+$value->ppn_masukan)
                ); 


            }
        }

        if(count($param_detail)){
            $status = $this->saveLoadData($param_jurnal,$param_detail);
            $status['keterangan'] = "total : Rp. ".number_format($value->total-$value->diskon+$value->ppn_masukan);
        } else {
            $status = array("status" => 3, "keterangan" => "Tidak dilakukan reload data karena total hutang Rp. 0");
        }
        
        // dd($status);

        /**** Update reloaddata status ****/
        $updatestatus = ReloadDataStatusDetail::on($this->getConnectionName())->find($CheckReloadStatus->id);
        $updatestatus->status = $status['status'];
        $updatestatus->keterangan = $status['keterangan'];
        $updatestatus->tglreload = $tgl_nota;
        $updatestatus->updated_by = Auth::user()->id;
        $updatestatus->save();

        return $status;
        // dd($status);

    }




    /*
        =======================================================================================
        For     : Proses Reload Hutang Usaha Belum Ditagih
        Author  : Citra
        Date    : 26/10/2021
        =======================================================================================
    */
    public function reloadhutangusahabelumditagih($id,$id_apotek,$tgl_nota)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        # ----- HUTANG USAHA BELUM DITAGIH -------------------- #
        # akun id : 47, 2-20211 Hutang Belum Ditagih (kredit) => total
        # akun id : 24, 1-10500 PPN Masukan (kredit) => ppn
        # akun id : 85, 5-50100 Diskon Pembelian (kredit) => diskon
        # akun id : 17, 1-10200 Persediaan Barang (debit) => total - diskon + ppn
        # ----------------------------------------------------- #

        $id_jenis_transaksi = null;

        $getData = DB::connection($this->getConnectionName())->table("tb_detail_nota_pembelian as a")
                    ->selectRaw("SUM(a.jumlah * a.harga_beli) AS total")
                    ->selectRaw("SUM((b.ppn / 100) * (a.jumlah * a.harga_beli - (b.diskon1 + b.diskon2))) AS ppn_masukan ")
                    ->selectRaw("SUM(b.diskon1 + b.diskon2) AS diskon ")
                    ->join("tb_nota_pembelian as b","b.id","a.id_nota")
                    ->whereRaw("a.is_deleted = 0")
                    ->whereRaw("b.id_apotek_nota = '".$id_apotek."'")
                    ->whereRaw("b.tgl_nota = '".$tgl_nota."'")
                    ->whereRaw("b.id_jenis_pembelian = 2")
                    ->whereRaw("b.is_tanda_terima  = 0")
                    ->whereRaw("b.is_lunas  = 0")
                    ->get();
        // dd($getData);

        // **** cek reload status **** //
        $var_reloadstatus = array("id_apotek"=>$id_apotek, "tglreload"=>$tgl_nota, "id_reloaddata"=>$id);
        $CheckReloadStatus = $this->CheckReloadStatus($var_reloadstatus);
        // dd($CheckReloadStatus);
        // **** cek reload status **** //

        /* **** Save to jurnal ***** 
        * param_jurnal format :
        * array(
        *   "is_reloaded" => "",
        *   "kode_referensi" => "",
        *   "tgl_transaksi" => ""
        * )
        */
        $param_jurnal = array(
            "kode_referensi" => $CheckReloadStatus->id,
            "tgl_transaksi" => $tgl_nota,
            "id_apotek" => $id_apotek
        );
                
        /*
        * param_detail format :
        * array(
        *   "id_jurnal" => "",
        *   "id_jenis_transaksi" => "",
        *   "kode_referensi" => "",
        *   "id_kode_akun" => "",
        *   "kredit" => ""/ "debit" => ""
        *   "is_reloaded" => "",
        * )
        */
        $param_detail = array();
        foreach ($getData as $key => $value) {
            if($value->total > 0){

                $param_detail[] = array(
                    "id_jenis_transaksi" => $id_jenis_transaksi,
                    "kode_referensi" => $CheckReloadStatus->id,
                    "id_kode_akun" => 47,
                    "kredit" => $value->total
                ); 

                $param_detail[] = array(
                    "id_jenis_transaksi" => $id_jenis_transaksi,
                    "kode_referensi" => $CheckReloadStatus->id,
                    "id_kode_akun" => 24,
                    "kredit" => $value->ppn_masukan
                ); 

                $param_detail[] = array(
                    "id_jenis_transaksi" => $id_jenis_transaksi,
                    "kode_referensi" => $CheckReloadStatus->id,
                    "id_kode_akun" => 85,
                    "is_dikurang" => 1,
                    "kredit" => $value->diskon
                ); 

                $param_detail[] = array(
                    "id_jenis_transaksi" => $id_jenis_transaksi,
                    "kode_referensi" => $CheckReloadStatus->id,
                    "id_kode_akun" => 17,
                    "debit" => ($value->total-$value->diskon+$value->ppn_masukan)
                );  


            }
        }

        if(count($param_detail)){
            $status = $this->saveLoadData($param_jurnal,$param_detail);
            $status['keterangan'] = "total : Rp. ".number_format($value->total-$value->diskon+$value->ppn_masukan);
        } else {
            $status = array("status" => 3, "keterangan" => "Tidak dilakukan reload data karena total hutang belum ditagih Rp. 0");
        }
        
        // dd($status);

        /**** Update reloaddata status ****/
        $updatestatus = ReloadDataStatusDetail::on($this->getConnectionName())->find($CheckReloadStatus->id);
        $updatestatus->status = $status['status'];
        $updatestatus->keterangan = $status['keterangan'];
        $updatestatus->tglreload = $tgl_nota;
        $updatestatus->updated_by = Auth::user()->id;
        $updatestatus->save();

        return $status;
        // dd($status);

    }





    /*
        =======================================================================================
        For     : Proses Reload Hutang Antar Outlet
        Author  : Citra
        Date    : 25/10/2021
        =======================================================================================
    */
    public function reloadhutangantaroutlet($id,$id_apotek,$tgl_nota)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        # ----- HUTANG ANTAR OUTLET ------------------------- #
        # masuk ke akun id : 49 s/d 54 => 2-20213 s/d 2-20218 - Hutang Antar-Outlet [x] (debit)
        # masuk ke akun id : 17 => 1-10200 - Persediaan Barang (kredit)
        # ----------------------------------------------------- #

        $id_kode_akun = 8;
        $id_jenis_transaksi = 1;

        # --- select akun piutang per outlet --- #
        $getakunoutlet = MasterKodeAkun::whereRaw('nama LIKE \'%hutang antar%\'')
                        ->whereRaw('id_relasi_apotek != '.$id_apotek)->get();
        // dd($getakunoutlet);

        if(!empty($getakunoutlet)){
            foreach ($getakunoutlet as $key => $value) {

                // **** cek reload status **** //
                $var_reloadstatus = array("id_apotek"=>$id_apotek, "tglreload"=>$tgl_nota, "id_reloaddata"=>$id, "jenis"=>$value->nama);
                $CheckReloadStatus = $this->CheckReloadStatus($var_reloadstatus);
                // dd($CheckReloadStatus);
                // **** cek reload status **** //

                /* **** Save to jurnal ***** 
                * param_jurnal format :
                * array(
                *   "is_reloaded" => "",
                *   "kode_referensi" => "",
                *   "tgl_transaksi" => ""
                * )
                */
                $param_jurnal = array(
                    "kode_referensi" => $CheckReloadStatus->id,
                    "tgl_transaksi" => $tgl_nota,
                    "id_apotek" => $id_apotek
                );
                
                $getData = DB::connection($this->getConnectionName())->table("tb_detail_nota_transfer_outlet as a")
                    ->selectRaw("SUM(a.jumlah * a.harga_outlet) as total")
                    ->join("tb_nota_transfer_outlet as b","b.id","a.id_nota")
                    ->whereRaw("a.is_deleted = 0")
                    ->whereRaw("b.is_deleted = 0")
                    ->whereRaw("b.id_apotek_tujuan = '".$id_apotek."'")
                    ->whereRaw("b.id_apotek_nota = '".$value->id_relasi_apotek."'")
                    ->whereRaw("b.tgl_nota = '".$tgl_nota."'")
                    ->get();
                // dd($getData);


                /*
                * param_detail format :
                * array(
                *   "id_jurnal" => "",
                *   "id_jenis_transaksi" => "",
                *   "kode_referensi" => "",
                *   "id_kode_akun" => "",
                *   "kredit" => ""/ "debit" => ""
                *   "is_reloaded" => "",
                * )
                */
                $param_detail = array();

                if($getData->count()){
                    foreach ($getData as $key => $detail) {
                        if($detail->total > 0){

                            $param_detail[] = array(
                                "id_jenis_transaksi" => $id_jenis_transaksi,
                                "kode_referensi" => $CheckReloadStatus->id,
                                "id_kode_akun" => $value->id,
                                "debit" => $detail->total
                            ); 

                            $param_detail[] = array(
                                "id_jenis_transaksi" => $id_jenis_transaksi,
                                "kode_referensi" => $CheckReloadStatus->id,
                                "id_kode_akun" => 17,
                                "kredit" => $detail->total
                            ); 

                        }
                    }
                }


                if(count($param_detail)){
                    $status = $this->saveLoadData($param_jurnal,$param_detail);
                    $status['keterangan'] = "total : Rp. ".number_format($detail->total);
                    // dd($status);
                } else {
                    $status = array("status" => 3, "keterangan" => "Tidak dilakukan reload data karena total hutang outlet Rp. 0");
                }


                /**** Update reloaddata status ****/
                $updatestatus = ReloadDataStatusDetail::on($this->getConnectionName())->find($CheckReloadStatus->id);
                $updatestatus->status = $status['status'];
                $updatestatus->keterangan = $status['keterangan'];
                $updatestatus->tglreload = $tgl_nota;
                $updatestatus->updated_by = Auth::user()->id;
                $updatestatus->save();


            }
        }

        return $status;
    }






    /*
        =======================================================================================
        For     : Proses Reload Penjualan
        Author  : Citra
        Date    : 26/10/2021
        =======================================================================================
    */ /*------- BELUM ------------*/
    public function reloadpenjualan($id,$id_apotek,$tgl_nota)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        # ----- PENJUALAN ------------------------------------- #
        # akun id : 79, 4-40000 Penjualan (kredit)
        #
        # ** debit sesuai kartu **
        # akun id : 2, 1-10002 Kas Kecil (debit)
        # ----------------------------------------------------- #

        $id_jenis_transaksi = 1;

        /* ------ OLD ------ */
        /*$getData = DB::connection($this->getConnectionName())->table("tb_closing_nota_penjualan")
                    ->selectRaw("SUM(total_penjualan) AS total")
                    ->selectRaw("SUM(total_diskon) AS diskon")
                    ->selectRaw("SUM(total_diskon) AS total_switch_cash")
                    ->whereRaw("id_apotek_nota = '".$id_apotek."'")
                    ->whereRaw("tanggal = '".$tgl_nota."'")
                    ->toSql();*/


        /* ----- QUERY PENJUALAN CASH SRI ----- 
            // cash => tb_nota_penjualan.id_kartu_debet_credit = 0
            // kartu => tb_nota_penjualan.id_kartu_debet_credit != 0

            $penjualan_debet = TransaksiPenjualan::select([
                            'tb_nota_penjualan.id',
                            DB::raw('COUNT(tb_nota_penjualan.id) AS jumlah_transaksi'),
                            DB::raw('SUM(debet) AS total_debet'), 
                            'a.id as id_kartu_debet_credit',
                            'a.nama as nama_kartu',
                            'a.charge'
                        ])
                        ->leftjoin('tb_m_kartu_debet_credit as a', 'a.id', 'tb_nota_penjualan.id_kartu_debet_credit')
                        ->where('tb_nota_penjualan.is_deleted', 0)
                        ->where('tb_nota_penjualan.is_kredit', 0)
                        ->where('tb_nota_penjualan.id_kartu_debet_credit', '!=', 0)
                        ->whereNotNull('tb_nota_penjualan.id_kartu_debet_credit')
                        ->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.created_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->orWhere('tb_nota_penjualan.is_deleted', 0)
                        ->where('tb_nota_penjualan.is_kredit', 0)
                        ->where('tb_nota_penjualan.id_kartu_debet_credit', '!=', '0')
                        ->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.created_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->groupBy('tb_nota_penjualan.id_kartu_debet_credit')
                        ->get();
        */




        $penjualan = TransaksiPenjualan::select([
                            'tb_nota_penjualan.tgl_nota',
                            'tb_nota_penjualan.id_kartu_debet_credit',
                            #DB::raw('SUM(cash) AS total_cash'), 
                            #DB::raw('SUM(debet) AS total_debet'), 
                            DB::raw('IF(tb_nota_penjualan.id_kartu_debet_credit = 0,SUM(cash),SUM(debet)) as total'), 
                            'a.nama as nama_kartu',
                            #'a.charge',
                            DB::RAW('IF(tb_nota_penjualan.id_kartu_debet_credit = 0,2,a.id_kode_akun) as id_kode_akun')
                        ])
                        ->leftjoin('tb_m_kartu_debet_credit as a', 'a.id', 'tb_nota_penjualan.id_kartu_debet_credit')
                        ->whereNotNull('tb_nota_penjualan.id_kartu_debet_credit')
                        ->whereRaw('tb_nota_penjualan.tgl_nota = \''.$tgl_nota.'\'')
                        ->whereRaw("tb_nota_penjualan.id_apotek_nota = '".$id_apotek."'")
                        // ->whereRaw('tb_nota_penjualan.id_kartu_debet_credit = 0')
                        ->whereRaw('tb_nota_penjualan.is_kredit = 0')
                        ->groupBy('tb_nota_penjualan.id_kartu_debet_credit')
                        // ->groupBy('tb_nota_penjualan.tgl_nota')
                        ->get();
        // dd($penjualan);

        // **** cek reload status **** //
        $var_reloadstatus = array("id_apotek"=>$id_apotek, "tglreload"=>$tgl_nota, "id_reloaddata"=>$id);
        $CheckReloadStatus = $this->CheckReloadStatus($var_reloadstatus);
        // dd($CheckReloadStatus);
        // **** cek reload status **** //


        /* **** Save to jurnal ***** 
        * param_jurnal format :
        * array(
        *   "is_reloaded" => "",
        *   "kode_referensi" => "",
        *   "tgl_transaksi" => ""
        * )
        */
        $param_jurnal = array(
            "kode_referensi" => $CheckReloadStatus->id,
            "tgl_transaksi" => $tgl_nota,
            "id_apotek" => $id_apotek
        );
                
        /*
        * param_detail format :
        * array(
        *   "id_jurnal" => "",
        *   "id_jenis_transaksi" => "",
        *   "kode_referensi" => "",
        *   "id_kode_akun" => "",
        *   "kredit" => ""/ "debit" => ""
        *   "is_reloaded" => "",
        * )
        */
        $param_detail = array();
        $totaldetil = 0;
        foreach ($penjualan as $key => $value) {
            if($value->total > 0){
                if(is_null($value->nama_kartu)){
                    $deskripsi = "Penjualan Cash";
                } else {
                    $deskripsi = "Penjualan ".$value->nama_kartu;
                }

                $param_detail[] = array(
                    "id_jenis_transaksi" => $id_jenis_transaksi,
                    "kode_referensi" => $CheckReloadStatus->id,
                    "id_kode_akun" => $value->id_kode_akun,
                    "deskripsi" => $deskripsi,
                    "debit" => $value->total
                ); 

                $totaldetil += $value->total;
            }
        }

        if($totaldetil > 0){
            // akun penjualan
            $param_detail[] = array(
                    "id_jenis_transaksi" => $id_jenis_transaksi,
                    "kode_referensi" => $CheckReloadStatus->id,
                    "id_kode_akun" => 79,
                    "kredit" => $totaldetil
                ); 
        }

        // dd($param_detail);  

        if(count($param_detail)){
            $status = $this->saveLoadData($param_jurnal,$param_detail);
            $status['keterangan'] = "total : Rp. ".number_format($totaldetil);
        } else {
            $status = array("status" => 3, "keterangan" => "Tidak dilakukan reload data karena total penjualan Rp. 0");
        }

        // dd($status);

        /**** Update reloaddata status ****/
        $updatestatus = ReloadDataStatusDetail::on($this->getConnectionName())->find($CheckReloadStatus->id);
        $updatestatus->status = $status['status'];
        $updatestatus->keterangan = $status['keterangan'];
        $updatestatus->tglreload = $tgl_nota;
        $updatestatus->updated_by = Auth::user()->id;
        $updatestatus->save();

        return $status;


    }




    /*
        =======================================================================================
        For     : Proses Reload Diskon Penjualan
        Author  : Citra
        Date    : 26/10/2021
        =======================================================================================
    */
    public function reloaddiskonpenjualan($id,$id_apotek,$tgl_nota)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        # ----- DISKON PENJUALAN ------------------------------------- #
        # akun id : 81, 4-40100 Diskon Penjualan (kredit)
        # akun id : 2, 1-10002 Kas Kecil (debit)
        # ----------------------------------------------------- #

        $id_jenis_transaksi = 1;

        $getData = DB::connection($this->getConnectionName())->table("tb_closing_nota_penjualan")
                    ->selectRaw("SUM(total_diskon) AS total")
                    ->whereRaw("id_apotek_nota = '".$id_apotek."'")
                    ->whereRaw("tanggal = '".$tgl_nota."'")
                    ->get();
        // dd($getData);

        // **** cek reload status **** //
        $var_reloadstatus = array("id_apotek"=>$id_apotek, "tglreload"=>$tgl_nota, "id_reloaddata"=>$id);
        $CheckReloadStatus = $this->CheckReloadStatus($var_reloadstatus);
        // dd($CheckReloadStatus);
        // **** cek reload status **** //


        /* **** Save to jurnal ***** 
        * param_jurnal format :
        * array(
        *   "is_reloaded" => "",
        *   "kode_referensi" => "",
        *   "tgl_transaksi" => ""
        * )
        */
        $param_jurnal = array(
            "kode_referensi" => $CheckReloadStatus->id,
            "tgl_transaksi" => $tgl_nota,
            "id_apotek" => $id_apotek
        );
                
        /*
        * param_detail format :
        * array(
        *   "id_jurnal" => "",
        *   "id_jenis_transaksi" => "",
        *   "kode_referensi" => "",
        *   "id_kode_akun" => "",
        *   "kredit" => ""/ "debit" => ""
        *   "is_reloaded" => "",
        * )
        */
        $param_detail = array();
        foreach ($getData as $key => $value) {
            if($value->total > 0){
                $param_detail[] = array(
                    "id_jenis_transaksi" => $id_jenis_transaksi,
                    "kode_referensi" => $CheckReloadStatus->id,
                    "id_kode_akun" => 81,
                    "kredit" => $value->total
                ); 

                $param_detail[] = array(
                    "id_jenis_transaksi" => $id_jenis_transaksi,
                    "kode_referensi" => $CheckReloadStatus->id,
                    "id_kode_akun" => 2,
                    "debit" => $value->total
                );
            } 
        }

        if(count($param_detail)){
            $status = $this->saveLoadData($param_jurnal,$param_detail);
            $status['keterangan'] = "total : Rp. ".number_format($value->total);
        } else {
            $status = array("status" => 3, "keterangan" => "Tidak dilakukan reload data karena total diskon penjualan Rp. 0");
        }
        
        // dd($status);

        /**** Update reloaddata status ****/
        $updatestatus = ReloadDataStatusDetail::on($this->getConnectionName())->find($CheckReloadStatus->id);
        $updatestatus->status = $status['status'];
        $updatestatus->keterangan = $status['keterangan'];
        $updatestatus->tglreload = $tgl_nota;
        $updatestatus->updated_by = Auth::user()->id;
        $updatestatus->save();

        return $status;
    }





    /*
        =======================================================================================
        For     : Proses Reload Retur Penjualan
        Author  : Citra
        Date    : 26/10/2021
        =======================================================================================
    */
    public function reloadreturpenjualan($id,$id_apotek,$tgl_nota)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        # ----- RETUR PENJUALAN ------------------------------------- #
        # RETUR terhadap PERSEDIAAN
        # akun id : 82, 4-40200 Retur Penjualan (kredit)
        # akun id : 17, 1-10200 - Persediaan Barang (debit)
        #
        # *** RETUR terhadap kas sesuai pembayaran ***
        # akun id : 82, 4-40200 Retur Penjualan (debit)
        # akun id : 2, 1-10002 Kas Kecil (kredit)
        # ----------------------------------------------------------- #

        $id_jenis_transaksi = 5;

        /* ---- OLD ---- */
        /*$getData = DB::connection($this->getConnectionName())->table("tb_closing_nota_penjualan")
                    ->selectRaw("SUM(total_penjualan_cn) AS total")
                    ->whereRaw("id_apotek_nota = '".$id_apotek."'")
                    ->whereRaw("tanggal = '".$tgl_nota."'")
                    ->get();*/
        // dd($getData);


        $getData = ReturPenjualan::select(
                    DB::RAW("SUM(tb_return_penjualan_obat.jumlah_cn*d.harga_jual) as total"),
                    'a.nama as nama_kartu',
                    DB::RAW('IF(nota.id_kartu_debet_credit = 0,2,a.id_kode_akun) as id_kode_akun')
                )
                ->join('tb_detail_nota_penjualan as d','d.id','tb_return_penjualan_obat.id_detail_nota')
                ->join('tb_nota_penjualan as nota','nota.id','d.id_nota')
                ->leftjoin('tb_m_kartu_debet_credit as a', 'a.id', 'nota.id_kartu_debet_credit')
                ->whereRaw("nota.id_apotek_nota = '".$id_apotek."'")
                ->whereRaw("nota.tgl_nota = '".$tgl_nota."'")
                ->groupBy('nota.id_kartu_debet_credit')
                ->get();
        // dd($getData);




        /* ---- RETUR terhadap KAS/BANK ---- */

        // **** cek reload status **** //
        $var_reloadstatus = array("id_apotek"=>$id_apotek, "tglreload"=>$tgl_nota, "id_reloaddata"=>$id);
        $CheckReloadStatus = $this->CheckReloadStatus($var_reloadstatus);
        // dd($CheckReloadStatus);
        // **** cek reload status **** //


        /* **** Save to jurnal ***** 
        * param_jurnal format :
        * array(
        *   "is_reloaded" => "",
        *   "kode_referensi" => "",
        *   "tgl_transaksi" => ""
        * )
        */
        $param_jurnal = array(
            "kode_referensi" => $CheckReloadStatus->id,
            "tgl_transaksi" => $tgl_nota,
            "id_apotek" => $id_apotek,
            "memo" => "Retur Penjualan terhadap Kas/Bank"
        );
                
        /*
        * param_detail format :
        * array(
        *   "id_jurnal" => "",
        *   "id_jenis_transaksi" => "",
        *   "kode_referensi" => "",
        *   "id_kode_akun" => "",
        *   "kredit" => ""/ "debit" => ""
        *   "is_reloaded" => "",
        * )
        */
        $param_detail = array();
        $total_retur = 0;
        foreach ($getData as $key => $value) {
            if($value->total > 0){
                if(is_null($value->nama_kartu)){
                    $deskripsi = "Retur Penjualan Cash";
                } else {
                    $deskripsi = "Retur Penjualan ".$value->nama_kartu;
                }

                $param_detail[] = array(
                    "id_jenis_transaksi" => $id_jenis_transaksi,
                    "kode_referensi" => $CheckReloadStatus->id,
                    "id_kode_akun" => $value->id_kode_akun,
                    "deskripsi" => $deskripsi,
                    "kredit" => $value->total
                ); 

                $total_retur += $value->total;
            } 
        }


        if($total_retur > 0){
            // akun retur //
            $param_detail[] = array(
                "id_jenis_transaksi" => $id_jenis_transaksi,
                "kode_referensi" => $CheckReloadStatus->id,
                "id_kode_akun" => 82,
                "debit" => $total_retur
            );
        }
        $status = $this->saveLoadData($param_jurnal,$param_detail);



        // RETUR terhadap PERSEDIAAN //
        $param_detail = array();
        if($total_retur > 0){
            $param_jurnal = array(
                "kode_referensi" => $CheckReloadStatus->id,
                "tgl_transaksi" => $tgl_nota,
                "id_apotek" => $id_apotek,
                "memo" => "Retur Penjualan terhadap Persediaan Barang"
            );

            // akun retur //
            $param_detail[] = array(
                "id_jenis_transaksi" => $id_jenis_transaksi,
                "kode_referensi" => $CheckReloadStatus->id,
                "id_kode_akun" => 82,
                "kredit" => $total_retur
            );

            // akun retur //
            $param_detail[] = array(
                "id_jenis_transaksi" => $id_jenis_transaksi,
                "kode_referensi" => $CheckReloadStatus->id,
                "id_kode_akun" => 17,
                "debit" => $total_retur
            );
        }

        if(count($param_detail)){
            $status = $this->saveLoadData($param_jurnal,$param_detail);
            $status['keterangan'] = "total : Rp. ".number_format($value->total);
        } else {
            $status = array("status" => 3, "keterangan" => "Tidak dilakukan reload data karena total retur penjualan Rp. 0");
        }

        /* ---- RETUR DEBIT ---- */
        
        // dd($status);

        /**** Update reloaddata status ****/
        $updatestatus = ReloadDataStatusDetail::on($this->getConnectionName())->find($CheckReloadStatus->id);
        $updatestatus->status = $status['status'];
        $updatestatus->keterangan = $status['keterangan'];
        $updatestatus->tglreload = $tgl_nota;
        $updatestatus->updated_by = Auth::user()->id;
        $updatestatus->save();

        return $status;
    }





    /*
        =======================================================================================
        For     : Proses Reload Harga Pokok Penjualan
        Author  : Citra
        Date    : 26/10/2021
        =======================================================================================
    */
    public function reloadhargapokokpenjualan($id,$id_apotek,$tgl_nota)
    {
        # ----- HARGA POKOK PENJUALAN ------------------------------- #
        # akun id : 84, 5-50000 Harga Pokok Penjualan (kredit)
        # akun id : 2, 1-10002 Kas Kecil (debit)
        # ----------------------------------------------------------- #

        $id_jenis_transaksi = 1;

        $getData = DB::connection($this->getConnectionName())->table("tb_detail_nota_penjualan as a")
                    ->selectRaw("SUM( a.jumlah * a.hb_ppn) as total")
                    ->join("tb_nota_penjualan as b","b.id","a.id_nota")
                    ->whereRaw("a.is_deleted = 0")
                    ->whereRaw("a.is_cn = 0")
                    ->whereRaw("b.is_deleted = 0")
                    ->whereRaw("b.id_apotek_nota = '".$id_apotek."'")
                    ->whereRaw("b.tgl_nota = '".$tgl_nota."'")
                    ->get();
        // dd($getData);

        // **** cek reload status **** //
        $var_reloadstatus = array("id_apotek"=>$id_apotek, "tglreload"=>$tgl_nota, "id_reloaddata"=>$id);
        $CheckReloadStatus = $this->CheckReloadStatus($var_reloadstatus);
        // dd($CheckReloadStatus);
        // **** cek reload status **** //


        /* **** Save to jurnal ***** 
        * param_jurnal format :
        * array(
        *   "is_reloaded" => "",
        *   "kode_referensi" => "",
        *   "tgl_transaksi" => ""
        * )
        */
        $param_jurnal = array(
            "kode_referensi" => $CheckReloadStatus->id,
            "tgl_transaksi" => $tgl_nota,
            "id_apotek" => $id_apotek
        );
                
        /*
        * param_detail format :
        * array(
        *   "id_jurnal" => "",
        *   "id_jenis_transaksi" => "",
        *   "kode_referensi" => "",
        *   "id_kode_akun" => "",
        *   "kredit" => ""/ "debit" => ""
        *   "is_reloaded" => "",
        * )
        */
        $param_detail = array();
        foreach ($getData as $key => $value) {
            if($value->total > 0){
                $param_detail[] = array(
                    "id_jenis_transaksi" => $id_jenis_transaksi,
                    "kode_referensi" => $CheckReloadStatus->id,
                    "id_kode_akun" => 84,
                    "kredit" => $value->total
                ); 

                $param_detail[] = array(
                    "id_jenis_transaksi" => $id_jenis_transaksi,
                    "kode_referensi" => $CheckReloadStatus->id,
                    "id_kode_akun" => 2,
                    "debit" => $value->total
                );
            } 
        }

        if(count($param_detail)){
            $status = $this->saveLoadData($param_jurnal,$param_detail);
            $status['keterangan'] = "total : Rp. ".number_format($value->total);
        } else {
            $status = array("status" => 3, "keterangan" => "Tidak dilakukan reload data karena total harga pokok penjualan Rp. 0");
        }
        
        // dd($status);

        /**** Update reloaddata status ****/
        $updatestatus = ReloadDataStatusDetail::on($this->getConnectionName())->find($CheckReloadStatus->id);
        $updatestatus->status = $status['status'];
        $updatestatus->keterangan = $status['keterangan'];
        $updatestatus->tglreload = $tgl_nota;
        $updatestatus->updated_by = Auth::user()->id;
        $updatestatus->save();

        return $status;
    }









    /*
        =======================================================================================
        For     : Proses Reload Pembelian
        Author  : Citra
        Date    : 25/10/2021
        =======================================================================================
    */
    public function reloadpembelian($id,$id_apotek,$tgl_nota)
    {
        $id_jenis_transaksi = 2;

        # ----- PEMBELIAN KREDIT ------------------------------ #
        # total -> akun persediaan id_akun : 17 (debet) 
        # ppn_masukan -> ppn masukan id akun : 24 (debet)
        # total + ppn_masukan ->hutang usaha id akun : 46 (kredit)
        # ----------------------------------------------------- #

        $getpembeliankredit = DB::connection($this->getConnectionName())->table("tb_detail_nota_pembelian as a")
                    ->selectRaw("SUM(a.jumlah * a.harga_beli - (b.diskon1 + b.diskon2)) AS total")
                    ->selectRaw("SUM((b.ppn / 100) * (a.jumlah * a.harga_beli - (b.diskon1 + b.diskon2))) AS ppn_masukan")
                    ->join("tb_nota_pembelian as b","b.id","a.id_nota")
                    ->whereRaw("a.is_deleted = 0")
                    ->whereRaw("b.is_deleted = 0")
                    ->whereRaw("b.id_jenis_pembelian = 2")
                    ->whereRaw("b.id_apotek_nota = '".$id_apotek."'")
                    ->whereRaw("b.tgl_nota = '".$tgl_nota."'")
                    ->get();
        // dd($getpembeliankredit);

        // **** cek reload status **** //
        $var_reloadstatus = array("id_apotek"=>$id_apotek, "tglreload"=>$tgl_nota, "id_reloaddata"=>$id, "jenis"=>"Credit");
        $CheckReloadStatus = $this->CheckReloadStatus($var_reloadstatus);
        // dd($CheckReloadStatus);
        // **** cek reload status **** //

        /* **** Save to jurnal ***** 
        * param_jurnal format :
        * array(
        *   "is_reloaded" => "",
        *   "kode_referensi" => "",
        *   "tgl_transaksi" => ""
        * )
        */
        $param_jurnal = array(
            "kode_referensi" => $CheckReloadStatus->id,
            "tgl_transaksi" => $tgl_nota,
            "id_apotek" => $id_apotek
        );



        /*
        * param_detail format :
        * array(
        *   "id_jurnal" => "",
        *   "id_jenis_transaksi" => "",
        *   "kode_referensi" => "",
        *   "id_kode_akun" => "",
        *   "kredit" => ""/ "debit" => ""
        *   "is_reloaded" => "",
        * )
        */
        $param_detail = array();
        foreach ($getpembeliankredit as $key => $value) {
            if($value->total > 0){
                // akun persediaan
                $param_detail[] = array(
                    "id_jenis_transaksi" => $id_jenis_transaksi,
                    "kode_referensi" => $CheckReloadStatus->id,
                    "id_kode_akun" => 17,
                    "debit" => $value->total
                );

                // akun ppn masukan
                $param_detail[] = array(
                    "id_jenis_transaksi" => $id_jenis_transaksi,
                    "kode_referensi" => $CheckReloadStatus->id,
                    "id_kode_akun" => 24,
                    "debit" => $value->ppn_masukan
                );

                // hutang usaha
                $param_detail[] = array(
                    "id_jenis_transaksi" => $id_jenis_transaksi,
                    "kode_referensi" => $CheckReloadStatus->id,
                    "id_kode_akun" => 46,
                    "kredit" => ($value->total+$value->ppn_masukan)
                );

            }
        }

        if(count($param_detail)){
            $status1 = $this->saveLoadData($param_jurnal,$param_detail);
            $status1['keterangan'] = "total : Rp. ".number_format(($value->total+$value->ppn_masukan)); 
        } else {
            $status1 = array("status" => 3, "keterangan" => "total pembelian kredit Rp. 0");
        }

        /**** Update reloaddata status ****/
        $updatestatus = ReloadDataStatusDetail::on($this->getConnectionName())->find($CheckReloadStatus->id);
        $updatestatus->status = $status1['status'];
        $updatestatus->keterangan = $status1['keterangan'];
        $updatestatus->tglreload = $tgl_nota;
        $updatestatus->updated_by = Auth::user()->id;
        $updatestatus->save();  

        $groupstatus['status'][] = $status1['status'];
        if($status1['keterangan'] != ""){ $groupstatus['keterangan'][] = $status1['keterangan']; }
        

        // dd($status1);

        # ----- // end PEMBELIAN KREDIT ------------------------------ #




        # ----- PEMBELIAN CASH ------------------------------ #
        # total -> akun persediaan id_akun : 17 (kredit) 
        # ppn_masukan -> ppn masukan id akun : 24 (kredit)
        # total + ppn_masukan ->hutang usaha id akun : 46 (debet)
        # ----------------------------------------------------- #

        $getpembeliancash = DB::connection($this->getConnectionName())->table("tb_detail_nota_pembelian as a")
                    ->selectRaw("SUM(a.jumlah * a.harga_beli - (b.diskon1 + b.diskon2)) AS total")
                    ->selectRaw("SUM((b.ppn/100) * (a.jumlah * a.harga_beli - (b.diskon1 + b.diskon2))) AS ppn_masukan")
                    ->join("tb_nota_pembelian as b","b.id","a.id_nota")
                    ->whereRaw("a.is_deleted = 0")
                    ->whereRaw("b.is_deleted = 0")
                    ->whereRaw("b.id_jenis_pembelian = 1")
                    ->whereRaw("b.id_apotek_nota = '".$id_apotek."'")
                    ->whereRaw("b.tgl_nota = '".$tgl_nota."'")
                    ->get();
        // dd($getpembeliankredit);



        // **** cek reload status **** //
        $var_reloadstatus = array("id_apotek"=>$id_apotek, "tglreload"=>$tgl_nota, "id_reloaddata"=>$id, "jenis"=>"Cash");
        $CheckReloadStatus = $this->CheckReloadStatus($var_reloadstatus);
        // dd($CheckReloadStatus);
        // **** cek reload status **** //

        /* **** Save to jurnal ***** 
        * param_jurnal format :
        * array(
        *   "is_reloaded" => "",
        *   "kode_referensi" => "",
        *   "tgl_transaksi" => ""
        * )
        */
        $param_jurnal = array(
            "kode_referensi" => $CheckReloadStatus->id,
            "tgl_transaksi" => $tgl_nota,
            "id_apotek" => $id_apotek
        );



        /*
        * param_detail format :
        * array(
        *   "id_jurnal" => "",
        *   "id_jenis_transaksi" => "",
        *   "kode_referensi" => "",
        *   "id_kode_akun" => "",
        *   "kredit" => ""/ "debit" => ""
        *   "is_reloaded" => "",
        * )
        */
        $param_detail = array();
        foreach ($getpembeliancash as $key => $value) {
            if($value->total > 0){
                // akun persediaan
                $param_detail[] = array(
                    "id_jenis_transaksi" => $id_jenis_transaksi,
                    "kode_referensi" => $CheckReloadStatus->id,
                    "id_kode_akun" => 17,
                    "debit" => $value->total
                );

                // akun ppn masukan
                $param_detail[] = array(
                    "id_jenis_transaksi" => $id_jenis_transaksi,
                    "kode_referensi" => $CheckReloadStatus->id,
                    "id_kode_akun" => 24,
                    "debit" => $value->ppn_masukan
                );

                // hutang usaha
                $param_detail[] = array(
                    "id_jenis_transaksi" => $id_jenis_transaksi,
                    "kode_referensi" => $CheckReloadStatus->id,
                    "id_kode_akun" => 46,
                    "kredit" => ($value->total+$value->ppn_masukan)
                );

            }
        }

        if(count($param_detail)){
            $status2 = $this->saveLoadData($param_jurnal,$param_detail);
            $status2['keterangan'] = "total : Rp. ".number_format(($value->total+$value->ppn_masukan)); 
        } else {
            $status2 = array("status" => 3, "keterangan" => "total pembelian kredit Rp. 0");
        }

        $groupstatus['status'][] = $status2['status'];
        if($status2['keterangan'] != ""){ $groupstatus['keterangan'][] = $status2['keterangan']; }
        

        // dd($status2);
        # ----- // end PEMBELIAN CASH ------------------------------ #

        // dd($groupstatus);


        /**** Update reloaddata status ****/
        $updatestatus = ReloadDataStatusDetail::on($this->getConnectionName())->find($CheckReloadStatus->id);
        $updatestatus->status = $status2['status'];
        $updatestatus->keterangan = $status2['keterangan'];
        $updatestatus->tglreload = $tgl_nota;
        $updatestatus->updated_by = Auth::user()->id;
        $updatestatus->save();     

        return $groupstatus;
    }




    /*
        =======================================================================================
        For     : Proses Reload Retur Pembelian
        Author  : Citra
        Date    : 25/10/2021
        =======================================================================================
    */
    public function reloadreturpembelian($id,$id_apotek,$tgl_nota)
    { 
        $id_jenis_transaksi = 26;

        # ----- RETUR PEMBELIAN ------------------------------ #
        # ***** RETUR terhadap KAS *****
        # total -> retur pembelian id akun : 86 (kredit)
        # ppn_masukan -> ppn masukan id akun : 24 (kredit)
        # total + ppn_masukan -> kas kecil id akun : 2 (debit)
        #
        #
        # ***** RETUR terhadap PERSEDIAAN *****
        # total -> retur pembelian id akun : 86 (debit)
        # ppn_masukan -> ppn masukan id akun : 24 (debit)
        # total + ppn_masukan -> persediaan id akun : 17 (kredit)
        # ----------------------------------------------------- #


        $getdata = DB::connection($this->getConnectionName())->table("tb_detail_nota_pembelian as a")
                    ->selectRaw("
                        DATE(a.retur_at) AS tgl_retur,
                        c.`jenis_pembayaran`,
                        SUM(a.jumlah * a.harga_beli - (b.diskon1 + b.diskon2)) AS total,
                        SUM((b.ppn / 100) * (a.jumlah * a.harga_beli - (b.diskon1 + b.diskon2))) AS ppn_masukan
                    ")

                    ->join("tb_nota_pembelian as b","b.id","a.id_nota")
                    ->join("tb_m_jenis_pembayaran as c","c.id","b.id_jenis_pembayaran")

                    ->whereRaw("a.is_deleted = 0")
                    ->whereRaw("a.is_retur = 1")
                    ->whereRaw("DATE(a.retur_at) = '".$tgl_nota."'")
                    ->whereRaw("b.is_deleted = 0")
                    ->whereRaw("b.is_lunas = 1")
                    ->whereRaw("b.id_apotek_nota = '".$id_apotek."'")

                    ->groupBy("b.tgl_nota")
                    ->groupBy("b.id_jenis_pembayaran")
                    ->get();
        // dd($getdata);

        // **** cek reload status **** //
        $var_reloadstatus = array("id_apotek"=>$id_apotek, "tglreload"=>$tgl_nota, "id_reloaddata"=>$id);
        $CheckReloadStatus = $this->CheckReloadStatus($var_reloadstatus);
        // dd($CheckReloadStatus);
        // **** cek reload status **** //


        # ***** RETUR terhadap KAS ***** #

        /* **** Save to jurnal ***** 
        * param_jurnal format :
        * array(
        *   "is_reloaded" => "",
        *   "kode_referensi" => "",
        *   "tgl_transaksi" => ""
        * )
        */
        $param_jurnal = array(
            "kode_referensi" => $CheckReloadStatus->id,
            "tgl_transaksi" => $tgl_nota,
            "id_apotek" => $id_apotek,
            "memo" => "Retur Pembelian terhadap Kas"
        );


        /*
        * param_detail format :
        * array(
        *   "id_jurnal" => "",
        *   "id_jenis_transaksi" => "",
        *   "kode_referensi" => "",
        *   "id_kode_akun" => "",
        *   "kredit" => ""/ "debit" => ""
        *   "is_reloaded" => "",
        * )
        */
        $param_detail = array();
        foreach ($getdata as $key => $value) {
            if(($value->total+$value->ppn_masukan) > 0){
                // akun persediaan
                $param_detail[] = array(
                    "id_jenis_transaksi" => $id_jenis_transaksi,
                    "kode_referensi" => $CheckReloadStatus->id,
                    "id_kode_akun" => 86,
                    "kredit" => $value->total
                );

                // akun ppn masukan
                $param_detail[] = array(
                    "id_jenis_transaksi" => $id_jenis_transaksi,
                    "kode_referensi" => $CheckReloadStatus->id,
                    "id_kode_akun" => 24,
                    "kredit" => $value->ppn_masukan
                );

                // hutang usaha
                $param_detail[] = array(
                    "id_jenis_transaksi" => $id_jenis_transaksi,
                    "kode_referensi" => $CheckReloadStatus->id,
                    "id_kode_akun" => 2,
                    "debit" => ($value->total+$value->ppn_masukan)
                );
            }

        }
        $status = $this->saveLoadData($param_jurnal,$param_detail);




        # ***** RETUR terhadap PERSEDIAAN ***** #

        $param_jurnal = array(
            "kode_referensi" => $CheckReloadStatus->id,
            "tgl_transaksi" => $tgl_nota,
            "id_apotek" => $id_apotek,
            "memo" => "Retur Pembelian terhadap Persediaan"
        );

        $param_detail = array();
        foreach ($getdata as $key => $value) {
            if(($value->total+$value->ppn_masukan) > 0){
                // akun persediaan
                $param_detail[] = array(
                    "id_jenis_transaksi" => $id_jenis_transaksi,
                    "kode_referensi" => $CheckReloadStatus->id,
                    "id_kode_akun" => 86,
                    "debit" => $value->total
                );

                // akun ppn masukan
                $param_detail[] = array(
                    "id_jenis_transaksi" => $id_jenis_transaksi,
                    "kode_referensi" => $CheckReloadStatus->id,
                    "id_kode_akun" => 24,
                    "debit" => $value->ppn_masukan
                );

                // hutang usaha
                $param_detail[] = array(
                    "id_jenis_transaksi" => $id_jenis_transaksi,
                    "kode_referensi" => $CheckReloadStatus->id,
                    "id_kode_akun" => 17,
                    "kredit" => ($value->total+$value->ppn_masukan)
                );
            }

        }


        if(count($param_detail)){
            $status = $this->saveLoadData($param_jurnal,$param_detail);
            $status['keterangan'] = "total : Rp. ".number_format(($value->total+$value->ppn_masukan)); 
        } else {
            $status = array("status" => 3, "keterangan" => "total retur pembelian Rp. 0");
        }

        // dd($status);

        /**** Update reloaddata status ****/
        $updatestatus = ReloadDataStatusDetail::on($this->getConnectionName())->find($CheckReloadStatus->id);
        $updatestatus->status = $status['status'];
        $updatestatus->keterangan = $status['keterangan'];
        $updatestatus->tglreload = $tgl_nota;
        $updatestatus->updated_by = Auth::user()->id;
        $updatestatus->save();

        return $status;
    }

}
