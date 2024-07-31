<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use App\Biaya;
use App\BiayaDetail;
use App\JurnalUmum;
use App\JurnalUmumDetail;
use App\MasterKodeAkun;
use Illuminate\Support\Collection;

use Auth;
use DB;

use Carbon\Carbon;

class BiayaImport implements ToCollection
{
    public $importstatus;
    public $flag_trx;
    protected $jenis;

     public function __construct(int $jenis)
    {
       $this->jenis = $jenis;
       $this->flag_trx = 1;
       $this->importstatus = array();
    }

    public function collection(Collection $data)
    {
        if($this->jenis == 1){
            return $this->importstatus = array('noimport' => 1);
        } else {
            /* URUTAN 
            [0]  'Kode Akun Bayar Dari',
            [1]  'Bayar Nanti(y/n)', 
            [2]  'Batas Pembayaran', 
            [3]  '(*)No. Transaksi', 
            [4]  '(*)Tanggal Transaksi', 
            [5]  'Kode Cara Pembayaran (1:cash,2:transfer)', 
            [6]  '(*)ID Supplier',
            [7]  'Alamat Penagihan', 
            [8]  'tag (pisahkan dengan tanda ,)', 
            [9]  'Memo', 
            [10] 'Kode Akun Pajak Potong', 
            [11] 'Nominal Potongan Pajak', 
            [12] '(*)Kode Akun', 
            [13] '(*)Deskripsi', 
            [14] 'Kode Akun Pajak (pisahkan dengan tanda ,)',
            [15] '(*)Biaya (tanpa titik)'
            */


            $biayaimport_ok = 0;
            $biayaimport_error = 0;
            $duplicatedata = 0;

            $subtotal = 0;
            $jurnal_kredit = 0;
            $jurnal_debit = 0;

            $detail = array();
            $lastbiaya = array();
            $idbiaya = '';
            $idjurnal_umum = '';
            $lasttrx = '';

            $kodeakun = MasterKodeAkun::on($this->getConnectionName())->whereNull("deleted_by")->get();

            

            // dd($data);

            DB::connection($this->getConnectionName())->beginTransaction(); 
            DB::enableQueryLog();

            try {

                foreach ($data as $key => $row) 
                {
                    if($key > 0){                        

                            if($row[2] != ""){
                                if(is_numeric($row[2])){
                                    $tglbatas = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[2]))->toDateString();
                                } else {
                                    $tglbatas = $row[2];
                                }
                                
                                $tglbatasbayar = Date("Y-m-d",strtotime($tglbatas));
                            } else { $tglbatasbayar = null; }
                            
                            if($row[4] != ""){
                                // dd($row[4]);
                                if(is_numeric($row[4])){
                                    $tgl = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[4]))->toDateString();
                                } else {
                                    $tgl = $row[4];
                                }

                                $tgltrx = Date("Y-m-d",strtotime($tgl));
                            } else { $tgltrx = null; }



                            $biayadetil = new BiayaDetail;
                            $biayadetil->setDynamicConnection();


                            if(!is_null($row[0])){
                                // dd("sini");
                                // untuk cek apakah ada data dengan kode akun bayar, batas pembayaran, no trx, tgl trx, cara bayar, id supplier, kode akun, biaya yang sama //
                                $checkdata = $biayadetil->checkExistImport($row[0],$row[2],$row[3],$tgltrx,$row[5],$row[6],$row[12],$row[15]);
                                $statuscek = $checkdata->count();
                            } else {
                                // dd($lastbiaya);
                                if(!empty($lastbiaya)){
                                    // dd($row[0]);
                                    $checkdata = $biayadetil->checkExistImport($lastbiaya['id_akun_bayar'],$lastbiaya['tgl_batas_pembayaran'],$lastbiaya['no_biaya'],$lastbiaya['tgl_transaksi'],$lastbiaya['id_cara_pembayaran'],$lastbiaya['id_supplier'],$row[12],$row[15]);
                                    $statuscek = $checkdata->count();
                                } else {
                                    $statuscek = 1;
                                }
                            }
                            // dd($checkdata->count());




                            // kalau hasil cek = 0, insert -> true // 
                            // if($checkdata->count() == 0){ $insert = true; } else { $insert = false; }
                            // dd($statuscek);
                            if($statuscek == 0){

                                // cek kalau last trx no sama yang no saat ini beda dan != kosong, insert new biaya //
                                if($row[3] != $lasttrx){

                                    if(!is_null($row[3]) && !is_null($row[4]) && !is_null($row[6])){

                                        // dd("insert");
                                        $subtotal = 0;

                                        $biaya = new Biaya;
                                        $biaya->setDynamicConnection();
                                        $biaya->id_apotek = session('id_apotek_active');

                                        if($row[0] != ""){ $biaya->id_akun_bayar = $row[0]; }
                                        else { $biaya->id_akun_bayar = null; }
                                        
                                        $biaya->is_bayar_nanti = 0;
                                        if($row[1] != ""){
                                            if($row[1] == 'y'){ $biaya->is_bayar_nanti = 1; }
                                        }

                                        $biaya->tgl_batas_pembayaran = $tglbatasbayar;
                                        
                                        $biaya->no_biaya = $row[3];
                                        $biaya->tgl_transaksi = $tgltrx;
                                        $biaya->id_cara_pembayaran = $row[5];
                                        $biaya->id_supplier = $row[6];
                                        $biaya->alamat_penagihan = $row[7];
                                        $biaya->tag = $row[8];
                                        $biaya->memo = $row[9];

                                        $akun_potongan = $kodeakun->where("kode",$row[10])->first();
                                        if(!empty($akun_potongan)){ $biaya->id_akun_ppn_potong = $akun_potongan->id; }

                                        $biaya->ppn_potong = $row[11];
                                        $biaya->created_by = Auth::user()->id;
                                        $biaya->is_imported = 1;
                                        // dd($biaya);

                                        if(!$biaya->save()){ $biayaimport_error++; }
                                        else {

                                            $jurnal_kredit = 0;
                                            $jurnal_debit = 0;

                                            $iddetailjurnal_biaya_akun_ppn_potong = '';
                                            $iddetailjurnal_biaya_akun_bayar = '';

                                            # ---- insert jurnal ---- #
                                            $jurnal_umum = new JurnalUmum;
                                            $jurnal_umum->setDynamicConnection();
                                            $jurnal_umum->id_apotek = $biaya->id_apotek;
                                            $jurnal_umum->flag_trx = $this->flag_trx;
                                            $jurnal_umum->kode_referensi = $biaya->id;
                                            $jurnal_umum->no_transaksi = $biaya->no_biaya;
                                            $jurnal_umum->tgl_transaksi = $biaya->tgl_transaksi;
                                            $jurnal_umum->tag = $biaya->tag;
                                            $jurnal_umum->memo = $biaya->memo;
                                            $jurnal_umum->created_at = $biaya->created_at;
                                            $jurnal_umum->created_by = $biaya->created_by;
                                            $jurnal_umum->is_imported = 1;
                                            $jurnal_umum->save();

                                            $idjurnal_umum = $jurnal_umum->id;
                                        }






                                        // $lasttrx = $row[0];
                                        $lastbiaya = $biaya->toArray();
                                        // dd($lastjurnal);

                                        $idbiaya = $biaya->id;
                                        $lasttrx = $row[3];
                                    } 
                                } 




                                if($idbiaya != ""){
                                    // add detail //
                                    # kalau kode akun tidak null #
                                    if(!is_null($row[12])){
                                        // import detail biaya //

                                        $detail = new BiayaDetail;
                                        $detail->setDynamicConnection();
                                        $detail->id_biaya = $idbiaya;

                                        $akun = $kodeakun->where("kode",$row[12])->first();
                                        if(!empty($akun)){ $detail->id_kode_akun = $akun->id; }

                                        $detail->deskripsi = $row[13];
                                        if(!is_null($row[14])){ 
                                            $detail->id_akun_pajak = json_encode(explode(',',$row[14]));
                                        }
                                        $detail->biaya = $row[15];
                                        
                                        $detail->created_by = Auth::user()->id;
                                        $detail->created_at = Date("Y-m-d H:i:s");

                                        if($detail->save()){ 
                                            $biayaimport_ok++; 
                                            if(!is_null($row[15])){ $subtotal += $row[15]; }

                                            if($idjurnal_umum != ""){
                                                $detiljurnal = new JurnalUmumDetail;
                                                $detiljurnal->setDynamicConnection();
                                                $detiljurnal->id_jurnal = $jurnal_umum->id; 
                                                $detiljurnal->id_kode_akun = $detail->id_kode_akun; 
                                                $detiljurnal->flag_trx = $this->flag_trx; 
                                                $detiljurnal->kode_referensi = $detail->id; 
                                                $detiljurnal->deskripsi = $detail->deskripsi; 
                                                $detiljurnal->debit = $detail->biaya;
                                                $detiljurnal->created_by = Auth::user()->id;
                                                $detiljurnal->created_at = Date("Y-m-d H:i:s");
                                                $detiljurnal->save();

                                                $jurnal_debit += $detail->biaya;
                                            }


                                        };
                                    }


                                    // ---- pajaak per akun belum ---- //



                                    // save detail jurnal untuk pajak //
                                    if(!is_null($biaya->id_akun_ppn_potong) && $idjurnal_umum != ""){
                                        // insert detil jurnal //
                                        if($iddetailjurnal_biaya_akun_ppn_potong == ""){
                                            $detiljurnal = new JurnalUmumDetail;
                                            $detiljurnal->setDynamicConnection();
                                        } else {
                                            $detiljurnal = JurnalUmumDetail::on($this->getConnectionName())->find($iddetailjurnal_biaya_akun_ppn_potong);
                                        }                                    

                                        $detiljurnal->id_jurnal = $jurnal_umum->id; 
                                        $detiljurnal->id_kode_akun = $biaya->id_akun_ppn_potong; 
                                        $detiljurnal->flag_trx = $this->flag_trx; 
                                        $detiljurnal->kredit = $biaya->ppn_potong;
                                        $detiljurnal->deskripsi = "Potongan Pajak";
                                        $detiljurnal->created_by = Auth::user()->id;
                                        $detiljurnal->created_at = Date("Y-m-d H:i:s");
                                        $detiljurnal->save();

                                        // $jurnal_debit += $detail->ppn_potong;
                                        $iddetailjurnal_biaya_akun_ppn_potong = $detiljurnal->id;

                                    }

                                    // save detail jurnal untuk akun bayar dari //
                                    $total = $subtotal - $biaya->ppn_potong;
                                    if(!is_null($biaya->id_akun_bayar) && $idjurnal_umum != ""){
                                        // insert detil jurnal //
                                        if($iddetailjurnal_biaya_akun_bayar == ""){
                                            $detiljurnal = new JurnalUmumDetail;
                                            $detiljurnal->setDynamicConnection();
                                        } else {
                                            $detiljurnal = JurnalUmumDetail::on($this->getConnectionName())->find($iddetailjurnal_biaya_akun_bayar);
                                        }

                                        $detiljurnal->id_jurnal = $jurnal_umum->id; 
                                        $detiljurnal->id_kode_akun = $biaya->id_akun_bayar; 
                                        $detiljurnal->flag_trx = $this->flag_trx; 
                                        $detiljurnal->kredit = $total;
                                        $detiljurnal->deskripsi = "Akun Bayar Biaya";
                                        $detiljurnal->created_by = Auth::user()->id;
                                        $detiljurnal->created_at = Date("Y-m-d H:i:s");
                                        $detiljurnal->save();

                                        // $jurnal_kredit += $total;
                                        $iddetailjurnal_biaya_akun_bayar = $detiljurnal->id;

                                    }


                                    $biaya->subtotal = $subtotal;
                                    $biaya->save();

                                    // save total ke jurnal //
                                    $jurnal_umum->total_kredit = $jurnal_kredit;
                                    $jurnal_umum->total_debit = $jurnal_debit;
                                    $jurnal_umum->save();
                                }


                                // $biayaimport_ok++;

                            } else {
                                $duplicatedata++;
                            }
                    
                        // dd($jurnalimport_ok);
                    }
                }


                DB::connection($this->getConnectionName())->commit();
                $this->importstatus = array("biayaimport_ok"=>$biayaimport_ok, "biayaimport_error"=>$biayaimport_error, "duplicatedata"=>$duplicatedata);
                
                // dd($status);

            } catch (Exception $e) {
                dd("aa");
                DB::connection($this->getConnectionName())->rollback();
            }

        }
    }


    public function getSatus(): array
    {
        return $this->importstatus;
    }
}
