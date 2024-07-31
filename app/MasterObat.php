<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;
use App\Events\ObatCreate;
class MasterObat extends Model
{
    /* 
		Model 	: Untuk Master Obat 
		Author 	: Sri U.
		Date 	: 25/02/2020
	*/
		
	protected $table = 'tb_m_obat';
    public $primaryKey = 'id';
    protected $fillable = ['barcode',
    						'nama',
    						'id_produsen',
    						'id_golongan_obat',
                            'id_satuan',
                            'id_penandaan_obat',
    						'isi_tab',
    						'isi_strip',
    						'untung_jual',
    						'untung_klinik',
    						'untung_dokter',
    						'harga_beli',
    						'harga_jual',
    						'rak',
                            'zat_aktif',
                            'bentuk_kekuatan_sediaan'
    						];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
    		'barcode' => 'required',
    		'nama' => 'required',
    		'id_produsen' => 'required',
    		'id_golongan_obat' => 'required',
            'id_satuan' => 'required',
            'id_penandaan_obat' => 'required',
            'isi_tab' => 'required',
            'isi_strip' => 'required',
            'untung_jual' => 'required',
            'untung_klinik' => 'required',
            'untung_dokter' => 'required',
            'harga_beli' => 'required',
            'harga_jual' => 'required',
            // 'zat_aktif' => 'required',
            // 'bentuk_kekuatan_sediaan' => 'required',
        ]);
    }

    public function save_plus(){
        $this->created_at = date('Y-m-d H:i:s');
        $this->created_by = Auth::user()->id;
        $this->save();

        #ObatCreate::dispatch($this);
    }

    public function save_edit(){
        $this->updated_at = date('Y-m-d H:i:s');
        $this->updated_by = Auth::user()->id;
        $this->save();
    }

    public function diskon(){
        return $this->hasMany('App\Diskon', 'id_obat', 'id');
    }

    public function produsen(){
        return $this->hasOne('App\MasterProdusen', 'id', 'id_produsen');
    }

    public function golongan_obat(){
        return $this->hasOne('App\MasterGolonganObat', 'id', 'id_golongan_obat');
    }

    public function satuan(){
        return $this->hasOne('App\MasterSatuan', 'id', 'id_satuan');
    }

    public function created_oleh(){
        return $this->hasOne('App\Users', 'id', 'created_by');
    }

    public function updated_oleh(){
        return $this->hasOne('App\Users', 'id', 'updated_by');
    }

    public function penandaan_obat(){
        return $this->hasOne('App\MasterPenandaanObat', 'id', 'id_penandaan_obat');
    }
}
