@extends('layout.app')

@section('title')
Jurnal Umum
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Jurnal Umum</a></li>
    <li class="breadcrumb-item active" aria-current="page">Update Data</li>
</ol>
@endsection

@section('content')
{!! Form::model($jurnal_umum, ['route' => ['jurnalumum.updatedata',Crypt::encrypt($jurnal_umum->id)], 'class'=>'validated_form', 'id'=>'form_jurnal', 'enctype' => 'multipart/form-data']) !!}  
    <input type="hidden" name="idjurnal" id="idjurnal" value="{{Crypt::encrypt($jurnal_umum->id)}}">  
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <div class="card-body">
                    @include('jurnal_umum/_form', ['submit_text' => 'Create','jurnal_umum'=>$jurnal_umum,'kode_akun'=>$kode_akun])
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
    @include('jurnal_umum/_form_js', ['jurnal_umum'=>$jurnal_umum])
@endsection

