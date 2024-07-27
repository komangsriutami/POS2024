<?php



namespace App\Exports;



use Maatwebsite\Excel\Concerns\FromCollection;

use Maatwebsite\Excel\Concerns\WithTitle;

use Maatwebsite\Excel\Concerns\WithColumnWidths;

use Maatwebsite\Excel\Concerns\WithStyles;

use Maatwebsite\Excel\Concerns\WithStartRow;

use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

use Maatwebsite\Excel\Events\AfterSheet;



use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use PhpOffice\PhpSpreadsheet\Style\Alignment;

use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

use Illuminate\Support\Collection;

use DB;



class ParetoPerMonthSheet implements FromCollection, WithTitle, WithColumnWidths, WithStyles, WithStartRow, WithStrictNullComparison

{

    protected $dataCollection;

    protected $inisial;

    protected $tgl_awal;

    protected $tgl_akhir;

    protected $i;

    protected $nama;





    public function __construct(Collection $dataCollection, string $inisial, string $tgl_awal, string $tgl_akhir, int $i)

    {

        $this->dataCollection = $dataCollection;

        $this->inisial = $inisial;

        $this->tgl_awal = $tgl_awal;

        $this->tgl_akhir = $tgl_akhir;

        $this->i = $i;



        if ($this->i == 1) {

            $this->nama = "Penjualan";

        } else if ($this->i == 2) {

            $this->nama = "Keuntungan";

        } else if ($this->i == 3) {

            $this->nama = "Resume Pareto";

        }

    }



    public function startRow(): int

    {

        return 3;

    }





    public function collection()

