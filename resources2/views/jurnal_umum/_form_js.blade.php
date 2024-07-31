{!! Html::script('js/number_format.js') !!}
<script type="text/javascript">
	var token = "";

	$(document).ready(function(){
		token = $('input[name="_token"]').val();
		$('#tgl_transaksi').datepicker({
		    autoclose:true,
			format:"yyyy-mm-dd",
		    forceParse: false
		});

		$('.input_select').select2();	

		$(".btnAddDetail").click(function(e){
			count = $("#count").val();
			$.ajax({
				type:"GET",
				url : '{{url("jurnalumum/adddetail")}}/',
				dataType : "json",
				data : {count:count},
				beforeSend: function(data){
					// replace dengan fungsi loading
				},
				success:  function(data){
					if(data.status ==1){
						$("#div_detail").append(data.form_detail);
						$("#id_kode_akun-"+count).select2();

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
		<?php if($jurnal_umum->id == "") { ?>
			$(".btnAddDetail").trigger("click");
		<?php } ?>



		$(".btnAddFile").click(function(e){
			count = $("#countfile").val();
			$.ajax({
				type:"GET",
				url : '{{url("jurnalumum/addfile")}}/',
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


		$("#form_jurnal").submit(function(e){
			e.preventDefault();

			if($(this).valid() == true){

	        	const formData = new FormData();

	        	$(".buktifile").each(function(i, obj) {
	        		formData.append('buktifile['+$(obj).data("idx")+']', $(this).prop('files')[0]);
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
							if(data.url != null){ 
								if(data.url != 'returnfalse') {
									location.href=data.url; 
								} else {
									goBack(); 
								}
							} else { 
								goBack(); 
							}
							show_info("Berhasil menyimpan data");
						} else {
							show_error("Terjadi kesalahan. Gagal menyimpan data");
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

		hitdebit();
		hitkredit();

	});

	function goBack() {
	    window.history.back();
	}

	function delDetail(i)
	{
		if($(".rowdetail").length < 2){
			show_error("Tidak dapat menghapus baris detail. Minimal menginputkan satu detail jurnal umum");
		} else {
			$("#row-"+i).remove();
		}
		
	}

	function delBukti(i)
	{
		$("#rowbukti-"+i).remove();
		
	}

	function hitdebit()
	{
		var tdebit = 0;
		$(".debit").each(function(i, obj) {
			if(this.value == ""){debit = 0;} else {debit = this.value;}
			tdebit = parseFloat(tdebit) + parseFloat(debit);
		});
		$("#tdebit").html(number_format(tdebit, 0, ',', '.'));
	}

	function hitkredit()
	{
		var tkredit = 0;
		$(".kredit").each(function(i, obj) {
			if(this.value == ""){kredit = 0;} else {kredit = this.value;}
			tkredit = parseFloat(tkredit) + parseFloat(kredit);
		});
		$("#tkredit").html(number_format(tkredit, 0, ',', '.'));
	}


	function openfile(id)
	{
		$.getJSON('{{url("jurnalumum/viewFile/")}}/'+id,{id:id}, function (data) {
			console.log(data.mime);

			var datafile = 'data:'+data.mime+';base64';
		    
		    var file = new Blob([data.file], {type:data.mime});
		    var fileURL = URL.createObjectURL(file);
		    /*window.open(encodeURI(fileURL),"File Bukti");*/

		    if(data.mime == "application/pdf"){
		    	content = "<iframe width='100%' height='100%' src='"+datafile+","+data.file+ "'></iframe>";
		    } else {
		    	content = "<img width='100%' height='100%' src='"+datafile+","+data.file+ "'></img>";
		    }
		    /*console.log(datafile);*/

			let showwindow = window.open("","aa","width=500,height=700");
		    showwindow.document.write(content);
		});
	}
</script>