@extends('layout.app')

@section('title')
Daftar Akun
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Daftar Akun</a></li>
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
            <a class="btn btn-success w-md m-b-5" href="{{url('kode_akuntansi/create')}}"><i class="fa fa-plus"></i> Tambah Data</a>
			<a class="btn btn-default bg-purple w-md m-b-5" href="{{url('jurnalumum/create')}}"><i class="fa fa-plus"></i> Tambah Jurnal Umum</a>
            <a class="btn btn-primary w-md m-b-5" href="{{url('jurnalumum/saldoawal')}}"><i class="fa fa-edit"></i> Set Saldo Awal</a>
            <div onclick="importdata()" class="btn btn-warning bg-yellow w-md m-b-5"><i class="fa fa-upload"></i> Import Jurnal</div>
            <div onclick="reloaddata()" class="btn btn-info w-md m-b-5"><i class="fa fa-sync"></i> &nbsp;Reload Data</div>
	    </div>
	</div>

	<div class="card card-info card-outline" id="main-box" style="">
  		<div class="card-header">
        	<h3 class="card-title">
          		<i class="fas fa-list"></i>
   				List Data Akun
        	</h3>
      	</div>
        <div class="card-body">
			<table  id="tb_jurnal" class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Akun</th>
                        <th>Nama Akun</th>
                        <th>Kategori Akun</th>
                        <th>Saldo</th>
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
                    url: '{{url("jurnalumum/list_jurnalumum")}}',
                    data:function(d){
                            //d.id_apotek   = $('#id_apotek').val();
                            //d.id_apoteker         = $('#id_apoteker').val();
                         }
                 },
            columns: [
                {data: 'no', name: 'no'},
                {data: 'kode_akun', name: 'kode_akun'},
                {data: 'nama_akun', name: 'nama_akun'},
                {data: 'kategori_akun', name: 'kategori_akun'},
                /*{data: 'pajak', name: 'pajak'},*/
                {data: 'saldo', name: 'saldo'},
                {data: 'action', name: 'id',orderable: false, searchable: false, class: "text-center"}
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





    function edit_data(id){
        $.ajax({
            type: "GET",
            url: '{{url("kode_akuntansi")}}/'+id+'/edit',
            async:true,
            data: {
                _token      : "{{csrf_token()}}",
            },
            beforeSend: function(data){
                // on_load();
                $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
                $("#modal-xl .modal-title").html("Edit Data - Kode Akuntansi");
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

    function submit_valid(id){
        if($(".validated_form").valid()) {
            data = {};
            $("#form-edit").find("input[name], select").each(function (index, node) {
                data[node.name] = node.value;
                
            });

            $.ajax({
                type:"PUT",
                url : '{{url("kode_akuntansi/")}}/'+id,
                dataType : "json",
                data : data,
                beforeSend: function(data){
                    // replace dengan fungsi loading
                },
                success:  function(data){
                    if(data.status ==1){
                        show_info("Data kode akuntansi berhasil disimpan!");
                        $('#modal-large').modal('toggle');
                    }else{
                        show_error("Gagal menyimpan data ini!");
                        return false;
                    }
                },
                complete: function(data){
                    // replace dengan fungsi mematikan loading
                    tb_jurnal.fnDraw(false);
                },
                error: function(data) {
                    show_error("error ajax occured!");
                }

            })
        } else {
            return false;
        }
    }


    function delete_kode_akuntansi(id){
        swal({
            title: "Apakah anda yakin menghapus data ini?",
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
                url: '{{url("kode_akuntansi")}}/'+id,
                async:true,
                data: {
                    _token:token,
                    id:id
                },
                beforeSend: function(data){
                    // replace dengan fungsi loading
                },
                success:  function(data){
                    if(data==1){
                        swal("Deleted!", "Data kode akuntansi berhasil dihapus.", "success");
                    }else{
                        
                        swal("Failed!", "Gagal menghapus data kode akuntansi.", "error");
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
                url: '{{url("jurnalumum/tutupbuku")}}',
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
            url: '{{url("jurnalumum/ImportJurnal")}}',
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


    function reloaddata(){
        $.ajax({
            type: "GET",
            url: '{{url("jurnalumum/ReloadData")}}',
            async:true,
            data: {_token:token},
            beforeSend: function(data){
              // on_load();
            $('#modal-xl').data('backdrop',"static");
            $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
            $("#modal-xl .modal-title").html("Reload Data");
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
</script>
@endsection