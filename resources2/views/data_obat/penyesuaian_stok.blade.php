@extends('layout.app')

@section('title')
Stok Obat
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Master Data</a></li>
    <li class="breadcrumb-item"><a href="#">Data Obat</a></li>
    <li class="breadcrumb-item active" aria-current="page">Stok Obat</li>
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
            <a href="{{ url('penyesuaian_stok/create/'.$obat->id) }}" onclick="#" class="btn btn-info btn-sm pull-right" data-toggle="tooltip" data-placement="top" title="Tambah Data"><i class="fa fa-plus"></i> Tambah</a>
        		<a href="{{ url('data_obat') }}" onclick="#" class="btn btn-danger btn-sm pull-right" data-toggle="tooltip" data-placement="top" title="Kembali ke daftar data"><i class="fa fa-undo"></i> Kembali</a>
            </div>
      	</div>
        <div class="card-body">
			<div class="row">
	            <div class="col-sm-6">
	                <input type="hidden" name="id_obat" id="id_obat" value="{{ $obat->id }}">
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
	                      <td width="13%">Stok</td>
	                      <td width="2%"> : </td>
	                      <td width="85">{{ $stok_harga->stok_akhir }}</td>
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
   				Histori Penyesuaian Stok Obat
        	</h3>
      	</div>
        <div class="card-body">
        	<div class="row">
	        	<div class="table-responsive">
	               	<div class="table-responsive">
	                  	<table  id="tb_penyesuaian_stok_obat" class="table table-bordered table-striped table-hover">
	                     	<thead>
	                        	<tr>
	                           		<th width="5%">No.</th>
	                           		<th width="10%">Tanggal</th>
	                           		<th width="10%">Stok Awal</th>
	                           		<th width="10%">Stok Akhir</th>
                                <th width="50%">Alasan</th>
	                           		<th width="15%">Oleh</th>
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
@endsection

@section('script')
<script type="text/javascript">
	var token = '{{csrf_token()}}';

	var tb_penyesuaian_stok_obat = $('#tb_penyesuaian_stok_obat').dataTable( {
           processing: true,
          serverSide: true,
          stateSave: true,
          scrollX: true,
           ajax:{
               url: '{{url("data_obat/list_data_penyesuaian_stok_obat")}}',
               data:function(d){
                 d.id_obat = $('#id_obat').val();
               }
            },
           columns: [
               {data: 'no', name: 'no', orderable: true, searchable: true, class:'text-center'},
               {data: 'created_at', name: 'created_at', orderable: true, searchable: true, class:'text-center'},
               {data: 'stok_awal', name: 'stok_awal', class:'text-center'},
               {data: 'stok_akhir', name: 'stok_akhir',  class:'text-center'},
               {data: 'alasan', name: 'alasan',  class:'text-center'},
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
	})

	function goBack() {
       window.history.back();
   }
</script>
@endsection