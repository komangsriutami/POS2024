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
use App\TransaksiPembelianDetail;
use App\TransaksiTODetail;
use App\MasterObat;
use App\HistoriStok;

use Auth;
use Cache;
use DB;

class PembelianExportSheets implements FromCollection, WithTitle, WithColumnWidths, WithStyles, WithStartRow, WithStrictNullComparison
{
    protected $id_pencarian;
    protected $tgl_awal;
    protected $tgl_akhir;
    protected $id_apotek;
    protected $limit;
    protected $inisial;
    protected $i;
    protected $nama;
    protected static $expiredAt = 6 * 60 * 60;

    public function __construct(int $id_pencarian, string $tgl_awal, string $tgl_akhir, int $id_apotek, int $limit, string $inisial, int $i)
    {
        $this->id_pencarian = $id_pencarian;
        $this->tgl_awal = $tgl_awal;
        $this->tgl_akhir = $tgl_akhir;
        $this->id_apotek = $id_apotek;
        $this->limit = $limit;
        $this->inisial = $inisial;
        $this->i = $i;

        if ($this->i == 1) {
            $this->nama = "Pembelian";
        } else if ($this->i == 2) {
            $this->nama = "Transfer Masuk";
        } else if ($this->i == 3) {
            $this->nama = "Transfer Keluar";
        } else if ($this->i == 4) {
            $this->nama = "Dead Stock";
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

        if($this->i ==  1) {
            $collection = collect();
            $collection[] = array('Apotek', $this->inisial, '');
            $collection[] = array('Tanggal', $awal.' - '.$akhir, '');
            $collection[] = array('', '', '');
            $collection[] = array('No', 'Nama Obat', 'Produsen', 'Jumlah', 'Harga Beli');

            if($this->id_pencarian == 9) {
                $cached_data = Cache::get('resume_pembelian_'.$this->id_pencarian.'_'.$this->tgl_awal.'_'.$this->tgl_akhir.'_list_data_'.$this->id_apotek);
            } elseif ($this->id_pencarian != 1) {
                $cached_data = Cache::get('resume_pembelian_'.$this->id_pencarian.'_list_data_'.$this->id_apotek);
            } else {
                $cached_data = Cache::get('resume_pembelian_'.$this->id_pencarian.'_'.Auth::user()->id.'_list_data_'.$this->id_apotek);
            }
            if($cached_data == null) {
                $pembelian = TransaksiPembelianDetail::select(
                    'tb_detail_nota_pembelian.id_obat',
                    DB::raw('SUM(tb_detail_nota_pembelian.jumlah) as jumlah'),
                )
                ->join('tb_nota_pembelian as b', 'b.id', '=', 'tb_detail_nota_pembelian.id_nota')
                ->leftjoin('tb_m_stok_harga_'.$this->inisial.' as c', 'c.id_obat', '=', 'tb_detail_nota_pembelian.id_obat')
                ->whereDate('b.created_at', '>=', $this->tgl_awal)
                ->whereDate('b.created_at', '<=', $this->tgl_akhir)
                ->where('b.id_apotek_nota', '=', $this->id_apotek)
                ->where('tb_detail_nota_pembelian.is_deleted', 0)
                ->where('b.is_deleted', 0)
                ->where('c.is_deleted', 0)
                ->groupBy('tb_detail_nota_pembelian.id_obat')
                ->orderByRaw('SUM(tb_detail_nota_pembelian.jumlah) DESC')
                ->get();

                if($this->id_pencarian == 9) {
                    Cache::forget('resume_pembelian_'.$this->id_pencarian.'_'.$this->tgl_awal.'_'.$this->tgl_akhir.'_list_data_'.$this->id_pencarian);
                    Cache::put('resume_pembelian_'.$this->id_pencarian.'_'.$this->tgl_awal.'_'.$this->tgl_akhir.'_list_data_'.$this->id_pencarian, $pembelian, self::$expiredAt);
                } elseif ($this->id_pencarian != 1) {
                    Cache::forget('resume_pembelian_'.$this->id_pencarian.'_list_data_'.$this->id_pencarian);
                    Cache::put('resume_pembelian_'.$this->id_pencarian.'_list_data_'.$this->id_pencarian, $pembelian, self::$expiredAt);
                } else {
                    Cache::forget('resume_pembelian_'.$this->id_pencarian.'_'.Auth::user()->id.'_list_data_'.$this->id_pencarian);
                    Cache::put('resume_pembelian_'.$this->id_pencarian.'_'.Auth::user()->id.'_list_data_'.$this->id_pencarian, $pembelian, self::$expiredAt);
                }
            } else {
                $pembelian = $cached_data;
            }

            $j = 0;
            foreach ($pembelian as $key => $obj) {
                $j++;
                $collection[] = array(
                    $j,
                    $obj->stok_harga->nama,
                    $obj->obat->produsen->nama,
                    $obj->jumlah,
                    $obj->stok_harga->harga_beli
                );
            }
        } else if($this->i == 2) {
            $collection = collect();
            $collection[] = array('Apotek', $this->inisial, '');
            $collection[] = array('Tanggal', $awal.' - '.$akhir, '');
            $collection[] = array('', '', '');
            $collection[] = array('No', 'Nama Obat', 'Produsen', 'Jumlah', 'Harga Beli');

            if($this->id_pencarian == 9) {
                $cached_data = Cache::get('resume_transfer_masuk_'.$this->id_pencarian.'_'.$this->tgl_awal.'_'.$this->tgl_akhir.'_list_data_'.$this->id_apotek);
            } elseif ($this->id_pencarian != 1) {
                $cached_data = Cache::get('resume_transfer_masuk_'.$this->id_pencarian.'_list_data_'.$this->id_apotek);
            } else {
                $cached_data = Cache::get('resume_transfer_masuk_'.$this->id_pencarian.'_'.Auth::user()->id.'_list_data_'.$this->id_apotek);
            }
            if($cached_data == null) {
                $transfer_masuk = TransaksiTODetail::select(
                    'tb_detail_nota_transfer_outlet.id_obat',
                    'c.nama',
                    DB::raw('SUM(tb_detail_nota_transfer_outlet.jumlah) as jumlah'),
                    'c.harga_beli'
                )
                ->join('tb_nota_transfer_outlet as b','b.id','=','tb_detail_nota_transfer_outlet.id_nota')
                ->leftjoin('tb_m_stok_harga_'.$this->inisial.' as c', 'c.id_obat', '=', 'tb_detail_nota_transfer_outlet.id_obat')
                ->whereDate('b.created_at','>=', $this->tgl_awal)
                ->whereDate('b.created_at','<=', $this->tgl_akhir)
                ->where('b.id_apotek_tujuan','=',$this->id_apotek)
                ->where('tb_detail_nota_transfer_outlet.is_deleted', 0)
                ->where('b.is_deleted', 0)
                ->where('c.is_deleted', 0)
                ->groupBy('tb_detail_nota_transfer_outlet.id_obat')
                ->orderByRaw('SUM(tb_detail_nota_transfer_outlet.jumlah) DESC')
                ->get();

                if($this->id_pencarian == 9) {
                    Cache::forget('resume_transfer_masuk_'.$this->id_pencarian.'_'.$this->tgl_awal.'_'.$this->tgl_akhir.'_list_data_'.$this->id_pencarian);
                    Cache::put('resume_transfer_masuk_'.$this->id_pencarian.'_'.$this->tgl_awal.'_'.$this->tgl_akhir.'_list_data_'.$this->id_pencarian, $transfer_masuk, self::$expiredAt);
                } elseif ($this->id_pencarian != 1) {
                    Cache::forget('resume_transfer_masuk_'.$this->id_pencarian.'_list_data_'.$this->id_pencarian);
                    Cache::put('resume_transfer_masuk_'.$this->id_pencarian.'_list_data_'.$this->id_pencarian, $transfer_masuk, self::$expiredAt);
                } else {
                    Cache::forget('resume_transfer_masuk_'.$this->id_pencarian.'_'.Auth::user()->id.'_list_data_'.$this->id_pencarian);
                    Cache::put('resume_transfer_masuk_'.$this->id_pencarian.'_'.Auth::user()->id.'_list_data_'.$this->id_pencarian, $transfer_masuk, self::$expiredAt);
                }
            } else {
                $transfer_masuk = $cached_data;
            }

            $j = 0;
            foreach ($transfer_masuk as $key => $obj) {
                $j++;
                $collection[] = array(
                    $j,
                    $obj->nama,
                    $obj->obat->produsen->nama,
                    $obj->jumlah,
                    $obj->harga_beli
                );
            }
        } else if($this->i == 3) {
            $collection = collect();
            $collection[] = array('Apotek', $this->inisial, '');
            $collection[] = array('Tanggal', $awal.' - '.$akhir, '');
            $collection[] = array('', '', '');
            $collection[] = array('No', 'Nama Obat', 'Produsen', 'Jumlah', 'Harga Beli');

            if($this->id_pencarian == 9) {
                $cached_data = Cache::get('resume_transfer_keluar_'.$this->id_pencarian.'_'.$this->tgl_awal.'_'.$this->tgl_akhir.'_list_data_'.$this->id_apotek);
            } elseif ($this->id_pencarian != 1) {
                $cached_data = Cache::get('resume_transfer_keluar_'.$this->id_pencarian.'_list_data_'.$this->id_apotek);
            } else {
                $cached_data = Cache::get('resume_transfer_keluar_'.$this->id_pencarian.'_'.Auth::user()->id.'_list_data_'.$this->id_apotek);
            }
            if($cached_data == null) {
                $transfer_keluar = TransaksiTODetail::select(
                    'tb_detail_nota_transfer_outlet.id_obat',
                    'c.nama',
                    DB::raw('SUM(tb_detail_nota_transfer_outlet.jumlah) as jumlah'),
                    'c.harga_beli'
                )
                ->join('tb_nota_transfer_outlet as b','b.id','=','tb_detail_nota_transfer_outlet.id_nota')
                ->leftjoin('tb_m_stok_harga_'.$this->inisial.' as c', 'c.id_obat', '=', 'tb_detail_nota_transfer_outlet.id_obat')
                ->whereDate('b.created_at','>=', $this->tgl_awal)
                ->whereDate('b.created_at','<=', $this->tgl_akhir)
                ->where('b.id_apotek_nota','=',$this->id_apotek)
                ->where('tb_detail_nota_transfer_outlet.is_deleted', 0)
                ->where('b.is_deleted', 0)
                ->where('c.is_deleted', 0)
                ->groupBy('tb_detail_nota_transfer_outlet.id_obat')
                ->orderByRaw('SUM(tb_detail_nota_transfer_outlet.jumlah) DESC')
                ->get();

                if($this->id_pencarian == 9) {
                    Cache::forget('resume_transfer_keluar_'.$this->id_pencarian.'_'.$this->tgl_awal.'_'.$this->tgl_akhir.'_list_data_'.$this->id_pencarian);
                    Cache::put('resume_transfer_keluar_'.$this->id_pencarian.'_'.$this->tgl_awal.'_'.$this->tgl_akhir.'_list_data_'.$this->id_pencarian, $transfer_keluar, self::$expiredAt);
                } elseif ($this->id_pencarian != 1) {
                    Cache::forget('resume_transfer_keluar_'.$this->id_pencarian.'_list_data_'.$this->id_pencarian);
                    Cache::put('resume_transfer_keluar_'.$this->id_pencarian.'_list_data_'.$this->id_pencarian, $transfer_keluar, self::$expiredAt);
                } else {
                    Cache::forget('resume_transfer_keluar_'.$this->id_pencarian.'_'.Auth::user()->id.'_list_data_'.$this->id_pencarian);
                    Cache::put('resume_transfer_keluar_'.$this->id_pencarian.'_'.Auth::user()->id.'_list_data_'.$this->id_pencarian, $transfer_keluar, self::$expiredAt);
                }
            } else {
                $transfer_keluar = $cached_data;
            }

            $j = 0;
            foreach ($transfer_keluar as $key => $obj) {
                $j++;
                $collection[] = array(
                    $j,
                    $obj->nama,
                    $obj->obat->produsen->nama,
                    $obj->jumlah,
                    $obj->harga_beli
                );
            }
        } else if($this->i == 4) {
            $collection = collect();
            $collection[] = array('Apotek', $this->inisial, '');
            $collection[] = array('Tanggal', $awal.' - '.$akhir, '');
            $collection[] = array('', '', '');
            $collection[] = array('No', 'Nama Obat', 'Produsen', 'Jumlah', 'Harga Beli');

            if($this->id_pencarian == 9) {
                $cached_data = Cache::get('resume_dead_stok_'.$this->id_pencarian.'_'.$this->tgl_awal.'_'.$this->tgl_akhir.'_list_data_'.$this->id_apotek);
            } elseif ($this->id_pencarian != 1) {
                $cached_data = Cache::get('resume_dead_stok_'.$this->id_pencarian.'_list_data_'.$this->id_apotek);
            } else {
                $cached_data = Cache::get('resume_dead_stok_'.$this->id_pencarian.'_'.Auth::user()->id.'_list_data_'.$this->id_apotek);
            }
            if($cached_data == null) {
                $awal = DB::connection($this->getConnectionName())->table('tb_detail_nota_penjualan as a')
                ->select([
                    'a.id_obat',
                    DB::raw('SUM(a.jumlah) as jumlah_pemakaian')
                ])
                ->leftjoin('tb_nota_penjualan as b','b.id','=','a.id_nota')
                ->whereRaw('b.created_at >="'.$this->tgl_awal.'"')
                ->whereRaw('b.created_at <="'.$this->tgl_akhir.'"')
                ->whereRaw('b.id_apotek_nota ="'.$this->id_apotek.'"')
                ->whereRaw('a.is_deleted = 0')
                ->whereRaw('b.is_deleted = 0')
                ->groupBy('a.id_obat');

                DB::connection($this->getConnection())->statement(DB::raw('set @rownum = 0'));
                $all = DB::connection($this->getConnectionDefault())->table('tb_m_stok_harga_'.$this->inisial.' as c')
                ->select([
                    DB::raw('IFNULL(y.id_obat, 0) as id_det'),
                    DB::raw('IFNULL(y.jumlah_pemakaian, 0) as jumlah_pemakaian'),
                    'c.*',
                    DB::raw('p.nama as nama_produsen')
                ])
                ->whereRaw('c.is_deleted = 0')
                ->join('tb_m_obat as o', 'o.id', '=', 'c.id_obat')
                ->join('tb_m_produsen as p', 'p.id', '=', 'o.id_produsen')
                ->leftJoin(DB::raw("({$awal->toSql()}) as y"), 'y.id_obat', '=', 'c.id_obat');

                $data = DB::connection($this->getConnectionName())->table(DB::raw("({$all->toSql()}) as j"))
                ->select([
                    DB::raw('@rownum  := @rownum  + 1 AS no'),
                    'j.*'
                ])
                ->whereRaw('jumlah_pemakaian = 0')->limit($this->limit)
                ->whereRaw('stok_akhir != 0')->limit($this->limit)
                ->orderByRaw('stok_akhir DESC')
                ->get();

                if($this->id_pencarian == 9) {
                    Cache::forget('resume_dead_stok_'.$this->id_pencarian.'_'.$this->tgl_awal.'_'.$this->tgl_akhir.'_list_data_'.$this->id_pencarian);
                    Cache::put('resume_dead_stok_'.$this->id_pencarian.'_'.$this->tgl_awal.'_'.$this->tgl_akhir.'_list_data_'.$this->id_pencarian, $data, self::$expiredAt);
                } elseif ($this->id_pencarian != 1) {
                    Cache::forget('resume_dead_stok_'.$this->id_pencarian.'_list_data_'.$this->id_pencarian);
                    Cache::put('resume_dead_stok_'.$this->id_pencarian.'_list_data_'.$this->id_pencarian, $data, self::$expiredAt);
                } else {
                    Cache::forget('resume_dead_stok_'.$this->id_pencarian.'_'.Auth::user()->id.'_list_data_'.$this->id_pencarian);
                    Cache::put('resume_dead_stok_'.$this->id_pencarian.'_'.Auth::user()->id.'_list_data_'.$this->id_pencarian, $data, self::$expiredAt);
                }
            } else {
                $data = $cached_data;
            }

            $i = 0;
            foreach ($data as $key => $obj) {
                $i++;

                $histori_stok = HistoriStok::select([
                    DB::raw('SUM(sisa_stok) as jum_sisa_stok'),
                    DB::raw('SUM(sisa_stok*hb_ppn) as total')
                ])
                ->where('id_obat', $obj->id_obat)
                ->whereIn('id_jenis_transaksi', [2,3,11,9])
                ->where('sisa_stok', '>', 0)
                ->orderBy('id', 'ASC')
                ->first();

                $avg = 0;
                if(!is_null($histori_stok)) {
                    if($histori_stok->total != 0) {
                        $avg = $histori_stok->total/$histori_stok->jum_sisa_stok;
                    }
                }
                $avg = number_format($avg, 2, ".", "");

                $collection[] = array(
                    $i,
                    $obj->nama,
                    $obj->nama_produsen,
                    $obj->stok_akhir,
                    $avg
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
            'C' => 33,
            'D' => 8,
            'E' => 10,
        ];
        return $width;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('B1:B2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER);

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
