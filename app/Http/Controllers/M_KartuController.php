<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterKartu;
use App\MasterJenisKartu;
use App;
use Datatables;
use DB;

class M_KartuController extends Controller
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
        return view('kartu.index');
    }


    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function list_kartu(Request $request)
    {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::statement(DB::raw('set @rownum = 0'));
        $data = MasterKartu::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_m_kartu_debet_credit.*'])
        ->where(function($query) use($request){
            $query->orwhere('tb_m_kartu_debet_credit.is_deleted','=','0');
        });
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->where('nama','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->editcolumn('id_jenis_kartu', function($data){
            return $data->jenis_kartu->nama; 
        }) 
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .= '<span class="btn btn-primary" onClick="edit_data('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span>';
            $btn .= '<span class="btn btn-danger" onClick="delete_kartu('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
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
    	$kartu = new MasterKartu;

    	$jenis_kartus = MasterJenisKartu::where('is_deleted', 0)->pluck('nama', 'id');
        $jenis_kartus->prepend('-- Pilih Jenis Kartu --','');

        return view('kartu.create')->with(compact('kartu', 'jenis_kartus'));
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
        $kartu = new MasterKartu;
        $kartu->fill($request->except('_token'));

        $jenis_kartus = MasterJenisKartu::where('is_deleted', 0)->pluck('nama', 'id');
        $jenis_kartus->prepend('-- Pilih Jenis Kartu --','');

        $validator = $kartu->validate();
        if($validator->fails()){
            return view('kartu.create')->with(compact('kartu', 'jenis_kartus'))->withErrors($validator);
        }else{
            $kartu->save_plus();
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('kartu');
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
        $kartu = MasterKartu::find($id);

        $jenis_kartus = MasterJenisKartu::where('is_deleted', 0)->pluck('nama', 'id');
        $jenis_kartus->prepend('-- Pilih Jenis Kartu --','');

        return view('kartu.edit')->with(compact('kartu', 'jenis_kartus'));
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
        $kartu = MasterKartu::find($id);
        $kartu->fill($request->except('_token'));

        $validator = $kartu->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            $kartu->save_edit();
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
        $kartu = MasterKartu::find($id);
        $kartu->is_deleted = 1;
        if($kartu->save()){
            echo 1;
        }else{
            echo 0;
        }
    }
}
