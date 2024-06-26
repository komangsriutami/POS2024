<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Events\AfterSheet;

use App\User;
use App\Absensi;

class InvoicesPerMonthSheet implements FromCollection, WithTitle, WithColumnWidths, WithStyles, WithStartRow
{
    protected $year;
    protected $month;
    protected $id_user;
    protected $user;
    protected $cek_tgl_absen;
    protected $last;


    public function __construct(int $year, int $month, int $id_user, string $user, $cek_tgl_absen)
    {
        $this->month = $month;
        $this->year  = $year;
        $this->id_user  = $id_user;
        $this->user  = $user;
        $this->cek_tgl_absen = $cek_tgl_absen;
    }

    public function startRow(): int
    {
        return 3;
    }


    /**
     * @return Builder
     */
    public function collection()
    {

        $collection = collect();
        $collection[] = array('Nama', ':', $this->user, '', '', '', '', '', ''); //1
        $collection[] = array('Tahun', ':', $this->year, '', '', '', '', '', ''); //2
        $collection[] = array('Bulan', ':', $this->month, '', '', '', '', '', ''); //3
        $collection[] = array('', '', '', '', '', '', '', '', ''); //4
        $collection[] = array('No', 'Tanggal', 'Jam Masuk I', 'Jam Pulang I', 'Jam Pulang II', 'Jam Pulang II', 'Total I', 'Total II', 'Total Final'); //5
        $no = 0;
        $total = 0;
        foreach ($this->cek_tgl_absen as $obj) {
            $no++;
            $i = $obj->tgl;
            $date = date ("Y-m-d", strtotime($i));
            $data = array($date);

            $cek_absen = Absensi::where('is_deleted', 0)->where('tgl', $date)->where('id_user', $this->id_user)->first(); //
            if(!empty($cek_absen)) {
                if($cek_absen->jam_datang != null && $cek_absen->jam_pulang == null) {
                    $jam1 = 0;
                    $cek_absen->jam_datang = 'x';
                    $cek_absen->jam_pulang = 'x';
                } else {
                    if($cek_absen->jam_datang != null){
                        $cek_absen->jam_datang = 'x';
                    } else if($cek_absen->jam_pulang != null) {
                        $cek_absen->jam_pulang = 'x';
                    }
                    $date1 = strtotime($cek_absen->tgl." ".$cek_absen->jam_datang);
                    $date2 = strtotime($cek_absen->tgl." ".$cek_absen->jam_pulang);
                    $diff1   = $date2 - $date1;
                    $jam1 = $diff1/(60 * 60);
                }

                if($cek_absen->jam_datang_split != null && $cek_absen->jam_pulang_split == null) {
                    $jam2 = 0;
                    $cek_absen->jam_datang_split = 'x';
                    $cek_absen->jam_pulang_split = 'x';
                } else {
                    if($cek_absen->jam_datang_split != null){
                        $cek_absen->jam_datang_split = 'x';
                    } else if($cek_absen->jam_pulang_split != null) {
                        $cek_absen->jam_pulang_split = 'x';
                    }

                    $date3 = strtotime($cek_absen->tgl." ".$cek_absen->jam_datang_split);
                    $date4 = strtotime($cek_absen->tgl." ".$cek_absen->jam_pulang_split);
                    $diff2   = $date4 - $date3;
                    $jam2 = $diff2/(60 * 60);
                }

                if($jam1 < 0) {
                    $jam1 = 0;
                }

                if($jam2 < 0) {
                    $jam2 = 0;
                }

                if($cek_absen->jumlah_jam_kerja < 0) {
                    $cek_absen->jumlah_jam_kerja = 0;
                }

                $total = $total+$cek_absen->jumlah_jam_kerja;
                
                $collection[] = array(
                    $no,
                    $date,
                    $cek_absen->jam_datang,
                    $cek_absen->jam_pulang,
                    $cek_absen->jam_datang_split,
                    $cek_absen->jam_pulang_split,
                    number_format($jam1,2),
                    number_format($jam2,2),
                    number_format($cek_absen->jumlah_jam_kerja,2)
                );
            } else {
                $collection[] = array(
                    $no,
                    $date,
                    "x",
                    "x",
                    "x",
                    "x",
                    0,
                    0,
                    0
                );
            }
        }

        $this->last = $no+6;
        $collection[] = array('TOTAL', '', '', '', '', '', '', '', number_format($total,2)); //5

        return $collection;
    }

    /*public function headings(): array
    {
        return ['No', 'Tanggal', 'Jam Masuk I', 'Jam Pulang I', 'Jam Pulang II', 'Jam Pulang II', 'Total I', 'Total II', 'Total Final'];
    } */

    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $cellRange = 'A1:I1'; // All headers
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(14);
            },
        ];
    }


    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 15,
            'C' => 15,
            'D' => 15,
            'E' => 15,
            'F' => 15,
            'G' => 15,
            'H' => 15,
            'I' => 15,      
        ];
    }

    public function styles(Worksheet $sheet)
    { 
        return [
            1    => ['font' => ['bold' => true]],
            2    => ['font' => ['bold' => true]],
            3    => ['font' => ['bold' => true]],
            3    => ['font' => ['bold' => true]],
            5    => ['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
            'A'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
            'B'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
            'C'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
            'D'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
            'E'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
            'F'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
            'G'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
            'H'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
            'I'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
             $this->last  => ['font' => ['bold' => true], 
                                'fill' => [
                                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
                                    'rotation' => 90,
                                    'startColor' => [
                                        'argb' => '009688',
                                    ],
                                    'endColor' => [
                                        'argb' => '009688',
                                    ],
                                ],
                            ],
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->user;
    }
}