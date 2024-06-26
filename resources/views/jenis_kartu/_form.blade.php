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
	<div class="form-group col-md-12">
	    {!! Form::label('nama', 'Jenis Kartu') !!}
	    {!! Form::text('nama', $jenis_kartu->nama, array('class' => 'form-control required', 'placeholder'=>'Masukan Nama Jenis Kartu')) !!}
	</div>
</div>