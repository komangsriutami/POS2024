<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;

class MasterJenisPromo extends Model
{
    /* 
		Model 	: Untuk Master Jenis Promo
		Author 	: Sri U.
		Date 	: 21/06/2020
	*/
		
	protected $table = 'tb_m_jenis_promo';
    public $primaryKey = 'id';
    protected $fillable = ['nama', 'keterangan'];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'nama' => 'required|max:255',
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
}
