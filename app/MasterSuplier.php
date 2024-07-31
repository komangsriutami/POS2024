<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;

class MasterSuplier extends Model
{
    /*
    	Model 	: Untuk Master Suplier 
		Author 	: Adistya
		Date 	: 22/02/2020
	*/

	protected $table = 'tb_m_suplier';
    public $primaryKey = 'id';
    protected $fillable = ['nama',
    						'npwp',
    						'alamat',
    						'id_kabupaten',
    						'id_provinsi',
    						'operator',
    						'telepon',
    						'keterangan',
    						'update_at',
    						'create_at',
    						'create_by',
    						'update_by',
    						'is_deleted'
    						];  

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'nama' => 'required|max:100',
            'npwp' => 'max:50',
            'alamat' => 'required|max:100',
            'id_kabupaten' => 'required|max:11',
            'id_provinsi' => 'required|max:11',
            'operator' => 'required|max:50',
            'telepon' => 'required|max:20'
        ]);
    }

	public function save_plus(){
        $this->created_at = date('Y-m-d H:i:s');
        $this->created_by = Auth::user()->id;
        $this->save();
    }

    public function save_edit(){
        $this->updated_at = date('Y-m-d H:i:s');
        $this->updated_by = Auth::user()->id;
        $this->save();
    }

    public function kabupaten(){
        return $this->hasOne('App\MasterKabupaten', 'id', 'id_kabupaten');
    }

    public function provinsi(){
        return $this->hasOne('App\MasterProvinsi', 'id', 'id_provinsi');
    }

    public function created_oleh(){
        return $this->hasOne('App\Users', 'id', 'created_by');
    }

    public function updated_oleh(){
        return $this->hasOne('App\Users', 'id', 'updated_by');
    }
}
