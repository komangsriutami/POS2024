<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\InvestasiModal;
use App\MasterApotek;
use App\MasterInvestor;
use App;
use Datatables;
use DB;
use App\Traits\DynamicConnectionTrait;

class InvestasiModalController extends Controller
{
    use DynamicConnectionTrait;
    /*
        =======================================================================================
        For     : Menampilkan halaman index investasi modal
        Author  : Govi.
        Date    : 12/09/2021
        =======================================================================================
    */
    public function index()
    {
        $investor = MasterInvestor::on($this->getConnectionName())->where('is_deleted', 0)->get();

        return view('investasi_modal.index')
            ->with(compact(
                'investor',
            ));
    }

    /*
        =======================================================================================
        For     : Menampilkan datatable investasi modal
        Author  : Govi.
        Date    : 12/09/2021
        =======================================================================================
    */
    public function list_investasi_modal(Request $request)
    {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::connection($this->getConnection())->statement(DB::raw('set @rownum = 0'));
        $data = InvestasiModal::on($this->getConnectionName())->select([
                DB::raw('@rownum  := @rownum  + 1 AS no'),
                'tb_investasi_modal.*',
                'tb_m_apotek.nama_singkat AS nama_singkat_apotek',
                'tb_m_investor.nama AS nama_investor',
            ])
            ->join('tb_m_apotek', 'tb_m_apotek.id', 'tb_investasi_modal.id_apotek')
            ->join('tb_m_investor', 'tb_m_investor.id', 'tb_investasi_modal.id_investor')
            ->where(function($query) use($request){
                $query->where('tb_investasi_modal.is_deleted','=','0');
                if ($request->id_investor != null) {
                    $query->where('tb_investasi_modal.id_investor','=',$request->id_investor);
                }
            });
        
        $datatables = Datatables::of($data);
        return $datatables
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->orwhere('tb_m_investor.nama','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('tb_m_apotek.nama_singkat','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->editcolumn('tgl_transaksi', function($data){
            return '<span><b>'.$data->tgl_transaksi.'</b></span>'; 
        }) 
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .= '<span class="btn btn-primary" onClick="edit_data('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span>';
            $btn .= '<span class="btn btn-danger" onClick="delete_investor('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['action', 'tgl_transaksi'])
        ->addIndexColumn()
        ->make(true);  
    }

    /*
        =======================================================================================
        For     : Menampilkan halaman form investasi modal
        Author  : Govi.
        Date    : 12/09/2021
        =======================================================================================
    */
    public function create()
    {
    	$investasi_modal = new InvestasiModal;
        $investasi_modal->setDynamicConnection();
        $apotek = MasterApotek::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama_panjang', 'id');
        $apotek->prepend('-- Pilih Apotek --','');

        $investor = MasterInvestor::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama', 'id');
        $investor->prepend('-- Pilih Investor --','');

        return view('investasi_modal.create')
            ->with(compact(
                'investasi_modal',
                'apotek',
                'investor',
            ));
    }

    /*
        =======================================================================================
        For     : Menyimpan data investasi modal
        Author  : Govi.
        Date    : 12/09/2021
        =======================================================================================
    */
    public function store(Request $request)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        $investasi_modal = new InvestasiModal;
        $investasi_modal->setDynamicConnection();
        $investasi_modal->fill($request->except('_token'));

        $validator = $investasi_modal->validate();
        if($validator->fails()){
            $apotek = MasterApotek::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama_panjang', 'id');
            $apotek->prepend('-- Pilih Apotek --','');

            $investor = MasterInvestor::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama', 'id');
            $investor->prepend('-- Pilih Investor --','');

            return view('investasi_modal.create')
                ->with(compact(
                    'investasi_modal',
                    'apotek',
                    'investor',
                ))
                ->withErrors($validator);
        }else{
            $investasi_modal->save_plus();
            $this->updatePersentaseKepemilikan($request->id_apotek);
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('investasi_modal');
        }
    }

    /*
        =======================================================================================
        For     : 
        Author  : Govi.
        Date    : 12/09/2021
        =======================================================================================
    */
    public function show($id)
    {
        //
    }

    /*
        =======================================================================================
        For     : Menampilkan halaman edit investasi modal
        Author  : Govi.
        Date    : 12/09/2021
        =======================================================================================
    */
    public function edit($id)
    {
        $investasi_modal = InvestasiModal::on($this->getConnectionName())->find($id);
        $apotek = MasterApotek::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama_panjang', 'id');
        $apotek->prepend('-- Pilih Apotek --','');

        $investor = MasterInvestor::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama', 'id');
        $investor->prepend('-- Pilih Investor --','');

        return view('investasi_modal.edit')
            ->with(compact(
                'investasi_modal',
                'apotek',
                'investor',
            ));
    }

    /*
        =======================================================================================
        For     : Update data investasi modal
        Author  : Govi.
        Date    : 12/09/2021
        =======================================================================================
    */
    public function update(Request $request, $id)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        $investasi_modal = InvestasiModal::on($this->getConnectionName())->find($id);
        $investasi_modal->fill($request->except('_token'));

        $validator = $investasi_modal->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            $investasi_modal->save_edit();
            $this->updatePersentaseKepemilikan($request->id_apotek);
            echo json_encode(array('status' => 1));
        }
    }

    /*
        =======================================================================================
        For     : Delete data investasi modal
        Author  : Govi.
        Date    : 12/09/2021
        =======================================================================================
    */
    public function destroy($id)
    {
        $investasi_modal = InvestasiModal::on($this->getConnectionName())->find($id);
        $investasi_modal->save_delete();
        if($investasi_modal->delete()){
            $this->updatePersentaseKepemilikan($investasi_modal->id_apotek);
            echo 1;
        }else{
            echo 0;
        }
    }

    
    /*
        =======================================================================================
        For     : Update seluruh nilai persentase kepemilikan pada apotek terkait
        Author  : Govi.
        Date    : 13/09/2021
        =======================================================================================
    */
    private function updatePersentaseKepemilikan($id_apotek)
    {
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        $investasi_modal_at_apotek = InvestasiModal::on($this->getConnectionName())->where('id_apotek',$id_apotek)->get();
        $investasi_modal_sum_saham = InvestasiModal::on($this->getConnectionName())->where('id_apotek',$id_apotek)->sum('jumlah_modal');
        foreach ($investasi_modal_at_apotek as $value) {
            $investasi_modal = InvestasiModal::on($this->getConnectionName())->find($value->id);
            $investasi_modal->persentase_kepemilikan = $this->calculatePersentaseKepemilikan($value->jumlah_modal, $investasi_modal_sum_saham);
            $investasi_modal->update();
        }
    }

    /*
        =======================================================================================
        For     : Menghitung nilai persentase kepemilikan saham
        Author  : Govi.
        Date    : 13/09/2021
        =======================================================================================
    */
    private function calculatePersentaseKepemilikan($nilai_saham, $total_saham)
    {
        $persen = 0;
        $persen = ($nilai_saham / $total_saham) * 100;
        return $persen;
    }
}
