{!! Form::model($penjualan, ['method' => 'PUT', 'class'=>'validated_form', 'id' => 'form-pembayaran-kredit', 'route' => ['penjualan.update', $penjualan->id]]) !!}
<?php $total_belanja_x = 0;?>
<div class="row">
    <div class="col-sm-12">
        <div class="card card-info card-outline">
            <div class="card-body">
            	<div class="row">
				    <!-- /.col -->
				    <div class="col-md-6">
		                <input type="hidden" name="id" id="id" value="{{ $penjualan->id }}">
                        <input type="hidden" name="_token" id="_token" value="{{csrf_token()}}">
                        <?php
                            $nama_apotek = strtoupper($apotek->nama_panjang);
                            $nama_apotek_singkat = strtoupper($apotek->nama_singkat);
                        ?>
                        <p align="center">APOTEK BWF-{{ $nama_apotek }}</p>
                        <p align="center">{{ $apotek->alamat }}</p>
                        <p align="center">Telp. {{ $apotek->telepon }}</p>
                        <hr>
                        <p style="margin-left: 10px;">No Nota : {{$nama_apotek_singkat}}-{{ $penjualan->id }}</p>
                        <p style="margin-left: 10px;">Tanggal : {{ $penjualan->created_at }}</p>
                        <hr>
						      
				      	@if($penjualan->is_penjualan_tanpa_item != 1)
                            <div class="table-responsive">
                                <?php 
                                    $no = 0; 
                                    $total_belanja = 0;
                                ?>
                                <table class="table">
                                    <tr>
                                        <td>No.</td>
                                        <td>Nama Obat</td>
                                        <td>Jumlah</td>
                                        <td>Harga</td>
                                        <td>Diskon</td>
                                        <td>Total</td>
                                    </tr>
                                        
                                    @foreach( $detail_penjualans as $obj )
                                        <?php 
                                            $no++;
                                            $total_1 = ($obj->jumlah-$obj->jumlah_cn) * $obj->harga_jual;
                                            $total_2 = $total_1 - $obj->diskon;
                                            $total_belanja = $total_belanja + $total_2;
                                            $harga_jual = number_format($obj->harga_jual,0,',',',');
                                            $diskon = number_format($obj->diskon,0,',',',');
                                            $total_2 = number_format($total_2,0,',',',');
                                        ?>
                                        <tr>
                                            <td>{{ $no }}</td>
                                            <td>{{ $obj->nama }}</td>
                                            <td>{{ $obj->jumlah }}</td>
                                            <td>{{ $harga_jual }}</td>
                                            <td>(-{{ $diskon }})</td>
                                            <td>Rp {{ $total_2 }}</td>
                                        </tr>
                                    @endforeach

                                </table>
                                <?php
                                    $total_diskon_persen = $penjualan->diskon_persen/100 * $total_belanja;
                                    $total_diskon_persen_vendor = $penjualan->diskon_vendor/100 * $total_belanja;
                                    $total_belanja_bayar = $total_belanja - ($total_diskon_persen + $penjualan->diskon_rp);
                                    $total_diskon = $total_diskon_persen+$penjualan->diskon_rp+$total_diskon_persen_vendor;
                                    $total_belanja = $total_belanja+$penjualan->biaya_jasa_dokter;
                                    $biaya_jasa_dokter = number_format($penjualan->biaya_jasa_dokter,0,',',',');
                                    $biaya_lab = number_format($penjualan->biaya_lab,0,',',',');
                                    $biaya_apd = number_format($penjualan->biaya_apd,0,',',',');
                                ?>
                                <hr>
                                <table class="table">
                                    <tr>
                                        <td>Jasa Dokter</td>
                                        <td>Rp&nbsp;{{ $biaya_jasa_dokter }}</td>
                                    </tr>
                                    @if($penjualan->id_jasa_resep != '') 
                                        <?php
                                            $jasa_resep_biaya = number_format($penjualan->jasa_resep,0,',',',');
                                            $total_belanja = $total_belanja+$penjualan->jasa_resep;
                                        ?>
                                        <tr>
                                            <td>Jasa Resep</td>
                                            <td>Rp&nbsp;{{ $jasa_resep_biaya }}</td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td>Jasa Resep</td>
                                            <td>Rp&nbsp;0</td>
                                        </tr>
                                    @endif
                                    @if($penjualan->id_paket_wd != '') 
                                        <?php
                                            $harga_wd = number_format($penjualan->harga_wd,0,',',',');
                                            $total_belanja = $total_belanja+$penjualan->harga_wd;
                                        ?>
                                        <tr>
                                            <td>Paket WT</td>
                                            <td>Rp&nbsp;{{ $harga_wd }}</td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td>Paket WT</td>
                                            <td>Rp&nbsp;0</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td>Biaya Lab</td>
                                        <td>Rp&nbsp;{{ $biaya_lab }}</td>
                                    </tr>
                                    <tr>
                                        <td>Biaya APD</td>
                                        <td>Rp&nbsp;{{ $biaya_apd }}</td>
                                    </tr>
                                </table>
                                <hr>
                                 <?php
                                    $debet = 0;
                                    if(!empty($penjualan->id_kartu_debet_credit)) {
                                        $debet = $penjualan->debet;
                                    } 
                                    $total_bayar = $debet+$penjualan->cash;

                                    if($total_bayar == 0) {
                                        $total_bayar = $total_belanja+$penjualan->kembalian;
                                    }
                                    $total_belanja_format = number_format($total_belanja,0,',',',');
                                    $total_diskon_format = number_format($total_diskon,0,',',',');
                                    $total_bayar_format = number_format($total_bayar,0,',',',');
                                    $kembalian_format = number_format($penjualan->kembalian,0,',',',');
                                    $grand_total = $total_belanja - $total_diskon;
                                    $grand_total_format = number_format($grand_total);
                                ?>

                                <table class="table">
                                    <tr>
                                        <td>Total</td>
                                        <td>Rp&nbsp;{{ $total_belanja_format }}</td>
                                    </tr>
                                    <tr>
                                        <td>Diskon</td>
                                        <td>Rp&nbsp;{{ $total_diskon_format }}</td>
                                    </tr>
                                    <tr>
                                        <td>Grand Total</td>
                                        <td>Rp&nbsp;{{ $grand_total_format }}</td>
                                    </tr>
                                </table>
                                <hr>
                                <p align="center">Terimakasih Atas Kunjungan Anda</p>
                                <p align="center">Semoga Lekas Sembuh</p>
                                <hr>
                            </div>
                        @endif
				    </div>
				    <div class="col-md-6">
				    	<div class="row">
                            @if($penjualan->is_kredit == 1)
                            <input type="hidden" name="total_belanja_st" id="total_belanja_st" value="{{ $total_belanja }}">
                            <div class="form-group col-md-12">
                                <label for="cash">Diskon (%)</label>
                                <div class="input-group"> 
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input type="hidden" name="param_diskon" id="param_diskon" value="{{ $penjualan->diskon_persen }}">
                                    <input id="diskon_persen" class="form-control" placeholder="Diskon" name="diskon_persen" type="text" autocomplete="off" value="{{ $penjualan->diskon_persen }}">
                                </div>
                            </div>
                            @endif
				    		<div class="form-group col-md-12">
		                        <label for="cash">Total Belanja</label>
		                        <div class="input-group"> 
		                            <div class="input-group-prepend">
		                                <span class="input-group-text">Rp</span>
		                            </div>
		                            <input type="hidden" name="total_belanja_value" id="total_belanja_value" value="{{ $grand_total }}"> 
		                            <input id="total_belanja" class="form-control required" placeholder="Total Belanja" name="total_belanja" type="text" readonly="readonly" value="{{ $grand_total }}">
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
                                    <?php
                                        $debet = $grand_total-$penjualan->cash;
                                    ?>
		                            {!! Form::text('debet_input', $penjualan->debet, array('id' => 'debet_input', 'class' => 'form-control', 'placeholder'=>'Masukan Jumlah Debet', 'autocomplete' => 'off')) !!}
		                        </div>
		                    </div>
		                    <div class="form-group col-md-12">
		                        {!! Form::label('total_debet', 'Total Debet') !!}
		                        <div class="input-group"> 
		                            <div class="input-group-prepend">
		                                <span class="input-group-text">Rp</span>
		                            </div>
                                    <?php
                                       // $debet = $grand_total-$penjualan->cash;
                                        $total_debet = $penjualan->debet + (($penjualan->surcharge/100) * $debet);
                                    ?>
		                            {!! Form::text('total_debet_input', $total_debet, array('id' => 'total_debet_input', 'class' => 'form-control', 'readonly' => 'readonly')) !!}
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
		                    <div class="form-group col-md-12">
		                        <button class="btn btn-success" type="button" onClick="submit_valid({{$penjualan->id}})" data-toggle="tooltip" data-placement="top" title="Simpan"><i class="fa fa-save"></i> Simpan</button>
		                        <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal"><i class="fa fa-undo"></i> Kembali</button>
		                    </div>
				    	</div>
				    </div>
				</div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
	$(document).ready(function(){
        var param_diskon = $("#param_diskon").val();
        if(param_diskon == '' || param_diskon == null) {
            $("#diskon_persen").val(0);
        }
        var cash_value = $('#cash_value').val(); 
        var total_belanja = $("#total_belanja").val();     
        var no_kartu_input = $("#no_kartu_input").val();  
        $('#cash_value').focus().val('').val(cash_value);  
        $("#id_kartu_debet_credit_input").select2({});
        //$('#id_kartu_debet_credit_input').select2('open');
        $("#cash_value").change(function() {
            cek_kembalian();
        });

        cek_kembalian();

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

        $("#diskon_persen").keypress(function(event){
            if (event.which == '10' || event.which == '13') {
                set_data_diskon(this);
                cek_kembalian();
                $("#cash_value").focus();
                event.preventDefault();
            }
        });

        //$("#surcharge_input").val(0);
        //$("#total_debet_input").val(0);

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
                $("#total_debet_input").val(0);
                $("#cash_value").val(total_belanja);
                $("#cash_value").focus().val('').val(cash_value);  
            } else {
                $("#debet_input").val(total_belanja);
                $("#no_kartu_input").focus().val('').val(no_kartu_input);  
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

    function set_data_diskon(obj){
        diskon_persen = $("#diskon_persen").val();
        harga_total_awal = $("#total_belanja").val();
        total_penjualan = $("#total_belanja_st").val();

        hitung = ((parseFloat(diskon_persen))/100) * parseFloat(total_penjualan);
        hitung_diskon = parseFloat(hitung);
       /* $("#diskon_persen").val(diskon_persen);
        $("#id_karyawan").val(id_karyawan);
        $("#diskon_persen_input").html("Karyawan : "+nama_karyawan);
        var diskon_total_rp = hitung_rp_khusus(hitung_diskon);
        $("#diskon_total").html(hitung_diskon);
        $("#diskon_total_input").val(hitung_diskon);*/

        //total_byr = parseFloat(harga_total_awal);
       
        total_byr = parseFloat(total_penjualan) - parseFloat(hitung_diskon); 
       // var total_byr_rp = hitung_rp_khusus(total_byr);
        $("#total_belanja").html(total_byr);
        $("#total_belanja").val(total_byr);
    }

	function cek_kembalian() {
        var total = $("#total_belanja").val();
        var cash = $("#cash_value").val();

        if(total != ""){
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
{!! Form::close() !!}