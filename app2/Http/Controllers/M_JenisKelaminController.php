<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterJenisKelamin;
use App;
use Datatables;
use DB;
use Excel;
use Auth;
use App\Traits\DynamicConnectionTrait;
class M_JenisKelaminController extends Controller
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
        return view('jenis_kelamin.index');
    }


    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function list_jenis_kelamin(Request $request)
    {
    	$order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::connection($this->getConnection())->statement(DB::raw('set @rownum = 0'));
        $data = MasterJenisKelamin::on($this->getConnectionName())->select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_m_jenis_kelamin.*'])
        ->where(function($query) use($request){
            $query->where('tb_m_jenis_kelamin.is_deleted','=','0');
        });
        
        $datatables = Datatables::of($data);
        return $datatables  
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->orwhere('jenis_kelamin','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .= '<span class="btn btn-primary" onClick="edit_data('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span>';
            $btn .= '<span class="btn btn-danger" onClick="delete_jenis_kelamin('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
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
        $jenis_kelamin = new MasterJenisKelamin;
        $jenis_kelamin->setDynamicConnection();

        return view('jenis_kelamin.create')->with(compact('jenis_kelamin'));
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
        $jenis_kelamin = new MasterJenisKelamin;
        $jenis_kelamin->setDynamicConnection();
        $jenis_kelamin->fill($request->except('_token'));

        $validator = $jenis_kelamin->validate();
        if($validator->fails()){
            return view('jenis_kelamin.create')->with(compact('jenis_kelamin'))->withErrors($validator);
        }else{
            $jenis_kelamin->created_by = Auth::user()->id;
            $jenis_kelamin->created_at = date('Y-m-d H:i:s');
            $jenis_kelamin->save();
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('jenis_kelamin');
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
        $jenis_kelamin = MasterJenisKelamin::on($this->getConnectionName())->find($id);

        return view('jenis_kelamin.edit')->with(compact('jenis_kelamin'));
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
        $jenis_kelamin = MasterJenisKelamin::on($this->getConnectionName())->find($id);
        $jenis_kelamin->fill($request->except('_token'));

        $validator = $jenis_kelamin->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            $jenis_kelamin->updated_by = Auth::user()->id;
            $jenis_kelamin->updated_at = date('Y-m-d H:i:s');
            $jenis_kelamin->save();
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
        $jenis_kelamin = MasterJenisKelamin::on($this->getConnectionName())->find($id);
        $jenis_kelamin->is_deleted = 1;
        if($jenis_kelamin->save()){
            echo 1;
        }else{
            echo 0;
        }
    }
}
