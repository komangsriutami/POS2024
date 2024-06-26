<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\Exportable;

class BiayaTemplateExportSheet implements WithMultipleSheets
{
    use Exportable;
    
    public function __construct()
    {
       
    }

    public function sheets(): array
    {
        $sheet['glossarium'] = new BiayaTemplateExport(1);
        $sheet['template'] = new BiayaTemplateExport(2);
        return $sheet;
    }

}