<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use App;
use Datatables;
use DB;
use View;
use Crypt;
use File;
use Input;

use App\Biaya;
use App\BiayaDetail;
use App\BiayaBukti;
use App\MasterKodeAkun;
use App\MasterSuplier;
use App\JurnalUmum;
use App\JurnalUmumDetail;
use App\JurnalUmumBukti;
use App\MasterPajak;
use App\MasterSyaratPembayaran;
use App\MasterApotek;
use App\User;
use App\MasterMember;

use App\Imports\BiayaImport;
use App\Imports\BiayaImportSheet;
use App\Exports\BiayaTemplateExportSheet;

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

class BiayaController extends Controller
{
    use DynamicConnectionTrait;
    protected $flag_trx = 1;
    protected $carabayar = array("-- Pilih Cara Pembayaran --","1"=>"Cash","2"=>"Transfer");

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     * 
     * ====================================
     * For      : Halaman utama Biaya
     * Author   : Citra
     * Date     : 29/09/2021
     * ====================================
     * 
     */
    public function index()
    {
        $getbulanini = Biaya::select(DB::RAW("(SUM(subtotal-IFNULL(ppn_potong,0))) as total"))
                    ->whereNull("tb_biaya.deleted_by")
                    ->whereRaw('MONTH(tgl_transaksi) = \''.Date('m').'\'')
                    ->whereRaw('YEAR(tgl_transaksi) = \''.Date('Y').'\'')
                    ->first();
        // dd($getbulanini);


        $tgl30harisebelumnya = Date("Y-m-d", strtotime('-30 day'));
        // dd($tgl30harisebelumnya);
        $get30hari = Biaya::select(DB::RAW("(SUM(subtotal-IFNULL(ppn_potong,0))) as total"))
                    ->whereNull("tb_biaya.deleted_by")
                    ->whereRaw('tgl_transaksi >= \''.$tgl30harisebelumnya.'\'')
                    ->whereRaw('tgl_transaksi <= \''.Date('Y-m-d').'\'')
                    ->first();
        // dd($get30hari);

        $belumlunas = Biaya::select(DB::RAW("(SUM(subtotal-IFNULL(ppn_potong,0))) as total"))
                    ->whereNull("tb_biaya.deleted_by")
                    ->whereRaw("id_status != 2")
                    ->first();
        // dd($belumlunas);

        // dd($this->flag_trx);
        return view('biaya.index')->with(compact('getbulanini','get30hari','belumlunas'));
    }


    /**
     * ====================================
     * For      : List data index
     * Author   : Citra
     * Date     : 29/09/2021
     * ====================================
     * 
     */
    public function list_biaya(Request $request)
    {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::connection($this->getConnection())->statement(DB::raw('set @rownum = 0'));
        $data = Biaya::select([
            DB::raw('@rownum  := @rownum  + 1 AS no'),
            "tb_biaya.*"
        ])
        ->whereNull('tb_biaya.deleted_by');

        return $datatables = Datatables::of($data)
        ->editcolumn('id_penerima', function($data){
            $penerima = '';
            if($data->tipe_penerima == 1) {
                $penerima = '<span class="badge badge-warning">Suplier</span> | '.$data->supplier->nama;
            } else if($data->tipe_penerima == 2) {
                $penerima = '<span class="badge badge-info">Staff</span> | '.$data->user->nama;
            } else if($data->tipe_penerima == 3) {
                $penerima = '<span class="badge badge-secondary">Member</span> | '.$data->member->nama;
            } 
            return $penerima; 
        })
        ->editcolumn('tgl_transaksi', function($data){
            return Date("d-m-Y",strtotime($data->tgl_transaksi)); 
        })
        ->addcolumn('status', function($data){
            $status = '<span class="badge badge-primary">Open</span>';

            if($data->id_status == 1){
                if(!is_null($data->tgl_batas_pembayaran)){
                    if($data->tgl_batas_pembayaran < Date("Y-m-d")){
                        $status = '<span class="badge badge-danger">Overdue</span><br><small class="text-danger">Due date : '.Date('d M Y',strtotime($data->tgl_batas_pembayaran)).'</small>';
                    }
                }

            } else if($data->id_status == 2) {
                $status = '<span class="badge badge-success">Closed</span>';
            }

            return $status; 
        }) 
        ->addcolumn('sisatagihan', function($data){
            return ''; 
        })
        ->addcolumn('total', function($data){

            $subtotal = $this->hitungtotalbiaya($data);

            $total = $subtotal - $data->ppn_potong;
            return number_format($total); 
        }) 
        ->addcolumn('action', function($data){
            $btn = '<div class="btn-group">';

            if($data->id_status == 2){
                $btn .= '<span class="btn btn-warning" onClick="updateStatus(\''.Crypt::encrypt($data->id).'\',1)" data-toggle="tooltip" data-placement="top" title="Update Status Open"><i class="fas fa-unlock"></i></span>';
            } else {
                $btn .= '<span class="btn btn-warning" onClick="updateStatus(\''.Crypt::encrypt($data->id).'\',2)" data-toggle="tooltip" data-placement="top" title="Update Status Close"><i class="fas fa-lock"></i></span>';
            }

            $btn .= '<a href="'.url("biaya/".Crypt::encrypt($data->id)).'" class="btn btn-default" data-toggle="tooltip" data-placement="top" title="View Data"><i class="fa fa-search"></i></a>';
            $btn .= '<a href="'.url("biaya/".Crypt::encrypt($data->id)).'/edit" class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></a>';
            $btn .= '<span class="btn btn-danger" onClick="deletedata(\''.Crypt::encrypt($data->id).'\')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
            $btn .='</div>';
            return $btn;
        }) 
        ->rawColumns(['status','action', 'id_penerima'])
        ->addIndexColumn()
        ->make(true)
        ;
    }




    public function hitungtotalbiaya($biaya)
    {
        $listpajak = MasterPajak::whereNull('deleted_by')->get();
        $detail = $biaya->detailbiaya;
        // dd($detail);
        
        $subtotal = 0;
        if(!is_null($detail)){
            foreach ($detail as $key => $value) {
                $pajak = json_decode($value->id_akun_pajak);
                $dtpajak = $listpajak->whereIn('id',$pajak);                    
                // dd($dtpajak);

                $jmlpajak = 0;
                if(!is_null($dtpajak)){
                    foreach ($dtpajak as $key => $p) {
                        if($p->is_pemotongan){
                            $jmlpajak -= $value->biaya * $p->persentase_efektif/100; 
                        } else {
                            $jmlpajak += $value->biaya * $p->persentase_efektif/100;
                        }
                    }
                }

                $subtotal += $value->biaya + $jmlpajak;

                // dd($jmlpajak);

            }
        }

        return $subtotal;

    }

    

