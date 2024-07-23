<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Validator;
use App\Traits\DynamicConnectionTrait;

class SkemaGajiDetail extends Model
{
    use HasFactory;
    use DynamicConnectionTrait;
    protected $table = 'tb_skema_gaji_detail';
    public $primaryKey = 'id';
    public  $timestamps = false;
    protected $fillable = ['id_skema_gaji', 'id_jabatan', 'id_posisi', 'id_status_karyawan', 'gaji_pokok', 'persen_omset', 'tunjangan_jabatan', 'tunjangan_ijin', 'tunjangan_makan', 'tunjangan_transportasi', 'lembur', 'pph', 'potongan_keterlambatan'];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
    		'id_skema_gaji' => 'required',
            'id_jabatan' => 'required|max:255',
            'id_posisi' => 'required',
            'id_status_karyawan' => 'required',
            'gaji_pokok' => 'required',
        ]);
    }
}
