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
      <h3><i class="fas fa-exclamation-triangle text-warning"></i> Oops! Sorry you have no permission to see this page.</h3>

      <p>
        Meanwhile, you may <a href="{{ url('/home') }}">return to home.</a> 
      </p>
    </div>
    <!-- /.error-content -->
  </div>
  <!-- /.error-page -->
</div>
@endsection

@section('script')
<script type="text/javascript">
  var token = '{{csrf_token()}}';
</script>
@endsection