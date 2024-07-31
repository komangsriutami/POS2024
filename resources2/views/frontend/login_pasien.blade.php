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

                    <!-- Login Tab Content -->
                    <div class="account-content">
                        <div class="row align-items-center justify-content-center">
                            <!-- <div class="col-md-7 col-lg-6 login-left">
                                <img src="assets_frontend/img/login-banner.png" class="img-fluid" alt="BWF Login">
                            </div> -->
                            <div class="col-md-12 col-lg-6 login-right">
                                <div class="login-header text-center">
                                    <h3>LOGIN PASIEN</h3>
                                    <hr>
                                </div>
                                <form method="POST" action="{{ route('login_pasien_post') }}">
                                    @csrf
                                    @if (session('status'))
                                        <div class="alert alert-success">
                                            {{ session('status') }}
                                        </div>
                                    @endif
                                    <div class="form-group">
                                        <div class="input-group">
                                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="Email">
                                            <div class="input-group-append">
                                                <div class="input-group-text">
                                                    <span class="fa fa-envelope"></span>
                                                </div>
                                            </div>
                                            @error('email')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="Password">
                                            <div class="input-group-append">
                                                <div class="input-group-text">
                                                    <span class="fas fa-lock"></span>
                                                </div>
                                            </div>
                                            @error('password')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <button class="btn btn-primary btn-lg login-btn text-right" type="submit">Login</button>
                                    </div>
                                    <div class="text-left">
                                        <a class="forgot-link" href="forgot-password">Lupa password</a> | <a class="forgot-link" href="register_pasien">Buat Akun</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- /Login Tab Content -->
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
@endsection
