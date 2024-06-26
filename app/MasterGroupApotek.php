<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;

class MasterGroupApotek extends Model
{
    /* 
		Model 	: Untuk Master Group Apotek 
		Author 	: Sri U.
		Date 	: 01/03/2020
	*/

	protected $table = 'tb_m_group_apotek';
    public $primaryKey = 'id';
    protected $fillable = ['kode', 
                            'nama_singkat',
                            'nama_panjang',
    						'alamat',
    						'telepon',
    						'deskripsi'
    						];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'kode' => 'required',
            'nama_singkat' => 'required|max:255',
            'nama_panjang' => 'required|max:255',
            'alamat' => 'required',
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

    public function created_oleh(){
        return $this->hasOne('App\Users', 'id', 'created_by');
    }

    public function updated_oleh(){
        return $this->hasOne('App\Users', 'id', 'updated_by');
    }
}
