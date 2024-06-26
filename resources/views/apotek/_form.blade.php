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
        {!! Form::label('kode_apotek', 'Kode Singkat') !!}
        {!! Form::text('kode_apotek', $apotek->kode_apotek, array('class' => 'form-control required', 'placeholder'=>'Masukan Kode Apotek')) !!}
    </div>
    <div class="form-group col-md-5">
        {!! Form::label('nama_singkat', 'Nama Singkat') !!}
        {!! Form::text('nama_singkat', $apotek->nama_singkat, array('class' => 'form-control required', 'placeholder'=>'Masukan Nama Singkat Apotek')) !!}
    </div>
    <div class="form-group col-md-5">
        {!! Form::label('nama_panjang', 'Nama Panjang') !!}
        {!! Form::text('nama_panjang', $apotek->nama_panjang, array('class' => 'form-control required', 'placeholder'=>'Masukan Nama Panjang Apotek')) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('id_group_apotek', 'Group Apotek') !!}
        {!! Form::select('id_group_apotek', $group_apoteks, $apotek->id_group_apotek, ['class' => 'form-control required input_select']) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('alamat', 'Alamat') !!}
        {!! Form::text('alamat', $apotek->alamat, array('class' => 'form-control required', 'placeholder'=>'Masukan Alamat')) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('telepon', 'Telepon') !!}
        {!! Form::text('telepon', $apotek->telepon, array('class' => 'form-control required number', 'placeholder'=>'Masukan Nomor Telepon')) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('id_apoteker', 'Apoteker') !!}
        {!! Form::select('id_apoteker', $apotekers, $apotek->id_apoteker, ['class' => 'form-control required input_select']) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('no_stra', 'No. STRA') !!}
        {!! Form::text('nostra', $apotek->nostra, array('class' => 'form-control required', 'placeholder'=>'Masukan Nomor STRA')) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('no_sia', 'No. SIA') !!}
        {!! Form::text('nosia', $apotek->nosia, array('class' => 'form-control required', 'placeholder'=>'Masukan Nomor SIA')) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('tgl_berdiri', 'Pilih Tanggal Berdiri') !!}
        {!! Form::text('tanggalberdiri', $apotek->tanggalberdiri, array('type' => 'text', 'class' => 'form-control datepicker required','placeholder' => 'Tanggal Berdiri', 'id' => 'tanggalberdiri')) !!}
    </div>
</div>
