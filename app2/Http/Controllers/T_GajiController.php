<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterApotek;
use App\MasterGroupApotek;
use App\MasterJabatan;
use App\MasterPosisi;
use App\MasterStatusKaryawan;
use App\SkemaGaji;
use App\SkemaGajiDetail;
use App\User;
use App\Absensi;
use App\TransaksiPenjualanClosing;
use App\TransaksiPenjualan;
use App;
use Datatables;
use DB;
use Auth;
use App\Traits\DynamicConnectionTrait;

class T_GajiController extends Controller
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
        $date_now = date('Y-m-d');
        return view('gaji.index')->with(compact('date_now'));
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 07/11/2020
        =======================================================================================
    */
    public function list_gaji(Request $request)
    {
        $apotek = MasterApotek::on($this->getConnectionName())->find(session('id_apotek_active'));
        $apoteker = User::on($this->getConnectionName())->find($apotek->id_apoteker);
        $id_user = Auth::user()->id;
        $hak_akses = 0;
        if($apoteker->id == $id_user) {
            $hak_akses = 1;
        }

        if(Auth::user()->is_admin == 1) {
            $hak_akses = 1;
        }

        $tanggal = date('Y-m-d');
        DB::connection($this->getConnection())->statement(DB::raw('set @rownum = 0'));
        $data = User::on($this->getConnectionName())->select([
                DB::raw('@rownum  := @rownum  + 1 AS no'),
                'users.*'
        ])
        ->leftJoin('rbac_user_apotek as a', 'a.id_user', '=', 'users.id')
        ->where(function($query) use($request, $tanggal){
            $query->where('users.is_deleted','=','0');
            $query->where('a.id_apotek','=',session('id_apotek_active'));
        })
        ->groupBy('users.id');
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request){
            $query->where(function($query) use($request){
                $query->orwhere('users.nama','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->editcolumn('nama', function($data){
            return $data->nama;
        })
        ->editcolumn('id_jabatan', function($data){
        	if($data->id_jabatan != null) {
        		$d_= $data->jabatan->nama;
        	} else {
        		$d_ = 'belum diset';
        	}
            return $d_;
        })
        ->editcolumn('id_posisi', function($data){
            if($data->id_posisi != null) {
        		$d_= $data->posisi->nama;
        	} else {
        		$d_ = 'belum diset';
        	}
            return $d_;
        })
        ->editcolumn('id_status_karyawan', function($data){
            if($data->id_status_karyawan != null) {
        		$d_= $data->status->nama;
        	} else {
        		$d_ = 'belum diset';
        	}
            return $d_;
        })
        ->addcolumn('action', function($data) use($hak_akses) {
            $btn = '<div class="btn-group">';
            $tahun = 2021;
            $bulan = 1;
            $btn .= '<a href="'.url('/gaji/detail/'.$data->id.'/'.$tahun.'/'.$bulan).'" title="Detail Data" class="btn btn-info btn-sm"><span data-toggle="tooltip" data-placement="top" title="Detail Data"><i class="fa fa-eye"></i> Detail</span></a>';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['action', 'is_tanda_terima', 'is_lunas', 'jumlah', 'suplier', 'apotek', 'id_jenis_pembelian'])
        ->addIndexColumn()
        ->make(true);  
    }

    public function create() {

    }

    public function store(Request $request) {

    }

    public function show($id) {

    }

    public function edit($id) {

    }

    public function update($request, $id) {

    }

    public function destroy($id) {

    }

    public function detail($id, $tahun, $bulan) {
        $pegawai = User::on($this->getConnectionName())->find($id);
        $skema_gaji = SkemaGaji::on($this->getConnectionName())->where('tgl_berlaku_start', '<=', date('Y-m-d'))->where('tgl_berlaku_end', '>=', date('Y-m-d'))->first();
        $skema_gaji_aktif = SkemaGajiDetail::on($this->getConnectionName())->where('id_skema_gaji', $skema_gaji->id)->where('id_jabatan', $pegawai->id_jabatan)->where('id_posisi', $pegawai->id_posisi)->where('id_status_karyawan', $pegawai->id_status_karyawan)->first();
        $jumlah_jam = Absensi::on($this->getConnectionName())->select([DB::raw('SUM(jumlah_jam_kerja) as jumlah_jam')])->where('id_user', $id)->where(DB::raw('YEAR(tgl)'), $tahun)->where(DB::raw('MONTH(tgl)'), $bulan)->first();
        $jumlah_hari_libur = 0;
        $jumlah_hari_all = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
        $jumlah_hari_all = $jumlah_hari_all-$jumlah_hari_libur;
        $jumlah_jam_kerja_all = $jumlah_hari_all*8;
        $jumlah_hari = Absensi::on($this->getConnectionName())->where('id_user', $id)->where(DB::raw('YEAR(tgl)'), $tahun)->where(DB::raw('MONTH(tgl)'), $bulan)->count();
        $total_omset = $this->get_omset($tahun, $bulan);
        return view('gaji._detail')->with(compact('pegawai', 'jumlah_jam', 'tahun', 'bulan', 'jumlah_hari', 'jumlah_jam_kerja_all', 'jumlah_hari_all', 'skema_gaji_aktif', 'total_omset'));
    }

    public function list_data(Request $request)
    {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::connection($this->getConnection())->statement(DB::raw('set @rownum = 0'));
        $data = Absensi::on($this->getConnectionName())->select([
                    DB::raw('@rownum  := @rownum  + 1 AS no'), 'tb_absensi.*'
                ])
                ->where(function($query) use($request){
                    $query->where('is_deleted','=','0');
                    $query->where('id_user',$request->id_pegawai);
                    if($request->id_searching_by == 2) {
                        $query->where('id_apotek','LIKE','%'.$request->id_apotek.'%');
                    }
                    $query->whereYear('tgl', $request->tahun);
                    $query->whereMonth('tgl', $request->bulan);
                })
                ->orderBy('tgl');
        
        $datatables = Datatables::of($data);
        return $datatables  
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                //$query->orwhere('nama_device','LIKE','%'.$request->get('search')['value'].'%');
               // $query->orwhere('mac_address','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->editcolumn('id_user', function($data){
            return $data->user->nama; 
        }) 
        ->editcolumn('jumlah_jam_kerja', function($data){
            return number_format($data->jumlah_jam_kerja,2); 
        }) 
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .= '<span class="btn btn-primary" onClick="edit_data('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span>';
            $btn .= '<span class="btn btn-danger" onClick="delete_data('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['action', 'id_user'])
        ->addIndexColumn()
        ->make(true);  
    }

    public function get_omset($tahun, $bulan) {
        $id_apotek = session('id_apotek_active');
        $penjualan = array();
       
        $rekaps = TransaksiPenjualanClosing::on($this->getConnectionName())->select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_closing_nota_penjualan.*'])
                            ->where(function($query) use($tahun, $bulan){
                                $query->where('tb_closing_nota_penjualan.id_apotek_nota','=',session('id_apotek_active'));
                                $query->whereYear('tb_closing_nota_penjualan.tanggal', $tahun);
                                $query->whereMonth('tb_closing_nota_penjualan.tanggal', $bulan);
                            })
                            ->orderBy('tb_closing_nota_penjualan.id', 'asc')
                            ->get();

        $rekap_alls = TransaksiPenjualanClosing::on($this->getConnectionName())->select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_closing_nota_penjualan.*'])
                            ->where(function($query) use($tahun, $bulan){
                                $query->whereYear('tb_closing_nota_penjualan.tanggal', $tahun);
                                $query->whereMonth('tb_closing_nota_penjualan.tanggal', $bulan);
                            })
                            ->orderBy('tb_closing_nota_penjualan.id', 'asc')
                            ->get();

       

        $hit_penjualan = TransaksiPenjualan::on($this->getConnectionName())->where('is_deleted', 0)
                            ->where('id_apotek_nota', session('id_apotek_active'))
                            ->whereYear('tgl_nota', $tahun)
                            ->whereMonth('tgl_nota', $bulan)
                            ->count();

        $hit_penjualan_all = TransaksiPenjualan::on($this->getConnectionName())->where('is_deleted', 0)
                            ->whereYear('tgl_nota', $tahun)
                                ->whereMonth('tgl_nota', $bulan)
                                ->count();


        $total_excel=0;
        foreach($rekaps as $rekap) {
            $total_1 = $rekap->jumlah_penjualan;
            if($total_1 == 0) {
                $total_1 = $rekap->total_penjualan+$rekap->total_diskon;
            }

            $total_3 = $total_1-$rekap->total_diskon;
            $grand_total = $total_3+$rekap->total_jasa_dokter+$rekap->total_jasa_resep+$rekap->total_paket_wd+$rekap->total_lab+$rekap->total_apd;

            $total_2 = $grand_total-$rekap->total_penjualan_cn;
           // $total_debet_x = $rekap->total_debet-$rekap->total_penjualan_cn_debet;
           // $total_cash_x = $rekap->uang_seharusnya-$rekap->total_penjualan_cn_cash;
            //$new_total = $rekap->total_akhir+$rekap->total_penjualan_kredit_terbayar;
            $new_total = $total_2+$rekap->total_penjualan_kredit;

            if($tahun == 2020) {
                $total_excel = $total_excel+$total_1;
            } else {
                $total_excel = $total_excel+$new_total;
            }
        }
        $total_excel = $total_excel; // total penjualan

        return $total_excel;
    }
}
