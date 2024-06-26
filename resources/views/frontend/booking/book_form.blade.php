<h4>Sisa book: {{$jadwal_dokter->book_max - $jadwal_dokter_count}}</h4>
<form class="validated_form" id="form-edit" method="POST" action="{{url('/book_dokter/book_post')}}" enctype="multipart/form-data">
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group col-md-12">
                                {!! Form::label('id_dokter', 'Pilih Dokter') !!}
                                <input type="hidden" name="id_jadwal" class="form-control" value="{{ $jadwal_dokter->id }}">
                                <input type="hidden" name="id_dokter" class="form-control" value="{{ $dokters->id }}">
                                <input type="text" name="id_dokter_case" class="form-control" value="{{ $dokters->nama }}" disabled>
                            </div>
                            <div class="form-group col-md-5">
                                {!! Form::label('tgl', 'Pilih Tanggal') !!}
                                {!! Form::text('tgl', $jadwal_dokter->tgl, array('type' => 'text', 'class' => 'form-control datepicker','placeholder' => 'Tanggal', 'id' => 'tgl', 'disabled' => true)) !!}
                            </div>
                            <div class="form-group col-md-5">
                                {!! Form::label('id_sesi', 'Pilih Sesi') !!}
                                {!! Form::select('id_sesi', $sesi_dokter, $jadwal_dokter->id_sesi, ['class' => 'form-control required', 'disabled' => true]) !!}
                            </div>
                            <div class="form-group col-md-5">
                                <label>Start / Jam Datang</label>
                                <div class="input-group">
                                    <div class="input-group-addon">
                                        <i class="fa fa-clock-o"></i>
                                    </div>
                                    <input disabled type="time" id="start" name="start" class="form-control" data-inputmask='"mask": "99:99:99"' data-mask value="{{$jadwal_dokter->start}}">
                                </div>
                            </div>
                            <div class="form-group col-md-5">
                                <label>End / Jam Pulang</label>
                                <div class="input-group">
                                    <div class="input-group-addon">
                                        <i class="fa fa-clock-o"></i>
                                    </div>
                                    <input disabled type="time" id="end" name="end" class="form-control" data-inputmask='"mask": "99:99:99"' data-mask value="{{$jadwal_dokter->end}}">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group col-md-12">
                                {!! Form::label('id_reg_pasien', 'Pilih Anggota Keluarga') !!}
                                {!! Form::select('id_reg_pasien', $anggota_keluarga, session('id'), ['class' => 'form-control required']) !!}
                            </div>
                            <div class="form-group col-md-12">
                                <div class="form-group">
                                    <label for="keluhan">Keluhan</label><br>
                                    <textarea class="form-control" id="id_keluhan" name="keluhan"></textarea>
                                </div>
                            </div>
                            <div class="form-group col-md-12">
                                <div class="form-group">
                                    <label for="alergi">Alergi</label><br>
                                    <textarea class="form-control" id="id_keluhan" name="alergi"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-success" type="submit" data-toggle="tooltip" data-placement="top" title="Simpan"><i class="fa fa-save"></i> Jadwalkan</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-undo"></i>Kembali</button>
                </div>
            </div>
        </div>
    </div>
</form>
<style type="text/css">
    #tb_pertanyaan_filter{
    display: none;
    }
</style>
{!! Form::close() !!}
@section('script')
	@include('rekammedis.frontend.booking._form_js')
@endsection
