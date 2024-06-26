<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;
use DB;

class RbacRole extends Model
{
    /* 
		Model 	: Untuk RBAC Role 
		Author 	: 
		Date 	: 
	*/
    protected $table = 'rbac_roles';
    public $primaryKey = 'id';
    protected $fillable = ['nama', 'deksripsi', 'weight', 'user_default', 'reviewer_default'];

    public function validate()
    {
        return Validator::make((array)$this->attributes, [
            'nama' => 'required|max:255',
        ]);
    }

    public function save_now($val, $permission_roles)
    {
        if ($val == 1) {
            $this->created_by = 1;
            $id_role = $this->save();
        } else {
            $this->updated_by = 1;
            $id_role = $this->save();
        }

        $array_id_permission = array();

        foreach ($permission_roles as $key) {
            $cek = RbacRolePermission::where('id_permission', $key['id_permission'])->where('id_role', $this->id)->first();

            if (empty($cek)) {
                $obj = new RbacRolePermission;
            } else {
                $obj = RbacRolePermission::find($cek->id);
            }

            $obj->id_role = $this->id;
            $obj->id_permission = (int) $key['id_permission'];
            $obj->created_by = Auth::id();
            $obj->updated_by = Auth::id();
            $obj->is_deleted = 0;

            $obj->save();
            $array_id_permission[] = $obj->id;
        }

        if (!empty($array_id_permission)) {
            DB::statement("DELETE FROM rbac_role_perm
                            WHERE id_role=" . $this->id . " AND 
                                    id NOT IN(" . implode(',', $array_id_permission) . ")");
        } else {
            DB::statement("DELETE FROM rbac_role_perm 
                            WHERE id_role=" . $this->id);
        }
    }

    public function created_oleh()
    {
        return $this->hasOne('App\Users', 'id', 'created_by');
    }

    public function updated_oleh()
    {
        return $this->hasOne('App\Users', 'id', 'updated_by');
    }
}
