@extends('layout.app_penjualan')

@section('title')
<?php 
    $nama_apotek_panjang_active = session('nama_apotek_panjang_active');
    $id_apotek_active = session('id_apotek_active');
    $so_status_aktif = session('so_status_aktif');
    $date = date('d-m-Y H:i:s');
?>
SO - Apotek {{ $nama_apotek_panjang_active }}
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item active" aria-current="page">Tanggal : {{ $date }}</li>
</ol>
@endsection

@section('content')
    <div class="row">
            <div class="col-md-1">
                <div class="info-box">
                    <span class="info-box-icon bg-info"><i class="far fa-envelope"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Barang</span>
                        <span class="info-box-number">{{ $total_barang }} drugs</span>
                    </div>
                </div>         
            </div>

            <div class="col-md-2">
                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="far fa-flag"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Barang Cek</span>
                        <span class="info-box-number">{{ $total_so }} drugs</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-danger"><i class="far fa-star"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Persediaan</span>
                        <?php $total_so_persediaan_format = number_format($total->total_tersedia, 2); ?>
                        <span class="info-box-number">Rp {{ $total_so_persediaan_format }} | {{ $total_so }} drugs | {{ $total->jumlah_tersedia }} items</span>
                    </div>
                </div>
            </div>


            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="far fa-copy"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Found (Stok Lebih)</span>
                        <?php $total_so_found_format = number_format($total->total_found, 2); ?>
                        <span class="info-box-number">Rp {{ $total_so_found_format }} | {{ $jumlah_item_found }} drugs | {{ $total->jumlah_found }} items</span>
                    </div>
                </div>
            </div>

             <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="far fa-copy"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Missing (Stok Hilang)</span>
                        <?php $total_so_missing_format = number_format($total->total_missing, 2); ?>
                        <span class="info-box-number">Rp {{ $total_so_missing_format }} | {{ $jumlah_item_missing }} drugs | {{ $total->jumlah_missing }} items</span>
                    </div>
                </div>
            </div>
        </div>
    <div class="card mb-12 border-left-primary card-info">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i>
                List Data Obat
                <small class="text-red"><b> | F2 : untuk fokus ke pencarian | </b></small> <small style="color: #FFEB3B;">Note : Stok yang diinputkan adalah stok fisik yang telah dikurangi jumlah penjualan.</small>
            </h3>
            </small>
            <span class="btn btn-sm btn-default float-right" onClick="export_data()"  data-toggle="modal" data-placement="top" title="Export Data Transfer"><i class="fa fa-file-excel" aria-hidden="true"></i> Export Data</span> 
        </div>
        <div class="card-body">
            <div class="row">
                
                <div class="form-group col-md-2">
                    <select id="so_status_aktif" name="so_status_aktif" class="form-control input_select">
                        <option value="1" {!!( "1" == $so_status_aktif ? 'selected' : '')!!}>Semua Data</option>
                        <option value="2" {!!( "2" == $so_status_aktif ? 'selected' : '')!!}>Data Selisih</option>
                        <option value="3" {!!( "3" == $so_status_aktif ? 'selected' : '')!!}>Stok Akhir 0</option>
                        <option value="4" {!!( "4" == $so_status_aktif ? 'selected' : '')!!}>Belum SO</option>
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <select id="tampil_popup" name="tampil_popup" class="form-control input_select">
                        <option value="1" {!!( "1" == $so_status_aktif ? 'selected' : '')!!}>Tampil Pop-Up</option>
                        <option value="2" {!!( "2" == $so_status_aktif ? 'selected' : '')!!}>Tidak Tampil Pop-Up</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <table  id="tb_m_stok_harga" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No.</th>
                                <th width="10%">Barcode</th>
                                <th width="20%">Nama</th>
                                <th width="10%">Harga Beli</th>
                                <th width="10%">Harga Jual</th>
                                <th width="5%">Oleh</th>
                                <th width="5%">Selisih</th>
                                <th width="10%">Stok Awal</th>
                                <th width="10%">Penjualan</th>
                                <th width="10%">Stok Akhir</th>
                                <th width="5%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('style')
    <style>
        .content-wrapper {
            /* height: 100% !important; */
        }
        .content {
            min-height: calc(100vh - calc(3.5rem + 1px) - calc(3.5rem + 1px));
        }
    </style>
@endsection

