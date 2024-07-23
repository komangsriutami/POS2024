<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DynamicConnectionTrait;

class MasterStokHarga extends Model
{
    use HasFactory;
    use DynamicConnectionTrait;

    protected $table = null;
    public $primaryKey = 'id';
    protected $fillable = ['id_obat', 'stok_awal', 'stok_akhir', 'harga_beli', 'harga_jual', 'stok_awal_so', 'stok_akhir_so', 'selisih', 'so_at', 'so_by'];

    public function __construct()
    {
           $this->setTable('tb_m_stok_harga_'.session('nama_apotek_singkat_active')) ;
    }

    public function setTable($tableName)
    {
        $this->table = $tableName;
    }

    public function obat(){
        return $this->hasOne('App\MasterObat', 'id', 'id_obat');
    }
}
