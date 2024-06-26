<script src="http://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.4/summernote.js"></script>
<script>
    $(document).ready(function() {

        $('.content-tips').summernote({

            height:300,

        });

    });
</script>

<script type="text/javascript">
    var token = "";

    $(document).ready(function(){
        token = $('input[name="_token"]').val();
    })

    function goBack() {
        window.history.back();
    }
</script>