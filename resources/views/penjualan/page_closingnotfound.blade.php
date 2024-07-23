@extends('layout.app')

@section('title')
Cetak Closing Transaksi Penjualan
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Transaksi</a></li>
    <li class="breadcrumb-item"><a href="#">Transaksi Penjualan</a></li>
    <li class="breadcrumb-item active" aria-current="page">Cetak Closing</li>
</ol>
@endsection

@section('content')
  <div class="card card-default" id="main-box" style="">
  <div class="error-page">
    <h2 class="headline text-warning"> 404</h2>

    <div class="error-content">
      <h3><i class="fas fa-exclamation-triangle text-warning"></i> Oops! anda belum melakukan closing.</h3>

      <p>
        Anda dapat <a href="{{ url('/penjualan') }}">kembali ke halaman list penjualan.</a> 
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