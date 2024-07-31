<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterJenisPembelian;
use App;
use Datatables;
use DB;
use Excel;
use Auth;
class M_JenisPembelianController extends Controller
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
        return view('jenis_pembelian.index');
    }


    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function list_jenis_pembelian(Request $request)
    {
    	$order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::statement(DB::raw('set @rownum = 0'));
        $data = MasterJenisPembelian::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_m_jenis_pembelian.*'])
        ->where(function($query) use($request){
            $query->where('tb_m_jenis_pembelian.is_deleted','=','0');
        });
        
        $datatables = Datatables::of($data);
        return $datatables  
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->orwhere('jenis_pembelian','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .= '<span class="btn btn-primary" onClick="edit_data('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span>';
            $btn .= '<span class="btn btn-danger" onClick="delete_jenis_pembelian('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
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
        $jenis_pembelian = new MasterJenisPembelian;

        return view('jenis_pembelian.create')->with(compact('jenis_pembelian'));
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
        $jenis_pembelian = new MasterJenisPembelian;
        $jenis_pembelian->fill($request->except('_token'));

        $validator = $jenis_pembelian->validate();
        if($validator->fails()){
            return view('jenis_pembelian.create')->with(compact('jenis_pembelian'))->withErrors($validator);
        }else{
            $jenis_pembelian->created_by = Auth::user()->id;
            $jenis_pembelian->created_at = date('Y-m-d H:i:s');
            $jenis_pembelian->save();
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('jenis_pembelian');
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
        $jenis_pembelian = MasterJenisPembelian::find($id);

        return view('jenis_pembelian.edit')->with(compact('jenis_pembelian'));
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
        $jenis_pembelian = MasterJenisPembelian::find($id);
        $jenis_pembelian->fill($request->except('_token'));

        $validator = $jenis_pembelian->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            $jenis_pembelian->updated_by = Auth::user()->id;
            $jenis_pembelian->updated_at = date('Y-m-d H:i:s');
            $jenis_pembelian->save();
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
        $jenis_pembelian = MasterJenisPembelian::find($id);
        $jenis_pembelian->is_deleted = 1;
        if($jenis_pembelian->save()){
            echo 1;
        }else{
            echo 0;
        }
    }
}
