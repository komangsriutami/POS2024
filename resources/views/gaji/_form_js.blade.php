<script type="text/javascript">
	var token = "";

	$(document).ready(function(){
		token = $('input[name="_token"]').val();

		$('#tgl_berlaku_start, #tgl_berlaku_end').datepicker({
            autoclose:true,
            format:"yyyy-mm-dd",
            forceParse: false
        });

		$('.input_select').select2();
	})

	function goBack() {
	    window.history.back();
	}
</script>