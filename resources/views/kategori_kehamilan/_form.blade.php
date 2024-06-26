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
	    {!! Form::label('keterangan', 'Kategori Kehamilan') !!}
	    {!! Form::text('keterangan', $kategori_kehamilan->keterangan, array('class' => 'form-control required', 'placeholder'=>'Masukan Nama Kategori Kehamilan')) !!}
	</div>
</div>