@extends('layout.app')

@section('title')
Data Aset
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Data Aset</a></li>
    <li class="breadcrumb-item active" aria-current="page">Index</li>
</ol>
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

<div class="card card-info card-outline mb-12 border-left-primary">
    <div class="card-body">
        <h4><i class="fa fa-info"></i> Informasi</h4>
        <p>Untuk pencarian, isikan kata yang ingin dicari pada kolom Search, lalu tekan enter.</p>
        <a class="btn btn-success w-md m-b-5" href="{{url('manajemen_aset/create')}}"><i class="fa fa-plus"></i> Tambah Data</a>
    </div>
</div>

<div class="card card-info card-outline" id="main-box" style="">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list"></i>
            List Data Aset
        </h3>
    </div>
    <div class="card-body">
        <form role="form" id="searching_form">
            <div class="row">
                <div class="col-lg-2 form-group">
                    <label>No. Transaksi</label>
                    <input type="text" id="no_transaksi" class="form-control" placeholder="Masukan Nomer Transaksi" autocomplete="off">
                </div>
                <div class="form-group col-md-2">
                    <label>Dari Tanggal</label>
                    <input type="text" name="tgl_awal"  id="tgl_awal" class="datepicker form-control" autocomplete="off">
                </div>
                <div class="form-group col-md-2">
                    <label>Sampai Tanggal</label>
                    <input type="text" name="tgl_akhir" id="tgl_akhir" class="datepicker form-control" autocomplete="off">
                </div>
                <div class="col-lg-6 form-group">
                    <label>Keterangan</label>
                    <input type="text" id="keterangan" class="form-control" placeholder="Masukan Keterangan Transaksi" autocomplete="off">
                </div>
                <div class="col-lg-12" style="text-align: center;">
                    <button type="submit" class="btn btn-primary" id="datatable_filter"><i class="fa fa-search"></i> Cari</button> 
                </div>
            </div>
        </form>
        <hr>
        <table id="tb_aset" class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th width="5%">No.</th>
                    <th width="10%">Tanggal</th>
                    <th width="15%">No. Transaksi</th>
                    <th width="55%">Keterangan</th>
                    <th width="15%">Action</th>
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
    var tb_aset = $('#tb_aset').dataTable({
        processing: true,
        serverSide: true,
        stateSave: true,
        ajax: {
            url: '{{url("manajemen_aset/list_data_aset")}}',
            data:function(d){
                d.no_transaksi = $('#no_transaksi').val();
                d.tgl_awal = $('#tgl_awal').val();
                d.tgl_akhir = $('#tgl_akhir').val();
                d.keterangan = $("#keterangan").val();
             }
        },
        columns: [{
                data: 'no',
                name: 'no',
                width: "2%"
            },
            {
                data: 'tgl_transaksi',
                name: 'tgl_transaksi'
            },
            {
                data: 'no_transaksi',
                name: 'no_transaksi'
            },
            {
                data: 'keterangan',
                name: 'keterangan'
            },
            {
                data: 'action',
                name: 'id',
                orderable: true,
                searchable: true
            }
        ],
        rowCallback: function(row, data, iDisplayIndex) {
            var api = this.api();
            var info = api.page.info();
            var page = info.page;
            var length = info.length;
            var index = (page * length + (iDisplayIndex + 1));
            $('td:eq(0)', row).html(index);
        },
        stateSaveCallback: function(settings, data) {
            localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data))
        },
        stateLoadCallback: function(settings) {
            return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance))
        },
        drawCallback: function(settings) {
            var api = this.api();
        }
    });
    $(document).ready(function() {
        $("#searching_form").submit(function(e){
            e.preventDefault();
            tb_aset.fnDraw(false);
        });
        $('.input_select').select2({});
        $('#tgl_awal, #tgl_akhir').datepicker({
            autoclose:true,
            format:"yyyy-mm-dd",
            forceParse: false
        });
    })
    
    function delete_data(id) {
        swal({
                title: "Apakah anda yakin menghapus data ini?",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Ya",
                cancelButtonText: "Tidak",
                closeOnConfirm: false
            },
            function() {
                $.ajax({
                    type: "DELETE",
                    url: '{{url("manajemen_aset")}}/' + id,
                    async: true,
                    data: {
                        _token: token,
                        id: id
                    },
                    beforeSend: function(data) {
                        // replace dengan fungsi loading
                    },
                    success: function(data) {
                        if (data == 1) {
                            swal("Deleted!", "Data aset berhasil dihapus.", "success");
                        } else {
                            swal("Failed!", "Gagal menghapus data aset.", "error");
                        }
                    },
                    complete: function(data) {
                        tb_aset.fnDraw(false);
                    },
                    error: function(data) {
                        swal("Error!", "Ajax occured.", "error");
                    }
                });
            });
    }
    
</script>
@endsection