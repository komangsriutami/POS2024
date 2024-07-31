<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterJasaResep;
use App;
use Datatables;
use DB;

class M_JasaResepController extends Controller
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
        return view('jasa_resep.index');
    }


    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function list_jasa_resep(Request $request)
    {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::statement(DB::raw('set @rownum = 0'));
        $data = MasterJasaResep::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_m_jasa_resep.*'])
        ->where(function($query) use($request){
            $query->orwhere('tb_m_jasa_resep.is_deleted','=','0');
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
                $btn .= '<span class="btn btn-danger" onClick="delete_jasa_resep('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
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
    	$jasa_resep = new MasterJasaResep;

        return view('jasa_resep.create')->with(compact('jasa_resep'));
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
        $jasa_resep = new MasterJasaResep;
        $jasa_resep->fill($request->except('_token'));

        $validator = $jasa_resep->validate();
        if($validator->fails()){
            return view('jasa_resep.create')->with(compact('jasa_resep'))->withErrors($validator);
        }else{
            $jasa_resep->save_plus();
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('jasa_resep');
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
        $jasa_resep = MasterJasaResep::find($id);

        return view('jasa_resep.edit')->with(compact('jasa_resep'));
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
        $jasa_resep = MasterJasaResep::find($id);
        $jasa_resep->fill($request->except('_token'));

        $validator = $jasa_resep->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            $jasa_resep->save_edit();
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
        $jasa_resep = MasterJasaResep::find($id);
        $jasa_resep->is_deleted = 1;
        if($jasa_resep->save()){
            echo 1;
        }else{
            echo 0;
        }
    }
}
