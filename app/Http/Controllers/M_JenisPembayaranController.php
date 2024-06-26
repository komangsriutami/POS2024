<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterJenisPembayaran;
use App;
use Datatables;
use DB;
use Excel;
use Auth;
class M_JenisPembayaranController extends Controller
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
        return view('jenis_pembayaran.index');
    }


    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function list_jenis_pembayaran(Request $request)
    {
    	$order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::statement(DB::raw('set @rownum = 0'));
        $data = MasterJenisPembayaran::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_m_jenis_pembayaran.*'])
        ->where(function($query) use($request){
            $query->where('tb_m_jenis_pembayaran.is_deleted','=','0');
        });
        
        $datatables = Datatables::of($data);
        return $datatables  
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->orwhere('jenis_pembayaran','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .= '<span class="btn btn-primary" onClick="edit_data('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span>';
            $btn .= '<span class="btn btn-danger" onClick="delete_jenis_pembayaran('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
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
        $jenis_pembayaran = new MasterJenisPembayaran;

        return view('jenis_pembayaran.create')->with(compact('jenis_pembayaran'));
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
        $jenis_pembayaran = new MasterJenisPembayaran;
        $jenis_pembayaran->fill($request->except('_token'));

        $validator = $jenis_pembayaran->validate();
        if($validator->fails()){
            return view('jenis_pembayaran.create')->with(compact('jenis_pembayaran'))->withErrors($validator);
        }else{
            $jenis_pembayaran->created_by = Auth::user()->id;
            $jenis_pembayaran->created_at = date('Y-m-d H:i:s');
            $jenis_pembayaran->save();
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('jenis_pembayaran');
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
        $jenis_pembayaran = MasterJenisPembayaran::find($id);

        return view('jenis_pembayaran.edit')->with(compact('jenis_pembayaran'));
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
        $jenis_pembayaran = MasterJenisPembayaran::find($id);
        $jenis_pembayaran->fill($request->except('_token'));

        $validator = $jenis_pembayaran->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            $jenis_pembayaran->updated_by = Auth::user()->id;
            $jenis_pembayaran->updated_at = date('Y-m-d H:i:s');
            $jenis_pembayaran->save();
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
        $jenis_pembayaran = MasterJenisPembayaran::find($id);
        $jenis_pembayaran->is_deleted = 1;
        if($jenis_pembayaran->save()){
            echo 1;
        }else{
            echo 0;
        }
    }
}
