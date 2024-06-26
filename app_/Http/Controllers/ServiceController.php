<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterApotek;
use App\MasterObat;
use App\SettingStokOpnam;
use Response;
use App;
use Datatables;
use DB;

class ServiceController extends Controller
{
    public function download_apotek()
    {
        $date_now = date('Ymd');
        $headers = [
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0'
            ,   'Content-type'        => 'text/csv'
            ,   'Content-Disposition' => 'attachment; filename=APOTIKBWF_MASTERSTORE_'.$date_now.'.csv'
            ,   'Expires'             => '0'
            ,   'Pragma'              => 'public'
        ];

        $list = MasterApotek::select('id as STORE_NUMBER', 'nama_panjang as STORE_NAME', 'alamat as STORE_ADDRESS')->whereIn('id', [1, 2, 3, 4])->get()->toArray();

        # add headers for each column in the CSV download
        array_unshift($list, array_keys($list[0]));

       $callback = function() use ($list) 
        {
            $FH = fopen('php://output', 'w');
            foreach ($list as $row) { 
                fputcsv($FH, $row, "|");
            }
            fclose($FH);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function download_master_obat()
    {
        $date_now = date('Ymd');
        $headers = [
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0'
            ,   'Content-type'        => 'text/csv'
            ,   'Content-Disposition' => 'attachment; filename=APOTIKBWF_MASTERPRODUCT_'.$date_now.'.csv'
            ,   'Expires'             => '0'
            ,   'Pragma'              => 'public'
        ];

        $list = MasterObat::select('tb_m_obat.id as NO_SKU', 
                            'tb_m_obat.nama as PRODUCT_NAME', 
                            DB::raw('a.satuan as UNIT'), 
                            'tb_m_obat.barcode as BAR_CODE', 
                            DB::raw('"-" as CATEGORY'), 
                            DB::raw('"-" as SUBCATEGORY'),
                            DB::raw('"-" as BRAND'), 
                            DB::raw('"-" as DOSE'))
                    ->leftJoin('tb_m_satuan as a', 'a.id', '=', 'tb_m_obat.id_satuan')
                    ->get()
                    ->toArray();

        # add headers for each column in the CSV download
        array_unshift($list, array_keys($list[0]));

       $callback = function() use ($list) 
        {
            $FH = fopen('php://output', 'w');
            foreach ($list as $row) { 
                fputcsv($FH, $row);
            }
            fclose($FH);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function download_stok_obat()
    {
        $date_now = date('YmdHms');
        $headers = [
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0'
            ,   'Content-type'        => 'text/csv'
            ,   'Content-Disposition' => 'attachment; filename=APOTIKBWF_PRODUCTONHAND_'.$date_now.'.csv'
            ,   'Expires'             => '0'
            ,   'Pragma'              => 'public'
        ];
        
        $data_apotek_LV = $this->hitung_stok_apotek(1);
       // $data_apotek_BKL = $this->hitung_stok_apotek(2);
       // $data_apotek_PJM = $this->hitung_stok_apotek(3);
        //$data_apotek_PG = $this->hitung_stok_apotek(4);
        $data_apotek_BKL = array();

        $data_new = $data_apotek_LV;
       // $data_new = array_merge($data_new, $data_apotek_BKL);
       // $data_new = array_merge($data_new, $data_apotek_PJM);
       // $data_new = array_merge($data_new, $data_apotek_PG);
        //$oneDimensionalArray = call_user_func_array('array_merge', $data_new);

    
        # add headers for each column in the CSV download
        array_unshift($data_new, array_keys($data_new[0]));


       $callback = function() use ($data_new) 
        {
            $FH = fopen('php://output', 'w');
            foreach ($data_new as $row) { 
                fputcsv($FH, $row);
            }
            fclose($FH);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function hitung_stok_apotek($var) {
        $apotek = MasterApotek::find($var);
        $inisial = strtolower($apotek->nama_singkat);

        $list = DB::table('tb_m_stok_harga_'.$inisial.'')
                ->select([
                    DB::raw(''.$var.' as STORE_NUMBER'),
                    'tb_m_stok_harga_'.$inisial.'.id_obat as NO_SKU', 
                    'tb_m_stok_harga_'.$inisial.'.stok_akhir as STOCK_AVAILABILITY', 
                    'tb_m_stok_harga_'.$inisial.'.harga_jual as BASE_PRICE', 
                    'tb_m_stok_harga_'.$inisial.'.harga_jual as DISCOUNT_PRICE'])
            ->leftJoin('tb_m_obat', 'tb_m_obat.id', '=', 'tb_m_stok_harga_'.$inisial.'.id_obat')
            ->where('tb_m_obat.is_deleted', 0)
            ->where('tb_m_stok_harga_'.$inisial.'.is_disabled', 0)
            ->get()->toArray();

        $data = array();
        foreach ($list as $key => $val) {
            $data[] = json_decode(json_encode($val), true);;
        }

        return $data;
    }

    // API Lavie
    public function ef4c2ce3032d8f024c320308d9880a06() {
        $var = 1;
        //$apotek = 'LV';
        $apotek = MasterApotek::find($var);
        $inisial = strtolower($apotek->nama_singkat);
        
        $rekaps = DB::table('tb_m_stok_harga_'.$inisial.'')
                ->select([
                            'tb_m_obat.id',
                            'tb_m_obat.barcode', 
                            'tb_m_obat.nama', 
                            'tb_m_golongan_obat.keterangan as golongan_obat',
                            'tb_m_obat.isi_tab as isibox',
                            'tb_m_obat.isi_strip as isistrip',
                            'tb_m_stok_harga_'.$inisial.'.harga_jual as patokanhargajual', 
                            'tb_m_stok_harga_'.$inisial.'.stok_akhir as total_stok'
                    ])
            ->leftJoin('tb_m_obat', 'tb_m_obat.id', '=', 'tb_m_stok_harga_'.$inisial.'.id_obat')
            ->leftJoin('tb_m_golongan_obat', 'tb_m_golongan_obat.id', '=', 'tb_m_obat.id_golongan_obat')
            ->where('tb_m_obat.is_deleted', 0)
            ->where('tb_m_stok_harga_'.$inisial.'.is_disabled', 0)
            ->get();

        echo json_encode($rekaps);
    }
    // API Bekul
    public function f31d5936f25442ecf43a2e4a9aa911d1() {
        $var = 2;
        //$apotek = 'BKL';
        $apotek = MasterApotek::find($var);
        $inisial = strtolower($apotek->nama_singkat);
        
        $rekaps = DB::table('tb_m_stok_harga_'.$inisial.'')
                ->select([
                            'tb_m_obat.id',
                            'tb_m_obat.barcode', 
                            'tb_m_obat.nama', 
                            'tb_m_golongan_obat.keterangan as golongan_obat',
                            'tb_m_obat.isi_tab as isibox',
                            'tb_m_obat.isi_strip as isistrip',
                            'tb_m_stok_harga_'.$inisial.'.harga_jual as patokanhargajual', 
                            'tb_m_stok_harga_'.$inisial.'.stok_akhir as total_stok'
                    ])
            ->leftJoin('tb_m_obat', 'tb_m_obat.id', '=', 'tb_m_stok_harga_'.$inisial.'.id_obat')
            ->leftJoin('tb_m_golongan_obat', 'tb_m_golongan_obat.id', '=', 'tb_m_obat.id_golongan_obat')
            ->where('tb_m_obat.is_deleted', 0)
            ->where('tb_m_stok_harga_'.$inisial.'.is_disabled', 0)
            ->get();

        echo json_encode($rekaps);
    }

    // API Pjm
    public function f36c008db00e367c7dae1c4a856e55ca() {
        $var = 3;
        //$apotek = 'PJM';
        $apotek = MasterApotek::find($var);
        $inisial = strtolower($apotek->nama_singkat);
        
        $rekaps = DB::table('tb_m_stok_harga_'.$inisial.'')
                ->select([
                            'tb_m_obat.id',
                            'tb_m_obat.barcode', 
                            'tb_m_obat.nama', 
                            'tb_m_golongan_obat.keterangan as golongan_obat',
                            'tb_m_obat.isi_tab as isibox',
                            'tb_m_obat.isi_strip as isistrip',
                            'tb_m_stok_harga_'.$inisial.'.harga_jual as patokanhargajual', 
                            'tb_m_stok_harga_'.$inisial.'.stok_akhir as total_stok'
                    ])
            ->leftJoin('tb_m_obat', 'tb_m_obat.id', '=', 'tb_m_stok_harga_'.$inisial.'.id_obat')
            ->leftJoin('tb_m_golongan_obat', 'tb_m_golongan_obat.id', '=', 'tb_m_obat.id_golongan_obat')
            ->where('tb_m_obat.is_deleted', 0)
            ->where('tb_m_stok_harga_'.$inisial.'.is_disabled', 0)
            ->get();

        echo json_encode($rekaps);
    }

    // API PG
    public function ed70a85853284244f63de7fbd08ccea5(){
        $var = 4;
        //$apotek = 'PG';
        $apotek = MasterApotek::find($var);
        $inisial = strtolower($apotek->nama_singkat);
        
        $rekaps = DB::table('tb_m_stok_harga_'.$inisial.'')
                ->select([
                            'tb_m_obat.id',
                            'tb_m_obat.barcode', 
                            'tb_m_obat.nama', 
                            'tb_m_golongan_obat.keterangan as golongan_obat',
                            'tb_m_obat.isi_tab as isibox',
                            'tb_m_obat.isi_strip as isistrip',
                            'tb_m_stok_harga_'.$inisial.'.harga_jual as patokanhargajual', 
                            'tb_m_stok_harga_'.$inisial.'.stok_akhir as total_stok'
                    ])
            ->leftJoin('tb_m_obat', 'tb_m_obat.id', '=', 'tb_m_stok_harga_'.$inisial.'.id_obat')
            ->leftJoin('tb_m_golongan_obat', 'tb_m_golongan_obat.id', '=', 'tb_m_obat.id_golongan_obat')
            ->where('tb_m_obat.is_deleted', 0)
            ->where('tb_m_stok_harga_'.$inisial.'.is_disabled', 0)
            ->get();

        echo json_encode($rekaps);
    }
}
