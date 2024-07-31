<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\TransaksiPenjualan;
use App\TransaksiPenjualanDetail;
use App\MasterObat;
use App\MasterApotek;
use App\MasterJasaResep;
use App\DefectaOutlet;
use App\MasterDokter;
use App\MasterKartu;
use App\User;
use App\MasterVendor;
use App\MasterAlasanRetur;
use App\ReturPenjualan;
use App\MasterMember;
use App\MasterPaketWD;
use App\TransaksiPenjualanClosing;
use App\SettingPromo;
use App\SettingPromoDetail;
use App\MasterJenisPromo;
use App\MasterMemberTipe;
use App\SettingPromoItemBeli;
use App\SettingPromoItemDiskon;
use App\HistoriStok;
use App\MasterStokHarga;
use App\MasterJenisKelamin;
use App\MasterGroupApotek;
use App\MasterKabupaten;
use Illuminate\Support\Carbon;
use Spipu\Html2Pdf\Html2Pdf;
use App\Events\PenjualanRetur;
use App\Events\PenjualanReturBatal;
use App\Events\PenjualanCreate;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\SettingStokOpnam;
use App;
use Datatables;
use DB;
use Auth;
use Excel;
use Crypt;
use PDF;
use Mail;

class T_PenjualanController extends Controller
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
        return view('penjualan.index');
    }


    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 07/11/2020
        =======================================================================================
    */
    public function list_penjualan(Request $request)
    {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $apoteker = User::find($apotek->id_apoteker);
        $id_user = Auth::user()->id;

        $hak_akses = 0;
        if($apoteker->id == $id_user) {
            $hak_akses = 1;
        }

        if(Auth::user()->is_admin == 1) {
            $hak_akses = 1;
        }

        $tanggal = date('Y-m-d');
        DB::statement(DB::raw('set @rownum = 0'));
        $data = TransaksiPenjualan::select([
                DB::raw('@rownum  := @rownum  + 1 AS no'),
                'tb_nota_penjualan.*', 
        ])
        ->where(function($query) use($request, $tanggal){
            $query->where('tb_nota_penjualan.is_deleted','=','0');
            $query->where('tb_nota_penjualan.id_apotek_nota','=',session('id_apotek_active'));
            if(session('id_role_active') != 1) {
                $query->where('tb_nota_penjualan.created_by', Auth::user()->id);
            }
            $query->where('tb_nota_penjualan.created_at', 'LIKE', '%'.$tanggal.'%');
        })
        ->orderBy('tb_nota_penjualan.id', 'DESC');
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request){
            $query->where(function($query) use($request){
                $query->orwhere('tb_nota_penjualan.id','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->editcolumn('created_at', function($data) use($request){
            $string = '';
            if($data->is_penjualan_tolak_cn == 1) {
                $string .='<br><small class="text-red">Penambahan dari penolakan retur</small>';
            }

            if($data->keterangan != "" OR $data->keterangan != null) {
                $keterangan = '('.$data->keterangan.')';
            } else {
                $keterangan = '';
            }

            if($data->is_kredit == 1) {
                $str_ = '';
                if($data->is_margin_kurang == 1) {
                    $str_ = '<span class="text-danger text-bold">[ MARGIN < 5% ]</span>';
                }
                return Carbon::parse($data->created_at)->format('d/m/Y H:i:s').$string.'<br><small><b>'.$data->vendor->nama.'</b>('.$keterangan.')</small><br>'.$str_;
            } else {
                return Carbon::parse($data->created_at)->format('d/m/Y H:i:s').$string;
            }
        })
        ->editcolumn('total_belanja', function($data) use($request){
            $total = $data->detail_penjualan_total[0]->total;
            if($total == "" || $total == null) {
                $total = 0;
            }

            $diskon = $data->diskon_persen/100*$total;
            $str_diskon = '';
            if($diskon != 0) {
                $str_diskon = "-(Rp ".number_format($diskon,2).')';
            }

            $string = '';
            if($data->cek_retur[0]->total_cn != 0) {
                $string = "<br>".'<small><b style="color:red;">-ADA RETUR ITEM OBAT-</b></small>';
            }
            return "Rp ".number_format($total,2).$string;
        })   
        ->editcolumn('biaya_jasa_dokter', function($data) use($request){
            return "Rp ".number_format($data->biaya_resep,2).'/'."Rp ".number_format($data->biaya_jasa_dokter,2).'/'."Rp ".number_format($data->harga_wd,2).'/'."Rp ".number_format($data->biaya_lab,2).'/'."Rp ".number_format($data->biaya_apd,2);
        })   
        ->editcolumn('total_fix', function($data) use($request){
             $total = $data->detail_penjualan_total[0]->total;
            $diskon = 0;
            if($data->diskon_persen != "" && $data->diskon_persen != null) {
                $diskon = $data->diskon_persen/100*$total;
                //$diskon = "Rp ".number_format($diskon,2);
            }

            $diskon_vendor = 0;
            if($data->diskon_vendor != "" && $data->diskon_vendor != null) {
                $diskon_vendor = $data->diskon_vendor/100*$total;
               // $diskon_vendor = "Rp ".number_format($diskon_vendor,2);
            }
            if($total == "" || $total == null) {
                $total = 0;
            }

            $total_diskon = $diskon+$diskon_vendor;
            $total_fix = ($total+$data->biaya_resep+$data->biaya_jasa_dokter+$data->harga_wd+$data->biaya_lab+$data->biaya_apd)-$total_diskon;
            return "("."Rp ".number_format($total_diskon,2).")"."<br><b>"."Rp ".number_format($total_fix,2).'</b>';
        })   
        ->editcolumn('is_kredit', function($data) use($request){
           
            if($data->is_kredit == 1) {
                $string = "K";
            } else {
                $string = "N";
            }
           
            return $string;
        }) 
        ->addcolumn('is_lunas', function($data) use($request){
           
            if($data->total_bayar > 0) {
                $string = "Y";
            } else {
                $string = "N";
            }
           
            return $string;
        })   
        ->addcolumn('action', function($data) use($hak_akses){
            $btn = '<div class="btn-group">';
            //$btn .= '<span class="btn btn-primary btn-sm" onClick="cetak_nota('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Cetak Nota"><i class="fa fa-print"></i> Cetak</span>';
            $btn .= '<span class="btn btn-primary btn-sm" onClick="pelunasan('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Cetak Nota"><i class="fa fa-clipboard-check"></i> Pelunasan</span>';
            $btn .= '<a href="'.url('/penjualan/cetak_nota/'.$data->id).'" title="Cetak Nota" target="_blank"  class="btn btn-default btn-sm"><span data-toggle="tooltip" data-placement="top" title="Cetak Nota"><i class="fa fa-print"></i> Cetak</span></a>';
            $btn .= '<a href="'.url('/penjualan/detail/'.$data->id).'" title="Lihat detail penjualan" class="btn btn-info btn-sm"><span data-toggle="tooltip" data-placement="top" title="Lihat detail penjualan"><i class="fa fa-eye"></i> Detail</span></a>';
            //$btn .= '<a href="'.url('/penjualan/'.$data->id.'/edit').'" title="Edit Data" class="btn btn-info btn-sm"><span data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i> Edit</span></a>';
            //$btn .= '<span class="btn btn-danger btn-sm" onClick="delete_penjualan('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i> Hapus</span>';

            if($data->total_bayar != 0 AND !is_null($data->total_bayar)) {
                # jika sudah dibayar
                if($hak_akses == 1) {
                    # jika admin
                    if($data->cek_retur[0]->total_cn == 0) {
                        $btn .= '<span class="btn btn-danger btn-sm" onClick="delete_penjualan('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i> Hapus</span>';
                    }
                } else {
                    //$btn .= '<span class="btn btn-danger btn-sm" onClick="delete_penjualan('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i> Hapus</span>';
                }
            } else {
                # jika belum dibayar
                if($data->cek_retur[0]->total_cn == 0) {
                    $btn .= '<span class="btn btn-danger btn-sm" onClick="delete_penjualan('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i> Hapus</span>';
                }
               
            }

            if($data->is_kredit == 1) {
                $btn .= '<a href="'.url('/penjualan/invoice/'.Crypt::encrypt($data->id)).'" target="_blank" title="Invoice" class="btn btn-warning btn-sm"><span data-toggle="tooltip" data-placement="top" title="Invoice"><i class="fa fa-file-pdf"></i> Invoice</span></a>';
            }

            $btn .='</div>';
            return $btn;
        })    
        ->setRowClass(function ($data) {
            if($data->total_bayar < 1 AND $data->is_kredit == 0) {
                if($data->cek_retur[0]->total_cn == 0) {
                    return 'bg-secondary';
                } else {
                    return 'bg-info';
                }
            } else {
                return '';
            }
        })  
        ->rawColumns(['action', 'created_at', 'total_fix', 'total_belanja', 'is_lunas'])
        ->addIndexColumn()
        ->make(true);  
    }

   
    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 07/11/2020
        =======================================================================================
    */
    public function create()
    {
        $penjualan = new TransaksiPenjualan;
        $detail_penjualans = new TransaksiPenjualanDetail;
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $tanggal = date('Y-m-d');
        $var = 1;

        $is_kredit = 0;
        $is_margin = 0;

        $members = MasterMember::where('is_deleted', 0)->pluck('nama', 'id');
        $members->prepend('-- Pilih Member --','');
        $hak_akses = 1;

        $apoteker = User::find($apotek->id_apoteker);
        $id_user = Auth::user()->id;

        $hak_akses_margin = 0;
        if($apoteker->id == $id_user) {
            $hak_akses_margin = 1;
        }

        if(Auth::user()->is_admin == 1) {
            $hak_akses_margin = 1;
        }

        return view('penjualan.create')->with(compact('penjualan', 'tanggal', 'detail_penjualans', 'var', 'is_kredit', 'inisial', 'apotek', 'members', 'hak_akses', 'is_margin', 'hak_akses_margin'));
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 07/11/2020
        =======================================================================================
    */
    public function store(Request $request)
    {
        DB::beginTransaction(); 
        try{
            $penjualan = new TransaksiPenjualan;
            $penjualan->fill($request->except('_token'));
            if($request->is_kredit == 1) {
                $vendor = MasterVendor::find($request->id_vendor);
                $penjualan->id_vendor = $request->id_vendor;
                $penjualan->diskon_vendor = $vendor->diskon;
                $penjualan->tgl_jatuh_tempo = $request->tgl_jatuh_tempo;
                $penjualan->cash = 0;
                $penjualan->kembalian = 0;
                $penjualan->total_bayar = 0;
            } else {
                $penjualan->tgl_jatuh_tempo = $request->tgl_nota;
            }

            if($request->id_pasien != '') {
                $penjualan->id_pasien = $request->id_pasien;
            } 

            if($request->id_jasa_resep == '') {
                $penjualan->id_jasa_resep = 4;
                $penjualan->biaya_resep = 0;
            } 

            if($request->id_dokter == '') {
                $penjualan->id_dokter = 0;
                $penjualan->biaya_jasa_dokter = 0;
            }  

            if($request->id_paket_wd == '') {
                $penjualan->id_paket_wd = 0;
                $penjualan->harga_wd = 0;
            } 

            if($request->nama_lab == '') {
                $penjualan->biaya_lab = 0;
                $penjualan->keterangan_lab = '';
            }

            if($request->biaya_apd == '') {
                $penjualan->biaya_apd = 0;
            }   

            $detail_penjualans = collect();
            $is_kredit = $request->is_kredit;
            $is_penjualan_tanpa_item = 1;
            $penjualan->is_penjualan_tanpa_item = $is_penjualan_tanpa_item;

            if(!empty($penjualan->id_jasa_resep)) {
                $biaya_resep = MasterJasaResep::find($penjualan->id_jasa_resep);
                $penjualan->biaya_resep = $biaya_resep->biaya;
            }
           
            $tanggal = date('Y-m-d');

            $apotek = MasterApotek::find(session('id_apotek_active'));
            $inisial = strtolower($apotek->nama_singkat);

            $members = MasterMember::where('is_deleted', 0)->pluck('nama', 'id');
            $members->prepend('-- Pilih Member --','');

            $validator = $penjualan->validate();
            if($validator->fails()){
                $var = 0;
                DB::rollback();
                echo json_encode(array('status' => 0));
                /*return view('penjualan.create')->with(compact('penjualan', 'apotek', 'tanggal', 'detail_penjualans', 'var', 'is_kredit', 'inisial', 'members'))->withErrors($validator);*/
            }else{
                if($penjualan->is_penjualan_tanpa_item == 0 && empty($detail_penjualans)){
                    $var = 0;
                    //session()->flash('error', 'Item penjualan belum ditambahkan!');
                    /*return view('penjualan.create')->with(compact('penjualan', 'apoteks', 'tanggal', 'detail_penjualans', 'var', 'is_kredit', 'members'))->withErrors($validator);*/
                    DB::rollback();
                    echo json_encode(array('status' => 0, 'id' => $penjualan->id));
                } else {                   
                    $penjualan->save_from_array($detail_penjualans,1);
                    //session()->flash('success', 'Sukses menyimpan data!');
                    //return redirect('penjualan');
                    DB::commit();
                    //return redirect('penjualan/cetak_nota/'.$penjualan->id);
                    
                    echo json_encode(array('status' => 1, 'id' => $penjualan->id));
                }
            }
        }catch(\Exception $e){
            DB::rollback();
            //session()->flash('error', 'Error!');
            echo json_encode(array('status' => 0));
            //return redirect('penjualan');
        }
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 07/11/2020
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
        Date    : 07/11/2020
        =======================================================================================
    */
    public function edit($id)
    {
        $penjualan = TransaksiPenjualan::find($id);
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $detail_penjualans = $penjualan->detail_penjualan;
        /*DB::table('tb_detail_nota_penjualan')
                                ->select('tb_detail_nota_penjualan.id',
                                    'tb_detail_nota_penjualan.id_obat',
                                    'tb_detail_nota_penjualan.id_nota',
                                    'tb_detail_nota_penjualan.harga_jual',
                                    'tb_detail_nota_penjualan.jumlah',
                                    'tb_detail_nota_penjualan.diskon',
                                    'a.nama as nama_obat',
                                    DB::raw('((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) - tb_detail_nota_penjualan.diskon) AS total'))
                                ->join('tb_m_obat as a','a.id','=','tb_detail_nota_penjualan.id_obat')
                                ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')
                                ->where('tb_detail_nota_penjualan.id_nota', $id)
                                ->where('tb_detail_nota_penjualan.is_cn', 0)
                                ->get();*/
        $var = 0;
        $tanggal = date('Y-m-d');
        $is_kredit = $penjualan->is_kredit;

        $members = MasterMember::where('is_deleted', 0)->pluck('nama', 'id');
        $members->prepend('-- Pilih Member --','');

        $apoteker = User::find($apotek->id_apoteker);
        $id_user = Auth::user()->id;

        $hak_akses = 0;
        if($apoteker->id == $id_user) {
            $hak_akses = 0;
        }

        if($id_user == 1 || $id_user == 2 || $id_user == 16) {
            $hak_akses = 1;
        }

        $vendor_kerjama = MasterVendor::where('is_deleted', 0)->get();

        $hak_akses_margin = $hak_akses;

        return view('penjualan.edit')->with(compact('penjualan', 'tanggal', 'detail_penjualans', 'var', 'is_kredit', 'inisial', 'apotek', 'members', 'hak_akses', 'vendor_kerjama', 'hak_akses_margin'));
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 07/11/2020
        =======================================================================================
    */
    public function update(Request $request, $id)
    {
        DB::beginTransaction(); 
        try{
            $penjualan = TransaksiPenjualan::find($id);
            $penjualan->fill($request->except('_token', 'created_at'));

            $is_penjualan_tanpa_item = 1;
            $jum = TransaksiPenjualanDetail::where('id_nota', $penjualan->id)->where('is_deleted', 0)->count();
            if($jum > 0) {
                $is_penjualan_tanpa_item = 0;
            } else {
                # jika ada diskon 
            }

            $penjualan->is_penjualan_tanpa_item = $is_penjualan_tanpa_item;

            if(isset($request->pembayaran_kredit) && $request->pembayaran_kredit == 1) {
                $penjualan->is_lunas_pembayaran_kredit = 1;
                $penjualan->is_lunas_pembayaran_kredit_at = date('Y-m-d H:i:s');
                $penjualan->is_lunas_pembayaran_kredit_by = Auth::user()->id;
                $penjualan->save();
                session()->flash('success', 'Sukses memperbaharui data!');
                return redirect('penjualan/penjualan_kredit')->with('message', 'Sukses menyimpan data');
            } else {
                if($request->is_kredit == 1) {
                    $penjualan->id_vendor = $request->id_vendor;
                    $penjualan->diskon_vendor = $request->diskon_vendor;
                    $penjualan->tgl_jatuh_tempo = $request->tgl_jatuh_tempo;
                    $penjualan->cash = 0;
                    $penjualan->kembalian = 0;
                } else {
                    $penjualan->tgl_jatuh_tempo = $request->tgl_nota;
                }

                if($request->id_pasien != '') {
                    $penjualan->id_pasien = $request->id_pasien;
                }   

                if(!empty($penjualan->id_jasa_resep)) {
                    $biaya_resep = MasterJasaResep::find($penjualan->id_jasa_resep);
                    $penjualan->biaya_resep = $biaya_resep->biaya;
                }

                $detail_penjualans = $request->detail_penjualan;
                $apoteks = MasterApotek::where('is_deleted', 0)->pluck('nama_panjang', 'id');

                $members = MasterMember::where('is_deleted', 0)->pluck('nama', 'id');
                $members->prepend('-- Pilih Member --','');

                $validator = $penjualan->validate();
                if($validator->fails()){
                    $var = 0;
                    DB::rollback();
                    echo json_encode(array('status' => 0, 'id' => $penjualan->id));
                    //return view('penjualan.edit')->with(compact('penjualan', 'apoteks', 'var', 'detail_penjualans', 'members'))->withErrors($validator);
                }else{
                    $penjualan->id_apotek_nota = session('id_apotek_active');
                    $penjualan->updated_by = Auth::user()->id;
                    $penjualan->tgl_nota = date('Y-m-d');
                    $penjualan->updated_at = date('Y-m-d H:i:s');
                    $penjualan->save();
                    //session()->flash('success', 'Sukses memperbaharui data!');
                    //return redirect('penjualan')->with('message', 'Sukses menyimpan data');
                    DB::commit();
                    
                    echo json_encode(array('status' => 1, 'id' => $penjualan->id));
                }
            }     
        }catch(\Exception $e){
            DB::rollback();
            echo json_encode(array('status' => 0));
        }  
    }


    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 07/11/2020
        =======================================================================================
    */
    public function destroy_back($id)
    {
        DB::beginTransaction(); 
        try{
            $apotek = MasterApotek::find(session('id_apotek_active'));
            $inisial = strtolower($apotek->nama_singkat);
            $penjualan = TransaksiPenjualan::find($id);
            $penjualan->is_deleted = 1;
            $penjualan->deleted_at = date('Y-m-d H:i:s');
            $penjualan->deleted_by = Auth::user()->id;

            $detail_penjualans = TransaksiPenjualanDetail::where('id_nota', $id)->where('is_deleted', 0)->get();
            $cek = TransaksiPenjualanDetail::where('id_nota', $id)->where('is_cn', 1)->count();
            if($cek > 0) {
                echo 0;
            } else {
                foreach ($detail_penjualans as $key => $val) {
                    $detail_penjualan = TransaksiPenjualanDetail::find($val->id);
                    $detail_penjualan->is_deleted = 1;
                    $detail_penjualan->deleted_at = date('Y-m-d H:i:s');
                    $detail_penjualan->deleted_by = Auth::user()->id;
                    $detail_penjualan->save();

                    $stok_before = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $detail_penjualan->id_obat)->first();
                    $jumlah = $detail_penjualan->jumlah;
                    $stok_now = $stok_before->stok_akhir+$jumlah;

                    # update ke table stok harga
                    DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $detail_penjualan->id_obat)->update(['stok_awal'=> $stok_before->stok_akhir, 'stok_akhir'=> $stok_now, 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => Auth::user()->id]);

                    # create histori
                    DB::table('tb_histori_stok_'.$inisial)->insert([
                        'id_obat' => $detail_penjualan->id_obat,
                        'jumlah' => $jumlah,
                        'stok_awal' => $stok_before->stok_akhir,
                        'stok_akhir' => $stok_now,
                        'id_jenis_transaksi' => 15, //hapus pembelian
                        'id_transaksi' => $detail_penjualan->id,
                        'batch' => null,
                        'ed' => null,
                        'created_at' => date('Y-m-d H:i:s'),
                        'created_by' => Auth::user()->id
                    ]);   
                }

                if($penjualan->save()){
                    DB::commit();
                    echo 1;
                }else{
                    echo 0;
                }
            }
        }catch(\Exception $e){
            DB::rollback();
            session()->flash('error', 'Error!');
            return redirect('penjualan');
        }
    }

    public function cari_obat(Request $request) {
        $obat = MasterObat::where('barcode', $request->barcode)->first();

        $cek_ = 0;
        $batas_max_hpp = 0;
        if(!empty($obat)) {
            $cek_ = 1;
            $harga_stok = DB::table('tb_m_stok_harga_'.$request->inisial)->where('id_obat', $obat->id)->first();
            $batas_max_hpp = $harga_stok->harga_beli_ppn + (10/100*$harga_stok->harga_beli_ppn);
        } else {
            $harga_stok = array();
        }

        $data = array('is_data' => $cek_, 'obat'=> $obat, 'harga_stok' => $harga_stok, 'batas_max_hpp' => $batas_max_hpp);
        return json_encode($data);
    }

    public function cari_obatID(Request $request) {
        $obat = MasterObat::where('id', $request->id_obat)->first();


        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);

        $cek_ = 0;
        $batas_max_hpp = 0;
        if(!empty($obat)) {
            $cek_ = 1;
            $harga_stok = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $obat->id)->first();
            $batas_max_hpp = $harga_stok->harga_beli_ppn + (10/100*$harga_stok->harga_beli_ppn);
        } else {
            $harga_stok = array();
        }

        $data = array('is_data' => $cek_, 'obat'=> $obat, 'harga_stok' => $harga_stok, 'batas_max_hpp' => $batas_max_hpp);
        return json_encode($data);
    }

    public function cari_obat_dialog(Request $request) {
        $obat = MasterObat::find($request->id_obat);

        return json_encode($obat);
    }

    public function open_data_obat(Request $request) {
        $barcode = $request->barcode;
        return view('penjualan._dialog_open_obat')->with(compact('barcode'));
    }

    public function cari_pasien_dialog(Request $request) {
        $pasien = MasterMember::find($request->id);

        return json_encode($pasien);
    }

    public function open_data_pasien(Request $request) {
        $pasien = $request->pasien;
        return view('penjualan._dialog_open_pasien')->with(compact('pasien'));
    }

    public function list_data_pasien(Request $request)
    {
        $pasien = $request->pasien;

        DB::statement(DB::raw('set @rownum = 0'));
        $data = MasterMember::select([
                DB::raw('@rownum  := @rownum  + 1 AS no'),
                'tb_m_member.*'
        ])
        ->where(function($query) use($request){
            $query->where('tb_m_member.is_deleted','=','0');
        });
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request, $pasien){
            $query->where(function($query) use($request, $pasien){
                $query->orwhere('tb_m_member.nama','LIKE','%'.$request->get('search')['value'].'%');
            });
        })   
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .= '<span class="btn btn-primary" onClick="add_pasien_dialog('.$data->id.')" data-toggle="tooltip" data-placement="top" title="pilih pasien"><i class="fa fa-check"></i></span>';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['action'])
        ->addIndexColumn()
        ->make(true);  
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
                'b.untung_jual',
                'b.sku'
        ])
        ->join('tb_m_obat as b', 'b.id', '=', 'a.id_obat')
        ->where(function($query) use($request){
            $query->where('b.is_deleted','=','0');
        });
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request, $barcode){
            $query->where(function($query) use($request, $barcode){
               // if($request->get('search')['value'] != "") {
                    $query->orwhere('b.nama','LIKE','%'.$request->get('search')['value'].'%');
                    $query->orwhere('b.barcode','LIKE','%'.$request->get('search')['value'].'%');
                    $query->orwhere('b.sku','LIKE','%'.$request->get('search')['value'].'%');
                /* else {
                    $query->limi(10);
                }*/
            });
        })   
        ->editcolumn('nama', function($data){
            return '<b>'.$data->nama.'</b><br><span class="text-info">SKU : '.$data->sku.'</span>'; 
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
        ->editcolumn('harga_beli_ppn', function($data){
            return 'Rp '.number_format($data->harga_beli_ppn, 2, '.', ','); 
        }) 
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .= '<span class="btn btn-primary" onClick="add_item_dialog('.$data->id_obat.', '.$data->harga_jual.', '.$data->harga_beli.', '.$data->stok_akhir.', '.$data->harga_beli_ppn.', '.$data->untung_jual.')" data-toggle="tooltip" data-placement="top" title="Tambah Item"><i class="fa fa-plus"></i></span>';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['nama', 'stok_akhir', 'action'])
        ->addIndexColumn()
        ->make(true);  
    }

    public function set_jasa_dokter(Request $request){
        $penjualan = new TransaksiPenjualan;
        if($request->id) {
            $penjualan = TransaksiPenjualan::find($request->id);
        }
        $dokters = MasterDokter::where('is_deleted', 0)->get();
        $jasa_reseps = MasterJasaResep::where('is_deleted', 0)->get();
        $harga_total = $request->harga_total;
        if(empty($harga_total)) {
            $harga_total = 0;
        }

        $biaya_jasa_dokter = $request->biaya_jasa_dokter;
        $biaya_resep = $request->biaya_resep;
        $id_dokter = $request->id_dokter;
        $id_jasa_resep = $request->id_jasa_resep;

        return view('penjualan._form_set_jasa_dokter')->with(compact('penjualan', 'dokters', 'harga_total', 'jasa_reseps', 'biaya_jasa_dokter', 'biaya_resep', 'id_dokter', 'id_jasa_resep'));
    }

    public function set_diskon_persen(Request $request){
        $penjualan = new TransaksiPenjualan;
        $karyawans = User::where('is_deleted', 0)->get();
        $total_penjualan = $request->total_penjualan; 
        $harga_total = $request->harga_total; //+ $request->total_biaya_dokter;
        $diskon_total = $request->diskon_total;
        $id_karyawan = $request->id_karyawan;
        $diskon_persen = $request->diskon_persen;

        if(empty($harga_total)) {
            $harga_total = 0;
        }

        if(empty($diskon_total)) {
            $diskon_total = 0;
        }
        return view('penjualan._form_set_diskon_persen')->with(compact('penjualan', 'harga_total', 'diskon_total', 'karyawans', 'total_penjualan', 'id_karyawan', 'diskon_persen'));
    }

    public function open_pembayaran(Request $request){
        if($request->id == "") {
            $penjualan = new TransaksiPenjualan;
        } else {
            $penjualan = TransaksiPenjualan::find($request->id);
        }
        $karyawans = User::where('is_deleted', 0)->get();
        $harga_total = $request->harga_total;
        $kartu_debets = MasterKartu::where('is_deleted', 0)->get();

        if(empty($harga_total)) {
            $harga_total = 0;
        }


        return view('penjualan._dialog_pembayaran')->with(compact('penjualan', 'harga_total', 'kartu_debets'));
    }

    public function find_ketentuan_keyboard(){
        return view('penjualan._form_ketentuan_keyboard');
    }

    public function edit_detail(Request $request){
        $id = $request->id;
        $no = $request->no;
        $detail = TransaksiPenjualanDetail::find($id);
        return view('penjualan._form_edit_detail')->with(compact('detail', 'no'));
    }

    public function update_penjualan_detail(Request $request, $id) {
        $detail = TransaksiPenjualanDetail::find($id);
        $detail->harga_jual = $request->harga_jual;
        $detail->diskon = $request->diskon;
        $detail->jumlah = $request->jumlah;

        // update ke tabel data pembelian


        if($detail->save()){
            return response()->json(array(
                'submit' => 1,
                'success' => 'Data berhasil disimpan'
            ));
        }
        else{
            return response()->json(array(
                'submit' => 0,
                'error' => 'Data gagal disimpan'
            ));
        }
    }

    public function cetak_tes($id) {
        $penjualan = TransaksiPenjualan::where('id', $id)->first();
        if($penjualan->id_jasa_resep == "" || $penjualan->id_jasa_resep == 0 || $penjualan->id_jasa_resep == null || $penjualan->id_jasa_resep == '0') {
            $penjualan->jasa_resep = 0;
        } else {
            $jasa_resep = MasterJasaResep::find($penjualan->id_jasa_resep);
            $penjualan->jasa_resep = $jasa_resep->biaya;
        }
        $detail_penjualans = TransaksiPenjualanDetail::select(['tb_detail_nota_penjualan.id',
                                                'tb_detail_nota_penjualan.id_nota',
                                                'tb_detail_nota_penjualan.id_obat',
                                                'tb_detail_nota_penjualan.jumlah',
                                                'tb_detail_nota_penjualan.harga_jual',
                                                'tb_detail_nota_penjualan.diskon',
                                                'tb_m_obat.nama',
                                                 DB::raw('(tb_detail_nota_penjualan.jumlah * tb_detail_nota_penjualan.harga_jual) - tb_detail_nota_penjualan.diskon  as total')])
                                               ->join('tb_m_obat', 'tb_m_obat.id', '=', 'tb_detail_nota_penjualan.id_obat')
                                               ->where('tb_detail_nota_penjualan.id_nota', $id)
                                               ->where('tb_detail_nota_penjualan.is_deleted', 0)
                                               ->get();
        $apotek = MasterApotek::find($penjualan->id_apotek_nota);

try {
    //$connector = new WindowsPrintConnector("POS58 Printer");
    /*$device = "/dev/ttyUSB002";
    $baud = 19200;
    $cmd = sprintf("stty -F %s %s", escapeshellarg($device), escapeshellarg($baud));
    exec($cmd);*/

        $connector = new FilePrintConnector('smb://LAPTOP-V1VRB9C4/POS58 Printer');
    /* Print a "Hello world" receipt" */
    $printer = new Printer($connector);
    $printer -> setJustification( Printer::JUSTIFY_CENTER );
    $printer -> text("APOTEK BWF-LAVIE\n");
    $printer -> text("Jl. Kampus Udayana No.18L, Jimbaran\n");
    $printer -> text("Telp. 085 100 766 784\n");
    $printer -> setJustification( Printer::JUSTIFY_LEFT );
    $printer -> text("----------------------------------------\n");
    $printer -> text("Tanggal  : ".date('d-m-Y H:i:s')."\n");
    $printer -> text("----------------------------------------\n");
    $printer -> cut();

    $printer -> close();
        } catch (Exception $e) {
            echo "Couldn't print to this printer: " . $e -> getMessage() . "\n";
        }


        //return view('penjualan._form_cetak_nota')->with(compact('penjualan', 'detail_penjualans', 'apotek'));

       /* $tmpdir = sys_get_temp_dir();   # ambil direktori temporary untuk simpan file.
        print_r($tmpdir);
        $file =  tempnam($tmpdir, 'ctk_nota');  # nama file temporary yang akan dicetak
        $handle = fopen($file, 'w');
        $condensed = Chr(27) . Chr(33) . Chr(4);
        $bold1 = Chr(27) . Chr(69);
        $bold0 = Chr(27) . Chr(70);
        $initialized = chr(27).chr(64);
        $condensed1 = chr(15);
        $condensed0 = chr(18);
        $Data  = $initialized;
        $Data .= $condensed1;

        
        $Data .= "==========================\n";
        $Data .= "|     ".$bold1."OFIDZ MAJEZTY".$bold0."      |\n";
        $Data .= "==========================\n";
        $Data .= "Ofidz Majezty is here\n";
        $Data .= "We Love PHP Indonesia\n";
        $Data .= "We Love PHP Indonesia\n";
        $Data .= "We Love PHP Indonesia\n";
        $Data .= "We Love PHP Indonesia\n";
        $Data .= "We Love PHP Indonesia\n";
        $Data .= "--------------------------\n";
        fwrite($handle, $Data);
        fclose($handle);
        copy($file, "//localhost/EPSON TM-U220 Receipt");  # Lakukan cetak
        unlink($file);
      */
        
        /*try {
            $connector = new WindowsPrintConnector("EPSON TM-U220 Receipt");
            $printer = new Printer($connector);
            $no = 0;

            $nota = TransaksiPenjualan::find($id);

            $active_apotek = session('id_apotek_active');
            if ($active_apotek == 1) {
                $printer -> setJustification( Printer::JUSTIFY_CENTER );
                $printer -> text("APOTEK BWF-LAVIE\n");
                $printer -> text("Jl. Kampus Udayana No.18L, Jimbaran\n");
                $printer -> text("Telp. 085 100 766 784\n");
                $printer -> setJustification( Printer::JUSTIFY_LEFT );
                $printer -> text("----------------------------------------\n");
                $printer -> text("No. Nota : LV-".$nota->id."\n");
                $printer -> text("Tanggal  : ".date('d-m-Y H:i:s')."\n");
                $printer -> text("----------------------------------------\n");
            } else if($active_apotek == 2) {
                $printer -> setJustification( Printer::JUSTIFY_CENTER );
                $printer -> text("APOTEK BWF-BEKUL\n");
                $printer -> text("Jl. Raya Uluwatu, Jimbaran\n");
                $printer -> text("Telp. 085 100 722 626\n");
                $printer -> setJustification( Printer::JUSTIFY_LEFT );
                $printer -> text("----------------------------------------\n");
                $printer -> text("No. Nota : LV-".$nota->id."\n");
                $printer -> text("Tanggal  : ".date('d-m-Y H:i:s')."\n");
                $printer -> text("----------------------------------------\n");
            } else if($active_apotek == 3) {
                $printer -> setJustification( Printer::JUSTIFY_CENTER );
                $printer -> text("APOTEK BWF-PUJA MANDALA\n");
                $printer -> text("Jl. Kurusetra No.31,PJM, Nusa Dua\n");
                $printer -> text("Telp. 085 104 533 579\n");
                $printer -> setJustification( Printer::JUSTIFY_LEFT );
                $printer -> text("----------------------------------------\n");
                $printer -> text("No. Nota : LV-".$nota->id."\n");
                $printer -> text("Tanggal  : ".date('d-m-Y H:i:s')."\n");
                $printer -> text("----------------------------------------\n");
            } else if($active_apotek == 4) {
                $printer -> setJustification( Printer::JUSTIFY_CENTER );
                $printer -> text("APOTEK BWF-PURI GADING\n");
                $printer -> text("Jl. Kampus Udayana No.18L, Jimbaran\n");
                $printer -> text("Telp. 085 100 766 784\n");
                $printer -> setJustification( Printer::JUSTIFY_LEFT );
                $printer -> text("----------------------------------------\n");
                $printer -> text("No. Nota : LV-".$nota->id."\n");
                $printer -> text("Tanggal  : ".date('d-m-Y H:i:s')."\n");
                $printer -> text("----------------------------------------\n");
            }

            $detail_penjualans = $nota->detail_penjualan;
            $total_belanja = 0;
            foreach ($detail_penjualans as $key => $val) {
                $no++;
                $total_1 = $val['jumlah'] * $val['harga_jual'];
                $total_2 = $total_1 - $val['diskon'];
                $total_belanja = $total_belanja + $total_2;
                $obat = $val->obat;
                $printer -> setJustification( Printer::JUSTIFY_LEFT );
                $printer -> text($no.".");
                $printer -> text($obat['nama']."\n");
                $printer -> text("     ".$val['jumlah']."X".number_format($val['harga_jual'],0,',',',')." (-".number_format($val['diskon'],0,',',',').")"." = Rp ".number_format($total_2,0,',',',')."\n");
            }

            $total_diskon_persen = $nota['diskon_persen']/100 * $total_belanja;
            $total_belanja_bayar = $total_belanja - ($total_diskon_persen + $nota['diskon_rp']);

            $printer -> setJustification( Printer::JUSTIFY_LEFT );
            $total_diskon = $total_diskon_persen+$nota['diskon_rp'];
            $printer -> text("----------------------------------------\n");
            $total_belanja = $total_belanja+$nota->biaya_jasa_dokter;
                $printer -> text("Jasa Dokter      :    Rp ".number_format($nota->biaya_jasa_dokter,0,',',',')."\n");
            if($nota->id_jasa_resep != '') {
                $printer -> text("Jasa Resep       :    Rp ".number_format($nota->jasa_resep->biaya,0,',',',')."\n");
            $total_belanja = $total_belanja+$nota->jasa_resep->biaya;
            } else {
            $printer -> text("Jasa Resep       :    Rp 0"."\n");
            }
            $printer -> text("----------------------------------------\n");
            $printer -> text("Total            :    Rp ".number_format($total_belanja,0,',',',')."\n");
            $printer -> text("Diskon           :    Rp ".number_format($total_diskon,0,',',',')."\n");
            //$printer -> text("Diskon           :    ".$nota['diskon_persen']."% (Rp ".number_format($total_diskon_persen,0,',',',').")\n");
            //$printer -> text("Diskon Nota (Rp) :    Rp ".number_format($nota['diskon_rp'],0,',',',')."\n");
            //$printer -> text("Total II         :    Rp ".number_format($total_belanja_bayar,0,',',',')."\n");
            
            // ini jika bayar dengan debet
            $debet = 0;
            if(!empty($penjualan->id_kartu_debet_credit)) {
                $printer -> text("Debet/Credit     :    Rp ".number_format($nota->debet,0,',',',')."\n");
            } else {
                $debet = $nota->debet;
            }
 
            $total_bayar = $debet+$nota->cash;

            if($total_bayar == 0) {
                $total_bayar = $total_belanja+$nota->kembalian;
            }

            //$printer -> text("Bayar            :    Rp ".number_format($nota->cash,0,',',',')."\n");
            $printer -> text("Bayar            :    Rp ".number_format($total_bayar,0,',',',')."\n");
            $printer -> text("Kembalian        :    Rp ".number_format($nota->kembalian,0,',',',')."\n");
            $printer -> text("----------------------------------------\n");
            $printer -> setJustification( Printer::JUSTIFY_CENTER );
            $printer -> text("Terimakasih Atas Kunjuangan Anda\n");
            $printer -> text("Semoga Lekas Sembuh\n");
            $printer -> cut();
            
            $printer -> close();
        } catch (Exception $e) {
            echo "Couldn't print to this printer: " . $e -> getMessage() . "\n";
        }*/
    }

    public function cetak_nota($id)
    {   
        $penjualan = TransaksiPenjualan::where('id', $id)->first();
        if($penjualan->id_jasa_resep == "" || $penjualan->id_jasa_resep == 0 || $penjualan->id_jasa_resep == null || $penjualan->id_jasa_resep == '0') {
            $penjualan->jasa_resep = 0;
        } else {
            $jasa_resep = MasterJasaResep::find($penjualan->id_jasa_resep);
            $penjualan->jasa_resep = $jasa_resep->biaya;
        }
        $detail_penjualans = TransaksiPenjualanDetail::select(['tb_detail_nota_penjualan.id',
                                                'tb_detail_nota_penjualan.id_nota',
                                                'tb_detail_nota_penjualan.id_obat',
                                                DB::raw('(tb_detail_nota_penjualan.jumlah-tb_detail_nota_penjualan.jumlah_cn) as jumlah'),
                                                'tb_detail_nota_penjualan.harga_jual',
                                                'tb_detail_nota_penjualan.diskon',
                                                'tb_m_obat.nama',
                                                 DB::raw('((tb_detail_nota_penjualan.jumlah-tb_detail_nota_penjualan.jumlah_cn) * tb_detail_nota_penjualan.harga_jual) - tb_detail_nota_penjualan.diskon  as total')])
                                               ->join('tb_m_obat', 'tb_m_obat.id', '=', 'tb_detail_nota_penjualan.id_obat')
                                               ->where('tb_detail_nota_penjualan.id_nota', $id)
                                               ->where('tb_detail_nota_penjualan.is_deleted', 0)
                                               ->get();
        $apotek = MasterApotek::find($penjualan->id_apotek_nota);

        $debet = 0;
        if(!empty($penjualan->id_kartu_debet_credit)) {
            $debet = $penjualan->debet;
        } 
        $total_bayar = $debet+$penjualan->cash;

        $id_printer_active = session('id_printer_active');
        if(is_null($id_printer_active)) {
            session(['id_printer_active' => $apotek->id_printer]);
            $id_printer_active = session('id_printer_active');
        }

        if($id_printer_active == 1) {
            return view('penjualan._form_cetak_nota')->with(compact('penjualan', 'detail_penjualans', 'apotek'));
        } else {
            return view('penjualan._form_cetak_nota2')->with(compact('penjualan', 'detail_penjualans', 'apotek'));
        }
    } 


    public function cetak_retur($id)
    {   
        $detail_penjualan = TransaksiPenjualanDetail::find($id);
        $penjualan = TransaksiPenjualan::where('id', $detail_penjualan->id_nota)->first();
        if($penjualan->id_jasa_resep == "" || $penjualan->id_jasa_resep == 0 || $penjualan->id_jasa_resep == null || $penjualan->id_jasa_resep == '0') {
            $penjualan->jasa_resep = 0;
        } else {
            $jasa_resep = MasterJasaResep::find($penjualan->id_jasa_resep);
            $penjualan->jasa_resep = $jasa_resep->biaya;
        }
        $apotek = MasterApotek::find($penjualan->id_apotek_nota);

        return view('penjualan._form_cetak_retur')->with(compact('penjualan', 'detail_penjualan', 'apotek'));
    } 

    public function histori() {
        $tanggal = date('Y-m-d H:i:s');
        $jam = date('H:i:s');
        if(Auth::user()->is_admin == 1) {
            $apoteks = MasterApotek::where('is_deleted', 0)->get();
        } else {
            $apoteks = MasterApotek::where('is_deleted', 0)->where('id', session('id_apotek_active'))->get();
        }
        $pasiens = MasterMember::where('is_deleted', 0)->get();
        $users = User::where('is_deleted', 0)->get();
        return view('histori.penjualan')->with(compact('pasiens', 'apoteks', 'tanggal', 'jam', 'users'));
    }

    public function list_histori(Request $request)
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

        $last_so = SettingStokOpnam::where('id_apotek', session('id_apotek_active'))->orderBy('id', 'DESC')->first();

        $tanggal = date('Y-m-d');
        DB::statement(DB::raw('set @rownum = 0'));
        $data = TransaksiPenjualan::select([
                DB::raw('@rownum  := @rownum  + 1 AS no'),
                'tb_nota_penjualan.*', 
        ])
        ->where(function($query) use($request, $tanggal){
            $query->where('tb_nota_penjualan.is_deleted','=','0');
            if($request->id_apotek != '') {
                $query->where('tb_nota_penjualan.id_apotek_nota', $request->id_apotek);
            } else {
                $query->where('tb_nota_penjualan.id_apotek_nota','=',session('id_apotek_active'));
            }

            if(empty($request->tanggal)) {
                $query->where('tb_nota_penjualan.created_at', 'LIKE', '%'.$tanggal.'%');
            } else {
                $query->where('tb_nota_penjualan.id','LIKE',($request->id > 0 ? $request->id : '%'.$request->id.'%'));
                //$query->where('tb_nota_penjualan.id_pasien','LIKE',($request->id_pasien > 0 ? $request->id_pasien : '%'.$request->id_pasien.'%'));
                if($request->tanggal != "") {
                    $split                      = explode("-", $request->tanggal);
                    $tgl_awal       = date('Y-m-d H:i:s',strtotime($split[0]));
                    $tgl_akhir      = date('Y-m-d H:i:s',strtotime($split[1]));
                    $query->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal);
                    $query->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir);
                }
            }

            if($request->id_user != '') {
                $query->where('tb_nota_penjualan.created_by', $request->id_user);
            }
        })
        ->orderBy('tb_nota_penjualan.id', 'DESC');
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request){
            $query->where(function($query) use($request){
                $query->orwhere('tb_nota_penjualan.id','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->editcolumn('created_at', function($data) use($request){
            $string = '';
            if($data->is_penjualan_tolak_cn == 1) {
                $string .='<br><small class="text-red">Penambahan dari penolakan retur</small>';
            }
            
            if($data->keterangan != "" OR $data->keterangan != null) {
                $keterangan = '('.$data->keterangan.')';
            } else {
                $keterangan = '';
            }
            
            if($data->is_kredit == 1) {
                return Carbon::parse($data->created_at)->format('d/m/Y H:i:s').$string.'<br><small><b>'.$data->vendor->nama.'</b>('.$keterangan.')</small>';
            } else {
                return Carbon::parse($data->created_at)->format('d/m/Y H:i:s').$string;
            }
        })
        ->editcolumn('created_by', function($data) {
            return '<small>'.$data->created_oleh->nama.'</small>';
        })
        ->editcolumn('total_belanja', function($data) use($request){
            $total = $data->detail_penjualan_total[0]->total;
            if($total == "" || $total == null) {
                $total = 0;
            }
            $string = '';
            if($data->cek_retur[0]->total_cn != 0) {
                $string = '<small><b style="color:red;">Ada retur</b></small>';
            }
            return "Rp ".number_format($total,2)."<br>".$string;
        })   
        ->editcolumn('id_jasa_resep', function($data) use($request){
            return "Rp ".number_format($data->biaya_resep,2).'/'."Rp ".number_format($data->biaya_jasa_dokter,2).'/'."Rp ".number_format($data->harga_wd,2).'/'."Rp ".number_format($data->biaya_lab,2).'/'."Rp ".number_format($data->biaya_apd,2);
        })   
        ->editcolumn('total_fix', function($data) use($request){
            $total = $data->detail_penjualan_total[0]->total;
            if($total == "" || $total == null) {
                $total = 0;
            }

            $total_diskon = $data->detail_penjualan_total[0]->total_diskon;
            if($total_diskon == "" || $total_diskon == null) {
                $total_diskon = 0;
            }
            $total = $total - $total_diskon;
            $diskon = $data->diskon_persen/100*$total;
            $x = $diskon+$total_diskon;
            $total_fix = ($total+$data->biaya_resep+$data->biaya_jasa_dokter+$data->harga_wd+$data->biaya_lab+$data->biaya_apd)-$diskon;

            $str_new_ = "<span style='font-size:8pt;' class='text-info'>";
            if($data->id_kartu_debet_credit != "" AND $data->id_kartu_debet_credit != 0) {
                $str_new_ .= "|D/C (".$data->kartu->nama."): Rp ".number_format($data->debet,0);
            } 
            if($data->cash != "" OR $data->cash != 0) {
                $str_new_ .= "|Cash : Rp ".number_format($data->cash,0);
            }
            //$str_new_ .= "|Kembalian : Rp ".number_format($data->kembalian,0);
            $str_new_ .= "|</span>";
            
            return "("."Rp ".number_format($x,2).")"."<br><b>"."Rp ".number_format($total_fix,2).'</b><br>'.$str_new_;
        })   
        ->editcolumn('is_kredit', function($data) use($request){
           
            if($data->is_kredit == 1) {
                $string = "K";
            } else {
                $string = "N";
            }
           
            return $string;
        })   
        ->addcolumn('action', function($data) use ($hak_akses, $last_so){
            $btn = '<div class="btn-group">';
            //$btn .= '<span class="btn btn-primary btn-sm" onClick="cetak_nota('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Cetak Nota"><i class="fa fa-print"></i> Cetak</span>';
            if(Auth::user()->is_admin == 1) {
                $btn .= '<span class="btn btn-primary btn-sm" onClick="pelunasan('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Cetak Nota"><i class="fa fa-clipboard-check"></i> Pelunasan</span>';
            }
            $btn .= '<a href="'.url('/penjualan/'.$data->id.'/edit').'" title="Detail Data" class="btn btn-info btn-sm"><span data-toggle="tooltip" data-placement="top" title="Detail Data"><i class="fa fa-edit"></i> Detail</span></a>';
            $btn .= '<a href="'.url('/penjualan/cetak_nota/'.$data->id).'" title="Cetak Nota" target="_blank"  class="btn btn-primary btn-sm"><span data-toggle="tooltip" data-placement="top" title="Cetak Nota"><i class="fa fa-print"></i> Cetak</span></a>';
            //$btn .= '<a href="'.url('/penjualan/'.$data->id.'/edit').'" title="Edit Data" class="btn btn-info btn-sm"><span data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i> Edit</span></a>';
            //$btn .= '<span class="btn btn-danger btn-sm" onClick="delete_penjualan('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i> Hapus</span>';
            if($data->is_kredit == 1) {
                $btn .= '<a href="'.url('/penjualan/invoice/'.Crypt::encrypt($data->id)).'" target="_blank" title="Invoice" class="btn btn-secondary btn-sm"><span data-toggle="tooltip" data-placement="top" title="Invoice"><i class="fa fa-file-pdf"></i> Invoice</span></a>';
            }
            if(Auth::user()->is_admin == 1) {
                if($data->cek_retur[0]->total_cn == 0) {
                    $btn .= '<span class="btn btn-danger btn-sm" onClick="delete_penjualan('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i> Hapus</span>';
                }
            } else {
                if(!empty($last_so)) {
                    if($data->tgl_nota > $last_so->tgl_so) {
                        if($hak_akses == 1) {
                            if($data->cek_retur[0]->total_cn == 0) {
                                $btn .= '<span class="btn btn-danger btn-sm" onClick="delete_penjualan('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i> Hapus</span>';
                            }
                        }
                    }
                } else {
                    if($hak_akses == 1) {
                        if($data->cek_retur[0]->total_cn == 0) {
                            $btn .= '<span class="btn btn-danger btn-sm" onClick="delete_penjualan('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i> Hapus</span>';
                        }
                    }
                }
            }
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['action', 'created_by', 'total_fix', 'total_belanja', 'created_at'])
        ->addIndexColumn()
        ->make(true);  
    }

    public function list_histori_back(Request $request)
    {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];
        $tgl_penjualan = $request->tgl_penjualan;
        $split           = explode(" - ", $tgl_penjualan);
        $date1 = strtr($split[0], '/', '-');
        $date2 = strtr($split[1], '/', '-');
        $date1 = date('Y-m-d', strtotime($date1));
        $date2 = date('Y-m-d', strtotime($date2));

        $tanggal = date('Y-m-d');
        DB::statement(DB::raw('set @rownum = 0'));
        $data = TransaksiPenjualanDetail::select([
                DB::raw('@rownum  := @rownum  + 1 AS no'),
                'tb_nota_penjualan.*', 
                DB::raw('SUM(tb_detail_nota_penjualan.jumlah * tb_detail_nota_penjualan.harga_jual - tb_detail_nota_penjualan.diskon) AS total')
        ])
        ->join('tb_nota_penjualan', 'tb_nota_penjualan.id', '=', 'tb_detail_nota_penjualan.id_nota')
        ->join('tb_m_obat', 'tb_m_obat.id', '=', 'tb_detail_nota_penjualan.id_obat')
        ->where(function($query) use($request, $date1, $date2){
            $query->where('tb_nota_penjualan.is_deleted','=','0');
            $query->where('tb_detail_nota_penjualan.is_deleted','=','0');
            $query->where('tb_nota_penjualan.id_apotek_nota','=',session('id_apotek_active'));
            $query->where('tb_nota_penjualan.tgl_nota', '>=', $date1);
            $query->where('tb_nota_penjualan.tgl_nota', '<=', $date2);
            $query->orwhere('tb_m_obat.barcode','LIKE','%'.$request->nama_obat.'%');
            $query->orwhere('tb_m_obat.sku','LIKE','%'.$request->nama_obat.'%');
            $query->orwhere('tb_m_obat.nama','LIKE','%'.$request->nama_obat.'%');
        })
        ->groupBy('tb_nota_penjualan.id');
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->orwhere('tb_nota_penjualan.id','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->editcolumn('created_at', function($data) use($request){
            return Carbon::parse($data->created_at)->format('d/m/Y H:i:s');
        })
        ->editcolumn('total', function($data) use($request){
            $total = $data->total;
            if($total == "" || $total == null) {
                $total = 0;
            }
            return "Rp ".number_format($total,2);
        })   
        ->editcolumn('biaya_jasa_dokter', function($data) use($request){
            return "Rp ".number_format($data->biaya_resep,2).'/'."Rp ".number_format($data->biaya_jasa_dokter,2);
        })   
        ->editcolumn('total_fix', function($data) use($request){
            $total = $data->total;
            if($total == "" || $total == null) {
                $total = 0;
            }
            $total_fix = $total+$data->biaya+$data->biaya_jasa_dokter;
            return "Rp ".number_format($total_fix,2);
        })   
        ->editcolumn('created_by', function($data) {
            return '<small>'.$data->created_oleh->nama.'</small>';
        })
        ->editcolumn('is_kredit', function($data) use($request){
           
            if($data->is_kredit == 1) {
                $string = "K";
            } else {
                $string = "N";
            }
           
            return $string;
        })   
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .= '<a href="'.url('/penjualan/'.$data->id.'/edit').'" title="Detail Data" class="btn btn-info btn-sm"><span data-toggle="tooltip" data-placement="top" title="Detail Data"><i class="fa fa-edit"></i> Detail</span></a>';
            /*$btn .= '<span class="btn btn-primary btn-sm" onClick="cetak_nota('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Cetak Nota"><i class="fa fa-print"></i> Cetak</span>';*/
            $btn .= '<a href="'.url('/penjualan/cetak_nota/'.$data->id).'" title="Cetak Nota" target="_blank"  class="btn btn-primary btn-sm"><span data-toggle="tooltip" data-placement="top" title="Cetak Nota"><i class="fa fa-print"></i> Cetak</span></a>';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['action', 'created_by'])
        ->addIndexColumn()
        ->make(true);  
    }

    public function detail($id)
    {
        $penjualan = TransaksiPenjualan::find($id);
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $detail_penjualans = $penjualan->detail_penjualan;
        $var = 3;
        $tanggal = date('Y-m-d');
        $is_kredit = $penjualan->is_kredit;

        $members = MasterMember::where('is_deleted', 0)->pluck('nama', 'id');
        $members->prepend('-- Pilih Member --','');

        $apoteker = User::find($apotek->id_apoteker);
        $id_user = Auth::user()->id;
        $hak_akses = 0;
        if($apoteker->id == $id_user) {
            $hak_akses = 1;
        }

        if($id_user == 1 || $id_user == 2 || $id_user == 16) {
            $hak_akses = 1;
        }

        $hak_akses_margin = $hak_akses;

        $vendor_kerjama = MasterVendor::where('is_deleted', 0)->get();

        return view('penjualan.detail')->with(compact('penjualan', 'tanggal', 'detail_penjualans', 'var', 'is_kredit', 'inisial', 'apotek', 'members', 'hak_akses', 'vendor_kerjama', 'hak_akses_margin'));
    }

    public function create_credit() {
        $penjualan = new TransaksiPenjualan;
        $detail_penjualans = new TransaksiPenjualanDetail;
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $tanggal = date('Y-m-d');
        $var = 1;

        $members = MasterMember::where('is_deleted', 0)->pluck('nama', 'id');
        $members->prepend('-- Pilih Member --','');

        $is_kredit = 1;
        $is_margin = 0;
        $vendor_kerjama = MasterVendor::where('is_deleted', 0)->get();

        $hak_akses = 1;

        $apoteker = User::find($apotek->id_apoteker);
        $id_user = Auth::user()->id;

        $hak_akses_margin = 0;
        if($apoteker->id == $id_user) {
            $hak_akses_margin = 1;
        }

        if(Auth::user()->is_admin == 1) {
            $hak_akses_margin = 1;
        }

        return view('penjualan.create_kredit')->with(compact('penjualan', 'tanggal', 'detail_penjualans', 'var', 'is_kredit', 'inisial', 'apotek', 'vendor_kerjama', 'members', 'hak_akses', 'is_margin', 'hak_akses_margin'));
    }

    public function kredit() {
        $vendor_kerjamas      = MasterVendor::where('is_deleted', 0)->pluck('nama', 'id');
        $vendor_kerjamas->prepend('-- Pilih Vendor --','');

        return view('histori.penjualan_kredit')->with(compact('vendor_kerjamas'));
    }

    public function list_kredit(Request $request)
    {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];
        $tgl_penjualan = $request->tgl_penjualan;
        $split           = explode(" - ", $tgl_penjualan);
        $date1 = strtr($split[0], '/', '-');
        $date2 = strtr($split[1], '/', '-');
        $date1 = date('Y-m-d', strtotime($date1));
        $date2 = date('Y-m-d', strtotime($date2));

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
        $data = TransaksiPenjualan::select([
                DB::raw('@rownum  := @rownum  + 1 AS no'),
                'tb_nota_penjualan.*', 
        ])
        ->where(function($query) use($request, $date1, $date2){
            $query->where('tb_nota_penjualan.is_deleted','=','0');
            $query->where('tb_nota_penjualan.is_kredit','=','1');
            $query->where('tb_nota_penjualan.id_apotek_nota','=',session('id_apotek_active'));
            if($request->id_vendor != '') {
                $query->where('tb_nota_penjualan.id_vendor', $request->id_vendor);
            }
            $query->where('tb_nota_penjualan.tgl_nota', '>=', $date1);
            $query->where('tb_nota_penjualan.tgl_nota', '<=', $date2);
            if($request->keterangan != '') {
                $query->where('tb_nota_penjualan.keterangan','LIKE','%'.$request->keterangan.'%');
            }
            if($request->is_lunas_pembayaran_kredit != '') {
                $query->where('tb_nota_penjualan.is_lunas_pembayaran_kredit','=',$request->is_lunas_pembayaran_kredit);
            }
        })
        ->groupBy('tb_nota_penjualan.id');
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->orwhere('tb_nota_penjualan.id','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->editcolumn('created_at', function($data) use($request){
            if($data->keterangan != "" OR $data->keterangan != null) {
                $keterangan = '('.$data->keterangan.')';
            } else {
                $keterangan = '';
            }
            return Carbon::parse($data->created_at)->format('d/m/Y H:i:s').'<br><small><b>'.$data->vendor->nama.'</b>('.$keterangan.')</small>';
        })
        ->editcolumn('total', function($data) use($request){
            $total = $data->detail_penjualan_total[0]->total;
            if($total == "" || $total == null) {
                $total = 0;
            }
            $string = '';
            if($data->cek_retur[0]->total_cn != 0) {
                $string = '<small><b style="color:red;">Ada retur</b></small>';
            }
            return "Rp ".number_format($total,2)."<br>".$string;
        })   
        ->editcolumn('debet', function($data) use($request){
            return "Rp ".number_format($data->debet,2);
        })   
        ->editcolumn('biaya_jasa_dokter', function($data) use($request){
            return "Rp ".number_format($data->biaya_resep,2).'/'."Rp ".number_format($data->biaya_jasa_dokter,2);
        })   
        ->editcolumn('total_fix', function($data) use($request){
            $total = $data->detail_penjualan_total[0]->total;
            $diskon = 0;
            if($data->diskon_persen != "" && $data->diskon_persen != null) {
                $diskon = $data->diskon_persen/100*$total;
                //$diskon = "Rp ".number_format($diskon,2);
            }

            $diskon_vendor = 0;
            if($data->diskon_vendor != "" && $data->diskon_vendor != null) {
                $diskon_vendor = $data->diskon_vendor/100*$total;
               // $diskon_vendor = "Rp ".number_format($diskon_vendor,2);
            }
            if($total == "" || $total == null) {
                $total = 0;
            }

            $total_diskon = $diskon+$diskon_vendor;
            $total_fix = ($total+$data->biaya_resep+$data->biaya_jasa_dokter+$data->harga_wd+$data->biaya_lab+$data->biaya_apd)-$total_diskon;
            return "("."Rp ".number_format($total_diskon,2).")"."<br><b>"."Rp ".number_format($total_fix,2).'</b>';
        })   
        ->editcolumn('is_lunas_pembayaran_kredit', function($data) use($request){
            if($data->is_lunas_pembayaran_kredit == 1) {
                $oleh = $data->lunas_oleh->nama;
                $tgl = Carbon::parse($data->is_lunas_pembayaran_kredit_at)->format('d/m/Y H:i:s');
                $string = '<span class="right badge badge-info">Lunas</span><br><small>'.$oleh.'|'.$tgl.'</small>';
            } else {
                $string = '<span class="right badge badge-danger">Belum Lunas</span>';
            }
           
            return $string;
        })   
        ->editcolumn('created_by', function($data) {
            return '<small>'.$data->created_oleh->nama.'</small>';
        })
        ->addcolumn('action', function($data) use($hak_akses){
            $btn = '<div class="btn-group">';
            //if($data->cek_retur[0]->total_cn != 0) {
                $btn .= '<span class="btn btn-primary btn-sm" onClick="pembayaran_kredit('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Cetak Nota"><i class="fa fa-clipboard-check"></i> Pelunasan</span>';
                $btn .= '<a href="'.url('/penjualan/cetak_nota/'.$data->id).'" title="Cetak Nota" target="_blank"  class="btn btn-secondary btn-sm"><span data-toggle="tooltip" data-placement="top" title="Cetak Nota"><i class="fa fa-print"></i> Cetak</span></a>';
            ///}
            $btn .= '<a href="'.url('/penjualan/detail/'.$data->id).'" title="Lihat detail penjualan" class="btn btn-info btn-sm"><span data-toggle="tooltip" data-placement="top" title="Lihat detail penjualan"><i class="fa fa-eye"></i> Detail</span></a>';
            $btn .= '<a href="'.url('/penjualan/invoice/'.Crypt::encrypt($data->id)).'" target="_blank" title="Invoice" class="btn btn-secondary btn-sm"><span data-toggle="tooltip" data-placement="top" title="Invoice"><i class="fa fa-file-pdf"></i> Invoice</span></a>';

            if($data->total_bayar != 0 AND !is_null($data->total_bayar)) {
                # jika sudah dibayar
                if($hak_akses == 1) {
                    # jika admin
                    if($data->cek_retur[0]->total_cn == 0) {
                        $btn .= '<span class="btn btn-danger btn-sm" onClick="delete_penjualan('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i> Hapus</span>';
                    }
                } /*else {
                    $btn .= '<span class="btn btn-danger btn-sm" onClick="delete_penjualan('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i> Hapus</span>';
                }*/
            } else {
                # jika belum dibayar
                if($data->cek_retur[0]->total_cn == 0) {
                    $btn .= '<span class="btn btn-danger btn-sm" onClick="delete_penjualan('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i> Hapus</span>';
                }
               
            }

            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['created_by', 'action', 'created_at', 'is_lunas_pembayaran_kredit', 'total', 'total_fix'])
        ->addIndexColumn()
        ->make(true);
    }

    public function pembayaran_kredit($id)
    {   
        $penjualan = TransaksiPenjualan::where('id', $id)->first();
        if($penjualan->id_jasa_resep == "" || $penjualan->id_jasa_resep == 0 || $penjualan->id_jasa_resep == null || $penjualan->id_jasa_resep == '0') {
            $penjualan->jasa_resep = 0;
        } else {
            $jasa_resep = MasterJasaResep::find($penjualan->id_jasa_resep);
            $penjualan->jasa_resep = $jasa_resep->biaya;
        }
        $detail_penjualans = TransaksiPenjualanDetail::select(['tb_detail_nota_penjualan.id',
                                                'tb_detail_nota_penjualan.id_nota',
                                                'tb_detail_nota_penjualan.id_obat',
                                                'tb_detail_nota_penjualan.jumlah',
                                                'tb_detail_nota_penjualan.jumlah_cn',
                                                'tb_detail_nota_penjualan.harga_jual',
                                                'tb_detail_nota_penjualan.diskon',
                                                'tb_m_obat.nama',
                                                 DB::raw('(tb_detail_nota_penjualan.jumlah * tb_detail_nota_penjualan.harga_jual) - tb_detail_nota_penjualan.diskon  as total')])
                                               ->join('tb_m_obat', 'tb_m_obat.id', '=', 'tb_detail_nota_penjualan.id_obat')
                                               ->where('tb_detail_nota_penjualan.id_nota', $id)
                                               ->where('tb_detail_nota_penjualan.is_deleted', 0)
                                               ->get();

        $kartu_debets = MasterKartu::where('is_deleted', 0)->get();

        $apotek = MasterApotek::find(session('id_apotek_active'));

        /*echo $penjualan->diskon_persen;exit()*/

        return view('histori._form_pembayaran_kredit')->with(compact('penjualan', 'detail_penjualans', 'kartu_debets', 'apotek'));
    }

    public function update_pembayaran_kredit(Request $request, $id) {
        $cash = $request->cash_value;
        $kembalian = $request->kembalian_value;
        $diskon_persen = $request->diskon_persen;
        $no_kartu = $request->no_kartu_input;
        $debet = $request->debet_input;
        $surcharge = $request->surcharge_input;
        $harga_belanja_awal = $request->total_belanja;
        $cash = $request->cash_value;
        $total = $request->total_debet_input;
        $id_kartu_debet_credit_input = $request->id_kartu_debet_credit_input;
        $total_charge = (floatval($surcharge)/100 * floatval($debet));
        $total_bayar = $request->total_bayar_input;
        
        if($no_kartu == 0 || is_numeric($no_kartu)) {
            if($harga_belanja_awal == "" || $harga_belanja_awal == 0) {
                echo json_encode(array('status' =>5));
            } else {
                if($kembalian >= 0) {
                    $penjualan = TransaksiPenjualan::find($id);
                    $penjualan->cash = $cash;
                    $penjualan->id_kartu_debet_credit = $id_kartu_debet_credit_input;
                    $penjualan->no_kartu = $no_kartu;
                    $penjualan->debet = $total;
                    $penjualan->surcharge = $surcharge;  
                    $penjualan->total_belanja = $harga_belanja_awal;   
                    $penjualan->diskon_persen = $diskon_persen;    
                    $penjualan->total_bayar = $total_bayar;
                    $penjualan->kembalian = $kembalian;
                    $penjualan->created_at = $penjualan->created_at;
                    $penjualan->updated_at = date('Y-m-d H:i:s');
                    if($penjualan->is_kredit == 1) {
                        $penjualan->is_lunas_pembayaran_kredit = 1;
                        $penjualan->is_lunas_pembayaran_kredit_at = date('Y-m-d H:i:s');
                        $penjualan->is_lunas_pembayaran_kredit_by = Auth::user()->id;
                    }

                    if($id_kartu_debet_credit_input != 0 && $kembalian > 0) {
                        return json_encode(array('status' => 2));
                    } else {
                        $penjualan->timestamps = false;
                        $penjualan->save();
                        return json_encode(array('status' => 1));
                    }
                } else {
                    return json_encode(array('status' =>3));
                }
            }
        } else {
            return json_encode(array('status' =>4));
        }        
    }

    public function retur_save(Request $request) {
        $details = $request->id_detail;
        $i = 0;

        $apotek = MasterApotek::find(session('id_apotek_active'));
        $apoteker = User::find($apotek->id_apoteker);
        $apoteker = $apoteker->toArray();
        //$email = $apoteker->email;
        $email = $apoteker['email'];
        $id_nota = '';
        $array_ = array();
        foreach ($details as $key => $val) {
            $new_ = array();
            $det_ = TransaksiPenjualanDetail::find($val);
            $det_->is_cn = 1;
            $det_->is_cn = 1;
            $det_->cn_at = date('Y-m-d H:i:s');
            $det_->cn_by = Auth::user()->id;
            
            if($det_->save()) {
                PenjualanRetur::dispatch($det_);
                $new_ = $det_->toArray();
                $new_['nama_obat'] = $det_->obat->nama;
                $id_nota = $det_->id_nota;
                $array_[] = $new_;
                $i++;
            } 
        }

        $penjualan = TransaksiPenjualan::find($id_nota);
        $penjualan->created_at = Carbon::parse($penjualan->created_at)->format('d-m-Y H:i:s');
        $penjualan = $penjualan->toArray();
        $user = User::find(Auth::user()->id);
        $user = $user->toArray();
        $tanggal = date('d-m-Y H:i:s');

        $data = array(['detail' => $array_, 'apoteker' => $apoteker, 'user' => $user, 'penjualan' => $penjualan, 'tanggal' => $tanggal]);
        if($i > 0) {
            # kirim email ke ka outlet
            Mail::to($email)->send(new \App\Mail\MailPenjualanRetur($data));
            return response()->json(array(
                'submit' => 1,
            ));
        }
        else{
            return response()->json(array(
                'submit' => 0,
            ));
        }
    } 

    public function retur_item(Request $request) {
        $details = explode(",", $request->input('id_detail'));
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);

        $detail_penjualans = TransaksiPenjualanDetail::whereIn('id', $details)->get();
        $id_nota = $detail_penjualans[0]->id_nota;
        $penjualan = TransaksiPenjualan::find($id_nota);
        $penjualan->created_at = Carbon::parse($penjualan->created_at)->format('d-m-Y H:i:s');

        return view('retur.retur_penjulan')->with(compact('detail_penjualans', 'penjualan', 'apotek', 'inisial'));
    }

    public function set_jumlah_retur(Request $request) {
        $no = $request->no;
        $detail_penjualan = TransaksiPenjualanDetail::find($request->id);

        $alasan_returs = MasterAlasanRetur::where('is_deleted', 0)->pluck('alasan', 'id');
        $alasan_returs->prepend('-- Pilih Alasan Retur --','');

        return view('penjualan._form_set_jumlah_retur')->with(compact('detail_penjualan', 'no', 'alasan_returs'));
    }

    public function update_retur(Request $request, $id) {
        DB::beginTransaction(); 
        try{
            if($request->jumlah_cn > 0) {
                $array_ = array();
                $i = 0;
                $obj = TransaksiPenjualanDetail::find($id);
                $penjualan = TransaksiPenjualan::find($obj->id_nota);
                $apotek = MasterApotek::find(session('id_apotek_active'));
                $apoteker = User::find($apotek->id_apoteker);
                $apoteker = $apoteker->toArray();
                $email = $apoteker['email'];
                //$email = 'sriutami821@gmail.com';
                
                $user = User::find(Auth::user()->id);
                $user = $user->toArray();
                $tanggal = date('d-m-Y H:i:s');

                if($request->jumlah_cn <= $obj->jumlah) {
                    $obj->is_cn = 1;
                    $obj->cn_at = date('Y-m-d H:i:s');
                    $obj->cn_by = Auth::user()->id;
                    $obj->jumlah_cn = $request->jumlah_cn;
                    $obj->id_alasan_retur = $request->id_alasan_retur;
                    $obj->alasan_lain = $request->alasan_lain;

                    // buat histori  retur
                    /*$retur_penjulan = ReturPenjualan::where('id_detail_nota', $obj->id)->where('is_deleted', 0)->first();
                    if(empty($retur_penjulan)) {
                        $retur_penjulan = new ReturPenjualan;    
                    }

                    $retur_penjulan->id_detail_nota = $obj->id;
                    $retur_penjulan->id_alasan_retur = $request->id_alasan_retur;
                    $retur_penjulan->alasan_lain = $request->alasan_lain;
                    $retur_penjulan->jumlah_cn = $request->jumlah_cn;
                    $retur_penjulan->created_at = date('Y-m-d H:i:s');
                    $retur_penjulan->created_by = Auth::user()->id;

                    if($retur_penjulan->save()){
                    } else {
                        DB::rollback();
                        echo json_encode(array('status' => 0, 'message' => 'Gagal menyimpan data!'));
                    }
                    $obj->id_retur_penjualan = $retur_penjulan->id;*/

                    if($obj->save()) {
                        DB::commit();

                        $new_ = $obj->toArray();
                        $new_['nama_obat'] = $obj->obat->nama;
                        $array_[] = $new_;
                        $i++;

                        $tgl_penjualan = Carbon::parse($penjualan->created_at)->format('d-m-Y H:i:s');
                        $penjualan = $penjualan->toArray();

                        $data = array(['detail' => $array_, 'apoteker' => $apoteker, 'user' => $user, 'penjualan' => $penjualan, 'tanggal' => $tanggal, 'tgl_penjualan' => $tgl_penjualan]);

                        Mail::to($email)->send(new \App\Mail\MailPenjualanRetur($data));
                        echo json_encode(array('status' => 1, 'message' => 'Sukses menyimpan data!'));
                    }
                } else {
                    DB::rollback();
                    echo json_encode(array('status' => 0, 'message' => 'Gagal menyimpan data! Pastikan jumlah retur tidak melebihi jumlah penjualan!'));
                }
            } else {
                DB::rollback();
                echo json_encode(array('status' => 0, 'message' => 'Gagal menyimpan data! Pastikan jumlah retur telah terisi lebih dari 0!'));
            }
        }catch(\Exception $e){
            DB::rollback();
            echo json_encode(array('status' => 0, 'message' => $e->getMessage()));
        }
    }

    public function list_penjualan_retur(Request $request)
    {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        $apotek = MasterApotek::find(session('id_apotek_active'));
        $apoteker = User::find($apotek->id_apoteker);
        $id_user = Auth::user()->id;
        $hak_akses = 0;
        if($apoteker->id == $id_user) {
            $hak_akses = 1;
        }

        DB::statement(DB::raw('set @rownum = 0'));
        $data = TransaksiPenjualanDetail::select([
                DB::raw('@rownum  := @rownum  + 1 AS no'),
                'tb_detail_nota_penjualan.*', 
        ])
        ->where(function($query) use($request){
            $query->where('tb_detail_nota_penjualan.is_deleted','=','0');
            $query->where('tb_detail_nota_penjualan.is_cn','=','1');
            $query->where('tb_detail_nota_penjualan.id_nota','=',$request->id);
        });
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->orwhere('tb_detail_nota_penjualan.id','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->editcolumn('tanggal', function($data) use($request){
            return Carbon::parse($data->cn_at)->format('d/m/Y H:i:s');
        })
        ->editcolumn('detail_obat', function($data) use($request){
            $string = '<b>'.$data->obat->nama.'<b><br>';
            $string .= '<small>Jumlah retur: '.$data->jumlah_cn.'</small>';
            return $string;
        })
        ->editcolumn('kasir', function($data) use($request){
            return $data->cn_oleh->nama;
        })
         ->editcolumn('alasan', function($data) use($request){
            $alasan = '';
            if($data->id_alasan_retur != '') {
                $alasan = $data->alasan->alasan;
                if($data->alasan_lain != '') {
                    $alasan .= ' ('.$data->alasan_lain.')';
                }
            } else {
                if($data->alasan_lain != '') {
                    $alasan .= '- ('.$data->alasan_lain.')';
                }
            }

            return $alasan;
        })
        ->editcolumn('status', function($data) use($request){
           // $x = $data->retur->is_approved;
            $btn = '';
            if($data->is_approved == 0) {
                $btn = '<span class="text-info"><i class="fa fa-fw fa-info"></i>belum dikonfirmasi</span>';
            } else if($data->is_approved == 1) {
                $btn = '<span class="text-success"><i class="fa fa-fw fa-info"></i>telah disetujui</span>';
            } else {
                $btn = '<span class="text-success"><i class="fa fa-fw fa-info"></i>tidak disetujui</span>';
            }
            return $btn;
        })
        ->editcolumn('aprove', function($data) use($request){
            //$x = $data->retur->is_approved;
            $btn = '';
           /* if($x == 0) {
                $btn = '-';
            } else if($x == 1) {
                $btn = $data->retur->aprove_oleh->nama;
            } else {
                $btn = $data->retur->aprove_oleh->nama;
            }*/

            return $btn;
        })
        ->addcolumn('action', function($data) use ($hak_akses){
            $btn = '<div class="btn-group">';
            $btn .= '<a href="'.url('/penjualan/cetak_retur/'.$data->id).'" title="Cetak Retur Penjualan" class="btn btn-primary btn-sm"><span data-toggle="tooltip" data-placement="top" title="Cetak Nota Retur"><i class="fa fa-print"></i> Cetak Retur</span></a>';
            if($hak_akses == 1) {
                $x = $data->retur->is_approved;
                if($x == 0) {
                } else if($x == 1) {
                    $btn .= '<span class="btn btn-danger btn-sm" onClick="batal_retur('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Batal Retur">Batal Retur</span>';
                } else {
                }
                
            } else {
                $btn .= '<span class="text-danger"><i class="fa fa-fw fa-info"></i>Tidak ada hak akses</span>';
            }
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['action', 'aprove', 'status', 'alasan', 'kasir', 'tanggal', 'detail_obat'])
        ->addIndexColumn()
        ->make(true);  
    }

    public function batal_retur(Request $request) {
        $detail_penjualan = TransaksiPenjualanDetail::find($request->id);
        $retur_penjulan = ReturPenjualan::find($detail_penjualan->id_retur_penjualan);
        $retur_penjulan->is_deleted = 1;
        $retur_penjulan->deleted_at = date('Y-m-d H:i:s');
        $retur_penjulan->deleted_by = Auth::user()->id;
       
        if($retur_penjulan->save()) {
            PenjualanReturBatal::dispatch($detail_penjualan);

            $detail_penjualan->is_cn = 0;
            $detail_penjualan->is_batal_cn = 1;
            $detail_penjualan->batal_cn_at = date('Y-m-d H:i:s');
            $detail_penjualan->batal_cn_by = Auth::user()->id;
            $detail_penjualan->id_retur_penjualan = null;
            $detail_penjualan->jumlah_cn = 0;

            if($detail_penjualan->save()) {
                echo 1;
            }else{
                echo 0;
            }
        } else {
            echo 0;
        }
    }

    public function aprove() {
        $cek_ = session('status_aproveretur_aktif');
        if($cek_ == null) {
            session(['status_aproveretur_aktif'=> 0]);
        }
        $status_aproveretur_aktif = session('status_aproveretur_aktif');
        return view('penjualan.aprove')->with(compact('status_aproveretur_aktif'));
    }

    public function set_status_aproveretur_aktif(Request $request) {
        session(['status_aproveretur_aktif'=> $request->id_status]);
        echo $request->id_status;
    }

    public function list_aprove(Request $request) {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        $apotek = MasterApotek::find(session('id_apotek_active'));
        $apoteker = User::find($apotek->id_apoteker);
        $id_user = Auth::user()->id;
        $hak_akses = 0;
        if($apoteker->id == $id_user) {
            $hak_akses = 1;
        }

        DB::statement(DB::raw('set @rownum = 0'));
        $data = TransaksiPenjualanDetail::select([
                DB::raw('@rownum  := @rownum  + 1 AS no'),
                'tb_detail_nota_penjualan.*', 
                //DB::Raw('IFNULL(a.is_approved, 0) as is_approved'),
               // 'a.id_alasan_retur',
                //'a.alasan_lain',
                'b.alasan',
                'c.nama as aprove_oleh'
        ])
        ->join('tb_nota_penjualan as x', 'x.id', '=', 'tb_detail_nota_penjualan.id_nota')
        //->leftjoin('tb_return_penjualan_obat as a', 'a.id_detail_nota', '=', 'tb_detail_nota_penjualan.id')
        ->leftjoin('tb_m_alasan_retur as b', 'b.id', '=', 'tb_detail_nota_penjualan.id_alasan_retur')
        ->leftjoin('users as c', 'c.id', '=', 'tb_detail_nota_penjualan.approved_by')
        ->where(function($query) use($request){
            $query->where('tb_detail_nota_penjualan.is_deleted','=','0');
            $query->where('tb_detail_nota_penjualan.is_cn','=','1');
            $query->where(DB::raw('YEAR(x.created_at)'),'!=','2020');
            $query->where('x.id_apotek_nota', session('id_apotek_active'));
            $query->where('tb_detail_nota_penjualan.is_approved', $request->is_status);
        })
        ->orderBy('tb_detail_nota_penjualan.id', 'DESC');
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->orwhere('tb_detail_nota_penjualan.id','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->editcolumn('tanggal', function($data) use($request){
            return Carbon::parse($data->cn_at)->format('d/m/Y H:i:s');
        })
        ->editcolumn('detail_obat', function($data) use($request){
            $string = '<b>'.$data->obat->nama.'<b><br>';
            $string .= '<small>Jumlah retur: '.$data->jumlah_cn.'</small>';
            return $string;
        })
        ->editcolumn('kasir', function($data) use($request){
            return $data->cn_oleh->nama;
        })
         ->editcolumn('alasan', function($data) use($request){
            $alasan = '';
            if($data->id_alasan_retur != '') {
                $alasan = $data->alasan;
                if($data->alasan_lain != '') {
                    $alasan .= ' ('.$data->alasan_lain.')';
                }
            } else {
                if($data->alasan_lain != '') {
                    $alasan .= '- ('.$data->alasan_lain.')';
                }
            }

            return $alasan;
        })
        ->editcolumn('status', function($data) use($request){
            $x = $data->is_approved;
            $btn = '';
            if($x == 0) {
                $btn = '<span class="text-info"><i class="fa fa-fw fa-info"></i>belum dikonfirmasi</span>';
            } else if($x == 1) {
                $btn = '<span class="text-success"><i class="fa fa-fw fa-info"></i>telah disetujui</span>';
            } else if($x == 2) {
                $btn = '<span class="text-danger"><i class="fa fa-fw fa-info"></i>tidak disetujui</span>';
            }
            return $btn;
        })
        ->editcolumn('aprove', function($data) use($request){
            $x = $data->is_approved;
            $btn = '';
            if($x == 0) {
                $btn = '-';
            } else if($x == 1) {
                $btn = $data->aprove_oleh;
            } else {
                $btn = $data->aprove_oleh;
            }

            return $btn;
        })
        ->addcolumn('action', function($data) use ($hak_akses){
            $btn = '<div class="btn-group">';
            $x = $data->is_approved;
            $btn = '';
            if($x != 0) {
                $btn .= '-';
            } else {
                $btn .= '<span class="btn btn-info btn-sm" onClick="konfirmasi_retur('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Konfirmasi Pengajuan"><i class="fa fa-check"></i> Konfirmasi</span>';
            }
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['action', 'aprove', 'status', 'alasan', 'kasir', 'tanggal', 'detail_obat'])
        ->addIndexColumn()
        ->make(true);  
    }

    public function retur() {
        return view('penjualan.retur');
    }

    public function list_retur(Request $request) {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        $apotek = MasterApotek::find(session('id_apotek_active'));
        $apoteker = User::find($apotek->id_apoteker);
        $id_user = Auth::user()->id;
        $hak_akses = 0;
        if($apoteker->id == $id_user) {
            $hak_akses = 1;
        }

        DB::statement(DB::raw('set @rownum = 0'));
        $data = TransaksiPenjualanDetail::select([
                DB::raw('@rownum  := @rownum  + 1 AS no'),
                'tb_detail_nota_penjualan.*', 
                DB::Raw('IFNULL(a.is_approved, 0) as is_approved'),
                'a.id_alasan_retur',
                'a.alasan_lain',
                'b.alasan',
                'c.nama as aprove_oleh'
        ])
        ->join('tb_nota_penjualan as x', 'x.id', '=', 'tb_detail_nota_penjualan.id_nota')
        ->leftjoin('tb_return_penjualan_obat as a', 'a.id_detail_nota', '=', 'tb_detail_nota_penjualan.id')
        ->join('tb_m_alasan_retur as b', 'b.id', '=', 'a.id_alasan_retur')
        ->leftjoin('users as c', 'c.id', '=', 'a.approved_by')
        ->where(function($query) use($request){
            $query->where('tb_detail_nota_penjualan.is_deleted','=','0');
            $query->where('tb_detail_nota_penjualan.is_cn','=','1');
            $query->where(DB::raw('YEAR(x.created_at)'),'!=','2020');
            $query->where('x.id_apotek_nota', session('id_apotek_active'));
        });
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->orwhere('tb_detail_nota_penjualan.id','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->editcolumn('tanggal', function($data) use($request){
            return Carbon::parse($data->cn_at)->format('d/m/Y H:i:s');
        })
        ->editcolumn('detail_obat', function($data) use($request){
            $string = '<b>'.$data->obat->nama.'<b><br>';
            $string .= '<small>Jumlah retur: '.$data->jumlah_cn.'</small>';
            return $string;
        })
        ->editcolumn('kasir', function($data) use($request){
            return $data->cn_oleh->nama;
        })
         ->editcolumn('alasan', function($data) use($request){
            $alasan = '';
            if($data->id_alasan_retur != '') {
                $alasan = $data->alasan;
                if($data->alasan_lain != '') {
                    $alasan .= ' ('.$data->alasan_lain.')';
                }
            } else {
                if($data->alasan_lain != '') {
                    $alasan .= '- ('.$data->alasan_lain.')';
                }
            }

            return $alasan;
        })
        ->editcolumn('status', function($data) use($request){
            $x = $data->is_approved;
            $btn = '';
            if($x == 0) {
                $btn = '<span class="text-info"><i class="fa fa-fw fa-info"></i>belum dikonfirmasi</span>';
            } else if($x == 1) {
                $btn = '<span class="text-success"><i class="fa fa-fw fa-info"></i>telah disetujui</span>';
            } else if($x == 2) {
                $btn = '<span class="text-danger"><i class="fa fa-fw fa-info"></i>tidak disetujui</span>';
            }
            return $btn;
        })
        ->editcolumn('aprove', function($data) use($request){
            $x = $data->is_approved;
            $btn = '';
            if($x == 0) {
                $btn = '-';
            } else if($x == 1) {
                $btn = $data->aprove_oleh;
            } else {
                $btn = $data->aprove_oleh;
            }

            return $btn;
        })
        ->addcolumn('action', function($data) use ($hak_akses){
            $btn = '<div class="btn-group">';
            $x = $data->is_approved;
            $btn = '';
            if($x != 0) {
                $btn .= '<span class="btn btn-info btn-sm" onClick="lihat_detail_retur('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Lihat Detail"><i class="fa fa-eye"></i> Detail</span>';
            } else {
                $btn .= '-';
            }
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['action', 'aprove', 'status', 'alasan', 'kasir', 'tanggal', 'detail_obat'])
        ->addIndexColumn()
        ->make(true);  
    }

    public function retur_aprove($id) {
        $detail_penjualan = TransaksiPenjualanDetail::find($id);
        $retur_penjulan = ReturPenjualan::find($detail_penjualan->id_retur_penjualan);

        $penjualan = TransaksiPenjualan::find($detail_penjualan->id_nota);
        $apotek = MasterApotek::find($penjualan->id_apotek_nota);

        return view('retur._retur_penjualan_aprove')->with(compact('detail_penjualan', 'retur_penjulan', 'penjualan', 'apotek'));
    }

    public function retur_aprove_update(Request $request, $id) {
        DB::beginTransaction(); 
        try{
            $act = $request->act; //1 = setuju, 2 = tidak setuju
            $detail_penjualan = TransaksiPenjualanDetail::find($id);
            $penjualan = TransaksiPenjualan::find($detail_penjualan->id_nota);
           // $retur_penjulan = ReturPenjualan::find($detail_penjualan->id_retur_penjualan);
            $i = 0;
            // update stok awal ->. histori retur
            //PenjualanRetur::dispatch($detail_penjualan);
            $apotek = MasterApotek::find(session('id_apotek_active'));
            $inisial = strtolower($apotek->nama_singkat);
            $stok_before = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $detail_penjualan->id_obat)->first();
            $stok_now = $stok_before->stok_akhir+$detail_penjualan->jumlah_cn;

            # update ke table stok harga
            /*$arrayupdate = array(
                'stok_awal'=> $stok_before->stok_akhir, 
                'stok_akhir'=> $stok_now, 
                'updated_at' => date('Y-m-d H:i:s'), 
                'updated_by' => Auth::user()->id
            );*/

            if($act == 1) {
                $stok_harga = MasterStokHarga::where('id_obat', $detail_penjualan->id_obat)->first();
                $stok_harga->stok_awal = $stok_before->stok_akhir;
                $stok_harga->stok_akhir = $stok_now;
                $stok_harga->updated_at = date('Y-m-d H:i:s');
                $stok_harga->updated_by = Auth::user()->id;
                if($stok_harga->save()) {
                } else {
                    DB::rollback();
                    echo json_encode(array('status' => 0, 'message' => 'Error, update master stok gagal'));
                }

                # create histori
                // $arrayinsert = array(
                // );

                # create histori
                $histori_stok = HistoriStok::where('id_obat', $detail_penjualan->id_obat)->where('jumlah', $detail_penjualan->jumlah_cn)->where('id_jenis_transaksi', 5)->where('id_transaksi', $detail_penjualan->id)->first();
                if(empty($histori_stok)) {
                    $histori_stok = new HistoriStok;
                }
                $histori_stok->id_obat = $detail_penjualan->id_obat;
                $histori_stok->jumlah = $detail_penjualan->jumlah_cn;
                $histori_stok->stok_awal = $stok_before->stok_akhir;
                $histori_stok->stok_akhir = $stok_now;
                $histori_stok->id_jenis_transaksi = 5; //retur
                $histori_stok->id_transaksi = $detail_penjualan->id;
                $histori_stok->batch = null;
                $histori_stok->ed = null;
                $histori_stok->sisa_stok = null;
                $histori_stok->hb_ppn = $detail_penjualan->harga_jual;
                $histori_stok->created_at = date('Y-m-d H:i:s');
                $histori_stok->created_by = Auth::user()->id;
                if($histori_stok->save()) {
                } else {
                    DB::rollback();
                    echo json_encode(array('status' => 0, 'message' => 'Error, gagal create histori stok'));
                }
                
                $i = $detail_penjualan->jumlah_cn;
                # update stok aktif 
                if(!is_null($detail_penjualan->id_histori_stok_detail)) {
                    $histori_stok_details = json_decode($detail_penjualan->id_histori_stok_detail);
                    foreach ($histori_stok_details as $y => $hist) {
                        $cekHistori = HistoriStok::find($hist->id_histori_stok);
                        if($i>0) {
                            if($hist->jumlah >= $detail_penjualan->jumlah_cn) {
                                $keterangan = $cekHistori->keterangan.', Retur Penjualan pada IDdet.'.$detail_penjualan->id.' sejumlah '.$detail_penjualan->jumlah_cn;
                                $cekHistori->sisa_stok = $cekHistori->sisa_stok + $detail_penjualan->jumlah_cn;
                                $cekHistori->keterangan = $keterangan;
                                $i = $i-$detail_penjualan->jumlah_cn;
                                if($cekHistori->save()) {
                                } else {
                                    DB::rollback();
                                    echo json_encode(array('status' => 0, 'message' => 'Error, gagal update stok sebelumnya'));
                                }
                            } else {
                                $keterangan = $cekHistori->keterangan.', Retur Penjualan pada IDdet.'.$detail_penjualan->id.' sejumlah '.$hist->jumlah;
                                $cekHistori->sisa_stok = $cekHistori->sisa_stok + $hist->jumlah;
                                $cekHistori->keterangan = $keterangan;
                                $i = $i-$hist->jumlah;
                                if($cekHistori->save()) {
                                } else {
                                    DB::rollback();
                                    echo json_encode(array('status' => 0, 'message' => 'Error, gagal update stok sebelumnya'));
                                }
                            }
                        }
                    }
                } else {
                    $cekHistori = HistoriStok::where('id_obat', $detail_penjualan->id_obat)
                                    ->whereIn('id_jenis_transaksi', [2,3,11,9])
                                    ->orderBy('id', 'DESC')
                                    ->first();

                    $keterangan = $cekHistori->keterangan.', Retur Penjualan pada IDdet.'.$detail_penjualan->id.' sejumlah '.$detail_penjualan->jumlah_cn;
                    $cekHistori->sisa_stok = $cekHistori->sisa_stok + $detail_penjualan->jumlah_cn;
                    $cekHistori->keterangan = $keterangan;
                    if($cekHistori->save()) {
                    } else {
                        DB::rollback();
                        echo json_encode(array('status' => 0, 'message' => 'Error, gagal update stok sebelumnya'));
                    }
                }

                // update status retur 
                $detail_penjualan->is_approved = 1;
                $detail_penjualan->approved_at = date('Y-m-d H:i:s');
                $detail_penjualan->approved_by = Auth::user()->id;

                if($detail_penjualan->save()) {
                    DB::commit();
                    echo json_encode(array('status' => 1, 'message' => 'Success, retur berhasil disimpan'));
                } else {
                    DB::rollback();
                    echo json_encode(array('status' => 0, 'message' => 'Error, gagal aprove retur penjualan'));
                }
            } else {
                # update jumlah retur sbelumnya kembali ke penjualan sebelumnya
               /* $detail_penjualan

                $detail_penjualan->is_cn = 0;
                $obj->cn_at = date('Y-m-d H:i:s');
                $obj->cn_by = Auth::user()->id;
                $obj->jumlah_cn = $request->jumlah_cn;*/

                // buat penjualan ulang
                /*$penjualan_cek = TransaksiPenjualan::where('id_apotek_nota', session('id_apotek_active'))->orderBy('created_at', 'DESC')->first();
                $total = ($detail_penjualan->jumlah*$detail_penjualan->harga_jual)-$detail_penjualan->diskon;
                $penjualan_new = new TransaksiPenjualan;
                $penjualan_new->id_apotek_nota = session('id_apotek_active');
                $penjualan_new->id_pasien = $penjualan->id_pasien;
                $penjualan_new->diskon_persen = 0;
                $penjualan_new->diskon_rp = 0;
                $penjualan_new->id_karyawan = NULL;
                $penjualan_new->debet = 0;
                $penjualan_new->no_kartu = 0;
                $penjualan_new->surcharge = 0;
                $penjualan_new->id_dokter =NULL;
                $penjualan_new->biaya_jasa_dokter = 0;
                $penjualan_new->id_jasa_resep = NULL;
                $penjualan_new->biaya_resep = 0;
                $penjualan_new->total_belanja = $total; // total
                $penjualan_new->total_bayar = $total; // total
                $penjualan_new->cash = $total; // total
                $penjualan_new->kembalian = 0;
                $penjualan_new->is_penjualan_tolak_cn = 1;
                $penjualan_new->created_by = $penjualan_cek->created_by;
                $penjualan_new->created_at = date('Y-m-d H:i:s');
                $penjualan_new->tgl_nota = date('Y-m-d');

                if($penjualan_new->save()) {
                } else {
                    DB::rollback();
                    echo json_encode(array('status' => 0, 'message' => 'Error, gagal create pengajuan retur penjualan'));
                }

                $det_penjualan_new = new TransaksiPenjualanDetail;
                $det_penjualan_new->id_nota = $penjualan_new->id;
                $det_penjualan_new->id_obat = $detail_penjualan->id_obat;
                $det_penjualan_new->harga_jual = $detail_penjualan->harga_jual;
                $det_penjualan_new->jumlah = $detail_penjualan->jumlah;
                $det_penjualan_new->diskon = $detail_penjualan->diskon;
                $det_penjualan_new->created_by = $penjualan_cek->created_by;
                $det_penjualan_new->created_at = date('Y-m-d H:i:s');
                $det_penjualan_new->save();
                if($det_penjualan_new->save()) {
                } else {
                    DB::rollback();
                    echo json_encode(array('status' => 0, 'message' => 'Error, gagal create detail pengajuan retur penjualan'));
                }*/

                // update stok awal ->. histori penjualan baru
                //PenjualanCreate::dispatch($det_penjualan_new);

                // update status retur 
                $detail_penjualan->is_approved = 2;
                $detail_penjualan->approved_at = date('Y-m-d H:i:s');
                $detail_penjualan->approved_by = Auth::user()->id;

                if($detail_penjualan->save()) {
                    DB::commit();
                    echo json_encode(array('status' => 1, 'message' => 'Success, retur berhasil disimpan'));
                } else {
                    DB::rollback();
                    echo json_encode(array('status' => 0, 'message' => 'Error, gagal aprove retur penjualan'));
                }
            }  
        }catch(\Exception $e){
            DB::rollback();
            echo json_encode(array('status' => 0));
        }   
    }

    public function lihat_detail_retur($id) {
        $detail_penjualan = TransaksiPenjualanDetail::find($id);
        $retur_penjulan = ReturPenjualan::find($detail_penjualan->id_retur_penjualan);

        $penjualan = TransaksiPenjualan::find($detail_penjualan->id_nota);
        $apotek = MasterApotek::find($penjualan->id_apotek_nota);

        return view('retur._lihat_detail_retur')->with(compact('detail_penjualan', 'retur_penjulan', 'penjualan', 'apotek'));
    }

    public function closing_kasir(Request $request) {
        if(empty($request->tanggal)) {
            $tanggal = date('Y-m-d');
        } else {
            $split                      = explode("-", $request->tanggal);
            $tgl_awal       = date('Y-m-d',strtotime($split[0]));
            $tgl_akhir      = date('Y-m-d',strtotime($split[1]));
            if($tgl_akhir == $tgl_awal) {
                $tanggal = $tgl_awal;
            } else {
                echo "Closing Kasir tidak dapat dilakukan, silakan pilih rentang tanggal pada hari yang sama, tidak diperbolehkan memilih rentang tanggal lebih dari 1 hari!";
                exit();
            }
        }

        //$tanggal = '2024-05-01';

        if(empty($request->id_user)) {
            $id_user =  Auth::user()->id;
        } else {
            $id_user = $request->id_user;
        }

        if(empty($request->id_apotek)) {
            $id_apotek =  session('id_apotek_active');
            $apotek = MasterApotek::find($id_apotek);
        } else {
            $id_apotek = $request->id_apotek;
            $apotek = MasterApotek::find($id_apotek);
        }


        $tgl_awal_baru = $tanggal.' 00:00:00';
        $tgl_akhir_baru = $tanggal.' 23:59:59';

        # pertama perlu mencari jumlah penjualan baik itu penjualan kredit dan penjualan non kredit
        # buatkan fungsi untuk mendapatkan jumlah penjualan yang sudah dipotong diskon (baik diskon item atau diskon persen atau diskon % dari vendor)


       /* $result = DB::select("
                        SELECT getTotalPenjualan(?, ?, ?, ?) AS total_penjualan_non_kredit
                        UNION ALL
                        SELECT getTotalPenjualan(?, ?, ?, ?) AS total_penjualan_kredit
                        UNION ALL
                        SELECT getTotalDiskonPersenPenjualan(?, ?, ?, ?) AS total_diskon_non_kredit
                        UNION ALL
                        SELECT getTotalDiskonPersenPenjualan(?, ?, ?, ?) AS total_diskon_kredit
                    ", [
                        $tanggal, $id_user, $id_apotek, 0,
                        $tanggal, $id_user, $id_apotek, 1,
                        $tanggal, $id_user, $id_apotek, 0,
                        $tanggal, $id_user, $id_apotek, 1
                    ]);
        dd($result);
*/

        // Mengambil nilai dari hasil query
       // $total_penjualan_1 = $result[0]->total_penjualan_1;
      //  $total_penjualan_2 = $result[1]->total_penjualan_2;


        $detail_penjualan = DB::table('tb_detail_nota_penjualan')
                        ->select(
                                DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) AS total_penjualan'),
                                DB::raw('SUM(tb_detail_nota_penjualan.diskon) AS total_diskon'),
                                DB::raw('SUM((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) - tb_detail_nota_penjualan.diskon) AS total'),
                                DB::raw('SUM(b.diskon_persen/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen'))
                        ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')
                        ->whereDate('b.created_at','>=', $tgl_awal_baru)
                        ->whereDate('b.created_at','<=', $tgl_akhir_baru)
                        ->where('b.created_by',$id_user)
                        ->where('b.id_apotek_nota','=',$id_apotek)
                        ->where('b.is_deleted', 0)
                        ->where('b.is_kredit', 0)
                        ->where('tb_detail_nota_penjualan.is_deleted', 0)
                        ->first();

        //dd($request->tanggal); exit();

        $sql = DB::table('tb_detail_nota_penjualan')
                        ->select(
                                ['b.*'])
                        ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')
                        ->leftjoin('tb_m_jasa_resep as a','a.id','=','b.id_jasa_resep')
                        ->whereRaw('b.created_at >= "'.$tgl_awal_baru.'"')
                        ->whereRaw('b.created_at <= "'.$tgl_akhir_baru.'"')
                        ->whereRaw('b.created_by = "'.$id_user.'"')
                        ->whereRaw('b.id_apotek_nota = "'.$id_apotek.'"')
                        ->whereRaw('b.is_deleted = 0')
                        ->whereRaw('b.is_kredit = 0')
                        ->whereRaw('tb_detail_nota_penjualan.is_deleted=0')
                        ->groupBy('b.id')
                        ->toSql();

        $penjualan = DB::table(DB::raw("($sql) AS t1"))->select([
                            DB::raw('SUM(t1.debet) AS total_debet')
                        ])
                        ->leftjoin('tb_m_jasa_resep as a','a.id','=','t1.id_jasa_resep')
                        ->first();

        $penjualan2 =  DB::table('tb_nota_penjualan')
                        ->select(
                                DB::raw('SUM(tb_nota_penjualan.biaya_jasa_dokter) AS total_jasa_dokter'),
                                DB::raw('SUM(a.biaya) AS total_jasa_resep'),
                                DB::raw('SUM(tb_nota_penjualan.debet) AS total_debet'),
                                DB::raw('SUM(tb_nota_penjualan.harga_wd) AS total_paket_wd'),
                                DB::raw('SUM(tb_nota_penjualan.biaya_lab) AS total_lab'),
                                DB::raw('SUM(tb_nota_penjualan.biaya_apd) AS total_apd'),
                                DB::raw('SUM(tb_nota_penjualan.diskon_rp) AS total_diskon_rp'))
                        ->leftjoin('tb_m_jasa_resep as a','a.id','=','tb_nota_penjualan.id_jasa_resep')
                        ->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.created_by',$id_user)
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->where('tb_nota_penjualan.is_deleted', 0)
                        ->where('tb_nota_penjualan.is_kredit', 0)
                        ->first();

        $detail_penjualan_kredit = DB::table('tb_detail_nota_penjualan')
                        ->select(
                                DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) AS total_penjualan'),
                                DB::raw('SUM(tb_detail_nota_penjualan.diskon) AS total_diskon'),
                                DB::raw('SUM((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) - tb_detail_nota_penjualan.diskon) AS total'),
                                DB::raw('SUM(b.diskon_persen/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen'),
                                DB::raw('SUM(a.diskon/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen_vendor')
                            )
                        ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')
                        ->leftjoin('tb_vendor_kerjasama as a','a.id','=','b.id_vendor')
                        //->where('b.created_at', 'LIKE', '%'.$tanggal.'%')
                        ->whereDate('b.tgl_nota','>=', $tgl_awal_baru)
                        ->whereDate('b.tgl_nota','<=', $tgl_akhir_baru)
                        ->where('b.created_by',$id_user)
                        ->where('b.id_apotek_nota','=',$id_apotek)
                        ->where('b.is_deleted', 0)
                        ->where('b.is_kredit', 1)
                        ->where('tb_detail_nota_penjualan.is_cn', 0)
                        ->where('tb_detail_nota_penjualan.is_deleted', 0)
                        ->first();
        
        /*print_r($detail_penjualan_kredit);exit();*/
        
        $penjualan_kredit =  DB::table('tb_nota_penjualan')
                        ->select(
                                DB::raw('SUM(tb_nota_penjualan.biaya_jasa_dokter) AS total_jasa_dokter'),
                                DB::raw('SUM(a.biaya) AS total_jasa_resep'),
                                DB::raw('SUM(tb_nota_penjualan.diskon_rp) AS total_diskon_rp'),
                                DB::raw('SUM(tb_nota_penjualan.debet) AS total_debet'))
                        ->leftjoin('tb_m_jasa_resep as a','a.id','=','tb_nota_penjualan.id_jasa_resep')
                        ->whereDate('tb_nota_penjualan.tgl_nota','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.tgl_nota','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.created_by',$id_user)
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->where('tb_nota_penjualan.is_deleted', 0)
                        ->where('tb_nota_penjualan.is_kredit', 1)
                        //->groupBy('tb_nota_penjualan.id')
                        ->first();

        $detail_penjualan_cn = DB::table('tb_detail_nota_penjualan')
                        ->select(
                                DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah_cn) AS total_penjualan'),
                                DB::raw('SUM(tb_detail_nota_penjualan.diskon) AS total_diskon'),
                                DB::raw('SUM((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah_cn) - tb_detail_nota_penjualan.diskon) AS total'),
                                DB::raw('SUM(b.diskon_persen/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah_cn)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen'))
                        ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')
                        ->whereDate('tb_detail_nota_penjualan.cn_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_detail_nota_penjualan.cn_at','<=', $tgl_akhir_baru)
                        ->where('tb_detail_nota_penjualan.created_by',$id_user)
                        ->where('b.id_apotek_nota','=',$id_apotek)
                        ->where('b.is_deleted', 0)
                        ->where('tb_detail_nota_penjualan.is_cn', 1)
                        ->where('tb_detail_nota_penjualan.is_approved', 1)
                        ->where('tb_detail_nota_penjualan.is_deleted', 0)
                        ->where('b.is_kredit', 0)
                        ->first();
        /*print_r($detail_penjualan_cn);exit()*/

        $penjualan_cn_cash = DB::table('tb_detail_nota_penjualan')
                        ->select(
                                DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah_cn) AS total_penjualan')
                            )
                        ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')
                        ->whereDate('tb_detail_nota_penjualan.cn_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_detail_nota_penjualan.cn_at','<=', $tgl_akhir_baru)
                        ->where('tb_detail_nota_penjualan.created_by',$id_user)
                        ->where('b.id_apotek_nota','=',$id_apotek)
                        ->where('b.is_deleted', 0)
                        ->where('b.debet', 0)
                        ->where('tb_detail_nota_penjualan.is_cn', 1)
                        ->where('tb_detail_nota_penjualan.is_approved', 1)
                        ->where('tb_detail_nota_penjualan.is_deleted', 0)
                        ->where('b.is_kredit', 0)
                        ->first();

        $sql2 = DB::table('tb_detail_nota_penjualan')
                        ->select(
                                ['b.*'])
                        ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')
                        ->leftjoin('tb_m_jasa_resep as a','a.id','=','b.id_jasa_resep')
                        ->whereRaw('b.created_at >= "'.$tgl_awal_baru.'"')
                        ->whereRaw('b.created_at <= "'.$tgl_akhir_baru.'"')
                        ->whereRaw('b.created_by = "'.$id_user.'"')
                        ->whereRaw('b.id_apotek_nota = "'.$id_apotek.'"')
                        ->whereRaw('b.is_deleted = 0')
                        ->whereRaw('b.is_kredit = 0')
                        ->whereRaw('tb_detail_nota_penjualan.is_cn=1')
                        ->whereRaw('tb_detail_nota_penjualan.is_approved=1')
                        ->whereRaw('tb_detail_nota_penjualan.is_deleted=0')
                        ->groupBy('b.id')
                        ->toSql();

        $penjualan_cn_debet = DB::table(DB::raw("($sql2) AS t1"))->select([
                            DB::raw('SUM(t1.debet) AS total_debet')
                        ])
                        ->join('tb_m_jasa_resep as a','a.id','=','t1.id_jasa_resep')
                        ->first();

        $detail_penjualan_kredit_terbayar = DB::table('tb_detail_nota_penjualan')
                        ->select(
                                DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) AS total_penjualan'),
                                DB::raw('SUM(tb_detail_nota_penjualan.diskon) AS total_diskon'),
                                DB::raw('SUM((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) - tb_detail_nota_penjualan.diskon) AS total'),
                                DB::raw('SUM(b.diskon_persen/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen'),
                                DB::raw('SUM(b.diskon_vendor/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_vendor')
                            )
                        ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')
                        ->whereDate('b.is_lunas_pembayaran_kredit_at','>=', $tgl_awal_baru)
                        ->whereDate('b.is_lunas_pembayaran_kredit_at','<=', $tgl_akhir_baru)
                        ->where('b.is_lunas_pembayaran_kredit_by',$id_user)
                        ->where('b.id_apotek_nota','=',$id_apotek)
                        ->where('b.is_deleted', 0)
                        ->where('b.is_kredit', 1)
                        ->where('b.is_lunas_pembayaran_kredit', 1)
                        ->where('tb_detail_nota_penjualan.is_cn', 0)
                        ->where('tb_detail_nota_penjualan.is_deleted', 0)
                        ->first();
        

        /*$data = DB::table('tb_detail_nota_penjualan')
                        ->select(
                                'b.id',
                                DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) AS total_penjualan'),
                                DB::raw('SUM(tb_detail_nota_penjualan.diskon) AS total_diskon'),
                                DB::raw('SUM((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) - tb_detail_nota_penjualan.diskon) AS total'),
                                DB::raw('SUM(b.diskon_persen/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen'),
                                DB::raw('SUM(b.diskon_vendor/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_vendor')
                            )
                        ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')
                        ->whereDate('b.is_lunas_pembayaran_kredit_at','>=', $tgl_awal_baru)
                        ->whereDate('b.is_lunas_pembayaran_kredit_at','<=', $tgl_akhir_baru)
                        ->where('b.is_lunas_pembayaran_kredit_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('b.id_apotek_nota','=',$id_apotek)
                        ->where('b.is_deleted', 0)
                        ->where('b.is_kredit', 1)
                        ->where('b.is_lunas_pembayaran_kredit', 1)
                        ->where('tb_detail_nota_penjualan.is_cn', 0)
                        ->groupBy('b.id')
                        ->get();

        foreach ($data as $key => $val) {
            $cek = TransaksiPenjualan::find($val->id);
            if($cek->debet != $val->total) {
                echo "ini totalnya-".$cek->debet.'--- dibandingkan dengan '.$val->total.'---<br>';
                echo("ini data yang tidak sesuai".$val->id);
            }
        }

        exit();*/
        
        $penjualan_kredit_terbayar =  DB::table('tb_nota_penjualan')
                        ->select(
                                DB::raw('SUM(tb_nota_penjualan.biaya_jasa_dokter) AS total_jasa_dokter'),
                                DB::raw('SUM(a.biaya) AS total_jasa_resep'),
                                DB::raw('SUM(tb_nota_penjualan.diskon_rp) AS total_diskon_rp'),
                                DB::raw('SUM(tb_nota_penjualan.debet) AS total_debet'))
                        ->leftjoin('tb_m_jasa_resep as a','a.id','=','tb_nota_penjualan.id_jasa_resep')
                        ->whereDate('tb_nota_penjualan.is_lunas_pembayaran_kredit_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.is_lunas_pembayaran_kredit_at','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.is_lunas_pembayaran_kredit_by',$id_user)
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->where('tb_nota_penjualan.is_deleted', 0)
                        ->where('tb_nota_penjualan.is_kredit', 1)
                        ->where('tb_nota_penjualan.is_lunas_pembayaran_kredit', 1)
                        //->groupBy('tb_nota_penjualan.id')
                        ->first();

        $penjualan_closing = TransaksiPenjualanClosing::where('id_apotek_nota', $id_apotek)->where('id_user',$id_user)->where('tanggal', $tanggal)->first();
        if(empty($penjualan_closing)) {
            $penjualan_closing = new TransaksiPenjualanClosing;
        }
        
        return view('penjualan.closing_kasir')->with(compact('tanggal', 'penjualan', 'penjualan_closing', 'detail_penjualan', 'penjualan_kredit', 'detail_penjualan_kredit', 'detail_penjualan_cn', 'penjualan_kredit_terbayar', 'detail_penjualan_kredit_terbayar', 'tanggal', 'id_user', 'penjualan2', 'penjualan_cn_debet', 'penjualan_cn_cash', 'apotek'));
    }

    public function closing_kasir_back16052024(Request $request) {
        if(empty($request->tanggal)) {
            $tanggal = date('Y-m-d');
        } else {
            $split                      = explode("-", $request->tanggal);
            $tgl_awal       = date('Y-m-d',strtotime($split[0]));
            $tgl_akhir      = date('Y-m-d',strtotime($split[1]));
            if($tgl_akhir == $tgl_awal) {
                $tanggal = $tgl_awal;
            } else {
                echo "Closing Kasir tidak dapat dilakukan, silakan pilih rentang tanggal pada hari yang sama, tidak diperbolehkan memilih rentang tanggal lebih dari 1 hari!";
                exit();
            }
        }

        //$tanggal = '2024-05-01';

        if(empty($request->id_user)) {
            $id_user =  Auth::user()->id;
        } else {
            $id_user = $request->id_user;
        }

        if(empty($request->id_apotek)) {
            $id_apotek =  session('id_apotek_active');
            $apotek = MasterApotek::find($id_apotek);
        } else {
            $id_apotek = $request->id_apotek;
            $apotek = MasterApotek::find($id_apotek);
        }


        $tgl_awal_baru = $tanggal.' 00:00:00';
        $tgl_akhir_baru = $tanggal.' 23:59:59';

        $detail_penjualan = DB::table('tb_detail_nota_penjualan')
                        ->select(
                                DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) AS total_penjualan'),
                                DB::raw('SUM(tb_detail_nota_penjualan.diskon) AS total_diskon'),
                                DB::raw('SUM((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) - tb_detail_nota_penjualan.diskon) AS total'),
                                DB::raw('SUM(b.diskon_persen/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen'))
                        ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')
                        ->whereDate('b.created_at','>=', $tgl_awal_baru)
                        ->whereDate('b.created_at','<=', $tgl_akhir_baru)
                        ->where('b.created_by',$id_user)
                        ->where('b.id_apotek_nota','=',$id_apotek)
                        ->where('b.is_deleted', 0)
                        ->where('b.is_kredit', 0)
                        ->where('tb_detail_nota_penjualan.is_deleted', 0)
                        ->first();

        //dd($detail_penjualan); exit();

        $sql = DB::table('tb_detail_nota_penjualan')
                        ->select(
                                ['b.*'])
                        ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')
                        ->leftjoin('tb_m_jasa_resep as a','a.id','=','b.id_jasa_resep')
                        ->whereRaw('b.created_at >= "'.$tgl_awal_baru.'"')
                        ->whereRaw('b.created_at <= "'.$tgl_akhir_baru.'"')
                        ->whereRaw('b.created_by = "'.$id_user.'"')
                        ->whereRaw('b.id_apotek_nota = "'.$id_apotek.'"')
                        ->whereRaw('b.is_deleted = 0')
                        ->whereRaw('b.is_kredit = 0')
                        ->whereRaw('tb_detail_nota_penjualan.is_deleted=0')
                        ->groupBy('b.id')
                        ->toSql();

        $penjualan = DB::table(DB::raw("($sql) AS t1"))->select([
                            DB::raw('SUM(t1.debet) AS total_debet')
                        ])
                        ->leftjoin('tb_m_jasa_resep as a','a.id','=','t1.id_jasa_resep')
                        ->first();

        $penjualan2 =  DB::table('tb_nota_penjualan')
                        ->select(
                                DB::raw('SUM(tb_nota_penjualan.biaya_jasa_dokter) AS total_jasa_dokter'),
                                DB::raw('SUM(a.biaya) AS total_jasa_resep'),
                                DB::raw('SUM(tb_nota_penjualan.debet) AS total_debet'),
                                DB::raw('SUM(tb_nota_penjualan.harga_wd) AS total_paket_wd'),
                                DB::raw('SUM(tb_nota_penjualan.biaya_lab) AS total_lab'),
                                DB::raw('SUM(tb_nota_penjualan.biaya_apd) AS total_apd'),
                                DB::raw('SUM(tb_nota_penjualan.diskon_rp) AS total_diskon_rp'))
                        ->leftjoin('tb_m_jasa_resep as a','a.id','=','tb_nota_penjualan.id_jasa_resep')
                        ->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.created_by',$id_user)
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->where('tb_nota_penjualan.is_deleted', 0)
                        ->where('tb_nota_penjualan.is_kredit', 0)
                        ->first();

        $detail_penjualan_kredit = DB::table('tb_detail_nota_penjualan')
                        ->select(
                                DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) AS total_penjualan'),
                                DB::raw('SUM(tb_detail_nota_penjualan.diskon) AS total_diskon'),
                                DB::raw('SUM((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) - tb_detail_nota_penjualan.diskon) AS total'),
                                DB::raw('SUM(b.diskon_persen/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen'),
                                DB::raw('SUM(a.diskon/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen_vendor')
                            )
                        ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')
                        ->leftjoin('tb_vendor_kerjasama as a','a.id','=','b.id_vendor')
                        //->where('b.created_at', 'LIKE', '%'.$tanggal.'%')
                        ->whereDate('b.tgl_nota','>=', $tgl_awal_baru)
                        ->whereDate('b.tgl_nota','<=', $tgl_akhir_baru)
                        ->where('b.created_by',$id_user)
                        ->where('b.id_apotek_nota','=',$id_apotek)
                        ->where('b.is_deleted', 0)
                        ->where('b.is_kredit', 1)
                        ->where('tb_detail_nota_penjualan.is_cn', 0)
                        ->where('tb_detail_nota_penjualan.is_deleted', 0)
                        ->first();
        
        /*print_r($detail_penjualan_kredit);exit();*/
        
        $penjualan_kredit =  DB::table('tb_nota_penjualan')
                        ->select(
                                DB::raw('SUM(tb_nota_penjualan.biaya_jasa_dokter) AS total_jasa_dokter'),
                                DB::raw('SUM(a.biaya) AS total_jasa_resep'),
                                DB::raw('SUM(tb_nota_penjualan.diskon_rp) AS total_diskon_rp'),
                                DB::raw('SUM(tb_nota_penjualan.debet) AS total_debet'))
                        ->leftjoin('tb_m_jasa_resep as a','a.id','=','tb_nota_penjualan.id_jasa_resep')
                        ->whereDate('tb_nota_penjualan.tgl_nota','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.tgl_nota','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.created_by',$id_user)
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->where('tb_nota_penjualan.is_deleted', 0)
                        ->where('tb_nota_penjualan.is_kredit', 1)
                        //->groupBy('tb_nota_penjualan.id')
                        ->first();

        $detail_penjualan_cn = DB::table('tb_detail_nota_penjualan')
                        ->select(
                                DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah_cn) AS total_penjualan'),
                                DB::raw('SUM(tb_detail_nota_penjualan.diskon) AS total_diskon'),
                                DB::raw('SUM((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah_cn) - tb_detail_nota_penjualan.diskon) AS total'),
                                DB::raw('SUM(b.diskon_persen/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah_cn)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen'))
                        ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')
                        ->whereDate('tb_detail_nota_penjualan.cn_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_detail_nota_penjualan.cn_at','<=', $tgl_akhir_baru)
                        ->where('tb_detail_nota_penjualan.created_by',$id_user)
                        ->where('b.id_apotek_nota','=',$id_apotek)
                        ->where('b.is_deleted', 0)
                        ->where('tb_detail_nota_penjualan.is_cn', 1)
                        ->where('tb_detail_nota_penjualan.is_approved', 1)
                        ->where('tb_detail_nota_penjualan.is_deleted', 0)
                        ->where('b.is_kredit', 0)
                        ->first();
        /*print_r($detail_penjualan_cn);exit()*/

        $penjualan_cn_cash = DB::table('tb_detail_nota_penjualan')
                        ->select(
                                DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah_cn) AS total_penjualan')
                            )
                        ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')
                        ->whereDate('tb_detail_nota_penjualan.cn_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_detail_nota_penjualan.cn_at','<=', $tgl_akhir_baru)
                        ->where('tb_detail_nota_penjualan.created_by',$id_user)
                        ->where('b.id_apotek_nota','=',$id_apotek)
                        ->where('b.is_deleted', 0)
                        ->where('b.debet', 0)
                        ->where('tb_detail_nota_penjualan.is_cn', 1)
                        ->where('tb_detail_nota_penjualan.is_approved', 1)
                        ->where('tb_detail_nota_penjualan.is_deleted', 0)
                        ->where('b.is_kredit', 0)
                        ->first();

        $sql2 = DB::table('tb_detail_nota_penjualan')
                        ->select(
                                ['b.*'])
                        ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')
                        ->leftjoin('tb_m_jasa_resep as a','a.id','=','b.id_jasa_resep')
                        ->whereRaw('b.created_at >= "'.$tgl_awal_baru.'"')
                        ->whereRaw('b.created_at <= "'.$tgl_akhir_baru.'"')
                        ->whereRaw('b.created_by = "'.$id_user.'"')
                        ->whereRaw('b.id_apotek_nota = "'.$id_apotek.'"')
                        ->whereRaw('b.is_deleted = 0')
                        ->whereRaw('b.is_kredit = 0')
                        ->whereRaw('tb_detail_nota_penjualan.is_cn=1')
                        ->whereRaw('tb_detail_nota_penjualan.is_approved=1')
                        ->whereRaw('tb_detail_nota_penjualan.is_deleted=0')
                        ->groupBy('b.id')
                        ->toSql();

        $penjualan_cn_debet = DB::table(DB::raw("($sql2) AS t1"))->select([
                            DB::raw('SUM(t1.debet) AS total_debet')
                        ])
                        ->join('tb_m_jasa_resep as a','a.id','=','t1.id_jasa_resep')
                        ->first();

        $detail_penjualan_kredit_terbayar = DB::table('tb_detail_nota_penjualan')
                        ->select(
                                DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) AS total_penjualan'),
                                DB::raw('SUM(tb_detail_nota_penjualan.diskon) AS total_diskon'),
                                DB::raw('SUM((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) - tb_detail_nota_penjualan.diskon) AS total'),
                                DB::raw('SUM(b.diskon_persen/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen'),
                                DB::raw('SUM(b.diskon_vendor/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_vendor')
                            )
                        ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')
                        ->whereDate('b.is_lunas_pembayaran_kredit_at','>=', $tgl_awal_baru)
                        ->whereDate('b.is_lunas_pembayaran_kredit_at','<=', $tgl_akhir_baru)
                        ->where('b.is_lunas_pembayaran_kredit_by',$id_user)
                        ->where('b.id_apotek_nota','=',$id_apotek)
                        ->where('b.is_deleted', 0)
                        ->where('b.is_kredit', 1)
                        ->where('b.is_lunas_pembayaran_kredit', 1)
                        ->where('tb_detail_nota_penjualan.is_cn', 0)
                        ->where('tb_detail_nota_penjualan.is_deleted', 0)
                        ->first();
        

        /*$data = DB::table('tb_detail_nota_penjualan')
                        ->select(
                                'b.id',
                                DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) AS total_penjualan'),
                                DB::raw('SUM(tb_detail_nota_penjualan.diskon) AS total_diskon'),
                                DB::raw('SUM((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) - tb_detail_nota_penjualan.diskon) AS total'),
                                DB::raw('SUM(b.diskon_persen/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen'),
                                DB::raw('SUM(b.diskon_vendor/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_vendor')
                            )
                        ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')
                        ->whereDate('b.is_lunas_pembayaran_kredit_at','>=', $tgl_awal_baru)
                        ->whereDate('b.is_lunas_pembayaran_kredit_at','<=', $tgl_akhir_baru)
                        ->where('b.is_lunas_pembayaran_kredit_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('b.id_apotek_nota','=',$id_apotek)
                        ->where('b.is_deleted', 0)
                        ->where('b.is_kredit', 1)
                        ->where('b.is_lunas_pembayaran_kredit', 1)
                        ->where('tb_detail_nota_penjualan.is_cn', 0)
                        ->groupBy('b.id')
                        ->get();

        foreach ($data as $key => $val) {
            $cek = TransaksiPenjualan::find($val->id);
            if($cek->debet != $val->total) {
                echo "ini totalnya-".$cek->debet.'--- dibandingkan dengan '.$val->total.'---<br>';
                echo("ini data yang tidak sesuai".$val->id);
            }
        }

        exit();*/
        
        $penjualan_kredit_terbayar =  DB::table('tb_nota_penjualan')
                        ->select(
                                DB::raw('SUM(tb_nota_penjualan.biaya_jasa_dokter) AS total_jasa_dokter'),
                                DB::raw('SUM(a.biaya) AS total_jasa_resep'),
                                DB::raw('SUM(tb_nota_penjualan.diskon_rp) AS total_diskon_rp'),
                                DB::raw('SUM(tb_nota_penjualan.debet) AS total_debet'))
                        ->leftjoin('tb_m_jasa_resep as a','a.id','=','tb_nota_penjualan.id_jasa_resep')
                        ->whereDate('tb_nota_penjualan.is_lunas_pembayaran_kredit_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.is_lunas_pembayaran_kredit_at','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.is_lunas_pembayaran_kredit_by',$id_user)
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->where('tb_nota_penjualan.is_deleted', 0)
                        ->where('tb_nota_penjualan.is_kredit', 1)
                        ->where('tb_nota_penjualan.is_lunas_pembayaran_kredit', 1)
                        //->groupBy('tb_nota_penjualan.id')
                        ->first();

        $penjualan_closing = TransaksiPenjualanClosing::where('id_apotek_nota', $id_apotek)->where('id_user',$id_user)->where('tanggal', $tanggal)->first();
        if(empty($penjualan_closing)) {
            $penjualan_closing = new TransaksiPenjualanClosing;
        }
        
        return view('penjualan.closing_kasir')->with(compact('tanggal', 'penjualan', 'penjualan_closing', 'detail_penjualan', 'penjualan_kredit', 'detail_penjualan_kredit', 'detail_penjualan_cn', 'penjualan_kredit_terbayar', 'detail_penjualan_kredit_terbayar', 'tanggal', 'id_user', 'penjualan2', 'penjualan_cn_debet', 'penjualan_cn_cash', 'apotek'));
    }

    public function set_paket_wd(Request $request){
        $penjualan = new TransaksiPenjualan;
        $pakets = MasterPaketWD::where('is_deleted', 0)->get();
        $harga_total = $request->harga_total;
        
        if(empty($harga_total)) {
            $harga_total = 0;
        }

        $id_paket_wd = $request->id_paket_wd;
        $harga_wd = $request->harga_wd;
        return view('penjualan._form_set_paket_wd')->with(compact('penjualan', 'pakets', 'harga_total', 'id_paket_wd', 'harga_wd'));
    }

    public function set_lab(Request $request){
        $penjualan = new TransaksiPenjualan;
        $harga_total = $request->harga_total;
        
        if(empty($harga_total)) {
            $harga_total = 0;
        }

        $biaya_lab = $request->biaya_lab;
        $nama_lab = $request->nama_lab;
        $keterangan_lab = $request->keterangan_lab;
        return view('penjualan._form_set_lab')->with(compact('penjualan', 'harga_total', 'biaya_lab', 'nama_lab', 'keterangan_lab'));
    }

    public function set_apd(Request $request){
        $penjualan = new TransaksiPenjualan;
        $harga_total = $request->harga_total;
        
        if(empty($harga_total)) {
            $harga_total = 0;
        }

        $biaya_apd = $request->biaya_apd;
        return view('penjualan._form_set_apd')->with(compact('penjualan', 'harga_total', 'biaya_apd'));
    }

    public function print_closing_kasir($id) {
        $penjualan_closing = TransaksiPenjualanClosing::find($id);

        if(empty($request->tanggal)) {
            $tanggal = date('Y-m-d');
        } else {
            $split                      = explode("-", $request->tanggal);
            $tgl_awal       = date('Y-m-d',strtotime($split[0]));
            $tgl_akhir      = date('Y-m-d',strtotime($split[1]));
            if($tgl_akhir == $tgl_awal) {
                $tanggal = $tgl_awal;
            } else {
                echo "Closing Kasir tidak dapat dilakukan, silakan pilih rentang tanggal pada hari yang sama, tidak diperbolehkan memilih rentang tanggal lebih dari 1 hari!";
                exit();
            }
        }

        if(empty($request->id_user)) {
            $id_user =  Auth::user()->id;
        } else {
            $id_user = $request->id_user;
        }

        if(empty($request->id_apotek)) {
            $id_apotek =  session('id_apotek_active');
        } else {
            $id_apotek = $request->id_apotek;
        }


        $tgl_awal_baru = $tanggal.' 00:00:00';
        $tgl_akhir_baru = $tanggal.' 23:59:59';

        $jasa_resep = TransaksiPenjualan::select([
                                DB::raw('COUNT(tb_nota_penjualan.id) AS jumlah_resep'),
                                DB::raw('SUM(biaya_resep) AS total_biaya_resep'), 
                                'a.nama as nama_jasa_resep',
                                'a.biaya'
                        ])
                        ->join('tb_m_jasa_resep as a', 'a.id', 'tb_nota_penjualan.id_jasa_resep')
                        ->where('tb_nota_penjualan.is_deleted', 0)
                        ->whereNotNull('tb_nota_penjualan.id_jasa_resep')
                        ->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.created_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->orWhere('tb_nota_penjualan.is_deleted', 0)
                        ->where('tb_nota_penjualan.id_jasa_resep', '!=', '0')
                        ->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.created_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->groupBy('tb_nota_penjualan.id_jasa_resep')
                        ->get();

        /*$penjualan_kredits = TransaksiPenjualan::select([
                                DB::raw('COUNT(tb_nota_penjualan.id) AS jumlah_transaksi'),
                                DB::raw('SUM(total_belanja) AS total'),
                                'a.nama as nama_vendor'
                        ])
                        ->leftjoin('tb_vendor_kerjasama as a', 'a.id', 'tb_nota_penjualan.id_vendor')
                        ->where('tb_nota_penjualan.is_deleted', 0)
                        ->whereNotNull('tb_nota_penjualan.id_vendor')
                        ->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.created_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->orWhere('tb_nota_penjualan.is_deleted', 0)
                        ->where('tb_nota_penjualan.id_vendor', '!=', '0')
                        ->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.created_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->groupBy('tb_nota_penjualan.id_vendor')
                        ->get();*/

        $penjualan_kredits = DB::table('tb_detail_nota_penjualan')
                        ->select(
                                DB::raw('COUNT(b.id) AS jumlah_transaksi'),
                                DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) AS total_penjualan'),
                                DB::raw('SUM(tb_detail_nota_penjualan.diskon) AS total_diskon'),
                                DB::raw('SUM((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) - tb_detail_nota_penjualan.diskon) AS total'),
                                DB::raw('SUM(b.diskon_persen/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen'),
                                DB::raw('SUM(a.diskon/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen_vendor'),
                                'a.nama as nama_vendor'
                            )

                        ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')
                        ->leftjoin('tb_vendor_kerjasama as a','a.id','=','b.id_vendor')
                        ->where('b.is_deleted', 0)
                        ->whereNotNull('b.id_vendor')
                        ->whereDate('b.created_at','>=', $tgl_awal_baru)
                        ->whereDate('b.created_at','<=', $tgl_akhir_baru)
                        ->where('b.created_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('b.id_apotek_nota','=',$id_apotek)
                        ->orWhere('b.is_deleted', 0)
                        ->where('b.id_vendor', '!=', '0')
                        ->whereDate('b.created_at','>=', $tgl_awal_baru)
                        ->whereDate('b.created_at','<=', $tgl_akhir_baru)
                        ->where('b.created_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('b.id_apotek_nota','=',$id_apotek)
                        ->groupBy('b.id_vendor')
                        ->get();

        $jasa_dokter = TransaksiPenjualan::select([
                            DB::raw('COUNT(tb_nota_penjualan.id) AS jumlah_transaksi'),
                            DB::raw('SUM(biaya_jasa_dokter) AS total_biaya_jasa_dokter'), 
                                'a.nama as nama_dokter', 
                                'a.fee'
                        ])
                        ->join('tb_m_dokter as a', 'a.id', 'tb_nota_penjualan.id_dokter')
                        ->where('tb_nota_penjualan.is_deleted', 0)
                        ->whereNotNull('tb_nota_penjualan.id_dokter')
                        ->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.created_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->orWhere('tb_nota_penjualan.is_deleted', 0)
                        ->where('tb_nota_penjualan.id_dokter', '!=', '0')
                        ->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.created_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->groupBy('tb_nota_penjualan.id_dokter')
                        ->get();

        $paket_wd = TransaksiPenjualan::select([
                            DB::raw('COUNT(tb_nota_penjualan.id) AS jumlah_paket'),
                            DB::raw('SUM(harga_wd) AS total_harga_wd'), 
                            'a.nama as nama_paket',
                            'a.harga'
                        ])
                        ->join('tb_m_paket_wd as a', 'a.id', 'tb_nota_penjualan.id_paket_wd')
                        ->where('tb_nota_penjualan.is_deleted', 0)
                        ->whereNotNull('tb_nota_penjualan.id_paket_wd')
                        ->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.created_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->orWhere('tb_nota_penjualan.is_deleted', 0)
                        ->where('tb_nota_penjualan.id_paket_wd', '!=', '0')
                        ->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.created_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->groupBy('tb_nota_penjualan.id_paket_wd')
                        ->get();

        $penjualan_debet = TransaksiPenjualan::select([
                            'tb_nota_penjualan.id',
                            DB::raw('COUNT(tb_nota_penjualan.id) AS jumlah_transaksi'),
                            DB::raw('SUM(debet) AS total_debet'), 
                            'a.id as id_kartu_debet_credit',
                            'a.nama as nama_kartu',
                            'a.charge'
                        ])
                        ->leftjoin('tb_m_kartu_debet_credit as a', 'a.id', 'tb_nota_penjualan.id_kartu_debet_credit')
                        ->where('tb_nota_penjualan.is_deleted', 0)
                        ->where('tb_nota_penjualan.is_kredit', 0)
                        ->where('tb_nota_penjualan.id_kartu_debet_credit', '!=', 0)
                        ->whereNotNull('tb_nota_penjualan.id_kartu_debet_credit')
                        ->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.created_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->orWhere('tb_nota_penjualan.is_deleted', 0)
                        ->where('tb_nota_penjualan.is_kredit', 0)
                        ->where('tb_nota_penjualan.id_kartu_debet_credit', '!=', '0')
                        ->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.created_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->groupBy('tb_nota_penjualan.id_kartu_debet_credit')
                        ->get();
/*
        $penjualan_debet = TransaksiPenjualan::select([
                            DB::raw('COUNT(tb_nota_penjualan.id) AS jumlah_transaksi'),
                            DB::raw('SUM(debet) AS total_debet'), 
                            'a.nama as nama_kartu',
                            'a.charge'
                        ])
                        ->join('tb_m_kartu_debet_credit as a', 'a.id', 'tb_nota_penjualan.id_kartu_debet_credit')
                        ->where('tb_nota_penjualan.is_deleted', 0)
                        ->where('tb_nota_penjualan.id_kartu_debet_credit', '!=', 1)
                        ->whereNotNull('tb_nota_penjualan.id_kartu_debet_credit')
                        ->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.created_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->orWhere('tb_nota_penjualan.is_deleted', 0)
                        ->where('tb_nota_penjualan.id_kartu_debet_credit', '!=', 1)
                        ->where('tb_nota_penjualan.id_kartu_debet_credit', '!=', '0')
                        ->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.created_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->groupBy('tb_nota_penjualan.id_kartu_debet_credit')
                        ->get();*/

        $apotek = MasterApotek::find($penjualan_closing->id_apotek_nota);

        

        $id_printer_active = session('id_printer_active');
        if(is_null($id_printer_active)) {
            session(['id_printer_active' => $apotek->id_printer]);
            $id_printer_active = session('id_printer_active');
        }

        if(Auth::user()->id == 1) {
            $id_printer_active = 2;            
        }

        if($id_printer_active == 1) {
            return view('penjualan._form_cetak_nota_closing')->with(compact('penjualan_closing', 'apotek', 'jasa_resep', 'jasa_dokter', 'paket_wd', 'penjualan_debet'));
        } else {
            return view('penjualan._form_cetak_nota_closing2')->with(compact('penjualan_closing', 'apotek', 'jasa_resep', 'jasa_dokter', 'paket_wd', 'penjualan_debet'));
        }
    }

    public function print_closing_kasir_thermal($id) {
        if(is_null($id) OR !isset($id) OR $id == 0) {
            //dd("dasda");
            return view('penjualan.page_closingnotfound');
        } else {
            
            $no = 0;

            if(empty($request->tanggal)) {
                $tanggal = date('Y-m-d');
                $id_user = Auth::user()->id;
            } else {
                $tanggal = $request->tanggal;
                $id_user = $request->id_user;
            }

            $data = TransaksiPenjualanClosing::find($id);
            $apotek = MasterApotek::find($data->id_apotek_nota);
            $nama_apotek = strtoupper($apotek->nama_panjang);
            $nama_apotek_singkat = strtoupper($apotek->nama_singkat);

            return view('penjualan._form_cetak_nota_closing2')->with(compact('data', 'apotek'));
        }
    }

    public function print_closing_kasir_pdf(Request $request) {
        $tanggal = date('Y-m-d');
        if(isset($request->tanggal)) {
            $tanggal = $request->tanggal;
        }
        
        $penjualan_closing = TransaksiPenjualanClosing::select([

                                    DB::raw('SUM(total_jasa_dokter) as total_jasa_dokter_a'),
                                    DB::raw('SUM(total_jasa_resep) as total_jasa_resep_a'),
                                    DB::raw('SUM(total_paket_wd) as total_paket_wd_a'),
                                    DB::raw('SUM(total_penjualan) as total_penjualan_a'),
                                    DB::raw('SUM(total_debet) as total_debet_a'),
                                    DB::raw('SUM(total_penjualan_cash) as total_penjualan_cash_a'),
                                    DB::raw('SUM(total_penjualan_cn) as total_penjualan_cn_a'),
                                    DB::raw('SUM(total_penjualan_kredit) as total_penjualan_kredit_a'),
                                    DB::raw('SUM(total_penjualan_kredit_terbayar) as total_penjualan_kredit_terbayar_a'),
                                    DB::raw('SUM(total_diskon) as total_diskon_a'),
                                    DB::raw('SUM(total_switch_cash) as total_switch_cash_a'),
                                    DB::raw('SUM(uang_seharusnya) as uang_seharusnya_a'),
                                    DB::raw('SUM(total_akhir) as total_akhir_a'),
                                    DB::raw('SUM(jumlah_tt) as jumlah_tt_a')
                                ])
                                ->where('tanggal', $tanggal)
                                ->where('id_apotek_nota', session('id_apotek_active'))
                                ->first();

        $rincians = TransaksiPenjualanClosing::where('tanggal', $tanggal)
                                ->where('id_apotek_nota', session('id_apotek_active'))
                                ->get();

        $apotek = MasterApotek::find(session('id_apotek_active'));
        $apoteker = User::find($apotek->id_apoteker);
        $email = $apoteker->email;
        $apoteker = $apoteker->toArray();

        $data = array(['penjualan_closing' => $penjualan_closing, 'apotek' => $apotek, 'tanggal' => $tanggal, 'apoteker' => $apoteker]);
        # kirim email ke ka outlet
       // Mail::to($email)->send(new \App\Mail\MailPenjualanClosing($data));

        $data = [
            'penjualan_closing' => $penjualan_closing,
            'apotek' => $apotek,
            'tanggal' => $tanggal,
            'rincians' => $rincians
        ];

        $nama_file_ = 'Cetak Report Penjualan Harian '.$tanggal;
       // $pdf = PDF::loadHTML(view('penjualan._form_cetak_nota_closing_pdf')->with(compact('penjualan_closing', 'apotek', 'tanggal')));
        
        /*$pdf->setOptions(array(
            'dpi' => 300,
            'page-size'=> 'A4',  
        ));*/

         $pdf = PDF::loadView('penjualan._form_cetak_nota_closing_pdf', $data);
          return $pdf->stream('Cetak Report Penjualan Harian '.$tanggal.'.pdf');
       // return $pdf->inline($nama_file_.'.pdf');

        /*$html2pdf = new Html2Pdf('P', 'A4', 'en', true, 'UTF-8', array(12, 12, 12, 12));
        $doc = view('penjualan._form_cetak_nota_closing_pdf',compact('penjualan_closing', 'apotek', 'tanggal'));
        $html2pdf->pdf->SetTitle('Cetak Report Penjualan Harian '.$tanggal);
        $html2pdf->setDefaultFont('Times');
        $html2pdf->writeHTML($doc,false);
        $html2pdf->Output('Cetak Report Penjualan Harian '.$tanggal.'.pdf');
        
        return $doc;*/
    }

    public function load_data_nota_print($id) {
        $no = 0;

        $nota = TransaksiPenjualan::find($id);
        $apotek = MasterApotek::find($nota->id_apotek_nota);
        $detail_penjualans = TransaksiPenjualanDetail::where('id_nota', $nota->id)->where('is_deleted', 0)->get();
        $nama_apotek = strtoupper($apotek->nama_panjang);
        $nama_apotek_singkat = strtoupper($apotek->nama_singkat);

        $a = str_pad("",40," ", STR_PAD_LEFT)."\n".
             str_pad("APOTEK BWF-".$nama_apotek, 40," ", STR_PAD_BOTH)."\n".
             str_pad($apotek->alamat, 40," ", STR_PAD_BOTH)."\n".
             str_pad("Telp. ". $apotek->telepon, 40," ", STR_PAD_BOTH);
        $a = $a."\n".
        "----------------------------------------\n".
        "No. Nota : ".$nama_apotek_singkat.'-'.$nota['id']."\n".
        "Tanggal  : ".Carbon::parse($nota['created_at'])->format('d-m-Y H:i:s')."\n".
        "----------------------------------------\n";
        if($nota->is_kredit == 1) {
            $vendor = MasterVendor::find($nota->id_vendor);
            $a = $a."\n".
            "----------------------------------------\n".
            "Penjualan Melalui : ".$vendor['nama']."\n".
            "----------------------------------------\n";
        }

        $total_belanja = 0;
        if($nota->is_penjualan_tanpa_item != 1) {
            foreach ($detail_penjualans as $key => $val) {
                $no++;

                $total_1 = $val->jumlah * $val->harga_jual;
                $total_2 = $total_1 - $val->diskon;
                $total_belanja = $total_belanja + $total_2;
                $harga_jual = number_format($val->harga_jual,0,',',',');
                $diskon = number_format($val->diskon,0,',',',');
                $total_2 = number_format($total_2,0,',',',');
                
                $a=$a.
                    str_pad($no.".".$val->obat->nama, 40," ", STR_PAD_RIGHT)."\n ".                 
                    //str_pad(" (diskon ".number_format($diskon, 0, '.', ',')."%)",11," ", STR_PAD_LEFT)."\n ".
                    str_pad($harga_jual, 7," ", STR_PAD_LEFT).
                    str_pad(" x ",3," ", STR_PAD_LEFT).
                    str_pad(number_format($val->jumlah, 0, '.', ',')." (-".number_format($val->diskon).")",9," ", STR_PAD_RIGHT).
                    str_pad("= ",3," ", STR_PAD_LEFT).str_pad("Rp ". $total_2,10," ", STR_PAD_LEFT)."\n";

            }
        } 
        $a=$a.
                "----------------------------------------\n";
        $total_diskon_persen = $nota->diskon_persen/100 * $total_belanja;
        $total_diskon_persen_vendor = $nota->diskon_vendor/100 * $total_belanja;
        $total_belanja_bayar = $total_belanja - ($total_diskon_persen + $nota->diskon_rp + $total_diskon_persen_vendor);
        $total_diskon = $total_diskon_persen+$nota->diskon_rp+$total_diskon_persen_vendor;
        $total_belanja = $total_belanja+$nota->biaya_jasa_dokter+$nota->biaya_lab+$nota->biaya_apd;
        $biaya_jasa_dokter = number_format($nota->biaya_jasa_dokter,0,',',',');
        $biaya_apd = number_format($nota->biaya_apd,0,',',',');
        $biaya_lab = number_format($nota->biaya_lab,0,',',',');

        if(!empty($nota->id_dokter)) {
            $a=$a."Jasa Dokter      : Rp ".$biaya_jasa_dokter;
        } else{
            $a=$a."Jasa Dokter      : Rp 0";
        }

        if(!empty($nota->id_jasa_resep)) {
            $x = MasterJasaResep::find($nota->id_jasa_resep);
            $jasa_resep_biaya = number_format($x->biaya,0,',',',');
            $total_belanja = $total_belanja+$x->biaya;
            $a=$a."\n"."Jasa Resep       : Rp ".$jasa_resep_biaya;
        } else {
            $a=$a."\n"."Jasa Resep       : Rp 0-";
        }
        
        if(!empty($nota->id_paket_wd)) {
            $harga_wd = number_format($nota->harga_wd,0,',',',');
            $total_belanja = $total_belanja+$nota->harga_wd;
            $a=$a."\n"."Paket WT         : Rp ".$harga_wd;
        } else {
            $a=$a."\n"."Paket WT         : Rp 0";
        }

        if(!empty($nota->biaya_lab)) {
            $a=$a."\n"."Biaya LAB        : Rp ".$biaya_lab;
        } else{
            $a=$a."\n"."Biaya LAB        : Rp 0";
        }

        if(!empty($nota->biaya_apd)) {
            $a=$a."\n"."Biaya APD        : Rp ".$biaya_apd;
        } else{
            $a=$a."\n"."Biaya APD        : Rp 0";
        }
        $a=$a."\n".
                "----------------------------------------\n";

        $debet = 0;
        if(!empty($nota->id_kartu_debet_credit)) {
            $debet = $nota->debet;
        } 
        $total_bayar = $debet+$nota->cash;

        if($total_bayar == 0) {
            $total_bayar = $total_belanja+$nota->kembalian;
        }
        $total_belanja_format = number_format($total_belanja,0,',',',');
        $total_diskon_format = number_format($total_diskon,0,',',',');
        $total_bayar_format = number_format($total_bayar,0,',',',');
        $kembalian_format = number_format($nota->kembalian,0,',',',');
        $grand_total = $total_belanja-$total_diskon;
        $grand_total_format = number_format($grand_total,0,',',',');

        $a=$a.
                "Total            : Rp ".$total_belanja_format."\n".
                "Diskon           : Rp ".$total_diskon_format."\n".
                "Grand Total      : Rp ".$grand_total_format."\n";
        if($nota->is_kredit != 1) {
            $a=$a.
                "Bayar            : Rp ".$total_bayar_format."\n".
                "Kembalian        : Rp ".$kembalian_format."\n".
                "----------------------------------------\n";
        } else {
            $a=$a.
                "----------------------------------------\n";
        }

        $b=$a.str_pad("",40," ", STR_PAD_LEFT)."\n".str_pad("Terimakasih Atas Kunjungan Anda", 40," ", STR_PAD_BOTH)."\n".str_pad("Semoga Lekas Sembuh", 40," ", STR_PAD_BOTH)."\n".str_pad("",40," ", STR_PAD_LEFT)."\n".str_pad("",40," ", STR_PAD_LEFT)."\n".str_pad("",40," ", STR_PAD_LEFT)."\n".str_pad("",40," ", STR_PAD_LEFT)."\n".str_pad("",40," ", STR_PAD_LEFT)."\n".str_pad("",40," ", STR_PAD_LEFT)."\n".str_pad("",40," ", STR_PAD_LEFT);  
        print_r($b);     
            
    }

    public function load_closing_kasir_print(Request $request, $id) {
        $no = 0;

        if(empty($request->tanggal)) {
            $tanggal = date('Y-m-d');
            $id_user = Auth::user()->id;
        } else {
            $tanggal = $request->tanggal;
            $id_user = $request->id_user;
        }

        $data = TransaksiPenjualanClosing::find($id);
        $apotek = MasterApotek::find($data->id_apotek_nota);
        $nama_apotek = strtoupper($apotek->nama_panjang);
        $nama_apotek_singkat = strtoupper($apotek->nama_singkat);

        $id_user = $data->id_user;
        $id_apotek = $data->id_apotek_nota;
        $tgl_awal_baru = $data->tanggal.' 00:00:00';
        $tgl_akhir_baru = $data->tanggal.' 23:59:59';

        $jasa_resep = TransaksiPenjualan::select([
                                DB::raw('COUNT(tb_nota_penjualan.id) AS jumlah_resep'),
                                DB::raw('SUM(biaya_resep) AS total_biaya_resep'), 
                                'a.id as id_jasa_resep',
                                'a.nama as nama_jasa_resep',
                                'a.biaya'
                        ])
                        ->leftjoin('tb_m_jasa_resep as a', 'a.id', 'tb_nota_penjualan.id_jasa_resep')
                        ->where('tb_nota_penjualan.is_deleted', 0)
                        ->whereNotNull('tb_nota_penjualan.id_jasa_resep')
                        ->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.created_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->orWhere('tb_nota_penjualan.is_deleted', 0)
                        ->where('tb_nota_penjualan.id_jasa_resep', '!=', '0')
                        ->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.created_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->groupBy('tb_nota_penjualan.id_jasa_resep')
                        ->get();

        /*$penjualan_kredits = TransaksiPenjualan::select([
                                DB::raw('COUNT(tb_nota_penjualan.id) AS jumlah_transaksi'),
                                DB::raw('SUM(total_belanja) AS total'),
                                'a.nama as nama_vendor'
                        ])
                        ->leftjoin('tb_vendor_kerjasama as a', 'a.id', 'tb_nota_penjualan.id_vendor')
                        ->where('tb_nota_penjualan.is_deleted', 0)
                        ->whereNotNull('tb_nota_penjualan.id_vendor')
                        ->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.created_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->orWhere('tb_nota_penjualan.is_deleted', 0)
                        ->where('tb_nota_penjualan.id_vendor', '!=', '0')
                        ->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.created_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->groupBy('tb_nota_penjualan.id_vendor')
                        ->get();*/

        $penjualan_kredits = DB::table('tb_detail_nota_penjualan')
                        ->select(
                                DB::raw('COUNT(b.id) AS jumlah_transaksi'),
                                DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) AS total_penjualan'),
                                DB::raw('SUM(tb_detail_nota_penjualan.diskon) AS total_diskon'),
                                DB::raw('SUM((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) - tb_detail_nota_penjualan.diskon) AS total'),
                                DB::raw('SUM(b.diskon_persen/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen'),
                                DB::raw('SUM(a.diskon/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen_vendor'),
                                'a.nama as nama_vendor'
                            )
                        ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')
                        ->leftjoin('tb_vendor_kerjasama as a','a.id','=','b.id_vendor')
                        ->where('b.is_deleted', 0)
                        ->where('tb_detail_nota_penjualan.is_deleted', 0)
                        ->whereNotNull('b.id_vendor')
                        ->whereDate('b.created_at','>=', $tgl_awal_baru)
                        ->whereDate('b.created_at','<=', $tgl_akhir_baru)
                        ->where('b.created_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('b.id_apotek_nota','=',$id_apotek)
                        ->where('tb_detail_nota_penjualan.is_cn', 0)
                        ->orWhere('b.is_deleted', 0)
                        ->where('tb_detail_nota_penjualan.is_deleted', 0)
                        ->where('b.id_vendor', '!=', '0')
                        ->whereDate('b.created_at','>=', $tgl_awal_baru)
                        ->whereDate('b.created_at','<=', $tgl_akhir_baru)
                        ->where('b.created_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('b.id_apotek_nota','=',$id_apotek)
                        ->groupBy('b.id_vendor')
                        ->where('tb_detail_nota_penjualan.is_cn', 0)
                        ->get();

        $jasa_dokter = TransaksiPenjualan::select([
                            DB::raw('COUNT(tb_nota_penjualan.id) AS jumlah_transaksi'),
                            DB::raw('SUM(biaya_jasa_dokter) AS total_biaya_jasa_dokter'), 
                                'a.id as id_dokter',
                                'a.nama as nama_dokter', 
                                'a.fee'
                        ])
                        ->leftjoin('tb_m_dokter as a', 'a.id', 'tb_nota_penjualan.id_dokter')
                        ->where('tb_nota_penjualan.is_deleted', 0)
                        ->whereNotNull('tb_nota_penjualan.id_dokter')
                        ->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.created_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->orWhere('tb_nota_penjualan.is_deleted', 0)
                        ->where('tb_nota_penjualan.id_dokter', '!=', '0')
                        ->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.created_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->groupBy('tb_nota_penjualan.id_dokter')
                        ->get();

        $paket_wd = TransaksiPenjualan::select([
                            DB::raw('COUNT(tb_nota_penjualan.id) AS jumlah_paket'),
                            DB::raw('SUM(harga_wd) AS total_harga_wd'), 
                            'a.id as id_paket_wd',
                            'a.nama as nama_paket',
                            'a.harga'
                        ])
                        ->leftjoin('tb_m_paket_wd as a', 'a.id', 'tb_nota_penjualan.id_paket_wd')
                        ->where('tb_nota_penjualan.is_deleted', 0)
                        ->whereNotNull('tb_nota_penjualan.id_paket_wd')
                        ->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.created_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->orWhere('tb_nota_penjualan.is_deleted', 0)
                        ->where('tb_nota_penjualan.id_paket_wd', '!=', '0')
                        ->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.created_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->groupBy('tb_nota_penjualan.id_paket_wd')
                        ->get();

        $penjualan_debet = TransaksiPenjualan::select([
                            'tb_nota_penjualan.id',
                            DB::raw('COUNT(tb_nota_penjualan.id) AS jumlah_transaksi'),
                            DB::raw('SUM(debet) AS total_debet'), 
                            'a.id as id_kartu_debet_credit',
                            'a.nama as nama_kartu',
                            'a.charge'
                        ])
                        ->leftjoin('tb_m_kartu_debet_credit as a', 'a.id', 'tb_nota_penjualan.id_kartu_debet_credit')
                        ->where('tb_nota_penjualan.is_deleted', 0)
                        ->where('tb_nota_penjualan.is_kredit', 0)
                        ->where('tb_nota_penjualan.id_kartu_debet_credit', '!=', 0)
                        ->whereNotNull('tb_nota_penjualan.id_kartu_debet_credit')
                        ->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.created_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->orWhere('tb_nota_penjualan.is_deleted', 0)
                        ->where('tb_nota_penjualan.is_kredit', 0)
                        ->where('tb_nota_penjualan.id_kartu_debet_credit', '!=', '0')
                        ->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.created_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->groupBy('tb_nota_penjualan.id_kartu_debet_credit')
                        ->get();

        $penjualan_debet2 = TransaksiPenjualan::select([
                            'tb_nota_penjualan.id',
                            DB::raw('COUNT(tb_nota_penjualan.id) AS jumlah_transaksi'),
                            DB::raw('SUM(debet) AS total_debet'), 
                            'a.id as id_kartu_debet_credit',
                            'a.nama as nama_kartu',
                            'a.charge'
                        ])
                        ->leftjoin('tb_m_kartu_debet_credit as a', 'a.id', 'tb_nota_penjualan.id_kartu_debet_credit')
                        ->where('tb_nota_penjualan.is_deleted', 0)
                        ->where('tb_nota_penjualan.id_kartu_debet_credit', '!=', 0)
                        ->whereNotNull('tb_nota_penjualan.id_kartu_debet_credit')
                        ->whereDate('tb_nota_penjualan.is_lunas_pembayaran_kredit_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.is_lunas_pembayaran_kredit_at','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.is_lunas_pembayaran_kredit_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->orWhere('tb_nota_penjualan.is_deleted', 0)
                        ->where('tb_nota_penjualan.id_kartu_debet_credit', '!=', '0')
                        ->whereDate('tb_nota_penjualan.is_lunas_pembayaran_kredit_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.is_lunas_pembayaran_kredit_at','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.is_lunas_pembayaran_kredit_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->groupBy('tb_nota_penjualan.id_kartu_debet_credit')
                        ->get();

        $admin = User::find($id_user);
        $a = str_pad("",40," ", STR_PAD_LEFT)."\n".
             str_pad("APOTEK BWF-".$nama_apotek, 40," ", STR_PAD_BOTH)."\n".
             str_pad($apotek->alamat, 40," ", STR_PAD_BOTH)."\n".
             str_pad("Telp. ". $apotek->telepon, 40," ", STR_PAD_BOTH);
             
        $tgl_nota = Carbon::parse($data->created_at)->format('d-m-Y H:i:s');
        $a = $a."\n".
        "----------------------------------------\n".
        "Kasir    : ".$admin->nama."\n".
        "Tanggal  : ".$tgl_nota."\n";
        
        $grand_total = $data->total_penjualan+$data->total_jasa_dokter+$data->total_jasa_resep+$data->total_paket_wd+$data->total_lab+$data->total_apd;
        $total_cash = $grand_total-$data->total_debet;

        $total_2 = $grand_total-$data->total_penjualan_cn;
        $total_debet_x = $data->total_debet-$data->total_penjualan_cn_debet;
        $total_cash_x = $total_cash-$data->total_penjualan_cn_cash;
        $a=$a."\n".
            "----------------------------------------\n".
            "Jumlah Penjualan     : ".number_format($data->jumlah_penjualan,0,',',',')."\n".
            "Jumlah Diskon Nota   : ".number_format($data->total_diskon,0,',',',')."\n".
            "------------------------------------ (-)\n".
            "Total Penjualan      : ".number_format($data->total_penjualan,0,',',',')."\n".
            "Total Jasa Dokter    : ".number_format($data->total_jasa_dokter,0,',',',')."\n".
            "Total Jasa Resep     : ".number_format($data->total_jasa_resep,0,',',',')."\n".
            "Total Paket WT       : ".number_format($data->total_paket_wd,0,',',',')."\n".
            "Total Lab            : ".number_format($data->total_lab,0,',',',')."\n".
            "Total APD            : ".number_format($data->total_apd,0,',',',')."\n".
            "------------------------------------ (+)\n".
            "Total I              : ".number_format($grand_total,0,',',',')."\n".
            "Total Debet/Credit   : ".number_format($data->total_debet,0,',',',')."\n".
            "Total Cash           : ".number_format($total_cash,0,',',',')."\n".
            "Total Retur          : ".number_format($data->total_penjualan_cn,0,',',',')."\n".
            "Total RD             : ".number_format($data->total_penjualan_cn_debet,0,',',',')."\n".
            "Total RC             : ".number_format($data->total_penjualan_cn_cash,0,',',',')."\n".
            "------------------------------------ (-)\n".
            "Total II             : ".number_format($total_2,0,',',',')."\n".
            "Total Debet/Credit   : ".number_format($total_debet_x,0,',',',')."\n".
            "Total Cash           : ".number_format($total_cash_x,0,',',',')."\n".
            "Switch Cash          : ".number_format($data->total_switch_cash,0,',',',')."\n".
            "Uang Seharusnya      : ".number_format($data->uang_seharusnya,0,',',',')."\n".
            "TT                   : ".number_format($data->jumlah_tt,0,',',',')."\n".
            "Total Akhir          : ".number_format($data->total_akhir,0,',',',')."\n";

        $a=$a.
            "----------------------------------------\n".
            "Total Penjualan K    : ".number_format($data->total_penjualan_kredit,0,',',',')."\n".
            "Penjualan K. Terbayar: ".number_format($data->total_penjualan_kredit_terbayar,0,',',',')."\n".
            "----------------------------------------\n";

         // "Total Penjualan K    : ".number_format($data->total_penjualan_kredit,0,',',',')."\n".
        //"Penjualan K. Terbayar: ".number_format($data->total_penjualan_kredit_terbayar,0,',',',')."\n".


        $a=$a.
            "Total Jasa Dokter    : Rp ".number_format($data->total_jasa_dokter,0,',',',')."\n";

        $total_jasa_dokter = 0;
        $jum_jasa_dokter = count($jasa_dokter);
        if($jum_jasa_dokter > 0) {
            $a=$a."Detail               : ";
            $i = 0;
            foreach($jasa_dokter as $obj){
                $total_biaya_jasa_dokter = number_format($obj->total_biaya_jasa_dokter,0,',',',');
                $fee = $obj->fee/100*$obj->total_biaya_jasa_dokter;
                $total_jasa_dokter = $total_jasa_dokter+$obj->total_biaya_jasa_dokter;
                $fee = number_format($fee,0,',',',');
                if($obj->id_dokter != 0) {
                    $i++;
                    $a=$a."\n"."- ".$obj->nama_dokter."\n".
                    "  (".$obj->jumlah_transaksi." transaksi) "." = Rp ".$total_biaya_jasa_dokter."\n".
                    "  Fee Dokter"."     = Rp ".$fee;
                }
            }
        } else {
            $a=$a."Detail               : -";
        }

        
        $a=$a."\n".
        "----------------------------------------\n";
        $a=$a.
            "Total Jasa Resep     : Rp ".number_format($data->total_jasa_dokter,0,',',',')."\n";

        $jum_jasa_resep = count($jasa_resep);
        $total_jasa_resep  = 0;
        if($jum_jasa_resep > 0) {
            $a=$a."Detail               : ";
            $i = 0;
            foreach($jasa_resep as $obj) {
                $jumlah_resep = $obj->jumlah_resep;
                $biaya = number_format($obj->biaya,0,',',',');
                $total_biaya_resep = number_format($obj->total_biaya_resep,0,',',',');
                $total_jasa_resep = $total_jasa_resep+$obj->total_biaya_resep;
                if($obj->id_jasa_resep != 4) {
                    $i++;
                    $a=$a."\n".$i.".".$obj->nama_jasa_resep."\n".
                    "  ".$jumlah_resep." x ".$biaya." = Rp ".$total_biaya_resep;
                }
            }
        } else {
            $a=$a."Detail               : -";
        }

        $a=$a."\n".
        "----------------------------------------\n";
        $a=$a.
            "Total Paket WT       : Rp ".number_format($data->total_paket_wd,0,',',',')."\n";

        $total_paket_wd = 0;
        $jum_paket_wd = count($paket_wd);
        if($jum_paket_wd > 0) {
            $a=$a."Detail               : ";
            $i = 0;
            foreach($paket_wd as $obj) {
                $jumlah_paket = $obj->jumlah_paket;
                $total_harga_wd = number_format($obj->total_harga_wd,0,',',',');
                $harga = number_format($obj->harga,0,',',',');
                $total_paket_wd = $total_paket_wd+$obj->total_harga_wd;
                if($obj->id_paket_wd != 0) {
                    $i++;
                    $a=$a."\n".$i.".".$obj->nama_paket."\n".
                    "  ".$jumlah_paket." x ".$harga." = Rp ".$total_harga_wd;
                }
            }
        } else {
            $a=$a."Detail               : -";
        }   

        $a=$a."\n".
        "----------------------------------------\n";

        $jum_penjualan_debet = count($penjualan_debet);
        if($jum_penjualan_debet > 0) {
            $a=$a."Detail Debet         : ";
            $i = 0;
            $id_aar = array();
            foreach($penjualan_debet as $obj) {
                $total_debet = number_format($obj->total_debet,0,',',',');
                if($obj->id_kartu_debet_credit != 0) {
                    $id_aar[] = $obj->id;
                    $i++;
                    $a=$a."\n".$i.".".$obj->nama_kartu."\n".
                    "  (".$obj->jumlah_transaksi." transaksi) = Rp ".$total_debet;
                }
            }
            
           /* if(count($penjualan_debet2) > 0) {
                $a=$a."\n"."\n"."** Khusus Pembayaran Kredit";
            }*/


           /* $ii = 0;
            foreach($penjualan_debet2 as $obj) {
                $total_debet = number_format($obj->total_debet,0,',',',');
                if($obj->id_kartu_debet_credit != 0) {
                    $id_aar[] = $obj->id;
                    $ii++;
                    $a=$a."\n".$ii.".".$obj->nama_kartu."\n".
                    "  (".$obj->jumlah_transaksi." transaksi) = Rp ".$total_debet;
                }
            }*/

        } else {
            $a=$a."Detail Debet         : -";

           /* if(count($penjualan_debet2) > 0) {
                $a=$a."\n"."\n"."** Khusus Pembayaran Kredit";
            }
            $ii = 0;
            foreach($penjualan_debet2 as $obj) {
                $total_debet = number_format($obj->total_debet,0,',',',');
                if($obj->id_kartu_debet_credit != 0) {
                    $id_aar[] = $obj->id;
                    $ii++;
                    $a=$a."\n".$ii.".".$obj->nama_kartu."\n".
                    "  (".$obj->jumlah_transaksi." transaksi) = Rp ".$total_debet;
                }
            }*/

        }
        $a=$a."\n".
        "----------------------------------------\n";
        $jum_penjualan_kredit = count($penjualan_kredits);
        if($jum_penjualan_kredit > 0) {
            $a=$a."Detail P. Kredit      : ";
            $ii = 0;
            foreach($penjualan_kredits as $obj) {
                $total = $obj->total - $obj->total_diskon_persen - $obj->total_diskon_persen_vendor;
                $total_ = number_format($total,0,',',',');
              //  $id_aar[] = $obj->id;
                $ii++;
                $a=$a."\n".$ii.".".$obj->nama_vendor."\n".
                "Rp ".$total_; //  (".$obj->jumlah_transaksi." transaksi) = 
            
            }
        } else {
            $a=$a."Detail P. Kredit      : -";
        }


        $a=$a."\n".
            "----------------------------------------\n".
            "Catatan : K= Kredit, RC = Retur Cash,"."\n".
            "          RD=Retur Debet"."\n";
        $a=$a.
            "----------------------------------------\n".
            "           ~ Selamat Bekerja ~          ";


       $b=$a.str_pad("",40," ", STR_PAD_LEFT)."\n".str_pad("----------------------------------------", 40," ", STR_PAD_BOTH)
       ."\n".str_pad("",40," ", STR_PAD_BOTH)
       ."\n".str_pad("",40," ", STR_PAD_LEFT)."\n".str_pad("",40," ", STR_PAD_LEFT)
       ."\n".str_pad("",40," ", STR_PAD_LEFT)."\n".str_pad("",40," ", STR_PAD_LEFT)
       ."\n".str_pad("",40," ", STR_PAD_LEFT)."\n".str_pad("",40," ", STR_PAD_LEFT)
       ."\n".str_pad("",40," ", STR_PAD_LEFT); 
            
        print_r($b) ;
    }

    public function pencarian_obat() {
        return view('penjualan.pencarian_obat');
    }

    public function list_pencarian_obat(Request $request) {
        DB::statement(DB::raw('set @rownum = 0'));
        $data = TransaksiPenjualanDetail::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_detail_nota_penjualan.*', 'a.nama'])
        ->join('tb_m_obat as a', 'a.id', 'tb_detail_nota_penjualan.id_obat')
        ->join('tb_nota_penjualan as b', 'b.id', 'tb_detail_nota_penjualan.id_nota')
        ->where(function($query) use($request){
            $query->where('tb_detail_nota_penjualan.is_deleted','=','0');
            $query->where('b.id_apotek_nota','=',session('id_apotek_active'));
        })
        ->orderBy('b.id', 'DESC');
        
        $datatables = Datatables::of($data);
        return $datatables  
        ->filter(function($query) use($request){
            $query->where(function($query) use($request){
                $query->orwhere('a.nama','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('a.barcode','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('a.sku','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->editcolumn('created_at', function($data) use($request){
            $string = '';
            if($data->is_penjualan_tolak_cn == 1) {
                $string .='<br><small class="text-red">Penambahan dari penolakan retur</small>';
            }
            return Carbon::parse($data->created_at)->format('d/m/Y H:i:s').$string;
        })
        ->editcolumn('created_by', function($data) use($request){
            return $data->created_oleh->nama;
        })
        ->editcolumn('id_obat', function($data) {
            return $data->nama;
        })  
        ->editcolumn('is_cn', function($data) use($request){
            if($data->is_cn == 1) {
                $string = '<b style="color:#d32f2f;">R</b>';
            } else {
                $string = "-";
            }
           
            return $string;
        })   
        ->editcolumn('total', function($data) {
            $total = ($data->jumlah*$data->harga_jual)-$data->diskon;
            if($total == "" || $total == null) {
                $total = 0;
            }
            $diskon = $data->nota->diskon_persen/100*$total;
            $total2 = $total-$diskon;
            $str_ = '';
            $str_ = $data->jumlah.' X Rp '.number_format($data->harga_jual, 2)."-(Rp ".number_format($diskon,2).') = Rp '.number_format($total2, 2);
            return $str_;
        })    
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['action', 'is_cn'])
        ->addIndexColumn()
        ->make(true);  
    }

    public function rekap_laboratorium() {
        return view('rekap.rekap_laboratorium');
    }

    public function list_rekap_laboratorium(Request $request) {
        DB::statement(DB::raw('set @rownum = 0'));
        $data = TransaksiPenjualan::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_nota_penjualan.*'])
        ->where(function($query) use($request){
            $query->where('tb_nota_penjualan.is_deleted','=','0');
            $query->whereNotNull('tb_nota_penjualan.biaya_lab');
            $query->where('tb_nota_penjualan.biaya_lab', '!=', 0);
            $query->where('tb_nota_penjualan.id_apotek_nota','=',session('id_apotek_active'));
            $query->where('tb_nota_penjualan.id','LIKE',($request->id > 0 ? $request->id : '%'.$request->id.'%'));
            if (!empty($request->tgl_awal) && !empty($request->tgl_akhir)) {
                $query->where('tb_nota_penjualan.tgl_nota','>=', $request->tgl_awal);
                $query->where('tb_nota_penjualan.tgl_nota','<=', $request->tgl_akhir);
            }
        })
        ->orderBy('tb_nota_penjualan.id', 'DESC');
        
        $datatables = Datatables::of($data);
        return $datatables  
        ->filter(function($query) use($request){
            $query->where(function($query) use($request){
                $query->orwhere('tb_nota_penjualan.nama_lab','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('tb_nota_penjualan.keterangan_lab','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->editcolumn('created_at', function($data) use($request){
            return Carbon::parse($data->created_at)->format('d/m/Y H:i:s');
        })
        ->editcolumn('created_by', function($data) use($request){
            return $data->created_oleh->nama;
        })
        ->editcolumn('biaya_lab', function($data) {
            return 'Rp '.number_format($data->biaya_lab, 2);
        })    
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['action', 'biaya_lab', 'created_at', 'created_by'])
        ->addIndexColumn()
        ->make(true);  
    }

    public function export_rekap_laboratorium(Request $request) 
    {
        $rekaps = TransaksiPenjualan::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_nota_penjualan.*'])
                                ->where(function($query) use($request){
                                    $query->where('tb_nota_penjualan.is_deleted','=','0');
                                    $query->whereNotNull('tb_nota_penjualan.biaya_lab');
                                    $query->where('tb_nota_penjualan.biaya_lab', '!=', 0);
                                    $query->where('tb_nota_penjualan.id_apotek_nota','=',session('id_apotek_active'));
                                    $query->where('tb_nota_penjualan.id','LIKE',($request->id > 0 ? $request->id : '%'.$request->id.'%'));
                                    if (!empty($request->tgl_awal) && !empty($request->tgl_akhir)) {
                                        $query->where('tb_nota_penjualan.tgl_nota','>=', $request->tgl_awal);
                                        $query->where('tb_nota_penjualan.tgl_nota','<=', $request->tgl_akhir);
                                    }
                                })
                                ->orderBy('tb_nota_penjualan.id', 'DESC')
                                ->get();


                $collection = collect();
                $no = 0;
                $total_excel=0;
                foreach($rekaps as $rekap) {
                    $no++;
                    $collection[] = array(
                        $no,
                        $rekap->id,
                        $rekap->created_at,
                        $rekap->created_oleh->nama,
                        $rekap->nama_lab,
                        $rekap->biaya_lab,
                        "Rp ".number_format($rekap->biaya_lab,2),
                        $rekap->keterangan_lab
                    );
                }

        $now = date('YmdHis');
        return Excel::download(new class($collection) implements FromCollection, WithHeadings, WithColumnWidths, WithStyles {

                    public function __construct($collection)
                    {
                        $this->collection = $collection;
                    }

                    public function headings(): array
                    {
                        return ['No', 'ID Nota', 'Tanggal', 'Kasir', 'Nama lab', 'Biaya', 'Biaya (Rp)', 'Keterangan'];
                    } 

                    public function columnWidths(): array
                    {
                        return [
                            'A' => 8,
                            'B' => 15,
                            'C' => 20,
                            'D' => 30,
                            'E' => 30,
                            'F' => 20,
                            'G' => 20,
                            'H' => 60            
                        ];
                    }

                    public function styles(Worksheet $sheet)
                    {
                        return [
                            1    => ['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'A'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'B'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'C'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            //'D'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'G'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                        ];
                    }


                    public function collection()
                    {
                        return $this->collection;
                    }
        },"Rekap Laboratorium_".$now.".xlsx");
    }

    public function rekap_jasa_dokter() {
        return view('rekap.rekap_jasa_dokter');
    }

    public function list_rekap_jasa_dokter(Request $request) {
        DB::statement(DB::raw('set @rownum = 0'));
        $data = TransaksiPenjualan::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_nota_penjualan.*'])
        ->where(function($query) use($request){
            $query->where('tb_nota_penjualan.is_deleted','=','0');
            $query->whereNotNull('tb_nota_penjualan.biaya_jasa_dokter');
            $query->where('tb_nota_penjualan.biaya_jasa_dokter', '!=', 0);
            $query->where('tb_nota_penjualan.id_apotek_nota','=',session('id_apotek_active'));
            $query->where('tb_nota_penjualan.id','LIKE',($request->id > 0 ? $request->id : '%'.$request->id.'%'));
            if (!empty($request->tgl_awal) && !empty($request->tgl_akhir)) {
                $query->where('tb_nota_penjualan.tgl_nota','>=', $request->tgl_awal);
                $query->where('tb_nota_penjualan.tgl_nota','<=', $request->tgl_akhir);
            }
        })
        ->orderBy('tb_nota_penjualan.id', 'DESC');
        
        $datatables = Datatables::of($data);
        return $datatables  
        ->filter(function($query) use($request){
            $query->where(function($query) use($request){
                $query->orwhere('tb_nota_penjualan.id','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->editcolumn('created_at', function($data) use($request){
            return Carbon::parse($data->created_at)->format('d/m/Y H:i:s');
        })
        ->editcolumn('created_by', function($data) use($request){
            $str = '';
            if(!is_null($data->created_by) AND $data->created_by != "") {
                $str = $data->created_oleh->nama;
            }
            return $str;
        })
        ->editcolumn('id_dokter', function($data) use($request){
            $str = '';
            if(is_null($data->id_dokter)) {
                $str = 'dokter belum disetting, silakan edit di penjualan sesuai ID nota.';
            } else {
                $dokter = MasterDokter::find($data->id_dokter);
                if(empty($dokter)) {
                    $str = 'dokter tidak ditemukan';
                } else {
                    $str = $dokter->nama;
                }
            }
            return $str;
        })
        ->editcolumn('biaya_jasa_dokter', function($data) {
            return 'Rp '.number_format($data->biaya_jasa_dokter, 2);
        })    
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['action', 'biaya_lab', 'created_at', 'created_by', 'id_dokter'])
        ->addIndexColumn()
        ->make(true);
    }


    public function export_rekap_jasa_dokter(Request $request) 
    {
        $rekaps = TransaksiPenjualan::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_nota_penjualan.*'])
                                ->where(function($query) use($request){
                                    $query->where('tb_nota_penjualan.is_deleted','=','0');
                                    $query->whereNotNull('tb_nota_penjualan.biaya_jasa_dokter');
                                    $query->where('tb_nota_penjualan.biaya_jasa_dokter', '!=', 0);
                                    $query->where('tb_nota_penjualan.id_apotek_nota','=',session('id_apotek_active'));
                                    $query->where('tb_nota_penjualan.id','LIKE',($request->id > 0 ? $request->id : '%'.$request->id.'%'));
                                    if (!empty($request->tgl_awal) && !empty($request->tgl_akhir)) {
                                        $query->where('tb_nota_penjualan.tgl_nota','>=', $request->tgl_awal);
                                        $query->where('tb_nota_penjualan.tgl_nota','<=', $request->tgl_akhir);
                                    }
                                })
                                ->orderBy('tb_nota_penjualan.id', 'DESC')
                                ->get();


                $collection = collect();
                $no = 0;
                $total_excel=0;
                foreach($rekaps as $rekap) {
                    $no++;

                    $str = '';
                    if(is_null($rekap->id_dokter)) {
                        $str = 'dokter belum disetting, silakan edit di penjualan sesuai ID nota.';
                    } else {
                        $dokter = MasterDokter::find($rekap->id_dokter);
                        if(empty($dokter)) {
                            $str = 'dokter tidak ditemukan';
                        } else {
                            $str = $dokter->nama;
                        }
                    }

                    $collection[] = array(
                        $no,
                        $rekap->id,
                        $rekap->created_at,
                        $rekap->created_oleh->nama,
                        $str,
                        $rekap->biaya_jasa_dokter,
                        "Rp ".number_format($rekap->biaya_jasa_dokter,2)
                    );
                }

        $now = date('YmdHis');
        return Excel::download(new class($collection) implements FromCollection, WithHeadings, WithColumnWidths, WithStyles {

                    public function __construct($collection)
                    {
                        $this->collection = $collection;
                    }

                    public function headings(): array
                    {
                        return ['No', 'ID Nota', 'Tanggal', 'Kasir', 'Dokter', 'Biaya', 'Biaya (Rp)'];
                    } 

                    public function columnWidths(): array
                    {
                        return [
                            'A' => 8,
                            'B' => 15,
                            'C' => 20,
                            'D' => 30,
                            'E' => 30,
                            'F' => 20,
                            'G' => 20        
                        ];
                    }

                    public function styles(Worksheet $sheet)
                    {
                        return [
                            1    => ['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'A'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'B'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'C'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'G'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                        ];
                    }


                    public function collection()
                    {
                        return $this->collection;
                    }
        },"Rekap Jasa Dokter_".$now.".xlsx");
    }


    public function rekap_jasa_resep() {
        return view('rekap.rekap_jasa_resep');
    }

    public function list_rekap_jasa_resep(Request $request) {
        DB::statement(DB::raw('set @rownum = 0'));
        $data = TransaksiPenjualan::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_nota_penjualan.*'])
        ->where(function($query) use($request){
            $query->where('tb_nota_penjualan.is_deleted','=','0');
            $query->whereNotNull('tb_nota_penjualan.id_jasa_resep');
            $query->where('tb_nota_penjualan.id_jasa_resep', '!=', 0);
            $query->where('tb_nota_penjualan.id_jasa_resep', '!=', 4);
            $query->where('tb_nota_penjualan.id_apotek_nota','=',session('id_apotek_active'));
            $query->where('tb_nota_penjualan.id','LIKE',($request->id > 0 ? $request->id : '%'.$request->id.'%'));
            if (!empty($request->tgl_awal) && !empty($request->tgl_akhir)) {
                $query->where('tb_nota_penjualan.tgl_nota','>=', $request->tgl_awal);
                $query->where('tb_nota_penjualan.tgl_nota','<=', $request->tgl_akhir);
            }
        })
        ->orderBy('tb_nota_penjualan.id', 'DESC');
        
        $datatables = Datatables::of($data);
        return $datatables  
        ->filter(function($query) use($request){
            $query->where(function($query) use($request){
                $query->orwhere('tb_nota_penjualan.id','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->editcolumn('created_at', function($data) use($request){
            return Carbon::parse($data->created_at)->format('d/m/Y H:i:s');
        })
        ->editcolumn('created_by', function($data) use($request){
            return $data->created_oleh->nama;
        })
        ->editcolumn('id_jasa_resep', function($data) use($request){
            return $data->jasa_resep->nama;
        })
        ->editcolumn('biaya_resep', function($data) {
            return 'Rp '.number_format($data->jasa_resep->biaya, 2);
        })    
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['action', 'biaya_resep', 'created_at', 'created_by', 'id_jasa_resep'])
        ->addIndexColumn()
        ->make(true);
    }

    public function export_rekap_jasa_resep(Request $request) 
    {
        $rekaps = TransaksiPenjualan::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_nota_penjualan.*'])
                                ->where(function($query) use($request){
                                    $query->where('tb_nota_penjualan.is_deleted','=','0');
                                    $query->whereNotNull('tb_nota_penjualan.id_jasa_resep');
                                    $query->where('tb_nota_penjualan.id_jasa_resep', '!=', 0);
                                    $query->where('tb_nota_penjualan.id_jasa_resep', '!=', 4);
                                    $query->where('tb_nota_penjualan.id_apotek_nota','=',session('id_apotek_active'));
                                    $query->where('tb_nota_penjualan.id','LIKE',($request->id > 0 ? $request->id : '%'.$request->id.'%'));
                                    if (!empty($request->tgl_awal) && !empty($request->tgl_akhir)) {
                                        $query->where('tb_nota_penjualan.tgl_nota','>=', $request->tgl_awal);
                                        $query->where('tb_nota_penjualan.tgl_nota','<=', $request->tgl_akhir);
                                    }
                                })
                                ->orderBy('tb_nota_penjualan.id', 'DESC')
                                ->get();


                $collection = collect();
                $no = 0;
                $total_excel=0;
                foreach($rekaps as $rekap) {
                    $no++;
                    $collection[] = array(
                        $no,
                        $rekap->id,
                        $rekap->created_at,
                        $rekap->created_oleh->nama,
                        $rekap->jasa_resep->nama,
                        $rekap->jasa_resep->biaya,
                        "Rp ".number_format($rekap->jasa_resep->biaya,2)
                    );
                }

        $now = date('YmdHis');
        return Excel::download(new class($collection) implements FromCollection, WithHeadings, WithColumnWidths, WithStyles {

                    public function __construct($collection)
                    {
                        $this->collection = $collection;
                    }

                    public function headings(): array
                    {
                        return ['No', 'ID Nota', 'Tanggal', 'Kasir', 'Jasa Resep', 'Biaya', 'Biaya (Rp)'];
                    } 

                    public function columnWidths(): array
                    {
                        return [
                            'A' => 8,
                            'B' => 15,
                            'C' => 20,
                            'D' => 30,
                            'E' => 30,
                            'F' => 20,
                            'G' => 20        
                        ];
                    }

                    public function styles(Worksheet $sheet)
                    {
                        return [
                            1    => ['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'A'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'B'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'C'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'G'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                        ];
                    }


                    public function collection()
                    {
                        return $this->collection;
                    }
        },"Rekap Jasa Resep_".$now.".xlsx");
    }

    public function rekap_paket_wt() {
        return view('rekap.rekap_paket_wd');
    }

    public function list_rekap_paket_wt(Request $request) {
        DB::statement(DB::raw('set @rownum = 0'));
        $data = TransaksiPenjualan::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_nota_penjualan.*'])
        ->where(function($query) use($request){
            $query->where('tb_nota_penjualan.is_deleted','=','0');
            $query->whereNotNull('tb_nota_penjualan.id_paket_wd');
            $query->where('tb_nota_penjualan.id_paket_wd', '!=', 0);
            $query->where('tb_nota_penjualan.id_apotek_nota','=',session('id_apotek_active'));
            $query->where('tb_nota_penjualan.id','LIKE',($request->id > 0 ? $request->id : '%'.$request->id.'%'));
            if (!empty($request->tgl_awal) && !empty($request->tgl_akhir)) {
                $query->where('tb_nota_penjualan.tgl_nota','>=', $request->tgl_awal);
                $query->where('tb_nota_penjualan.tgl_nota','<=', $request->tgl_akhir);
            }
        })
        ->orderBy('tb_nota_penjualan.id', 'DESC');
        
        $datatables = Datatables::of($data);
        return $datatables  
        ->filter(function($query) use($request){
            $query->where(function($query) use($request){
                $query->orwhere('tb_nota_penjualan.id','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->editcolumn('created_at', function($data) use($request){
            return Carbon::parse($data->created_at)->format('d/m/Y H:i:s');
        })
        ->editcolumn('created_by', function($data) use($request){
            return $data->created_oleh->nama;
        })
        ->editcolumn('id_paket_wd', function($data) use($request){
            return $data->paket_wd->nama;
        })
        ->editcolumn('harga_wd', function($data) {
            return 'Rp '.number_format($data->paket_wd->harga, 2);
        })    
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['action', 'harga_wd', 'created_at', 'created_by', 'id_paket_wd'])
        ->addIndexColumn()
        ->make(true);
    }


    public function export_rekap_paket_wt(Request $request) 
    {
        $rekaps = TransaksiPenjualan::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_nota_penjualan.*'])
                                ->where(function($query) use($request){
                                    $query->where('tb_nota_penjualan.is_deleted','=','0');
                                    $query->whereNotNull('tb_nota_penjualan.id_paket_wd');
                                    $query->where('tb_nota_penjualan.id_paket_wd', '!=', 0);
                                    $query->where('tb_nota_penjualan.id_apotek_nota','=',session('id_apotek_active'));
                                    $query->where('tb_nota_penjualan.id','LIKE',($request->id > 0 ? $request->id : '%'.$request->id.'%'));
                                    if (!empty($request->tgl_awal) && !empty($request->tgl_akhir)) {
                                        $query->where('tb_nota_penjualan.tgl_nota','>=', $request->tgl_awal);
                                        $query->where('tb_nota_penjualan.tgl_nota','<=', $request->tgl_akhir);
                                    }
                                })
                                ->orderBy('tb_nota_penjualan.id', 'DESC')
                                ->get();


                $collection = collect();
                $no = 0;
                $total_excel=0;
                foreach($rekaps as $rekap) {
                    $no++;
                    $collection[] = array(
                        $no,
                        $rekap->id,
                        $rekap->created_at,
                        $rekap->created_oleh->nama,
                        $rekap->paket_wd->nama,
                        $rekap->paket_wd->harga,
                        "Rp ".number_format($rekap->paket_wd->harga,2)
                    );
                }

        $now = date('YmdHis');
        return Excel::download(new class($collection) implements FromCollection, WithHeadings, WithColumnWidths, WithStyles {

                    public function __construct($collection)
                    {
                        $this->collection = $collection;
                    }

                    public function headings(): array
                    {
                        return ['No', 'ID Nota', 'Tanggal', 'Kasir', 'Nama Paket', 'Biaya', 'Biaya (Rp)'];
                    } 

                    public function columnWidths(): array
                    {
                        return [
                            'A' => 8,
                            'B' => 15,
                            'C' => 20,
                            'D' => 30,
                            'E' => 30,
                            'F' => 20,
                            'G' => 20        
                        ];
                    }

                    public function styles(Worksheet $sheet)
                    {
                        return [
                            1    => ['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'A'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'B'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'C'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'G'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                        ];
                    }


                    public function collection()
                    {
                        return $this->collection;
                    }
        },"Rekap Paket WT_".$now.".xlsx");
    }

    public function rekap_apd() {
        return view('rekap.rekap_apd');
    }

    public function list_rekap_apd(Request $request) {
        DB::statement(DB::raw('set @rownum = 0'));
        $data = TransaksiPenjualan::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_nota_penjualan.*'])
        ->where(function($query) use($request){
            $query->where('tb_nota_penjualan.is_deleted','=','0');
            $query->whereNotNull('tb_nota_penjualan.biaya_apd');
            $query->where('tb_nota_penjualan.biaya_apd', '!=', 0);
            $query->where('tb_nota_penjualan.id_apotek_nota','=',session('id_apotek_active'));
            $query->where('tb_nota_penjualan.id','LIKE',($request->id > 0 ? $request->id : '%'.$request->id.'%'));
            if (!empty($request->tgl_awal) && !empty($request->tgl_akhir)) {
                $query->where('tb_nota_penjualan.tgl_nota','>=', $request->tgl_awal);
                $query->where('tb_nota_penjualan.tgl_nota','<=', $request->tgl_akhir);
            }
        })
        ->orderBy('tb_nota_penjualan.id', 'DESC');
        
        $datatables = Datatables::of($data);
        return $datatables  
        ->filter(function($query) use($request){
            $query->where(function($query) use($request){
                $query->orwhere('tb_nota_penjualan.id','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->editcolumn('created_at', function($data) use($request){
            return Carbon::parse($data->created_at)->format('d/m/Y H:i:s');
        })
        ->editcolumn('created_by', function($data) use($request){
            return $data->created_oleh->nama;
        })
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['action', 'created_at', 'created_by'])
        ->addIndexColumn()
        ->make(true);
    }

    public function export_rekap_apd(Request $request) 
    {
        $rekaps = TransaksiPenjualan::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_nota_penjualan.*'])
                                ->where(function($query) use($request){
                                    $query->where('tb_nota_penjualan.is_deleted','=','0');
                                    $query->whereNotNull('tb_nota_penjualan.biaya_apd');
                                    $query->where('tb_nota_penjualan.biaya_apd', '!=', 0);
                                    $query->where('tb_nota_penjualan.id_apotek_nota','=',session('id_apotek_active'));
                                    $query->where('tb_nota_penjualan.id','LIKE',($request->id > 0 ? $request->id : '%'.$request->id.'%'));
                                    if (!empty($request->tgl_awal) && !empty($request->tgl_akhir)) {
                                        $query->where('tb_nota_penjualan.tgl_nota','>=', $request->tgl_awal);
                                        $query->where('tb_nota_penjualan.tgl_nota','<=', $request->tgl_akhir);
                                    }
                                })
                                ->orderBy('tb_nota_penjualan.id', 'DESC')
                                ->get();


                $collection = collect();
                $no = 0;
                $total_excel=0;
                foreach($rekaps as $rekap) {
                    $no++;
                    $collection[] = array(
                        $no,
                        $rekap->id,
                        $rekap->created_at,
                        $rekap->created_oleh->nama,
                        $rekap->biaya_apd,
                        "Rp ".number_format($rekap->biaya_apd,2)
                    );
                }

        $now = date('YmdHis');
        return Excel::download(new class($collection) implements FromCollection, WithHeadings, WithColumnWidths, WithStyles {

                    public function __construct($collection)
                    {
                        $this->collection = $collection;
                    }

                    public function headings(): array
                    {
                        return ['No', 'ID Nota', 'Tanggal', 'Kasir', 'Biaya', 'Biaya (Rp)'];
                    } 

                    public function columnWidths(): array
                    {
                        return [
                            'A' => 8,
                            'B' => 15,
                            'C' => 20,
                            'D' => 30,
                            'F' => 20,
                            'G' => 20        
                        ];
                    }

                    public function styles(Worksheet $sheet)
                    {
                        return [
                            1    => ['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'A'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'B'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'C'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'G'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                        ];
                    }


                    public function collection()
                    {
                        return $this->collection;
                    }
        },"Rekap APD_".$now.".xlsx");
    }

    public function rekap_omset() {
        $first_day = date('Y-m-01');
        $date_now = date('Y-m-d');

        return view('rekap.rekap_omset')->with(compact('date_now', 'first_day'));
    }


    public function list_rekap_omset(Request $request) {

        $query_date = date('Y-m-d');
        $first = date('Y-m-01', strtotime($query_date));
        $end = date('Y-m-t', strtotime($query_date));

        DB::statement(DB::raw('set @rownum = 0'));
        $penjualans = TransaksiPenjualan::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_nota_penjualan.*'])
        ->where(function($query) use($request, $first, $end){
            $query->where('tb_nota_penjualan.id_apotek_nota','=',session('id_apotek_active'));
            if (!empty($request->tgl_awal) && !empty($request->tgl_akhir)) {
                $query->where('tb_nota_penjualan.tgl_nota','>=', $request->tgl_awal);
                $query->where('tb_nota_penjualan.tgl_nota','<=', $request->tgl_akhir);
            } else {
                $query->where('tb_nota_penjualan.tgl_nota','>=', $first);
                $query->where('tb_nota_penjualan.tgl_nota','<=', $end);
            }
        })
        ->orderBy('tb_nota_penjualan.tgl_nota', 'DESC')
        ->orderBy('tb_nota_penjualan.id', 'ASC')
        ->groupBy('tb_nota_penjualan.tgl_nota')
        ->groupBy('tb_nota_penjualan.created_by')
        ->get();
       /* $jum = count($penjualans);
        return json_encode($penjualans);*/

        $data = collect();
        $i = 0;
        foreach($penjualans as $obj) {

            $omset = TransaksiPenjualanClosing::select(['tb_closing_nota_penjualan.*'])
                    ->where(function($query) use($obj){
                        $query->where('tb_closing_nota_penjualan.id_apotek_nota','=',session('id_apotek_active'));
                        $query->where('tb_closing_nota_penjualan.tanggal','>=', $obj->tgl_nota);
                        $query->where('tb_closing_nota_penjualan.id_user',$obj->created_by);
                    })->first();

                $tgl_awal_baru = $obj->tgl_nota.' 00:00:00';
                $tgl_akhir_baru = $obj->tgl_nota.' 23:59:59';

                $detail_penjualan = DB::table('tb_detail_nota_penjualan')
                            ->select(
                                    DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) AS total_penjualan'),
                                    DB::raw('SUM(tb_detail_nota_penjualan.diskon) AS total_diskon'),
                                    DB::raw('SUM((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) - tb_detail_nota_penjualan.diskon) AS total'),
                                    DB::raw('SUM(b.diskon_persen/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen'))
                            ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')
                            ->whereDate('b.created_at','>=', $tgl_awal_baru)
                            ->whereDate('b.created_at','<=', $tgl_akhir_baru)
                            ->where('b.created_by',$obj->created_by)
                            ->where('b.id_apotek_nota','=',$obj->id_apotek_nota)
                            ->where('b.is_deleted', 0)
                            ->where('b.is_kredit', 0)
                            ->where('tb_detail_nota_penjualan.is_deleted', 0)
                            ->first();

                $penjualan2 =  DB::table('tb_nota_penjualan')
                            ->select(
                                    DB::raw('SUM(tb_nota_penjualan.biaya_jasa_dokter) AS total_jasa_dokter'),
                                    DB::raw('SUM(a.biaya) AS total_jasa_resep'),
                                    DB::raw('SUM(tb_nota_penjualan.debet) AS total_debet'),
                                    DB::raw('SUM(tb_nota_penjualan.harga_wd) AS total_paket_wd'),
                                    DB::raw('SUM(tb_nota_penjualan.biaya_lab) AS total_lab'),
                                    DB::raw('SUM(tb_nota_penjualan.biaya_apd) AS total_apd'),
                                    DB::raw('SUM(tb_nota_penjualan.diskon_rp) AS total_diskon_rp'))
                            ->leftjoin('tb_m_jasa_resep as a','a.id','=','tb_nota_penjualan.id_jasa_resep')
                            ->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal_baru)
                            ->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir_baru)
                            ->where('tb_nota_penjualan.created_by',$obj->created_by)
                            ->where('tb_nota_penjualan.id_apotek_nota','=',$obj->id_apotek_nota)
                            ->where('tb_nota_penjualan.is_deleted', 0)
                            ->where('tb_nota_penjualan.is_kredit', 0)
                            ->first();


                $detail_penjualan_cn = DB::table('tb_detail_nota_penjualan')
                            ->select(
                                    DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah_cn) AS total_penjualan'),
                                    DB::raw('SUM(tb_detail_nota_penjualan.diskon) AS total_diskon'),
                                    DB::raw('SUM((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah_cn) - tb_detail_nota_penjualan.diskon) AS total'),
                                    DB::raw('SUM(b.diskon_persen/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah_cn)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen'))
                            ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')
                            ->whereDate('tb_detail_nota_penjualan.cn_at','>=', $tgl_awal_baru)
                            ->whereDate('tb_detail_nota_penjualan.cn_at','<=', $tgl_akhir_baru)
                            ->where('b.created_by',$obj->created_by)
                            ->where('b.id_apotek_nota','=',$obj->id_apotek_nota)
                            ->where('b.is_deleted', 0)
                            ->where('tb_detail_nota_penjualan.is_cn', 1)
                            ->where('tb_detail_nota_penjualan.is_approved', 1)
                            ->where('tb_detail_nota_penjualan.is_deleted', 0)
                            ->where('b.is_kredit', 0)
                            ->first();

                $detail_penjualan_kredit = DB::table('tb_detail_nota_penjualan')
                        ->select(
                                DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) AS total_penjualan'),
                                DB::raw('SUM(tb_detail_nota_penjualan.diskon) AS total_diskon'),
                                DB::raw('SUM((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) - tb_detail_nota_penjualan.diskon) AS total'),
                                DB::raw('SUM(b.diskon_persen/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen'),
                                DB::raw('SUM(a.diskon/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen_vendor')
                            )
                        ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')
                        ->leftjoin('tb_vendor_kerjasama as a','a.id','=','b.id_vendor')
                        //->where('b.created_at', 'LIKE', '%'.$tanggal.'%')
                         ->whereDate('b.tgl_nota','>=', $tgl_awal_baru)
                        ->whereDate('b.tgl_nota','<=', $tgl_akhir_baru)
                        ->where('b.created_by',$obj->created_by)
                        ->where('b.id_apotek_nota','=',$obj->id_apotek_nota)
                        ->where('b.is_deleted', 0)
                        ->where('b.is_kredit', 1)
                        ->where('tb_detail_nota_penjualan.is_cn', 0)
                        ->where('tb_detail_nota_penjualan.is_deleted', 0)
                        ->first();


                $total_cn = $detail_penjualan_cn->total - $detail_penjualan_cn->total_diskon_persen;
                $total_diskon = $detail_penjualan->total_diskon_persen + $penjualan2->total_diskon_rp;
                $total_3 = $detail_penjualan->total-($total_diskon+$total_cn);

                $total_cash_kredit = $detail_penjualan_kredit->total - $detail_penjualan_kredit->total_diskon_persen_vendor - $detail_penjualan_kredit->total_diskon_persen;

            $i++;
            $arr = collect();
            $arr->no = $i;
            if(!empty($omset)) {
                $i = $this->cek_last_closing_kasir($omset->tanggal);
                $shift = 'Shift 1';
                if($i==$omset->id) {
                    $shift = 'Shift 2';
                }
                
                $btn = '<div class="btn-group">';
                $btn .= '<span class="btn btn-primary btn-sm" onClick="detail_data('.$omset->id.')" data-toggle="tooltip" data-placement="top" title="Detail Data">Lihat Detail</span>';
                $i = $this->cek_last_closing_kasir($omset->tanggal);
                $jum = $this->jumlah_closing_kasir($omset->tanggal);
                
                if($jum > 2) {
                     $btn .= '<span class="btn btn-danger btn-sm" onClick="hapus_closing(\''.$omset->id.'\')" data-toggle="tooltip" data-placement="top" title="Hapus Data">Hapus Data</span>';
                }

                if($i==$omset->id) {
                    $btn .= '<span class="btn btn-warning btn-sm" onClick="cetak_report(\''.$omset->tanggal.'\')" data-toggle="tooltip" data-placement="top" title="Cetak Report">Cetak Report</span>';
                }

                $btn .='</div>';

                $arr->created_at = $omset->tanggal;
                $arr->created_by = $omset->kasir->nama;
                $arr->shift = $shift;
                $arr->action = $btn;
                $arr->total_penjualan = $omset->total_penjualan - $omset->total_penjualan_cn;
                $arr->total_penjualan_kredit = $omset->total_penjualan_kredit;
                $arr->hit_penjualan = $total_3;
                $arr->hit_penjualan_kredit = $total_cash_kredit;
                $arr->selisih = abs($omset->total_penjualan - $total_3);
            } else {
                $arr->created_at = $obj->tgl_nota;
                $arr->created_by = $obj->created_oleh->nama;
                $arr->shift = '<span class="text-danger">(not closed)</span>';
                $arr->action = '-';
                $arr->total_penjualan = 0;
                $arr->total_penjualan_kredit = 0;
                $arr->hit_penjualan = $total_3;
                $arr->hit_penjualan_kredit = 0;
                $arr->selisih = 0;
            }
            $data[] = $arr;

        }

        


       // print_r($data);exit();


        /*$data = TransaksiPenjualanClosing::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_closing_nota_penjualan.*'])
        ->where(function($query) use($request, $first, $end){
            $query->where('tb_closing_nota_penjualan.id_apotek_nota','=',session('id_apotek_active'));
            if (!empty($request->tgl_awal) && !empty($request->tgl_akhir)) {
                $query->where('tb_closing_nota_penjualan.tanggal','>=', $request->tgl_awal);
                $query->where('tb_closing_nota_penjualan.tanggal','<=', $request->tgl_akhir);
            } else {
                $query->where('tb_closing_nota_penjualan.tanggal','>=', $first);
                $query->where('tb_closing_nota_penjualan.tanggal','<=', $end);
            }
        })
        ->orderBy('tb_closing_nota_penjualan.tanggal', 'DESC')
        ->orderBy('tb_closing_nota_penjualan.id', 'DESC');*/
        
        $datatables = Datatables::of($data);
        return $datatables  
        
        ->editcolumn('created_at', function($data) use($request){
            return Carbon::parse($data->created_at)->format('d/m/Y');
        })
        ->editcolumn('created_by', function($data) use($request){
            return $data->created_by;
        })
        ->addcolumn('shift', function($data) {
            
            return $data->shift;
        })   
        ->editcolumn('total_penjualan', function($data) use($request){
            return $data->total_penjualan;
        })
        ->editcolumn('total_penjualan_kredit', function($data) use($request){
            return $data->total_penjualan_kredit;
        })
        ->editcolumn('hit_penjualan', function($data) use($request){
            return $data->hit_penjualan;
        })
        ->editcolumn('hit_penjualan_kredit', function($data) use($request){
            return $data->hit_penjualan_kredit;
        })
        ->addcolumn('action', function($data) {
            return $data->action;
        })    
        /*->setRowClass(function ($data) {
            if($data->no % 2 == 0){
                return 'bg-closing';
            } else {
                return '';
            }
        })  */
        ->rawColumns(['action', 'created_at', 'created_by', 'shift'])
        ->addIndexColumn()
        ->make(true);
    }

    public function list_rekap_omset_back(Request $request) {

        $query_date = date('Y-m-d');
        $first = date('Y-m-01', strtotime($query_date));
        $end = date('Y-m-t', strtotime($query_date));

        DB::statement(DB::raw('set @rownum = 0'));
        $data = TransaksiPenjualanClosing::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_closing_nota_penjualan.*'])
        ->where(function($query) use($request, $first, $end){
            $query->where('tb_closing_nota_penjualan.id_apotek_nota','=',session('id_apotek_active'));
            if (!empty($request->tgl_awal) && !empty($request->tgl_akhir)) {
                $query->where('tb_closing_nota_penjualan.tanggal','>=', $request->tgl_awal);
                $query->where('tb_closing_nota_penjualan.tanggal','<=', $request->tgl_akhir);
            } else {
                $query->where('tb_closing_nota_penjualan.tanggal','>=', $first);
                $query->where('tb_closing_nota_penjualan.tanggal','<=', $end);
            }
        })
        ->orderBy('tb_closing_nota_penjualan.tanggal', 'DESC')
        ->orderBy('tb_closing_nota_penjualan.id', 'DESC');
        
        $datatables = Datatables::of($data);
        return $datatables  
        ->filter(function($query) use($request){
            $query->where(function($query) use($request){
                $query->orwhere('tb_closing_nota_penjualan.id','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->editcolumn('created_at', function($data) use($request){
            return Carbon::parse($data->created_at)->format('d/m/Y');
        })
        ->editcolumn('created_by', function($data) use($request){
            return $data->kasir->nama;
        })
        ->addcolumn('hit_penjualan', function($data) use($request){
            $tgl_awal_baru = $data->tanggal.' 00:00:00';
            $tgl_akhir_baru = $data->tanggal.' 23:59:59';

            $detail_penjualan = DB::table('tb_detail_nota_penjualan')
                        ->select(
                                DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) AS total_penjualan'),
                                DB::raw('SUM(tb_detail_nota_penjualan.diskon) AS total_diskon'),
                                DB::raw('SUM((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) - tb_detail_nota_penjualan.diskon) AS total'),
                                DB::raw('SUM(b.diskon_persen/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen'))
                        ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')
                        ->whereDate('b.created_at','>=', $tgl_awal_baru)
                        ->whereDate('b.created_at','<=', $tgl_akhir_baru)
                        ->where('b.created_by',$data->id_user)
                        ->where('b.id_apotek_nota','=',$data->id_apotek_nota)
                        ->where('b.is_deleted', 0)
                        ->where('b.is_kredit', 0)
                        ->where('tb_detail_nota_penjualan.is_deleted', 0)
                        ->first();

            $penjualan2 =  DB::table('tb_nota_penjualan')
                        ->select(
                                DB::raw('SUM(tb_nota_penjualan.biaya_jasa_dokter) AS total_jasa_dokter'),
                                DB::raw('SUM(a.biaya) AS total_jasa_resep'),
                                DB::raw('SUM(tb_nota_penjualan.debet) AS total_debet'),
                                DB::raw('SUM(tb_nota_penjualan.harga_wd) AS total_paket_wd'),
                                DB::raw('SUM(tb_nota_penjualan.biaya_lab) AS total_lab'),
                                DB::raw('SUM(tb_nota_penjualan.biaya_apd) AS total_apd'),
                                DB::raw('SUM(tb_nota_penjualan.diskon_rp) AS total_diskon_rp'))
                        ->leftjoin('tb_m_jasa_resep as a','a.id','=','tb_nota_penjualan.id_jasa_resep')
                        ->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.created_by',$data->id_user)
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$data->id_apotek_nota)
                        ->where('tb_nota_penjualan.is_deleted', 0)
                        ->where('tb_nota_penjualan.is_kredit', 0)
                        ->first();


            $detail_penjualan_cn = DB::table('tb_detail_nota_penjualan')
                        ->select(
                                DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah_cn) AS total_penjualan'),
                                DB::raw('SUM(tb_detail_nota_penjualan.diskon) AS total_diskon'),
                                DB::raw('SUM((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah_cn) - tb_detail_nota_penjualan.diskon) AS total'),
                                DB::raw('SUM(b.diskon_persen/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah_cn)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen'))
                        ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')
                        ->whereDate('tb_detail_nota_penjualan.cn_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_detail_nota_penjualan.cn_at','<=', $tgl_akhir_baru)
                        ->where('b.created_by',$data->id_user)
                        ->where('b.id_apotek_nota','=',$data->id_apotek_nota)
                        ->where('b.is_deleted', 0)
                        ->where('tb_detail_nota_penjualan.is_cn', 1)
                        ->where('tb_detail_nota_penjualan.is_approved', 1)
                        ->where('tb_detail_nota_penjualan.is_deleted', 0)
                        ->where('b.is_kredit', 0)
                        ->first();


            $total_cn = $detail_penjualan_cn->total - $detail_penjualan_cn->total_diskon_persen;
            $total_diskon = $detail_penjualan->total_diskon_persen + $penjualan2->total_diskon_rp;
            $total_3 = $detail_penjualan->total-($total_diskon+$total_cn);

            return $total_3;
        })
        ->editcolumn('hit_penjualan_kredit', function($data) use($request){
            return '';
        })
        ->addcolumn('shift', function($data) {
            $i = $this->cek_last_closing_kasir($data->tanggal);
            $shift = 'Shift 1';
            if($i==$data->id) {
                $shift = 'Shift 2';
            }
            return $shift;
        })   
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .= '<span class="btn btn-primary btn-sm" onClick="detail_data('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Detail Data">Lihat Detail</span>';
            $i = $this->cek_last_closing_kasir($data->tanggal);
            $jum = $this->jumlah_closing_kasir($data->tanggal);
            
            if($jum > 2) {
                 $btn .= '<span class="btn btn-danger btn-sm" onClick="hapus_closing(\''.$data->id.'\')" data-toggle="tooltip" data-placement="top" title="Hapus Data">Hapus Data</span>';
            }

            if($i==$data->id) {
                $btn .= '<span class="btn btn-warning btn-sm" onClick="cetak_report(\''.$data->tanggal.'\')" data-toggle="tooltip" data-placement="top" title="Cetak Report">Cetak Report</span>';
            }

            $btn .='</div>';
            return $btn;
        })    
        ->setRowClass(function ($data) {
            if($data->no % 2 == 0){
                return 'bg-closing';
            } else {
                return '';
            }
        })  
        ->rawColumns(['action', 'created_at', 'created_by', 'shift', 'hit_penjualan', 'hit_penjualan_kredit'])
        ->addIndexColumn()
        ->make(true);
    }

    public function cek_last_closing_kasir($tgl) {
        $cek = TransaksiPenjualanClosing::where('id_apotek_nota', session('id_apotek_active'))->whereDate('tanggal', $tgl)->orderBy('id', 'desc')->first();

        $i = 0;
        if(!empty($cek)) {
            $i = $cek->id;
        }
        return $i;
    }

    public function jumlah_closing_kasir($tgl) {
        $cek = TransaksiPenjualanClosing::where('id_apotek_nota', session('id_apotek_active'))->whereDate('tanggal', $tgl)->count();
        return $cek;
    }

    public function hapus_closing($id) {
        DB::beginTransaction(); 
        try{
            $close = TransaksiPenjualanClosing::find($id);
            $jumlah = TransaksiPenjualanClosing::where('id_apotek_nota', session('id_apotek_active'))
                        ->where('jumlah_penjualan', $close->jumlah_penjualan)
                        ->where('total_penjualan', $close->total_penjualan)
                        ->where('id_user', $close->created_by)
                        ->whereDate('tanggal', $close->tanggal)
                        ->count();
            if($jumlah > 1) {
                #diijinkan untuk hapus
                if($close->delete()){
                    DB::commit();
                    echo 1;
                }else{
                    DB::rollback();
                    echo 0;
                }
            } else {
                DB::rollback();
                echo 0;
            }
            
        }catch(\Exception $e){
            DB::rollback();
            echo 0;
        }
    }

    public function detail_closing_kasir($id) {
        $data = TransaksiPenjualanClosing::find($id);

        return view('rekap.detail_closing_kasir')->with(compact('data'));
    }

    public function export_rekap_omset(Request $request) 
    {
        $query_date = date('Y-m-d');
        $first = date('Y-m-01', strtotime($query_date));
        $end = date('Y-m-t', strtotime($query_date));

        $rekaps = TransaksiPenjualanClosing::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_closing_nota_penjualan.*'])
                                ->where(function($query) use($request, $first, $end){
                                    $query->where('tb_closing_nota_penjualan.id_apotek_nota','=',session('id_apotek_active'));
                                    if (!empty($request->tgl_awal) && !empty($request->tgl_akhir)) {
                                        $query->where('tb_closing_nota_penjualan.tanggal','>=', $request->tgl_awal);
                                        $query->where('tb_closing_nota_penjualan.tanggal','<=', $request->tgl_akhir);
                                    } else {
                                        $query->where('tb_closing_nota_penjualan.tanggal','>=', $first);
                                        $query->where('tb_closing_nota_penjualan.tanggal','<=', $end);
                                    }
                                })
                                ->orderBy('tb_closing_nota_penjualan.id', 'asc')
                                ->get();



                $collection = collect();
                $no = 0;
                $total_excel=0;
                $tanggal = '';
                $i_tgl = 0;

                $kartus = MasterKartu::where('is_deleted', 0)->get();
                foreach($rekaps as $rekap) {
                    $no++;
                    if($tanggal == '') {
                        $tanggal = $rekap->tanggal;
                        $i_tgl = $i_tgl+1;
                    } else {
                        if($tanggal == $rekap->tanggal) {
                            $i_tgl = $i_tgl+1;
                        } else {
                            $i_tgl = 0;
                            $i_tgl = $i_tgl+1;
                            $tanggal = $rekap->tanggal;
                        }
                    }

                    if($i_tgl == 1) {
                        $shift = 'Pagi';
                    } else {
                        $shift = 'Sore';
                    }

                    $total_1 = $rekap->jumlah_penjualan;
                    if($total_1 == 0) {
                        $total_1 = $rekap->total_penjualan+$rekap->total_diskon;
                    }
                    $total_3 = $total_1-$rekap->total_diskon;
                    $grand_total = $total_3+$rekap->total_jasa_dokter+$rekap->total_jasa_resep+$rekap->total_paket_wd+$rekap->total_lab+$rekap->total_apd;

                    $total_2 = $grand_total-$rekap->total_penjualan_cn;
                    $total_debet_x = $rekap->total_debet-$rekap->total_penjualan_cn_debet;
                    $total_cash_x = $rekap->uang_seharusnya-$rekap->total_penjualan_cn_cash;
                    $new_total = $rekap->total_akhir+$rekap->total_penjualan_kredit_terbayar;

                    $tgl_awal_baru = $rekap->tanggal.' 00:00:00';
                    $tgl_akhir_baru = $rekap->tanggal.' 23:59:59';
                    $id_user = $rekap->id_user;
                    $id_apotek = $rekap->id_apotek_nota;

                    $penjualan_debet = TransaksiPenjualan::select([
                            'tb_nota_penjualan.id',
                            DB::raw('COUNT(tb_nota_penjualan.id) AS jumlah_transaksi'),
                            DB::raw('SUM(debet) AS total_debet'), 
                            'a.id as id_kartu_debet_credit',
                            'a.nama as nama_kartu',
                            'a.charge'
                        ])
                        ->leftjoin('tb_m_kartu_debet_credit as a', 'a.id', 'tb_nota_penjualan.id_kartu_debet_credit')
                        ->where('tb_nota_penjualan.is_deleted', 0)
                        ->where('tb_nota_penjualan.is_kredit', 0)
                        ->where('tb_nota_penjualan.id_kartu_debet_credit', '!=', 0)
                        ->whereNotNull('tb_nota_penjualan.id_kartu_debet_credit')
                        ->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.created_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->orWhere('tb_nota_penjualan.is_deleted', 0)
                        ->where('tb_nota_penjualan.is_kredit', 0)
                        ->where('tb_nota_penjualan.id_kartu_debet_credit', '!=', '0')
                        ->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.created_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->groupBy('tb_nota_penjualan.id_kartu_debet_credit')
                        ->get();

                    $penjualan_debet2 = TransaksiPenjualan::select([
                                        'tb_nota_penjualan.id',
                                        DB::raw('COUNT(tb_nota_penjualan.id) AS jumlah_transaksi'),
                                        DB::raw('SUM(debet) AS total_debet'), 
                                        'a.id as id_kartu_debet_credit',
                                        'a.nama as nama_kartu',
                                        'a.charge'
                                    ])
                                    ->leftjoin('tb_m_kartu_debet_credit as a', 'a.id', 'tb_nota_penjualan.id_kartu_debet_credit')
                                    ->where('tb_nota_penjualan.is_deleted', 0)
                                    ->where('tb_nota_penjualan.id_kartu_debet_credit', '!=', 0)
                                    ->whereNotNull('tb_nota_penjualan.id_kartu_debet_credit')
                                    ->whereDate('tb_nota_penjualan.is_lunas_pembayaran_kredit_at','>=', $tgl_awal_baru)
                                    ->whereDate('tb_nota_penjualan.is_lunas_pembayaran_kredit_at','<=', $tgl_akhir_baru)
                                    ->where('tb_nota_penjualan.is_lunas_pembayaran_kredit_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                                    ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                                    ->orWhere('tb_nota_penjualan.is_deleted', 0)
                                    ->where('tb_nota_penjualan.id_kartu_debet_credit', '!=', '0')
                                    ->whereDate('tb_nota_penjualan.is_lunas_pembayaran_kredit_at','>=', $tgl_awal_baru)
                                    ->whereDate('tb_nota_penjualan.is_lunas_pembayaran_kredit_at','<=', $tgl_akhir_baru)
                                    ->where('tb_nota_penjualan.is_lunas_pembayaran_kredit_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                                    ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                                    ->groupBy('tb_nota_penjualan.id_kartu_debet_credit')
                                    ->get();

                    $detail_penjualan_kredit = DB::table('tb_detail_nota_penjualan')
                        ->select(
                                DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) AS total_penjualan'),
                                DB::raw('SUM(tb_detail_nota_penjualan.diskon) AS total_diskon'),
                                DB::raw('SUM((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) - tb_detail_nota_penjualan.diskon) AS total'),
                                DB::raw('SUM(b.diskon_persen/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen'),
                                DB::raw('SUM(a.diskon/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen_vendor')
                            )
                        ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')
                        ->leftjoin('tb_vendor_kerjasama as a','a.id','=','b.id_vendor')
                        //->where('b.created_at', 'LIKE', '%'.$tanggal.'%')
                        ->whereDate('b.tgl_nota','>=', $tgl_awal_baru)
                        ->whereDate('b.tgl_nota','<=', $tgl_akhir_baru)
                        ->where('b.created_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('b.id_apotek_nota','=',$id_apotek)
                        ->where('b.is_deleted', 0)
                        ->where('b.is_kredit', 1)
                        ->where('tb_detail_nota_penjualan.is_cn', 0)
                        ->first();
        
                    $diskon_penjualan_kredit = $detail_penjualan_kredit->total_diskon_persen_vendor + $detail_penjualan_kredit->total_diskon_persen;        
                    $jum_penjualan_debet = count($penjualan_debet);
                    $jum_penjualan_debet2 = count($penjualan_debet2);
                    $a ='';
                    $id_aar = array();
                    if($jum_penjualan_debet > 0) {
                        $a=$a."";
                        $i = 0;
                        foreach($penjualan_debet as $obj) {
                            //$total_debet = number_format($obj->total_debet,0,',',',');
                            if($obj->id_kartu_debet_credit != 0) {
                                $i++;
                                if(isset($id_aar[$obj->id_kartu_debet_credit])) {
                                    $id_aar[$obj->id_kartu_debet_credit] = $id_aar[$obj->id_kartu_debet_credit] + $obj->total_debet;
                                } else {
                                    $id_aar[$obj->id_kartu_debet_credit] = $obj->total_debet;
                                }
                                
                                /*$a=$a."\n".$i.".".$obj->nama_kartu."\n".
                                "  (".$obj->jumlah_transaksi." transaksi) = Rp ".$total_debet;*/
                            }
                        }
                        
                        if($jum_penjualan_debet2 > 0) {
                            $a=$a."** Khusus Pembayaran Kredit";
                            $ii = 0;
                            foreach($penjualan_debet2 as $obj) {
                                //$total_debet = number_format($obj->total_debet,0,',',',');
                                if($obj->id_kartu_debet_credit != 0) {
                                    $ii++;
                                    if(isset($id_aar[$obj->id_kartu_debet_credit])) {
                                        $id_aar[$obj->id_kartu_debet_credit] = $id_aar[$obj->id_kartu_debet_credit] + $obj->total_debet;
                                    } else {
                                        $id_aar[$obj->id_kartu_debet_credit] = $obj->total_debet;
                                    }
                                    
                                    /*$a=$a."\n".$ii.".".$obj->nama_kartu."\n".
                                    "  (".$obj->jumlah_transaksi." transaksi) = Rp ".$total_debet;*/
                                }
                            }
                        }
                    } else {
                        $a=$a."";

                        if($jum_penjualan_debet2 > 0) {
                            $a=$a."\n"."** Khusus Pembayaran Kredit";
                            $ii = 0;
                            foreach($penjualan_debet2 as $obj) {
                                //$total_debet = number_format($obj->total_debet,0,',',',');
                                if($obj->id_kartu_debet_credit != 0) {
                                    $ii++;
                                    if(isset($id_aar[$obj->id_kartu_debet_credit])) {
                                        $id_aar[$obj->id_kartu_debet_credit] = $id_aar[$obj->id_kartu_debet_credit] + $obj->total_debet;
                                    } else {
                                        $id_aar[$obj->id_kartu_debet_credit] = $obj->total_debet;
                                    }

                                    /*$a=$a."\n".$ii.".".$obj->nama_kartu."\n".
                                    "  (".$obj->jumlah_transaksi." transaksi) = Rp ".$total_debet;*/
                                }
                            }
                        }
                    }

                    $t_penjualan_kredit = $rekap->total_penjualan_kredit+$diskon_penjualan_kredit;
                    $array_1 = array(
                        $no, //a
                        $rekap->tanggal, //b
                        $rekap->kasir->nama, //c
                        $shift.' ('.$i_tgl.')', //d
                        $t_penjualan_kredit, //e
                        $diskon_penjualan_kredit,
                        $rekap->jumlah_penjualan, //f
                        $rekap->total_diskon, //g
                        $rekap->total_penjualan, //h
                        $rekap->total_jasa_resep, //i
                        $rekap->total_jasa_dokter, //j
                        $rekap->total_paket_wd, //k
                        $rekap->total_lab, //l
                        $rekap->total_apd, //m
                        $grand_total, //n
                        '', //o
                        $rekap->total_penjualan_cn, //p
                        '', // q
                        $total_2, //r
                        '',//s
                        $rekap->total_switch_cash, //t
                        $rekap->uang_seharusnya, //u
                        $rekap->jumlah_tt, //v
                        $rekap->total_akhir, //w
                        $rekap->total_penjualan_kredit_terbayar, //x
                        $new_total //y
                        //'',
                    );

                    foreach ($kartus as $y => $yval) {
                        array_push($array_1, '');
                    }

                    $collection[] = $array_1;

                    $array_2 = array(
                        '', //a
                        '', //b
                        '', //c
                        'Debet/Kredit', //d
                        '', //e
                        '', //e
                        '', //f
                        '', //g
                        '', //h
                        '', //i
                        '', //j
                        '', //k
                        '', //l
                        '', //m
                        '', //n
                        $rekap->total_debet, //o
                        '', //p
                        $rekap->total_penjualan_cn_debet, // q
                        '', //r
                        $total_debet_x,//s
                        '', //t
                        '', //u
                        '', //v
                        '', //w
                        '', //x
                        ''//y
                        //$a
                    );

                    foreach ($kartus as $y => $yval) {
                        if(isset($id_aar[$yval->id])) {
                            array_push($array_2, $id_aar[$yval->id]);
                        } else {
                            array_push($array_2, '');
                        }
                    }
                    $collection[] = $array_2;

                    $array_3 = array(
                        '', //a
                        '', //b
                        '', //c
                        'Cash', //d
                        '', //e
                        '', //e
                        '', //f
                        '', //g
                        '', //h
                        '', //i
                        '', //j
                        '', //k
                        '', //l
                        '', //m
                        '', //n
                        $rekap->uang_seharusnya, //o
                        '', //p
                        $rekap->total_penjualan_cn_cash, // q
                        '', //r
                        $total_cash_x,//s
                        '', //t
                        '', //u
                        '', //v
                        '', //w
                        '', //x
                        '', //y
                    );

                    foreach ($kartus as $y => $yval) {
                        array_push($array_3, '');
                    }
                    $collection[] = $array_3; 
                }

        $now = date('YmdHis');
        $array_kartu = array(
                                'No', // a
                                'Tanggal', // b 
                                'Kasir',  //c
                                'Shift',  //d
                                'Total Penjualan Kredit', //e
                                'Total Diskon Penjualan Kredit', //e
                                'Jumlah Penjualan', //f
                                'Jumlah Diskon',  //g
                                'Total Penjualan', //h
                                'Total Jasa Resep',  //i
                                'Total Jasa Dokter',  //j
                                'Total Paket WT', //k
                                'Total Lab', //l
                                'Total APD', //m
                                'Total I',  //n
                                'Detail Total I',  //o
                                'Total Retur',  //p
                                'Detail Total Retur',  //q
                                'Total II',  // r
                                'Detail Total II',  // s
                                'Switch Cash',  // t
                                'Uang Seharusnya',  // u
                                'TT',  //v
                                'Total III (Cash)', //w 
                                'Total Pembayaran Penjualan Kredit', //x 
                                'Grand Total', //y
                                //'Keterangan Debet/Credit'
                            );

        foreach ($kartus as $y => $yval) {
            $array_kartu[] = $yval->nama;
        }

        return Excel::download(new class($collection, $array_kartu) implements FromCollection, WithHeadings, WithColumnWidths, WithStyles {

                    public function __construct($collection, $array_kartu)
                    {
                        $this->collection = $collection;
                        $this->array_kartu = $array_kartu;
                    }

                    public function headings(): array
                    {
                        return $this->array_kartu;
                    } 

                    public function columnWidths(): array
                    {
                        return [
                            'A' => 8,
                            'B' => 15,
                            'C' => 30,
                            'D' => 15,
                            'E' => 20,
                            'F' => 25,
                            'G' => 20,
                            'H' => 18,
                            'I' => 18,
                            'J' => 18,
                            'K' => 18,
                            'L' => 18,
                            'M' => 15,
                            'N' => 15,       
                            'O' => 15,
                            'P' => 25, //add
                            'Q' => 15,
                            'R' => 25, //add
                            'S' => 15,
                            'T' => 25, //add
                            'U' => 15,
                            'V' => 20,
                            'W' => 15,
                            'X' => 18,
                            'Y' => 25,
                            'Z' => 20,
                            'AA' => 18,
                            'AB' => 18,
                            'AC' => 18,
                            'AD' => 18,
                            'AE' => 18,
                            'AF' => 18,
                            'AG' => 18,
                            'AH' => 18,
                            'AI' => 18,
                            'AJ' => 18,
                            'AK' => 18,
                            'AL' => 18,
                            'AM' => 18,
                            'AN' => 18,
                            'AO' => 18,
                            'AP' => 18,
                        ];
                    }

                    public function styles(Worksheet $sheet)
                    {
                        return [
                            1    => ['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'A'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'B'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'E' => ['font' => ['bold' => true]],
                            'F' => ['font' => ['bold' => true]],
                            'O' => ['font' => ['bold' => true]],
                            'Q' => ['font' => ['bold' => true]],
                            'P' => ['font' => ['italic' => true]],
                            'R' => ['font' => ['italic' => true]],
                            'T' => ['font' => ['italic' => true]],
                            'S' => ['font' => ['bold' => true]],
                            'X' => ['font' => ['bold' => true]],
                            //'C'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],],
                            /*'D'  => [
                                        'fill' => [
                                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                            'color' => array('rgb' => 'FF0000')
                                        ],
                                    ],*/
                        ];
                    }


                    public function collection()
                    {
                        return $this->collection;
                    }
        },"Rekap Omset_".$now.".xlsx");
    }

    public function hpp() {
        $tahun = date('Y');
        $bulan = date('m');
        $first_day = date('Y-m-d');
        return view('rekap.hpp')->with(compact('tahun', 'bulan', 'first_day'));
    }

    public function list_hpp(Request $request) {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);

        $awal = $request->tgl_awal;
        $akhir = $request->tgl_akhir;
        $tgl_awal_baru = $awal.' 00:00:00';
        $tgl_akhir_baru = $akhir.' 23:59:59';

        DB::statement(DB::raw('set @rownum = 0'));
        $data = TransaksiPenjualanDetail::select([
                    DB::raw('@rownum  := @rownum  + 1 AS no'),
                    'tb_detail_nota_penjualan.*', 
                    'b.diskon_persen', 
                    'b.tgl_nota',
                    'c.harga_beli'
                    //'c.harga_beli_ppn'
                ])
                ->join('tb_nota_penjualan as b', 'b.id', 'tb_detail_nota_penjualan.id_nota')
                ->join('tb_m_stok_harga_'.$inisial.' as c', 'c.id_obat', 'tb_detail_nota_penjualan.id_obat')
                ->where(function($query) use($request, $tgl_awal_baru, $tgl_akhir_baru){
                    $query->where('tb_detail_nota_penjualan.is_deleted','=','0');
                    $query->where('b.id_apotek_nota', '=', session('id_apotek_active'));
                    $query->whereDate('b.created_at','>=', $tgl_awal_baru);
                    $query->whereDate('b.created_at','<=', $tgl_akhir_baru);
                })
                ->groupBy('tb_detail_nota_penjualan.id')
                ->orderBy('tb_detail_nota_penjualan.id', 'ASC');
        
        $datatables = Datatables::of($data);
        return $datatables  
        ->filter(function($query) use($request){
            $query->where(function($query) use($request){
               // $query->orwhere('a.nama','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->editcolumn('created_at', function($data) use($request){
            $string = '';
            if($data->is_penjualan_tolak_cn == 1) {
                $string .='<br><small class="text-red">Penambahan dari penolakan retur</small>';
            }
            return Carbon::parse($data->created_at)->format('d/m/Y H:i:s').$string;
        })
        ->editcolumn('id_obat', function($data) {
            return $data->obat->nama;
        })  
        ->editcolumn('diskon', function($data) {
            if(empty($data->total_diskon)) {
                $diskon = 0;
            } else {
                $diskon = $data->total_diskon;
            }
            return "Rp ".number_format($diskon,0);
        })  
        ->editcolumn('is_cn', function($data) use($request){
            if($data->is_cn == 1) {
                $string = '<b style="color:#d32f2f;">R</b>';
            } else {
                $string = "-";
            }
           
            return $string;
        })   
        ->editcolumn('total', function($data) {    
            $jumlah = $data->jumlah-$data->jumlah_cn;
            $total = $jumlah * $data->harga_jual;
            return "Rp ".number_format($total,0);
        })    
        ->editcolumn('harga_beli_ppn', function($data) {
            $harga_pokok = $data->hb_ppn;
            $jumlah = $data->jumlah-$data->jumlah_cn;
            $total = $jumlah * $data->harga_jual;
            $total_hp = $jumlah*$harga_pokok;
            $laba = $total-$total_hp;
            if($laba < 0) {
                $harga_pokok = $data->harga_beli;
            } 
            return "Rp ".number_format($harga_pokok,0);
        })  
        ->editcolumn('total_hp', function($data) {
            $harga_pokok = $data->hb_ppn;
            $jumlah = $data->jumlah-$data->jumlah_cn;
            $total = $jumlah * $data->harga_jual;
            $total_hp = $jumlah*$harga_pokok;
            $laba = $total-$total_hp;

            if($laba < 0) {
                $total_hp = $jumlah * $data->harga_beli;
            } 
            return "Rp ".number_format($total_hp,0);
        }) 
        ->editcolumn('jumlah_cn', function($data) {
            return $data->jumlah_cn;
        })  
        ->editcolumn('jumlah', function($data) {
            $jumlah = $data->jumlah-$data->jumlah_cn;
            return $jumlah;
        })  
        ->editcolumn('laba', function($data) {
            $harga_pokok = $data->hb_ppn;
            $jumlah = $data->jumlah-$data->jumlah_cn;
            $total = $jumlah * $data->harga_jual;
            $total_hp = $jumlah*$harga_pokok;

            $laba = $this->cek_laba(1, $jumlah, $total_hp, $total, $data->harga_beli);

            return "Rp ".number_format($laba,0);
        })  
        ->editcolumn('persentase_laba', function($data) {
            $harga_pokok = $data->hb_ppn;
            $jumlah = $data->jumlah-$data->jumlah_cn;
            $total = $jumlah * $data->harga_jual;
            $total_hp = $jumlah*$harga_pokok;
            
            $persentase_laba = $this->cek_laba(2, $jumlah, $total_hp, $total, $data->harga_beli);

            return number_format($persentase_laba,2).'%';
        })  
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['action', 'is_cn', 'diskon', 'total', 'harga_beli_ppn', 'total_hp', 'jumlah', 'jumlah_cn', 'laba', 'persentase_laba'])
        ->addIndexColumn()
        ->make(true);  
    }

    public function cek_laba($cek_what, $jumlah, $total_hp, $total, $harga_beli)
    {
        $laba = $total-$total_hp;
        if($laba < 0) {
            if($total_hp < 0) {
                $total_hp = $jumlah * $harga_beli;
                $laba = $total-$total_hp;
                $persentase_laba = ($laba/$total_hp)*100;
            } else {
                $laba = 0;
                $persentase_laba = 0;
            }
        } else {
            if($total_hp > 0) {
                $persentase_laba = ($laba/$total_hp)*100;
            } else {
                $total_hp = $jumlah * $harga_beli;
                if($total_hp > 0) {
                    $laba = $total-$total_hp;
                    $persentase_laba = ($laba/$total_hp)*100;
                } else {
                    $laba = 0;
                    $persentase_laba = 0;
                }
            }
        }
        if($cek_what == 1) {
            return $laba;
        } else {
            return $persentase_laba;
        }
    }

    public function export_hpp(Request $request) 
    {
        ini_set('memory_limit','-1');
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
       /* $monthNum = preg_replace('/0/', '', $request->bulan, 1);
        $bulan = date('F', mktime(0, 0, 0, $monthNum, 10));
        
        $timestamp    = strtotime($bulan.' '.$request->tahun);
        $first_second = date('01', $timestamp);
        $last_second  = date('t', $timestamp); 

        $first = preg_replace('/0/', '', $first_second, 1);
        $last = $last_second;//preg_replace('/0/', '', $last_second, 1);
        */

        $collection = collect();
        $no = 0;
        $total_excel=0;
        $tanggal = '';
        $i_tgl = 0;
        
        $strDateFrom = $request->tgl_awal;
        $strDateTo = $request->tgl_akhir;
        $all = $this->createDateRangeArray($strDateFrom,$strDateTo);
        foreach ($all as $key => $date) {
           /* $num_tgl_ = sprintf("%02d", $i);
            $str_tgl_ = $request->tahun.'-'.$request->bulan.'-'.$num_tgl_;*/
            $tgl_ = date('Y-m-d', strtotime($date));
            $rekaps = TransaksiPenjualanDetail::select([
                                    DB::raw('@rownum  := @rownum  + 1 AS no'),
                                    'tb_detail_nota_penjualan.*', 
                                    'b.diskon_persen', 
                                    'b.tgl_nota',
                                    'c.harga_beli'
                                    //'c.harga_beli_ppn'
                                ])
                                ->join('tb_nota_penjualan as b', 'b.id', 'tb_detail_nota_penjualan.id_nota')
                                ->leftjoin('tb_m_stok_harga_'.$inisial.' as c', 'c.id_obat', 'tb_detail_nota_penjualan.id_obat')
                                ->where(function($query) use($request, $tgl_){
                                    $query->where('tb_detail_nota_penjualan.is_deleted','=','0');
                                    $query->where('b.id_apotek_nota', '=', session('id_apotek_active'));
                                   // $query->where(DB::raw('YEAR(b.tgl_nota)'), $request->tahun);
                                    //$query->where(DB::raw('MONTH(b.tgl_nota)'), $request->bulan);
                                    $query->whereDate('b.tgl_nota', $tgl_);
                                    $query->where('b.is_deleted',0);
                                })
                                ->groupBy('tb_detail_nota_penjualan.id')
                                ->orderBy('tb_detail_nota_penjualan.id', 'ASC')
                                ->get();

            $x = 0;
            foreach($rekaps as $rekap) {
                $no++;
                $x++;
                if($x == 1) {
                    $tanggal = $tgl_;
                } else {
                    $tanggal = '';
                }

                $jumlah_cn = $rekap->jumlah_cn;
                if(empty($jumlah_cn)) {
                    $jumlah_cn = 0;
                }
                
                $harga_pokok = $rekap->hb_ppn;
                $jumlah = $rekap->jumlah-$jumlah_cn;
                $total = $jumlah * $rekap->harga_jual;
                $total_diskon = ($rekap->diskon_persen/100) * $total;
                $total_hp = $jumlah*$harga_pokok;
                $laba = $this->cek_laba(1, $jumlah, $total_hp, $total, $rekap->harga_beli);
                $persentase_laba = $this->cek_laba(2, $jumlah, $total_hp, $total, $rekap->harga_beli);

                if(empty($total_diskon)) {
                    $diskon = 0;
                } else {
                    $diskon = $total_diskon;
                }
                $keterangan = 'dihitung dari harga beli + ppn';

                if($laba < 0) {
                    $total_hp = $jumlah * $rekap->harga_beli;
                    $harga_pokok = $rekap->harga_beli;
                    $laba = $this->cek_laba(1, $jumlah, $total_hp, $total, $rekap->harga_beli);
                    $persentase_laba = $this->cek_laba(2, $jumlah, $total_hp, $total, $rekap->harga_beli);
                    $keterangan = 'harga beli + ppn tidak sesuai (dihitung dari harga beli)';
                } 
                
                if($jumlah_cn == 0) {
                    $jumlah_cn = '0';
                }

                if($jumlah == 0) {
                    $jumlah = '0';
                }

                if($total == 0) {
                    $total = '0';
                }

                if($total_hp == 0) {
                    $total_hp = '0';
                }

                if($rekap->obat->id_produsen == '') {
                    $produsen = '';
                } else {
                    $produsen = $rekap->obat->produsen->nama;
                }

                if($rekap->obat->id_penandaan_obat == '') {
                    $penandaan_obat = '';
                } else {
                    $penandaan_obat = $rekap->obat->penandaan_obat->nama;
                }

                if($rekap->obat->id_satuan == '' OR $rekap->obat->id_satuan == null OR $rekap->obat->id_satuan == 0) {
                    $satuan = '';
                } else {
                    $satuan = $rekap->obat->satuan->satuan;
                }
                
                $collection[] = array(
                    $no, //a
                    $tanggal, //b
                    $diskon, //c
                    $rekap->obat->nama, //d
                    $rekap->jumlah, //e
                    $jumlah_cn, 
                    $jumlah,
                    $rekap->harga_jual, //f
                    $total, //g
                    $harga_pokok, //h
                    $total_hp, //i
                    $laba, //j
                    number_format($persentase_laba,2).'%', //k
                    $keterangan,
                    $produsen,
                    $penandaan_obat,
                    $satuan
                );
            }
        }
                

        $now = date('YmdHis'); // WithColumnFormatting
        return Excel::download(new class($collection) implements FromCollection, WithHeadings, WithColumnWidths, WithStyles {

                    public function __construct($collection)
                    {
                        $this->collection = $collection;
                    }

                    public function headings(): array
                    {
                        return [
                                'No', // a
                                'Tanggal', // b 
                                'Diskon',  //c
                                'Nama Obat',  //d
                                'Jumlah Jual', //e
                                'Jumlah Retur', //f
                                'Jumlah', //g
                                'Harga Jual', //h
                                'Total HJ',  //i
                                'Harga Pokok', //j
                                'Total HP',  //k
                                'Laba',  //l
                                'Persentase Laba', //m
                                'Keterangan', //n,
                                'Penandaan Obat', //o
                                'Produsen', //p
                                'Satuan', 
                            ];
                    } 

                    /*public function columnFormats(): array
                    {
                        return [
                            'F' => NumberFormat::FORMAT_NUMBER,
                            'G' => NumberFormat::FORMAT_NUMBER,
                        ];
                    }*/

                    public function columnWidths(): array
                    {
                        return [
                            'A' => 8,
                            'B' => 15,
                            'C' => 15,
                            'D' => 45,
                            'E' => 15,
                            'F' => 15,
                            'G' => 10,
                            'H' => 15,
                            'I' => 15,
                            'J' => 15,
                            'K' => 15,
                            'L' => 15,
                            'M' => 18,
                            'N' => 30,
                            'O' => 30,
                            'P' => 30,
                            'Q' => 18,
                        ];
                    }

                    public function styles(Worksheet $sheet)
                    {
                        return [
                            1    => ['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'A'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'M'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'C'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                            'H'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                            'I'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                            'J'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                            'K'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                            'L'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                        ];
                    }


                    public function collection()
                    {
                        return $this->collection;
                    }
        },"HPP_".$strDateFrom."_to_".$strDateTo.".xlsx");
    }

    public function createDateRangeArray($strDateFrom,$strDateTo) {
        $aryRange = [];

        $iDateFrom = mktime(1, 0, 0, substr($strDateFrom, 5, 2), substr($strDateFrom, 8, 2), substr($strDateFrom, 0, 4));
        $iDateTo = mktime(1, 0, 0, substr($strDateTo, 5, 2), substr($strDateTo, 8, 2), substr($strDateTo, 0, 4));

        if ($iDateTo >= $iDateFrom) {
            array_push($aryRange, date('Y-m-d', $iDateFrom)); // first entry
            while ($iDateFrom<$iDateTo) {
                $iDateFrom += 86400; // add 24 hours
                array_push($aryRange, date('Y-m-d', $iDateFrom));
            }
        }
        return $aryRange;
    }

    public function export_hpp_v2(Request $request) 
    {
       // dd($request->bulan);
        ini_set('memory_limit','-1');
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $monthNum = preg_replace('/0/', '', $request->bulan, 1);
        $bulan = date('F', mktime(0, 0, 0, $monthNum, 10));
        
        $timestamp    = strtotime($bulan.' '.$request->tahun);
        $first_second = date('01', $timestamp);
        $last_second  = date('t', $timestamp); 

        $first = preg_replace('/0/', '', $first_second, 1);
        $last = preg_replace('/0/', '', $last_second, 1);

        $collection = collect();
        $no = 0;
        $total_excel=0;
        $tanggal = '';
        $i_tgl = 0;
        for ($i=$first; $i <= $last; $i++) { 
            $num_tgl_ = sprintf("%02d", $i);
            $str_tgl_ = $request->tahun.'-'.$request->bulan.'-'.$num_tgl_;
            $tgl_ = date('Y-m-d', strtotime($str_tgl_));
            $rekaps = TransaksiPenjualanDetail::select([
                                    DB::raw('@rownum  := @rownum  + 1 AS no'),
                                    'tb_detail_nota_penjualan.id_obat',
                                    DB::raw('(tb_detail_nota_penjualan.jumlah) as  total_jual'), 
                                    'b.diskon_persen', 
                                    'b.tgl_nota',
                                    'c.harga_beli',
                                    'c.harga_beli_ppn'
                                ])
                                ->join('tb_nota_penjualan as b', 'b.id', 'tb_detail_nota_penjualan.id_nota')
                                ->leftjoin('tb_m_stok_harga_'.$inisial.' as c', 'c.id_obat', 'tb_detail_nota_penjualan.id_obat')
                                ->where(function($query) use($request, $tgl_){
                                    $query->where('tb_detail_nota_penjualan.is_deleted','=','0');
                                    $query->where('b.id_apotek_nota', '=', session('id_apotek_active'));
                                   // $query->where(DB::raw('YEAR(b.tgl_nota)'), $request->tahun);
                                    //$query->where(DB::raw('MONTH(b.tgl_nota)'), $request->bulan);
                                    $query->whereDate('b.tgl_nota', $tgl_);
                                    $query->where('b.is_deleted',0);
                                })
                                ->groupBy('tb_detail_nota_penjualan.id_obat')
                                ->orderBy('tb_detail_nota_penjualan.id', 'ASC')
                                ->get();

            $x = 0;
            foreach($rekaps as $rekap) {
                $no++;
                $x++;
                if($x == 1) {
                    $tanggal = $tgl_;
                } else {
                    $tanggal = '';
                }

                $jumlah_cn = $rekap->jumlah_cn;
                if(empty($jumlah_cn)) {
                    $jumlah_cn = 0;
                }
                
                $harga_pokok = $rekap->harga_beli_ppn;
                $jumlah = $rekap->jumlah-$jumlah_cn;
                $total = $jumlah * $rekap->harga_jual;
                $total_diskon = ($rekap->diskon_persen/100) * $total;
                $total_hp = $jumlah*$harga_pokok;
                $laba = $this->cek_laba(1, $jumlah, $total_hp, $total, $rekap->harga_beli);
                $persentase_laba = $this->cek_laba(2, $jumlah, $total_hp, $total, $rekap->harga_beli);

                if(empty($total_diskon)) {
                    $diskon = 0;
                } else {
                    $diskon = $total_diskon;
                }
                $keterangan = 'dihitung dari harga beli + ppn';

                if($laba < 0) {
                    $total_hp = $jumlah * $rekap->harga_beli;
                    $harga_pokok = $rekap->harga_beli;
                    $laba = $this->cek_laba(1, $jumlah, $total_hp, $total, $rekap->harga_beli);
                    $persentase_laba = $this->cek_laba(2, $jumlah, $total_hp, $total, $rekap->harga_beli);
                    $keterangan = 'harga beli + ppn tidak sesuai (dihitung dari harga beli)';
                } 
                
                if($jumlah_cn == 0) {
                    $jumlah_cn = '0';
                }

                if($jumlah == 0) {
                    $jumlah = '0';
                }

                if($total == 0) {
                    $total = '0';
                }

                if($total_hp == 0) {
                    $total_hp = '0';
                }
                
                $collection[] = array(
                    $no, //a
                    $tanggal, //b
                    $diskon, //c
                    $rekap->obat->nama, //d
                    $rekap->jumlah, //e
                    $jumlah_cn, 
                    $jumlah,
                    $rekap->harga_jual, //f
                    $total, //g
                    $harga_pokok, //h
                    $total_hp, //i
                    $laba, //j
                    number_format($persentase_laba,2).'%', //k
                    $keterangan
                );
            }
        }
                

        $now = date('YmdHis'); // WithColumnFormatting
        return Excel::download(new class($collection) implements FromCollection, WithHeadings, WithColumnWidths, WithStyles {

                    public function __construct($collection)
                    {
                        $this->collection = $collection;
                    }

                    public function headings(): array
                    {
                        return [
                                'No', // a
                                'Tanggal', // b 
                                'Diskon',  //c
                                'Nama Obat',  //d
                                'Jumlah Jual', //e
                                'Jumlah Retur', //f
                                'Jumlah', //g
                                'Harga Jual', //h
                                'Total HJ',  //i
                                'Harga Pokok', //j
                                'Total HP',  //k
                                'Laba',  //l
                                'Persentase Laba', //m
                                'Keterangan' //n
                            ];
                    } 

                    /*public function columnFormats(): array
                    {
                        return [
                            'F' => NumberFormat::FORMAT_NUMBER,
                            'G' => NumberFormat::FORMAT_NUMBER,
                        ];
                    }*/

                    public function columnWidths(): array
                    {
                        return [
                            'A' => 8,
                            'B' => 15,
                            'C' => 15,
                            'D' => 45,
                            'E' => 15,
                            'F' => 15,
                            'G' => 10,
                            'H' => 15,
                            'I' => 15,
                            'J' => 15,
                            'K' => 15,
                            'L' => 15,
                            'M' => 18,
                            'N' => 30,
                        ];
                    }

                    public function styles(Worksheet $sheet)
                    {
                        return [
                            1    => ['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'A'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'M'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'C'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                            'H'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                            'I'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                            'J'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                            'K'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                            'L'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                        ];
                    }


                    public function collection()
                    {
                        return $this->collection;
                    }
        },"HPP_Group_".$now.".xlsx");
    }

    public function export_penjualan_kredit(Request $request) 
    {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $tgl_penjualan = $request->tgl_penjualan;
        $split           = explode(" - ", $tgl_penjualan);
        $date1 = strtr($split[0], '/', '-');
        $date2 = strtr($split[1], '/', '-');
        $date1 = date('Y-m-d', strtotime($date1));
        $date2 = date('Y-m-d', strtotime($date2));

        $rekaps = TransaksiPenjualan::select([
                                    DB::raw('@rownum  := @rownum  + 1 AS no'),
                                    'tb_nota_penjualan.*'
                                ])
                                ->where(function($query) use($request, $date1, $date2){
                                    $query->where('tb_nota_penjualan.is_deleted','=','0');
                                    $query->where('tb_nota_penjualan.is_kredit','=','1');
                                    $query->where('tb_nota_penjualan.id_apotek_nota','=',session('id_apotek_active'));
                                    if($request->id_vendor != '') {
                                        $query->where('tb_nota_penjualan.id_vendor', $request->id_vendor);
                                    }
                                    $query->where('tb_nota_penjualan.tgl_nota', '>=', $date1);
                                    $query->where('tb_nota_penjualan.tgl_nota', '<=', $date2);
                                    if($request->keterangan != '') {
                                        $query->where('tb_nota_penjualan.keterangan','LIKE','%'.$request->keterangan.'%');
                                    }
                                    if($request->is_lunas_pembayaran_kredit != '') {
                                        $query->where('tb_nota_penjualan.is_lunas_pembayaran_kredit','=',$request->is_lunas_pembayaran_kredit);
                                    }
                                })
                                ->groupBy('tb_nota_penjualan.id')
                                ->get();

                $collection = collect();
                $no = 0;
                $total_excel=0;
                foreach($rekaps as $data) {
                    $no++;
                    $lunas = 'Belum Lunas';
                    $oleh = '';
                    $tgl = '';
                    if($data->is_lunas_pembayaran_kredit == 1) {
                        $lunas = 'Lunas';
                        $oleh = $data->lunas_oleh->nama;
                        $tgl = Carbon::parse($data->is_lunas_pembayaran_kredit_at)->format('d/m/Y H:i:s');
                    }

                    $total = $data->detail_penjualan_total[0]->total;
                    if($total == "" || $total == null) {
                        $total = 0;
                    }

                    $total = $data->detail_penjualan_total[0]->total;
                    if($total == "" || $total == null) {
                        $total = 0;
                    }
                    $total_fix = $total+$data->biaya+$data->biaya_jasa_dokter;
                    
                    if($data->cash != 0 && $data->debet != 0) {
                        if(!empty($data->kartu)) {
                            $kartu = $data->kartu->nama;
                        } else {
                            $kartu = "Tidak diinputkan";
                        }
                        $metode = 'Gabung ('.$kartu.')';
                    } else {
                        if($data->cash != 0) {
                            $metode = 'Cash';
                        } else {
                            if($data->debet != 0) {
                                if(!empty($data->kartu)) {
                                    $kartu = $data->kartu->nama;
                                } else {
                                    $kartu = "Tidak diinputkan";
                                }
                                $metode = 'Debet/Credit ('.$kartu.')';
                            } else {
                                $metode = '';
                            }
                        }
                    }
                    $collection[] = array(
                        $no, //a
                        Carbon::parse($data->created_at)->format('d/m/Y H:i:s'), //b
                        $data->vendor->nama, //c
                        $total, //d
                        $data->biaya_resep, //e
                        $data->biaya_jasa_dokter,  //f
                        $total_fix, //g
                        $metode, //h
                        $data->cash, //i
                        $data->debet, //j
                        $lunas, //k
                        $oleh, //l
                        $tgl, //m
                        $data->keterangan //n
                    );
                }

        $now = date('YmdHis'); // WithColumnFormatting
        return Excel::download(new class($collection) implements FromCollection, WithHeadings, WithColumnWidths, WithStyles {

                    public function __construct($collection)
                    {
                        $this->collection = $collection;
                    }

                    public function headings(): array
                    {
                        return [
                                'No', // a
                                'Tanggal', // b 
                                'Penjualan Melaui', //c
                                'Penjualan Item ',  //d
                                'Jasa Resep',  //e
                                'Jasa Dokter', //f
                                'Total', //g
                                'Metode', //h
                                'Cash', //i
                                'Debet/Credit',  //j
                                'Status', //k
                                'Lunas Oleh',  //l
                                'Tanggal Lunas',  //m
                                'Keterangan' //n
                            ];
                    } 

                    /*public function columnFormats(): array
                    {
                        return [
                            'F' => NumberFormat::FORMAT_NUMBER,
                            'G' => NumberFormat::FORMAT_NUMBER,
                        ];
                    }*/

                    public function columnWidths(): array
                    {
                        return [
                            'A' => 8,
                            'B' => 15,
                            'C' => 25,
                            'D' => 20,
                            'E' => 18,
                            'F' => 18,
                            'G' => 15,
                            'H' => 25,
                            'I' => 15,
                            'J' => 18,
                            'K' => 15,
                            'L' => 25,
                            'M' => 15,
                            'N' => 30,
                        ];
                    }

                    public function styles(Worksheet $sheet)
                    {
                        return [
                            1    => ['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'A'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'M'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'D'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                            'E'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                            'F'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                            'G'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                            'I'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                            'J'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                        ];
                    }


                    public function collection()
                    {
                        return $this->collection;
                    }
        },"Rekap Penjualan Kredit_".$apotek->nama."_".$now.".xlsx");
    }

    public function export_all(Request $request) 
    {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $tanggal = $request->tanggal;
        $rekaps = TransaksiPenjualan::select([
                            DB::raw('@rownum  := @rownum  + 1 AS no'),
                            'tb_nota_penjualan.*', 
                    ])
                    ->where(function($query) use($request, $tanggal){
                        $query->where('tb_nota_penjualan.is_deleted','=','0');
                        if($request->id_apotek != '') {
                            $query->where('tb_nota_penjualan.id_apotek_nota', $request->id_apotek);
                        } else {
                            $query->where('tb_nota_penjualan.id_apotek_nota','=',session('id_apotek_active'));
                        }

                        //$query->where('tb_nota_penjualan.is_lunas_pembayaran_kredit','=','1');
                        //$query->where('tb_nota_penjualan.is_kredit','=','0');

                        if(empty($request->tanggal)) {
                            $query->where('tb_nota_penjualan.created_at', 'LIKE', '%'.$tanggal.'%');
                        } else {
                            $query->where('tb_nota_penjualan.id','LIKE',($request->id > 0 ? $request->id : '%'.$request->id.'%'));
                            //$query->where('tb_nota_penjualan.id_pasien','LIKE',($request->id_pasien > 0 ? $request->id_pasien : '%'.$request->id_pasien.'%'));
                            if($request->tanggal != "") {
                                $split                      = explode("-", $request->tanggal);
                                $tgl_awal       = date('Y-m-d H:i:s',strtotime($split[0]));
                                $tgl_akhir      = date('Y-m-d H:i:s',strtotime($split[1]));
                                $query->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal);
                                $query->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir);
                            }
                        }

                        if($request->id_user != '') {
                            $query->where('tb_nota_penjualan.created_by', $request->id_user);
                        }
                    })
                    ->groupBy('tb_nota_penjualan.id')
                    ->get();

                $collection = collect();
                $no = 0;
                $total_excel=0;
                foreach($rekaps as $data) {
                    $no++;
                    $lunas = 'Belum Lunas';
                    $oleh = '';
                    $tgl = '';
                    

                    $total = $data->detail_penjualan_total[0]->total;
                    if($total == "" || $total == null) {
                        $total = 0;
                    }

                    $total = $data->detail_penjualan_total[0]->total;
                    if($total == "" || $total == null) {
                        $total = 0;
                    }
                    $total_fix = $total+$data->biaya+$data->biaya_jasa_dokter;
                    
                    if($data->cash != 0 && $data->debet != 0) {
                        if(!empty($data->kartu)) {
                            $kartu = $data->kartu->nama;
                        } else {
                            $kartu = "Tidak diinputkan";
                        }
                        $metode = 'Gabung ('.$kartu.')';
                    } else {
                        if($data->cash != 0) {
                            $metode = 'Cash';
                        } else {
                            if($data->debet != 0) {
                                if(!empty($data->kartu)) {
                                    $kartu = $data->kartu->nama;
                                } else {
                                    $kartu = "Tidak diinputkan";
                                }
                                $metode = 'Debet/Credit ('.$kartu.')';
                            } else {
                                $metode = '';
                            }
                        }
                    }

                    if($data->is_kredit == 1) {
                        $status_p = "Kredit";
                        if($data->is_lunas_pembayaran_kredit == 1) {
                            $lunas = 'Lunas';
                            $oleh = $data->lunas_oleh->nama;
                            $tgl = Carbon::parse($data->is_lunas_pembayaran_kredit_at)->format('d/m/Y H:i:s');
                        }
                    } else {
                        $status_p = "Non Kredit";
                        $lunas = 'Lunas';
                        $oleh = $data->created_oleh->nama;
                        $tgl = Carbon::parse($data->created_at)->format('d/m/Y H:i:s');
                    }
                    
                    $collection[] = array(
                        $no, //a
                        Carbon::parse($data->created_at)->format('d/m/Y H:i:s'), //b
                        'ID.'.$data->id.'|'.$status_p, //c
                        $total, //d
                        $data->biaya_resep, //e
                        $data->biaya_jasa_dokter,  //f
                        $data->harga_wd,  
                        $data->biaya_lab,  
                        $data->biaya_apd,  
                        $total_fix, //g
                        $metode, //h
                        $data->cash, //i
                        $data->debet, //j
                        $lunas, //k
                        $oleh, //l
                        $tgl, //m
                        $data->keterangan //n
                    );
                }

        $now = date('YmdHis'); // WithColumnFormatting
        return Excel::download(new class($collection) implements FromCollection, WithHeadings, WithColumnWidths, WithStyles {

                    public function __construct($collection)
                    {
                        $this->collection = $collection;
                    }

                    public function headings(): array
                    {
                        return [
                                'No', // a
                                'Tanggal', // b 
                                'Status', //c
                                'Penjualan Item ',  //d
                                'Jasa Resep',  //e
                                'Jasa Dokter', //f
                                'Paket WD', //g
                                'Lab', //h
                                'APD', //i
                                'Total', //j
                                'Metode', //h
                                'Cash', //i
                                'Debet/Credit',  //j
                                'Status', //k
                                'Lunas Oleh',  //l
                                'Tanggal Lunas',  //m
                                'Keterangan' //n
                            ];
                    } 

                    /*public function columnFormats(): array
                    {
                        return [
                            'F' => NumberFormat::FORMAT_NUMBER,
                            'G' => NumberFormat::FORMAT_NUMBER,
                        ];
                    }*/

                    public function columnWidths(): array
                    {
                        return [
                            'A' => 8,
                            'B' => 15,
                            'C' => 25,
                            'D' => 20,
                            'E' => 18,
                            'F' => 18,
                            'G' => 18,
                            'H' => 18,
                            'I' => 18,
                            'J' => 15,
                            'K' => 25,
                            'L' => 15,
                            'M' => 18,
                            'N' => 15,
                            'O' => 25,
                            'P' => 15,
                            'Q' => 30,
                        ];
                    }

                    public function styles(Worksheet $sheet)
                    {
                        return [
                            1    => ['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'A'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'M'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'D'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                            'E'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                            'F'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                            'G'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                            'H'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                            'I'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                            'J'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                            'L'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                            'M'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                        ];
                    }


                    public function collection()
                    {
                        return $this->collection;
                    }
        },"Rekap Penjualan All_".$apotek->nama."_".$now.".xlsx");
    }


    public function hapus_detail($id) {
        DB::beginTransaction(); 
        try{
            $detail_penjualan = TransaksiPenjualanDetail::find($id);
            $detail_penjualan->is_deleted = 1;
            $detail_penjualan->deleted_at= date('Y-m-d H:i:s');
            $detail_penjualan->deleted_by = Auth::user()->id;

            $penjualan = TransaksiPenjualan::find($detail_penjualan->id_nota);
            $apotek = MasterApotek::find(session('id_apotek_active'));
            $inisial = strtolower($apotek->nama_singkat);

            $stok_before = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $detail_penjualan->id_obat)->first();
            $jumlah = $detail_penjualan->jumlah;
            $stok_now = $stok_before->stok_akhir+$jumlah;

            # update ke table stok harga
            DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $detail_penjualan->id_obat)->update(['stok_awal'=> $stok_before->stok_akhir, 'stok_akhir'=> $stok_now, 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => Auth::user()->id]);

            # create histori
            DB::table('tb_histori_stok_'.$inisial)->insert([
                'id_obat' => $detail_penjualan->id_obat,
                'jumlah' => $jumlah,
                'stok_awal' => $stok_before->stok_akhir,
                'stok_akhir' => $stok_now,
                'id_jenis_transaksi' => 15, //hapus penjualan
                'id_transaksi' => $detail_penjualan->id,
                'batch' => null,
                'ed' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => Auth::user()->id
            ]);  

            $total = TransaksiPenjualanDetail::select([
                                DB::raw('SUM((harga_jual*jumlah)-diskon) as total_all')
                                ])
                                ->where('id', '!=', $detail_penjualan->id)
                                ->where('id_nota', $detail_penjualan->id_nota)
                                ->where('is_deleted', 0)
                                ->first();
            $y = 0;
            if($total->total_all == 0 OR $total->total_all == '') {
                $y = 0;
            } else {
                $y = $total->total_all;
            }

            if($y == 0) {
                $penjualan->total_belanja = $y;
                $penjualan->is_deleted = 1;
                $penjualan->deleted_at= date('Y-m-d H:i:s');
                $penjualan->deleted_by = Auth::user()->id;
            }

            if($detail_penjualan->save()){
                $penjualan->save();
                DB::commit();
                echo 1;
            }else{
                echo 0;
            }
        }catch(\Exception $e){
            DB::rollback();
            session()->flash('error', 'Error!');
            return redirect('penjualan/detail/'.$penjualan->id);
        }
    }

    public function cek_diskon(Request $request) {
        $array_diskon = array();
        $data = $request->data;
        $counter = $request->counter;
        $date_now = date('Y-m-d');
        $array = array();
        $id_apotek = session('id_apotek_active');
        $id_member_tipe = 7;
        if($data['id_pasien'] != '') {
            $pasien = MasterMember::find($data['id_pasien']);
            $id_member_tipe = $pasien->id_tipe_member;
        }

        for ($i=0; $i <= $counter ; $i++) { 
            $val = $data['detail_penjualan['.$i];
            $array[] = $val;
            // cari diskon per item
            $cek = SettingPromoItemBeli::select(['tb_setting_promo_item_beli.*', 'a.id_member_tipe', 'a.id_apotek'])
                    ->join('tb_setting_promo as a', 'a.id', '=', 'tb_setting_promo_item_beli.id_setting_promo')
                    ->where('tb_setting_promo_item_beli.id_obat', $val['id_obat'])
                    ->whereDate('a.tgl_awal','>=', $date_now)
                    ->whereDate('a.tgl_awal','<=', $date_now)
                    ->OrWhere('tb_setting_promo_item_beli.id_obat', $val['id_obat'])
                    ->whereDate('a.tgl_akhir','>=', $date_now)
                    ->whereDate('a.tgl_akhir','<=', $date_now)
                    ->get();

            foreach ($cek as $key => $obj) {
                $split = explode(",", $obj->id_member_tipe);
                $split_apotek = explode(",", $obj->id_apotek);
                if($val['jumlah'] >= $obj->jumlah AND in_array($id_member_tipe, $split) AND in_array($id_apotek, $split_apotek)){
                    $array_diskon = $obj;
                }
            }
        }

        // cari diskon per kombinasi item
        $diskon_sub = SettingPromo::select([
                            'tb_setting_promo.*'
                    ])
                    ->where(function($query) use($request, $date_now){
                        $query->where('tb_setting_promo.is_deleted','=','0');
                        $query->where('tb_setting_promo.is_sub','=','1');
                        $query->whereDate('tb_setting_promo.tgl_awal','>=', $date_now);
                        $query->whereDate('tb_setting_promo.tgl_awal','<=', $date_now);
                        $query->OrWhere('tb_setting_promo.is_deleted','=','0');
                        $query->where('tb_setting_promo.is_sub','=','1');
                        $query->whereDate('tb_setting_promo.tgl_akhir','>=', $date_now);
                        $query->whereDate('tb_setting_promo.tgl_akhir','<=', $date_now);
                    })
                    ->get();

        foreach ($diskon_sub as $key => $val) {
            
        }
        print_r($diskon_sub);
        exit();
    }

    public function cek_diskon_item(Request $request) {
        if($request->jumlah == "") {
            $data = array('is_data' => 0);
        } else {
            $data_ = SettingPromoItemBeli::select(['tb_setting_promo_item_beli.*', 'tb_setting_promo.id_jenis_promo', 'tb_setting_promo.persen_diskon', 'tb_setting_promo.rp_diskon'])
                        ->join('tb_setting_promo', 'tb_setting_promo.id', '=', 'tb_setting_promo_item_beli.id_setting_promo')
                        ->where('tb_setting_promo_item_beli.id_obat', $request->id_obat)
                        ->where('tb_setting_promo_item_beli.jumlah', $request->jumlah)
                        ->first();
                        
            if(!empty($data_)) {
                # cek apotek
                $cek_ap_ = SettingPromoDetail::where('id_setting_promo', $data_->id_setting_promo)->where('id_apotek', session('id_apotek_active'))->first();
                if(!empty($cek_ap_)) {
                    $cek_ = 1;
                    if($data_->id_jenis_promo == 1) {
                        $diskon = ($data_->persen_diskon/100*$request->harga_jual)*$request->jumlah;
                    } else{
                        $diskon = $data_->rp_diskon;
                    }

                    $data = array('is_data' => 1, 'data'=> $data_, 'diskon' => $diskon);
                } else {
                    $data = array('is_data' => 0);
                }
            } else {
                $data = array('is_data' => 0);
            }
        }
        return json_encode($data);
    }

    public function cetak_nota_thermal($id) {
        $penjualan = TransaksiPenjualan::where('id', $id)->first();
        if($penjualan->id_jasa_resep == "" || $penjualan->id_jasa_resep == 0 || $penjualan->id_jasa_resep == null || $penjualan->id_jasa_resep == '0') {
            $penjualan->jasa_resep = 0;
        } else {
            $jasa_resep = MasterJasaResep::find($penjualan->id_jasa_resep);
            $penjualan->jasa_resep = $jasa_resep->biaya;
        }
        $detail_penjualans = TransaksiPenjualanDetail::select(['tb_detail_nota_penjualan.id',
                                                'tb_detail_nota_penjualan.id_nota',
                                                'tb_detail_nota_penjualan.id_obat',
                                                'tb_detail_nota_penjualan.jumlah',
                                                'tb_detail_nota_penjualan.harga_jual',
                                                'tb_detail_nota_penjualan.diskon',
                                                'tb_m_obat.nama',
                                                 DB::raw('(tb_detail_nota_penjualan.jumlah * tb_detail_nota_penjualan.harga_jual) - tb_detail_nota_penjualan.diskon  as total')])
                                               ->join('tb_m_obat', 'tb_m_obat.id', '=', 'tb_detail_nota_penjualan.id_obat')
                                               ->where('tb_detail_nota_penjualan.id_nota', $id)
                                               ->where('tb_detail_nota_penjualan.is_deleted', 0)
                                               ->get();
        $apotek = MasterApotek::find($penjualan->id_apotek_nota);

        $debet = 0;
        if(!empty($penjualan->id_kartu_debet_credit)) {
            $debet = $penjualan->debet;
        } 
        $total_bayar = $debet+$penjualan->cash;

        $html = '';
        $html .= 'Sekian dan terimakah';

        $text = 'Contoh Cetak Data';     
/* tulis dan buka koneksi ke printer, sesuaikan dengan printer di komputer anda */    
$printer = printer_open("doPDF v7");  
/* write the text to the print job */  
printer_write($printer, $text);   
/* close the connection */ 
printer_close($printer);

        //return json_encode($html);


    }

    public function load_page_print_closing_kasir($id) {
        $no = 0;

        if(empty($request->tanggal)) {
            $tanggal = date('Y-m-d');
            $id_user = Auth::user()->id;
        } else {
            $tanggal = $request->tanggal;
            $id_user = $request->id_user;
        }

        $data = TransaksiPenjualanClosing::find($id);
        $apotek = MasterApotek::find($data->id_apotek_nota);
        $nama_apotek = strtoupper($apotek->nama_panjang);
        $nama_apotek_singkat = strtoupper($apotek->nama_singkat);

        $id_user = $data->id_user;
        $id_apotek = $data->id_apotek_nota;
        $tgl_awal_baru = $data->tanggal.' 00:00:00';
        $tgl_akhir_baru = $data->tanggal.' 23:59:59';

        $jasa_resep = TransaksiPenjualan::select([
                                DB::raw('COUNT(tb_nota_penjualan.id) AS jumlah_resep'),
                                DB::raw('SUM(biaya_resep) AS total_biaya_resep'), 
                                'a.id as id_jasa_resep',
                                'a.nama as nama_jasa_resep',
                                'a.biaya'
                        ])
                        ->join('tb_m_jasa_resep as a', 'a.id', 'tb_nota_penjualan.id_jasa_resep')
                        ->where('tb_nota_penjualan.is_deleted', 0)
                        ->whereNotNull('tb_nota_penjualan.id_jasa_resep')
                        ->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.created_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->orWhere('tb_nota_penjualan.is_deleted', 0)
                        ->where('tb_nota_penjualan.id_jasa_resep', '!=', '0')
                        ->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.created_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->groupBy('tb_nota_penjualan.id_jasa_resep')
                        ->get();

        $jasa_dokter = TransaksiPenjualan::select([
                            DB::raw('COUNT(tb_nota_penjualan.id) AS jumlah_transaksi'),
                            DB::raw('SUM(biaya_jasa_dokter) AS total_biaya_jasa_dokter'), 
                                'a.id as id_dokter',
                                'a.nama as nama_dokter', 
                                'a.fee'
                        ])
                        ->join('tb_m_dokter as a', 'a.id', 'tb_nota_penjualan.id_dokter')
                        ->where('tb_nota_penjualan.is_deleted', 0)
                        ->whereNotNull('tb_nota_penjualan.id_dokter')
                        ->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.created_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->orWhere('tb_nota_penjualan.is_deleted', 0)
                        ->where('tb_nota_penjualan.id_dokter', '!=', '0')
                        ->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.created_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->groupBy('tb_nota_penjualan.id_dokter')
                        ->get();

        /*$penjualan_kredits = TransaksiPenjualan::select([
                                DB::raw('COUNT(tb_nota_penjualan.id) AS jumlah_transaksi'),
                                DB::raw('SUM(total_belanja) AS total'),
                                'a.nama as nama_vendor'
                        ])
                        ->leftjoin('tb_vendor_kerjasama as a', 'a.id', 'tb_nota_penjualan.id_vendor')
                        ->where('tb_nota_penjualan.is_deleted', 0)
                        ->whereNotNull('tb_nota_penjualan.id_vendor')
                        ->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.created_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->orWhere('tb_nota_penjualan.is_deleted', 0)
                        ->where('tb_nota_penjualan.id_vendor', '!=', '0')
                        ->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.created_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->groupBy('tb_nota_penjualan.id_vendor')
                        ->get();*/

        $penjualan_kredits = DB::table('tb_detail_nota_penjualan')
                        ->select(
                                DB::raw('COUNT(b.id) AS jumlah_transaksi'),
                                DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) AS total_penjualan'),
                                DB::raw('SUM(tb_detail_nota_penjualan.diskon) AS total_diskon'),
                                DB::raw('SUM((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) - tb_detail_nota_penjualan.diskon) AS total'),
                                DB::raw('SUM(b.diskon_persen/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen'),
                                DB::raw('SUM(a.diskon/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen_vendor'),
                                'a.nama as nama_vendor'
                            )

                        ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')
                        ->leftjoin('tb_vendor_kerjasama as a','a.id','=','b.id_vendor')
                        ->where('b.is_deleted', 0)
                        ->where('tb_detail_nota_penjualan.is_deleted', 0)
                        ->whereNotNull('b.id_vendor')
                        ->whereDate('b.created_at','>=', $tgl_awal_baru)
                        ->whereDate('b.created_at','<=', $tgl_akhir_baru)
                        ->where('b.created_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('b.id_apotek_nota','=',$id_apotek)
                        ->orWhere('b.is_deleted', 0)
                        ->where('tb_detail_nota_penjualan.is_deleted', 0)
                        ->where('b.id_vendor', '!=', '0')
                        ->whereDate('b.created_at','>=', $tgl_awal_baru)
                        ->whereDate('b.created_at','<=', $tgl_akhir_baru)
                        ->where('b.created_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('b.id_apotek_nota','=',$id_apotek)
                        ->groupBy('b.id_vendor')
                        ->get();

        $paket_wd = TransaksiPenjualan::select([
                            DB::raw('COUNT(tb_nota_penjualan.id) AS jumlah_paket'),
                            DB::raw('SUM(harga_wd) AS total_harga_wd'), 
                            'a.id as id_paket_wd',
                            'a.nama as nama_paket',
                            'a.harga'
                        ])
                        ->join('tb_m_paket_wd as a', 'a.id', 'tb_nota_penjualan.id_paket_wd')
                        ->where('tb_nota_penjualan.is_deleted', 0)
                        ->whereNotNull('tb_nota_penjualan.id_paket_wd')
                        ->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.created_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->orWhere('tb_nota_penjualan.is_deleted', 0)
                        ->where('tb_nota_penjualan.id_paket_wd', '!=', '0')
                        ->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.created_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->groupBy('tb_nota_penjualan.id_paket_wd')
                        ->get();

        $penjualan_debet = TransaksiPenjualan::select([
                            DB::raw('COUNT(tb_nota_penjualan.id) AS jumlah_transaksi'),
                            DB::raw('SUM(debet) AS total_debet'), 
                            'a.id as id_kartu_debet_credit',
                            'a.nama as nama_kartu',
                            'a.charge'
                        ])
                        ->join('tb_m_kartu_debet_credit as a', 'a.id', 'tb_nota_penjualan.id_kartu_debet_credit')
                        ->where('tb_nota_penjualan.is_deleted', 0)
                        ->where('tb_nota_penjualan.id_kartu_debet_credit', '!=', 0)
                        ->whereNotNull('tb_nota_penjualan.id_kartu_debet_credit')
                        ->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.created_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->orWhere('tb_nota_penjualan.is_deleted', 0)
                        ->where('tb_nota_penjualan.id_kartu_debet_credit', '!=', '0')
                        ->whereDate('tb_nota_penjualan.created_at','>=', $tgl_awal_baru)
                        ->whereDate('tb_nota_penjualan.created_at','<=', $tgl_akhir_baru)
                        ->where('tb_nota_penjualan.created_by','LIKE',($id_user > 0 ? $id_user : '%'.$id_user.'%'))
                        ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                        ->groupBy('tb_nota_penjualan.id_kartu_debet_credit')
                        ->get();

        $admin = User::find($id_user);
        $a = '
                <!DOCTYPE html>
                <html lang="en">
                    <style rel="stylesheet">
                       @font-face {
                            font-family: "arial_monospaced_mt";
                            src: url('.url('assets/dist/font/arial_monospaced_mt.ttf').') format("truetype");
                            font-weight: normal;
                            font-style: normal;

                        }

                        * {
                            font-size: 11px;
                            font-family: "arial_monospaced_mt";
                            margin-left:  1px;
                            margin-right: 1px;
                            margin-top: 0px;
                            margin-bottom: 0px;
                        }

                        td,
                        th,
                        tr,
                        table {
                            /*border-top: 1px solid black;*/
                            border-collapse: collapse;
                        }

                        .centered {
                            text-align: center;
                            align-content: center;
                        }

                        .ticket {
                            width: 200px;
                            max-width: 200px;
                            background-color: white !important;
                        }

                        @media print {
                            .hidden-print,
                            .hidden-print * {
                                display: none !important;
                            }
                        }

                        .btn-sm {
                            padding: .25rem .5rem;
                            font-size: .875rem;
                            line-height: 1.5;
                            border-radius: .2rem;
                        }

                        .btn-info {
                            color: #fff;
                            background-color: #17a2b8;
                            border-color: #17a2b8;
                            box-shadow: none;
                        }
                        .btn {
                            display: inline-block;
                            font-weight: 400;
                            color: #212529;
                            text-align: center;
                            vertical-align: middle;
                            cursor: pointer;
                            -webkit-user-select: none;
                            -moz-user-select: none;
                            -ms-user-select: none;
                            user-select: none;
                            background-color: transparent;
                            border: 1px solid transparent;
                            padding: .375rem .75rem;
                            font-size: 1rem;
                            line-height: 1.5;
                            border-radius: .25rem;
                            transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;
                        }

                    </style>
                    <body>
                        <!--  -->
                        <button id="btnPrint" class="hidden-print btn btn-sm btn-info" style="margin:0;color: #fff;background-color: #17a2b8;border-color: #17a2b8;box-shadow: none; font-size:10pt;">Print Nota | Ctrl+P</button>
                        <br>
                        <br>
                        <br>
                        <div class="ticket">
                            <input type="hidden" name="id" id="id" value="'.$data->id.'">
                            <table width="100%">';
        $a .= ' <tr>
                    <td style="text-align: center;" colspan="2">APOTEK BWF-'.$nama_apotek.'</td>
                </tr>
                <tr>
                    <td style="text-align: center;" colspan="2">'.$apotek->alamat.'</td>
                </tr>
                <tr>
                    <td style="text-align: center;" colspan="2">Telp. '.$apotek->telepon.'</td>
                </tr>
                <tr>
                    <td colspan="2">------------------------------</td>
                </tr>';
             
        $tgl_nota = Carbon::parse($data->created_at)->format('d-m-Y H:i:s');

        $a .= ' <tr>
                    <td colspan="2">Kasir &nbsp;&nbsp;: '.$admin->nama.'</td>
                </tr>
                <tr>
                    <td colspan="2">Tanggal : '.$tgl_nota.'</td>
                </tr>
                <tr>
                    <td colspan="2">------------------------------</td>
                </tr>';
        
        $grand_total = $data->total_penjualan+$data->total_jasa_dokter+$data->total_jasa_resep+$data->total_paket_wd+$data->total_lab+$data->total_apd;
        $total_cash = $grand_total-$data->total_debet;

        $total_2 = $grand_total-$data->total_penjualan_cn;
        $total_debet_x = $data->total_debet-$data->total_penjualan_cn_debet;
        $total_cash_x = $total_cash-$data->total_penjualan_cn_cash;
        $a .= '  <tr>
                    <td colspan="2">Penjualan : Rp&nbsp;'.number_format($data->jumlah_penjualan,0,',',',').'</td>
                </tr>
                <tr>
                    <td colspan="2">Diskon Nota : Rp&nbsp;'.number_format($data->total_diskon,0,',',',').'</td>
                </tr>
                <tr>
                    <td colspan="2">---------------------------(-)</td>
                </tr>
                <tr>
                    <td colspan="2">T. Penjualan &nbsp;&nbsp;: Rp&nbsp;'.number_format($data->total_penjualan,0,',',',').'</td>
                </tr>
                <tr>
                    <td colspan="2">T. Jasa Dokter : Rp&nbsp;'.number_format($data->total_jasa_dokter,0,',',',').'</td>
                </tr>
                <tr>
                    <td colspan="2">T. Jasa Resep &nbsp;: Rp&nbsp;'.number_format($data->total_jasa_resep,0,',',',').'</td>
                </tr>
                <tr>
                    <td colspan="2">T. Paket WT &nbsp;&nbsp;&nbsp;: Rp&nbsp;'.number_format($data->total_paket_wd,0,',',',').'</td>
                </tr>
                <tr>
                    <td colspan="2">T. Lab &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: Rp&nbsp;'.number_format($data->total_lab,0,',',',').'</td>
                </tr>
                <tr>
                    <td colspan="2">T. APD &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: Rp&nbsp;'.number_format($data->total_apd,0,',',',').'</td>
                </tr>
                <tr>
                    <td colspan="2">---------------------------(+)</td>
                </tr>
                <tr>
                    <td colspan="2">Total I &nbsp;&nbsp;&nbsp;&nbsp;: Rp&nbsp;'.number_format($grand_total,0,',',',').'</td>
                </tr>
                <tr>
                    <td colspan="2">Total D/C &nbsp;&nbsp;: Rp&nbsp;'.number_format($data->total_debet,0,',',',').'</td>
                </tr>
                <tr>
                    <td colspan="2">Total Cash &nbsp;: Rp&nbsp;'.number_format($total_cash,0,',',',').'</td>
                </tr>
                <tr>
                    <td colspan="2">Total Retur : Rp&nbsp;'.number_format($data->total_penjualan_cn,0,',',',').'</td>
                </tr>
                <tr>
                    <td colspan="2">Total RD &nbsp;&nbsp;&nbsp;: Rp&nbsp;'.number_format($data->total_penjualan_cn_debet,0,',',',').'</td>
                </tr>
                <tr>
                    <td colspan="2">Total RC &nbsp;&nbsp;&nbsp;: Rp&nbsp;'.number_format($data->total_penjualan_cn_cash,0,',',',').'</td>
                </tr>
                <tr>
                    <td colspan="2">---------------------------(-)</td>
                </tr>
                <tr>
                    <td colspan="2">Total II &nbsp;&nbsp;&nbsp;: Rp&nbsp;'.number_format($total_2,0,',',',').'</td>
                </tr>
                <tr>
                    <td colspan="2">Total D/C &nbsp;&nbsp;: Rp&nbsp;'.number_format($total_debet_x,0,',',',').'</td>
                </tr>
                <tr>
                    <td colspan="2">Total Cash &nbsp;: Rp&nbsp;'.number_format($total_cash_x,0,',',',').'</td>
                </tr>
                <tr>
                    <td colspan="2">Switch Cash : Rp&nbsp;'.number_format($data->total_switch_cash,0,',',',').'</td>
                </tr>
                <tr>
                    <td colspan="2">Uang Seharusnya : Rp&nbsp;'.number_format($data->uang_seharusnya,0,',',',').'</td>
                </tr>
                <tr>
                    <td colspan="2">TT &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: Rp&nbsp;'.number_format($data->jumlah_tt,0,',',',').'</td>
                </tr>
                <tr>
                    <td colspan="2">Total Akhir : Rp&nbsp;'.number_format($data->total_akhir,0,',',',').'</td>
                </tr>
                <tr>
                    <td colspan="2">---------------------------(-)</td>
                </tr>
                <tr>
                    <td colspan="2">Total Penjualan K &nbsp;&nbsp;&nbsp;&nbsp;: </td>
                </tr>
                <tr>
                    <td colspan="2">Rp&nbsp;'.number_format($data->total_penjualan_kredit,0,',',',').'</td>
                </tr>
                <tr>
                    <td colspan="2">Penjualan K. Terbayar : </td>
                </tr>
                <tr>
                    <td colspan="2">Rp&nbsp;'.number_format($data->total_penjualan_kredit_terbayar,0,',',',').'</td>
                </tr>
                 <tr>
                    <td colspan="2">------------------------------</td>
                </tr>
                ';

         // "Total Penjualan K    : ".number_format($data->total_penjualan_kredit,0,',',',')."\n".
        //"Penjualan K. Terbayar: ".number_format($data->total_penjualan_kredit_terbayar,0,',',',')."\n".


        $a .= '<tr>
                    <td colspan="2">T. Jasa Dokter : Rp&nbsp;'.number_format($data->total_jasa_dokter,0,',',',').'</td>
                </tr>';

        $total_jasa_dokter = 0;
        $jum_jasa_dokter = count($jasa_dokter);
        if($jum_jasa_dokter > 0) {
            $a .= '<tr>
                    <td colspan="2">Detail : </td>
                </tr> ';
            $i = 0;
            foreach($jasa_dokter as $obj){
                $total_biaya_jasa_dokter = number_format($obj->total_biaya_jasa_dokter,0,',',',');
                $fee = $obj->fee/100*$obj->total_biaya_jasa_dokter;
                $total_jasa_dokter = $total_jasa_dokter+$obj->total_biaya_jasa_dokter;
                $fee = number_format($fee,0,',',',');
                if($obj->id_dokter != 0) {
                    $i++;
                    $a .= ' <tr>
                                <td colspan="2">-&nbsp;'.$obj->nama_dokter.'</td>
                            </tr>
                            <tr>
                                <td colspan="2">('.$obj->jumlah_transaksi.') transaksi = Rp &nbsp; '.$total_biaya_jasa_dokter.'</td>
                            </tr>
                            <tr>
                                <td colspan="2">Fee Dokter &nbsp; = Rp &nbsp; '.$fee.'</td>
                            </tr>
                            ';
                }
            }
        } else {
             $a .= '<tr>
                    <td colspan="2">Detail : -</td>
                </tr> ';
        }

        $a .= '<tr>
                    <td colspan="2">------------------------------</td>
                </tr> ';

        $a .= '<tr>
                    <td colspan="2">T. Jasa Resep : Rp&nbsp;'.number_format($data->total_jasa_resep,0,',',',').'</td>
                </tr>';

        $jum_jasa_resep = count($jasa_resep);
        $total_jasa_resep  = 0;
        if($jum_jasa_resep > 0) {
            $a .= '<tr>
                    <td colspan="2">Detail : </td>
                </tr> ';
            $i = 0;
            foreach($jasa_resep as $obj) {
                $jumlah_resep = $obj->jumlah_resep;
                $biaya = number_format($obj->biaya,0,',',',');
                $total_biaya_resep = number_format($obj->total_biaya_resep,0,',',',');
                $total_jasa_resep = $total_jasa_resep+$obj->total_biaya_resep;
                if($obj->id_jasa_resep != 4) {
                    $i++;
                    $a .= ' <tr>
                                <td colspan="2">-&nbsp;'.$i.'.'.$obj->nama_jasa_resep.'</td>
                            </tr>
                            <tr>
                                <td colspan="2">'.$jumlah_resep.'&nbsp;x&nbsp;'.$biaya.' = Rp &nbsp; '.$total_biaya_resep.'</td>
                            </tr>
                            ';
                }
            }
        } else {
            $a .= '<tr>
                    <td colspan="2">Detail : -</td>
                </tr> ';
        }

        $a .= '<tr>
                    <td colspan="2">------------------------------</td>
                </tr> ';

        $a .= '<tr>
                    <td colspan="2">T. Paket WT : Rp&nbsp;'.number_format($data->total_paket_wd,0,',',',').'</td>
                </tr>';

        $total_paket_wd = 0;
        $jum_paket_wd = count($paket_wd);
        if($jum_paket_wd > 0) {
            $a .= '<tr>
                    <td colspan="2">Detail : </td>
                </tr> ';
            $i = 0;
            foreach($paket_wd as $obj) {
                $jumlah_paket = $obj->jumlah_paket;
                $total_harga_wd = number_format($obj->total_harga_wd,0,',',',');
                $harga = number_format($obj->harga,0,',',',');
                $total_paket_wd = $total_paket_wd+$obj->total_harga_wd;
                if($obj->id_paket_wd != 0) {
                    $i++;
                    $a .= ' <tr>
                                <td colspan="2">-&nbsp;'.$i.'.'.$obj->nama_paket.'</td>
                            </tr>
                            <tr>
                                <td colspan="2">'.$jumlah_paket.'&nbsp;x&nbsp;'.$harga.'= Rp &nbsp; '.$total_harga_wd.'</td>
                            </tr>
                            ';
                }
            }
        } else {
            $a .= '<tr>
                    <td colspan="2">Detail : -</td>
                </tr> ';
        }   

        $a .= '<tr>
                    <td colspan="2">------------------------------</td>
                </tr> ';
        $jum_penjualan_debet = count($penjualan_debet);
        if($jum_penjualan_debet > 0) {
            $a .= '<tr>
                    <td colspan="2">Detail Debet: </td>
                </tr> ';
            $i = 0;
            foreach($penjualan_debet as $obj) {
                $total_debet = number_format($obj->total_debet,0,',',',');
                if($obj->id_kartu_debet_credit != 0) {
                    $i++;
                    $a .= ' <tr>
                                <td colspan="2">-&nbsp;'.$i.'.'.$obj->nama_kartu.'</td>
                            </tr>
                            <tr>
                                <td colspan="2">('.$obj->jumlah_transaksi.') transaksi = Rp &nbsp; '.$total_debet.'</td>
                            </tr>
                            ';
                }
            }
        } else {
            $a .= '<tr>
                    <td colspan="2">Detail Debet: -</td>
                </tr> ';
        }


        $a .= '<tr>
                    <td colspan="2">------------------------------</td>
                </tr> ';
        $jum_penjualan_kredit = count($penjualan_kredits);
        if($jum_penjualan_kredit > 0) {
            $a .= '<tr>
                    <td colspan="2">Detail P. Kredit: </td>
                </tr> ';
            $i = 0;
            foreach($penjualan_kredits as $obj) {
                $total = $obj->total - $obj->total_diskon_persen_vendor - $obj->total_diskon_persen;
                $total_ = number_format($total,0,',',',');
                $i++;
                $a .= ' <tr>
                            <td colspan="2">-&nbsp;'.$i.'.'.$obj->nama_vendor.'</td>
                        </tr>
                        <tr>
                            <td colspan="2">Rp &nbsp; '.$total_.'</td>
                        </tr>
                        '; //('.$obj->jumlah_transaksi.') transaksi = 
            }
        } else {
            $a .= '<tr>
                    <td colspan="2">Detail P. Kredit: -</td>
                </tr> ';
        }

        $a .= '<tr>
                    <td colspan="2">------------------------------</td>
                </tr>
                <tr>
                    <td colspan="2">Catatan : </td>
                </tr>
                <tr>
                    <td colspan="2">K= Kredit</td>
                </tr>
                <tr>
                    <td colspan="2">RC = Retur Cash</td>
                </tr>
                <tr>
                    <td colspan="2">RD=Retur Debet</td>
                </tr>
                <tr>
                    <td colspan="2">D/C = Debet/Credit</td>
                </tr>
                <tr>
                    <td colspan="2">------------------------------</td>
                </tr>
                <tr>
                    <td colspan="2" align="center"> ~ Selamat Bekerja ~</td>
                </tr>
                 <tr>
                    <td colspan="2">------------------------------</td>
                </tr>
                ';
        $a .= '</table>';
        $a .= ' </div>
            </body>
        </html>';
        $html=$a;

        /*print_r($html);
        exit();
*/
        return $html;
    }

    public function load_page_print_nota($id) {
        $no = 0;

        $nota = TransaksiPenjualan::find($id);
        $apotek = MasterApotek::find($nota->id_apotek_nota);
        $detail_penjualans = TransaksiPenjualanDetail::where('id_nota', $nota->id)->where('is_deleted', 0)->get();
        $nama_apotek = strtoupper($apotek->nama_panjang);
        $nama_apotek_singkat = strtoupper($apotek->nama_singkat);


        $a = '
                <!DOCTYPE html>
                <html lang="en">
                    <style rel="stylesheet">
                       @font-face {
                            font-family: "arial_monospaced_mt";
                            src: url('.url('assets/dist/font/arial_monospaced_mt.ttf').') format("truetype");
                            font-weight: normal;
                            font-style: normal;

                        }

                        * {
                            font-size: 11px;
                            font-family: "arial_monospaced_mt";
                            margin-left:  1px;
                            margin-right: 1px;
                            margin-top: 0px;
                            margin-bottom: 0px;
                        }

                        td,
                        th,
                        tr,
                        table {
                            /*border-top: 1px solid black;*/
                            border-collapse: collapse;
                        }

                        .centered {
                            text-align: center;
                            align-content: center;
                        }

                        .ticket {
                            width: 200px;
                            max-width: 200px;
                            background-color: white !important;
                        }

                        @media print {
                            .hidden-print,
                            .hidden-print * {
                                display: none !important;
                            }
                        }

                        .btn-sm {
                            padding: .25rem .5rem;
                            font-size: .875rem;
                            line-height: 1.5;
                            border-radius: .2rem;
                        }

                        .btn-info {
                            color: #fff;
                            background-color: #17a2b8;
                            border-color: #17a2b8;
                            box-shadow: none;
                        }
                        .btn {
                            display: inline-block;
                            font-weight: 400;
                            color: #212529;
                            text-align: center;
                            vertical-align: middle;
                            cursor: pointer;
                            -webkit-user-select: none;
                            -moz-user-select: none;
                            -ms-user-select: none;
                            user-select: none;
                            background-color: transparent;
                            border: 1px solid transparent;
                            padding: .375rem .75rem;
                            font-size: 1rem;
                            line-height: 1.5;
                            border-radius: .25rem;
                            transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;
                        }

                    </style>
                    <body>
                        <!--  -->
                        <button id="btnPrint" class="hidden-print btn btn-sm btn-info" style="margin:0;color: #fff;background-color: #17a2b8;border-color: #17a2b8;box-shadow: none; font-size:10pt;">Print Nota | Ctrl+P</button>
                        <br>
                        <br>
                        <br>
                        <div class="ticket">
                            <input type="hidden" name="id" id="id" value="'.$nota->id.'">
                            <table width="100%">';

        $a .= ' <tr>
                    <td style="text-align: center;" colspan="2">APOTEK BWF-'.$nama_apotek.'</td>
                </tr>
                <tr>
                    <td style="text-align: center;" colspan="2">'.$apotek->alamat.'</td>
                </tr>
                <tr>
                    <td style="text-align: center;" colspan="2">Telp. '.$apotek->telepon.'</td>
                </tr>
                <tr>
                    <td colspan="2">------------------------------</td>
                </tr>';
             
        $tgl_nota = Carbon::parse($nota->created_at)->format('d-m-Y H:i:s');

        $a .= ' <tr>
                    <td colspan="2">No. Nota : '.$nota->id.'</td>
                </tr>
                <tr>
                    <td colspan="2">Tanggal  &nbsp;: '.$tgl_nota.'</td>
                </tr>
                <tr>
                    <td colspan="2">------------------------------</td>
                </tr>';
        
        if($nota->is_kredit == 1) {
            $vendor = MasterVendor::find($nota->id_vendor);
            $a .= ' 
                <tr>
                    <td colspan="2">------------------------------</td>
                </tr>
                <tr>
                    <td colspan="2">Penjualan Melalui &nbsp;: '.$vendor->nama.'</td>
                </tr>
                <tr>
                    <td colspan="2">------------------------------</td>
                </tr>';
        }

        $total_belanja = 0;
        if($nota->is_penjualan_tanpa_item != 1) {
            foreach ($detail_penjualans as $key => $val) {
                $no++;

                $total_1 = ($val->jumlah-$val->jumlah_cn) * $val->harga_jual;
                $total_2 = $total_1 - $val->diskon;
                $total_belanja = $total_belanja + $total_2;
                $harga_jual = number_format($val->harga_jual,0,',',',');
                $diskon = number_format($val->diskon,0,',',',');
                $total_2 = number_format($total_2,0,',',',');

                $a .= ' 
                <tr>
                    <td colspan="2">'.$no.'.'.$val->obat->nama.'</td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;&nbsp;'.$harga_jual.'x'.number_format($val->jumlah, 0, '.', ',')."(-".number_format($val->diskon).') = '.'Rp'. $total_2.'</td>
                </tr>';
            }
        } 
        $a .= ' <tr>
                    <td colspan="2">------------------------------</td>
                </tr>';

        $total_diskon_persen = $nota->diskon_persen/100 * $total_belanja;
        $total_diskon_persen_vendor = $nota->diskon_vendor/100 * $total_belanja;
        $total_belanja_bayar = $total_belanja - ($total_diskon_persen + $nota->diskon_rp + $total_diskon_persen_vendor);
        $total_diskon = $total_diskon_persen+$nota->diskon_rp+$total_diskon_persen_vendor;
        $total_belanja = $total_belanja+$nota->biaya_jasa_dokter+$nota->biaya_lab+$nota->biaya_apd;
        $biaya_jasa_dokter = number_format($nota->biaya_jasa_dokter,0,',',',');
        $biaya_apd = number_format($nota->biaya_apd,0,',',',');
        $biaya_lab = number_format($nota->biaya_lab,0,',',',');

        if(!empty($nota->id_dokter)) {
            $a .= ' <tr>
                    <td colspan="2">Jasa Dokter : Rp '.$biaya_jasa_dokter.'</td>
                </tr>';
        } else{
            $a .= ' <tr>
                    <td colspan="2">Jasa Dokter : Rp 0</td>
                </tr>';
        }

        if(!empty($nota->id_jasa_resep)) {
            $x = MasterJasaResep::find($nota->id_jasa_resep);
            $jasa_resep_biaya = number_format($x->biaya,0,',',',');
            $total_belanja = $total_belanja+$x->biaya;
            $a .= ' <tr>
                    <td colspan="2">Jasa Resep &nbsp;: Rp '.$jasa_resep_biaya.'</td>
                </tr>';
        } else {
            $a .= ' <tr>
                    <td colspan="2">Jasa Resep &nbsp;: Rp 0</td>
                </tr>';
        }
        
        if(!empty($nota->id_paket_wd)) {
            $harga_wd = number_format($nota->harga_wd,0,',',',');
            $total_belanja = $total_belanja+$nota->harga_wd;
            $a .= ' <tr>
                    <td colspan="2">Paket WT &nbsp;&nbsp;&nbsp;: Rp '.$harga_wd.'</td>
                </tr>';
        } else {
            $a .= ' <tr>
                    <td colspan="2">Paket WT &nbsp;&nbsp;&nbsp;: Rp 0</td>
                </tr>';
        }

        if(!empty($nota->biaya_lab)) {
            $a .= ' <tr>
                    <td colspan="2">Biaya LAB &nbsp;&nbsp;: Rp '.$biaya_lab.'</td>
                </tr>';
        } else{
            $a .= ' <tr>
                    <td colspan="2">Biaya LAB &nbsp;&nbsp;: Rp 0</td>
                </tr>';
        }

        if(!empty($nota->biaya_apd)) {
            $a .= ' <tr>
                    <td colspan="2">Biaya APD &nbsp;&nbsp;: Rp '.$biaya_apd.'</td>
                </tr>';
        } else{
            $a .= ' <tr>
                    <td colspan="2">Biaya APD &nbsp;&nbsp;: Rp 0</td>
                </tr>';
        }
        $a .= ' <tr>
                    <td colspan="2">------------------------------</td>
                </tr>';


        $debet = 0;
        if(!empty($nota->id_kartu_debet_credit)) {
            $debet = $nota->debet;
        } 
        $total_bayar = $debet+$nota->cash;

        if($total_bayar == 0) {
            $total_bayar = $total_belanja+$nota->kembalian;
        }
        $total_belanja_format = number_format($total_belanja,0,',',',');
        $total_diskon_format = number_format($total_diskon,0,',',',');
        $total_bayar_format = number_format($total_bayar,0,',',',');
        $kembalian_format = number_format($nota->kembalian,0,',',',');
        $grand_total = $total_belanja-$total_diskon;
        $grand_total_format = number_format($grand_total,0,',',',');

        $a .= ' 
                <tr>
                    <td colspan="2">Total &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: Rp '.$total_belanja_format.'</td>
                </tr>
                <tr>
                    <td colspan="2">Diskon &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: Rp '.$total_diskon_format.'</td>
                </tr>
                 <tr>
                    <td colspan="2">Grand Total : Rp '.$grand_total_format.'</td>
                </tr>';
        

       
        if($nota->is_kredit != 1) {
            $a .= ' 
                <tr>
                    <td colspan="2">Bayar &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: Rp '.$total_bayar_format.'</td>
                </tr>
                <tr>
                    <td colspan="2">Kembalian &nbsp;&nbsp;: Rp '.$kembalian_format.'</td>
                </tr>
                <tr>
                    <td colspan="2">------------------------------</td>
                </tr>';
        } else {
            $a .= ' <tr>
                    <td colspan="2">------------------------------</td>
                </tr>';

        }

        $a .= '
                <tr>
                    <td colspan="2" align="center">Terimakasih Atas Kunjungan Anda</td>
                </tr>
                <tr>
                    <td colspan="2" align="center"> ~ Semoga Lekas Sembuh ~</td>
                </tr>
                 <tr>
                    <td colspan="2">------------------------------</td>
                </tr>
                ';

        $a .= '</table>';
        $a .= ' </div>
            </body>
        </html>';
        $html=$a;

        print_r($html);
        exit();

        return $html;
    }

    public function penjualan_margin() {
        $vendor_kerjamas      = MasterVendor::where('is_deleted', 0)->pluck('nama', 'id');
        $vendor_kerjamas->prepend('-- Pilih Vendor --','');

        return view('histori.penjualan_kredit')->with(compact('vendor_kerjamas'));
    }

    public function list_kredit_xx(Request $request)
    {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];
        $tgl_penjualan = $request->tgl_penjualan;
        $split           = explode(" - ", $tgl_penjualan);
        $date1 = strtr($split[0], '/', '-');
        $date2 = strtr($split[1], '/', '-');
        $date1 = date('Y-m-d', strtotime($date1));
        $date2 = date('Y-m-d', strtotime($date2));

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
        $data = TransaksiPenjualan::select([
                DB::raw('@rownum  := @rownum  + 1 AS no'),
                'tb_nota_penjualan.*', 
        ])
        ->where(function($query) use($request, $date1, $date2){
            $query->where('tb_nota_penjualan.is_deleted','=','0');
            $query->where('tb_nota_penjualan.is_kredit','=','1');
            $query->where('tb_nota_penjualan.id_apotek_nota','=',session('id_apotek_active'));
            if($request->id_vendor != '') {
                $query->where('tb_nota_penjualan.id_vendor', $request->id_vendor);
            }
            $query->where('tb_nota_penjualan.tgl_nota', '>=', $date1);
            $query->where('tb_nota_penjualan.tgl_nota', '<=', $date2);
            if($request->keterangan != '') {
                $query->where('tb_nota_penjualan.keterangan','LIKE','%'.$request->keterangan.'%');
            }
            if($request->is_lunas_pembayaran_kredit != '') {
                $query->where('tb_nota_penjualan.is_lunas_pembayaran_kredit','=',$request->is_lunas_pembayaran_kredit);
            }
        })
        ->groupBy('tb_nota_penjualan.id');
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->orwhere('tb_nota_penjualan.id','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->editcolumn('created_at', function($data) use($request){
            return Carbon::parse($data->created_at)->format('d/m/Y H:i:s').'<br><b>'.$data->vendor->nama.'</b>';
        })
        ->editcolumn('total', function($data) use($request){
            $total = $data->detail_penjualan_total[0]->total;
            if($total == "" || $total == null) {
                $total = 0;
            }
            $string = '';
            if($data->cek_retur[0]->total_cn != 0) {
                $string = '<small><b style="color:red;">Ada retur</b></small>';
            }
            return "Rp ".number_format($total,2)."<br>".$string;
        })   
        ->editcolumn('debet', function($data) use($request){
            return "Rp ".number_format($data->debet,2);
        })   
        ->editcolumn('biaya_jasa_dokter', function($data) use($request){
            return "Rp ".number_format($data->biaya_resep,2).'/'."Rp ".number_format($data->biaya_jasa_dokter,2);
        })   
        ->editcolumn('total_fix', function($data) use($request){
            $total = $data->detail_penjualan_total[0]->total;
            if($total == "" || $total == null) {
                $total = 0;
            }
            $total_fix = $total+$data->biaya+$data->biaya_jasa_dokter;
            return "Rp ".number_format($total_fix,2);
        })   
        ->editcolumn('is_lunas_pembayaran_kredit', function($data) use($request){
            if($data->is_lunas_pembayaran_kredit == 1) {
                $oleh = $data->lunas_oleh->nama;
                $tgl = Carbon::parse($data->is_lunas_pembayaran_kredit_at)->format('d/m/Y H:i:s');
                $string = '<span class="right badge badge-info">Lunas</span><br><small>'.$oleh.'|'.$tgl.'</small>';
            } else {
                $string = '<span class="right badge badge-danger">Belum Lunas</span>';
            }
           
            return $string;
        })   
        ->editcolumn('created_by', function($data) {
            return '<small>'.$data->created_oleh->nama.'</small>';
        })
        ->addcolumn('action', function($data) use($hak_akses){
            $btn = '<div class="btn-group">';
            if($data->cek_retur[0]->total_cn != 0) {
                $btn .= '<span class="btn btn-primary btn-sm" onClick="pembayaran_kredit('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Cetak Nota"><i class="fa fa-clipboard-check"></i> Pelunasan</span>';
                $btn .= '<a href="'.url('/penjualan/cetak_nota/'.$data->id).'" title="Cetak Nota" target="_blank"  class="btn btn-secondary btn-sm"><span data-toggle="tooltip" data-placement="top" title="Cetak Nota"><i class="fa fa-print"></i> Cetak</span></a>';
            }
            $btn .= '<a href="'.url('/penjualan/detail/'.$data->id).'" title="Lihat detail penjualan" class="btn btn-info btn-sm"><span data-toggle="tooltip" data-placement="top" title="Lihat detail penjualan"><i class="fa fa-eye"></i> Detail</span></a>';
            if($hak_akses == 1) {
                if($data->cek_retur[0]->total_cn == 0) {
                    $btn .= '<span class="btn btn-danger btn-sm" onClick="delete_penjualan('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i> Hapus</span>';
                }
            }
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['created_by', 'action', 'created_at', 'is_lunas_pembayaran_kredit', 'total'])
        ->addIndexColumn()
        ->make(true);
    }

    public function cari_info(Request $request) {
        $tgl_awal = $request->tgl_awal;
        $tgl_akhir = $request->tgl_akhir;
        $id_apotek = session('id_apotek_active');

        $detail_penjualan = DB::table('tb_detail_nota_penjualan')
                    ->select(
                            DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) AS total_penjualan'),
                            DB::raw('SUM(tb_detail_nota_penjualan.diskon) AS total_diskon'),
                            DB::raw('SUM((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) - tb_detail_nota_penjualan.diskon) AS total'),
                            DB::raw('SUM(b.diskon_persen/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen'))
                    ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')
                    ->whereDate('b.tgl_nota','>=', $tgl_awal)
                    ->whereDate('b.tgl_nota','<=', $tgl_akhir)
                    ->where('b.id_apotek_nota','=',$id_apotek)
                    ->where('b.is_deleted', 0)
                    ->where('b.is_kredit', 0)
                    ->first();

        $penjualan2 =  DB::table('tb_nota_penjualan')
                    ->select(
                            DB::raw('SUM(tb_nota_penjualan.biaya_jasa_dokter) AS total_jasa_dokter'),
                            DB::raw('SUM(a.biaya) AS total_jasa_resep'),
                            DB::raw('SUM(tb_nota_penjualan.debet) AS total_debet'),
                            DB::raw('SUM(tb_nota_penjualan.harga_wd) AS total_paket_wd'),
                            DB::raw('SUM(tb_nota_penjualan.biaya_lab) AS total_lab'),
                            DB::raw('SUM(tb_nota_penjualan.biaya_apd) AS total_apd'),
                            DB::raw('SUM(tb_nota_penjualan.diskon_rp) AS total_diskon_rp'))
                    ->join('tb_m_jasa_resep as a','a.id','=','tb_nota_penjualan.id_jasa_resep')
                    ->whereDate('tgl_nota','>=', $tgl_awal)
                    ->whereDate('tgl_nota','<=', $tgl_akhir)
                    ->where('id_apotek_nota','=',$id_apotek)
                    ->where('tb_nota_penjualan.is_deleted', 0)
                    ->where('tb_nota_penjualan.is_kredit', 0)
                    ->first();

        $penjualan_closing = TransaksiPenjualanClosing::select([

                                    DB::raw('SUM(total_jasa_dokter) as total_jasa_dokter_a'),
                                    DB::raw('SUM(total_jasa_resep) as total_jasa_resep_a'),
                                    DB::raw('SUM(total_paket_wd) as total_paket_wd_a'),
                                    DB::raw('SUM(total_penjualan) as total_penjualan_a'),
                                    DB::raw('SUM(total_debet) as total_debet_a'),
                                    DB::raw('SUM(total_penjualan_cash) as total_penjualan_cash_a'),
                                    DB::raw('SUM(total_penjualan_cn) as total_penjualan_cn_a'),
                                    DB::raw('SUM(total_penjualan_kredit) as total_penjualan_kredit_a'),
                                    DB::raw('SUM(total_penjualan_kredit_terbayar) as total_penjualan_kredit_terbayar_a'),
                                    DB::raw('SUM(total_diskon) as total_diskon_a'),
                                    DB::raw('SUM(uang_seharusnya) as uang_seharusnya_a'),
                                    DB::raw('SUM(total_akhir) as total_akhir_a'),
                                    DB::raw('SUM(jumlah_tt) as jumlah_tt_a')
                                ])
                                ->whereDate('tanggal','>=', $tgl_awal)
                                ->whereDate('tanggal','<=', $tgl_akhir)
                                ->where('id_apotek_nota','=',$id_apotek)
                                ->first();

        $detail_penjualan_cn = DB::table('tb_detail_nota_penjualan')
                        ->select(
                                DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah_cn) AS total_penjualan'),
                                DB::raw('SUM(tb_detail_nota_penjualan.diskon) AS total_diskon'),
                                DB::raw('SUM((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah_cn) - tb_detail_nota_penjualan.diskon) AS total'),
                                DB::raw('SUM(b.diskon_persen/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah_cn)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen'))
                        ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')
                        ->whereDate('tb_detail_nota_penjualan.cn_at','>=', $tgl_awal)
                        ->whereDate('tb_detail_nota_penjualan.cn_at','<=', $tgl_akhir)
                        ->where('b.id_apotek_nota','=',$id_apotek)
                        ->where('b.is_deleted', 0)
                        ->where('tb_detail_nota_penjualan.is_cn', 1)
                        ->where('tb_detail_nota_penjualan.is_approved', 1)
                        ->where('b.is_kredit', 0)
                        ->first();

        $penjualan_cn_cash = DB::table('tb_detail_nota_penjualan')
                        ->select(
                                DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah_cn) AS total_penjualan')
                            )
                        ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')
                        ->whereDate('tb_detail_nota_penjualan.cn_at','>=', $tgl_awal)
                        ->whereDate('tb_detail_nota_penjualan.cn_at','<=', $tgl_akhir)
                        ->where('b.id_apotek_nota','=',$id_apotek)
                        ->where('b.is_deleted', 0)
                        ->where('b.debet', 0)
                        ->where('tb_detail_nota_penjualan.is_cn', 1)
                        ->where('tb_detail_nota_penjualan.is_approved', 1)
                        ->where('b.is_kredit', 0)
                        ->first();

        $new_total_total_kredit = 0;
        $new_total_total_kredit_terbayar = 0;
        $new_total_total_kredit_blm_terbayar = 0;
        $new_total_total_non_kredit = 0;
        $new_total_total_non_kredit_cash = 0;
        $new_total_total_non_kredit_non_cash = 0;
        $new_total_total_non_kredit_tt = 0;
        $new_total_total_penjualan = 0;
        $new_total_total_jasa_dokter = 0;
        $new_total_total_jasa_resep = 0;
        $new_total_total_paket_wd = 0;
        $new_total_total_lab = 0;
        $new_total_total_apd = 0;

        $total_diskon = $detail_penjualan->total_diskon_persen + $penjualan2->total_diskon_rp;
        $total_3 = $detail_penjualan->total-$total_diskon;
        $grand_total = $total_3+$penjualan2->total_jasa_dokter+$penjualan2->total_jasa_resep+$penjualan2->total_paket_wd+$penjualan2->total_lab+$penjualan2->total_apd;
        $total_cash = $grand_total - $penjualan2->total_debet;
        $total_penjualan_cn_cash = 0;
        if(!empty($penjualan_cn_cash->total_penjualan)) {
            $total_penjualan_cn_cash = $penjualan_cn_cash->total_penjualan - $detail_penjualan_cn->total_diskon_persen;
        }
        $total_penjualan_cn_debet = 0;
        if(!empty($penjualan_cn_debet->total_debet)) {
            $total_penjualan_cn_debet = $detail_penjualan_cn->total-$total_penjualan_cn_cash;
        }
        $total_cn = 0 + $detail_penjualan_cn->total - $detail_penjualan_cn->total_diskon_persen;
        $total_2 = $grand_total-$total_cn;
        $total_cash_x = $total_cash-$total_penjualan_cn_cash;
        $total_debet_x = $penjualan2->total_debet-$total_penjualan_cn_debet;
        $total_penjualan = $total_2-($penjualan2->total_jasa_dokter+$penjualan2->total_jasa_resep+$penjualan2->total_paket_wd+$penjualan2->total_lab+$penjualan2->total_apd);
        $total_3_format = number_format($total_2,0,',',',');
        $g_format = number_format($total_debet_x,0,',',',');
        $h_format = number_format($total_cash_x,0,',',',');
        $a_format = number_format($penjualan2->total_jasa_dokter,0,',',',');
        $b_format = number_format($penjualan2->total_jasa_resep,0,',',',');
        $c_format = number_format($penjualan2->total_paket_wd,0,',',',');
        $d_format = number_format($penjualan2->total_lab,0,',',',');
        $e_format = number_format($penjualan2->total_apd,0,',',',');
        $f_format = number_format($penjualan_closing->jumlah_tt_a,0,',',',');
        $total_penjualan_format = number_format($total_penjualan,0,',',',');
     
        # update 
        $new_total_total_non_kredit = $new_total_total_non_kredit + $total_2;
        $new_total_total_non_kredit_cash = $new_total_total_non_kredit_cash + $total_cash_x;
        $new_total_total_non_kredit_non_cash = $new_total_total_non_kredit_non_cash + $total_debet_x;
        $new_total_total_non_kredit_tt = $new_total_total_non_kredit_tt + $penjualan_closing->jumlah_tt_a;
        $new_total_total_penjualan = $new_total_total_penjualan + $total_penjualan;
        $new_total_total_jasa_dokter = $new_total_total_jasa_dokter + $penjualan2->total_jasa_dokter;
        $new_total_total_jasa_resep = $new_total_total_jasa_resep + $penjualan2->total_jasa_resep;
        $new_total_total_paket_wd = $new_total_total_paket_wd + $penjualan2->total_paket_wd;
        $new_total_total_lab = $new_total_total_lab + $penjualan2->total_lab;
        $new_total_total_apd = $new_total_total_apd + $penjualan2->total_apd;
        $new_total_total_ongkir = 0;

        ##  PENJUALAN KREDIT ##
        $detail_penjualan_kredit = DB::table('tb_detail_nota_penjualan')
                    ->select(
                            DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) AS total_penjualan'),
                            DB::raw('SUM(tb_detail_nota_penjualan.diskon) AS total_diskon'),
                            DB::raw('SUM((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) - tb_detail_nota_penjualan.diskon) AS total'),
                            DB::raw('SUM(b.diskon_persen/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen'))
                    ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')
                    ->whereDate('b.tgl_nota','>=', $tgl_awal)
                    ->whereDate('b.tgl_nota','<=', $tgl_akhir)
                    ->where('b.id_apotek_nota','=', $id_apotek)
                    ->where('b.is_deleted', 0)
                    ->where('b.is_kredit', 1)
                    ->where('tb_detail_nota_penjualan.is_cn', 0)
                    ->first();

        $penjualan_kredit =  DB::table('tb_nota_penjualan')
                    ->select(
                            DB::raw('SUM(tb_nota_penjualan.biaya_jasa_dokter) AS total_jasa_dokter'),
                            DB::raw('SUM(a.biaya) AS total_jasa_resep'),
                            DB::raw('SUM(tb_nota_penjualan.debet) AS total_debet'),
                            DB::raw('SUM(tb_nota_penjualan.harga_wd) AS total_paket_wd'),
                            DB::raw('SUM(tb_nota_penjualan.biaya_lab) AS total_lab'),
                            DB::raw('SUM(tb_nota_penjualan.biaya_apd) AS total_apd'),
                            DB::raw('SUM(tb_nota_penjualan.diskon_rp) AS total_diskon_rp'))
                    ->join('tb_m_jasa_resep as a','a.id','=','tb_nota_penjualan.id_jasa_resep')
                    ->whereDate('tgl_nota','>=', $tgl_awal)
                    ->whereDate('tgl_nota','<=', $tgl_akhir)
                    ->where('id_apotek_nota','=', $id_apotek)
                    ->where('tb_nota_penjualan.is_deleted', 0)
                    ->where('tb_nota_penjualan.is_kredit', 1)
                    ->first();

        $total_cash_kredit = $detail_penjualan_kredit->total - $penjualan_kredit->total_debet;
        $total_cash_kredit_format = number_format($total_cash_kredit,0,',',',');


        $detail_penjualan_kredit_terbayar = DB::table('tb_detail_nota_penjualan')
                    ->select(
                            DB::raw('SUM(tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) AS total_penjualan'),
                            DB::raw('SUM(tb_detail_nota_penjualan.diskon) AS total_diskon'),
                            DB::raw('SUM((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) - tb_detail_nota_penjualan.diskon) AS total'),
                            DB::raw('SUM(b.diskon_persen/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_persen'),
                            DB::raw('SUM(b.diskon_vendor/100 * ((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah)- tb_detail_nota_penjualan.diskon)) AS total_diskon_vendor')
                        )
                    ->join('tb_nota_penjualan as b','b.id','=','tb_detail_nota_penjualan.id_nota')
                    ->whereDate('b.is_lunas_pembayaran_kredit_at','>=', $tgl_awal)
                    ->whereDate('b.is_lunas_pembayaran_kredit_at','<=', $tgl_akhir)
                    ->where('b.id_apotek_nota','=',$id_apotek)
                    ->where('b.is_deleted', 0)
                    ->where('b.is_kredit', 1)
                    ->where('b.is_lunas_pembayaran_kredit', 1)
                    ->where('tb_detail_nota_penjualan.is_cn', 0)
                    ->first();
    
        $penjualan_kredit_terbayar =  DB::table('tb_nota_penjualan')
                    ->select(
                            DB::raw('SUM(tb_nota_penjualan.biaya_jasa_dokter) AS total_jasa_dokter'),
                            DB::raw('SUM(a.biaya) AS total_jasa_resep'),
                            DB::raw('SUM(tb_nota_penjualan.diskon_rp) AS total_diskon_rp'),
                            DB::raw('SUM(tb_nota_penjualan.debet) AS total_debet'))
                    ->join('tb_m_jasa_resep as a','a.id','=','tb_nota_penjualan.id_jasa_resep')
                    ->whereDate('tb_nota_penjualan.is_lunas_pembayaran_kredit_at','>=', $tgl_awal)
                    ->whereDate('tb_nota_penjualan.is_lunas_pembayaran_kredit_at','<=', $tgl_akhir)
                    ->where('tb_nota_penjualan.id_apotek_nota','=',$id_apotek)
                    ->where('tb_nota_penjualan.is_deleted', 0)
                    ->where('tb_nota_penjualan.is_kredit', 1)
                    ->where('tb_nota_penjualan.is_lunas_pembayaran_kredit', 1)
                    ->first();


        $total_cash_kredit_terbayar = ($detail_penjualan_kredit_terbayar->total + $penjualan_kredit_terbayar->total_jasa_dokter + $penjualan_kredit_terbayar->total_jasa_resep) - $penjualan_kredit_terbayar->total_debet-$detail_penjualan_kredit_terbayar->total_diskon_vendor;
        $total_penjualan_kredit_terbayar = $penjualan_kredit_terbayar->total_debet+$total_cash_kredit_terbayar;
        $total_penjualan_kredit_terbayar_format = number_format($total_penjualan_kredit_terbayar,0,',',',');
        $total_penjualan_kredit_blm_terbayar = $total_cash_kredit - $total_penjualan_kredit_terbayar;
        $total_penjualan_kredit_blm_terbayar_format = number_format($total_penjualan_kredit_blm_terbayar,0,',',',');
        $total_penjualan = $detail_penjualan_kredit->total-($penjualan_kredit->total_jasa_dokter+$penjualan_kredit->total_jasa_resep+$penjualan_kredit->total_paket_wd+$penjualan_kredit->total_lab+$penjualan_kredit->total_apd);
     
        $a_format = number_format($penjualan_kredit->total_jasa_dokter,0,',',',');
        $b_format = number_format($penjualan_kredit->total_jasa_resep,0,',',',');
        $c_format = number_format($penjualan_kredit->total_paket_wd,0,',',',');
        $d_format = number_format($penjualan_kredit->total_lab,0,',',',');
        $e_format = number_format($penjualan_kredit->total_apd,0,',',',');
        $total_penjualan_format = number_format($total_penjualan,0,',',',');

        # update 
        $new_total_total_kredit = $new_total_total_kredit + $total_cash_kredit;
        $new_total_total_kredit_terbayar = $new_total_total_kredit_terbayar + $total_penjualan_kredit_terbayar;
        $new_total_total_kredit_blm_terbayar = $new_total_total_kredit_blm_terbayar + $total_penjualan_kredit_blm_terbayar;
        $new_total_total_penjualan = $new_total_total_penjualan + $total_penjualan;
        $new_total_total_jasa_dokter = $new_total_total_jasa_dokter + $penjualan_kredit->total_jasa_dokter;
        $new_total_total_jasa_resep = $new_total_total_jasa_resep + $penjualan_kredit->total_jasa_resep;
        $new_total_total_paket_wd = $new_total_total_paket_wd + $penjualan_kredit->total_paket_wd;
        $new_total_total_lab = $new_total_total_lab + $penjualan_kredit->total_lab;
        $new_total_total_apd = $new_total_total_apd + $penjualan_kredit->total_apd;
        $new_total_total_ongkir = $new_total_total_ongkir + 0;


        $new_total_total_kredit_format = number_format($new_total_total_kredit,0,',',',');
        $new_total_total_kredit_terbayar_format = number_format($new_total_total_kredit_terbayar,0,',',',');
        $new_total_total_kredit_blm_terbayar_format = number_format($new_total_total_kredit_blm_terbayar,0,',',',');
        $new_total_total_non_kredit_format = number_format($new_total_total_non_kredit,0,',',',');
        $new_total_total_non_kredit_cash_format = number_format($new_total_total_non_kredit_cash,0,',',',');
        $new_total_total_non_kredit_non_cash_format = number_format($new_total_total_non_kredit_non_cash,0,',',',');
        $new_total_total_non_kredit_tt_format = number_format($new_total_total_non_kredit_tt,0,',',',');
        $new_total_total_penjualan_format = number_format($new_total_total_penjualan,0,',',',');
        $new_total_total_jasa_dokter_format = number_format($new_total_total_jasa_dokter,0,',',',');
        $new_total_total_jasa_resep_format = number_format($new_total_total_jasa_resep,0,',',',');
        $new_total_total_paket_wd_format = number_format($new_total_total_paket_wd,0,',',',');
        $new_total_total_lab_format = number_format($new_total_total_lab,0,',',',');
        $new_total_total_apd_format = number_format($new_total_total_apd,0,',',',');
        $new_total_total_ongkir_format = number_format($new_total_total_ongkir,0,',',',');


        $arr_ = array(
                    'penjualan_kredit' => $new_total_total_kredit_format, 
                    'penjualan_kredit_sudah_terbayar' => $new_total_total_kredit_terbayar_format, 
                    'penjualan_kredit_belum_terbayar' => $new_total_total_kredit_blm_terbayar_format,
                    'penjualan_non_kredit' => $new_total_total_non_kredit_format,
                    'penjualan_non_kredit_cash' => $new_total_total_non_kredit_cash_format,
                    'penjualan_non_kredit_non_cash' => $new_total_total_non_kredit_non_cash_format,
                    'penjualan_non_kredit_tt' => $new_total_total_non_kredit_tt_format,
                    'penjualan_dokter' => $new_total_total_jasa_dokter_format,
                    'penjualan_jasa_dokter' => $new_total_total_jasa_resep_format,
                    'penjualan_paket_wd' => $new_total_total_paket_wd_format,
                    'penjualan_lab' => $new_total_total_lab_format,
                    'penjualan_apd' => $new_total_total_apd_format,
                    'penjualan_ongkir' => $new_total_total_ongkir_format
                );
        
        return response()->json($arr_); 
    }

    public function create_margin() {
        $penjualan = new TransaksiPenjualan;
        $detail_penjualans = new TransaksiPenjualanDetail;
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $tanggal = date('Y-m-d');
        $var = 1;

        $members = MasterMember::where('is_deleted', 0)->pluck('nama', 'id');
        $members->prepend('-- Pilih Member --','');

        $is_kredit = 0;
        $is_margin = 1;
        $vendor_kerjama = MasterVendor::where('is_deleted', 0)->get();

        $hak_akses = 1;

        $apoteker = User::find($apotek->id_apoteker);
        $id_user = Auth::user()->id;

        $hak_akses_margin = 0;
        if($apoteker->id == $id_user) {
            $hak_akses_margin = 1;
        }

        if(Auth::user()->is_admin == 1) {
            $hak_akses_margin = 1;
        }

        return view('penjualan.create_margin')->with(compact('penjualan', 'tanggal', 'detail_penjualans', 'var', 'is_kredit', 'inisial', 'apotek', 'vendor_kerjama', 'members', 'hak_akses', 'is_margin', 'hak_akses_margin'));
    }

    public function create_credit_margin() {
        $penjualan = new TransaksiPenjualan;
        $detail_penjualans = new TransaksiPenjualanDetail;
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $tanggal = date('Y-m-d');
        $var = 1;

        $members = MasterMember::where('is_deleted', 0)->pluck('nama', 'id');
        $members->prepend('-- Pilih Member --','');

        $is_kredit = 1;
        $is_margin = 1;
        $vendor_kerjama = MasterVendor::where('is_deleted', 0)->get();

        $hak_akses = 1;

        $apoteker = User::find($apotek->id_apoteker);
        $id_user = Auth::user()->id;

        $hak_akses_margin = 0;
        if($apoteker->id == $id_user) {
            $hak_akses_margin = 1;
        }

        if(Auth::user()->is_admin == 1) {
            $hak_akses_margin = 1;
        }

        return view('penjualan.create_kredit_margin')->with(compact('penjualan', 'tanggal', 'detail_penjualans', 'var', 'is_kredit', 'inisial', 'apotek', 'vendor_kerjama', 'members', 'hak_akses', 'is_margin', 'hak_akses_margin'));
    }

    public function invoice($id) {
        $date_now = date('Y-m-d');
        $id_ = Crypt::decrypt($id);

        $penjualan = TransaksiPenjualan::find($id_);
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $detail_penjualans = $penjualan->detail_penjualan;
        $vendor = MasterVendor::find($penjualan->id_vendor);

        return view('penjualan._invoice')->with(compact('date_now', 'penjualan', 'apotek', 'detail_penjualans', 'id', 'vendor'));
    }

    public function invoiceprint($id) {
        $date_now = date('Y-m-d');
        $id_ = Crypt::decrypt($id);

        $penjualan = TransaksiPenjualan::find($id_);
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $detail_penjualans = $penjualan->detail_penjualan;
        $vendor = MasterVendor::find($penjualan->id_vendor);

        return view('penjualan._invoiceprint')->with(compact('date_now', 'penjualan', 'apotek', 'detail_penjualans', 'id', 'vendor'));
    }

    public function generatepdf($id) {
        $date_now = date('Y-m-d');
        $id_ = Crypt::decrypt($id);

        $penjualan = TransaksiPenjualan::find($id_);
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $detail_penjualans = $penjualan->detail_penjualan;
        $vendor = MasterVendor::find($penjualan->id_vendor);
        //dd($vendor);

        $nama_file_ = 'pdf_penjualan_'.$inisial.'_'.$date_now;
        $pdf = PDF::loadHTML(view('penjualan._generatepdf')->with(compact('date_now', 'penjualan', 'apotek', 'detail_penjualans', 'id', 'vendor')));
        
        $pdf->setOptions(array(
            'dpi' => 300,
            'page-size'=> 'Folio',  
        ));
        return $pdf->stream($nama_file_.'.pdf');
    }

    public function list_detail_penjualan(Request $request) {
        # get total to
        $id = $request->id;
        $is_access = 1;
        $total_penjualan = 0;
        $penjualan = collect();
        $nama_dokter = '-';
        $nama_jasa_resep = '-';
        $nama_paket = '-';
        $nama_karyawan = '-';
        $counter = 0;
        $nama_lab = '-';
        if(is_null($id)) {
            
        } else {
            $penjualan = TransaksiPenjualan::find($id);

            $total_penjualan = $penjualan->detail_penjualan_total[0]->total;
            if($total_penjualan == "" || $total_penjualan == null) {
                $total_penjualan = 0;
            }

            if(!is_null($penjualan->id_dokter) AND $penjualan->id_dokter != 0) {
                $nama_dokter = $penjualan->dokter->nama;
            } 
            if(!is_null($penjualan->id_jasa_resep) AND $penjualan->id_jasa_resep != 0) {
                $nama_jasa_resep = $penjualan->jasa_resep->nama;
            } 
            if(!is_null($penjualan->id_paket_wd) AND $penjualan->id_paket_wd != 0) {
                $nama_paket = $penjualan->paket_wd->nama;
            } 
            if(!is_null($penjualan->id_karyawan) AND $penjualan->id_karyawan != 0) {
                $nama_karyawan = $penjualan->karyawan->nama;
            } 
            if(!is_null($penjualan->nama_lab)) {
                $nama_lab = $penjualan->nama_lab;
            } 

            if($penjualan->total_bayar != 0 AND !is_null($penjualan->total_bayar)) {
                $is_access = 0;
            }
            //$is_access = 1;

            $counter = count($penjualan->detail_penjualan);
        }

        if(Auth::user()->is_admin == 1) {
            $is_access = 1;
        }

        DB::statement(DB::raw('set @rownum = 0'));
        $data = TransaksiPenjualanDetail::select([
                DB::raw('@rownum  := @rownum  + 1 AS no'),
                'tb_detail_nota_penjualan.*', 
        ])
        ->where(function($query) use($request){
            $query->where('tb_detail_nota_penjualan.is_deleted','=','0');
            if(is_null($request->id)) {
                $query->where('tb_detail_nota_penjualan.id_nota','=',0);
            } else {
                $query->where('tb_detail_nota_penjualan.id_nota','=',$request->id);
            }
            
        })
        ->orderBy('tb_detail_nota_penjualan.id', 'ASC');
        
        $datatables = Datatables::of($data);
        return $datatables
        /*->filter(function($query) use($request){
            $query->where(function($query) use($request){
                $query->orwhere('tb_detail_nota_penjualan.id','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  */
        ->editcolumn('action', function($data) use($request, $is_access, $penjualan){
            $btn ='';
            if($penjualan->is_kredit == 1) {
                $btn .= '<span class="btn btn-danger btn-xs btn_hapus" onClick="delete_item('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-trash"></i></span>';
            } else {
                if($is_access == 1) {
                    $btn .= '<span class="btn btn-danger btn-xs btn_hapus" onClick="delete_item('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-trash"></i></span>';
                } else {
                    if($data->is_cn != 1) {
                        if($penjualan->tgl_nota == date('Y-m-d')) {
                            $btn .= '<span class="btn btn-primary btn-xs" onClick="set_jumlah_retur('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Retur Barang"><i class="fa fa-edit"></i></span>';
                        }
                    } 
                }
            }

            return $btn;
        })
        ->editcolumn('nama_barang', function($data) use($request){
            $nama = '';
            $obat = $data->obat;
            $nama = $obat->nama;
            return $nama;
        })
        ->editcolumn('harga_jual', function($data) use($request){
            return $data->harga_jual;
           // return "Rp ".number_format($data->harga_jual,0);
        })   
        ->editcolumn('hb_ppn', function($data) use($request){
            return $data->hb_ppn;
           // return "Rp ".number_format($data->harga_jual,0);
        })   
        ->editcolumn('total', function($data) use($request){
            $total_penjualan = ($data->jumlah * $data->harga_jual)- $data->diskon;

            //return "Rp ".number_format($total_penjualan,0);
            return $total_penjualan;
        })  
        ->addcolumn('action', function($data){
            $btn = '<div class="btn-group">';
            $btn .='</div>';
            return $btn;
        }) 
        ->with([
            "penjualan" => $penjualan,
            "total_penjualan" => $total_penjualan,
            "total_penjualan_format" => "Rp ".number_format($total_penjualan,0),
            "nama_dokter" => $nama_dokter,
            "nama_jasa_resep" => $nama_jasa_resep,
            "nama_paket" => $nama_paket,
            "nama_karyawan" => $nama_karyawan,
            "nama_lab" => $nama_lab,
            "counter" => $counter
        ])  
        //->addIndexColumn() 
        ->rawColumns(['action', 'nama_barang', 'harga_jual', 'total', 'hb_ppn'])
        ->make(true);  
    }

    public function AddItem(Request $request) {
        DB::beginTransaction(); 
        try{
            $penjualan = new TransaksiPenjualan;
            $penjualan->fill($request->except('_token'));

            # set data untuk case khusus
            if($request->is_kredit == 1) {
                $vendor = MasterVendor::find($request->id_vendor);
                $penjualan->id_vendor = $request->id_vendor;
                $penjualan->diskon_vendor = $vendor->diskon;
                $penjualan->tgl_jatuh_tempo = $request->tgl_jatuh_tempo;
                $penjualan->cash = 0;
                $penjualan->kembalian = 0;
                $penjualan->total_bayar = 0;
            } else {
                $penjualan->tgl_jatuh_tempo = $request->tgl_nota;
            }

            if($request->id_pasien != '') {
                $penjualan->id_pasien = $request->id_pasien;
            } 

            $penjualan->is_penjualan_tanpa_item = 0;
          
            $detail_penjualans = array();
            $detail_penjualans[] = array(
                'id' => null,
                'id_obat' => $request->id_obat, 
                'harga_jual' => $request->harga_jual,
                'hb_ppn' => $request->hb_ppn,
                'jumlah' => $request->jumlah,
                'diskon' => 0,
                'margin' => $request->margin,
            );

            //dd($detail_penjualans);exit();

            $tanggal = date('Y-m-d');

            $apotek = MasterApotek::find(session('id_apotek_active'));
            $inisial = strtolower($apotek->nama_singkat);

            $result = $penjualan->save_from_array($detail_penjualans, 1);
            if($result['status']) {
                DB::commit();
                echo json_encode(array('status' => 1, 'id' => $penjualan->id, 'message' => $result['message']));
            } else {
                DB::rollback();
                echo json_encode(array('status' => 0, 'message' => 'Error, silakan cek kembali data yang diinputkan'));
            }
        }catch(\Exception $e){
            DB::rollback();
            echo json_encode(array('status' => 0, 'message' => $e->getMessage()));
        }
    }

    public function UpdateItem(Request $request) {
        DB::beginTransaction(); 
        try{
            $id = $request->id;
            $penjualan = TransaksiPenjualan::find($id);
            if($penjualan->is_deleted != 1) {
                $penjualan->fill($request->except('_token'));

                # set data untuk case khusus
                if($request->is_kredit == 1) {
                    $vendor = MasterVendor::find($request->id_vendor);
                    $penjualan->id_vendor = $request->id_vendor;
                    $penjualan->diskon_vendor = $vendor->diskon;
                    $penjualan->tgl_jatuh_tempo = $request->tgl_jatuh_tempo;
                    $penjualan->cash = 0;
                    $penjualan->kembalian = 0;
                    $penjualan->total_bayar = 0;
                } else {
                    $penjualan->tgl_jatuh_tempo = $request->tgl_nota;
                }

                if($request->id_pasien != '') {
                    $penjualan->id_pasien = $request->id_pasien;
                } 

                $penjualan->is_penjualan_tanpa_item = 0;

                $detail_penjualans = array();
                $detail_penjualans[] = array(
                    'id' => null,
                    'id_obat' => $request->id_obat, 
                    'harga_jual' => $request->harga_jual,
                    'hb_ppn' => $request->hb_ppn,
                    'jumlah' => $request->jumlah,
                    'diskon' => 0,
                    'margin' => $request->margin,
                );

                //dd($detail_penjualans);
                $tanggal = date('Y-m-d');

                $apotek = MasterApotek::find(session('id_apotek_active'));
                $inisial = strtolower($apotek->nama_singkat);
                
                $result = $penjualan->save_from_array($detail_penjualans, 2);

                if($result['status']) {
                    DB::commit();
                    echo json_encode(array('status' => 1, 'id' => $penjualan->id, 'message' => $result['message']));
                } else {
                    DB::rollback();
                    echo json_encode(array('status' => 0, 'message' => 'Error, silakan cek kembali data yang diinputkan'));
                }
            } else {
                DB::rollback();
                echo json_encode(array('status' => 0, 'message' => 'Error, nota ini sudah dihapus, silakan tambah nota baru'));
            }
        }catch(\Exception $e){
            DB::rollback();
            echo json_encode(array('status' => 0));
        }
    }

    public function DeleteItem(Request $request, $id) {
        # yang bisa didelete adalah | yang belum dikonfirm
        DB::beginTransaction(); 
        try{
            $detail_penjualan = TransaksiPenjualanDetail::find($id);
            $detail_penjualan->is_deleted = 1;
            $detail_penjualan->deleted_at = date('Y-m-d H:i:s');
            $detail_penjualan->deleted_by = Auth::user()->id;
           
            # crete histori stok barang
            $apotek = MasterApotek::find(session('id_apotek_active'));
            $inisial = strtolower($apotek->nama_singkat);
            $stok_before = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $detail_penjualan->id_obat)->first(); 
            $stok_now = $stok_before->stok_akhir+$detail_penjualan->jumlah;

            /*$arrayupdate = array(
                'stok_awal'=> $stok_before->stok_akhir, 
                'stok_akhir'=> $stok_now, 
                'updated_at' => date('Y-m-d H:i:s'), 
                'updated_by' => Auth::user()->id
            );*/

            # update ke table stok harga
            $stok_harga = MasterStokHarga::where('id_obat', $detail_penjualan->id_obat)->first();
            $stok_harga->stok_awal = $stok_before->stok_akhir;
            $stok_harga->stok_akhir = $stok_now;
            $stok_harga->updated_at = date('Y-m-d H:i:s'); 
            $stok_harga->updated_by = Auth::user()->id;
            if($stok_harga->save()) {
            } else {
                DB::rollback();
                echo json_encode(array('status' => 0));
            }

            /*$arrayinsert = array(
                'id_obat' => $detail_penjualan->id_obat,
                'jumlah' => $detail_penjualan->jumlah,
                'stok_awal' => $stok_before->stok_akhir,
                'stok_akhir' => $stok_now,
                'id_jenis_transaksi' => 15, //hapus to penjualan
                'id_transaksi' => $detail_penjualan->id,
                'batch' => null,
                'ed' => null,
                'sisa_stok' => null,
                'hb_ppn' => $detail_penjualan->hb_ppn,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => Auth::user()->id
            );*/

            # create histori
            /*$histori_stok = HistoriStok::where('id_obat', $detail_penjualan->id_obat)->where('jumlah', $detail_penjualan->jumlah)->where('id_jenis_transaksi', 15)->where('id_transaksi', $detail_penjualan->id)->first();
            if(empty($histori_stok)) {*/
                $histori_stok = new HistoriStok;
            //}
            $histori_stok->id_obat = $detail_penjualan->id_obat;
            $histori_stok->jumlah = $detail_penjualan->jumlah;
            $histori_stok->stok_awal = $stok_before->stok_akhir;
            $histori_stok->stok_akhir = $stok_now;
            $histori_stok->id_jenis_transaksi = 15; //hapus to penjualan
            $histori_stok->id_transaksi = $detail_penjualan->id;
            $histori_stok->batch = null;
            $histori_stok->ed = null;
            $histori_stok->sisa_stok = null;
            $histori_stok->hb_ppn = $detail_penjualan->hb_ppn;
            $histori_stok->created_at = date('Y-m-d H:i:s');
            $histori_stok->created_by = Auth::user()->id;
            if($histori_stok->save()) {
            } else {
                DB::rollback();
                echo json_encode(array('status' => 0));
            }

            # update stok aktif 
            $histori_stok_details = json_decode($detail_penjualan->id_histori_stok_detail);
            if(count($histori_stok_details) == 0) {
                DB::rollback();
                echo json_encode(array('status' => 0));
            } else {
                foreach ($histori_stok_details as $y => $hist) {
                    $cekHistori = HistoriStok::find($hist->id_histori_stok);
                    $keterangan = $cekHistori->keterangan.', Hapus Penjualan pada IDdet.'.$detail_penjualan->id.' sejumlah '.$hist->jumlah;
                    $cekHistori->sisa_stok = $cekHistori->sisa_stok + $hist->jumlah;
                    $cekHistori->keterangan = $keterangan;
                    if($cekHistori->save()) {
                    } else {
                        DB::rollback();
                        echo json_encode(array('status' => 0));
                    }
                }
            }
            
            if($detail_penjualan->save()) {
                # cek apakah masih ada item pada nota yang sama
                $jum_details = TransaksiPenjualanDetail::where('is_deleted', 0)->where('id_nota', $detail_penjualan->id_nota)->count();
                $is_sisa = 1;
                if($jum_details == 0) {
                    $penjualan = TransaksiPenjualan::find($detail_penjualan->id_nota);
                    $penjualan->is_deleted = 1;
                    $penjualan->deleted_at = date('Y-m-d H:i:s');
                    $penjualan->deleted_by = Auth::user()->id;
                    if($penjualan->save()) {
                    } else {
                        DB::rollback();
                        echo json_encode(array('status' => 0));
                    }

                    $is_sisa = 0;
                }

                DB::commit();
                echo json_encode(array('status' => 1, 'is_sisa' => $is_sisa));
            } else {
                DB::rollback();
                echo json_encode(array('status' => 0));
            }
        }catch(\Exception $e){
            DB::rollback();
            echo json_encode(array('status' => 0));
        }
    }

    public function destroy($id) {
        DB::beginTransaction(); 
        try{
            $apotek = MasterApotek::find(session('id_apotek_active'));
            $inisial = strtolower($apotek->nama_singkat);
            $penjualan = TransaksiPenjualan::find($id);
            $penjualan->is_deleted = 1;
            $penjualan->deleted_at = date('Y-m-d H:i:s');
            $penjualan->deleted_by = Auth::user()->id;

            $detail_penjualans = TransaksiPenjualanDetail::where('id_nota', $penjualan->id)->get();
            foreach ($detail_penjualans as $key => $detail_penjualan) {
                $detail_penjualan->is_deleted = 1;
                $detail_penjualan->deleted_at = date('Y-m-d H:i:s');
                $detail_penjualan->deleted_by = Auth::user()->id;
               
                # crete histori stok barang
                $apotek = MasterApotek::find(session('id_apotek_active'));
                $inisial = strtolower($apotek->nama_singkat);
                $stok_before = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $detail_penjualan->id_obat)->first(); 
                $stok_now = $stok_before->stok_akhir+$detail_penjualan->jumlah;

                /*$arrayupdate = array(
                    'stok_awal'=> $stok_before->stok_akhir, 
                    'stok_akhir'=> $stok_now, 
                    'updated_at' => date('Y-m-d H:i:s'), 
                    'updated_by' => Auth::user()->id
                );*/

                # update ke table stok harga
                $stok_harga = MasterStokHarga::where('id_obat', $detail_penjualan->id_obat)->first();
                $stok_harga->stok_awal = $stok_before->stok_akhir;
                $stok_harga->stok_akhir = $stok_now;
                $stok_harga->updated_at = date('Y-m-d H:i:s'); 
                $stok_harga->updated_by = Auth::user()->id;
                if($stok_harga->save()) {
                } else {
                    DB::rollback();
                    echo json_encode(array('status' => 0));
                }

                /*$arrayinsert = array(
                    'id_obat' => $detail_penjualan->id_obat,
                    'jumlah' => $detail_penjualan->jumlah,
                    'stok_awal' => $stok_before->stok_akhir,
                    'stok_akhir' => $stok_now,
                    'id_jenis_transaksi' => 15, //hapus to penjualan
                    'id_transaksi' => $detail_penjualan->id,
                    'batch' => null,
                    'ed' => null,
                    'sisa_stok' => null,
                    'hb_ppn' => $detail_penjualan->hb_ppn,
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => Auth::user()->id
                );*/

                # create histori
                /*$histori_stok = HistoriStok::where('id_obat', $detail_penjualan->id_obat)->where('jumlah', $detail_penjualan->jumlah)->where('id_jenis_transaksi', 15)->where('id_transaksi', $detail_penjualan->id)->first();
                if(empty($histori_stok)) {*/
                    $histori_stok = new HistoriStok;
                //}
                $histori_stok->id_obat = $detail_penjualan->id_obat;
                $histori_stok->jumlah = $detail_penjualan->jumlah;
                $histori_stok->stok_awal = $stok_before->stok_akhir;
                $histori_stok->stok_akhir = $stok_now;
                $histori_stok->id_jenis_transaksi = 15; //hapus to penjualan
                $histori_stok->id_transaksi = $detail_penjualan->id;
                $histori_stok->batch = null;
                $histori_stok->ed = null;
                $histori_stok->sisa_stok = null;
                $histori_stok->hb_ppn = $detail_penjualan->hb_ppn;
                $histori_stok->created_at = date('Y-m-d H:i:s');
                $histori_stok->created_by = Auth::user()->id;
                if($histori_stok->save()) {
                } else {
                    DB::rollback();
                    echo json_encode(array('status' => 0));
                }

                # update stok aktif 
                $histori_stok_details = json_decode($detail_penjualan->id_histori_stok_detail);
                if(count($histori_stok_details) == 0) {
                    DB::rollback();
                    echo json_encode(array('status' => 0));
                } else {
                    foreach ($histori_stok_details as $y => $hist) {
                        $cekHistori = HistoriStok::find($hist->id_histori_stok);
                        $keterangan = $cekHistori->keterangan.', Hapus Penjualan pada IDdet.'.$detail_penjualan->id.' sejumlah '.$hist->jumlah;
                        $cekHistori->sisa_stok = $cekHistori->sisa_stok + $hist->jumlah;
                        $cekHistori->keterangan = $keterangan;
                        if($cekHistori->save()) {
                        } else {
                            DB::rollback();
                        }
                    }
                }

                if($detail_penjualan->save()) {
                } else {
                    DB::rollback();
                }
            }
            
            if($penjualan->save()){
                echo 1;
                DB::commit();
            }else{
                echo 0;
                DB::rollback();
            }
        }catch(\Exception $e){
            DB::rollback();
            session()->flash('error', 'Error!');
            return redirect('penjualan');
        }
    }

    public function informasi(){
        return view('penjualan.informasi');
    }

    public function UpdateJasaDokter(Request $request) {
        DB::beginTransaction(); 
        try{
            $is_penjualan_tanpa_item = 1;
            if(is_null($request->id)) {
                $penjualan = new TransaksiPenjualan;
            } else {
                $penjualan = TransaksiPenjualan::find($request->id_penjualan);
                $jum = TransaksiPenjualanDetail::where('id_nota', $penjualan->id)->where('is_deleted', 0)->count();
                if($jum > 0) {
                    $is_penjualan_tanpa_item = 0;
                }
            }

            $penjualan->fill($request->except('_token'));
            if($request->is_kredit == 1) {
                $vendor = MasterVendor::find($request->id_vendor);
                $penjualan->id_vendor = $request->id_vendor;
                $penjualan->diskon_vendor = $vendor->diskon;
                $penjualan->tgl_jatuh_tempo = $request->tgl_jatuh_tempo;
                $penjualan->cash = 0;
                $penjualan->kembalian = 0;
                $penjualan->total_bayar = 0;
            } else {
                $penjualan->tgl_jatuh_tempo = $request->tgl_nota;
            }

            if($request->id_pasien != '') {
                $penjualan->id_pasien = $request->id_pasien;
            } 

            if($request->id_jasa_resep == '') {
                $penjualan->id_jasa_resep = 4;
                $penjualan->biaya_resep = 0;
            } 

            if($request->id_dokter == '') {
                $penjualan->id_dokter = 0;
                $penjualan->biaya_jasa_dokter = 0;
            }  

            if($request->id_paket_wd == '') {
                $penjualan->id_paket_wd = 0;
                $penjualan->harga_wd = 0;
            } 

            if($request->nama_lab == '') {
                $penjualan->biaya_lab = 0;
                $penjualan->keterangan_lab = '';
            }

            if($request->biaya_apd == '') {
                $penjualan->biaya_apd = 0;
            }   

            $is_kredit = $request->is_kredit;
            $penjualan->is_penjualan_tanpa_item = $is_penjualan_tanpa_item;

            if(!empty($penjualan->id_jasa_resep)) {
                $biaya_resep = MasterJasaResep::find($penjualan->id_jasa_resep);
                $penjualan->biaya_resep = $biaya_resep->biaya;
            }

            $detail_penjualans = array();
           
            $tanggal = date('Y-m-d');

            $apotek = MasterApotek::find(session('id_apotek_active'));
            $inisial = strtolower($apotek->nama_singkat);

            $members = MasterMember::where('is_deleted', 0)->pluck('nama', 'id');
            $members->prepend('-- Pilih Member --','');

            $validator = $penjualan->validate();
            if($validator->fails()){
                $var = 0;
                DB::rollback();
                echo json_encode(array('status' => 0));
            }else{
                $result = $penjualan->save_from_array($detail_penjualans, 1);
                if($result['status']) {
                    DB::commit();
                    echo json_encode(array('status' => 1, 'id' => $penjualan->id, 'message' => $result['message']));
                } else {
                    DB::rollback();
                    echo json_encode(array('status' => 0, 'message' => 'Error, silakan cek kembali data yang diinputkan'));
                }
            }
        }catch(\Exception $e){
            DB::rollback();
            echo json_encode(array('status' => 0));
        }
    }


    public function CheckDiskon(Request $request) {
        $id = $request->id;
        $diskon_persen = $request->diskon_persen;

        $penjualan = TransaksiPenjualan::find($id);
        $details = TransaksiPenjualanDetail::where('id_nota', $penjualan->id)->where('is_deleted', 0)->get();
        $jum = count($details);

        $is_check = 0;
        if($jum > 0) {
            foreach ($details as $key => $val) {
                $total = ($val->jumlah * $val->harga_jual);
                $diskon = $diskon_persen/100 * $total;
                $hitung = $total - $diskon;
                $hb_ppn = $val->hb_ppn + ((5/100)*$val->hb_ppn);
                $hb_ppn = $val->jumlah * $hb_ppn;
                if($hb_ppn > $hitung) {
                    $is_check = $is_check+1;
                }
            }
        } 

        if($is_check > 0) {
            echo 0;
        } else {
            echo 1;
        }
    }

    public function getHbppn(Request $request) {
        $id = $request->id;
        $id_obat = $request->id_obat;
        $jumlah = $request->jumlah;

        $kurangStok = $this->kurangStok($id_obat, $jumlah);
        if($kurangStok['status'] == 0) {
            $rsp = array('status' => 0, 'message' => 'Stok yang tersedia tidak mencukupi');
            return $rsp;
        } else {
            $rsp = array('status' => 1, 'message' => 'Hbppn berhasil didapatkan', 'hb_ppn' => $kurangStok['hb_ppn']);
            return $rsp;
        }
    }

    public function kurangStok($id_obat, $jumlah) {
        $inisial = strtolower(session('nama_apotek_singkat_active'));
        $cekHistori = DB::table('tb_histori_stok_'.$inisial)
                            ->where('id_obat', $id_obat)
                            ->whereIn('id_jenis_transaksi', [2,3,11,9])
                            ->where('sisa_stok', '>', 0)
                            ->orderBy('id', 'ASC')
                            ->first();

        $array_id_histori_stok = array();
        $array_id_histori_stok_detail = array();
        $hb_ppn = 0;

        if(!is_null($cekHistori)) {
            if($cekHistori->sisa_stok >= $jumlah) {
                # kosongkan sisa stok histori sebelumnya 
                $array_id_histori_stok[] = $cekHistori->id;
                $array_id_histori_stok_detail[] = array('id_histori_stok' => $cekHistori->id, 'jumlah' => $jumlah);
                $hb_ppn = $cekHistori->hb_ppn;
            } else {
                # jika jumlahnya tidak sama maka
                $selisih = $jumlah - $cekHistori->sisa_stok;

                # update jumlah selisih ke histori yang ada stok sebelumnya
                $i = $jumlah;
                $total  = 0;
                $array_id_histori_stok_tota = array();
                while($i >= 1) {
                    # cari histori berikutnya yg bisa dikurangi
                    $cekHistoriLanj = DB::table('tb_histori_stok_'.$inisial)
                            ->where('id_obat', $id_obat)
                            ->whereIn('id_jenis_transaksi', [2,3,11,9])
                            ->where('sisa_stok', '>', 0)
                            ->whereNotIn('id', $array_id_histori_stok)
                            ->orderBy('id', 'ASC')
                            ->first();

                    if($cekHistoriLanj->sisa_stok >= $i) {
                        # update selisih jika stok melebihi jumlah
                        $sisa = $cekHistoriLanj->sisa_stok - $i;
                        $array_id_histori_stok_detail[] = array('id_histori_stok' => $cekHistoriLanj->id, 'jumlah' => $i);
                        $total = $total + ($cekHistoriLanj->hb_ppn * $i);
                        $array_id_histori_stok_tota[] = array('total'=>$total, 'hb_ppn' => $cekHistoriLanj->hb_ppn, 'sisa_stok' => $i);
                        $i = 0;
                    } else {
                        # update selisih jika stok kurang dari jumlah
                        $sisa = $i - $cekHistoriLanj->sisa_stok;
                        $i = $sisa;
                        $array_id_histori_stok_detail[] = array('id_histori_stok' => $cekHistoriLanj->id, 'jumlah' => $cekHistoriLanj->sisa_stok);
                        $total = $total + ($cekHistoriLanj->hb_ppn * $cekHistoriLanj->sisa_stok);
                        $array_id_histori_stok_tota[] = array('total'=>$total, 'hb_ppn' => $cekHistoriLanj->hb_ppn, 'sisa_stok' => $cekHistoriLanj->sisa_stok);
                    }
                    
                    $array_id_histori_stok[] = $cekHistoriLanj->id;
                }

                $hb_ppn = $total/$jumlah;
                $hb_ppn = ceil($hb_ppn);
               // dd($hb_ppn);
            } 

            //exit();
            
            $rsp = array('status' => 1, 'array_id_histori_stok' => json_encode($array_id_histori_stok), 'array_id_histori_stok_detail' => json_encode($array_id_histori_stok_detail), 'hb_ppn' => $hb_ppn);
            return $rsp;
        } else {
            $rsp = array('status' => 0, 'array_id_histori_stok' => null, 'array_id_histori_stok_detail' => null, 'hb_ppn' => null);
            return $rsp;
        }
    }

    public function addMember() {
        $data_ = new MasterMember;

        $jenis_kelamins = MasterJenisKelamin::where('is_deleted', 0)->pluck('jenis_kelamin', 'id');
        $jenis_kelamins->prepend('-- Pilih Jenis Kelamin --','');

        $group_outlets = MasterGroupApotek::where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $group_outlets->prepend('-- Pilih Group Outlet --','');

        $tipe_members = MasterMemberTipe::where('is_deleted', 0)->pluck('nama', 'id');
        $tipe_members->prepend('-- Pilih Tipe Member --','');

        $kabupatens = MasterKabupaten::where('is_deleted', 0)->pluck('nama', 'id');
        $kabupatens->prepend('-- Pilih Kabupaten --','');

        return view('penjualan.member_create')->with(compact('data_', 'jenis_kelamins', 'group_outlets', 'tipe_members', 'kabupatens'));
    }

    public function StoreMember(Request $request) {
        $data_ = new MasterMember;
        $data_->fill($request->except('_token'));

        $jenis_kelamins = MasterJenisKelamin::where('is_deleted', 0)->pluck('jenis_kelamin', 'id');
        $jenis_kelamins->prepend('-- Pilih Jenis Kelamin --','');

        $group_outlets = MasterGroupApotek::where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $group_outlets->prepend('-- Pilih Group Outlet --','');

        $tipe_members = MasterMemberTipe::where('is_deleted', 0)->pluck('nama', 'id');
        $tipe_members->prepend('-- Pilih Tipe Member --','');

        $data_->activated = 1;
        $data_->status = 1;
        $data_->last_year_voucher = date('Y');
        $validator = $data_->validateFP();
        if($validator->fails()){
            dd($data_);
            echo json_encode(array('status' => 0));
        }else{
            $data_->tgl_lahir = date('Y-m-d', strtotime($data_->tgl_lahir));
            $data_->created_by = Auth::user()->id;
            $data_->save();
            echo json_encode(array('status' => 1));
        }
    }


    public function viewClosing(){
        $date_now = date('Y-m-d');
        //$date_now = '2024-05-11';

        $jum_penjualan_count = TransaksiPenjualan::where(function($query) use( $date_now){
            $query->where('tb_nota_penjualan.is_deleted','=','0');
            $query->where('tb_nota_penjualan.is_kredit','=','0');
            $query->where('tb_nota_penjualan.total_bayar','<=','0');
            $query->where('tb_nota_penjualan.id_apotek_nota','=',session('id_apotek_active'));
            if(session('id_role_active') != 1) {
                $query->where('tb_nota_penjualan.created_by', Auth::user()->id);
            }
            //$query->where('tb_nota_penjualan.tgl_nota', 'LIKE', '%'.$date_now.'%');
            $query->where('tb_nota_penjualan.tgl_nota', $date_now);
        })
        ->count();

        $tanggal = Carbon::parse($date_now);
        $tanggal = $tanggal->format('d M Y');
        $jum_penjualan = TransaksiPenjualan::where('is_deleted', 0)->where('tgl_nota', $date_now)->where('created_by', Auth::user()->id)->where('id_apotek_nota','=',session('id_apotek_active'))->count();
        $jum_penjualan_paid = TransaksiPenjualan::where('is_deleted', 0)->where('tgl_nota', $date_now)->where('created_by', Auth::user()->id)->where('total_bayar', '>', 0)->where('id_apotek_nota','=',session('id_apotek_active'))->count();

        $jum_penjualan_notpaid = TransaksiPenjualan::where('is_deleted', 0)->where('tgl_nota', $date_now)->where('created_by', Auth::user()->id)->where('total_bayar', '<=', 0)->where('is_kredit', 0)->where('id_apotek_nota','=',session('id_apotek_active'))->count();

        $jum_penjualan_void = TransaksiPenjualanDetail::join('tb_nota_penjualan as a', 'a.id', '=', 'tb_detail_nota_penjualan.id_nota')
                        ->where('a.tgl_nota', $date_now)
                        ->where('a.created_by', Auth::user()->id)
                        ->where('tb_detail_nota_penjualan.is_cn', 1)
                        ->where('a.is_deleted', 0)
                        ->where('tb_detail_nota_penjualan.is_deleted', 0)
                        ->where('a.id_apotek_nota','=',session('id_apotek_active'))
                        ->count();

        $jum_penjualan_voidaproved = TransaksiPenjualanDetail::join('tb_nota_penjualan as a', 'a.id', '=', 'tb_detail_nota_penjualan.id_nota')
                        ->where('a.tgl_nota', $date_now)
                        ->where('a.created_by', Auth::user()->id)
                        ->where('tb_detail_nota_penjualan.is_cn', 1)
                        ->where('tb_detail_nota_penjualan.is_approved', '!=', 0)
                        ->where('a.is_deleted', 0)
                        ->where('tb_detail_nota_penjualan.is_deleted', 0)
                        ->where('a.id_apotek_nota','=',session('id_apotek_active'))
                        ->count();

        $jum_penjualan_voidnotaproved = TransaksiPenjualanDetail::join('tb_nota_penjualan as a', 'a.id', '=', 'tb_detail_nota_penjualan.id_nota')
                        ->where('a.tgl_nota', $date_now)
                        ->where('a.created_by', Auth::user()->id)
                        ->where('tb_detail_nota_penjualan.is_cn', 1)
                        ->where('tb_detail_nota_penjualan.is_approved', 0)
                        ->where('a.is_deleted', 0)
                        ->where('tb_detail_nota_penjualan.is_deleted', 0)
                        ->where('a.id_apotek_nota','=',session('id_apotek_active'))
                        ->count();

        return view('penjualan.view_closing')->with(compact('jum_penjualan_count', 'jum_penjualan', 'jum_penjualan_paid', 'jum_penjualan_notpaid', 'jum_penjualan_void', 'jum_penjualan_voidaproved', 'jum_penjualan_voidnotaproved'));
    }

}
