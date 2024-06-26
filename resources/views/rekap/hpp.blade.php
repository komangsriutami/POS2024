@extends('layout.app_penjualan')

@section('title')
Harga Pokok Penjualan (HPP)
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Rekap Data</a></li>
    <li class="breadcrumb-item"><a href="#">Harga Pokok Penjualan</a></li>
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
   				List Data Harga Pokok Penjualan
        	</h3>
      	</div>
        <div class="card-body">
        	<form role="form" id="searching_form">
                <!-- text input -->
                <div class="row">
                    <!-- <div class="form-group col-md-3">
				        {!! Form::label('tahun', 'Pilih Tahun') !!}
				        <select id="tahun" name="tahun" class="form-control input_select">
				        	<option value="2020" {!!( "2020" == $tahun ? 'selected' : '')!!}>2020</option>
				        	<option value="2021" {!!( "2021" == $tahun ? 'selected' : '')!!}>2021</option>
				        	<option value="2022" {!!( "2022" == $tahun ? 'selected' : '')!!}>2022</option>
				        </select>
				    </div>
				    <div class="form-group col-md-3">
				        {!! Form::label('bulan', 'Pilih Bulan') !!}
				        <select id="bulan" name="bulan" class="form-control input_select">
				        	<option value="1" {!!( "1" == $bulan ? 'selected' : '')!!}>Januari</option>
				        	<option value="2" {!!( "2" == $bulan ? 'selected' : '')!!}>Februari</option>
				        	<option value="3" {!!( "3" == $bulan ? 'selected' : '')!!}>Maret</option>
				        	<option value="4" {!!( "4" == $bulan ? 'selected' : '')!!}>April</option>
				        	<option value="5" {!!( "5" == $bulan ? 'selected' : '')!!}>Mei</option>
				        	<option value="6" {!!( "6" == $bulan ? 'selected' : '')!!}>Juni</option>
				        	<option value="7" {!!( "7" == $bulan ? 'selected' : '')!!}>Juli</option>
				        	<option value="8" {!!( "8" == $bulan ? 'selected' : '')!!}>Agustus</option>
				        	<option value="9" {!!( "9" == $bulan ? 'selected' : '')!!}>September</option>
				        	<option value="10" {!!( "10" == $bulan ? 'selected' : '')!!}>Oktober</option>
				        	<option value="11" {!!( "11" == $bulan ? 'selected' : '')!!}>November</option>
				        	<option value="12" {!!( "12" == $bulan ? 'selected' : '')!!}>Desember</option>
				        </select>
				    </div> -->
				    <div class="form-group  col-md-2">
                        <label>Dari Tanggal</label>
                        <input type="text" name="tgl_awal"  id="tgl_awal" class="datepicker form-control" autocomplete="off" value="{{ $first_day }}">
                    </div>
                    <div class="form-group  col-md-2">
                        <label>Sampai Tanggal</label>
                        <input type="text" name="tgl_akhir" id="tgl_akhir" class="datepicker form-control" autocomplete="off" value="{{ $first_day }}">
                    </div>
                    <div class="col-lg-12" style="text-align: center;">
                        <button type="submit" class="btn btn-primary" id="datatable_filter"><i class="fa fa-search"></i> Cari</button>
                        <span class="btn bg-olive" onClick="export_data()"  data-toggle="modal" data-placement="top" title="Export Data"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export</span> 
                        <!-- <span class="btn bg-olive" onClick="export_data_versi1()"  data-toggle="modal" data-placement="top" title="Export Data"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export V2</span>  -->
                        <span class="btn bg-olive" onClick="export_data_versi2()"  data-toggle="modal" data-placement="top" title="Export Data"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export V2</span> 
                    </div>
                </div>
            </form>
			<hr>
			<table  id="tb_data_hpp" class="table table-bordered table-striped table-hover" width="100%">
		    	<thead>
			        <tr>
			            <th width="3%" class="text-center">No.</th>
			            <th width="7%" class="text-center">Tanggal</th>
			            <th width="7%" class="text-center">Diskon</th>
			            <th width="20%" class="text-center">Nama Obat</th> 
			            <th width="7%" class="text-center">Jumlah</th>
			            <th width="10%" class="text-center">Harga Jual</th>
			            <th width="10%" class="text-center">Total HJ</th>
                        <th width="10%" class="text-center">Harga Pokok</th>
                        <th width="10%" class="text-center">Total HP</th>
                        <th width="8%" class="text-center">Laba</th>
                        <th width="8%" class="text-center">% Laba</th>
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
	var tb_data_hpp = $('#tb_data_hpp').dataTable( {
			processing: true,
	        serverSide: true,
	        stateSave: true,
	        ajax:{
			        url: '{{url("penjualan/list_hpp")}}',
			        data:function(d){
                        d.tgl_awal = $("#tgl_awal").val();
                        d.tgl_akhir = $("#tgl_akhir").val();
				    }
			     },
	        columns: [
	            {data: 'no', name: 'no',width:"2%", class:'text-center'},
	            {data: 'tgl_nota', name: 'tgl_nota', class:'text-center'},
	            {data: 'diskon', name: 'diskon', class:'text-center'},
	            {data: 'id_obat', name: 'id_obat'},
	            {data: 'jumlah', name: 'jumlah'},
                {data: 'harga_jual', name: 'harga_jual', class:'text-center'},
                {data: 'total', name: 'total', class:'text-center'},
                {data: 'harga_beli_ppn', name: 'harga_beli_ppn', class:'text-center'},
                {data: 'total_hp', name: 'total_hp', class:'text-center'},
	            {data: 'laba', name: 'laba', orderable: false, searchable: true},
                {data: 'persentase_laba', name: 'persentase_laba', orderable: false, searchable: true}
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
            tb_data_hpp.fnDraw(false);
        });

        $('#tgl_awal, #tgl_akhir').datepicker({
            autoclose:true,
            format:"yyyy-mm-dd",
            forceParse: false
        });

        $('.input_select').select2({});

        $('body').addClass('sidebar-collapse');
	})

    function export_data(){
        window.open("{{ url('penjualan/export_hpp') }}"+ "?tgl_awal="+$('#tgl_awal').val()+"&tgl_akhir="+$('#tgl_akhir').val(),"_blank");
    }

    function export_data_versi1(){
        window.open("{{ url('penjualan/export_hpp_v1') }}"+ "?tgl_awal="+$('#tgl_awal').val()+"&tgl_akhir="+$('#tgl_akhir').val(),"_blank");
    }

    function export_data_versi2(){
        window.open("{{ url('penjualan/export_hpp_v2') }}"+ "?tgl_awal="+$('#tgl_awal').val()+"&tgl_akhir="+$('#tgl_akhir').val(),"_blank");
    }
</script>
@endsection