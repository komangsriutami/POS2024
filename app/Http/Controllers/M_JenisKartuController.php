<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterJenisKartu;
use App;
use Datatables;
use DB;
class M_JenisKartuController extends Controller
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
        return view('jenis_kartu.index');
    }


    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function list_jenis_kartu(Request $request)
    {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::statement(DB::raw('set @rownum = 0'));
        $data = MasterJenisKartu::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_m_jenis_kartu.*'])
        ->where(function($query) use($request){
            $query->orwhere('tb_m_jenis_kartu.is_deleted','=','0');
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
            $btn .= '<span class="btn btn-danger" onClick="delete_jenis_kartu('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
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
    	$jenis_kartu = new MasterJenisKartu;

        return view('jenis_kartu.create')->with(compact('jenis_kartu'));
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
        $jenis_kartu = new MasterJenisKartu;
        $jenis_kartu->fill($request->except('_token'));

        $validator = $jenis_kartu->validate();
        if($validator->fails()){
            return view('jenis_kartu.create')->with(compact('jenis_kartu'))->withErrors($validator);
        }else{
            $jenis_kartu->save_plus();
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('jenis_kartu');
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
        $jenis_kartu = MasterJenisKartu::find($id);

        return view('jenis_kartu.edit')->with(compact('jenis_kartu'));
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
        $jenis_kartu = MasterJenisKartu::find($id);
        $jenis_kartu->fill($request->except('_token'));

        $validator = $jenis_kartu->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            $jenis_kartu->save_edit();
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
        $jenis_kartu = MasterJenisKartu::find($id);
        $jenis_kartu->is_deleted = 1;
        if($jenis_kartu->save()){
            echo 1;
        }else{
            echo 0;
        }
    }
}
