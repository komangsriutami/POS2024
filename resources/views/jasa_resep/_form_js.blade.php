<script type="text/javascript">
	var token = "";

	$(document).ready(function(){
		token = $('input[name="_token"]').val();

		$('.input_select').select2();
	})

	function goBack() {
	    window.history.back();
	}
</script>