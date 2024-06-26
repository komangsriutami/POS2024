<!--
Model : Javascript Diagnosa
Author : Tangkas.
Date : 22/06/2021
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
    })

    function goBack() {
        window.history.back();
    }
</script>
