<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;
class MasterKodeAkunSub extends Model
{
	/* 
		Model 	: Untuk Master Kode Akuntansi
		Author 	: Surya Adiputra.
		Date 	: 29/05/2020
	*/
    protected $table = 'tb_m_sub_kode_akun';
    public $primaryKey = 'id';
    protected $fillable = ['id_kode_akun',
    						'kode',
    						'nama'
    						];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'kode' => 'required',
            'nama' => 'required',
        ]);
    }

    public function save_plus(){
        $this->created_by = Auth::user()->id;
        $this->created_at = date('Y-m-d H:i:s');
        $this->save();
    }

    public function save_edit(){
        $this->updated_by = Auth::user()->id;
        $this->updated_at = date('Y-m-d H:i:s');
        $this->save();
    }

    public function kode_akun(){
        return $this->hasOne('App\MasterKodeAkun', 'id', 'id_kode_akun');
    }

    public function created_oleh(){
        return $this->hasOne('App\User', 'id', 'created_by');
    }

    public function updated_oleh(){
        return $this->hasOne('App\User', 'id', 'updated_by');
    }
}
