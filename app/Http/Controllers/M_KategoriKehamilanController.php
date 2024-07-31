<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterKategoriKehamilan;
use App;
use Datatables;
use DB;
use Excel;
use Auth;
class M_KategoriKehamilanController extends Controller
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
        return view('kategori_kehamilan.index');
    }


    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function list_kategori_kehamilan(Request $request)
    {
    	$order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::statement(DB::raw('set @rownum = 0'));
        $data = MasterKategoriKehamilan::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_m_kategori_kehamilan.*'])
        ->where(function($query) use($request){
            $query->where('tb_m_kategori_kehamilan.is_deleted','=','0');
        });
        
        $datatables = Datatables::of($data);
        return $datatables  
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->orwhere('keterangan','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .= '<span class="btn btn-primary" onClick="edit_data('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span>';
            $btn .= '<span class="btn btn-danger" onClick="delete_kategori_kehamilan('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
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
        $kategori_kehamilan = new MasterKategoriKehamilan;

        return view('kategori_kehamilan.create')->with(compact('kategori_kehamilan'));
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
        $kategori_kehamilan = new MasterKategoriKehamilan;
        $kategori_kehamilan->fill($request->except('_token'));

        $validator = $kategori_kehamilan->validate();
        if($validator->fails()){
            return view('kategori_kehamilan.create')->with(compact('kategori_kehamilan'))->withErrors($validator);
        }else{
            $kategori_kehamilan->created_by = Auth::user()->id;
            $kategori_kehamilan->created_at = date('Y-m-d H:i:s');
            $kategori_kehamilan->save();
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('kategori_kehamilan');
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
        $kategori_kehamilan = MasterKategoriKehamilan::find($id);

        return view('kategori_kehamilan.edit')->with(compact('kategori_kehamilan'));
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
        $kategori_kehamilan = MasterKategoriKehamilan::find($id);
        $kategori_kehamilan->fill($request->except('_token'));

        $validator = $kategori_kehamilan->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            $kategori_kehamilan->updated_by = Auth::user()->id;
            $kategori_kehamilan->updated_at = date('Y-m-d H:i:s');
            $kategori_kehamilan->save();
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
        $kategori_kehamilan = MasterKategoriKehamilan::find($id);
        $kategori_kehamilan->is_deleted = 1;
        if($kategori_kehamilan->save()){
            echo 1;
        }else{
            echo 0;
        }
    }
}
