@extends('layout.app')

@section('title')
Riwayat Kunjungan
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Riwayat Kunjungan</a></li>
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
	<input type="hidden" name="hak_akses" id="hak_akses" value="{{ $hak_akses }}">
	<div class="card card-info card-outline" id="main-box" style="">
  		<div class="card-header">
        	<h3 class="card-title">
          		<i class="fas fa-list"></i>
   				Detail Riwayat Kunjungan
        	</h3>
      	</div>
        <div class="card-body">
        	<form role="form" id="searching_form">
                <!-- text input -->
                <div class="row">
                    <div class="col-lg-3 form-group">
						<label>Tanggal</label>
						<input type="text" id="search_tanggal" class="form-control" placeholder="Tanggal Penjualan">
			    	</div>
                    <div class="col-lg-12" style="text-align: center;">
                        <button type="submit" class="btn btn-primary" id="datatable_filter"><i class="fa fa-search"></i> Cari</button>
                    </div>
                </div>
            </form>
			<hr>
			<div class="chart">
                <canvas id="lineChartKunjunganJam" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
        </div>
  	</div>
@endsection

@section('script')
<script type="text/javascript">
	var token = '{{csrf_token()}}';
	$(document).ready(function(){
		var hak_akses = $("#hak_akses").val();
		if(hak_akses == 1) {
			load_grafik_kunjungan();
		}
	})

	function load_grafik_kunjungan() {
		var id_role_active = $("#id_role_active").val();
		$.ajax({
            type: "GET",
            url: '{{url("home/load_grafik_kunjungan")}}',
            // dataType:'json',
            data: { 
            },
            beforeSend: function(data){
                // replace dengan fungsi loading
                spinner.show();
            },
            success:  function(data){
                
            },
            complete: function(data){
            	console.log(data.responseJSON.kunjungan);

       
            	var areaChartOptions = {
						maintainAspectRatio : false,
						responsive : true,
						legend: {
						display: false
					},
					scales: {
					xAxes: [{
						gridLines : {
						display : false,
						}
					}],
					yAxes: [{
						gridLines : {
						display : false,
						}
					}]
					}
				}
				
			
				var areaChartkunjunganData = {
					labels  : data.responseJSON.kunjungan.label,
					datasets: [
						{
						label               : 'Kunjungan Outlet : ',
						backgroundColor     : 'rgba(0, 151, 167,0.9)',
						borderColor         : 'rgba(0, 151, 167,0.8)',
						pointRadius          : false,
						pointColor          : '#006064',
						pointStrokeColor    : 'rgba(0, 151, 167,1)',
						pointHighlightFill  : '#fff',
						pointHighlightStroke: 'rgba(0, 151, 167,1)',
						data                : data.responseJSON.kunjungan.kunjungan,
						},
						{
						label               : 'Rata-rata Group : ',
						backgroundColor     : 'rgba(251, 140, 0, 1)',
						borderColor         : 'rgba(251, 140, 0, 1)',
						pointRadius         : false,
						pointColor          : 'rgba(251, 140, 0, 1)',
						pointStrokeColor    : '#e65100',
						pointHighlightFill  : '#fff',
						pointHighlightStroke: 'rgba(251, 140, 0, 1)',
						data                : data.responseJSON.kunjungan.all_kunjungan
						},
					]
				}

			    var lineChartKunjunganCanvas = $('#lineChartKunjunganJam').get(0).getContext('2d')
				var lineChartKunjunganOptions = jQuery.extend(true, {}, areaChartOptions)
				var lineChartKunjunganData = jQuery.extend(true, {}, areaChartkunjunganData)
				lineChartKunjunganData.datasets[0].fill = false;
				lineChartKunjunganData.datasets[1].fill = false;
				lineChartKunjunganOptions.datasetFill = false
				
				var lineChartKunjungan = new Chart(lineChartKunjunganCanvas, { 
					type: 'line',
					data: lineChartKunjunganData, 
					options: lineChartKunjunganOptions
				})

                spinner.hide();
            },
            error: function(data) {
                alert("error ajax occured!");
                // done_load();
            }
        });
	}
</script>
@endsection