@extends('layout.app')

@section('title')
Login As
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Home</a></li>
    <li class="breadcrumb-item active" aria-current="page">Login As</li>
</ol>
@endsection

@section('content')
     <div class="login-box" style="width:50%">
        <div class="login-logo">
            <a href="#"><b>Login</b>As</a>
        </div>
        <!-- /.login-logo -->
        <div class="login-box-body">
                <form role="form" method="POST" action="{{ url('loginas/login') }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="id_user_awal" id="id_user_awal" value="{{ $id_user }}">
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label></label>
                            <select name="user" id="user" class="form-control input_select">
                                @if($user != null)
                                    @foreach($user as $u)
                                        @if($u->id_sso != null)
                                            <option value="{{ $u->id }}">{{ $u->nama }}</option>
                                        @else
                                            <option value="{{ $u->id }}">{{ $u->nama }}</option>
                                        @endif
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group hide">
                        <input id="password" type="password" class="form-control" name="password" placeholder="Password">
                    </div>
                    <div class="row">
                        <div class="col-xs-12">
                            <a href="{{ url('/home') }}" class="btn btn-danger btn-flat ">Kembali</a>
                            <button type="submit" class="btn btn-tumblr btn-flat pull-right">Log In</button>
                        </div>
                        <!-- /.col -->
                    </div>
                </form>
            </div>
            <!-- /.login-box-body -->
        </div>
        <!-- /.login-box -->
@endsection

@section('script')
@endsection

