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
            <div class="overlay-wrapper" id="overlay-wrapper-id">
                <div class="overlay" id="overlay-id">
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <form role="form" id="searching_form">
                            <?php $jum = count($apoteks);?>
                            <input type="hidden" name="jumApoteks" id="jumApoteks" value="{{$jum}}">
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
                        <br>
                        <div class="progress" style="height: 30px;">
                            <div id="progress-bar" class="progress-bar bg-success" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div> 
                        </div>
                        <br>
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
                        </div>
                        <table class="table table-bordered table-striped table-hover" id="data_rekap_global">

                            <thead>

                                <tr>

                                    <th class="text-center text-white" style="background-color:#00bcd4;">APOTEK</th>

                                    <th class="text-center text-white" style="background-color:#00bcd4;">COUNT PEMBELIAN</th>

                                    <th class="text-center text-white" style="background-color:#00bcd4;">COUNT PENJUALLAN</th>

                                    <th class="text-center text-white" style="background-color:#00bcd4;">TOTAL PENJUALAN NON KREDIT</th>

                                    <th class="text-center text-white" style="background-color:#00acc1;">TOTAL PENJUALAN KREDIT</th>

                                    <th class="text-center text-white" style="background-color:#00acc1;">TOTAL TT PENJUALAN</th>

                                    <th class="text-center text-white" style="background-color:#00acc1;">TOTAL PEMBAYARAN PENJUALAN KREDIT</th>

                                    <th class="text-center text-white" style="background-color:#00acc1;">TOTAL PEMBELIAN</th>

                                    <th class="text-center text-white" style="background-color:#00acc1;">TOTAL PIUTANG PEMBELIAN</th>

                                    <th class="text-center text-white" style="background-color:#0097a7;">TOTAL PEMBELIAN TERBAYAR</th>

                                    <th class="text-center text-white" style="background-color:#0097a7;">TOTAL PEMBELIAN JATUH TEMPO</th>

                                </tr>

                            </thead>
                            <tbody>
                                <div id="data_rekap_global"></div>
                            </tbody>
                        </table>
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
    let progressBar = $('#progress-bar');
    let width = 0;
    const totalLoops = $("#jumApoteks").val();
    const increment = 100 / totalLoops;
    let currentLoop = 0;
    var apoteks = @json($apoteks);
    var overlay = document.getElementById('overlay-wrapper-id');
    var overlaybody = document.getElementById('overlay-id');
    var tableBody = document.querySelector('#data_rekap_global tbody');

    $(document).ready(function(){
        overlay.classList.remove('overlay-wrapper');
        overlaybody.classList.remove('overlay');
        $("#searching_form").submit(function(e){
            e.preventDefault();
            overlay.classList.add('overlay-wrapper');
            overlaybody.classList.add('overlay');
            swal({
                title: "Apakah anda akan yakin melihat rekap data?",
                text: 'Proses ini akan memerlukan waktu yang cukup lama, mohon bersabar sampai proses selesai. Anda dapat melihat progres load data pada halaman ini.',
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Ya",
                cancelButtonText: "Tidak",
                closeOnConfirm: true
            },
            function(){
                getData();
            });
        });

        $('#tgl_awal, #tgl_akhir').datepicker({
            autoclose:true,
            format:"yyyy-mm-dd",
            forceParse: false
        });

        $('.input_select').select2({});
    })

    function updateProgressBar() {
        currentLoop++;
        width += increment;
        progressBar.css('width', width + '%');
        progressBar.attr('aria-valuenow', width);
        progressBar.text(Math.round(width) + '%');
    }



    function getData() {
        $.ajax({
            type: "GET",
            url: '{{ url("home/cari_info") }}',
            async: true,
            data: {
                _token: token,
                tgl_awal: $("#tgl_awal").val(),
                tgl_akhir: $("#tgl_akhir").val(),
                id_apotek:apoteks[currentLoop].id
            },
            beforeSend: function(data) {
                // replace dengan fungsi loading
            },
            success: function(data) {
                $("#data_rekap_global tbody").append(data);
            },
            complete: function(data) {
                updateProgressBar();
                if (currentLoop < totalLoops) {
                    getData(); // Call getData again until totalLoops is reached
                }

                if(currentLoop == 100) {
                    overlay.classList.remove('overlay-wrapper');
                    overlaybody.classList.remove('overlay');
                }
            },
            error: function(data) {
                swal("Error!", "Ajax occured.", "error");
            }
        });
    }
</script>
@endsection