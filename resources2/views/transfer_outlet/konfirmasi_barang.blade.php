@extends('layout.app')

@section('title')
Konfirmasi Barang Datang
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Transaksi</a></li>
    <li class="breadcrumb-item"><a href="#">Data Transfer Outlet</a></li>
    <li class="breadcrumb-item active" aria-current="page">Konfirmasi Barang Datang</li>
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
	      	<p>Untuk pencarian, isikan kata yang ingin dicari pada kolom search, lalu tekan enter.</p>
	    </div>
	</div>

	<div class="card card-info card-outline" id="main-box" style="">
  		<div class="card-header">
        	<h3 class="card-title">
          		<i class="fas fa-list"></i>
   				List Data Transfer Outlet - Obat Masuk
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
                        <input type="text" name="tgl_awal"  id="tgl_awal" class="datepicker form-control" autocomplete="off">
                    </div>
                    <div class="form-group  col-md-2">
                        <label>Sampai Tanggal</label>
                        <input type="text" name="tgl_akhir" id="tgl_akhir" class="datepicker form-control" autocomplete="off">
                    </div>
                    <div class="col-lg-12" style="text-align: center;">
                        <button type="submit" class="btn btn-primary" id="datatable_filter"><i class="fa fa-search"></i> Cari</button>
                        <span class="btn bg-olive" onClick="export_data_transfer()"  data-toggle="modal" data-placement="top" title="Export Data Transfer"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export</span> 
                    </div>
                </div>
            </form>
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
                        <th width="10%" class="text-center">Lunas ?</th>
                        <th width="10%" class="text-center">Status</th>
                        <th width="12%" class="text-center">Action</th>
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
            url: '{{url("transfer_outlet/list_konfirmasi_barang")}}',
		        data:function(d){
		        	d.id         = $('#search_id').val();
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
            {data: 'is_status', name: 'is_tanda_terima', class:'text-center'},
            {data: 'action', name: 'id',orderable: true, searchable: true, class:'text-center'}
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
		});

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
</script>
@endsection