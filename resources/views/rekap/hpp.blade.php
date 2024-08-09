@extends('layout.app')

@section('title')
Harga Pokok Penjualan (HPP)
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Rekap Data</a></li>
    <li class="breadcrumb-item"><a href="#">Harga Pokok Penjualan</a></li>
    <li class="breadcrumb-item active" aria-current="page">Index</li>
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

	<div class="card card-info card-outline" id="main-box" style="">
  		<div class="card-header">
        	<h3 class="card-title">
          		<i class="fas fa-list"></i>
   				List Data Harga Pokok Penjualan
        	</h3>
      	</div>
        <div class="card-body">
        	<div class="overlay-wrapper" id="overlay-wrapper-hpp-id">
                <div class="overlay" id="overlay-hpp-id">
                </div>
	        	<form role="form" id="searching_form">
	                <!-- text input -->
	                <input type="hidden" name="jum_obat" id="jum_obat" value="{{$jum_obat}}">
	                <?php
                		$nama_apotek_active = session('nama_apotek_active');
                	?>
                	<input type="hidden" name="nama_apotek" id="nama_apotek" value="{{ $nama_apotek_active }}">
	                <div class="row">
					    <div class="form-group  col-md-2">
	                        <label>Dari Tanggal</label>
	                        <input type="text" name="tgl_awal"  id="tgl_awal" class="datepicker form-control" autocomplete="off" value="{{ $first_day }}">
	                    </div>
	                    <div class="form-group  col-md-2">
	                        <label>Sampai Tanggal</label>
	                        <input type="text" name="tgl_akhir" id="tgl_akhir" class="datepicker form-control" autocomplete="off" value="{{ $first_day }}">
	                    </div>
	                    <div class="col-lg-12" style="text-align: center;">
	                       <!--  <button type="submit" class="btn btn-primary" id="datatable_filter"><i class="fa fa-search"></i> Cari</button> -->
	                        <span class="btn bg-olive" onClick="export_data()"  data-toggle="modal" data-placement="top" title="Export Data"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export</span> 
	                        <!-- <span class="btn bg-olive" onClick="export_data_versi1()"  data-toggle="modal" data-placement="top" title="Export Data"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export V2</span>  -->
	                        <!-- <span class="btn bg-olive" onClick="export_data_versi2()"  data-toggle="modal" data-placement="top" title="Export Data"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export V2</span>  -->
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
	const totalLoops = 1; //ini cukup 1 kali loop $("#jum_obat").val(); // Total items, e.g., 12000
	//const itemsPerBatch = 1000; // Number of items to process per batch
	//const totalLoops = Math.ceil(totalItems / itemsPerBatch); // Total number of loops required
	const increment = 100 / totalLoops;
    let currentLoop = 0;
    var overlay = document.getElementById('overlay-wrapper-hpp-id');
    var overlaybody = document.getElementById('overlay-hpp-id');

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

    function updateProgressBar() {
        currentLoop++;
        width += increment;
        progressBar.css('width', width + '%');
        progressBar.attr('aria-valuenow', width);
        progressBar.text(Math.round(width) + '%');

        if(width == 100) {
            overlay.classList.remove('overlay-wrapper');
            overlaybody.classList.remove('overlay');
        }
    }

    function getData() {
    	$.ajax({
        xhrFields: {
            responseType: 'blob',
        },
        type: "GET",
        url: '{{url("penjualan/export_hpp")}}',
        async: true,
        data: {
            _token: token,
            tgl_awal: $("#tgl_awal").val(),
            tgl_akhir: $("#tgl_akhir").val()
        },
        beforeSend: function(data){
            // Optional: Show loading spinner
        },
        success: function(result, status, xhr) {
            updateProgressBar();
            if (currentLoop < totalLoops) {
                getData(); // Call getData again until totalLoops is reached
            } else {
                var dateObj = new Date();
                var month = String(dateObj.getMonth() + 1).padStart(2, '0');
                var day = String(dateObj.getDate()).padStart(2, '0');
                var year = dateObj.getFullYear();
                var today = day + month + year;

                var namafile = "HPP_" + $("#nama_apotek").val() + "_" + $("#tgl_awal").val() + "-sd-" + $("#tgl_akhir").val() + "_" + today + ".xlsx";
                var disposition = xhr.getResponseHeader('content-disposition');
                var matches = /"([^"]*)"/.exec(disposition);
                var filename = (matches != null && matches[1] ? matches[1] : namafile);

                // Actual download
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
            }
        },
        complete: function(data){
            // Optional: Hide loading spinner
        },
        error: function(data) {
            swal("Error!", "Ajax occured.", "error");
        }
    });
    }

    function export_data_versi1(){
        window.open("{{ url('penjualan/export_hpp_v1') }}"+ "?tgl_awal="+$('#tgl_awal').val()+"&tgl_akhir="+$('#tgl_akhir').val(),"_blank");
    }

    function export_data_versi2(){
        window.open("{{ url('penjualan/export_hpp_v2') }}"+ "?tgl_awal="+$('#tgl_awal').val()+"&tgl_akhir="+$('#tgl_akhir').val(),"_blank");
    }

     function export_data_backup(){
        window.open("{{ url('penjualan/export_hpp') }}"+ "?tgl_awal="+$('#tgl_awal').val()+"&tgl_akhir="+$('#tgl_akhir').val(),"_blank");
    }
</script>
@endsection