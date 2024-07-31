<script type="text/javascript">
	var token = "";

	$(document).ready(function(){
		token = $('input[name="_token"]').val();
		$('.input_select').select2();

		$('#is_pemotongan').click(function(){
		    if($(this).is(':checked')){
		    	alert('is checked');

		    	// non label 
		    	$('#id_akun_pajak_satu').attr('name', 'id_akun_pajak_pembelian');
		    	$('#id_akun_pajak_dua').attr('name', 'id_akun_pajak_penjualan');

				// label
				$("#id_akun_pajak_satu_lbl").html('Akun Pajak Pembelian (*)');
				$("#id_akun_pajak_dua_lbl").html('Akun Pajak Penjualan (*)');

		    } else {
		        alert('not is checked');

		        // non label 
		        $('#id_akun_pajak_satu').attr('name', 'id_akun_pajak_penjualan');
		    	$('#id_akun_pajak_dua').attr('name', 'id_akun_pajak_pembelian');

		        // label
				$("#id_akun_pajak_satu_lbl").html('Akun Pajak Penjualan (*)');
				$("#id_akun_pajak_dua_lbl").html('Akun Pajak Pembelian (*)');
		    }
		});
	})

	function goBack() {
	    window.history.back();
	}
</script>