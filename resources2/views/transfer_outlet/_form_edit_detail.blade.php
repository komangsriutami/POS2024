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
                            {!! Form::label('harga_outlet', 'Harga(*)') !!}
                            {!! Form::text('harga_outlet', $detail->harga_outlet, array('id' => 'harga_outlet_val','class' => 'form-control required', 'placeholder'=>'Harga')) !!}
                        </div>
                        <div class="form-group col-md-2">
                            {!! Form::label('jumlah', 'Jumlah(*)') !!}
                            {!! Form::text('jumlah', $detail->jumlah, array('id' => 'jumlah_val','class' => 'form-control required', 'placeholder'=>'Jumlah Item')) !!}
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
        /*$("#harga_outlet").focus();

        $("#harga_outlet").keypress(function(event){
            if (event.which == '10' || event.which == '13') {
                $("#jumlah").focus();
                event.preventDefault();
            }
        });

        $("#jumlah").keypress(function(event){
            if (event.which == '10' || event.which == '13') {
                no = $("#no").val();
                set_detail_new(this, no);
                event.preventDefault();
            }
        });*/
    })

    function set_detail_new(obj, no){
        var harga_outlet = $("#harga_outlet_val").val();
        var jumlah = $("#jumlah_val").val();
        var total = parseFloat(harga_outlet) * parseFloat(jumlah);
        var total_rp = hitung_rp_khusus(total);
        $("#harga_outlet_"+no).val(harga_outlet);
        $("#jumlah_"+no).val(jumlah);
        $("#hitung_total_"+no).data("total", total);
        $("#hitung_total_"+no).val(total);

        $("#harga_obat_view_"+no).html(harga_outlet);
        $("#jumlah_view_"+no).html(jumlah);
        //$("#hitung_total_view_"+no).html('Rp '+total_rp);

        $("#hitung_total_view_"+no).html(total);
  
        hitung_total();
        $('#modal-xl').modal('toggle');
    }
</script>

