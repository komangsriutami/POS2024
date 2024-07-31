<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterSatuan;
use App;
use Datatables;
use DB;

class M_SatuanController extends Controller
{
    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 27/02/2020
        =======================================================================================
    */
    public function index()
    {
        return view('satuan.index');
    }


    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 27/02/2020
        =======================================================================================
    */
    public function list_satuan(Request $request)
    {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::statement(DB::raw('set @rownum = 0'));
        $data = MasterSatuan::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_m_satuan.*'])
        ->where(function($query) use($request){
            $query->orwhere('tb_m_satuan.is_deleted','=','0');
        });
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->where('satuan','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .= '<span class="btn btn-primary" onClick="edit_data('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span>';
            $btn .= '<span class="btn btn-danger" onClick="delete_satuan('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
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
        Author  : Sri U.
        Date    : 27/02/2020
        =======================================================================================
    */
    public function create()
    {
    	$satuan = new MasterSatuan;

        return view('satuan.create')->with(compact('satuan'));
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 27/02/2020
        =======================================================================================
    */
    public function store(Request $request)
    {
        $satuan = new MasterSatuan;
        $satuan->fill($request->except('_token'));

        $validator = $satuan->validate();
        if($validator->fails()){
            return view('satuan.create')->with(compact('satuan'))->withErrors($validator);
        }else{
            $satuan->save_plus();
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('satuan');
        }
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 27/02/2020
        =======================================================================================
    */
    public function show($id)
    {
        //
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 27/02/2020
        =======================================================================================
    */
    public function edit($id)
    {
        $satuan 		= MasterSatuan::find($id);

        return view('satuan.edit')->with(compact('satuan'));
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 27/02/2020
        =======================================================================================
    */
    public function update(Request $request, $id)
    {
        $satuan = MasterSatuan::find($id);
        $satuan->fill($request->except('_token'));

        $validator = $satuan->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            $satuan->save_edit();
            echo json_encode(array('status' => 1));
        }
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 27/02/2020
        =======================================================================================
    */
    public function destroy($id)
    {
        $satuan = MasterSatuan::find($id);
        $satuan->is_deleted = 1;
        if($satuan->save()){
            echo 1;
        }else{
            echo 0;
        }
    }
}
