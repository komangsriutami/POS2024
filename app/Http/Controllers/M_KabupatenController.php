<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterKabupaten;
use App\MasterProvinsi;
use App;
use Datatables;
use DB;
use App\Traits\DynamicConnectionTrait;

class M_KabupatenController extends Controller
{
    use DynamicConnectionTrait;
    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function index()
    {
        return view('kabupaten.index');
    }


    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function list_kabupaten(Request $request)
    {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::connection($this->getConnection())->statement(DB::raw('set @rownum = 0'));
        $data = MasterKabupaten::on($this->getConnectionName())->select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_m_kabupaten.*'])
        ->where(function($query) use($request){
            $query->orwhere('tb_m_kabupaten.is_deleted','=','0');
        });
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->where('nama','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->editcolumn('id_provinsi', function($data){
            return $data->provinsi->nama; 
        }) 
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .= '<span class="btn btn-primary" onClick="edit_data('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span>';
            $btn .= '<span class="btn btn-danger" onClick="delete_kabupaten('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
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
        Date    : 4/03/2020
        =======================================================================================
    */
    public function create()
    {
    	$kabupaten = new MasterKabupaten;
        $kabupaten->setDynamicConnection();

    	$provinsis = MasterProvinsi::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama', 'id');
        $provinsis->prepend('-- Pilih Jenis kabupaten --','');

        return view('kabupaten.create')->with(compact('kabupaten', 'provinsis'));
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
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        $kabupaten = new MasterKabupaten;
        $kabupaten->setDynamicConnection();
        $kabupaten->fill($request->except('_token'));

        $provinsis = MasterProvinsi::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama', 'id');
        $provinsis->prepend('-- Pilih Jenis kabupaten --','');

        $validator = $kabupaten->validate();
        if($validator->fails()){
            return view('kabupaten.create')->with(compact('kabupaten', 'provinsis'))->withErrors($validator);
        }else{
            $kabupaten->save_plus();
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('kabupaten');
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
        $kabupaten = MasterKabupaten::on($this->getConnectionName())->find($id);

        $provinsis = MasterProvinsi::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama', 'id');
        $provinsis->prepend('-- Pilih Jenis kabupaten --','');

        return view('kabupaten.edit')->with(compact('kabupaten', 'provinsis'));
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
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        $kabupaten = MasterKabupaten::on($this->getConnectionName())->find($id);
        $kabupaten->fill($request->except('_token'));

        $validator = $kabupaten->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            $kabupaten->save_edit();
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
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        $kabupaten = MasterKabupaten::on($this->getConnectionName())->find($id);
        $kabupaten->is_deleted = 1;
        if($kabupaten->save()){
            echo 1;
        }else{
            echo 0;
        }
    }
}
