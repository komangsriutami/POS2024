@extends('layout.app')

@section('title')
Laporan
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Laporan</a></li>
    <li class="breadcrumb-item active" aria-current="page">Index</li>
</ol>
@endsection

@section('content')
	<style type="text/css">
		.select2 {
		  width: 100%!important; /* overrides computed width, 100px in your demo */
		}

		.bg-closing {
			background-color: #cfd8dc;
		}
	</style>

	<div class="card card-secondary card-outline">
	    <!-- <div class="card-header">
	        <h3 class="card-title">
	            <i class="fas fa-edit"></i>
	            Tabs Custom Content Examples
	        </h3>
	    </div> -->
	    <div class="card-body">
	        <!-- <h4>Custom Content Below</h4> -->
	        <ul class="nav nav-tabs" id="custom-content-below-tab" role="tablist">
	            <li class="nav-item">
	                <a class="nav-link active" id="custom-content-below-home-tab" data-toggle="pill" href="#custom-content-below-home" role="tab" aria-controls="custom-content-below-home" aria-selected="true">Sekilas Bisnis</a>
	            </li>
	            <!-- <li class="nav-item">
	                <a class="nav-link" id="custom-content-below-penjualan-tab" data-toggle="pill" href="#custom-content-below-penjualan" role="tab" aria-controls="custom-content-below-penjualan" aria-selected="false">Penjualan</a>
	            </li>
	            <li class="nav-item">
	                <a class="nav-link" id="custom-content-below-pembelian-tab" data-toggle="pill" href="#custom-content-below-pembelian" role="tab" aria-controls="custom-content-below-pembelian" aria-selected="false">Pembelian</a>
	            </li>
	            <li class="nav-item">
	                <a class="nav-link" id="custom-content-below-produk-tab" data-toggle="pill" href="#custom-content-below-produk" role="tab" aria-controls="custom-content-below-produk" aria-selected="false">Produk</a>
	            </li>
	            <li class="nav-item">
	                <a class="nav-link" id="custom-content-below-aset-tab" data-toggle="pill" href="#custom-content-below-aset" role="tab" aria-controls="custom-content-below-aset" aria-selected="false">Aset</a>
	            </li>
	            <li class="nav-item">
	                <a class="nav-link" id="custom-content-below-bank-tab" data-toggle="pill" href="#custom-content-below-bank" role="tab" aria-controls="custom-content-below-bank" aria-selected="false">Bank</a>
	            </li>
	            <li class="nav-item">
	                <a class="nav-link" id="custom-content-below-pajak-tab" data-toggle="pill" href="#custom-content-below-pajak" role="tab" aria-controls="custom-content-below-pajak" aria-selected="false">Pajak</a>
	            </li> -->
	        </ul>
	        <div class="tab-content" id="custom-content-below-tabContent">
	            <div class="tab-pane fade active show" id="custom-content-below-home" role="tabpanel" aria-labelledby="custom-content-below-home-tab">
	            	@include('laporan._form_home')
	            </div>
	            <!-- <div class="tab-pane fade" id="custom-content-below-penjualan" role="tabpanel" aria-labelledby="custom-content-below-penjualan-tab">
	            	@include('laporan._form_penjualan')
	            </div>
	            <div class="tab-pane fade" id="custom-content-below-pembelian" role="tabpanel" aria-labelledby="custom-content-below-pembelian-tab">
	                @include('laporan._form_pembelian')
	            </div>
	            <div class="tab-pane fade" id="custom-content-below-produk" role="tabpanel" aria-labelledby="custom-content-below-produk-tab">
	                @include('laporan._form_produk')
	            </div>
	            <div class="tab-pane fade" id="custom-content-below-aset" role="tabpanel" aria-labelledby="custom-content-below-aset-tab">
	                @include('laporan._form_aset')
	            </div>
	            <div class="tab-pane fade" id="custom-content-below-bank" role="tabpanel" aria-labelledby="custom-content-below-bank-tab">
	                @include('laporan._form_bank')
	            </div>
	            <div class="tab-pane fade" id="custom-content-below-pajak" role="tabpanel" aria-labelledby="custom-content-below-pajak-tab">
	                @include('laporan._form_pajak')
	            </div> -->
	        </div>
	    </div>
	    <!-- /.card -->
	</div>
@endsection

@section('script')
<script type="text/javascript">
	var token = '{{csrf_token()}}';

	$(document).ready(function(){
	})
</script>
</script>
@endsection