<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Validator;
use DB;
use Auth;
use App\Traits\DynamicConnectionTrait;

class User extends Authenticatable
{
    use Notifiable;
    use DynamicConnectionTrait;   
    /* 
        Model   : Untuk RBAC Data User
        Author  : Sri U.
        Date    : 17/2/2020
    */
  
    protected $table = 'users';
    public $primaryKey = 'id';

    protected $fillable = [
        'username', 
        'password',
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
        'id_group_apotek',
        'id_jabatan',
        'id_posisi',
        'id_status_karyawan',
        'nostra',
        'nosipa',
        'file'
    ];

    public function validate(){
        return Validator::make((array)$this->attributes, [
            'nama' => 'required',
            'password' => 'required',
            'username' => 'required',
            'tempat_lahir' => 'required',
            'tgl_lahir' => 'required',
            'id_jenis_kelamin' => 'required',
            'alamat' => 'required',
            'id_kewarganegaraan' => 'required',
            'id_agama' => 'required',
            'id_gol_darah' => 'required',
            'telepon' => 'required',
            'email' => 'required',
            'id_group_apotek' => 'required'
        ]);
    }

    /*
        =======================================================================================
        For     : Validasi invite user
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

    public function user_roles(){
        return $this->hasMany('App\RbacUserRole', 'id_user', 'id');
    }

    public function user_apoteks(){
        return $this->hasMany('App\RbacUserApotek', 'id_user', 'id');
    }

    public function messages()
    {
      return $this->hasMany('App\Message', 'id_user', 'id');
    }

    public function save_plus($val, $user_roles){
        if($val==1)
        {
            $this->created_by = Auth::id();
            $id_role = $this->save();
        }else{
            $this->updated_by = Auth::id();
            $id_role = $this->save();
        }

        $array_id_user_roles = array();
        if(!empty($user_roles)) {
            foreach ($user_roles as $key) {
                $obj = RbacUserRole::on($this->getConnectionName())->where('id_role', $key['id_role'])->where('id_user', $this->id)->first();

                if(empty($obj)) {
                    $obj = new RbacUserRole;
                    $obj->setDynamicConnection();
                    $obj->id_user = $this->id;
                    $obj->id_role = $key['id_role'];
                    $obj->created_by = Auth::id();
                    $obj->updated_by = Auth::id();
                    $obj->is_deleted = 0;

                    $obj->save();
                } else {
                    RbacUserRole::on($this->getConnectionName())->where('id_role', $key['id_role'])
                            ->where('id_user', $this->id)
                            ->update(['updated_by' => Auth::id()]);
                }
             
                $array_id_user_roles[] = $obj->id_role;
            }
        }
        if(!empty($array_id_user_roles)){
            DB::connection($this->getConnection())->statement("DELETE FROM rbac_user_role
                            WHERE id_user=".$this->id." AND 
                                    id_role NOT IN(".implode(',', $array_id_user_roles).")");
        }else{
            DB::connection($this->getConnection())->statement("DELETE FROM rbac_user_role 
                            WHERE id_user=".$this->id);

        }
    }

    public function save_plus_apotek($val, $user_apoteks){
        if($val==1)
        {
            $this->created_by = Auth::id();
            $id = $this->save();
        }else{
            $this->updated_by = Auth::id();
            $id = $this->save();
        }

        $array_id_user_apoteks = array();
        if(!empty($user_apoteks)) {
            foreach ($user_apoteks as $key) {
                $obj = RbacUserApotek::on($this->getConnectionName())->where('id_apotek', $key['id_apotek'])->where('id_user', $this->id)->first();

                if(empty($obj)) {
                    $obj = new RbacUserApotek;
                    $obj->setDynamicConnection();
                    $obj->id_user = $this->id;
                    $obj->id_apotek = $key['id_apotek'];
                    $obj->created_by = Auth::id();
                    $obj->updated_by = Auth::id();
                    $obj->is_deleted = 0;

                    $obj->save();
                } else {
                    RbacUserApotek::on($this->getConnectionName())->where('id_apotek', $key['id_apotek'])
                            ->where('id_user', $this->id)
                            ->update(['updated_by' => Auth::id()]);
                }
             
                $array_id_user_apoteks[] = $obj->id_apotek;
            }
        }
        if(!empty($array_id_user_apoteks)){
            DB::connection($this->getConnection())->statement("DELETE FROM rbac_user_apotek
                            WHERE id_user=".$this->id." AND 
                                    id_apotek NOT IN(".implode(',', $array_id_user_apoteks).")");
        }else{
            DB::connection($this->getConnection())->statement("DELETE FROM rbac_user_apotek 
                            WHERE id_user=".$this->id);
        }
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


    public function jenis_kelamin(){
        return $this->hasOne('App\MasterJenisKelamin', 'id', 'id_jenis_kelamin');
    }

    public function kewarganegaraan(){
        return $this->hasOne('App\MasterKewarganegaraan', 'id', 'id_kewarganegaraan');
    }

    public function agama(){
        return $this->hasOne('App\MasterAgama', 'id', 'id_agama');
    }

    public function golongan_darah(){
        return $this->hasOne('App\MasterGolonganDarah', 'id', 'id_gol_darah');
    }

    public function group_apotek(){
        return $this->hasOne('App\MasterGroupApotek', 'id', 'id_group_apotek');
    }

    public function created_oleh(){
        return $this->hasOne('App\Users', 'id', 'created_by');
    }

    public function updated_oleh(){
        return $this->hasOne('App\Users', 'id', 'updated_by');
    }

    public function jabatan(){
        return $this->hasOne('App\MasterJabatan', 'id', 'id_jabatan');
    }

    public function posisi(){
        return $this->hasOne('App\MasterPosisi', 'id', 'id_posisi');
    }

    public function status(){
        return $this->hasOne('App\MasterStatusKaryawan', 'id', 'id_status_karyawan');
    }
}
