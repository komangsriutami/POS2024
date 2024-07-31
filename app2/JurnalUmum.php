<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;

use App\JurnalUmumDetail;
use App\Traits\DynamicConnectionTrait;

class JurnalUmum extends Model
{
    use DynamicConnectionTrait;
    protected $table = 'tb_jurnal_umum';
    public $primaryKey = 'id';
    // protected $fillable = ['tgl_transaksi', 'no_transaksi', 'id_kode_akun', 'id_sub_kode_akun', 'jumlah', 'keterangan'];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
    		'tgl_transaksi' => 'required',
            'no_transaksi' => 'required|max:255'
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


    public function detailjurnal(){
        return $this->hasMany('App\JurnalUmumDetail', 'id_jurnal', 'id')->whereNull('deleted_by');
    }


    public function filebuktijurnal(){
        return $this->hasMany('App\JurnalUmumBukti', 'id_jurnal', 'id')->whereNull('deleted_by');
    }

    public function userUpdate(){
        if(is_null($this->updated_by)){
            return $this->hasOne('App\User', 'id', 'created_by');
        } else {
            return $this->hasOne('App\User', 'id', 'updated_by');
        }
    }



    /*
        =======================================================================================
        For     : Fungsi save ke jurnal untuk proses reload
        Author  : Citra
        Date    : 23/10/2021
        =======================================================================================
    */
    public function saveLoadDataToJurnal($param,$param_detail)
    {
        // dd($datasave);
        // **** CheckJurnal **** //
        /* 
        * 1. cek apakah ada jurnal umum yang di cek : 
        *           is_reloaded = 1,  kode_referensi = id_reload_status, tgl_transaksi = tgl_nota, id_apotek 
        * kalau ada : update. => update juga total di tb_jurnal_umum.
        * 2. kalau tidak ada new jurnal umum.
        */
        $checkjurnal = JurnalUmum::on($this->getConnectionName())->where("is_reloaded",1)
            ->where("kode_referensi",$param['kode_referensi'])
            ->where("tgl_transaksi",$param['tgl_transaksi'])
            ->whereNull("deleted_by")
            ->where(function($subquery) use ($param) {
                if(isset($param['memo'])){
                    $subquery->whereRaw("memo = '".$param['memo']."'");
                }
            })
            ->first();
        if(is_null($checkjurnal)){
            $jurnal = new JurnalUmum;
            $jurnal->setDynamicConnection();
            $jurnal->kode_referensi = $param['kode_referensi'];
            $jurnal->tgl_transaksi = $param['tgl_transaksi'];
            $jurnal->id_apotek = $param['id_apotek'];
            $jurnal->created_by = Auth::user()->id;    
            $jurnal->is_reloaded = 1;   

            if(isset($param['memo'])){
                $jurnal->memo = $param['memo'];
            }

            $jurnal->save();        
        } else {
            $jurnal = JurnalUmum::on($this->getConnectionName())->find($checkjurnal->id);
            $jurnal->updated_by = Auth::user()->id;
        }        

        if(!is_null($jurnal)){
           // insert detail //
            $total_debit = 0;
            $total_kredit = 0;

            foreach ($param_detail as $key => $value) {
                // dd($value);

                # check detail #
                $cekdetail = JurnalUmumDetail::on($this->getConnectionName())->whereRaw("id_jurnal = '".$jurnal->id."'")
                            ->whereRaw("id_jenis_transaksi = '".$value['id_jenis_transaksi']."'") 
                            ->whereRaw("id_kode_akun = '".$value['id_kode_akun']."'") 
                            ->whereRaw("is_reloaded = 1")
                            ->whereRaw("kode_referensi = '".$value['kode_referensi']."'")
                            ->where(function($subquery) use ($value) {
                                if(isset($value['deskripsi'])){
                                    $subquery->whereRaw("deskripsi = '".$value['deskripsi']."'");
                                }
                            })
                            ->whereNull("deleted_by")
                            ->first();
                if(!is_null($cekdetail)){
                    $detail = JurnalUmumDetail::on($this->getConnectionName())->find($cekdetail->id);
                } else {
                    $detail = new JurnalUmumDetail;
                    $detail->setDynamicConnection();
                }

                $detail->id_jurnal = $jurnal->id;
                $detail->id_jenis_transaksi = $value['id_jenis_transaksi'];
                $detail->kode_referensi = $value['kode_referensi'];
                $detail->id_kode_akun = $value['id_kode_akun'];
                $detail->is_reloaded = 1;

                if(isset($value['is_dikurang'])){
                    $detail->is_dikurang = $value['is_dikurang'];
                }

                if(isset($value['deskripsi'])){
                    $detail->deskripsi = $value['deskripsi'];
                }

                if(isset($value['debit'])){
                    $detail->debit = $value['debit'];

                    if($detail->is_dikurang == 1){
                        $total_debit -= $value['debit'];  
                    } else {
                        $total_debit += $value['debit'];
                    }
                    
                } 

                if(isset($value['kredit'])){
                    $detail->kredit = $value['kredit'];
                    
                    if($detail->is_dikurang == 1){
                        $total_kredit -= $value['kredit'];  
                    } else {
                        $total_kredit += $value['kredit'];
                    }
                }



                $detail->save();

            } 

            $jurnal->total_kredit = $total_kredit;
            $jurnal->total_debit = $total_debit;
            $jurnal->save();

            $status = array("status" => 1, "keterangan" => "");

        } else {
            $status = array("status" => 2, "keterangan" => "jurnal tidak ditemukan");
        }

        return $status;
    } 




}
