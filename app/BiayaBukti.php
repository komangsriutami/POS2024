<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
class BiayaBukti extends Model
{
    protected $table = 'tb_biaya_bukti';
    public $primaryKey = 'id';
    public  $timestamps = false;

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'id_biaya' => 'required',
            'keterangan' => 'required|max:255',
            'file' => 'required'
        ]);
    }

    public function akun(){
        return $this->belongsTo('App\MasterKodeAkun', 'id_akun');
    }

    public function supplier(){
        return $this->belongsTo('App\MasterSuplier', 'id_supplier');
    }
}
