<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;

class SettingPromoDetail extends Model
{
    /* 
		Model 	: Untuk Setting Promo
		Author 	: Sri U.
		Date 	: 21/06/2020
	*/
		
	protected $table = 'tb_setting_promo_detail';
    public $primaryKey = 'id';
    protected $fillable = ['id_setting_promo','id_apotek'];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
    		'id_setting_promo' => 'required',
            'id_apotek' => 'required',
        ]);
    }

    public function save_plus(){
        $this->created_at = date('Y-m-d H:i:s');
        $this->save();
    }

    public function save_edit(){
        $this->updated_at = date('Y-m-d H:i:s');
        $this->save();
    }
}
