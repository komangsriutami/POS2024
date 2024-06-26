<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;
use DB;
class TransaksiTD extends Model
{
    // ini tabel nota penjualan
    protected $table = 'tb_nota_transfer_dokter';
    public $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = ['id_apotek_nota',
                            'tgl_nota',
    						'id_dokter',
    						'grand_total',
    						'keterangan',
                            'is_deleted',
                            'deleted_at',
                            'deleted_by'
    						];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'id_apotek_nota' => 'required',
            'id_dokter' => 'required',
            'tgl_nota' => 'required',
        ]);
    }

    public function save_from_array($details, $val){
        if($val==1)
        {
            $this->id_apotek_nota = session('id_apotek_active');
            $this->tgl_nota = date('Y-m-d');
            $this->created_by = Auth::user()->id;
            $this->created_at = date('Y-m-d H:i:s');
            $id_nota = $this->save();
        }else{
            $this->id_apotek_nota = session('id_apotek_active');
            $this->tgl_nota = date('Y-m-d');
            $this->updated_by = Auth::user()->id;
            $this->updated_at = date('Y-m-d H:i:s');
            $id_nota = $this->save();
        }

        $status = true;
        $str_array_id = array();
        $array_id_obat = array();
        $grand_total = 0;
        foreach ($details as $detail) {
            if(!in_array($detail['id_obat'], $array_id_obat)){
                if($detail['id']>0){
                    $obj = TransaksiTDDetail::find($detail['id']);
                }else{
                    $obj = new TransaksiTDDetail;
                }

                $obj->id_nota = $this->id;
                $obj->id_obat = $detail['id_obat'];
                $obj->harga_dokter = $detail['harga_dokter'];
                $obj->jumlah = $detail['jumlah'];
                $obj->total = $detail['harga_dokter']*$detail['jumlah'];
                $obj->created_by = Auth::user()->id;
                $obj->created_at = date('Y-m-d H:i:s');
                $obj->updated_at = null;
                $obj->updated_by = null;
                $obj->is_deleted = 0;

                $obj->save();
                $grand_total = $grand_total + $obj->total;
                $array_id_obat[] = $obj->id;

                $apotek = MasterApotek::find(session('id_apotek_active'));
		        $inisial = strtolower($apotek->nama_singkat);
		        $stok_before = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $obj->id_obat)->first();
		        $stok_now = $stok_before->stok_akhir-$obj->jumlah;

		        # update ke table stok harga
		        DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $obj->id_obat)->update(['stok_awal'=> $stok_before->stok_akhir, 'stok_akhir'=> $stok_now, 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => Auth::user()->id]);

		        # create histori
		        DB::table('tb_histori_stok_'.$inisial)->insert([
		            'id_obat' => $obj->id_obat,
		            'jumlah' => $obj->jumlah,
		            'stok_awal' => $stok_before->stok_akhir,
		            'stok_akhir' => $stok_now,
		            'id_jenis_transaksi' => '22', //transfer dokter
		            'id_transaksi' => $obj->id,
		            'batch' => null,
		            'ed' => null,
		            'created_at' => date('Y-m-d H:i:s'),
		            'created_by' => Auth::user()->id
		        ]);
            }
        }

        $this->grand_total = $grand_total;
        $this->save();
        
        if(!empty($array_id_obat)){
            DB::statement("DELETE FROM tb_detail_nota_transfer_dokter
                            WHERE id_nota=".$this->id." AND 
                                    id NOT IN(".implode(',', $array_id_obat).")");
        }else{
            DB::statement("DELETE FROM tb_detail_nota_transfer_dokter 
                            WHERE id_nota=".$this->id);
        }
    }

    public function detail_transfer_dokter(){
        return $this->hasMany('App\TransaksiTDDetail', 'id_nota', 'id')->where('tb_detail_nota_transfer_dokter.is_deleted', 0);
    }

    public function dokter(){
        return $this->hasOne('App\MasterDokter', 'id', 'id_dokter');
    }

    public function apotek_nota(){
        return $this->hasOne('App\MasterApotek', 'id', 'id_apotek_nota');
    }

    public function created_oleh(){
        return $this->hasOne('App\User', 'id', 'created_by');
    }

    public function updated_oleh(){
        return $this->hasOne('App\User', 'id', 'updated_by');
    }
}
