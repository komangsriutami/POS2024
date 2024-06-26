<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
class MasterSyaratPembayaran extends Model
{
    protected $table = 'tb_m_syarat_pembayaran';
    public $primaryKey = 'id';
    protected $fillable = ['nama', 'jangka_waktu'];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'nama' => 'required|max:100',
            'jangka_waktu' => 'required',
        ]);
    }
}
