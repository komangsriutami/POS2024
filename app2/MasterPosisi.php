<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use App\Traits\DynamicConnectionTrait;

class MasterPosisi extends Model
{
    use DynamicConnectionTrait;
    protected $table = 'tb_m_posisi';
    public $primaryKey = 'id';
    public  $timestamps = false;
    protected $fillable = ['id_group_apotek', 'nama', 'deskripsi'];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
    		'id_group_apotek' => 'required',
            'nama' => 'required|max:255',
        ]);
    }
}