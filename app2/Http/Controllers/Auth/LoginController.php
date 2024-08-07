<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

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
use App\MasterKewarganegaraan;
use App\MasterApoteker;
use App\MasterDokter;

use Auth;
use Route;
use Session;
use Cache;
use App\Traits\DynamicConnectionTrait;
use DB;

class LoginController extends Controller
{
    use DynamicConnectionTrait;
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function login_system()
    {
       return view('frontend.login');
    }

    public function login_check(Request $request)
    {
        if(!isset($request->kode_apotek)){
            $user = User::on($this->getConnectionDefault())->where('username', '=', $request->username)->first();
            $cekuser = User::on($this->getConnectionDefault())->where('username', '=', $request->username)->count();
            
            if ($cekuser >= 1) {
                if ($user->is_admin == 1) {
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
                        Cache::put('sessionApotek_', 1, now()->addDay());

                        DB::connection($this->getConnectionDefault())->table('tb_log_login')->insert(['type' => 1, 'server_name'=> env('SERVER_ID'), 'server_ip' => env('SERVER_IP'), 'client_ip' => request()->ip(), 'id_user' => $user->id, 'id_apotek' => 1, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'), 'from_cache' => 0]);

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
                        return redirect()->back()->withInput($request->only('username', 'remember'))->withErrors([
                            'password' => 'Password yang anda masukan tidak tesuai, silakan periksa dan login kembali.',
                        ]);
                    }
                } else {
                    return redirect()->intended('login_admin')->withErrors([
                        'username' => 'Username ' . $request->username . ', tidak terdaftar pada sebagai staff PT, silakan periksa dan login kembali.',
                    ]);
                }
            } else {
                return redirect()->intended('login_admin')->withErrors([
                    'username' => 'Username ' . $request->username . ', tidak terdaftar, silakan periksa dan login kembali.',
                ]);
            }
        } else {
            $apotek = MasterApotek::on($this->getConnectionDefault())->where('kode_apotek', $request->kode_apotek)->first();
            $from_cache = 0;
            if (!empty($apotek)) {
                if(isset($request->username)) {
                    $user = User::on($this->getConnectionDefault())->where('username', '=', $request->username)->first();
                } else {
                    if(!is_null(session('user'))) {
                        $user = session('user');
                        $from_cache = 1;
                    } else {
                        return redirect()->back()->withInput($request->only('username', 'remember', 'kode_apotek'))->withErrors([
                            'username' => 'Silakan login kembali.',
                        ]);
                    }
                }
            } else {
                return redirect()->back()->withInput($request->only('username', 'remember', 'kode_apotek'))->withErrors([
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

                        DB::connection($this->getConnectionDefault())->table('tb_log_login')->insert(['type' => 1, 'server_name'=> env('SERVER_ID'), 'server_ip' => env('SERVER_IP'), 'client_ip' => request()->ip(), 'id_user' => $user->id, 'id_apotek' => $apotek->id, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'), 'from_cache' => $from_cache]);

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
                        return redirect()->back()->withInput($request->only('username', 'remember', 'kode_apotek'))->withErrors([
                            'password' => 'Password yang anda masukan tidak tesuai, silakan periksa dan login kembali.',
                        ]);
                    }
                } else {
                    return redirect()->back()->withInput($request->only('username', 'remember', 'kode_apotek'))->withErrors([
                        'kode_apotek' => 'Anda tidak terdaftar sebagai staf diapotek ini, silakan hubungi administrator atau kepala outlet anda.',
                    ]);
                }
            } else {
                return redirect()->intended('login_outlet')->withErrors([
                    'username' => 'Username <strong>' . $request->username . '</strong> tidak terdaftar, silakan periksa dan login kembali.',
                ]);
            }
        }
    }

    public function login_admin()
    {
        return view('frontend.login_pt');
    }

    public function login_outlet()
    {
        return view('frontend.login_outlet');
    }

    public function login_outlet_check(Request $request)
    {
        $apotek = MasterApotek::on($this->getConnectionDefault())->where('kode_apotek', $request->kode_apotek)->first();
        if (!empty($apotek)) {
            $user = User::on($this->getConnectionDefault())->where('username', '=', $request->username)->first();
            $cekuser = User::on($this->getConnectionDefault())->where('username', '=', $request->username)->count();
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

                        return redirect()->intended(route('home'));
                    } else {
                        return redirect()->back()->withInput($request->only('username', 'remember', 'kode_apotek'))->withErrors([
                            'password' => 'Password yang anda masukan tidak tesuai, silakan periksa dan login kembali.',
                        ]);
                    }
                } else {
                    return redirect()->back()->withInput($request->only('username', 'remember', 'kode_apotek'))->withErrors([
                        'kode_apotek' => 'Anda tidak terdaftar sebagai staf diapotek ini, silakan hubungi administrator atau kepala outlet anda.',
                    ]);
                }
            } else {
                return redirect()->intended('login_outlet')->withErrors([
                    'username' => 'Username <strong>' . $request->username . '</strong> tidak terdaftar, silakan periksa dan login kembali.',
                ]);
            }
        } else {
            return redirect()->back()->withInput($request->only('username', 'remember', 'kode_apotek'))->withErrors([
                'kode_apotek' => 'Kode apotek yang anda masukan tidak tesuai, silakan periksa dan login kembali.',
            ]);
        }
    }

