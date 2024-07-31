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
	<div class="form-group col-md-4">
	    {!! Form::label('nama', 'Nama Role (*)') !!}
	    {!! Form::text('nama', $role->nama, array('id' => 'nama', 'class' => 'form-control required', 'placeholder'=>'Nama Role')) !!}
	</div>
	<div class="form-group col-md-8">
	    {!! Form::label('deksripsi', 'Deskripsi') !!}
	    {!! Form::text('deksripsi', $role->deksripsi, array('id' => 'deksripsi', 'class' => 'form-control', 'placeholder'=>'Deskripsi')) !!}
	</div>
</div>

