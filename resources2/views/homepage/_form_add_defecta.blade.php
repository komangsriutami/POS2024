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

                        <div class="col-md-12">

                            <h3>

                                {!! Form::label('obat', $obat->nama) !!}

                            </h3>

                        </div>

                        <div class="form-group col-md-12">

                            {!! Form::label('jumlah_diajukan', 'Kuantitas') !!}

                            {!! Form::text('jumlah_diajukan', $defecta->jumlah_diajukan, array('id' => 'jumlah_diajukan', 'class' => 'form-control', 'placeholder' => 'Kuantitas')) !!}

                        </div>

                        <div class="form-group col-md-12">

                            {!! Form::label('id_satuan', 'Satuan') !!}

                            {!! Form::select('id_satuan', $satuans, $obat->id_satuan, ['class' => 'form-control input_select required']) !!}

                        </div>

                        <div class="form-group col-md-12">

                            @if($suplier->id != null) 

                                {!! Form::label('id_suplier_order', 'Suplier') !!}

                                {!! Form::select('id_suplier_order', $supliers, $obat->id_suplier_order, ['class' => 'form-control input_select required']) !!}

                            @else

                                {!! Form::label('id_suplier_order', 'Suplier') !!}

                                {!! Form::select('id_suplier_order', $supliers, $obat->id_suplier_order, ['class' => 'form-control input_select required']) !!}

                            @endif

                        </div>

                        <div class="form-group col-md-12">

                            {!! Form::label('komentar', 'Komentar') !!}

                            {!! Form::text('komentar', $defecta->komentar, array('id' => 'komentar','class' => 'form-control', 'placeholder'=>'Komentar atau catatan')) !!}

                        </div>

                        <div class="row m-0 w-100">

                            <div class="d-flex justify-content-center pt-2 col-md-1"><i class="fa fa-info-circle fa-lg"></i></div>

                            <div class="col-md-11">

                                @if(count($referensi) != 0)
                                
                                    @foreach($referensi as $data)

                                        @if($loop->iteration <= 3)
                                        
                                            <label>{{ $loop->iteration }}. {{ $data->nama }}:</label> Rp. {{ intval($data->harga_beli_ppn) }} ( {{ $data->tgl_nota}} )<br>

                                        @endif
                                        
                                    @endforeach

                                @else

                                    <span class="text-danger"><b>Belum ada rekaman pembelian</b></span><br>

                                    <span class="text-info"><b>Pembelian Outlet Lain</b></span><br>

                                    @foreach($referensis as $data)

                                        @if($loop->iteration <= 3)
                                        
                                            <label>{{ $loop->iteration }}. {{ $data->nama }}:</label> Rp. {{ intval($data->harga_beli_ppn) }} ({{$data->tgl_nota}})<br>

                                        @endif
                                        
                                    @endforeach
                                    
                                @endif

                                <div><hr></div>

                            </div>

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

//        $("#jumlah_diajukan").setCursorToTextEnd();



        $('.input_select').select2({});

    })

</script>