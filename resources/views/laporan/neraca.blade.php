@extends('layout.app')

@section('title')
Neraca
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Laporan</a></li>
    <li class="breadcrumb-item active" aria-current="page">Neraca</li>
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
                                <th colspan="2" class="text-right bg-secondary">Tanggal : {{ $date_now }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="2" class="bg-secondary disabled "><b>Aktiva</b></td>
                            </tr>
                            @foreach($arr_aktivas as $obj)
                                <tr>
                                    <td colspan="2" style="padding-left:20px!important;">
                                        <span style="font-size:10pt;"><b>({{ ($obj['kode']) }}) {{ $obj['nama'] }}</b></span>
                                    </td>
                                </tr>
                                @foreach($obj['akuns'] as $val)
                                <tr>
                                    <td width="70%" style="padding-left:40px!important;">
                                        <span style="font-size:10pt;"><a href="" class="text-info">({{ $val['kode'] }}) {{ $val['nama'] }}</a></span>
                                    </td>
                                    <td width="30%" class="text-right"><span style="font-size:10pt;">{{ $val['saldo_akhir'] }}</span></td>
                                </tr>
                                @endforeach
                                <tr>
                                    <td style="padding-left:20px!important;"><span style="font-size:10pt;"><b><i class="fa fa-long-arrow-alt-right"></i> Total {{ $obj['nama'] }}</b></span></td>
                                    <td width="30%" class="text-right"><span style="font-size:10pt;"><b>{{ $obj['total_akun'] }}</b></span></td>
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="1">Total Aktiva</td>
                                <td width="30%" class="text-right" style="padding-left:40px!important;"><b>{{ $total['total_aktiva'] }}</b></td>
                            </tr>

                            <tr>
                                <td colspan="2" class="bg-secondary disabled "><b>Pasiva</b></td>
                            </tr>
                            @foreach($arr_pasivas as $obj)
                                <tr>
                                    <td colspan="2" style="padding-left:20px!important;">
                                        <span style="font-size:10pt;"><b>({{ ($obj['kode']) }}) {{ $obj['nama'] }}</b></span>
                                    </td>
                                </tr>
                                @foreach($obj['akuns'] as $val)
                                <tr>
                                    <td width="70%" style="padding-left:40px!important;">
                                        <span style="font-size:10pt;"><a href="" class="text-info">({{ $val['kode'] }}) {{ $val['nama'] }}</a></span>
                                    </td>
                                    <td width="30%" class="text-right"><span style="font-size:10pt;">{{ $val['saldo_akhir'] }}</span></td>
                                </tr>
                                @endforeach
                                <tr>
                                    <td style="padding-left:20px!important;"><span style="font-size:10pt;"><b><i class="fa fa-long-arrow-alt-right"></i> Total {{ $obj['nama'] }}</b></span></td>
                                    <td width="30%" class="text-right"><span style="font-size:10pt;"><b>{{ $obj['total_akun'] }}</b></span></td>
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="1">Total Pasiva</td>
                                <td width="30%" class="text-right" style="padding-left:40px!important;"><b>{{ $total['total_aktiva'] }}</b></td>
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