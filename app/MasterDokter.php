<?php

namespace App;

// use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class MasterDokter extends Authenticatable
{
    use Notifiable;
    /*
        Model   : Untuk Master Apotek
        Author  : Surya Adiputra
        Date    : 3/04/2020
    */

    /*
        Model   : Menambahkan Field di Master Dokter
        Field   : 'id_apotek', 'spesialis', 'img'
        Author  : Tangkas.
        Date    : 16/06/2021
    */

    protected $table = 'tb_m_dokter';
    public $primaryKey = 'id';
    protected $fillable = [
        'id_group_apotek',
        'id_apotek',
        'nama',
        'spesialis',
        'sib',
        'alamat',
        'telepon',
        'fee',
        'img',
        'email',
        'password',
        'activated',
        'remember_token'
    ];

    public function validate()
    {
        return Validator::make((array)$this->attributes, [
            'id_group_apotek' => 'required',
            'spesialis' => 'required',
            'nama' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'sib' => 'required|max:255',
            'alamat' => 'required',
            'telepon' => 'required|max:25',
            'fee' => 'required',
            'img' => 'required'
        ]);
    }

    public function validate_invite()
    {
        return Validator::make((array)$this->attributes, [
            'nama' => 'required',
            'email' => 'required|email',
        ]);
    }

    public function validate_confirm_dokter()
    {
        return Validator::make((array)$this->attributes, [
            'id_group_apotek' => 'required',
            'id_apotek' => 'required',
            'spesialis' => 'required',
            'nama' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'sib' => 'required|max:255',
            'alamat' => 'required',
            'telepon' => 'required|max:25',
        ]);
    }

    public function validator_dokter()
    {
        return Validator::make((array)$this->attributes, [
            'nama' => 'required|max:255',
            'email' => 'required|string|email|max:255|unique:tb_m_dokter',
            'password' => 'required|string|min:6',
        ]);
    }

    //tangkas 16/06/2021

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

    //tambahan tangkas 16/06/2021
    public function group_apoteks()
    {
        return $this->hasOne('App\MasterGroupApotek', 'id', 'id_group_apotek');
    }

    public function apoteks()
    {
        return $this->hasOne('App\MasterApotek', 'id', 'id_apotek');
    }

    public function spesialiss()
    {
        return $this->hasOne('App\MasterSpesialis', 'id', 'spesialis');
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
