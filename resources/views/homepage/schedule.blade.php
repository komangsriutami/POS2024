@extends('homepage.layouts.app')

@section('content')
<!-- SERVICE PAGE BANNER -->

<div class="page-banner">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="page-banner-content">
                    <div class="d-sm-flex justify-content-between align-items-center">
                        <div class="title">
                            {{-- <h6 class="text-left text-capitalized">Jadwal</h6> --}}
                            <h2>Jadwal</h2>
                        </div>
                        {{-- <div class="link text-sm-right text-left">
                            <a href="home.html">Home <i class="ti-angle-double-right"></i></a>
                            Jadwal
                        </div> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SERVICE PAGE BANNER  END-->

<!-- DEPARTMENT SECTION START -->

<section class="department-section">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-title text-left">
                    {{-- <h2>Jadwal</h2> --}}
                    <p>
                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Sint omnis corporis magni excepturi error eum quo, ea quam maxime similique a consectetur culpa inventore necessitatibus consequuntur atque exercitationem, corrupti quaerat.
                    </p>
                    <div class="section-border">
                        <div class="icon">
                            <i class="fas fa-tint"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-3">

            <div class="col-md-12">
                <div id='calendar'></div>
            </div>
            
        </div>
    </div>
</section>

<!-- DEPARTMENT SECTION END -->
@endsection

@push('script')
    {{-- <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" /> --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js" integrity="sha256-4iQZ6BVL4qNKlQ27TExEhBN1HFPvAvAMbFavKKosSWQ=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.js"></script>
@endpush

@push('script')
<script>
    $(document).ready(function() {
        var SITEURL = "{{url('/')}}";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var calendar = $('#calendar').fullCalendar({
            editable: false,
            events: SITEURL + "/schedule",
            displayEventTime: true,
            eventRender: function(event, element, view) {
                if (event.allDay === 'true') {
                    event.allDay = true;
                } else {
                    event.allDay = false;
                }
            },
            selectable: true,
            selectOverlap: false,
            selectMinDistance: false,
            selectHelper: true,
            validRange: function(nowDate) {
                return {
                    start: nowDate,
                    end: nowDate.clone().add(3, 'months')
                };
            },
            select: function(start, end, allDay) {
                if(start.isBefore(moment())) {
                    $('#calendar').fullCalendar('unselect');
                    return false;
                }

                // var title = prompt('Event Title:');
                var start = $.fullCalendar.formatDate(start, "Y-MM-DD");
                if (confirm('Registrasi untuk tanggal '+start+'?')) {
                    window.location.href = "{{route('schedule.select')}}"+"?book="+start;
                }

                // if (title) {
                //     var start = $.fullCalendar.formatDate(start, "Y-MM-DD HH:mm:ss");
                //     var end = $.fullCalendar.formatDate(end, "Y-MM-DD HH:mm:ss");
                //     // $.ajax({
                //     //     url: SITEURL + "/schedule/select",
                //     //     data: 'title=' + title + '&start=' + start + '&end=' + end,
                //     //     type: "POST",
                //     //     success: function(data) {
                //     //         displayMessage("Added Successfully");
                //     //     }
                //     // });
                //     calendar.fullCalendar('renderEvent', {
                //             title: title,
                //             start: start,
                //             end: end,
                //             allDay: allDay
                //         },
                //         true
                //     );
                // }
                // calendar.fullCalendar('unselect');
            },
            eventDrop: function(event, delta) {
                alert('eventDrop');
                // var start = $.fullCalendar.formatDate(event.start, "Y-MM-DD HH:mm:ss");
                // var end = $.fullCalendar.formatDate(event.end, "Y-MM-DD HH:mm:ss");
                // $.ajax({
                //     url: SITEURL + '/fullcalendareventmaster/update',
                //     data: 'title=' + event.title + '&start=' + start + '&end=' + end + '&id=' + event.id,
                //     type: "POST",
                //     success: function(response) {
                //         displayMessage("Updated Successfully");
                //     }
                // });
            },
            eventClick: function(event) {
                alert('eventClick');
                // var deleteMsg = confirm("Do you really want to delete?");
                // if (deleteMsg) {
                //     $.ajax({
                //         type: "POST",
                //         url: SITEURL + '/fullcalendareventmaster/delete',
                //         data: "&id=" + event.id,
                //         success: function(response) {
                //             if (parseInt(response) > 0) {
                //                 $('#calendar').fullCalendar('removeEvents', event.id);
                //                 displayMessage("Deleted Successfully");
                //             }
                //         }
                //     });
                // }
            }
        });
    });

function displayMessage(message) {
    $(".response").html(" "+message+" ");
            setInterval(function() {
                $(".success").fadeOut();
            }, 1000);
        }
</script>
@endpush