<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\User;
use App\Absensi;

class AbsensiExport implements WithMultipleSheets
{
    use Exportable;
    protected $year;
    protected $month;
    protected $id_searching_by;
    protected $id_apotek;
    
    public function __construct(int $year, int $month, int $id_searching_by, int $id_apotek)
    {
        $this->year = $year;
        $this->month = $month;
        $this->id_searching_by = $id_searching_by;
        $this->id_apotek = $id_apotek;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
        $cek_absensi = Absensi::select(['id_user'])
                                ->where(function($query){
                                    $query->where('is_deleted', 0);
                                    $query->whereYear('tgl', $this->year);
                                    $query->whereMonth('tgl', $this->month);
                                    if($this->id_searching_by == 2) {
                                        $query->where('id_apotek', $this->id_apotek);
                                    }
                                })
                                ->get();

        $cek_tgl_absen = Absensi::select(['tgl'])
                                ->where(function($query){
                                    $query->where('is_deleted', 0);
                                    $query->whereYear('tgl', $this->year);
                                    $query->whereMonth('tgl', $this->month);
                                    if($this->id_searching_by == 2) {
                                        $query->where('id_apotek', $this->id_apotek);
                                    }
                                })
                                ->groupBy('tgl')
                                ->orderBy('tgl', 'ASC')
                                ->get();

        $users = User::whereIn('id', $cek_absensi)->get();
        foreach ($users as $key => $user) {
            $sheets[] = new AbsensiPerMonthSheet($this->year, $this->month, $this->id_searching_by, $this->id_apotek, $user->id, $user->nama, $cek_tgl_absen);
        }

        return $sheets;
    }
}
