{!! Form::model($detail_pembelian, ['method' => 'POST', 'id' => 'myform', 'class'=>'validated_form', 'route' => ['pembelian.update_pembayaran_konsinyasi', $detail_pembelian->id]]) !!}
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-md-3">
                            {!! Form::label('id_obat', 'ID Obat') !!}
                            {!! Form::text('id_obat', $detail_pembelian->id_obat, array('class' => 'form-control', 'placeholder'=>'ID Obat', 'readonly' => 'readonly')) !!}
                        </div>
                        <div class="form-group col-md-9">
                            {!! Form::label('nama_obat', 'Nama Obat') !!}
                            {!! Form::text('nama_obat', $detail_pembelian->obat->nama, array('class' => 'form-control', 'placeholder'=>'Nama Obat', 'readonly' => 'readonly')) !!}
                        </div>
                        <div class="form-group col-md-3">
                            {!! Form::label('harga_beli', 'Harga Beli') !!}
                            {!! Form::text('harga_beli', $detail_pembelian->harga_beli, array('class' => 'form-control', 'placeholder'=>'Harga Beli', 'readonly' => 'readonly')) !!}
                        </div>
                        <div class="form-group col-md-3">
                            {!! Form::label('total_harus_jumlah_bayar', 'Jumlah yang harus dibayarkan') !!}
                            {!! Form::text('total_harus_jumlah_bayar', $detail_pembelian->jumlah, array('id'=>'total_harus_jumlah_bayar', 'class' => 'form-control', 'placeholder'=>'Jumlah yang harus dibayarkan', 'readonly' => 'readonly')) !!}
                        </div>
                        <div class="form-group col-md-3">
                            {!! Form::label('total_jumlah_bayar', 'Jumlah yang sudah terbayar') !!}
                            {!! Form::text('total_jumlah_bayar', $detail_pembelian->jumlah_bayar, array('id'=>'total_jumlah_bayar', 'class' => 'form-control', 'placeholder'=>'Jumlah yang sudah terbayar', 'readonly' => 'readonly')) !!}
                        </div>
                        <div class="form-group col-md-3">
                            {!! Form::label('total_sisa_bayar', 'Sisa yang harus dibayar') !!}
                            {!! Form::text('total_sisa_bayar', 0, array('id'=>'total_sisa_bayar', 'class' => 'form-control', 'placeholder'=>'Sisa yang harus dibayar', 'readonly' => 'readonly')) !!}
                        </div>
                        <div class="form-group col-md-12">
                            <div class="checkbox">
                                <label>
                                    {!! Form::checkbox('is_retur', 1, ($detail_pembelian->is_retur == 1 ? true : false), ['id' => 'is_retur']) !!} Retur dari obat yang belum terbayarkan
                                </label>
                            </div>
                        </div>
                        <div class="form-group col-md-4 show_hide_alasan">
                            {!! Form::label('id_alasan_retur', 'Alasan Retur (*)') !!}
                            {!! Form::select('id_alasan_retur', $alasan_returs, $retur_pembelian_obat->id_alasan_retur, ['class' => 'form-control input_select required']) !!}
                        </div>
                        <div class="form-group col-md-8 show_hide_alasan">
                            {!! Form::label('alasan_lain', 'Alasan Lainnya') !!}
                            {!! Form::text('alasan_lain', $retur_pembelian_obat->alasan_lain, array('id' => 'alasan_lain', 'class' => 'form-control', 'placeholder'=>'Alasan lainnya')) !!}
                        </div>
                    </div>
                </div>
            </div>
         </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <div class="card-header with-border">
                    <h3 class="card-title">Detail Pembayaran Konsinyasi | <a class="btn bg-olive btn-flat pull-right btn-sm" onClick="add_pembayaran_konsinyasi()"><i class="fa fa-plus"></i> Add Data Pembayaran</a></h3>
                </div>
                <div class="card-body" id="detail_data_pembayaran">
                    <?php 
                        $pembayaran_konsinyasis = $detail_pembelian->pembayaran_konsinyasi;
                        $jumlah = count($pembayaran_konsinyasis);

                        $pembayaran_konsinyasi = new App\PembayaranKonsinyasi;
                    ?>
                    @if($jumlah>0)
                        <?php $no = 0; ?>
                        @foreach($pembayaran_konsinyasis as $pembayaran_konsinyasi)
                            <?php $no++; ?>
                            @include('pembayaran_faktur/_form_add_pembayaran', ['no'=>$no, 'pembayaran_konsinyasi'=>$pembayaran_konsinyasi])
                        @endforeach
                    @else
                        @for($no=0;$no<1;$no++)
                            @include('pembayaran_faktur/_form_add_pembayaran', ['no'=>$no+1, 'pembayaran_konsinyasi'=>$pembayaran_konsinyasi])
                        @endfor
                    @endif 
                    <input type="hidden" name="counter" id="counter" value="<?php echo $no ?>">
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <div class="card-body">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="form-group action-group">
                                <button class="btn btn-primary" type="button" onClick="submit_valid()" data-toggle="tooltip" data-placement="top" title="Save"><i class="fa fa-save"></i> Simpan</button>
                                <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-undo"></i>Kembali</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

