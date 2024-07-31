<!--
Model : Layout Form Tindakan
Author : Tangkas.
Date : 12/06/2021
-->

@if (count($errors) > 0)
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
    <div class="form-group col-md-12">
        {!! Form::label('nama', 'Nama Tindakan') !!}
        {!! Form::text('nama', $data_->nama, ['class' => 'form-control required', 'placeholder' => 'Masukkan nama tindakan']) !!}
    </div>
    <div class="form-group col-md-12">
        {!! Form::label('keterangan', 'Keterangan') !!}
        {!! Form::textarea('keterangan', $data_->keterangan, ['class' => 'form-control required', 'placeholder' => 'Masukkan keterangan']) !!}
    </div>
</div>
