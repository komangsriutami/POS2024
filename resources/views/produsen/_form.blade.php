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
        {!! Form::label('nama', 'Nama Produsen') !!}
        {!! Form::text('nama', $produsen->nama, array('class' => 'form-control required', 'placeholder'=>'Masukan Nama Produsen')) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('alamat', 'Alamat') !!}
            {!! Form::text('alamat', $produsen->alamat, array('class' => 'form-control required', 'placeholder'=>'Masukan Alamat')) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('telepon', 'Telepon') !!}
            {!! Form::text('telepon', $produsen->telepon, array('class' => 'form-control required number', 'placeholder'=>'Masukan Nomor Telepon')) !!}
    </div>
     <div class="form-group col-md-6">
        {!! Form::label('id_provinsi', 'Provinsi') !!}
        {!! Form::select('id_provinsi', $provinsis, $produsen->id_provinsi, ['class' => 'form-control required input_select']) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('id_kabupaten', 'Kabupaten') !!}
        {!! Form::select('id_kabupaten', $kabupatens, $produsen->id_kabupaten, ['class' => 'form-control required input_select']) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('keterangan', 'Keterangan') !!}
            {!! Form::text('keterangan', $produsen->keterangan, array('class' => 'form-control', 'placeholder'=>'Masukan Keterangan')) !!}
    </div>
</div>
