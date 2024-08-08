<?php



namespace App\Http\Controllers;



use Illuminate\Http\Request;

use App\Http\Requests;

use App\RbacUserRole;

use App\RbacRolePermission;

use App\RbacPermission;

use App\RbacMenu;

use App\MasterApotek;

use App\MasterTahun;

use App\TransaksiPenjualanClosing;

use App\TransaksiPembelian;

use App\TransaksiPembelianDetail;

use App\TransaksiTO;

use App\TransaksiTODetail;

use App\User;

use App\MasterVendor;

use App\MasterSuplier;

use App\MasterObat;

use App\MasterMember;

use App\InvestasiModal;

use App\MasterSatuan;

use App\MasterProdusen;

use App\HistoriStok;

use App\MasterStokHarga;

use App\MasterPengumuman;

use App\DefectaOutlet;

use App;

use Cache;

use Datatables;

use DB;

use Auth;

use Mail;

use File;

use Crypt;

use ZipArchive;



use App\TransaksiPenjualan;

use App\TransaksiPenjualanDetail;

use App\Exports\ParetoExport;

use App\Exports\PembelianExport;

use App\Exports\RekapPembelianOutletExport;

use Maatwebsite\Excel\Facades\Excel;



use App\Support\Collection;

use Illuminate\Pagination\Paginator;

use Carbon\CarbonImmutable;



class HomeController extends Controller

{

    protected static $expiredAt = 6 * 60 * 60;

    /**

     * Create a new controller instance.

     *

     * @return void

     */

    public function __construct()

    {

        $this->middleware('auth:web');

    }



    /**

     * Show the application dashboard.

     *

     * @return \Illuminate\Contracts\Support\Renderable

     */

    public function index()

    {
         return view('home2');

        $id_apotek = session('id_apotek_active');

        $id_role_active = session('id_role_active');

        $mytime = date('Y-m-d');

        if(!empty($id_apotek)) {

            $apotek = MasterApotek::find(session('id_apotek_active'));

            $apoteker = User::find($apotek->id_apoteker);

            $id_user = Auth::user()->id;



            $hak_akses = 0;

            if($apoteker->id == $id_user) {

                $hak_akses = 1;

            }



            if($id_role_active == 1 || $id_role_active == 4 || $id_role_active == 6 || $id_role_active == 11) {

                $hak_akses = 1;

            }



            $tanggal = date('Y-m-d');

           // $tanggal = '2022-10-25';

            $tgl_awal_baru = $tanggal.' 00:00:00';

            $tgl_akhir_baru = $tanggal.' 23:59:59';

            $detail_penjualan_kredit = array();

            $penjualan_kredit = array();

            $detail_penjualan = array();

            $penjualan2 = array();

            $detail_penjualan_kredit_terbayar = array();

            $penjualan_kredit_terbayar = array();

            $detail_tf_masuk = array();

            $detail_tf_keluar = array();

            $detail_pembelian = array();

            $detail_pembelian_terbayar = array();

            $detail_pembelian_blm_terbayar = array();

            $detail_pembelian_jatuh_tempo = array();

            $kasir = '';

            $closing_kasir = array();

            $kasir_aktif = '';

            $jumlah_kasir = 0;

            $jumlah_closing_kasir = 0;

            $detail_penjualan_cn = array();

            if($hak_akses == 1) {

                /*$jumlah_kasir = TransaksiPenjualan::whereDate('tb_nota_penjualan.tgl_nota','>=', $tgl_awal_baru)

                                ->whereDate('tb_nota_penjualan.tgl_nota','<=', $tgl_akhir_baru)

                                ->where('tb_nota_penjualan.id_apotek_nota','=',$apotek->id)

                                ->where('tb_nota_penjualan.is_deleted', 0)

                                ->groupBy('created_by')

                                ->get();*/



                $kasir = TransaksiPenjualan::select(['a.username', 'tb_nota_penjualan.created_by'])

                                ->join('users as a', 'a.id', '=', 'tb_nota_penjualan.created_by')

                                ->whereDate('tb_nota_penjualan.tgl_nota','>=', $tgl_awal_baru)

                                ->whereDate('tb_nota_penjualan.tgl_nota','<=', $tgl_akhir_baru)

                                ->where('tb_nota_penjualan.id_apotek_nota','=',$apotek->id)

                                ->where('tb_nota_penjualan.is_deleted', 0)

                                ->groupBy('tb_nota_penjualan.created_by')

                                ->get();



                $closing_kasir = TransaksiPenjualanClosing::whereDate('tb_closing_nota_penjualan.tanggal','>=', $tgl_awal_baru)

                                ->whereDate('tb_closing_nota_penjualan.tanggal','<=', $tgl_akhir_baru)

                                ->where('tb_closing_nota_penjualan.id_apotek_nota','=',$apotek->id)

                                ->groupBy('id_user')

                                ->get();



                $kasir_aktif = '';

                $i = 0;

                foreach ($kasir as $key => $val) {

                    $i++;

                    $cek = TransaksiPenjualanClosing::whereDate('tb_closing_nota_penjualan.tanggal','>=', $tgl_awal_baru)

                                ->whereDate('tb_closing_nota_penjualan.tanggal','<=', $tgl_akhir_baru)

                                ->where('tb_closing_nota_penjualan.id_apotek_nota','=',$apotek->id)

                                ->where('tb_closing_nota_penjualan.id_user', $val->created_by)

                                ->count();

                    $str_ = '';

                    if($cek > 0) {

                        $str_ = ' (CLOSED)';

                    }

                    if($i == 1) {

                        $kasir_aktif.= $val->username.$str_;

                    } else {

                        $kasir_aktif.= ' | '.$val->username.$str_;

                    }

                }



                $jumlah_kasir = $i;



                

                $jumlah_closing_kasir = count($closing_kasir);



                $detail_penjualan_kredit = DB::table('tb_detail_nota_penjualan')

                            ->select(

                                    DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) AS total_penjualan'),

                                    DB::raw('SUM(tb_detail_nota_penjualan.diskon) AS total_diskon'),

                                    DB::raw('SUM((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) - tb_detail_nota_penjualan.diskon) AS total'),

                                    DB::raw('SUM(b.diskon_persen/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen'))

                            ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')

                            ->whereDate('b.tgl_nota','>=', $tgl_awal_baru)

                            ->whereDate('b.tgl_nota','<=', $tgl_akhir_baru)

                            ->where('b.id_apotek_nota','=',$apotek->id)

                            ->where('b.is_deleted', 0)

                            ->where('b.is_kredit', 1)

                            ->where('tb_detail_nota_penjualan.is_cn', 0)

                            ->where('tb_detail_nota_penjualan.is_deleted', 0)

                            ->first();



                $penjualan_kredit =  DB::table('tb_nota_penjualan')

                            ->select(

                                    DB::raw('SUM(tb_nota_penjualan.biaya_jasa_dokter) AS total_jasa_dokter'),

                                    DB::raw('SUM(a.biaya) AS total_jasa_resep'),

                                    DB::raw('SUM(tb_nota_penjualan.diskon_rp) AS total_diskon_rp'),

                                    DB::raw('SUM(tb_nota_penjualan.debet) AS total_debet'))

                            ->join('tb_m_jasa_resep as a','a.id','=','tb_nota_penjualan.id_jasa_resep')

                            ->whereDate('tb_nota_penjualan.tgl_nota','>=', $tgl_awal_baru)

                            ->whereDate('tb_nota_penjualan.tgl_nota','<=', $tgl_akhir_baru)

                            ->where('tb_nota_penjualan.id_apotek_nota','=',$apotek->id)

                            ->where('tb_nota_penjualan.is_deleted', 0)

                            ->where('tb_nota_penjualan.is_kredit', 1)

                            ->first();



                $detail_penjualan = DB::table('tb_detail_nota_penjualan')

                            ->select(

                                    DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) AS total_penjualan'),

                                    DB::raw('SUM(tb_detail_nota_penjualan.diskon) AS total_diskon'),

                                    DB::raw('SUM((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) - tb_detail_nota_penjualan.diskon) AS total'),

                                    DB::raw('SUM(b.diskon_persen/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen'))

                            ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')

                            ->whereDate('b.created_at','>=', $tgl_awal_baru)

                            ->whereDate('b.created_at','<=', $tgl_akhir_baru)

                            ->where('b.id_apotek_nota','=',$apotek->id)

                            ->where('b.is_deleted', 0)

                            ->where('b.is_kredit', 0)

                            ->where('tb_detail_nota_penjualan.is_deleted', 0)

                            ->first();





                $detail_penjualan_cn = DB::table('tb_detail_nota_penjualan')

                        ->select(

                                DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah_cn) AS total_penjualan'),

                                DB::raw('SUM(tb_detail_nota_penjualan.diskon) AS total_diskon'),

                                DB::raw('SUM((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah_cn) - tb_detail_nota_penjualan.diskon) AS total'),

                                DB::raw('SUM(b.diskon_persen/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah_cn)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen'))

                        ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')

                        ->whereDate('tb_detail_nota_penjualan.cn_at','>=', $tgl_awal_baru)

                        ->whereDate('tb_detail_nota_penjualan.cn_at','<=', $tgl_akhir_baru)

                        ->where('b.id_apotek_nota','=',$apotek->id)

                        ->where('b.is_deleted', 0)

                        ->where('tb_detail_nota_penjualan.is_cn', 1)

                         ->where('tb_detail_nota_penjualan.is_approved', 1)

                        ->where('tb_detail_nota_penjualan.is_deleted', 0)

                        ->where('b.is_kredit', 0)

                        ->first();



                //dd($detail_penjualan);exit();



                $penjualan2 =  DB::table('tb_nota_penjualan')

                            ->select(

                                    DB::raw('SUM(tb_nota_penjualan.biaya_jasa_dokter) AS total_jasa_dokter'),

                                    DB::raw('SUM(a.biaya) AS total_jasa_resep'),

                                    DB::raw('SUM(tb_nota_penjualan.debet) AS total_debet'),

                                    DB::raw('SUM(tb_nota_penjualan.harga_wd) AS total_paket_wd'),

                                    DB::raw('SUM(tb_nota_penjualan.biaya_lab) AS total_lab'),

                                    DB::raw('SUM(tb_nota_penjualan.biaya_apd) AS total_apd'),

                                    DB::raw('SUM(tb_nota_penjualan.diskon_rp) AS total_diskon_rp'))

                            ->join('tb_m_jasa_resep as a','a.id','=','tb_nota_penjualan.id_jasa_resep')

                            ->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal_baru)

                            ->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir_baru)

                            ->where('tb_nota_penjualan.id_apotek_nota','=',$apotek->id)

                            ->where('tb_nota_penjualan.is_deleted', 0)

                            ->where('tb_nota_penjualan.is_kredit', 0)

                            ->first();



                $detail_penjualan_kredit_terbayar = DB::table('tb_detail_nota_penjualan')

                            ->select(

                                    DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) AS total_penjualan'),

                                    DB::raw('SUM(tb_detail_nota_penjualan.diskon) AS total_diskon'),

                                    DB::raw('SUM((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) - tb_detail_nota_penjualan.diskon) AS total'),

                                    DB::raw('SUM(b.diskon_persen/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen'),

                                    DB::raw('SUM(b.diskon_vendor/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_vendor')

                                )

                            ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')

                            ->whereDate('b.is_lunas_pembayaran_kredit_at','>=', $tgl_awal_baru)

                            ->whereDate('b.is_lunas_pembayaran_kredit_at','<=', $tgl_akhir_baru)

                            ->where('b.id_apotek_nota','=',$apotek->id)

                            ->where('b.is_deleted', 0)

                            ->where('b.is_kredit', 1)

                            ->where('b.is_lunas_pembayaran_kredit', 1)

                            ->where('tb_detail_nota_penjualan.is_cn', 0)

                            ->where('tb_detail_nota_penjualan.is_deleted', 0)

                            ->first();

            

                $penjualan_kredit_terbayar =  DB::table('tb_nota_penjualan')

                            ->select(

                                    DB::raw('SUM(tb_nota_penjualan.biaya_jasa_dokter) AS total_jasa_dokter'),

                                    DB::raw('SUM(a.biaya) AS total_jasa_resep'),

                                    DB::raw('SUM(tb_nota_penjualan.diskon_rp) AS total_diskon_rp'),

                                    DB::raw('SUM(tb_nota_penjualan.debet) AS total_debet'))

                            ->join('tb_m_jasa_resep as a','a.id','=','tb_nota_penjualan.id_jasa_resep')

                            ->whereDate('tb_nota_penjualan.is_lunas_pembayaran_kredit_at','>=', $tgl_awal_baru)

                            ->whereDate('tb_nota_penjualan.is_lunas_pembayaran_kredit_at','<=', $tgl_akhir_baru)

                            ->where('tb_nota_penjualan.id_apotek_nota','=',$apotek->id)

                            ->where('tb_nota_penjualan.is_deleted', 0)

                            ->where('tb_nota_penjualan.is_kredit', 1)

                            ->where('tb_nota_penjualan.is_lunas_pembayaran_kredit', 1)

                            //->groupBy('tb_nota_penjualan.id')

                            ->first();



                $detail_tf_masuk = DB::table('tb_detail_nota_transfer_outlet')

                            ->select(

                                    DB::raw('SUM(tb_detail_nota_transfer_outlet.harga_outlet * tb_detail_nota_transfer_outlet.jumlah) AS total'))

                            ->join('tb_nota_transfer_outlet as b','b.id','=','tb_detail_nota_transfer_outlet.id_nota')

                            ->whereDate('b.created_at','>=', $tgl_awal_baru)

                            ->whereDate('b.created_at','<=', $tgl_akhir_baru)

                            ->where('b.id_apotek_tujuan','=',$apotek->id)

                            ->where('b.is_deleted', 0)

                            ->where('tb_detail_nota_transfer_outlet.is_deleted', 0)

                            ->first();



                $detail_tf_keluar = DB::table('tb_detail_nota_transfer_outlet')

                            ->select(

                                    DB::raw('SUM(tb_detail_nota_transfer_outlet.harga_outlet * tb_detail_nota_transfer_outlet.jumlah) AS total'))

                            ->join('tb_nota_transfer_outlet as b','b.id','=','tb_detail_nota_transfer_outlet.id_nota')

                            ->whereDate('b.created_at','>=', $tgl_awal_baru)

                            ->whereDate('b.created_at','<=', $tgl_akhir_baru)

                            ->where('b.id_apotek_nota','=',$apotek->id)

                            ->where('b.is_deleted', 0)

                            ->where('tb_detail_nota_transfer_outlet.is_deleted', 0)

                            ->first();





                $detail_pembelian = DB::table('tb_detail_nota_pembelian')

                            ->select(

                                    DB::raw('SUM(tb_detail_nota_pembelian.diskon) AS total_diskon'),

                                    DB::raw('SUM(tb_detail_nota_pembelian.diskon_persen * tb_detail_nota_pembelian.total_harga/100) AS total_diskon_persen'),

                                    DB::raw('SUM(tb_detail_nota_pembelian.total_harga) AS jumlah'),

                                    DB::raw('SUM((b.ppn/100)*(tb_detail_nota_pembelian.total_harga-(b.diskon1+b.diskon2))) AS total'))

                            ->join('tb_nota_pembelian as b','b.id','=','tb_detail_nota_pembelian.id_nota')

                            ->whereDate('b.tgl_faktur','>=', $tgl_awal_baru)

                            ->whereDate('b.tgl_faktur','<=', $tgl_akhir_baru)

                            ->where('b.id_apotek_nota','=',$apotek->id)

                            ->where('b.is_deleted', 0)

                            ->where('tb_detail_nota_pembelian.is_deleted', 0)

                            ->first();

