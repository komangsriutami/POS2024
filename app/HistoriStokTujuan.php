<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoriStokTujuan extends Model
{
    use HasFactory;

    protected $table = null;
    public $primaryKey = 'id';
    protected $fillable = ['id_obat', 'jumlah', 'stok_awal', 'stok_akhir', 'id_jenis_transaksi', 'id_transaksi', 'batch', 'ed', 'hb_ppn', 'keterangan', 'sisa_stok'];

    public function __construct()
    {
    }

    public function setTable($tableName)
    {
        $this->table = $tableName;
    }

    public function obat(){
        return $this->hasOne('App\MasterObat', 'id', 'id_obat');
    }
}
