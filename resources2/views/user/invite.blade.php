<!--
Model : Layout Invite User
Author : Govi.
Date : 14/06/2021
-->

@extends('layout.app')
@section('title')
    Invite User
@endsection

@section('breadcrumb')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="#">Home</a></li>
        <li class="breadcrumb-item">Data User</li>
        <li class="breadcrumb-item active">Invite</li>
    </ol>
@endsection

@section('content')
    {!! Form::model(new App\User(), ['route' => ['user.invite_submit'], 'class' => 'validated_form']) !!}
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <div class="card-body">
                    @if (count($errors) > 0)
                        <div class="alert alert-danger">
                            @foreach ($errors->all() as $error)
                                {{ $error }}<br>
                            @endforeach
                        </div>
                    @endif
                    <div class="row">
                        <div class="form-group col-md-6">
                            {!! Form::label('nama', 'Nama') !!}
                            {!! Form::text('nama', $user->nama, ['class' => 'form-control required', 'placeholder' => 'Masukan Nama']) !!}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('email', 'Email') !!}
                            {!! Form::text('email', $user->email, ['class' => 'form-control required', 'placeholder' => 'Masukan Email']) !!}
                        </div>
                        <div class="form-group col-md-12">
                            {!! Form::label('role', 'Wewenang') !!}
                            <?php $i = 0 ?>
                            @foreach ($roles as $role)
                                <div class="mb-2">
                                    <input type="checkbox" name="roles[{{$i}}]" value="{{$role->id}}">
                                    <span class="ml-2">{{ $role->nama }}</span>
                                    <div class="ml-4 text-secondary">{{ $role->deksripsi }}</div>
                                </div>
                                <?php $i++ ?>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="border-top">
                    <div class="card-body">
                        <button class="btn btn-primary" type="submit" data-toggle="tooltip" data-placement="top"
                            title="Simpan data"><i class="fa fa-save"></i> Simpan</button>
                        <a href="{{ url('/user') }}" class="btn btn-danger" data-toggle="tooltip" data-placement="top" title="Kembali ke daftar data"><i class="fa fa-undo"></i> Kembali</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {!! Form::close() !!}
@endsection


@section('script')
    @include('user._form_js')
@endsection