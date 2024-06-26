<!--
Model : Layout Edit Dokter
Author : Tangkas.
Date : 12/06/2021
-->

@extends('layout.app')
@section('title')
    Data Dokter
@endsection

@section('breadcrumb')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="#">Home</a></li>
        <li class="breadcrumb-item">Data Dokter</li>
        <li class="breadcrumb-item active">Edit</li>
    </ol>
@endsection

@section('content')
    {!! Form::model($data_, ['method' => 'PUT', 'class'=>'validated_form', 'id'=>'form-edit', 'files' => true, 'enctype' => 'multipart/form-data', 'route' => ['dokter.update', $data_->id]]) !!}
    {!! Form::hidden('id', null, ['class' => 'form-control', 'placeholder' => 'Id ', 'id' => 'id']) !!}
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <div class="card-body">
                    @include('dokter._form', ['submit_text' => 'Update','data_'=>$data_])
                </div>
                <div class="border-top">
                    <div class="card-body">
                        <button class="btn btn-primary" type="submit" data-toggle="tooltip" data-placement="top"
                            title="Simpan data"><i class="fa fa-save"></i> Simpan</button>
                        <a href="{{ url('/dokter') }}" class="btn btn-danger" data-toggle="tooltip" data-placement="top"
                            title="Kembali ke daftar data"><i class="fa fa-undo"></i> Kembali</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {!! Form::close() !!}
@endsection


@section('script')
    @include('dokter._form_js')
@endsection
