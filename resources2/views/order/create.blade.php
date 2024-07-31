@extends('layout.app')

@section('title')
Nota Order
 <a class="btn bg-navy margin" href="#" onclick="find_ketentuan_keyboard()"><i class="fa fa-bookmark-o"></i> Kode Keyboard | F10</a>
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Pembelian</a></li>
    <li class="breadcrumb-item"><a href="#">Nota Order</a></li>
    <li class="breadcrumb-item active" aria-current="page">Create Data</li>
</ol>
@endsection

@section('content')
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
{!! Form::model(new App\TransaksiOrder, ['route' => ['order.store'], 'class'=>'validated_form', 'id'=> 'form_order', 'name' => 'form_order']) !!}
    <div class="row" id="divfix">
        <div class="col-sm-12">
            <div class="callout callout-success">
                <a class="btn btn-info text-white" style="text-decoration: none;" type="button" href="{{ url('order/data_order')}}" data-toggle="tooltip" data-placement="top" title="List Data SP"><i class="fa fa-home"></i></a> 
                <button class="btn btn-primary" type="button" onclick="save_data()" data-toggle="tooltip" data-placement="top" title="Simpan data"><i class="fa fa-save"></i> Simpan | F2</button> 
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <div class="card-body">
                    @include('order/_form', ['submit_text' => 'Create'])
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
    @include('order/_form_js')
@endsection

