<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;

class SettingPromoItemDiskon extends Model
{
    /* 
		Model 	: Untuk Setting Promo ->item diskon
		Author 	: Sri U.
		Date 	: 21/06/2020
	*/
		
	protected $table = 'tb_setting_promo_item_diskon';
    public $primaryKey = 'id';
    protected $fillable = ['id_setting_diskon', 'id_obat', 'jumlah'];

    public function validate(){
        return Validator::make((array)$this->attributes, [
            'id_setting_diskon' => 'required',
            'id_obat' => 'required',
            'jumlah' => 'required',
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
