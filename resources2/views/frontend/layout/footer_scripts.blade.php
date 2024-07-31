{!! Html::script('assets_frontend/js/jquery.min.js') !!}

<!-- Bootstrap Core JS -->
{!! Html::script('assets_frontend/js/popper.min.js') !!}
{!! Html::script('assets_frontend/js/bootstrap.min.js') !!}
<!-- jquery-validation -->
{!! Html::script('assets/plugins/jquery-validation/jquery.validate.min.js') !!}
{!! Html::script('assets/plugins/jquery-validation/additional-methods.min.js') !!}
{!! Html::script('js/_validation.js') !!}
<!-- Swiper JS -->
{!! Html::script('assets_frontend/plugins/swiper/js/swiper.min.js') !!}

<!-- Datetimepicker JS -->
{!! Html::script('assets_frontend/js/moment.min.js') !!}
{!! Html::script('assets_frontend/js/bootstrap-datetimepicker.min.js') !!}
{!! Html::script('assets_frontend/plugins/daterangepicker/daterangepicker.js') !!}

<!-- Full Calendar JS -->
{!! Html::script('assets_frontend/plugins/jquery-ui/jquery-ui.min.js') !!}
{!! Html::script('assets_frontend/plugins/fullcalendar/fullcalendar.min.js') !!}
{!! Html::script('assets_frontend/plugins/fullcalendar/jquery.fullcalendar.js') !!}

<!-- Sticky Sidebar JS -->
{!! Html::script('assets_frontend/plugins/theia-sticky-sidebar/ResizeSensor.js') !!}
{!! Html::script('assets_frontend/plugins/theia-sticky-sidebar/theia-sticky-sidebar.js') !!}

<!-- Select2 JS -->
{!! Html::script('assets_frontend/plugins/select2/js/select2.min.js') !!}

<!-- Fancybox JS -->
{!! Html::script('assets_frontend/plugins/fancybox/jquery.fancybox.min.js') !!}

<!-- Dropzone JS -->
{!! Html::script('assets_frontend/plugins/dropzone/dropzone.min.js') !!}

<!-- Bootstrap Tagsinput JS -->
{!! Html::script('assets_frontend/plugins/bootstrap-tagsinput/js/bootstrap-tagsinput.js') !!}

<!-- Profile Settings JS -->
{!! Html::script('assets_frontend/js/profile-settings.js') !!}

<!-- Circle Progress JS -->
{!! Html::script('assets_frontend/js/circle-progress.min.js') !!}

<!-- Slick JS -->
{!! Html::script('assets_frontend/js/slick.js') !!}

<!-- Custom JS -->
{!! Html::script('assets_frontend/js/script.js') !!}

@if (Route::is(['map-grid', 'map-list']))
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD6adZVdzTvBpE2yBRK8cDfsss8QXChK0I"></script>
    {!! Html::script('assets_frontend/js/map.js') !!}
@endif

<script type="text/javascript">
    $(document).ready(function(){
        /*console.log(window.location.href);*/
      // Add smooth scrolling to all links
      $("header").children("a").on('click', function(event) {

        // Make sure this.hash has a value before overriding default behavior
        if (this.hash !== "") {
          // Prevent default anchor click behavior
          event.preventDefault();

          // Store hash
          var hash = this.hash;

          // Using jQuery's animate() method to add smooth page scroll
          // The optional number (800) specifies the number of milliseconds it takes to scroll to the specified area
          $('html, body').animate({
            scrollTop: $(hash).offset().top - $("#nav-header").height() - 30
          }, 500, function(){
            // Add hash (#) to URL when done scrolling (default click behavior)
            //window.location.hash = hash;
          });
        } // End if
      });
    });
     $(document).ready(function(){
        $("#datepicker").daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        autoUpdateInput: false,
      });
    });

     $("#datepicker").on('apply.daterangepicker', function(ev, picker) {
      $(this).val(picker.startDate.format('YYYY/MM/DD/'));
  });



    function Onepage(value){
            $(".main-nav li").each(function(){
                $(this).removeClass("active");
            });
            $("."+value).addClass("active");
        }
    $(document).ready(function(){
        
        $(".main-nav li").each(function(){
            $(this).removeClass("active");
        });

        /*if('<?php //echo $page;?>' == 'homepage'){
            var currentUrl = window.location.href;
            var splitUrl = currentUrl.split("#");
            console.log(currentUrl.indexOf("#"));
            if(currentUrl.indexOf("#")!==-1){
                $("."+splitUrl[1]).addClass("active");
            }else{
                console.log("homepage");
                $(".menu-homepage").addClass("active");
            };
        }*/
    });


</script>
