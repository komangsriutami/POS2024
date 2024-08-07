@extends('layout.app')
@section('title')
    Data News
@endsection

@section('breadcrumb')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="#">Home</a></li>
        <li class="breadcrumb-item">Data News</li>
        <li class="breadcrumb-item active">Create</li>
    </ol>
@endsection

@section('content')
    {!! Form::model($data_, ['method' => 'PUT', 'class'=>'validated_form', 'id'=>'form-edit', 'enctype'=>"multipart/form-data", 'route' => ['news.update', $data_->id]]) !!}
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <div class="card-body">
                    @include('news/_form', ['submit_text' => 'Update', 'data_'=>$data_])
                </div>
                <div class="border-top">
                    <div class="card-body">
                        <button class="btn btn-primary" type="submit" data-toggle="tooltip" data-placement="top"
                            title="Simpan data"><i class="fa fa-save"></i> Simpan</button>
                        <a href="{{ url('/news') }}" class="btn btn-danger" data-toggle="tooltip" data-placement="top"
                            title="Kembali ke daftar data"><i class="fa fa-undo"></i> Kembali</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {!! Form::close() !!}
@endsection


@section('script')
    @include('news._form_js')
@endsection
