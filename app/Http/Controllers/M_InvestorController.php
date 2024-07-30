<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterInvestor;
use App\MasterJenisKelamin;
use App\MasterKewarganegaraan;
use App\MasterAgama;
use App;
use Datatables;
use DB;
use App\Traits\DynamicConnectionTrait;

class M_InvestorController extends Controller
{
    use DynamicConnectionTrait;
    /*
        =======================================================================================
        For     : Menampilkan halaman index investor
        Author  : Govi.
        Date    : 11/09/2021
        =======================================================================================
    */
    public function index()
    {
        return view('investor.index');
    }

    /*
        =======================================================================================
        For     : Menampilkan datatable investor
        Author  : Govi.
        Date    : 11/09/2021
        =======================================================================================
    */
    public function list_investor(Request $request)
    {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::connection($this->getConnection())->statement(DB::raw('set @rownum = 0'));
        $data = MasterInvestor::on($this->getConnectionName())->select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_m_investor.*'])
        ->where(function($query) use($request){
            $query->where('tb_m_investor.is_deleted','=','0');
        });
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->orwhere('nama','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->editcolumn('nama', function($data){
            return '<span><b>'.$data->nama.'</b></span>'; 
        }) 
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .= '<span class="btn btn-primary" onClick="edit_data('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span>';
            $btn .= '<span class="btn btn-danger" onClick="delete_investor('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['action', 'nama'])
        ->addIndexColumn()
        ->make(true);  
    }

    /*
        =======================================================================================
        For     : Menampilkan halaman form investor
        Author  : Govi.
        Date    : 11/09/2021
        =======================================================================================
    */
    public function create()
    {
    	$investor = new MasterInvestor;
        $investor->setDynamicConnection();

        $jenis_kelamin = MasterJenisKelamin::on($this->getConnectionName())->where('is_deleted', 0)->pluck('jenis_kelamin', 'id');
        $jenis_kelamin->prepend('-- Pilih Jenis Kelamin --','');

        $agama = MasterAgama::on($this->getConnectionName())->where('is_deleted', 0)->pluck('agama', 'id');
        $agama->prepend('-- Pilih Agama --','');

        $kewarganegaraan = MasterKewarganegaraan::on($this->getConnectionName())->where('is_deleted', 0)->pluck('kewarganegaraan', 'id');
        $kewarganegaraan->prepend('-- Pilih Kewarganegaraan --','');

        return view('investor.create')->with(compact('investor','jenis_kelamin','agama','kewarganegaraan'));
    }

    /*
        =======================================================================================
        For     : Menyimpan data investor
        Author  : Govi.
        Date    : 11/09/2021
        =======================================================================================
    */
    public function store(Request $request)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        $investor = new MasterInvestor;
        $investor->setDynamicConnection();
        $investor->fill($request->except('_token'));

        $validator = $investor->validate();
        if($validator->fails()){
            $jenis_kelamin = MasterJenisKelamin::on($this->getConnectionName())->where('is_deleted', 0)->pluck('jenis_kelamin', 'id');
            $jenis_kelamin->prepend('-- Pilih Jenis Kelamin --','');

            $agama = MasterAgama::on($this->getConnectionName())->where('is_deleted', 0)->pluck('agama', 'id');
            $agama->prepend('-- Pilih Agama --','');

            $kewarganegaraan = MasterKewarganegaraan::on($this->getConnectionName())->where('is_deleted', 0)->pluck('kewarganegaraan', 'id');
            $kewarganegaraan->prepend('-- Pilih Kewarganegaraan --','');

            return view('investor.create')->with(compact('investor','jenis_kelamin','agama','kewarganegaraan'))->withErrors($validator);
        }else{
            $investor->save_plus();
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('investor');
        }
    }

    /*
        =======================================================================================
        For     : 
        Author  : Govi.
        Date    : 11/09/2021
        =======================================================================================
    */
    public function show($id)
    {
        //
    }

    /*
        =======================================================================================
        For     : Menampilkan halaman edit investor
        Author  : Govi.
        Date    : 11/09/2021
        =======================================================================================
    */
    public function edit($id)
    {
        $investor = MasterInvestor::on($this->getConnectionName())->find($id);

        $jenis_kelamin = MasterJenisKelamin::on($this->getConnectionName())->where('is_deleted', 0)->pluck('jenis_kelamin', 'id');
        $jenis_kelamin->prepend('-- Pilih Jenis Kelamin --','');

        $agama = MasterAgama::on($this->getConnectionName())->where('is_deleted', 0)->pluck('agama', 'id');
        $agama->prepend('-- Pilih Agama --','');

        $kewarganegaraan = MasterKewarganegaraan::on($this->getConnectionName())->where('is_deleted', 0)->pluck('kewarganegaraan', 'id');
        $kewarganegaraan->prepend('-- Pilih Kewarganegaraan --','');

        return view('investor.edit')->with(compact('investor','jenis_kelamin','agama','kewarganegaraan'));
    }

    /*
        =======================================================================================
        For     : Update data investor
        Author  : Govi.
        Date    : 11/09/2021
        =======================================================================================
    */
    public function update(Request $request, $id)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        $investor = MasterInvestor::on($this->getConnectionName())->find($id);
        $investor->fill($request->except('_token'));

        $validator = $investor->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            $investor->save_edit();
            echo json_encode(array('status' => 1));
        }
    }

    /*
        =======================================================================================
        For     : Delete data investor
        Author  : Govi.
        Date    : 11/09/2021
        =======================================================================================
    */
    public function destroy($id)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        $investor = MasterInvestor::on($this->getConnectionName())->find($id);
        $investor->save_delete();
        if($investor->delete()){
            echo 1;
        }else{
            echo 0;
        }
    }
}
