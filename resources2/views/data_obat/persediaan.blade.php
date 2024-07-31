@extends('layout.app_penjualan')

@section('title')
Persediaan Obat
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Data Obat</a></li>
    <li class="breadcrumb-item"><a href="#">Persediaan Obat</a></li>
    <li class="breadcrumb-item active" aria-current="page">Index</li>
</ol>
@endsection

@section('content')
	<style type="text/css">
        /*custom style, untuk hide datatable length dan search value*/
        .dataTables_filter{
            display: none;
        }
        .select2 {
          width: 100%!important; /* overrides computed width, 100px in your demo */
        }
    </style>

	<div class="card card-info card-outline mb-12 border-left-primary">
	    <div class="card-body">
	      	<h4><i class="fa fa-info"></i> Informasi</h4>
	      	<p>Untuk pencarian, isikan kata yang ingin dicari pada kolom seacrh, lalu tekan enter.</p>
	    </div>
	</div>

	<div class="card card-info card-outline" id="main-box" style="">
  		<div class="card-header">
        	<h3 class="card-title">
          		<i class="fas fa-list"></i>
   				List Data Persediaan
        	</h3>
      	</div>
        <div class="card-body">
        	<form role="form" id="searching_form">
                <!-- text input -->
                <div class="row">
                	<?php
                		$nama_apotek_active = session('nama_apotek_active');
                	?>
                	<input type="hidden" name="nama_apotek" id="nama_apotek" value="{{ $nama_apotek_active }}">
                	<div class="form-group  col-md-2">
                        <label>Dari Tanggal</label>
                        <input type="text" name="tgl_awal"  id="tgl_awal" class="datepicker form-control" autocomplete="off" value="{{ $first_day }}">
                    </div>
                    <div class="form-group  col-md-2">
                        <label>Sampai Tanggal</label>
                        <input type="text" name="tgl_akhir" id="tgl_akhir" class="datepicker form-control" autocomplete="off" value="{{ $first_day }}">
                    </div>
                    <!-- <div class="form-group col-md-3">
				        {!! Form::label('tahun', 'Pilih Tahun') !!}
				        <select id="tahun" name="tahun" class="form-control input_select">
				        	<option value="2020" {!!( "2020" == $tahun ? 'selected' : '')!!}>2020</option>
				        	<option value="2021" {!!( "2021" == $tahun ? 'selected' : '')!!}>2021</option>
				        	<option value="2022" {!!( "2022" == $tahun ? 'selected' : '')!!}>2022</option>
				        </select>
				    </div>
				    <div class="form-group col-md-3">
				        {!! Form::label('bulan', 'Pilih Bulan') !!}
				        <select id="bulan" name="bulan" class="form-control input_select">
				        	<option value="1" {!!( "1" == $bulan ? 'selected' : '')!!}>Januari</option>
				        	<option value="2" {!!( "2" == $bulan ? 'selected' : '')!!}>Februari</option>
				        	<option value="3" {!!( "3" == $bulan ? 'selected' : '')!!}>Maret</option>
				        	<option value="4" {!!( "4" == $bulan ? 'selected' : '')!!}>April</option>
				        	<option value="5" {!!( "5" == $bulan ? 'selected' : '')!!}>Mei</option>
				        	<option value="6" {!!( "6" == $bulan ? 'selected' : '')!!}>Juni</option>
				        	<option value="7" {!!( "7" == $bulan ? 'selected' : '')!!}>Juli</option>
				        	<option value="8" {!!( "8" == $bulan ? 'selected' : '')!!}>Agustus</option>
				        	<option value="9" {!!( "9" == $bulan ? 'selected' : '')!!}>September</option>
				        	<option value="10" {!!( "10" == $bulan ? 'selected' : '')!!}>Oktober</option>
				        	<option value="11" {!!( "11" == $bulan ? 'selected' : '')!!}>November</option>
				        	<option value="12" {!!( "12" == $bulan ? 'selected' : '')!!}>Desember</option>
				        </select>
				    </div> -->
                    <div class="col-lg-12" style="text-align: center;">
                        <button type="submit" class="btn btn-primary" id="datatable_filter"><i class="fa fa-search"></i> Cari</button>
                        <span class="btn bg-olive" onClick="export_data()"  data-toggle="modal" data-placement="top" title="Export Data Transfer"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export</span> 
                        <span class="btn bg-danger" onClick="clear_cache()"  data-toggle="modal" data-placement="top" title="Clear Cache"><i class="fa fa-retweet" aria-hidden="true"></i> Clear Cache</span> 
                    </div>
                </div>
            </form>
			<hr>
			<table  id="tb_data_persediaan" class="table table-bordered table-striped table-hover" width="100%">
		    	<thead>
			        <tr>
			            <th width="3%" class="text-center">No.</th>
			            <th width="10%" class="text-center">Barcode</th>
			            <th width="20%" class="text-center">Nama Obat</th>
			            <th width="7%" class="text-center">Stok Awal</th> 
			            <th width="7%" class="text-center">Jum Jual</th>
			            <th width="7%" class="text-center">Jum Beli</th>
			            <th width="7%" class="text-center">Jum Transfer</th>
			            <th width="6%" class="text-center">P.Plus</th>
			            <th width="6%" class="text-center">P.Min</th>
			            <th width="7%" class="text-center">Stok Akhir</th>
                        <th width="10%" class="text-center">Harga Pokok</th>
                        <th width="10%" class="text-center">Harga Jual</th>
			        </tr>
		        </thead>
		        <tbody>
		        </tbody>
			</table>
        </div>
  	</div>
