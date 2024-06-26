@extends('layout.app')

@section('title')
Detail Absensi
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Data Absensi</a></li>
    <li class="breadcrumb-item active" aria-current="page">Detail</li>
</ol>
@endsection

@section('content')
	<style type="text/css">
		.select2 {
		  width: 100%!important; /* overrides computed width, 100px in your demo */
		}
	</style>

	<div class="card card-info card-outline" id="main-box" style="">
  		<div class="card-header">
        	<h3 class="card-title">
          		<i class="fas fa-star"></i> Data Absensi 
        	</h3>
        	<div class="card-tools">
        		<a href="{{url('absensi')}}" class="btn btn-danger btn-sm pull-right" data-toggle="tooltip" data-placement="top" title="Kembali ke daftar data"><i class="fa fa-undo"></i> Kembali</a>
            </div>
      	</div>
        <div class="card-body">
			<div class="row">
				<div class="col-sm-6">
				    <input type="hidden" name="id_user" id="id_user" value="{{ $user->id }}">
				    <input type="hidden" name="tahun" id="tahun" value="{{ $tahun }}">
				    <input type="hidden" name="bulan" id="bulan" value="{{ $bulan }}">
				    <input type="hidden" name="id_apotek" id="id_apotek" value="{{ $id_apotek }}">
				    <input type="hidden" name="id_searching_by" id="id_searching_by" value="{{ $id_searching_by }}">
				    <h3 class="m-t-0">Detail Absensi</h3>
				    <table width="100%">
				    	<?php
				    		$gaji1 = 3000000;
				    		$gaji = number_format($gaji1,2);
			         		$jumlah_jam = number_format($jumlah_jam->jumlah_jam,2);
			         		$lembur1 = $jumlah_jam-$jumlah_jam_kerja_all;
			         		$lembur = number_format($lembur1,2);
			         		$uang_makan1 = $jumlah_hari*40000;
			         		$uang_makan = number_format($uang_makan1,2);
			         		$uang_lembur1 = $lembur1*3*1/173*$gaji1;
			         		$uang_lembur = number_format($uang_lembur1,2);
			         		$total1 = 3000000+$uang_makan1+$uang_lembur1;
			         		$total = number_format($total1,2);
			         	?>
				      	<tr>
				         	<td width="20%">Nama</td>
				         	<td width="2%"> : </td>
				         	<td width="78">{{ $user->nama }}</td>
				      	</tr>
				      	<tr>
				         	<td width="20%">Jumlah Hari/All</td>
				         	<td width="2%"> : </td>
				         	<td width="78">{{ $jumlah_hari }}/{{ $jumlah_hari_all }}</td>
				      	</tr>
				      	<tr>
				         	<td width="20%">Jumlah Jam/All</td>
				         	<td width="2%"> : </td>
				         	
				         	<td width="78">{{ $jumlah_jam }} jam/{{ $jumlah_jam_kerja_all}} jam</td>
				      	</tr>
				      	<tr>
				         	<td width="20%">Jumlah Jam Lembur</td>
				         	<td width="2%"> : </td>
				         	<td width="78">{{ $lembur }} jam</td>
				      	</tr>
				      	<tr>
				         	<td width="20%">Gaji Pokok</td>
				         	<td width="2%"> : </td>
				         	
				         	<td width="78">Rp {{ $gaji }}</td>
				      	</tr>
				      	<tr>
				         	<td width="20%">Uang Makan</td>
				         	<td width="2%"> : </td>
				         	
				         	<td width="78">Rp {{ $uang_makan }}</td>
				      	</tr>
				      	<tr>
				         	<td width="20%">Uang Lembur</td>
				         	<td width="2%"> : </td>
				         	
				         	<td width="78">Rp {{ $uang_lembur }}</td>
				      	</tr>
				      	<tr>
				         	<td width="20%">Total Uang Diterima</td>
				         	<td width="2%"> : </td>
				         	
				         	<td width="78">Rp {{ $total }}</td>
				      	</tr>
				   	</table>
				</div>
        	</div>
        </div>
        <div class="card-footer">
        	<span class="text-info"><i class="fas fa-info"></i>&nbsp;Untuk pencarian, isikan kata yang ingin dicari pada kolom search, lalu tekan enter.</span>
      	</div>
  	</div>
  	<div class="card card-info card-outline" id="main-box" style="">
  		<div class="card-header">
        	<h3 class="card-title">
          		<i class="fas fa-list"></i>
   				Histori Absensi
        	</h3>
      	</div>
        <div class="card-body">
        	<div class="row">
	        	<div class="table-responsive">
	               	<div class="table-responsive">
	                  	<table  id="tb_user" class="table table-bordered table-striped table-hover">
							<thead>
							    <tr>
					            <th width="5%">No.</th>
					            <th width="16%">Tanggal</th>
					            <th width="12%">Jam Datang</th>
					            <th width="12%">Jam Pulang</th>
					            <th width="15%">Jumlah Jam Kerja</th>
					        </tr>
							</thead>
							<tbody>
							</tbody>
						</table>
	               	</div>
	            </div>
	        </div>
        </div>
    </div>
@endsection

@section('script')
<script type="text/javascript">
	var token = '{{csrf_token()}}';
	var tb_user = $('#tb_user').dataTable( {
			processing: true,
	        serverSide: true,
	        stateSave: true,
	        ajax:{
			        url: '{{url("absensi/list_data2")}}',
			        data:function(d){
			        	d.id_user = $("#id_user").val();
			        	d.tahun = $("#tahun").val();
			        	d.bulan = $("#bulan").val();
			        	d.id_apotek = $("#id_apotek").val();
			        	d.id_searching_by = $("#id_searching_by").val();
				    }
			     },
	        columns: [
	            {data: 'no', name: 'no',width:"2%"},
	            {data: 'tgl', name: 'tgl', class: 'text-center'},
	            {data: 'jam_datang', name: 'jam_datang', class: 'text-center'},
	            {data: 'jam_pulang', name: 'jam_pulang', class: 'text-center'},
	            {data: 'jumlah_jam_kerja', name: 'jumlah_jam_kerja', class: 'text-center'}
	        ],
	        rowCallback: function( row, data, iDisplayIndex ) {
	            var api = this.api();
	            var info = api.page.info();
	            var page = info.page;
	            var length = info.length;
	            var index = (page * length + (iDisplayIndex +1));
	            $('td:eq(0)', row).html(index);
	        },
	        stateSaveCallback: function(settings,data) {
				localStorage.setItem( 'DataTables_' + settings.sInstance, JSON.stringify(data) )
			},
			stateLoadCallback: function(settings) {
			    return JSON.parse( localStorage.getItem( 'DataTables_' + settings.sInstance ) )
			},
			drawCallback: function( settings ) {
		        var api = this.api();
		    }
 		});

	$(document).ready(function(){
	})

	function goBack() {
       window.history.back();
   }
</script>
@endsection