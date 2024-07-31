@extends('layout.app')

@section('title')
Data Loss Sell
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Transaksi Penjualan</a></li>
    <li class="breadcrumb-item"><a href="#">Loss Sell</a></li>
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
			<a class="btn btn-success w-md m-b-5" href="{{url('loss_sell/create')}}"><i class="fa fa-plus"></i> Tambah Data</a>
	    </div>
	</div>

	<div class="card card-info card-outline" id="main-box" style="">
  		<div class="card-header">
        	<h3 class="card-title">
          		<i class="fas fa-list"></i>
   				List Data Loss Sell
        	</h3>
      	</div>
        <div class="card-body">
        	<form role="form" id="searching_form">
			    <!-- text input -->
			    <div class="row">
			    	<div class="col-lg-6 form-group">
						<label>Apotek</label>
						<select class="form-control input_select" id="id_apotek" name="id_apotek">
							@foreach($apoteks as $obj)
							<option value="{{$obj->id}}"> {{$obj->nama_panjang}} </option>
							@endforeach
						</select>
			    	</div>
			    	<div class="col-lg-12" style="text-align: center;">
			    		<button type="submit" class="btn btn-primary" id="datatable_filter"><i class="fa fa-search"></i> Cari</button> 
			    		<span class="btn bg-olive" onClick="export_data()"  data-toggle="modal" data-placement="top" title="Export Data Penjualan"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export</span>
			    	</div>
			    </div>
		  	</form>
        	<p class="text-danger">Tabel dengan warna abu adalah loss sell obat baru (tidak ada di database).</p>
			<table  id="tb_loss_sell" class="table table-bordered table-striped table-hover">
		    	<thead>
			        <tr>
			            <th width="3%">No.</th>
			            <th width="10%">Tanggal</th>
			            <th width="20%">Obat</th>
			            <th width="5%">Harga</th>
			            <th width="5%">Jumlah</th>
			            <th width="5%">Total</th>
			            <th width="35%">Keterangan</th>
			            <th width="12%">Sign</th>
			            <th width="5%">Action</th>
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
	var tb_loss_sell = $('#tb_loss_sell').dataTable( {
			processing: true,
	        serverSide: true,
	        stateSave: true,
	        ajax:{
			        url: '{{url("loss_sell/list_loss_sell")}}',
			        data:function(d){
			        	d.id_apotek = $('#id_apotek').val();
				    }
			    },
	        columns: [
	            {data: 'no', name: 'no',width:"2%", class:'text-center'},
	            {data: 'tanggal', name: 'tanggal', class:'text-center'},
	            {data: 'id_obat', name: 'id_obat'},
	            {data: 'harga', name: 'harga', class:'text-center'},
	            {data: 'jumlah', name: 'jumlah', class:'text-center'},
	            {data: 'total', name: 'total', class:'text-center'},
	            {data: 'keterangan', name: 'keterangan'},
	            {data: 'is_sign', name: 'is_sign', class:'text-center'},
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
		$("#searching_form").submit(function(e){
			e.preventDefault();
			tb_loss_sell.fnDraw(false);
        });

        $('#id_apotek').on('select2:select', function (e) {
            e.preventDefault();
			tb_loss_sell.fnDraw(false);
        });
	})

	function delete_loss_sell(id){
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
				url: '{{url("loss_sell")}}/'+id,
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
						swal("Deleted!", "Data loss sell berhasil dihapus.", "success");
					}else{
						
						swal("Failed!", "Gagal menghapus data loss sell.", "error");
					}
				},
				complete: function(data){
					tb_loss_sell.fnDraw(false);
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
				url : '{{url("loss_sell/")}}/'+id,
				dataType : "json",
				data : data,
				beforeSend: function(data){
					// replace dengan fungsi loading
				},
				success:  function(data){
					if(data.status ==1){
						show_info("Data loss sell berhasil disimpan!");
						$('#modal-large').modal('toggle');
					}else{
						show_error("Gagal menyimpan data ini!");
						return false;
					}
				},
				complete: function(data){
					// replace dengan fungsi mematikan loading
					tb_loss_sell.fnDraw(false);
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
	        url: '{{url("loss_sell")}}/'+id+'/edit',
	        async:true,
	        data: {
	            _token		: "{{csrf_token()}}",
	        },
	        beforeSend: function(data){
	          	// on_load();
		        $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
		        $("#modal-xl .modal-title").html("Edit Data - Loss Sell");
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

  	function sign(id) {
        swal({
            title: "Tanda Tangan",
            text: "Pastikan inputan sudah sesuai, kemudian tuliskan nama pendek/panggilan yang menginputkan data ini :",
            type: "input",
            showCancelButton: true,
            closeOnConfirm: false,
            animation: "slide-from-top",
            inputPlaceholder: "tulis disini..."
        },
        function(inputValue){
            if (inputValue === null) return false;
          
            if (inputValue === "") {
                swal.showInputError("tuliskan nama pendek/panggilan pada kolom input!");
                return false
            }
            $.ajax({
                type: "POST",
                url: '{{url("loss_sell/send_sign")}}',
                async:true,
                data: {
                    _token:token,
                    id:id,
                    sign_by:inputValue
                },
                beforeSend: function(data){
                    // replace dengan fungsi loading
                },
                success:  function(data){
                    if(data==1){
                        swal("Success!", "Data input data sudah di tanda tangani.", "success");
                    }else{
                        
                        swal("Failed!", "Gagal menyimpan data.", "error");
                    }
                },
                complete: function(data){
                    tb_loss_sell.draw(false);
                },
                error: function(data) {
                    swal("Error!", "Ajax occured.", "error");
                }
            });
            //swal("Nice!", "You wrote: " + inputValue, "success");
        });
    }

    function informasi(){
        $.ajax({
            type: "POST",
            url: '{{url("obat_operasional/informasi")}}',
            async:true,
            data: {
                _token:"{{csrf_token()}}",
            },
            beforeSend: function(data){
              // on_load();
            $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-info");
            $("#modal-xl .modal-title").html("Informasi");
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

    function batal_sign(id){
        swal({
            title: "Apakah anda yakin membatalkan sign data ini?",
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
                url: '{{url("loss_sell/batal_sign")}}',
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
                        swal("Success!", "Berhasil melakukan batal sign.", "success");
                    }else{
                        
                        swal("Failed!", "Gagal menyimpan data.", "error");
                    }
                },
                complete: function(data){
                    tb_loss_sell.draw(false);
                },
                error: function(data) {
                    swal("Error!", "Ajax occured.", "error");
                }
            });
        });
    }

    function export_data(){
        window.open("{{ url('loss_sell/export') }}"+ "?id_apotek="+$('#id_apotek').val(),"_blank");
    }
</script>
@endsection