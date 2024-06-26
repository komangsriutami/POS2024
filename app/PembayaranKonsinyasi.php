<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;
class PembayaranKonsinyasi extends Model
{
    // ini tabel pasien
    protected $table = 'tb_pembayaran_konsinyasi';
    public $primaryKey = 'id';
    protected $fillable = ['id_detail_nota',
    						'jumlah_bayar',
    						'tgl_bayar',
                            'id_kartu_debet_credit',
                            'debet',
                            'biaya_admin',
                            'cash',
                            'total_bayar'
    						];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
    		'id_nota' => 'required',
    		'jumlah_bayar' => 'required',
            'tgl_bayar' => 'required',
            'total_bayar' => 'required',
        ]);
    }

    public function save_plus(){
    	$this->created_at = date('Y-m-d H:i:s');
        $this->created_by = Auth::user()->id;
        $this->tgl_bayar = date('Y-m-d', strtotime($this->tgl_bayar ));
        $this->save();
    }

    public function save_edit(){
    	$this->updated_at = date('Y-m-d H:i:s');
        $this->updated_by = Auth::user()->id;
        $this->tgl_bayar = date('Y-m-d', strtotime($this->tgl_bayar ));
        $this->save();
    }
}
