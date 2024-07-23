<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;
use DB;
use App\Traits\DynamicConnectionTrait;

class TransaksiPO extends Model
{
    use DynamicConnectionTrait;
    // ini tabel nota penjualan
    protected $table = 'tb_nota_po';
    public $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = ['id_apotek_nota',
                            'tgl_nota',
    						'grand_total',
    						'keterangan',
                            'is_deleted',
                            'deleted_at',
                            'deleted_by'
    						];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'id_apotek_nota' => 'required',
            'tgl_nota' => 'required',
        ]);
    }

    public function save_from_array($details, $val){
        if($val==1)
        {
            $this->id_apotek_nota = session('id_apotek_active');
            $this->tgl_nota = date('Y-m-d');
            $this->created_by = Auth::user()->id;
            $this->created_at = date('Y-m-d H:i:s');
        }else{
            $this->id_apotek_nota = session('id_apotek_active');
            $this->tgl_nota = date('Y-m-d');
            $this->updated_by = Auth::user()->id;
            $this->updated_at = date('Y-m-d H:i:s');
        }

        if($this->save()) {
            $id_nota = $this->id;
        } else {
            DB::connection($this->getConnectionName())->rollback();
            $rsp = array('status' => 0, 'message' => 'Error, periksa kembali data yang disimpan');
            return $rsp;
        }

        $status = true;
        $str_array_id = array();
        $array_id_obat = array();
        $grand_total = 0;
        foreach ($details as $detail) {
            if(!in_array($detail['id_obat'], $array_id_obat)){
                $is_history = 0;
                if($detail['id']>0){
                    $obj = TransaksiPODetail::on($this->getConnectionName())->find($detail['id']);
                }else{
                    $is_history = 1;
                    $obj = new TransaksiPODetail;
                    $obj->setDynamicConnection();
                }

                $obj->id_nota = $this->id;
                $obj->id_obat = $detail['id_obat'];
                $obj->harga_jual = $detail['harga_jual'];
                $obj->jumlah = $detail['jumlah'];
                $obj->total = $detail['harga_jual']*$detail['jumlah'];
                $obj->created_by = Auth::user()->id;
                $obj->created_at = date('Y-m-d H:i:s');
                $obj->updated_at = null;
                $obj->updated_by = null;
                $obj->is_deleted = 0;

                if($obj->save()) {
                } else {
                    DB::connection($this->getConnectionName())->rollback();
                    $rsp = array('status' => 0, 'message' => 'Error, periksa kembali data yang disimpan');
                    return $rsp;
                }
                $grand_total = $grand_total + $obj->total;
                $array_id_obat[] = $obj->id;

                $kurangStok = $this->kurangStok($obj->id, $obj->id_obat, $obj->jumlah);
                if($kurangStok['status'] == 0) {
                    DB::connection($this->getConnectionName())->rollback();
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
                        $apotek = MasterApotek::on($this->getConnectionName())->find(session('id_apotek_active'));
                        $inisial = strtolower($apotek->nama_singkat);
                        $stok_before = DB::connection($this->getConnectionName())->table('tb_m_stok_harga_'.$inisial)->where('id_obat', $obj->id_obat)->first(); 
                        $stok_now = $stok_before->stok_akhir-$obj->jumlah;

                        # update ke table stok harga
                        $stok_harga = MasterStokHarga::on($this->getConnectionName())->where('id_obat', $obj->id_obat)->first();
                        $stok_harga->stok_awal = $stok_before->stok_akhir;
                        $stok_harga->stok_akhir = $stok_now;
                        $stok_harga->updated_at = date('Y-m-d H:i:s');
                        $stok_harga->updated_by = Auth::user()->id;
                        if($stok_harga->save()) {
                        } else {
                            DB::connection($this->getConnectionName())->rollback();
                            $rsp = array('status' => 0, 'message' => 'Error, periksa kembali data yang disimpan');
                            return $rsp;
                        }

                        # create histori
                        $histori_stok = HistoriStok::on($this->getConnectionName())->where('id_obat', $obj->id_obat)->where('jumlah', $obj->jumlah)->where('id_jenis_transaksi', 1)->where('id_transaksi', $obj->id)->first();
                        if(empty($histori_stok)) {
                            $histori_stok = new HistoriStok;
                            $histori_stok->setDynamicConnection();
                        }
                        $histori_stok->id_obat = $obj->id_obat;
                        $histori_stok->jumlah = $obj->jumlah;
                        $histori_stok->stok_awal = $stok_before->stok_akhir;
                        $histori_stok->stok_akhir = $stok_now;
                        $histori_stok->id_jenis_transaksi = 18; //penjualan operasional
                        $histori_stok->id_transaksi = $obj->id;
                        $histori_stok->batch = null;
                        $histori_stok->ed = null;
                        $histori_stok->sisa_stok = null;
                        $histori_stok->hb_ppn = $obj->hb_ppn;
                        $histori_stok->created_at = date('Y-m-d H:i:s');
                        $histori_stok->created_by = Auth::user()->id;
                        if($histori_stok->save()) {
                        } else {
                             DB::connection($this->getConnectionName())->rollback();
                            $rsp = array('status' => 0, 'message' => 'Error, periksa kembali data yang disimpan');
                            return $rsp;
                        }
                    }

                    if($obj->save()) {
                        $this->grand_total = $grand_total;
                        if($this->save()) {
                            $rsp = array('status' => 1, 'message' => 'Data penjualan operasional berhasil disimpan');
                            return $rsp;
                        } else {
                            DB::connection($this->getConnectionName())->rollback();
                            $rsp = array('status' => 0, 'message' => 'Error, periksa kembali data yang disimpan');
                            return $rsp;
                        }
                    } else {
                        DB::connection($this->getConnectionName())->rollback();
                        $rsp = array('status' => 0, 'message' => 'Error, periksa kembali data yang disimpan');
                        return $rsp;
                    }
                }
            }
        }
       /*
        $this->save();

        if(!empty($array_id_obat)){
            DB::connection($this->getConnection())->statement("DELETE FROM tb_detail_nota_po
                            WHERE id_nota=".$this->id." AND 
                                    id NOT IN(".implode(',', $array_id_obat).")");
        }else{
            DB::connection($this->getConnection())->statement("DELETE FROM tb_detail_nota_po 
                            WHERE id_nota=".$this->id);
        }*/
    }

    public function kurangStok($id_detail, $id_obat, $jumlah) {
        $inisial = strtolower(session('nama_apotek_singkat_active'));
        $cekHistori = DB::connection($this->getConnectionName())->table('tb_histori_stok_'.$inisial)
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
                $keterangan = $cekHistori->keterangan.', PO pada IDdet.'.$id_detail.' sejumlah '.$jumlah;
                DB::connection($this->getConnectionName())->table('tb_histori_stok_'.$inisial)->where('id', $cekHistori->id)->update(['sisa_stok' => $sisa_stok, 'keterangan' => $keterangan]);
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
                    $cekHistoriLanj = DB::connection($this->getConnectionName())->table('tb_histori_stok_'.$inisial)
                            ->where('id_obat', $id_obat)
                            ->whereIn('id_jenis_transaksi', [2,3,11,9])
                            ->where('sisa_stok', '>', 0)
                            ->orderBy('id', 'ASC')
                            ->first();

                    if($cekHistoriLanj->sisa_stok >= $i) {
                        # update selisih jika stok melebihi jumlah
                        $keterangan = $cekHistoriLanj->keterangan.', PO pada IDdet.'.$id_detail.' sejumlah '.$i;
                        $sisa = $cekHistoriLanj->sisa_stok - $i;
                        DB::connection($this->getConnectionName())->table('tb_histori_stok_'.$inisial)->where('id', $cekHistoriLanj->id)->update(['sisa_stok' => $sisa, 'keterangan' => $keterangan]);
                        $array_id_histori_stok_detail[] = array('id_histori_stok' => $cekHistoriLanj->id, 'jumlah' => $i);
                        $total = $total + $cekHistoriLanj->hb_ppn * $i;
                         $i = 0;
                    } else {
                        # update selisih jika stok kurang dari jumlah
                        $keterangan = $cekHistoriLanj->keterangan.', PO pada IDdet.'.$id_detail.' sejumlah '.$cekHistoriLanj->sisa_stok;
                        $sisa = $i - $cekHistoriLanj->sisa_stok;
                        DB::connection($this->getConnectionName())->table('tb_histori_stok_'.$inisial)->where('id', $cekHistoriLanj->id)->update(['sisa_stok' => 0, 'keterangan' => $keterangan]);
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

    public function detail_po(){
        return $this->hasMany('App\TransaksiPODetail', 'id_nota', 'id')->where('tb_detail_nota_po.is_deleted', 0);
    }

    public function apotek_nota(){
        return $this->hasOne('App\MasterApotek', 'id', 'id_apotek_nota');
    }

    public function created_oleh(){
        return $this->hasOne('App\User', 'id', 'created_by');
    }

    public function updated_oleh(){
        return $this->hasOne('App\User', 'id', 'updated_by');
    }

    public function detail_po_total(){
        return $this->hasMany('App\TransaksiPODetail', 'id_nota', 'id')
                    ->select([
                        DB::raw('SUM(tb_detail_nota_po.jumlah * tb_detail_nota_po.harga_jual) AS total')
                    ])
                    ->where('tb_detail_nota_po.is_deleted', 0)->limit(1);
    }
}
