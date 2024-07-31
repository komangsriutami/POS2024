@extends('layout.app')

@section('title')
Harga Obat
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Master Data</a></li>
    <li class="breadcrumb-item"><a href="#">Data Obat</a></li>
    <li class="breadcrumb-item active" aria-current="page">Harga Obat</li>
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
          		<i class="fas fa-star"></i> Detail Obat 
        	</h3>
        	<div class="card-tools">
        		<a href="#" onclick="goBack()" class="btn btn-danger btn-sm pull-right" data-toggle="tooltip" data-placement="top" title="Kembali ke daftar data"><i class="fa fa-undo"></i> Kembali</a>
            </div>
      	</div>
        <div class="card-body">
			<div class="row">
				<div class="col-sm-6">
				    <input type="hidden" name="id_obat" id="id_obat" value="{{ $obat->id }}">
				    <h3 class="m-t-0">Detail Obat</h3>
				    <table width="100%">
				      	<tr>
				         	<td width="13%">ID</td>
				         	<td width="2%"> : </td>
				         	<td width="85">{{ $obat->id }}</td>
				      	</tr>
				      	<tr>
				         	<td width="13%">Barcode</td>
				         	<td width="2%"> : </td>
				         	<td width="85">{{ $obat->barcode }}</td>
				      	</tr>
				      	<tr>
				         	<td width="13%">Nama</td>
				         	<td width="2%"> : </td>
				         	<td width="85">{{ $obat->nama }}</td>
				      	</tr>
				      	<tr>
				         	<td width="13%">Harga Beli</td>
				         	<td width="2%"> : </td>
				         	<td width="85">{{ $stok_harga->harga_beli }}</td>
				      	</tr>
				      	<tr>
				         	<td width="13%">Harga Jual</td>
				         	<td width="2%"> : </td>
				         	<td width="85">{{ $stok_harga->harga_jual }}</td>
				      	</tr>
				   	</table>
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
   				Histori Harga Obat - <b>Apotek {{ $apotek->nama_singkat }}</b>
        	</h3>
      	</div>
        <div class="card-body">
        	<div class="row">
	        	<div class="table-responsive">
	               	<div class="table-responsive">
	                  	<table  id="tb_histori_harga" class="table table-bordered table-striped table-hover" width="100%">
							<thead>
							    <tr>
							        <th width="5%">No.</th>
							        <th width="10%">Tanggal</th>
							        <th width="20%">Detail</th>
							        <th width="10%">HB Awal</th>
							        <th width="10%">HB Akhir</th>
							        <th width="10%">HJ Awal</th>
							        <th width="10%">HJ Akhir</th>
							        <th width="25%">Oleh</th>
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

    <hr>
    <div class="card card-info card-outline" id="main-box" style="">
  		<div class="card-header">
        	<h3 class="card-title">
          		<i class="fas fa-list"></i>
   				HPP Terakhir Seluruh Outlet</b>
        	</h3>
      	</div>
        <div class="card-body">
        	<div class="row">
	        	<div class="table-responsive">
	               	<div class="table-responsive">
	                  	<table  id="tb_hpp" class="table table-bordered table-striped table-hover" width="100%">
							<thead>
							    <tr>
							        <th width="5%" align="center">No.</th>
							        <th width="55%" align="center">Outlet</th>
							        <th width="15%" align="center">HPP</th>
							        <th width="10%" align="center">Margin</th>
							        <th width="15%" align="center">Harga Jual</th>
							        <th width="10%" align="center">Stok</th>
							    </tr>
							</thead>
							<tbody>
								<?php $i = 0; ?>
								@foreach($list_hpp as $obj)
									<?php 
										$i++;
										$hpp = number_format($obj['hpp'], 2); 
										$harga_jual = number_format($obj['harga_jual'], 2); 
									?>
									<tr>
										<td align="center">{{ $i }}</td>
										<td>{{ $obj['nama_panjang'] }}</td>
										<td align="right">Rp {{ $hpp }}</td>
										<td align="center">{{ $obj['margin'] }} %</td>
										<td align="right">Rp {{ $harga_jual }}</td>
										<td align="center">{{ $obj['stok_akhir'] }}</td>
									</tr>
								@endforeach
							</tbody>
						</table>
	               	</div>
	            </div>
	        </div>
        </div>
    </div>
	<!-- @foreach($apoteks as $obj)
		<div class="card card-info card-outline" id="main-box" style="">
	  		<div class="card-header">
	        	<h3 class="card-title">
	          		<i class="fas fa-list"></i>
	   				Histori Harga Obat - Apotek {{ $obj->nama_panjang}}
	        	</h3>
	      	</div>
	        <div class="card-body">
	        	<div class="row">
		        	<div class="table-responsive">
		               	<div class="table-responsive">
		                  	<table  id="tb_histori_harga_{{ $obj->nama_singkat }}" class="table table-bordered table-striped table-hover" width="100%">
								<thead>
								    <tr>
								        <th width="5%">No.</th>
								        <th width="10%">Tanggal</th>
								        <th width="20%">Detail</th>
								        <th width="10%">HB Awal</th>
								        <th width="10%">HB Akhir</th>
								        <th width="10%">HJ Awal</th>
								        <th width="10%">HJ Akhir</th>
								        <th width="25%">Oleh</th>
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
	@endforeach -->
