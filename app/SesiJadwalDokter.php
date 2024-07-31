<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SesiJadwalDokter extends Model
{
    protected $table = 'tb_sesi_dokter';
    public $primaryKey = 'id';
    protected $fillable = ['sesi', 'keterangan'];
}
