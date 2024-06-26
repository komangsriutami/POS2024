<script type="text/javascript">
	var token = "";


	var tb_nota_penjualan = $('#tb_nota_penjualan').dataTable( {
            processing: true,
            serverSide: true,
            stateSave: true,
            paging: false,
            ajax:{
                    url: '{{url("penjualan/list_detail_penjualan")}}',
                    data:function(d){
                        d.id = $("#id").val();
                }
            },
            columns: [
               {data: 'no', name: 'no', orderable: false, searchable: true, class:'text-center'},
                {data: 'action', name: 'action', orderable: false, searchable: true, class:'text-center'},
                {data: 'nama_barang', name: 'nama_barang', orderable: false, searchable: true, class:'text-left'},
                {data: 'harga_jual', name: 'harga_jual', orderable: false, searchable: true, class:'text-right'},
                {data: 'diskon', name: 'diskon', orderable: false, searchable: true, class:'text-right'},
                {data: 'jumlah', name: 'jumlah', orderable: false, searchable: true, class:'text-center'},
                {data: 'jumlah_cn', name: 'jumlah_cn', orderable: false, searchable: true, class:'text-center'},
                {data: 'total', name: 'total', orderable: false, searchable: true, class:'text-right'}
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

                // set total pembayaran
                var total = settings['jqXHR']['responseJSON']['total_penjualan'];
                var total_rp = settings['jqXHR']['responseJSON']['total_penjualan_format'];
              	var penjualan = settings['jqXHR']['responseJSON']['penjualan'];
                var nama_dokter = settings['jqXHR']['responseJSON']['nama_dokter'];
                var nama_jasa_resep = settings['jqXHR']['responseJSON']['nama_jasa_resep'];
                var nama_paket = settings['jqXHR']['responseJSON']['nama_paket'];
                var nama_karyawan = settings['jqXHR']['responseJSON']['nama_karyawan'];
                var nama_lab = settings['jqXHR']['responseJSON']['nama_lab'];
                var counter = settings['jqXHR']['responseJSON']['counter'];
                
               // console.log(penjualan);
               var biaya_resep = 0;
               var biaya_jasa_dokter = 0;
               if(penjualan.biaya_resep != null) {
               		biaya_resep = penjualan.biaya_resep;
               }
               if(penjualan.biaya_jasa_dokter != null) {
               		biaya_jasa_dokter = penjualan.biaya_jasa_dokter;
               }

	            $("#id_dokter").val(penjualan.id_dokter);
			    $("#id_dokter_input").html("Dokter : "+nama_dokter);
			    $("#biaya_jasa_dokter_input").html(biaya_jasa_dokter);
			    $("#biaya_jasa_dokter").val(biaya_jasa_dokter);
		        
		        if(penjualan.id_jasa_resep != '') {
		    	    $("#id_jasa_resep_input").html(penjualan.id_jasa_resep);
		    	    $("#id_jasa_resep").val(penjualan.id_jasa_resep);
		    	    $("#biaya_resep_input").html(biaya_resep);
		    	    $("#biaya_resep").val(biaya_resep);
		        } else {
		            biaya_resep = 0;
		            $("#id_jasa_resep_input").html('-');
		            $("#id_jasa_resep").val(0);
		            $("#biaya_resep").val(0);
		            $("#biaya_resep_input").html("0");
		        }
		       	
		       	var biaya_lab = 0;
               	if(penjualan.biaya_lab != null) {
               		biaya_lab = penjualan.biaya_lab;
               	}

			    $("#nama_lab_input").val(nama_lab);
			    $("#nama_lab_input").html("Laboratorium : "+nama_lab);
			    $("#biaya_lab_input").html(biaya_lab);
			    $("#biaya_lab").val(biaya_lab);
		        $("#nama_lab").val(nama_lab);
		        $("#keterangan_lab").val(penjualan.keterangan_lab);

		        var biaya_apd = 0;
               	if(penjualan.biaya_apd != null) {
               		biaya_apd = penjualan.biaya_apd;
               	}
               	
			    $("#biaya_apd_input").html(penjualan.biaya_apd);
			    $("#biaya_apd").val(biaya_apd);
	    	
	    		var harga_wd = 0;
               	if(penjualan.harga_wd != null) {
               		harga_wd = penjualan.harga_wd;
               	}

			    $("#id_paket_wd").val(penjualan.id_paket_wd);
			    $("#id_paket_wd_input").html("Paket WD : "+nama_paket);
			    $("#harga_wd_input").html(harga_wd);
			    $("#harga_wd").val(harga_wd);


			    var diskon_persen = 0;
               	if(penjualan.diskon_persen != null) {
               		diskon_persen = penjualan.diskon_persen;
               	}

			    hitung = ((parseFloat(diskon_persen))/100) * parseFloat(total);
			    hitung_diskon = parseFloat(hitung);
			    $("#diskon_persen").val(diskon_persen);
			    $("#id_karyawan").val(penjualan.id_karyawan);
			    $("#diskon_persen_input").html("Karyawan : "+nama_karyawan);
			    $("#diskon_total").html(hitung_diskon);
			    $("#diskon_total_input").val(hitung_diskon);

			    var diskon_vendor = 0;
               	if(penjualan.diskon_vendor != null) {
               		diskon_vendor = penjualan.diskon_vendor;
               	}

               	hitung_vendor = ((parseFloat(diskon_vendor))/100) * parseFloat(total);
			    hitung_vendor = hitung_rp_khusus(hitung_vendor);;
			    $("#diskon_vendor_total").html(hitung_vendor);
			    $("#diskon_vendor_total_input").val(hitung_vendor);


		        /////////////// TOTAL ////////////////////////
		        var total_biaya_dokter = parseFloat(biaya_resep) + parseFloat(biaya_jasa_dokter);	   

		        var total_byr = (parseFloat(total) + parseFloat(total_biaya_dokter) + parseFloat(biaya_lab)+ parseFloat(biaya_apd) + parseFloat(harga_wd)) - (parseFloat(hitung_diskon) + parseFloat(hitung_vendor));

		        var total_byr_rp = hitung_rp_khusus(total_byr);

			    $("#total_biaya_dokter_input").html(total_biaya_dokter);
			    $("#total_biaya_dokter").val(total_biaya_dokter);
              
                $("#harga_total").html(total);
                $("#harga_total_input").val(total);
                $("#counter").val(counter);

                $("#total_pembayaran").html(total_byr);
		        $("#total_pembayaran_input").val(total_byr);
		        $("#total_pembayaran_display").html("Rp "+ total_byr_rp +", -");
		        $("#count_total_belanja").val(total_byr);
			    $('#modal-xl').modal("hide");
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

            }
        });

	var tb_penjualan_retur = $('#tb_penjualan_retur').dataTable( {
			processing: true,
	        serverSide: true,
	        stateSave: true,
	        ajax:{
			        url: '{{url("penjualan/list_penjualan_retur")}}',
			        data:function(d){
			        	d.id = $("#id").val();
				    }
			     },
	        columns: [
	            {data: 'no', name: 'no',width:"2%", class:'text-center'},
	            {data: 'tanggal', name: 'tanggal', class:'text-center', orderable: true, searchable: true},
	            {data: 'detail_obat', name: 'detail_obat', orderable: false, searchable: false},
	            {data: 'kasir', name: 'kasir', class:'text-center', orderable: false, searchable: false},
	            {data: 'alasan', name: 'alasan', orderable: false, searchable: false},
	            {data: 'status', name: 'status', class:'text-center', orderable: false, searchable: false},
	            {data: 'aprove', name: 'aprove', class:'text-center', orderable: false, searchable: false},
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
		token = $('input[name="_token"]').val();

		$('.input_select').select2();

		$("#pasien").keypress(function(event){
		    if (event.which == '10' || event.which == '13') {
		    	cari_pasien();
		        event.preventDefault();
		    }
		});

		$("#barcode").focus();
		$("#barcode").keypress(function(event){
		    if (event.which == '10' || event.which == '13') {
		    	cari_obat();
		        event.preventDefault();
		    }
		});

		$("#margin").keypress(function(event){
		    if (event.which == '10' || event.which == '13') {
		    	hitung_harga();
		    	event.preventDefault();
		    }
		});

		$("#margin").keyup(function(){
			var is_margin = $("#is_margin").val();
		    if(is_margin == 1) {
	        	var margin = $("#margin").val();
	        	var jumlah = $("#jumlah").val();
		        var hb_ppn = $("#hb_ppn").val();
		        var harga_jual_new = (parseFloat(margin)/100*parseFloat(hb_ppn)) + parseFloat(hb_ppn);
		        $("#harga_jual").val(harga_jual_new);
	        }
		});

		$("#jumlah").keypress(function(event){
			var is_margin = $("#is_margin").val();
        	if(is_margin == 1) {
        		// cari hbppn averagenya
        		if (event.which == '10' || event.which == '13') {
	        		getHbppn();
	        	}
        	} else {
			    if (event.which == '10' || event.which == '13') {
			    	var cek_ = cek_kelengkapan_form();
			        if(cek_ == 1) {
					 	var hb_ppn = $("#hb_ppn").val();
				        var harga_jual = $("#harga_jual").val();
			        	var deviasi_untung = parseFloat(hb_ppn) + (5/100 * parseFloat(hb_ppn));

				        if(harga_jual < deviasi_untung) {
				        	var hak_akses = $("#hak_akses_margin").val();
				        	if(hak_akses == 1) {
				        		var cek_ = cek_kelengkapan_form();
						        if(cek_ == 1) {
						        	$("#is_margin_kurang").val(1);
				        			simpan_data();
				        		} else {
									show_error("Data item penjualan tidak lengkap!");
								}
				        	} else {
					        	Swal.fire("Harga Jual Tidak Sesuai!", "Harga jual tidak sesuai, margin dibawah 5%! Mohon cek data kembali.", "error");
				        	}
				        }  else {
				        	var cek_ = cek_kelengkapan_form();
					        if(cek_ == 1) {
			        			simpan_data();
			        		} else {
								show_error("Data item penjualan tidak lengkap!");
							}
				        }
					} else {
						show_error("Data item penjualan tidak lengkap!");
					}
			        event.preventDefault();
			    }
		    }
		});

		/*$("#jumlah").keyup(function(event){
	        cek_diskon_item();
		});*/

		$(document).on("keyup", function(e){
		  	var x = e.keyCode || e.which;
		    if (x == 16) {  
		    	// fungsi shift 
		        $("#barcode").focus();
		    } else if(x==113){
		    	// fungsi F2 
		    	var id = $("#id").val();
		    	var is_kredit = $("#is_kredit").val();
		    	if(id == "" || id == null) {
		    		if(is_kredit == 1) {
		    			submit_valid();
		    		} else {
		    			open_pembayaran();
		    		}
		    	} 
		    } else if(x==115){
		    	// fungsi F4
		    	$("#jumlah").focus();
		    } else if(x==118){
		    	// fungsi F7
		    	// tidak bisa dipakai
		    } else if(x==119){
		    	// fungsi F8
		    	set_jasa_dokter();
		    } else if(x==120){
		    	// fungsi F9
		    	set_diskon_persen();
		    } else if(x==121){
		    	// fungsi F10
		    	find_ketentuan_keyboard();
		    } else if(x == 17) {
		    	open_data_obat('');
		    }
		})

        $('body').addClass('sidebar-collapse');

        $('#tgl_jatuh_tempo').datepicker({
            autoclose:true,
            format:"yyyy-mm-dd",
            forceParse: false
        });

        unHideDiskon();
	})

	function goBack() {
	    window.history.back();
	}

	function hitung_harga() {
        var is_margin = $("#is_margin").val();
        if(is_margin == 1) {
        	var margin = $("#margin").val();
        	var jumlah = $("#jumlah").val();


	        if(margin >= 5) {
		        var hb_ppn = $("#hb_ppn").val();
		        var harga_jual_new = (parseFloat(margin)/100*parseFloat(hb_ppn)) + parseFloat(hb_ppn);
		        $("#harga_jual").val(harga_jual_new);
	        } else {
	        	var hak_akses = $("#hak_akses_margin").val();
	        	if(hak_akses == 1) {
	        		var hb_ppn = $("#hb_ppn").val();
			        var harga_jual_new = (parseFloat(margin)/100*parseFloat(hb_ppn)) + parseFloat(hb_ppn);
			        $("#harga_jual").val(harga_jual_new);
			        $("#is_margin_kurang").val(1);
	        	} else {
		        	Swal.fire("Harga Jual Tidak Sesuai!", "Harga jual tidak sesuai, margin dibawah 5%! Mohon cek data kembali.", "error");
	        		return false;
	        	}
	        }

	        var hb_ppn = $("#hb_ppn").val();
	        var harga_jual = $("#harga_jual").val();
        	var deviasi_untung = parseFloat(hb_ppn) + (5/100 * parseFloat(hb_ppn));

	        if(harga_jual < deviasi_untung) {
	        	var hak_akses = $("#hak_akses_margin").val();
	        	if(hak_akses == 1) {
	        		var cek_ = cek_kelengkapan_form();
			        if(cek_ == 1) {
			        	$("#is_margin_kurang").val(1);
	        			simpan_data();
	        		} else {
						show_error("Data item penjualan tidak lengkap!");
					}
	        	} else {
		        	Swal.fire("Harga Jual Tidak Sesuai!", "Harga jual tidak sesuai, margin dibawah 5%! Mohon cek data kembali.", "error");
	        		return false;
	        	}
	        }  else {
	        	var cek_ = cek_kelengkapan_form();
		        if(cek_ == 1) {
        			simpan_data();
        		} else {
					show_error("Data item penjualan tidak lengkap!");
				}
	        }
        }
    }

    function getHbppn() {
    	var id = $("#id").val();
    	var id_obat = $("#id_obat").val();
        var jumlah = $("#jumlah").val();
    	$.ajax({
            type: "POST",
            url: '{{url("penjualan/getHbppn")}}',
            async:true,
            data: {
                _token:token,
                id:id,
                id_obat:id_obat,
                jumlah:jumlah
            },
            beforeSend: function(data){
                // replace dengan fungsi loading
            },
            success:  function(data) {
                if(data.status==1){
                  	$("#hb_ppn").val(data.hb_ppn);
                  	$("#margin").focus();
                }else{
                	show_error(data.message)
	    			return false;
                }
            },
            complete: function(data){
            },
            error: function(data) {
                show_error("Error!", "Ajax occured.", "error");
            }
        });
    }

	function cek_diskon_item() {
		var inisial = $("#inisial").val();
		var id_obat = $("#id_obat").val();
		var harga_jual = $("#harga_jual").val();
        var diskon = $("#diskon").val();
        var jumlah = $("#jumlah").val();
		$.ajax({
            url:'{{url("penjualan/cek_diskon_item")}}',
            type: 'POST',
            data: {
                _token      : "{{csrf_token()}}",
                id_obat: id_obat,
                inisial: inisial,
                jumlah: jumlah,
                harga_jual: harga_jual,
            },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success:function(data){
            	if(data.is_data == 1) {
            		$("#diskon").val(data.diskon);
			        //$("#jumlah").focus();
            	} else {
            		$("#diskon").val(0);
			        //$("#jumlah").focus();
            	}
            }
        });
	}

	function check_vendor(obj) {
      	var myoption = obj.options[obj.selectedIndex];
      	var uid = myoption.dataset.diskon;
      	var statusId = myoption.dataset["diskon"];
      	$("#diskon_vendor").val(uid);
	}

	function cari_pasien() {
		var pasien = $("#pasien").val();
		open_data_pasien(pasien);
	}

	function add_pasien_dialog(id) {
		$.ajax({
            url:'{{url("penjualan/cari_pasien_dialog")}}',
            type: 'POST',
            data: {
                _token      : "{{csrf_token()}}",
                id: id
            },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success:function(data){
        		$("#pasien").val(data.nama);
        		$("#id_pasien").val(data.id);
        		$("#barcode").focus();
		        $('#modal-xl').modal('toggle');
            }
        });
	}

	function open_data_pasien(pasien) {
		$.ajax({
            type: "POST",
            url: '{{url("penjualan/open_data_pasien")}}',
            async:true,
            data: {
                _token  : "{{csrf_token()}}",
                pasien : pasien,
            },
            beforeSend: function(data){
                // on_load();
                $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
                $("#modal-xl .modal-title").html("Data Pasien");
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
                show_error("error ajax occured!");
              }
        });
	}


	function cari_obat() {
		var barcode = parseInt($("#barcode").val());
		var inisial = $("#inisial").val();
		if(Number.isInteger(barcode)) {
			$.ajax({
	            url:'{{url("penjualan/cari_obat")}}',
	            type: 'POST',
	            data: {
	                _token      : "{{csrf_token()}}",
	                barcode: barcode,
	                inisial: inisial
	            },
	            dataType: 'json',
	            headers: {
	                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	            },
	            success:function(data){
	            	if(data.is_data == 1) {
	            		var harga_jual_new = parseInt(data.harga_stok.harga_jual);
	            		var harga_beli_ppn_new = parseInt(data.harga_stok.harga_beli_ppn);
	            		if(harga_jual_new <= harga_beli_ppn_new) {
                            show_error("Alert! Harga beli ppn lebih besar daripada harga jual, item dapat diinput setelah data disesuaikan!");
                            kosongkan_form();
                        } else {
		            		$("#barcode").val(data.obat.barcode);
		            		$("#id_obat").val(data.obat.id);
			            	$("#nama_obat").val(data.obat.nama);
			            	$("#hb_ppn").val(data.harga_stok.harga_beli_ppn);
			                $("#harga_jual_default").val(data.harga_stok.harga_jual_default);
			                $("#harga_jual").val(data.harga_stok.harga_jual);
			                $("#stok_obat").val(data.harga_stok.stok_akhir);
					        $("#diskon").val(0);
					        
					        if($("#margin").is(":hidden")) {
					        	$("#margin").val(data.obat.untung_jual);
							    $("#jumlah").focus();
							} else {
								//$("#margin").focus();
								$("#jumlah").focus();
							}
					    }
	            	} else {
	            		show_error("Obat dengan barcode tersebut tidak dapat ditemukan!");
	            		kosongkan_form();
	            	}
	            	
	            }
	        });
		} else {
			open_data_obat(barcode);
		}		
	}

	function add_item_dialog(id_obat, harga_jual, harga_beli, stok_akhir, harga_beli_ppn, untung_jual) {
		var inisial = $("#inisial").val();
		$.ajax({
            url:'{{url("penjualan/cari_obat_dialog")}}',
            type: 'POST',
            data: {
                _token      : "{{csrf_token()}}",
                id_obat: id_obat,
                inisial: inisial
            },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success:function(data){
            	var harga_jual_new = parseInt(harga_jual);
	            var harga_beli_ppn_new = parseInt(harga_beli_ppn);
            	if(harga_jual_new <= harga_beli_ppn_new) {
                    $('#modal-xl').modal('toggle');
                    show_error("Alert! Harga beli ppn lebih besar daripada harga jual, item dapat diinput setelah data disesuaikan!");
                    kosongkan_form();
                } else {
	        		$("#barcode").val(data.barcode);
	        		$("#id_obat").val(data.id);
	            	$("#nama_obat").val(data.nama);
	                $("#hb_ppn").val(harga_beli_ppn);
	                $("#harga_jual").val(harga_jual);
	                $("#harga_jual_default").val(harga_jual);
	                $("#stok_obat").val(stok_akhir);
			        $("#diskon").val(0);

			        if($("#margin").is(":hidden")) {
			        	$("#margin").val(untung_jual);
					    $("#jumlah").focus();
					} else {
						//$("#margin").focus();
						$("#jumlah").focus();
					}
			        
			        $('#modal-xl').modal('toggle');
			    }
            }
        });
	}


	function kosongkan_form(){
		$("#barcode").val('');
		$("#id_obat").val('');
    	$("#nama_obat").val('');
        $("#harga_jual").val('');
        $("#stok_obat").val('');
        $("#diskon").val('');
        $("#jumlah").val('');
        $("#hb_ppn").val('');
		$("#harga_jual_default").val('');
		$("#margin").val('');
        $("#barcode").focus();
	}

	$("#add_row_penjualan").click(function(){
		var cek_ = cek_kelengkapan_form();
        if(cek_ == 1) {
        	var is_margin = $("#is_margin").val();
	        if(is_margin == 1) {
	        	var margin = $("#margin").val();
	        	var jumlah = $("#jumlah").val();
		        if(margin >= 5) {
			        var hb_ppn = $("#hb_ppn").val();
			        var harga_jual_new = (parseFloat(margin)/100*parseFloat(hb_ppn)) + parseFloat(hb_ppn);
			        $("#harga_jual").val(harga_jual_new);
		        } else {
		        	var hak_akses = $("#hak_akses_margin").val();
		        	if(hak_akses == 1) {
		        		var hb_ppn = $("#hb_ppn").val();
				        var harga_jual_new = (parseFloat(margin)/100*parseFloat(hb_ppn)) + parseFloat(hb_ppn);
				        $("#harga_jual").val(harga_jual_new);
				        $("#is_margin_kurang").val(1);
		        	} else {
			        	Swal.fire("Harga Jual Tidak Sesuai!", "Harga jual tidak sesuai, margin dibawah 5%! Mohon cek data kembali.", "error");
			        	return false;
		        	}
		        }
	        }

			var hb_ppn = $("#hb_ppn").val();
	        var harga_jual = $("#harga_jual").val();
        	var deviasi_untung = parseFloat(hb_ppn) + (5/100 * parseFloat(hb_ppn));

	        if(harga_jual < deviasi_untung) {
	        	var hak_akses = $("#hak_akses_margin").val();
		        if(hak_akses == 1) {
		        	$("#is_margin_kurang").val(1);
		        	var cek_ = cek_kelengkapan_form();
			        if(cek_ == 1) {
	        			simpan_data();
	        		} else {
						show_error("Data item penjualan tidak lengkap!");
					}
		        } else {
	            	Swal.fire("Harga Jual Tidak Sesuai!", "Harga jual tidak sesuai, margin dibawah 5%! Mohon cek data kembali.", "error");
	            }
	        }  else {
	        	var cek_ = cek_kelengkapan_form();
		        if(cek_ == 1) {
        			simpan_data();
        		} else {
					show_error("Data item penjualan tidak lengkap!");
				}
	        }
		} else {
			show_error("Data item penjualan tidak lengkap!");
		}
    });

    function cek_kelengkapan_form() {
    	var barcode = $("#barcode").val();
		var id_obat = $("#id_obat").val();
    	var nama_obat = $("#nama_obat").val();
        var harga_jual = $("#harga_jual").val();
        var stok_obat = $("#stok_obat").val();
        var diskon = $("#diskon").val();
        var jumlah = $("#jumlah").val();
        var margin = $("#margin").val();
        if(barcode != '' && id_obat != '' && nama_obat != '' && harga_jual != '' && stok_obat != '' && diskon != '' && jumlah != '' && margin != '') {
        	return 1;
        } else {
        	return 2;
        }
    }

    function simpan_data() {
        data = {};
        $("#form_penjualan").find("input[name], select").each(function (index, node) {
            data[node.name] = node.value;
        });

        var id = $("#id").val();
        if(id) {
            $.ajax({
                type:"PUT",
                url : '{{url("penjualan/update_item")}}/'+id,
                dataType : "json",
                data : data,
                beforeSend: function(data){
                    // replace dengan fungsi loading
                    spinner.show();
                },
                success:  function(data){
                    if(data.status ==1){
                        kosongkan_form();
                        unHideDiskon();
                    }else{
                        //show_error(data.message);
                        Swal.fire(data.message, "error");
                        return false;
                    }
                },
                complete: function(data){
                    tb_nota_penjualan.fnDraw(false);
                    spinner.hide();
                },
                error: function(data) {
                    show_error("error ajax occured!");
                }

            });
        } else {
            $.ajax({
                type:"POST",
                url : '{{url("penjualan/add_item")}}',
                dataType : "json",
                data : data,
                beforeSend: function(data){
                    // replace dengan fungsi loading
                    spinner.show();
                },
                success:  function(data){
                    if(data.status == 1){
                        kosongkan_form();
                        $("#id").val(data.id);
                        unHideDiskon();
                    }else{
                        //show_error(data.message);
                        Swal.fire(data.message, "error");
                        return false;
                    }
                },
                complete: function(data){
                    tb_nota_penjualan.fnDraw(false);
                    spinner.hide();
                },
                error: function(data) {
                    show_error("error ajax occured!");
                }

            });
        }
    }

    function unHideDiskon() {
    	var id = $("#id").val();
    	if(id != "") {
    		$(".unHideDiskon").show();
    	} else {
    		$(".unHideDiskon").hide();
    	}
    }

    function hitung_rp_khusus(nilai) {
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

	function tambah_item_obat(){
		var is_kredit = $("#is_kredit").val();
		var id_vendor = $("#id_vendor").val();
		if(is_kredit == 1 && id_vendor == "") {
			show_error("Penjualan melalui belum dipilih, silakan pilih kategori tersebut terlebih dahulu !");
		} else {
		 	var counter = $("#counter").val();
		 	var id_obat = $("#id_obat").val();
		 	var hb_ppn = $("#hb_ppn").val();
		 	var id_obat = $("#id_obat").val();
		 	var nama_obat = $('#nama_obat').val();
	        var harga_jual = $("#harga_jual").val();
	        var margin = $("#margin").val();
	        var harga_jual_rp = hitung_rp(harga_jual);
	        var stok_obat = parseFloat($("#stok_obat").val());
	        var diskon = $("#diskon").val();
	        var diskon_rp = hitung_rp(diskon);
	        var jumlah = parseFloat($("#jumlah").val());
	        var total = (jumlah * (harga_jual));
	        total = total-diskon;
	        var total_rp = hitung_rp(total);

	        var deviasi_untung = parseFloat(hb_ppn) + (5/100 * parseFloat(hb_ppn));
	        var input = true;

	        //alert(hb_ppn);
	       /* console.log(harga_jual);
	        console.log(deviasi_untung);*/

	        if(harga_jual < deviasi_untung) {
	        	var hak_akses = $("#hak_akses_margin").val();
	        	if(hak_akses == 1) {
	        		$("#is_margin_kurang").val(1);
	        		input = true;
	        	} else {
		        	Swal.fire("Harga Jual Tidak Sesuai!", "Harga jual tidak sesuai, margin dibawah 5%! Mohon cek data kembali.", "error");
	            	input = false;
	        	}
	        } 

	        if(input) {
		        if(stok_obat >= jumlah) {
			        var markup = "<tr>"+
			        				"<td><input type='checkbox' name='record'>"
			        				+"<input type='hidden' id='id_"+counter+"' name='detail_penjualan["+counter+"][id]'><span class='label label-primary btn-sm' onClick='deleteRow(this)' data-toggle='tooltip' data-placement='top' title='Hapus Data'><i class='fa fa-edit'></i> Hapus</span></td> "+
			        				"<td style='display:none;'><input type='hidden' id='detail_penjualan["+counter+"][id_obat]' name='detail_penjualan["+counter+"][id_obat]' value='"+id_obat+"' data-id-obat='"+id_obat+"'>" + id_obat + "</td>"+
			        				"<td><input type='hidden' id='nama_obat_"+counter+"' name='detail_penjualan["+counter+"][nama_obat]' value='"+nama_obat+"'>" + nama_obat + "</td>"+
			        				"<td style='text-align:right;'><input type='hidden' id='harga_jual_"+counter+"' name='detail_penjualan["+counter+"][harga_jual]' value='"+harga_jual+"'>" + harga_jual + "</td>"+
			        				"<td style='display:none;'><input type='hidden' id='margin_"+counter+"' name='detail_penjualan["+counter+"][margin]' value='"+margin+"'>" + margin + "</td>"+
			        				"<td style='display:none;'><input type='hidden' id='hb_ppn_"+counter+"' name='detail_penjualan["+counter+"][hb_ppn]' value='"+hb_ppn+"'>" + hb_ppn + "</td>"+
			        				"<td style='text-align:right;'><input type='hidden' id='diskon_"+counter+"' name='detail_penjualan["+counter+"][diskon]' value='"+diskon+"' class='diskon' data-id-obat='"+id_obat+"'><span class='diskon_label'>"+ diskon +"</span></td>"+
			        				"<td style='display:none;' id='hitung_diskon_"+counter+"' class='hitung_diskon'>" + diskon + "</td>"+
			        				"<td style='text-align:center;'><input type='hidden' id='jumlah_"+counter+"' name='detail_penjualan["+counter+"][jumlah]' value='"+jumlah+"' class='jumlah' data-id-obat='"+id_obat+"'><span class='jumlah_label'>" + jumlah + "</span></td>"+
			        				"<td><input type='hidden' id='jumlah_cn_"+counter+"' name='detail_penjualan["+counter+"][jumlah_cn]' value='0'>0</td>"+
			        				"<td style='display:none;' id='hitung_total_"+counter+"' class='hitung_total' data-total='"+total+"'>" + total + "</td>"+
			        				"<td style='text-align:right;' id='total_"+counter+"'><input type='hidden' class='total' data-id-obat='"+id_obat+"' value='"+total+"'><span class='total_label'>" + total + "</span></td>"+
			        			"</tr>";
			        
			        
			        var jumlah_label = $(".jumlah_label");
			        var diskon_label = $(".diskon_label");
			        var total_label = $(".total_label");
			        var status_append = true;

			        $(".jumlah").each(function(i,l){
					  	if($(l).data("id-obat")== id_obat){
						    var nilai_jumlah = parseFloat($(l).val());
						    if(isNaN(nilai_jumlah)){
						    	nilai_jumlah = 0;
						    }

						    var jumlah_var = parseFloat( jumlah );
						    if(isNaN(jumlah_var)){
						    	jumlah_var = 0;
						    }
						    
						    //var new_jumlah = jumlah_var+nilai_jumlah;
						    var new_jumlah = jumlah_var;

						    $(l).val(new_jumlah);
						    $(jumlah_label[i]).html(new_jumlah);

					  		status_append = false;
					  	}
					})

					$(".diskon").each(function(i,l){
					  	if($(l).data("id-obat")== id_obat){
						    var nilai_diskon = parseFloat($(l).val());
						    if(isNaN(nilai_diskon)){
						    	nilai_diskon = 0;
						    }

						    var diskon_var = parseFloat( diskon );
						    if(isNaN(diskon_var)){
						    	diskon_var = 0;
						    }
						    
						    //var new_diskon = diskon_var+nilai_diskon;
						    var new_diskon = diskon_var;

						    $(l).val(new_diskon);
						    $(diskon_label[i]).html(new_diskon);
						    $("#hitung_diskon_"+i).html(new_diskon);

					  		status_append = false;
					  	}
					})

					$(".total").each(function(i,l){
					  	if($(l).data("id-obat")== id_obat){
						    var nilai_total = parseFloat($(l).val());
						    if(isNaN(nilai_total)){
						    	nilai_total = 0;
						    }

						    var total_var = parseFloat( total );
						    if(isNaN(total_var)){
						    	total_var = 0;
						    }
						    
						    //var new_total = total_var+nilai_total;
						    var new_total = total_var;

						    $(l).val(new_total);
						    $(total_label[i]).html(new_total);
						    $("#hitung_total_"+i).html(new_total);

					  		status_append = false;
					  	}
					})

					if(status_append == true){
			        	$("#tb_nota_penjualan tbody").append(markup);
			        	current_counter = parseFloat($("#counter").val());
				        if(isNaN(current_counter)){
				            current_counter = 0;
				        }
				          
				        $("#counter").val(current_counter+1);
					}

			        hitung_total();

			    	// hapus seluruh data ditempat input
			    	kosongkan_form();
			    } else {
			    	show_error("Stok obat tidak mencukupi untuk melakukan transaksi ini!");
			    }
		    }
		}
	}

	function hitung_total(){
		// ini untuk hitung total penjualan
        var tes = $('.hitung_total');
        var total = 0;
        $(tes).each(function(i,l){
        	sub_total = parseFloat( $(l).data('total') );
        	if(isNaN(sub_total)){
        		sub_total = 0;
        	}

        	total = total+sub_total;
        })

        var total_rp = hitung_rp(total);
    	$("#harga_total").html(total);
    	$("#harga_total_input").val(total);
    	// ini untuk hitung diskon
    	//var diskon = $('.hitung_diskon');
        var t_diskon = 0;
       /* $(diskon).each(function(i,l){
        	sub_diskon = parseFloat( $(l).html() );
        	if(isNaN(sub_diskon)){
        		sub_diskon = 0;
        	}
        	t_diskon = t_diskon+sub_diskon;
        })*/

        var harga_wd = $("#harga_wd").val()
        if(harga_wd == "") {
        	harga_wd = 0;
        }
        var harga_wd_rp = hitung_rp(harga_wd);
	    $("#harga_wd_input").html(harga_wd);

	    var biaya_lab = $("#biaya_lab").val()
        if(biaya_lab == "") {
        	biaya_lab = 0;
        }
        var biaya_lab_rp = hitung_rp(biaya_lab);
	    $("#biaya_lab_input").html(biaya_lab);

	    var biaya_apd = $("#biaya_apd").val()
        if(biaya_apd == "") {
        	biaya_apd = 0;
        }
        var biaya_apd_rp = hitung_rp(biaya_apd);
	    $("#biaya_apd_input").html(biaya_apd);

	    ////

        var biaya_jasa_dokter = $("#biaya_jasa_dokter").val()
        if(biaya_jasa_dokter == "") {
        	biaya_jasa_dokter = 0;
        }
        var biaya_jasa_dokter_rp = hitung_rp(biaya_jasa_dokter);
	    $("#biaya_jasa_dokter_input").html(biaya_jasa_dokter);

	    var biaya_resep = $("#biaya_resep").val();
	    if(biaya_resep == "") {
        	biaya_resep = 0;
        }
        var biaya_resep_rp = hitung_rp(biaya_resep);
	    $("#biaya_resep_input").html(biaya_resep);

	    total_biaya_dokter = parseFloat(biaya_jasa_dokter) + parseFloat(biaya_resep);
	    if(total_biaya_dokter == "") {
        	total_biaya_dokter = 0;
        }
        var total_biaya_dokter_rp = hitung_rp(total_biaya_dokter);
	    $("#total_biaya_dokter_input").html(total_biaya_dokter);

    	$("#diskon_total").html(t_diskon);
    	$("#diskon_total_input").val(t_diskon);
    	$("#biaya_jasa_dokter").html("Rp 0");
    	$("#biaya_jasa_dokter_input").val(0);

    	// hitung jumlah bayar
        if(total_biaya_dokter == "") {
        	total_biaya_dokter = 0;
        }

        var diskon_total_awal = $("#diskon_total_input").val();

        var diskon_persen = $("#diskon_persen").val();
        
        if(diskon_persen != "") {
        	x = parseFloat(total);//+ parseFloat(total_biaya_dokter);
    		hitung2_ = ((parseFloat(diskon_persen))/100) * x;
    	} else {
    		hitung2_ = 0;
    	}
	    hitung_diskon = parseFloat(diskon_total_awal) + parseFloat(hitung2_);
	    var diskon_total_rp = hitung_rp(hitung_diskon);
	    $("#diskon_total").html(hitung_diskon);
	    $("#diskon_total_input").val(hitung_diskon);

    	var total_byr = (parseFloat(total) + parseFloat(total_biaya_dokter) + parseFloat(harga_wd) + parseFloat(biaya_lab) + parseFloat(biaya_apd)) - (parseFloat(t_diskon)+parseFloat(hitung_diskon)); 
    	var total_byr_rp = hitung_rp(total_byr);
    	$("#total_pembayaran").html("Rp "+ total_byr_rp +", -");
    	$("#total_pembayaran_input").val(total_byr);
    	$("#total_pembayaran_display").html("Rp "+ total_byr_rp +", -");
    	$("#count_total_belanja").val(total_byr);

    	var is_kredit = $("#is_kredit").val();
    	if(is_kredit == 1) {
	    	var diskon_vendor = $("#diskon_vendor").val();
			if(diskon_vendor != "") {
				var harga_total_awal = $("#total_pembayaran_input").val();
			    if(diskon_total_awal == "") {
			    	diskon_total_awal = 0;
			    }

			    hitung = ((parseFloat(diskon_vendor))/100) * parseFloat(harga_total_awal);
		    	total_byr = parseFloat(harga_total_awal) - parseFloat(hitung); 
		    	var total_byr_rp = hitung_rp(total_byr);
		        $("#total_pembayaran").html("Rp "+ total_byr_rp +", -");
		        $("#total_pembayaran_display").html("Rp "+ total_byr_rp +", -");
		        $("#total_pembayaran_input").val(total_byr);
		        $("#count_total_belanja").val(total_byr);
			}
		}
	}

	function hitung_rp(nilai) {
		/*var	number_string = nilai.toString();
		var sisa 	= number_string.length % 3;
		var rupiah 	= number_string.substr(0, sisa);
		var ribuan 	= number_string.substr(sisa).match(/\d{3}/g);
				
		if (ribuan) {
			separator = sisa ? '.' : '';
			rupiah += separator + ribuan.join('.');
		}
		return rupiah;*/

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

	function hapus_item_obat() {
		$("table tbody").find('input[name="record"]').each(function(){
        	if($(this).is(":checked")){
                $(this).parents("tr").remove();

                hitung_total();
            }
        });
	}

	function open_data_obat(barcode) {
		$.ajax({
            type: "POST",
            url: '{{url("penjualan/open_data_obat")}}',
            async:true,
            data: {
                _token  : "{{csrf_token()}}",
                barcode : barcode,
            },
            beforeSend: function(data){
                // on_load();
                $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
                $("#modal-xl .modal-title").html("Data Obat");
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
                show_error("error ajax occured!");
              }
        });
	}


	function set_jasa_dokter(){
	    $.ajax({
	        type: "POST",
	        url: '{{url("penjualan/set_jasa_dokter")}}',
	        async:true,
	        data: {
	        	_token:"{{csrf_token()}}",
	        	id : $("#id").val(),
	        	biaya_jasa_dokter : $("#biaya_jasa_dokter").val(),
	        	biaya_resep : $("#biaya_resep").val(),
	        	id_dokter : $("#id_dokter").val(),
	        	id_jasa_resep : $("#id_jasa_resep").val(),
	        	harga_total:$("#total_pembayaran_input").val(),
	        },
	        beforeSend: function(data){
	          	// on_load();
		        $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
	            $("#modal-xl .modal-title").html("Jasa Dokter");
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
	            show_error("error ajax occured!");
	        }
	    });
	}

	function set_diskon_persen(){
	    $.ajax({
	        type: "POST",
	        url: '{{url("penjualan/set_diskon_persen")}}',
	        async:true,
	        data: {
	        	_token:"{{csrf_token()}}",
	        	id_karyawan : $("#id_karyawan").val(),
	        	diskon_persen : $("#diskon_persen").val(),
	        	total_penjualan:$("#harga_total_input").val(),
	        	harga_total:$("#total_pembayaran_input").val(),
	        	diskon_total:$("#diskon_total_input").val(),
	          	total_biaya_dokter : $("#total_biaya_dokter").val(),
	        },
	        beforeSend: function(data){
	          // on_load();
	        	$('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
	            $("#modal-xl .modal-title").html("Diskon Karyawan");
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
	            show_error("error ajax occured!");
	        }
	    });
	}

	function open_pembayaran() {
		var id = $("#id").val();
		if(id != null) {
			$.ajax({
		        type: "POST",
		        url: '{{url("penjualan/open_pembayaran")}}',
		        async:true,
		        data: {
		        	_token:"{{csrf_token()}}",
		        	harga_total:$("#total_pembayaran_input").val(),
		        },
		        beforeSend: function(data){
		          // on_load();
		        	$('#modal-lg').find('.modal-lg').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
		            $("#modal-lg .modal-title").html("Pembayaran");
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
		            show_error("error ajax occured!");
		        }
		    });
	    } else {
	    	show_error("Error, tidak ditemukan data yang dapat disimpan!");
	    }
	}

	function submit_valid(){
		var id = $("#id").val();
		Swal.fire({
            title: "Apakah anda yakin menyimpan data ini?",
            icon: "warning",
            showDenyButton: true,
		    showCancelButton: true,
		    confirmButtonText: "Ya",
		    denyButtonText: 'Tidak',
            closeOnConfirm: true
        }).then((result) => {
        	if (result.isConfirmed) {
			    submit_valid_konfirm(id);
			} else if (result.isDenied) {
				return false;
			}
        });
	}

	function submit_valid_konfirm(id){
		if($(".validated_form").valid()) {
			data = {};
			$("#form_penjualan").find("input[name], select").each(function (index, node) {
		        data[node.name] = node.value;
		    });

			//document.form_penjualan.submit() ;
			//$("#form_penjualan").submit();
			if(id != "") {
				$.ajax({
					type:"PUT",
					url : '{{url("penjualan/")}}/'+id,
					dataType : "json",
					data : data,
					beforeSend: function(data){
						// replace dengan fungsi loading
						spinner.show();
					},
					success:  function(data){
						if(data.status ==1){
							show_info("Data penjualan berhasil disimpan!");
							kosongkan_form();
							clear_page();
							$('#modal-lg').modal("hide");
							print_nota(data.id);
						}else{
							show_error("Gagal menyimpan data penjualan ini!");
							return false;
						}
					},
					complete: function(data){
						// replace dengan fungsi mematikan loading
						spinner.hide();
						tb_nota_penjualan.fnDraw(false);
					},
					error: function(data) {
						show_error("error ajax occured!");
					}

				});
			} else {
				$.ajax({
					type:"POST",
					url : '{{url("penjualan/")}}',
					dataType : "json",
					data : data,
					beforeSend: function(data){
						// replace dengan fungsi loading
						spinner.show();
					},
					success:  function(data){
						if(data.status ==1){
							show_info("Data penjualan berhasil disimpan!");
							kosongkan_form();
							clear_page();
							$('#modal-lg').modal("hide");
							print_nota(data.id);
						}else{
							show_error("Gagal menyimpan data penjualan ini!");
							return false;
						}
					},
					complete: function(data){
						// replace dengan fungsi mematikan loading
						spinner.hide();
						tb_nota_penjualan.fnDraw(false);
						//location.reload();
					},
					error: function(data) {
						show_error("error ajax occured!");
					}

				});
			}
		} else {
			//spinner.hide();
			return false;
		}
	}

	function clear_page() {
		$("table tbody").find('input[name="record"]').each(function(){
            $(this).parents("tr").remove();
        });

		$("#id").val('');
		// kosongkan apd
		$("#nama_apd_input").val('');
	    $("#nama_apd_input").html("Laboratorium : -");
		$("#biaya_apd_input").html('');
	    $("#biaya_apd").val('');

	    // diskon persen
	    $("#diskon_persen").val('');
	    $("#id_karyawan").val('');
	    $("#diskon_persen_input").html("Karyawan : -");
	    $("#diskon_total").html('');
	    $("#diskon_total_input").val('');

	    // dokter
	    $("#id_dokter").val('');
	    $("#id_dokter_input").html("Dokter : -");
	    $("#biaya_jasa_dokter_input").html('');
	    $("#biaya_jasa_dokter").val('');
        
        $("#id_jasa_resep_input").html('');
        $("#id_jasa_resep").val('');
        $("#biaya_resep").val('');
        $("#biaya_resep_input").html('');

        $("#total_biaya_dokter_input").html('');
	    $("#total_biaya_dokter").val('');

	    // lab
	    $("#nama_lab_input").val('');
	    $("#nama_lab_input").html("Laboratorium : -");
	    $("#biaya_lab_input").html('');
	    $("#biaya_lab").val('');
        $("#nama_lab").val('');
        $("#keterangan_lab").val('');

        // paket wd
        $("#id_paket_wd").val('');
	    $("#id_paket_wd_input").html("Paket WD : -");
	    $("#harga_wd_input").html('');
	    $("#harga_wd").val('');

	    hitung_total();
	}

	function print_nota(id) {
		window.location.replace("{{ url('penjualan/cetak_nota') }}/"+id);
		//window.open("{{ url('penjualan/cetak_nota') }}/"+id); 
		//,"_blank"
		/*$.ajax({
	        type: "GET",
	        url: '{{url("penjualan/cetak_nota_thermal")}}/'+id,
	        async:true,
	        data: {
	        	_token:"{{csrf_token()}}",
	        },
	        beforeSend: function(data){
	          // on_load();
	       
	        },
	        success:  function(data){

	        },
	        complete: function(data){
	            $("#modal-lg").find(".overlay").fadeOut("200");
	        },
	          error: function(data) {
	            show_error("error ajax occured!");
	          }
	    });*/
	}

	function find_ketentuan_keyboard(){
	    $.ajax({
	        type: "POST",
	        url: '{{url("penjualan/find_ketentuan_keyboard")}}',
	        async:true,
	        data: {
	        	_token:"{{csrf_token()}}",
	        },
	        beforeSend: function(data){
	          // on_load();
	        $('#modal-lg').find('.modal-lg').find(".modal-content").find(".modal-header").attr("class","modal-header bg-info");
	        $("#modal-lg .modal-title").html("Ketentuan Kode Keyboard");
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
	            show_error("error ajax occured!");
	          }
	    });
	}

	function edit_detail(no, id){
	    $.ajax({
	        type: "POST",
	        url: '{{url("penjualan/edit_detail")}}',
	        async:true,
	        data: {
	        	_token:"{{csrf_token()}}",
	        	no : no,
	        	id : id,
	        },
	        beforeSend: function(data){
	          // on_load();
	        $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
	        $("#modal-xl .modal-title").html("Edit Data Penjualan");
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
	            show_error("error ajax occured!");
	          }
	    });
	}

	function deleteRow(r) {
        var i = r.parentNode.parentNode.rowIndex;
        document.getElementById("tb_nota_penjualan").deleteRow(i);
        hitung_total();
    }

    function retur_item() {
    	if ($("#tb_nota_penjualan input:checkbox[name=check_list]:checked").length > 0) {
            var arr_id_detail = [];
            $("#tb_nota_penjualan input:checkbox[name=check_list]:checked").each(function(){
                arr_id_detail.push($(this).data('id'));
            })
            
            var url = '{{url("penjualan/retur_item")}}';
            var form = $('<form action="' + url + '" method="post" id="form_retur">' +
                        '<input type="hidden" name="_token" id="_token" value="{{csrf_token()}}">' +
                        '<input type="hidden" name="id_detail" value="'+ arr_id_detail +'" />' +
              '</form>');
            $('body').append(form);
            form_retur.submit();
        }
        else{
            Swal.fire({
                title: "Warning",
                text: "centang data yang akan diretur terlebih dahulu!",
                type: "error",
                timer: 5000,
                showConfirmButton: false
            });
        }
    }

    function set_jumlah_retur(id, no){
        $.ajax({
            type: "POST",
            url: '{{url("penjualan/set_jumlah_retur")}}',
            async:true,
            data: {
                _token:"{{csrf_token()}}",
                id:id,
                no : no,
            },
            beforeSend: function(data){
              // on_load();
                $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
                $("#modal-xl .modal-title").html("Setting Jumlah Retur");
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
                show_error("error ajax occured!");
            }
        });
    }

    function retur_save() {
    	if($(".validated_form").valid()) {
			data = {};
			$("#form_retur_penjualan").find("input[name], select").each(function (index, node) {
		        data[node.name] = node.value;
		    });

			$("#form_retur_penjualan").submit();
			
		} else {
			return false;
		}
    }

    function batal_retur(id){
        Swal.fire({
            title: "Apakah anda yakin membatalkan retur ini?",
            icon: "warning",
            showDenyButton: true,
		    showCancelButton: true,
		    confirmButtonText: "Ya",
		    denyButtonText: 'Tidak',
            closeOnConfirm: true
       	}).then((result) => {
        	if (result.isConfirmed) {
			    $.ajax({
	                type: "POST",
	                url: '{{url("penjualan/batal_retur")}}',
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
	                        Swal.fire("Deleted!", "Data retur berhasil dibatalkan.", "success");
	                    }else{
	                        Swal.fire("Failed!", "Gagal menyimpan data.", "error");
	                    }
	                },
	                complete: function(data){
	                    tb_penjualan_retur.fnDraw(false);
	                },
	                error: function(data) {
	                    Swal.fire("Error!", "Ajax occured.", "error");
	                }
	            });
			} else if (result.isDenied) {
				return false;
			}
        });
    }

    function set_paket(){
	    $.ajax({
	        type: "POST",
	        url: '{{url("penjualan/set_paket_wd")}}',
	        async:true,
	        data: {
	        	_token:"{{csrf_token()}}",
	        	id_paket_wd : $("#id_paket_wd").val(),
	        	harga_wd : $("#harga_wd").val(),
	        	harga_total : $("#total_pembayaran_input").val(),
	        },
	        beforeSend: function(data){
	          	// on_load();
		        $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
	            $("#modal-xl .modal-title").html("Paket WD");
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
	            show_error("error ajax occured!");
	        }
	    });
	}

	function set_pembayaran_lab(){
	    $.ajax({
	        type: "POST",
	        url: '{{url("penjualan/set_lab")}}',
	        async:true,
	        data: {
	        	_token:"{{csrf_token()}}",
	        	biaya_lab : $("#biaya_lab").val(),
	        	nama_lab : $("#nama_lab").val(),
	        	keterangan_lab : $("#keterangan_lab").val(),
	        	harga_total : $("#total_pembayaran_input").val(),
	        },
	        beforeSend: function(data){
	          	// on_load();
		        $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
	            $("#modal-xl .modal-title").html("Setting Biaya Laboratorium");
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
	            show_error("error ajax occured!");
	        }
	    });
	}

	function set_pembayaran_apd(){
	    $.ajax({
	        type: "POST",
	        url: '{{url("penjualan/set_apd")}}',
	        async:true,
	        data: {
	        	_token:"{{csrf_token()}}",
	        	biaya_apd : $("#biaya_apd").val(),
	        	harga_total : $("#total_pembayaran_input").val(),
	        },
	        beforeSend: function(data){
	          	// on_load();
		        $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
	            $("#modal-xl .modal-title").html("Setting Biaya APD");
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
	            show_error("error ajax occured!");
	        }
	    });
	}

	function checkdiskon(diskon_persen, id_karyawan) {
		var id = $("#id").val();
		var hit_true = 0;
		$.ajax({
            type: "POST",
            url: '{{url("penjualan/checkdiskon")}}',
            async:true,
            data: {
                _token:token,
                id:id,
                diskon_persen:diskon_persen
            },
            beforeSend: function(data){
                // replace dengan fungsi loading
            },
            success:  function(data) {
                if(data==1){
                  	simpandiskon(diskon_persen, id_karyawan);
                }else{
                	show_error("Diskon telah melebihi ketentuan, silakan cek kembali.")
	    			$('#modal-xl').modal("hide");
	    			return false;
                }
            },
            complete: function(data){
            	tb_nota_penjualan.fnDraw(false);
            },
            error: function(data) {
                Swal.fire("Error!", "Ajax occured.", "error");
            }
        });
	}

	function simpandiskon(diskon_persen, id_karyawan) {
		var id = $("#id").val();
		data = {};
        $("#form_penjualan").find("input[name], select").each(function (index, node) {
            data[node.name] = node.value;
        });
        data["diskon_persen"] = diskon_persen;
        data["id_karyawan"] = id_karyawan;
        var total_penjualan = $("#harga_total_input").val();

        if(total_penjualan > 0) {
            $.ajax({
                type:"PUT",
                url : '{{url("penjualan/")}}/'+id,
                dataType : "json",
                data : data,
                beforeSend: function(data){
                    // replace dengan fungsi loading
                },
                success:  function(data){
                    if(data.status ==1){
                        kosongkan_form();
                        unHideDiskon();
                    }else{
                        show_error(data.message);
                        return false;
                    }
                },
                complete: function(data){
                    tb_nota_penjualan.fnDraw(false);
                },
                error: function(data) {
                    show_error("error ajax occured!");
                }

            });
        } else {
            show_error("Nota ini tidak memiliki item penjualan, diskon tidak dapat diberikan.")
    		$('#modal-xl').modal("hide");
        }
	}

	function delete_item(element, id){
        Swal.fire({
            title: "Apakah anda yakin menghapus data ini?",
            icon: "warning",
            showDenyButton: true,
		    showCancelButton: true,
		    confirmButtonText: "Ya",
		    denyButtonText: 'Tidak',
            closeOnConfirm: true
        }).then((result) => {
        	if (result.isConfirmed) {
        		// Disable the button to prevent double-click
			    element.disabled = true;

			    // Optionally, change the appearance of the button to indicate it's been clicked
			    element.classList.add('disabled');

			    $.ajax({
	                type:"GET",
	                url : '{{url("penjualan/delete_item/")}}/'+id,
	                dataType : "json",
	                data : {},
	                beforeSend: function(data){
	                    // replace dengan fungsi loading
	                    spinner.show();
	                },
	                success:  function(data){
	                    if(data.status ==1){
	                    	kosongkan_form();
	                    	if(data.is_sisa == 1) {

	                    	} else {
	                    		// tidak ada sisa item penjualan clear semua cache
								window.location.replace('{{url("penjualan")}}/');
							}
	                        show_info("Data penjualan berhasil dihapus!");
	                        tb_nota_penjualan.fnDraw(false);
	                    }else{
	                        show_error("Gagal menghapus data penjualan ini!");
	                        return false;
	                    }
	                },
	                complete: function(data){
	                    // replace dengan fungsi mematikan loading
	                    spinner.hide();
	                    element.disabled = false;
                		element.classList.remove('disabled');
	                },
	                error: function(data) {
	                    show_error("error ajax occured!");
	                }

	            });
			} else if (result.isDenied) {
				return false;
			}
        });
    }

    function add_member(){
      	$.ajax({
          	type: "GET",
	        url: '{{url("penjualan/add_member")}}',
	        async:true,
	        data: {
	            _token		: "{{csrf_token()}}",
	        },
	        beforeSend: function(data){
	          	// on_load();
		        $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
		        $("#modal-xl .modal-title").html("Tambah Data Member");
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
	            show_error("error ajax occured!");
	        }

	    });
  	}

  	function submit_valid_member(id){
        status = $(".validated_form").valid();

        if(status) {
            data = {};
            $("#form-add-member").find("input[name], select").each(function (index, node) {
                data[node.name] = node.value;
                
            });

            $.ajax({
                type:"POST",
                url : '{{url("penjualan/store_member")}}',
                dataType : "json",
                data : data,
                beforeSend: function(data){
                    // replace dengan fungsi loading
                },
                success:  function(data){
                    if(data.status ==1){
                        show_info("Data member berhasil disimpan...");
                        $('#modal-xl').modal('toggle');
                    }else{
                        show_error("Gagal menyimpan data ini !");
                        return false;
                    }
                },
                complete: function(data){
                    // replace dengan fungsi mematikan loading
                    //tb_member.fnDraw(false);
                },
                error: function(data) {
                    show_error("error ajax occured!");
                }

            })
        }
    }
</script>