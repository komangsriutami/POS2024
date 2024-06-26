<script type="text/javascript">
	var token = "";

	$(document).ready(function(){
		token = $('input[name="_token"]').val();

		$('#tgl_lahir').datepicker({
		    autoclose:true,
			format:"yyyy-mm-dd",
		    forceParse: false
		});

		if($('#is_ganti_password').attr('checked')) {
			$('#is_ganti_password_val').val(1);
	        document.getElementById("password").disabled = false;
	    } else {
	    	$('#is_ganti_password_val').val(0);
	        document.getElementById("password").disabled = true;

	    }


	    $('#is_ganti_password').click(function() {
	        if (this.checked) {
	        	$('#is_ganti_password_val').val(1);
	          	document.getElementById("password").disabled = false;
	        } else {
	        	$('#is_ganti_password_val').val(0);
	          	document.getElementById("password").disabled = true;
	        }
	    });

	    $('.input_select').select2();
	})

	function goBack() {
	    window.history.back();
	}
</script>