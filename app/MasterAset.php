<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Validator;
use Auth;
use App\Traits\DynamicConnectionTrait;
class MasterAset extends Model
{
    use DynamicConnectionTrait;
    protected $table = 'tb_m_aset';
    public $primaryKey = 'id';
    protected $fillable = ['id', 'nama', 'kode_aset', 'id_jenis_aset'];
    protected $guard = 'apoteker';
    /*
        Model   : Untuk Master Aset
        Author  : Agus Yudi.
        Date    : 13/09/2021
    */

    public function validate()
    {
        return Validator::make((array)$this->attributes, [
            'nama' => 'required|max:255',
            'id_jenis_aset' => 'required',
        ]);
    }

    public function save_plus()
    {
        $this->created_by = Auth::user()->id;
        $this->created_at = date('Y-m-d H:i:s');
        $this->save();
    }

    public function save_edit()
    {
        $this->updated_by = Auth::user()->id;
        $this->updated_at = date('Y-m-d H:i:s');
        $this->save();
    }

    public function created_oleh()
    {
        return $this->hasOne('App\Users', 'id', 'created_by');
    }

    public function updated_oleh()
    {
        return $this->hasOne('App\Users', 'id', 'updated_by');
    }
}
