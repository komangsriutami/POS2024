<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\JadwalDokter;
use App\User;
use App\MasterDokter;
use App\SesiJadwalDokter;
use Carbon\Carbon;

use Auth;
use App;
use Datatables;
use DB;
use App\Traits\DynamicConnectionTrait;

class JadwalDokterController extends Controller
{
    use DynamicConnectionTrait;
    public function index()
    {
        $tahun = date('Y');
        $bulan = date('m');
        $dokters = MasterDokter::on($this->getConnectionName())->where('is_deleted', 0)->get();
        return view('jadwal_dokter.index')->with(compact('dokters', 'tahun', 'bulan'));
    }

    public function list_data(Request $request)
    {
        DB::connection($this->getConnection())->statement(DB::raw('set @rownum = 0'));
        $data = MasterDokter::select([DB::raw('@rownum  := @rownum  + 1 AS no'), 'tb_m_dokter.*'])
            ->where(function ($query) use ($request) {
                //$query->where('dokter.is_deleted','=','0');
            });

        $datatables = Datatables::of($data);
        return $datatables
            ->filter(function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->orwhere('id_group_apotek', 'LIKE', '%' . $request->get('search')['value'] . '%');
                    $query->orwhere('id_apotek', 'LIKE', '%' . $request->get('search')['value'] . '%');
                    $query->orwhere('nama', 'LIKE', '%' . $request->get('search')['value'] . '%');
                    $query->orwhere('spesialis', 'LIKE', '%' . $request->get('search')['value'] . '%');
                    $query->orwhere('sib', 'LIKE', '%' . $request->get('search')['value'] . '%');
                    $query->orwhere('alamat', 'LIKE', '%' . $request->get('search')['value'] . '%');
                    $query->orwhere('telepon', 'LIKE', '%' . $request->get('search')['value'] . '%');
                    $query->orwhere('fee', 'LIKE', '%' . $request->get('search')['value'] . '%');
                    $query->orwhere('img', 'LIKE', '%' . $request->get('search')['value'] . '%');
                });
            })
            ->addColumn('img', function ($data) {
                if (($data->img == "#") || (empty($data->img))) {
                    return '-';
                } else {
                    // return '<div class="col-md-4"><img src="' . url($data->img) . '" width="300px"></div>';
                    return '<div class="col-md-4"><img src="/userfiles/dokter/' . $data->img . '" width="150"></div>';
                }
            })
            ->addcolumn('action', function ($data) use($request) {
                $btn = '<div class="btn-group">';
                 $btn .= '<a href="'.url('/jadwal_dokter/detail_data/'.$data->id.'/'.$request->tahun.'/'.$request->bulan).'" title="Detail Data" class="btn btn-info btn-sm"><span data-toggle="tooltip" data-placement="top" title="Detail Data"><i class="fa fa-history"></i></span></a>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['img', 'action'])
            ->addIndexColumn()
            ->make(true);
    }

    public function detail_data($id, $tahun, $bulan) {
        $dokter = MasterDokter::on($this->getConnectionName())->find($id);
        return view('jadwal_dokter._detail')->with(compact('dokter', 'tahun', 'bulan'));
    }

    public function list_jadwal_dokter(Request $request)
    {
        DB::connection($this->getConnection())->statement(DB::raw('set @rownum = 0'));
        $data = JadwalDokter::select([DB::raw('@rownum  := @rownum  + 1 AS no'),
        		'tb_jadwal_dokter.*'])
        ->where(function($query) use($request){
            $query->where('tb_jadwal_dokter.is_deleted','=','0');
            $query->where('tb_jadwal_dokter.id_dokter','LIKE','%'.$request->id_dokter.'%');
        });

        $datatables = Datatables::of($data);
        if ($keyword = $request->get('search')['value']) {
            $datatables->filterColumn('no', 'whereRaw', '@rownum  + 1 like ?', ["%{$keyword}%"]);
            $datatables->filterColumn('id', 'whereRaw', 'id like ?', ["%{$keyword}%"]);
        }
        return $datatables
        ->addColumn('id_dokter', function ($data) {
           return $data->dokter->nama;
        })
        ->addColumn('id_sesi', function ($data) {
           return $data->sesi->sesi;
        })
        ->addColumn('jumlah_pasien', function ($data) {
           return 0;
        })
        ->addcolumn('action',
            '<span class="label label-primary" onClick="edit_data({!! $id !!})" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span>
            <span class="label label-danger label-delete" onClick="delete_jadwal_kerja({!! $id !!})" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>
            ')
        ->make(true);
    }

    public function create()
    {
        $jadwal_dokter = new JadwalDokter;
        $jadwal_dokter->setDynamicConnection();

        $dokters = MasterDokter::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama', 'id');
        $dokters->prepend('-- Pilih Dokter --', '');
        $sesi_dokter = SesiJadwalDokter::on($this->getConnectionName())->where('is_deleted', 0)->pluck('sesi', 'id');
        $sesi_dokter->prepend('-- Pilih Sesi --', '');

        return view('jadwal_dokter.create')->with(compact('jadwal_dokter', 'dokters', 'sesi_dokter'));
    }

    function getDatesFromRange($Date1, $Date2) {
        $array = array();
        $Variable1 = strtotime($Date1);
        $Variable2 = strtotime($Date2);
        for ($currentDate = $Variable1; $currentDate <= $Variable2;
                                        $currentDate += (86400)) {

        $Store = date('Y-m-d', $currentDate);
        $array[] = $Store;
        }
        return $array;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        $split                      = explode("-", $request->tgl);
        $start       = date('Y-m-d',strtotime($split[0]));
        $end      = date('Y-m-d',strtotime($split[1]));
        $range_date = $this->getDatesFromRange($start, $end);

        $dataCheck = JadwalDokter::whereDate('tgl', '>=' ,$start)
        ->whereDate('tgl', '<=' ,$end)
        ->whereTime('start', '>=',$request->start)
        ->whereTime('end', '<=',$request->end)
        ->get();
        
        $sukses = 0;
        $jadwal_dokter = new JadwalDokter;
        $jadwal_dokter->setDynamicConnection();
        foreach ($range_date as $key => $obj) {
            $jadwal_dokter = new JadwalDokter;
            $jadwal_dokter->setDynamicConnection();
            $jadwal_dokter->fill($request->except('_token'));
            $jadwal_dokter->tgl = $obj;
            $validator = $jadwal_dokter->validate();
            if($validator->fails()){
                break;
            }else if($request->book_max<=0){
                $validator = "Jumlah book minimal 1";
                $sukses = 0;
                break;
            }else if(strtotime($request->start)>=strtotime($request->end)){
                $validator = "Waktu awal lebih besar atau sama dengan waktu akhir yang anda inputkan";
                $sukses = 0;
                break;
            } else if(count($dataCheck)>0){
                $validator = "Tanggal dan Waktu yang di inputkan sudah ada di jadwal";
                $sukses = 0;
                break;
            }else {
                $jadwal_dokter->save_plus();
                $sukses = $sukses+1;
            }
        }

        $dokters = MasterDokter::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama', 'id');
        $sesi_dokter = SesiJadwalDokter::on($this->getConnectionName())->where('is_deleted', 0)->pluck('sesi', 'id');
        if($sukses == 0){
            $jadwal_dokter->tgl = $request->tgl;
            
            $dokters->prepend('-- Pilih Dokter --', '');
        
            $sesi_dokter->prepend('-- Pilih Sesi --', '');
            return view('jadwal_dokter.create')->with(compact('jadwal_dokter', 'dokters', 'sesi_dokter'))->withErrors($validator);
        }else{
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('jadwal_dokter/create')->with(compact('jadwal_dokter', 'dokters', 'sesi_dokter'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $jadwal_dokter = JadwalDokter::on($this->getConnectionName())->find($id);

        $dokters = MasterDokter::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama', 'id');
        $dokters->prepend('-- Pilih Dokter --', '');
        $sesi_dokter = SesiJadwalDokter::on($this->getConnectionName())->where('is_deleted', 0)->pluck('sesi', 'id');
        $sesi_dokter->prepend('-- Pilih Sesi --', '');

        return view('jadwal_dokter.edit')->with(compact('jadwal_dokter', 'dokters', 'sesi_dokter', 'dates'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $jadwal_dokter = JadwalDokter::on($this->getConnectionName())->find($id);
        $jadwal_dokter->fill($request->except('_token'));

        $validator = $jadwal_dokter->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            $jadwal_dokter->save_edit();
            echo json_encode(array('status' => 1));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        // $jadwal_dokter = JadwalDokter::on($this->getConnectionName())->find($id);
        // $jadwal_dokter->is_deleted = 1;
        // if($jadwal_dokter->save()){
        //     echo 1;
        // }else{
        //     echo 0;
        // }
        // session()->flash('success', 'Sukses menghapus data!');
        $jadwal_dokter = JadwalDokter::on($this->getConnectionName())->find($id);
        if($jadwal_dokter->delete()){
            echo 1;
        }else{
            echo 0;
        }
    }

    public function lihat_jadwal_kerja() {
        $dokters = MasterDokter::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama', 'id');
        $dokters->prepend('-- Pilih Dokter --', '');

        return view('jadwal_dokter._form_lihat_jadwal_kerja')->with(compact('dokters'));
    }

    public function load_list_jadwal_dokter(Request $request){
        $jadwal_dokters = JadwalDokter::select('tb_jadwal_dokter.*')
                            ->where(function($query) use($request){
                                    $query->where('tb_jadwal_dokter.is_deleted','=','0');
                                    $query->where('tb_jadwal_dokter.id_dokter','LIKE','%'.$request->id_dokter.'%');
                             })
                            ->get();


        $new_array = array();
        foreach($jadwal_dokters as $jadwal_dokter)
        {

            $dokter = $jadwal_dokter->dokter;
            $title_dokter='';
            if ($dokter->apoteks) {
                $title_dokter = "(".$dokter->apoteks->nama_singkat.') '.$dokter->nama;
            }else {
                $title_dokter = $dokter->nama;
            }
            $data = (object) array(
                            'id' => $jadwal_dokter->id,
                            'id_dokter' => $jadwal_dokter->id_dokter,
                            'start' => $jadwal_dokter->tgl." ".$jadwal_dokter->start,
                            'end' => $jadwal_dokter->tgl." ".$jadwal_dokter->end,
                            'sesi' => $jadwal_dokter->id_sesi,
                            'dokter' => $jadwal_dokter->dokter,
                            'color' => '#00bcd4',
                            // 'title' => "(".$dokter->apoteks->nama_singkat.') '.$dokter->nama,
                            'title' => $title_dokter,
                            'url' => url('detail_jadwal_dokter/' . $jadwal_dokter->id),
                        );
            $new_array[] = $data;
        }
        return $new_array;
    }

    // public function load_data_jadwal_dokter(Request $request) {
    //     $jadwal_dokter = JadwalDokter::on($this->getConnectionName())->find($request->id);
    //     $dokters = MasterDokter::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama', 'id');
    //     $dokters->prepend('-- Pilih Dokter --', '');
    //     $sesi_dokter = SesiJadwalDokter::on($this->getConnectionName())->where('is_deleted', 0)->pluck('sesi', 'id');
    //     $sesi_dokter->prepend('-- Pilih Sesi --', '');

    //     return view('jadwal_dokter.edit')->with(compact('jadwal_dokter', 'dokters', 'sesi_dokter'));
    // }

    public function load_data_jadwal_dokter(Request $request) {
        $date = Carbon::now();
        $date->setTimezone('Asia/Singapore');
        $dates = $date->toDateString();

        $jadwal_dokter = JadwalDokter::on($this->getConnectionName())->find($request->id);
        $dokters = MasterDokter::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama', 'id');
        $dokters->prepend('-- Pilih Dokter --', '');
        $sesi_dokter = SesiJadwalDokter::on($this->getConnectionName())->where('is_deleted', 0)->pluck('sesi', 'id');
        $sesi_dokter->prepend('-- Pilih Sesi --', '');

        $userAkses = true;
        if(session("user_roles.0.id")==7){
            $userAkses = ($jadwal_dokter->id_dokter == Auth::guard("dokter")->user()->id);
        }
        $userEdit = $userAkses && (strtotime($dates) < strtotime($jadwal_dokter->tgl));

        return view('jadwal_dokter.edit')->with(compact('jadwal_dokter', 'dokters', 'sesi_dokter','userEdit'));
    }
}
