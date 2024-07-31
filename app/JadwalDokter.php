<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Validator;
use Auth;

class JadwalDokter extends Model
{
    use SoftDeletes;

    protected $table = 'tb_jadwal_dokter';
    public $primaryKey = 'id';
    protected $fillable = ['id_dokter', 'tgl', 'id_sesi', 'start', 'end', 'book_max', 'book_count'];
    protected $dates = ['deleted_at'];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'id_dokter' => 'required|max:255',
            'tgl' => 'required',
            'id_sesi' => 'required',
            'book_max' => 'required',
        ]);
    }

    public function save_plus(){
        if (session('user_roles.0.id')==7) {
            $this->created_by = Auth::guard('dokter')->user()->id;
        }else {
            $this->created_by = Auth::user()->id;
        }
        $this->save();
    }

    public function save_edit(){
        if (session('user_roles.0.id')==7) {
            $this->created_by = Auth::guard('dokter')->user()->id;
        }else {
            $this->updated_by = Auth::user()->id;
        }
        $this->save();
    }

    public function dokter(){
        return $this->hasOne('App\MasterDokter', 'id', 'id_dokter');
    }

    public function sesi(){
        return $this->hasOne('App\SesiJadwalDokter', 'id', 'id_sesi');
    }
}
