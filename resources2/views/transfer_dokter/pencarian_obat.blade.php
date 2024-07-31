@extends('layout.app')

@section('title')
Histori Transfer Obat ke Dokter
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Transaksi Transfers Dokter</a></li>
    <li class="breadcrumb-item"><a href="#">Histori Transfer Obat</a></li>
    <li class="breadcrumb-item active" aria-current="page">Index</li>
</ol>
@endsection

@section('content')
	<style type="text/css">
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
   				List Data Transfer Obat
        	</h3>
      	</div>
        <div class="card-body">
			<table  id="tb_histori_item_transfer" class="table table-bordered table-striped table-hover">
		    	<thead>
			        <tr>
			            <th width="3%" class="text-center">No.</th>
			            <th width="5%" class="text-center">ID Nota</th>
			            <th width="17%" class="text-center">Tanggal</th>
			            <th width="10%" class="text-center">Kasir</th>
			            <th width="40%" class="text-center">Nama Obat</th>
			            <th width="25%" class="text-center">Total</th>
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
	var tb_histori_item_transfer = $('#tb_histori_item_transfer').dataTable( {
			processing: true,
	        serverSide: true,
	        stateSave: true,
	        ajax:{
			        url: '{{url("transfer_dokter/list_pencarian_obat")}}',
			        data:function(d){
				         }
			     },
	        columns: [
	            {data: 'no', name: 'no',width:"2%", class:'text-center'},
	            {data: 'id', name: 'id', class:'text-center'},
	            {data: 'created_at', name: 'created_at', class:'text-center'},
	            {data: 'created_by', name: 'created_by'},
	            {data: 'id_obat', name: 'id_obat'},
	            {data: 'total', name: 'total', orderable: false, searchable: true}
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
	})
</script>
@endsection