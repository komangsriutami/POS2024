<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;
use DB;
use App\Events\PenjualanCreate;

class TransaksiPenjualan extends Model
{
    /* 
        Model   : Untuk Transaksi Penjualan
        Author  : Sri Utami
        Date    : 7/11/2020
    */
    protected $table = 'tb_nota_penjualan';
    public $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = ['id_apotek_nota', 'id_pasien', 'tgl_nota', 'keterangan', 'diskon_persen', 'diskon_rp', 'id_karyawan', 'cash', 'kembalian', 'id_kartu_debet_credit', 'debet', 'no_kartu', 'surcharge', 'id_dokter', 'biaya_jasa_dokter', 'id_jasa_resep', 'is_penjualan_tanpa_item', 'is_kredit', 'is_lunas_pembayaran_kredit', 'id_vendor', 'diskon_vendor', 'biaya_resep', 'total_belanja', 'total_bayar', 'id_paket_wd', 'harga_wd', 'nama_lab', 'biaya_lab', 'keterangan_lab', 'biaya_apd', 'is_margin', 'tgl_jatuh_tempo', 'is_margin_kurang'];

    public function validate(){
        return Validator::make((array)$this->attributes, [
            'cash' => 'required',
            'kembalian' => 'required'
        ]);
    }

    public function save_from_array($detail_penjualans, $val){
        if($val==1)
        {
            $this->id_apotek_nota = session('id_apotek_active');
            $this->created_by = Auth::user()->id;
            $this->tgl_nota = date('Y-m-d');
            $this->created_at = date('Y-m-d H:i:s');
        }else{
            $this->id_apotek_nota = session('id_apotek_active');
            $this->updated_by = Auth::user()->id;
            $this->tgl_nota = date('Y-m-d');
            $this->updated_at = date('Y-m-d H:i:s');
        }

        if($this->save()) {
            $id_nota = $this->id;
        } else {
            DB::rollback();
            $rsp = array('status' => 0, 'message' => 'Error, periksa kembali data yang disimpan');
            return $rsp;
        }
        

        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);

        $status = true;
        $str_array_id = array();
        $array_id_obat = array();

