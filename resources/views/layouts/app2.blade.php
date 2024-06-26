<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>BWF | Registrasi Pasien</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.6 -->
  {!! Html::style('assets/bootstrap/css/bootstrap.min.css') !!}
  <!-- Font Awesome -->
  {!! Html::style('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css') !!}
  <!-- Ionicons -->
  {!! Html::style('https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css') !!}
  <!-- Theme style -->
  {!! Html::style('assets/dist/css/AdminLTE.min.css') !!}
  <!-- iCheck -->
  {!! Html::style('assets/plugins/iCheck/square/blue.css') !!}
  {!! Html::style('assets/css/datepicker.css') !!}

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
  <style>
    html, body {
        height: 140vh;
    }

    .full-height {
        height: 140vh;
    }

    .flex-center {
        align-items: center;
        display: flex;
        justify-content: center;
    }

    .position-ref {
        position: relative;
    }

    .top-right {
        position: absolute;
        right: 10px;
        top: 18px;
    }

    .content {
        text-align: center;
    }

    .title {
        font-size: 84px;
    }

    .links > a {
        color: #636b6f;
        padding: 0 25px;
        font-size: 13px;
        font-weight: 600;
        letter-spacing: .1rem;
        text-decoration: none;
        text-transform: uppercase;
    }

    .m-b-md {
        margin-bottom: 30px;
    }
</style>
</head>
<body class="hold-transition login-page">
  <div class="flex-center position-ref full-height">
      <div class="top-right links">
          <a href="{{ url('register_pasien/view_data_registrasi') }}">List Pasien / Patient List</a>
          @if(Auth::check())
              <a href="{{ url('/home') }}">Home</a>
          @else
              <a href="{{ url('login') }}">Login</a>
          @endif
      </div>
      @yield('content')
  </div>

<!-- jQuery 2.1.4 -->
{!! Html::script('assets/plugins/jQuery/jquery-2.2.3.min.js') !!}
<!-- jQuery UI -->
{!! Html::script('assets/plugins/jQueryUI/jquery-ui.min.js') !!}
<!-- Bootstrap 3.3.2 JS -->
{!! Html::script('assets/bootstrap/js/bootstrap.min.js') !!}
<!-- AdminLTE App -->
{!! Html::script('assets/dist/js/app.min.js') !!}
{!! Html::script('assets/js/datepicker.js') !!}
<!--    timepicker -->
{!! Html::style('assets/plugins/timepicker/bootstrap-timepicker.min.css') !!}
{!! Html::script('assets/plugins/timepicker/bootstrap-timepicker.min.js') !!}

<!-- custom validation -->
<!-- {!! Html::script('assets/plugins/jqueryvalidation/my_validation.js') !!} -->

@include('/layout/modal')
@yield('script')
@include('/layout/infobox')
</body>
</html>
