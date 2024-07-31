<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
class Absensi extends Model
{
    protected $table = 'tb_absensi';
    public $primaryKey = 'id';
    public  $timestamps = false;
    protected $fillable = ['id_apotek', 'id_kasir_aktif', 'id_user', 'tgl', 'jam_datang', 'jam_pulang', 'jumlah_jam_kerja'];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'id_kasir_aktif' => 'required|max:255',
        ]);
    }

    public function apotek(){
        return $this->belongsTo('App\MasterApotek', 'id_apotek');
    }

    public function user(){
        return $this->belongsTo('App\User', 'id_user');
    }

    public function kasir(){
        return $this->belongsTo('App\User', 'id_kasir_aktif');
    }
}
