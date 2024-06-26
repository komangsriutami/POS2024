<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;

class MasterJenisKartu extends Model
{
    /* 
		Model 	: Untuk Master Jenis Kartu
		Author 	: Surya Adiputra.
		Date 	: 03/03/2020
	*/

	protected $table = 'tb_m_jenis_kartu';
    public $primaryKey = 'id';
    protected $fillable = ['nama'
    						];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'nama' => 'required|max:255'
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
