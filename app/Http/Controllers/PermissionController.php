<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\RbacPermission;
use App\RbacMenu;
use Datatables;
use DB;
use Route;
use URL;
use App\Traits\DynamicConnectionTrait;

class PermissionController extends Controller
{
    use DynamicConnectionTrait;
    /*
        =================================================================================================================
        For     : Tampilan index Permission
        Author  : 
        Date    : 
        =================================================================================================================
    */
    public function index()
    {
        $app = app();
        $routes = $app->routes->getRoutes();

        $menus = RbacMenu::on($this->getConnectionName())->where('is_deleted', 0)->where('link', '!=', '#')->pluck('nama_panjang','id');
        $menus->prepend('-- Pilih Menu --','');

        return view('permission.index')->with(compact('menus', 'routes'));
    }

    /*
        =================================================================================================================
        For     : List Permission yang diload pada halaman index permission
        Author  : 
        Date    : 
        =================================================================================================================
    */
    public function list_permission(Request $request)
    {
        $data = RbacPermission::select([
                'rbac_permissions.id',
                'rbac_permissions.id_menu', 
                'rbac_permissions.nama', 
                'rbac_permissions.uri', 
                'rbac_permissions.type',
                'rbac_permissions.method',
                'rbac_permissions.group', 
                'rbac_permissions.flag_core',
                'a.nama_singkatan as menu'])
        ->leftJoin('rbac_menu as a', 'a.id', '=', 'rbac_permissions.id_menu')
        ->where(function($query) use($request){
            $query->where('rbac_permissions.is_deleted','=','0');
        })
        ->orderByRaw('id asc');

        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request){
            $query->where(function($query) use($request){
                $query->orwhere('uri','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('nama','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('group','LIKE','%'.$request->get('search')['value'].'%');
            });
        })
        ->editcolumn('menu', function($data) {
            if(empty($data->menu)) {
                $menu = 'Tidak berelasi dengan menu';
            } else {
                $menu = $data->menu;
            }

            return '('.$data->id_menu.')- '.$menu;
        })
        ->addcolumn('action',
            '<a href=" {{url("/permission/".$id."/edit/")}} " title="Ubah" data-toggle="modal" data-id="{!! $id !!}"><span class="label label-primary" data-toggle="tooltip" data-placement="top" title="'.trans('label.tooltip_edit_data').'"><i class="fa fa-edit"></i></span> </a>
            <span class="label label-danger label-delete" onClick="delete_permission({!! $id !!})" data-toggle="tooltip" data-placement="top" title="'.trans('label.tooltip_delete_data').'"><i class="fa fa-times"></i></span>
            ')
        ->addIndexColumn()
        ->rawColumns(['menu', 'DT_RowIndex', 'action'])
        ->make(true);  
    }  



    /*
        =================================================================================================================
        For     : call create  function | Tidak digunakan
        Author  : 
        Date    : 
        =================================================================================================================
    */
    public function create()
    {
       
    }




    /*
        =================================================================================================================
        For     : store data permission | Tidak digunakan
        Author  : 
        Date    : 
        =================================================================================================================
    */
    public function store(Request $request)
    {
       
    }

    /*
        =================================================================================================================
        For     : Tidak digunakan 
        Author  : 
        Date    : 
        =================================================================================================================
    */
    public function show($id)
    {

    }



    /*
        =================================================================================================================
        For     : call edit permission function | Tidak digunakan
        Author  : 
        Date    : 
        =================================================================================================================
    */
    public function edit($id)
    {
        
    }



    /*
        =================================================================================================================
        For     : update data permission
        Author  : 
        Date    : 
        =================================================================================================================
    */
    public function update(Request $request, $id)
    {
        
    }



    /*
        =================================================================================================================
        For     : delete data permission
        Author  : 
        Date    : 
        =================================================================================================================
    */
    public function destroy($id)
    {
        $permission = RbacPermission::on($this->getConnectionName())->find($id);
        $permission->is_deleted = 1;

        if($permission->save()){
            echo 1;
        }else{
            echo 0;
        }
      
    }

    /*
        =================================================================================================================
        For     : reload data permission
        Author  : 
        Date    : 
        =================================================================================================================
    */
    public function reload_permission() {
        $app = app();
        $routes = $app->routes->getRoutes();

        $permissions = RbacPermission::all();
        /*if(!empty($permissions)) {
            RbacPermission::truncate();
        }*/
        
        $array_id_permission = array();

        $i = 0;
        foreach ($routes as $route) {
            $i++;
            $route_name = $route->getName();
            $cek = RbacPermission::on($this->getConnectionName())->where('nama', $route_name)->first();

            if(empty($cek)) {
                $permission = new RbacPermission;
                $permission->setDynamicConnection();
            } else {
                $permission = RbacPermission::on($this->getConnectionName())->find($cek->id);
            }

            
            $permission->nama = $route_name;
            $permission->uri  = $route->uri;
            $permission->type = $route->getPrefix();
            $permission->method = $route->getActionMethod();
            $permission->flag_core = 0;

            if($permission->id_menu == "") {
                if($route_name != "") {
                    $split_id = explode('.' , $route->getName());
                    $permission->group = $split_id[0];
                    $menu = RbacMenu::on($this->getConnectionName())->where('route_group', $split_id[0])->first();
                    
                    if(!empty($menu)) {
                        $permission->id_menu = $menu->id;
                    } else {
                        $permission->id_menu = 0;
                    }
                } else {
                    $permission->group = "";
                    $permission->id_menu = 0;
                }
            }

            $permission->save();
            $array_id_permission[] = $permission->id;
        }

       /* if(!empty($array_id_permission)){
            DB::connection($this->getConnection())->statement("DELETE FROM rbac_permissions
                            WHERE id_menu=".$this->id." AND 
                                    id NOT IN(".implode(',', $array_id_permission).")");
        }else{
            DB::connection($this->getConnection())->statement("DELETE FROM rbac_permissions 
                            WHERE id_menu=".$this->id);
        }*/

        if($i > 0){
            session()->flash('warning', 'Tidak ada data permission yang direload!');
            return redirect('permission')->with('message', 'Tidak ada data permission yang direload!');
        }else{
            session()->flash('success', 'Data permission telah berhasil direload!');
            return redirect('permission')->with('message', 'Data permission telah berhasil direload!');
        }

    }
}
