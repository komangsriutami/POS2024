<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;
use DB;
class TransaksiTO extends Model
{
    // ini tabel nota penjualan
    //protected $table = 'tb_nota_transfer_outlet';
    public $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = ['id_apotek_nota',
                            'tgl_nota',
    						'id_apotek_asal',
    						'id_apotek_tujuan',
    						'keterangan',
                            'is_deleted',
                            'deleted_at',
                            'deleted_by',
                            'is_from_transfer'
    						];

    public function __construct()
    {
        if(session('id_tahun_active') == date('Y')) {
            $this->setTable('tb_nota_transfer_outlet');
        } else {
            $this->setTable('tb_nota_transfer_outlet_histori');
        }
    }

    public function setTable($tableName)
    {
        $this->table = $tableName;
    }

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'id_apotek_tujuan' => 'required',
            'keterangan' => 'required',
        ]);
    }

    public function save_from_array($detail_transfer_outlets, $val){
        if($val==1)
        {
            $this->tgl_nota = date('Y-m-d H:i:s');
            $this->id_apotek_nota = session('id_apotek_active');
            $this->id_apotek_asal = session('id_apotek_active');
            $this->created_by = Auth::user()->id;
            $this->created_at = date('Y-m-d H:i:s');
        }else{
            $this->tgl_nota = date('Y-m-d H:i:s');
            $this->id_apotek_nota = session('id_apotek_active');
            $this->id_apotek_asal = session('id_apotek_active');
            $this->updated_by = Auth::user()->id;
            $this->updated_at = date('Y-m-d H:i:s');
        }

        if($this->save()) {
            $id_nota = $this->id;
        } else {
            DB::rollback();
            $rsp = array('status' => 0, 'message' => 'Error, periksa kembali data yang disimpan');
            return $rsp;
        }

        $status = true;
        $str_array_id = array();
        $array_id_obat = array();
        $total_nota = 0;
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $is_history = 0;

        $apotek2 = MasterApotek::find($this->id_apotek_tujuan);
        $inisial2 = strtolower($apotek2->nama_singkat);
        foreach ($detail_transfer_outlets as $detail_transfer_outlet) {
            if(!in_array($detail_transfer_outlet['id_obat'], $array_id_obat)){
                if($detail_transfer_outlet['id']>0){
                    $obj = TransaksiTODetail::find($detail_transfer_outlet['id']);
                }else{
                    $is_history = 1;
                    $obj = new TransaksiTODetail;
                }

                $obj->id_nota = $this->id;
                $obj->id_obat = $detail_transfer_outlet['id_obat'];
                $obj->harga_outlet = $detail_transfer_outlet['harga_outlet'];
                $obj->jumlah = $detail_transfer_outlet['jumlah'];
                $obj->total = $detail_transfer_outlet['harga_outlet'] * $detail_transfer_outlet['jumlah'];
                $obj->created_by = Auth::user()->id;
                $obj->created_at = date('Y-m-d H:i:s');
                $obj->updated_at = date('Y-m-d H:i:s');
                $obj->updated_by = '';
                $obj->is_deleted = 0;

                if($obj->save()) {
                } else {
                    DB::rollback();
                    $rsp = array('status' => 0, 'message' => 'Error, periksa kembali data yang disimpan');
                    return $rsp;
                }
                $array_id_obat[] = $obj->id;
                

                if(isset($this->is_from_transfer) AND $this->is_from_transfer == 1) {
                    $tf = TransaksiTransferDetail::find($detail_transfer_outlet['id_detail_transfer']);
                    $tf->is_status = 1;
                    $tf->jumlah = $obj->jumlah;
                    $tf->id_nota_transfer_outlet = $this->id;
                    $tf->id_det_nota_transfer_outlet = $obj->id;
                    $tf->created_by = Auth::id();
                    $tf->created_at = date('Y-m-d H:i:s');

                    if($tf->save()) {
                    } else {
                        DB::rollback();
                        $rsp = array('status' => 0, 'message' => 'Error, periksa kembali data yang disimpan');
                        return $rsp;
                    }

                    if($tf->id_defecta != null OR $tf->id_defecta != 0) {
                        DefectaOutlet::where('id', $tf->id_defecta)->update(['jumlah' => $obj->jumlah, 'id_process' => 2, 'last_update_process' => date('Y-m-d H:i:s')]);
                        DefectaOutletHistori::create([
                            'id_defecta' => $tf->id_defecta,
                            'id_status' => 2,
                            'created_at' => date('Y-m-d H:i:s'),
                            'created_by' => Auth::id(),
                            'updated_at' => date('Y-m-d H:i:s'),
                            'updated_by' => Auth::id(),
                        ]);
                    }

                    $cek_all_status = TransaksiTransferDetail::where('id_nota', $tf->id_nota)->where('is_status', 0)->count();
                    if($cek_all_status < 1) {
                        TransaksiTransfer::where('id', $tf->id_nota)->update(['is_status' => 1, 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => Auth::id()]);
                    }
                }

                $kurangStok = $this->kurangStok($obj->id, $obj->id_obat, $obj->jumlah);
                //dd($kurangStok);
                if($kurangStok['status'] == 0) {
                    DB::rollback();
                    $rsp = array('status' => 0, 'message' => 'Stok yang tersedia tidak mencukupi');
                    return $rsp;
                } else {
                    $obj->id_histori_stok = $kurangStok['array_id_histori_stok'];
                    $obj->id_histori_stok_detail = $kurangStok['array_id_histori_stok_detail'];
                    if($detail_transfer_outlet['is_margin'] != 1) {
                        $obj->harga_outlet = $kurangStok['hb_ppn'];
                        $obj->total = $kurangStok['hb_ppn'] * $detail_transfer_outlet['jumlah'];
                    } else {
                        $obj->harga_outlet = ($detail_transfer_outlet['persen']/100 * $kurangStok['hb_ppn'])+ $kurangStok['hb_ppn'];
                        $obj->total = $obj->harga_outlet * $detail_transfer_outlet['jumlah'];
                    }

                   // dd($obj);exit();
                    $total_nota = $total_nota+$obj->total;
                   
                    # crete histori stok barang
                    if($is_history == 1) {
                        $apotek = MasterApotek::find(session('id_apotek_active'));
                        $inisial = strtolower($apotek->nama_singkat);
                        $stok_before = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $obj->id_obat)->first(); 
                        $stok_now = $stok_before->stok_akhir-$obj->jumlah;

                        /*$arrayupdate = array(
                            'stok_awal'=> $stok_before->stok_akhir, 
                            'stok_akhir'=> $stok_now, 
                            'updated_at' => date('Y-m-d H:i:s'), 
                            'updated_by' => Auth::user()->id
                        );*/

                        # update ke table stok harga
                        $stok_harga = MasterStokHarga::where('id_obat', $obj->id_obat)->first();
                        $stok_harga->stok_awal = $stok_before->stok_akhir;
                        $stok_harga->stok_akhir = $stok_now;
                        $stok_harga->updated_at = date('Y-m-d H:i:s'); 
                        $stok_harga->updated_by = Auth::user()->id;
                        if($stok_harga->save()) {
                        } else {
                            DB::rollback();
                            $rsp = array('status' => 0, 'message' => 'Error, periksa kembali data yang disimpan');
                            return $rsp;
                        }

                        /*$arrayinsert = array(
                            'id_obat' => $obj->id_obat,
                            'jumlah' => $obj->jumlah,
                            'stok_awal' => $stok_before->stok_akhir,
                            'stok_akhir' => $stok_now,
                            'id_jenis_transaksi' => 4, //transfer keluar
                            'id_transaksi' => $obj->id,
                            'batch' => null,
                            'ed' => null,
                            'sisa_stok' => null,
                            'hb_ppn' => $obj->harga_outlet,
                            'created_at' => date('Y-m-d H:i:s'),
                            'created_by' => Auth::user()->id
                        );*/

                        # create histori
                        $histori_stok = HistoriStok::where('id_obat', $obj->id_obat)->where('jumlah', $obj->jumlah)->where('id_jenis_transaksi', 4)->where('id_transaksi', $obj->id)->first();
                        if(empty($histori_stok)) {
                            $histori_stok = new HistoriStok;
                        }
                        $histori_stok->id_obat = $obj->id_obat;
                        $histori_stok->jumlah = $obj->jumlah;
                        $histori_stok->stok_awal = $stok_before->stok_akhir;
                        $histori_stok->stok_akhir = $stok_now;
                        $histori_stok->id_jenis_transaksi = 4; //transfer keluar
                        $histori_stok->id_transaksi = $obj->id;
                        $histori_stok->batch = null;
                        $histori_stok->ed = null;
                        $histori_stok->sisa_stok = null;
                        $histori_stok->hb_ppn = $obj->harga_outlet;
                        $histori_stok->created_at = date('Y-m-d H:i:s');
                        $histori_stok->created_by = Auth::user()->id;
                        if($histori_stok->save()) {
                        } else {
                             DB::rollback();
                            $rsp = array('status' => 0, 'message' => 'Error, periksa kembali data yang disimpan');
                            return $rsp;
                        }
                    }

                    if($obj->save()) {
                        $this->total = $total_nota;
                        if($this->save()) {
                            $rsp = array('status' => 1, 'message' => 'Data transfer outlet berhasil disimpan');
                            return $rsp;
                        } else {
                            DB::rollback();
                            $rsp = array('status' => 0, 'message' => 'Error, periksa kembali data yang disimpan');
                            return $rsp;
                        }
                    } else {
                        DB::rollback();
                        $rsp = array('status' => 0, 'message' => 'Error, periksa kembali data yang disimpan');
                        return $rsp;
                    }

                    $rsp = array('status' => 1, 'message' => 'Data transfer berhasil disimpan');
                    return $rsp;
                }
            }
        }   
    }

    public function kurangStok($id_detail, $id_obat, $jumlah) {
        $inisial = strtolower(session('nama_apotek_singkat_active'));
        $cekHistori = DB::table('tb_histori_stok_'.$inisial)
                            ->where('id_obat', $id_obat)
                            ->whereIn('id_jenis_transaksi', [2,3,11,9])
                            ->where('sisa_stok', '>', 0)
                            ->orderBy('id', 'ASC')
                            ->first();

        $array_id_histori_stok = array();
        $array_id_histori_stok_detail = array();
        $hb_ppn = 0;

        if(!is_null($cekHistori)) {
            if($cekHistori->sisa_stok >= $jumlah) {
                # kosongkan sisa stok histori sebelumnya 
                $sisa_stok = $cekHistori->sisa_stok - $jumlah;
                $keterangan = $cekHistori->keterangan.', TO pada IDdet.'.$id_detail.' sejumlah '.$jumlah;
                DB::table('tb_histori_stok_'.$inisial)->where('id', $cekHistori->id)->update(['sisa_stok' => $sisa_stok, 'keterangan' => $keterangan]);
                $array_id_histori_stok[] = $cekHistori->id;
                $array_id_histori_stok_detail[] = array('id_histori_stok' => $cekHistori->id, 'jumlah' => $jumlah);
                $hb_ppn = $cekHistori->hb_ppn;
            } else {
                # jika jumlahnya tidak sama maka
                $selisih = $jumlah - $cekHistori->sisa_stok;

                # update jumlah selisih ke histori yang ada stok sebelumnya
                $i = $jumlah;
                $total  = 0;
                $array_id_histori_stok_tota = array();
                while($i >= 1) {
                    # cari histori berikutnya yg bisa dikurangi
                    $cekHistoriLanj = DB::table('tb_histori_stok_'.$inisial)
                            ->where('id_obat', $id_obat)
                            ->whereIn('id_jenis_transaksi', [2,3,11,9])
                            ->where('sisa_stok', '>', 0)
                            ->orderBy('id', 'ASC')
                            ->first();
                    //dd($cekHistoriLanj);exit();

                    if($cekHistoriLanj->sisa_stok >= $i) {
                        # update selisih jika stok melebihi jumlah
                        $keterangan = $cekHistoriLanj->keterangan.', TO pada IDdet.'.$id_detail.' sejumlah '.$i;
                        $sisa = $cekHistoriLanj->sisa_stok - $i;
                        DB::table('tb_histori_stok_'.$inisial)->where('id', $cekHistoriLanj->id)->update(['sisa_stok' => $sisa, 'keterangan' => $keterangan]);
                        $array_id_histori_stok_detail[] = array('id_histori_stok' => $cekHistoriLanj->id, 'jumlah' => $i);
                        $total = $total + ($cekHistoriLanj->hb_ppn * $i);
                        $array_id_histori_stok_tota[] = array('total'=>$total, 'hb_ppn' => $cekHistoriLanj->hb_ppn, 'sisa_stok' => $i);
                        $i = 0;
                    } else {
                        # update selisih jika stok kurang dari jumlah
                        $keterangan = $cekHistoriLanj->keterangan.', TO pada IDdet.'.$id_detail.' sejumlah '.$cekHistoriLanj->sisa_stok;
                        $sisa = $i - $cekHistoriLanj->sisa_stok;

                        DB::table('tb_histori_stok_'.$inisial)->where('id', $cekHistoriLanj->id)->update(['sisa_stok' => 0, 'keterangan' => $keterangan]);
                        $i = $sisa;
                        $array_id_histori_stok_detail[] = array('id_histori_stok' => $cekHistoriLanj->id, 'jumlah' => $cekHistoriLanj->sisa_stok);
                        $total = $total + ($cekHistoriLanj->hb_ppn * $cekHistoriLanj->sisa_stok);
                        $array_id_histori_stok_tota[] = array('total'=>$total, 'hb_ppn' => $cekHistoriLanj->hb_ppn, 'sisa_stok' => $cekHistoriLanj->sisa_stok);
                    }
                    
                    $array_id_histori_stok[] = $cekHistoriLanj->id;
                }

                $hb_ppn = $total/$jumlah;
                $hb_ppn = ceil($hb_ppn);
               // dd($hb_ppn);
            } 

            //exit();
            
            $rsp = array('status' => 1, 'array_id_histori_stok' => json_encode($array_id_histori_stok), 'array_id_histori_stok_detail' => json_encode($array_id_histori_stok_detail), 'hb_ppn' => $hb_ppn);
            return $rsp;
        } else {
            $rsp = array('status' => 0, 'array_id_histori_stok' => null, 'array_id_histori_stok_detail' => null, 'hb_ppn' => null);
            return $rsp;
        }
    }

    public function detail_transfer_outlet(){
        return $this->hasMany('App\TransaksiTODetail', 'id_nota', 'id')->where('tb_detail_nota_transfer_outlet.is_deleted', 0);
    }

    public function detail_transfer_total(){
        if(session('id_tahun_active') == date('Y')) {
            $detTable = 'tb_detail_nota_transfer_outlet';
        } else {
            $detTable = 'tb_detail_nota_transfer_outlet_histori';
        }

        return $this->hasMany('App\TransaksiTODetail', 'id_nota', 'id')
                    ->select([
                        DB::raw("SUM($detTable.jumlah * $detTable.harga_outlet) AS total")
                    ])
                    ->where("$detTable.is_deleted", 0)->limit(1);
    }

    public function apotek_asal(){
        return $this->hasOne('App\MasterApotek', 'id', 'id_apotek_asal');
    }

    public function apotek_tujuan(){
        return $this->hasOne('App\MasterApotek', 'id', 'id_apotek_tujuan');
    }

    public function created_oleh(){
        return $this->hasOne('App\User', 'id', 'created_by');
    }

    public function updated_oleh(){
        return $this->hasOne('App\User', 'id', 'updated_by');
    }
}