    {

        $awal = date('d-m-Y', strtotime($this->tgl_awal));

        $akhir = date('d-m-Y', strtotime($this->tgl_akhir));

        foreach ($this->dataCollection as $item) {
            $stok_akhir = DB::connection($this->getConnectionDefault())->table('tb_m_stok_harga_'.$this->inisial)
                ->select(
                    'id_obat',
                    'stok_akhir',
                )
                ->where(function($query) use ($item) {
                    $query->where('is_deleted', '=', 0);
                    $query->where('id_obat', '=', $item->id_obat);
                })
                ->first();

            $item->stok_akhir = $stok_akhir->stok_akhir;
        }


        if($this->i ==  1) {

            $collection = collect();

            $collection[] = array('Apotek', $this->inisial, '');

            $collection[] = array('Tanggal', $awal.' - '.$akhir, '');

            $collection[] = array('', '', '');

            $collection[] = array('No', 'Nama Obat', 'Penandaan Obat', 'Jenis', 'Produsen', 'Jumlah', 'Penjualan', 'Persentase Penjualan', 'Klasifikasi Penjualan', 'Stok Akhir');
            


            $sortedCollection = clone $this->dataCollection;

            $sortedCollection = $sortedCollection->sortBy(function ($item) {

                return [$item->persentase_penjualan];

            }, SORT_REGULAR, true);



            $i = 0;

            foreach ($sortedCollection as $key => $obj) {

                $i++;

                if($obj->klasifikasi_penjualan == 1) {

                    $obj->klasifikasi_penjualan = 'A';

                } elseif($obj->klasifikasi_penjualan == 2) {

                    $obj->klasifikasi_penjualan = 'B';

                } elseif($obj->klasifikasi_penjualan == 3) {

                    $obj->klasifikasi_penjualan = 'C';

                }

                

                $collection[] = array(

                    $i,

                    $obj->nama,

                    $obj->obat->penandaan_obat->nama,

                    $obj->obat->satuan->satuan,

                    $obj->obat->produsen->nama,

                    $obj->jumlah_penjualan,

                    $obj->penjualan,

                    $obj->persentase_penjualan/100,

                    $obj->klasifikasi_penjualan,

                    $obj->stok_akhir

                );

            }

        } else if($this->i == 2) {

            $collection = collect();

            $collection[] = array('Apotek', $this->inisial, '');

            $collection[] = array('Tanggal', $awal.' - '.$awal, '');

            $collection[] = array('', '', '');

            $collection[] = array('No', 'Nama Obat', 'Penandaan Obat', 'Jenis', 'Produsen', 'Jumlah', 'Keuntungan', 'Persentase Keuntungan', 'Klasifikasi Keuntungan', 'Stok Akhir');



            $sortedCollection = clone $this->dataCollection;

            $sortedCollection = $sortedCollection->sortBy(function ($item) {

                return [$item->persentase_keuntungan];

            }, SORT_REGULAR, true);



            $j = 0;

            foreach ($sortedCollection as $key => $obj) {

                $j++;

                if($obj->klasifikasi_keuntungan == 1) {

                    $obj->klasifikasi_keuntungan = 'A';

                } elseif($obj->klasifikasi_keuntungan == 2) {

                    $obj->klasifikasi_keuntungan = 'B';

                } elseif($obj->klasifikasi_keuntungan == 3) {

                    $obj->klasifikasi_keuntungan = 'C';

                } elseif($obj->klasifikasi_keuntungan == 4) {

                    $obj->klasifikasi_keuntungan = 'Error';

                }

                

                $collection[] = array(

                    $j,

                    $obj->nama,

                    $obj->obat->penandaan_obat->nama,

                    $obj->obat->satuan->satuan,

                    $obj->obat->produsen->nama,

                    $obj->jumlah_penjualan,

                    $obj->keuntungan,

                    $obj->persentase_keuntungan/100,

                    $obj->klasifikasi_keuntungan,

                    $obj->stok_akhir

                );

            }

        } else if($this->i == 3) {

            $collection = collect();

            $collection[] = array('Apotek', $this->inisial, '');

            $collection[] = array('Tanggal', $awal.' - '.$awal, '');

            $collection[] = array('', '', '');

            $collection[] = array('No', 'Nama Obat', 'Penandaan Obat', 'Jenis', 'Produsen', 'Jumlah', 'Penjualan', 'Persentase Penjualan', 'Klasifikasi Penjualan', 'Keuntungan', 'Persentase Keuntungan', 'Klasifikasi Keuntungan', 'Klasifikaasi Pareto', 'Stok Akhir');



            $sortedCollection = clone $this->dataCollection;

            $sortedCollection = $sortedCollection->sortBy(function ($item) {

                return [$item->persentase_penjualan, $item->persentase_keuntungan];

            }, SORT_REGULAR, true);



            $j = 0;

            foreach ($sortedCollection as $key => $obj) {

                $j++;

                if($obj->klasifikasi_penjualan == 1) {

                    $obj->klasifikasi_penjualan = 'A';

                } elseif($obj->klasifikasi_penjualan == 2) {

                    $obj->klasifikasi_penjualan = 'B';

                } elseif($obj->klasifikasi_penjualan == 3) {

                    $obj->klasifikasi_penjualan = 'C';

                }

                

                if($obj->klasifikasi_keuntungan == 1) {

                    $obj->klasifikasi_keuntungan = 'A';

                } elseif($obj->klasifikasi_keuntungan == 2) {

                    $obj->klasifikasi_keuntungan = 'B';

                } elseif($obj->klasifikasi_keuntungan == 3) {

                    $obj->klasifikasi_keuntungan = 'C';

                } elseif($obj->klasifikasi_keuntungan == 4) {

                    $obj->klasifikasi_keuntungan = 'Error';

                }

                

                if($obj->klasifikasi_pareto == 1) {

                    $obj->klasifikasi_pareto = 'A';

                } elseif($obj->klasifikasi_pareto == 2) {

                    $obj->klasifikasi_pareto = 'B';

                } elseif($obj->klasifikasi_pareto == 3) {

                    $obj->klasifikasi_pareto = 'C';

                }

                

                $collection[] = array(

                    $j,

                    $obj->nama,

                    $obj->obat->penandaan_obat->nama,

                    $obj->obat->satuan->satuan,

                    $obj->obat->produsen->nama,

                    $obj->jumlah_penjualan,

                    $obj->penjualan,

                    $obj->persentase_penjualan/100,

                    $obj->klasifikasi_penjualan,

                    $obj->keuntungan,

                    $obj->persentase_keuntungan/100,

                    $obj->klasifikasi_keuntungan,

                    $obj->klasifikasi_pareto,

                    $obj->stok_akhir

                );

            }
            
        } 

        return $collection;

    } 



    public function registerEvents(): array

    {

        return [

            AfterSheet::class => function (AfterSheet $event) {

                $cellRange = 'A1:E1'; // All headers

                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(14);

            },

        ];

    }



    public function columnWidths(): array

    {

        $width = [

            'A' => 10,

            'B' => 53,

            'C' => 35,

            'D' => 7,

            'E' => 33,

            'F' => 8,

            'G' => 12,

            'H' => 12,

            'I' => 12,

            'J' => 8,

        ];

        if ($this->i == 3) {

            $width['J'] = 12;

            $width['K'] = 12;

            $width['L'] = 12;

            $width['M'] = 12;

            $width['N'] = 8;

        }

        return $width;

    }



    public function styles(Worksheet $sheet)

    { 

        $sheet->getStyle('B1:B2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $sheet->getStyle('A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('F')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $sheet->getStyle('G')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $sheet->getStyle('H')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);

        $sheet->getStyle('I')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('J')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        if ($this->i == 3) {
            
            $sheet->getStyle('J')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            
            $sheet->getStyle('K')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
            
            $sheet->getStyle('L')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
            $sheet->getStyle('M')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
        }

        $sheet->getStyle('4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('4')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('4')->getAlignment()->setWrapText(true);

        $sheet->getStyle('1:4')->getFont()->setBold(true);

    }



    public function title(): string

    {

        return $this->nama;

    }

}