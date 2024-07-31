<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterKodeAkun;
use App\MasterKategoriAkun;
use Auth;
use App;
use Datatables;
use DB;

class M_KodeAkunController extends Controller
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
        return view('kode_akuntansi.index');
    }


    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 29/05/2020
        =======================================================================================
    */
    public function list_kode_akuntansi(Request $request)
    {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::statement(DB::raw('set @rownum = 0'));
        $data = MasterKodeAkun::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_m_kode_akun.*'])
        ->where(function($query) use($request){
            $query->where('tb_m_kode_akun.is_deleted','=','0');
        });
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->orwhere('nama','LIKE','%'.$request->get('search')['value'].'%');
            });
        }) 
        ->editcolumn('kategori_akun', function($data) use($request){
            return $data->kategori->nama;
        }) 
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
                $btn .= '<span class="btn btn-primary" onClick="edit_data('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span>';
                $btn .= '<span class="btn btn-danger" onClick="delete_kode_akuntansi('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['action', 'kategori_akun'])
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
    	$kode_akuntansi = new MasterKodeAkun;

        $kategori = MasterKategoriAkun::where("is_deleted",0)->orderBy('nama')->get();
        // dd($kategori);

        return view('kode_akuntansi.create')->with(compact('kode_akuntansi','kategori'));
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
        $kode_akuntansi = new MasterKodeAkun;
        $kode_akuntansi->fill($request->except('_token'));

        $validator = $kode_akuntansi->validate();
        if($validator->fails()){
            $kategori = MasterKategoriAkun::where("is_deleted",0)->orderBy('nama')->get();
            return view('kode_akuntansi.create')->with(compact('kode_akuntansi','kategori'))->withErrors($validator);
        }else{
            $kode_akuntansi->save_plus();
            session()->flash('success', 'Sukses menyimpan data!');
            // return redirect('kode_akuntansi');
            return redirect('jurnalumum');
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
        $kode_akuntansi = MasterKodeAkun::find($id);

        $kategori = MasterKategoriAkun::where("is_deleted",0)->orderBy('nama')->get();

        return view('kode_akuntansi.edit')->with(compact('kode_akuntansi','kategori'));
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
        $kode_akuntansi = MasterKodeAkun::find($id);
        $kode_akuntansi->fill($request->except('_token'));

        $validator = $kode_akuntansi->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            $kode_akuntansi->save_edit();
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
        $kode_akuntansi = MasterKodeAkun::find($id);
        $kode_akuntansi->is_deleted = 1;
        $kode_akuntansi->deleted_at = date('Y-m-d H:i:s');
        $kode_akuntansi->deleted_by = Auth::user()->id;
        if($kode_akuntansi->save()){
            echo 1;
        } else {
            echo 0;
        }
    }
}
