<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BiayaImportSheet implements WithMultipleSheets
{
    public $importstatus = array();

    public function sheets(): array
    {
        $importstatus[0] = new BiayaImport(1);
        $importstatus[1] = new BiayaImport(2);

        $this->importstatus = $importstatus;

        return $importstatus;
    }

    public function getSatus(): array
    {
        return $this->importstatus;
    }
}
