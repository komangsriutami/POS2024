<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;
class SettingStokOpnam extends Model
{
    protected $table = 'tb_setting_stok_opnam';
    public $primaryKey = 'id';
    protected $fillable = ['id_apotek','tgl_so'];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'id_apotek' => 'required',
            'tgl_so' => 'required',
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

    public function apotek(){
        return $this->hasOne('App\MasterApotek', 'id', 'id_apotek');
    }

    public function created_oleh(){
        return $this->hasOne('App\User', 'id', 'created_by');
    }

    public function updated_oleh(){
        return $this->hasOne('App\User', 'id', 'updated_by');
    }
}
