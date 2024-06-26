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
	<div class="form-group col-md-9">
	    {!! Form::label('nama', 'Nama') !!}
	    {!! Form::text('nama', $jasa_resep->nama, array('class' => 'form-control required', 'placeholder'=>'Masukan Nama Jasa Resep')) !!}
	</div>
	<div class="form-group col-md-3">
	    {!! Form::label('biaya', 'Biaya') !!}
	    {!! Form::text('biaya', $jasa_resep->biaya, array('class' => 'form-control required number', 'placeholder'=>'Masukan Biaya')) !!}
	</div>
</div>