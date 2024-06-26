<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\RbacUserRole;
use App\RbacRolePermission;
use App\RbacPermission;
use App\RbacMenu;
use App\MasterApotek;
use App\MasterTahun;
use App\TransaksiPenjualanClosing;
use App\TransaksiPembelian;
use App\TransaksiPembelianDetail;
use App\TransaksiTO;
use App\TransaksiTODetail;
use App\User;
use App\MasterVendor;
use App\MasterSuplier;
use App\MasterObat;
use App\MasterMember;
use App\InvestasiModal;
use App\MasterSatuan;
use App\HistoriStok;
use App\MasterStokHarga;
use App;
use Datatables;
use DB;
use Auth;
use Mail;

use App\TransaksiPenjualan;
use App\TransaksiPenjualanDetail;

class MonitoringController extends Controller
{
   	public function __construct()
    {
        $this->middleware('auth:web');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
    }

    public function monitoring_data(){
        $first_day = date('Y-m-d');
    	return view('monitoring_data')->with(compact('first_day'));
    }

    public function getData(Request $request) {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);


        #(SELECT b.stok_awal FROM tb_histori_stok_".$inisial." AS b WHERE b.id_obat = a.id_obat AND  a.id > b.id ORDER BY b.id DESC LIMIT 1) AS stok_awal_before,
        #(SELECT b.stok_akhir FROM tb_histori_stok_".$inisial." AS b WHERE b.id_obat = a.id_obat and  a.id > b.id order by b.id desc LIMIT 1) AS stok_akhir_before,
        #(select c.stok_akhir from tb_histori_stok_".$inisial." as c where c.id_obat = a.id_obat and a.id < c.id limit 1) as stok_akhir_after

        $data = DB::select("SELECT t1.* FROM (SELECT
                                a.id,
                                  a.id_obat,
                                  a.stok_awal,
                                  a.stok_akhir,
                                  a.created_at,
                                  a.id_jenis_transaksi,
                                  a.id_transaksi,
                                  (SELECT c.stok_awal FROM tb_histori_stok_".$inisial." AS c WHERE c.id_obat = a.id_obat AND a.id < c.id LIMIT 1) AS stok_awal_after
                                FROM
                                    tb_histori_stok_lv AS a) AS t1
                                WHERE t1.stok_akhir != t1.stok_awal_after AND t1.id_jenis_transaksi != 11 AND t1.created_at > '2022-10-09 00:00:01'
                                  ");


        //DB::statement(DB::raw('set @rownum = 0'));
       
        //->where('b.stok_awal', '!=', 'tb_histori_stok_'.$inisial.'.stok_akhir')

       /*  $q1 = DB::table('tb_histori_stok_'.$inisial)
                ->select([  DB::raw('@rownum  := @rownum  + 1 AS no'),
                    'tb_histori_stok_'.$inisial.'.id',
                    'tb_histori_stok_'.$inisial.'.id_obat',
                    'tb_histori_stok_'.$inisial.'.stok_awal',
                    'tb_histori_stok_'.$inisial.'.stok_akhir',
                    'tb_histori_stok_'.$inisial.'.created_at',
                    'tb_histori_stok_'.$inisial.'.id_jenis_transaksi',
                    'tb_histori_stok_'.$inisial.'.id_transaksi',
                    'b.id as id_awal_after',
                    'b.stok_awal as stok_awal_after'
                ])
                ->join('tb_histori_stok_'.$inisial.' as b', function($join) use($inisial){
                    $join->on('b.id_obat', '=', 'tb_histori_stok_'.$inisial.'.id_obat');
                    $join->on('tb_histori_stok_'.$inisial.'.id', '<', 'b.id');
                })
                ->where('tb_histori_stok_'.$inisial.'.created_at', '>', '2022-10-09 00:00:01')
                ->groupBy('tb_histori_stok_'.$inisial.'.id');

      $data = DB::table(DB::raw("({$q1->toSql()}) as sub"))
                ->mergeBindings($q1)
                ->select(['sub.*'])
                ->where('stok_awal_after', '!=', 'stok_awal')
                ->get();
*/

