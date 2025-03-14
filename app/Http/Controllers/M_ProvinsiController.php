<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterProvinsi;
use App\MasterJenisprovinsi;
use App;
use Datatables;
use DB;
class M_ProvinsiController extends Controller
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
        return view('provinsi.index');
    }


    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function list_provinsi(Request $request)
    {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::statement(DB::raw('set @rownum = 0'));
        $data = MasterProvinsi::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_m_provinsi.*'])
        ->where(function($query) use($request){
            $query->orwhere('tb_m_provinsi.is_deleted','=','0');
        });
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->where('nama','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .= '<span class="btn btn-primary" onClick="edit_data('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span>';
            $btn .= '<span class="btn btn-danger" onClick="delete_provinsi('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
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
    	$provinsi = new MasterProvinsi;

        return view('provinsi.create')->with(compact('provinsi'));
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
        $provinsi = new MasterProvinsi;
        $provinsi->fill($request->except('_token'));

        $validator = $provinsi->validate();
        if($validator->fails()){
            return view('provinsi.create')->with(compact('provinsi'))->withErrors($validator);
        }else{
            $provinsi->save_plus();
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('provinsi');
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
        $provinsi = MasterProvinsi::find($id);

        return view('provinsi.edit')->with(compact('provinsi'));
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
        $provinsi = MasterProvinsi::find($id);
        $provinsi->fill($request->except('_token'));

        $validator = $provinsi->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            $provinsi->save_edit();
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
        $provinsi = MasterProvinsi::find($id);
        $provinsi->is_deleted = 1;
        if($provinsi->save()){
            echo 1;
        }else{
            echo 0;
        }
    }
}
