<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterKlinik;
use App\MasterGroupApotek;
use App;
use Datatables;
use DB;

class M_KlinikController extends Controller
{
    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 27/02/2020
        =======================================================================================
    */
    public function index()
    {
        return view('klinik.index');
    }


    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 27/02/2020
        =======================================================================================
    */
    public function list_klinik(Request $request)
    {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        $super_admin = session('super_admin');
        DB::statement(DB::raw('set @rownum = 0'));
        $data = MasterKlinik::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_m_klinik.*'])
        ->where(function($query) use($request, $super_admin){
            $query->where('tb_m_klinik.is_deleted','=','0');
            if($super_admin == 0) {
                $query->where('tb_m_klinik.id_group_apotek', Auth::user()->id_group_apotek);
            }
        });
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->orwhere('nama','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('telepon','LIKE','%'.$request->get('search')['value'].'%');
            });
        }) 
         ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
                $btn .= '<span class="btn btn-primary" onClick="edit_data('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span>';
                
                $btn .= '<span class="btn btn-danger" onClick="delete_klinik('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
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
        Author  : Sri U.
        Date    : 27/02/2020
        =======================================================================================
    */
    public function create()
    {
    	$klinik = new MasterKlinik;

        $group_apoteks      = MasterGroupApotek::where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $group_apoteks->prepend('-- Pilih Group Apotek --','');

        return view('klinik.create')->with(compact('klinik', 'group_apoteks'));
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 27/02/2020
        =======================================================================================
    */
    public function store(Request $request)
    {
        $klinik = new MasterKlinik;
        $klinik->fill($request->except('_token'));

        $group_apoteks      = MasterGroupApotek::where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $group_apoteks->prepend('-- Pilih Group Apotek --','');

        $validator = $klinik->validate();
        if($validator->fails()){
            return view('klinik.create')->with(compact('klinik', 'group_apoteks'))->withErrors($validator);
        }else{
            $klinik->save_plus();
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('klinik');
        }
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 27/02/2020
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
        Date    : 27/02/2020
        =======================================================================================
    */
    public function edit($id)
    {
        $klinik 		= MasterKlinik::find($id);

        $group_apoteks      = MasterGroupApotek::where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $group_apoteks->prepend('-- Pilih Group Apotek --','');

        return view('klinik.edit')->with(compact('klinik', 'group_apoteks'));
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 27/02/2020
        =======================================================================================
    */
    public function update(Request $request, $id)
    {
        $klinik = MasterKlinik::find($id);
        $klinik->fill($request->except('_token'));

        $validator = $klinik->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            $klinik->save_edit();
            echo json_encode(array('status' => 1));
        }
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 27/02/2020
        =======================================================================================
    */
    public function destroy($id)
    {
        $klinik = MasterKlinik::find($id);
        $klinik->is_deleted = 1;
        if($klinik->save()){
            echo 1;
        }else{
            echo 0;
        }
    }
}
