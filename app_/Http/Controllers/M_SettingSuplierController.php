<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterObat;
use App\MasterGolonganObat;
use App\MasterPenandaanObat;
use App\MasterProdusen;
use App\MasterSuplier;
use App\MasterSatuan;
use App\MasterApotek;
use App\HistoriHarga;
use App\TransaksiPembelian;
use App\TransaksiPembelianDetail;
use App\MasterSettingSuplier;
use App;
use Datatables;
use DB;
use Excel;
use Auth;

class M_SettingSuplierController extends Controller
{
    /*
        =======================================================================================
        For     : 
        Author  : 
        Date    : 09/02/2023
        =======================================================================================
    */
    public function index()
    {
        $golongan_obats = MasterGolonganObat::where('is_deleted', 0)->pluck('keterangan', 'id');
        $golongan_obats->prepend('-- Pilih Golongan Obat --','');

        $penandaan_obats = MasterPenandaanObat::where('is_deleted', 0)->pluck('nama', 'id');
        $penandaan_obats->prepend('-- Pilih Penandaan Obat --','');

        $produsens = MasterProdusen::where('is_deleted', 0)->pluck('nama', 'id');
        $produsens->prepend('-- Pilih Produsen --','');

        return view('setting_suplier.index')->with(compact('golongan_obats', 'penandaan_obats', 'produsens'));
    }

