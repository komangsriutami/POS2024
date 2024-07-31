@extends('layout.app')

@section('title')
Rekap Omset
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Rekap</a></li>
    <li class="breadcrumb-item"><a href="#">Rekap Omset</a></li>
    <li class="breadcrumb-item active" aria-current="page">Index</li>
</ol>
@endsection

@section('content')
	<style type="text/css">
		.select2 {
		  width: 100%!important; /* overrides computed width, 100px in your demo */
		}

		.bg-closing {
			background-color: #cfd8dc;
		}
	</style>

	<div class="card card-info card-outline" id="main-box" style="">
  		<div class="card-header">
        	<h3 class="card-title">
          		<i class="fas fa-list"></i>
   				List Data Omset
        	</h3>
      	</div>
        <div class="card-body">
        	<form role="form" id="searching_form">
                <!-- text input -->
                <div class="row">
                    <div class="form-group  col-md-2">
                        <label>Dari Tanggal</label>
                        <input type="text" name="tgl_awal"  id="tgl_awal" class="datepicker form-control" value="{{ $first_day }}" autocomplete="off">
                    </div>
                    <div class="form-group  col-md-3">
                        <label>Sampai Tanggal</label>
                        <div class="input-group">
	                        <input type="text" name="tgl_akhir" id="tgl_akhir" class="datepicker form-control" value="{{ $date_now }}" autocomplete="off">
	                        <div class="input-group-append">
				                <button type="submit" class="btn btn-primary" id="datatable_filter"><i class="fa fa-search"></i> Cari</button>
	                        	<span class="btn bg-olive" onClick="export_data()"  data-toggle="modal" data-placement="top" title="Export Data Transfer"><i class="fa fa-file-excel" aria-hidden="true"></i> Export</span> 
				            </div>
				        </div>
                    </div>
                </div>
            </form>
			<hr>
		    <div class="card card-secondary">
		    	<br>
		        <h3 class="card-title text-center text-info"> - PENJUALAN NON KREDIT -</h3>
		        <div class="card-body">
		        	<div class="col-lg-12">
		        		<div class="row">
			                <div class="col-md-3 col-sm-6 col-12">
			                    <div class="info-box bg-secondary">
			                        <div class="info-box-content">
			                            <span class="info-box-text">Total Penjualan</span>
			                            <div class="progress">
			                                <div class="progress-bar" style="width: 100%"></div>
			                            </div>
			                            <span class="info-box-number" id="penjualan_non_kredit">Rp 0,-</span>
			                        </div>
			                        <!-- /.info-box-content -->
			                    </div>
			                    <!-- /.info-box -->
			                </div>
			                <!-- /.col -->
			                <div class="col-md-3 col-sm-6 col-12">
			                    <div class="info-box bg-secondary">
			                        <div class="info-box-content">
			                            <span class="info-box-text">Cash</span>
			                            <div class="progress">
			                                <div class="progress-bar" style="width: 100%"></div>
			                            </div>
			                            <span class="info-box-number" id="penjualan_non_kredit_cash">Rp 0,-</span>
			                        </div>
			                        <!-- /.info-box-content -->
			                    </div>
			                    <!-- /.info-box -->
			                </div>
			                <!-- /.col -->
			                <div class="col-md-3 col-sm-6 col-12">
			                    <div class="info-box bg-secondary">
			                        <div class="info-box-content">
			                            <span class="info-box-text">Non Cash</span>
			                            <div class="progress">
			                                <div class="progress-bar" style="width: 100%"></div>
			                            </div>
			                            <span class="info-box-number" id="penjualan_non_kredit_non_cash">Rp 0,-</span>
			                        </div>
			                        <!-- /.info-box-content -->
			                    </div>
			                    <!-- /.info-box -->
			                </div>
			                <!-- /.col -->
			                <div class="col-md-3 col-sm-6 col-12">
			                    <div class="info-box bg-secondary">
			                        <div class="info-box-content">
			                            <span class="info-box-text">TT</span>
			                            <div class="progress">
			                                <div class="progress-bar" style="width: 100%"></div>
			                            </div>
			                            <span class="info-box-number" id="penjualan_non_kredit_tt">Rp 0,-</span>
			                        </div>
			                        <!-- /.info-box-content -->
			                    </div>
			                    <!-- /.info-box -->
			                </div>
			                <!-- /.col -->
			            </div>
			    	</div>
		        </div>
		        <!-- /.card-body -->

		        <h3 class="card-title text-center text-warning"> - PENJUALAN KREDIT -</h3>
		        <div class="card-body">
		        	<div class="col-lg-12">
						<div class="row">
			                <div class="col-md-4 col-sm-6 col-12">
			                    <div class="info-box bg-secondary">
			                        <div class="info-box-content">
			                            <span class="info-box-text">Total Penjualan</span>
			                            <div class="progress">
			                                <div class="progress-bar" style="width: 100%"></div>
			                            </div>
			                            <span class="info-box-number" id="penjualan_kredit">Rp 0,-</span>
			                        </div>
			                        <!-- /.info-box-content -->
			                    </div>
			                    <!-- /.info-box -->
			                </div>
			                <!-- /.col -->
			                <div class="col-md-4 col-sm-6 col-12">
			                    <div class="info-box bg-secondary">
			                        <div class="info-box-content">
			                            <span class="info-box-text">Total Sudah Terbayar</span>
			                            <div class="progress">
			                                <div class="progress-bar" style="width: 100%"></div>
			                            </div>
			                            <span class="info-box-number" id="penjualan_kredit_sudah_terbayar">Rp 0,-</span>
			                        </div>
			                        <!-- /.info-box-content -->
			                    </div>
			                    <!-- /.info-box -->
			                </div>
			                <!-- /.col -->
			                <div class="col-md-4 col-sm-6 col-12">
			                    <div class="info-box bg-secondary">
			                        <div class="info-box-content">
			                            <span class="info-box-text">Total Belum Terbayar</span>
			                            <div class="progress">
			                                <div class="progress-bar" style="width: 100%"></div>
			                            </div>
			                            <span class="info-box-number" id="penjualan_kredit_belum_terbayar">Rp 0,-</span>
			                        </div>
			                        <!-- /.info-box-content -->
			                    </div>
			                    <!-- /.info-box -->
			                </div>
			                <!-- /.col -->
			            </div>
			    	</div>
		        </div>
		        <!-- /.card-body -->
		        <h3 class="card-title text-center text-purple"> - RINCIAN PENJUALAN NON ITEM -</h3>
		        <div class="card-body">
		        	<div class="col-lg-12">
						<div class="row">
			                <div class="col-md-4 col-sm-6 col-12">
			                    <div class="info-box bg-secondary">
			                        <div class="info-box-content">
			                            <span class="info-box-text">Dokter</span>
			                            <div class="progress">
			                                <div class="progress-bar" style="width: 100%"></div>
			                            </div>
			                            <span class="info-box-number" id="penjualan_dokter">Rp 0,-</span>
			                        </div>
			                        <!-- /.info-box-content -->
			                    </div>
			                    <!-- /.info-box -->
			                </div>
			                <!-- /.col -->
			                <div class="col-md-4 col-sm-6 col-12">
			                    <div class="info-box bg-secondary">
			                        <div class="info-box-content">
			                            <span class="info-box-text">Jasa Dokter</span>
			                            <div class="progress">
			                                <div class="progress-bar" style="width: 100%"></div>
			                            </div>
			                            <span class="info-box-number" id="penjualan_jasa_dokter">Rp 0,-</span>
			                        </div>
			                        <!-- /.info-box-content -->
			                    </div>
			                    <!-- /.info-box -->
			                </div>
			                <!-- /.col -->
			                <div class="col-md-4 col-sm-6 col-12">
			                    <div class="info-box bg-secondary">
			                        <div class="info-box-content">
			                            <span class="info-box-text">Paket WD</span>
			                            <div class="progress">
			                                <div class="progress-bar" style="width: 100%"></div>
			                            </div>
			                            <span class="info-box-number" id="penjualan_paket_wd">Rp 0,-</span>
			                        </div>
			                        <!-- /.info-box-content -->
			                    </div>
			                    <!-- /.info-box -->
			                </div>
			                <!-- /.col -->
			                <div class="col-md-4 col-sm-6 col-12">
			                    <div class="info-box bg-secondary">
			                        <div class="info-box-content">
			                            <span class="info-box-text">Paket LAB</span>
			                            <div class="progress">
			                                <div class="progress-bar" style="width: 100%"></div>
			                            </div>
			                            <span class="info-box-number" id="penjualan_lab">Rp 0,-</span>
			                        </div>
			                        <!-- /.info-box-content -->
			                    </div>
			                    <!-- /.info-box -->
			                </div>
			                <!-- /.col -->
			                <div class="col-md-4 col-sm-6 col-12">
			                    <div class="info-box bg-secondary">
			                        <div class="info-box-content">
			                            <span class="info-box-text">Paket APD</span>
			                            <div class="progress">
			                                <div class="progress-bar" style="width: 100%"></div>
			                            </div>
			                            <span class="info-box-number" id="penjualan_apd">Rp 0,-</span>
			                        </div>
			                        <!-- /.info-box-content -->
			                    </div>
			                    <!-- /.info-box -->
			                </div>
			                <!-- /.col -->
			                <div class="col-md-4 col-sm-6 col-12">
			                    <div class="info-box bg-secondary">
			                        <div class="info-box-content">
			                            <span class="info-box-text">Biaya Ongkir</span>
			                            <div class="progress">
			                                <div class="progress-bar" style="width: 100%"></div>
			                            </div>
			                            <span class="info-box-number" id="penjualan_ongkir">Rp 0,-</span>
			                        </div>
			                        <!-- /.info-box-content -->
			                    </div>
			                    <!-- /.info-box -->
			                </div>
			                <!-- /.col -->
			            </div>
			    	</div>
		        </div>
		        <!-- /.card-body -->
		    </div>
		    <!-- /.card -->
            <hr>
			<table  id="tb_data_closing" class="table table-bordered">
		    	<thead>
			        <tr>
			            <th width="3%" class="text-center">No.</th>
			            <th width="10%" class="text-center">Tanggal</th>
			            <th width="5%" class="text-center">Shift</th>
			            <th width="20%" class="text-center">Kasir</th>
			            <th width="15%" class="text-center">total penjualan</th>
			            <th width="10%" class="text-center">total penjualan kredit</th>
			            <th width="15%" class="text-center">hit penjualan</th>
			            <th width="10%" class="text-center">hiit penjualan K</th>
			            <th width="12%" class="text-center">Action</th>
			        </tr>
		        </thead>
		        <tbody>
		        </tbody>
		        <tfoot>
                    <tr>
                        <td colspan="4" style="text-align: left!important;">Total</td>
                        <td id="total_penjualan" class="text-right"></td>
                        <td id="total_penjualan_kredit" class="text-right"></td>
                        <td id="total_hit_penjualan" class="text-right"></td>
                        <td id="total_hit_penjualan_kredit" class="text-right"></td>
                        <td></td>
                    </tr>
                </tfoot>
			</table>
        </div>
  	</div>
