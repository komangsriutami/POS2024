@extends('layout.app')

@section('title')
Profil
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">User</a></li>
    <li class="breadcrumb-item active" aria-current="page">Profil</li>
</ol>
@endsection

@section('content')
    {!! Form::model($user, ['method' => 'PUT', 'class'=>'validated_form', 'route' => ['admin.update_profile', $user->id], 'files'=> true]) !!}
        <input type="hidden" name="from_profile" id="from_profile" value="0">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        @include('admin/_form', ['submit_text' => 'Update', 'user'=>$user])
                    </div>
                    <div class="border-top">
                        <div class="card-body">
                            <button class="btn btn-primary" type="submit" data-toggle="tooltip" data-placement="top" title="Simpan data"><i class="fa fa-save"></i> Simpan</button> 
                            <a href="{{ url('/home') }}" class="btn btn-danger" data-toggle="tooltip" data-placement="top" title="Kembali ke daftar data"><i class="fa fa-undo"></i> Kembali</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {!! Form::close() !!}
@endsection

@section('script')
    @include('admin/_form_js')
@endsection