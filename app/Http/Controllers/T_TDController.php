<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\TransaksiTD;
use App\TransaksiTDDetail;
use App\MasterObat;
use App\MasterApotek;
use App\MasterDokter;
use App\s;
use App\User;
use App;
use Datatables;
use DB;
use Auth;
use Excel;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
class T_TDController extends Controller
{
    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 07/11/2020
        =======================================================================================
    */
    public function index()
    {
        $dokters = MasterDokter::where('is_deleted', 0)->get();
        return view('transfer_dokter.index')->with(compact('dokters'));
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 07/11/2020
        =======================================================================================
    */
    public function list_transfer_dokter(Request $request)
    {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $apoteker = User::find($apotek->id_apoteker);
        $id_user = Auth::user()->id;

        $hak_akses = 0;
        if($apoteker->id == $id_user) {
            $hak_akses = 1;
        }

        if($id_user == 1 || $id_user == 2 || $id_user == 16) {
            $hak_akses = 1;
        }

        $tanggal = date('Y-m-d');
        DB::statement(DB::raw('set @rownum = 0'));
        $data = TransaksiTD::select([
                DB::raw('@rownum  := @rownum  + 1 AS no'),
	            'tb_nota_transfer_dokter.*', 
        ])
        ->where(function($query) use($request, $tanggal){
            $query->where('tb_nota_transfer_dokter.is_deleted','=','0');
            $query->where('tb_nota_transfer_dokter.id_apotek_nota','=',session('id_apotek_active'));
            $query->where('tb_nota_transfer_dokter.id','LIKE',($request->id > 0 ? $request->id : '%'.$request->id.'%'));
            $query->where('tb_nota_transfer_dokter.id_dokter','LIKE',($request->id_dokter > 0 ? $request->id_dokter : '%'.$request->id_dokter.'%'));
            if($request->tgl_awal != "") {
                $tgl_awal       = date('Y-m-d H:i:s',strtotime($request->tgl_awal));
                $query->whereDate('tb_nota_transfer_dokter.created_at','>=', $tgl_awal);
            }

            if($request->tgl_akhir != "") {
                $tgl_akhir      = date('Y-m-d H:i:s',strtotime($request->tgl_akhir));
                $query->whereDate('tb_nota_transfer_dokter.created_at','<=', $tgl_akhir);
            }
        });
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request){
            $query->where(function($query) use($request){
                //$query->orwhere('tb_nota_transfer_dokter.no_faktur','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->editcolumn('id_dokter', function($data){
        	return $data->dokter->nama;
        })
        ->editcolumn('is_lunas', function($data){
            if($data->is_lunas == 0) {
                return '<span class="label label-danger" data-toggle="tooltip" data-placement="top" title="Faktur Belum Dibayar">Belum Dibayar</span>';
            } else {
                return '<span class="label label-success" data-toggle="tooltip" data-placement="top" title="Faktur Sudah Dibayar"></i> Lunas</span>';
            }
        })   
        ->addcolumn('action', function($data) use($hak_akses){
            $btn = '<div class="btn-group">';
            $btn .= '<span class="btn btn-primary btn-sm" onClick="cetak_nota('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Cetak Nota"><i class="fa fa-print"></i> Cetak</span>';
            $btn .= '<a href="'.url('/transfer_dokter/'.$data->id.'/edit').'" title="Edit Data" class="btn btn-info btn-sm"><span data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i> Edit</span></a>';
            if($hak_akses == 1) {
                $btn .= '<span class="btn btn-danger btn-sm" onClick="delete_transfer('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i> Hapus</span>';
            }
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['action', 'is_lunas', 'id_dokter'])
        ->addIndexColumn()
        ->make(true);  
    }

    public function create() {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $dokters = MasterDokter::where('is_deleted', 0)->pluck('nama', 'id');
        $tanggal = date('Y-m-d');
        $transfer_dokter = new TransaksiTD;
        $detail_transfer_dokters = new TransaksiTDDetail;
        $var = 1;
        return view('transfer_dokter.create')->with(compact('transfer_dokter', 'dokters', 'detail_transfer_dokters', 'var', 'apotek', 'inisial'));
    }

    public function store(Request $request) {
        DB::beginTransaction(); 
        try{
            $transfer_dokter = new TransaksiTD;
            $transfer_dokter->fill($request->except('_token'));
            $transfer_dokter->id_apotek_nota = session('id_apotek_active');
            $transfer_dokter->tgl_nota = date('Y-m-d');
            $detail_transfer_dokters = $request->detail_transfer_dokter;

            $apotek = MasterApotek::find(session('id_apotek_active'));
            $inisial = strtolower($apotek->nama_singkat);
            $dokters = MasterDokter::where('is_deleted', 0)->pluck('nama', 'id');
            $tanggal = date('Y-m-d');

            $validator = $transfer_dokter->validate();
            if($validator->fails()){
                $var = 0;
                return view('transfer_dokter.create')->with(compact('transfer_dokter', 'dokters', 'detail_transfer_dokters', 'var', 'apotek', 'inisial'))->withErrors($validator);
            }else{
                $transfer_dokter->save_from_array($detail_transfer_dokters,1);
                DB::commit();
                session()->flash('success', 'Sukses menyimpan data!');
                return redirect('transfer_dokter');
            } 
        }catch(\Exception $e){
            DB::rollback();
            session()->flash('error', 'Error!');
            return redirect('penjualan');
        }
    }

    public function edit($id) {
        $transfer_dokter = TransaksiTD::find($id);
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $dokters = MasterDokter::where('is_deleted', 0)->pluck('nama', 'id');
        $tanggal = date('Y-m-d');

        $detail_transfer_dokters = $transfer_dokter->detail_transfer_dokter;

        $var = 0;
        return view('transfer_dokter.edit')->with(compact('transfer_dokter', 'dokters', 'detail_transfer_dokters', 'var', 'apotek', 'inisial'));
    }

    public function show($id) {

    }

    public function update(Request $request, $id) {
    	DB::beginTransaction(); 
        try{
	        $transfer_dokter = TransaksiTD::find($id);
	        $transfer_dokter->fill($request->except('_token'));
	        $detail_transfer_dokters = $request->detail_transfer_dokter;

	        $apotek = MasterApotek::find(session('id_apotek_active'));
	        $inisial = strtolower($apotek->nama_singkat);
	        $dokters = MasterDokter::where('is_deleted', 0)->pluck('nama', 'id');
	        $tanggal = date('Y-m-d');

	        $validator = $transfer_dokter->validate();
	        if($validator->fails()){
	            $var = 1;
	            return view('transfer_dokter.edit')->with(compact('transfer_dokter', 'dokters', 'detail_transfer_dokters', 'var', 'apotek', 'inisial'))->withErrors($validator);
	        }else{
	            $apotek = MasterApotek::find(session('id_apotek_active'));
	            $inisial = strtolower($apotek->nama_singkat);
	            $total_nota = 0;
	            foreach ($detail_transfer_dokters as $detail_transfer_dokter) {
	                $obj = TransaksiTDDetail::find($detail_transfer_dokter['id']);
	                $stok_before = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $obj->id_obat)->first();
	                $selisih = $obj->jumlah - $detail_transfer_dokter['jumlah'];

                    $selisih_format = abs($selisih);
	                if($selisih <= 0) { //plus
	                    $id_jenis_transaksi = 23;
	                    $stok_now = $stok_before->stok_akhir-$selisih_format;
	                } else {
	                    $id_jenis_transaksi = 24;
	                    $stok_now = $stok_before->stok_akhir+$selisih_format;
	                }

	                if($stok_now < 0) {
	                    session()->flash('error', 'Data tidak bisa disimpan, stok minus jika data ini disimpan!');
	                    return redirect('transfer_dokter')->with('message', 'Sukses menyimpan data');
	                } else {
	                    # update ke table stok harga
	                    DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $obj->id_obat)->update(['stok_awal'=> $stok_before->stok_akhir, 'stok_akhir'=> $stok_now, 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => Auth::user()->id]);

	                    # create histori
	                    DB::table('tb_histori_stok_'.$inisial)->insert([
	                        'id_obat' => $obj->id_obat,
	                        'jumlah' => $selisih_format,
	                        'stok_awal' => $stok_before->stok_akhir,
	                        'stok_akhir' => $stok_now,
	                        'id_jenis_transaksi' => $id_jenis_transaksi, 
	                        'id_transaksi' => $obj->id,
	                        'batch' => null,
	                        'ed' => null,
	                        'created_at' => date('Y-m-d H:i:s'),
	                        'created_by' => Auth::user()->id
	                    ]);

	                    $obj->harga_dokter = $detail_transfer_dokter['harga_dokter'];
	                    $obj->jumlah = $detail_transfer_dokter['jumlah'];
	                    $obj->total = $detail_transfer_dokter['harga_dokter'] * $detail_transfer_dokter['jumlah'];
	                    $obj->save();
	                    $total_nota = $total_nota+$obj->total;
	                }
	            }

	            $transfer_dokter->grand_total = $total_nota;
	            $transfer_dokter->save();
	            DB::commit();
	            session()->flash('success', 'Sukses memperbaharui data!');
	            return redirect('transfer_dokter')->with('message', 'Sukses menyimpan data');
	        }
	   	}catch(\Exception $e){
            DB::rollback();
            session()->flash('error', 'Error!');
            return redirect('transfer_dokter');
        }
    }