    /**
     * ====================================
     * For      : new data
     * Author   : Citra
     * Date     : 01/10/2021
     * ====================================
     * 
     */
    public function create()
    {
        $biaya = new Biaya;
        $biaya->setDynamicConnection();

        $akundompet = MasterKodeAkun::select("id",DB::RAW("CONCAT(kode,' - ',nama) as nama_akun"))
                    ->where('is_deleted', 0)->pluck('nama_akun', 'id');
        $akundompet->prepend('-- Pilih Akun --','');


        $supplier = $this->get_penerima();
        /*$supplier = $supplier->pluck('nama', 'id');
        $supplier->prepend('-- Pilih Penerima --','');*/

        $carabayar = $this->carabayar;

        $syarat_pembayaran = MasterSyaratPembayaran::whereNull("deleted_by")->get();

        return view('biaya.create')->with(compact('biaya','akundompet','supplier','carabayar','syarat_pembayaran'));
    }

    public function get_penerima() {
        $suppliers = MasterSuplier::on($this->getConnectionName())->where("is_deleted",0)->get();
        $users = User::on($this->getConnectionName())->where("is_deleted",0)->get();
        $members = MasterMember::on($this->getConnectionName())->where("is_deleted",0)->get();

        $arr_ = collect();
        foreach ($suppliers as $key => $val) {
            $new = array();
            $new['type'] = 1; // supplier
            $new['id'] = $val->id;
            $new['nama'] = $val->nama;

            $arr_[] = $new;
        } 

        foreach ($users as $key => $val) {
            $new = array();
            $new['type'] = 2; // user
            $new['id'] = $val->id;
            $new['nama'] = $val->nama;

            $arr_[] = $new;
        } 

        foreach ($members as $key => $val) {
            $new = array();
            $new['type'] = 2; // member
            $new['id'] = $val->id;
            $new['nama'] = $val->nama;

            $arr_[] = $new;
        } 

        return $arr_;
    }


    /*
        =======================================================================================
        For     : Menambah Form Input Detail biaya
        Author  : Citra
        Date    : 01/10/2021
        =======================================================================================
    */
    public function addDetail(Request $request)
    {
        $kode_akun= MasterKodeAkun::select('id',DB::RAW('CONCAT(kode,\' - \',nama) as nama_akun'))
                    ->where('is_deleted', 0)->pluck('nama_akun', 'id');
        $kode_akun->prepend('-- Pilih Akun --','');

        $listpajak = MasterPajak::whereNull('deleted_by')->pluck('nama', 'id');
        //$listpajak->prepend('-- Pilih Pajak --','');
        // dd($listpajak);

        $detailbiaya = new BiayaDetail;
        $detailbiaya->setDynamicConnection();
        $count = $request->count;
        $form_detail = View::make('biaya._form_detail',compact('kode_akun','detailbiaya','count','listpajak'))->render();
        $status = 1;

        return json_encode(compact('status','form_detail'));
    }




    /*
        =======================================================================================
        For     : Menambah Form Input File lampiran biaya
        Author  : Citra
        Date    : 01/10/2021
        =======================================================================================
    */
    public function addfile(Request $request)
    {
        // dd($request->input());
        $filebukti = new BiayaBukti;
        $filebukti->setDynamicConnection();
        $count = $request->count;
        $form_detail = View::make('biaya._form_file',compact('filebukti','count'))->render();
        $status = 1;

        return json_encode(compact('status','form_detail'));
    }




