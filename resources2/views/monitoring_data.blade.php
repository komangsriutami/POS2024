@extends('layout.app')

@section('title')
Monitoring Data
@endsection

@section('breadcrumb')
@endsection

@section('content')
    <style type="text/css">
        .select2 {
          width: 100%!important; /* overrides computed width, 100px in your demo */
        }
    </style>
    <style type="text/css">
        #divfix {
           bottom: 0;
           right: 0;
           position: fixed;
           z-index: 3000;
            }
        .format_total {
            font-size: 18px;
            font-weight: bold;
            color:#D81B60;
        }
    </style>

    <div class="card card-info card-outline" id="main-box" style="">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i>
                List Data
            </h3>
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
                        <span class="btn bg-olive" onClick="export_data_transfer()"  data-toggle="modal" data-placement="top" title="Export Data Transfer"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export</span> 
                    </div>
                </div>
            </form>
            <hr>
            <table  id="tb_monitoring_data" class="table table-bordered table-striped table-hover" style="width: 100%!important;">
                <thead>
                    <tr>
                        <th width="3%">No.</th>
                        <th width="35%">Nama Obat</th>
                        <th width="10%">ID.Hist</th>
                        <th width="12%">Jenis Transaksi</th>
                        <th width="10%">Stok Awal</th>
                        <th width="10%">Stok Akhir</th>
                        <th width="10%">ID.Transaction</th>
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

    var tb_monitoring_data = $('#tb_monitoring_data').dataTable( {
            processing: true,
            serverSide: true,
            stateSave: true,
            ajax:{
                    url: '{{url("monitoring/getData")}}',
                    data:function(d){
                        d.tgl_awal = $("#tgl_awal").val();
                        d.tgl_akhir = $("#tgl_akhir").val();
                    }
                 },
            columns: [
                {data: 'no', name: 'no',width:"2%"},
                {data: 'id_obat', name: 'id_obat'},
                {data: 'id', name: 'id', class: 'text-center'},
                {data: 'id_jenis_transaksi', name: 'id_jenis_transaksi', class: 'text-center'},
                {data: 'stok_awal', name: 'stok_awal', class: 'text-center'},
                {data: 'stok_akhir', name: 'stok_akhir', class: 'text-center'},
                {data: 'id_transaksi', name: 'id_transaksi', class: 'text-center'},
                {data: 'action', name: 'id',orderable: true, searchable: true, class: 'text-center'}
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
            tb_monitoring_data.fnDraw(false);
        });

        $('#tgl_awal, #tgl_akhir').datepicker({
            autoclose:true,
            format:"yyyy-mm-dd",
            forceParse: false
        });

        $('.input_select').select2({});
    })

    function cari_info() {
        $.ajax({
            type: "GET",
            url: '{{url("home/cari_info")}}',
            async:true,
            data: {
                _token:token,
                tgl_awal : $("#tgl_awal").val(),
                tgl_akhir : $("#tgl_akhir").val(),
            },
            beforeSend: function(data){
                // replace dengan fungsi loading
                spinner.show();
            },
            success:  function(data){
                $("#data_rekap_global").html(data);
                spinner.hide();
            },
            complete: function(data){
                
            },
            error: function(data) {
                swal("Error!", "Ajax occured.", "error");
            }
        });
    }
</script>
@endsection