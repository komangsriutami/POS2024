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
use App\MasterApotek;
use App\RbacUserApotek;
use App\MasterPosisi;
use App\MasterJabatan;
use App\MasterStatusKaryawan;
use Illuminate\Support\Facades\Hash;

use App;
use Datatables;
use DB;
use Excel;
use Auth;
use Storage;
use File;;
use App\Traits\DynamicConnectionTrait;

class UserController extends Controller
{
    use DynamicConnectionTrait;
    /*
        =================================================================================================================
        For     : Tampilan index data user
        Author  : 
        Date    : 
        =================================================================================================================
    */
    public function index()
    {
        $users = User::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama','id');
        $users->prepend('-- Pilih User --', "");

        return view('admin.index')->with(compact('users'));
    }

    /*
        =================================================================================================================
        For     : Get list data user pada tampilan index
        Author  : 
        Date    : 
        =================================================================================================================
    */
    public function list_user(Request $request)
    {
        DB::connection($this->getConnection())->statement(DB::raw('set @rownum = 0'));
        $data = User::select([DB::raw('@rownum  := @rownum  + 1 AS no'), 
                'users.*'])
        ->where(function($query) use($request){
            $query->where('users.is_deleted', 0);
        });

        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request){
            $query->where(function($query) use($request){
                $query->orwhere('username','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('nama','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('email','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('telepon','LIKE','%'.$request->get('search')['value'].'%');
            });
        })
        ->editcolumn('apoteks', function($data) {
            $user_apoteks = $data->user_apoteks;
            $str_apoteks = '';
            foreach ($user_apoteks as $user_apotek) {
                $str_apoteks = $str_apoteks." - ".$user_apotek->apotek->nama_singkat.'<br/>';
            }

            if($str_apoteks == ''){
                $str_apoteks = '<label class="label label-danger"><i class="fa fa-times"></i> Apotek belum di setting</label>';
            }

            return $str_apoteks;
        })
        ->editcolumn('roles', function($data) {
            $user_roles = $data->user_roles;
            $str_roles = '';
            foreach ($user_roles as $user_role) {
                $str_roles = $str_roles." - ".$user_role->role->nama.'<br/>';
            }

            if($str_roles == ''){
                $str_roles = '<label class="label label-danger"><i class="fa fa-times"></i> Tidak Ada Hak Akses</label>';
            }

            return $str_roles;
        })
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .= '<a href="'.url('/admin/'.$data->id.'/edit').'" title="Edit data" class="btn btn-info"><span data-toggle="tooltip" data-placement="top" title="Edit data"><i class="fa fa-edit"></i></span></a>';
            $btn .= '<a href="'.url('/admin/setting_role_akses/'.$data->id).'" title="Setting Role Akses" class="btn btn-secondary"><span data-toggle="tooltip" data-placement="top" title="Setting Role Akses"><i class="fa fa-users-cog"></i></span></a>';
            $btn .= '<a href="'.url('/admin/setting_apotek_akses/'.$data->id).'" title="Setting Akses Apotek" class="btn bg-olive"><span data-toggle="tooltip" data-placement="top" title="Setting Akses Apotek"><i class="fa fa-building"></i></span></a>';
            $btn .= '<span class="btn btn-danger" onClick="delete_user('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus data"><i class="fa fa-times"></i></span>';
            $btn .='</div>';
            return $btn;
        })
        ->addIndexColumn()
        ->rawColumns(['roles', 'DT_RowIndex', 'action', 'apoteks'])
        ->make(true);  
    }

    public function list_calon_user(Request $request){
        $users = User::select(DB::raw('CAST(id AS CHAR CHARACTER SET utf8) AS id'), 'nama') 
                                    ->where('id', 'LIKE', '%'.$request->q.'%')
                                    ->orWhere('nama', 'LIKE', '%'.$request->q.'%')
                                    ->limit(30)
                                    ->get();

        $data = array();

        foreach ($users as $user) {
            $obj = array();
            $obj['id'] = $user->id;
            $obj['nama'] = $user->nama;
            $data[] = $obj;
        }

        echo json_encode(array(
                            'incomplete_results'=>false,
                            'items'=>$data,
                            'total_count'=>count($users)
                        ));
    }

   
    /*
        =================================================================================================================
        For     : call create user function
        Author  : 
        Date    : 
        =================================================================================================================
    */
    public function create()
    {
        $user = new User;
        $user->setDynamicConnection();
        $jenis_kelamins = MasterJenisKelamin::on($this->getConnectionName())->where('is_deleted', 0)->pluck('jenis_kelamin', 'id');
        $jenis_kelamins->prepend('-- Pilih Jenis Kelamin --','');

        $kewarganegaraans = MasterKewarganegaraan::on($this->getConnectionName())->where('is_deleted', 0)->pluck('kewarganegaraan', 'id');
        $kewarganegaraans->prepend('-- Pilih Kewarganegaraan --','');

        $agamas = MasterAgama::on($this->getConnectionName())->where('is_deleted', 0)->pluck('agama', 'id');
        $agamas->prepend('-- Pilih Agama --','');

        $golongan_darahs = MasterGolonganDarah::on($this->getConnectionName())->where('is_deleted', 0)->pluck('golongan_darah', 'id');
        $golongan_darahs->prepend('-- Pilih Golongan Darah --','');

        $group_apoteks      = MasterGroupApotek::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $group_apoteks->prepend('-- Pilih Group Apotek --','');

        $jabatans = MasterJabatan::on($this->getConnectionName())->where('is_deleted', 0)->where('id_group_apotek', session('id_apotek_active'))->pluck('nama', 'id');
        $jabatans->prepend('-- Pilih Jabatan --','');

        $posisis = MasterPosisi::on($this->getConnectionName())->where('is_deleted', 0)->where('id_group_apotek', session('id_apotek_active'))->pluck('nama', 'id');
        $posisis->prepend('-- Pilih Posisi --','');

        $status_karyawans = MasterStatusKaryawan::pluck('nama', 'id');
        $status_karyawans->prepend('-- Pilih Status --','');

        return view('admin.create')->with(compact('user', 'jenis_kelamins', 'agamas', 'kewarganegaraans', 'golongan_darahs', 'group_apoteks', 'jabatans', 'posisis', 'status_karyawans'));
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

    protected function getToken()
    {
        return hash_hmac('sha256', str_random(40), config('app.key'));
    }

    /*
        =================================================================================================================
        For     : store data user
        Author  : 
        Date    : 
        =================================================================================================================
    */
    public function store(Request $request)
    {
        $user = new User;
        $user->setDynamicConnection();
        $user->fill($request->except('_token', 'password'));
        $user->password = bcrypt($request->password);
        $user->activated = 1;

        $jenis_kelamins = MasterJenisKelamin::on($this->getConnectionName())->where('is_deleted', 0)->pluck('jenis_kelamin', 'id');
        $jenis_kelamins->prepend('-- Pilih Jenis Kelamin --','');

        $kewarganegaraans = MasterKewarganegaraan::on($this->getConnectionName())->where('is_deleted', 0)->pluck('kewarganegaraan', 'id');
        $kewarganegaraans->prepend('-- Pilih Kewarganegaraan --','');

        $agamas = MasterAgama::on($this->getConnectionName())->where('is_deleted', 0)->pluck('agama', 'id');
        $agamas->prepend('-- Pilih Agama --','');

        $golongan_darahs = MasterGolonganDarah::on($this->getConnectionName())->where('is_deleted', 0)->pluck('golongan_darah', 'id');
        $golongan_darahs->prepend('-- Pilih Golongan Darah --','');

        $group_apoteks      = MasterGroupApotek::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $group_apoteks->prepend('-- Pilih Group Apotek --','');

        $jabatans = MasterJabatan::on($this->getConnectionName())->where('is_deleted', 0)->where('id_group_apotek', session('id_apotek_active'))->pluck('nama', 'id');
        $jabatans->prepend('-- Pilih Jabatan --','');

        $posisis = MasterPosisi::on($this->getConnectionName())->where('is_deleted', 0)->where('id_group_apotek', session('id_apotek_active'))->pluck('nama', 'id');
        $posisis->prepend('-- Pilih Posisi --','');

        $status_karyawans = MasterStatusKaryawan::pluck('nama', 'id');
        $status_karyawans->prepend('-- Pilih Status --','');

        $validator = $user->validate();
        if($validator->fails()){
            return view('admin.create')->with(compact('user', 'jenis_kelamins', 'kewarganegaraans', 'agamas', 'golongan_darahs', 'group_apoteks', 'jabatans', 'posisis', 'status_karyawans'))->withErrors($validator);
        }else{
            $user->tgl_lahir = date('Y-m-d', strtotime($user->tgl_lahir));
            $user->created_by = Auth::user()->id;
            $user->color = '#e040fb';
            $user->save();
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('admin');
        }
    }

    /*
        =================================================================================================================
        For     : call edit user function
        Author  : 
        Date    : 
        =================================================================================================================
    */
    public function edit($id)
    {
        $user = User::on($this->getConnectionName())->find($id);
        $user->tgl_lahir = date('Y-m-d', strtotime($user->tgl_lahir));

        $jenis_kelamins = MasterJenisKelamin::on($this->getConnectionName())->where('is_deleted', 0)->pluck('jenis_kelamin', 'id');
        $jenis_kelamins->prepend('-- Pilih Jenis Kelamin --','');

        $kewarganegaraans = MasterKewarganegaraan::on($this->getConnectionName())->where('is_deleted', 0)->pluck('kewarganegaraan', 'id');
        $kewarganegaraans->prepend('-- Pilih Kewarganegaraan --','');

        $agamas = MasterAgama::on($this->getConnectionName())->where('is_deleted', 0)->pluck('agama', 'id');
        $agamas->prepend('-- Pilih Agama --','');

        $golongan_darahs = MasterGolonganDarah::on($this->getConnectionName())->where('is_deleted', 0)->pluck('golongan_darah', 'id');
        $golongan_darahs->prepend('-- Pilih Golongan Darah --','');

        $group_apoteks      = MasterGroupApotek::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $group_apoteks->prepend('-- Pilih Group Apotek --','');

        $jabatans = MasterJabatan::on($this->getConnectionName())->where('is_deleted', 0)->where('id_group_apotek', session('id_apotek_active'))->pluck('nama', 'id');
        $jabatans->prepend('-- Pilih Jabatan --','');

        $posisis = MasterPosisi::on($this->getConnectionName())->where('is_deleted', 0)->where('id_group_apotek', session('id_apotek_active'))->pluck('nama', 'id');
        $posisis->prepend('-- Pilih Posisi --','');

        $status_karyawans = MasterStatusKaryawan::pluck('nama', 'id');
        $status_karyawans->prepend('-- Pilih Status --','');

        return view('admin.edit')->with(compact('user', 'jenis_kelamins', 'kewarganegaraans', 'agamas', 'golongan_darahs', 'group_apoteks', 'jabatans', 'posisis', 'status_karyawans'));
    }



    /*
        =================================================================================================================
        For     : call update bank function
        Author  : 
        Date    : 
        =================================================================================================================
    */
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = User::on($this->getConnectionName())->find($id);
        $user->fill($request->except('_token', 'password'));

        $from_profile = $request->from_profile;
        if(isset($request->is_ganti_password)) {
            if($request->is_ganti_password_val == 1) {
                $user->password = bcrypt($request->password);
            }
        } 

        $jenis_kelamins = MasterJenisKelamin::on($this->getConnectionName())->where('is_deleted', 0)->pluck('jenis_kelamin', 'id');
        $jenis_kelamins->prepend('-- Pilih Jenis Kelamin --','');

        $kewarganegaraans = MasterKewarganegaraan::on($this->getConnectionName())->where('is_deleted', 0)->pluck('kewarganegaraan', 'id');
        $kewarganegaraans->prepend('-- Pilih Kewarganegaraan --','');

        $agamas = MasterAgama::on($this->getConnectionName())->where('is_deleted', 0)->pluck('agama', 'id');
        $agamas->prepend('-- Pilih Agama --','');

        $golongan_darahs = MasterGolonganDarah::on($this->getConnectionName())->where('is_deleted', 0)->pluck('golongan_darah', 'id');
        $golongan_darahs->prepend('-- Pilih Golongan Darah --','');

        $group_apoteks      = MasterGroupApotek::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $group_apoteks->prepend('-- Pilih Group Apotek --','');

        $jabatans = MasterJabatan::on($this->getConnectionName())->where('is_deleted', 0)->where('id_group_apotek', session('id_apotek_active'))->pluck('nama', 'id');
        $jabatans->prepend('-- Pilih Jabatan --','');

        $posisis = MasterPosisi::on($this->getConnectionName())->where('is_deleted', 0)->where('id_group_apotek', session('id_apotek_active'))->pluck('nama', 'id');
        $posisis->prepend('-- Pilih Posisi --','');

        $status_karyawans = MasterStatusKaryawan::pluck('nama', 'id');
        $status_karyawans->prepend('-- Pilih Status --','');

        $validator = $user->validate();
        if($validator->fails()){
            return view('admin.edit')->with(compact('user', 'jenis_kelamins', 'kewarganegaraans', 'agamas', 'golongan_darahs', 'group_apoteks', 'jabatans', 'posisis', 'status_karyawans'))->withErrors($validator);
        }else{
            $user->tgl_lahir = date('Y-m-d', strtotime($user->tgl_lahir));
            $user->updated_by = Auth::user()->id;
            $user->save();
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('admin');
        }
    }


    public function setting_role_akses($id)
    {
        $user = User::on($this->getConnectionName())->find($id);
        $roles = RbacRole::on($this->getConnectionName())->where('is_deleted', 0)->get();

        foreach ($roles as $key => $val) {
            $user_role  = RbacUserRole::on($this->getConnectionName())->where('id_user', $user->id)->where('id_role', $val->id)->first();
            if(!empty($user_role)) {
                $val->ada_role = 1;
            } else {
                $val->ada_role = 0;
            }
        }

        return view('admin.setting_role_akses')->with(compact('user', 'roles'));
    }

    public function update_roles_akses(Request $request, $id)
    {
        $user = User::on($this->getConnectionName())->find($id);
        $roles = RbacRole::on($this->getConnectionName())->where('is_deleted', 0)->get();

        if(isset($roles)){
            foreach ($roles as $key => $val) {
                $user_role  = RbacUserRole::on($this->getConnectionName())->where('id_user', $user->id)->where('id_role', $val->id)->first();
                if(!empty($user_role)) {
                    $val->ada_role = 1;
                } else {
                    $val->ada_role = 0;
                }
            }
        }

        $validator = $user->validate();
        if($validator->fails()){
            session()->flash('error', 'Gagal menyimpan data! Perhatikan semua kolom agar input sesuai');
            return view('admin.setting_role_akses')->with(compact('user', 'roles'))->withErrors($validator);
        }else{
            $user->save_plus(2, $request->user_role);

            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('admin');
        }
    }

    public function add_row_role(Request $request)
    {
        $counter = $request->counter;
        $no = $counter+1;

        $user_role = new RbacUserRole;
        $user_role->setDynamicConnection();
        $roles = RbacRole::on($this->getConnectionName())->where('is_deleted', 0)->get();

        return view('admin._role_detail_form')->with(compact('user_role','no','roles'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::on($this->getConnectionName())->find($id);
        $user->user_roles()->delete();
        $user->is_deleted = 1;
        if($user->save()){
            echo 1;
        }else{
            echo 0;
        }
    }

    public function profile() {
        $id = Auth::user()->id;
        $user = User::on($this->getConnectionName())->find($id);

        $jenis_kelamins = MasterJenisKelamin::on($this->getConnectionName())->where('is_deleted', 0)->pluck('jenis_kelamin', 'id');
        $jenis_kelamins->prepend('-- Pilih Jenis Kelamin --','');

        $kewarganegaraans = MasterKewarganegaraan::on($this->getConnectionName())->where('is_deleted', 0)->pluck('kewarganegaraan', 'id');
        $kewarganegaraans->prepend('-- Pilih Kewarganegaraan --','');

        $agamas = MasterAgama::on($this->getConnectionName())->where('is_deleted', 0)->pluck('agama', 'id');
        $agamas->prepend('-- Pilih Agama --','');

        $golongan_darahs = MasterGolonganDarah::on($this->getConnectionName())->where('is_deleted', 0)->pluck('golongan_darah', 'id');
        $golongan_darahs->prepend('-- Pilih Golongan Darah --','');

        $group_apoteks      = MasterGroupApotek::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $group_apoteks->prepend('-- Pilih Group Apotek --','');

        $jabatans = MasterJabatan::on($this->getConnectionName())->where('is_deleted', 0)->where('id_group_apotek', session('id_apotek_active'))->pluck('nama', 'id');
        $jabatans->prepend('-- Pilih Jabatan --','');

        $posisis = MasterPosisi::on($this->getConnectionName())->where('is_deleted', 0)->where('id_group_apotek', session('id_apotek_active'))->pluck('nama', 'id');
        $posisis->prepend('-- Pilih Posisi --','');

        $status_karyawans = MasterStatusKaryawan::pluck('nama', 'id');
        $status_karyawans->prepend('-- Pilih Status --','');

        $ttd = DB::connection($this->getConnectionName())->table('tb_users_ttd')->where('id_user', $user->id)->first();

        return view('admin.profile')->with(compact('user', 'jenis_kelamins', 'kewarganegaraans', 'agamas', 'golongan_darahs', 'group_apoteks', 'jabatans', 'posisis', 'status_karyawans', 'ttd'));
    }

    public function ubah_password($id) 
    {
        $user = User::on($this->getConnectionName())->find($id);

        return view('admin.ubah_password')->with(compact('user'));
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri Utami.
        Date    : 24/02/2020
        =======================================================================================
    */
    public function update_profile(Request $request, $id)
    {

        $user = User::on($this->getConnectionName())->find($id);
        $url_file = $user->file;
        $user->fill($request->except('_token', 'password'));

        $from_profile = $request->from_profile;
        if(isset($request->is_ganti_password)) {
            if($request->is_ganti_password_val == 1) {
                $user->password = bcrypt($request->password);
            }
        } 

        $validator = $user->validate();
        if($validator->fails()){
            $jenis_kelamins = MasterJenisKelamin::on($this->getConnectionName())->where('is_deleted', 0)->pluck('jenis_kelamin', 'id');
            $jenis_kelamins->prepend('-- Pilih Jenis Kelamin --','');

            $kewarganegaraans = MasterKewarganegaraan::on($this->getConnectionName())->where('is_deleted', 0)->pluck('kewarganegaraan', 'id');
            $kewarganegaraans->prepend('-- Pilih Kewarganegaraan --','');

            $agamas = MasterAgama::on($this->getConnectionName())->where('is_deleted', 0)->pluck('agama', 'id');
            $agamas->prepend('-- Pilih Agama --','');

            $golongan_darahs = MasterGolonganDarah::on($this->getConnectionName())->where('is_deleted', 0)->pluck('golongan_darah', 'id');
            $golongan_darahs->prepend('-- Pilih Golongan Darah --','');

            $group_apoteks      = MasterGroupApotek::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama_singkat', 'id');
            $group_apoteks->prepend('-- Pilih Group Apotek --','');

            $jabatans = MasterJabatan::on($this->getConnectionName())->where('is_deleted', 0)->where('id_group_apotek', session('id_apotek_active'))->pluck('nama', 'id');
            $jabatans->prepend('-- Pilih Jabatan --','');

            $posisis = MasterPosisi::on($this->getConnectionName())->where('is_deleted', 0)->where('id_group_apotek', session('id_apotek_active'))->pluck('nama', 'id');
            $posisis->prepend('-- Pilih Posisi --','');

            $status_karyawans = MasterStatusKaryawan::pluck('nama', 'id');
            $status_karyawans->prepend('-- Pilih Status --','');

            return view('profile')->with(compact('user', 'jenis_kelamins', 'kewarganegaraans', 'agamas', 'golongan_darahs', 'group_apoteks', 'jabatans', 'posisis', 'status_karyawans'))->withErrors($validator);
        }else{
            # file file
            $date_now = date('Y-m-d H:i:s');
            if (!empty($request->file)) {
                $nama_file = $request->file->getClientOriginalName();
                $split_co = explode('.' , $nama_file);
                $ext_co = end($split_co);
                $ext_aprove = array('png', 'jpg', 'jpeg');
                # cek kembali apakah ext file tersebut ada di ext yang valid
                if (in_array($ext_co, $ext_aprove)) {
                    $file_name_co = md5($split_co[0]."-".$date_now);
                    $file = $request->file;
                    $destination_path_co = public_path("temp/");
                    $destination_filename_co = $file_name_co.".".$ext_co;
                    $user->file->move($destination_path_co, $destination_filename_co);
                    $user->file = $file_name_co.".".$ext_co;

                    # SRI | convert image to blob
                    $path = $destination_path_co.$destination_filename_co;
                    $ttd = file_get_contents($path);
                    $base64 = base64_encode($ttd);

                    $cek = DB::connection($this->getConnectionName())->table('tb_users_ttd')->where('id_user', $user->id)->first();
                    if(is_null($cek)) {
                        DB::connection($this->getConnectionName())->table('tb_users_ttd')->insert(['id_user' => $user->id, 'image' => $base64, 'created_at' => date('Y-m-d H:i:s')]);
                    } else {
                        DB::connection($this->getConnectionName())->table('tb_users_ttd')->where('id_user', $user->id)->update(['image' => $base64, 'updated_at' => date('Y-m-d H:i:s')]);
                    }

                    # SRI | hapus image
                    if (File::exists($path)) {
                        File::delete($path);
                    }
                } else {
                    session()->flash('error', 'Extensi file tidak diperbolehkan!');
                    return redirect('/profile')->with('error', 'Extensi file tidak diperbolehkan!');
                }
            }

            $user->tgl_lahir = date('Y-m-d', strtotime($user->tgl_lahir));
            $user->updated_by = Auth::user()->id;
            $user->save();
            session()->flash('success', 'Sukses memperbaharui data!');
            return redirect('/profile')->with('message', 'Sukses menyimpan data');
            
        }
    }

    public function setting_apotek_akses($id)
    {
        $user = User::on($this->getConnectionName())->find($id);
        $apoteks = MasterApotek::on($this->getConnectionName())->where('is_deleted', 0)->where('id_group_apotek', $user->id_group_apotek)->get();

        foreach ($apoteks as $key => $val) {
            $user_apotek  = RbacUserApotek::on($this->getConnectionName())->where('id_user', $user->id)->where('id_apotek', $val->id)->first();
            if(!empty($user_apotek)) {
                $val->ada_apotek = 1;
            } else {
                $val->ada_apotek = 0;
            }
        }

        return view('admin.setting_apotek_akses')->with(compact('user', 'apoteks'));
    }

    public function update_apotek_akses(Request $request, $id)
    {
        $user = User::on($this->getConnectionName())->find($id);
        $apoteks = MasterApotek::on($this->getConnectionName())->where('is_deleted', 0)->where('id_group_apotek', $user->id_group_apotek)->get();

        if(isset($apoteks)){
            foreach ($apoteks as $key => $val) {
                $user_apotek  = RbacUserApotek::on($this->getConnectionName())->where('id_user', $user->id)->where('id_apotek', $val->id)->first();
                if(!empty($user_apotek)) {
                    $val->ada_apotek = 1;
                } else {
                    $val->ada_apotek = 0;
                }
            }
        }

        $validator = $user->validate();
        if($validator->fails()){
            session()->flash('error', 'Gagal menyimpan data! Perhatikan semua kolom agar input sesuai');
            return view('admin.setting_apotek_akses')->with(compact('user', 'apoteks'))->withErrors($validator);
        }else{
            $user->save_plus_apotek(2, $request->user_apotek);

            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('admin');
        }
    }
}
