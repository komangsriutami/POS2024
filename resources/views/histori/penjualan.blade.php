@extends('layout.app')

@section('title')
Histori Transaksi Penjualan
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Histori</a></li>
    <li class="breadcrumb-item"><a href="#">Transaksi Penjualan</a></li>
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
	      	<span class="btn bg-blue" onClick="closing_kasir()"  data-toggle="modal" data-placement="top" title="Closing Kasir"><i class="fa fa-cog" aria-hidden="true"></i> Closing Kasir</span>
	    </div>
	</div>

	<div class="card card-info card-outline" id="main-box" style="">
  		<div class="card-header">
        	<h3 class="card-title">
          		<i class="fas fa-list"></i>
   				List Data Penjualan
        	</h3>
      	</div>
        <div class="card-body">
        	<form role="form" id="searching_form">
			    <!-- text input -->
			    <div class="row">
			    	<div class="col-lg-6 form-group">
						<label>ID Penjualan</label>
						<input type="text" id="search_id" class="form-control" placeholder="Masukan ID Penjualan">
			    	</div>
			    	<div class="col-lg-6 form-group">
						<label>Apotek</label>
						<select class="form-control input_select" id="id_apotek" name="id_apotek">
							<option value=""> --- Pilih Apotek ---</option>
							@foreach($apoteks as $obj)
							<option value="{{$obj->id}}"> {{$obj->nama_panjang}} </option>
							@endforeach
						</select>
			    	</div>
			    	<div class="col-lg-6 form-group">
						<label>Pasien</label>
						<select class="form-control input_select" id="id_pasien" name="id_pasien">
							<option value=""> --- Pilih Pasien ---</option>
							@foreach($pasiens as $obj)
							<option value="{{$obj->id}}"> {{$obj->nama}} </option>
							@endforeach
						</select>
			    	</div>
			    	<div class="col-md-6 form-group">
						<label>User</label>
						<select class="form-control input_select" id="id_user" name="id_user">
							<option value=""> --- Pilih User ---</option>
							@foreach($users as $obj)
							<option value="{{$obj->id}}"> {{$obj->nama}} </option>
							@endforeach
						</select>
			    	</div>
			    	<div class="col-lg-6 form-group">
						<label>Tanggal</label>
						<input type="text" id="search_tanggal" class="form-control" placeholder="Masukan Tanggal Penjualan">
			    	</div>
			    	<div class="col-lg-12" style="text-align: center;">
			    		<button type="submit" class="btn btn-primary" id="datatable_filter"><i class="fa fa-search"></i> Cari</button> 
			    		<span class="btn bg-olive" onClick="export_data_penjualan()"  data-toggle="modal" data-placement="top" title="Export Data Penjualan"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export</span>
			    	</div>
			    </div>
		  	</form>
			<table  id="tb_penjualan" class="table table-bordered table-striped table-hover">
		    	<thead>
			        <tr>
			            <th width="3%" class="text-center">No.</th>
			            <th width="5%" class="text-center">ID Nota</th>
			            <th width="12%" class="text-center">Tanggal</th>
			            <th width="20%" class="text-center">Penjualan Item</th>
			            <th width="20%" class="text-center">Resep/Dokter/Paket WD/Lab/APD</th>
			            <th width="15%" class="text-center">Total</th>
			            <th width="2%" class="text-center">S</th>
			            <th width="8%" class="text-center">Oleh</th>
			            <th width="14%" class="text-center">Action</th>
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
	var tb_penjualan = $('#tb_penjualan').DataTable( {
		paging:true,
        processing: true,
        serverSide: true,
        stateSave: true,
        scrollX: true,
        ajax: {
            url: '{{url("penjualan/list_histori")}}',
			        data:function(d){
			        	d.id = $('#search_id').val();
			        	d.id_apotek = $('#id_apotek').val();
			        	d.id_pasien = $('#id_pasien').val();
			        	d.id_user = $('#id_user').val();
			        	d.tanggal = $('#search_tanggal').val();
				    }
        },
        order: [],
        columns: [
        	{data: 'no', name: 'no',width:"2%", class:'text-center'},
            {data: 'id', name: 'id', class:'text-center'},
            {data: 'created_at', name: 'created_at', class:'text-center'},
            {data: 'total_belanja', name: 'total_belanja', class:'text-right'},
            {data: 'id_jasa_resep', name: 'id_jasa_resep', class:'text-right'},
            {data: 'total_fix', name: 'total_fix', class:'text-right', orderable: false, searchable: false},
            {data: 'is_kredit', name: 'is_kredit', class:'text-center'},
            {data: 'created_by', name: 'created_by', class:'text-center'},
            {data: 'action', name: 'id',orderable: true, searchable: true, class:'text-center'}
        ],
        drawCallback: function(callback) {
            $("#btn_set").html(callback['jqXHR']['responseJSON']['btn_set']);
            //console.log(callback['jqXHR']['responseJSON']['btn_set'])
        }
	});


 	setTimeout(function(){
        $('#tb_penjualan .checkAlltogle').prop('checked', false);
    }, 1);

	$(document).ready(function(){
	    $('#search_tanggal').daterangepicker({
		    autoclose:true,
			//format:"yyyy-mm-dd",
		    forceParse: false
		});

		$("#searching_form").submit(function(e){
			e.preventDefault();
			tb_penjualan.draw(false);
		});


		 $('.input_select').select2({});
	})

	function delete_penjualan(id){
        swal({
            title: "Apakah anda yakin menghapus data penjualan ini?",
            text: "Setelah data terhapus, stok juga akan diupdate kembali.",
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
                url: '{{url("penjualan")}}/'+id,
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
                        swal("Deleted!", "Data penjualan berhasil dihapus.", "success");
                    }else{
                        
                        swal("Failed!", "Gagal menghapus data penjualan.", "error");
                    }
                },
                complete: function(data){
                    tb_penjualan.draw(false);
                },
                error: function(data) {
                    swal("Error!", "Ajax occured.", "error");
                }
            });
        });
    }
	
    function cetak_nota(id){
		$.ajax({
		    type: "POST",
		    url: '{{url("penjualan/cetak_nota")}}/'+id,
		    async:true,
		    data: {
		    	_token:"{{csrf_token()}}"

		    },
		    beforeSend: function(data){
		      // on_load();
		    $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
		    $("#modal-xl .modal-title").html("Cetak Nota Penjualan");
		    $('#modal-xl').modal("show");
		    $('#modal-xl').find('.modal-body-content').html('');
		    $("#modal-xl").find(".overlay").fadeIn("200");
		    },
		    success:  function(data){
		      $('#modal-xl').find('.modal-body-content').html(data);
		    },
		    complete: function(data){
		        $("#modal-xl").find(".overlay").fadeOut("200");
		        startConnection();
		    },
		      error: function(data) {
		        alert("error ajax occured!");
		      }
		});
	}

	function closing_kasir(){
		$.ajax({
		    type: "POST",
		    url: '{{url("penjualan/closing_kasir")}}',
		    async:true,
		    data: {
		    	_token:"{{csrf_token()}}",
		    	id : $('#search_id').val(),
				id_apotek : $('#id_apotek').val(),
				id_pasien : $('#id_pasien').val(),
				id_user : $('#id_user').val(),
				tanggal : $('#search_tanggal').val(),

		    },
		    beforeSend: function(data){
		      // on_load();
			    $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
			    $("#modal-xl .modal-title").html("Closing Kasir");
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

	function export_data_penjualan(){
        window.open("{{ url('penjualan/export_all') }}"+ "?id_apotek="+$('#id_apotek').val()+"&id_pasien="+$('#id_pasien').val()+"&id_user="+$('#id_user').val()+"&tanggal="+$('#search_tanggal').val()+"&id="+$('#search_id').val(),"_blank");
    }

    function pelunasan(id){
		$.ajax({
		    type: "POST",
		    url: '{{url("penjualan/pembayaran_kredit")}}/'+id,
		    async:true,
		    data: {
		    	_token:"{{csrf_token()}}"
		    },
		    beforeSend: function(data){
		      // on_load();
		    $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
		    $("#modal-xl .modal-title").html("Pembayaran Penjualan");
		    $('#modal-xl').modal("show");
		    $('#modal-xl').find('.modal-body-content').html('');
		    $("#modal-xl").find(".overlay").fadeIn("200");
		    },
		    success:  function(data){
		      $('#modal-xl').find('.modal-body-content').html(data);
		    },
		    complete: function(data){
		        $("#modal-xl").find(".overlay").fadeOut("200")
		    },
		      error: function(data) {
		        alert("error ajax occured!");
		      }
		});
	}
</script>
@endsection