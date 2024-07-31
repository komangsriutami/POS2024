<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;

class MasterKartu extends Model
{
    /* 
		Model 	: Untuk Master Katu Debet/Kredit 
		Author 	: Sri U.
		Date 	: 27/02/2020
	*/

	protected $table = 'tb_m_kartu_debet_credit';
    public $primaryKey = 'id';
    protected $fillable = ['nama','charge', 'id_jenis_kartu'];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'nama' => 'required|max:255',
            'charge' => 'required',
            'id_jenis_kartu' => 'required'
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

    public function jenis_kartu(){
        return $this->hasOne('App\MasterJenisKartu', 'id', 'id_jenis_kartu');
    }
}
