    {!! Form::model($order, ['class'=>'validated_form', 'id'=>'form-edit-order']) !!} 
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
                    <input type="hidden" name="id_detail_order" id="id_detail_order" value="{{ $detailOrder->id }}">
                    <input type="hidden" name="id_new" id="id_new" value="{{ $pembelian->id }}">
                    <input type="hidden" name="id_obat_new" id="id_obat_new" value="{{ $order->id_obat }}">

                    <?php
                        $jumlah = $order->jumlah;
                        if(!is_null($detail->id)) {
                            $jumlah = $detail->jumlah;
                        }
                    ?>

                    <div class="row">
                        <div class="form-group col-md-12">
                            {!! Form::label('obat', 'Nama Obat') !!}
                            {!! Form::text('obat', $order->obat->nama, array('class' => 'form-control', 'placeholder'=>'Nama Apotek', 'readonly' => 'readonly')) !!}
                        </div>
                        <div class="form-group col-md-2">
                            {!! Form::label('jumlah_rev', 'Jumlah') !!}
                            {!! Form::text('jumlah_rev', $jumlah, array('class' => 'form-control', 'placeholder'=>'Masukan Jumlah Rev', 'id' => 'jumlah_rev')) !!}
                        </div>
                        <div class="form-group col-md-3">
                            {!! Form::label('harga', 'Harga Beli') !!}
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                {!! Form::text('harga_beli_new', $detail->harga_beli, array('class' => 'form-control', 'placeholder'=>'Masukan Harga', 'id' => 'harga_beli_new', 'readonly' => 'readonly')) !!}
                            </div>
                        </div>
                        <?php
                            $total = '';
                            $diskon_persen = '';
                            $total2 = 0;
                        ?>
                        <div class="form-group col-md-3">
                            {!! Form::label('total_harga', 'Total I') !!}
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                {!! Form::text('total_harga_new', $total, array('class' => 'form-control', 'placeholder'=>'Total Harga', 'id' => 'total_harga_new')) !!}
                            </div>
                        </div> 
                        <div class="form-group col-md-3">
                            {!! Form::label('diskon', 'Diskon') !!}
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                {!! Form::text('diskon_new', $detail->diskon, array('class' => 'form-control', 'placeholder'=>'Masukan Diskon', 'id' => 'diskon_new')) !!}
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            {!! Form::label('diskon_persen', 'Diskon Persen') !!}
                            <div class="input-group">
                                {!! Form::text('diskon_persen_new', $detail->diskon_persen, array('class' => 'form-control', 'placeholder'=>'Masukan Diskon', 'id' => 'diskon_persen_new')) !!}
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        
                        <div class="form-group col-md-3">
                            {!! Form::label('total', 'Total II') !!}
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                {!! Form::text('total', $total2, array('class' => 'form-control', 'placeholder'=>'Total', 'id' => 'total', 'readonly' => 'readonly')) !!}
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            {!! Form::label('tgl_batch', 'Tanggal Expired') !!}
                             {!! Form::text('tgl_batch_new', $detail->tgl_batch, array('class' => 'form-control datepicker', 'placeholder'=>'Masukan Tanggal Batch', 'id' => 'tgl_batch_new')) !!}
                        </div>
                        <div class="form-group col-md-3">
                            {!! Form::label('id_batch', 'ID Batch') !!}
                            {!! Form::text('id_batch_new', $detail->id_batch, array('class' => 'form-control', 'placeholder'=>'Masukan ID Batch', 'id' => 'id_batch_new')) !!}
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-success" type="button" onClick="set_detail(this, {{$no}})" data-toggle="tooltip" data-placement="top" title="Simpan"><i class="fa fa-save"></i> Simpan</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-undo"></i> Kembali</button>
                </div>
            </div>
         </div>
    </div>
