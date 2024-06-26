<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterGroupApotek;
use App;
use Datatables;
use DB;

class M_GroupApotekController extends Controller
{
    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 01/03/2020
        =======================================================================================
    */
    public function index()
    {
        return view('group_apotek.index');
    }


    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 01/03/2020
        =======================================================================================
    */
    public function list_group_apotek(Request $request)
    {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::statement(DB::raw('set @rownum = 0'));
        $data = MasterGroupApotek::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_m_group_apotek.*'])
        ->where(function($query) use($request){
            $query->where('tb_m_group_apotek.is_deleted','=','0');
        });
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->orwhere('nama_singkat','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('nama_panjang','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('telepon','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->editcolumn('nama_panjang', function($data){
            return '<span><b>('.$data->kode.') '.$data->nama_singkat.'</b></span></br><span>'.$data->nama_panjang.'</span>'; 
        }) 
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .= '<span class="btn btn-primary" onClick="edit_data('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span>';
            $btn .= '<span class="btn btn-danger" onClick="delete_group_apotek('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['action', 'nama_panjang'])
        ->addIndexColumn()
        ->make(true);  
    }

   
    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 01/03/2020
        =======================================================================================
    */
    public function create()
    {
    	$group_apotek = new MasterGroupApotek;

        return view('group_apotek.create')->with(compact('group_apotek'));
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 01/03/2020
        =======================================================================================
    */
    public function store(Request $request)
    {
        $group_apotek = new MasterGroupApotek;
        $group_apotek->fill($request->except('_token'));

        $validator = $group_apotek->validate();
        if($validator->fails()){
            return view('group_apotek.create')->with(compact('group_apotek'))->withErrors($validator);
        }else{
            $group_apotek->save_plus();
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('group_apotek');
        }
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 01/03/2020
        =======================================================================================
    */
    public function show($id)
    {
        //
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 01/03/2020
        =======================================================================================
    */
    public function edit($id)
    {
        $group_apotek 		= MasterGroupApotek::find($id);

        return view('group_apotek.edit')->with(compact('group_apotek'));
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 01/03/2020
        =======================================================================================
    */
    public function update(Request $request, $id)
    {
        $group_apotek = MasterGroupApotek::find($id);
        $group_apotek->fill($request->except('_token'));

        $validator = $group_apotek->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            $group_apotek->save_edit();
            echo json_encode(array('status' => 1));
        }
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 01/03/2020
        =======================================================================================
    */
    public function destroy($id)
    {
        $group_apotek = MasterGroupApotek::find($id);
        $group_apotek->is_deleted = 1;
        if($group_apotek->save()){
            echo 1;
        }else{
            echo 0;
        }
    }
}
