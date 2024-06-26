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
	    {!! Form::label('nama', 'Nama') !!}
	    {!! Form::text('nama', $skema_gaji->nama, array('class' => 'form-control required', 'placeholder'=>'Masukan Satuan')) !!}
	</div>
    <div class="form-group col-md-3">
        {!! Form::label('tgl_berlaku_start', 'Start') !!}
        {!! Form::text('tgl_berlaku_start', $skema_gaji->tgl_berlaku_start, array('class' => 'form-control required', 'placeholder'=>'Masukan Satuan')) !!}
    </div>
    <div class="form-group col-md-3">
        {!! Form::label('tgl_berlaku_end', 'End') !!}
        {!! Form::text('tgl_berlaku_end', $skema_gaji->tgl_berlaku_end, array('class' => 'form-control required', 'placeholder'=>'Masukan Satuan')) !!}
    </div>
</div>