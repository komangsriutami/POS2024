{!! Form::model(new App\DefectaOutlet(), ['route' => ['defecta.store'], 'class' => 'validated_form', 'id' => 'form-add-defecta']) !!}

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

                    <input type="hidden" name="id_status" id="id_status" value="2">

                    <input type="hidden" name="id_suplier_order" id="id_suplier_order" value="null">

                    <input type="hidden" name="id_suplier" id="id_suplier" value="null">

                    <input type="hidden" name="id_apotek" id="id_apotek" value="{{ $apotek->id }}">

                    <input type="hidden" name="id_stok_harga" id="id_stok_harga">

                    <div class="row">

                       <div class="col-md-12">

                            {!! Form::label('id_obat', 'Pilih Obat') !!}

                            {!! Form::select('id_obat', $obats, $defecta->id_obat, ['class' => 'form-control required']) !!}

                        </div>

                        <div class="form-group col-md-12">

                            {!! Form::label('jumlah_diajukan', 'Kuantitas') !!}

                            {!! Form::text('jumlah_diajukan', $defecta->jumlah_diajukan, array('id' => 'jumlah_diajukan', 'class' => 'form-control', 'placeholder' => 'Kuantitas')) !!}

                        </div>

                        <div class="form-group col-md-12">

                            {!! Form::label('id_satuan', 'Satuan') !!}

                            {!! Form::select('id_satuan', $satuans, $defecta->id_satuan, ['class' => 'form-control input_select required']) !!}

                        </div>

                        <div class="form-group col-md-12">

                            {!! Form::label('id_apotek_transfer', 'Apotek') !!}

                            {!! Form::select('id_apotek_transfer', $apoteks, $defecta->id_apotek_transfer, ['class' => 'form-control input_select required']) !!}

                        </div>

                        <div class="form-group col-md-12">

                            {!! Form::label('komentar', 'Komentar') !!}

                            {!! Form::text('komentar', $defecta->komentar, array('id' => 'komentar','class' => 'form-control', 'placeholder'=>'Komentar atau catatan')) !!}

                        </div>

                        <div class="row m-0 w-100">

                            <div class="d-flex justify-content-center pt-2 col-md-1"><i class="fa fa-info-circle fa-lg"></i></div>

                            <div class="col-md-11">

                                <div id="div_info"></div>

                                <div><hr></div>

                            </div>

                        </div>

                    </div>

                </div>

                <div class="card-footer">

                    <button class="btn btn-success" type="button" onClick="submit_manual()" data-toggle="tooltip" data-placement="top" title="Simpan"><i class="fa fa-save"></i> Simpan</button>

                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-undo"></i> Kembali</button>

                </div>

            </div>

         </div>

    </div>

{!! Form::close() !!}

<script type="text/javascript">

    $(document).ready(function(){

        $('#jumlah_diajukan').focus();

        //$("#jumlah_diajukan").setCursorToTextEnd();

        $('#id_obat').select2({
            minimumInputLength: 3 
        });

        $('#id_obat').change(function(){
            getKonten(); 
        });

        $('.input_select').select2({});

    })

    function getKonten() {
        var id_obat = $("#id_obat").val();
        var id_defecta = $("#id_defecta").val();
        $.ajax({
            type: "GET",
            url: '{{url("defecta/load_konten_transfer")}}',
            // dataType:'json',
            data: { 
                id_obat:id_obat,
                id_defecta:id_defecta
            },
            beforeSend: function(data){
                // replace dengan fungsi loading
                spinner.show();
            },
            success:  function(data){
                $("#div_info").html(data.div_info);
            },
            complete: function(data){
                spinner.hide();
            },
            error: function(data) {
                alert("error ajax occured!");
                // done_load();
            }
        });
    }

</script>