<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasterJenisTransaksi extends Model
{
    /* 
		Model 	: Untuk Master Jenis Transaksi
		Author 	: Sri U.
		Date 	: 24/02/2020
	*/
    protected $table = 'tb_m_jenis_transaksi';
    public $primaryKey = 'id';
    protected $fillable = ['nama','keterangan'];

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
}
