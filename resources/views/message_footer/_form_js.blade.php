<!--
Model : Javascript Backend Message Footer pada Frontend
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
    })

    function goBack() {
        window.history.back();
    }

    // $(".bpjsform").css('display', 'none');
    // $("#adabpjs").change(function() {
    //     if ($(this).val() == "1") {
    //         $('.adabpjsform').fadeIn();
    //     } else if ($(this).val() == "2") {
    //         $('.adabpjsform').fadeOut();
    //     } else {
    //         $('.adabpjsform').fadeOut();
    //     }
    // });
</script>
