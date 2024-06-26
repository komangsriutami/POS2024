<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\RegistrasiPasien;

class RegistrasiPasienCart extends Model
{
    protected $table = 'tb_registrasi_pasien_cart';
    protected $fillable = [
        'session_id',
        'doctor_id',
        'booking_date',
        'nama',
        'tgl_lahir',
        'id_jenis_kelamin',
        'id_status_perkawinan',
        'telepon',
        'email',
        'alamat',
        'alergi_obat',
        'is_pernah_berobat',
        'tgl_periksa',
        'id_pilihan_jam',
        'no_urut',
        'jam_kedatangan'
    ];

    public static function generateNoUrut($date = null) {
        $data = RegistrasiPasien::where('tgl_periksa', $date)->orderBy('no_urut','DESC')->first();
        if($data){
            return (int)$data->no_urut+1;
        }else{
            return 1;
        }
    }
}
