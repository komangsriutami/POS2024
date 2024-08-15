@extends('layout.app')

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

	<div class="card card-info card-outline" id="main-box" style="">
  		<div class="card-header">
        	<h3 class="card-title">
          		<i class="fas fa-list"></i>
   				List Data Persediaan
        	</h3>
      	</div>
        <div class="card-body">
        	<div class="overlay-wrapper" id="overlay-wrapper-persediaan-id">
                <div class="overlay" id="overlay-persediaan-id">
                </div>
	        	<form role="form" id="searching_form">
	                <!-- text input -->
	                <input type="hidden" name="jum_obat" id="jum_obat" value="{{$jum_obat}}">
	                <?php
                		$nama_apotek_active = session('nama_apotek_active');
                	?>
                	<input type="hidden" name="nama_apotek" id="nama_apotek" value="{{ $nama_apotek_active }}">
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
	                    <div class="col-lg-12" style="text-align: center;">
	                        <span class="btn bg-olive" onClick="export_data()"  data-toggle="modal" data-placement="top" title="Export Data Transfer"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export</span> 
	                    </div>
	                </div>
	            </form>
				<hr>
	            <div class="progress" style="height: 30px;">
	                <div id="progress-bar" class="progress-bar bg-success" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div> 
	            </div>
	            <hr>
			</div>
        </div>
  	</div>
@endsection

@section('script')
<script type="text/javascript">
	var token = '{{csrf_token()}}';
	let progressBar = $('#progress-bar');
	let width = 0;
	const totalItems = $("#jum_obat").val(); // Total items, e.g., 12000
	const itemsPerBatch = 200; // Number of items to process per batch
	const totalLoops = Math.ceil(totalItems / itemsPerBatch); // Total number of loops required
	const increment = 100 / totalLoops;
    let currentLoop = 0;
    var overlay = document.getElementById('overlay-wrapper-persediaan-id');
    var overlaybody = document.getElementById('overlay-persediaan-id');

	$(document).ready(function(){
        overlay.classList.remove('overlay-wrapper');
        overlaybody.classList.remove('overlay');
        $('#tgl_awal, #tgl_akhir').datepicker({
            autoclose:true,
            format:"yyyy-mm-dd",
            forceParse: false
        });

        $('.input_select').select2({});

        $('body').addClass('sidebar-collapse');
	})


	function export_data() {
		//alert(totalLoops);
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
			overlay.classList.add('overlay-wrapper');
            overlaybody.classList.add('overlay');
			getData();
		});
	}

	function getData() {
		$.ajax({
			type: "GET",
			url: '{{url("data_obat/reload_export_persediaan")}}',
			async:true,
			data: {
				_token:token,
				tgl_awal : $("#tgl_awal").val(),
                tgl_akhir : $("#tgl_akhir").val(),
                iterasi: currentLoop
			},
			beforeSend: function(data){
				// replace dengan fungsi loading
			},
			success: function(data) {
				updateProgressBar();
	            if (currentLoop < totalLoops) {
	                getData(); // Call getData again until totalLoops is reached
	            } else {
	            	stop();
	            }
		    },
			complete: function(data){
				
			},
			error: function(data) {
				swal("Error!", "Ajax occured.", "error");
			}
		});
	}

	function updateProgressBar() {
        currentLoop++;
        width += increment;
        progressBar.css('width', width + '%');
        progressBar.attr('aria-valuenow', width);
        progressBar.text(Math.round(width) + '%');
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
				//spinner.hide();
				//tb_data_persediaan.fnDraw(false);
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

		        overlay.classList.remove('overlay-wrapper');
	            overlaybody.classList.remove('overlay');
		    },
			complete: function(data){
				clear_cache();
			},
			error: function(data) {
				swal("Error!", "Ajax occured.", "error");
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

    function export_data_new() {
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
					spinner.show();
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

			        /*clear_cache();*/
			    },
				complete: function(data){
					spinner.hide();
					tb_data_persediaan.fnDraw(false);
				},
				error: function(data) {
					swal("Error!", "Ajax occured.", "error");
					/*clear_cache();*/
					spinner.hide();
					tb_data_persediaan.fnDraw(false);
				}
			});
		});
	}
</script>
@endsection