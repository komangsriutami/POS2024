<?php

namespace App\Console\Commands\Histori;

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

class HistoriCronSRJ extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'historisrj:cron';

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
        $obat = MasterObat::select(['id'])->orderBy('id', 'DESC')->where('is_deleted', 0)->first();
        $last_id_obat = $obat->id;
        $last_id_obat_ex = 0;
        $id_apotek = 10;
        $skip = 0;
        $apotek = MasterApotek::on($this->getConnectionName())->find($id_apotek);
        $inisial = strtolower($apotek->nama_singkat);
        $cek = DB::connection($this->getConnectionName())->table('tb_bantu_update_srj')->orderBy('id', 'DESC')->first();
        if(!empty($cek)) {
            $last_id_obat_ex = $cek->last_id_obat_after;
            if($last_id_obat_ex >= $last_id_obat) {
                # selesai : 1. hapus data di tb_bantu_update_srj dan ulang dari 0
                DB::connection($this->getConnectionName())->table('tb_bantu_update_srj')->truncate();
                $skip = 1;
            } else {
                $last_id_obat_ex = $last_id_obat_ex+1;
                $last_id_obat_after = $last_id_obat_ex+2-1;
            }
        } else {
            $last_id_obat_ex = $last_id_obat_ex+1;
            $last_id_obat_after = $last_id_obat_ex+2-1;
        }

        if($skip != 1) {
            DB::connection($this->getConnectionName())->table('tb_bantu_update_srj')
                ->insert(['last_id_obat_before' => $last_id_obat_ex, 'last_id_obat_after' => $last_id_obat_after, 'id_apotek' => $id_apotek, 'created_at' => date('Y-m-d H:i:s')]);
            
            $stok_hgs = DB::connection($this->getConnectionName())->table('tb_m_stok_harga_'.$inisial.'')->whereBetween('id_obat', [$last_id_obat_ex, $last_id_obat_after])->get();
            $x=0;
            $data_ = array();
            $now = date('Y-m-d');
            foreach ($stok_hgs as $key => $val) {
                $x++;
                $historis = DB::connection($this->getConnectionName())->table('tb_histori_stok_'.$inisial)->where('id_obat', $val->id_obat)->where('is_reload_histori', 0)->get();
                $data_pembelian_ = array(2, 12, 13, 14, 26, 27, 30, 31);
                $data_tf_masuk_ = array(3, 7, 16, 28, 32, 33);
                $data_tf_keluar_ = array(4, 8, 17, 29, 32, 33);
                $data_penjualan_ = array(1, 6, 5, 15);
                $data_penjualan_op_ = array(18, 19, 20);
                $data_penyesuaian_ = array(9,10);
                $data_so_ = array(11);
                $data_po_ = array(18, 19, 20, 21);
                $data_td_ = array(22, 23, 24, 25);

                DB::connection($this->getConnectionName())->table('tb_m_stok_harga_'.$inisial)
                                ->where('id_obat', $val->id)
                                ->update(['hb_ppn' => 0, 'hb_ppn_avg' => 0, 'is_reload_histori' => 0]);

                $i = 0;
                $last_stok = 0;
                foreach ($historis as $key => $data) {
                    $i++;

                    if (in_array($data->id_jenis_transaksi, $data_pembelian_)) {
                    $check = TransaksiPembelianDetail::on($this->getConnectionName())->find($data->id_transaksi);

                    # jika data pertama
                    if($i == 1) {
                        $hb_ppn = $check->harga_beli_ppn;
                        $hb = $check->harga_beli;
                        $hb_ppn_avg = $hb_ppn;

                        # set hb_ppn di tb_histori_stok_{{apotek}} = hb_ppn
                        DB::connection($this->getConnectionName())->table('tb_histori_stok_'.$inisial)
                            ->where('id', $data->id)
                            ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'is_reload_histori' => 1]);

                        # set harga_beli_ppn di tb_m_stok_harga_{{apotek}} = hb_ppn & hb_ppn_avg = hb_ppn_avg
                        DB::connection($this->getConnectionName())->table('tb_m_stok_harga_'.$inisial)
                            ->where('id_obat', $data->id_obat)
                            ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'hb' => $hb, 'is_reload_histori' => 1]);
                        $last_stok = $data->stok_akhir;
                    } else {
                        $cek_obat_ = DB::connection($this->getConnectionName())->table('tb_m_stok_harga_'.$inisial)->where('id_obat', $data->id_obat)->first();
                        $hb_ppn = $check->harga_beli_ppn;
                        $hb = $check->harga_beli;
                        $hb_ppn_avg = (($cek_obat_->hb_ppn_avg * $last_stok) + ($data->jumlah*$hb_ppn))/($data->jumlah + $last_stok);

                        # set hb_ppn di tb_histori_stok_{{apotek}} = hb_ppn
                        DB::connection($this->getConnectionName())->table('tb_histori_stok_'.$inisial)
                            ->where('id', $data->id)
                            ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'is_reload_histori' => 1]);

                        # set harga_beli_ppn di tb_m_stok_harga_{{apotek}} = hb_ppn & hb_ppn_avg = hb_ppn_avg
                        DB::connection($this->getConnectionName())->table('tb_m_stok_harga_'.$inisial)
                            ->where('id_obat', $data->id_obat)
                            ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'hb' => $hb, 'is_reload_histori' => 1]);
                        $last_stok = $data->stok_akhir;
                    }
                } else if (in_array($data->id_jenis_transaksi, $data_tf_masuk_)) {
                    $check = TransaksiTODetail::on($this->getConnectionName())->find($data->id_transaksi);

                    # jika data pertama
                    if($i == 1) {
                        $hb_ppn = $check->harga_outlet;
                        $hb = $check->harga_outlet;
                        $hb_ppn_avg = $hb_ppn;

                        # set hb_ppn di tb_histori_stok_{{apotek}} = hb_ppn
                        DB::connection($this->getConnectionName())->table('tb_histori_stok_'.$inisial)
                            ->where('id', $data->id)
                            ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'is_reload_histori' => 1]);

                        # set harga_beli_ppn di tb_m_stok_harga_{{apotek}} = hb_ppn & hb_ppn_avg = hb_ppn_avg
                        DB::connection($this->getConnectionName())->table('tb_m_stok_harga_'.$inisial)
                            ->where('id_obat', $data->id_obat)
                            ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'hb' => $hb, 'is_reload_histori' => 1]);
                        $last_stok = $data->stok_akhir;
                    } else {
                        $cek_obat_ = DB::connection($this->getConnectionName())->table('tb_m_stok_harga_'.$inisial)->where('id_obat', $data->id_obat)->first();
                        $hb_ppn = $check->harga_outlet;
                        $hb = $cek_obat_->harga_beli;
                        if($cek_obat_->harga_beli > $hb_ppn) {
                            $hb = $hb_ppn;
                        }
                        $hb_ppn_avg = (($cek_obat_->hb_ppn_avg * $last_stok) + ($data->jumlah*$hb_ppn))/($data->jumlah + $last_stok);
                        /*if($data->id==132294) {
                            echo $cek_obat_->hb_ppn_avg."*".$last_stok.'+'.$data->jumlah.'*'.$hb_ppn;
                            echo "</br>";
                            print_r($hb_ppn_avg);
                            exit();
                        }*/

                        # set hb_ppn di tb_histori_stok_{{apotek}} = hb_ppn
                        DB::connection($this->getConnectionName())->table('tb_histori_stok_'.$inisial)
                            ->where('id', $data->id)
                            ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'is_reload_histori' => 1]);

                        # set harga_beli_ppn di tb_m_stok_harga_{{apotek}} = hb_ppn & hb_ppn_avg = hb_ppn_avg
                        DB::connection($this->getConnectionName())->table('tb_m_stok_harga_'.$inisial)
                            ->where('id_obat', $data->id_obat)
                            ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'hb' => $hb, 'is_reload_histori' => 1]);
                        $last_stok = $data->stok_akhir;
                    }
                } else if (in_array($data->id_jenis_transaksi, $data_penjualan_)) {
                    $check = TransaksiPenjualanDetail::on($this->getConnectionName())->find($data->id_transaksi);
                    $cek_obat_ = DB::connection($this->getConnectionName())->table('tb_m_stok_harga_'.$inisial)->where('id_obat', $data->id_obat)->first();

                    # jika data pertama
                    if($i == 1) {
                        $last_pembelian = TransaksiPembelianDetail::on($this->getConnectionName())->where('tb_detail_nota_pembelian.id_obat', $data->id_obat)
                                            ->join('tb_nota_pembelian as b','b.id','=','tb_detail_nota_pembelian.id_nota')
                                            ->whereDate('b.tgl_nota','<', $data->created_at)
                                            ->where('tb_detail_nota_pembelian.is_deleted', 0)
                                            ->where('b.is_deleted', 0)
                                            ->where('b.id_apotek_nota','=',$id_apotek)
                                            ->first();

                        if(!empty($last_pembelian)) {
                            $hb_ppn = $last_pembelian->harga_beli_ppn;
                            $hb = $last_pembelian->harga_beli;
                            $hb_ppn_avg = $hb_ppn;
                        } else {
                            $last_tf_masuk = TransaksiTODetail::on($this->getConnectionName())->where('tb_detail_nota_transfer_outlet.id_obat', $data->id_obat)
                                            ->join('tb_nota_transfer_outlet as b','b.id','=','tb_detail_nota_transfer_outlet.id_nota')
                                            ->whereDate('b.tgl_nota','<', $data->created_at)
                                            ->where('tb_detail_nota_transfer_outlet.is_deleted', 0)
                                            ->where('b.is_deleted', 0)
                                            ->where('b.id_apotek_tujuan','=',$id_apotek)
                                            ->first();

                            if(!empty($last_tf_masuk)) {
                                $hb_ppn = $last_tf_masuk->harga_outlet;
                                $hb = $cek_obat_->harga_beli;
                                if($cek_obat_->harga_beli > $hb_ppn) {
                                    $hb = $hb_ppn;
                                }
                                $hb_ppn_avg = $hb_ppn;
                            } else {
                                $hb_ppn = $cek_obat_->harga_beli_ppn;
                                $hb = $cek_obat_->harga_beli;
                                if($cek_obat_->harga_beli > $hb_ppn) {
                                    $hb = $hb_ppn;
                                }
                                $hb_ppn_avg = $hb_ppn;
                            }
                        } 

                        # set hb_ppn di tb_histori_stok_{{apotek}} = hb_ppn
                        DB::connection($this->getConnectionName())->table('tb_histori_stok_'.$inisial)
                            ->where('id', $data->id)
                            ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'is_reload_histori' => 1]);

                        # set harga_beli_ppn di tb_m_stok_harga_{{apotek}} = hb_ppn & hb_ppn_avg = hb_ppn_avg
                        DB::connection($this->getConnectionName())->table('tb_m_stok_harga_'.$inisial)
                            ->where('id_obat', $data->id_obat)
                            ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'hb' => $hb, 'is_reload_histori' => 1]);

                        #set hb_ppn di tb_detail_nota_penjualan
                        $check->hb_ppn = $hb_ppn;
                        $check->is_reload_histori = 1;
                        $check->save();

                        $last_stok = $data->stok_akhir;
                    } else {
                        $cek_obat_ = DB::connection($this->getConnectionName())->table('tb_m_stok_harga_'.$inisial)->where('id_obat', $data->id_obat)->first();

                        # set hb_ppn di tb_histori_stok_{{apotek}} = hb_ppn
                        DB::connection($this->getConnectionName())->table('tb_histori_stok_'.$inisial)
                            ->where('id', $data->id)
                            ->update(['hb_ppn' => $cek_obat_->hb_ppn_avg, 'hb_ppn_avg' => $cek_obat_->hb_ppn_avg, 'is_reload_histori' => 1]);

                        #set hb_ppn di tb_detail_nota_penjualan = hb_ppn_avg dari tb_m_stok_{{apotek}} 
                        $check->hb_ppn = $cek_obat_->hb_ppn_avg;
                        $check->is_reload_histori = 1;
                        $check->save();
                        $last_stok = $data->stok_akhir;
                    }
                } else  if (in_array($data->id_jenis_transaksi, $data_tf_keluar_)) {
                    $check = TransaksiTODetail::on($this->getConnectionName())->find($data->id_transaksi);

                    # jika data pertama
                    if($i == 1) {
                        $hb_ppn = $check->harga_outlet;
                        $hb = $hb_ppn;
                        $hb_ppn_avg = $hb_ppn;

                        # set hb_ppn di tb_histori_stok_{{apotek}} = hb_ppn
                        DB::connection($this->getConnectionName())->table('tb_histori_stok_'.$inisial)
                            ->where('id', $data->id)
                            ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'is_reload_histori' => 1]);

                        # set harga_beli_ppn di tb_m_stok_harga_{{apotek}} = hb_ppn & hb_ppn_avg = hb_ppn_avg
                        DB::connection($this->getConnectionName())->table('tb_m_stok_harga_'.$inisial)
                            ->where('id_obat', $data->id_obat)
                            ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'hb' => $hb, 'is_reload_histori' => 1]);
                        $last_stok = $data->stok_akhir;
                    } else {
                        $cek_obat_ = DB::connection($this->getConnectionName())->table('tb_m_stok_harga_'.$inisial)->where('id_obat', $data->id_obat)->first();
                        //$hb_ppn = $check->harga_outlet;
                        $hb_ppn = $cek_obat_->hb_ppn_avg;
                        $hb = $cek_obat_->hb;
                        if($cek_obat_->harga_beli > $hb_ppn) {
                            $hb = $hb_ppn;
                        }
                        $hb_ppn_avg = (($cek_obat_->hb_ppn_avg * $last_stok) + ($data->jumlah*$hb_ppn))/($data->jumlah + $last_stok);

                        # set hb_ppn di tb_histori_stok_{{apotek}} = hb_ppn
                        DB::connection($this->getConnectionName())->table('tb_histori_stok_'.$inisial)
                            ->where('id', $data->id)
                            ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'is_reload_histori' => 1]);

                        # set harga_beli_ppn di tb_m_stok_harga_{{apotek}} = hb_ppn & hb_ppn_avg = hb_ppn_avg
                        DB::connection($this->getConnectionName())->table('tb_m_stok_harga_'.$inisial)
                            ->where('id_obat', $data->id_obat)
                            ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'hb' => $hb, 'is_reload_histori' => 1]);
                        $last_stok = $data->stok_akhir;
                    }
                } else  if (in_array($data->id_jenis_transaksi, $data_penjualan_op_)) {
                    $check = TransaksiPODetail::on($this->getConnectionName())->find($data->id_transaksi);

                    # jika data pertama
                    if($i == 1) {
                        $hb_ppn = $check->harga_jual;
                        $hb = $hb_ppn;
                        $hb_ppn_avg = $hb_ppn;

                        # set hb_ppn di tb_histori_stok_{{apotek}} = hb_ppn
                        DB::connection($this->getConnectionName())->table('tb_histori_stok_'.$inisial)
                            ->where('id', $data->id)
                            ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'is_reload_histori' => 1]);

                        # set harga_beli_ppn di tb_m_stok_harga_{{apotek}} = hb_ppn & hb_ppn_avg = hb_ppn_avg
                        DB::connection($this->getConnectionName())->table('tb_m_stok_harga_'.$inisial)
                            ->where('id_obat', $data->id_obat)
                            ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'hb' => $hb, 'is_reload_histori' => 1]);

                        $last_stok = $data->stok_akhir;
                    } else {
                        $cek_obat_ = DB::connection($this->getConnectionName())->table('tb_m_stok_harga_'.$inisial)->where('id_obat', $data->id_obat)->first();
                        $hb_ppn = $check->harga_jual;
                        $hb = $cek_obat_->harga_beli;
                        if($cek_obat_->harga_beli > $hb_ppn) {
                            $hb = $hb_ppn;
                        }
                        $hb_ppn_avg = (($cek_obat_->hb_ppn_avg * $last_stok) + ($data->jumlah*$hb_ppn))/($data->jumlah + $last_stok);

                        # set hb_ppn di tb_histori_stok_{{apotek}} = hb_ppn
                        DB::connection($this->getConnectionName())->table('tb_histori_stok_'.$inisial)
                            ->where('id', $data->id)
                            ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'is_reload_histori' => 1]);

                        # set harga_beli_ppn di tb_m_stok_harga_{{apotek}} = hb_ppn & hb_ppn_avg = hb_ppn_avg
                        DB::connection($this->getConnectionName())->table('tb_m_stok_harga_'.$inisial)
                            ->where('id_obat', $data->id_obat)
                            ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'hb' => $hb, 'is_reload_histori' => 1]);
                        $last_stok = $data->stok_akhir;
                    }
                } else {
                    $cek_obat_ = DB::connection($this->getConnectionName())->table('tb_m_stok_harga_'.$inisial)->where('id_obat', $data->id_obat)->first();

                    //if(empty($cek_obat_) OR $cek_obat_->hb_ppn == null) {
                        $last_pembelian = TransaksiPembelianDetail::on($this->getConnectionName())->where('tb_detail_nota_pembelian.id_obat', $data->id_obat)
                                            ->join('tb_nota_pembelian as b','b.id','=','tb_detail_nota_pembelian.id_nota')
                                            ->whereDate('b.tgl_nota','<', $data->created_at)
                                            ->where('tb_detail_nota_pembelian.is_deleted', 0)
                                            ->where('b.is_deleted', 0)
                                            ->where('b.id_apotek_nota','=',$id_apotek)
                                            ->first();

                        if(!empty($last_pembelian)) {
                            $hb_ppn = $last_pembelian->harga_beli_ppn;
                            $hb = $last_pembelian->harga_beli;
                            $hb_ppn_avg = $hb_ppn;
                        } else {
                            $last_tf_masuk = TransaksiTODetail::on($this->getConnectionName())->where('tb_detail_nota_transfer_outlet.id_obat', $data->id_obat)
                                            ->join('tb_nota_transfer_outlet as b','b.id','=','tb_detail_nota_transfer_outlet.id_nota')
                                            ->whereDate('b.tgl_nota','<', $data->created_at)
                                            ->where('tb_detail_nota_transfer_outlet.is_deleted', 0)
                                            ->where('b.is_deleted', 0)
                                            ->where('b.id_apotek_tujuan','=',$id_apotek)
                                            ->first();

                            if(!empty($last_tf_masuk)) {
                                $hb_ppn = $last_tf_masuk->harga_outlet;
                                $hb = $cek_obat_->harga_beli;
                                if($cek_obat_->harga_beli > $hb_ppn) {
                                    $hb = $hb_ppn;
                                }
                                $hb_ppn_avg = $hb_ppn;
                            } else {
                                $hb_ppn = $cek_obat_->harga_beli_ppn;
                                $hb = $cek_obat_->harga_beli;
                                if($cek_obat_->harga_beli > $hb_ppn) {
                                    $hb = $hb_ppn;
                                }
                                $hb_ppn_avg = $hb_ppn;
                            }
                        } 
                   /* } else {
                        $hb_ppn = $cek_obat_->hb_ppn;
                        $hb = $cek_obat_->hb;
                        $hb_ppn_avg = $hb_ppn_avg;
                    }*/

                    # set hb_ppn di tb_histori_stok_{{apotek}} = hb_ppn
                    DB::connection($this->getConnectionName())->table('tb_histori_stok_'.$inisial)
                        ->where('id', $data->id)
                        ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'is_reload_histori' => 1]);

                    # set harga_beli_ppn di tb_m_stok_harga_{{apotek}} = hb_ppn & hb_ppn_avg = hb_ppn_avg
                    DB::connection($this->getConnectionName())->table('tb_m_stok_harga_'.$inisial)
                        ->where('id_obat', $data->id_obat)
                        ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'hb' => $hb, 'is_reload_histori' => 1]);

                    $last_stok = $data->stok_akhir;
                }
                }
            }

            if($x > 0) { 
                \Log::info("Cron SRJ history is working fine! Reload data ".$last_id_obat_ex.' until '.$last_id_obat_after);
            } else {
                \Log::info("Cron SRJ history not working! Reload data ".$last_id_obat_ex.' until '.$last_id_obat_after);
            }
        } else {
            \Log::info("Cron SRJ history is working fine! Apotek tidak ditemukan.");
        }
        \Log::info("Cron SRJ history is working fine!");
    }
}
