<!--
Model : Javascript Pasien
Author : Tangkas.
Date : 12/06/2021
-->

<script type="text/javascript">
    var token = "";

    $(document).ready(function() {
        token = $('input[name="_token"]').val();
        $('#tgl_lahir').datepicker({
            autoclose: true,
            format: "yyyy-mm-dd",
            forceParse: false,
            todayHighlight: true,
            endDate: '0d',
        });

        $('.input_select').select2();
        $(".bpjsform").css('display', 'none');

        if($('#is_ganti_password').attr('checked')) {
            $('#is_ganti_password_val').val(1);
            document.getElementById("password").disabled = false;
        } else {
            var x = $("#id").val();
            if(x == "") {
                $('#is_ganti_password_val').val(1);
                document.getElementById("password").disabled = false;
            } else {
                $('#is_ganti_password_val').val(0);
                document.getElementById("password").disabled = true;
            }
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

        var x = $("#id").val();
        if(x != "") {
            var is_bpjs = $("#is_bpjs").val();
            if(is_bpjs == 1) {
                $('.adabpjsform').show();
            } else {
                $('.adabpjsform').hide();
            }
        } 

        $("#is_bpjs").change(function() {
            if ($(this).val() == 1) {
                $('.adabpjsform').show();
            } else if ($(this).val() == 2) {
                $('.adabpjsform').hide();
            } else {
                $('.adabpjsform').hide();
            }
        });
    })

    function goBack() {
        window.history.back();
    }
</script>
