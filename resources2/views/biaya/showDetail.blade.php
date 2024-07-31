@extends('layout.app')

@section('title')
Biaya
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Biaya</a></li>
    <li class="breadcrumb-item active" aria-current="page">Detail Biaya</li>
</ol>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <b class="text-muted">Transaksi</b><br>
                        <b class="text-blue" style="font-size: 25pt">
                            {{$biaya->no_biaya}}
                        </b><br>
                    </h3>
                    <div class="card-tools"><small></small></div>
                </div>
                <div class="card-body">

                    <div class="row">
                        <div class="col-sm-2">
                            <b>Tgl. Transaksi</b><br>{{Date("d F Y",strtotime($biaya->tgl_transaksi))}}
                        </div>
                        <div class="col-sm-2">
                            <b>No. Transaksi</b><br>{{$biaya->no_biaya}}
                        </div>
                        <div class="col-sm-5">
                            <b>Penerima</b><br>
                            @if($biaya->tipe_penerima == 1)
                            {{$biaya->supplier->nama}}
                            @elseif($biaya->tipe_penerima == 2)
                            {{$biaya->user->nama}}
                            @elseif($biaya->tipe_penerima == 3)
                            {{$biaya->member->nama}}
                            @endif
                        </div>
                        <div class="col-sm-3 text-right">
                            <b>Tag</b> :&nbsp;{{$biaya->tag}}
                        </div>
                    </div>

                    <div class="row" style="padding-top: 20px;">
                        <div class="col-sm-4">
                            <b>Bayar Dari</b><br>
                            @if(!is_null($biaya->AkunBayar))
                                {{$biaya->AkunBayar->kode.' - '.$biaya->AkunBayar->nama}}
                            @else
                                <i>Bayar Nanti</i>
                            @endif
                        </div>
                        <div class="col-sm-2">
                            <b>Cara Pembayaran</b><br>
                            @if(!is_null($biaya->id_cara_pembayaran))
                                @if($biaya->id_cara_pembayaran == 1)
                                    Cash
                                @elseif($biaya->id_cara_pembayaran == 2)
                                    Transfer
                                @else
                                    -
                                @endif
                            @else
                                -
                            @endif
                        </div>
                        <div class="col-sm-6">
                            <b>Alamat Penagihan</b><br>
                            {{$biaya->alamat_penagihan}}
                        </div>
                    </div>


                    <?php $total = $biaya->subtotal; $detailpajak = array(); ?>
                    @if(!empty($biaya->detailbiaya))
                        <table class="table table-sm" border="0" style="margin-top: 30px;">
                            <thead>
                                <tr>
                                    <th>Nomor Akun</th>
                                    <th>Akun</th>
                                    <th>Deskripsi</th>
                                    <th class="text-right">Pajak</th>
                                    <th class="text-right">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($biaya->detailbiaya as $d)
                                <tr>
                                    <td>{{$d->kode_akun->kode}}</td>
                                    <td>{{$d->kode_akun->nama}}</td>
                                    <td>{{$d->deskripsi}}</td>
                                    <td class="text-right">
                                        <?php
                                            $pjk = '';
                                            if(!is_null($d->id_akun_pajak)){
                                                $datapajak = json_decode($d->id_akun_pajak);
                                                if(count($datapajak)){
                                                    foreach ($datapajak as $key => $value) {
                                                        if($pjk != ""){ $pjk .=', '; }

                                                        $getpajak = $pajak->where("id",$value)->first();
                                                        if(!empty($getpajak)){
                                                            $isi['nama_pajak'] = $getpajak->nama;
                                                            $isi['persentase_efektif'] = $getpajak->persentase_efektif;
                                                            $isi['is_pemotongan'] = $getpajak->is_pemotongan;
                                                            $isi['jml_pajak'] = $d->biaya*$getpajak->persentase_efektif/100;

                                                            if($getpajak->is_pemotongan){
                                                                $isi['text_color'] = "text-red";
                                                            } else {
                                                                $isi['text_color'] = "";
                                                            }

                                                            $pjk .= $getpajak->nama;
                                                        }

                                                        $detailpajak[] = $isi;
                                                    }

                                                    echo $pjk;
                                                }
                                            }
                                        ?>
                                    </td>
                                    <td class="text-right">{{number_format($d->biaya)}}</td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" style="padding-top: 50px;">
                                        <b>Memo</b>
                                        <p style="">{{$biaya->memo}}</p>
                                    </td>
                                    <td class="text-right" style="padding-top: 50px;"><b>Sub Total</b></td>
                                    <td class="text-right" style="padding-top: 50px;">{{number_format($biaya->subtotal)}}</td>
                                </tr>

                                @if(count($detailpajak))
                                    @foreach($detailpajak as $p)
                                        <tr class="{{$p['text_color']}}">
                                            <td colspan="3"></td>
                                            <td class="text-right">{{$p['nama_pajak'].' ('.$p['persentase_efektif'].'%)'}}</td>
                                            <td class="text-right" >
                                                @if($p['is_pemotongan'])
                                                    {{'('.number_format($p['jml_pajak']).')'}}
                                                    <?php $total = $total - $p['jml_pajak']; ?>
                                                @else
                                                    {{number_format($p['jml_pajak'])}}
                                                    <?php $total = $total + $p['jml_pajak']; ?>
                                                @endif                                                
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif

                                @if(!is_null($biaya->kode_akun_ppn_potong))
                                <tr class="text-red">
                                    <td colspan="3"></td>
                                    <td class="text-right">
                                        <b>Potongan Pajak</b>
                                        <br>{{$biaya->kode_akun_ppn_potong->kode.' - '.$biaya->kode_akun_ppn_potong->nama}}
                                    </td>
                                    <td class="text-right" >({{number_format($biaya->ppn_potong)}})</td>
                                    <?php $total = $total - $biaya->ppn_potong; ?>
                                </tr>
                                @endif

                                <tr style="font-size: 25px;">
                                    <td colspan="3"></td>
                                    <td class="text-right"><b>Total</b></td>
                                    <td class="text-right" >{{number_format($total)}}</td>
                                </tr>
                            </tfoot>
                        </table>
                    @else
                        tidak ada data detail
                    @endif

                            <b><i class="fa fa-paperclip"></i>&nbsp;Lampiran</b>
                            @if(!empty($biaya->filebuktibiaya))
                                @foreach($biaya->filebuktibiaya as $b)
                                    <div class="row" style="margin-top: 5px;">
                                        <div class="col-sm-1">
                                            <div class="btn btn-block btn-outline-secondary btn-xs" onclick="openfile('{{Crypt::encrypt($b->id)}}')"><i class="fa fa-search"></i> Lihat File</div>
                                        </div>
                                        <div class="col-sm-10">
                                            {{$b->keterangan}}
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <br><i>Tidak ada</i>
                            @endif
                    


                    <div class="row">
                        <div class="col-sm-12 text-right" style="margin-top:30px">
                            <small>Terakhir diubah oleh {{$biaya->userUpdate->nama}} 
                            pada 
                            <?php if(is_null($biaya->updated_by)){ 
                                echo Date("d-m-Y H:i",strtotime($biaya->created_at));
                            } else {
                                echo Date("d-m-Y H:i",strtotime($biaya->updated_at));
                            } ?>
                            </small>
                        </div>
                    </div>
                </div>


                <div class="border-top">
                    <div class="card-body text-center">

                        @if(!$biaya->is_tutup_buku)
                            <a href="{{url('biaya/'.Crypt::encrypt($biaya->id)).'/edit'}}" class="btn btn-primary " data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i> Edit</a>
                            <span class="btn btn-danger" onClick="delete_detail('{{Crypt::encrypt($biaya->id)}}')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i> Hapus</span>
                        @endif

                        <div onclick="goBack()" class="btn btn-default pull-right" data-toggle="tooltip" data-placement="top" title="Kembali ke daftar data"><i class="fa fa-undo"></i> Kembali</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('script')
