<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\MasterMember;
use App\TransaksiPenjualan;
use App\TransaksiPenjualanDetail;
use DB;
use Datatables;
use App\Traits\DynamicConnectionTrait;

class CRMController extends Controller
{
    use DynamicConnectionTrait;
    
    public function index() {

    }

    public function create() {

    }

    public function store(Request $request) {

    }

    public function show($id) {

    }

    public function edit($id) {

    } 

    public function update($id, Request $request) {

    }
    public function crm_member() {
        return view('crm.crm_member');
    }

    public function list_crm_member(Request $request) {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::connection($this->getConnection())->statement(DB::raw('set @rownum = 0'));
        $data = MasterMember::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_m_member.*'])
        ->where(function($query) use($request){
            $query->where('tb_m_member.is_deleted','=','0');

            if($request->tgl_awal != "") {
                $tgl_awal       = date('d',strtotime($request->tgl_awal));
                $month = date('m',strtotime($request->tgl_awal));
                $day = date('d',strtotime($request->tgl_awal));
                if(substr($day, 0, 1) == 0) {
                    $day = substr($day, 1, 1);
                }
                $query->whereMonth('tb_m_member.tgl_lahir',$month);
                $query->whereDay('tb_m_member.tgl_lahir','>=', $day);
            }

            if($request->tgl_akhir != "") {
                $tgl_akhir      = date('d',strtotime($request->tgl_akhir));
                $month = date('m',strtotime($request->tgl_akhir));
                $day = date('d',strtotime($request->tgl_akhir));
                if(substr($day, 0, 1) == 0) {
                    $day = substr($day, 1, 1);
                }
                $query->whereMonth('tb_m_member.tgl_lahir',$month);
                $query->whereDay('tb_m_member.tgl_lahir','<=', $day);
            }
        });
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->orwhere('nama','LIKE','%'.$request->get('search')['value'].'%');
            });
        }) 
        ->editcolumn('jum_kunjungan', function($data) use($request){
            $jum_kunjungan = TransaksiPenjualan::on($this->getConnectionName())->where('is_deleted', 0)->where('id_pasien', $data->id)->count();
            return $jum_kunjungan. ' kunjungan';
        }) 
        ->editcolumn('poin', function($data) use($request){
            $total_belanja = TransaksiPenjualanDetail::select([DB::raw('SUM(harga_jual*jumlah) as total')])
                                ->join('tb_nota_penjualan as a', 'a.id', '=', 'tb_detail_nota_penjualan.id_nota')
                                ->where('a.is_deleted', 0)->where('a.id_pasien', $data->id)->first();
            $poin = $total_belanja->total/5000;
            return $poin.' poin';
        }) 
        ->editcolumn('tempat_lahir', function($data) use($request){
            return $data->tempat_lahir.'/'.$data->tgl_lahir;
        }) 
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
                $btn .= '<span class="btn btn-primary" onClick="edit_data('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span>';
                $btn .= '<a href="'.url('/member/detail/'.$data->id).'" title="Lihat detail akun" class="btn btn-info btn-sm"><span data-toggle="tooltip" data-placement="top" title="Lihat detail akun"><i class="fa fa-eye"></i></span></a>';
                // cek tgl lahir 
                /*$bulan_now = date('m');
                $date_now = date('d');
                $bulan_lahir = date("m",strtotime($data->tgl_lahir));
                $date_lahir = date("d",strtotime($data->tgl_lahir));
                if($bulan_now == $bulan_lahir AND $date_now == $date_lahir) {
                    $btn .= '<span class="btn btn-default" onClick="generate_vouhcer('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Generate Voucher"><i class="fa fa-sync"></i></span>';
                }*/


                $btn .= '<a href="'.url('/member/detail_voucher/'.$data->id).'" title="Lihat detail voucher" class="btn btn-secondary btn-sm"><span data-toggle="tooltip" data-placement="top" title="Lihat detail voucher"><i class="fa fa-list"></i></span></a>';

                $btn .= '<span class="btn btn-danger" onClick="delete_data('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['action', 'jum_kunjungan', 'poin'])
        ->addIndexColumn()
        ->make(true);  
    }    
}
