<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;
class TransaksiTODetail extends Model
{
    // ini tabel nota detail penjualan
    //protected $table = 'tb_detail_nota_transfer_outlet';
    public $primaryKey = 'id';
    protected $fillable = ['id_nota',
    						'id_obat',
    						'harga_outlet',
    						'jumlah',
                            'id_histori_stok',
                            'id_histori_stok_detail'
    						];

    public function __construct()
    {
        if(session('id_tahun_active') == date('Y')) {
            $this->setTable('tb_detail_nota_transfer_outlet');
        } else {
            $this->setTable('tb_detail_nota_transfer_outlet_histori');
        }
    }

    public function setTable($tableName)
    {
        $this->table = $tableName;
    }

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'id_nota' => 'required',
            'id_obat' => 'required',
            'harga_outlet' => 'required',
            'jumlah' => 'required',                                                                                  
        ]);
    }

    public function save_plus(){
        $this->created_by = Auth::user()->id;
        $this->save();
    }

    public function save_edit(){
        $this->updated_by = Auth::user()->id;
        $this->save();
    }

    public function obat(){
        return $this->hasOne('App\MasterObat', 'id', 'id_obat');
    }

    public function nota(){
        return $this->hasOne('App\TransaksiTO', 'id', 'id_nota');
    }

    public function created_oleh(){
        return $this->hasOne('App\User', 'id', 'created_by');
    }

    public function updated_oleh(){
        return $this->hasOne('App\User', 'id', 'updated_by');
    }

    public function konfirm_oleh(){
        return $this->hasOne('App\User', 'id', 'konfirm_by');
    }
}
