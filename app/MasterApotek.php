<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;

class MasterApotek extends Model
{
    /* 
		Model 	: Untuk Master Apotek 
		Author 	: Sri U.
		Date 	: 22/02/2020
	*/

	protected $table = 'tb_m_apotek';
    public $primaryKey = 'id';
    protected $fillable = ['id_group_apotek',
                            'kode_apotek',
                            'nama_singkat',
                            'nama_panjang',
    						'alamat',
    						'telepon',
    						'id_apoteker',
    						'nostra',
    						'nosia',
    						'tanggalberdiri'
    						];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'id_group_apotek' => 'required',
            'kode_apotek' => 'required',
            'nama_singkat' => 'required|max:255',
            'nama_panjang' => 'required|max:255',
            'alamat' => 'required',
            'telepon' => 'required|max:25',
            'id_apoteker' => 'required',
            'nostra' => 'required',
            'nosia' => 'required',
        ]);
    }


    public function save_plus(){
        $this->created_at = date('Y-m-d H:i:s');
        $this->created_by = Auth::user()->id;
        $this->tanggalberdiri = date('Y-m-d', strtotime($this->tanggalberdiri ));
        $this->save();
    }

    public function save_edit(){
        $this->created_by = date('Y-m-d H:i:s');
        $this->updated_by = Auth::user()->id;
        $this->tanggalberdiri = date('Y-m-d', strtotime($this->tanggalberdiri ));
        $this->save();
    }

    public function apoteker(){
        return $this->hasOne('App\User', 'id', 'id_apoteker');
    }

    public function group_apotek(){
        return $this->hasOne('App\MasterGroupApotek', 'id', 'id_group_apotek');
    }

    public function created_oleh(){
        return $this->hasOne('App\Users', 'id', 'created_by');
    }

    public function updated_oleh(){
        return $this->hasOne('App\Users', 'id', 'updated_by');
    }
}
