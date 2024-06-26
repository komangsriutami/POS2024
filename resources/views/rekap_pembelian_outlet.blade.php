@extends('layout.app')

@section('title')
Rekap Pembelian Outlet
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Rekap Pembelian Outlet</a></li>
    <li class="breadcrumb-item active" aria-current="page">Index</li>
</ol>
@endsection

@section('content')
	<style type="text/css">
        /*custom style, untuk hide datatable length dan search value*/
        .dataTables_filter{
            display: none;
        }
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
   				Rekap Pembelian Outlet
        	</h3>
      	</div>
        <div class="card-body">
        	<form role="form" id="searching_form">
                <!-- text input -->
                <div class="row">
                	<div class="col-lg-1 form-group">
						<label>Limit Data</label>
						<input type="text" id="limit" class="form-control" placeholder="Limit" value="100">
			    	</div>
			    	<div class="form-group col-md-2">
                		<div class="row">
							<div class="form-group col-lg-12">
								{!! Form::label('id_pencarian', 'Pilih Tipe Pencarian') !!}
								{!! Form::select('id_pencarian', ['1' => 'Hari Ini', '2' => 'Kemarin', '3' => 'Pekan Ini', '4' => 'Pekan Lalu', '5' => 'Bulan Ini', '6' => 'Bulan Lalu', '7' => '3 Bulan Terakhir', '8' => '6 Bulan Terakhir', '9' => 'Kalender'], 1, ['placeholder' => '-- tipe --', 'class' => 'form-control input_select required']) !!}
							</div>
							<div class="col-lg-12 form-group" hidden>
								<label>Tanggal</label>
								<input type="text" id="search_tanggal" class="form-control" placeholder="Tanggal Penjualan">
							</div>
						</div>
				    </div>
                	<div class="col-lg-2 form-group">
						<label>Nama Obat</label>
						<input type="text" id="nama" class="form-control" placeholder="Nama">
			    	</div>
			    	<div class="form-group col-md-2">
						{!! Form::label('id_satuan', 'Pilih Satuan') !!}
				        {!! Form::select('id_satuan', $satuans, null, ['class' => 'form-control input_select required']) !!}
				    </div>
					<div class="form-group col-md-2">
						{!! Form::label('id_produsen', 'Pilih Produsen') !!}
						{!! Form::select('id_produsen', $produsens, null, ['class' => 'form-control input_select required']) !!}
					</div>
                    <div class="col-lg-12" style="text-align: center;">
                        <button type="submit" class="btn btn-primary" id="datatable_filter"><i class="fa fa-search"></i> Cari</button>
                        <span class="btn bg-olive" onClick="export_data()"  data-toggle="modal" data-placement="top" title="Export Data"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export</span>
                        <span class="btn bg-olive" onClick="export_data_all()"  data-toggle="modal" data-placement="top" title="Export Data All"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export All Outlet</span>
                    </div>
                </div>
            </form>
			<hr>
			<div id="view_here">
				<div class="row">
					<div class="col-md-12">
						<table class="table table-bordered table-striped table-hover" id="tb_pembelian" width="100%">
		                    <thead>
		                        <tr>
		                            <th width="100%" colspan="14" class="text-center text-white" style="background-color:#455a64;">PEMBELIAN</th>
		                        </tr>
		                        <tr>
		                            <th width="5%" class="text-center text-white" style="background-color:#00bcd4;">No</th>
		                            <th width="45%" class="text-center text-white" style="background-color:#00bcd4;">Nama Obat</th>
		                            <th width="15%" class="text-center text-white" style="background-color:#00bcd4;">Penandaan Obat</th>
		                            <th width="5%" class="text-center text-white" style="background-color:#00bcd4;">Jenis</th>
		                            <th width="15%" class="text-center text-white" style="background-color:#00bcd4;">Produsen</th>
		                            <th width="15%" class="text-center text-white" style="background-color:#00bcd4;">Supplier</th>
		                            <th width="15%" class="text-center text-white" style="background-color:#00bcd4;">Jumlah</th>
		                        </tr>
		                    </thead>
		                    <tbody>
		                    </tbody>
		                </table>
					</div>
				</div>
				<br>
				<div class="row">
					<div class="col-md-12">
						<table class="table table-bordered table-striped table-hover" id="tb_transfer_keluar" width="100%">
		                    <thead>
		                        <tr>
		                            <th width="100%" colspan="14" class="text-center text-white" style="background-color:#455a64;">TRANSFER KELUAR</th>
		                        </tr>
		                        <tr>
		                            <th width="5%" class="text-center text-white" style="background-color:#00bcd4;">No</th>
		                            <th width="30%" class="text-center text-white" style="background-color:#00bcd4;">Nama Obat</th>
		                            <th width="25%" class="text-center text-white" style="background-color:#00bcd4;">Penandaan Obat</th>
		                            <th width="5%" class="text-center text-white" style="background-color:#00bcd4;">Jenis</th>
		                            <th width="25%" class="text-center text-white" style="background-color:#00bcd4;">Produsen</th>
		                            <th width="15%" class="text-center text-white" style="background-color:#00bcd4;">Jumlah</th>
		                        </tr>
		                    </thead>
		                    <tbody>
		                	</tbody>
		                </table>
					</div>
					<div class="col-md-12">
						<table class="table table-bordered table-striped table-hover" id="tb_transfer_masuk" width="100%">
		                    <thead>
		                        <tr>
		                            <th width="100%" colspan="14" class="text-center text-white" style="background-color:#455a64;">TRANSFER MASUK</th>
		                        </tr>
		                        <tr>
		                            <th width="5%" class="text-center text-white" style="background-color:#00bcd4;">No</th>
		                            <th width="30%" class="text-center text-white" style="background-color:#00bcd4;">Nama Obat</th>
		                            <th width="25%" class="text-center text-white" style="background-color:#00bcd4;">Penandaan Obat</th>
		                            <th width="5%" class="text-center text-white" style="background-color:#00bcd4;">Jenis</th>
		                            <th width="25%" class="text-center text-white" style="background-color:#00bcd4;">Produsen</th>
		                            <th width="15%" class="text-center text-white" style="background-color:#00bcd4;">Jumlah</th>
		                        </tr>
		                    </thead>
		                    <tbody>
		                    </tbody>
		                </table>
					</div>
				</div>
			</div>
        </div>
  	</div>
