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
	<div class="form-group col-sm-9">
	    {!! Form::label('nama', 'Nama (*)') !!}
	    {!! Form::text('nama', $paket_wd->nama, array('class' => 'form-control required', 'placeholder'=>'Masukan Nama')) !!}
	</div>
	<div class="form-group col-sm-3">
	    {!! Form::label('harga', 'Harga (*)') !!}
	    {!! Form::text('harga', $paket_wd->harga, array('class' => 'form-control required', 'placeholder'=>'Masukan Harga')) !!}
	</div>
	<div class="form-group col-sm-12">
	    {!! Form::label('keterangan', 'Harga (*)') !!}
	    {!! Form::text('keterangan', $paket_wd->keterangan, array('class' => 'form-control required', 'placeholder'=>'Masukan Keterangan')) !!}
	</div>
</div>