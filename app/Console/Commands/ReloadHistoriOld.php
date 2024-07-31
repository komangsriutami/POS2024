<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\MasterApotek;
use App\MasterObat;
use App\TransaksiPenjualan;
use App\TransaksiPenjualanDetail;
use App\TransaksiPembelian;
use App\TransaksiPembelianDetail;
use App\TransaksiTO;
use App\TransaksiTODetail;
use DB;

class ReloadHistoriOld extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'histori_old:cron';

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
        $obat = MasterObat::select(['id'])->orderBy('id', 'DESC')->first();
        $last_id_obat = $obat->id;
        $last_id_obat_ex = 0;
        $id_apotek = 1;
        $skip = 0;
        $cek = DB::table('tb_bantu_update_old')->orderBy('id', 'DESC')->first();
        if(!empty($cek)) {
            $last_id_obat_ex = $cek->last_id_obat_after;
            if($last_id_obat_ex >= $last_id_obat) {
                $id_apotek = $cek->id_apotek+1;
                $last_id_obat_ex = 0;
                $last_id_obat_ex = $last_id_obat_ex+1;
                $last_id_obat_after = $last_id_obat_ex+300-1;
            } else {
                $id_apotek = $cek->id_apotek;
                $last_id_obat_ex = $last_id_obat_ex+1;
                $last_id_obat_after = $last_id_obat_ex+300-1;
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
            $last_id_obat_after = $last_id_obat_ex+300-1;
        }

        if($skip != 1) {
            DB::table('tb_bantu_update_old')
                ->insert(['last_id_obat_before' => $last_id_obat_ex, 'last_id_obat_after' => $last_id_obat_after, 'id_apotek' => $id_apotek]);
            
            $data = DB::table('tb_m_stok_harga_'.$inisial.'')->whereBetween('id_obat', [$last_id_obat_ex, $last_id_obat_after])->get();
            $i=0;
            $data_ = array();
            $now = date('Y-m-d');
            foreach ($data as $key => $obj) {
                $det_historis = DB::table('tb_histori_all_'.$inisial.'')->where('id_obat', $obj->id_obat)->whereIn('id_jenis_transaksi', [1, 2, 3, 4, 14, 15, 16, 17])->get();
                foreach ($det_historis as $key => $val) {
                    if($val->id_jenis_transaksi == 1) {
                        # penjualan
                        $det_  = TransaksiPenjualanDetail::find($val->id_transaksi);
                        $nota = $det_->nota;
                        $created_at = $nota->created_at;
                    } else if($val->id_jenis_transaksi == 2) {
                        # pembelian
                        $det_  = TransaksiPembelianDetail::find($val->id_transaksi);
                        $nota = $det_->nota;
                        $created_at = $nota->created_at;
                    } else if($val->id_jenis_transaksi == 3) {
                        # transfer masuk
                        $det_  = TransaksiTODetail::find($val->id_transaksi);
                        $nota = $det_->nota;
                        $created_at = $nota->created_at;
                    } else if($val->id_jenis_transaksi == 4) {
                        # transfer keluar
                        $det_  = TransaksiTODetail::find($val->id_transaksi);
                        $nota = $det_->nota;
                        $created_at = $nota->created_at;
                    } else if($val->id_jenis_transaksi == 14) {
                        # hapus pembelian
                        $det_  = TransaksiPembelianDetail::find($val->id_transaksi);
                        $nota = $det_->nota;
                        $created_at = $nota->deleted_at;
                    } else if($val->id_jenis_transaksi == 15) {
                        # hapus penjualan
                        $det_  = TransaksiPenjualanDetail::find($val->id_transaksi);
                        $nota = $det_->nota;
                        $created_at = $nota->deleted_at;
                    } else if($val->id_jenis_transaksi == 16) {
                        # hapus transfer masuk
                        $det_  = TransaksiTODetail::find($val->id_transaksi);
                        $nota = $det_->nota;
                        $created_at = $nota->deleted_at;
                    } else if($val->id_jenis_transaksi == 17) {
                        # hapus transfre keluar
                        $det_  = TransaksiTODetail::find($val->id_transaksi);
                        $nota = $det_->nota;
                        $created_at = $nota->deleted_at;
                    }

                    $cek = DB::table('tb_histori_stok_'.$inisial.'')->where('id_obat', $val->id_obat)
                                ->where('id_jenis_transaksi', $val->id_jenis_transaksi)
                                ->where('id_transaksi', $val->id_transaksi)
                                ->first();

                    $stok_awal = 0;
                    $stok_akhir = 0;
                    if(!empty($cek)) {
                        $stok_awal = $cek->stok_awal;
                        $stok_akhir = $cek->stok_akhir;
                    }

                    DB::table('tb_histori_all_'.$inisial.'')
                        ->where('id_obat', $val->id_obat)
                        ->where('id_jenis_transaksi', $val->id_jenis_transaksi)
                        ->where('id_transaksi', $val->id_transaksi)
                        ->update(['stok_awal' => $stok_akhir, 'stok_akhir' => $stok_akhir, 'created_at' => $created_at]);
                }

                $i++;
            }

            if($i > 0) {
                \Log::info("Cron history is working fine! Reload data ".$last_id_obat_ex.' until '.$last_id_obat_after);
            } else {
                \Log::info("Cron history not working! Reload data ".$last_id_obat_ex.' until '.$last_id_obat_after);
            }
        } else {
            \Log::info("Cron history is working fine! Apotek tidak ditemukan.");
        }
    }
}
