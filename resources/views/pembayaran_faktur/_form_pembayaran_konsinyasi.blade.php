@extends('layout.app')

@section('title')
Pembayaran Konsinyasi
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Keuangan</a></li>
    <li class="breadcrumb-item"><a href="#">Pembayaran Konsinyasi</a></li>
    <li class="breadcrumb-item active" aria-current="page">Proses</li>
</ol>
@endsection

@section('content')
	<style type="text/css">
		.select2 {
		  width: 100%!important; /* overrides computed width, 100px in your demo */
		}
	</style>
    <style type="text/css">
        #divfix {
           bottom: 0;
           right: 0;
           position: fixed;
           z-index: 3000;
            }
        .format_total {
            font-size: 18px;
            font-weight: bold;
            color:#D81B60;
        }
    </style>
    <div class="row" id="divfix">
        <div class="col-sm-12">
            <div class="callout callout-success">
                <div id="btn_set" style="display: inline-block;"></div>
            </div>
        </div>
    </div>

	<div class="card card-info card-outline" id="main-box" style="">
  		<div class="card-header">
        	<h3 class="card-title">
          		<i class="fas fa-list"></i>
   				Data Pembelian
        	</h3>
      	</div>
        <div class="card-body">
            <form role="form" id="searching_form">
            	<div class="row">
                    <div class="form-group col-md-4">
                        {!! Form::hidden('is_update_pembelian', 1, array('class' => 'form-control', 'id' =>'is_update_pembelian')) !!}
                        {!! Form::label('no_faktur', 'No Faktur') !!}
                        {!! Form::text('no_faktur', $pembelian->no_faktur, array('class' => 'form-control', 'placeholder'=>'No Faktur', 'readonly' => 'readonly')) !!}
                    </div>
                    <div class="form-group col-md-4">
                        {!! Form::label('tgl_faktur', 'Tanggal Faktur') !!}
                        {!! Form::text('tgl_faktur', $pembelian->tgl_faktur, array('class' => 'form-control', 'placeholder'=>'Tanggal Faktur', 'readonly' => 'readonly')) !!}
                    </div>
                    <div class="form-group col-md-4">
                        {!! Form::label('tgl_jatuh_tempo', 'Tanggal Jatuh Tempo') !!}
                        {!! Form::text('tgl_jatuh_tempo', $pembelian->tgl_jatuh_tempo, array('class' => 'form-control', 'placeholder'=>'Tanggal jatuh tempo', 'readonly' => 'readonly')) !!}
                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('apotek', 'Apotek') !!}
                        {!! Form::text('apotek', $pembelian->apotek->nama_panjang, array('class' => 'form-control', 'placeholder'=>'Nama Apotek', 'readonly' => 'readonly')) !!}
                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('suplier', 'Suplier') !!}
                        {!! Form::text('suplier', $pembelian->suplier->nama, array('class' => 'form-control', 'placeholder'=>'Nama Suplier', 'readonly' => 'readonly')) !!}
                    </div>
                    <?php
                        $total_diskon = $pembelian->total_diskon + $pembelian->total_diskon_persen + $pembelian->diskon1 + $pembelian->diskon2;
                        $total1 = $pembelian->jumlah - $total_diskon;
                        $total2 = $total1 + ($total1 * $pembelian->ppn/100);
                    ?>
                    <div class="form-group col-md-3">
                        {!! Form::label('total_pembelian', 'Total Pembelian') !!}
                        {!! Form::text('total_pembelian', $pembelian->jumlah, array('class' => 'form-control', 'placeholder'=>'Total Pembelian', 'readonly' => 'readonly')) !!}
                    </div>
                    <div class="form-group col-md-3">
                        {!! Form::label('total_diskon', 'Total Diskon') !!}
                        {!! Form::text('total_diskon', $total_diskon, array('class' => 'form-control', 'placeholder'=>'Total Diskon', 'readonly' => 'readonly')) !!}
                    </div>
                    <div class="form-group col-md-3">
                        {!! Form::label('ppn', 'PPN') !!}
                        {!! Form::text('ppn', $pembelian->ppn, array('class' => 'form-control', 'placeholder'=>'PPN', 'readonly' => 'readonly')) !!}
                    </div>
                    <div class="form-group col-md-3">
                        {!! Form::label('total', 'Total') !!}
                        {!! Form::text('total', $total2, array('class' => 'form-control', 'placeholder'=>'Total', 'readonly' => 'readonly')) !!}
                    </div>
            	</div>
            </form>
			<hr>
			<table  id="tb_faktur" class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th width="3%">No.</th>
                        <th width="7%">ID Nota</th>
                        <th width="30%">Nama Obat</th>
                        <th width="8%">Jumlah</th>
                        <th width="8%">HB</th>
                        <th width="8%">Diskon</th>
                        <th width="11%">Total</th>
                        <th width="30%">Detail</th>
                        <th width="10%">Status</th>
                        <th width="15%">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 0;?>
                    @foreach($detail_pembelians as $obj)
                    <?php 
                        $no++;
                        $total = ($obj->jumlah * $obj->harga_beli) - $obj->diskon;
                        $pembayaran_konsinyasis = $obj->pembayaran_konsinyasi;
                        $jumlah = count($pembayaran_konsinyasis);

                        $pembayaran_konsinyasi = new App\PembayaranKonsinyasi;
                        $jumlah_terbayar = 0;
                    ?>
                    <tr>
                        <td>{{$no}}</td>
                        <td>{{$obj->id}}</td>
                        <td>{{$obj->obat->nama}}</td>
                        <td>{{$obj->jumlah}}</td>
                        <td>{{$obj->harga_beli}}</td>
                        <td>{{$obj->diskon}}</td>
                        <td>{{$total}}</td>

                        @if($jumlah>0)
                            <td>
                                <?php 
                                    $i=0;
                                ?>
                                @foreach($pembayaran_konsinyasis as $val)
                                <?php 
                                    $i++;
                                    $jumlah_terbayar = $jumlah_terbayar + $val->jumlah_bayar;
                                ?>
                                Pembayaran ke-{{$i}}<br>Tgl : {{$val->tgl_bayar}} <br>
                                    Jumlah : {{$val->jumlah_bayar}}
                                    <hr>
                                
                                @endforeach
                            </td>
                            <?php 
                                $a = ($jumlah_terbayar/$obj->jumlah) * 100 ; 
                                $persentase = number_format($a, 2);
                                $sisa = $obj->jumlah-$jumlah_terbayar;
                                $b = ($sisa/$obj->jumlah) * 100;
                                $persentase_b = number_format($b, 2);
                            ?>
                            <td>
                                <span class="label label-success">{{$persentase}}% Terbayar</span>
                                @if($obj->is_retur == 1) 
                                    <span class="label label-warning">{{$persentase_b}}% Retur</span>
                                @endif
                            </td>
                            
                        @else 
                            <td>-</td>
                            <td><span class="label label-danger">Belum Terbayar</span></td>
                        @endif
                        <td><span class="label label-primary" onClick="set_pembayaran_kosinyasi({{$obj->id}})"  data-toggle="modal" data-id="{{$obj->id}}" data-placement="top" title="Data Pembayaran"><i class="fa fa-edit"></i></span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
  	</div>
@endsection

@section('script')
<script type="text/javascript">
	var token = '{{csrf_token()}}';
 
	$(document).ready(function(){	
        $('.input_select').select2({});
	})

    function set_pembayaran_kosinyasi(id){
        $.ajax({
            type: "POST",
            url: '{{url("pembelian/set_pembayaran_kosinyasi")}}/'+id,
            async:true,
            data: {
                _token:"{{csrf_token()}}"

            },
            beforeSend: function(data){
              // on_load();
            $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
            $("#modal-xl .modal-title").html("Pembayaran Konsinyasi");
            $('#modal-xl').modal("show");
            $('#modal-xl').find('.modal-body-content').html('');
            $("#modal-xl").find(".overlay").fadeIn("200");
            },
            success:  function(data){
              $('#modal-xl').find('.modal-body-content').html(data);
            },
            complete: function(data){
                $("#modal-xl").find(".overlay").fadeOut("200");
            },
              error: function(data) {
                alert("error ajax occured!");
            }
        });
    }
</script>
@endsection