@endsection

@section('script')
<script type="text/javascript">
	var token = '{{csrf_token()}}';
	var tb_data_persediaan = $('#tb_data_persediaan').dataTable( {
			processing: true,
	        serverSide: true,
	        stateSave: true,
	        ajax:{
			        url: '{{url("data_obat/list_persediaan")}}',
			        data:function(d){
                        d.tgl_awal = $("#tgl_awal").val();
                        d.tgl_akhir = $("#tgl_akhir").val();
				    }
			     },
	        columns: [
	            {data: 'no', name: 'no',width:"2%", class:'text-center'},
	            {data: 'barcode', name: 'barcode', class:'text-center'},
	            {data: 'id_obat', name: 'id_obat'},
	            {data: 'stok_awal', name: 'stok_awal', class:'text-center'},
	            {data: 'jumlah_jual', name: 'jumlah_jual', class:'text-center'},
                {data: 'jumlah_beli', name: 'jumlah_beli', class:'text-center'},
                {data: 'jumlah_transfer', name: 'jumlah_transfer', class:'text-center'},
                {data: 'jumlah_p_plus', name: 'jumlah_p_plus', class:'text-center'},
                {data: 'jumlah_p_min', name: 'jumlah_p_min', class:'text-center'},
                {data: 'stok_akhir', name: 'stok_akhir', class:'text-center'},
                {data: 'harga_beli_ppn', name: 'harga_beli_ppn', class:'text-center'},
                {data: 'harga_jual', name: 'harga_jual', class:'text-center'}
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
        $("#searching_form").submit(function(e){
            e.preventDefault();
            tb_data_persediaan.fnDraw(false);
        });

        $('#tgl_awal, #tgl_akhir').datepicker({
            autoclose:true,
            format:"yyyy-mm-dd",
            forceParse: false
        });

        $('.input_select').select2({});

        $('body').addClass('sidebar-collapse');
	})


	function export_data() {
		swal({
		  	title: "Apakah anda akan melakukan download data persedian?",
		  	text: 'Proses ini akan memerlukan waktu yang cukup lama, mohon bersabar sampai proses selesai.',
		  	type: "warning",
		  	showCancelButton: true,
		  	confirmButtonColor: "#DD6B55",
		  	confirmButtonText: "Ya",
		  	cancelButtonText: "Tidak",
		  	closeOnConfirm: true
		},
		function(){
			spinner.show();
			load_next();
		});
	}

	function load_next() {
		$.ajax({
			type: "GET",
			url: '{{url("data_obat/reload_export_persediaan")}}',
			async:true,
			data: {
				_token:token,
				tgl_awal : $("#tgl_awal").val(),
                tgl_akhir : $("#tgl_akhir").val()
			},
			beforeSend: function(data){
				// replace dengan fungsi loading
			},
			success: function(data) {
				if(data == 0) {
					load_next();
				} else {
					stop();
				}
		    },
			complete: function(data){
				
			},
			error: function(data) {
				swal("Error!", "Ajax occured.", "error");
				clear_cache();
			}
		});
	}

	function clear_cache() {
		$.ajax({
			type: "GET",
			url: '{{url("data_obat/clear_cache_persediaan")}}',
			async:true,
			data: {
				_token:token,
				tgl_awal : $("#tgl_awal").val(),
                tgl_akhir : $("#tgl_akhir").val()
			},
			beforeSend: function(data){
				// replace dengan fungsi loading
			},
			success: function(data) {
				
		    },
			complete: function(data){
				spinner.hide();
				tb_data_persediaan.fnDraw(false);
			},
			error: function(data) {
				swal("Error!", "Ajax occured.", "error");
			}
		});
	}

	function stop() {
		$.ajax({
			xhrFields: {
		        responseType: 'blob',
		    },
			type: "GET",
			url: '{{url("data_obat/export_persediaan")}}',
			async:true,
			data: {
				_token:token,
				tgl_awal : $("#tgl_awal").val(),
                tgl_akhir : $("#tgl_akhir").val()
			},
			beforeSend: function(data){
				// replace dengan fungsi loading
				//spinner.show();
			},
			success: function(result, status, xhr) {
				var dateObj = new Date();
				var month = String(dateObj.getMonth()).padStart(2, '0');
			    var day = String(dateObj.getDate()).padStart(2, '0');
			    var year = dateObj.getFullYear();
			    var today = day+month+year;

				var namafile = "Persediaan_"+$("#nama_apotek").val()+"_"+$("#tgl_awal").val()+"-sd-"+$("#tgl_akhir").val()+"_"+today+".xlsx";
		        var disposition = xhr.getResponseHeader('content-disposition');
		        var matches = /"([^"]*)"/.exec(disposition);
		        var filename = (matches != null && matches[1] ? matches[1] : namafile);

		        // The actual download
		        var blob = new Blob([result], {
		            type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
		        });
		        var link = document.createElement('a');
		        link.href = window.URL.createObjectURL(blob);
		        link.download = filename;

		        document.body.appendChild(link);

		        link.click();
		        document.body.removeChild(link);

		        clear_cache();
		    },
			complete: function(data){
				
			},
			error: function(data) {
				swal("Error!", "Ajax occured.", "error");
				clear_cache();
			}
		});
	}



	/*function export_data() {
		swal({
		  	title: "Apakah anda akan melakukan download data persedian?",
		  	type: "warning",
		  	showCancelButton: true,
		  	confirmButtonColor: "#DD6B55",
		  	confirmButtonText: "Ya",
		  	cancelButtonText: "Tidak",
		  	closeOnConfirm: true
		},
		function(){
			$.ajax({
				xhrFields: {
			        responseType: 'blob',
			    },
				type: "GET",
				url: '{{url("data_obat/export_persediaan")}}',
				async:true,
				data: {
					_token:token,
					tahun : $("#tahun").val(),
					bulan : $("#bulan").val(),
				},
				beforeSend: function(data){
					// replace dengan fungsi loading
					spinner.show();
				},
				success: function(result, status, xhr) {
					var dateObj = new Date();
					var month = String(dateObj.getMonth()).padStart(2, '0');
				    var day = String(dateObj.getDate()).padStart(2, '0');
				    var year = dateObj.getFullYear();
				    var today = day+month+year;

					var namafile = "Persediaan_"+$("#nama_apotek").val()+"_"+$("#tahun").val()+"_"+$("#bulan").val()+"_"+today+".xlsx";
			        var disposition = xhr.getResponseHeader('content-disposition');
			        var matches = /"([^"]*)"/.exec(disposition);
			        var filename = (matches != null && matches[1] ? matches[1] : namafile);

			        // The actual download
			        var blob = new Blob([result], {
			            type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
			        });
			        var link = document.createElement('a');
			        link.href = window.URL.createObjectURL(blob);
			        link.download = filename;

			        document.body.appendChild(link);

			        link.click();
			        document.body.removeChild(link);
			    },
				complete: function(data){
					spinner.hide();
					tb_data_persediaan.fnDraw(false);
				},
				error: function(data) {
					swal("Error!", "Ajax occured.", "error");
				}
			});
		});
	}
*/
    function export_data_back(){
        window.open("{{ url('data_obat/export_persediaan') }}"+ "?tahun="+$('#tahun').val()+"&bulan="+$('#bulan').val(),"_blank");
    }
</script>
@endsection