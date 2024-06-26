@extends('layout.app')

@section('title')
Home
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Home</a></li>
    <li class="breadcrumb-item active" aria-current="page">Page Not Authorized</li>
</ol>
@endsection

@section('content')
  <div class="card card-default" id="main-box" style="">
  <div class="error-page">
    <h2 class="headline text-warning"> 401</h2>

    <div class="error-content">
      <h3><i class="fas fa-exclamation-triangle text-warning"></i> Oops! Maaf anda belum memilih apotek yang benar, silakan pilih <b>Apotek {{ $apotek->nama_panjang }}</b>.</h3>

      <p>
        Untuk kembali ke home klik,  <a href="{{ url('/home') }}"> Home.</a> 
      </p>
    </div>
    <!-- /.error-content -->
  </div>
  <!-- /.error-page -->
</div>
@endsection

@section('style')
    <style>
        .content-wrapper {
            /* height: 100% !important; */
        }
        .content {
            min-height: calc(100vh - calc(3.5rem + 1px) - calc(3.5rem + 1px));
        }
    </style>
@endsection

@section('script')
<script type="text/javascript">
  var token = '{{csrf_token()}}';
</script>
@endsection