        /*
        =======================================================================================
        For     : 
        Author  : 
        Date    : 09/02/2023
        =======================================================================================
    */
    public function listSettingSuplier(Request $request)
    {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::statement(DB::raw('set @rownum = 0'));
        $data = MasterObat::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_m_obat.*'])
        ->where(function($query) use($request){
            $query->where('tb_m_obat.is_deleted','=','0');
            $query->where('id_penandaan_obat','LIKE',($request->id_penandaan_obat > 0 ? $request->id_penandaan_obat : '%'.$request->id_penandaan_obat.'%'));
            $query->where('id_golongan_obat','LIKE',($request->id_golongan_obat > 0 ? $request->id_golongan_obat : '%'.$request->id_golongan_obat.'%'));
            $query->where('id_produsen','LIKE',($request->id_produsen > 0 ? $request->id_produsen : '%'.$request->id_produsen.'%'));
        });
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->orwhere('nama','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('barcode','LIKE','%'.$request->get('search')['value'].'%');
            });
        })   
        ->editcolumn('isi_tab', function($data){
            return $data->isi_tab.'/'.$data->isi_strip; 
        }) 
        ->editcolumn('id_produsen', function($data){
            $str_ = '<span class="text-danger"><small>- produsen belum dipilih -</small></span>';
            if(!is_null($data->id_produsen) AND $data->id_produsen != '') {
                $str_ = $data->produsen->nama;
            }
            return $str_; 
        }) 
        ->editcolumn('id_penandaan_obat', function($data){
            $str_ = '<span class="text-danger"><small>- penandaan obat belum dipilih -</small></span>';
            if(!is_null($data->id_penandaan_obat) AND $data->id_penandaan_obat != '') {
                $str_ = $data->penandaan_obat->nama;
            }
            return $str_; 
        }) 
        ->editcolumn('id_golongan_obat', function($data){
            $str_ = '<span class="text-danger"><small>- golongan obat belum dipilih -</small></span>';
            if(!is_null($data->id_golongan_obat) AND $data->id_golongan_obat != '') {
                if(isset($data->golongan_obat)) {
                    $str_ = $data->golongan_obat->keterangan;
                }
            }
            return $str_; 
        }) 
        ->editcolumn('setting', function($data){
            $str = '';
            $settings = MasterSettingSuplier::where('id_obat', $data->id)->where('is_deleted', 0)->get();

            if(count($settings) > 0) {
                $str .= '<br>';

                $str .= '<table style="width:100%;">
                            <tbody>';

                            $i = 0;
                            foreach ($settings as $key => $val) {
                                $i++;

                                $btn = '<div class="btn-group">';
                                $btn .= '<span class="btn btn-secondary btn-sm" onClick="edit_detail('.$val->id.')" data-toggle="tooltip" data-placement="top" title="Edit Data Detail"><i class="fa fa-fw fa-edit"></i></span>';
                                $btn .= '<span class="btn btn-danger btn-sm" onClick="delete_detail('.$val->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data Detail"><i class="fa fa-fw fa-trash"></i></span>';
                                $btn .='</div>';

                                $str .= '<tr>
                                            <td style="width:90%;">
                                                <b>'.$i.'. </b> : '.$val->suplier->nama.' <br> &nbsp;&nbsp;&nbsp; <b>Prioriti </b>: '.$val->level.'
                                            </td>
                                            <td style="width:10%;" class="text-center">
                                                '.$btn.'
                                            </td>
                                        </tr>';
                            }
                $str .=     '</tbody>
                        </table>';
            } else {
                $str = '<span class="label label-danger" data-toggle="tooltip" data-placement="top" title="Suplier belum disetting" style="font-size:8pt;color:#e91e63;">Belum disetting</span>';
            }

            return $str;
        }) 
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .= '<span class="btn btn-info btn-sm" onClick="add_detail('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Setting Suplier"> [setting]</span>';
            //$btn .= '<a href="'.url('/obat/'.$data->id.'/edit').'" title="Edit Data" class="btn btn-primary"><span data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span></a>';
            return $btn;
        })    
        ->rawColumns(['id_produsen', 'id_golongan_obat', 'isi_tab', 'stok', 'action', 'id_produsen', 'id_penandaan_obat', 'id_golongan_obat', 'setting'])
        ->addIndexColumn()
        ->make(true);  
    }

    
    /*
        =======================================================================================
        For     : 
        Author  : 
        Date    : 09/02/2023
        =======================================================================================
    */
    public function create()
    {
        $data_ = new MasterSettingSuplier;

        $supliers = MasterSuplier::pluck('nama', 'id');
        $supliers->prepend('-- Pilih Suplier --','');

        return view('setting_suplier.create')->with(compact('data_'));
    }

    /*
        =======================================================================================
        For     : 
        Author  : Adistya
        Date    : 09/02/2023
        =======================================================================================
    */
    public function store(Request $request)
    {
        $data_ = new MasterSettingSuplier;
        $data_->fill($request->except('_token'));

        $validator = $data_->validate();
        if($validator->fails()){
            return view('setting_suplier.create')->with(compact('data_'))->withErrors($validator);
        }else{
            $data_->created_at = date('Y-m-d H:i:s');
            $data_->created_by = Auth::user()->id;
            $data_->save();
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('setting_suplier');
        }
    }

    /*
        =======================================================================================
        For     : 
        Author  : 
        Date    : 09/02/2023
        =======================================================================================
    */
    public function show($id)
    {
        //
    }

    /*
        =======================================================================================
        For     : 
        Author  : 
        Date    : 09/02/2023
        =======================================================================================
    */
    public function edit($id)
    {
        $data_      = MasterSettingSuplier::find($id);

        return view('setting_suplier.edit')->with(compact('data_'));
    }

    /*
        =======================================================================================
        For     : 
        Author  : 
        Date    : 09/02/2023
        =======================================================================================
    */
    public function update(Request $request, $id)
    {
        $data_ = MasterSettingSuplier::find($id);
        $data_->fill($request->except('_token'));

        $validator = $data_->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            $data_->updated_at = date('Y-m-d H:i:s');
            $data_->updated_by = Auth::user()->id;
            $data_->save();
            echo json_encode(array('status' => 1));
        }
    }

    /*
        =======================================================================================
        For     : 
        Author  : 
        Date    : 09/02/2023
        =======================================================================================
    */
    public function destroy($id)
    {
        $data_ = MasterSettingSuplier::find($id);
        $data_->is_deleted = 1;
        $data_->deleted_at = date('Y-m-d H:i:s');
        $data_->deleted_by = Auth::user()->id;
        if($data_->save()){
            echo 1;
        }else{
            echo 0;
        }
    }

    public function addDetail($id) {
        $obat = MasterObat::find($id);
        $data_ = new MasterSettingSuplier;

        $supliers = MasterSuplier::where('is_deleted', 0)->pluck('nama', 'id');
        $supliers->prepend('-- Pilih Suplier --','');

        return view('setting_suplier.add_detail')->with(compact('data_', 'obat', 'supliers'));
    }

    public function storeDetail(Request $request) {
        $data_ = new MasterSettingSuplier;
        $data_->fill($request->except('_token'));
        $data_->status = 1;

        $obat = MasterObat::find($request->id_obat);
        $validator = $data_->validate();
        if($validator->fails()){
            echo 0;
        }else{
            $data_->created_at = date('Y-m-d H:i:s');
            $data_->created_by = Auth::user()->id;
            $data_->save();
            echo 1;
        }
    }

    public function editDetail($id) {
        $data_ = MasterSettingSuplier::find($id);
        $obat = MasterObat::find($data_->id_obat);

        $supliers = MasterSuplier::where('is_deleted', 0)->pluck('nama', 'id');
        $supliers->prepend('-- Pilih Suplier --','');

        return view('setting_suplier.edit_detail')->with(compact('data_', 'obat', 'supliers'));
    }

    public function updateDetail(Request $request, $id)
    {
        $data_ = MasterSettingSuplier::find($id);
        $data_->fill($request->except('_token'));

        $obat = MasterObat::find($data_->id_obat);
        $validator = $data_->validate();

        if($validator->fails()){
            echo 0;
        }else{
            $data_->updated_at = date('Y-m-d H:i:s');
            $data_->updated_by = Auth::user()->id;
            $data_->save();
            echo 1;
        }
    }

    public function deleteDetail($id)
    {
        $data_ = MasterSettingSuplier::find($id);
        $data_->is_deleted = 1;
        $data_->deleted_at = date('Y-m-d H:i:s');
        $data_->deleted_by = Auth::user()->id;
        if($data_->save()){
            echo 1;
        }else{
            echo 0;
        }
    }

    public function getReloadData() {
        $obats = MasterObat::where('is_deleted', 0)->where('reload_suplier', 0)->limit(100)->get();
        $i = 0;
        $id_obat = 0;
        foreach($obats as $obj) {
            $sub = TransaksiPembelianDetail::select(['tb_detail_nota_pembelian.id_obat', DB::raw('MAX(a.tgl_nota) as tgl'), 'a.id_suplier'])
                            ->join('tb_nota_pembelian as a', 'a.id', 'tb_detail_nota_pembelian.id_nota')
                            ->where('a.is_deleted', 0)
                            ->where('tb_detail_nota_pembelian.is_deleted', 0)
                            ->where('id_obat', $obj->id)
                            ->groupBy('a.id_suplier');

            $pembelians = DB::table( DB::raw("({$sub->toSql()}) as sub") )
                            ->mergeBindings($sub->getQuery())
                            ->orderBy('tgl', 'DESC')->get();


            if(count($pembelians) > 0) {
                foreach($pembelians as $val){
                    $data_ = MasterSettingSuplier::where('is_deleted', 0)
                                ->where('id_obat', $obj->id)
                                ->where('id_suplier', $val->id_suplier)
                                ->first();
                    $jum = MasterSettingSuplier::where('is_deleted', 0)
                                ->where('id_obat', $obj->id)
                                ->count();
                    $jumx = $jum+1;
                    $status = 0;
                    if($jumx == 1) {
                        $status = 1;
                    }

                    if(empty($data_)) {
                        $data_ = new MasterSettingSuplier;
                        $data_->level = $jumx;
                    } 

                    $data_->id_obat = $obj->id;
                    $data_->id_suplier = $val->id_suplier;
                    $data_->status = $status;
                    $data_->created_at = date('Y-m-d H:i:s');
                    $data_->created_by = Auth::user()->id;
                    $data_->save();

                    $id_obat = $obj->id;
                }
            }

            MasterObat::where('id', $obj->id)->update(['reload_suplier' => 1]);

            $i++;
        }

        echo "Anda berhasil reload data sebanyak ".$i." data obat, last id obat ".$id_obat;
    }
}
