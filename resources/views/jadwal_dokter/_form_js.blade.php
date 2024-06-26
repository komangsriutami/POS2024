<!-- InputMask -->
<script src="../assets/plugins/inputmask/min/jquery.inputmask.bundle.min.js"></script>
<script type="text/javascript">
    var token = "";
    $(document).ready(function(){
        token = $('input[name="_token"]').val();

       /* $('#tgl').datepicker({
            autoclose:true,
            format:"yyyy-mm-dd",
            forceParse: false,
        });*/

         $('#tgl').daterangepicker({
            autoclose:true,
            //format:"yyyy-mm-dd",
            forceParse: false
        });

        //Datemask dd/mm/yyyy
        $('#datemask').inputmask('dd/mm/yyyy', { 'placeholder': 'dd/mm/yyyy' })
        //Datemask2 mm/dd/yyyy
        $('#datemask2').inputmask('mm/dd/yyyy', { 'placeholder': 'mm/dd/yyyy' })
        //Money Euro
        $('[data-mask]').inputmask();

        $('#calendar').fullCalendar({
            weekends: true,
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
            },
            editable: false,
            eventLimit: true,
            events: {
                url: '{{url("jadwal_dokter/load_list_jadwal_dokter")}}',
                data:{
                    id_dokter:$("#id_dokter_pilih").val()
                },
                error: function() {
                    alert("cannot load json");
                }
            },
            eventRender: function (event, element) {
                element.attr('href', 'javascript:void(0);');
            },
            eventClick: function(calEvent, jsEvent, view) {
              show_event(calEvent.id);
                //alert('Event: ' + calEvent.peminjam);
              /*    $.ajax({
                    type: "POST",
                    url: '{{url("jadwal_kerja/load_data_jadwal_kerja")}}',
                    async:true,
                    data: {
                    _token:"{{csrf_token()}}",
                    id : calEvent.id,
                    },
                    beforeSend: function(data){
                        // on_load();
                        $('#modal-large').find('.modal-lg').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
                        $("#modal-large .modal-title").html("Edit Jadwal");
                        $('#modal-large').modal("show");
                        $('#modal-large').find('.modal-body-content').html('');
                        $("#modal-large").find(".overlay").fadeIn("200");
                    },
                    success:  function(data){
                        $('#modal-large').find('.modal-body-content').html(data);
                    },
                    complete: function(data){
                        $("#modal-large").find(".overlay").fadeOut("200");
                    },
                    error: function(data) {
                        alert("error ajax occured!");
                    }
                });*/
            }
        });

        $('#id_dokter_pilih').change(function(){
            $('#calendar').data('fullCalendar').options.events.data.id_dokter = $(this).val();
            $('#calendar').fullCalendar('refetchEvents');
        });


        function show_event(id){
            // console.log(id);
            // console.log(<?php echo session('user_roles.0.id') ?>);
            // console.log(<?php echo Auth::guard('dokter')->user()->id ?>)
            // if (<?php echo session('user_roles.0.id') ?> ==7) {
            //     if (<?php echo Auth::guard('dokter')->user()->id ?> !=id) {
            //         return;
            //     }
            // }
            $.ajax({
                type: "GET",
                url: '{{url("jadwal_dokter/load_data_jadwal_dokter")}}',
                async:true,
                data: {
                    _token:"{{csrf_token()}}",
                    id:id,
                },
                beforeSend: function(data){
                    // console.log(data['jadwal_dokter);
                  // on_load();
                  $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
                  $("#modal-xl .modal-title").html("Edit Kalender");
                  $('#modal-xl').modal("show");
                  $('#modal-xl').find('.modal-body-content').html('');
                  $("#modal-xl").find(".overlay").fadeIn("200");
                },
                success:  function(data){
                  $('#modal-xl').find('.modal-body-content').html(data);
                },
                complete: function(data){
                    $("#modal-xl").find(".overlay").fadeOut("200");
                },
                error: function(data) {
                    alert("error ajax occured!");
                }
            });
        }
    })

    function submit_valid(id){
        //console.log("tes");
        status = $("#form-edit").valid();
        //console.log(status);
        if(status) {
            data = {};
            $("#form-edit").find("input[name], select").each(function (index, node) {
                data[node.name] = node.value;

            });

            $.ajax({
                type:"PUT",
                url : '{{url("jadwal_dokter/")}}/'+id,
                dataType : "json",
                data : data,
                beforeSend: function(data){
                    // replace dengan fungsi loading
                },
                success:  function(data){
                    if(data.status ==1){
                        show_info("Data jadwal dokter berhasil diperbaharui !");
                        $('#modal-xl').modal('toggle');
                    }else{
                        show_error("Gagal menyimpan data ini !");
                        return false;
                    }
                },
                complete: function(data){
                    // replace dengan fungsi mematikan loading
                    //tb_fasilitas.fnDraw(false);
                    location.reload();
                },
                error: function(data) {
                    alert("error ajax occured!");
                    // done_load();
                }

            })
        }
    }

    function submit_valid_delete(id){
        //console.log("tes");
        status = $("#form-edit").valid();
        //console.log(status);
        if(status) {
            data = {};
            $("#form-edit").find("input[name], select").each(function (index, node) {
                data[node.name] = node.value;

            });

            $.ajax({
                type:"DELETE",
                url : '{{url("jadwal_dokter/")}}/'+id,
                async:true,
                data: {
                    _token:"{{csrf_token()}}",
                    id:id,
                },
                beforeSend: function(data){
                    // replace dengan fungsi loading
                },
                success:  function(data){
                    if(data ==1){
                        show_info("Data jadwal dokter berhasil dihapus !");
                        $('#modal-xl').modal('toggle');
                    }else{
                        show_error("Gagal hapus data ini !");
                        return false;
                    }
                },
                complete: function(data){
                    // replace dengan fungsi mematikan loading
                    //tb_fasilitas.fnDraw(false);
                    location.reload();
                },
                error: function(data) {
                    alert("error ajax occured!");
                    // done_load();
                }

            })
        }
    }

</script>