@include('biaya/_form_js', ['biaya'=>$biaya])
<script type="text/javascript">
    function delete_detail(id){
        swal({
            title: "Apakah anda yakin menghapus data?",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Ya",
            cancelButtonText: "Tidak",
            closeOnConfirm: false
        },
        function(){
            $.ajax({
                type: "DELETE",
                url: '{{url("biaya")}}/'+id,
                async:true,
                dataType:"json",
                data: {
                    _token:"{{csrf_token()}}"
                },
                beforeSend: function(data){
                    // replace dengan fungsi loading
                },
                success:  function(data){
                    if(data.status==1){

                        /*swal("Deleted!", "Data Biaya berhasil dihapus.", "success");*/

                        if(data.statusjurnal==1){
                            swal("Deleted!", "Data berhasil dihapus.", "success");
                        } else {
                            swal("Warning!", "Data biaya berhasil dihapus. Gagal menghapus data jurnal terkait biaya.", "warning");
                        }

                    }else if(data.status == 2){                        
                        swal("Failed!", "Data biaya tidak ditemukan.", "error");
                    }else{                        
                        swal("Failed!", "Gagal menghapus data biaya.", "error");
                    }
                },
                complete: function(data){
                    goBack();
                },
                error: function(data) {
                    swal("Error!", "Ajax occured.", "error");
                }
            });
        });
    }
</script>
@endsection

