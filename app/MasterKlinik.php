<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;

class MasterKlinik extends Model
{
    /* 
		Model 	: Untuk Master Klinik 
		Author 	: Sri U.
		Date 	: 27/02/2020
	*/
		
	protected $table = 'tb_m_klinik';
    public $primaryKey = 'id';
    protected $fillable = ['id_group_apotek', 'nama', 'alamat', 'telepon', 'email'];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'id_group_apotek' => 'required',
    		'nama' => 'required',
            'alamat' => 'required',
            'telepon' => 'required',
            'email' => 'required',
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
