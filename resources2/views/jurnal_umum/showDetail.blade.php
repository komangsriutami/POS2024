@extends('layout.app')

@section('title')
Jurnal Umum
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Jurnal Umum</a></li>
    <li class="breadcrumb-item active" aria-current="page">Detail data jurnal</li>
</ol>
@endsection

@section('content')
{!! Form::model($jurnal_umum, ['route' => ['jurnalumum.updatedata',Crypt::encrypt($jurnal_umum->id)], 'class'=>'validated_form', 'id'=>'form_jurnal', 'enctype' => 'multipart/form-data']) !!}  
    <input type="hidden" name="idjurnal" id="idjurnal" value="{{Crypt::encrypt($jurnal_umum->id)}}">  
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <b class="text-muted">Transaksi</b><br>
                        <b class="text-blue" style="font-size: 25pt">
                            {{$jurnal_umum->no_transaksi}}
                        </b><br>
                    </h3>
                    <div class="card-tools"><small></small></div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-2">
                            <b>Tgl. Transaksi</b><br>{{Date("d F Y",strtotime($jurnal_umum->tgl_transaksi))}}</small>
                        </div>
                        <div class="col-sm-2">
                            <b>No. Transaksi</b><br>{{$jurnal_umum->no_transaksi}}</small>
                        </div>
                        <div class="col-sm-5"></div>
                        <div class="col-sm-3 text-right">
                            <b>Tag</b> :&nbsp;{{$jurnal_umum->tag}}</small>
                        </div>
                    </div>


                    <?php $total_debit = 0; $total_kredit = 0; ?>
                    @if($jurnal_umum->detailjurnal->count())
                        <table class="table table-sm" style="margin-top: 30px;">
                            <thead>
                                <tr>
                                    <th>Nomor Akun</th>
                                    <th>Akun</th>
                                    <th>Deskripsi</th>
                                    <th class="text-right">Debit</th>
                                    <th class="text-right">Kredit</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($jurnal_umum->detailjurnal as $d)
                                <tr>
                                    <td>{{$d->kode_akun->kode}}</td>
                                    <td>{{$d->kode_akun->nama}}</td>
                                    <td>{{$d->deskripsi}}</td>
                                    <td class="text-right">
                                        <?php if($d->debit > 0){
                                            if($d->is_dikurang == 1){
                                                echo '('.number_format($d->debit).')';
                                            } else {
                                                echo number_format($d->debit);
                                            }
                                        } else { echo 0; }?>
                                    </td>
                                    <td class="text-right">
                                        <?php if($d->kredit > 0){
                                            if($d->is_dikurang == 1){
                                                echo '('.number_format($d->kredit).')';
                                            } else {
                                                echo number_format($d->kredit);
                                            }
                                        } else { echo 0; } ?>
                                    </td>
                                </tr>

                                <?php 
                                if($d->is_dikurang == 1){
                                    $total_debit -= $d->debit;
                                    $total_kredit -= $d->kredit;
                                } else {
                                    $total_debit += $d->debit;
                                    $total_kredit += $d->kredit;
                                } ?>
                            @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" style="padding-top: 50px;">
                                        <b>Memo</b>
                                        <p style="">{{$jurnal_umum->memo}}</p>
                                    </td>
                                    <td class="text-right"  style="padding-top: 50px;">
                                        <b>Total Debit</b><br>
                                        {{number_format($total_debit)}}
                                    </td>
                                    <td class="text-right"  style="padding-top: 50px;">
                                        <b>Total Kredit</b><br>
                                        {{number_format($total_kredit)}}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    @else
                        tidak ada data detail
                    @endif

                            <b><i class="fa fa-paperclip"></i>&nbsp;Lampiran</b>
                            @if($jurnal_umum->filebuktijurnal->count())
                                @foreach($jurnal_umum->filebuktijurnal as $b)
                                    <div class="row" style="margin-top: 5px;">
                                        <div class="col-sm-1">
                                            <div class="btn btn-block btn-outline-secondary btn-xs" onclick="openfile('{{Crypt::encrypt($b->id)}}','{{$b->type_file}}')"><i class="fa fa-search"></i> Lihat File</div>
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
                            <small>Terakhir diubah oleh {{$jurnal_umum->userUpdate->nama}} 
                            pada 
                            <?php if(is_null($jurnal_umum->updated_by)){ 
                                echo Date("d-m-Y H:i",strtotime($jurnal_umum->created_at));
                            } else {
                                echo Date("d-m-Y H:i",strtotime($jurnal_umum->updated_at));
                            } ?>
                            </small>
                        </div>
                    </div>
                </div>


                <div class="border-top">
                    <div class="card-body text-center">
                       <!--  <a target="_blank" href="{{url('jurnalumum/cetak/'.Crypt::encrypt($jurnal_umum->id))}}" class="hide btn btn-info " data-toggle="tooltip" data-placement="top" title="Print Data"><i class="fa fa-print"></i> Cetak</a> -->

                        @if(!$jurnal_umum->is_tutup_buku)
                            <a href="{{url('jurnalumum/'.Crypt::encrypt($jurnal_umum->id)).'/edit'}}" class="btn btn-primary " data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i> Edit</a>
                            <span class="btn btn-danger" onClick="delete_detail('{{Crypt::encrypt($jurnal_umum->id)}}')" data-toggle="tooltip" data-placement="top" title="Hapus Data"><i class="fa fa-times"></i> Hapus</span>
                        @endif

                        <div onclick="goBack()" class="btn btn-default pull-right" data-toggle="tooltip" data-placement="top" title="Kembali ke daftar data"><i class="fa fa-undo"></i> Kembali</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
{!! Form::close() !!}
@endsection

@section('script')
@include('jurnal_umum/_form_js')
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
                url: '{{url("jurnalumum/hapusJurnal")}}/'+id,
                async:true,
                data: {
                    _token:"{{csrf_token()}}"
                },
                beforeSend: function(data){
                    // replace dengan fungsi loading
                },
                success:  function(data){
                    if(data==1){
                        swal("Deleted!", "Detail Jurnal berhasil dihapus.", "success");
                    }else{
                        
                        swal("Failed!", "Gagal menghapus detail jurnal.", "error");
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

