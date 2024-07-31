<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use App\JurnalUmum;
use App\JurnalUmumDetail;
use App\MasterKodeAkun;

use Auth;

class JurnalUmumImport implements ToCollection
{
    public $importstatus = array();
    protected $jenis;

    public function __construct(int $jenis)
    {
       $this->jenis = $jenis;
       $this->importstatus = array();
    }

    public function collection(Collection $data)
    {
        if($this->jenis == 1){
            return $this->importstatus = array('noimport' => 1);
        } else {
            /* URUTAN 
            [0] 'No. Transaksi(*)', 
            [1] 'Tanggal Transaksi(*)', 
            [2] 'ID Jenis Transaksi(*)', 
            [3] 'Kode Referensi / Kontak',
            [4] 'tag', 
            [5] 'Memo', 
            [6] 'Kode Akun(*)', 
            [7] 'Deskripsi', 
            [8] 'Kredit (tanpa titik)', 
            [9] 'Debit (tanpa titik)'
            */


            $jurnalimport_ok = 0;
            $jurnalimport_error = 0;
            $duplicatedata = 0;

            $detail = array();
            $lastjurnal = array();
            $idjurnal = '';
            $lasttrx = '';

            $kodeakun = MasterKodeAkun::on($this->getConnectionName())->whereNull("deleted_by")->get();

            $total_debit = 0;
            $total_kredit = 0;

            foreach ($data as $key => $row) 
            {
                if($key > 0){
                    
                        $tgl = Date("Y-m-d",strtotime($row[1]));


                        $jurnaldetail = new JurnalUmumDetail;
                        $jurnaldetail->setDynamicConnection();


                        if(!is_null($row[0])){
                            // dd("sini");
                            // untuk cek apakah ada data dengan no trx, tgl trx, kode akun, debit, kredit yang sama //
                            $checkdata = $jurnaldetail->checkExistImport($row[0],$tgl,$row[6],$row[8],$row[9]);
                            $statuscek = $checkdata->count();
                        } else {
                            // dd($lastjurnal);
                            if(!empty($lastjurnal)){
                                // dd($row[0]);
                                $checkdata = $jurnaldetail->checkExistImport($lastjurnal['no_transaksi'],$lastjurnal['tgl_transaksi'],$row[3],$row[5],$row[6]);
                                $statuscek = $checkdata->count();
                            } else {
                                $statuscek = 1;
                            }
                        }
                        // dd($checkdata->count());




                        // kalau hasil cek = 0, insert -> true // 
                        // if($checkdata->count() == 0){ $insert = true; } else { $insert = false; }

                        if($statuscek == 0){

                            // cek kalau last trx no sama yang no saat ini beda dan != kosong, insert new jurnal //
                            if($row[0] != $lasttrx){

                                if(!is_null($row[0]) && !is_null($row[1]) && !is_null($row[2])){
                                    $jurnal = new JurnalUmum;
                                    $jurnal->id_apotek = session('id_apotek_active');
                                    $jurnal->no_transaksi = $row[0];
                                    $jurnal->tgl_transaksi = $tgl;
                                    $jurnal->tag = $row[4];
                                    $jurnal->memo = $row[5];
                                    $jurnal->created_by = Auth::user()->id;
                                    $jurnal->is_imported = 1;
                                    // dd($jurnal);

                                    if($jurnal->save()){  } else { $jurnalimport_error++; }
                                    // $lasttrx = $row[0];
                                    $lastjurnal = $jurnal->toArray();
                                    // dd($lastjurnal);

                                    $idjurnal = $jurnal->id;
                                    $lasttrx = $row[0];
                                } 
                            } 

                            if($idjurnal != ""){
                                if(!is_null($row[6])){
                                    // import detail //
                                    $detail = new JurnalUmumDetail;
                                    $detail->id_jurnal = $idjurnal;
                                    $detail->id_jenis_transaksi = $row[2];
                                    $detail->kode_referensi = $row[3];

                                    $akun = $kodeakun->where("kode",$row[6])->first();
                                    if(!empty($akun)){ $detail->id_kode_akun = $akun->id; }
                                    
                                    $detail->deskripsi = $row[7];
                                    if(!is_null($row[8])){ $detail->kredit = $row[8];}
                                    if(!is_null($row[9])){ $detail->debit = $row[9];}

                                    $detail->created_by = Auth::user()->id;

                                    if($detail->save()){ 
                                        $jurnalimport_ok++; 
                                        if(!is_null($row[8])){ $total_debit += $row[8]; }
                                        if(!is_null($row[9])){ $total_kredit += $row[9]; }
                                    };
                                }
                            }


                            $jurnal->total_debit = $total_debit;
                            $jurnal->total_kredit = $total_kredit;
                            $jurnal->save();

                        } else {
                            $duplicatedata++;
                        }
                
                    // dd($jurnalimport_ok);
                }
            }

            $this->importstatus = array("jurnalimport_ok"=>$jurnalimport_ok, "jurnalimport_error"=>$jurnalimport_error, "duplicatedata"=>$duplicatedata);
            
            // dd($status);

            return $this->importstatus; 
        }
    }

    public function getSatus(): array
    {
        return $this->importstatus;
    }
}
