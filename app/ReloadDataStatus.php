<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReloadDataStatus extends Model
{
    /* 
		Model 	: Untuk Reload Data Status
		Author 	: 
		Date 	: 
	*/
    protected $table = 'tb_reloaddata';
    public $primaryKey = 'id';
    public  $timestamps = false;

    public function detail($id_apotek,$tgl_nota){
        return $this->hasMany('App\ReloadDataStatusDetail', 'id_reloaddata', 'id')
                ->where('tglreload',$tgl_nota)
                ->where('id_apotek',$id_apotek);
    }
}
