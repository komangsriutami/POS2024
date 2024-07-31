<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterJenisPromo;
use App;
use Datatables;
use DB;
use Excel;
use Auth;

class M_JenisPromoController extends Controller
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
        return view('jenis_promo.index');
    }


    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function list_jenis_promo(Request $request)
    {
    	$order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::statement(DB::raw('set @rownum = 0'));
        $data = MasterJenisPromo::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_m_jenis_promo.*'])
        ->where(function($query) use($request){
            $query->where('tb_m_jenis_promo.is_deleted','=','0');
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
            $btn .= '<span class="btn btn-danger" onClick="delete_jenis_promo('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
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
        $jenis_promo = new MasterJenisPromo;

        return view('jenis_promo.create')->with(compact('jenis_promo'));
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
        $jenis_promo = new MasterJenisPromo;
        $jenis_promo->fill($request->except('_token'));

        $validator = $jenis_promo->validate();
        if($validator->fails()){
            return view('jenis_promo.create')->with(compact('jenis_promo'))->withErrors($validator);
        }else{
            $jenis_promo->created_by = Auth::user()->id;
            $jenis_promo->created_at = date('Y-m-d H:i:s');
            $jenis_promo->save();
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('jenis_promo');
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
        $jenis_promo = MasterJenisPromo::find($id);

        return view('jenis_promo.edit')->with(compact('jenis_promo'));
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
        $jenis_promo = MasterJenisPromo::find($id);
        $jenis_promo->fill($request->except('_token'));

        $validator = $jenis_promo->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            $jenis_promo->updated_by = Auth::user()->id;
            $jenis_promo->updated_at = date('Y-m-d H:i:s');
            $jenis_promo->save();
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
        $jenis_promo = MasterJenisPromo::find($id);
        $jenis_promo->is_deleted = 1;
        if($jenis_promo->save()){
            echo 1;
        }else{
            echo 0;
        }
    }
}
