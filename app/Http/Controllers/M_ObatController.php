<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use App\Http\Requests;
use App\Support\Collection;
use App\MasterObat;
use App\MasterGolonganObat;
use App\MasterPenandaanObat;
use App\MasterProdusen;
use App\MasterSatuan;
use App\MasterApotek;
use App\HistoriHarga;
use App\TransaksiPembelian;
use App\TransaksiPembelianDetail;
use App;
use Datatables;
use DB;
use Excel;
use Auth;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use function PHPUnit\Framework\isNull;
use App\Imports\BarangImport;
use App\Traits\DynamicConnectionTrait;

class M_ObatController extends Controller
{
    use DynamicConnectionTrait;
    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 25/02/2020
        =======================================================================================
    */
    public function index()
    {
        $golongan_obats = MasterGolonganObat::on($this->getConnectionName())->where('is_deleted', 0)->pluck('keterangan', 'id');
        $golongan_obats->prepend('-- Pilih Golongan Obat --','');

        $penandaan_obats = MasterPenandaanObat::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama', 'id');
        $penandaan_obats->prepend('-- Pilih Penandaan Obat --','');

        $produsens = MasterProdusen::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama', 'id');
        $produsens->prepend('-- Pilih Produsen --','');

        return view('obat.index')->with(compact('golongan_obats', 'penandaan_obats', 'produsens'));
    }


    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 25/02/2020
        =======================================================================================
    */
    public function list_obat(Request $request)
    {
        $apotek = MasterApotek::on($this->getConnectionName())->find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::connection($this->getConnection())->statement(DB::raw('set @rownum = 0'));
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
                $query->orwhere('sku','LIKE','%'.$request->get('search')['value'].'%');
            });
        })   
        ->editcolumn('nama', function($data){
            return '<b>'.$data->nama.'</b><br><span class="text-info">SKU : '.$data->sku.'</span>'; 
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
                $str_ = $data->golongan_obat->keterangan;
            }
            return $str_; 
        }) 
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            //$btn .= '<a href="'.url('/obat/'.$data->id.'/edit').'" title="Edit Data" class="btn btn-primary"><span data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span></a>';
            $btn .= '<span class="btn btn-primary" onClick="edit_data('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span>';
            $btn .= '<span class="btn btn-danger" onClick="delete_obat('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
            $btn .= '<span class="btn btn-secondary" onClick="sync_outlet('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Sync Data"><i class="fa fa-retweet"></i></span>';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['nama','id_produsen', 'id_golongan_obat', 'isi_tab', 'stok', 'action', 'id_produsen', 'id_penandaan_obat', 'id_golongan_obat'])
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
    public function create()
    {
        $obat = new MasterObat;
        $obat->setDynamicConnection();
        $produsens = MasterProdusen::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama', 'id');
        $produsens->prepend('-- Pilih Produsen --','');

        $satuans = MasterSatuan::on($this->getConnectionName())->where('is_deleted', 0)->pluck('satuan', 'id');
        $satuans->prepend('-- Pilih Satuan --','');

        $golongan_obats = MasterGolonganObat::on($this->getConnectionName())->where('is_deleted', 0)->pluck('keterangan', 'id');
        $golongan_obats->prepend('-- Pilih Golongan Obat --','');

        $penandaan_obats = MasterPenandaanObat::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama', 'id');
        $penandaan_obats->prepend('-- Pilih Penandaan Obat --','');

        return view('obat.create')->with(compact('obat', 'produsens', 'satuans', 'golongan_obats', 'penandaan_obats'));
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 26/02/2020
        =======================================================================================
    */
    public function store(Request $request)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        $obat = new MasterObat;
        $obat->setDynamicConnection();
        $obat->fill($request->except('_token'));

        $sync_by = Auth::id();
        $sync_at = date('Y-m-d H:i:s');

        $apoteks = MasterApotek::on($this->getConnectionName())->where('is_deleted', 0)->get();

        $produsens = MasterProdusen::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama', 'id');
        $produsens->prepend('-- Pilih Produsen --','');

        $satuans = MasterSatuan::on($this->getConnectionName())->where('is_deleted', 0)->pluck('satuan', 'id');
        $satuans->prepend('-- Pilih Satuan --','');

        $golongan_obats = MasterGolonganObat::on($this->getConnectionName())->where('is_deleted', 0)->pluck('keterangan', 'id');
        $golongan_obats->prepend('-- Pilih Golongan Obat --','');

        $penandaan_obats = MasterPenandaanObat::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama', 'id');
        $penandaan_obats->prepend('-- Pilih Penandaan Obat --','');

        $validator = $obat->validate();
        if($validator->fails()){
            return view('obat.create')->with(compact('obat', 'produsens', 'satuans', 'golongan_obats', 'penandaan_obats'))->withErrors($validator);
        }else{
            $obat->save_plus();

            $array_ = array('id_obat' => $obat->id, 'stok_awal' => 0, 'stok_akhir' => 0, 'harga_beli' => $obat->harga_beli, 'harga_jual' => $obat->harga_jual, 'created_at' => $sync_at, 'created_by' => $sync_by, 'sync_at' => $sync_at, 'sync_by' => $sync_by);

            foreach ($apoteks as $key => $val) {
                $inisial = strtolower($val->nama_singkat);
                DB::connection($this->getConnectionDefault())->table('tb_m_stok_harga_'.$inisial.'')->insert($array_);
                $val->is_sync = 1;
                $val->sync_by = $sync_by;
                $val->sync_at = $sync_at;
                $val->sync_last_id = $obat->id;
                $val->sync_count = 1;
                $val->save();
            }
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('obat');
        }
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 26/02/2020
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
    public function edit($id)
    {
        $obat = MasterObat::on($this->getConnectionName())->find($id);

        $produsens = MasterProdusen::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama', 'id');
        $produsens->prepend('-- Pilih Produsen --','');

        $satuans = MasterSatuan::on($this->getConnectionName())->where('is_deleted', 0)->pluck('satuan', 'id');
        $satuans->prepend('-- Pilih Satuan --','');

        $golongan_obats = MasterGolonganObat::on($this->getConnectionName())->where('is_deleted', 0)->pluck('keterangan', 'id');
        $golongan_obats->prepend('-- Pilih Golongan Obat --','');

        $penandaan_obats = MasterPenandaanObat::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama', 'id');
        $penandaan_obats->prepend('-- Pilih Penandaan Obat --','');

        return view('obat.edit')->with(compact('obat', 'produsens', 'satuans', 'golongan_obats', 'penandaan_obats'));
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
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        $obat = MasterObat::on($this->getConnectionName())->find($id);
        $harga_beli_awal = $obat->harga_beli;
        $harga_jual_awal = $obat->harga_jual;
        if(is_null($obat->zat_aktif)){
            $obat->zat_aktif = '-';
        }
        if(is_null($obat->bentuk_kekuatan_sediaan)){
            $obat->bentuk_kekuatan_sediaan = '-';
        }
        $obat->fill($request->except('_token'));
        $apoteks = MasterApotek::on($this->getConnectionName())->where('is_deleted', 0)->get();
        $validator = $obat->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            $obat->save_edit();
            // add histori perubahan data harga obat
            if($harga_beli_awal != $request->harga_beli OR $harga_jual_awal != $request->harga_jual) {
                $data_histori_ = array('id_obat' => $obat->id, 'harga_beli_awal' => $harga_beli_awal, 'harga_beli_akhir' => $request->harga_beli, 'harga_jual_awal' => $harga_jual_awal, 'harga_jual_akhir' => $request->harga_jual, 'created_by' => Auth::id(), 'created_at' => date('Y-m-d H:i:s'));

                // update harga obat masing2 outlet
                foreach ($apoteks as $key => $obj) {
                    $inisial = strtolower($obj->nama_singkat);
                    $cek = DB::connection($this->getConnectionDefault())->table('tb_m_stok_harga_'.$inisial)->where('id_obat', $obat->id)->first();
                    if($cek->is_status_harga == 0) {
                        DB::connection($this->getConnectionName())->table('tb_histori_harga_'.$inisial.'')->insert($data_histori_);
                        DB::connection($this->getConnectionDefault())->table('tb_m_stok_harga_'.$inisial)->where('id_obat', $obat->id)->update(['updated_at' => date('Y-m-d H:i:s'), 'harga_beli' => $request->harga_beli, 'harga_jual' => $request->harga_jual, 'updated_by' => Auth::user()->id]);
                    }
                }
            }
            echo json_encode(array('status' => 1));
        }
    }

    /*
        =======================================================================================
        For     : 
        Author  : Sri U.
        Date    : 26/02/2020
        =======================================================================================
    */
    public function destroy($id)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        $obat = MasterObat::on($this->getConnectionName())->find($id);
        $obat->is_deleted = 1;
        $obat->deleted_by = Auth::user()->id;
        $obat->deleted_at = date('Y-m-d H:i:s');
        $apoteks = MasterApotek::on($this->getConnectionName())->where('is_deleted', 0)->get();
        if($obat->save()){
            // update harga obat masing2 outlet
            foreach ($apoteks as $key => $obj) {
                $inisial = strtolower($obj->nama_singkat);
                $cek_ = DB::connection($this->getConnectionDefault())->table('tb_m_stok_harga_'.$inisial)->where('id_obat', $obat->id)->first();
                if(!empty($cek_)) {
                    DB::connection($this->getConnectionDefault())->table('tb_m_stok_harga_'.$inisial)->where('id_obat', $obat->id)->update(['is_deleted' => 1, 'deleted_at' => date('Y-m-d H:i:s'), 'deleted_by' => Auth::user()->id]);
                }
            }
            echo 1;
        }else{
            echo 0;
        }
    }

    public function kenaikan_harga() {
        return view('obat.kenaikan_harga');
    }

    public function list_kenaikan_harga(Request $request) {
        $currentPage = $request->page_num;
        $sort = $request->sort;
        $orderBy = 'id';
        if($sort == 1) {
            $orderBy = 'barcode';
        } else if($sort == 2) {
            $orderBy = 'nama';
        }


        //currentPathResolver : jika menggunakan link
        Paginator::currentPageResolver(function() use ($currentPage) {
            return $currentPage;
        });

        //$obats = MasterObat::on($this->getConnectionName())->where('is_deleted', 0)->get();
        $persen = $request->persen_kenaikan;
        if($persen == '') {
            $persen = 0;
        }

        $data1 = TransaksiPembelianDetail::select([
                    'tb_detail_nota_pembelian.*', 
                    'b.nama', 
                    'b.barcode', 
                    'b.isi_tab', 
                    'harga_jual', 
                    'b.untung_jual', 
                    DB::raw('(((b.untung_jual/100) * tb_detail_nota_pembelian.harga_beli_ppn) + tb_detail_nota_pembelian.harga_beli_ppn) as harga_jual_now'), 
                    DB::raw('((('.$persen.'/100) * harga_jual) + harga_jual) as harga_ambang_batas')
                    ])
                    //DB::raw('((3/100) * (((b.untung_jual/100) * tb_detail_nota_pembelian.harga_beli_ppn) + tb_detail_nota_pembelian.harga_beli_ppn) + (((b.untung_jual/100) * tb_detail_nota_pembelian.harga_beli_ppn) + tb_detail_nota_pembelian.harga_beli_ppn)) as harga_ambang_batas')])
                    ->join('tb_nota_pembelian as a', 'a.id', '=', 'tb_detail_nota_pembelian.id_nota')
                    ->join('tb_m_obat as b', 'b.id', '=', 'tb_detail_nota_pembelian.id_obat')
                    ->where(function($query) use($request){
                        $query->whereRaw('tb_detail_nota_pembelian.is_deleted = 0');
                        if($request->nama_obat != '') {
                            $query->whereRaw('b.nama LIKE "%'.$request->nama_obat.'%"');
                        }
                    })
                    ->groupBy('tb_detail_nota_pembelian.id_obat')
                    ->orderBy('a.tgl_nota')->toSql();

        $obats = DB::connection($this->getConnectionName())->table(DB::raw("($data1) AS t1"))->select([
                            DB::raw('*')
                        ])
                        ->whereRaw('harga_jual_now > harga_ambang_batas')
                        ->orderBy($orderBy, 'ASC')
                        ->get();

        $obats = (new Collection($obats))->sortBy($orderBy)->paginate(10);
        
        return view("obat._form_kenaikan_harga", compact('obats', 'currentPage', 'persen'));
    }

    public function setting_harga_jual(Request $request) {
        $id = $request->id;
        $harga_beli = $request->harga_beli;
        $harga_beli_ppn = $request->harga_beli_ppn;
        $id_asal = $request->id_asal;
        $obat = MasterObat::on($this->getConnectionName())->find($id);
        return view('obat._form_set_harga')->with(compact('obat', 'harga_beli', 'harga_beli_ppn', 'id_asal'));
    }

    public function update_harga(Request $request, $id)
    {
        $obat = MasterObat::on($this->getConnectionName())->find($id);
        $harga_beli_awal = $obat->harga_beli;
        $harga_jual_awal = $obat->harga_jual;
        $apoteks = MasterApotek::on($this->getConnectionName())->where('is_deleted', 0)->get();
        $validator = $obat->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            $obat->harga_jual = $request->harga_jual;
            $i = 0;
            // add histori perubahan data harga obat
            if($harga_jual_awal != $request->harga_jual) {
                $data_histori_ = array('id_obat' => $obat->id, 'harga_beli_awal' => $harga_beli_awal, 'harga_beli_akhir' => $request->harga_beli, 'harga_jual_awal' => $harga_jual_awal, 'harga_jual_akhir' => $request->harga_jual, 'is_asal' => 1, 'id_asal' => $request->id_asal, 'created_by' => Auth::id(), 'created_at' => date('Y-m-d H:i:s'));

                // update harga obat masing2 outlet
                foreach ($apoteks as $key => $obj) {
                    $inisial = strtolower($obj->nama_singkat);
                    $cek = DB::connection($this->getConnectionDefault())->table('tb_m_stok_harga_'.$inisial)->where('id_obat', $obat->id)->first();
                    if($cek->is_status_harga == 0) {
                        DB::connection($this->getConnectionName())->table('tb_histori_harga_'.$inisial.'')->insert($data_histori_);
                        DB::connection($this->getConnectionDefault())->table('tb_m_stok_harga_'.$inisial)->where('id_obat', $obat->id)->update(['updated_at' => date('Y-m-d H:i:s'), 'harga_beli' => $request->harga_beli, 'harga_beli_ppn' => $request->harga_beli_ppn, 'harga_jual' => $request->harga_jual, 'updated_by' => Auth::user()->id]);
                    }
                }

                $obat->save_edit();
                $i = 1;
            }

            if($i == 1) {
                echo json_encode(array('status' => 1));
            } else {
                echo json_encode(array('status' => 0));
            }
        }
    }

    public function export_data(Request $request) 
    {
        $apotek = MasterApotek::on($this->getConnectionName())->find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);

        $data = DB::connection($this->getConnectionDefault())->table('tb_m_stok_harga_'.$inisial.'')->select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_m_stok_harga_'.$inisial.'.*', 'tb_m_obat.nama', 'tb_m_obat.sku', 'tb_m_obat.isi_tab', 'tb_m_obat.isi_strip', 'tb_m_produsen.nama as produsen', 'tb_m_penandaan_obat.nama as penandaan_obat', 'tb_m_golongan_obat.keterangan as golongan_obat'])
                    ->join('tb_m_obat', 'tb_m_obat.id', '=', 'tb_m_stok_harga_'.$inisial.'.id_obat')
                    ->join('tb_m_produsen', 'tb_m_produsen.id', '=', 'tb_m_obat.id_produsen')
                    ->join('tb_m_penandaan_obat', 'tb_m_penandaan_obat.id', '=', 'tb_m_obat.id_penandaan_obat')
                    ->join('tb_m_golongan_obat', 'tb_m_golongan_obat.id', '=', 'tb_m_obat.id_golongan_obat')
                    ->where('tb_m_stok_harga_'.$inisial.'.is_deleted', 0)
                    ->where('tb_m_stok_harga_'.$inisial.'.is_disabled', 0)
                    ->where('tb_m_obat.id_penandaan_obat','LIKE',($request->id_penandaan_obat > 0 ? $request->id_penandaan_obat : '%'.$request->id_penandaan_obat.'%'))
                    ->where('tb_m_obat.id_golongan_obat','LIKE',($request->id_golongan_obat > 0 ? $request->id_golongan_obat : '%'.$request->id_golongan_obat.'%'))
                    ->get();

        /*MasterObat::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_m_obat.*'])
                ->where(function($query) use($request){
                    $query->where('is_deleted','=','0');
                    $query->where('id_penandaan_obat','LIKE',($request->id_penandaan_obat > 0 ? $request->id_penandaan_obat : '%'.$request->id_penandaan_obat.'%'));
                    $query->where('id_golongan_obat','LIKE',($request->id_golongan_obat > 0 ? $request->id_golongan_obat : '%'.$request->id_golongan_obat.'%'));
                })
                ->orderBy('id', 'DESC')
                ->get();*/

        $collection = collect();
        $no = 0;
        $total_excel=0;
        foreach($data as $rekap) {
            $no++;
            if($rekap->harga_beli_ppn == '' || $rekap->harga_beli_ppn == 0) {
                $rekap->harga_beli_ppn = $rekap->harga_beli;
            } 

            $collection[] = array(
                $no,
                $rekap->id,
                $rekap->sku,
                $rekap->nama,
                $rekap->produsen,
                $rekap->penandaan_obat,
                $rekap->golongan_obat,
                $rekap->isi_tab,
                $rekap->isi_strip,
                $rekap->harga_jual,
                $rekap->harga_beli,
                $rekap->harga_beli_ppn,
                $rekap->stok_akhir
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
                        return ['No', 'ID', 'SKU','Nama', 'Produsen', 'Penandaan Obat', 'Golongan Diskon', 'Isi /tab','Isi /strip', 'Harga Jual',  'Harga Beli', 'HB+PPN', 'Stok'];
                    } 

                    public function columnWidths(): array
                    {
                        return [
                            'A' => 8,
                            'B' => 8,
                            'C' => 20,
                            'D' => 50,
                            'E' => 35,
                            'F' => 35,
                            'G' => 20,
                            'H' => 10,
                            'I' => 10,
                            'J' => 15,
                            'K' => 15,
                            'L' => 15,
                            'M' => 10,        
                        ];
                    }

                    public function styles(Worksheet $sheet)
                    {
                        return [
                            1    => ['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            //'A'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            //'B'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            //'C'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            //'D'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                           // 'G'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                        ];
                    }


                    public function collection()
                    {
                        return $this->collection;
                    }
        },"Data Obat_".$now.".xlsx");
    }

    public function sync_obat_outlet($id) {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        $obat = MasterObat::on($this->getConnectionName())->find($id);
        $apoteks = MasterApotek::on($this->getConnectionName())->where('is_deleted', 0)->get();

        $sync_by = Auth::id();
        $sync_at = date('Y-m-d H:i:s');
        $array_ = array('id_obat' => $obat->id, 'stok_awal' => 0, 'stok_akhir' => 0, 'harga_beli' => $obat->harga_beli, 'harga_jual' => $obat->harga_jual, 'created_at' => $sync_at, 'created_by' => $sync_by, 'sync_at' => $sync_at, 'sync_by' => $sync_by);

        foreach ($apoteks as $key => $val) {
            $inisial = strtolower($val->nama_singkat);
            $cek = DB::connection($this->getConnectionDefault())->table('tb_m_stok_harga_'.$inisial)->where('id_obat', $obat->id)->first();
            
            if(empty($cek)) {
                DB::connection($this->getConnectionDefault())->table('tb_m_stok_harga_'.$inisial.'')->insert($array_);
                $val->is_sync = 1;
                $val->sync_by = $sync_by;
                $val->sync_at = $sync_at;
                $val->sync_last_id = $obat->id;
                $val->sync_count = 1;
                $val->save();
            }
        }

        echo 1;
    }

    public function import() {
        $data = new MasterObat;
        $data->setDynamicConnection();
        return view("obat/import_form",compact('data'));
    }

    public function UploadDataProcess(Request $request)
    {
        $file_data = $request->file('file_data');
        if($file_data){

            // cek mime //
            // dd($file_data->getClientMimeType());
            if($file_data->getClientMimeType() == "text/csv"){

                try {
                    DB::connection($this->getConnectionName())->beginTransaction(); 

                    $import = new BarangImport();
                    $exl = Excel::import($import,$file_data);
                    
                    if($import->importstatus['status']){
                        DB::connection($this->getConnectionName())->commit();
                        return json_encode(["status"=>1, "message"=>"Berhasil import data [ berhasil import : ".$import->importstatus['berhasil']." | gagal import : ".$import->importstatus['gagal']]);
                    } else {
                        DB::connection($this->getConnectionName())->rollback();
                        return json_encode(["status"=>2, "message"=>"Gagal import data"]);
                    }            

                } catch (Exception $e) {
                    DB::connection($this->getConnectionName())->rollback();
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
