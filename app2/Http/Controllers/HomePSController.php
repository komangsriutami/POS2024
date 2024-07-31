<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

use App\AppointmentPasien;
use App\MasterPasien;
use App\MasterJenisKelamin;
use App\MasterKewarganegaraan;
use App\MasterAgama;
use App\MasterGolonganDarah;
Use Carbon\Carbon;
use App\Traits\DynamicConnectionTrait;

class HomePSController extends Controller
{
    use DynamicConnectionTrait;
    public function __construct()
    {
        $this->middleware('auth:pasien');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $dataAnggota = MasterPasien::on($this->getConnectionName())->select('tb_m_pasien.id')->orderBy("tb_m_pasien.id")->where('id_reference',session('id'))->pluck('id');
        $anggotaKeluargas = MasterPasien::orderBy("id")->where('id_reference',session('id_reference'))->get();
        $appointmentPasiens = AppointmentPasien::orderBy("tb_appointment_pasien.id")->whereIn('id_reg_pasien',
            $dataAnggota
        )->join('tb_m_dokter', "tb_m_dokter.id", '=', 'tb_appointment_pasien.id_dokter')
        ->join('tb_m_pasien', 'tb_m_pasien.id', '=', 'tb_appointment_pasien.id_reg_pasien')
        ->join('tb_m_spesialis', 'tb_m_spesialis.id', '=', 'tb_m_dokter.spesialis')
        ->join('tb_jadwal_dokter', 'tb_jadwal_dokter.id', '=', 'tb_appointment_pasien.id_jadwal')
        ->join('tb_sesi_dokter', 'tb_sesi_dokter.id', '=', 'tb_jadwal_dokter.id_sesi')
        ->select('tb_appointment_pasien.*',
        'tb_m_dokter.nama AS nama_dokter',
        'tb_m_pasien.nama AS nama_pasien',
        'tb_m_spesialis.spesialis as spesialis',
        //'tb_appointment_pasien.created_at as created_at',
        'tb_m_dokter.img AS img_dokter',
        'tb_jadwal_dokter.tgl AS tgl',
        'tb_jadwal_dokter.end AS end',
        'tb_sesi_dokter.sesi AS sesi')
        ->get();

        $data2 =[
            'id' => session('id'),
            'email' => session('email'),
            'id_reference' => session('id_reference'),
            'kategori' => 2,
        ];
        $parameter[1] = Crypt::encrypt($data2);

        return view('frontend.v2.home_pasien')->with(compact('appointmentPasiens','anggotaKeluargas', 'parameter'));
    }

    // ISI DATA DIRI, EDIT DATA DAN TAMBAH DATA KELUARGA PASIEN

    public function InfoAkun(){

        $data1 =[
            'id' => session('id'),
            'email' => session('email'),
            'id_reference' => session('id_reference'),
            'kategori' => 1,
        ];
        $parameter[0] = Crypt::encrypt($data1);
        $data2 =[
            'id' => session('id'),
            'email' => session('email'),
            'id_reference' => session('id_reference'),
            'kategori' => 2,
        ];
        $parameter[1] = Crypt::encrypt($data2);

        $kewarganegaraans = MasterKewarganegaraan::orderBy("id")->get();

        $jeniskelamins = MasterJenisKelamin::orderBy("id")->get();

        $golongandarahs = MasterGolonganDarah::orderBy("id")->get();

        $anggotas = MasterPasien::on($this->getConnectionName())->where('id_reference', session('id'))
        ->where('tb_m_pasien.id', '!=' , session('id'))
        ->join("tb_m_kewarganegaraan", "tb_m_kewarganegaraan.id",'=',"tb_m_pasien.id_kewarganegaraan")
        ->select(
        "tb_m_pasien.id as id",
        'kewarganegaraan',
        'id_jenis_kelamin',
        'id_golongan_darah',
        // 'id_agama',
        'nik',
        'nama',
        'tempat_lahir',
        'tgl_lahir',
        'telepon',
        'alamat',
        'alergi_obat',
        'is_pernah_berobat',
        'is_bpjs',
        'no_bpjs')
        ->paginate(3);

        $index=0;
        $parameterAnggota=array();
        foreach($anggotas as $item){
            $dataAnggota1 =[
                'id' => $item->id,
                'email' => session('email'),
                'id_reference' => session('id_reference'),
                'kategori' => 1,
            ];
            $parameterAnggota[(string)$item->id][0] = Crypt::encrypt($dataAnggota1);
            $dataAnggota2 =[
                'id' => $item->id,
                'email' => session('email'),
                'id_reference' => session('id_reference'),
                'kategori' => 2,
            ];
            $parameterAnggota[(string)$item->id][1] = Crypt::encrypt($dataAnggota2);
        }

        if(!isset($parameterAnggota)) $parameterAnggota="";
        return view('frontend.v2.home_pasien.info_akun')->with(compact('kewarganegaraans', 'jeniskelamins', 'golongandarahs', 'anggotas', 'parameter','parameterAnggota'));
    }

