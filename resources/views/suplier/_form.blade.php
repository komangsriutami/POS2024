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
        {!! Form::label('nama', 'Nama Suplier') !!}
        {!! Form::text('nama', $suplier->nama, array('class' => 'form-control required', 'placeholder'=>'Masukan Nama Suplier')) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('npwp', 'NPWP') !!}
        {!! Form::text('npwp', $suplier->npwp, array('class' => 'form-control required', 'placeholder'=>'Masukan NPWP')) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('alamat', 'Alamat') !!}
        {!! Form::text('alamat', $suplier->alamat, array('class' => 'form-control required', 'placeholder'=>'Masukan Alamat')) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('telepon', 'Telepon') !!}
        {!! Form::text('telepon', $suplier->telepon, array('class' => 'form-control required number', 'placeholder'=>'Masukan Nomor Telepon')) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('id_provinsi', 'Provinsi') !!}
        {!! Form::select('id_provinsi', $provinsis, $suplier->id_provinsi, ['class' => 'form-control required input_select']) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('id_kabupaten', 'Kabupaten') !!}
        {!! Form::select('id_kabupaten', $kabupatens, $suplier->id_kabupaten, ['class' => 'form-control required input_select']) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('operator', 'Operator') !!}
        {!! Form::text('operator', $suplier->operator, array('class' => 'form-control required', 'placeholder'=>'Masukan Nama Operator')) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('keterangan', 'Keterangan') !!}
            {!! Form::text('keterangan', $suplier->keterangan, array('class' => 'form-control', 'placeholder'=>'Masukan Keterangan')) !!}
    </div>
</div>
