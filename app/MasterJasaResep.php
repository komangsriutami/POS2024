<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;

class MasterJasaResep extends Model
{
    /* 
		Model 	: Untuk Master Jasa Resep 
		Author 	: Sri U.
		Date 	: 27/02/2020
	*/

	protected $table = 'tb_m_jasa_resep';
    public $primaryKey = 'id';
    protected $fillable = ['nama',
    						'biaya'
    						];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'nama' => 'required|max:255',
            'biaya' => 'required'
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