    public function destroy($id) {
        DB::beginTransaction(); 
        try{
            $apotek = MasterApotek::find(session('id_apotek_active'));
            $inisial = strtolower($apotek->nama_singkat);
            $to = TransaksiTD::find($id);
            $to->is_deleted = 1;
            $to->deleted_at = date('Y-m-d H:i:s');
            $to->deleted_by = Auth::user()->id;
            $to->grand_total = 0;
            
            $detail_transfer_dokters = TransaksiTDDetail::where('id_nota', $to->id)->get();
            foreach ($detail_transfer_dokters as $key => $val) {
                $detail_transfer_dokter = TransaksiTDDetail::find($val->id);
                $detail_transfer_dokter->is_deleted = 1;
                $detail_transfer_dokter->deleted_at = date('Y-m-d H:i:s');
                $detail_transfer_dokter->deleted_by = Auth::user()->id;
                $detail_transfer_dokter->save();

                $stok_before = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $detail_transfer_dokter->id_obat)->first();
                $selisih = $detail_transfer_dokter->jumlah;

                $id_jenis_transaksi = 25;
                $stok_now = $stok_before->stok_akhir+$selisih;
                # update ke table stok harga
                DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $detail_transfer_dokter->id_obat)->update(['stok_awal'=> $stok_before->stok_akhir, 'stok_akhir'=> $stok_now, 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => Auth::user()->id]);

                # create histori
                DB::table('tb_histori_stok_'.$inisial)->insert([
                    'id_obat' => $detail_transfer_dokter->id_obat,
                    'jumlah' => $selisih,
                    'stok_awal' => $stok_before->stok_akhir,
                    'stok_akhir' => $stok_now,
                    'id_jenis_transaksi' => $id_jenis_transaksi, 
                    'id_transaksi' => $detail_transfer_dokter->id,
                    'batch' => null,
                    'ed' => null,
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => Auth::user()->id
                ]);
            }

