@extends('layout.app')

@section('title')
Data Pembelian
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Transaksi</a></li>
    <li class="breadcrumb-item"><a href="#">Data Pembelian</a></li>
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
    <div class="row" id="divfix">
        <div class="col-sm-12">
            <div class="callout callout-success">
                <div id="btn_set" style="display: inline-block;"></div>
            </div>
        </div>
    </div>

	<!-- <div class="card card-info card-outline mb-12 border-left-primary">
	    <div class="card-body">
	      	<h4><i class="fa fa-info"></i> Informasi</h4>
	      	<p>Untuk pencarian, isikan kata yang ingin dicari pada kolom search, lalu tekan enter.</p>
	    </div>
	</div> -->

	<div class="card card-info card-outline" id="main-box" style="">
  		<div class="card-header">
        	<h3 class="card-title">
          		<i class="fas fa-list"></i>
   				List Data Pembelian
        	</h3>
            <!-- <a class="btn btn-warning w-md m-b-5 float-right" href="#" onclick="informasi()"><i class="fa fa-exclamation-triangle"></i> Informasi</a> -->
      	</div>
        <div class="card-body">
            <form role="form" id="searching_form">
            	<div class="row">
                    <div class="col-lg-3 form-group">
                        <label>No Faktur</label>
                        <input type="text" id="no_faktur" class="form-control" placeholder="Masukan Nomer Faktur" autocomplete="off">
                    </div>
                    <div class="col-lg-2 form-group">
                        <label>Jenis Pembelian</label>
                        <select id="id_jenis_pembelian" name="id_jenis_pembelian" class="form-control input_select" autocomplete="off">
                            <option value="">------Pilih-----</option>
                            <?php $no = 0; ?>
                            @foreach( $jenis_pembelians as $jenis_pembelian )
                                <?php $no = $no+1; ?>
                                <option value="{{ $jenis_pembelian->id }}">{{ $jenis_pembelian->jenis_pembelian }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-5 form-group">
                        <label>Suplier</label>
                        <select id="id_suplier" name="id_suplier" class="form-control input_select" autocomplete="off">
                            <option value="">------Pilih-----</option>
                            <?php $no = 0; ?>
                            @foreach( $supliers as $suplier )
                                <?php $no = $no+1; ?>
                                <option value="{{ $suplier->id }}">{{ $suplier->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 form-group">
                        <label>Status</label>
                        <select id="id_status_lunas" name="id_status_lunas" class="form-control" autocomplete="off">
                            <option value="">------Pilih-----</option>
                            <option value="0">Belum Lunas</option>
                            <option value="1">Lunas</option>
                        </select>
                    </div>
                    <div class="form-group  col-md-3">
                        <label>Dari Tanggal Faktur</label>
                        <input type="text" name="tgl_awal_faktur"  id="tgl_awal_faktur"  value="{{ $first }}" class="datepicker form-control" autocomplete="off">
                    </div>
                    <div class="form-group  col-md-3">
                        <label>Sampai Tanggal Faktur</label>
                        <input type="text" name="tgl_akhir_faktur" id="tgl_akhir_faktur" value="{{ $last }}" class="datepicker form-control" autocomplete="off">
                    </div>
                    <div class="form-group  col-md-3">
                        <label>Dari Tanggal JT</label>
                        <input type="text" name="tgl_awal"  id="tgl_awal" class="datepicker form-control" autocomplete="off">
                    </div>
                    <div class="form-group  col-md-3">
                        <label>Sampai Tanggal JT</label>
                        <input type="text" name="tgl_akhir" id="tgl_akhir" class="datepicker form-control" autocomplete="off">
                    </div>
                    <div class="col-lg-12" style="text-align: center;">
                        <button type="submit" class="btn btn-primary" id="datatable_filter"><i class="fa fa-search"></i> Cari</button> 
                        <span class="btn bg-olive" onClick="export_data()"  data-toggle="modal" data-placement="top" title="Export Data Transfer"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export</span> 
                    </div>
            	</div>
            </form>
			<hr>
            <div class="row">
                <div class="col-md-4">
                    <div class="info-box bg-secondary">
                        <span class="info-box-icon"><i class="far fa-calendar-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Faktur Jatuh Tempo</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                            <span class="info-box-number" id="total_faktur_jatuh_tempo">Rp 0,-</span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>
                <!-- /.col -->
                <div class="col-md-4">
                    <div class="info-box bg-info">
                        <span class="info-box-icon"><i class="far fa-check-square"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Faktur Lunas</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                            <span class="info-box-number" id="total_faktur_jatuh_tempo_lunas">Rp 0,-</span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>
                <!-- /.col -->
                <div class="col-md-4">
                    <div class="info-box bg-warning">
                        <span class="info-box-icon"><i class="far fa-bookmark"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Faktur Belum Lunas</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                            <span class="info-box-number" id="total_faktur_jatuh_tempo_belum_lunas">Rp 0,-</span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>
                <!-- /.col -->
                <span style="font-size: 10pt;" class="text-red">*) Note : jika tanggal jatuh tempo tidak diisikan, maka data yang tampil adalah pembelian yang jatuh tempo hari ini.</span>
            </div>
            <hr>
			<table  id="tb_data_pembelian" class="table table-bordered table-striped table-hover">
		    	<thead>
			        <tr>
                        <th width="3%" class="text-center">No.</th>
                        <th width="5%" class="text-center">ID Nota</th>
                        <th width="7%" class="text-center">Tanggal</th>
                        <th width="7%" class="text-center">JT</th>
                        <th width="20%" class="text-center">Suplier</th>
                        <th width="10%" class="text-center">No Faktur</th>
                        <th width="10%" class="text-center">Total</th>
                        <th width="6%" class="text-center">Jenis Pembelian</th>
                        <th width="7%" class="text-center">Lunas ?</th>
                        <th width="5%" class="text-center">Tanda Terima</th>
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
<script type="text/javascript">
	var token = '{{csrf_token()}}';
 	

 	var tb_data_pembelian = $('#tb_data_pembelian').DataTable( {
		paging:true,
        processing: true,
        serverSide: true,
        stateSave: true,
        scrollX: true,
        deferRender: true,
        ajax: {
            url: '{{url("pembelian/list_pembelian")}}',
		        data:function(d){
		        	d.id_apotek = $('#id_apotek').val();
                    d.no_faktur = $('#no_faktur').val();
                    d.id_jenis_pembelian = $("#id_jenis_pembelian").val();
                    d.id_suplier = $("#id_suplier").val();
                    d.id_status_lunas = $("#id_status_lunas").val();
                    d.tgl_awal = $("#tgl_awal").val();
                    d.tgl_akhir = $("#tgl_akhir").val();
                    d.tgl_awal_faktur = $("#tgl_awal_faktur").val();
                    d.tgl_akhir_faktur = $("#tgl_akhir_faktur").val();
		         }
        },
        order: [],
        columns: [
        	{data: 'no', name: 'no',width:"2%", class:'text-center'},
            {data: 'id', name: 'id', class:'text-center'},
            {data: 'tgl_nota', name: 'tgl_nota', class:'text-center'},
            {data: 'tgl_jatuh_tempo', name: 'tgl_jatuh_tempo', class:'text-center'},
            {data: 'suplier', name: 'Suplier'},
            {data: 'no_faktur', name: 'no_faktur', class:'text-center'},
            {data: 'jumlah', name: 'jumlah', class:'text-right'},
            {data: 'id_jenis_pembelian', name: 'id_jenis_pembelian', class:'text-center'},
            {data: 'is_lunas', name: 'is_lunas', class:'text-center'},
            {data: 'is_tanda_terima', name: 'is_tanda_terima', class:'text-center'},
            {data: 'is_sign', name: 'is_tanda_terima', class:'text-center'},
            {data: 'action', name: 'id',orderable: true, searchable: true}
        ],
        drawCallback: function(callback) {
            $("#btn_set").html(callback['jqXHR']['responseJSON']['btn_set']);
            //console.log(callback['jqXHR']['responseJSON']['btn_set'])
        }
	});


 	setTimeout(function(){
        $('#tb_data_pembelian .checkAlltogle').prop('checked', false);
    }, 1);

	$(document).ready(function(){
		$("#searching_form").submit(function(e){
			e.preventDefault();
			tb_data_pembelian.draw(false);
		    cari_info();
        });

        cari_info();

        $('.input_select').select2({});

		$('#tgl_awal, #tgl_akhir, #tgl_awal_faktur, #tgl_akhir_faktur').datepicker({
            autoclose:true,
            format:"yyyy-mm-dd",
            forceParse: false
        });
	})

    function delete_pembelian(id){
        swal({
            title: "Apakah anda yakin menghapus data pembelian ini?",
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
                url: '{{url("pembelian")}}/'+id,
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
                        swal("Deleted!", "Data pembelian berhasil dihapus.", "success");
                    }else{
                        
                        swal("Failed!", "Gagal menghapus data pembelian.", "error");
                    }
                },
                complete: function(data){
                    tb_data_pembelian.draw(false);
                },
                error: function(data) {
                    swal("Error!", "Ajax occured.", "error");
                }
            });
        });
    }

    function cek_tanda_terima_faktur(id){
        swal({
            title: "Apakah anda yakin sudah menerima faktur ini?",
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
                url: '{{url("pembelian/cek_tanda_terima_faktur")}}',
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
                    tb_data_pembelian.draw(false);
                },
                error: function(data) {
                    swal("Error!", "Ajax occured.", "error");
                }
            });
        });
    }
