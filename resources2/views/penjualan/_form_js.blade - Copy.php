<script type="text/javascript">
	var token = "";

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

		$("#jumlah").keypress(function(event){
		    if (event.which == '10' || event.which == '13') {
		    	var cek_ = cek_kelengkapan_form();
			        if(cek_ == 1) {
						tambah_item_obat();
					} else {
						show_error("Data item penjualan tidak lengkap!");
					}
		        event.preventDefault();
		    }
		});

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
		    	open_data_obat();
		    }
		})

        $('body').addClass('sidebar-collapse');

        hitung_total();
	})

	function goBack() {
	    window.history.back();
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
                alert("error ajax occured!");
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
	            		$("#barcode").val(data.obat.barcode);
	            		$("#id_obat").val(data.obat.id);
		            	$("#nama_obat").val(data.obat.nama);
		                $("#harga_jual").val(data.harga_stok.harga_jual);
		                $("#stok_obat").val(data.harga_stok.stok_akhir);
				        $("#diskon").val(0);
				        $("#jumlah").focus();
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

	function add_item_dialog(id_obat, harga_jual, harga_beli, stok_akhir, harga_beli, ppn) {
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
        		$("#barcode").val(data.barcode);
        		$("#id_obat").val(data.id);
            	$("#nama_obat").val(data.nama);
                $("#harga_jual").val(harga_jual);
                $("#stok_obat").val(stok_akhir);
		        $("#diskon").val(0);
		        $("#jumlah").focus();
		        $('#modal-xl').modal('toggle');
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
        $("#barcode").focus();
	}

	$("#add_row_penjualan").click(function(){
		var cek_ = cek_kelengkapan_form();
        if(cek_ == 1) {
			tambah_item_obat();
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
        if(barcode != '' && id_obat != '' && nama_obat != '' && harga_jual != '' && stok_obat != '' && diskon != '' && jumlah != '') {
        	return 1;
        } else {
        	return 2;
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
		 	var nama_obat = $('#nama_obat').val();
	        var harga_jual = $("#harga_jual").val();
	        var harga_jual_rp = hitung_rp(harga_jual);
	        var stok_obat = parseInt($("#stok_obat").val());
	        var diskon = $("#diskon").val();
	        var diskon_rp = hitung_rp(diskon);
	        var jumlah = parseInt($("#jumlah").val());
	        var total = ($("#jumlah").val() * $("#harga_jual").val());
	        var total_rp = hitung_rp(total);
	        if(stok_obat >= jumlah) {
		        var markup = "<tr>"+
		        				"<td><input type='checkbox' name='record'>"
		        				+"<input type='hidden' id='detail_penjualan["+counter+"][id]' name='detail_penjualan["+counter+"][id]'><span class='label label-primary btn-sm' onClick='deleteRow(this)' data-toggle='tooltip' data-placement='top' title='Hapus Data'><i class='fa fa-edit'></i> Hapus</span></td> "+
		        				"<td style='display:none;'><input type='hidden' id='detail_penjualan["+counter+"][id_obat]' name='detail_penjualan["+counter+"][id_obat]' value='"+id_obat+"' data-id-obat='"+id_obat+"'>" + id_obat + "</td>"+
		        				"<td><input type='hidden' id='detail_penjualan["+counter+"][nama_obat]' name='detail_penjualan["+counter+"][nama_obat]' value='"+nama_obat+"'>" + nama_obat + "</td>"+
		        				"<td style='text-align:right;'><input type='hidden' id='detail_penjualan["+counter+"][harga_jual]' name='detail_penjualan["+counter+"][harga_jual]' value='"+harga_jual+"'>Rp " + harga_jual_rp + "</td>"+
		        				"<td style='text-align:right;'><input type='hidden' id='detail_penjualan["+counter+"][diskon]' name='detail_penjualan["+counter+"][diskon]' value='"+diskon+"' class='diskon' data-id-obat='"+id_obat+"'><span class='diskon_label'>"+ diskon_rp +"</span></td>"+
		        				"<td style='display:none;' id='hitung_diskon_"+counter+"' class='hitung_diskon'>" + diskon + "</td>"+
		        				"<td style='text-align:center;'><input type='hidden' id='detail_penjualan["+counter+"][jumlah]' name='detail_penjualan["+counter+"][jumlah]' value='"+jumlah+"' class='jumlah' data-id-obat='"+id_obat+"'><span class='jumlah_label'>" + jumlah + "</span></td>"+
		        				"<td style='text-align:center;'><input type='hidden' id='detail_penjualan["+counter+"][jumlah_cn]' name='detail_penjualan["+counter+"][jumlah_cn]' value='0'>0</td>"+
		        				"<td style='display:none;' id='hitung_total_"+counter+"' class='hitung_total' data-total='"+total+"'>" + total + "</td>"+
		        				"<td style='text-align:right;' id='detail_penjualan["+counter+"][total]'><input type='hidden' class='total' data-id-obat='"+id_obat+"' value='"+total+"'><span class='total_label'>Rp " + total_rp + "</span></td>"+
		        			"</tr>";
		        
		        var jumlah_label = $(".jumlah_label");
		        var diskon_label = $(".diskon_label");
		        var total_label = $(".total_label");
		        var status_append = true;

		        $(".jumlah").each(function(i,l){
				  	if($(l).data("id-obat")== id_obat){
					    var nilai_jumlah = parseInt($(l).val());
					    if(isNaN(nilai_jumlah)){
					    	nilai_jumlah = 0;
					    }

					    var jumlah_var = parseInt( jumlah );
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
					    var nilai_diskon = parseInt($(l).val());
					    if(isNaN(nilai_diskon)){
					    	nilai_diskon = 0;
					    }

					    var diskon_var = parseInt( diskon );
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
					    var nilai_total = parseInt($(l).val());
					    if(isNaN(nilai_total)){
					    	nilai_total = 0;
					    }

					    var total_var = parseInt( total );
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
		        	get_data_tabel(counter);
		        	current_counter = parseInt($("#counter").val());
			        if(isNaN(current_counter)){
			            current_counter = 0;
			        }
			          
			        $("#counter").val(current_counter+1);
				} else {
					get_data_tabel(counter-1);
				}

		        hitung_total();
		        
		    	// hapus seluruh data ditempat input
		    	kosongkan_form();
		    } else {
		    	show_error("Stok obat tidak mencukupi untuk melakukan transaksi ini!");
		    }
	    }
	}

	function get_data_tabel(counter) {
    	data = {};
		$("#form_penjualan").find("input[name], select").each(function (index, node) {
	        data[node.name] = node.value;
	    });

	   /* $("#form_penjualan" ).submit(function( event ) {
		  console.log( $( this ).serializeArray() );
		  event.preventDefault();
		});*/

	  //  var data = $("#form_penjualan").serializeArray()


		//data = $("[name='detail_penjualan']");

	   //console.log(data);
       
    	var inisial = $("#inisial").val();
		$.ajax({
            url:'{{url("penjualan/cek_diskon")}}',
            type: 'POST',
            data: {
                _token: "{{csrf_token()}}",
                data: data,
                inisial: inisial,
                counter: counter
            },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success:function(data){
        		$("#barcode").val(data.barcode);
        		$("#id_obat").val(data.id);
            	$("#nama_obat").val(data.nama);
                $("#harga_jual").val(harga_jual);
                $("#stok_obat").val(stok_akhir);
		        $("#diskon").val(0);
		        $("#jumlah").focus();
		        $('#modal-xl').modal('toggle');
            }
        });
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
    	$("#harga_total").html("Rp "+total_rp);
    	$("#harga_total_input").val(total);
    	// ini untuk hitung diskon
    	var diskon = $('.hitung_diskon');
        var t_diskon = 0;
        $(diskon).each(function(i,l){
        	sub_diskon = parseFloat( $(l).html() );
        	if(isNaN(sub_diskon)){
        		sub_diskon = 0;
        	}
        	t_diskon = t_diskon+sub_diskon;
        })

        var harga_wd = $("#harga_wd").val()
        if(harga_wd == "") {
        	harga_wd = 0;
        }
        var harga_wd_rp = hitung_rp(harga_wd);
	    $("#harga_wd_input").html("Rp "+ harga_wd_rp);

	    var biaya_lab = $("#biaya_lab").val()
        if(biaya_lab == "") {
        	biaya_lab = 0;
        }
        var biaya_lab_rp = hitung_rp(biaya_lab);
	    $("#biaya_lab_input").html("Rp "+ biaya_lab_rp);

	    var biaya_apd = $("#biaya_apd").val()
        if(biaya_apd == "") {
        	biaya_apd = 0;
        }
        var biaya_apd_rp = hitung_rp(biaya_apd);
	    $("#biaya_apd_input").html("Rp "+ biaya_apd_rp);

	    ////

        var biaya_jasa_dokter = $("#biaya_jasa_dokter").val()
        if(biaya_jasa_dokter == "") {
        	biaya_jasa_dokter = 0;
        }
        var biaya_jasa_dokter_rp = hitung_rp(biaya_jasa_dokter);
	    $("#biaya_jasa_dokter_input").html("Rp "+ biaya_jasa_dokter_rp);

	    var biaya_resep = $("#biaya_resep").val();
	    if(biaya_resep == "") {
        	biaya_resep = 0;
        }
        var biaya_resep_rp = hitung_rp(biaya_resep);
	    $("#biaya_resep_input").html("Rp "+ biaya_resep_rp);

	    total_biaya_dokter = parseFloat(biaya_jasa_dokter) + parseFloat(biaya_resep);
	    if(total_biaya_dokter == "") {
        	total_biaya_dokter = 0;
        }
        var total_biaya_dokter_rp = hitung_rp(total_biaya_dokter);
	    $("#total_biaya_dokter_input").html("Rp "+ total_biaya_dokter_rp);

    	$("#diskon_total").html("Rp "+t_diskon);
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
        	x = parseFloat(total) + parseFloat(total_biaya_dokter);
    		hitung2_ = ((parseFloat(diskon_persen))/100) * x;
    	} else {
    		hitung2_ = 0;
    	}
	    hitung_diskon = parseFloat(diskon_total_awal) + parseFloat(hitung2_);
	    var diskon_total_rp = hitung_rp(hitung_diskon);
	    $("#diskon_total").html("Rp "+diskon_total_rp);
	    $("#diskon_total_input").val(hitung_diskon);

    	var total_byr = (parseFloat(total) + parseFloat(total_biaya_dokter) + parseFloat(harga_wd) + parseFloat(biaya_lab)) - (parseFloat(t_diskon)+parseFloat(hitung_diskon)); 
    	var total_byr_rp = hitung_rp(total_byr);
    	$("#total_pembayaran").html("Rp "+ total_byr_rp);
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
		        $("#total_pembayaran").html("Rp "+ total_byr_rp);
		        var total_byr_rp = hitung_rp(total_byr);
		        $("#total_pembayaran_display").html("Rp "+ total_byr_rp +", -");
		        $("#total_pembayaran_input").val(total_byr);
		        $("#count_total_belanja").val(total_byr);
			}
		}
	}

	function hitung_rp(nilai) {
		var	number_string = nilai.toString(),
			sisa 	= number_string.length % 3,
			rupiah 	= number_string.substr(0, sisa),
			ribuan 	= number_string.substr(sisa).match(/\d{3}/g);
				
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
                alert("error ajax occured!");
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
	            alert("error ajax occured!");
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
	            alert("error ajax occured!");
	        }
	    });
	}

	function open_pembayaran() {
		$.ajax({
	        type: "POST",
	        url: '{{url("penjualan/open_pembayaran")}}',
	        async:true,
	        data: {
	        	_token:"{{csrf_token()}}",
	        	id:$("#id").val(),
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
	            alert("error ajax occured!");
	        }
	    });
	}

	function submit_valid(id){
		swal({
            title: "Apakah anda yakin menyimpan data ini?",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Ya",
            cancelButtonText: "Tidak",
            closeOnConfirm: false
        },
        function(){
            submit_valid_konfirm(id);
        });
	}

	function submit_valid_konfirm(id){
		if($(".validated_form").valid()) {
			data = {};
			$("#form_penjualan").find("input[name], select").each(function (index, node) {
		        data[node.name] = node.value;
		    });

			//document.form_penjualan.submit() ;
			$("#form_penjualan").submit();
			/*$.ajax({
				type:"POST",
				url : '{{url("penjualan/")}}',
				dataType : "json",
				data : data,
				beforeSend: function(data){
					// replace dengan fungsi loading
				},
				success:  function(data){
					if(data.status ==1){
						show_info("Data penjualan berhasil disimpan!");
						$('#modal-lg').modal("hide");
					}else{
						show_error("Gagal menyimpan data penjualan ini!");
						return false;
					}
				},
				complete: function(data){
					// replace dengan fungsi mematikan loading
				},
				error: function(data) {
					show_error("error ajax occured!");
				}

			})*/
		} else {
			return false;
		}
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
	            alert("error ajax occured!");
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
	            alert("error ajax occured!");
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
            swal({
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
                alert("error ajax occured!");
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
        swal({
            title: "Apakah anda yakin membatalkan retur ini?",
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
                        swal("Deleted!", "Data retur berhasil dibatalkan.", "success");
                    }else{
                        swal("Failed!", "Gagal menyimpan data.", "error");
                    }
                },
                complete: function(data){
                    tb_penjualan_retur.fnDraw(false);
                },
                error: function(data) {
                    swal("Error!", "Ajax occured.", "error");
                }
            });
        });
    }

    function set_paket(){
	    $.ajax({
	        type: "POST",
	        url: '{{url("penjualan/set_paket_wd")}}',
	        async:true,
	        data: {
	        	_token:"{{csrf_token()}}",
	        	harga_total:$("#total_pembayaran_input").val(),
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
	            alert("error ajax occured!");
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
	        	harga_total:$("#total_pembayaran_input").val(),
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
	            alert("error ajax occured!");
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
	        	harga_total:$("#total_pembayaran_input").val(),
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
	            alert("error ajax occured!");
	        }
	    });
	}

	function hapus_detail(r, id){
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
                url: '{{url("penjualan/hapus_detail/")}}/'+id,
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
                    	var i = r.parentNode.parentNode.rowIndex;
			        	document.getElementById("tb_nota_penjualan").deleteRow(i);
                        swal("Deleted!", "Item penjualan berhasil dihapus.", "success");
                    }else{
                        swal("Failed!", "Gagal menghapus item penjualan.", "error");
                    }
                },
                complete: function(data){
			        hitung_total();
                },
                error: function(data) {
                    swal("Error!", "Ajax occured.", "error");
                }
            });
        });
    }

</script>