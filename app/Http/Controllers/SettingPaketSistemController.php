<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SettingPaketSistem;
use App\MasterJenisPaketSistem;
use App;
use Datatables;
use DB;
use Excel;
use Auth;
use App\Traits\DynamicConnectionTrait;

class SettingPaketSistemController extends Controller
{
    use DynamicConnectionTrait;
    /*
        =======================================================================================
        For     : 
        Author  : Wiwan Gussanda
        Date    : 11/09/2021
        =======================================================================================
    */
    public function index()
    {
        return view('setting_paket_sistem.index');
    }


    /*
        =======================================================================================
        For     : 
        Author  : Wiwan Gussanda
        Date    : 11/09/2021
        =======================================================================================
    */
    public function list_setting_paket_sistem(Request $request)
    {
    	$order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::connection($this->getConnection())->statement(DB::raw('set @rownum = 0'));
        $data = SettingPaketSistem::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_setting_paket_sistem.*'])
        ->where(function($query) use($request){
            $query->with('jenis_paket_sistem')->where('tb_setting_paket_sistem.is_deleted','=','0');
        });
        
        $datatables = Datatables::of($data);
        return $datatables  
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->orwhere('fee','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('jumlah_user','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('jumlah_dokter','LIKE','%'.$request->get('search')['value'].'%');
            });
        })
        ->editcolumn('id_jenis_paket_sistem', function($data){
            return $data->jenisPaketSistem->nama; 
        })
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .= '<span class="btn btn-primary" onClick="edit_data('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span>';
            $btn .= '<span class="btn btn-danger" onClick="delete_setting_paket_sistem('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
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
        Author  : Wiwan Gussanda
        Date    : 11/09/2021
        =======================================================================================
    */
    public function create()
    {
        $setting_paket_sistem = new SettingPaketSistem;
        $setting_paket_sistem->setDynamicConnection();

        $jenis_paket_sistems = MasterJenisPaketSistem::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama', 'id');
        $jenis_paket_sistems->prepend('-- Pilih Jenis Paket Sistem --','');

        return view('setting_paket_sistem.create')->with(compact('setting_paket_sistem', 'jenis_paket_sistems'));
    }

    /*
        =======================================================================================
        For     : 
        Author  : Wiwan Gussanda
        Date    : 11/09/2021
        =======================================================================================
    */
    public function store(Request $request)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        $setting_paket_sistem = new SettingPaketSistem;
        $setting_paket_sistem->setDynamicConnection();
        $setting_paket_sistem->fill($request->except('_token'));

        $jenis_paket_sistems = MasterJenisPaketSistem::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama', 'id');
        $jenis_paket_sistems->prepend('-- Pilih Jenis Paket Sistem --','');

        $validator = $setting_paket_sistem->validate();
        if($validator->fails()){
            return view('setting_paket_sistem.create')->with(compact('setting_paket_sistem', 'jenis_paket_sistems'))->withErrors($validator);
        }else{
            $setting_paket_sistem->created_by = Auth::user()->id;
            $setting_paket_sistem->created_at = date('Y-m-d H:i:s');
            $setting_paket_sistem->save();
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('setting_paket_sistem');
        }
    }

    /*
        =======================================================================================
        For     : 
        Author  : Wiwan Gussanda
        Date    : 11/09/2021
        =======================================================================================
    */
    public function show($id)
    {
        //
    }

    /*
        =======================================================================================
        For     : 
        Author  : Wiwan Gussanda
        Date    : 11/09/2021
        =======================================================================================
    */
    public function edit($id)
    {
        $setting_paket_sistem = SettingPaketSistem::on($this->getConnectionName())->find($id);

        $jenis_paket_sistems = MasterJenisPaketSistem::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama', 'id');
        $jenis_paket_sistems->prepend('-- Pilih Jenis Paket Sistem --','');

        return view('setting_paket_sistem.edit')->with(compact('setting_paket_sistem', 'jenis_paket_sistems'));
    }

    /*
        =======================================================================================
        For     : 
        Author  : Wiwan Gussanda
        Date    : 11/09/2021
        =======================================================================================
    */
    public function update(Request $request, $id)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        $setting_paket_sistem = SettingPaketSistem::on($this->getConnectionName())->find($id);
        $setting_paket_sistem->fill($request->except('_token'));

        $validator = $setting_paket_sistem->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            $setting_paket_sistem->updated_by = Auth::user()->id;
            $setting_paket_sistem->updated_at = date('Y-m-d H:i:s');
            $setting_paket_sistem->save();
            echo json_encode(array('status' => 1));
        }
    }

    /*
        =======================================================================================
        For     : 
        Author  : Wiwan Gussanda
        Date    : 11/09/2021
        =======================================================================================
    */
    public function destroy($id)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        $setting_paket_sistem = SettingPaketSistem::on($this->getConnectionName())->find($id);
        $setting_paket_sistem->is_deleted = 1;
        if($setting_paket_sistem->save()){
            echo 1;
        }else{
            echo 0;
        }
    }
}