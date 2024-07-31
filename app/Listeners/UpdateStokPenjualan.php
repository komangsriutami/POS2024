<?php

namespace App\Listeners;

use App\Events\PenjualanCreate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\MasterApotek;
use DB;
use Auth;
class UpdateStokPenjualan
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  PenjualanCreate  $event
     * @return void
     */
    public function handle(PenjualanCreate $event)
    {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $stok_before = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $event->detail_penjualan->id_obat)->first();
        $stok_now = $stok_before->stok_akhir-$event->detail_penjualan->jumlah;

        # update ke table stok harga
        DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $event->detail_penjualan->id_obat)->update(['stok_awal'=> $stok_before->stok_akhir, 'stok_akhir'=> $stok_now, 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => Auth::user()->id]);

        # create histori
        DB::table('tb_histori_stok_'.$inisial)->insert([
            'id_obat' => $event->detail_penjualan->id_obat,
            'jumlah' => $event->detail_penjualan->jumlah,
            'stok_awal' => $stok_before->stok_akhir,
            'stok_akhir' => $stok_now,
            'id_jenis_transaksi' => '1', //penjualan
            'id_transaksi' => $event->detail_penjualan->id,
            'batch' => null,
            'ed' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => Auth::user()->id
        ]);
    }
}
