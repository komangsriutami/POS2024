<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;
use KyslikColumnSortableSortable;

class TransaksiPembelianDetail extends Model
{
    protected $table = 'tb_detail_nota_pembelian';
    public $primaryKey = 'id';
    protected $fillable = ['id_nota',
                            'id_obat',
                            'total_harga',
                            'jumlah',
                            'harga_beli',
                            'diskon',
                            'diskon_persen',
                            'id_batch',
                            'tgl_batch',
                            'is_retur',
                            'harga_beli_ppn',
                            'margin'
                            ];
    public $sortable = ['id_obat',
                        'harga_beli',
                        'harga_beli_ppn'];

    public function validate(){
        return Validator::make((array)$this->attributes, [
            'id_nota' => 'required',
            'id_obat' => 'required',
            'total_harga' => 'required',
            'jumlah' => 'required',
            'harga_beli' => 'required',
            'diskon' => 'required',
            'diskon_persen' => 'required',
            'id_batch' => 'required',
            'tgl_batch' => 'required',
            'harga_beli_ppn' => 'required',
            'margin' => 'required',
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

    public function pembayaran_konsinyasi(){
        return $this->hasMany('App\PembayaranKonsinyasi', 'id_detail_nota', 'id');
    }

    public function obat(){
        return $this->hasOne('App\MasterObat', 'id', 'id_obat');
    }

    public function stok_harga(){
        return $this->hasOne('App\MasterStokHarga', 'id_obat', 'id_obat');
    }

    public function nota(){
        return $this->hasOne('App\TransaksiPembelian', 'id', 'id_nota');
    }

    public function revisi(){
        return $this->hasOne('App\RevisiPembelian', 'id', 'id_retur_penjualan');
    }

    public function created_oleh(){
        return $this->hasOne('App\User', 'id', 'created_by');
    }

    public function updated_oleh(){
        return $this->hasOne('App\User', 'id', 'updated_by');
    }

}
