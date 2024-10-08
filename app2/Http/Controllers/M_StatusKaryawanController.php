<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterStatusKaryawan;
use App\MasterGroupApotek;
use App\MasterApotek;
use App;
use Datatables;
use DB;
use Excel;
use Auth;
use App\Traits\DynamicConnectionTrait;

class M_StatusKaryawanController extends Controller
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
        return view('status_karyawan.index');
    }


    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function list_status_karyawan(Request $request)
    {
    	$order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::connection($this->getConnection())->statement(DB::raw('set @rownum = 0'));
        $data = MasterStatusKaryawan::on($this->getConnectionName())->select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_m_status_karyawan.*'])
        ->where(function($query) use($request){
            $query->where('tb_m_status_karyawan.is_deleted','=','0');
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
            $btn .= '<span class="btn btn-danger" onClick="delete_status('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
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
        $status_karyawan = new MasterStatusKaryawan;
        $status_karyawan->setDynamicConnection();

        return view('status_karyawan.create')->with(compact('status_karyawan'));
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
        $status_karyawan = new MasterStatusKaryawan;
        $status_karyawan->setDynamicConnection();
        $status_karyawan->fill($request->except('_token'));
        $status_karyawan->id_group_apotek = Auth::user()->id_group_apotek;

        $validator = $status_karyawan->validate();
        if($validator->fails()){
            return view('status_karyawan.create')->with(compact('status_karyawan'))->withErrors($validator);
        }else{
            $status_karyawan->created_by = Auth::user()->id;
            $status_karyawan->created_at = date('Y-m-d H:i:s');
            $status_karyawan->save();
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('status_karyawan');
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
        $status_karyawan = MasterStatusKaryawan::on($this->getConnectionName())->find($id);

        return view('status_karyawan.edit')->with(compact('status_karyawan'));
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
        $status_karyawan = MasterStatusKaryawan::on($this->getConnectionName())->find($id);
        $status_karyawan->fill($request->except('_token'));

        $validator = $status_karyawan->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            $status_karyawan->updated_by = Auth::user()->id;
            $status_karyawan->updated_at = date('Y-m-d H:i:s');
            $status_karyawan->save();
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
        $status_karyawan = MasterStatusKaryawan::on($this->getConnectionName())->find($id);
        $status_karyawan->is_deleted = 1;
        if($status_karyawan->save()){
            echo 1;
        }else{
            echo 0;
        }
    }
}
