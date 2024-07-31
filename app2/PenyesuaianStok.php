<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;
use DB;
use App\Traits\DynamicConnectionTrait;

class PenyesuaianStok extends Model
{
    use DynamicConnectionTrait;
    protected $table = 'tb_penyesuaian_stok_obat';
    public $primaryKey = 'id';
    protected $fillable = ['id_obat',
                            'id_jenis_penyesuaian',
                            'stok_awal',
                            'stok_akhir',
                            'hb_ppn',
                            'alasan'
    						];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'id_obat' => 'required',
            'id_jenis_penyesuaian' => 'required',
            'stok_awal' => 'required',
            'stok_akhir' => 'required',
            'hb_ppn' => 'required',
            'alasan' => 'required',
        ]);
    }

    public function created_oleh(){
        return $this->hasOne('App\User', 'id', 'created_by');
    }

    public function updated_oleh(){
        return $this->hasOne('App\User', 'id', 'updated_by');
    }

    public function obat(){
        return $this->hasOne('App\MasterObat', 'id', 'id_obat');
    }
}
