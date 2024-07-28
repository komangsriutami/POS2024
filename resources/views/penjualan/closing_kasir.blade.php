<style type="text/css">
    table {
        table-layout: fixed;
    }
</style>
<div class="row">
    <div class="col-sm-12">
        <div class="card card-info card-outline">
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-12">
                        <p class="text-red">*) Note : penjualan kredit yang diretur hari itu juga tidak akan terhitung ke dalam total penjualan kredit karena <b>[tidak ada pembayaran yang dilakukan]</b>.</p>
                    </div>
                    <form id="form-closing" class="validated_form">
                        <input type="hidden" name="id_user" id="id_user" value="{{ $id_user }}">
                        <input type="hidden" name="id" id="id" value="{{ $penjualan_closing->id }}">
                        <input type="hidden" name="tanggal" id="tanggal" value="{{ $tanggal }}">
                        <input type="hidden" name="token" id="token" value="{{ csrf_token() }}">
                        <meta name="csrf-token" content="{{ csrf_token() }}">
                            <?php 
                                $total_diskon = $detail_penjualan->total_diskon_persen + $penjualan2->total_diskon_rp; // baru ditambhin ini | 15/27/2022
                                // $detail_penjualan_kredit->total_diskon_persen_vendor + $detail_penjualan_kredit->total_diskon_persen;
                                $total_1 = $detail_penjualan->total;
                                $total_3 = $detail_penjualan->total-$total_diskon;//+ $detail_penjualan_kredit->total;

                                $total_penjualan_cn_cash = 0;
                                if(!empty($penjualan_cn_cash->total_penjualan)) {
                                    $total_penjualan_cn_cash = $penjualan_cn_cash->total_penjualan - $detail_penjualan_cn->total_diskon_persen;
                                }

                                $total_penjualan_cn_debet = 0;
                                if(!empty($detail_penjualan_cn->total)) {
                                    $total_penjualan_cn_debet = $detail_penjualan_cn->total-$total_penjualan_cn_cash-$detail_penjualan_cn->total_diskon_persen;
                                }

                                $grand_total = $total_3+$penjualan2->total_jasa_dokter+$penjualan2->total_jasa_resep+$penjualan2->total_paket_wd+$penjualan2->total_lab+$penjualan2->total_apd;

                                $total_cash = $grand_total - $penjualan2->total_debet;
                                $total_cash_kredit = $detail_penjualan_kredit->total - $detail_penjualan_kredit->total_diskon_persen_vendor - $detail_penjualan_kredit->total_diskon_persen;
                                $total_cn = 0 + $detail_penjualan_cn->total - $detail_penjualan_cn->total_diskon_persen;
                                $total_cash_kredit_terbayar = ($detail_penjualan_kredit_terbayar->total + $penjualan_kredit_terbayar->total_jasa_dokter + $penjualan_kredit_terbayar->total_jasa_resep) - $penjualan_kredit_terbayar->total_debet-$detail_penjualan_kredit_terbayar->total_diskon_vendor - $detail_penjualan_kredit_terbayar->total_diskon_persen;
                                if($total_cash_kredit_terbayar < 10) {
                                    $total_cash_kredit_terbayar = 0;
                                }
                                $total_penjualan_kredit_terbayar = $penjualan_kredit_terbayar->total_debet+$total_cash_kredit_terbayar;

                                $uang_seharusnya = ($total_cash + $total_cash_kredit_terbayar) - ($total_diskon + $penjualan_cn_cash->total_penjualan);
                            ?>
                         
                           
                            <div class="row">
                                <div class="form-group  col-md-4">
                                    {!! Form::label('total_penjualan_kredit', 'Total Penjualan Kredit') !!}
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        {!! Form::text('total_penjualan_kredit', $total_cash_kredit, array('id' =>'total_penjualan_kredit', 'class' => 'form-control', 'readonly' => 'readonly')) !!}
                                    </div>
                                </div>
                                <div class="form-group  col-md-4">
                                    {!! Form::label('total_cash_kredit_terbayar', 'Pembayaran Penjualan Kredit (Cash)') !!}
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        {!! Form::text('total_cash_kredit_terbayar', $total_cash_kredit_terbayar, array('id' =>'total_cash_kredit_terbayar', 'class' => 'form-control', 'readonly' => 'readonly')) !!}
                                    </div>
                                </div>   
                                <div class="form-group  col-md-4">
                                    {!! Form::label('total_debet_kredit_terbayar', 'Uang Pembayaran Penjualan Kredit (Debet)') !!}
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        {!! Form::text('total_debet_kredit_terbayar', $penjualan_kredit_terbayar->total_debet, array('id' =>'total_debet_kredit_terbayar', 'class' => 'form-control', 'readonly' => 'readonly')) !!}
                                    </div>
                                </div>          
                                <input type="hidden" name="total_penjualan_kredit_terbayar" name="total_penjualan_kredit_terbayar" value="{{ $total_penjualan_kredit_terbayar }}">         
                            </div>
                            <hr style="border: 1px solid #004d40; padding: 0px; margin-top: 0px; margin-bottom: 10px;">
                            <div class="form-group col-md-12">
                                <table class="table table-sm" style="width: 100%!important;">
                                    <tbody>
                                        <tr>
                                            <?php
                                                $total_1_format = number_format($total_1,0,',',',');
                                            ?>
                                            <td style="width: 28%;">Jumlah Penjualan</td>
                                            <td style="width: 2%;"> : </td>
                                            <td style="width: 70%;">Rp {{ $total_1_format }}</td>
                                            <input type="hidden" name="jumlah_penjualan" id="jumlah_penjualan" value="{{ $total_1 }}">
                                        </tr>
                                        <tr>
                                            <?php
                                                $total_diskon_format = number_format($total_diskon,0,',',',');
                                            ?>
                                            <td style="width: 28%;">Jumlah Diskon Nota</td>
                                            <td style="width: 2%;"> : </td>
                                            <td style="width: 70%;">Rp {{ $total_diskon_format }}</td>
                                            <input type="hidden" name="total_diskon" id="total_diskon" value="{{ $total_diskon }}">
                                        </tr>
                                        <tr>
                                            <td colspan="3" style="height: 1px;padding: 0px;">
                                                -------------------------------------------------------------------------------------------- <b>-</b>
                                            </td>
                                        </tr>
                                        <tr>
                                            <?php
                                                $total_3_format = number_format($total_3,0,',',',');
                                            ?>
                                            <td style="width: 28%;"><b>Total Penjualan</b></td>
                                            <td style="width: 2%;"><b> : </b></td>
                                            <td style="width: 70%;"><b>Rp {{ $total_3_format }}</b></td>
                                            <input type="hidden" name="total_penjualan" id="total_penjualan" value="{{ $total_3 }}">
                                        </tr>
                                        <tr>
                                            <?php
                                                $a_format = number_format($penjualan2->total_jasa_dokter,0,',',',');
                                            ?>
                                            <td style="width: 28%;">Total Jasa Dokter</td>
                                            <td style="width: 2%;"> : </td>
                                            <td style="width: 70%;">Rp {{ $a_format }}</td>
                                            <input type="hidden" name="total_jasa_dokter" id="total_jasa_dokter" value="{{ $penjualan2->total_jasa_dokter }}">
                                        </tr>
                                        <tr>
                                            <?php
                                                $b_format = number_format($penjualan2->total_jasa_resep,0,',',',');
                                            ?>
                                            <td style="width: 28%;">Total Jasa Resep</td>
                                            <td style="width: 2%;"> : </td>
                                            <td style="width: 70%;">Rp {{ $b_format }}</td>
                                            <input type="hidden" name="total_jasa_resep" id="total_jasa_resep" value="{{ $penjualan2->total_jasa_resep }}">
                                        </tr>
                                        <tr>
                                            <?php
                                                $c_format = number_format($penjualan2->total_paket_wd,0,',',',');
                                            ?>
                                            <td style="width: 28%;">Total Paket WT</td>
                                            <td style="width: 2%;"> : </td>
                                            <td style="width: 70%;">Rp {{ $c_format }}</td>
                                            <input type="hidden" name="total_paket_wd" id="total_paket_wd" value="{{ $penjualan2->total_paket_wd }}">
                                        </tr>
                                        <tr>
                                            <?php
                                                $d_format = number_format($penjualan2->total_lab,0,',',',');
                                            ?>
                                            <td style="width: 28%;">Total Lab</td>
                                            <td style="width: 2%;"> : </td>
                                            <td style="width: 70%;">Rp {{ $d_format }}</td>
                                            <input type="hidden" name="total_lab" id="total_lab" value="{{ $penjualan2->total_lab }}">
                                        </tr>
                                        <tr>
                                            <?php
                                                $e_format = number_format($penjualan2->total_apd,0,',',',');
                                            ?>
                                            <td style="width: 28%;">Total APD</td>
                                            <td style="width: 2%;"> : </td>
                                            <td style="width: 70%;">Rp {{ $e_format }}</td>
                                            <input type="hidden" name="total_apd" id="total_apd" value="{{ $penjualan2->total_apd }}">
                                        </tr>
                                        <tr>
                                            <td colspan="3" style="height: 1px;padding: 0px;">
                                                -------------------------------------------------------------------------------------------- <b>+</b>
                                            </td>
                                        </tr>
                                        <tr>
                                            <?php
                                                $f_format = number_format($grand_total,0,',',',');
                                                $g_format = number_format($penjualan2->total_debet,0,',',',');
                                                $h_format = number_format($total_cash,0,',',',');
                                            ?>
                                            <td style="width: 28%;">
                                                <b>Total I</b><br>
                                                <small>Total Debet/Credit   : Rp {{ $g_format }}</small><br>
                                                <small>Total Cash : Rp {{ $h_format }}</small><br>
                                            </td>
                                            <td style="width: 2%;"><b> : </b></td>
                                            <td style="width: 70%;"><b>Rp {{ $f_format }}</b></td>
                                            <input type="hidden" name="total_debet" id="total_debet" value="{{ $penjualan2->total_debet }}">
                                            <input type="hidden" name="total_penjualan_cash" id="total_penjualan_cash" value="{{ $total_cash }}">
                                        </tr>
                                        <tr>
                                            <?php
                                                $i_format = number_format($total_cn,0,',',',');
                                                $j_format = number_format($total_penjualan_cn_debet,0,',',',');
                                                $k_format = number_format($total_penjualan_cn_cash,0,',',',');
                                            ?>
                                            <td style="width: 28%;">
                                                <b>Total Retur</b><br>
                                                <small>Total Retur Debet/Credit   : Rp {{ $j_format }}</small><br>
                                                <small>Total Retur Cash : Rp {{ $k_format }}</small><br>
                                            </td>
                                            <td style="width: 2%;"><b> : </b></td>
                                            <td style="width: 70%;"><b>Rp {{ $i_format }}</b></td>
                                            <input type="hidden" name="total_penjualan_cn" id="total_penjualan_cn" value="{{ $total_cn }}">
                                            <input type="hidden" name="total_penjualan_cn_cash" id="total_penjualan_cn_cash" value="{{ $total_penjualan_cn_cash }}">
                                            <input type="hidden" name="total_penjualan_cn_debet" id="total_penjualan_cn_debet" value="{{ $total_penjualan_cn_debet }}">
                                        </tr>
                                        <tr>
                                            <td colspan="3" style="height: 1px;padding: 0px;">
                                                -------------------------------------------------------------------------------------------- <b>-</b>
                                            </td>
                                        </tr>
                                        <?php
                                            $total_2 = $grand_total-$total_cn;
                                            $total_debet_x = $penjualan2->total_debet-$total_penjualan_cn_debet;
                                            $total_cash_x = $total_cash-$total_penjualan_cn_cash;
                                        ?>
                                        <?php
                                                $l_format = number_format($total_2,0,',',',');
                                                $m_format = number_format($total_debet_x,0,',',',');
                                                $n_format = number_format($total_cash_x,0,',',',');
                                            ?>
                                        <tr>
                                            <td style="width: 28%;">
                                                <b>Total II</b><br>
                                                <small>Total Debet/Credit   : Rp {{ $m_format }}</small><br>
                                                <small>Total Cash : Rp {{ $n_format }}</small><br>
                                                <input type="hidden" name="total_cash_new" id="total_cash_new" value="{{ $total_cash_x }}">
                                            </td>
                                            <td style="width: 2%;"><b> : </b></td>
                                            <td style="width: 70%;"><b>Rp {{ $l_format }}</b></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <hr style="border: 1px solid #004d40; padding: 0px; margin-top: 0px; margin-bottom: 10px;">
                            <div class="row">
                                <div class="form-group  col-md-3">
                                    {!! Form::label('total_switch_cash', 'Switch Cash ke Debet') !!}
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        {!! Form::text('total_switch_cash', $penjualan_closing->total_switch_cash, array('class' => 'form-control', 'id' => 'total_switch_cash')) !!}
                                    </div>
                                </div>  
                                <div class="form-group  col-md-3">
                                    {!! Form::label('uang_seharusnya', 'Jumlah Uang Seharusnya') !!}
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        {!! Form::text('uang_seharusnya', null, array('id' => 'uang_seharusnya', 'class' => 'form-control', 'readonly' => 'readonly')) !!}
                                    </div>
                                </div>
                                <div class="form-group  col-md-3">
                                    {!! Form::label('jumlah', 'Jumlah TT') !!}
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        {!! Form::text('jumlah_tt', $penjualan_closing->jumlah_tt, array('class' => 'form-control', 'id' => 'jumlah_tt', 'readonly' => 'readonly')) !!}
                                    </div>
                                </div>
                                <div class="form-group  col-md-3">
                                    {!! Form::label('total_akhir', 'Total Uang Cash') !!}
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        {!! Form::text('total_akhir', $penjualan_closing->total_akhir, array('class' => 'form-control', 'id' => 'total_akhir')) !!}
                                    </div>
                                </div>  
                            </div>
                        </form> 
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="form-group col-md-6">
                        <div id="qz-connection" class="panel panel-default">
                            <div class="panel-heading">
                                <button class="close tip" data-toggle="tooltip" title="Launch QZ" id="launch" href="#" onclick="launchQZ();" style="display: none;">
                                    <i class="fa fa-external-link"></i>
                                </button>
                                <p class="panel-title">
                                    Connection: <span id="status_qz" class="text-muted" style="font-weight: bold;">Unknown</span>
                                </p>
                            </div>

                            <div class="panel-body">
                                <div class="btn-toolbar">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-success btn-sm" onclick="startConnection();">Connect</button>
                                        <button type="button" class="btn btn-warning btn-sm" onclick="endConnection();">Disconnect</button>
                                    </div>
                                    <!-- <button type="button" class="btn btn-info" onclick="listNetworkInfo();">List Network Info</button> -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-md-6">
                        <div class="panel panel-primary">
                            <div class="panel-body">
                                <div class="form-group">
                                    <label>Current printer:</label>
                                    <div id="configPrinter">NONE</div>
                                </div>
                                <div class="btn-toolbar">
                                    <div class="form-group">
                                        <div class="btn-group" role="group">
                                            
                                            <?php
                                                if(empty($penjualan_closing->id)) {
                                                    $value = 0;
                                                } else {
                                                    $value = $penjualan_closing->id;
                                                }
                                            ?>
                                            <button class="btn btn-success btn-sm" type="button" onClick="submit_valid({{ $value }})" data-toggle="tooltip" data-placement="top" title="Simpan data"><i class="fa fa-save"></i> Simpan</button>
                                            @if($apotek->id_printer == 1) 
                                            <button class="btn btn-primary btn-sm" type="button" onclick="print_closing_kasir();" data-toggle="tooltip" data-placement="top" title="Cetak nota"><i class="fa fa-print"></i> Cetak</button>
                                             <button class="btn btn-primary btn-sm" type="button" onClick="print_closing_kasir_thermal({{ $value }})" data-toggle="tooltip" data-placement="top" title="Cetak nota"><i class="fa fa-print"></i> Cetak Thermal</button>
                                            @else
                                             <button class="btn btn-primary btn-sm" type="button" onClick="print_closing_kasir_thermal({{ $value }})" data-toggle="tooltip" data-placement="top" title="Cetak nota"><i class="fa fa-print"></i> Cetak</button>
                                            @endif
                                            <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal"><i class="fa fa-undo"></i>Kembali</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
     </div>
