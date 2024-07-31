<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use App\Traits\DynamicConnectionTrait;

class SesiJadwalKerja extends Model
{
    use DynamicConnectionTrait;
    protected $table = 'tb_sesi_jadwal_kerja';
    public $primaryKey = 'id';
    protected $fillable = ['nama'];
}
