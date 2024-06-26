<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\MasterInvestor;
use App\MasterApotek;
use Validator;
use Auth;

class InvestasiModal extends Model
{
    /* 
		Model 	: Untuk Investasi Modal
		Author 	: Govi.
		Date 	: 12/09/2021
	*/

    use SoftDeletes;
    public $primaryKey = 'id';
    protected $table = 'tb_investasi_modal';
    protected $fillable = [
        'tgl_transaksi',
        'id_apotek',
        'id_investor',
        'saham',
        'jumlah_modal',
        'persentase_kepemilikan',
        'created_at',
        'updated_at',
        'deleted_at',
        'created_by',
        'updated_by',
        'deleted_by',
        'is_delete',
    ];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'tgl_transaksi' => 'required|date',
            'id_apotek' => 'required',
            'id_investor' => 'required',
            'saham' => 'required|numeric|digits_between:1,99',
            'jumlah_modal' => 'required|numeric|digits_between:4,99',
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

    public function save_delete(){
        $this->deleted_by = Auth::user()->id;
        $this->is_deleted = 1;
        $this->save();
    }

    public function apotek()
    {
        return $this->hasOne('App\MasterApotek', 'id', 'id_apotek');
    }

    public function investor()
    {
        return $this->hasOne('App\MasterInvestor', 'id', 'id_investor');
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
