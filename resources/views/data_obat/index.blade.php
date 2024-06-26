@extends('layout.app')

@section('title')
Data Obat
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Data</a></li>
    <li class="breadcrumb-item active" aria-current="page">Data Obat</li>
</ol>
@endsection

@section('content')
	<style type="text/css">
		.select2 {
		  width: 100%!important; /* overrides computed width, 100px in your demo */
		}
	</style>

	<style>
		.switch {
		  position: relative;
		  display: inline-block;
		  width: 45px;
		  height: 26px;
		}

		.switch input {display:none;}

		.slider {
		  position: absolute;
		  cursor: pointer;
		  top: 0;
		  left: 0;
		  right: 0;
		  bottom: 0;
		  background-color: #ccc;
		  -webkit-transition: .4s;
		  transition: .4s;
		}

		.slider:before {
		  position: absolute;
		  content: "";
		  height: 18px;
		  width: 18px;
		  left: 4px;
		  bottom: 4px;
		  background-color: white;
		  -webkit-transition: .4s;
		  transition: .4s;
		}

		input:checked + .slider {
		  background-color: #2196F3;
		}

		input:focus + .slider {
		  box-shadow: 0 0 1px #2196F3;
		}

		input:checked + .slider:before {
		  -webkit-transform: translateX(19px);
		  -ms-transform: translateX(19px);
		  transform: translateX(19px);
		}

		/* Rounded sliders */
		.slider.round {
		  border-radius: 22px;
		}

		.slider.round:before {
		  border-radius: 50%;
		}
	</style>

	<div class="card card-info card-outline mb-12 border-left-primary">
	    <div class="card-body">
	      	<h4><i class="fa fa-info"></i> Informasi</h4>
	      	<p>Untuk pencarian, isikan kata yang ingin dicari pada kolom seacrh, lalu tekan enter.</p>
	      	<?php
	      		$hak_akses = 0;
	      		$id_user = Auth::user()->id;
	      		if($id_user == 1 || $id_user == 2 || $id_user == 16) {
	      			$hak_akses = 1;
	      		}
	      	?>
	      	@if($hak_akses == 1)
	      		<a class="btn btn-info" href="#" onClick="template_data()"><i class="fa fa-cloud-download"></i> Template Data HJ Statik</a>
				<!-- <a class="btn bg-maroon" href="#" onClick="import_data()"><i class="fa fa-cloud-upload"></i> Import Data HJ Statik</a> -->
	      		<!-- <a class="btn btn w-md m-b-5" href="#" onclick="sycn_harga_obat_tahap_satu()" style="background-color: #00838f; color:#fff;"><i class="fa fa-refresh"></i> Histori Harga 1</a>
	      		<a class="btn btn w-md m-b-5" href="#" onclick="sycn_harga_obat_tahap_dua()" style="background-color: #00838f; color:#fff;"><i class="fa fa-refresh"></i> Histori Harga 2</a> -->
			@endif

	      	@if($hak_akses == 1)
				<a class="btn btn w-md m-b-5" href="#" onclick="sycn_harga_obat_all()" style="background-color: #00838f; color:#fff;"><i class="fa fa-refresh"></i> Sync All Harga</a>
				<a class="btn w-md m-b-5" href="{{ url('data_obat/data_penyesuaian_stok')}} " onclick="#" style="background-color: #ad1457; color:#fff;"><i class="fa fa-file-excel-o"></i> Export Data Penyesuaian Stok</a>
			@endif
	        <a class="btn w-md m-b-5" href="#" onclick="export_data_obat()" style="background-color: #ad1457; color:#fff;"><i class="fa fa-file-excel-o"></i> Export Data</a>
	        
	    </div>
	</div>
	<div class="col-md-12">
		<div class="callout callout-info">
			<p class="text-red">Static Harga (SH) : diaktifkan jika harga outlet tidak mengikuti harga group.</p>
        </div>
	</div>
	<div class="card card-info card-outline" id="main-box" style="">
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
			<table  id="tb_data_obat" class="table table-bordered table-striped table-hover">
		    	<thead>
			        <tr>
			            <th width="5%">No.</th>
			            <th width="10%">Barcode</th>
			            <th width="22%">Nama</th>
			            <th width="5%">Isi</th>
			            <th width="4%">Margin</th>
			            <th width="7%">HB+ppn (last)</th>
			            <th width="7%">HB+ppn avg</th>
			            <th width="7%">HJ</th>
			            <th width="4%">Stok</th>
			            <th width="4%">SH</th>
			            <th width="5%">S</th>
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

	var tb_data_obat = $('#tb_data_obat').dataTable( {
			processing: true,
	        serverSide: true,
	        stateSave: true,
	        scrollX: true,
	        ajax:{
			        url: '{{url("data_obat/list_data_obat")}}',
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
	            {data: 'isi_tab', name: 'isi_tab', orderable: true, searchable: true, class:'text-center'},
	            {data: 'untung_jual', name: 'untung_jual', orderable: true, searchable: true, class:'text-center'},
	            {data: 'harga_beli_ppn', name: 'harga_beli_ppn', orderable: true, searchable: true, class:'text-center'},
	            {data: 'hb_ppn_avg', name: 'hb_ppn_avg', orderable: true, searchable: true, class:'text-center'},
	            {data: 'harga_jual', name: 'harga_jual', orderable: true, searchable: true, class:'text-center'},
	            {data: 'stok_akhir', name: 'stok_akhir', orderable: true, searchable: true, class:'text-center'},
	            {data: 'is_status_harga', name: 'is_status_harga', orderable: true, searchable: true, class:'text-center'},
	            {data: 'is_disabled', name: 'is_disabled', orderable: true, searchable: true, class:'text-center'},
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
			tb_data_obat.fnDraw(false);
		});

        $('.input_select').select2({});
	})

	function sycn_harga_obat(id){
		swal({
		  	title: "Apakah anda yakin melakukan sinkronisasi harga data obat ini?",
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
				url: '{{url("data_obat/sycn_harga_obat")}}',
				async:true,
				data: {
					_token:token,
					id:id,
				},
				beforeSend: function(data){
					// replace dengan fungsi loading
				},
				success:  function(data){
					if(data==1){
						swal("Success!", "Data harga obat telah disinkronisasi.", "success");
					}else{
						swal("Alert!", "Harga obat sudah sama.", "error");
					}
				},
				complete: function(data){
					tb_data_obat.fnDraw(false);
				},
				error: function(data) {
					swal("Error!", "Ajax occured.", "error");
				}
			});
		});
	}

  	function sycn_harga_obat_all(id){
		swal({
		  	title: "Apakah anda yakin melakukan sinkronisasi harga seluruh data obat?",
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
				url: '{{url("data_obat/sycn_harga_obat_all")}}',
				async:true,
				data: {
					_token:token,
				},
				beforeSend: function(data){
					// replace dengan fungsi loading
				},
				success:  function(data){
					if(data==1){
						swal("Success!", "Data harga obat telah disinkronisasi.", "success");
					}else{
						swal("Alert!", "Tidak ada data harga baru.", "error");
					}
				},
				complete: function(data){
					tb_data_obat.fnDraw(false);
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
			$("#form-edit").find("input[name], select").each(function (index, node) {
		        data[node.name] = node.value;
    			
		    });

			$.ajax({
				type:"PUT",
				url : '{{url("data_obat/")}}/'+id,
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
					tb_data_obat.fnDraw(false);
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
	        url: '{{url("data_obat")}}/'+id+'/edit',
	        async:true,
	        data: {
	            _token		: "{{csrf_token()}}",
	        },
	        beforeSend: function(data){
	          	// on_load();
		        $('#modal-xl').find('.modal-lg').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
		        $("#modal-xl .modal-title").html("Edit Data - Obat");
		        $('#modal-xl').modal("show");
		        $('#modal-xl').find('.modal-body-content').html('');
		        $("#modal-xl").find(".overlay").fadeIn("200");
	        },
	        	success:  function(data){
	          	$('#modal-xl').find('.modal-body-content').html(data);
	        },
	        	complete: function(data){
	            $("#modal-lx").find(".overlay").fadeOut("200");
	        },
	          	error: function(data) {
	            alert("error ajax occured!");
	        }

	    });
  	}

  	function disabled_obat(id){
		swal({
		  	title: "Apakah anda yakin untuk menon-aktifkan obat data obat ini?",
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
				url: '{{url("data_obat/disabled_obat")}}',
				async:true,
				data: {
					_token:token,
					id:id,
				},
				beforeSend: function(data){
					// replace dengan fungsi loading
				},
				success:  function(data){
					if(data==1){
						swal("Success!", "Data obat telah dinon-aktifkan.", "success");
					}else{
						swal("Gagal!", "Data obat gagal dinon-aktifkan.", "error");
					}
				},
				complete: function(data){
					tb_data_obat.fnDraw(false);
				},
				error: function(data) {
					swal("Error!", "Ajax occured.", "error");
				}
			});
		});
	}

	function disabled_obat(id){
		swal({
		  	title: "Apakah anda yakin untuk menon-aktifkan obat data obat ini?",
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
				url: '{{url("data_obat/disabled_obat")}}',
				async:true,
				data: {
					_token:token,
					id:id,
				},
				beforeSend: function(data){
					// replace dengan fungsi loading
				},
				success:  function(data){
					if(data==1){
						swal("Success!", "Data obat telah dinon-aktifkan.", "success");
					}else{
						swal("Gagal!", "Data obat gagal dinon-aktifkan.", "error");
					}
				},
				complete: function(data){
					tb_data_obat.fnDraw(false);
				},
				error: function(data) {
					swal("Error!", "Ajax occured.", "error");
				}
			});
		});
	}

	function export_data_obat(){
        window.open("{{ url('data_obat/export') }}","_blank");
    }

    function export_data(){
        window.open("{{ url('data_obat/export_data') }}?id_penandaan_obat="+$("#id_penandaan_obat").val()+"&id_golongan_obat="+$("#id_golongan_obat").val()+"&id_produsen="+$("#id_produsen").val(),"_blank");
    }

    function sycn_harga_obat_tahap_satu(id){
		swal({
		  	title: "Apakah anda yakin melakukan reload harga seluruh data obat?",
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
				url: '{{url("data_obat/sycn_harga_obat_tahap_satu")}}',
				async:true,
				data: {
					_token:token,
				},
				beforeSend: function(data){
					// replace dengan fungsi loading
					spinner.show();
				},
				success:  function(data){
					if(data==1){
						swal("Success!", "Data harga obat telah disinkronisasi.", "success");
					}else{
						swal("Alert!", "Tidak ada data harga baru.", "error");
					}
				},
				complete: function(data){
					spinner.hide();
					tb_data_obat.fnDraw(false);
				},
				error: function(data) {
					swal("Error!", "Ajax occured.", "error");
				}
			});
		});
	}

	function sycn_harga_obat_tahap_dua(id){
		swal({
		  	title: "Apakah anda yakin melakukan reload harga seluruh data obat?",
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
				url: '{{url("data_obat/sycn_harga_obat_tahap_dua")}}',
				async:true,
				data: {
					_token:token,
				},
				beforeSend: function(data){
					// replace dengan fungsi loading
				},
				success:  function(data){
					if(data==1){
						swal("Success!", "Data harga obat telah disinkronisasi.", "success");
					}else{
						swal("Alert!", "Tidak ada data harga baru.", "error");
					}
				},
				complete: function(data){
					tb_data_obat.fnDraw(false);
				},
				error: function(data) {
					swal("Error!", "Ajax occured.", "error");
				}
			});
		});
	}

	function edit_harga_beli(id){
      	$.ajax({
          	type: "GET",
	        url: '{{url("data_obat/edit_harga_beli/")}}/'+id,
	        async:true,
	        data: {
	            _token		: "{{csrf_token()}}",
	        },
	        beforeSend: function(data){
	          	// on_load();
		        $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
		        $("#modal-xl .modal-title").html("Edit Data - Harga Beli Outlet");
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


  	function edit_harga_beli_ppn(id){
      	$.ajax({
          	type: "GET",
	        url: '{{url("data_obat/edit_harga_beli_ppn/")}}/'+id,
	        async:true,
	        data: {
	            _token		: "{{csrf_token()}}",
	        },
	        beforeSend: function(data){
	          	// on_load();
		        $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
		        $("#modal-xl .modal-title").html("Edit Data - Harga Beli + PPN Outlet");
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

  	function edit_harga_jual(id){
      	$.ajax({
          	type: "GET",
	        url: '{{url("data_obat/edit_harga_jual/")}}/'+id,
	        async:true,
	        data: {
	            _token		: "{{csrf_token()}}",
	        },
	        beforeSend: function(data){
	          	// on_load();
		        $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
		        $("#modal-xl .modal-title").html("Edit Data - Harga Jual Outlet");
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

  	function gunakan_hb(id, id_obat, hb, hb_ppn){
		swal({
		  	title: "Apakah anda yakin mengganti harga beli dan harga beli + ppn yang aktif?",
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
				url: '{{url("data_obat/gunakan_hb")}}',
				async:true,
				data: {
					_token:token,
					id : id,
					id_obat : id_obat,
					hb : hb,
					hb_ppn : hb_ppn
				},
				beforeSend: function(data){
					// replace dengan fungsi loading
					spinner.show();
				},
				success:  function(data){
					spinner.hide();
					if(data==1){
						swal("Success!", "Data harga beli dan harga beli ppn telah disesuaikan.", "success");
					}else{
						swal("Alert!", "Penyesuaian harga beli dan harga beli ppn gagal.", "error");
					}
				},
				complete: function(data){
					tb_data_obat.fnDraw(false);
				},
				error: function(data) {
					swal("Error!", "Ajax occured.", "error");
				}
			});
		});
	}


	function gunakan_hb_ppn(id, id_obat, id_jenis_transaksi, ht, hb_ppn){
		swal({
		  	title: "Apakah anda yakin mengganti harga beli + ppn yang aktif?",
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
				url: '{{url("data_obat/gunakan_hb_ppn")}}',
				async:true,
				data: {
					_token:token,
					id : id,
					id_obat : id_obat,
					id_jenis_transaksi : id_jenis_transaksi,
					ht : ht,
					hb_ppn : hb_ppn
				},
				beforeSend: function(data){
					// replace dengan fungsi loading
					spinner.show();
				},
				success:  function(data){
					spinner.hide();
					if(data==1){
						swal("Success!", "Data harga beli ppn telah disesuaikan.", "success");
					}else{
						swal("Alert!", "Penyesuaian harga beli ppn gagal.", "error");
					}
				},
				complete: function(data){
					tb_data_obat.fnDraw(false);
				},
				error: function(data) {
					swal("Error!", "Ajax occured.", "error");
				}
			});
		});
	}


  	function gunakan_hj(id, id_obat, hj){
		swal({
		  	title: "Apakah anda yakin mengganti harga jual yang aktif?",
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
				url: '{{url("data_obat/gunakan_hj")}}',
				async:true,
				data: {
					_token:token,
					id : id,
					id_obat : id_obat,
					hj : hj
				},
				beforeSend: function(data){
					// replace dengan fungsi loading
					spinner.show();
				},
				success:  function(data){
					spinner.hide();
					if(data==1){
						swal("Success!", "Data harga jual telah disesuaikan.", "success");
					}else{
						swal("Alert!", "Penyesuaian harga jual ppn gagal.", "error");
					}
				},
				complete: function(data){
					tb_data_obat.fnDraw(false);
				},
				error: function(data) {
					swal("Error!", "Ajax occured.", "error");
				}
			});
		});
	}

	function checkStatus(val, id) {
		var str_confirm = '';
		if(val == 0) { 
			str_confirm = "Apakah anda yakin menset harga obat ini tidak mengikuti harga pusat?";
			var nilai = 1;
		} else {
			str_confirm = "Apakah anda yakin menset harga obat ini mengikuti harga pusat?";
			var nilai = 0;
		}

		swal({
		  	title: str_confirm,
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
				url: '{{url("data_obat/set_status_harga_outlet")}}',
				async:true,
				data: {
					_token:token,
					id:id,
					nilai : nilai
				},
				beforeSend: function(data){
					// replace dengan fungsi loading
					spinner.show();
				},
				success:  function(data){
					spinner.hide();
					if(data==1){
						swal("Success!", "Data setting berhasil dilakukan.", "success");
					}else{
						swal("Alert!", "Setting data gagal.", "error");
					}
				},
				complete: function(data){
					tb_data_obat.fnDraw(false);
				},
				error: function(data) {
					swal("Error!", "Ajax occured.", "error");
				}
			});
		});
	}

	function template_data(){
	    window.open("{{ url('data_obat/template_data') }}","_blank");
	}

	function import_data(){
	    $.ajax({
	        type: "POST",
	        url: '{{url("data_obat/import_data")}}',
	        async:true,
	        data: {
	        	_token:token,
	        },
	        beforeSend: function(data){
	          // on_load();
	        $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
	        $("#modal-xl .modal-title").html("Import Data Golongan & Penanda Obat");
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

	function reload_data_histori_transaksi(id){
		swal({
		  	title: "Apakah anda yakin untuk melakukan reload data?",
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
				url: '{{url("data_obat/reload_data_histori_transaksi")}}',
				async:true,
				data: {
					_token:token,
					id:id,
				},
				beforeSend: function(data){
					// replace dengan fungsi loading
				},
				success:  function(data){
					if(data==1){
						swal("Success!", "Data histori transaksi obat ini telah direload.", "success");
					}else{
						swal("Gagal!", "Data histori transaksi gagal direload.", "error");
					}
				},
				complete: function(data){
					tb_data_obat.fnDraw(false);
				},
				error: function(data) {
					swal("Error!", "Ajax occured.", "error");
				}
			});
		});
	}

	function lihat_stok_tersedia(id){
      	$.ajax({
          	type: "GET",
	        url: '{{url("data_obat/lihat_stok_tersedia/")}}/'+id,
	        async:true,
	        data: {
	            _token		: "{{csrf_token()}}",
	        },
	        beforeSend: function(data){
	          	// on_load();
		        $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
		        $("#modal-xl .modal-title").html("Stok Tersedia");
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
</script>
@endsection