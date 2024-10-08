<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterPaketWD;
use App\MasterPaketWDDetail;
use App;
use Datatables;
use DB;
use App\Traits\DynamicConnectionTrait;

class M_PaketWDController extends Controller
{
    use DynamicConnectionTrait;
    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function index()
    {
        return view('paket_wd.index');
    }


    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function list_data_paket_wd(Request $request)
    {
        DB::connection($this->getConnection())->statement(DB::raw('set @rownum = 0'));
        $data = MasterPaketWD::on($this->getConnectionName())->select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_m_paket_wd.*'])
        ->where(function($query) use($request){
            $query->orwhere('tb_m_paket_wd.is_deleted','=','0');
        });
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request){
            $query->where(function($query) use($request){
                $query->where('nama','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .= '<span class="btn btn-primary" onClick="edit_data('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span>';
            $btn .= '<span class="btn btn-danger" onClick="delete_paket_wd('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
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
    	$paket_wd = new MasterPaketWD;
        $paket_wd->setDynamicConnection();

        return view('paket_wd.create')->with(compact('paket_wd'));
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
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        $paket_wd = new MasterPaketWD;
        $paket_wd->setDynamicConnection();
        $paket_wd->fill($request->except('_token'));

        $validator = $paket_wd->validate();
        if($validator->fails()){
            return view('paket_wd.create')->with(compact('paket_wd'))->withErrors($validator);
        }else{
            $paket_wd->save_plus();
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('paket_wd');
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
        $paket_wd = MasterPaketWD::on($this->getConnectionName())->find($id);

        return view('paket_wd.edit')->with(compact('paket_wd'));
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
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        $paket_wd = MasterPaketWD::on($this->getConnectionName())->find($id);
        $paket_wd->fill($request->except('_token'));

        $validator = $paket_wd->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            $paket_wd->save_edit();
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
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        $paket_wd = MasterPaketWD::on($this->getConnectionName())->find($id);
        $paket_wd->is_deleted = 1;
        if($paket_wd->save()){
            echo 1;
        }else{
            echo 0;
        }
    }
}
