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
        	<div class="row">
	        	<div class="form-group col-md-2">
	        		<?php 
	        			$date = date('d/m/Y').' - '.date('d/m/Y');
	        		?>
				    {!! Form::label('tgl_penjualan', 'Tanggal Penjualan (*)') !!}
				    {!! Form::text('tgl_penjualan', $date, array('id' => 'tgl_penjualan', 'class' => 'form-control', 'placeholder'=>'Masukan tanggal penjualan')) !!}
				</div>
				<div class="form-group col-md-10">
					{!! Form::label('nama_obat', 'Barcode/Nama Obat (*)') !!}
				    {!! Form::text('nama_obat', null, array('id' => 'nama_obat', 'class' => 'form-control', 'placeholder'=>'Masukan barcode/nama obat')) !!}
				</div>
			</div>
			<table  id="tb_penjualan" class="table table-bordered table-striped table-hover">
		    	<thead>
			        <tr>
			            <th width="3%" class="text-center">No.</th>
			            <th width="5%" class="text-center">ID Nota</th>
			            <th width="12%" class="text-center">Tanggal</th>
			            <th width="10%" class="text-center">Penjualan Item</th>
			            <th width="15%" class="text-center">Resep/Dokter</th>
			            <th width="15%" class="text-center">Debet/Credit</th>
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
	var tb_penjualan = $('#tb_penjualan').dataTable( {
			processing: true,
	        serverSide: true,
	        stateSave: true,
	        ajax:{
			        url: '{{url("penjualan/list_histori")}}',
			        data:function(d){
			        	d.tgl_penjualan = $("#tgl_penjualan").val();
			        	d.nama_obat = $("#nama_obat").val();
				    }
			     },
	        columns: [
	            {data: 'no', name: 'no',width:"2%", class:'text-center'},
	            {data: 'id', name: 'id', class:'text-center'},
	            {data: 'created_at', name: 'created_at', class:'text-center'},
	            {data: 'total', name: 'total', class:'text-right'},
	            {data: 'biaya_jasa_dokter', name: 'biaya_jasa_dokter', class:'text-right'},
	            {data: 'debet', name: 'debet', class:'text-right'},
	            {data: 'total_fix', name: 'total_fix', class:'text-right', orderable: false, searchable: false},
	            {data: 'is_kredit', name: 'is_kredit', class:'text-center'},
	            {data: 'created_by', name: 'created_by', class:'text-center'},
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
		$('#tgl_penjualan').daterangepicker({
        	timePicker: false,
        	timePickerIncrement: 30,
		    locale: {
		       	format: 'DD/MM/YYYY'
		    }
        });

        $("#tgl_penjualan").change(function(e){
		    e.preventDefault();
			tb_penjualan.fnDraw();
	    });

	    $("#nama_obat").keypress(function(event){
		    if (event.which == '10' || event.which == '13') {
		        event.preventDefault();
		        tb_penjualan.fnDraw();
		    }
		});
	})
	
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
</script>
@endsection