@endsection

@section('script')
<script type="text/javascript">
	var token = '{{csrf_token()}}';
	var tb_data_closing = $('#tb_data_closing').dataTable( {
			processing: true,
	        serverSide: true,
	        stateSave: true,
	        ajax:{
			        url: '{{url("penjualan/list_rekap_omset")}}',
			        data:function(d){
			        	d.id         = $('#search_id').val();
	                    d.tgl_awal = $("#tgl_awal").val();
	                    d.tgl_akhir = $("#tgl_akhir").val();
				    }
			     },
	        columns: [
	            {data: 'DT_RowIndex', name: 'DT_RowIndex',width:"2%", class:'text-center'},
	            {data: 'created_at', name: 'created_at', class:'text-center'},
	            {data: 'shift', name: 'shift', class:'text-center', orderable: false, searchable: true},
	            {data: 'created_by', name: 'created_by'},
	            {data: 'total_penjualan', name: 'total_penjualan', class:'text-center'},
	            {data: 'total_penjualan_kredit', name: 'total_penjualan_kredit', class:'text-center'},
	            {data: 'hit_penjualan', name: 'hit_penjualan', class:'text-center'},
	            {data: 'hit_penjualan_kredit', name: 'hit_penjualan_kredit', class:'text-center'},
	            {data: 'action', name: 'action', orderable: false, searchable: true}
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
		    },
		    "footerCallback": function ( row, data, start, end, display ) {
	            var api = this.api();

	            // Remove the formatting to get integer data for summation
	            var intVal = function ( i ) {
	                return typeof i === 'string' ?
	                    i.replace(/[\$,]/g, '')*1 :
	                    typeof i === 'number' ?
	                        i : 0;
	            };

	            total_penjualan = api
	                .column(4)
	                .data()
	                .reduce( function (a, b) {
	                    return intVal(a) + intVal(b);
	                },0);
	            $(api.column(4).footer()).html(total_penjualan);

	            total_hit_penjualan = api
	                .column(5)
	                .data()
	                .reduce( function (a, b) {
	                    return intVal(a) + intVal(b);
	                },0);
	            $(api.column(5).footer()).html(total_hit_penjualan);

	            total_hit_penjualan = api
	                .column(6)
	                .data()
	                .reduce( function (a, b) {
	                    return intVal(a) + intVal(b);
	                },0);
	            $(api.column(6).footer()).html(total_hit_penjualan);

	            total_hit_penjualan = api
	                .column(7)
	                .data()
	                .reduce( function (a, b) {
	                    return intVal(a) + intVal(b);
	                },0);
	            $(api.column(7).footer()).html(total_hit_penjualan);


        	}
 		});

	$(document).ready(function(){
		$("#searching_form").submit(function(e){
			e.preventDefault();
			tb_data_closing.fnDraw(false);
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

	function cari_info() {
        $.ajax({
            type: "GET",
            url: '{{url("penjualan/q")}}',
            async:true,
            data: {
                _token:token,
                id_apotek : $('#id_apotek').val(),
                id_suplier : $('#id_suplier').val(),
                id_status_lunas : $('#id_status_lunas').val(),
                tgl_awal : $('#tgl_awal').val(),
                tgl_akhir : $('#tgl_akhir').val(),
            },
            beforeSend: function(data){
                // replace dengan fungsi loading
            },
            success:  function(data){
                $("#penjualan_kredit").html(data.penjualan_kredit);
                $("#penjualan_kredit_sudah_terbayar").html(data.penjualan_kredit_sudah_terbayar);
                $("#penjualan_kredit_belum_terbayar").html(data.penjualan_kredit_belum_terbayar);
                $("#penjualan_non_kredit").html(data.penjualan_non_kredit);
                $("#penjualan_non_kredit_cash").html(data.penjualan_non_kredit_cash);
                $("#penjualan_non_kredit_non_cash").html(data.penjualan_non_kredit_non_cash);
                $("#penjualan_non_kredit_tt").html(data.penjualan_non_kredit_tt);
                $("#penjualan_dokter").html(data.penjualan_dokter);
                $("#penjualan_jasa_dokter").html(data.penjualan_jasa_dokter);
                $("#penjualan_paket_wd").html(data.penjualan_paket_wd);
                $("#penjualan_lab").html(data.penjualan_lab);
                $("#penjualan_apd").html(data.penjualan_apd);
                $("#penjualan_ongkir").html(data.penjualan_ongkir);
            },
            complete: function(data){
                
            },
            error: function(data) {
                swal("Error!", "Ajax occured.", "error");
            }
        });
    }

	function export_data(){
        window.open("{{ url('penjualan/export_rekap_omset') }}"+ "?tgl_awal="+$('#tgl_awal').val()+"&tgl_akhir="+$('#tgl_akhir').val(),"_blank");
    }

    function cetak_report(tgl){
    	console.log(tgl);
        window.open("{{ url('penjualan/print_closing_kasir_pdf') }}"+ "?tanggal="+tgl,"_blank");
    }

    function detail_data(id){
      	$.ajax({
          	type: "GET",
	        url: '{{url("penjualan/detail_closing_kasir")}}/'+id,
	        async:true,
	        data: {
	            _token		: "{{csrf_token()}}",
	        },
	        beforeSend: function(data){
	          	// on_load();
		        $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
		        $("#modal-xl .modal-title").html("Detail Closing Kasir");
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

  	function hapus_closing(id){
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
				url: '{{url("penjualan/hapus_closing/")}}/'+id,
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
						swal("Deleted!", "Data closing berhasil dihapus.", "success");
					}else{
						
						swal("Failed!", "Gagal menghapus data closing.", "error");
					}
				},
				complete: function(data){
					tb_data_closing.fnDraw(false);
				},
				error: function(data) {
					swal("Error!", "Ajax occured.", "error");
				}
			});
		});
	}
</script>
</script>
@endsection