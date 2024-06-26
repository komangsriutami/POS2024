<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterAgama;
use App;
use Datatables;
use DB;
use Excel;
use Auth;
class M_AgamaController extends Controller
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
        return view('agama.index');
    }


    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function list_agama(Request $request)
    {
    	$order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::statement(DB::raw('set @rownum = 0'));
        $data = MasterAgama::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_m_agama.*'])
        ->where(function($query) use($request){
            $query->where('tb_m_agama.is_deleted','=','0');
        });
        
        $datatables = Datatables::of($data);
        return $datatables  
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->orwhere('agama','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .= '<span class="btn btn-primary" onClick="edit_data('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span>';
            $btn .= '<span class="btn btn-danger" onClick="delete_agama('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
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
        $agama = new MasterAgama;

        return view('agama.create')->with(compact('agama'));
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
        $agama = new MasterAgama;
        $agama->fill($request->except('_token'));

        $validator = $agama->validate();
        if($validator->fails()){
            return view('agama.create')->with(compact('agama'))->withErrors($validator);
        }else{
            $agama->created_by = Auth::user()->id;
            $agama->created_at = date('Y-m-d H:i:s');
            $agama->save();
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('agama');
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
        $agama = MasterAgama::find($id);

        return view('agama.edit')->with(compact('agama'));
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
        $agama = MasterAgama::find($id);
        $agama->fill($request->except('_token'));

        $validator = $agama->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            $agama->updated_by = Auth::user()->id;
            $agama->updated_at = date('Y-m-d H:i:s');
            $agama->save();
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
        $agama = MasterAgama::find($id);
        $agama->is_deleted = 1;
        if($agama->save()){
            echo 1;
        }else{
            echo 0;
        }
    }
}
