<script type="text/javascript">
	var token = "";

	$(document).ready(function(){
		token = $('input[name="_token"]').val();
		$('#tanggalberdiri').datepicker({
		    autoclose:true,
			format:"yyyy-mm-dd",
		    forceParse: false
		});

		$('.input_select').select2({});

		$("#btn_masuk").hide();
		$("#btn_keluar").hide();
		$("#btn_masuk_split").hide();
		$("#btn_keluar_split").hide(); 

		$("#datatable_filter").click(function() { 
			var strcari = $("#text_cari").val();
			if (strcari != "") { 
				$("#hasil").html("");
				$.ajax({ 
					type:"POST", 
					url:'{{url("absensi/cari_user")}}', 
					data:{
				            _token		: "{{csrf_token()}}",
				            txtcari : strcari,
				        }, 
					success: function(data){ 
						var json_obj = $.parseJSON(data);
						$("#id_user").val(json_obj.user.id);
						$("#nama_user").html(json_obj.user.nama); 

						if(json_obj.jum == 1) {
							$("#status").html("Data Absensi Sudah Masuk"); 
							$("#id_status").val(2);
							$("#btn_masuk").hide();
							$("#btn_keluar").show();
							$("#btn_masuk_split").hide();
							$("#btn_keluar_split").hide(); 
						} else if(json_obj.jum == 0) {
							$("#status").html(" Data Absensi Belum Masuk"); 
							$("#id_status").val(1);
							$("#btn_masuk").show();
							$("#btn_keluar").hide(); 
							$("#btn_masuk_split").hide();
							$("#btn_keluar_split").hide();
						} else if (json_obj.jum == 2) {
							$("#status").html("Data Absensi Sudah Masuk"); 
							$("#id_status").val(3);
							$("#btn_masuk").hide(); 
							$("#btn_keluar").hide();
							$("#btn_masuk_split").show();
							$("#btn_keluar_split").hide();
						} else if (json_obj.jum == 3) {
							$("#status").html("Data Absensi Sudah Masuk"); 
							$("#id_status").val(4);
							$("#btn_masuk").hide(); 
							$("#btn_keluar").hide(); 
							$("#btn_masuk_split").hide();
							$("#btn_keluar_split").show();
						}
					} 
				}); 
			} 
		});
	})

	function goBack() {
	    window.history.back();
	}
</script>