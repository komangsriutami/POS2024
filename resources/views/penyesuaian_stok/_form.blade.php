@if (count( $errors) > 0 )
    <div class="alert alert-danger">
        @foreach ($errors->all() as $error)
            {{ $error }}<br>        
        @endforeach
    </div>
@endif
<style type="text/css">
    .select2 {
      width: 100%!important; /* overrides computed width, 100px in your demo */
    }
</style>
<div class="row">
	<input type="hidden" name="id_obat" id="id_obat" value="{{ $obat->id }}">
	<div class="form-group col-md-6">
	    {!! Form::label('nama', 'Nama (*)') !!}
	    {!! Form::text('nama', $obat->nama, array('class' => 'form-control required', 'placeholder'=>'Masukan Nama', 'readonly'=>'readonly')) !!}
	</div>
	<div class="form-group col-md-2">
	    {!! Form::label('stok_awal', 'Stok Awal  (*)') !!}
	    {!! Form::text('stok_awal', $stok_harga->stok_akhir, array('class' => 'form-control required', 'placeholder'=>'Masukan Stok Awal', 'readonly'=>'readonly')) !!}
	</div>
	<div class="form-group col-md-2">
	    {!! Form::label('stok_akhir', 'Stok Akhir  (*)') !!}
	    {!! Form::text('stok_akhir', $penyesuaian_stok->stok_akhir, array('class' => 'form-control required', 'placeholder'=>'Masukan Stok Saat Ini')) !!}
	</div>
	<div class="form-group col-md-2">
	    {!! Form::label('hb_ppn', 'HBPPN  (*)') !!}
	    {!! Form::text('hb_ppn', $penyesuaian_stok->hb_ppn, array('class' => 'form-control required', 'placeholder'=>'Masukan HBPPN')) !!}
	</div>
	<div class="form-group col-md-12">
	    {!! Form::label('alasan', 'Alasan  (*)') !!}
	    {!! Form::textarea('alasan', $penyesuaian_stok->alasan, array('class' => 'form-control required', 'placeholder'=>'Masukan Alasan')) !!}
	</div>
</div>