<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use App\Traits\DynamicConnectionTrait;

class SkemaGaji extends Model
{
    use DynamicConnectionTrait;
    protected $table = 'tb_skema_gaji';
    public $primaryKey = 'id';
    public  $timestamps = false;
    protected $fillable = ['id_group_apotek', 'nama', 'tgl_berlaku_start', 'tgl_berlaku_end'];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
    		'id_group_apotek' => 'required',
            'nama' => 'required|max:255',
            'tgl_berlaku_start' => 'required',
            'tgl_berlaku_end' => 'required',
        ]);
    }
}
