<?php

namespace App\Exports;

use App\TransaksiPembelianDetail;
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

class RekapPembelianOutletExport implements FromCollection, WithColumnWidths, WithStyles, WithStartRow
{   
    use Exportable;

    public function __construct(string $tgl_awal, string $tgl_akhir, int $id_apotek, string $inisial){
        $this->tgl_awal = $tgl_awal;
        $this->tgl_akhir = $tgl_akhir;
        $this->id_apotek = $id_apotek;
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
        $tgl_awal = $this->tgl_awal;
        $tgl_akhir = $this->tgl_akhir;
        $id_apotek = $this->id_apotek;
        $inisial = $this->inisial;
        $data =  TransaksiPembelianDetail::select(
                                'tb_detail_nota_pembelian.id_obat',
                                'c.nama',
                                DB::raw('SUM(tb_detail_nota_pembelian.jumlah) as jumlah_pemakaian')
                        )
                        ->join('tb_nota_pembelian as b','b.id','=','tb_detail_nota_pembelian.id_nota')
                        ->leftjoin('tb_m_stok_harga_'.$inisial.' as c', 'c.id_obat', '=', 'tb_detail_nota_pembelian.id_obat')
                        ->join('tb_m_obat as d','d.id','=','tb_detail_nota_pembelian.id_obat')
                        ->whereDate('b.tgl_faktur','>=', $tgl_awal)
                        ->whereDate('b.tgl_faktur','<=', $tgl_akhir)
                        ->where('b.id_apotek_nota','=',$id_apotek)
                        ->where('b.is_deleted', 0)
                        ->where('tb_detail_nota_pembelian.is_deleted', 0)
                        ->orderByRaw('SUM(tb_detail_nota_pembelian.jumlah) DESC')
                        ->groupBy('tb_detail_nota_pembelian.id_obat')
                        ->get();
                        //->orderByRaw('SUM(tb_detail_nota_pembelian.jumlah) DESC')
                        //->limit($limit);

        $collection = collect();
        $collection[] = array('No', 'ID', 'Nama', 'Jumlah'); //1
        $no = 0;
        $total = 0;
        foreach ($data as $obj) {
            $no++;    
            $collection[] = array(
                $no,
                $obj->id_obat,
                $obj->nama,
                $obj->jumlah_pemakaian
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
            'A' => -1,
            'B' => 8,
            'C' => 45,
            'D' => 8,
        ];
    }

    public function styles(Worksheet $sheet)
    { 
        return [
            1    => ['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
            'A'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
            'B'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
            'D'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
        ];
    }
}
