@extends('layout.app')

@section('title')
Monitoring Pembelian
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
                    <div class="form-group  col-md-2">
                        <label>Kenaikan (%)</label>
                        <input type="text" name="kenaikan"  id="kenaikan" class="form-control" value="50" autocomplete="off">
                    </div>
                    <div class="col-lg-12" style="text-align: center;">
                        <button type="submit" class="btn btn-primary" id="datatable_filter"><i class="fa fa-search"></i> Cari</button>
                        <span class="btn bg-olive" onClick="export_data_transfer()"  data-toggle="modal" data-placement="top" title="Export Data Transfer"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export</span> 
                    </div>
                </div>
            </form>
            <hr>
            <p class="text-red">*) Penanda warna abu merupakan tanda bahwa ada kenaikan >= 50%. Silakan perbaiki nota jika ada kesalahan input faktur pembelian sebelum stok opnam. </p>
            <p class="text-red">Jika faktur sudah disign maka bisa request ke kepala outlet untuk batal sign.</p>
            <p class="text-red">Jika faktur sudah lunas dan merupakan faktur kredit dapat menghubungi tim purchasing untuk perubahan data.</p>
            <table  id="tb_monitoring_pembelian" class="table table-bordered table-striped table-hover" style="width: 100%!important;">
                <thead>
                    <tr>
                        <th width="3%">No.</th>
                        <th width="35%">ID|Nama Obat</th>
                        <th width="10%">ID Nota| ID Detail</th>
                        <th width="7%">Tanggal</th>
                        <th width="7%">Jumlah</th>
                        <th width="8%">HBPPN</th>
                        <th width="8%">HBPPN Before</th>
                        <th width="10%">(%) Kenaikan/Penurunan</th>
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

    var tb_monitoring_pembelian = $('#tb_monitoring_pembelian').dataTable( {
            processing: true,
            serverSide: true,
            stateSave: true,
            ajax:{
                    url: '{{url("monitoring/getDataPembelian")}}',
                    data:function(d){
                        d.tgl_awal = $("#tgl_awal").val();
                        d.tgl_akhir = $("#tgl_akhir").val();
                        d.kenaikan = $("#kenaikan").val();
                    }
                 },
            columns: [
                {data: 'no', name: 'no',width:"2%"},
                {data: 'id_obat', name: 'id_obat'},
                {data: 'id', name: 'id', class: 'text-center'},
                {data: 'tgl_faktur', name: 'tgl_faktur', class: 'text-center'},
                {data: 'jumlah', name: 'jumlah', class: 'text-center'},
                {data: 'harga_beli_ppn', name: 'harga_beli_ppn', class: 'text-center'},
                {data: 'harga_beli_ppn_before', name: 'harga_beli_ppn_before', class: 'text-center'},
                {data: 'kenaikan', name: 'kenaikan', class: 'text-center'},
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
            tb_monitoring_pembelian.fnDraw(false);
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