</div>

<style type="text/css">
    #tb_temp_order_filter{
        display: block!important;
    }
    #tb_temp_order_length{
        display: block!important;
    }
    .hilangkan select{
        display: none!important;
    }
</style>

<script type="text/javascript">
    var token = '{{csrf_token()}}';

    $(document).ready(function(){
        var id = $("#id").val();

        if(id != '') {
            $("#cetakID").css("display", "block");
        }

        var a= $("#total_cash_new").val();
        var b= $("#total_switch_cash").val();
        if(b == '') {
            b = 0;
        }
        var xyz= parseFloat(a) - parseFloat(b);
        $("#uang_seharusnya").val(xyz);

        var total_akhir  = $("#total_akhir").val();
        if(total_akhir == 0 || total_akhir == null) {
            var jumlah_tt  = $("#jumlah_tt").val();
            if (jumlah_tt) {
                var uang_seharusnya = $("#uang_seharusnya").val();
                var total = parseFloat(jumlah_tt) +  parseFloat(uang_seharusnya);
                $("#total_akhir").val(total);
            }
        } else {
            var uang_seharusnya = $("#uang_seharusnya").val();
            var tt = parseFloat(total_akhir) -  parseFloat(uang_seharusnya);
            $("#jumlah_tt").val(tt);
        }

        $("#total_switch_cash").keyup(function() {
            var a= $("#total_cash_new").val();
            var b= $("#total_switch_cash").val();
            var xyz= parseFloat(a) - parseFloat(b);
            
           // var x = parseFloat(uang_seharusnya) - parseFloat(total_switch_cash);
            $("#uang_seharusnya").val(xyz);
        });

        $("#total_switch_cash").keypress(function(event){
            if (event.which == '10' || event.which == '13') {
                $("#total_akhir").focus();
                event.preventDefault();
            }
        });

        $("#total_akhir").keyup(function() {
            var total_uang_cash     = $("#total_akhir").val();
            var uang_seharusnya = $("#uang_seharusnya").val();
            
            var jumlah_tt_new = parseFloat(total_uang_cash) - parseFloat(uang_seharusnya);
            $("#jumlah_tt").val(jumlah_tt_new);
        });
    })

    function print_closing_kasir_thermal(id) {
        window.open("https://apotekeren.id/penjualan/print_closing_kasir_thermal/"+id, '_blank');
        //window.open("print_closing_kasir_thermal/"+id);
    }

    function submit_valid(id){
        if($(".validated_form").valid()) {
            data = {};
            $("#form-closing").find("input[name], select").each(function (index, node) {
                data[node.name] = node.value;
                
            });

            data['_token'] = "{{csrf_token()}}";

            if(id == 0) {
                $.ajax({
                    type:"POST",
                    url : '{{url("penjualan_closing/")}}',
                    dataType : "json",
                    data : data,
                    beforeSend: function(data){
                        // replace dengan fungsi loading
                    },
                    success:  function(data){
                        if(data.status ==1){
                            show_info("Data closing penjualan berhasil disimpan!");
                            $('#modal-xl').modal('toggle');
                            $("#cetakID").css("display", "block");
                        }else{
                            show_error("Gagal menyimpan data closing kasir!");
                            return false;
                        }
                    },
                    complete: function(data){
                        // replace dengan fungsi mematikan loading
                        tb_penjualan.fnDraw(false);
                    },
                    error: function(data) {
                        show_error("error ajax occured!");
                    }
                })
            } else {
                $.ajax({
                    type:"PUT",
                    url : '{{url("penjualan_closing/")}}/'+id,
                    dataType : "json",
                    data : data,
                    beforeSend: function(data){
                        // replace dengan fungsi loading
                    },
                    success:  function(data){
                        if(data.status ==1){
                            show_info("Data closing penjualan berhasil disimpan!");
                            $('#modal-xl').modal('toggle');
                            $("#cetakID").css("display", "block");
                        }else{
                            show_error("Gagal menyimpan data closing kasir!");
                            return false;
                        }
                    },
                    complete: function(data){
                        // replace dengan fungsi mematikan loading
                        tb_penjualan.fnDraw(false);
                    },
                    error: function(data) {
                        show_error("error ajax occured!");
                    }
                })
            }
        } else {
            return false;
        }
    }
</script>

