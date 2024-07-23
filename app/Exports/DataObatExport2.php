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

class DataObatExport2 implements FromCollection, WithColumnWidths, WithStyles, WithStartRow
{   
    use Exportable;

    public function __construct(string $inisial, string $id_penandaan_obat, string $id_golongan_obat, string $id_produsen){
        $this->inisial = $inisial;
        $this->id_penandaan_obat = $id_penandaan_obat;
        $this->id_golongan_obat = $id_golongan_obat;
        $this->id_produsen = $id_produsen;
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
        $id_penandaan_obat = $this->id_penandaan_obat;
        $id_golongan_obat = $this->id_golongan_obat;
        $id_produsen = $this->id_produsen;
        $rekaps = DB::connection($this->getConnectionName())->table('tb_m_stok_harga_'.$inisial.'')
                        ->select([
                            DB::raw('@rownum  := @rownum  + 1 AS no'),
                            'tb_m_stok_harga_'.$inisial.'.id_obat',
                            'tb_m_stok_harga_'.$inisial.'.barcode',
                            'tb_m_stok_harga_'.$inisial.'.nama',
                            'tb_m_stok_harga_'.$inisial.'.harga_beli_ppn',
                            'tb_m_stok_harga_'.$inisial.'.harga_jual',
                            'tb_m_stok_harga_'.$inisial.'.stok_akhir',
                            'tb_m_produsen.nama as produsen', 
                            'tb_m_penandaan_obat.nama as penandaan_obat', 
                            'tb_m_golongan_obat.keterangan as golongan_obat'
                        ])
                        ->join('tb_m_obat as a', 'a.id', 'tb_m_stok_harga_'.$inisial.'.id_obat')
                        ->join('tb_m_produsen', 'tb_m_produsen.id', '=', 'a.id_produsen')
                        ->join('tb_m_penandaan_obat', 'tb_m_penandaan_obat.id', '=', 'a.id_penandaan_obat')
                        ->join('tb_m_golongan_obat', 'tb_m_golongan_obat.id', '=', 'a.id_golongan_obat')
                        ->where(function($query) use($inisial, $id_penandaan_obat, $id_golongan_obat, $id_produsen){
                            $query->where('tb_m_stok_harga_'.$inisial.'.is_deleted','=','0');
                            if($id_penandaan_obat != "") {
                                $query->where('a.id_penandaan_obat',$id_penandaan_obat);
                            }

                            if($id_golongan_obat != "") {
                                $query->where('a.id_golongan_obat',$id_golongan_obat);
                            }

                            if($id_produsen != "") {
                                $query->where('a.id_produsen',$id_produsen);
                            }
                        })
                        ->orderBy('tb_m_stok_harga_'.$inisial.'.id')
                        ->get();

        $collection = collect();
        $collection[] = array('No', 'ID', 'Barcode', 'Nama', 'Penandaan Obat', 'Golongan Obat', 'Produsen', 'Harga Beli PPN', 'Harga Jual', 'Stok Akhir'); //1
        $no = 0;
        $total = 0;
        foreach ($rekaps as $obj) {
            $no++;    
            $collection[] = array(
                $no,
                $obj->id_obat,
                $obj->barcode,
                $obj->nama,
                $obj->penandaan_obat,
                $obj->golongan_obat,
                $obj->produsen,
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
            'E' => 30,
            'F' => 30,
            'G' => 30,
            'H' => 15,
            'I' => 15,
            'J' => 12,    
        ];
    }

    public function styles(Worksheet $sheet)
    { 
        return [
            1    => ['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
            'A'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
            'B'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
            'H'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
            'I'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
            'J'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
        ];
    }
}
