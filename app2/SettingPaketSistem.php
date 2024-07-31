<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;
use App\Traits\DynamicConnectionTrait;

class SettingPaketSistem extends Model
{
    use DynamicConnectionTrait;
    /* 
		Model 	: Model Setting Paket Sistem
		Author 	: Wiwan Gussanda
		Date 	: 11/09/2021
	*/
    protected $table = 'tb_setting_paket_sistem';
    public $primaryKey = 'id';
    protected $fillable = ['id','id_jenis_paket_sistem','fee','jumlah_user','jumlah_dokter','keterangan'];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'fee' => 'required|max:18',
            'jumlah_user' => 'required|max:18',
            'jumlah_dokter' => 'required|max:18',
        ]);
    }

    public function jenisPaketSistem(){
        return $this->hasOne('App\MasterJenisPaketSistem', 'id', 'id_jenis_paket_sistem');
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
