<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\MasterMember;
use App\MasterAgama;
use App\MasterGolonganDarah;
use App\MasterJenisKelamin;
use App\MasterKewarganegaraan;
use App\MasterGroupApotek;
use App\MasterMemberTipe;
use App\TransaksiPenjualan;
use App\MasterObat;
use App\MasterApotek;
use App\TransaksiPenjualanDetail;
use App;
use Datatables;
use DB;
use Excel;
use Auth;
use Crypt;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Traits\DynamicConnectionTrait;

class M_MemberController extends Controller
{
    use DynamicConnectionTrait;
    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function index()
    {
        return view('member.index');
    }


    /*
        =======================================================================================
        For     : 
        Author  : Surya Adiputra
        Date    : 4/03/2020
        =======================================================================================
    */
    public function list_member(Request $request)
    {
    	$order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        $super_admin = session('super_admin');
        DB::connection($this->getConnection())->statement(DB::raw('set @rownum = 0'));
        $data = MasterMember::on($this->getConnectionName())->select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_m_member.*'])
        ->where(function($query) use($request, $super_admin){
            $query->where('tb_m_member.is_deleted','=','0');
            if($super_admin == 0) {
                $query->where('tb_m_member.id_group_apotek', Auth::user()->id_group_apotek);
            }
        });
        
        $datatables = Datatables::of($data);
        return $datatables  
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->orwhere('nama','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('username','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('telepon','LIKE','%'.$request->get('search')['value'].'%');
                $query->orwhere('email','LIKE','%'.$request->get('search')['value'].'%');
            });
        })  
        ->editcolumn('id_group_apotek', function($data){
            return $data->group_apotek->nama_singkat; 
        }) 
        ->editcolumn('total_transaksi', function($data){
            $getTotal = TransaksiPenjualan::on($this->getConnectionName())->select([
                            DB::raw('SUM(total_belanja) as total_belanja_fix')
                        ])
                        ->where('id_pasien', $data->id)
                        ->where('is_deleted', 0)
                        ->first();

            return "Rp ".number_format($getTotal->total_belanja_fix,2); 
        }) 
        ->addcolumn('action', function($data) {
            $btn = '<div class="btn-group">';
            $btn .= '<span class="btn btn-primary" onClick="edit_data('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span>';
            $btn .= '<span class="btn btn-danger" onClick="delete_member('.$data->id.')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i></span>';
            $btn .= '<a href="'.url('/member/detail'.'/'.Crypt::encrypt($data->id)).'" target="_blank" title="detail" class="btn btn-info"><span data-toggle="tooltip" data-placement="top" title="detail"><i class="fa fa-list"></i></span></a>';
            $btn .='</div>';
            return $btn;
        })    
        ->rawColumns(['id_group_apotek', 'action', 'total_transaksi'])
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
        $member = new MasterMember;
        $member->setDynamicConnection();

        $jenis_kelamins = MasterJenisKelamin::on($this->getConnectionName())->where('is_deleted', 0)->pluck('jenis_kelamin', 'id');
        $jenis_kelamins->prepend('-- Pilih Jenis Kelamin --','');

        $kewarganegaraans = MasterKewarganegaraan::on($this->getConnectionName())->where('is_deleted', 0)->pluck('kewarganegaraan', 'id');
        $kewarganegaraans->prepend('-- Pilih Kewarganegaraan --','');

        $agamas = MasterAgama::on($this->getConnectionName())->where('is_deleted', 0)->pluck('agama', 'id');
        $agamas->prepend('-- Pilih Agama --','');

        $golongan_darahs = MasterGolonganDarah::on($this->getConnectionName())->where('is_deleted', 0)->pluck('golongan_darah', 'id');
        $golongan_darahs->prepend('-- Pilih Golongan Darah --','');

        $group_apoteks      = MasterGroupApotek::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $group_apoteks->prepend('-- Pilih Group Apotek --','');

        $tipe_members      = MasterMemberTipe::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama', 'id');
        $tipe_members->prepend('-- Pilih Tipe Member --','');

        return view('member.create')->with(compact('member', 'jenis_kelamins', 'agamas', 'kewarganegaraans', 'golongan_darahs', 'group_apoteks', 'tipe_members'));
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
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        $member = new MasterMember;
        $member->setDynamicConnection();
        $member->fill($request->except('_token', 'password'));
        $member->password = md5($request->password);
        $member->activated = 1;

        $jenis_kelamins = MasterJenisKelamin::on($this->getConnectionName())->where('is_deleted', 0)->pluck('jenis_kelamin', 'id');
        $jenis_kelamins->prepend('-- Pilih Jenis Kelamin --','');

        $kewarganegaraans = MasterKewarganegaraan::on($this->getConnectionName())->where('is_deleted', 0)->pluck('kewarganegaraan', 'id');
        $kewarganegaraans->prepend('-- Pilih Kewarganegaraan --','');

        $agamas = MasterAgama::on($this->getConnectionName())->where('is_deleted', 0)->pluck('agama', 'id');
        $agamas->prepend('-- Pilih Agama --','');

        $golongan_darahs = MasterGolonganDarah::on($this->getConnectionName())->where('is_deleted', 0)->pluck('golongan_darah', 'id');
        $golongan_darahs->prepend('-- Pilih Golongan Darah --','');

        $group_apoteks      = MasterGroupApotek::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $group_apoteks->prepend('-- Pilih Group Apotek --','');

        $tipe_members      = MasterMemberTipe::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama', 'id');
        $tipe_members->prepend('-- Pilih Tipe Member --','');

        $validator = $member->validate();
        if($validator->fails()){
            return view('member.create')->with(compact('member', 'jenis_kelamins', 'kewarganegaraans', 'agamas', 'golongan_darahs', 'group_apoteks', 'tipe_members'))->withErrors($validator);
        }else{
            $member->tgl_lahir = date('Y-m-d', strtotime($member->tgl_lahir));
            $member->created_by = Auth::user()->id;
            $member->save();
            session()->flash('success', 'Sukses menyimpan data!');
            return redirect('member');
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
        $member        = MasterMember::on($this->getConnectionName())->find($id);

        $jenis_kelamins = MasterJenisKelamin::on($this->getConnectionName())->where('is_deleted', 0)->pluck('jenis_kelamin', 'id');
        $jenis_kelamins->prepend('-- Pilih Jenis Kelamin --','');

        $kewarganegaraans = MasterKewarganegaraan::on($this->getConnectionName())->where('is_deleted', 0)->pluck('kewarganegaraan', 'id');
        $kewarganegaraans->prepend('-- Pilih Kewarganegaraan --','');

        $agamas = MasterAgama::on($this->getConnectionName())->where('is_deleted', 0)->pluck('agama', 'id');
        $agamas->prepend('-- Pilih Agama --','');

        $golongan_darahs = MasterGolonganDarah::on($this->getConnectionName())->where('is_deleted', 0)->pluck('golongan_darah', 'id');
        $golongan_darahs->prepend('-- Pilih Golongan Darah --','');

        $group_apoteks      = MasterGroupApotek::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama_singkat', 'id');
        $group_apoteks->prepend('-- Pilih Group Apotek --','');

        $tipe_members      = MasterMemberTipe::on($this->getConnectionName())->where('is_deleted', 0)->pluck('nama', 'id');
        $tipe_members->prepend('-- Pilih Tipe Member --','');

        return view('member.edit')->with(compact('member', 'jenis_kelamins', 'kewarganegaraans', 'agamas', 'golongan_darahs', 'group_apoteks', 'tipe_members'));
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
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        $member = MasterMember::on($this->getConnectionName())->find($id);
        $member->fill($request->except('_token', 'password'));

        if(isset($request->is_ganti_password)) {
            if($request->is_ganti_password_val == 1) {
                $member->password = md5($request->password);
            }
        } 

        $validator = $member->validate();
        if($validator->fails()){
            echo json_encode(array('status' => 0));
        }else{
            $member->tgl_lahir = date('Y-m-d', strtotime($member->tgl_lahir));
            $member->updated_by = Auth::user()->id;
            $member->save();
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
        if($this->getAccess() == 0) {
            return view('page_not_authorized');
        }
        $member = MasterMember::on($this->getConnectionName())->find($id);
        $member->is_deleted = 1;
        if($member->save()){
            echo 1;
        }else{
            echo 0;
        }
    }

    public function GetDetail($id_pasien) {
        $id_pasien = Crypt::decrypt($id_pasien);
        $data = MasterMember::on($this->getConnectionName())->find($id_pasien);

        return view('member.detail')->with(compact('data'));
    }

    public function GetListDetail(Request $request) {
        $order = $request->get('order');
        $columns = $request->get('columns');
        $order_column = $columns[$order[0]['column']]['data'];
        $order_dir = $order[0]['dir'];

        $id_pasien = Crypt::decryptString($request->id_user);
        $user = MasterMember::on($this->getConnectionName())->find($id_pasien);
        $getTotal = TransaksiPenjualan::on($this->getConnectionName())->select([
                            DB::raw('SUM(total_belanja) as total_belanja_fix')
                        ])
                        ->where('id_pasien', $user->id)
                        ->where('is_deleted', 0)
                        ->first();
        $total = $getTotal->total_belanja_fix;

        DB::connection($this->getConnection())->statement(DB::raw('set @rownum = 0'));
        $data = TransaksiPenjualan::on($this->getConnectionName())->select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_nota_penjualan.*'])
        ->where(function($query) use($request, $user){
            $query->where('tb_nota_penjualan.is_deleted','=','0');
            $query->where('tb_nota_penjualan.id_pasien', $user->id);
        });
      
        $datatables = Datatables::of($data);
        return $datatables  
        ->filter(function($query) use($request,$order_column,$order_dir){
            $query->where(function($query) use($request){
                $query->orwhere('id','LIKE','%'.$request->get('search')['value'].'%');
            });
        })    
        ->addcolumn('id_nota', function($data) use($request){
            return 'ID. '.$data->id; 
        }) 
        ->addcolumn('tgl_nota', function($data) use($request){
            return date('d-m-Y', strtotime($data->tgl_nota)); 
        }) 
        ->editcolumn('total_transaksi', function($data){
            $jumlah_jual = $data->total_belanja;
            return 'Rp '.number_format($jumlah_jual,2); 
        }) 
        ->editcolumn('poin', function($data){
            if($data->total_belanja > 0) {
                $poin = $data->total_belanja/5000; // ini nanti custome total poinnya
            } else {
                $poin = 0;
            }
            
            return $poin; 
        }) 
        ->addcolumn('action', function($data) use($request) {
            $id = Crypt::encryptString($data->id);
            $btn = '<div class="btn-group">';
            $btn .= '<a href="'.url('/penjualan/detail/'.$data->id).'" target="_blank" title="detail" class="btn btn-secondary"><span data-toggle="tooltip" data-placement="top" title="detail transaksi">[detail]</span></a>';
            $btn .='</div>';
            return $btn;
        })    
        ->with([
            "total" => $total,
            "total_format" => "Rp ".number_format($total,2),
        ])   
        ->rawColumns(['tgl_nota', 'total_transaksi', 'poin', 'action'])
        ->addIndexColumn()
        ->make(true);  
    }

    public function GetExportDetail($tgl_awal, $tgl_akhir) {
        DB::connection($this->getConnection())->statement(DB::raw('set @rownum = 0'));
        $trxs = TransaksiPenjualanDetail::on($this->getConnectionName())->select([DB::raw('@rownum  := @rownum  + 1 AS no'),'tb_detail_nota_penjualan.*', 'a.tgl_nota', 'a.id_pasien'])
                ->join('tb_nota_penjualan as a', 'a.id', '=', 'tb_detail_nota_penjualan.id_nota')
                ->where(function($query) use($tgl_awal, $tgl_akhir){
                    $query->whereDate('a.tgl_nota','>=', $tgl_awal);
                    $query->whereDate('a.tgl_nota','<=', $tgl_akhir);
                    $query->whereNotNull('a.id_pasien');
                    $query->where('a.id_pasien','!=', 0);
                    $query->where('a.id_apotek_nota', session('id_apotek_active'));
                    $query->where('tb_detail_nota_penjualan.is_cn', 0);
                    $query->where('tb_detail_nota_penjualan.is_deleted', 0);
                })
                ->get();

        $outlet = MasterApotek::on($this->getConnectionName())->find(session('id_apotek_active'));
        $inisial = strtolower($outlet->nama_singkat);

        $collection = collect();
        $array_iterasi = array();
        $no = 0;
        $total_excel=0;
        foreach($trxs as $trx) {
            $no++;
            $member = MasterMember::on($this->getConnectionName())->find($trx->id_pasien);
            $barang = MasterObat::on($this->getConnectionName())->find($trx->id_obat);
            $total = ($trx->harga_jual * $trx->jumlah)-$trx->diskon;
            $array_iterasi[] = count($collection)+2;
            $collection[] = array(
                $no,
                $trx->tgl_nota,
                $outlet->nama_singkat,
                'ID. '.$trx->id_nota.' | IDdet. '.$trx->id,
                $member->nama,
                $member->telepon,
                $member->email,
                $barang->nama,
                $trx->jumlah,
                $total,
                'Rp '.number_format($total,2),
                $trx->margin
            );
        }      

        return Excel::download(new class($collection, $array_iterasi) implements FromCollection, WithHeadings, WithColumnWidths, WithStyles {

                    public function __construct($collection, $array_iterasi)
                    {
                        $this->collection = $collection;
                        $this->array_iterasi = $array_iterasi;
                    }

                    public function headings(): array
                    {
                        return [
                        'No', 
                        'Tanggal Nota', 
                        'Apotek', 
                        'ID.Nota', 
                        'Nama Member', 
                        'Telp', 
                        'Member',
                        'Nama Obat',
                        'Jumlah', 
                        'Total Transaksi', 'Total Transaksi Format', 'Margin'];
                    } 

                    public function columnWidths(): array
                    {
                        return [
                            'A' => 8,
                            'B' => 15,
                            'C' => 25,
                            'D' => 15,
                            'E' => 35,
                            'F' => 20,
                            'G' => 35,
                            'H' => 35,
                            'I' => 10,
                            'J' => 20,
                            'K' => 30,
                            'L' => 18,           
                        ];
                    }

                    public function styles(Worksheet $sheet) 
                    {
                        $str = [
                            1    => ['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'A'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT]],
                            'B'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT]],
                            'D'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT]],
                            'E'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT]],
                            'H'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT]],
                            'I'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
                            'J'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                            'K'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                            'L'  => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
                        ];

                        return $str;
                    }

                    public function collection()
                    {
                        return $this->collection;
                    }
        },"Data-Transaksi-".$tgl_awal."-".$tgl_akhir.".xlsx");
    }

}
