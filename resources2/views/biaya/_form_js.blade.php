{!! Html::script('js/number_format.js') !!}
<script type="text/javascript">
	var token = "";
	var subtotal = 0;
	var besarpajak = 0;

	$(document).ready(function(){
		token = $('input[name="_token"]').val();
		$('#tgl_batas_pembayaran').datepicker({
		    autoclose:true,
			format:"yyyy-mm-dd",
		    forceParse: false
		});

		$('#tgl_transaksi').datepicker({
		    autoclose:true,
			format:"yyyy-mm-dd",
		    forceParse: false
		});



		$('.input_select').select2();
		$('#id_akun_ppn_potong').select2({
			allowClear: true,
    		placeholder: "- tanpa pajak -"
		}).on('change', function() {
			besarpajak = 0;
			
		    if(this.value == ""){
		    	$("#jumlahpajak").html(0);
		    	$("#potongan_pajak").val(0);
		    	$("#potongan_pajak").prop("disabled",true);
		    } else {
		    	$("#potongan_pajak").prop("disabled",false);
		    }

		    hitTotal();
		}).trigger('change');


		$("#id_syarat_pembayaran").select2().on('change', function(){

			var net = parseInt($(this).find("option:selected").data('waktu'));
			if(isNaN(net)){
				date2 = new Date();
			} else {
				var date2 = $('#tgl_batas_pembayaran').datepicker('getDate', '+1d'); 
				date2.setDate(date2.getDate()+net); 
			}
			
			$('#tgl_batas_pembayaran').datepicker('setDate', date2);

		})



		$("#is_bayar_nanti").change(function(e){
			if(this.checked){
				$("#id_akun_bayar").attr("disabled",true);
				$(".bayar_nanti").fadeIn('slow');
			} else {
				$("#id_akun_bayar").attr("disabled",false);
				$(".bayar_nanti").fadeOut('slow');
			}
		});
		$("#is_bayar_nanti").trigger("change");


		$(".btnAddDetail").click(function(e){
			count = $("#count").val();
			$.ajax({
				type:"GET",
				url : '{{url("biaya/adddetail")}}',
				dataType : "json",
				data : {count:count},
				beforeSend: function(data){
					// replace dengan fungsi loading
				},
				success:  function(data){
					if(data.status ==1){
						$("#div_detail").append(data.form_detail);
						$("#id_kode_akun-"+count).select2();
						$("#id_akun_pajak-"+count).select2();

						count = parseInt(count)+1;
						$("#count").val(count);	
					}
					/*else {
						show_error("Terjadi kesalahan. Gagal memuat form detail jurnal umum");
					}*/
				},
				complete: function(data){
					
				},
				error: function(data) {
					show_error("Terjadi kesalahan. Gagal memuat form detail jurnal umum");
				}

			});
		});
		<?php if($biaya->id == "") { ?>
			$(".btnAddDetail").trigger("click");
		<?php } ?>


		$(".btnAddSyarat").click(function(e){
			$.ajax({
				type:"GET",
				url : '{{url("syaratpembayaran/create")}}',
				dataType : "json",
				data : {Fromjson:1},
				beforeSend: function(data){
					// replace dengan fungsi loading
					$('#modal-xl').find('.modal-body-content').html('');
				},
				success:  function(data){
					if(data.status ==1){
						// on_load();
				        $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
				        $("#modal-xl .modal-title").html("Tambah Syarat Pembayaran");
				        $('#modal-xl').find('.modal-body-content').html(data.form);
				        $('#modal-xl').modal("show");
				        $("#modal-xl").find(".overlay").fadeIn("200");
					}
				},
				complete: function(data){
					
				},
				error: function(data) {
					show_error("Terjadi kesalahan. Gagal memuat form syarat pembayaran");
				}

			});
		});










		$("#potongan_pajak").change(function(e){
			if($("#persen").is(":checked")){
				hitPajakpersen();
			} else {
				hitPajaknominal();
			}
		});

		$('input[type="radio"]').click(function(){
			$("#potongan_pajak").val(0);

			if($("#persen").is(":checked")){
				hitPajakpersen();
			} else {
				hitPajaknominal();
			}
		});


		$(".btnAddFile").click(function(e){
			count = $("#countfile").val();
			$.ajax({
				type:"GET",
				url : '{{url("biaya/addfile")}}',
				dataType : "json",
				data : {count:count},
				beforeSend: function(data){
					// replace dengan fungsi loading
				},
				success:  function(data){
					if(data.status ==1){
						$("#div_bukti").append(data.form_detail);
						count = parseInt(count)+1;
						$("#countfile").val(count);	
					}
					/*else {
						show_error("Terjadi kesalahan. Gagal memuat form detail jurnal umum");
					}*/
				},
				complete: function(data){
					
				},
				error: function(data) {
					show_error("Terjadi kesalahan. Gagal memuat form detail jurnal umum");
				}

			});
		});





		$(document).on("keyup", function(e){
		  	var x = e.keyCode || e.which;
		    if(x == 18) {
		    	$(".btnAddDetail").trigger("click");
		    }
		})


		$("#form_biaya").submit(function(e){
			e.preventDefault();

			if($(this).valid() == true){

	        	const formData = new FormData();

	        	$(".buktifile").each(function(i, obj) {
	        		index = $(this).data("idx");
	        		formData.append('buktifile['+index+']', $(this).prop('files')[0]);
	        	});

	        	$.each($(this).serializeArray(),function(key,input){
				    formData.append(input.name,input.value);
				});

				$.ajax({
					type:"POST",
					url : this.action,
					dataType : "json",
					data : formData,
					processData: false,
					contentType: false,
					beforeSend: function(data){
						// replace dengan fungsi loading
					},
					success:  function(data){
						if(data.status ==1){
							show_info("Berhasil menyimpan data");
							if(data.url != null){ location.href=data.url; } else { goBack(); }
						} else {
							show_error("Terjadi kesalahan. Gagal menyimpan data. "+data.errorMessages);
						}
					},
					complete: function(data){
						
					},
					error: function(data) {
						show_error("ajax post error");
					}

				});
			}
			
		});

		hitbiaya();

		$('#id_penerima').on('select2:select', function (e) {
		    let tipe_penerima = $(e.params.data.element).data('tipe_penerima');	
		    $('#tipe_penerima').val(tipe_penerima);
		});
	});

	function goBack() {
	    window.history.back();
	}

	function delDetail(i)
	{
		if($(".rowdetail").length < 2){
			show_error("Tidak dapat menghapus baris detail. Minimal menginputkan satu detail biaya");
		} else {
			$("#row-"+i).remove();
			hitbiaya();
		}
		
	}

	function delBukti(i)
	{
		$("#rowbukti-"+i).remove();
		
	}

	function hitbiaya()
	{
		var tbiaya = 0;
		var st = 0;
		var biaya = [];
		var akun_pajak = new Array;

		$(".biaya").each(function(i, obj) {
			var idx = $(this).data("idx");
			biaya.push(this.value);
			akun_pajak[i] = $("#id_akun_pajak-"+idx).val();
		});

		$.ajax({
				type:"GET",
				url : '{{url("biaya/DetailPajak")}}',
				dataType : "json",
				data : {
					biaya:biaya,
					akun_pajak:akun_pajak
				},
				beforeSend: function(data){
					// replace dengan fungsi loading
				},
				success:  function(data){
					if(data.status == 1){
						$("#div_pajak").html(data.view);
						$("#total").html(number_format(data.subtotal, 0, ',', '.'));
						subtotal = data.total;
						$("#potongan_pajak").trigger("change");
					}
				},
				complete: function(data){
						
				},
				error: function(data) {
					if(data.status == 2){
						show_error("Terjadi kesalahan. Gagal memuat detail pajak");
					}
				}
			});
	}


	function hitPajakpersen()
	{
		if($("#potongan_pajak").val() != ""){ persen = $("#potongan_pajak").val(); } else { persen = 0;}
		besarpajak = parseInt(subtotal)*(parseFloat(persen)/100);
		$("#jumlahpajak").html(number_format(besarpajak, 0, ',', '.')+')');
		hitTotal();
	}


	function hitPajaknominal()
	{
		if($("#potongan_pajak").val() != ""){ besarpajak = $("#potongan_pajak").val(); } else { besarpajak = 0;}
		$("#jumlahpajak").html(number_format(besarpajak, 0, ',', '.')+')');
		hitTotal();
	}


	function hitTotal()
	{
		totalakhir = parseInt(subtotal)-parseInt(besarpajak);
		$("#totalakhir").html(number_format(totalakhir, 0, ',', '.'));
	}


	function openfile(id)
	{
		$.get('{{url("biaya/viewFile/")}}/'+id,{id:id}, function (data) {
		    var file = new Blob([data], {type: 'application/pdf'});
		    var fileURL = URL.createObjectURL(file);
		    /*window.open(encodeURI(fileURL),"aa","width=500,height=700");*/

		    console.log(data)

			let pdfWindow = window.open("","aa","width=500,height=700")
		    pdfWindow.document.write("<iframe width='100%' height='100%' src='data:application/pdf;base64,"+encodeURI(data) + "'></iframe>");
		});
	}
</script>