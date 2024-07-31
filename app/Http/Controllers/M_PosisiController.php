<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterPosisi;
use App\MasterGroupApotek;
use App\MasterApotek;
use App;
use Datatables;
use DB;
use Excel;
use Auth;
class M_PosisiController extends Controller
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
        return view('posisi.index');
    }


    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function list_posisi(Request $request)
    {
    	$order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::statement(DB::raw('set @rownum = 0'));
        $data = MasterPosisi::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_m_posisi.*'])
        ->where(function($query) use($request){
            $query->where('tb_m_posisi.is_deleted','=','0');
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
            $btn .= '<span class="btn btn-danger" onClick="delete_posisi('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
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
        $posisi = new MasterPosisi;

        return view('posisi.create')->with(compact('posisi'));
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
        $posisi = new MasterPosisi;
        $posisi->fill($request->except('_token'));
        $posisi->id_group_apotek = Auth::user()->id_group_apotek;

        $validator = $posisi->validate();
        if($validator->fails()){
            return view('posisi.create')->with(compact('posisi'))->withErrors($validator);
        }else{
            $posisi->created_by = Auth::user()->id;
            $posisi->created_at = date('Y-m-d H:i:s');
            $posisi->save();
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('posisi');
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
        $posisi = MasterPosisi::find($id);

        return view('posisi.edit')->with(compact('posisi'));
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
        $posisi = MasterPosisi::find($id);
        $posisi->fill($request->except('_token'));

        $validator = $posisi->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            $posisi->updated_by = Auth::user()->id;
            $posisi->updated_at = date('Y-m-d H:i:s');
            $posisi->save();
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
        $posisi = MasterPosisi::find($id);
        $posisi->is_deleted = 1;
        if($posisi->save()){
            echo 1;
        }else{
            echo 0;
        }
    }
}