    /*  $q1 = DB::table('tb_histori_stok_'.$inisial)
                ->select([  DB::raw('@rownum  := @rownum  + 1 AS no'),
                    'tb_histori_stok_'.$inisial.'.id',
                    'tb_histori_stok_'.$inisial.'.id_obat',
                    'tb_histori_stok_'.$inisial.'.stok_awal',
                    'tb_histori_stok_'.$inisial.'.stok_akhir',
                    'tb_histori_stok_'.$inisial.'.created_at',
                    'tb_histori_stok_'.$inisial.'.id_jenis_transaksi',
                    'tb_histori_stok_'.$inisial.'.id_transaksi',
                    DB::raw('(SELECT a.stok_awal FROM tb_histori_stok_'.$inisial.' AS a WHERE a.id_obat = tb_histori_stok_'.$inisial.'.id_obat AND tb_histori_stok_'.$inisial.'.id < a.id LIMIT 1) as stok_awal_after')
                ]);

        $data = DB::table(DB::raw("({$q1->toSql()}) as sub"))
                ->mergeBindings($q1)
                ->join('tb_m_stok_harga_'.$inisial.' as master', 'master.id_obat', 'sub.id_obat')
                ->select(['sub.*', 'master.nama', 'master.barcode'])
                ->where('sub.stok_akhir', '!=', 'sub.stok_awal_after')
                ->where('sub.created_at', '>', '2022-10-09 00:00:01')
                ->where('sub.id_jenis_transaksi', '!=', 11)->count();*/
        //dd($data);

        $datatables = Datatables::of($data);
        return $datatables
        ->editcolumn('id_obat', function($data) {
            
            return $data->id_obat;
        })    
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
           
