<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterApotek;
use App\JurnalUmum;
use App\JurnalUmumDetail;
use App\JurnalUmumBukti;
use App\MasterKodeAkun;
use App\MasterKategoriAkun;
use App\MasterJenisTransaksi;
use App\ReloadDataStatus;
use App\ReloadDataStatusDetail;
use App\TransaksiPenjualan;
use App\ReturPenjualan;

use App\Exports\JurnalUmumTemplateExport;
use App\Exports\JurnalUmumKeterangan;
use App\Imports\JurnalUmumImport;
use App;
use Datatables;
use DB;
use Auth;
use Mail;
use App\Traits\DynamicConnectionTrait;

class LaporanController extends Controller
{
    use DynamicConnectionTrait;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:web');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
    	return view('laporan.index');
    }

    # laporan home/rincian bisnis
	public function neraca() {
		$date_now = date('Y-m-d');

		$aktivas = MasterKategoriAkun::on($this->getConnectionName())->whereIn('kode', [1,2,3,4,5,6,7])
									->where('is_deleted', 0)
									->get();

		$arr_aktivas = array();
		$total_aktiva = 0;
		foreach ($aktivas as $key => $val) {
            $akuns = MasterKodeAkun::on($this->getConnectionName())->where('id_kategori_akun', $val->id)
                                    ->where('is_deleted', 0)
                                    ->get();
            $i_val = 0;
            $arr_akuns = array();
            $total_akun = 0;
            foreach ($akuns as $x => $obj) {
    			$getdebit = JurnalUmumDetail::on($this->getConnectionName())->select(DB::RAW("SUM(debit) as total_debit"))
                        ->join("tb_jurnal_umum as j","j.id","id_jurnal")
                        ->whereRaw("id_kode_akun = '".$obj->id."'")
                        ->whereRaw("id_apotek = '".session('id_apotek_active')."'")
                        ->whereNull("tb_jurnal_umum_detail.deleted_by")
                        ->whereNull("j.deleted_by")
                        ->first();

                if(empty($getdebit)){ $total_debit = 0; } else { $total_debit = $getdebit->total_debit ; }

                $getkredit = JurnalUmumDetail::on($this->getConnectionName())->select(DB::RAW("SUM(kredit) as total_kredit"))
                        ->join("tb_jurnal_umum as j","j.id","id_jurnal")
                        ->where("id_kode_akun",$obj->id)
                        ->whereRaw("id_apotek = '".session('id_apotek_active')."'")
                        ->whereNull("tb_jurnal_umum_detail.deleted_by")
                        ->whereNull("j.deleted_by")
                        ->first();

                if(empty($getkredit)){ $total_kredit = 0; } else { $total_kredit = $getkredit->total_kredit ; }

                $saldo = $total_debit - $total_kredit;
                $total_akun = $total_akun + $saldo;
                if($saldo != 0) {
                	$saldo = number_format($saldo);
    				$arr_akuns[] = array('kode' => $obj->kode, 'nama' => $obj->nama, 'saldo_akhir' => $saldo);
                    $i_val++;
    			}
            }

            $total_aktiva = $total_aktiva + $total_akun;
            if($i_val > 0) {
                $arr_aktivas[] = array('id' => $val->id, 'kode' => $val->kode, 'nama' => $val->nama, 'akuns' => $arr_akuns, 'total_akun' => $total_akun);
            }
		}

        $pasivas = MasterKategoriAkun::on($this->getConnectionName())->whereIn('kode', [8,9,10,11,12])
                                    ->where('is_deleted', 0)
                                    ->get();

        $arr_pasivas = array();
        $total_pasiva = 0;
        foreach ($pasivas as $key => $val) {
            $akuns = MasterKodeAkun::on($this->getConnectionName())->where('id_kategori_akun', $val->id)
                                    ->where('is_deleted', 0)
                                    ->get();
            $i_val = 0;
            $arr_akuns = array();
            $total_akun = 0;
            foreach ($akuns as $x => $obj) {
                $getdebit = JurnalUmumDetail::on($this->getConnectionName())->select(DB::RAW("SUM(debit) as total_debit"))
                        ->join("tb_jurnal_umum as j","j.id","id_jurnal")
                        ->whereRaw("id_kode_akun = '".$obj->id."'")
                        ->whereRaw("id_apotek = '".session('id_apotek_active')."'")
                        ->whereNull("tb_jurnal_umum_detail.deleted_by")
                        ->whereNull("j.deleted_by")
                        ->first();

                if(empty($getdebit)){ $total_debit = 0; } else { $total_debit = $getdebit->total_debit ; }

                $getkredit = JurnalUmumDetail::on($this->getConnectionName())->select(DB::RAW("SUM(kredit) as total_kredit"))
                        ->join("tb_jurnal_umum as j","j.id","id_jurnal")
                        ->where("id_kode_akun",$obj->id)
                        ->whereRaw("id_apotek = '".session('id_apotek_active')."'")
                        ->whereNull("tb_jurnal_umum_detail.deleted_by")
                        ->whereNull("j.deleted_by")
                        ->first();

                if(empty($getkredit)){ $total_kredit = 0; } else { $total_kredit = $getkredit->total_kredit ; }

                $saldo = $total_debit - $total_kredit;
                $total_akun = $total_akun + $saldo;
                if($saldo != 0) {
                    $saldo = number_format($saldo);
                    $arr_akuns[] = array('kode' => $obj->kode, 'nama' => $obj->nama, 'saldo_akhir' => $saldo);
                    $i_val++;
                }
            }

            $total_pasiva = $total_pasiva + $total_akun;
            if($i_val > 0) {
                $arr_pasivas[] = array('id' => $val->id, 'kode' => $val->kode, 'nama' => $val->nama, 'akuns' => $arr_akuns, 'total_akun' => $total_akun);
            }
        }
		

		$total = array(
						'total_aktiva' => $total_aktiva, 
						'total_pasiva' => $total_pasiva
					);

		return view('laporan.neraca')->with(compact('date_now', 'arr_aktivas', 'total', 'arr_pasivas'));
	}

	public function buku_besar() {
		$date_now = date('Y-m-d');
		return view('laporan.buku_besar')->with(compact('date_now'));
	}

	public function cari_info_buku_besar(Request $request) {
		$tgl_awal = '2021-01-01';//$request->tgl_awal;
        $tgl_akhir = $request->tgl_akhir;

        $akuns = MasterKodeAkun::on($this->getConnectionName())->where('is_deleted', 0)->get();
        $data = array();
		foreach ($akuns as $key => $val) {
			$getdebit = JurnalUmumDetail::on($this->getConnectionName())->select(DB::RAW("SUM(debit) as total_debit"))
                    ->join("tb_jurnal_umum as j","j.id","id_jurnal")
                    ->whereRaw("tgl_transaksi >= '".$tgl_awal."'")
                	->whereRaw("tgl_transaksi <= '".$tgl_akhir."'")
                    ->whereRaw("id_kode_akun = '".$val->id."'")
                    ->whereRaw("id_apotek = '".session('id_apotek_active')."'")
                    ->whereNull("tb_jurnal_umum_detail.deleted_by")
                    ->whereNull("j.deleted_by")
                    ->first();
            if(empty($getdebit)){ $total_debit = 0; } else { $total_debit = $getdebit->total_debit ; }

            $getkredit = JurnalUmumDetail::on($this->getConnectionName())->select(DB::RAW("SUM(kredit) as total_kredit"))
                    ->join("tb_jurnal_umum as j","j.id","id_jurnal")
                    ->where("id_kode_akun",$val->id)
                    ->whereRaw("tgl_transaksi >= '".$tgl_awal."'")
                	->whereRaw("tgl_transaksi <= '".$tgl_akhir."'")
                    ->whereRaw("id_apotek = '".session('id_apotek_active')."'")
                    ->whereNull("tb_jurnal_umum_detail.deleted_by")
                    ->whereNull("j.deleted_by")
                    ->first();
            if(empty($getkredit)){ $total_kredit = 0; } else { $total_kredit = $getkredit->total_kredit ; }

            $saldo = $total_debit - $total_kredit;
            if($saldo != 0 OR $total_kredit != 0 OR $total_debit != 0) {
				$data[] = array('id' => $val->id, 'kode' => $val->kode, 'nama' => $val->nama, 'total_debit' => $total_debit, 'total_kredit' => $total_kredit, 'saldo_akhir' => $saldo);
			}
		}

		$string = '<table class="table table-sm" style="margin-top: 30px;width:100%!important;">
                    <thead>
                        <tr>
                            <th class="bg-secondary" width="55%!important;" colspan="4">Akun</th>
                            <th class="bg-secondary text-right" width="15%!important;">Debet</th>
                            <th class="bg-secondary text-right" width="15%!important;">Kredit</th>
                            <th class="bg-secondary text-right" width="15%!important;">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>';
        foreach ($data as $key => $val) {
        	$saldo = number_format($val['saldo_akhir']);
        	$debit = number_format($val['total_debit']);
        	$kredit = number_format($val['total_kredit']);
        	$string .= '<tr class="clickable" data-toggle="collapse" data-target="#group-of-rows-'.$val['id'].'" aria-expanded="false" aria-controls="group-of-rows-1">';
        	$string .= '<td colspan="4" width="40%" class="bg-secondary disabled color-palette"><span style="font-size:10pt;"><i class="expandable-table-caret fas fa-caret-right fa-fw"></i><a href="" class="text-white">('.$val['kode'].') '.$val['nama'].'</a><span></td>';
        	$string .= '<td width="15%" class="bg-secondary disabled color-palette text-right"><span style="font-size:10pt;"><b>'.$debit.'</b></span></td>';
        	$string .= '<td width="15%" class="bg-secondary disabled color-palette text-right"><span style="font-size:10pt;"><b>'.$kredit.'</b></span></td>';
        	$string .= '<td width="15%" class="bg-secondary disabled color-palette text-right"><span style="font-size:10pt;"><b>'.$saldo.'</b></span></td>';
        	$string .= '</tr>';

    		$string .= '<tbody id="group-of-rows-'.$val['id'].'" class="collapse">
		                <tr>
	                        <td width="15%!important;"><span style="font-size:10pt;" class="text-info">Tanggal</span></td>
	                        <td width="10%!important;"><span style="font-size:10pt;" class="text-info">Transaksi</span></td>
	                        <td width="10%!important;"><span style="font-size:10pt;" class="text-info">Nomor</span></td>
	                        <td width="20%!important;"><span style="font-size:10pt;" class="text-info">Keterangan</span></td>
	                        <td class="text-right" width="15%!important;"><span style="font-size:10pt;" class="text-info">Debet</span></td>
	                        <td class="text-right" width="15%!important;"><span style="font-size:10pt;" class="text-info">Kredit</span></td>
	                        <td class="text-right" width="15%!important;"><span style="font-size:10pt;" class="text-info">Saldo</span></td>
		                </tr>';

		    $date_before = date($tgl_awal, strtotime(' -1 day'));
		    $getdebit = JurnalUmumDetail::on($this->getConnectionName())->select(DB::RAW("SUM(debit) as total_debit"))
                    ->join("tb_jurnal_umum as j","j.id","id_jurnal")
                    ->whereRaw("tgl_transaksi < '".$date_before."'")
                    ->whereRaw("id_kode_akun = '".$val['id']."'")
                    ->whereRaw("id_apotek = '".session('id_apotek_active')."'")
                    ->whereNull("tb_jurnal_umum_detail.deleted_by")
                    ->whereNull("j.deleted_by")
                    ->first();

            if(empty($getdebit)){ $total_debit = 0; } else { $total_debit = $getdebit->total_debit ; }

            $getkredit = JurnalUmumDetail::on($this->getConnectionName())->select(DB::RAW("SUM(kredit) as total_kredit"))
                    ->join("tb_jurnal_umum as j","j.id","id_jurnal")
                    ->where("id_kode_akun",$val['id'])
                    ->whereRaw("tgl_transaksi < '".$date_before."'")
                    ->whereRaw("id_apotek = '".session('id_apotek_active')."'")
                    ->whereNull("tb_jurnal_umum_detail.deleted_by")
                    ->whereNull("j.deleted_by")
                    ->first();
            if(empty($getkredit)){ $total_kredit = 0; } else { $total_kredit = $getkredit->total_kredit ; }

            $saldo_awal = $total_debit - $total_kredit;
            $saldo_awal_format = number_format($saldo_awal);
        	$total_debit = number_format($total_debit);
        	$total_kredit = number_format($total_kredit);
        	
            $string .= '<tr>
	                        <td width="10%!important;"><span style="font-size:10pt;">'.$date_before.'</span></td>
	                        <td width="13%!important;"><span style="font-size:10pt;">Saldo Awal</span></td>
	                        <td width="10%!important;"><span style="font-size:10pt;"></span></td>
	                        <td width="22%!important;"><span style="font-size:10pt;"></span></td>
	                        <td class="text-right" width="15%!important;"><span style="font-size:10pt;"></span></td>
	                        <td class="text-right" width="15%!important;"><span style="font-size:10pt;"></span></td>
	                        <td class="text-right" width="15%!important;"><span style="font-size:10pt;">'.$saldo_awal_format.'</span></td>
		                </tr>';

		    $transaksis = JurnalUmumDetail::on($this->getConnectionName())->select(
                    "tb_jurnal_umum_detail.id",
                    "id_jurnal",
                    "id_kode_akun",
                    "id_jenis_transaksi",
                    "akun.nama as nama_akun",
                    "deskripsi",
                    "debit",
                    "kredit",
                    "tb_jurnal_umum_detail.kode_referensi",
                    "j.no_transaksi",
                    "j.tgl_transaksi",
                    "j.is_tutup_buku",
                    "ap.nama_panjang"
                )
                ->join("tb_m_kode_akun as akun","akun.id","tb_jurnal_umum_detail.id_kode_akun")
                ->join("tb_jurnal_umum as j","j.id","tb_jurnal_umum_detail.id_jurnal")
                ->join("tb_m_apotek as ap","ap.id","j.id_apotek")
                ->where("id_kode_akun",$val['id'])
                ->where("j.id_apotek",session('id_apotek_active'))
                ->where("j.tgl_transaksi", ">=", $tgl_awal)
               	->where("j.tgl_transaksi", "<=", $tgl_akhir)
                ->whereNull("tb_jurnal_umum_detail.deleted_by")
                ->whereNull("j.deleted_by")->get();

            $jum = count($transaksis);
            if($jum > 0) {
            	foreach ($transaksis as $x => $xx) {
            		$saldo = $saldo_awal + $xx->debit - $xx->kredit;
            		$x_saldo = number_format($saldo);
            		$x_debit = number_format($xx->debit);
        			$x_kredit = number_format($xx->kredit);
            		$string .= '<tr>
	                        <td width="10%!important;"><span style="font-size:10pt;">'.$xx->tgl_transaksi.'</span></td>
	                        <td width="13%!important;"><span style="font-size:10pt;">Transaksi Penjualan</span></td>
	                        <td width="10%!important;"><span style="font-size:10pt;">'.$xx->no_transaksi.'</span></td>
	                        <td width="22%!important;"><span style="font-size:10pt;">Faktur Penjualan#ID</span></td>
	                        <td class="text-right" width="15%!important;"><span style="font-size:10pt;">'.$x_debit.'</span></td>
	                        <td class="text-right" width="15%!important;"><span style="font-size:10pt;">'.$x_kredit.'</span></td>
	                        <td class="text-right" width="15%!important;"><span style="font-size:10pt;">'.$x_saldo.'</span></td>
		                </tr>';
            	}
            } else {
            	#get last saldo akhir
            }

            $string .= '</tbody>';
        }
        $string .= '</tbody>
                    </table>';

		return response()->json($string); 
	}
	public function jurnal() {
		$date_now = date('Y-m-d');
		return view('laporan.jurnal')->with(compact('date_now'));
	}

	public function cari_info_jurnal(Request $request) {
        $tgl_awal = '2021-01-01';//$request->tgl_awal;
        $tgl_akhir = $request->tgl_akhir;

        $data = array();
		$transaksis = JurnalUmum::on($this->getConnectionName())->select(['*'])
                ->whereRaw("tgl_transaksi >= '".$tgl_awal."'")
                ->whereRaw("tgl_transaksi <= '".$tgl_akhir."'")
                ->whereRaw("id_apotek = '".session('id_apotek_active')."'")
                ->whereNull("deleted_by")
                ->get();

       	$string = '<table class="table table-sm" style="margin-top: 30px;width:100%!important;">
                        <thead>
                            <tr>
                                <th class="bg-secondary" width="55%!important;">Akun</th>
                                <th class="bg-secondary" width="15%!important;"></th>
                                <th class="bg-secondary text-right" width="15%!important;">Debet</th>
                                <th class="bg-secondary text-right" width="15%!important;">Kredit</th>
                            </tr>
                        </thead>
                        <tbody>';
        $grand_total_debit = 0;
       	$grand_total_kredit = 0;
        foreach ($transaksis as $key => $val) {
        	$string .= '<tr>';
        	$string .= '<td colspan="4" width="100%" class="bg-info"><a href="">Transaksi #'.$val->no_transaksi.'</a> | '.$val->tgl_transaksi.' (created on '.$val->created_at.')</td>';
        	$string .= '</tr>';

        	$total_debit = 0;
        	$total_kredit = 0;
        	$details = $val->detailjurnal;
        	foreach ($details as $x => $obj) {
        		$debit = number_format($obj->debit);
        		$kredit = number_format($obj->kredit);
        		$string .= '<tr>';
	        	$string .= '<td width="55%"><span style="font-size:10pt;"><a href="" class="text-info">('.$obj->kode_akun->kode.') - '.$obj->kode_akun->nama.'</a><span><br><span style="font-size:10pt;">Faktur Penjualan#'.$obj->kode_referensi.'</span></td>';
	        	$string .= '<td width="15%"></td>';
	        	$string .= '<td width="15%" class="text-right"><span style="font-size:10pt;">'.$debit.'</span></td>';
	        	$string .= '<td width="15%" class="text-right"><span style="font-size:10pt;">'.$kredit.'</span></td>';
	        	$string .= '</tr>';

	        	$total_debit = $total_debit + $obj->debit;
	        	$total_kredit = $total_kredit + $obj->kredit;
        	}

        	$grand_total_debit = $grand_total_debit + $total_debit;
	        $grand_total_kredit = $grand_total_kredit + $total_kredit;

        	$total_debit = number_format($total_debit);
        	$total_kredit = number_format($total_kredit);
        	$string .= '<tr style="border:none;">';
	    	$string .= '<td width="55%"></td>';
	    	$string .= '<td width="15%" class="text-right"><b>Total</b></td>';
	    	$string .= '<td width="15%" class="text-right"><b>'.$total_debit.'</b></td>';
	    	$string .= '<td width="15%" class="text-right"><b>'.$total_kredit.'</b></td>';
	    	$string .= '</tr>';
        }

        $grand_total_debit = number_format($grand_total_debit);
        $grand_total_kredit = number_format($grand_total_kredit);
        $string .= '<tr style="border:none;">';
    	$string .= '<td width="55%"></td>';
    	$string .= '<td width="15%" class="text-right"><b>Grand Total</b></td>';
    	$string .= '<td width="15%" class="text-right"><b>'.$grand_total_debit.'</b></td>';
    	$string .= '<td width="15%" class="text-right"><b>'.$grand_total_kredit.'</b></td>';
    	$string .= '</tr>';
        
        $string .= '</tbody>
                    </table>';
        return response()->json($string); 
    }

	public function laba_rugi() {
		$date_now = date('Y-m-d');

        $pendapatans = MasterKategoriAkun::on($this->getConnectionName())->whereIn('kode', [13,17])
                                    ->where('is_deleted', 0)
                                    ->get();

        $arr_pendapatans = array();
        $total_pendapatan = 0;
        foreach ($pendapatans as $key => $val) {
            $akuns = MasterKodeAkun::on($this->getConnectionName())->where('id_kategori_akun', $val->id)
                                    ->where('is_deleted', 0)
                                    ->get();
            $i_val = 0;
            $arr_akuns = array();
            $total_akun = 0;
            foreach ($akuns as $x => $obj) {
                $getdebit = JurnalUmumDetail::on($this->getConnectionName())->select(DB::RAW("SUM(debit) as total_debit"))
                        ->join("tb_jurnal_umum as j","j.id","id_jurnal")
                        ->whereRaw("id_kode_akun = '".$obj->id."'")
                        ->whereRaw("id_apotek = '".session('id_apotek_active')."'")
                        ->whereNull("tb_jurnal_umum_detail.deleted_by")
                        ->whereNull("j.deleted_by")
                        ->first();

                if(empty($getdebit)){ $total_debit = 0; } else { $total_debit = $getdebit->total_debit ; }

                $getkredit = JurnalUmumDetail::on($this->getConnectionName())->select(DB::RAW("SUM(kredit) as total_kredit"))
                        ->join("tb_jurnal_umum as j","j.id","id_jurnal")
                        ->where("id_kode_akun",$obj->id)
                        ->whereRaw("id_apotek = '".session('id_apotek_active')."'")
                        ->whereNull("tb_jurnal_umum_detail.deleted_by")
                        ->whereNull("j.deleted_by")
                        ->first();

                if(empty($getkredit)){ $total_kredit = 0; } else { $total_kredit = $getkredit->total_kredit ; }

                $saldo = $total_debit - $total_kredit;
                $total_akun = $total_akun + $saldo;
                if($saldo != 0) {
                    $saldo = number_format($saldo);
                    $arr_akuns[] = array('kode' => $obj->kode, 'nama' => $obj->nama, 'saldo_akhir' => $saldo);
                    $i_val++;
                }
            }

            $total_pendapatan = $total_pendapatan + $total_akun;
            if($i_val > 0) {
                $arr_pendapatans[] = array('id' => $val->id, 'kode' => $val->kode, 'nama' => $val->nama, 'akuns' => $arr_akuns, 'total_akun' => $total_akun);
            }
        }

        $biayas = MasterKategoriAkun::on($this->getConnectionName())->whereIn('kode', [14,15,16,18])
                                    ->where('is_deleted', 0)
                                    ->get();

        $arr_biayas = array();
        $total_biaya = 0;
        foreach ($biayas as $key => $val) {
            $akuns = MasterKodeAkun::on($this->getConnectionName())->where('id_kategori_akun', $val->id)
                                    ->where('is_deleted', 0)
                                    ->get();
            $i_val = 0;
            $arr_akuns = array();
            $total_akun = 0;
            foreach ($akuns as $x => $obj) {
                $getdebit = JurnalUmumDetail::on($this->getConnectionName())->select(DB::RAW("SUM(debit) as total_debit"))
                        ->join("tb_jurnal_umum as j","j.id","id_jurnal")
                        ->whereRaw("id_kode_akun = '".$obj->id."'")
                        ->whereRaw("id_apotek = '".session('id_apotek_active')."'")
                        ->whereNull("tb_jurnal_umum_detail.deleted_by")
                        ->whereNull("j.deleted_by")
                        ->first();

                if(empty($getdebit)){ $total_debit = 0; } else { $total_debit = $getdebit->total_debit ; }

                $getkredit = JurnalUmumDetail::on($this->getConnectionName())->select(DB::RAW("SUM(kredit) as total_kredit"))
                        ->join("tb_jurnal_umum as j","j.id","id_jurnal")
                        ->where("id_kode_akun",$obj->id)
                        ->whereRaw("id_apotek = '".session('id_apotek_active')."'")
                        ->whereNull("tb_jurnal_umum_detail.deleted_by")
                        ->whereNull("j.deleted_by")
                        ->first();

                if(empty($getkredit)){ $total_kredit = 0; } else { $total_kredit = $getkredit->total_kredit ; }

                $saldo = $total_debit - $total_kredit;
                $total_akun = $total_akun + $saldo;
                if($saldo != 0) {
                    $saldo = number_format($saldo);
                    $arr_akuns[] = array('kode' => $obj->kode, 'nama' => $obj->nama, 'saldo_akhir' => $saldo);
                    $i_val++;
                }
            }

            $total_biaya = $total_biaya + $total_akun;
            if($i_val > 0) {
                $arr_biayas[] = array('id' => $val->id, 'kode' => $val->kode, 'nama' => $val->nama, 'akuns' => $arr_akuns, 'total_akun' => $total_akun);
            }
        }
        
        $total_profit = $total_pendapatan-$total_biaya;
        $total_profit_format = number_format($total_profit);
        $total = array(
                        'total_pendapatan' => $total_pendapatan, 
                        'total_biaya' => $total_biaya
                    );
        
        return view('laporan.laba_rugi')->with(compact('date_now', 'arr_pendapatans', 'arr_biayas', 'total', 'total_profit'));
	}

	public function trial_balance() {
		$date_now = date('Y-m-d');
		return view('laporan.trial_balance')->with(compact('date_now'));
	}

	public function arus_kas() {
		$date_now = date('Y-m-d');
		return view('laporan.arus_kas')->with(compact('date_now'));
	}

	public function ringkasan_bisnis() {
		$date_now = date('Y-m-d');
		return view('laporan.ringkasan_bisnis')->with(compact('date_now'));
	}

	public function perubahan_modal() {
		$date_now = date('Y-m-d');
		return view('laporan.perubahan_modal')->with(compact('date_now'));
	}

	public function anggaran_laba_rugi() {
		$date_now = date('Y-m-d');
		return view('laporan.anggaran_laba_rugi')->with(compact('date_now'));
	}

	public function manajemen_anggaran() {
		$date_now = date('Y-m-d');
		return view('laporan.manajemen_anggaran')->with(compact('date_now'));
	}

	# laporan penjualan
	public function daftar_penjualan() {
		echo "under construction";
	}

	public function penjualan_per_pelanggan() {
		echo "under construction";
	}

	public function piutang_pelanggan() {
		echo "under construction";
	}

	public function usia_piutang() {
		echo "under construction";
	}

	public function pengiriman_penjualan() {
		echo "under construction";
	}

	public function penjulan_per_produk() {
		echo "under construction";
	}

	public function penyelesaian_pemesanan_penjualan() {
		echo "under construction";
	}

	public function profitabilitas_produk() {
		echo "under construction";
	}

	public function daftar_performa_invoice() {
		echo "under construction";
	}

	public function daftar_tukar_faktur() {
		echo "under construction";
	}

	# laporan pembelian
	public function daftar_pembelian() {
		echo "under construction";
	}

	public function pembelian_per_suplier() {
		echo "under construction";
	}

	public function hutang_suplier() {
		echo "under construction";
	}

	public function daftar_pengeluaran() {
		echo "under construction";
	}

	public function rincian_pengeluaran() {
		echo "under construction";
	}

	public function usia_hutang() {
		echo "under construction";
	}

	public function pengiriman_pembelian() {
		echo "under construction";
	}

	public function pembelian_per_produk() {
		echo "under construction";
	}

	public function penyelesaian_pemesanan_pembelian() {
		echo "under construction";
	}

	# laporan produk
	public function ringkasan_persediaan_barang_fifo() {
		echo "under construction";
	}

	public function kuantitas_stok_gudang() {
		echo "under construction";
	}

	public function nilai_persediaan_barang_fifo() {
		echo "under construction";
	}

	public function pergerakan_barang_gudang() {
		echo "under construction";
	}

	public function rincian_persediaan_barang() {
		echo "under construction";
	}

	
	# laporan aset
	public function ringkasan_aset_tetap() {
		echo "under construction";
	}

	public function detail_aset_tetap() {
		echo "under construction";
	}

	public function sold_and_dispossal_asset() {
		echo "under construction";
	}

	# laporan bank
	public function rekonsiliasis() {
		echo "under construction";
	}

	public function mutasi_rekening() {
		echo "under construction";
	}

	# laporan pajak
	public function pajak_pemotongan() {
		echo "under construction";
	}

	public function pajak_penjualan() {
		echo "under construction";
	}
}
