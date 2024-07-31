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
        {!! Form::label('id_jenis_kartu', 'Jenis Kartu (*)') !!}
        {!! Form::select('id_jenis_kartu', $jenis_kartus, $kartu->id_jenis_kartu, ['class' => 'form-control required input_select']) !!}
    </div>
	<div class="form-group col-md-8">
	    {!! Form::label('nama', 'Nama Kartu (*)') !!}
	    {!! Form::text('nama', $kartu->nama, array('class' => 'form-control required', 'placeholder'=>'Masukan Nama')) !!}
	</div>
    <div class="form-group col-md-1">
        {!! Form::label('charge', 'Charge (*)') !!}
        {!! Form::text('charge', $kartu->charge, array('class' => 'form-control required', 'placeholder'=>'Masukan Charge')) !!}
    </div>
</div>