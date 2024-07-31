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
    @if(isset($fromjson))
        <input type="hidden" name="fromjson" value="{{$fromjson}}">
    @endif
    <div class="form-group col-md-6">
        {!! Form::label('nama', 'Nama') !!}
        {!! Form::text('nama', $syarat_pembayaran->nama, array('class' => 'form-control required')) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('jangka_waktu', 'Jangka Waktu') !!}
        <div class="input-group mb-3">
            <input type="number" class="form-control" name="jangka_waktu" value="{{$syarat_pembayaran->jangka_waktu}}">
            <div class="input-group-append">
                <span class="input-group-text">Hari</span>
            </div>
        </div>
    </div>
</div>
