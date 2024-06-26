<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterJabatan;
use App\MasterGroupApotek;
use App\MasterApotek;
use App;
use Datatables;
use DB;
use Excel;
use Auth;
class M_JabatanController extends Controller
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
        return view('jabatan.index');
    }


    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function list_jabatan(Request $request)
    {
    	$order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::statement(DB::raw('set @rownum = 0'));
        $data = MasterJabatan::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_m_jabatan.*'])
        ->where(function($query) use($request){
            $query->where('tb_m_jabatan.is_deleted','=','0');
        });
        
        $datatables = Datatables::of($data);
        return $datatables  
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->orwhere('nama','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .= '<span class="btn btn-primary" onClick="edit_data('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span>';
            $btn .= '<span class="btn btn-danger" onClick="delete_jabatan('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
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
        $jabatan = new MasterJabatan;

        return view('jabatan.create')->with(compact('jabatan'));
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
        $jabatan = new MasterJabatan;
        $jabatan->fill($request->except('_token'));
        $jabatan->id_group_apotek = Auth::user()->id_group_apotek;

        $validator = $jabatan->validate();
        if($validator->fails()){
            return view('jabatan.create')->with(compact('jabatan'))->withErrors($validator);
        }else{
            $jabatan->created_by = Auth::user()->id;
            $jabatan->created_at = date('Y-m-d H:i:s');
            $jabatan->save();
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('jabatan');
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
        $jabatan = MasterJabatan::find($id);

        return view('jabatan.edit')->with(compact('jabatan'));
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
        $jabatan = MasterJabatan::find($id);
        $jabatan->fill($request->except('_token'));

        $validator = $jabatan->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            $jabatan->updated_by = Auth::user()->id;
            $jabatan->updated_at = date('Y-m-d H:i:s');
            $jabatan->save();
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
        $jabatan = MasterJabatan::find($id);
        $jabatan->is_deleted = 1;
        if($jabatan->save()){
            echo 1;
        }else{
            echo 0;
        }
    }
}
