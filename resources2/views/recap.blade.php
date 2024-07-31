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
                    <div class="form-group col-md-3">
				        {!! Form::label('tahun', 'Pilih Tahun') !!}
				        <select id="tahun" name="tahun" class="form-control input_select">
				        	<option value="2020" {!!( "2020" == $tahun ? 'selected' : '')!!}>2020</option>
				        	<option value="2021" {!!( "2021" == $tahun ? 'selected' : '')!!}>2021</option>
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

        $('#tgl_awal, #tgl_akhir').datepicker({
            autoclose:true,
            format:"yyyy-mm-dd",
            forceParse: false
        });

        $('.input_select').select2({});

        $('body').addClass('sidebar-collapse');
	})

	function load_view() {
		$("#view_here").html('');
		var tahun = $("#tahun").val();
		var bulan = $("#bulan").val();
		$.ajax({
			type: "GET",
			url: '{{url("recap_all_load_view")}}',
			async:true,
			data: {
				_token:token,
				tahun:tahun,
				bulan:bulan,
			},
			beforeSend: function(data){
				// replace dengan fungsi loading
				spinner.show();
			},
			success:  function(data){
				$("#view_here").html(data);
				load_view_pembelian();
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
		var tahun = $("#tahun").val();
		var bulan = $("#bulan").val();
		$.ajax({
			type: "GET",
			url: '{{url("recap_all_pembelian_load_view")}}',
			async:true,
			data: {
				_token:token,
				tahun:tahun,
				bulan:bulan,
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

    function export_data(){
        window.open("{{ url('penjualan/export_hpp') }}"+ "?tahun="+$('#tahun').val()+"&bulan="+$('#bulan').val(),"_blank");
    }
</script>
@endsection