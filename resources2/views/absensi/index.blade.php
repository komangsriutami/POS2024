@extends('layout.app')

@section('title')
Data Absensi
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Data Absensi</a></li>
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
   				List Data Absensi
        	</h3>
      	</div>
        <div class="card-body">
        	<form role="form" id="searching_form">
            	<div class="row">
            		<div class="col-lg-2 form-group">
                        <label>Searching by</label>
                        <select id="id_searching_by" name="id_searching_by" class="form-control input_select" autocomplete="off">
                            <option value="1">All</option>
                            <option value="2">Per Apotek</option>
                        </select>
                    </div>
        			<div class="col-lg-2 form-group" id="div_apotek" style="display: none;">
                        <label>Apotek</label>
                        <select id="id_apotek" name="id_apotek" class="form-control input_select" autocomplete="off">
                            <?php $no = 0; ?>
                            @foreach( $apoteks as $apotek )
                                <?php $no = $no+1; ?>
                                <option value="{{ $apotek->id }}">{{ $apotek->nama_singkat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 form-group">
                        <label>Bulan</label>
                        <select id="bulan" name="bulan" class="form-control input_select" autocomplete="off">
                            <?php $no = 0; ?>
                            @foreach($months as $key => $bulan)
                                <?php $no = $no+1; ?>
                                <option value="{{ $key }}">{{ $bulan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-12" style="text-align: center;">
                        <button type="submit" class="btn btn-primary" id="datatable_filter"><i class="fa fa-search"></i> Cari</button> 
                        <span class="btn bg-olive" onClick="export_data()"  data-toggle="modal" data-placement="top" title="Export Data Absensi"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export</span> 
                    </div>
            	</div>
            </form>
			<hr>
			<table  id="tb_absensi" class="table table-bordered table-striped table-hover">
		    	<thead>
			        <tr>
			            <th width="5%">No.</th>
			            <th width="75%">User</th>
			            <th width="15%">Total</th>
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
	var tb_absensi = $('#tb_absensi').dataTable( {
			processing: true,
	        serverSide: true,
	        stateSave: true,
	        ajax:{
			        url: '{{url("absensi/list_absensi")}}',
			        data:function(d){
			        	d.id_apotek = $("#id_apotek").val();
			        	d.id_searching_by = $("#id_searching_by").val();
			        	d.bulan = $("#bulan").val();
				    }
			     },
	        columns: [
	            {data: 'no', name: 'no',width:"2%", class:'text-center'},
	            {data: 'id_user', name: 'id_user'},
	            {data: 'total', name: 'total', class:'text-center'},
	            {data: 'action', name: 'id',orderable: false, searchable: false, class:'text-center'}
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
			tb_absensi.fnDraw(false);
		});

        $('.input_select').select2({});

		$('#tgl_awal, #tgl_akhir').datepicker({
            autoclose:true,
            format:"yyyy-mm-dd",
            forceParse: false
        });

        $("#id_searching_by").change(function () {
        	var id_searching_by = $("#id_searching_by").val();
        	if(id_searching_by == 2) {
        		$("#div_apotek").show();	
        	} else {
        		$("#div_apotek").hide();
        	}
		})  
	})

	function export_data(){
        window.open("{{ url('absensi/export_absensi') }}"+ "?id_apotek="+$('#id_apotek').val()+"&id_searching_by="+$('#id_searching_by').val()+"&bulan="+$('#bulan').val(),"_blank");
    }
</script>
@endsection