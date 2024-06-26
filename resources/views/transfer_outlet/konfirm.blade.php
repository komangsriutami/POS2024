@extends('layout.app')

@section('title')
Konfirmasi Barang Datang
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Transaksi</a></li>
    <li class="breadcrumb-item"><a href="#">Transaksi Transfer Outlet</a></li>
    <li class="breadcrumb-item active" aria-current="page">Konfirmasi Barang Datang</li>
</ol>
@endsection

@section('content')
{!! Form::model($transfer_outlet, ['method' => 'PUT', 'class'=>'validated_form', 'id'=>'form_toKonfirm', 'route' => ['transfer_outlet.konfirm_update', $transfer_outlet->id]]) !!}
    <style type="text/css">
        #divfix {
           bottom: 0;
           right: 0;
           position: fixed;
           z-index: 3000;
            }
        .format_total {
            font-size: 18px;
            font-weight: bold;
            color:#D81B60;
        }
    </style>

    <div class="row" id="divfix">
        <div class="col-sm-12">
            <div class="callout callout-success">
                <a class="btn btn-info text-white" style="text-decoration: none;" type="button" href="{{ url('transfer_outlet/konfirmasi_barang')}}" data-toggle="tooltip" data-placement="top" title="List Data Konfirmasi Barang"><i class="fa fa-home"></i></a> 
                @if($transfer_outlet->is_status != 1)
                <button class="btn btn-primary" type="button" onclick="submit_valid_konfirm(1)" data-toggle="tooltip" data-placement="top" title="Konfirmasi barang diterima"><i class="fa fa-check-circle"></i> Diterima</button> 
                <button class="btn btn-danger" type="button" onclick="submit_valid_konfirm(2)" data-toggle="tooltip" data-placement="top" title="Konfirmasi barang tidak diterima"><i class="fa fa-times-circle"></i> Tidak Diterima</button> 
                @endif
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <div class="card-body">
                    <input type="hidden" name="is_status" id="is_status">
                    @include('transfer_outlet/_form_konfirm', ['submit_text' => 'Update', 'transfer_outlet'=>$transfer_outlet])
                </div>
            </div>
        </div>
    </div>
{!! Form::close() !!}
@endsection

@section('style')
    <style>
        .content-wrapper {
            /* height: 100% !important; */
        }
        .content {
            min-height: calc(100vh - calc(3.5rem + 1px) - calc(3.5rem + 1px));
        }
    </style>
@endsection

@section('script')
    @include('transfer_outlet/_form_konfirm_js')
@endsection

