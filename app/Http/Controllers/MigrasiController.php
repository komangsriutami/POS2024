<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ScheduleMigrasi;
use App\ScheduleMigrasiDetail;
use App\MasterApotek;

use App\TransaksiPenjualanDetail;
use App\TransaksiPembelianDetail;
use App\TransaksiTODetail;
use App\TransaksiPODetail;
use App\HistoriStok;

use App;
use Datatables;
use DB;
use Excel;
use Auth;
use Crypt;
class MigrasiController extends Controller
{
    private $squences_limit = 500;
    private $table_migrasi = 'tb_nota_penjualan_migrasi';
    private $table_migrasi_detail = 'tb_detail_nota_penjualan_migrasi';
    private $table_kwitansi_migrate_log = 'tb_nota_penjualan_migrasi_log';


    public function index() {
        return view('migrasi.index');
    }   


    public function getListData(Request $request)
    {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::statement(DB::raw('set @rownum = 0'));
        $data = ScheduleMigrasi::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'m_schedule_migrasi.*']);

        $datatables = Datatables::of($data);

        return $datatables
        ->addcolumn('jumlah', function($data) {
            //$getData = $this->getDataPenjualan('count',$data->tahun,$data->bulan);
            //return number_format($getData->jumlah);
            return 0;
        })
        ->addcolumn('jumlah_migrasi', function($data) {
            /*$getData = $this->getDataPenjualan('count',$data->tahun,$data->bulan);
            $getDataMigrasi = $this->getDataMigrasi('count',$data->tahun,$data->bulan);
            
            if($getData->jumlah != $getDataMigrasi->jumlah){
                $class = 'text-danger';
            } else {
                $class = 'text-success';
            }
            
            return '<b class="'.$class.'">'.number_format($getDataMigrasi->jumlah).'</b>';*/
            return 0;
        })
        ->editcolumn('tahun', function($data) {
            return $data->tahun;
        })
        ->editcolumn('bulan', function($data) {
            return $data->bulan;
        })
        ->editcolumn('status', function($data) {
            if($data->status == 1){
                $s = 'Selesai';
            } elseif($data->status == 2) {
                $s = 'Terjadi Kesalahan';
            } else {
                $s = 'Belum';
            }

            return $s;
        })
        ->editcolumn('created_at', function($data) {
            if(!is_null($data->created_at)){ return date('d-m-Y H:i',strtotime($data->created_at)); }
        })
        ->editcolumn('updated_at', function($data) {
            if(!is_null($data->updated_at)){ return date('d-m-Y H:i',strtotime($data->updated_at)); }
        })
        ->addcolumn('action', function($data) {
            $action = '';

            $href = url('kwitansi-migrasi/'.Crypt::encrypt($data->id));

            if($data->status == 0){
                $action = '<a href="'.$href.'" class="btn btn-info btn-xs" onClick="mulai_migrasi('.Crypt::encrypt($data->id).',\'migrasi kwitansi '.$data->tahun.' bulan'.$data->bulan.'\')" data-toggle="tooltip" data-placement="top" title="Klik untuk melakukan migrasi">[proses]</a>';
            } else {
                $action = '<a href="'.$href.'" class="btn btn-info btn-xs" onClick="mulai_migrasi('.Crypt::encrypt($data->id).',\'migrasi ulang kwitansi '.$data->tahun.' bulan'.$data->bulan.' \')" data-toggle="tooltip" data-placement="top" title="Klik untuk melakukan migrasi ulang">[proses ulang]</a>';
            }


            return $action;

        })
        ->rawColumns(['action','jumlah_migrasi'])
        ->addIndexColumn()
        ->make(true);  
    }

    public function getDataTrx($tipe, $tgl) {
        if($tipe == "count"){
            $query = HistoriStok::select(DB::RAW('COUNT(id_obat) as jumlah'))
                    ->whereDate('created_at',$tgl)
                    ->orwhereNull('created_at')
                    ->groupBy('id_obat')
                    ->first();
        } else {
            $query = HistoriStok::select(DB::RAW('COUNT(id_obat) as jumlah'))
                    ->whereDate('created_at',$tgl)
                    ->orwhereNull('created_at')
                    ->groupBy('id_obat')
                    ->get();
        }
        
        return $query;
    }

    public function getDataPenjualan($tipe,$ta,$bln, $ap)
    {
        if($tipe == "count"){
            $query = TransaksiPenjualanDetail::select(DB::RAW('COUNT(tb_detail_nota_penjualan.id) as jumlah'))
                    ->join('tb_nota_penjualan as a', 'a.id', '=', 'tb_detail_nota_penjualan.id_nota')
                    ->whereYear('a.tgl_nota',$ta)
                    ->whereMonth('a.tgl_nota',$bln)
                    ->whereMonth('a.id_apotek_nota',$ap)
                    ->where('a.is_deleted', 0)
                    ->where('tb_detail_nota_penjualan.is_deleted', 0)
                    ->first();
        } else {
            $query = TransaksiPenjualanDetail::select(['tb_detail_nota_penjualan.*'])
                    ->join('tb_nota_penjualan as a', 'a.id', '=', 'tb_detail_nota_penjualan.id_nota')
                    ->whereYear('a.tgl_nota',$ta)
                    ->whereMonth('a.tgl_nota',$bln)
                    ->whereMonth('a.id_apotek_nota',$ap)
                    ->where('a.is_deleted', 0)
                    ->where('tb_detail_nota_penjualan.is_deleted', 0)
                    ->get();
        }
        
        return $query;
    }

   
    public function getDataPembelian($tipe,$ta,$bln)
    {
        if($tipe == "count"){
            $query = TransaksiPembelianDetail::select(DB::RAW('COUNT(tb_detail_nota_pembelian.id) as jumlah'))
                    ->join('tb_nota_pembelian as a', 'a.id', '=', 'tb_detail_nota_pembelian.id_nota')
                    ->whereYear('a.tgl_nota',$ta)
                    ->whereMonth('a.tgl_nota',$bln)
                    ->whereMonth('a.id_apotek_nota',$ap)
                    ->where('a.is_deleted', 0)
                    ->where('tb_detail_nota_pembelian.is_deleted', 0)
                    ->first();
        } else {
            $query = TransaksiPembelianDetail::select(['tb_detail_nota_penjualan.*'])
                    ->join('tb_nota_pembelian as a', 'a.id', '=', 'tb_detail_nota_pembelian.id_nota')
                    ->whereYear('a.tgl_nota',$ta)
                    ->whereMonth('a.tgl_nota',$bln)
                    ->whereMonth('a.id_apotek_nota',$ap)
                    ->where('a.is_deleted', 0)
                    ->where('tb_detail_nota_pembelian.is_deleted', 0)
                    ->get();
        }
        
        return $query;
    }


    public function getDataTOMasuk($tipe,$ta,$bln)
    {
        if($tipe == "count"){
            $query = TransaksiTODetail::select(DB::RAW('COUNT(tb_detail_nota_transfer_outlet.id) as jumlah'))
                    ->join('tb_nota_transfer_outlet as a', 'a.id', '=', 'tb_detail_nota_transfer_outlet.id_nota')
                    ->whereYear('a.tgl_nota',$ta)
                    ->whereMonth('a.tgl_nota',$bln)
                    ->whereMonth('a.id_apotek_tujuan',$ap)
                    ->where('a.is_deleted', 0)
                    ->where('tb_detail_nota_transfer_outlet.is_deleted', 0)
                    ->first();
        } else {
            $query = TransaksiTODetail::select(['tb_detail_nota_penjualan.*'])
                    ->join('tb_nota_transfer_outlet as a', 'a.id', '=', 'tb_detail_nota_transfer_outlet.id_nota')
                    ->whereYear('a.tgl_nota',$ta)
                    ->whereMonth('a.tgl_nota',$bln)
                    ->whereMonth('a.id_apotek_tujuan',$ap)
                    ->where('a.is_deleted', 0)
                    ->where('tb_detail_nota_transfer_outlet.is_deleted', 0)
                    ->get();
        }
        
        return $query;
    }

    public function getDataTOKeluar($tipe,$ta,$bln)
    {
        if($tipe == "count"){
            $query = TransaksiTODetail::select(DB::RAW('COUNT(tb_detail_nota_transfer_outlet.id) as jumlah'))
                    ->join('tb_nota_transfer_outlet as a', 'a.id', '=', 'tb_detail_nota_transfer_outlet.id_nota')
                    ->whereYear('a.tgl_nota',$ta)
                    ->whereMonth('a.tgl_nota',$bln)
                    ->whereMonth('a.id_apotek_nota',$ap)
                    ->where('a.is_deleted', 0)
                    ->where('tb_detail_nota_transfer_outlet.is_deleted', 0)
                    ->first();
        } else {
            $query = TransaksiTODetail::select(['tb_detail_nota_penjualan.*'])
                    ->join('tb_nota_transfer_outlet as a', 'a.id', '=', 'tb_detail_nota_transfer_outlet.id_nota')
                    ->whereYear('a.tgl_nota',$ta)
                    ->whereMonth('a.tgl_nota',$bln)
                    ->whereMonth('a.id_apotek_nota',$ap)
                    ->where('a.is_deleted', 0)
                    ->where('tb_detail_nota_transfer_outlet.is_deleted', 0)
                    ->get();
        }
        
        return $query;
    }

    public function getDataPO($tipe,$ta,$bln)
    {
        if($tipe == "count"){
            $query = TransaksiPODetail::select(DB::RAW('COUNT(tb_detail_nota_po.id) as jumlah'))
                    ->join('tb_nota_transfer_po as a', 'a.id', '=', 'tb_detail_nota_po.id_nota')
                    ->whereYear('a.tgl_nota',$ta)
                    ->whereMonth('a.tgl_nota',$bln)
                    ->whereMonth('a.id_apotek_nota',$ap)
                    ->where('a.is_deleted', 0)
                    ->where('tb_detail_nota_po.is_deleted', 0)
                    ->first();
        } else {
            $query = TransaksiPODetail::select(['tb_detail_nota_penjualan.*'])
                    ->join('tb_nota_transfer_po as a', 'a.id', '=', 'tb_detail_nota_po.id_nota')
                    ->whereYear('a.tgl_nota',$ta)
                    ->whereMonth('a.tgl_nota',$bln)
                    ->whereMonth('a.id_apotek_nota',$ap)
                    ->where('a.is_deleted', 0)
                    ->where('tb_detail_nota_po.is_deleted', 0)
                    ->get();
        }
        
        return $query;
    }

    public function show($id)
    {
        try {
            $id = Crypt::decrypt($id);

            $migrasi = ScheduleMigrasi::find($id);

            if(is_null($migrasi)){
                session()->flash('error', 'Data Migrasi tidak ditemukan');
                return redirect('migrasi');
            }

           /* $DataPenjualan = $this->getDataPenjualan('count',$migrasi->tahun,$migrasi->bulan);
            $DataPembelian = $this->getDataPembelian('count',$migrasi->tahun,$migrasi->bulan);
            $DataPenjualan = $this->getDataPenjualan('count',$migrasi->tahun,$migrasi->bulan);
            $DataPenjualan = $this->getDataPenjualan('count',$migrasi->tahun,$migrasi->bulan);
            $DataMigrasi = $this->getDataMigrasi('count',$migrasi->tahun,$migrasi->bulan);*/

            // dd($DataEpayment);

            return view('migrasi.show')->with(compact('migrasi'));

        } catch (\Throwable $th) {
            session()->flash('error', 'Data Migrasi tidak ditemukan');
            return redirect('kwitansi-migrasi');
        }
    }

    public function getListDetail(Request $request)
    {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        $id_migrasi = Crypt::decrypt($request->id);

        DB::statement(DB::raw('set @rownum = 0'));
        $data = ScheduleMigrasiDetail::select([
                    DB::raw('@rownum  := @rownum  + 1 AS no'),
                    'm_schedule_migrasi_detail.*'
                ])
                ->where('id_migrasi',$id_migrasi)
                ;



        $datatables = Datatables::of($data);

        return $datatables
        ->editcolumn('jml', function($data) {
            return number_format($data->jml_data);
        })
        ->editcolumn('jml_migrasi', function($data) {
            return number_format($data->jml_migrasi);
        })
        ->editcolumn('start_at', function($data) {
            if(is_null($data->start_at)){ return "-"; }
            return date('d-m-Y H:i',strtotime($data->start_at));
        })
        ->editcolumn('end_at', function($data) {
            if(is_null($data->end_at)){ return "-"; }
            return date('d-m-Y H:i',strtotime($data->end_at));
        })
        ->editcolumn('status', function($data) {
            if($data->status == 0){
                return '<b class="text-gray">Belum Mulai</b>';
            }
            
            if($data->status == 1){
                return '<b class="text-green">Berhasil</b>';
            }
            
            if($data->status == 2){
                return '<b class="text-orange">Proses Generate</b>';
            }
            
            if($data->status == 3){
                return '<b class="text-danger">Gagal. Terjadi Kesalahan</b>';
            }
        })
        ->addcolumn('action', function($data) {
            $action = '';

            if($data->status == 0){
                $action = '<div class="btn btn-info btn-xs" onClick="migrasi_init_squence(\''.Crypt::encrypt($data->id).'\',\''.$data->sequence.'\')" data-toggle="tooltip" data-placement="top" title="Klik untuk melakukan migrasi">[mulai migrasi]</div>';
            } else {
                $action = '<div class="btn btn-info btn-xs" onClick="migrasi_init_squence(\''.Crypt::encrypt($data->id).'\',\''.$data->sequence.'\')" data-toggle="tooltip" data-placement="top" title="Klik untuk melakukan migrasi ulang">[migrasi ulang]</div>';
            }

            if($data->status == 1){
                $getQueryCekKwitansi = $this->cekJumlah($data)->get();
                $cek = $getQueryCekKwitansi->where('jumlah','>',1)->count();

                if($cek > 0){
                    $btn_class = 'btn-warning';
                } else {
                    $btn_class = 'btn-primary';
                }

                $action .= ' <div class="btn '.$btn_class.' btn-xs" onClick="showSquence(\''.Crypt::encrypt($data->id).'\',\''.$data->sequence.'\')" data-toggle="tooltip" data-placement="top" title="Klik untuk melihat detail data migrasi">[Lihat Detail]</div>';

                $action .= ' <div class="btn btn-secondary  btn-xs" onClick="resetSquence(\''.Crypt::encrypt($data->id).'\',\''.$data->sequence.'\')" data-toggle="tooltip" data-placement="top" title="Reset Data Migrasi Pada squence '.$data->sequence.'">[Reset Squence]</div>';

            }   

            return $action;

        })
        ->rawColumns(['action','jumlah_migrasi','status'])
        ->addIndexColumn()
        ->make(true);  
    }

    public function cekJumlah($squence)
    {
        $data = DB::table($this->table_migrasi_detail.' as a')
                ->select(
                    DB::raw('@rownum  := @rownum  + 1 AS no'), 
                    'b.nim',
                    'a.id_old',
                    DB::RAW('count(a.id_old) as jumlah')
                    
                )
                ->join($this->table_migrasi.' as b','b.id','=','a.id_kwitansi')
                ->where('a.id_old','>=',$squence->id_kwitansi_awal)
                ->where('a.id_old','<=',$squence->id_kwitansi_akhir)
                ->groupBy('b.nim','a.id_old')
                ;

        return $data;
    }

    public function MigrasiGenerateAwal(Request $request) {
        try {
            $apotek = MasterApotek::find(session('id_apotek_active'));
            // tahun awal = 2020, tahun akhir 2024, awal bulan = 1, akhir bulan = bulan now

            // Tahun awal dan akhir
            $tahun_awal = 2024;
            $tahun_akhir = date('Y');

            // Bulan awal dan akhir
            $bulan_awal = 1;
            $bulan_akhir = 12;//date('n'); // Bulan saat ini

            // Loop untuk tahun
            for ($tahun = $tahun_awal; $tahun <= $tahun_akhir; $tahun++) {
                // Loop untuk bulan
                for ($bulan = $bulan_awal; $bulan <= $bulan_akhir; $bulan++) {
                    // Insert ke tabel m_schedule_migrasi
                    $stmt = ScheduleMigrasi::where('id_apotek', session('id_apotek_active'))
                                        ->where('tahun', $tahun)
                                        ->where('bulan', $bulan)
                                        ->first();
                    if(!isset($stmt)) {
                        $stmt = new ScheduleMigrasi;
                        $stmt->tahun = $tahun;
                        $stmt->bulan = $bulan;
                        $stmt->id_apotek = session('id_apotek_active');
                        $stmt->created_at = date('Y-m-d H:i:s');
                        $stmt->save();

                    }

                    // Ambil ID dari insert terakhir
                    $id_migrasi = $stmt->id;

                    // Hitung jumlah hari dalam bulan ini
                    $days_in_month = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

                    // Loop untuk hari dalam bulan ini
                    $total = 0;
                    for ($day = 1; $day <= $days_in_month; $day++) {
                        // Buat tanggal dalam format YYYY-MM-DD
                        $tanggal = sprintf('%04d-%02d-%02d', $tahun, $bulan, $day);

                        // Insert ke tabel m_schedule_migrasi_detail
                        $stmt_detail = ScheduleMigrasiDetail::where('id_migrasi', $id_migrasi)
                                            ->where('tgl', $tanggal)
                                            ->first();
                        if(!isset($stmt_detail)) {
                            $stmt_detail = new ScheduleMigrasiDetail;
                            $stmt_detail->id_migrasi = $id_migrasi;
                            $stmt_detail->tgl = $tanggal;
                            $stmt_detail->created_at = date('Y-m-d H:i:s');
                            $stmt_detail->save();
                        }

                        // check jumlah obat yang aktif pada periode ini
                        $getDataTrx = $this->getDataTrx('data',$tanggal);
                        if(!$getDataTrx->count()){
                            /*return json_encode(['status'=>false, "message"=>"Proses Preparation berhenti, Data Transaksi Tanggal ".$tanggal." tidak ada"]);*/
                            $stmt_detail->jml = 0;
                            $stmt_detail->save();
                        } else {
                            $arraydata = $getDataTrx->sortBy('id')->toArray();
                            $squences = array_chunk($arraydata,$this->squences_limit);

                            $stmt_detail->jml = count($squences);
                            $stmt_detail->save();

                            // bersihkan sequences sebelumnya
                            $clean_squences = ScheduleMigrasiDetailSequences::where('id_migrasi_detail',$stmt_detail->id)->delete();
                            foreach ($squencesPenjualan as $seq => $datachunk) {
                                $new_squences = new ScheduleMigrasiDetailSequences;
                                $new_squences->id_migrasi_detail = $stmt_detail->id;
                                $new_squences->sequence = ($seq+1);
                                $new_squences->id_awal = $datachunk[0]['id'];
                                $data_akhir = last($datachunk);
                                $new_squences->id_akhir = $data_akhir['id'];
                                $new_squences->jml = count($datachunk);
                                $new_squences->save();
                            }
                        }

                        $total = $total+$stmt_detail->jml;
                    }

                    $stmt->jml = $total;
                    $stmt->save();
                }
            }

            return json_encode(['status'=>true]);

        } catch (\Throwable $th) {
            return $th->getMessage();
            // return json_encode(['status'=>false, "message"=> "tidak dapat menampilkan dialog migrasi data"]);
        }
    }


    public function MigrasiInit(Request $request)
    {   
        try {

            if(isset($request->seq)){
                // kalau ada squence get id migrasi dari data detail
                $id_detail = Crypt::decrypt($request->id);
                $migrasi_detail = ScheduleMigrasiDetail::find($id_detail);
                if(is_null($migrasi_detail)){
                    return json_encode(['status'=>false, "message"=> "Data Squence tidak ada"]);
                }

                $id_migrasi = $migrasi_detail->id_migrasi;

            } else {
                $migrasi_detail = null;
                $id_migrasi = Crypt::decrypt($request->id);
            }

            

            $migrasi = ScheduleMigrasi::find($id_migrasi);
            if(is_null($migrasi)){
                return json_encode(['status'=>false, "message"=> "tidak dapat menampilkan dialog migrasi data"]);
            }

            $squences_limit = $this->squences_limit;
            
            $view =  view('migrasi.migrasi_init')->with(compact('migrasi','squences_limit','migrasi_detail'))->render();

            return json_encode(['status'=>true, 'view'=>$view]);

        } catch (\Throwable $th) {
            return $th->getMessage();
            // return json_encode(['status'=>false, "message"=> "tidak dapat menampilkan dialog migrasi data"]);
        }
    }


    public function PersiapanMigrasi(Request $request)
    {
        try {
            $seq_active = 0;

            $id_migrasi = Crypt::decrypt($request->id_migrasi);

            $migrasi = ScheduleMigrasi::find($id_migrasi);
            if(is_null($migrasi)){
                return json_encode(['status'=>false, "message"=>"Proses Preparation Gagal, Data Migrasi tidak ditemukan"]);
            }

            // kalau ada id detail
            if(isset($request->id_migrasi_detail)){ 
                $id_migrasi_detail = Crypt::decrypt($request->id_migrasi_detail); 
                $migrasi_detail = ScheduleMigrasiDetail::find($id_migrasi_detail);
                if(is_null($migrasi_detail)){
                    return json_encode(['status'=>false, "message"=>"Proses Preparation Gagal, Data Squence Migrasi tidak ditemukan"]);
                }

                $squences =[1];
                $seq_active = $migrasi_detail->sequence;

                $jum_sequence = count($squences);

            } else {

                $apoteks = MasterApotek::all();
                $jum_sequence = 0;
                foreach($apoteks as $key => $obj) {
                    // get Data Penjualan
                    $getDataPenjualan = $this->getDataPenjualan('data',$migrasi->tahun,$migrasi->bulan,$apotek->id);
                    if(!$getDataPenjualan->count()){
                        return json_encode(['status'=>false, "message"=>"Proses Preparation berhenti, Data Penjualan Tahun ".$data->tahun."bulan".$data->bulan." tidak ada"]);
                    }

                    $arraydataPenjualan = $getDataPenjualan->sortBy('id')->toArray();
                    $squencesPenjualan = array_chunk($arraydataPenjualan,$this->squences_limit);

                    // bersihkan sequences sebelumnya
                    $clean_squences = ScheduleMigrasiDetail::where('id_migrasi',$id_migrasi)->where('id_apotek',$apotek->id)->where('type',1)->delete();
                    foreach ($squencesPenjualan as $seq => $datachunk) {
                        $new_squences = new ScheduleMigrasiDetail;
                        $new_squences->id_migrasi = $id_migrasi;
                        $new_squences->id_apotek = $apotek->id;
                        $new_squences->sequence = ($seq+1);
                        $new_squences->id_awal = $datachunk[0]['id'];
                        $data_akhir = last($datachunk);
                        $new_squences->id_akhir = $data_akhir['id'];
                        $new_squences->jml = count($datachunk);
                        $new_squences->type = 1;
                        $new_squences->save();
                    }

                    $jum_sequence = $jum_sequence + count($squencesPenjualan);
                    

                    // get Data Pembelian
                    $getDataPembelian = $this->getDataPembelian('data',$migrasi->tahun,$migrasi->bulan,$apotek->id);
                    if(!$getDataPembelian->count()){
                        return json_encode(['status'=>false, "message"=>"Proses Preparation berhenti, Data Pembelian Tahun ".$data->tahun."bulan".$data->bulan." tidak ada"]);
                    }

                    $arraydataPembelian = $getDataPembelian->sortBy('id')->toArray();
                    $squencesPembelian = array_chunk($arraydataPembelian,$this->squences_limit);

                    // bersihkan sequences sebelumnya
                    $clean_squences = ScheduleMigrasiDetail::where('id_migrasi',$id_migrasi)->where('id_apotek',$apotek->id)->where('type',2)->delete();
                    foreach ($squencesPembelian as $seq => $datachunk) {
                        $new_squences = new ScheduleMigrasiDetail;
                        $new_squences->id_migrasi = $id_migrasi;
                        $new_squences->id_apotek = $apotek->id;
                        $new_squences->sequence = ($seq+1);
                        $new_squences->id_awal = $datachunk[0]['id'];
                        $data_akhir = last($datachunk);
                        $new_squences->id_akhir = $data_akhir['id'];
                        $new_squences->jml = count($datachunk);
                        $new_squences->type = 2;
                        $new_squences->save();
                    }

                    $jum_sequence = $jum_sequence + count($squencesPembelian);


                    // get Data TO Masuk
                    $getDataTOMasuk = $this->getDataTOMasuk('data',$migrasi->tahun,$migrasi->bulan,$apotek->id);
                    if(!$getDataTOMasuk->count()){
                        return json_encode(['status'=>false, "message"=>"Proses Preparation berhenti, Data TO Masuk Tahun ".$data->tahun."bulan".$data->bulan." tidak ada"]);
                    }

                    $arraydataTOMasuk = $getDataTOMasuk->sortBy('id')->toArray();
                    $squencesTOMasuk = array_chunk($arraydataTOMasuk,$this->squences_limit);

                    // bersihkan sequences sebelumnya
                    $clean_squences = ScheduleMigrasiDetail::where('id_migrasi',$id_migrasi)->where('id_apotek',$apotek->id)->where('type',3)->delete();
                    foreach ($squencesTOMasuk as $seq => $datachunk) {
                        $new_squences = new ScheduleMigrasiDetail;
                        $new_squences->id_migrasi = $id_migrasi;
                        $new_squences->id_apotek = $apotek->id;
                        $new_squences->sequence = ($seq+1);
                        $new_squences->id_awal = $datachunk[0]['id'];
                        $data_akhir = last($datachunk);
                        $new_squences->id_akhir = $data_akhir['id'];
                        $new_squences->jml = count($datachunk);
                        $new_squences->type = 3;
                        $new_squences->save();
                    }

                    $jum_sequence = $jum_sequence + count($squencesTOMasuk);

                    // get Data TO Keluar
                    $getDataTOKeluar = $this->getDataTOKeluar('data',$migrasi->tahun,$migrasi->bulan,$apotek->id);
                    if(!$getDataTOKeluar->count()){
                        return json_encode(['status'=>false, "message"=>"Proses Preparation berhenti, Data TO Keluar Tahun ".$data->tahun."bulan".$data->bulan." tidak ada"]);
                    }

                    $arraydataTOKeluar = $getDataTOKeluar->sortBy('id')->toArray();
                    $squencesTOKeluar = array_chunk($arraydataTOKeluar,$this->squences_limit);

                    // bersihkan sequences sebelumnya
                    $clean_squences = ScheduleMigrasiDetail::where('id_migrasi',$id_migrasi)->where('id_apotek',$apotek->id)->where('type',4)->delete();
                    foreach ($squencesTOKeluar as $seq => $datachunk) {
                        $new_squences = new ScheduleMigrasiDetail;
                        $new_squences->id_migrasi = $id_migrasi;
                        $new_squences->id_apotek = $apotek->id;
                        $new_squences->sequence = ($seq+1);
                        $new_squences->id_awal = $datachunk[0]['id'];
                        $data_akhir = last($datachunk);
                        $new_squences->id_akhir = $data_akhir['id'];
                        $new_squences->jml = count($datachunk);
                        $new_squences->type = 4;
                        $new_squences->save();
                    }

                    $jum_sequence = $jum_sequence + count($squencesTOKeluar);

                    // get Data PO
                    $getDataPO = $this->getDataPO('data',$migrasi->tahun,$migrasi->bulan,$apotek->id);
                    if(!$getDataPO->count()){
                        return json_encode(['status'=>false, "message"=>"Proses Preparation berhenti, Data PO Tahun ".$data->tahun."bulan".$data->bulan." tidak ada"]);
                    }

                    $arraydataPO = $getDataPO->sortBy('id')->toArray();
                    $squencesPO = array_chunk($arraydataPO,$this->squences_limit);

                    // bersihkan sequences sebelumnya
                    $clean_squences = ScheduleMigrasiDetail::where('id_migrasi',$id_migrasi)->where('id_apotek',$apotek->id)->where('type',5)->delete();
                    foreach ($squencesPO as $seq => $datachunk) {
                        $new_squences = new ScheduleMigrasiDetail;
                        $new_squences->id_migrasi = $id_migrasi;
                        $new_squences->id_apotek = $apotek->id;
                        $new_squences->sequence = ($seq+1);
                        $new_squences->id_awal = $datachunk[0]['id'];
                        $data_akhir = last($datachunk);
                        $new_squences->id_akhir = $data_akhir['id'];
                        $new_squences->jml = count($datachunk);
                        $new_squences->type = 5;
                        $new_squences->save();
                    }

                    $jum_sequence = $jum_sequence + count($squencesPO);
                   
                }
            }

            return json_encode(['status'=>true, 'id'=>Crypt::encrypt($id_migrasi), 'jumlah_seq'=>$jum_sequence, 'seq'=>$seq_active]);

        } catch (\Throwable $th) {
            throw $th;
            return json_encode(['status'=>false, "message"=>"Proses Preparation Gagal"]);
        }
    }

}
