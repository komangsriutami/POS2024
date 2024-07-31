<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;
use DB;
use App\Traits\DynamicConnectionTrait;
class TransaksiPenjualanClosing extends Model
{
    use DynamicConnectionTrait;
    /* 
		Model 	: Untuk Transaksi Penjualan Closing
		Author 	: Sri Utami
		Date 	: 7/11/2020
	*/
    protected $table = 'tb_closing_nota_penjualan';
    public $primaryKey = 'id';
    protected $fillable = ['tanggal',
    						'id_user',
                            'id_apotek_nota',
                            'jumlah_penjualan',
    						'total_penjualan',
    						'total_jasa_resep',
    						'total_jasa_dokter',
    						'total_paket_wd',
                            'total_lab',
                            'total_apd',
    						'total_debet',
    						'total_penjualan_cash',
    						'total_penjualan_cn',
                            'total_penjualan_cn_cash',
                            'total_penjualan_cn_debet',
    						'total_penjualan_kredit',
    						'total_penjualan_kredit_terbayar',
                            'total_diskon',
                            'total_switch_cash',
                            'uang_seharusnya',
                            'jumlah_tt',
                            'total_akhir'
    						];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'tanggal' => 'required',
            'id_user' => 'required',
            'id_apotek_nota' => 'required',
            // 'jumlah_penjualan' => 'required',
            // 'total_penjualan' => 'required',
            // 'total_jasa_resep' => 'required',
            // 'total_jasa_dokter' => 'required',
            // 'total_paket_wd' => 'required',
            // 'total_lab' => 'required',
            // 'total_apd' => 'required',
            // 'total_debet' => 'required',
            // 'total_penjualan_cash' => 'required',
            // 'total_penjualan_cn' => 'required',
            // 'total_penjualan_cn_cash' => 'required',
            // 'total_penjualan_cn_debet' => 'required',
            // 'total_penjualan_kredit' => 'required',
            // 'total_penjualan_kredit_terbayar' => 'required',
            // 'total_diskon' => 'required',
            // 'total_switch_cash' => 'required',
            'uang_seharusnya' => 'required',
            'jumlah_tt' => 'required',
            'total_akhir' => 'required',
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

    public function kasir(){
        return $this->hasOne('App\User', 'id', 'id_user');
    }

    public function created_oleh(){
        return $this->hasOne('App\User', 'id', 'created_by');
    }

    public function updated_oleh(){
        return $this->hasOne('App\User', 'id', 'updated_by');
    }
}
