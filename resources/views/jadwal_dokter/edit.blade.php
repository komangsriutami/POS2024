{!! Form::model($jadwal_dokter, ['method' => 'PUT', 'class'=>'validated_form','id'=>'form-edit', 'route' => ['jadwal_dokter.update', $jadwal_dokter->id]]) !!}
<form class="validated_form" id="form-edit" enctype="multipart/form-data">
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="card-body">
                    <div class="form-group col-md-12">
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
                    <div class="form-group col-md-3">
                        {!! Form::label('tgl', 'Pilih Tanggal') !!}
                        {!! Form::text('tgl', $jadwal_dokter->tgl, array('type' => 'text', 'class' => 'form-control datepicker','placeholder' => 'Tanggal', 'id' => 'tgl', 'disabled' => !$userEdit)) !!}
                    </div>
                    <div class="form-group col-md-3">
                        {!! Form::label('id_sesi', 'Pilih Sesi') !!}
                        {!! Form::select('id_sesi', $sesi_dokter, $jadwal_dokter->id_sesi, ['class' => 'form-control required', 'disabled' => !$userEdit]) !!}
                    </div>
                    <div class="form-group col-md-3">
                        <label>Start / Jam Datang</label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-clock-o"></i>
                            </div>
                            <input @if(!$userEdit) disabled @endif type="time" id="start" name="start" class="form-control" data-inputmask='"mask": "99:99:99"' data-mask value="{{$jadwal_dokter->start}}">
                        </div>
                    </div>
                    <div class="form-group col-md-3">
                        <label>End / Jam Pulang</label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-clock-o"></i>
                            </div>
                            <input @if(!$userEdit) disabled @endif type="time" id="end" name="end" class="form-control" data-inputmask='"mask": "99:99:99"' data-mask value="{{$jadwal_dokter->end}}">
                        </div>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Jumlah Book</label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-clock-o"></i>
                            </div>
                            <input @if(!$userEdit) disabled @endif type="number" min="1" name="book_max" class="form-control" value="{{$jadwal_dokter->book_max}}">
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    @if($userEdit)
                        <button class="btn btn-success" type="button" onClick="submit_valid({{$jadwal_dokter->id}})" data-toggle="tooltip" data-placement="top" title="Simpan"><i class="fa fa-save"></i> Simpan</button>
                        <button class="btn btn-warning text-white" type="button" onClick="submit_valid_delete({{$jadwal_dokter->id}})" data-toggle="tooltip" data-placement="top" title="Delete"><i class="fa fa-save"></i> Delete</button>
                    @endif
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
@include('jadwal_dokter._form_js')
