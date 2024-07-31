<?php

namespace App\SO;

use Illuminate\Database\Eloquent\Model;

class StokHargaSG extends Model
{
    /* 
		Model 	: Untuk Master Stok dan Harga  
		Author 	: 
		Date 	: 
	*/
    protected $table = 'tb_m_stok_harga_sg';
    public $primaryKey = 'id';
    protected $fillable = ['id_obat', 'stok_awal', 'stok_akhir', 'harga_beli', 'harga_jual', 'stok_awal_so', 'stok_akhir_so', 'selisih', 'so_at', 'so_by'];

    public function obat(){
        return $this->hasOne('App\MasterObat', 'id', 'id_obat');
    }
}
