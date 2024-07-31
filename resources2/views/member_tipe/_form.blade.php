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
	    {!! Form::label('nama', 'Nama (*)') !!}
	    {!! Form::text('nama', $member_tipe->nama, array('class' => 'form-control required', 'placeholder'=>'Masukan Nama')) !!}
	</div>
    <div class="form-group col-md-2">
        {!! Form::label('etichal', 'Etichal (*)') !!}
        {!! Form::text('etichal', $member_tipe->etichal, array('class' => 'form-control required', 'placeholder'=>'Masukan Etichal')) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('non_etichal', 'Non Etichal (*)') !!}
        {!! Form::text('non_etichal', $member_tipe->non_etichal, array('class' => 'form-control required', 'placeholder'=>'Masukan Non Etichal')) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('limit', 'Limit (*)') !!}
        {!! Form::text('limit', $member_tipe->limit, array('class' => 'form-control required', 'placeholder'=>'Masukan Limit')) !!}
    </div>
</div>