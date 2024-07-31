@extends('layout.app')

@section('title')
Permission
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Rbac</a></li>
    <li class="breadcrumb-item"><a href="#">Permission</a></li>
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
			<a class="btn btn-success w-md m-b-5" href="{{url('permission/reload_permission')}}"><i class="fa fa-sync-alt"></i> Reload</a>
	    </div>
	</div>

	<div class="card card-info card-outline" id="main-box" style="">
  		<div class="card-header">
        	<h3 class="card-title">
          		<i class="fas fa-list"></i>
   				List Permission
        	</h3>
      	</div>
        <div class="card-body">
			<table  id="tb_permission" class="table table-bordered table-striped table-hover">
		    	<thead>
			        <tr>
			            <th width="5%">No.</th>
			            <th width="20%">Menu</th>
			            <th width="20%">Nama</th>
			            <th width="20%">Uri</th>
			            <th width="20%">Method</th>
			            <th width="15%">Group</th>
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
	var tb_permission = $('#tb_permission').DataTable( {
		paging:true,
		destroy: true,
        autoWidth: false,
        processing: true,
        serverSide: true,
        ajax: {
            "url" : '{{url("permission/list_permission")}}',
            "type" : "GET",
            "data" : function (d) {
            }
        },
        order: [],
        columns: [
         	{data: 'DT_RowIndex', name: 'DT_RowIndex',width:"2%"},
            {data: 'menu', name: 'menu'},
            {data: 'nama', name: 'nama'},
            {data: 'uri', name: 'uri'},
            {data: 'method', name: 'method'},
            {data: 'group', name: 'group'},
            //{data: 'action', name: 'id',orderable: false, searchable: false, class:'text-center'}
        ],
        drawCallback: function(callback) {
        }
	});

	$(document).ready(function(){
	})
</script>
@endsection