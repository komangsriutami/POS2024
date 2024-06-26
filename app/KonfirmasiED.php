<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;
class KonfirmasiED extends Model
{
    // ini tabel konfirmasi obat ed
    protected $table = 'tb_konfirmasi_ed';
    public $primaryKey = 'id';
    protected $fillable = ['id_detail_nota',
    						'id_jenis_penanganan',
    						'jumlah_ed',
    						'id_referensi'
    						];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
    		'id_detail_nota' => 'required',
    		'id_jenis_penanganan' => 'required',
            'jumlah_ed' => 'required',
            'id_referensi'
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
