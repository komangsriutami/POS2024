<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\Exportable;

class JurnalUmumTemplateExportSheet implements WithMultipleSheets
{
    use Exportable;
    
    public function __construct()
    {
       
    }

    public function sheets(): array
    {
        $sheet['glossarium'] = new JurnalUmumTemplateExport(1);
        $sheet['template'] = new JurnalUmumTemplateExport(2);
        return $sheet;
    }

}