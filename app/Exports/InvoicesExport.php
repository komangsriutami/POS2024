<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\User;
use App\Absensi;

class InvoicesExport implements WithMultipleSheets
{
    use Exportable;
    protected $year;
    protected $month;
    
    
    public function __construct(int $year, int $month)
    {
        $this->year = $year;
        $this->month = $month;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
        $cek_absensi = Absensi::select(['id_user'])
                                ->where('is_deleted', 0)
                                ->whereYear('tgl', $this->year)
                                ->whereMonth('tgl', $this->month)
                                ->get();

        $cek_tgl_absen = Absensi::select(['tgl'])
                                ->where('is_deleted', 0)
                                ->whereYear('tgl', $this->year)
                                ->whereMonth('tgl', $this->month)
                                ->groupBy('tgl')
                                ->orderBy('tgl', 'ASC')
                                ->get();

        $users = User::whereIn('id', $cek_absensi)->get();
        foreach ($users as $key => $user) {
            $sheets[] = new InvoicesPerMonthSheet($this->year, $this->month, $user->id, $user->nama, $cek_tgl_absen);
        }

        return $sheets;
    }
}
