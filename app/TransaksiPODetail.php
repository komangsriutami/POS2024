<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;
class TransaksiPODetail extends Model
{
    // ini tabel nota detail po
    protected $table = 'tb_detail_nota_po';
    public $primaryKey = 'id';
    protected $fillable = ['id_nota',
    						'id_obat',
    						'harga_jual',
    						'jumlah'
    						];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'id_nota' => 'required',
            'id_obat' => 'required',
            'harga_jual' => 'required',
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

    public function obat(){
        return $this->hasOne('App\MasterObat', 'id', 'id_obat');
    }

    public function nota(){
        return $this->hasOne('App\TransaksiPO', 'id', 'id_nota');
    }

    public function created_oleh(){
        return $this->hasOne('App\User', 'id', 'created_by');
    }

    public function updated_oleh(){
        return $this->hasOne('App\User', 'id', 'updated_by');
    }
}
