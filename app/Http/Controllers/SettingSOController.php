<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\SettingStokOpnam;
use App\MasterApotek;
use App\MasterObat;
use App\HistoriStok;
use App;
use Datatables;
use DB;
use Excel;
use Auth;
use PDO;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SettingSOController extends Controller
{
    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function index()
    {
       /* if(Auth::user()->id!=1) {
            echo "under maintenance"; exit();
        } else {*/
            return view('setting_so.index');
      //  }
    }


    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function list_setting_so(Request $request)
    {
    	$order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::statement(DB::raw('set @rownum = 0'));
        $data = SettingStokOpnam::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_setting_stok_opnam.*'])
        ->where(function($query) use($request){
            $query->where('tb_setting_stok_opnam.is_deleted','=','0');
        })->orderBy('id', 'ASC');
        
        $datatables = Datatables::of($data);
        return $datatables  
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->orwhere('tgl_so','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->editcolumn('id_apotek', function($data) use($request){
            return $data->apotek->nama_panjang;
        })
        ->editcolumn('step', function($data) use($request){
            $now = $data->step+1;

            $last_so = SettingStokOpnam::where('id_apotek', $data->id_apotek)->where('step', '>', 1)->orderBy('id', 'DESC')->first();

            $btn = '';
            if($now == 1) {
                $btn .= '<span class="btn btn-info btn-sm" onClick="step_satu('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Step Satu"><i class="fa fa-reload"></i> Step 1 : Set Awal</span>';
                $btn .= '<span class="btn btn-default btn-sm" onClick="alert_check()" data-toggle="tooltip" data-placement="top" title="Step Dua"><i class="fa fa-reload"></i> Step 2 : Set Akhir</span>';
                $btn .= '<span class="btn btn-default btn-sm" onClick="alert_check()" data-toggle="tooltip" data-placement="top" title="Step Tiga"><i class="fa fa-reload"></i> Step 3 : Download Data</span>';
            } else if($now == 2) {
                $btn .= '<span class="btn btn-default btn-sm" onClick="alert_check()" data-toggle="tooltip" data-placement="top" title="Step Satu"><i class="fa fa-reload"></i> Step 1 : Set Awal</span>';
                $btn .= '<span class="btn btn-info btn-sm" onClick="step_dua('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Step Dua"><i class="fa fa-reload"></i> Step 2 : Set Akhir</span>';
                $btn .= '<span class="btn btn-default btn-sm" onClick="alert_check()" data-toggle="tooltip" data-placement="top" title="Step Tiga"><i class="fa fa-reload"></i> Step 3 : Download Data</span>';
            } else if($now == 3) {
                $btn .= '<span class="btn btn-default btn-sm" onClick="alert_check()" data-toggle="tooltip" data-placement="top" title="Step Satu"><i class="fa fa-reload"></i> Step 1 : Set Awal</span>';
                $btn .= '<span class="btn btn-default btn-sm" onClick="alert_check()" data-toggle="tooltip" data-placement="top" title="Step Dua"><i class="fa fa-reload"></i> Step 2 : Set Akhir</span>';
                $btn .= '<span class="btn btn-info btn-sm" onClick="download_akhir('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Step Tiga"><i class="fa fa-reload"></i> Step 3 : Download Data</span>';

                if($last_so->id == $data->id) {
                    if($data->is_backup == 0) {
                        $btn .= '<span class="btn btn-warning btn-sm" onClick="backup_data_so('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Backup Data SO"><i class="fa fa-server"></i>Backup Data</span>';
                    } else {
                        $btn .= '<span class="btn btn-default btn-sm" onClick="alert_backup()" data-toggle="tooltip" data-placement="top" title="Backup Data SO"><i class="fa fa-server"></i> Backup Data</span>';
                    }
                } else {
                    if($data->is_backup == 0) {
                    } else {
                        $btn .= '<span class="btn btn-default btn-sm" onClick="alert_backup()" data-toggle="tooltip" data-placement="top" title="Backup Data SO"><i class="fa fa-server"></i> Backup Data</span>';
                    }
                }
            }
            
            return $btn;
        })
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .= '<span class="btn btn-primary btn-sm" onClick="edit_data('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span>';
            $btn .= '<span class="btn btn-danger btn-sm" onClick="delete_setting_so('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['action', 'step'])
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
        $setting_so = new SettingStokOpnam;
        $apoteks      = MasterApotek::where('is_deleted', 0)->pluck('nama_panjang', 'id');
        $apoteks->prepend('-- Pilih Apotek --','');

        return view('setting_so.create')->with(compact('setting_so', 'apoteks'));
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
        $setting_so = new SettingStokOpnam;
        $setting_so->fill($request->except('_token'));

        $validator = $setting_so->validate();
        if($validator->fails()){
            $apoteks      = MasterApotek::where('is_deleted', 0)->pluck('nama_panjang', 'id');
            $apoteks->prepend('-- Pilih Apotek --','');

            return view('setting_so.create')->with(compact('setting_so', 'apoteks'))->withErrors($validator);
        }else{
            $setting_so->created_by = Auth::user()->id;
            $setting_so->created_at = date('Y-m-d H:i:s');
            $setting_so->save();
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('setting_so');
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
        $setting_so = SettingStokOpnam::find($id);
        $apoteks      = MasterApotek::where('is_deleted', 0)->pluck('nama_panjang', 'id');
        $apoteks->prepend('-- Pilih Apotek --','');

        return view('setting_so.edit')->with(compact('setting_so', 'apoteks'));
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
        $setting_so = SettingStokOpnam::find($id);
        $setting_so->fill($request->except('_token'));

        $validator = $setting_so->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            $setting_so->updated_by = Auth::user()->id;
            $setting_so->updated_at = date('Y-m-d H:i:s');
            $setting_so->save();
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
        $setting_so = SettingStokOpnam::find($id);
        $setting_so->is_deleted = 1;
        $setting_so->deleted_by = Auth::user()->id;
        $setting_so->deleted_at = date('Y-m-d H:i:s');

        if($setting_so->save()){
            echo 1;
        }else{
            echo 0;
        }
    }

    public function reload_data_awal(Request $request) {
        ini_set('memory_limit', '-1'); 
        ini_set('max_execution_time', '0');

        if(Auth::user()->id == 1) {
            DB::beginTransaction(); 
            try{
                $setting_so = SettingStokOpnam::find($request->id);
                $apotek = MasterApotek::find($setting_so->id_apotek);
                $inisial = strtolower($apotek->nama_singkat);
                # set awal
                //DB::table('tb_m_stok_harga_'.$inisial)->update(['stok_awal_so'=> DB::raw('stok_akhir'), 'stok_akhir_so'=> 0, 'selisih' => 0]);
                $data = DB::table('tb_m_stok_harga_'.$inisial)->select(['id', 'id_obat', 'stok_akhir'])->get();
                //dd($data);exit();
                
                foreach ($data as $key => $obj) {
                    if($obj->id_obat != null) {
                        $obat = MasterObat::find($obj->id_obat);
                        $array_id_histori_stok_awal = array();
                        $historis = DB::table('tb_histori_stok_'.$inisial)
                                ->where('id_obat', $obj->id_obat)
                                ->whereIn('id_jenis_transaksi', [2,3,11,9])
                                ->where('sisa_stok', '>', 0)
                                ->orderBy('id', 'ASC')
                                ->get();

                        foreach ($historis as $key => $val) {
                            $array_id_histori_stok_awal[] = array('id_histori_stok' => $val->id, 'jumlah' => $val->sisa_stok, 'hb_ppn' => $val->hb_ppn, 'hb_ppn_avg' => $val->hb_ppn_avg);
                        }

                        DB::table('tb_m_stok_harga_'.$inisial)->where('id', $obj->id)->update(['nama' => $obat->nama, 'barcode' => $obat->barcode, 'stok_awal_so'=> $obj->stok_akhir, 'stok_akhir_so'=> 0, 'selisih' => 0, 'total_penjualan_so' => 0, 'id_so' => $setting_so->id, 'is_so' => 0, 'so_at' => null, 'so_by' => null, 'so_by_nama' => null, 'id_histori_stok_awal' => json_encode($array_id_histori_stok_awal)]); //, 'id_histori_stok_awal' => json_encode($array_id_histori_stok_awal
                    }
                }
                
                # update step
                $setting_so->step = $setting_so->step+1;
                $setting_so->updated_by = Auth::user()->id;
                $setting_so->updated_at = date('Y-m-d H:i:s');
                if($setting_so->save()){
                    DB::commit();
                    echo 1;
                }else{
                    DB::rollback();
                    echo 0;
                }
            }catch(\Exception $e){
                DB::rollback();
                session()->flash('error', 'Error!');
                return redirect('setting_so');
            }
        } else {
            DB::beginTransaction(); 
            try{
                $setting_so = SettingStokOpnam::find($request->id);
                $apotek = MasterApotek::find($setting_so->id_apotek);
                $inisial = strtolower($apotek->nama_singkat);
                # set awal
                //DB::table('tb_m_stok_harga_'.$inisial)->update(['stok_awal_so'=> DB::raw('stok_akhir'), 'stok_akhir_so'=> 0, 'selisih' => 0]);
                $data = DB::table('tb_m_stok_harga_'.$inisial)->select(['id', 'id_obat', 'stok_akhir'])->get();
                //dd($data);exit();
                
                foreach ($data as $key => $obj) {
                    if($obj->id_obat != null) {
                        $obat = MasterObat::find($obj->id_obat);
                        $array_id_histori_stok_awal = array();
                        $historis = DB::table('tb_histori_stok_'.$inisial)
                                ->where('id_obat', $obj->id_obat)
                                ->whereIn('id_jenis_transaksi', [2,3,11,9])
                                ->where('sisa_stok', '>', 0)
                                ->orderBy('id', 'ASC')
                                ->get();

                        foreach ($historis as $key => $val) {
                            $array_id_histori_stok_awal[] = array('id_histori_stok' => $val->id, 'jumlah' => $val->sisa_stok, 'hb_ppn' => $val->hb_ppn, 'hb_ppn_avg' => $val->hb_ppn_avg);
                        }

                        DB::table('tb_m_stok_harga_'.$inisial)->where('id', $obj->id)->update(['nama' => $obat->nama, 'barcode' => $obat->barcode, 'stok_awal_so'=> $obj->stok_akhir, 'stok_akhir_so'=> 0, 'selisih' => 0, 'total_penjualan_so' => 0, 'id_so' => $setting_so->id, 'is_so' => 0, 'so_at' => null, 'so_by' => null, 'so_by_nama' => null, 'id_histori_stok_awal' => json_encode($array_id_histori_stok_awal)]); //, 'id_histori_stok_awal' => json_encode($array_id_histori_stok_awal
                    }
                }
                
                # update step
                $setting_so->step = $setting_so->step+1;
                $setting_so->updated_by = Auth::user()->id;
                $setting_so->updated_at = date('Y-m-d H:i:s');
                if($setting_so->save()){
                    DB::commit();
                    echo 1;
                }else{
                    DB::rollback();
                    echo 0;
                }
            }catch(\Exception $e){
                DB::rollback();
                session()->flash('error', 'Error!');
                return redirect('setting_so');
            }
        }
    }

    public function reload_data_akhir(Request $request) {
        ini_set('memory_limit', '-1'); 
        ini_set('max_execution_time', '0');
        DB::beginTransaction(); 
        try{
            $now = date('Y-m-d');
            $setting_so = SettingStokOpnam::find($request->id);
                if($now = $setting_so) {
                $apotek = MasterApotek::find($setting_so->id_apotek);
                $inisial = strtolower($apotek->nama_singkat);
                # buat histori
                $data = DB::table('tb_m_stok_harga_'.$inisial)->where('is_so', 0)->get();
                
                foreach ($data as $key => $obj) {
                    $stok_awal = $obj->stok_awal_so;
                    $stok_akhir = $obj->stok_akhir;
                    $hb_ppn_so = 0;
                    if(is_null($obj->hb_ppn_so) AND $obj->hb_ppn_so==0){
                        $hb_ppn_so = $obj->harga_beli_ppn;
                    }else{
                        $hb_ppn_so = $obj->hb_ppn_so;
                    }

                    $selisih = $stok_awal-$stok_akhir;
                    $stok_tersedia = 0;
                    $found = 0;
                    $stok_found = 0;
                    $stok_missing = 0;
                    $missing = abs($selisih);
                    if($hb_ppn_so != 0) {
                        $stok_missing = $missing*$hb_ppn_so;
                    } else {
                        $stok_missing = $missing*$obj->harga_beli;
                    }

                    DB::table('tb_m_stok_harga_'.$inisial)->where('id', $obj->id)->update(['stok_awal'=> $obj->stok_awal_so, 'stok_akhir' => 0, 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => Auth::user()->id, 'is_so' => 1, 'so_at' => date('Y-m-d H:i:s'), 'so_by' => Auth::user()->id, 'so_by_nama' => 'by sistem', 'stok_tersedia' => $stok_tersedia, 'stok_found' => $stok_found, 'stok_missing' => $stok_missing, 'found' => $found, 'missing' => $missing, 'hb_ppn_so' => $hb_ppn_so]);

                    DB::table('tb_histori_stok_'.$inisial)->insert([
                        'id_obat' => $obj->id_obat,
                        'jumlah' => $obj->stok_akhir_so,
                        'stok_awal' => $obj->stok_awal_so,
                        'stok_akhir' => 0,
                        'id_jenis_transaksi' => 11, //stok opnam
                        'id_transaksi' => $setting_so->id,
                        'batch' => null,
                        'ed' => null,
                        'hb_ppn' => $hb_ppn_so,
                        'sisa_stok' => 0,
                        'keterangan' => 'Stok Opnam (SO) pada ID.'.$setting_so->id.' sejumlah 0',
                        'created_at' => date('Y-m-d H:i:s'),
                        'created_by' => Auth::user()->id
                    ]);
                }

                # update step
                $setting_so->step = $setting_so->step+1;
                $setting_so->updated_by = Auth::user()->id;
                $setting_so->updated_at = date('Y-m-d H:i:s');
                if($setting_so->save()){
                    DB::commit();
                    echo 1;
                }else{
                    echo 0;
                }
            } else {
                echo 2;
            }
        }catch(\Exception $e){
            DB::rollback();
            session()->flash('error', 'Error!');
            return redirect('setting_so');
        }
    }

    public function export(Request $request) 
    {
        ini_set('memory_limit', '-1');

        $setting_so = SettingStokOpnam::find($request->id);
        $apotek = MasterApotek::find($setting_so->id_apotek);
        $inisial = strtolower($apotek->nama_singkat);
       
        $rekaps = DB::table('tb_m_stok_harga_'.$inisial.'')
                    ->where('tb_m_stok_harga_'.$inisial.'.is_deleted', 0)
                    ->get();


                $collection = collect();
                $no = 0;
                $total_excel=0;
                foreach($rekaps as $rekap) {
                    $no++;

                    $so = 'Tidak';
                    $stok_awal = $rekap->stok_awal_so;
                    $stok_akhir = $rekap->stok_akhir;
                    $selisih = $stok_awal-$stok_akhir;

                    if($stok_awal == 0) {
                        $stok_awal = '0';
                    }
                    if($stok_akhir == 0) {
                        $stok_akhir = '0';
                    }
                    if($selisih == 0) {
                        $selisih = '0';
                    }
                    $hb_ppn_so = 0;
                    if(is_null($rekap->hb_ppn_so) AND $rekap->hb_ppn_so==0){
                        $hb_ppn_so = $rekap->harga_beli_ppn;
                    }else{
                        $hb_ppn_so = $rekap->hb_ppn_so;
                    }

                    $stok_tersedia = 0;
                    if(is_null($rekap->stok_tersedia)) {
                    }else{
                        $stok_tersedia = $rekap->stok_tersedia;
                    }

                    $found = 0;
                    if(is_null($rekap->found)) {
                    }else{
                        $found = $rekap->found;
                    }

                    $stok_found = 0;
                    if(is_null($rekap->stok_found)) {
                    }else{
                        $stok_found = $rekap->stok_found;
                    }

                    $missing = 0;
                    if(is_null($rekap->missing)) {
                    }else{
                        $missing = $rekap->missing;
                    }

                    $stok_missing = 0;
                    if(is_null($rekap->stok_missing)) {
                    }else{
                        $stok_missing = $rekap->stok_missing;
                    }

                    if($rekap->is_so == 1) {
                        $so = 'Ya';
                        $stok_awal = $rekap->stok_awal_so;
                        $stok_akhir = $rekap->stok_akhir_so;
                        $selisih = $rekap->selisih;
                    } else {
                        $stok_tersedia = 0;
                        $found = 0;
                        $stok_found = 0;
                        $missing = abs($selisih);
                        if($hb_ppn_so != 0) {
                            $stok_missing = $missing*$hb_ppn_so;
                        } else {
                            $stok_missing = $missing*$rekap->harga_beli;
                        }
                    }

                    if($stok_tersedia == 0) {
                        $stok_tersedia = '0';
                    }

                    if($found == 0) {
                        $found = '0';
                    }

                    if($stok_found == 0) {
                        $stok_found = '0';
                    }

                    if($missing == 0) {
                        $missing = '0';
                    }

                    if($stok_missing == 0) {
                        $stok_missing = '0';
                    }

                    $obat = MasterObat::find($rekap->id_obat);

                    $collection[] = array(
                        $no, // a
                        $obat->barcode, //b
                        $obat->nama, //c
                        $rekap->harga_beli,
                        $hb_ppn_so,
                        $rekap->harga_jual,
                        $stok_awal, //g
                        $rekap->total_penjualan_so, //h
                        $stok_akhir, //i
                        $selisih, //j
                        $so, //k
                        $rekap->so_by_nama, //l
                        $rekap->so_at, //m
                        $stok_tersedia,
                        $found,
                        $stok_found,
                        $missing,
                        $stok_missing
                    );
                }

        return Excel::download(new class($collection) implements FromCollection, WithHeadings, WithColumnWidths, WithStyles {

                    public function __construct($collection)
                    {
                        $this->collection = $collection;
                    }

                    public function headings(): array
                    {
                        return ['No', 'Barcode', 'Nama Obat', 'Harga Beli', 'Harga Beli+PPN', 'Harga Jual', 'Stok Awal', 'Penjualan', 'Stok Akhir', 'Selisih', 'SO?', 'Update By', 'Update at', 'Total Persediaan', 'Jumlah Ditemukan', 'Total Nilai Ditemukan', 'Jumlah Hilang', 'Total Nilai Hilang'];
                    } 

                    public function columnWidths(): array
                    {
                        return [
                            'A' => 8,
                            'B' => 20,
                            'C' => 40,
                            'D' => 20,
                            'E' => 20,
                            'F' => 20,
                            'G' => 10,
                            'H' => 10, 
                            'I' => 10, 
                            'J' => 10,  
                            'K' => 10,  
                            'L' => 30,   
                            'M' => 20,
                            'N' => 20,
                            'O' => 20,
                            'P' => 20,
                            'Q' => 20,
                            'R' => 20,
                        ];
                    }

                    public function styles(Worksheet $sheet)
                    {
                        return [
                            1    => ['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'A'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'B'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'D'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                            'E'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                            'F'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                            'G'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'G'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'H'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'I'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'J'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'L'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'N'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                            'O'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'P'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                            'Q'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'R'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                        ];
                    }


                    public function collection()
                    {
                        return $this->collection;
                    }
        },"Data SO Apotek ".$apotek->nama_singkat.".xlsx");
    }

    public function backup_data_so(Request $request) {
        ini_set('memory_limit', '-1'); 
        DB::beginTransaction(); 
        try{
            $now = date('Y-m-d H:i:s');
            $id_user = Auth::user()->id;
            $setting_so = SettingStokOpnam::find($request->id);
            $apotek = MasterApotek::find($setting_so->id_apotek);
            $inisial = strtolower($apotek->nama_singkat);
            # select all data so

            $rekaps = DB::table('tb_m_stok_harga_'.$inisial.'')
                        ->select([DB::raw(''.$apotek->id.' as id_apotek'), 'id_obat', 'is_so', 'id_so', 'stok_awal_so', 'stok_akhir_so', 'selisih', DB::raw(''.$id_user.' as id_histori_stok_awal'), 'stok_tersedia', 'stok_found', 'stok_missing', 'found', 'missing', 'hb_ppn_so', 'total_penjualan_so', 'so_at', 'so_by', 'so_by_nama', DB::raw('"'.$now.'" as created_at'), DB::raw(''.$id_user.' as created_by'), DB::raw('"'.$now.'" as updated_at'), DB::raw(''.$id_user.' as updated_by')])
                        ->where('is_deleted', 0)
                        ->get();
            $rekaps = $rekaps->toArray();

            $data= json_decode( json_encode($rekaps), true);
            DB::connection()->getPdo()->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
            DB::table('tb_backup_data_so')->insert($data);
            DB::connection()->getPdo()->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            
            $setting_so->is_backup = 1;
            $setting_so->backup_by = Auth::user()->id;
            $setting_so->backup_at = date('Y-m-d H:i:s');
            if($setting_so->save()){
                DB::commit();
                echo 1;
            }else{
                DB::rollback();
                echo 0;
            }
        }catch(\Exception $e){
            DB::rollback();
            echo 0;
            session()->flash('error', 'Error!');
        }
    }
}
