<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
*/
Route::middleware('api')->group( function () {
    //Route::resource('products', 'API\ProductController');

    Route::get('download_apotek', ['as'=>'download_apotek', 'uses'=>'API\ServiceAppController@download_apotek']);
	Route::get('download_master_obat', ['as'=>'download_master_obat', 'uses'=>'API\ServiceAppController@download_master_obat']);
	Route::get('download_stok_obat', ['as'=>'download_stok_obat', 'uses'=>'API\ServiceAppController@download_stok_obat']);

	// route untuk go apotek
	Route::get('ef4c2ce3032d8f024c320308d9880a06', ['as'=>'ef4c2ce3032d8f024c320308d9880a06', 'uses'=>'API\ServiceAppController@ef4c2ce3032d8f024c320308d9880a06']);
	Route::get('f31d5936f25442ecf43a2e4a9aa911d1', ['as'=>'f31d5936f25442ecf43a2e4a9aa911d1', 'uses'=>'API\ServiceAppController@f31d5936f25442ecf43a2e4a9aa911d1']);
	Route::get('f36c008db00e367c7dae1c4a856e55ca', ['as'=>'f36c008db00e367c7dae1c4a856e55ca', 'uses'=>'API\ServiceAppController@f36c008db00e367c7dae1c4a856e55ca']);
	Route::get('ed70a85853284244f63de7fbd08ccea5', ['as'=>'ed70a85853284244f63de7fbd08ccea5', 'uses'=>'API\ServiceAppController@ed70a85853284244f63de7fbd08ccea5']);
	Route::get('f60ba84e9e162c05eaf305d15372e4f4', ['as'=>'f60ba84e9e162c05eaf305d15372e4f4', 'uses'=>'API\ServiceAppController@f60ba84e9e162c05eaf305d15372e4f4']);

	// template sinkron go apotek
	Route::get('template_lv', ['as'=>'template_lv', 'uses'=>'API\ServiceAppController@template_lv']);
	Route::get('template_bkl', ['as'=>'template_bkl', 'uses'=>'API\ServiceAppController@template_bkl']);
	Route::get('template_pjm', ['as'=>'template_pjm', 'uses'=>'API\ServiceAppController@template_pjm']);
	Route::get('template_pg', ['as'=>'template_pg', 'uses'=>'API\ServiceAppController@template_pg']);
	Route::get('template_tl', ['as'=>'template_tl', 'uses'=>'API\ServiceAppController@template_tl']);


	Route::get('get_data_apoteker/{id_ap}', ['as'=>'get_data_apoteker', 'uses'=>'API\ServiceAppController@get_data_apoteker']);

	Route::get('cek_absen/{id}/{id_apotek}', ['as'=>'cek_absen/{id}/{id_apotek}', 'uses'=>'API\ServiceAppController@cek_absen']);
	Route::get('send_absen/{id}/{id_apotek}/{pass}/{id_jenis_absen}', ['as'=>'send_absen/{id}/{id_apotek}/{pass}/{id_jenis_absen}', 'uses'=>'API\ServiceAppController@send_absen']);
	Route::get('list_data_rekap_absensi_per_bulan/{id_user}/{tahun}', ['as'=>'list_data_rekap_absensi_per_bulan/{id_user}/{tahun}', 'uses'=>'API\ServiceAppController@list_data_rekap_absensi_per_bulan']);
	Route::get('list_data_rekap_absensi_per_hari/{id_user}/{tahun}/{bulan}', ['as'=>'list_data_rekap_absensi_per_hari/{id_user}/{tahun}/{bulan}', 'uses'=>'API\ServiceAppController@list_data_rekap_absensi_per_hari']);


	// API data master
	Route::get('/getDataSales','API\ServiceAppController@GetDataSales');
	Route::get('/getDataPurchasing','API\ServiceAppController@GetDataPurchasing');
	Route::post('/getDataGroupApotek','API\ServiceAppController@GetDataGroupApotek');
	Route::post('/getDataApotek','API\ServiceAppController@GetDataApotek');
	Route::post('/getDataUser','API\ServiceAppController@GetDataUser');
	Route::post('/getDataSuplier','API\ServiceAppController@GetDataSuplier');
	Route::post('/getDataGolonganObat','API\ServiceAppController@GetDataGolonganObat');
	Route::post('/getDataPenandaanObat','API\ServiceAppController@GetDataPenandaanObat');
	Route::post('/getDataProdusen','API\ServiceAppController@GetDataProdusen');
	Route::post('/getDataSatuan','API\ServiceAppController@GetDataSatuan');
	Route::post('/getDataMember','API\ServiceAppController@GetDataMember');
	Route::post('/getDataApoteker','API\ServiceAppController@GetDataApoteker');
	Route::post('/getDataKlinik','API\ServiceAppController@GetDataKlinik');
	Route::post('/getDataDokter','API\ServiceAppController@GetDataDokter');
	Route::post('/login', 'API\ServiceAppController@apiLogin');
	

	
});