            if($to->save()){
                DB::commit();
                echo 1;
            }else{
                echo 0;
            }
        }catch(\Exception $e){
            DB::rollback();
            session()->flash('error', 'Error!');
            return redirect('transfer_dokter');
        }
    }

    public function find_ketentuan_keyboard(){
        return view('transfer_dokter._form_ketentuan_keyboard');
    }

    public function edit_detail(Request $request){
        $id = $request->id;
        $no = $request->no;
        $detail = TransaksiTDDetail::find($id);
        return view('transfer_dokter._form_edit_detail')->with(compact('detail', 'no'));
    }

    public function hapus_detail($id) {
        DB::beginTransaction(); 
        try{
            $detail_transfer_dokter = TransaksiTDDetail::find($id);
            $detail_transfer_dokter->is_deleted = 1;
            $detail_transfer_dokter->deleted_at= date('Y-m-d H:i:s');
            $detail_transfer_dokter->deleted_by = Auth::user()->id;
            $detail_transfer_dokter->save();

            $apotek = MasterApotek::find(session('id_apotek_active'));
            $inisial = strtolower($apotek->nama_singkat);
            $stok_before = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $detail_transfer_dokter->id_obat)->first();
            $selisih = $detail_transfer_dokter->jumlah;

            $id_jenis_transaksi = 25;
            $stok_now = $stok_before->stok_akhir+$selisih;
           
            # update ke table stok harga
            DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $detail_transfer_dokter->id_obat)->update(['stok_awal'=> $stok_before->stok_akhir, 'stok_akhir'=> $stok_now, 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => Auth::user()->id]);

            # create histori
            DB::table('tb_histori_stok_'.$inisial)->insert([
                'id_obat' => $detail_transfer_dokter->id_obat,
                'jumlah' => $selisih,
                'stok_awal' => $stok_before->stok_akhir,
                'stok_akhir' => $stok_now,
                'id_jenis_transaksi' => $id_jenis_transaksi, 
                'id_transaksi' => $detail_transfer_dokter->id,
                'batch' => null,
                'ed' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => Auth::user()->id
            ]);

            $total = TransaksiTDDetail::select([
                                DB::raw('SUM(total) as total_all')
                                ])
                                ->where('id', '!=', $detail_transfer_dokter->id)
                                ->where('id_nota', $detail_transfer_dokter->id_nota)
                                ->where('is_deleted', 0)
                                ->first();

            $y = 0;
            if($total->total_all == 0 OR $total->total_all == '') {
                $y = 0;
            } else {
                $y = $total->total_all;
            }

            $transfer_dokter = TransaksiTD::find($detail_transfer_dokter->id_nota);
            if($y == 0) {
                $transfer_dokter->grand_total = $y;
                $transfer_dokter->is_deleted = 1;
                $transfer_dokter->deleted_at= date('Y-m-d H:i:s');
                $transfer_dokter->deleted_by = Auth::user()->id;
            }   

            if($transfer_dokter->save()){
                DB::commit();
                echo 1;
            }else{
                echo 0;
            }
        }catch(\Exception $e){
            DB::rollback();
            session()->flash('error', 'Error!');
            return redirect('transfer_dokter');
        }
    }

    public function cetak_nota(Request $request)
    {   
        $transfer_dokter = TransaksiTD::where('id', $request->id)->first();
        $detail_transfer_dokters = TransaksiTDDetail::select(['tb_detail_nota_transfer_dokter.*'])
                                               ->where('tb_detail_nota_transfer_dokter.id_nota', $transfer_dokter->id)
                                               ->get();

        return view('transfer_dokter._form_cetak_nota')->with(compact('transfer_dokter', 'detail_transfer_dokters'));
    } 

    public function load_data_nota_print($id) {
        $no = 0;

        $nota = TransaksiTD::find($id);
        $detail_transfer_dokters = TransaksiTDDetail::where('id_nota', $nota->id)->get();
        $apotek = MasterApotek::find(session('id_apotek_active'));
	    $inisial = strtolower($apotek->nama_singkat);
        $nama_apotek = strtoupper($apotek->nama_panjang);
        $nama_apotek_singkat = strtoupper($apotek->nama_singkat);

        $a = str_pad("",40," ", STR_PAD_LEFT)."\n".
             str_pad("APOTEK BWF-".$nama_apotek, 40," ", STR_PAD_BOTH)."\n".
             str_pad($apotek->alamat, 40," ", STR_PAD_BOTH)."\n".
             str_pad("Telp. ". $apotek->telepon, 40," ", STR_PAD_BOTH);
        $a = $a."\n".
        "----------------------------------------\n".
        "No. Nota  : ".$nama_apotek_singkat."-".$nota['id']."\n".
        "Tanggal   : ".date_format($nota['created_at'],'d-m-Y H:i:s')."\n".
        "Dokter    : ".$nota->dokter->nama."\n".
        "----------------------------------------\n";

        $b="\n".
        "       Kasir,       ".$nota->dokter->nama.",       \n".
        "                                        \n".
        "                                        \n".
        "                                        \n".
        "(-----------------) (-----------------)\n";

        
        $total_belanja = 0;
        foreach ($detail_transfer_dokters as $key => $val) {
            $no++;
            $total_belanja = $total_belanja + $val->total;
            
            $a=$a.
                str_pad($no.".".$val->obat->nama, 40," ", STR_PAD_RIGHT)."\n ".                 
                //str_pad(" (diskon ".number_format($diskon, 0, '.', ',')."%)",11," ", STR_PAD_LEFT)."\n ".
                str_pad(number_format($val->harga_dokter, 0, '.', ','), 7," ", STR_PAD_LEFT).
                str_pad(" x ",3," ", STR_PAD_LEFT).
                str_pad(number_format($val->jumlah, 0, '.', ','),9," ", STR_PAD_RIGHT).
                str_pad("= ",3," ", STR_PAD_LEFT).str_pad("Rp ". number_format($val->total, 0, '.', ','),10," ", STR_PAD_LEFT)."\n";

        }

        $a=$a.
            "----------------------------------------\n".
            "Total     : Rp ".number_format($total_belanja,0,',',',')."\n".
            "----------------------------------------\n";
        $a=$a.$b."\n".
            "----------------------------------------\n";
        $a=$a.str_pad("~ Selamat bekerja ~", 40," ", STR_PAD_BOTH);
        $a=$a."\n".
            "----------------------------------------\n";


        $b=$a.str_pad("",40," ", STR_PAD_LEFT)."\n"."\n".str_pad("",40," ", STR_PAD_LEFT)."\n".str_pad("",40," ", STR_PAD_LEFT)."\n".str_pad("",40," ", STR_PAD_LEFT)."\n".str_pad("",40," ", STR_PAD_LEFT)."\n".str_pad("",40," ", STR_PAD_LEFT)."\n".str_pad("",40," ", STR_PAD_LEFT)."\n".str_pad("",40," ", STR_PAD_LEFT);       
            
            print_r($b) ;
    }

    public function pencarian_obat() {
        return view('transfer_dokter.pencarian_obat');
    }

    public function list_pencarian_obat(Request $request) {
        DB::statement(DB::raw('set @rownum = 0'));
        $data = TransaksiTDDetail::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_detail_nota_transfer_dokter.*', 'a.nama'])
        ->join('tb_m_obat as a', 'a.id', 'tb_detail_nota_transfer_dokter.id_obat')
        ->join('tb_nota_transfer_dokter as b', 'b.id', 'tb_detail_nota_transfer_dokter.id_nota')
        ->where(function($query) use($request){
            $query->where('tb_detail_nota_transfer_dokter.is_deleted','=','0');
            $query->where('b.id_apotek_nota','=',session('id_apotek_active'));
        })
        ->orderBy('b.id', 'DESC');
        
        $datatables = Datatables::of($data);
        return $datatables  
        ->filter(function($query) use($request){
            $query->where(function($query) use($request){
                $query->orwhere('a.nama','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->editcolumn('created_at', function($data) use($request){
            return Carbon::parse($data->created_at)->format('d/m/Y H:i:s');
        })
        ->editcolumn('created_by', function($data) use($request){
            return $data->created_oleh->nama;
        })
        ->editcolumn('id_obat', function($data) {
            $info = '<small>Dokter : '.$data->nota->dokter->nama.'</small>';
            return $data->nama.'<br>'.$info;
        })  
        ->editcolumn('total', function($data) {
            $str_ = '';
            $str_ = $data->jumlah.' X Rp '.number_format($data->harga_dokter, 2).' = Rp '.number_format($data->total, 2);
            return $str_;
        })    
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['action', 'id_obat'])
        ->addIndexColumn()
        ->make(true);  
    }

    public function export(Request $request) 
    {
        $rekaps = TransaksiTD::select([
                                    DB::raw('@rownum  := @rownum  + 1 AS no'),
                                    'tb_nota_transfer_dokter.*'
                                ])
                                ->where(function($query) use($request){
                                    $query->where('tb_nota_transfer_dokter.is_deleted','=','0');
                                    $query->where('tb_nota_transfer_dokter.id_apotek_nota','=',session('id_apotek_active'));
                                    $query->where('tb_nota_transfer_dokter.id','LIKE',($request->id > 0 ? $request->id : '%'.$request->id.'%'));
                                    $query->where('tb_nota_transfer_dokter.id_dokter','LIKE',($request->id_dokter > 0 ? $request->id_dokter : '%'.$request->id_dokter.'%'));
                                    if (!empty($request->tgl_awal) && !empty($request->tgl_akhir)) {
                                        $query->where('tb_nota_transfer_dokter.created_at','>=', $request->tgl_awal);
                                        $query->where('tb_nota_transfer_dokter.created_at','<=', $request->tgl_akhir);
                                    }
                                })
                                ->groupBy('tb_nota_transfer_dokter.id')
                                ->get();


                $collection = collect();
                $no = 0;
                $total_excel=0;
                foreach($rekaps as $rekap) {
                    $no++;
                    $collection[] = array(
                        $no,
                        $rekap->created_at,
                        $rekap->created_oleh->nama,
                        $rekap->dokter->nama,
                        $rekap->grand_total,
                        "Rp ".number_format($rekap->grand_total,2),
                        $rekap->keterangan
                    );
                }

        return Excel::download(new class($collection) implements FromCollection, WithHeadings, WithColumnWidths, WithStyles {

                    public function __construct($collection)
                    {
                        $this->collection = $collection;
                    }

                    public function headings(): array
                    {
                        return ['No', 'Tanggal', 'Kasir', 'Dokter', 'Total', 'Total (Rp)', 'Keterangan'];
                    } 

                    /*public function columnFormats(): array
                    {
                        return [
                            'B' => NumberFormat::FORMAT_DATE_DDMMYYYY,
                            'C' => NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE,
                        ];
                    }*/

                    public function columnWidths(): array
                    {
                        return [
                            'A' => 8,
                            'B' => 20,
                            'C' => 30,
                            'D' => 30,
                            'E' => 18,
                            'F' => 18,
                            'G' => 50,            
                        ];
                    }

                    public function styles(Worksheet $sheet)
                    {
                        return [
                            1    => ['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'A'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'B'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            //'C'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            //'D'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'F'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                        ];
                    }


                    public function collection()
                    {
                        return $this->collection;
                    }
        },"Rekap Transfer Dokter.xlsx");
    }
}
