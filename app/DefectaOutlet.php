<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;
use App\Traits\DynamicConnectionTrait;

class DefectaOutlet extends Model
{
    use DynamicConnectionTrait;
    /* 
		Model 	: Untuk Defecta Outlet
		Author 	: 
		Date 	: 
	*/
    protected $table = 'tb_defecta_outlet';
    public $primaryKey = 'id';
    protected $fillable = ['id_obat', 'id_suplier_order', 'id_apotek', 'jumlah_diajukan', 'jumlah_order', 'komentar', 'total_stok', 'total_buffer', 'forcasting', 'jumlah_penjualan', 'margin', 'harga_beli', 'id_satuan', 'id_status', 'id_process'];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'id_obat' => 'required',
            'id_suplier_order' => 'required',
            'id_apotek' => 'required',
            'jumlah_diajukan' => 'required',
            //'total_stok' => 'required',
            //'total_buffer' => 'required',
            //'forcasting' => 'required',
           // 'jumlah_penjualan' => 'required',
            //'margin' => 'required'
        ]);
    }

    public function data_pembelians(){
        return $this->hasMany('App\TransaksiPembelianDetail', 'id_obat', 'id_obat')
                    ->select(['b.nama', 'tb_detail_nota_pembelian.id'])
                    ->join('tb_nota_pembelian as a', 'a.id', 'tb_detail_nota_pembelian.id_nota')
                    ->join('tb_m_suplier as b', 'b.id', 'a.id_suplier')
                    ->where('a.is_deleted', 0)
                    ->orderBy('a.id', 'desc')->limit(3);
    }

    public function obat(){
        return $this->hasOne('App\MasterObat', 'id', 'id_obat');
    }

    public function suplier(){
        return $this->hasOne('App\MasterSuplier', 'id', 'id_suplier_order');
    }

    public function satuan(){
        return $this->hasOne('App\MasterSatuan', 'id', 'id_satuan');
    }

    public function statusOrder(){
        return $this->hasOne('App\MasterStatusOrder', 'id', 'id_status');
    }

}