@endsection

@section('script')
<script type="text/javascript">
	var token = '{{csrf_token()}}';

	var tb_histori_harga = $('#tb_histori_harga').dataTable( {
    	processing: true,
        serverSide: true,
        stateSave: true,
        scrollX: true,
        ajax:{
            url: '{{url("data_obat/list_data_histori_harga")}}',
            data:function(d){
              d.id_obat = $('#id_obat').val();
            }
         },
        columns: [
            {data: 'no', name: 'no', orderable: true, searchable: true, class:'text-center'},
            {data: 'created_at', name: 'created_at', orderable: true, searchable: true, class:'text-center'},
            {data: 'id_asal', name: 'id_asal'},
            {data: 'harga_beli_awal', name: 'harga_beli_awal'},
            {data: 'harga_beli_akhir', name: 'harga_beli_akhir', class:'text-center'},
            {data: 'harga_jual_awal', name: 'harga_jual_awal', class:'text-center'},
            {data: 'harga_jual_akhir', name: 'harga_jual_akhir', class:'text-center'},
            {data: 'created_by', name: 'created_by', class:'text-center'}
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
		/*<?php //foreach ($apoteks as $key => $obj) :?>
			var tb_histori_harga_{{$obj->nama_singkat}} = $('#tb_histori_harga_{{$obj->nama_singkat}}').dataTable( {
		    	processing: true,
		        serverSide: true,
		        stateSave: true,
		        scrollX: true,
		        ajax:{
		            url: '{{url("data_obat/list_data_histori_harga")}}',
		            data:function(d){
		              d.id_obat = $('#id_obat').val();
		              d.id_apotek = '{{$obj->id}}';
		            }
		         },
		        columns: [
		            {data: 'no', name: 'no', orderable: true, searchable: true, class:'text-center'},
		            {data: 'created_at', name: 'created_at', orderable: true, searchable: true, class:'text-center'},
		            {data: 'id_asal', name: 'id_asal'},
		            {data: 'harga_beli_awal', name: 'harga_beli_awal'},
		            {data: 'harga_beli_akhir', name: 'harga_beli_akhir', class:'text-center'},
		            {data: 'harga_jual_awal', name: 'harga_jual_awal', class:'text-center'},
		            {data: 'harga_jual_akhir', name: 'harga_jual_akhir', class:'text-center'},
		            {data: 'created_by', name: 'created_by', class:'text-center'}
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
		<?php //endforeach ?>*/
	})

	function goBack() {
       window.history.back();
   }
</script>
@endsection