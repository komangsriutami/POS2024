<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Validator;
use DB;
use Auth;
class MasterPajak extends Model
{
    use HasFactory;

    protected $table = 'tb_m_pajak';
    public $primaryKey = 'id';
    public  $timestamps = false;
    protected $fillable = ['nama', 'persentase_efektif', 'id_akun_pajak_penjualan', 'id_akun_pajak_pembelian'];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'nama' => 'required|max:255',
            'persentase_efektif' => 'required',
            'id_akun_pajak_penjualan' => 'required',
            'id_akun_pajak_pembelian' => 'required',
        ]);
    }

    public function save_plus(){
        $this->created_by = Auth::user()->id;
        $this->created_at = date('Y-m-d H:i:s');
        $this->save();
    }

    public function save_edit(){
        $this->updated_by = Auth::user()->id;
        $this->updated_at = date('Y-m-d H:i:s');
        $this->save();
    }

    public function created_oleh(){
        return $this->hasOne('App\Users', 'id', 'created_by');
    }

    public function updated_oleh(){
        return $this->hasOne('App\Users', 'id', 'updated_by');
    }

    public function akun_penjualan(){
        return $this->hasOne('App\MasterKodeAkun', 'id', 'id_akun_pajak_penjualan');
    }

    public function akun_pembelian(){
        return $this->hasOne('App\MasterKodeAkun', 'id', 'id_akun_pajak_pembelian');
    }
}
