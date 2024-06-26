@extends('layout.app')

@section('title')
Arus Kas
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Laporan</a></li>
    <li class="breadcrumb-item active" aria-current="page">Arus Kas</li>
</ol>
@endsection

@section('content')
	<style type="text/css">
		.select2 {
		  width: 100%!important; /* overrides computed width, 100px in your demo */
		}

		.bg-closing {
			background-color: #cfd8dc;
		}
	</style>

	<div class="card card-secondary card-outline">
	   <div class="card-body">
        	<form role="form" id="searching_form">
                <!-- text input -->
                <div class="row">
                    <div class="form-group  col-md-3">
                        <label>Pilih Tanggal</label>
                        <div class="input-group">
	                        <input type="text" name="tgl_akhir" id="tgl_akhir" class="datepicker form-control" value="{{ $date_now }}" autocomplete="off">
	                        <div class="input-group-append">
				                <button type="submit" class="btn btn-primary" id="datatable_filter"><i class="fa fa-search"></i> Cari</button>
	                        	<span class="btn bg-olive" onClick="export_data()"  data-toggle="modal" data-placement="top" title="Export Data Transfer"><i class="fa fa-file-excel" aria-hidden="true"></i> Export</span> 
				            </div>
				        </div>
                    </div>
                </div>

                <div class="row">
                	<table class="table table-sm" style="margin-top: 30px;">
                        <thead>
                            <tr>
                                <th colspan="3" class="text-right bg-secondary">Tanggal : {{ $date_now }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        	<tr>
                                <td colspan="3" class="bg-secondary disabled "><b>Aset</b></td>
                            </tr>
                            <tr>
                                <td colspan="3" style="padding-left:20px!important;"><b>Aset Lancar</b></td>
                            </tr>
                            @foreach($aset_lancar as $obj)
                            <tr>
                                <td width="10%" style="padding-left:40px!important;">{{ $obj['kode'] }}</td>
                                <td width="70%"><a href="">{{ $obj['nama'] }}</a></td>
                                <td width="30%" class="text-right">{{ $obj['saldo_akhir'] }}</td>
                            </tr>
                            @endforeach
                            <tr>
                                <td colspan="2" style="padding-left:20px!important;"><b>Total Aset Lancar</b></td>
                                <td width="30%" class="text-right"><b>{{ $total['total_aset_lancar'] }}</b></td>
                            </tr>
                            <tr>
                                <td colspan="2">Total Aset</td>
                                <td width="30%" class="text-right" style="padding-left:40px!important;"><b>{{ $total['total_aset_lancar'] }}</b></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="bg-secondary disabled"><b>Liabilitas dan Modal</b></td>
                            </tr>
                            <tr>
                                <td colspan="3" style="padding-left:20px!important;"><b>Liabilitas Jangka Pendek</b></td>
                            </tr>
                            @foreach($liabilitas_jangka_pendek as $obj)
                            <tr>
                                <td width="10%" style="padding-left:40px!important;">{{ $obj['kode'] }}</td>
                                <td width="70%"><a href="">{{ $obj['nama'] }}</a></td>
                                <td width="30%" class="text-right">{{ $obj['saldo_akhir'] }}</td>
                            </tr>
                            @endforeach
                            <tr>
                                <td colspan="2" style="padding-left:20px!important;"><b>Total Liabilitas</b></td>
                                <td width="30%" class="text-right">{{ $total['total_liabilitas_jangka_pendek'] }}</td>
                            </tr>
                             <tr>
                                <td colspan="3" style="padding-left:20px!important;"><b>Modal Pemilik</b></td>
                            </tr>
                             @foreach($modal as $obj)
                            <tr>
                                <td width="10%" style="padding-left:40px!important;">{{ $obj['kode'] }}</td>
                                <td width="70%"><a href="">{{ $obj['nama'] }}</a></td>
                                <td width="30%" class="text-right">{{ $obj['saldo_akhir'] }}</td>
                            </tr>
                            @endforeach
                            <tr>
                                <td width="10%" style="padding-left:40px!important;"></td>
                                <td width="70%">Akumulasi pendapatan komprehensif lain</td>
                                <td width="30%" class="text-right">0</td>
                            </tr>
                            <tr>
                                <td width="10%" style="padding-left:40px!important;"></td>
                                <td width="70%">Pendapatan sampai Tahun lalu</td>
                                <td width="30%" class="text-right">0</td>
                            </tr>
                            <tr>
                                <td width="10%" style="padding-left:40px!important;"></td>
                                <td width="70%">Pendapatan Periode ini</td>
                                <td width="30%" class="text-right">0</td>
                            </tr>   
                            <tr>
                                <td colspan="2"><b>Total Liabilitas dan Modal</b></td>
                                <td width="30%" class="text-right">{{ $total['total_modal'] }}</td>
                            </tr>                         
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
	</div>
@endsection

@section('script')
<script type="text/javascript">
	var token = '{{csrf_token()}}';

	$(document).ready(function(){
	})

	function export_data(){
        window.open("{{ url('laporan/export_neraca') }}"+ "?tgl_awal="+$('#tgl_awal').val()+"&tgl_akhir="+$('#tgl_akhir').val(),"_blank");
    }
</script>
</script>
@endsection