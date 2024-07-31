@extends('layout.app')

@section('title')
Stok Obat
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Master Data</a></li>
    <li class="breadcrumb-item"><a href="#">Data Obat</a></li>
    <li class="breadcrumb-item active" aria-current="page">Stok Obat</li>
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
            <i class="fas fa-star"></i> Detail Obat 
        </h3>
        <div class="card-tools">
            <a href="#" onclick="goBack()" class="btn btn-danger btn-sm pull-right" data-toggle="tooltip" data-placement="top" title="Kembali ke daftar data"><i class="fa fa-undo"></i> Kembali</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-sm-6">
                <input type="hidden" name="id_obat" id="id_obat" value="{{ $obat->id }}">
                <table width="100%">
                    <tr>
                        <td width="13%">ID</td>
                        <td width="2%"> : </td>
                        <td width="85">{{ $obat->id }}</td>
                    </tr>
                    <tr>
                        <td width="13%">Barcode</td>
                        <td width="2%"> : </td>
                        <td width="85">{{ $obat->barcode }}</td>
                    </tr>
                    <tr>
                        <td width="13%">Nama</td>
                        <td width="2%"> : </td>
                        <td width="85">{{ $obat->nama }}</td>
                    </tr>
                    <tr>
                        <td width="13%">Stok</td>
                        <td width="2%"> : </td>
                        <td width="85">{{ $stok_harga->stok_akhir }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="card-footer">
        <span class="text-info"><i class="fas fa-info"></i>&nbsp;Untuk pencarian, isikan kata yang ingin dicari pada kolom search, lalu tekan enter.</span>
    </div>
</div>
<div class="card card-info card-outline" id="main-box" style="">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list"></i>
            Histori Stok Obat
        </h3>
    </div>
    <div class="card-body">
      <form role="form" id="searching_form">
        <div class="row">
            <div class="form-group col-md-4">
                <label>Jenis Transaksi</label>
                {!! Form::select('id_jenis_transaksi', $jenis_transasksis, null, ['id'=>'id_jenis_transaksi', 'class' => 'form-control input_select']) !!}
            </div>
            <div class="form-group  col-md-4">
                <label>Dari Tanggal Transaksi</label>
                <input type="text" name="tgl_awal"  id="tgl_awal" class="datepicker form-control" autocomplete="off">
            </div>
            <div class="form-group  col-md-4">
                <label>Sampai Tanggal Transaksi</label>
                <input type="text" name="tgl_akhir" id="tgl_akhir" class="datepicker form-control" autocomplete="off">
            </div>

            <div class="col-lg-12" style="text-align: center;">
                <button type="submit" class="btn btn-primary" id="datatable_filter"><i class="fa fa-search"></i> Cari</button> 
            </div>
        </div>
      </form>
        <hr>
        <table id="tb_stok_obat" class="table table-bordered table-striped table-hover" width="100%">
            <thead>
                <tr>
                    <th width="5%">No.</th>
                    <th width="10%">Tanggal</th>
                    <th width="18%">Jenis Transaksi</th>
                    <th width="14%">Harga</th>
                    <th width="7%">Masuk</th>
                    <th width="7%">Keluar</th>
                    <th width="7%">Stok</th>
                    <th width="7%">No Batch</th>
                    <th width="10%">ED</th>
                    <th width="15%">Oleh</th>
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

	var tb_stok_obat = $('#tb_stok_obat').dataTable( {
       processing: true,
            processing: true,
            serverSide: true,
            stateSave: true,
            scrollX: true,
            ajax:{
               url: '{{url("data_obat/list_data_histori_all")}}',
               data:function(d){
                 d.id_obat = $('#id_obat').val();
                 d.id_jenis_transaksi = $("#id_jenis_transaksi").val();
                 d.tgl_awal = $("#tgl_awal").val();
                 d.tgl_akhir = $("#tgl_akhir").val();
               }
            },
           columns: [
               {data: 'no', name: 'no', orderable: true, searchable: true, class:'text-center'},
               {data: 'created_at', name: 'created_at', orderable: true, searchable: true, class:'text-center'},
               {data: 'id_jenis_transaksi', name: 'id_jenis_transaksi'},
               {data: 'harga', name: 'harga'},
               {data: 'masuk', name: 'masuk', class:'text-center'},
               {data: 'keluar', name: 'keluar', class:'text-center'},
               {data: 'stok_akhir', name: 'stok_akhir', class:'text-center'},
               {data: 'batch', name: 'batch', class:'text-center'},
               {data: 'ed', name: 'ed',  class:'text-center'},
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
    $('.input_select').select2({});

    $('#tgl_awal, #tgl_akhir').datepicker({
        autoclose:true,
        format:"yyyy-mm-dd",
        forceParse: false
    });

    $("#searching_form").submit(function(e){
      e.preventDefault();
      tb_stok_obat.fnDraw(false);
    });
	})

	function goBack() {
       window.history.back();
   }
</script>
@endsection