/*
    function export_data(){
        window.open("{{ url('pembelian/export') }}"+ "?id_apotek="+$('#id_apotek').val()+"&no_faktur="+$('#no_faktur').val()+"&id_jenis_pembelian="+$('#id_jenis_pembelian').val()+"&id_suplier="+$('#id_suplier').val()+"&id_status_lunas="$("#id_status_lunas").val()+"&tgl_awal="+$('#tgl_awal').val()+"&tgl_akhir="+$('#tgl_akhir').val(),"_blank");
    }
*/
    function export_data(){
        window.open("{{ url('pembelian/export_all') }}"+ "?id_apotek="+"&no_faktur="+$('#no_faktur').val()+"&id_jenis_pembelian="+$('#id_jenis_pembelian').val()+"&id_suplier="+$('#id_suplier').val()+"&id_status_lunas=1"+"&tgl_awal="+$('#tgl_awal').val()+"&tgl_akhir="+$('#tgl_akhir').val()+"&tgl_awal_faktur="+$('#tgl_awal_faktur').val()+"&tgl_akhir_faktur="+$('#tgl_akhir_faktur').val(),"_blank");
    }

    function cari_info() {
        $.ajax({
            type: "GET",
            url: '{{url("pembelian/cari_info2")}}',
            async:true,
            data: {
                _token:token,
                id_apotek : '',
                no_faktur : $('#no_faktur').val(),
                id_jenis_pembelian : $("#id_jenis_pembelian").val(),
                id_suplier : $("#id_suplier").val(),
                id_status_lunas : $("#id_status_lunas").val(),
                tgl_awal : $("#tgl_awal").val(),
                tgl_akhir : $("#tgl_akhir").val(),
            },
            beforeSend: function(data){
                // replace dengan fungsi loading
            },
            success:  function(data){
                $("#total_faktur_jatuh_tempo").html(data.total_all);
                $("#total_faktur_jatuh_tempo_lunas").html(data.total_lunas);
                $("#total_faktur_jatuh_tempo_belum_lunas").html(data.total_belum_lunas);
            },
            complete: function(data){
                
            },
            error: function(data) {
                swal("Error!", "Ajax occured.", "error");
            }
        });
    }

    function sign(id) {
        swal({
            title: "Tanda Tangan",
            text: "Pastikan inputan sudah sesuai, kemudian tuliskan nama pendek/panggilan yang menginputkan faktur ini :",
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
                url: '{{url("pembelian/send_sign")}}',
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
                        swal("Success!", "Data input faktur sudah di tanda tangani.", "success");
                    }else{
                        
                        swal("Failed!", "Gagal menyimpan data.", "error");
                    }
                },
                complete: function(data){
                    tb_data_pembelian.draw(false);
                },
                error: function(data) {
                    swal("Error!", "Ajax occured.", "error");
                }
            });
            //swal("Nice!", "You wrote: " + inputValue, "success");
        });
    }

    function informasi(){
        $.ajax({
            type: "POST",
            url: '{{url("pembelian/informasi")}}',
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

    function batal_sign(id){
        swal({
            title: "Apakah anda yakin membatalkan sign faktur ini?",
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
                url: '{{url("pembelian/batal_sign")}}',
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
                    tb_data_pembelian.draw(false);
                },
                error: function(data) {
                    swal("Error!", "Ajax occured.", "error");
                }
            });
        });
    }
</script>
@endsection