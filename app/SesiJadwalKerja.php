<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
class SesiJadwalKerja extends Model
{
    protected $table = 'tb_sesi_jadwal_kerja';
    public $primaryKey = 'id';
    protected $fillable = ['nama'];
}
