<script type="text/javascript">
	function formatRepo (repo) {
        if (repo.loading) return repo.text;

        markup = '<option val="'+repo.id+'">'+repo.nama+'</option>';
        return markup;
    }

    function formatRepoSelection (repo) {
        return repo.nama;
    }

    $('.form-control#id').select2({
        ajax: {
            url: "{{url('admin/list_calon_user')}}",
            dataType: 'json',
            method: 'GET',
            delay: 250,
            data: function (params) {
              return {
                q: params.term 
              };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;

                return {
                        results: data.items,
                        pagination: {
                          more: (params.page * 30) < data.total_count
                        }
                    };
            },
            cache: true
        },
        escapeMarkup: function (markup) { return markup; }, 
        minimumInputLength: 0,
        templateResult: formatRepo, 
        templateSelection: formatRepoSelection 
    }).on("change", function (e) {
    });

    $(document).ready(function(){
        token = $('input[name="_token"]').val();

        $('#tgl_lahir').datepicker({
            autoclose:true,
            format:"yyyy-mm-dd",
            forceParse: false
        });

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