<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterSuplier;
use App\MasterKabupaten;
use App\MasterProvinsi;
use App;
use Datatables;
use DB;
use Excel;

class M_SuplierController extends Controller
{
    /*
        =======================================================================================
        For     : 
        Author  : Adistya.
        Date    : 22/02/2020
        =======================================================================================
    */
    public function index()
    {
        return view('suplier.index');
    }

        /*
        =======================================================================================
        For     : 
        Author  : Adistya.
        Date    : 22/02/2020
        =======================================================================================
    */
    public function list_suplier(Request $request)
    {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::statement(DB::raw('set @rownum = 0'));
        $data = MasterSuplier::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_m_suplier.*'])
        ->where(function($query) use($request){
            $query->where('tb_m_suplier.is_deleted','=','0');
           // $query->where('tb_m_suplier.id','LIKE','%'.$request->id_suplier.'%');
           // $query->where('tb_m_suplier.id_kabupaten','LIKE','%'.$request->id_kabupaten.'%');
           // $query->where('tb_m_suplier.id_provinsi','LIKE','%'.$request->id_provinsi.'%');
        });
        
        $datatables = Datatables::of($data);
        return $datatables  
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->orwhere('nama','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('npwp','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('telepon','LIKE','%'.$request->get('search')['value'].'%');
            });
        })    
        ->editcolumn('id_kabupaten', function($data){
            if($data->id_kabupaten != 0) {
                $nama = $data->kabupaten->nama;
            } else {
                $nama = 'Data tidak ditemukan';
            }
            return $nama; 
        }) 
        ->editcolumn('id_provinsi', function($data){
            if($data->id_provinsi != 0) {
                $nama = $data->provinsi->nama;
            } else {
                $nama = 'Data tidak ditemukan';
            }
            return $nama; 
        }) 
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .= '<span class="btn btn-primary" onClick="edit_data('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span>';
            $btn .= '<span class="btn btn-danger" onClick="delete_suplier('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['id_kabupaten', 'id_provinsi', 'action'])
        ->addIndexColumn()
        ->make(true);  
    }

    
    /*
        =======================================================================================
        For     : 
        Author  : Adistya.
        Date    : 22/02/2020
        =======================================================================================
    */
    public function create()
    {
    	$suplier = new MasterSuplier;

        $kabupatens = MasterKabupaten::where('is_deleted', 0)->pluck('nama', 'id');
        $kabupatens->prepend('-- Pilih Kabupaten --','');

        $provinsis = MasterProvinsi::where('is_deleted', 0)->pluck('nama', 'id');
        $provinsis->prepend('-- Pilih Provinsi --','');
        
        return view('suplier.create')->with(compact('suplier', 'provinsis', 'kabupatens'));
    }

    /*
        =======================================================================================
        For     : 
        Author  : Adistya
        Date    : 22/02/2020
        =======================================================================================
    */
    public function store(Request $request)
    {
        $suplier = new MasterSuplier;
        $suplier->fill($request->except('_token'));

        $kabupatens      = MasterKabupaten::where('is_deleted', 0)->pluck('nama', 'id');
        $kabupatens->prepend('-- Pilih Kabupaten --','');

        $provinsis      = MasterProvinsi::where('is_deleted', 0)->pluck('nama', 'id');
        $provinsis ->prepend('-- Pilih Provinsi --','');

        $validator = $suplier->validate();
        if($validator->fails()){
            return view('suplier.create')->with(compact('suplier', 'kabupatens', 'provinsis'))->withErrors($validator);
        }else{
            $suplier->save_plus();
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('suplier');
        }
    }

    /*
        =======================================================================================
        For     : 
        Author  : Adistya.
        Date    : 22/02/2020
        =======================================================================================
    */
    public function show($id)
    {
        //
    }

    /*
        =======================================================================================
        For     : 
        Author  : Adistya.
        Date    : 22/02/2020
        =======================================================================================
    */
    public function edit($id)
    {
        $suplier 		= MasterSuplier::find($id);

        $kabupatens     = MasterKabupaten::where('is_deleted', 0)->pluck('nama', 'id');
        $kabupatens->prepend('-- Pilih Kabupaten --','');

        $provinsis     = MasterProvinsi::where('is_deleted', 0)->pluck('nama', 'id');
        $provinsis->prepend('-- Pilih Provinsi --','');

        return view('suplier.edit')->with(compact('suplier', 'kabupatens', 'provinsis'));
    }

    /*
        =======================================================================================
        For     : 
        Author  : Adistya.
        Date    : 22/02/2020
        =======================================================================================
    */
    public function update(Request $request, $id)
    {
        $suplier = MasterSuplier::find($id);
        $suplier->fill($request->except('_token'));

        $validator = $suplier->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            $suplier->save_edit();
            echo json_encode(array('status' => 1));
        }
    }

    /*
        =======================================================================================
        For     : 
        Author  : Adistya.
        Date    : 22/02/2020
        =======================================================================================
    */
    public function destroy($id)
    {
        $suplier = MasterSuplier::find($id);
        $suplier->is_deleted = 1;
        if($suplier->save()){
            echo 1;
        }else{
            echo 0;
        }
    }
}
