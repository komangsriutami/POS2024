<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
class JenisSP extends Model
{
    protected $table = 'tb_m_jenis_sp';
    public $primaryKey = 'id';
    public  $timestamps = false;
    protected $fillable = ['id', 'jenis'];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'jenis' => 'required|max:255',
        ]);
    }
}
