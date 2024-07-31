<!DOCTYPE html>
<html>
@include('/frontend/v3/header')
<body class="main-header-fixed">
      @include('/frontend/v3/theme')

      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
            <!-- Main content -->
            @yield('content')
            <!-- /.content -->
      </div>
      <!-- /.content-wrapper -->
      <!-- ./wrapper -->
      @include('/frontend/v3/footer')
    @yield('script')
</body>
</html>
