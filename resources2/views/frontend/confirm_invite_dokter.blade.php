@extends('frontend.layout_frontend.app')
@section('content')

<style>
    .form-focus .focus-label {
        top: -15px !important;
    }
    .form-focus .form-control {
        height: 55px;
        padding: 23px 12px 6px;
    }
</style>

<!-- Breadcrumb -->
<div class="breadcrumb-bar">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-12 col-12">
                <nav aria-label="breadcrumb" class="page-breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/homepage') }}">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Konfirmasi Undangan</li>
                    </ol>
                </nav>
                <h2 class="breadcrumb-title">Konfirmasi Undangan</h2>
            </div>
        </div>
    </div>
</div>
<!-- /Breadcrumb -->

<!-- Page Content -->
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <!-- Register Content -->
                <div class="account-content">
                    <div class="row align-items-center justify-content-center">
                        <div class="col-md-7 col-lg-6 align-self-start login-left">
                            <img src="images/register_image.png" class="img-fluid" alt="Logo">
                        </div>
                        <div class="col-md-12 col-lg-6 login-right">
                            <div class="login-header">
                                <h3>Konfirmasi Undangan</a></h3>
                            </div>
                            <!-- Register Form -->
                            <form action="{{route('confirm_dokter_post')}}" method="POST">
                                    {{csrf_field()}}
                                    <input type="hidden" name="id" value="{{ $dokter->id }}">
                                    @if (session('status'))
                                        <div class="alert alert-success">
                                            {{ session('status') }}
                                        </div>
                                    @endif
                                    <div class="form-group form-focus">
                                        {!! Form::select('id_group_apotek', $group_apoteks, $dokter->id_group_apotek, ['class' => 'form-control required floating input_select']) !!}
                                        <label class="focus-label">Pilih Group Apotek</label>
                                    </div>
                                    <div class="form-group form-focus">
                                        {!! Form::select('id_apotek', $apoteks, $dokter->id_apotek, ['class' => 'form-control required floating input_select']) !!}
                                        <label class="focus-label">Pilih Apotek</label>
                                    </div>
                                    <div class="form-group form-focus">
                                        {!! Form::select('spesialis', $spesialiss, $dokter->spesialis, ['class' => 'form-control required floating input_select']) !!}
                                        <label class="focus-label">Pilih Spesialis</label>
                                    </div>
                                    <div class="form-group form-focus">
                                        {!! Form::text('nama', $dokter->nama, array('class' => 'form-control floating required')) !!}
                                        <label class="focus-label">Nama</label>
                                    </div>
                                    <div class="form-group form-focus">
                                        {!! Form::text('sib', $dokter->sib, array('class' => 'form-control floating required')) !!}
                                        <label class="focus-label">SIB</label>
                                    </div>
                                    <div class="form-group form-focus">
                                        {!! Form::text('alamat', $dokter->alamat, array('class' => 'form-control floating required')) !!}
                                        <label class="focus-label">Alamat</label>
                                    </div>
                                    <div class="form-group form-focus">
                                        {!! Form::text('telepon', $dokter->telepon, array('class' => 'form-control floating required')) !!}
                                        <label class="focus-label">Telepon</label>
                                    </div>
                                    <div class="form-group form-focus">
                                        {!! Form::email('email', $dokter->email, array('class' => 'form-control floating required')) !!}
                                        <label class="focus-label">Email</label>
                                    </div>
                                    <div class="form-group form-focus">
                                        <input type="password" class="form-control floating required" name="password" value="{{$dokter->password}}">
                                        <label class="focus-label">Password</label>
                                    </div>
                                <button class="btn btn-primary btn-block btn-lg register-btn" type="submit">Registrasi</button>
                                <div class="login-or">
                                    <span class="or-line"></span>
                                    <span class="span-or">or</span>
                                </div>
                                <div class="text-right">
                                    <a class="forgot-link" href="{{ url('/login_dokter') }}">Anda sudah mempunyai akun? Klik disini untuk login.</a>
                                </div>
                            </form>
                            <!-- /Register Form -->
                        </div>
                    </div>
                    <hr>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /Page Content -->
</div>
@endsection
@section('script')
    <!-- ini diisi jika ada script tambahan yang hanya berlaku pada page ini-->

<script type="text/javascript">
    $(document).ready(function() {
    });
</script>
@endsection