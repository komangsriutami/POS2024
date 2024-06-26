<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;

class DetailInputAset extends Model
{
    /* 
		Model 	: Untuk Detail Input Aset
		Author 	: Agus Yudi.
		Date 	: 21/09/2021
	*/

    use SoftDeletes;
    public $primaryKey = 'id';
    protected $table = 'tb_detail_transaksi_aset';
    protected $fillable = [
        'id_transaksi_aset',
        'id_aset',
        'jumlah',
        'merek',
        'nilai_satuan',
        'total_nilai',
        'id_dasar_harga',
        'id_kondisi_aset',
        'created_at',
        'updated_at',
        'deleted_at',
        'created_by',
        'updated_by',
        'deleted_by',
        'is_delete',
    ];

    public function validate()
    {
        return Validator::make((array)$this->attributes, [
            'id_aset' => 'required',
            'jumlah' => 'required',
            'merek' => 'required',
            'nilai_satuan' => 'required',
            'total_nilai' => 'required',
            'id_dasar_harga' => 'required',
            'id_kondisi_aset' => 'required',
        ]);
    }

    public function transaksi_aset(){
        return $this->hasOne('App\InputAset', 'id', 'id_transaksi_aset');
    }

    public function aset(){
        return $this->hasOne('App\MasterAset', 'id', 'id_aset');
    }

    public function save_plus()
    {
        $this->created_by = Auth::user()->id;
        $this->save();
    }

    public function save_edit()
    {
        $this->updated_by = Auth::user()->id;
        $this->save();
    }

    public function save_delete()
    {
        $this->deleted_by = Auth::user()->id;
        $this->is_deleted = 1;
        $this->save();
    }

    public function created_oleh()
    {
        return $this->hasOne('App\Users', 'id', 'created_by');
    }

    public function updated_oleh()
    {
        return $this->hasOne('App\Users', 'id', 'updated_by');
    }

    public function deleted_oleh()
    {
        return $this->hasOne('App\Users', 'id', 'deleted_by');
    }
}