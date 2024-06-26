<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;

class MasterJenisPaketSistem extends Model
{
        /* 
		Model 	: Untuk Master Jenis Paket Sistem 
		Author 	: Wiwan Gussanda.
		Date 	: 11/09/2021
	*/
    protected $table = 'tb_m_jenis_paket_sistem';
    public $primaryKey = 'id';
    protected $fillable = ['id','nama', 'keterangan'];
    public $timestamps = false;

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'nama' => 'required|max:255',
            'keterangan' => 'required|max:255',
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

    public function created_oleh(){
        return $this->hasOne('App\Users', 'id', 'created_by');
    }

    public function updated_oleh(){
        return $this->hasOne('App\Users', 'id', 'updated_by');
    }
}
