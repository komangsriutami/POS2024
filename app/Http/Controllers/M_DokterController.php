<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterDokter;
use App\MasterGroupApotek;
use App\MasterApotek;
use App\MasterSpesialis;
use App\RbacRole;
use App\RbacUserRole;
use Auth;
use App;
use Datatables;
use DB;
use File;
use Mail;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Traits\DynamicConnectionTrait;

class M_DokterController extends Controller
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
        return view('dokter.index');
    }

    protected function getToken()
    {
        $random = Str::random(40);
        return hash_hmac('sha256', $random, config('app.key'));
    }
    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function list_data(Request $request)
    {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        $super_admin = session('super_admin');
        DB::connection($this->getConnection())->statement(DB::raw('set @rownum = 0'));
        $data = MasterDokter::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_m_dokter.*'])
        ->where(function($query) use($request, $super_admin){
            $query->where('tb_m_dokter.is_deleted','=','0');
            if($super_admin == 0) {
                $query->where('tb_m_dokter.id_group_apotek', Auth::user()->id_group_apotek);
            }
        });
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->orwhere('nama','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('telepon','LIKE','%'.$request->get('search')['value'].'%');
            });
        }) 
        ->addColumn('img', function ($data) {
            if (($data->image == "#") || (empty($data->image))) {
                return '-';
            } else {
                return '<div class="col-md-4"><img src="data:image/png;base64,'.$data->image.'" width="200"></div>';
            }
        })
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .= '<a href="'.url('/dokter/'.$data->id.'/edit').'" title="Edit Data" class="btn btn-primary"><span data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span></a>';
            $btn .= '<span class="btn btn-danger" onClick="delete_dokter('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['action', 'img'])
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
    	$data_ = new MasterDokter;
        $data_->setDynamicConnection();

        $group_apoteks      = MasterGroupApotek::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $group_apoteks->prepend('-- Pilih Group Apotek --','');

        # Tangkas
        $apoteks      = MasterApotek::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $apoteks->prepend('-- Pilih Apotek --', '');

        $spesialiss      = MasterSpesialis::on($this->getConnectionName())->where('is_deleted', 0)->pluck('spesialis', 'id');
        $spesialiss->prepend('-- Pilih Spesialis --', '');

        return view('dokter.create')->with(compact('data_', 'group_apoteks', 'apoteks', 'spesialiss'));
    }
    /*
        =======================================================================================
        For     : view from klik email to page confirm dokter
        Author  : Wiwan Gussanda
        Date    : 14/09/2021
        =======================================================================================
    */
    public function invite_confirm(Request $request)
    {
        $dokter = MasterDokter::on($this->getConnectionName())->where('remember_token', $request->token)->first();

        $group_apoteks      = MasterGroupApotek::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $group_apoteks->prepend('-- Pilih Group Apotek --','');

        $apoteks      = MasterApotek::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $apoteks->prepend('-- Pilih Apotek --', '');

        $spesialiss      = MasterSpesialis::on($this->getConnectionName())->where('is_deleted', 0)->pluck('spesialis', 'id');
        $spesialiss->prepend('-- Pilih Spesialis --', '');

        return view('frontend.confirm_invite_dokter')->with(compact(
            'dokter',
            'group_apoteks',
            'apoteks',
            'spesialiss',
        ));
    }
    /*
        =======================================================================================
        For     : post data confirm dokter
        Author  : Wiwan Gussanda
        Date    : 14/09/2021
        =======================================================================================
    */

    public function invite_confirm_post(Request $request)
    {
        $dokter = MasterDokter::on($this->getConnectionName())->where('id', $request->id)->first();
        $dokter->fill($request->except('_token'));
        
        $validator = $dokter->validate_confirm_dokter();
        if($validator->fails()){

            $group_apoteks      = MasterGroupApotek::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama_singkat', 'id');
            $group_apoteks->prepend('-- Pilih Group Apotek --', '');

            $apoteks      = MasterApotek::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama_singkat', 'id');
            $apoteks->prepend('-- Pilih Apotek --', '');

            $spesialiss      = MasterSpesialis::on($this->getConnectionName())->where('is_deleted', 0)->pluck('spesialis', 'id');
            $spesialiss->prepend('-- Pilih Spesialis --', '');
            
            return view('frontend.confirm_invite_dokter')
                ->with(compact(
                    'dokter',
                    'group_apoteks',
                    'apoteks',
                    'spesialiss',
                ))
                ->withErrors($validator);
        }else{
            $dokter->password = bcrypt($request->password);
            $dokter->activated = 1;
            $dokter->updated_by = $dokter->id;
            $dokter->color = '#e040fb';
            $dokter->remember_token = null;
            $dokter->save();
            session()->flash('success', 'Sukses melakukan registrasi!');
            return redirect('/login_dokter')->with('status', 'Selamat anda telah melengkapi data, Silakan isikan username dan password untuk login ke sistem');
            // return redirect('/login_dokter');
        }

    }
    /*
        =======================================================================================
        For     : Menampilkan halaman invite dokter
        Author  : Wiwan Gussanda
        Date    : 14/09/2021
        =======================================================================================
    */
    public function invite_view()
    {
        $dokter = new MasterDokter;   
        $dokter->setDynamicConnection();   
        $roles = RbacRole::on($this->getConnectionName())->where('is_deleted', 0)->get();

        return view('dokter.invite')->with(compact('dokter','roles'));
    }
    /*
        =======================================================================================
        For     : Kirim email invite dokter
        Author  : Wiwan Gussanda
        Date    : 14/09/2021
        =======================================================================================
    */
    public function invite_submit(Request $request)
    {
        DB::connection($this->getConnectionName())->beginTransaction();  
        try {
            $dokter = new MasterDokter;
            $dokter->setDynamicConnection();
            $dokter->fill($request->except('_token'));

            $validator = $dokter->validate_invite();
            if($validator->fails()){
                $roles = RbacRole::on($this->getConnectionName())->where('is_deleted', 0)->get();
                return view('dokter.invite')
                    ->with(compact('dokter', 'roles'))
                    ->withErrors($validator);
            } else {
                $token = $this->getToken();
                $dokter->remember_token = $token;
                $dokter->created_at = date('Y-m-d H:i:s');
                $dokter->save();
                
                foreach ($request->roles as $role) {
                    $rbac_user_role = new RbacUserRole;
                    $rbac_user_role->setDynamicConnection();
                    $rbac_user_role->id_user = $dokter->id;
                    $rbac_user_role->id_role = $role;
                    $rbac_user_role->save();
                }
                                
                $link = route('confirm_dokter', $dokter->remember_token);
                Mail::to($dokter->email)->send(new \App\Mail\MailInviteDokter($dokter, $link));
                DB::connection($this->getConnectionName())->commit();

                session()->flash('success', 'Sukses invite dokter!');
                return redirect('dokter');
            }
        } catch(\Exception $e){
            dd($e);
            DB::connection($this->getConnectionName())->rollback();
            session()->flash('error', 'Error!');
            return redirect('dokter');
        }
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
        # Tangkas
        $data_ = new MasterDokter;
        $data_->setDynamicConnection();
        $data_->fill($request->except('_token')); // fill untuk menyimpan data dari request
        $data_->password = bcrypt($request->password);
        $data_->activated = 1;

        # file image | tambahan sri 14/6/2021
        $date_now = date('Y-m-d H:i:s');
        if (!empty($request->img)) {
            $nama_cover = $request->img->getClientOriginalName();
            $split_co = explode('.', $nama_cover);
            $ext_co = $split_co[1];
            $file_name_co = md5($split_co[0] . "-" . $date_now);
            $logo = $request->img;
            $destination_path_co = public_path("temp/");
            $destination_filename_co = $file_name_co . "." . $ext_co;
            $data_->img->move($destination_path_co, $destination_filename_co);
            $data_->img = $file_name_co . "." . $ext_co;
            
            # SRI | convert image to blob
            $path = $destination_path_co.$destination_filename_co;
            $logo = file_get_contents($path);
            $base64 = base64_encode($logo);
            $data_->image = $base64;

            # SRI | hapus image
            if (File::exists($path)) {
                unlink($path);
            }
        }

        $validator = $data_->validate();
        if ($validator->fails()) {
            $group_apoteks      = MasterGroupApotek::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama_singkat', 'id');
            $group_apoteks->prepend('-- Pilih Group Apotek --', '');

            $apoteks      = MasterApotek::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama_singkat', 'id');
            $apoteks->prepend('-- Pilih Apotek --', '');

            $spesialiss      = MasterSpesialis::on($this->getConnectionName())->where('is_deleted', 0)->pluck('spesialis', 'id');
            $spesialiss->prepend('-- Pilih Spesialis --', '');

            Session()->flash('error', 'Gagal menyimpan data!');
            return view('dokter.create')->with(compact('data_', 'group_apoteks', 'apoteks', 'spesialiss'))->withErrors($validator);
        } else {
            if ($data_->save()) {
                Session()->flash('success', 'Sukses menyimpan data!');
                return redirect('dokter');
            } else {
                Session()->flash(
                    'error',
                    'Gagal menyimpan data!'
                );
                return redirect('dokter');
            }
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
        $data_ 		= MasterDokter::on($this->getConnectionName())->find($id);

        $group_apoteks      = MasterGroupApotek::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $group_apoteks->prepend('-- Pilih Group Apotek --','');

        # Tangkas
        $apoteks      = MasterApotek::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $apoteks->prepend('-- Pilih Apotek --', '');

        $spesialiss      = MasterSpesialis::on($this->getConnectionName())->where('is_deleted', 0)->pluck('spesialis', 'id');
        $spesialiss->prepend('-- Pilih Spesialis --', '');

        return view('dokter.edit')->with(compact('data_', 'group_apoteks', 'apoteks', 'spesialiss'));
    }

    /*
        =======================================================================================
        For     : 
        Author  : Tangkas
        Date    : 
        =======================================================================================
    */
    public function update(Request $request, $id)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        $data_ = MasterDokter::on($this->getConnectionName())->find($id);
        $data_->fill($request->except('_token')); // fill untuk menyimpan data dari request
        if(isset($request->is_ganti_password)) {
            if($request->is_ganti_password_val == 1) {
                $data_->password = bcrypt($request->password);
            }
        } 

        # file image | tambahan sri 14/6/2021
        $date_now = date('Y-m-d H:i:s');
        if ($request->hasFile('img')) {
            $image = $request->file('img');
            $nama_cover = $image->getClientOriginalName();
            $split_co = explode('.', $nama_cover);
            $ext_co = $split_co[1];
            $file_name_co = md5($split_co[0] . "-" . $date_now);
            $logo = $request->img;
            $destination_path_co = public_path("temp/");
            $destination_filename_co = $file_name_co . "." . $ext_co;
            $data_->img->move($destination_path_co, $destination_filename_co);
            $data_->img = $file_name_co . "." . $ext_co;

            # SRI | convert image to blob
            $path = $destination_path_co.$destination_filename_co;
            $logo = file_get_contents($path);
            $base64 = base64_encode($logo);
            $data_->image = $base64;

            # SRI | hapus image
            if (File::exists($path)) {
                unlink($path);
            }
        } else {
            $data_->img = $data_->img;
        }

        $validator = $data_->validate();
        if ($validator->fails()) {
            $group_apoteks      = MasterGroupApotek::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama_singkat', 'id');
            $group_apoteks->prepend('-- Pilih Group Apotek --','');

            # Tangkas
            $apoteks      = MasterApotek::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama_singkat', 'id');
            $apoteks->prepend('-- Pilih Apotek --', '');

            $spesialiss      = MasterSpesialis::on($this->getConnectionName())->where('is_deleted', 0)->pluck('spesialis', 'id');
            $spesialiss->prepend('-- Pilih Spesialis --', '');

            Session()->flash('error', 'Gagal menyimpan data!');
            return view('dokter.edit')->with(compact('data_', 'group_apoteks', 'apoteks', 'spesialiss'))->withErrors($validator);
        } else {
            if ($data_->save()) {
                Session()->flash('success', 'Sukses menyimpan data!');
                return redirect('dokter');
            } else {
                Session()->flash(
                    'error',
                    'Gagal menyimpan data!'
                );
                return redirect('dokter');
            }
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
        $data_ = MasterDokter::on($this->getConnectionName())->find($id);
        $data_->is_deleted = 1;
        $data_->deleted_at = date('Y-m-d H:i:s');
        $data_->deleted_by = Auth::user()->id;
        if($data_->save()){
            echo 1;
        } else {
            echo 0;
        }
    }
}
