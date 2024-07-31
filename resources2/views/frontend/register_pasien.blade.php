@extends('frontend.v2._app')
@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-bar">
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
                        <div class="col-md-12 col-lg-6 login-right">
                            <div class="login-header text-center">
                                <h3>REGISTRASI PASIEN</h3>
                                <hr>
                            </div>
                            <!-- Register Form -->
                            <form action="{{route('register_pasien_post')}}" method="POST">
                                {{csrf_field()}}
                                @if (session('status'))
                                    <div class="alert alert-success">
                                        {{ session('status') }}
                                    </div>
                                @endif
                                <div class="form-group form-focus">
                                    <input type="text" name="nama" value="{{ old('nama') }}" class="form-control floating">
                                    <label class="focus-label">Nama</label>
                                </div>
                                <div class="form-group form-focus">
                                    <input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control floating">
                                    <label class="focus-label">Email</label>
                                </div>
                                <div class="form-group form-focus">
                                    <input type="password" name="password" value="{{ old('password') }}" class="form-control floating">
                                    <label class="focus-label">Password</label>
                                </div>
                                <div class="form-group form-focus">
                                    <input type="password" name="password_confirm" value="{{ old('password_confirm') }}" class="form-control floating">
                                    <label class="focus-label">Konfirmasi Password</label>
                                </div>
                                <div class="text-right">
                                    <button class="btn btn-primary btn-lg register-btn" type="submit">Register</button>
                                </div>
                                <div class="login-or">
                                    <span class="or-line"></span>
                                    <span class="span-or">or</span>
                                </div>
                                <div class="text-right">
                                    <a class="forgot-link" href="{{ url('/login_pasien') }}">Anda sudah mempunyai akun? Klik disini untuk login.</a>
                                </div>
                            </form>
                            <!-- /Register Form -->
                        </div>
                    </div>
                </div>
                <br>
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
