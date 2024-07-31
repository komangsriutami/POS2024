<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\User;
use App\RbacUserRole;
use App\RbacRole;
use App\MasterAgama;
use App\MasterGolonganDarah;
use App\MasterJenisKelamin;
use App\MasterKewarganegaraan;
use App\MasterGroupApotek;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use App;
use Datatables;
use DB;
use Excel;
use Auth;
use Mail;

class M_UserController extends Controller
{
    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 24/02/2020
        =======================================================================================
    */
    public function index()
    {
        return view('user.index');
    }


    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 22/02/2020
        =======================================================================================
    */
    public function list_data_user(Request $request)
    {
    	$order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        $super_admin = session('super_admin');
        DB::statement(DB::raw('set @rownum = 0'));
        $data = User::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'users.*'])
        ->where(function($query) use($request, $super_admin){
            $query->where('users.is_deleted','=','0');
            if($super_admin == 0) {
                $query->where('users.id_group_apotek', Auth::user()->id_group_apotek);
            }
        });
        
        $datatables = Datatables::of($data);
        return $datatables  
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->orwhere('nama','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('username','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('telepon','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('email','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  

        ->editcolumn('id_jenis_kelamin', function($data){
            return $data->jenis_kelamin->jenis_kelamin; 
        }) 
        ->editcolumn('id_kewarganegaraan', function($data){
            return $data->kewarganegaraan->kewarganegaraan; 
        }) 
        ->editcolumn('id_agama', function($data){
            return $data->agama->agama; 
        }) 
        ->editcolumn('id_gol_darah', function($data){
            return $data->golongan_darah->golongan_darah; 
        }) 
        ->editcolumn('id_group_apotek', function($data){
            return $data->group_apotek->nama_singkat; 
        }) 
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .= '<span class="btn btn-primary" onClick="edit_data('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span>';
            $btn .= '<span class="btn btn-danger" onClick="delete_user('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus data"><i class="fa fa-times"></i></span>';
            $btn .='</div>';
            return $btn;
        })
        ->addIndexColumn()
        ->rawColumns(['id_apoteker', 'id_group_apotek', 'action'])
        ->make(true);  
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri Utami.
        Date    : 24/02/2020
        =======================================================================================
    */
    public function create()
    {
        $user = new User;

        $jenis_kelamins = MasterJenisKelamin::where('is_deleted', 0)->pluck('jenis_kelamin', 'id');
        $jenis_kelamins->prepend('-- Pilih Jenis Kelamin --','');

        $kewarganegaraans = MasterKewarganegaraan::where('is_deleted', 0)->pluck('kewarganegaraan', 'id');
        $kewarganegaraans->prepend('-- Pilih Kewarganegaraan --','');

        $agamas = MasterAgama::where('is_deleted', 0)->pluck('agama', 'id');
        $agamas->prepend('-- Pilih Agama --','');

        $golongan_darahs = MasterGolonganDarah::where('is_deleted', 0)->pluck('golongan_darah', 'id');
        $golongan_darahs->prepend('-- Pilih Golongan Darah --','');

        $group_apoteks      = MasterGroupApotek::where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $group_apoteks->prepend('-- Pilih Group Apotek --','');

        return view('user.create')->with(compact('user', 'jenis_kelamins', 'agamas', 'kewarganegaraans', 'golongan_darahs', 'group_apoteks'));
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri Utami
        Date    : 24/02/2020
        =======================================================================================
    */
    public function store(Request $request)
    {
        $user = new User;
        $user->fill($request->except('_token', 'password'));
        $user->password = bcrypt($request->password);
        $user->activated = 1;

        $jenis_kelamins = MasterJenisKelamin::where('is_deleted', 0)->pluck('jenis_kelamin', 'id');
        $jenis_kelamins->prepend('-- Pilih Jenis Kelamin --','');

        $kewarganegaraans = MasterKewarganegaraan::where('is_deleted', 0)->pluck('kewarganegaraan', 'id');
        $kewarganegaraans->prepend('-- Pilih Kewarganegaraan --','');

        $agamas = MasterAgama::where('is_deleted', 0)->pluck('agama', 'id');
        $agamas->prepend('-- Pilih Agama --','');

        $golongan_darahs = MasterGolonganDarah::where('is_deleted', 0)->pluck('golongan_darah', 'id');
        $golongan_darahs->prepend('-- Pilih Golongan Darah --','');

        $group_apoteks      = MasterGroupApotek::where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $group_apoteks->prepend('-- Pilih Group Apotek --','');

        $validator = $user->validate();
        if($validator->fails()){
            return view('user.create')->with(compact('user', 'jenis_kelamins', 'kewarganegaraans', 'agamas', 'golongan_darahs', 'group_apoteks'))->withErrors($validator);
        }else{
            $user->tgl_lahir = date('Y-m-d', strtotime($user->tgl_lahir));
            $user->created_by = Auth::user()->id;
            $user->color = '#e040fb';
            $user->save();
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('user');
        }
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri Utami.
        Date    : 24/02/2020
        =======================================================================================
    */
    public function show($id)
    {
        //
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri Utami.
        Date    : 24/02/2020
        =======================================================================================
    */
    public function edit($id)
    {
        $user        = User::find($id);

        $jenis_kelamins = MasterJenisKelamin::where('is_deleted', 0)->pluck('jenis_kelamin', 'id');
        $jenis_kelamins->prepend('-- Pilih Jenis Kelamin --','');

        $kewarganegaraans = MasterKewarganegaraan::where('is_deleted', 0)->pluck('kewarganegaraan', 'id');
        $kewarganegaraans->prepend('-- Pilih Kewarganegaraan --','');

        $agamas = MasterAgama::where('is_deleted', 0)->pluck('agama', 'id');
        $agamas->prepend('-- Pilih Agama --','');

        $golongan_darahs = MasterGolonganDarah::where('is_deleted', 0)->pluck('golongan_darah', 'id');
        $golongan_darahs->prepend('-- Pilih Golongan Darah --','');

        $group_apoteks      = MasterGroupApotek::where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $group_apoteks->prepend('-- Pilih Group Apotek --','');

        return view('user.edit')->with(compact('user', 'jenis_kelamins', 'kewarganegaraans', 'agamas', 'golongan_darahs', 'group_apoteks'));
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri Utami.
        Date    : 24/02/2020
        =======================================================================================
    */
    public function update(Request $request, $id)
    {
        $user = User::find($id);
        $user->fill($request->except('_token', 'password'));

        $from_profile = $request->from_profile;
        if(isset($request->is_ganti_password)) {
            if($request->is_ganti_password_val == 1) {
                $user->password = bcrypt($request->password);
            }
        } 

        if($from_profile == 1) {
            $validator = $user->validate();
            if($validator->fails()){
                $jenis_kelamins = MasterJenisKelamin::where('is_deleted', 0)->pluck('jenis_kelamin', 'id');
                $jenis_kelamins->prepend('-- Pilih Jenis Kelamin --','');

                $kewarganegaraans = MasterKewarganegaraan::where('is_deleted', 0)->pluck('kewarganegaraan', 'id');
                $kewarganegaraans->prepend('-- Pilih Kewarganegaraan --','');

                $agamas = MasterAgama::where('is_deleted', 0)->pluck('agama', 'id');
                $agamas->prepend('-- Pilih Agama --','');

                $golongan_darahs = MasterGolonganDarah::where('is_deleted', 0)->pluck('golongan_darah', 'id');
                $golongan_darahs->prepend('-- Pilih Golongan Darah --','');

                $group_apoteks      = MasterGroupApotek::where('is_deleted', 0)->pluck('nama_singkat', 'id');
                $group_apoteks->prepend('-- Pilih Group Apotek --','');

                return view('profile')->with(compact('user', 'jenis_kelamins', 'kewarganegaraans', 'agamas', 'golongan_darahs', 'group_apoteks'))->withErrors($validator);
            }else{
                $user->tgl_lahir = date('Y-m-d', strtotime($user->tgl_lahir));
                $user->updated_by = Auth::user()->id;
                $user->save();
                session()->flash('success', 'Sukses memperbaharui data!');
                return redirect('/profile')->with('message', 'Sukses menyimpan data');
                
            }
        } else {
            $validator = $user->validate();
            if($validator->fails()){
                echo json_encode(array('status' => 0));
            }else{
                $user->tgl_lahir = date('Y-m-d', strtotime($user->tgl_lahir));
                $user->updated_by = Auth::user()->id;
                $user->save();
                echo json_encode(array('status' => 1));
            }
        }
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri Utami.
        Date    : 24/02/2020
        =======================================================================================
    */
    public function destroy($id)
    {
        $user = User::find($id);
        $user->is_deleted = 1;
        if($user->save()){
            echo 1;
        }else{
            echo 0;
        }
    }

    /*
        =======================================================================================
        For     : Get token
        Author  : Govi.
        Date    : 16/09/2021
        =======================================================================================
    */
    protected function getToken()
    {
        $random = Str::random(40);
        return hash_hmac('sha256', $random, config('app.key'));
    }

    /*
        =======================================================================================
        For     : Menampilkan halaman invite user
        Author  : Govi.
        Date    : 14/09/2021
        =======================================================================================
    */
    public function invite_view(Request $request)
    {
        $user = new User;
        $roles = RbacRole::where('is_deleted', 0)->get();
        return view('user.invite')->with(compact('user', 'roles'));
    }

    /*
        =======================================================================================
        For     : Kirim email invite user
        Author  : Govi.
        Date    : 14/09/2021
        =======================================================================================
    */
    public function invite_submit(Request $request)
    {
        DB::beginTransaction(); 
        try {
            $user = new User;
            $user->fill($request->except('_token'));

            $validator = $user->validate_invite();
            if($validator->fails()){
                $roles = RbacRole::where('is_deleted', 0)->get();
                return view('user.invite')
                    ->with(compact('user', 'roles'))
                    ->withErrors($validator);
            } else {
                $user->remember_token = $this->getToken();
                $user->created_by = Auth::user()->id;
                $user->save();
                foreach ($request->roles as $role) {
                    $rbac_user_role = new RbacUserRole;
                    $rbac_user_role->id_user = $user->id;
                    $rbac_user_role->id_role = $role;
                    $rbac_user_role->save();
                }
                $link = route('confirm_user', $user->remember_token);
                Mail::to($user->email)->send(new \App\Mail\MailInviteUser($user, $link));
                DB::commit();
                session()->flash('success', 'Sukses invite user!');
                return redirect('user');
            }
        } catch(\Exception $e){
            dd($e);
            DB::rollback();
            session()->flash('error', 'Error!');
            return redirect('user');
        }
    }

    /*
        =======================================================================================
        For     : Halaman confirm invite user
        Author  : Govi.
        Date    : 16/09/2021
        =======================================================================================
    */
    public function invite_confirm(Request $request)
    {
        $user = User::where('remember_token', $request->token)->first();
        $user->tgl_lahir = null;

        $jenis_kelamins = MasterJenisKelamin::where('is_deleted', 0)->pluck('jenis_kelamin', 'id');
        $kewarganegaraans = MasterKewarganegaraan::where('is_deleted', 0)->pluck('kewarganegaraan', 'id');
        $agamas = MasterAgama::where('is_deleted', 0)->pluck('agama', 'id');
        $golongan_darahs = MasterGolonganDarah::where('is_deleted', 0)->pluck('golongan_darah', 'id');
        $group_apoteks      = MasterGroupApotek::where('is_deleted', 0)->pluck('nama_singkat', 'id');

        return view('frontend.confirm_invite_user')->with(compact(
            'user',
            'jenis_kelamins',
            'agamas',
            'kewarganegaraans',
            'golongan_darahs',
            'group_apoteks'
        ));
    }

    /*
        =======================================================================================
        For     : Update user by invitation
        Author  : Govi.
        Date    : 16/09/2021
        =======================================================================================
    */
    public function invite_confirm_post(Request $request)
    {
        $user = User::where('id', $request->id)->first();
        $user->fill($request->except('_token'));
        
        $validator = $user->validate();
        if($validator->fails()){
            $jenis_kelamins = MasterJenisKelamin::where('is_deleted', 0)->pluck('jenis_kelamin', 'id');
            $kewarganegaraans = MasterKewarganegaraan::where('is_deleted', 0)->pluck('kewarganegaraan', 'id');
            $agamas = MasterAgama::where('is_deleted', 0)->pluck('agama', 'id');
            $golongan_darahs = MasterGolonganDarah::where('is_deleted', 0)->pluck('golongan_darah', 'id');
            $group_apoteks = MasterGroupApotek::where('is_deleted', 0)->pluck('nama_singkat', 'id');
            
            return view('frontend.confirm_invite_user')
                ->with(compact(
                    'user',
                    'jenis_kelamins',
                    'agamas',
                    'kewarganegaraans',
                    'golongan_darahs',
                    'group_apoteks'
                ))
                ->withErrors($validator);
        }else{
            $user->password = bcrypt($request->password);
            $user->activated = 1;
            $user->updated_by = $user->id;
            $user->color = '#e040fb';
            $user->remember_token = null;
            $user->save();
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('login_outlet');
        }

    }
}
