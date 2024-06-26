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
        {!! Form::label('id_apotek', 'Apotek (*)') !!}
        {!! Form::select('id_apotek', $apoteks, $setting_so->id_apotek, ['class' => 'form-control required input_select']) !!}
    </div>
	<div class="form-group col-md-3">
	    {!! Form::label('tgl_so', 'Tanggal SO (*)') !!}
	    {!! Form::text('tgl_so', $setting_so->tgl_so, array('class' => 'form-control required', 'placeholder'=>'Masukan Tanggal SO', 'autocomplete' => 'off')) !!}
	</div>
</div>