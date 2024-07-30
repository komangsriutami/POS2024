<?php

namespace App\Http\Controllers;

use App\MasterApotek;
use Illuminate\Http\Request;
use App\Tips;
use App\News;
use App\MasterSpesialis;
use App\MasterDokter;
use App\MasterApoteker;
use App\Traits\DynamicConnectionTrait;

class HomePageController extends Controller
{
	use DynamicConnectionTrait;
	public function index()
	{
		$newss = News::on($this->getConnectionName())->where('is_deleted', 0)->orderBy('created_at', 'DESC')->limit(4)->get();
		foreach ($newss as $key => $value) {
			$text = strip_tags($value->content);
			$panjang = strlen($text);
			$content = substr($text, 0, 100) . ($panjang > 100 ? '...' : '');
			$newss[$key]->content = $content;
		}

		$tipss = Tips::on($this->getConnectionName())->where('is_deleted', 0)->orderBy('created_at', 'DESC')->limit(4)->get();
		foreach ($tipss as $key => $value) {
			$text = strip_tags($value->content);
			$panjang = strlen($text);
			$content = substr($text, 0, 100) . ($panjang > 100 ? '...' : '');
			$tipss[$key]->content = $content;
		}

		$spesialiss = MasterSpesialis::orderBy("spesialis")->get();

		$apoteks = MasterApotek::all();

		$dokters = MasterDokter::on($this->getConnectionName())->select([
			'tb_m_dokter.*',
			'tb_m_spesialis.spesialis'
		])
			->join('tb_m_spesialis', 'tb_m_spesialis.id', '=', 'tb_m_dokter.spesialis')->get();

		$apotekers = MasterApoteker::get();

		//  return view('frontend.v2.homepage')->with(compact('newss', 'tipss', 'spesialiss', 'dokters', 'apotekers', 'apoteks'));

		return view('frontend.v3.index2')->with(compact('newss', 'tipss', 'spesialiss', 'dokters'));
	}

	public function tips(Request $request)
	{
		$listTipss = Tips::on($this->getConnectionName())->where('is_deleted', 0)->orderBy('created_at', 'DESC')->limit(5)->get();

		$query_tips = Tips::on($this->getConnectionName())->select('*')->orderBy('created_at', 'DESC')->where('is_deleted', 0);

		if ($request->title) {
			$query_title = str_replace('+', ' ', $request->title);
			$query_tips->where('tb_tips.title', 'LIKE', "%$query_title%");
		}

		$tipss = $query_tips->paginate(5);
		foreach ($tipss as $key => $value) {
			$text = strip_tags($value->content);
			$panjang = strlen($text);
			$content = substr($text, 0, 500) . ($panjang > 500 ? '...' : '');
			$tipss[$key]->content = $content;
		}

		return view('frontend.v2.tips.index')->with(compact('tipss', 'listTipss'));
	}

	public function tipsDetails(Request $request, $slug)
	{

		$query_tips = Tips::on($this->getConnectionName())->where('slug', $slug);

		if ($request->title) {
			$query_title = str_replace('+', ' ', $request->title);
			$query_tips->where('tb_tips.title', 'LIKE', "%$query_title%");
		}

		$listTipss = Tips::on($this->getConnectionName())->where('is_deleted', 0)->orderBy('created_at', 'DESC')->limit(5)->get();

		$tipss = $query_tips->get();
		return view('frontend.v2.tips._detail')->with(compact('tipss', 'listTipss'));
	}

	public function news(Request $request)
	{
		$listNewss = News::on($this->getConnectionName())->where('is_deleted', 0)->orderBy('created_at', 'DESC')->limit(5)->get();

		$query_news = News::on($this->getConnectionName())->select('*')->orderBy('created_at', 'DESC')->where('is_deleted', 0);

		if ($request->title) {
			$query_title = str_replace('+', ' ', $request->title);
			$query_news->where('tb_news.title', 'LIKE', "%$query_title%");
		}

		$newss = $query_news->paginate(5);
		foreach ($newss as $key => $value) {
			$text = strip_tags($value->content);
			$panjang = strlen($text);
			$content = substr($text, 0, 500) . ($panjang > 500 ? '...' : '');
			$newss[$key]->content = $content;
		}

		return view('frontend.v2.news.index')->with(compact('newss', 'listNewss'));
	}