    public function EditInfoAkun($parameter){
        $parameterDec = Crypt::decrypt($parameter);

        $id = $parameterDec['id'];
        $pilihan = $parameterDec['kategori'];

        $kewarganegaraans = MasterKewarganegaraan::orderBy("id")->get();
        $agamas = MasterAgama::orderBy("id")->get();
        $jeniskelamins = MasterJenisKelamin::orderBy("id")->get();
        $golongandarahs = MasterGolonganDarah::orderBy("id")->get();

        $anggotas = MasterPasien::on($this->getConnectionName())->where('tb_m_pasien.id', $id)
        ->leftJoin("tb_m_kewarganegaraan", "tb_m_kewarganegaraan.id",'=',"tb_m_pasien.id_kewarganegaraan")
        ->select(
        "tb_m_pasien.id as id",
        'id_kewarganegaraan',
        'id_jenis_kelamin',
        'id_golongan_darah',
        'id_agama',
        'pekerjaan',
        'nik',
        'nama',
        'tempat_lahir',
        'tgl_lahir',
        'telepon',
        'alamat',
        'alergi_obat',
        'is_pernah_berobat',
        'is_bpjs',
        'activated',
        'no_bpjs')->first();

        //dd($anggotas);

        return view('frontend.v2.home_pasien.isi_data_diri')->with(compact('kewarganegaraans', 'jeniskelamins', 'golongandarahs', 'agamas', 'anggotas','pilihan', 'parameter'));
    }

    public function EditAkun($parameter,Request $data){
        $parameterDec = Crypt::decrypt($parameter);
        $id = $parameterDec['id'];
        $user = MasterPasien::on($this->getConnectionName())->find($id);
        $user->fill($data->except('_token'));


        $validator = $user->validate_isidatadiri();
        $errorMessage = $validator->messages();
        if($validator->fails()){
            $pilihan = $parameterDec['kategori'];

            $kewarganegaraans = MasterKewarganegaraan::orderBy("id")->get();
            $agamas = MasterAgama::orderBy("id")->get();
            $jeniskelamins = MasterJenisKelamin::orderBy("id")->get();
            $golongandarahs = MasterGolonganDarah::orderBy("id")->get();

               $anggotas = MasterPasien::on($this->getConnectionName())->where('tb_m_pasien.id', $id)
        ->leftJoin("tb_m_kewarganegaraan", "tb_m_kewarganegaraan.id",'=',"tb_m_pasien.id_kewarganegaraan")
        ->select(
        "tb_m_pasien.id as id",
        'id_kewarganegaraan',
        'id_jenis_kelamin',
        'id_golongan_darah',
        'id_agama',
        'pekerjaan',
        'nik',
        'nama',
        'tempat_lahir',
        'tgl_lahir',
        'telepon',
        'alamat',
        'alergi_obat',
        'is_pernah_berobat',
        'is_bpjs',
        'activated',
        'no_bpjs')->first();

            //return redirect()->intended('/home_pasien/data_diri/'.$parameter)->withErrors(['username' => $errorMessage->first(),])->withInput($data->all());
            return  view('frontend.v2.home_pasien.isi_data_diri')->with(compact('kewarganegaraans', 'jeniskelamins', 'golongandarahs', 'agamas', 'anggotas','pilihan', 'parameter'))->withErrors($validator);
        }else if($data['is_bpjs']=="1" && (is_null($data['no_bpjs']) || $data['no_bpjs']=="")){
            return redirect()->intended('/home_pasien/data_diri/'.$parameter)->withErrors([
                'username' => "Nomor BPJS isi terlabih dahulu.",
            ])->withInput($data->all());
        }else{
            $user->no_rm = 'RM.001.001.011';
            $user->save();
            if(session("id") == $id){
                session(['nama' => $user['nama']]);
                session(['nik' => $user['nik']]);
                session(['nampekerjaana' => $user['pekerjaan']]);
                session(['tempat_lahir' => $user['tempat_lahir']]);
                session(['tgl_lahir' => $user['tgl_lahir']]);
                session(['alamat' => $user['alamat']]);
                session(['telepon' => $user['telepon']]);
                session(['alergi_obat' => $user['alergi_obat']]);

                $kewarganegaraan = MasterKewarganegaraan::on($this->getConnectionName())->where("id", $user['id_kewarganegaraan'])->orderBy("id")->first();
                session(['kewarganegaraan' => $kewarganegaraan->kewarganegaraan]);

                session(['id_kewarganegaraan' => $user['id_kewarganegaraan']]);
                session(['id_jenis_kelamin' => $user['id_jenis_kelamin']]);
                session(['id_golongan_darah' => $user['id_golongan_darah']]);
                session(['is_pernah_berobat' => $user['is_pernah_berobat']]);
                session(['is_bpjs' => $user['is_bpjs']]);
                session(['no_bpjs' => $user['no_bpjs']]);
                session(['activated' => $user['activated']]);
            }
            return redirect('home_pasien/info_akun');
        }
    }

