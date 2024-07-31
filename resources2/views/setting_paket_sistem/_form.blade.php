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
        {!! Form::label('id_jenis_paket_sistem', 'Pilih Jenis Paket Sistem') !!}
        {!! Form::select('id_jenis_paket_sistem', $jenis_paket_sistems, $setting_paket_sistem->id_jenis_paket_sistem, ['class' => 'form-control required input_select']) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('fee', 'Fee') !!}
        {!! Form::number('fee', $setting_paket_sistem->fee, array('class' => 'form-control required number', 'placeholder'=>'Masukan Jumlah Fee')) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('jumlah_user', 'Jumlah User') !!}
        {!! Form::number('jumlah_user', $setting_paket_sistem->jumlah_user, array('class' => 'form-control required number', 'placeholder'=>'Masukan Jumlah User')) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('jumlah_dokter', 'Jumlah Dokter') !!}
        {!! Form::number('jumlah_dokter', $setting_paket_sistem->jumlah_dokter, array('class' => 'form-control required number', 'placeholder'=>'Masukan Jumlah Dokter')) !!}
    </div>
    <div class="form-group col-md-12">
        {!! Form::label('keterangan', 'Keterangan') !!}
        {!! Form::textarea('keterangan', $setting_paket_sistem->keterangan, array('class' => 'form-control required', 'placeholder'=>'Masukan Keterangan')) !!}
    </div>
</div>
<script>
    $('#id_jenis_paket_sistem').select2({
        placeholder: "Pilih...",
    });
</script>