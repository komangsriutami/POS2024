@extends('layout.app')

@section('title')
Setting Stok Opnam
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">SO</a></li>
    <li class="breadcrumb-item"><a href="#">Setting Stok Opnam</a></li>
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
			<a class="btn btn-success w-md m-b-5" href="{{url('setting_so/create')}}"><i class="fa fa-plus"></i> Tambah Data</a>
	    </div>
	</div>

	<div class="card card-info card-outline" id="main-box" style="">
  		<div class="card-header">
        	<h3 class="card-title">
          		<i class="fas fa-list"></i>
   				List Setting Stok Opnam
        	</h3>
      	</div>
        <div class="card-body">
        	<p class="text-red">Note : Saat tombol "Set Akhir" dijalankan, sistem secara otomatis menSet stok akhir obat 0 pada data yang tidak tercatat saat Stok Opnam berlangsung. </p>
			<table  id="tb_setting_so" class="table table-bordered table-striped table-hover">
		    	<thead>
			        <tr>
			            <th width="5%">No.</th>
			            <th width="15%">Apotek</th>
			            <th width="15%">Tanggal</th>
			            <th width="55%">Action</th>
			            <th width="10%">Action</th>
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
	var tb_setting_so = $('#tb_setting_so').dataTable( {
			processing: true,
	        serverSide: true,
	        stateSave: true,
	        ajax:{
			        url: '{{url("setting_so/list_setting_so")}}',
			        data:function(d){
				         }
			     },
	        columns: [
	            {data: 'no', name: 'no',width:"2%"},
	            {data: 'id_apotek', name: 'id_apotek'},
	            {data: 'tgl_so', name: 'tgl_so', class : 'text-center'},
	            {data: 'step', name: 'step', class : 'text-center'},
	            {data: 'action', name: 'id',orderable: true, searchable: true, class : 'text-center'}
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

	function delete_setting_so(id){
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
				url: '{{url("setting_so")}}/'+id,
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
						swal("Deleted!", "Data setting stok opnam berhasil dihapus.", "success");
					}else{
						
						swal("Failed!", "Gagal menghapus data setting stok opnam.", "error");
					}
				},
				complete: function(data){
					tb_setting_so.fnDraw(false);
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
				url : '{{url("setting_so/")}}/'+id,
				dataType : "json",
				data : data,
				beforeSend: function(data){
					// replace dengan fungsi loading
				},
				success:  function(data){
					if(data.status ==1){
						show_info("Data setting stok opnam berhasil disimpan!");
						$('#modal-large').modal('toggle');
					}else{
						show_error("Gagal menyimpan data ini!");
						return false;
					}
				},
				complete: function(data){
					// replace dengan fungsi mematikan loading
					tb_setting_so.fnDraw(false);
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
	        url: '{{url("setting_so")}}/'+id+'/edit',
	        async:true,
	        data: {
	            _token		: "{{csrf_token()}}",
	        },
	        beforeSend: function(data){
	          	// on_load();
		        $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
		        $("#modal-xl .modal-title").html("Edit Data - Setting Stok Opnam");
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

  	function step_satu(id){
		swal({
		  	title: "Apakah anda ingin melakukan reload data awal ?",
		  	type: "warning",
		  	showCancelButton: true,
		  	confirmButtonColor: "#DD6B55",
		  	confirmButtonText: "Ya",
		  	cancelButtonText: "Tidak",
		  	closeOnConfirm: true
		},
		function(){
			$.ajax({
				type: "GET",
				url: '{{url("setting_so/reload_data_awal/")}}',
				async:true,
				data: {
					_token:token,
					id:id
				},
				beforeSend: function(data){
					// replace dengan fungsi loading
					spinner.show();
				},
				success:  function(data){
					spinner.hide();
					if(data==1){
						swal("Success!", "Reload data awal setting stok opnam berhasil dilakukan.", "success");
					}else{
						
						swal("Failed!", "Gagal reload data awal setting stok opnam.", "error");
					}
				},
				complete: function(data){
					tb_setting_so.fnDraw(false);
				},
				error: function(data) {
					swal("Error!", "Ajax occured.", "error");
				}
			});
		});
	}

	function step_dua(id){
		swal({
		  	title: "Apakah anda ingin melakukan reload data akhir ?",
		  	text: "Reload data ini juga akan membuat histori di kartu stok.",
		  	type: "warning",
		  	showCancelButton: true,
		  	confirmButtonColor: "#DD6B55",
		  	confirmButtonText: "Ya",
		  	cancelButtonText: "Tidak",
		  	closeOnConfirm: true
		},
		function(){
			$.ajax({
				type: "GET",
				url: '{{url("setting_so/reload_data_akhir/")}}',
				async:true,
				data: {
					_token:token,
					id:id
				},
				beforeSend: function(data){
					// replace dengan fungsi loading
					spinner.show();
				},
				success:  function(data){
					spinner.hide();
					if(data==1){
						swal("Success!", "Reload data akhir setting stok opnam berhasil dilakukan.", "success");
					}else if(data == 2){
						swal("Failed!", "Tanggal stok opnam sudah lewat!.", "error");
					} else {
						
						swal("Failed!", "Gagal reload data akhir setting stok opnam.", "error");
					}
				},
				complete: function(data){
					tb_setting_so.fnDraw(false);
				},
				error: function(data) {
					swal("Error!", "Ajax occured.", "error");
				}
			});
		});
	}

	function alert_check(id){
		swal("Warning!", "Step ini tidak aktif (sudah dilewati/harus melewati step aktif).", "error");
	}

	function download_akhir(id){
        window.open("{{ url('setting_so/export') }}"+ "?id="+id,"_blank");
    }

    function backup_data_so(id){
        swal({
		  	title: "Apakah anda yakin melakukan backup data stok opnam ini ?",
		  	text: "Proses backup data ini membutuhkan waktu yag cukup lama, harap bersabar.",
		  	type: "warning",
		  	showCancelButton: true,
		  	confirmButtonColor: "#DD6B55",
		  	confirmButtonText: "Ya",
		  	cancelButtonText: "Tidak",
		  	closeOnConfirm: true
		},
		function(){
			$.ajax({
				type: "GET",
				url: '{{url("setting_so/backup_data_so/")}}',
				async:true,
				data: {
					_token:token,
					id:id
				},
				beforeSend: function(data){
					// replace dengan fungsi loading
					spinner.show();
				},
				success:  function(data){
					spinner.hide();
					if(data==1){
						swal("Success!", "Backup data stok opnam berhasil dilakukan.", "success");
					}else if(data == 2){
						swal("Failed!", "Tanggal stok opnam sudah lewat!.", "error");
					} else {
						
						swal("Failed!", "Gagal melakukan backup data stok opnam.", "error");
					}
				},
				complete: function(data){
					tb_setting_so.fnDraw(false);
				},
				error: function(data) {
					swal("Error!", "Ajax occured.", "error");
				}
			});
		});
    }

    function alert_backup(id){
		swal("Warning!", "Data stok opnam sudah terbackup!", "error");
	}
</script>
@endsection