        if ($this->is_penjualan_tanpa_item == 0) {
            foreach ($detail_penjualans as $detail_penjualan) {
                if(!in_array($detail_penjualan['id_obat'], $array_id_obat)){
                    $is_history = 0;
                    if($detail_penjualan['id']>0){
                        $obj = TransaksiPenjualanDetail::find($detail_penjualan['id']);
                    }else{
                        $is_history = 1;
                        $obj = new TransaksiPenjualanDetail;
                    }
                    $stok_harga = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $detail_penjualan['id_obat'])->first();
                    $obj->id_nota = $this->id;
                    $obj->id_obat = $detail_penjualan['id_obat'];
                    $obj->hb_ppn = $detail_penjualan['hb_ppn'];
                    $obj->margin = $detail_penjualan['margin'];
                    $obj->harga_jual = $detail_penjualan['harga_jual'];
                    $obj->jumlah = $detail_penjualan['jumlah'];
                    $obj->diskon = $detail_penjualan['diskon'];
                    $obj->created_by = Auth::user()->id;
                    $obj->created_at = date('Y-m-d H:i:s');
                    $obj->updated_at = date('Y-m-d H:i:s');
                    $obj->updated_by = '';
                    $obj->is_deleted = 0;
                    if($obj->save()) {
                    } else {
                        DB::rollback();
                        $rsp = array('status' => 0, 'message' => 'Error, periksa kembali data yang disimpan');
                        return $rsp;
                    }
                    $array_id_obat[] = $obj->id;

                    $kurangStok = $this->kurangStok($obj->id, $obj->id_obat, $obj->jumlah);
                    if($kurangStok['status'] == 0) {
                        DB::rollback();
                        $rsp = array('status' => 0, 'message' => 'Stok yang tersedia tidak mencukupi');
                        return $rsp;
                    } else {
                        $obj->id_histori_stok = $kurangStok['array_id_histori_stok'];
                        $obj->id_histori_stok_detail = $kurangStok['array_id_histori_stok_detail'];
                        if($this->is_margin == 0) {
                            $obj->hb_ppn = $kurangStok['hb_ppn'];
                        } else {
                            $obj->hb_ppn = $kurangStok['hb_ppn'];
                            $obj->harga_jual = ($detail_penjualan['margin']/100 * $kurangStok['hb_ppn'])+$kurangStok['hb_ppn'];
                        }
                       
                        # crete histori stok barang
                        if($is_history == 1) {
                            $apotek = MasterApotek::find(session('id_apotek_active'));
                            $inisial = strtolower($apotek->nama_singkat);
                            $stok_before = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $obj->id_obat)->first(); 
                            $stok_now = $stok_before->stok_akhir-$obj->jumlah;

                            /*$arrayupdate = array(
                                'stok_awal'=> $stok_before->stok_akhir, 
                                'stok_akhir'=> $stok_now, 
                                'updated_at' => date('Y-m-d H:i:s'),
                                'updated_by' => Auth::user()->id
                            );*/

                            # update ke table stok harga
                            $stok_harga = MasterStokHarga::where('id_obat', $obj->id_obat)->first();
                            $stok_harga->stok_awal = $stok_before->stok_akhir;
                            $stok_harga->stok_akhir = $stok_now;
                            $stok_harga->updated_at = date('Y-m-d H:i:s');
                            $stok_harga->updated_by = Auth::user()->id;
                            if($stok_harga->save()) {
                            } else {
                                DB::rollback();
                                $rsp = array('status' => 0, 'message' => 'Error, periksa kembali data yang disimpan');
                                return $rsp;
                            }

                            /*$arrayinsert = array(
                                'id_obat' => $obj->id_obat,
                                'jumlah' => $obj->jumlah,
                                'stok_awal' => $stok_before->stok_akhir,
                                'stok_akhir' => $stok_now,
                                'id_jenis_transaksi' => 1, //penjualan
                                'id_transaksi' => $obj->id,
                                'batch' => null,
                                'ed' => null,
                                'sisa_stok' => null,
                                'hb_ppn' => $obj->hb_ppn,
                                'created_at' => date('Y-m-d H:i:s'),
                                'created_by' => Auth::user()->id
                            );*/

                            # create histori
                           /* $histori_stok = HistoriStok::where('id_obat', $obj->id_obat)->where('jumlah', $obj->jumlah)->where('id_jenis_transaksi', 1)->where('id_transaksi', $obj->id)->first();
                            if(empty($histori_stok)) {*/
                                $histori_stok = new HistoriStok;
                            //}
                            $histori_stok->id_obat = $obj->id_obat;
                            $histori_stok->jumlah = $obj->jumlah;
                            $histori_stok->stok_awal = $stok_before->stok_akhir;
                            $histori_stok->stok_akhir = $stok_now;
                            $histori_stok->id_jenis_transaksi = 1; //penjualan
                            $histori_stok->id_transaksi = $obj->id;
                            $histori_stok->batch = null;
                            $histori_stok->ed = null;
                            $histori_stok->sisa_stok = null;
                            $histori_stok->hb_ppn = $obj->hb_ppn;
                            $histori_stok->created_at = date('Y-m-d H:i:s');
                            $histori_stok->created_by = Auth::user()->id;
                            if($histori_stok->save()) {
                            } else {
                                 DB::rollback();
                                $rsp = array('status' => 0, 'message' => 'Error, periksa kembali data yang disimpan');
                                return $rsp;
                            }
                        }

                        if($obj->save()) {
                            if($this->save()) {
                                $rsp = array('status' => 1, 'message' => 'Data penjualan berhasil disimpan');
                                return $rsp;
                            } else {
                                DB::rollback();
                                $rsp = array('status' => 0, 'message' => 'Error, periksa kembali data yang disimpan');
                                return $rsp;
                            }
                        } else {
                            DB::rollback();
                            $rsp = array('status' => 0, 'message' => 'Error, periksa kembali data yang disimpan');
                            return $rsp;
                        }
                    }
                }
            }

          /*  if(!empty($array_id_obat)){
                DB::statement("DELETE FROM tb_detail_nota_penjualan
                                WHERE id_nota=".$this->id." AND 
                                        id NOT IN(".implode(',', $array_id_obat).")");
            }else{
                DB::statement("DELETE FROM tb_detail_nota_penjualan 
                                WHERE id_nota=".$this->id);
            }*/
        } else {
            $rsp = array('status' => 1, 'message' => 'Data penjualan berhasil disimpan');
            return $rsp;
        }
        
    }

    public function kurangStok($id_detail, $id_obat, $jumlah) {
        $inisial = strtolower(session('nama_apotek_singkat_active'));
        $cekHistori = DB::table('tb_histori_stok_'.$inisial)
                            ->where('id_obat', $id_obat)
                            ->whereIn('id_jenis_transaksi', [2,3,11,9])
                            ->where('sisa_stok', '>', 0)
                            ->orderBy('id', 'ASC')
                            ->first();

        $array_id_histori_stok = array();
        $array_id_histori_stok_detail = array();
        $hb_ppn = 0;

        if(!is_null($cekHistori)) {
            if($cekHistori->sisa_stok >= $jumlah) {
                # kosongkan sisa stok histori sebelumnya 
                $sisa_stok = $cekHistori->sisa_stok - $jumlah;
                $keterangan = $cekHistori->keterangan.', Penjualan pada IDdet.'.$id_detail.' sejumlah '.$jumlah;
                DB::table('tb_histori_stok_'.$inisial)->where('id', $cekHistori->id)->update(['sisa_stok' => $sisa_stok, 'keterangan' => $keterangan]);
                $array_id_histori_stok[] = $cekHistori->id;
                $array_id_histori_stok_detail[] = array('id_histori_stok' => $cekHistori->id, 'jumlah' => $jumlah);
                $hb_ppn = $cekHistori->hb_ppn;
            } else {
                # jika jumlahnya tidak sama maka
                $selisih = $jumlah - $cekHistori->sisa_stok;

                # update jumlah selisih ke histori yang ada stok sebelumnya
                $i = $jumlah;
                $total  = 0;
                while($i >= 1) {
                    # cari histori berikutnya yg bisa dikurangi
                    $cekHistoriLanj = DB::table('tb_histori_stok_'.$inisial)
                            ->where('id_obat', $id_obat)
                            ->whereIn('id_jenis_transaksi', [2,3,11,9])
                            ->where('sisa_stok', '>', 0)
                            ->orderBy('id', 'ASC')
                            ->first();

                    if($cekHistoriLanj->sisa_stok >= $i) {
                        # update selisih jika stok melebihi jumlah
                        $keterangan = $cekHistoriLanj->keterangan.', Penjualan pada IDdet.'.$id_detail.' sejumlah '.$i;
                        $sisa = $cekHistoriLanj->sisa_stok - $i;
                        DB::table('tb_histori_stok_'.$inisial)->where('id', $cekHistoriLanj->id)->update(['sisa_stok' => $sisa, 'keterangan' => $keterangan]);
                        $array_id_histori_stok_detail[] = array('id_histori_stok' => $cekHistoriLanj->id, 'jumlah' => $i);
                        $total = $total + $cekHistoriLanj->hb_ppn * $i;
                         $i = 0;
                    } else {
                        # update selisih jika stok kurang dari jumlah
                        $keterangan = $cekHistoriLanj->keterangan.', Penjualan pada IDdet.'.$id_detail.' sejumlah '.$cekHistoriLanj->sisa_stok;
                        $sisa = $i - $cekHistoriLanj->sisa_stok;
                        DB::table('tb_histori_stok_'.$inisial)->where('id', $cekHistoriLanj->id)->update(['sisa_stok' => 0, 'keterangan' => $keterangan]);
                        $i = $sisa;
                        $array_id_histori_stok_detail[] = array('id_histori_stok' => $cekHistoriLanj->id, 'jumlah' => $cekHistoriLanj->sisa_stok);
                        $total = $total + $cekHistoriLanj->hb_ppn * $cekHistoriLanj->sisa_stok;
                    }
                    $array_id_histori_stok[] = $cekHistoriLanj->id;
                }
                $hb_ppn = $total/$jumlah;
                $hb_ppn = ceil($hb_ppn);
            } 

            $rsp = array('status' => 1, 'array_id_histori_stok' => json_encode($array_id_histori_stok), 'array_id_histori_stok_detail' => json_encode($array_id_histori_stok_detail), 'hb_ppn' => $hb_ppn);
            return $rsp;
        } else {
            $rsp = array('status' => 0, 'array_id_histori_stok' => null, 'array_id_histori_stok_detail' => null, 'hb_ppn' => null);
            return $rsp;
        }
    }

    public function detail_penjualan(){
        return $this->hasMany('App\TransaksiPenjualanDetail', 'id_nota', 'id')->where('is_deleted', 0);
    }

    public function detail_penjualan_total(){
        return $this->hasMany('App\TransaksiPenjualanDetail', 'id_nota', 'id')
                    ->select([
                        DB::raw('SUM(tb_detail_nota_penjualan.jumlah * tb_detail_nota_penjualan.harga_jual) AS total'),
                        DB::raw('SUM(tb_detail_nota_penjualan.diskon) AS total_diskon')
                    ])
                    ->where('tb_detail_nota_penjualan.is_deleted', 0)->limit(1);
    }

    public function cek_retur(){
        return $this->hasMany('App\TransaksiPenjualanDetail', 'id_nota', 'id')
                    ->select([
                        DB::raw('COUNT(tb_detail_nota_penjualan.id) AS total_cn')
                    ])
                    ->where('tb_detail_nota_penjualan.is_cn', 1)
                    ->limit(1);
    }

    public function created_oleh(){
        return $this->hasOne('App\User', 'id', 'created_by');
    }

    public function updated_oleh(){
        return $this->hasOne('App\User', 'id', 'updated_by');
    }

    public function dokter(){
        return $this->hasOne('App\MasterDokter', 'id', 'id_dokter');
    }

    public function jasa_resep(){
        return $this->hasOne('App\MasterJasaResep', 'id', 'id_jasa_resep');
    }

    public function karyawan(){
        return $this->hasOne('App\User', 'id', 'id_karyawan');
    }

    public function vendor(){
        return $this->hasOne('App\MasterVendor', 'id', 'id_vendor');
    }

    public function pasien(){
        return $this->hasOne('App\MasterMember', 'id', 'id_pasien');
    }

    public function paket_wd(){
        return $this->hasOne('App\MasterPaketWD', 'id', 'id_paket_wd');
    }

    public function lunas_oleh(){
        return $this->hasOne('App\User', 'id', 'is_lunas_pembayaran_kredit_by');
    }

    public function kartu(){
        return $this->hasOne('App\MasterKartu', 'id', 'id_kartu_debet_credit');
    }
}