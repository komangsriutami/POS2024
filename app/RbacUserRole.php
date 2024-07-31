<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;

class RbacUserRole extends Model
{
    /* 
		Model 	: Untuk RBAC User Role
		Author 	: Sri U.
		Date 	: 17/2/2020
	*/
    protected $table = 'rbac_user_role';

    public function role(){
    	return $this->belongsTo('App\RbacRole', 'id_role');
    }

    public function created_oleh(){
        return $this->hasOne('App\Users', 'id', 'created_by');
    }

    public function updated_oleh(){
        return $this->hasOne('App\Users', 'id', 'updated_by');
    }
}
