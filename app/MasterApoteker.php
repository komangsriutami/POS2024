<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Validator;
use Auth;

class MasterApoteker extends Authenticatable
{
    use Notifiable;
    protected $guard = 'apoteker';
    /*
        Model   : Untuk Master Apoteker
        Author  : Sri U.
        Date    : 25/11/2019
    */

    protected $table = 'tb_m_apoteker';
    public $primaryKey = 'id';
    protected $fillable = [
        'id_group_apotek',
        'nostra',
        'nama',
        'tempat_lahir',
        'tgl_lahir',
        'id_jenis_kelamin',
        'alamat',
        'id_kewarganegaraan',
        'id_agama',
        'id_gol_darah',
        'telepon',
        'email',
        'activated',
        'remember_token'
    ];

    public function validate()
    {
        return Validator::make((array)$this->attributes, [
            'id_group_apotek' => 'required',
            'nostra' => 'required',
            'nama' => 'required|max:255',
            'tempat_lahir' => 'required',
            'tgl_lahir' => 'required',
            'id_jenis_kelamin' => 'required',
            'alamat' => 'required',
            'id_kewarganegaraan' => 'required',
            'id_agama' => 'required',
            'id_gol_darah' => 'required',
            'telepon' => 'required',
            'email' => 'required',
        ]);
    }

    public function validator_apoteker()
    {
        return Validator::make((array)$this->attributes, [
            'nama' => 'required|max:255',
            'email' => 'required|string|email|max:255|unique:tb_m_apoteker',
            'password' => 'required|string|min:6',
        ]);
    }

    public function validate_invite()
    {
        return Validator::make((array)$this->attributes, [
            'nama' => 'required',
            'email' => 'required|email',
        ]);
    }

    public function validate_confirm_apoteker()
    {
        return Validator::make((array)$this->attributes, [
            'id_group_apotek' => 'required',
            'nostra' => 'required',
            'nama' => 'required|max:255',
            'tempat_lahir' => 'required',
            'tgl_lahir' => 'required',
            'id_jenis_kelamin' => 'required',
            'alamat' => 'required',
            'id_kewarganegaraan' => 'required',
            'id_agama' => 'required',
            'id_gol_darah' => 'required',
            'telepon' => 'required',
            'email' => 'required',
        ]);
    }

    public function save_plus()
    {
        $this->tgl_lahir = date('Y-m-d', strtotime($this->tgl_lahir));
        $this->created_by = Auth::user()->id;
        $this->created_at = date('Y-m-d H:i:s');
        $this->save();
    }

    public function save_edit()
    {
        $this->tgl_lahir = date('Y-m-d', strtotime($this->tgl_lahir));
        $this->updated_by = Auth::user()->id;
        $this->updated_at = date('Y-m-d H:i:s');
        $this->save();
    }

    public function jenis_kelamin()
    {
        return $this->hasOne('App\MasterJenisKelamin', 'id', 'id_jenis_kelamin');
    }

    public function kewarganegaraan()
    {
        return $this->hasOne('App\MasterKewarganegaraan', 'id', 'id_kewarganegaraan');
    }

    public function agama()
    {
        return $this->hasOne('App\MasterAgama', 'id', 'id_agama');
    }

    public function golongan_darah()
    {
        return $this->hasOne('App\MasterGolonganDarah', 'id', 'id_gol_darah');
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
}
