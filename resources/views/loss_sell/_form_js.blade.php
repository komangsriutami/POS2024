<script type="text/javascript">
	var token = "";

	$(document).ready(function(){
		token = $('input[name="_token"]').val();
		$('#tanggalberdiri').datepicker({
		    autoclose:true,
			format:"yyyy-mm-dd",
		    forceParse: false,
		});

		$('.input_select').select2({
			minimumInputLength: 3 
		});

		$('#id_obat').on("select2:select", function(e) { 
		    var id_obat = $("#id_obat").val();
		    if(id_obat == 0) {
		    	$("#nama_obat_view").show();
		    	$("#harga_obat_view").show();
		    } else {
		    	$("#nama_obat_view").hide();
		    	$("#harga_obat_view").hide();
		    }
		});
	})

	function goBack() {
	    window.history.back();
	}
</script>