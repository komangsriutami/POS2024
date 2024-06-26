<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;

class ReturPembelian extends Model
{
    //retur pembelian obat
    /* 
		Model 	: Untuk Retur pembelian
		Author 	: Sri U.
		Date 	: 21/06/2020
	*/
		
	protected $table = 'tb_return_pembelian_obat';
    public $primaryKey = 'id';
    protected $fillable = ['id_detail_nota', 'id_alasan_retur', 'jumlah', 'alasan_lain'];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
    		'id_detail_nota' => 'required',
            'id_alasan_retur' => 'required',
            'jumlah' => 'required',
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

    public function alasan(){
        return $this->hasOne('App\MasterAlasanReturPembelian', 'id', 'id_alasan_retur');
    }

    public function created_oleh(){
        return $this->hasOne('App\User', 'id', 'created_by');
    }

    public function updated_oleh(){
        return $this->hasOne('App\User', 'id', 'updated_by');
    }

    public function aprove_oleh(){
        return $this->hasOne('App\User', 'id', 'approved_by');
    }
}
