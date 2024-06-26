<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;

class RbacRolePermission extends Model
{
    /* 
		Model 	: Untuk RBAC Role Permission
		Author 	: 
		Date 	: 
	*/
    protected $table = 'rbac_role_perm';
    public $primaryKey = 'id';
    protected $fillable = ['id_role', 'id_permission'];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'id_role' => 'required',
            'id_permission' => 'required',
		]);
    }


    public function Permission()
    {
        return $this->hasOne('App\Permission', 'id', 'id_permission');
    }

    public function created_oleh(){
        return $this->hasOne('App\Users', 'id', 'created_by');
    }

    public function updated_oleh(){
        return $this->hasOne('App\Users', 'id', 'updated_by');
    }
}
