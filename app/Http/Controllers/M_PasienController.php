<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\MasterPasien;
use App\MasterJenisKelamin;
use App\MasterKewarganegaraan;
use App\MasterAgama;
use App\MasterGolonganDarah;
use App\RbacRole;
use App\RbacUserRole;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use Auth;
use Mail;

class M_PasienController extends Controller
{
    # untuk menampilkan halaman awal
    public function index()
    {
        return view('pasien.index');
    }

    # untuk menampilkan mengambil data dari database
    public function get_data(Request $request)
    {
        DB::statement(DB::raw('set @rownum = 0'));
        $data = MasterPasien::select([DB::raw('@rownum  := @rownum  + 1 AS no'), 'tb_m_pasien.*'])
            ->where(function ($query) use ($request) {
                //$query->where('pasien.is_deleted','=','0');
            });

        $datatables = Datatables::of($data);
        return $datatables
            ->filter(function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->orwhere('nik', 'LIKE', '%' . $request->get('search')['value'] . '%');
                    $query->orwhere('nama', 'LIKE', '%' . $request->get('search')['value'] . '%');
                    $query->orwhere('email', 'LIKE', '%' . $request->get('search')['value'] . '%');
                });
            })
            ->addcolumn('action', function ($data) {
                $btn = '<div class="btn-group">';
                $btn .= '<span class="btn btn-primary" onClick="show_data(' . $data->id . ')" data-toggle="tooltip" data-placement="top" title="Show Data"><i class="fa fa-eye"></i></span>';
                $btn .= '<span class="btn btn-success" onClick="edit_data(' . $data->id . ')" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span>';
                $btn .= '<span class="btn btn-danger" onClick="delete_data(' . $data->id . ')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-trash"></i></span>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->addIndexColumn()
            ->make(true);
    }

    # untuk menampilkan form create
    public function create()
    {
        $data_ = new MasterPasien; //inisialisasi array

        $jenis_kelamins      = MasterJenisKelamin::where('is_deleted', 0)->pluck('jenis_kelamin', 'id');
        $jenis_kelamins->prepend('-- Pilih Status --', '');

        $kewarganegaraans      = MasterKewarganegaraan::where('is_deleted', 0)->pluck('kewarganegaraan', 'id');
        $kewarganegaraans->prepend('-- Pilih Status --', '');

        $agamas      = MasterAgama::where('is_deleted', 0)->pluck('agama', 'id');
        $agamas->prepend('-- Pilih Status --', '');

        $gol_darahs      = MasterGolonganDarah::where('is_deleted', 0)->pluck('golongan_darah', 'id');
        $gol_darahs->prepend('-- Pilih Status --', '');

        return view('pasien.create')->with(compact('data_', 'jenis_kelamins', 'kewarganegaraans', 'agamas', 'gol_darahs'));
    }

    # untuk menyimpan data dari form store
    public function store(Request $request)
    {
        $data_ = new MasterPasien;
        $data_->fill($request->except('_token')); // fill untuk menyimpan data dari request

        $jenis_kelamins      = MasterJenisKelamin::where('is_deleted', 0)->pluck('jenis_kelamin', 'id');
        $jenis_kelamins->prepend('-- Pilih Status --', '');

        $kewarganegaraans      = MasterKewarganegaraan::where('is_deleted', 0)->pluck('kewarganegaraan', 'id');
        $kewarganegaraans->prepend('-- Pilih Status --', '');

        $agamas      = MasterAgama::where('is_deleted', 0)->pluck('agama', 'id');
        $agamas->prepend('-- Pilih Status --', '');

        $gol_darahs      = MasterGolonganDarah::where('is_deleted', 0)->pluck('golongan_darah', 'id');
        $gol_darahs->prepend('-- Pilih Status --', '');

        $validator = $data_->validate();
        if ($validator->fails()) {
            Session()->flash('error', 'Gagal menyimpan data!');
            return view('pasien.create')->with(compact('data_', 'jenis_kelamins', 'kewarganegaraans', 'agamas', 'gol_darahs'))->withErrors($validator);
        } else {
            if ($data_->save()) {
                Session()->flash('success', 'Sukses menyimpan data!');
                return redirect('pasien');
            } else {
                Session()->flash(
                    'error',
                    'Gagal menyimpan data!'
                );
                return redirect('pasien');
            }
        }
    }

    # untuk menampilakn data detail show
    public function show($id)
    {
        $data_ = MasterPasien::find($id);

        return view('pasien.show')->with(compact('data_'));
    }

    # untuk menampilkan form edit
    public function edit($id)
    {
        $data_ = MasterPasien::find($id);

        $jenis_kelamins      = MasterJenisKelamin::where('is_deleted', 0)->pluck('jenis_kelamin', 'id');
        $jenis_kelamins->prepend('-- Pilih Status --', '');

        $kewarganegaraans      = MasterKewarganegaraan::where('is_deleted', 0)->pluck('kewarganegaraan', 'id');
        $kewarganegaraans->prepend('-- Pilih Status --', '');

        $agamas      = MasterAgama::where('is_deleted', 0)->pluck('agama', 'id');
        $agamas->prepend('-- Pilih Status --', '');

        $gol_darahs      = MasterGolonganDarah::where('is_deleted', 0)->pluck('golongan_darah', 'id');
        $gol_darahs->prepend('-- Pilih Status --', '');

        return view('pasien.edit')->with(compact('data_', 'jenis_kelamins', 'kewarganegaraans', 'agamas', 'gol_darahs'));
    }

    # untuk menyimpan data edit
    public function update(Request $request, $id)
    {
        $data_ = MasterPasien::find($id);
        $data_->fill($request->except('_token')); // fill untuk menyimpan data dari request

        $validator = $data_->validate();
        if ($validator->fails()) {
            echo 0;
        } else {
            if ($data_->save()) {
                echo 1;
                // Session()->flash('success', 'Sukses menyimpan data!');
                // return redirect('pasien');
            } else {
                echo 0;
                // Session()->flash('error', 'Gagal menyimpan data!');
                // return redirect('pasien');
            }
        }
    }

    # untuk menghapus data
    public function destroy($id)
    {
        $data_ = MasterPasien::find($id);
        $data_->is_deleted = 1;
        $data_->deleted_by = Auth::user()->id;
        $data_->deleted_at = date('Y-m-d H:i:s');
        if ($data_->save()) {
            echo 1;
        } else {
            echo 0;
        }
    }

    /*
        =======================================================================================
        For     : Get token
        Author  : Govi.
        Date    : 16/09/2021
        =======================================================================================
    */
    protected function getToken()
    {
        $random = Str::random(40);
        return hash_hmac('sha256', $random, config('app.key'));
    }

    /*
        =======================================================================================
        For     : Menampilkan halaman invite pasien
        Author  : Govi.
        Date    : 14/09/2021
        =======================================================================================
    */
    public function invite_view(Request $request)
    {
        $pasien = new MasterPasien;
        $roles = RbacRole::where('is_deleted', 0)->get();
        return view('pasien.invite')->with(compact('pasien', 'roles'));
    }

    /*
        =======================================================================================
        For     : Kirim email invite pasien
        Author  : Govi.
        Date    : 14/09/2021
        =======================================================================================
    */
    public function invite_submit(Request $request)
    {
        DB::beginTransaction(); 
        try {
            $pasien = new MasterPasien;
            $pasien->fill($request->except('_token'));
            $pasien->remember_token = $this->getToken();
            $pasien->created_at = date('Y-m-d H:i:s');
            $pasien->created_by = Auth::user()->id;

            $validator = $pasien->validate_invite();
            if($validator->fails()){
                $roles = RbacRole::where('is_deleted', 0)->get();
                return view('pasien.invite')
                    ->with(compact('pasien', 'roles'))
                    ->withErrors($validator);
            } else {
                $pasien->save();
                foreach ($request->roles as $role) {
                    $rbac_user_role = new RbacUserRole;
                    $rbac_user_role->id_user = $pasien->id;
                    $rbac_user_role->id_role = $role;
                    $rbac_user_role->save();
                }
                $link = route('confirm_pasien', $pasien->remember_token);
                Mail::to($pasien->email)->send(new \App\Mail\MailInvitePasien($pasien, $link));
                DB::commit();
                session()->flash('success', 'Sukses invite pasien!');
                return redirect('pasien');
            }
        } catch(\Exception $e){
            dd($e);
            DB::rollback();
            session()->flash('error', 'Error!');
            return redirect('pasien');
        }
    }

    /*
        =======================================================================================
        For     : Halaman confirm invite pasien
        Author  : Wiwan Gussanda.
        Date    : 16/09/2021
        =======================================================================================
    */
    public function invite_confirm(Request $request)
    {
        $pasien = MasterPasien::where('remember_token', $request->token)->first();

        $jenis_kelamins      = MasterJenisKelamin::where('is_deleted', 0)->pluck('jenis_kelamin', 'id');
        $jenis_kelamins->prepend('-- Pilih Status --', '');

        $kewarganegaraans      = MasterKewarganegaraan::where('is_deleted', 0)->pluck('kewarganegaraan', 'id');
        $kewarganegaraans->prepend('-- Pilih Status --', '');

        $agamas      = MasterAgama::where('is_deleted', 0)->pluck('agama', 'id');
        $agamas->prepend('-- Pilih Status --', '');

        $gol_darahs      = MasterGolonganDarah::where('is_deleted', 0)->pluck('golongan_darah', 'id');
        $gol_darahs->prepend('-- Pilih Status --', '');

        return view('frontend.confirm_invite_pasien')->with(compact(
            'pasien',
            'jenis_kelamins',
            'kewarganegaraans',
            'agamas',
            'gol_darahs',
        ));
    }

    /*
        =======================================================================================
        For     : store confirm invite pasien
        Author  : Wiwan Gussanda.
        Date    : 16/09/2021
        =======================================================================================
    */
    public function invite_confirm_post(Request $request){
        $pasien = MasterPasien::where('id', $request->id)->first();
        $pasien->fill($request->except('_token'));
        
        $validator = $pasien->validator_confirm_pasien();
        if($validator->fails()){
            $jenis_kelamins      = MasterJenisKelamin::where('is_deleted', 0)->pluck('jenis_kelamin', 'id');
            $jenis_kelamins->prepend('-- Pilih Status --', '');

            $kewarganegaraans      = MasterKewarganegaraan::where('is_deleted', 0)->pluck('kewarganegaraan', 'id');
            $kewarganegaraans->prepend('-- Pilih Status --', '');

            $agamas      = MasterAgama::where('is_deleted', 0)->pluck('agama', 'id');
            $agamas->prepend('-- Pilih Status --', '');

            $gol_darahs      = MasterGolonganDarah::where('is_deleted', 0)->pluck('golongan_darah', 'id');
            $gol_darahs->prepend('-- Pilih Status --', '');
            
            return view('frontend.confirm_invite_pasien')
                ->with(compact(
                    'pasien',
                    'jenis_kelamins',
                    'kewarganegaraans',
                    'agamas',
                    'gol_darahs',
                ))
                ->withErrors($validator);
        }else{
            $pasien->password = bcrypt($request->password);
            $pasien->activated = 1;
            $pasien->updated_by = $pasien->id;
            $pasien->remember_token = null;
            $pasien->save();
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('/login_pasien')->with('status', 'Selamat anda telah melengkapi data, Silakan isikan username dan password untuk login ke sistem');
        }
    }
}
