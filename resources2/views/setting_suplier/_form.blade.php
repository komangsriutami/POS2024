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
	<div class="form-group col-md-6">
        {!! Form::label('id_group_apotek', 'Group Apotek') !!}
        {!! Form::select('id_group_apotek', $group_apoteks, $klinik->id_group_apotek, ['class' => 'form-control required input_select']) !!}
    </div>
	<div class="form-group col-md-6">
	    {!! Form::label('nama', 'Nama') !!}
	    {!! Form::text('nama', $klinik->nama, array('class' => 'form-control required', 'placeholder'=>'Masukan Nama')) !!}
	</div>
	<div class="form-group col-md-4">
	    {!! Form::label('telepon', 'Telepon') !!}
	    {!! Form::text('telepon', $klinik->telepon, array('class' => 'form-control required number', 'placeholder'=>'Masukan Nomor Telepon')) !!}
	</div>
	<div class="form-group col-md-4">
	    {!! Form::label('email', 'Email') !!}
	    {!! Form::text('email', $klinik->email, array('class' => 'form-control required', 'placeholder'=>'Masukan Email')) !!}
	</div>
	<div class="form-group col-md-4">
	    {!! Form::label('alamat', 'Alamat') !!}
	    {!! Form::text('alamat', $klinik->alamat, array('class' => 'form-control required', 'placeholder'=>'Masukan Alamat')) !!}
	</div>
</div>