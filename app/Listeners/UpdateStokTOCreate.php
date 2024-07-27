<?php

namespace App\Listeners;

use App\Events\TOCreate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\MasterApotek;
use DB;
use Auth;
class UpdateStokTOCreate
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
     * @param  TOCreate  $event
     * @return void
     */
     public function handle(TOCreate $event)
    {
        $apotek = MasterApotek::on($this->getConnectionName())->find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $stok_before = DB::connection($this->getConnectionDefault())->table('tb_m_stok_harga_'.$inisial)->where('id_obat', $event->detail_pembelian->id_obat)->first();
        $stok_now = $stok_before->stok_akhir+$event->detail_pembelian->jumlah;

        # update ke table stok harga
        DB::connection($this->getConnectionDefault())->table('tb_m_stok_harga_'.$inisial)->where('id_obat', $event->detail_pembelian->id_obat)->update(['stok_awal'=> $stok_before->stok_akhir, 'stok_akhir'=> $stok_now, 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => Auth::user()->id]);

        # create histori
        DB::connection($this->getConnectionDefault())->table('tb_histori_stok_'.$inisial)->insert([
            'id_obat' => $event->detail_pembelian->id_obat,
            'jumlah' => $event->detail_pembelian->jumlah,
            'stok_awal' => $stok_before->stok_akhir,
            'stok_akhir' => $stok_now,
            'id_jenis_transaksi' => 2, //pembelian
            'id_transaksi' => $event->detail_pembelian->id,
            'batch' => $event->detail_pembelian->id_batch,
            'ed' => $event->detail_pembelian->tgl_batch,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => Auth::user()->id
        ]);
    }
}
