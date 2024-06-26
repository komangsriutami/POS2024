
<script src="../assets/plugins/inputmask/min/jquery.inputmask.bundle.min.js"></script>
<script type="text/javascript">

    $(document).ready(function(){
        $('#calendars').fullCalendar({
            weekends: true,
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
            },
            editable: false,
            eventLimit: true,
            events: {
                url: '{{url("/book_dokter/load_list_jadwal_dokter/".$dokters->id)}}',
                error: function() {
                    alert("cannot load json");
                }
            },
            eventRender: function (event, element) {
                element.attr('href', 'javascript:void(0);');
            },
            eventClick: function(calEvent, jsEvent, view) {
                show_event(calEvent.id);
            }
        });

        function show_event(id){
            $.ajax({
                type: "GET",
                url: '{{url("book_dokter/load_data_jadwal_dokter")}}'+'/'+id,
                async:true,
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
    });    
</script>