@extends('layout.app')

@section('title')
Setting Promo/Diskon
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Promo/Diskon</a></li>
    <li class="breadcrumb-item"><a href="#">Setting</a></li>
    <li class="breadcrumb-item active" aria-current="page">Create Data</li>
</ol>
@endsection

@section('content')
{!! Form::model(new App\SettingStokOpnam, ['route' => ['setting_promo.store'], 'class'=>'validated_form']) !!}
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <div class="card-body">
                    @include('setting_promo/_form', ['submit_text' => 'Create'])
                    <br>
                    <div class="card card-info col-sm-12">
                        <div class="card-tools">
                            <a class="btn btn-sm bg-info btn-flat pull-right" onClick="add_row_item_beli()"><i class="fa fa-plus"></i>  Tambah Item Beli</a>
                        </div>
                        <div class="card-body p-2" style="display: block;" id="detail_item_beli">
                            <?php 
                                $item_belis = $setting_promo->item_belis;
                                $jum = count($item_belis);

                                $item_beli = new App\SettingPromoItemBeli;
                            ?>

                            @if($jum>0)
                                <?php $nomer = 0; ?>
                                @foreach($item_beli as $item_beli)
                                    <?php $nomer++; ?>
                                    @include('setting_promo/_form_item_beli', ['nomer'=>$nomer, 'item_beli'=>$item_beli])
                                @endforeach
                            @else
                                <?php $y = 1; ?>
                                @for($nomer=0;$nomer<1;$nomer++)
                                    @include('setting_promo/_form_item_beli', ['nomer'=>$nomer+1, 'item_beli'=>$item_beli])
                                @endfor
                            @endif 
                            <input type="hidden" name="count" id="count" value="<?php echo $nomer ?>">
                       </div>
                       <!-- /.card-body -->
                    </div>
                    <div class="card card-info col-sm-12" id="form_item_diskon" style="display: none;">
                        <div class="card-tools">
                            <a class="btn btn-sm bg-info btn-flat pull-right" onClick="add_row_item_diskon()"><i class="fa fa-plus"></i>  Tambah Item Diskon</a>
                        </div>
                        <div class="card-body p-2" style="display: block;" id="detail_item_diskon">
                            <?php 
                                $item_diskons = $setting_promo->item_diskons;
                                $jum = count($item_diskons);

                                $item_diskon = new App\SettingPromoItemBeli;
                            ?>

                            @if($jum>0)
                                <?php $nomer = 0; ?>
                                @foreach($item_diskons as $item_diskon)
                                    <?php $nomer++; ?>
                                    @include('setting_promo/_form_item_diskon', ['nomer'=>$nomer, 'item_diskon'=>$item_diskon])
                                @endforeach
                            @else
                                <?php $y = 1; ?>
                                @for($nomer=0;$nomer<1;$nomer++)
                                    @include('setting_promo/_form_item_diskon', ['nomer'=>$nomer+1, 'item_diskon'=>$item_diskon])
                                @endfor
                            @endif 
                            <input type="hidden" name="counter" id="counter" value="<?php echo $nomer ?>">
                       </div>
                       <!-- /.card-body -->
                    </div>
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

