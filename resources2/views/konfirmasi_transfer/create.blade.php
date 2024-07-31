@extends('layout.app')

@section('title')
Konfirmasi Transfer
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Transaksi Transfer Outlet</a></li>
    <li class="breadcrumb-item"><a href="#">Konfirmasi Transfer</a></li>
    <li class="breadcrumb-item active" aria-current="page">Create Data</li>
</ol>
@endsection

@section('content')
{!! Form::model(new App\TransaksiTO, ['route' => ['transfer_outlet.konfirmasi_transfer_store'], 'class'=>'validated_form']) !!}
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <div class="card-body">
                    @include('konfirmasi_transfer/_form', ['submit_text' => 'Create'])
                </div>
                <div class="border-top">
                    <div class="card-body">
                        <button type="button" class="btn btn-primary" onclick="konfirm_barang_disetujui()" data-toggle="tooltip" data-placement="top" title="Konfirmasi Disetujui"><i class="fa fa-save"></i> Setuju</button> 
                        <button type="button" class="btn btn-warning" onclick="konfirm_barang_tidak_disetujui()" data-toggle="tooltip" data-placement="top" title="Konfirmasi Ditolak"><i class="fa fa-save"></i> Tolak</button> 
                        <a href="{{ url('/transfer_outlet/permintaan_transfer') }}" class="btn btn-danger" data-toggle="tooltip" data-placement="top" title="Kembali ke daftar data"><i class="fa fa-undo"></i> Kembali</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
{!! Form::close() !!}
@endsection

@section('script')
    @include('konfirmasi_transfer/_form_js')
@endsection