@section('script')
<script type="text/javascript">
    var token = '{{csrf_token()}}';
    var tb_m_stok_harga = $('#tb_m_stok_harga').dataTable( {
            processing: true,
            serverSide: true,
            stateSave: true,
            ajax:{
                    url: '{{url("stok_opnam/get_data")}}',
                    data:function(d){
                        d.so_status_aktif = $("#so_status_aktif").val();
                         }
                 },
            columns: [
                {data: 'no', name: 'no',width:"2%"},
                {data: 'barcode', name: 'barcode'},
                {data: 'nama', name: 'nama'},
                {data: 'harga_beli_ppn', name: 'harga_beli_ppn'},
                {data: 'harga_jual', name: 'harga_jual'},
                {data: 'so_by_nama', name: 'so_by_nama'},
                {data: 'selisih', name: 'selisih'},
                {data: 'stok_awal_so', name: 'stok_awal_so'},
                {data: 'total_penjualan_so', name: 'total_penjualan_so'},
                {data: 'stok_akhir', name: 'stok_akhir'},
                {data: 'action', name: 'id',orderable: true, searchable: true}
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

    $(document).on("keyup", function(e){
        var x = e.keyCode || e.which;
        if (x == 16) {  
            // fungsi shift -> add_row penjualan
        } else if(x==113){
            // fungsi F2 -> buka modal find obat
            $('div.dataTables_filter input').focus();
        } else if(x==115){
            // fungsi F4
        } else if(x==118){
            // fungsi F7
        } else if(x==119){
            // fungsi 
        } else if(x==120){
            // fungsi F9
        } else if(x==121){
            // fungsi F10
        }
    })

    $(document).ready(function(){
         $('#so_status_aktif').change(function(){
            $.ajax({
                url:'{{url("stok_opnam/set_so_status_aktif")}}',
                type: 'POST',
                data: {
                    _token      : "{{csrf_token()}}",
                    so_status_aktif: $('#so_status_aktif').val()
                },
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success:function(data){
                    //location.reload();
                    tb_m_stok_harga.fnDraw(false);
                }
            });
        });   
    })

    function export_data(){
        window.open("{{ url('stok_opnam/export') }}","_blank");
    }

    function edit_stok(id){
        $.ajax({
            type: "GET",
            url: '{{url("stok_opnam/edit_stok/")}}/'+id,
            async:true,
            data: {
                _token      : "{{csrf_token()}}",
            },
            beforeSend: function(data){
                // on_load();
                $('#modal-sm').find('.modal-sm').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
                $("#modal-sm .modal-title").html("Edit Data - Stok");
                $('#modal-sm').modal("show");
                $('#modal-sm').find('.modal-body-content').html('');
                $("#modal-sm").find(".overlay").fadeIn("200");
            },
                success:  function(data){
                $('#modal-sm').find('.modal-body-content').html(data);
            },
                complete: function(data){
                $("#modal-sm").find(".overlay").fadeOut("200");
            },
                error: function(data) {
                alert("error ajax occured!");
            }

        });
    }

    function set_data(obj){
        var id_obat = $("#id_obat").val();
        var id = $("#id").val();
        var stok_akhir_so = $("#stok_akhir_so").val();
        var stok_awal_so = $("#stok_awal_so").val();
        $.ajax({
            type:"POST",
            url : '{{url("stok_opnam/update_stok")}}',
            dataType : "json",
            data : {
                _token      : "{{csrf_token()}}",
                id : id,
                id_obat : id_obat,
                stok_awal_so : stok_awal_so,
                stok_akhir_so : stok_akhir_so,
            },
            beforeSend: function(data){
                // replace dengan fungsi loading
            },
            success:  function(data){
                if(data.status ==1){
                    show_info("Data obat berhasil disimpan...");
                    $('#modal-sm').modal('toggle');

                    var tampil_popup = $("#tampil_popup").val();
                    if(tampil_popup == 1) {
                        if(data.total_penjualan != 0 || data.selisih != 0) {
                            show_histori_stok(data.id);
                        }
                    }
                }else{
                    show_error("Gagal menyimpan data ini!");
                    return false;
                }
            },
            complete: function(data){
                // replace dengan fungsi mematikan loading
                tb_m_stok_harga.fnDraw(false);
            },
            error: function(data) {
                show_error("error ajax occured!");
            }

        });
    }

    function show_histori_stok(id){
        $.ajax({
            type: "GET",
            url: '{{url("stok_opnam/show_histori_stok/")}}/'+id,
            async:true,
            data: {
                _token      : "{{csrf_token()}}",
            },
            beforeSend: function(data){
                // on_load();
                $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
                $("#modal-xl .modal-title").html("Histori Stok");
                $('#modal-xl').modal("show");
                $('#modal-xl').find('.modal-body-content').html('');
                $("#modal-xl").find(".overlay").fadeIn("200");
            },
                success:  function(data){
                $('#modal-xl').find('.modal-body-content').html(data);
            },
                complete: function(data){
                $("#modal-xl").find(".overlay").fadeOut("200");
            },
                error: function(data) {
                alert("error ajax occured!");
            }

        });
    }

    function reload_stok_awal(id){
        swal({
            title: "Apakah anda yakin untuk melakukan reload data stok awal obat ini ?",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Ya",
            cancelButtonText: "Tidak",
            closeOnConfirm: false
        },
        function(){
            $.ajax({
                type: "POST",
                url: '{{url("stok_opnam/reload_stok_awal")}}',
                async:true,
                data: {
                    _token:token,
                    id:id,
                },
                beforeSend: function(data){
                    // replace dengan fungsi loading
                },
                success:  function(data){
                    if(data==1){
                        swal("Success!", "Reload stok awal berhasil!", "success");
                    }else{
                        swal("Gagal!", "Reload stok awal gagal!", "error");
                    }
                },
                complete: function(data){
                    tb_m_stok_harga.fnDraw(false);
                },
                error: function(data) {
                    swal("Error!", "Ajax occured.", "error");
                }
            });
        });
    }
</script>
@endsection