            $btn .='</div>';
            return $btn;
        })    
        /*->setRowClass(function ($data) {
            if($data->is_sign == 0) {
                return 'bg-secondary';
            } else {
                return '';
            }
        })  */
        ->rawColumns(['action'])
        ->addIndexColumn()
        ->make(true);  
        
    }

    public function monitoring_pembelian(){
    	$first_day = date('Y-m-d');
        return view('monitoring_pembelian')->with(compact('first_day'));
    }

    public function getDataPembelian(Request $request) {
        $apotek = MasterApotek::find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);

        $tgl_awal_baru = $request->tgl_awal.' 00:00:01';
        $tgl_akhir_baru = $request->tgl_akhir.' 23:59:59';

        $rekaps = TransaksiPembelianDetail::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_detail_nota_pembelian.*', 'a.nama','b.no_faktur', 'b.tgl_faktur'])
        ->join('tb_m_obat as a', 'a.id', 'tb_detail_nota_pembelian.id_obat')
        ->join('tb_nota_pembelian as b', 'b.id', 'tb_detail_nota_pembelian.id_nota')
        ->where(function($query) use($request, $tgl_awal_baru, $tgl_akhir_baru){
            $query->where('tb_detail_nota_pembelian.is_deleted','=','0');
            $query->where('b.id_apotek_nota','=',session('id_apotek_active'));

            if (!empty($request->tgl_awal) && !empty($request->tgl_akhir)) {
                $query->where('b.tgl_nota','>=', $tgl_awal_baru);
                $query->where('b.tgl_nota','<=', $tgl_akhir_baru);
            }
        })
        ->orderBy('b.id', 'DESC')->get();

        $data = collect();
        foreach ($rekaps as $key => $obj) {
            $last = HistoriStok::where('id_obat', $obj->id_obat)
                            ->where('id_jenis_transaksi', [2])
                            ->where('id_transaksi', '=', $obj->id)
                            ->orderBy('id', 'DESC')
                            ->first();



            $histori_stok = HistoriStok::select(['hb_ppn', 'id_transaksi', 'id_jenis_transaksi'])
                            ->where('id_obat', $obj->id_obat)
                            ->whereIn('id_jenis_transaksi', [2,3,11,9])
                            ->whereNotIn('id', [$obj->id])
                            ->where('hb_ppn', '>', 0)
                            ->orderBy('id', 'DESC')
                            ->first();
            /*if($obj->id_obat == '9486') {
                dd($histori_stok);
            }*/

            $avg = 0;
            $kenaikan = 0;
            $harga_beli_ppn_beforeid = 0;
            $kategori = 1;
            if(!empty($histori_stok)) {
                if($histori_stok->hb_ppn == 0 OR is_null($histori_stok->hb_ppn)) {
                    $data_pembelian_ = array(2, 12, 13, 14, 26, 27, 30, 31);
                    $data_tf_masuk_ = array(3, 7, 16, 28, 29, 32, 33);

                    if (in_array($data->id_jenis_transaksi, $data_pembelian_)) {
                        $check = TransaksiPembelianDetail::find($data->id_transaksi);
                        $id_nota = ' | IDNota : '.$check->nota->id;
                        $hb = $check->harga_beli;
                        $ppn = $check->nota->ppn;
                        $harga_beli_ppn = $check->harga_beli_ppn;
                    } else if (in_array($data->id_jenis_transaksi, $data_tf_masuk_)) {
                        $check = TransaksiTODetail::find($data->id_transaksi);
                        $hb = 0;
                        $ppn = 0;
                        $harga_beli_ppn = $check->harga_outlet;
                    } 
                    $avg = $harga_beli_ppn;
                } else {
                    $avg = $histori_stok->hb_ppn;
                }

                if($avg > $obj->harga_beli_ppn) {
                    # jika avg lebih besasr dari hbppn | kenaikan
                    $selisih = $avg - $obj->harga_beli_ppn;
                    if($selisih > 0 && $obj->harga_beli_ppn > 0) {
                        $kenaikan = ($selisih/$obj->harga_beli_ppn)*100;
                    }
                    $kategori = 2;
                } else {
                    # penurunan
                    $selisih = $obj->harga_beli_ppn - $avg;
                    if($selisih > 0 && $obj->harga_beli_ppn > 0) {
                        $kenaikan = ($selisih/$obj->harga_beli_ppn)*100;
                    }
                }

                $harga_beli_ppn_beforeid = $histori_stok->id_transaksi;
            }
        
            $obj->harga_beli_ppn_before = $avg;
            $obj->kategori = $kategori;
            $obj->harga_beli_ppn_beforeid = $harga_beli_ppn_beforeid;
            $obj->kenaikan = $kenaikan;
            if($kenaikan >= $request->kenaikan) {
                $data[] = $obj;
            }
        }

        $datatables = Datatables::of($data);
        return $datatables
        ->editcolumn('id_obat', function($data) {
            return $data->id_obat.' | '.$data->nama;
        })  
        ->editcolumn('id', function($data) {
            return $data->id_nota.' | '.$data->id;
        })
        ->editcolumn('harga_beli_ppn', function($data) {
            /* $btn = '<span class="label" onClick="gunakan_hb('.$data->id.', '.$data->id_obat
            .', '.$data->harga_beli.', '.$data->harga_beli_ppn.')" data-toggle="tooltip" data-placement="top" title="Gunakan ini" style="font-size:10pt;color:#0097a7;">[Terapkan]</span>';  */

            $btn ='';

            return $data->harga_beli_ppn.'<br>'.$btn; 
        })
        ->addcolumn('harga_beli_ppn_before', function($data) {
            $avg = number_format($data->harga_beli_ppn_before,2,".","");//.'----'.$data->harga_beli_ppn_beforeid;

            return $avg;
        })  
        ->addcolumn('kenaikan', function($data) {
            if($data->kategori == 1) {
                $str = '<span class="text-danger"><i class="fa fa-arrow-up"></i></span> ';
            } else {
                $str = '<span class="text-info"><i class="fa fa-arrow-down"></i></span> ';
            }

            $kenaikan = $str.' '.number_format($data->kenaikan,2,".","");
            return $kenaikan.'%';
        })    
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .= '<a href="'.url('/pembelian/'.$data->id_nota.'/edit').'" target="_blank" title="Edit Data" class="btn btn-info btn-sm"><span data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i> Edit</span></a>';
            $btn .= '<a href="'.url('/data_obat/histori_harga/'.$data->id_obat).'" target="_blank" title="Histori Harga" class="btn btn-secondary btn-sm"><span data-toggle="tooltip" data-placement="top" title="Histori Harga"><i class="fa fa-history"></i> Histori Harga</span></a>';
            $btn .='</div>';
            return $btn;
        })    
        ->setRowClass(function ($data) {
            if($data->kenaikan > 50) {
                return 'bg-secondary';
            } else {
                return '';
            }
        })  
        ->rawColumns(['action', 'kenaikan', 'harga_beli_ppn_before', 'harga_beli_ppn'])
        ->addIndexColumn()
        ->make(true);  
        
    }
}
