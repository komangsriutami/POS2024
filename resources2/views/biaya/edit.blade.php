@extends('layout.app')

@section('title')
Biaya
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Biaya</a></li>
    <li class="breadcrumb-item active" aria-current="page">Update Data</li>
</ol>
@endsection

@section('content')
{!! Form::model($biaya, ['route' => ['biaya.updatedata',Crypt::encrypt($biaya->id)], 'class'=>'validated_form', 'id'=>'form_biaya', 'enctype' => 'multipart/form-data']) !!}  
    <input type="hidden" name="idbiaya" id="idbiaya" value="{{Crypt::encrypt($biaya->id)}}">  
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <div class="card-body">
                    @include('biaya/_form', ['submit_text' => 'Create','biaya'=>$biaya])
                </div>
                <div class="border-top">
                    <div class="card-body text-center">
                        <button class="btn btn-primary" type="submit" data-toggle="tooltip" data-placement="top" title="Simpan data"><i class="fa fa-save"></i> Simpan</button> 
                        <div onclick="goBack()" class="btn btn-danger" data-toggle="tooltip" data-placement="top" title="Kembali ke daftar data"><i class="fa fa-undo"></i> Kembali</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
{!! Form::close() !!}
@endsection

@section('script')
    @include('biaya/_form_js', ['biaya'=>$biaya])
@endsection

