<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterMember;
use App\MasterAgama;
use App\MasterGolonganDarah;
use App\MasterJenisKelamin;
use App\MasterKewarganegaraan;
use App\MasterGroupApotek;
use App\MasterMemberTipe;
use App\MasterKabupaten;
use App\TransaksiPenjualan;
use App\TransaksiPenjualanDetail;
use App;
use Datatables;
use DB;
use Excel;
use Auth;

class M_MemberController extends Controller
{
    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function index()
    {
        return view('member.index');
    }


    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function list_member(Request $request)
    {
    	$order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        $super_admin = session('super_admin');
        DB::statement(DB::raw('set @rownum = 0'));
        $data = MasterMember::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_m_member.*'])
        ->where(function($query) use($request, $super_admin){
            $query->where('tb_m_member.is_deleted','=','0');
            if($super_admin == 0) {
                $query->where('tb_m_member.id_group_apotek', Auth::user()->id_group_apotek);
            }
        });
        
        $datatables = Datatables::of($data);
        return $datatables  
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->orwhere('nama','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('username','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('telepon','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('email','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->editcolumn('id_group_apotek', function($data){
            return $data->group_apotek->nama_singkat; 
        }) 
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .= '<span class="btn btn-primary" onClick="edit_data('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span>';
            $btn .= '<span class="btn btn-danger" onClick="delete_member('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['id_group_apotek', 'action'])
        ->addIndexColumn()
        ->make(true);  
    }

    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function create()
    {
        $member = new MasterMember;

        $jenis_kelamins = MasterJenisKelamin::where('is_deleted', 0)->pluck('jenis_kelamin', 'id');
        $jenis_kelamins->prepend('-- Pilih Jenis Kelamin --','');

        $kewarganegaraans = MasterKewarganegaraan::where('is_deleted', 0)->pluck('kewarganegaraan', 'id');
        $kewarganegaraans->prepend('-- Pilih Kewarganegaraan --','');

        $agamas = MasterAgama::where('is_deleted', 0)->pluck('agama', 'id');
        $agamas->prepend('-- Pilih Agama --','');

        $golongan_darahs = MasterGolonganDarah::where('is_deleted', 0)->pluck('golongan_darah', 'id');
        $golongan_darahs->prepend('-- Pilih Golongan Darah --','');

        $group_apoteks      = MasterGroupApotek::where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $group_apoteks->prepend('-- Pilih Group Apotek --','');

        $tipe_members      = MasterMemberTipe::where('is_deleted', 0)->pluck('nama', 'id');
        $tipe_members->prepend('-- Pilih Tipe Member --','');

        $kabupatens = MasterKabupaten::where('is_deleted', 0)->pluck('nama', 'id');
        $kabupatens->prepend('-- Pilih Kabupaten --','');

        return view('member.create')->with(compact('member', 'jenis_kelamins', 'agamas', 'kewarganegaraans', 'golongan_darahs', 'group_apoteks', 'tipe_members', 'kabupatens'));
    }

    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function store(Request $request)
    {
        $member = new MasterMember;
        $member->fill($request->except('_token', 'password'));
        $member->password = md5($request->password);
        $member->activated = 1;

        $jenis_kelamins = MasterJenisKelamin::where('is_deleted', 0)->pluck('jenis_kelamin', 'id');
        $jenis_kelamins->prepend('-- Pilih Jenis Kelamin --','');

        $kewarganegaraans = MasterKewarganegaraan::where('is_deleted', 0)->pluck('kewarganegaraan', 'id');
        $kewarganegaraans->prepend('-- Pilih Kewarganegaraan --','');

        $agamas = MasterAgama::where('is_deleted', 0)->pluck('agama', 'id');
        $agamas->prepend('-- Pilih Agama --','');

        $golongan_darahs = MasterGolonganDarah::where('is_deleted', 0)->pluck('golongan_darah', 'id');
        $golongan_darahs->prepend('-- Pilih Golongan Darah --','');

        $group_apoteks      = MasterGroupApotek::where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $group_apoteks->prepend('-- Pilih Group Apotek --','');

        $tipe_members      = MasterMemberTipe::where('is_deleted', 0)->pluck('nama', 'id');
        $tipe_members->prepend('-- Pilih Tipe Member --','');

        $kabupatens = MasterKabupaten::where('is_deleted', 0)->pluck('nama', 'id');
        $kabupatens->prepend('-- Pilih Kabupaten --','');

        $validator = $member->validateFP();
        if($validator->fails()){
            return view('member.create')->with(compact('member', 'jenis_kelamins', 'kewarganegaraans', 'agamas', 'golongan_darahs', 'group_apoteks', 'tipe_members', 'kabupatens'))->withErrors($validator);
        }else{
            $member->tgl_lahir = date('Y-m-d', strtotime($member->tgl_lahir));
            $member->created_by = Auth::user()->id;
            $member->save();
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('member');
        }
    }

    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function show($id)
    {
        //
    }

    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function edit($id)
    {
        $member        = MasterMember::find($id);

        $jenis_kelamins = MasterJenisKelamin::where('is_deleted', 0)->pluck('jenis_kelamin', 'id');
        $jenis_kelamins->prepend('-- Pilih Jenis Kelamin --','');

        $kewarganegaraans = MasterKewarganegaraan::where('is_deleted', 0)->pluck('kewarganegaraan', 'id');
        $kewarganegaraans->prepend('-- Pilih Kewarganegaraan --','');

        $agamas = MasterAgama::where('is_deleted', 0)->pluck('agama', 'id');
        $agamas->prepend('-- Pilih Agama --','');

        $golongan_darahs = MasterGolonganDarah::where('is_deleted', 0)->pluck('golongan_darah', 'id');
        $golongan_darahs->prepend('-- Pilih Golongan Darah --','');

        $group_apoteks      = MasterGroupApotek::where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $group_apoteks->prepend('-- Pilih Group Apotek --','');

        $tipe_members      = MasterMemberTipe::where('is_deleted', 0)->pluck('nama', 'id');
        $tipe_members->prepend('-- Pilih Tipe Member --','');

        $kabupatens = MasterKabupaten::where('is_deleted', 0)->pluck('nama', 'id');
        $kabupatens->prepend('-- Pilih Kabupaten --','');

        return view('member.edit')->with(compact('member', 'jenis_kelamins', 'kewarganegaraans', 'agamas', 'golongan_darahs', 'group_apoteks', 'tipe_members', 'kabupatens'));
    }

    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function update(Request $request, $id)
    {
        $member = MasterMember::find($id);
        $member->fill($request->except('_token', 'password'));

        if(isset($request->is_ganti_password)) {
            if($request->is_ganti_password_val == 1) {
                $member->password = md5($request->password);
            }
        } 

        $validator = $member->validateFP();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            $member->tgl_lahir = date('Y-m-d', strtotime($member->tgl_lahir));
            $member->updated_by = Auth::user()->id;
            $member->save();
            echo json_encode(array('status' => 1));
        }
    }

    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function destroy($id)
    {
        $member = MasterMember::find($id);
        $member->is_deleted = 1;
        if($member->save()){
            echo 1;
        }else{
            echo 0;
        }
    }

