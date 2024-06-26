<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\MessageFooter;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Auth;

class MessageController extends Controller
{
    # untuk menampilkan halaman awal
    public function index()
    {
        return view('message_footer.index');
    }

    # untuk menampilkan mengambil data dari database
    public function get_data(Request $request)
    {
        DB::statement(DB::raw('set @rownum = 0'));
        $data = MessageFooter::select([DB::raw('@rownum  := @rownum  + 1 AS no'), 'tb_message.*'])
            ->where(function ($query) use ($request) {
                //$query->where('message.is_deleted','=','0');
            });

        $datatables = Datatables::of($data);
        return $datatables
            ->filter(function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->orwhere('name', 'LIKE', '%' . $request->get('search')['value'] . '%');
                    $query->orwhere('email', 'LIKE', '%' . $request->get('search')['value'] . '%');
                    $query->orwhere('phone_number', 'LIKE', '%' . $request->get('search')['value'] . '%');
                    $query->orwhere('additional_message', 'LIKE', '%' . $request->get('search')['value'] . '%');
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

    # untuk menyimpan data dari form store
    public function store(Request $request)
    {
        $data_ = new MessageFooter;
        $data_->fill($request->except('_token')); // fill untuk menyimpan data dari request

        $validator = $data_->validate();
        if ($validator->fails()) {
            Session()->flash('error', 'Gagal menyimpan data!');
            return back()->with(compact('data_'))->withErrors($validator);
        } else {
            if ($data_->save()) {
                Session()->flash('success', 'Sukses menyimpan data!');
                return back();
            } else {
                Session()->flash(
                    'error',
                    'Gagal menyimpan data!'
                );
                return back();
            }
        }
    }

    # untuk menampilakn data detail show
    public function show($id)
    {
        $data_ = MessageFooter::find($id);

        return view('message_footer.show')->with(compact('data_'));
    }

    # untuk menampilkan form edit
    public function edit($id)
    {
        $data_ = MessageFooter::find($id);

        return view('message_footer.edit')->with(compact('data_'));
    }

    # untuk menyimpan data edit
    public function update(Request $request, $id)
    {
        $data_ = MessageFooter::find($id);
        $data_->fill($request->except('_token')); // fill untuk menyimpan data dari request

        $validator = $data_->validate();
        if ($validator->fails()) {
            Session()->flash('error', 'Gagal menyimpan data!');
            return view('message_footer.edit')->with(compact('data_'))->withErrors($validator);
        } else {
            if ($data_->save()) {
                echo 1;
                // Session()->flash('success', 'Sukses menyimpan data!');
                // return redirect('message');
            } else {
                echo 0;
                // Session()->flash('error', 'Gagal menyimpan data!');
                // return redirect('message');
            }
        }
    }

    # untuk menghapus data
    public function destroy($id)
    {
        $data_ = MessageFooter::find($id);
        if ($data_->delete()) {
            echo 1;
        } else {
            echo 0;
        }
    }
}
