<!--
Model : Javascript Dokter
Author : Tangkas.
Date : 12/06/2021
-->

<script type="text/javascript">
    var token = "";

    $(document).ready(function() {
        token = $('input[name="_token"]').val();
        // $('#tgl_lahir').datepicker({
        //     autoclose: true,
        //     format: "yyyy-mm-dd",
        //     forceParse: false,
        //     todayHighlight: true,
        //     endDate: '0d',
        // });

        $('.input_select').select2();

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
    })

    function goBack() {
        window.history.back();
    }
</script>
