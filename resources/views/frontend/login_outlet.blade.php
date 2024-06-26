@extends('frontend')

@section('content')
<!--dari sini mulai page wrapper-->
    <div id="page-wrapper">
        <div class="main-header bg-header wow fadeInDown">
            <div class="container">
                <a href="{{ url('/') }}" class="header-logo" title="APOTEKEREN"><span style="font-size: 18pt;color: #00897b!important;margin: 2pt;">APOTEKEREN</span><span style="font-size: 10pt;color: #00897b!important;">THE PHARMACY POINT OF SALE SYSTEM</span></a>
                @include('/frontend/v3/menubar-top')
            </div>
        </div>
        <div class="hero-box hero-box-smaller full-bg-5 font-inverse clearfix controls" style="padding: 20px!important;margin: none!important">
            <div class="container text-center">
                <p class="text-right">Home / Login</p>
            </div>
            <div class="hero-overlay bg-black"></div>
        </div>
        <div class="container large-padding">
            <div class="row">
                <div class="col-xs-12 col-md-6">
                    <img src="{{ url('img/register_image.png') }}" width="100%">
                </div>
                <div class="col-xs-12 col-md-6">
                    <form action="{{route('login_outlet_post')}}" id="login-validation" class="center-margin" method="POST">
                        <h3 class="text-center pad25B font-gray text-transform-upr font-size-23">- Login -</h3>
                        
                        {{csrf_field()}}
                        @if (count( $errors) > 0 )
                        <div class="alert alert-danger">
                            @foreach ($errors->all() as $error)
                            {{ $error }}<br>
                            @endforeach
                        </div>
                        @endif
                        @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                        @endif
                        
                        <div id="login-form" class="content-box bg-default">
                            <div class="content-box-wrapper pad20A">
                                <img class="mrg25B center-margin radius-all-100 display-block" src="../../assets/image-resources/gravatar.jpg" alt="">
                                <div class="form-group">
                                    <div class="input-group">
                                        <span class="input-group-addon addon-inside bg-gray">
                                            <i class="glyph-icon icon-building"></i>
                                        </span>
                                        <input type="text" name="kode_apotek" id="kode_apotek" value="{{ old('kode_apotek') }}" class="form-control @error('kode_apotek') is-invalid @enderror" required autocomplete="kode_apotek" autofocus placeholder="Kode Apotek">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="input-group">
                                        <span class="input-group-addon addon-inside bg-gray">
                                            <i class="glyph-icon icon-user"></i>
                                        </span>
                                        <input type="text" name="username" id="username" value="{{ old('username') }}" class="form-control @error('username') is-invalid @enderror" required autocomplete="username" autofocus placeholder="Username">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="input-group">
                                        <span class="input-group-addon addon-inside bg-gray">
                                            <i class="glyph-icon icon-lock"></i>
                                        </span>
                                        <input type="password" name="password" id="password" value="{{ old('password') }}" class="form-control @error('password') is-invalid @enderror" required autocomplete="password" autofocus placeholder="Password">
                                    </div>
                                </div>
                                <div class="form-group text-right">
                                    <button type="submit" class="btn btn-sm btn-primary">Login</button>
                                </div>
                                <div class="row">
                                    <div class="checkbox-primary col-md-6" style="height: 20px;">
                                        <label>
                                            <input type="checkbox" id="loginCheckbox1" class="custom-checkbox">
                                            Remember me
                                        </label>
                                    </div>
                                    <div class="text-right col-md-6">
                                        <a href="#" class="switch-button" switch-target="#login-forgot" switch-parent="#login-form" title="Recover password">Forgot your password?</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="login-forgot" class="content-box bg-default hide">
                            <div class="content-box-wrapper pad20A">

                                <div class="form-group">
                                    <label for="exampleInputEmail2">Email address:</label>
                                    <div class="input-group">
                                        <span class="input-group-addon addon-inside bg-gray">
                                            <i class="glyph-icon icon-envelope-o"></i>
                                        </span>
                                        <input type="email" class="form-control" id="exampleInputEmail2" placeholder="Enter email">
                                    </div>
                                </div>
                            </div>
                            <div class="button-pane text-center">
                                <button type="submit" class="btn btn-md btn-primary">Recover Password</button>
                                <a href="#" class="btn btn-md btn-link switch-button" switch-target="#login-form" switch-parent="#login-forgot" title="Cancel">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @include('/frontend/v3/footer2')
    </div>
@endsection