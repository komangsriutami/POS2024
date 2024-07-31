@extends('layout.app')

@section('title')
Setting Promo/Diskon
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Promo/Diskon</a></li>
    <li class="breadcrumb-item"><a href="#">Setting</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit Data</li>
</ol>
@endsection

@section('content')
{!! Form::model($setting_promo, ['method' => 'PUT', 'class'=>'validated_form', 'id'=>'form-edit', 'route' => ['setting_promo.update', $setting_promo->id]]) !!}

    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <div class="card-body">
                    @include('setting_promo/_form', ['submit_text' => 'Update', 'setting_promo'=>$setting_promo])
                </div>
                <div class="border-top">
                    <div class="card-body">
                        <button class="btn btn-primary" type="submit" data-toggle="tooltip" data-placement="top" title="Simpan data"><i class="fa fa-save"></i> Simpan</button> 
                        <a href="{{ url('/setting_promo') }}" class="btn btn-danger" data-toggle="tooltip" data-placement="top" title="Kembali ke daftar data"><i class="fa fa-undo"></i> Kembali</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
{!! Form::close() !!}
@endsection

@section('script')
    @include('setting_promo/_form_js')
@endsection

