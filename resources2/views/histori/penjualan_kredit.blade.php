@extends('layout.app')

@section('title')
Histori Transaksi Penjualan
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Histori</a></li>
    <li class="breadcrumb-item"><a href="#">Transaksi Penjualan</a></li>
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
	    </div>
	</div>

	<div class="card card-info card-outline" id="main-box" style="">
  		<div class="card-header">
        	<h3 class="card-title">
          		<i class="fas fa-list"></i>
   				List Data Penjualan
        	</h3>
      	</div>
        <div class="card-body">
        	<form role="form" id="searching_form">
	        	<div class="row">
		        	<div class="form-group col-md-3">
		        		<?php 
		        			$date = date('d/m/Y').' - '.date('d/m/Y');
		        		?>
					    {!! Form::label('tgl_penjualan', 'Tanggal Penjualan (*)') !!}
					    {!! Form::text('tgl_penjualan', $date, array('id' => 'tgl_penjualan', 'class' => 'form-control', 'placeholder'=>'Masukan tanggal penjualan')) !!}
					</div>
					<div class="form-group col-md-2">
				        {!! Form::label('id_vendor', 'Penjualan Melalui') !!}
				        {!! Form::select('id_vendor', $vendor_kerjamas, '', ['class' => 'form-control input_select', 'autocomplete' => 'off']) !!}
				    </div>
				    <div class="col-lg-2 form-group">
                        <label>Status</label>
                        <select id="is_lunas_pembayaran_kredit" name="is_lunas_pembayaran_kredit" class="form-control input_select">
                            <option value="">------Pilih Status-----</option>
                            <option value="0">Belum Lunas</option>
                            <option value="1">Lunas</option>
                        </select>
                    </div>
				    <div class="col-lg-5 form-group">
	                    <label>Keterangan</label>
	                    <input type="text" id="keterangan" class="form-control" placeholder="Masukan Keterangan [Exp : nomor order]" autocomplete="off">
	                </div>
	                <div class="col-lg-12" style="text-align: center;">
	                    <button type="submit" class="btn btn-primary" id="datatable_filter"><i class="fa fa-search"></i> Cari</button> 
	                    <span class="btn bg-olive" onClick="export_data()"  data-toggle="modal" data-placement="top" title="Export Data"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export</span> 
	                </div>
				</div>
			</form>
			<table  id="tb_penjualan" class="table table-bordered table-striped table-hover">
		    	<thead>
			        <tr>
			            <th width="3%" class="text-center">No.</th>
			            <th width="5%" class="text-center">ID Nota</th>
			            <th width="12%" class="text-center">Tanggal</th>
			            <th width="10%" class="text-center">Penjualan Item</th>
			            <th width="13%" class="text-center">Resep/Dokter</th>
			            <th width="13%" class="text-center">Total Debet/Credit</th>
			            <th width="14%" class="text-center">Total</th>
			            <th width="12%" class="text-center">Lunas ?</th>
			            <th width="12%" class="text-center">Oleh</th>
			            <th width="10%" class="text-center">Action</th>
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
	var tb_penjualan = $('#tb_penjualan').dataTable( {
			processing: true,
	        serverSide: true,
	        stateSave: true,
	        ajax:{
			        url: '{{url("penjualan/list_kredit")}}',
			        data:function(d){
			        	d.tgl_penjualan = $("#tgl_penjualan").val();
			        	d.id_vendor = $("#id_vendor").val();
			        	d.keterangan = $("#keterangan").val();
			        	d.is_lunas_pembayaran_kredit = $("#is_lunas_pembayaran_kredit").val();
				    }
			     },
	        columns: [
	            {data: 'no', name: 'no',width:"2%", class:'text-center'},
	            {data: 'id', name: 'id', class:'text-center'},
	            {data: 'created_at', name: 'created_at', class:'text-center'},
	            {data: 'total', name: 'total', class:'text-right'},
	            {data: 'biaya_jasa_dokter', name: 'biaya_jasa_dokter', class:'text-right'},
	            {data: 'debet', name: 'debet', class:'text-right'},
	            {data: 'total_fix', name: 'total_fix', class:'text-right', orderable: false, searchable: false},
	            {data: 'is_lunas_pembayaran_kredit', name: 'is_lunas_pembayaran_kredit', class:'text-center'},
	            {data: 'created_by', name: 'created_by', class:'text-center'},
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
		$("#searching_form").submit(function(e){
			e.preventDefault();
			tb_penjualan.fnDraw(false);
		});

		$('#tgl_penjualan').daterangepicker({
        	timePicker: false,
        	timePickerIncrement: 30,
		    locale: {
		       	format: 'DD/MM/YYYY'
		    }
        });

        /*$("#tgl_penjualan, #id_vendor").change(function(e){
		    e.preventDefault();
			tb_penjualan.fnDraw();
	    });

	    $("#keterangan").keypress(function(event){
		    if (event.which == '10' || event.which == '13') {
		        event.preventDefault();
		        tb_penjualan.fnDraw();
		    }
		});*/

		$('.input_select').select2();
	})

	function pembayaran_kredit(id){
		$.ajax({
		    type: "POST",
		    url: '{{url("penjualan/pembayaran_kredit")}}/'+id,
		    async:true,
		    data: {
		    	_token:"{{csrf_token()}}"
		    },
		    beforeSend: function(data){
		      // on_load();
		    $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
		    $("#modal-xl .modal-title").html("Pembayaran Penjualan Kredit");
		    $('#modal-xl').modal("show");
		    $('#modal-xl').find('.modal-body-content').html('');
		    $("#modal-xl").find(".overlay").fadeIn("200");
		    },
		    success:  function(data){
		      $('#modal-xl').find('.modal-body-content').html(data);
		    },
		    complete: function(data){
		        $("#modal-xl").find(".overlay").fadeOut("200")
		    },
		      error: function(data) {
		        alert("error ajax occured!");
		      }
		});
	}

	function submit_valid(id){
		if($(".validated_form").valid()) {
			data = {};
			$("#form-pembayaran-kredit").find("input[name], select").each(function (index, node) {
		        data[node.name] = node.value;
    			
		    });

			$.ajax({
				type:"PUT",
				url : '{{url("penjualan/update_pembayaran_kredit/")}}/'+id,
				dataType : "json",
				data : data,
				beforeSend: function(data){
					// replace dengan fungsi loading
				},
				success:  function(data){
					if(data.status ==1){
						show_info("Data pembayaran penjualan kredit berhasil disimpan!");
						$('#modal-large').modal('toggle');
					} else if(data.status == 2){
						show_error("Jika menggunakan kartu debet/kredit, tidak diperkenankan ada kembalian, silakan cek kembali!");
						return false;
					} else if(data.status == 3){
						show_error("Uang yang dimasukan kurang!");
						return false;
					} else if(data.status == 4){
						show_error("Nomor kartu harus angka!");
						return false;
					} else if(data.status == 5){
						show_error("Belum ada item belanja!");
						return false;
					} else {
						show_error("Gagal menyimpan data ini!");
						return false;
					}
				},
				complete: function(data){
					// replace dengan fungsi mematikan loading
					tb_penjualan.fnDraw(false);
				},
				error: function(data) {
					show_error("error ajax occured!");
				}

			})
		} else {
			return false;
		}
	}

	function export_data(){
        window.open("{{ url('penjualan/export_penjualan_kredit') }}"+ "?tgl_penjualan="+$('#tgl_penjualan').val()+"&id_vendor="+$('#id_vendor').val()+"&keterangan="+$('#keterangan').val()+"&is_lunas_pembayaran_kredit="+$('#is_lunas_pembayaran_kredit').val(),"_blank");
    }

    function delete_penjualan(id){
        swal({
            title: "Apakah anda yakin menghapus data penjualan ini?",
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
                url: '{{url("penjualan")}}/'+id,
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
                        swal("Deleted!", "Data penjualan berhasil dihapus.", "success");
                    }else{
                        
                        swal("Failed!", "Gagal menghapus data penjualan.", "error");
                    }
                },
                complete: function(data){
                    tb_penjualan.fnDraw(false);
                },
                error: function(data) {
                    swal("Error!", "Ajax occured.", "error");
                }
            });
        });
    }
</script>
@endsection