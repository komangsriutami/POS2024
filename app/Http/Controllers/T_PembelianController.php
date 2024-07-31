<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\TransaksiPembelian;
use App\TransaksiPembelianDetail;
use App\MasterObat;
use App\MasterApotek;
use App\MasterJenisPembelian;
use App\MasterJasaResep;
use App\DefectaOutlet;
use App\MasterSuplier;
use App\MasterKartu;
use App\MasterMember;
use App\User;
use App\RevisiPembelian;
use App\TransaksiOrder;
use App\TransaksiOrderDetail;
use App\PembayaranKonsinyasi;
use App\ReturPembelian;
use App\MasterAlasanReturPembelian;
use App\KonfirmasiED;
use App\MasterJenisPenanganan;
use App\HistoriStok;
use App\MasterStokHarga;
use App\SettingStokOpnam;
use App\JenisSP;
use App\DefectaOutletHistori;
use App;
use Datatables;
use DB;
use Auth;
use Illuminate\Support\Carbon;
use App\Events\PembelianRetur;
use App\Events\PembelianCreate;
use App\Http\Controllers\Controller;
use Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class T_PembelianController extends Controller
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
        //echo "sementara ditutup, sampai so selesai";exit();
        $date_now = date('Y-m-d');
        $first = date('Y-m-01');
        $last = date("Y-m-t", strtotime($first));
        $supliers =MasterSuplier::where('is_deleted', 0)->get();
        $jenis_pembelians = MasterJenisPembelian::where('is_deleted', 0)->get();
        return view('pembelian.index')->with(compact('supliers', 'jenis_pembelians', 'date_now', 'first', 'last'));
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 07/11/2020
        =======================================================================================
    */
    public function list_pembelian(Request $request)
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

        $last_so = SettingStokOpnam::where('id_apotek', session('id_apotek_active'))->where('step', '>', 1)->orderBy('id', 'DESC')->first();

        $tanggal = date('Y-m-d');
        DB::statement(DB::raw('set @rownum = 0'));
        $data = TransaksiPembelian::select([
                DB::raw('@rownum  := @rownum  + 1 AS no'),
                'tb_nota_pembelian.*', 
        ])
        ->where(function($query) use($request, $tanggal){
            $query->where('tb_nota_pembelian.is_deleted','=','0');
            $query->where('tb_nota_pembelian.id_apotek_nota','=',session('id_apotek_active'));
            $query->where('tb_nota_pembelian.no_faktur','LIKE',($request->no_faktur > 0 ? $request->no_faktur : '%'.$request->no_faktur.'%'));
            
            if($request->id_jenis_pembelian > 0) {
                $query->where('tb_nota_pembelian.id_jenis_pembelian',$request->id_jenis_pembelian);
            }

            if($request->id_suplier > 0) {
                $query->where('tb_nota_pembelian.id_suplier',$request->id_suplier);
            }

            if($request->id_apotek > 0) {
                $query->where('tb_nota_pembelian.id_apotek',$request->id_apotek);
            }
            
           // $query->where('tb_nota_pembelian.id_apotek','LIKE',($request->id_apotek > 0 ? $request->id_apotek : '%'.$request->id_apotek.'%'));
           // $query->where('tb_nota_pembelian.id_supliers','LIKE',($request->id_suplier > 0 ? $request->id_suplier : '%'.$request->id_suplier.'%'));
            if($request->tgl_awal != "") {
                $tgl_awal       = date('Y-m-d H:i:s',strtotime($request->tgl_awal));
                $query->whereDate('tb_nota_pembelian.tgl_jatuh_tempo','>=', $tgl_awal);
            }

            if($request->tgl_akhir != "") {
                $tgl_akhir      = date('Y-m-d H:i:s',strtotime($request->tgl_akhir));
                $query->whereDate('tb_nota_pembelian.tgl_jatuh_tempo','<=', $tgl_akhir);
            }

            if($request->tgl_awal_faktur != "") {
                $tgl_awal_faktur       = date('Y-m-d H:i:s',strtotime($request->tgl_awal_faktur));
                $query->whereDate('tb_nota_pembelian.tgl_faktur','>=', $tgl_awal_faktur);
            }

            if($request->tgl_akhir_faktur != "") {
                $tgl_akhir_faktur      = date('Y-m-d H:i:s',strtotime($request->tgl_akhir_faktur));
                $query->whereDate('tb_nota_pembelian.tgl_faktur','<=', $tgl_akhir_faktur);
            }

            if($request->tgl_awal == "" AND $request->tgl_akhir == "" AND $request->tgl_awal_faktur == "" AND $request->tgl_akhir_faktur == "") {
                $query->whereYear('tb_nota_pembelian.tgl_nota', '>=', 2022); //session('id_tahun_active')
            }
        });
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request){
            $query->where(function($query) use($request){
                $query->orwhere('tb_nota_pembelian.no_faktur','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->editcolumn('apotek', function($data){
            return $data->apotek->nama_singkat;
        })
        ->editcolumn('suplier', function($data){
            return $data->suplier->nama;
        })
        ->editcolumn('jumlah', function($data){
            $new = $data->detail_pembelian_total->first();
            # cek 1 jika jumlah retur sama dengan jumlah detail penjualan - diskon
            $total_ = $new->jumlah - $data->diskon1;
            $diskon1_ = $data->diskon1;
            if($total_ == $new->total_retur) {
                $total1 = ($new->jumlah) - ($data->diskon1 + $new->total_retur);
            } else {
                $total1 = ($new->jumlah) - ($data->diskon1 + $data->diskon2 + $new->total_retur);
            }
            
            //$str_ = 'total_ :'.$total_.'---'.'diskon1 : '.$diskon1_.'----diskon2'.$data->diskon2.'-----retur'.$new->total_retur;
            
            $total2 = $total1 + ($total1 * $data->ppn/100);

            if($total1 < 1) {
                if($data->is_lunas == 1) {
                    $total2 = $new->total_lunas;
                } 
            }

            return "Rp ".number_format($total2,2);
        })
        ->editcolumn('is_lunas', function($data){
            if($data->is_lunas == 0) {
                return '<span class="label label-danger" data-toggle="tooltip" data-placement="top" title="Faktur Belum Dibayar" style="font-size:8pt;color:#e91e63;">Belum Dibayar</span>';
            } else {
                $tgl_lunas_ = $data->lunas_at;
                if($data->id_jenis_pembelian == 1) {
                    $tgl_lunas_ = $data->tgl_faktur;
                } else {
                    if($tgl_lunas_ == '') {
                        $tgl_lunas_ = $data->updated_at;
                    }
                }
                return '<span class="label label-success" data-toggle="tooltip" data-placement="top" title="Faktur Sudah Dibayar" style="font-size:8pt;color:#009688;"></i> Lunas <br>@ : '.$tgl_lunas_.'</span>';
            }
        })      
        ->editcolumn('is_tanda_terima', function($data){
            if($data->is_tanda_terima == 0) {
                return '<span class="label label-danger" data-toggle="tooltip" data-placement="top" title="Faktur Asli Belum Diterima" style="font-size:8pt;color:#e91e63;">Belum Diterima</span>';
            } else {
                return '<span class="label label-success" data-toggle="tooltip" data-placement="top" title="Faktur Asli Sudah Diterima" style="font-size:8pt;color:#009688;"></i> Sudah Diterima</span>';
            }
        })      
        ->editcolumn('id_jenis_pembelian', function($data){
            if ($data->id_jenis_pembelian == 3) {
                $btn = '<a href="'.url('/pembelian/pembayaran_konsinyasi/'.$data->id).'" title="Pembayaran Konsinyasi" class="btn btn-info btn-sm"><span data-toggle="tooltip" data-placement="top" title="Pembayaran Konsinyasi"><i class="fa fa-cogs"></i> Set Pembayaran</span></a>';
                return $data->jenis_pembelian->jenis_pembelian.'<br>'.$btn;
            }else {
                return $data->jenis_pembelian->jenis_pembelian;
            }
        })
        ->editcolumn('is_sign', function($data){
            if($data->is_sign == 0) {
                return '<span class="label label-danger" data-toggle="tooltip" data-placement="top" title="Nota belum dicek atau dittd" style="font-size:8pt;color:#e91e63;">Belum diTTD</span>';
            } else {
                return '<span class="label label-success" data-toggle="tooltip" data-placement="top" title="Nota belum dicek atau dittd" style="font-size:8pt;color:#009688;"></i> TTD by <span class="text-warning">'.$data->sign_by.'</span></span>';
            }
        })  
        ->addcolumn('action', function($data) use($hak_akses, $last_so) {
            $btn = '<div class="btn-group">';
            $btn .= '<span class="btn btn-primary btn-sm" onClick="cek_tanda_terima_faktur('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Sudah menerima faktur asli"><i class="fa fa-check"></i> Faktur Asli</span>';
            $btn .= '<a href="'.url('/pembelian/'.$data->id.'/edit').'" title="Edit Data" class="btn btn-info btn-sm"><span data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i> Edit</span></a>';
            
            if(!empty($last_so)) {
                if($data->tgl_nota >= $last_so->tgl_so) {
                    if($data->is_sign == 1) {
                        if($hak_akses == 1) {
                            if($data->is_lunas != 1) {
                                # jika nota belum lunas
                                $btn .= '<span class="btn btn-primary btn-sm" onClick="batal_sign('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Batalkan sign ini"><i class="fa fa-unlock"></i>Batal Sign</span>';
                                $btn .= '<span class="btn btn-danger btn-sm" onClick="delete_pembelian('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i> Hapus</span>';
                            } else {
                                if($data->id_jenis_pembelian == 1) {
                                    # jika nota pembayaran cash
                                    $btn .= '<span class="btn btn-primary btn-sm" onClick="batal_sign('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Batalkan sign ini"><i class="fa fa-unlock"></i>Batal Sign</span>';
                                    $btn .= '<span class="btn btn-danger btn-sm" onClick="delete_pembelian('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i> Hapus</span>';
                                } else {
                                    $btn .= '<span class="btn btn-warning btn-sm" onClick="sign('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Tandai faktur ini sudah dicek dan ttd"><i class="fa fa-signature"></i> Sign</span>';
                                }
                            }
                        }
                    } else {
                        if($data->is_lunas != 1) {
                            # jika nota belum lunas
                            $btn .= '<span class="btn btn-warning btn-sm" onClick="sign('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Tandai faktur ini sudah dicek dan ttd"><i class="fa fa-signature"></i> Sign</span>';
                            $btn .= '<span class="btn btn-danger btn-sm" onClick="delete_pembelian('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i> Hapus</span>';
                        } else {
                            if($data->id_jenis_pembelian == 1) {
                                # jika nota pembayaran cash
                                $btn .= '<span class="btn btn-warning btn-sm" onClick="sign('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Tandai faktur ini sudah dicek dan ttd"><i class="fa fa-signature"></i> Sign</span>';
                                $btn .= '<span class="btn btn-danger btn-sm" onClick="delete_pembelian('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i> Hapus</span>';
                            } else {
                                $btn .= '<span class="btn btn-warning btn-sm" onClick="sign('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Tandai faktur ini sudah dicek dan ttd"><i class="fa fa-signature"></i> Sign</span>';
                            }
                        }
                    }
                } else {
                    if($data->is_sign == 1) {
                    } else {
                        $btn .= '<span class="btn btn-warning btn-sm" onClick="sign('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Tandai faktur ini sudah dicek dan ttd"><i class="fa fa-signature"></i> Sign</span>';
                    }
                }
            } else {
                if($data->is_sign == 1) {
                    if($hak_akses == 1) {
                        if($data->is_lunas != 1) {
                            # jika nota belum lunas
                            $btn .= '<span class="btn btn-primary btn-sm" onClick="batal_sign('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Batalkan sign ini"><i class="fa fa-unlock"></i>Batal Sign</span>';
                            $btn .= '<span class="btn btn-danger btn-sm" onClick="delete_pembelian('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i> Hapus</span>';
                        } else {
                            if($data->id_jenis_pembelian == 1) {
                                # jika nota pembayaran cash
                                $btn .= '<span class="btn btn-primary btn-sm" onClick="batal_sign('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Batalkan sign ini"><i class="fa fa-unlock"></i>Batal Sign</span>';
                                $btn .= '<span class="btn btn-danger btn-sm" onClick="delete_pembelian('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i> Hapus</span>';
                             } else {
                                $btn .= '<span class="btn btn-warning btn-sm" onClick="sign('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Tandai faktur ini sudah dicek dan ttd"><i class="fa fa-signature"></i> Sign</span>';
                            }
                        }
                    }
                } else {
                    if($data->is_lunas != 1) {
                        # jika nota belum lunas
                        $btn .= '<span class="btn btn-warning btn-sm" onClick="sign('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Tandai faktur ini sudah dicek dan ttd"><i class="fa fa-signature"></i> Sign</span>';
                        $btn .= '<span class="btn btn-danger btn-sm" onClick="delete_pembelian('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i> Hapus</span>';
                    } else {
                        if($data->id_jenis_pembelian == 1) {
                            # jika nota pembayaran cash
                            $btn .= '<span class="btn btn-warning btn-sm" onClick="sign('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Tandai faktur ini sudah dicek dan ttd"><i class="fa fa-signature"></i> Sign</span>';
                            $btn .= '<span class="btn btn-danger btn-sm" onClick="delete_pembelian('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i> Hapus</span>';
                        } else {
                            $btn .= '<span class="btn btn-warning btn-sm" onClick="sign('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Tandai faktur ini sudah dicek dan ttd"><i class="fa fa-signature"></i> Sign</span>';
                        }
                    }
                }
            }

            $btn .='</div>';
            return $btn;
        })    
        ->setRowClass(function ($data) {
            if($data->is_sign == 0) {
                return 'bg-secondary';
            } else {
                return '';
            }
        })  
        ->rawColumns(['action', 'is_tanda_terima', 'is_lunas', 'jumlah', 'suplier', 'apotek', 'id_jenis_pembelian', 'is_sign'])
        ->addIndexColumn()
        ->make(true);  
    }

    public function create() {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $apoteks = MasterApotek::whereIn('id', [$apotek->id])->where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $jenis_pembelians = MasterJenisPembelian::where('is_deleted', 0)->pluck('jenis_pembelian', 'id');
        $jenis_pembelians->prepend('-- Pilih Jenis Pembelian --','');
        $tanggal = date('Y-m-d');
        $pembelian = new TransaksiPembelian;
        $detail_pembelians = new TransaksiPembelianDetail;
        $var = 1;
        return view('pembelian.create')->with(compact('pembelian', 'apoteks', 'jenis_pembelians', 'detail_pembelians', 'var', 'apotek', 'inisial'));
    }

    public function store(Request $request) {
        DB::beginTransaction(); 
        try{
            $pembelian = new TransaksiPembelian;
            $pembelian->fill($request->except('_token'));
            //$detail_pembelians = $request->detail_pembelian;   

            if($pembelian->id_jenis_pembayaran == 2) {
                $pembelian->is_tanda_terima = 1;
                $pembelian->is_lunas = 1;
            } else {
                $pembelian->is_tanda_terima = 0;
                $pembelian->is_lunas = 0;
            }

            $apotek = MasterApotek::find(session('id_apotek_active'));
            $inisial = strtolower($apotek->nama_singkat);
            $apoteks = MasterApotek::whereIn('id', [$apotek->id])->where('is_deleted', 0)->pluck('nama_singkat', 'id');
            $jenis_pembelians = MasterJenisPembelian::where('is_deleted', 0)->pluck('jenis_pembelian', 'id');
            $jenis_pembelians->prepend('-- Pilih Jenis Pembelian --','');
            $tanggal = date('Y-m-d');

            $validator = $pembelian->validate();
            if($validator->fails()){
                $var = 0;
                DB::rollback();
                /*return view('pembelian.create')->with(compact('pembelian', 'apoteks', 'jenis_pembelians', 'var', 'apotek', 'inisial'))->withErrors($validator);*/
                echo json_encode(array('status' => 0));
            }else{
                /*if($pembelian->ppn > 0) {
                    $details = TransaksiPembelianDetail::where('is_deleted', 0)->where('id_nota', $pembelian->id)->get();
                    $jum_details = count($details);
                    if($jum_details > 0) {
                        foreach ($details as $key => $obj) {
                            $obj->harga_beli_ppn = $obj->harga_beli+($pembelian->ppn/100*$obj->harga_beli);
                            $stok_before = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $obj->id_obat)->first();

                            if($stok_before->harga_beli != $obj->harga_beli) {
                                $data_histori_ = array('id_obat' => $obj->id_obat, 'harga_beli_awal' => $stok_before->harga_beli, 'harga_beli_akhir' => $obj->harga_beli, 'harga_jual_awal' => $stok_before->harga_jual, 'harga_jual_akhir' => $stok_before->harga_jual, 'created_by' => Auth::id(), 'created_at' => date('Y-m-d H:i:s'));

                                DB::table('tb_histori_harga_'.$inisial.'')->insert($data_histori_);
                            }

                            $stok_harga = MasterStokHarga::where('id_obat', $obj->id_obat)->first();
                            $stok_harga->updated_at = date('Y-m-d H:i:s'); 
                            $stok_harga->harga_beli = $obj->harga_beli;
                            $stok_harga->harga_beli_ppn = $obj->harga_beli_ppn;
                            $stok_harga->updated_by = Auth::user()->id;
                            if($stok_harga->save()) {
                            } else {
                                DB::rollback();
                                echo json_encode(array('status' => 0));
                            }

                            $histori_stok = HistoriStok::where('id_obat', $obj->id_obat)->where('jumlah', $obj->jumlah)->where('id_jenis_transaksi', 2)->where('id_transaksi', $obj->id)->first();
                            $histori_stok->hb_ppn = $obj->harga_beli_ppn;
                            if($histori_stok->save()) {
                            } else {
                                DB::rollback();
                                echo json_encode(array('status' => 0));
                            }

                            if($obj->save()) {
                            } else {
                                DB::rollback();
                                echo json_encode(array('status' => 0));
                            }
                        }
                    }
                }*/


                if($pembelian->save()) {
                    DB::commit();
                    echo json_encode(array('status' => 1, 'id' => $pembelian->id));
                } else {
                    DB::rollback();
                    echo json_encode(array('status' => 0));
                }
            } 
        }catch(\Exception $e){
            DB::rollback();
            echo json_encode(array('status' => 0));
        }
    }

    public function edit($id) {
        $pembelian = TransaksiPembelian::find($id);
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $apoteks = MasterApotek::whereIn('id', [$apotek->id])->where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $jenis_pembelians = MasterJenisPembelian::where('is_deleted', 0)->pluck('jenis_pembelian', 'id');
        $jenis_pembelians->prepend('-- Pilih Jenis Pembelian --','');
        $tanggal = date('Y-m-d');

        $detail_pembelians = $pembelian->detail_pembalian;
        $var = 0;
        return view('pembelian.edit')->with(compact('pembelian', 'apoteks', 'jenis_pembelians', 'detail_pembelians', 'var', 'apotek', 'inisial'));
    }

    public function show($id) {

    }

    public function update(Request $request, $id) {
        DB::beginTransaction(); 
        try{
            $pembelian = TransaksiPembelian::find($id);
            $pembelian->fill($request->except('_token'));
            $pembelian->updated_at = date('Y-m-d H:i:s');
            $pembelian->updated_by = Auth::user()->id;

            if($pembelian->id_jenis_pembayaran == 2) {
                $pembelian->is_tanda_terima = 1;
                $pembelian->is_lunas = 1;
            } else {
                $pembelian->is_tanda_terima = 0;
                $pembelian->is_lunas = 0;
            }

            $apotek = MasterApotek::find(session('id_apotek_active'));
            $inisial = strtolower($apotek->nama_singkat);

            $validator = $pembelian->validate();
            if($validator->fails()){
                $apotek = MasterApotek::find(session('id_apotek_active'));
                $inisial = strtolower($apotek->nama_singkat);
                $apoteks = MasterApotek::whereIn('id', [$apotek->id])->where('is_deleted', 0)->pluck('nama_singkat', 'id');
                $jenis_pembelians = MasterJenisPembelian::where('is_deleted', 0)->pluck('jenis_pembelian', 'id');
                $jenis_pembelians->prepend('-- Pilih Jenis Pembelian --','');
                $tanggal = date('Y-m-d');

                $var = 0;
                /*return view('pembelian.edit')->with(compact('pembelian', 'apoteks', 'jenis_pembelians', 'var', 'apotek', 'inisial'))->withErrors($validator);*/
                DB::rollback();
                echo json_encode(array('status' => 0));
            }else{
                /*if($pembelian->ppn > 0) {
                    $details = TransaksiPembelianDetail::where('is_deleted', 0)->where('id_nota', $pembelian->id)->get();
                    $jum_details = count($details);
                    if($jum_details > 0) {
                        foreach ($details as $key => $obj) {
                            $obj->harga_beli_ppn = $obj->harga_beli+($pembelian->ppn/100*$obj->harga_beli);
                            $stok_before = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $obj->id_obat)->first();

                            if($stok_before->harga_beli != $obj->harga_beli) {
                                $data_histori_ = array('id_obat' => $obj->id_obat, 'harga_beli_awal' => $stok_before->harga_beli, 'harga_beli_akhir' => $obj->harga_beli, 'harga_jual_awal' => $stok_before->harga_jual, 'harga_jual_akhir' => $stok_before->harga_jual, 'created_by' => Auth::id(), 'created_at' => date('Y-m-d H:i:s'));

                                DB::table('tb_histori_harga_'.$inisial.'')->insert($data_histori_);
                            }

                            $stok_harga = MasterStokHarga::where('id_obat', $obj->id_obat)->first();
                            $stok_harga->updated_at = date('Y-m-d H:i:s'); 
                            $stok_harga->harga_beli = $obj->harga_beli;
                            $stok_harga->harga_beli_ppn = $obj->harga_beli_ppn;
                            $stok_harga->updated_by = Auth::user()->id;
                            if($stok_harga->save()) {
                            } else {
                                DB::rollback();
                                echo json_encode(array('status' => 0));
                            }

                            $histori_stok = HistoriStok::where('id_obat', $obj->id_obat)->where('jumlah', $obj->jumlah)->where('id_jenis_transaksi', 2)->where('id_transaksi', $obj->id)->first();
                            $histori_stok->hb_ppn = $obj->harga_beli_ppn;
                            if($histori_stok->save()) {
                            } else {
                                DB::rollback();
                                echo json_encode(array('status' => 0));
                            }

                            if($obj->save()) {
                            } else {
                                DB::rollback();
                                echo json_encode(array('status' => 0));
                            }
                        }
                    }
                }*/

                if($pembelian->save()) {
                    DB::commit();
                    echo json_encode(array('status' => 1, 'id' => $pembelian->id));
                } else {
                    DB::rollback();
                    echo json_encode(array('status' => 0));
                }
            } 
        }catch(\Exception $e){
            DB::rollback();
            echo json_encode(array('status' => 0));
        }
    }

    public function destroy_($id) {
        DB::beginTransaction(); 
        try{
            $apotek = MasterApotek::find(session('id_apotek_active'));
            $inisial = strtolower($apotek->nama_singkat);
            $pembelian = TransaksiPembelian::find($id);
            $pembelian->is_deleted = 1;

            $detail_pembelians = TransaksiPembelianDetail::where('id_nota', $pembelian->id)->get();
            foreach ($detail_pembelians as $key => $detail_pembelian) {
                $detail_pembelian->is_deleted = 1;
                $detail_pembelian->deleted_at = date('Y-m-d H:i:s');
                $detail_pembelian->deleted_by = Auth::user()->id;
                $detail_pembelian->save();

                $stok_before = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $detail_pembelian->id_obat)->first();
                if($detail_pembelian->id_jenis_revisi == 1) {
                    $jumlah = $detail_pembelian->selisih;
                } else {
                    $jumlah = $detail_pembelian->jumlah;
                }

                $stok_now = $stok_before->stok_akhir-$jumlah;

                # update ke table stok harga
                DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $detail_pembelian->id_obat)->update(['stok_awal'=> $stok_before->stok_akhir, 'stok_akhir'=> $stok_now, 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => Auth::user()->id]);

                # create histori
                DB::table('tb_histori_stok_'.$inisial)->insert([
                    'id_obat' => $detail_pembelian->id_obat,
                    'jumlah' => $jumlah,
                    'stok_awal' => $stok_before->stok_akhir,
                    'stok_akhir' => $stok_now,
                    'id_jenis_transaksi' => 14, //hapus pembelian
                    'id_transaksi' => $detail_pembelian->id,
                    'batch' => $detail_pembelian->id_batch,
                    'ed' => $detail_pembelian->tgl_batch,
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => Auth::user()->id
                ]);
            }

            if($pembelian->save()){
                DB::commit();
                echo 1;
            }else{
                echo 0;
            }
        }catch(\Exception $e){
            DB::rollback();
            session()->flash('error', 'Error!');
            return redirect('penjualan');
        }
    }

    public function open_data_suplier(Request $request) {
        $suplier = $request->suplier;
        return view('pembelian._dialog_open_suplier')->with(compact('suplier'));
    }

    public function list_data_suplier(Request $request)
    {
        $suplier = $request->suplier;

        DB::statement(DB::raw('set @rownum = 0'));
        $data = MasterSuplier::select([
                DB::raw('@rownum  := @rownum  + 1 AS no'),
                'tb_m_suplier.*'
        ])
        ->where(function($query) use($request){
            $query->where('tb_m_suplier.is_deleted','=','0');
        });
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request, $suplier){
            $query->where(function($query) use($request, $suplier){
                $query->orwhere('tb_m_suplier.nama','LIKE','%'.$request->get('search')['value'].'%');
            });
        })   
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .= '<span class="btn btn-primary" onClick="add_suplier_dialog('.$data->id.')" data-toggle="tooltip" data-placement="top" title="pilih suplier"><i class="fa fa-check"></i></span>';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['stok_akhir', 'action'])
        ->addIndexColumn()
        ->make(true);  
    }

    public function cari_suplier_dialog(Request $request) {
        $suplier = MasterSuplier::find($request->id);

        return json_encode($suplier);
    }

    public function find_ketentuan_keyboard(){
        return view('pembelian._form_ketentuan_keyboard');
    }

    public function edit_detail(Request $request){
        $id = $request->id;
        $no = $request->no;
        $detail = TransaksiPembelianDetail::find($id);
        return view('pembelian._form_edit_detail')->with(compact('detail', 'no'));
    }

    public function list_pembelian_revisi(Request $request)
    {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::statement(DB::raw('set @rownum = 0'));
        $data = RevisiPembelian::select([
                DB::raw('@rownum  := @rownum  + 1 AS no'),
                'tb_revisi_pembelian_obat.*', 
        ])
        ->join('tb_detail_nota_pembelian as a', 'a.id', '=', 'tb_revisi_pembelian_obat.id_detail_nota')
        ->where(function($query) use($request){
            $query->where('a.is_deleted','=','0');
            $query->where('a.is_revisi','=','1');
            $query->where('a.id_nota','=',$request->id);
        });
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->orwhere('tb_revisi_pembelian_obat.id','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->editcolumn('tanggal', function($data) use($request){
            return Carbon::parse($data->created_at)->format('d/m/Y H:i:s');
        })
        ->editcolumn('detail_obat', function($data) use($request){
            $string = '<b>'.$data->obat->nama.'<b><br>';
            return $string;
        })
        ->editcolumn('kasir', function($data) use($request){
            return $data->created_oleh->nama;
        })
        ->rawColumns(['kasir', 'tanggal', 'detail_obat'])
        ->addIndexColumn()
        ->make(true);  
    }

    public function data_pembelian_item() {
        return view('pembelian.data_pembelian_item');
    }

    public function list_pembelian_item(Request $request) {

    }

    public function konfirmasi_barang_datang($id) {
        $id = decrypt($id);
        $order = TransaksiOrder::select('tb_nota_order.*')
                                ->where('tb_nota_order.is_deleted', 0)
                                ->where('tb_nota_order.is_status', 0)
                                ->where('tb_nota_order.id_apotek', session('id_apotek_active'))
                                ->where('id', $id)
                                ->first();

        $jenisSP = JenisSP::pluck('jenis', 'id');
        $jenisSP->prepend('-- Pilih Jenis SP --','');
                
        //$supliers = MasterSuplier::where('is_deleted', 0)->pluck('nama','id');
        $idPembelianRelasi = TransaksiOrderDetail::select('tb_detail_nota_order.id_nota_pembelian')
                                ->where('tb_detail_nota_order.is_deleted', 0)
                                ->where('id_nota', $id)
                                ->get();
        $pembelians = TransaksiPembelian::whereIn('id', $idPembelianRelasi)->where('is_deleted', 0)->get();

        //print_r($pembelians);exit();

        return view('konfirmasi_barang.create')->with(compact('order', 'pembelians', 'jenisSP'));
    }

    public function konfirmasi_barang_store(Request $request) {
        $pembelian = new TransaksiPembelian;
        $pembelian->fill($request->except('_token'));
        $details = explode(",",$request->arr_id_order);
        $id_jenis_konfirmasi = $request->id_jenis_konfirmasi;
        $id_pembelian = $request->id_nota_pembelian;

        $id_det_order = array();
        $detail_pembelians = array();
        $id_order = '';
        $id_suplier = '';
        foreach ($details as $key => $val) {
            $id_det_order[] = $val;
            $order = TransaksiOrderDetail::select(['tb_detail_nota_order.*'])
                        ->where('tb_detail_nota_order.id', $val)
                        ->first();
            $id_order = $order->id_nota;
        }

        if($id_order == '') {
            $id_order = encrypt($id_order);
            session()->flash('error', 'Data SP tidak ditemukan !');
            return redirect('pembelian/konfirmasi_barang_datang/'.$id_order);
        } else {
            $order = TransaksiOrder::find($id_order);
            $pembelian->id_suplier = $order->id_suplier;
        }

        if($id_pembelian == '') {
        } else {
            $pembelian = TransaksiPembelian::find($id_pembelian);
        }

        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $apoteks = MasterApotek::whereIn('id', [$apotek->id])->where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $jenis_pembelians = MasterJenisPembelian::where('is_deleted', 0)->pluck('jenis_pembelian', 'id');
        $jenis_pembelians->prepend('-- Pilih Jenis Pembelian --','');
        $tanggal = date('Y-m-d');

        $details = json_encode($id_det_order);
        $var = 1;
        $is_from_order = 1;
        return view('pembelian_order.create')->with(compact('pembelian', 'apoteks', 'jenis_pembelians', 'apotek', 'detail_pembelians', 'var', 'is_from_order', 'details', 'order'));
        
    }

    public function set_konfirm_barang_tidak_diterima(Request $request) {
        DB::beginTransaction(); 
        try{
            $arr_id_order = $request->arr_id_order;

            $orderDets = TransaksiOrderDetail::select([
                            'tb_detail_nota_order.*'
                        ])
                        ->where('is_deleted', 0)
                        ->whereIn('id', $arr_id_order)
                        ->get();

            foreach($orderDets as $obj) {
                $obj->is_status = 2;
                $obj->updated_by = Auth::user()->id;
                $obj->updated_at = date('Y-m-d H:i:s');
                $obj->save();

                if(isset($obj->id_defecta)) {
                    $defecta = DefectaOutlet::find($obj->id_defecta);
                    //setelah itu, update tabel temp order
                    $defecta->id_process = 2;
                    $defecta->save();

                    DefectaOutletHistori::create([
                        'id_defecta' => $defecta->id,
                        'id_status' => 4,
                        'id_process' => 2,
                        'created_at' => date('Y-m-d H:i:s'),
                        'created_by' => Auth::user()->id,
                        'updated_at' => date('Y-m-d H:i:s'),
                        'updated_by' => Auth::user()->id,
                    ]);
                }
            }

            DB::commit();
            return response()->json(array(
                'submit' => 1,
                'message' => 'data berhasil disimpan',
            ));
        }catch(\Exception $e){
            DB::rollback();
            return response()->json(array(
                    'submit' => 0,
                    'message' => $e->getMessage(),
                ));
        }
    }

    public function list_data_order(Request $request) {
        DB::statement(DB::raw('set @rownum = 0'));
        $data = TransaksiOrderDetail::select([
                DB::raw('@rownum  := @rownum  + 1 AS no'),
                'tb_detail_nota_order.*'
        ])
        ->join('tb_nota_order', 'tb_nota_order.id', '=', 'tb_detail_nota_order.id_nota')
        ->join('tb_m_obat', 'tb_m_obat.id', '=', 'tb_detail_nota_order.id_obat')
        ->where(function($query) use($request){
            $query->where('tb_detail_nota_order.is_deleted','=','0');
            $query->where('tb_detail_nota_order.is_status','=','0');
            $query->where('tb_detail_nota_order.id_nota', $request->id_nota);
            $query->where('tb_nota_order.id_apotek', session('id_apotek_active'));
            //$query->where('tb_nota_order.id_jenis','LIKE',($request->id_jenis > 0 ? $request->id_jenis : '%'.$request->id_jenis.'%'));
        });
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request){
            $query->where(function($query) use($request){
                $query->orwhere('tb_detail_nota_order.id','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('tb_m_obat.nama','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('tb_m_obat.barcode','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('tb_m_obat.sku','LIKE','%'.$request->get('search')['value'].'%');
            });
        })   
        ->addColumn('checkList', function ($data) {
            return '<input type="checkbox" name="check_list" data-id="'.$data->id.'" data-id_apotek="'.$data->id_apotek.'" value="'.$data->id.'"/>';
            //return '<input type="checkbox" name="detail_order['.$data->no.'][id_detail_order]" id="detail_order['.$data->no.'][id_detail_order]" value="'.$data->id.'">';
        })
        /*->editcolumn('checkList', function($data){
            return '<input type="checkbox" name="detail_order['.$data->no.'][id_detail_order]" id="detail_order['.$data->no.'][id_detail_order]" value="'.$data->id.'">';
        })*/
        ->editcolumn('id_obat', function($data) {
            $string = $data->obat->nama;
            $string .= '<br><small>Keterangan : '.$data->keterangan.'</small>';
            return $string;
        })
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            /*$btn .= '<span class="btn btn-primary" onClick="add_suplier_dialog('.$data->id.')" data-toggle="tooltip" data-placement="top" title="pilih suplier"><i class="fa fa-check"></i></span>';*/

            if($data->id_obat == 0) {
                $btn .= '<a href="#" onClick="add_data_obat('.$data->id.')" title="Add data obat" data-toggle="modal" ><span class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="Add data obat"><i class="fa fa-plus"></i></span> </a>';
            } else {
                $btn .= '<p style="color:#ff5722;">-</p>';
            }

            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['select', 'action', 'checkList', 'id_obat'])
        ->addIndexColumn()
        ->make(true);  
    }

    public function edit_detail_from_order(Request $request){
        DB::beginTransaction(); 
        try{
            $id = $request->id_detail_order;
            $no = $request->no;
            $order = TransaksiOrderDetail::find($id);
            if(is_null($order->id_det_nota_pembalian)) {
                $detail = new TransaksiPembelianDetail;
                $pembelian = new TransaksiPembelian;
            } else {
                $detail = TransaksiPembelianDetail::find($order->id_det_nota_pembalian);
                $pembelian = TransaksiPembelian::find($detail->id_nota);
                /*$detail->is_deleted = 1;
                $detail->deleted_at = date('Y-m-d H:i:s');
                $detail->deleted_by = Auth::user()->id;                
               
                # crete histori stok barang
                $apotek = MasterApotek::find(session('id_apotek_active'));
                $inisial = strtolower($apotek->nama_singkat);
                $stok_before = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $detail->id_obat)->first(); 
                $stok_now = $stok_before->stok_akhir-$detail->jumlah;

                # update ke table stok harga
                $stok_harga = MasterStokHarga::where('id_obat', $detail->id_obat)->first();
                $stok_harga->stok_awal = $stok_before->stok_akhir;
                $stok_harga->stok_akhir = $stok_now;
                $stok_harga->updated_at = date('Y-m-d H:i:s'); 
                $stok_harga->updated_by = Auth::user()->id;
                if($stok_harga->save()) {
                } else {
                    DB::rollback();
                    return "Error, gagal save data di master stok harga.";
                }
                
                $histori_stok = new HistoriStok;
                $histori_stok->id_obat = $detail->id_obat;
                $histori_stok->jumlah = $detail->jumlah;
                $histori_stok->stok_awal = $stok_before->stok_akhir;
                $histori_stok->stok_akhir = $stok_now;
                $histori_stok->id_jenis_transaksi = 14; //hapus pembelian
                $histori_stok->id_transaksi = $detail->id;
                $histori_stok->batch = null;
                $histori_stok->ed = null;
                $histori_stok->sisa_stok = null;
                $histori_stok->hb_ppn = $detail->harga_beli_ppn;
                $histori_stok->created_at = date('Y-m-d H:i:s');
                $histori_stok->created_by = Auth::user()->id;
                if($histori_stok->save()) {
                } else {
                    DB::rollback();
                    return "Error, gagal save data di histori stok."; exit();
                }

                # update stok aktif 
                $cekHistori = HistoriStok::where('id_jenis_transaksi', 2)->where('id_transaksi', $detail->id)->first();

                if($cekHistori->sisa_stok < $detail->jumlah) {
                    $kurangStok = $this->kurangStok($detail->id, $detail->id_obat, $detail->jumlah);
                    if($kurangStok['status'] == 0) {
                        DB::rollback();
                        return "Error, gagal save data, stok yang ada saat ini kurang untuk melakukan penghapusan data."; exit();
                    } else {
                        $detail->id_histori_stok = $kurangStok['array_id_histori_stok'];
                        $detail->id_histori_stok_detail = $kurangStok['array_id_histori_stok_detail'];
                        if($detail->save()) {
                        } else {
                            DB::rollback();
                            return "Error, gagal save data detail pembelian."; exit();
                        }
                    }
                } else {
                    $keterangan = $cekHistori->keterangan.', Hapus Pembelian pada IDdet.'.$detail->id.' sejumlah '.$detail->jumlah;
                    $cekHistori->sisa_stok = $cekHistori->sisa_stok - $detail->jumlah;
                    $cekHistori->keterangan = $keterangan;
                    if($cekHistori->save()) {
                    } else {
                        DB::rollback();
                        return "Error, gagal update data histori stok."; exit();
                    }
                }

                if($detail->save()) {
                    # cek apakah masih ada item pada nota yang sama
                    $jum_details = TransaksiPembelianDetail::where('is_deleted', 0)->where('id_nota', $detail->id_nota)->count();
                     $is_sisa = 1;
                    if($jum_details == 0) {
                        $pembelian->is_deleted = 1;
                        $pembelian->deleted_at = date('Y-m-d H:i:s');
                        $pembelian->deleted_by = Auth::user()->id;
                        if($pembelian->save()) {
                        } else {
                            DB::rollback();
                            return "Error, gagal update data pembelian."; exit();
                        }
                        $is_sisa = 0;
                    }
                } else {
                    DB::rollback();
                    return "Error, gagal update data detail pembelian."; exit();
                }

                DB::commit();

                $detail = new TransaksiPembelianDetail;
                $pembelian = new TransaksiPembelian;*/
            }

            $detailOrder = TransaksiOrderDetail::find($id);
            /*$detailOrder->id_nota_pembelian = null;
            $detailOrder->id_det_nota_pembalian = null;
            $detailOrder->save();*/

            return view('pembelian_order._form_edit_detail_from_order')->with(compact('detail', 'no', 'order', 'pembelian', 'detailOrder'));
        }catch(\Exception $e){
            DB::rollback();
            session()->flash('error', 'Error!');
            return redirect('pembelian');
        }
    }

    public function cek_tanda_terima_faktur(Request $request)
    {
        $pembelian = TransaksiPembelian::find($request->id);
        $pembelian->is_tanda_terima = 1;
        $pembelian->tanda_terima_at = date('Y-m-d H:i:s');
        $pembelian->tanda_terima_by = Auth::user()->id;

        if($pembelian->save()){
            echo 1;
        }else{
            echo 0;
        }
    } 

    public function pembayaran_faktur_belum_lunas(){
        $apoteks = MasterApotek::where('is_deleted', 0)->get();
        $supliers = MasterSuplier::where('is_deleted', 0)->get();
        $date_now = date('Y-m-d');

        return view('pembayaran_faktur._form_pembayaran_faktur_belum_lunas')->with(compact('supliers', 'apoteks', 'date_now'));
    }

    public function list_pembayaran_faktur_belum_lunas(Request $request)
    {
        DB::statement(DB::raw('set @rownum = 0'));
        $data = TransaksiPembelian::select([
                DB::raw('@rownum  := @rownum  + 1 AS no'),
                'tb_nota_pembelian.*'])
                ->where(function($query) use($request){
                    $query->where('tb_nota_pembelian.is_deleted','=','0');
                    $query->where('tb_nota_pembelian.is_tanda_terima','=','1');
                    $query->where('tb_nota_pembelian.is_lunas','=','0');
                    $query->where('tb_nota_pembelian.id_apotek','LIKE',($request->id_apotek > 0 ? $request->id_apotek : '%'.$request->id_apotek.'%'));
                    $query->where('tb_nota_pembelian.id_suplier','LIKE',($request->id_suplier > 0 ? $request->id_suplier : '%'.$request->id_suplier.'%'));
                    $query->where('tb_nota_pembelian.is_lunas','LIKE',($request->id_status_lunas > 0 ? $request->id_status_lunas : '%'.$request->id_status_lunas.'%'));
                    if (!empty($request->tgl_awal) && !empty($request->tgl_akhir)) {
                        $query->where('tb_nota_pembelian.tgl_jatuh_tempo','>=', $request->tgl_awal);
                        $query->where('tb_nota_pembelian.tgl_jatuh_tempo','<=', $request->tgl_akhir);
                    }
                })
                ->orderBy('tgl_jatuh_tempo','asc')
                ->orderBy('id_suplier')
                ->groupBy('tb_nota_pembelian.id');
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request){
            $query->where(function($query) use($request){
                $query->orwhere('tb_nota_pembelian.id','LIKE','%'.$request->get('search')['value'].'%');
            });
        })   
        ->editcolumn('suplier', function($data){
            return $data->suplier->nama;
        })
        ->editcolumn('apotek', function($data){
            return $data->apotek->nama_panjang;
        })
        ->editcolumn('jenis_pembelian', function($data){
            if ($data->id_jenis_pembelian == 3) {
                return $data->jenis_pembelian->jenis_pembelian.'<br><a href="'.url('/pembelian/pembayaran_konsinyasi/'.$data->id).'" title="Ubah" data-toggle="modal" data-id="{!! $id !!}" ><span class="label label-warning" data-toggle="tooltip" data-placement="top" title="Konsinyasi"><i class="fa fa-cogs"></i> Set Pembayaran</span></a>';
            }else {
                return $data->jenis_pembelian->jenis_pembelian;
            }
        })
        ->editcolumn('jumlah', function($data){
            $x = $data->detail_pembelian_total[0];
            $total1 = $x->jumlah - ($data->diskon1 + $data->diskon2);
            $total2 = $total1 + ($total1 * $data->ppn/100);
            return "Rp ".number_format($total2,2);
        })
        ->editcolumn('is_lunas', function($data){
            if ($data->is_lunas == 0) {
                return '<span class="text-info"><i class="fa fa-fw fa-question"></i>Belum Lunas</span>';
            } else if($data->is_lunas == 1) {
                return '<span class="text-success"><i class="fa fa-fw fa-check"></i>Lunas</span>';
            }
           
        }) 
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';

            $btn .= '<a href="#" onClick="lihat_detail_faktur('.$data->id.')" title="Detail Faktur" data-toggle="modal" ><span class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="Lihat Faktur"><i class="fa fa-eye"></i></span> </a>';
            $btn .= '<a href="#" onClick="lunas_pembayaran('.$data->id.')" title="Set Lunas Faktur" data-toggle="modal" ><span class="btn btn-success" data-toggle="tooltip" data-placement="top" title="Set Lunas"><i class="fa fa-check"></i></span> </a>';

            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['suplier', 'action', 'apotek', 'jenis_pembelian', 'jumlah', 'is_lunas'])
        ->addIndexColumn()
        ->make(true);  
    }

    public function pembayaran_faktur_lunas(){
        $apoteks = MasterApotek::where('is_deleted', 0)->get();
        $supliers = MasterSuplier::where('is_deleted', 0)->get();
        $date_now = date('Y-m-d');

        return view('pembayaran_faktur._form_pembayaran_faktur_lunas')->with(compact('supliers', 'apoteks', 'date_now'));
    }

    public function list_pembayaran_faktur_lunas(Request $request)
    {
        DB::statement(DB::raw('set @rownum = 0'));
        $data = TransaksiPembelian::select([
                DB::raw('@rownum  := @rownum  + 1 AS no'),
                'tb_nota_pembelian.*'])
                ->where(function($query) use($request){
                    $query->where('tb_nota_pembelian.is_deleted','=','0');
                    $query->where('tb_nota_pembelian.is_tanda_terima','=','1');
                    $query->where('tb_nota_pembelian.is_lunas','=','1');
                    $query->where('tb_nota_pembelian.id_apotek','LIKE',($request->id_apotek > 0 ? $request->id_apotek : '%'.$request->id_apotek.'%'));
                    $query->where('tb_nota_pembelian.id_suplier','LIKE',($request->id_suplier > 0 ? $request->id_suplier : '%'.$request->id_suplier.'%'));
                    $query->where('tb_nota_pembelian.is_lunas','LIKE',($request->id_status_lunas > 0 ? $request->id_status_lunas : '%'.$request->id_status_lunas.'%'));
                    if (!empty($request->tgl_awal) && !empty($request->tgl_akhir)) {
                        $query->where('tb_nota_pembelian.tgl_jatuh_tempo','>=', $request->tgl_awal);
                        $query->where('tb_nota_pembelian.tgl_jatuh_tempo','<=', $request->tgl_akhir);
                    }
                })
                ->orderBy('tgl_jatuh_tempo','asc')
                ->orderBy('id_suplier')
                ->groupBy('tb_nota_pembelian.id');
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request){
            $query->where(function($query) use($request){
                $query->orwhere('tb_nota_pembelian.id','LIKE','%'.$request->get('search')['value'].'%');
            });
        })   
        ->editcolumn('suplier', function($data){
            return $data->suplier->nama;
        })
        ->editcolumn('apotek', function($data){
            return $data->apotek->nama_panjang;
        })
        ->editcolumn('jenis_pembelian', function($data){
            if ($data->id_jenis_pembelian == 3) {
                return $data->jenis_pembelian->jenis_pembelian.'<br><a href="'.url('/pembelian/pembayaran_konsinyasi/'.$data->id).'" title="Ubah" data-toggle="modal" data-id="{!! $id !!}" ><span class="label label-warning" data-toggle="tooltip" data-placement="top" title="Konsinyasi"><i class="fa fa-cogs"></i> Set Pembayaran</span></a>';
            }else {
                return $data->jenis_pembelian->jenis_pembelian;
            }
        })
        ->editcolumn('jumlah', function($data){
            $x = $data->detail_pembelian_total[0];
            $total1 = $x->jumlah - ($data->diskon1 + $data->diskon2);
            $total2 = $total1 + ($total1 * $data->ppn/100);
            return "Rp ".number_format($total2,2);
        })
        ->editcolumn('is_lunas', function($data){
            if ($data->is_lunas == 0) {
                return '<span class="text-info"><i class="fa fa-fw fa-question"></i>Belum Lunas</span>';
            } else if($data->is_lunas == 1) {
                return '<span class="text-success"><i class="fa fa-fw fa-check"></i>Lunas</span>';
            }
           
        })   
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';

            $btn .= '<a href="#" onClick="lihat_detail_faktur('.$data->id.')" title="Detail Faktur" data-toggle="modal" ><span class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="Lihat Faktur"><i class="fa fa-eye"></i></span> </a>';

            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['suplier', 'action', 'apotek', 'jenis_pembelian', 'jumlah', 'is_lunas'])
        ->addIndexColumn()
        ->make(true);  
    }

    public function pembayaran_faktur(){
        $apoteks = MasterApotek::where('is_deleted', 0)->get();
        $supliers = MasterSuplier::where('is_deleted', 0)->get();
        $date_now = date('Y-m-d');

        return view('pembayaran_faktur.index')->with(compact('supliers', 'apoteks', 'date_now'));
    }

    public function list_pembayaran_faktur(Request $request)
    {
        DB::statement(DB::raw('set @rownum = 0'));
        $data = TransaksiPembelian::select([
                DB::raw('@rownum  := @rownum  + 1 AS no'),
                'tb_nota_pembelian.*'])
                ->where(function($query) use($request){
                    $query->where('tb_nota_pembelian.is_deleted','=','0');
                    $query->where('tb_nota_pembelian.is_tanda_terima','=','1');
                    $query->where('tb_nota_pembelian.id_apotek','LIKE',($request->id_apotek > 0 ? $request->id_apotek : '%'.$request->id_apotek.'%'));
                    $query->where('tb_nota_pembelian.id_suplier','LIKE',($request->id_suplier > 0 ? $request->id_suplier : '%'.$request->id_suplier.'%'));
                    $query->where('tb_nota_pembelian.is_lunas','LIKE',($request->id_status_lunas > 0 ? $request->id_status_lunas : '%'.$request->id_status_lunas.'%'));
                    if (!empty($request->tgl_awal) && !empty($request->tgl_akhir)) {
                        $query->where('tb_nota_pembelian.tgl_jatuh_tempo','>=', $request->tgl_awal);
                        $query->where('tb_nota_pembelian.tgl_jatuh_tempo','<=', $request->tgl_akhir);
                    }
                })
                ->orderBy('tgl_jatuh_tempo','asc')
                ->orderBy('id_suplier')
                ->groupBy('tb_nota_pembelian.id');
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request){
            $query->where(function($query) use($request){
                $query->orwhere('tb_nota_pembelian.id','LIKE','%'.$request->get('search')['value'].'%');
            });
        })   
        ->editcolumn('suplier', function($data){
            return $data->suplier->nama;
        })
        ->editcolumn('apotek', function($data){
            return $data->apotek->nama_panjang;
        })
        ->editcolumn('jenis_pembelian', function($data){
            if ($data->id_jenis_pembelian == 3) {
                $btn = '<a href="'.url('/pembelian/pembayaran_konsinyasi/'.$data->id).'" title="Pembayaran Konsinyasi" class="btn btn-info btn-sm"><span data-toggle="tooltip" data-placement="top" title="Pembayaran Konsinyasi"><i class="fa fa-cogs"></i> Set Pembayaran</span></a>';
                return $data->jenis_pembelian->jenis_pembelian.'<br>'.$btn;
            }else {
                return $data->jenis_pembelian->jenis_pembelian;
            }
        })
        ->editcolumn('jumlah', function($data){
            $x = $data->detail_pembelian_total[0];
            $total1 = $x->jumlah - ($data->diskon1 + $data->diskon2);
            $total2 = $total1 + ($total1 * $data->ppn/100);
            return "Rp ".number_format($total2,2);
        })
        ->editcolumn('is_lunas', function($data){
            if ($data->is_lunas == 0) {
                return '<span class="text-info"><i class="fa fa-fw fa-question"></i>Belum Lunas</span>';
            } else if($data->is_lunas == 1) {
                return '<span class="text-success"><i class="fa fa-fw fa-check"></i>Lunas</span>';
            }
        })  
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';

            $btn .= '<a href="#" onClick="lihat_detail_faktur('.$data->id.')" title="Detail Faktur" data-toggle="modal" ><span class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="Lihat Faktur"><i class="fa fa-eye"></i></span> </a>';
            $btn .= '<a href="#" onClick="lunas_pembayaran('.$data->id.')" title="Set Lunas Faktur" data-toggle="modal" ><span class="btn btn-success" data-toggle="tooltip" data-placement="top" title="Set Lunas"><i class="fa fa-check"></i></span> </a>';

            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['suplier', 'action', 'apotek', 'jenis_pembelian', 'jumlah', 'is_lunas'])
        ->addIndexColumn()
        ->make(true);  
    }

    public function lunas_pembayaran(Request $request)
    {
        $pembelian = TransaksiPembelian::find($request->id);
        $pembelian->is_lunas = 1;
        $pembelian->lunas_at = date('Y-m-d H:i:s');
        $pembelian->lunas_by = Auth::user()->id;

        if($pembelian->save()){
            echo 1;
        }else{
            echo 0;
        }
    } 

    public function lihat_detail_faktur(Request $request)
    {
        $pembelian = TransaksiPembelian::find($request->id);

        return view('pembayaran_faktur._form_lihat_detail_faktur')->with(compact('pembelian'));
    } 

    public function reload_harga_beli_ppn() {
        $pembelian = TransaksiPembelian::all();
        $i = 0;
        foreach ($pembelian as $key => $val) {
            $detail_pembelians = TransaksiPembelianDetail::where('id_nota', $val->id)->get();
            foreach ($detail_pembelians as $x => $obj) {
                $i++;
                $obj->harga_beli_ppn = $obj->harga_beli+($val->ppn/100*$obj->harga_beli);
                $obj->save();
            }
        }

        echo $i." data";
    }


    public function reload_harga_ppn_form_outlet($id) {
        $apotek = MasterApotek::find($id);
        $inisial = strtolower($apotek->nama_singkat);
        $data = DB::table('tb_m_stok_harga_'.$inisial.'')->get();
        foreach ($data as $key => $val) {
            $cari_last = TransaksiPembelianDetail::where('id_obat', $val->id_obat)->orderBy('id_old', 'DESC')->first();
            if(!empty($cari_last)) {
                if($val->harga_beli != $cari_last->harga_beli) {
                    $data_histori_ = array('id_obat' => $val->id_obat, 'harga_beli_awal' => $val->harga_beli, 'harga_beli_akhir' => $cari_last->harga_beli, 'harga_jual_awal' => $val->harga_jual, 'harga_jual_akhir' => $val->harga_jual, 'created_by' => Auth::id(), 'created_at' => date('Y-m-d H:i:s'));

                    DB::table('tb_histori_harga_'.$inisial.'')->insert($data_histori_);

                    // update harga beli dan harga beli ppn
                    DB::table('tb_m_stok_harga_'.$inisial.'')
                        ->where('id', $val->id)
                        ->update(['harga_beli' => $cari_last->harga_beli, 'harga_beli_ppn' => $cari_last->harga_beli_ppn, 'updated_by' => Auth::user()->id, 'updated_at' => date('Y-m-d H:i:s')]);
                } else {
                    // update harga beli ppn
                    DB::table('tb_m_stok_harga_'.$inisial.'')
                        ->where('id', $val->id)
                        ->update(['harga_beli_ppn' => $cari_last->harga_beli_ppn, 'updated_by' => Auth::user()->id, 'updated_at' => date('Y-m-d H:i:s')]);
                }
            } else {
                DB::table('tb_m_stok_harga_'.$inisial.'')
                        ->where('id', $val->id)
                        ->update(['harga_beli_ppn' => $val->harga_beli, 'updated_by' => Auth::user()->id, 'updated_at' => date('Y-m-d H:i:s')]);
            }
        }
    }

    public function pencarian_obat() {
        return view('pembelian.pencarian_obat');
    }

    public function list_pencarian_obat(Request $request) {
        DB::statement(DB::raw('set @rownum = 0'));
        $data = TransaksiPembelianDetail::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_detail_nota_pembelian.*', 'a.nama','b.no_faktur'])
        ->join('tb_m_obat as a', 'a.id', 'tb_detail_nota_pembelian.id_obat')
        ->join('tb_nota_pembelian as b', 'b.id', 'tb_detail_nota_pembelian.id_nota')
        ->where(function($query) use($request){
            $query->where('tb_detail_nota_pembelian.is_deleted','=','0');
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
                $query->orwhere('b.no_faktur','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->editcolumn('created_at', function($data) use($request){
            return Carbon::parse($data->created_at)->format('d/m/Y H:i:s');
        })
        ->editcolumn('created_by', function($data) use($request){
            return $data->created_oleh->nama;
        })
        ->editcolumn('id_obat', function($data) {
            $info = '<small>No Faktur : '.$data->no_faktur.' | Batch : '.$data->id_batch.' | Tanggal Batch : '.Carbon::parse($data->tgl_batch)->format('d/m/Y').'</small>';
            return $data->nama.'<br>'.$info;
        })
        ->editcolumn('total', function($data) {
            $total = ($data->jumlah*$data->harga_beli)-$data->diskon;
            if($total == "" || $total == null) {
                $total = 0;
            }
            $diskon = $data->diskon_persen/100*$total;
            $total2 = $total-$diskon;
            $str_ = '';
            $str_ = $data->jumlah.' X Rp '.number_format($data->harga_beli, 2)."-(Rp ".number_format($diskon,2).') = Rp '.number_format($total2,2);
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
        $rekaps = TransaksiPembelian::select([
                            DB::raw('@rownum  := @rownum  + 1 AS no'),
                            'tb_nota_pembelian.*'])
                            ->where(function($query) use($request){
                                $query->where('tb_nota_pembelian.is_deleted','=','0');
                                $query->where('tb_nota_pembelian.is_tanda_terima','=','1');
                                if($request->id_suplier != 0 AND $request->id_suplier != "") {
                                    $query->where('tb_nota_pembelian.id_suplier',$request->id_suplier);
                                }

                                if($request->id_apotek != 0 AND $request->id_apotek != "") {
                                    $query->where('tb_nota_pembelian.id_apotek',$request->id_apotek);
                                }

                                if($request->id_status_lunas != "") {
                                    $query->where('tb_nota_pembelian.is_lunas',$request->id_status_lunas);
                                }

                                if (!empty($request->tgl_awal) && !empty($request->tgl_akhir)) {
                                    $query->where('tb_nota_pembelian.tgl_jatuh_tempo','>=', $request->tgl_awal);
                                    $query->where('tb_nota_pembelian.tgl_jatuh_tempo','<=', $request->tgl_akhir);
                                }

                                /*if($request->tgl_awal_faktur != "") {
                                    $tgl_awal_faktur       = date('Y-m-d H:i:s',strtotime($request->tgl_awal_faktur));
                                    $query->whereDate('tb_nota_pembelian.tgl_faktur','>=', $tgl_awal_faktur);
                                }

                                if($request->tgl_akhir_faktur != "") {
                                    $tgl_akhir_faktur      = date('Y-m-d H:i:s',strtotime($request->tgl_akhir_faktur));
                                    $query->whereDate('tb_nota_pembelian.tgl_faktur','<=', $tgl_akhir_faktur);
                                }*/
                            })
                            ->orderBy('tgl_jatuh_tempo','asc')
                            ->orderBy('id_suplier')
                            ->groupBy('tb_nota_pembelian.id')
                            ->get();
           /* print_r($rekaps);
            exit();*/
                $collection = collect();
                $no = 0;
                $total_excel=0;
                foreach($rekaps as $rekap) {
                    $no++;
                    $x = $rekap->detail_pembelian_total[0];
                    $total1 = $x->jumlah - ($rekap->diskon1 + $rekap->diskon2);
                    $total2 = $total1 + ($total1 * $rekap->ppn/100);

                    $collection[] = array(
                        $no,
                        $rekap->tgl_faktur,
                        $rekap->tgl_jatuh_tempo,
                        $rekap->suplier->nama,
                        $rekap->apotek->nama_singkat,
                        $rekap->no_faktur,
                        $total2,
                        'Rp '.number_format($total2,2),
                        '',
                    );
                }


        return Excel::download(new class($collection) implements FromCollection, WithHeadings, WithColumnWidths, WithStyles {

                    public function __construct($collection)
                    {
                        $this->collection = $collection;
                    }

                    public function headings(): array
                    {
                        return [
                        'No', 'Tanggal Faktur', 'Tgl Jatuh Tempo', 'Suplier', 'Apotek', 'No Faktur', 'Total', 'Total Format', 'TTD'
                        ];
                    } 

                    public function columnWidths(): array
                    {
                        return [
                            'A' => 8,
                            'B' => 20,
                            'C' => 20,
                            'D' => 35,
                            'E' => 15,
                            'F' => 15,
                            'G' => 18,
                            'H' => 18,
                            'I' => 20,           
                        ];
                    }

                    public function styles(Worksheet $sheet)
                    {
                        return [
                            1    => ['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'A'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'B'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'C'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'E'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'F'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'H'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                        ];
                    }

                    public function collection()
                    {
                        return $this->collection;
                    }
        },"Pembayaran Faktur.xlsx");
    }

    public function export_all(Request $request) 
    {
        $rekaps = TransaksiPembelian::select([
                                    DB::raw('@rownum  := @rownum  + 1 AS no'),
                                    'tb_nota_pembelian.*', 
                            ])
                            ->where(function($query) use($request){
                                $query->where('tb_nota_pembelian.is_deleted','=','0');
                                $query->where('tb_nota_pembelian.id_apotek_nota','=',session('id_apotek_active'));
                                $query->where('tb_nota_pembelian.no_faktur','LIKE',($request->no_faktur > 0 ? $request->no_faktur : '%'.$request->no_faktur.'%'));
                                
                                if($request->id_jenis_pembelian != 0 AND $request->id_jenis_pembelian != "") {
                                    $query->where('tb_nota_pembelian.id_jenis_pembelian',$request->id_jenis_pembelian);
                                }

                                if($request->id_suplier != 0 AND $request->id_suplier != "") {
                                    $query->where('tb_nota_pembelian.id_suplier',$request->id_suplier);
                                }

                                if($request->id_apotek != 0 AND $request->id_apotek != "") {
                                    //$query->where('tb_nota_pembelian.id_apotek',$request->id_apotek);
                                }
                                
                               // $query->where('tb_nota_pembelian.id_apotek','LIKE',($request->id_apotek > 0 ? $request->id_apotek : '%'.$request->id_apotek.'%'));
                               // $query->where('tb_nota_pembelian.id_supliers','LIKE',($request->id_suplier > 0 ? $request->id_suplier : '%'.$request->id_suplier.'%'));
                                if($request->tgl_awal != "") {
                                    $tgl_awal       = date('Y-m-d H:i:s',strtotime($request->tgl_awal));
                                    $query->whereDate('tb_nota_pembelian.tgl_jatuh_tempo','>=', $tgl_awal);
                                }

                                if($request->tgl_akhir != "") {
                                    $tgl_akhir      = date('Y-m-d H:i:s',strtotime($request->tgl_akhir));
                                    $query->whereDate('tb_nota_pembelian.tgl_jatuh_tempo','<=', $tgl_akhir);
                                }

                                if($request->tgl_awal_faktur != "") {
                                    $tgl_awal_faktur       = date('Y-m-d H:i:s',strtotime($request->tgl_awal_faktur));
                                    $query->whereDate('tb_nota_pembelian.tgl_faktur','>=', $tgl_awal_faktur);
                                }

                                if($request->tgl_akhir_faktur != "") {
                                    $tgl_akhir_faktur      = date('Y-m-d H:i:s',strtotime($request->tgl_akhir_faktur));
                                    $query->whereDate('tb_nota_pembelian.tgl_faktur','<=', $tgl_akhir_faktur);
                                }
                            })
                            ->orderBy('tgl_jatuh_tempo','asc')
                            ->orderBy('id_suplier')
                            ->groupBy('tb_nota_pembelian.id')
                            ->get();

                $collection = collect();
                $no = 0;
                $total_excel=0;
                foreach($rekaps as $rekap) {
                    $no++;
                    $x = $rekap->detail_pembelian_total[0];
                    $total1 = $x->jumlah - ($rekap->diskon1 + $rekap->diskon2);
                    $total2 = $total1 + ($total1 * $rekap->ppn/100);
                    $lunas = "Belum Dibayar";
                    $tgl_bayar = '';
                    if($rekap->is_lunas == 1) {
                        $lunas = "Lunas";
                        $tgl_bayar = $rekap->lunas_at;
                    }
                    
                    $pembayaran = "Pembayaran Langsung";
                    if($rekap->id_jenis_pembayaran == 1) {
                        $pembayaran = "Pembayaran Tidak Langsung";
                    }

                    $collection[] = array(
                        $no,
                        $rekap->tgl_faktur,
                        $rekap->tgl_jatuh_tempo,
                        $rekap->jenis_pembelian->jenis_pembelian,
                        $pembayaran,
                        $rekap->suplier->nama,
                        $rekap->apotek->nama_singkat,
                        $rekap->no_faktur,
                        $total2,
                        'Rp '.number_format($total2,2),
                        $lunas,
                        $tgl_bayar,
                        '',
                    );
                }

        return Excel::download(new class($collection) implements FromCollection, WithHeadings, WithColumnWidths, WithStyles {

                    public function __construct($collection)
                    {
                        $this->collection = $collection;
                    }

                    public function headings(): array
                    {
                        return [
                        'No', 'Tanggal Faktur', 'Tgl Jatuh Tempo', 'Jenis Pembelian', 'Jenis Pembayaran', 'Suplier', 'Apotek', 'No Faktur', 'Total', 'Total Format', 'Status', 'Tanggal Bayar', 'TTD'
                        ];
                    } 

                    public function columnWidths(): array
                    {
                        return [
                            'A' => 8,
                            'B' => 20,
                            'C' => 20,
                            'D' => 20,
                            'E' => 20,
                            'F' => 35,
                            'G' => 15,
                            'H' => 15,
                            'I' => 18,
                            'J' => 18,
                            'K' => 20,  
                            'L' => 20,           
                            'M' => 20, 
                        ];
                    }

                    public function styles(Worksheet $sheet)
                    {
                        return [
                            1    => ['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'A'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'B'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'C'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'F'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'H'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'J'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                        ];
                    }

                    public function collection()
                    {
                        return $this->collection;
                    }
        },"Rekap Data Pembelian.xlsx");
    }

    public function pembayaran_konsinyasi($id) 
    {
        $pembelian = TransaksiPembelian::find($id);

        $detail_pembelians = TransaksiPembelianDetail::
                                select('tb_detail_nota_pembelian.*')
                                ->where('tb_detail_nota_pembelian.id_nota', $id)
                                ->where('tb_detail_nota_pembelian.is_deleted', 0)
                                ->get();

        return view('pembayaran_faktur._form_pembayaran_konsinyasi')->with(compact('pembelian', 'detail_pembelians'));
    }

    public function set_pembayaran_kosinyasi($id)
    {
        $detail_pembelian = TransaksiPembelianDetail::
                                select('tb_detail_nota_pembelian.id',
                                    'tb_detail_nota_pembelian.id_nota',
                                    'tb_detail_nota_pembelian.id_obat',
                                    'tb_detail_nota_pembelian.jumlah',
                                    'tb_detail_nota_pembelian.harga_beli',
                                    'tb_detail_nota_pembelian.diskon',
                                    'tb_detail_nota_pembelian.is_retur',
                                    DB::raw("SUM(b.jumlah_bayar) as jumlah_bayar"))
                                ->leftJoin('tb_pembayaran_konsinyasi as b', 'b.id_detail_nota', '=', 'tb_detail_nota_pembelian.id')
                                ->where('tb_detail_nota_pembelian.id', $id)
                                ->first();
       // dd($detail_pembelian);

        $retur_pembelian_obat = ReturPembelian::where('is_deleted', 0)
                                            ->where('id_detail_nota', $detail_pembelian->id)
                                            ->first();
        $kartu_debets = MasterKartu::where('id_jenis_kartu', 1)->where('is_deleted', 0)->get();

        if(empty($retur_pembelian_obat)) {
            $retur_pembelian_obat = new ReturPembelian;
        }
        $alasan_returs = MasterAlasanReturPembelian::where('id', 2)->pluck('alasan', 'id');

        return view('pembayaran_faktur._form_set_pembayaran_kosinyasi')->with(compact('detail_pembelian', 'alasan_returs', 'retur_pembelian_obat', 'kartu_debets'));
    }

    public function add_pembayaran_konsinyasi(Request $request){
        $counter = $request->counter;
        $no = $counter+1;
        $detail_pembelian = new TransaksiPembelianDetail;
        $pembayaran_konsinyasi = new PembayaranKonsinyasi;
        $kartu_debets = MasterKartu::where('id_jenis_kartu', 1)->where('is_deleted', 0)->get();
        return view('pembayaran_faktur._form_add_pembayaran')->with(compact('no','detail_pembelian', 'pembayaran_konsinyasi', 'kartu_debets'));
    }

    public function update_pembayaran_konsinyasi(Request $request, $id)
    {
        //dd($request);
        //echo "kurangStok";exit();
       // if(Auth::user()->id == 1) {
            DB::beginTransaction(); 
            try{
                $detail_pembelian = TransaksiPembelianDetail::find($id);
                $detail_pembelian->fill($request->except('_token'));
                $status = 0;
                $apotek = MasterApotek::find(session('id_apotek_active'));
                $inisial = strtolower($apotek->nama_singkat);
                $array_id_pembayaran = array();
                if(empty($request->is_retur)) {
                    $request->is_retur == 0;
                    $detail_pembelian->jumlah_retur = 0;
                    $detail_pembelian->retur_at = null;
                    $detail_pembelian->retur_by = null;
                } else {
                    $detail_pembelian->jumlah_retur = $request->total_sisa_bayar;
                    $detail_pembelian->retur_at = date('Y-m-d H:i:s');
                    $detail_pembelian->retur_by = Auth::user()->id;
                }
                $detail_pembelian->is_retur = $request->is_retur;
                $detail_pembelian->save();

                $pembayaran_konsinyasis = $request->pembayaran_konsinyasi;
                if($request->is_retur == 1) {
                    $retur_pembelian_obat = ReturPembelian::where('is_deleted', 0)
                                                ->where('id_detail_nota', $detail_pembelian->id)
                                                ->first();

                    if(empty($retur_pembelian_obat)) {
                        $retur_pembelian_obat = new ReturPembelian;
                        $retur_pembelian_obat->id_detail_nota = $detail_pembelian->id;
                        $retur_pembelian_obat->jumlah = $request->total_sisa_bayar;
                        $retur_pembelian_obat->id_alasan_retur = $request->id_alasan_retur;
                        $retur_pembelian_obat->alasan_lain = $request->alasan_lain;
                        $retur_pembelian_obat->created_at = date('Y-m-d H:i:s');
                        $retur_pembelian_obat->created_by = Auth::user()->id;
                        $retur_pembelian_obat->save();
                    } else {
                        $retur_pembelian_obat->jumlah = $request->total_sisa_bayar;
                        $retur_pembelian_obat->id_alasan_retur = $request->id_alasan_retur;
                        $retur_pembelian_obat->alasan_lain = $request->alasan_lain;
                        $retur_pembelian_obat->updated_at = date('Y-m-d H:i:s');
                        $retur_pembelian_obat->updated_by = Auth::user()->id;
                        $retur_pembelian_obat->save();
                    }

                    $pembelian = TransaksiPembelian::find($detail_pembelian->id_nota);
                    $pembelian->is_lunas = 1;
                    $pembelian->lunas_at = date('Y-m-d H:i:s');
                    $pembelian->lunas_by = Auth::user()->id;
                    $pembelian->save();


                     // sesuaikan stok 
                    $stok_before = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $detail_pembelian->id_obat)->first();
                    $stok_now = $stok_before->stok_akhir-$retur_pembelian_obat->jumlah;
                
                    # update ke table stok harga
                    $stok_harga = MasterStokHarga::where('id_obat', $detail_pembelian->id_obat)->first();
                    $stok_harga->stok_awal = $stok_before->stok_akhir;
                    $stok_harga->stok_akhir = $stok_now;
                    $stok_harga->updated_at = date('Y-m-d H:i:s'); 
                    $stok_harga->updated_by = Auth::user()->id;
                    if($stok_harga->save()) {
                    } else {
                        DB::rollback();
                        session()->flash('error', 'Error!');
                        return redirect('pembelian');
                    }
            
                    # create histori
                    $histori_stok = HistoriStok::where('id_obat', $detail_pembelian->id_obat)->where('id_jenis_transaksi', 26)->where('id_transaksi', $detail_pembelian->id)->first();
                    if(empty($histori_stok)) {
                        $histori_stok = new HistoriStok;
                    }
                    $histori_stok->id_obat = $detail_pembelian->id_obat;
                    $histori_stok->jumlah = $retur_pembelian_obat->jumlah;
                    $histori_stok->stok_awal = $stok_before->stok_akhir;
                    $histori_stok->stok_akhir = $stok_now;
                    $histori_stok->id_jenis_transaksi = 26; //retur pembelian
                    $histori_stok->id_transaksi = $retur_pembelian_obat->id;
                    $histori_stok->batch = $detail_pembelian->id_batch;
                    $histori_stok->ed = $detail_pembelian->tgl_batch;
                    $histori_stok->sisa_stok = null;
                    $histori_stok->hb_ppn = $detail_pembelian->harga_beli_ppn;
                    $histori_stok->keterangan = 'Retur Pembelian pada ID.'.$retur_pembelian_obat->id.' sejumlah '.$retur_pembelian_obat->jumlah;
                    $histori_stok->created_at = date('Y-m-d H:i:s');
                    $histori_stok->created_by = Auth::user()->id;
                    if($histori_stok->save()) {
                    } else {
                        DB::rollback();
                        session()->flash('error', 'Error!');
                        return redirect('pembelian');
                    }

                    # update stok aktif 
                    $cekHistori = HistoriStok::where('id_jenis_transaksi', 2)->where('id_transaksi', $detail_pembelian->id)->first();
                    if(!is_null($cekHistori)) {
                        if($cekHistori->sisa_stok < $detail_pembelian->jumlah OR is_null($cekHistori)) {
                            $kurangStok = $this->kurangStokRetur($detail_pembelian->id, $retur_pembelian_obat->id, $detail_pembelian->id_obat, $retur_pembelian_obat->jumlah);
                            if($kurangStok['status'] == 0) {
                                DB::rollback();
                                session()->flash('error', 'Error!');
                                return redirect('pembelian');
                            } else {
                                $detail_pembelian->id_histori_stok = $kurangStok['array_id_histori_stok'];
                                $detail_pembelian->id_histori_stok_detail = $kurangStok['array_id_histori_stok_detail'];
                                if($detail_pembelian->save()) {
                                } else {
                                    DB::rollback();
                                    session()->flash('error', 'Error!');
                                    return redirect('pembelian');
                                }
                            }
                        } else {
                            $keterangan = $cekHistori->keterangan.', Retur Pembelian pada IDRetur.'.$retur_pembelian_obat->id.' IDdet.'.$detail_pembelian->id.' sejumlah '.$detail_pembelian->jumlah;
                            $cekHistori->sisa_stok = $cekHistori->sisa_stok - $detail_pembelian->jumlah;
                            $cekHistori->keterangan = $keterangan;
                            if($cekHistori->save()) {
                            } else {
                                DB::rollback();
                                session()->flash('error', 'Error!');
                                return redirect('pembelian');
                            }
                        }
                    } else {
                        $kurangStok = $this->kurangStokRetur($detail_pembelian->id, $retur_pembelian_obat->id, $detail_pembelian->id_obat, $retur_pembelian_obat->jumlah);
                        if($kurangStok['status'] == 0) {
                            DB::rollback();
                            session()->flash('error', 'Error!');
                            return redirect('pembelian');
                        } else {
                            $detail_pembelian->id_histori_stok = $kurangStok['array_id_histori_stok'];
                            $detail_pembelian->id_histori_stok_detail = $kurangStok['array_id_histori_stok_detail'];
                            if($detail_pembelian->save()) {
                            } else {
                                DB::rollback();
                                session()->flash('error', 'Error!');
                                return redirect('pembelian');
                            }
                        }
                    }
                } else {
                    $retur_pembelian_obat = ReturPembelian::where('is_deleted', 0)
                                                ->where('id_detail_nota', $detail_pembelian->id)
                                                ->first();

                    if(!empty($retur_pembelian_obat)) {
                        $retur_pembelian_obat->is_deleted = 1;
                        $retur_pembelian_obat->deleted_at = date('Y-m-d H:i:s');
                        $retur_pembelian_obat->deleted_by = Auth::user()->id;
                        $retur_pembelian_obat->save();
                   

                        // sesuaikan stok 
                        $stok_before = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $detail_pembelian->id_obat)->first();
                        $stok_now = $stok_before->stok_akhir+$retur_pembelian_obat->jumlah;
                    
                        # update ke table stok harga
                        $stok_harga = MasterStokHarga::where('id_obat', $detail_pembelian->id_obat)->first();
                        $stok_harga->stok_awal = $stok_before->stok_akhir;
                        $stok_harga->stok_akhir = $stok_now;
                        $stok_harga->updated_at = date('Y-m-d H:i:s'); 
                        $stok_harga->updated_by = Auth::user()->id;
                        if($stok_harga->save()) {
                        } else {
                            DB::rollback();
                            session()->flash('error', 'Error!');
                            return redirect('pembelian');
                        }
                
                        # create histori
                        $histori_stok = HistoriStok::where('id_obat', $detail_pembelian->id_obat)->where('id_jenis_transaksi', 26)->where('id_transaksi', $detail_pembelian->id)->first();
                        if(empty($histori_stok)) {
                            $histori_stok = new HistoriStok;
                        }
                        $histori_stok->id_obat = $detail_pembelian->id_obat;
                        $histori_stok->jumlah = $retur_pembelian_obat->jumlah;
                        $histori_stok->stok_awal = $stok_before->stok_akhir;
                        $histori_stok->stok_akhir = $stok_now;
                        $histori_stok->id_jenis_transaksi = 27; //batal retur pembelian
                        $histori_stok->id_transaksi = $retur_pembelian_obat->id;
                        $histori_stok->batch = $detail_pembelian->id_batch;
                        $histori_stok->ed = $detail_pembelian->tgl_batch;
                        $histori_stok->sisa_stok = null;
                        $histori_stok->hb_ppn = $detail_pembelian->harga_beli_ppn;
                        $histori_stok->keterangan = 'Batal Retur Pembelian pada ID.'.$retur_pembelian_obat->id.' sejumlah '.$retur_pembelian_obat->jumlah;
                        $histori_stok->created_at = date('Y-m-d H:i:s');
                        $histori_stok->created_by = Auth::user()->id;
                        if($histori_stok->save()) {
                        } else {
                            DB::rollback();
                            session()->flash('error', 'Error!');
                            return redirect('pembelian');
                        }
                     }
                }
        
                foreach ($pembayaran_konsinyasis as $pembayaran_konsinyasi) {
                    if($pembayaran_konsinyasi['id']>0){
                        $obj = PembayaranKonsinyasi::find($pembayaran_konsinyasi['id']);
                    }else{
                        $obj = new PembayaranKonsinyasi;
                    }

                    $obj->id_detail_nota = $id;
                    $obj->tgl_bayar = $pembayaran_konsinyasi['tgl_bayar'];
                    $obj->jumlah_bayar = $pembayaran_konsinyasi['jumlah_bayar'];
                    $obj->id_kartu_debet_credit = $pembayaran_konsinyasi['id_kartu_debet_credit'];
                    $obj->debet = $pembayaran_konsinyasi['debet'];
                    $obj->biaya_admin = $pembayaran_konsinyasi['biaya_admin'];
                    $obj->cash = $pembayaran_konsinyasi['cash'];
                    $obj->total_bayar = $pembayaran_konsinyasi['total_bayar'];
                    $obj->created_by = Auth::user()->id;
                    $obj->created_at = date('Y-m-d H:i:s');
                    $obj->save();
                    $array_id_pembayaran[] = $obj->id;
                    $status = 1;
                }

                if($request->total_sisa_bayar == 0) {
                    $pembelian = TransaksiPembelian::find($detail_pembelian->id_nota);
                    $pembelian->is_lunas = 1;
                    $pembelian->lunas_at = date('Y-m-d H:i:s');
                    $pembelian->lunas_by = Auth::user()->id;
                    $pembelian->save();
                }

                if(!empty($array_id_pembayaran)){
                    DB::statement("DELETE FROM tb_pembayaran_konsinyasi
                                    WHERE id_detail_nota=".$id." AND 
                                            id NOT IN(".implode(',', $array_id_pembayaran).")");
                }else{
                    DB::statement("DELETE FROM tb_pembayaran_konsinyasi 
                                    WHERE id_detail_nota=".$id);
                }

                if($status == 1){
                    DB::commit();
                    session()->flash('success', 'Sukses menyimpan data!');
                    return redirect('pembelian/pembayaran_konsinyasi/'.$detail_pembelian->id_nota);
                }else{
                    session()->flash('error', 'Gagal menyimpan data!');
                    return redirect('pembelian/pembayaran_faktur/'.$detail_pembelian->id_nota);
                }
            }catch(\Exception $e){
                DB::rollback();
                session()->flash('error', 'Error!');
                return redirect('pembelian');
            }
       /* } else {
            session()->flash('info', 'Undermaintenance!');
            return redirect('pembelian');
        }*/
    }

    public function obat_kadaluarsa() {
        return view('pembelian.obat_kadaluarsa');
    }

    public function list_obat_kadaluarsa(Request $request) {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);

        DB::statement(DB::raw('set @rownum = 0'));
        $data = TransaksiPembelianDetail::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_detail_nota_pembelian.*', 'a.nama', 'b.no_faktur', 'c.stok_akhir'])
        ->join('tb_m_obat as a', 'a.id', 'tb_detail_nota_pembelian.id_obat')
        ->join('tb_nota_pembelian as b', 'b.id', 'tb_detail_nota_pembelian.id_nota')
        ->join('tb_m_stok_harga_'.$inisial.' as c', 'c.id_obat', 'tb_detail_nota_pembelian.id_obat')
        ->where(function($query) use($request){
            $query->where('tb_detail_nota_pembelian.is_deleted','=','0');
            $query->where('b.id_apotek_nota','=',session('id_apotek_active'));
            $query->where('tb_detail_nota_pembelian.id_batch','LIKE',($request->batch > 0 ? $request->batch : '%'.$request->batch.'%'));
            $query->where('b.no_faktur','LIKE',($request->no_faktur > 0 ? $request->no_faktur : '%'.$request->no_faktur.'%'));
            
            if (!empty($request->tgl_awal) && !empty($request->tgl_akhir)) {
                $query->where('tb_detail_nota_pembelian.tgl_batch','>=', $request->tgl_awal);
                $query->where('tb_detail_nota_pembelian.tgl_batch','<=', $request->tgl_akhir);
            } else {
                $now = date('Y-m-d');
                $query->where('tb_detail_nota_pembelian.tgl_batch','>=', $now);
                $query->where('tb_detail_nota_pembelian.tgl_batch','<=', $now);
            }

            $query->where('c.stok_akhir','>',0);
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
            return Carbon::parse($data->created_at)->format('d/m/Y H:i:s');
        })
        ->editcolumn('created_by', function($data) use($request){
            return $data->created_oleh->nama;
        })
        ->editcolumn('id_obat', function($data) {
            $info = '<small>No Faktur : '.$data->no_faktur.'</small>';
            return $data->nama.'<br>'.$info;
        })
        ->editcolumn('stok', function($data) {
            return $data->stok_akhir;
        })
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .= '<a href="'.url('/pembelian/konfirmasi_ed/'.$data->id).'" title="Konfirmasi ED" class="btn btn-info btn-sm"><span data-toggle="tooltip" data-placement="top" title="Konfirmasi ED"><i class="fa fa-cogs"></i> Konfirmasi ED</span></a>';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['action', 'id_obat'])
        ->addIndexColumn()
        ->make(true);  
    }

    public function konfirmasi_ed($id) {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $detail_pembelian = TransaksiPembelianDetail::find($id);
        $pembelian = $detail_pembelian->nota;
        $konfirmasi_ed = KonfirmasiED::where('id_detail_nota', $detail_pembelian->id)->first();
        if(empty($konfirmasi_ed)) {
            $konfirmasi_ed = new KonfirmasiED;
        }

        $stok_before = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $detail_pembelian->id_obat)->first();

        $retur_pembelian_obat = ReturPembelian::where('is_deleted', 0)
                                            ->where('id_detail_nota', $detail_pembelian->id)
                                            ->first();
        if(empty($retur_pembelian_obat)) {
            $retur_pembelian_obat = new ReturPembelian;
        }

        $jenis_penanganans = MasterJenisPenanganan::where('is_deleted', 0)->pluck('nama', 'id');
        $jenis_penanganans->prepend('-- Pilih Jenis Penanganan --','');

        $alasan_returs = MasterAlasanReturPembelian::where('is_deleted',0)->pluck('alasan', 'id');
        $alasan_returs->prepend('-- Pilih Alasan Retur --','');

        return view('konfirmasi_ed._form')->with(compact('apotek', 'detail_pembelian', 'pembelian', 'konfirmasi_ed', 'stok_before', 'jenis_penanganans', 'alasan_returs', 'retur_pembelian_obat'));
    } 

    public function update_konfirmasi_ed(Request $request, $id) {
        /*DB::beginTransaction(); 
        try{*/
            $detail_pembelian = TransaksiPembelianDetail::find($id);
            $detail_pembelian->fill($request->except('_token'));
            $status = 0;
            $apotek = MasterApotek::find(session('id_apotek_active'));
            $inisial = strtolower($apotek->nama_singkat);
            $array_id_pembayaran = array();
            if($request->id_jenis_penanganan == 1) {
                $detail_pembelian->is_retur = 1;
                $detail_pembelian->jumlah_retur = $request->jumlah_ed;
                $detail_pembelian->retur_at = date('Y-m-d H:i:s');
                $detail_pembelian->retur_by = Auth::user()->id;
                $detail_pembelian->save();

                $retur_pembelian_obat = ReturPembelian::where('is_deleted', 0)
                                            ->where('id_detail_nota', $detail_pembelian->id)
                                            ->where('id_alasan_retur', 4)
                                            ->first();

                if(empty($retur_pembelian_obat)) {
                    $retur_pembelian_obat = new ReturPembelian;
                    $retur_pembelian_obat->id_detail_nota = $detail_pembelian->id;
                    $retur_pembelian_obat->jumlah = $request->jumlah_ed;
                    $retur_pembelian_obat->id_alasan_retur = 4;
                    $retur_pembelian_obat->alasan_lain = $request->alasan_lain;
                    $retur_pembelian_obat->created_at = date('Y-m-d H:i:s');
                    $retur_pembelian_obat->created_by = Auth::user()->id;
                    $retur_pembelian_obat->save();

                    // update stok 
                    $stok_before = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $detail_pembelian->id_obat)->first();
                    $stok_now = $stok_before->stok_akhir-$retur_pembelian_obat->jumlah;

                    # update ke table stok harga
                    DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $detail_pembelian->id_obat)->update(['stok_awal'=> $stok_before->stok_akhir, 'stok_akhir'=> $stok_now, 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => Auth::user()->id]);

                    # create histori
                    DB::table('tb_histori_stok_'.$inisial)->insert([
                        'id_obat' => $detail_pembelian->id_obat,
                        'jumlah' => $retur_pembelian_obat->jumlah,
                        'stok_awal' => $stok_before->stok_akhir,
                        'stok_akhir' => $stok_now,
                        'id_jenis_transaksi' => 26, //retur pembelian
                        'id_transaksi' => $retur_pembelian_obat->id,
                        'batch' => $detail_pembelian->id_batch,
                        'ed' => $detail_pembelian->tgl_batch,
                        'created_at' => date('Y-m-d H:i:s'),
                        'created_by' => Auth::user()->id
                    ]);

                    $status = 1;
                } else {
                    $retur_pembelian_obat->jumlah = $request->jumlah_ed;
                    $retur_pembelian_obat->id_alasan_retur = 4;
                    $retur_pembelian_obat->alasan_lain = $request->alasan_lain;
                    $retur_pembelian_obat->updated_at = date('Y-m-d H:i:s');
                    $retur_pembelian_obat->updated_by = Auth::user()->id;
                    $retur_pembelian_obat->save();

                    $status = 1;
                }
            } else {
                $retur_pembelian_obat = ReturPembelian::where('is_deleted', 0)
                                            ->where('id_detail_nota', $detail_pembelian->id)
                                            ->where('id_alasan_retur', 4)
                                            ->first();

                if(!empty($retur_pembelian_obat)) {
                    $retur_pembelian_obat->is_deleted = 1;
                    $retur_pembelian_obat->deleted_at = date('Y-m-d H:i:s');
                    $retur_pembelian_obat->deleted_by = Auth::user()->id;
                    $retur_pembelian_obat->save();

                    // update stok 
                    $stok_before = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $detail_pembelian->id_obat)->first();
                    $stok_now = $stok_before->stok_akhir+$retur_pembelian_obat->jumlah;

                    # update ke table stok harga
                    DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $detail_pembelian->id_obat)->update(['stok_awal'=> $stok_before->stok_akhir, 'stok_akhir'=> $stok_now, 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => Auth::user()->id]);

                    # create histori
                    DB::table('tb_histori_stok_'.$inisial)->insert([
                        'id_obat' => $detail_pembelian->id_obat,
                        'jumlah' => $retur_pembelian_obat->jumlah,
                        'stok_awal' => $stok_before->stok_akhir,
                        'stok_akhir' => $stok_now,
                        'id_jenis_transaksi' => 27, //retur pembelian
                        'id_transaksi' => $retur_pembelian_obat->id,
                        'batch' => $detail_pembelian->id_batch,
                        'ed' => $detail_pembelian->tgl_batch,
                        'created_at' => date('Y-m-d H:i:s'),
                        'created_by' => Auth::user()->id
                    ]);

                    $status = 1;
                }
            }

          /*  print_r($status);
            exit();*/

            if($status == 1){
                //DB::commit();
                session()->flash('success', 'Sukses menyimpan data!');
                return redirect('pembelian/konfirmasi_ed/'.$detail_pembelian->id_nota);
            }else{
                session()->flash('error', 'Gagal menyimpan data!');
                return redirect('pembelian/konfirmasi_ed/'.$detail_pembelian->id_nota);
            }
        /*}catch(\Exception $e){
            DB::rollback();
            session()->flash('error', 'Error!');
            return redirect('pembelian');
        }*/
    }

    public function export_ed(Request $request) 
    {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);

        $rekaps = TransaksiPembelianDetail::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_detail_nota_pembelian.*', 'a.nama', 'b.tgl_faktur', 'b.no_faktur', 'c.stok_akhir'])
                    ->join('tb_m_obat as a', 'a.id', 'tb_detail_nota_pembelian.id_obat')
                    ->join('tb_nota_pembelian as b', 'b.id', 'tb_detail_nota_pembelian.id_nota')
                    ->join('tb_m_stok_harga_'.$inisial.' as c', 'c.id_obat', 'tb_detail_nota_pembelian.id_obat')
                    ->where(function($query) use($request){
                        $query->where('tb_detail_nota_pembelian.is_deleted','=','0');
                        $query->where('b.id_apotek_nota','=',session('id_apotek_active'));
                        $query->where('tb_detail_nota_pembelian.id_batch','LIKE',($request->batch > 0 ? $request->batch : '%'.$request->batch.'%'));
                        $query->where('b.no_faktur','LIKE',($request->no_faktur > 0 ? $request->no_faktur : '%'.$request->no_faktur.'%'));
                        if (!empty($request->tgl_awal) && !empty($request->tgl_akhir)) {
                            $query->where('tb_detail_nota_pembelian.tgl_batch','>=', $request->tgl_awal);
                            $query->where('tb_detail_nota_pembelian.tgl_batch','<=', $request->tgl_akhir);
                        } else {
                            $now = date('Y-m-d');
                            $query->where('tb_detail_nota_pembelian.tgl_batch','>=', $now);
                            $query->where('tb_detail_nota_pembelian.tgl_batch','<=', $now);
                        }
                        $query->where('c.stok_akhir','>',0);
                    })
                    ->orderBy('b.id', 'DESC')
                    ->get();

                $collection = collect();
                $no = 0;
                $total_excel=0;
                foreach($rekaps as $rekap) {
                    $no++;
                    $status = '';

                    $collection[] = array(
                        $no,
                        $rekap->id_nota,
                        $rekap->tgl_faktur,
                        $rekap->no_faktur,
                        $rekap->nama,
                        $rekap->tgl_batch,
                        $rekap->id_batch,
                        $rekap->stok_akhir,
                        $status
                    );
                }


        return Excel::download(new class($collection) implements FromCollection, WithHeadings, WithColumnWidths, WithStyles {

                    public function __construct($collection)
                    {
                        $this->collection = $collection;
                    }

                    public function headings(): array
                    {
                        return [
                        'No', 'ID Nota', 'Tanggal Faktur', 'No Faktur', 'Nama Obat', 'ED', 'Batch', 'Stok', 'Status'
                        ];
                    } 

                    public function columnWidths(): array
                    {
                        return [
                            'A' => 8,
                            'B' => 15,
                            'C' => 20,
                            'D' => 20,
                            'E' => 40,
                            'F' => 15,
                            'G' => 15,
                            'H' => 15,  
                            'I' => 18,           
                        ];
                    }

                    public function styles(Worksheet $sheet)
                    {
                        return [
                            1    => ['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'A'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'B'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'C'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'D'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'F'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'G'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                        ];
                    }

                    public function collection()
                    {
                        return $this->collection;
                    }
        },"Rekap Data Obat ED.xlsx");
    }

    public function reload_hb_ppn($id)
    {
        $pembelian = TransaksiPembelian::find($id);
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $detail_pembelians = TransaksiPembelianDetail::where('is_deleted', 0)->where('id_nota', $pembelian->id)->get();    
        $i = 0;
        foreach($detail_pembelians as $key => $obj) {
            $cek = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $obj->id_obat)->first();
            $harga_before = DB::table('tb_histori_harga_'.$inisial)->where('id_obat', $obj->id_obat)->first();
            $harga_ppn_now = ($pembelian->ppn/100 * $obj->harga_beli) + $obj->harga_beli;
            if($harga_ppn_now != $cek->harga_beli_ppn) {
                # update ke table stok harga
                DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $obj->id_obat)->update(['harga_beli_ppn'=> $harga_ppn_now, 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => Auth::user()->id]);
                $i++;
            }
        }

        echo json_encode($i);            
    }

    public function hapus_detail($id) {
        DB::beginTransaction(); 
        try{
            $detail_pembelian = TransaksiPembelianDetail::find($id);
            $detail_pembelian->is_deleted = 1;
            $detail_pembelian->deleted_at= date('Y-m-d H:i:s');
            $detail_pembelian->deleted_by = Auth::user()->id;

            $pembelian = TransaksiPembelian::find($detail_pembelian->id_nota);
            $apotek = MasterApotek::find(session('id_apotek_active'));
            $inisial = strtolower($apotek->nama_singkat);

            $stok_before = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $detail_pembelian->id_obat)->first();
            $jumlah = $detail_pembelian->jumlah;
            $stok_now = $stok_before->stok_akhir-$jumlah;

            $total = $detail_pembelian->harga_beli*$detail_pembelian->jumlah;
            $diskon = $detail_pembelian->diskon+(($detail_pembelian->diskon_persen/100)*$total);
            $total_final = $total-$diskon;
            $detail_pembelian->total_harga = $total_final;


            # update ke table stok harga
            DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $detail_pembelian->id_obat)->update(['stok_awal'=> $stok_before->stok_akhir, 'stok_akhir'=> $stok_now, 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => Auth::user()->id]);

            # create histori
            DB::table('tb_histori_stok_'.$inisial)->insert([
                'id_obat' => $detail_pembelian->id_obat,
                'jumlah' => $jumlah,
                'stok_awal' => $stok_before->stok_akhir,
                'stok_akhir' => $stok_now,
                'id_jenis_transaksi' => 14, //hapus pembelian
                'id_transaksi' => $detail_pembelian->id,
                'batch' => $detail_pembelian->id_batch,
                'ed' => $detail_pembelian->tgl_batch,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => Auth::user()->id
            ]);  

            $total = TransaksiPembelianDetail::select([
                                DB::raw('SUM(total_harga) as total_all')
                                ])
                                ->where('id', '!=', $detail_pembelian->id)
                                ->where('id_nota', $detail_pembelian->id_nota)
                                ->where('is_deleted', 0)
                                ->first();
            $y = 0;
            if($total->total_all == 0 OR $total->total_all == '') {
                $y = 0;
            } else {
                $y = $total->total_all;
            }

            if($y == 0) {
                $pembelian->is_deleted = 1;
                $pembelian->deleted_at= date('Y-m-d H:i:s');
                $pembelian->deleted_by = Auth::user()->id;
            }

            if($detail_pembelian->save()){
                $pembelian->save();
                DB::commit();
                echo 1;
            }else{
                echo 0;
            }
        }catch(\Exception $e){
            DB::rollback();
            session()->flash('error', 'Error!');
            return redirect('pembelian/'.$pembelian->id.'/edit');
        }
    }

    public function change_obat(Request $request) {
        $detail_pembelian = TransaksiPembelianDetail::find($request->id_detail_pembelian);
        $obats      = MasterObat::where('is_deleted', 0)->pluck('nama', 'id');
        $no = $request->no;

        return view('pembelian._change_obat')->with(compact('detail_pembelian', 'obats', 'no'));
    }


    public function update_obat(Request $request, $id) {
        DB::beginTransaction(); 
        try{
            $detail_pembelian = TransaksiPembelianDetail::find($id);
            $pembelian = TransaksiPembelian::find($detail_pembelian->id_nota);
            $apotek = MasterApotek::find($pembelian->id_apotek_nota);
            $inisial = strtolower($apotek->nama_singkat);

            if($request->id_obat_awal != $request->id_obat_akhir) {
                // create histori stok dengan id_obat_awal
                $stok_before_awal = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $request->id_obat_awal)->first();
                $jumlah = $detail_pembelian->jumlah;
                $stok_now_awal = $stok_before_awal->stok_akhir-$jumlah;

                # update ke table stok harga
                DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $request->id_obat_awal)->update(['stok_awal'=> $stok_before_awal->stok_akhir, 'stok_akhir'=> $stok_now_awal, 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => Auth::user()->id]);

                # create histori
                DB::table('tb_histori_stok_'.$inisial)->insert([
                    'id_obat' => $request->id_obat_awal,
                    'jumlah' => $jumlah,
                    'stok_awal' => $stok_before_awal->stok_akhir,
                    'stok_akhir' => $stok_now_awal,
                    'id_jenis_transaksi' => 30, //hapus pembelian -> ganti obat
                    'id_transaksi' => $detail_pembelian->id,
                    'batch' => $detail_pembelian->id_batch,
                    'ed' => $detail_pembelian->tgl_batch,
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => Auth::user()->id
                ]);  

                // create histori stok dengan id_obat_akhir
                $stok_before_akhir = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $request->id_obat_akhir)->first();
                $stok_now_akhir = $stok_before_akhir->stok_akhir+$jumlah;

                # update ke table stok harga
                DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $request->id_obat_akhir)->update(['stok_awal'=> $stok_before_akhir->stok_akhir, 'stok_akhir'=> $stok_now_akhir, 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => Auth::user()->id]);

                # create histori
                DB::table('tb_histori_stok_'.$inisial)->insert([
                    'id_obat' => $request->id_obat_akhir,
                    'jumlah' => $jumlah,
                    'stok_awal' => $stok_before_akhir->stok_akhir,
                    'stok_akhir' => $stok_now_akhir,
                    'id_jenis_transaksi' => 31, //penambahan item pembelian -> ganti obat
                    'id_transaksi' => $detail_pembelian->id,
                    'batch' => $detail_pembelian->id_batch,
                    'ed' => $detail_pembelian->tgl_batch,
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => Auth::user()->id
                ]); 

                $detail_pembelian->id_obat = $request->id_obat_akhir;
                $detail_pembelian->updated_at= date('Y-m-d H:i:s');
                $detail_pembelian->updated_by = Auth::user()->id;

                if($detail_pembelian->save()){
                    DB::commit();
                    echo 1;
                }else{
                    echo 0;
                }
            } else {
                echo 0;
            }   
        }catch(\Exception $e){
            DB::rollback();
            session()->flash('error', 'Error!');
            return redirect('pembelian/'.$id.'/edit');
        }
    }

    public function cari_info(Request $request) {
        $datas = TransaksiPembelian::select([
                DB::raw('@rownum  := @rownum  + 1 AS no'),
                'tb_nota_pembelian.*'])
                ->where(function($query) use($request){
                    $query->where('tb_nota_pembelian.is_deleted','=','0');
                    $query->where('tb_nota_pembelian.is_tanda_terima','=','1');
                    $query->where('tb_nota_pembelian.id_apotek','LIKE',($request->id_apotek > 0 ? $request->id_apotek : '%'.$request->id_apotek.'%'));
                    $query->where('tb_nota_pembelian.id_suplier','LIKE',($request->id_suplier > 0 ? $request->id_suplier : '%'.$request->id_suplier.'%'));
                    if (!empty($request->tgl_awal) && !empty($request->tgl_akhir)) {
                        $query->where('tb_nota_pembelian.tgl_jatuh_tempo','>=', $request->tgl_awal);
                        $query->where('tb_nota_pembelian.tgl_jatuh_tempo','<=', $request->tgl_akhir);
                    }
                })
                ->orderBy('tgl_jatuh_tempo','asc')
                ->orderBy('id_suplier')
                ->groupBy('tb_nota_pembelian.id')
                ->get();

        $no = 0;
        $total_all = 0;
        $total_lunas = 0;
        $total_belum_lunas = 0;
        foreach($datas as $rekap) {
            $no++;
            $x = $rekap->detail_pembelian_total[0];
            $total1 = $x->jumlah - ($rekap->diskon1 + $rekap->diskon2);
            $total2 = $total1 + ($total1 * $rekap->ppn/100);
            $total_all = $total_all + $total2;
            
            if($rekap->is_lunas == 1) {
                $total_lunas = $total_lunas + $total2;
            } else {
                $total_belum_lunas = $total_belum_lunas + $total2;
            }
        }

        $total_all  = 'Rp '.number_format($total_all,2);
        $total_lunas  = 'Rp '.number_format($total_lunas,2);
        $total_belum_lunas  = 'Rp '.number_format($total_belum_lunas,2);

        $arr_ = array('total_all' => $total_all, 'total_lunas' => $total_lunas, 'total_belum_lunas' => $total_belum_lunas);
        return response()->json($arr_); 
    }

    public function cari_info2(Request $request) {
        $datas = TransaksiPembelian::select([
                DB::raw('@rownum  := @rownum  + 1 AS no'),
                'tb_nota_pembelian.*'])
                ->where(function($query) use($request){
                    $query->where('tb_nota_pembelian.is_deleted','=','0');
                    $query->where('tb_nota_pembelian.is_tanda_terima','=','1');
                    $query->where('tb_nota_pembelian.id_apotek_nota', session('id_apotek_active'));
                    $query->where('tb_nota_pembelian.id_suplier','LIKE',($request->id_suplier > 0 ? $request->id_suplier : '%'.$request->id_suplier.'%'));
                    if (!empty($request->tgl_awal) && !empty($request->tgl_akhir)) {
                        $query->where('tb_nota_pembelian.tgl_jatuh_tempo','>=', $request->tgl_awal);
                        $query->where('tb_nota_pembelian.tgl_jatuh_tempo','<=', $request->tgl_akhir);
                    } else {
                        $query->where('tb_nota_pembelian.tgl_jatuh_tempo','>=', date('Y-m-d'));
                        $query->where('tb_nota_pembelian.tgl_jatuh_tempo','<=', date('Y-m-d'));
                    }

                    if($request->id_jenis_pembelian > 0) {
                        $query->where('tb_nota_pembelian.id_jenis_pembelian',$request->id_jenis_pembelian);
                    }

                    if($request->tgl_awal_faktur != "") {
                        $tgl_awal_faktur       = date('Y-m-d H:i:s',strtotime($request->tgl_awal_faktur));
                        $query->whereDate('tb_nota_pembelian.tgl_faktur','>=', $tgl_awal_faktur);
                    }

                    if($request->tgl_akhir_faktur != "") {
                        $tgl_akhir_faktur      = date('Y-m-d H:i:s',strtotime($request->tgl_akhir_faktur));
                        $query->whereDate('tb_nota_pembelian.tgl_faktur','<=', $tgl_akhir_faktur);
                    }
                })
                ->orderBy('tgl_jatuh_tempo','asc')
                ->orderBy('id_suplier')
                ->groupBy('tb_nota_pembelian.id')
                ->get();

        $no = 0;
        $total_all = 0;
        $total_lunas = 0;
        $total_belum_lunas = 0;
        foreach($datas as $rekap) {
            $no++;
            $x = $rekap->detail_pembelian_total[0];
            $total1 = $x->jumlah - ($rekap->diskon1 + $rekap->diskon2);
            $total2 = $total1 + ($total1 * $rekap->ppn/100);
            $total_all = $total_all + $total2;
            
            if($rekap->is_lunas == 1) {
                $total_lunas = $total_lunas + $total2;
            } else {
                $total_belum_lunas = $total_belum_lunas + $total2;
            }
        }

        $total_all  = 'Rp '.number_format($total_all,2);
        $total_lunas  = 'Rp '.number_format($total_lunas,2);
        $total_belum_lunas  = 'Rp '.number_format($total_belum_lunas,2);

        $arr_ = array('total_all' => $total_all, 'total_lunas' => $total_lunas, 'total_belum_lunas' => $total_belum_lunas);
        return response()->json($arr_); 
    }

    public function cekHarga(Request $request) {
        if($request->harga_beli >= $request->harga_beli_sebelumnya_plus) {
            return 1;
        } else if($request->harga_beli <= $request->harga_beli_sebelumnya_minus) {
            return 1;
        } else {
            return 1;
        }
    }

    public function list_detail_pembelian(Request $request) {
        # get total to
        $id = $request->id;
        if(is_null($id)) {
            $total_pembelian = 0;
            $total_diskon = 0;
            $diskon2 = 0;
            $ppn = 0;
            $total2 = 0;
            $total_pembelian_bayar = 0;
            $is_sign = 0;
        } else {
            $pembelian = TransaksiPembelian::find($id);

            $total_pembelian = $pembelian->detail_pembelian_total[0]->jumlah;
            $total_diskon = $pembelian->detail_pembelian_total[0]->total_diskon + $pembelian->detail_pembelian_total[0]->total_diskon_persen;
            $diskon2 = $pembelian->diskon2;
            $ppn = $pembelian->ppn;
            if($total_pembelian == "" || $total_pembelian == null) {
                $total_pembelian = 0;
            }

            if($total_diskon == "" || $total_diskon == null) {
                $total_diskon = 0;
            }

            if($diskon2 == "" || $diskon2 == null) {
                $diskon2 = 0;
            }

            if($ppn == "" || $ppn == null) {
                $ppn = 0;
            }
            $total2 = $total_pembelian-$total_diskon;
            $total_pembelian_bayar = ($total2 + ($ppn/100 * $total2)) - $diskon2;
            $is_sign = $pembelian->is_sign;
        }

        if(Auth::user()->is_admin == 1) {
            $is_sign = 1;
        }

        $last_so = SettingStokOpnam::where('id_apotek', session('id_apotek_active'))->where('step', '>', 1)->orderBy('id', 'DESC')->first();

        DB::statement(DB::raw('set @rownum = 0'));
        $data = TransaksiPembelianDetail::select([
                DB::raw('@rownum  := @rownum  + 1 AS no'),
                'tb_detail_nota_pembelian.*', 
        ])
        ->where(function($query) use($request){
            $query->where('tb_detail_nota_pembelian.is_deleted','=','0');
            if(is_null($request->id)) {
                $query->where('tb_detail_nota_pembelian.id_nota','=',0);
            } else {
                $query->where('tb_detail_nota_pembelian.id_nota','=',$request->id);
            }
            
        })
        ->orderBy('tb_detail_nota_pembelian.id', 'ASC');
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request){
            $query->where(function($query) use($request){
                $query->orwhere('tb_detail_nota_pembelian.id','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->editcolumn('action', function($data) use($request, $is_sign, $last_so){
            $btn ='';

            if($is_sign == 1) {
                # jika sudah di ttd maka tidak muncul
                if(Auth::user()->is_admin == 1) {
                    $btn .= '<span class="btn btn-danger btn-xs" onClick="delete_item('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-trash"></i></span>';
                }
            } else {
                if(!empty($last_so)) {
                    $xxx = $last_so->tgl_so.' 01:00:00';
                    if($data->created_at >= $xxx) {
                        if($data->nota->is_lunas != 1) {
                            $btn .= '<span class="btn btn-danger btn-xs" onClick="delete_item('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-trash"></i></span>';
                        } else {
                            if($data->nota->id_jenis_pembelian == 1) {
                                # jika nota pembayaran cash
                                $btn .= '<span class="btn btn-danger btn-xs" onClick="delete_item('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-trash"></i></span>';
                            }
                        }
                    } else if(Auth::user()->is_admin == 1) {
                        if($data->nota->is_lunas != 1) {
                            $btn .= '<span class="btn btn-danger btn-xs" onClick="delete_item('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-trash"></i></span>';
                        } else {
                            if($data->nota->id_jenis_pembelian == 1) {
                                # jika nota pembayaran cash
                                $btn .= '<span class="btn btn-danger btn-xs" onClick="delete_item('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-trash"></i></span>';
                            }
                        }
                    }
                } else {
                    if($data->nota->is_lunas != 1) {
                        $btn .= '<span class="btn btn-danger btn-xs" onClick="delete_item('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-trash"></i></span>';
                    } else {
                        if($data->nota->id_jenis_pembelian == 1) {
                            # jika nota pembayaran cash
                            $btn .= '<span class="btn btn-danger btn-xs" onClick="delete_item('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-trash"></i></span>';
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
        ->editcolumn('harga_beli', function($data) use($request){
            return "Rp ".number_format($data->harga_beli,0);
        }) 
        ->editcolumn('harga_beli_ppn', function($data) use($request){
            return "Rp ".number_format($data->harga_beli_ppn,0);
        })   
        ->editcolumn('diskon', function($data) use($request){
            return "Rp ".number_format($data->diskon,0);
        })  
        ->editcolumn('diskon_persen', function($data) use($request){
            return $data->diskon_persen.'%';
        })  
        ->editcolumn('margin', function($data) use($request){
            $margin = 0;
            if($data->harga_beli > 0) {
                $margin = ($data->stok_harga->harga_jual/$data->harga_beli_ppn)*100;
                $margin = number_format($margin,2);
            }
            return $margin.'%';
        })  
        ->editcolumn('total', function($data) use($request){
            $pembelian = $data->nota;
            $total_pembelian = $pembelian->detail_pembelian_total[0]->total;

            return "Rp ".number_format($total_pembelian,0);
        })
        ->editcolumn('total1', function($data) use($request){
            return "Rp ".number_format($data->total_harga,0);
        })  
        ->editcolumn('total2', function($data) use($request){
            $total = $data->total_harga;
            $diskon_persen = ($data->diskon_persen/100) * $total;
            $total = $total-($data->diskon+$diskon_persen);

            return "Rp ".number_format($total,0);
        })
        ->addcolumn('action', function($data){
            $btn = '<div class="btn-group">';
            $btn .='</div>';
            return $btn; 
        }) 
        ->with([
            "total_pembelian" => $total_pembelian,
            "total_pembelian_format" => "Rp ".number_format($total_pembelian,0),
            "total_diskon" => $total_diskon,
            "total_diskon_format" => "Rp ".number_format($total_diskon,0),
            "diskon2" => $diskon2,
            "diskon2_format" => "Rp ".number_format($diskon2,0),
            "ppn" => $ppn,
            "ppn_format" => "Rp ".number_format($ppn,0),
            "total2" => $total2,
            "total2_format" => "Rp ".number_format($total2,0),
            "total_pembelian_bayar" => $total_pembelian_bayar,
            "total_pembelian_bayar_format" => "Rp ".number_format($total_pembelian_bayar,0)
        ])   
        ->rawColumns(['action', 'nama_barang', 'harga_beli', 'total', 'total1', 'total2', 'diskon'])
        ->addIndexColumn()
        ->make(true);  
    }

    public function AddItem(Request $request) {
        DB::beginTransaction(); 
        try{
            $pembelian = new TransaksiPembelian;
            $pembelian->fill($request->except('_token'));
            if($pembelian->id_jenis_pembayaran == 2) {
                $pembelian->is_tanda_terima = 1;
                $pembelian->is_lunas = 1;
            }  

            if(is_null($request->diskon1)) {
                $pembelian->diskon1 = 0;
            }

            if(is_null($request->diskon2)) {
                $pembelian->diskon2 = 0;
            }

            if(is_null($request->ppn)) {
                $pembelian->ppn = 0;
            }

            $detail_pembelians = array();
            $detail_pembelians[] = array(
                'id' => null,
                'id_obat' => $request->id_obat, 
                'total_harga' => $request->total_harga,
                'jumlah' => $request->jumlah,
                'harga_beli' => $request->harga_beli,
                'diskon' => $request->diskon,
                'diskon_persen' => $request->diskon_persen,
                'id_batch' => $request->id_batch,
                'tgl_batch' => $request->tgl_batch,
                'id_detail_order' => $request->id_detail_order
            );
            //print_r($detail_pembelians);exit();

            $validator = $pembelian->validate();
            if($validator->fails()){
                DB::rollback();
                echo json_encode(array('status' => 0, 'message' => 'Silakan lengkapi data yang wajib diisikan'));
            } else {
                $tanggal = date('Y-m-d');

                $apotek = MasterApotek::find(session('id_apotek_active'));
                $inisial = strtolower($apotek->nama_singkat);

                $result = $pembelian->save_from_array($detail_pembelians, 1);
                if($result['status']) {
                    DB::commit();
                    echo json_encode(array('status' => 1, 'id' => $pembelian->id, 'message' => $result['message']));
                } else {
                    DB::rollback();
                    echo json_encode(array('status' => 0, 'message' => 'Error, silakan cek kembali data yang diinputkan'));
                }
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
            $pembelian = TransaksiPembelian::find($id);
            //dd($pembelian);
            if($pembelian->is_deleted != 1) {  
                $pembelian->fill($request->except('_token'));
                if($pembelian->id_jenis_pembayaran == 2) {
                    $pembelian->is_tanda_terima = 1;
                    $pembelian->is_lunas = 1;
                }  

                if(is_null($request->diskon1)) {
                    $pembelian->diskon1 = 0;
                }

                if(is_null($request->diskon2)) {
                    $pembelian->diskon2 = 0;
                }

                if(is_null($request->ppn)) {
                    $pembelian->ppn = 0;
                }

                $detail_pembelians = array();
                $detail_pembelians[] = array(
                    'id' => null,
                    'id_obat' => $request->id_obat, 
                    'total_harga' => $request->total_harga,
                    'jumlah' => $request->jumlah,
                    'harga_beli' => $request->harga_beli,
                    'diskon' => $request->diskon,
                    'diskon_persen' => $request->diskon_persen,
                    'id_batch' => $request->id_batch,
                    'tgl_batch' => $request->tgl_batch,
                    'id_detail_order' => $request->id_detail_order
                );

                //print_r($detail_pembelians);exit();

                $tanggal = date('Y-m-d');

                $apotek = MasterApotek::find(session('id_apotek_active'));
                $inisial = strtolower($apotek->nama_singkat);
                
                $result = $pembelian->save_from_array($detail_pembelians, 2);
                if($result['status']) {
                    DB::commit();
                    echo json_encode(array('status' => 1, 'id' => $pembelian->id, 'message' => $result['message']));
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
            $detail_pembelian = TransaksiPembelianDetail::find($id);
            $detail_pembelian->is_deleted = 1;
            $detail_pembelian->deleted_at = date('Y-m-d H:i:s');
            $detail_pembelian->deleted_by = Auth::user()->id;
           
            # crete histori stok barang
            $apotek = MasterApotek::find(session('id_apotek_active'));
            $inisial = strtolower($apotek->nama_singkat);
            $stok_before = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $detail_pembelian->id_obat)->first(); 
            $stok_now = $stok_before->stok_akhir-$detail_pembelian->jumlah;

            /*$arrayupdate = array(
                'stok_awal'=> $stok_before->stok_akhir, 
                'stok_akhir'=> $stok_now, 
                'updated_at' => date('Y-m-d H:i:s'), 
                'updated_by' => Auth::user()->id
            );*/

            # update ke table stok harga
            $stok_harga = MasterStokHarga::where('id_obat', $detail_pembelian->id_obat)->first();
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
                'id_obat' => $detail_pembelian->id_obat,
                'jumlah' => $detail_pembelian->jumlah,
                'stok_awal' => $stok_before->stok_akhir,
                'stok_akhir' => $stok_now,
                'id_jenis_transaksi' => 14, //hapus pembelian
                'id_transaksi' => $detail_pembelian->id,
                'batch' => null,
                'ed' => null,
                'sisa_stok' => null,
                'hb_ppn' => $detail_pembelian->harga_beli_ppn,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => Auth::user()->id
            );*/

            # create histori
            /*$histori_stok = HistoriStok::where('id_obat', $detail_pembelian->id_obat)->where('jumlah', $detail_pembelian->jumlah)->where('id_jenis_transaksi', 14)->where('id_transaksi', $detail_pembelian->id)->first();
            if(empty($histori_stok)) {*/
                $histori_stok = new HistoriStok;
            //}
            $histori_stok->id_obat = $detail_pembelian->id_obat;
            $histori_stok->jumlah = $detail_pembelian->jumlah;
            $histori_stok->stok_awal = $stok_before->stok_akhir;
            $histori_stok->stok_akhir = $stok_now;
            $histori_stok->id_jenis_transaksi = 14; //hapus pembelian
            $histori_stok->id_transaksi = $detail_pembelian->id;
            $histori_stok->batch = null;
            $histori_stok->ed = null;
            $histori_stok->sisa_stok = null;
            $histori_stok->hb_ppn = $detail_pembelian->harga_beli_ppn;
            $histori_stok->created_at = date('Y-m-d H:i:s');
            $histori_stok->created_by = Auth::user()->id;
            if($histori_stok->save()) {
            } else {
                DB::rollback();
                echo json_encode(array('status' => 0));
            }

            # update stok aktif 
            $cekHistori = HistoriStok::where('id_jenis_transaksi', 2)->where('id_transaksi', $detail_pembelian->id)->first();
            if($cekHistori->sisa_stok < $detail_pembelian->jumlah) {
                $kurangStok = $this->kurangStok($detail_pembelian->id, $detail_pembelian->id_obat, $detail_pembelian->jumlah);
                if($kurangStok['status'] == 0) {
                    DB::rollback();
                    echo json_encode(array('status' => 0));
                } else {
                    $detail_pembelian->id_histori_stok = $kurangStok['array_id_histori_stok'];
                    $detail_pembelian->id_histori_stok_detail = $kurangStok['array_id_histori_stok_detail'];
                    if($detail_pembelian->save()) {
                    } else {
                        DB::rollback();
                        echo 0;
                    }
                }
            } else {
                $keterangan = $cekHistori->keterangan.', Hapus Pembelian pada IDdet.'.$detail_pembelian->id.' sejumlah '.$detail_pembelian->jumlah;
                $cekHistori->sisa_stok = $cekHistori->sisa_stok - $detail_pembelian->jumlah;
                $cekHistori->keterangan = $keterangan;
                if($cekHistori->save()) {
                } else {
                    DB::rollback();
                    echo json_encode(array('status' => 0));
                }
            }

            if($detail_pembelian->save()) {
                # cek apakah masih ada item pada nota yang sama
                $jum_details = TransaksiPembelianDetail::where('is_deleted', 0)->where('id_nota', $detail_pembelian->id_nota)->count();
                 $is_sisa = 1;
                if($jum_details == 0) {
                    $pembelian = TransaksiPembelian::find($detail_pembelian->id_nota);
                    $pembelian->is_deleted = 1;
                    $pembelian->deleted_at = date('Y-m-d H:i:s');
                    $pembelian->deleted_by = Auth::user()->id;
                    if($pembelian->save()) {
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
            $pembelian = TransaksiPembelian::find($id);
            $pembelian->is_deleted = 1;
            $pembelian->deleted_at = date('Y-m-d H:i:s');
            $pembelian->deleted_by = Auth::user()->id;

            //dd($pembelian);exit();

            $detail_pembelians = TransaksiPembelianDetail::where('id_nota', $pembelian->id)->where('is_deleted',0)->get();
            foreach ($detail_pembelians as $key => $detail_pembelian) {
                $detail_pembelian->is_deleted = 1;
                $detail_pembelian->deleted_at = date('Y-m-d H:i:s');
                $detail_pembelian->deleted_by = Auth::user()->id;
               
                # crete histori stok barang
                $apotek = MasterApotek::find(session('id_apotek_active'));
                $inisial = strtolower($apotek->nama_singkat);
                $stok_before = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $detail_pembelian->id_obat)->first(); 
                $stok_now = $stok_before->stok_akhir-$detail_pembelian->jumlah;

                /*$arrayupdate = array(
                    'stok_awal'=> $stok_before->stok_akhir, 
                    'stok_akhir'=> $stok_now, 
                    'updated_at' => date('Y-m-d H:i:s'), 
                    'updated_by' => Auth::user()->id
                );*/

                # update ke table stok harga
                $stok_harga = MasterStokHarga::where('id_obat', $detail_pembelian->id_obat)->first();
                $stok_harga->stok_awal = $stok_before->stok_akhir;
                $stok_harga->stok_akhir = $stok_now;
                $stok_harga->updated_at = date('Y-m-d H:i:s'); 
                $stok_harga->updated_by = Auth::user()->id;
                if($stok_harga->save()) {
                } else {
                    DB::rollback();
                    echo 0;
                }

                /*$arrayinsert = array(
                    'id_obat' => $detail_pembelian->id_obat,
                    'jumlah' => $detail_pembelian->jumlah,
                    'stok_awal' => $stok_before->stok_akhir,
                    'stok_akhir' => $stok_now,
                    'id_jenis_transaksi' => 14, //hapus pembelian
                    'id_transaksi' => $detail_pembelian->id,
                    'batch' => null,
                    'ed' => null,
                    'sisa_stok' => null,
                    'hb_ppn' => $detail_pembelian->hb_ppn,
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => Auth::user()->id
                );*/

                # create histori
                /*$histori_stok = HistoriStok::where('id_obat', $detail_pembelian->id_obat)->where('jumlah', $detail_pembelian->jumlah)->where('id_jenis_transaksi', 14)->where('id_transaksi', $detail_pembelian->id)->first();
                if(empty($histori_stok)) {*/
                    $histori_stok = new HistoriStok;
                //}
                $histori_stok->id_obat = $detail_pembelian->id_obat;
                $histori_stok->jumlah = $detail_pembelian->jumlah;
                $histori_stok->stok_awal = $stok_before->stok_akhir;
                $histori_stok->stok_akhir = $stok_now;
                $histori_stok->id_jenis_transaksi = 14; //hapus pembelian
                $histori_stok->id_transaksi = $detail_pembelian->id;
                $histori_stok->batch = null;
                $histori_stok->ed = null;
                $histori_stok->sisa_stok = null;
                $histori_stok->hb_ppn = $detail_pembelian->hb_ppn;
                $histori_stok->created_at = date('Y-m-d H:i:s');
                $histori_stok->created_by = Auth::user()->id;
                if($histori_stok->save()) {
                } else {
                    DB::rollback();
                    echo 0;
                }

                # update stok aktif 
                $cekHistori = HistoriStok::where('id_jenis_transaksi', 2)->where('id_transaksi', $detail_pembelian->id)->first();
                if($cekHistori->sisa_stok < $detail_pembelian->jumlah) {
                    $kurangStok = $this->kurangStok($detail_pembelian->id, $detail_pembelian->id_obat, $detail_pembelian->jumlah);
                    if($kurangStok['status'] == 0) {
                        DB::rollback();
                        echo 0;
                    } else {
                        $detail_pembelian->id_histori_stok = $kurangStok['array_id_histori_stok'];
                        $detail_pembelian->id_histori_stok_detail = $kurangStok['array_id_histori_stok_detail'];
                        if($detail_pembelian->save()) {
                        } else {
                            DB::rollback();
                            echo 0;
                        }
                    }
                } else {
                    $keterangan = $cekHistori->keterangan.', Hapus Pembelian pada IDdet.'.$detail_pembelian->id.' sejumlah '.$detail_pembelian->jumlah;
                    $cekHistori->sisa_stok = $cekHistori->sisa_stok - $detail_pembelian->jumlah;
                    $cekHistori->keterangan = $keterangan;
                    //dd($cekHistori);
                    if($cekHistori->save()) {
                    } else {
                        DB::rollback();
                        echo 0;
                    }
                }


                if($detail_pembelian->save()) {
                } else {
                    DB::rollback();
                    echo 0;
                }
            }

            if($pembelian->save()){
                DB::commit();
                echo 1;
            }else{
                DB::rollback();
                echo 0;
            }
        }catch(\Exception $e){
            DB::rollback();
            echo 0;
        }
    }

    public function kurangStok($id_detail, $id_obat, $jumlah) {
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
                $sisa_stok = $cekHistori->sisa_stok - $jumlah;
                $keterangan = $cekHistori->keterangan.', Hapus Pembelian pada IDdet.'.$id_detail.' sejumlah '.$jumlah;
                DB::table('tb_histori_stok_'.$inisial)->where('id', $cekHistori->id)->update(['sisa_stok' => $sisa_stok, 'keterangan' => $keterangan]);
                $array_id_histori_stok[] = $cekHistori->id;
                $array_id_histori_stok_detail[] = array('id_histori_stok' => $cekHistori->id, 'jumlah' => $jumlah);
                $hb_ppn = $cekHistori->hb_ppn;
            } else {
                # jika jumlahnya tidak sama maka
                $selisih = $jumlah - $cekHistori->sisa_stok;

                # update jumlah selisih ke histori yang ada stok sebelumnya
                $i = $jumlah;
                $total  = 0;
                while($i >= 1) {
                    # cari histori berikutnya yg bisa dikurangi
                    $cekHistoriLanj = DB::table('tb_histori_stok_'.$inisial)
                            ->where('id_obat', $id_obat)
                            ->whereIn('id_jenis_transaksi', [2,3,11,9])
                            ->where('sisa_stok', '>', 0)
                            ->orderBy('id', 'ASC')
                            ->first();

                    if($cekHistoriLanj->sisa_stok >= $i) {
                        # update selisih jika stok melebihi jumlah
                        $keterangan = $cekHistoriLanj->keterangan.', Hapus Pembelian pada IDdet.'.$id_detail.' sejumlah '.$i;
                        $sisa = $cekHistoriLanj->sisa_stok - $i;
                        DB::table('tb_histori_stok_'.$inisial)->where('id', $cekHistoriLanj->id)->update(['sisa_stok' => $sisa, 'keterangan' => $keterangan]);
                        $array_id_histori_stok_detail[] = array('id_histori_stok' => $cekHistoriLanj->id, 'jumlah' => $i);
                        $total = $total + $cekHistoriLanj->hb_ppn * $i;
                         $i = 0;
                    } else {
                        # update selisih jika stok kurang dari jumlah
                        $keterangan = $cekHistoriLanj->keterangan.', Hapus Pembelian pada IDdet.'.$id_detail.' sejumlah '.$cekHistoriLanj->sisa_stok;
                        $sisa = $i - $cekHistoriLanj->sisa_stok;
                        DB::table('tb_histori_stok_'.$inisial)->where('id', $cekHistoriLanj->id)->update(['sisa_stok' => 0, 'keterangan' => $keterangan]);
                        $i = $sisa;
                        $array_id_histori_stok_detail[] = array('id_histori_stok' => $cekHistoriLanj->id, 'jumlah' => $cekHistoriLanj->sisa_stok);
                        $total = $total + $cekHistoriLanj->hb_ppn * $cekHistoriLanj->sisa_stok;
                    }
                    $array_id_histori_stok[] = $cekHistoriLanj->id;
                }
                $hb_ppn = $total/$jumlah;
                $hb_ppn = ceil($hb_ppn);
            } 

            $rsp = array('status' => 1, 'array_id_histori_stok' => json_encode($array_id_histori_stok), 'array_id_histori_stok_detail' => json_encode($array_id_histori_stok_detail), 'hb_ppn' => $hb_ppn);
            return $rsp;
        } else {
            $rsp = array('status' => 0, 'array_id_histori_stok' => null, 'array_id_histori_stok_detail' => null, 'hb_ppn' => null);
            return $rsp;
        }
    }


    public function kurangStokRetur($id_detail, $id_retur, $id_obat, $jumlah) {
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
                $sisa_stok = $cekHistori->sisa_stok - $jumlah;
                $keterangan = $cekHistori->keterangan.', Retur Pembelian pada IDRetur.'.$id_retur.' IDdet.'.$id_detail.' sejumlah '.$jumlah;
                DB::table('tb_histori_stok_'.$inisial)->where('id', $cekHistori->id)->update(['sisa_stok' => $sisa_stok, 'keterangan' => $keterangan]);
                $array_id_histori_stok[] = $cekHistori->id;
                $array_id_histori_stok_detail[] = array('id_histori_stok' => $cekHistori->id, 'jumlah' => $jumlah);
                $hb_ppn = $cekHistori->hb_ppn;
            } else {
                # jika jumlahnya tidak sama maka
                $selisih = $jumlah - $cekHistori->sisa_stok;

                # update jumlah selisih ke histori yang ada stok sebelumnya
                $i = $jumlah;
                $total  = 0;
                while($i >= 1) {
                    # cari histori berikutnya yg bisa dikurangi
                    $cekHistoriLanj = DB::table('tb_histori_stok_'.$inisial)
                            ->where('id_obat', $id_obat)
                            ->whereIn('id_jenis_transaksi', [2,3,11,9])
                            ->where('sisa_stok', '>', 0)
                            ->orderBy('id', 'ASC')
                            ->first();

                    if($cekHistoriLanj->sisa_stok >= $i) {
                        # update selisih jika stok melebihi jumlah
                        $keterangan = $cekHistoriLanj->keterangan.', Retur Pembelian pada IDRetur.'.$id_retur.' IDdet.'.$id_detail.' sejumlah '.$i;
                        $sisa = $cekHistoriLanj->sisa_stok - $i;
                        DB::table('tb_histori_stok_'.$inisial)->where('id', $cekHistoriLanj->id)->update(['sisa_stok' => $sisa, 'keterangan' => $keterangan]);
                        $array_id_histori_stok_detail[] = array('id_histori_stok' => $cekHistoriLanj->id, 'jumlah' => $i);
                        $total = $total + $cekHistoriLanj->hb_ppn * $i;
                         $i = 0;
                    } else {
                        # update selisih jika stok kurang dari jumlah
                        $keterangan = $cekHistoriLanj->keterangan.', Retur Pembelian pada IDRetur.'.$id_retur.' IDdet.'.$id_detail.' sejumlah '.$cekHistoriLanj->sisa_stok;
                        $sisa = $i - $cekHistoriLanj->sisa_stok;
                        DB::table('tb_histori_stok_'.$inisial)->where('id', $cekHistoriLanj->id)->update(['sisa_stok' => 0, 'keterangan' => $keterangan]);
                        $i = $sisa;
                        $array_id_histori_stok_detail[] = array('id_histori_stok' => $cekHistoriLanj->id, 'jumlah' => $cekHistoriLanj->sisa_stok);
                        $total = $total + $cekHistoriLanj->hb_ppn * $cekHistoriLanj->sisa_stok;
                    }
                    $array_id_histori_stok[] = $cekHistoriLanj->id;
                }
                $hb_ppn = $total/$jumlah;
                $hb_ppn = ceil($hb_ppn);
            } 

            $rsp = array('status' => 1, 'array_id_histori_stok' => json_encode($array_id_histori_stok), 'array_id_histori_stok_detail' => json_encode($array_id_histori_stok_detail), 'hb_ppn' => $hb_ppn);
            return $rsp;
        } else {
            $rsp = array('status' => 0, 'array_id_histori_stok' => null, 'array_id_histori_stok_detail' => null, 'hb_ppn' => null);
            return $rsp;
        }
    }


    public function informasi(){
        return view('pembelian.informasi');
    }

    public function send_sign(Request $request)
    {
        if($request->sign_by != 'false' OR $request->sign_by != false) {
            $pembelian = TransaksiPembelian::find($request->id);
            $pembelian->is_sign = 1;
            $pembelian->sign_by = $request->sign_by;
            $pembelian->sign_at = date('Y-m-d H:i:s');

            if($pembelian->save()){
                echo 1;
            }else{
                echo 0;
            }
        } else {
            echo 0;
        }
    } 

    public function batal_sign(Request $request)
    {
        if($request->sign_by != 'false' OR $request->sign_by != false) {
            $pembelian = TransaksiPembelian::find($request->id);
            $pembelian->is_sign = 0;
            $pembelian->sign_by = null;
            $pembelian->sign_at = null;
            $pembelian->updated_by = Auth::user()->id;
            $pembelian->updated_at = date('Y-m-d H:i:s');

            if($pembelian->save()){
                echo 1;
            }else{
                echo 0;
            }
        } else {
            echo 0;
        }
    } 

    public function UpdatePPN(Request $request) {
        DB::beginTransaction(); 
        try{
            $id = $request->id;
            $pembelian = TransaksiPembelian::find($id);
            $inisial = strtolower(session('nama_apotek_singkat_active'));
            /*dd($pembelian);*/
            if($pembelian->is_deleted == 0) {  
                $pembelian->ppn = $request->ppn;
                $pembelian->save();

                $details = TransaksiPembelianDetail::where('is_deleted', 0)->where('id_nota', $pembelian->id)->get();
                $jum_details = count($details);
                //dd($details);
                if($jum_details > 0) {
                    foreach ($details as $key => $obj) {
                        if($request->ppn > 0) {
                            $obj->harga_beli_ppn = $obj->harga_beli+($pembelian->ppn/100*$obj->harga_beli);
                        } else {
                            $obj->harga_beli_ppn = $obj->harga_beli;
                        }
                        $stok_before = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $obj->id_obat)->first();

                        if($stok_before->harga_beli != $obj->harga_beli) {
                            $data_histori_ = array('id_obat' => $obj->id_obat, 'harga_beli_awal' => $stok_before->harga_beli, 'harga_beli_akhir' => $obj->harga_beli, 'harga_jual_awal' => $stok_before->harga_jual, 'harga_jual_akhir' => $stok_before->harga_jual, 'created_by' => Auth::id(), 'created_at' => date('Y-m-d H:i:s'));

                            DB::table('tb_histori_harga_'.$inisial.'')->insert($data_histori_);
                        }

                        $stok_harga = MasterStokHarga::where('id_obat', $obj->id_obat)->first();
                        $stok_harga->updated_at = date('Y-m-d H:i:s'); 
                        $stok_harga->harga_beli = $obj->harga_beli;
                        $stok_harga->harga_beli_ppn = $obj->harga_beli_ppn;
                        $stok_harga->updated_by = Auth::user()->id;
                        if($stok_harga->save()) {
                        } else {
                            DB::rollback();
                            echo json_encode(array('status' => 0, 'message' => 'stok harga'));
                        }

                        $histori_stok = HistoriStok::where('id_obat', $obj->id_obat)->where('jumlah', $obj->jumlah)->where('id_jenis_transaksi', 2)->where('id_transaksi', $obj->id)->first();
                        $histori_stok->hb_ppn = $obj->harga_beli_ppn;
                        if($histori_stok->save()) {
                        } else {
                            DB::rollback();
                            echo json_encode(array('status' => 0, 'message' => 'histori_stok'));
                        }

                        if($obj->save()) {
                            DB::commit();
                        } else {
                            DB::rollback();
                            echo json_encode(array('status' => 0, 'message' => 'detail pembelian'));
                        }
                    }
                }
            } else {
                DB::rollback();
                echo json_encode(array('status' => 0, 'message' => 'Error, nota ini sudah dihapus, silakan tambah nota baru'));
            }
        }catch(\Exception $e){
            DB::rollback();
            echo json_encode(array('status' => 0, 'message' => $e->getMessage()));
        }
    }

    public function list_detail_pembelian_order(Request $request) {
        # get total to
        $id = $request->id;
        if(is_null($id)) {
            $total_pembelian = 0;
            $total_diskon = 0;
            $diskon2 = 0;
            $ppn = 0;
            $total2 = 0;
            $total_pembelian_bayar = 0;
            $is_sign = 0;
        } else {
            $pembelian = TransaksiPembelian::find($id);

            $total_pembelian = $pembelian->detail_pembelian_total[0]->jumlah;
            $total_diskon = $pembelian->detail_pembelian_total[0]->total_diskon + $pembelian->detail_pembelian_total[0]->total_diskon_persen;
            $diskon2 = $pembelian->diskon2;
            $ppn = $pembelian->ppn;
            if($total_pembelian == "" || $total_pembelian == null) {
                $total_pembelian = 0;
            }

            if($total_diskon == "" || $total_diskon == null) {
                $total_diskon = 0;
            }

            if($diskon2 == "" || $diskon2 == null) {
                $diskon2 = 0;
            }

            if($ppn == "" || $ppn == null) {
                $ppn = 0;
            }
            $total2 = $total_pembelian-$total_diskon;
            $total_pembelian_bayar = ($total2 + ($ppn/100 * $total2)) - $diskon2;
            $is_sign = $pembelian->is_sign;
        }

        if(Auth::user()->is_admin == 1) {
            $is_sign = 1;
        }

        $last_so = SettingStokOpnam::where('id_apotek', session('id_apotek_active'))->where('step', '>', 1)->orderBy('id', 'DESC')->first();


        $details = json_decode($request->id_det_order);

       // print_r($details);exit();

        DB::statement(DB::raw('set @rownum = 0'));
       /* $data = TransaksiPembelianDetail::select([
                DB::raw('@rownum  := @rownum  + 1 AS no'),
                'tb_detail_nota_pembelian.*', 
        ])
        ->where(function($query) use($request){
            $query->where('tb_detail_nota_pembelian.is_deleted','=','0');
            if(is_null($request->id)) {
                $query->where('tb_detail_nota_pembelian.id_nota','=',0);
            } else {
                $query->where('tb_detail_nota_pembelian.id_nota','=',$request->id);
            }
            
        })
        ->orderBy('tb_detail_nota_pembelian.id', 'ASC');*/

        $data = TransaksiOrderDetail::select([
                        DB::raw('@rownum  := @rownum  + 1 AS no'),
                        'tb_detail_nota_order.*',
                        DB::raw('null as id_nota'),
                        DB::raw('0 as jumlah_strip'),
                        DB::raw('0 as jumlah_tab'),
                        DB::raw('0 as total_harga'),
                        DB::raw('0 as harga_jual'),
                        DB::raw('0 as harga_beli'),
                        DB::raw('0 as harga_beli_ppn'),
                        DB::raw('null as id_batch'),
                        DB::raw('null as tgl_batch'),
                        DB::raw('0 as diskon'),
                        DB::raw('0 as diskon_persen'),
                        DB::raw('0 as total'),
                        DB::raw('0 as margin'),
                        DB::raw('0 as total_harga'),
                        DB::raw('0 as total_diskon_persen'),
                        DB::raw('0 as is_revisi'),
                        DB::raw('0 as id_jenis_revisi'),
                        DB::raw('0 as jumlah_revisi'),
                        DB::raw('0 as is_sign'),
                        DB::raw('0 as is_terelasi')
                    ])
                    ->leftJoin('tb_detail_nota_pembelian as a', 'a.id', 'tb_detail_nota_order.id_det_nota_pembalian')
                    ->where(function($query) use($request, $details){
                        $query->where('tb_detail_nota_order.is_deleted','=','0');
                        //$query->where('tb_detail_nota_order.is_status','=','0');
                        $query->whereIn('tb_detail_nota_order.id', $details);
                        $query->Orwhere('a.is_deleted','=','0');
                        $query->where('a.id_nota','=',$request->id);
                    });


        /*$data2 = TransaksiPembelianDetail::select([
                'tb_detail_nota_pembelian.*', 
                DB::raw('1 as is_terelasi')
        ])
        ->where(function($query) use($request){
            $query->where('tb_detail_nota_pembelian.is_deleted','=','0');
            if(is_null($request->id)) {
                $query->where('tb_detail_nota_pembelian.id_nota','=',0);
            } else {
                $query->where('tb_detail_nota_pembelian.id_nota','=',$request->id);
            }
            
        })
        ->orderBy('tb_detail_nota_pembelian.id', 'ASC')->get();

        $data = $data1->merge($data2);*/

        $datatables = Datatables::of($data);
        return $datatables
       
        ->editcolumn('action', function($data) use($request, $is_sign, $last_so){
            $btn ='';

            if($data->is_status == 0) {
                $btn .= '<a href="#" onClick="edit_detail_from_order('.$data->no.','.$data->id.', '.$data->id_obat.')" title="Add data obat" data-toggle="modal" ><span class="btn btn-primary btn-xs" data-toggle="tooltip" data-placement="top" title="Add data obat"><i class="fa fa-plus"></i></span> </a>';
            } else {
                /*$btn .= '<a href="#" onClick="edit_detail_from_order_hapus('.$data->no.','.$data->id.', '.$data->id_obat.')" title="Add data obat" data-toggle="modal" ><span class="btn btn-primary btn-xs" data-toggle="tooltip" data-placement="top" title="Add data obat"><i class="fa fa-edit"></i></span> </a>';*/
            }
            return $btn;
        })
        ->editcolumn('nama_barang', function($data) use($request){
            $nama = '';
            $obat = $data->obat;
            $nama = $obat->nama;
            return $nama;
        })
        ->editcolumn('harga_beli', function($data) use($request){
            if(isset($data->detpembelian)) {
                $harga = $data->detpembelian->harga_beli;
            } else {
                $harga = 0;
            }

            return "Rp ".number_format($harga,0);
        }) 
        ->editcolumn('harga_beli_ppn', function($data) use($request){
            if(isset($data->detpembelian)) {
                $harga = $data->detpembelian->harga_beli_ppn;
            } else {
                $harga = 0;
            }

            return "Rp ".number_format($harga,0);
        })   
        ->editcolumn('diskon', function($data) use($request){
            if(isset($data->detpembelian)) {
                $diskon = $data->detpembelian->diskon;
            } else {
                $diskon = 0;
            }

            return "Rp ".number_format($diskon,0);
        })  
        ->editcolumn('diskon_persen', function($data) use($request){
            if(isset($data->detpembelian)) {
                $diskon_persen = $data->detpembelian->diskon_persen;
            } else {
                $diskon_persen = 0;
            }
            return $diskon_persen.'%';
        })  
        ->editcolumn('margin', function($data) use($request){
            if(isset($data->detpembelian)) {
                $margin = ($data->detpembelian->stok_harga->harga_jual/$data->detpembelian->harga_beli_ppn)*100;
                $margin = number_format($margin,2);
            } else {
                $margin = 0;
            }

            return $margin.'%';
        })  
        ->editcolumn('total', function($data) use($request){
            if(isset($data->pembelian)) {
                $pembelian = $data->pembelian;
                $total_pembelian = $pembelian->detail_pembelian_total[0]->total;
            } else {
                $total_pembelian = 0;
            }

            return "Rp ".number_format($total_pembelian,0);
        })
        ->editcolumn('total1', function($data) use($request){
            if(isset($data->detpembelian)) {
                $total_harga = $data->detpembelian->total_harga;
            } else {
                $total_harga = 0;
            }

            return "Rp ".number_format($total_harga,0);
        })  
        ->editcolumn('total2', function($data) use($request){
            if(isset($data->detpembelian)) {
                $total = $data->detpembelian->total_harga;
                $diskon_persen = ($data->detpembelian->diskon_persen/100) * $total;
                $total = $total-($data->detpembelian->diskon+$diskon_persen);
            } else {
                $total = 0;
            }

            return "Rp ".number_format($total,0);
        })
        ->addcolumn('action', function($data){
            $btn = '<div class="btn-group">';
            $btn .='</div>';
            return $btn; 
        }) 
        ->with([
            "total_pembelian" => $total_pembelian,
            "total_pembelian_format" => "Rp ".number_format($total_pembelian,0),
            "total_diskon" => $total_diskon,
            "total_diskon_format" => "Rp ".number_format($total_diskon,0),
            "diskon2" => $diskon2,
            "diskon2_format" => "Rp ".number_format($diskon2,0),
            "ppn" => $ppn,
            "ppn_format" => "Rp ".number_format($ppn,0),
            "total2" => $total2,
            "total2_format" => "Rp ".number_format($total2,0),
            "total_pembelian_bayar" => $total_pembelian_bayar,
            "total_pembelian_bayar_format" => "Rp ".number_format($total_pembelian_bayar,0)
        ])   
        ->rawColumns(['action', 'nama_barang', 'harga_beli', 'total', 'total1', 'total2', 'diskon'])
        ->addIndexColumn()
        ->make(true);  
    }
}
