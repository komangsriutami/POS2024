@extends('layout.app')



@section('title')

Resume Pareto

@endsection



@section('breadcrumb')

<ol class="breadcrumb float-sm-right">

    <li class="breadcrumb-item"><a href="#">Resume Pareto</a></li>

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

   				Detail Resume Pareto

        	</h3>

      	</div>

        <div class="card-body">

        	<form role="form" id="searching_form">

                <!-- text input -->

                <div class="row">

                	<div class="col-lg-1 form-group">

						<label>Limit Data</label>

						<input type="text" id="limit" class="form-control" placeholder="Limit" value="100">

			    	</div>

			    	<div class="form-group col-md-2">

                		<div class="row">

							<div class="form-group col-lg-12">

								{!! Form::label('id_pencarian', 'Pilih Tipe Pencarian') !!}

								{!! Form::select('id_pencarian', ['1' => 'Hari Ini', '2' => 'Kemarin', '3' => 'Pekan Ini', '4' => 'Pekan Lalu', '5' => 'Bulan Ini', '6' => 'Bulan Lalu', '7' => '3 Bulan Terakhir', '8' => '6 Bulan Terakhir', '9' => 'Kalender'], 1, ['placeholder' => '-- tipe --', 'class' => 'form-control input_select required']) !!}

							</div>

							<div class="col-lg-12 form-group" hidden>

								<label>Tanggal</label>

								<input type="text" id="search_tanggal" class="form-control" placeholder="Tanggal Penjualan">

							</div>

						</div>

				    </div>

                	<div class="col-lg-2 form-group">

						<label>Nama Obat</label>

						<input type="text" id="nama" class="form-control" placeholder="Nama">

			    	</div>

					<div class="form-group col-lg-1">

						{!! Form::label('nilai_pareto', 'Nilai Pareto') !!}

						{!! Form::select('nilai_pareto', ['1' => 'A', '2' => 'B', '3' => 'C'], null, ['placeholder' => '-- nilai --', 'class' => 'form-control input_select']) !!}

					</div>

                    <div class="col-lg-12" style="text-align: center;">

                        <button type="submit" class="btn btn-primary" id="datatable_filter"><i class="fa fa-search"></i> Cari</button>

                        <span class="btn bg-olive" onClick="export_pareto()"  data-toggle="modal" data-placement="top" title="Export Pareto"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export Pareto</span>

                        <span class="btn bg-olive" onClick="export_pembelian()"  data-toggle="modal" data-placement="top" title="Export Pembelian"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export Pembelian</span>

						<?php

						if (in_array(session('id_role_active'), [1, 6])) {

						?>

							<span class="btn bg-olive" onClick="export_pareto_all()" data-toggle="modal" data-placement="top" title="Export Pareto All"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export Pareto All</span>

						<?php

						}

						?>

                    </div>

                </div>

            </form>

			<hr>

			<div id="view_here">

				<div class="card p-4" id="akumulasi_produk">

					<div class="row">

						<div class="col-md-12">

							<h4 class="akumulasi_produk_date">Akumulasi Hasil Analisis dari _ s.d. _</h4>

                		</div>

						<div class="col-md-6">

							<div class="row">

								<div class="col-md-6">Pareto A</div>

								<div class="col-md-6 pareto-a">: 0 produk</div>

							</div>

							<div class="row">

								<div class="col-md-6">Pareto B</div>

								<div class="col-md-6 pareto-b">: 0 produk</div>

							</div>

						</div>

						<div class="col-md-6">

							<div class="row">

								<div class="col-md-6">Pareto C</div>

								<div class="col-md-6 pareto-c">: 0 produk</div>

							</div>

							<div class="row">

								<div class="col-md-6">Total Produk</div>

								<div class="col-md-6 pareto-total">: 0 produk</div>

							</div>

						</div>

					</div>

				</div>

				<div class="row">

					<div class="col-md-12">

						<table class="table table-bordered table-striped table-hover" id="tb_penjualan" width="100%">

		                    <thead>

		                        <tr>

		                            <th width="100%" colspan="16" class="text-center text-white" style="background-color:#455a64;">PENJUALAN</th>

		                        </tr>

		                        <tr>

		                            <th width="5%" class="text-center text-white" style="background-color:#00bcd4;">No</th>

		                            <th width="15%" class="text-center text-white" style="background-color:#00bcd4;">Nama Obat</th>

		                            <th width="15%" class="text-center text-white" style="background-color:#00bcd4;">Penandaan Obat</th>

		                            <th width="5%" class="text-center text-white" style="background-color:#00bcd4;">Jenis</th>

		                            <th width="15%" class="text-center text-white" style="background-color:#00bcd4;">Produsen</th>

		                            <th width="5%" class="text-center text-white" style="background-color:#00bcd4;">Jumlah</th>

		                            <th width="10%" class="text-center text-white" style="background-color:#00acc1;">Penjualan</th>

		                            <th width="10%" class="text-center text-white" style="background-color:#00acc1;">Persentase Penjualan</th>

		                            <th width="5%" class="text-center text-white" style="background-color:#00acc1;">Klasifikasi Penjualan</th>

		                            <th width="10%" class="text-center text-white" style="background-color:#00acc1;">Keuntungan</th>

		                            <th width="10%" class="text-center text-white" style="background-color:#00acc1;">Persentase Keuntungan</th>

		                            <th width="5%" class="text-center text-white" style="background-color:#00acc1;">Klasifikasi Keuntungan</th>

		                            <th width="5%" class="text-center text-white" style="background-color:#00acc1;">Klasifikasi Pareto</th>

									<th width="5%" class="text-center text-white" style="background-color:#00acc1;">Stok Akhir</th>

									<th width="5%" class="text-center text-white" style="background-color:#00acc1;">Sedang Dipesan</th>

									<th width="5%" class="text-center text-white" style="background-color:#00acc1;">Add to Defecta</th>

		                        </tr>

		                    </thead>

		                    <tbody>

		                	</tbody>

		                	<tfoot>

					            <tr>

					                <th colspan="5" style="text-align:right" width="80%">Total / Rata-rata:</th>

					                <th></th>

					                <th></th>

					            </tr>

					        </tfoot>

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
	$(document).ready(function(){

        $("#searching_form").submit(function(e){
            e.preventDefault();
            tb_penjualan.fnDraw(false);

        });

	})
</script>

@endsection