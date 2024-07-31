<div class="row">
    <div class="col-sm-12">
        <div class="card card-info card-outline">
            <div class="card-body">
                <div class="row">
                    <input type="hidden" name="id" id="id" value="{{ $penjualan->id }}">
                    <div class="form-group col-md-12">
                        <label for="cash">Total Belanja</label>
                        <div class="input-group"> 
                            <div class="input-group-prepend">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input type="hidden" name="total_belanja_value" id="total_belanja_value" value="{{ $harga_total }}"> 
                            <input id="total_belanja" class="form-control required" placeholder="Total Belanja" name="total_belanja" type="text" readonly="readonly" value="{{ $harga_total }}">
                        </div>
                    </div>
                    
                    <input  name="is_debet_or_creadit" type="hidden" value="1" id="is_debet_or_creadit">
                    <div class="form-group col-md-12">
                        <label for="cash">Tunai</label>
                        <div class="input-group"> 
                            <div class="input-group-prepend">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input id="cash_value" class="form-control required" placeholder="Cash" name="cash_value" type="text" autocomplete="off" value="{{ $penjualan->cash }}">
                        </div>
                    </div>
                    <div class="form-group col-md-7">
                        {!! Form::label('id_kartu_debet_credit_input', 'Metode Pembayaran') !!}
                        <select id="id_kartu_debet_credit_input" name="id_kartu_debet_credit_input" class="form-control">
                            <option value="">------Pilih Kartu-----</option>
                            <option value="0" data-charge_kartu="0" {!!( "0" == $penjualan->id_kartu_debet_credit ? 'selected' : '')!!}>Cash Only</option>
                            <?php $no = 0; ?>
                            @foreach( $kartu_debets as $kartu_debet )
                                <?php $no = $no+1; ?>
                                <option value="{{ $kartu_debet->id }}" data-charge_kartu="{{ $kartu_debet->charge }}" {!!( $kartu_debet->id == $penjualan->id_kartu_debet_credit ? 'selected' : '')!!}>{{ $kartu_debet->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        {!! Form::label('surcharge', 'Surcharge') !!}
                        <div class="input-group"> 
                             <?php
                                if($penjualan->surcharge != 0) {
                                    $surcharge = $penjualan->surcharge;
                                } else {
                                    $surcharge = 0;
                                }
                            ?>
                            {!! Form::text('surcharge_input', $surcharge, array('id' => 'surcharge_input', 'class' => 'form-control', 'placeholder'=>'Masukan Surcharge', 'readonly' => 'readonly')) !!}
                            <div class="input-group-prepend">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-md-12">
                        {!! Form::label('no_kartu', 'Nomor Kartu') !!}
                        {!! Form::text('no_kartu_input', $penjualan->no_kartu, array('id' => 'no_kartu_input', 'class' => 'form-control', 'placeholder'=>'Masukan Nomor Kartu', 'autocomplete' => 'off')) !!}
                    </div>
                    <div class="form-group col-md-12">
                        {!! Form::label('debet', 'Debet/Credit') !!}
                        <div class="input-group"> 
                            <div class="input-group-prepend">
                                <span class="input-group-text">Rp</span>
                            </div>
                            {!! Form::text('debet_input', $penjualan->debet, array('id' => 'debet_input', 'class' => 'form-control', 'placeholder'=>'Masukan Jumlah Debet', 'autocomplete' => 'off')) !!}
                        </div>
                    </div>
                    <div class="form-group col-md-12 kartu_debet_credit">
                        {!! Form::label('total_debet', 'Total Debet') !!}
                        <div class="input-group"> 
                            <div class="input-group-prepend">
                                <span class="input-group-text">Rp</span>
                            </div>
                            {!! Form::text('total_debet_input', $penjualan->total_debet, array('id' => 'total_debet_input', 'class' => 'form-control', 'readonly' => 'readonly')) !!}
                        </div>
                    </div>
                    <div class="form-group col-md-12">
                        {!! Form::label('total_bayar_input', 'Total Bayar') !!}
                        <div class="input-group"> 
                            <div class="input-group-prepend">
                                <span class="input-group-text">Rp</span>
                            </div>
                            {!! Form::text('total_bayar_input', $penjualan->total_bayar, array('id' => 'total_bayar_input', 'class' => 'form-control', 'readonly' => 'readonly')) !!}
                        </div>
                    </div>
                    <div class="form-group col-md-12">
                        <label for="kembalian">Kembalian</label>
                        <div class="input-group"> 
                            <div class="input-group-prepend">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input id="kembalian_value" class="form-control required" placeholder="Kembalian" readonly="readonly" name="kembalian_value" type="text">
                        </div>
                    </div>
                </div>
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

<script type="text/javascript">
    var token = '{{csrf_token()}}';

    $(document).ready(function(){
        $("#cash_value").focus();
        $("#id_kartu_debet_credit_input").select2({});
        //$('#id_kartu_debet_credit_input').select2('open');
        $("#cash_value").change(function() {
            cek_kembalian();
        });

        $("#cash_value").keypress(function(event){
            if (event.which == '10' || event.which == '13') {
                $('#id_kartu_debet_credit_input').select2('open');
                event.preventDefault();
            }
        });

        if($('#is_debet_or_creadit').attr('checked')) {
            $(".kartu_debet_credit").show();
        } else {
            $(".kartu_debet_credit").hide();
        }

        $('input[name="is_debet_or_creadit"]').click(function() {
            if (this.checked) {
                $(".kartu_debet_credit").show();
            } else {
                $(".kartu_debet_credit").hide();
            }
        });

        $("#surcharge_input").val(0);
        $("#total_debet_input").val(0);

        $("#debet_input, #surcharge_input").keyup(function() {
            var debet       = $("#debet_input").val();
            var charge   = $("#surcharge_input").val();
            var total = parseFloat(debet) + (parseFloat(charge)/100 * parseFloat(debet));
            $("#total_debet_input").val(total);

            cek_kembalian();
        });

        $('#id_kartu_debet_credit_input').on('select2:select', function (e) {
            charge = $(this).find(':selected').data('charge_kartu');
            $("#surcharge_input").val(charge);
            var id_kartu_debet_credit_input = $("#id_kartu_debet_credit_input").val();
            if(id_kartu_debet_credit_input == 0) {
                $("#no_kartu_input").val(0);
                $("#debet_input").val(0);
                $("#debet_input").focus();
            } else {
                $("#no_kartu_input").focus();
            }
            
            cek_kembalian();
        });


        $("#id_kartu_debet_credit_input").keypress(function(event){
            if (event.which == '10' || event.which == '13') {
                $("#no_kartu_input").focus();
                event.preventDefault();
            }
        });

        $("#no_kartu_input").keypress(function(event){
            if (event.which == '10' || event.which == '13') {
                $("#debet_input").focus();
                event.preventDefault();
            }
        });

        $("#debet_input").keypress(function(event){
            if (event.which == '10' || event.which == '13') {
                set_data(this);
            }
        });
    })

    function set_data(obj){
        no_kartu = parseInt($("#no_kartu_input").val());
        var id = $("#id").val();
        if(Number.isInteger(no_kartu)) {
            total_belanja = $("#total_belanja_value").val();
            if(total_belanja == "" || total_belanja == 0) {
                show_error("Belum ada item belanja!");
                return false;
            } else {
                cash = $("#cash_value").val();
                kembalian = $("#kembalian_value").val();
                
                debet = $("#debet_input").val();
                surcharge = $("#surcharge_input").val();
                harga_belanja_awal = $("#total_belanja_value").val();
                cash = $("#cash_value").val();
                total = $("#total_debet_input").val();
                id_kartu_debet_credit_input = $("#id_kartu_debet_credit_input").val();
                total_charge = (parseFloat(surcharge)/100 * parseFloat(debet));
                total_bayar = $("#total_bayar_input").val();
                
                if(kembalian >= 0) {
                    $("#cash").val(cash);
                    $("#id_kartu_debet_credit").val(id_kartu_debet_credit_input);
                    $("#no_kartu").val(no_kartu);
                    $("#debet").val(total);
                    $("#surcharge").val(surcharge);
                    $("#total_belanja").val(harga_belanja_awal);
                    $("#total_bayar").val(total_bayar);
                    $("#kembalian").val(kembalian);
                    $("#count_total_belanja").val(parseFloat(harga_belanja_awal) + parseFloat(total_charge));
                    if(id_kartu_debet_credit_input != 0 && kembalian > 0) {
                        show_error("Jika menggunakan kartu debet/kredit, tidak diperkenankan ada kembalian, silakan cek kembali!");
                        return false;
                    } else {
                        submit_valid(id);
                    }
                } else {
                    show_error("Uang yang dimasukan kurang!");
                    return false;
                }
            }
        } else {
            show_error("Nomor kartu harus angka!");
            return false;
        }
    }

    function cek_kembalian() {
        var total = $("#total_pembayaran_input").val();
        var cash = $("#cash_value").val();

        if(total != "" && total != 0 && cash != ""){
            var uang        = $("#cash_value").val();
            var debet       = $("#debet_input").val();
            if(debet == "") {
                debet = 0;
            }

            var surcharge = $("#surcharge_input").val();
            if(surcharge == "") {
                surcharge = 0;
            }

            var total_charge = (parseFloat(surcharge)/100 * parseFloat(debet));
            var total_bayar = (parseFloat(total_charge) + parseFloat(cash) + parseFloat(debet));

            var total_debet = (parseFloat(surcharge)/100 * parseFloat(debet)) + parseFloat(debet);
            var total_1     = parseFloat(uang) + parseFloat(debet);
            var kembalian = total_1 - total;
            $("#total_bayar_input").val(total_bayar);
            $("#kembalian_value").val(kembalian);
        } else {
            $("#kembalian_value").val(0);
        }
    }
</script>

