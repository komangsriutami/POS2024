@extends('layout.app')

@section('title')
Data Penyesuaian Obat
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Data Obat</a></li>
    <li class="breadcrumb-item"><a href="#">Data Penyesuaian Obat</a></li>
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
            Histori Penyesuaian Stok Obat
        </h3>
        <div class="card-tools">
            <a href="{{ url('data_obat') }}" onclick="#" class="btn btn-danger btn-sm pull-right" data-toggle="tooltip" data-placement="top" title="Kembali ke daftar data"><i class="fa fa-undo"></i> Kembali</a>
        </div>
    </div>
    <div class="card-body">
        <form role="form" id="searching_form">
            <!-- text input -->
            <div class="row">
                <div class="form-group  col-md-2">
                    <label>Dari Tanggal</label>
                    <input type="text" name="tgl_awal"  id="tgl_awal" class="datepicker form-control" value="{{ $first_day }}" autocomplete="off">
                </div>
                <div class="form-group  col-md-2">
                    <label>Sampai Tanggal</label>
                    <input type="text" name="tgl_akhir"  id="tgl_akhir" class="datepicker form-control" value="{{ $first_day }}" autocomplete="off">
                </div>
                <div class="col-lg-12" style="text-align: center;">
                    <button type="submit" class="btn btn-primary" id="datatable_filter"><i class="fa fa-search"></i> Cari</button>
                    <span class="btn bg-olive" onClick="export_data()"  data-toggle="modal" data-placement="top" title="Export Data Transfer"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export</span> 
                </div>
            </div>
        </form>
        <hr>
        <div class="row">
            <div class="table-responsive">
                <div class="table-responsive">
                    <table  id="tb_penyesuaian_stok_obat" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No.</th>
                                <th width="10%">Tanggal</th>
                                <th width="25%">Nama Obat</th>
                                <th width="10%">Stok Awal</th>
                                <th width="10%">Stok Akhir</th>
                                <th width="25%">Alasan</th>
                                <th width="15%">Oleh</th>
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

	var tb_penyesuaian_stok_obat = $('#tb_penyesuaian_stok_obat').dataTable( {
           processing: true,
          serverSide: true,
          stateSave: true,
          scrollX: true,
           ajax:{
               url: '{{url("data_obat/get_data_penyesuaian_stok")}}',
               data:function(d){
                 d.tgl_awal = $("#tgl_awal").val();
                 d.tgl_akhir = $("#tgl_akhir").val();
               }
            },
           columns: [
               {data: 'no', name: 'no', orderable: true, searchable: true, class:'text-center'},
               {data: 'created_at', name: 'created_at', orderable: true, searchable: true, class:'text-center'},
               {data: 'id_obat', name: 'id_obat'},
               {data: 'stok_awal', name: 'stok_awal', class:'text-center'},
               {data: 'stok_akhir', name: 'stok_akhir',  class:'text-center'},
               {data: 'alasan', name: 'alasan',  class:'text-center'},
               {data: 'created_by', name: 'created_by', class:'text-center'}
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
    $('#tgl_awal, #tgl_akhir').datepicker({
        autoclose:true,
        format:"yyyy-mm-dd",
        forceParse: false
    });


    $("#searching_form").submit(function(e){
      e.preventDefault();
      tb_penyesuaian_stok_obat.fnDraw(false);
    });
	})

	function goBack() {
       window.history.back();
   }

   function export_data(){
        window.open("{{ url('data_obat/export_data_penyesuaian_stok') }}"+ "?tgl_awal="+$('#tgl_awal').val()+"&tgl_akhir="+$('#tgl_akhir').val(),"_blank");
    }
</script>
@endsection