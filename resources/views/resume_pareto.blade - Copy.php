@extends('layout.app')



@section('title')

Resume Pareto

@endsection



@section('breadcrumb')

<ol class="breadcrumb float-sm-right">

    <li class="breadcrumb-item"><a href="#">Resume Pareto</a></li>

    <li class="breadcrumb-item active" aria-current="page">Index</li>

</ol>

@endsection



@section('content')

	<style type="text/css">

        /*custom style, untuk hide datatable length dan search value*/

        .dataTables_filter{

            display: none;

        }

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

   				Detail Resume Pareto

        	</h3>

      	</div>

        <div class="card-body">

        	<form role="form" id="searching_form">

                <!-- text input -->

                <div class="row">

                	<div class="col-lg-1 form-group">

						<label>Limit Data</label>

						<input type="text" id="limit" class="form-control" placeholder="Limit" value="100">

			    	</div>

			    	<div class="form-group col-md-2">

                		<div class="row">

							<div class="form-group col-lg-12">

								{!! Form::label('id_pencarian', 'Pilih Tipe Pencarian') !!}

								{!! Form::select('id_pencarian', ['1' => 'Hari Ini', '2' => 'Kemarin', '3' => 'Pekan Ini', '4' => 'Pekan Lalu', '5' => 'Bulan Ini', '6' => 'Bulan Lalu', '7' => '3 Bulan Terakhir', '8' => '6 Bulan Terakhir', '9' => 'Kalender'], 1, ['placeholder' => '-- tipe --', 'class' => 'form-control input_select required']) !!}

							</div>

							<div class="col-lg-12 form-group" hidden>

								<label>Tanggal</label>

								<input type="text" id="search_tanggal" class="form-control" placeholder="Tanggal Penjualan">

							</div>

						</div>

				    </div>

                	<div class="col-lg-2 form-group">

						<label>Nama Obat</label>

						<input type="text" id="nama" class="form-control" placeholder="Nama">

			    	</div>

					<div class="form-group col-lg-1">

						{!! Form::label('nilai_pareto', 'Nilai Pareto') !!}

						{!! Form::select('nilai_pareto', ['1' => 'A', '2' => 'B', '3' => 'C'], null, ['placeholder' => '-- nilai --', 'class' => 'form-control input_select']) !!}

					</div>

                    <div class="col-lg-12" style="text-align: center;">

                        <button type="submit" class="btn btn-primary" id="datatable_filter"><i class="fa fa-search"></i> Cari</button>

                        <span class="btn bg-olive" onClick="export_pareto()"  data-toggle="modal" data-placement="top" title="Export Pareto"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export Pareto</span>

                        <span class="btn bg-olive" onClick="export_pembelian()"  data-toggle="modal" data-placement="top" title="Export Pembelian"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export Pembelian</span>

						<?php

						if (in_array(session('id_role_active'), [1, 6])) {

						?>

							<span class="btn bg-olive" onClick="export_pareto_all()" data-toggle="modal" data-placement="top" title="Export Pareto All"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export Pareto All</span>

						<?php

						}

						?>

                    </div>

                </div>

            </form>

			<hr>

			<div id="view_here">

				<div class="card p-4" id="akumulasi_produk">

					<div class="row">

						<div class="col-md-12">

							<h4 class="akumulasi_produk_date">Akumulasi Hasil Analisis dari _ s.d. _</h4>

                		</div>

						<div class="col-md-6">

							<div class="row">

								<div class="col-md-6">Pareto A</div>

								<div class="col-md-6 pareto-a">: 0 produk</div>

							</div>

							<div class="row">

								<div class="col-md-6">Pareto B</div>

								<div class="col-md-6 pareto-b">: 0 produk</div>

							</div>

						</div>

						<div class="col-md-6">

							<div class="row">

								<div class="col-md-6">Pareto C</div>

								<div class="col-md-6 pareto-c">: 0 produk</div>

							</div>

							<div class="row">

								<div class="col-md-6">Total Produk</div>

								<div class="col-md-6 pareto-total">: 0 produk</div>

							</div>

						</div>

					</div>

				</div>

				<div class="row">

					<div class="col-md-12">

						<table class="table table-bordered table-striped table-hover" id="tb_penjualan" width="100%">

		                    <thead>

		                        <tr>

		                            <th width="100%" colspan="16" class="text-center text-white" style="background-color:#455a64;">PENJUALAN</th>

		                        </tr>

		                        <tr>

		                            <th width="5%" class="text-center text-white" style="background-color:#00bcd4;">No</th>

		                            <th width="15%" class="text-center text-white" style="background-color:#00bcd4;">Nama Obat</th>

		                            <th width="15%" class="text-center text-white" style="background-color:#00bcd4;">Penandaan Obat</th>

		                            <th width="5%" class="text-center text-white" style="background-color:#00bcd4;">Jenis</th>

		                            <th width="15%" class="text-center text-white" style="background-color:#00bcd4;">Produsen</th>

		                            <th width="5%" class="text-center text-white" style="background-color:#00bcd4;">Jumlah</th>

		                            <th width="10%" class="text-center text-white" style="background-color:#00acc1;">Penjualan</th>

		                            <th width="10%" class="text-center text-white" style="background-color:#00acc1;">Persentase Penjualan</th>

		                            <th width="5%" class="text-center text-white" style="background-color:#00acc1;">Klasifikasi Penjualan</th>

		                            <th width="10%" class="text-center text-white" style="background-color:#00acc1;">Keuntungan</th>

		                            <th width="10%" class="text-center text-white" style="background-color:#00acc1;">Persentase Keuntungan</th>

		                            <th width="5%" class="text-center text-white" style="background-color:#00acc1;">Klasifikasi Keuntungan</th>

		                            <th width="5%" class="text-center text-white" style="background-color:#00acc1;">Klasifikasi Pareto</th>

									<th width="5%" class="text-center text-white" style="background-color:#00acc1;">Stok Akhir</th>

									<th width="5%" class="text-center text-white" style="background-color:#00acc1;">Sedang Dipesan</th>

									<th width="5%" class="text-center text-white" style="background-color:#00acc1;">Add to Defecta</th>

		                        </tr>

		                    </thead>

		                    <tbody>

		                	</tbody>

		                	<tfoot>

					            <tr>

					                <th colspan="5" style="text-align:right" width="80%">Total / Rata-rata:</th>

					                <th></th>

					                <th></th>

					            </tr>

					        </tfoot>

		                </table>

					</div>

				</div>

			</div>

        </div>

  	</div>

