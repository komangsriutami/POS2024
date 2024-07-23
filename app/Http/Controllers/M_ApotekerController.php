<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterApoteker;
use App\MasterAgama;
use App\MasterGolonganDarah;
use App\MasterJenisKelamin;
use App\MasterKewarganegaraan;
use App\MasterGroupApotek;
use App;
use App\MasterApotek;
use Datatables;
use DB;
use Excel;
use Auth;
use Dotenv\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Traits\DynamicConnectionTrait;

class M_ApotekerController extends Controller
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
        return view('apoteker.index');
    }


    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function list_apoteker(Request $request)
    {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        $super_admin = session('super_admin');
        DB::connection($this->getConnection())->statement(DB::raw('set @rownum = 0'));
        $data = MasterApoteker::select([DB::raw('@rownum  := @rownum  + 1 AS no'), 'tb_m_apoteker.*'])
            ->where(function ($query) use ($request, $super_admin) {
                $query->where('tb_m_apoteker.is_deleted', '=', '0');
                if ($super_admin == 0) {
                    $query->where('tb_m_apoteker.id_group_apotek', Auth::user()->id_group_apotek);
                }
            });

        $datatables = Datatables::of($data);
        return $datatables
            ->filter(function ($query) use ($request, $order_column, $order_dir) {
                $query->where(function ($query) use ($request) {
                    $query->orwhere('nama', 'LIKE', '%' . $request->get('search')['value'] . '%');
                    $query->orwhere('nostra', 'LIKE', '%' . $request->get('search')['value'] . '%');
                    $query->orwhere('telepon', 'LIKE', '%' . $request->get('search')['value'] . '%');
                    $query->orwhere('email', 'LIKE', '%' . $request->get('search')['value'] . '%');
                });
            })
            ->addcolumn('action', function ($data) {
                $btn = '<div class="btn-group">';
                $btn .= '<span class="btn btn-primary" onClick="edit_data(' . $data->id . ')" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span>';
                $btn .= '<span class="btn btn-danger" onClick="delete_apoteker(' . $data->id . ')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
                $btn .= '</div>';
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
        $apoteker = new MasterApoteker;
        $apoteker->setDynamicConnection();

        $jenis_kelamins = MasterJenisKelamin::on($this->getConnectionName())->where('is_deleted', 0)->pluck('jenis_kelamin', 'id');
        $jenis_kelamins->prepend('-- Pilih Jenis Kelamin --', '');

        $kewarganegaraans = MasterKewarganegaraan::on($this->getConnectionName())->where('is_deleted', 0)->pluck('kewarganegaraan', 'id');
        $kewarganegaraans->prepend('-- Pilih Kewarganegaraan --', '');

        $agamas = MasterAgama::on($this->getConnectionName())->where('is_deleted', 0)->pluck('agama', 'id');
        $agamas->prepend('-- Pilih Agama --', '');

        $golongan_darahs = MasterGolonganDarah::on($this->getConnectionName())->where('is_deleted', 0)->pluck('golongan_darah', 'id');
        $golongan_darahs->prepend('-- Pilih Golongan Darah --', '');

        $group_apoteks      = MasterGroupApotek::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $group_apoteks->prepend('-- Pilih Group Apotek --', '');

        return view('apoteker.create')->with(compact('apoteker', 'jenis_kelamins', 'agamas', 'kewarganegaraans', 'golongan_darahs', 'group_apoteks'));
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
        $apoteker = new MasterApoteker;
        $apoteker->setDynamicConnection();
        $apoteker->fill($request->except('_token'));

        $jenis_kelamins = MasterJenisKelamin::on($this->getConnectionName())->where('is_deleted', 0)->pluck('jenis_kelamin', 'id');
        $jenis_kelamins->prepend('-- Pilih Jenis Kelamin --', '');

        $kewarganegaraans = MasterKewarganegaraan::on($this->getConnectionName())->where('is_deleted', 0)->pluck('kewarganegaraan', 'id');
        $kewarganegaraans->prepend('-- Pilih Kewarganegaraan --', '');

        $agamas = MasterAgama::on($this->getConnectionName())->where('is_deleted', 0)->pluck('agama', 'id');
        $agamas->prepend('-- Pilih Agama --', '');

        $golongan_darahs = MasterGolonganDarah::on($this->getConnectionName())->where('is_deleted', 0)->pluck('golongan_darah', 'id');
        $golongan_darahs->prepend('-- Pilih Golongan Darah --', '');

        $group_apoteks      = MasterGroupApotek::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $group_apoteks->prepend('-- Pilih Group Apotek --', '');

        $validator = $apoteker->validate();
        if ($validator->fails()) {
            return view('apoteker.create')->with(compact('apoteker', 'jenis_kelamins', 'kewarganegaraans', 'agamas', 'golongan_darahs', 'group_apoteks'))->withErrors($validator);
        } else {
            $apoteker->tgl_lahir = date('Y-m-d', strtotime($apoteker->tgl_lahir));
            $apoteker->created_by = Auth::user()->id;
            $apoteker->save();
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('apoteker');
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
        $apoteker        = MasterApoteker::on($this->getConnectionName())->find($id);

        $jenis_kelamins = MasterJenisKelamin::on($this->getConnectionName())->where('is_deleted', 0)->pluck('jenis_kelamin', 'id');
        $jenis_kelamins->prepend('-- Pilih Jenis Kelamin --', '');

        $kewarganegaraans = MasterKewarganegaraan::on($this->getConnectionName())->where('is_deleted', 0)->pluck('kewarganegaraan', 'id');
        $kewarganegaraans->prepend('-- Pilih Kewarganegaraan --', '');

        $agamas = MasterAgama::on($this->getConnectionName())->where('is_deleted', 0)->pluck('agama', 'id');
        $agamas->prepend('-- Pilih Agama --', '');

        $golongan_darahs = MasterGolonganDarah::on($this->getConnectionName())->where('is_deleted', 0)->pluck('golongan_darah', 'id');
        $golongan_darahs->prepend('-- Pilih Golongan Darah --', '');

        $group_apoteks      = MasterGroupApotek::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $group_apoteks->prepend('-- Pilih Group Apotek --', '');

        return view('apoteker.edit')->with(compact('apoteker', 'jenis_kelamins', 'kewarganegaraans', 'agamas', 'golongan_darahs', 'group_apoteks'));
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
        $apoteker = MasterApoteker::on($this->getConnectionName())->find($id);
        $apoteker->fill($request->except('_token'));

        $validator = $apoteker->validate();
        if ($validator->fails()) {
            echo json_encode(array('status' => 0));
        } else {
            $apoteker->tgl_lahir = date('Y-m-d', strtotime($apoteker->tgl_lahir));
            $apoteker->updated_by = Auth::user()->id;
            $apoteker->save();
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
        $apoteker = MasterApoteker::on($this->getConnectionName())->find($id);
        $apoteker->is_deleted = 1;
        if ($apoteker->save()) {
            echo 1;
        } else {
            echo 0;
        }
    }


    public function invite_view(Request $request)
    {
        $apoteker = new MasterApoteker;
        $apoteker->setDynamicConnection();
        return view('apoteker.invite')->with(compact('apoteker'));
    }

    public function invite_submit(Request $request)
    {
        DB::connection($this->getConnectionName())->beginTransaction(); 
        try {
            $apoteker = new MasterApoteker;
            $apoteker->setDynamicConnection();
            $apoteker->fill($request->except('_token'));

            $validator = $apoteker->validate_invite();
            if ($validator->fails()) {
                return view('apoteker.invite')
                    ->with(compact('apoteker'))
                    ->withErrors($validator);
            } else {
                $token = $this->getToken();
                $apoteker->remember_token = $token;
                $apoteker->created_at = date('Y-m-d H:i:s');
                $apoteker->save();

                $link = route('confirm_apoteker', $apoteker->remember_token);
                Mail::to($apoteker->email)->send(new \App\Mail\MailInviteApoteker($apoteker, $link));
                DB::connection($this->getConnectionName())->commit();

                session()->flash('success', 'Sukses invite Apoteker!');
                return redirect('apoteker');
            }
        } catch (\Exception $e) {
            dd($e);
            DB::connection($this->getConnectionName())->rollback();
            session()->flash('error', 'Error!');
            return redirect('apoteker');
        }
    }

    public function invite_confirm(Request $request)
    {
        $apoteker = MasterApoteker::on($this->getConnectionName())->where('remember_token', $request->token)->first();

        $jenis_kelamins = MasterJenisKelamin::on($this->getConnectionName())->where('is_deleted', 0)->pluck('jenis_kelamin', 'id');
        $jenis_kelamins->prepend('-- Pilih Jenis Kelamin --', '');

        $kewarganegaraans = MasterKewarganegaraan::on($this->getConnectionName())->where('is_deleted', 0)->pluck('kewarganegaraan', 'id');
        $kewarganegaraans->prepend('-- Pilih Kewarganegaraan --', '');

        $agamas = MasterAgama::on($this->getConnectionName())->where('is_deleted', 0)->pluck('agama', 'id');
        $agamas->prepend('-- Pilih Agama --', '');

        $golongan_darahs = MasterGolonganDarah::on($this->getConnectionName())->where('is_deleted', 0)->pluck('golongan_darah', 'id');
        $golongan_darahs->prepend('-- Pilih Golongan Darah --', '');

        $group_apoteks      = MasterGroupApotek::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $group_apoteks->prepend('-- Pilih Group Apotek --', '');

        return view('frontend.confirm_invite_apoteker')->with(compact(
            'apoteker',
            'jenis_kelamins',
            'kewarganegaraans',
            'agamas',
            'golongan_darahs',
            'group_apoteks'
        ));
    }

    public function invite_confirm_post(Request $request)
    {
        $apoteker = MasterApoteker::on($this->getConnectionName())->where('id', $request->id)->first();
        $apoteker->fill($request->except('_token'));

        $validator = $apoteker->validate_confirm_apoteker();

        if ($validator->fails()) {

            $jenis_kelamins = MasterJenisKelamin::on($this->getConnectionName())->where('is_deleted', 0)->pluck('jenis_kelamin', 'id');
            $jenis_kelamins->prepend('-- Pilih Jenis Kelamin --', '');

            $kewarganegaraans = MasterKewarganegaraan::on($this->getConnectionName())->where('is_deleted', 0)->pluck('kewarganegaraan', 'id');
            $kewarganegaraans->prepend('-- Pilih Kewarganegaraan --', '');

            $agamas = MasterAgama::on($this->getConnectionName())->where('is_deleted', 0)->pluck('agama', 'id');
            $agamas->prepend('-- Pilih Agama --', '');

            $golongan_darahs = MasterGolonganDarah::on($this->getConnectionName())->where('is_deleted', 0)->pluck('golongan_darah', 'id');
            $golongan_darahs->prepend('-- Pilih Golongan Darah --', '');

            $group_apoteks      = MasterGroupApotek::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama_singkat', 'id');
            $group_apoteks->prepend('-- Pilih Group Apotek --', '');

            return view('frontend.confirm_invite_apoteker')
                ->with(compact(
                    'apoteker',
                    'jenis_kelamins',
                    'kewarganegaraans',
                    'agamas',
                    'golongan_darahs',
                    'group_apoteks'
                ))
                ->withErrors($validator);
        } else {
            $apoteker->password = bcrypt($request->password);
            $apoteker->activated = 1;
            $apoteker->updated_by = $apoteker->id;
            $apoteker->remember_token = null;
            $apoteker->save();
            session()->flash('success', 'Sukses melakukan registrasi!');
            return redirect('/login_apoteker')->with('status', 'Selamat anda telah melengkapi data, Silakan isikan username dan password untuk login ke sistem');
        }
    }

    protected function getToken()
    {
        $random = Str::random(40);
        return hash_hmac('sha256', $random, config('app.key'));
    }
}
