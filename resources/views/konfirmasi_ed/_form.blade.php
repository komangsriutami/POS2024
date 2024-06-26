@extends('layout.app')

@section('title')
Konfirmasi ED
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Transaksi Pembelian</a></li>
    <li class="breadcrumb-item"><a href="#">Konfirmasi ED</a></li>
    <li class="breadcrumb-item active" aria-current="page">Index</li>
</ol>
@endsection

@section('content')
{!! Form::model($detail_pembelian, ['method' => 'POST', 'id' => 'myform', 'class'=>'validated_form', 'route' => ['pembelian.update_konfirmasi_ed', $detail_pembelian->id]]) !!}
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
                <div id="btn_set" style="display: inline-block;">
                    <a class="btn btn-info text-white" style="text-decoration: none;" type="button" href="{{ url('pembelian/obat_kadaluarsa')}}" data-toggle="tooltip" data-placement="top" title="List Data Penjualan"><i class="fa fa-home"></i></a> 
                    <button class="btn btn-primary" type="button" onClick="submit_valid()" data-toggle="tooltip" data-placement="top" title="Save"><i class="fa fa-save"></i> Simpan</button>
                        </div>
                </div>
            </div>
        </div>
    </div>

	<div class="card card-info card-outline" id="main-box" style="">
  		<div class="card-header">
        	<h3 class="card-title">
          		<i class="fas fa-list"></i>
   				Detail Data
        	</h3>
            <div class="card-tools">
            </div>
      	</div>
        <div class="card-body">
        	<div class="row">
                <?php
                    $nama_apotek = strtoupper($apotek->nama_panjang);
                ?>
                <table width="100%">
                    <tr>
                        <td width="27%">Apotek</td>
                        <td width="2%"> : </td>
                        <td width="70">APOTEK BWF-{{ $nama_apotek }}</td>
                    </tr>
                    <tr>
                        <td colspan="3"><hr style="border: 1px solid #004d40; padding: 0px; margin-top: 0px; margin-bottom: 10px;"></td>
                    </tr>
                    <tr>
                        <td width="27%">No Nota</td>
                        <td width="2%"> : </td>
                        <td width="70">{{$apotek->nama_singkat }}-{{ $pembelian->id }}</td>
                    </tr>
                    <tr>
                        <td width="27%">Tanggal</td>
                        <td width="2%"> : </td>
                        <td width="70">{{ $pembelian->created_at }}</td>
                    </tr>
                    <tr>
                        <td width="27%">Kasir</td>
                        <td width="2%"> : </td>
                        <td width="70">{{$detail_pembelian->created_oleh->nama }}</td>
                    </tr>
                    <tr>
                        <td colspan="3"><hr style="border: 1px solid #004d40; padding: 0px; margin-top: 0px; margin-bottom: 10px;"></td>
                    </tr>
                    <tr>
                        <td width="27%">Nama Obat</td>
                        <td width="2%"> : </td>
                        <td width="70">{{ $detail_pembelian->obat->nama }}</td>
                    </tr>
                    <tr>
                        <td width="27%">Stok Saat Ini</td>
                        <td width="2%"> : </td>
                        <td width="70">{{ $stok_before->stok_akhir }}</td>
                    </tr>
                    <tr>
                        <td width="27%">Tanggal Batch</td>
                        <td width="2%"> : </td>
                        <td width="70">{{ $detail_pembelian->tgl_batch }}</td>
                    </tr>
                    <tr>
                        <td width="27%">Batch</td>
                        <td width="2%"> : </td>
                        <td width="70">{{$detail_pembelian->id_batch }}</td>
                    </tr>
                    <?php
                        $btn = '';
                        if($konfirmasi_ed->id_jenis_penanganan == "") {
                            $btn = '<span class="text-info"><i class="fa fa-circle"></i> belum dikonfirmasi</span>';
                        } else {
                            $btn = '<span class="text-success"><i class="fa fa-check"></i> telah dikonfirmasi</span>';
                        } 
                    ?>
                    <tr>
                        <td width="13%">Status</td>
                        <td width="2%"> : </td>
                        <td width="70">{!! $btn !!}</td>
                    </tr>
                </table>
        	</div>
            <br>
            <div class="row">
                <div class="form-group col-md-2">
                    {!! Form::label('jumlah_ed', 'Jumlah Obat (*)') !!}
                    {!! Form::text('jumlah_ed', $konfirmasi_ed->jumlah_ed, array('id' => 'jumlah_ed', 'class' => 'form-control', 'placeholder'=>'Masukan jumlah obat', 'autocomplete'=>'off')) !!}
                </div>
                <div class="form-group col-md-4">
                    {!! Form::label('id_jenis_penanganan', 'Jenis Penanganan (*)') !!}
                    {!! Form::select('id_jenis_penanganan', $jenis_penanganans, $konfirmasi_ed->id_jenis_penanganan, ['class' => 'form-control input_select required', 'autocomplete'=>'off']) !!}
                </div>
            </div>
            <div class="row show_satu" style="display: none;">
                <div class="form-group col-md-4">
                    {!! Form::label('id_alasan_retur', 'Alasan Retur (*)') !!}
                    {!! Form::select('id_alasan_retur', $alasan_returs, $retur_pembelian_obat->id_alasan_retur, ['class' => 'form-control input_select required', 'autocomplete'=>'off']) !!}
                </div>
                <div class="form-group col-md-8">
                    {!! Form::label('alasan_lain', 'Alasan Lainnya') !!}
                    {!! Form::text('alasan_lain', $retur_pembelian_obat->alasan_lain, array('id' => 'alasan_lain', 'class' => 'form-control', 'placeholder'=>'Alasan lainnya', 'autocomplete'=>'off')) !!}
                </div>
            </div>
            <div class="row show_dua" style="display: none;">
                <div class="form-group col-md-3">
                    {!! Form::label('tgl_pelaksanaan', 'Tanggal Pelaksanaan (*)') !!}
                    {!! Form::text('tgl_pelaksanaan', $konfirmasi_ed->tgl_pelaksanaan, array('id' => 'tgl_pelaksanaan', 'class' => 'form-control datepicker', 'placeholder'=>'Tanggal pelaksanaan', 'autocomplete'=>'off')) !!}
                </div>
            </div>
        </div>
  	</div>
{!! Form::close() !!}
@endsection

@section('script')
<script type="text/javascript">
	var token = '{{csrf_token()}}';
 
	$(document).ready(function(){	
        $('.input_select').select2({});

        $('#tgl_pelaksanaan').datepicker({
            autoclose:true,
            format:"yyyy-mm-dd",
            forceParse: false
        });

        $('#id_jenis_penanganan').on('select2:select', function (e) {
            var abc = $("#id_jenis_penanganan").val();
            if(abc == 1) {
                $(".show_satu").show();
                $(".show_dua").hide();
            } else {
                $(".show_satu").hide();
                $(".show_dua").show();
            }
        });
	})

    function goBack() {
       window.history.back();
   }

   function submit_valid(){
        $(".validated_form").submit();
    }
</script>
@endsection