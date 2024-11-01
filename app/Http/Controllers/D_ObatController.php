<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Requests;
use App\MasterObat;
use App\MasterGolonganObat;
use App\MasterPenandaanObat;
use App\MasterProdusen;
use App\MasterSatuan;
use App\MasterApotek;
use App\TransaksiPembelian;
use App\TransaksiPembelianDetail;
use App\TransaksiTO;
use App\TransaksiTODetail;
use App\PenyesuaianStok;
use App\MasterJenisTransaksi;
use App\MasterSuplier;
use App\TransaksiPenjualan;
use App\TransaksiPenjualanDetail;
use App\TransaksiPODetail;
use App\TransaksiPO;
use App\TransaksiTD;
use App\TransaksiTDDetail;
use App\User;
use App\SettingStokOpnam;
use App\ReturPembelian;
use App\HistoriStok;
use App;
use Datatables;
use DB;
use Excel;
use Auth;
use Cache;
use Input;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Imports\GolonganObatImport;
use App\Imports\HJStaticImport;
use App\Exports\DataObatExport;
use App\Exports\DataObatExport2;

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Type;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Schema;

class D_ObatController extends Controller
{
    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 25/02/2020
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

        return view('data_obat.index')->with(compact('golongan_obats', 'penandaan_obats', 'produsens'));
    }


    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 25/02/2020
        =======================================================================================
    */
    public function list_data_obat(Request $request)
    {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        $apotek = MasterApotek::find(session('id_apotek_active'));
        $apoteker = User::find($apotek->id_apoteker);
        $id_user = Auth::user()->id;
        $id_role_active = session('id_role_active');
        $hak_akses = 0;
        if($apoteker->id == $id_user) {
            $hak_akses = 1;
        }

        if($id_role_active == 1 || $id_role_active == 4 || $id_role_active == 6) {
            $hak_akses = 1;
        }

        $apoteks = MasterApotek::where('is_deleted', 0)->whereNotIn('id', [$apotek->id])->get();

        DB::statement(DB::raw('set @rownum = 0'));
        $data = DB::table('tb_m_stok_harga_'.$inisial.'')->select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_m_stok_harga_'.$inisial.'.id','tb_m_stok_harga_'.$inisial.'.harga_beli','tb_m_stok_harga_'.$inisial.'.hb_ppn_avg', 'tb_m_stok_harga_'.$inisial.'.harga_jual','tb_m_stok_harga_'.$inisial.'.harga_beli_ppn','tb_m_stok_harga_'.$inisial.'.is_status_harga','tb_m_stok_harga_'.$inisial.'.is_disabled','tb_m_stok_harga_'.$inisial.'.id_obat','tb_m_stok_harga_'.$inisial.'.stok_akhir', 'tb_m_obat.nama', 'tb_m_obat.barcode', 'tb_m_obat.isi_tab', 'tb_m_obat.isi_strip', 'tb_m_obat.rak', 'tb_m_obat.untung_jual', 'tb_m_obat.sku'])
                    ->join('tb_m_obat', 'tb_m_obat.id', '=', 'tb_m_stok_harga_'.$inisial.'.id_obat')
                    ->where(function($query) use($inisial, $request){
                        $query->where('tb_m_stok_harga_'.$inisial.'.is_deleted','=','0');
                        if($request->id_penandaan_obat != "") {
                            $query->where('tb_m_obat.id_penandaan_obat',$request->id_penandaan_obat);
                        }

                        if($request->id_golongan_obat != "") {
                            $query->where('tb_m_obat.id_golongan_obat',$request->id_golongan_obat);
                        }

                        if($request->id_produsen != "") {
                            $query->where('tb_m_obat.id_produsen',$request->id_produsen);
                        }
                    });
                    /*->where('tb_m_stok_harga_'.$inisial.'.is_deleted', 0)
                    ->where('tb_m_obat.id_penandaan_obat','LIKE',($request->id_penandaan_obat > 0 ? $request->id_penandaan_obat : '%'.$request->id_penandaan_obat.'%'))
                    ->where('tb_m_obat.id_golongan_obat','LIKE',($request->id_golongan_obat > 0 ? $request->id_golongan_obat : '%'.$request->id_golongan_obat.'%'))
                    ->where('tb_m_obat.id_produsen','LIKE',($request->id_produsen > 0 ? $request->id_produsen : '%'.$request->id_produsen.'%'));*/
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->orwhere('tb_m_obat.nama','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('tb_m_obat.barcode','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('tb_m_obat.sku','LIKE','%'.$request->get('search')['value'].'%');
            });
        })   
        ->editcolumn('nama', function($data) use($apoteks){
            $info = '<small>';
            $i = 0;
            foreach($apoteks as $obj) {
                $i++;
                $inisial = strtolower($obj->nama_singkat);
                $cek_ = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $data->id_obat)->first();
                if(isset($cek_)) {
                    $info .= $obj->nama_singkat.' : '.$cek_->stok_akhir;
                    if($i != count($apoteks)) {
                        $info .= ' | ';
                    }
                } else {
                    $info .= $obj->nama_singkat.' : data tidak ditemukan';
                    if($i != count($apoteks)) {
                        $info .= ' | ';
                    }
                }
            }
            $info .= '</small>';
            return '<b>'.$data->nama.'</b><br><span class="text-info">SKU : '.$data->sku.'</span><br>'.$info;
        })
        ->editcolumn('isi_tab', function($data){
            return $data->isi_tab.'/'.$data->isi_strip; 
        }) 
        ->editcolumn('untung_jual', function($data){
            return $data->untung_jual.'%'; 
        }) 
        ->editcolumn('harga_beli', function($data) use($hak_akses){
            $info = '';
            $info .= $data->harga_beli.'<br>';
           // if($hak_akses == 1) {
                $info .= '<span class="label" onClick="edit_harga_beli('.$data->id_obat.')" data-toggle="tooltip" data-placement="top" title="Edit Data" style="font-size:10pt;color:#0097a7;">[Edit]</span>';
            //}
            return $info; 
        }) 
        ->editcolumn('harga_beli_ppn', function($data) use($hak_akses){
            $info = '';
            $info .= $data->harga_beli_ppn.'<br>';
            //if($hak_akses == 1) {
                $info .= '<span class="label" onClick="edit_harga_beli('.$data->id_obat.')" data-toggle="tooltip" data-placement="top" title="Edit Data" style="font-size:10pt;color:#0097a7;">[Edit]</span>';
            //}
            return $info; 
        }) 
        ->editcolumn('hb_ppn_avg', function($data) use($hak_akses){
            $info = '';
            $histori_stok = HistoriStok::select([DB::raw('SUM(sisa_stok) as jum_sisa_stok'), DB::raw('SUM(sisa_stok*hb_ppn) as total')])
                            ->where('id_obat', $data->id_obat)
                            ->whereIn('id_jenis_transaksi', [2,3,11,9])
                            ->where('sisa_stok', '>', 0)
                            ->orderBy('id', 'ASC')
                            ->first();
            $avg = 0;
            $btn = '<span class="label text-red">[stok kosong]</span>';
            if(!is_null($histori_stok)) {
                if($histori_stok->total!=0) {
                    $avg = $histori_stok->total/$histori_stok->jum_sisa_stok;

                    $btn = '<span class="label" onClick="lihat_stok_tersedia('.$data->id_obat.')" data-toggle="tooltip" data-placement="top" title="Lihat Stok Tersedia" style="font-size:10pt;color:#0097a7;">[lihat stok tersedia]</span>';
                }
            }
            $avg = number_format($avg,2,".","");
            $info .= $avg.'<br>'.$btn;

            return $info; 
        }) 
        ->editcolumn('harga_jual', function($data) use($hak_akses){
            $info = '';
            $info .= $data->harga_jual.'<br>';
            if($hak_akses == 1) {
                $info .= '<span class="label" onClick="edit_harga_jual('.$data->id_obat.')" data-toggle="tooltip" data-placement="top" title="Edit Data" style="font-size:10pt;color:#0097a7;">[Edit]</span>';
            }
            return $info; 
        }) 
        ->editcolumn('is_disabled', function($data){
            if($data->is_disabled == 1) {
                $s = 'Non Aktif';'<small style="color:#d32f2f;"><i class="fa fa-close"></i></small>';
            } else {
                $s = 'Aktif';//;'<small style="color:#388e3c;"><i class="fa fa-check-square-o"></i></small>';
            }
            return $s; 
        }) 
        ->editcolumn('is_status_harga', function($data){
            $status = '';
            $status .= '<label class="switch">';
            if($data->is_status_harga == 0) {
                $status .= '<input type="checkbox" name="is_status_harga" id="is_status_harga" value="0" onclick="checkStatus(0, '.$data->id.')" >
                <span class="slider round"></span>';
            } else {
                $status .= '<input type="checkbox" name="is_status_harga" id="is_status_harga" value="1" checked="checked" onclick="checkStatus(1, '.$data->id.')">
                <span class="slider round"></span>';
             }
            $status .= '</label>';

            return $status; 
        }) 
        ->addcolumn('action', function($data) use ($hak_akses, $apoteker) {
            $btn = '<div class="btn-group">';
            if($hak_akses == 1) {
                $btn .= '<span class="btn btn-primary" onClick="edit_data('.$data->id_obat.')" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span>';
            }
            $btn .= '<a href="'.url('/data_obat/stok_obat/'.$data->id_obat).'" title="Stok Obat" class="btn btn-info"><span data-toggle="tooltip" data-placement="top" title="Stok Obat"><i class="fa fa-prescription-bottle-alt"></i></span></a>';
            $btn .= '<a href="'.url('/data_obat/histori_harga/'.$data->id_obat).'" title="Histori Harga" class="btn btn-secondary"><span data-toggle="tooltip" data-placement="top" title="Histori Harga"><i class="fa fa-history"></i></span></a>';
            /*$btn .= '<a href="'.url('/data_obat/histori_all/'.$data->id_obat).'" title="Histori All" class="btn btn-warning"><span data-toggle="tooltip" data-placement="top" title="Histori All"><i class="fa fa-clone"></i></span></a>';*/

            if($hak_akses == 1) {
                    $btn .= '<a href="'.url('/data_obat/penyesuaian_stok/'.$data->id_obat).'" class="btn"  style="background-color: #8BC34A; color:#fff;" onClick="#" data-toggle="tooltip" data-placement="top" title="Penyesuaian Stok"><i class="fa fa-flag"></i></a>';
                    $btn .= '<span class="btn btn-danger" onClick="disabled_obat('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Non-aktifkan Obat"><i class="fa fa-power-off"></i></span>';
            } 
            // request kak rudy, minta buat dimatikan
           /* $btn .= '<span class="btn btn-warning" onClick="sycn_harga_obat('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Sinkronisasi Obat"><i class="fa fa-sync"></i></span>';*/
            
            
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['isi_tab', 'is_disabled', 'DT_RowIndex', 'action', 'nama', 'hb_ppn_avg', 'harga_beli_ppn', 'harga_jual', 'is_status_harga', 'untung_jual'])
        ->addIndexColumn()
        ->make(true);  
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 26/02/2020
        =======================================================================================
    */
    public function edit($id)
    {
        $obat = MasterObat::find($id);
        $id_apotek = session('id_apotek_active');
        $apotek = MasterApotek::find($id_apotek);
        $inisial = strtolower($apotek->nama_singkat);
        $outlet = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $obat->id)->first();

        $produsens = MasterProdusen::where('is_deleted', 0)->pluck('nama', 'id');
        $produsens->prepend('-- Pilih Produsen --','');

        $satuans = MasterSatuan::where('is_deleted', 0)->pluck('satuan', 'id');
        $satuans->prepend('-- Pilih Satuan --','');

        $golongan_obats = MasterGolonganObat::where('is_deleted', 0)->pluck('keterangan', 'id');
        $golongan_obats->prepend('-- Pilih Golongan Obat --','');

        $penandaan_obats = MasterPenandaanObat::where('is_deleted', 0)->pluck('nama', 'id');
        $penandaan_obats->prepend('-- Pilih Penandaan Obat --','');

        return view('data_obat.edit')->with(compact('obat', 'produsens', 'satuans', 'golongan_obats', 'penandaan_obats', 'outlet'));
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
        Date    : 26/02/2020
        =======================================================================================
    */
    public function update(Request $request, $id)
    {
        $obat = MasterObat::find($id);
        $id_apotek = session('id_apotek_active');
        $apotek = MasterApotek::find($id_apotek);
        $inisial = strtolower($apotek->nama_singkat);
        $outlet = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $obat->id)->first();
        $harga_beli_awal = $outlet->harga_beli;
        $harga_jual_awal = $outlet->harga_jual;
        $validator = $obat->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            // add histori perubahan data harga obat
            if($harga_jual_awal != $request->harga_jual) {
                $data_histori_ = array('id_obat' => $obat->id, 'harga_beli_awal' => $harga_beli_awal, 'harga_beli_akhir' => $request->harga_beli, 'harga_jual_awal' => $harga_jual_awal, 'harga_jual_akhir' => $request->harga_jual, 'created_by' => Auth::id(), 'created_at' => date('Y-m-d H:i:s'));

                // update harga obat
                $inisial = strtolower($apotek->nama_singkat);
                DB::table('tb_histori_harga_'.$inisial.'')->insert($data_histori_);
                DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $obat->id)->update(['updated_at' => date('Y-m-d H:i:s'), 'harga_jual' => $request->harga_jual, 'updated_by' => Auth::user()->id]);
            } 

            echo json_encode(array('status' => 1));
        }
    }

    public function sycn_harga_obat_all() {

        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);

        $data = DB::table('tb_histori_harga_'.$inisial.'')
                ->where('created_by', 1)
                ->get();

                foreach($data as $key) {
                    $cek = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $key->id_obat)->first();
                    DB::table('tb_m_stok_harga_'.$inisial.'')
                        ->where('id_obat', $key->id_obat)
                        ->update(['is_status_harga' => 0, 'harga_jual' =>$key->harga_jual_awal, 'status_harga_by' => Auth::id(), 'status_harga_at' => date('Y-m-d H:i:s')]);

                    $data_histori_ = array('id_obat' => $key->id, 'harga_beli_awal' => $cek->harga_beli_ppn, 'harga_beli_akhir' => $cek->harga_beli_ppn, 'harga_jual_awal' => $key->harga_jual_akhir, 'harga_jual_akhir' => $key->harga_jual_awal, 'created_by' => Auth::id(), 'created_at' => date('Y-m-d H:i:s'));
                    DB::table('tb_histori_harga_'.$inisial.'')->insert($data_histori_);
                }

                echo "berhasil reload";
        exit();
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);

        $data = DB::table('tb_histori_stok_'.$inisial.'')
                    ->select([DB::raw('COUNT(id)  AS jumlah'), 'id_obat', 'id_jenis_transaksi', 'id_transaksi'])
                    ->groupBy('id_obat', 'id_jenis_transaksi', 'id_transaksi');

        /*$data = 
        echo "dasdas";
        print_r($data);
        exit();*/
        /*$i=0;
        foreach ($data as $key => $val) {
            $cek_ = MasterObat::find($val->id_obat);
            if(!empty($cek_)) {
                if($cek_->harga_beli != $val->harga_beli OR $cek_->harga_jual != $val->harga_jual) {
                    $i++;
                    DB::table('tb_m_stok_harga_'.$inisial.'')
                    ->where('id', $val->id)
                    ->update(['harga_beli' => $cek_->harga_beli, 'harga_jual' => $cek_->harga_jual, 'is_sync' => 1, 'sync_by' => Auth::id(), 'sync_at' => date('Y-m-d H:i:s')]);

                    // add histori perubahan data harga obat
                    $data_histori_ = array('id_obat' => $val->id_obat, 'harga_beli_awal' => $val->harga_beli, 'harga_beli_akhir' => $cek_->harga_beli, 'harga_jual_awal' => $val->harga_beli, 'harga_jual_akhir' => $cek_->harga_jual, 'created_by' => Auth::id(), 'created_at' => date('Y-m-d H:i:s'));

                    DB::table('tb_histori_harga_'.$inisial.'')->insert($data_histori_);
                } 
            } else {
                DB::table('tb_m_stok_harga_'.$inisial.'')
                    ->where('id', $val->id)
                    ->update(['is_deleted' => 1, 'deleted_by' => Auth::user()->id, 'deleted_at' => date('Y-m-d H:i:s')]);
            }
        }

        if($i > 0) {
            echo 1;
        } else {
            echo 0;
        }*/
    }

    /*public function sycn_harga_obat_all() {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);

        $data = DB::table('tb_m_stok_harga_'.$inisial.'')->get();
        $i=0;
        foreach ($data as $key => $val) {
            $cek_ = MasterObat::find($val->id_obat);
            if(!empty($cek_)) {
                if($cek_->harga_beli != $val->harga_beli OR $cek_->harga_jual != $val->harga_jual) {
                    $i++;
                    DB::table('tb_m_stok_harga_'.$inisial.'')
                    ->where('id', $val->id)
                    ->update(['harga_beli' => $cek_->harga_beli, 'harga_jual' => $cek_->harga_jual, 'is_sync' => 1, 'sync_by' => Auth::id(), 'sync_at' => date('Y-m-d H:i:s')]);

                    // add histori perubahan data harga obat
                    $data_histori_ = array('id_obat' => $val->id_obat, 'harga_beli_awal' => $val->harga_beli, 'harga_beli_akhir' => $cek_->harga_beli, 'harga_jual_awal' => $val->harga_beli, 'harga_jual_akhir' => $cek_->harga_jual, 'created_by' => Auth::id(), 'created_at' => date('Y-m-d H:i:s'));

    	   			DB::table('tb_histori_harga_'.$inisial.'')->insert($data_histori_);
                } 
            } else {
                DB::table('tb_m_stok_harga_'.$inisial.'')
                    ->where('id', $val->id)
                    ->update(['is_deleted' => 1, 'deleted_by' => Auth::user()->id, 'deleted_at' => date('Y-m-d H:i:s')]);
            }
        }

        if($i > 0) {
            echo 1;
        } else {
            echo 0;
        }
    }*/

    public function sycn_harga_obat(Request $request) {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);

        $val = DB::table('tb_m_stok_harga_'.$inisial.'')->where('id_obat', $request->id)->first();
        $cek_ = MasterObat::find($request->id);

        $i=0;
        if($cek_->harga_beli != $val->harga_beli OR $cek_->harga_jual != $val->harga_jual) {
        	$i++;
            DB::table('tb_m_stok_harga_'.$inisial.'')
            ->where('id', $val->id)
            ->update(['harga_beli' => $cek_->harga_beli, 'harga_jual' => $cek_->harga_jual, 'is_sync' => 1, 'sync_by' => Auth::id(), 'sync_at' => date('Y-m-d H:i:s')]);

            // add histori perubahan data harga obat
            $data_histori_ = array('id_obat' => $val->id_obat, 'harga_beli_awal' => $val->harga_beli, 'harga_beli_akhir' => $cek_->harga_beli, 'harga_jual_awal' => $val->harga_beli, 'harga_jual_akhir' => $cek_->harga_jual, 'created_by' => Auth::id(), 'created_at' => date('Y-m-d H:i:s'));

   			DB::table('tb_histori_harga_'.$inisial.'')->insert($data_histori_);
        } 

        if($i > 0) {
            echo 1;
        } else {
            echo 0;
        }
    }

    public function disabled_obat(Request $request)
    {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        DB::table('tb_m_stok_harga_'.$inisial.'')
                ->where('id', $request->id)
                ->update(['is_disabled' => 1, 'disabled_by' => Auth::id(), 'disabled_at' => date('Y-m-d H:i:s')]);

        echo 1;
    }

    public function stok_obat($id) {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $stok_harga = DB::table('tb_m_stok_harga_'.$inisial.'')->where('id_obat', $id)->first();
        $obat = MasterObat::find($id);
        $jenis_transasksis      = MasterJenisTransaksi::pluck('nama', 'id');
        $jenis_transasksis->prepend('-- Pilih Jenis Transaksi --','');
        return view('data_obat.stok_obat')->with(compact('obat', 'stok_harga', 'jenis_transasksis'));
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 21/06/2020
        =======================================================================================
    */
    public function list_data_stok_obat(Request $request)
    {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

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
                ->where(function($query) use($request, $inisial){
                    $query->where('tb_histori_stok_'.$inisial.'.id_obat', $request->id_obat);
                    $query->where('tb_histori_stok_'.$inisial.'.id_jenis_transaksi','LIKE',($request->id_jenis_transaksi > 0 ? $request->id_jenis_transaksi : '%'.$request->id_jenis_transaksi.'%'));

                    if($request->tgl_awal != "") {
                        $tgl_awal       = date('Y-m-d H:i:s',strtotime($request->tgl_awal));
                        $query->whereDate('tb_histori_stok_'.$inisial.'.created_at','>=', $tgl_awal);
                    }

                    if($request->tgl_akhir != "") {
                        $tgl_akhir      = date('Y-m-d H:i:s',strtotime($request->tgl_akhir));
                        $query->whereDate('tb_histori_stok_'.$inisial.'.created_at','<=', $tgl_akhir);
                    }

                })
                ->whereYear('tb_histori_stok_'.$inisial.'.created_at', session('id_tahun_active'))
                ->orderBy('tb_histori_stok_'.$inisial.'.id');
        
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
            $sign_by = '';
            if (in_array($data->id_jenis_transaksi, $data_pembelian_)) {
                if($data->id_jenis_transaksi == 26) {
                    $retur = ReturPembelian::find($data->id_transaksi);
                    $check = TransaksiPembelianDetail::find($retur->id_detail_nota);
                    $id_nota = ' | IDNota : '.$check->nota->id.' | No.Faktur : '.$check->nota->no_faktur;
                    $string = '<b>'.$check->nota->suplier->nama.'</b>';
                    $sign_by = $check->nota->sign_by;
                } else {
                    $check = TransaksiPembelianDetail::find($data->id_transaksi);
                    $id_nota = ' | IDNota : '.$check->nota->id.' | No.Faktur : '.$check->nota->no_faktur;
                    $string = '<b>'.$check->nota->suplier->nama.'</b>';
                    $sign_by = $check->nota->sign_by;
                }

                if($sign_by = '') {
                    $sign_by = 'Belum diTTD';
                }
            } else if (in_array($data->id_jenis_transaksi, $data_tf_masuk_)) {
                $check = TransaksiTODetail::find($data->id_transaksi);
                $id_nota = ' | IDNota : '.$check->nota->id;
                $string = '<b>Masuk dari '.$check->nota->apotek_asal->nama_singkat.'</b>';
            } else if (in_array($data->id_jenis_transaksi, $data_tf_keluar_)) {
                $check = TransaksiTODetail::find($data->id_transaksi);
                $id_nota = ' | IDNota : '.$check->nota->id;
                $string = '<b>Tujuan ke '.$check->nota->apotek_tujuan->nama_singkat.'</b>';
                $sign_by = $check->nota->sign_by;
                if($sign_by = '') {
                    $sign_by = 'Belum diTTD';
                }
            } else if (in_array($data->id_jenis_transaksi, $data_penjualan_)) {
                $tahun = date('Y', strtotime($data->created_at));
                if($tahun!=date('Y')) {
                    $id_nota = '| Detail belum bisa ditampilkan.';
                } else {
                    $check = TransaksiPenjualanDetail::find($data->id_transaksi);
                    if($check->nota->is_kredit == 1) {
                        $string = '<b>Vendor : '.$check->nota->vendor->nama.'</b>';
                    } else {
                        $string = '<b>Member : - </b>';
                    }
                    $id_nota = ' | IDNota : '.$check->nota->id;
                }
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
                $sign_by = $check->nota->sign_by;
                if($sign_by = '') {
                    $sign_by = 'Belum diTTD';
                }
            } 

            if($string != '') {
                $string = '<br>'.$string;
            }

            $det_ = '<span style="font-size:10pt;">'.$data->nama_transaksi.$string.'<br>'.'IDdet : '.$data->id_transaksi.$id_nota.'</span>';
            if($sign_by != '') {
                $det_ = $det_.'<br><span class="text-warning" style="font-size:10pt;">'.'sign by '.$sign_by.'</span>';
            }
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
                if($data->id_jenis_transaksi == 26) {
                    $retur = ReturPembelian::find($data->id_transaksi);
                    $check = TransaksiPembelianDetail::find($retur->id_detail_nota);
                } else {
                    $check = TransaksiPembelianDetail::find($data->id_transaksi);
                }
                
                //$ed = '('.$data->batch.')<br>';
                if($check->tgl_batch == '' OR $check->tgl_batch == null OR $check->tgl_batch == '0') {
                    $ed = $data->ed;
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
                    $oleh .= '<hr><span class="text-danger">[belum dikonfirm]</span>';
                }
            }

            $oleh.= '</span>';

            $oleh = strtolower($oleh);

            return $oleh;
        }) 
        ->rawColumns(['craeted_at', 'id_jenis_transaksi', 'masuk', 'keluar', 'stok_akhir', 'batch', 'ed', 'created_by', 'hb_ppn'])
        ->make(true);  
    }

    public function histori_harga($id) {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $stok_harga = DB::table('tb_m_stok_harga_'.$inisial.'')->where('id_obat', $id)->first();
        $obat = MasterObat::find($id);
        $apoteks = MasterApotek::where('is_deleted', 0)->whereNotIn('id',[$apotek->id])->get();

        $list_hpp = array();
        foreach ($apoteks as $key => $val) {
            $apotek = MasterApotek::find($val->id);
            $inisial = strtolower($apotek->nama_singkat);
            $getHpp = DB::table('tb_m_stok_harga_'.$inisial)
                        ->where('id_obat', $obat->id)
                        ->first();
            $d_ = array();
            $d_['id_outlet'] = $val->id;
            $d_['nama_singkat'] = $val->nama_singkat;
            $d_['nama_panjang'] = $val->nama_panjang;
            $d_['hpp'] = $getHpp->harga_beli_ppn;
            $d_['harga_jual'] = $getHpp->harga_jual;
            $d_['stok_akhir'] = $getHpp->stok_akhir;
            $d_['margin'] = $obat->untung_jual;
            $list_hpp[] = $d_;
 
        }
        return view('data_obat.histori_harga')->with(compact('obat', 'stok_harga', 'apoteks', 'apotek', 'list_hpp'));
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 21/06/2020
        =======================================================================================
    */
    public function list_data_histori_harga(Request $request)
    {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        if(isset($request->id_apotek)) {
            $apotek = MasterApotek::find($request->id_apotek);
            $inisial = strtolower($apotek->nama_singkat);
        }

        DB::statement(DB::raw('set @rownum = 0'));
        $data = DB::table('tb_histori_harga_'.$inisial.'')->select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_histori_harga_'.$inisial.'.*', 'users.nama as oleh'])
                ->join('users', 'users.id', '=', 'tb_histori_harga_'.$inisial.'.created_by')
                ->where('tb_histori_harga_'.$inisial.'.id_obat', $request->id_obat);
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request,$order_column,$order_dir, $inisial){
            $query->where(function($query) use($request, $inisial){
                $query->orwhere('tb_histori_harga_'.$inisial.'.created_at','LIKE','%'.$request->get('search')['value'].'%');
            });
        })   
        ->editcolumn('created_at', function($data){
            return date('d-m-Y', strtotime($data->created_at)); 
        }) 
        ->editcolumn('harga_beli_awal', function($data){
            $harga_beli_awal = 'Rp '.number_format($data->harga_beli_awal,0,',','.');
            return $harga_beli_awal; 
        }) 
        ->editcolumn('harga_beli_akhir', function($data){
            $harga_beli_akhir = 'Rp '.number_format($data->harga_beli_akhir,0,',','.');
            return $harga_beli_akhir; 
        }) 
        ->editcolumn('harga_jual_awal', function($data){
            $harga_jual_awal = 'Rp '.number_format($data->harga_jual_awal,0,',','.');
            return $harga_jual_awal; 
        }) 
        ->editcolumn('harga_jual_akhir', function($data){
            $harga_jual_akhir = 'Rp '.number_format($data->harga_jual_akhir,0,',','.');
            return $harga_jual_akhir; 
        }) 
        ->editcolumn('id_asal', function($data){
            $string = '';
            if($data->is_asal == 1) {
                $string = 'Pembelian<b> | ID Transaksi : '.$data->id_asal.'</b>';
            } else if($data->is_asal == 2) {
                $string = 'Penyesuaian Harga<b> | Stok Opnam</b>';
            } else {
                $string = 'Penyesuaian Harga<b> | Master Data</b>';
            }
            return $string; 
        }) 
        ->editcolumn('created_by', function($data){
            if(strlen($data->oleh) > 20) {
                $trimstring = substr($data->oleh, 0, 20);
                $oleh = 'by '.$trimstring;
            } else {
                $oleh = 'by '.$data->oleh;
            }

            return strtolower($oleh);
        }) 
        ->rawColumns(['craeted_at', 'created_by', 'id_asal'])
        ->make(true);  
    }

    public function export_data_obat_stok(Request $request)
    {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);

        return Excel::download(new DataObatExport($inisial), 'data_stok_'.$inisial.'.xlsx');

        

      // ::import(new MasterObat, 'users.xlsx');

        /*$myFile =  Excel::create('Data Stok Obat', function($excel) use ($request, $inisial) {
            
            $excel->sheet('Sheet 1', function($sheet) use ($request, $inisial) {

                $headings = array('No', 'Barcode','Nama', 'Isi /tab','Isi /strip', 'Harga Beli', 'Harga Jual', 'Stok');
                 $sheet->cell('A1:Q1', function($cell) {
                    $cell->setFontWeight('bold');
                });

                $sheet->appendRow(1, $headings);

                DB::statement(DB::raw('set @rownum = 0'));
                $data = DB::table('tb_m_stok_harga_'.$inisial.'')->select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_m_stok_harga_'.$inisial.'.*', 'tb_m_obat.nama', 'tb_m_obat.barcode', 'tb_m_obat.isitab', 'tb_m_obat.isistrip'])
                    ->join('tb_m_obat', 'tb_m_obat.id', '=', 'tb_m_stok_harga_'.$inisial.'.id_obat')
                    ->where('tb_m_stok_harga_'.$inisial.'.is_deleted', 0)
                    ->where('tb_m_stok_harga_'.$inisial.'.is_disabled', 0)
                    ->get();

                $no = 0;
                foreach($rekaps as $rekap) {
                    $no++;
                    $data[] = array(
                        $no,
                        $rekap->barcode,
                        $rekap->nama,
                        $rekap->isitab,
                        $rekap->isistrip,
                        $rekap->harga_beli,
                        $rekap->harga_jual,
                        $rekap->stok_akhir
                    );
                }
                
                $sheet->fromArray($data, null, 'A2', false, false);
            });
        });

        $myFile = $myFile->string('xlsx'); //change xlsx for the format you want, default is xls
        $response =  array(
           'name' => "Data Stok Obat", //no extention needed
           'file' => "data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64,".base64_encode($myFile) //mime type of used format
        );
        return response()->json($response);*/
    }

    public function export_data(Request $request)
    {
        $id_penandaan_obat = $request->id_penandaan_obat;
        $id_golongan_obat = $request->id_golongan_obat;
        $id_produsen = $request->id_produsen;
        if(is_null($id_penandaan_obat)) {
            $id_penandaan_obat = '';
        }
        if(is_null($id_golongan_obat)) {
            $id_golongan_obat = '';
        }
        if(is_null($id_produsen)) {
            $id_produsen = '';
        }
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);

        return Excel::download(new DataObatExport2($inisial, $id_penandaan_obat, $id_golongan_obat, $id_produsen), 'data_obat_'.$inisial.'.xlsx');
    }

    public function penyesuaian_stok($id) {
        if(Auth::user()->is_admin!=1) {
            echo "under maintenance"; exit();
        } else {
            $apotek = MasterApotek::find(session('id_apotek_active'));
            $inisial = strtolower($apotek->nama_singkat);
            $stok_harga = DB::table('tb_m_stok_harga_'.$inisial.'')->where('id_obat', $id)->first();
            $obat = MasterObat::find($id);
            return view('data_obat.penyesuaian_stok')->with(compact('obat', 'stok_harga'));
        }
    }

    /*$data = DB::table('tb_histori_stok_'.$inisial.'')->select([
                    DB::raw('@rownum  := @rownum  + 1 AS no'), 
                    'tb_histori_stok_'.$inisial.'.*', 
                    'users.nama as oleh',
                    'tb_m_jenis_transaksi.nama as nama_transaksi',
                    'tb_m_jenis_transaksi.act'
                ])
                ->join('users', 'users.id', '=', 'tb_histori_stok_'.$inisial.'.created_by')
                ->join('tb_m_jenis_transaksi', 'tb_m_jenis_transaksi.id', '=', 'tb_histori_stok_'.$inisial.'.id_jenis_transaksi')
                ->where('tb_histori_stok_'.$inisial.'.id_obat', $request->id_obat);
        
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
            return $data->nama_transaksi.' | ID Transaksi : '.$data->id_transaksi; 
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
            if($data->id_jenis_transaksi == 2) {
                $batch = $data->batch;
            }
            return $batch;
        }) 
        ->editcolumn('ed', function($data){
            $ed = '-';
            if($data->id_jenis_transaksi == 2) {
                $batch = $data->ed;
            }
            return $ed;
        }) 
        ->editcolumn('created_by', function($data){
            if(strlen($data->oleh) > 15) {
                $trimstring = substr($data->oleh, 0, 15);
                $oleh = 'by '.$trimstring;
            } else {
                $oleh = 'by '.$data->oleh;
            }

            return strtolower($oleh);
        }) 
        ->rawColumns(['craeted_at', 'id_jenis_transaksi', 'masuk', 'keluar', 'stok_akhir', 'batch', 'ed', 'created_by'])
        ->make(true);  */

    public function list_data_penyesuaian_stok_obat(Request $request) {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        $super_admin = session('super_admin');
        DB::statement(DB::raw('set @rownum = 0'));
        $data = PenyesuaianStok::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_penyesuaian_stok_obat.*', 'users.nama as oleh'])
        ->join('users', 'users.id', '=', 'tb_penyesuaian_stok_obat.created_by')
        ->where(function($query) use($request, $super_admin){
            $query->where('tb_penyesuaian_stok_obat.is_deleted','=','0');
            $query->where('tb_penyesuaian_stok_obat.id_obat', $request->id_obat);
            $query->where('tb_penyesuaian_stok_obat.id_apotek_nota', session('id_apotek_active'));
        });
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->orwhere('stok_awal','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('stok_akhir','LIKE','%'.$request->get('search')['value'].'%');
            });
        }) 
        ->editcolumn('created_at', function($data) use($request){
            return Carbon::parse($data->created_at)->format('d/m/Y H:i:s');
        })
        ->editcolumn('created_by', function($data){
            if(strlen($data->oleh) > 15) {
                $trimstring = substr($data->oleh, 0, 15);
                $oleh = 'by '.$trimstring;
            } else {
                $oleh = 'by '.$data->oleh;
            }

            return strtolower($oleh);
        }) 
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['action', 'created_by'])
        ->make(true);  
    }

    public function export(Request $request) 
    {
        //ini_set('memory_limit', '-1'); 
        
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
       
        $rekaps = DB::table('tb_m_stok_harga_'.$inisial.'')
                    ->select([
                            'tb_m_stok_harga_'.$inisial.'.*', 
                            'tb_m_obat.nama', 
                            'tb_m_obat.sku', 
                            'tb_m_obat.isi_tab', 
                            'tb_m_obat.isi_strip', 
                            'tb_m_obat.rak'
                    ])
                    ->join('tb_m_obat', 'tb_m_obat.id', '=', 'tb_m_stok_harga_'.$inisial.'.id_obat')
                    ->where('tb_m_stok_harga_'.$inisial.'.is_deleted', 0)
                    ->get();


                $collection = collect();
                $no = 0;
                $total_excel=0;
                foreach($rekaps as $rekap) {
                    $no++;
                    $collection[] = array(
                        $no,
                        $rekap->sku,
                        $rekap->nama,
                        $rekap->isi_strip,
                        $rekap->isi_tab,
                        $rekap->rak,
                        "Rp ".number_format($rekap->harga_beli,2),
                        "Rp ".number_format($rekap->harga_beli_ppn,2),
                        "Rp ".number_format($rekap->harga_jual,2),
                        $rekap->stok_akhir
                    );
                }

        return Excel::download(new class($collection) implements FromCollection, WithHeadings, WithColumnWidths, WithStyles {

                    public function __construct($collection)
                    {
                        $this->collection = $collection;
                    }

                    public function headings(): array
                    {
                        return ['No', 'SKU', 'Nama Obat', 'Isi/strip', 'Isi/tab', 'Rak', 'Harga Beli', 'Harga Beli + PPN', 'Harga Jual', 'Stok'];
                    } 

                    public function columnWidths(): array
                    {
                        return [
                            'A' => 8,
                            'B' => 20,
                            'C' => 40,
                            'D' => 10,
                            'E' => 10,
                            'F' => 10,
                            'G' => 20,
                            'H' => 20,
                            'I' => 20,  
                            'J' => 10,      
                        ];
                    }

                    public function styles(Worksheet $sheet)
                    {
                        return [
                            1    => ['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'A'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'B'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'D'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'E'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'F'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'I'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                            'H'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                            'G'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                            'J'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                        ];
                    }


                    public function collection()
                    {
                        return $this->collection;
                    }
        },"Data Obat Apotek ".$apotek->nama_singkat.".xlsx");
    }

    public function persediaan() {
       //exit();
        $tahun = date('Y');
        $bulan = date('m');
        $first_day = date('Y-m-d');
        $jum_obat = MasterObat::count();
        return view('data_obat.persediaan')->with(compact('tahun', 'bulan', 'first_day', 'jum_obat'));
    }

    public function list_persediaan(Request $request) {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);

        $awal = DB::table('tb_histori_stok_'.$inisial.'')
                    ->select([
                        DB::raw('MIN(tb_histori_stok_'.$inisial.'.id) as id'),
                        'tb_histori_stok_'.$inisial.'.id_obat'
                    ])
                    ->where(function($query) use($request){
                        if($request->tgl_awal != "") {
                            $dd = $request->tgl_awal.' 00:00:01';
                            $tgl_awal       = date('Y-m-d H:i:s',strtotime($dd));
                            $query->whereRaw('DATE(created_at) >= "'.$tgl_awal.'"');
                        }

                        if($request->tgl_akhir != "") {
                            $dd = $request->tgl_akhir.' 23:59:59';
                            $tgl_akhir      = date('Y-m-d H:i:s',strtotime($dd));
                            $query->whereRaw('DATE(created_at) <= "'.$tgl_akhir.'"');
                        }
                    })
                    ->groupBy('id_obat');

        $akhir = DB::table('tb_histori_stok_'.$inisial.'')
                    ->select([
                        DB::raw('MAX(tb_histori_stok_'.$inisial.'.id) as id'),
                        'tb_histori_stok_'.$inisial.'.id_obat'
                    ])
                    ->where(function($query) use($request){
                        if($request->tgl_awal != "") {
                            $dd = $request->tgl_awal.' 00:00:01';
                            $tgl_awal       = date('Y-m-d H:i:s',strtotime($dd));
                            $query->whereRaw('DATE(created_at) >= "'.$tgl_awal.'"');
                        }

                        if($request->tgl_akhir != "") {
                            $dd = $request->tgl_akhir.' 23:59:59';
                            $tgl_akhir      = date('Y-m-d H:i:s',strtotime($dd));
                            $query->whereRaw('DATE(created_at) <= "'.$tgl_akhir.'"');
                        }
                    })
                    ->groupBy('id_obat');

        $p_plus = DB::table('tb_histori_stok_'.$inisial.'')
                    ->select([
                        DB::raw('SUM(tb_histori_stok_'.$inisial.'.jumlah) as total_plus'),
                        'tb_histori_stok_'.$inisial.'.id_obat'
                    ])
                    ->where(function($query) use($request){
                        $query->whereRaw('id_jenis_transaksi = 9');
                        if($request->tgl_awal != "") {
                            $dd = $request->tgl_awal.' 00:00:01';
                            $tgl_awal       = date('Y-m-d H:i:s',strtotime($dd));
                            $query->whereRaw('DATE(created_at) >= "'.$tgl_awal.'"');
                        }

                        if($request->tgl_akhir != "") {
                            $dd = $request->tgl_akhir.' 23:59:59';
                            $tgl_akhir      = date('Y-m-d H:i:s',strtotime($dd));
                            $query->whereRaw('DATE(created_at) <= "'.$tgl_akhir.'"');
                        }
                    })
                    ->groupBy('id_obat');

        $p_min = DB::table('tb_histori_stok_'.$inisial.'')
                    ->select([
                        DB::raw('SUM(tb_histori_stok_'.$inisial.'.jumlah) as total_min'),
                        'tb_histori_stok_'.$inisial.'.id_obat'
                    ])
                    ->where(function($query) use($request){
                        $query->whereRaw('id_jenis_transaksi = 10');
                        if($request->tgl_awal != "") {
                            $dd = $request->tgl_awal.' 00:00:01';
                            $tgl_awal       = date('Y-m-d H:i:s',strtotime($dd));
                            $query->whereRaw('DATE(created_at) >= "'.$tgl_awal.'"');
                        }

                        if($request->tgl_akhir != "") {
                            $dd = $request->tgl_akhir.' 23:59:59';
                            $tgl_akhir      = date('Y-m-d H:i:s',strtotime($dd));
                            $query->whereRaw('DATE(created_at) <= "'.$tgl_akhir.'"');
                        }
                    })
                    ->groupBy('id_obat');

        $penjualan = DB::table('tb_detail_nota_penjualan')
                    ->select([
                        DB::raw('SUM(jumlah-jumlah_cn) as total_jual'),
                        'id_obat'
                    ])
                    ->join('tb_nota_penjualan as a', 'a.id', 'tb_detail_nota_penjualan.id_nota')
                    ->where(function($query) use($request){
                        $query->whereRaw('tb_detail_nota_penjualan.is_deleted = 0');
                        $query->whereRaw('a.id_apotek_nota = '.session('id_apotek_active').'');
                        if($request->tgl_awal != "") {
                            $dd = $request->tgl_awal;
                            $tgl_awal       = date('Y-m-d',strtotime($dd));
                            $query->whereRaw('DATE(a.tgl_nota) >= "'.$tgl_awal.'"');
                        }

                        if($request->tgl_akhir != "") {
                            $dd = $request->tgl_akhir;
                            $tgl_akhir      = date('Y-m-d',strtotime($dd));
                            $query->whereRaw('DATE(a.tgl_nota) <= "'.$tgl_akhir.'"');
                        }
                    })
                    ->groupBy('id_obat');

        $pembelian = DB::table('tb_detail_nota_pembelian')
                    ->select([
                        DB::raw('SUM(jumlah) as total_beli'),
                        'id_obat'
                    ])
                    ->join('tb_nota_pembelian as a', 'a.id', 'tb_detail_nota_pembelian.id_nota')
                    ->where(function($query) use($request){
                        $query->whereRaw('tb_detail_nota_pembelian.is_deleted = 0');
                        $query->whereRaw('a.id_apotek_nota = '.session('id_apotek_active').'');
                        if($request->tgl_awal != "") {
                            $dd = $request->tgl_awal;
                            $tgl_awal       = date('Y-m-d',strtotime($dd));
                            $query->whereRaw('DATE(a.tgl_nota) >= "'.$tgl_awal.'"');
                        }

                        if($request->tgl_akhir != "") {
                            $dd = $request->tgl_akhir;
                            $tgl_akhir      = date('Y-m-d',strtotime($dd));
                            $query->whereRaw('DATE(a.tgl_nota) <= "'.$tgl_akhir.'"');
                        }
                    })
                    ->groupBy('id_obat');

        $transfer = DB::table('tb_detail_nota_transfer_outlet')
                    ->select([
                        DB::raw('SUM(jumlah) as total_transfer'),
                        'id_obat'
                    ])
                    ->join('tb_nota_transfer_outlet as a', 'a.id', 'tb_detail_nota_transfer_outlet.id_nota')
                    ->where(function($query) use($request){
                        $query->whereRaw('tb_detail_nota_transfer_outlet.is_deleted = 0');
                        $query->whereRaw('a.id_apotek_nota = '.session('id_apotek_active').'');
                        if($request->tgl_awal != "") {
                            $dd = $request->tgl_awal;
                            $tgl_awal       = date('Y-m-d',strtotime($dd));
                            $query->whereRaw('DATE(a.tgl_nota) >= "'.$tgl_awal.'"');
                        }

                        if($request->tgl_akhir != "") {
                            $dd = $request->tgl_akhir;
                            $tgl_akhir      = date('Y-m-d',strtotime($dd));
                            $query->whereRaw('DATE(a.tgl_nota) <= "'.$tgl_akhir.'"');
                        }
                    })
                    ->groupBy('id_obat');

        DB::statement(DB::raw('set @rownum = 0'));
        $data = DB::table('tb_m_stok_harga_'.$inisial.'')
                    ->select([DB::raw('@rownum  := @rownum  + 1 AS no'),
                        'tb_m_stok_harga_'.$inisial.'.id',
                        'tb_m_stok_harga_'.$inisial.'.id_obat',
                        'tb_m_stok_harga_'.$inisial.'.harga_jual',
                        'tb_m_stok_harga_'.$inisial.'.harga_beli',
                        'tb_m_stok_harga_'.$inisial.'.harga_beli_ppn',
                        'tb_m_stok_harga_'.$inisial.'.stok_awal as awalan_stok',
                        'tb_m_stok_harga_'.$inisial.'.stok_akhir as akhiran_stok',
                        'tb_m_obat.nama', 
                        'tb_m_obat.barcode',
                        'a.id as id_histori_awal',
                        'b.id as id_histori_akhir',
                        'c.total_jual',
                        'd.total_beli',
                        'e.total_transfer',
                        'f.total_plus',
                        'g.total_min'
                    ])
                    ->join('tb_m_obat', 'tb_m_obat.id', '=', 'tb_m_stok_harga_'.$inisial.'.id_obat')
                    ->leftjoin(DB::raw("({$awal->toSql()}) as a"), 'a.id_obat', '=', 'tb_m_stok_harga_'.$inisial.'.id_obat')
                    ->leftjoin(DB::raw("({$akhir->toSql()}) as b"), 'b.id_obat', '=', 'tb_m_stok_harga_'.$inisial.'.id_obat')
                    ->leftjoin(DB::raw("({$penjualan->toSql()}) as c"), 'c.id_obat', '=', 'tb_m_stok_harga_'.$inisial.'.id_obat')
                    ->leftjoin(DB::raw("({$pembelian->toSql()}) as d"), 'd.id_obat', '=', 'tb_m_stok_harga_'.$inisial.'.id_obat')
                    ->leftjoin(DB::raw("({$transfer->toSql()}) as e"), 'e.id_obat', '=', 'tb_m_stok_harga_'.$inisial.'.id_obat')
                    ->leftjoin(DB::raw("({$p_plus->toSql()}) as f"), 'f.id_obat', '=', 'tb_m_stok_harga_'.$inisial.'.id_obat')
                    ->leftjoin(DB::raw("({$p_min->toSql()}) as g"), 'g.id_obat', '=', 'tb_m_stok_harga_'.$inisial.'.id_obat')
                    ->where(function($query) use($request, $inisial){
                        $query->whereRaw('tb_m_stok_harga_'.$inisial.'.is_deleted = 0');
                        $query->whereRaw('tb_m_obat.is_deleted = 0');
                    })
                    ->orderBy('tb_m_stok_harga_'.$inisial.'.id_obat', 'ASC');
        
        $datatables = Datatables::of($data);
        return $datatables  
        ->filter(function($query) use($request){
            $query->where(function($query) use($request){
               // $query->orwhere('a.nama','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->editcolumn('id_obat', function($data) {
            return $data->nama;
        })  
        ->editcolumn('stok_awal', function($data) {
            $stok = $data->akhiran_stok;
            if(!empty($data->id_histori_awal)) {
                $stok = $this->cari_stok(1, $data->id_histori_awal);
                $stok = $stok['stok'];
            }
            return $stok;
        })  
        ->editcolumn('jumlah_jual', function($data) {
            if(is_null($data->total_jual)) {
                $data->total_jual = 0;
            }
            return $data->total_jual;
        })  
        ->editcolumn('jumlah_beli', function($data) {
            if(is_null($data->total_beli)) {
                $data->total_beli = 0;
            }
            return $data->total_beli;
        })  
         ->editcolumn('jumlah_transfer', function($data) {
            if(is_null($data->total_transfer)) {
                $data->total_transfer = 0;
            }
            return $data->total_transfer;
        })  
         ->editcolumn('jumlah_p_plus', function($data) {
            if(is_null($data->total_plus)) {
                $data->total_plus = 0;
            }
            return $data->total_plus;
        })  
         ->editcolumn('jumlah_p_min', function($data) {
            if(is_null($data->total_min)) {
                $data->total_min = 0;
            }
            return $data->total_min;
        })  
        ->editcolumn('stok_akhir', function($data) {
            $stok = $data->akhiran_stok;
            if(!empty($data->akhir_stok_akhir)) {
                $stok = $this->cari_stok(2, $data->id_histori_akhir);
                $stok = $stok['stok'];
            }
            return $stok;
        }) 
        ->editcolumn('harga_beli_ppn', function($data) {
            // | 5/9/2021 | Sri Utami
            $harga_pokok = $data->harga_beli_ppn;
            if(!empty($data->akhir_stok_akhir)) {
                $stok = $this->cari_stok(2, $data->id_histori_akhir);
                $harga_pokok = $stok['hb_ppn'];
            }
           /* $jumlah = $data->total_jual;
            $total = $jumlah * $data->harga_jual;
            $total_hp = $jumlah*$harga_pokok;
            $laba = $total-$total_hp;
            if($laba < 0) {
                $harga_pokok = $data->harga_beli;
            } */
            return "Rp ".number_format($harga_pokok,0);
        })  
        ->editcolumn('harga_jual', function($data) {
            return "Rp ".number_format($data->harga_jual,0);
        })
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['action', 'id_obat', 'stok_awal', 'jumlah_jual', 'jumlah_beli', 'stok_akhir', 'harga_beli_ppn', 'harga_jual', 'jumlah_p_plus', 'jumlah_p_min'])
        ->addIndexColumn()
        ->make(true);  
    }

    public function cari_stok($act, $id) {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);

        $cek = DB::table('tb_histori_stok_'.$inisial.'')
                    ->select([
                        'tb_histori_stok_'.$inisial.'.*'
                    ])
                    ->where('id', $id)
                    ->first();
        
        if ($act == 1) {
            $arr = array(11,14,15,16,17,7,8,12,13,19,20,23,24,25,28,29,30,31,32,33);
            if(in_array($cek->id_jenis_transaksi, $arr)) {
                $new_arr = array('id_jenis_transaksi' => $cek->id_jenis_transaksi, 'stok' => $cek->stok_akhir, 'hb_ppn' => $cek->hb_ppn);
                return $new_arr;
            } else {
                $new_arr = array('id_jenis_transaksi' => $cek->id_jenis_transaksi, 'stok' => $cek->stok_awal, 'hb_ppn' => $cek->hb_ppn);
                return $new_arr;
            }
        } else {
            $new_arr = array('id_jenis_transaksi' => $cek->id_jenis_transaksi, 'stok' => $cek->stok_akhir, 'hb_ppn' => $cek->hb_ppn);
            return $new_arr;
        }
    }

    function getIdIterasi($iterasi) {
        $range = 200;
        $id_awal = ($iterasi - 1) * $range + 1;
        $id_akhir = $iterasi * $range;
    
        return array('iterasi' => $iterasi, 'id_awal' => $id_awal, 'id_akhir' => $id_akhir);
    }

    function getTanggalByIterasi($tgl_awal, $tgl_akhir, $iterasi) {
        // Konversi tanggal awal dan akhir menjadi timestamp
        $timestampAwal = strtotime($tgl_awal);
        $timestampAkhir = strtotime($tgl_akhir);

        // Tambahkan jumlah hari sesuai iterasi ke timestamp awal
        $timestampIterasi = strtotime("+$iterasi days", $timestampAwal);

        // Jika tgl_awal dan tgl_akhir sama dan iterasi adalah 0, kembalikan tgl_awal
        if ($timestampAwal == $timestampAkhir && $iterasi == 0) {
           // return date('Y-m-d', $timestampAwal);
            return array('iterasi' => $iterasi, 'tgl' => date('Y-m-d',$timestampAwal));
        }

        // Pastikan timestamp hasil tidak melebihi timestamp akhir
        if ($timestampIterasi > $timestampAkhir) {
            return "Iterasi melebihi tanggal akhir yang diberikan.";
        }

        // Format tanggal hasil iterasi ke dalam format 'Y-m-d'
        return array('iterasi' => $iterasi, 'tgl' => date('Y-m-d',$tanggalIterasi));
    }

    public function reload_dw_awal(Request $request) {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $tgl_awal = $request->tgl_awal;
        $tgl_akhir = $request->tgl_akhir;

        $iterasi = $request->iterasi;
        $getIdIterasi = $this->getTanggalByIterasi($tgl_awal, $tgl_awal, $iterasi);

        # cek apakah sudah ada data tgl itu atau blm
        $count = DB::table('tb_dw')->where('id_apotek', $apotek->id)->where('tgl', $getIdIterasi['tgl'])->count();

        if($count > 0) {
            DB::table('tb_dw')->where('id_apotek', $apotek->id)->where('tgl', $getIdIterasi['tgl'])->delete();
        }

        $rekaps = DB::select('CALL getPersediaanPerTanggalApotekAwal(?, ?, ?, ?)', [$getIdIterasi['tgl'], $getIdIterasi['tgl'], 'tb_histori_stok_'.$inisial, 'tb_m_stok_harga_'.$inisial]);

       // dd($rekaps);

        $batchSize = 1000; // Ukuran batch yang bisa disesuaikan

        if(count($rekaps) > 0) {
            $dataToInsert = [];
            foreach ($rekaps as $rekap) {
                 $dataToInsert[] = [
                    'id_obat' => $rekap->id_obat,
                    'tgl' => $getIdIterasi['tgl'],
                    'stok_awal_' => $rekap->stok_awal_,
                    'stok_akhir_' => $rekap->stok_akhir_,
                    'id_awal' => $rekap->id_awal,
                    'id_akhir' => $rekap->id_akhir,
                    'hbppn' => $rekap->hbppn,
                    'harga_jual' => $rekap->harga_jual,
                    'id_apotek' => $apotek->id
                ];

                // Ketika batch penuh, lakukan insert
                if (count($dataToInsert) >= $batchSize) {
                    DB::table('tb_dw')->insert($dataToInsert);
                    // Kosongkan array setelah insert
                    $dataToInsert = [];
                }
            }

            // Insert sisa data yang mungkin belum terinsert
            if (count($dataToInsert) > 0) {
                DB::table('tb_dw')->insert($dataToInsert);
            }
        }

        echo 0;
    }

    public function reload_dw_pj(Request $request){
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $tgl_awal = $request->tgl_awal;
        $tgl_akhir = $request->tgl_akhir;

        $iterasi = $request->iterasi;
        $getIdIterasi = $this->getTanggalByIterasi($tgl_awal, $tgl_awal, $iterasi);

        $rekaps = DB::select('CALL getHppPerTanggalApotek(?, ?)', [$getIdIterasi['tgl'], $apotek->id]);

        if(count($rekaps) > 0) {
            foreach ($rekaps as $rekap) {
                $total_penjualan_final = $rekap->total_fix-$rekap->total_diskon_fix;
                $laba = $total_penjualan_final-$rekap->total_hbppn_fix;
                DB::table('tb_dw')->where('id_apotek', $apotek->id)->where('tgl', $getIdIterasi['tgl'])->where('id_obat', $rekap->id_obat)->update([
                        'total_jual' => $rekap->jumlah_fix,
                        'total_retur' => $rekap->total_retur,
                        'total_penjualan' => $rekap->total_fix,
                        'total_diskon' => $rekap->total_diskon_fix,
                        'total_penjualan_hpp' => $rekap->total_hbppn_fix,
                        'total_penjualan_final' => $total_penjualan_final,
                        'laba' => $laba
                    ]);
            }
        }

        echo 0;
    }

    public function reload_dw_pb(Request $request){
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $tgl_awal = $request->tgl_awal;
        $tgl_akhir = $request->tgl_akhir;

        $iterasi = $request->iterasi;
        $getIdIterasi = $this->getTanggalByIterasi($tgl_awal, $tgl_awal, $iterasi);

        $rekaps = DB::select('CALL getPembelianPerTanggalApotek(?, ?)', [$getIdIterasi['tgl'], $apotek->id]);
        $batchSize = 1000; // Ukuran batch yang bisa disesuaikan

        if(count($rekaps) > 0) {
            $dataToInsert = [];
            foreach ($rekaps as $rekap) {
                $total_diskon_pembelian = $rekap->diskon+$rekap->total_diskon_persen;
                $total_pembelian_final = $rekap->total_pembelian-($total_diskon_pembelian+$rekap->total_retur)+$total_ppn;
                DB::table('tb_dw')->where('id_apotek', $apotek->id)->where('tgl', $getIdIterasi['tgl'])->where('id_obat', $rekap->id_obat)->update([
                        'total_beli' => $rekap->jumlah_fix,
                        'total_pembelian' => $rekap->total_pembelian,
                        'total_diskon_pembelian' => $total_diskon_pembelian,
                        'total_retur_pembelian' => $rekap->total_retur,
                        'total_lunas' => $rekap->total_lunas,
                        'total_ppn' => $total_ppn,
                        'total_pembelian_final' => $total_pembelian_final
                    ]);
            }
        }

        echo 0;
    }


    public function reload_dw(Request $request) {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $tgl_awal = $request->tgl_awal;
        $tgl_akhir = $request->tgl_akhir;

        $iterasi = $request->iterasi;
        $getIdIterasi = $this->getTanggalByIterasi($tgl_awal, $tgl_awal, $iterasi);
        
        $collection = collect();
      
        if ($iterasi == 0) {
            // Menambahkan header
          //  $header = ['No', 'ID', 'Barcode', 'Nama Obat', 'Stok Awal', 'Stok Akhir', 'HB+PPN', 'Harga Jual', 'Penjualan', 'Retur', 'Pembelian', 'T.Keluar', 'T.Masuk', 'T.Operasional'];
        } 
       
        //$data_all = Cache::get('persediaan_'.$request->tgl_awal.'_'.$request->tgl_akhir.'_'.Auth::user()->id.'_rekaps_all_'.$apotek->id);

        //$rekaps = DB::select('CALL getPersediaanPerTanggalApotek(?, ?, ?, ?, ?, ?, ?)', [$tgl_awal, $tgl_akhir, 'tb_histori_stok_'.$inisial, 'tb_m_stok_harga_'.$inisial, $getIdIterasi['id_awal'], $getIdIterasi['id_akhir'], $apotek->id]);
       
        //$x = 0;

        
        foreach($rekaps as $rekap) {
            $x++;

            if($rekap->stok_awal_ == 0) {
                $stok_awal = '0';
            } else {
                $stok_awal = $rekap->stok_awal_;
            }

            if($rekap->stok_akhir_ == 0) {
                $stok_akhir = '0';
            } else {
                $stok_akhir = $rekap->stok_akhir_;
            }

            if($rekap->hbppn == 0) {
                $hbppn = '0';
            } else {
                $hbppn = $rekap->hbppn;
            }

            if($rekap->harga_jual == 0) {
                $harga_jual = '0';
            } else {
                $harga_jual = $rekap->harga_jual;
            }

           /* $penjualan = DB::select('SELECT getJumlahItemPenjualan(?, ?, ?, ?) AS total_jual', [$apotek->id, $tgl_awal, $tgl_akhir, $rekap->id_obat]);
            $pembelian =  DB::select('SELECT getJumlahItemPembelian(?, ?, ?, ?) AS total_beli', [$apotek->id, $tgl_awal, $tgl_akhir, $rekap->id_obat]);
            $toMasuk = DB::select('SELECT getJumlahItemTOMasuk(?, ?, ?, ?) AS total_to_masuk', [$apotek->id, $tgl_awal, $tgl_akhir, $rekap->id_obat]);
            $toKeluar =  DB::select('SELECT getJumlahItemTOKeluar(?, ?, ?, ?) AS total_to_keluar', [$apotek->id, $tgl_awal, $tgl_akhir, $rekap->id_obat]);
            $penjualanRetur =  DB::select('SELECT getJumlahItemPenjualanRetur(?, ?, ?, ?) AS total_retur', [$apotek->id, $tgl_awal, $tgl_akhir, $rekap->id_obat]);*/

            $jum_penjualan = '0';
            $jum_pembelian = '0';
            $jum_to_masuk  = '0';
            $jum_to_keluar = '0';
            $jum_retur = '0';
            $jum_po = '0';

            if($rekap->total_jual != null) {
                $jum_penjualan = $rekap->total_jual;
            }

            if($rekap->total_beli != null) {
                $jum_pembelian = $rekap->total_beli;
            }

            if($rekap->total_to_masuk != null) {
                $jum_to_masuk = $rekap->total_to_masuk;
            }

            if($rekap->total_to_keluar != null) {
                $jum_to_keluar = $rekap->total_to_keluar;
            }

            if($rekap->total_retur != null) {
                $jum_retur = $rekap->total_retur;
            }

            if($rekap->total_po != null) {
                $jum_po = $rekap->total_po;
            }
            //collection[]
            $row = array(
                $x, //a
                $rekap->id_obat, //b
                $rekap->barcode, //b
                $rekap->nama, //c
                $stok_awal, //d
                $stok_akhir,
                $hbppn,
                $harga_jual,
                $jum_penjualan,
                $jum_pembelian,
                $jum_to_masuk,
                $jum_to_keluar,
                $jum_retur,
                $jum_po
            );

            $writer->addRow(WriterEntityFactory::createRowFromArray($row));
        }

        //if($request->iterasi_last == $iterasi) {
            $writer->close();
        //}



        /*if(isset($data_all)) {
            $mergedCollection = $data_all->merge($collection);
        } else {
            $mergedCollection = $collection;
        }
        $expiresAt = now()->addDay(1);
        Cache::put('persediaan_'.$request->tgl_awal.'_'.$request->tgl_akhir.'_'.Auth::user()->id.'_rekaps_all_'.$apotek->id, $mergedCollection, $expiresAt);*/
        echo 0;
    }

    public function reload_export_persediaan(Request $request) {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $iterasi = $request->iterasi+1;
        $getIdIterasi = $this->getIdIterasi($iterasi);
        $tgl_awal = $request->tgl_awal;
        $tgl_akhir = $request->tgl_akhir;
        $collection = array();
        
        // membuat tabel untuk menampung data
        if (Schema::hasTable('tb_temp_persediaan_'.$inisial.'_'.Auth::user()->id.'')) {
            if($iterasi == 1) {
                $tableName = 'tb_temp_persediaan_' . $inisial . '_' . Auth::user()->id;
                \DB::statement('TRUNCATE TABLE ' . $tableName);
            }
        } else {
            \DB::statement('CREATE TABLE tb_temp_persediaan_'.$inisial.'_'.Auth::user()->id.' LIKE sample_persediaan');
        }
        
        $rekaps = DB::select('CALL getPersediaanPerTanggalApotek(?, ?, ?, ?, ?, ?, ?)', [$tgl_awal, $tgl_akhir, 'tb_histori_stok_'.$inisial, 'tb_m_stok_harga_'.$inisial, $getIdIterasi['id_awal'], $getIdIterasi['id_akhir'], $apotek->id]);
       
        $x = 0;

        
        foreach($rekaps as $rekap) {
            $x++;

            if($rekap->stok_awal_ == 0) {
                $stok_awal = '0';
            } else {
                $stok_awal = $rekap->stok_awal_;
            }

            if($rekap->stok_akhir_ == 0) {
                $stok_akhir = '0';
            } else {
                $stok_akhir = $rekap->stok_akhir_;
            }

            if($rekap->hbppn == 0) {
                $hbppn = '0';
            } else {
                $hbppn = $rekap->hbppn;
            }

            if($rekap->harga_jual == 0) {
                $harga_jual = '0';
            } else {
                $harga_jual = $rekap->harga_jual;
            }

           /* $penjualan = DB::select('SELECT getJumlahItemPenjualan(?, ?, ?, ?) AS total_jual', [$apotek->id, $tgl_awal, $tgl_akhir, $rekap->id_obat]);
            $pembelian =  DB::select('SELECT getJumlahItemPembelian(?, ?, ?, ?) AS total_beli', [$apotek->id, $tgl_awal, $tgl_akhir, $rekap->id_obat]);
            $toMasuk = DB::select('SELECT getJumlahItemTOMasuk(?, ?, ?, ?) AS total_to_masuk', [$apotek->id, $tgl_awal, $tgl_akhir, $rekap->id_obat]);
            $toKeluar =  DB::select('SELECT getJumlahItemTOKeluar(?, ?, ?, ?) AS total_to_keluar', [$apotek->id, $tgl_awal, $tgl_akhir, $rekap->id_obat]);
            $penjualanRetur =  DB::select('SELECT getJumlahItemPenjualanRetur(?, ?, ?, ?) AS total_retur', [$apotek->id, $tgl_awal, $tgl_akhir, $rekap->id_obat]);*/

            $jum_penjualan = '0';
            $jum_pembelian = '0';
            $jum_to_masuk  = '0';
            $jum_to_keluar = '0';
            $jum_retur = '0';
            $jum_po = '0';

            if($rekap->total_jual != null) {
                $jum_penjualan = $rekap->total_jual;
            }

            if($rekap->total_beli != null) {
                $jum_pembelian = $rekap->total_beli;
            }

            if($rekap->total_to_masuk != null) {
                $jum_to_masuk = $rekap->total_to_masuk;
            }

            if($rekap->total_to_keluar != null) {
                $jum_to_keluar = $rekap->total_to_keluar;
            }

            if($rekap->total_retur != null) {
                $jum_retur = $rekap->total_retur;
            }

            if($rekap->total_po != null) {
                $jum_po = $rekap->total_po;
            }
            //collection[]
            $row = array(
                'tgl_awal' => $tgl_awal,
                'tgl_akhir' => $tgl_akhir,
                'id_obat' => $rekap->id_obat, //b
                'stok_awal' => $stok_awal, //d
                'stok_akhir' => $stok_akhir,
                'hbppn' => $hbppn,
                'harga_jual' => $harga_jual,
                'total_penjualan' => $jum_penjualan,
                'total_pembelian' => $jum_pembelian,
                'total_to_keluar' => $jum_to_keluar,
                'total_to_masuk' => $jum_to_masuk,
                'total_penjualan_retur' => $jum_retur,
                'total_po' => $jum_po,
                'id_apotek' => $apotek->id
            );

            $collection[] = $row;
        }

        DB::table('tb_temp_persediaan_'.$inisial.'_'.Auth::user()->id.'')->insertOrIgnore($collection);
   
        echo 0;
    }

    public function clear_cache_persediaan(Request $request) {
        // $apotek = MasterApotek::find(session('id_apotek_active'));
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        /*Cache::forget('persediaan_'.$request->tgl_awal.'_'.$request->tgl_akhir.'_'.Auth::user()->id.'_rekaps_all_'.$apotek->id);*/

       /* if(env('APP_ENV') == 'local') {
            $tempFilePath = storage_path('app/temp_inventory.xlsx');
        } else {*/
           // $tempFilePath = storage_path('app/temp_inventory_'.$apotek->nama_singkat.'_'.Auth::user()->id.'.xlsx');
       // }

        // Hapus file setelah pengiriman selesai
        /*if (file_exists($tempFilePath)) {
            unlink($tempFilePath); // Menghapus file secara manual
        }*/

        // Nama tabel dinamis
        $tableName = 'tb_temp_persediaan_' . $inisial . '_' . Auth::user()->id;

        // Cek apakah tabel ada
        if (Schema::hasTable($tableName)) {
            // Hapus tabel jika ada
            Schema::dropIfExists($tableName);
        }
    }

    public function export_persediaan(Request $request) 
    {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);

        $tgl_awal = $request->tgl_awal;
        $tgl_akhir = $request->tgl_akhir;

        /*$subquery = DB::table('tb_m_obat as a')
            ->leftJoin('tb_temp_persediaan_'.$inisial.'_'.Auth::user()->id.' as b', 'b.id_obat', '=', 'a.id')
            ->select('a.id as id_master', 'b.id_obat');

        // Subquery to get id_master where id_obat is null
        $query = DB::table(DB::raw("({$subquery->toSql()}) as t1"))
            ->whereNull('t1.id_obat')
            ->select('t1.id_master')
            ->get();

        // Convert query result to array of id_master
        $id = $query->pluck('id_master')->toArray(); // Pluck id_master column

        // Query to get the MasterObat data
        $obats = MasterObat::select([
                'id as id_obat', 
                'nama as nama', 
                'barcode as barcode', 
                DB::raw('0 as stok_awal'), 
                DB::raw('0 as stok_akhir'), 
                'harga_beli as hbppn', 
                'harga_jual as harga_jual', 
                DB::raw('0 as total_penjualan'), 
                DB::raw('0 as total_penjualan_retur'), 
                DB::raw('0 as total_pembelian'), 
                DB::raw('0 as total_to_keluar'), 
                DB::raw('0 as total_to_masuk'), 
                DB::raw('0 as total_po')
            ])
            ->whereIn('id', $id) // Use array of id_master in whereIn
            ->get()
            ->toArray();

        if(count($obats) > 0) {
            DB::table('tb_temp_persediaan_'.$inisial.'_'.Auth::user()->id)->insert($obats);
        } */

        DB::statement(DB::raw('set @rownum = 0'));
        $getData = DB::table('tb_m_obat as b')
            ->select([
                DB::raw('@rownum  := @rownum  + 1 AS no'), 
                'b.id as id_obat',
                'b.nama',
                'b.barcode',
                'a.stok_awal',
                'a.stok_akhir',
                DB::raw('IFNULL(a.hbppn, b.harga_beli) as hbppn'),
                DB::raw('IFNULL(a.harga_jual, b.harga_jual) as harga_jual'),
                DB::raw('IFNULL(a.total_penjualan, 0) as total_penjualan'),
                DB::raw('IFNULL(a.total_penjualan_retur, 0) as total_penjualan_retur'),
                DB::raw('IFNULL(a.total_pembelian, 0) as total_pembelian'),
                DB::raw('IFNULL(a.total_to_keluar, 0) as total_to_keluar'),
                DB::raw('IFNULL(a.total_to_masuk, 0) as total_to_masuk'),
                DB::raw('IFNULL(a.total_po, 0) as total_po')
            ])
            ->leftjoin('tb_temp_persediaan_'.$inisial.'_'.Auth::user()->id.' as a', 'b.id', '=', 'a.id_obat')
            ->orderBy('b.id', 'ASC')
            ->get();

        $no = 0;
        $collection = collect();
        // Memproses hasil query dan menambahkan ke Collection
        $getData->each(function ($obj) use (&$collection, &$no) {
            $no++;
            $collection->push([
                $obj->no,
                $obj->id_obat,
                $obj->nama,
                $obj->barcode,
                $obj->stok_awal,
                $obj->stok_akhir,
                $obj->hbppn,
                $obj->harga_jual,
                $obj->total_penjualan,
                $obj->total_penjualan_retur,
                $obj->total_pembelian,
                $obj->total_to_keluar,
                $obj->total_to_masuk,
                $obj->total_po,
            ]);
        });

        //$collection = Cache::get('persediaan_'.$request->tgl_awal.'_'.$request->tgl_akhir.'_'.Auth::user()->id.'_rekaps_all_'.$apotek->id);
        $now = date('YmdHis'); // WithColumnFormatting
        $tgl_awal = date('Ymd', strtotime($request->tgl_awal));
        $tgl_akhir = date('Ymd', strtotime($request->tgl_akhir));
        //dd($tgl_awal);
        $nama = "Persediaan_".$apotek->nama_singkat."_".$tgl_awal."-sd-".$tgl_akhir."_".$now.".xlsx";
        return Excel::download(new class($collection) implements FromCollection, WithHeadings, WithColumnWidths, WithStyles {

                    public function __construct($collection)
                    {
                        $this->collection = $collection;
                    }

                    public function headings(): array
                    {
                         return [
                                'No', // a
                                'ID', // b 
                                'Barcode', // c
                                'Nama Obat',  //d
                                'Stok Awal', //e
                                'Stok Akhir', //f
                                'HB+PPN', //g
                                'Harga Jual', //h
                                'Penjualan', //i
                                'Retur', //j
                                'Pembelian', //k
                                'T.Keluar', //l
                                'T.Masuk', //m
                                'T.Operasional', //n
                               
                            ];

                       /* return [
                                'No', // a
                                'ID', // b 
                                'Barcode', // c
                                'Nama Obat',  //d
                                'Stok Awal', //e
                                'Penjualan', //f
                                'Retur', //g
                                'Pembelian', //h
                                'T.Keluar', //i
                                'T.Masuk', //j
                                'P.Plus', //k
                                'P.Min', //l
                                'Stok Akhir',  //m
                                'Firt SO', //n
                                'Last SO', //o
                                'Harga Pokok', //p
                                'Harga Jual', //q
                                'Keterangan' //r
                            ];*/
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
                            'B' => 8,
                            'C' => 15,
                            'D' => 45,
                            'E' => 15,
                            'F' => 15,
                            'G' => 15,
                            'H' => 15,
                            'I' => 20,
                            'J' => 20,
                            'K' => 20,
                            'L' => 20,
                            'M' => 15,
                            'N' => 10,
                            /*'O' => 10,
                            'P' => 15,
                            'Q' => 15,
                            'R' => 70,*/
                        ];
                    }

                    public function styles(Worksheet $sheet)
                    {
                        return [
                            1    => ['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'A'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'B'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'E'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'F'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'G'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'H'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'I'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'J'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'K'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'L'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'N'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            /*'P'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                            'Q'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],*/
                        ];
                    }


                    public function collection()
                    {
                        return $this->collection;
                    }
        },$nama);
    }

    public function reload_data_pembelian() {
        $det_pembelians = DB::table('tb_detail_nota_pembelian') 
                                ->select([
                                    'tb_detail_nota_pembelian.*', 
                                    'tb_nota_pembelian.ppn',
                                    DB::raw('CAST(
                                      (
                                        (jumlah * harga_beli) - (
                                          diskon + (
                                            diskon_persen / 100 * (jumlah * harga_beli)
                                          )
                                        )
                                      ) AS DECIMAL (16, 0)
                                    ) AS total_new'),
                                    DB::raw('CAST(total_harga AS DECIMAL (16, 0)) AS total_harga_')
                                ])
                                ->join('tb_nota_pembelian', 'tb_nota_pembelian.id', '=', 'tb_detail_nota_pembelian.id_nota');

        $data_ = DB::table(DB::raw("({$det_pembelians->toSql()}) as x"))
            ->select(['*'])
            ->whereRaw('total_new > total_harga_')
            ->get();

        $i = 0;
        foreach ($data_ as $key => $val) {
            $hb_ppn = $val->harga_beli+(($val->ppn/100)*$val->harga_beli);
            DB::table('tb_detail_nota_pembelian')
                ->where('id', $val->id)
                ->update(['total_harga' => $val->total_new, 'harga_beli_ppn' => $hb_ppn]);
            $i++;
        }

        echo "reload data sebanyak ".$i;
    }


    public function reload_data_histori() {
        $id_apotek = 1;
        $apotek = MasterApotek::find($id_apotek);
        $inisial = strtolower($apotek->nama_singkat);
        $det_historis = DB::table('tb_histori_stok_'.$inisial.'')->whereIn('id_jenis_transaksi', [1, 2, 3, 4, 14, 15, 16, 17])->get();
        $i = 0;
        foreach ($det_historis as $key => $val) {
            DB::table('tb_histori_all_'.$inisial.'')
                ->where('id_obat', $val->id_obat)
                ->where('id_jenis_transaksi', $val->id_jenis_transaksi)
                ->where('id_transaksi', $val->id_transaksi)
                ->whereDate('created_at', $val->created_at)
                ->update(['stok_awal' => $val->stok_akhir, 'stok_akhir' => $val->stok_akhir]);
            $i++;
        }
        echo "reload data sebanyak ".$i;
    }

    public function sycn_harga_obat_tahap_satu(Request $request, $id) {
        $obat = MasterObat::select(['id'])->orderBy('id', 'DESC')->first();
        $last_id_obat = $obat->id;
        $last_id_obat_ex = 0;
        $id_apotek = $id;
        $cek = DB::table('tb_bantu_update')->orderBy('id', 'DESC')->first();
        if(!empty($cek)) {
            $last_id_obat_ex = $cek->last_id_obat_after;
            if($last_id_obat_ex >= $last_id_obat) {
                $id_apotek = $cek->id_apotek+1;
            } else {
                $id_apotek = $cek->id_apotek;
            }
            $apotek = MasterApotek::find($cek->id_apotek);
            $inisial = strtolower($apotek->nama_singkat);
        } else {
            $apotek = MasterApotek::find($id_apotek);
            $inisial = strtolower($apotek->nama_singkat);
        }

        $last_id_obat_ex = $last_id_obat_ex+1;
        $last_id_obat_after = $last_id_obat_ex+200-1;
        DB::table('tb_bantu_update')
            ->insert(['last_id_obat_before' => $last_id_obat_ex, 'last_id_obat_after' => $last_id_obat_after, 'id_apotek' => $id_apotek]);
        
        $data = DB::table('tb_m_stok_harga_'.$inisial.'')->whereBetween('id_obat', [$last_id_obat_ex, $last_id_obat_after])->get();
        $i=0;
        $last_id_obat_after = 0;
        $data_ = array();
        foreach ($data as $key => $val) {
            # data pembelian obat keseluruhan
            $det_pembelians = DB::table('tb_detail_nota_pembelian') 
                                ->select(['tb_detail_nota_pembelian.*', 'tb_nota_pembelian.ppn'])
                                ->join('tb_nota_pembelian', 'tb_nota_pembelian.id', '=', 'tb_detail_nota_pembelian.id_nota')
                                ->where('id_obat', $val->id_obat)
                                ->where('id_apotek_nota', $apotek->id)
                                ->whereDate('created_at', '<', $now)
                                ->get();
            
            foreach ($det_pembelians as $key => $val) {
                $new_arr = array();
                $new_arr['id_obat'] = $val->id_obat;
                $new_arr['harga_beli'] = $val->harga_beli;
                $new_arr['ppn'] = $val->ppn;
                if($val->ppn != null AND $val->ppn > 0) {
                    $new_arr['harga_beli_ppn'] = $val->harga_beli+(($val->ppn/100)*$val->harga_beli);
                } else {
                    $new_arr['harga_beli_ppn'] = $val->harga_beli;
                }
                $new_arr['harga_jual'] = null;
                $new_arr['harga_transfer'] = null;
                $new_arr['jumlah'] = $val->jumlah;
                $new_arr['stok_awal'] = 0;
                $new_arr['stok_akhir'] = 0;
                $new_arr['id_jenis_transaksi'] = 2;
                $new_arr['id_transaksi'] = $val->id;
                $new_arr['batch'] = $val->id_batch;
                $new_arr['ed'] = $val->tgl_batch;
                $new_arr['created_at'] = $val->created_at; 
                $new_arr['created_by'] = $val->created_by;
                array_push($data_, $new_arr);
            }

            # data pembelian obat dihapus
            $det_pembelian_hapuss = DB::table('tb_detail_nota_pembelian')
                                ->select(['tb_detail_nota_pembelian.*', 'tb_nota_pembelian.ppn'])
                                ->join('tb_nota_pembelian', 'tb_nota_pembelian.id', '=', 'tb_detail_nota_pembelian.id_nota')
                                ->where('id_obat', $val->id_obat)
                                ->where('id_apotek_nota', $apotek->id)
                                ->where('tb_nota_pembelian.is_deleted', 1)
                                ->get();

            foreach ($det_pembelian_hapuss as $key => $val) {
                $new_arr = array();
                $new_arr['id_obat'] = $val->id_obat;
                $new_arr['harga_beli'] = $val->harga_beli;
                $new_arr['ppn'] = $val->ppn;
                if($val->ppn != null AND $val->ppn > 0) {
                    $new_arr['harga_beli_ppn'] = $val->harga_beli+(($val->ppn/100)*$val->harga_beli);
                } else {
                    $new_arr['harga_beli_ppn'] = $val->harga_beli;
                }
                $new_arr['harga_jual'] = null;
                $new_arr['harga_transfer'] = null;
                $new_arr['jumlah'] = $val->jumlah;
                $new_arr['stok_awal'] = 0;
                $new_arr['stok_akhir'] = 0;
                $new_arr['id_jenis_transaksi'] = 14;
                $new_arr['id_transaksi'] = $val->id;
                $new_arr['batch'] = $val->id_batch;
                $new_arr['ed'] = $val->tgl_batch;
                $new_arr['created_at'] = $val->created_at; 
                $new_arr['created_by'] = $val->created_by;
                array_push($data_, $new_arr);
            }

            # data pembelian obat retur
           /* $det_pembelian_returs = DB::table('tb_detail_nota_pembelian')
                                ->select(['tb_detail_nota_pembelian.*', 'tb_nota_pembelian.ppn'])
                                ->join('tb_nota_pembelian', 'tb_nota_pembelian.id', '=', 'tb_detail_nota_pembelian.id_nota')
                                ->where('id_obat', $val->id_obat)
                                ->where('id_apotek_nota', $apotek->id)
                                ->where('tb_detail_nota_pembelian.is_retur', 1)
                                ->get();

            foreach ($det_pembelian_returs as $key => $val) {
                $new_arr = array();
                $new_arr['id_obat'] = $val->id_obat;
                $new_arr['harga_beli'] = $val->harga_beli;
                $new_arr['ppn'] = $val->ppn;
                if($val->ppn != null AND $val->ppn > 0) {
                    $new_arr['harga_beli_ppn'] = $val->harga_beli+(($val->ppn/100)*$val->harga_beli);
                } else {
                    $new_arr['harga_beli_ppn'] = $val->harga_beli;
                }
                $new_arr['harga_jual'] = null;
                $new_arr['harga_transfer'] = null;
                $new_arr['jumlah'] = $val->jumlah;
                $new_arr['stok_awal'] = 0;
                $new_arr['stok_akhir'] = 0;
                $new_arr['id_jenis_transaksi'] = 26;
                $new_arr['id_transaksi'] = $val->id;
                $new_arr['batch'] = $val->id_batch;
                $new_arr['ed'] = $val->tgl_batch;
                $new_arr['created_at'] = $val->created_at; 
                $new_arr['created_by'] = $val->created_by;
                $data_[] = $new_arr;
            }*/

            // -----------------------------------------------------------------------------------
            # data penjualan obat keseluruhan
            $det_penjualans = DB::table('tb_detail_nota_penjualan')
                                ->select(['tb_detail_nota_penjualan.*'])
                                ->join('tb_nota_penjualan', 'tb_nota_penjualan.id', '=', 'tb_detail_nota_penjualan.id_nota')
                                ->where('id_obat', $val->id_obat)
                                ->where('id_apotek_nota', $apotek->id)
                                ->get();

            foreach ($det_penjualans as $key => $val) {
                $new_arr = array();
                $new_arr['id_obat'] = $val->id_obat;
                $new_arr['harga_beli'] = null;
                $new_arr['ppn'] = null;
                $new_arr['harga_beli_ppn'] = null;
                $new_arr['harga_jual'] = $val->harga_jual;
                $new_arr['harga_transfer'] = null;
                $new_arr['jumlah'] = $val->jumlah;
                $new_arr['stok_awal'] = 0;
                $new_arr['stok_akhir'] = 0;
                $new_arr['id_jenis_transaksi'] = 1;
                $new_arr['id_transaksi'] = $val->id;
                $new_arr['batch'] = null;
                $new_arr['ed'] = null;
                $new_arr['created_at'] = $val->created_at; 
                $new_arr['created_by'] = $val->created_by;
                array_push($data_, $new_arr);
            }

            # data penjualan obat dihapus
            $det_penjualan_hapuss = DB::table('tb_detail_nota_penjualan')
                                ->select(['tb_detail_nota_penjualan.*'])
                                ->join('tb_nota_penjualan', 'tb_nota_penjualan.id', '=', 'tb_detail_nota_penjualan.id_nota')
                                ->where('id_obat', $val->id_obat)
                                ->where('id_apotek_nota', $apotek->id)
                                ->where('tb_nota_penjualan.is_deleted', 1)
                                ->get();

            foreach ($det_penjualan_hapuss as $key => $val) {
                $new_arr = array();
                $new_arr['id_obat'] = $val->id_obat;
                $new_arr['harga_beli'] = null;
                $new_arr['ppn'] = null;
                $new_arr['harga_beli_ppn'] = null;
                $new_arr['harga_jual'] = $val->harga_jual;
                $new_arr['harga_transfer'] = null;
                $new_arr['jumlah'] = $val->jumlah;
                $new_arr['stok_awal'] = 0;
                $new_arr['stok_akhir'] = 0;
                $new_arr['id_jenis_transaksi'] = 15;
                $new_arr['id_transaksi'] = $val->id;
                $new_arr['batch'] = null;
                $new_arr['ed'] = null;
                $new_arr['created_at'] = $val->created_at; 
                $new_arr['created_by'] = $val->created_by;
                array_push($data_, $new_arr);
            }

            # data penjualan obat retur
            /*$det_penjualan_returs = DB::table('tb_detail_nota_penjualan')
                                ->select(['tb_detail_nota_penjualan.*'])
                                ->join('tb_nota_penjualan', 'tb_nota_penjualan.id', '=', 'tb_detail_nota_penjualan.id_nota')
                                ->where('id_obat', $val->id_obat)
                                ->where('id_apotek_nota', $apotek->id)
                                ->where('tb_detail_nota_penjualan.is_cn', 1)
                                ->get();

            foreach ($det_penjualan_returs as $key => $val) {
                $new_arr = array();
                $new_arr['id_obat'] = $val->id_obat;
                $new_arr['harga_beli'] = null;
                $new_arr['ppn'] = null;
                $new_arr['harga_beli_ppn'] = null;
                $new_arr['harga_jual'] = $val->harga_jual;
                $new_arr['harga_transfer'] = null;
                $new_arr['jumlah'] = $val->jumlah;
                $new_arr['stok_awal'] = 0;
                $new_arr['stok_akhir'] = 0;
                $new_arr['id_jenis_transaksi'] = 5;
                $new_arr['id_transaksi'] = $val->id;
                $new_arr['batch'] = null;
                $new_arr['ed'] = null;
                $new_arr['created_at'] = $val->created_at; 
                $new_arr['created_by'] = $val->created_by;
                $data_[] = $new_arr;
            }*/

            // -----------------------------------------------------------------------------------
            # data transfer masuk obat keseluruhan
            $det_transfer_masuks = DB::table('tb_detail_nota_transfer_outlet')
                                ->select(['tb_detail_nota_transfer_outlet.*'])
                                ->join('tb_nota_transfer_outlet', 'tb_nota_transfer_outlet.id', '=', 'tb_detail_nota_transfer_outlet.id_nota')
                                ->where('id_obat', $val->id_obat)
                                ->where('id_apotek_tujuan', $apotek->id)
                                ->get();

            foreach ($det_transfer_masuks as $key => $val) {
                $new_arr = array();
                $new_arr['id_obat'] = $val->id_obat;
                $new_arr['harga_beli'] = null;
                $new_arr['ppn'] = null;
                $new_arr['harga_beli_ppn'] = null;
                $new_arr['harga_jual'] = null;
                $new_arr['harga_transfer'] = $val->harga_outlet;
                $new_arr['jumlah'] = $val->jumlah;
                $new_arr['stok_awal'] = 0;
                $new_arr['stok_akhir'] = 0;
                $new_arr['id_jenis_transaksi'] = 3;
                $new_arr['id_transaksi'] = $val->id;
                $new_arr['batch'] = null;
                $new_arr['ed'] = null;
                $new_arr['created_at'] = $val->created_at; 
                $new_arr['created_by'] = $val->created_by;
                array_push($data_, $new_arr);
            }

            # data transfer masuk obat dihapus
            $det_transfer_masuk_hapuss = DB::table('tb_detail_nota_transfer_outlet')
                                ->select(['tb_detail_nota_transfer_outlet.*'])
                                ->join('tb_nota_transfer_outlet', 'tb_nota_transfer_outlet.id', '=', 'tb_detail_nota_transfer_outlet.id_nota')
                                ->where('id_obat', $val->id_obat)
                                ->where('id_apotek_tujuan', $apotek->id)
                                ->where('tb_nota_transfer_outlet.is_deleted', 1)
                                ->get();

            foreach ($det_transfer_masuk_hapuss as $key => $val) {
                $new_arr = array();
                $new_arr['id_obat'] = $val->id_obat;
                $new_arr['harga_beli'] = null;
                $new_arr['ppn'] = null;
                $new_arr['harga_beli_ppn'] = null;
                $new_arr['harga_jual'] = null;
                $new_arr['harga_transfer'] = $val->harga_outlet;
                $new_arr['jumlah'] = $val->jumlah;
                $new_arr['stok_awal'] = 0;
                $new_arr['stok_akhir'] = 0;
                $new_arr['id_jenis_transaksi'] = 16;
                $new_arr['id_transaksi'] = $val->id;
                $new_arr['batch'] = null;
                $new_arr['ed'] = null;
                $new_arr['created_at'] = $val->created_at; 
                $new_arr['created_by'] = $val->created_by;
                array_push($data_, $new_arr);
            }

            /*# data transfer masuk obat retur
            $det_transfer_masuk_returs = DB::table('tb_detail_nota_transfer_outlet')
                                ->join('tb_nota_transfer_outlet', 'tb_nota_transfer_outlet.id', '=', 'tb_detail_nota_transfer_outlet.id_nota')
                                ->where('id_obat', $val->id_obat)
                                ->where('id_apotek_tujuan', $apotek->id)
                                ->where('tb_nota_transfer_outlet.is_retur', 1)
                                ->get();*/

            // -----------------------------------------------------------------------------------
            # data transfer keluar obat keseluruhan
            $det_transfer_keluars = DB::table('tb_detail_nota_transfer_outlet')
                                ->select(['tb_detail_nota_transfer_outlet.*'])
                                ->join('tb_nota_transfer_outlet', 'tb_nota_transfer_outlet.id', '=', 'tb_detail_nota_transfer_outlet.id_nota')
                                ->where('id_obat', $val->id_obat)
                                ->where('id_apotek_nota', $apotek->id)
                                ->get();

            foreach ($det_transfer_keluars as $key => $val) {
                $new_arr = array();
                $new_arr['id_obat'] = $val->id_obat;
                $new_arr['harga_beli'] = null;
                $new_arr['ppn'] = null;
                $new_arr['harga_beli_ppn'] = null;
                $new_arr['harga_jual'] = null;
                $new_arr['harga_transfer'] = $val->harga_outlet;
                $new_arr['jumlah'] = $val->jumlah;
                $new_arr['stok_awal'] = 0;
                $new_arr['stok_akhir'] = 0;
                $new_arr['id_jenis_transaksi'] = 4;
                $new_arr['id_transaksi'] = $val->id;
                $new_arr['batch'] = null;
                $new_arr['ed'] = null;
                $new_arr['created_at'] = $val->created_at; 
                $new_arr['created_by'] = $val->created_by;
                array_push($data_, $new_arr);
            }

            # data transfer keluar obat dihapus
            $det_transfer_keluar_hapuss = DB::table('tb_detail_nota_transfer_outlet')
                                ->select(['tb_detail_nota_transfer_outlet.*'])
                                ->join('tb_nota_transfer_outlet', 'tb_nota_transfer_outlet.id', '=', 'tb_detail_nota_transfer_outlet.id_nota')
                                ->where('id_obat', $val->id_obat)
                                ->where('id_apotek_nota', $apotek->id)
                                ->where('tb_nota_transfer_outlet.is_deleted', 1)
                                ->get();

            foreach ($det_transfer_masuk_hapuss as $key => $val) {
                $new_arr = array();
                $new_arr['id_obat'] = $val->id_obat;
                $new_arr['harga_beli'] = null;
                $new_arr['ppn'] = null;
                $new_arr['harga_beli_ppn'] = null;
                $new_arr['harga_jual'] = null;
                $new_arr['harga_transfer'] = $val->harga_outlet;
                $new_arr['jumlah'] = $val->jumlah;
                $new_arr['stok_awal'] = 0;
                $new_arr['stok_akhir'] = 0;
                $new_arr['id_jenis_transaksi'] = 17;
                $new_arr['id_transaksi'] = $val->id;
                $new_arr['batch'] = null;
                $new_arr['ed'] = null;
                $new_arr['created_at'] = $val->created_at; 
                $new_arr['created_by'] = $val->created_by;
                array_push($data_, $new_arr);
            }

           /* # data transfer masuk obat retur
            $det_transfer_keluar_returs = DB::table('tb_detail_nota_transfer_outlet')
                                ->join('tb_nota_transfer_outlet', 'tb_nota_transfer_outlet.id', '=', 'tb_detail_nota_transfer_outlet.id_nota')
                                ->where('id_obat', $val->id_obat)
                                ->where('id_apotek_nota', $apotek->id)
                                ->where('tb_nota_transfer_outlet.is_retur', 1)
                                ->get();*/


            # history lain-lain 
            $det_historis = DB::table('tb_histori_stok_'.$inisial.'')->where('id_obat', $val->id_obat)->whereNotIn('id_jenis_transaksi', [1, 2, 3, 4, 14, 15, 16, 17])->get();

            foreach ($det_historis as $key => $val) {
                $new_arr = array();
                $new_arr['id_obat'] = $val->id_obat;
                $new_arr['harga_beli'] = null;
                $new_arr['ppn'] = null;
                $new_arr['harga_beli_ppn'] = null;
                $new_arr['harga_jual'] = null;
                $new_arr['harga_transfer'] = null;
                $new_arr['jumlah'] = $val->jumlah;
                $new_arr['stok_awal'] = $val->stok_awal;
                $new_arr['stok_akhir'] = $val->stok_akhir;
                $new_arr['id_jenis_transaksi'] = $val->id_jenis_transaksi;
                $new_arr['id_transaksi'] = $val->id_transaksi;
                $new_arr['batch'] = $val->batch;
                $new_arr['ed'] = $val->ed;
                $new_arr['created_at'] = $val->created_at; 
                $new_arr['created_by'] = $val->created_by;
                array_push($data_, $new_arr);
            }

            $i++;
            $last_id_obat_after = $val->id_obat;
        }

        if($i > 0) {
            DB::table('tb_histori_all_lv')
                ->insert($data_);

            DB::table('tb_bantu_update')
                ->insert(['last_id_obat_before' => $last_id_obat_ex, 'last_id_obat_after' => $last_id_obat_after]);

            echo 1;
        } else {
            echo 0;
        }
    }

    public function histori_all($id) {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $stok_harga = DB::table('tb_m_stok_harga_'.$inisial.'')->where('id_obat', $id)->first();
        $obat = MasterObat::find($id);
        $jenis_transasksis      = MasterJenisTransaksi::pluck('nama', 'id');
        $jenis_transasksis->prepend('-- Pilih Jenis Transaksi --','');

        return view('data_obat.histori_all')->with(compact('obat', 'stok_harga', 'jenis_transasksis'));
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 21/06/2020
        =======================================================================================
    */
    public function list_data_histori_all(Request $request)
    {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::statement(DB::raw('set @rownum = 0'));
        $data = DB::table('tb_histori_all_'.$inisial.'')->select([
                    DB::raw('@rownum  := @rownum  + 1 AS no'), 
                    'tb_histori_all_'.$inisial.'.*', 
                    'users.nama as oleh',
                    'tb_m_jenis_transaksi.nama as nama_transaksi',
                    'tb_m_jenis_transaksi.act'
                ])
                ->join('users', 'users.id', '=', 'tb_histori_all_'.$inisial.'.created_by')
                ->join('tb_m_jenis_transaksi', 'tb_m_jenis_transaksi.id', '=', 'tb_histori_all_'.$inisial.'.id_jenis_transaksi')
                ->where(function($query) use($request, $inisial){
                    $query->where('tb_histori_all_'.$inisial.'.id_obat', $request->id_obat);
                    $query->where('tb_histori_all_'.$inisial.'.id_jenis_transaksi','LIKE',($request->id_jenis_transaksi > 0 ? $request->id_jenis_transaksi : '%'.$request->id_jenis_transaksi.'%'));

                    if($request->tgl_awal != "") {
                        $tgl_awal       = date('Y-m-d H:i:s',strtotime($request->tgl_awal));
                        $query->whereDate('tb_histori_all_'.$inisial.'.created_at','>=', $tgl_awal);
                    }

                    if($request->tgl_akhir != "") {
                        $tgl_akhir      = date('Y-m-d H:i:s',strtotime($request->tgl_akhir));
                        $query->whereDate('tb_histori_all_'.$inisial.'.created_at','<=', $tgl_akhir);
                    }

                    $query->whereYear('tb_histori_all_'.$inisial.'.created_at', session('id_tahun_active'));
                })
                ->orderBy('created_at', 'ASC');
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request,$order_column,$order_dir, $inisial){
            $query->where(function($query) use($request, $inisial){
                $query->orwhere('tb_histori_all_'.$inisial.'.created_at','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('tb_histori_all_'.$inisial.'.batch','LIKE','%'.$request->get('search')['value'].'%');
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
                $check = TransaksiPembelianDetail::find($data->id_transaksi);
                $id_nota = ' | IDNota : '.$check->nota->id;
                $string = '<b>'.$check->nota->suplier->nama.'</b>';
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
            }

            if($string != '') {
                $string = '<br>'.$string;
            }

            return $data->nama_transaksi.$string.'<br>'.'IDdet : '.$data->id_transaksi.$id_nota; 
        }) 
        ->editcolumn('harga', function($data){
            $hb = $data->harga_beli;
            if($hb == null) {
                $hb = '-';
            }

            $ppn= $data->ppn.'%';
            if($data->ppn == null) {
                $ppn = '-';
            }

            $harga_beli_ppn= $data->harga_beli_ppn;
            if($harga_beli_ppn == null) {
                $harga_beli_ppn = '-';
            }

            $harga_jual= $data->harga_jual;
            if($harga_jual == null) {
                $harga_jual = '-';
            }

            $harga_transfer= $data->harga_transfer;
            if($harga_transfer == null) {
                $harga_transfer = '-';
            }

            $string = '<span style="font-size:9pt;">';
            $string.= 'HB     : '.$hb.'<br>';
            $string.= 'PPN    : '.$ppn.'<br>';
            $string.= 'HB+PPN : '.$harga_beli_ppn.'<br>';
            $string.= 'HJ     : '.$harga_jual.'<br>';
            $string.= 'HT     : '.$harga_transfer;
            $string.= '</span>';
            return $string; 
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
            $data_ = array(2, 12, 13, 26, 27);
            if (in_array($data->id_jenis_transaksi, $data_))
            {
                $batch = $data->batch;
            }
            return $ed;
        }) 
        ->editcolumn('created_by', function($data){
            if(strlen($data->oleh) > 15) {
                $trimstring = substr($data->oleh, 0, 15);
                $oleh = 'by '.$trimstring;
            } else {
                $oleh = 'by '.$data->oleh;
            }

            return strtolower($oleh);
        }) 
        ->rawColumns(['craeted_at', 'id_jenis_transaksi', 'masuk', 'keluar', 'stok_akhir', 'batch', 'ed', 'created_by', 'harga'])
        ->make(true);  
    }

    public function edit_harga_beli($id) {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        
        $obat = MasterObat::find($id);
        $sh = DB::table('tb_m_stok_harga_'.$inisial.'')->select(['*'])->where('id_obat', $obat->id)->first();

        return view('data_obat.edit_harga_beli')->with(compact('obat', 'sh'));
    }

    public function list_edit_harga_beli(Request $request)
    {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];


        $apoteker = User::find($apotek->id_apoteker);
        $id_user = Auth::user()->id;
        $id_role_active = session('id_role_active');
        $hak_akses = 0;
        if($apoteker->id == $id_user) {
            $hak_akses = 1;
        }

        if(Auth::user()->is_admin ==1) {
            $hak_akses = 1;
        }

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
                ->where(function($query) use($request, $inisial){
                    $query->where('tb_histori_stok_'.$inisial.'.id_obat', $request->id_obat);
                    $query->whereIn('tb_histori_stok_'.$inisial.'.id_jenis_transaksi', [2, 12, 13, 14, 26, 27, 30, 31, 3, 7, 16, 28, 29, 32, 33]);
                    if($request->tgl_awal != "") {
                        $tgl_awal       = date('Y-m-d H:i:s',strtotime($request->tgl_awal));
                        $query->whereDate('tb_histori_stok_'.$inisial.'.created_at','>=', $tgl_awal);
                    }

                    if($request->tgl_akhir != "") {
                        $tgl_akhir      = date('Y-m-d H:i:s',strtotime($request->tgl_akhir));
                        $query->whereDate('tb_histori_stok_'.$inisial.'.created_at','<=', $tgl_akhir);
                    }

                    //$query->whereYear('tb_histori_stok_'.$inisial.'.created_at', session('id_tahun_active'));
                })
                ->orderBy('created_at', 'ASC');
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request,$order_column,$order_dir, $inisial){
            $query->where(function($query) use($request, $inisial){
                $query->orwhere('tb_histori_stok_'.$inisial.'.created_at','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('tb_histori_stok_'.$inisial.'.batch','LIKE','%'.$request->get('search')['value'].'%');
            });
        })   
        ->editcolumn('created_at', function($data) use ($hak_akses){
            $data_pembelian_ = array(2, 12, 13, 14, 26, 27, 30, 31);
            $data_tf_masuk_ = array(3, 7, 16, 28, 29, 32, 33);
            $th  = date('Y', strtotime($data->created_at));
            if($th != date('Y')) {
                $btn = '<span class="label" onClick="gunakan_hb('.$data->id.', '.$data->id_obat
                .', '.$data->hb_ppn.', '.$data->hb_ppn.')" data-toggle="tooltip" data-placement="top" title="Gunakan ini" style="font-size:10pt;color:#0097a7;">[Terapkan]</span>';
            } else {
                if (in_array($data->id_jenis_transaksi, $data_pembelian_)) {
                    $check = TransaksiPembelianDetail::find($data->id_transaksi);
                    $id_nota = ' | IDNota : '.$check->nota->id;
                    $hb = $check->harga_beli;
                    $ppn = $check->nota->ppn;
                    $harga_beli_ppn = $check->harga_beli_ppn;
                    $harga_jual = 0;
                    $harga_transfer = 0;
                } else if (in_array($data->id_jenis_transaksi, $data_tf_masuk_)) {
                    $check = TransaksiTODetail::find($data->id_transaksi);
                    $hb = 0;
                    $ppn = 0;
                    $harga_beli_ppn = $check->harga_outlet;
                    $harga_jual = 0;
                    $harga_transfer = $check->harga_outlet;
                } 

                if($hak_akses == 1) {
                    $btn = '<span class="label" onClick="gunakan_hb('.$data->id.', '.$data->id_obat
                .', '.$hb.', '.$harga_beli_ppn.')" data-toggle="tooltip" data-placement="top" title="Gunakan ini" style="font-size:10pt;color:#0097a7;">[Terapkan]</span>';
                } else {
                    $btn = '';
                }
            }
            
            return date('d-m-Y', strtotime($data->created_at)).'<br>'.$btn; 
        }) 
        ->editcolumn('id_jenis_transaksi', function($data){
            $string = '';
            $id_nota = ''; 
            $data_pembelian_ = array(2, 12, 13, 14, 26, 27, 30, 31);
            $data_tf_masuk_ = array(3, 7, 16, 28, 29, 32, 33);

            $th  = date('Y', strtotime($data->created_at));
            if($th != date('Y')) {
                $string = '<br><b>DATA HISTORI</b>';
            } else {
                if (in_array($data->id_jenis_transaksi, $data_pembelian_)) {
                    $check = TransaksiPembelianDetail::find($data->id_transaksi);
                    $id_nota = ' | IDNota : '.$check->nota->id;
                    $string = '<b>'.$check->nota->suplier->nama.'</b>';
                } else if (in_array($data->id_jenis_transaksi, $data_tf_masuk_)) {
                    $check = TransaksiTODetail::find($data->id_transaksi);
                    $id_nota = ' | IDNota : '.$check->nota->id;
                    $string = '<b>Masuk dari '.$check->nota->apotek_asal->nama_singkat.'</b>';
                } 

                if($string != '') {
                    $string = '<br>'.$string;
                }
            }

            return $data->nama_transaksi.$string.'<br>'.'IDdet : '.$data->id_transaksi.$id_nota; 
        }) 
        ->editcolumn('harga', function($data){
            $string = '';
            $data_pembelian_ = array(2, 12, 13, 14, 26, 27, 30, 31);
            $data_tf_masuk_ = array(3, 7, 16, 28, 29, 32, 33);

            $th  = date('Y', strtotime($data->created_at));
            if($th != date('Y')) {
                $string.= '<b>DATA HISTORI</b>';
                $string.= '<br><span style="font-size:9pt;">';
                $string.= 'HB+PPN : '.$data->hb_ppn;
                $string.= '</span>';
            } else {
    
                if (in_array($data->id_jenis_transaksi, $data_pembelian_)) {
                    $check = TransaksiPembelianDetail::find($data->id_transaksi);
                    $id_nota = ' | IDNota : '.$check->nota->id;
                    $hb = $check->harga_beli;
                    $ppn = $check->nota->ppn;
                    $harga_beli_ppn = $check->harga_beli_ppn;
                    $harga_jual = null;
                    $harga_transfer = null;
                } else if (in_array($data->id_jenis_transaksi, $data_tf_masuk_)) {
                    $check = TransaksiTODetail::find($data->id_transaksi);
                    $hb = null;
                    $ppn = null;
                    $harga_beli_ppn = $check->harga_outlet;
                    $harga_jual = null;
                    $harga_transfer = $check->harga_outlet;
                } 


                if($hb == null) {
                    $hb = '-';
                }

               
                if($ppn == null) {
                    $ppn = '-';
                }

                if($harga_beli_ppn == null) {
                    $harga_beli_ppn = '-';
                }

                if($harga_jual == null) {
                    $harga_jual = '-';
                }

                if($harga_transfer == null) {
                    $harga_transfer = '-';
                }

                $string.= '<span style="font-size:9pt;">';
                $string.= 'HB     : '.$hb.'<br>';
                $string.= 'PPN    : '.$ppn.'<br>';
                $string.= 'HB+PPN : '.$harga_beli_ppn.'<br>';
                $string.= 'HJ     : '.$harga_jual.'<br>';
                $string.= 'HT     : '.$harga_transfer;
                $string.= '</span>';
            }

            return $string; 
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
            $data_ = array(2, 12, 13, 26, 27);
            if (in_array($data->id_jenis_transaksi, $data_))
            {
                $batch = $data->batch;
            }
            return $ed;
        }) 
        ->editcolumn('created_by', function($data){
            if(strlen($data->oleh) > 15) {
                $trimstring = substr($data->oleh, 0, 15);
                $oleh = 'by '.$trimstring;
            } else {
                $oleh = 'by '.$data->oleh;
            }

            return strtolower($oleh);
        }) 
        ->rawColumns(['craeted_at', 'id_jenis_transaksi', 'masuk', 'keluar', 'stok_akhir', 'batch', 'ed', 'created_by', 'harga', 'created_at'])
        ->make(true);  
    }

    public function gunakan_hb(Request $request) {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $stok_harga = DB::table('tb_m_stok_harga_'.$inisial.'')->where('id_obat', $request->id_obat)->first();

        $harga_sebelumnya = $stok_harga->harga_beli_ppn;

        if(DB::table('tb_m_stok_harga_'.$inisial.'')
                    ->where('id_obat', $request->id_obat)
                    ->update(['harga_beli' => $request->hb, 'harga_beli_ppn' => $request->hb_ppn, 'id_histori_hb' => $request->id, 'id_histori_hb_ppn' => $request->id])){

            DB::table('tb_histori_stok_'.$inisial.'')
                    ->where('id_obat', $request->id_obat)
                    ->where('sisa_stok', '>', 0)
                    ->where('hb_ppn', $harga_sebelumnya)
                    ->update(['hb_ppn' => $request->hb_ppn]);

            echo 1;
        } else {
            echo 0;
        }
    }

    public function edit_harga_beli_ppn($id) {
        $obat = MasterObat::find($id);

        return view('data_obat.edit_harga_beli_ppn')->with(compact('obat'));
    }

    public function list_edit_harga_beli_ppn(Request $request)
    {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        $apoteker = User::find($apotek->id_apoteker);
        $id_user = Auth::user()->id;
        $id_role_active = session('id_role_active');
        $hak_akses = 0;
        if($apoteker->id == $id_user) {
            $hak_akses = 1;
        }

        if(Auth::user()->is_admin ==1) {
            $hak_akses = 1;
        }


        DB::statement(DB::raw('set @rownum = 0'));
        $data = DB::table('tb_histori_all_'.$inisial.'')->select([
                    DB::raw('@rownum  := @rownum  + 1 AS no'), 
                    'tb_histori_all_'.$inisial.'.*', 
                    'users.nama as oleh',
                    'tb_m_jenis_transaksi.nama as nama_transaksi',
                    'tb_m_jenis_transaksi.act'
                ])
                ->join('users', 'users.id', '=', 'tb_histori_all_'.$inisial.'.created_by')
                ->join('tb_m_jenis_transaksi', 'tb_m_jenis_transaksi.id', '=', 'tb_histori_all_'.$inisial.'.id_jenis_transaksi')
                ->where(function($query) use($request, $inisial){
                    $query->where('tb_histori_all_'.$inisial.'.id_obat', $request->id_obat);
                    $query->whereIn('tb_histori_all_'.$inisial.'.id_jenis_transaksi', [2, 12, 13, 14, 26, 27, 30, 31, 3, 7, 16, 28, 29, 32, 33]);
                    $query->whereYear('tb_histori_all_'.$inisial.'.created_at', session('id_tahun_active'));
                })
                ->orderBy('created_at', 'ASC');
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request,$order_column,$order_dir, $inisial){
            $query->where(function($query) use($request, $inisial){
                $query->orwhere('tb_histori_all_'.$inisial.'.created_at','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('tb_histori_all_'.$inisial.'.batch','LIKE','%'.$request->get('search')['value'].'%');
            });
        })   
        ->editcolumn('created_at', function($data) use ($hak_akses){
            if($hak_akses == 1) {
                $btn = '<span class="label" onClick="gunakan_hb_ppn('.$data->id.', '.$data->id_obat.', '.$data->id_jenis_transaksi.', '.$data->harga_transfer.', '.$data->harga_beli_ppn.')" data-toggle="tooltip" data-placement="top" title="Gunakan ini" style="font-size:10pt;color:#0097a7;">[Terapkan]</span>';
            } else  {
                $btn = '';
            }
            return date('d-m-Y', strtotime($data->created_at)).'<br>'.$btn; 
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
                $check = TransaksiPembelianDetail::find($data->id_transaksi);
                $id_nota = ' | IDNota : '.$check->nota->id;
                $string = '<b>'.$check->nota->suplier->nama.'</b>';
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
            }

            if($string != '') {
                $string = '<br>'.$string;
            }

            return $data->nama_transaksi.$string.'<br>'.'IDdet : '.$data->id_transaksi.$id_nota; 
        }) 
        ->editcolumn('harga', function($data){
            $hb = $data->harga_beli;
            if($hb == null) {
                $hb = '-';
            }

            $ppn= $data->ppn.'%';
            if($data->ppn == null) {
                $ppn = '-';
            }

            $harga_beli_ppn= $data->harga_beli_ppn;
            if($harga_beli_ppn == null) {
                $harga_beli_ppn = '-';
            }

            $harga_jual= $data->harga_jual;
            if($harga_jual == null) {
                $harga_jual = '-';
            }

            $harga_transfer= $data->harga_transfer;
            if($harga_transfer == null) {
                $harga_transfer = '-';
            }

            $string = '<span style="font-size:9pt;">';
            $string.= 'HB     : '.$hb.'<br>';
            $string.= 'PPN    : '.$ppn.'<br>';
            $string.= 'HB+PPN : '.$harga_beli_ppn.'<br>';
            $string.= 'HJ     : '.$harga_jual.'<br>';
            $string.= 'HT     : '.$harga_transfer;
            $string.= '</span>';
            return $string; 
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
            $data_ = array(2, 12, 13, 26, 27);
            if (in_array($data->id_jenis_transaksi, $data_))
            {
                $batch = $data->batch;
            }
            return $ed;
        }) 
        ->editcolumn('created_by', function($data){
            if(strlen($data->oleh) > 15) {
                $trimstring = substr($data->oleh, 0, 15);
                $oleh = 'by '.$trimstring;
            } else {
                $oleh = 'by '.$data->oleh;
            }

            return strtolower($oleh);
        }) 
        ->rawColumns(['craeted_at', 'id_jenis_transaksi', 'masuk', 'keluar', 'stok_akhir', 'batch', 'ed', 'created_by', 'harga', 'created_at'])
        ->make(true);  
    }

    public function gunakan_hb_ppn(Request $request) {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $stok_harga = DB::table('tb_m_stok_harga_'.$inisial.'')->where('id_obat', $request->id_obat)->first();

        $data_pembelian_ = array(2, 12, 13, 14, 26, 27, 30, 31);
        $data_tf_masuk_ = array(3, 7, 16, 28, 29, 32, 33);
        if (in_array($request->id_jenis_transaksi, $data_pembelian_)) {
            $harga = $request->hb_ppn;
        } else if (in_array($request->id_jenis_transaksi, $data_tf_masuk_)) {
            $harga = $request->ht;
        } 

        $harga_sebelumnya = $stok_harga->harga_beli_ppn;

        if(DB::table('tb_m_stok_harga_'.$inisial.'')
                    ->where('id_obat', $request->id_obat)
                    ->update(['harga_beli_ppn' => $harga, 'id_histori_hb_ppn' => $request->id])){

            DB::table('tb_histori_stok_'.$inisial.'')
                    ->where('id_obat', $request->id_obat)
                    ->where('sisa_stok', '>', 0)
                    ->where('hb_ppn', $harga_sebelumnya)
                    ->update(['hb_ppn' => $harga]);

            echo 1;
        } else {
            echo 0;
        }
    }

    public function edit_harga_jual($id) {
        $obat = MasterObat::find($id);
        return view('data_obat.edit_harga_jual')->with(compact('obat'));
    }

    public function list_edit_harga_jual(Request $request)
    {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::statement(DB::raw('set @rownum = 0'));
        $data = DB::table('tb_histori_all_'.$inisial.'')->select([
                    DB::raw('@rownum  := @rownum  + 1 AS no'), 
                    'tb_histori_all_'.$inisial.'.*', 
                    'users.nama as oleh',
                    'tb_m_jenis_transaksi.nama as nama_transaksi',
                    'tb_m_jenis_transaksi.act'
                ])
                ->join('users', 'users.id', '=', 'tb_histori_all_'.$inisial.'.created_by')
                ->join('tb_m_jenis_transaksi', 'tb_m_jenis_transaksi.id', '=', 'tb_histori_all_'.$inisial.'.id_jenis_transaksi')
                ->where(function($query) use($request, $inisial){
                    $query->where('tb_histori_all_'.$inisial.'.id_obat', $request->id_obat);
                    $query->whereIn('tb_histori_all_'.$inisial.'.id_jenis_transaksi', [1, 5, 6, 15]);
                    if($request->tgl_awal != "") {
                        $tgl_awal       = date('Y-m-d H:i:s',strtotime($request->tgl_awal));
                        $query->whereDate('tb_histori_all_'.$inisial.'.created_at','>=', $tgl_awal);
                    }

                    if($request->tgl_akhir != "") {
                        $tgl_akhir      = date('Y-m-d H:i:s',strtotime($request->tgl_akhir));
                        $query->whereDate('tb_histori_all_'.$inisial.'.created_at','<=', $tgl_akhir);
                    }

                    $query->whereYear('tb_histori_all_'.$inisial.'.created_at', session('id_tahun_active'));
                })
                ->orderBy('created_at', 'ASC');
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request,$order_column,$order_dir, $inisial){
            $query->where(function($query) use($request, $inisial){
                $query->orwhere('tb_histori_all_'.$inisial.'.created_at','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('tb_histori_all_'.$inisial.'.batch','LIKE','%'.$request->get('search')['value'].'%');
            });
        })   
        ->editcolumn('created_at', function($data){
            $btn = '<span class="label" onClick="gunakan_hj('.$data->id.', '.$data->id_obat.', '.$data->harga_jual.')" data-toggle="tooltip" data-placement="top" title="Gunakan ini" style="font-size:10pt;color:#0097a7;">[Terapkan]</span>';
            return date('d-m-Y', strtotime($data->created_at)).'<br>'.$btn; 
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
                $check = TransaksiPembelianDetail::find($data->id_transaksi);
                $id_nota = ' | IDNota : '.$check->nota->id;
                $string = '<b>'.$check->nota->suplier->nama.'</b>';
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
            }

            if($string != '') {
                $string = '<br>'.$string;
            }

            return $data->nama_transaksi.$string.'<br>'.'IDdet : '.$data->id_transaksi.$id_nota; 
        }) 
        ->editcolumn('harga', function($data){
            $hb = $data->harga_beli;
            if($hb == null) {
                $hb = '-';
            }

            $ppn= $data->ppn.'%';
            if($data->ppn == null) {
                $ppn = '-';
            }

            $harga_beli_ppn= $data->harga_beli_ppn;
            if($harga_beli_ppn == null) {
                $harga_beli_ppn = '-';
            }

            $harga_jual= $data->harga_jual;
            if($harga_jual == null) {
                $harga_jual = '-';
            }

            $harga_transfer= $data->harga_transfer;
            if($harga_transfer == null) {
                $harga_transfer = '-';
            }

            $string = '<span style="font-size:9pt;">';
            $string.= 'HB     : '.$hb.'<br>';
            $string.= 'PPN    : '.$ppn.'<br>';
            $string.= 'HB+PPN : '.$harga_beli_ppn.'<br>';
            $string.= 'HJ     : '.$harga_jual.'<br>';
            $string.= 'HT     : '.$harga_transfer;
            $string.= '</span>';
            return $string; 
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
            $data_ = array(2, 12, 13, 26, 27);
            if (in_array($data->id_jenis_transaksi, $data_))
            {
                $batch = $data->batch;
            }
            return $ed;
        }) 
        ->editcolumn('created_by', function($data){
            if(strlen($data->oleh) > 15) {
                $trimstring = substr($data->oleh, 0, 15);
                $oleh = 'by '.$trimstring;
            } else {
                $oleh = 'by '.$data->oleh;
            }

            return strtolower($oleh);
        }) 
        ->rawColumns(['craeted_at', 'id_jenis_transaksi', 'masuk', 'keluar', 'stok_akhir', 'batch', 'ed', 'created_by', 'harga', 'created_at'])
        ->make(true);  
    }


    public function lihat_stok_tersedia($id) {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        
        $obat = MasterObat::find($id);
        $sh = DB::table('tb_m_stok_harga_'.$inisial.'')->select(['*'])->where('id_obat', $obat->id)->first();

        return view('data_obat.lihat_stok_tersedia')->with(compact('obat', 'sh'));
    }

    public function list_lihat_stok_tersedia(Request $request)
    {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        $histori_stok = HistoriStok::select([DB::raw('SUM(sisa_stok) as jum_sisa_stok'), DB::raw('SUM(sisa_stok*hb_ppn) as total')])
                            ->where('id_obat', $request->id_obat)
                            ->whereIn('id_jenis_transaksi', [2,3,11,9])
                            ->where('sisa_stok', '>', 0)
                            ->orderBy('id', 'ASC')
                            ->first();
        $avg = 0;
        if(!is_null($histori_stok)) {
            if($histori_stok->total!=0) {
                $avg = $histori_stok->total/$histori_stok->jum_sisa_stok;
            }
        }
        $avg = number_format($avg,2,".","");

        DB::statement(DB::raw('set @rownum = 0'));
        $data = DB::table('tb_histori_stok_'.$inisial.'')->select([DB::raw('@rownum  := @rownum  + 1 AS no'), 'tb_histori_stok_'.$inisial.'.*'])
                ->where(function($query) use($request, $inisial){
                    $query->where('tb_histori_stok_'.$inisial.'.id_obat', $request->id_obat);
                    $query->whereIn('tb_histori_stok_'.$inisial.'.id_jenis_transaksi', [2,3,11,9]);
                    
                    $query->where('tb_histori_stok_'.$inisial.'.sisa_stok',  '>', 0);
                })
                ->orderBy('id', 'ASC');
        
        $datatables = Datatables::of($data);
        return $datatables
        ->editcolumn('sisa_stok', function($data){
            return number_format($data->sisa_stok,0);
        }) 
        ->with([
            "avg" => $avg
        ]) 
        ->make(true);  
    }


    public function gunakan_hj(Request $request) {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $stok_harga = DB::table('tb_m_stok_harga_'.$inisial.'')->where('id_obat', $request->id_obat)->first();

        if(DB::table('tb_m_stok_harga_'.$inisial.'')
                    ->where('id_obat', $request->id_obat)
                    ->update(['harga_jual' => $request->hj, 'id_histori_hj' => $request->id])){
            echo 1;
        } else {
            echo 0;
        }
    }

    public function import_data(Request $request) {
        return view('data_obat._import_data');
    }

    public function import_obat_to_excel(Request $request)
    {
        /*if(Input::hasFile('import_file')){
            $path = $request->file('import_file');
            Excel::import(new HJStaticImport, $path);
            
        }*/
        
        //$apotek = MasterApotek::find(session('id_apotek_active'));
        //$inisial = strtolower($apotek->nama_singkat);
        $inisial = 'hw';

        $datas = DB::table('tb_bantu_sh_hw')->get();

        $data_update = 0;
        $data_skip = 0;
        foreach ($datas as $key => $val) {
            $cek = DB::table('tb_m_stok_harga_'.$inisial)->where('nama', 'LIKE', '%'.$val->nama.'%')->first();
            if($cek) {
                if($cek->harga_beli_ppn != 0) {
                    DB::table('tb_m_stok_harga_'.$inisial.'')
                    ->where('id', $cek->id)
                    ->update(['harga_beli_ppn' => $cek->harga_beli_ppn]);
                }

                if($cek->harga_jual != 0) {
                    DB::table('tb_m_stok_harga_'.$inisial.'')
                    ->where('id', $cek->id)
                    ->update(['harga_jual' => $cek->harga_jual]);
                }
                $data_update = $data_update+1;
            } else {
                $data_skip = $data_skip+1;
            }
        }

        echo $data_update;
        echo "-";
        echo $data_skip;

        
        /*session()->flash('success', 'Import data reviewer berhasil!');
        return redirect('/data_obat');*/
    }

    public function perbaikan_data(Request $request) {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);

        $data_ = DB::table('tb_histori_stok_'.$inisial.'')
                    ->where('id_obat', $request->id_obat)
                    ->orderBy('id', 'ASC')
                    ->get();

        # hapus data yang tidak perlu
        $id_skip = array();
        foreach ($data_ as $key => $val) {
            if (!in_array($val->id, $id_skip)) {
                $cek_ = DB::table('tb_histori_stok_'.$inisial.'')
                            ->where('id_obat', $request->id_obat)
                            ->where('id_jenis_transaksi', $val->id_jenis_transaksi)
                            ->where('id_transaksi', $val->id_transaksi)
                            ->where('jumlah', $val->jumlah)
                            ->where('id', '!=',  $val->id)
                            ->get();
                if(count($cek_) > 0) {
                    foreach ($cek_ as $x => $obj) {
                        DB::table('tb_histori_stok_'.$inisial.'')
                            ->where('id', $obj->id)
                            ->delete();
                        $id_skip[] = $obj->id;
                    }
                }
            }
        }

        # sesuaikan datanya
        $data_ = DB::table('tb_histori_stok_'.$inisial.'')
                    ->select(['tb_histori_stok_'.$inisial.'.*', 'a.act'])
                    ->join('tb_m_jenis_transaksi as a', 'a.id', '=', 'tb_histori_stok_'.$inisial.'.id_jenis_transaksi')
                    ->where('id_obat', $request->id_obat)
                    ->orderBy('id', 'ASC')
                    ->get();

        $stok_akhir = 0;
        $stok_awal = 0;
        $i=0;
        foreach ($data_ as $key => $val) {
            $i++;
            if($i != 0) {
                if($val->act == 1) {
                    # kurang stok
                    $jumlah = $val->jumlah;
                    $stok_awal = $stok_akhir;
                    $stok_akhir_new = $stok_akhir+$val->jumlah;

                    if($val->id_jenis_transaksi == 7) {
                        # Revisi Transfer Masuk
                        $last_ = DB::table('tb_histori_stok_'.$inisial.'')->where('id_transaksi', $val->id_transaksi)->whereIn('id_jenis_transaksi', [3,7])->orderBy('id', 'DESC')->where('id', '!=',  $val->id)->first();
                        if($last_->jumlah == $val->jumlah) {
                            $stok_awal = $stok_akhir;
                            $stok_akhir_new = $stok_akhir+0;
                        }
                    } else if($val->id_jenis_transaksi == 13) {
                        # Revisi Pembelian (Minus)
                        $last_ = DB::table('tb_histori_stok_'.$inisial.'')->where('id_transaksi', $val->id_transaksi)->whereIn('id_jenis_transaksi', [2,13])->orderBy('id', 'DESC')->where('id', '!=',  $val->id)->first();
                        if($last_->jumlah == $val->jumlah) {
                            $stok_awal = $stok_akhir;
                            $stok_akhir_new = $stok_akhir+0;
                        }
                    } else if($val->id_jenis_transaksi == 20)  {
                        # Revisi Penjualan Operasional (Minus)
                        $last_ = DB::table('tb_histori_stok_'.$inisial.'')->where('id_transaksi', $val->id_transaksi)->whereIn('id_jenis_transaksi', [18,20])->orderBy('id', 'DESC')->where('id', '!=',  $val->id)->first();
                        if($last_->jumlah == $val->jumlah) {
                            $stok_awal = $stok_akhir;
                            $stok_akhir_new = $stok_akhir+0;
                        }
                    }
                } else if($val->act == 2) {
                    # tambah stok
                    $jumlah = $val->jumlah;
                    $stok_awal = $stok_akhir;
                    $stok_akhir_new = $stok_akhir-$val->jumlah;

                    if($val->id_jenis_transaksi == 8) {
                        # Revisi Transfer keluar
                        $last_ = DB::table('tb_histori_stok_'.$inisial.'')->where('id_transaksi', $val->id_transaksi)->whereIn('id_jenis_transaksi', [3,8])->orderBy('id', 'DESC')->where('id', '!=',  $val->id)->first();
                        if($last_->jumlah == $val->jumlah) {
                            $stok_awal = $stok_akhir;
                            $stok_akhir_new = $stok_akhir-0;
                        }
                    } else if($val->id_jenis_transaksi == 12) {
                        # Revisi Pembelian (Plus)
                        $last_ = DB::table('tb_histori_stok_'.$inisial.'')->where('id_transaksi', $val->id_transaksi)->whereIn('id_jenis_transaksi', [2,12])->orderBy('id', 'DESC')->where('id', '!=',  $val->id)->first();
                        if($last_->jumlah == $val->jumlah) {
                           // $jumlah = 0;
                            $stok_awal = $stok_akhir;
                            $stok_akhir_new = $stok_akhir-0;
                        }
                    } else if($val->id_jenis_transaksi == 19) {
                        # Revisi Penjualan Operasional (Plus)
                        $last_ = DB::table('tb_histori_stok_'.$inisial.'')->where('id_transaksi', $val->id_transaksi)->whereIn('id_jenis_transaksi', [18,19])->orderBy('id', 'DESC')->where('id', '!=',  $val->id)->first();
                        if($last_->jumlah == $val->jumlah) {
                            $stok_awal = $stok_akhir;
                            $stok_akhir_new = $stok_akhir-0;
                        }
                    } else if($val->id_jenis_transaksi == 16) {
                        # Hapus Transfer Masuk (Double)
                        # cek ada gk history transaksi yang masuk dengan id tersebut
                        $cek = DB::table('tb_histori_stok_'.$inisial.'')->where('id_transaksi', $val->id_transaksi)->whereIn('id_jenis_transaksi', [7, 3])->orderBy('id', 'DESC')->where('id', '!=',  $val->id)->count();
                        if($cek < 1) {
                            $jumlah = $val->jumlah;
                            $stok_awal = $stok_akhir;
                            $stok_akhir_new = $stok_akhir+$val->jumlah;
                            DB::table('tb_histori_stok_'.$inisial.'')
                            ->where('id', $val->id)
                            ->delete();
                        }
                    }
                } else {
                    $jumlah = $val->jumlah;
                    $stok_awal = $stok_akhir;
                    $stok_akhir_new = $val->jumlah;
                }

                DB::table('tb_histori_stok_'.$inisial.'')->where('id', $val->id)->update(['stok_akhir' => $stok_akhir_new, 'stok_awal' => $stok_awal, 'jumlah' => $jumlah]);
                $stok_akhir = $stok_akhir_new;
                $stok_awal = $stok_awal;
            } else {
                $jumlah = $val->jumlah;
                $stok_akhir = $val->stok_akhir;
                $stok_awal = $val->stok_awal;
            }
        }

        if($i > 0) {
            DB::table('tb_m_stok_harga_'.$inisial.'')->where('id_obat', $request->id_obat)->update(['stok_akhir' => $stok_akhir, 'stok_awal' => $stok_awal]);
        }

        echo $i;
    }

    public function set_status_harga_outlet(Request $request)
    {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $stok = DB::table('tb_m_stok_harga_'.$inisial.'')
                ->where('id', $request->id)
                ->first();


        if($request->nilai == 1) {
            DB::table('tb_m_stok_harga_'.$inisial.'')
                ->where('id', $request->id)
                ->update(['is_status_harga' => $request->nilai, 'status_harga_by' => Auth::id(), 'status_harga_at' => date('Y-m-d H:i:s')]);
        } else {
            $obat = MasterObat::find($stok->id_obat);
            DB::table('tb_m_stok_harga_'.$inisial.'')
                ->where('id', $request->id)
                ->update(['is_status_harga' => $request->nilai, 'status_harga_by' => Auth::id(), 'status_harga_at' => date('Y-m-d H:i:s'), 'harga_jual' => $obat->harga_jual]);
        }

        echo 1;
    }

    public function reload_hpp_from_another_outlet() {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
      /*  $obats = MasterObat::where('is_deleted', 0)->get();*/
        $obats = DB::table('tb_m_stok_harga_'.$inisial.'')
                    ->where('harga_beli_ppn',0)
                    ->get();
        $i = 0;
        foreach ($obats as $key => $val) {
                $cek2 = DB::table('tb_m_stok_harga_pg')
                        ->where('id_obat', $val->id_obat)
                        ->first();

                if(!empty($cek2)) {
                    DB::table('tb_m_stok_harga_'.$inisial.'')
                    ->where('id_obat', $val->id_obat)
                    ->update(['harga_beli_ppn' => $cek2->harga_beli_ppn, 'updated_by' => Auth::id(), 'updated_at' => date('Y-m-d H:i:s')]);
                    $i++;
                }
                
        }

        echo $i;

    }

    public function reload_data_histori_transaksi(Request $request) {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $historis = DB::table('tb_histori_stok_'.$inisial)->where('id_obat', $request->id)->where('is_reload_histori', 0)->get();
        $data_pembelian_ = array(2, 12, 13);
        $data_tf_masuk_ = array(3, 7);
        $data_tf_keluar_ = array(4, 8);
        $data_penjualan_ = array(1, 6);
        $data_penjualan_op_ = array(18, 19, 20);
        $data_penyesuaian_ = array(9,10);
        $data_so_ = array(11);
        $data_po_ = array(18, 19, 20, 21);
        $data_td_ = array(22, 23, 24, 25);

        DB::table('tb_m_stok_harga_'.$inisial)
                        ->where('id_obat', $request->id)
                        ->update(['hb_ppn' => 0, 'hb_ppn_avg' => 0, 'is_reload_histori' => 0]);

        $i = 0;
        $last_stok = 0;
        foreach ($historis as $key => $data) {
            $i++;

            if (in_array($data->id_jenis_transaksi, $data_pembelian_)) {
                $check = TransaksiPembelianDetail::find($data->id_transaksi);

                # jika data pertama
                if($i == 1) {
                    $hb_ppn = $check->harga_beli_ppn;
                    $hb = $check->harga_beli;
                    $hb_ppn_avg = $hb_ppn;

                    # set hb_ppn di tb_histori_stok_{{apotek}} = hb_ppn
                    DB::table('tb_histori_stok_'.$inisial)
                        ->where('id', $data->id)
                        ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg]);

                    # set harga_beli_ppn di tb_m_stok_harga_{{apotek}} = hb_ppn & hb_ppn_avg = hb_ppn_avg
                    DB::table('tb_m_stok_harga_'.$inisial)
                        ->where('id_obat', $data->id_obat)
                        ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'hb' => $hb, 'is_reload_histori' => 1]);
                    $last_stok = $data->stok_akhir;
                } else {
                    $cek_obat_ = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $data->id_obat)->first();
                    $hb_ppn = $check->harga_beli_ppn;
                    $hb = $check->harga_beli;
                    $hb_ppn_avg = (($cek_obat_->hb_ppn_avg * $last_stok) + ($data->jumlah*$hb_ppn))/($data->jumlah + $last_stok);

                    # set hb_ppn di tb_histori_stok_{{apotek}} = hb_ppn
                    DB::table('tb_histori_stok_'.$inisial)
                        ->where('id', $data->id)
                        ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg]);

                    # set harga_beli_ppn di tb_m_stok_harga_{{apotek}} = hb_ppn & hb_ppn_avg = hb_ppn_avg
                    DB::table('tb_m_stok_harga_'.$inisial)
                        ->where('id_obat', $data->id_obat)
                        ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'hb' => $hb, 'is_reload_histori' => 1]);
                    $last_stok = $data->stok_akhir;
                }
            } else if (in_array($data->id_jenis_transaksi, $data_tf_masuk_)) {
                $check = TransaksiTODetail::find($data->id_transaksi);

                # jika data pertama
                if($i == 1) {
                    $hb_ppn = $check->harga_outlet;
                    $hb = $check->harga_outlet;
                    $hb_ppn_avg = $hb_ppn;

                    # set hb_ppn di tb_histori_stok_{{apotek}} = hb_ppn
                    DB::table('tb_histori_stok_'.$inisial)
                        ->where('id', $data->id)
                        ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg]);

                    # set harga_beli_ppn di tb_m_stok_harga_{{apotek}} = hb_ppn & hb_ppn_avg = hb_ppn_avg
                    DB::table('tb_m_stok_harga_'.$inisial)
                        ->where('id_obat', $data->id_obat)
                        ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'hb' => $hb, 'is_reload_histori' => 1]);
                    $last_stok = $data->stok_akhir;
                } else {
                    $cek_obat_ = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $data->id_obat)->first();
                    $hb_ppn = $check->harga_outlet;
                    $hb = $cek_obat_->harga_beli;
                    if($cek_obat_->harga_beli > $hb_ppn) {
                        $hb = $hb_ppn;
                    }
                    $hb_ppn_avg = (($cek_obat_->hb_ppn_avg * $last_stok) + ($data->jumlah*$hb_ppn))/($data->jumlah + $last_stok);
                    /*if($data->id==132294) {
                        echo $cek_obat_->hb_ppn_avg."*".$last_stok.'+'.$data->jumlah.'*'.$hb_ppn;
                        echo "</br>";
                        print_r($hb_ppn_avg);
                        exit();
                    }*/

                    # set hb_ppn di tb_histori_stok_{{apotek}} = hb_ppn
                    DB::table('tb_histori_stok_'.$inisial)
                        ->where('id', $data->id)
                        ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg]);

                    # set harga_beli_ppn di tb_m_stok_harga_{{apotek}} = hb_ppn & hb_ppn_avg = hb_ppn_avg
                    DB::table('tb_m_stok_harga_'.$inisial)
                        ->where('id_obat', $data->id_obat)
                        ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'hb' => $hb, 'is_reload_histori' => 1]);
                    $last_stok = $data->stok_akhir;
                }
            } else if (in_array($data->id_jenis_transaksi, $data_penjualan_)) {
                $check = TransaksiPenjualanDetail::find($data->id_transaksi);
                $cek_obat_ = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $data->id_obat)->first();

                # jika data pertama
                if($i == 1) {
                    $last_pembelian = TransaksiPembelianDetail::where('tb_detail_nota_pembelian.id_obat', $data->id_obat)
                                        ->join('tb_nota_pembelian as b','b.id','=','tb_detail_nota_pembelian.id_nota')
                                        ->whereDate('b.tgl_nota','<', $data->created_at)
                                        ->where('tb_detail_nota_pembelian.is_deleted', 0)
                                        ->where('b.is_deleted', 0)
                                        ->where('b.id_apotek_nota','=',$apotek->id)
                                        ->first();

                    if(!empty($last_pembelian)) {
                        $hb_ppn = $last_pembelian->harga_beli_ppn;
                        $hb = $last_pembelian->harga_beli;
                        $hb_ppn_avg = $hb_ppn;
                    } else {
                        $last_tf_masuk = TransaksiTODetail::where('tb_detail_nota_transfer_outlet.id_obat', $data->id_obat)
                                        ->join('tb_nota_transfer_outlet as b','b.id','=','tb_detail_nota_transfer_outlet.id_nota')
                                        ->whereDate('b.tgl_nota','<', $data->created_at)
                                        ->where('tb_detail_nota_transfer_outlet.is_deleted', 0)
                                        ->where('b.is_deleted', 0)
                                        ->where('b.id_apotek_tujuan','=',$apotek->id)
                                        ->first();

                        if(!empty($last_tf_masuk)) {
                            $hb_ppn = $last_tf_masuk->harga_outlet;
                            $hb = $cek_obat_->harga_beli;
                            if($cek_obat_->harga_beli > $hb_ppn) {
                                $hb = $hb_ppn;
                            }
                            $hb_ppn_avg = $hb_ppn;
                        } else {
                            $hb_ppn = $cek_obat_->harga_beli_ppn;
                            $hb = $cek_obat_->harga_beli;
                            if($cek_obat_->harga_beli > $hb_ppn) {
                                $hb = $hb_ppn;
                            }
                            $hb_ppn_avg = $hb_ppn;
                        }
                    } 

                    # set hb_ppn di tb_histori_stok_{{apotek}} = hb_ppn
                    DB::table('tb_histori_stok_'.$inisial)
                        ->where('id', $data->id)
                        ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg]);

                    # set harga_beli_ppn di tb_m_stok_harga_{{apotek}} = hb_ppn & hb_ppn_avg = hb_ppn_avg
                    DB::table('tb_m_stok_harga_'.$inisial)
                        ->where('id_obat', $data->id_obat)
                        ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'hb' => $hb, 'is_reload_histori' => 1]);

                    #set hb_ppn di tb_detail_nota_penjualan
                    $check->hb_ppn = $hb_ppn;
                    $check->is_reload_histori = 1;
                    $check->save();

                    $last_stok = $data->stok_akhir;
                } else {
                    $cek_obat_ = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $data->id_obat)->first();

                    # set hb_ppn di tb_histori_stok_{{apotek}} = hb_ppn
                    DB::table('tb_histori_stok_'.$inisial)
                        ->where('id', $data->id)
                        ->update(['hb_ppn' => $cek_obat_->hb_ppn_avg, 'hb_ppn_avg' => $cek_obat_->hb_ppn_avg]);

                    #set hb_ppn di tb_detail_nota_penjualan = hb_ppn_avg dari tb_m_stok_{{apotek}} 
                    $check->hb_ppn = $cek_obat_->hb_ppn_avg;
                    $check->is_reload_histori = 1;
                    $check->save();
                    $last_stok = $data->stok_akhir;
                }
            } else  if (in_array($data->id_jenis_transaksi, $data_tf_keluar_)) {
                $check = TransaksiTODetail::find($data->id_transaksi);

                # jika data pertama
                if($i == 1) {
                    $hb_ppn = $check->harga_outlet;
                    $hb = $hb_ppn;
                    $hb_ppn_avg = $hb_ppn;

                    # set hb_ppn di tb_histori_stok_{{apotek}} = hb_ppn
                    DB::table('tb_histori_stok_'.$inisial)
                        ->where('id', $data->id)
                        ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg]);

                    # set harga_beli_ppn di tb_m_stok_harga_{{apotek}} = hb_ppn & hb_ppn_avg = hb_ppn_avg
                    DB::table('tb_m_stok_harga_'.$inisial)
                        ->where('id_obat', $data->id_obat)
                        ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'hb' => $hb, 'is_reload_histori' => 1]);
                    $last_stok = $data->stok_akhir;
                } else {
                    $cek_obat_ = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $data->id_obat)->first();
                    //$hb_ppn = $check->harga_outlet;
                    $hb_ppn = $cek_obat_->hb_ppn_avg;
                    $hb = $cek_obat_->hb;
                    if($cek_obat_->harga_beli > $hb_ppn) {
                        $hb = $hb_ppn;
                    }
                    $hb_ppn_avg = (($cek_obat_->hb_ppn_avg * $last_stok) + ($data->jumlah*$hb_ppn))/($data->jumlah + $last_stok);

                    # set hb_ppn di tb_histori_stok_{{apotek}} = hb_ppn
                    DB::table('tb_histori_stok_'.$inisial)
                        ->where('id', $data->id)
                        ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg]);

                    # set harga_beli_ppn di tb_m_stok_harga_{{apotek}} = hb_ppn & hb_ppn_avg = hb_ppn_avg
                    DB::table('tb_m_stok_harga_'.$inisial)
                        ->where('id_obat', $data->id_obat)
                        ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'hb' => $hb, 'is_reload_histori' => 1]);
                    $last_stok = $data->stok_akhir;
                }
            } else  if (in_array($data->id_jenis_transaksi, $data_penjualan_op_)) {
                $check = TransaksiPODetail::find($data->id_transaksi);

                # jika data pertama
                if($i == 1) {
                    $hb_ppn = $check->harga_jual;
                    $hb = $hb_ppn;
                    $hb_ppn_avg = $hb_ppn;

                    # set hb_ppn di tb_histori_stok_{{apotek}} = hb_ppn
                    DB::table('tb_histori_stok_'.$inisial)
                        ->where('id', $data->id)
                        ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg]);

                    # set harga_beli_ppn di tb_m_stok_harga_{{apotek}} = hb_ppn & hb_ppn_avg = hb_ppn_avg
                    DB::table('tb_m_stok_harga_'.$inisial)
                        ->where('id_obat', $data->id_obat)
                        ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'hb' => $hb, 'is_reload_histori' => 1]);

                    $last_stok = $data->stok_akhir;
                } else {
                    $cek_obat_ = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $data->id_obat)->first();
                    $hb_ppn = $check->harga_jual;
                    $hb = $cek_obat_->harga_beli;
                    if($cek_obat_->harga_beli > $hb_ppn) {
                        $hb = $hb_ppn;
                    }
                    $hb_ppn_avg = (($cek_obat_->hb_ppn_avg * $last_stok) + ($data->jumlah*$hb_ppn))/($data->jumlah + $last_stok);

                    # set hb_ppn di tb_histori_stok_{{apotek}} = hb_ppn
                    DB::table('tb_histori_stok_'.$inisial)
                        ->where('id', $data->id)
                        ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg]);

                    # set harga_beli_ppn di tb_m_stok_harga_{{apotek}} = hb_ppn & hb_ppn_avg = hb_ppn_avg
                    DB::table('tb_m_stok_harga_'.$inisial)
                        ->where('id_obat', $data->id_obat)
                        ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'hb' => $hb, 'is_reload_histori' => 1]);
                    $last_stok = $data->stok_akhir;
                }
            } else {
                $cek_obat_ = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $data->id_obat)->first();

                if(empty($cek_obat_) OR $cek_obat_->hb_ppn == null) {
                    $last_pembelian = TransaksiPembelianDetail::where('tb_detail_nota_pembelian.id_obat', $data->id_obat)
                                        ->join('tb_nota_pembelian as b','b.id','=','tb_detail_nota_pembelian.id_nota')
                                        ->whereDate('b.tgl_nota','<', $data->created_at)
                                        ->where('tb_detail_nota_pembelian.is_deleted', 0)
                                        ->where('b.is_deleted', 0)
                                        ->where('b.id_apotek_nota','=',$apotek->id)
                                        ->first();

                    if(!empty($last_pembelian)) {
                        $hb_ppn = $last_pembelian->harga_beli_ppn;
                        $hb = $last_pembelian->harga_beli;
                        $hb_ppn_avg = $hb_ppn;
                    } else {
                        $last_tf_masuk = TransaksiTODetail::where('tb_detail_nota_transfer_outlet.id_obat', $data->id_obat)
                                        ->join('tb_nota_transfer_outlet as b','b.id','=','tb_detail_nota_transfer_outlet.id_nota')
                                        ->whereDate('b.tgl_nota','<', $data->created_at)
                                        ->where('tb_detail_nota_transfer_outlet.is_deleted', 0)
                                        ->where('b.is_deleted', 0)
                                        ->where('b.id_apotek_tujuan','=',$apotek->id)
                                        ->first();

                        if(!empty($last_tf_masuk)) {
                            $hb_ppn = $last_tf_masuk->harga_outlet;
                            $hb = $cek_obat_->harga_beli;
                            if($cek_obat_->harga_beli > $hb_ppn) {
                                $hb = $hb_ppn;
                            }
                            $hb_ppn_avg = $hb_ppn;
                        } else {
                            $hb_ppn = $cek_obat_->harga_beli_ppn;
                            $hb = $cek_obat_->harga_beli;
                            if($cek_obat_->harga_beli > $hb_ppn) {
                                $hb = $hb_ppn;
                            }
                            $hb_ppn_avg = $hb_ppn;
                        }
                    } 
                } else {
                    $hb_ppn = $cek_obat_->hb_ppn;
                    $hb = $cek_obat_->hb;
                    $hb_ppn_avg = $hb_ppn_avg;
                }

                # set hb_ppn di tb_histori_stok_{{apotek}} = hb_ppn
                DB::table('tb_histori_stok_'.$inisial)
                    ->where('id', $data->id)
                    ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg]);

                # set harga_beli_ppn di tb_m_stok_harga_{{apotek}} = hb_ppn & hb_ppn_avg = hb_ppn_avg
                DB::table('tb_m_stok_harga_'.$inisial)
                    ->where('id_obat', $data->id_obat)
                    ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'hb' => $hb, 'is_reload_histori' => 1]);

                $last_stok = $data->stok_akhir;
            }
        }

        if($i > 0 ) {
            echo 1;
        } else {
            echo 0;
        }
        //echo "finish id obat ".$request->id.' sejumlah '.$i.' data';
    }

    public function template_data(Request $request)
    {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
       
        $rekaps = DB::table('tb_m_stok_harga_'.$inisial.'')
                    ->select([
                            'tb_m_stok_harga_'.$inisial.'.*', 
                            'tb_m_obat.nama', 
                            'tb_m_obat.sku', 
                            'tb_m_obat.untung_jual'
                    ])
                    ->join('tb_m_obat', 'tb_m_obat.id', '=', 'tb_m_stok_harga_'.$inisial.'.id_obat')
                    ->where('tb_m_stok_harga_'.$inisial.'.is_deleted', 0)
                    ->get();


                $collection = collect();
                $no = 0;
                $total_excel=0;
                foreach($rekaps as $rekap) {
                    $no++;
                    $collection[] = array(
                        $no,
                        $rekap->id_obat,
                        $rekap->sku,
                        $rekap->nama,
                        $rekap->untung_jual,
                        "Rp ".number_format($rekap->harga_beli_ppn,2),
                        "Rp ".number_format($rekap->harga_jual,2),
                        '',
                        ''
                    );
                }

        return Excel::download(new class($collection) implements FromCollection, WithHeadings, WithColumnWidths, WithStyles {

                    public function __construct($collection)
                    {
                        $this->collection = $collection;
                    }

                    public function headings(): array
                    {
                        return ['No', 'ID', 'SKU', 'Nama Obat', 'Margin',  'HB+PPN', 'Harga Jual', 'SH? (1 = jika ya)', 'Harga Jual Update (format number tanpa Rp)'];
                    } 

                    public function columnWidths(): array
                    {
                        return [
                            'A' => 8,
                            'B' => 8,
                            'C' => 20,
                            'D' => 45,
                            'E' => 10,
                            'F' => 15,
                            'G' => 15,
                            'H' => 20,
                            'I' => 30,      
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
                            'G'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'H'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                        ];
                    }


                    public function collection()
                    {
                        return $this->collection;
                    }
        },"Template HJ Static ".$apotek->nama_singkat.".xlsx");
    }


    public function reload_data_histori_data() {
      //  ini_set('memory_limit', '-1'); 
        $obats = MasterObat::where('is_deleted', 0)->whereBetween('id', [101, 200])->get();
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $id_apotek = $apotek->id;
        $inisial = strtolower($apotek->nama_singkat);
        $data_pembelian_ = array(2, 12, 13, 14, 26, 27, 30, 31);
        $data_tf_masuk_ = array(3, 7, 16, 28, 32, 33);
        $data_tf_keluar_ = array(4, 8, 17, 29, 32, 33);
        $data_penjualan_ = array(1, 6, 5, 15);
        $data_penjualan_op_ = array(18, 19, 20);
        $data_penyesuaian_ = array(9,10);
        $data_so_ = array(11);
        $data_po_ = array(18, 19, 20, 21);
        $data_td_ = array(22, 23, 24, 25);


        $j = 0;
        foreach ($obats as $key => $val) {
            $historis = DB::table('tb_histori_stok_'.$inisial.'')
                    ->where('id_obat', $val->id)
                    ->get();


            DB::table('tb_m_stok_harga_'.$inisial)
                                ->where('id_obat', $val->id)
                                ->update(['hb_ppn' => 0, 'hb_ppn_avg' => 0, 'is_reload_histori' => 0]);

            $i = 0;
            $last_stok = 0;
            foreach ($historis as $key => $data) {
                $i++;

                if (in_array($data->id_jenis_transaksi, $data_pembelian_)) {
                    $check = TransaksiPembelianDetail::find($data->id_transaksi);

                    # jika data pertama
                    if($i == 1) {
                        $hb_ppn = $check->harga_beli_ppn;
                        $hb = $check->harga_beli;
                        $hb_ppn_avg = $hb_ppn;

                        # set hb_ppn di tb_histori_stok_{{apotek}} = hb_ppn
                        DB::table('tb_histori_stok_'.$inisial)
                            ->where('id', $data->id)
                            ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'is_reload_histori' => 1]);

                        # set harga_beli_ppn di tb_m_stok_harga_{{apotek}} = hb_ppn & hb_ppn_avg = hb_ppn_avg
                        DB::table('tb_m_stok_harga_'.$inisial)
                            ->where('id_obat', $data->id_obat)
                            ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'hb' => $hb, 'is_reload_histori' => 1]);
                        $last_stok = $data->stok_akhir;
                    } else {
                        $cek_obat_ = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $data->id_obat)->first();
                        $hb_ppn = $check->harga_beli_ppn;
                        $hb = $check->harga_beli;
                        $x = ($data->jumlah + $last_stok);
                        if($last_stok != 0 && $x != 0) {
                            $hb_ppn_avg = (($cek_obat_->hb_ppn_avg * $last_stok) + ($data->jumlah*$hb_ppn))/$x;
                        }  else {
                            $hb_ppn_avg = $hb_ppn;
                        } 

                        # set hb_ppn di tb_histori_stok_{{apotek}} = hb_ppn
                        DB::table('tb_histori_stok_'.$inisial)
                            ->where('id', $data->id)
                            ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'is_reload_histori' => 1]);

                        # set harga_beli_ppn di tb_m_stok_harga_{{apotek}} = hb_ppn & hb_ppn_avg = hb_ppn_avg
                        DB::table('tb_m_stok_harga_'.$inisial)
                            ->where('id_obat', $data->id_obat)
                            ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'hb' => $hb, 'is_reload_histori' => 1]);
                        $last_stok = $data->stok_akhir;
                    }
                } else if (in_array($data->id_jenis_transaksi, $data_tf_masuk_)) {
                    $check = TransaksiTODetail::find($data->id_transaksi);

                    # jika data pertama
                    if($i == 1) {
                        $hb_ppn = $check->harga_outlet;
                        $hb = $check->harga_outlet;
                        $hb_ppn_avg = $hb_ppn;

                        # set hb_ppn di tb_histori_stok_{{apotek}} = hb_ppn
                        DB::table('tb_histori_stok_'.$inisial)
                            ->where('id', $data->id)
                            ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'is_reload_histori' => 1]);

                        # set harga_beli_ppn di tb_m_stok_harga_{{apotek}} = hb_ppn & hb_ppn_avg = hb_ppn_avg
                        DB::table('tb_m_stok_harga_'.$inisial)
                            ->where('id_obat', $data->id_obat)
                            ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'hb' => $hb, 'is_reload_histori' => 1]);
                        $last_stok = $data->stok_akhir;
                    } else {
                        $cek_obat_ = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $data->id_obat)->first();
                        $hb_ppn = $check->harga_outlet;
                        $hb = $cek_obat_->harga_beli;
                        if($cek_obat_->harga_beli > $hb_ppn) {
                            $hb = $hb_ppn;
                        }
                        $hb_ppn_avg = (($cek_obat_->hb_ppn_avg * $last_stok) + ($data->jumlah*$hb_ppn))/($data->jumlah + $last_stok);
                        /*if($data->id==132294) {
                            echo $cek_obat_->hb_ppn_avg."*".$last_stok.'+'.$data->jumlah.'*'.$hb_ppn;
                            echo "</br>";
                            print_r($hb_ppn_avg);
                            exit();
                        }*/

                        # set hb_ppn di tb_histori_stok_{{apotek}} = hb_ppn
                        DB::table('tb_histori_stok_'.$inisial)
                            ->where('id', $data->id)
                            ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'is_reload_histori' => 1]);

                        # set harga_beli_ppn di tb_m_stok_harga_{{apotek}} = hb_ppn & hb_ppn_avg = hb_ppn_avg
                        DB::table('tb_m_stok_harga_'.$inisial)
                            ->where('id_obat', $data->id_obat)
                            ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'hb' => $hb, 'is_reload_histori' => 1]);
                        $last_stok = $data->stok_akhir;
                    }
                } else if (in_array($data->id_jenis_transaksi, $data_penjualan_)) {
                    $check = TransaksiPenjualanDetail::find($data->id_transaksi);
                    $cek_obat_ = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $data->id_obat)->first();

                    # jika data pertama
                    if($i == 1) {
                        $last_pembelian = TransaksiPembelianDetail::where('tb_detail_nota_pembelian.id_obat', $data->id_obat)
                                            ->join('tb_nota_pembelian as b','b.id','=','tb_detail_nota_pembelian.id_nota')
                                            ->whereDate('b.tgl_nota','<', $data->created_at)
                                            ->where('tb_detail_nota_pembelian.is_deleted', 0)
                                            ->where('b.is_deleted', 0)
                                            ->where('b.id_apotek_nota','=',$id_apotek)
                                            ->first();

                        if(!empty($last_pembelian)) {
                            $hb_ppn = $last_pembelian->harga_beli_ppn;
                            $hb = $last_pembelian->harga_beli;
                            $hb_ppn_avg = $hb_ppn;
                        } else {
                            $last_tf_masuk = TransaksiTODetail::where('tb_detail_nota_transfer_outlet.id_obat', $data->id_obat)
                                            ->join('tb_nota_transfer_outlet as b','b.id','=','tb_detail_nota_transfer_outlet.id_nota')
                                            ->whereDate('b.tgl_nota','<', $data->created_at)
                                            ->where('tb_detail_nota_transfer_outlet.is_deleted', 0)
                                            ->where('b.is_deleted', 0)
                                            ->where('b.id_apotek_tujuan','=',$id_apotek)
                                            ->first();

                            if(!empty($last_tf_masuk)) {
                                $hb_ppn = $last_tf_masuk->harga_outlet;
                                $hb = $cek_obat_->harga_beli;
                                if($cek_obat_->harga_beli > $hb_ppn) {
                                    $hb = $hb_ppn;
                                }
                                $hb_ppn_avg = $hb_ppn;
                            } else {
                                $hb_ppn = $cek_obat_->harga_beli_ppn;
                                $hb = $cek_obat_->harga_beli;
                                if($cek_obat_->harga_beli > $hb_ppn) {
                                    $hb = $hb_ppn;
                                }
                                $hb_ppn_avg = $hb_ppn;
                            }
                        } 

                        # set hb_ppn di tb_histori_stok_{{apotek}} = hb_ppn
                        DB::table('tb_histori_stok_'.$inisial)
                            ->where('id', $data->id)
                            ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'is_reload_histori' => 1]);

                        # set harga_beli_ppn di tb_m_stok_harga_{{apotek}} = hb_ppn & hb_ppn_avg = hb_ppn_avg
                        DB::table('tb_m_stok_harga_'.$inisial)
                            ->where('id_obat', $data->id_obat)
                            ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'hb' => $hb, 'is_reload_histori' => 1]);

                        #set hb_ppn di tb_detail_nota_penjualan
                        $check->hb_ppn = $hb_ppn;
                        $check->is_reload_histori = 1;
                        $check->save();

                        $last_stok = $data->stok_akhir;
                    } else {
                        $cek_obat_ = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $data->id_obat)->first();

                        # set hb_ppn di tb_histori_stok_{{apotek}} = hb_ppn
                        DB::table('tb_histori_stok_'.$inisial)
                            ->where('id', $data->id)
                            ->update(['hb_ppn' => $cek_obat_->hb_ppn_avg, 'hb_ppn_avg' => $cek_obat_->hb_ppn_avg, 'is_reload_histori' => 1]);

                        #set hb_ppn di tb_detail_nota_penjualan = hb_ppn_avg dari tb_m_stok_{{apotek}} 
                        $check->hb_ppn = $cek_obat_->hb_ppn_avg;
                        $check->is_reload_histori = 1;
                        $check->save();
                        $last_stok = $data->stok_akhir;
                    }
                } else  if (in_array($data->id_jenis_transaksi, $data_tf_keluar_)) {
                    $check = TransaksiTODetail::find($data->id_transaksi);

                    # jika data pertama
                    if($i == 1) {
                        $hb_ppn = $check->harga_outlet;
                        $hb = $hb_ppn;
                        $hb_ppn_avg = $hb_ppn;

                        # set hb_ppn di tb_histori_stok_{{apotek}} = hb_ppn
                        DB::table('tb_histori_stok_'.$inisial)
                            ->where('id', $data->id)
                            ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'is_reload_histori' => 1]);

                        # set harga_beli_ppn di tb_m_stok_harga_{{apotek}} = hb_ppn & hb_ppn_avg = hb_ppn_avg
                        DB::table('tb_m_stok_harga_'.$inisial)
                            ->where('id_obat', $data->id_obat)
                            ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'hb' => $hb, 'is_reload_histori' => 1]);
                        $last_stok = $data->stok_akhir;
                    } else {
                        $cek_obat_ = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $data->id_obat)->first();
                        //$hb_ppn = $check->harga_outlet;
                        $hb_ppn = $cek_obat_->hb_ppn_avg;
                        $hb = $cek_obat_->hb;
                        if($cek_obat_->harga_beli > $hb_ppn) {
                            $hb = $hb_ppn;
                        }
                        $hb_ppn_avg = (($cek_obat_->hb_ppn_avg * $last_stok) + ($data->jumlah*$hb_ppn))/($data->jumlah + $last_stok);

                        # set hb_ppn di tb_histori_stok_{{apotek}} = hb_ppn
                        DB::table('tb_histori_stok_'.$inisial)
                            ->where('id', $data->id)
                            ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'is_reload_histori' => 1]);

                        # set harga_beli_ppn di tb_m_stok_harga_{{apotek}} = hb_ppn & hb_ppn_avg = hb_ppn_avg
                        DB::table('tb_m_stok_harga_'.$inisial)
                            ->where('id_obat', $data->id_obat)
                            ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'hb' => $hb, 'is_reload_histori' => 1]);
                        $last_stok = $data->stok_akhir;
                    }
                } else if (in_array($data->id_jenis_transaksi, $data_penjualan_op_)) {
                    $check = TransaksiPODetail::find($data->id_transaksi);

                    # jika data pertama
                    if($i == 1) {
                        $hb_ppn = $check->harga_jual;
                        $hb = $hb_ppn;
                        $hb_ppn_avg = $hb_ppn;

                        # set hb_ppn di tb_histori_stok_{{apotek}} = hb_ppn
                        DB::table('tb_histori_stok_'.$inisial)
                            ->where('id', $data->id)
                            ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'is_reload_histori' => 1]);

                        # set harga_beli_ppn di tb_m_stok_harga_{{apotek}} = hb_ppn & hb_ppn_avg = hb_ppn_avg
                        DB::table('tb_m_stok_harga_'.$inisial)
                            ->where('id_obat', $data->id_obat)
                            ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'hb' => $hb, 'is_reload_histori' => 1]);

                        $last_stok = $data->stok_akhir;
                    } else {
                        $cek_obat_ = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $data->id_obat)->first();
                        $hb_ppn = $check->harga_jual;
                        $hb = $cek_obat_->harga_beli;
                        if($cek_obat_->harga_beli > $hb_ppn) {
                            $hb = $hb_ppn;
                        }
                        $hb_ppn_avg = (($cek_obat_->hb_ppn_avg * $last_stok) + ($data->jumlah*$hb_ppn))/($data->jumlah + $last_stok);

                        # set hb_ppn di tb_histori_stok_{{apotek}} = hb_ppn
                        DB::table('tb_histori_stok_'.$inisial)
                            ->where('id', $data->id)
                            ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'is_reload_histori' => 1]);

                        # set harga_beli_ppn di tb_m_stok_harga_{{apotek}} = hb_ppn & hb_ppn_avg = hb_ppn_avg
                        DB::table('tb_m_stok_harga_'.$inisial)
                            ->where('id_obat', $data->id_obat)
                            ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'hb' => $hb, 'is_reload_histori' => 1]);
                        $last_stok = $data->stok_akhir;
                    }
                } else {
                    $cek_obat_ = DB::table('tb_m_stok_harga_'.$inisial)->where('id_obat', $data->id_obat)->first();
                    $last_stok = $data->stok_akhir;
                    $created_at = $data->created_at;
                    if($created_at == '' OR $created_at == null) {
                        if($data->id_jenis_transaksi == 11) {
                            $so = SettingStokOpnam::find($data->id_transaksi);
                            $created_at = $so->created_at;
                        }
                    }

                    $last_pembelian = TransaksiPembelianDetail::where('tb_detail_nota_pembelian.id_obat', $data->id_obat)
                                        ->join('tb_nota_pembelian as b','b.id','=','tb_detail_nota_pembelian.id_nota')
                                        ->whereDate('b.tgl_nota','<', $created_at)
                                        ->where('tb_detail_nota_pembelian.is_deleted', 0)
                                        ->where('b.is_deleted', 0)
                                        ->where('b.id_apotek_nota','=',$id_apotek)
                                        ->first();

                    if(!empty($last_pembelian)) {
                        if($last_pembelian->harga_beli_ppn == '0.00' OR $last_pembelian->harga_beli_ppn == null) {
                            if($last_pembelian->harga_beli == '0.00' OR $last_pembelian->harga_beli == null) {
                                $get_hist = DB::table('tb_histori_stok_'.$inisial)->where('id_obat', $data->id_obat)->where('hb_ppn', '!=', 0)->first();
                                $hb_ppn = $get_hist->hb_ppn;
                                $hb = $get_hist->hb_ppn;
                            } else {
                                $hb_ppn = $last_pembelian->harga_beli;
                                $hb = $last_pembelian->harga_beli;
                            }
                        } else {
                            $hb_ppn = $last_pembelian->harga_beli_ppn;
                            $hb = $last_pembelian->harga_beli;
                        }

                        # jika data pertama
                        if($i == 1) {
                            $hb_ppn_avg = $hb_ppn;
                        } else {
                            $x = ($data->jumlah + $last_stok);
                            if($last_stok != 0 && $x != 0) {
                                $hb_ppn_avg = (($cek_obat_->hb_ppn_avg * $last_stok) + ($data->jumlah*$hb_ppn))/$x;
                            } else {
                                $hb_ppn_avg = $hb_ppn;
                            }
                        }
                    } else {
                        $last_tf_masuk = TransaksiTODetail::where('tb_detail_nota_transfer_outlet.id_obat', $data->id_obat)
                                        ->join('tb_nota_transfer_outlet as b','b.id','=','tb_detail_nota_transfer_outlet.id_nota')
                                        ->whereDate('b.tgl_nota','<', $created_at)
                                        ->where('tb_detail_nota_transfer_outlet.is_deleted', 0)
                                        ->where('b.is_deleted', 0)
                                        ->where('b.id_apotek_tujuan','=',$id_apotek)
                                        ->first();

                        if(!empty($last_tf_masuk)) {
                            $hb_ppn = $last_tf_masuk->harga_outlet;
                            $hb = $cek_obat_->harga_beli;
                            if($cek_obat_->harga_beli > $hb_ppn) {
                                $hb = $hb_ppn;
                            }
                            $hb_ppn_avg = $hb_ppn;
                        } else {
                            $hb_ppn = $cek_obat_->harga_beli_ppn;
                            $hb = $cek_obat_->harga_beli;
                            if($cek_obat_->harga_beli > $hb_ppn) {
                                $hb = $hb_ppn;
                            }
                            $hb_ppn_avg = $hb_ppn;
                        }
                    } 
                   /* } else {
                        $hb_ppn = $cek_obat_->hb_ppn;
                        $hb = $cek_obat_->hb;
                        $hb_ppn_avg = $hb_ppn_avg;
                    }*/

                    # set hb_ppn di tb_histori_stok_{{apotek}} = hb_ppn
                    DB::table('tb_histori_stok_'.$inisial)
                        ->where('id', $data->id)
                        ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'is_reload_histori' => 1]);

                    # set harga_beli_ppn di tb_m_stok_harga_{{apotek}} = hb_ppn & hb_ppn_avg = hb_ppn_avg
                    DB::table('tb_m_stok_harga_'.$inisial)
                        ->where('id_obat', $data->id_obat)
                        ->update(['hb_ppn' => $hb_ppn, 'hb_ppn_avg' => $hb_ppn_avg, 'hb' => $hb, 'is_reload_histori' => 1]);

                    $last_stok = $data->stok_akhir;
                }
            }
            $j++;
            print_r($j);
        }



        echo "data sudah direload sejumlah ".$j." data";
    }

    public function data_penyesuaian_stok() {
        $first_day = date('Y-m-d');
        return view('data_obat.data_penyesuaian_stok')->with(compact('first_day'));
    }

    public function get_data_penyesuaian_stok(Request $request) {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        $awal = $request->tgl_awal;
        $akhir = $request->tgl_akhir;
        $tgl_awal_baru = $awal.' 00:00:00';
        $tgl_akhir_baru = $akhir.' 23:59:59';

        $super_admin = session('super_admin');
        DB::statement(DB::raw('set @rownum = 0'));
        $data = PenyesuaianStok::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_penyesuaian_stok_obat.*', 'users.nama as oleh'])
        ->join('users', 'users.id', '=', 'tb_penyesuaian_stok_obat.created_by')
        ->where(function($query) use($request, $super_admin, $tgl_awal_baru, $tgl_akhir_baru){
            $query->where('tb_penyesuaian_stok_obat.is_deleted','=','0');
            $query->where('tb_penyesuaian_stok_obat.id_apotek_nota', session('id_apotek_active'));
            $query->whereDate('tb_penyesuaian_stok_obat.created_at','>=', $tgl_awal_baru);
            $query->whereDate('tb_penyesuaian_stok_obat.created_at','<=', $tgl_akhir_baru);
        });
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->orwhere('stok_awal','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('stok_akhir','LIKE','%'.$request->get('search')['value'].'%');
            });
        }) 
        ->editcolumn('id_obat', function($data) use($request){
            return $data->obat->nama;
        })
        ->editcolumn('created_at', function($data) use($request){
            return Carbon::parse($data->created_at)->format('d/m/Y H:i:s');
        })
        ->editcolumn('created_by', function($data){
            if(strlen($data->oleh) > 15) {
                $trimstring = substr($data->oleh, 0, 15);
                $oleh = 'by '.$trimstring;
            } else {
                $oleh = 'by '.$data->oleh;
            }

            return strtolower($oleh);
        }) 
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['action', 'created_by'])
        ->make(true);  
    }

    public function export_data_penyesuaian_stok(Request $request)
    {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);

        $awal = $request->tgl_awal;
        $akhir = $request->tgl_akhir;
        $tgl_awal_baru = $awal.' 00:00:00';
        $tgl_akhir_baru = $akhir.' 23:59:59';

        $super_admin = session('super_admin');
        DB::statement(DB::raw('set @rownum = 0'));
        $rekaps = PenyesuaianStok::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_penyesuaian_stok_obat.*', 'users.nama as oleh'])
        ->join('users', 'users.id', '=', 'tb_penyesuaian_stok_obat.created_by')
        ->where(function($query) use($request, $super_admin, $tgl_awal_baru, $tgl_akhir_baru){
            $query->where('tb_penyesuaian_stok_obat.is_deleted','=','0');
            $query->where('tb_penyesuaian_stok_obat.id_apotek_nota', session('id_apotek_active'));
            $query->whereDate('tb_penyesuaian_stok_obat.created_at','>=', $tgl_awal_baru);
            $query->whereDate('tb_penyesuaian_stok_obat.created_at','<=', $tgl_akhir_baru);
        })->get();
        

        $collection = collect();
        $no = 0;
        $total_excel=0;
        foreach($rekaps as $rekap) {
            $no++;
            $collection[] = array(
                $no, //a
                $rekap->created_at, //b
                $rekap->obat->id, // c
                $rekap->obat->nama, //d
                $rekap->stok_awal, //e
                $rekap->stok_akhir, //f
                $rekap->alasan, //g
                $rekap->created_oleh->nama, //h
            );
        }

        /*print_r($rekap);exit();*/

        return Excel::download(new class($collection) implements FromCollection, WithHeadings, WithColumnWidths, WithStyles {

                    public function __construct($collection)
                    {
                        $this->collection = $collection;
                    }

                    public function headings(): array
                    {
                        return [
                        'No', 'Tanggal', 'ID Obat', 'Nama Obat', 'Stok Awal', 'Stok Akhir', 'Alasan', 'Oleh'
                        ];
                    } 

                    public function columnWidths(): array
                    {
                        return [
                            'A' => 8,
                            'B' => 20,
                            'C' => 10,
                            'D' => 35,
                            'E' => 10,
                            'F' => 10,
                            'G' => 50,
                            'H' => 30,           
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
                        ];
                    }

                    public function collection()
                    {
                        return $this->collection;
                    }
        },"Data Penyesuaian Stok_".$tgl_awal_baru."_to_".$tgl_akhir_baru.".xlsx");
    }

    public function uploaddata(Request $request) {
        $file_data = $request->file('file_data');
        if($file_data){

            // cek mime //
            // dd($file_data->getClientMimeType());
            if($file_data->getClientMimeType() == "text/csv"){

                try {
                    DB::beginTransaction();

                    $import = new HJStaticImport();
                    $exl = Excel::import($import,$file_data);
                    
                    if($import->importstatus['status']){
                        DB::commit();
                        return json_encode(["status"=>1, "message"=>"Berhasil import data [ berhasil import : ".$import->importstatus['berhasil']." | gagal import : ".$import->importstatus['gagal']]);
                    } else {
                        DB::rollBack();
                        return json_encode(["status"=>2, "message"=>"Gagal import data"]);
                    }            

                } catch (Exception $e) {
                    DB::rollBack();
                    return json_encode(["status"=>2, "message"=>"Gagal import data. ".$e->getMessage()]);
                }                

            } else {
                return json_encode(["status"=>2, "message"=>"Jenis file tidak sesuai"]);
            }

        } else {
            return json_encode(["status"=>2, "message"=>"Anda belum melakukan upload file"]);
        }
    }
}
