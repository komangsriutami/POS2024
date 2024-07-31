{!! Form::model($defecta, ['method' => 'PUT', 'class'=>'validated_form', 'id'=>'form-edit', 'route' => ['defecta.update', $data_->id]]) !!}
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
                    <input type="hidden" name="id_defecta" id="id_defecta" value="{{ $defecta->id }}">
                    <input type="hidden" name="id_status" id="id_status" value="1">
                    <input type="hidden" name="id_apotek" id="id_apotek" value="{{ $apotek->id }}">
                    <input type="hidden" name="id_obat" id="id_obat" value="{{ $obat->id }}">
                    <input type="hidden" name="id_stok_harga" id="id_stok_harga" value="{{ $data_->id }}">
                    <div class="row">
                        <div class="form-group col-md-4">
                            {!! Form::label('apotek', 'Apotek') !!}
                            {!! Form::text('apotek', $apotek->nama_panjang, array('class' => 'form-control', 'placeholder'=>'Nama Apotek', 'readonly' => 'readonly')) !!}
                        </div>
                        <div class="form-group col-md-8">
                            {!! Form::label('obat', 'Nama Obat') !!}
                            {!! Form::text('obat', $obat->nama, array('class' => 'form-control', 'placeholder'=>'Nama Apotek', 'readonly' => 'readonly')) !!}
                        </div>
                        <div class="form-group col-md-2">
                            {!! Form::label('golongan', 'Golongan Obat') !!}
                            {!! Form::text('golongan', $obat->golongan_obat->keterangan, array('class' => 'form-control', 'placeholder'=>'Golongan Obat', 'readonly' => 'readonly')) !!}
                        </div>
                        <div class="form-group col-md-4">
                            {!! Form::label('penandaan_obat', 'Penandaan Obat') !!}
                            {!! Form::text('penandaan_obat', $obat->penandaan_obat->nama, array('class' => 'form-control', 'placeholder'=>'Penandaan Obat', 'readonly' => 'readonly')) !!}
                        </div>
                        <div class="form-group col-md-2">
                            {!! Form::label('satuan', 'Satuan') !!}
                            {!! Form::text('satuan', $obat->satuan->satuan, array('class' => 'form-control', 'placeholder'=>'Satuan Obat', 'readonly' => 'readonly')) !!}
                        </div>
                        <div class="form-group col-md-1">
                            {!! Form::label('jumlah_penjualan', 'Penjualan') !!}
                            {!! Form::text('jumlah_penjualan', $jumlah_pemakaian, array('id' => 'jumlah_penjualan','class' => 'form-control required', 'placeholder'=>'Jumlah Penjualan', 'readonly' => 'readonly')) !!}
                        </div>
                        <div class="form-group col-md-2">
                            {!! Form::label('margin', 'Margin') !!}
                            {!! Form::text('margin', $margin, array('id' => 'margin','class' => 'form-control required', 'placeholder'=>'Margin Penjualan', 'readonly' => 'readonly')) !!}
                        </div>
                        <div class="form-group col-md-1">
                            {!! Form::label('stok', 'Stok') !!}
                            {!! Form::text('stok', $data_->stok_akhir, array('id' => 'stok','class' => 'form-control required', 'placeholder'=>'Stok', 'readonly' => 'readonly')) !!}
                        </div>
                        <input type="hidden" name="buffer" id="buffer" value="0">
                        <input type="hidden" name="forcasting" id="forcasting" value="0">
                        <!-- <div class="form-group col-md-1">
                            {!! Form::label('buffer', 'Buffer') !!}
                            {!! Form::text('buffer', $data_->total_buffer, array('id' => 'buffer','class' => 'form-control required', 'placeholder'=>'Buffer', 'readonly' => 'readonly')) !!}
                        </div>
                        <div class="form-group col-md-1">
                            {!! Form::label('forcasting', 'Forcasting') !!}
                            {!! Form::text('forcasting', $data_->forcasting, array('id' => 'forcasting','class' => 'form-control required', 'placeholder'=>'Forcasting', 'readonly' => 'readonly')) !!}
                        </div> -->
                        <div class="form-group col-md-2">
                            {!! Form::label('jumlah_diajukan', 'Jumlah  Diajukan(*)') !!}
                            {!! Form::text('jumlah_diajukan', $defecta->jumlah_diajukan, array('id' => 'jumlah_diajukan','class' => 'form-control required', 'placeholder'=>'Jumlah Diajukan')) !!}
                        </div>
                        <div class="form-group col-md-12">
                            {!! Form::label('komentar', 'Komentar') !!}
                            {!! Form::text('komentar', $defecta->komentar, array('id' => 'komentar','class' => 'form-control', 'placeholder'=>'Komentar atau catatan')) !!}
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-success" type="button" onClick="submit_valid({{$data_->id}})" data-toggle="tooltip" data-placement="top" title="Simpan"><i class="fa fa-save"></i> Simpan</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-undo"></i> Kembali</button>
                </div>
            </div>
         </div>
    </div>
{!! Form::close() !!}
<script type="text/javascript">
    $(document).ready(function(){
        $('#jumlah_diajukan').focus();
        $("#jumlah_diajukan").setCursorToTextEnd();
    })
</script>


