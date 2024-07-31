<?php

namespace App\Imports;

use App\User;
use App\MasterObat;
use App\MasterApotek;
use DB;
use Auth;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class HJStaticImport implements  ToCollection,WithHeadingRow
{
    public function __construct()
    {
       $this->importstatus = array();
    }

    public function collection(Collection $rows)
    {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);

        $berhasil = 0;
        $gagal = 0;

        // dd($rows);
        if(count($rows)){
            foreach ($rows as $key => $value) {
               // dd($value);exit();
                // cek kalau ada update kalau tidak insert //
               // if($value['sh'] == 1) {
                    $cek = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $value['id'])->first();
                    DB::table('tb_m_stok_harga_'.$inisial.'')
                        ->where('id_obat', $value['id'])
                        ->update(['is_status_harga' => 0, 'harga_jual' =>$value['hjupdate'], 'status_harga_by' => Auth::id(), 'status_harga_at' => date('Y-m-d H:i:s')]);

                    $data_histori_ = array('id_obat' => $value['id'], 'harga_beli_awal' => $cek->harga_beli_ppn, 'harga_beli_akhir' => $cek->harga_beli_ppn, 'harga_jual_awal' => $cek->harga_jual, 'harga_jual_akhir' => $value['hjupdate'], 'created_by' => Auth::id(), 'created_at' => date('Y-m-d H:i:s'));
                    DB::table('tb_histori_harga_'.$inisial.'')->insert($data_histori_);


                    $berhasil++;
                /*} else {
                    $gagal++;
                }*/
            }
        }
        
        return $this->importstatus = array("status"=>1, "berhasil" => $berhasil, "gagal" => $gagal);
    }
}
