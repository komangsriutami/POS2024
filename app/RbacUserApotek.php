<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;

class RbacUserApotek extends Model
{
    /* 
		Model 	: Untuk RBAC User Apotek
		Author 	: Sri U.
		Date 	: 17/2/2020
	*/
    protected $table = 'rbac_user_apotek';

    public function apotek(){
    	return $this->belongsTo('App\MasterApotek', 'id_apotek');
    }

    public function created_oleh(){
        return $this->hasOne('App\Users', 'id', 'created_by');
    }

    public function updated_oleh(){
        return $this->hasOne('App\Users', 'id', 'updated_by');
    }
}
