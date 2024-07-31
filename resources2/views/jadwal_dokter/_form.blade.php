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
    <div class="col-md-4">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">Buat Jadwal</h3>
            </div>
            <div class="box-body">
                <div class="form-group col-md-12">
                    {{-- {{ session('user_roles.0.id') }} --}}
                    @if (session('user_roles.0.id')==7)
                        {!! Form::label('id_dokter', 'Nama Dokter') !!}
                        {{-- {!! Form::text('id_dokter', $dokters, $jadwal_dokter->id_dokter, ['class' => 'form-control required', 'value' => Auth::guard('dokter')->user()->id]) !!} --}}
                        <input type="hidden" name="id_dokter" class="form-control" value="{{ Auth::guard('dokter')->user()->id }}">
                        <input type="text" name="id_dokter_case" class="form-control" value="{{ Auth::guard('dokter')->user()->nama }}" disabled>
                    @else
                        {!! Form::label('id_dokter', 'Pilih Dokter') !!}
                        {!! Form::select('id_dokter', $dokters, $jadwal_dokter->id_dokter, ['class' => 'form-control required']) !!}
                    @endif
                </div>
                <div class="form-group col-md-12">
                    {!! Form::label('tgl', 'Pilih Tanggal') !!}
                    {!! Form::text('tgl', $jadwal_dokter->tgl, array('type' => 'text', 'class' => 'form-control datepicker','placeholder' => 'Tanggal', 'id' => 'tgl', 'autocomplete'=>'off')) !!}
                </div>
                <div class="form-group col-md-12">
                    {!! Form::label('id_sesi', 'Pilih Sesi') !!}
                    {!! Form::select('id_sesi', $sesi_dokter, $jadwal_dokter->id_sesi, ['class' => 'form-control required']) !!}
                </div>
                <div class="form-group col-md-12">
                    <label>Start / Jam Datang</label>
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-clock-o"></i>
                        </div>
                        <input type="time" step="2" id="start" name="start" class="form-control">
                    </div>
                </div>
                <div class="form-group col-md-12">
                    <label>End / Jam Pulang</label>
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-clock-o"></i>
                        </div>
                        <input type="time" step="2" id="end" name="end" class="form-control">
                    </div>
                </div>
                <div class="form-group col-md-12">
                    <label>Jumlah Book</label>
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-clock-o"></i>
                        </div>
                        <input type="number" min="1" name="book_max" class="form-control">
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <div class="form-group action-group">
                            <button class="btn btn-primary" type="submit" data-toggle="tooltip" data-placement="top" title="Simpan"><i class="fa fa-save"></i> Simpan</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">Detail Calender</h3>
            </div>
            <div class="box-body no-padding">
                <div class="col-lg-6 form-group">
                    <label>Dokter</label>
                    {!! Form::select('id_dokter_pilih', $dokters, null, ['id' => 'id_dokter_pilih', 'class' => 'form-control']) !!}
                </div>
                <div class="col-lg-12 form-group">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
</div>