<script type="text/javascript">
    $(document).ready(function(){
        $("#jumlah_rev").focus();

        var id_detail = $("#id_detail").val();
        if(id_detail != null) {
            var jumlah_rev = $("#jumlah_rev").val();
            var harga_beli = $("#harga_beli_new").val();
            if(harga_beli == "") {
                harga_beli = 0;
            } 
            var total_harga_new = parseFloat(jumlah_rev) * parseFloat(harga_beli);
            $("#total_harga_new").val(total_harga_new);

            cek_perubahan_harga_beli_new();
        }

        $("#total_harga_new, #diskon_new, #diskon_persen_new").change(function() {
            cek_perubahan_harga_beli_new();

            var total_harga = $("#total_harga_new").val(); 
            var diskon = $("#diskon_persen_new").val();
            var dis_1 = (parseFloat(diskon)/100)* total_harga;
            var dis_2 = $("#diskon_new").val();
            var total_diskon = parseFloat(dis_1) + parseFloat(dis_2); 
            var total_2 = parseFloat(total_harga) - parseFloat(total_diskon);

            $("#total").val(total_2);
        });

        $("#jumlah_rev").change(function() {
            cek_perubahan_harga_beli_new();

            var total_harga = $("#total_harga_new").val();
            var diskon = $("#diskon_persen_new").val();
            var dis_1 = (parseFloat(diskon)/100)* total_harga;
            var dis_2 = $("#diskon_new").val();
            var total_diskon = parseFloat(dis_1) + parseFloat(dis_2); 
            var total_2 = parseFloat(total_harga) - parseFloat(total_diskon);

            $("#total").val(total_2);
        });

        $('.datepicker').datepicker({
            autoclose:true,
            format:"yyyy-mm-dd",
            forceParse: false
        });

        $("#jumlah_rev").keypress(function(event){
            if (event.which == '10' || event.which == '13') {
                $("#total_harga_new").focus();
                event.preventDefault();
            }
        });

        $("#total_harga_new").keypress(function(event){
            if (event.which == '10' || event.which == '13') {
                $("#diskon_new").focus();
                event.preventDefault();
            }
        });

        $("#diskon_new").keypress(function(event){
            if (event.which == '10' || event.which == '13') {
                $("#diskon_persen_new").focus();
                event.preventDefault();
            }
        });

        $("#diskon_persen_new").keypress(function(event){
            if (event.which == '10' || event.which == '13') {
                $("#tgl_batch_new").focus();
                event.preventDefault();
            }
        });

        $('#tgl_batch_new').change(function(event){
            $("#id_batch_new").focus();
        });

        $("#id_batch_new").keypress(function(event){
            if (event.which == '10' || event.which == '13') {
                var no = $("#no").val();
                set_detail_new(this, no);
                event.preventDefault();
            }
        });
    })

    function set_detail(obj, no){
        data = {};
        $("#form_pembelian").find("input[name], select").each(function (index, node) {
            data[node.name] = node.value;
        });

        delete data['id'];
        delete data['id_obat'];
        delete data['harga_beli'];
        delete data['total_harga'];
        delete data['total'];
        delete data['jumlah'];
        delete data['jumlah_rev'];
        delete data['diskon'];
        delete data['id_batch'];
        delete data['tgl_batch'];
        delete data['diskon_persen'];
        var id = $("#id").val();
        data.id  = id;
        data.id_detail_order  = $("#id_detail_order").val();
        data.id_obat  = $("#id_obat_new").val();
        data.harga_beli  = $("#harga_beli_new").val();
        data.total_harga  = $("#total_harga_new").val();
        data.total  = $("#total").val();
        data.jumlah  = $("#jumlah_rev").val();
        data.diskon  = $("#diskon_new").val();
        data.id_batch  = $("#id_batch_new").val();
        data.tgl_batch  = $("#tgl_batch_new").val();
        data.diskon_persen  = $("#diskon_persen_new").val();

        //console.log(data);
        if(id) {
            $.ajax({
                type:"PUT",
                url : '{{url("pembelian/update_item")}}/'+id,
                dataType : "json",
                data : data,
                beforeSend: function(data){
                    // replace dengan fungsi loading
                    spinner.show();
                },
                success:  function(data){
                    if(data.status ==1){
                        kosongkan_form();
                        $('#modal-xl').modal('toggle');
                    }else{
                        //show_error(data.message);
                        swal(data.message, "error");
                        return false;
                    }
                },
                complete: function(data){
                    tb_nota_pembelian.fnDraw(false);
                    spinner.hide();
                },
                error: function(data) {
                    show_error("error ajax occured!");
                }

            });
        } else {
            $.ajax({
                type:"POST",
                url : '{{url("pembelian/add_item")}}',
                dataType : "json",
                data : data,
                beforeSend: function(data){
                    // replace dengan fungsi loading
                    spinner.show();
                },
                success:  function(data){
                    if(data.status == 1){
                        kosongkan_form();
                        $("#id").val(data.id);
                        $('#modal-xl').modal('toggle');
                    }else{
                        //show_error(data.message);
                        swal(data.message, "error");
                        return false;
                    }
                },
                complete: function(data){
                    tb_nota_pembelian.fnDraw(false);
                    spinner.hide();
                },
                error: function(data) {
                    show_error("error ajax occured!");
                }

            });
        }
    }

    function cek_perubahan_harga_beli_new() {
        var jumlah = $("#jumlah_rev").val();
        var diskon = $("#diskon_new").val();
        var diskon_persen = $("#diskon_persen_new").val();
        var total_harga = $("#total_harga_new").val();


        if(jumlah == "") {
            jumlah = 1;
        } else {
            jumlah = jumlah;
        }

        if(diskon == "") {
            diskon = 0;
        } else {
            diskon = diskon;
        } 

        if(diskon_persen == "") {
            diskon_persen = 0;
        } else {
            diskon_persen = diskon_persen;
        }

        if(total_harga == "") {
            total_harga = 0;
        } else {
            total_harga = total_harga;
        }

 
        var total_diskon = parseFloat(diskon) + (parseFloat(diskon_persen)/100 * parseFloat(total_harga));
        hitung_1 = (parseFloat(total_harga)-parseFloat(total_diskon));
        harga_beli = parseFloat(hitung_1)/parseFloat(jumlah);
        $("#harga_beli_new").val(harga_beli);
    }
</script>

