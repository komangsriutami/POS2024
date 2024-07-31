<?php



namespace App\Exports;



use Maatwebsite\Excel\Concerns\Exportable;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

use App\User;

use Illuminate\Support\Collection;



class ParetoExport implements WithMultipleSheets

{

    use Exportable;

    protected $dataCollection;

    protected $inisial;

    protected $tgl_awal;

    protected $tgl_akhir;

    public function __construct(Collection $dataCollection, string $inisial, string $tgl_awal, string $tgl_akhir)

    {

        $this->dataCollection = $dataCollection;

        $this->inisial = $inisial;

        $this->tgl_awal = $tgl_awal;

        $this->tgl_akhir = $tgl_akhir;

    }



    /**

     * @return array

     */

    public function sheets(): array

    {

        $sheets = [];

        for ($i = 1; $i <= 3; $i++) {

            $sheets[] = new ParetoPerMonthSheet($this->dataCollection, $this->inisial, $this->tgl_awal, $this->tgl_akhir, $i);
            
        }



        return $sheets;

    }

}

