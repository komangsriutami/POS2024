<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;
use DB;
use App\Events\PembelianCreate;

class TransaksiPembelian extends Model
{
    //protected $table = 'tb_nota_pembelian';
    protected $table = null;
    public $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = ['id_apotek_nota',
                            'id_jenis_pembayaran',
                            'tgl_nota',
    						'no_faktur',
                            'tgl_faktur',
    						'tgl_jatuh_tempo',
    						'id_suplier',
    						'id_apotek',
    						'diskon1',
    						'diskon2',
    						'ppn',
    						'id_jenis_pembelian',
                            'is_tanda_terima',
                            'is_from_order'
    						];

    public function __construct()
    {
        if(session('id_tahun_active') == date('Y')) {
            $this->setTable('tb_nota_pembelian');
        } else {
            $this->setTable('tb_nota_pembelian_histori');
        }
    }

    public function setTable($tableName)
    {
        $this->table = $tableName;
    }
    						
    public function validate(){
        return Validator::make((array)$this->attributes, [
            'no_faktur' => 'required',
            //'tgl_faktur' => 'required',
           // 'tgl_jatuh_tempo' => 'required',
            'id_suplier' => 'required',
            //'id_apotek' => 'required',
            'diskon1' => 'required',
            'diskon2' => 'required',
            'ppn' => 'required',
            //'id_jenis_pembelian' => 'required',
        ]);
    }

