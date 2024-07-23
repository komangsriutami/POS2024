<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use App\Traits\DynamicConnectionTrait;

class JurnalUmumDetail extends Model
{
    use DynamicConnectionTrait;
    protected $table = 'tb_jurnal_umum_detail';
    public $primaryKey = 'id';
    protected $fillable = ['id_kode_akun', 'deskripsi', 'debet', 'kredit'];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
    		'id_kode_akun' => 'required',
            'deskripsi' => 'required|max:255',
            'debet' => 'number',
            'kredit' => 'number'
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



    /*
        =======================================================================================
        For     : untuk mengecek duplicate data saat import
        Author  : Citra
        Date    : 22/09/2021
        =======================================================================================
    */
    public function checkExistImport($notrx,$tgltrx,$kodeakun,$kredit,$debit)
    {
        return $this->join("tb_jurnal_umum as j","j.id",'=',$this->table.".id_jurnal")
            ->join("tb_m_kode_akun as a","a.id",'=',$this->table.".id_kode_akun")
            ->whereRaw("j.no_transaksi = '".$notrx."'")
            ->whereRaw("j.tgl_transaksi = '".$tgltrx."'")
            ->whereRaw("a.kode = '".$kodeakun."'")
            ->where(function($subquery) use ($debit,$kredit){
                if($debit != ""){ $subquery->whereRaw("debit = '".$debit."'"); }
                else if($kredit != ""){ $subquery->whereRaw("kredit = '".$kredit."'"); }
            })
            ->get();
    }




    public function kode_akun(){
        return $this->hasOne('App\MasterKodeAkun', 'id', 'id_kode_akun');
    }

    public function jenis_transaksi(){
        return $this->hasOne('App\MasterJenisTransaksi', 'id', 'id_jenis_transaksi');
    }

    public function jurnal_umum(){
        return $this->hasOne('App\JurnalUmum', 'id', 'id_jurnal');
    }
}