    public function InfoLogin(){
        return view('frontend.v2.home_pasien.isi_data_login');
    }

    public function EditAkunLogin(Request $data){

        //return redirect()->intended('/info_akun')->withInput($data->all());
        //dd($data['id']);exit;
        if($data->password != "") $data->password = bcrypt($data->password);

        $user = MasterPasien::on($this->getConnectionName())->find(session('id'));

        $allData = new MasterPasien;
        $allData->setDynamicConnection();
        $allData->fill($data->only(
            ['password']
        ));

        $validator = $allData->validateLogin();
        $errorMessage = $validator->messages();

        if($validator->fails()){
            return redirect()->back()->withErrors([
                'username' => $errorMessage->first(),
            ])->withInput($data->all());
        }else if(strlen($data['password']) < 6){
            return redirect()->back()->withErrors([
                'username' => "Password minimal 6 karakter",
            ])->withInput($data->all());
        }else if($data['password'] != $data['password_confirm']){
            return redirect()->back()->withErrors([
                'username' => "Password dan confirm password tidak sama",
            ])->withInput($data->all());
        }else{
            //$user->email = $data['email'];
            $user->password = bcrypt($data['password']);
            $user->save();
            //session(['email' => $user['email']]);

            return redirect('home_pasien/info_akun');
        }
    }

    public function AnggotaKeluarga(){
        $kewarganegaraans = MasterKewarganegaraan::orderBy("id")->get();
        $jeniskelamins = MasterJenisKelamin::orderBy("id")->get();
        $golongandarahs = MasterGolonganDarah::orderBy("id")->get();

        return view('frontend.v2.home_pasien.add_anggota')->with(compact('kewarganegaraans', 'jeniskelamins', 'golongandarahs'));
    }

    public function AddAnggotaKeluarga(Request $data){
        $user = new MasterPasien;
        $user->setDynamicConnection();
        $user->fill($data->except('_token'));

        $validator = $user->validate_isidatadiri();
        $errorMessage = $validator->messages();

        if($validator->fails()){
            return redirect()->intended('/home_pasien/anggota_keluarga/')->withErrors([
                'username' => $errorMessage->first(),
            ])->withInput($data->all());
        }else if($data['is_bpjs']=="1" && (is_null($data['no_bpjs']) || $data['no_bpjs']=="")){
            return redirect()->intended('/home_pasien/anggota_keluarga/')->withErrors([
                'username' => "Nomor BPJS isi terlabih dahulu.",
            ])->withInput($data->all());
        }else{
            $user->activated = 1;
            $user->id_reference = session('id');
            $user->save();
            return redirect()->route('home_pasien/info_akun');
        }
    }
}
