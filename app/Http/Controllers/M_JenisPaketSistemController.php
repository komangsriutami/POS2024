<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterJenisPaketSistem;
use App;
use Datatables;
use DB;
use Excel;
use Auth;

class M_JenisPaketSistemController extends Controller
{
    /*
        =======================================================================================
        For     : Direct to index jenis_paket_sistem views
        Author  : Wiwan Gussanda
        Date    : 11/09/2021
        =======================================================================================
    */
    public function index()
    {
        return view('jenis_paket_sistem.index');
    }

    
    /*
        =======================================================================================
        For     : List Data For Datable Jenis Paket Sistem
        Author  : Wiwan Gussanda
        Date    : 11/09/2021
        =======================================================================================
    */
    public function list_jenis_paket_sistem(Request $request)
    {
    	$order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::statement(DB::raw('set @rownum = 0'));
        $data = MasterJenisPaketSistem::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_m_jenis_paket_sistem.*'])
        ->where(function($query) use($request){
            $query->where('tb_m_jenis_paket_sistem.is_deleted','=','0');
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
            $btn .= '<span class="btn btn-danger" onClick="delete_jenis_paket_sistem('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['action'])
        ->addIndexColumn()
        ->make(true);  
    }

    /*
        =======================================================================================
        For     : Direct to form create
        Author  : Wiwan Gussanda
        Date    : 11/09/2021
        =======================================================================================
    */
    public function create()
    {
        $jenis_paket_sistem = new MasterJenisPaketSistem;

        return view('jenis_paket_sistem.create')->with(compact('jenis_paket_sistem'));
    }

   /*
        =======================================================================================
        For     : Store Data
        Author  : Wiwan Gussanda
        Date    : 11/09/2021
        =======================================================================================
    */
    public function store(Request $request)
    {
        $jenis_paket_sistem = new MasterJenisPaketSistem;
        $jenis_paket_sistem->fill($request->except('_token'));

        $validator = $jenis_paket_sistem->validate();
        if($validator->fails()){
            return view('jenis_paket_sistem.create')->with(compact('jenis_paket_sistem'))->withErrors($validator);
        }else{
            $jenis_paket_sistem->created_by = Auth::user()->id;
            $jenis_paket_sistem->created_at = date('Y-m-d H:i:s');
            $jenis_paket_sistem->save();
            session()->flash('success', 'Sukses menyimpan data !');
            return redirect('jenis_paket_sistem');
        }
    }

    /*
        =======================================================================================
        For     : Store Data
        Author  : Wiwan Gussanda
        Date    : 11/09/2021
        =======================================================================================
    */
    public function show($id)
    {
    }

    /*
        =======================================================================================
        For     : Store Data
        Author  : Wiwan Gussanda
        Date    : 11/09/2021
        =======================================================================================
    */
    public function edit($id)
    {
        $jenis_paket_sistem = MasterJenisPaketSistem::find($id);

        return view('jenis_paket_sistem.edit')->with(compact('jenis_paket_sistem'));
    }

    /*
        =======================================================================================
        For     : Store Data
        Author  : Wiwan Gussanda
        Date    : 11/09/2021
        =======================================================================================
    */
    public function update(Request $request, $id)
    {
        $jenis_paket_sistem = MasterJenisPaketSistem::find($id);
        $jenis_paket_sistem->fill($request->except('_token'));

        $validator = $jenis_paket_sistem->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            $jenis_paket_sistem->updated_by = Auth::user()->id;
            $jenis_paket_sistem->updated_at = date('Y-m-d H:i:s');
            $jenis_paket_sistem->save();
            echo json_encode(array('status' => 1));
        }
    }

    /*
        =======================================================================================
        For     : Store Data
        Author  : Wiwan Gussanda
        Date    : 11/09/2021
        =======================================================================================
    */
    public function destroy($id)
    {
        $jenis_paket_sistem = MasterJenisPaketSistem::find($id);
        $jenis_paket_sistem->is_deleted = 1;
        if($jenis_paket_sistem->save()){
            echo 1;
        }else{
            echo 0;
        }
    }
}
