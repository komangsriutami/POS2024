@extends('layout.app')

@section('title')
Menu
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Rbac</a></li>
    <li class="breadcrumb-item"><a href="#">Menu</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit Data</li>
</ol>
@endsection

@section('content')
{!! Form::model($menu, ['method' => 'PUT', 'class'=>'validated_form','id'=>'form-edit', 'route' => ['menu.update', $menu->id]]) !!}
	<div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    @include('menu/_form', ['submit_text' => 'Edit'])
                </div>
                <div class="border-top">
                    <div class="card-body">
                        <button class="btn btn-primary" type="submit" data-toggle="tooltip" data-placement="top" title="Simpan data"><i class="fa fa-save"></i> Simpan</button> 
                        <a href="{{ url('/menu') }}" class="btn btn-danger" data-toggle="tooltip" data-placement="top" title="Kembali ke daftar data"><i class="fa fa-undo"></i> Kembali</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
{!! Form::close() !!}
@endsection

@section('script')
	@include('menu/_form_js')
@endsection