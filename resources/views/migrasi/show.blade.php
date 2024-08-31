@extends('layout.app')

@section('title')
Detail Migrasi Kwitansi E-Payment
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Data Master</a></li>
    <li class="breadcrumb-item"><a href="{{url('kwitansi')}}">Data Kwitansi</a></li>
    <li class="breadcrumb-item"><a href="{{url('kwitansi-migrasi')}}">Migrasi Kwitansi E-payment</a></li>
    <li class="breadcrumb-item active" aria-current="page">Detail</li>
</ol>
@endsection

@section('content')
	<style type="text/css">
        .hide {
            display:none;
        }

		.select2 {
		  width: 100%!important; /* overrides computed width, 100px in your demo */
		}

        .table td, .table th {
            padding: 0.2rem!important;
            font-size:14px;
        }
	</style>


	<div class="card card-default card-outline" id="main-box" style="">
  		<div class="card-header">
        	<div class="row">
                <div class="col-md-12">   
                    <div class="callout callout-info">
                        <div class="row">
                                @if(!is_null($migrasi))
                                    <div class="col-2">
                                        <b>Tahun Ajaran</b><br>
                                        {{$tahun_ajaran->nama_tahun_ajar}}
                                    </div>
                                    <div class="col-2">
                                        <b>Status</b><br>
                                        @if($migrasi->status == 2)
                                            <b class="text-red">Gagal</b>
                                        @elseif($migrasi->status == 1)
                                            <b class="text-success">Berhasil</b>
                                        @else
                                        <b class="text-gray">Belum Mulai</b>
                                        @endif
                                    </div>

                                    <?php 
                                        if($DataEpayment->jumlah == $DataMigrasi->jumlah){
                                            $textcolor = 'text-success';
                                        } else {
                                            $textcolor = 'text-danger';
                                        }
                                    ?> 
                                    <div class="col-2">
                                        <b>Jumlah Data E-payment</b><br>
                                        {{number_format($DataEpayment->jumlah)}}
                                    </div>
                                    <div class="col-2">
                                        <b>Jumlah Data Migrasi</b><br>
                                        <b class="{{$textcolor}}">{{number_format($DataMigrasi->jumlah)}}</b>
                                    </div>
                                    <div class="col-2">
                                        <b>Jumlah Sequences</b><br>
                                        {{$migrasi->detail_migrasi->count()}}
                                    </div>
                                @else
                                    <div class="col-12"><i></i></div>
                                @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <a class="btn btn-danger btn-sm w-md m-b-5" title="kembali" href="{{url('kwitansi-migrasi')}}"><i class="fa fa-arrow-left"></i> Kembali</a>
                    <div class="btn btn-primary btn-sm w-md m-b-5" title="Mulai Migrasi Data" onclick="migrasi_init()"><i class="fa fa-sync"></i> Mulai Migrasi</div>
                </div>
            </div>
      	</div>
        <div class="card-body">
			<table  id="tb_migrasi_detail" class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th width="2%">No.</th>
                        <th width="10%">Kwitansi Awal</th>
                        <th width="10%">Kwitansi Akhir</th>
                        <th width="10%">Jumlah Data</th>
                        <th width="10%">Jumlah Migrasi</th>
                        <th width="10%">Status</th>
                        <th width="10%">Waktu Mulai</th>
                        <th width="10%">Waktu Selesai</th>
                        <th width="30%">Action</th>
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
    var tb_migrasi_detail = $('#tb_migrasi_detail').DataTable( {
            processing: true,
            serverSide: true,
            stateSave: true,
            ajax:{
                    url: '{{url("kwitansi-migrasi/listdetail")}}',
                    data:function(d){
                        d.id = "{{Crypt::encrypt($migrasi->id)}}";
                    }
                },
            columns: [
                {data: 'no', name: 'no',width:"2%"},
                {data: 'id_kwitansi_awal', name: 'id_kwitansi_awal', class:'text-center'},
                {data: 'id_kwitansi_akhir', name: 'id_kwitansi_akhir', class:'text-center'},
                {data: 'jml_data', name: 'jml_data', class:'text-right'},
                {data: 'jml_migrasi', name: 'jml_migrasi', class:'text-right'},
                {data: 'status', name: 'status', class:'text-center'},
                {data: 'start_at', name: 'start_at', class:'text-center'},
                {data: 'end_at', name: 'end_at', class:'text-center'},
                {data: 'action', name: 'id',orderable: true, searchable: true, class:'text-center'}
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
    });

    function RefreshData()
    {
        tb_migrasi_detail.draw(true);
    }

    function migrasi_init() {
        $.ajax({
            type: "POST",
            url: '{{url("kwitansi-migrasi/init")}}',
            async:true,
            dataType:'json',
            data: {
                _token:token,
                id:"{{Crypt::encrypt($migrasi->id)}}",
            },
            beforeSend: function(data){
                spinner.show();

                $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
                $("#modal-xl .modal-title").html("Migrasi Kwitansi");
                $('#modal-xl').find('.modal-body-content').html('');
                $("#modal-xl").find(".overlay").fadeIn("200");
            },
            success:  function(data){
                spinner.hide();
                if(data.status){
                    $('#modal-xl').find('.modal-body-content').html(data.view);
                    $('#modal-xl').modal("show");
                }else{
                    swal("Terjadi Kesalahan", "Tidak dapat menampilkan dialog migrasi data.", "error");
                }
            },
            complete: function(data){
                    
            },
            error: function(data) {
                swal("Terjadi Kesalahan", "Tidak dapat menampilkan dialog migrasi data.", "error");
                spinner.hide();
            }
        });
    }


    function migrasi_init_squence(id,seq)
    {
        $.ajax({
            type: "POST",
            url: '{{url("kwitansi-migrasi/init")}}',
            async:true,
            dataType:'json',
            data: {
                _token:token,
                seq:seq,
                id:id,
            },
            beforeSend: function(data){
                spinner.show();

                $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
                $("#modal-xl .modal-title").html("Migrasi Kwitansi - Squence "+seq);
                $('#modal-xl').find('.modal-body-content').html('');
                $("#modal-xl").find(".overlay").fadeIn("200");
            },
            success:  function(data){
                spinner.hide();
                if(data.status){
                    $('#modal-xl').find('.modal-body-content').html(data.view);
                    $('#modal-xl').modal("show");
                }else{
                    swal("Terjadi Kesalahan", "Tidak dapat menampilkan dialog migrasi data.", "error");
                }
            },
            complete: function(data){
                    
            },
            error: function(data) {
                swal("Terjadi Kesalahan", "Tidak dapat menampilkan dialog migrasi data.", "error");
                spinner.hide();
            }
        });
    }



    function showSquence(id,seq)
    {
        $.ajax({
            type: "POST",
            url: '{{url("kwitansi-migrasi/squence")}}',
            async:true,
            dataType:'json',
            data: {
                _token:token,
                seq:seq,
                id:id,
            },
            beforeSend: function(data){
                spinner.show();

                $('#modal-md').find('.modal-md').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
                $("#modal-md .modal-title").html("Detail Data Migrasi - Squence "+seq);
                $('#modal-md').find('.modal-body-content').html('');
                $("#modal-md").find(".overlay").fadeIn("200");
            },
            success:  function(data){
                spinner.hide();
                if(data.status){
                    $('#modal-md').find('.modal-body-content').html(data.view);
                    $('#modal-md').modal("show");
                }else{
                    swal("Terjadi Kesalahan", "Tidak dapat menampilkan dialog migrasi data.", "error");
                }
            },
            complete: function(data){
                
                $('#tb_detail').DataTable({
                                    processing: true,
                                    serverSide: true,
                                    stateSave: true,
                                    ajax:{
                                            url: '{{url("kwitansi-migrasi/listdetailsquence")}}',
                                            data:function(d){
                                                d.id = id;
                                            }
                                        },
                                    columns: [
                                        {data: 'no', name: 'no',width:"2%"},
                                        {data: 'id_old', name: 'id_old', class:'text-center'},
                                        {data: 'jumlah', name: 'jumlah', class:'text-center'},
                                        {data: 'detail_nominal', name: 'detail_nominal', class:'text-center'},
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

            },
            error: function(data) {
                swal("Terjadi Kesalahan", "Tidak dapat menampilkan dialog migrasi data.", "error");
                spinner.hide();
            }
        });
    }



    function resetSquence(id,seq)
    {
        swal({
            title: "Apakah anda yakin ingin reset data migrasi pada squence "+seq+"? Data yang sudah hilang tidak bisa kembali lagi.",
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
                url: '{{url("kwitansi-migrasi/reset")}}/'+id,
                async:true,
                dataType:'json',
                data: {
                    _token:token,
                    id:id
                },
                beforeSend: function(data){
                    // replace dengan fungsi loading
                },
                success:  function(data){
                    if(data.status){
                        RefreshData();
                        swal("",data.message, "success");
                    } else {
                        swal("Terjadi Kesalahan", data.message, "error");
                    }
                },
                complete: function(data){
                    // tb_migrasi_detail.fnDraw(false);
                },
                error: function(data) {
                    swal("Error!", "Ajax occured.", "error");
                }
            });
        });
    }






</script>
@endsection