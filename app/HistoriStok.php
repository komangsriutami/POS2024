<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DynamicConnectionTrait;

class HistoriStok extends Model
{
    use HasFactory;
    use DynamicConnectionTrait;

    protected $table = null;
    public $primaryKey = 'id';
    protected $fillable = ['id_obat', 'jumlah', 'stok_awal', 'stok_akhir', 'id_jenis_transaksi', 'id_transaksi', 'batch', 'ed', 'hb_ppn', 'keterangan', 'sisa_stok'];

    public function __construct()
    {
           $this->setTable('tb_histori_stok_'.session('nama_apotek_singkat_active')) ;
    }

    public function setTable($tableName)
    {
        $this->table = $tableName;
    }

    public function obat(){
        return $this->hasOne('App\MasterObat', 'id', 'id_obat');
    }
}
