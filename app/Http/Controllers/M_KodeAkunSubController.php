<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterKodeAkun;
use App\MasterKodeAkunSub;
use Auth;
use App;
use Datatables;
use DB;
class M_KodeAkunSubController extends Controller
{
    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 29/05/2020
        =======================================================================================
    */
    public function index()
    {
        return view('sub_kode_akuntansi.index');
    }


    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 29/05/2020
        =======================================================================================
    */
    public function list_sub_kode_akuntansi(Request $request)
    {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::statement(DB::raw('set @rownum = 0'));
        $data = MasterKodeAkunSub::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_m_sub_kode_akun.*'])
        ->where(function($query) use($request){
            $query->where('tb_m_sub_kode_akun.is_deleted','=','0');
        });
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->orwhere('nama','LIKE','%'.$request->get('search')['value'].'%');
            });
        }) 
        ->editcolumn('id_kode_akun', function($data) use($request){
            return $data->kode_akun->kode;
        }) 
        ->editcolumn('nama_akun', function($data) use($request){
            return $data->kode_akun->nama;
        }) 
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
                $btn .= '<span class="btn btn-primary" onClick="edit_data('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span>';
                $btn .= '<span class="btn btn-danger" onClick="delete_sub_kode_akuntansi('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['action'])
        ->addIndexColumn()
        ->make(true);  
    }

    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 29/05/2020
        =======================================================================================
    */
    public function create()
    {
    	$sub_kode_akuntansi = new MasterKodeAkunSub;

    	$kode_akuntansis      = MasterKodeAkun::where('is_deleted', 0)->pluck('nama', 'id');
        $kode_akuntansis->prepend('-- Pilih Kode Akuntansi --','');

        return view('sub_kode_akuntansi.create')->with(compact('sub_kode_akuntansi', 'kode_akuntansis'));
    }

    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 29/05/2020
        =======================================================================================
    */
    public function store(Request $request)
    {
        $sub_kode_akuntansi = new MasterKodeAkunSub;
        $sub_kode_akuntansi->fill($request->except('_token'));

        $validator = $sub_kode_akuntansi->validate();
        if($validator->fails()){
        	$kode_akuntansis      = MasterKodeAkun::where('is_deleted', 0)->pluck('nama', 'id');
        	$kode_akuntansis->prepend('-- Pilih Kode Akuntansi --','');

            return view('sub_kode_akuntansi.create')->with(compact('sub_kode_akuntansi', 'kode_akuntansis'))->withErrors($validator);
        }else{
            $sub_kode_akuntansi->save_plus();
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('sub_kode_akuntansi');
        }
    }

    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 29/05/2020
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
        Date    : 29/05/2020
        =======================================================================================
    */
    public function edit($id)
    {
        $sub_kode_akuntansi = MasterKodeAkunSub::find($id);

        $kode_akuntansis      = MasterKodeAkun::where('is_deleted', 0)->pluck('nama', 'id');
        $kode_akuntansis->prepend('-- Pilih Kode Akuntansi --','');

        return view('sub_kode_akuntansi.edit')->with(compact('sub_kode_akuntansi', 'kode_akuntansis'));
    }

    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 29/05/2020
        =======================================================================================
    */
    public function update(Request $request, $id)
    {
        $sub_kode_akuntansi = MasterKodeAkunSub::find($id);
        $sub_kode_akuntansi->fill($request->except('_token'));

        $validator = $sub_kode_akuntansi->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            $sub_kode_akuntansi->save_edit();
            echo json_encode(array('status' => 1));
        }
    }

    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 29/05/2020
        =======================================================================================
    */
    public function destroy($id)
    {
        $sub_kode_akuntansi = MasterKodeAkun::find($id);
        $sub_kode_akuntansi->is_deleted = 1;
        $sub_kode_akuntansi->deleted_at = date('Y-m-d H:i:s');
        $sub_kode_akuntansi->deleted_by = Auth::user()->id;
        if($sub_kode_akuntansi->save()){
            echo 1;
        } else {
            echo 0;
        }
    }
}
