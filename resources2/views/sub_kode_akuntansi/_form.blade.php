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
	    {!! Form::label('id_kode_akun', 'Pilih Kode Akun (*)') !!}
	    {!! Form::select('id_kode_akun', $kode_akuntansis, $sub_kode_akuntansi->id_kode_akun, ['class' => 'form-control required input_select']) !!}
	</div>
	<div class="form-group col-md-2">
	    {!! Form::label('kode', 'Kode Akun (*)') !!}
	    {!! Form::text('kode', $sub_kode_akuntansi->kode, array('class' => 'form-control required', 'placeholder'=>'Masukan Kode Akun')) !!}
	</div>
	<div class="form-group col-md-7">
	    {!! Form::label('nama', 'Nama Akun (*)') !!}
	    {!! Form::text('nama', $sub_kode_akuntansi->nama, array('class' => 'form-control required', 'placeholder'=>'Masukan Nama Akun')) !!}
	</div>
</div>