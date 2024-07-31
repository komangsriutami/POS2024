<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterPenandaanObat;
use App;
use Datatables;
use DB;

class M_PenandaanObatController extends Controller
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
        return view('penandaan_obat.index');
    }


    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function list_penandaan_obat(Request $request)
    {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::statement(DB::raw('set @rownum = 0'));
        $data = MasterPenandaanObat::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_m_penandaan_obat.*'])
        ->where(function($query) use($request){
            $query->orwhere('tb_m_penandaan_obat.is_deleted','=','0');
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
                $btn .= '<span class="btn btn-danger" onClick="delete_penandaan_obat('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
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
    	$penandaan_obat = new MasterPenandaanObat;

        return view('penandaan_obat.create')->with(compact('penandaan_obat'));
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
        $penandaan_obat = new MasterPenandaanObat;
        $penandaan_obat->fill($request->except('_token'));

        $validator = $penandaan_obat->validate();
        if($validator->fails()){
            return view('penandaan_obat.create')->with(compact('penandaan_obat'))->withErrors($validator);
        }else{
            $penandaan_obat->save_plus();
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('penandaan_obat');
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
        $penandaan_obat = MasterPenandaanObat::find($id);

        return view('penandaan_obat.edit')->with(compact('penandaan_obat'));
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
        $penandaan_obat = MasterPenandaanObat::find($id);
        $penandaan_obat->fill($request->except('_token'));

        $validator = $penandaan_obat->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            $penandaan_obat->save_edit();
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
        $penandaan_obat = MasterPenandaanObat::find($id);
        $penandaan_obat->is_deleted = 1;
        if($penandaan_obat->save()){
            echo 1;
        }else{
            echo 0;
        }
    }
}
