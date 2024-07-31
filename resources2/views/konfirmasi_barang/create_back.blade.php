@extends('layout.app')

@section('title')
Konfirmasi Barang Datang
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Transaksi Pembelian</a></li>
    <li class="breadcrumb-item"><a href="#">Konfirmasi Barang Datang</a></li>
    <li class="breadcrumb-item active" aria-current="page">Create Data</li>
</ol>
@endsection

@section('content')
{!! Form::model(new App\TransaksiPembelian, ['route' => ['pembelian.konfirmasi_barang_store'], 'class'=>'validated_form']) !!}
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <div class="card-body">
                    @include('konfirmasi_barang/_form', ['submit_text' => 'Create'])
                </div>
                <div class="border-top">
                    <div class="card-body">
                        <button class="btn btn-primary" type="submit" data-toggle="tooltip" data-placement="top" title="Simpan data"><i class="fa fa-save"></i> Simpan</button> 
                        <a href="{{ url('/pembelian/konfirmasi_barang') }}" class="btn btn-danger" data-toggle="tooltip" data-placement="top" title="Kembali ke daftar data"><i class="fa fa-undo"></i> Kembali</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
{!! Form::close() !!}
@endsection

@section('script')
    @include('konfirmasi_barang/_form_js')
@endsection