    public function login_admin_check(Request $request)
    {
        $user = User::on($this->getConnectionDefault())->where('username', '=', $request->username)->first();
        $cekuser = User::on($this->getConnectionDefault())->where('username', '=', $request->username)->count();

        if ($cekuser >= 1) {
            if ($user->is_admin == 1) {
                if (Auth::guard()->attempt(['username' => $request->username, 'password' => $request->password], $request->remember)) {
                    $role_list = array();
                    $actions = array();
                    $user_roles = RbacUserRole::leftJoin('rbac_roles', 'rbac_roles.id', '=', 'rbac_user_role.id_role')
                        ->where("id_user", Auth::id())
                        ->orderBy('rbac_roles.is_superadmin', 'DESC')
                        ->get();

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
                    session(['is_status_login' => '1']);
                    session(['status_login' => 'PT']);

                    return redirect()->intended(route('home'));
                } else {
                    return redirect()->back()->withInput($request->only('username', 'remember'))->withErrors([
                        'password' => 'Password yang anda masukan tidak tesuai, silakan periksa dan login kembali.',
                    ]);
                }
            } else {
                return redirect()->intended('login_admin')->withErrors([
                    'username' => 'Username ' . $request->username . ', tidak terdaftar pada sebagai staff PT, silakan periksa dan login kembali.',
                ]);
            }
        } else {
            return redirect()->intended('login_admin')->withErrors([
                'username' => 'Username ' . $request->username . ', tidak terdaftar, silakan periksa dan login kembali.',
            ]);
        }
    }

