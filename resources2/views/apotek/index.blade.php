@extends('layout.app')

@section('title')
Data Apotek
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Data Master</a></li>
    <li class="breadcrumb-item"><a href="#">Data Apotek</a></li>
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
			<a class="btn btn-success w-md m-b-5" href="{{url('apotek/create')}}"><i class="fa fa-plus"></i> Tambah Data</a>
	    </div>
	</div>

	<div class="card card-info card-outline" id="main-box" style="">
  		<div class="card-header">
        	<h3 class="card-title">
          		<i class="fas fa-list"></i>
   				List Data Apotek
        	</h3>
      	</div>
        <div class="card-body">
			<table  id="tb_apotek" class="table table-bordered table-striped table-hover">
		    	<thead>
			        <tr>
			            <th>No.</th>
			            <th>Nama</th>
			            <th>Group</th>
			            <th>Alamat</th>
			            <th>Telepon</th>
			            <th>Sync</th>
			            <th>Keterangan</th>
			            <th>Action</th>
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
	var tb_apotek = $('#tb_apotek').dataTable( {
			processing: true,
	        serverSide: true,
	        stateSave: true,
	        ajax:{
			        url: '{{url("apotek/list_apotek")}}',
			        data:function(d){
				         }
			     },
	        columns: [
	            {data: 'no', name: 'no',width:"2%"},
	            {data: 'nama_panjang', name: 'nama_panjang'},
	            {data: 'id_group_apotek', name: 'id_group_apotek'},
	            {data: 'alamat', name: 'alamat'},
	            {data: 'telepon', name: 'telepon'},
	            {data: 'is_sync', name: 'is_sync'},
	            {data: 'sync_count', name: 'sync_count'},
	            {data: 'action', name: 'id',orderable: true, searchable: true}
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

	function delete_apotek(id){
		swal({
		  	title: "Apakah anda yakin menghapus data ini?",
		  	type: "warning",
		  	showCancelButton: true,
		  	confirmButtonColor: "#DD6B55",
		  	confirmButtonText: "Ya",
		  	cancelButtonText: "Tidak",
		  	closeOnConfirm: false
		},
		function(){
			$.ajax({
				type: "DELETE",
				url: '{{url("apotek")}}/'+id,
				async:true,
				data: {
					_token:token,
					id:id
				},
				beforeSend: function(data){
					// replace dengan fungsi loading
				},
				success:  function(data){
					if(data==1){
						swal("Deleted!", "Data Apotek berhasil dihapus.", "success");
					}else{
						
						swal("Failed!", "Gagal menghapus data apotek.", "error");
					}
				},
				complete: function(data){
					tb_apotek.fnDraw(false);
				},
				error: function(data) {
					swal("Error!", "Ajax occured.", "error");
				}
			});
		});
	}

	function submit_valid(id){
		if($(".validated_form").valid()) {
			data = {};
			$("#form-edit").find("input[name], select").each(function (index, node) {
		        data[node.name] = node.value;
    			
		    });

			$.ajax({
				type:"PUT",
				url : '{{url("apotek/")}}/'+id,
				dataType : "json",
				data : data,
				beforeSend: function(data){
					// replace dengan fungsi loading
				},
				success:  function(data){
					if(data.status ==1){
						show_info("Data apotek berhasil disimpan!");
						$('#modal-large').modal('toggle');
					}else{
						show_error("Gagal menyimpan data ini!");
						return false;
					}
				},
				complete: function(data){
					// replace dengan fungsi mematikan loading
					tb_apotek.fnDraw(false);
				},
				error: function(data) {
					show_error("error ajax occured!");
				}

			})
		} else {
			return false;
		}
	}

	function edit_data(id){
      	$.ajax({
          	type: "GET",
	        url: '{{url("apotek")}}/'+id+'/edit',
	        async:true,
	        data: {
	            _token		: "{{csrf_token()}}",
	        },
	        beforeSend: function(data){
	          	// on_load();
		        $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
		        $("#modal-xl .modal-title").html("Edit Data - Apotek");
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
	            alert("error ajax occured!");
	        }

	    });
  	}

  	function export_data_apotek(){
      	$.ajax({
          	cache: false,
          	type: "GET",
          	url: '{{url("apotek/export_data_apotek")}}',
          	data: {
	            _token		: "{{csrf_token()}}",
	            id 			: $('#id_apotek').val(),
	        	nosia 		: $('#no_sia').val(),
	        	apoteker 	: $('#id_apoteker').val()
          	},
          	success: function (response, textStatus, request) {
	            var a = document.createElement("a");
	            a.href = response.file; 
	            a.download = response.name;
	            document.body.appendChild(a);
	            a.click();
	            a.remove();
          	},
          	error: function (ajaxContext) {
           		toastr.error('Export error: '+ajaxContext.responseText);
         	}
    	});
  	}

  	function sync_data_stok_harga(id) {
  		swal({
		  	title: "Apakah anda yakin melakukan sinkronisasi data stok dan harga obat?",
		  	type: "warning",
		  	showCancelButton: true,
		  	confirmButtonColor: "#DD6B55",
		  	confirmButtonText: "Ya",
		  	cancelButtonText: "Tidak",
		  	closeOnConfirm: false
		},
		function(){
			$.ajax({
				type: "POST",
				url : '{{url("apotek/sync_data_stok_harga")}}',
				async:true,
				data: {
					_token:token,
					id:id
				},
				beforeSend: function(data){
					// replace dengan fungsi loading
				},
				success:  function(data){
					if(data==1){
						swal("Success!", "Data stok dan harga obat pada apotek ini berhasil disinkronisasi.", "success");
					} else if(data==2) {
						swal("Alert!", "Tidak ada data obat baru yang dapat disesuaikan.", "error");
					} else {
						swal("Failed!", "Gagal melakukan sinkronisasi data stok dan harga pada apotek ini.", "error");
					}
				},
				complete: function(data){
					tb_apotek.fnDraw(false);
				},
				error: function(data) {
					swal("Error!", "Ajax occured.", "error");
				}
			});
		});
  	}

  	function add_table_stok_harga(id) {
  		swal({
		  	title: "Apakah anda yakin membuat tabel stok dan harga untuk apotek ini?",
		  	type: "warning",
		  	showCancelButton: true,
		  	confirmButtonColor: "#DD6B55",
		  	confirmButtonText: "Ya",
		  	cancelButtonText: "Tidak",
		  	closeOnConfirm: false
		},
		function(){
			$.ajax({
				type: "POST",
				url : '{{url("apotek/add_table_stok_harga")}}',
				async:true,
				data: {
					_token:token,
					id:id
				},
				beforeSend: function(data){
					// replace dengan fungsi loading
				},
				success:  function(data){
					if(data==1){
						swal("Success!", "Tabel stok dan harga obat pada apotek ini telah dibuat.", "success");
					} else {
						swal("Failed!", "Gagal membuat tabel data stok dan harga pada apotek ini.", "error");
					}
				},
				complete: function(data){
					tb_apotek.fnDraw(false);
				},
				error: function(data) {
					swal("Error!", "Ajax occured.", "error");
				}
			});
		});
  	}
</script>
@endsection