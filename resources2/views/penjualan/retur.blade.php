@extends('layout.app')

@section('title')
Transaksi Penjualan
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Transaksi</a></li>
    <li class="breadcrumb-item"><a href="#">Transaksi Penjualan</a></li>
    <li class="breadcrumb-item active" aria-current="page">Data Retur</li>
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
   				List Data Retur
        	</h3>
      	</div>
        <div class="card-body">
			<table  id="tb_retur" class="table table-bordered table-striped table-hover">
		    	<thead>
			        <tr>
			            <th width="3%" class="text-center">No.</th>
                        <th width="5%" class="text-center">Tanggal</th>
                        <th width="30%" class="text-center">Detail Obat</th>
                        <th width="10%" class="text-center">Kasir</th>
                        <th width="20%" class="text-center">Alasan</th>
                        <th width="10%" class="text-center">Status</th>
                        <th width="10%" class="text-center">Disetujui</th>
                        <th width="12%" class="text-center">Action</th>
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
	var tb_retur = $('#tb_retur').dataTable( {
			processing: true,
	        serverSide: true,
	        stateSave: true,
	        scrollX: true,
	        ajax:{
			        url: '{{url("penjualan/list_retur")}}',
			        data:function(d){
				         }
			     },
	        columns: [
	            {data: 'no', name: 'no',width:"2%", class:'text-center'},
	            {data: 'tanggal', name: 'tanggal', class:'text-center', orderable: true, searchable: true},
	            {data: 'detail_obat', name: 'detail_obat', orderable: false, searchable: false},
	            {data: 'kasir', name: 'kasir', class:'text-center', orderable: false, searchable: false},
	            {data: 'alasan', name: 'alasan', orderable: false, searchable: false},
	            {data: 'status', name: 'status', class:'text-center', orderable: false, searchable: false},
	            {data: 'aprove', name: 'aprove', class:'text-center', orderable: false, searchable: false},
	            {data: 'action', name: 'id',orderable: true, searchable: true, class:'text-center'}
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

	function lihat_detail_retur(id){
		$.ajax({
            type: "GET",
            url: '{{url("penjualan/lihat_detail_retur/")}}/'+id,
            async:true,
            data: {
                  _token:"{{csrf_token()}}"
            },
            beforeSend: function(data){
                $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
                $("#modal-xl .modal-title").html("Detail Retur");
                $('#modal-xl').modal("show");
                $('#modal-xl').find('.modal-body-content').html('');
                $("#modal-xl").find(".overlay").fadeIn("200");
            },
            success:  function(data){
                $('#modal-xl').find('.modal-body-content').html(data);
            },
            complete: function(data){
                $("#modal-xl").find(".overlay").fadeOut("200");
            },
            error: function(data) {
                show_error("error!");
            }
        });
	}
</script>
@endsection