<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;

class MasterAgama extends Model
{
    /* 
		Model 	: Untuk Master Agama 
		Author 	: Sri U.
		Date 	: 24/02/2020
	*/
    protected $table = 'tb_m_agama';
    public $primaryKey = 'id';
    protected $fillable = ['id','agama'];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'agama' => 'required|max:255',
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
