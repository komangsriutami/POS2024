<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterProdusen;
use App\MasterKabupaten;
use App\MasterProvinsi;
use App;
use Datatables;
use DB;
use Excel;

class M_ProdusenController extends Controller
{
    /*
        =======================================================================================
        For     : 
        Author  : Adistya.
        Date    : 25/02/2020
        =======================================================================================
    */
    public function index()
    {
        return view('produsen.index');
    }

        /*
        =======================================================================================
        For     : 
        Author  : Adistya.
        Date    : 22/02/2020
        =======================================================================================
    */
    public function list_produsen(Request $request)
    {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::statement(DB::raw('set @rownum = 0'));
        $data = MasterProdusen::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_m_produsen.*'])
        ->where(function($query) use($request){
            $query->where('tb_m_produsen.is_deleted','=','0');
           // $query->where('tb_m_suplier.id','LIKE','%'.$request->id_suplier.'%');
           // $query->where('tb_m_suplier.id_kabupaten','LIKE','%'.$request->id_kabupaten.'%');
           // $query->where('tb_m_suplier.id_provinsi','LIKE','%'.$request->id_provinsi.'%');
        });
        
        $datatables = Datatables::of($data);
        return $datatables  
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->orwhere('nama','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('telepon','LIKE','%'.$request->get('search')['value'].'%');
            });
        })    
        ->editcolumn('id_kabupaten', function($data){
            return $data->kabupaten->nama; 
        }) 
        ->editcolumn('id_provinsi', function($data){
            return $data->kabupaten->provinsi->nama; 
        }) 
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .= '<span class="btn btn-primary" onClick="edit_data('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span>';
            $btn .= '<span class="btn btn-danger" onClick="delete_produsen('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
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
        Date    : 25/02/2020
        =======================================================================================
    */
    public function create()
    {
    	$produsen = new MasterProdusen;

        $kabupatens = MasterKabupaten::where('is_deleted', 0)->pluck('nama', 'id');
        $kabupatens->prepend('-- Pilih Kabupaten --','');

        $provinsis = MasterProvinsi::where('is_deleted', 0)->pluck('nama', 'id');
        $provinsis->prepend('-- Pilih Provinsi --','');

        return view('produsen.create')->with(compact('produsen', 'provinsis', 'kabupatens'));
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
        $produsen = new MasterProdusen;
        $produsen->fill($request->except('_token'));

        $kabupatens      = MasterKabupaten::where('is_deleted', 0)->pluck('nama', 'id');
        $kabupatens->prepend('-- Pilih Kabupaten --','');

        $provinsis      = MasterKabupaten::where('is_deleted', 0)->pluck('nama', 'id');
        $provinsis ->prepend('-- Pilih Provinsi --','');

        $validator = $produsen->validate();
        if($validator->fails()){
            return view('produsen.create')->with(compact('produsen', 'kabupatens', 'provinsis'))->withErrors($validator);
        }else{
            $produsen->save_plus();
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('produsen');
        }
    }

    /*
        =======================================================================================
        For     : 
        Author  : Adistya.
        Date    : 25/02/2020
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
        Date    : 25/02/2020
        =======================================================================================
    */
    public function edit($id)
    {
        $produsen 		= MasterProdusen::find($id);

        $kabupatens     = MasterKabupaten::where('is_deleted', 0)->pluck('nama', 'id');
        $kabupatens->prepend('-- Pilih Kabupaten --','');

        $provinsis     = MasterProvinsi::where('is_deleted', 0)->pluck('nama', 'id');
        $provinsis->prepend('-- Pilih Provinsi --','');

        return view('produsen.edit')->with(compact('produsen', 'kabupatens', 'provinsis'));
    }

    /*
        =======================================================================================
        For     : 
        Author  : Adistya.
        Date    : 25/02/2020
        =======================================================================================
    */
    public function update(Request $request, $id)
    {
        $produsen = MasterProdusen::find($id);
        $produsen->fill($request->except('_token'));

        $validator = $produsen->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            $produsen->save_edit();
            echo json_encode(array('status' => 1));
        }
    }

    /*
        =======================================================================================
        For     : 
        Author  : Adistya.
        Date    : 25/02/2020
        =======================================================================================
    */
    public function destroy($id)
    {
        $produsen = MasterProdusen::find($id);
        $produsen->is_deleted = 1;
        if($produsen->save()){
            echo 1;
        }else{
            echo 0;
        }
    }
}
