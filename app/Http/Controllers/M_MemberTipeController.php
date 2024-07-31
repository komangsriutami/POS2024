<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterMemberTipe;
use App;
use Datatables;
use DB;
use Excel;
use Auth;

class M_MemberTipeController extends Controller
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
        return view('member_tipe.index');
    }


    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function list_member_tipe(Request $request)
    {
    	$order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::statement(DB::raw('set @rownum = 0'));
        $data = MasterMemberTipe::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_m_tipe_member.*'])
        ->where(function($query) use($request){
            $query->where('tb_m_tipe_member.is_deleted','=','0');
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
            $btn .= '<span class="btn btn-danger" onClick="delete_member_tipe('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
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
        $member_tipe = new MasterMemberTipe;

        return view('member_tipe.create')->with(compact('member_tipe'));
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
        $member_tipe = new MasterMemberTipe;
        $member_tipe->fill($request->except('_token'));

        $validator = $member_tipe->validate();
        if($validator->fails()){
            return view('member_tipe.create')->with(compact('member_tipe'))->withErrors($validator);
        }else{
            $member_tipe->created_by = Auth::user()->id;
            $member_tipe->created_at = date('Y-m-d H:i:s');
            $member_tipe->save();
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('member_tipe');
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
        $member_tipe = MasterMemberTipe::find($id);

        return view('member_tipe.edit')->with(compact('member_tipe'));
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
        $member_tipe = MasterMemberTipe::find($id);
        $member_tipe->fill($request->except('_token'));

        $validator = $member_tipe->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            $member_tipe->updated_by = Auth::user()->id;
            $member_tipe->updated_at = date('Y-m-d H:i:s');
            $member_tipe->save();
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
        $member_tipe = MasterMemberTipe::find($id);
        $member_tipe->is_deleted = 1;
        if($member_tipe->save()){
            echo 1;
        }else{
            echo 0;
        }
    }
}
