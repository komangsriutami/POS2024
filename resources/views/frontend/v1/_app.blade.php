<!DOCTYPE html>
<html lang="en">

<head>
    @include('frontend.v1._head')
</head>
    @include('frontend.v1._header')
    @yield('content')
    @include('frontend.v1._footer')
    @include('frontend.v1._footer_scripts')
    @yield('script')
</body>

</html>
<script>
    $(document).ready(function() {
        // alert(1);
        /*$('.submenu li a').click(function(){
          $(.submenu li a).removeClass("active");
          $(this).addClass("active");
          $('.has-submenu a').removeClass("active");
          $('.has-submenu a').addClass("active");

          //$(this).toggleClass("active");
        });*/
    });
</script>
