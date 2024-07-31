<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterApotek;
use App\MasterGroupApotek;
use App\MasterApoteker;
use App\MasterObat;
use App\User;
use Auth;
use App;
use Datatables;
use DB;
use Excel;
use DateTimeInterface;

class M_ApotekController extends Controller
{
    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 22/02/
        =======================================================================================
    */
    public function index()
    {
        return view('apotek.index');
    }


    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 22/02/2020
        =======================================================================================
    */
    public function list_apotek(Request $request)
    {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        $super_admin = session('super_admin');
        DB::statement(DB::raw('set @rownum = 0'));
        $data = MasterApotek::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_m_apotek.*'])
        ->where(function($query) use($request, $super_admin){
            $query->where('tb_m_apotek.is_deleted','=','0');
            if($super_admin == 0) {
                $query->where('tb_m_apotek.id_group_apotek', Auth::user()->id_group_apotek);
            }
        });
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->orwhere('nama_singkat','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('nama_panjang','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('telepon','LIKE','%'.$request->get('search')['value'].'%');
            });
        })   
        ->editcolumn('nama_panjang', function($data){
            return '<span><b>('.$data->kode_apotek.') '.$data->nama_singkat.'</b></span></br><span>'.$data->nama_panjang.'</span>'; 
        }) 
        ->editcolumn('id_apoteker', function($data){
            return $data->apoteker->nama; 
        }) 
        ->editcolumn('id_group_apotek', function($data){
            return $data->group_apotek->nama_singkat; 
        })
        ->editcolumn('is_sync', function($data){

            if($data->is_sync == 1) {
                $var = '<span style="color:#0097A7;"><i class="fa fa-check-square-o"></i></span>';
            } else {
                $var = '<span style="color:#C2185B;"><i class="fa fa-window-close-o"></i></span>';
            }
            return $var; 
        })  
        ->editcolumn('sync_count', function($data){
            $jum_obat = MasterObat::count();
            $selisih = $jum_obat-$data->sync_count;
            return '<small>Total sync data : '.$data->sync_count.'</br>Last id obat : '.$data->sync_last_id.'</br>Selisih data : '.$selisih.'</small>'; 
        })  
         ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
                $btn .= '<span class="btn btn-primary" onClick="edit_data('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span>';
                $inisial = strtolower($data->nama_singkat);
                if (Schema::hasTable('tb_m_stok_harga_'.$inisial.'')) {
                    $btn .= ' <span class="btn btn-warning" onClick="sync_data_stok_harga('.$data->id.')" data-toggle="tooltip" data-placement="center" title="Sinkronasi Stok dan Harga Obat"><i class="fa fa-sync"></i></span>';
                } else {
                    $btn .= ' <span class="btn btn-info" onClick="add_table_stok_harga('.$data->id.')" data-toggle="tooltip" data-placement="center" title="Membuat Tabel Stok dan Harga"><i class="fa fa-plus"></i></span>';
                }
                
                $btn .= '<span class="btn btn-danger" onClick="delete_apotek('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['id_apoteker', 'id_group_apotek', 'is_sync', 'sync_count', 'nama_panjang', 'action'])
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
    public function export_data_apotek(Request $request)
    {
        $myFile = Excel::create('Data Apotek', function($excel) use ($request) {
            
            $excel->sheet('Sheet 1', function($sheet) use ($request) {

                $headings = array('No', 'Nama','Alamat', 'Telepon','Apoteker', 'Nostra','Nosia', 'Tanggal Berdiri');
                 $sheet->cell('A1:H1', function($cell) {
                    $cell->setFontWeight('bold');
                });

                $sheet->appendRow(1, $headings);

                $rekaps = MasterApotek::select([DB::raw('@rownum  := @rownum  + 1 AS no'),
                            'tb_m_apotek.id',
                            'tb_m_apotek.nama_singkat', 
                            'tb_m_apotek.nama_panjang', 
                            'tb_m_apotek.alamat', 
                            'tb_m_apotek.telepon', 
                            'a.nama as apoteker', 
                            'tb_m_apotek.nostra', 
                            'tb_m_apotek.nosia', 
                            'tb_m_apotek.tanggalberdiri'])
                ->join('tb_m_apoteker as a','a.id','=','tb_m_apotek.id')
                ->where('tb_m_apotek.is_deleted','=','0')
                ->where('tb_m_apotek.id','LIKE','%'.$request->id.'%')
                ->where('tb_m_apotek.nosia','LIKE','%'.$request->nosia.'%')
                ->where('a.id','LIKE','%'.$request->apoteker.'%')
                ->get();

                $no = 0;
                foreach($rekaps as $rekap) {
                    $no++;
                    $data[] = array(
                        $no,
                        $rekap->nama_singkat,
                        $rekap->alamat,
                        $rekap->telepon,
                        $rekap->apoteker,
                        $rekap->nostra,
                        $rekap->nosia, 
                        $rekap->tanggalberdiri,
                    );
                }
                
                $sheet->fromArray($data, null, 'A2', false, false);
            });
        });
        $myFile = $myFile->string('xlsx'); //change xlsx for the format you want, default is xls
        $response =  array(
           'name' => "Data Apotek", //no extention needed
           'file' => "data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64,".base64_encode($myFile) //mime type of used format
        );
        return response()->json($response);
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
    	$apotek = new MasterApotek;

        $group_apoteks      = MasterGroupApotek::where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $group_apoteks->prepend('-- Pilih Group Apotek --','');

        $apotekers      = User::where('is_deleted', 0)->pluck('nama', 'id');
        $apotekers->prepend('-- Pilih Apoteker --','');

        return view('apotek.create')->with(compact('apotek', 'group_apoteks', 'apotekers'));
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
        $apotek = new MasterApotek;
        $apotek->fill($request->except('_token'));

        $group_apoteks      = MasterGroupApotek::where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $group_apoteks->prepend('-- Pilih Group Apotek --','');

        $apotekers      = User::where('is_deleted', 0)->pluck('nama', 'id');
        $apotekers->prepend('-- Pilih Apoteker --','');

        $validator = $apotek->validate();
        if($validator->fails()){
            return view('apotek.create')->with(compact('apotek', 'group_apoteks', 'apotekers'))->withErrors($validator);
        }else{
            $apotek->save_plus();
            $inisial = strtolower($apotek->nama_singkat);
            if (Schema::hasTable('tb_m_stok_harga_'.$inisial.'')) {
            } else {
                \DB::statement('CREATE TABLE tb_m_stok_harga_'.$inisial.' LIKE sample_tb_m_stok_harga');
                \DB::statement('CREATE TABLE tb_histori_harga_'.$inisial.' LIKE sample_tb_histori_harga');
                \DB::statement('CREATE TABLE tb_histori_stok_'.$inisial.' LIKE sample_tb_histori_stok');
            }

            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('apotek');
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
        $apotek 		= MasterApotek::find($id);

        /*$group_apotek =  MasterGroupApotek::addSelect(['id_apotek' => MasterApotek::select('nama_singkat')
    ->whereColumn('id_group_apotek', 'tb_m_group_apotek.id')
    ->orderBy('id', 'desc')
])->get();

        $apotek = MasterApotek::select('tb_m_apotek.*')->addSelect(['id_group_apotek' => MasterGroupApotek::select('nama_singkat')
    ->whereColumn('id', 'tb_m_apotek.id_group_apotek')
    ->orderBy('id', 'desc')
])->get();

        print_r($apotek);
        exit();*/
        
        $group_apoteks      = MasterGroupApotek::where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $group_apoteks->prepend('-- Pilih Group Apotek --','');

        $apotekers      = User::where('is_deleted', 0)->pluck('nama', 'id');
        $apotekers->prepend('-- Pilih Apoteker --','');

        return view('apotek.edit')->with(compact('apotek', 'group_apoteks', 'apotekers'));
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
        $apotek = MasterApotek::find($id);
        $apotek->fill($request->except('_token'));

        $validator = $apotek->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            $apotek->save_edit();
            $inisial = strtolower($apotek->nama_singkat);
            if (Schema::hasTable('tb_m_stok_harga_'.$inisial.'')) {
            } else {
                \DB::statement('CREATE TABLE tb_m_stok_harga_'.$inisial.' LIKE sample_tb_m_stok_harga');
                \DB::statement('CREATE TABLE tb_histori_harga_'.$inisial.' LIKE sample_tb_histori_harga');
                \DB::statement('CREATE TABLE tb_histori_stok_'.$inisial.' LIKE sample_tb_histori_stok');
            }

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
        $apotek = MasterApotek::find($id);
        $apotek->is_deleted = 1;
        $apotek->is_sync = 0;
        $apotek->sync_at = null;
        $apotek->sync_by = null;
        $apotek->sync_last_id = 0;
        $apotek->sync_count = 0;

        $inisial = strtolower($apotek->nama_singkat);
        if($apotek->save()){
            Schema::drop('tb_m_stok_harga_'.$inisial.'');
            Schema::drop('tb_histori_harga_'.$inisial.'');
            Schema::drop('tb_histori_stok_'.$inisial.'');
            echo 1;
        } else {
            echo 0;
        }
    }


    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 22/02/2020
        =======================================================================================
    */
    public function list_apoteker(Request $request){
        $apotekers = User::select('nama', 'id')
                                    ->where('id', 'LIKE', '%'.$request->q.'%')
                                    ->orWhere('nama', 'LIKE', '%'.$request->q.'%')
                                    ->limit(30)
                                    ->get();

        $data = array();

        foreach ($apotekers as $apoteker) {
            $obj = array();
            $obj['id'] = $apoteker->id;
            $obj['nama'] = $apoteker->nama;
            $data[] = $obj;
        }
        echo json_encode(array(
                            'incomplete_results'=>false,
                            'items'=>$data,
                            'total_count'=>count($apotekers)
                        ));
    }

    public function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function sync_data_stok_harga(Request $request) {
        ini_set('memory_limit', '-1'); 

        $sync_by = Auth::id();
        $sync_at = date('Y-m-d H:i:s');
        $created_at = date('Y-m-d H:i:s');

        $apotek = MasterApotek::find($request->id);
        $obats = MasterObat::select([
                                'id as id_obat', 
                                'nama as nama', 
                                'barcode as barcode', 
                                DB::raw('0 as stok_awal'), 
                                DB::raw('0 as stok_akhir'), 
                                'harga_beli', 
                                'harga_jual', 
                                DB::raw('"'.$sync_at.'" AS sync_at'), 
                                DB::raw('"'.$sync_by.'" AS sync_by')
                            ])
                            ->where(function($query) use($apotek){
                                if($apotek->is_sync == 1) {
                                    $query->where('id','>',$apotek->sync_last_id);
                                }
                            })
                            ->limit(500)
                            ->get()
                            ->toArray();
    
        if(count($obats) > 0) {
            $inisial = strtolower($apotek->nama_singkat);
            DB::table('tb_m_stok_harga_'.$inisial.'')->insert($obats);

            $data_terupdate = DB::table('tb_m_stok_harga_'.$inisial.'')->select([DB::raw('COUNT(*) as jum_terupdate'), DB::raw('MAX(id_obat) as sync_last_id')])->first();

            if(!empty($data_terupdate) && $data_terupdate->jum_terupdate > 0) {
                $apotek->is_sync = 1;
                $apotek->sync_by = $sync_by;
                $apotek->sync_at = $sync_at;
                $apotek->sync_last_id = $data_terupdate->sync_last_id;
                $apotek->sync_count = $data_terupdate->jum_terupdate;
                $apotek->save();

                echo 1;
            } else {
                echo 0;
            }
        } else {
            echo 2;
        }
    }

    public function add_table_stok_harga(Request $request) {
        $apotek = MasterApotek::find($request->id);
        $inisial = strtolower($apotek->nama_singkat);
        if (Schema::hasTable('tb_m_stok_harga_'.$inisial.'')) {
            echo 0;
        } else {
            \DB::statement('CREATE TABLE tb_m_stok_harga_'.$inisial.' LIKE sample_tb_m_stok_harga');
            \DB::statement('CREATE TABLE tb_histori_harga_'.$inisial.' LIKE sample_tb_histori_harga');
            \DB::statement('CREATE TABLE tb_histori_stok_'.$inisial.' LIKE sample_tb_histori_stok');
            echo 1;
        }
    }
}
