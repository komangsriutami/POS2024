<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterObat;
use App\MasterStokHarga;
use App\MasterApotek;
use App\User;
use App\LossSell;
use App;
use Datatables;
use DB;
use Excel;
use Auth;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LossSellController extends Controller
{
    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function index()
    {
        if(Auth::user()->is_admin == 1) {
            $apoteks = MasterApotek::where('is_deleted', 0)->get();
        } else {
            $apoteks = MasterApotek::where('is_deleted', 0)->where('id', session('id_apotek_active'))->get();
        }
        return view('loss_sell.index')->with(compact('apoteks'));
    }


    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function list_loss_sell(Request $request)
    {
    	$order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        $apotek = MasterApotek::find(session('id_apotek_active'));
        $apoteker = User::find($apotek->id_apoteker);
        $id_user = Auth::user()->id;

        $hak_akses = 0;
        if($apoteker->id == $id_user) {
            $hak_akses = 0;
        }

        if($id_user == 1 || $id_user == 2 || $id_user == 16) {
            $hak_akses = 1;
        }


        DB::statement(DB::raw('set @rownum = 0'));
        $data = LossSell::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_loss_sell.*'])
        ->where(function($query) use($request, $hak_akses){
            $query->where('tb_loss_sell.is_deleted','=','0');
            if($hak_akses == 1) {
                $query->where('id_apotek', $request->id_apotek);
            } else {
                $query->where('id_apotek', session('id_apotek_active'));
            }
        });
        
        $datatables = Datatables::of($data);
        return $datatables  
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->orwhere('id','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->addcolumn('id_obat', function($data) {
            if($data->id_obat != 0) {
                return $data->obat->nama;
            } else {
                return 'Data obat tidak ada di database | '.$data->nama_obat;
            }
        })
        ->editcolumn('is_sign', function($data){
            if($data->is_sign == 0) {
                return '<span class="label label-danger" data-toggle="tooltip" data-placement="top" title="Nota belum dicek atau dittd" style="font-size:8pt;color:#e91e63;">Belum diTTD</span>';
            } else {
                return '<span class="label label-success" data-toggle="tooltip" data-placement="top" title="Nota belum dicek atau dittd" style="font-size:8pt;color:#009688;"></i> TTD by <span class="text-warning">'.$data->sign_by.'</span></span>';
            }
        }) 
        ->addcolumn('action', function($data) use($hak_akses) {
            $btn = '<div class="btn-group">';
            $btn .= '<span class="btn btn-info btn-sm" onClick="edit_data('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i> Edit</span>';

            if($data->is_sign == 1) {
	            if($hak_akses == 1) {
	            	$btn .= '<span class="btn btn-primary btn-sm" onClick="batal_sign('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Batalkan sign ini"><i class="fa fa-unlock"></i> Batal Sign</span>';
	            	$btn .= '<span class="btn btn-danger" onClick="delete_loss_sell('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i> Hapus</span>';
	            }
	        } else {
	        	$btn .= '<span class="btn btn-warning btn-sm" onClick="sign('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Tandai faktur ini sudah dicek dan ttd"><i class="fa fa-signature"></i> Sign</span>';
                $btn .= '<span class="btn btn-danger" onClick="delete_loss_sell('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i> Hapus</span>';
	        }

            $btn .='</div>';
            return $btn;
        })    
        ->setRowClass(function ($data) {
            if($data->id_obat != 0) {
                return '';
            } else {
                return 'bg-secondary';
            }
        })  
        ->rawColumns(['action', 'is_sign'])
        ->addIndexColumn()
        ->make(true);  
    }

    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function create()
    {
        $data_ = new LossSell;
        $obats = MasterObat::where('is_deleted', 0)->pluck('nama', 'id');
        $obats->prepend('-- Pilih Obat --','');
        $obats->prepend('-- Data obat tidak ada dalam list --','0');

        return view('loss_sell.create')->with(compact('data_', 'obats'));
    }

    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function store(Request $request)
    {
        $data_ = new LossSell;
        $data_->fill($request->except('_token'));
        $data_->tanggal = date('Y-m-d');
        $data_->id_apotek = session('id_apotek_active');

        if(!empty($data_->id_obat)) {
            $obat = MasterStokHarga::where('id_obat', $data_->id_obat)->first();
            $data_->harga = $obat->harga_jual;
            $data_->total = $data_->jumlah*$data_->harga;
        } else {
            if(!is_null($data_->harga) OR $data_->harga != '' OR $data_->harga != 0) {
                $data_->total = $data_->jumlah*$data_->harga;
            } 
        }

        $validator = $data_->validate();
        if($validator->fails()){
            return view('loss_sell.create')->with(compact('data_', 'obats'))->withErrors($validator);
        }else{
            $data_->created_by = Auth::user()->id;
            $data_->created_at = date('Y-m-d H:i:s');
            $data_->save();
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('loss_sell');
        }
    }

    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function show($id)
    {
        //
    }

    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function edit($id)
    {
        $data_ = LossSell::find($id);
        $obats = MasterObat::where('is_deleted', 0)->pluck('nama', 'id');
        $obats->prepend('-- Pilih Obat --','');
        $obats->prepend('-- Data obat tidak ada dalam list --','0');

        return view('loss_sell.edit')->with(compact('data_', 'obats'));
    }

    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function update(Request $request, $id)
    {
        $data_ = LossSell::find($id);
        $data_->fill($request->except('_token'));

        if(!empty($data_->id_obat)) {
            $obat = MasterStokHarga::where('id_obat', $data_->id_obat)->first();
            $data_->harga = $obat->harga_jual;
            $data_->total = $data_->jumlah*$data_->harga;
        } else {
            if(!is_null($data_->harga) OR $data_->harga != '' OR $data_->harga != 0) {
                $data_->total = $data_->jumlah*$data_->harga;
            } 
        }

        $validator = $data_->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            $data_->updated_by = Auth::user()->id;
            $data_->updated_at = date('Y-m-d H:i:s');
            $data_->save();
            echo json_encode(array('status' => 1));
        }
    }

    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function destroy($id)
    {
        $data_ = LossSell::find($id);
        $data_->is_deleted = 1;
        $data_->deleted_by = Auth::user()->id;
        $data_->deleted_at = date('Y-m-d H:i:s');
        if($data_->save()){
            echo 1;
        }else{
            echo 0;
        }
    }

    public function informasi(){
        return view('loss_sell.informasi');
    }

    public function send_sign(Request $request)
    {
        $data_ = LossSell::find($request->id);
        $data_->is_sign = 1;
        $data_->sign_by = $request->sign_by;
        $data_->sign_at = date('Y-m-d H:i:s');

        if($data_->save()){
            echo 1;
        }else{
            echo 0;
        }
    } 

    public function batal_sign(Request $request)
    {
        $data_ = LossSell::find($request->id);
        $data_->is_sign = 0;
        $data_->sign_by = null;
        $data_->sign_at = null;
        $data_->updated_by = Auth::user()->id;
        $data_->updated_at = date('Y-m-d H:i:s');

        if($data_->save()){
            echo 1;
        }else{
            echo 0;
        }
    } 

    public function export(Request $request) 
    {
        if($request->id_apotek != '') {
            $apotek = MasterApotek::find($request->id_apotek);
            $inisial = strtolower($apotek->nama_singkat);
        } else {
            $apotek = MasterApotek::find(session('id_apotek_active'));
            $inisial = strtolower($apotek->nama_singkat);
        }
        $tanggal = $request->tanggal;
        $rekaps = LossSell::select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_loss_sell.*'])
                            ->where(function($query) use($request){
                                $query->where('tb_loss_sell.is_deleted','=','0');
                                if($request->id_apotek != '') {
                                    $query->where('id_apotek', $request->id_apotek);
                                } else {
                                    $query->where('id_apotek', session('id_apotek_active'));
                                }
                            })
                            ->groupBy('id')
                            ->get();

                $collection = collect();
                $no = 0;
                $total_excel=0;
                foreach($rekaps as $data) {
                    $no++;
                    $nama = $data->nama_obat;
                    if($data->id_obat > 0) {
                        $nama = $data->obat->nama;
                    }
                    $collection[] = array(
                        $no, //a
                        Carbon::parse($data->created_at)->format('d/m/Y H:i:s'), //b
                        $data->apotek->nama_singkat,
                        $data->id_obat, //c
                        $nama, //d
                        $data->harga, //e
                        $data->jumlah, //e
                        $data->total, //e
                        $data->keterangan
                    );
                }

        $now = date('YmdHis'); // WithColumnFormatting
        return Excel::download(new class($collection) implements FromCollection, WithHeadings, WithColumnWidths, WithStyles {

                    public function __construct($collection)
                    {
                        $this->collection = $collection;
                    }

                    public function headings(): array
                    {
                        return [
                                'No', // a
                                'Tanggal', // b 
                                'Apotek', //c
                                'ID Obat',  //d
                                'Nama',  //e
                                'Harga', //f
                                'Jumlah', //f
                                'Total', //f
                                'Keterangan',
                            ];
                    } 

                    /*public function columnFormats(): array
                    {
                        return [
                            'F' => NumberFormat::FORMAT_NUMBER,
                            'G' => NumberFormat::FORMAT_NUMBER,
                        ];
                    }*/

                    public function columnWidths(): array
                    {
                        return [
                            'A' => 8,
                            'B' => 15,
                            'C' => 10,
                            'D' => 15,
                            'E' => 30,
                            'F' => 15,
                            'G' => 15,
                            'H' => 15,
                            'I' => 50,
                        ];
                    }

                    public function styles(Worksheet $sheet)
                    {
                        return [
                            1    => ['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'A'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'M'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                        ];
                    }


                    public function collection()
                    {
                        return $this->collection;
                    }
        },"Rekap Loss Sell_".$apotek->nama_singkat."_".$now.".xlsx");
    }
}
