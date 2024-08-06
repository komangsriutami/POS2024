<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\TransaksiTO;
use App\TransaksiTODetail;
use App\MasterObat;
use App\MasterApotek;
use App\User;
use App\TransaksiTransfer;
use App\TransaksiTransferDetail;
use App\HistoriStok;
use App\MasterStokHarga;
use App\HistoriStokTujuan;
use App\MasterStokHargaTujuan;
use App\SettingStokOpnam;
use App\TransaksiOrderDetail;
use App\DefectaOutlet;
use App\DefectaOutletHistori;
use App;
use Datatables;
use DB;
use Auth;
use Excel;
use PDF;
use Crypt;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
class T_TOController extends Controller
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
        /*if(Auth::user()->id == 1) {
        } else {
            echo "under maintenance"; exit();
        }*/
        $first_day = date('Y-m-01');
        $date_now = date('Y-m-d');

        $apoteks = MasterApotek::where('is_deleted', 0)->get();
        return view('transfer_outlet.index')->with(compact('apoteks', 'date_now', 'first_day'));
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 07/11/2020
        =======================================================================================
    */
    public function list_transfer_outlet(Request $request)
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

        if(session('id_tahun_active') == date('Y')) {
            $table = 'tb_nota_transfer_outlet';
        } else {
            $table = 'tb_nota_transfer_outlet_histori';
        }

        $tanggal = date('Y-m-d');
        DB::statement(DB::raw('set @rownum = 0'));
        $data = TransaksiTO::select([
                DB::raw('@rownum  := @rownum  + 1 AS no'),
	            "$table.*", 
        ])
        ->where(function($query) use($request, $tanggal, $table){
            $query->where("$table.is_deleted",'=','0');
            $query->where("$table.id_apotek_nota",'=',session('id_apotek_active'));
            $query->where("$table.id",'LIKE',($request->id > 0 ? $request->id : '%'.$request->id.'%'));
            $query->where("$table.id_apotek_tujuan",'LIKE',($request->id_apotek_tujuan > 0 ? $request->id_apotek_tujuan : '%'.$request->id_apotek_tujuan.'%'));
            if($request->tgl_awal != "") {
                $tgl_awal       = date('Y-m-d H:i:s',strtotime($request->tgl_awal));
                $query->whereDate("$table.created_at",'>=', $tgl_awal);
            }

            if($request->tgl_akhir != "") {
                $tgl_akhir      = date('Y-m-d H:i:s',strtotime($request->tgl_akhir));
                $query->whereDate("$table.created_at",'<=', $tgl_akhir);
            }

            if($request->tgl_akhir == "" AND $request->tgl_awal == "") {
                $query->whereYear("$table.created_at", '>=', 2022); // session('id_tahun_active')
            }
        });
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request, $table){
            $query->where(function($query) use($request, $table){
                //$query->orwhere("$table.no_faktur",'LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->editcolumn('id_apotek_asal', function($data){
        	return $data->apotek_asal->nama_singkat;
        })
        ->editcolumn('id_apotek_tujuan', function($data){
        	return $data->apotek_tujuan->nama_singkat;
        })
        ->editcolumn('is_lunas', function($data){
            if($data->is_lunas == 0) {
                return '<span class="label label-danger" data-toggle="tooltip" data-placement="top" title="Faktur Belum Dibayar">Belum Dibayar</span>';
            } else {
                return '<span class="label label-success" data-toggle="tooltip" data-placement="top" title="Faktur Sudah Dibayar"></i> Lunas</span>';
            }
        })     
        ->editcolumn('is_status', function($data){
            if($data->is_status == 0) {
                return '<span class="label label-danger" data-toggle="tooltip" data-placement="top" title="Item obat belum diterima">Belum Diterima</span>';
            } else {
                return '<span class="label label-success" data-toggle="tooltip" data-placement="top" title="Item obat sudah diterima"></i> Sudah Diterima</span>';
            }
        })    
        ->editcolumn('total', function($data) {
            $x = $data->detail_transfer_total[0];

            return 'Rp '.number_format($x->total, 2);
        })  
        ->editcolumn('is_sign', function($data){
            if($data->is_sign == 0) {
                return '<span class="label label-danger" data-toggle="tooltip" data-placement="top" title="Nota belum dicek atau dittd" style="font-size:8pt;color:#e91e63;">Belum diTTD</span>';
            } else {
                return '<span class="label label-success" data-toggle="tooltip" data-placement="top" title="Nota belum dicek atau dittd" style="font-size:8pt;color:#009688;"></i> TTD by <span class="text-warning">'.$data->sign_by.'</span></span>';
            }
        })
        ->addcolumn('action', function($data) use($hak_akses, $apotek, $last_so){
            $btn = '<div class="btn-group">';
            $id_printer_active = session('id_printer_active');
            if(is_null($id_printer_active)) {
                session(['id_printer_active' => $apotek->id_printer]);
                $id_printer_active = session('id_printer_active');
            }

            $cek_det_all = TransaksiTODetail::where('is_deleted', 0)->where('id_nota', $data->id)->count();
            $cek_det_status = TransaksiTODetail::where('is_deleted', 0)->where('id_nota', $data->id)->where('is_status', '!=', 1)->count();

            $check_status = 0;
            if($cek_det_status == $cek_det_all) {
                $check_status = 1;
            }

            $btn .= '<a href="'.url('/transfer_outlet/'.$data->id.'/edit').'" title="Edit Data" class="btn btn-secondary btn-sm"><span data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i> Edit</span></a>';
            if(!empty($last_so)) {
                if($data->tgl_nota >= $last_so->tgl_so) {
                    if($data->is_sign == 1) {
                        if($hak_akses == 1) {
                            if($data->is_status != 1 OR $check_status ==1) {
                                # jika nota belum dikonfirmasi
                                $btn .= '<span class="btn btn-primary btn-sm" onClick="batal_sign('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Batalkan sign ini"><i class="fa fa-unlock"></i>Batal Sign</span>';
                                $btn .= '<span class="btn btn-danger btn-sm" onClick="delete_transfer('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i> Hapus</span>';
                            }
                        }
                    } else {
                        if($data->is_status != 1 OR $check_status ==1) {
                            # jika nota belum dikonfirmasi
                            $btn .= '<span class="btn btn-warning btn-sm" onClick="sign('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Tandai faktur ini sudah dicek dan ttd"><i class="fa fa-signature"></i> Sign</span>';
                            $btn .= '<span class="btn btn-danger btn-sm" onClick="delete_transfer('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i> Hapus</span>';
                        } else {
                            $btn .= '<span class="btn btn-warning btn-sm" onClick="sign('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Tandai faktur ini sudah dicek dan ttd"><i class="fa fa-signature"></i> Sign</span>';
                        }
                    }
                } else {
                    if($hak_akses == 1) {
                        if($data->is_status != 1 OR $check_status ==1) {
                            # jika nota belum dikonfirmasi
                            $btn .= '<span class="btn btn-warning btn-sm" onClick="sign('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Tandai faktur ini sudah dicek dan ttd"><i class="fa fa-signature"></i> Sign</span>';
                            $btn .= '<span class="btn btn-danger btn-sm" onClick="delete_transfer('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i> Hapus</span>';
                        } else {
                            $btn .= '<span class="btn btn-warning btn-sm" onClick="sign('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Tandai faktur ini sudah dicek dan ttd"><i class="fa fa-signature"></i> Sign</span>';
                        }
                    } else {
                        $btn .= '<span class="btn btn-warning btn-sm" onClick="sign('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Tandai faktur ini sudah dicek dan ttd"><i class="fa fa-signature"></i> Sign</span>';
                    }
                }
            } else {
                if($data->is_sign == 1) {
                    if($hak_akses == 1) {
                        if($data->is_status != 1 OR $check_status ==1) {
                            # jika nota belum dikonfirmasi
                            $btn .= '<span class="btn btn-primary btn-sm" onClick="batal_sign('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Batalkan sign ini"><i class="fa fa-unlock"></i>Batal Sign</span>';
                            $btn .= '<span class="btn btn-danger btn-sm" onClick="delete_transfer('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i> Hapus</span>';
                        }
                    } 
                } else {
                    if($data->is_status != 1 OR $check_status ==1) {
                        # jika nota belum dikonfirmasi
                        $btn .= '<span class="btn btn-warning btn-sm" onClick="sign('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Tandai faktur ini sudah dicek dan ttd"><i class="fa fa-signature"></i> Sign</span>';
                        $btn .= '<span class="btn btn-danger btn-sm" onClick="delete_transfer('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i> Hapus</span>';
                    } else {
                        $btn .= '<span class="btn btn-warning btn-sm" onClick="sign('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Tandai faktur ini sudah dicek dan ttd"><i class="fa fa-signature"></i> Sign</span>';
                    }
                }
            }

            if($id_printer_active == 1) {
                $btn .= '<span class="btn btn-primary btn-sm" onClick="cetak_nota('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Cetak Nota"><i class="fa fa-print"></i> Cetak</span>';
            } else {
                $btn .= '<a href="'.url('/transfer_outlet/_cetak_nota/'.$data->id).'" title="Cetak Nota" target="_blank"  class="btn btn-default btn-sm"><span data-toggle="tooltip" data-placement="top" title="Cetak Nota"><i class="fa fa-print"></i> Cetak</span></a>';
            }

            $btn .= '<a href="'.url('/transfer_outlet/invoice/'.Crypt::encrypt($data->id)).'" title="Invoice" class="btn btn-info btn-sm"><span data-toggle="tooltip" data-placement="top" title="Invoice"><i class="fa fa-print"></i> Invoice</span></a>';

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
        ->rawColumns(['action', 'is_status', 'is_lunas', 'id_apotek_asal', 'id_apotek_tujuan', 'total', 'is_sign'])
        ->addIndexColumn()
        ->make(true);  
    }

    public function create() {
        if(session('id_tahun_active') == date('Y')) {
        } else {
            return view('page_not_authorized');
        }
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
    	$apoteks = MasterApotek::whereNotIn('id', [$apotek->id])->where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $tanggal = date('Y-m-d');
        $transfer_outlet = new TransaksiTO;
        $detail_transfer_outlets = new TransaksiTODetail;
        $var = 1;
        return view('transfer_outlet.create')->with(compact('transfer_outlet', 'apoteks', 'detail_transfer_outlets', 'var', 'apotek', 'inisial'));
    }

    public function store(Request $request) {
        if(session('id_tahun_active') == date('Y')) {
        } else {
            return view('page_not_authorized');
        }
        DB::beginTransaction(); 
        try{
            $transfer_outlet = new TransaksiTO;
            $transfer_outlet->fill($request->except('_token'));
            $detail_transfer_outlets = $request->detail_transfer_outlet;

            $apotek = MasterApotek::find(session('id_apotek_active'));
            $inisial = strtolower($apotek->nama_singkat);
            $apoteks = MasterApotek::whereNotIn('id', [$apotek->id])->where('is_deleted', 0)->pluck('nama_singkat', 'id');
            $tanggal = date('Y-m-d');


            $validator = $transfer_outlet->validate();
            if($validator->fails()){
                $var = 0;
                echo json_encode(array('status' => 0));
                /*return view('transfer_outlet.create')->with(compact('transfer_outlet', 'apoteks', 'detail_transfer_outlets', 'var', 'apotek', 'inisial'))->withErrors($validator);*/
            }else{
                if($transfer_outlet->save()) {
                    DB::commit();
                    echo json_encode(array('status' => 1, 'id' => $transfer_outlet->id));
                } else {
                    DB::rollback();
                    echo json_encode(array('status' => 0));
                }
                /*DB::commit();
                session()->flash('success', 'Sukses menyimpan data!');
                return redirect('transfer_outlet');*/
            }
        }catch(\Exception $e){
            DB::rollback();
            echo json_encode(array('status' => 0));
        }
    }

    public function edit($id) {
        /*if(Auth::user()->id == 1) {
        } else {
            echo "under maintenance"; exit();
        }*/
        $transfer_outlet = TransaksiTO::find($id);
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $apoteks = MasterApotek::whereNotIn('id', [$apotek->id])->where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $tanggal = date('Y-m-d');
        $detail_transfer_outlets = $transfer_outlet->detail_transfer_outlet;

        $var = 0;
        if($transfer_outlet->id_apotek_nota != session('id_apotek_active')) {
            session()->flash('error', 'Anda tidak mempunyai hak akses pada nota ini!');
            return redirect('transfer_outlet')->with('message', 'Anda tidak mempunyai hak akses pada nota ini!');
        }
        return view('transfer_outlet.edit')->with(compact('transfer_outlet', 'apoteks', 'detail_transfer_outlets', 'var', 'apotek', 'inisial'));
    }

    public function show($id) {

    }

    public function update(Request $request, $id) {
        DB::beginTransaction(); 
        try{
            $transfer_outlet = TransaksiTO::find($id);
            $transfer_outlet->fill($request->except('_token'));
            $detail_transfer_outlets = $request->detail_transfer_outlet;

            $apotek = MasterApotek::find(session('id_apotek_active'));
            $inisial = strtolower($apotek->nama_singkat);
            $apoteks = MasterApotek::whereNotIn('id', [$apotek->id])->where('is_deleted', 0)->pluck('nama_singkat', 'id');
            $tanggal = date('Y-m-d');

            $validator = $transfer_outlet->validate();
            if($validator->fails()){
                //return view('transfer_outlet.edit')->with(compact('transfer_outlet', 'apoteks', 'detail_transfer_outlets', 'var', 'apotek', 'inisial'))->withErrors($validator);
                echo json_encode(array('status' => 0));
            }else{
                /*$transfer_outlet->save();

                DB::commit();
                session()->flash('success', 'Sukses memperbaharui data!');
                return redirect('transfer_outlet')->with('message', 'Sukses menyimpan data');*/

                if($transfer_outlet->save()) {
                    DB::commit();
                    echo json_encode(array('status' => 1, 'id' => $transfer_outlet->id));
                } else {
                    DB::rollback();
                    echo json_encode(array('status' => 0));
                }
            }
        }catch(\Exception $e){
            DB::rollback();
            /*session()->flash('error', 'Error!');
            return redirect('transfer_outlet');*/
            echo json_encode(array('status' => 0));
        }
    }

    public function destroy_back($id) {
        DB::beginTransaction(); 
        try{
            $apotek = MasterApotek::find(session('id_apotek_active'));
            $inisial = strtolower($apotek->nama_singkat);
            $to = TransaksiTO::find($id);
            $to->is_deleted = 1;
            $to->deleted_at = date('Y-m-d H:i:s');
            $to->deleted_by = Auth::user()->id;
            $apotek2 = MasterApotek::find($to->id_apotek_tujuan);
            $inisial2 = strtolower($apotek2->nama_singkat);

            $detail_transfer_outlets = TransaksiTODetail::where('id_nota', $to->id)->where('is_deleted', 0)->get();
            foreach ($detail_transfer_outlets as $key => $val) {
                $detail_transfer_outlet = TransaksiTODetail::find($val->id);
                $detail_transfer_outlet->is_deleted = 1;
                $detail_transfer_outlet->deleted_at = date('Y-m-d H:i:s');
                $detail_transfer_outlet->deleted_by = Auth::user()->id;
                $detail_transfer_outlet->save();

                $stok_before = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $detail_transfer_outlet->id_obat)->first();
                $jumlah = $detail_transfer_outlet->jumlah;
                $stok_now = $stok_before->stok_akhir+$jumlah;

                # update ke table stok harga
                DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $detail_transfer_outlet->id_obat)->update(['stok_awal'=> $stok_before->stok_akhir, 'stok_akhir'=> $stok_now, 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => Auth::user()->id]);

                # create histori
                DB::table('tb_histori_stok_'.$inisial)->insert([
                    'id_obat' => $detail_transfer_outlet->id_obat,
                    'jumlah' => $jumlah,
                    'stok_awal' => $stok_before->stok_akhir,
                    'stok_akhir' => $stok_now,
                    'id_jenis_transaksi' => 17, //hapus tranfer keluar
                    'id_transaksi' => $detail_transfer_outlet->id,
                    'batch' => null,
                    'ed' => null,
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => Auth::user()->id
                ]);  

                // turn off -> because add konfirmasi transfer barang
                /*$stok_before2 = DB::table('tb_m_stok_harga_'.$inisial2)->where('id_obat', $detail_transfer_outlet->id_obat)->first();
                $stok_now2 = $stok_before2->stok_akhir-$detail_transfer_outlet->jumlah;

                # update ke table stok harga
                DB::table('tb_m_stok_harga_'.$inisial2)->where('id_obat', $detail_transfer_outlet->id_obat)->update(['stok_awal'=> $stok_before2->stok_akhir, 'stok_akhir'=> $stok_now2, 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => Auth::user()->id]);

                # create histori
                DB::table('tb_histori_stok_'.$inisial2)->insert([
                    'id_obat' => $detail_transfer_outlet->id_obat,
                    'jumlah' => $detail_transfer_outlet->jumlah,
                    'stok_awal' => $stok_before2->stok_akhir,
                    'stok_akhir' => $stok_now2,
                    'id_jenis_transaksi' => 16, //hapus transfer masuk
                    'id_transaksi' => $detail_transfer_outlet->id,
                    'batch' => null,
                    'ed' => null,
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => Auth::user()->id
                ]);*/
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
            return redirect('transfer_outlet');
        }
    }

    public function find_ketentuan_keyboard(){
        return view('transfer_outlet._form_ketentuan_keyboard');
    }

    public function edit_detail(Request $request){
        $id = $request->id;
        $no = $request->no;
        $detail = TransaksiTODetail::find($id);
        return view('
            transfer_outlet._form_edit_detail')->with(compact('detail', 'no'));
    }

    public function hapus_detail($id) {
        if(session('id_tahun_active') == date('Y')) {
        } else {
            return view('page_not_authorized');
        }
        DB::beginTransaction(); 
        try{
            $detail_transfer_outlet = TransaksiTODetail::find($id);
            $detail_transfer_outlet->is_deleted = 1;
            $detail_transfer_outlet->deleted_at= date('Y-m-d H:i:s');
            $detail_transfer_outlet->deleted_by = Auth::user()->id;

            $transfer_outlet = TransaksiTO::find($detail_transfer_outlet->id_nota);
            $apotek = MasterApotek::find(session('id_apotek_active'));
            $inisial = strtolower($apotek->nama_singkat);
            $apotek2 = MasterApotek::find($transfer_outlet->id_apotek_tujuan);
            $inisial2 = strtolower($apotek2->nama_singkat);

            $stok_before = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $detail_transfer_outlet->id_obat)->first();
            $jumlah = $detail_transfer_outlet->jumlah;
            $stok_now = $stok_before->stok_akhir+$jumlah;

            # update ke table stok harga
            DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $detail_transfer_outlet->id_obat)->update(['stok_awal'=> $stok_before->stok_akhir, 'stok_akhir'=> $stok_now, 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => Auth::user()->id]);

            # create histori
            DB::table('tb_histori_stok_'.$inisial)->insert([
                'id_obat' => $detail_transfer_outlet->id_obat,
                'jumlah' => $jumlah,
                'stok_awal' => $stok_before->stok_akhir,
                'stok_akhir' => $stok_now,
                'id_jenis_transaksi' => 17, //hapus tranfer keluar
                'id_transaksi' => $detail_transfer_outlet->id,
                'batch' => null,
                'ed' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => Auth::user()->id
            ]);  

            // turn off -> because add konfirmasi transfer barang
            /*$stok_before2 = DB::table('tb_m_stok_harga_'.$inisial2)->where('id_obat', $detail_transfer_outlet->id_obat)->first();
            $stok_now2 = $stok_before2->stok_akhir-$detail_transfer_outlet->jumlah;

            # update ke table stok harga
            DB::table('tb_m_stok_harga_'.$inisial2)->where('id_obat', $detail_transfer_outlet->id_obat)->update(['stok_awal'=> $stok_before2->stok_akhir, 'stok_akhir'=> $stok_now2, 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => Auth::user()->id]);

            # create histori
            DB::table('tb_histori_stok_'.$inisial2)->insert([
                'id_obat' => $detail_transfer_outlet->id_obat,
                'jumlah' => $detail_transfer_outlet->jumlah,
                'stok_awal' => $stok_before2->stok_akhir,
                'stok_akhir' => $stok_now2,
                'id_jenis_transaksi' => 16, //hapus transfer masuk
                'id_transaksi' => $detail_transfer_outlet->id,
                'batch' => null,
                'ed' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => Auth::user()->id
            ]);*/


            $total = TransaksiTODetail::select([
                                DB::raw('SUM(total) as total_all')
                                ])
                                ->where('id', '!=', $detail_transfer_outlet->id)
                                ->where('id_nota', $detail_transfer_outlet->id_nota)
                                ->where('is_deleted', 0)
                                ->first();
            $y = 0;
            if($total->total_all == 0 OR $total->total_all == '') {
                $y = 0;
            } else {
                $y = $total->total_all;
            }

            if($y == 0) {
                $transfer_outlet->total = $y;
                $transfer_outlet->is_deleted = 1;
                $transfer_outlet->deleted_at= date('Y-m-d H:i:s');
                $transfer_outlet->deleted_by = Auth::user()->id;
            }

            $rsp = array();
            if($detail_transfer_outlet->save()){
                $transfer_outlet->save();
                $rsp['status'] = 'Sukses'; 
                $rsp['status_code'] = '0000';
                $rsp['message'] = 'Data berhasil dihapus!';
                $rsp['is_deleted'] = $transfer_outlet->is_deleted;
                $rsp['data'] = $transfer_outlet;

                DB::commit();
                echo json_encode($rsp);
            }else{
                $rsp['status'] = 'Gagal'; 
                $rsp['status_code'] = '1000';
                $rsp['message'] = 'Data gagal dihapus!';
                $rsp['is_deleted'] = $transfer_outlet->is_deleted;
                $rsp['data'] = $transfer_outlet;
                echo json_encode($rsp);
            }
        }catch(\Exception $e){
            DB::rollback();
            session()->flash('error', 'Error!');
            return redirect('transfer_dokter');
        }
    }

    public function cetak_nota(Request $request)
    {   
        $transfer_outlet = TransaksiTO::where('id', $request->id)->first();
        $detail_transfer_outlets = TransaksiTODetail::select(['tb_detail_nota_transfer_outlet.*',
                                                 DB::raw('(tb_detail_nota_transfer_outlet.jumlah * tb_detail_nota_transfer_outlet.harga_outlet) as total')])
                                               ->where('tb_detail_nota_transfer_outlet.id_nota', $transfer_outlet->id)
                                               ->where('tb_detail_nota_transfer_outlet.is_deleted', 0)
                                               ->get();

        return view('transfer_outlet._form_cetak_nota')->with(compact('transfer_outlet', 'detail_transfer_outlets'));
    } 

    public function cetak_nota_thermal($id)
    {   
        $transfer_outlet = TransaksiTO::where('id', $id)->first();
        $detail_transfer_outlets = TransaksiTODetail::select(['tb_detail_nota_transfer_outlet.*',
                                                 DB::raw('(tb_detail_nota_transfer_outlet.jumlah * tb_detail_nota_transfer_outlet.harga_outlet) as total')])
                                               ->where('tb_detail_nota_transfer_outlet.id_nota', $transfer_outlet->id)
                                               ->where('tb_detail_nota_transfer_outlet.is_deleted', 0)
                                               ->get();

        $apotek = MasterApotek::find($transfer_outlet->id_apotek_nota);

        return view('transfer_outlet._form_cetak_nota2')->with(compact('transfer_outlet', 'detail_transfer_outlets', 'apotek'));
    } 

    public function load_data_nota_print($id) {
        $no = 0;

        $nota = TransaksiTO::find($id);
        $detail_transfer_outlets = TransaksiTODetail::where('id_nota', $nota->id)->where('is_deleted', 0)->get();
        $apotek = $nota->apotek_tujuan;
        $apotek_asal = $nota->apotek_asal;

        $nama_apotek = strtoupper($apotek_asal->nama_panjang);
        $nama_apotek_singkat = strtoupper($apotek_asal->nama_singkat);

        $nama_singkat_tujuan = strtoupper($apotek->nama_singkat);

        $a = str_pad("",40," ", STR_PAD_LEFT)."\n".
             str_pad("APOTEK BWF-".$nama_apotek, 40," ", STR_PAD_BOTH)."\n".
             str_pad($apotek_asal->alamat, 40," ", STR_PAD_BOTH)."\n".
             str_pad("Telp. ". $apotek_asal->telepon, 40," ", STR_PAD_BOTH);
        $a = $a."\n".
        "----------------------------------------\n".
        "No. Nota  : ".$nama_apotek_singkat."-".$nota['id']."\n".
        "Tanggal   : ".Carbon::parse($nota['created_at'])->format('d-m-Y H:i:s')."\n".
        "AP Tujuan : ".$apotek->nama_panjang."\n".
        "----------------------------------------\n";

        $b="\n".
        "        ".$nama_apotek_singkat.",               Kurir,       \n".
        "                                        \n".
        "                                        \n".
        "                                        \n".
        "(-----------------)  (-----------------)\n";

        $b=$b."\n".
        "       Kurir,                ".$nama_singkat_tujuan.",       \n".
        "                                        \n".
        "                                        \n".
        "                                        \n".
        "(-----------------)  (-----------------)\n";

        
        $total_belanja = 0;
        foreach ($detail_transfer_outlets as $key => $val) {
            $no++;
            $total_1 = $val->jumlah * $val->harga_outlet;
            $total_belanja = $total_belanja + $total_1;
            
          /*  $printer -> setJustification( Printer::JUSTIFY_LEFT );
            $printer -> text($no.".");
            $printer -> text("(".$val['id_obat'].")");
            $printer -> text($obat['nama']."\n");
            $printer -> text("     ".$val['jumlah']."X".number_format($val['harga_jual'],0,',',',')." (-".number_format($val['diskon'],0,',',',').")"." = Rp ".number_format($total_2,0,',',',')."\n");
*/
            $a=$a.
                str_pad($no.".".$val->obat->nama, 40," ", STR_PAD_RIGHT)."\n ".                 
                //str_pad(" (diskon ".number_format($diskon, 0, '.', ',')."%)",11," ", STR_PAD_LEFT)."\n ".
                str_pad(number_format($val->harga_outlet, 0, '.', ','), 7," ", STR_PAD_LEFT).
                str_pad(" x ",3," ", STR_PAD_LEFT).
                str_pad(number_format($val->jumlah, 0, '.', ','),9," ", STR_PAD_RIGHT).
                str_pad("= ",3," ", STR_PAD_LEFT).str_pad("Rp ". number_format($total_1, 0, '.', ','),10," ", STR_PAD_LEFT)."\n";

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

    public function load_page_print_nota($id) {
        $no = 0;

        $nota = TransaksiTO::find($id);
        $apotek = MasterApotek::find($nota->id_apotek_nota);
        $detail_transfer_outlets = TransaksiTODetail::where('id_nota', $nota->id)->where('is_deleted', 0)->get();
        $apotek = $nota->apotek_tujuan;
        $apotek_asal = $nota->apotek_asal;

        $nama_apotek = strtoupper($apotek_asal->nama_panjang);
        $nama_apotek_singkat = strtoupper($apotek_asal->nama_singkat);

        $nama_singkat_tujuan = strtoupper($apotek->nama_singkat);


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
                    <td colspan="2">Tujuan &nbsp;&nbsp;: '.$apotek->nama_panjang.'</td>
                </tr>
                <tr>
                    <td colspan="2">------------------------------</td>
                </tr>';

        $total_belanja = 0;
        foreach ($detail_transfer_outlets as $key => $val) {
            $no++;
            $total_1 = $val->jumlah * $val->harga_outlet;
            $total_belanja = $total_belanja + $total_1;
            $harga_jual = number_format($val->harga_outlet,0,',',',');
            $total_2 = number_format($total_1,0,',',',');

            $a .= ' 
            <tr>
                <td colspan="2">'.$no.'.'.$val->obat->nama.'</td>
            </tr>
            <tr>
                <td colspan="2">&nbsp;&nbsp;'.$harga_jual.'x'.number_format($val->jumlah, 0, '.', ',').' = '.'Rp'. $total_2.'</td>
            </tr>';
        } 
        $a .= ' <tr>
                    <td colspan="2">------------------------------</td>
                </tr>';

        $grand_total_format = number_format($total_belanja,0,',',',');

        $a .= ' 
                <tr>
                    <td colspan="2">Total &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: Rp '.$grand_total_format.'</td>
                </tr>';
        $a .= ' <tr>
                <td colspan="2">------------------------------</td>
            </tr>';
       
        $a .= ' <tr>
                <td colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$nama_apotek_singkat.',&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Kurir, &nbsp;&nbsp;&nbsp;</td>
            </tr>';
         $a .= ' <tr>
                <td colspan="2"><br></td>
            </tr>';
        $a .= ' <tr>
            <td colspan="2"><br></td>
        </tr>';
        $a .= ' <tr>
            <td colspan="2"><br></td>
        </tr>';
        $a .= ' <tr>
            <td colspan="2"><br></td>
        </tr>';
         $a .= ' <tr>
                <td colspan="2">(-------------)(-------------)</td>
            </tr>';
         $a .= ' <tr>
            <td colspan="2"><br></td>
        </tr>';
        $a .= ' <tr>
            <td colspan="2">&nbsp;&nbsp;&nbsp; Kurir, &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$nama_singkat_tujuan.',&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
        </tr>';
        $a .= ' <tr>
                <td colspan="2"><br></td>
            </tr>';
        $a .= ' <tr>
            <td colspan="2"><br></td>
        </tr>';
        $a .= ' <tr>
            <td colspan="2"><br></td>
        </tr>';
        $a .= ' <tr>
            <td colspan="2"><br></td>
        </tr>';
        $a .= ' <tr>
                <td colspan="2">(-------------)(-------------)</td>
            </tr>';
    
        $a .= ' <tr>
                <td colspan="2">------------------------------</td>
            </tr>';

        $a .= '
                <tr>
                    <td colspan="2" align="center"> ~ Selamat bekerja ~</td>
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

    public function pencarian_obat() {
        return view('transfer_outlet.pencarian_obat');
    }

    public function list_pencarian_obat(Request $request) {
        if(session('id_tahun_active') == date('Y')) {
            $detTable = 'tb_detail_nota_transfer_outlet';
            $table = 'tb_nota_transfer_outlet';
        } else {
            $detTable = 'tb_detail_nota_transfer_outlet_histori';
            $table = 'tb_nota_transfer_outlet_histori';
            $is_sign = 0;
        }

        DB::statement(DB::raw('set @rownum = 0'));
        $data = TransaksiTODetail::select([DB::raw('@rownum  := @rownum  + 1 AS no'),"$detTable.*", 'a.nama'])
        ->join('tb_m_obat as a', 'a.id', "$detTable.id_obat")
        ->join("$table as b", 'b.id', "$detTable.id_nota")
        ->where(function($query) use($request, $detTable, $table){
            $query->where("$detTable.is_deleted",'=','0');
            $query->where('b.id_apotek_nota','=',session('id_apotek_active'));
        })
        ->orderBy('b.id', 'DESC');
        
        $datatables = Datatables::of($data);
        return $datatables  
        ->filter(function($query) use($request, $detTable, $table){
            $query->where(function($query) use($request, $detTable, $table){
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
            $info = '<small>AP Tujuan : '.$data->nota->apotek_tujuan->nama_panjang.'</small>';
            return $data->nama.'<br>'.$info;
        })  
        ->editcolumn('total', function($data) {
            $total = ($data->jumlah*$data->harga_outlet);
            $str_ = '';
            $str_ = $data->jumlah.' X Rp '.number_format($data->harga_outlet, 2).' = Rp '.number_format($total, 2);
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
        $start = date_create("2021-01-01");
        $end = date_create("2021-01-10");
        $rekaps = TransaksiTO::select([
                                    DB::raw('@rownum  := @rownum  + 1 AS no'),
                                    'tb_nota_transfer_outlet.*'
                                ])
                                ->where(function($query) use($request){
                                    $query->where('tb_nota_transfer_outlet.is_deleted','=','0');
                                    $query->where('tb_nota_transfer_outlet.id_apotek_nota','=',session('id_apotek_active'));
                                    $query->where('tb_nota_transfer_outlet.id','LIKE',($request->id > 0 ? $request->id : '%'.$request->id.'%'));
                                    $query->where('tb_nota_transfer_outlet.id_apotek_tujuan','LIKE',($request->id_apotek_tujuan > 0 ? $request->id_apotek_tujuan : '%'.$request->id_apotek_tujuan.'%'));
                                    if (!empty($request->tgl_awal) && !empty($request->tgl_akhir)) {
                                        $query->where('tb_nota_transfer_outlet.created_at','>=', $request->tgl_awal);
                                        $query->where('tb_nota_transfer_outlet.created_at','<=', $request->tgl_akhir);
                                    }
                                })
                                ->groupBy('tb_nota_transfer_outlet.id')
                                ->get();


                $collection = collect();
                $no = 0;
                $total_excel=0;
                foreach($rekaps as $rekap) {
                    $no++;
                    $x = $rekap->detail_transfer_total[0];
                    $collection[] = array(
                        $no,
                        $rekap->created_at,
                        $rekap->apotek_asal->nama_singkat,
                        $rekap->apotek_tujuan->nama_singkat,
                        $x->total,
                        "Rp ".number_format($x->total,2),
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
                        return ['No', 'Tanggal', 'AP Asal', 'AP Tujuan', 'Total', 'Total (Rp)', 'Keterangan'];
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
                            'C' => 15,
                            'D' => 15,
                            'E' => 25,
                            'F' => 25,
                            'G' => 70,            
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
                            'F'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                        ];
                    }


                    public function collection()
                    {
                        return $this->collection;
                    }
        },"Rekap Transfer Outlet.xlsx");
    }

    public function change_apotek(Request $request) {
        $transfer_outlet = TransaksiTO::find($request->id_transfer);
        $apoteks      = MasterApotek::where('is_deleted', 0)->pluck('nama_singkat', 'id');
        /*$apoteks->prepend('-- Pilih Apotek --','');*/
        return view('transfer_outlet._change_apotek')->with(compact('transfer_outlet', 'apoteks'));
    }


    public function update_apotek(Request $request, $id) {
        DB::beginTransaction(); 
        try{
            $transfer_outlet = TransaksiTO::find($id);

            if($request->id_apotek_awal != $request->id_apotek_akhir) {
                $detail_transfer_outlets = $transfer_outlet->detail_transfer_outlet;
                $apotek_awal = MasterApotek::find($request->id_apotek_awal);
                $inisial_awal = strtolower($apotek_awal->nama_singkat);

                $apotek_akhir = MasterApotek::find($request->id_apotek_akhir);
                $inisial_akhir = strtolower($apotek_akhir->nama_singkat);

                foreach ($detail_transfer_outlets as $key => $detail_transfer_outlet) {
                    # cek apakah transaksinya telah diterima sebelumnya
                    $cek_ = DB::table('tb_histori_stok_'.$inisial_awal)
                            ->where('id_obat', $detail_transfer_outlet->id_obat)
                            ->where('id_transaksi', $detail_transfer_outlet->id)
                            ->first();

                    if($cek_) {
                        // create histori stok hapus data pembelian dengan id_apotek_awal
                        $stok_before_awal = DB::table('tb_m_stok_harga_'.$inisial_awal)->where('id_obat', $detail_transfer_outlet->id_obat)->first();
                        $jumlah = $detail_transfer_outlet->jumlah;
                        $stok_now_awal = $stok_before_awal->stok_akhir-$jumlah;

                        # update ke table stok harga
                        DB::table('tb_m_stok_harga_'.$inisial_awal)->where('id_obat', $detail_transfer_outlet->id_obat)->update(['stok_awal'=> $stok_before_awal->stok_akhir, 'stok_akhir'=> $stok_now_awal, 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => Auth::user()->id]);

                        # create histori
                        DB::table('tb_histori_stok_'.$inisial_awal)->insert([
                            'id_obat' => $detail_transfer_outlet->id_obat,
                            'jumlah' => $jumlah,
                            'stok_awal' => $stok_before_awal->stok_akhir,
                            'stok_akhir' => $stok_now_awal,
                            'id_jenis_transaksi' => 28, //hapus tranfer keluar -> ganti apotek
                            'id_transaksi' => $detail_transfer_outlet->id,
                            'batch' => null,
                            'ed' => null,
                            'created_at' => date('Y-m-d H:i:s'),
                            'created_by' => Auth::user()->id
                        ]);  
                    } 

                    // turn off -> because add konfirmasi transfer barang
                    // create gistori stok yang baru dengan id_apotek_baru
                    /*$stok_before_akhir = DB::table('tb_m_stok_harga_'.$inisial_akhir)->where('id_obat', $detail_transfer_outlet->id_obat)->first();
                    $jumlah = $detail_transfer_outlet->jumlah;
                    $stok_now_akhir = $stok_before_akhir->stok_akhir+$jumlah;

                    # update ke table stok harga
                    DB::table('tb_m_stok_harga_'.$inisial_akhir)->where('id_obat', $detail_transfer_outlet->id_obat)->update(['stok_awal'=> $stok_before_akhir->stok_akhir, 'stok_akhir'=> $stok_now_akhir, 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => Auth::user()->id]);

                    # create histori
                    DB::table('tb_histori_stok_'.$inisial_akhir)->insert([
                        'id_obat' => $detail_transfer_outlet->id_obat,
                        'jumlah' => $jumlah,
                        'stok_awal' => $stok_before_akhir->stok_akhir,
                        'stok_akhir' => $stok_now_akhir,
                        'id_jenis_transaksi' => 29, //penambahan tranfer keluar -> ganti apotek
                        'id_transaksi' => $detail_transfer_outlet->id,
                        'batch' => null,
                        'ed' => null,
                        'created_at' => date('Y-m-d H:i:s'),
                        'created_by' => Auth::user()->id
                    ]); */
                }

                $transfer_outlet->id_apotek_tujuan = $request->id_apotek_akhir;
                $transfer_outlet->updated_at= date('Y-m-d H:i:s');
                $transfer_outlet->updated_by = Auth::user()->id;

                if($transfer_outlet->save()){
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
            return redirect('transfer_outlet/'.$id.'/edit');
        }
    }

    public function change_obat(Request $request) {
        $detail_transfer_outlet = TransaksiTODetail::find($request->id_detail_transfer);
        $obats      = MasterObat::where('is_deleted', 0)->pluck('nama', 'id');
        $no = $request->no;

        return view('transfer_outlet._change_obat')->with(compact('detail_transfer_outlet', 'obats', 'no'));
    }


    public function update_obat(Request $request, $id) {
        DB::beginTransaction(); 
        try{
            $detail_transfer_outlet = TransaksiTODetail::find($id);
            $transfer_outlet = TransaksiTO::find($detail_transfer_outlet->id_nota);
            $apotek = MasterApotek::find($transfer_outlet->id_apotek_nota);
            $inisial = strtolower($apotek->nama_singkat);

            if($request->id_obat_awal != $request->id_obat_akhir) {
                // create histori stok dengan id_obat_awal
                $stok_before_awal = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $request->id_obat_awal)->first();
                $jumlah = $detail_transfer_outlet->jumlah;
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
                    'id_transaksi' => $detail_transfer_outlet->id,
                    'batch' => null,
                    'ed' => null,
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => Auth::user()->id
                ]);  

                // turn off -> because add konfirmasi transfer barang
                // create histori stok dengan id_obat_akhir
                /*$stok_before_akhir = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $request->id_obat_akhir)->first();
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
                    'id_transaksi' => $detail_transfer_outlet->id,
                    'batch' => null,
                    'ed' => null,
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => Auth::user()->id
                ]); */

                $detail_transfer_outlet->id_obat = $request->id_obat_akhir;
                $detail_transfer_outlet->updated_at= date('Y-m-d H:i:s');
                $detail_transfer_outlet->updated_by = Auth::user()->id;

                if($detail_transfer_outlet->save()){
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
            return redirect('transfer_outlet/'.$id.'/edit');
        }
    }

    public function open_list_harga(Request $request) {
        $id_obat = $request->id_obat;
        $obat = MasterObat::find($id_obat);
        return view('transfer_outlet._dialog_open_list_harga')->with(compact('id_obat', 'obat'));
    }

    public function list_data_harga_obat(Request $request)
    {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);

        DB::statement(DB::raw('set @rownum = 0'));
        $data = DB::table('tb_histori_harga_'.$inisial.'')->select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_histori_harga_'.$inisial.'.*', 'users.nama as oleh'])
                ->join('users', 'users.id', '=', 'tb_histori_harga_'.$inisial.'.created_by')
                ->where('tb_histori_harga_'.$inisial.'.id_obat', $request->id_obat);
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request, $barcode){
            $query->where(function($query) use($request, $barcode){
                $query->orwhere('b.nama','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('b.barcode','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('b.sku','LIKE','%'.$request->get('search')['value'].'%');
            });
        })   
        ->editcolumn('harga_beli_ppn', function($data){
            return 'Rp '.number_format($data->harga_beli_ppn, 2, '.', ','); 
        }) 
        ->editcolumn('harga_beli', function($data){
            return 'Rp '.number_format($data->harga_beli, 2, '.', ','); 
        }) 
        ->editcolumn('harga_jual', function($data){
            return 'Rp '.number_format($data->harga_jual, 2, '.', ','); 
        }) 
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .= '<span class="btn btn-primary" onClick="add_harga_item('.$data->id_obat.', '.$data->harga_beli_ppn.')" data-toggle="tooltip" data-placement="top" title="Tambah Item"><i class="fa fa-plus"></i></span>';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['harga_beli_ppn', 'action'])
        ->addIndexColumn()
        ->make(true);  
    }

    public function konfirmasi_transfer($id) {
        $id = decrypt($id);
        $transfer = TransaksiTransfer::select('tb_nota_transfer.*')
                                ->where('tb_nota_transfer.is_deleted', 0)
                                ->where('tb_nota_transfer.is_status', 0)
                                ->where('tb_nota_transfer.id_apotek_transfer', session('id_apotek_active'))
                                ->where('id', $id)
                                ->first();

        $apoteks = MasterApotek::where('is_deleted', 0)->pluck('nama_singkat','id');
        $idTORelasi = TransaksiTransferDetail::select('tb_detail_nota_transfer.id_nota_transfer_outlet')
                                ->where('tb_detail_nota_transfer.is_deleted', 0)
                                ->where('id_nota', $id)
                                ->get();

        $transfer_outlets = TransaksiTO::whereIn('id', $idTORelasi)->where('is_deleted', 0)->get();

        return view('konfirmasi_transfer.create')->with(compact('transfer_outlets', 'apoteks', 'transfer'));
    }

    public function list_data_transfer(Request $request) {
        if(session('id_tahun_active') == date('Y')) {
            $detTable = 'tb_detail_nota_transfer_outlet';
            $table = 'tb_nota_transfer_outlet';
        } else {
            $detTable = 'tb_detail_nota_transfer_outlet_histori';
            $table = 'tb_nota_transfer_outlet_histori';
            $is_sign = 0;
        }

        DB::statement(DB::raw('set @rownum = 0'));
        $data = TransaksiTransferDetail::select([
                DB::raw('@rownum  := @rownum  + 1 AS no'),
                "$detTable.*"
        ])
        ->join("$table", "$table.id", '=', "$detTable.id_nota")
        ->join('tb_m_obat', 'tb_m_obat.id', '=', "$detTable.id_obat")
        ->where(function($query) use($request, $table, $detTable){
            $query->where("$detTable.is_deleted",'=','0');
            $query->where("$detTable.is_status",'=','0');
            $query->where("$detTable.id_nota", $request->id_nota);
            $query->where("$detTable.id_apotek_transfer", session('id_apotek_active'));
        });
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request, $table, $detTable){
            $query->where(function($query) use($request, $table, $detTable){
                $query->orwhere("$detTable.id",'LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('tb_m_obat.nama','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('tb_m_obat.barcode','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('tb_m_obat.sku','LIKE','%'.$request->get('search')['value'].'%');
            });
        })   
        ->addColumn('checkList', function ($data) {
            return '<input type="checkbox" name="check_list" data-id="'.$data->id.'" data-id_apotek="'.$data->id_apotek.'" value="'.$data->id.'"/>';
            //return '<input type="checkbox" name="detail_transfer['.$data->no.'][id_detail_transfer]" id="detail_transfer['.$data->no.'][id_detail_transfer]" value="'.$data->id.'">';
        })
        /*->editcolumn('checkList', function($data){
            return '<input type="checkbox" name="detail_transfer['.$data->no.'][id_detail_transfer]" id="detail_transfer['.$data->no.'][id_detail_transfer]" value="'.$data->id.'">';
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

    public function konfirmasi_transfer_store(Request $request) {
        if(session('id_tahun_active') == date('Y')) {
        } else {
            return view('page_not_authorized');
        }
        $transfer_outlet = new TransaksiTO;
        $transfer_outlet->fill($request->except('_token'));
        $details = explode(",", $request->arr_id_transfer);
        $id_jenis_konfirmasi = $request->id_jenis_konfirmasi;
        $id_transfer_outlet = $request->id_nota_transfer;
        //dd($transfer_outlet);
        $id_det_transfer = array();
        $detail_transfer_outlets = array();
        $id_transfer = '';
        $id_apotek_transfer = '';
        foreach ($details as $key => $val) {
            $id_det_transfer[] = $val;
            $transfer = TransaksiTransferDetail::select(['tb_detail_nota_transfer.*'])
                        ->where('tb_detail_nota_transfer.id', $val)
                        ->first();
            $id_transfer = $transfer->id_nota;
        }

        if($id_transfer == '') {
            $id_transfer = encrypt($id_transfer);
            session()->flash('error', 'Data SP tidak ditemukan !');
            return redirect('transfer_outlet/konfirmasi_barang/'.$id_transfer);
        } else {
            $transfer = TransaksiTransfer::find($id_transfer);
            $transfer_outlet->id_apotek_tujuan = $transfer->id_apotek;
        }


        if($id_transfer_outlet == '' OR is_null($id_transfer_outlet)) {
            $idTORelasi = TransaksiTransferDetail::select('tb_detail_nota_transfer.id_nota_transfer_outlet')
                                ->where('tb_detail_nota_transfer.is_deleted', 0)
                                ->where('id_nota', $id_transfer)
                                ->first();
            if(!is_null($idTORelasi->id_nota_transfer_outlet)) {
                $transfer_outlet = TransaksiTO::find($idTORelasi->id_nota_transfer_outlet);
            }
        } else {
            $transfer_outlet = TransaksiTO::find($id_transfer_outlet);
        }

        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $apoteks = MasterApotek::whereIn('id', [$transfer->id_apotek])->where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $tanggal = date('Y-m-d');

        $details = json_encode($id_det_transfer);
        $var = 1;
        $is_from_transfer = 1;
        return view('transfer_outlet_defecta.create')->with(compact('transfer_outlet', 'apoteks', 'apotek', 'detail_transfer_outlets', 'var', 'is_from_transfer', 'details', 'transfer'));
    }

    public function set_konfirm_barang_tidak_disetujui(Request $request)
    {
        if(session('id_tahun_active') == date('Y')) {
        } else {
            return view('page_not_authorized');
        }
        DB::beginTransaction(); 
        try{
            $arr_id_transfer = $request->arr_id_transfer;

            $orderDets = TransaksiTransferDetail::select([
                            'tb_detail_nota_transfer.*'
                        ])
                        ->where('is_deleted', 0)
                        ->whereIn('id', $arr_id_transfer)
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
                        'id_status' => 3,
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

    public function list_detail_transfer_outlet_defecta(Request $request) {
        # get total to
        $id = $request->id;
        if(is_null($id)) {
            $total_transfer = 0;
            $is_sign = 0;
        } else {
            $transfer_outlet = TransaksiTO::find($id);



            $total_transfer = $transfer_outlet->detail_transfer_total[0]->total;
            if($total_transfer == "" || $total_transfer == null) {
                $total_transfer = 0;
            }

            $is_sign = $transfer_outlet->is_sign;
        }

        $last_so = SettingStokOpnam::where('id_apotek', session('id_apotek_active'))->where('step', '>', 1)->orderBy('id', 'DESC')->first();


        $details = json_decode($request->id_det_transfer);
        DB::statement(DB::raw('set @rownum = 0'));

        if(session('id_tahun_active') == date('Y')) {
            $detTable = 'tb_detail_nota_transfer_outlet';
            $table = 'tb_nota_transfer_outlet';
        } else {
            $detTable = 'tb_detail_nota_transfer_outlet_histori';
            $table = 'tb_nota_transfer_outlet_histori';
            $is_sign = 0;
        }
    
        $data = TransaksiTransferDetail::select([
                        DB::raw('@rownum  := @rownum  + 1 AS no'),
                        'tb_detail_nota_transfer.*'
                    ])
                    ->leftJoin("$detTable as a", 'a.id', 'tb_detail_nota_transfer.id_det_nota_transfer_outlet')
                    ->where(function($query) use($request, $details, $table, $detTable){
                        $query->where('tb_detail_nota_transfer.is_deleted','=','0');
                        //$query->where('tb_detail_nota_transfer.is_status','=','0');
                        $query->whereIn('tb_detail_nota_transfer.id', $details);
                        $query->Orwhere('a.is_deleted','=','0');
                        $query->where('a.id_nota','=',$request->id);
                    });
                    //->get();

        //$dd = $data->toArray();
        /*foreach($data as $obj) {
            if(isset($data->detTO)) {
                $harga = $data->detTO->harga_outlet;
            } else {
                $harga = 0;
            }

            dd($harga);
        }*/

        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request){
            $query->where(function($query) use($request){
                $query->orwhere('tb_detail_nota_transfer.id','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->editcolumn('action', function($data) use($request, $is_sign, $last_so){
            $btn ='';

            if($data->is_status == 0) {
                $btn .= '<a href="#" onClick="edit_detail_from_transfer('.$data->no.','.$data->id.', '.$data->id_obat.')" title="Add data obat" data-toggle="modal" ><span class="btn btn-primary btn-xs" data-toggle="tooltip" data-placement="top" title="Add data obat"><i class="fa fa-plus"></i></span> </a>';

            }
            return $btn;
        })
        ->editcolumn('nama_barang', function($data) use($request){
            $nama = '';
            $obat = $data->obat;
            $nama = $obat->nama;
            return $nama;
        })
        ->editcolumn('harga_outlet', function($data) use($request){
            if(isset($data->detTO)) {
                $harga = $data->detTO->harga_outlet;
            } else {
                $harga = 0;
            }

            return "Rp ".number_format($harga,0);
        }) 
        ->editcolumn('total', function($data) use($request){
            if(isset($data->detTO)) {
                $total = $data->detTO->total;
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
            "total_transfer" => $total_transfer,
            "total_transfer_format" => "Rp ".number_format($total_transfer,0)
        ])   
        ->rawColumns(['action', 'nama_barang', 'harga_outlet', 'total'])
        ->addIndexColumn()
        ->make(true);  
    }

    public function edit_detail_from_transfer(Request $request) {
        $id = $request->id_detail_transfer;
        $no = $request->no;
        $transfer = TransaksiTransferDetail::find($id);
        if(is_null($transfer->id_det_nota_transfer_outlet)) {
            $detail = new TransaksiTODetail;
            $transfer_outlet = new TransaksiTO;
        } else {
            $detail = TransaksiTODetail::find($order->id_det_nota_transfer_outlet);
            $transfer_outlet = TransaksiTO::find($detail->id_nota);
        }

         $detailTransfer = TransaksiTransferDetail::find($id);

        return view('transfer_outlet_defecta._form_edit_detail')->with(compact('detail', 'no', 'transfer', 'transfer_outlet', 'detailTransfer'));
    }

    public function konfirmasi_barang() {
        /*if(Auth::user()->id == 1) {
        } else {
            echo "under maintenance"; exit();
        }*/
        $apoteks = MasterApotek::where('is_deleted', 0)->get();
        return view('transfer_outlet.konfirmasi_barang')->with(compact('apoteks'));
    }

    public function list_konfirmasi_barang(Request $request)
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

        if(session('id_tahun_active') == date('Y')) {
            $detTable = 'tb_detail_nota_transfer_outlet';
            $table = 'tb_nota_transfer_outlet';
        } else {
            $detTable = 'tb_detail_nota_transfer_outlet_histori';
            $table = 'tb_nota_transfer_outlet_histori';
            $hak_akses = 0;
        }

        $tanggal = date('Y-m-d');
        DB::statement(DB::raw('set @rownum = 0'));
        $data = TransaksiTO::select([
                DB::raw('@rownum  := @rownum  + 1 AS no'),
                "$table.*", 
        ])
        ->where(function($query) use($request, $tanggal, $table, $detTable){
            $query->where("$table.is_deleted",'=','0');
            $query->where("$table.is_status", 0);
            $query->where("$table.is_sign", 1);
            $query->where("$table.id_apotek_tujuan",'=',session('id_apotek_active'));
            $query->where("$table.id",'LIKE',($request->id > 0 ? $request->id : '%'.$request->id.'%'));
            if($request->tgl_awal != "") {
                $tgl_awal       = date('Y-m-d H:i:s',strtotime($request->tgl_awal));
                $query->whereDate("$table.created_at",'>=', $tgl_awal);
            }

            if($request->tgl_akhir != "") {
                $tgl_akhir      = date('Y-m-d H:i:s',strtotime($request->tgl_akhir));
                $query->whereDate("$table.created_at",'<=', $tgl_akhir);
            }
        });
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request, $table, $detTable){
            $query->where(function($query) use($request, $table, $detTable){
                //$query->orwhere("$table.no_faktur",'LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->editcolumn('id_apotek_asal', function($data){
            return $data->apotek_asal->nama_singkat;
        })
        ->editcolumn('id_apotek_tujuan', function($data){
            return $data->apotek_tujuan->nama_singkat;
        })
        ->editcolumn('is_lunas', function($data){
            if($data->is_lunas == 0) {
                return '<span class="text-danger" data-toggle="tooltip" data-placement="top" title="Faktur Belum Dibayar">Belum Dibayar</span>';
            } else {
                return '<span class="text-success" data-toggle="tooltip" data-placement="top" title="Faktur Sudah Dibayar"></i> Lunas</span>';
            }
        })     
        ->editcolumn('is_status', function($data){
            if($data->is_status == 0) {
                return '<span class="text-danger" data-toggle="tooltip" data-placement="top" title="Item obat belum diterima">Belum Diterima</span>';
            } else {
                return '<span class="text-success" data-toggle="tooltip" data-placement="top" title="Item obat sudah diterima"></i> Sudah Diterima</span>';
            }
        })    
        ->editcolumn('total', function($data) {
            //if($data->total == null OR $data->total == 0) {
                $total = $data->detail_transfer_total[0]->total;
            //} else {
              //  $total = $data->total;
           // }

            return 'Rp '.number_format($total, 2);
        })  
        ->addcolumn('action', function($data) use($hak_akses){
            $btn = '<div class="btn-group">';
            $btn .= '<a href="'.url('/transfer_outlet/konfirm/'.$data->id).'" title="Konfirmasi Barang" class="btn btn-info btn-sm"><span data-toggle="tooltip" data-placement="top" title="Konfirmasi Barang"><i class="fa fa-check"></i> Konfirmasi</span></a>';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['action', 'is_status', 'is_lunas', 'id_apotek_asal', 'id_apotek_tujuan', 'total'])
        ->addIndexColumn()
        ->make(true);  
    }

    public function konfirm($id) {
        $transfer_outlet = TransaksiTO::find($id);
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $apotek_asal = MasterApotek::find($transfer_outlet->id_apotek_asal);
        $inisial_asal = strtolower($apotek_asal->nama_singkat);
        $apoteks = MasterApotek::whereNotIn('id', [$transfer_outlet->id_apotek_asal])->where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $tanggal = date('Y-m-d');

        $detail_transfer_outlets = $transfer_outlet->detail_transfer_outlet;
        if($transfer_outlet->id == 68071) {
            //$detail_transfer_outlets = TransaksiTODetail::where('id_nota', $transfer_outlet->id)->where('is_deleted', 0)->where('is_status', 0)->get();
            //dd($detail_transfer_outlets);
        } 

        $cek_harga = TransaksiTODetail::where('id_nota', $transfer_outlet->id)->where('is_deleted', 0)->where('harga_outlet', 0)->count();

        $var = 0;
        if($transfer_outlet->id_apotek_tujuan != session('id_apotek_active')) {
            session()->flash('error', 'Anda tidak mempunyai hak akses untuk melakukan konfirmasi pada nota ini!');
            return redirect('transfer_outlet/konfirmasi_barang')->with('message', 'Anda tidak mempunyai hak akses untuk melakukan konfirmasi pada nota ini!');
        }
        return view('transfer_outlet.konfirm')->with(compact('transfer_outlet', 'apoteks', 'detail_transfer_outlets', 'var', 'apotek', 'inisial', 'apotek_asal', 'inisial_asal', 'cek_harga'));
    }

    public function konfirm_update(Request $request, $id) {
        if(session('id_tahun_active') == date('Y')) {
        } else {
            return view('page_not_authorized');
        }

        //echo $id; exit();
        ini_set('memory_limit', '-1'); 
        DB::beginTransaction(); 
        try{
            $transfer_outlet = TransaksiTO::find($id);
            $detail_transfer_outlets = $request->detail_transfer_outlet;
            $is_status = $request->is_status;
            $apotek1 = MasterApotek::find($transfer_outlet->id_apotek_asal);
            $inisial1 = strtolower($apotek1->nama_singkat);

            $apotek2 = MasterApotek::find($transfer_outlet->id_apotek_tujuan);
            $inisial2 = strtolower($apotek2->nama_singkat);

            if($transfer_outlet->id == 73943) {
                $detail_transfer_outlets = TransaksiTODetail::where('is_deleted', 0)->where('id_nota', $transfer_outlet->id)->where('is_status', 0)->get();
                $i = 0;
                foreach ($detail_transfer_outlets as $key => $detail_transfer_outlet) { 
                    
                    $obj = TransaksiTODetail::find($detail_transfer_outlet->id);
                    $obj->is_status = $is_status;
                    $obj->konfirm_at = date('Y-m-d H:i:s');
                    $obj->konfirm_by = Auth::user()->id;
                    if($obj->save()){
                        
                    } else {
                        DB::rollback();
                        session()->flash('error', 'Gagal mengkonfirmasi data transfer masuk!');
                        return redirect('transfer_outlet/konfirm/'.$id)->with('message', 'Gagal mengkonfirmasi data transfer masuk!');
                    }

                    // jika barang diterima buat histori
                    if($obj->is_status == 1) {
                        // turn off -> because add konfirmasi transfer barang
                        $stok_before2 = DB::table('tb_m_stok_harga_'.$inisial2)->where('id_obat', $obj->id_obat)->first();
                        $outlet1 = DB::table('tb_m_stok_harga_'.$inisial1)->where('id_obat', $obj->id_obat)->first();

                        $stok_now2 = $stok_before2->stok_akhir+$obj->jumlah;

                        # update ke table stok harga
                        /*$stok_harga = new MasterStokHargaTujuan;
                        $stok_harga->setTable('tb_m_stok_harga_'.$inisial2);
                        $stok_harga->where('id_obat', $obj->id_obat)->first();
                        $stok_harga->stok_awal = $stok_before2->stok_akhir;
                        $stok_harga->stok_akhir = $stok_now2;
                        $stok_harga->updated_at = date('Y-m-d H:i:s');
                        $stok_harga->updated_by = Auth::user()->id;
                        dd($stok_harga);
                        if($stok_harga->save()) {
                        } else {
                            DB::rollback();
                            session()->flash('error', 'Gagal update data ke master stok!');
                            return redirect('transfer_outlet/konfirm/'.$id)->with('message', 'Gagal update data ke master stok!');
                        }*/

                        DB::table('tb_m_stok_harga_'.$inisial2)->where('id_obat', $obj->id_obat)->update(['stok_awal'=> $stok_before2->stok_akhir, 'stok_akhir'=> $stok_now2, 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => Auth::user()->id]);

                        # create histori
                        $histori_stok = new HistoriStokTujuan;
                        $histori_stok->setTable('tb_histori_stok_'.$inisial2);
                        $histori_stok->where('id_obat', $obj->id_obat)->where('jumlah', $obj->jumlah)->where('id_jenis_transaksi', 3)->where('id_transaksi', $obj->id)->first();
                        if(empty($histori_stok)) {
                            $histori_stok = new HistoriStokTujuan;
                            $histori_stok->setTable('tb_histori_stok_'.$inisial2);
                        }
                        $histori_stok->id_obat = $obj->id_obat;
                        $histori_stok->jumlah = $obj->jumlah;
                        $histori_stok->stok_awal = $stok_before2->stok_akhir;
                        $histori_stok->stok_akhir = $stok_now2;
                        $histori_stok->id_jenis_transaksi = 3; //transfer masuk
                        $histori_stok->id_transaksi = $obj->id;
                        $histori_stok->batch = null;
                        $histori_stok->ed = null;
                        $histori_stok->sisa_stok = $obj->jumlah;
                        $histori_stok->hb_ppn = $obj->harga_outlet;
                        $histori_stok->keterangan = 'TO Masuk pada IDdet.'.$obj->id.' sejumlah '.$obj->jumlah;
                        $histori_stok->created_at = $transfer_outlet->created_at;
                        $histori_stok->created_by = $transfer_outlet->created_by;

                        if($histori_stok->save()) {
                        } else {
                            DB::rollback();
                            session()->flash('error', 'Gagal create histori stok!');
                            return redirect('transfer_outlet/konfirm/'.$id)->with('message', 'Gagal create histori stok!');
                        }
                      
                        /*DB::table('tb_histori_stok_'.$inisial2)->insert([
                            'id_obat' => $obj->id_obat,
                            'jumlah' => $obj->jumlah,
                            'stok_awal' => $stok_before2->stok_akhir,
                            'stok_akhir' => $stok_now2,
                            'id_jenis_transaksi' => 3, //transfer masuk
                            'id_transaksi' => $obj->id,
                            'batch' => null,
                            'ed' => null,
                            'sisa_stok' => $obj->jumlah,
                            'hb_ppn' => $obj->harga_outlet,
                            'keterangan' => 'TO Masuk pada IDdet.'.$obj->id.' sejumlah '.$obj->jumlah,
                            'created_at' => $transfer_outlet->created_at,
                            'created_by' => $transfer_outlet->created_by
                        ]);*/

                        if($stok_before2->harga_beli_ppn != $obj->harga_outlet) {
                            $data_histori_ = array('id_obat' => $obj->id_obat, 'harga_beli_awal' => $stok_before2->harga_beli, 'harga_beli_akhir' => $outlet1->harga_beli, 'harga_jual_awal' => $stok_before2->harga_jual, 'harga_jual_akhir' => $outlet1->harga_jual, 'created_by' => Auth::id(), 'created_at' => date('Y-m-d H:i:s'));

                            // update harga obat
                            DB::table('tb_histori_harga_'.$inisial2.'')->insert($data_histori_);
                            DB::table('tb_m_stok_harga_'.$inisial2)->where('id_obat', $obj->id_obat)->update(['updated_at' => date('Y-m-d H:i:s'), 'harga_beli' => $outlet1->harga_beli, 'harga_beli_ppn' => $obj->harga_outlet, 'updated_by' => Auth::user()->id]);
                        }
                    }
                    $i++;
                }

                if($i > 0) {
                    $total_to = TransaksiTODetail::where('id_nota', $id)->where('is_deleted', 0)->count();
                    $check_to_diterima = TransaksiTODetail::where('id_nota', $id)->where('is_deleted', 0)->where('is_status', '!=',  0)->count();
                    
                    if($total_to == $check_to_diterima) {
                        $transfer_outlet->is_status = 1;
                        $transfer_outlet->complete_at = date('Y-m-d H:i:s');
                        $transfer_outlet->complete_by = Auth::user()->id;
                        if($transfer_outlet->save()){
                        } else {
                            DB::rollback();
                            session()->flash('error', 'Gagal mengkonfirmasi data transfer masuk!');
                            return redirect('transfer_outlet/konfirm/'.$id)->with('message', 'Gagal mengkonfirmasi data transfer masuk!');
                        }
                    } 

                    DB::commit();
                    session()->flash('success', 'Sukses mengkonfirmasi data transfer masuk!');
                    return redirect('transfer_outlet/konfirm/'.$id)->with('message', 'Sukses mengkonfirmasi data transfer masuk!');
                } else {
                    DB::rollback();
                    session()->flash('error', 'Gagal mengkonfirmasi data transfer masuk!');
                    return redirect('transfer_outlet/konfirm/'.$id)->with('message', 'Gagal mengkonfirmasi data transfer masuk!');
                }
                dd("yuhu");
            } else {
                $i = 0;
                foreach ($detail_transfer_outlets as $key => $detail_transfer_outlet) { 
                    //dd($detail_transfer_outlet);
                    if(isset($detail_transfer_outlet['record'])) {
                        $obj = TransaksiTODetail::find($detail_transfer_outlet['id']);
                        $obj->is_status = $is_status;
                        $obj->konfirm_at = date('Y-m-d H:i:s');
                        $obj->konfirm_by = Auth::user()->id;
                        if($obj->save()){
                            
                        } else {
                            DB::rollback();
                            session()->flash('error', 'Gagal mengkonfirmasi data transfer masuk!');
                            return redirect('transfer_outlet/konfirm/'.$id)->with('message', 'Gagal mengkonfirmasi data transfer masuk!');
                        }

                        // jika barang diterima buat histori
                        if($obj->is_status == 1) {
                            // turn off -> because add konfirmasi transfer barang
                            $stok_before2 = DB::table('tb_m_stok_harga_'.$inisial2)->where('id_obat', $obj->id_obat)->first();
                            $outlet1 = DB::table('tb_m_stok_harga_'.$inisial1)->where('id_obat', $obj->id_obat)->first();

                            $stok_now2 = $stok_before2->stok_akhir+$obj->jumlah;

                            # update ke table stok harga
                            /*$stok_harga = new MasterStokHargaTujuan;
                            $stok_harga->setTable('tb_m_stok_harga_'.$inisial2);
                            $stok_harga->where('id_obat', $obj->id_obat)->first();
                            $stok_harga->stok_awal = $stok_before2->stok_akhir;
                            $stok_harga->stok_akhir = $stok_now2;
                            $stok_harga->updated_at = date('Y-m-d H:i:s');
                            $stok_harga->updated_by = Auth::user()->id;
                            dd($stok_harga);
                            if($stok_harga->save()) {
                            } else {
                                DB::rollback();
                                session()->flash('error', 'Gagal update data ke master stok!');
                                return redirect('transfer_outlet/konfirm/'.$id)->with('message', 'Gagal update data ke master stok!');
                            }*/

                            DB::table('tb_m_stok_harga_'.$inisial2)->where('id_obat', $obj->id_obat)->update(['stok_awal'=> $stok_before2->stok_akhir, 'stok_akhir'=> $stok_now2, 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => Auth::user()->id]);

                            # create histori
                            $histori_stok = new HistoriStokTujuan;
                            $histori_stok->setTable('tb_histori_stok_'.$inisial2);
                            $histori_stok->where('id_obat', $obj->id_obat)->where('jumlah', $obj->jumlah)->where('id_jenis_transaksi', 3)->where('id_transaksi', $obj->id)->first();
                            if(empty($histori_stok)) {
                                $histori_stok = new HistoriStokTujuan;
                                $histori_stok->setTable('tb_histori_stok_'.$inisial2);
                            }
                            $histori_stok->id_obat = $obj->id_obat;
                            $histori_stok->jumlah = $obj->jumlah;
                            $histori_stok->stok_awal = $stok_before2->stok_akhir;
                            $histori_stok->stok_akhir = $stok_now2;
                            $histori_stok->id_jenis_transaksi = 3; //transfer masuk
                            $histori_stok->id_transaksi = $obj->id;
                            $histori_stok->batch = null;
                            $histori_stok->ed = null;
                            $histori_stok->sisa_stok = $obj->jumlah;
                            $histori_stok->hb_ppn = $obj->harga_outlet;
                            $histori_stok->keterangan = 'TO Masuk pada IDdet.'.$obj->id.' sejumlah '.$obj->jumlah;
                            $histori_stok->created_at = $transfer_outlet->created_at;
                            $histori_stok->created_by = $transfer_outlet->created_by;

                            if($histori_stok->save()) {
                            } else {
                                DB::rollback();
                                session()->flash('error', 'Gagal create histori stok!');
                                return redirect('transfer_outlet/konfirm/'.$id)->with('message', 'Gagal create histori stok!');
                            }
                          
                            /*DB::table('tb_histori_stok_'.$inisial2)->insert([
                                'id_obat' => $obj->id_obat,
                                'jumlah' => $obj->jumlah,
                                'stok_awal' => $stok_before2->stok_akhir,
                                'stok_akhir' => $stok_now2,
                                'id_jenis_transaksi' => 3, //transfer masuk
                                'id_transaksi' => $obj->id,
                                'batch' => null,
                                'ed' => null,
                                'sisa_stok' => $obj->jumlah,
                                'hb_ppn' => $obj->harga_outlet,
                                'keterangan' => 'TO Masuk pada IDdet.'.$obj->id.' sejumlah '.$obj->jumlah,
                                'created_at' => $transfer_outlet->created_at,
                                'created_by' => $transfer_outlet->created_by
                            ]);*/

                            if($stok_before2->harga_beli_ppn != $obj->harga_outlet) {
                                $data_histori_ = array('id_obat' => $obj->id_obat, 'harga_beli_awal' => $stok_before2->harga_beli, 'harga_beli_akhir' => $outlet1->harga_beli, 'harga_jual_awal' => $stok_before2->harga_jual, 'harga_jual_akhir' => $outlet1->harga_jual, 'created_by' => Auth::id(), 'created_at' => date('Y-m-d H:i:s'));

                                // update harga obat
                                DB::table('tb_histori_harga_'.$inisial2.'')->insert($data_histori_);
                                DB::table('tb_m_stok_harga_'.$inisial2)->where('id_obat', $obj->id_obat)->update(['updated_at' => date('Y-m-d H:i:s'), 'harga_beli' => $outlet1->harga_beli, 'harga_beli_ppn' => $obj->harga_outlet, 'updated_by' => Auth::user()->id]);
                            }
                        }
                        $i++;
                    }
                }

                if($i > 0) {
                    $total_to = TransaksiTODetail::where('id_nota', $id)->where('is_deleted', 0)->count();
                    $check_to_diterima = TransaksiTODetail::where('id_nota', $id)->where('is_deleted', 0)->where('is_status', '!=',  0)->count();
                    
                    if($total_to == $check_to_diterima) {
                        $transfer_outlet->is_status = 1;
                        $transfer_outlet->complete_at = date('Y-m-d H:i:s');
                        $transfer_outlet->complete_by = Auth::user()->id;
                        if($transfer_outlet->save()){
                        } else {
                            DB::rollback();
                            session()->flash('error', 'Gagal mengkonfirmasi data transfer masuk!');
                            return redirect('transfer_outlet/konfirm/'.$id)->with('message', 'Gagal mengkonfirmasi data transfer masuk!');
                        }
                    } 

                    DB::commit();
                    session()->flash('success', 'Sukses mengkonfirmasi data transfer masuk!');
                    return redirect('transfer_outlet/konfirm/'.$id)->with('message', 'Sukses mengkonfirmasi data transfer masuk!');
                } else {
                    DB::rollback();
                    session()->flash('error', 'Gagal mengkonfirmasi data transfer masuk!');
                    return redirect('transfer_outlet/konfirm/'.$id)->with('message', 'Gagal mengkonfirmasi data transfer masuk!');
                }
            }

            

        }catch(\Exception $e){
            DB::rollback();
            session()->flash('error', $e->getMessage());
            return redirect('transfer_outlet/konfirm/'.$id);
        }
    }

    function cari_info(Request $request) {
        $tgl_awal = $request->tgl_awal.' 23:59:59';
        $tgl_awal = date('Y-m-d H:i:s', strtotime($tgl_awal));

        $tgl_akhir = $request->tgl_akhir.' 23:59:59';
        $tgl_akhir = date('Y-m-d H:i:s', strtotime($tgl_akhir));

        $apoteks = MasterApotek::where('id_group_apotek', Auth::user()->id_group_apotek)->where('is_deleted', 0)->whereNotIn('id', [session('id_apotek_active')])->get();
        $transfer_masuk = '<table class="table m-0">
                                    <thead>
                                        <tr>
                                            <th width="70%" class="text-center">Apotek</th>
                                            <th width="30%" class="text-center">Total Konfirm</th>
                                        </tr>
                                    </thead>
                                    <tbody>';
        
        if(session('id_tahun_active') == date('Y')) {
            $detTable = 'tb_detail_nota_transfer_outlet';
            $table = 'tb_nota_transfer_outlet';
        } else {
            $detTable = 'tb_detail_nota_transfer_outlet_histori';
            $table = 'tb_nota_transfer_outlet_histori';
            $hak_akses = 0;
        }

        foreach ($apoteks as $key => $val) {
            $data = TransaksiTODetail::select([
                                DB::raw("SUM($detTable.jumlah * $detTable.harga_outlet) AS total")
                            ])
                            ->join("$table", "$table.id", '=', "$detTable.id_nota")
                            ->where(function($query) use($request, $val, $tgl_awal, $tgl_akhir, $table, $detTable){
                                $query->where("$table.is_deleted",'=','0');
                                $query->where("$detTable.is_deleted",'=','0');
                                $query->where("$detTable.is_status",'=','1');
                                $query->where("$table.id_apotek_nota",'=', $val->id);
                                $query->where("$table.id_apotek_tujuan",'=',session('id_apotek_active'));
                                $query->where("$table.id",'LIKE',($request->id > 0 ? $request->id : '%'.$request->id.'%'));
                                if (!empty($request->tgl_awal) && !empty($request->tgl_akhir)) {
                                    $query->where("$table.created_at",'>=', $tgl_awal);
                                    $query->where("$table.created_at",'<=', $tgl_akhir);
                                }
                            })
                            ->first();
            $total = "Rp ".number_format($data->total,2);
            $transfer_masuk .= '                           
                                        <tr>
                                            <td width="70%"><span class="text-info">'.$val->nama_panjang.'</span></td>
                                            <td width="30%">'.$total.'</td>
                                        </tr>';
        }
        $transfer_masuk .= '                      
                                    </tbody>
                                </table>';


        $transfer_keluar = '<table class="table m-0">
                                    <thead>
                                        <tr>
                                            <th width="50%" class="text-center">Apotek</th>
                                            <th width="25%" class="text-center">Total</th>
                                            <th width="25%" class="text-center">Total Konfirm</th>
                                        </tr>
                                    </thead>
                                    <tbody>';
        

        foreach ($apoteks as $key => $val) {
            $data = TransaksiTODetail::select([
                                DB::raw("SUM($detTable.jumlah * $detTable.harga_outlet) AS total")
                            ])
                            ->join("$table", "$table.id", '=', "$detTable.id_nota")
                            ->where(function($query) use($request, $val, $tgl_awal, $tgl_akhir, $table, $detTable){
                                $query->where("$table.is_deleted",'=','0');
                                $query->where("$detTable.is_deleted",'=','0');
                                //$query->where("$detTable.is_status",'=','0');
                                $query->where("$table.id_apotek_tujuan",'=', $val->id);
                                $query->where("$table.id_apotek_nota",'=',session('id_apotek_active'));
                                $query->where("$table.id",'LIKE',($request->id > 0 ? $request->id : '%'.$request->id.'%'));
                                if (!empty($request->tgl_awal) && !empty($request->tgl_akhir)) {
                                    $query->where("$table.created_at",'>=', $tgl_awal);
                                    $query->where("$table.created_at",'<=', $tgl_akhir);
                                }
                            })
                            ->first();

            $data_konfirm = TransaksiTODetail::select([
                                DB::raw("SUM($detTable.jumlah * $detTable.harga_outlet) AS total")
                            ])
                            ->join("$table", "$table.id", '=', "$detTable.id_nota")
                            ->where(function($query) use($request, $val, $tgl_awal, $tgl_akhir, $table, $detTable){
                                $query->where("$table.is_deleted",'=','0');
                                $query->where("$detTable.is_deleted",'=','0');
                                $query->where("$detTable.is_status",'=','1');
                                $query->where("$table.id_apotek_tujuan",'=', $val->id);
                                $query->where("$table.id_apotek_nota",'=',session('id_apotek_active'));
                                $query->where("$table.id",'LIKE',($request->id > 0 ? $request->id : '%'.$request->id.'%'));
                                if (!empty($request->tgl_awal) && !empty($request->tgl_akhir)) {
                                    $query->where("$table.created_at",'>=', $tgl_awal);
                                    $query->where("$table.created_at",'<=', $tgl_akhir);
                                }
                            })
                            ->first();

            $total = "Rp ".number_format($data->total,2);
            $total_konfirm = "Rp ".number_format($data_konfirm->total,2);
            $transfer_keluar .= '                           
                                        <tr>
                                            <td width="50%"><span class="text-info">'.$val->nama_panjang.'</span></td>
                                            <td width="25%">'.$total.'</td>
                                            <td width="25%">'.$total_konfirm.'</td>
                                        </tr>';
        }
        $transfer_keluar .= '                      
                                    </tbody>
                                </table>';

        $arr_ = array(
                    'data_transfer_masuk' => $transfer_masuk, 
                    'data_transfer_keluar' => $transfer_keluar
                );

        return response()->json($arr_); 
    }

    public function create_margin() {
        /*if(Auth::user()->id == 1) {
        } else {
            echo "under maintenance"; exit();
        }*/
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $apoteks = MasterApotek::whereNotIn('id', [$apotek->id])->where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $tanggal = date('Y-m-d');
        $transfer_outlet = new TransaksiTO;
        $detail_transfer_outlets = new TransaksiTODetail;
        $var = 1;
        return view('transfer_outlet.create_margin')->with(compact('transfer_outlet', 'apoteks', 'detail_transfer_outlets', 'var', 'apotek', 'inisial'));
    }

    public function invoice($id) {
        $date_now = date('Y-m-d');
        $id_ = Crypt::decrypt($id);

        $transfer_outlet = TransaksiTO::find($id_);
        $detail_transfer_outlets = $transfer_outlet->detail_transfer_outlet;

        $apotek1 = MasterApotek::find($transfer_outlet->id_apotek_asal);
        $inisial1 = strtolower($apotek1->nama_singkat);

        $apotek2 = MasterApotek::find($transfer_outlet->id_apotek_tujuan);
        $inisial2 = strtolower($apotek2->nama_singkat);

        return view('transfer_outlet._invoice')->with(compact('date_now', 'transfer_outlet', 'apotek1', 'apotek2', 'detail_transfer_outlets', 'id'));
    }

    public function invoiceprint($id) {
        $date_now = date('Y-m-d');
        $id_ = Crypt::decrypt($id);

        $transfer_outlet = TransaksiTO::find($id_);
        $detail_transfer_outlets = $transfer_outlet->detail_transfer_outlet;

        $apotek1 = MasterApotek::find($transfer_outlet->id_apotek_asal);
        $inisial1 = strtolower($apotek1->nama_singkat);

        $apotek2 = MasterApotek::find($transfer_outlet->id_apotek_tujuan);
        $inisial2 = strtolower($apotek2->nama_singkat);

        return view('transfer_outlet._invoiceprint')->with(compact('date_now', 'transfer_outlet', 'apotek1', 'apotek2', 'detail_transfer_outlets', 'id'));
    }

    public function generatepdf($id) {
        $date_now = date('Y-m-d');
        $id_ = Crypt::decrypt($id);

        $transfer_outlet = TransaksiTO::find($id_);
        $detail_transfer_outlets = $transfer_outlet->detail_transfer_outlet;

        $apotek1 = MasterApotek::find($transfer_outlet->id_apotek_asal);
        $inisial1 = strtolower($apotek1->nama_singkat);

        $apotek2 = MasterApotek::find($transfer_outlet->id_apotek_tujuan);
        $inisial2 = strtolower($apotek2->nama_singkat);

        $nama_file_ = 'pdf_to_'.$inisial1.'_'.$date_now;
        $pdf = PDF::loadHTML(view('transfer_outlet._generatepdf')->with(compact('date_now', 'transfer_outlet', 'apotek1', 'apotek2', 'detail_transfer_outlets', 'id')));
        
        $pdf->setOptions(array(
            'dpi' => 300,
            'page-size'=> 'Folio',  
        ));
        return $pdf->inline($nama_file_.'.pdf');
    }

    public function pindah_transfer() {
        $all_stok = DB::table('tb_m_stok_harga_ho')->where('stok_akhir', '!=', 0)->where('is_transfer', 0)->limit(20)->get();
        
        $transfer_outlet = new TransaksiTO;
        $transfer_outlet->id_apotek_nota = 9;
        $transfer_outlet->tgl_nota = date('Y-m-d');
        $transfer_outlet->id_apotek_asal = 9;
        $transfer_outlet->id_apotek_tujuan = 11;
        $transfer_outlet->total = 0;
        $transfer_outlet->keterangan = 'transfer by sistem';
        $transfer_outlet->created_at = date('Y-m-d H:i:s');
        $transfer_outlet->created_by = Auth::user()->id;
        $transfer_outlet->is_status = 0;
        $transfer_outlet->save();

        $total = 0;
        foreach ($all_stok as $key => $val) {
            $det_ = new TransaksiTODetail;
            $det_->id_nota = $transfer_outlet->id;
            $det_->id_obat = $val->id_obat;
            $det_->harga_outlet = $val->harga_beli_ppn+(5/100*$val->harga_beli_ppn);
            $det_->jumlah = $val->stok_akhir;
            $det_->total = $det_->jumlah*$det_->harga_outlet;
            $det_->is_status = 0;
            $det_->created_at = date('Y-m-d H:i:s');
            $det_->created_by = Auth::user()->id;
            $det_->save();

            $stok_before = DB::table('tb_m_stok_harga_ho')->where('id_obat', $val->id_obat)->first();
            $stok_now = $stok_before->stok_akhir-$val->jumlah;

            # update ke table stok harga
            DB::table('tb_m_stok_harga_ho')->where('id_obat', $val->id_obat)->update(['stok_awal'=> $stok_before->stok_akhir, 'stok_akhir'=> $stok_now, 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => Auth::user()->id, 'is_transfer' => '1']);

            # create histori
            DB::table('tb_histori_stok_ho')->insert([
                'id_obat' => $val->id_obat,
                'jumlah' => $val->jumlah,
                'stok_awal' => $stok_before->stok_akhir,
                'stok_akhir' => $stok_now,
                'id_jenis_transaksi' => 4, //transfer keluar
                'id_transaksi' => $det_->id,
                'batch' => null,
                'ed' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => Auth::user()->id
            ]);

            $total  = $total+$det_->total;
        }

        $transfer_outlet->total = $total;
        $transfer_outlet->save();

        echo "finish";
    }

    public function permintaan_transfer() {
       $transfers = TransaksiTransfer::select('tb_nota_transfer.*')
                                ->where('tb_nota_transfer.is_deleted', 0)
                                ->where('tb_nota_transfer.is_status', 0)
                                ->where('tb_nota_transfer.id_apotek_transfer', session('id_apotek_active'))
                                ->get();

                
        $apoteks = MasterApotek::where('is_deleted', 0)->pluck('nama_singkat','id');
        $transfer_outlet = new TransaksiTO;
       // $pembelian->

        return view('permintaan_transfer.create')->with(compact('apoteks', 'transfers', 'transfer_outlet'));
    }

    public function list_detail_transfer_outlet(Request $request) {
        # get total to
        $id = $request->id;
        if(is_null($id)) {
            $total_transfer = 0;
            $is_sign = 0;
        } else {
            $transfer_outlet = TransaksiTO::find($id);

            $total_transfer = $transfer_outlet->detail_transfer_total[0]->total;
            if($total_transfer == "" || $total_transfer == null) {
                $total_transfer = 0;
            }

            $is_sign = $transfer_outlet->is_sign;
        }

        $last_so = SettingStokOpnam::where('id_apotek', session('id_apotek_active'))->where('step', '>', 1)->orderBy('id', 'DESC')->first();

        DB::statement(DB::raw('set @rownum = 0'));
        $data = TransaksiTODetail::select([
                DB::raw('@rownum  := @rownum  + 1 AS no'),
                'tb_detail_nota_transfer_outlet.*', 
        ])
        ->where(function($query) use($request){
            $query->where('tb_detail_nota_transfer_outlet.is_deleted','=','0');
            if(is_null($request->id)) {
                $query->where('tb_detail_nota_transfer_outlet.id_nota','=',0);
            } else {
                $query->where('tb_detail_nota_transfer_outlet.id_nota','=',$request->id);
            }
            
        })
        ->orderBy('tb_detail_nota_transfer_outlet.id', 'ASC');
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request){
            $query->where(function($query) use($request){
                $query->orwhere('tb_detail_nota_transfer_outlet.id','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->editcolumn('action', function($data) use($request, $is_sign, $last_so){
            $btn ='';
        
            if($is_sign == 1) {
                # jika sudah di ttd maka tidak muncul
            } else {
                if(!empty($last_so)) {
                    $xxx = $last_so->tgl_so.' 23:59:59';
                    if($data->created_at >= $xxx) {
                        if($data->nota->is_status != 1) {
                            $btn .= '<span class="btn btn-danger btn-xs" onClick="delete_item('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-trash"></i></span>';
                        }
                    }
                } else {
                    if($data->nota->is_status != 1) {
                        $btn .= '<span class="btn btn-danger btn-xs" onClick="delete_item('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-trash"></i></span>';
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
        ->editcolumn('harga_outlet', function($data) use($request){
            return "Rp ".number_format($data->harga_outlet,0);
        })   
        
        ->editcolumn('total', function($data) use($request){
            $to = $data->nota;
            $total_transfer = $data->jumlah * $data->harga_outlet;

            return "Rp ".number_format($total_transfer,0);
        })  
        ->with([
            "total_transfer" => $total_transfer,
            "total_transfer_format" => "Rp ".number_format($total_transfer,0)
        ])   
        ->rawColumns(['action', 'nama_barang', 'harga_outlet', 'total'])
        ->addIndexColumn()
        ->make(true);  
    }


    public function AddItem(Request $request) {
        if(session('id_tahun_active') == date('Y')) {
        } else {
            return view('page_not_authorized');
        }
        DB::beginTransaction(); 
        try{
            $transfer_outlet = new TransaksiTO;
            $transfer_outlet->fill($request->except('_token'));

            $detail_transfer_outlets = array();
            $detail_transfer_outlets[] = array(
                'id' => null,
                'id_obat' => $request->id_obat, 
                'harga_outlet' => $request->harga_outlet,
                'jumlah' => $request->jumlah,
                'is_margin' => $request->is_margin,
                'persen' => $request->persen,
                'id_detail_transfer' => $request->id_detail_transfer
            );

            $validator = $transfer_outlet->validate();
            if($validator->fails()){
                DB::rollback();
                echo json_encode(array('status' => 0, 'message' => 'Silakan lengkapi apotek tujuan dan keterangan'));
            } else {
                $tanggal = date('Y-m-d');

                $apotek = MasterApotek::find(session('id_apotek_active'));
                $inisial = strtolower($apotek->nama_singkat);

                $result = $transfer_outlet->save_from_array($detail_transfer_outlets, 1);
                if($result['status']) {
                    DB::commit();
                    echo json_encode(array('status' => 1, 'id' => $transfer_outlet->id, 'message' => $result['message']));
                } else {
                    DB::rollback();
                    echo json_encode(array('status' => 0, 'message' => $result['message'], 'message1' => 'Error, silakan cek kembali data yang diinputkan'));
                }
            }
        }catch(\Exception $e){
            DB::rollback();
            echo json_encode(array('status' => 0, 'message' => $e->getMessage()));
        }
    }

    public function UpdateItem(Request $request) {
        if(session('id_tahun_active') == date('Y')) {
        } else {
            return view('page_not_authorized');
        }
        DB::beginTransaction(); 
        try{
            $id = $request->id;
            $transfer_outlet = TransaksiTO::find($id);
            if($transfer_outlet->is_deleted != 1) {   
                $transfer_outlet->fill($request->except('_token'));

                $detail_transfer_outlets = array();
                $detail_transfer_outlets[] = array(
                    'id' => null,
                    'id_obat' => $request->id_obat, 
                    'harga_outlet' => $request->harga_outlet,
                    'jumlah' => $request->jumlah,
                    'is_margin' => $request->is_margin,
                    'persen' => $request->persen,
                    'id_detail_transfer' => $request->id_detail_transfer
                );

                $tanggal = date('Y-m-d');

                $apotek = MasterApotek::find(session('id_apotek_active'));
                $inisial = strtolower($apotek->nama_singkat);
                
                $result = $transfer_outlet->save_from_array($detail_transfer_outlets, 2);
                if($result['status']) {
                    DB::commit();
                    echo json_encode(array('status' => 1, 'id' => $transfer_outlet->id, 'message' => $result['message']));
                } else {
                    DB::rollback();
                    echo json_encode(array('status' => 0, 'message' => $result['message'], 'message1' => 'Error, silakan cek kembali data yang diinputkan'));
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
        if(session('id_tahun_active') == date('Y')) {
        } else {
            return view('page_not_authorized');
        }
        # yang bisa didelete adalah | yang belum dikonfirm
        DB::beginTransaction(); 
        try{
            $detail_transfer_outlet = TransaksiTODetail::find($id);
            $detail_transfer_outlet->is_deleted = 1;
            $detail_transfer_outlet->deleted_at = date('Y-m-d H:i:s');
            $detail_transfer_outlet->deleted_by = Auth::user()->id;
           
            # crete histori stok barang
            $apotek = MasterApotek::find(session('id_apotek_active'));
            $inisial = strtolower($apotek->nama_singkat);
            $stok_before = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $detail_transfer_outlet->id_obat)->first(); 
            $stok_now = $stok_before->stok_akhir+$detail_transfer_outlet->jumlah;

            /*$arrayupdate = array(
                'stok_awal'=> $stok_before->stok_akhir, 
                'stok_akhir'=> $stok_now, 
                'updated_at' => date('Y-m-d H:i:s'), 
                'updated_by' => Auth::user()->id
            );*/

            # update ke table stok harga
            $stok_harga = MasterStokHarga::where('id_obat', $detail_transfer_outlet->id_obat)->first();
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
                'id_obat' => $detail_transfer_outlet->id_obat,
                'jumlah' => $detail_transfer_outlet->jumlah,
                'stok_awal' => $stok_before->stok_akhir,
                'stok_akhir' => $stok_now,
                'id_jenis_transaksi' => 17, //hapus to keluar
                'id_transaksi' => $detail_transfer_outlet->id,
                'batch' => null,
                'ed' => null,
                'sisa_stok' => null,
                'hb_ppn' => $detail_transfer_outlet->hb_ppn,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => Auth::user()->id
            );*/

            # create histori
            $histori_stok = HistoriStok::where('id_obat', $detail_transfer_outlet->id_obat)->where('jumlah', $detail_transfer_outlet->jumlah)->where('id_jenis_transaksi', 17)->where('id_transaksi', $detail_transfer_outlet->id)->first();
            if(empty($histori_stok)) {
                $histori_stok = new HistoriStok;
            }
            $histori_stok->id_obat = $detail_transfer_outlet->id_obat;
            $histori_stok->jumlah = $detail_transfer_outlet->jumlah;
            $histori_stok->stok_awal = $stok_before->stok_akhir;
            $histori_stok->stok_akhir = $stok_now;
            $histori_stok->id_jenis_transaksi = 17; //hapus to keluar
            $histori_stok->id_transaksi = $detail_transfer_outlet->id;
            $histori_stok->batch = null;
            $histori_stok->ed = null;
            $histori_stok->sisa_stok = null;
            $histori_stok->hb_ppn = $detail_transfer_outlet->hb_ppn;
            $histori_stok->created_at = date('Y-m-d H:i:s');
            $histori_stok->created_by = Auth::user()->id;
            if($histori_stok->save()) {
            } else {
                DB::rollback();
                echo json_encode(array('status' => 0));
            }

            # update stok aktif 
            $histori_stok_details = json_decode($detail_transfer_outlet->id_histori_stok_detail);
            if(count($histori_stok_details) == 0) {
                DB::rollback();
                echo json_encode(array('status' => 0));
            } else {
                foreach ($histori_stok_details as $y => $hist) {
                    $cekHistori = HistoriStok::find($hist->id_histori_stok);
                    $keterangan = $cekHistori->keterangan.', Hapus TO pada IDdet.'.$detail_transfer_outlet->id.' sejumlah '.$hist->jumlah;
                    $cekHistori->sisa_stok = $cekHistori->sisa_stok + $hist->jumlah;
                    $cekHistori->keterangan = $keterangan;
                    if($cekHistori->save()) {
                    } else {
                        DB::rollback();
                        echo json_encode(array('status' => 0));
                    }
                }
            }
        
            if($detail_transfer_outlet->save()) {
                # cek apakah masih ada item pada nota yang sama
                $jum_details = TransaksiTODetail::where('is_deleted', 0)->where('id_nota', $detail_transfer_outlet->id_nota)->count();
                $is_sisa = 1;
                if($jum_details == 0) {
                    $transfer_outlet = TransaksiTO::find($detail_transfer_outlet->id_nota);
                    $transfer_outlet->is_deleted = 1;
                    $transfer_outlet->deleted_at = date('Y-m-d H:i:s');
                    $transfer_outlet->deleted_by = Auth::user()->id;
                    if($transfer_outlet->save()) {
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
        if(session('id_tahun_active') == date('Y')) {
        } else {
            return view('page_not_authorized');
        }
        DB::beginTransaction(); 
        try{
            $apotek = MasterApotek::find(session('id_apotek_active'));
            $inisial = strtolower($apotek->nama_singkat);
            $to = TransaksiTO::find($id);
            $to->is_deleted = 1;
            $to->deleted_at = date('Y-m-d H:i:s');
            $to->deleted_by = Auth::user()->id;

            $detail_transfer_outlets = TransaksiTODetail::where('id_nota', $to->id)->where('is_deleted', 0)->get();
            foreach ($detail_transfer_outlets as $key => $detail_transfer_outlet) {
                $detail_transfer_outlet->is_deleted = 1;
                $detail_transfer_outlet->deleted_at = date('Y-m-d H:i:s');
                $detail_transfer_outlet->deleted_by = Auth::user()->id;
               
                # crete histori stok barang
                $apotek = MasterApotek::find(session('id_apotek_active'));
                $inisial = strtolower($apotek->nama_singkat);
                $stok_before = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $detail_transfer_outlet->id_obat)->first(); 
                $stok_now = $stok_before->stok_akhir+$detail_transfer_outlet->jumlah;

                /*$arrayupdate = array(
                    'stok_awal'=> $stok_before->stok_akhir, 
                    'stok_akhir'=> $stok_now, 
                    'updated_at' => date('Y-m-d H:i:s'), 
                    'updated_by' => Auth::user()->id
                );*/

                # update ke table stok harga
                $stok_harga = MasterStokHarga::where('id_obat', $detail_transfer_outlet->id_obat)->first();
                $stok_harga->stok_awa = $stok_before->stok_akhir;
                $stok_harga->stok_akhir = $stok_now;
                $stok_harga->updated_at = date('Y-m-d H:i:s'); 
                $stok_harga->updated_by = Auth::user()->id;
                if($stok_harga->save()) {
                } else {
                    DB::rollback();
                    echo json_encode(array('status' => 0));
                }

               /* $arrayinsert = array(
                    'id_obat' => $detail_transfer_outlet->id_obat,
                    'jumlah' => $detail_transfer_outlet->jumlah,
                    'stok_awal' => $stok_before->stok_akhir,
                    'stok_akhir' => $stok_now,
                    'id_jenis_transaksi' => 17, //hapus to keluar
                    'id_transaksi' => $detail_transfer_outlet->id,
                    'batch' => null,
                    'ed' => null,
                    'sisa_stok' => null,
                    'hb_ppn' => $detail_transfer_outlet->hb_ppn,
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => Auth::user()->id
                );*/

                # create histori
                $histori_stok = HistoriStok::where('id_obat', $detail_transfer_outlet->id_obat)->where('jumlah', $detail_transfer_outlet->jumlah)->where('id_jenis_transaksi', 17)->where('id_transaksi', $detail_transfer_outlet->id)->first();
                if(empty($histori_stok)) {
                    $histori_stok = new HistoriStok;
                }
                $histori_stok->id_obat = $detail_transfer_outlet->id_obat;
                $histori_stok->jumlah = $detail_transfer_outlet->jumlah;
                $histori_stok->stok_awal = $stok_before->stok_akhir;
                $histori_stok->stok_akhir = $stok_now;
                $histori_stok->id_jenis_transaksi = 17; //hapus to keluar
                $histori_stok->id_transaksi = $detail_transfer_outlet->id;
                $histori_stok->batch = null;
                $histori_stok->ed = null;
                $histori_stok->sisa_stok = null;
                $histori_stok->hb_ppn = $detail_transfer_outlet->hb_ppn;
                $histori_stok->created_at = date('Y-m-d H:i:s');
                $histori_stok->created_by = Auth::user()->id;
                if($histori_stok->save()) {
                } else {
                    DB::rollback();
                    echo json_encode(array('status' => 0));
                }

                # update stok aktif 
                $histori_stok_details = json_decode($detail_transfer_outlet->id_histori_stok_detail);
                if(count($histori_stok_details) == 0) {
                    DB::rollback();
                    echo json_encode(array('status' => 0));
                } else {
                    foreach ($histori_stok_details as $y => $hist) {
                        $cekHistori = HistoriStok::find($hist->id_histori_stok);
                        $keterangan = $cekHistori->keterangan.', Hapus TO pada IDdet.'.$detail_transfer_outlet->id.' sejumlah '.$hist->jumlah;
                        $cekHistori->sisa_stok = $cekHistori->sisa_stok + $hist->jumlah;
                        $cekHistori->keterangan = $keterangan;
                        if($cekHistori->save()) {
                        } else {
                            DB::rollback();
                            echo 0;
                        }
                    }
                }

                if($detail_transfer_outlet->save()) {
                } else {
                    DB::rollback();
                    echo 0;
                }
            }
            
            if($to->save()){
                echo 1;
                DB::commit();
            }else{
                echo 0;
                DB::rollback();
            }
        }catch(\Exception $e){
            DB::rollback();
            session()->flash('error', 'Error!');
            return redirect('transfer_outlet');
        }
    }

    public function informasi(){
        return view('transfer_outlet.informasi');
    }

    public function send_sign(Request $request)
    {
        $to = TransaksiTO::find($request->id);
        $to->is_sign = 1;
        $to->sign_by = $request->sign_by;
        $to->sign_at = date('Y-m-d H:i:s');

        if($to->save()){
            echo 1;
        }else{
            echo 0;
        }
    } 

    public function batal_sign(Request $request)
    {
        $to = TransaksiTO::find($request->id);
        $to->is_sign = 0;
        $to->sign_by = null;
        $to->sign_at = null;
        $to->updated_by = Auth::user()->id;
        $to->updated_at = date('Y-m-d H:i:s');

        if($to->save()){
            echo 1;
        }else{
            echo 0;
        }
    } 


    public function konfirm_ulang($id) {
        if(session('id_tahun_active') == date('Y')) {
        } else {
            return view('page_not_authorized');
        }
        //echo $id; exit();
        //ini_set('memory_limit', '-1'); 
        DB::beginTransaction(); 
        try{
            $id = 69227;
            $transfer_outlet = TransaksiTO::find($id);
            $detail_transfer_outlets = TransaksiTODetail::where('id_nota', $id)->where('is_deleted', 0)->where('is_reload_all', 1)->get();
            $is_status = 1;
            $apotek1 = MasterApotek::find($transfer_outlet->id_apotek_asal);
            $inisial1 = strtolower($apotek1->nama_singkat);

            $apotek2 = MasterApotek::find($transfer_outlet->id_apotek_tujuan);
            $inisial2 = strtolower($apotek2->nama_singkat);

            //dd($apotek2);exit();
            $i = 0;
            foreach ($detail_transfer_outlets as $key => $detail_transfer_outlet) { 
                //dd($detail_transfer_outlet);
                $obj = TransaksiTODetail::find($detail_transfer_outlet['id']);
                $obj->is_status = $is_status;
                $obj->konfirm_at = date('Y-m-d H:i:s');
                $obj->konfirm_by = Auth::user()->id;
                if($obj->save()){
                    
                } else {
                    DB::rollback();
                    session()->flash('error', 'Gagal mengkonfirmasi data transfer masuk!');
                    return redirect('transfer_outlet/konfirm/'.$id)->with('message', 'Gagal mengkonfirmasi data transfer masuk!');
                }

                // jika barang diterima buat histori
                if($obj->is_status == 1) {
                    // turn off -> because add konfirmasi transfer barang
                    $stok_before2 = DB::table('tb_m_stok_harga_'.$inisial2)->where('id_obat', $obj->id_obat)->first();
                    $outlet1 = DB::table('tb_m_stok_harga_'.$inisial1)->where('id_obat', $obj->id_obat)->first();

                    $stok_now2 = $stok_before2->stok_akhir+$obj->jumlah;

                    DB::table('tb_m_stok_harga_'.$inisial2)->where('id_obat', $obj->id_obat)->update(['stok_awal'=> $stok_before2->stok_akhir, 'stok_akhir'=> $stok_now2, 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => Auth::user()->id]);

                    # create histori
                    $histori_stok = new HistoriStokTujuan;
                    $histori_stok->setTable('tb_histori_stok_'.$inisial2);
                    $histori_stok->where('id_obat', $obj->id_obat)->where('jumlah', $obj->jumlah)->where('id_jenis_transaksi', 3)->where('id_transaksi', $obj->id)->first();
                    if(empty($histori_stok)) {
                        $histori_stok = new HistoriStokTujuan;
                        $histori_stok->setTable('tb_histori_stok_'.$inisial2);
                    }
                    $histori_stok->id_obat = $obj->id_obat;
                    $histori_stok->jumlah = $obj->jumlah;
                    $histori_stok->stok_awal = $stok_before2->stok_akhir;
                    $histori_stok->stok_akhir = $stok_now2;
                    $histori_stok->id_jenis_transaksi = 3; //transfer masuk
                    $histori_stok->id_transaksi = $obj->id;
                    $histori_stok->batch = null;
                    $histori_stok->ed = null;
                    $histori_stok->sisa_stok = $obj->jumlah;
                    $histori_stok->hb_ppn = $obj->harga_outlet;
                    $histori_stok->keterangan = 'TO Masuk pada IDdet.'.$obj->id.' sejumlah '.$obj->jumlah;
                    $histori_stok->created_at = $transfer_outlet->created_at;
                    $histori_stok->created_by = $transfer_outlet->created_by;

                    if($histori_stok->save()) {
                    } else {
                        DB::rollback();
                        session()->flash('error', 'Gagal create histori stok!');
                        return redirect('transfer_outlet/konfirm/'.$id)->with('message', 'Gagal create histori stok!');
                    }
              
                    if($stok_before2->harga_beli_ppn != $obj->harga_outlet) {
                        $data_histori_ = array('id_obat' => $obj->id_obat, 'harga_beli_awal' => $stok_before2->harga_beli, 'harga_beli_akhir' => $outlet1->harga_beli, 'harga_jual_awal' => $stok_before2->harga_jual, 'harga_jual_akhir' => $outlet1->harga_jual, 'created_by' => Auth::id(), 'created_at' => date('Y-m-d H:i:s'));

                        // update harga obat
                        DB::table('tb_histori_harga_'.$inisial2.'')->insert($data_histori_);
                        DB::table('tb_m_stok_harga_'.$inisial2)->where('id_obat', $obj->id_obat)->update(['updated_at' => date('Y-m-d H:i:s'), 'harga_beli' => $outlet1->harga_beli, 'harga_beli_ppn' => $obj->harga_outlet, 'updated_by' => Auth::user()->id]);
                    }
                }
                $i++;
            }

            if($i > 0) {
                $total_to = TransaksiTODetail::where('id_nota', $id)->where('is_deleted', 0)->count();
                $check_to_diterima = TransaksiTODetail::where('id_nota', $id)->where('is_deleted', 0)->where('is_status', '!=',  0)->count();
                
                if($total_to == $check_to_diterima) {
                    $transfer_outlet->is_status = 1;
                    $transfer_outlet->complete_at = date('Y-m-d H:i:s');
                    $transfer_outlet->complete_by = Auth::user()->id;
                    if($transfer_outlet->save()){
                    } else {
                        DB::rollback();
                        session()->flash('error', 'Gagal mengkonfirmasi data transfer masuk!');
                        return redirect('transfer_outlet/konfirm/'.$id)->with('message', 'Gagal mengkonfirmasi data transfer masuk!');
                    }
                } 

                DB::commit();
                session()->flash('success', 'Sukses mengkonfirmasi data transfer masuk!');
                return redirect('transfer_outlet/konfirm/'.$id)->with('message', 'Sukses mengkonfirmasi data transfer masuk!');
            } else {
                DB::rollback();
                session()->flash('error', 'Gagal mengkonfirmasi data transfer masuk!');
                return redirect('transfer_outlet/konfirm/'.$id)->with('message', 'Gagal mengkonfirmasi data transfer masuk!');
            }

        }catch(\Exception $e){
            DB::rollback();
            session()->flash('error', $e->getMessage());
            return redirect('transfer_outlet/konfirm/'.$id);
        }
    }
}
