<script type="text/javascript">
	var token = "";

	$(document).ready(function(){
		token = $('input[name="_token"]').val();

		$('.input_select').select2();
		$("#barcode").focus();
		$("#barcode").keypress(function(event){
		    if (event.which == '10' || event.which == '13') {
		    	cari_obat();
		        event.preventDefault();
		    }
		});

		$('#id_satuan').on("select2:select", function(e) { 
		    $("#jumlah").focus();
		});

		$("#jumlah").keypress(function(event){
		    if (event.which == '10' || event.which == '13') {
		    	tambah_item_obat();
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
		    	save_data();
		    } else if(x==115){
		    	// fungsi F4
		    	$("#jumlah").focus();
		    } else if(x==121){
		    	// fungsi F10
		    	find_ketentuan_keyboard();
		    }
		})
	})

	function goBack() {
	    window.history.back();
	}

	function cari_obat() {
		var barcode = $("#barcode").val();
		var inisial = $("#inisial").val();
		$.ajax({
            url:'{{url("transfer/cari_obat")}}',
            type: 'POST',
            data: {
                _token      : "{{csrf_token()}}",
                barcode: barcode
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
			        $("#id_satuan").focus();
            	} else {
            		show_error("Obat dengan barcode tersebut tidak dapat ditemukan!");
            		kosongkan_form();
            	}
            	
            }
        });
	}

	function add_item_dialog(id_obat) {
		var inisial = $("#inisial").val();
		$.ajax({
            url:'{{url("transfer/cari_obat_dialog")}}',
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
		        $("#id_satuan").focus();
		        $('#modal-xl').modal('toggle');
            }
        });
	}

	function kosongkan_form(){
		$("#barcode").val('');
		$("#id_obat").val('');
    	$("#nama_obat").val('');
        $("#jumlah").val('');
        $("#barcode").focus();
	}

	$("#add_row_transfer").click(function(){
		var cek_ = cek_kelengkapan_form();
        if(cek_ == 1) {
			tambah_item_obat();
		} else {
			show_error("Data item transfer tidak lengkap!");
		}
    });

	function cek_kelengkapan_form() {
    	var id_satuan = $("#id_satuan").val();
		var id_obat = $("#id_obat").val();
    	var nama_obat = $("#nama_obat").val();
        var jumlah = $("#jumlah").val();
        if(id_satuan != '' && id_obat != '' && nama_obat != '' && jumlah != '') {
        	return 1;
        } else {
        	return 2;
        }
    }
	
	function open_data_obat() {
		$.ajax({
            type: "POST",
            url: '{{url("transfer/open_data_obat")}}',
            async:true,
            data: {
                _token    : "{{csrf_token()}}",
                id_apotek : $('#id_apotek').val(),
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

	function tambah_item_obat(){
	 	var counter = $("#counter").val();
	 	var id_obat = $("#id_obat").val();
	 	var nama_obat = $('#nama_obat').val();
        var jumlah = parseInt($("#jumlah").val());
        var id_apotek = $("#id_apotek").val();
        var id_satuan = $("#id_satuan").val();
        var satuan = $("#id_satuan option:selected").text();
        var keterangan = "add by purchasing";
        var markup = "<tr>"+
        				"<td><input type='checkbox' name='record'>"
        				+"<input type='hidden' id='detail_transfer["+counter+"][id]' name='detail_transfer["+counter+"][id]'></td> "+
        	
        				"<td style='display:none;'><input type='hidden' id='detail_transfer["+counter+"][id_obat]' name='detail_transfer["+counter+"][id_obat]' value='"+id_obat+"' data-id-obat='"+id_obat+"'>" + id_obat + "</td>"+
        				"<td style='display:none;'><input type='hidden' id='detail_transfer["+counter+"][id_defecta]' name='detail_transfer["+counter+"][id_defecta]' value=''></td>"+
        				"<td style='display:none;'><input type='hidden' id='detail_transfer["+counter+"][is_purchasing_add]' name='detail_transfer["+counter+"][is_purchasing_add]' value='1'></td>"+
        				"<td style='display:none;'><input type='hidden' id='detail_transfer["+counter+"][id_apotek]' name='detail_transfer["+counter+"][id_apotek]' value='"+id_apotek+"'></td>"+
        				"<td><input type='hidden' id='detail_transfer["+counter+"][nama_obat]' name='detail_transfer["+counter+"][nama_obat]' value='"+nama_obat+"'>" + nama_obat + "</td>"+
        				"<td><input type='hidden' id='detail_transfer["+counter+"][jumlah_diajukan]' name='detail_transfer["+counter+"][jumlah_diajukan]' value='"+jumlah+"'>" + jumlah + "</td>"+
        				"<td><input type='hidden' id='detail_transfer["+counter+"][jumlah]' name='detail_transfer["+counter+"][jumlah]' value='"+jumlah+"' class='jumlah' data-id-obat='"+id_obat+"'><span class='jumlah_label'>" + jumlah + "</span></td>"+

        				"<td><input type='hidden' id='detail_transfer["+counter+"][id_satuan]' name='detail_transfer["+counter+"][id_satuan]' value='"+id_satuan+"' class='jumlah' data-id-obat='"+id_obat+"'><span class='id_satuan_label'>" + satuan + "</span></td>"+

        				"<td><input type='hidden' id='detail_transfer["+counter+"][keterangan]' name='detail_transfer["+counter+"][keterangan]' value='"+keterangan+"' class='keterangan' data-id-obat='"+id_obat+"'>" + keterangan + "</td>"+
        				"<td id='detail_transfer["+counter+"][action]'><span class='label label-primary btn-sm' onClick='deleteRow(this)' data-toggle='tooltip' data-placement='top' title='Hapus Data'><i class='fa fa-edit'></i> Hapus</span></td>"+
        			"</tr>";

        var jumlah_label = $(".jumlah_label");
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

		if(status_append == true){
        	$("#tb_nota_transfer tbody").append(markup);
        	current_counter = parseInt($("#counter").val());
	        if(isNaN(current_counter)){
	            current_counter = 0;
	        }
	          
	        $("#counter").val(current_counter+1);
		}
    	// hapus seluruh data ditempat input
    	kosongkan_form();
	}


	function edit_detail(no, id){
	    $.ajax({
	        type: "POST",
	        url: '{{url("transfer/edit_detail")}}',
	        async:true,
	        data: {
	        	_token:"{{csrf_token()}}",
	        	no : no,
	        	id : id,
	        },
	        beforeSend: function(data){
	          // on_load();
	        $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
	        $("#modal-xl .modal-title").html("Edit Data Transfer");
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

	function edit_transfer(no, id){
	    $.ajax({
	        type: "POST",
	        url: '{{url("transfer/edit_transfer")}}',
	        async:true,
	        data: {
	        	_token:"{{csrf_token()}}",
	        	no : no,
	        	id : id,
	        },
	        beforeSend: function(data){
	          // on_load();
	        $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
	        $("#modal-xl .modal-title").html("Edit Data Transfer");
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
			$("#form-edit-detail").find("input[name], select").each(function (index, node) {
		        data[node.name] = node.value;
    			
		    });

			$.ajax({
				type:"PUT",
				url : '{{url("transfer/update_defecta/")}}/'+id,
				dataType : "json",
				data : data,
				beforeSend: function(data){
					// replace dengan fungsi loading
				},
				success:  function(data){
					if(data.submit ==1){
						$('#modal-xl').modal('toggle');
					}else{
						return false;
					}
				},
				complete: function(data){
				},
				error: function(data) {
					show_error("error ajax occured!");
				}

			})
		} else {
			return false;
		}
	}

	function submit_detail(id){
		if($(".validated_form").valid()) {
			data = {};
			$("#form-edit-detail-transfer").find("input[name], select").each(function (index, node) {
		        data[node.name] = node.value;
    			
		    });

			$.ajax({
				type:"PUT",
				url : '{{url("transfer/update_transfer_detail/")}}/'+id,
				dataType : "json",
				data : data,
				beforeSend: function(data){
					// replace dengan fungsi loading
				},
				success:  function(data){
					if(data.submit ==1){
						$('#modal-xl').modal('toggle');
					}else{
						return false;
					}
				},
				complete: function(data){
				},
				error: function(data) {
					show_error("error ajax occured!");
				}

			})
		} else {
			return false;
		}
	}

    function deleteRow(r) {
        var i = r.parentNode.parentNode.rowIndex;
        document.getElementById("tb_nota_transfer").deleteRow(i);
    }

    function save_data(){
		if($(".validated_form").valid()) {
			data = {};
			$("#form_transfer").find("input[name], select").each(function (index, node) {
		        data[node.name] = node.value;
		    });

			document.form_transfer.submit();
		} else {
			return false;
		}
	}

	function save_edit(){
		if($(".validated_form").valid()) {
			data = {};
			$("#form_transfer").find("input[name], select").each(function (index, node) {
		        data[node.name] = node.value;
		    });

			document.form_transfer.submit();
		} else {
			return false;
		}
	}
</script>