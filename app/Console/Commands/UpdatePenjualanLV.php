<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\MasterApotek;
use App\MasterObat;
use App\TransaksiPenjualanDetail;
use DB;

class UpdatePenjualanLV extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updatepenjualanlv:cron';

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

        $penjualan = TransaksiPenjualanDetail::select(['id'])->orderBy('id', 'DESC')->first();
        $last_id_obat = $penjualan->id;
        $last_id_obat_ex = 0;
        $id_apotek = 1;
        $skip = 0;
        $cek = DB::table('tb_bantu_transaksi_update_lv')->orderBy('id', 'DESC')->first();
        if(!empty($cek)) {
            $last_id_obat_ex = $cek->last_id_obat_after;
            if($last_id_obat_ex >= $last_id_obat) {
                $id_apotek = $cek->id_apotek+1;
                $last_id_obat_ex = 0;
                $last_id_obat_ex = $last_id_obat_ex+1;
                $last_id_obat_after = $last_id_obat_ex+10-1;
            } else {
                $id_apotek = $cek->id_apotek;
                $last_id_obat_ex = $last_id_obat_ex+1;
                $last_id_obat_after = $last_id_obat_ex+10-1;
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
            $last_id_obat_after = $last_id_obat_ex+10-1;
        }

        if($skip != 1) {
            DB::table('tb_bantu_transaksi_update_lv')
                ->insert(['last_id_obat_before' => $last_id_obat_ex, 'last_id_obat_after' => $last_id_obat_after, 'id_apotek' => $id_apotek, 'created_at' => date('Y-m-d H:i:s')]);
            
            $data = TransaksiPenjualanDetail::select(['tb_detail_nota_penjualan.*', 'a.created_at as tgl_nota_buat'])
                                            ->join('tb_nota_penjualan as a', 'a.id', '=', 'tb_detail_nota_penjualan.id_nota')
                                            ->where('a.id_apotek_nota', 1)
                                            ->whereBetween('id', [$last_id_obat_ex, $last_id_obat_after])
                                            ->get();
            $i=0;
            $data_ = array();
            $now = date('Y-m-d');
            foreach ($data as $key => $val) {
                # data pembelian obat keseluruhan
                $cek_last_histori = DB::table('tb_histori_all_'.$inisial.'')->where('created_at', '>', $val->tgl_nota_buat)->orderBy('created_at', 'DESC')->first();

                if(!empty($cek_last_histori)) {
                    TransaksiPenjualanDetail::where('id', $val->id)->update(['harga_beli_ppn' => $cek_last_histori->harga_beli_ppn]);
                }
                

                $i++;
            }

            if($i > 0) {
                \Log::info("Cron history is working fine! Update data penjualan ".$last_id_obat_ex.' until '.$last_id_obat_after);
            } else {
                \Log::info("Cron history not working! Update data penjualan ".$last_id_obat_ex.' until '.$last_id_obat_after);
            }
        } else {
            \Log::info("Cron history is working fine! Update data penjualan : Apotek tidak ditemukan.");
        }
        \Log::info("Cron update data penjualan is working fine!");
    }
}
