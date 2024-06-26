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
                        <div class="form-group col-md-12">
                            {!! Form::label('obat', 'Nama Obat') !!}
                            {!! Form::text('obat', $order->obat->nama, array('class' => 'form-control', 'placeholder'=>'Nama Apotek', 'readonly' => 'readonly')) !!}
                        </div>
                        <div class="form-group col-md-2">
                            {!! Form::label('jumlah_rev', 'Jumlah') !!}
                            {!! Form::text('jumlah_rev', $order->jumlah, array('class' => 'form-control', 'placeholder'=>'Masukan Jumlah Rev', 'id' => 'jumlah_rev')) !!}
                        </div>
                        <div class="form-group col-md-3">
                            {!! Form::label('harga', 'Harga Beli') !!}
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                {!! Form::text('harga_beli_new', 0, array('class' => 'form-control', 'placeholder'=>'Masukan Harga', 'id' => 'harga_beli_new', 'readonly' => 'readonly')) !!}
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
                    <button class="btn btn-success" type="button" onClick="set_detail_new(this, {{$no}})" data-toggle="tooltip" data-placement="top" title="Simpan"><i class="fa fa-save"></i> Simpan</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-undo"></i> Kembali</button>
                </div>
            </div>
         </div>
    </div>
<script type="text/javascript">
    $(document).ready(function(){
        $("#jumlah_rev").focus();

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

    function set_detail_new(obj, no){
        var harga_beli = $("#harga_beli_new").val();
        var total_harga = $("#total_harga_new").val();
        var total = $("#total").val();
        var jumlah = $("#jumlah").val();
        var jumlah_rev = $("#jumlah_rev").val();
        var jumlah = $("#jumlah").val();
        var diskon = $("#diskon_new").val();
        var id_batch = $("#id_batch_new").val();
        var tgl_batch = $("#tgl_batch_new").val();
        var diskon_persen = $("#diskon_persen_new").val();
        var hit_diskon_persen = parseFloat(diskon_persen)/100 * parseFloat(total_harga);
        var selisih = parseInt(jumlah) - parseFloat(jumlah_rev);
        if(selisih == 0) {
            
        } else {
            $("#jumlah_revisi_"+no).val(jumlah);
            $("#jumlah_"+no).val(jumlah_rev);
            $("#jumlah_"+no).html(jumlah_rev);
            if(selisih < 0) {
                //alert('penambahan');
                $("#id_jenis_revisi_"+no).val(1);
            } else {
                //alert('pengurangan');
                $("#id_jenis_revisi_"+no).val(2);
            }
        }
        $("#harga_beli_"+no).val(harga_beli);
        $("#diskon_"+no).val(diskon);
        $("#total_harga_"+no).val(total_harga);
        $("#total_2_"+no).val(total);
        $("#id_batch_"+no).val(id_batch_new);
        $("#tgl_batch_"+no).val(tgl_batch_new);
        $("#diskon_persen_"+no).val(diskon_persen);
        $("#hitung_total_"+no).val(total_harga);
        $("#hitung_diskon_"+no).val(diskon);
        $("#hitung_diskon_persen_"+no).val(hit_diskon_persen);

        $("#harga_beli_"+no).html(harga_beli);
        $("#diskon_"+no).html(diskon);
        $("#total_harga_"+no).html(total_harga);
        $("#total_2_"+no).html(total);
        $("#id_batch_"+no).html(id_batch_new);
        $("#tgl_batch_"+no).html(tgl_batch_new);
        $("#diskon_persen_"+no).html(diskon_persen);
        $("#hitung_total_"+no).html(total_harga);
        $("#hitung_diskon_"+no).html(diskon);
        $("#hitung_diskon_persen_"+no).html(hit_diskon_persen);

        $("#total_harga_lbl_"+no).html(total_harga);
        $("#diskon_lbl_"+no).html(diskon);
        $("#diskon_persen_lbl_"+no).html(diskon_persen);
        $("#total_2_lbl_"+no).html(total);
        $("#jumlah_lbl_"+no).html(jumlah_rev);
        $("#harga_beli_lbl_"+no).html(harga_beli);
        
        hitung_total();
        $('#modal-xl').modal('toggle');
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