@endsection

@section('script')
<script type="text/javascript">
	var token = '{{csrf_token()}}';

	var tb_pembelian = $('#tb_pembelian').dataTable( {
		paging : false,
		processing: true,
		serverSide: true,
		stateSave: true,
		ajax:{
				url: '{{url("home/list_pareto_pembelian")}}',
				data:function(d){
					d.tanggal = $('#search_tanggal').val();
					d.limit = $('#limit').val();
					d.id_satuan = $("#id_satuan").val();
					d.id_produsen = $("#id_produsen").val();
					d.nama = $("#nama").val();
					d.id_pencarian = $("#id_pencarian").val();
				}
				},
		columns: [
			{data: 'no', name: 'no',width:"2%"},
			{data: 'nama_obat', name: 'nama_obat'},
			{data: 'id_penandaan_obat', name: 'id_penandaan_obat'},
			{data: 'id_satuan', name: 'id_satuan'},
			{data: 'id_produsen', name: 'id_produsen'},
			{data: 'id_suplier', name: 'id_suplier'},
			{data: 'jumlah_total', name: 'jumlah_total', class: "text-center"}
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

	var tb_transfer_keluar = $('#tb_transfer_keluar').dataTable( {
		paging : false,
		processing: true,
		serverSide: true,
		stateSave: true,
		ajax:{
				url: '{{url("home/list_pareto_transfer_keluar")}}',
				data:function(d){
					d.tanggal = $('#search_tanggal').val();
					d.limit = $('#limit').val();
					d.id_satuan = $("#id_satuan").val();
					d.id_produsen = $("#id_produsen").val();
					d.nama = $("#nama").val();
					d.id_pencarian = $("#id_pencarian").val();
				}
				},
		columns: [
			{data: 'no', name: 'no',width:"2%"},
			{data: 'nama', name: 'nama'},
			{data: 'id_penandaan_obat', name: 'id_penandaan_obat'},
			{data: 'id_satuan', name: 'id_satuan'},
			{data: 'id_produsen', name: 'id_produsen'},
			{data: 'jumlah_pemakaian', name: 'jumlah_pemakaian', class: "text-center"}
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

	var tb_transfer_masuk = $('#tb_transfer_masuk').dataTable( {
		paging : false,
		processing: true,
		serverSide: true,
		stateSave: true,
		ajax:{
				url: '{{url("home/list_pareto_transfer_masuk")}}',
				data:function(d){
					d.tanggal = $('#search_tanggal').val();
					d.limit = $('#limit').val();
					d.id_satuan = $("#id_satuan").val();
					d.id_produsen = $("#id_produsen").val();
					d.nama = $("#nama").val();
					d.id_pencarian = $("#id_pencarian").val();
				}
				},
		columns: [
			{data: 'no', name: 'no',width:"2%"},
			{data: 'nama', name: 'nama'},
			{data: 'id_penandaan_obat', name: 'id_penandaan_obat'},
			{data: 'id_satuan', name: 'id_satuan'},
			{data: 'id_produsen', name: 'id_produsen'},
			{data: 'jumlah_pemakaian', name: 'jumlah_pemakaian', class: "text-center"}
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
            tb_pembelian.fnDraw(false);
            tb_transfer_keluar.fnDraw(false);
            tb_transfer_masuk.fnDraw(false);
        });

        $('#search_tanggal').daterangepicker({
		    autoclose:true,
		    forceParse: false
		});

		$('#id_pencarian').on('change', function() {
			if (this.value == '9') {
			$('#search_tanggal').parent().removeAttr('hidden');
			} else {
			$('#search_tanggal').parent().attr('hidden', true);
			}
		});

        $('.input_select').select2({});

        $('body').addClass('sidebar-collapse');
	})

	function export_data(){
        window.open("{{ url('rekap_pembelian_outlet/export_rekap_data') }}"+ "?id_pencarian="+$('#id_pencarian').val()+"&tanggal="+$('#search_tanggal').val()+"&limit="+$('#limit').val(),"_blank");
    }

	function export_data_all(){
        window.open("{{ url('rekap_pembelian_outlet/export_rekap_data_all') }}"+ "?id_pencarian="+$('#id_pencarian').val()+"&tanggal="+$('#search_tanggal').val()+"&limit="+$('#limit').val(),"_blank");
    }
</script>
@endsection