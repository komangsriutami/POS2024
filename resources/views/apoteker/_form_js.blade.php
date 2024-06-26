<script type="text/javascript">
	var token = "";

	$(document).ready(function(){
		token = $('input[name="_token"]').val();

		$('#tgl_lahir').datepicker({
		    autoclose:true,
			format:"yyyy-mm-dd",
		    forceParse: false
		});
	})

	function goBack() {
	    window.history.back();
	}
</script>