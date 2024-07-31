<script type="text/javascript">
	var token = "";

	$(document).ready(function(){
		token = $('input[name="_token"]').val();
		$('#tanggal_aktif').daterangepicker({
		    autoclose:true,
			//format:"yyyy-mm-dd",
		    forceParse: false
		});

		$('.input_select').select2({
			minimumInputLength: 3 
		});
	})

	function goBack() {
	    window.history.back();
	}
</script>