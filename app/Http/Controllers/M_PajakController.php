<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\MasterPajak;
use App\MasterKodeAkun;
use App;
use Datatables;
use DB;
use App\Traits\DynamicConnectionTrait;

class M_PajakController extends Controller
{
    use DynamicConnectionTrait;
    /*
        =======================================================================================
        For     : 
        Author  : Sri Utami
        Date    : 10/09/2021
        =======================================================================================
    */
    public function index()
    {
        return view('pajak.index');
    }


    /*
        =======================================================================================
        For     : 
        Author  : Sri Utami
        Date    : 10/09/2021
        =======================================================================================
    */
    public function list_pajak(Request $request)
    {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::connection($this->getConnection())->statement(DB::raw('set @rownum = 0'));
        $data = MasterPajak::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_m_pajak.*'])
        ->where(function($query) use($request){
            $query->orwhere('tb_m_pajak.is_deleted','=','0');
        });
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->where('nama','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->editcolumn('id_akun_pajak_penjualan', function($data){
            return $data->akun_penjualan->nama;
        })
        ->editcolumn('id_akun_pajak_pembelian', function($data){
            return $data->akun_pembelian->nama;
        })
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .= '<span class="btn btn-primary" onClick="edit_data('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span>';
            $btn .= '<span class="btn btn-danger" onClick="delete_pajak('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
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
        Author  : Sri Utami
        Date    : 10/09/2021
        =======================================================================================
    */
    public function create()
    {
    	$pajak = new MasterPajak;
        $pajak->setDynamicConnection();

    	$akuns = MasterKodeAkun::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama', 'id');
        $akuns->prepend('-- Pilih Akun --','');

        return view('pajak.create')->with(compact('pajak', 'akuns'));
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri Utami
        Date    : 10/09/2021
        =======================================================================================
    */
    public function store(Request $request)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        $pajak = new MasterPajak;
        $pajak->setDynamicConnection();
        $pajak->fill($request->except('_token'));

        $akuns = MasterKodeAkun::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama', 'id');
        $akuns->prepend('-- Pilih Akun --','');

        $validator = $pajak->validate();
        if($validator->fails()){
            return view('pajak.create')->with(compact('pajak', 'akuns'))->withErrors($validator);
        }else{
            $pajak->save_plus();
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('pajak');
        }
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri Utami
        Date    : 10/09/2021
        =======================================================================================
    */
    public function show($id)
    {
        //
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri Utami
        Date    : 10/09/2021
        =======================================================================================
    */
    public function edit($id)
    {
        $pajak = MasterPajak::on($this->getConnectionName())->find($id);

        $akuns = MasterKodeAkun::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama', 'id');
        $akuns->prepend('-- Pilih Akun --','');

        return view('pajak.edit')->with(compact('pajak', 'akuns'));
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri Utami
        Date    : 10/09/2021
        =======================================================================================
    */
    public function update(Request $request, $id)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        $pajak = MasterPajak::on($this->getConnectionName())->find($id);
        $pajak->fill($request->except('_token'));

        $validator = $pajak->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            $pajak->save_edit();
            echo json_encode(array('status' => 1));
        }
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri Utami
        Date    : 10/09/2021
        =======================================================================================
    */
    public function destroy($id)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        $pajak = MasterPajak::on($this->getConnectionName())->find($id);
        $pajak->is_deleted = 1;
        if($pajak->save()){
            echo 1;
        }else{
            echo 0;
        }
    }
}
