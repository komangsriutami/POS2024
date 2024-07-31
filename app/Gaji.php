<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
class Gaji extends Model
{
    protected $table = 'tb_skema_gaji';
    public $primaryKey = 'id';
    public  $timestamps = false;
    protected $fillable = ['id_skema_gaji', 'id_user', 'tahun', 'bulan', 'jumlah_hari_kerja', 'jumlah_jam_lembur', 'bonus', 'thr', 'total_gaji'];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
    		'id_skema_gaji' => 'required',
            'id_user' => 'required|max:255',
            'tahun' => 'required',
            'bulan' => 'required',
            'jumlah_jam_lembur' => 'required',
            'jumlah_keterlambatan' => 'required',
            'bonus' => 'required',
            'thr' => 'required',
            'total_gaji' => 'required',
        ]);
    }
}
