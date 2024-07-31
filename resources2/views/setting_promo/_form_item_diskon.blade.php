<div class="row" >
   <div class="form-group col-md-12" id="detail_item_beli_{{$nomer}}">
      <div class="input-group col-sm-12">
         <div class="custom-file">
            {!! Form::hidden('item_diskon['.$nomer.'][urutan]', null, array('class'=>'urutan')) !!}
            {!! Form::hidden('item_diskon['.$nomer.'][id]', $item_diskon->id) !!}
            <span class="btn btn-sm btn-danger" onclick="delete_row_item_diskon({{ $nomer }});" data-no="{{$nomer}}"><i class="fa fa-times"></i></span>
            &nbsp;
            {!! Form::hidden('item_diskon['.$nomer.'][id_obat]', $item_diskon->id_obat, array('id' => 'id_obat_'.$nomer, 'class' => 'form-control', 'placeholder'=>'Masukan id obat '.$nomer)) !!}
            {!! Form::text('item_diskon['.$nomer.'][barcode]', $item_diskon->barcode, array('id' => 'barcode_'.$nomer, 'class' => 'form-control', 'placeholder'=>'Barcode obat '.$nomer)) !!}
            &nbsp;
            <span class="btn btn-sm btn-primary"  data-toggle="modal" data-placement="top" title="Cari Item Obat" onclick="open_data_obat('')"><i class="fa fa-search"></i></span>
         </div>
         <div class="input-group-append col-sm-2">
            {!! Form::text('item_diskon['.$nomer.'][jumlah]', $item_diskon->jumlah, array('id' => 'jumlah_'.$nomer, 'class' => 'form-control text_disable', 'placeholder'=>'Jumlah '.$nomer, 'autocomplete' => 'off')) !!}
         </div>
      </div>
   </div>
</div>
<style type="text/css">
   .pointer{
   cursor: pointer;
   }
</style>