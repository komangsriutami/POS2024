<?php

namespace App\Listeners;

use App\Events\ObatCreate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\MasterApotek;
use DB;
use Auth;
class AddObatOutlet
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
     * @param  ObatCreate  $event
     * @return void
     */
    public function handle(ObatCreate $event)
    {
        $apoteks = MasterApotek::where('is_deleted', 0)->get();
        foreach ($apoteks as $key => $apotek) {
            $inisial = strtolower($apotek->nama_singkat);
            $cek = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $event->obat->id_obat)->first();
            
            if(empty($cek)) {
                # insert data ke master stok dan harga
                DB::table('tb_m_stok_harga_'.$inisial)->insert([
                    'id_obat' => $event->obat->id_obat,
                    'stok_awal' => 0,
                    'stok_akhir' => 0,
                    'harga_beli' => $event->obat->harga_beli,
                    'harga_jual' => $event->obat->harga_jual,
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => Auth::user()->id,
                    'is_sync' => 1,
                    'sync_at' => date('Y-m-d H:i:s'),
                    'sync_by' => Auth::user()->id,
                    'total_buffer' => 0,
                    'forcasting' => 0,
                    'last_hitung' => date('Y-m-d H:i:s'),
                    'is_defecta' => 0,
                    'selisih' => 0
                ]);
            }
        }
    }
}
