@extends('layout.app')

@section('title')
Data Obat
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Data Master</a></li>
    <li class="breadcrumb-item"><a href="#">Data Obat</a></li>
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
			<a class="btn btn-success w-md m-b-5" href="{{url('obat/create')}}"><i class="fa fa-plus"></i> Tambah Data</a>
	    </div>
	</div>

	<div class="card card-info card-outline" id="main-box">
  		<div class="card-header">
        	<h3 class="card-title">
          		<i class="fas fa-list"></i>
   				List Data Obat
        	</h3>
      	</div>
        <div class="card-body">
        	<form role="form" id="searching_form">
                <!-- text input -->
                <div class="row">
				    <div class="form-group col-md-3">
				        {!! Form::label('id_penandaan_obat', 'Pilih Penandaan Obat') !!}
				        {!! Form::select('id_penandaan_obat', $penandaan_obats, null, ['class' => 'form-control input_select']) !!}
				    </div>
				    <div class="form-group col-md-3">
				        {!! Form::label('id_golongan_obat', 'Pilih Golongan Penandaan Diskon') !!}
				        {!! Form::select('id_golongan_obat', $golongan_obats, null, ['class' => 'form-control input_select']) !!}
				    </div>
				    <div class="form-group col-md-3">
				        {!! Form::label('id_produsen', 'Pilih Produsen') !!}
				        {!! Form::select('id_produsen', $produsens, null, ['class' => 'form-control input_select']) !!}
				    </div>
                    <div class="col-lg-12" style="text-align: center;">
                        <button type="submit" class="btn btn-primary" id="datatable_filter"><i class="fa fa-search"></i> Cari</button>
                        <span class="btn bg-olive" onClick="export_data()"  data-toggle="modal" data-placement="top" title="Export Data Transfer"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export</span> 
                    </div>
                </div>
            </form>
			<hr>
			<table  id="tb_obat" class="table table-bordered table-striped table-hover">
		    	<thead>
			        <tr>
			            <th width="5%">No.</th>
			            <th width="10%">Barcode</th>
			            <th width="25%">Nama</th>
			            <th width="10%">Produsen</th>
			            <th width="10%">Golongan Obat</th>
			            <!-- <th width="5%">Isi</th> -->
			            <!-- <th width="5%">Rak</th> -->
			            <th width="10%">Penandaan Obat</th>
			            <th width="5%">HB</th>
			            <th width="5%">HJ</th>
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

	var tb_obat = $('#tb_obat').dataTable( {
			processing: true,
	        serverSide: true,
	        stateSave: true,
	        ajax:{
			        url: '{{url("obat/list_obat")}}',
			        data:function(d){
			        	d.id_penandaan_obat = $("#id_penandaan_obat").val();
			        	d.id_golongan_obat = $("#id_golongan_obat").val();
			        	d.id_produsen = $("#id_produsen").val();
			        }
			     },
	        columns: [
	            {data: 'no', name: 'no', orderable: true, searchable: true, class:'text-center'},
	            {data: 'barcode', name: 'barcode', orderable: true, searchable: true, class:'text-center'},
	            {data: 'nama', name: 'nama', orderable: true, searchable: true},
	            {data: 'id_produsen', name: 'id_produsen', orderable: true, searchable: true},
	            {data: 'id_golongan_obat', name: 'id_golongan_obat', orderable: true, searchable: true},
	            {data: 'id_penandaan_obat', name: 'id_penandaan_obat', orderable: true, searchable: true},
	            {data: 'harga_beli', name: 'harga_beli', orderable: true, searchable: true, class:'text-center'},
	            {data: 'harga_jual', name: 'harga_jual', orderable: true, searchable: true, class:'text-center'},
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
		$("#searching_form").submit(function(e){
			e.preventDefault();
			tb_obat.fnDraw(false);
		});

        $('.input_select').select2({});
	})

	function delete_obat(id){
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
				url: '{{url("obat")}}/'+id,
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
						swal("Deleted!", "Data obat berhasil dihapus.", "success");
					}else{
						
						swal("Failed!", "Gagal menghapus data obat.", "error");
					}
				},
				complete: function(data){
					tb_obat.fnDraw(false);
				},
				error: function(data) {
					swal("Error!", "Ajax occured.", "error");
				}
			});
		});
	}

	function submit_valid(id){
		status = $(".validated_form").valid();

		if(status) {
			data = {};
			$("#form-edit").find("input[name], select, textarea").each(function (index, node) {
		        data[node.name] = node.value;
    			
		    });

			$.ajax({
				type:"PUT",
				url : '{{url("obat/")}}/'+id,
				dataType : "json",
				data : data,
				beforeSend: function(data){
					// replace dengan fungsi loading
				},
				success:  function(data){
					if(data.status ==1){
						show_info("Data obat berhasil disimpan...");
						$('#modal-large').modal('toggle');
					}else{
						show_error("Gagal menyimpan data ini!");
						return false;
					}
				},
				complete: function(data){
					// replace dengan fungsi mematikan loading
					tb_obat.fnDraw(false);
				},
				error: function(data) {
					show_error("error ajax occured!");
				}

			})
		}
	}

	function edit_data(id){
      	$.ajax({
          	type: "GET",
	        url: '{{url("obat")}}/'+id+'/edit',
	        async:true,
	        data: {
	            _token		: "{{csrf_token()}}",
	        },
	        beforeSend: function(data){
	          	// on_load();
		        $('#modal-xl').find('.modal-xls').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
		        $("#modal-xl .modal-title").html("Edit Data - Obat");
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

  	function export_data(){
        window.open("{{ url('obat/export_data') }}"+ "?id_penandaan_obat="+$('#id_penandaan_obat').val()+"&id_golongan_obat="+$('#id_golongan_obat').val(),"_blank");
    }

    function sync_outlet(id){
		swal({
		  	title: "Apakah anda yakin melakukan sinronisasi ke seluruh outlet?",
		  	type: "warning",
		  	showCancelButton: true,
		  	confirmButtonColor: "#DD6B55",
		  	confirmButtonText: "Ya",
		  	cancelButtonText: "Tidak",
		  	closeOnConfirm: false
		},
		function(){
			$.ajax({
				type: "GET",
				url: '{{url("obat/sync_obat_outlet/")}}/'+id,
				async:true,
				data: {
					_token:token,
				},
				beforeSend: function(data){
					// replace dengan fungsi loading
					spinner.show();
				},
				success:  function(data){
					spinner.hide();
					if(data==1){
						swal("Success!", "Data obat oulet berhasil disesuaikan!.", "success");
					}else{
						swal("Alert!", "Data obat oulet gagal disesuaikan!.", "error");
					}
				},
				complete: function(data){
					tb_obat.fnDraw(false);
				},
				error: function(data) {
					swal("Error!", "Ajax occured.", "error");
				}
			});
		});
	}
</script>
@endsection