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
                
            } else {
                $user = Cache::get('sessionUser_');
                if(!is_null($user)){
                   
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
        }

        return $next($request);
    }
}
