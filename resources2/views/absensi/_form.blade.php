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
        {!! Form::label('id_apotek', 'Pilih Apotek') !!}
        {!! Form::select('id_apotek', $apoteks, $absensi->id_apotek, ['class' => 'form-control required']) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('id_user', 'Pilih User') !!}
        {!! Form::select('id_user', $users, $absensi->id_user, ['class' => 'form-control required']) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('sesi', 'Pilih Sesi') !!}
        {!! Form::select('sesi', $sesi_jadwal_kerja, $absensi->sesi, ['class' => 'form-control required']) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('is_weekend', 'Weekend ?') !!}
        <div class="checkbox">
            <label>
                {!! Form::hidden('is_weekend', $absensi->is_weekend, array('class'=>'is_weekend')) !!}
                {!! Form::checkbox('is_weekend', 1, ($absensi->is_weekend == 1 ? true : false), ['id' => 'is_weekend']) !!} Ya
            </label>
        </div>
    </div>
    <div class="form-group col-md-4">
        {!! Form::label('jumlah_jam_kerja', 'Jumlah Jam Kerja') !!}
        {!! Form::text('jumlah_jam_kerja', $absensi->jumlah_jam_kerja, array('class' => 'form-control required', 'placeholder'=>'Otomatis Terhitung', 'readonly' => 'readonly')) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('jam_datang', 'Jam Datang') !!}
        {!! Form::text('jam_datang', $absensi->jam_datang, array('class' => 'form-control required', 'placeholder'=>'Masukan Jam Datang')) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('jam_pulang', 'Jam Pulang') !!}
        {!! Form::text('jam_pulang', $absensi->jam_pulang, array('class' => 'form-control required', 'placeholder'=>'Masukan Jam  Pulang')) !!}
    </div>
</div>