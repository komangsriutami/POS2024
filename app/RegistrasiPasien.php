<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\DynamicConnectionTrait;

class RegistrasiPasien extends Model
{
    use DynamicConnectionTrait;
    protected $table = 'tb_registrasi_pasien';
}