    /*
        =======================================================================================
        For     : Hitung pajak per detail
        Author  : Citra
        Date    : 14/10/2021
        =======================================================================================
    */
    public function HitungDetailPajak(Request $request)
    {
        // dd($request->input());
        try {

            $view_pajak = '';
            $akun_pajak = [];
            $history_hitung_pajak = [];

            $subtotal = 0;
            $subtotalawal = 0;
            $total = 0;

            if(isset($request->biaya)){
                $history_hitung_pajak = [];

                foreach ($request->biaya as $key => $biaya) {

                    $subtotalpajak[$key] = 0;

                    // dd($request->akun_pajak);

                    if(isset($request->akun_pajak[$key])){
                        $get_akun_pajak = MasterPajak::whereIn('id',$request->akun_pajak[$key])->get();
                        if(!is_null($get_akun_pajak)){
                            foreach ($get_akun_pajak as $key_akun => $value_akun) {

                                $pajak = $biaya*$value_akun->persentase_efektif/100;

                                if($value_akun->is_pemotongan){ 
                                    $subtotalpajak[$key] -= $pajak; 
                                    $pemotongan = '(-)';
                                } else { 
                                    $subtotalpajak[$key] += $pajak;
                                    $pemotongan = '(+)';
                                }

                                $history_hitung_pajak[] = $value_akun->nama.' - '.$value_akun->persentase_efektif.' - '.$pemotongan.' pajak:'.number_format($pajak);

                                $akun_pajak[$value_akun->id] = $get_akun_pajak[$key_akun];
                                

                                if(!isset($subtotalpajak_perakun[$value_akun->id])){
                                    $subtotalpajak_perakun[$value_akun->id] = $pajak;
                                } else {
                                    $subtotalpajak_perakun[$value_akun->id] += $pajak;
                                }

                                if(!isset($list_biaya[$value_akun->id])){
                                    $list_biaya[$value_akun->id] = $biaya;
                                } else {
                                    $list_biaya[$value_akun->id] += $biaya;
                                }                                

                            }                   
                        }
                    }
                    
                    $subtotal += $biaya;
                    $total += $biaya + $subtotalpajak[$key];
                }
            }

            // dd($subtotalpajak);
            
            // dd($akun_pajak);
            if(count($akun_pajak)){
                $view_pajak = View::make('biaya._detail_pajak',compact('akun_pajak','list_biaya','history_hitung_pajak','subtotalpajak_perakun'))->render();
            }

            echo json_encode(array("status"=>1, "view"=>$view_pajak, "subtotal"=>$subtotal, "total"=>$total));

        } catch (Exception $e) {
            echo json_encode(array("status"=>2));
        }
    }







    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }

        DB::connection($this->getConnectionName())->beginTransaction();  
        try{
             // dd($request->id_akun_pajak);
            // dd($request->buktifile);


            $biaya = new Biaya;
            $biaya->setDynamicConnection();
            if(isset($request->is_bayar_nanti)){
                $biaya->id_akun_bayar = null;
                $biaya->is_bayar_nanti = $request->is_bayar_nanti;
                $biaya->id_status = 1; // open jika bayar nanti
            } else {
                $biaya->id_akun_bayar = $request->id_akun_bayar;
                $biaya->id_status = 2; // close jika bukan bayar nanti
            }

            $biaya->tipe_penerima = $request->tipe_penerima;
            $biaya->id_penerima = $request->id_penerima;
            $biaya->tgl_transaksi = $request->tgl_transaksi;    
            $biaya->id_cara_pembayaran = $request->id_cara_pembayaran;    
            // $biaya->no_biaya = $request->no_biaya;    
            $biaya->tag = $request->tag;    
            $biaya->alamat_penagihan = $request->alamat_penagihan;
            if(isset($request->is_termasuk_pajak)){ $biaya->is_termasuk_pajak = $request->is_termasuk_pajak; }    
            $biaya->memo = $request->memo;
            

            $validator = $biaya->validate();
            // dd($validator->messages()->get('*'));

            if(!$validator->fails()){

                $biaya->id_apotek = session('id_apotek_active');
                $biaya->created_by = Auth::user()->id;
                $biaya->created_at = Date("Y-m-d H:i:s");

                $getdataapotek = MasterApotek::on($this->getConnectionName())->find(session('id_apotek_active'));
                if(!empty($getdataapotek)){
                    $aptk =  $getdataapotek->nama_singkat;
                } else {
                    $aptk =  '[noapotek]';
                }

                $bulan = Date('m');
                // dd($bulan);

                $format = $aptk.'-'.$bulan.'-';

                // generate nomor //
                # no urut 4 digit (0001)
                $getLastNo = Biaya::select('no_biaya')
                            ->whereRaw('MONTH(tgl_transaksi) = \''.Date("m",strtotime($request->tgl_transaksi)).'\'')
                            ->orderBy("id","desc")->first();
                // dd($getLastNo);
                if(is_null($getLastNo)){
                    $nomorbaru = '0001';
                } else {
                    $getlastnomor = explode($format,$getLastNo->no_biaya);
                    $lastno = (int)end($getlastnomor);
                    $lastno = ($lastno+1)/10000;
                    $lastno = explode('.',$lastno);
                    $nomorbaru = end($lastno);
                    // dd($lastno);
                }

                $biaya->no_biaya = $format.$nomorbaru;
                // dd($format.$nomorbaru);


                if($biaya->save()){
     
                    // ---- save jurnal ---- //
                    $statusjurnalumum = 0;
                    $jurnal_umum = new JurnalUmum;
                    $jurnal_umum->setDynamicConnection();
                    $jurnal_umum->id_apotek = $biaya->id_apotek;
                    $jurnal_umum->flag_trx = $this->flag_trx;
                    $jurnal_umum->kode_referensi = $biaya->id;
                    $jurnal_umum->no_transaksi = $biaya->no_biaya;
                    $jurnal_umum->tgl_transaksi = $biaya->tgl_transaksi;
                    $jurnal_umum->tag = $biaya->tag;
                    $jurnal_umum->memo = $biaya->memo;
                    $jurnal_umum->created_at = $biaya->created_at;
                    $jurnal_umum->created_by = $biaya->created_by;
                    if($jurnal_umum->save()){ $statusjurnalumum = 1;}


                    // save detail
                    $subtotal = 0;
                    if(isset($request->id_kode_akun)){
                        foreach ($request->id_kode_akun as $key => $kode) {
                            if(!is_null($kode)){
                                $detail = new BiayaDetail;
                                $detail->setDynamicConnection();
                                $detail->id_biaya = $biaya->id;
                                $detail->id_kode_akun = $kode;
                                $detail->deskripsi = $request->deskripsi[$key];

                                if(isset($request->id_akun_pajak[$key])){ 
                                    $detail->id_akun_pajak = json_encode($request->id_akun_pajak[$key]);
                                } else {
                                    $detail->id_akun_pajak = null;
                                }

                                $detail->biaya = $request->biaya[$key];
                                $detail->created_by = Auth::user()->id;
                                $detail->created_at = Date("Y-m-d H:i:s");
                                if($detail->save()){

                                    $statusdetiljurnal[$detail->id] = 0;
                                    if($statusjurnalumum){
                                        // insert detil jurnal //
                                        $detiljurnal = new JurnalUmumDetail;
                                        $detiljurnal->setDynamicConnection();
                                        $detiljurnal->id_jurnal = $jurnal_umum->id; 
                                        $detiljurnal->id_kode_akun = $detail->id_kode_akun; 
                                        $detiljurnal->flag_trx = $this->flag_trx; 
                                        $detiljurnal->kode_referensi = $detail->id; 
                                        $detiljurnal->deskripsi = $detail->deskripsi; 
                                        $detiljurnal->debit = $detail->biaya;
                                        $detiljurnal->created_by = Auth::user()->id;
                                        $detiljurnal->created_at = Date("Y-m-d H:i:s");
                                        if($detiljurnal->save()){ $statusdetiljurnal[$detail->id] = 1; };
                                    }

                                }

                                $subtotal += $request->biaya[$key];
                            }
                        }
                    }



                    $ppn_potong = 0;
                    if($request->id_akun_ppn_potong != ""){
                        if($request->options_pajak == "1"){
                            $ppn_potong = $request->potongan_pajak/100 * $subtotal;
                        } else {
                            $ppn_potong = $request->potongan_pajak;
                        }
                        $biaya->id_akun_ppn_potong = $request->id_akun_ppn_potong;
                        $biaya->ppn_potong = $ppn_potong;
                    }

                    // dd($ppn_potong);

                    $biaya->subtotal = $subtotal;
                    $biaya->save();

                    $total_kredit = $subtotal+$ppn_potong;


                    if($statusjurnalumum){
                        // save detail jurnal untuk pajak //
                        if($request->id_akun_ppn_potong != ""){
                            // insert detil jurnal //
                            $detiljurnal = new JurnalUmumDetail;
                            $detiljurnal->setDynamicConnection();
                            $detiljurnal->id_jurnal = $jurnal_umum->id; 
                            $detiljurnal->id_kode_akun = $request->id_akun_ppn_potong; 
                            $detiljurnal->flag_trx = $this->flag_trx; 
                            $detiljurnal->kredit = $ppn_potong;
                            $detiljurnal->created_by = Auth::user()->id;
                            $detiljurnal->created_at = Date("Y-m-d H:i:s");
                            if($detiljurnal->save()){ $statusdetiljurnal[$detail->id] = 1; }
                        }


                        // save detail jurnal untuk akun bayar dari //
                        if(isset($request->id_akun_bayar)){
                            // insert detil jurnal //
                            $detiljurnal = new JurnalUmumDetail;
                            $detiljurnal->setDynamicConnection();
                            $detiljurnal->id_jurnal = $jurnal_umum->id; 
                            $detiljurnal->id_kode_akun = $request->id_akun_bayar; 
                            $detiljurnal->flag_trx = $this->flag_trx; 
                            $detiljurnal->kredit = $total_kredit;
                            $detiljurnal->created_by = Auth::user()->id;
                            $detiljurnal->created_at = Date("Y-m-d H:i:s");
                            if($detiljurnal->save()){ $statusdetiljurnal[$detail->id] = 1; }
                        }

                        // save total ke jurnal //
                        $jurnal_umum->total_kredit = $total_kredit;
                        $jurnal_umum->total_debit = $total_kredit;
                        $jurnal_umum->save();
                    }



                    // save bukti
                    $errorfile = 0;
                    $errorMessages = "";
                    if(isset($request->buktifile)){
                        // dd($request->buktifile);
                        if(count($request->buktifile)){
                            foreach ($request->buktifile as $key => $bukti) {
                                // dd($bukti->getMimeType());
                                if($bukti->getMimeType() == "application/pdf" || $bukti->getMimeType() == "image/jpeg" || $bukti->getMimeType() == "image/jpg"|| $bukti->getMimeType() == "image/png"){
                                    if(!empty($bukti)){
                                        $mime = $bukti->getMimeType();

                                        $buktibiaya = new BiayaBukti;
                                        $buktibiaya->setDynamicConnection();

                                        $nama_file = $bukti->getClientOriginalName();
                                        $split = explode('.', $nama_file);
                                        $ext = $split[1];
                                        $file_name = md5($split[0] . "-" . Date("Y-m-d H:i:s"));
                                        // $logo = $request->img;
                                        $destination_path = public_path('temp\\');
                                        $destination_filename = $file_name . "." . $ext;
                                        // dd($destination_path);
                                        $path = $destination_path.$destination_filename;

                                        $bukti->move($destination_path, $destination_filename);
                                        $buktibiaya->file = $file_name . "." . $ext;

                                        // dd($bukti);

                                        if($mime == "application/pdf"){
                                            $fp = fopen($path,'r');
                                            $content = fread($fp, filesize($destination_path.$destination_filename));
                                            // $content = addslashes($content);
                                            fclose($fp);
                                        } else {
                                            $content = file_get_contents($path);
                                        }

                                        // dd($key);

                                        $buktibiaya->id_biaya = $biaya->id;
                                        $buktibiaya->type_file = $mime;
                                        $buktibiaya->file = base64_encode($content);
                                        $buktibiaya->keterangan = $request->keterangan[$key];
                                        $buktibiaya->created_by = Auth::user()->id;
                                        $buktibiaya->created_at = Date("Y-m-d H:i:s");

                                        // dd($buktibiaya);

                                        if($buktibiaya->save()){

                                            if($statusjurnalumum){
                                                // save ke file bukti jurnal
                                                $buktijurnal = new JurnalUmumBukti;
                                                $buktijurnal->setDynamicConnection();
                                                $buktijurnal->id_jurnal = $jurnal_umum->id;
                                                $buktijurnal->flag_trx = $this->flag_trx;
                                                $buktijurnal->kode_referensi = $buktibiaya->id;
                                                $buktijurnal->keterangan = $buktibiaya->keterangan;
                                                $buktijurnal->type_file = $buktibiaya->type_file;
                                                $buktijurnal->file = $buktibiaya->file;
                                                $buktijurnal->created_by = Auth::user()->id;
                                                $buktijurnal->created_at = Date("Y-m-d H:i:s");
                                                $buktijurnal->save();
                                            }

                                        }


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

                    // dd($errorfile);

                    if($errorfile > 0){ $errorMessages = "terdapat ".$errorfile." file bukti dengan ekstensi yang tidak sesuai"; }
                    DB::connection($this->getConnectionName())->commit();

                    echo json_encode(array("status" => 1,"errorMessages" => $errorMessages, "url" => url('biaya')));

                }

            } else {
                echo json_encode(array("status" => 2, "errorMessages" => "terdapat data tidak valid"));
            }
        } catch(\Exception $e){
            DB::connection($this->getConnectionName())->rollback();
            // dd($e->getMessage());
            echo json_encode(array("status" => 2, "errorMessages" => "ERROR : ".$e->getMessage()));
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
        $id = Crypt::decrypt($id);
        $biaya = Biaya::on($this->getConnectionName())->find($id);        
        if(!empty($biaya)){            
            $pajak = MasterPajak::whereNull("deleted_by")->get();
            return view('biaya.showDetail')->with(compact("biaya","pajak"));
        } else {
            echo "Data Biaya tidak ditemukan";
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
        $id = Crypt::decrypt($id);

        $biaya = Biaya::on($this->getConnectionName())->find($id);

        $akundompet = MasterKodeAkun::select("id",DB::RAW("CONCAT(kode,' - ',nama) as nama_akun"))
                    ->where('is_deleted', 0)->pluck('nama_akun', 'id');
        $akundompet->prepend('-- Pilih Akun --','');

        $supplier = $this->get_penerima();
        /*$supplier = MasterSuplier::on($this->getConnectionName())->where("is_deleted",0)->pluck('nama', 'id');
        $supplier->prepend('-- Pilih Penerima --','');*/

        $carabayar = $this->carabayar;

        /* --- untuk load detail --- */
        $kode_akun= MasterKodeAkun::select('id',DB::RAW('CONCAT(kode,\' - \',nama) as nama_akun'))
                    ->where('is_deleted', 0)->pluck('nama_akun', 'id');
        $kode_akun->prepend('-- Pilih Akun --','');

        $listpajak = MasterPajak::whereNull('deleted_by')->pluck('nama', 'id');
       // $listpajak->prepend('-- Pilih Pajak --','');


        return view('biaya.edit')->with(compact('biaya','akundompet','supplier','carabayar','kode_akun','listpajak'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }

        DB::connection($this->getConnectionName())->beginTransaction();  
        try{
            // dd($request->input());
            // dd($request->buktifile);
            $id = Crypt::decrypt($id);

            $biaya = Biaya::on($this->getConnectionName())->find($id);
            if(isset($request->is_bayar_nanti)){
                $biaya->id_akun_bayar = null;
                $biaya->is_bayar_nanti = $request->is_bayar_nanti;
                $biaya->id_status = 1; // open jika bayar nanti
            } else {
                $biaya->id_akun_bayar = $request->id_akun_bayar;
                $biaya->id_status = 2; // open jika bukan bayar nanti
            }

            $biaya->tipe_penerima = $request->tipe_penerima;
            $biaya->id_penerima = $request->id_penerima;
            $biaya->tgl_transaksi = $request->tgl_transaksi;    
            $biaya->id_cara_pembayaran = $request->id_cara_pembayaran;    
            $biaya->no_biaya = $request->no_biaya;    
            $biaya->tag = $request->tag;    
            $biaya->alamat_penagihan = $request->alamat_penagihan;
            if(isset($request->is_termasuk_pajak)){ $biaya->is_termasuk_pajak = $request->is_termasuk_pajak; }    
            $biaya->memo = $request->memo;
            

            $validator = $biaya->validate();
            // dd($validator->messages());

            if(!$validator->fails()){

                $biaya->id_apotek = session('id_apotek_active');
                $biaya->updated_by = Auth::user()->id;
                $biaya->updated_at = Date("Y-m-d H:i:s");

                if($biaya->save()){

                    // check jurnal umum //
                    $check_jurnal_umum = JurnalUmum::on($this->getConnectionName())->where("flag_trx",$this->flag_trx)->where("kode_referensi",$biaya->id)
                                        ->whereNull('deleted_by')->first();
                    if(!empty($check_jurnal_umum)){
                        $jurnal_umum = JurnalUmum::on($this->getConnectionName())->find($check_jurnal_umum->id);    
                        $jurnal_umum->updated_by = Auth::user()->id;
                        $jurnal_umum->updated_at = Date("Y-m-d H:i:s");
                    } else {
                        $jurnal_umum = new JurnalUmum; 
                        $jurnal_umum->setDynamicConnection();
                        $jurnal_umum->created_by = Auth::user()->id;
                        $jurnal_umum->created_at = Date("Y-m-d H:i:s");
                    }

                    // ---- save jurnal ---- //
                    $statusjurnalumum = 0;
                    $jurnal_umum->id_apotek = $biaya->id_apotek;
                    $jurnal_umum->flag_trx = $this->flag_trx;
                    $jurnal_umum->kode_referensi = $biaya->id;
                    $jurnal_umum->no_transaksi = $biaya->no_biaya;
                    $jurnal_umum->tgl_transaksi = $biaya->tgl_transaksi;
                    $jurnal_umum->tag = $biaya->tag;
                    $jurnal_umum->memo = $biaya->memo;
                    if($jurnal_umum->save()){ $statusjurnalumum = 1;}



                    // save detail
                    $subtotal = 0;
                    $array_detail = array();
                    if(isset($request->id_kode_akun)){
                        foreach ($request->id_kode_akun as $key => $kode) {

                            if(!is_null($kode)){
                                if($request->iddetail[$key] == ""){
                                    $detail = new BiayaDetail;
                                    $detail->setDynamicConnection();
                                    $detail->created_by = Auth::user()->id;
                                    $detail->created_at = Date("Y-m-d H:i:s");
                                } else {
                                    $iddetail = Crypt::decrypt($request->iddetail[$key]);
                                    $detail = BiayaDetail::on($this->getConnectionName())->find($iddetail);
                                    if(empty($detail)){ 
                                        $detail = new BiayaDetail;
                                        $detail->setDynamicConnection();
                                        $detail->created_by = Auth::user()->id;
                                        $detail->created_at = Date("Y-m-d H:i:s"); 
                                    } else {
                                        $detail->updated_by = Auth::user()->id;
                                        $detail->updated_at = Date("Y-m-d H:i:s"); 
                                    }
                                }
                                
                                $detail->id_biaya = $biaya->id;
                                $detail->id_kode_akun = $kode;
                                $detail->deskripsi = $request->deskripsi[$key];

                                if(isset($request->id_akun_pajak[$key])){
                                    $detail->id_akun_pajak =json_encode($request->id_akun_pajak[$key]);
                                } else {
                                    $detail->id_akun_pajak = null;
                                }
                                
                                $detail->biaya = $request->biaya[$key];
                               
                                if($detail->save()){

                                    $statusdetiljurnal[$detail->id] = 0;
                                    if($statusjurnalumum){
                                        // cek detil jurnal sudah ada atau tidak //
                                        $check_detil_jurnal = JurnalUmumDetail::on($this->getConnectionName())->where('flag_trx',$this->flag_trx)
                                                            ->where("kode_referensi",$detail->id)
                                                            ->whereNull('deleted_by')
                                                            ->first();

                                        if(empty($check_detil_jurnal)){
                                            $detiljurnal = new JurnalUmumDetail;
                                            $detiljurnal->setDynamicConnection();
                                            $detiljurnal->created_by = Auth::user()->id;
                                            $detiljurnal->created_at = Date("Y-m-d H:i:s");
                                        } else {
                                            $detiljurnal = JurnalUmumDetail::on($this->getConnectionName())->find($check_detil_jurnal->id);
                                            if(empty($detiljurnal)){
                                                $detiljurnal = new JurnalUmumDetail;
                                                $detiljurnal->setDynamicConnection();
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
                                        $detiljurnal->deskripsi = $detail->deskripsi; 
                                        $detiljurnal->debit = $detail->biaya;
                                        if($detiljurnal->save()){ $statusdetiljurnal[$detail->id] = 1; };
                                    }

                                    $array_detail[] = $detail->id;

                                }

                                $subtotal += $request->biaya[$key];
                            }
                        }
                    }

                    // delete yang tidak digunakan //
                    # --- soft delete detail biaya yang tidak digunakan --- #
                    $del_biaya_detail = BiayaDetail::on($this->getConnectionName())->where('id_biaya',$biaya->id)
                                    ->whereNotIn('id',$array_detail)
                                    ->update([
                                        'deleted_at' => Date("Y-m-d H:i:s"),
                                        'deleted_by' => Auth::user()->id
                                    ]);

                    # --- soft delete detail jurnal yang tidak digunakan --- #
                    $del_jurnal_detail = JurnalUmumDetail::on($this->getConnectionName())->where('flag_trx',$this->flag_trx)
                                    ->where('id_jurnal',$jurnal_umum->id)
                                    ->whereNotIn("kode_referensi",$array_detail)
                                    ->update([
                                        'deleted_at' => Date("Y-m-d H:i:s"),
                                        'deleted_by' => Auth::user()->id
                                    ]);


                    $ppn_potong = 0;
                    if($request->id_akun_ppn_potong != ""){
                        if($request->options_pajak == "1"){
                            $ppn_potong = $request->potongan_pajak/100 * $subtotal;
                        } else {
                            $ppn_potong = $request->potongan_pajak;
                        }
                        $biaya->id_akun_ppn_potong = $request->id_akun_ppn_potong;
                        $biaya->ppn_potong = $ppn_potong;
                    } else {
                        $biaya->id_akun_ppn_potong = null;
                        $biaya->ppn_potong = 0;
                    }

                    // dd($ppn_potong);

                    $biaya->subtotal = $subtotal;
                    $biaya->save();

                    $total_kredit = $subtotal+$ppn_potong;


                    if($statusjurnalumum){
                        // delete detail jurnal yang kode_referensi nya null //
                        $del = JurnalUmumDetail::on($this->getConnectionName())->where('id_jurnal',$jurnal_umum->id)
                                ->whereNull('kode_referensi')->delete();


                        // save detail jurnal untuk pajak //
                        if($request->id_akun_ppn_potong != ""){
                            // insert detil jurnal //
                            $detiljurnal = new JurnalUmumDetail;
                            $detiljurnal->setDynamicConnection();
                            $detiljurnal->id_jurnal = $jurnal_umum->id; 
                            $detiljurnal->id_kode_akun = $request->id_akun_ppn_potong; 
                            $detiljurnal->flag_trx = $this->flag_trx; 
                            $detiljurnal->debit = $ppn_potong;
                            $detiljurnal->created_by = Auth::user()->id;
                            $detiljurnal->created_at = Date("Y-m-d H:i:s");
                            if($detiljurnal->save()){ $statusdetiljurnal[$detail->id] = 1; }
                        }


                        // save detail jurnal untuk akun bayar dari //
                        if(isset($request->id_akun_bayar)){
                            // insert detil jurnal //
                            $detiljurnal = new JurnalUmumDetail;
                            $detiljurnal->setDynamicConnection();
                            $detiljurnal->id_jurnal = $jurnal_umum->id; 
                            $detiljurnal->id_kode_akun = $request->id_akun_bayar; 
                            $detiljurnal->flag_trx = $this->flag_trx; 
                            $detiljurnal->kredit = $total_kredit;
                            $detiljurnal->created_by = Auth::user()->id;
                            $detiljurnal->created_at = Date("Y-m-d H:i:s");
                            if($detiljurnal->save()){ $statusdetiljurnal[$detail->id] = 1; }
                        }

                        // save total ke jurnal //
                        $jurnal_umum->total_kredit = $total_kredit;
                        $jurnal_umum->total_debit = $total_kredit;
                        $jurnal_umum->save();
                    }



                    $errorfile = 0;
                    $errorMessages = "";
                    $array_bukti = array();                
                   /* if(isset($request->buktifile)){
                        if(count($request->buktifile)){
                            foreach ($request->buktifile as $key => $bukti) {
                                // dd($bukti->getMimeType());
                                if($bukti->getMimeType() == "application/pdf" || $bukti->getMimeType() == "image/jpeg" || $bukti->getMimeType() == "image/jpg"){
                                    if(!empty($bukti)){
                                        $mime = $bukti->getMimeType();

                                        if($request->idbukti[$key] == ""){
                                            $buktibiaya = new BiayaBukti;
                                            $buktibiaya->setDynamicConnection();
                                        } else {
                                            $idbukti = Crypt::decrypt($request->idbukti[$key]);
                                            $buktibiaya = BiayaBukti::on($this->getConnectionName())->find($idbukti);
                                        }                                    

                                        $nama_file = $bukti->getClientOriginalName();
                                        $split = explode('.', $nama_file);
                                        $ext = $split[1];
                                        $file_name = md5($split[0] . "-" . Date("Y-m-d H:i:s"));
                                        // $logo = $request->img;
                                        $destination_path = public_path('temp\\');
                                        $destination_filename = $file_name . "." . $ext;
                                        // dd($destination_path);
                                        $path = $destination_path.$destination_filename;

                                        $bukti->move($destination_path, $destination_filename);
                                        $buktibiaya->file = $file_name . "." . $ext;

                                        // dd($bukti);

                                        if($mime == "application/pdf"){
                                            $fp = fopen($path,'r');
                                            $content = fread($fp, filesize($destination_path.$destination_filename));
                                            // $content = addslashes($content);
                                            fclose($fp);
                                        } else {
                                            $content = file_get_contents($path);
                                        }

                                        // dd($key);

                                        $buktibiaya->id_biaya = $biaya->id;
                                        $buktibiaya->type_file = $mime;
                                        $buktibiaya->file = base64_encode($content);
                                        $buktibiaya->keterangan = $request->keterangan[$key];
                                        $buktibiaya->created_by = Auth::user()->id;
                                        $buktibiaya->created_at = Date("Y-m-d H:i:s");

                                        // dd($buktibiaya);

                                        if($buktibiaya->save()){

                                            if($statusjurnalumum){
                                                // save ke file bukti jurnal

                                                $check_bukti_jurnal = JurnalUmumBukti::on($this->getConnectionName())->where('id_jurnal',$jurnal_umum->id)
                                                                    ->where('flag_trx',$this->flag_trx)
                                                                    ->where('kode_referensi',$buktibiaya->id)
                                                                    ->whereNull('deleted_by')
                                                                    ->first();

                                                if(!empty($check_bukti_jurnal)){
                                                    $buktijurnal = JurnalUmumBukti::on($this->getConnectionName())->find($check_bukti_jurnal->id);    
                                                    $buktijurnal->updated_by = Auth::user()->id;
                                                    $buktijurnal->updated_at = Date("Y-m-d H:i:s");
                                                } else {
                                                    $buktijurnal = new JurnalUmumBukti; 
                                                    $buktijurnal->setDynamicConnection();
                                                    $buktijurnal->created_by = Auth::user()->id;
                                                    $buktijurnal->created_at = Date("Y-m-d H:i:s");
                                                }

                                                $buktijurnal->id_jurnal = $jurnal_umum->id;
                                                $buktijurnal->flag_trx = $this->flag_trx;
                                                $buktijurnal->kode_referensi = $buktibiaya->id;
                                                $buktijurnal->keterangan = $buktibiaya->keterangan;
                                                $buktijurnal->type_file = $buktibiaya->type_file;
                                                $buktijurnal->file = $buktibiaya->file;
                                                $buktijurnal->save();
                                            }

                                            $array_bukti[] = $buktibiaya->id;

                                        }


                                        if (File::exists($path)) {
                                            unlink($path);
                                        }
                                    }
                                } else {
                                    $errorfile++;
                                }
                            }

                            dd($array_bukti);
                        }
                    }*/


                    if(isset($request->idbukti)){
                        if(count($request->idbukti)){
                            foreach ($request->idbukti as $key => $id_bukti) {

                                if($id_bukti == ""){
                                    $buktibiaya = new BiayaBukti;
                                    $buktibiaya->setDynamicConnection();
                                } else {
                                    $idbukti = Crypt::decrypt($id_bukti);
                                    $buktibiaya = BiayaBukti::on($this->getConnectionName())->find($idbukti);
                                } 


                                // hanya diproses jika (id kosong & ada file) atau id tidak kosong //
                                if(($id_bukti == "" && isset($request->buktifile[$key])) || $id_bukti != ""){

                                    // kalau ada file //
                                    if(isset($request->buktifile[$key])){

                                        $bukti = $request->buktifile[$key];
                                        if(!empty($bukti)){
                                            // dd($bukti->getMimeType());
                                            if($bukti->getMimeType() == "application/pdf" || $bukti->getMimeType() == "image/jpeg" || $bukti->getMimeType() == "image/jpg" || $bukti->getMimeType() == "image/png"){

                                                $mime = $bukti->getMimeType();
                                                $nama_file = $bukti->getClientOriginalName();
                                                $split = explode('.', $nama_file);
                                                $ext = $split[1];
                                                $file_name = md5($split[0] . "-" . Date("Y-m-d H:i:s"));
                                                // $logo = $request->img;
                                                $destination_path = public_path('temp\\');
                                                $destination_filename = $file_name . "." . $ext;
                                                // dd($destination_path);
                                                $path = $destination_path.$destination_filename;

                                                $bukti->move($destination_path, $destination_filename);
                                                $buktibiaya->file = $file_name . "." . $ext;


                                                if($mime == "application/pdf"){
                                                    $fp = fopen($path,'r');
                                                    $content = fread($fp, filesize($destination_path.$destination_filename));
                                                    // $content = addslashes($content);
                                                    fclose($fp);
                                                } else {
                                                    $content = file_get_contents($path);
                                                }

                                                $buktibiaya->type_file = $mime;
                                                $buktibiaya->file = base64_encode($content);

                                                if (File::exists($path)) {
                                                    unlink($path);
                                                }

                                            }
                                        
                                        }

                                    }


                                    $buktibiaya->id_biaya = $biaya->id;
                                    $buktibiaya->keterangan = $request->keterangan[$key];
                                    $buktibiaya->created_by = Auth::user()->id;
                                    $buktibiaya->created_at = Date("Y-m-d H:i:s");

                                    // dd($buktibiaya);
                                    if($buktibiaya->save()){
                                        if($statusjurnalumum){
                                            
                                            // save ke file bukti jurnal
                                            $check_bukti_jurnal = JurnalUmumBukti::on($this->getConnectionName())->where('id_jurnal',$jurnal_umum->id)
                                                        ->where('flag_trx',$this->flag_trx)
                                                        ->where('kode_referensi',$buktibiaya->id)
                                                        ->whereNull('deleted_by')
                                                        ->first();

                                            if(!empty($check_bukti_jurnal)){
                                                $buktijurnal = JurnalUmumBukti::on($this->getConnectionName())->find($check_bukti_jurnal->id);    
                                                $buktijurnal->updated_by = Auth::user()->id;
                                                $buktijurnal->updated_at = Date("Y-m-d H:i:s");
                                            } else {
                                                $buktijurnal = new JurnalUmumBukti; 
                                                $buktijurnal->setDynamicConnection();
                                                $buktijurnal->created_by = Auth::user()->id;
                                                $buktijurnal->created_at = Date("Y-m-d H:i:s");
                                            }

                                            $buktijurnal->id_jurnal = $jurnal_umum->id;
                                            $buktijurnal->flag_trx = $this->flag_trx;
                                            $buktijurnal->kode_referensi = $buktibiaya->id;
                                            $buktijurnal->keterangan = $buktibiaya->keterangan;
                                            $buktijurnal->type_file = $buktibiaya->type_file;
                                            $buktijurnal->file = $buktibiaya->file;
                                            $buktijurnal->save();
                                        }

                                        $array_bukti[] = $buktibiaya->id;

                                    }

                                }

                            }
                        }
                    }


                    // dd($array_bukti);


                    // delete yang tidak digunakan //
                    # --- soft delete bukti biaya yang tidak digunakan --- #
                    $del_biaya_bukti = BiayaBukti::on($this->getConnectionName())->where('id_biaya',$biaya->id)
                                    ->whereNotIn('id',$array_bukti)
                                    ->update([
                                        'deleted_at' => Date("Y-m-d H:i:s"),
                                        'deleted_by' => Auth::user()->id
                                    ]);

                    # --- soft delete bukti jurnal yang tidak digunakan --- #
                    $del_jurnal_bukti = JurnalUmumBukti::on($this->getConnectionName())->where('flag_trx',$this->flag_trx)
                                    ->where('id_jurnal',$jurnal_umum->id)
                                    ->whereNotIn("kode_referensi",$array_bukti)
                                    ->update([
                                        'deleted_at' => Date("Y-m-d H:i:s"),
                                        'deleted_by' => Auth::user()->id
                                    ]);

                    // dd($errorfile);

                    if($errorfile > 0){ $errorMessages = "terdapat ".$errorfile." file bukti dengan ekstensi yang tidak sesuai"; }

                    DB::connection($this->getConnectionName())->commit();
                    echo json_encode(array("status" => 1,"errorMessages" => $errorMessages, "url" => url('biaya')));

                }
            }  else {
                echo json_encode(array("status" => 2, "errorMessages" => "terdapat data tidak valid"));
            }
        } catch(\Exception $e){
            DB::connection($this->getConnectionName())->rollback();
            echo json_encode(array("status" => 2, "errorMessages" => "ERROR : ".$e->getMessage()));
        }
    }


    /*
        =======================================================================================
        For     : untuk membuka file blob
        Author  : Citra
        Date    : 05/10/2021
        =======================================================================================
    */
    public function viewFile($id)
    {
        // dd($request->input());
        $id = Crypt::decrypt($id);
        $filebukti = BiayaBukti::on($this->getConnectionName())->find($id);
        if(!empty($filebukti)){
            return base64_decode($filebukti->file);
        } else {
            echo "File tidak ditemukan";
        }
    }



    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        
        $id = Crypt::decrypt($id);
        $biaya = Biaya::on($this->getConnectionName())->find($id);
        if($biaya){
            $biaya->deleted_at = date('Y-m-d H:i:s');
            $biaya->deleted_by = Auth::user()->id;
            if($biaya->save()){

                $jurnal_umum = JurnalUmum::on($this->getConnectionName())->where("flag_trx",$this->flag_trx)->where("kode_referensi",$id)
                            ->update([
                                "deleted_at" => date('Y-m-d H:i:s'),
                                "deleted_by" => Auth::user()->id
                            ]);
                if($jurnal_umum){ $deljurnal = 1; } else { $deljurnal = 0; }

                echo json_encode(array("status" => 1, "statusjurnal" => $deljurnal));

            }else{
               echo json_encode(array("status" => 0));
            }
        } else {
            echo json_encode(array("status" => 2)); 
        }
    }




    /*
        =======================================================================================
        For     : Import xls form
        Author  : Citra
        Date    : 07/10/2021
        =======================================================================================
    */
    public function ImportBiaya()
    {
        return view('biaya._import_data');
    }






    /*
        =======================================================================================
        For     : Download template import xls
        Author  : Citra
        Date    : 08/10/2021
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
                return [
                    'Kode Akun Bayar Dari',
                    'Bayar Nanti(y/n)', 
                    'Batas Pembayaran', 
                    '(*)No. Transaksi', 
                    '(*)Tanggal Transaksi', 
                    'Kode Cara Pembayaran (1:cash,2:transfer)', 
                    '(*)ID Supplier',
                    '(*)Alamat Penagihan', 
                    'tag (pisahkan dengan tanda ,)', 
                    'Memo', 
                    'Kode Akun Pajak Potong', 
                    'Nominal Potongan Pajak', 
                    '(*)Kode Akun', 
                    '(*)Deskripsi', 
                    'Kode Akun Pajak (pisahkan dengan tanda ,)',
                    '(*)Biaya (tanpa titik)'
                ];
            }

            public function columnWidths(): array
            {
                return [
                    'A' => 15,
                    'B' => 20,
                    'C' => 20,
                    'D' => 30,
                    'E' => 30,
                    'F' => 30,
                    'G' => 20,        
                    'H' => 20,        
                    'I' => 20,        
                    'J' => 20,        
                    'K' => 20,        
                    'L' => 20,        
                    'M' => 20,        
                    'N' => 20,        
                    'O' => 20,        
                    'P' => 20        
                ];
            }

            
            public function title(): string
            {
                return 'Template Import';
            }

        },"Template Import Biaya.xlsx");*/


        return (new BiayaTemplateExportSheet())->download('Template Import Biaya.xlsx');
    }



    /*
        =======================================================================================
        For     : Import xls prosess
        Author  : Citra
        Date    : 09/10/2021
        =======================================================================================
    */
    public function import_biaya_from_excel(Request $request)
    {
        // dd($request->import_file);
        if(Input::hasFile('import_file')){
            $path = Input::file('import_file')->getRealPath();

            $importxls = new BiayaImportSheet;
            $import = Excel::import($importxls, $path);
            // dd($importxls->importstatus[0]->importstatus);

            // return status //
            if(isset($importxls->importstatus[0]->importstatus['biayaimport_ok'])){
                $status = $importxls->importstatus[0]->importstatus;
            } else if(isset($importxls->importstatus[1]->importstatus['biayaimport_ok'])){
                $status = $importxls->importstatus[1]->importstatus;
            }


            // return status //
            $keterangan = "Berhasil import : ".$status['biayaimport_ok']." Baris <br> Gagal import : ".$status['biayaimport_error']." Baris <br> Baris yang sama : ".$status['duplicatedata']." Baris";

            $status = array("status"=>1,"keterangan"=>$keterangan);
            
            echo json_encode($status);
        }
    }



    /*
        =======================================================================================
        For     : Update Status Biaya
        Author  : Citra
        Date    : 18/10/2021
        =======================================================================================
    */
    public function updateStatus($id, Request $request)
    {
        // dd($request->input());
        $id = Crypt::decrypt($id);
        $biaya = Biaya::on($this->getConnectionName())->find($id);
        if(!is_null($biaya)){
            $biaya->id_status = $request->st;
            $biaya->updated_by = Auth::user()->id;
            $biaya->updated_at = Date("Y-m-d H:i:s");
            if($biaya->save()){
                echo json_encode(array("status"=>1));
            } else {
                echo json_encode(array("status"=>2, "error" => "Gagal menyimpan data"));
            }
        } else {
            echo json_encode(array("status"=>2, "error" => "Data Biaya tidak ditemukan"));
        }
    }



        
}
