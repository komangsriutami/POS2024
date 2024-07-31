<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;

class MasterJenisPembelian extends Model
{
    /* 
		Model 	: Untuk Master Jenis Pembelian 
		Author 	: Sri U.
		Date 	: 27/02/2020
	*/
		
	protected $table = 'tb_m_jenis_pembelian';
    public $primaryKey = 'id';
    protected $fillable = ['id','jenis_pembelian'];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'jenis_pembelian' => 'required|max:255',
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
