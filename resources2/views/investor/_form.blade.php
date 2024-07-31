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
    <div class="form-group col-md-4">
        {!! Form::label('nama', 'Nama') !!}
        {!! Form::text('nama', $investor->nama, array('class' => 'form-control required', 'placeholder'=>'Masukan Nama')) !!}
    </div>
    <div class="form-group col-md-4">
        {!! Form::label('tgl_lahir', 'Tanggal Lahir') !!}
        {!! Form::text('tgl_lahir', $investor->tgl_lahir, array('class' => 'datepicker form-control required', 'placeholder'=>'Masukan Tanggal Lahir', 'id'=>'tgl_lahir', 'autocomplete'=>'off')) !!}
    </div>
    <div class="form-group col-md-4">
        {!! Form::label('tempat_lahir', 'Tempat Lahir') !!}
        {!! Form::text('tempat_lahir', $investor->tempat_lahir, array('class' => 'form-control required', 'placeholder'=>'Masukan Tempat Lahir')) !!}
    </div>
    <div class="form-group col-md-4">
        {!! Form::label('id_jenis_kelamin', 'Jenis Kelamin') !!}
        {!! Form::select('id_jenis_kelamin', $jenis_kelamin, $investor->id_jenis_kelamin, ['class' => 'form-control required']) !!}
    </div>
    <div class="form-group col-md-4">
        {!! Form::label('id_agama', 'Agama') !!}
        {!! Form::select('id_agama', $agama, $investor->id_agama, ['class' => 'form-control required']) !!}
    </div>
    <div class="form-group col-md-4">
        {!! Form::label('id_kewarganegaraan', 'Kewarganegaraan') !!}
        {!! Form::select('id_kewarganegaraan', $kewarganegaraan, $investor->id_kewarganegaraan, ['class' => 'form-control required']) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('nik', 'NIK') !!}
        {!! Form::text('nik', $investor->nik, array('class' => 'form-control required number nik', 'placeholder'=>'Masukan NIK')) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('npwp', 'NPWP') !!}
        {!! Form::text('npwp', $investor->npwp, array('class' => 'form-control required number npwp', 'placeholder'=>'Masukan NPWP')) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('no_telp', 'Nomor Telepon') !!}
        {!! Form::text('no_telp', $investor->no_telp, array('class' => 'form-control required', 'placeholder'=>'Masukan Nomor Telepon')) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('email', 'Email') !!}
        {!! Form::text('email', $investor->email, array('class' => 'form-control required email', 'placeholder'=>'Masukan Email')) !!}
    </div>
    <div class="form-group col-md-12">
        {!! Form::label('alamat', 'Alamat') !!}
        {!! Form::text('alamat', $investor->alamat, array('class' => 'form-control required', 'placeholder'=>'Masukan Alamat')) !!}
    </div>
</div>

@section('script')
<script type="text/javascript">
    $(document).ready(function(){
		$('#tgl_lahir').datepicker({
            autoclose:true,
            format:"yyyy-mm-dd",
            forceParse: false
        });
	})
</script>
@endsection