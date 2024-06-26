@extends('layout.app')

@section('title')
Biaya Pengeluaran
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Biaya</a></li>
    <li class="breadcrumb-item active" aria-current="page">Index</li>
</ol>
@endsection

@section('content')
	<style type="text/css">
		.select2 {
		  width: 100%!important; /* overrides computed width, 100px in your demo */
		}

        .total {
            font-size: 25px;
            text-align: right;
            font-weight: bold;
        }
	</style>

    <div class="row">
        <div class="col-sm-4">
            <div class="card card-info card-outline mb-12 border-left-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        Total Biaya Bulan ini (dalam IDR)
                    </h3>
                    <div class="card-tools"></div>
                </div>
                <div class="card-body total">
                    Rp. &nbsp;{{number_format($getbulanini->total)}}
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card card-info card-outline mb-12 border-left-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        Biaya 30 Hari Terakhir (dalam IDR)
                    </h3>
                    <div class="card-tools"></div>
                </div>
                <div class="card-body total">
                    Rp. &nbsp;{{number_format($get30hari->total)}}
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card card-info card-outline mb-12 border-left-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        Biaya biaya belum dibayar (dalam IDR)
                    </h3>
                    <div class="card-tools"></div>
                </div>
                <div class="card-body total">
                    Rp. &nbsp;{{number_format($belumlunas->total)}}
                </div>
            </div>
        </div>
    </div>
	

	<div class="card card-info card-outline" id="main-box" style="">
  		<div class="card-header">
        	<h3 class="card-title">
          		<i class="fas fa-list"></i>
   				Daftar Biaya
        	</h3>
            <div class="card-tools">
                <a class="btn btn-success w-md m-b-5" href="{{url('biaya/create')}}"><i class="fa fa-plus"></i> Tambah Biaya Baru</a>
                <div onclick="importdata()" class="btn btn-warning bg-yellow w-md m-b-5"><i class="fa fa-upload"></i> Import Biaya</div>
            </div>
      	</div>
        <div class="card-body">
			<table  id="tb_biaya" class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nomor</th>
                        <!-- <th>Kategori</th> -->
                        <th>Penerima</th>
                        <th>Status</th>
                        <th>Sisa Tagihan</th>
                        <th>Total</th>
                        <th>Tags</th>
                        <th>Action</th>
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
    var tb_biaya = $('#tb_biaya').dataTable( {
            processing: true,
            serverSide: true,
            stateSave: true,
            ajax:{
                    url: '{{url("biaya/list_biaya")}}',
                    data:function(d){
                            //d.id_apotek   = $('#id_apotek').val();
                            //d.id_apoteker         = $('#id_apoteker').val();
                         }
                 },
            columns: [
                {data: 'tgl_transaksi', name: 'tgl_transaksi'},
                {data: 'no_biaya', name: 'no_biaya'},
                {data: 'id_penerima', name: 'id_penerima'},
                {data: 'status', class: 'text-center', name: 'status'},
                {data: 'sisatagihan', name: 'sisatagihan'},
                {data: 'total', name: 'total',class: 'text-right'},
                {data: 'tag', name: 'tag'},
                {data: 'action', name: 'id',class: 'text-center',orderable: false, searchable: false}
            ],
            rowCallback: function( row, data, iDisplayIndex ) {
                /*var api = this.api();
                var info = api.page.info();
                var page = info.page;
                var length = info.length;
                var index = (page * length + (iDisplayIndex +1));
                $('td:eq(0)', row).html(index);*/
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



    $('#myModal').on('shown', function () {
          $('#myModal').modal('hide');
    })


    function importdata(){
        $.ajax({
            type: "GET",
            url: '{{url("biaya/ImportBiaya")}}',
            async:true,
            data: {_token:token},
            beforeSend: function(data){
              // on_load();
            $('#modal-lg').data('backdrop',"static");
            $('#modal-lg').find('.modal-lg').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
            $("#modal-lg .modal-title").html("Import Jurnal Umum");
            $('#modal-lg').modal("show");
            $('#modal-lg').find('.modal-body-content').html('');
            $("#modal-lg").find(".overlay").fadeIn("200");
            },
            success:  function(data){
              $('#modal-lg').find('.modal-body-content').html(data);
            },
            complete: function(data){
                $("#modal-lg").find(".overlay").fadeOut("200");
            },
              error: function(data) {
                alert("error ajax occured!");
              }
        });
    }


    function updateStatus(id,st){
        if(st==1){
            title = "Apakah anda yakin set status Open kembali?";
        } else {
            title = "Apakah anda yakin set status Closed?";
        }

        swal({
            title: title,
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
                url: '{{url("biaya")}}/status/'+id,
                async:true,
                dataType:"json",
                data: {
                    _token:"{{csrf_token()}}",
                    st:st
                },
                beforeSend: function(data){
                    // replace dengan fungsi loading
                },
                success:  function(data){
                    if(data.status==1){
                        swal("Updated!", "Data berhasil disimpan.", "success");
                    } else if(data.status == 2){                        
                        swal("Failed!", data.error, "error");
                    }
                },
                complete: function(data){
                    tb_biaya.fnDraw(false);
                },
                error: function(data) {
                    swal("Error!", "Ajax occured.", "error");
                }
            });
        });
    }


    function deletedata(id){
        swal({
            title: "Apakah anda yakin menghapus data?",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Ya",
            cancelButtonText: "Tidak",
            closeOnConfirm: false
        },
        function(){
            $.ajax({
                type: "DELETE",
                url: '{{url("biaya")}}/'+id,
                async:true,
                dataType:"json",
                data: {
                    _token:"{{csrf_token()}}"
                },
                beforeSend: function(data){
                    // replace dengan fungsi loading
                },
                success:  function(data){
                    if(data.status==1){

                        /*swal("Deleted!", "Data Biaya berhasil dihapus.", "success");*/

                        if(data.statusjurnal==1){
                            swal("Deleted!", "Data berhasil dihapus.", "success");
                        } else {
                            swal("Warning!", "Data biaya berhasil dihapus. Gagal menghapus data jurnal terkait biaya.", "warning");
                        }

                    }else if(data.status == 2){                        
                        swal("Failed!", "Data biaya tidak ditemukan.", "error");
                    }else{                        
                        swal("Failed!", "Gagal menghapus data biaya.", "error");
                    }
                },
                complete: function(data){
                    tb_biaya.fnDraw(false);
                },
                error: function(data) {
                    swal("Error!", "Ajax occured.", "error");
                }
            });
        });
    }
</script>
@endsection