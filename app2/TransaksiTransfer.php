<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;
use DB;
use App\Traits\DynamicConnectionTrait;

class TransaksiTransfer extends Model
{
    use DynamicConnectionTrait;
     // ini tabel order
    protected $table = 'tb_nota_transfer';
    public $primaryKey = 'id';
    protected $fillable = ['tgl_nota', 'id_apotek_transfer', 'id_apotek', 'is_status'];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
    		'id_apotek_transfer' => 'required',
            'id_apotek' => 'required',
        ]);
    }

    public function save_from_array($detail_transfers, $val){
        if($val==1) {
            $this->tgl_nota = date('Y-m-d');
            $this->created_at = date('Y-m-d H:i:s');
            $this->created_by = Auth::user()->id;
            $id_nota = $this->save();
        }else{
            $this->updated_at = date('Y-m-d H:i:s');
            $this->updated_by = Auth::user()->id;
            $id_nota = $this->save();
        }

        $status = true;
        $array_id_obat = array();
        foreach ($detail_transfers as $detail_transfer) {
            if($detail_transfer['id']>0){
                $obj = TransaksiTransferDetail::on($this->getConnectionName())->find($detail_transfer['id']);
            }else{
                $obj = new TransaksiTransferDetail;
                $obj->setDynamicConnection();
            }

            $is_titip_order = 0;
            if($this->id_apotek != $detail_transfer['id_apotek']) {
                $is_titip_order = 1;
            }
            $obj->id_nota = $this->id;
            $obj->id_obat = $detail_transfer['id_obat'];
            $obj->jumlah = $detail_transfer['jumlah'];
            $obj->id_satuan = $detail_transfer['id_satuan'];
            $obj->is_titip_order = $is_titip_order;
            $obj->is_purchasing_add = $detail_transfer['is_purchasing_add'];
            $obj->keterangan = $detail_transfer['keterangan'];
            $obj->created_by = Auth::user()->id;
            $obj->created_at = date('Y-m-d H:i:s');
            $obj->updated_at = date('Y-m-d H:i:s');
            $obj->updated_by = '';
            $obj->is_deleted = 0;



            if($detail_transfer['id_defecta'] != '') {
                $obj->id_defecta = $detail_transfer['id_defecta'];
                $defecta = DefectaOutlet::on($this->getConnectionName())->find($detail_transfer['id_defecta']);
                //setelah itu, update tabel temp order
                $defecta->id_process = 1;
                $defecta->save();

                DefectaOutletHistori::create([
                    'id_defecta' => $defecta->id,
                    'id_status' => 2,
                    'id_process' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => Auth::user()->id,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'updated_by' => Auth::user()->id,
                ]);
            } else {
                # create data defecta
                $defecta = new DefectaOutlet;
                $defecta->setDynamicConnection();
                $defecta->id_obat = $obj->id_obat;
                $defecta->id_suplier = null;
                $defecta->id_suplier_order = null;
                $defecta->id_apotek_transfer = $obj->id_apotek_transfer;
                $defecta->total_stok = null;
                $defecta->total_buffer = null;
                $defecta->forcasting = null;
                $defecta->id_apotek = $this->id_apotek;
                $defecta->jumlah_diajukan = $obj->jumlah;
                $defecta->jumlah_order = $obj->jumlah;
                $defecta->id_satuan = $obj->id_satuan;
                $defecta->komentar = $obj->keterangan;
                $defecta->is_add_manual = 1;
                $defecta->created_at = date('Y-m-d H:i:s');
                $defecta->created_by = Auth::id();
                //setelah itu, update tabel temp order
                $defecta->id_process = 1;
                $defecta->id_status = 2;  //transfer
                $defecta->save();

                $obj->id_defecta = $defecta->id;
                $obj->save();

                DefectaOutletHistori::create([
                    'id_defecta' => $defecta->id,
                    'id_status' => 2,
                    'id_process' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => Auth::user()->id,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'updated_by' => Auth::user()->id,
                ]);
            }

            $obj->save();
            $array_id_obat[] = $obj->id;            
        }

        if(!empty($array_id_obat)){
            DB::connection($this->getConnection())->statement("DELETE FROM tb_detail_nota_transfer
                            WHERE id_nota=".$this->id." AND 
                                    id NOT IN(".implode(',', $array_id_obat).")");
        }else{
            DB::connection($this->getConnection())->statement("DELETE FROM tb_detail_nota_transfer 
                            WHERE id_nota=".$this->id);
        }
    }

    public function save_plus(){
        $this->created_by = Auth::user()->id;
        $this->save();
    }

    public function save_edit(){
        $this->updated_by = Auth::user()->id;
        $this->save();
    }

    public function detail_transfer(){
        return $this->hasMany('App\TransaksiTransferDetail', 'id_nota', 'id');
    }

    public function detail_transfer_belum(){
        return $this->hasMany('App\TransaksiTransferDetail', 'id_nota', 'id')->where('is_deleted', 0)->where('is_status', 0);
    }

    public function detail_transfer_sudah(){
        return $this->hasMany('App\TransaksiTransferDetail', 'id_nota', 'id')->where('is_deleted', 0)->where('is_status', '>', 0);
    }

    public function getSign(){
        return $this->hasOne('App\User', 'id', 'sign_by');
    }
}
