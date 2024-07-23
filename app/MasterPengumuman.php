<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Validator;
use App\Traits\DynamicConnectionTrait;

class MasterPengumuman extends Model
{
    use HasFactory;
    use DynamicConnectionTrait;

    protected $table = 'tb_pengumuman';
    public $primaryKey = 'id';
    public  $timestamps = false;
    protected $fillable = ['judul', 'isi', 'id_role_penerima', 'id_asal_pengumuman', 'tanggal_mulai', 'tanggal_selesai', 'file'];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'judul' => 'required',
            'isi' => 'required',
            'id_role_penerima' => 'required',
            'id_asal_pengumuman' => 'required',
            'tanggal_mulai' => 'required',
            'tanggal_selesai' => 'required'
        ]);
    }

    public function created_oleh(){
        return $this->hasOne('App\Users', 'id', 'created_by');
    }

    public function updated_oleh(){
        return $this->hasOne('App\Users', 'id', 'updated_by');
    }
}
