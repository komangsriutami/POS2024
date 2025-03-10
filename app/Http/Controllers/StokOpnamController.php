<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataTables\StokSODataTable;
use App\DataTables\StokSODataTableEditor;
use App\MasterApotek;
use App\SettingStokOpnam;
use App\TransaksiPenjualanDetail;
use App\ReturPembelian;
use App\TransaksiPembelianDetail;
use App\TransaksiTODetail;
use App\TransaksiTDDetail;
use App\TransaksiPODetail;
use App\HistoriStok;
use App\MasterStokHarga;
use App\MasterObat;
use Datatables;
use DB;
use Excel;
use Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StokOpnamController extends Controller
{
    public function index(StokSODataTable $dataTable)
    {
        //echo "under maintenance"; exit();
        /*if(Auth::user()->id!=1) {
            echo "under maintenance"; exit();
        } else {*/
            $nama_apotek_singkat_active = session('nama_apotek_singkat_active');
        	$id_apotek = session('id_apotek_active');
        	$now = date('Y-m-d');
            $datenow = date('d-m-Y H:i:s');
        	$cek = SettingStokOpnam::where('id_apotek', $id_apotek)->where('tgl_so', $now)->where('step',1)->first();
            $total_barang = MasterObat::where('is_deleted', 0)->count();
            $total_so = MasterStokHarga::where('is_deleted', 0)->where('is_so', 1)->count();
            $total = MasterStokHarga::select([
                            DB::raw('SUM(stok_tersedia) as total_tersedia'),
                            DB::raw('SUM(stok_found) as total_found'),
                            DB::raw('SUM(stok_missing) as total_missing'),
                            DB::raw('SUM(found) as jumlah_found'),
                            DB::raw('SUM(missing) as jumlah_missing'),
                            DB::raw('SUM(stok_akhir_so) as jumlah_tersedia')
                        ])
                        ->where('is_deleted', 0)->where('is_so', 1)
                        ->first();

            $jumlah_item_found = MasterStokHarga::where('found','>', 0)->where('is_deleted', 0)->where('is_so', 1)->count();
            $jumlah_item_missing = MasterStokHarga::where('missing','>', 0)->where('is_deleted', 0)->where('is_so', 1)->count();
        	if($id_apotek != $id_apotek) {
        		$apotek = MasterApotek::find($id_apotek);
        		return view('so.page_not_select_apotek')->with(compact('apotek'));
        	} else {
        		if($cek == null) {
        			return view('so.page_not_setting_so');
        		} else {
                    $cek_ = session('so_status_aktif');

                    if($cek_ == null) {
                        session(['so_status_aktif'=> 1]);
                    }
                    
                    session(['id_so'=> $cek->id]);
        			return $dataTable->render('so._form_so')->with(compact('total_barang', 'datenow', 'total_so', 'total', 'jumlah_item_found', 'jumlah_item_missing'));
        		}
        	}
        //}
    }

    public function set_so_status_aktif(Request $request) {
        session(['so_status_aktif'=>$request->so_status_aktif]);
        echo 1;
    }

    public function store(StokSODataTableEditor $editor)
    {
        return $editor->process(request());
    }

    public function export(Request $request) 
    {
        ini_set('memory_limit', '-1');
        
        $id_apotek = session('id_apotek_active');
        $apotek = MasterApotek::find($id_apotek);
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
                    if($rekap->is_so == 1) {
                        $so = 'Ya';
                        $stok_awal = $rekap->stok_awal_so;
                        $stok_akhir = $rekap->stok_akhir_so;
                        $selisih = $rekap->selisih;
                    }

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

                    $collection[] = array(
                        $no, // a
                        $rekap->barcode, //b
                        $rekap->nama, //c
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


    public function export_awal(Request $request) 
    {
        $id_apotek = session('id_apotek_active');
        $apotek = MasterApotek::find($id_apotek);
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
                    if($rekap->is_so == 1) {
                    	$so = 'Ya';
                    }
                    
                    $collection[] = array(
                        $no,
                        $rekap->barcode,
                        $rekap->nama,
                        "Rp ".number_format($rekap->harga_beli,2),
                        "Rp ".number_format($rekap->harga_jual,2),
                        $rekap->stok_awal_so,
                        $rekap->stok_akhir_so,
                        $rekap->selisih,
                        $so,
                        $rekap->so_by_name,
                        $rekap->so_at
                    );
                }

        return Excel::download(new class($collection) implements FromCollection, WithHeadings, WithColumnWidths, WithStyles {

                    public function __construct($collection)
                    {
                        $this->collection = $collection;
                    }

                    public function headings(): array
                    {
                        return ['No', 'Barcode', 'Nama Obat', 'Harga Beli', 'Harga Jual', 'Stok Awal', 'Stok Akhir', 'Selisih', 'SO?', 'Update By', 'Update at'];
                    } 

                    public function columnWidths(): array
                    {
                        return [
                            'A' => 8,
                            'B' => 20,
                            'C' => 40,
                            'D' => 20,
                            'E' => 20,
                            'F' => 10, 
                            'G' => 10, 
                            'H' => 10,  
                            'I' => 10,  
                            'J' => 30,   
                            'K' => 20,
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
                            'F'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'G'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'H'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'I'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'K'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                        ];
                    }


                    public function collection()
                    {
                        return $this->collection;
                    }
        },"Data SO Apotek Awal".$apotek->nama_singkat.".xlsx");
    }

    public function reload_stok_awal(Request $request)
    {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $cek = DB::table('tb_m_stok_harga_'.$inisial.'')
                ->where('id', $request->id)
                ->first();

        $last = DB::table('tb_histori_stok_'.$inisial.'')
                ->where('id_obat', $cek->id_obat)
                ->where('id_jenis_transaksi', '!=', 11)
                ->orderBy('id', 'DESC')
                ->first();

        if(!empty($last)) {
            DB::table('tb_m_stok_harga_'.$inisial.'')
                ->where('id', $request->id)
                ->update(['stok_awal_so' => $last->stok_akhir]);
        }

        echo 1;
    }

    
    public function get_data(Request $request)
    {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $so_status_aktif = session('so_status_aktif');
        DB::statement(DB::raw('set @rownum = 0'));
        $data = DB::table('tb_m_stok_harga_'.$inisial.'')->select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_m_stok_harga_'.$inisial.'.*'])
        ->where(function($query) use($request, $inisial, $so_status_aktif){
            $query->where('tb_m_stok_harga_'.$inisial.'.is_deleted','=','0');
            if($so_status_aktif == 2) {
                $query->where('tb_m_stok_harga_'.$inisial.'.selisih','!=','0');
            } else if($so_status_aktif == 3) {
                $query->where('tb_m_stok_harga_'.$inisial.'.is_so', 1);
                $query->where('tb_m_stok_harga_'.$inisial.'.stok_awal_so', '!=','0');
                $query->where('tb_m_stok_harga_'.$inisial.'.stok_akhir_so','0');
            } else if($so_status_aktif == 4) {
                $query->where('tb_m_stok_harga_'.$inisial.'.is_so', 0);
                $query->where('tb_m_stok_harga_'.$inisial.'.stok_awal_so', '!=','0');
            }
        });
        
        $datatables = Datatables::of($data);
        return $datatables  
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->orwhere('nama','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('barcode','LIKE','%'.$request->get('search')['value'].'%');
            });
        })   
        ->editcolumn('stok_akhir', function($data){
            $btn_edit = '<span class="label" onClick="edit_stok('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Edit Data" style="font-size:10pt;color:#0097a7;">[Edit]</span>';

            //$btn_edit ='';

            return $data->stok_akhir.'</br>'.$btn_edit; 
        })
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .= '<span class="btn btn-default" onClick="show_histori_stok('.$data->id_obat.')" data-toggle="tooltip" data-placement="top" title="Histori Stok"><i class="fa fa-eye"></i></span>';
           /* $btn .= '<span class="btn btn-primary" onClick="edit_data('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span>';*/
          /*  $btn .= '<span class="btn btn-danger" onClick="delete_agama('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';*/
            /*$btn .= '<span class="btn btn-default" onClick="reload_stok_awal('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Reload Stok Awal"><i class="fa fa-retweet"></i></span>';*/

            
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['action', 'stok_akhir'])
        ->addIndexColumn()
        ->make(true);  
    }


    public function edit_stok($id) {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $obat = DB::table('tb_m_stok_harga_'.$inisial.'')
                ->where('id', $id)
                ->first();


        return view('so.edit_stok')->with(compact('obat'));
    }

    public function update_stok(Request $request) {
       // dd("asda");
        DB::beginTransaction(); 
        try{
            $apotek = MasterApotek::find(session('id_apotek_active'));
            $inisial = strtolower($apotek->nama_singkat);
            $id_so = session('id_so');
            $keterangan = "-";
            $stok_before = MasterStokHarga::find($request->id);
            
            if(is_null($stok_before->id_histori_stok_awal) OR $stok_before->id_histori_stok_awal =='' OR $stok_before->id_histori_stok_awal =='[]' ) {
                $historis =DB::table('tb_histori_stok_'.$inisial)->where('id_obat', $stok_before->id_obat)
                        ->whereIn('id_jenis_transaksi', [2,3,11,9])
                        ->where('sisa_stok', '<=', 0)
                        ->orderBy('id', 'DESC')
                        ->limit(1)
                        ->get();

                $array_id_histori_stok_awal = array();
                foreach ($historis as $key => $val) {
                    $array_id_histori_stok_awal[] = array('id_histori_stok' => $val->id, 'jumlah' => $val->sisa_stok, 'hb_ppn' => $val->hb_ppn, 'hb_ppn_avg' => $val->hb_ppn_avg);
                }

                     // dd($array_id_histori_stok_awal);

                # update dlu ke histori awal
                 DB::table('tb_m_stok_harga_'.$inisial)->where('id', $request->id)->update(['id_histori_stok_awal'=> json_encode($array_id_histori_stok_awal), 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => Auth::user()->id]);

                $stok_before = MasterStokHarga::find($request->id);
                $array_id_histori_stok_awal = json_decode($stok_before->id_histori_stok_awal);
                //dd($array_id_histori_stok_awal);exit();

            } else {
                $array_id_histori_stok_awal = json_decode($stok_before->id_histori_stok_awal);
            }
          
            $total_penjualan = DB::table('tb_histori_stok_'.$inisial)->select([DB::raw('SUM(jumlah) as total')])->where('id_obat', $request->id_obat)->where('id_jenis_transaksi', 1)->whereDate('created_at', date('Y-m-d'))->first();
            $total_hapus = DB::table('tb_histori_stok_'.$inisial)->select([DB::raw('SUM(jumlah) as total')])->where('id_obat', $request->id_obat)->where('id_jenis_transaksi', 15)->whereDate('created_at', date('Y-m-d'))->first();
            $total_retur = DB::table('tb_histori_stok_'.$inisial)->select([DB::raw('SUM(jumlah) as total')])->where('id_obat', $request->id_obat)->where('id_jenis_transaksi', 5)->whereDate('created_at', date('Y-m-d'))->first();
            $total_batal_retur = DB::table('tb_histori_stok_'.$inisial)->select([DB::raw('SUM(jumlah) as total')])->where('id_obat', $request->id_obat)->where('id_jenis_transaksi', 6)->whereDate('created_at', date('Y-m-d'))->first();
            $count_penjualan = ($total_penjualan->total+$total_batal_retur->total)-($total_hapus->total+$total_retur->total);
            $stok_awal_so = $request->stok_awal_so;
            $stok_awal = $stok_awal_so-$count_penjualan;
            $stok_akhir = $request->stok_akhir_so;
            $stok_akhir_so = $request->stok_akhir_so;
            $selisih = ($stok_akhir_so+$count_penjualan)-$stok_awal_so;

                //print_r($stok_before); exit();

            # set 0 sejumlah stok opnam 
            $i = $stok_akhir_so;
            $total_hpp = 0;
            $jumlah = 0;
            $hpp_khusus_0 = 0;
            foreach ($array_id_histori_stok_awal as $y => $hist) {
                $cekHistori = HistoriStok::find($hist->id_histori_stok);
                # cari hbppn
                $total_hpp = $total_hpp + ($hist->jumlah * $hist->hb_ppn);
                $jumlah = $jumlah + $hist->jumlah;

                if($hist->jumlah == 0) {
                    $hpp_khusus_0 = $hist->hb_ppn;
                   // exit();
                }

                # kosongkan semua stok
                if(!is_null($cekHistori->keterangan)) {
                    $keterangan = $cekHistori->keterangan;
                }
                $keterangan = $keterangan.', Rest Stok (SO) pada ID.'.$id_so.' sejumlah '.$hist->jumlah;
                $cekHistori->sisa_stok = 0;
                $cekHistori->keterangan = $keterangan;
                if($cekHistori->save()) {
                } else {
                    DB::rollback();
                    echo json_encode(array('status' => 0, 'message' => 'Error, gagal melakukan pengembalian stok ke histori sebelumnya'));
                }
            } 

            //print_r($total_hpp);exit();
            if($jumlah == 0) {
                $hpp = $hpp_khusus_0;
            } else {
                $hpp = $total_hpp/$jumlah;
                $hpp = ceil($hpp);
            }

            $stok_tersedia = $stok_akhir_so * $hpp;
            $stok_tersedia = ceil($stok_tersedia);

            # kosongkan juga jika sudah ada stok opnam sebelumnya
            $befores = HistoriStok::where('id_obat', $request->id_obat)->where('id_transaksi', $id_so)->get();
            foreach ($befores as $y => $hist) {
                # kosongkan semua stok
                if(!is_null($cekHistori->keterangan)) {
                    $keterangan = $cekHistori->keterangan;
                }
                $keterangan = $keterangan.', Rest Stok (SO) pada ID.'.$id_so.' sejumlah '.$hist->jumlah;
                $hist->sisa_stok = 0;
                $hist->keterangan = $keterangan;
                if($hist->save()) {
                } else {
                    DB::rollback();
                    echo json_encode(array('status' => 0, 'message' => 'Error, gagal melakukan pengembalian stok ke histori stok opnam sebelumnya'));
                }
            }

            # cek selisih
            if($selisih < 0) {
                # jika selisih kurang dari 0 maka ada kemungkinan barang hilang | jika selisih kurang dari 0 maka ada kemungkinan barang hilang - -10
                //echo "jika selisih kurang dari 0 maka ada kemungkinan barang hilang - ".$selisih;
                $stok_found = 0;
                $found = 0;

                $stok_missing = abs($selisih) * $hpp;
                $stok_missing = ceil($stok_missing);
                $missing = abs($selisih);
            } else {
                # jika selisih lebih maka ada barang yang ditemukan  | jika selisih lebih maka ada barang yang ditemukan - 20
                //echo "jika selisih lebih maka ada barang yang ditemukan  - ".$selisih;

               // dd($array_id_histori_stok_awal);exit();
               
                $stok_found = $selisih * $hpp;
                $stok_found = ceil($stok_found);
                $found = $selisih;

                $stok_missing = 0;
                $missing = 0;
            }

            $stok_before->stok_awal = $stok_awal;
            $stok_before->stok_akhir = $stok_akhir; 
            $stok_before->stok_akhir_so = $stok_akhir_so; 
            $stok_before->total_penjualan_so = $count_penjualan;
            $stok_before->selisih = $selisih;
            $stok_before->id_so = $id_so;
            $stok_before->is_so = 1;
            $stok_before->so_at = date('Y-m-d H:i:s');
            $stok_before->so_by = Auth::user()->id;
            $stok_before->updated_at = date('Y-m-d H:i:s');
            $stok_before->updated_by = Auth::user()->id;
            $stok_before->so_by_nama = Auth::user()->username;
            $stok_before->stok_tersedia = $stok_tersedia;
            $stok_before->stok_found = $stok_found;
            $stok_before->stok_missing = $stok_missing; 
            $stok_before->found = $found;
            $stok_before->missing = $missing; 
            $stok_before->hb_ppn_so = $hpp;
            if($stok_before->save()) {
            } else {
                DB::rollback();
                echo json_encode(array('status' => 0, 'message' => 'Error, gagal update master stok'));
            }

            $arrayinsert_ = array(
                'id_obat' => $request->id_obat,
                'jumlah' => $stok_akhir_so,
                'stok_awal' => $stok_awal_so,
                'stok_akhir' => $stok_akhir_so,
                'id_jenis_transaksi' => 11, //stok opnam
                'id_transaksi' => $id_so,
                'batch' => null,
                'ed' => null,
                'hb_ppn' => $hpp,
                'sisa_stok' => $stok_akhir_so,
                'keterangan' => 'Stok Opnam (SO) pada ID.'.$id_so.' sejumlah '.$stok_akhir_so,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => Auth::user()->id
            );

            # create histori
            $histori_stok = new HistoriStok;
            if($histori_stok->insert($arrayinsert_)) {
                DB::commit();
                echo json_encode(array('status' => 1, 'total_penjualan' => $count_penjualan, 'id' => $request->id_obat, 'selisih' => $selisih));
            } else {
                DB::rollback();
                echo json_encode(array('status' => 0, 'message' => 'Error, gagal create histori stok'));
            }
        }catch(\Exception $e){
            DB::rollback();
            echo json_encode(array('status' => 0, 'message' => $e->getMessage()));
        }
    }


    public function kurangStok($id_so, $id_obat, $jumlah) {
        $inisial = strtolower(session('nama_apotek_singkat_active'));
        $cekHistori = DB::table('tb_histori_stok_'.$inisial)
                            ->where('id_obat', $id_obat)
                            ->whereIn('id_jenis_transaksi', [2,3,11,9])
                            ->where('sisa_stok', '>=', 1)
                            ->orderBy('id', 'ASC')
                            ->get();

        $array_id_histori_stok = array();
        $array_id_histori_stok_detail = array();
        $hb_ppn = 0;
        if(!empty($cekHistori)) {
            $total = 0;
            $i = 0;
           // print_r($cekHistori);exit();
            foreach ($cekHistori as $key => $val) {
                $keterangan = $val->keterangan.', Rest Stok (SO) pada ID.'.$id_so.' sejumlah '.$val->sisa_stok;
                DB::table('tb_histori_stok_'.$inisial)->where('id', $val->id)->update(['sisa_stok' => 0, 'keterangan' => $keterangan]);
                $array_id_histori_stok[] = $val->id;
                $array_id_histori_stok_detail[] = array('id_histori_stok' => $val->id, 'jumlah' => $val->sisa_stok);
                $total = $total + $val->hb_ppn * $val->sisa_stok;
                $i = $i + $val->sisa_stok;
            }
            if($total != 0) {
                $hb_ppn = $total/$i;
            } 

            $hb_ppn = ceil($hb_ppn);
            $rsp = array('status' => 1, 'array_id_histori_stok' => json_encode($array_id_histori_stok), 'array_id_histori_stok_detail' => json_encode($array_id_histori_stok_detail), 'hb_ppn' => $hb_ppn);
            return $rsp;
        } else {
            # jika seluruh stok telah habis maka
            $cekHistoriLanj = DB::table('tb_histori_stok_'.$inisial)
            ->where('id_obat', $id_obat)
            ->whereIn('id_jenis_transaksi', [2,3,11,9])
            ->orderBy('id', 'DESC')
            ->first();

            if(is_null($cekHistoriLanj)) {
                $stokharga = DB::table('tb_m_stok_harga_'.$inisial)
                    ->where('id_obat', $id_obat)
                    ->first();
                    
                if(is_null($stokharga)) {
                    $hb_ppn = 0;
                    $rsp = array('status' => 0, 'array_id_histori_stok' => null, 'array_id_histori_stok_detail' => null, 'hb_ppn' => $hb_ppn);
                } else{
                    $hb_ppn = $stokharga->harga_beli_ppn;
                    $rsp = array('status' => 1, 'array_id_histori_stok' => null, 'array_id_histori_stok_detail' => null, 'hb_ppn' => $hb_ppn);
                } 
            } else {
                $hb_ppn = $cekHistoriLanj->hb_ppn;
                $rsp = array('status' => 1, 'array_id_histori_stok' => null, 'array_id_histori_stok_detail' => null, 'hb_ppn' => null);
            }
            
            return $rsp;
        }
    }

    public function show_histori_stok($id) {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $obat = DB::table('tb_m_stok_harga_'.$inisial.'')
                ->where('id_obat', $id)
                ->first();

        return view('so.histori_stok')->with(compact('obat'));
    }


    public function get_data_stok_harga(Request $request)
    {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];
        $now = date('Y-m-d');

        $month = date('m');
        $year = date('Y');
        
        DB::statement(DB::raw('set @rownum = 0'));
        $data = DB::table('tb_histori_stok_'.$inisial.'')->select([
                    DB::raw('@rownum  := @rownum  + 1 AS no'), 
                    'tb_histori_stok_'.$inisial.'.*', 
                    'users.nama as oleh',
                    'tb_m_jenis_transaksi.nama as nama_transaksi',
                    'tb_m_jenis_transaksi.act'
                ])
                ->join('users', 'users.id', '=', 'tb_histori_stok_'.$inisial.'.created_by')
                ->join('tb_m_jenis_transaksi', 'tb_m_jenis_transaksi.id', '=', 'tb_histori_stok_'.$inisial.'.id_jenis_transaksi')
                ->where(function($query) use($request, $inisial, $now, $month, $year){
                    $query->where('tb_histori_stok_'.$inisial.'.id_obat', $request->id_obat);
                    $query->whereMonth('tb_histori_stok_'.$inisial.'.created_at', $month);
                    $query->whereYear('tb_histori_stok_'.$inisial.'.created_at', $year);
                });

        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request,$order_column,$order_dir, $inisial){
            $query->where(function($query) use($request, $inisial){
                $query->orwhere('tb_histori_stok_'.$inisial.'.created_at','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('tb_histori_stok_'.$inisial.'.batch','LIKE','%'.$request->get('search')['value'].'%');
            });
        })   
        ->editcolumn('created_at', function($data){
            return date('d-m-Y', strtotime($data->created_at)); 
        }) 
        ->editcolumn('id_jenis_transaksi', function($data){
            $string = '';
            $id_nota = ''; 
            $data_pembelian_ = array(2, 12, 13, 14, 26, 27, 30, 31);
            $data_tf_masuk_ = array(3, 7, 16, 28, 29, 32, 33);
            $data_tf_keluar_ = array(4, 8, 17);
            $data_penjualan_ = array(1, 5, 6, 15);
            $data_penyesuaian_ = array(9,10);
            $data_so_ = array(11);
            $data_po_ = array(18, 19, 20, 21);
            $data_td_ = array(22, 23, 24, 25);
            if (in_array($data->id_jenis_transaksi, $data_pembelian_)) {
                if($data->id_jenis_transaksi == 26) {
                    $retur = ReturPembelian::find($data->id_transaksi);
                    $check = TransaksiPembelianDetail::find($retur->id_detail_nota);
                    $id_nota = ' | IDNota : '.$check->nota->id.' | No.Faktur : '.$check->nota->no_faktur;
                    $string = '<b>'.$check->nota->suplier->nama.'</b>';
                } else {
                    $check = TransaksiPembelianDetail::find($data->id_transaksi);
                    $id_nota = ' | IDNota : '.$check->nota->id.' | No.Faktur : '.$check->nota->no_faktur;
                    $string = '<b>'.$check->nota->suplier->nama.'</b>';
                }
            } else if (in_array($data->id_jenis_transaksi, $data_tf_masuk_)) {
                $check = TransaksiTODetail::find($data->id_transaksi);
                $id_nota = ' | IDNota : '.$check->nota->id;
                $string = '<b>Masuk dari '.$check->nota->apotek_asal->nama_singkat.'</b>';
            } else if (in_array($data->id_jenis_transaksi, $data_tf_keluar_)) {
                $check = TransaksiTODetail::find($data->id_transaksi);
                $id_nota = ' | IDNota : '.$check->nota->id;
                $string = '<b>Tujuan ke '.$check->nota->apotek_tujuan->nama_singkat.'</b>';
            } else if (in_array($data->id_jenis_transaksi, $data_penjualan_)) {
                $check = TransaksiPenjualanDetail::find($data->id_transaksi);
                if($check->nota->is_kredit == 1) {
                    $string = '<b>Vendor : '.$check->nota->vendor->nama.'</b>';
                } else {
                    $string = '<b>Member : - </b>';
                }
                $id_nota = ' | IDNota : '.$check->nota->id;
            } else if (in_array($data->id_jenis_transaksi, $data_po_)) {
                $check = TransaksiPODetail::find($data->id_transaksi);
                $id_nota = ' | IDNota : '.$check->nota->id;
            } else if (in_array($data->id_jenis_transaksi, $data_td_)) {
                $check = TransaksiTDDetail::find($data->id_transaksi);
                $id_nota = ' | IDNota : '.$check->nota->id;
            } else  if(in_array($data->id_jenis_transaksi, array(26))) {
                $retur = ReturPembelian::find($data->id_transaksi);
                $check = TransaksiPembelianDetail::find($retur->id_detail_nota);
                $id_nota = ' | IDNota : '.$check->nota->id.' | No.Faktur : '.$check->nota->no_faktur;
                $string = '<b>'.$check->nota->suplier->nama.'</b>';
            } 

            if($string != '') {
                $string = '<br>'.$string;
            }

            $det_ = '<span style="font-size:10pt;">'.$data->nama_transaksi.$string.'<br>'.'IDdet : '.$data->id_transaksi.$id_nota.'</span>';

            return $det_; 
        }) 
        ->editcolumn('hb_ppn', function($data){
            $hb_ppn = 'Rp '.number_format($data->hb_ppn,0,',','.');
            $hb_ppn_avg = 'Rp '.number_format($data->hb_ppn_avg,0,',','.');
            $info = '<span>';
            $info .= '<b>'.$hb_ppn.'</b></span><br>';
            $info .= '<span class="badge badge-warning"><i class="fa fa-angle-double-right"></i> avg : '.$hb_ppn_avg.' <br>';
            $info .= '</span>';
            return $info; 
        }) 
        ->editcolumn('masuk', function($data){
            $masuk = 0;
            if($data->act == 1) {
                $masuk = $data->jumlah;
            } 
            return $masuk; 
        }) 
        ->editcolumn('keluar', function($data){
            $keluar = 0;
            if($data->act == 2) {
                $keluar = $data->jumlah;
            } 
            return $keluar;  
        }) 
        ->editcolumn('stok_akhir', function($data){
            return $data->stok_akhir; 
        }) 
        ->editcolumn('batch', function($data){
            $batch = '-';
            $data_ = array(2, 12, 13, 26, 27);
            if (in_array($data->id_jenis_transaksi, $data_))
            {
                $batch = $data->batch;
            }
            return $batch;
        }) 
        ->editcolumn('ed', function($data){
            $ed = '-';
            $data_ = array(2, 12, 13, 14, 26, 27, 30, 31);
            if (in_array($data->id_jenis_transaksi, $data_))
            {
                $check = TransaksiPembelianDetail::find($data->id_transaksi);
                //$ed = '('.$data->batch.')<br>';
                if($check->tgl_batch == '' OR $check->tgl_batch == null OR $check->tgl_batch == '0') {
                    $ed = '-';
                } else {
                    $ed = $check->tgl_batch;
                }
            }
            return $ed;
        }) 
        ->editcolumn('created_by', function($data){
            if(strlen($data->oleh) > 15) {
                //$trimstring = substr($data->oleh, 0, 15);
                $trimstring = $data->oleh;
                $oleh = '<span class="badge">created by : '.$trimstring;
            } else {
                $oleh = '<span class="badge">crated by : '.$data->oleh;
            }

            $data_tf_ = array(3, 7, 16, 28, 29, 32, 33, 4, 8, 17);

            if (in_array($data->id_jenis_transaksi, $data_tf_)) {
                $check = TransaksiTODetail::find($data->id_transaksi);
                

                if($check->is_status == 1) {
                    $oleh .= '<hr><span class="text-info">konfirm by : '.$check->konfirm_oleh->nama.'</span>';
                } else {
                    $oleh .= '<hr><span class="text-info">konfirm by :  [belum dikonfirm]'.'</span>';
                }
            }

            $oleh.= '</span>';

            $oleh = strtolower($oleh);

            return $oleh;
        }) 
        ->rawColumns(['craeted_at', 'id_jenis_transaksi', 'masuk', 'keluar', 'stok_akhir', 'batch', 'ed', 'created_by', 'hb_ppn'])
        ->make(true);  
    }
}
