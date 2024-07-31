<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;

class MasterTahun extends Model
{
    /* 
		Model 	: Untuk Master Tahun 
		Author 	: Sri U.
		Date 	: 27/02/2021
	*/
		
	protected $table = 'tb_m_tahun';
    public $primaryKey = 'id';
    protected $fillable = ['tahun'];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'tahun' => 'required',
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
