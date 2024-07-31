<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class JurnalUmumImportSheet implements WithMultipleSheets
{
    public $importstatus = array();

    public function sheets(): array
    {
        $importstatus[0] = new JurnalUmumImport(1);
        $importstatus[1] = new JurnalUmumImport(2);

        $this->importstatus = $importstatus;

        return $importstatus;
    }

    public function getSatus(): array
    {
        return $this->importstatus;
    }
}