    public function logout(Request $request)
    {
        $user = session('user');
        DB::connection($this->getConnectionDefault())->table('tb_log_login')->insert(['type' => 2, 'server_name'=> env('SERVER_ID'), 'server_ip' => env('SERVER_IP'), 'client_ip' => request()->ip(), 'id_user' => $user->id, 'id_apotek' => session('id_apotek_active'), 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'), 'from_cache' => 0]);

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
        return redirect()->intended('/');
    }

    public function logout_pasien(Request $request) {
        Session::flush();
        Session::forget('id');
        Session::forget('id_kewarganegaraan');
        Session::forget('id_jenis_kelamin');
        Session::forget('id_golongan_darah');
        Session::forget('username');
        Session::forget('password');
        Session::forget('nama');
        Session::forget('tempat_lahir');
        Session::forget('tgl_lahir');
        Session::forget('telepon');
        Session::forget('alamat');
        Session::forget('email');
        Session::forget('is_pernah_berobat');
        Session::forget('is_bpjs');
        Session::forget('no_bpjs');
        Session::forget('id_reference');
        Auth::logout();
        return redirect()->intended('/homepage');
    }

    public function login_pasien()
    {
        return view('frontend.login_pasien');
    }

    public function login_pasien_check(Request $request)
    {
        $user = MasterPasien::on($this->getConnectionDefault())->where('email','=',$request->email)->first();
        $cekuser = MasterPasien::on($this->getConnectionDefault())->where('email','=',$request->email)->count();
        if($cekuser>=1){
            if (Auth::guard("pasien")->attempt(['email' => $request->email, 'password' => $request->password])) {
                $role_list = array();
                $actions = array();
                $user_roles = RbacRole::on($this->getConnectionDefault())->whereIn('id',[10])->get();
                session(['super_admin' => 0]);
                foreach ($user_roles as $user_role) {
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
                // dd(Auth::guard('pasien')->user()->nama);
                // return;

                $tahuns = MasterTahun::orderby('id', 'DESC')->get();
                session(['id_tahun_active' => date('Y')]);

                $_SESSION["isLogedIn"] = 1;
                session(['actions' => $actions]);
                session(['menu' => $parent_menu]);
                session(['tahuns' => $tahuns]);
                session(['isLogedIn' => 1]);
                session(['role_list' => $role_list]);
                session(['user_roles' => $user_roles]);
                session(['is_status_login' => '1']);
                session(['id' => $user['id']]);

                $kewarganegaraan = MasterKewarganegaraan::on($this->getConnectionDefault())->select("kewarganegaraan")->where("id", $user['id_kewarganegaraan'])->first();
                if (!is_null($kewarganegaraan)) {
                    session(['kewarganegaraan' => $kewarganegaraan["kewarganegaraan"]]);
                }
                session(['id_kewarganegaraan' => $user["id_kewarganegaraan"]]);
                session(['id_jenis_kelamin' => $user['id_jenis_kelamin']]);
                session(['id_golongan_darah' => $user['id_golongan_darah']]);
                session(['nama' => $user['nama']]);
                session(['tempat_lahir' => $user['tempat_lahir']]);
                session(['tgl_lahir' => $user['tgl_lahir']]);
                session(['telepon' => $user['telepon']]);
                session(['email' => $user['email']]);
                session(['alamat' => $user['alamat']]);
                session(['is_pernah_berobat' => $user['is_pernah_berobat']]);
                session(['is_bpjs' => $user['is_bpjs']]);
                session(['no_bpjs' => $user['no_bpjs']]);
                session(['id_reference' => $user['id_reference']]);

                return redirect()->intended('home_pasien/isi_data_diri');
            } else {
                return redirect()->back()->withErrors([
                    'password' => 'Password yang anda masukan tidak tesuai, silakan periksa dan login kembali.',
                ]);
            }
        } else {
            return redirect()->back()->withErrors([
                'email' => 'email ' . $request->email . ' tidak terdaftar, silakan periksa dan login kembali.',
            ]);
        }
    }

    //Login Dokter
    public function login_dokter()
    {
        return view('frontend.login_dokter');
    }

    public function login_dokter_check(Request $request)
    {
        $user = MasterDokter::on($this->getConnectionDefault())->where('email', '=', $request->email)->first();
        $cekuser = MasterDokter::on($this->getConnectionDefault())->where('email', '=', $request->email)->count();

        if ($cekuser >= 1) {
            if (Auth::guard("dokter")->attempt(['email' => $request->email, 'password' => $request->password])) {
                $role_list = array();
                $actions = array();
                $user_roles = RbacRole::on($this->getConnectionDefault())->whereIn('id', [7])->get();

                session(['super_admin' => 0]);
                foreach ($user_roles as $user_role) {
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

                $apotek_group = MasterGroupApotek::on($this->getConnectionDefault())->find(Auth::guard('dokter')->user()->id_group_apotek);

                $apoteks = MasterApotek::on($this->getConnectionDefault())->where('is_deleted', 0)->get();
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
                session(['is_status_login' => '1']);
                session(['status_login' => 'PT']);

                return redirect()->intended('home_dokter');
            } else {
                return redirect()->back()->withErrors([
                    'password' => 'Password yang anda masukan tidak tesuai, silakan periksa dan login kembali.',
                ]);
            }
        } else {
            return redirect()->back()->withErrors([
                'email' => 'Email ' . $request->email . ' tidak terdaftar, silakan periksa dan login kembali.',
            ]);
        }
    }

    //Login Apoteker
    public function login_apoteker()
    {
        return view('frontend.login_apoteker');
    }

    public function login_apoteker_check(Request $request)
    {
        $user = MasterApoteker::on($this->getConnectionDefault())->where('email', '=', $request->email)->first();
        $cekuser = MasterApoteker::on($this->getConnectionDefault())->where('email', '=', $request->email)->count();


        if ($cekuser >= 1) {
            if (Auth::guard("apoteker")->attempt(['email' => $request->email, 'password' => $request->password])) {
                $role_list = array();
                $actions = array();
                $user_roles = RbacRole::on($this->getConnectionDefault())->whereIn('id', [9])->get();

                session(['super_admin' => 0]);
                foreach ($user_roles as $user_role) {
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

                $apoteks = MasterApotek::on($this->getConnectionDefault())->where('is_deleted', 0)->get();
                $tahuns = MasterTahun::orderby('id', 'DESC')->get();
                session(['id_tahun_active' => date('Y')]);

                $_SESSION["isLogedIn"] = 1;
                session(['actions' => $actions]);
                session(['menu' => $parent_menu]);
                session(['apoteks' => $apoteks]);
                session(['tahuns' => $tahuns]);
                session(['isLogedIn' => 1]);
                session(['role_list' => $role_list]);
                session(['user_roles' => $user_roles]);
                session(['is_status_login' => '1']);
                session(['status_login' => 'PT']);

                return redirect()->intended('home_apoteker');
            } else {
                return redirect()->back()->withErrors([
                    'password' => 'Password yang anda masukan tidak tesuai, silakan periksa dan login kembali.',
                ]);
            }
        } else {
            return redirect()->intended('login')->withErrors([
                'username' => 'Username <strong>' . $request->username . '</strong> tidak terdaftar, silakan periksa dan login kembali.',
            ]);
        }
    }
}
