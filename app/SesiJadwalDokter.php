<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\DynamicConnectionTrait;

class SesiJadwalDokter extends Model
{
    use DynamicConnectionTrait;
    protected $table = 'tb_sesi_dokter';
    public $primaryKey = 'id';
    protected $fillable = ['sesi', 'keterangan'];
}
