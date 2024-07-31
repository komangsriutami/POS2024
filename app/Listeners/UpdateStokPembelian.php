<?php

namespace App\Listeners;

use App\Events\PembelianCreate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\MasterApotek;
use DB;
use Auth;
class UpdateStokPembelian
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
     * @param  PembelianCreate  $event
     * @return void
     */
    public function handle(PembelianCreate $event)
    {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $stok_before = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $event->detail_pembelian->id_obat)->first();
        $stok_now = $stok_before->stok_akhir+$event->detail_pembelian->jumlah;


        #histori harga
        if($stok_before->harga_beli != $event->detail_pembelian->harga_beli) {
            $data_histori_ = array('id_obat' => $event->detail_pembelian->id_obat, 'harga_beli_awal' => $stok_before->harga_beli, 'harga_beli_akhir' => $event->detail_pembelian->harga_beli, 'harga_jual_awal' => $stok_before->harga_jual, 'harga_jual_akhir' => $stok_before->harga_jual, 'created_by' => Auth::id(), 'created_at' => date('Y-m-d H:i:s'));

            DB::table('tb_histori_harga_'.$inisial.'')->insert($data_histori_);
        }

        # update ke table stok harga
        DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $event->detail_pembelian->id_obat)->update(['stok_awal'=> $stok_before->stok_akhir, 'stok_akhir'=> $stok_now, 'updated_at' => date('Y-m-d H:i:s'), 'harga_beli' => $event->detail_pembelian->harga_beli, 'harga_beli_ppn' => $event->detail_pembelian->harga_beli_ppn, 'updated_by' => Auth::user()->id]);

        # create histori
        DB::table('tb_histori_stok_'.$inisial)->insert([
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
