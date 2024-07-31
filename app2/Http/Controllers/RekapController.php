<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\TransaksiPenjualan;
use App\TransaksiPenjualanDetail;
use App\TransaksiPenjualanClosing;
use App\MasterApotek;
use DB;
use PDF;
use Auth;
use App\Traits\DynamicConnectionTrait;

class RekapController extends Controller
{
    use DynamicConnectionTrait;
    public function omset(Request $request) {
    	$inisial = session('nama_apotek_singkat_active');
    	$id_apotek = session('id_apotek_active');
    	$apotek = MasterApotek::on($this->getConnectionName())->find($id_apotek);

        $date_now = date('Y-m-d');
        $first = date('Y-m-01', strtotime($date_now));
        $end = date('Y-m-t', strtotime($date_now));

        DB::connection($this->getConnection())->statement(DB::raw('set @rownum = 0'));
        $data = TransaksiPenjualanClosing::on($this->getConnectionName())->select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_closing_nota_penjualan.*'])
        ->where(function($query) use($request, $first, $end){
            $query->where('tb_closing_nota_penjualan.id_apotek_nota','=',session('id_apotek_active'));
            if (!empty($request->tgl_awal) && !empty($request->tgl_akhir)) {
                $query->where('tb_closing_nota_penjualan.tanggal','>=', $request->tgl_awal);
                $query->where('tb_closing_nota_penjualan.tanggal','<=', $request->tgl_akhir);
            } else {
                $query->where('tb_closing_nota_penjualan.tanggal','>=', $first);
                $query->where('tb_closing_nota_penjualan.tanggal','<=', $end);
            }
        })
        ->orderBy('tb_closing_nota_penjualan.tanggal', 'DESC')
        ->orderBy('tb_closing_nota_penjualan.id', 'DESC')->get();
				
		$orientation = 'Landscape';

		$pdf = PDF::loadHTML(view('rekap.pdf_omset',compact('data', 'date_now', 'apotek', 'first', 'end')));
        $pdf->setOptions(array(
            'dpi' => 500,
            'image-dpi' => 500,
            'page-size'=> 'A4', 
            'encoding' => 'utf-8', 
            'orientation'=>$orientation,
        ));

        return $pdf->inline('RekapOmset_'.$inisial.'_'.$date_now.'.pdf');
    }
}
