@extends('layout.app')

@section('title')
User
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Rbac</a></li>
    <li class="breadcrumb-item"><a href="#">User</a></li>
    <li class="breadcrumb-item active" aria-current="page">Setting Akses Apotek</li>
</ol>
@endsection

@section('content')
{!! Form::model($user, ['method' => 'PUT', 'class'=>'validated_form','id'=>'form-edit', 'route' => ['admin.update_apotek_akses', $user->id]]) !!}
	<div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    @include('admin/_form_apoteks', ['submit_text' => 'Setting Akses Apotek'])
                </div>
                <div class="border-top">
                    <div class="card-body">
                        <button class="btn btn-primary" type="submit" data-toggle="tooltip" data-placement="top" title="Simpan data"><i class="fa fa-save"></i> Simpan</button> 
                        <a href="{{ url('/admin') }}" class="btn btn-danger" data-toggle="tooltip" data-placement="top" title="Kembali ke daftar data"><i class="fa fa-undo"></i> Kembali</a>
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