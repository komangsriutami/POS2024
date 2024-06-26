@extends('layout.app')

@section('title')
Invite Apoteker
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Data Master</a></li>
    <li class="breadcrumb-item"><a href="#">Data Apoteker</a></li>
    <li class="breadcrumb-item active" aria-current="page">Invite Apoteker</li>
</ol>
@endsection

@section('content')
{!! Form::model(new App\MasterApoteker, ['route' => ['apoteker.invite_submit'], 'class'=>'validated_form']) !!}
<div class="row">
    <div class="col-sm-12">
        <div class="card card-info card-outline">
            <div class="card-body">
                <div class="row">
                    <div class="form-group col-md-6">
                        {!! Form::label('nama', 'Nama Lengkap') !!}
                        {!! Form::text('nama', $apoteker->nama, array('class' => 'form-control required', 'placeholder'=>'Masukan Nama Lengkap')) !!}
                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('email', 'Email') !!}
                        {!! Form::text('email', $apoteker->email, array('class' => 'form-control required', 'placeholder'=>'Masukan Alamat Email')) !!}
                    </div>
                </div>
            </div>
            <div class="border-top">
                <div class="card-body">
                    <button class="btn btn-primary" type="submit" data-toggle="tooltip" data-placement="top" title="Simpan data"><i class="fa fa-save"></i> Simpan</button>
                    <a href="{{ url('/apoteker') }}" class="btn btn-danger" data-toggle="tooltip" data-placement="top" title="Kembali ke daftar data"><i class="fa fa-undo"></i> Kembali</a>
                </div>
            </div>
        </div>
    </div>
</div>
{!! Form::close() !!}
@endsection

@section('script')
@include('apoteker/_form_js')
@endsection