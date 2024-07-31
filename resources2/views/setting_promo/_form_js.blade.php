<script type="text/javascript">
	var token = "";

	$(document).ready(function(){
		token = $('input[name="_token"]').val();
		$('#tgl_awal, #tgl_akhir').datepicker({
            autoclose:true,
            format:"yyyy-mm-dd",
            forceParse: false
        });

		$('.input_select').select2();

		$('#id_jenis_promo').on('select2:select', function (e) {
		  	var id_jenis_promo = $("#id_jenis_promo").val();
			if(id_jenis_promo == 1) {
				$("#show_diskon_persen").show();
				$("#show_diskon_rp").hide();
				$("#form_item_diskon").hide();
			} else if(id_jenis_promo == 2) {
				$("#show_diskon_persen").hide();
				$("#show_diskon_rp").show();
				$("#form_item_diskon").hide();
			} else if(id_jenis_promo == 3) {	
				$("#show_diskon_persen").hide();
				$("#show_diskon_rp").hide();
				$("#form_item_diskon").show();
			} else {
				$("#show_diskon_persen").hide();
				$("#show_diskon_rp").hide();
				$("#form_item_diskon").hide();
			}
		});
	})

	function goBack() {
	    window.history.back();
	}

	function add_row_item_beli(){
	    $.ajax({
	        type: "POST",
	         url: '{{url("setting_promo/add_row_item_beli")}}',
	        async:true,
	        data: {
	        	_token:$('input[name="_token"]').val(),
	          	count:$("#count").val()
	        },
	        // dataType: 'json',
	        beforeSend: function(data){
	          	// replace dengan fungsi loading
	          	$(".overlay").show();
	        },
	        success:  function(data){
				$("#detail_item_beli").append(data);
				//auto_number('numbering');
	          	current_counter = parseInt($("#count").val());
	          	if(isNaN(current_counter)){
					current_counter = 0;
	          	}
	          	current_counter = current_counter+1;
          		$("#count").val(current_counter);
          
          		$('html, body').animate({
			        scrollTop: $("#detail_item_beli_"+current_counter).offset().top
			    }, 1000);
	        },
	        complete: function(data){
	            // replace dengan fungsi mematikan loading
	            $(".overlay").hide();
	            //auto_number("nomor_file");
	        },
	        error: function(data) {
	            alert("error ajax occured!");
	            // done_load();
	        }
    	});
  	}

  	function delete_row_item_beli(no){
    	y = confirm("Apakah anda yakin untuk menghapus data ini?");
  		if(y){
  			$("#detail_item_beli_"+no).remove();
  		}
    }

    function add_row_item_diskon(){
	    $.ajax({
	        type: "POST",
	         url: '{{url("setting_promo/add_row_item_diskon")}}',
	        async:true,
	        data: {
	        	_token:$('input[name="_token"]').val(),
	          	counter:$("#counter").val()
	        },
	        // dataType: 'json',
	        beforeSend: function(data){
	          	// replace dengan fungsi loading
	          	$(".overlay").show();
	        },
	        success:  function(data){
				$("#detail_item_diskon").append(data);
				//auto_number('numbering');
	          	current_counter = parseInt($("#counter").val());
	          	if(isNaN(current_counter)){
					current_counter = 0;
	          	}
	          	current_counter = current_counter+1;
          		$("#counter").val(current_counter);
          
          		$('html, body').animate({
			        scrollTop: $("#detail_item_diskon_"+current_counter).offset().top
			    }, 1000);
	        },
	        complete: function(data){
	            // replace dengan fungsi mematikan loading
	            $(".overlay").hide();
	            //auto_number("nomor_file");
	        },
	        error: function(data) {
	            alert("error ajax occured!");
	            // done_load();
	        }
    	});
  	}

  	function delete_row_item_beli(no){
    	y = confirm("Apakah anda yakin untuk menghapus data ini?");
  		if(y){
  			$("#detail_item_diskon_"+no).remove();
  		}
    }

    function open_data_obat(barcode) {
		$.ajax({
            type: "POST",
            url: '{{url("setting_promo/open_data_obat")}}',
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
		        $("#jumlah").focus();
		        $('#modal-xl').modal('toggle');
            }
        });
	}
</script>