<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterGolonganObat;
use App;
use Datatables;
use DB;
use Excel;
use Auth;
class M_GolonganObatController extends Controller
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
        return view('golongan_obat.index');
    }


    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function list_golongan_obat(Request $request)
    {
    	$order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::statement(DB::raw('set @rownum = 0'));
        $data = MasterGolonganObat::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_m_golongan_obat.*'])
        ->where(function($query) use($request){
            $query->where('tb_m_golongan_obat.is_deleted','=','0');
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
            $btn .= '<span class="btn btn-danger" onClick="delete_golongan_obat('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
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
        $golongan_obat = new MasterGolonganObat;

        return view('golongan_obat.create')->with(compact('golongan_obat'));
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
        $golongan_obat = new MasterGolonganObat;
        $golongan_obat->fill($request->except('_token'));

        $validator = $golongan_obat->validate();
        if($validator->fails()){
            return view('golongan_obat.create')->with(compact('golongan_obat'))->withErrors($validator);
        }else{
            $golongan_obat->created_by = Auth::user()->id;
            $golongan_obat->created_at = date('Y-m-d H:i:s');
            $golongan_obat->save();
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('golongan_obat');
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
        $golongan_obat = MasterGolonganObat::find($id);

        return view('golongan_obat.edit')->with(compact('golongan_obat'));
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
        $golongan_obat = MasterGolonganObat::find($id);
        $golongan_obat->fill($request->except('_token'));

        $validator = $golongan_obat->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            $golongan_obat->updated_by = Auth::user()->id;
            $golongan_obat->updated_at = date('Y-m-d H:i:s');
            $golongan_obat->save();
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
        $golongan_obat = MasterGolonganObat::find($id);
        $golongan_obat->is_deleted = 1;
        if($golongan_obat->save()){
            echo 1;
        }else{
            echo 0;
        }
    }
}
