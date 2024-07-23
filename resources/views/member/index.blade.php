@extends('layout.app')

@section('title')
Data Member
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Data Master</a></li>
    <li class="breadcrumb-item"><a href="#">Data Member</a></li>
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
			<a class="btn btn-success w-md m-b-5" href="{{url('member/create')}}"><i class="fa fa-plus"></i> Tambah Data</a>
	    </div>
	</div>

	<div class="card card-info card-outline" id="main-box" style="">
  		<div class="card-header">
        	<h3 class="card-title">
          		<i class="fas fa-list"></i>
   				List Data Member
        	</h3>
      	</div>
        <?php
            $first = date('Y-m-d');
            $last = date('Y-m-t');
        ?>
        <div class="card-body">
            <hr>
            <form role="form" id="searching_form">
                <div class="row">
                    <div class="form-group  col-md-2">
                        <label>Dari Tanggal Transaksi</label>
                        <input type="text" name="tgl_awal"  id="tgl_awal"  value="{{ $first }}" class="datepicker form-control" autocomplete="off">
                    </div>
                    <div class="form-group  col-md-2">
                        <label>Sampai Tanggal Transaksi</label>
                        <input type="text" name="tgl_akhir" id="tgl_akhir" value="{{ $last }}" class="datepicker form-control" autocomplete="off">
                    </div>

                    <div class="col-lg-5">
                        <label>- Action -</label><br>
                        <!-- <button type="submit" class="btn btn-info" id="datatable_filter"><i class="fa fa-search"></i> Cari</button>  -->
                        <span class="btn bg-secondary" onClick="export_detail_transaksi()"  data-toggle="modal" data-placement="top" title="Export Data"><i class="fa fa-download" aria-hidden="true"></i> Export Data Transaksi</span> 
                    </div>
                </div>
            </form>
            <hr>
			<table  id="tb_member" class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Group Apotek</th>
                        <th>Telepon</th>
                        <th>Email</th>
                        <th>Total Transaksi</th>
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
    var tb_member = $('#tb_member').dataTable( {
            processing: true,
            serverSide: true,
            stateSave: true,
            ajax:{
                    url: '{{url("member/list_member")}}',
                    data:function(d){
                         }
                 },
            columns: [
                {data: 'no', name: 'no',width:"2%"},
                {data: 'nama', name: 'nama'},
                {data: 'username', name: 'username'},
                {data: 'id_group_apotek', name: 'id_group_apotek'},
                {data: 'telepon', name: 'telepon'},
                {data: 'email', name: 'email'},
                {data: 'total_transaksi', name: 'total_transaksi', class:'text-center bg-info disabled color-palette'},
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

    $(document).ready(function(){
    })

    function delete_member(id){
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
                url: '{{url("member")}}/'+id,
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
                        swal("Deleted!", "Data member berhasil dihapus.", "success");
                    }else{
                        
                        swal("Failed!", "Gagal menghapus data member.", "error");
                    }
                },
                complete: function(data){
                    tb_member.fnDraw(false);
                },
                error: function(data) {
                    swal("Error!", "Ajax occured.", "error");
                }
            });
        });
    }

    function submit_valid(id){
        status = $(".validated_form").valid();

        if(status) {
            data = {};
            $("#form-edit").find("input[name], select").each(function (index, node) {
                data[node.name] = node.value;
                
            });

            $.ajax({
                type:"PUT",
                url : '{{url("member/")}}/'+id,
                dataType : "json",
                data : data,
                beforeSend: function(data){
                    // replace dengan fungsi loading
                },
                success:  function(data){
                    if(data.status ==1){
                        show_info("Data member berhasil disimpan...");
                        $('#modal-large').modal('toggle');
                    }else{
                        show_error("Gagal menyimpan data ini !");
                        return false;
                    }
                },
                complete: function(data){
                    // replace dengan fungsi mematikan loading
                    tb_member.fnDraw(false);
                },
                error: function(data) {
                    show_error("error ajax occured!");
                }

            })
        }
    }

    function edit_data(id){
        $.ajax({
            type: "GET",
            url: '{{url("member")}}/'+id+'/edit',
            async:true,
            data: {
                _token      : "{{csrf_token()}}",
            },
            beforeSend: function(data){
                // on_load();
                $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
                $("#modal-xl .modal-title").html("Edit Data - Member");
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

    function export_detail_transaksi(){
        window.open("{{ url('member/export/detail') }}"+"/"+$('#tgl_awal').val()+"/"+$('#tgl_akhir').val(),"_blank");
    }
</script>
@endsection