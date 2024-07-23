<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\PenyesuaianStok;
use App\MasterObat;
use App\MasterApotek;
use App\HistoriStok;
use Auth;
use App;
use Datatables;
use DB;
use App\Traits\DynamicConnectionTrait;

class PenyesuaianStokController extends Controller
{
    use DynamicConnectionTrait;
	public function index() {

	}

    public function create($id) {
    	$penyesuaian_stok = new PenyesuaianStok;
        $penyesuaian_stok->setDynamicConnection();
    	$apotek = MasterApotek::on($this->getConnectionName())->find(session('id_apotek_active'));
        $inisial = strtolower($apotek->nama_singkat);
        $stok_harga = DB::connection($this->getConnectionName())->table('tb_m_stok_harga_'.$inisial.'')->where('id_obat', $id)->first();
        $obat = MasterObat::on($this->getConnectionName())->find($id);

        return view('penyesuaian_stok.create')->with(compact('penyesuaian_stok', 'obat', 'stok_harga'));
    }

    public function store(Request $request) {
        DB::connection($this->getConnectionName())->beginTransaction();  
        try{
        	$penyesuaian_stok = new PenyesuaianStok;
            $penyesuaian_stok->setDynamicConnection();
            $penyesuaian_stok->fill($request->except('_token'));

            $apotek = MasterApotek::on($this->getConnectionName())->find(session('id_apotek_active'));
            $inisial = strtolower($apotek->nama_singkat);
            $stok_harga = DB::connection($this->getConnectionName())->table('tb_m_stok_harga_'.$inisial.'')->where('id_obat', $request->id_obat)->first();
            $obat = MasterObat::on($this->getConnectionName())->find($request->id_obat);

            $id_jenis_transaksi = 0;
            /*if($penyesuaian_stok->stok_awal == $penyesuaian_stok->stok_akhir) {
            	session()->flash('error', 'Stok awal dan stok akhir jumlahnya sama, tidak dapat disesuikan!');
                return redirect('data_obat/penyesuaian_stok/'.$obat->id);
            } else {*/
    	        if($penyesuaian_stok->stok_awal > $penyesuaian_stok->stok_akhir) {
    	        	$penyesuaian_stok->id_jenis_penyesuaian = 2;
    	        	$id_jenis_transaksi = 10;
    	        } else {
    	        	$penyesuaian_stok->id_jenis_penyesuaian = 1;
    	        	$id_jenis_transaksi = 9;
    	        }
           // }

            $penyesuaian_stok->id_apotek_nota = session('id_apotek_active');
            $penyesuaian_stok->created_at = date('Y-m-d H:i:s');
            $penyesuaian_stok->created_by = Auth::user()->id;

            $validator = $penyesuaian_stok->validate();
            if($validator->fails()){
                DB::connection($this->getConnectionName())->rollback();
            	session()->flash('error', 'Data yang diinputkan tidak sesuai!');
                return redirect('penyesuaian_stok/create/'.$obat->id);
            }else{
                $penyesuaian_stok->save();

                # kosongkan juga jika sudah ada stok opnam sebelumnya
                $array_id_histori_stok_awal =DB::connection($this->getConnectionName())->table('tb_histori_stok_'.$inisial)->where('id_obat', $obat->id)
                            ->whereIn('id_jenis_transaksi', [2,3,11,9])
                            ->where('sisa_stok', '>', 0)
                            ->orderBy('id', 'ASC')
                            ->get();

                if(count($array_id_histori_stok_awal) < 1) {
                    $array_id_histori_stok_awal =DB::connection($this->getConnectionName())->table('tb_histori_stok_'.$inisial)->where('id_obat', $obat->id)
                            ->whereIn('id_jenis_transaksi', [2,3,11,9])
                            ->orderBy('id', 'ASC')
                            ->limit(1)
                            ->get();
                }
               // dd($array_id_histori_stok_awal);

                foreach ($array_id_histori_stok_awal as $y => $hist) {
                    $cekHistori = HistoriStok::on($this->getConnectionName())->find($hist->id);
                    # kosongkan semua stok
                    $keterangan = $cekHistori->keterangan.', Penyesuaian Stok ID.'.$penyesuaian_stok->id.' sejumlah '.$hist->jumlah;
                    $cekHistori->sisa_stok = 0;
                    $cekHistori->keterangan = $keterangan;
                    if($cekHistori->save()) {
                    } else {
                        DB::connection($this->getConnectionName())->rollback();
                        session()->flash('error', 'Data yang diinputkan tidak sesuai!');
                        return redirect('data_obat/penyesuaian_stok/'.$obat->id);
                    }
                } 

    	        $stok_now = $penyesuaian_stok->stok_akhir;
    	        $jumlah = $penyesuaian_stok->stok_akhir-$penyesuaian_stok->stok_awal;

    	        # update ke table stok harga
    	        DB::connection($this->getConnectionName())->table('tb_m_stok_harga_'.$inisial)->where('id_obat', $obat->id)->update(['stok_awal'=> $stok_harga->stok_akhir, 'stok_akhir'=> $stok_now, 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => Auth::user()->id]);

    	        # create histori
    	        DB::connection($this->getConnectionName())->table('tb_histori_stok_'.$inisial)->insert([
    	            'id_obat' => $obat->id,
    	            'jumlah' => $jumlah,
    	            'stok_awal' => $stok_harga->stok_akhir,
    	            'stok_akhir' => $stok_now,
    	            'id_jenis_transaksi' => $id_jenis_transaksi,
    	            'id_transaksi' => $penyesuaian_stok->id,
    	            'batch' => null,
    	            'ed' => null,
                    'hb_ppn' => $penyesuaian_stok->hb_ppn,
                    'sisa_stok' => $stok_now,
                    'keterangan' => 'Penyesuaian Stok pada ID.'.$penyesuaian_stok.' sejumlah '.$stok_now,
    	            'created_at' => date('Y-m-d H:i:s'),
    	            'created_by' => Auth::user()->id
    	        ]);

                if($penyesuaian_stok->save()) {
                    DB::connection($this->getConnectionName())->commit();
                    session()->flash('success', 'Sukses menyimpan data!');
                return redirect('data_obat/penyesuaian_stok/'.$obat->id);
                } else {
                    DB::connection($this->getConnectionName())->rollback();
                    session()->flash('error', 'Data yang diinputkan tidak sesuai!');
                    return redirect('data_obat/penyesuaian_stok/'.$obat->id);
                }
            }
        }catch(\Exception $e){
            DB::connection($this->getConnectionName())->rollback();
            session()->flash('error', 'Cek kembali data yang anda inputkan');
            return redirect('penyesuaian_stok/create/'.$obat->id);
        }
    }  

    public function edit($id) {

    }

    public function update(Request $request, $id) {

    }

    public function destroy($id) {

    }
}
