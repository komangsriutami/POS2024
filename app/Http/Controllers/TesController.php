<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Flow\ETL\Flow;
use Flow\ETL\Extractor\ArrayExtractor;
use Flow\ETL\Loader\CallbackLoader;
use Flow\ETL\Row;
use Flow\ETL\Rows;
use Flow\ETL\Transformer\AutoCastTransformer;
use Flow\ETL\PHP\Type\AutoCaster;
use Flow\ETL\Transformer\CallbackRowTransformer;
use App\Http\Controllers\PdoLoader;
use fab2s\YaEtl\YaEtl;
use fab2s\YaEtl\Laravel\DbExtractor;
use DB;
use PDO;
class TesController extends Controller
{
    public function index_sa()
    {
        $extractQuery = DB::table('tb_detail_nota_penjualan')
            ->select([
                'tb_detail_nota_penjualan.id',
                'tb_detail_nota_penjualan.id_nota',
                'd.nama_singkat as id_apotek_nota',
                DB::raw('IFNULL(c.nama, null) as id_pasien'),
                'a.tgl_nota',
                'b.nama as id_obat',
                'tb_detail_nota_penjualan.hb_ppn as hbppn',
                'tb_detail_nota_penjualan.harga_jual',
                'tb_detail_nota_penjualan.margin',
                'tb_detail_nota_penjualan.jumlah',
                'tb_detail_nota_penjualan.diskon',
                DB::raw('((tb_detail_nota_penjualan.harga_jual * tb_detail_nota_penjualan.jumlah) - tb_detail_nota_penjualan.diskon) as total'),
                DB::raw('(tb_detail_nota_penjualan.hb_ppn * tb_detail_nota_penjualan.jumlah) as total_hbppn')
            ])
            ->join('tb_nota_penjualan as a', 'a.id', '=', 'tb_detail_nota_penjualan.id_nota')
            ->join('tb_m_obat as b', 'b.id', '=', 'tb_detail_nota_penjualan.id_obat')
            ->leftjoin('tb_m_member as c', 'c.id', '=', 'a.id_pasien')
            ->join('tb_m_apotek as d', 'd.id', '=', 'a.id_apotek_nota')
            ->whereDate('a.tgl_nota', '>=', '2023-01-01')
            ->whereDate('a.tgl_nota', '<=', '2023-12-31')
            ->where('a.is_deleted', 0)
            ->where('tb_detail_nota_penjualan.is_deleted', 0);

        // instantiate the generic db extractor
        $dbExtractor = new DbExtractor;

        // set extract query and fetch 5000 records at a time
        $dbExtractor->setExtractQuery($extractQuery)->setBatchSize(5000);


        // define load query
        $loadQuery = DB::connection('db_dw')->table('fact_sales');

        // instantiate the generic db loader
        $dbLoader = new DbLoader($loadQuery);

        // set load query and the fields to use in the update where/insert field list
        // clause
        $dbLoader->setLoadQuery($loadQuery)->setWhereFields([
            'fact_sales.id',
            'fact_sales.id_nota',
            'fact_sales.id_apotek_nota',
            'fact_sales.id_pasien',
            'fact_sales.tgl_nota',
            'fact_sales.id_obat',
            'fact_sales.hbppn',
            'fact_sales.hargajual',
            'fact_sales.margin',
            'fact_sales.jumlah',
            'fact_sales.diskon',
            'fact_sales.total',
            'fact_sales.totalhbppn',
            'fact_sales.id_waktu'
        ]);

        // run the ETL
        $yaEtl = new YaEtl;
        $yaEtl->from($dbExtractor)
            ->transform(new Transformer)
            ->to($dbLoader)
            ->exec();
    }

    public function index()
    {
        // Step 1: Extract Data from REST API
        $client = new Client();
        $response = $client->get('http://localhost/POS2024/public/api/getDataSales?tgl_start=2021-01-02&tgl_end=2021-01-02');
        $rsp = json_decode($response->getBody(), true);

        if ($rsp['success']) {
            $data = $rsp['data'];
            
            // Step 2: Transform
            $rows = array();
            foreach($data as $key => $obj) {
                $row = array();
                $row['id'] = $obj['id'];
                $row['id_nota'] = $obj['id_nota'];
                $row['id_apotek_nota'] = $obj['id_apotek_nota'];
                $row['id_pasien'] = $obj['id_pasien'];
                $row['tgl_nota'] = $obj['tgl_nota'];
                $row['id_obat'] = $obj['id_obat'];
                $row['hbppn'] = $obj['hbppn'];
                $row['hargajual'] = $obj['harga_jual'];
                $row['margin'] = $obj['margin'];
                $row['jumlah'] = $obj['jumlah'];
                $row['diskon'] = $obj['diskon'];
                $row['total'] = $obj['total'];
                $row['totalhbppn'] = $obj['total_hbppn'];
                $row['id_waktu'] = 1;
                $rows[] = $row;
            }

            // Step 3: Load
            DB::connection('db_dw')->table('fact_sales')->insert($rows);

            echo 'Data processed successfully.';
        } else {
            echo 'Failed to process data.';
        }
    }


