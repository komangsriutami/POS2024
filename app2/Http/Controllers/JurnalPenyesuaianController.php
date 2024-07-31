<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\MasterApotek;
use App\JurnalUmum;
use App\JurnalUmumDetail;
use App\JurnalUmumBukti;
use App\MasterKodeAkun;
use App\MasterKategoriAkun;
use App\MasterJenisTransaksi;
use App\ReloadDataStatus;
use App\ReloadDataStatusDetail;

use App\Exports\JurnalUmumTemplateExport;
use App\Exports\JurnalUmumKeterangan;
use App\Imports\JurnalPenyesuaianImport;

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

class JurnalPenyesuaianController extends Controller
{
    use DynamicConnectionTrait;
    /*
        =======================================================================================
        For     : Halaman utama jurnal penyesuaian
        Author  : Citra
        Date    : 29/10/2021
        =======================================================================================
    */
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('jurnal_penyesuaian.index');
    }


    /*
        =======================================================================================
        For     : Menampilkan list detail jurnal per akun
        Author  : Citra
        Date    : 20/09/2021
        =======================================================================================
    */
    public function listdata(Request $request)
    {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::connection($this->getConnection())->statement(DB::raw('set @rownum = 0'));
        $data = JurnalUmum::on($this->getConnectionName())->whereRaw('is_penyesuaian = 1')->whereNull("deleted_by");
        
        $datatables = Datatables::of($data);
        return $datatables
        /*->filter(function($query) use($request,$order_column,$order_dir){
            // $query->where(function($query) use($request){
                $query->where('akun.nama','LIKE','%'.$request->get('search')['value'].'%');
                $query->Orwhere('akun.kode','LIKE','%'.$request->get('search')['value'].'%');
            // });
        })  */ 
        ->editcolumn('total_debit', function($data){ return 'Rp. '.number_format($data->total_debit); })  
        ->editcolumn('total_kredit', function($data){ return 'Rp. '.number_format($data->total_kredit); })  
        ->editcolumn('no_transaksi', function($data){ return "Jurnal Penyesuaian #".$data->no_transaksi; })
        ->editcolumn('tgl_transaksi', function($data){ return Date("d-m-Y", strtotime($data->tgl_transaksi)); })  
        ->editcolumn('is_tutup_buku', function($data){ 
            if($data->is_tutup_buku){
                return '<small class="text-success"><i class="fa fa-check"> Tutup Buku</i></small>';
            } else {
                return '<small class="text-muted"><i class="fa fa-clock"> Belum Tutup Buku</i></small>';
            }
        })  
        ->addcolumn('action', function($data) {
            if(!$data->is_tutup_buku){
                $btn = '<div class="btn-group">';
                $btn .= '<a href="'.url("jurnalpenyesuaian/".Crypt::encrypt($data->id)).'" class="btn btn-default" data-toggle="tooltip" data-placement="top" title="View Data"><i class="fa fa-search"></i></a>';
                $btn .= '<a href="'.url("jurnalpenyesuaian/".Crypt::encrypt($data->id)).'/edit" class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></a>';
                $btn .= '<span class="btn btn-danger" onClick="deletejurnal(\''.Crypt::encrypt($data->id).'\')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
                $btn .='</div>';
                return $btn;
            }
        })    
        ->rawColumns(['action','is_tutup_buku','no_transaksi'])
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
        $jurnal_penyesuaian = new JurnalUmum;
        $jurnal_penyesuaian->setDynamicConnection();
        $jenistransaksi = MasterJenisTransaksi::get();
        return view('jurnal_penyesuaian.create')->with(compact('jurnal_penyesuaian','jenistransaksi'));
    }


    /*
        =======================================================================================
        For     : Menambah Form Input Detail jurnal penyesuaian
        Author  : Citra
        Date    : 29/10/2021
        =======================================================================================
    */
    public function addDetail(Request $request)
    {
        // dd($request->input());
        $kode_akun= MasterKodeAkun::on($this->getConnectionName())->select('id',DB::RAW('CONCAT(kode,\' - \',nama) as nama_akun'))->where('is_deleted', 0)->pluck('nama_akun', 'id');
        $kode_akun->prepend('-- Pilih Akun --','');

        $detailjurnal = new JurnalUmumDetail;
        $detailjurnal->setDynamicConnection();
        $count = $request->count;
        $form_detail = View::make('jurnal_penyesuaian._form_detail',compact('kode_akun','detailjurnal','count'))->render();
        $status = 1;

        return json_encode(compact('status','form_detail'));
    }


    /*
        =======================================================================================
        For     : Menambah Form Input File lampiran jurnal penyesuaian
        Author  : Citra
        Date    : 29/10/2021
        =======================================================================================
    */
    public function addfile(Request $request)
    {
        // dd($request->input());
        $filebukti = new JurnalUmumBukti;
        $filebukti->setDynamicConnection();
        $count = $request->count;
        $form_detail = View::make('jurnal_penyesuaian._form_file',compact('filebukti','count'))->render();
        $status = 1;

        return json_encode(compact('status','form_detail'));
    }





    /*
        =======================================================================================
        For     : Menyimpan data jurnal ke db
        Author  : Citra
        Date    : 29/10/2021
        =======================================================================================
    */
    public function store(Request $request)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        // dd($request->input());
        DB::connection($this->getConnectionName())->beginTransaction();  
        try {

            $jurnal_penyesuaian = new JurnalUmum;
            $jurnal_penyesuaian->setDynamicConnection();
            $jurnal_penyesuaian->no_transaksi = $request->no_transaksi;
            $jurnal_penyesuaian->tgl_transaksi = $request->tgl_transaksi;
            $jurnal_penyesuaian->memo = $request->memo;
            $jurnal_penyesuaian->is_penyesuaian = 1;
            $jurnal_penyesuaian->tag = $request->tag;
            $validator = $jurnal_penyesuaian->validate();

            if(!$validator->fails()){

                $jurnal_penyesuaian->id_apotek = session('id_apotek_active');
                $jurnal_penyesuaian->created_by = Auth::user()->id;

                if($jurnal_penyesuaian->save()){

                    // simpan detail //
                    if(isset($request->id_kode_akun)){
                        foreach ($request->id_kode_akun as $key => $kode) {
                            if(!is_null($kode)){
                                $detail = new JurnalUmumDetail;
                                $detail->setDynamicConnection();
                                $detail->id_jurnal = $jurnal_penyesuaian->id;
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

                                        if($mime == "application/pdf"){
                                            $fp = fopen($path,'r');
                                            $content = fread($fp, filesize($destination_path.$destination_filename));
                                            // $content = addslashes($content);
                                            fclose($fp);
                                        } else {
                                            $content = file_get_contents($path);
                                        }


                                        $buktijurnal->id_jurnal = $jurnal_penyesuaian->id;
                                        $buktijurnal->type_file = $mime;
                                        $buktijurnal->file = base64_encode($content);
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
                    echo json_encode(array("status" => 1,"errorMessages" => $errorMessages, "url" => url('jurnalpenyesuaian')));


                } else {
                    echo json_encode(array("status" => 2));
                }


            } else {
                echo json_encode(array("status" => 2));
            }
            
        } catch(Exception $e){
            DB::connection($this->getConnectionName())->rollback();
            echo json_encode(array('status' => 2));
        }
    }




    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $idjurnal = Crypt::decrypt($id);
        $jurnal_penyesuaian = JurnalUmum::on($this->getConnectionName())->find($idjurnal);
        if(!empty($jurnal_penyesuaian)){
            return view('jurnal_penyesuaian.showDetail')->with(compact("jurnal_penyesuaian"));
        } else {
            echo "jurnal tidak ditemukan";
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $idjurnal = Crypt::decrypt($id);
        $jurnal_penyesuaian = JurnalUmum::on($this->getConnectionName())->find($idjurnal);
        if(!empty($jurnal_penyesuaian)){
            $kode_akun= MasterKodeAkun::on($this->getConnectionName())->select('id',DB::RAW('CONCAT(kode,\' - \',nama) as nama_akun'))->where('is_deleted', 0)->pluck('nama_akun', 'id');
            $kode_akun->prepend('-- Pilih Akun --','');

            return view('jurnal_penyesuaian.edit')->with(compact("jurnal_penyesuaian","kode_akun"));
        } else {
            echo "jurnal tidak ditemukan";
        }
    }

    /*
        =======================================================================================
        For     : Update jurnal penyesuaian
        Author  : Ayu Citra
        Date    : 29/10/2021
        =======================================================================================
    */
    public function update(Request $request, $id)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        DB::connection($this->getConnectionName())->beginTransaction(); 
        try {
            
            // dd($request->input());
            $id = Crypt::decrypt($id);
            $jurnal_penyesuaian = JurnalUmum::on($this->getConnectionName())->find($id);
            $jurnal_penyesuaian->no_transaksi = $request->no_transaksi;
            $jurnal_penyesuaian->tgl_transaksi = $request->tgl_transaksi;
            $jurnal_penyesuaian->memo = $request->memo;
            // $jurnal_penyesuaian->id_jenis_transaksi = $request->id_jenis_transaksi;
            $jurnal_penyesuaian->tag = $request->tag;
            // $jurnal_penyesuaian->kode_referensi = $request->kode_referensi;
            $validator = $jurnal_penyesuaian->validate();
            if(!$validator->fails()){

                $jurnal_penyesuaian->updated_by = Auth::user()->id;
                $jurnal_penyesuaian->updated_at = Date("Y-m-d H:i:s");

                 if($jurnal_penyesuaian->save()){

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
                                
                                $detail->id_jurnal = $jurnal_penyesuaian->id;
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
                        $del = JurnalUmumDetail::on($this->getConnectionName())->where(function($query) use ($listakun,$jurnal_penyesuaian){
                            if(count($listakun)){
                                $query->whereNotIn("id",$listakun);
                                $query->where("id_jurnal",$jurnal_penyesuaian->id); // SRI | sepertinya selain akun yang diupdate kehapus semua kalau tanpa ini
                            } else {
                                $query->where("id_jurnal",$jurnal_penyesuaian->id);
                            }
                        }) ->update([
                            "deleted_by" => Auth::user()->id,
                            "deleted_at" => Date("Y-m-d H:i:s")
                        ]);
                    }

                    $jurnal_penyesuaian->total_debit = $total_debit;
                    $jurnal_penyesuaian->total_kredit = $total_kredit;
                    $jurnal_penyesuaian->save();


                     // simpan bukti //
                    $errorfile = 0;
                    $errorMessages = "";
                    $listbukti = array();


                    if(isset($request->keterangan)){
                        if(count($request->keterangan)){
                            foreach ($request->keterangan as $key => $keterangan) {

                                if(isset($request->buktifile[$key])){

                                    $bukti = $request->buktifile[$key];
                                    
                                    if($bukti->getMimeType() == "application/pdf" || $bukti->getMimeType() == "image/jpeg" || $bukti->getMimeType() == "image/jpg" || $bukti->getMimeType() == "image/png"){

                                        $mime = $bukti->getMimeType();

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

                                        // dd($bukti);
                                        if($mime == "application/pdf"){
                                            $fp = fopen($path,'r');
                                            $content = fread($fp, filesize($destination_path.$destination_filename));
                                            $content = addslashes($content);
                                            fclose($fp);
                                        } else {
                                            $content = file_get_contents($path);
                                        }

                                        if($request->idbukti[$key] == ""){
                                            $buktijurnal = new JurnalUmumBukti;
                                            $buktijurnal->setDynamicConnection();
                                            $buktijurnal->type_file = $mime;
                                            $buktijurnal->file = base64_encode($content);
                                            $buktijurnal->file = $file_name . "." . $ext;
                                            $buktijurnal->created_by = Auth::user()->id;
                                        } else {
                                            $buktijurnal = JurnalUmumBukti::on($this->getConnectionName())->find(Crypt::decrypt($request->idbukti[$key]));
                                            $buktijurnal->updated_by = Auth::user()->id;
                                            $buktijurnal->updated_at = Date("Y-m-d H:i:s");
                                        }
                                        
                                        $buktijurnal->id_jurnal = $jurnal_penyesuaian->id;
                                        $buktijurnal->keterangan = $request->keterangan[$key];
                                        $buktijurnal->save();

                                        if(isset($path)){
                                            if (File::exists($path)) {
                                                unlink($path);
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

                                }

                                $listbukti[] = $buktijurnal->id;
                            }
                        }
                    }

                    // dd($listbukti);

                    /* --- Hapus detail yang tidak ada dalam list --- */
                    $del = JurnalUmumBukti::on($this->getConnectionName())->where(function($query) use ($listbukti,$jurnal_penyesuaian){
                        if(count($listbukti)){
                            $query->whereNotIn("id",$listbukti);
                            $query->where("id_jurnal",$jurnal_penyesuaian->id); // SRI | sepertinya selain akun yang diupdate kehapus semua kalau tanpa ini
                        } else {
                            $query->where("id_jurnal",$jurnal_penyesuaian->id);
                        }
                    }) ->update([
                        "deleted_by" => Auth::user()->id,
                        "deleted_at" => Date("Y-m-d H:i:s")
                    ]);


                    if($errorfile > 0){ $errorMessages = "terdapat "+$errorfile+" file bukti dengan ekstensi yang tidak sesuai"; }

                    DB::connection($this->getConnectionName())->commit();
                    echo json_encode(array("status" => 1,"errorMessages" => $errorMessages, "url" => url('jurnalpenyesuaian')));


                 } else {
                    echo json_encode(array("status" => 2));
                 }

            } else {
                echo json_encode(array("status" => 2));
            }

        } catch (Exception $e) {
            DB::connection($this->getConnectionName())->rollback();
            echo json_encode(array("status" => 2));
        }
    }



    /*
        =======================================================================================
        For     : hapus jurnal
        Author  : Ayu Citra
        Date    : 29/10/2021
        =======================================================================================
    */
    public function destroy($id)
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
        For     : Download template import xls
        Author  : Citra
        Date    : 22/09/2021
        =======================================================================================
    */
    public function gettemplate()
    {
        return Excel::download(new class() implements WithHeadings, WithColumnWidths, WithTitle {

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

            /**
             * @return string
             */
            public function title(): string
            {
                return 'Template Import';
            }

        },"Template Import Jurnal Penyesuaian.xlsx");
    }



    /*
        =======================================================================================
        For     : Import xls form
        Author  : Citra
        Date    : 01/11/2021
        =======================================================================================
    */
    public function ImportJurnal()
    {
        return view('jurnal_penyesuaian._import_data');
    }



    /*
        =======================================================================================
        For     : Import xls prosess
        Author  : Citra
        Date    : 01/11/2021
        =======================================================================================
    */
    public function import_jurnal_from_excel(Request $request)
    {
        // dd($request->import_file);
        if(Input::hasFile('import_file')){
            $path = Input::file('import_file')->getRealPath();

            $importxls = new JurnalPenyesuaianImport;
            $import = Excel::import($importxls, $path);
            // dd($importxls->importstatus);

            // return status //
            $keterangan = "Berhasil import : ".$importxls->importstatus['jurnalimport_ok']." Baris <br> Gagal import : ".$importxls->importstatus['jurnalimport_error']." Baris <br> Baris yang sama : ".$importxls->importstatus['duplicatedata']." Baris";

            $status = array("status"=>1,"keterangan"=>$keterangan);
            
            echo json_encode($status);
        }
    }




}
