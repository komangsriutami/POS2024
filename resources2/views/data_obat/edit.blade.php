{!! Form::model($obat, ['method' => 'PUT', 'class'=>'validated_form', 'id'=>'form-edit', 'route' => ['data_obat.update', $obat->id]]) !!}
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <div class="card-body">
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
                        <div class="form-group col-sm-3">
                            {!! Form::label('barcode', 'Barcode') !!}
                            {!! Form::text('barcode', $obat->barcode, array('class' => 'form-control required', 'placeholder'=>'Masukan Barcode', 'id' => 'barcode', 'readonly' => 'readonly')) !!}
                        </div>
                        <div class="form-group col-md-9">
                            {!! Form::label('nama', 'Nama Obat') !!}
                            {!! Form::text('nama', $obat->nama, array('class' => 'form-control required', 'placeholder'=>'Masukan Nama', 'readonly' => 'readonly')) !!}
                        </div>
                        <div class="form-group col-md-2">
                            {!! Form::label('id_satuan', 'Pilih Satuan') !!}
                            {!! Form::select('id_satuan', $satuans, $obat->id_satuan, ['class' => 'form-control input_select required', 'disabled' => 'disabled']) !!}
                        </div>
                        <div class="form-group col-md-3">
                            {!! Form::label('id_penandaan_obat', 'Pilih Penandaan Obat') !!}
                            {!! Form::select('id_penandaan_obat', $penandaan_obats, $obat->id_penandaan_obat, ['class' => 'form-control input_select required', 'disabled' => 'disabled']) !!}
                        </div>
                        <div class="form-group col-md-3">
                            {!! Form::label('id_golongan_obat', 'Pilih Golongan Penandaan Diskon') !!}
                            {!! Form::select('id_golongan_obat', $golongan_obats, $obat->id_golongan_obat, ['class' => 'form-control input_select required', 'disabled' => 'disabled']) !!}
                        </div>
                        <div class="form-group col-md-4">
                            {!! Form::label('id_produsen', 'Pilih Produsen') !!}
                            {!! Form::select('id_produsen', $produsens, $obat->id_produsen, ['class' => 'form-control input_select required', 'disabled' => 'disabled']) !!}
                        </div>
                        
                        <div class="form-group col-md-3">
                            {!! Form::label('rak', 'Rak') !!}
                            {!! Form::text('rak', $obat->rak, array('class' => 'form-control required', 'placeholder'=>'Masukan Nama Rak', 'readonly' => 'readonly')) !!}
                        </div>
                        <div class="form-group col-md-3">
                            {!! Form::label('isi_tab', 'Isi /Box') !!}
                            <div class="input-group">
                                <div class="input-group-prepend">
                                  <span class="input-group-text">@</span>
                                </div>
                                {!! Form::text('isi_tab', $obat->isi_tab, array('class' => 'form-control required number', 'placeholder'=>'Masukan Isi tab /box', 'readonly' => 'readonly')) !!}
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            {!! Form::label('isi_strip', 'Isi /Strip') !!}
                            <div class="input-group">
                                <div class="input-group-prepend">
                                  <span class="input-group-text">@</span>
                                </div>
                                {!! Form::text('isi_strip', $obat->isi_strip, array('class' => 'form-control required number', 'placeholder'=>'Masukan Isi strip /box', 'readonly' => 'readonly')) !!}
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            {!! Form::label('untung_jual', 'Untung Jual') !!}
                            <div class="input-group">
                                {!! Form::text('untung_jual', $obat->untung_jual, array('class' => 'form-control required number', 'placeholder'=>'Masukan Untung Jual', 'readonly' => 'readonly')) !!}
                                <div class="input-group-prepend">
                                  <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            {!! Form::label('untung_klinik', 'Untung Klinik') !!}
                            <div class="input-group"> 
                                {!! Form::text('untung_klinik', $obat->untung_klinik, array('class' => 'form-control required number', 'placeholder'=>'Masukan Untung Klinik', 'readonly' => 'readonly')) !!}
                                <div class="input-group-prepend">
                                  <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            {!! Form::label('untung_dokter', 'Untung Dokter') !!}
                            <div class="input-group"> 
                                {!! Form::text('untung_dokter', $obat->untung_dokter, array('class' => 'form-control required number', 'placeholder'=>'Masukan Untung Dokter', 'readonly' => 'readonly')) !!}
                                <div class="input-group-prepend">
                                  <span class="input-group-text">%</span>
                                </div>
                            </div> 
                        </div>
                        <div class="form-group col-md-3">
                            {!! Form::label('harga_beli', 'Harga Beli') !!}
                            <div class="input-group"> 
                                <div class="input-group-prepend">
                                  <span class="input-group-text">Rp</span>
                                </div>
                                {!! Form::text('harga_beli', $outlet->harga_beli, array('class' => 'form-control required number', 'placeholder'=>'Masukan Harga Beli', 'readonly' => 'readonly')) !!}
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            {!! Form::label('harga_jual', 'Harga Jual') !!}
                            <div class="input-group"> 
                                <div class="input-group-prepend">
                                  <span class="input-group-text">Rp</span>
                                </div>
                                {!! Form::text('harga_jual', $outlet->harga_jual, array('id' => 'harga_jual', 'class' => 'form-control required number', 'placeholder'=>'Masukan Harga Jual')) !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-success" type="button" onClick="submit_valid({{$obat->id}})" data-toggle="tooltip" data-placement="top" title="Simpan"><i class="fa fa-save"></i> Simpan</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-undo"></i> Kembali</button>
                </div>
            </div>
         </div>
    </div>
{!! Form::close() !!}
@include('obat/_form_js')