/*

                $detail_pembelian = DB::table('tb_detail_nota_pembelian')

                            ->select(

                                    DB::raw('SUM(tb_detail_nota_pembelian.diskon) AS total_diskon'),

                                    DB::raw('SUM(tb_detail_nota_pembelian.diskon_persen * tb_detail_nota_pembelian.total_harga/100) AS total_diskon_persen'),

                                    DB::raw('SUM(tb_detail_nota_pembelian.total_harga) AS jumlah'),

                                    DB::raw('SUM((b.ppn/100)*(tb_detail_nota_pembelian.total_harga-(b.diskon1+b.diskon2))) AS total'))

                            ->join('tb_nota_pembelian as b','b.id','=','tb_detail_nota_pembelian.id_nota')

                            ->whereDate('b.created_at','>=', $tgl_awal_baru)

                            ->whereDate('b.created_at','<=', $tgl_akhir_baru)

                            ->where('b.id_apotek_nota','=',$apotek->id)

                            ->where('b.is_deleted', 0)

                            ->first();*/



                $detail_pembelian_terbayar = DB::table('tb_detail_nota_pembelian')

                            ->select(

                                    DB::raw('SUM(tb_detail_nota_pembelian.diskon) AS total_diskon'),

                                    DB::raw('SUM(tb_detail_nota_pembelian.diskon_persen * tb_detail_nota_pembelian.total_harga/100) AS total_diskon_persen'),

                                    DB::raw('SUM(tb_detail_nota_pembelian.total_harga) AS jumlah'),

                                    DB::raw('SUM((b.ppn/100)*(tb_detail_nota_pembelian.total_harga-(b.diskon1+b.diskon2))) AS total'))

                            ->join('tb_nota_pembelian as b','b.id','=','tb_detail_nota_pembelian.id_nota')

                            ->whereDate('b.lunas_at','>=', $tgl_awal_baru)

                            ->whereDate('b.lunas_at','<=', $tgl_akhir_baru)

                            ->where('b.id_apotek_nota','=',$apotek->id)

                            ->where('b.is_deleted', 0)

                            ->where('b.is_lunas', 1)

                            ->where('tb_detail_nota_pembelian.is_deleted', 0)

                            ->first();



                $detail_pembelian_blm_terbayar = DB::table('tb_detail_nota_pembelian')

                            ->select(

                                    DB::raw('SUM(tb_detail_nota_pembelian.diskon) AS total_diskon'),

                                    DB::raw('SUM(tb_detail_nota_pembelian.diskon_persen * tb_detail_nota_pembelian.total_harga/100) AS total_diskon_persen'),

                                    DB::raw('SUM(tb_detail_nota_pembelian.total_harga) AS jumlah'),

                                    DB::raw('SUM((b.ppn/100)*(tb_detail_nota_pembelian.total_harga-(b.diskon1+b.diskon2))) AS total'))

                            ->join('tb_nota_pembelian as b','b.id','=','tb_detail_nota_pembelian.id_nota')

                            ->where('b.id_apotek_nota','=',$apotek->id)

                            ->where('b.is_deleted', 0)

                            ->where('b.is_lunas', 0)

                            ->where('tb_detail_nota_pembelian.is_deleted', 0)

                            ->first();



                $detail_pembelian_jatuh_tempo = DB::table('tb_detail_nota_pembelian')

                            ->select(

                                    DB::raw('SUM(tb_detail_nota_pembelian.diskon) AS total_diskon'),

                                    DB::raw('SUM(tb_detail_nota_pembelian.diskon_persen * tb_detail_nota_pembelian.total_harga/100) AS total_diskon_persen'),

                                    DB::raw('SUM(tb_detail_nota_pembelian.total_harga) AS jumlah'),

                                    DB::raw('SUM((b.ppn/100)*(tb_detail_nota_pembelian.total_harga-(b.diskon1+b.diskon2))) AS total'))

                            ->join('tb_nota_pembelian as b','b.id','=','tb_detail_nota_pembelian.id_nota')

                            ->whereDate('b.tgl_jatuh_tempo','>=', $tgl_awal_baru)

                            ->whereDate('b.tgl_jatuh_tempo','<=', $tgl_akhir_baru)

                            ->where('b.id_apotek_nota','=',$apotek->id)

                            ->where('b.is_deleted', 0)

                            ->where('b.is_lunas', 0)

                            ->where('tb_detail_nota_pembelian.is_deleted', 0)

                            ->first();

            }



            $hit_penjualan = TransaksiPenjualan::where('is_deleted', 0)

                                ->where('id_apotek_nota', $apotek->id)

                                ->whereDate('created_at','>=', $tgl_awal_baru)

                                ->whereDate('created_at','<=', $tgl_akhir_baru)

                                ->count();

            $hit_pembelian = TransaksiPembelian::where('is_deleted', 0)

                                ->where('id_apotek_nota', $apotek->id)

                                ->whereDate('tgl_faktur','>=', $tgl_awal_baru)

                                ->whereDate('tgl_faktur','<=', $tgl_akhir_baru)

                                ->count();

            $hit_obat = MasterObat::where('is_deleted', 0)

                                ->whereDate('created_at','>=', $tgl_awal_baru)

                                ->whereDate('created_at','<=', $tgl_akhir_baru)

                                ->count();



            $hit_member = MasterMember::where('is_deleted', 0)

                                ->where('id_group_apotek', $apotek->id_group_apotek)

                                ->whereDate('created_at','>=', $tgl_awal_baru)

                                ->whereDate('created_at','<=', $tgl_akhir_baru)

                                ->count();



            $staffs = User::select(['users.*'])

                            ->join('rbac_user_apotek as a', 'a.id_user', 'users.id')

                            ->where('users.is_deleted', 0)

                            ->where('a.id_apotek', $apotek->id)

                            ->groupBy('users.id')

                            ->get();



            $insvestors = InvestasiModal::select(['a.id', 'a.nama', DB::raw('SUM(tb_investasi_modal.persentase_kepemilikan) as saham_persen')])

                            ->join('tb_m_investor as a', 'a.id', 'tb_investasi_modal.id_investor')

                            ->where('tb_investasi_modal.is_deleted', 0)

                            ->where('tb_investasi_modal.id_apotek', $apotek->id)

                            ->groupBy('a.id')

                            ->get();



            return view('home')->with(compact('hak_akses', 'detail_penjualan_kredit', 'penjualan_kredit', 'detail_penjualan', 'penjualan2', 'detail_penjualan_kredit_terbayar', 'penjualan_kredit_terbayar', 'detail_tf_masuk', 'detail_tf_keluar', 'detail_pembelian', 'detail_pembelian_terbayar', 'detail_pembelian_blm_terbayar', 'detail_pembelian_jatuh_tempo', 'hit_penjualan', 'hit_pembelian', 'hit_obat', 'hit_member', 'apoteker', 'staffs', 'insvestors', 'detail_penjualan_cn', 'jumlah_kasir', 'jumlah_closing_kasir', 'kasir_aktif'));

        } else {

            return view('home2');

        }        

    }



    public function recap_all() {

        $tahun = date('Y');

        $bulan = date('m');



        return view('recap')->with(compact('tahun', 'bulan'));

    }



    public function recap_all_load_view(Request $request) {

        $id_apotek = session('id_apotek_active');

        $data = '';

        $data .= '<table class="table table-bordered table-striped table-hover">

                    <thead>

                        <tr>

                            <th width="10%" colspan="14" class="text-center text-white" style="background-color:#455a64;">PENJUALAN</th>

                        </tr>

                        <tr>

                            <th width="10%" rowspan="2" class="text-center">KERJASAMA</th>

                            <th width="20%" colspan="3" class="text-center text-white" style="background-color:#00bcd4;">KREDIT</th>

                            <th width="20%" colspan="4" class="text-center text-white" style="background-color:#00acc1;">NON KREDIT</th>

                            <th width="20%" colspan="6" class="text-center text-white" style="background-color:#0097a7;">RINCIAN NON KREDIT</th>

                        </tr>

                        <tr>

                            <th class="text-center text-white" style="background-color:#00bcd4;">Total Penjualan</th>

                            <th class="text-center text-white" style="background-color:#00bcd4;">Sudah Terbayar</th>

                            <th class="text-center text-white" style="background-color:#00bcd4;">Belum Terbayar</th>

                            <th class="text-center text-white" style="background-color:#00acc1;">Total Penjualan</th>

                            <th class="text-center text-white" style="background-color:#00acc1;">Cash</th>

                            <th class="text-center text-white" style="background-color:#00acc1;">Non Cash</th>

                            <th class="text-center text-white" style="background-color:#00acc1;">TT</th>

                            <th class="text-center text-white" style="background-color:#0097a7;">Penjualan</th>

                            <th class="text-center text-white" style="background-color:#0097a7;">Jasa Dokter</th>

                            <th class="text-center text-white" style="background-color:#0097a7;">Jasa Resep</th>

                            <th class="text-center text-white" style="background-color:#0097a7;">Paket WT</th>

                            <th class="text-center text-white" style="background-color:#0097a7;">Lab</th>

                            <th class="text-center text-white" style="background-color:#0097a7;">APD</th>

                        </tr>

                    </thead>

                    <tbody>';

        $penjualan = array();



        $detail_penjualan = DB::table('tb_detail_nota_penjualan')

                    ->select(

                            DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) AS total_penjualan'),

                            DB::raw('SUM(tb_detail_nota_penjualan.diskon) AS total_diskon'),

                            DB::raw('SUM((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) - tb_detail_nota_penjualan.diskon) AS total'),

                            DB::raw('SUM(b.diskon_persen/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen'))

                    ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')

                    ->whereMonth('b.tgl_nota','=',$request->bulan)

                    ->whereYear('b.tgl_nota','=', $request->tahun)

                    ->where('b.id_apotek_nota','=',$id_apotek)

                    ->where('b.is_deleted', 0)

                    ->where('b.is_kredit', 0)

                    ->where('tb_detail_nota_penjualan.is_deleted', 0)

                    ->first();



        $penjualan2 =  DB::table('tb_nota_penjualan')

                    ->select(

                            DB::raw('SUM(tb_nota_penjualan.biaya_jasa_dokter) AS total_jasa_dokter'),

                            DB::raw('SUM(a.biaya) AS total_jasa_resep'),

                            DB::raw('SUM(tb_nota_penjualan.debet) AS total_debet'),

                            DB::raw('SUM(tb_nota_penjualan.harga_wd) AS total_paket_wd'),

                            DB::raw('SUM(tb_nota_penjualan.biaya_lab) AS total_lab'),

                            DB::raw('SUM(tb_nota_penjualan.biaya_apd) AS total_apd'),

                            DB::raw('SUM(tb_nota_penjualan.diskon_rp) AS total_diskon_rp'))

                    ->join('tb_m_jasa_resep as a','a.id','=','tb_nota_penjualan.id_jasa_resep')

                    ->whereMonth('tgl_nota','=', $request->bulan)

                    ->whereYear('tgl_nota','=', $request->tahun)

                    ->where('id_apotek_nota','=',$id_apotek)

                    ->where('tb_nota_penjualan.is_deleted', 0)

                    ->where('tb_nota_penjualan.is_kredit', 0)

                    ->first();



        $penjualan_closing = TransaksiPenjualanClosing::select([



                                    DB::raw('SUM(total_jasa_dokter) as total_jasa_dokter_a'),

                                    DB::raw('SUM(total_jasa_resep) as total_jasa_resep_a'),

                                    DB::raw('SUM(total_paket_wd) as total_paket_wd_a'),

                                    DB::raw('SUM(total_penjualan) as total_penjualan_a'),

                                    DB::raw('SUM(total_debet) as total_debet_a'),

                                    DB::raw('SUM(total_penjualan_cash) as total_penjualan_cash_a'),

                                    DB::raw('SUM(total_penjualan_cn) as total_penjualan_cn_a'),

                                    DB::raw('SUM(total_penjualan_kredit) as total_penjualan_kredit_a'),

                                    DB::raw('SUM(total_penjualan_kredit_terbayar) as total_penjualan_kredit_terbayar_a'),

                                    DB::raw('SUM(total_diskon) as total_diskon_a'),

                                    DB::raw('SUM(uang_seharusnya) as uang_seharusnya_a'),

                                    DB::raw('SUM(total_akhir) as total_akhir_a'),

                                    DB::raw('SUM(jumlah_tt) as jumlah_tt_a')

                                ])

                                ->whereMonth('tanggal','=', $request->bulan)

                                ->whereYear('tanggal','=', $request->tahun)

                                ->where('id_apotek_nota','=',$id_apotek)

                                ->first();



        $detail_penjualan_cn = DB::table('tb_detail_nota_penjualan')

                        ->select(

                                DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah_cn) AS total_penjualan'),

                                DB::raw('SUM(tb_detail_nota_penjualan.diskon) AS total_diskon'),

                                DB::raw('SUM((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah_cn) - tb_detail_nota_penjualan.diskon) AS total'),

                                DB::raw('SUM(b.diskon_persen/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah_cn)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen'))

                        ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')

                        ->whereMonth('tb_detail_nota_penjualan.cn_at','=', $request->bulan)

                        ->whereYear('tb_detail_nota_penjualan.cn_at','=', $request->tahun)

                        ->where('b.id_apotek_nota','=',$id_apotek)

                        ->where('b.is_deleted', 0)

                        ->where('tb_detail_nota_penjualan.is_cn', 1)

                        ->where('tb_detail_nota_penjualan.is_approved', 1)

                        ->where('tb_detail_nota_penjualan.is_deleted', 0)

                        ->where('b.is_kredit', 0)

                        ->first();



        $penjualan_cn_cash = DB::table('tb_detail_nota_penjualan')

                        ->select(

                                DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah_cn) AS total_penjualan')

                            )

                        ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')

                        ->whereMonth('tb_detail_nota_penjualan.cn_at','=', $request->bulan)

                        ->whereYear('tb_detail_nota_penjualan.cn_at','=', $request->tahun)

                        ->where('b.id_apotek_nota','=',$id_apotek)

                        ->where('b.is_deleted', 0)

                        ->where('b.debet', 0)

                        ->where('tb_detail_nota_penjualan.is_cn', 1)

                        ->where('tb_detail_nota_penjualan.is_approved', 1)

                        ->where('tb_detail_nota_penjualan.is_deleted', 0)

                        ->where('b.is_kredit', 0)

                        ->first();



        $new_total_total_kredit = 0;

        $new_total_total_kredit_terbayar = 0;

        $new_total_total_kredit_blm_terbayar = 0;

        $new_total_total_non_kredit = 0;

        $new_total_total_non_kredit_cash = 0;

        $new_total_total_non_kredit_non_cash = 0;

        $new_total_total_non_kredit_tt = 0;

        $new_total_total_penjualan = 0;

        $new_total_total_jasa_dokter = 0;

        $new_total_total_jasa_resep = 0;

        $new_total_total_paket_wd = 0;

        $new_total_total_lab = 0;

        $new_total_total_apd = 0;



        $total_diskon = $detail_penjualan->total_diskon_persen + $penjualan2->total_diskon_rp;

        $total_3 = $detail_penjualan->total-$total_diskon;

        $grand_total = $total_3+$penjualan2->total_jasa_dokter+$penjualan2->total_jasa_resep+$penjualan2->total_paket_wd+$penjualan2->total_lab+$penjualan2->total_apd;

        $total_cash = $grand_total - $penjualan2->total_debet;

        $total_penjualan_cn_cash = 0;

        if(!empty($penjualan_cn_cash->total_penjualan)) {

            $total_penjualan_cn_cash = $penjualan_cn_cash->total_penjualan - $detail_penjualan_cn->total_diskon_persen;

        }

        $total_penjualan_cn_debet = 0;

        if(!empty($penjualan_cn_debet->total_debet)) {

            $total_penjualan_cn_debet = $detail_penjualan_cn->total-$total_penjualan_cn_cash;

        }

        $total_cn = 0 + $detail_penjualan_cn->total - $detail_penjualan_cn->total_diskon_persen;

        $total_2 = $grand_total-$total_cn;

        $total_cash_x = $total_cash-$total_penjualan_cn_cash;

        $total_debet_x = $penjualan2->total_debet-$total_penjualan_cn_debet;

        $total_penjualan = $total_2-($penjualan2->total_jasa_dokter+$penjualan2->total_jasa_resep+$penjualan2->total_paket_wd+$penjualan2->total_lab+$penjualan2->total_apd);

        $total_3_format = number_format($total_2,0,',',',');

        $g_format = number_format($total_debet_x,0,',',',');

        $h_format = number_format($total_cash_x,0,',',',');

        $a_format = number_format($penjualan2->total_jasa_dokter,0,',',',');

        $b_format = number_format($penjualan2->total_jasa_resep,0,',',',');

        $c_format = number_format($penjualan2->total_paket_wd,0,',',',');

        $d_format = number_format($penjualan2->total_lab,0,',',',');

        $e_format = number_format($penjualan2->total_apd,0,',',',');

        $f_format = number_format($penjualan_closing->jumlah_tt_a,0,',',',');

        $total_penjualan_format = number_format($total_penjualan,0,',',',');

        $new_data = array();

        $new_data['kerjasama'] = 'Umum';

        $new_data['total_kredit'] = '-';

        $new_data['total_kredit_terbayar'] = '-';

        $new_data['total_kredit_blm_terbayar'] = '-';

        $new_data['total_non_kredit'] = 'Rp '.$total_3_format;

        $new_data['total_non_kredit_cash'] = 'Rp '.$h_format;

        $new_data['total_non_kredit_non_cash'] = 'Rp '.$g_format;

        $new_data['total_non_kredit_tt'] = 'Rp '.$f_format;

        $new_data['total_penjualan'] = 'Rp '.$total_penjualan_format;

        $new_data['total_jasa_dokter'] = 'Rp '.$a_format;

        $new_data['total_jasa_resep'] = 'Rp '.$b_format;

        $new_data['total_paket_wd'] = 'Rp '.$c_format;

        $new_data['total_lab'] = 'Rp '.$d_format;

        $new_data['total_apd'] = 'Rp '.$e_format;

        $penjualan[] = $new_data;



        # update 

        $new_total_total_non_kredit = $new_total_total_non_kredit + $total_2;

        $new_total_total_non_kredit_cash = $new_total_total_non_kredit_cash + $total_cash_x;

        $new_total_total_non_kredit_non_cash = $new_total_total_non_kredit_non_cash + $total_debet_x;

        $new_total_total_non_kredit_tt = $new_total_total_non_kredit_tt + $penjualan_closing->jumlah_tt_a;

        $new_total_total_penjualan = $new_total_total_penjualan + $total_penjualan;

        $new_total_total_jasa_dokter = $new_total_total_jasa_dokter + $penjualan2->total_jasa_dokter;

        $new_total_total_jasa_resep = $new_total_total_jasa_resep + $penjualan2->total_jasa_resep;

        $new_total_total_paket_wd = $new_total_total_paket_wd + $penjualan2->total_paket_wd;

        $new_total_total_lab = $new_total_total_lab + $penjualan2->total_lab;

        $new_total_total_apd = $new_total_total_apd + $penjualan2->total_apd;



        $vendors = MasterVendor::where('is_deleted', 0)->get();

        foreach ($vendors as $key => $val) {

            $detail_penjualan_kredit = DB::table('tb_detail_nota_penjualan')

                        ->select(

                                DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) AS total_penjualan'),

                                DB::raw('SUM(tb_detail_nota_penjualan.diskon) AS total_diskon'),

                                DB::raw('SUM((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) - tb_detail_nota_penjualan.diskon) AS total'),

                                DB::raw('SUM(b.diskon_persen/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen'))

                        ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')

                        ->whereMonth('b.tgl_nota','=', $request->bulan)

                        ->whereYear('b.tgl_nota','=', $request->tahun)

                        ->where('b.id_apotek_nota','=', $id_apotek)

                        ->where('b.id_vendor','=', $val->id)

                        ->where('b.is_deleted', 0)

                        ->where('b.is_kredit', 1)

                        ->where('tb_detail_nota_penjualan.is_cn', 0)

                        ->where('tb_detail_nota_penjualan.is_deleted', 0)

                        ->first();



            $penjualan_kredit =  DB::table('tb_nota_penjualan')

                        ->select(

                                DB::raw('SUM(tb_nota_penjualan.biaya_jasa_dokter) AS total_jasa_dokter'),

                                DB::raw('SUM(a.biaya) AS total_jasa_resep'),

                                DB::raw('SUM(tb_nota_penjualan.debet) AS total_debet'),

                                DB::raw('SUM(tb_nota_penjualan.harga_wd) AS total_paket_wd'),

                                DB::raw('SUM(tb_nota_penjualan.biaya_lab) AS total_lab'),

                                DB::raw('SUM(tb_nota_penjualan.biaya_apd) AS total_apd'),

                                DB::raw('SUM(tb_nota_penjualan.diskon_rp) AS total_diskon_rp'))

                        ->join('tb_m_jasa_resep as a','a.id','=','tb_nota_penjualan.id_jasa_resep')

                        ->whereMonth('tgl_nota','=', $request->bulan)

                        ->whereYear('tgl_nota','=', $request->tahun)

                        ->where('id_apotek_nota','=', $id_apotek)

                        ->where('id_vendor','=', $val->id)

                        ->where('tb_nota_penjualan.is_deleted', 0)

                        ->where('tb_nota_penjualan.is_kredit', 1)

                        ->first();



            $total_cash_kredit = $detail_penjualan_kredit->total - $penjualan_kredit->total_debet;

            $total_cash_kredit_format = number_format($total_cash_kredit,0,',',',');





            $detail_penjualan_kredit_terbayar = DB::table('tb_detail_nota_penjualan')

                        ->select(

                                DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) AS total_penjualan'),

                                DB::raw('SUM(tb_detail_nota_penjualan.diskon) AS total_diskon'),

                                DB::raw('SUM((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) - tb_detail_nota_penjualan.diskon) AS total'),

                                DB::raw('SUM(b.diskon_persen/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen'),

                                DB::raw('SUM(b.diskon_vendor/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_vendor')

                            )

                        ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')

                        ->whereMonth('b.is_lunas_pembayaran_kredit_at','=', $request->bulan)

                        ->whereYear('b.is_lunas_pembayaran_kredit_at','=', $request->tahun)

                        ->where('b.id_apotek_nota','=',$id_apotek)

                        ->where('b.id_vendor','=', $val->id)

                        ->where('b.is_deleted', 0)

                        ->where('b.is_kredit', 1)

                        ->where('b.is_lunas_pembayaran_kredit', 1)

                        ->where('tb_detail_nota_penjualan.is_cn', 0)

                        ->where('tb_detail_nota_penjualan.is_deleted', 0)

                        ->first();

        

            $penjualan_kredit_terbayar =  DB::table('tb_nota_penjualan')

                        ->select(

                                DB::raw('SUM(tb_nota_penjualan.biaya_jasa_dokter) AS total_jasa_dokter'),

                                DB::raw('SUM(a.biaya) AS total_jasa_resep'),

                                DB::raw('SUM(tb_nota_penjualan.diskon_rp) AS total_diskon_rp'),

                                DB::raw('SUM(tb_nota_penjualan.debet) AS total_debet'))

                        ->join('tb_m_jasa_resep as a','a.id','=','tb_nota_penjualan.id_jasa_resep')

                        ->whereMonth('tb_nota_penjualan.is_lunas_pembayaran_kredit_at','=',  $request->bulan)

                        ->whereYear('tb_nota_penjualan.is_lunas_pembayaran_kredit_at','=',  $request->tahun)

                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)

                        ->where('tb_nota_penjualan.id_vendor','=', $val->id)

                        ->where('tb_nota_penjualan.is_deleted', 0)

                        ->where('tb_nota_penjualan.is_kredit', 1)

                        ->where('tb_nota_penjualan.is_lunas_pembayaran_kredit', 1)

                        ->first();





            $total_cash_kredit_terbayar = ($detail_penjualan_kredit_terbayar->total + $penjualan_kredit_terbayar->total_jasa_dokter + $penjualan_kredit_terbayar->total_jasa_resep) - $penjualan_kredit_terbayar->total_debet-$detail_penjualan_kredit_terbayar->total_diskon_vendor;

            $total_penjualan_kredit_terbayar = $penjualan_kredit_terbayar->total_debet+$total_cash_kredit_terbayar;

            $total_penjualan_kredit_terbayar_format = number_format($total_penjualan_kredit_terbayar,0,',',',');

            $total_penjualan_kredit_blm_terbayar = $total_cash_kredit - $total_penjualan_kredit_terbayar;

            $total_penjualan_kredit_blm_terbayar_format = number_format($total_penjualan_kredit_blm_terbayar,0,',',',');



            $total_penjualan = $detail_penjualan_kredit->total-($penjualan_kredit->total_jasa_dokter+$penjualan_kredit->total_jasa_resep+$penjualan_kredit->total_paket_wd+$penjualan_kredit->total_lab+$penjualan_kredit->total_apd);

         

            $a_format = number_format($penjualan_kredit->total_jasa_dokter,0,',',',');

            $b_format = number_format($penjualan_kredit->total_jasa_resep,0,',',',');

            $c_format = number_format($penjualan_kredit->total_paket_wd,0,',',',');

            $d_format = number_format($penjualan_kredit->total_lab,0,',',',');

            $e_format = number_format($penjualan_kredit->total_apd,0,',',',');

            $total_penjualan_format = number_format($total_penjualan,0,',',',');



            $new_data = array();

            $new_data['kerjasama'] = $val->nama;

            $new_data['total_kredit'] = 'Rp '.$total_cash_kredit_format;

            $new_data['total_kredit_terbayar'] = 'Rp '.$total_penjualan_kredit_terbayar_format;

            $new_data['total_kredit_blm_terbayar'] = 'Rp '.$total_penjualan_kredit_blm_terbayar_format;

            $new_data['total_non_kredit'] = '-';

            $new_data['total_non_kredit_cash'] = '-';

            $new_data['total_non_kredit_non_cash'] = '-';

            $new_data['total_non_kredit_tt'] = '-';

            $new_data['total_penjualan'] = 'Rp '.$total_penjualan_format;

            $new_data['total_jasa_dokter'] = 'Rp '.$a_format;

            $new_data['total_jasa_resep'] = 'Rp '.$b_format;

            $new_data['total_paket_wd'] = 'Rp '.$c_format;

            $new_data['total_lab'] = 'Rp '.$d_format;

            $new_data['total_apd'] = 'Rp '.$e_format;

            $penjualan[] = $new_data;



            # update 

            $new_total_total_kredit = $new_total_total_kredit + $total_cash_kredit;

            $new_total_total_kredit_terbayar = $new_total_total_kredit_terbayar + $total_penjualan_kredit_terbayar;

            $new_total_total_kredit_blm_terbayar = $new_total_total_kredit_blm_terbayar + $total_penjualan_kredit_blm_terbayar;

            $new_total_total_penjualan = $new_total_total_penjualan + $total_penjualan;

            $new_total_total_jasa_dokter = $new_total_total_jasa_dokter + $penjualan_kredit->total_jasa_dokter;

            $new_total_total_jasa_resep = $new_total_total_jasa_resep + $penjualan_kredit->total_jasa_resep;

            $new_total_total_paket_wd = $new_total_total_paket_wd + $penjualan_kredit->total_paket_wd;

            $new_total_total_lab = $new_total_total_lab + $penjualan_kredit->total_lab;

            $new_total_total_apd = $new_total_total_apd + $penjualan_kredit->total_apd;

        }



        foreach ($penjualan as $key => $obj) {

            $data.= '<tr>

                            <td class="text-left">'.$obj['kerjasama'].'</td>

                            <td class="text-right">'.$obj['total_kredit'].'</td>

                            <td class="text-right">'.$obj['total_kredit_terbayar'].'</td>

                            <td class="text-right">'.$obj['total_kredit_blm_terbayar'].'</td>

                            <td class="text-right">'.$obj['total_non_kredit'].'</td>

                            <td class="text-right">'.$obj['total_non_kredit_cash'].'</td>

                            <td class="text-right">'.$obj['total_non_kredit_non_cash'].'</td>

                            <td class="text-right">'.$obj['total_non_kredit_tt'].'</td>

                            <td class="text-right">'.$obj['total_penjualan'].'</td>

                            <td class="text-right">'.$obj['total_jasa_dokter'].'</td>

                            <td class="text-right">'.$obj['total_jasa_resep'].'</td>

                            <td class="text-right">'.$obj['total_paket_wd'].'</td>

                            <td class="text-right">'.$obj['total_lab'].'</td>

                            <td class="text-right">'.$obj['total_apd'].'</td>

                        </tr>';

        }



        if(count($penjualan) == 0) {

            $data.= '<tr>

                            <td class="text-center" colspan="14">TIDAK ADA PENJUALAN</td>

                        </tr>';

        }



        $new_total_total_kredit_format = number_format($new_total_total_kredit,0,',',',');

        $new_total_total_kredit_terbayar_format = number_format($new_total_total_kredit_terbayar,0,',',',');

        $new_total_total_kredit_blm_terbayar_format = number_format($new_total_total_kredit_blm_terbayar,0,',',',');

        $new_total_total_non_kredit_format = number_format($new_total_total_non_kredit,0,',',',');

        $new_total_total_non_kredit_cash_format = number_format($new_total_total_non_kredit_cash,0,',',',');

        $new_total_total_non_kredit_non_cash_format = number_format($new_total_total_non_kredit_non_cash,0,',',',');

        $new_total_total_non_kredit_tt_format = number_format($new_total_total_non_kredit_tt,0,',',',');

        $new_total_total_penjualan_format = number_format($new_total_total_penjualan,0,',',',');

        $new_total_total_jasa_dokter_format = number_format($new_total_total_jasa_dokter,0,',',',');

        $new_total_total_jasa_resep_format = number_format($new_total_total_jasa_resep,0,',',',');

        $new_total_total_paket_wd_format = number_format($new_total_total_paket_wd,0,',',',');

        $new_total_total_lab_format = number_format($new_total_total_lab,0,',',',');

        $new_total_total_apd_format = number_format($new_total_total_apd,0,',',',');



        $grand_total = $new_total_total_kredit+$new_total_total_non_kredit;

        $grand_total_format = number_format($grand_total,0,',',',');



        $data .= '<tr>

                    <td class="text-left"><b>TOTAL</b></td>

                    <td class="text-right text-white" style="background-color:#00bcd4;">Rp '.$new_total_total_kredit_format.'</td>

                    <td class="text-right text-white" style="background-color:#00bcd4;">Rp '.$new_total_total_kredit_terbayar_format.'</td>

                    <td class="text-right text-white" style="background-color:#00bcd4;">Rp '.$new_total_total_kredit_blm_terbayar_format.'</td>

                    <td class="text-right text-white" style="background-color:#00acc1;">Rp '.$new_total_total_non_kredit_format.'</td>

                    <td class="text-right text-white" style="background-color:#00acc1;">Rp '.$new_total_total_non_kredit_cash_format.'</td>

                    <td class="text-right text-white" style="background-color:#00acc1;">Rp '.$new_total_total_non_kredit_non_cash_format.'</td>

                    <td class="text-right text-white" style="background-color:#00acc1;">Rp '.$new_total_total_non_kredit_tt_format.'</td>

                    <td class="text-right text-white" style="background-color:#0097a7;">Rp '.$new_total_total_penjualan_format.'</td>

                    <td class="text-right text-white" style="background-color:#0097a7;">Rp '.$new_total_total_jasa_dokter_format.'</td>

                    <td class="text-right text-white" style="background-color:#0097a7;">Rp '.$new_total_total_jasa_resep_format.'</td>

                    <td class="text-right text-white" style="background-color:#0097a7;">Rp '.$new_total_total_paket_wd_format.'</td>

                    <td class="text-right text-white" style="background-color:#0097a7;">Rp '.$new_total_total_lab_format.'</td>

                    <td class="text-right text-white" style="background-color:#0097a7;">Rp '.$new_total_total_apd_format.'</td>

                </tr>';



        $data .= '<tr>

                    <td class="text-left" colspan="8"><b>GRAND TOTAL</b></td>

                    <td class="text-right text-white" style="background-color:#0097a7;" colspan="6">Rp '.$grand_total_format.'</td>

                </tr>';



        $data .= '</tbody></table>';

        echo $data;

    }



    public function recap_all_pembelian_load_view(Request $request) {

        $id_apotek = session('id_apotek_active');

        $data = '';

        $data .= '<table class="table table-bordered table-striped table-hover">

                    <thead>

                        <tr>

                            <th width="10%" colspan="14" class="text-center text-white" style="background-color:#455a64;">PEMBELIAN</th>

                        </tr>

                        <tr>

                            <th width="10%" rowspan="3" class="text-center">SUPLIER</th>

                            <th width="20%" rowspan="3" class="text-center text-white" style="background-color:#9575cd;">TOTAL PEMBELIAN</th>

                            <th width="20%" colspan="5" class="text-center text-white" style="background-color:#7e57c2;">RINCIAN</th>

                            <th width="20%" rowspan="2" colspan="2" class="text-center text-white" style="background-color:#673ab7;">JATUH TEMPO</th>

                        </tr>

                        <tr>

                            <th class="text-center text-white" style="background-color:#7e57c2;" rowspan="2">Cash</th>

                            <th class="text-center text-white" style="background-color:#7e57c2;" colspan="2">Credit</th>

                            <th class="text-center text-white" style="background-color:#7e57c2;" colspan="2">Konsinyasi</th>

                        </tr>

                        <tr>

                            <th class="text-center text-white" style="background-color:#7e57c2;">Sudah Terbayar</th>

                            <th class="text-center text-white" style="background-color:#7e57c2;">Belum terbayar</th>

                            <th class="text-center text-white" style="background-color:#7e57c2;">Sudah Terbayar</th>

                            <th class="text-center text-white" style="background-color:#7e57c2;">Belum terbayar</th>

                            <th class="text-center text-white" style="background-color:#673ab7;">Sudah Terbayar</th>

                            <th class="text-center text-white" style="background-color:#673ab7;">Belum terbayar</th>

                        </tr>

                    </thead>

                    <tbody>';

        $pembelian = array();



        $new_total_pembelian = 0;

        $new_total_pembelian_cash = 0;

        $new_total_pembelian_credit_terbayar = 0;

        $new_total_pembelian_credit_blm_terbayar = 0;

        $new_total_pembelian_konsinyasi_terbayar = 0;

        $new_total_pembelian_konsinyasi_blm_terbayar = 0;

        $new_total_pembelian_jatuhtempo_terbayar = 0;

        $new_total_pembelian_jetuhtempo_blm_terbayar = 0;



        $supliers = MasterSuplier::where('is_deleted', 0)->get();

        foreach ($supliers as $key => $val) {

            $detail_pembelian = DB::table('tb_detail_nota_pembelian')

                        ->select(

                                DB::raw('SUM(tb_detail_nota_pembelian.diskon) AS total_diskon'),

                                DB::raw('SUM((tb_detail_nota_pembelian.diskon_persen/100) * tb_detail_nota_pembelian.total_harga) AS total_diskon_persen'),

                                DB::raw('SUM(tb_detail_nota_pembelian.total_harga) AS total'),

                                'b.diskon1',

                                'b.diskon2',

                                'b.ppn')

                        ->join('tb_nota_pembelian as b','b.id','=','tb_detail_nota_pembelian.id_nota')

                        ->whereMonth('b.tgl_faktur','=',$request->bulan)

                        ->whereYear('b.tgl_faktur','=', $request->tahun)

                        ->where('b.id_apotek_nota','=',$id_apotek)

                        ->where('b.id_suplier','=',$val->id)

                        ->where('b.is_deleted', 0)

                        ->first();



            if($detail_pembelian->total != 0) {

                $detail_pembelian_cash = DB::table('tb_detail_nota_pembelian')

                            ->select(

                                    DB::raw('SUM(tb_detail_nota_pembelian.diskon) AS total_diskon'),

                                    DB::raw('SUM((tb_detail_nota_pembelian.diskon_persen/100) * tb_detail_nota_pembelian.total_harga) AS total_diskon_persen'),

                                    DB::raw('SUM(tb_detail_nota_pembelian.total_harga) AS total'),

                                    'b.diskon1',

                                    'b.diskon2',

                                    'b.ppn')

                            ->join('tb_nota_pembelian as b','b.id','=','tb_detail_nota_pembelian.id_nota')

                            ->whereMonth('b.tgl_faktur','=',$request->bulan)

                            ->whereYear('b.tgl_faktur','=', $request->tahun)

                            ->where('b.id_apotek_nota','=',$id_apotek)

                            ->where('b.id_suplier','=',$val->id)

                            ->where('b.id_jenis_pembelian','=',1)

                            ->where('b.is_deleted', 0)

                            ->where('tb_detail_nota_pembelian.is_deleted', 0)

                            ->first();



                $detail_pembelian_credit = DB::table('tb_detail_nota_pembelian')

                            ->select(

                                    DB::raw('SUM(tb_detail_nota_pembelian.diskon) AS total_diskon'),

                                    DB::raw('SUM((tb_detail_nota_pembelian.diskon_persen/100) * tb_detail_nota_pembelian.total_harga) AS total_diskon_persen'),

                                    DB::raw('SUM(tb_detail_nota_pembelian.total_harga) AS total'),

                                    'b.diskon1',

                                    'b.diskon2',

                                    'b.ppn')

                            ->join('tb_nota_pembelian as b','b.id','=','tb_detail_nota_pembelian.id_nota')

                            ->whereMonth('b.tgl_faktur','=',$request->bulan)

                            ->whereYear('b.tgl_faktur','=', $request->tahun)

                            ->where('b.id_apotek_nota','=',$id_apotek)

                            ->where('b.id_suplier','=',$val->id)

                            ->where('b.id_jenis_pembelian','=',2)

                            ->where('b.is_deleted', 0)

                            ->where('tb_detail_nota_pembelian.is_deleted', 0)

                            ->first();



                $detail_pembelian_konsinyasi = DB::table('tb_detail_nota_pembelian')

                            ->select(

                                    DB::raw('SUM(tb_detail_nota_pembelian.diskon) AS total_diskon'),

                                    DB::raw('SUM((tb_detail_nota_pembelian.diskon_persen/100) * tb_detail_nota_pembelian.total_harga) AS total_diskon_persen'),

                                    DB::raw('SUM(tb_detail_nota_pembelian.total_harga) AS total'),

                                    'b.diskon1',

                                    'b.diskon2',

                                    'b.ppn')

                            ->join('tb_nota_pembelian as b','b.id','=','tb_detail_nota_pembelian.id_nota')

                            ->whereMonth('b.tgl_faktur','=',$request->bulan)

                            ->whereYear('b.tgl_faktur','=', $request->tahun)

                            ->where('b.id_apotek_nota','=',$id_apotek)

                            ->where('b.id_suplier','=',$val->id)

                            ->where('b.id_jenis_pembelian','=',3)

                            ->where('b.is_deleted', 0)

                            ->where('tb_detail_nota_pembelian.is_deleted', 0)

                            ->first();



                $detail_pembelian_credit_terbayar = DB::table('tb_detail_nota_pembelian')

                            ->select(

                                    DB::raw('SUM(tb_detail_nota_pembelian.diskon) AS total_diskon'),

                                    DB::raw('SUM((tb_detail_nota_pembelian.diskon_persen/100) * tb_detail_nota_pembelian.total_harga) AS total_diskon_persen'),

                                    DB::raw('SUM(tb_detail_nota_pembelian.total_harga) AS total'),

                                    'b.diskon1',

                                    'b.diskon2',

                                    'b.ppn')

                            ->join('tb_nota_pembelian as b','b.id','=','tb_detail_nota_pembelian.id_nota')

                            ->whereMonth('b.tgl_faktur','=',$request->bulan)

                            ->whereYear('b.tgl_faktur','=', $request->tahun)

                            ->where('b.id_apotek_nota','=',$id_apotek)

                            ->where('b.id_suplier','=',$val->id)

                            ->where('b.id_jenis_pembelian','=',2)

                            ->where('b.is_deleted', 0)

                            ->where('b.is_lunas', 1)

                            ->where('tb_detail_nota_pembelian.is_deleted', 0)

                            ->first();





                $detail_pembelian_konsinyasi_terbayar = DB::table('tb_detail_nota_pembelian')

                            ->select(

                                    DB::raw('SUM(tb_detail_nota_pembelian.diskon) AS total_diskon'),

                                    DB::raw('SUM((tb_detail_nota_pembelian.diskon_persen/100) * tb_detail_nota_pembelian.total_harga) AS total_diskon_persen'),

                                    DB::raw('SUM(tb_detail_nota_pembelian.total_harga) AS total'),

                                    'b.diskon1',

                                    'b.diskon2',

                                    'b.ppn')

                            ->join('tb_nota_pembelian as b','b.id','=','tb_detail_nota_pembelian.id_nota')

                            ->whereMonth('b.tgl_faktur','=',$request->bulan)

                            ->whereYear('b.tgl_faktur','=', $request->tahun)

                            ->where('b.id_apotek_nota','=',$id_apotek)

                            ->where('b.id_suplier','=',$val->id)

                            ->where('b.id_jenis_pembelian','=',3)

                            ->where('b.is_deleted', 0)

                            ->where('b.is_lunas', 1)

                            ->where('tb_detail_nota_pembelian.is_deleted', 0)

                            ->first();



                $detail_pembelian_jatuh_tempo_blm_bayar = DB::table('tb_detail_nota_pembelian')

                            ->select(

                                    DB::raw('SUM(tb_detail_nota_pembelian.diskon) AS total_diskon'),

                                    DB::raw('SUM((tb_detail_nota_pembelian.diskon_persen/100) * tb_detail_nota_pembelian.total_harga) AS total_diskon_persen'),

                                    DB::raw('SUM(tb_detail_nota_pembelian.total_harga) AS total'),

                                    'b.diskon1',

                                    'b.diskon2',

                                    'b.ppn')

                            ->join('tb_nota_pembelian as b','b.id','=','tb_detail_nota_pembelian.id_nota')

                            ->whereMonth('b.tgl_faktur','=',$request->bulan)

                            ->whereYear('b.tgl_faktur','=', $request->tahun)

                            ->whereMonth('b.tgl_jatuh_tempo','=',$request->bulan)

                            ->whereYear('b.tgl_jatuh_tempo','=', $request->tahun)

                            ->where('b.id_apotek_nota','=',$id_apotek)

                            ->where('b.id_suplier','=',$val->id)

                            ->where('b.id_jenis_pembelian','=',2)

                            ->where('b.is_deleted', 0)

                            ->where('b.is_lunas', 0)

                            ->where('tb_detail_nota_pembelian.is_deleted', 0)

                            ->first();



                $detail_pembelian_jatuh_tempo_terbayar = DB::table('tb_detail_nota_pembelian')

                            ->select(

                                    DB::raw('SUM(tb_detail_nota_pembelian.diskon) AS total_diskon'),

                                    DB::raw('SUM((tb_detail_nota_pembelian.diskon_persen/100) * tb_detail_nota_pembelian.total_harga) AS total_diskon_persen'),

                                    DB::raw('SUM(tb_detail_nota_pembelian.total_harga) AS total'),

                                    'b.diskon1',

                                    'b.diskon2',

                                    'b.ppn')

                            ->join('tb_nota_pembelian as b','b.id','=','tb_detail_nota_pembelian.id_nota')

                            ->whereMonth('b.tgl_faktur','=',$request->bulan)

                            ->whereYear('b.tgl_faktur','=', $request->tahun)

                            ->whereMonth('b.tgl_jatuh_tempo','=',$request->bulan)

                            ->whereYear('b.tgl_jatuh_tempo','=', $request->tahun)

                            ->where('b.id_apotek_nota','=',$id_apotek)

                            ->where('b.id_suplier','=',$val->id)

                            ->where('b.id_jenis_pembelian','=',2)

                            ->where('b.is_deleted', 0)

                            ->where('b.is_lunas', 1)

                            ->where('tb_detail_nota_pembelian.is_deleted', 0)

                            ->first();



                $total_pembelian1 = $detail_pembelian->total-($detail_pembelian->diskon1+$detail_pembelian->diskon2);

                $total_pembelian = $total_pembelian1 + ($total_pembelian1 * $detail_pembelian->ppn/100);



                $total_pembelian_cash1 = $detail_pembelian_cash->total-($detail_pembelian_cash->diskon1+$detail_pembelian_cash->diskon2);

                $total_pembelian_cash = $total_pembelian_cash1 + ($total_pembelian_cash1 * $detail_pembelian_cash->ppn/100);



                $total_pembelian_credit_terbayar1 = $detail_pembelian_credit_terbayar->total-($detail_pembelian_credit_terbayar->diskon1+$detail_pembelian_credit_terbayar->diskon2);

                $total_pembelian_credit_terbayar = $total_pembelian_credit_terbayar1 + ($total_pembelian_credit_terbayar1 * $detail_pembelian_credit_terbayar->ppn/100);



                $total_pembelian_credit_blm_terbayar1 = $detail_pembelian_credit->total-($detail_pembelian_credit->diskon1+$detail_pembelian_credit->diskon2);

                $total_pembelian_credit_blm_terbayar = $total_pembelian_credit_blm_terbayar1 + ($total_pembelian_credit_blm_terbayar1 * $detail_pembelian_credit->ppn/100);

                $total_pembelian_credit_blm_terbayar = $total_pembelian_credit_blm_terbayar - $total_pembelian_credit_terbayar;



                $total_pembelian_konsinyasi_terbayar1 = $detail_pembelian_konsinyasi_terbayar->total-($detail_pembelian_konsinyasi_terbayar->diskon1+$detail_pembelian_konsinyasi_terbayar->diskon2);

                $total_pembelian_konsinyasi_terbayar = $total_pembelian_konsinyasi_terbayar1 + ($total_pembelian_konsinyasi_terbayar1 * $detail_pembelian_konsinyasi_terbayar->ppn/100);



                $total_pembelian_konsinyasi_blm_terbayar1 = $detail_pembelian_konsinyasi->total-($detail_pembelian_konsinyasi->diskon1+$detail_pembelian_konsinyasi->diskon2);

                $total_pembelian_konsinyasi_blm_terbayar2 = $total_pembelian_konsinyasi_blm_terbayar1 + ($total_pembelian_konsinyasi_blm_terbayar1 * $detail_pembelian_konsinyasi->ppn/100);

                $total_pembelian_konsinyasi_blm_terbayar3 = $detail_pembelian_konsinyasi_terbayar->total-($detail_pembelian_konsinyasi_terbayar->diskon1+$detail_pembelian_konsinyasi_terbayar->diskon2);

                $total_pembelian_konsinyasi_blm_terbayar4 = $total_pembelian_konsinyasi_blm_terbayar3 + ($total_pembelian_konsinyasi_blm_terbayar3 * $detail_pembelian_konsinyasi_terbayar->ppn/100);

                $total_pembelian_konsinyasi_blm_terbayar = $total_pembelian_konsinyasi_blm_terbayar2 - $total_pembelian_konsinyasi_blm_terbayar4;



                $total_pembelian_jatuhtempo_terbayar1 = $detail_pembelian_jatuh_tempo_terbayar->total-($detail_pembelian_jatuh_tempo_terbayar->diskon1+$detail_pembelian_jatuh_tempo_terbayar->diskon2);

                $total_pembelian_jatuhtempo_terbayar = $total_pembelian_jatuhtempo_terbayar1 + ($total_pembelian_jatuhtempo_terbayar1 * $detail_pembelian_jatuh_tempo_terbayar->ppn/100);



                $total_pembelian_jetuhtempo_blm_terbayar1 = $detail_pembelian_jatuh_tempo_blm_bayar->total-($detail_pembelian_jatuh_tempo_blm_bayar->diskon1+$detail_pembelian_jatuh_tempo_blm_bayar->diskon2);

                $total_pembelian_jetuhtempo_blm_terbayar = $total_pembelian_jetuhtempo_blm_terbayar1 + ($total_pembelian_jetuhtempo_blm_terbayar1 * $detail_pembelian_jatuh_tempo_blm_bayar->ppn/100);



                $new_total_pembelian = $new_total_pembelian+$detail_pembelian->total;

                $new_total_pembelian_cash = $new_total_pembelian_cash+$detail_pembelian_cash->total;

                $new_total_pembelian_credit_terbayar = $new_total_pembelian_credit_terbayar+$detail_pembelian_credit_terbayar->total;

                $new_total_pembelian_credit_blm_terbayar = $new_total_pembelian_credit_blm_terbayar+$detail_pembelian_credit->total - $detail_pembelian_credit_terbayar->total;

                $new_total_pembelian_konsinyasi_terbayar = $new_total_pembelian_konsinyasi_terbayar+$detail_pembelian_konsinyasi_terbayar->total;

                $new_total_pembelian_konsinyasi_blm_terbayar = $new_total_pembelian_konsinyasi_blm_terbayar+$detail_pembelian_konsinyasi->total - $detail_pembelian_konsinyasi_terbayar->total;

                $new_total_pembelian_jatuhtempo_terbayar = $new_total_pembelian_jatuhtempo_terbayar+$detail_pembelian_jatuh_tempo_terbayar->total;

                $new_total_pembelian_jetuhtempo_blm_terbayar = $new_total_pembelian_jetuhtempo_blm_terbayar+$detail_pembelian_jatuh_tempo_blm_bayar->total;



                $total_pembelian_format = number_format($total_pembelian,0,',',',');

                $total_pembelian_cash_format = number_format($total_pembelian_cash,0,',',',');

                $total_pembelian_credit_terbayar_format = number_format($total_pembelian_credit_terbayar,0,',',',');

                $total_pembelian_credit_blm_terbayar_format = number_format($total_pembelian_credit_blm_terbayar,0,',',',');

                $total_pembelian_konsinyasi_terbayar_format = number_format($total_pembelian_konsinyasi_terbayar,0,',',',');

                $total_pembelian_konsinyasi_blm_terbayar_format = number_format($total_pembelian_konsinyasi_blm_terbayar,0,',',',');

                $total_pembelian_jatuhtempo_terbayar_format = number_format($total_pembelian_jatuhtempo_terbayar,0,',',',');

                $total_pembelian_jetuhtempo_blm_terbayar_format = number_format($total_pembelian_jetuhtempo_blm_terbayar,0,',',',');



                $new_data = array();

                $new_data['suplier'] = $val->nama;

                $new_data['total'] = $total_pembelian;

                $new_data['total_pembelian'] = 'Rp '.$total_pembelian_format;

                $new_data['total_pembelian_cash'] = 'Rp '.$total_pembelian_cash_format;

                $new_data['total_pembelian_credit_terbayar'] = 'Rp '.$total_pembelian_credit_terbayar_format;

                $new_data['total_pembelian_credit_blm_terbayar'] = 'Rp '.$total_pembelian_credit_blm_terbayar_format;

                $new_data['total_pembelian_konsinyasi_terbayar'] = 'Rp '.$total_pembelian_konsinyasi_terbayar_format;

                $new_data['total_pembelian_konsinyasi_blm_terbayar'] = 'Rp '.$total_pembelian_konsinyasi_blm_terbayar_format;

                $new_data['total_pembelian_jatuhtempo_terbayar'] = 'Rp '.$total_pembelian_jatuhtempo_terbayar_format;

                $new_data['total_pembelian_jetuhtempo_blm_terbayar'] = 'Rp '.$total_pembelian_jetuhtempo_blm_terbayar_format;

                $pembelian[] = $new_data;



            } 

        }



        foreach ($pembelian as $key => $obj) {

            $data.= '<tr>

                            <td class="text-left">'.$obj['suplier'].'</td>

                            <td class="text-right">'.$obj['total_pembelian'].'</td>

                            <td class="text-right">'.$obj['total_pembelian_cash'].'</td>

                            <td class="text-right">'.$obj['total_pembelian_credit_terbayar'].'</td>

                            <td class="text-right">'.$obj['total_pembelian_credit_blm_terbayar'].'</td>

                            <td class="text-right">'.$obj['total_pembelian_konsinyasi_terbayar'].'</td>

                            <td class="text-right">'.$obj['total_pembelian_konsinyasi_blm_terbayar'].'</td>

                            <td class="text-right">'.$obj['total_pembelian_jatuhtempo_terbayar'].'</td>

                            <td class="text-right">'.$obj['total_pembelian_jetuhtempo_blm_terbayar'].'</td>

                        </tr>';

        }



        if(count($pembelian) == 0) {

            $data.= '<tr>

                            <td class="text-center" colspan="9">TIDAK ADA PEMBELIAN</td>

                        </tr>';

        }



        $new_total_pembelian_format = number_format($new_total_pembelian,0,',',',');

        $new_total_pembelian_cash_format = number_format($new_total_pembelian_cash,0,',',',');

        $new_total_pembelian_credit_terbayar_format = number_format($new_total_pembelian_credit_terbayar,0,',',',');

        $new_total_pembelian_credit_blm_terbayar_format = number_format($new_total_pembelian_credit_blm_terbayar,0,',',',');

        $new_total_pembelian_konsinyasi_terbayar_format = number_format($new_total_pembelian_konsinyasi_terbayar,0,',',',');

        $new_total_pembelian_konsinyasi_blm_terbayar_format = number_format($new_total_pembelian_konsinyasi_blm_terbayar,0,',',',');

        $new_total_pembelian_jatuhtempo_terbayar_format = number_format($new_total_pembelian_jatuhtempo_terbayar,0,',',',');

        $new_total_pembelian_jetuhtempo_blm_terbayar_format = number_format($new_total_pembelian_jetuhtempo_blm_terbayar,0,',',',');



        $data .= '<tr>

                    <td class="text-left"><b>TOTAL</b></td>

                    <td class="text-right text-white" style="background-color:#9575cd;">Rp '.$new_total_pembelian_format.'</td>

                    <td class="text-right text-white" style="background-color:#7e57c2;">Rp '.$new_total_pembelian_cash_format.'</td>

                    <td class="text-right text-white" style="background-color:#7e57c2;">Rp '.$new_total_pembelian_credit_terbayar_format.'</td>

                    <td class="text-right text-white" style="background-color:#7e57c2;">Rp '.$new_total_pembelian_credit_blm_terbayar_format.'</td>

                    <td class="text-right text-white" style="background-color:#7e57c2;">Rp '.$new_total_pembelian_konsinyasi_terbayar_format.'</td>

                    <td class="text-right text-white" style="background-color:#7e57c2;">Rp '.$new_total_pembelian_konsinyasi_blm_terbayar_format.'</td>

                    <td class="text-right text-white" style="background-color:#673ab7;">Rp '.$new_total_pembelian_jatuhtempo_terbayar_format.'</td>

                    <td class="text-right text-white" style="background-color:#673ab7;">Rp '.$new_total_pembelian_jetuhtempo_blm_terbayar_format.'</td>

                </tr>';



        $data .= '</tbody></table>';

        echo $data;

    }



    public function recap_perhari() {

        $tahun = date('Y');

        $bulan = date('m');



        return view('recap_perhari')->with(compact('tahun', 'bulan'));

    }



    public function recap_perhari_load_view(Request $request) {

        if($request->tanggal != "") {

            $split                      = explode("-", $request->tanggal);

            $tgl_awal       = date('Y-m-d H:i:s',strtotime($split[0]));

            $tgl_akhir      = date('Y-m-d H:i:s',strtotime($split[1]));

        } else {

            $tgl_awal       = date('Y-m-d H:i:s');

            $tgl_akhir      = date('Y-m-d H:i:s');

        }



        $id_apotek = session('id_apotek_active');

        $data = '';

        $data .= '<table class="table table-bordered table-striped table-hover">

                    <thead>

                        <tr>

                            <th width="10%" colspan="14" class="text-center text-white" style="background-color:#455a64;">PENJUALAN</th>

                        </tr>

                        <tr>

                            <th width="10%" rowspan="2" class="text-center">KERJASAMA</th>

                            <th width="20%" colspan="3" class="text-center text-white" style="background-color:#00bcd4;">KREDIT</th>

                            <th width="20%" colspan="4" class="text-center text-white" style="background-color:#00acc1;">NON KREDIT</th>

                            <th width="20%" colspan="6" class="text-center text-white" style="background-color:#0097a7;">RINCIAN NON KREDIT</th>

                        </tr>

                        <tr>

                            <th class="text-center text-white" style="background-color:#00bcd4;">Total Penjualan</th>

                            <th class="text-center text-white" style="background-color:#00bcd4;">Sudah Terbayar</th>

                            <th class="text-center text-white" style="background-color:#00bcd4;">Belum Terbayar</th>

                            <th class="text-center text-white" style="background-color:#00acc1;">Total Penjualan</th>

                            <th class="text-center text-white" style="background-color:#00acc1;">Cash</th>

                            <th class="text-center text-white" style="background-color:#00acc1;">Non Cash</th>

                            <th class="text-center text-white" style="background-color:#00acc1;">TT</th>

                            <th class="text-center text-white" style="background-color:#0097a7;">Penjualan</th>

                            <th class="text-center text-white" style="background-color:#0097a7;">Jasa Dokter</th>

                            <th class="text-center text-white" style="background-color:#0097a7;">Jasa Resep</th>

                            <th class="text-center text-white" style="background-color:#0097a7;">Paket WT</th>

                            <th class="text-center text-white" style="background-color:#0097a7;">Lab</th>

                            <th class="text-center text-white" style="background-color:#0097a7;">APD</th>

                        </tr>

                    </thead>

                    <tbody>';

        $penjualan = array();



        $detail_penjualan = DB::table('tb_detail_nota_penjualan')

                    ->select(

                            DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) AS total_penjualan'),

                            DB::raw('SUM(tb_detail_nota_penjualan.diskon) AS total_diskon'),

                            DB::raw('SUM((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) - tb_detail_nota_penjualan.diskon) AS total'),

                            DB::raw('SUM(b.diskon_persen/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen'))

                    ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')

                    ->whereDate('b.tgl_nota','>=', $tgl_awal)

                    ->whereDate('b.tgl_nota','<=', $tgl_akhir)

                    ->where('b.id_apotek_nota','=',$id_apotek)

                    ->where('b.is_deleted', 0)

                    ->where('b.is_kredit', 0)

                    ->where('tb_detail_nota_penjualan.is_deleted', 0)

                    ->first();



        $penjualan2 =  DB::table('tb_nota_penjualan')

                    ->select(

                            DB::raw('SUM(tb_nota_penjualan.biaya_jasa_dokter) AS total_jasa_dokter'),

                            DB::raw('SUM(a.biaya) AS total_jasa_resep'),

                            DB::raw('SUM(tb_nota_penjualan.debet) AS total_debet'),

                            DB::raw('SUM(tb_nota_penjualan.harga_wd) AS total_paket_wd'),

                            DB::raw('SUM(tb_nota_penjualan.biaya_lab) AS total_lab'),

                            DB::raw('SUM(tb_nota_penjualan.biaya_apd) AS total_apd'),

                            DB::raw('SUM(tb_nota_penjualan.diskon_rp) AS total_diskon_rp'))

                    ->join('tb_m_jasa_resep as a','a.id','=','tb_nota_penjualan.id_jasa_resep')

                    ->whereDate('tgl_nota','>=', $tgl_awal)

                    ->whereDate('tgl_nota','<=', $tgl_akhir)

                    ->where('id_apotek_nota','=',$id_apotek)

                    ->where('tb_nota_penjualan.is_deleted', 0)

                    ->where('tb_nota_penjualan.is_kredit', 0)

                    ->first();



        $penjualan_closing = TransaksiPenjualanClosing::select([



                                    DB::raw('SUM(total_jasa_dokter) as total_jasa_dokter_a'),

                                    DB::raw('SUM(total_jasa_resep) as total_jasa_resep_a'),

                                    DB::raw('SUM(total_paket_wd) as total_paket_wd_a'),

                                    DB::raw('SUM(total_penjualan) as total_penjualan_a'),

                                    DB::raw('SUM(total_debet) as total_debet_a'),

                                    DB::raw('SUM(total_penjualan_cash) as total_penjualan_cash_a'),

                                    DB::raw('SUM(total_penjualan_cn) as total_penjualan_cn_a'),

                                    DB::raw('SUM(total_penjualan_kredit) as total_penjualan_kredit_a'),

                                    DB::raw('SUM(total_penjualan_kredit_terbayar) as total_penjualan_kredit_terbayar_a'),

                                    DB::raw('SUM(total_diskon) as total_diskon_a'),

                                    DB::raw('SUM(uang_seharusnya) as uang_seharusnya_a'),

                                    DB::raw('SUM(total_akhir) as total_akhir_a'),

                                    DB::raw('SUM(jumlah_tt) as jumlah_tt_a')

                                ])

                                ->whereDate('tanggal','>=', $tgl_awal)

                                ->whereDate('tanggal','<=', $tgl_akhir)

                                ->where('id_apotek_nota','=',$id_apotek)

                                ->first();



        $detail_penjualan_cn = DB::table('tb_detail_nota_penjualan')

                        ->select(

                                DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah_cn) AS total_penjualan'),

                                DB::raw('SUM(tb_detail_nota_penjualan.diskon) AS total_diskon'),

                                DB::raw('SUM((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah_cn) - tb_detail_nota_penjualan.diskon) AS total'),

                                DB::raw('SUM(b.diskon_persen/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah_cn)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen'))

                        ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')

                        ->whereDate('tb_detail_nota_penjualan.cn_at','>=', $tgl_awal)

                        ->whereDate('tb_detail_nota_penjualan.cn_at','<=', $tgl_akhir)

                        ->where('b.id_apotek_nota','=',$id_apotek)

                        ->where('b.is_deleted', 0)

                        ->where('tb_detail_nota_penjualan.is_cn', 1)

                         ->where('tb_detail_nota_penjualan.is_approved', 1)

                        ->where('tb_detail_nota_penjualan.is_deleted', 0)

                        ->where('b.is_kredit', 0)

                        ->first();



        $penjualan_cn_cash = DB::table('tb_detail_nota_penjualan')

                        ->select(

                                DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah_cn) AS total_penjualan')

                            )

                        ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')

                        ->whereDate('tb_detail_nota_penjualan.cn_at','>=', $tgl_awal)

                        ->whereDate('tb_detail_nota_penjualan.cn_at','<=', $tgl_akhir)

                        ->where('b.id_apotek_nota','=',$id_apotek)

                        ->where('b.is_deleted', 0)

                        ->where('b.debet', 0)

                        ->where('tb_detail_nota_penjualan.is_cn', 1)

                         ->where('tb_detail_nota_penjualan.is_approved', 1)

                        ->where('tb_detail_nota_penjualan.is_deleted', 0)

                        ->where('b.is_kredit', 0)

                        ->first();



        $new_total_total_kredit = 0;

        $new_total_total_kredit_terbayar = 0;

        $new_total_total_kredit_blm_terbayar = 0;

        $new_total_total_non_kredit = 0;

        $new_total_total_non_kredit_cash = 0;

        $new_total_total_non_kredit_non_cash = 0;

        $new_total_total_non_kredit_tt = 0;

        $new_total_total_penjualan = 0;

        $new_total_total_jasa_dokter = 0;

        $new_total_total_jasa_resep = 0;

        $new_total_total_paket_wd = 0;

        $new_total_total_lab = 0;

        $new_total_total_apd = 0;



        $total_diskon = $detail_penjualan->total_diskon_persen + $penjualan2->total_diskon_rp;

        $total_3 = $detail_penjualan->total-$total_diskon;

        $grand_total = $total_3+$penjualan2->total_jasa_dokter+$penjualan2->total_jasa_resep+$penjualan2->total_paket_wd+$penjualan2->total_lab+$penjualan2->total_apd;

        $total_cash = $grand_total - $penjualan2->total_debet;

        $total_penjualan_cn_cash = 0;

        if(!empty($penjualan_cn_cash->total_penjualan)) {

            $total_penjualan_cn_cash = $penjualan_cn_cash->total_penjualan - $detail_penjualan_cn->total_diskon_persen;

        }

        $total_penjualan_cn_debet = 0;

        if(!empty($penjualan_cn_debet->total_debet)) {

            $total_penjualan_cn_debet = $detail_penjualan_cn->total-$total_penjualan_cn_cash;

        }

        $total_cn = 0 + $detail_penjualan_cn->total - $detail_penjualan_cn->total_diskon_persen;

        $total_2 = $grand_total-$total_cn;

        $total_cash_x = $total_cash-$total_penjualan_cn_cash;

        $total_debet_x = $penjualan2->total_debet-$total_penjualan_cn_debet;

        $total_penjualan = $total_2-($penjualan2->total_jasa_dokter+$penjualan2->total_jasa_resep+$penjualan2->total_paket_wd+$penjualan2->total_lab+$penjualan2->total_apd);

        $total_3_format = number_format($total_2,0,',',',');

        $g_format = number_format($total_debet_x,0,',',',');

        $h_format = number_format($total_cash_x,0,',',',');

        $a_format = number_format($penjualan2->total_jasa_dokter,0,',',',');

        $b_format = number_format($penjualan2->total_jasa_resep,0,',',',');

        $c_format = number_format($penjualan2->total_paket_wd,0,',',',');

        $d_format = number_format($penjualan2->total_lab,0,',',',');

        $e_format = number_format($penjualan2->total_apd,0,',',',');

        $f_format = number_format($penjualan_closing->jumlah_tt_a,0,',',',');

        $total_penjualan_format = number_format($total_penjualan,0,',',',');

        $new_data = array();

        $new_data['kerjasama'] = 'Umum';

        $new_data['total_kredit'] = '-';

        $new_data['total_kredit_terbayar'] = '-';

        $new_data['total_kredit_blm_terbayar'] = '-';

        $new_data['total_non_kredit'] = 'Rp '.$total_3_format;

        $new_data['total_non_kredit_cash'] = 'Rp '.$h_format;

        $new_data['total_non_kredit_non_cash'] = 'Rp '.$g_format;

        $new_data['total_non_kredit_tt'] = 'Rp '.$f_format;

        $new_data['total_penjualan'] = 'Rp '.$total_penjualan_format;

        $new_data['total_jasa_dokter'] = 'Rp '.$a_format;

        $new_data['total_jasa_resep'] = 'Rp '.$b_format;

        $new_data['total_paket_wd'] = 'Rp '.$c_format;

        $new_data['total_lab'] = 'Rp '.$d_format;

        $new_data['total_apd'] = 'Rp '.$e_format;

        $penjualan[] = $new_data;



        # update 

        $new_total_total_non_kredit = $new_total_total_non_kredit + $total_2;

        $new_total_total_non_kredit_cash = $new_total_total_non_kredit_cash + $total_cash_x;

        $new_total_total_non_kredit_non_cash = $new_total_total_non_kredit_non_cash + $total_debet_x;

        $new_total_total_non_kredit_tt = $new_total_total_non_kredit_tt + $penjualan_closing->jumlah_tt_a;

        $new_total_total_penjualan = $new_total_total_penjualan + $total_penjualan;

        $new_total_total_jasa_dokter = $new_total_total_jasa_dokter + $penjualan2->total_jasa_dokter;

        $new_total_total_jasa_resep = $new_total_total_jasa_resep + $penjualan2->total_jasa_resep;

        $new_total_total_paket_wd = $new_total_total_paket_wd + $penjualan2->total_paket_wd;

        $new_total_total_lab = $new_total_total_lab + $penjualan2->total_lab;

        $new_total_total_apd = $new_total_total_apd + $penjualan2->total_apd;



        $vendors = MasterVendor::where('is_deleted', 0)->get();

        foreach ($vendors as $key => $val) {

            $detail_penjualan_kredit = DB::table('tb_detail_nota_penjualan')

                        ->select(

                                DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) AS total_penjualan'),

                                DB::raw('SUM(tb_detail_nota_penjualan.diskon) AS total_diskon'),

                                DB::raw('SUM((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) - tb_detail_nota_penjualan.diskon) AS total'),

                                DB::raw('SUM(b.diskon_persen/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen'))

                        ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')

                        ->whereDate('b.tgl_nota','>=', $tgl_awal)

                        ->whereDate('b.tgl_nota','<=', $tgl_akhir)

                        ->where('b.id_apotek_nota','=', $id_apotek)

                        ->where('b.id_vendor','=', $val->id)

                        ->where('b.is_deleted', 0)

                        ->where('b.is_kredit', 1)

                        ->where('tb_detail_nota_penjualan.is_cn', 0)

                        ->where('tb_detail_nota_penjualan.is_deleted', 0)

                        ->first();



            $penjualan_kredit =  DB::table('tb_nota_penjualan')

                        ->select(

                                DB::raw('SUM(tb_nota_penjualan.biaya_jasa_dokter) AS total_jasa_dokter'),

                                DB::raw('SUM(a.biaya) AS total_jasa_resep'),

                                DB::raw('SUM(tb_nota_penjualan.debet) AS total_debet'),

                                DB::raw('SUM(tb_nota_penjualan.harga_wd) AS total_paket_wd'),

                                DB::raw('SUM(tb_nota_penjualan.biaya_lab) AS total_lab'),

                                DB::raw('SUM(tb_nota_penjualan.biaya_apd) AS total_apd'),

                                DB::raw('SUM(tb_nota_penjualan.diskon_rp) AS total_diskon_rp'))

                        ->join('tb_m_jasa_resep as a','a.id','=','tb_nota_penjualan.id_jasa_resep')

                        ->whereDate('tgl_nota','>=', $tgl_awal)

                        ->whereDate('tgl_nota','<=', $tgl_akhir)

                        ->where('id_apotek_nota','=', $id_apotek)

                        ->where('id_vendor','=', $val->id)

                        ->where('tb_nota_penjualan.is_deleted', 0)

                        ->where('tb_nota_penjualan.is_kredit', 1)

                        ->first();



            $total_cash_kredit = $detail_penjualan_kredit->total - $penjualan_kredit->total_debet;

            $total_cash_kredit_format = number_format($total_cash_kredit,0,',',',');





            $detail_penjualan_kredit_terbayar = DB::table('tb_detail_nota_penjualan')

                        ->select(

                                DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) AS total_penjualan'),

                                DB::raw('SUM(tb_detail_nota_penjualan.diskon) AS total_diskon'),

                                DB::raw('SUM((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) - tb_detail_nota_penjualan.diskon) AS total'),

                                DB::raw('SUM(b.diskon_persen/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen'),

                                DB::raw('SUM(b.diskon_vendor/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_vendor')

                            )

                        ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')

                        ->whereDate('b.is_lunas_pembayaran_kredit_at','>=', $tgl_awal)

                        ->whereDate('b.is_lunas_pembayaran_kredit_at','<=', $tgl_akhir)

                        ->where('b.id_apotek_nota','=',$id_apotek)

                        ->where('b.id_vendor','=', $val->id)

                        ->where('b.is_deleted', 0)

                        ->where('b.is_kredit', 1)

                        ->where('b.is_lunas_pembayaran_kredit', 1)

                        ->where('tb_detail_nota_penjualan.is_cn', 0)

                        ->where('tb_detail_nota_penjualan.is_deleted', 0)

                        ->first();

        

            $penjualan_kredit_terbayar =  DB::table('tb_nota_penjualan')

                        ->select(

                                DB::raw('SUM(tb_nota_penjualan.biaya_jasa_dokter) AS total_jasa_dokter'),

                                DB::raw('SUM(a.biaya) AS total_jasa_resep'),

                                DB::raw('SUM(tb_nota_penjualan.diskon_rp) AS total_diskon_rp'),

                                DB::raw('SUM(tb_nota_penjualan.debet) AS total_debet'))

                        ->join('tb_m_jasa_resep as a','a.id','=','tb_nota_penjualan.id_jasa_resep')

                        ->whereDate('tb_nota_penjualan.is_lunas_pembayaran_kredit_at','>=', $tgl_awal)

                        ->whereDate('tb_nota_penjualan.is_lunas_pembayaran_kredit_at','<=', $tgl_akhir)

                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)

                        ->where('tb_nota_penjualan.id_vendor','=', $val->id)

                        ->where('tb_nota_penjualan.is_deleted', 0)

                        ->where('tb_nota_penjualan.is_kredit', 1)

                        ->where('tb_nota_penjualan.is_lunas_pembayaran_kredit', 1)

                        ->first();





            $total_cash_kredit_terbayar = ($detail_penjualan_kredit_terbayar->total + $penjualan_kredit_terbayar->total_jasa_dokter + $penjualan_kredit_terbayar->total_jasa_resep) - $penjualan_kredit_terbayar->total_debet-$detail_penjualan_kredit_terbayar->total_diskon_vendor;

            $total_penjualan_kredit_terbayar = $penjualan_kredit_terbayar->total_debet+$total_cash_kredit_terbayar;

            $total_penjualan_kredit_terbayar_format = number_format($total_penjualan_kredit_terbayar,0,',',',');

            $total_penjualan_kredit_blm_terbayar = $total_cash_kredit - $total_penjualan_kredit_terbayar;

            $total_penjualan_kredit_blm_terbayar_format = number_format($total_penjualan_kredit_blm_terbayar,0,',',',');

            $total_penjualan = $detail_penjualan_kredit->total-($penjualan_kredit->total_jasa_dokter+$penjualan_kredit->total_jasa_resep+$penjualan_kredit->total_paket_wd+$penjualan_kredit->total_lab+$penjualan_kredit->total_apd);

         

            $a_format = number_format($penjualan_kredit->total_jasa_dokter,0,',',',');

            $b_format = number_format($penjualan_kredit->total_jasa_resep,0,',',',');

            $c_format = number_format($penjualan_kredit->total_paket_wd,0,',',',');

            $d_format = number_format($penjualan_kredit->total_lab,0,',',',');

            $e_format = number_format($penjualan_kredit->total_apd,0,',',',');

            $total_penjualan_format = number_format($total_penjualan,0,',',',');



            $new_data = array();

            $new_data['kerjasama'] = $val->nama;

            $new_data['total_kredit'] = 'Rp '.$total_cash_kredit_format;

            $new_data['total_kredit_terbayar'] = 'Rp '.$total_penjualan_kredit_terbayar_format;

            $new_data['total_kredit_blm_terbayar'] = 'Rp '.$total_penjualan_kredit_blm_terbayar_format;

            $new_data['total_non_kredit'] = '-';

            $new_data['total_non_kredit_cash'] = '-';

            $new_data['total_non_kredit_non_cash'] = '-';

            $new_data['total_non_kredit_tt'] = '-';

            $new_data['total_penjualan'] = 'Rp '.$total_penjualan_format;

            $new_data['total_jasa_dokter'] = 'Rp '.$a_format;

            $new_data['total_jasa_resep'] = 'Rp '.$b_format;

            $new_data['total_paket_wd'] = 'Rp '.$c_format;

            $new_data['total_lab'] = 'Rp '.$d_format;

            $new_data['total_apd'] = 'Rp '.$e_format;

            $penjualan[] = $new_data;



            # update 

            $new_total_total_kredit = $new_total_total_kredit + $total_cash_kredit;

            $new_total_total_kredit_terbayar = $new_total_total_kredit_terbayar + $total_penjualan_kredit_terbayar;

            $new_total_total_kredit_blm_terbayar = $new_total_total_kredit_blm_terbayar + $total_penjualan_kredit_blm_terbayar;

            $new_total_total_penjualan = $new_total_total_penjualan + $total_penjualan;

            $new_total_total_jasa_dokter = $new_total_total_jasa_dokter + $penjualan_kredit->total_jasa_dokter;

            $new_total_total_jasa_resep = $new_total_total_jasa_resep + $penjualan_kredit->total_jasa_resep;

            $new_total_total_paket_wd = $new_total_total_paket_wd + $penjualan_kredit->total_paket_wd;

            $new_total_total_lab = $new_total_total_lab + $penjualan_kredit->total_lab;

            $new_total_total_apd = $new_total_total_apd + $penjualan_kredit->total_apd;

        }



        foreach ($penjualan as $key => $obj) {

            $data.= '<tr>

                            <td class="text-left">'.$obj['kerjasama'].'</td>

                            <td class="text-right">'.$obj['total_kredit'].'</td>

                            <td class="text-right">'.$obj['total_kredit_terbayar'].'</td>

                            <td class="text-right">'.$obj['total_kredit_blm_terbayar'].'</td>

                            <td class="text-right">'.$obj['total_non_kredit'].'</td>

                            <td class="text-right">'.$obj['total_non_kredit_cash'].'</td>

                            <td class="text-right">'.$obj['total_non_kredit_non_cash'].'</td>

                            <td class="text-right">'.$obj['total_non_kredit_tt'].'</td>

                            <td class="text-right">'.$obj['total_penjualan'].'</td>

                            <td class="text-right">'.$obj['total_jasa_dokter'].'</td>

                            <td class="text-right">'.$obj['total_jasa_resep'].'</td>

                            <td class="text-right">'.$obj['total_paket_wd'].'</td>

                            <td class="text-right">'.$obj['total_lab'].'</td>

                            <td class="text-right">'.$obj['total_apd'].'</td>

                        </tr>';

        }





        if(count($penjualan) == 0) {

            $data.= '<tr>

                            <td class="text-center" colspan="14">TIDAK ADA PENJUALAN</td>

                        </tr>';

        }





        $new_total_total_kredit_format = number_format($new_total_total_kredit,0,',',',');

        $new_total_total_kredit_terbayar_format = number_format($new_total_total_kredit_terbayar,0,',',',');

        $new_total_total_kredit_blm_terbayar_format = number_format($new_total_total_kredit_blm_terbayar,0,',',',');

        $new_total_total_non_kredit_format = number_format($new_total_total_non_kredit,0,',',',');

        $new_total_total_non_kredit_cash_format = number_format($new_total_total_non_kredit_cash,0,',',',');

        $new_total_total_non_kredit_non_cash_format = number_format($new_total_total_non_kredit_non_cash,0,',',',');

        $new_total_total_non_kredit_tt_format = number_format($new_total_total_non_kredit_tt,0,',',',');

        $new_total_total_penjualan_format = number_format($new_total_total_penjualan,0,',',',');

        $new_total_total_jasa_dokter_format = number_format($new_total_total_jasa_dokter,0,',',',');

        $new_total_total_jasa_resep_format = number_format($new_total_total_jasa_resep,0,',',',');

        $new_total_total_paket_wd_format = number_format($new_total_total_paket_wd,0,',',',');

        $new_total_total_lab_format = number_format($new_total_total_lab,0,',',',');

        $new_total_total_apd_format = number_format($new_total_total_apd,0,',',',');



        $data .= '<tr>

                    <td class="text-left"><b>TOTAL</b></td>

                    <td class="text-right text-white" style="background-color:#00bcd4;">Rp '.$new_total_total_kredit_format.'</td>

                    <td class="text-right text-white" style="background-color:#00bcd4;">Rp '.$new_total_total_kredit_terbayar_format.'</td>

                    <td class="text-right text-white" style="background-color:#00bcd4;">Rp '.$new_total_total_kredit_blm_terbayar_format.'</td>

                    <td class="text-right text-white" style="background-color:#00acc1;">Rp '.$new_total_total_non_kredit_format.'</td>

                    <td class="text-right text-white" style="background-color:#00acc1;">Rp '.$new_total_total_non_kredit_cash_format.'</td>

                    <td class="text-right text-white" style="background-color:#00acc1;">Rp '.$new_total_total_non_kredit_non_cash_format.'</td>

                    <td class="text-right text-white" style="background-color:#00acc1;">Rp '.$new_total_total_non_kredit_tt_format.'</td>

                    <td class="text-right text-white" style="background-color:#0097a7;">Rp '.$new_total_total_penjualan_format.'</td>

                    <td class="text-right text-white" style="background-color:#0097a7;">Rp '.$new_total_total_jasa_dokter_format.'</td>

                    <td class="text-right text-white" style="background-color:#0097a7;">Rp '.$new_total_total_jasa_resep_format.'</td>

                    <td class="text-right text-white" style="background-color:#0097a7;">Rp '.$new_total_total_paket_wd_format.'</td>

                    <td class="text-right text-white" style="background-color:#0097a7;">Rp '.$new_total_total_lab_format.'</td>

                    <td class="text-right text-white" style="background-color:#0097a7;">Rp '.$new_total_total_apd_format.'</td>

                </tr>';



        $data .= '</tbody></table>';

        echo $data;

    }



    public function recap_perhari_pembelian_load_view(Request $request) {

        if($request->tanggal != "") {

            $split                      = explode("-", $request->tanggal);

            $tgl_awal       = date('Y-m-d H:i:s',strtotime($split[0]));

            $tgl_akhir      = date('Y-m-d H:i:s',strtotime($split[1]));

        } else {

            $tgl_awal       = date('Y-m-d H:i:s');

            $tgl_akhir      = date('Y-m-d H:i:s');

        }



        $id_apotek = session('id_apotek_active');

        $data = '';

        $data .= '<table class="table table-bordered table-striped table-hover">

                    <thead>

                        <tr>

                            <th width="10%" colspan="14" class="text-center text-white" style="background-color:#455a64;">PEMBELIAN</th>

                        </tr>

                        <tr>

                            <th width="10%" rowspan="3" class="text-center">SUPLIER</th>

                            <th width="20%" rowspan="3" class="text-center text-white" style="background-color:#9575cd;">TOTAL PEMBELIAN</th>

                            <th width="20%" colspan="5" class="text-center text-white" style="background-color:#7e57c2;">RINCIAN</th>

                            <th width="20%" rowspan="2" colspan="2" class="text-center text-white" style="background-color:#673ab7;">JATUH TEMPO</th>

                        </tr>

                        <tr>

                            <th class="text-center text-white" style="background-color:#7e57c2;" rowspan="2">Cash</th>

                            <th class="text-center text-white" style="background-color:#7e57c2;" colspan="2">Credit</th>

                            <th class="text-center text-white" style="background-color:#7e57c2;" colspan="2">Konsinyasi</th>

                        </tr>

                        <tr>

                            <th class="text-center text-white" style="background-color:#7e57c2;">Sudah Terbayar</th>

                            <th class="text-center text-white" style="background-color:#7e57c2;">Belum terbayar</th>

                            <th class="text-center text-white" style="background-color:#7e57c2;">Sudah Terbayar</th>

                            <th class="text-center text-white" style="background-color:#7e57c2;">Belum terbayar</th>

                            <th class="text-center text-white" style="background-color:#673ab7;">Sudah Terbayar</th>

                            <th class="text-center text-white" style="background-color:#673ab7;">Belum terbayar</th>

                        </tr>

                    </thead>

                    <tbody>';

        $pembelian = array();



        $new_total_pembelian = 0;

        $new_total_pembelian_cash = 0;

        $new_total_pembelian_credit_terbayar = 0;

        $new_total_pembelian_credit_blm_terbayar = 0;

        $new_total_pembelian_konsinyasi_terbayar = 0;

        $new_total_pembelian_konsinyasi_blm_terbayar = 0;

        $new_total_pembelian_jatuhtempo_terbayar = 0;

        $new_total_pembelian_jetuhtempo_blm_terbayar = 0;



        $supliers = MasterSuplier::where('is_deleted', 0)->get();

        foreach ($supliers as $key => $val) {

            $detail_pembelian = DB::table('tb_detail_nota_pembelian')

                        ->select(

                                DB::raw('SUM(tb_detail_nota_pembelian.diskon) AS total_diskon'),

                                DB::raw('SUM((tb_detail_nota_pembelian.diskon_persen/100) * tb_detail_nota_pembelian.total_harga) AS total_diskon_persen'),

                                DB::raw('SUM(tb_detail_nota_pembelian.total_harga) AS total'),

                                'b.diskon1',

                                'b.diskon2',

                                'b.ppn')

                        ->join('tb_nota_pembelian as b','b.id','=','tb_detail_nota_pembelian.id_nota')

                        ->whereDate('b.tgl_faktur','>=', $tgl_awal)

                        ->whereDate('b.tgl_faktur','<=', $tgl_akhir)

                        ->where('b.id_apotek_nota','=',$id_apotek)

                        ->where('b.id_suplier','=',$val->id)

                        ->where('b.is_deleted', 0)

                        ->where('tb_detail_nota_pembelian.is_deleted', 0)

                        ->first();



            if($detail_pembelian->total != 0) {

                $detail_pembelian_cash = DB::table('tb_detail_nota_pembelian')

                            ->select(

                                    DB::raw('SUM(tb_detail_nota_pembelian.diskon) AS total_diskon'),

                                    DB::raw('SUM((tb_detail_nota_pembelian.diskon_persen/100) * tb_detail_nota_pembelian.total_harga) AS total_diskon_persen'),

                                    DB::raw('SUM(tb_detail_nota_pembelian.total_harga) AS total'),

                                    'b.diskon1',

                                    'b.diskon2',

                                    'b.ppn')

                            ->join('tb_nota_pembelian as b','b.id','=','tb_detail_nota_pembelian.id_nota')

                            ->whereDate('b.tgl_faktur','>=', $tgl_awal)

                            ->whereDate('b.tgl_faktur','<=', $tgl_akhir)

                            ->where('b.id_apotek_nota','=',$id_apotek)

                            ->where('b.id_suplier','=',$val->id)

                            ->where('b.id_jenis_pembelian','=',1)

                            ->where('b.is_deleted', 0)

                            ->where('tb_detail_nota_pembelian.is_deleted', 0)

                            ->first();



                $detail_pembelian_credit = DB::table('tb_detail_nota_pembelian')

                            ->select(

                                    DB::raw('SUM(tb_detail_nota_pembelian.diskon) AS total_diskon'),

                                    DB::raw('SUM((tb_detail_nota_pembelian.diskon_persen/100) * tb_detail_nota_pembelian.total_harga) AS total_diskon_persen'),

                                    DB::raw('SUM(tb_detail_nota_pembelian.total_harga) AS total'),

                                    'b.diskon1',

                                    'b.diskon2',

                                    'b.ppn')

                            ->join('tb_nota_pembelian as b','b.id','=','tb_detail_nota_pembelian.id_nota')

                            ->whereDate('b.tgl_faktur','>=', $tgl_awal)

                            ->whereDate('b.tgl_faktur','<=', $tgl_akhir)

                            ->where('b.id_apotek_nota','=',$id_apotek)

                            ->where('b.id_suplier','=',$val->id)

                            ->where('b.id_jenis_pembelian','=',2)

                            ->where('b.is_deleted', 0)

                            ->where('tb_detail_nota_pembelian.is_deleted', 0)

                            ->first();



                $detail_pembelian_konsinyasi = DB::table('tb_detail_nota_pembelian')

                            ->select(

                                    DB::raw('SUM(tb_detail_nota_pembelian.diskon) AS total_diskon'),

                                    DB::raw('SUM((tb_detail_nota_pembelian.diskon_persen/100) * tb_detail_nota_pembelian.total_harga) AS total_diskon_persen'),

                                    DB::raw('SUM(tb_detail_nota_pembelian.total_harga) AS total'),

                                    'b.diskon1',

                                    'b.diskon2',

                                    'b.ppn')

                            ->join('tb_nota_pembelian as b','b.id','=','tb_detail_nota_pembelian.id_nota')

                            ->whereDate('b.tgl_faktur','>=', $tgl_awal)

                            ->whereDate('b.tgl_faktur','<=', $tgl_akhir)

                            ->where('b.id_apotek_nota','=',$id_apotek)

                            ->where('b.id_suplier','=',$val->id)

                            ->where('b.id_jenis_pembelian','=',3)

                            ->where('b.is_deleted', 0)

                            ->where('tb_detail_nota_pembelian.is_deleted', 0)

                            ->first();



                $detail_pembelian_credit_terbayar = DB::table('tb_detail_nota_pembelian')

                            ->select(

                                    DB::raw('SUM(tb_detail_nota_pembelian.diskon) AS total_diskon'),

                                    DB::raw('SUM((tb_detail_nota_pembelian.diskon_persen/100) * tb_detail_nota_pembelian.total_harga) AS total_diskon_persen'),

                                    DB::raw('SUM(tb_detail_nota_pembelian.total_harga) AS total'),

                                    'b.diskon1',

                                    'b.diskon2',

                                    'b.ppn')

                            ->join('tb_nota_pembelian as b','b.id','=','tb_detail_nota_pembelian.id_nota')

                            ->whereDate('b.tgl_faktur','>=', $tgl_awal)

                            ->whereDate('b.tgl_faktur','<=', $tgl_akhir)

                            ->where('b.id_apotek_nota','=',$id_apotek)

                            ->where('b.id_suplier','=',$val->id)

                            ->where('b.id_jenis_pembelian','=',2)

                            ->where('b.is_deleted', 0)

                            ->where('b.is_lunas', 1)

                            ->where('tb_detail_nota_pembelian.is_deleted', 0)

                            ->first();





                $detail_pembelian_konsinyasi_terbayar = DB::table('tb_detail_nota_pembelian')

                            ->select(

                                    DB::raw('SUM(tb_detail_nota_pembelian.diskon) AS total_diskon'),

                                    DB::raw('SUM((tb_detail_nota_pembelian.diskon_persen/100) * tb_detail_nota_pembelian.total_harga) AS total_diskon_persen'),

                                    DB::raw('SUM(tb_detail_nota_pembelian.total_harga) AS total'),

                                    'b.diskon1',

                                    'b.diskon2',

                                    'b.ppn')

                            ->join('tb_nota_pembelian as b','b.id','=','tb_detail_nota_pembelian.id_nota')

                            ->whereDate('b.tgl_faktur','>=', $tgl_awal)

                            ->whereDate('b.tgl_faktur','<=', $tgl_akhir)

                            ->where('b.id_apotek_nota','=',$id_apotek)

                            ->where('b.id_suplier','=',$val->id)

                            ->where('b.id_jenis_pembelian','=',3)

                            ->where('b.is_deleted', 0)

                            ->where('b.is_lunas', 1)

                            ->where('tb_detail_nota_pembelian.is_deleted', 0)

                            ->first();



                $detail_pembelian_jatuh_tempo_blm_bayar = DB::table('tb_detail_nota_pembelian')

                            ->select(

                                    DB::raw('SUM(tb_detail_nota_pembelian.diskon) AS total_diskon'),

                                    DB::raw('SUM((tb_detail_nota_pembelian.diskon_persen/100) * tb_detail_nota_pembelian.total_harga) AS total_diskon_persen'),

                                    DB::raw('SUM(tb_detail_nota_pembelian.total_harga) AS total'),

                                    'b.diskon1',

                                    'b.diskon2',

                                    'b.ppn')

                            ->join('tb_nota_pembelian as b','b.id','=','tb_detail_nota_pembelian.id_nota')

                            ->whereDate('b.tgl_faktur','>=', $tgl_awal)

                            ->whereDate('b.tgl_faktur','<=', $tgl_akhir)

                            ->whereDate('b.tgl_jatuh_tempo','>=', $tgl_awal)

                            ->whereDate('b.tgl_jatuh_tempo','<=', $tgl_akhir)

                            ->where('b.id_apotek_nota','=',$id_apotek)

                            ->where('b.id_suplier','=',$val->id)

                            ->where('b.id_jenis_pembelian','=',2)

                            ->where('b.is_deleted', 0)

                            ->where('b.is_lunas', 0)

                            ->where('tb_detail_nota_pembelian.is_deleted', 0)

                            ->first();



                $detail_pembelian_jatuh_tempo_terbayar = DB::table('tb_detail_nota_pembelian')

                            ->select(

                                    DB::raw('SUM(tb_detail_nota_pembelian.diskon) AS total_diskon'),

                                    DB::raw('SUM((tb_detail_nota_pembelian.diskon_persen/100) * tb_detail_nota_pembelian.total_harga) AS total_diskon_persen'),

                                    DB::raw('SUM(tb_detail_nota_pembelian.total_harga) AS total'),

                                    'b.diskon1',

                                    'b.diskon2',

                                    'b.ppn')

                            ->join('tb_nota_pembelian as b','b.id','=','tb_detail_nota_pembelian.id_nota')

                            ->whereDate('b.tgl_faktur','>=', $tgl_awal)

                            ->whereDate('b.tgl_faktur','<=', $tgl_akhir)

                            ->whereDate('b.tgl_jatuh_tempo','>=', $tgl_awal)

                            ->whereDate('b.tgl_jatuh_tempo','<=', $tgl_akhir)

                            ->where('b.id_apotek_nota','=',$id_apotek)

                            ->where('b.id_suplier','=',$val->id)

                            ->where('b.id_jenis_pembelian','=',2)

                            ->where('b.is_deleted', 0)

                            ->where('b.is_lunas', 1)

                            ->where('tb_detail_nota_pembelian.is_deleted', 0)

                            ->first();



                $total_pembelian1 = $detail_pembelian->total-($detail_pembelian->diskon1+$detail_pembelian->diskon2);

                $total_pembelian = $total_pembelian1 + ($total_pembelian1 * $detail_pembelian->ppn/100);



                $total_pembelian_cash1 = $detail_pembelian_cash->total-($detail_pembelian_cash->diskon1+$detail_pembelian_cash->diskon2);

                $total_pembelian_cash = $total_pembelian_cash1 + ($total_pembelian_cash1 * $detail_pembelian_cash->ppn/100);



                $total_pembelian_credit_terbayar1 = $detail_pembelian_credit_terbayar->total-($detail_pembelian_credit_terbayar->diskon1+$detail_pembelian_credit_terbayar->diskon2);

                $total_pembelian_credit_terbayar = $total_pembelian_credit_terbayar1 + ($total_pembelian_credit_terbayar1 * $detail_pembelian_credit_terbayar->ppn/100);



                $total_pembelian_credit_blm_terbayar1 = $detail_pembelian_credit->total-($detail_pembelian_credit->diskon1+$detail_pembelian_credit->diskon2);

                $total_pembelian_credit_blm_terbayar = $total_pembelian_credit_blm_terbayar1 + ($total_pembelian_credit_blm_terbayar1 * $detail_pembelian_credit->ppn/100);

                $total_pembelian_credit_blm_terbayar = $total_pembelian_credit_blm_terbayar - $total_pembelian_credit_terbayar;



                $total_pembelian_konsinyasi_terbayar1 = $detail_pembelian_konsinyasi_terbayar->total-($detail_pembelian_konsinyasi_terbayar->diskon1+$detail_pembelian_konsinyasi_terbayar->diskon2);

                $total_pembelian_konsinyasi_terbayar = $total_pembelian_konsinyasi_terbayar1 + ($total_pembelian_konsinyasi_terbayar1 * $detail_pembelian_konsinyasi_terbayar->ppn/100);



                $total_pembelian_konsinyasi_blm_terbayar1 = $detail_pembelian_konsinyasi->total-($detail_pembelian_konsinyasi->diskon1+$detail_pembelian_konsinyasi->diskon2);

                $total_pembelian_konsinyasi_blm_terbayar2 = $total_pembelian_konsinyasi_blm_terbayar1 + ($total_pembelian_konsinyasi_blm_terbayar1 * $detail_pembelian_konsinyasi->ppn/100);

                $total_pembelian_konsinyasi_blm_terbayar3 = $detail_pembelian_konsinyasi_terbayar->total-($detail_pembelian_konsinyasi_terbayar->diskon1+$detail_pembelian_konsinyasi_terbayar->diskon2);

                $total_pembelian_konsinyasi_blm_terbayar4 = $total_pembelian_konsinyasi_blm_terbayar3 + ($total_pembelian_konsinyasi_blm_terbayar3 * $detail_pembelian_konsinyasi_terbayar->ppn/100);

                $total_pembelian_konsinyasi_blm_terbayar = $total_pembelian_konsinyasi_blm_terbayar2 - $total_pembelian_konsinyasi_blm_terbayar4;



                $total_pembelian_jatuhtempo_terbayar1 = $detail_pembelian_jatuh_tempo_terbayar->total-($detail_pembelian_jatuh_tempo_terbayar->diskon1+$detail_pembelian_jatuh_tempo_terbayar->diskon2);

                $total_pembelian_jatuhtempo_terbayar = $total_pembelian_jatuhtempo_terbayar1 + ($total_pembelian_jatuhtempo_terbayar1 * $detail_pembelian_jatuh_tempo_terbayar->ppn/100);



                $total_pembelian_jetuhtempo_blm_terbayar1 = $detail_pembelian_jatuh_tempo_blm_bayar->total-($detail_pembelian_jatuh_tempo_blm_bayar->diskon1+$detail_pembelian_jatuh_tempo_blm_bayar->diskon2);

                $total_pembelian_jetuhtempo_blm_terbayar = $total_pembelian_jetuhtempo_blm_terbayar1 + ($total_pembelian_jetuhtempo_blm_terbayar1 * $detail_pembelian_jatuh_tempo_blm_bayar->ppn/100);



                $new_total_pembelian = $new_total_pembelian+$detail_pembelian->total;

                $new_total_pembelian_cash = $new_total_pembelian_cash+$detail_pembelian_cash->total;

                $new_total_pembelian_credit_terbayar = $new_total_pembelian_credit_terbayar+$detail_pembelian_credit_terbayar->total;

                $new_total_pembelian_credit_blm_terbayar = $new_total_pembelian_credit_blm_terbayar+$detail_pembelian_credit->total - $detail_pembelian_credit_terbayar->total;

                $new_total_pembelian_konsinyasi_terbayar = $new_total_pembelian_konsinyasi_terbayar+$detail_pembelian_konsinyasi_terbayar->total;

                $new_total_pembelian_konsinyasi_blm_terbayar = $new_total_pembelian_konsinyasi_blm_terbayar+$detail_pembelian_konsinyasi->total - $detail_pembelian_konsinyasi_terbayar->total;

                $new_total_pembelian_jatuhtempo_terbayar = $new_total_pembelian_jatuhtempo_terbayar+$detail_pembelian_jatuh_tempo_terbayar->total;

                $new_total_pembelian_jetuhtempo_blm_terbayar = $new_total_pembelian_jetuhtempo_blm_terbayar+$detail_pembelian_jatuh_tempo_blm_bayar->total;



                $total_pembelian_format = number_format($total_pembelian,0,',',',');

                $total_pembelian_cash_format = number_format($total_pembelian_cash,0,',',',');

                $total_pembelian_credit_terbayar_format = number_format($total_pembelian_credit_terbayar,0,',',',');

                $total_pembelian_credit_blm_terbayar_format = number_format($total_pembelian_credit_blm_terbayar,0,',',',');

                $total_pembelian_konsinyasi_terbayar_format = number_format($total_pembelian_konsinyasi_terbayar,0,',',',');

                $total_pembelian_konsinyasi_blm_terbayar_format = number_format($total_pembelian_konsinyasi_blm_terbayar,0,',',',');

                $total_pembelian_jatuhtempo_terbayar_format = number_format($total_pembelian_jatuhtempo_terbayar,0,',',',');

                $total_pembelian_jetuhtempo_blm_terbayar_format = number_format($total_pembelian_jetuhtempo_blm_terbayar,0,',',',');



                $new_data = array();

                $new_data['suplier'] = $val->nama;

                $new_data['total'] = $total_pembelian;

                $new_data['total_pembelian'] = 'Rp '.$total_pembelian_format;

                $new_data['total_pembelian_cash'] = 'Rp '.$total_pembelian_cash_format;

                $new_data['total_pembelian_credit_terbayar'] = 'Rp '.$total_pembelian_credit_terbayar_format;

                $new_data['total_pembelian_credit_blm_terbayar'] = 'Rp '.$total_pembelian_credit_blm_terbayar_format;

                $new_data['total_pembelian_konsinyasi_terbayar'] = 'Rp '.$total_pembelian_konsinyasi_terbayar_format;

                $new_data['total_pembelian_konsinyasi_blm_terbayar'] = 'Rp '.$total_pembelian_konsinyasi_blm_terbayar_format;

                $new_data['total_pembelian_jatuhtempo_terbayar'] = 'Rp '.$total_pembelian_jatuhtempo_terbayar_format;

                $new_data['total_pembelian_jetuhtempo_blm_terbayar'] = 'Rp '.$total_pembelian_jetuhtempo_blm_terbayar_format;

                $pembelian[] = $new_data;



            } 

        }



        foreach ($pembelian as $key => $obj) {

            $data.= '<tr>

                            <td class="text-left">'.$obj['suplier'].'</td>

                            <td class="text-right">'.$obj['total_pembelian'].'</td>

                            <td class="text-right">'.$obj['total_pembelian_cash'].'</td>

                            <td class="text-right">'.$obj['total_pembelian_credit_terbayar'].'</td>

                            <td class="text-right">'.$obj['total_pembelian_credit_blm_terbayar'].'</td>

                            <td class="text-right">'.$obj['total_pembelian_konsinyasi_terbayar'].'</td>

                            <td class="text-right">'.$obj['total_pembelian_konsinyasi_blm_terbayar'].'</td>

                            <td class="text-right">'.$obj['total_pembelian_jatuhtempo_terbayar'].'</td>

                            <td class="text-right">'.$obj['total_pembelian_jetuhtempo_blm_terbayar'].'</td>

                        </tr>';

        }



        if(count($pembelian) == 0) {

            $data.= '<tr>

                            <td class="text-center" colspan="9">TIDAK ADA PEMBELIAN</td>

                        </tr>';

        }



        $new_total_pembelian_format = number_format($new_total_pembelian,0,',',',');

        $new_total_pembelian_cash_format = number_format($new_total_pembelian_cash,0,',',',');

        $new_total_pembelian_credit_terbayar_format = number_format($new_total_pembelian_credit_terbayar,0,',',',');

        $new_total_pembelian_credit_blm_terbayar_format = number_format($new_total_pembelian_credit_blm_terbayar,0,',',',');

        $new_total_pembelian_konsinyasi_terbayar_format = number_format($new_total_pembelian_konsinyasi_terbayar,0,',',',');

        $new_total_pembelian_konsinyasi_blm_terbayar_format = number_format($new_total_pembelian_konsinyasi_blm_terbayar,0,',',',');

        $new_total_pembelian_jatuhtempo_terbayar_format = number_format($new_total_pembelian_jatuhtempo_terbayar,0,',',',');

        $new_total_pembelian_jetuhtempo_blm_terbayar_format = number_format($new_total_pembelian_jetuhtempo_blm_terbayar,0,',',',');



        $data .= '<tr>

                    <td class="text-left"><b>TOTAL</b></td>

                    <td class="text-right text-white" style="background-color:#9575cd;">Rp '.$new_total_pembelian_format.'</td>

                    <td class="text-right text-white" style="background-color:#7e57c2;">Rp '.$new_total_pembelian_cash_format.'</td>

                    <td class="text-right text-white" style="background-color:#7e57c2;">Rp '.$new_total_pembelian_credit_terbayar_format.'</td>

                    <td class="text-right text-white" style="background-color:#7e57c2;">Rp '.$new_total_pembelian_credit_blm_terbayar_format.'</td>

                    <td class="text-right text-white" style="background-color:#7e57c2;">Rp '.$new_total_pembelian_konsinyasi_terbayar_format.'</td>

                    <td class="text-right text-white" style="background-color:#7e57c2;">Rp '.$new_total_pembelian_konsinyasi_blm_terbayar_format.'</td>

                    <td class="text-right text-white" style="background-color:#673ab7;">Rp '.$new_total_pembelian_jatuhtempo_terbayar_format.'</td>

                    <td class="text-right text-white" style="background-color:#673ab7;">Rp '.$new_total_pembelian_jetuhtempo_blm_terbayar_format.'</td>

                </tr>';



        $data .= '</tbody></table>';

        echo $data;

    }



    public function page_not_authorized()

    {

        return view('page_not_authorized');

    }



    public function page_not_found()

    {

        return view('page_not_found');

    }



    public function set_active_apotek($id_apotek){

        $apotek = MasterApotek::where('id', '=', $id_apotek)->first();



        if(!is_null($apotek)){

            session(['nama_apotek_singkat_active'=>strtolower($apotek->nama_singkat)]);

            session(['nama_apotek_panjang_active'=>$apotek->nama_panjang]);

            session(['nama_apotek_active'=>$apotek->nama_singkat]);

            session(['id_apotek_active'=>$apotek->id]);

            session()->flash('success', 'Sukses melakukan perubahan apotek menjadi '.$apotek->nama_singkat.'!');

        }else{

            session()->flash('error', 'Gagal melakukan perubahan apotek!. Apotek tidak dapat ditemukan.');

        }

        return redirect()->intended('/home');

    }



    public function set_active_role($id_role){

        $user_role = RbacUserRole::join('rbac_roles', 'rbac_roles.id', '=', 'rbac_user_role.id_role')

                                ->where('rbac_user_role.id_user', '=', Auth::user()->id)

                                ->where('rbac_roles.id', '=', $id_role)

                                ->first();



        if(!empty($user_role)){

            session(['nama_role_active'=>$user_role->nama]);

            session(['id_role_active'=>$user_role->id]);



            $menus = array();



            $role_permissions = RbacRolePermission::where("id_role", $user_role->id)->get();

            foreach ($role_permissions as $role_permission) {

                $permission = RbacPermission::find($role_permission->id_permission);

                $menus[] = $permission->id_menu;

            }





            $menu = RbacMenu::where('is_deleted', 0)->whereIn('id', $menus)->orderBy('weight')->get();

            $parents = array();

            foreach ($menu as $key => $val) {

                if($val->parent == 0) {

                    $data_parent = RbacMenu::find($val->id);

                    $parents[] = $data_parent->id;

                } else {

                    $data_parent = RbacMenu::find($val->parent);

                    $parents[] = $data_parent->id;

                }

               

            }



            $parent_menu = RbacMenu::where('is_deleted', 0)->whereIn('id', $parents)->orderBy('weight')->get();



            foreach ($parent_menu as $key => $obj) {

                $sub_menu = array(); 

                if ($obj->link == "#") {

                    foreach ($menu as $key => $val) {

                        if($val->parent == $obj->id) {

                            $sub_menu[] = $val;

                        }

                    }

                    $obj->link == "#";

                    $obj->submenu = $sub_menu;

                    $obj->ada_sub = 1;

                    

                } else {

                    $obj->submenu = "";

                    $obj->ada_sub = 0;

                }

            }

            



            session(['menu' => $parent_menu]);

            session()->flash('success', 'Sukses melakukan perubahan role menjadi dan dengan menu '.$user_role->nama);

        }else{

            session()->flash('error', 'Gagal melakukan perubahan role menjadi '.$user_role->nama.'. Anda tidak memiliki role tersebut.');

        }



        return redirect()->intended('/home');

    }



    public function set_active_tahun($tahun){

        $tahun = MasterTahun::where('tahun', '=', $tahun)->first();



        if(!is_null($tahun)){

            session(['id_tahun_active'=>$tahun->tahun]);

            session()->flash('success', 'Sukses melakukan perubahan tahun menjadi '.$tahun->tahun.'!');

        }else{

            session()->flash('error', 'Gagal melakukan perubahan tahun!. Tahun tidak dapat ditemukan.');

        }

        return redirect()->intended('/home');

    }





    public function send_email() {

   

        $details = [

            'title' => 'Mail from ItSolutionStuff.com',

            'body' => 'This is for testing email using smtp'

        ];

       

        \Mail::to('sriutami821@gmail.com')->send(new \App\Mail\MailPenjualanRetur($details));

       

        dd("Email is Sent.");

    }



    public function load_grafik(Request $request) {

        $data = array();

        $app = app();

        $data_ = $app->make('stdClass');

        $tahun = session('id_tahun_active');

        $currentMonth = date('m');



        /*$startMonth = date('1');

        $endtMonth = date('12');

        for($m=$endtMonth ; $m<=$currentMonth; ++$m){

            $months[$m] = strftime('%B', mktime(0, 0, 0, $m, 1));

            echo $months[$m]."<br>";

        }*/



        $label_ = array();

        $values_ = array();

        $kunjungan_ = array();

        $all_kunjungan_ = array();

        $all_ = array();

        $values_pembelian_ = array();

        $values_to_masuk_ = array();

        $values_to_keluar_ = array();

        $total_apotek = MasterApotek::where('is_deleted', 0)->count();

        $total_apotek = $total_apotek-2;



        for($i=1;$i<=$currentMonth;$i++){

            $months[$i] = strftime('%B', mktime(0, 0, 0, $i, 1));

            array_push($label_, $months[$i]);



            $rekaps = TransaksiPenjualanClosing::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_closing_nota_penjualan.*'])

                                ->where(function($query) use($request, $tahun, $i){

                                    $query->where('tb_closing_nota_penjualan.id_apotek_nota','=',session('id_apotek_active'));

                                    $query->whereYear('tb_closing_nota_penjualan.tanggal', $tahun);

                                    $query->whereMonth('tb_closing_nota_penjualan.tanggal', $i);

                                })

                                ->orderBy('tb_closing_nota_penjualan.id', 'asc')

                                ->get();



            $rekap_alls = TransaksiPenjualanClosing::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_closing_nota_penjualan.*'])

                                ->where(function($query) use($request, $tahun, $i){

                                    $query->whereYear('tb_closing_nota_penjualan.tanggal', $tahun);

                                    $query->whereMonth('tb_closing_nota_penjualan.tanggal', $i);

                                })

                                ->orderBy('tb_closing_nota_penjualan.id', 'asc')

                                ->get();



            $rekap_pembelian = TransaksiPembelianDetail::select([

                                        DB::raw('@rownum  := @rownum  + 1 AS no'), 

                                        DB::raw('CAST(SUM(tb_detail_nota_pembelian.total_harga) as decimal(20,2)) as total_pembelian')

                                ])

                                ->join('tb_nota_pembelian as a', 'a.id', '=', 'tb_detail_nota_pembelian.id_nota')

                                ->where(function($query) use($request, $tahun, $i){

                                    $query->where('a.is_deleted', 0);

                                    $query->where('tb_detail_nota_pembelian.is_deleted', 0);

                                    $query->where('a.id_apotek_nota','=',session('id_apotek_active'));

                                    $query->whereYear('a.tgl_faktur', $tahun);

                                    $query->whereMonth('a.tgl_faktur', $i);

                                })

                                ->first();



            $to_masuk = TransaksiTODetail::select([

                                        DB::raw('@rownum  := @rownum  + 1 AS no'), 

                                        DB::raw('CAST(SUM(tb_detail_nota_transfer_outlet.total) as decimal(20,2)) as total_to_masuk')

                                ])

                                ->join('tb_nota_transfer_outlet as a', 'a.id', '=', 'tb_detail_nota_transfer_outlet.id_nota')

                                ->where(function($query) use($request, $tahun, $i){

                                    $query->where('a.is_deleted', 0);

                                    $query->where('tb_detail_nota_transfer_outlet.is_deleted', 0);

                                    $query->where('a.id_apotek_tujuan','=',session('id_apotek_active'));

                                    $query->whereYear('a.tgl_nota', $tahun);

                                    $query->whereMonth('a.tgl_nota', $i);

                                })

                                ->first();



            $to_keluar = TransaksiTODetail::select([

                                        DB::raw('@rownum  := @rownum  + 1 AS no'), 

                                        DB::raw('CAST(SUM(tb_detail_nota_transfer_outlet.total) as decimal(20,2)) as total_to_keluar')

                                ])

                                ->join('tb_nota_transfer_outlet as a', 'a.id', '=', 'tb_detail_nota_transfer_outlet.id_nota')

                                ->where(function($query) use($request, $tahun, $i){

                                    $query->where('a.is_deleted', 0);

                                    $query->where('tb_detail_nota_transfer_outlet.is_deleted', 0);

                                    $query->where('a.id_apotek_nota','=',session('id_apotek_active'));

                                    $query->whereYear('a.tgl_nota', $tahun);

                                    $query->whereMonth('a.tgl_nota', $i);

                                })

                                ->first();



            $hit_penjualan = TransaksiPenjualan::where('is_deleted', 0)

                                ->where('id_apotek_nota', session('id_apotek_active'))

                                ->whereYear('tgl_nota', $tahun)

                                ->whereMonth('tgl_nota', $i)

                                ->count();



            $hit_penjualan_all = TransaksiPenjualan::where('is_deleted', 0)

                                ->whereYear('tgl_nota', $tahun)

                                ->whereMonth('tgl_nota', $i)

                                ->count();





            $total_excel=0;

            $penjualan_kredit_ = 0;

            foreach($rekaps as $rekap) {

                /*$total_1 = $rekap->jumlah_penjualan;

                if($total_1 == 0) {

                    $total_1 = $rekap->total_penjualan+$rekap->total_diskon+$rekap->total_penjualan_kredit;

                }*/



                  # 16/7/2022

                $total_1 = $rekap->total_penjualan + $rekap->total_penjualan_kredit - $rekap->total_penjualan_cn;

                $penjualan_kredit_ = $penjualan_kredit_ + $rekap->total_penjualan_cn;

               // $total_debet_x = $rekap->total_debet-$rekap->total_penjualan_cn_debet;

               // $total_cash_x = $rekap->uang_seharusnya-$rekap->total_penjualan_cn_cash;

                //$new_total = $rekap->total_akhir+$rekap->total_penjualan_kredit_terbayar;



                $new_total = $total_1;



                if($tahun == 2020) {

                    $total_excel = $total_excel+$rekap->jumlah_penjualan;

                } else {

                    $total_excel = $total_excel+$new_total;

                }

            }

            $total_excel = $total_excel;



           /* if($i==7) {

            dd($penjualan_kredit_); //17612139.6

            }*/



            $total_all=0;

            foreach($rekap_alls as $rekap) {

                /*$total_1 = $rekap->jumlah_penjualan;

                if($total_1 == 0) {

                    $total_1 = $rekap->total_penjualan+$rekap->total_diskon+$rekap->total_penjualan_kredit;

                }

                $total_3 = $total_1-$rekap->total_diskon;

                $grand_total = $total_3;



                $total_2 = $grand_total-$rekap->total_penjualan_cn;*/



                # 16/7/2022

                $total_1 = $rekap->total_penjualan + $rekap->total_penjualan_kredit - $rekap->total_penjualan_cn;

               // $total_debet_x = $rekap->total_debet-$rekap->total_penjualan_cn_debet;

               // $total_cash_x = $rekap->uang_seharusnya-$rekap->total_penjualan_cn_cash;

                //$new_total = $total_2+$rekap->total_penjualan_kredit;

                /*$new_total_x = $new_total/$total_apotek;*/



                $new_total = $total_1;

                 $new_total_x = $new_total/$total_apotek;



                if($tahun == 2020) {

                    $new_total_x = $rekap->jumlah_penjualan/$total_apotek;

                    $total_all = $total_all+$new_total_x;

                } else {

                    $total_all = $total_all+$new_total_x;

                }

            }



            $total_all = $total_all;

            $total_pembelian = $rekap_pembelian->total_pembelian;

            $total_to_masuk = $to_masuk->total_to_masuk;

            $total_to_keluar = $to_keluar->total_to_keluar;

            $total_kunjungan = $hit_penjualan;

            $total_all_kunjungan = $hit_penjualan_all/$total_apotek;

            array_push($kunjungan_, $total_kunjungan);

            array_push($values_, $total_excel);

            array_push($values_pembelian_, $total_pembelian);

            array_push($values_to_masuk_, $total_to_masuk);

            array_push($values_to_keluar_, $total_to_keluar);

            array_push($all_, $total_all);

            array_push($all_kunjungan_, $total_all_kunjungan);

        }



        //$penjualan = TransaksiPenjualanClosing::where('')

        $penjualan = $app->make('stdClass');

        $penjualan->label = $label_;//array('January', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli');

        $penjualan->values = $values_;//array(10,20,30,40,50,60,70);

        $penjualan->all = $all_;

        $data_->penjualan = $penjualan;



        $kunjungan = $app->make('stdClass');

        $kunjungan->label = $label_;

        $kunjungan->kunjungan = $kunjungan_;//array(10,20,30,40,50,60,70);

        $kunjungan->all_kunjungan = $all_kunjungan_;

        $data_->kunjungan = $kunjungan;



        $pembelian = $app->make('stdClass');

        $pembelian->values = $values_pembelian_;

        $data_->pembelian = $pembelian;





        $transfer_masuk = $app->make('stdClass');

        $transfer_masuk->values = $values_to_masuk_;

        $data_->transfer_masuk = $transfer_masuk;



        $transfer_keluar = $app->make('stdClass');

        $transfer_keluar->values = $values_to_keluar_;

        $data_->transfer_keluar = $transfer_keluar;



        return response()->json($data_);

    }



    public function resume_pareto() {
        //return view('page_not_maintenance');

        $satuans = MasterSatuan::where('is_deleted', 0)->pluck('satuan', 'id');

        $satuans->prepend('-- all --','');

        $produsens = MasterProdusen::where('is_deleted', 0)->pluck('nama', 'id');

        $produsens->prepend('-- produsen --', '');

        return view('resume_pareto')->with(compact('satuans', 'produsens'));

    }



    public function resume_pareto_load_view(Request $request) {

        ini_set('memory_limit', '-1'); 



        if($request->tanggal != "") {

            $split                      = explode("-", $request->tanggal);

            $tgl_awal       = date('Y-m-d H:i:s',strtotime($split[0]));

            $tgl_akhir      = date('Y-m-d H:i:s',strtotime($split[1]));

        } else {

            $tgl_awal = date('Y-m-d').' 00:00:00';

            $tgl_akhir = date('Y-m-d').' 00:00:00';

        }

        $limit = $request->limit;

        $apotek = MasterApotek::find(session('id_apotek_active'));

        $inisial = strtolower($apotek->nama_singkat);

        $app = app();

        $data_ = $app->make('stdClass');



        $data = '';

        $data .= '<div class="row">';



        $penjualan = TransaksiPenjualanDetail::select(

                                'tb_detail_nota_penjualan.id_obat',

                                DB::raw('SUM(tb_detail_nota_penjualan.jumlah) as jumlah_pemakaian'),

                                DB::raw('SUM((tb_detail_nota_penjualan.jumlah * tb_detail_nota_penjualan.harga_jual)-tb_detail_nota_penjualan.diskon) as omzet'),

                                DB::raw('SUM(tb_detail_nota_penjualan.jumlah * (tb_detail_nota_penjualan.harga_jual-tb_detail_nota_penjualan.hb_ppn)) as margin')

                            )

                            ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')

                            //->join('tb_m_obat as c','c.id','=','tb_detail_nota_penjualan.id_obat')

                            //->join('tb_m_stok_harga_'.$inisial.' as c', 'c.id_obat', '=', 'tb_detail_nota_penjualan.id_obat')

                            ->whereDate('b.created_at','>=', $tgl_awal)

                            ->whereDate('b.created_at','<=', $tgl_akhir)

                            ->where('b.id_apotek_nota','=',$apotek->id)

                            ->where('b.is_deleted', 0)

                            ->where('tb_detail_nota_penjualan.is_deleted', 0)

                            //->where('c.id_satuan', 'LIKE', $request->id_satuan == '' ? '%%' : $request->id_satuan)

                            ->groupBy('tb_detail_nota_penjualan.id_obat')

                            ->orderByRaw('SUM(tb_detail_nota_penjualan.jumlah) DESC')

                            ->limit($limit)

                            ->get();





        $pembelian = TransaksiPembelianDetail::select(

                                'tb_detail_nota_pembelian.id_obat',

                                DB::raw('SUM(tb_detail_nota_pembelian.jumlah) as jumlah_pemakaian')

                        )

                        ->join('tb_nota_pembelian as b','b.id','=','tb_detail_nota_pembelian.id_nota')

                        //->join('tb_m_obat as c','c.id','=','tb_detail_nota_pembelian.id_obat')

                        ->whereDate('b.tgl_faktur','>=', $tgl_awal)

                        ->whereDate('b.tgl_faktur','<=', $tgl_akhir)

                        ->where('b.id_apotek_nota','=',$apotek->id)

                        ->where('b.is_deleted', 0)

                        ->where('tb_detail_nota_pembelian.is_deleted', 0)

                        //->where('c.id_satuan', 'LIKE', $request->id_satuan == '' ? '%%' : $request->id_satuan)

                        ->groupBy('tb_detail_nota_pembelian.id_obat')

                        ->orderByRaw('SUM(tb_detail_nota_pembelian.jumlah) DESC')

                        ->limit($limit)

                        ->get();





        $transfer_masuk = TransaksiTODetail::select(

                                'tb_detail_nota_transfer_outlet.id_obat',

                                DB::raw('SUM(tb_detail_nota_transfer_outlet.jumlah) as jumlah_pemakaian')

                        )

                        ->join('tb_nota_transfer_outlet as b','b.id','=','tb_detail_nota_transfer_outlet.id_nota')

                        //->join('tb_m_obat as c','c.id','=','tb_detail_nota_transfer_outlet.id_obat')

                        ->whereDate('b.created_at','>=', $tgl_awal)

                        ->whereDate('b.created_at','<=', $tgl_akhir)

                        ->where('b.id_apotek_tujuan','=',$apotek->id)

                        ->where('b.is_deleted', 0)

                        ->where('tb_detail_nota_transfer_outlet.is_deleted', 0)

                        //->where('c.id_satuan', 'LIKE', $request->id_satuan == '' ? '%%' : $request->id_satuan)

                        ->groupBy('tb_detail_nota_transfer_outlet.id_obat')

                        ->orderByRaw('SUM(tb_detail_nota_transfer_outlet.jumlah) DESC')

                        ->limit($limit)

                        ->get();



        $transfer_keluar = TransaksiTODetail::select(

                                'tb_detail_nota_transfer_outlet.id_obat',

                                DB::raw('SUM(tb_detail_nota_transfer_outlet.jumlah) as jumlah_pemakaian')

                        )

                        ->join('tb_nota_transfer_outlet as b','b.id','=','tb_detail_nota_transfer_outlet.id_nota')

                        //->join('tb_m_obat as c','c.id','=','tb_detail_nota_transfer_outlet.id_obat')

                        ->whereDate('b.created_at','>=', $tgl_awal)

                        ->whereDate('b.created_at','<=', $tgl_akhir)

                        ->where('b.id_apotek_nota','=',$apotek->id)

                        ->where('b.is_deleted', 0)

                        ->where('tb_detail_nota_transfer_outlet.is_deleted', 0)

                        //->where('c.id_satuan', 'LIKE', $request->id_satuan == '' ? '%%' : $request->id_satuan)

                        ->groupBy('tb_detail_nota_transfer_outlet.id_obat')

                        ->orderByRaw('SUM(tb_detail_nota_transfer_outlet.jumlah) DESC')

                        ->limit($limit)

                        ->get();

                        

        /*---------------------------------PENJUALAN-------------------------------------*/

        $data .= '<div class="col-md-6">';

        $data .= '<table class="table table-bordered table-striped table-hover">

                    <thead>

                        <tr>

                            <th width="10%" colspan="14" class="text-center text-white" style="background-color:#455a64;">PENJUALAN</th>

                        </tr>

                        <tr>

                            <th width="5%" class="text-center text-white" style="background-color:#00bcd4;">No</th>

                            <th width="30%" class="text-center text-white" style="background-color:#00bcd4;">Nama Obat</th>

                            <th width="10%" class="text-center text-white" style="background-color:#00bcd4;">Penandaan Obat</th>

                            <th width="10%" class="text-center text-white" style="background-color:#00bcd4;">Produsen</th>

                            <th width="15%" class="text-center text-white" style="background-color:#00bcd4;">Jumlah</th>

                            <th width="15%" class="text-center text-white" style="background-color:#00acc1;">Omzet</th>

                            <th width="15%" class="text-center text-white" style="background-color:#00acc1;">Margin</th>

                            <th width="15%" class="text-center text-white" style="background-color:#00acc1;">Tes</th>

                        </tr>

                    </thead>

                    <tbody>';





        $i = 0;

        $total_omzet = 0;

        $total_margin = 0;

        $total_margin2 = 0;

        foreach ($penjualan as $key => $obj) {

            $i++;

            $cek_ = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $obj->id_obat)->first();

            $omzet = $obj->omzet;

            $margin = $obj->omzet - ($obj->jumlah_pemakaian*$cek_->harga_beli_ppn);

            $margin2 = $obj->margin;

            $total_omzet = $total_omzet+$omzet;

            $total_margin = $total_margin+$margin;

            $total_margin2 = $total_margin2+$margin2;

            $omzet_format = number_format($omzet,0,',',',');

            $margin_format = number_format($margin,0,',',',');

            $margin_format2 = number_format($margin2,0,',',',');

            $data.= '<tr>

                            <td class="text-center">'.$i.'</td>

                            <td class="text-left">'.$obj->obat->nama.'</td>

                            <td class="text-left">'.$obj->obat->penandaan_obat->nama.'</td>

                            <td class="text-left">'.$obj->obat->produsen->nama.'</td>

                            <td class="text-center">'.$obj->jumlah_pemakaian.'</td>

                            <td class="text-right">Rp '.$omzet_format.'</td>

                            <td class="text-right">Rp '.$margin_format.'</td>

                            <td class="text-right">Rp '.$margin_format2.'</td>

                        </tr>';

        }



        $total_omzet_format = number_format($total_omzet,0,',',',');

        $total_margin_format = number_format($total_margin,0,',',',');



        if(count($penjualan) == 0) {

            $data.= '<tr>

                            <td class="text-center" colspan="5">TIDAK ADA PENJUALAN</td>

                        </tr>';

        }



        $data .= '<tr>

                    <td class="text-left" colspan="3"><b>TOTAL</b></td>

                    <td class="text-right text-white" style="background-color:#0097a7;">Rp '.$total_omzet_format.'</td>

                    <td class="text-right text-white" style="background-color:#0097a7;">Rp '.$total_margin_format.'</td>

                </tr>';



        $data .= '</tbody></table>';

        $data .= '</div>';

        /*---------------------------------END PENJUALAN-------------------------------------*/

        /*---------------------------------PEMBELIAN-------------------------------------*/

        $data .= '<div class="col-md-6">';

        $data .= '<table class="table table-bordered table-striped table-hover">

                    <thead>

                        <tr>

                            <th width="10%" colspan="14" class="text-center text-white" style="background-color:#455a64;">PEMBELIAN</th>

                        </tr>

                        <tr>

                            <th width="5%" class="text-center text-white" style="background-color:#00bcd4;">No</th>

                            <th width="80%" class="text-center text-white" style="background-color:#00bcd4;">Nama Obat</th>

                            <th width="15%" class="text-center text-white" style="background-color:#00bcd4;">Jumlah</th>

                        </tr>

                    </thead>

                    <tbody>';

        $penjualan = array();



        

        $j = 0;

        foreach ($pembelian as $key => $obj) {

            $j++;

            $data.= '<tr>

                            <td class="text-center">'.$j.'</td>

                            <td class="text-left">'.$obj->obat->nama.'</td>

                            <td class="text-center">'.$obj->jumlah_pemakaian.'</td>

                        </tr>';

        }



        if(count($pembelian) == 0) {

            $data.= '<tr>

                            <td class="text-center" colspan="3">TIDAK ADA PEMBELIAN</td>

                        </tr>';

        }

        $data .= '</tbody></table>';

        $data .= '</div>';

        /*---------------------------------END PEMBELIAN-------------------------------------*/

        $data .= '</div>';

        $data .= '<hr>';



        /*---------------------------------TO KELUAR-------------------------------------*/

        $data .= '<div class="row">';

        $data .= '<div class="col-md-6">';

        $data .= '<table class="table table-bordered table-striped table-hover">

                    <thead>

                        <tr>

                            <th width="10%" colspan="14" class="text-center text-white" style="background-color:#455a64;">TRANSFER KELUAR</th>

                        </tr>

                        <tr>

                            <th width="5%" class="text-center text-white" style="background-color:#00bcd4;">No</th>

                            <th width="80%" class="text-center text-white" style="background-color:#00bcd4;">Nama Obat</th>

                            <th width="15%" class="text-center text-white" style="background-color:#00bcd4;">Jumlah</th>

                        </tr>

                    </thead>

                    <tbody>';

        $penjualan = array();



        

        $j = 0;

        foreach ($transfer_keluar as $key => $obj) {

            $j++;

            $data.= '<tr>

                            <td class="text-center">'.$j.'</td>

                            <td class="text-left">'.$obj->obat->nama.'</td>

                            <td class="text-center">'.$obj->jumlah_pemakaian.'</td>

                        </tr>';

        }



        if(count($transfer_keluar) == 0) {

            $data.= '<tr>

                            <td class="text-center" colspan="3">TIDAK ADA TRANSFER KELUAR</td>

                        </tr>';

        }



        $data .= '</tbody></table>';

        $data .= '</div>';

        /*---------------------------------END TO KELUAR-------------------------------------*/



        /*---------------------------------TO MASUK-------------------------------------*/

        $data .= '<div class="col-md-6">';

        $data .= '<table class="table table-bordered table-striped table-hover">

                    <thead>

                        <tr>

                            <th width="10%" colspan="14" class="text-center text-white" style="background-color:#455a64;">TRANSFER MASUK</th>

                        </tr>

                        <tr>

                            <th width="5%" class="text-center text-white" style="background-color:#00bcd4;">No</th>

                            <th width="80%" class="text-center text-white" style="background-color:#00bcd4;">Nama Obat</th>

                            <th width="15%" class="text-center text-white" style="background-color:#00bcd4;">Jumlah</th>

                        </tr>

                    </thead>

                    <tbody>';

        $penjualan = array();



        

        $j = 0;

        foreach ($transfer_masuk as $key => $obj) {

            $j++;

            $data.= '<tr>

                            <td class="text-center">'.$j.'</td>

                            <td class="text-left">'.$obj->obat->nama.'</td>

                            <td class="text-center">'.$obj->jumlah_pemakaian.'</td>

                        </tr>';

        }



        if(count($transfer_masuk) == 0) {

            $data.= '<tr>

                            <td class="text-center" colspan="3">TIDAK ADA TRANSFER MASUK</td>

                        </tr>';

        }

        $data .= '</tbody></table>';

        $data .= '</div>';

        /*---------------------------------END TO MASUK-------------------------------------*/

        $data .= '</div>';



        echo $data;

    }



    /*

        =======================================================================================

        For     : get date filter range

        Author  : Anang Bagus Prakoso

        Date    : 28/04/2023

        =======================================================================================

    */

    public function getDateFilter($id_pencarian, $tanggal) {

        $date_today = CarbonImmutable::today();

        $tgl_awal = date('Y-m-d H:i:s', strtotime($date_today));

        $tgl_akhir = date('Y-m-d H:i:s', strtotime($date_today->add(1, 'day')->subSecond()));

        if ($id_pencarian == 2) {

            $tgl_awal = date('Y-m-d H:i:s', strtotime($date_today->sub(1, 'day')));

            $tgl_akhir = date('Y-m-d H:i:s', strtotime($date_today->subSecond()));

        }

        elseif ($id_pencarian == 3) {

            $tgl_awal = date('Y-m-d H:i:s', strtotime($date_today->startOfWeek()));

        }

        elseif ($id_pencarian == 4) {

            $tgl_awal = date('Y-m-d H:i:s', strtotime($date_today->startOfWeek()->sub(1, 'week')));

            $tgl_akhir = date('Y-m-d H:i:s', strtotime($date_today->endOfWeek()->sub(1, 'week')));

        }

        elseif ($id_pencarian == 5) {

            $tgl_awal = date('Y-m-d H:i:s', strtotime($date_today->startOfMonth()));

            $tgl_akhir = date('Y-m-d H:i:s', strtotime($date_today->endOfMonth()));

        }

        elseif ($id_pencarian == 6) {

            $tgl_awal = date('Y-m-d H:i:s', strtotime($date_today->startOfMonth()->sub(1, 'day')->startOfMonth()));

            $tgl_akhir = date('Y-m-d H:i:s', strtotime($date_today->startOfMonth()->subSecond()));

        }

        elseif ($id_pencarian == 7) {

            $tgl_awal = date('Y-m-d H:i:s', strtotime($date_today->startOfMonth()->sub(1, 'day')->startOfMonth()->sub(1, 'day')->startOfMonth()->sub(1, 'day')->startOfMonth()));

            $tgl_akhir = date('Y-m-d H:i:s', strtotime($date_today->startOfMonth()->subSecond()));

        }

        elseif ($id_pencarian == 8) {

            $tgl_awal = date('Y-m-d H:i:s', strtotime($date_today->startOfMonth()->sub(1, 'day')->startOfMonth()->sub(1, 'day')->startOfMonth()->sub(1, 'day')->startOfMonth()->sub(1, 'day')->startOfMonth()->sub(1, 'day')->startOfMonth()->sub(1, 'day')->startOfMonth()));

            $tgl_akhir = date('Y-m-d H:i:s', strtotime($date_today->startOfMonth()->subSecond()));

        }

        elseif ($id_pencarian == 9) {

            if($tanggal != "") {

                $split = explode("-", $tanggal);

                $tgl_awal       = date('Y-m-d H:i:s',strtotime($split[0]));

                $tgl_akhir      = date('Y-m-d H:i:s',strtotime($split[1]));

            } else {

                $tgl_awal = date('Y-m-d').' 00:00:00';

                $tgl_akhir = date('Y-m-d').' 23:59:59';

            }

        }



        return array($tgl_awal, $tgl_akhir);

    }





    /*

        =======================================================================================

        For     : get amount of each pareto's

        Author  : Anang Bagus Prakoso

        Date    : 02/06/2023

        =======================================================================================

    */

    public function getAmountProduct(Request $request) {
        list($tgl_awal, $tgl_akhir) = HomeController::getDateFilter($request->id_pencarian, $request->tanggal);
        
        $apotek = MasterApotek::find(session('id_apotek_active'));
        if($request->id_pencarian == 9) {
            $cached_resume = Cache::get('resume_pareto_'.$request->id_pencarian.'_'.$tgl_awal.'_'.$tgl_akhir.'_resume_'.$apotek->id);
        } elseif ($request->id_pencarian != 1) {
            $cached_resume = Cache::get('resume_pareto_'.$request->id_pencarian.'_resume_'.$apotek->id);
        } else {
            $cached_resume = Cache::get('resume_pareto_'.$request->id_pencarian.'_'.Auth::user()->id.'_resume_'.$apotek->id);
        }
        if($cached_resume == null){
            if($request->id_pencarian == 9) {
                $data = Cache::get('resume_pareto_'.$request->id_pencarian.'_'.$tgl_awal.'_'.$tgl_akhir.'_list_data_'.$apotek->id);
            } elseif ($request->id_pencarian != 1) {
                $data = Cache::get('resume_pareto_'.$request->id_pencarian.'_list_data_'.$apotek->id);
            } else {
                $data = Cache::get('resume_pareto_'.$request->id_pencarian.'_'.Auth::user()->id.'_list_data_'.$apotek->id);
            }
    
            $pareto_a = 0;
            $pareto_b = 0;
            $pareto_c = 0;

            foreach ($data as $obat) {
                switch ($obat['klasifikasi_pareto']) {
                    case 1:
                        $pareto_a++;
                        break;
                    case 2:
                        $pareto_b++;
                        break;
                    case 3:
                        $pareto_c++;
                        break;
                }
            }

            $total = count($data);

            $datas = (object) [
                'pareto_a' => $pareto_a,
                'pareto_b' => $pareto_b,
                'pareto_c' => $pareto_c,
                'total' => $total,
                'tgl_awal' => $tgl_awal,
                'tgl_akhir' => $tgl_akhir,
            ];

            if($request->id_pencarian == 9) {
                Cache::forget('resume_pareto_'.$request->id_pencarian.'_'.$tgl_awal.'_'.$tgl_akhir.'_resume_'.$apotek->id);
                Cache::put('resume_pareto_'.$request->id_pencarian.'_'.$tgl_awal.'_'.$tgl_akhir.'_resume_'.$apotek->id, $datas, self::$expiredAt);
            } elseif ($request->id_pencarian != 1) {
                Cache::forget('resume_pareto_'.$request->id_pencarian.'_resume_'.$apotek->id);
                Cache::put('resume_pareto_'.$request->id_pencarian.'_resume_'.$apotek->id, $datas, self::$expiredAt);
            } else {
                Cache::forget('resume_pareto_'.$request->id_pencarian.'_'.Auth::user()->id.'_resume_'.$apotek->id);
                Cache::put('resume_pareto_'.$request->id_pencarian.'_'.Auth::user()->id.'_resume_'.$apotek->id, $datas, self::$expiredAt);
            }
        }
        else{
            $datas = $cached_resume;
        }

        return $datas;
    }



    public function list_pareto_penjualan(Request $request)

    {
        set_time_limit(120);
        ini_set('memory_limit', '-1'); 
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        list($tgl_awal, $tgl_akhir) = HomeController::getDateFilter($request->id_pencarian, $request->tanggal);
        
        $limit = $request->limit;
        $id_apotek = $request->id_apotek ?? session('id_apotek_active');
        $apotek = MasterApotek::find($id_apotek);
        $inisial = strtolower($apotek->nama_singkat);

        if($request->id_pencarian == 9) {
            $cached_data = Cache::get('resume_pareto_'.$request->id_pencarian.'_'.$tgl_awal.'_'.$tgl_akhir.'_list_data_'.$apotek->id);
        } elseif ($request->id_pencarian != 1) {
            $cached_data = Cache::get('resume_pareto_'.$request->id_pencarian.'_list_data_'.$apotek->id);
        } else {
            $cached_data = Cache::get('resume_pareto_'.$request->id_pencarian.'_'.Auth::user()->id.'_list_data_'.$apotek->id);
        }
        if($cached_data != null){
            if ($request->nilai_pareto != null or $request->nama != null) {
                $nama = $request->nama;
                $nilai_pareto = $request->nilai_pareto;
                $collection = $cached_data->filter(function ($obat) use ($nilai_pareto, $nama) {
                    $result = true;
                    if (!is_null($nilai_pareto)) {
                        if ($obat->klasifikasi_pareto != $nilai_pareto) {
                            $result = false;
                        }
                    }
                    if (!is_null($nama)) {
                        if (stripos($obat->nama, $nama) === false) {
                            $result = false;
                        }
                    }
                    return $result;
                });
            }
            else{
                $collection = $cached_data;
            }

            foreach ($collection as $item) {
                $stok_akhir = DB::table('tb_m_stok_harga_'.$inisial)
                    ->select(
                        'id_obat',
                        'stok_akhir',
                    )
                    ->where(function($query) use ($item) {
                        $query->where('is_deleted', '=', 0);
                        $query->where('id_obat', '=', $item->id_obat);
                    })
                    ->first();

                $item->stok_akhir = $stok_akhir->stok_akhir;
            }

            $sorted = $collection->sortBy(function ($item) use ($order_column) {
                return $item->$order_column;
            }, SORT_REGULAR, $order_dir == 'asc' ? false : true);

            $total_data = $collection->count();

            $data = $sorted->slice(0, $limit);
        }
        else{
            $total_penjualan = DB::table('tb_detail_nota_penjualan AS a')
                ->select(DB::raw('SUM(a.jumlah * a.harga_jual) AS total'))
                ->join('tb_nota_penjualan AS b', 'b.id', '=', 'a.id_nota')
                ->where('b.created_at', '>=', $tgl_awal)
                ->where('b.created_at', '<=', $tgl_akhir)
                ->where('b.id_apotek_nota', '=', $apotek->id)
                ->where('b.is_deleted', '=', 0)
                ->where('a.is_deleted', '=', 0);
            
            $total_keuntungan = DB::table('tb_detail_nota_penjualan AS a')
                ->select(DB::raw('(SUM(a.jumlah * a.harga_jual) - SUM(a.jumlah * a.hb_ppn)) AS total'))
                ->join('tb_nota_penjualan AS b', 'b.id', '=', 'a.id_nota')
                ->where('b.created_at', '>=', $tgl_awal)
                ->where('b.created_at', '<=', $tgl_akhir)
                ->where('b.id_apotek_nota', '=', $apotek->id)
                ->where('b.is_deleted', '=', 0)
                ->where('a.is_deleted', '=', 0);
            
            $sub_penjualan = clone $total_penjualan;
            $sub_penjualan = $sub_penjualan->select('a.id_obat', DB::raw('SUM(a.jumlah * a.harga_jual) AS total'))
                ->groupBy('a.id_obat');
                
            $sub_keuntungan = DB::table('tb_detail_nota_penjualan AS a')
            ->select(
                'a.id_obat',
                DB::raw('(sub_penjualan.total - SUM(a.jumlah * a.hb_ppn)) AS sub_keuntungan'),
                DB::raw('sub_penjualan.total AS sub_penjualan'),
            )
            ->join('tb_nota_penjualan AS b', 'b.id', '=', 'a.id_nota')
            ->join(DB::raw("({$sub_penjualan->toSql()}) AS sub_penjualan"), 'sub_penjualan.id_obat', '=', 'a.id_obat')
            ->mergeBindings($sub_penjualan)
            ->where('b.created_at', '>=', $tgl_awal)
            ->where('b.created_at', '<=', $tgl_akhir)
            ->where('b.id_apotek_nota', '=', $apotek->id)
            ->where('b.is_deleted', '=', 0)
            ->where('a.is_deleted', '=', 0)
            ->groupBy('a.id_obat');
            
            $subquery = TransaksiPenjualanDetail::select(
                DB::raw('(@rownum := @rownum + 1) AS no'),
                'tb_detail_nota_penjualan.id_obat',
                'c.nama',
                'c.stok_akhir',
                'd.id_satuan',
                'd.id_produsen',
                DB::raw('SUM(tb_detail_nota_penjualan.jumlah) AS jumlah_penjualan'),
                DB::raw('sub_query.sub_penjualan AS penjualan'),
                DB::raw('TRUNCATE(((sub_query.sub_penjualan / total_penjualan.total) * 100), 6) AS persentase_penjualan'),
                DB::raw('sub_query.sub_keuntungan AS keuntungan'),
                DB::raw('TRUNCATE(((sub_query.sub_keuntungan / total_keuntungan.total) * 100), 6) AS persentase_keuntungan'),
                DB::raw('CASE WHEN TRUNCATE(SUM((sub_query.sub_penjualan / total_penjualan.total) * 100) OVER (ORDER BY (sub_query.sub_penjualan / total_penjualan.total), tb_detail_nota_penjualan.id_obat desc), 6) >= 20 THEN 1 WHEN TRUNCATE(SUM((sub_query.sub_penjualan / total_penjualan.total) * 100) OVER (ORDER BY (sub_query.sub_penjualan / total_penjualan.total), tb_detail_nota_penjualan.id_obat desc), 6) >= 5 THEN 2 WHEN TRUNCATE(SUM((sub_query.sub_penjualan / total_penjualan.total) * 100) OVER (ORDER BY (sub_query.sub_penjualan / total_penjualan.total), tb_detail_nota_penjualan.id_obat desc), 6) >= 0 THEN 3 ELSE 4 END AS klasifikasi_penjualan'),
                DB::raw('CASE WHEN TRUNCATE((SUM(((sub_query.sub_keuntungan / total_keuntungan.total) * 100)) OVER (ORDER BY ((sub_query.sub_keuntungan / total_keuntungan.total)), tb_detail_nota_penjualan.id_obat asc)), 6) >= 20 THEN 1 WHEN TRUNCATE((SUM(((sub_query.sub_keuntungan / total_keuntungan.total) * 100)) OVER (ORDER BY ((sub_query.sub_keuntungan / total_keuntungan.total)), tb_detail_nota_penjualan.id_obat asc)), 6) >= 5 THEN 2 WHEN TRUNCATE((SUM(((sub_query.sub_keuntungan / total_keuntungan.total) * 100)) OVER (ORDER BY ((sub_query.sub_keuntungan / total_keuntungan.total)), tb_detail_nota_penjualan.id_obat asc)), 6) >= 0 THEN 3 ELSE 4 END AS klasifikasi_keuntungan'),
                DB::raw('CASE WHEN (TRUNCATE(SUM((sub_query.sub_penjualan / total_penjualan.total) * 100) OVER (ORDER BY (sub_query.sub_penjualan / total_penjualan.total), tb_detail_nota_penjualan.id_obat desc), 6) >= 20) AND (TRUNCATE((SUM(((sub_query.sub_keuntungan / total_keuntungan.total) * 100)) OVER (ORDER BY ((sub_query.sub_keuntungan / total_keuntungan.total)), tb_detail_nota_penjualan.id_obat asc)), 6) >= 5) THEN 1 WHEN (TRUNCATE(SUM((sub_query.sub_penjualan / total_penjualan.total) * 100) OVER ( ORDER BY (sub_query.sub_penjualan / total_penjualan.total), tb_detail_nota_penjualan.id_obat desc), 6) >= 20) AND (TRUNCATE((SUM(((sub_query.sub_keuntungan / total_keuntungan.total) * 100)) OVER (ORDER BY ((sub_query.sub_keuntungan / total_keuntungan.total)), tb_detail_nota_penjualan.id_obat asc)), 6) < 5) THEN 2 WHEN (TRUNCATE(SUM((sub_query.sub_penjualan / total_penjualan.total) * 100) OVER (ORDER BY (sub_query.sub_penjualan / total_penjualan.total), tb_detail_nota_penjualan.id_obat desc), 6) >= 5) AND (TRUNCATE((SUM(((sub_query.sub_keuntungan / total_keuntungan.total) * 100)) OVER (ORDER BY ((sub_query.sub_keuntungan / total_keuntungan.total)), tb_detail_nota_penjualan.id_obat asc)), 6) >= 5) THEN 2 WHEN (TRUNCATE(SUM((sub_query.sub_penjualan / total_penjualan.total) * 100) OVER (ORDER BY (sub_query.sub_penjualan / total_penjualan.total), tb_detail_nota_penjualan.id_obat desc), 6) < 5) AND (TRUNCATE((SUM(((sub_query.sub_keuntungan / total_keuntungan.total) * 100)) OVER (ORDER BY ((sub_query.sub_keuntungan / total_keuntungan.total)), tb_detail_nota_penjualan.id_obat asc)), 6) >= 20) THEN 2 ELSE 3 END AS klasifikasi_pareto'),
            )
            ->join('tb_nota_penjualan AS b','b.id','=','tb_detail_nota_penjualan.id_nota')
            ->leftjoin('tb_m_stok_harga_'.$inisial.' AS c', 'c.id_obat', '=', 'tb_detail_nota_penjualan.id_obat')
            ->join('tb_m_obat AS d','d.id','=','tb_detail_nota_penjualan.id_obat')
            ->join(DB::raw("({$total_penjualan->toSql()}) AS total_penjualan"), DB::raw('1'), '=', DB::raw('1'))
            ->mergeBindings($total_penjualan)
            ->join(DB::raw("({$total_keuntungan->toSql()}) AS total_keuntungan"), DB::raw('1'), '=', DB::raw('1'))
            ->mergeBindings($total_keuntungan)
            ->join(DB::raw("({$sub_keuntungan->toSql()}) AS sub_query"), 'sub_query.id_obat', '=', 'tb_detail_nota_penjualan.id_obat')
            ->mergeBindings($sub_keuntungan)
            ->where('b.created_at', '>=', $tgl_awal)
            ->where('b.created_at', '<=', $tgl_akhir)
            ->where('b.id_apotek_nota', '=', $apotek->id)
            ->where('b.is_deleted', '=', 0)
            ->where('tb_detail_nota_penjualan.is_deleted', '=', 0)
            ->groupBy('tb_detail_nota_penjualan.id_obat');
    
            DB::statement(DB::raw('set @rownum = 0'));
            $data =  TransaksiPenjualanDetail::select(
                DB::raw('*')
            )
            ->from(
                DB::raw("({$subquery->toSql()}) AS subquery")
            )
            ->mergeBindings($subquery->getQuery())
            ->groupBy('subquery.id_obat');

            $cloned_data = clone $data;
            $cloned_data = $cloned_data->get();
            
            if($request->id_pencarian == 9) {
                Cache::forget('resume_pareto_'.$request->id_pencarian.'_'.$tgl_awal.'_'.$tgl_akhir.'_list_data_'.$apotek->id);
                Cache::put('resume_pareto_'.$request->id_pencarian.'_'.$tgl_awal.'_'.$tgl_akhir.'_list_data_'.$apotek->id, $cloned_data, self::$expiredAt);
            } elseif ($request->id_pencarian != 1) {
                Cache::forget('resume_pareto_'.$request->id_pencarian.'_list_data_'.$apotek->id);
                Cache::put('resume_pareto_'.$request->id_pencarian.'_list_data_'.$apotek->id, $cloned_data, self::$expiredAt);
            } else {
                Cache::forget('resume_pareto_'.$request->id_pencarian.'_'.Auth::user()->id.'_list_data_'.$apotek->id);
                Cache::put('resume_pareto_'.$request->id_pencarian.'_'.Auth::user()->id.'_list_data_'.$apotek->id, $cloned_data, self::$expiredAt);
            }

            $total_data = $cloned_data->count();
            
            $data = $data->limit($limit);
        }
        
        $datatables = Datatables::of($data);
        if($request->id_apotek) {
            return $datatables
            ->editcolumn('id_penandaan_obat', function($data) {
                return $data->obat->penandaan_obat->nama;
            })
            ->make(true);
        }
        return $datatables
        ->editcolumn('id_penandaan_obat', function($data) {
            return $data->obat->penandaan_obat->nama;
        })
        ->editcolumn('id_satuan', function($data) {
            return $data->obat->satuan->satuan;
        })
        ->editcolumn('id_produsen', function($data) {
            return $data->obat->produsen->nama;
        })
        ->editcolumn('penjualan', function($data) {
            $penjualan_format = 'Rp. '.number_format($data->penjualan,0,'.',',');
            return $penjualan_format;
        })
        ->editcolumn('persentase_penjualan', function($data) {
            $persentase_format = number_format($data->persentase_penjualan,2,',','.').' %';
            return $persentase_format;
        })
        ->editcolumn('keuntungan', function($data) {
            $keuntungan_format = 'Rp. '.number_format($data->keuntungan,0,'.',',');
            return $keuntungan_format;
        })
        ->editcolumn('persentase_keuntungan', function($data) {
            $persentase_format = number_format($data->persentase_keuntungan,2,',','.').' %';
            return $persentase_format;
        })
        ->editcolumn('sedang_dipesan', function($data) use($request, $apotek){
            $cek = DefectaOutlet::where('is_deleted', 0)
                        ->where('id_obat', $data->id_obat)
                        ->where('id_apotek', session('id_apotek_active'))
                        ->where('is_deleted', 0)
                        ->whereIn('id_status', [1,2])
                        ->whereIn('id_process', [0,1])
                        ->first();

            $status = '<span class="label label-danger" data-toggle="tooltip" data-placement="top" title="Belum masuk defecta" style="font-size:8pt;color:#e91e63;"><i class="fa fa-window-close" style="font-size:24px" aria-hidden="true"></i></span>';
            if(!empty($cek)) {
                $status = '<span class="label label-success" data-toggle="tooltip" data-placement="top" title="Sudah masuk defecta" style="font-size:8pt;color:#009688;"><i class="fa fa-check-circle" style="font-size:24px" aria-hidden="true"></i></span>';
            } 

            return $status; 
        }) 
        ->addcolumn('action', function($data) {

            $btn = '<span class="btn btn-info btn-sm" onClick="add_keranjang('.$data->id_obat.', 0, '.$data->jumlah_penjualan.', '.$data->keuntungan.')" data-toggle="tooltip" data-placement="top" title="Add to Defecta">[order]</span>';

            $btn .= '<span class="btn btn-info btn-sm" onClick="add_keranjang_transfer('.$data->id_obat.', 0, '.$data->jumlah_penjualan.', '.$data->keuntungan.')" data-toggle="tooltip" data-placement="top" title="Add to Defecta">[transfer]</span>';

            return $btn;
        })
        ->rawColumns(['sedang_dipesan', 'action'])
        ->addIndexColumn()
        ->setTotalRecords($total_data)
        ->make(true);
    }



    public function list_pareto_pembelian(Request $request)

    {

        $order = $request->get('order');

        $columns = $request->get('columns');

        $order_column = $columns[$order[0]['column']]['data'];

        $order_dir = $order[0]['dir'];



        list($tgl_awal, $tgl_akhir) = HomeController::getDateFilter($request->id_pencarian, $request->tanggal);



        $limit = $request->limit;

        $apotek = MasterApotek::find(session('id_apotek_active'));

        $inisial = strtolower($apotek->nama_singkat);



        $q1 = DB::table('tb_detail_nota_pembelian as a')

            ->select(

                DB::raw('max(b.tgl_faktur) as tgl_faktur'),

                DB::raw('sum(a.jumlah) as jumlah_total'),

                'a.id_obat'

            )

            ->join('tb_nota_pembelian as b', 'b.id', '=', 'a.id_nota')

            ->where('b.tgl_faktur', '>=', $tgl_awal)

            ->where('b.tgl_faktur', '<=', $tgl_akhir)

            ->where('b.id_apotek_nota', '=', $apotek->id)

            ->where('a.is_deleted', '=', 0)

            ->where('b.is_deleted', '=', 0)

            ->groupBy('a.id_obat');



        $q2 = DB::table('tb_detail_nota_pembelian as a')

            ->select(

                'b.tgl_faktur',

                'b.id_suplier',

                'a.id_obat'

            )

            ->join('tb_nota_pembelian as b', 'b.id', '=', 'a.id_nota')

            ->where('b.tgl_faktur', '>=', $tgl_awal)

            ->where('b.tgl_faktur', '<=', $tgl_akhir)

            ->where('b.id_apotek_nota', '=', $apotek->id)

            ->where('a.is_deleted', '=', 0)

            ->where('b.is_deleted', '=', 0);



        DB::statement(DB::raw('set @rownum = 0'));

        $data = DB::table(function ($subquery) use ($q1, $q2) {

            $subquery->select(DB::raw('@rownum  := @rownum  + 1 AS no'), 'a.*', 'b.jumlah_total')

                ->from(DB::raw("({$q2->toSql()}) as a"))

                ->mergeBindings($q2)

                ->join(DB::raw("({$q1->toSql()}) as b"), function ($join) {

                    $join->on('b.id_obat', '=', 'a.id_obat')

                    ->on('b.tgl_faktur', '=', 'a.tgl_faktur');

                })

                ->mergeBindings($q1);

        }, 'q')

            ->join('tb_m_obat as c', 'c.id', '=', 'q.id_obat')

            ->join('tb_m_suplier as d', 'd.id', '=', 'q.id_suplier')

            ->join('tb_m_penandaan_obat as e', 'e.id', '=', 'c.id_penandaan_obat')

            ->join('tb_m_satuan as f', 'f.id', '=', 'c.id_satuan')

            ->join('tb_m_produsen as g', 'g.id', '=', 'c.id_produsen')

            ->select('q.*', 'c.nama as nama_obat', 'd.nama as suplier', 'e.nama as nama_penandaan', 'f.satuan', 'g.nama as nama_produsen')

            ->where('c.id_satuan', 'LIKE', $request->id_satuan == '' ? '%%' : $request->id_satuan)

            ->where('c.id_produsen', 'LIKE', $request->id_produsen == '' ? '%%' : $request->id_produsen)

            ->where('c.nama', 'LIKE', $request->nama == '' ? '%%' : '%'.$request->nama.'%');



        if($order_column == "jumlah_total" || $order_column == "" || $order_column == "no") {

            $data = $data->orderByRaw('q.jumlah_total '.$order_dir.', nama_obat '.$order_dir);

        }



        $data = $data->limit($limit);



      

        $datatables = Datatables::of($data);

        return $datatables  

        /*->filter(function($query) use($request,$order_column,$order_dir){

            $query->where(function($query) use($request){

                $query->orwhere('agama','LIKE','%'.$request->get('search')['value'].'%');

            });

        })  */

        ->editcolumn('nama_obat', function($data) {

            return $data->nama_obat;

        })

        ->editcolumn('id_penandaan_obat', function($data) {

            return $data->nama_penandaan;

        })

        ->editcolumn('id_satuan', function($data) {

            return $data->satuan;

        })

        ->editcolumn('id_produsen', function($data) {

            return $data->nama_produsen;

        })

        ->editcolumn('id_suplier', function($data) {

            return $data->suplier;

        })

        ->addIndexColumn()

        ->make(true);  

    }



    public function list_pareto_transfer_masuk(Request $request)

    {

        $order = $request->get('order');

        $columns = $request->get('columns');

        $order_column = $columns[$order[0]['column']]['data'];

        $order_dir = $order[0]['dir'];



        list($tgl_awal, $tgl_akhir) = HomeController::getDateFilter($request->id_pencarian, $request->tanggal);



        $limit = $request->limit;

        $apotek = MasterApotek::find(session('id_apotek_active'));

        $inisial = strtolower($apotek->nama_singkat);



        DB::statement(DB::raw('set @rownum = 0'));

        $data =  TransaksiTODetail::select(

                                DB::raw('@rownum  := @rownum  + 1 AS no'),

                                'tb_detail_nota_transfer_outlet.id_obat',

                                'c.nama',

                                DB::raw('SUM(tb_detail_nota_transfer_outlet.jumlah) as jumlah_pemakaian')

                        )

                        ->join('tb_nota_transfer_outlet as b','b.id','=','tb_detail_nota_transfer_outlet.id_nota')

                        ->leftjoin('tb_m_stok_harga_'.$inisial.' as c', 'c.id_obat', '=', 'tb_detail_nota_transfer_outlet.id_obat')

                        ->join('tb_m_obat as d','d.id','=','tb_detail_nota_transfer_outlet.id_obat')

                        ->whereDate('b.created_at','>=', $tgl_awal)

                        ->whereDate('b.created_at','<=', $tgl_akhir)

                        ->where('b.id_apotek_tujuan','=',$apotek->id)

                        ->where('b.is_deleted', 0)

                        ->where('tb_detail_nota_transfer_outlet.is_deleted', 0)

                        ->where('d.id_satuan', 'LIKE', $request->id_satuan == '' ? '%%' : $request->id_satuan)

                        ->where('d.id_produsen', 'LIKE', $request->id_produsen == '' ? '%%' : $request->id_produsen)

                        ->where('d.nama', 'LIKE', $request->nama == '' ? '%%' : '%'.$request->nama.'%')

                        ->groupBy('tb_detail_nota_transfer_outlet.id_obat');



        if($order_column == "jumlah_pemakaian" || $order_column == "" || $order_column == "no") {

            $data = $data->orderByRaw('SUM(tb_detail_nota_transfer_outlet.jumlah) '.$order_dir.', d.nama '.$order_dir);

        } 



        $data = $data->limit($limit);



      

        $datatables = Datatables::of($data);

        return $datatables  

        /*->filter(function($query) use($request,$order_column,$order_dir){

            $query->where(function($query) use($request){

                $query->orwhere('agama','LIKE','%'.$request->get('search')['value'].'%');

            });

        })  */

        ->editcolumn('nama', function($data) {

            return $data->obat->nama;

        })

        ->editcolumn('id_penandaan_obat', function($data) {

            return $data->obat->penandaan_obat->nama;

        })

        ->editcolumn('id_satuan', function($data) {

            return $data->obat->satuan->satuan;

        })

        ->editcolumn('id_produsen', function($data) {

            return $data->obat->produsen->nama;

        })

        ->addIndexColumn()

        ->make(true);  

    }



    public function list_pareto_transfer_keluar(Request $request)

    {

        $order = $request->get('order');

        $columns = $request->get('columns');

        $order_column = $columns[$order[0]['column']]['data'];

        $order_dir = $order[0]['dir'];



        list($tgl_awal, $tgl_akhir) = HomeController::getDateFilter($request->id_pencarian, $request->tanggal);



        $limit = $request->limit;

        $apotek = MasterApotek::find(session('id_apotek_active'));

        $inisial = strtolower($apotek->nama_singkat);



        DB::statement(DB::raw('set @rownum = 0'));

        $data =  TransaksiTODetail::select(

                                DB::raw('@rownum  := @rownum  + 1 AS no'),

                                'tb_detail_nota_transfer_outlet.id_obat',

                                'c.nama',

                                DB::raw('SUM(tb_detail_nota_transfer_outlet.jumlah) as jumlah_pemakaian')

                        )

                        ->join('tb_nota_transfer_outlet as b','b.id','=','tb_detail_nota_transfer_outlet.id_nota')

                        ->leftjoin('tb_m_stok_harga_'.$inisial.' as c', 'c.id_obat', '=', 'tb_detail_nota_transfer_outlet.id_obat')

                        ->join('tb_m_obat as d','d.id','=','tb_detail_nota_transfer_outlet.id_obat')

                        ->whereDate('b.created_at','>=', $tgl_awal)

                        ->whereDate('b.created_at','<=', $tgl_akhir)

                        ->where('b.id_apotek_nota','=',$apotek->id)

                        ->where('b.is_deleted', 0)

                        ->where('tb_detail_nota_transfer_outlet.is_deleted', 0)

                        ->where('d.id_satuan', 'LIKE', $request->id_satuan == '' ? '%%' : $request->id_satuan)

                        ->where('d.id_produsen', 'LIKE', $request->id_produsen == '' ? '%%' : $request->id_produsen)

                        ->where('d.nama', 'LIKE', $request->nama == '' ? '%%' : '%'.$request->nama.'%')

                        ->groupBy('tb_detail_nota_transfer_outlet.id_obat');



        if($order_column == "jumlah_pemakaian" || $order_column == "" || $order_column == "no") {

            $data = $data->orderByRaw('SUM(tb_detail_nota_transfer_outlet.jumlah) '.$order_dir.', d.nama '.$order_dir);

        } 



        $data = $data->limit($limit);

      

        $datatables = Datatables::of($data);

        return $datatables  

        /*->filter(function($query) use($request,$order_column,$order_dir){

            $query->where(function($query) use($request){

                $query->orwhere('agama','LIKE','%'.$request->get('search')['value'].'%');

            });

        })  */

        ->editcolumn('nama', function($data) {

            return $data->obat->nama;

        })

        ->editcolumn('id_penandaan_obat', function($data) {

            return $data->obat->penandaan_obat->nama;

        })

        ->editcolumn('id_satuan', function($data) {

            return $data->obat->satuan->satuan;

        })

        ->editcolumn('id_produsen', function($data) {

            return $data->obat->produsen->nama;

        })

        ->addIndexColumn()

        ->make(true);  

    }

    public function clear_cache() {
        ini_set('memory_limit', '-1');
        $super_admin = session('super_admin');
        $all_apotek = MasterApotek::where(function($query) use($super_admin){
            $query->where('tb_m_apotek.is_deleted', '=', '0');
            if($super_admin == 0) {
                $query->where('tb_m_apotek.id_group_apotek', Auth::user()->id_group_apotek);
            }
        });
        try {
            foreach ($all_apotek as $apotek) {
                Cache::forget('resume_pareto_1_'.Auth::user()->id.'_resume_'.$apotek->id);
                Cache::forget('resume_pareto_1_'.Auth::user()->id.'_list_data_'.$apotek->id);
            }

            return response()->json(['message' => 'success clear cache']);
        } catch (Exception $e) {
            return response()->json(['error' => $e], 500);
        }
    }

    public function export(Request $request){
        $id_pencarian = $request->id_pencarian;
        $tanggal = $request->tanggal;
        return view('export')->with(compact('id_pencarian', 'tanggal'));
    }

    public function export_pareto(Request $request) 

    {
        //set_time_limit(120);
        //ini_set('memory_limit', '-1'); 

        list($tgl_awal, $tgl_akhir) = HomeController::getDateFilter($request->id_pencarian, $request->tanggal);
        
        $id_apotek = session('id_apotek_active');
        $apotek = MasterApotek::find($id_apotek);
        $inisial = strtolower($apotek->nama_singkat);
        $now = date('YmdHis');

        if($request->id_pencarian == 9) {
            $data = Cache::get('resume_pareto_'.$request->id_pencarian.'_'.$tgl_awal.'_'.$tgl_akhir.'_list_data_'.$apotek->id);
        } elseif ($request->id_pencarian != 1) {
            $data = Cache::get('resume_pareto_'.$request->id_pencarian.'_list_data_'.$apotek->id);
        } else {
            $data = Cache::get('resume_pareto_'.$request->id_pencarian.'_'.Auth::user()->id.'_list_data_'.$apotek->id);
        }

        return (new ParetoExport($data, $inisial, $tgl_awal, $tgl_akhir))->download('Pareto_'.$inisial.'_'.$now.'.xlsx');
    }

    public function export_pembelian(Request $request)
    {
        //set_time_limit(120);
        //ini_set('memory_limit', '-1'); 

        list($tgl_awal, $tgl_akhir) = HomeController::getDateFilter($request->id_pencarian, $request->tanggal);
        
        $limit = $request->limit;
        $id_apotek = session('id_apotek_active');
        $apotek = MasterApotek::find($id_apotek);
        $inisial = strtolower($apotek->nama_singkat);
        $now = date('YmdHis');
        
        return (new PembelianExport($request->id_pencarian, $tgl_awal, $tgl_akhir, $apotek->id, $limit, $inisial))->download('Resume_pembelian_'.$inisial.'_'.$now.'.xlsx');
    }



    public function export_pareto_all(Request $request) 

    {
        //set_time_limit(120);
        ///ini_set('memory_limit', '-1'); 

        list($tgl_awal, $tgl_akhir) = HomeController::getDateFilter($request->id_pencarian, $request->tanggal);
        
        $now = date('YmdHis');
        $apoteks = MasterApotek::select(
            'id',
            'nama_singkat'
        )
        ->whereRaw('is_deleted = 0')
        ->get();

        $zip = new ZipArchive();
        $zipFileName = 'Pareto_All_Outlet_'.$now.'.zip';

        if ($zip->open($zipFileName, ZipArchive::CREATE) === true) {
            foreach($apoteks as $apotek){
                if($request->id_pencarian == 9) {
                    $data = Cache::get('resume_pareto_'.$request->id_pencarian.'_'.$tgl_awal.'_'.$tgl_akhir.'_list_data_'.$apotek->id);
                } elseif ($request->id_pencarian != 1) {
                    $data = Cache::get('resume_pareto_'.$request->id_pencarian.'_list_data_'.$apotek->id);
                } else {
                    $data = Cache::get('resume_pareto_'.$request->id_pencarian.'_'.Auth::user()->id.'_list_data_'.$apotek->id);
                }
                $export = new ParetoExport($data, strtolower($apotek->nama_singkat), $tgl_awal, $tgl_akhir);
                $fileName = 'Pareto_'.strtolower($apotek->nama_singkat).'_'.$now.'.xlsx';
                $path = Excel::store($export, $fileName);
                $zip->addFile(storage_path('app/'.$fileName), $fileName);
            }
            
            $zip->close();
            
            foreach($apoteks as $apotek){
                $fileName = 'Pareto_'.strtolower($apotek->nama_singkat).'_'.$now.'.xlsx';
                unlink(storage_path('app/'.$fileName));
            }

            return response()->download($zipFileName)->deleteFileAfterSend();
        } else {
            return 'Failed to create ZIP file.';
        }
    }



    public function export_rekap_data(Request $request) 

    {

        ini_set('memory_limit', '-1'); 



        list($tgl_awal, $tgl_akhir) = HomeController::getDateFilter($request->id_pencarian, $request->tanggal);



        $limit = $request->limit;

        $id_apotek = session('id_apotek_active');

        $apotek = MasterApotek::find($id_apotek);

        $inisial = strtolower($apotek->nama_singkat);

        $now = date('YmdHis');



        return (new RekapPembelianOutletExport($tgl_awal, $tgl_akhir, $id_apotek, $inisial))->download('Rekap_Pembelian_'.$inisial.'_'.$now.'.xlsx');

    }



    public function export_rekap_data_all(Request $request) 

    {

        ini_set('memory_limit', '-1'); 



        list($tgl_awal, $tgl_akhir) = HomeController::getDateFilter($request->id_pencarian, $request->tanggal);



        $now = date('YmdHis');

        $apoteks = MasterApotek::select(

            'id',

            'nama_singkat'

        )

        ->whereRaw('is_deleted = 0')

        ->get();



        $zip = new ZipArchive();

        $zipFileName = 'Rekap_Pembelian_All_Outlet_'.$now.'.zip';



        if ($zip->open($zipFileName, ZipArchive::CREATE) === true) {

            foreach($apoteks as $apotek){

                $export = new RekapPembelianOutletExport($tgl_awal, $tgl_akhir, $apotek->id, strtolower($apotek->nama_singkat));

                $fileName = 'Rekap_Pembelian_'.strtolower($apotek->nama_singkat).'_'.$now.'.xlsx';

                $path = Excel::store($export, $fileName);

                $zip->addFile(storage_path('app/'.$fileName), $fileName);

            }

            

            $zip->close();

            

            foreach($apoteks as $apotek){

                $fileName = 'Rekap_Pembelian_'.strtolower($apotek->nama_singkat).'_'.$now.'.xlsx';

                unlink(storage_path('app/'.$fileName));

            }



            return response()->download($zipFileName)->deleteFileAfterSend();

        } else {

            return 'Failed to create ZIP file.';

        }

    }

    

    public function list_pareto_penjualan_deadstok(Request $request)

    {

        ini_set('memory_limit', '-1'); 

        $order = $request->get('order');

        $columns = $request->get('columns');

        $order_column = $columns[$order[0]['column']]['data'];

        $order_dir = $order[0]['dir'];



        list($tgl_awal, $tgl_akhir) = HomeController::getDateFilter($request->id_pencarian, $request->tanggal);

        

        $limit = $request->limit;

        $apotek = MasterApotek::find(session('id_apotek_active'));

        $inisial = strtolower($apotek->nama_singkat);



        $id_satuan = $request->id_satuan;

        if($id_satuan == '') {

            $id_satuan = 'LIKE "%%"';

        } else {

             $id_satuan = '= '.$id_satuan.'';

        }

        //dd($id_satuan);



        $id_produsen = $request->id_produsen;

        if($id_produsen == '') {

            $id_produsen = 'LIKE "%%"';

        } else {

            $id_produsen = '= '.$id_produsen.'';

        }

        $awal = DB::table('tb_detail_nota_penjualan as a')

                    ->select([

                        'a.id_obat',

                        DB::raw('SUM(a.jumlah) as jumlah_pemakaian')

                    ])

                    ->leftjoin('tb_nota_penjualan as b','b.id','=','a.id_nota')

                    ->whereRaw('b.created_at >="'.$tgl_awal.'"')

                    ->whereRaw('b.created_at <="'.$tgl_akhir.'"')

                    ->whereRaw('b.id_apotek_nota ="'.$apotek->id.'"')

                    ->whereRaw('b.is_deleted = 0')

                    ->whereRaw('a.is_deleted = 0')

                    ->groupBy('a.id_obat');

       

       DB::statement(DB::raw('set @rownum = 0'));

        $all = DB::table('tb_m_stok_harga_'.$inisial.' as c')

                    ->select([

                        DB::raw('IFNULL(y.id_obat, 0) as id_det'),

                        DB::raw('IFNULL(y.jumlah_pemakaian, 0) as jumlah_pemakaian'),

                        'c.*',

                        'e.satuan',

                        'f.nama as penandaan_obat',

                        'g.nama as produsen'

                    ])

                    ->leftJoin(DB::raw("({$awal->toSql()}) as y"), 'y.id_obat', '=', 'c.id_obat')

                    ->join('tb_m_obat as d','d.id','=','c.id_obat')

                    ->join('tb_m_satuan as e','e.id','=','d.id_satuan')

                    ->join('tb_m_penandaan_obat as f','f.id','=','d.id_penandaan_obat')

                    ->join('tb_m_produsen as g','g.id','=','d.id_produsen')

                    ->whereRaw('d.id_satuan '.$id_satuan.'')

                    ->whereRaw('d.id_produsen '.$id_produsen.'')

                    ->orderByRaw('c.stok_akhir DESC');



        $data = DB::table(DB::raw("({$all->toSql()}) as j"))

            ->select([

                    DB::raw('@rownum  := @rownum  + 1 AS no'), 'j.*'])

            ->whereRaw('stok_akhir != 0')->whereRaw('jumlah_pemakaian = 0')->limit($limit);



        /*$data = DB::table('tb_m_stok_harga_'.$inisial.'')

                    ->select([

                        DB::raw('@rownum  := @rownum  + 1 AS no'),

                        'tb_m_stok_harga_'.$inisial.'.*'

                    ])

                    //->join('tb_detail_nota_penjualan as a', 'a.id_obat', '=', 'tb_m_stok_harga_'.$inisial.'.id_obat')

                    ->where('tb_m_stok_harga_'.$inisial.'.stok_akhir', '!=', 0)

                    ->whereNotExists(function ($query) use($tgl_awal, $tgl_akhir, $inisial, $apotek) {

                        $query->select(\DB::raw(1))

                            ->from('tb_detail_nota_penjualan as a')

                            //->join('tb_detail_nota_penjualan as a', 'a.id_obat', '=', 'tb_m_stok_harga_'.$inisial.'.id_obat')

                            ->join('tb_nota_penjualan as b','b.id','=','a.id_nota')

                            ->where('a.id_obat', '=', 'tb_m_stok_harga_'.$inisial.'.id_obat')

                            ->whereDate('b.created_at','>=', $tgl_awal)

                            ->whereDate('b.created_at','<=', $tgl_akhir)

                            ->where('b.id_apotek_nota','=',$apotek->id)

                            ->where('b.is_deleted', 0)

                            ->groupBy('tb_m_stok_harga_'.$inisial.'.id_obat');

                    })

                    ->orderByRaw('tb_m_stok_harga_'.$inisial.'.stok_akhir DESC');*/

                    



        if($order_column == "stok_akhir" || $order_column == "" || $order_column == "no") {

             $data = $data->orderByRaw('stok_akhir '.$order_dir.'');

        } 



        $data = $data->limit($limit);

      

        $datatables = Datatables::of($data);

        return $datatables  

        /*->filter(function($query) use($request,$order_column,$order_dir){

            $query->where(function($query) use($request){

                $query->orwhere('agama','LIKE','%'.$request->get('search')['value'].'%');

            });

        })  */

        ->editcolumn('nama', function($data) {

            return $data->nama; //.'-'.$data->jumlah_pemakaian;

        })

        ->editcolumn('id_penandaan_obat', function($data) {

            return $data->penandaan_obat;

        })

        ->editcolumn('id_satuan', function($data) {

            return $data->satuan;

        })

        ->editcolumn('id_produsen', function($data) {

            return $data->produsen;

        })

        ->editcolumn('harga_beli_ppn', function($data) {

            $histori_stok = HistoriStok::select([DB::raw('SUM(sisa_stok) as jum_sisa_stok'), DB::raw('SUM(sisa_stok*hb_ppn) as total')])

                            ->where('id_obat', $data->id_obat)

                            ->whereIn('id_jenis_transaksi', [2,3,11,9])

                            ->where('sisa_stok', '>', 0)

                            ->orderBy('id', 'ASC')

                            ->first();

            $avg = 0;

            $btn = '<span class="label text-red">[stok kosong]</span>';

            if(!is_null($histori_stok)) {

                if($histori_stok->total!=0) {

                    $avg = $histori_stok->total/$histori_stok->jum_sisa_stok;

                }

            }

            $format = number_format($avg,0,',',',');

            return $format;

        })

        ->editcolumn('harga_jual', function($data) {

            $format = number_format($data->harga_jual,0,',',',');

            return $format;

        })

        ->addIndexColumn()

        ->make(true);  

    }



    public function rekap_all_outlet() {
        //echo "Sedang maintenance";exit();

        $first_day = date('Y-m-d');



        $apoteks = MasterApotek::where('is_deleted', 0)->limit(1)->get();

        return view('rekap_all_outlet')->with(compact('apoteks', 'first_day'));

    }



    function cari_info(Request $request) {

        $alls = MasterApotek::where('is_deleted', 0)->where('id', session('id_apotek_active'))->get();

        $awal = $request->tgl_awal;

        $akhir = $request->tgl_akhir;

        $tgl_awal_baru = $awal.' 00:00:00';

        $tgl_akhir_baru = $akhir.' 23:59:59';

        $data_ = '';

        $data_ .= '<table class="table table-bordered table-striped table-hover">

                    <thead>

                        <tr>

                            <th class="text-center text-white" style="background-color:#00bcd4;">APOTEK</th>

                            <th class="text-center text-white" style="background-color:#00bcd4;">COUNT PEMBELIAN</th>

                            <th class="text-center text-white" style="background-color:#00bcd4;">COUNT PENJUALLAN</th>

                            <th class="text-center text-white" style="background-color:#00bcd4;">TOTAL PENJUALAN NON KREDIT</th>

                            <th class="text-center text-white" style="background-color:#00acc1;">TOTAL PENJUALAN KREDIT</th>

                            <th class="text-center text-white" style="background-color:#00acc1;">TOTAL TT PENJUALAN</th>

                            <th class="text-center text-white" style="background-color:#00acc1;">TOTAL PEMBAYARAN PENJUALAN KREDIT</th>

                            <th class="text-center text-white" style="background-color:#00acc1;">TOTAL PEMBELIAN</th>

                            <th class="text-center text-white" style="background-color:#00acc1;">TOTAL PIUTANG PEMBELIAN</th>

                            <th class="text-center text-white" style="background-color:#0097a7;">TOTAL PEMBELIAN TERBAYAR</th>

                            <th class="text-center text-white" style="background-color:#0097a7;">TOTAL PEMBELIAN JATUH TEMPO</th>

                        </tr>

                    </thead><tbody>';


        foreach ($alls as $x => $xyz) {

            $result = DB::select('SELECT getCountPenjualan(?, ?, ?) AS hit_penjualan', [$xyz->id, $tgl_awal_baru, $tgl_akhir_baru]);
            $hit_penjualan = $result[0]->hit_penjualan;

            $result = DB::select('SELECT getCountPembelian(?, ?, ?) AS hit_pembelian', [$xyz->id, $tgl_awal_baru, $tgl_akhir_baru]);
            $hit_pembelian = $result[0]->hit_pembelian;

            $detail_penjualan_kredit = DB::select('CALL getSumDetailPenjualanKredit(?, ?, ?)', [$xyz->id, $tgl_awal_baru, $tgl_akhir_baru]);
            $penjualan_kredit =  DB::select('CALL getSumPenjualanKredit(?, ?, ?)', [$xyz->id, $tgl_awal_baru, $tgl_akhir_baru]);

            $detail_penjualan = DB::select('CALL getSumDetailPenjualan(?, ?, ?)', [$xyz->id, $tgl_awal_baru, $tgl_akhir_baru]);
            $penjualan2 =  DB::select('CALL getSumPenjualan(?, ?, ?)', [$xyz->id, $tgl_awal_baru, $tgl_akhir_baru]);

            $detail_penjualan_kredit_terbayar = DB::select('CALL getSumDetailPenjualanKreditTerbayar(?, ?, ?)', [$xyz->id, $tgl_awal_baru, $tgl_akhir_baru]);
            $penjualan_kredit_terbayar =  DB::select('CALL getSumPenjualanKreditTerbayar(?, ?, ?)', [$xyz->id, $tgl_awal_baru, $tgl_akhir_baru]);

            $detail_tf_masuk = DB::select('CALL getSumTOMasuk(?, ?, ?)', [$xyz->id, $tgl_awal_baru, $tgl_akhir_baru]);
            $detail_tf_keluar =  DB::select('CALL getSumTOKeluar(?, ?, ?)', [$xyz->id, $tgl_awal_baru, $tgl_akhir_baru]);

            $detail_penjualan_cn = DB::select('CALL getSumDetailPenjualanCn(?, ?, ?)', [$xyz->id, $tgl_awal_baru, $tgl_akhir_baru]);

            $getPembelian = DB::select('CALL getSumDetailPembelian(?, ?, ?, ?)', [$xyz->id, $tgl_awal_baru, $tgl_akhir_baru, session('id_tahun_active')]);
            $total_pembelian = $getPembelian[0]->sum_total2;

            $getPembelianTerbayar = DB::select('CALL getSumDetailPembelianTerbayar(?, ?, ?, ?)', [$xyz->id, $tgl_awal_baru, $tgl_akhir_baru, session('id_tahun_active')]);
            $total_pembelian_terbayar = $getPembelianTerbayar[0]->sum_total2;

            $total_pembelian_blm_terbayar = $total_pembelian-$total_pembelian_terbayar;
            
            $getPembelianJT = DB::select('CALL getSumDetailPembelianTerbayar(?, ?, ?, ?)', [$xyz->id, $tgl_awal_baru, $tgl_akhir_baru, session('id_tahun_active')]);
            $total_pembelian_jatuh_tempo = $getPembelianJT[0]->sum_total2;

            $penjualan_closing = DB::select('CALL getSumClosingPenjualan(?, ?, ?, ?)', [$xyz->id, $tgl_awal_baru, $tgl_akhir_baru, session('id_tahun_active')]);

            $total_tt = $penjualan_closing[0]->total;
            $diskon_penjualan_kredit = $detail_penjualan_kredit[0]->total_diskon_persen_vendor + $detail_penjualan_kredit[0]->total_diskon_persen;  
            $total_cash_kredit =  $detail_penjualan_kredit[0]->total - $detail_penjualan_kredit[0]->total_diskon_persen_vendor - $detail_penjualan_kredit[0]->total_diskon_persen;
            $total_cash_kredit_format = number_format($total_cash_kredit,0,',',',');
            $total_cn = 0 + $detail_penjualan_cn[0]->total - $detail_penjualan_cn[0]->total_diskon_persen;

            $total_diskon = $detail_penjualan[0]->total_diskon_persen + $penjualan2[0]->total_diskon_rp;
            $total_3 = $detail_penjualan[0]->total-$total_diskon-$total_cn;
            $total_3_format = number_format($total_3,0,',',',');

            $total_cash_kredit_terbayar = ($detail_penjualan_kredit_terbayar[0]->total + $penjualan_kredit_terbayar[0]->total_jasa_dokter + $penjualan_kredit_terbayar[0]->total_jasa_resep) - $penjualan_kredit_terbayar[0]->total_debet-$detail_penjualan_kredit_terbayar[0]->total_diskon_vendor;
            $total_penjualan_kredit_terbayar = $penjualan_kredit_terbayar[0]->total_debet+$total_cash_kredit_terbayar;
            $total_penjualan_kredit_terbayar_format = number_format($total_penjualan_kredit_terbayar,0,',',',');

            $total_tf_masuk = number_format($total_penjualan_kredit_terbayar,0,',',',');
            $total_tf_keluar = number_format($total_penjualan_kredit_terbayar,0,',',',');

            $total_pembelian = number_format($total_pembelian,0,',',',');
            $total_pembelian_terbayar = number_format($total_pembelian_terbayar,0,',',',');
            $total_pembelian_blm_terbayar = number_format($total_pembelian_blm_terbayar,0,',',',');
            $total_pembelian_jatuh_tempo = number_format($total_pembelian_jatuh_tempo,0,',',',');
            $total_tt = number_format($total_tt,0,',',',');

            $data_ .= '
                        <tr>
                            <td>'.$xyz->nama_singkat.'</td>
                            <td><span class="text-info">'.$hit_pembelian.' invoices</span></td>
                            <td><span class="text-info">'.$hit_penjualan.' sales</span></td>
                            <td>'.$total_3_format.'</td>
                            <td>'.$total_cash_kredit_format.'</td>
                            <td>'.$total_tt.'</td>
                            <td>'.$total_penjualan_kredit_terbayar_format.'</td>
                            <td>'.$total_pembelian.'</td>
                            <td>'.$total_pembelian_blm_terbayar.'</td>
                            <td>'.$total_pembelian_terbayar.'</td>
                            <td>'.$total_pembelian_jatuh_tempo.'</td
                        </tr>
                ';
        }
         $data_ .= '                      
                    </tbody>
                </table>';
    


        return response()->json($data_); 

    }



    public function rekap_penjualan() {

        $first_day = date('Y-m-d');



        $apoteks = MasterApotek::where('is_deleted', 0)->get();

        return view('rekap_penjualan')->with(compact('apoteks', 'first_day'));

    }



    function cari_info_penjualan(Request $request) {

        $alls = MasterApotek::where('id_group_apotek', Auth::user()->id_group_apotek)->where('is_deleted', 0)->get();

        $awal = $request->tgl_awal;

        $akhir = $request->tgl_akhir;

        $tgl_awal_baru = $awal.' 00:00:00';

        $tgl_akhir_baru = $akhir.' 23:59:59';

        $data_ = '';

        $data_ .= '<table class="table table-bordered table-striped table-hover">

                    <thead>

                        <tr>

                            <th class="text-center text-white" style="background-color:#00bcd4;">APOTEK</th>

                            <th class="text-center text-white" style="background-color:#00bcd4;">COUNT PENJUALLAN</th>

                            <th class="text-center text-white" style="background-color:#FBC02D;">TOTAL PENJUALAN</th>

                            <th class="text-center text-white" style="background-color:#FBC02D;">TOTAL DISKON</th>

                            <th class="text-center text-white" style="background-color:#FBC02D;">TOTAL RETUR</th>

                            <th class="text-center text-white" style="background-color:#FBC02D;">TOTAL PENJULAN FINAL</th>

                            <th class="text-center text-white" style="background-color:#FBC02D;">TOTAL HPP</th>

                             <th class="text-center text-white" style="background-color:#FBC02D;">TOTAL LABA</th>

                            <th class="text-center text-white" style="background-color:#00bcd4;">TOTAL PENJUALAN NON KREDIT</th>

                            <th class="text-center text-white" style="background-color:#00acc1;">TOTAL PENJUALAN KREDIT</th>

                            <th class="text-center text-white" style="background-color:#00acc1;">TOTAL TT PENJUALAN</th>

                            <th class="text-center text-white" style="background-color:#00acc1;">TOTAL PEMBAYARAN PENJUALAN KREDIT</th>

                        </tr>

                    </thead><tbody>';

        $hit_total_kunjungan_penjualan = 0; $hit_total_penjualan = 0; $hit_total_diskon = 0; $hit_total_retur = 0; $hit_total_penjualan_final = 0; $hit_total_hpp = 0; $hit_total_laba = 0; $hit_penjualan_non_kredit = 0; $hit_total_penjualan_kredit = 0; $hit_total_tt = 0; $hit_total_pembayaran_penjualan_kredit = 0;

        foreach ($alls as $x => $xyz) {

            $hit_penjualan = TransaksiPenjualan::where('is_deleted', 0)

                                ->where('id_apotek_nota', $xyz->id)

                                ->whereDate('created_at','>=', $tgl_awal_baru)

                                ->whereDate('created_at','<=', $tgl_akhir_baru)

                                ->count();

            $detail_penjualan_kredit = DB::table('tb_detail_nota_penjualan')

                            ->select(

                                    DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) AS total_penjualan'),

                                    DB::raw('SUM(tb_detail_nota_penjualan.diskon) AS total_diskon'),

                                    DB::raw('SUM((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) - tb_detail_nota_penjualan.diskon) AS total'),

                                    DB::raw('SUM(b.diskon_persen/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen'),

                                     DB::raw('SUM(a.diskon/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen_vendor')

                                )

                            ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')

                            ->leftjoin('tb_vendor_kerjasama as a','a.id','=','b.id_vendor')

                            ->whereDate('b.tgl_nota','>=', $tgl_awal_baru)

                            ->whereDate('b.tgl_nota','<=', $tgl_akhir_baru)

                            ->where('b.id_apotek_nota','=',$xyz->id)

                            ->where('b.is_deleted', 0)

                            ->where('b.is_kredit', 1)

                            ->where('tb_detail_nota_penjualan.is_cn', 0)

                            ->where('tb_detail_nota_penjualan.is_deleted', 0)

                            ->first();



            /*if($xyz->id == 6) {

                print_r($detail_penjualan_kredit);exit();

            }*/



            $penjualan_kredit =  DB::table('tb_nota_penjualan')

                        ->select(

                                DB::raw('SUM(tb_nota_penjualan.biaya_jasa_dokter) AS total_jasa_dokter'),

                                DB::raw('SUM(a.biaya) AS total_jasa_resep'),

                                DB::raw('SUM(tb_nota_penjualan.diskon_rp) AS total_diskon_rp'),

                                DB::raw('SUM(tb_nota_penjualan.debet) AS total_debet'))

                        ->join('tb_m_jasa_resep as a','a.id','=','tb_nota_penjualan.id_jasa_resep')

                        ->whereDate('tb_nota_penjualan.tgl_nota','>=', $tgl_awal_baru)

                        ->whereDate('tb_nota_penjualan.tgl_nota','<=', $tgl_akhir_baru)

                        ->where('tb_nota_penjualan.id_apotek_nota','=',$xyz->id)

                        ->where('tb_nota_penjualan.is_deleted', 0)

                        ->where('tb_nota_penjualan.is_kredit', 1)

                        ->first();



            $detail_penjualan = DB::table('tb_detail_nota_penjualan')

                        ->select(

                                DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) AS total_penjualan'),

                                DB::raw('SUM(tb_detail_nota_penjualan.diskon) AS total_diskon'),

                                DB::raw('SUM((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) - tb_detail_nota_penjualan.diskon) AS total'),

                                DB::raw('SUM(b.diskon_persen/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen'))

                        ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')

                        ->whereDate('b.created_at','>=', $tgl_awal_baru)

                        ->whereDate('b.created_at','<=', $tgl_akhir_baru)

                        ->where('b.id_apotek_nota','=',$xyz->id)

                        ->where('b.is_deleted', 0)

                        ->where('b.is_kredit', 0)

                        ->where('tb_detail_nota_penjualan.is_deleted', 0)

                        ->first();



            $penjualan2 =  DB::table('tb_nota_penjualan')

                        ->select(

                                DB::raw('SUM(tb_nota_penjualan.biaya_jasa_dokter) AS total_jasa_dokter'),

                                DB::raw('SUM(a.biaya) AS total_jasa_resep'),

                                DB::raw('SUM(tb_nota_penjualan.debet) AS total_debet'),

                                DB::raw('SUM(tb_nota_penjualan.harga_wd) AS total_paket_wd'),

                                DB::raw('SUM(tb_nota_penjualan.biaya_lab) AS total_lab'),

                                DB::raw('SUM(tb_nota_penjualan.biaya_apd) AS total_apd'),

                                DB::raw('SUM(tb_nota_penjualan.diskon_rp) AS total_diskon_rp'))

                        ->join('tb_m_jasa_resep as a','a.id','=','tb_nota_penjualan.id_jasa_resep')

                        ->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal_baru)

                        ->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir_baru)

                        ->where('tb_nota_penjualan.id_apotek_nota','=',$xyz->id)

                        ->where('tb_nota_penjualan.is_deleted', 0)

                        ->where('tb_nota_penjualan.is_kredit', 0)

                        ->first();



            $detail_penjualan_kredit_terbayar = DB::table('tb_detail_nota_penjualan')

                        ->select(

                                DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) AS total_penjualan'),

                                DB::raw('SUM(tb_detail_nota_penjualan.diskon) AS total_diskon'),

                                DB::raw('SUM((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) - tb_detail_nota_penjualan.diskon) AS total'),

                                DB::raw('SUM(b.diskon_persen/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen'),

                                DB::raw('SUM(b.diskon_vendor/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_vendor')

                            )

                        ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')

                        ->whereDate('b.is_lunas_pembayaran_kredit_at','>=', $tgl_awal_baru)

                        ->whereDate('b.is_lunas_pembayaran_kredit_at','<=', $tgl_akhir_baru)

                        ->where('b.id_apotek_nota','=',$xyz->id)

                        ->where('b.is_deleted', 0)

                        ->where('b.is_kredit', 1)

                        ->where('b.is_lunas_pembayaran_kredit', 1)

                        ->where('tb_detail_nota_penjualan.is_cn', 0)

                        ->where('tb_detail_nota_penjualan.is_deleted', 0)

                        ->first();

        

            $penjualan_kredit_terbayar =  DB::table('tb_nota_penjualan')

                        ->select(

                                DB::raw('SUM(tb_nota_penjualan.biaya_jasa_dokter) AS total_jasa_dokter'),

                                DB::raw('SUM(a.biaya) AS total_jasa_resep'),

                                DB::raw('SUM(tb_nota_penjualan.diskon_rp) AS total_diskon_rp'),

                                DB::raw('SUM(tb_nota_penjualan.debet) AS total_debet'))

                        ->join('tb_m_jasa_resep as a','a.id','=','tb_nota_penjualan.id_jasa_resep')

                        ->whereDate('tb_nota_penjualan.is_lunas_pembayaran_kredit_at','>=', $tgl_awal_baru)

                        ->whereDate('tb_nota_penjualan.is_lunas_pembayaran_kredit_at','<=', $tgl_akhir_baru)

                        ->where('tb_nota_penjualan.id_apotek_nota','=',$xyz->id)

                        ->where('tb_nota_penjualan.is_deleted', 0)

                        ->where('tb_nota_penjualan.is_kredit', 1)

                        ->where('tb_nota_penjualan.is_lunas_pembayaran_kredit', 1)

                        //->groupBy('tb_nota_penjualan.id')

                        ->first();



            $detail_penjualan_cn = DB::table('tb_detail_nota_penjualan')

                        ->select(

                                DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah_cn) AS total_penjualan'),

                                DB::raw('SUM(tb_detail_nota_penjualan.diskon) AS total_diskon'),

                                DB::raw('SUM((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah_cn) - tb_detail_nota_penjualan.diskon) AS total'),

                                DB::raw('SUM(b.diskon_persen/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah_cn)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen'))

                        ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')

                        ->whereDate('tb_detail_nota_penjualan.cn_at','>=', $tgl_awal_baru)

                        ->whereDate('tb_detail_nota_penjualan.cn_at','<=', $tgl_akhir_baru)

                        ->where('b.id_apotek_nota','=',$xyz->id)

                        ->where('b.is_deleted', 0)

                        ->where('tb_detail_nota_penjualan.is_cn', 1)

                        ->where('tb_detail_nota_penjualan.is_approved', 1)

                        ->where('tb_detail_nota_penjualan.is_deleted', 0)

                        ->where('b.is_kredit', 0)

                        ->first();



            $penjualan_closing = TransaksiPenjualanClosing::select([DB::raw('SUM(jumlah_tt) as total')])

                                        ->where(function($query) use($tgl_awal_baru, $tgl_akhir_baru, $xyz){

                                            //$query->where('is_deleted','=','0');

                                            $query->whereDate('tanggal','>=', $tgl_awal_baru);

                                            $query->whereDate('tanggal','<=', $tgl_akhir_baru);

                                            $query->where('id_apotek_nota','=',$xyz->id);

                                        })->first();

            

            $detail_penjualanAll = DB::table('tb_detail_nota_penjualan')

                        ->select(

                                DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) AS total_penjualan'),

                                DB::raw('SUM(tb_detail_nota_penjualan.diskon) AS total_diskon'),

                                DB::raw('SUM((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) - tb_detail_nota_penjualan.diskon) AS total'),

                                DB::raw('SUM(((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)) - (tb_detail_nota_penjualan.jumlah * tb_detail_nota_penjualan.hb_ppn)) AS total_laba'),

                                DB::raw('SUM((tb_detail_nota_penjualan.jumlah * tb_detail_nota_penjualan.hb_ppn)) AS total_hpp'),

                                DB::raw('SUM(((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) - tb_detail_nota_penjualan.diskon) - (tb_detail_nota_penjualan.jumlah * tb_detail_nota_penjualan.hb_ppn)) as total_laba'),

                                DB::raw('SUM(b.diskon_persen/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen'))

                        ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')

                        ->whereDate('b.created_at','>=', $tgl_awal_baru)

                        ->whereDate('b.created_at','<=', $tgl_akhir_baru)

                        ->where('b.id_apotek_nota','=',$xyz->id)

                        ->where('b.is_deleted', 0)

                        ->where('tb_detail_nota_penjualan.is_deleted', 0)

                        ->first();



            $penjualanAll =  DB::table('tb_nota_penjualan')

                        ->select(

                                DB::raw('SUM(tb_nota_penjualan.biaya_jasa_dokter) AS total_jasa_dokter'),

                                DB::raw('SUM(a.biaya) AS total_jasa_resep'),

                                DB::raw('SUM(tb_nota_penjualan.debet) AS total_debet'),

                                DB::raw('SUM(tb_nota_penjualan.harga_wd) AS total_paket_wd'),

                                DB::raw('SUM(tb_nota_penjualan.biaya_lab) AS total_lab'),

                                DB::raw('SUM(tb_nota_penjualan.biaya_apd) AS total_apd'),

                                DB::raw('SUM(tb_nota_penjualan.diskon_rp) AS total_diskon_rp'))

                        ->join('tb_m_jasa_resep as a','a.id','=','tb_nota_penjualan.id_jasa_resep')

                        ->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal_baru)

                        ->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir_baru)

                        ->where('tb_nota_penjualan.id_apotek_nota','=',$xyz->id)

                        ->where('tb_nota_penjualan.is_deleted', 0)

                        ->first();



            $detail_penjualan_Allcn = DB::table('tb_detail_nota_penjualan')

                        ->select(

                                DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah_cn) AS total_penjualan'),

                                DB::raw('SUM(tb_detail_nota_penjualan.diskon) AS total_diskon'),

                                DB::raw('SUM((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah_cn) - tb_detail_nota_penjualan.diskon) AS total'),

                                DB::raw('SUM(b.diskon_persen/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah_cn)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen'))

                        ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')

                        ->whereDate('tb_detail_nota_penjualan.cn_at','>=', $tgl_awal_baru)

                        ->whereDate('tb_detail_nota_penjualan.cn_at','<=', $tgl_akhir_baru)

                        ->where('b.id_apotek_nota','=',$xyz->id)

                        ->where('b.is_deleted', 0)

                        ->where('tb_detail_nota_penjualan.is_cn', 1)

                        ->where('tb_detail_nota_penjualan.is_approved', 1)

                        ->where('tb_detail_nota_penjualan.is_deleted', 0)

                        ->first();



            $total_cnAll = 0 + $detail_penjualan_Allcn->total - $detail_penjualan_Allcn->total_diskon_persen;

            $total_diskonAll = $detail_penjualanAll->total_diskon_persen + $penjualanAll->total_diskon_rp;

            $total_All_final = $detail_penjualanAll->total-$total_diskonAll-$total_cnAll;

            $total_All_format = number_format($detail_penjualanAll->total,0,',',',');

            $total_hpp = $detail_penjualanAll->total_hpp-$total_diskonAll-$total_cnAll;

            $total_All_hpp_format = number_format($total_hpp,0,',',',');

            $total_All_laba_format = number_format($detail_penjualanAll->total_laba,0,',',',');

            $total_All_final_format = number_format($total_All_final,0,',',',');

            $total_diskonAll_format = number_format($total_diskonAll,0,',',',');

            $total_cnAll_format = number_format($total_cnAll,0,',',',');



            $total_tt = $penjualan_closing->total;

            $diskon_penjualan_kredit = $detail_penjualan_kredit->total_diskon_persen_vendor + $detail_penjualan_kredit->total_diskon_persen;  

            $total_cash_kredit =  $detail_penjualan_kredit->total - $detail_penjualan_kredit->total_diskon_persen_vendor - $detail_penjualan_kredit->total_diskon_persen;

            $total_cash_kredit_format = number_format($total_cash_kredit,0,',',',');



            $total_cn = 0 + $detail_penjualan_cn->total - $detail_penjualan_cn->total_diskon_persen;



            $total_diskon = $detail_penjualan->total_diskon_persen + $penjualan2->total_diskon_rp;

            $total_3 = $detail_penjualan->total-$total_diskon-$total_cn;

            $total_3_format = number_format($total_3,0,',',',');



            $total_cash_kredit_terbayar = ($detail_penjualan_kredit_terbayar->total + $penjualan_kredit_terbayar->total_jasa_dokter + $penjualan_kredit_terbayar->total_jasa_resep) - $penjualan_kredit_terbayar->total_debet-$detail_penjualan_kredit_terbayar->total_diskon_vendor;

            $total_penjualan_kredit_terbayar = $penjualan_kredit_terbayar->total_debet+$total_cash_kredit_terbayar;

            $total_penjualan_kredit_terbayar_format = number_format($total_penjualan_kredit_terbayar,0,',',',');



            $total_tt_format = number_format($total_tt,0,',',',');



            $hit_total_kunjungan_penjualan = $hit_total_kunjungan_penjualan + $hit_penjualan; 

            $hit_total_penjualan = $hit_total_penjualan + $detail_penjualanAll->total; 

            $hit_total_diskon = $hit_total_diskon + $total_diskonAll; 

            $hit_total_retur = $hit_total_retur + $total_cnAll; 

            $hit_total_penjualan_final = $hit_total_penjualan_final + $total_All_final; 

            $hit_total_hpp = $hit_total_hpp + $total_hpp; 

            $hit_total_laba = $hit_total_laba + $detail_penjualanAll->total_laba; 

            $hit_penjualan_non_kredit = $hit_penjualan_non_kredit + $total_3; 

            $hit_total_penjualan_kredit = $hit_total_penjualan_kredit + $total_cash_kredit; 

            $hit_total_tt = $hit_total_tt + $total_tt; 

            $hit_total_pembayaran_penjualan_kredit = $hit_total_pembayaran_penjualan_kredit + $total_penjualan_kredit_terbayar;

            

            $data_ .= '

                        <tr>

                            <td class="text-center">'.$xyz->nama_singkat.'</td>

                            <td class="text-center"><span class="text-info">'.$hit_penjualan.' sales</span></td>

                            <td class="text-right">'.$total_All_format.'</td>

                            <td class="text-right">'.$total_diskonAll_format.'</td>

                            <td class="text-right">'.$total_cnAll.'</td>

                            <td class="text-right">'.$total_All_final_format.'</td>

                            <td class="text-right">'.$total_All_hpp_format.'</td>

                            <td class="text-right">'.$total_All_laba_format.'</td>

                            <td class="text-right">'.$total_3_format.'</td>

                            <td class="text-right">'.$total_cash_kredit_format.'</td>

                            <td class="text-right">'.$total_tt_format.'</td>

                            <td class="text-right">'.$total_penjualan_kredit_terbayar_format.'</td>

                        </tr>

                ';

        }



        $data_ .= '

                        <tr>

                            <td class="text-center bg-danger">TOTAL</td>

                            <td class="text-center bg-danger"><span class="text-info">'.number_format($hit_total_kunjungan_penjualan,0,',',',').' sales</span></td>

                            <td class="text-right bg-danger">'.number_format($hit_total_penjualan,0,',',',').'</td>

                            <td class="text-right bg-danger">'.number_format($hit_total_diskon,0,',',',').'</td>

                            <td class="text-right bg-danger">'.number_format($hit_total_retur,0,',',',').'</td>

                            <td class="text-right bg-danger">'.number_format($hit_total_penjualan_final,0,',',',').'</td>

                            <td class="text-right bg-danger">'.number_format($hit_total_hpp,0,',',',').'</td>

                            <td class="text-right bg-danger">'.number_format($hit_total_laba,0,',',',').'</td>

                            <td class="text-right bg-danger">'.number_format($hit_penjualan_non_kredit,0,',',',').'</td>

                            <td class="text-right bg-danger">'.number_format($hit_total_penjualan_kredit,0,',',',').'</td>

                            <td class="text-right bg-danger">'.number_format($hit_total_tt,0,',',',').'</td>

                            <td class="text-right bg-danger">'.number_format($hit_total_pembayaran_penjualan_kredit,0,',',',').'</td>

                        </tr>

                ';



         $data_ .= '                      

                    </tbody>

                </table>';



        return response()->json($data_); 

    }



    public function rekap_pembelian() {

        $first_day = date('Y-m-d');



        $apoteks = MasterApotek::where('is_deleted', 0)->get();

        return view('rekap_pembelian')->with(compact('apoteks', 'first_day'));

    }



    public function cari_info_pembelian(Request $request) {

       $alls = MasterApotek::where('id_group_apotek', Auth::user()->id_group_apotek)->where('is_deleted', 0)->get();

        $awal = $request->tgl_awal;

        $akhir = $request->tgl_akhir;

        $tgl_awal_baru = $awal.' 00:00:00';

        $tgl_akhir_baru = $akhir.' 23:59:59';

        $data_ = '';

        $data_ .= '<table class="table table-bordered table-striped table-hover">

                    <thead>

                        <tr>

                            <th class="text-center text-white" style="background-color:#00bcd4;">APOTEK</th>

                            <th class="text-center text-white" style="background-color:#00bcd4;">COUNT PEMBELIAN</th>

                            <th class="text-center text-white" style="background-color:#00acc1;">TOTAL PEMBELIAN</th>

                            <th class="text-center text-white" style="background-color:#00acc1;">TOTAL PIUTANG PEMBELIAN</th>

                            <th class="text-center text-white" style="background-color:#0097a7;">TOTAL PEMBELIAN TERBAYAR</th>

                            <th class="text-center text-white" style="background-color:#0097a7;">TOTAL PEMBELIAN JATUH TEMPO</th>

                        </tr>

                    </thead><tbody>';

        foreach ($alls as $x => $xyz) {

            $hit_pembelian = TransaksiPembelian::where('is_deleted', 0)

                                ->where('id_apotek_nota', $xyz->id)

                                ->whereDate('tgl_faktur','>=', $tgl_awal_baru)

                                ->whereDate('tgl_faktur','<=', $tgl_akhir_baru)

                                ->count();





            $pembelians = TransaksiPembelian::select([

                            'tb_nota_pembelian.*'])

                            ->where(function($query) use($tgl_awal_baru, $tgl_akhir_baru, $xyz){

                                $query->where('tb_nota_pembelian.is_deleted','=','0');

                                $query->whereDate('tgl_faktur','>=', $tgl_awal_baru);

                                $query->whereDate('tgl_faktur','<=', $tgl_akhir_baru);

                                $query->where('id_apotek_nota','=',$xyz->id);

                            })

                            ->orderBy('tgl_jatuh_tempo','asc')

                            ->orderBy('id_suplier')

                            ->groupBy('tb_nota_pembelian.id')

                            ->get();



            $collection = collect();

            $no = 0;

            $total_pembelian=0;

            foreach($pembelians as $rekap) {

                $no++;

                $x = $rekap->detail_pembelian_total[0];

                $total1 = $x->jumlah - ($rekap->diskon1 + $rekap->diskon2);

                $total2 = $total1 + ($total1 * $rekap->ppn/100);



               $total_pembelian = $total_pembelian + $total2;

            }



            $pembelians_terbayar = TransaksiPembelian::select([

                            'tb_nota_pembelian.*'])

                            ->where(function($query) use($tgl_awal_baru, $tgl_akhir_baru, $xyz){

                                $query->where('tb_nota_pembelian.is_lunas','=','1');

                                $query->where('tb_nota_pembelian.is_deleted','=','0');

                                $query->whereDate('lunas_at','>=', $tgl_awal_baru);

                                $query->whereDate('lunas_at','<=', $tgl_akhir_baru);

                                $query->where('id_apotek_nota','=',$xyz->id);

                            })

                            ->orderBy('tgl_jatuh_tempo','asc')

                            ->orderBy('id_suplier')

                            ->groupBy('tb_nota_pembelian.id')

                            ->get();



            $collection = collect();

            $no = 0;

            $total_pembelian_terbayar=0;

            foreach($pembelians_terbayar as $rekap) {

                $no++;

                $x = $rekap->detail_pembelian_total[0];

                $total1 = $x->jumlah - ($rekap->diskon1 + $rekap->diskon2);

                $total2 = $total1 + ($total1 * $rekap->ppn/100);



               $total_pembelian_terbayar = $total_pembelian_terbayar + $total2;

            }





            $pembelians_blm_terbayar = TransaksiPembelian::select([

                            'tb_nota_pembelian.*'])

                            ->where(function($query) use($tgl_awal_baru, $tgl_akhir_baru, $xyz){

                                $query->where('tb_nota_pembelian.is_lunas','=','0');

                                $query->where('tb_nota_pembelian.is_deleted','=','0');

                                $query->where('id_apotek_nota','=',$xyz->id);

                            })

                            ->orderBy('tgl_jatuh_tempo','asc')

                            ->orderBy('id_suplier')

                            ->groupBy('tb_nota_pembelian.id')

                            ->get();



            $collection = collect();

            $no = 0;

            $total_pembelian_blm_terbayar=0;

            foreach($pembelians_blm_terbayar as $rekap) {

                $no++;

                $x = $rekap->detail_pembelian_total[0];

                $total1 = $x->jumlah - ($rekap->diskon1 + $rekap->diskon2);

                $total2 = $total1 + ($total1 * $rekap->ppn/100);



               $total_pembelian_blm_terbayar = $total_pembelian_blm_terbayar + $total2;

            }





            $pembelians_jatuh_tempo = TransaksiPembelian::select([

                            'tb_nota_pembelian.*'])

                            ->where(function($query) use($tgl_awal_baru, $tgl_akhir_baru, $xyz){

                                $query->where('tb_nota_pembelian.is_lunas','=','0');

                                $query->where('tb_nota_pembelian.is_deleted','=','0');

                                $query->whereDate('tgl_jatuh_tempo','>=', $tgl_awal_baru);

                                $query->whereDate('tgl_jatuh_tempo','<=', $tgl_akhir_baru);

                                $query->where('id_apotek_nota','=',$xyz->id);

                            })

                            ->orderBy('tgl_jatuh_tempo','asc')

                            ->orderBy('id_suplier')

                            ->groupBy('tb_nota_pembelian.id')

                            ->get();



            $collection = collect();

            $no = 0;

            $total_pembelian_jatuh_tempo=0;

            foreach($pembelians_jatuh_tempo as $rekap) {

                $no++;

                $x = $rekap->detail_pembelian_total[0];

                $total1 = $x->jumlah - ($rekap->diskon1 + $rekap->diskon2);

                $total2 = $total1 + ($total1 * $rekap->ppn/100);



               $total_pembelian_jatuh_tempo = $total_pembelian_jatuh_tempo + $total2;

            }



            

            $total_pembelian = number_format($total_pembelian,0,',',',');

            $total_pembelian_terbayar = number_format($total_pembelian_terbayar,0,',',',');

            $total_pembelian_blm_terbayar = number_format($total_pembelian_blm_terbayar,0,',',',');

            $total_pembelian_jatuh_tempo = number_format($total_pembelian_jatuh_tempo,0,',',',');

            

            $data_ .= '

                        <tr>

                            <td>'.$xyz->nama_singkat.'</td>

                            <td><span class="text-info">'.$hit_pembelian.' invoices</span></td>

                            <td>'.$total_pembelian.'</td>

                            <td>'.$total_pembelian_blm_terbayar.'</td>

                            <td>'.$total_pembelian_terbayar.'</td>

                            <td>'.$total_pembelian_jatuh_tempo.'</td>

                        </tr>

                ';



           

        }

         $data_ .= '                      

                    </tbody>

                </table>';



        return response()->json($data_); 

    }



    public function rekap_pembelian_outlet() {

        $satuans = MasterSatuan::where('is_deleted', 0)->pluck('satuan', 'id');

        $satuans->prepend('-- all --','');

        $produsens = MasterProdusen::where('is_deleted', 0)->pluck('nama', 'id');

        $produsens->prepend('-- produsen --', '');

        return view('rekap_pembelian_outlet')->with(compact('satuans', 'produsens'));

    }



    public function set_active_printer($printer){

        if($printer == 1) {

            $nama = 'Dot Matrix';

        } else {

            $nama = 'Thermal';

        }

        

        if(!is_null($printer)){

            session(['id_printer_active'=>$printer]);

            session()->flash('success', 'Sukses melakukan perubahan printer menjadi '.$nama.'!');

        }else{

            session()->flash('error', 'Gagal melakukan perubahan printer menjadi '.$nama.'!. Printer tidak dapat ditemukan.');

        }

        return redirect()->intended('/home');

    }



    public function riwayat_kunjungan() {

        $id_apotek = session('id_apotek_active');

        $id_role_active = session('id_role_active');

        $hak_akses = 0;

        if(!empty($id_apotek)) {

            $apotek = MasterApotek::find(session('id_apotek_active'));

            $apoteker = User::find($apotek->id_apoteker);

            $id_user = Auth::user()->id;



            $hak_akses = 0;

            if($apoteker->id == $id_user) {

                $hak_akses = 1;

            }



            if($id_role_active == 1 || $id_role_active == 4 || $id_role_active == 6 || $id_role_active == 11) {

                $hak_akses = 1;

            }

        }



        return view('riwayat_kunjungan')->with(compact('hak_akses'));

    }



    public function load_grafik_kunjungan(Request $request) {

        $data = array();

        $app = app();

        $data_ = $app->make('stdClass');

        $tahun = session('id_tahun_active');

        $currentMonth = date('m');



        if($request->tanggal != "") {

            $split                      = explode("-", $request->tanggal);

            $tgl_awal       = date('Y-m-d H:i:s',strtotime($split[0]));

            $tgl_akhir      = date('Y-m-d H:i:s',strtotime($split[1]));

        } else {

            $tgl_awal = date('Y-m-d').' 00:00:00';

            $tgl_akhir = date('Y-m-d').' 00:00:00';

        }





        $jam = array();

        $jam[] = array('id' => 1, 'nama' => '07.00', 'jam_mulai' => '07:00:01', 'jam_selesai' => '08:00:00');

        $jam[] = array('id' => 2, 'nama' => '08.00', 'jam_mulai' => '08:00:01', 'jam_selesai' => '09:00:00');

        $jam[] = array('id' => 3, 'nama' => '09.00', 'jam_mulai' => '09:00:01', 'jam_selesai' => '10:00:00');

        $jam[] = array('id' => 4, 'nama' => '10.00', 'jam_mulai' => '10:00:01', 'jam_selesai' => '11:00:00');

        $jam[] = array('id' => 5, 'nama' => '11.00', 'jam_mulai' => '11:00:01', 'jam_selesai' => '12:00:00');

        $jam[] = array('id' => 6, 'nama' => '12.00', 'jam_mulai' => '12:00:01', 'jam_selesai' => '13:00:00');

        $jam[] = array('id' => 7, 'nama' => '13.00', 'jam_mulai' => '13:00:01', 'jam_selesai' => '14:00:00'); 

        $jam[] = array('id' => 8, 'nama' => '14.00', 'jam_mulai' => '14:00:01', 'jam_selesai' => '15:00:00');

        $jam[] = array('id' => 9, 'nama' => '15.00', 'jam_mulai' => '15:00:01', 'jam_selesai' => '16:00:00');

        $jam[] = array('id' => 10, 'nama' => '16.00', 'jam_mulai' => '16:00:01', 'jam_selesai' => '17:00:00');

        $jam[] = array('id' => 11, 'nama' => '17.00', 'jam_mulai' => '17:00:01', 'jam_selesai' => '18:00:00');

        $jam[] = array('id' => 12, 'nama' => '18.00', 'jam_mulai' => '18:00:01', 'jam_selesai' => '19:00:00');

        $jam[] = array('id' => 13, 'nama' => '19.00', 'jam_mulai' => '19:00:01', 'jam_selesai' => '20:00:00');

        $jam[] = array('id' => 14, 'nama' => '20.00', 'jam_mulai' => '20:00:01', 'jam_selesai' => '21:00:00');

        $jam[] = array('id' => 15, 'nama' => '21.00', 'jam_mulai' => '21:00:01', 'jam_selesai' => '22:00:00');

        $jam[] = array('id' => 16, 'nama' => '22.00', 'jam_mulai' => '22:00:01', 'jam_selesai' => '23:00:00');

        $jam[] = array('id' => 17, 'nama' => '23.00', 'jam_mulai' => '23:00:01', 'jam_selesai' => '00:00:00');



        $label_ = array();

        $kunjungan_ = array();

        $all_kunjungan_ = array();

        $total_apotek = MasterApotek::where('is_deleted', 0)->count();

        $total_apotek = $total_apotek-2;





        foreach ($jam as $key => $obj) {

            array_push($label_, $obj['nama']);



            $hit_penjualan = TransaksiPenjualan::where('is_deleted', 0)

                                ->where('id_apotek_nota', session('id_apotek_active'))

                                ->whereTime('created_at', '>=', \Carbon\Carbon::parse($obj['jam_mulai']))

                                ->whereTime('created_at', '<=', \Carbon\Carbon::parse($obj['jam_selesai']))

                                ->whereDate('created_at','>=', $tgl_awal)

                                ->whereDate('created_at','<=', $tgl_akhir)

                                ->count();



            $hit_penjualan_all = TransaksiPenjualan::where('is_deleted', 0)

                                ->whereTime('created_at', '>=', \Carbon\Carbon::parse($obj['jam_mulai']))

                                ->whereTime('created_at', '<=', \Carbon\Carbon::parse($obj['jam_selesai']))

                                ->whereDate('created_at','>=', $tgl_awal)

                                ->whereDate('created_at','<=', $tgl_akhir)

                                ->count();



            $total_kunjungan = $hit_penjualan;

            $total_all_kunjungan = $hit_penjualan_all/$total_apotek;

            $total_kunjungan = number_format($total_kunjungan,0);

            $total_all_kunjungan = number_format($total_all_kunjungan,0);

            $total_kunjungan = (int) $total_kunjungan;

            $total_all_kunjungan = (int) $total_all_kunjungan;

            array_push($kunjungan_, $total_kunjungan);

            array_push($all_kunjungan_, $total_all_kunjungan);

        }



     

        $kunjungan = $app->make('stdClass');

        $kunjungan->label = $label_;

        $kunjungan->kunjungan = $kunjungan_;//array(10,20,30,40,50,60,70);

        $kunjungan->all_kunjungan = $all_kunjungan_;

        $data_->kunjungan = $kunjungan;

        //dd($data_);exit();



        return response()->json($data_);

    }



    public function cek_data() {

        $inisial = strtolower('pjm');



        $get_noid = DB::select('SELECT t2.id_obat,t2.request_id, t2.id_jenis_transaksi, t2.`hb_ppn`, b.`harga_beli_ppn` FROM (

        SELECT t1.id_obat,t1.request_id, a.id_jenis_transaksi, a.`hb_ppn` FROM (SELECT id_obat, request_id

        FROM (

            SELECT id_obat, MAX(id) AS request_id

            FROM tb_histori_stok_'.$inisial.'

            WHERE `id_jenis_transaksi` IN (2,3) 

            GROUP BY id_obat DESC) AS ids

        ORDER BY id_obat) AS t1

        JOIN tb_histori_stok_'.$inisial.' AS a

            ON a.id = t1.request_id) AS t2

            JOIN tb_m_stok_harga_'.$inisial.' AS b

            ON b.id_obat = t2.id_obat

            WHERE t2.`hb_ppn` != b.`harga_beli_ppn`');

        //$list_noid = join(',',array_map('current', $get_noid));

        //dd($get_noid);

        $i = 0;

        foreach ($get_noid as $key => $obj) {

           /* $cek = MasterStokHarga::where('id_obat', $obj->id_obat)->first();

            $cek->harga_beli_ppn = $obj->hb_ppn;

            $cek->save();*/



            DB::table('tb_m_stok_harga_'.$inisial)->update(['harga_beli_ppn' => $obj->hb_ppn]);

            $i++;

        }

        echo $i;

    }

    

    public function getInformasi(Request $request){

        $string = $request->url;

        $prefix = url('/');

        $index = strpos($string, $prefix) + strlen($prefix);

        $result = substr($string, $index);

        $result = str_replace("#","",$result);

        $result = ''.$result.'';



        $menu = RbacMenu::where('link', '=', $result)->first();

        if(is_null($menu)){

            $menu = new RbacMenu;

        }

        

        return view('informasi')->with(compact('menu'));

    }



    public function getLogBook(Request $request){

        $string = $request->url;

        $prefix = url('/');

        $index = strpos($string, $prefix) + strlen($prefix);

        $result = substr($string, $index);

        $result = str_replace("#","",$result);

        $result = ''.$result.'';



        $menu = RbacMenu::where('link', '=', $result)->first();

        if(is_null($menu)){

            $menu = new RbacMenu;

        }

        return view('error_book')->with(compact('menu'));

    }



    public function getFAQ(Request $request){

        $string = $request->url;

        $prefix = url('/');

        $index = strpos($string, $prefix) + strlen($prefix);

        $result = substr($string, $index);

        $result = str_replace("#","",$result);

        $result = ''.$result.'';



        $menu = RbacMenu::where('link', '=', $result)->first();

        if(is_null($menu)){

            $menu = new RbacMenu;

        }

        return view('faq')->with(compact('menu'));

    }



     public function FileAccess($id, $cstmUrl, $filename)

    {

        $id = Crypt::decryptString($id);

        $cstmUrl = Crypt::decryptString($cstmUrl);

        $filename = Crypt::decryptString($filename);

        //dd($filename);

        //dd($cstmUrl);



        $split = explode('.' , $filename);

        $ext = end($split);

        //dd($ext);

        if(in_array($ext, array('jpeg', 'jpg', 'png', 'PNG', 'JPG', 'JPEG'))) {

            $ContentType = 'image/jpeg';

        } else  if(in_array($ext, array('doc'))) {

            $ContentType = 'application/msword';

        } else if(in_array($ext, array('docx'))) {

            $ContentType = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';

        } else if(in_array($ext, array('pdf'))) {

            $ContentType = 'application/pdf';

        } else {

            return view('page_not_found');

        }

        //dd($ContentType);



        $file = storage_path('userfiles/'.$cstmUrl.'/'.$filename);

        //dd($file);

        if(!File::exists( $file )) {

            return view('page_not_found');

        } else {



            //return Image::make($profile_picture_url)->response();

            $fp = fopen($file, 'rb');



            // send the right headers

            header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');

            header('Expires: January 01, 2013'); // Date in the past

            header('Pragma: no-cache');

            header("Content-Type: ".$ContentType);

            /* header("Content-Length: " . filesize($name)); */



            // dump the picture and stop the script

            fpassthru($fp);

            exit();

        }

    }



    public function get_pengumuman_popup(Request $request) {

        $currentPage = $request->page_num;



        //currentPathResolver : jika menggunakan link

        Paginator::currentPageResolver(function() use ($currentPage) {

            return $currentPage;

        });

        

        $date_now = date('Y-m-d');

        $data_ = DB::table('tb_pengumuman')

                ->where(function ($query) use($date_now){

                    $query->where('tanggal_mulai','>=', $date_now)

                        ->where('tanggal_mulai','<=', $date_now)

                        ->where('is_deleted', 0)

                        ->where('show_popup',1);

                })

                ->orwhere(function ($query) use($date_now){

                    $query->where('tanggal_selesai','>=', $date_now)

                        ->where('tanggal_selesai','<=', $date_now)

                        ->where('is_deleted', 0)

                        ->where('show_popup',1);

                })

                ->orwhere(function ($query) use($date_now){

                    $query->where('tanggal_mulai','<=', $date_now)

                        ->where('tanggal_selesai','>=', $date_now)

                        ->where('is_deleted', 0)

                        ->where('show_popup',1);

                });



        $data_ = $data_->get();



        $pengumumans = collect();

        foreach ($data_ as $x => $obj) {

            $data_arr = $obj;

            foreach ($data_arr as $key => $value) {

                if ($key=='created_at'){

                    $data_arr->$key = ($value!='' && $value!='0000-00-00'?Carbon::parse($value)->format('d/m/Y H:i:s'):'');

                }

                else{

                    $data_arr->$key = ($value!=null?$value:'');

                }



                if (substr($key, 0,5)=='file_') {

                    $data_arr->$key = Common::get_link_file($value,'Download');

                }

                else{

                    $data_arr->$key = ($value!=null?$value:'');

                }

            }



            if(in_array(session('id_role_active'), json_decode($data_arr->id_role_penerima))){

                $pengumumans[] = $data_arr;

            }   

        }



        $pengumumans = (new Collection($pengumumans))->paginate(5);



        $jumlah_data = count($pengumumans);



        if($jumlah_data > 0){

            return view("home_pengumuman_popup")->with(compact('pengumumans', 'jumlah_data'));

        }

        else{

            return "";

        }

    }

}

