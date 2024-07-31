<?php

namespace App\Exports;

use App\TransaksiPenjualanDetail;
use App\DefectaOutlet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Events\AfterSheet;
use DB;

class AnalisaPembelianExport implements FromCollection, WithColumnWidths, WithStyles, WithStartRow
{   
    use Exportable;

    public function __construct(int $referensi, int $id_apotek, string $inisial){
        $this->referensi = $referensi;
        $this->id_apotek = $id_apotek;
        $this->inisial = $inisial;
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
        $akhir = date('Y-m-d');
        $awal = date('Y-m-d');
        if($this->referensi !="") {
            if($this->referensi == 1) {
                # data penjualan 1 bulan
                $awal = date('Y-m-d', strtotime("-1 month"));
            } else if($this->referensi == 2) {
                # data penjualan dalam 3 bulan 
                $awal = date('Y-m-d', strtotime("-3 month"));
            } else if($this->referensi == 3) {
                # data penjualan dalam 6 bulan 
                $awal = date('Y-m-d', strtotime("-6 month"));
            } else if($this->referensi == 4) {
                # data penjualan dalam 1 tahun
                $awal = date('Y-m-d', strtotime("-12 month"));
            } 
        }
        $tgl_akhir = $akhir.' 23:59:59';
        $tgl_awal = $awal.' 00:00:01';

        DB::connection($this->getConnection())->statement(DB::raw('set @rownum = 0'));
        $sub2 = TransaksiPenjualanDetail::on($this->getConnectionName())->select([
            'a.id',
            DB::raw('SUM(tb_detail_nota_penjualan.jumlah) as terjual'),
            DB::raw('(TRUNCATE
                ((CASE
                    WHEN "'.$this->referensi.'" = "" THEN 0
                    WHEN "'.$this->referensi.'" = 1 THEN (SUM(tb_detail_nota_penjualan.jumlah)/1)
                    WHEN "'.$this->referensi.'" = 2 THEN (SUM(tb_detail_nota_penjualan.jumlah)/3)
                    WHEN "'.$this->referensi.'" = 3 THEN (SUM(tb_detail_nota_penjualan.jumlah)/6)
                    WHEN "'.$this->referensi.'" = 4 THEN (SUM(tb_detail_nota_penjualan.jumlah)/12)
                    ELSE 0
                END), 0) + 1) AS kebutuhan'),
            DB::raw('(TRUNCATE
                ((0.1 * (CASE
                    WHEN "'.$this->referensi.'" = "" THEN 0
                    WHEN "'.$this->referensi.'" = 1 THEN (SUM(tb_detail_nota_penjualan.jumlah)/1)
                    WHEN "'.$this->referensi.'" = 2 THEN (SUM(tb_detail_nota_penjualan.jumlah)/3)
                    WHEN "'.$this->referensi.'" = 3 THEN (SUM(tb_detail_nota_penjualan.jumlah)/6)
                    WHEN "'.$this->referensi.'" = 4 THEN (SUM(tb_detail_nota_penjualan.jumlah)/12)
                    ELSE 0
                END)) + (CASE
                    WHEN "'.$this->referensi.'" = "" THEN 0
                    WHEN "'.$this->referensi.'" = 1 THEN (SUM(tb_detail_nota_penjualan.jumlah)/1)
                    WHEN "'.$this->referensi.'" = 2 THEN (SUM(tb_detail_nota_penjualan.jumlah)/3)
                    WHEN "'.$this->referensi.'" = 3 THEN (SUM(tb_detail_nota_penjualan.jumlah)/6)
                    WHEN "'.$this->referensi.'" = 4 THEN (SUM(tb_detail_nota_penjualan.jumlah)/12)
                    ELSE 0
                END), 0) + 1) AS kebutuhan_up'),
        ])
        ->leftJoin('tb_m_obat as a', 'a.id', '=', 'tb_detail_nota_penjualan.id_obat')
        ->leftjoin('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')
        ->where(function($query) use($tgl_awal, $tgl_akhir){
            $query->where('a.is_deleted', 0);
            $query->where('b.is_deleted', 0);
            $query->where('tb_detail_nota_penjualan.is_deleted', 0);
            $query->where('b.id_apotek_nota','=', $this->id_apotek);
            $query->whereDate('b.tgl_nota','>=', $tgl_awal);
            $query->whereDate('b.tgl_nota','<=', $tgl_akhir);

        })
        ->groupBy('a.id');

        //$data = $sub2->get();

        $sub3 = DB::connection($this->getConnectionName())->table( 'tb_m_obat as a' )
            ->select([
                DB::raw('@rownum := @rownum + 1 AS no'),
                'a.nama as nama_obat',
                'a.id as id_obat',
                'a.id_satuan',
                'a.id_produsen',
                'b.satuan',
                'c.nama as produsen',
                'd.stok_akhir as stok_obat',
                'sub2.terjual as terjual',
                DB::raw('IFNULL(sub2.kebutuhan, 0) as kebutuhan'),
                DB::raw('IFNULL(sub2.kebutuhan_up, 0) as kebutuhan_up'),
                DB::raw('(CASE
                    WHEN d.stok_akhir > 0 AND IFNULL(sub2.kebutuhan, 0) = 0 AND IFNULL(sub2.terjual, 0) = 0 THEN "Dead Stok"
                    WHEN d.stok_akhir > IFNULL(sub2.kebutuhan_up, 0) THEN "Overstok"
                    WHEN d.stok_akhir = 0 AND IFNULL(sub2.kebutuhan, 0) > 0 THEN "Potensial Loss"
                    WHEN d.stok_akhir < IFNULL(sub2.kebutuhan, 0) THEN "Understok"
                    WHEN d.stok_akhir > 0 AND IFNULL(sub2.kebutuhan, 0) > 0 AND IFNULL(sub2.terjual, 0) > 0 THEN "Stok On Hand"
                    WHEN d.stok_akhir = 0 AND IFNULL(sub2.kebutuhan, 0) = 0 THEN "Stock Off"
                    ELSE ""
                END) AS status'),
            ])
            ->leftjoin('tb_m_satuan as b','b.id','=','a.id_satuan')
            ->leftjoin('tb_m_produsen as c','c.id','=','a.id_produsen')
            ->leftjoin('tb_m_stok_harga_'.$this->inisial.' as d', 'd.id_obat', '=', 'a.id')
            ->leftjoin(DB::raw("({$sub2->toSql()}) as sub2"), 'sub2.id', '=', 'a.id')
            ->mergeBindings($sub2->getQuery());

         $data = DB::connection($this->getConnectionName())->table( DB::raw("({$sub3->toSql()}) as sub3") )
            ->mergeBindings($sub3) 
            ->select(['sub3.*'])
            ->orderByRaw('sub3.id_obat')
            ->get();

        $collection = collect();
        $collection[] = array('Apotek', $this->inisial, ''); //1
        $collection[] = array('Tanggal', $awal.' - '.$akhir, ''); //2
        $collection[] = array('', '', ''); //3
        $collection[] = array('ID', 'Nama', 'Terjual', 'Satuan', 'Produsen', 'Kebutuhan/Bulan', 'Stok', 'Status', 'Saran Pembelian', 'Sedang Dipesan'); //4
        foreach ($data as $obj) {
            if($obj->terjual == 0) {
                $obj->terjual = '0';
            };
            if($obj->kebutuhan == 0) {
                $obj->kebutuhan = '0';
            };
            if($obj->stok_obat == 0) {
                $obj->stok_obat = '0';
            };

            $selisih = $obj->kebutuhan - $obj->stok_obat;

            $saran = '';
            if($obj->status == "Overstok") {
                $saran .= $selisih;
            } else if($obj->status == "Understok") {
                $saran .= $selisih;
            } else if($obj->status == "Potensial Loss") {
                $saran .= $selisih;
            } else if($obj->status == "Stock Off") {
                $saran .= 0;
            } else if($obj->status == "Dead Stok") {
                $saran .= $selisih;
            } else if($obj->status == "Stok On Hand") {
                $saran .= $selisih;
            };

            if($saran == '') {
                $saran = 'Tidak ada saran yang sesuai dengan kategori';
            };

            $cek = DefectaOutlet::on($this->getConnectionName())->where('is_deleted', 0)
                ->where('id_obat', $obj->id_obat)
                ->where('id_apotek', $this->id_apotek)
                ->where('id_status', '!=', 1)
                ->where('id_process', '!=', 2)
                ->first();

            $status = 'Belum Masuk Defecta';
            if(!empty($cek)){
                $status = 'Sudah Masuk Defecta';
            }

            $collection[] = array(
                $obj->id_obat,
                $obj->nama_obat,
                $obj->terjual,
                $obj->satuan,
                $obj->produsen,
                $obj->kebutuhan,
                $obj->stok_obat,
                $obj->status,
                $saran,
                $status,
            );
        }

        return $collection;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 40,
            'C' => 8,
            'D' => 9,
            'E' => 30,
            'F' => 16,
            'G' => 6,
            'H' => 14,
            'I' => 16,
            'J' => 20,
        ];
    }

    public function styles(Worksheet $sheet)
    { 
        return [
            1    => ['font' => ['bold' => true]],
            2    => ['font' => ['bold' => true]],
            'A'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
            'F'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
            'G'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
            4    => ['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
        ];
    }
}
