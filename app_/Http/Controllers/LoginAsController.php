<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;
use App\RbacMenu;
use App\RbacPermission;
use App\RbacRole;
use App\RbacRolePermission;
use App\RbacUserRole;
use App\RbacUserApotek;
use App\MasterApotek;
use App\MasterGroupApotek;
use App\MasterTahun;
use Auth;
use Route;
use Session;
class LoginAsController extends Controller
{
    /*
        ========================================================================================================================================
        For     : Tampilan index halaman login as
        Author  : Citra
        Date    : 17/4/2018
        ========================================================================================================================================
    */
    public function index()
    {

   
        $id_user = session('id');
        $data['user'] = User::where('is_deleted',0)->get();

        return view('auth.loginas')->with($data, $id_user);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /*
        ========================================================================================================================================
        For     : Update session app
        Author  : Citra
        Date    : 17/4/2018
        ========================================================================================================================================
    */
    public function login(Request $request)
    {
        $idUser = $request->user;

        $user = User::find($idUser);
        
        if(!is_null($user)){
            $actions = array();
            $menus = array();
            $role_list = array();

            $user_language = Language::all();
            session(['id_sso' => $user->id_sso]);
            session(['id_user' => $user->id]);
            session(['user' => $user]);
            session(['id_jenis_user' => $user->jenis]);
            session(['identifier' => $user->identifier]);
            session(['nama_user' => $user->nama_user]);
            session(['user_language' => $user_language]);


        # --- USER ROLE --- #
            $user_roles = UserRole::leftJoin('rbac_roles','rbac_roles.id', '=', 'rbac_user_role.id_role')
                            ->where("id_user", $user->id)
                            ->orderBy('rbac_roles.is_superadmin', 'DESC')
                            ->get();

           /* dd($user_roles);*/

            session(['super_admin' => 0]);
            foreach ($user_roles as $user_role) {
                if($user_role->is_superadmin==1){
                    session(['super_admin' => 1]);
                }

                array_push($role_list, $user_role->nama);
            }
        # --- end USER ROLE --- #

        # --- MENU --- #
            if(!empty($user_roles)){
                session(['active_role'=>$user_roles[0]->nama]);
                session(['active_role_id'=>$user_roles[0]->id]);
                //session(['active_menu'=>$user_roles[0]->menu]);

                $role_permissions = RolePermission::where("id_role", $user_roles[0]->id)->get();
                foreach ($role_permissions as $role_permission) {
                    $permission = Permission::find($role_permission->id_permission);
                    $actions[] = $permission->nama;
                    /*if (!in_array($permission->id_menu, $menus)) {
                    } else {*/
                        $menus[] = $permission->id_menu;
                    //}
                }

                $menu = Menu::where('is_deleted', 0)->whereIn('id', $menus)->orderBy('weight')->get();
            
                $parents = array();
                foreach ($menu as $key => $val) {
                    if($val->parent == 0) {
                        $data_parent = Menu::find($val->id);
                        $parents[] = $data_parent->id;
                    } else {
                        $data_parent = Menu::find($val->parent);
                        $parents[] = $data_parent->id;
                    }
                   
                }

                $parent_menu = Menu::where('is_deleted', 0)->whereIn('id', $parents)->orderBy('weight')->get();

                foreach ($parent_menu as $key => $obj) {
                    $sub_menu = array(); 
                    if ($obj->link == "#") {
                        foreach ($menu as $key => $val) {
                            if($val->parent == $obj->id) {
                                $sub_menu[] = $val;
                            }
                        }
                        $obj->link == "#";
                        $obj->submenu = $sub_menu;
                        $obj->ada_sub = 1;
                        
                    } else {
                        $obj->submenu = "";
                        $obj->ada_sub = 0;
                    }
                }
            }
        # --- end MENU --- #

        # --- LANG --- #
            if(!empty($user_language)){
                session(['active_language'=>$user_language[0]->nama_panjang]);
                session(['active_language_frontend'=>$user_language[0]->nama_frontend]);
                session(['active_language_image'=>$user_language[0]->image]);
                session(['active_language_id'=>$user_language[0]->id]);
                session(['active_language_locale' => $user_language[0]->nama_singkat]);
            }
        # --- end LANG --- #


            session(['actions' => $actions]);
            session(['menu' => $parent_menu]);
            session(['role_list' => $role_list]);

            if(!is_null($user->id_sso)){
                /*$userunud = UserImissu::find($user->id_sso);
                session(['photo' => 'https://imissu.unud.ac.id/upload/photos/'.$userunud->photo ]);*/
                session(['photo' => '' ]); 
            } else {
                session(['photo' => '' ]); 
            }

            session(['logout' => url('admin_login')]);

            # --- FLAG LOGIN AS --- #
            session(['loginas' => 1]);

            return redirect('/dashboard');

        } else {

        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
