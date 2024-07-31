{!! Form::model($obat, ['method' => 'PUT', 'class'=>'validated_form', 'id'=>'form-edit-harga', 'route' => ['obat.update_harga', $obat->id]]) !!}
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <div class="card-body">
                    <div class="row">
                        <input type="hidden" name="id" id="id" value="{{ $obat->id }}">
                        <input type="hidden" name="id_asal" id="id_asal" value="{{ $id_asal }}">
                        <div class="form-group col-sm-3">
                            {!! Form::label('barcode', 'Barcode') !!}
                            {!! Form::text('barcode', $obat->barcode, array('class' => 'form-control required', 'placeholder'=>'Masukan Barcode', 'id' => 'barcode', 'readonly' => 'readonly')) !!}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('nama', 'Nama Obat') !!}
                            {!! Form::text('nama', $obat->nama, array('class' => 'form-control required', 'placeholder'=>'Masukan Nama', 'readonly' => 'readonly')) !!}
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
                            {!! Form::label('harga_beli', 'Harga Beli') !!}
                            <div class="input-group"> 
                                <div class="input-group-prepend">
                                  <span class="input-group-text">Rp</span>
                                </div>
                                {!! Form::text('harga_beli', $harga_beli, array('class' => 'form-control required number', 'placeholder'=>'Masukan Harga Beli', 'readonly' => 'readonly')) !!}
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            {!! Form::label('harga_beli_ppn', 'Harga Beli + PPN') !!}
                            <div class="input-group"> 
                                <div class="input-group-prepend">
                                  <span class="input-group-text">Rp</span>
                                </div>
                                {!! Form::text('harga_beli_ppn', $harga_beli_ppn, array('id' => 'harga_beli_ppn', 'class' => 'form-control required number', 'placeholder'=>'Masukan Harga Beli ppn', 'readonly' => 'readonly')) !!}
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            {!! Form::label('harga_jual', 'Harga Jual') !!}
                            <div class="input-group"> 
                                <div class="input-group-prepend">
                                  <span class="input-group-text">Rp</span>
                                </div>
                                {!! Form::text('harga_jual', $obat->harga_jual, array('id' => 'harga_jual', 'class' => 'form-control required number', 'placeholder'=>'Masukan Harga Jual')) !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-success btn-sm" type="button" onClick="submit_valid({{$obat->id}})" data-toggle="tooltip" data-placement="top" title="Simpan"><i class="fa fa-save"></i> Simpan</button>
                    <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal"><i class="fa fa-undo"></i> Kembali</button>
                </div>
            </div>
         </div>
    </div>
<script type="text/javascript">

    $(document).ready(function(){
        post_this();
        var harga_jual = $('#harga_jual').val();        
        $('#harga_jual').focus().val('').val(harga_jual);   

        $("#harga_jual").keypress(function(event){
            if (event.which == '10' || event.which == '13') {
                var id = $("#id").val();
                submit_valid(id);
            }
        });
    })
</script>
{!! Form::close() !!}