	public function newsDetails(Request $request, $slug)
	{

		$query_news = News::on($this->getConnectionName())->where('slug', $slug);

		if ($request->title) {
			$query_title = str_replace('+', ' ', $request->title);
			$query_news->where('tb_news.title', 'LIKE', "%$query_title%");
		}

		$listNewss = News::on($this->getConnectionName())->where('is_deleted', 0)->orderBy('created_at', 'DESC')->limit(5)->get();

		$newss = $query_news->get();

		return view('frontend.v2.news._detail')->with(compact('newss', 'listNewss'));
	}

	public function contact()
	{
		return view('frontend.v2.kontak');
	}

	public function showGallery()
	{
		return view('frontend.v3.galeri');
	}
	public function detailFitur(Request $request)
	{
		return view('frontend.v3.detail-fitur');
	}

	public function konsultasiDokter(Request $request)
	{
		$spesialiss = MasterSpesialis::orderBy("spesialis")->get();

		$query_dokter = MasterDokter::on($this->getConnectionName())->select([
			'tb_m_dokter.*',
			'tb_m_spesialis.spesialis'
		])
			->join('tb_m_spesialis', 'tb_m_spesialis.id', '=', 'tb_m_dokter.spesialis');
		if ($request->nama) {
			$query_nama = str_replace('+', ' ', $request->nama);
			$query_dokter->where('tb_m_dokter.nama', 'LIKE', "%$query_nama%");
		}
		if ($request->spesialis && $request->spesialis != '-1') {
			$query_dokter->where('tb_m_dokter.spesialis', $request->spesialis);
		}
		if ($request->lokasi) {
			$query_lokasi = str_replace('+', ' ', $request->lokasi);
			$query_dokter->where('tb_m_dokter.alamat', 'LIKE', "%$query_lokasi%");
		}
		$dokters = $query_dokter->paginate(12);

		return view('frontend.v2.konsultasi_dokter')->with(compact('spesialiss', 'dokters'));
	}

	public function konsultasiApoteker(Request $request)
	{

		$query_apoteker = MasterApoteker::on($this->getConnectionName())->select('*');
		if ($request->nama) {
			$query_nama = str_replace('+', ' ', $request->nama);
			$query_apoteker->where('tb_m_apoteker.nama', 'LIKE', "%$query_nama%");
		}
		$apotekers = $query_apoteker->get();

		return view('frontend.v2.konsultasi_apoteker')->with(compact('apotekers'));
	}

	public function konsultasiApotek(Request $request)
	{

		$query_apotek = MasterApotek::on($this->getConnectionName())->select('*');
		if ($request->nama) {
			$query_nama = str_replace('+', ' ', $request->nama);
			$query_apotek->where('tb_m_apoteker.nama', 'LIKE', "%$query_nama%");
		}
		$apoteks = $query_apotek->get();

		return view('frontend.v2.konsultasi_apotek')->with(compact('apoteks'));
	}

	public function dokter_profile($id)
	{
		$spesialiss = MasterSpesialis::orderBy("spesialis")->get();

		$dokters = MasterDokter::on($this->getConnectionName())->select([
			'tb_m_dokter.*',
			'tb_m_spesialis.spesialis',
		])
			->join('tb_m_spesialis', 'tb_m_spesialis.id', '=', 'tb_m_dokter.spesialis')
			->where('tb_m_dokter.id', $id)
			->first();


		return view('frontend.dokter_profile')->with(compact('spesialiss', 'dokters'));
	}


	public function apoteker_profile($id)
	{

		$apotekers =  MasterApoteker::on($this->getConnectionName())->find($id);


		return view('frontend.apoteker_profile')->with(compact('apotekers'));
	}

	public function apotek_detail($id)
	{

		$apoteks =  MasterApotek::on($this->getConnectionName())->find($id);


		return view('frontend.apotek_detail')->with(compact('apoteks'));
	}
}
