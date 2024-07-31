<?php

namespace App\Console\Commands\Defecta;

use Illuminate\Console\Command;
use App\MasterObat;
use App\MasterGolonganObat;
use App\MasterPenandaanObat;
use App\MasterProdusen;
use App\MasterSatuan;
use App\MasterApotek;
use App\TransaksiPembelian;
use App\TransaksiPembelianDetail;
use App\TransaksiTO;
use App\TransaksiTODetail;
use App\PenyesuaianStok;
use App\MasterJenisTransaksi;
use App\MasterSuplier;
use App\TransaksiPenjualan;
use App\TransaksiPenjualanDetail;
use App\TransaksiPODetail;
use App\TransaksiPO;
use App\TransaksiTD;
use App\TransaksiTDDetail;
use DB;

class DefectaCronMG extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'defectamg:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $obat = MasterObat::on($this->getConnectionName())->select(['id'])->orderBy('id', 'DESC')->where('is_deleted', 0)->first();
        $last_id_obat = $obat->id;
        $last_id_obat_ex = 0;
        $id_apotek = 11;
        $skip = 0;
        $apotek = MasterApotek::on($this->getConnectionName())->find($id_apotek);
        $inisial = strtolower($apotek->nama_singkat);
        $cek = DB::connection($this->getConnectionName())->table('tb_bantu_transaksi_update_mg')->orderBy('id', 'DESC')->first();
        if(!empty($cek)) {
            $last_id_obat_ex = $cek->last_id_obat_after;
            if($last_id_obat_ex >= $last_id_obat) {
                # selesai : 1. hapus data di tb_bantu_transaksi_update_mg dan ulang dari 0
                DB::connection($this->getConnectionName())->table('tb_bantu_transaksi_update_mg')->truncate();
                $skip = 1;
            } else {
                $last_id_obat_ex = $last_id_obat_ex+1;
                $last_id_obat_after = $last_id_obat_ex+15-1;
            }
        } else {
            $last_id_obat_ex = $last_id_obat_ex+1;
            $last_id_obat_after = $last_id_obat_ex+15-1;
        }

        if($skip != 1) {
            DB::connection($this->getConnectionName())->table('tb_bantu_transaksi_update_mg')
                ->insert(['last_id_obat_before' => $last_id_obat_ex, 'last_id_obat_after' => $last_id_obat_after, 'id_apotek' => $id_apotek, 'created_at' => date('Y-m-d H:i:s')]);
            
            $obats = DB::connection($this->getConnectionDefault())->table('tb_m_stok_harga_'.$inisial.'')->whereBetween('id_obat', [$last_id_obat_ex, $last_id_obat_after])->get();
            $x=0;
            $data_ = array();
            $now = date('Y-m-d');
            foreach ($obats as $key => $obj) {
                $total_buffer = 0;
                $y1 = 0; 
                $y2 = 0;
                $y3 = 0;
                for ($i=1; $i <=3 ; $i++) { 
                    $data_ = DB::connection($this->getConnectionName())->table('tb_detail_nota_penjualan')
                    ->select([
                                DB::raw('SUM(tb_detail_nota_penjualan.jumlah-tb_detail_nota_penjualan.jumlah_cn) AS jumlah')
                                ])
                    ->join('tb_nota_penjualan','tb_nota_penjualan.id','=','tb_detail_nota_penjualan.id_nota')
                    ->where(function ($query) use ($apotek, $i, $obj) {
                        $bulan_aktif = date('m') - $i;
                        $query->whereRaw('tb_detail_nota_penjualan.is_deleted = 0');
                        $query->whereRaw('tb_detail_nota_penjualan.id_obat = '.$obj->id_obat.'');
                        $query->whereRaw('tb_nota_penjualan.id_apotek_nota = '.$apotek->id.'');
                        $query->whereRaw('MONTH(tb_detail_nota_penjualan.created_at) ='.$bulan_aktif.'');
                    })
                    ->first();

                    if($i==1) {
                        if($data_->jumlah != '' OR $data_->jumlah != null) {
                            $total_buffer = $data_->jumlah;
                        }
                    }

                    if($data_->jumlah != '' OR $data_->jumlah != null) {
                        if($i == 1) {
                            $y1 = $data_->jumlah;
                        } else if($i == 2) {
                            $y2 = $data_->jumlah;
                        } else if($i == 3) {
                            $y3 = $data_->jumlah;
                        }
                    }
                }

                $x  = 3; //jumlah periode bulan yang digunakan
                $x1 = 1; $x2 = 2; $x3 = 3;

                $jum_x = 6; // jumlah dari bulan1 = 1, bulan2 = 2, bulan3 = 3
                $jum_x_kuadrat = 14; // jumlah x kuadrat dati tiap bulan
                $x_rata_rata = $jum_x/ $x;

                $jum_y = $y1 + $y2 + $y3; // ini jumlah penjualan selama 3 bulan terakhir
                $y_rata_rata =  $jum_y/$x;

                $jum_x_y = ($x1 * $y1) + ($x2 * $y2) + ($x3 * $y3); // jumlah pengalian diantara x dan y

                $b = ($jum_x_y - ($x * ($x_rata_rata * $y_rata_rata))) / ($jum_x_kuadrat - ($x * ($x_rata_rata * $x_rata_rata)));
                $a = $y_rata_rata - ($b * $x_rata_rata); // ini tuntuk mencari nilai dari a
                $y = $a + $b * 4; // a + bx;
                $abc = ceil($y);

                DB::connection($this->getConnectionDefault())->table('tb_m_stok_harga_'.$inisial)->where('id_obat', $obj->id_obat)->update(['total_buffer'=> $total_buffer, 'forcasting'=>$abc, 'last_hitung' => date('Y-m-d H:i:s')]);
                $x++;
            }

            if($x > 0) { 
                \Log::info("Cron MG history is working fine! Reload data ".$last_id_obat_ex.' until '.$last_id_obat_after);
            } else {
                \Log::info("Cron MG history not working! Reload data ".$last_id_obat_ex.' until '.$last_id_obat_after);
            }
        } else {
            \Log::info("Cron MG history is working fine! Apotek tidak ditemukan.");
        }
        \Log::info("Cron MG history is working fine!");
    }
}
