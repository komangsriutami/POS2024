@extends('layout.app')

@section('title')
Cek Obat Kadaluarsa
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Transaksi Pembelian</a></li>
    <li class="breadcrumb-item"><a href="#">Cek Obat Kadaluarsa</a></li>
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
   				List Data Obat
        	</h3>
      	</div>
        <div class="card-body">
        	 <form role="form" id="searching_form">
            	<div class="row">
                    <div class="col-lg-3 form-group">
                        <label>No Faktur</label>
                        <input type="text" id="no_faktur" class="form-control" placeholder="Masukan Nomer Faktur" autocomplete="off">
                    </div>
                    <div class="col-lg-3 form-group">
                        <label>Batch</label>
                        <input type="text" id="batch" class="form-control" placeholder="Masukan Bacth Obat" autocomplete="off">
                    </div>
                    <div class="form-group  col-md-3">
                        <label>Dari Tanggal ED</label>
                        <input type="text" name="tgl_awal"  id="tgl_awal" class="datepicker form-control" autocomplete="off">
                    </div>
                    <div class="form-group  col-md-3">
                        <label>Sampai Tanggal ED</label>
                        <input type="text" name="tgl_akhir" id="tgl_akhir" class="datepicker form-control" autocomplete="off">
                    </div>
                    <div class="col-lg-12" style="text-align: center;">
                        <button type="submit" class="btn btn-primary" id="datatable_filter"><i class="fa fa-search"></i> Cari</button> 
                        <span class="btn bg-olive" onClick="export_data_ed()"  data-toggle="modal" data-placement="top" title="Export Data Transfer"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export</span> 
                    </div>
            	</div>
            </form>
			<hr>
			<table  id="tb_histori_item" class="table table-bordered table-striped table-hover">
		    	<thead>
			        <tr>
			            <th width="3%" class="text-center">No.</th>
			            <th width="5%" class="text-center">ID Nota</th>
			            <th width="15%" class="text-center">Tanggal</th>
			            <th width="35%" class="text-center">Nama Obat</th>
			            <th width="10%" class="text-center">ED</th>
			            <th width="10%" class="text-center">Batch</th>
			            <th width="7%" class="text-center">Stok</th>s
			            <th width="15%" class="text-center">Action</th>
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
	var tb_histori_item = $('#tb_histori_item').dataTable( {
			processing: true,
	        serverSide: true,
	        stateSave: true,
	        ajax:{
			        url: '{{url("pembelian/list_obat_kadaluarsa")}}',
			        data:function(d){
			        	d.batch = $('#batch').val();
	                    d.no_faktur = $('#no_faktur').val();
	                    d.tgl_awal = $("#tgl_awal").val();
	                    d.tgl_akhir = $("#tgl_akhir").val();
				    }
			     },
	        columns: [
	            {data: 'no', name: 'no',width:"2%", class:'text-center'},
	            {data: 'id', name: 'id', class:'text-center'},
	            {data: 'created_at', name: 'created_at', class:'text-center'},
	            {data: 'id_obat', name: 'id_obat'},
	            {data: 'tgl_batch', name: 'tgl_batch'},
	            {data: 'id_batch', name: 'id_batch'},
	            {data: 'stok', name: 'stok'},
	            {data: 'action', name: 'action', orderable: false, searchable: true, class:'text-center'}
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
			tb_histori_item.fnDraw(false);
		});

        $('.input_select').select2({});

		$('#tgl_awal, #tgl_akhir').datepicker({
            autoclose:true,
            format:"yyyy-mm-dd",
            forceParse: false
        });
	})

	function export_data_ed(){
        window.open("{{ url('pembelian/export_ed') }}"+ "?batch="+$('#batch').val()+"&no_faktur="+$('#no_faktur').val()+"&tgl_awal="+$('#tgl_awal').val()+"&tgl_akhir="+$('#tgl_akhir').val(),"_blank");
    }
</script>
@endsection