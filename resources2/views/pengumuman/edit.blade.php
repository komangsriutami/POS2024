@extends('layout.app')

@section('title')
Data Pengumuman
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Data Pengumuman</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit Data</li>
</ol>
@endsection

@section('content')
{!! Form::model($data_, ['method' => 'PUT', 'class'=>'validated_form', 'id'=>'form-edit', 'route' => ['pengumuman.update', $data_->id], 'files'=> true]) !!}    
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <div class="card-body">
                    @include('pengumuman/_form', ['submit_text' => 'Update', 'data_'=>$data_])
                </div>
                <div class="card-footer">
                    <button class="btn btn-primary" type="submit" data-toggle="tooltip" data-placement="top" title="Simpan data"><i class="fa fa-save"></i> Simpan</button> 
                    <a href="{{ url('/pengumuman') }}" class="btn btn-danger" data-toggle="tooltip" data-placement="top" title="Kembali ke daftar data"><i class="fa fa-undo"></i> Kembali</a>
            </div>
         </div>
    </div>
{!! Form::close() !!}
@endsection

@section('script')
    @include('pengumuman/_form_js')
@endsection



