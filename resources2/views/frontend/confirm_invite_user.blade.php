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
                        <li class="breadcrumb-item active" aria-current="page">Register User</li>
                    </ol>
                </nav>
                <h2 class="breadcrumb-title">Register User</h2>
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
                        {{-- <div class="col-md-7 col-lg-6 align-self-start login-left">
                            <img src="images/register_image.png" class="img-fluid" alt="Logo">
                        </div> --}}
                        <div class="col-md-12 login-right">
                            <div class="login-header">
                                <h3>Registrasi User</a></h3>
                            </div>
                            <!-- Register Form -->
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
                            <form action="{{route('confirm_user_post')}}" method="POST" class="row">
                                {{csrf_field()}}
                                <input type="hidden" name="id" value="{{ $user->id }}">
                                <div class="col-md-6">
                                    <div class="form-group form-focus">
                                        {!! Form::text('nama', $user->nama, array('class' => 'form-control floating required')) !!}
                                        <label class="focus-label">Nama</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group form-focus">
                                        {!! Form::text('tempat_lahir', $user->tempat_lahir, array('class' => 'form-control floating required')) !!}
                                        <label class="focus-label">Tempat Lahir</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group form-focus">
                                        {!! Form::text('tgl_lahir', $user->tgl_lahir, array('class' => 'form-control floating required', 'id'=>'tgl_lahir')) !!}
                                        <label class="focus-label">Tanggal Lahir</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group form-focus">
                                        {!! Form::select('id_jenis_kelamin', $jenis_kelamins, $user->id_jenis_kelamin, ['class' => 'form-control custom-select floating required']) !!}
                                        <label class="focus-label">Pilih Jenis Kelamin</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group form-focus">
                                        {!! Form::select('id_kewarganegaraan', $kewarganegaraans, $user->id_kewarganegaraan, ['class' => 'form-control required custom-select floating input_select']) !!}
                                        <label class="focus-label">Pilih Kewarganegaraan</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group form-focus">
                                        {!! Form::select('id_gol_darah', $golongan_darahs, $user->id_gol_darah, ['class' => 'form-control required custom-select floating input_select']) !!}
                                        <label class="focus-label">Pilih Golongan Darah</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group form-focus">
                                        {!! Form::select('id_agama', $agamas, $user->id_agama, ['class' => 'form-control required custom-select floating input_select']) !!}
                                        <label class="focus-label">Pilih Agama</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group form-focus">
                                        {!! Form::select('id_group_apotek', $group_apoteks, $user->id_group_apotek, ['class' => 'form-control required custom-select floating input_select']) !!}
                                        <label class="focus-label">Pilih Group Apotek</label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group form-focus">
                                        {!! Form::text('alamat', $user->alamat, array('class' => 'form-control floating required')) !!}
                                        <label class="focus-label">Alamat</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group form-focus">
                                        {!! Form::text('telepon', $user->telepon, array('class' => 'form-control floating number required')) !!}
                                        <label class="focus-label">Telepon</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group form-focus">
                                        {!! Form::text('email', $user->email, array('class' => 'form-control floating email required')) !!}
                                        <label class="focus-label">Email</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group form-focus">
                                        {!! Form::text('username', $user->username, array('class' => 'form-control floating required')) !!}
                                        <label class="focus-label">Username</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group form-focus">
                                        <input type="password" class="form-control floating required" name="password" value="{{$user->password}}">
                                        <label class="focus-label">Password</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-primary btn-block btn-lg login-btn" type="submit">Registrasi</button>
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
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/css/bootstrap-datepicker.css" rel="stylesheet">
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/js/bootstrap-datepicker.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        $('#tgl_lahir').datepicker({
		    autoclose:true,
			format:"yyyy-mm-dd",
		    forceParse: false,
		});
        $('#tgl_lahir').focus(function(){
            var $this = $(this);
            var top = $this.offset().top - 200;
            var left = $this.offset().left;

            $('.datepicker').css({
                'top' : top
            });
        });

    });
</script>
@endsection
