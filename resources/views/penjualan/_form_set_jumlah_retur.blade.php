<div class="row">
    <div class="col-sm-12">
        <div class="card card-info card-outline">
            <div class="card-body">
                <div class="row">
                    <input type="hidden" name="id_detail" id="id_detail" value="{{ $detail_penjualan->id }}">
                    <input type="hidden" name="alasan" id="alasan" value="">
                    <div class="form-group col-md-3">
                    {!! Form::label('id_obat', 'Kode Obat | Shift') !!}
                    <div class="input-group">
                        {!! Form::hidden('id_obat', $detail_penjualan->id_obat, array('id' => 'id_obat', 'class' => 'form-control', 'placeholder'=>'Masukan Obat')) !!}
                        {!! Form::text('barcode', $detail_penjualan->obat->barcode, array('id' => 'barcode', 'class' => 'form-control', 'placeholder'=>'Masukan Barcode', 'readonly' => 'readonly')) !!}
                    </div>
                </div>
                <div class="form-group col-md-9">
                    {!! Form::label('id_obat', 'Nama Obat') !!}
                    {!! Form::text('nama_obat', $detail_penjualan->obat->nama, array('id' => 'nama_obat', 'class' => 'form-control', 'placeholder'=>'Nama Obat', 'readonly' => 'readonly')) !!}
                </div>
                <div class="form-group col-md-3">
                    {!! Form::label('harga_jual', 'Harga Jual') !!}
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Rp</span>
                        </div>
                        {!! Form::text('harga_jual', $detail_penjualan->harga_jual, array('id' => 'harga_jual', 'class' => 'form-control', 'placeholder'=>'Harga Jual', 'readonly' => 'readonly')) !!}
                    </div>
                </div>
                <div class="form-group col-md-3">
                    {!! Form::label('diskon', 'Diskon') !!}
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Rp</span>
                        </div>
                        {!! Form::text('diskon', $detail_penjualan->diskon, array('id' => 'diskon', 'class' => 'form-control', 'placeholder'=>'Diskon', 'readonly' => 'readonly')) !!}
                    </div>
                </div>
                <div class="form-group col-md-3">
                    {!! Form::label('jumlah', 'Jumlah') !!}
                    <div class="input-group">
                        {!! Form::text('jumlah', $detail_penjualan->jumlah, array('id' => 'jumlah', 'class' => 'form-control', 'placeholder'=>'Jumlah', 'readonly' => 'readonly')) !!}
                    </div>
                </div>
                <div class="form-group col-md-3">
                    {!! Form::label('jumlah_cn', 'Jumlah Retur (*)') !!}
                    <input type="hidden" name="jumlah_cn_sebelumnya" id="jumlah_cn_sebelumnya" value="{{ $detail_penjualan->jumlah_cn }}">
                    <div class="input-group">
                        {!! Form::text('jumlah_cn', null, array('id' => 'jumlah_cn', 'class' => 'form-control required', 'placeholder'=>'Jumlah Retur')) !!}
                    </div>
                </div>
                <div class="form-group col-md-4">
                    {!! Form::label('id_alasan_retur', 'Alasan Retur (*)') !!}
                    {!! Form::select('id_alasan_retur', $alasan_returs, $detail_penjualan->id_alasan_retur, ['class' => 'form-control input_select required']) !!}
                </div>
                <div class="form-group col-md-8">
                    {!! Form::label('alasan_lain', 'Alasan Lainnya') !!}
                    {!! Form::text('alasan_lain', $detail_penjualan->alasan_lain, array('id' => 'alasan_lain', 'class' => 'form-control', 'placeholder'=>'Alasan lainnya')) !!}
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="form-group col-md-12">
                            <button class="btn btn-success btn-sm" type="button" onClick="set_data(this)" data-toggle="tooltip" data-placement="top" title="Simpan"><i class="fa fa-save"></i> Simpan</button>
                            <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal"><i class="fa fa-undo"></i> Kembali</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function(){
        $("#jumlah_cn").val('');
        $("#jumlah_cn").focus();

        $('.input_select').select2();

        $("#jumlah_cn").keypress(function(event){
            if (event.which == '10' || event.which == '13') {
                $('#id_alasan_retur').select2('open');
                event.preventDefault();
            }
        });

        $('#id_alasan_retur').on('select2:select', function (e) {
            alasan = $(this).find(':selected').html();
            $("#alasan").val(alasan);
            $("#alasan_lain").focus();
        });

        $("#alasan_lain").keypress(function(event){
            if (event.which == '10' || event.which == '13') {
                set_data(this);
            }
        });
    })

    function set_data(obj){
        var token = "{{csrf_token()}}";
        var id_detail = $("#id_detail").val();
        var jumlah = $("#jumlah").val();
        var jumlah_cn = $("#jumlah_cn").val();
        var jumlah_cn_sebelumnya = $("#jumlah_cn_sebelumnya").val();
        var alasan = $("#alasan").val();
        var alasan_lain = $("#alasan_lain").val();
        var id_alasan_retur = $("#id_alasan_retur").val();

        var cek = parseFloat(jumlah)-(parseFloat(jumlah_cn_sebelumnya) + parseFloat(jumlah_cn));
        if(cek < 0) {
            show_error("Jumlah item yang diretur melebihi jumlah item penjualan!");
            return false;
        } else {
            //alert(token);
            var myformData = new FormData();        
            myformData.append('jumlah_cn', jumlah_cn);
            myformData.append('id_alasan_retur', id_alasan_retur);
            myformData.append('alasan_lain', alasan_lain);
            myformData.append('_token', token);

            $.ajax({
                type:"POST",
                url : '{{url("penjualan/update_retur")}}/'+id_detail,
                dataType : "json",
                data : myformData,
                processData: false,
                contentType: false,
                beforeSend: function(data){
                    // replace dengan fungsi loading
                },
                success:  function(data){
                    if(data.status == 1){
                        show_info(data.message);
                        unHideRetur(1);
                        $('#modal-xl').modal("hide");

                    }else{
                        show_error(data.message);
                        return false;
                    }
                },
                complete: function(data){
                    tb_nota_penjualan.fnDraw(false);
                    tb_penjualan_retur.fnDraw(false);
                },
                error: function(data) {
                    show_error("error ajax occured!");
                }

            });

            /*$.ajax({
               url: '{{url("penjualan/update_retur")}}/'+id,
               type: 'POST',
               data: myformData,
               processData: false,
               contentType: false,
               enctype: 'multipart/form-data',
               success: function(data) {
                console.log(data);
                    if(data.status == 1){
                        tb_nota_penjualan.fnDraw(false);
                        show_info(data.message);
                        $('#modal-xl').modal("hide");
                    }else{
                        show_error(data.message);
                        return false;
                    }
               },
               error: function(data) {
                    show_error("error ajax occured!");
                }
            });*/
        }
    }
</script>