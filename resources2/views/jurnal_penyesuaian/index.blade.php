@extends('layout.app')

@section('title')
Daftar Akun
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Jurnal Penyesuaian</a></li>
    <li class="breadcrumb-item active" aria-current="page">Index</li>
</ol>
@endsection

@section('content')
	<style type="text/css">
		.select2 {
		  width: 100%!important; /* overrides computed width, 100px in your demo */
		}
	</style>

	<div class="card card-info card-outline mb-12 border-left-primary">
	    <div class="card-body">
	      	<h4><i class="fa fa-info"></i> Informasi</h4>
	      	<p>Untuk pencarian, isikan kata yang ingin dicari pada kolom seacrh, lalu tekan enter.</p>
			<a class="btn btn-success w-md m-b-5" href="{{url('jurnalpenyesuaian/create')}}"><i class="fa fa-plus"></i> Tambah Jurnal Penyesuaian</a>
            <div onclick="importdata()" class="btn btn-warning bg-yellow w-md m-b-5"><i class="fa fa-upload"></i> Import Jurnal</div>
	    </div>
	</div>

	<div class="card card-info card-outline" id="main-box" style="">
  		<div class="card-header">
        	<h3 class="card-title">
          		<i class="fas fa-list"></i>
   				List Data Jurnal Penyesuaian
        	</h3>
      	</div>
        <div class="card-body">
			<table  id="tb_jurnal" class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nomor</th>
                        <th>Total Debit (IDR)</th>
                        <th>Total Kredit (IDR)</th>
                        <th>Status</th>
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
    var tb_jurnal = $('#tb_jurnal').dataTable( {
            processing: true,
            serverSide: true,
            stateSave: true,
            ajax:{
                    url: '{{url("jurnalpenyesuaian/listdata")}}',
                    data:function(d){
                            //d.id_apotek   = $('#id_apotek').val();
                            //d.id_apoteker         = $('#id_apoteker').val();
                         }
                 },
            columns: [
                {data: 'tgl_transaksi', name: 'tgl_transaksi'},
                {data: 'no_transaksi', name: 'no_transaksi'},
                {data: 'total_debit', name: 'total_debit', class:'text-right'},
                {data: 'total_kredit', name: 'total_kredit', class:'text-right'},
                {data: 'is_tutup_buku', name: 'is_tutup_buku', class:'text-center'},
                {data: 'action', name: 'id',orderable: false, searchable: false, class:'text-center'}
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



    $('#myModal').on('shown', function () {
          $('#myModal').modal('hide');
    });


    function deletejurnal(id){
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
                url: '{{url("jurnalpenyesuaian")}}/'+id,
                async:true,
                data: {
                    _token:"{{csrf_token()}}"
                },
                beforeSend: function(data){
                    // replace dengan fungsi loading
                },
                success:  function(data){
                    if(data==1){
                        swal("Deleted!", "Detail Jurnal berhasil dihapus.", "success");
                    }else{
                        
                        swal("Failed!", "Gagal menghapus detail jurnal.", "error");
                    }
                },
                complete: function(data){
                    tb_jurnal.fnDraw(false);
                },
                error: function(data) {
                    swal("Error!", "Ajax occured.", "error");
                }
            });
        });
    }



    function tutupbuku(){
        swal({
            title: "Apakah anda yakin ingin menjalankan proses TUTUP BUKU?",
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
                url: '{{url("jurnalpenyesuaian/tutupbuku")}}',
                async:true,
                dataType:"json",
                data: {
                    _token:token
                },
                beforeSend: function(data){
                    // replace dengan fungsi loading
                },
                success:  function(data){
                    if(data.status==1){
                        swal("Berhasil !", "Proses tutup buku berhasil.", "success");
                    }else{                        
                        swal(data.errorMessages, "error");
                    }
                },
                complete: function(data){
                    tb_jurnal.fnDraw(false);
                },
                error: function(data) {
                    swal("Error!", "Ajax occured.", "error");
                }
            });
        });
    }


    function importdata(){
        $.ajax({
            type: "GET",
            url: '{{url("jurnalpenyesuaian/ImportJurnal")}}',
            async:true,
            data: {_token:token},
            beforeSend: function(data){
              // on_load();
            $('#modal-lg').data('backdrop',"static");
            $('#modal-lg').find('.modal-lg').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
            $("#modal-lg .modal-title").html("Import Jurnal Penyesuaian");
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
</script>
@endsection