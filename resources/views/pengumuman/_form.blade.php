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
    <div class="form-group col-md-12">
        {!! Form::label('judul', 'Judul') !!}
        {!! Form::text('judul', $data_->judul, array('class' => 'form-control required', 'placeholder'=>'Keterangan')) !!}
    </div>
    <div class="form-group col-md-4">
        {!! Form::label('id_role_penerima', 'Pilih Penerima') !!}
        {!! Form::select('id_role_penerima[]', $roles, $data_->id_role_penerima, ['class' => 'form-control input_select required', 'multiple'=>'multiple']) !!}
    </div>
    <div class="form-group col-md-2">
        <label>Pilih Asal (*)</label>
        <select class="input_select form-control required" name="id_asal_pengumuman" id="id_asal_pengumuman">
            <option value="1">Administrator</option>
            <option value="2">Manajemen</option>
            <option value="3">Kepala Outlet</option>
        </select>
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('tanggal_aktif', 'Tanggal') !!}
        {!! Form::text('tanggal_aktif', $data_->tanggal_aktif, array('class' => 'form-control required', 'placeholder'=>'Tanggal')) !!}
    </div>
    <div class="form-group col-md-12">
        {!! Form::label('isi', 'Konten/Isi Pengumuman') !!}
        {!! Form::textarea('isi', $data_->isi, array('class' => 'form-control required', 'placeholder'=>'Isian')) !!}
    </div>
    <div class="col-md-6">
        {!! Form::label('file', 'Files') !!}
        @if(!is_null($data_->file))
        <?php $id_en = Crypt::encryptString($data_->id); ?>
        <?php $jenis_encrypt = Crypt::encryptString('pengumuman'); ?>
        <?php $filename = Crypt::encryptString($data_->file); ?>
        <a href={{ url("fileaccess") }}/{{$id_en}}/{{ $jenis_encrypt }}/{{ $filename }} target="_blank">[ FILE ]</a>
        @endif
        <input type="file" name="file" id="file" class="form-control max_file_size" accept="image/x-png,image/jpeg,image/jpg,image/png,application/pdf" onchange="return validationTypeImagePdf('file')">
        <div class="well well-sm no-shadow bg-navy">
            <font>Extensi file : .png, .jpg, .jpeg, .pdf &nbsp; | Maksimal Upload File : 2 MB</font>
        </div>
    </div>
</div>