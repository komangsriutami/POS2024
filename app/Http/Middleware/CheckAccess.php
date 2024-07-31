<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Route;
use Closure;
use Auth;
use App\User;
use App\MasterPasien;
use App\RbacMenu;
use App\RbacPermission;
use App\RbacRole;
use App\RbacRolePermission;
use App\RbacUserRole;
use App\RbacUserApotek;
use App\MasterApotek;
use App\MasterGroupApotek;
use App\MasterTahun;
use Cache;
use DB;
use Session;

use App\Traits\DynamicConnectionTrait;

class CheckAccess
{
    use DynamicConnectionTrait;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
       // print_r(session('actions'));exit();
        $action =  Route::getCurrentRoute()->getAction();
        $action_name = $action['as'];
        if(session()->has('actions')){
            //if(Auth::user()->id == 1) {
                /*if(in_array($action_name, session('actions'))){
                    return $next($request);
                }else{
                    return $next($request);
                    //return redirect()->intended('page_not_authorized');
                }*/

                return $next($request);
            /*} else {
                return redirect()->intended('page_not_authorized');
            }*/
        }else{
            if(session()->has('user')){
                $user = session('user');
                Auth::login($user);
                DB::connection($this->getConnectionDefault())->table('tb_log_login')->insert(['type' => 1, 'server_name'=> env('SERVER_ID'), 'server_ip' => env('SERVER_IP'), 'client_ip' => request()->ip(), 'id_user' => $user->id, 'id_apotek' => session('id_apotek_active'), 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'), 'from_cache' => 1]);

                /*if ($user->is_admin == 1) {
                    $role_list = array();
                    $actions = array();
                    $user_roles = RbacUserRole::leftJoin('rbac_roles', 'rbac_roles.id', '=', 'rbac_user_role.id_role')
                        ->where("id_user", Auth::id())
                        ->orderBy('rbac_roles.is_superadmin', 'DESC')
                        ->get();
                    session(['user' => $user]);
                    Cache::forget('sessionUser_');
                    Cache::put('sessionUser_', $user, now()->addDay());
                    Cache::forget('sessionApotek_');
                    Cache::put('sessionApotek_', 1, now()->addDay());

                    DB::connection($this->getConnectionDefault())->table('tb_log_login')->insert(['server_name'=> env('SERVER_ID'), 'server_ip' => env('SERVER_IP'), 'client_ip' => request()->ip(), 'id_user' => $user->id, 'id_apotek' => 1, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'), 'from_cache' => 1]);

                    session(['super_admin' => 0]);
                    foreach ($user_roles as $user_role) {
                        if ($user_role->is_superadmin == 1) {
                            session(['super_admin' => 1]);
                        }

                        array_push($role_list, $user_role->nama);
                    }

                    if (!empty($user_roles)) {
                        session(['nama_role_active' => $user_roles[0]->nama]);
                        session(['id_role_active' => $user_roles[0]->id]);
                        $menus = array();

                        $role_permissions = RbacRolePermission::on($this->getConnectionDefault())->where("id_role", $user_roles[0]->id)->get();
                        foreach ($role_permissions as $role_permission) {
                            $permission = RbacPermission::on($this->getConnectionDefault())->find($role_permission->id_permission);
                            $actions[] = $permission->nama;

                            $menus[] = $permission->id_menu;
                        }

                        $menu = RbacMenu::on($this->getConnectionDefault())->where('is_deleted', 0)->whereIn('id', $menus)->orderBy('weight')->get();

                        $parents = array();
                        foreach ($menu as $key => $val) {
                            if ($val->parent == 0) {
                                $data_parent = RbacMenu::on($this->getConnectionDefault())->find($val->id);
                                $parents[] = $data_parent->id;
                            } else {
                                $data_parent = RbacMenu::on($this->getConnectionDefault())->find($val->parent);
                                $parents[] = $data_parent->id;
                            }
                        }

                        $parent_menu = RbacMenu::on($this->getConnectionDefault())->where('is_deleted', 0)->whereIn('id', $parents)->orderBy('weight')->get();

                        foreach ($parent_menu as $key => $obj) {
                            $sub_menu = array();
                            if ($obj->link == "#") {
                                foreach ($menu as $key => $val) {
                                    $sub_sub_menu = array();
                                    if ($val->parent == $obj->id) {
                                        if ($val->link == "#") {
                                            $val->link == "#";
                                            $sub_sub_menu = RbacMenu::on($this->getConnectionDefault())->where('is_deleted', 0)->where('sub_parent', $val->id)->orderBy('weight')->get();
                                            $val->subsubmenu = $sub_sub_menu;
                                            $val->ada_sub_sub = 1;
                                            $sub_menu[] = $val;
                                        } else {
                                            $obj->subsubmenu = "";
                                            $obj->ada_sub_sub = 0;
                                            $sub_menu[] = $val;
                                        }
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

                    $apotek_group = MasterGroupApotek::on($this->getConnectionDefault())->find(Auth::user()->id_group_apotek);
                    if(session('id_role_active') == 1) {
                        $apoteks = MasterApotek::on($this->getConnectionDefault())->where('is_deleted', 0)->where('id_group_apotek', Auth::user()->id_group_apotek)->get();
                    } else {
                        $cek_apotek_akses = RbacUserApotek::on($this->getConnectionDefault())->select('id_apotek')->where('id_user', $user->id)->get();
                        $apoteks = MasterApotek::on($this->getConnectionDefault())->where('is_deleted', 0)->where('id_group_apotek', Auth::user()->id_group_apotek)->whereIn('id', $cek_apotek_akses)->get();
                    }
                    $tahuns = MasterTahun::orderby('id', 'DESC')->get();
                    $apotek = MasterApotek::on($this->getConnectionDefault())->where('is_deleted', 0)->where('id_group_apotek', Auth::user()->id_group_apotek)->first();
                    session(['id_tahun_active' => date('Y')]);
                    session(['connection_active' => $this->getConnectionDefault()]);
                    session(['nama_apotek_singkat_active' => strtolower($apotek->nama_singkat)]);
                    session(['nama_apotek_panjang_active' => $apotek->nama_panjang]);
                    session(['nama_apotek_active' => $apotek->nama_singkat]);
                    session(['kode_apotek_active' => $apotek->kode_apotek]);
                    session(['id_apotek_active' => $apotek->id]);

                    $_SESSION["isLogedIn"] = 1;
                    session(['actions' => $actions]);
                    session(['menu' => $parent_menu]);
                    session(['apoteks' => $apoteks]);
                    session(['tahuns' => $tahuns]);
                    session(['apotek_group' => $apotek_group]);
                    session(['isLogedIn' => 1]);
                    session(['role_list' => $role_list]);
                    session(['user_roles' => $user_roles]);
                    session(['is_status_login' => '1']);
                    session(['status_login' => 'PT']);
                    session(['id_printer_active' => 1]);

                    return redirect()->intended(route('home'));
                } else {
                    $id_apotek = Cache::get('sessionApotek_');
                    $apotek = MasterApotek::on($this->getConnectionDefault())->where('id', $id_apotek)->first();
                    if (!empty($apotek)) {
                    } else {
                        return redirect()->intended('login_system')->withErrors([
                            'kode_apotek' => 'Kode apotek yang anda masukan tidak tesuai, silakan periksa dan login kembali.',
                        ]);
                    }

                    $cekuser = User::on($this->getConnectionDefault())->where('username', '=', $user->username)->count();
                    $cek_apotek_akses = RbacUserApotek::on($this->getConnectionDefault())->where('id_user', $user->id)->where('id_apotek', $apotek->id)->first();

                    if ($cekuser >= 1) {
                        if (!empty($cek_apotek_akses)) {
                            if (Auth::guard()->attempt(['username' => $request->username, 'password' => $request->password], $request->remember)) {
                                $role_list = array();
                                $actions = array();
                                $user_roles = RbacUserRole::leftJoin('rbac_roles', 'rbac_roles.id', '=', 'rbac_user_role.id_role')
                                    ->where("id_user", Auth::id())
                                    ->orderBy('rbac_roles.is_superadmin', 'DESC')
                                    ->get();
                                session(['user' => $user]);
                                Cache::forget('sessionUser_');
                                Cache::put('sessionUser_', $user, now()->addDay());
                                Cache::forget('sessionApotek_');
                                Cache::put('sessionApotek_', $apotek->id, now()->addDay());

                                DB::connection($this->getConnectionDefault())->table('tb_log_login')->insert(['server_name'=> env('SERVER_ID'), 'server_ip' => env('SERVER_IP'), 'client_ip' => request()->ip(), 'id_user' => $user->id, 'id_apotek' => $apotek->id, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'), 'from_cache' => 1]);

                                session(['super_admin' => 0]);
                                foreach ($user_roles as $user_role) {
                                    if ($user_role->is_superadmin == 1) {
                                        session(['super_admin' => 1]);
                                    }

                                    array_push($role_list, $user_role->nama);
                                }

                                if (!empty($user_roles)) {
                                    session(['nama_role_active' => $user_roles[0]->nama]);
                                    session(['id_role_active' => $user_roles[0]->id]);
                                    $menus = array();

                                    $role_permissions = RbacRolePermission::on($this->getConnectionDefault())->where("id_role", $user_roles[0]->id)->get();
                                    foreach ($role_permissions as $role_permission) {
                                        $permission = RbacPermission::on($this->getConnectionDefault())->find($role_permission->id_permission);
                                        $actions[] = $permission->nama;

                                        $menus[] = $permission->id_menu;
                                    }

                                    $menu = RbacMenu::on($this->getConnectionDefault())->where('is_deleted', 0)->whereIn('id', $menus)->orderBy('weight')->get();

                                    $parents = array();
                                    foreach ($menu as $key => $val) {
                                        if ($val->parent == 0) {
                                            $data_parent = RbacMenu::on($this->getConnectionDefault())->find($val->id);
                                            $parents[] = $data_parent->id;
                                        } else {
                                            $data_parent = RbacMenu::on($this->getConnectionDefault())->find($val->parent);
                                            $parents[] = $data_parent->id;
                                        }
                                    }

                                    $parent_menu = RbacMenu::on($this->getConnectionDefault())->where('is_deleted', 0)->whereIn('id', $parents)->orderBy('weight')->get();

                                    foreach ($parent_menu as $key => $obj) {
                                        $sub_menu = array();
                                        if ($obj->link == "#") {
                                            foreach ($menu as $key => $val) {
                                                $sub_sub_menu = array();
                                                if ($val->parent == $obj->id) {
                                                    if ($val->link == "#") {
                                                        $val->link == "#";
                                                        $sub_sub_menu = RbacMenu::on($this->getConnectionDefault())->where('is_deleted', 0)->where('sub_parent', $val->id)->orderBy('weight')->get();
                                                        $val->subsubmenu = $sub_sub_menu;
                                                        $val->ada_sub_sub = 1;
                                                        $sub_menu[] = $val;
                                                    } else {
                                                        $obj->subsubmenu = "";
                                                        $obj->ada_sub_sub = 0;
                                                        $sub_menu[] = $val;
                                                    }
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
                                $apotek_group = MasterGroupApotek::on($this->getConnectionDefault())->find($apotek->id_group_apotek);

                                session(['nama_apotek_singkat_active' => strtolower($apotek->nama_singkat)]);
                                session(['nama_apotek_panjang_active' => $apotek->nama_panjang]);
                                session(['nama_apotek_active' => $apotek->nama_singkat]);
                                session(['kode_apotek_active' => $apotek->kode_apotek]);
                                session(['id_apotek_active' => $apotek->id]);

                                $apoteks = MasterApotek::on($this->getConnectionDefault())->where('is_deleted', 0)->where('id_group_apotek', Auth::user()->id_group_apotek)->get();
                                $tahuns = MasterTahun::orderby('id', 'DESC')->get();
                                session(['id_tahun_active' => date('Y')]);

                                $_SESSION["isLogedIn"] = 1;
                                session(['actions' => $actions]);
                                session(['menu' => $parent_menu]);
                                session(['apoteks' => $apoteks]);
                                session(['tahuns' => $tahuns]);
                                session(['apotek_group' => $apotek_group]);
                                session(['isLogedIn' => 1]);
                                session(['role_list' => $role_list]);
                                session(['user_roles' => $user_roles]);
                                session(['is_status_login' => '2']);
                                session(['status_login' => 'Outlet']);
                                session(['id_printer_active' => $apotek->id_printer]);

                                return redirect()->intended(route('home'));
                            } else {
                                return redirect()->intended('login_system')->withErrors([
                                    'password' => 'Password yang anda masukan tidak tesuai, silakan periksa dan login kembali.',
                                ]);
                            }
                        } else {
                            return redirect()->intended('login_system')->withErrors([
                                'kode_apotek' => 'Anda tidak terdaftar sebagai staf diapotek ini, silakan hubungi administrator atau kepala outlet anda.',
                            ]);
                        }
                    } else {
                        return redirect()->intended('login_system')->withErrors([
                            'username' => 'Username <strong>' . $request->username . '</strong> tidak terdaftar, silakan periksa dan login kembali.',
                        ]);
                    }
                }*/
                
            } else {
                Session::flush();
                Session::forget('user');
                Session::forget('super_admin');
                Session::forget('nama_role_active');
                Session::forget('id_role_active');
                Session::forget('actions');
                Session::forget('apoteks');
                Session::forget('isLogedIn');
                Session::forget('role_list');
                Session::forget('user_roles');
                Session::forget('nama_apotek_panjang_active');
                Session::forget('nama_apotek_active');
                Session::forget('id_apotek_active');
                Cache::forget('sessionUser_');
                Cache::forget('sessionApotek_');
                Auth::logout();
                session()->flash('error', 'Silakan Login terlebih dahulu sebelum anda mengakses halaman ini!');
                return redirect()->intended('/');
            }
        }

        return $next($request);
    }
}
