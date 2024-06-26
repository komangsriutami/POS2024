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
    <div class="form-group col-md-2">
        {!! Form::label('kode', 'Kode') !!}
        {!! Form::text('kode', $group_apotek->kode, array('class' => 'form-control required', 'placeholder'=>'Masukan Kode')) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('nama_singkat', 'Nama Singkat') !!}
        {!! Form::text('nama_singkat', $group_apotek->nama_singkat, array('class' => 'form-control required', 'placeholder'=>'Masukan Nama Singkat Apotek')) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('nama_panjang', 'Nama Panjang') !!}
        {!! Form::text('nama_panjang', $group_apotek->nama_panjang, array('class' => 'form-control required', 'placeholder'=>'Masukan Nama Panjang Apotek')) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('alamat', 'Alamat') !!}
        {!! Form::text('alamat', $group_apotek->alamat, array('class' => 'form-control required', 'placeholder'=>'Masukan Alamat')) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('telepon', 'Telepon') !!}
        {!! Form::text('telepon', $group_apotek->telepon, array('class' => 'form-control required number', 'placeholder'=>'Masukan Nomor Telepon')) !!}
    </div>
    <div class="form-group col-md-12">
        {!! Form::label('deskripsi', 'Deskripsi') !!}
        {!! Form::text('deskripsi', $group_apotek->deskripsi, array('class' => 'form-control', 'placeholder'=>'Masukan Deskripsi')) !!}
    </div>
</div>