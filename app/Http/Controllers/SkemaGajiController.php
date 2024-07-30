<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterApotek;
use App\MasterGroupApotek;
use App\MasterJabatan;
use App\MasterPosisi;
use App\MasterStatusKaryawan;
use App\SkemaGaji;
use App\SkemaGajiDetail;
use Auth;
use App;
use Datatables;
use DB;
use Excel;
use App\Traits\DynamicConnectionTrait;

class SkemaGajiController extends Controller
{
    use DynamicConnectionTrait;
    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 22/02/
        =======================================================================================
    */
    public function index()
    {
        return view('skema_gaji.index');
    }


    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 22/02/2020
        =======================================================================================
    */
    public function list_skema_gaji(Request $request)
    {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        $super_admin = session('super_admin');
        DB::connection($this->getConnection())->statement(DB::raw('set @rownum = 0'));
        $data = SkemaGaji::on($this->getConnectionName())->select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_skema_gaji.*'])
        ->where(function($query) use($request, $super_admin){
            $query->where('tb_skema_gaji.is_deleted','=','0');
            if($super_admin == 0) {
                $query->where('tb_skema_gaji.id_group_apotek', Auth::user()->id_group_apotek);
            }
        });
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->orwhere('nama','LIKE','%'.$request->get('search')['value'].'%');
            });
        })   
         ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
                $btn .= '<span class="btn btn-primary" onClick="edit_data('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span>';
                $btn .= '<a href="'.url('/skema_gaji/setting/'.$data->id).'" title="Setting Skema gaji" class="btn btn-warning"><span data-toggle="tooltip" data-placement="top" title="Setting Skema gaji"><i class="fa fa-cogs"></i></span></a>';
                $btn .= '<span class="btn btn-danger" onClick="delete_apotek('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
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
        Author  : Sri U.
        Date    : 22/02/2020
        =======================================================================================
    */
    public function create()
    {
    	$skema_gaji = new SkemaGaji;
        $skema_gaji->setDynamicConnection();

        return view('skema_gaji.create')->with(compact('skema_gaji'));
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 22/02/2020
        =======================================================================================
    */
    public function store(Request $request)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        $skema_gaji = new SkemaGaji;
        $skema_gaji->setDynamicConnection();
        $skema_gaji->fill($request->except('_token'));
        $skema_gaji->id_group_apotek = Auth::user()->id_group_apotek;

        $validator = $skema_gaji->validate();
        if($validator->fails()){
            return view('skema_gaji.create')->with(compact('skema_gaji'))->withErrors($validator);
        }else{
            $skema_gaji->created_at = date('Y-m-d H:i:s');
            $skema_gaji->created_by = Auth::user()->id;
            $skema_gaji->save();
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('skema_gaji');
        }
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 22/02/2020
        =======================================================================================
    */
    public function show($id)
    {
        //
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 22/02/2020
        =======================================================================================
    */
    public function edit($id)
    {
        $skema_gaji = SkemaGaji::on($this->getConnectionName())->find($id);

        return view('skema_gaji.edit')->with(compact('skema_gaji'));
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 22/02/2020
        =======================================================================================
    */
    public function update(Request $request, $id)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        $skema_gaji = SkemaGaji::on($this->getConnectionName())->find($id);
        $skema_gaji->fill($request->except('_token'));
        $skema_gaji->id_group_apotek = Auth::user()->id_group_apotek;

        $validator = $skema_gaji->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            $skema_gaji->updated_at = date('Y-m-d H:i:s');
            $skema_gaji->updated_by = Auth::user()->id;
            $skema_gaji->save();
            echo json_encode(array('status' => 1));
        }
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 22/02/2020
        =======================================================================================
    */
    public function destroy($id)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        $skema_gaji = SkemaGaji::on($this->getConnectionName())->find($id);
        $skema_gaji->is_deleted = 1;
        $skema_gaji->deleted_at = date('Y-m-d H:i:s');
        $skema_gaji->deleted_by = Auth::user()->id;
        if($skema_gaji->save()){
            echo 1;
        } else {
            echo 0;
        }
    }

    public function setting($id) {
        $skema_gaji = SkemaGaji::on($this->getConnectionName())->find($id);
        $jabatans = MasterJabatan::on($this->getConnectionName())->where('is_deleted', 0)->where('id_group_apotek', session('id_apotek_active'))->get();
        $posisis = MasterPosisi::on($this->getConnectionName())->where('is_deleted', 0)->where('id_group_apotek', session('id_apotek_active'))->get();
        $status_karyawans = MasterStatusKaryawan::get();

        $data_ = array();
        foreach ($posisis as $a => $ps) {
            $new = array();
            foreach ($jabatans as $b => $jb) {
                foreach ($status_karyawans as $c => $sk) {
                    $cek = SkemaGajiDetail::on($this->getConnectionName())->where('id_skema_gaji', $id)->where('id_jabatan', $jb->id)->where('id_posisi', $ps->id)->where('id_status_karyawan', $sk->id)->first();
                    if(empty($cek)) {
                        $skema_gaji_detail = new SkemaGajiDetail;
                        $skema_gaji_detail->setDynamicConnection();
                        $skema_gaji_detail->nama_jabatan = $jb->nama;
                        $skema_gaji_detail->nama_status = $sk->nama;
                        $skema_gaji_detail->id_skema_gaji = $id;
                        $skema_gaji_detail->id_posisi = $ps->id;
                        $skema_gaji_detail->id_jabatan = $jb->id;
                        $skema_gaji_detail->id_status_karyawan = $sk->id;
                    } else {
                        $skema_gaji_detail = SkemaGajiDetail::on($this->getConnectionName())->find($cek->id);
                        $skema_gaji_detail->nama_jabatan = $jb->nama;
                        $skema_gaji_detail->nama_status = $sk->nama;
                    }

                    $new[] = $skema_gaji_detail;
                }
            }
            $data_[$ps->id] = $new;
        }

        return view('skema_gaji.setting')->with(compact('skema_gaji', 'jabatans', 'status_karyawans', 'posisis', 'data_'));
    } 

    public function add_update_detail(Request $request, $id) {
        $skema_gaji = SkemaGaji::on($this->getConnectionName())->find($id);
        $skema_gaji_details = $request->detail;
        $i = 0;
        foreach ($skema_gaji_details as $key => $val) {
            $cek = SkemaGajiDetail::on($this->getConnectionName())->where('id_skema_gaji', $val['id_skema_gaji'])->where('id_jabatan', $val['id_jabatan'])->where('id_posisi', $val['id_posisi'])->where('id_status_karyawan', $val['id_status_karyawan'])->first();
            if(empty($cek)) {
                $skema_gaji_detail = new SkemaGajiDetail;
                $skema_gaji_detail->setDynamicConnection();
                $skema_gaji_detail->fill($request->except('_token'));
                $skema_gaji_detail->created_at = date('Y-m-d H:i:s');
                $skema_gaji_detail->created_by = Auth::user()->id;
            } else {
                $skema_gaji_detail = SkemaGajiDetail::on($this->getConnectionName())->find($cek->id);
                $skema_gaji_detail->fill($request->except('_token'));
                $skema_gaji_detail->updated_at = date('Y-m-d H:i:s');
                $skema_gaji_detail->updated_by = Auth::user()->id;
            }

            $skema_gaji_detail->id_skema_gaji = $val['id_skema_gaji'];
            $skema_gaji_detail->id_jabatan = $val['id_jabatan'];
            $skema_gaji_detail->id_posisi = $val['id_posisi'];
            $skema_gaji_detail->id_status_karyawan = $val['id_status_karyawan'];
            $skema_gaji_detail->gaji_pokok = $val['gaji_pokok'];
            $skema_gaji_detail->persen_omset = $val['persen_omset'];
            $skema_gaji_detail->tunjangan_jabatan = $val['tunjangan_jabatan'];
            $skema_gaji_detail->tunjangan_ijin = $val['tunjangan_ijin'];
            $skema_gaji_detail->tunjangan_makan = $val['tunjangan_makan'];
            $skema_gaji_detail->tunjangan_transportasi = $val['tunjangan_transportasi'];
            $skema_gaji_detail->lembur = $val['lembur'];
            $skema_gaji_detail->pph = $val['pph'];
            $skema_gaji_detail->potongan_keterlambatan = $val['potongan_keterlambatan'];

            $skema_gaji_detail->save();
            $i++;
        }
        
        session()->flash('success', 'Sukses menyimpan data!');
        return redirect('skema_gaji/setting/'.$id);
    }
}
