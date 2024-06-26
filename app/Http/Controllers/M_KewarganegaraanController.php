<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterKewarganegaraan;
use App;
use Datatables;
use DB;
use Excel;
use Auth;
class M_KewarganegaraanController extends Controller
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
        return view('kewarganegaraan.index');
    }


    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function list_kewarganegaraan(Request $request)
    {
    	$order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::statement(DB::raw('set @rownum = 0'));
        $data = MasterKewarganegaraan::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_m_kewarganegaraan.*'])
        ->where(function($query) use($request){
            $query->where('tb_m_kewarganegaraan.is_deleted','=','0');
        });
        
        $datatables = Datatables::of($data);
        return $datatables  
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->orwhere('kewarganegaraan','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .= '<span class="btn btn-primary" onClick="edit_data('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span>';
            $btn .= '<span class="btn btn-danger" onClick="delete_kewarganegaraan('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
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
        $kewarganegaraan = new MasterKewarganegaraan;

        return view('kewarganegaraan.create')->with(compact('kewarganegaraan'));
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
        $kewarganegaraan = new MasterKewarganegaraan;
        $kewarganegaraan->fill($request->except('_token'));

        $validator = $kewarganegaraan->validate();
        if($validator->fails()){
            return view('kewarganegaraan.create')->with(compact('kewarganegaraan'))->withErrors($validator);
        }else{
            $kewarganegaraan->created_by = Auth::user()->id;
            $kewarganegaraan->created_at = date('Y-m-d H:i:s');
            $kewarganegaraan->save();
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('kewarganegaraan');
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
        $kewarganegaraan = MasterKewarganegaraan::find($id);

        return view('kewarganegaraan.edit')->with(compact('kewarganegaraan'));
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
        $kewarganegaraan = MasterKewarganegaraan::find($id);
        $kewarganegaraan->fill($request->except('_token'));

        $validator = $kewarganegaraan->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            $kewarganegaraan->updated_by = Auth::user()->id;
            $kewarganegaraan->updated_at = date('Y-m-d H:i:s');
            $kewarganegaraan->save();
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
        $kewarganegaraan = MasterKewarganegaraan::find($id);
        $kewarganegaraan->is_deleted = 1;
        if($kewarganegaraan->save()){
            echo 1;
        }else{
            echo 0;
        }
    }
}
