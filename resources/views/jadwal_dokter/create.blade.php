@extends('layout.app')
@section('title')
    Jadwal Dokter
@endsection

@section('breadcrumb')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="#">Home</a></li>
        <li class="breadcrumb-item">Jadwal Dokter</li>
        <li class="breadcrumb-item active">Create</li>
    </ol>
@endsection

@section('content')
    {!! Form::model(new App\JadwalDokter(), ['route' => ['jadwal_dokter.store'], 'class' => 'validated_form', 'files'=> true]) !!}
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <div class="card-body">
                    @include('jadwal_dokter._form', ['submit_text' => 'Create'])
                </div>
            </div>
        </div>
    </div>
    {!! Form::close() !!}
@endsection


@section('script')
    @include('jadwal_dokter._form_js')
@endsection
