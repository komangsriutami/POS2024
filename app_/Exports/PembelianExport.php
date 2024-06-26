<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PembelianExport implements WithMultipleSheets
{
	use Exportable;
    protected $id_pencarian;
    protected $tgl_awal;
    protected $tgl_akhir;
    protected $id_apotek;
    protected $limit;
    protected $inisial;

    public function __construct(int $id_pencarian, string $tgl_awal, string $tgl_akhir, int $id_apotek, int $limit, string $inisial)
    {
    	$this->id_pencarian = $id_pencarian;
        $this->tgl_awal = $tgl_awal;
        $this->tgl_akhir = $tgl_akhir;
        $this->id_apotek = $id_apotek;
        $this->limit = $limit;
        $this->inisial = $inisial;
    }

     /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
        for ($i = 1; $i <= 4; $i++) { 
            $sheets[] = new PembelianExportSheets($this->id_pencarian, $this->tgl_awal, $this->tgl_akhir, $this->id_apotek, $this->limit, $this->inisial, $i);
        }

        return $sheets;
    }
}
