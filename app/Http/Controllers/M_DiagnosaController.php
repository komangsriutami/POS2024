<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterDiagnosa;
use Auth;
use Datatables;
use DB;

class M_DiagnosaController extends Controller
{   
	# untuk menampilkan halaman awal
    public function index()
    {
        return view('diagnosa.index');
    }

    # untuk menampilkan mengambil data dari database
    public function get_data(Request $request)
    {
        DB::statement(DB::raw('set @rownum = 0'));
        $data = MasterDiagnosa::select([DB::raw('@rownum  := @rownum  + 1 AS no'), 'tb_m_diagnosa.*'])
            ->where(function ($query) use ($request) {
                $query->where('tb_m_diagnosa.is_deleted', '=', '0');
            });

        $datatables = Datatables::of($data);
        return $datatables
            ->filter(function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->orwhere('diagnosa', 'LIKE', '%' . $request->get('search')['value'] . '%');
                    $query->orwhere('keterangan', 'LIKE', '%' . $request->get('search')['value'] . '%');
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
        $data_ = new MasterDiagnosa; //inisialisasi array

        return view('diagnosa.create')->with(compact('data_'));
    }

    # untuk menyimpan data dari form store
    public function store(Request $request)
    {
        $data_ = new MasterDiagnosa;
        $data_->fill($request->except('_token')); // fill untuk menyimpan data dari request

        $validator = $data_->validate();
        if ($validator->fails()) {
            Session()->flash('error', 'Gagal menyimpan data!');
            return view('diagnosa.create')->with(compact('data_'))->withErrors($validator);
        } else {
            if ($data_->save()) {
                Session()->flash('success', 'Sukses menyimpan data!');
                return redirect('diagnosa');
            } else {
                Session()->flash(
                    'error',
                    'Gagal menyimpan data!'
                );
                return redirect('diagnosa');
            }
        }
    }

    # untuk menampilakn data detail show
    public function show($id)
    {
        $data_ = MasterDiagnosa::find($id);

        return view('diagnosa.show')->with(compact('data_'));
    }

    # untuk menampilkan form edit
    public function edit($id)
    {
        $data_ = MasterDiagnosa::find($id);
        return view('diagnosa.edit')->with(compact('data_'));
    }

    # untuk menyimpan data edit
    public function update(Request $request, $id)
    {
        $data_ = MasterDiagnosa::find($id);
        $data_->fill($request->except('_token')); // fill untuk menyimpan data dari request

        $validator = $data_->validate();
        if ($validator->fails()) {
            Session()->flash('error', 'Gagal menyimpan data!');
            return view('diagnosa.edit')->with(compact('data_'))->withErrors($validator);
        } else {
            if ($data_->save()) {
                echo 1;
                // Session()->flash('success', 'Sukses menyimpan data!');
                // return redirect('diagnosa');
            } else {
                echo 0;
                // Session()->flash('error', 'Gagal menyimpan data!');
                // return redirect('diagnosa');
            }
        }
    }

    # untuk menghapus data
    public function destroy($id)
    {
        $data_ = MasterDiagnosa::find($id);
        $data_->is_deleted = 1;
        $data_->deleted_at = date('Y-m-d H:i:s');
        $data_->deleted_by = Auth::user()->id;
        if ($data_->save()) {
            echo 1;
        } else {
            echo 0;
        }
    }
}