    public function save_from_array($detail_pembelians, $val){
        if($val==1)
        {
            $this->tgl_nota = date('Y-m-d H:i:s');
            $this->id_apotek_nota = session('id_apotek_active');
            $this->created_by = Auth::user()->id;
            $this->created_at = date('Y-m-d H:i:s');
        }else{
            $this->tgl_nota = date('Y-m-d H:i:s');
            $this->id_apotek_nota = session('id_apotek_active');
            $this->updated_by = Auth::user()->id;
            $this->updated_at = date('Y-m-d H:i:s');
        }

        if($this->save()) {
            $id_nota = $this->id;
        } else {
            DB::rollback();
            $rsp = array('status' => 0, 'message' => 'Error, periksa kembali data yang disimpan');
            return $rsp;
        }

        $status = true;
        $str_array_id = array();
        $array_id_obat = array();

        foreach ($detail_pembelians as $detail_pembelian) {
            if(!in_array($detail_pembelian['id_obat'], $array_id_obat)){
                if($detail_pembelian['id']>0){
                    $obj = TransaksiPembelianDetail::find($detail_pembelian['id']);
                }else{
                    $obj = new TransaksiPembelianDetail;
                }
                
                $obj->id_nota = $this->id;
                $obj->id_obat = $detail_pembelian['id_obat'];
                $obj->total_harga = $detail_pembelian['total_harga'];
                $obj->harga_beli = $detail_pembelian['harga_beli'];
                $obj->harga_beli_ppn = $detail_pembelian['harga_beli']+($this->ppn/100*$detail_pembelian['harga_beli']);
                $obj->jumlah = $detail_pembelian['jumlah'];
                $obj->diskon = $detail_pembelian['diskon'];
                $obj->diskon_persen = $detail_pembelian['diskon_persen'];
                $obj->id_batch = $detail_pembelian['id_batch'];
                $obj->tgl_batch = $detail_pembelian['tgl_batch'];
                $obj->created_by = Auth::user()->id;
                $obj->created_at = date('Y-m-d H:i:s');
                $obj->updated_at = date('Y-m-d H:i:s');
                $obj->updated_by = '';
                $obj->is_deleted = 0;

                if($obj->save()) {

                    if(isset($this->is_from_order) AND $this->is_from_order == 1) {
                        $order = TransaksiOrderDetail::find($detail_pembelian['id_detail_order']);
                        $order->is_status = 1;
                        $order->id_nota_pembelian = $this->id;
                        $order->id_det_nota_pembalian = $obj->id;
                        $order->created_by = Auth::id();
                        $order->created_at = date('Y-m-d H:i:s');
                        if($order->save()) {
                        } else {
                            DB::rollback();
                            $rsp = array('status' => 0, 'message' => 'Error, periksa kembali data yang disimpan');
                            return $rsp;
                        }

                        if($order->id_defecta != null OR $order->id_defecta != 0) {
                            DefectaOutlet::where('id', $order->id_defecta)->update(['id_process' => 2, 'last_update_process' => date('Y-m-d H:i:s')]);
                            DefectaOutletHistori::create([
                                'id_defecta' => $order->id_defecta,
                                'id_status' => 1,
                                'created_at' => date('Y-m-d H:i:s'),
                                'created_by' => Auth::id(),
                                'updated_at' => date('Y-m-d H:i:s'),
                                'updated_by' => Auth::id(),
                            ]);
                        }

                        $cek_all_status = TransaksiOrderDetail::where('id_nota', $order->id_nota)->where('is_status', 0)->count();
                        if($cek_all_status < 1) {
                            TransaksiOrder::where('id', $order->id_nota)->update(['is_status' => 1, 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => Auth::id()]);
                        }
                    }

                    $array_id_obat[] = $obj->id;
                    # update stok ke 
                    $apotek = MasterApotek::find(session('id_apotek_active'));
                    $inisial = strtolower($apotek->nama_singkat);
                    $stok_before = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $obj->id_obat)->first();
                    $stok_now = $stok_before->stok_akhir+$obj->jumlah;


                    #histori harga
                    if($stok_before->harga_beli != $obj->harga_beli) {
                        $data_histori_ = array('id_obat' => $obj->id_obat, 'harga_beli_awal' => $stok_before->harga_beli, 'harga_beli_akhir' => $obj->harga_beli, 'harga_jual_awal' => $stok_before->harga_jual, 'harga_jual_akhir' => $stok_before->harga_jual, 'created_by' => Auth::id(), 'created_at' => date('Y-m-d H:i:s'));

                        DB::table('tb_histori_harga_'.$inisial.'')->insert($data_histori_);
                    }

                    /*$arrayupdate = array(
                        'stok_awal'=> $stok_before->stok_akhir, 
                        'stok_akhir'=> $stok_now, 
                        'updated_at' => date('Y-m-d H:i:s'), 
                        'harga_beli' => $obj->harga_beli, 
                        'harga_beli_ppn' => $obj->harga_beli_ppn, 
                        'updated_by' => Auth::user()->id
                    );*/

                    # update ke table stok harga
                    $stok_harga = MasterStokHarga::where('id_obat', $obj->id_obat)->first();
                    $stok_harga->stok_awal = $stok_before->stok_akhir;
                    $stok_harga->stok_akhir = $stok_now;
                    $stok_harga->updated_at = date('Y-m-d H:i:s'); 
                    $stok_harga->harga_beli = $obj->harga_beli;
                    $stok_harga->harga_beli_ppn = $obj->harga_beli_ppn;
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
                        'id_jenis_transaksi' => 2, //pembelian
                        'id_transaksi' => $obj->id,
                        'batch' => $obj->id_batch,
                        'ed' => $obj->tgl_batch,
                        'sisa_stok' => $obj->jumlah,
                        'hb_ppn' => $obj->harga_beli_ppn,
                        'keterangan' => 'Pembelian pada IDdet.'.$obj->id.' sejumlah '.$obj->jumlah,
                        'created_at' => date('Y-m-d H:i:s'),
                        'created_by' => Auth::user()->id
                    );*/

                    # create histori
                    $histori_stok = HistoriStok::where('id_obat', $obj->id_obat)->where('jumlah', $obj->jumlah)->where('id_jenis_transaksi', 2)->where('id_transaksi', $obj->id)->first();
                    if(empty($histori_stok)) {
                        $histori_stok = new HistoriStok;
                    }
                    $histori_stok->id_obat = $obj->id_obat;
                    $histori_stok->jumlah = $obj->jumlah;
                    $histori_stok->stok_awal = $stok_before->stok_akhir;
                    $histori_stok->stok_akhir = $stok_now;
                    $histori_stok->id_jenis_transaksi = 2; //pembelian
                    $histori_stok->id_transaksi = $obj->id;
                    $histori_stok->batch = $obj->id_batch;
                    $histori_stok->ed = $obj->tgl_batch;
                    $histori_stok->sisa_stok = $obj->jumlah;
                    $histori_stok->hb_ppn = $obj->harga_beli_ppn;
                    $histori_stok->keterangan = 'Pembelian pada IDdet.'.$obj->id.' sejumlah '.$obj->jumlah;
                    $histori_stok->created_at = date('Y-m-d H:i:s');
                    $histori_stok->created_by = Auth::user()->id;
                    if($histori_stok->save()) {

                    } else {
                         DB::rollback();
                        $rsp = array('status' => 0, 'message' => 'Error, periksa kembali data yang disimpan');
                        return $rsp;
                    }

                   if($this->save()) {
                        $rsp = array('status' => 1, 'message' => 'Data pembelian berhasil disimpan');
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

        /*if(!empty($array_id_obat)){
            DB::statement("DELETE FROM tb_detail_nota_pembelian
                            WHERE id_nota=".$this->id." AND 
                                    id NOT IN(".implode(',', $array_id_obat).")");
        }else{
            DB::statement("DELETE FROM tb_detail_nota_pembelian 
                            WHERE id_nota=".$this->id);
        }*/
    }

    public function save_plus(){
        $this->created_by = Auth::user()->id;
        $this->save();
    }

    public function save_edit(){
        $this->updated_by = Auth::user()->id;
        $this->save();
    }

    public function detail_pembalian(){
        return $this->hasMany('App\TransaksiPembelianDetail', 'id_nota', 'id')->where('is_deleted', 0);
    }

    public function detail_pembelian_total(){
        if(session('id_tahun_active') == date('Y')) {
            $detTable = 'tb_detail_nota_pembelian';
        } else {
            $detTable = 'tb_detail_nota_pembelian_histori';
        }

        return $this->hasMany('App\TransaksiPembelianDetail', 'id_nota', 'id')
                    ->select([
                        DB::raw("SUM($detTable.diskon) AS total_diskon"),
                        DB::raw("SUM($detTable.jumlah_retur * $detTable.harga_beli) AS total_retur"),
                        DB::raw("SUM(($detTable.jumlah - jumlah_retur) * $detTable.harga_beli) AS total_lunas"),
                        DB::raw("SUM($detTable.diskon_persen * $detTable.total_harga/100) AS total_diskon_persen"),
                        DB::raw("SUM($detTable.total_harga) AS jumlah")
                    ])
                    ->where("$detTable.is_deleted", 0)->limit(1);
    }

    public function created_oleh(){
        return $this->hasOne('App\User', 'id', 'created_by');
    }

    public function updated_oleh(){
        return $this->hasOne('App\User', 'id', 'updated_by');
    }

    public function apotek(){
        return $this->hasOne('App\MasterApotek', 'id', 'id_apotek');
    }

    public function suplier(){
        return $this->hasOne('App\MasterSuplier', 'id', 'id_suplier');
    }

    public function jenis_pembelian(){
        return $this->hasOne('App\MasterJenisPembelian', 'id', 'id_jenis_pembelian');
    }
}
