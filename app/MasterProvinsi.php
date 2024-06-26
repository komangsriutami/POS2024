<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;

class MasterProvinsi extends Model
{
    /*
    	Model 	: Untuk Master Suplier 
		Author 	: Adistya
		Date 	: 22/02/2020
	*/

	protected $table = 'tb_m_provinsi';
    public $primaryKey = 'id';
    protected $fillable = [ 'nama',
    						'created_at',
    						'updated_at',
    						'created_by',
    						'updated_by',
    						'is_deleted'
    						];  

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'nama' => 'required|max:100',
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
