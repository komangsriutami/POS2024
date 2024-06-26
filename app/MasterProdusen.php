<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;

class MasterProdusen extends Model
{
     /*
    	Model 	: Untuk Master Suplier 
		Author 	: Adistya
		Date 	: 25/02/2020
	*/

	protected $table = 'tb_m_produsen';
    public $primaryKey = 'id';
    protected $fillable = ['nama',
    						'alamat',
    						'id_kabupaten',
    						'id_provinsi',
    						'telepon',
    						'keterangan',
    						'create_at',
    						'update_at',
    						'create_by',
    						'update_by',
    						'is_deleted'
    						];  

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'nama' => 'required|max:100',
            'alamat' => 'required|max:100',
            'id_kabupaten' => 'required|max:11',
            'id_provinsi' => 'required|max:11',
            'telepon' => 'required|max:20',
            'keterangan' => 'required',
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
}