    public function getPembelian()
    {
        // Step 1: Extract Data from REST API
        $client = new Client();
        $response = $client->get('http://localhost/POS2024/public/api/getDataPurchasing?tgl_start=2021-01-02&tgl_end=2021-01-02');
        $rsp = json_decode($response->getBody(), true);

        if ($rsp['success']) {
            $data = $rsp['data'];
            
            // Step 2: Transform
            $rows = array();
            foreach($data as $key => $obj) {
                $row = array();
                $row['id'] = $obj['id'];
                $row['id_nota'] = $obj['id_nota'];
                $row['id_apotek_nota'] = $obj['id_apotek_nota'];
                $row['id_suplier'] = $obj['id_suplier'];
                $row['tgl_nota'] = $obj['tgl_nota'];
                $row['id_obat'] = $obj['id_obat'];
                $row['hb'] = $obj['hb'];
                $row['ppn'] = $obj['ppn'];
                $row['hbppn'] = $obj['hbppn'];
                $row['jumlah'] = $obj['jumlah'];
                $row['diskon'] = $obj['diskon'];
                $row['total'] = $obj['total'];
                $row['id_waktu'] = 1;
                $rows[] = $row;
            }

            // Step 3: Load
            DB::connection('db_dw')->table('fact_sales')->insert($rows);

            echo 'Data processed successfully.';
        } else {
            echo 'Failed to process data.';
        }
    }
   
    public function index_manual()
    {
        // Step 1: Extract Data from REST API
        $client = new Client();
        $response = $client->get('http://localhost/POS2024/public/api/getDataSales?tgl_start=2023-03-01&tgl_end=2023-03-31');
        $rsp = json_decode($response->getBody(), true);

        if ($rsp['success']) {
            $data = $rsp['data'];
            
            // Step 2: Transform
            $rows = array();
            foreach($data as $key => $obj) {
                $row = array();
                $row['id'] = $obj['id'];
                $row['id_nota'] = $obj['id_nota'];
                $row['id_apotek_nota'] = $obj['id_apotek_nota'];
                $row['id_pasien'] = $obj['id_pasien'];
                $row['tgl_nota'] = $obj['tgl_nota'];
                $row['id_obat'] = $obj['id_obat'];
                $row['hbppn'] = $obj['hbppn'];
                $row['hargajual'] = $obj['harga_jual'];
                $row['margin'] = $obj['margin'];
                $row['jumlah'] = $obj['jumlah'];
                $row['diskon'] = $obj['diskon'];
                $row['total'] = $obj['total'];
                $row['totalhbppn'] = $obj['total_hbppn'];
                $row['id_waktu'] = 1;
                $rows[] = $row;
            }

            // Step 3: Load
            DB::connection('db_dw')->table('fact_sales')->insert($rows);

            echo 'Data processed successfully.';
        } else {
            echo 'Failed to process data.';
        }
    }

    public function index_()
    {
        // Step 1: Extract Data from REST API
        $client = new Client();
        $response = $client->get('http://localhost/POS2024/public/api/getDataSales?tgl_start=2023-03-01&tgl_end=2023-03-31');
        $rsp = json_decode($response->getBody(), true);

        if ($rsp['success']) {
            $data = $rsp['data'];
            
            $pdo = new \PDO("mysql:host=localhost;dbname=dw_apotekbw", "root", "210594");

            $etl = (new Flow())
                ->extract(new ArrayExtractor($data))
                ->transform(new CallbackRowTransformer(function (Row $row) {
                    // Transform data as needed
                    return $row->with('id', $row->get('id'))
                               ->with('id_nota', $row->get('id_nota'))
                               ->with('id_apotek_nota', $row->get('id_apotek_nota'))
                               ->with('id_pasien', $row->get('id_pasien'))
                               ->with('tgl_nota', $row->get('tgl_nota'))
                               ->with('id_obat', $row->get('id_obat'))
                               ->with('hbppn', $row->get('hbppn'))
                               ->with('harga_jual', $row->get('harga_jual'))
                               ->with('margin', $row->get('margin'))
                               ->with('jumlah', $row->get('jumlah'))
                               ->with('diskon', $row->get('diskon'))
                               ->with('total', $row->get('total'))
                               ->with('total_hbppn', $row->get('total_hbppn'));
                }))
                 ->load(new PdoLoader($pdo)); // Gunakan PdoLoader

            $etl->run();

            echo 'Data processed successfully.';
        } else {
            echo 'Failed to process data.';
        }
    }

    /*public function store(TesDataTableEditor $editor)
    {
        return $editor->process(request());

        new Row\Entry\IntegerEntry('id', $item['id']),
            new Row\Entry\StringEntry('id_nota', $item['id_nota']),
            new Row\Entry\StringEntry('id_apotek_nota', $item['id_apotek_nota']),
            new Row\Entry\StringEntry('id_pasien', $item['id_pasien']),
            new Row\Entry\DateEntry('tgl_nota', new \DateTime($item['tgl_nota'])),
            new Row\Entry\StringEntry('id_obat', $item['id_obat']),
            new Row\Entry\FloatEntry('hbppn', $item['hbppn']),
            new Row\Entry\FloatEntry('harga_jual', $item['harga_jual']),
            new Row\Entry\FloatEntry('margin', $item['margin']),
            new Row\Entry\IntegerEntry('jumlah', $item['jumlah']),
            new Row\Entry\FloatEntry('diskon', $item['diskon']),
            new Row\Entry\FloatEntry('total', $item['total']),
            new Row\Entry\FloatEntry('total', $item['total_hbppn'])
    }*/
}
