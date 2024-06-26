@extends('layout.app')

@section('title')
Data Transfer Outlet
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Transaksi</a></li>
    <li class="breadcrumb-item"><a href="#">Data Transfer Outlet</a></li>
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
	      	<p>Informasi/SOP pada menu ini dapat dilihat pada menu <b>informasi</b>.</p>
            <!-- <a class="btn btn-warning w-md m-b-5" href="#" onclick="informasi()"><i class="fa fa-exclamation-triangle"></i> Informasi</a> -->
            <a class="btn btn-success w-md m-b-5" href="{{url('transfer_outlet/create')}}"><i class="fa fa-plus"></i> Tambah Transfer</a>
            <a class="btn btn-success w-md m-b-5" href="{{url('transfer_outlet/create_margin')}}"><i class="fa fa-plus"></i> Tambah Transfer Margin</a>
	    </div>
	</div>

	<div class="card card-info card-outline" id="main-box" style="">
  		<div class="card-header">
        	<h3 class="card-title">
          		<i class="fas fa-list"></i>
   				List Data Transfer Outlet
        	</h3>
      	</div>
        <div class="card-body">
        	<form role="form" id="searching_form">
                <!-- text input -->
                <div class="row">
                    <div class="col-lg-4 form-group">
                        <label>ID Nota</label>
                        <input type="text" id="search_id" class="form-control" placeholder="Masukan ID Nota" autocomplete="off">
                    </div>
                    <div class="form-group  col-md-2">
                        <label>Dari Tanggal</label>
                        <input type="text" name="tgl_awal"  id="tgl_awal" class="datepicker form-control" value="{{ $first_day }}" autocomplete="off">
                    </div>
                    <div class="form-group  col-md-2">
                        <label>Sampai Tanggal</label>
                        <input type="text" name="tgl_akhir" id="tgl_akhir" class="datepicker form-control" value="{{ $date_now }}"autocomplete="off">
                    </div>
                    <div class="col-lg-4 form-group">
                        <label>Apotek Tujuan</label>
                        <select class="form-control input_select" id="id_apotek" name="id_apotek" autocomplete="off">
                            <option value=""> --- Pilih Apotek ---</option>
                            @foreach($apoteks as $obj)
                            <option value="{{$obj->id}}"> {{ $obj->nama_panjang }} </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-12" style="text-align: center;">
                        <button type="submit" class="btn btn-primary" id="datatable_filter"><i class="fa fa-search"></i> Cari</button>
                        <span class="btn bg-olive" onClick="export_data_transfer()"  data-toggle="modal" data-placement="top" title="Export Data Transfer"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export</span> 
                    </div>
                </div>
            </form>
			<hr>
            <div class="row">
                <div class="col-lg-6">
                    <div class="card card-secondary">
                        <div class="card-header border-transparent">
                            <h3 class="card-title">Transfer Masuk</h3>
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
                                <div id="data_transfer_masuk"></div>
                            </div>
                            <!-- /.table-responsive -->
                        </div>
                        <!-- /.card-body -->
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card card-secondary">
                        <div class="card-header border-transparent">
                            <h3 class="card-title">Transfer Keluar</h3>
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
                                <div id="data_transfer_keluar"></div>
                            </div>
                            <!-- /.table-responsive -->
                        </div>
                        <!-- /.card-body -->
                    </div>
                </div>
            </div>
            <hr>
			<table  id="tb_data_transfer" class="table table-bordered table-striped table-hover">
		    	<thead>
			        <tr>
                        <th width="3%" class="text-center">No.</th>
                        <th width="5%" class="text-center">ID Nota</th>
                        <th width="10%" class="text-center">Tanggal</th>
                        <th width="20%" class="text-center">Apotek Asal</th>
                        <th width="20%" class="text-center">Apotek Tujuan</th>
                        <th width="10%" class="text-center">Total</th>
                        <th width="7%" class="text-center">Lunas ?</th>
                        <th width="5%" class="text-center">Status</th>
                        <th width="5%" class="text-center">Sign</th>
                        <th width="15%" class="text-center">Action</th>
                    </tr>
		        </thead>
		        <tbody>
		        </tbody>
			</table>
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
 	

 	var tb_data_transfer = $('#tb_data_transfer').DataTable( {
		paging:true,
        processing: true,
        serverSide: true,
        stateSave: true,
        scrollX: true,
        ajax: {
            url: '{{url("transfer_outlet/list_transfer_outlet")}}',
		        data:function(d){
		        	d.id         = $('#search_id').val();
                    d.id_apotek_tujuan = $('#id_apotek').val();
                    d.tgl_awal = $("#tgl_awal").val();
                    d.tgl_akhir = $("#tgl_akhir").val();
		         }
        },
        order: [],
        columns: [
        	{data: 'no', name: 'no',width:"2%", class:'text-center'},
            {data: 'id', name: 'id', class:'text-center'},
            {data: 'tgl_nota', name: 'tgl_nota', class:'text-center'},
            {data: 'id_apotek_asal', name: 'id_apotek_asal', class:'text-center'},
            {data: 'id_apotek_tujuan', name: 'id_apotek_tujuan'},
            {data: 'total', name: 'total', class:'text-center'},
            {data: 'is_lunas', name: 'is_lunas', class:'text-center'},
            {data: 'is_status', name: 'is_status', class:'text-center'},
            {data: 'is_sign', name: 'is_sign', class:'text-center'},
            {data: 'action', name: 'id',orderable: true, searchable: true}
        ],
        drawCallback: function(callback) {
            $("#btn_set").html(callback['jqXHR']['responseJSON']['btn_set']);
            //console.log(callback['jqXHR']['responseJSON']['btn_set'])
        }
	});


 	setTimeout(function(){
        $('#tb_data_transfer .checkAlltogle').prop('checked', false);
    }, 1);

	$(document).ready(function(){
		$("#searching_form").submit(function(e){
			e.preventDefault();
			tb_data_transfer.draw(false);
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

    function delete_transfer(id){
        swal({
            title: "Apakah anda yakin menghapus data transfer ini?",
            text: "Setelah data terhapus, stok juga akan diupdate kembali.",
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
                url: '{{url("transfer_outlet")}}/'+id,
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
                        swal("Deleted!", "Data transfer berhasil dihapus.", "success");
                    }else{
                        
                        swal("Failed!", "Gagal menghapus data transfer.", "error");
                    }
                },
                complete: function(data){
                    tb_data_transfer.draw(false);
                },
                error: function(data) {
                    swal("Error!", "Ajax occured.", "error");
                }
            });
        });
    }

    function cetak_nota(id){
        $.ajax({
            type: "POST",
            url: '{{url("transfer_outlet/cetak_nota")}}',
            async:true,
            data: {
                _token:"{{csrf_token()}}",
                id:id

            },
            beforeSend: function(data){
              // on_load();
            $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
            $("#modal-xl .modal-title").html("Cetak Nota Transfer Internal");
            $('#modal-xl').modal("show");
            $('#modal-xl').find('.modal-body-content').html('');
            $("#modal-xl").find(".overlay").fadeIn("200");
            },
            success:  function(data){
              $('#modal-xl').find('.modal-body-content').html(data);
            },
            complete: function(data){
                $("#modal-xl").find(".overlay").fadeOut("200");
                startConnection();
            },
              error: function(data) {
                alert("error ajax occured!");
              }
        });
    }

    function export_data_transfer(){
        window.open("{{ url('transfer_outlet/export') }}"+ "?id="+$('#search_id').val()+"&id_apotek_tujuan="+$('#id_apotek').val()+"&tgl_awal="+$('#tgl_awal').val()+"&tgl_akhir="+$('#tgl_akhir').val(),"_blank");
    }

    function cari_info() {
        $.ajax({
            type: "GET",
            url: '{{url("transfer_outlet/cari_info")}}',
            async:true,
            data: {
                _token:token,
                id         : $('#search_id').val(),
                id_apotek_tujuan : $('#id_apotek').val(),
                tgl_awal : $("#tgl_awal").val(),
                tgl_akhir : $("#tgl_akhir").val(),
            },
            beforeSend: function(data){
                // replace dengan fungsi loading
            },
            success:  function(data){
                $("#data_transfer_masuk").html(data.data_transfer_masuk);
                $("#data_transfer_keluar").html(data.data_transfer_keluar);
            },
            complete: function(data){
                
            },
            error: function(data) {
                swal("Error!", "Ajax occured.", "error");
            }
        });
    }

    function informasi(){
        $.ajax({
            type: "POST",
            url: '{{url("transfer_outlet/informasi")}}',
            async:true,
            data: {
                _token:"{{csrf_token()}}",
            },
            beforeSend: function(data){
              // on_load();
            $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-info");
            $("#modal-xl .modal-title").html("Informasi");
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

    function sign(id) {
        swal({
            title: "Tanda Tangan",
            text: "Pastikan inputan sudah sesuai, kemudian tuliskan nama pendek/panggilan yang menginputkan nota ini :",
            type: "input",
            showCancelButton: true,
            closeOnConfirm: false,
            animation: "slide-from-top",
            inputPlaceholder: "tulis disini..."
        },
        function(inputValue){
            if (inputValue === null) return false;
          
            if (inputValue === "") {
                swal.showInputError("tuliskan nama pendek/panggilan pada kolom input!");
                return false
            }
            $.ajax({
                type: "POST",
                url: '{{url("transfer_outlet/send_sign")}}',
                async:true,
                data: {
                    _token:token,
                    id:id,
                    sign_by:inputValue
                },
                beforeSend: function(data){
                    // replace dengan fungsi loading
                },
                success:  function(data){
                    if(data==1){
                        swal("Success!", "Data input nota transfer sudah di tanda tangani.", "success");
                    }else{
                        
                        swal("Failed!", "Gagal menyimpan data.", "error");
                    }
                },
                complete: function(data){
                    tb_data_transfer.draw(false);
                },
                error: function(data) {
                    swal("Error!", "Ajax occured.", "error");
                }
            });
        });
    }

    function batal_sign(id){
        swal({
            title: "Apakah anda yakin membatalkan sign nota ini?",
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
                url: '{{url("transfer_outlet/batal_sign")}}',
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
                        swal("Success!", "Data berhasil disimpan.", "success");
                    }else{
                        
                        swal("Failed!", "Gagal menyimpan data.", "error");
                    }
                },
                complete: function(data){
                    tb_data_transfer.draw(false);
                },
                error: function(data) {
                    swal("Error!", "Ajax occured.", "error");
                }
            });
        });
    }
</script>
@endsection