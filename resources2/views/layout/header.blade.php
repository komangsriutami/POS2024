<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>{{ session('apotek_group')->nama_singkat }} | Dashboard</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Font Awesome -->
  {!! Html::style('assets/plugins/fontawesome-free/css/all.min.css') !!}
  <link rel="stylesheet" href="">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Select2 -->
  {!! Html::style('assets/plugins/select2/css/select2.min.css') !!}
  <!-- Tempusdominus Bbootstrap 4 -->
  {!! Html::style('assets/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') !!}
  <!-- iCheck -->
  {!! Html::style('assets/plugins/icheck-bootstrap/icheck-bootstrap.min.css') !!}
  <!-- JQVMap -->
 <!--  {!! Html::style('assets/plugins/jqvmap/jqvmap.min.css') !!} -->
  <!-- Theme style -->

   <!-- DataTables -->
   {!! Html::style('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') !!}
   {!! Html::style('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') !!}

   {!! Html::style('assets/plugins/datatables_editor/css/editor.bootstrap4.min.css') !!}
   {!! Html::style('assets/plugins/datatables_editor/css/editor.dataTables.min.css') !!}
  <!-- pace-progress -->
  {!! Html::style('assets/plugins/pace-progress/themes/black/pace-theme-flat-top.css') !!}
  {!! Html::style('assets/dist/css/adminlte.min.css') !!}
  <!-- overlayScrollbars -->
  {!! Html::style('assets/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') !!}
  <!-- Daterange picker -->
  {!! Html::style('assets/plugins/daterangepicker/daterangepicker.css') !!}
  <!-- summernote -->
  {!! Html::style('assets/plugins/summernote/summernote-bs4.css') !!}
  {!! Html::style('assets/plugins/sweetalert/sweetalert.css') !!}

  {!! Html::style('assets/plugins/datepicker/datepicker3.css') !!}
  {!! Html::style('js/_validation.css') !!}

  <link rel="stylesheet" href="{{ url('assets/plugins/fullcalendar/fullcalendar.min.css') }}">
  <link rel="stylesheet" href="{{ url('assets/plugins/fullcalendar/fullcalendar.print.css') }}" media="print">
  
  <!-- Google Font: Source Sans Pro -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
  <style type="text/css">
    #loader {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      width: 100%;
      background: rgba(0,0,0,0.75)  url('{{ asset('assets/dist/gif/loading-16.gif')}}') no-repeat center center;
      z-index: 10000;
    }
  </style>
</head>