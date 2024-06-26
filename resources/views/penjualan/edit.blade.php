@extends('layout.app')

@section('title')
Transaksi Penjualan
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Transaksi</a></li>
    <li class="breadcrumb-item"><a href="#">Transaksi Penjualan</a></li>
    <li class="breadcrumb-item active" aria-current="page">Create Data</li>
</ol>
@endsection

@section('content')
{!! Form::model($penjualan, ['method' => 'PUT', 'class'=>'validated_form', 'id'=>'form_penjualan', 'route' => ['penjualan.update', $penjualan->id]]) !!}
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
                <a class="btn btn-info text-white" style="text-decoration: none;" type="button" href="{{ url('penjualan')}}" data-toggle="tooltip" data-placement="top" title="List Data Penjualan"><i class="fa fa-home"></i></a> 
                @if($hak_akses == 1)
                <button class="btn btn-primary" type="button" onclick="submit_valid()" data-toggle="tooltip" data-placement="top" title="Simpan data"><i class="fa fa-save"></i> Simpan | F2</button> 
                <button class="btn btn-primary" type="button" onclick="retur_item()" data-toggle="tooltip" data-placement="top" title="Simpan data retur"><i class="fa fa-undo-alt"></i> Retur</button> 
                @endif
                <!-- <button class="btn btn-primary" type="button" onclick="retur_item()" data-toggle="tooltip" data-placement="top" title="Simpan data retur"><i class="fa fa-undo-alt"></i> Retur</button>  -->
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <div class="card-body">
                    @include('penjualan/_form', ['submit_text' => 'Update', 'penjualan'=>$penjualan])
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
    @include('penjualan/_form_js')
@endsection

