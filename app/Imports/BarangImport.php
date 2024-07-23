<?php

namespace App\Imports;

use App\User;
use App\MasterObat;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

use DB;
use Crypt;
use Auth;
class BarangImport implements  ToCollection,WithHeadingRow
{
    public function __construct()
    {
       $this->importstatus = array();
    }

    public function collection(Collection $rows)
    {
        $berhasil = 0;
        $gagal = 0;

        // dd($rows);
        if(count($rows)){
            foreach ($rows as $key => $value) {
               // dd($value);exit();
                // cek kalau ada update kalau tidak insert //
                $cekdetail = MasterObat::whereRaw('id = \''.$value['id'].'\'')
                            ->first();

    
                if(is_null($cekdetail)){
                    //$detail = new MasterBarang;
                   // $detail->setDynamicConnection();

                    $gagal++;
                } else {    
                    $detail = MasterObat::on($this->getConnectionName())->find($cekdetail->id);
                    $detail->sku = $value['sku'];
                    $detail->updated_at = date('Y-m-d H:i:s');
                    $detail->updated_by = Auth::id();

                    if($detail->save()){
                        $berhasil++;
                    } else {
                        $gagal++;
                    }
                }
            }
        }
        
        return $this->importstatus = array("status"=>1, "berhasil" => $berhasil, "gagal" => $gagal);
    }
}
