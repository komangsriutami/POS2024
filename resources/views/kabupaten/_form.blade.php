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
    <div class="form-group col-md-3">
        {!! Form::label('id_provinsi', 'Provinsi') !!}
        {!! Form::select('id_provinsi', $provinsis, $kabupaten->id_provinsi, ['class' => 'form-control required input_select']) !!}
    </div>
	<div class="form-group col-md-9">
	    {!! Form::label('nama', 'Nama Kabupaten') !!}
	    {!! Form::text('nama', $kabupaten->nama, array('class' => 'form-control required', 'placeholder'=>'Masukan Nama Kabupaten')) !!}
	</div>
</div>