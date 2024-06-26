@if (count( $errors) > 0 )
<div class="alert alert-danger">
    @foreach ($errors->all() as $error)
    {{ $error }}<br>
    @endforeach
</div>
@endif
<style type="text/css">
    .select2 {
        width: 100% !important;
        /* overrides computed width, 100px in your demo */
    }
</style>
<div class="row">
    <div class="form-group col-md-3">
        {!! Form::label('id_jenis_aset', 'Jenis Aset (*)') !!}
        {!! Form::select('id_jenis_aset', $jenis_asets, $aset->id_jenis_aset, ['class' => 'form-control required input_select']) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('kode_aset', 'Kode Aset (*)') !!}
        {!! Form::text('kode_aset', $aset->kode_aset, array('class' => 'form-control required', 'placeholder'=>'Masukan Kode Aset')) !!}
    </div>
    <div class="form-group col-md-7">
        {!! Form::label('nama', 'Nama (*)') !!}
        {!! Form::text('nama', $aset->nama, array('class' => 'form-control required', 'placeholder'=>'Masukan Nama')) !!}
    </div>
</div>