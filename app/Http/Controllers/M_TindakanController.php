<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\MasterTindakan;
use Auth;
use Datatables;
use DB;
use App\Traits\DynamicConnectionTrait;

class M_TindakanController extends Controller
{
    use DynamicConnectionTrait;
    # untuk menampilkan halaman awal
    public function index()
    {
        return view('tindakan.index');
    }

    # untuk menampilkan mengambil data dari database
    public function get_data(Request $request)
    {
        DB::connection($this->getConnection())->statement(DB::raw('set @rownum = 0'));
        $data = MasterTindakan::select([DB::raw('@rownum  := @rownum  + 1 AS no'), 'tb_m_tindakan.*'])
            ->where(function ($query) use ($request) {
                $query->where('tb_m_tindakan.is_deleted','=','0');
            });

        $datatables = Datatables::of($data);
        return $datatables
            ->filter(function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->orwhere('nama', 'LIKE', '%' . $request->get('search')['value'] . '%');
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
        $data_ = new MasterTindakan; //inisialisasi array
        $data_->setDynamicConnection();

        return view('tindakan.create')->with(compact('data_'));
    }

    # untuk menyimpan data dari form store
    public function store(Request $request)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        $data_ = new MasterTindakan;
        $data_->setDynamicConnection();
        $data_->fill($request->except('_token')); // fill untuk menyimpan data dari request

        $validator = $data_->validate();
        if ($validator->fails()) {
            Session()->flash('error', 'Gagal menyimpan data!');
            return view('tindakan.create')->with(compact('data_'))->withErrors($validator);
        } else {
            if ($data_->save()) {
                Session()->flash('success', 'Sukses menyimpan data!');
                return redirect('tindakan');
            } else {
                Session()->flash(
                    'error',
                    'Gagal menyimpan data!'
                );
                return redirect('tindakan');
            }
        }
    }

    # untuk menampilakn data detail show
    public function show($id)
    {
        $data_ = MasterTindakan::on($this->getConnectionName())->find($id);

        return view('tindakan.show')->with(compact('data_'));
    }

    # untuk menampilkan form edit
    public function edit($id)
    {
        $data_ = MasterTindakan::on($this->getConnectionName())->find($id);
        return view('tindakan.edit')->with(compact('data_'));
    }

    # untuk menyimpan data edit
    public function update(Request $request, $id)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        $data_ = MasterTindakan::on($this->getConnectionName())->find($id);
        $data_->fill($request->except('_token')); // fill untuk menyimpan data dari request

        $validator = $data_->validate();
        if ($validator->fails()) {
            Session()->flash('error', 'Gagal menyimpan data!');
            return view('tindakan.edit')->with(compact('data_'))->withErrors($validator);
        } else {
            if ($data_->save()) {
                echo 1;
                // Session()->flash('success', 'Sukses menyimpan data!');
                // return redirect('tindakan');
            } else {
                echo 0;
                // Session()->flash('error', 'Gagal menyimpan data!');
                // return redirect('tindakan');
            }
        }
    }

    # untuk menghapus data
    public function destroy($id)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        $data_ = MasterTindakan::on($this->getConnectionName())->find($id);
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
