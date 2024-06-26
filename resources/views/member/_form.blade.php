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
        {!! Form::label('id_tipe_member', 'Tipe Member') !!}
        {!! Form::select('id_tipe_member', $tipe_members, $member->id_tipe_member, ['class' => 'form-control required input_select']) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('norm', 'NoRM') !!}
        {!! Form::text('norm', $member->norm, array('type' => 'text', 'class' => 'form-control required','placeholder' => 'Nomor RM', 'id' => 'norm')) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('nik', 'NIK') !!}
        {!! Form::text('nik', $member->nik, array('type' => 'text', 'class' => 'form-control required','placeholder' => 'Nomor NIK', 'id' => 'nik')) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('nama', 'Nama') !!}
        {!! Form::text('nama', $member->nama, array('class' => 'form-control required', 'placeholder'=>'Masukan Nama')) !!}
    </div>
    <div class="form-group col-md-3">
        {!! Form::label('tempat', 'Tempat Lahir') !!}
        {!! Form::text('tempat_lahir', $member->tempat_lahir, array('class' => 'form-control required', 'placeholder'=>'Masukan Tempat Lahir')) !!}
    </div>
    <div class="form-group col-md-3">
        {!! Form::label('tanggal_lahir', 'Pilih Tanggal Lahir') !!}
        {!! Form::text('tgl_lahir', $member->tgl_lahir, array('type' => 'text', 'class' => 'form-control datepicker required','placeholder' => 'Tanggal Lahir', 'id' => 'tgl_lahir')) !!}
    </div>
    <div class="form-group col-md-3">
        {!! Form::label('id_jenis_kelamin', 'Pilih Jenis Kelamin') !!}
        {!! Form::select('id_jenis_kelamin', $jenis_kelamins, $member->id_jenis_kelamin, ['class' => 'form-control required']) !!}
    </div>
    <div class="form-group col-md-3">
        {!! Form::label('id_group_apotek', 'Group Outlet') !!}
        {!! Form::select('id_group_apotek', $group_apoteks, $member->id_group_apotek, ['class' => 'form-control required input_select']) !!}
    </div>
    <div class="form-group col-md-3">
        {!! Form::label('telepon', 'Telepon') !!}
        <input type="text" value="{{ $member->telepon }}" name="telepon" id="telepon" class="form-control required datamask"
               data-inputmask="'mask': ['999999', '(999)99999999', '(999)999999999']">
    </div>
    <div class="form-group col-md-3">
        {!! Form::label('email', 'Email') !!}
        {!! Form::email('email', $member->email, array('class' => 'form-control required', 'placeholder'=>'Masukan Alamat Email')) !!}
    </div>
    <div class="form-group col-md-9">
        {!! Form::label('alamat', 'Alamat') !!}
        {!! Form::text('alamat', $member->alamat, array('class' => 'form-control required', 'placeholder'=>'Masukan Alamat')) !!}
    </div>
     <div class="form-group col-md-3">
        {!! Form::label('id_kabupaten', 'Kabupaten') !!}
        {!! Form::select('id_kabupaten', $kabupatens, $member->id_kabupaten, ['class' => 'form-control required input_select']) !!}
    </div>
</div>