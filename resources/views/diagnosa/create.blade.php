<!--
Model : Layout Create Diagnosa
Author : Tangkas.
Date : 22/06/2021
-->

@extends('layout.app')
@section('title')
    Data Diagnosa
@endsection

@section('breadcrumb')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="/">Home</a></li>
        <li class="breadcrumb-item">Data Diagnosa</li>
        <li class="breadcrumb-item active">Create</li>
    </ol>
@endsection

@section('content')
    {!! Form::model(new App\MasterDiagnosa(), ['route' => ['diagnosa.store'], 'class' => 'validated_form']) !!}
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <div class="card-body">
                    @include('diagnosa._form', ['submit_text' => 'Create'])
                </div>
                <div class="border-top">
                    <div class="card-body">
                        <button class="btn btn-primary" type="submit" data-toggle="tooltip" data-placement="top"
                            title="Simpan data"><i class="fa fa-save"></i> Simpan</button>
                        <a href="{{ url('/diagnosa') }}" class="btn btn-danger" data-toggle="tooltip" data-placement="top"
                            title="Kembali ke daftar data"><i class="fa fa-undo"></i> Kembali</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {!! Form::close() !!}
@endsection


@section('script')
    @include('diagnosa._form_js')
@endsection
