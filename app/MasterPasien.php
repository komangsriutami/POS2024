<?php

namespace App;


use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class MasterPasien extends Authenticatable
{
    use Notifiable;
    protected $guard = 'pasien';

    /*
		Model 	: Menambahkan Field di Master Pasien
        Field   : 'is_pernah_berobat', 'adabpjs', 'alergi_obat'
		Author 	: Tangkas.
		Date 	: 16/06/2021
	*/

    protected $table = 'tb_m_pasien';
    public $primaryKey = 'id';
    protected $fillable = [
        'id_kewarganegaraan',
        'id_jenis_kelamin',
        'id_golongan_darah',
         'id_agama',
        'nik',
        'nama',
        'no_rm',
        'tempat_lahir',
        'tgl_lahir',
        'alamat',
        'pekerjaan',
        'telepon',
        'alergi_obat',
        'is_pernah_berobat',
        'is_bpjs',
        'no_bpjs',
        'email',
        'password',
        'activated',
        'remember_token'
    ];

    public function validate()
    {
        return Validator::make((array)$this->attributes, [
            'id_kewarganegaraan' => 'required',
            'id_jenis_kelamin' => 'required',
            'id_golongan_darah' => 'required',
            'id_agama' => 'required',
            'nik' => 'required',
            'nama' => 'required',
            // 'no_rm'  => 'required',
            'tempat_lahir' => 'required',
            'tgl_lahir' => 'required',
            'alamat' => 'required',
            'pekerjaan' => 'required',
            'telepon' => 'required|max:20',
            'alergi_obat' => 'required',
            'is_pernah_berobat' => 'required',
            'is_bpjs' => 'required'
        ]);
    }

    /*
        =======================================================================================
        For     : Validasi invite pasien
        Author  : Govi.
        Date    : 14/09/2021
        =======================================================================================
    */
    public function validate_invite()
    {
        return Validator::make((array)$this->attributes, [
            'nama' => 'required',
            'email' => 'required|email',
        ]);
    }

    public function validator_confirm_pasien()
    {
        return Validator::make((array)$this->attributes, [
            'id_kewarganegaraan' => 'required',
            'id_jenis_kelamin' => 'required',
            'id_golongan_darah' => 'required',
            'id_agama' => 'required',
            'nik' => 'required',
            'nama' => 'required',
            'tempat_lahir' => 'required',
            'tgl_lahir' => 'required',
            'alamat' => 'required',
            'pekerjaan' => 'required',
            'telepon' => 'required|max:20',
            'email' => 'required',
            'password' => 'required',
        ]);
    }

    public function validator_pasien()
    {
        return Validator::make((array)$this->attributes, [
            'nama' => 'required|max:255',
            'email' => 'required|string|email|max:255|unique:tb_m_pasien',
            'password' => 'required|string|min:6',
        ]);
    }

    /*public function validateLogin(){
        return Validator::make((array)$this->attributes, [
            'password'  => 'required',
        ],$messages = [
            'required' => ':attribute isi terlebih dahulu.',
        ],[
            'password'  => 'Password'
        ]);
    }*/

    public function save_plus(){
        $this->tgl_lahir = date('Y-m-d', strtotime($this->tgl_lahir));
        $this->created_by = Auth::user()->id;
        $this->created_at = date('Y-m-d H:i:s');
        $this->save();
    }

    public function save_edit(){
        $this->tgl_lahir = date('Y-m-d', strtotime($this->tgl_lahir));
        $this->updated_by = Auth::user()->id;
        $this->updated_at = date('Y-m-d H:i:s');
        $this->save();
    }

    //tambahan tangkas 16/06/2021
    public function jeniskelamins()
    {
        return $this->hasOne('App\MasterJenisKelamin', 'id', 'id_jenis_kelamin');
    }

    public function kewarganegaraans()
    {
        return $this->hasOne('App\MasterKewarganegaraan', 'id', 'id_kewarganegaraan');
    }

    public function agamas()
    {
        return $this->hasOne('App\MasterAgama', 'id', 'id_agama');
    }

    public function golongandarahs()
    {
        return $this->hasOne('App\MasterGolonganDarah', 'id', 'id_golongan_darah');
    }

    public function created_oleh()
    {
        return $this->hasOne('App\Users', 'id', 'created_by');
    }

    public function updated_oleh()
    {
        return $this->hasOne('App\Users', 'id', 'updated_by');
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function validate_isidatadiri(){
        return Validator::make((array)$this->attributes, [
            'id_kewarganegaraan' => 'required',
            'id_jenis_kelamin' => 'required',
            'id_golongan_darah' => 'required',
            'id_agama' => 'required',
            'nik' => 'required',
            //'no_rm'  => 'required',
            'tempat_lahir' => 'required',
            'tgl_lahir' => 'required',
            'alamat' => 'required',
            'pekerjaan' => 'required',
            'telepon' => 'required|max:20',
            'alergi_obat' => 'required',
            'is_pernah_berobat' => 'required',
            'is_bpjs' => 'required'
        ]);
    }
}
