@extends('layout.app')

@section('title')
Rekap Data
@endsection

@section('breadcrumb')
<!-- <ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Transaksi</a></li>
    <li class="breadcrumb-item"><a href="#">Data Transfer Outlet</a></li>
    <li class="breadcrumb-item active" aria-current="page">Index</li>
</ol> -->
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
            <div class="row">
                <div class="col-lg-12">
                    <div class="card card-secondary">
                        <div class="card-header border-transparent">
                            <h3 class="card-title text-center">REKAP SELURUH TRANSAKSI</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                                </button>
                                <button type="button" class="btn btn-tool" data-card-widget="remove">
                                <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <div id="data_rekap_global"></div>
                            </div>
                            <!-- /.table-responsive -->
                        </div>
                        <!-- /.card-body -->
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')

{!! Html::script('assets/qz-tray/dependencies/rsvp-3.1.0.min.js') !!}
{!! Html::script('assets/qz-tray/dependencies/sha-256.min.js') !!}
{!! Html::script('assets/qz-tray/qz-tray.js') !!}
{!! Html::script('assets/qz-tray/qz_print_script.js') !!}
<script type="text/javascript">
    var token = '{{csrf_token()}}';

    $(document).ready(function(){
        $("#searching_form").submit(function(e){
            e.preventDefault();
    
            cari_info();
        });

        cari_info();


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