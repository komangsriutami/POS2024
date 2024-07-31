@extends('layout.app')

@section('title')
Recap Data Transaksi
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Recap Data Transaksi</a></li>
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
   				Detail Recap Data
        	</h3>
      	</div>
        <div class="card-body">
        	<form role="form" id="searching_form">
                <!-- text input -->
                <div class="row">
                    <div class="col-lg-6 form-group">
						<label>Pilih Tanggal</label>
						<input type="text" id="search_tanggal" class="form-control" placeholder="Masukan Tanggal">
			    	</div>
                    <div class="col-lg-12" style="text-align: center;">
                        <button type="submit" class="btn btn-primary" id="datatable_filter"><i class="fa fa-search"></i> Cari</button>
                        <!-- <span class="btn bg-olive" onClick="export_data()"  data-toggle="modal" data-placement="top" title="Export Data"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export</span>  -->
                    </div>
                </div>
            </form>
			<hr>
			<div id="view_here">
			</div>
			<hr>
			<div id="view_here_pembelian">
			</div>
        </div>
  	</div>
@endsection

@section('script')
<script type="text/javascript">
	var token = '{{csrf_token()}}';
	$(document).ready(function(){
		load_view();
        $("#searching_form").submit(function(e){
            e.preventDefault();
            load_view();
        });

        $('#search_tanggal').daterangepicker({
		    autoclose:true,
			//format:"yyyy-mm-dd",
		    forceParse: false
		});

        $('.input_select').select2({});

        $('body').addClass('sidebar-collapse');
	})

	function load_view() {
		$("#view_here").html('');
		var tanggal = $("#search_tanggal").val();
		$.ajax({
			type: "GET",
			url: '{{url("recap_perhari_load_view")}}',
			async:true,
			data: {
				_token:token,
				tanggal:tanggal,
			},
			beforeSend: function(data){
				// replace dengan fungsi loading
				spinner.show();
			},
			success:  function(data){
				//if(data != ''){
					$("#view_here").html(data);
					load_view_pembelian();
				/*} else {
					$("#view_here").html('<p>Load View Recap Data Transaksi Gagal!</p>');
				}*/
			},
			complete: function(data){
				//spinner.hide();
			},
			error: function(data) {
				swal("Error!", "Ajax occured.", "error");
			}
		});
	}

	function load_view_pembelian() {
		$("#view_here_pembelian").html('');
		var tanggal = $("#search_tanggal").val();
		$.ajax({
			type: "GET",
			url: '{{url("recap_perhari_pembelian_load_view")}}',
			async:true,
			data: {
				_token:token,
				tanggal:tanggal,
			},
			beforeSend: function(data){
				// replace dengan fungsi loading
				spinner.show();
			},
			success:  function(data){
				$("#view_here_pembelian").html(data);
			},
			complete: function(data){
				spinner.hide();
			},
			error: function(data) {
				swal("Error!", "Ajax occured.", "error");
			}
		});
	}
</script>
@endsection