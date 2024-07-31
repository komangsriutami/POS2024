<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use App\Traits\DynamicConnectionTrait;

class BiayaDetail extends Model
{
    use DynamicConnectionTrait;
    protected $table = 'tb_biaya_detail';
    public $primaryKey = 'id';
    public  $timestamps = false;

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'id_biaya' => 'required',
            'id_akun' => 'required',
            'deskripsi' => 'required|max:255',
            'biaya' => 'number'
        ]);
    }

    public function kode_akun(){
        return $this->belongsTo('App\MasterKodeAkun', 'id_kode_akun');
    }

    public function kode_akun_pajak(){
        return $this->belongsTo('App\MasterKodeAkun', 'id_akun_pajak');
    }

    /*
        =======================================================================================
        For     : untuk mengecek duplicate data saat import
        Author  : Citra
        Date    : 09/10/2021
        =======================================================================================
    */
    public function checkExistImport($kode_akun_bayar, $bataspembayaran, $notrx, $tgltrx, $carabayar, $idsupplier, $kodeakun, $biaya)
    {
        return $this->join("tb_biaya as b","b.id",'=',$this->table.".id_biaya")
            ->join("tb_m_kode_akun as a","a.id",'=',$this->table.".id_kode_akun")
            ->whereRaw("b.no_biaya = '".$notrx."'")
            ->whereRaw("b.tgl_transaksi = '".$tgltrx."'")
            ->whereRaw("b.id_supplier = '".$idsupplier."'")
            ->whereRaw("a.kode = '".$kodeakun."'")
            ->whereRaw("biaya = '".$biaya."'")
            ->where(function($subquery) use ($kode_akun_bayar,$bataspembayaran,$carabayar,$idsupplier){
                if($kode_akun_bayar != ""){ 
                    $subquery->whereRaw("b.id_akun_bayar = '".$kode_akun_bayar."'"); 
                }
                else if($kode_akun_bayar == ""){ 
                    $subquery->whereRaw("tgl_batas_pembayaran = '".$bataspembayaran."'"); 
                } 

                if($carabayar != ""){ 
                    $subquery->whereRaw("id_cara_pembayaran = '".$carabayar."'"); 
                }
            })
            ->get();
    }
}
