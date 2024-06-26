<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;

class Biaya extends Model
{
    protected $table = 'tb_biaya';
    public $primaryKey = 'id';
    public  $timestamps = false;
    // protected $fillable = ['id_apotek', 'id_kasir_aktif', 'id_user', 'tgl', 'jam_datang', 'jam_pulang', 'jumlah_jam_kerja'];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'tipe_penerima' => 'required',
            'id_penerima' => 'required',
            'tgl_transaksi' => 'required'
        ]);
    }

    public function AkunBayar(){
        return $this->belongsTo('App\MasterKodeAkun', 'id_akun_bayar');
    }

    public function kode_akun_ppn_potong(){
        return $this->belongsTo('App\MasterKodeAkun', 'id_akun_ppn_potong');
    }

    public function supplier(){
        return $this->belongsTo('App\MasterSuplier', 'id_penerima');
    }

    public function user(){
        return $this->belongsTo('App\User', 'id_penerima');
    }

    public function member(){
        return $this->belongsTo('App\MasterMember', 'id_penerima');
    }

    public function carabayar(){
        return $this->belongsTo('App\MasterJenisPembayaran', 'id_cara_pembayaran');
    }

    public function detailbiaya(){
        return $this->hasMany('App\BiayaDetail', 'id_biaya', 'id')->whereNull('deleted_by');
    }

    public function filebuktibiaya(){
        return $this->hasMany('App\BiayaBukti', 'id_biaya', 'id')->whereNull('deleted_by');
    }

    public function userUpdate(){
        if(is_null($this->updated_by)){
            return $this->hasOne('App\User', 'id', 'created_by');
        } else {
            return $this->hasOne('App\User', 'id', 'updated_by');
        }
    }


    /*
        =======================================================================================
        For     : untuk mengecek duplicate data saat import
        Author  : Citra
        Date    : 09/10/2021
        =======================================================================================
    */
    /*public function checkExistImport($kode_akun_bayar, $bataspembayaran, $notrx, $tgltrx, $carabayar, $idsupplier)
    {
        return $this->whereRaw("b.no_biaya = '".$notrx."'")
            ->whereRaw("b.tgl_transaksi = '".$tgltrx."'")
            ->whereRaw("b.id_supplier = '".$idsupplier."'")
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
    }*/
}
