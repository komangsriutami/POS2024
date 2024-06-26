<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\MasterApotek;
use App\MasterObat;
use DB;

class HistoriCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'histori:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        /*$obat = MasterObat::select(['id'])->orderBy('id', 'DESC')->first();
        $last_id_obat = $obat->id;
        $last_id_obat_ex = 0;
        $id_apotek = 1;
        $skip = 0;
        $cek = DB::table('tb_bantu_update')->orderBy('id', 'DESC')->first();
        if(!empty($cek)) {
            $last_id_obat_ex = $cek->last_id_obat_after;
            if($last_id_obat_ex >= $last_id_obat) {
                $id_apotek = $cek->id_apotek+1;
                $last_id_obat_ex = 0;
                $last_id_obat_ex = $last_id_obat_ex+1;
                $last_id_obat_after = $last_id_obat_ex+250-1;
            } else {
                $id_apotek = $cek->id_apotek;
                $last_id_obat_ex = $last_id_obat_ex+1;
                $last_id_obat_after = $last_id_obat_ex+250-1;
            }
            $apotek = MasterApotek::find($cek->id_apotek);
            if(!empty($apotek)) {
                $inisial = strtolower($apotek->nama_singkat);
            } else {
                $skip = 1;
            }
        } else {
            $apotek = MasterApotek::find($id_apotek);
            $inisial = strtolower($apotek->nama_singkat);
            $last_id_obat_ex = $last_id_obat_ex+1;
            $last_id_obat_after = $last_id_obat_ex+250-1;
        }

        if($skip != 1) {
            DB::table('tb_bantu_update')
                ->insert(['last_id_obat_before' => $last_id_obat_ex, 'last_id_obat_after' => $last_id_obat_after, 'id_apotek' => $id_apotek]);
            
            $data = DB::table('tb_m_stok_harga_'.$inisial.'')->whereBetween('id_obat', [$last_id_obat_ex, $last_id_obat_after])->get();
            $i=0;
            $data_ = array();
            $now = date('Y-m-d');
            foreach ($data as $key => $val) {
                # data pembelian obat keseluruhan
                $det_pembelians = DB::table('tb_detail_nota_pembelian') 
                                    ->select(['tb_detail_nota_pembelian.*', 'tb_nota_pembelian.ppn'])
                                    ->leftJoin('tb_nota_pembelian', 'tb_nota_pembelian.id', '=', 'tb_detail_nota_pembelian.id_nota')
                                    ->where('id_obat', $val->id_obat)
                                    ->where('id_apotek_nota', $apotek->id)
                                    ->whereDate('tb_detail_nota_pembelian.created_at', '<', $now)
                                    ->get();
                
                foreach ($det_pembelians as $key => $val) {
                    $new_arr = array();
                    $new_arr['id_obat'] = $val->id_obat;
                    $new_arr['harga_beli'] = $val->harga_beli;
                    $new_arr['ppn'] = $val->ppn;
                    if($val->ppn != null AND $val->ppn > 0) {
                        $new_arr['harga_beli_ppn'] = $val->harga_beli+(($val->ppn/100)*$val->harga_beli);
                    } else {
                        $new_arr['harga_beli_ppn'] = $val->harga_beli;
                    }
                    $new_arr['harga_jual'] = null;
                    $new_arr['harga_transfer'] = null;
                    $new_arr['jumlah'] = $val->jumlah;
                    $new_arr['stok_awal'] = 0;
                    $new_arr['stok_akhir'] = 0;
                    $new_arr['id_jenis_transaksi'] = 2;
                    $new_arr['id_transaksi'] = $val->id;
                    $new_arr['batch'] = $val->id_batch;
                    $new_arr['ed'] = $val->tgl_batch;
                    $new_arr['created_at'] = $val->created_at; 
                    $new_arr['created_by'] = $val->created_by;
                    array_push($data_, $new_arr);
                }

                # data pembelian obat dihapus
                $det_pembelian_hapuss = DB::table('tb_detail_nota_pembelian')
                                    ->select(['tb_detail_nota_pembelian.*', 'tb_nota_pembelian.ppn'])
                                    ->leftJoin('tb_nota_pembelian', 'tb_nota_pembelian.id', '=', 'tb_detail_nota_pembelian.id_nota')
                                    ->where('id_obat', $val->id_obat)
                                    ->where('id_apotek_nota', $apotek->id)
                                    ->whereDate('tb_detail_nota_pembelian.created_at', '<', $now)
                                    ->where('tb_nota_pembelian.is_deleted', 1)
                                    ->get();

                foreach ($det_pembelian_hapuss as $key => $val) {
                    $new_arr = array();
                    $new_arr['id_obat'] = $val->id_obat;
                    $new_arr['harga_beli'] = $val->harga_beli;
                    $new_arr['ppn'] = $val->ppn;
                    if($val->ppn != null AND $val->ppn > 0) {
                        $new_arr['harga_beli_ppn'] = $val->harga_beli+(($val->ppn/100)*$val->harga_beli);
                    } else {
                        $new_arr['harga_beli_ppn'] = $val->harga_beli;
                    }
                    $new_arr['harga_jual'] = null;
                    $new_arr['harga_transfer'] = null;
                    $new_arr['jumlah'] = $val->jumlah;
                    $new_arr['stok_awal'] = 0;
                    $new_arr['stok_akhir'] = 0;
                    $new_arr['id_jenis_transaksi'] = 14;
                    $new_arr['id_transaksi'] = $val->id;
                    $new_arr['batch'] = $val->id_batch;
                    $new_arr['ed'] = $val->tgl_batch;
                    $new_arr['created_at'] = $val->created_at; 
                    $new_arr['created_by'] = $val->created_by;
                    array_push($data_, $new_arr);
                }

                // -----------------------------------------------------------------------------------
                # data penjualan obat keseluruhan
                $det_penjualans = DB::table('tb_detail_nota_penjualan')
                                    ->select(['tb_detail_nota_penjualan.*'])
                                    ->leftJoin('tb_nota_penjualan', 'tb_nota_penjualan.id', '=', 'tb_detail_nota_penjualan.id_nota')
                                    ->where('id_obat', $val->id_obat)
                                    ->where('id_apotek_nota', $apotek->id)
                                    ->whereDate('tb_detail_nota_penjualan.created_at', '<', $now)
                                    ->get();

                foreach ($det_penjualans as $key => $val) {
                    $new_arr = array();
                    $new_arr['id_obat'] = $val->id_obat;
                    $new_arr['harga_beli'] = null;
                    $new_arr['ppn'] = null;
                    $new_arr['harga_beli_ppn'] = null;
                    $new_arr['harga_jual'] = $val->harga_jual;
                    $new_arr['harga_transfer'] = null;
                    $new_arr['jumlah'] = $val->jumlah;
                    $new_arr['stok_awal'] = 0;
                    $new_arr['stok_akhir'] = 0;
                    $new_arr['id_jenis_transaksi'] = 1;
                    $new_arr['id_transaksi'] = $val->id;
                    $new_arr['batch'] = null;
                    $new_arr['ed'] = null;
                    $new_arr['created_at'] = $val->created_at; 
                    $new_arr['created_by'] = $val->created_by;
                    array_push($data_, $new_arr);
                }

                # data penjualan obat dihapus
                $det_penjualan_hapuss = DB::table('tb_detail_nota_penjualan')
                                    ->select(['tb_detail_nota_penjualan.*'])
                                    ->leftJoin('tb_nota_penjualan', 'tb_nota_penjualan.id', '=', 'tb_detail_nota_penjualan.id_nota')
                                    ->where('id_obat', $val->id_obat)
                                    ->where('id_apotek_nota', $apotek->id)
                                    ->whereDate('tb_detail_nota_penjualan.created_at', '<', $now)
                                    ->where('tb_nota_penjualan.is_deleted', 1)
                                    ->get();

                foreach ($det_penjualan_hapuss as $key => $val) {
                    $new_arr = array();
                    $new_arr['id_obat'] = $val->id_obat;
                    $new_arr['harga_beli'] = null;
                    $new_arr['ppn'] = null;
                    $new_arr['harga_beli_ppn'] = null;
                    $new_arr['harga_jual'] = $val->harga_jual;
                    $new_arr['harga_transfer'] = null;
                    $new_arr['jumlah'] = $val->jumlah;
                    $new_arr['stok_awal'] = 0;
                    $new_arr['stok_akhir'] = 0;
                    $new_arr['id_jenis_transaksi'] = 15;
                    $new_arr['id_transaksi'] = $val->id;
                    $new_arr['batch'] = null;
                    $new_arr['ed'] = null;
                    $new_arr['created_at'] = $val->created_at; 
                    $new_arr['created_by'] = $val->created_by;
                    array_push($data_, $new_arr);
                }

                // -----------------------------------------------------------------------------------
                # data transfer masuk obat keseluruhan
                $det_transfer_masuks = DB::table('tb_detail_nota_transfer_outlet')
                                    ->select(['tb_detail_nota_transfer_outlet.*'])
                                    ->leftJoin('tb_nota_transfer_outlet', 'tb_nota_transfer_outlet.id', '=', 'tb_detail_nota_transfer_outlet.id_nota')
                                    ->where('id_obat', $val->id_obat)
                                    ->where('id_apotek_tujuan', $apotek->id)
                                    ->whereDate('tb_detail_nota_transfer_outlet.created_at', '<', $now)
                                    ->get();

                foreach ($det_transfer_masuks as $key => $val) {
                    $new_arr = array();
                    $new_arr['id_obat'] = $val->id_obat;
                    $new_arr['harga_beli'] = null;
                    $new_arr['ppn'] = null;
                    $new_arr['harga_beli_ppn'] = null;
                    $new_arr['harga_jual'] = null;
                    $new_arr['harga_transfer'] = $val->harga_outlet;
                    $new_arr['jumlah'] = $val->jumlah;
                    $new_arr['stok_awal'] = 0;
                    $new_arr['stok_akhir'] = 0;
                    $new_arr['id_jenis_transaksi'] = 3;
                    $new_arr['id_transaksi'] = $val->id;
                    $new_arr['batch'] = null;
                    $new_arr['ed'] = null;
                    $new_arr['created_at'] = $val->created_at; 
                    $new_arr['created_by'] = $val->created_by;
                    array_push($data_, $new_arr);
                }

                # data transfer masuk obat dihapus
                $det_transfer_masuk_hapuss = DB::table('tb_detail_nota_transfer_outlet')
                                    ->select(['tb_detail_nota_transfer_outlet.*'])
                                    ->leftJoin('tb_nota_transfer_outlet', 'tb_nota_transfer_outlet.id', '=', 'tb_detail_nota_transfer_outlet.id_nota')
                                    ->where('id_obat', $val->id_obat)
                                    ->where('id_apotek_tujuan', $apotek->id)
                                    ->whereDate('tb_detail_nota_transfer_outlet.created_at', '<', $now)
                                    ->where('tb_nota_transfer_outlet.is_deleted', 1)
                                    ->get();

                foreach ($det_transfer_masuk_hapuss as $key => $val) {
                    $new_arr = array();
                    $new_arr['id_obat'] = $val->id_obat;
                    $new_arr['harga_beli'] = null;
                    $new_arr['ppn'] = null;
                    $new_arr['harga_beli_ppn'] = null;
                    $new_arr['harga_jual'] = null;
                    $new_arr['harga_transfer'] = $val->harga_outlet;
                    $new_arr['jumlah'] = $val->jumlah;
                    $new_arr['stok_awal'] = 0;
                    $new_arr['stok_akhir'] = 0;
                    $new_arr['id_jenis_transaksi'] = 16;
                    $new_arr['id_transaksi'] = $val->id;
                    $new_arr['batch'] = null;
                    $new_arr['ed'] = null;
                    $new_arr['created_at'] = $val->created_at; 
                    $new_arr['created_by'] = $val->created_by;
                    array_push($data_, $new_arr);
                }

                // -----------------------------------------------------------------------------------
                # data transfer keluar obat keseluruhan
                $det_transfer_keluars = DB::table('tb_detail_nota_transfer_outlet')
                                    ->select(['tb_detail_nota_transfer_outlet.*'])
                                    ->leftJoin('tb_nota_transfer_outlet', 'tb_nota_transfer_outlet.id', '=', 'tb_detail_nota_transfer_outlet.id_nota')
                                    ->where('id_obat', $val->id_obat)
                                    ->where('id_apotek_nota', $apotek->id)
                                    ->whereDate('tb_detail_nota_transfer_outlet.created_at', '<', $now)
                                    ->get();

                foreach ($det_transfer_keluars as $key => $val) {
                    $new_arr = array();
                    $new_arr['id_obat'] = $val->id_obat;
                    $new_arr['harga_beli'] = null;
                    $new_arr['ppn'] = null;
                    $new_arr['harga_beli_ppn'] = null;
                    $new_arr['harga_jual'] = null;
                    $new_arr['harga_transfer'] = $val->harga_outlet;
                    $new_arr['jumlah'] = $val->jumlah;
                    $new_arr['stok_awal'] = 0;
                    $new_arr['stok_akhir'] = 0;
                    $new_arr['id_jenis_transaksi'] = 4;
                    $new_arr['id_transaksi'] = $val->id;
                    $new_arr['batch'] = null;
                    $new_arr['ed'] = null;
                    $new_arr['created_at'] = $val->created_at; 
                    $new_arr['created_by'] = $val->created_by;
                    array_push($data_, $new_arr);
                }

                # data transfer keluar obat dihapus
                $det_transfer_keluar_hapuss = DB::table('tb_detail_nota_transfer_outlet')
                                    ->select(['tb_detail_nota_transfer_outlet.*'])
                                    ->leftJoin('tb_nota_transfer_outlet', 'tb_nota_transfer_outlet.id', '=', 'tb_detail_nota_transfer_outlet.id_nota')
                                    ->where('id_obat', $val->id_obat)
                                    ->where('id_apotek_nota', $apotek->id)
                                    ->whereDate('tb_detail_nota_transfer_outlet.created_at', '<', $now)
                                    ->where('tb_nota_transfer_outlet.is_deleted', 1)
                                    ->get();

                foreach ($det_transfer_masuk_hapuss as $key => $val) {
                    $new_arr = array();
                    $new_arr['id_obat'] = $val->id_obat;
                    $new_arr['harga_beli'] = null;
                    $new_arr['ppn'] = null;
                    $new_arr['harga_beli_ppn'] = null;
                    $new_arr['harga_jual'] = null;
                    $new_arr['harga_transfer'] = $val->harga_outlet;
                    $new_arr['jumlah'] = $val->jumlah;
                    $new_arr['stok_awal'] = 0;
                    $new_arr['stok_akhir'] = 0;
                    $new_arr['id_jenis_transaksi'] = 17;
                    $new_arr['id_transaksi'] = $val->id;
                    $new_arr['batch'] = null;
                    $new_arr['ed'] = null;
                    $new_arr['created_at'] = $val->created_at; 
                    $new_arr['created_by'] = $val->created_by;
                    array_push($data_, $new_arr);
                }

                # history lain-lain 
                $det_historis = DB::table('tb_histori_stok_'.$inisial.'')
                                    ->where('id_obat', $val->id_obat)
                                    ->whereNotIn('id_jenis_transaksi', [1, 2, 3, 4, 14, 15, 16, 17])
                                    ->whereDate('created_at', '<', $now)
                                    ->get();

                foreach ($det_historis as $key => $val) {
                    $new_arr = array();
                    $new_arr['id_obat'] = $val->id_obat;
                    $new_arr['harga_beli'] = null;
                    $new_arr['ppn'] = null;
                    $new_arr['harga_beli_ppn'] = null;
                    $new_arr['harga_jual'] = null;
                    $new_arr['harga_transfer'] = null;
                    $new_arr['jumlah'] = $val->jumlah;
                    $new_arr['stok_awal'] = $val->stok_awal;
                    $new_arr['stok_akhir'] = $val->stok_akhir;
                    $new_arr['id_jenis_transaksi'] = $val->id_jenis_transaksi;
                    $new_arr['id_transaksi'] = $val->id_transaksi;
                    $new_arr['batch'] = $val->batch;
                    $new_arr['ed'] = $val->ed;
                    $new_arr['created_at'] = $val->created_at; 
                    $new_arr['created_by'] = $val->created_by;
                    array_push($data_, $new_arr);
                }

                $i++;
            }

            if($i > 0) {
                foreach (array_chunk($data_,50) as $t) {
                   DB::table('tb_histori_all_'.$inisial.'')
                    ->insert($t);
                }
                
                \Log::info("Cron history is working fine! Reload data ".$last_id_obat_ex.' until '.$last_id_obat_after);
            } else {
                \Log::info("Cron history not working! Reload data ".$last_id_obat_ex.' until '.$last_id_obat_after);
            }
        } else {
            \Log::info("Cron history is working fine! Apotek tidak ditemukan.");
        }*/
        \Log::info("Cron history is working fine!");
    }
}
