<!--
Model : Layout Form Pasien
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
    <input type="hidden" name="id" id="id" value="{{$data_->id}}">
    <div class="form-group col-md-2">
        {!! Form::label('is_pernah_berobat', 'Pernah Berobat ? (*)') !!}
        <select id="is_pernah_berobat" name="is_pernah_berobat" class="form-control required input_select">
            <option value="">-- Pilih Status --</option>
            <option value="1" {{ 1 == $data_->is_pernah_berobat ? 'selected="selected"' : '' }}>Ya</option>
            <option value="2" {{ 2 == $data_->is_pernah_berobat ? 'selected="selected"' : '' }}>Tidak</option>
        </select>
    </div>
    <div class="form-group col-md-4">
        {!! Form::label('nik', 'Nomor Induk Kependudukan (*)') !!}
        {!! Form::text('nik', $data_->nik, ['class' => 'form-control required', 'placeholder' => 'Masukan Nomor Induk Kependudukan', 'autocomplete' => 'off']) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('nama', 'Nama (*)') !!}
        {!! Form::text('nama', $data_->nama, ['class' => 'form-control required', 'placeholder' => 'Masukan Nama Lengkap', 'autocomplete' => 'off']) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('is_bpjs', 'Punya BPJS ? (*)') !!}
        <select id="is_bpjs" name="is_bpjs" class="form-control required input_select">
            <option value="">-- Pilih Status --</option>
            <option value="1" {{ 1 == $data_->is_bpjs ? 'selected="selected"' : '' }}>Ya</option>
            <option value="2" {{ 2 == $data_->is_bpjs ? 'selected="selected"' : '' }}>Tidak</option>
        </select>
    </div>
    <div class="form-group col-md-4 adabpjsform bpjsform">
        {!! Form::label('no_bpjs', 'Nomor BPJS') !!}
        {!! Form::text('no_bpjs', $data_->no_bpjs, ['class' => 'form-control', 'placeholder' => 'Masukan Nomor BPJS', 'autocomplete' => 'off']) !!}
    </div>
    <div class="form-group col-md-3">
        {!! Form::label('tempat_lahir', 'Tempat Lahir (*)') !!}
        {!! Form::text('tempat_lahir', $data_->tempat_lahir, ['class' => 'form-control required', 'placeholder' => 'Masukan Tempat Lahir', 'autocomplete' => 'off']) !!}
    </div>
    <div class="form-group col-md-3">
        {!! Form::label('tgl_lahir', 'Tanggal Lahir (*)') !!}
        {!! Form::text('tgl_lahir', $data_->tgl_lahir, ['type' => 'text', 'class' => 'form-control datepicker required', 'placeholder' => 'Tanggal Lahir', 'id' => 'tgl_lahir', 'autocomplete' => 'off']) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('id_jenis_kelamin', 'Jenis Kelamin (*)') !!}
        {!! Form::select('id_jenis_kelamin', $jenis_kelamins, $data_->id_jenis_kelamin, ['class' => 'form-control required input_select', 'autocomplete' => 'off']) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('id_kewarganegaraan', 'Kewarganegaraan (*)') !!}
        {!! Form::select('id_kewarganegaraan', $kewarganegaraans, $data_->id_kewarganegaraan, ['class' => 'form-control required input_select', 'autocomplete' => 'off']) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('id_agama', 'Agama (*)') !!}
        {!! Form::select('id_agama', $agamas, $data_->id_agama, ['class' => 'form-control required input_select', 'autocomplete' => 'off']) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('id_golongan_darah', 'Golongan Darah (*)') !!}
        {!! Form::select('id_golongan_darah', $gol_darahs, $data_->id_golongan_darah, ['class' => 'form-control required input_select', 'autocomplete' => 'off']) !!}
    </div>
    <div class="form-group col-md-4">
        {!! Form::label('pekerjaan', 'Pekerjaan (*)') !!}
        {!! Form::text('pekerjaan', $data_->pekerjaan, ['class' => 'form-control required', 'placeholder' => 'Masukan Pekerjaan', 'autocomplete' => 'off']) !!}
    </div>
    <div class="form-group col-md-12">
        {!! Form::label('alamat', 'Alamat (*)') !!}
        {!! Form::text('alamat', $data_->alamat, ['class' => 'form-control required', 'placeholder' => 'Masukan Alamat', 'autocomplete' => 'off']) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('telepon', 'Telepon (*)') !!}
        {!! Form::text('telepon', $data_->telepon, ['class' => 'form-control required', 'placeholder' => 'Masukan Telepon', 'autocomplete' => 'off']) !!}
    </div>
    <div class="form-group col-md-4">
        {!! Form::label('email', 'Email') !!}
        {!! Form::text('email', $data_->email, ['class' => 'form-control', 'placeholder' => 'Masukan Email', 'autocomplete' => 'off']) !!}
    </div>
    <div class="form-group col-md-6">
        @if($data_->password != null)
        <label><input type="checkbox" name="is_ganti_password" id="is_ganti_password" value="1"> Klik untuk ganti password </label>
        <input type="hidden" name="is_ganti_password_val" id="is_ganti_password_val" value="0">
        <input id="password" type="password" class="form-control required" name="password" placeholder="Masukan Password" value="{{$data_->password}}" autocomplete="'off's">
        @else 
        {!! Form::label('password', 'Password') !!}
        {!! Form::text('password', $data_->password, ['class' => 'form-control', 'placeholder' => 'Masukan Password', 'autocomplete' => 'off']) !!}
        @endif
    </div>
    <div class="form-group col-md-12">
        {!! Form::label('alergi_obat', 'Alergi (*)') !!}
        {!! Form::textarea('alergi_obat', $data_->alergi_obat, ['class' => 'form-control required', 'placeholder' => 'Masukan Alergi', 'autocomplete' => 'off']) !!}
    </div>
</div>
