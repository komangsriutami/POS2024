<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Validator;
use App\Http\Requests;
use App\MasterObat;
use App\MasterGroupApotek;
use App\MasterApotek;
use App\MasterSuplier;
use App\MasterGolonganObat;
use App\MasterPenandaanObat;
use App\MasterProdusen;
use App\MasterSatuan;
use App\MasterApoteker;
use App\MasterMember;
use App\MasterDokter;
use App\MasterKlinik;
use App\User;
use App\RbacUserApotek;
use App\Absensi;
use App;
use Datatables;
use DB;
use Auth;
use Illuminate\Support\Carbon;
use Mail;
use Spipu\Html2Pdf\Html2Pdf;
use PDF;
use Response;
use Hash;
use Excel;
use Crypt;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Traits\DynamicConnectionTrait;

class ServiceAppController extends BaseController
{
    use DynamicConnectionTrait;
    public function apiLogin(Request $request)
    {
        $user = User::on($this->getConnectionName())->where('username', '=', $request->username)->first();
        $cekuser = User::on($this->getConnectionName())->where('username', '=', $request->username)->count();

        if ($cekuser >= 1) {
            if (Auth::guard()->attempt(['username' => $request->username, 'password' => $request->password], $request->remember)) {
                //$user = Auth::user();
               // $token = $user->createToken('MyApp')->accessToken;
                //$token = '';
                return response()->json(['user' => $user], 200);
            } else {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }
    }

 	// API Lavie
    public function ef4c2ce3032d8f024c320308d9880a06() {
        $inisial = 'lv';

        $rekaps = DB::connection($this->getConnectionName())->table('tb_m_stok_harga_'.$inisial.' as a')
        			->select(['b.id',
                            'b.barcode', 
                            'b.nama', 
                            'c.keterangan as golongan_obat',
                            'd.satuan',
                            'b.isi_tab as isibox',
                            'b.isi_strip as isistrip',
                            'a.harga_jual as patokanhargajual', 
                            'a.stok_akhir as total_stok'
                        ])
        			->leftJoin('tb_m_obat as b', 'b.id', '=', 'a.id_obat')
        			->leftJoin('tb_m_golongan_obat as c','c.id','=','b.id_golongan_obat')
                    ->leftJoin('tb_m_satuan as d','d.id','=','b.id_satuan')
        			->where('a.is_disabled', 0)
        			->where('a.is_deleted', 0)
        			->get();

        echo json_encode($rekaps);
        /* if(count($rekaps) > 0){ 
            return $this->sendResponse($rekaps, 'Successfully get data stock apotek lavie.');
        } 
        else{ 
            return $this->sendError('Failed.', ['error'=>'Failed get data stock apotek lavie']);
        } */
    }
    // API Bekul
    public function f31d5936f25442ecf43a2e4a9aa911d1() {
        $inisial = 'bkl';

        $rekaps = DB::connection($this->getConnectionName())->table('tb_m_stok_harga_'.$inisial.' as a')
        			->select(['b.id',
                            'b.barcode', 
                            'b.nama', 
                            'c.keterangan as golongan_obat',
                            'd.satuan',
                            'b.isi_tab as isibox',
                            'b.isi_strip as isistrip',
                            'b.harga_jual as patokanhargajual', 
                            'a.stok_akhir as total_stok'
                        ])
                    ->leftJoin('tb_m_obat as b', 'b.id', '=', 'a.id_obat')
                    ->leftJoin('tb_m_golongan_obat as c','c.id','=','b.id_golongan_obat')
                    ->leftJoin('tb_m_satuan as d','d.id','=','b.id_satuan')
                    ->where('a.is_disabled', 0)
                    ->where('a.is_deleted', 0)
        			->get();

        echo json_encode($rekaps);
        /* if(count($rekaps) > 0){ 
            return $this->sendResponse($rekaps, 'Successfully get data stock apotek bekul.');
        } 
        else{ 
            return $this->sendError('Failed.', ['error'=>'Failed get data stock apotek bekul']);
        } */
    }

    // API Pjm
    public function f36c008db00e367c7dae1c4a856e55ca() {
        $inisial = 'pjm';

        $rekaps = DB::connection($this->getConnectionName())->table('tb_m_stok_harga_'.$inisial.' as a')
        			->select(['b.id',
                            'b.barcode', 
                            'b.nama', 
                            'c.keterangan as golongan_obat',
                            'd.satuan',
                            'b.isi_tab as isibox',
                            'b.isi_strip as isistrip',
                            'b.harga_jual as patokanhargajual', 
                            'a.stok_akhir as total_stok'
                        ])
                    ->leftJoin('tb_m_obat as b', 'b.id', '=', 'a.id_obat')
                    ->leftJoin('tb_m_golongan_obat as c','c.id','=','b.id_golongan_obat')
                    ->leftJoin('tb_m_satuan as d','d.id','=','b.id_satuan')
                    ->where('a.is_disabled', 0)
                    ->where('a.is_deleted', 0)
        			->get();

        echo json_encode($rekaps);
        /* if(count($rekaps) > 0){ 
            return $this->sendResponse($rekaps, 'Successfully get data stock apotek pujamandala.');
        } 
        else{ 
            return $this->sendError('Failed.', ['error'=>'Failed get data stock apotek pujamandala']);
        } */
    }

    // API PG
    public function ed70a85853284244f63de7fbd08ccea5(){
        $inisial = 'pg';

        $rekaps = DB::connection($this->getConnectionName())->table('tb_m_stok_harga_'.$inisial.' as a')
        			->select(['b.id',
                            'b.barcode', 
                            'b.nama', 
                            'c.keterangan as golongan_obat',
                            'd.satuan',
                            'b.isi_tab as isibox',
                            'b.isi_strip as isistrip',
                            'b.harga_jual as patokanhargajual', 
                            'a.stok_akhir as total_stok'
                        ])
                    ->leftJoin('tb_m_obat as b', 'b.id', '=', 'a.id_obat')
                    ->leftJoin('tb_m_golongan_obat as c','c.id','=','b.id_golongan_obat')
                    ->leftJoin('tb_m_satuan as d','d.id','=','b.id_satuan')
                    ->where('a.is_disabled', 0)
                    ->where('a.is_deleted', 0)
        			->get();
                    
        echo json_encode($rekaps);
        /* if(count($rekaps) > 0){ 
            return $this->sendResponse($rekaps, 'Successfully get data stock apotek puri gading.');
        } 
        else{ 
            return $this->sendError('Failed.', ['error'=>'Failed get data stock apotek puri gading']);
        } */
    }

    // API TL
    public function f60ba84e9e162c05eaf305d15372e4f4(){
        $inisial = 'tl';

        $rekaps = DB::connection($this->getConnectionName())->table('tb_m_stok_harga_'.$inisial.' as a')
        			->select(['b.id',
                            'b.barcode', 
                            'b.nama', 
                            'c.keterangan as golongan_obat',
                            'd.satuan',
                            'b.isi_tab as isibox',
                            'b.isi_strip as isistrip',
                            'b.harga_jual as patokanhargajual', 
                            'a.stok_akhir as total_stok'
                        ])
                    ->leftJoin('tb_m_obat as b', 'b.id', '=', 'a.id_obat')
                    ->leftJoin('tb_m_golongan_obat as c','c.id','=','b.id_golongan_obat')
                    ->leftJoin('tb_m_satuan as d','d.id','=','b.id_satuan')
                    ->where('a.is_disabled', 0)
                    ->where('a.is_deleted', 0)
        			->get();
        
        echo json_encode($rekaps);
        /* if(count($rekaps) > 0){ 
            return $this->sendResponse($rekaps, 'Successfully get data stock apotek legian 777.');
        } 
        else{ 
            return $this->sendError('Failed.', ['error'=>'Failed get data stock apotek legian 777']);
        } */
    }


    // API SG
    public function f5dae429688af1c521ad87ac394192c6d(){
        $inisial = 'sg';

        $rekaps = DB::connection($this->getConnectionName())->table('tb_m_stok_harga_'.$inisial.' as a')
                    ->select(['b.id',
                            'b.barcode', 
                            'b.nama', 
                            'c.keterangan as golongan_obat',
                            'd.satuan',
                            'b.isi_tab as isibox',
                            'b.isi_strip as isistrip',
                            'b.harga_jual as patokanhargajual', 
                            'a.stok_akhir as total_stok'
                        ])
                    ->leftJoin('tb_m_obat as b', 'b.id', '=', 'a.id_obat')
                    ->leftJoin('tb_m_golongan_obat as c','c.id','=','b.id_golongan_obat')
                    ->leftJoin('tb_m_satuan as d','d.id','=','b.id_satuan')
                    ->where('a.is_disabled', 0)
                    ->where('a.is_deleted', 0)
                    ->get();
        
        echo json_encode($rekaps);
        /* if(count($rekaps) > 0){ 
            return $this->sendResponse($rekaps, 'Successfully get data stock apotek legian 777.');
        } 
        else{ 
            return $this->sendError('Failed.', ['error'=>'Failed get data stock apotek legian 777']);
        } */
    }

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

        $list = MasterApotek::select('id as STORE_NUMBER', 'nama_singkat as STORE_NAME', 'alamat as STORE_ADDRESS')->whereIn('id', [1, 2, 3, 4, 6, 7, 10, 11])->get()->toArray();

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
                    ->where('tb_m_obat.is_deleted', 0)
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
        
    
        $data_apotek_LV = $this->hitung_stok_apotek(1, 'lv');
        $data_apotek_BKL = $this->hitung_stok_apotek(2, 'bkl');
        $data_apotek_PJM = $this->hitung_stok_apotek(3, 'pjm');
        $data_apotek_PG = $this->hitung_stok_apotek(4, 'pg');
        $data_apotek_SG = $this->hitung_stok_apotek(6, 'sg');
        $data_apotek_HW = $this->hitung_stok_apotek(7, 'hw');
        $data_apotek_SRJ = $this->hitung_stok_apotek(10, 'srj');
        $data_apotek_MG = $this->hitung_stok_apotek(11, 'mg');

        $data_new = $data_apotek_LV;
        $data_new = array_merge($data_new, $data_apotek_BKL);
        $data_new = array_merge($data_new, $data_apotek_PJM);
        $data_new = array_merge($data_new, $data_apotek_PG);
        $data_new = array_merge($data_new, $data_apotek_SG);
        $data_new = array_merge($data_new, $data_apotek_HW);
        $data_new = array_merge($data_new, $data_apotek_SRJ);
        $data_new = array_merge($data_new, $data_apotek_MG);

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

    public function hitung_stok_apotek($var, $apotek) {
        $list = DB::connection($this->getConnectionName())->table('tb_m_stok_harga_'.$apotek.' as a')
        			->select([DB::raw(''.$var.' as STORE_NUMBER'),
        					'b.id as NO_SKU',
                            'a.stok_akhir as STOCK_AVAILABILITY',
                            'a.harga_jual as BASE_PRICE',
                            'a.harga_jual as DISCOUNT_PRICE'
                        ])
        			->leftJoin('tb_m_obat as b', 'b.id', '=', 'a.id_obat')
        			->where('a.is_deleted', 0)
        			->get()->toArray();
        $array = json_decode(json_encode($list), true);
       

        return $array;
    }

    // API template go apotek lavie
    public function template_lv() {
        $inisial = 'lv';

        $rekaps = DB::connection($this->getConnectionName())->table('tb_m_stok_harga_'.$inisial.' as a')
                    ->select(['b.id',
                            'b.barcode', 
                            'b.nama', 
                            'c.keterangan as golongan_obat',
                            'b.isi_tab as isibox',
                            'b.isi_strip as isistrip',
                            'b.harga_jual as patokanhargajual', 
                            'a.stok_akhir as total_stok',
                            'd.satuan'
                        ])
                    ->leftJoin('tb_m_obat as b', 'b.id', '=', 'a.id_obat')
                    ->leftJoin('tb_m_golongan_obat as c','c.id','=','b.id_golongan_obat')
                    ->leftJoin('tb_m_satuan as d','d.id','=','b.id_satuan')
                    ->where('a.is_disabled', 0)
                    ->where('a.is_deleted', 0)
                    ->get();
        
    

        $collection = collect();
        $no = 0;
        $total_excel=0;
        foreach($rekaps as $rekap) {
            $no++;
            $collection[] = array(
                $rekap->id,
                $rekap->nama,
                $rekap->satuan,
                $rekap->patokanhargajual,
                $rekap->total_stok
            );
        }

        return Excel::download(new class($collection) implements FromCollection, WithHeadings {

                    public function __construct($collection)
                    {
                        $this->collection = $collection;
                    }

                    public function headings(): array
                    {
                        return ['Kode Item', 'Nama Item', 'Satuan', 'Harga', 'Stok'];
                    } 

                    public function collection()
                    {
                        return $this->collection;
                    }
        },"Template Sinkron Go Apotek_Lavie.xlsx");
    }
    // API Bekul
    public function template_bkl() {
        $inisial = 'bkl';

        $rekaps = DB::connection($this->getConnectionName())->table('tb_m_stok_harga_'.$inisial.' as a')
                    ->select(['b.id',
                            'b.barcode', 
                            'b.nama', 
                            'c.keterangan as golongan_obat',
                            'b.isi_tab as isibox',
                            'b.isi_strip as isistrip',
                            'b.harga_jual as patokanhargajual', 
                            'a.stok_akhir as total_stok',
                            'd.satuan'
                        ])
                    ->leftJoin('tb_m_obat as b', 'b.id', '=', 'a.id_obat')
                    ->leftJoin('tb_m_golongan_obat as c','c.id','=','b.id_golongan_obat')
                    ->leftJoin('tb_m_satuan as d','d.id','=','b.id_satuan')
                    ->where('a.is_disabled', 0)
                    ->where('a.is_deleted', 0)
                    ->get();
        
    

        $collection = collect();
        $no = 0;
        $total_excel=0;
        foreach($rekaps as $rekap) {
            $no++;
            $collection[] = array(
                $rekap->id,
                $rekap->nama,
                $rekap->satuan,
                $rekap->patokanhargajual,
                $rekap->total_stok
            );
        }

        return Excel::download(new class($collection) implements FromCollection, WithHeadings {

                    public function __construct($collection)
                    {
                        $this->collection = $collection;
                    }

                    public function headings(): array
                    {
                        return ['Kode Item', 'Nama Item', 'Satuan', 'Harga', 'Stok'];
                    } 

                    public function collection()
                    {
                        return $this->collection;
                    }
        },"Template Sinkron Go Apotek_Bekul.xlsx");
    }

    // API Pjm
    public function template_pjm() {
        $inisial = 'pjm';

        $rekaps = DB::connection($this->getConnectionName())->table('tb_m_stok_harga_'.$inisial.' as a')
                    ->select(['b.id',
                            'b.barcode', 
                            'b.nama', 
                            'c.keterangan as golongan_obat',
                            'b.isi_tab as isibox',
                            'b.isi_strip as isistrip',
                            'b.harga_jual as patokanhargajual', 
                            'a.stok_akhir as total_stok',
                            'd.satuan'
                        ])
                    ->leftJoin('tb_m_obat as b', 'b.id', '=', 'a.id_obat')
                    ->leftJoin('tb_m_golongan_obat as c','c.id','=','b.id_golongan_obat')
                    ->leftJoin('tb_m_satuan as d','d.id','=','b.id_satuan')
                    ->where('a.is_disabled', 0)
                    ->where('a.is_deleted', 0)
                    ->get();
        
    

        $collection = collect();
        $no = 0;
        $total_excel=0;
        foreach($rekaps as $rekap) {
            $no++;
            $collection[] = array(
                $rekap->id,
                $rekap->nama,
                $rekap->satuan,
                $rekap->patokanhargajual,
                $rekap->total_stok
            );
        }

        return Excel::download(new class($collection) implements FromCollection, WithHeadings {

                    public function __construct($collection)
                    {
                        $this->collection = $collection;
                    }

                    public function headings(): array
                    {
                        return ['Kode Item', 'Nama Item', 'Satuan', 'Harga', 'Stok'];
                    } 

                    public function collection()
                    {
                        return $this->collection;
                    }
        },"Template Sinkron Go Apotek_Pujamandala.xlsx");
    }

    // API PG
    public function template_pg(){
        $inisial = 'pg';

        $rekaps = DB::connection($this->getConnectionName())->table('tb_m_stok_harga_'.$inisial.' as a')
                    ->select(['b.id',
                            'b.barcode', 
                            'b.nama', 
                            'c.keterangan as golongan_obat',
                            'b.isi_tab as isibox',
                            'b.isi_strip as isistrip',
                            'b.harga_jual as patokanhargajual', 
                            'a.stok_akhir as total_stok',
                            'd.satuan'
                        ])
                    ->leftJoin('tb_m_obat as b', 'b.id', '=', 'a.id_obat')
                    ->leftJoin('tb_m_golongan_obat as c','c.id','=','b.id_golongan_obat')
                    ->leftJoin('tb_m_satuan as d','d.id','=','b.id_satuan')
                    ->where('a.is_disabled', 0)
                    ->where('a.is_deleted', 0)
                    ->get();
        
    

        $collection = collect();
        $no = 0;
        $total_excel=0;
        foreach($rekaps as $rekap) {
            $no++;
            $collection[] = array(
                $rekap->id,
                $rekap->nama,
                $rekap->satuan,
                $rekap->patokanhargajual,
                $rekap->total_stok
            );
        }

        return Excel::download(new class($collection) implements FromCollection, WithHeadings {

                    public function __construct($collection)
                    {
                        $this->collection = $collection;
                    }

                    public function headings(): array
                    {
                        return ['Kode Item', 'Nama Item', 'Satuan', 'Harga', 'Stok'];
                    } 

                    public function collection()
                    {
                        return $this->collection;
                    }
        },"Template Sinkron Go Apotek_Puri Gading.xlsx");
    }

    // API TL
    public function template_tl(){
        $inisial = 'tl';

        $rekaps = DB::connection($this->getConnectionName())->table('tb_m_stok_harga_'.$inisial.' as a')
                    ->select(['b.id',
                            'b.barcode', 
                            'b.nama', 
                            'c.keterangan as golongan_obat',
                            'b.isi_tab as isibox',
                            'b.isi_strip as isistrip',
                            'b.harga_jual as patokanhargajual', 
                            'a.stok_akhir as total_stok',
                            'd.satuan'
                        ])
                    ->leftJoin('tb_m_obat as b', 'b.id', '=', 'a.id_obat')
                    ->leftJoin('tb_m_golongan_obat as c','c.id','=','b.id_golongan_obat')
                    ->leftJoin('tb_m_satuan as d','d.id','=','b.id_satuan')
                    ->where('a.is_disabled', 0)
                    ->where('a.is_deleted', 0)
                    ->get();
        
    

        $collection = collect();
        $no = 0;
        $total_excel=0;
        foreach($rekaps as $rekap) {
            $no++;
            $collection[] = array(
                $rekap->id,
                $rekap->nama,
                $rekap->satuan,
                $rekap->patokanhargajual,
                $rekap->total_stok
            );
        }

        return Excel::download(new class($collection) implements FromCollection, WithHeadings {

                    public function __construct($collection)
                    {
                        $this->collection = $collection;
                    }

                    public function headings(): array
                    {
                        return ['Kode Item', 'Nama Item', 'Satuan', 'Harga', 'Stok'];
                    } 

                    public function collection()
                    {
                        return $this->collection;
                    }
        },"Template Sinkron Go Apotek_Legian.xlsx");
    }

    public function get_data_apoteker($id_apotek) {
       /* header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: *');
        header('Access-Control-Allow-Headers: *');*/

        $user_apoteks = RbacUserApotek::select(['rbac_user_apotek.id_user'])->get(); //->where('id_apotek', $id_apotek)
        $apoteker = User::on($this->getConnectionName())->where('is_deleted', 0)->get(); //whereIn('id', $user_apoteks)->
        $apoteker = $apoteker->toArray();
       /* $plus_1 = User::on($this->getConnectionName())->find(1);
        $plus_1 = $plus_1->toArray();
        $apoteker[] = $plus_1;

        $plus_1 = User::on($this->getConnectionName())->find(1);
        $plus_1 = $plus_1->toArray();
        $apoteker[] = $plus_1;*/

      
        

        echo json_encode($apoteker);
    }

    public function cek_absen($id_user, $id_apotek) {
        $absensi = Absensi::on($this->getConnectionName())->where('id_user', $id_user)->where('id_apotek', $id_apotek)->where('tgl', date('Y-m-d'))->first();
        $apotek = MasterApotek::on($this->getConnectionName())->find($id_apotek);

        $new_array = array();
        if(empty($absensi)) {
            $new_array['id'] = null;
            $new_array['id_apotek'] = (int) $id_apotek;
            $new_array['nama_apotek'] = $apotek->nama;
            $new_array['id_kasir_aktif'] = null;
            $new_array['id_user'] = (int) $id_user;
            $new_array['tgl'] = date('Y-m-d');
            $new_array['jam_datang'] = null;
            $new_array['jam_pulang'] = null;
            $new_array['jumlah_jam_kerja'] = null;
            $new_array['jam_datang_split'] = null;
            $new_array['jam_pulang_split'] = null;
            $new_array['created_at'] = date('Y-m-d');
        } else {
            $new_array['id'] = $absensi->id;
            $new_array['id_apotek'] = (int) $id_apotek;
            $new_array['nama_apotek'] = $apotek->nama;
            $new_array['id_kasir_aktif'] = (int) $absensi->id_kasir_aktif;
            $new_array['id_user'] = (int) $id_user;
            $new_array['tgl'] = $absensi->tgl;
            $new_array['jam_datang'] = $absensi->jam_datang;
            $new_array['jam_pulang'] = $absensi->jam_pulang;
            $new_array['jumlah_jam_kerja'] = $absensi->jumlah_jam_kerja;
            $new_array['jam_datang_split'] = $absensi->jam_datang_split;
            $new_array['jam_pulang_split'] = $absensi->jam_pulang_split;
            $new_array['created_at'] = $absensi->created_at;
        }

        echo json_encode($new_array);
    }

    public function send_absen($id_user, $id_apotek, $password, $id_jenis_absen) {
        $user = User::on($this->getConnectionName())->find($id_user);
        $password_ = bcrypt($password);

        if(Hash::check($password,$user->password)) {
            // jika password usernya sama
            $cek_absen_ = Absensi::on($this->getConnectionName())->where('id_user', $id_user)->where('id_apotek', $id_apotek)->where('tgl', date('Y-m-d'))->first();

            if(empty($cek_absen_)) {
                $cek_absen_ = new Absensi;
                $cek_absen_->setDynamicConnection();
                $cek_absen_->id_apotek = $id_apotek;
                $cek_absen_->id_kasir_aktif = 1;
                $cek_absen_->id_user = $id_user;
                $cek_absen_->tgl = date('Y-m-d');
                $cek_absen_->created_at = date('Y-m-d H:i:s');
            }
            
            if($id_jenis_absen == 1) {
                $cek_absen_->jam_datang = date('H:i:s');
                $cek_absen_->jam_pulang = null;
                $cek_absen_->jumlah_jam_kerja = null;
                $cek_absen_->jam_datang_split = null;
                $cek_absen_->jam_pulang_split = null;
            } else if($id_jenis_absen == 2) {
                $cek_absen_->jam_pulang = date('H:i:s');
                $date1 = strtotime($cek_absen_->tgl." ".$cek_absen_->jam_datang);
                $date2 = strtotime($cek_absen_->tgl." ".date('H:i:s'));
                $diff   = $date2 - $date1;
                $jam = $diff/(60 * 60);
                $cek_absen_->jumlah_jam_kerja = $jam; 
                $cek_absen_->jam_datang_split = null;
                $cek_absen_->jam_pulang_split = null;
            } else if($id_jenis_absen == 3) {
                $cek_absen_->jam_datang_split = date('H:i:s');
                $cek_absen_->jam_pulang_split = null;
            } else {
                $cek_absen_->jam_pulang_split = date('H:i:s');

                $date1 = strtotime($cek_absen_->tgl." ".$cek_absen_->jam_datang);
                $date2 = strtotime($cek_absen_->tgl." ".$cek_absen_->jam_pulang);
                $diff1   = $date2 - $date1;
                $jam1 = $diff1/(60 * 60);

                $date3 = strtotime($cek_absen_->tgl." ".$cek_absen_->jam_datang_split);
                $date4 = strtotime($cek_absen_->tgl." ".date('H:i:s'));
                $diff2   = $date4 - $date3;
                $jam2 = $diff2/(60 * 60);

                $cek_absen_->jumlah_jam_kerja = $jam1 + $jam2; 
            }

            $cek_absen_->save();
            echo 1;
        } else {
            // jika password usernya tidak sama
            echo 0;
            
        }
    }

    public function list_data_rekap_absensi_per_bulan($id_user, $tahun) {
        $array_all = array();

        $absensi = Absensi::select([
                                DB::raw('YEAR(tgl) as tahun'),
                                DB::raw('MONTH(tgl) as bulan_ke'),
                                DB::raw('COUNT(id) as jumlah_kehadiran')
                            ])
                            ->where('id_user', $id_user)
                            ->where(DB::raw('YEAR(tgl)'), $tahun)
                            ->groupBy(DB::raw('MONTH(tgl)'))
                            ->orderBy(DB::raw('MONTH(tgl)'), 'DESC')
                            ->get();

        foreach ($absensi as $key => $obj) {
            $monthName = date("F", mktime(0, 0, 0, $obj->bulan_ke, 10));
            $array_all[] = array('tahun' => $obj->tahun, 'bulan_ke' => $obj->bulan_ke, 'bulan' => $monthName, 'jumlah_kehadiran' => $obj->jumlah_kehadiran);
        }


        echo json_encode($array_all);
    }

    public function list_data_rekap_absensi_per_hari($id_user, $tahun, $bulan) {
        $array_all = array();
        $jum_hari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

        $absensi = Absensi::select([
                                'tb_absensi.id',
                                'tb_absensi.tgl',
                                'tb_absensi.id_apotek',
                                'tb_absensi.jam_datang',
                                'tb_absensi.jam_pulang',
                                'tb_absensi.jam_datang_split',
                                'tb_absensi.jam_pulang_split',
                                'tb_absensi.jumlah_jam_kerja',
                                'a.nama_singkat as nama_apotek',
                                DB::raw('YEAR(tb_absensi.tgl) as tahun'),
                                DB::raw('MONTH(tb_absensi.tgl) as bulan_ke')
                            ])
                            ->leftJoin('tb_m_apotek as a', 'a.id', '=', 'tb_absensi.id_apotek')
                            ->where('tb_absensi.id_user', $id_user)
                            ->where(DB::raw('YEAR(tb_absensi.tgl)'), $tahun)
                            ->where(DB::raw('MONTH(tb_absensi.tgl)'), $bulan)
                            ->orderBy(DB::raw('DATE(tb_absensi.tgl)'), 'ASC')
                            ->get();

        foreach ($absensi as $key => $obj) {
            $tanggal = date('d F Y', strtotime($obj->tgl));
            $tanggal_ke = date('d', strtotime($obj->tgl));
            $array_all[] = array('id' => $obj->id, 'tanggal_ke' => $tanggal_ke, 'tanggal' => $tanggal, 'id_apotek' => $obj->id_apotek, 'nama_apotek' => $obj->nama_apotek, 'jam_datang' => $obj->jam_datang, 'jam_pulang' => $obj->jam_pulang, 'jam_datang_split' => $obj->jam_datang_split, 'jam_pulang_split' => $obj->jam_pulang_split, 'jumlah_jam_kerja' => number_format($obj->jumlah_jam_kerja,2));
        }

        echo json_encode($array_all);
    }

    public function GetDataGroupApotek(Request $request) {
        $data = (array)json_decode(Crypt::Decrypt($request->data,$request->key));
        if(isset($request->key)) {
            if($data['kategori'] == 'all') {
                $all = MasterGroupApotek::on($this->getConnectionName())->where('is_deleted', 0)->get();
            } else {
                if(isset($data['id'])) {
                    $all = MasterGroupApotek::on($this->getConnectionName())->where('is_deleted', 0)->where('id', $data['id'])->first();
                } else {
                    $all = collect();
                }
            }

            if(count($all) > 0){ 
                return $this->sendResponse($all, 'Successfully get data.');
            } 
            else{ 
                return $this->sendError('Failed.', ['error'=>'Failed get data, data is not found']);
            } 
        } else {
            return $this->sendError('Failed.', ['error'=>'Failed get data, key is not found']);
        }
    }


    public function GetDataApotek(Request $request) {
        $data = (array)json_decode(Crypt::Decrypt($request->data,$request->key));
        if(isset($request->key)) {
            if($data['kategori'] == 'all') {
                $all = MasterApotek::on($this->getConnectionName())->where('is_deleted', 0)->get();
            } else {
                if(isset($data['id'])) {
                    $all = MasterApotek::on($this->getConnectionName())->where('is_deleted', 0)->where('id', $data['id'])->first();
                } else {
                    $all = collect();
                }
            }

            if(count($all) > 0){ 
                return $this->sendResponse($all, 'Successfully get data.');
            } 
            else{ 
                return $this->sendError('Failed.', ['error'=>'Failed get data, data is not found']);
            } 
        } else {
            return $this->sendError('Failed.', ['error'=>'Failed get data, key is not found']);
        }
    }

    public function GetDataUser(Request $request) {
        $data = (array)json_decode(Crypt::Decrypt($request->data,$request->key));
        if(isset($request->key)) {
            if($data['kategori'] == 'all') {
                $all = User::on($this->getConnectionName())->where('is_deleted', 0)->get();
            } else {
                if(isset($data['id'])) {
                    $all = User::on($this->getConnectionName())->where('is_deleted', 0)->where('id', $data['id'])->first();
                } else {
                    $all = collect();
                }
            }

            if(count($all) > 0){ 
                return $this->sendResponse($all, 'Successfully get data.');
            } 
            else{ 
                return $this->sendError('Failed.', ['error'=>'Failed get data, data is not found']);
            } 
        } else {
            return $this->sendError('Failed.', ['error'=>'Failed get data, key is not found']);
        }
    }

    public function GetDataSuplier(Request $request) {
        $data = (array)json_decode(Crypt::Decrypt($request->data,$request->key));
        if(isset($request->key)) {
            if($data['kategori'] == 'all') {
                $all = MasterSuplier::on($this->getConnectionName())->where('is_deleted', 0)->get();
            } else {
                if(isset($data['id'])) {
                    $all = MasterSuplier::on($this->getConnectionName())->where('is_deleted', 0)->where('id', $data['id'])->first();
                } else {
                    $all = collect();
                }
            }

            if(count($all) > 0){ 
                return $this->sendResponse($all, 'Successfully get data.');
            } 
            else{ 
                return $this->sendError('Failed.', ['error'=>'Failed get data, data is not found']);
            } 
        } else {
            return $this->sendError('Failed.', ['error'=>'Failed get data, key is not found']);
        }
    }

    public function GetDataGolonganObat(Request $request) {
        $data = (array)json_decode(Crypt::Decrypt($request->data,$request->key));
        if(isset($request->key)) {
            if($data['kategori'] == 'all') {
                $all = MasterGolonganObat::on($this->getConnectionName())->where('is_deleted', 0)->get();
            } else {
                if(isset($data['id'])) {
                    $all = MasterGolonganObat::on($this->getConnectionName())->where('is_deleted', 0)->where('id', $data['id'])->first();
                } else {
                    $all = collect();
                }
            }

            if(count($all) > 0){ 
                return $this->sendResponse($all, 'Successfully get data.');
            } 
            else{ 
                return $this->sendError('Failed.', ['error'=>'Failed get data, data is not found']);
            } 
        } else {
            return $this->sendError('Failed.', ['error'=>'Failed get data, key is not found']);
        }
    }

    public function GetDataPenandaanObat(Request $request) {
        $data = (array)json_decode(Crypt::Decrypt($request->data,$request->key));
        if(isset($request->key)) {
            if($data['kategori'] == 'all') {
                $all = MasterPenandaanObat::on($this->getConnectionName())->where('is_deleted', 0)->get();
            } else {
                if(isset($data['id'])) {
                    $all = MasterPenandaanObat::on($this->getConnectionName())->where('is_deleted', 0)->where('id', $data['id'])->first();
                } else {
                    $all = collect();
                }
            }

            if(count($all) > 0){ 
                return $this->sendResponse($all, 'Successfully get data.');
            } 
            else{ 
                return $this->sendError('Failed.', ['error'=>'Failed get data, data is not found']);
            } 
        } else {
            return $this->sendError('Failed.', ['error'=>'Failed get data, key is not found']);
        }
    }

    public function GetDataProdusen(Request $request) {
        $data = (array)json_decode(Crypt::Decrypt($request->data,$request->key));
        if(isset($request->key)) {
            if($data['kategori'] == 'all') {
                $all = MasterProdusen::on($this->getConnectionName())->where('is_deleted', 0)->get();
            } else {
                if(isset($data['id'])) {
                    $all = MasterProdusen::on($this->getConnectionName())->where('is_deleted', 0)->where('id', $data['id'])->first();
                } else {
                    $all = collect();
                }
            }

            if(count($all) > 0){ 
                return $this->sendResponse($all, 'Successfully get data.');
            } 
            else{ 
                return $this->sendError('Failed.', ['error'=>'Failed get data, data is not found']);
            } 
        } else {
            return $this->sendError('Failed.', ['error'=>'Failed get data, key is not found']);
        }
    }

    public function GetDataSatuan(Request $request) {
        $data = (array)json_decode(Crypt::Decrypt($request->data,$request->key));
        if(isset($request->key)) {
            if($data['kategori'] == 'all') {
                $all = MasterSatuan::on($this->getConnectionName())->where('is_deleted', 0)->get();
            } else {
                if(isset($data['id'])) {
                    $all = MasterSatuan::on($this->getConnectionName())->where('is_deleted', 0)->where('id', $data['id'])->first();
                } else {
                    $all = collect();
                }
            }

            if(count($all) > 0){ 
                return $this->sendResponse($all, 'Successfully get data.');
            } 
            else{ 
                return $this->sendError('Failed.', ['error'=>'Failed get data, data is not found']);
            } 
        } else {
            return $this->sendError('Failed.', ['error'=>'Failed get data, key is not found']);
        }
    }

    public function GetDataMember(Request $request) {
        $data = (array)json_decode(Crypt::Decrypt($request->data,$request->key));
        if(isset($request->key)) {
            if($data['kategori'] == 'all') {
                $all = MasterMember::on($this->getConnectionName())->where('is_deleted', 0)->get();
            } else {
                if(isset($data['id'])) {
                    $all = MasterMember::on($this->getConnectionName())->where('is_deleted', 0)->where('id', $data['id'])->first();
                } else {
                    $all = collect();
                }
            }

            if(count($all) > 0){ 
                return $this->sendResponse($all, 'Successfully get data.');
            } 
            else{ 
                return $this->sendError('Failed.', ['error'=>'Failed get data, data is not found']);
            } 
        } else {
            return $this->sendError('Failed.', ['error'=>'Failed get data, key is not found']);
        }
    }

    public function GetDataApoteker(Request $request) {
        $data = (array)json_decode(Crypt::Decrypt($request->data,$request->key));
        if(isset($request->key)) {
            if($data['kategori'] == 'all') {
                $all = MasterApoteker::on($this->getConnectionName())->where('is_deleted', 0)->get();
            } else {
                if(isset($data['id'])) {
                    $all = MasterApoteker::on($this->getConnectionName())->where('is_deleted', 0)->where('id', $data['id'])->first();
                } else {
                    $all = collect();
                }
            }

            if(count($all) > 0){ 
                return $this->sendResponse($all, 'Successfully get data.');
            } 
            else{ 
                return $this->sendError('Failed.', ['error'=>'Failed get data, data is not found']);
            } 
        } else {
            return $this->sendError('Failed.', ['error'=>'Failed get data, key is not found']);
        }
    }

    public function GetDataKlinik(Request $request) {
        $data = (array)json_decode(Crypt::Decrypt($request->data,$request->key));
        if(isset($request->key)) {
            if($data['kategori'] == 'all') {
                $all = MasterKlinik::on($this->getConnectionName())->where('is_deleted', 0)->get();
            } else {
                if(isset($data['id'])) {
                    $all = MasterKlinik::on($this->getConnectionName())->where('is_deleted', 0)->where('id', $data['id'])->first();
                } else {
                    $all = collect();
                }
            }

            if(count($all) > 0){ 
                return $this->sendResponse($all, 'Successfully get data.');
            } 
            else{ 
                return $this->sendError('Failed.', ['error'=>'Failed get data, data is not found']);
            } 
        } else {
            return $this->sendError('Failed.', ['error'=>'Failed get data, key is not found']);
        }
    }

    public function GetDataDokter(Request $request) {
        $data = (array)json_decode(Crypt::Decrypt($request->data,$request->key));
        if(isset($request->key)) {
            if($data['kategori'] == 'all') {
                $all = MasterDokter::on($this->getConnectionName())->where('is_deleted', 0)->get();
            } else {
                if(isset($data['id'])) {
                    $all = MasterDokter::on($this->getConnectionName())->where('is_deleted', 0)->where('id', $data['id'])->first();
                } else {
                    $all = collect();
                }
            }

            if(count($all) > 0){ 
                return $this->sendResponse($all, 'Successfully get data.');
            } 
            else{ 
                return $this->sendError('Failed.', ['error'=>'Failed get data, data is not found']);
            } 
        } else {
            return $this->sendError('Failed.', ['error'=>'Failed get data, key is not found']);
        }
    }
}
