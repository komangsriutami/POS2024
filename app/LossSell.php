<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Validator;
class LossSell extends Model
{
    use HasFactory;

    protected $table = 'tb_loss_sell';
    public $primaryKey = 'id';
    public  $timestamps = false;
    protected $fillable = ['id_apotek', 'tanggal', 'id_obat', 'jumlah', 'keterangan', 'nama_obat', 'harga', 'total'];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'id_apotek' => 'required',
            'tanggal' => 'required',
            'id_obat' => 'required',
            'jumlah' => 'required',
            'keterangan' => 'required'
        ]);
    }

    public function apotek(){
        return $this->belongsTo('App\MasterApotek', 'id_apotek');
    }

    public function obat(){
        return $this->belongsTo('App\MasterObat', 'id_obat');
    }

    public function created_oleh(){
        return $this->hasOne('App\Users', 'id', 'created_by');
    }

    public function updated_oleh(){
        return $this->hasOne('App\Users', 'id', 'updated_by');
    }
}