@endsection



@section('script')

<script type="text/javascript">

	spinner.show();

	var token = '{{csrf_token()}}';

	var tb_penjualan = $('#tb_penjualan').dataTable( {

			paging : false,

			processing: true,

	        serverSide: true,

	        stateSave: true,

	        ajax:{

			        url: '{{url("home/list_pareto_penjualan")}}',

			        data:function(d){

			        	d.tanggal = $('#search_tanggal').val();

			        	d.limit = $('#limit').val();

			        	d.nama = $('#nama').val();

			        	d.id_satuan = $("#id_satuan").val();

						d.persentase = $("#persentase").val();

						d.id_pencarian = $("#id_pencarian").val();

						d.id_produsen = $("#id_produsen").val();

						d.nilai_penjualan = $("#nilai_penjualan").val();

						d.nilai_keuntungan = $("#nilai_keuntungan").val();

						d.nilai_pareto = $("#nilai_pareto").val();

				    }

			     },

	        columns: [

	            {data: 'no', name: 'no',width:"2%"},

	            {data: 'nama', name: 'nama'},

	            {data: 'id_penandaan_obat', name: 'id_penandaan_obat'},

	            {data: 'id_satuan', name: 'id_satuan'},

	            {data: 'id_produsen', name: 'id_produsen'},

	            {data: 'jumlah_penjualan', name: 'jumlah_penjualan', class:'text-center'},

	            {data: 'penjualan', name: 'penjualan', class:'text-right'},

	            {data: 'persentase_penjualan', name: 'persentase_penjualan', class:'text-center'},

				{ 
					data: 'klasifikasi_penjualan', 
					name: 'klasifikasi_penjualan', 
					class: 'text-center',
					render: function (data, type, row) {
						if (data === '1') {
							return 'A';
						} else if (data === '2') {
							return 'B';
						} else if (data === '3') {
							return 'C';
						} else {
							return '';
						}
					}
				},

	            {data: 'keuntungan', name: 'keuntungan', class:'text-right'},

	            {data: 'persentase_keuntungan', name: 'persentase_keuntungan', class:'text-center'},

	            { 
					data: 'klasifikasi_keuntungan', 
					name: 'klasifikasi_keuntungan', 
					class: 'text-center',
					render: function (data, type, row) {
						if (data === '1') {
							return 'A';
						} else if (data === '2') {
							return 'B';
						} else if (data === '3') {
							return 'C';
						} else {
							return '';
						}
					}
				},

	            { 
					data: 'klasifikasi_pareto', 
					name: 'klasifikasi_pareto', 
					class: 'text-center',
					render: function (data, type, row) {
						if (data === '1') {
							return 'A';
						} else if (data === '2') {
							return 'B';
						} else if (data === '3') {
							return 'C';
						} else {
							return '';
						}
					}
				},

	            {data: 'stok_akhir', name: 'stok_akhir', class:'text-right'},

	            {data: 'sedang_dipesan', name: 'sedang_dipesan', orderable: false, class:'text-right'},

				{data: 'action', name: 'id', orderable: false,}

	        ],

			columnDefs: [
				{
					targets: [0],
					orderData: [12, 8, 11, 13],
				},
				{
					targets: [2],
					orderData: [2, 12, 8, 11, 13]
				},
				{
					targets: [3],
					orderData: [3, 12, 8, 11, 13]
				},
				{
					targets: [4],
					orderData: [4, 12, 8, 11, 13]
				},
				{
					targets: [5],
					orderData: [5, 12, 8, 11, 13]
				},
				{
					targets: [8],
					orderData: [8, 11, 12, 13]
				},
				{
					targets: [11],
					orderData: [11, 8, 12, 13]
				},
				{
					targets: [12],
					orderData: [12, 8, 11, 13]
				},
				{
					targets: [13],
					orderData: [13, 12, 8, 11, 13]
				}
			],

			order: [[0, 'asc']],

	        footerCallback: function ( row, data, start, end, display ) {

	            var api = this.api(), data;

	 

	            // Remove the formatting to get integer data for summation

	            var intVal = function ( i ) {

	                return typeof i === 'string' ?

	                    i.replace(/[\$,]/g, '')*1 :

	                    typeof i === 'number' ?

	                        i : 0;

	            };

	 

	            // Total over all pages

	            total = api

	                .column( 5 )

	                .data()

	                .reduce( function (a, b) {

	                    return intVal(a) + intVal(b);

	                }, 0 );

	 

	            // Total over this page

	            pageTotal3 = api

	                .column( 5, { page: 'current'} )

	                .data()

	                .reduce( function (a, b) {

	                    return intVal(a) + intVal(b);

	                }, 0 );

	            pageTotal3 = hitung_rp(pageTotal3)



				// Total over this page

	            pageTotal4 = api

	                .column( 6, { page: 'current'} )

	                .data()

	                .reduce( function (a, b) {

	                    return intVal(a) + intVal(b);

	                }, 0 );



	            // Update footer

	            $( api.column( 5 ).footer() ).html(

	                pageTotal3 

	            );



	            var rata2 = pageTotal4/$('#limit').val();

	            rata2 = hitung_rp(rata2)

	            pageTotal4 = hitung_rp(rata2)



	            $( api.column( 6 ).footer() ).html(

	                'Rp '+rata2 

	            );

	            //+' ( $'+ total +' total)'

	        },

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
				spinner.hide();
				getAmountProduct();

		    }

 		});

	$(window).on('beforeunload', function() {
		$.ajax({
			type: 'GET',
			url: '{{url("resume_pareto/clear_cache")}}',
			error: function(xhr, status, error) {
				alert(error);
			}
		});
	});

	$(document).ready(function(){

        $("#searching_form").submit(function(e){

			spinner.show();

            e.preventDefault();

            tb_penjualan.fnDraw(false);

        });



        $('#search_tanggal').daterangepicker({

		    autoclose:true,

		    forceParse: false

		});



		$('#id_pencarian').on('change', function() {

			if (this.value == '9') {

			$('#search_tanggal').parent().removeAttr('hidden');

			} else {

			$('#search_tanggal').parent().attr('hidden', true);

			}

		});



        $('.input_select').select2({});

		



        $('body').addClass('sidebar-collapse');

	})



	/*

        =======================================================================================

        For     : Untuk membuka form add_defecta

        Author  : Anang B.P.

        Date    : 05/04/2023

        =======================================================================================

    */



	function add_keranjang(id_obat, id_defecta, jumlah, margin){

		//alert(jumlah);

	    $.ajax({

	        type: "POST",

	        url: '{{url("defecta/add_keranjang")}}',

	        async:true,

	        data: {

	        	_token: "{{csrf_token()}}",

	        	id_obat: id_obat,

	        },

	        beforeSend: function(data){

		        $('#modal-md').find('.modal-md').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");

		        $("#modal-md .modal-title").html("Masukkan ke Keranjang");

		        $('#modal-md').modal("show");

		        $('#modal-md').find('.modal-body-content').html('');

		        $("#modal-md").find(".overlay").fadeIn("200");

	        },

	        success:  function(data){

	          	$('#modal-md').find('.modal-body-content').html(data);

	        },

	        complete: function(data){

	            $("#modal-md").find(".overlay").fadeOut("200");

	        },

	          error: function(data) {

	            alert("error ajax occured!");

	          }

	    });

	}



	function add_keranjang_transfer(id_obat){

        //alert(jumlah);

        $.ajax({

            type: "POST",

            url: '{{url("defecta/add_keranjang_transfer")}}',

            async:true,

            data: {

                _token: "{{csrf_token()}}",

                id_obat: id_obat

            },

            beforeSend: function(data){

                // on_load();

                $('#modal-md').find('.modal-md').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");

                $("#modal-md .modal-title").html("Masukkan ke Keranjang");

                $('#modal-md').modal("show");

                $('#modal-md').find('.modal-body-content').html('');

                $("#modal-md").find(".overlay").fadeIn("200");

            },

            success:  function(data){

                $('#modal-md').find('.modal-body-content').html(data);

            },

            complete: function(data){

                $("#modal-md").find(".overlay").fadeOut("200");

            },

              error: function(data) {

                alert("error ajax occured!");

              }

        });

    }



	/*

        =======================================================================================

        For     : Untuk mengirimkan form defecta

        Author  : Anang B.P.

        Date    : 05/04/2023

        =======================================================================================

    */



	function submit_valid(id){

		$(".validated_form").validate({

			rules: {

				kuantitas: {

					number: true

				},

				harga_beli: {

					number: true

				}

			}

		});



		if($(".validated_form").valid()) {

			data = {};

			$("#form-edit").find("input[name], select").each(function (index, node) {

				data[node.name] = node.value;

		    });



			$.ajax({

				type:"PUT",

				url : '{{url("defecta/")}}/'+id,

				dataType : "json",

				data : data,

				beforeSend: function(data){

					// replace dengan fungsi loading

				},

				success:  function(data){

					if(data.status == 1){

						show_info("Data defecta berhasil disimpan!");

						$('#modal-md').modal('toggle');

					}else{

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



	/*

        =======================================================================================

        For     : Untuk menghitung akumulasi produk tiap pareto

        Author  : Anang B.P.

        Date    : 02/06/2023

        =======================================================================================

    */



	function getAmountProduct(){

	    month = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

		var range = [];

		var date = [];

		var tanggal = $('#search_tanggal').val();

		var limit = $('#limit').val();

		var nama = $('#nama').val();

		var id_satuan = $("#id_satuan").val();

		var persentase = $("#persentase").val();

		var id_pencarian = $("#id_pencarian").val();

		var id_produsen = $("#id_produsen").val();

		var nilai_penjualan = $("#nilai_penjualan").val();

		var nilai_keuntungan = $("#nilai_keuntungan").val();

		var nilai_pareto = $("#nilai_pareto").val();



		$.ajax({

			type:"GET",

			url : '{{url("resume_pareto/akumulasi_produk")}}',

			data: {

				tanggal: tanggal,

				limit: limit,

				nama: nama,

				id_satuan: id_satuan,

				persentase: persentase,

				id_pencarian: id_pencarian,

				id_produsen: id_produsen,

				nilai_penjualan: nilai_penjualan,

				nilai_keuntungan: nilai_keuntungan,

				nilai_pareto: nilai_pareto,

			},

			beforeSend: function(data){

				// replace dengan fungsi loading

			},

			success:  function(data){

				date[0] = new Date(data.tgl_awal);

				date[1] = new Date(data.tgl_akhir);

				range[0] = date[0].getDate()+' '+month[date[0].getMonth()]+' '+date[0].getFullYear();

				range[1] = date[1].getDate()+' '+month[date[1].getMonth()]+' '+date[1].getFullYear();

				$('#akumulasi_produk').find('.akumulasi_produk_date').html('Akumulasi Hasil Analisis dari '+range[0]+' s.d. '+range[1]);

				$('#akumulasi_produk').find('.pareto-a').html(': '+data.pareto_a+' produk');

				$('#akumulasi_produk').find('.pareto-b').html(': '+data.pareto_b+' produk');

				$('#akumulasi_produk').find('.pareto-c').html(': '+data.pareto_c+' produk');

				$('#akumulasi_produk').find('.pareto-total').html(': '+data.total+' produk');

			},

			complete: function(data){

				// replace dengan fungsi mematikan loading

			},

			error: function(data) {

				show_error("error ajax occured!");

			}



		})

	}



	function hitung_rp(nilai) {

		var nilai_str = nilai.toString();

        var res = nilai_str.split(".");

        var number_string = res[0],

            sisa    = number_string.length % 3,

            rupiah  = number_string.substr(0, sisa),

            ribuan  = number_string.substr(sisa).match(/\d{3}/g);

                

        if (ribuan) {

            separator = sisa ? '.' : '';

            rupiah += separator + ribuan.join('.');

        }

        return rupiah;

	}



	function export_pareto(){
        window.open("{{ url('home/export_pareto') }}"+ "?id_pencarian="+$('#id_pencarian').val()+"&tanggal="+$('#search_tanggal').val(),"_blank");
    }

	function export_pembelian(){
        window.open("{{ url('home/export_pembelian') }}"+ "?id_pencarian="+$('#id_pencarian').val()+"&tanggal="+$('#search_tanggal').val()+"&limit="+$('#limit').val(),"_blank");
    }

	function export_pareto_all(){
        window.open("{{ url('home/export') }}"+ "?id_pencarian="+$('#id_pencarian').val()+"&tanggal="+$('#search_tanggal').val(),"_blank");
    }

</script>

@endsection