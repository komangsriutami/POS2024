<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;
use App\Traits\DynamicConnectionTrait;

class RbacPermission extends Model
{
    use DynamicConnectionTrait;
    /* 
		Model 	: Untuk RBAC Permission
		Author 	: 
		Date 	: 
	*/
    protected $table = 'rbac_permissions';
    public $primaryKey = 'id';
    protected $fillable = ['id_menu', 'nama', 'uri', 'type', 'method', 'group'];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'uri' => 'required|max:255',
		]);
    }
}
