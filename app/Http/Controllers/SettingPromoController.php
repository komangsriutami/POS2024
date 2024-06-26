<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\SettingPromo;
use App\SettingPromoDetail;
use App\MasterJenisPromo;
use App\MasterApotek;
use App\MasterObat;
use App\MasterMemberTipe;
use App\SettingPromoItemBeli;
use App\SettingPromoItemDiskon;
use Illuminate\Support\Carbon;
use App;
use Datatables;
use DB;
use Auth;

class SettingPromoController extends Controller
{
    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 21/06/2020
        =======================================================================================
    */
    public function index()
    {
        return view('setting_promo.index');
    }


    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 21/06/2020
        =======================================================================================
    */
    public function list_setting_promo(Request $request)
    {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::statement(DB::raw('set @rownum = 0'));
        $data = SettingPromo::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_setting_promo.*'])
        ->where(function($query) use($request){
            $query->where('tb_setting_promo.is_deleted','=','0');
        });
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request, $order_column, $order_dir){
            $query->where(function($query) use($request){
                //$query->orwhere('telepon','LIKE','%'.$request->get('search')['value'].'%');
            });
        }) 
        ->addcolumn('tgl_awal', function ($data) {
            $tgl_awal = Carbon::parse($data->tgl_awal)->format('d/m/Y');
            $tgl_akhir = Carbon::parse($data->tgl_akhir)->format('d/m/Y');
            
            return $tgl_awal.' - '.$tgl_akhir;
        })
        ->addcolumn('detail', function ($data) {
        	$btn = '';
            
            return $btn;
        })
        ->addcolumn('action', function ($data) {
        	$btn = '<div class="btn-group">';
            $btn .= '<a href="'.url('/setting_promo/'.$data->id.'/edit').'" title="Edit Data" class="btn btn-info"><span data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span></a>';
            $btn .= '<span class="btn btn-danger" onClick="delete_setting_diskon('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
            $btn .='</div>';
            return $btn;
        })
        ->rawColumns(['action'])
        ->make(true);  
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 21/06/2020
        =======================================================================================
    */
    public function create()
    {
    	$setting_promo = new SettingPromo;

        $apoteks = MasterApotek::where('is_deleted', 0)->pluck('nama_panjang', 'id');

        $jenis_promos = DB::table('tb_m_jenis_promo')->where('is_deleted', 0)->pluck('nama', 'id');
        $jenis_promos->prepend('-- Pilih Jenis Promo --','');

        $tipe_members = MasterMemberTipe::where('is_deleted', 0)->pluck('nama', 'id');
        $tipe_members->prepend('-- Pilih Tipe Member --','');
        
        $obats = MasterObat::where('is_deleted', 0)->limit(50)->pluck('nama', 'id');
        $obats->prepend('-- Pilih Obat --','');

        // ini dihapus ketika ada perbaikan setting promo
        $setting_promo_item_beli = new SettingPromoItemBeli;

        return view('setting_promo.create')->with(compact('setting_promo', 'apoteks', 'jenis_promos', 'obats', 'tipe_members', 'setting_promo_item_beli'));
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
        $setting_promo = new SettingPromo;
        $setting_promo->fill($request->except('_token'));

        $apoteks = MasterApotek::where('is_deleted', 0)->pluck('nama_panjang', 'id');

        $jenis_promos = DB::table('tb_m_jenis_promo')->pluck('nama', 'id');
        $jenis_promos->prepend('-- Pilih Jenis Promo --','');

        $tipe_members = MasterMemberTipe::where('is_deleted', 0)->pluck('nama', 'id');
        $tipe_members->prepend('-- Pilih Tipe Member --','');
        
        $obats = MasterObat::where('is_deleted', 0)->limit(50)->pluck('nama', 'id');
        $obats->prepend('-- Pilih Obat --','');

        $id_apotek = $request->id_apotek;
        $setting_promo_item_beli = new SettingPromoItemBeli;
        $setting_promo_item_beli->fill($request->except('_token'));
    

        if($setting_promo->id_jenis_promo == 1) {
            $validator = $setting_promo->validate_persen();
        } else if($setting_promo->id_jenis_promo == 2){
            $validator = $setting_promo->validate_rp();
        } else {
            $validator = $setting_promo->validate_item();
        }

        if($validator->fails()){
            return view('setting_promo.create')->with(compact('setting_promo', 'apoteks', 'jenis_promos', 'obats', 'tipe_members'))->withErrors($validator);
        }else{

            //if($setting_promo->id_jenis_promo == 1) {
                # diskon berdasarkan %




                $setting_promo->created_by = Auth::user()->id;
                $setting_promo->created_at = date('Y-m-d');
                $setting_promo->save();

                foreach ($id_apotek as $key => $val) {
                    $setting_promo_det = new SettingPromoDetail;
                    $setting_promo_det->id_setting_promo = $setting_promo->id;
                    $setting_promo_det->id_apotek = $val;
                    $setting_promo_det->save();
                }

                $setting_promo_item_beli->id_setting_promo = $setting_promo->id;
                $validator_item_beli = $setting_promo_item_beli->validate();

                if($validator->fails()){
                    return view('setting_promo.create')->with(compact('setting_promo', 'apoteks', 'jenis_promos', 'obats', 'tipe_members'))->withErrors($validator);
                }else{
                    $setting_promo_item_beli->save();

                    session()->flash('success', 'Sukses menyimpan data!');
                    return redirect('setting_promo');
                }

                /*$array_id_obat = array();
                foreach ($item_diskons as $item_diskon) {
                    if(!in_array($item_diskon['id_obat'], $array_id_obat)){
                        if($item_diskon['id']>0){
                            $obj = SettingPromoItemBeli::find($item_diskon['id']);
                        }else{
                            $obj = new SettingPromoItemBeli;
                        }

                        $obj->id_setting_promo = $setting_promo->id;
                        $obj->id_obat = $item_diskon['id_obat'];
                        $obj->jumlah = $item_diskon['jumlah'];
                        $obj->created_by = Auth::user()->id;
                        $obj->created_at = date('Y-m-d H:i:s');
                        $obj->updated_at = date('Y-m-d H:i:s');
                        $obj->updated_by = '';
                        $obj->is_deleted = 0;

                        $array_id_obat[] = $obj->id;
                    }
                }

                if(!empty($array_id_obat)){
                    DB::statement("DELETE FROM tb_setting_promo_item_beli
                                    WHERE id_setting_promo=".$this->id." AND 
                                            id NOT IN(".implode(',', $array_id_obat).")");
                }else{
                    DB::statement("DELETE FROM tb_setting_promo_item_beli 
                                    WHERE id_nota=".$this->id);
                }*/

            /*} else if($setting_promo->id_jenis_promo == 2){
                # diskon berdasarkan rp
            } else {
                # diskon berdasarkan item
            }
            $setting_promo->save_plus();*/

            
        }
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 21/06/2020
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
        Date    : 21/06/2020
        =======================================================================================
    */
    public function edit($id)
    {
        $setting_promo = SettingPromo::find($id);
        
        $apoteks = MasterApotek::where('is_deleted', 0)->pluck('nama_panjang', 'id');

        $jenis_promos = DB::table('tb_m_jenis_promo')->pluck('nama', 'id');
        $jenis_promos->prepend('-- Pilih Jenis Promo --','');

        $tipe_members = MasterMemberTipe::where('is_deleted', 0)->pluck('nama', 'id');
        $tipe_members->prepend('-- Pilih Tipe Member --','');

        $obats = MasterObat::where('is_deleted', 0)->limit(50)->pluck('nama', 'id');
        $obats->prepend('-- Pilih Obat --','');

        // ini dihapus ketika ada perbaikan setting promo
        $setting_promo_item_beli = SettingPromoItemBeli::where('id_setting_promo', $setting_promo->id)->first();
        $obat = MasterObat::find($setting_promo_item_beli->id_obat);
        $setting_promo_item_beli->barcode = $obat->barcode;
        $setting_promo_item_beli->nama_obat = $obat->nama;

        return view('setting_promo.edit')->with(compact('setting_promo', 'apoteks', 'jenis_promos', 'obats', 'tipe_members', 'setting_promo_item_beli'));
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 21/06/2020
        =======================================================================================
    */
    public function update(Request $request, $id)
    {
        $setting_promo = SettingPromo::find($id);
        $setting_promo->fill($request->except('_token'));

        $apoteks = MasterApotek::where('is_deleted', 0)->pluck('nama_panjang', 'id');

        $jenis_promos = DB::table('tb_m_jenis_promo')->pluck('nama', 'id');
        $jenis_promos->prepend('-- Pilih Jenis Promo --','');

        $tipe_members = MasterMemberTipe::where('is_deleted', 0)->pluck('nama', 'id');
        $tipe_members->prepend('-- Pilih Tipe Member --','');
        
        $obats = MasterObat::where('is_deleted', 0)->limit(50)->pluck('nama', 'id');
        $obats->prepend('-- Pilih Obat --','');

        $id_apotek = $request->id_apotek;
        $setting_promo_item_beli = SettingPromoItemBeli::where('id_setting_promo', $setting_promo->id)->first();
        $setting_promo_item_beli->fill($request->except('_token'));
    

        if($setting_promo->id_jenis_promo == 1) {
            $validator = $setting_promo->validate_persen();
        } else if($setting_promo->id_jenis_promo == 2){
            $validator = $setting_promo->validate_rp();
        } else {
            $validator = $setting_promo->validate_item();
        }

        if($validator->fails()){
            return view('setting_promo.create')->with(compact('setting_promo', 'apoteks', 'jenis_promos', 'obats', 'tipe_members'))->withErrors($validator);
        }else{

            //if($setting_promo->id_jenis_promo == 1) {
                # diskon berdasarkan %
                $setting_promo->created_by = Auth::user()->id;
                $setting_promo->created_at = date('Y-m-d');
                $setting_promo->save();


                /*foreach ($id_apotek as $key => $val) {
                    $setting_promo_det = SettingPromoDetail::where();
                    $setting_promo_det->id_setting_promo = $setting_promo->id;
                    $setting_promo_det->id_apotek = $val;
                    $setting_promo_det->save();
                }*/

                $setting_promo_item_beli->id_setting_promo = $setting_promo->id;
                $validator_item_beli = $setting_promo_item_beli->validate();

                if($validator->fails()){
                    return view('setting_promo.edit')->with(compact('setting_promo', 'apoteks', 'jenis_promos', 'obats', 'tipe_members'))->withErrors($validator);
                }else{
                    $setting_promo_item_beli->save();

                    session()->flash('success', 'Sukses menyimpan data!');
                    return redirect('setting_promo');
                }

                /*$array_id_obat = array();
                foreach ($item_diskons as $item_diskon) {
                    if(!in_array($item_diskon['id_obat'], $array_id_obat)){
                        if($item_diskon['id']>0){
                            $obj = SettingPromoItemBeli::find($item_diskon['id']);
                        }else{
                            $obj = new SettingPromoItemBeli;
                        }

                        $obj->id_setting_promo = $setting_promo->id;
                        $obj->id_obat = $item_diskon['id_obat'];
                        $obj->jumlah = $item_diskon['jumlah'];
                        $obj->created_by = Auth::user()->id;
                        $obj->created_at = date('Y-m-d H:i:s');
                        $obj->updated_at = date('Y-m-d H:i:s');
                        $obj->updated_by = '';
                        $obj->is_deleted = 0;

                        $array_id_obat[] = $obj->id;
                    }
                }

                if(!empty($array_id_obat)){
                    DB::statement("DELETE FROM tb_setting_promo_item_beli
                                    WHERE id_setting_promo=".$this->id." AND 
                                            id NOT IN(".implode(',', $array_id_obat).")");
                }else{
                    DB::statement("DELETE FROM tb_setting_promo_item_beli 
                                    WHERE id_nota=".$this->id);
                }*/

            /*} else if($setting_promo->id_jenis_promo == 2){
                # diskon berdasarkan rp
            } else {
                # diskon berdasarkan item
            }
            $setting_promo->save_plus();*/

            
        }
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 21/06/2020
        =======================================================================================
    */
    public function destroy($id)
    {
        $setting_promo = SettingPromo::find($id);
        $setting_promo->is_deleted = 1;
        $setting_promo->deleted_at = date('Y-m-d H:i:s');;
        $setting_promo->deleted_by = Auth::user()->id;

        if($setting_promo->save()){
            echo 1;
        } else {
            echo 0;
        }
    }

    public function add_row_item_beli(Request $request)
    {
        $count = $request->count;
        $nomer = $count+1;
        $setting_promo = new SettingPromo;
        $item_beli = new SettingPromoItemBeli;
        return view('setting_promo._form_item_beli')->with(compact('nomer','setting_promo', 'item_beli'));
    }

    public function add_row_item_diskon(Request $request)
    {
        $counter = $request->counter;
        $nomer = $counter+1;
        $setting_promo = new SettingPromo;
        $item_diskon = new SettingPromoItemDiskon;
        return view('setting_promo._form_item_diskon')->with(compact('nomer','setting_promo', 'item_diskon'));
    }

    public function open_data_obat(Request $request) {
        $barcode = $request->barcode;
        return view('setting_promo._dialog_open_obat')->with(compact('barcode'));
    }

    public function list_data_obat(Request $request)
    {
        $barcode = $request->barcode;
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);

        DB::statement(DB::raw('set @rownum = 0'));
        $data = DB::table('tb_m_stok_harga_'.$inisial.' as a')
        ->select([
                DB::raw('@rownum  := @rownum  + 1 AS no'),
                'a.*',
                'b.nama',
                'b.barcode',
        ])
        ->join('tb_m_obat as b', 'b.id', '=', 'a.id_obat')
        ->where(function($query) use($request){
            $query->where('b.is_deleted','=','0');
        });
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request, $barcode){
            $query->where(function($query) use($request, $barcode){
                $query->orwhere('b.nama','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('b.barcode','LIKE','%'.$request->get('search')['value'].'%');
            });
        })   
        ->editcolumn('stok_akhir', function($data){
            return $data->stok_akhir; 
        }) 
        ->editcolumn('harga_beli', function($data){
            return 'Rp '.number_format($data->harga_beli, 2, '.', ','); 
        }) 
        ->editcolumn('harga_jual', function($data){
            return 'Rp '.number_format($data->harga_jual, 2, '.', ','); 
        }) 
        ->addcolumn('action', function($data){
            $btn = '<div class="btn-group">';
            $btn .= '<span class="btn btn-primary" onClick="add_item_dialog('.$data->id_obat.', '.$data->harga_jual.', '.$data->harga_beli.', '.$data->stok_akhir.', '.$data->harga_beli_ppn.')" data-toggle="tooltip" data-placement="top" title="Tambah Item"><i class="fa fa-plus"></i></span>';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['stok_akhir', 'action'])
        ->addIndexColumn()
        ->make(true);  
    }
}
