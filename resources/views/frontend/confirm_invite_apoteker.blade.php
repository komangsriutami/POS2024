@extends('frontend.layout_frontend.app')
@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-bar">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-12 col-12">
                <nav aria-label="breadcrumb" class="page-breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/homepage') }}">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Comfirmasi Undangan</li>
                    </ol>
                </nav>
                <h2 class="breadcrumb-title">Comfirmasi Undangan</h2>
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
                        <div class="col-md-12 col-lg-12 login-right">
                            <div class="login-header">
                                <h3>Comfirmasi Undangan</a></h3>
                            </div>
                            <!-- Register Form -->
                            <form action="{{route('confirm_apoteker_post')}}" method="POST">
                                {{csrf_field()}}
                                <input type="hidden" name="id" value="{{ $apoteker->id }}">
                                @if (session('status'))
                                <div class="alert alert-success">
                                    {{ session('status') }}
                                </div>
                                @endif
                                @if (count( $errors) > 0 )
                                <div class="alert alert-danger">
                                    @foreach ($errors->all() as $error)
                                    {{ $error }}<br>
                                    @endforeach
                                </div>
                                @endif
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        {!! Form::label('id_group_apotek', 'Group Apotek') !!}
                                        {!! Form::select('id_group_apotek', $group_apoteks, $apoteker->id_group_apotek, ['class' => 'form-control required input_select']) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('nostra', 'No. STRA') !!}
                                        {!! Form::text('nostra', $apoteker->nostra, array('class' => 'form-control required', 'placeholder'=>'Masukan Nomor STRA')) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('nama', 'Nama Lengkap') !!}
                                        {!! Form::text('nama', $apoteker->nama, array('class' => 'form-control required', 'placeholder'=>'Masukan Nama Lengkap')) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('tempat', 'Tempat Lahir') !!}
                                        {!! Form::text('tempat_lahir', $apoteker->tempat_lahir, array('class' => 'form-control required', 'placeholder'=>'Masukan Tempat Lahir')) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('tanggal_lahir', 'Pilih Tanggal Lahir') !!}
                                        {!! Form::date('tgl_lahir', $apoteker->tgl_lahir, array('type' => 'text', 'class' => 'form-control datepicker','placeholder' => 'Tanggal Lahir', 'id' => 'tgl_lahir')) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('id_jenis_kelamin', 'Pilih Jenis Kelamin') !!}
                                        {!! Form::select('id_jenis_kelamin', $jenis_kelamins, $apoteker->id_jenis_kelamin, ['class' => 'form-control required']) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('alamat', 'Alamat') !!}
                                        {!! Form::text('alamat', $apoteker->alamat, array('class' => 'form-control required', 'placeholder'=>'Masukan Alamat')) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('kwgn', 'Pilih Kewarganegaraan') !!}
                                        {!! Form::select('id_kewarganegaraan', $kewarganegaraans, $apoteker->id_kewarganegaraan, ['class' => 'form-control required']) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('agama', 'Pilih Agama') !!}
                                        {!! Form::select('id_agama', $agamas, $apoteker->id_agama, ['class' => 'form-control required']) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('golongan_darah', 'Golongan Darah') !!}
                                        {!! Form::select('id_gol_darah', $golongan_darahs, $apoteker->id_gol_darah, ['class' => 'form-control required']) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('telepon', 'Telepon') !!}
                                        {!! Form::text('telepon', $apoteker->telepon, array('class' => 'form-control required number', 'placeholder'=>'Masukan Nomor Telepon')) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('email', 'Email') !!}
                                        {!! Form::text('email', $apoteker->email, array('class' => 'form-control required', 'placeholder'=>'Masukan Alamat Email')) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('password', 'Password') !!}
                                        <input type="password" class="form-control required" name="password" placeholder="Masukan Password" autocomplete="'off'">
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('password_confirm', 'Comfirm Password') !!}
                                        <input type="password" class="form-control required" name="password_confirm" placeholder="Masukan Password Confirm" autocomplete="'off'">
                                    </div>
                                </div>
                                <button class="btn btn-primary btn-block btn-lg register-btn" type="submit">Confirmation</button>
                                <div class="login-or">
                                    <span class="or-line"></span>
                                    <span class="span-or">or</span>
                                </div>
                                <div class="text-right">
                                    <a class="forgot-link" href="{{ url('/login_apoteker') }}">Anda sudah mempunyai akun? Klik disini untuk login.</a>
                                </div>
                            </form>
                            <!-- /Register Form -->
                        </div>
                    </div>
                </div>
                <hr>
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
    $(document).ready(function() {});
</script>
@endsection