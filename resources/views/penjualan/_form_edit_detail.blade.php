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
                    <input type="hidden" name="id_detail" id="id_detail" value="{{ $detail->id }}">
                    <input type="hidden" name="no" id="no" value="{{ $no }}">
                    <div class="row">
                        <div class="form-group col-md-6">
                            {!! Form::label('obat', 'Nama Obat') !!}
                            {!! Form::text('obat', $detail->obat->nama, array('class' => 'form-control', 'placeholder'=>'Nama Apotek', 'readonly' => 'readonly')) !!}
                        </div>
                        <div class="form-group col-md-2">
                            {!! Form::label('harga_jual', 'Harga Jual(*)') !!}
                            {!! Form::text('harga_jual', $detail->harga_jual, array('id' => 'harga_jual','class' => 'form-control required', 'placeholder'=>'Harga Jual')) !!}
                        </div>
                        <div class="form-group col-md-2">
                            {!! Form::label('diskon', 'Diskon(*)') !!}
                            {!! Form::text('diskon', $detail->diskon, array('id' => 'diskon','class' => 'form-control required', 'placeholder'=>'Diskon')) !!}
                        </div>
                        <div class="form-group col-md-2">
                            {!! Form::label('jumlah', 'Jumlah(*)') !!}
                            {!! Form::text('jumlah', $detail->jumlah, array('id' => 'jumlah','class' => 'form-control required', 'placeholder'=>'Jumlah Item')) !!}
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-success" type="button" onClick="set_detail_new(this, {{$no}})" data-toggle="tooltip" data-placement="top" title="Simpan"><i class="fa fa-save"></i> Simpan</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-undo"></i> Kembali</button>
                </div>
            </div>
         </div>
    </div>
<script type="text/javascript">
    $(document).ready(function(){
    })

    function set_detail_new(obj, no){
        harga_jual = $("#harga_jual").val();
        diskon = $("#diskon").val();
        jumlah = $("#jumlah").val();
        total = (parseFloat(harga_jual) * parseFloat(jumlah)) - parseFloat(diskon);
        var total_rp = hitung_rp(total);
        $("#harga_jual_"+no).val(harga_jual);
        $("#jumlah_"+no).val(jumlah);
        $("#diskon_"+no).val(diskon);
        $("#hitung_total_"+no).data("total", total);
        $("#hitung_total_"+no).val(total);

        $("#harga_jual_"+no).html(harga_jual);
        $("#jumlah_"+no).html(jumlah);
        $("#diskon_"+no).html(diskon);
        $("#hitung_total_"+no).html('Rp '+total_rp);
        
        hitung_total();
        $('#modal-xl').modal('toggle');
    }
</script>

