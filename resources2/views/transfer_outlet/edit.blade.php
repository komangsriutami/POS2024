@extends('layout.app')

@section('title')
Transaksi Transfer Outlet
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Transaksi</a></li>
    <li class="breadcrumb-item"><a href="#">Transaksi Transfer Outlet</a></li>
    <li class="breadcrumb-item active" aria-current="page">Create Data</li>
</ol>
@endsection

@section('content')
{!! Form::model($transfer_outlet, ['method' => 'PUT', 'class'=>'validated_form', 'id'=>'form_to', 'route' => ['transfer_outlet.update', $transfer_outlet->id]]) !!}
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
                <a class="btn btn-info text-white" style="text-decoration: none;" type="button" href="{{ url('transfer_outlet')}}" data-toggle="tooltip" data-placement="top" title="List Data Transfer Outlet"><i class="fa fa-home"></i></a> 
                <button class="btn btn-primary" type="button" onclick="save_data()" data-toggle="tooltip" data-placement="top" title="Simpan data" id="btn_save"><i class="fa fa-save"></i> Simpan | F2</button> 
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <div class="card-body">
                    @include('transfer_outlet/_form', ['submit_text' => 'Update', 'transfer_outlet'=>$transfer_outlet])
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
    @include('transfer_outlet/_form_js')
@endsection

