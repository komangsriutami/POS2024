<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;

class SettingPromo extends Model
{
    /* 
		Model 	: Untuk Setting Promo 
		Author 	: Sri U.
		Date 	: 21/06/2020
	*/
		
	protected $table = 'tb_setting_promo';
    public $primaryKey = 'id';
    protected $fillable = ['id_jenis_promo', 'nama', 'tgl_awal', 'tgl_akhir', 'pembelian_ke', 'persen_diskon', 'rp_diskon', 'id_tipe_member'];

    public function validate_persen(){
    	return Validator::make((array)$this->attributes, [
    		'id_jenis_promo' => 'required',
            'id_tipe_member' => 'required',
            'nama' => 'required',
            'tgl_awal' => 'required',
            'tgl_akhir' => 'required',
            'pembelian_ke' => 'required',
            'persen_diskon' => 'required',
        ]);
    }

    public function validate_rp(){
        return Validator::make((array)$this->attributes, [
            'id_jenis_promo' => 'required',
            'id_tipe_member' => 'required',
            'nama' => 'required',
            'tgl_awal' => 'required',
            'tgl_akhir' => 'required',
            'pembelian_ke' => 'required',
            'rp_diskon' => 'required',
        ]);
    }

    public function validate_item(){
        return Validator::make((array)$this->attributes, [
            'id_jenis_promo' => 'required',
            'id_tipe_member' => 'required',
            'nama' => 'required',
            'tgl_awal' => 'required',
            'tgl_akhir' => 'required',
            'pembelian_ke' => 'required',
        ]);
    }

    public function save_plus(){
        $this->created_by = Auth::user()->id;
        $this->save();
    }

    public function save_edit(){
        $this->updated_by = Auth::user()->id;
        $this->save();
    }

    public function details(){
        return $this->hasMany('App\SettingPromoDetail', 'id_setting_promo', 'id');
    }

    public function item_belis(){
        return $this->hasMany('App\SettingPromoItemBeli', 'id_setting_promo', 'id');
    }

    public function item_diskons(){
        return $this->hasMany('App\SettingPromoItemDiskon', 'id_setting_promo', 'id');
    }

}
