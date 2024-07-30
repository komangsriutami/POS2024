<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\User;
use App\MasterPasien;
use App\MasterDokter;
use App\MasterApoteker;
use App\ActivationService;
use App\RbacUserRole;
use App\MasterJenisPaketSistem;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Session;
use Auth;
use Mail;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Traits\DynamicConnectionTrait;

class RegisterController extends Controller
{
    use DynamicConnectionTrait;
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
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
        $this->middleware('guest');
    }

    protected function getToken()
    {
        $random = Str::random(40);
        return hash_hmac('sha256', $random, config('app.key'));
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    //Register Pasien
    public function register_pasien()
    {
        return view('frontend.register_pasien');
    }


    public function register_pasien_post(Request $request)
    {
        $pasien = new MasterPasien;
        $pasien->setDynamicConnection();
        $pasien->nama = $request->nama;
        $pasien->email = $request->email;
        $pasien->activated = 0;
        $pasien->password = bcrypt($request->password);

        $validator = $pasien->validator_pasien();
        if ($validator->fails()) {
            return view('frontend.register_pasien')->withErrors($validator);
        } else {
            $pasien->save();
            $token = $this->getToken();
            $pasien->remember_token = $token;
            $pasien->created_at = date('Y-m-d H:i:s');
            $pasien->save();
            $link = route('pasien.activate', $token);
            $data = array('pesan' => $link, 'pasien' => $pasien->toArray());
            Mail::to($pasien->email)->send(new \App\Mail\MailActivationPasien($data));
        }

        return redirect('/login_pasien')->with('status', 'Terimakasih telah melakukan registrasi. Email aktivasi akun telah dikirimkan ke email anda. Silakan klik link aktivasi email untuk mengaktifkan akun anda. Jika anda tidak mendapatkan email, silakan cek folder spam pada email anda.');
    }

    // public function register_pasien(){
    //     $kewarganegaraans = MasterKewarganegaraan::orderBy("id")->get();

    //     $jeniskelamins = MasterJenisKelamin::orderBy("id")->get();

    //     $golongandarahs = MasterGolonganDarah::orderBy("id")->get();
    //     return view('frontend.register_pasien')->with(compact('kewarganegaraans', 'jeniskelamins', 'golongandarahs'));
    // }

    // protected function create_pasien(Request $data)
    // {
    //     $allData = new RegPasien;
    //     $allData->setDynamicConnection();
    //     $allData->fill($data->only(
    //         ['id_kewarganegaraan',
    //         'id_jenis_kelamin',
    //         'id_golongan_darah',
    //         'username',
    //         'password',
    //         'nama',
    //         'tempat_lahir',
    //         'tgl_lahir',
    //         'telepon',
    //         'email',
    //         'alamat',
    //         'is_pernah_berobat',
    //         'is_bpjs',
    //         'no_bpjs',
    //         'id_reference']
    //     ));

    //     $user = RegPasien::on($this->getConnectionName())->where('email','=',$data->email)->count();

    //     $validator = $allData->validate();
    //     $errorMessage = $validator->messages();

    //     $dataCache = $data->all();
    //     //dd($dataCache['nama']);
    //     if($validator->fails()){
    //         return redirect()->intended('/register_pasien')->withErrors([
    //             'username' => $errorMessage->first(),
    //         ])->withInput($data->all());
    //     }else if($user >= 1){
    //         return redirect()->intended('/register_pasien')->withErrors([
    //             'username' => 'Email yang didaftarkan sudah ada',
    //         ])->withInput($data->all());
    //     }else if($data['password'] != $data["password_confirm"]){
    //         return redirect()->intended('/register_pasien')->withErrors([
    //             'username' => 'Password dan Confirm Password tidak Sama',
    //         ])->withInput($data->all());
    //     }else if(strlen($data['password'])<6){
    //         return redirect()->intended('/register_pasien')->withErrors([
    //             'username' => 'Password minimal 6 karakter',
    //         ])->withInput($data->all());
    //     }else if($data['is_bpjs']=="1" && (is_null($data['no_bpjs']) || $data['no_bpjs']=="")){
    //         return redirect()->intended('/register_pasien')->withErrors([
    //             'username' => "Nomor BPJS isi terlabih dahulu.",
    //         ])->withInput($data->all());
    //     }else{
    //         $user = RegPasien::create([
    //             'id_kewarganegaraan' => $data['id_kewarganegaraan'],
    //             'id_jenis_kelamin' => $data['id_jenis_kelamin'],
    //             'id_golongan_darah' => $data['id_golongan_darah'],
    //             'id_agama' => 0,
    //             'username' => $data['username'],
    //             'password' => Hash::make($data['password']),
    //             'nama' => $data['nama'],
    //             'tempat_lahir' => $data['tempat_lahir'],
    //             'tgl_lahir' => $data['tgl_lahir'],
    //             'telepon' => $data['telepon'],
    //             'email' => $data['email'],
    //             'alamat' => $data['alamat'],
    //             'is_pernah_berobat' => $data['is_pernah_berobat'],
    //             'is_bpjs' => $data['is_bpjs'],
    //             'no_bpjs' => $data['no_bpjs'],
    //             'id_reference' => 1,
    //             'activated' => 1,
    //         ]);
    //         session(['id' => $user['id']]);

    //         $kewarganegaraan = MasterKewarganegaraan::on($this->getConnectionName())->select("kewarganegaraan")->where("id", $user['id_kewarganegaraan'])->first();
    //         session(['kewarganegaraan' => $kewarganegaraan["kewarganegaraan"]]);
    //         session(['id_kewarganegaraan' => $user["id_kewarganegaraan"]]);
    //         session(['id_jenis_kelamin' => $user['id_jenis_kelamin']]);
    //         session(['id_golongan_darah' => $user['id_golongan_darah']]);
    //         session(['username' => $user['username']]);
    //         session(['password' => $user['password']]);
    //         session(['nama' => $user['nama']]);
    //         session(['tempat_lahir' => $user['tempat_lahir']]);
    //         session(['tgl_lahir' => $user['tgl_lahir']]);
    //         session(['telepon' => $user['telepon']]);
    //         session(['email' => $user['email']]);
    //         session(['alamat' => $user['alamat']]);
    //         session(['is_pernah_berobat' => $user['is_pernah_berobat']]);
    //         session(['is_bpjs' => $user['is_bpjs']]);
    //         session(['no_bpjs' => $user['no_bpjs']]);
    //         session(['id_reference' => $user['id_reference']]);
    //         return redirect()->intended("/search_dokter");
    //     }
    // }

    // protected function create_anggota_pasien(Request $data)
    // {
    //     $allData = new RegPasien;
    //     $allData->setDynamicConnection();
    //     $allData->fill($data->only(
    //         ['id_kewarganegaraan',
    //         'id_jenis_kelamin',
    //         'id_golongan_darah',
    //         'username',
    //         'password',
    //         'nama',
    //         'tempat_lahir',
    //         'tgl_lahir',
    //         'telepon',
    //         'email',
    //         'alamat',
    //         'is_pernah_berobat',
    //         'is_bpjs',
    //         'no_bpjs',
    //         'id_reference']
    //     ));
    //     $validator = $allData->validateAnggota();
    //     $errorMessage = $validator->messages();

    //     if($validator->fails()){
    //         return redirect()->intended("/info_akun")->withErrors([
    //             'username' => $errorMessage->first(),
    //         ])->withInput($data->all());
    //     }else if($data['is_bpjs']=="1" && (is_null($data['no_bpjs']) || $data['no_bpjs']=="")){
    //         return redirect()->intended('/info_akun')->withErrors([
    //             'username' => "Nomor BPJS isi terlabih dahulu.",
    //         ])->withInput($data->all());
    //     }else{
    //         $user = RegPasien::create([
    //             'email' => "-",
    //             'password' => "-",
    //             'id_kewarganegaraan' => $data['id_kewarganegaraan'],
    //             'id_jenis_kelamin' => $data['id_jenis_kelamin'],
    //             'id_golongan_darah' => $data['id_golongan_darah'],
    //             'username' => $data['username'],
    //             'nama' => $data['nama'],
    //             'tempat_lahir' => $data['tempat_lahir'],
    //             'tgl_lahir' => $data['tgl_lahir'],
    //             'telepon' => $data['telepon'],
    //             'alamat' => $data['alamat'],
    //             'is_pernah_berobat' => $data['is_pernah_berobat'],
    //             'is_bpjs' => $data['is_bpjs'],
    //             'no_bpjs' => $data['no_bpjs'],
    //             'id_reference' => session('id'),
    //             'activated' => 1,
    //         ]);
    //     }

    //     return redirect()->intended("/info_akun");
    // }

    //Register Dokter
    public function register_dokter()
    {
        return view('frontend.register_dokter');
    }

    public function register_dokter_post(Request $request)
    {
        $dokter = new MasterDokter;
        $dokter->setDynamicConnection();
        $dokter->nama = $request->nama;
        $dokter->email = $request->email;
        $dokter->activated = 0;
        $dokter->password = bcrypt($request->password);

        $validator = $dokter->validator_dokter();
        if ($validator->fails()) {
            return view('frontend.register_dokter')->withErrors($validator);
        } else {
            $token = $this->getToken();
            $dokter->remember_token = $token;
            $dokter->created_at = date('Y-m-d H:i:s');
            $dokter->save();
            $link = route('dokter.activate', $token);
            $data = array('pesan' => $link, 'dokter' => $dokter->toArray());
            Mail::to($dokter->email)->send(new \App\Mail\MailActivationDokter($data));
        }

        //
        return redirect('/login_dokter')->with('status', 'Terimakasih telah melakukan registrasi. Email aktivasi akun telah dikirimkan ke email anda. Silakan klik link aktivasi email untuk mengaktifkan akun anda. Jika anda tidak mendapatkan email, silakan cek folder spam pada email anda.');
    }

    //Register Outlet
    public function register_outlet()
    {
        $jenispaket = MasterJenisPaketSistem::on($this->getConnectionName())->where('is_deleted', 0)->get();

        return view('frontend.register_outlet')->with(compact('jenispaket'));
    }

    public function register_outlet_post(Request $request)
    {
        $user = new User;
        $user->setDynamicConnection();
        $user->username = $request->username;
        $user->nama = $request->nama;
        $user->email = $request->email;
        $user->id_jenis_paket_sistem = $request->id_jenis_paket_sistem;
        $user->activated = 0;
        $user->password = bcrypt($request->password);

        $validator = Validator::make((array)$request->all(), [
            'username' => 'required',
            'nama' => 'required',
            'email' => 'required',
            'id_jenis_paket_sistem' => 'required',
            'password' => 'required',
            'password_confirm' => 'required'
        ]);

        if ($validator->fails()) {
            $jenispaket = MasterJenisPaketSistem::on($this->getConnectionName())->where('is_deleted', 0)->get();
            return view('frontend.register_outlet')->with(compact('jenispaket'))->withErrors($validator);
        } else {
            $token = $this->getToken();
            $user->remember_token = $token;
            $user->created_at = date('Y-m-d H:i:s');
            $user->save();

            $rbac_role_user = new RbacUserRole();
            $rbac_role_user->setDynamicConnection();
            $rbac_role_user->id_user = $user->id;
            $rbac_role_user->id_role = 6;
            $rbac_role_user->save();

            $link = route('outlet.activate', $token);
            $data = array('pesan' => $link, 'outlet' => $user->toArray());
            Mail::to($user->email)->send(new \App\Mail\MailActivationOutlet($data));
        }
        return redirect('/login_outlet')->with('status', 'Terimakasih telah melakukan registrasi. Email aktivasi akun telah dikirimkan ke email anda. Silakan klik link aktivasi email untuk mengaktifkan akun anda. Jika anda tidak mendapatkan email, silakan cek folder spam pada email anda.');
    }

    //Register Apoteker
    public function register_apoteker()
    {
        return view('frontend.register_apoteker');
    }

    public function register_apoteker_post(Request $request)
    {
        $apoteker = new MasterApoteker;
        $apoteker->setDynamicConnection();
        $apoteker->nama = $request->nama;
        $apoteker->email = $request->email;
        $apoteker->activated = 0;
        $apoteker->password = bcrypt($request->password);

        $validator = $apoteker->validator_apoteker();
        if ($validator->fails()) {
            return view('frontend.register_apoteker')->withErrors($validator);
        } else {
            $token = $this->getToken();
            $apoteker->remember_token = $token;
            $apoteker->created_at = date('Y-m-d H:i:s');
            $apoteker->save();
            $link = route('apoteker.activate', $token);
            $data = array('pesan' => $link, 'apoteker' => $apoteker->toArray());
            Mail::to($apoteker->email)->send(new \App\Mail\MailActivationApoteker($data));
        }
        return redirect('/login_apoteker')->with('status', 'Terimakasih telah melakukan registrasi. Email aktivasi akun telah dikirimkan ke email anda. Silakan klik link aktivasi email untuk mengaktifkan akun anda. Jika anda tidak mendapatkan email, silakan cek folder spam pada email anda.');
    }

    public function activateApoteker($token)
    {
        $activation = MasterApoteker::on($this->getConnectionName())->where('remember_token', $token)->first();
        if ($activation === null) {
            return null;
        }

        $user = MasterApoteker::on($this->getConnectionName())->find($activation->id);
        $user->activated = true;

        if ($user->save()) {
            return redirect('/login_apoteker')->with('status', 'Akun anda berhasil diaktivasi. Silakan gunakan email dan password anda untuk melakukan login.');
        }
        abort(404);
    }

    public function activateOutlet($token)
    {
        $activation = User::on($this->getConnectionName())->where('remember_token', $token)->first();
        if ($activation === null) {
            return null;
        }

        $user = User::on($this->getConnectionName())->find($activation->id);
        $user->activated = true;

        if ($user->save()) {
            return redirect('/login_outlet')->with('status', 'Akun anda berhasil diaktivasi. Silakan gunakan email dan password anda untuk melakukan login.');
        }
        abort(404);
    }

    public function activateDokter($token)
    {
        $activation = MasterDokter::on($this->getConnectionName())->where('remember_token', $token)->first();
        if ($activation === null) {
            return null;
        }

        $user = MasterDokter::on($this->getConnectionName())->find($activation->id);
        $user->activated = true;

        if ($user->save()) {
            return redirect('/login_dokter')->with('status', 'Akun anda berhasil diaktivasi. Silakan gunakan email dan password anda untuk melakukan login.');
        }
        abort(404);
    }

    public function activatePasien($token)
    {
        $activation = MasterPasien::on($this->getConnectionName())->where('remember_token', $token)->first();
        if ($activation === null) {
            return null;
        }

        $user = MasterPasien::on($this->getConnectionName())->find($activation->id);
        $user->activated = true;

        if ($user->save()) {
            return redirect('/login_pasien')->with('status', 'Akun anda berhasil diaktivasi. Silakan gunakan email dan password anda untuk melakukan login.');
        }
        abort(404);
    }
}
