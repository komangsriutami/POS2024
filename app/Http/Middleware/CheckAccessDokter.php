<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Route;
use Closure;

class CheckAccessDokter
{
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
            if(in_array($action_name, session('actions'))){
                return $next($request);
            }else{
                return $next($request);
                //return redirect()->intended('page_not_authorized');
            }

        }else{
            session()->flash('error', 'Silakan Login terlebih dahulu sebelum anda mengakses halaman ini!');
            return redirect()->intended('login_dokter');
        }

        return $next($request);
    }
}
