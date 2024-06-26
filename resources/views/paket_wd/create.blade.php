@extends('layout.app')

@section('title')
Paket WT
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Data Master</a></li>
    <li class="breadcrumb-item"><a href="#">Paket WT</a></li>
    <li class="breadcrumb-item active" aria-current="page">Create Data</li>
</ol>
@endsection

@section('content')
{!! Form::model(new App\MasterPaketWD, ['route' => ['paket_wd.store'], 'class'=>'validated_form', 'files'=> true]) !!}
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        @include('paket_wd/_form', ['submit_text' => 'Create'])
                    </div>
                    <div class="border-top">
                        <div class="card-body">
                            <button class="btn btn-primary" type="submit" data-toggle="tooltip" data-placement="top" title="Simpan data"><i class="fa fa-save"></i> Simpan</button> 
                            <a href="{{ url('/paket_wd') }}" class="btn btn-danger" data-toggle="tooltip" data-placement="top" title="Kembali ke daftar data"><i class="fa fa-undo"></i> Kembali</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
{!! Form::close() !!}
@endsection

@section('script')
    @include('paket_wd/_form_js')
@endsection