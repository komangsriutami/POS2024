@extends('layout.app')

@section('title')
Home
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item active" aria-current="page">Home</li>
</ol>
@endsection

@section('content')
<style type="text/css">
	hr.new2 {
	  border-top: 1px dashed red;
	}

</style>
	<input type="hidden" name="hak_akses" id="hak_akses" value="{{ $hak_akses }}">
	<div class="card mb-12 border-left-primary card-info">
	    <div class="card-body">
	      	<div class="row">
	      		<?php 
                    $nama_apotek_panjang_active = session('nama_apotek_panjang_active');
                    $id_apotek_active = session('id_apotek_active');
                    $id_role_active = session('id_role_active');
                    $date = date('d-m-Y H:i:s');
                ?>
                <input type="hidden" name="id_role_active" id="id_role_active" value="{{ $id_role_active }}">
                @if(empty($id_apotek_active))
                	<div class="col-md-12">
	      				<br>
                		<p class="text-red"><cite><b>Anda belum memilih apotek, silakan pilih apotek terlebih dahulu!</b></cite></p>
	      			</div>
                @else
                	<div class="col-lg-12 col-12">
				        <!-- small box -->
				        <div class="small-box bg-secondary">
				            <div class="inner text-center">
				                <h3>Apotek {{ $nama_apotek_panjang_active }}</h3>
				                <p>Taanggal Hari ini : {{ $date }}</p>
				            </div>
				            <div class="icon">
				                <i class="fa fa-hospital-user"></i>
				            </div>
				        </div>
				    </div>
				    <div class="col-md-12">
				    	<div class="row">
						    <div class="col-12 col-sm-6 col-md-3">
						        <div class="info-box">
						            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-boxes"></i></span>
						            <div class="info-box-content">
						                <span class="info-box-text">Purchases</span>
						                <span class="info-box-number">
						                {{ $hit_pembelian }}
						                <small>invoices</small>
						                </span>
						            </div>
						            <!-- /.info-box-content -->
						        </div>
						        <!-- /.info-box -->
						    </div>
						    <!-- /.col -->
						    <div class="col-12 col-sm-6 col-md-3">
						        <div class="info-box mb-3">
						            <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-shopping-cart"></i></span>
						            <div class="info-box-content">
						                <span class="info-box-text">Sales</span>
						                <span class="info-box-number">{{ $hit_penjualan }}
						                	<small>visits</small>
						                </span>
						            </div>
						            <!-- /.info-box-content -->
						        </div>
						        <!-- /.info-box -->
						    </div>
						    <!-- /.col -->
						    <!-- fix for small devices only -->
						    <div class="clearfix hidden-md-up"></div>
						    <div class="col-12 col-sm-6 col-md-3">
						        <div class="info-box mb-3">
						            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-database"></i></span>
						            <div class="info-box-content">
						                <span class="info-box-text">New Items</span>
						                <span class="info-box-number">{{ $hit_obat }}
						                	<small>items</small>
						                </span>
						            </div>
						            <!-- /.info-box-content -->
						        </div>
						        <!-- /.info-box -->
						    </div>
						    <!-- /.col -->
						    <div class="col-12 col-sm-6 col-md-3">
						        <div class="info-box mb-3">
						            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-users"></i></span>
						            <div class="info-box-content">
						                <span class="info-box-text">New Members</span>
						                <span class="info-box-number">{{ $hit_member }}
						                	<small>members</small>
						                </span>
						            </div>
						            <!-- /.info-box-content -->
						        </div>
						        <!-- /.info-box -->
						    </div>
						    <!-- /.col -->
						</div>
				    </div>
				    @if($hak_akses == 1)
				    	<?php 
                            
                            $total_cash_kredit = $detail_penjualan_kredit->total - $penjualan_kredit->total_debet;
                            $total_cash_kredit_format = number_format($total_cash_kredit,0,',',',');

                            $total_diskon = $detail_penjualan->total_diskon_persen + $penjualan2->total_diskon_rp;
                            $total_cn = $detail_penjualan_cn->total - $detail_penjualan_cn->total_diskon_persen;
                            $total_3 = $detail_penjualan->total-($total_diskon);//+$total_cn
                            $total_3_format = number_format($total_3,0,',',',');

                            $total_cash_kredit_terbayar = ($detail_penjualan_kredit_terbayar->total + $penjualan_kredit_terbayar->total_jasa_dokter + $penjualan_kredit_terbayar->total_jasa_resep) - $penjualan_kredit_terbayar->total_debet-$detail_penjualan_kredit_terbayar->total_diskon_vendor;
                            $total_penjualan_kredit_terbayar = $penjualan_kredit_terbayar->total_debet+$total_cash_kredit_terbayar;
                            $total_penjualan_kredit_terbayar_format = number_format($total_penjualan_kredit_terbayar,0,',',',');

                            $total_tf_masuk = number_format($total_penjualan_kredit_terbayar,0,',',',');
                            $total_tf_keluar = number_format($total_penjualan_kredit_terbayar,0,',',',');

                            $total_pembelian = number_format($detail_pembelian->total,0,',',',');
                            $total_pembelian_terbayar = number_format($detail_pembelian_terbayar->total,0,',',',');
                            $total_pembelian_blm_terbayar = number_format($detail_pembelian_blm_terbayar->total,0,',',',');
                            $total_pembelian_jatuh_tempo = number_format($detail_pembelian_jatuh_tempo->total,0,',',',');
                        ?>
						<div class="col-lg-12">
						    <!-- AREA CHART -->
						    <div class="card card-secondary">
						        <div class="card-header">
						            <h3 class="card-title"><i class="fa fa-chart-area"></i> Recap Report</h3>
						            <div class="card-tools">
						            	<a type="button" class="btn btn-info btn-sm" target="_blank" href="{{ url('resume_pareto') }}">Lihat Resume Pareto</i>
						                </a>
						            	<a type="button" class="btn btn-info btn-sm" target="_blank" href="{{ url('recap_all') }}">Lihat Detail Perbulan</i>
						                </a>
						                <a type="button" class="btn btn-info btn-sm" target="_blank" href="{{ url('recap_perhari') }}">Lihat Detail Perhari</i>
						                </a>
						                <a type="button" class="btn btn-info btn-sm" target="_blank" href="{{ url('riwayat_kunjungan') }}">Riwayat Kunjungan</i>
						                </a>
						                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
						                </button>
						                <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-times"></i></button>
						            </div>
						        </div>
						        <div class="card-body">
						        	<div class="col-lg-12">
										<div class="row">
											<div class="col-lg-12">
												<div class="callout callout-danger">
													<p class="text-red" style="font-size: 12pt;"><i class="fa fa-exclamation-circle"></i> PASTIKAN JUMLAH KASIR AKTIF DAN JUMLAH CLOSING KASIR SAMA, konfirmasi ke kasir yang bertugas jika jumlah tidak sama.</p>
												</div>
											</div>
											<div class="col-sm-3">
										        <div class="description-block border-right">
										            <!-- <span class="description-percentage text-success"><i class="fas fa-caret-up"></i> 17%</span> -->
										            <h5 class="description-header">{{ $jumlah_kasir }} / {{ $jumlah_closing_kasir }}</h5>
										            <span class="description-text">TOTAL KASIR AKTIF/CLOSING</span><br>
										            <span>{{ $kasir_aktif }}</span>
										        </div>
										        <!-- /.description-block -->
										    </div>
										    <div class="col-sm-3">
										        <div class="description-block border-right">
										            <!-- <span class="description-percentage text-success"><i class="fas fa-caret-up"></i> 17%</span> -->
										            <h5 class="description-header">Rp {{ $total_3_format }}</h5>
										            <span class="description-text">TOTAL PENJUALAN NON KREDIT</span>
										        </div>
										        <!-- /.description-block -->
										    </div>
										    <!-- /.col -->
										    <div class="col-sm-3">
										        <div class="description-block border-right">
										            <!-- <span class="description-percentage text-warning"><i class="fas fa-caret-left"></i> 0%</span> -->
										            <h5 class="description-header">Rp {{ $total_cash_kredit_format }}</h5>
										            <span class="description-text">TOTAL PENJUALAN KREDIT</span>
										        </div>
										        <!-- /.description-block -->
										    </div>
										    <!-- /.col -->
										    <div class="col-sm-3">
										        <div class="description-block">
										            <!-- <span class="description-percentage text-success"><i class="fas fa-caret-up"></i> 20%</span> -->
										            <h5 class="description-header">Rp {{ $total_penjualan_kredit_terbayar_format }}</h5>
										            <span class="description-text">TOTAL PEMBAYARAN PENJUALAN KREDIT</span>
										        </div>
										        <!-- /.description-block -->
										    </div>
										</div>
							    	</div>
							    	@if($id_role_active != 11)
							    	<hr>
							    	<div class="col-lg-12">
										<div class="row">
										    <div class="col-sm-3 col-6">
										        <div class="description-block border-right">
										            <!-- <span class="description-percentage text-success"><i class="fas fa-caret-up"></i> 17%</span> -->
										            <h5 class="description-header">Rp {{ $total_pembelian }}</h5>
										            <span class="description-text">TOTAL PEMBELIAN</span>
										        </div>
										        <!-- /.description-block -->
										    </div>
										    <!-- /.col -->
										    <div class="col-sm-3 col-6">
										        <div class="description-block border-right">
										            <!-- <span class="description-percentage text-warning"><i class="fas fa-caret-left"></i> 0%</span> -->
										            <h5 class="description-header">Rp {{ $total_pembelian_blm_terbayar }}</h5>
										            <span class="description-text">TOTAL PIUTANG PEMBELIAN</span>
										        </div>
										        <!-- /.description-block -->
										    </div>
										    <!-- /.col -->
										    <div class="col-sm-3 col-6">
										        <div class="description-block border-right">
										            <!-- <span class="description-percentage text-success"><i class="fas fa-caret-up"></i> 20%</span> -->
										            <h5 class="description-header">Rp {{ $total_pembelian_terbayar }}</h5>
										            <span class="description-text">TOTAL PEMBELIAN TERBAYAR</span>
										        </div>
										        <!-- /.description-block -->
										    </div>
										    <!-- /.col -->
										    <div class="col-sm-3 col-6">
										        <div class="description-block">
										            <!-- <span class="description-percentage text-danger"><i class="fas fa-caret-down"></i> 18%</span> -->
										            <h5 class="description-header">Rp {{ $total_pembelian_jatuh_tempo }}</h5>
										            <span class="description-text">TOTAL PEMBELIAN JATUH TEMPO</span>
										        </div>
										        <!-- /.description-block -->
										    </div>
										</div>
							    	</div>
							    	<hr>
						            <div class="col-lg-12">
										<div class="row">
										    <div class="col-sm-3 col-6">
										        <div class="description-block border-right">
										            <!-- <span class="description-percentage text-success"><i class="fas fa-caret-up"></i> 20%</span> -->
										            <h5 class="description-header">Rp {{ $total_tf_masuk }}</h5>
										            <span class="description-text">TOTAL TRANSFER MASUK</span>
										        </div>
										        <!-- /.description-block -->
										    </div>
										    <!-- /.col -->
										    <div class="col-sm-3 col-6">
										        <div class="description-block">
										            <!-- <span class="description-percentage text-danger"><i class="fas fa-caret-down"></i> 18%</span> -->
										            <h5 class="description-header text-info">-informasi belum tersedia-</h5>
										            <span class="description-text">TOTAL TRANSFER KELUAR TERBAYAR</span>
										        </div>
										        <!-- /.description-block -->
										    </div>
										    <!-- /.col -->
										    <div class="col-sm-3 col-6">
										        <div class="description-block border-right">
										            <!-- <span class="description-percentage text-success"><i class="fas fa-caret-up"></i> 20%</span> -->
										            <h5 class="description-header">Rp {{ $total_tf_keluar }}</h5>
										            <span class="description-text">TOTAL TRANSFER KELUAR</span>
										        </div>
										        <!-- /.description-block -->
										    </div>
										    <!-- /.col -->
										    <div class="col-sm-3 col-6">
										        <div class="description-block">
										            <!-- <span class="description-percentage text-danger"><i class="fas fa-caret-down"></i> 18%</span> -->
										            <h5 class="description-header text-info">-informasi belum tersedia-</h5>
										            <span class="description-text">TOTAL PIUTANG TRANSFER MASUK</span>
										        </div>
										        <!-- /.description-block -->
										    </div>
										</div>
							    	</div>
							    	@endif
						        </div>
						        <!-- /.card-body -->
						    </div>
						    <!-- /.card -->
						</div>
						<div class="col-md-12">
							<div class="callout callout-info">
								<p class="text-red">Data penjualan pada grafik ditampilkan dari data closing kasir.</p>
			                </div>
						</div>
	                    <div class="col-lg-4">
						    <!-- AREA CHART -->
						    <div class="card card-info">
						        <div class="card-header">
						            <h3 class="card-title"><i class="fa fa-chart-area"></i> Penjualan</h3>
						            <div class="card-tools">
						                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
						                </button>
						                <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-times"></i></button>
						            </div>
						        </div>
						        <div class="card-body">
						            <div class="chart">
						                <canvas id="areaChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
						            </div>
						        </div>
						        <!-- /.card-body -->
						    </div>
						    <!-- /.card -->
						</div>
						<div class="col-lg-4">
						    <!-- AREA CHART -->
						    <div class="card card-info">
						        <div class="card-header">
						            <h3 class="card-title"><i class="fa fa-chart-line"></i> Penjualan</h3>
						            <div class="card-tools">
						                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
						                </button>
						                <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-times"></i></button>
						            </div>
						        </div>
						        <div class="card-body">
						            <div class="chart">
					                  <canvas id="lineChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
					                </div>
						        </div>
						        <!-- /.card-body -->
						    </div>
						    <!-- /.card -->
						</div>
						<div class="col-lg-4">
						    <!-- AREA CHART -->
						    <div class="card card-info">
						        <div class="card-header">
						            <h3 class="card-title"><i class="fa fa-chart-bar"></i> Penjualan</h3>
						            <div class="card-tools">
						                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
						                </button>
						                <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-times"></i></button>
						            </div>
						        </div>
						        <div class="card-body">
						            <div class="chart">
					                  <canvas id="barChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
					                </div>
						        </div>
						        <!-- /.card-body -->
						    </div>
						    <!-- /.card -->
						</div>
						@if($id_role_active != 11)
						<div class="col-lg-12">
						    <!-- AREA CHART -->
						    <div class="card card-secondary">
						        <div class="card-header">
						            <h3 class="card-title"><i class="fa fa-chart-bar"></i> Penjualan, Pembelian, Transfer Masuk, & Transfer Keluar</h3>
						            <div class="card-tools">
						                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
						                </button>
						                <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-times"></i></button>
						            </div>
						        </div>
						        <div class="card-body">
						            <div class="chart">
					                  <canvas id="barChartAll" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
					                </div>
						        </div>
						        <!-- /.card-body -->
						    </div>
						    <!-- /.card -->
						</div>
						@endif
						<div class="col-lg-12">
						    <!-- AREA CHART -->
						    <div class="card card-info">
						        <div class="card-header">
						            <h3 class="card-title"><i class="fa fa-chart-line"></i> Riwayat Kunjungan</h3>
						            <div class="card-tools">
						                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
						                </button>
						                <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-times"></i></button>
						            </div>
						        </div>
						        <div class="card-body">
						            <div class="chart">
					                  <canvas id="lineChartKunjungan" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
					                </div>
						        </div>
						        <!-- /.card-body -->
						    </div>
						    <!-- /.card -->
						</div>
					@endif
                @endif
                @if($id_role_active == 11 || $id_role_active == 1 || $id_role_active == 6)
			    <div class="col-lg-6">
			        <div class="card card-secondary">
			            <div class="card-header border-0">
			                <h3 class="card-title">Staff Operasional</h3>
			                <div class="card-tools">
			                    <a href="#" class="btn btn-tool btn-sm">
			                    <i class="fas fa-download"></i>
			                    </a>
			                    <a href="#" class="btn btn-tool btn-sm">
			                    <i class="fas fa-bars"></i>
			                    </a>
			                </div>
			            </div>
			            <div class="card-body table-responsive p-0">
			                <table class="table table-striped table-valign-middle">
			                    <thead>
			                        <tr>
			                            <th width="5%">No</th>
			                            <th width="70%">Nama</th>
			                            <th width="25%">Jabatan</th>
			                        </tr>
			                    </thead>
			                    <tbody>
			                        <tr>
			                            <td>
			                                1
			                            </td>
			                            <td>{{ $apoteker->nama }}</td>
			                            <td>APJ</td>
			                        </tr>
			                        <?php $i=1; ?>
			                        @foreach($staffs as $obj)
			                        <?php $i++; ?>
			                        <tr>
			                            <td>
			                                {{ $i }}
			                            </td>
			                            <td>{{ $obj->nama }}</td>
			                            <td>Staff</td>
			                        </tr>
			                        @endforeach
			                    </tbody>
			                </table>
			            </div>
			        </div>
			        <!-- /.card -->
			    </div>

			    <div class="col-lg-6">
			        <div class="card card-secondary">
			            <div class="card-header border-0">
			                <h3 class="card-title">Investor</h3>
			                <div class="card-tools">
			                    <a href="#" class="btn btn-tool btn-sm">
			                    <i class="fas fa-download"></i>
			                    </a>
			                    <a href="#" class="btn btn-tool btn-sm">
			                    <i class="fas fa-bars"></i>
			                    </a>
			                </div>
			            </div>
			            <div class="card-body table-responsive p-0">
			                <table class="table table-striped table-valign-middle">
			                    <thead>
			                        <tr>
			                            <th width="5%">No</th>
			                            <th width="75%">Nama</th>
			                            <th width="20%">Kepemilikan</th>
			                        </tr>
			                    </thead>
			                    <tbody>
			                        <?php $i=0; ?>
			                        @foreach($insvestors as $obj)
			                        <?php $i++; ?>
			                        <tr>
			                            <td>
			                                {{ $i }}
			                            </td>
			                            <td>{{ $obj->nama }}</td>
			                            <td>{{ $obj->saham_persen }} %</td>
			                        </tr>
			                        @endforeach
			                    </tbody>
			                </table>
			            </div>
			        </div>
			        <!-- /.card -->
			    </div>
			    @endif
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
			load_garfik();
		} else {
			get_pengumuman_popup('', 1);
		}
	})


	function load_garfik() {
		var id_role_active = $("#id_role_active").val();
		$.ajax({
            type: "GET",
            url: '{{url("home/load_grafik")}}',
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
                // replace dengan fungsi mematikan loading
                var areaChartCanvas = $('#areaChart').get(0).getContext('2d')
				var areaChartData = {
					labels  : data.responseJSON.penjualan.label,
					datasets: [
						{
						label               : 'Penjualan Outlet',
						backgroundColor     : 'rgba(0, 151, 167,0.9)',
						borderColor         : 'rgba(0, 151, 167,0.8)',
						pointRadius          : false,
						pointColor          : '#006064',
						pointStrokeColor    : 'rgba(0, 151, 167,1)',
						pointHighlightFill  : '#fff',
						pointHighlightStroke: 'rgba(0, 151, 167,1)',
						data                : data.responseJSON.penjualan.values,
						},
						{
						label               : 'Rata-rata Group',
						backgroundColor     : 'rgba(251, 140, 0, 1)',
						borderColor         : 'rgba(251, 140, 0, 1)',
						pointRadius         : false,
						pointColor          : 'rgba(251, 140, 0, 1)',
						pointStrokeColor    : '#e65100',
						pointHighlightFill  : '#fff',
						pointHighlightStroke: 'rgba(251, 140, 0, 1)',
						data                : data.responseJSON.penjualan.all
						},
					]
				}
				
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
				
				// This will get the first returned node in the jQuery collection.
				var areaChart       = new Chart(areaChartCanvas, { 
					type: 'line',
					data: areaChartData, 
					options: areaChartOptions
				})

				var lineChartCanvas = $('#lineChart').get(0).getContext('2d')
				var lineChartOptions = jQuery.extend(true, {}, areaChartOptions)
				var lineChartData = jQuery.extend(true, {}, areaChartData)
				lineChartData.datasets[0].fill = false;
				lineChartData.datasets[1].fill = false;
				lineChartOptions.datasetFill = false
				
				var lineChart = new Chart(lineChartCanvas, { 
					type: 'line',
					data: lineChartData, 
					options: lineChartOptions
				})

			    var barChartCanvas = $('#barChart').get(0).getContext('2d')
			    var barChartData = jQuery.extend(true, {}, areaChartData)
			    var temp0 = areaChartData.datasets[0]
			    var temp1 = areaChartData.datasets[1]
			    barChartData.datasets[0] = temp1
			    barChartData.datasets[1] = temp0

			    var barChartOptions = {
			      responsive              : true,
			      maintainAspectRatio     : false,
			      datasetFill             : false
			    }

			    var barChart = new Chart(barChartCanvas, {
			      type: 'bar', 
			      data: barChartData,
			      options: barChartOptions
			    })


			    var areaChartDataAll = {
					labels  : data.responseJSON.penjualan.label,
					datasets: [
						{
							label               : 'Penjualan',
							backgroundColor     : 'rgba(251, 140, 0, 1)',
							borderColor         : 'rgba(251, 140, 0, 1)',
							pointRadius         : false,
							pointColor          : 'rgba(251, 140, 0, 1)',
							pointStrokeColor    : '#e65100',
							pointHighlightFill  : '#fff',
							pointHighlightStroke: 'rgba(251, 140, 0, 1)',
							data                : data.responseJSON.penjualan.values
						},	
						{
							label               : 'Pembelian',
							backgroundColor     : 'rgba(0, 151, 167,0.9)',
							borderColor         : 'rgba(0, 151, 167,0.8)',
							pointRadius          : false,
							pointColor          : '#006064',
							pointStrokeColor    : 'rgba(0, 151, 167,1)',
							pointHighlightFill  : '#fff',
							pointHighlightStroke: 'rgba(0, 151, 167,1)',
							data                : data.responseJSON.pembelian.values,
						},
						{
							label               : 'Transfer Keluar',
							backgroundColor     : 'rgba(0, 121, 107,0.9)',
							borderColor         : 'rgba(0, 121, 107,0.8)',
							pointRadius          : false,
							pointColor          : '#d81b60',
							pointStrokeColor    : 'rgba(0, 121, 107,1)',
							pointHighlightFill  : '#fff',
							pointHighlightStroke: 'rgba(0, 121, 107,1)',
							data                : data.responseJSON.transfer_keluar.values,
						},
						{
							label               : 'Transfer Masuk',
							backgroundColor     : 'rgba(2, 136, 209, 1)',
							borderColor         : 'rgba(2, 136, 209, 1)',
							pointRadius         : false,
							pointColor          : 'rgba(2, 136, 209, 1)',
							pointStrokeColor    : '#0277bd',
							pointHighlightFill  : '#fff',
							pointHighlightStroke: 'rgba(2, 136, 209, 1)',
							data                : data.responseJSON.transfer_masuk.values
						},
						/*{
						label               : 'Transfer Keluar',
						backgroundColor     : 'rgba(216, 27, 96,0.9)',
						borderColor         : 'rgba(216, 27, 96,0.8)',
						pointRadius          : false,
						pointColor          : '#d81b60',
						pointStrokeColor    : 'rgba(216, 27, 96,1)',
						pointHighlightFill  : '#fff',
						pointHighlightStroke: 'rgba(216, 27, 96,1)',
						data                : data.responseJSON.values,
						},*/
						
					]
				}

				if(id_role_active != 11) {
				    var barChartCanvasAll = $('#barChartAll').get(0).getContext('2d')
				    var barChartDataAll = jQuery.extend(true, {}, areaChartDataAll)
				    var temp0All = areaChartDataAll.datasets[0]
				    var temp1All = areaChartDataAll.datasets[1]
				    var temp2All = areaChartDataAll.datasets[2]
				    var temp3All = areaChartDataAll.datasets[3]
				    barChartDataAll.datasets[0] = temp0All
				    barChartDataAll.datasets[1] = temp1All
				    barChartDataAll.datasets[2] = temp2All
				    barChartDataAll.datasets[3] = temp3All

				    var barChartOptionsAll = {
				      responsive              : true,
				      maintainAspectRatio     : false,
				      datasetFill             : false
				    }

				    var barChartAll = new Chart(barChartCanvasAll, {
				      type: 'bar', 
				      data: barChartDataAll,
				      options: barChartOptionsAll
				    })
			    }

				var areaChartkunjunganData = {
					labels  : data.responseJSON.kunjungan.label,
					datasets: [
						{
						label               : 'Kunjungan Outlet',
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
						label               : 'Rata-rata Group',
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

			    var lineChartKunjunganCanvas = $('#lineChartKunjungan').get(0).getContext('2d')
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

                get_pengumuman_popup('', 1);
            },
            error: function(data) {
                alert("error ajax occured!");
                // done_load();
            }
        });
	}

	function get_pengumuman_popup(page_num, popup)
    {
        $.ajax({
            type: "POST",
            url: '{{url("home/get_pengumuman_popup")}}',
            async:true,
            data: {
                _token: "{{csrf_token()}}",
                page_num:page_num,
                popup:popup
            },
            beforeSend: function(data){
                // replace dengan fungsi loading
            },
            success:  function(data){
                if(data!=''){
                    show_modal_pengumuman(data);
                }
            },
            complete: function(data){
                // replace dengan fungsi mematikan loading
                //tb_quis.fnDraw(false);
            },
            error: function(data) {
                alert("error ajax occured!");
                // done_load();
            }
        });
    }

    function show_modal_pengumuman(data) {
        // on_load();
        $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class", "modal-header bg-light-blue");
        $("#modal-xl .modal-title").html("<i class='fas fa-bullhorn'></i> PENGUMUMAN");
        $('#modal-xl').modal("show");
        $('#modal-xl').find('.modal-body-content').html('');
        $("#modal-xl").find(".overlay").fadeIn("200");
        $('#modal-xl').find('.modal-body-content').html(data);
        $("#modal-xl").find(".overlay").fadeOut("200");
    }
</script>
@endsection
