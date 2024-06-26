<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth; 

class MasterPaketWD extends Model
{
    /* 
		Model 	: Untuk paket pengobatan dokter
		Author 	: Sri U.
		Date 	: 25/02/2020
	*/

	protected $table = 'tb_m_paket_wd';
    public $primaryKey = 'id';
    protected $fillable = ['nama', 'harga', 'keterangan'];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
    		'nama' => 'required',
            'harga' => 'required',
            'keterangan' => 'required',
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
