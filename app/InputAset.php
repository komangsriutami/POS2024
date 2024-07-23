<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;
use DB;
use App\Traits\DynamicConnectionTrait;

class InputAset extends Model
{
    use DynamicConnectionTrait;
    /* 
		Model 	: Untuk Input Aset
		Author 	: Agus Yudi.
		Date 	: 21/09/2021
	*/

    use SoftDeletes;
    public $primaryKey = 'id';
    protected $table = 'tb_transaksi_aset';
    public $timestamps = false;
    protected $fillable = [
        'no_transaksi',
        'tgl_transaksi',
        'keterangan',
        'created_at',
        'updated_at',
        'deleted_at',
        'created_by',
        'updated_by',
        'deleted_by',
        'is_delete',
    ];

    public function detail_aset(){
        return $this->hasMany('App\DetailInputAset', 'id_transaksi_aset', 'id')->where('tb_detail_transaksi_aset.is_deleted', 0);
    }

    public function detail_aset_total(){
        return $this->hasMany('App\DetailInputAset', 'id_transaksi_aset', 'id')
                    ->select([
                        DB::raw('SUM(tb_detail_transaksi_aset.total_nilai) AS total')
                    ])
                    ->where('tb_detail_transaksi_aset.is_deleted', 0)->limit(1);
    }

    public function validate()
    {
        return Validator::make((array)$this->attributes, [
            'tgl_transaksi' => 'required|date',
            'no_transaksi' => 'required',
            'keterangan' => 'required',
        ]);
    }

    public function save_from_array($detail_asets, $val){
        if($val==1)
        {
            $this->created_by = Auth::user()->id;
            $this->created_at = date('Y-m-d H:i:s');
            $id_nota = $this->save();
        }else{
            $this->updated_by = Auth::user()->id;
            $this->updated_at = date('Y-m-d H:i:s');
            $id_nota = $this->save();
        }

        $status = true;
        $str_array_id = array();
        $array_id_aset = array();

        foreach ($detail_asets as $detail_aset) {
            if(!in_array($detail_aset['id_aset'], $array_id_aset)){
                if($detail_aset['id']>0){
                    $obj = DetailInputAset::on($this->getConnectionName())->find($detail_aset['id']);
                }else{
                    $obj = new DetailInputAset;
                    $obj->setDynamicConnection();
                }

                $obj->id_transaksi_aset = $this->id;
                $obj->id_aset = $detail_aset['id_aset'];
                $obj->jumlah = $detail_aset['jumlah'];
                $obj->merek = $detail_aset['merek'];
                $obj->nilai_satuan = $detail_aset['nilai_satuan'];
                $obj->total_nilai = $detail_aset['total_nilai'];
                $obj->id_dasar_harga = $detail_aset['id_dasar_harga'];
                $obj->id_kondisi_aset = $detail_aset['id_kondisi_aset'];
                $obj->created_by = Auth::user()->id;
                $obj->created_at = date('Y-m-d H:i:s');
                $obj->updated_at = date('Y-m-d H:i:s');
                $obj->updated_by = '';
                $obj->is_deleted = 0;

                $obj->save();
                $array_id_aset[] = $obj->id;
            }
        }

        if(!empty($array_id_aset)){
            DB::connection($this->getConnection())->statement("DELETE FROM tb_detail_transaksi_aset
                            WHERE id_transaksi_aset=".$this->id." AND 
                                    id NOT IN(".implode(',', $array_id_aset).")");
        }else{
            DB::connection($this->getConnection())->statement("DELETE FROM tb_detail_transaksi_aset 
                            WHERE id_transaksi_aset=".$this->id);
        }
        
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
