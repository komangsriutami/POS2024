<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;
use App\Traits\DynamicConnectionTrait;

class RbacMenu extends Model
{
    use DynamicConnectionTrait;
    /* 
		Model 	: Untuk RBAC Menu 
		Author 	: 
		Date 	: 
	*/
    protected $table = 'rbac_menu';
    public $primaryKey = 'id';

    protected $fillable = ['nama_singkatan', 'nama_panjang', 'deskripsi', 'route_group', 'link', 'parent', 'sub_parent','depth', 'weight', 'id_icon', 'flag_core', 'informasi', 'error_log', 'faq'];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'nama_singkatan' => 'required',
            'link' => 'required',
            'route_group' => 'required',
		]);
    }

    public function save_now($val){
        if($val==1)
        {
            $this->created_at = date('Y-m-d H:i:s');
            $this->created_by = Auth::id();
            $id_menu = $this->save();
        }else{
            $this->updated_at = date('Y-m-d H:i:s');
            $this->updated_by = Auth::id();
            $id_menu = $this->save();
        }

    }

    public function created_oleh(){
        return $this->hasOne('App\Users', 'id', 'created_by');
    }

    public function updated_oleh(){
        return $this->hasOne('App\Users', 'id', 'updated_by');
    }
}
