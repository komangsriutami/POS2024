<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;
use Illuminate\Support\Facades\Hash;

class MasterInvestor extends Model
{
    /* 
        Model   : Untuk Master Investor
        Author  : Govi.
        Date    : 11/09/2021
    */

    use SoftDeletes;
    public $primaryKey = 'id';
    protected $table = 'tb_m_investor';
    protected $fillable = [
        'nik',
        'nama',
        'tgl_lahir',
        'tempat_lahir',
        'id_jenis_kelamin',
        'id_agama',
        'id_kewarganegaraan',
        'no_telp',
        'email',
        'npwp',
        'alamat',
        'created_at',
        'updated_at',
        'deleted_at',
        'created_by',
        'updated_by',
        'deleted_by',
        'is_delete',
    ];

    public function validate(){
        return Validator::make((array)$this->attributes, [
            'nik' => 'required|numeric|digits:16',
            'nama' => 'required|min:3|max:255',
            'tgl_lahir' => 'required|date',
            'tempat_lahir' => 'required|max:255',
            'id_jenis_kelamin' => 'required',
            'id_agama' => 'required',
            'id_kewarganegaraan' => 'required',
            'no_telp' => 'required|numeric|digits_between:8,14',
            'email' => 'required|email',
            'npwp' => 'required|numeric|digits:15',
            'alamat' => 'required|min:3',
        ]);
    }

    public function save_plus(){
        $this->created_by = Auth::user()->id;
        $this->save();

        $user = new User;
        $user->nama = $this->nama;
        $user->password = Hash::make('investor2021');
        $user->username = 'generate';
        $user->tempat_lahir = $this->tempat_lahir;
        $user->tgl_lahir = $this->tgl_lahir;
        $user->id_jenis_kelamin = $this->id_jenis_kelamin;
        $user->id_kewarganegaraan = $this->id_kewarganegaraan;
        $user->id_agama = $this->id_agama;
        $user->id_gol_darah = 1;
        $user->telepon = $this->no_telp;
        $user->alamat = $this->alamat;
        $user->email = $this->email;
        $user->id_group_apotek = 1;
        $user->is_admin = 1;
        $user->save();

        $this->id_user = $user->id;
        $this->save();
    }

    public function save_edit(){
        $this->updated_by = Auth::user()->id;
        $this->save();

        $user = User::find($this->id_user);
        $user->nama = $this->nama;
        $user->tempat_lahir = $this->tempat_lahir;
        $user->tgl_lahir = $this->tgl_lahir;
        $user->id_jenis_kelamin = $this->id_jenis_kelamin;
        $user->id_kewarganegaraan = $this->id_kewarganegaraan;
        $user->id_agama = $this->id_agama;
        $user->telepon = $this->no_telp;
        $user->alamat = $this->alamat;
        $user->email = $this->email;
        $user->save();
    }

    public function save_delete(){
        $this->deleted_by = Auth::user()->id;
        $this->is_deleted = 1;
        $this->save();

        $user = User::find($this->id_user);
        $user->is_deleted = 1;
        $user->save();
    }

    public function created_oleh()
    {
        return $this->hasOne('App\Users', 'id', 'created_by');
    }

    public function updated_oleh()
    {
        return $this->hasOne('App\Users', 'id', 'updated_by');
    }

    public function deleted_oleh()
    {
        return $this->hasOne('App\Users', 'id', 'deleted_by');
    }
}
