<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\RbacRole;
use App\RbacPermission;
use App\RbacRolePermission;
use App\RbacMenu;

use DB;
use URL;
use Datatables;

class RoleController extends Controller
{
    /*
    	========================================================================================================================================
    	For     : Tampilan index role
    	Author 	: 
		Date 	: 
		========================================================================================================================================
    */
    public function index()
    {
        //
        $roles = RbacRole::where('is_deleted', 0)->pluck('nama','id');
        $roles->prepend('-- Pilih Role --','');

        return view('role.index')->with(compact('roles'));
    }



    /*
    	========================================================================================================================================
    	For     : List Role yang diload pada halaman index role
    	Author 	: 
		Date 	: 
		========================================================================================================================================
    */
    public function list_role(Request $request)
    {
        DB::statement(DB::raw('set @rownum = 0'));
        $data = DB::table('rbac_roles')
        ->select([DB::raw('@rownum  := @rownum  + 1 AS no'), 
        		'rbac_roles.*'])
        ->where(function($query) use($request){
            $query->where('rbac_roles.is_deleted','=','0');
            $query->where('rbac_roles.id','LIKE',($request->id_role > 0 ? $request->id_role : '%'.$request->id_role.'%'));
        });

        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request){
            $query->where(function($query) use($request){
                $query->orwhere('nama','LIKE','%'.$request->get('search')['value'].'%');
            });
        })
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .= '
                    <a href="'.url('/role/'.$data->id.'/edit').'" title="Edit data" class="btn btn-info"><span data-toggle="tooltip" data-placement="top" title="Edit data"><i class="fa fa-edit"></i></span></a>  

                      ';
            $btn .= '<span class="btn btn-danger" onClick="delete_role('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus data"><i class="fa fa-times"></i></span>';
            $btn .='</div>';
            return $btn;
        })
        ->addIndexColumn()
        ->rawColumns(['DT_RowIndex', 'action'])
        ->make(true);  
    }  



    /*
    	========================================================================================================================================
    	For     : call create role function
    	Author 	: 
		Date 	: 
		========================================================================================================================================
    */
    public function create()
    {
        $role = new RbacRole;

        # ini untuk mencari menu yang menjadi parent
        $menu = RbacMenu::where('is_deleted', 0)->where('parent', 0)->get();

        foreach ($menu as $key => $val) {
        	#untuk mencari sub parent menu
        	$sub_menu = RbacMenu::where('is_deleted', 0)->where('parent', $val->id)->get();

            if($val->link != '#') {
                $permission  = RbacPermission::where('id_menu', $val->id)->get();
                $val->permission = $permission;
            } else {
                foreach ($sub_menu as $a => $obj) {
                    # untuk mencari permission dari setiap sub parent
                    $permission  = RbacPermission::where('id_menu', $obj->id)->get();
                    $obj->permission = $permission;
                }
            }
        	
        	$val->sub_menu = $sub_menu;
        }

        $permission_tanpa_menu  = RbacPermission::where('id_menu', '=', 0)->get();
        return view('role.create')->with(compact('role', 'menu', 'permission_tanpa_menu'));
    }




    /*
    	========================================================================================================================================
    	For     : store data role
    	Author 	: 
		Date 	: 
		========================================================================================================================================
    */
    public function store(Request $request)
    {
        $role = new RbacRole;
        $role->fill($request->except('_token'));


        $permission_role = $request->permission_role;
       /* $array_permission = array();
        foreach ($permission_role as $obj) {
            foreach ($obj as $val) {
            	$array_permission[] = $val;
            }
        }*/

        $validator = $role->validate();
        if($validator->fails()){
            return view('role.create')->with(compact('role'))->withErrors($validator);
        }else{
            $role->save_now(1, $permission_role);
            session()->flash('success', 'Berhasil menyimpan data!');
            return redirect('role');
        }
    }

    /*
    	========================================================================================================================================
    	For     : Tidak digunakan
    	Author 	: 
		Date 	: 
		========================================================================================================================================
    */
    public function show($id)
    {

    }



    /*
    	========================================================================================================================================
    	For     : call edit role function
    	Author 	: 
		Date 	: 
		========================================================================================================================================
    */
    public function edit($id)
    {
        $role = RbacRole::find($id);


        # ini untuk mencari menu yang menjadi parent
        $menu = RbacMenu::where('is_deleted', 0)->where('parent', 0)->get();
       /* print_r($menu);
        exit();*/

        foreach ($menu as $key => $val) {
        	#untuk mencari sub parent menu
        	$sub_menu = RbacMenu::where('is_deleted', 0)->where('parent', $val->id)->get();

            if($val->link != '#') {
                $permission  = RbacPermission::where('id_menu', $val->id)->get();
                foreach ($permission as $b => $p) {
                    # untuk mengecek apakah ada relasi ke permission
                    $var_cek = RbacRolePermission::where('id_role', $id)->where('id_permission', $p->id)->first();

                    if(!empty($var_cek)) {
                        $p->ada_permission = 1;
                    } else {
                        $p->ada_permission = 0;
                    }
                }
                $val->permission = $permission;

            } else {
            	foreach ($sub_menu as $a => $obj) {
            		# untuk mencari permission dari setiap sub parent
            		$permission  = RbacPermission::where('id_menu', $obj->id)->get();

            		foreach ($permission as $b => $p) {
            			# untuk mengecek apakah ada relasi ke permission
            			$var_cek = RbacRolePermission::where('id_role', $id)->where('id_permission', $p->id)->first();

            			if(!empty($var_cek)) {
            				$p->ada_permission = 1;
            			} else {
            				$p->ada_permission = 0;
            			}
            		}
            		
            		$obj->permission = $permission;
            	}
            }
        	$val->sub_menu = $sub_menu;
        }

        $permission_tanpa_menu  = RbacPermission::where('id_menu', '=', 0)->get();
        foreach ($permission_tanpa_menu as $b => $p) {
            # untuk mengecek apakah ada relasi ke permission
            $var_cek = RbacRolePermission::where('id_role', $id)->where('id_permission', $p->id)->first();

            if(!empty($var_cek)) {
                $p->ada_permission = 1;
            } else {
                $p->ada_permission = 0;
            }
        }

        return view('role.edit')->with(compact('role', 'menu', 'permission_tanpa_menu'));
    }



    /*
    	========================================================================================================================================
    	For     : update data role
    	Author 	: 
		Date 	: 
		========================================================================================================================================
    */
    public function update(Request $request, $id)
    {
        $role = RbacRole::find($id);
        $role->fill($request->except('_token'));

        $permission_role = $request->permission_role;
        /*$array_permission = array();
        foreach ($permission_role as $obj) {
            foreach ($obj as $val) {
            	$array_permission[] = $val;
            }
        }*/

        $validator = $role->validate();
        if($validator->fails()){
            return view('role.edit')->with(compact('role'))->withErrors($validator);
        }else{
            $role->save_now(2, $permission_role);
            session()->flash('success', 'Berhasil memperbaharui data!');
            return redirect('role')->with('message', 'Berhasil memperbaharui data!');
        }
    }



    /*
    	========================================================================================================================================
    	For     : delete data role
    	Author 	: 
		Date 	: 
		========================================================================================================================================
    */
    public function destroy($id)
    {
        $role = RbacRole::find($id);
        $role->is_deleted = 1;

        if($role->save()){
            echo 1;
        }else{
            echo 0;
        }
      
    }
}
