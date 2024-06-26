@extends('layout.app')

@section('title')
Data Setting Suplier
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Data Master</a></li>
    <li class="breadcrumb-item"><a href="#">Data Setting Suplier</a></li>
    <li class="breadcrumb-item active" aria-current="page">Index</li>
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
          		<i class="fas fa-list"></i>
   				List Data Setting Suplier
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
				        {!! Form::label('id_golongan_obat', 'Pilih Golongan Penandaan Obat') !!}
				        {!! Form::select('id_golongan_obat', $golongan_obats, null, ['class' => 'form-control input_select']) !!}
				    </div>
				    <div class="form-group col-md-3">
				        {!! Form::label('id_produsen', 'Pilih Produsen') !!}
				        {!! Form::select('id_produsen', $produsens, null, ['class' => 'form-control input_select']) !!}
				    </div>
                    <div class="col-lg-12" style="text-align: center;">
                        <button type="submit" class="btn btn-primary" id="datatable_filter"><i class="fa fa-search"></i> Cari</button>
                       <!--  <span class="btn bg-olive" onClick="export_data()"  data-toggle="modal" data-placement="top" title="Export Data Transfer"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export</span>  -->
                    </div>
                </div>
            </form>
			<hr>
			<table  id="tb_setting_suplier" class="table table-bordered table-striped table-hover">
		    	<thead>
			        <tr>
			            <th width="3%">No.</th>
			            <th width="8%">Barcode</th>
			            <th width="25%">Nama</th>
			            <th width="10%">Produsen</th>
			            <th width="10%">Golongan Obat</th>
			            <th width="10%">Penandaan Obat</th>
			            <th width="16%">Setting</th>
			            <th width="8%">Action</th>
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

	var tb_setting_suplier = $('#tb_setting_suplier').dataTable( {
			processing: true,
	        serverSide: true,
	        stateSave: true,
	        ajax:{
			        url: '{{url("setting_suplier/list_setting_suplier")}}',
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
	            {data: 'setting', name: 'setting', orderable: true, searchable: true},
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
			tb_setting_suplier.fnDraw(false);
		});

        $('.input_select').select2({});
	})

	
	function add_detail(id){
        $.ajax({
            type: "GET",
            url: '{{url("setting_suplier/add_detail/")}}/'+id,
            async:true,
            data: {
                _token      : "{{csrf_token()}}",
            },
            beforeSend: function(data){
                // on_load();
                $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
                $("#modal-xl .modal-title").html("Setting Suplier");
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

    function submit_valid_detail(jenis, id){
        var id_obat = $("#id_obat").val();
        var id_suplier = $("#id_suplier").val();
        var level = $("#level").val();

        if(jenis == 1) {
            status = $(".validated_form").valid();

            if(status) {
                var myformData = new FormData();        
                myformData.append('id_obat', id_obat);
                myformData.append('id_suplier', id_suplier);
                myformData.append('level', level);
                myformData.append('_token', token);
                $.ajax({
                   url: '{{url("setting_suplier/storedetail")}}',
                   type: 'POST',
                   data: myformData,
                   processData: false,
                   contentType: false,
                   enctype: 'multipart/form-data',
                   success: function(data) {
                        if(data ==1){
                            tb_setting_suplier.fnDraw(false);
                            show_info("Data setting suplier berhasil disimpan.");
                            $('#modal-xl').modal('toggle');
                        }else{
                            show_error("Data setting suplier gagal disimpan.");
                            return false;
                        }
                   },
                   error: function(data) {
                        show_error("error ajax occured!");
                    }
                });
            }
        } else {
            status = $(".validated_form").valid();

            if(status) {
                var myformData = new FormData();        
                myformData.append('id_obat', id_obat);
                myformData.append('id_suplier', id_suplier);
                myformData.append('level', level);
                myformData.append('_token', token);
                $.ajax({
                   url: '{{url("setting_suplier/updatedetail")}}/'+id,
                   type: 'POST',
                   data: myformData,
                   processData: false,
                   contentType: false,
                   enctype: 'multipart/form-data',
                   success: function(data) {
                        if(data ==1){
                            tb_setting_suplier.fnDraw(false);
                            show_info("Data setting suplier berhasil disimpan.");
                            $('#modal-xl').modal('toggle');
                        }else{
                            show_error("Data setting suplier gagal disimpan.");
                            return false;
                        }
                   },
                   error: function(data) {
                        show_error("error ajax occured!");
                    }
                });
            }
        }
    }

    function edit_detail(id){
        $.ajax({
            type: "GET",
            url: '{{url("setting_suplier/edit_detail/")}}/'+id,
            async:true,
            data: {
                _token      : "{{csrf_token()}}",
            },
            beforeSend: function(data){
                // on_load();
                $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
                $("#modal-xl .modal-title").html("Edit Detail Data - Setting Suplier");
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

    function delete_detail(id){
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
                type: "GET",
                url: '{{url("setting_suplier/deletedetail")}}/'+id,
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
                        swal("Deleted!", "Data setting suplier berhasil dihapus.", "success");
                    }else{
                        
                        swal("Failed!", "Gagal menghapus data setting suplier.", "error");
                    }
                },
                complete: function(data){
                    tb_setting_suplier.fnDraw(false);
                },
                error: function(data) {
                    swal("Error!", "Ajax occured.", "error");
                }
            });
        });
    }
</script>
@endsection