<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use DB;
use App\Traits\DynamicConnectionTrait;

class Pasien extends Authenticatable
{
    use Notifiable;
    use DynamicConnectionTrait;
    
    protected $table = 'tb_m_pasien';

    const MEDICAL_CODE = '33.80.';

    public function generateMedicalNumber()
    {
        $data = DB::connection($this->getConnectionName())->table('tb_m_pasien')->orderBy('nomor_rekam_medis', 'DESC')->first();
        $number = explode('.', $data->nomor_rekam_medis);
        $number = (int)$number[2] + 1;
        return static::MEDICAL_CODE.$number;
    }
}