    public function detail($id) {
        $data_ = MasterMember::find($id);
        $jum_kunjungan = TransaksiPenjualan::where('is_deleted', 0)->where('id_pasien', $data_->id)->count();
        $total_belanja = TransaksiPenjualanDetail::select([DB::raw('SUM(harga_jual*jumlah) as total')])
                                ->join('tb_nota_penjualan as a', 'a.id', '=', 'tb_detail_nota_penjualan.id_nota')
                                ->where('a.is_deleted', 0)
                                ->where('tb_detail_nota_penjualan.is_deleted', 0)
                                ->where('a.id_pasien', $data_->id)
                                ->first();
                                
        $poin = $total_belanja->total/5000;  // ini custome jumlah poinnya
        return view('member._detail')->with(compact('data_', 'poin', 'jum_kunjungan', 'total_belanja'));
    }

    public function list_data_transaksi(Request $request) {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::statement(DB::raw('set @rownum = 0'));
        $data = TransaksiPenjualanDetail::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_detail_nota_penjualan.*', 'a.tgl_nota'])
        ->join('tb_nota_penjualan as a', 'a.id', '=', 'tb_detail_nota_penjualan.id_nota')
        ->where(function($query) use($request){
            $query->where('a.is_deleted','=','0');
            $query->where('a.total_bayar','>',0);
            $query->where('a.id_pasien','=', $request->id);
        });
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->orwhere('a.id','LIKE','%'.$request->get('search')['value'].'%');
            });
        }) 
        ->editcolumn('tgl_transaksi', function($data) use($request){
            return $data->tgl_nota;
        }) 
        ->editcolumn('diskon', function($data) use($request){
            return 0;
        }) 
        ->editcolumn('id_obat', function($data) use($request){
            $nama = '';
            return $nama = $data->obat->nama;
        }) 
        ->editcolumn('harga_jual', function($data) use($request){
            $total = $data->harga_jual;
            return 'Rp. '.number_format($total);
        }) 
        ->editcolumn('total', function($data) use($request){
            $total = $data->jumlah * $data->harga_jual;
            return 'Rp. '.number_format($total);
        }) 
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
                $btn .= '<a href="'.url('/jurnalumum/'.$data->id.'/edit?id_kode_akun='.$data->id_kode_akun.'').'" title="Edit Data" class="btn btn-info btn-sm"><span data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span></a>';
                $btn .= '<span class="btn btn-danger" onClick="delete_data('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['action', 'no_transaksi', 'kontak', 'is_tutup_buku'])
        ->addIndexColumn()
        ->make(true);  
    }

}
