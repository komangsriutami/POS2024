<!DOCTYPE html>
<html lang="en">

<head>
    @include('frontend.v2._head')
</head>
    @include('frontend.v2._header')
    <div class="col-sm-12">
        <!-- Breadcrumb -->
        <div class="breadcrumb-bar">
          <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6">
                  <p></p>
                  <h2 class="breadcrumb-title">@yield('title')</h2>
                  <p></p>
                </div>
                <div class="col-md-6">
                  <nav aria-label="breadcrumb" class="page-breadcrumb">
                      @yield('breadcrumb')
                  </nav>
              </div>
            </div>
          </div>
        </div>
        <!-- /Breadcrumb -->
    </div>

    <!-- Page Content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                @include('frontend.v2._sidebar')
                @yield('content')
            </div>
        </div>
    </div>
    <!-- /Page Content -->
    
    @include('frontend.v2._footer')
    @include('frontend.v2._footer_scripts')
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
