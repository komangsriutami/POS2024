<div class="row" >
   <div class="form-group col-md-12" id="detail_item_beli_{{$nomer}}">
      <div class="input-group col-sm-12">
         <div class="custom-file">
            {!! Form::hidden('item_beli['.$nomer.'][urutan]', null, array('class'=>'urutan')) !!}
            {!! Form::hidden('item_beli['.$nomer.'][id]', $item_beli->id) !!}
            <span class="btn btn-sm btn-danger" onclick="delete_row_item_beli({{ $nomer }});" data-no="{{$nomer}}"><i class="fa fa-times"></i></span>
            &nbsp;
            {!! Form::hidden('item_beli['.$nomer.'][id_obat]', $item_beli->id_obat, array('id' => 'id_obat_'.$nomer, 'class' => 'form-control', 'placeholder'=>'Masukan id obat '.$nomer)) !!}
            {!! Form::text('item_beli['.$nomer.'][barcode]', $item_beli->barcode, array('id' => 'barcode_'.$nomer, 'class' => 'form-control', 'placeholder'=>'Barcode obat '.$nomer)) !!}
            &nbsp;
            <span class="btn btn-sm btn-primary"  data-toggle="modal" data-placement="top" title="Cari Item Obat" onclick="open_data_obat({{ $nomer }})"><i class="fa fa-search"></i></span>
         </div>
         <div class="input-group-append col-sm-7">
            {!! Form::text('item_beli['.$nomer.'][nama_obat]', $item_beli->nama_obat, array('id' => 'nama_obat_'.$nomer, 'class' => 'form-control', 'placeholder'=>'Nama Obat  '.$nomer, 'autocomplete' => 'off', 'readonly' => 'readonly')) !!}
         </div>
         <div class="input-group-append col-sm-2">
            {!! Form::text('item_beli['.$nomer.'][jumlah]', $item_beli->jumlah, array('id' => 'jumlah_'.$nomer, 'class' => 'form-control text_disable', 'placeholder'=>'Jumlah '.$nomer, 'autocomplete' => 'off')) !!}
         </div>
      </div>
   </div>
</div>
<style type="text/css">
   .pointer{
   cursor: pointer;
   }
</style>