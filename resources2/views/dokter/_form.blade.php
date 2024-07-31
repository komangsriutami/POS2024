<!--
Model : Layout Form Dokter
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
    <div class="form-group col-md-6">
        {!! Form::label('id_group_apotek', 'Group Apotek') !!}
        {!! Form::select('id_group_apotek', $group_apoteks, $data_->id_group_apotek, ['class' => 'form-control required input_select', 'autocomplete' => 'off']) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('id_apotek', 'Lokasi Apotek') !!}
        {!! Form::select('id_apotek', $apoteks, $data_->id_apotek, ['class' => 'form-control required input_select', 'autocomplete' => 'off']) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('nama', 'Nama Dokter') !!}
        {!! Form::text('nama', $data_->nama, ['class' => 'form-control required', 'placeholder' => 'Masukan Nama Lengkap', 'autocomplete' => 'off']) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('spesialis', 'Pilih Spesialis') !!}
        {!! Form::select('spesialis', $spesialiss, $data_->spesialis, ['class' => 'form-control required input_select', 'autocomplete' => 'off']) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('sib', 'Surat Izin Praktek Dokter') !!}
        {!! Form::text('sib', $data_->sib, ['class' => 'form-control required', 'placeholder' => 'Masukan Surat Izin Praktek Dokter', 'autocomplete' => 'off']) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('email', 'Email') !!}
        {!! Form::text('email', $data_->email, ['class' => 'form-control required email', 'placeholder' => 'Masukan Alamat', 'autocomplete' => 'off']) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('alamat', 'Alamat') !!}
        {!! Form::text('alamat', $data_->alamat, ['class' => 'form-control required', 'placeholder' => 'Masukan Alamat', 'autocomplete' => 'off']) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('telepon', 'Telepon') !!}
        {!! Form::text('telepon', $data_->telepon, ['class' => 'form-control required', 'placeholder' => 'Masukan Telepon', 'autocomplete' => 'off']) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('fee', 'Biaya Dokter') !!}
        {!! Form::text('fee', $data_->fee, ['class' => 'form-control required', 'placeholder' => 'Masukan Biaya Dokter', 'autocomplete' => 'off']) !!}
    </div>
    <div class="form-group col-md-6">
        @if($data_->password != null)
        <label><input type="checkbox" name="is_ganti_password" id="is_ganti_password" value="1"> Klik untuk ganti password </label>
        <input type="hidden" name="is_ganti_password_val" id="is_ganti_password_val" value="0">
        <input id="password" type="password" class="form-control required" name="password" placeholder="Masukan Password" value="{{$data_->password}}" autocomplete="'off'">
        @else 
        {!! Form::label('password', 'Password') !!}
        {!! Form::text('password', $data_->password, ['class' => 'form-control required', 'placeholder' => 'Masukan Password', 'autocomplete' => 'off']) !!}
        @endif
    </div>
    <div class="form-group col-md-6">
        <div class="row">
            <div class="col-5 text-center">
                @if (!empty($data_->image))
                    <img src="data:image/png;base64, {{ $data_->image}}" alt="user-avatar" class="img-circle img-fluid" width="200" height="250"></a>
                @else
                    <img src="{{ asset('img/user-icon.png') }}" width="200" height="250">
                @endif
            </div>
            <div class="col-7">
                {!! Form::label('img', 'Foto Dokter') !!} <br>
                {!! Form::file('img', null, ['id' => 'img', 'type' => 'file', 'class' => 'form-control required', 'autocomplete' => 'off']) !!} 
                <div class="well well-sm no-shadow">
                    <span style="font-size: 10pt;" class="text-red">| Ukuran : 4 x 6 cm</span>
                </div>
            </div>
        </div>
    </div>
</div>
