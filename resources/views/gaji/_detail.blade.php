@extends('layout.app')

@section('title')
Detail Presensi
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Data Presensi</a></li>
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
          		<i class="fas fa-star"></i> Data Presensi 
        	</h3>
        	<div class="card-tools">
        		<a href="{{url('gaji')}}" class="btn btn-danger btn-sm pull-right" data-toggle="tooltip" data-placement="top" title="Kembali ke daftar data"><i class="fa fa-undo"></i> Kembali</a>
            </div>
      	</div>
        <div class="card-body">
			<div class="row">
				<div class="col-sm-12">
				    <input type="hidden" name="id_pegawai" id="id_pegawai" value="{{ $pegawai->id }}">
				    <input type="hidden" name="tahun" id="tahun" value="{{ $tahun }}">
				    <input type="hidden" name="bulan" id="bulan" value="{{ $bulan }}">
				    <h3 class="m-t-0">Detail Presensi</h3>
				    <div class="row">
					    <div class="col-sm-6">
					    	<table width="100%">
						    	<?php
						    		$gaji1 = $skema_gaji_aktif->gaji_pokok;
						    		$gaji = number_format($gaji1,2);

						    		$tunjangan_jabatan1 = $skema_gaji_aktif->tunjangan_jabatan;
						    		$tunjangan_jabatan = number_format($tunjangan_jabatan1,2);

						    		$tunjangan_ijin1 = $skema_gaji_aktif->tunjangan_ijin;
						    		$tunjangan_ijin = number_format($tunjangan_ijin1,2);

					         		$jumlah_jam = number_format($jumlah_jam->jumlah_jam,2);

					         		$lembur1 = $jumlah_jam-$jumlah_jam_kerja_all;
					         		if($lembur1 < 0) {
					         			$lembur1 = 0;
					         		}
					         		$lembur = number_format($lembur1,2);

					         		$total_omset_f = number_format($total_omset,2);
					         		$bonus_omset1 = 0;
					         		if($total_omset > 100000000) {
					         			$bonus_omset1 = (($skema_gaji_aktif->persen_omset/100)*$total_omset);
					         		}
					         		$bonus_omset = number_format($bonus_omset1,2);

					         		$uang_makan1 = $jumlah_hari*$skema_gaji_aktif->tunjangan_makan;
					         		$uang_makan = number_format($uang_makan1,2);

					         		$uang_transfort1 = $jumlah_hari*$skema_gaji_aktif->tunjangan_transportasi;
					         		$uang_transfort = number_format($uang_transfort1,2);

					         		$uang_lembur1 = $lembur1*3*1/173*$gaji1;
					         		$uang_lembur = number_format($uang_lembur1,2);
					         		$total1 = $gaji1+$tunjangan_jabatan1+$tunjangan_ijin1+$uang_makan1+$uang_lembur1+$bonus_omset1+$uang_makan1;
					         		$total = number_format($total1,2);
					         	?>
					         	<tr>
						         	<td width="40%">Tahun</td>
						         	<td width="2%"> : </td>
						         	<td width="58%">{{ $tahun }}</td>
						      	</tr>
						      	<tr>
						         	<td width="40%">Bulan</td>
						         	<td width="2%"> : </td>
						         	<td width="58%">{{ $bulan }}</td>
						      	</tr>
						      	<tr>
						         	<td width="40%">Nama</td>
						         	<td width="2%"> : </td>
						         	<td width="58%">{{ $pegawai->nama }}</td>
						      	</tr>
						      	<tr>
						         	<td width="40%">Jumlah Hari/All</td>
						         	<td width="2%"> : </td>
						         	<td width="58%">{{ $jumlah_hari }}/{{ $jumlah_hari_all }}</td>
						      	</tr>
						      	<tr>
						         	<td width="40%">Jumlah Jam/All</td>
						         	<td width="2%"> : </td>
						         	
						         	<td width="58%">{{ $jumlah_jam }} jam/{{ $jumlah_jam_kerja_all}} jam</td>
						      	</tr>
						      	<tr>
						         	<td width="40%">Jumlah Jam Lembur</td>
						         	<td width="2%"> : </td>
						         	<td width="58%">{{ $lembur }} jam</td>
						      	</tr>
						      	<tr>
						         	<td width="40%">Total Omset Outlet</td>
						         	<td width="2%"> : </td>
						         	<td width="58%">{{ $total_omset_f }}</td>
						      	</tr>
						    </table>
					    </div>
					    <div class="col-sm-6">
					    	<table width="100%">
						      	<tr>
						         	<td width="40%">Gaji Pokok</td>
						         	<td width="2%"> : </td>
						         	
						         	<td width="58%">Rp {{ $gaji }}</td>
						      	</tr>
						      	<tr>
						         	<td width="40%">Tunjangan Jabatan</td>
						         	<td width="2%"> : </td>
						         	
						         	<td width="58%">Rp {{ $tunjangan_jabatan }}</td>
						      	</tr>
						      	<tr>
						         	<td width="40%">Tunjangan Izin</td>
						         	<td width="2%"> : </td>
						         	
						         	<td width="58%">Rp {{ $tunjangan_ijin }}</td>
						      	</tr>
						      	<tr>
						         	<td width="40%">Uang Makan</td>
						         	<td width="2%"> : </td>
						         	
						         	<td width="58%">Rp {{ $uang_makan }}</td>
						      	</tr>
						      	<tr>
						         	<td width="40%">Uang Transportasi</td>
						         	<td width="2%"> : </td>
						         	
						         	<td width="58%">Rp {{ $uang_transfort }}</td>
						      	</tr>
						      	<tr>
						         	<td width="40%">Uang Lembur</td>
						         	<td width="2%"> : </td>
						         	
						         	<td width="58%">Rp {{ $uang_lembur }}</td>
						      	</tr>
						      	<tr>
						         	<td width="40%">Bonus Omset</td>
						         	<td width="2%"> : </td>
						         	
						         	<td width="58%">Rp {{ $bonus_omset }}</td>
						      	</tr>
						      	<tr>
						         	<td width="40%"><b>Total Uang Diterima</b></td>
						         	<td width="2%"> : </td>
						         	
						         	<td width="58%"><b>Rp {{ $total }}</b></td>
						      	</tr>
						   	</table>
						</div>
					</div>
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
	                  	<table  id="tb_pegawai" class="table table-bordered table-striped table-hover">
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
	var tb_pegawai = $('#tb_pegawai').dataTable( {
			processing: true,
	        serverSide: true,
	        stateSave: true,
	        ajax:{
			        url: '{{url("gaji/list_data")}}',
			        data:function(d){
			        	d.id_pegawai = $("#id_pegawai").val();
			        	d.tahun = $("#tahun").val();
			        	d.bulan = $("#bulan").val();
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