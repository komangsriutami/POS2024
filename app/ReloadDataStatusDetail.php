<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReloadDataStatusDetail extends Model
{
    /* 
		Model 	: Untuk Reload Data Status
		Author 	: 
		Date 	: 
	*/
    protected $table = 'tb_reloaddata_status';
    public $primaryKey = 'id';
    public  $timestamps = false;

    public function updated_oleh()
    {
        return $this->hasOne('App\User', 'id', 'updated_by');
    }
}
