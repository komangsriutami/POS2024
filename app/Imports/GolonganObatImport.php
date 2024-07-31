<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use App\User;
use App\MasterObat;
use Illuminate\Support\Collection;

class GolonganObatImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) 
        {
            MasterObat::where('id', $row[0])
            ->update([
                'id_penandaan_obat' => $row[2], 
                'id_golongan_obat' => $row[3], 
                'stok_setting' => $row[4],
            ]);
        }


    }
}
