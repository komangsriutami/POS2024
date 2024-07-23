<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Absensi;
use App\User;
use App\MasterApotek;
use App\SesiJadwalKerja;
use App\Exports\AbsensiExport;

use App;
use Datatables;
use DB;
use Auth;
use Hash;
use Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Traits\DynamicConnectionTrait;

class AbsensiController extends Controller
{
    use DynamicConnectionTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $id_role_active = session('id_role_active');
        $id_apotek_active = session('id_apotek_active');
        if($id_role_active == 1 || $id_role_active == 6) {
            $apoteks = MasterApotek::on($this->getConnectionName())->where('is_deleted', 0)->where('id_group_apotek', Auth::user()->id_group_apotek)->get();
        } else {
            $apoteks = MasterApotek::on($this->getConnectionName())->where('id', $id_apotek_active)->get();
        }
        
        $months = array();
        for ($i = 0; $i < 12; $i++) {
            $timestamp = mktime(0, 0, 0, date('n') - $i, 1);
            $months[date('n', $timestamp)] = date('F', $timestamp);
        }
        ksort($months);
        return view('absensi.index')->with(compact('apoteks', 'months', 'id_apotek_active', 'id_role_active'));
    }

    public function list_absensi(Request $request)
    {
    	$order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];
        $tanggal = date('Y-m-d');

        DB::connection($this->getConnection())->statement(DB::raw('set @rownum = 0'));
        $data = Absensi::select([
                    DB::raw('@rownum  := @rownum  + 1 AS no'),
                    'id_user', 
                    DB::raw('SUM(jumlah_jam_kerja) as total')
                ])
                ->where(function($query) use($request, $tanggal){
                    $query->where('is_deleted','=','0');
                    if($request->id_searching_by == 2) {
                        $query->where('tb_absensi.id_apotek','LIKE','%'.$request->id_apotek.'%');
                    }
                    $query->whereYear('tgl', date('Y'));
                    $query->whereMonth('tgl', $request->bulan);
                })
                ->groupBy('id_user');

        $datatables = Datatables::of($data);
        return $datatables  
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
              
            });
        })  
        ->editcolumn('id_user', function($data) use($request){
            if(empty($data->user)) {
                $string = 'tidak ditemukan-'.$data->id_user;
            } else {
                $string = $data->user->nama;
            }
            return $string;
        })
        ->editcolumn('total', function($data) use($request){
            return "<b>".number_format ($data->total,2)." jam</b>";
        })
        ->addcolumn('action', function($data) use($request){
            $btn = '<div class="btn-group">';
            $btn .= '<a href="'.url('/absensi/detail_data/'.$data->id_user.'/'.$request->bulan.'/'.$request->id_apotek.'/'.$request->id_searching_by).'" title="Detail Data" class="btn btn-info btn-sm"><span data-toggle="tooltip" data-placement="top" title="Detail Data"><i class="fa fa-history"></i></span></a>';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['action', 'total', 'id_user'])
        ->addIndexColumn()
        ->make(true);  
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $absensi = new Absensi;
        $absensi->setDynamicConnection();

        $users =  User::on($this->getConnectionName())->where('is_deleted', 0)->lists('nama', 'id');
        $apoteks = MasterApotek::on($this->getConnectionName())->where('is_deleted', 0)->lists('nama', 'id');
        $sesi_jadwal_kerja = SesiJadwalKerja::on($this->getConnectionName())->where('is_deleted', 0)->lists('nama', 'id');
        
        return view('absensi.create')->with(compact('absensi', 'users', 'apoteks', 'sesi_jadwal_kerja'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($request->id_status == 1) {
            $absensi = new Absensi;
            $absensi->setDynamicConnection();
            $absensi->fill($request->except('_token'));
            $absensi->id_apotek = session('id_apotek_active');
            $absensi->id_kasir_aktif = Auth::user()->id;
            $absensi->tgl = date('Y-m-d');
            $absensi->jam_datang = date('H:i:s');
            $absensi->jam_pulang = "00:00:00";
            $absensi->jumlah_jam_kerja = "0";
        } else if ($request->id_status == 2) {
            $absensi = Absensi::on($this->getConnectionName())->where('id_user', $request->id_user)->where('tgl', date('Y-m-d'))->first();
            $absensi->jam_datang = $absensi->jam_datang;
            $absensi->jam_pulang = date('H:i:s');
            $date1 = strtotime($absensi->tgl." ".$absensi->jam_datang);
            $date2 = strtotime($absensi->tgl." ".date('H:i:s'));
            $diff   = $date2 - $date1;
            $jam = $diff/(60 * 60);
            $absensi->jumlah_jam_kerja = $jam; 
        } else if ($request->id_status == 3) {
            $absensi = Absensi::on($this->getConnectionName())->where('id_user', $request->id_user)->where('tgl', date('Y-m-d'))->first();
            $absensi->jam_datang = $absensi->jam_datang;
            $absensi->jam_pulang = date('H:i:s');
            $absensi->jam_datang_split = date('H:i:s');
            $absensi->jam_pulang_split = "00:00:00";
            $absensi->jumlah_jam_kerja = $absensi->jumlah_jam_kerja; 
        } else if ($request->id_status == 4) {
            $absensi = Absensi::on($this->getConnectionName())->where('id_user', $request->id_user)->where('tgl', date('Y-m-d'))->first();
            $absensi->jam_datang = $absensi->jam_datang;
            $absensi->jam_pulang = $absensi->jam_pulang;
            
            # sesi 2
            $date1_split = strtotime($absensi->tgl." ".$absensi->jam_datang_split);
            $date2_split = strtotime($absensi->tgl." ".date('H:i:s'));
            $diff_split   = $date2_split - $date1_split;
            $jam_split = $diff_split/(60 * 60);
            $absensi->jumlah_jam_kerja = $absensi->jumlah_jam_kerja + $jam_split; 
        }
        
        $new_password = bcrypt($request->password);
        $user = User::on($this->getConnectionName())->find($request->id_user);

        $cek_1 = Hash::check($request->password, $new_password);
        $cek_2 = Hash::check($request->password, $user->password);

        if($cek_1 && $cek_2) {
            $validator = $absensi->validate();
            if($validator->fails()){
                return view('absensi.create')->with(compact('absensi'))->withErrors($validator);
            }else{
                $absensi->save();
                session()->flash('success', 'Sukses menyimpan data!');
                return redirect('absensi/add_absensi')->with(compact('absensi'));
            }
        } else {
            session()->flash('error', 'Password yang anda masukan salah!');
             return redirect('absensi/add_absensi')->with(compact('absensi'));
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
        $absensi = Absensi::on($this->getConnectionName())->find($id);
        return view('absensi.edit')->with(compact('absensi'));
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
        $absensi = Absensi::on($this->getConnectionName())->find($id);
        $absensi->fill($request->except('_token'));

        $validator = $absensi->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            $absensi->save_edit();
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
        $absensi = Absensi::on($this->getConnectionName())->find($id);
        $absensi->is_deleted = 1;
        if($absensi->save()){
            echo 1;
        }else{
            echo 0;
        }
        // session()->flash('success', 'Sukses menghapus data!');
    }

    public function add_absensi() {
        if(Auth::user()->is_admin == 1) {
            $users = User::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama', 'id');
            $users->prepend('--- Pilih User ---','');

            return view('absensi._form_add_absensi')->with((compact('users')));
        } else {
            return view('page_not_authorized');
        }
    }

    public function cari_user(Request $request) {
        $user = User::on($this->getConnectionName())->where('id', $request->txtcari)->first();
        $now = date('Y-m-d');
        $cek_absensi = Absensi::on($this->getConnectionName())->where('id_user', $user->id)->where('tgl', $now)->first();

        $jum = 0;
        if(!empty($cek_absensi)) {
            //$jum =count($cek_absensi); 

            if ($cek_absensi->jam_datang == "00:00:00" AND $cek_absensi->jam_datang == null AND $cek_absensi->jam_datang == "") {
                $jum = $jum + 0;
            } else {
                $jum = $jum + 1;
            }

            if ($cek_absensi->jam_pulang == "00:00:00" OR $cek_absensi->jam_pulang == null OR $cek_absensi->jam_pulang == "") {
                $jum = $jum + 0;
            } else {
                $jum = $jum + 1;
            } 

            if ($cek_absensi->jam_datang_split == "00:00:00" OR $cek_absensi->jam_datang_split == null OR $cek_absensi->jam_datang_split == "") {
                $jum = $jum + 0;
            }  else {
                $jum = $jum + 1;
            }

            if ($cek_absensi->jam_pulang_split == "00:00:00" OR $cek_absensi->jam_pulang_split == null OR $cek_absensi->jam_pulang_split == "") {
                $jum = $jum + 0;
            } else {
                $jum = $jum + 1;
            }
        } 

        if(empty($user)) {
            echo "Data user tidak ditemukan !";
        } else {
            echo json_encode(array('jum' => $jum, 'user' => $user, 'cek_absensi' => $cek_absensi));
        }
    }

    public function export_absensi(Request $request)
    {
        $id_searching_by = $request->id_searching_by;
        $tahun = session('id_tahun_active');
        $bulan = $request->bulan;

        $str_name = $tahun.'_'.$bulan;
        if($id_searching_by == 2) {
            $id_apotek = $request->id_apotek;
            $apotek = MasterApotek::on($this->getConnectionName())->find($id_apotek);
            $str_name .= '_'.$apotek->nama_singkat;
            
        } else {
            $id_apotek = 1;
        }
        return (new AbsensiExport($tahun, $bulan, $id_searching_by, $id_apotek))->download('absensi_'.$str_name.'.xlsx');
    }

    public function export_absensi_back(Request $request)
    {
        $myFile = Excel::create('Data Absensi', function($excel) use ($request) {
            $excel->sheet('Sheet 1', function($sheet) use ($request) {
                $alpha = array(
                            'A','B','C','D','E','F','G','H','I','J','K', 'L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
                            'AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK', 'AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ',
                            'BA','BB','BC','BD','BE','BF','BG','BH','BI','BJ','BK', 'BL','BM','BN','BO','BP','BQ','BR','BS','BT','BU','BV','BW','BX','BY','BZ',
                            'CA','CB','CC','CD','CE','CF','CG','CH','CI','CJ','CK', 'CL','CM','CN','CO','CP','CQ','CR','CS','CT','CU','CV','CW','CX','CY','CZ',
                            'DA','DB','DC','DD','DE','DF','DG','DH','DI','DJ','DK', 'DL','DM','DN','DO','DP','DQ','DR','DS','DT','DU','DV','DW','DX','DY','DZ',
                            'EA','EB','EC','ED','EE','EF','EG','EH','EI','EJ','EK', 'EL','EM','EN','EO','EP','EQ','ER','ES','ET','EU','EV','EW','EX','EY','EZ',
                            'FA','FB','FC','FD','FE','FF','FG','FH','FI','FJ','FK', 'FL','FM','FN','FO','FP','FQ','FR','FS','FT','FU','FV','FW','FX','FY','FZ',
                            'GA','GB','GC','GD','GE','GF','GG','GH','GI','GJ','GK', 'GL','GM','GN','GO','GP','GQ','GR','GS','GT','GU','GV','GW','GX','GY','GZ',
                            'HA','HB','HC','HD','HE','HF','HG','HH','HI','HJ','HK', 'HL','HM','HN','HO','HP','HQ','HR','HS','HT','HU','HV','HW','HX','HY','HZ',
                            'IA','IB','IC','ID','IE','IF','IG','IH','II','IJ','IK', 'IL','IM','IN','IO','IP','IQ','IR','IS','IT','IU','IV','IW','IX','IY','IZ',
                            'JA','JB','JC','JD','JE','JF','JG','JH','JI','JJ','JK', 'JL','JM','JN','JO','JP','JQ','JR','JS','JT','JU','JV','JW','JX','JY','JZ',
                            'KA','KB','KC','KD','KE','KF','KG','KH','KI','KJ','KK', 'KL','KM','KN','KO','KP','KQ','KR','KS','KT','KU','KV','KW','KX','KY','KZ',
                            'LA','LB','LC','LD','LE','LF','LG','LH','LI','LJ','LK', 'LL','LM','LN','LO','LP','LQ','LR','LS','LT','LU','LV','LW','LX','LY','LZ',
                            'MA','MB','MC','MD','ME','MF','MG','MH','MI','MJ','MK', 'ML','MM','MN','MO','MP','MQ','MR','MS','MT','MU','MV','MW','MX','MY','MZ',
                            'NA','NB','NC','ND','NE','NF','NG','NH','NI','NJ','NK', 'NL','NM','NN','NO','NP','NQ','NR','NS','NT','NU','NV','NW','NX','NY','NZ'
                            );

                $sheet->setWidth('A', 20);

                $a = 0;
                foreach ($alpha as $key) {
                    $a++;
                    if($a != 1) {
                        $sheet->setWidth($key, 15);
                    }
                }

                $sheet->mergeCells('A1:I1');
                $sheet->row(1, function ($row) {
                    $row->setFontFamily('Calibri');
                    $row->setFontSize(14);
                    $row->setAlignment('center');
                    $row->setFontWeight('bold');
                });

                $sheet->row(1, array('Data Absensi Apotek'));
                $sheet->mergeCells('A2:B2');
                $sheet->row(2, array(''));


                $apotek = Apotek::on($this->getConnectionName())->find($request->id_apotek);

                $sheet->row(3, function ($row) {
                    $row->setFontFamily('Calibri');
                    $row->setFontSize(11);
                    $row->setFontWeight('bold');

                });
                $sheet->row(4, function ($row) {
                    $row->setFontFamily('Calibri');
                    $row->setFontSize(11);
                    $row->setFontWeight('bold');
                });

                $sheet->row(6, function ($row) {
                    $row->setFontFamily('Calibri');
                    $row->setFontSize(11);
                    $row->setFontWeight('bold');
                    $row->setAlignment('center');
                });
                $sheet->row(7, function ($row) {
                    $row->setFontFamily('Calibri');
                    $row->setFontSize(11);
                    $row->setFontWeight('bold');
                    $row->setAlignment('center');
                });

                $sheet->row(3, array('Nama Apotek', $apotek->nama));
                $sheet->row(4, array('Tanggal', $request->tgl));
                $sheet->row(5, array(''));

                $split                      = explode("-", $request->tgl);
                $tgl_awal       = date('Y-m-d',strtotime($split[0]));
                $tgl_akhir      = date('Y-m-d',strtotime($split[1]));
                $cek_user_absen = Absensi::select(['id_user'])->where('is_deleted', 0)->where('id_apotek', $request->id_apotek)->whereBetween('tgl', [$tgl_awal, $tgl_akhir])->get();
                $data_users = User::on($this->getConnectionName())->where('is_deleted', 0)->where('is_absensi', 1)->whereIn('id', $cek_user_absen)->get();



                $array_sheet = array('');
                $sheet->mergeCells('A6:A7');
                $sheet->setCellValue('A6', 'Tanggal');

                $i = 0;
                foreach ($data_users as $key => $val) {
                    $i_start = $i+1;
                    $i_end  = $i+7;
                    //$sheet->setWidth($alpha[$i], 10);
                    $sheet->mergeCells($alpha[$i_start].'6:'.$alpha[$i_end].'6');
                    $sheet->setCellValue($alpha[$i_start].'6', $val->nama);
                    array_push($array_sheet, 'Jam Masuk I', 'Jam Pulang I', 'Jam Masuk II', 'Jam Pulang II', 'Total I', 'Total II', 'Total Final');
                    $i = $i_end;
                }
                    
                $sheet->row(7, $array_sheet);

                $sheet->setStyle(array(
                    'font' => array(
                        'name'      =>  'Calibri',
                        'size'      =>  10
                    )
                ));

                $no = 0;
                $cek_tgl_absen = Absensi::select(['tgl'])
                                ->where(function($query) use($request, $tanggal){
                                    $query->where('is_deleted','=','0');
                                    $query->where('id_apotek','LIKE','%'.$request->id_apotek.'%');
                                    if($request->tgl_awal != "") {
                                        $tgl_awal       = date('Y-m-d H:i:s',strtotime($request->tgl_awal));
                                        $query->whereDate('tgl','>=', $tgl_awal);
                                    }

                                    if($request->tgl_akhir != "") {
                                        $tgl_akhir      = date('Y-m-d H:i:s',strtotime($request->tgl_akhir));
                                        $query->whereDate('tgl','<=', $tgl_akhir);
                                    }
                                })
                                ->groupBy('tgl')
                                ->orderBy('tgl', 'ASC')
                                ->get();

                foreach ($cek_tgl_absen as $obj) {
                    $i = $obj->tgl;
                    $date = date ("Y-m-d", strtotime($i));
                    $data = array($date);

                    foreach ($data_users as $key => $val) {
                      //  echo $val->username;
                        $cek_absen = Absensi::on($this->getConnectionName())->where('is_deleted', 0)->where('tgl', $date)->where('id_apotek', $request->id_apotek)->where('id_user', $val->id)->first(); //
                        if(!empty($cek_absen)) {
                            if($cek_absen->jam_datang != null && $cek_absen->jam_pulang == null) {
                                $jam1 = 0;
                            } else {
                                $date1 = strtotime($cek_absen->tgl." ".$cek_absen->jam_datang);
                                $date2 = strtotime($cek_absen->tgl." ".$cek_absen->jam_pulang);
                                $diff1   = $date2 - $date1;
                                $jam1 = $diff1/(60 * 60);
                            }

                            if($cek_absen->jam_datang_split != null && $cek_absen->jam_pulang_split == null) {
                                $jam2 = 0;
                            } else {
                                $date3 = strtotime($cek_absen->tgl." ".$cek_absen->jam_datang_split);
                                $date4 = strtotime($cek_absen->tgl." ".$cek_absen->jam_pulang_split);
                                $diff2   = $date4 - $date3;
                                $jam2 = $diff2/(60 * 60);
                            }
                            

                            array_push($data,
                                $cek_absen->jam_datang,
                                $cek_absen->jam_pulang,
                                $cek_absen->jam_datang_split,
                                $cek_absen->jam_pulang_split,
                                number_format($jam1,2),
                                number_format($jam2,2),
                                number_format($cek_absen->jumlah_jam_kerja,2)
                                );
                        } else {
                            array_push($data, "x", "x", "x", "x", "x", "x", "x");
                        }
                    }

                    $sheet->appendRow($data);   
                }
            });
        });
        $myFile = $myFile->string('xlsx'); //change xlsx for the format you want, default is xls
        $response =  array(
           'name' => "Data Absensi Apotek-".$request->id_apotek.'-'.$request->tgl, //no extention needed
           'file' => "data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64,".base64_encode($myFile) //mime type of used format
        );
        return response()->json($response);
    }

    public function detail_data($id_user, $bulan, $id_apotek, $id_searching_by) {
        $user = User::on($this->getConnectionName())->find($id_user);
        $tahun = session('id_tahun_active');
        $jumlah_jam = Absensi::select([DB::raw('SUM(jumlah_jam_kerja) as jumlah_jam')])
                                ->where(function($query) use($id_user, $bulan, $tahun, $id_apotek, $id_searching_by){
                                    $query->where('is_deleted', 0);
                                    $query->where('id_user', $id_user);
                                    $query->where(DB::raw('YEAR(tgl)'), $tahun);
                                    $query->where(DB::raw('MONTH(tgl)'), $bulan);
                                    if($id_searching_by == 2) {
                                        $query->where('id_apotek', $id_apotek);
                                    }
                                })
                                ->first();

        /*$jumlah_hari_libur = DB::connection($this->getConnectionName())->table('j_setting_tgl_libur')->where(DB::raw('YEAR(tgl)'), $tahun)->where(DB::raw('MONTH(tgl)'), $bulan)->count();
        $jumlah_hari_all = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
        $jumlah_hari_all = $jumlah_hari_all-$jumlah_hari_libur;
        $jumlah_jam_kerja_all = $jumlah_hari_all*8;
        $jumlah_hari = J_Absensi::on($this->getConnectionName())->where('id_pegawai', $id)->where(DB::raw('YEAR(tgl)'), $tahun)->where(DB::raw('MONTH(tgl)'), $bulan)->where('is_status', 1)->count();*/

        $jumlah_hari_libur = 0;
        $jumlah_hari_all = 0;
        $jumlah_jam_kerja_all = 0;
        $jumlah_hari = 30;

        return view('absensi._detail')->with(compact('user', 'jumlah_jam', 'tahun', 'bulan', 'jumlah_hari', 'jumlah_jam_kerja_all', 'jumlah_hari_all', 'id_apotek', 'id_searching_by'));
    }

    public function list_data2(Request $request)
    {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        DB::connection($this->getConnection())->statement(DB::raw('set @rownum = 0'));
        $data = Absensi::select([
                    DB::raw('@rownum  := @rownum  + 1 AS no'), 'tb_absensi.*'
                ])
                ->where(function($query) use($request){
                    $query->where('is_deleted','=','0');
                     $query->where('id_user',$request->id_user);
                    if($request->id_searching_by == 2) {
                        $query->where('id_apotek','LIKE','%'.$request->id_apotek.'%');
                    }
                    $query->whereYear('tgl', $request->tahun);
                    $query->whereMonth('tgl', $request->bulan);
                })
                ->orderBy('tgl');
        
        $datatables = Datatables::of($data);
        return $datatables  
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                //$query->orwhere('nama_device','LIKE','%'.$request->get('search')['value'].'%');
               // $query->orwhere('mac_address','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->editcolumn('id_user', function($data){
            return $data->user->nama; 
        }) 
        ->editcolumn('jumlah_jam_kerja', function($data){
            return number_format($data->jumlah_jam_kerja,2); 
        }) 
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .= '<span class="btn btn-primary" onClick="edit_data('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span>';
            $btn .= '<span class="btn btn-danger" onClick="delete_data('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['action', 'id_user'])
        ->addIndexColumn()
        ->make(true);  
    }
}