{!! Form::close() !!}
<script type="text/javascript">
    var token = "";

    $(document).ready(function(){
        token = $('input[name="_token"]').val();
        set_awal();


        if($('#is_retur').attr('checked')) {
            $(".show_hide_alasan").show();
        } else {
            $(".show_hide_alasan").hide();
        }

        $('input[name="is_retur"]').click(function() {
            if (this.checked) {
                $(".show_hide_alasan").show();
            } else {
                $(".show_hide_alasan").hide();
            }
        });
    })

    function set_awal() {
        var total_harus_jumlah_bayar = $("#total_harus_jumlah_bayar").val();
        var total_jumlah_bayar = $("#total_jumlah_bayar").val();
        if(total_jumlah_bayar == '') {
            total_jumlah_bayar = 0;
        } 
        var total_sisa_bayar = parseInt(total_harus_jumlah_bayar)-parseInt(total_jumlah_bayar);
        $("#total_jumlah_bayar").val(total_jumlah_bayar);
        $("#total_sisa_bayar").val(total_sisa_bayar);   
    }

    function set_kartu(no) {
        var id_kartu_debet_credit = $("#id_kartu_debet_credit_"+no).val();
        if(id_kartu_debet_credit == 0) {
            $("#debet_"+no).val(0);
            $("#biaya_admin_"+no).val(0);
            var hitung_bayar = $("#hitung_bayar_"+no).val()
            $("#cash_"+no).val(hitung_bayar);
            var cash = $("#cash_"+no).val();
            $("#cash_"+no).focus().val('').val(cash);   
        } else {
            $("#cash_"+no).val(0);
            $("#biaya_admin_"+no).val(0);
            var hitung_bayar = $("#hitung_bayar_"+no).val()
            $("#debet_"+no).val(hitung_bayar);
            var debet = $("#debet_"+no).val();
            $("#biaya_admin_"+no).focus();
        }
        var cash = $("#cash_"+no).val();
        var debet = $("#debet_"+no).val();
        var biaya = $("#biaya_admin_"+no).val();
        var total = parseFloat(cash) + parseFloat(debet) + parseFloat(biaya);
        $("#total_bayar_"+no).val(total);
    }

    function set_jumlah_bayar(no){
        var jumlah_bayar = $("#jumlah_bayar_"+no).val();
        var harga_beli = $("#harga_beli").val();
        var hitung_bayar = parseFloat(jumlah_bayar)*parseFloat(harga_beli);
        $("#hitung_bayar_"+no).val(hitung_bayar);
        set_awal_jumlah_bayar();
    }

    function set_awal_jumlah_bayar() {
        var jumlah_data = $("#counter").val();
        var total_harus_jumlah_bayar = $("#total_harus_jumlah_bayar").val();
        var jumlah = 0;
        for (var i=1;i<=jumlah_data;i++) {
            var jumlah_bayar = $("#jumlah_bayar_"+i).val();
            if(jumlah_bayar == '') {
                jumlah_bayar = 0;
            }

            jumlah = parseFloat(jumlah) + parseFloat(jumlah_bayar);
        }
        
        if(jumlah>total_harus_jumlah_bayar) {
            alert("Jumlah yang akan dibayarkan melebihi jumlah yang harus dibayarkan!");
            $("#jumlah_bayar_"+jumlah_data).val('');
            $("#tgl_bayar_"+jumlah_data).val('');
        } else {
            $("#total_jumlah_bayar").val(jumlah);
        }
        set_awal()
    }

    function submit_valid(){
        $(".validated_form").submit();
    }

    function add_pembayaran_konsinyasi(){
        $.ajax({
            type: "POST",
             url: '{{url("pembelian/add_pembayaran_konsinyasi")}}',
            async:true,
            // dataType:'json',
            data: {
                _token:token,
                counter:$("#counter").val()
            },
            // dataType: 'json',
            beforeSend: function(data){
                // replace dengan fungsi loading
                $(".overlay").show();
            },
            success:  function(data){
                $("#detail_data_pembayaran").append(data);
                //auto_number('numbering');
                current_counter = parseInt($("#counter").val());
                if(isNaN(current_counter)){
                    current_counter = 0;
                }
                current_counter = current_counter+1;
                $("#counter").val(current_counter);
                set_awal_jumlah_bayar();
          
                $('html, body').animate({
                    scrollTop: $("#detail_data_pembayaran_"+current_counter).offset().top
                }, 1000);

            },
            complete: function(data){
                // replace dengan fungsi mematikan loading
                $(".overlay").hide();
                //auto_number("nomor_urut");
            },
            error: function(data) {
                alert("error ajax occured!");
                // done_load();
            }
        });
    }
</script>


