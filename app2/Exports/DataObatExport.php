<?php

namespace App\Exports;

use App\MasterObat;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Events\AfterSheet;
use DB;

class DataObatExport implements FromCollection, WithColumnWidths, WithStyles, WithStartRow
{   
    use Exportable;

    public function __construct(string $inisial){
        $this->inisial = $inisial;
    }    

    public function startRow(): int
    {
        return 1;
    }


    /**
     * @return Builder
     */
    public function collection()
    {
        $inisial = $this->inisial;
        $rekaps = DB::connection($this->getConnectionDefault())->table('tb_m_stok_harga_'.$inisial.'')
                        ->select([
                            DB::raw('@rownum  := @rownum  + 1 AS no'),
                            'tb_m_stok_harga_'.$inisial.'.id_obat',
                            'tb_m_stok_harga_'.$inisial.'.barcode',
                            'tb_m_stok_harga_'.$inisial.'.nama',
                            'tb_m_stok_harga_'.$inisial.'.harga_beli_ppn',
                            'tb_m_stok_harga_'.$inisial.'.harga_jual',
                            'tb_m_stok_harga_'.$inisial.'.stok_akhir'
                        ])
                        ->where(function($query) use($inisial){
                            $query->where('tb_m_stok_harga_'.$inisial.'.is_deleted','=','0');
                        })
                        ->orderBy('tb_m_stok_harga_'.$inisial.'.id')
                        ->limit(10)
                        ->get();

        $collection = collect();
        $collection[] = array('No', 'ID', 'Barcode', 'Nama', 'Harga Beli PPN', 'Harga Jual', 'Stok Akhir'); //1
        $no = 0;
        $total = 0;
        foreach ($rekaps as $obj) {
            $no++;    
            $collection[] = array(
                $no,
                $obj->id_obat,
                $obj->barcode,
                $obj->nama,
                $obj->harga_beli_ppn,
                $obj->harga_jual,
                $obj->stok_akhir,
            );
        }

        return $collection;
    }


    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $cellRange = 'A1:G1'; // All headers
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(14);
            },
        ];
    }


    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 8,
            'C' => 15,
            'D' => 30,
            'E' => 15,
            'F' => 15,
            'G' => 12,    
        ];
    }

    public function styles(Worksheet $sheet)
    { 
        return [
            1    => ['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
            'A'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
            'B'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
            'E'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
            'F'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
            'G'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
        ];
    }
}
