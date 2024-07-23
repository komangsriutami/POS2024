<?php

namespace App\Http\Controllers;

use App\MasterAset;
use Illuminate\Http\Request;
use App;
use Datatables;
use DB;
use Excel;
use Auth;
use App\Traits\DynamicConnectionTrait;

class M_AsetController extends Controller
{
    use DynamicConnectionTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('aset.index');
    }

    public function list_aset(Request $request)
    {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::connection($this->getConnection())->statement(DB::raw('set @rownum = 0'));
        $data = MasterAset::select([DB::raw('@rownum  := @rownum  + 1 AS no'), 'tb_m_aset.*'])
            ->where(function ($query) use ($request) {
                $query->where('tb_m_aset.is_deleted', '=', '0');
            });

        $datatables = DataTables::of($data);
        return $datatables
            ->filter(function ($query) use ($request, $order_column, $order_dir) {
                $query->where(function ($query) use ($request) {
                    $query->orwhere('nama', 'LIKE', '%' . $request->get('search')['value'] . '%');
                });
            })
            ->editcolumn('id_jenis_aset', function($data){
                if($data->id_jenis_aset == 1) {
                    $aset = 'Aset Tetap';
                } else {

                    $aset = 'Aset Tak Berwujud';
                }
                return $aset; 
            }) 
            ->addcolumn('action', function ($data) {
                $btn = '<div class="btn-group">';
                $btn .= '<span class="btn btn-primary" onClick="edit_data(' . $data->id . ')" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span>';
                $btn .= '<span class="btn btn-danger" onClick="delete_data(' . $data->id . ')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['action', 'id_jenis_aset'])
            ->addIndexColumn()
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $aset = new MasterAset();
        $aset->setDynamicConnection();

        $jenis_asets = collect([['id' => 1, 'nama' => 'Aset Tetap'], ['id' => 2, 'nama' => 'Aset Tak Berwujud']])->pluck('nama', 'id');
        $jenis_asets->prepend('-- Pilih Jenis Aset --','');

        return view('aset.create')->with(compact('aset', 'jenis_asets'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $aset = new MasterAset();
        $aset->setDynamicConnection();
        $aset->fill($request->except('_token'));

        $validator = $aset->validate();
        if ($validator->fails()) {
            $jenis_asets = collect([['id' => 1, 'nama' => 'Aset Tetap'], ['id' => 2, 'nama' => 'Aset Tak Berwujud']])->pluck('nama', 'id');
            $jenis_asets->prepend('-- Pilih Jenis Aset --','');

            return view('aset.create')->with(compact('aset', 'jenis_asets'))->withErrors($validator);
        } else {
            $aset->created_by = Auth::user()->id;
            $aset->created_at = date('Y-m-d H:i:s');
            $aset->save();
            session()->flash('success', 'Sukses menyimpan data !');
            return redirect('aset');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\MasterAset  $masterAset
     * @return \Illuminate\Http\Response
     */
    public function show(MasterAset $masterAset)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\MasterAset  $masterAset
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $aset = MasterAset::on($this->getConnectionName())->find($id);
        $jenis_asets = collect([['id' => 1, 'nama' => 'Aset Tetap'], ['id' => 2, 'nama' => 'Aset Tak Berwujud']])->pluck('nama', 'id');
        $jenis_asets->prepend('-- Pilih Jenis Aset --','');

        return view('aset.edit')->with(compact('aset', 'jenis_asets'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\MasterAset  $masterAset
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $aset = MasterAset::on($this->getConnectionName())->find($id);
        $aset->fill($request->except('_token'));

        $validator = $aset->validate();
        if ($validator->fails()) {
            echo json_encode(array('status' => 0));
        } else {
            $aset->updated_by = Auth::user()->id;
            $aset->updated_at = date('Y-m-d H:i:s');
            $aset->save();
            echo json_encode(array('status' => 1));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\MasterAset  $masterAset
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $aset = MasterAset::on($this->getConnectionName())->find($id);
        $aset->is_deleted = 1;
        $aset->deleted_at = date('Y-m-d H:i:s');
        $aset->deleted_by = Auth::user()->id;
        if ($aset->save()) {
            echo 1;
        } else {
            echo 0;
        }
    }
}
