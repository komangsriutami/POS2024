<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\TransaksiPenjualanClosing;
use App;
use Datatables;
use DB;
use Auth;
use App\Traits\DynamicConnectionTrait;

class T_PenjualanClosingController extends Controller
{
    use DynamicConnectionTrait;
    public function index() {

    }

    public function create() {

    }

    public function store(Request $request) {
    	$penjualan_closing = new TransaksiPenjualanClosing;
        $penjualan_closing->setDynamicConnection();
        $penjualan_closing->fill($request->except('_token'));
        $penjualan_closing->id_apotek_nota = session('id_apotek_active');
        // dd($penjualan_closing);exit();
       
        $tanggal = $request->tanggal;
        $id_user = $request->id_user;

        if($request->total_penjualan_cn_debet == '') {
            $penjualan_closing->total_penjualan_cn_debet = 0;
        }

        if($request->total_penjualan_cn_cash == '') {
            $penjualan_closing->total_penjualan_cn_cash = 0;
        }

        if($request->total_apd == '') {
            $penjualan_closing->total_apd = 0;
        }

        if($request->total_switch_cash == '') {
            $penjualan_closing->total_switch_cash = 0;
        }

        /*$tgl_awal_baru = $tanggal.' 00:00:00';
        $tgl_akhir_baru = $tanggal.' 23:59:59';*/

        /*$cari  = TransaksiPenjualanClosing::whereDate('created_at','>=', $tgl_awal_baru)
                        ->whereDate('created_at','<=', $tgl_akhir_baru)->get();
        if(!empty($cari)) {
            $date = date('Y-m-d', strtotime($tanggal)).' 15:00:00';
        } else {
            $date = date('Y-m-d', strtotime($tanggal)).' 22:00:00';
        }*/

        $penjualan_closing->created_at = date('Y-m-d H:i:s');
        $penjualan_closing->created_by = $id_user;

        $validator = $penjualan_closing->validate();
        if($validator->fails()){
            //print_r($validator);
            //exit();
            echo json_encode(array('status' => 0));
        }else{
            $penjualan_closing->save_plus();
            echo json_encode(array('status' => 1));
        }
    }

    public function edit($id) {

    }

    public function update(Request $request, $id) {
    	$penjualan_closing = TransaksiPenjualanClosing::on($this->getConnectionName())->find($id);
        $penjualan_closing->fill($request->except('_token'));
       
        $tanggal = $request->tanggal;
        $id_user = $request->id_user;

        /*$tgl_awal_baru = $tanggal.' 00:00:00';
        $tgl_akhir_baru = $tanggal.' 23:59:59';

        $cari  = TransaksiPenjualanClosing::whereDate('created_at','>=', $tgl_awal_baru)
                        ->whereDate('created_at','<=', $tgl_akhir_baru)->get();
        if(!empty($cari)) {
            $date = date('Y-m-d', strtotime($tanggal)).' 15:00:00';
        } else {
            $date = date('Y-m-d', strtotime($tanggal)).' 22:00:00';
        }*/

        $penjualan_closing->updated_at = date('Y-m-d H:i:s');
        $penjualan_closing->updated_by = $id_user;

        $validator = $penjualan_closing->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            $penjualan_closing->save_plus();
            echo json_encode(array('status' => 1));
        }
    } 

    public function destroy($id) {

    }
}
