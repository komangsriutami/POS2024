<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterPengumuman;
use App\MasterApotek;
use App\RbacUserRole;
use App\RbacRole;
use App\User;
use App;
use Datatables;
use DB;
use Excel;
use Auth;
use Crypt;
use App\Traits\DynamicConnectionTrait;

class M_PengumumanController extends Controller
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
        return view('pengumuman.index');
    }


    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function list_pengumuman(Request $request)
    {
    	$order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        $apotek = MasterApotek::on($this->getConnectionName())->find(session('id_apotek_active'));
        $apoteker = User::on($this->getConnectionName())->find($apotek->id_apoteker);
        $id_user = Auth::user()->id;

        $hak_akses = 0;
        if($apoteker->id == $id_user) {
            $hak_akses = 0;
        }

        if($id_user == 1 || $id_user == 2 || $id_user == 16) {
            $hak_akses = 1;
        }


        DB::connection($this->getConnection())->statement(DB::raw('set @rownum = 0'));
        $data = MasterPengumuman::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_pengumuman.*'])
        ->leftJoin('rbac_roles as a', 'a.id', '=', 'tb_pengumuman.id_role_penerima')
        ->where(function($query) use($request){
            $query->where('tb_pengumuman.is_deleted','=','0');
        });
        
        $datatables = Datatables::of($data);
        return $datatables  
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->orwhere('tb_pengumuman.id','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->editColumn('id_role_penerima', function ($data) {
            $id_role_penerima = json_decode($data->id_role_penerima);
	        $penerima = RbacRole::on($this->getConnectionName())->where('is_deleted', 0)->whereIn('id', $id_role_penerima)->get();
	        $jum = count($penerima);
	        $string = '';

	        $i = 0;
	        foreach ($penerima as $key => $obj) {
	            $i++;
	            $string .= '- '.$obj->nama;
	            if($i<$jum) {
	                $string .= '<br>';
	            }
	        }
	        return $string;
	    })
        ->editColumn('id_asal_pengumuman', function ($data) {
            $string = '';

            if($data->id_asal_pengumuman == 1) {
                $string = 'Administrator';
            } else if($data->id_asal_pengumuman == 2) {
                $string = 'Manajemen';
            } else {
                $string = 'Kepala Outlet';
            }

            return $string;
        })
        ->addcolumn('action', function($data) use($hak_akses) {
            $btn = '<div class="btn-group">';
            $btn .= '<a href="'.url('/pengumuman/'.Crypt::encrypt($data->id)).'/edit" class="btn btn-info btn-sm" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i> Edit</a>';
	        $btn .= '<span class="btn btn-danger btn-sm" onClick="delete_loss_sell('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i> Hapus</span>';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['action', 'id_role_penerima'])
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
        $data_ = new MasterPengumuman;
        $data_->setDynamicConnection();
        $roles = RbacRole::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama', 'id');

        return view('pengumuman.create')->with(compact('data_', 'roles'));
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
        $data_ = new MasterPengumuman;
        $data_->setDynamicConnection();
        $data_->fill($request->except('_token'));
        $data_->id_role_penerima = json_encode($request->id_role_penerima);
        $data_->show_popup = $request->show_popup;
        
        $date_range = explode(' - ',$request->tanggal_aktif);
        $date1 = strtr($date_range[0], '/', '-');
        $date2 = strtr($date_range[1], '/', '-');

        $data_->tanggal_mulai = date('Y-m-d', strtotime($date1));
        $data_->tanggal_selesai =  date('Y-m-d', strtotime($date2));

        $roles = RbacRole::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama', 'id');
        $date_now = date('Y-m-d H:i:s');
        $validator = $data_->validate();
        if($validator->fails()){
            return view('pengumuman.create')->with(compact('data_', 'roles'))->withErrors($validator);
        }else{
            # file file
            if (!empty($request->file)) {
                $nama_file = $request->file->getClientOriginalName();
                $split_co = explode('.' , $nama_file);
                $ext_co = end($split_co);
                $ext_aprove = array('.png', '.jpg', '.jpeg', '.pdf');
                # cek kembali apakah ext file tersebut ada di ext yang valid
                if (in_array($ext_co, $ext_aprove)) {
                    $file_name_co = md5($split_co[0]."-".$date_now);
                    $file = $request->file;
                    $destination_path_co = storage_path('userfiles/pengumuman');
                    $destination_filename_co = $file_name_co.".".$ext_co;
                    $data_->file->move($destination_path_co, $destination_filename_co);
                    $data_->file = $file_name_co.".".$ext_co;
                } else {
                    session()->flash('error', 'Extensi file tidak diperbolehkan!');
                    return view('pengumuman.create')->with(compact('data_', 'roles'));
                }
            } 

            $data_->created_by = Auth::user()->id;
            $data_->created_at = date('Y-m-d H:i:s');
            $data_->save();
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('pengumuman');
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
        $id = Crypt::decrypt($id);
        $data_ = MasterPengumuman::on($this->getConnectionName())->find($id);
        $data_->tanggal_aktif = date('m/d/Y', strtotime($data_->tanggal_mulai)).' - '. date('m/d/Y', strtotime($data_->tanggal_selesai));
        $data_->id_role_penerima = json_decode($data_->id_role_penerima);
        $roles = RbacRole::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama', 'id');

        return view('pengumuman.edit')->with(compact('data_', 'roles'));
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
        $data_ = MasterPengumuman::on($this->getConnectionName())->find($id);
        $data_->fill($request->except('_token'));
        $data_->id_role_penerima = json_encode($request->id_role_penerima);
        $data_->show_popup = $request->show_popup;

        $date_range = explode(' - ',$request->tanggal_aktif);
        $date1 = strtr($date_range[0], '/', '-');
        $date2 = strtr($date_range[1], '/', '-');
        $data_->tanggal_mulai = date('Y-m-d', strtotime($date1));
        $data_->tanggal_selesai =  date('Y-m-d', strtotime($date2));

        $roles = RbacRole::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama', 'id');
        $date_now = date('Y-m-d H:i:s');
        $validator = $data_->validate();
        if($validator->fails()){
            $data_->tanggal_aktif = date('m/d/Y', strtotime($data_->tanggal_mulai)).' - '. date('m/d/Y', strtotime($data_->tanggal_selesai));
            $data_->id_role_penerima = json_decode($data_->id_role_penerima);
            session()->flash('error', 'Terdapat data yang tidak lengkap');
            return view('pengumuman.edit')->with(compact('data_', 'roles'));
        }else{
            # file file
            if (!empty($request->file)) {
                $nama_file = $request->file->getClientOriginalName();
                $split_co = explode('.' , $nama_file);
                $ext_co = end($split_co);
                $ext_aprove = array('png', 'jpg', 'jpeg', 'pdf');
                # cek kembali apakah ext file tersebut ada di ext yang valid
                if (in_array($ext_co, $ext_aprove)) {
                    $file_name_co = md5($split_co[0]."-".$date_now);
                    $file = $request->file;
                    $destination_path_co = storage_path('userfiles/pengumuman');
                    $destination_filename_co = $file_name_co.".".$ext_co;
                    $data_->file->move($destination_path_co, $destination_filename_co);
                    $data_->file = $file_name_co.".".$ext_co;
                } else {
                    $data_->tanggal_aktif = date('m/d/Y', strtotime($data_->tanggal_mulai)).' - '. date('m/d/Y', strtotime($data_->tanggal_selesai));
                    $data_->id_role_penerima = json_decode($data_->id_role_penerima);
                    session()->flash('error', 'Extensi file tidak diperbolehkan!');
                    return view('pengumuman.edit')->with(compact('data_', 'roles'));
                }
            } else {
                $data_->file = $data_->file;
            }

            $data_->updated_by = Auth::user()->id;
            $data_->updated_at = date('Y-m-d H:i:s');
            $data_->save();
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('pengumuman');
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
        $data_ = MasterPengumuman::on($this->getConnectionName())->find($id);
        $data_->is_deleted = 1;
        $data_->deleted_by = Auth::user()->id;
        $data_->deleted_at = date('Y-m-d H:i:s');
        if($data_->save()){
            echo 1;
        }else{
            echo 0;
        }
    }

    public function informasi(){
        return view('pengumuman.informasi');
    }
}
