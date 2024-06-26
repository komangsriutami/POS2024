<div class="row">
    <div class="col-sm-12">
        <div class="card card-info card-outline">
            <div class="card-body">
                <?php
                    $total_1 = $data->jumlah_penjualan;
                    if($total_1 == 0) {
                        $total_1 = $data->total_penjualan+$data->total_diskon;
                    }
                    $total_3 = $total_1-$data->total_diskon;
                    $grand_total = $total_3+$data->total_jasa_dokter+$data->total_jasa_resep+$data->total_paket_wd+$data->total_lab+$data->total_apd;
                ?>
                <div class="row">
                    <div class="form-group col-md-12">
                        <table class="table_closing" style="width: 100%!important;">
                            <tbody>
                                <tr>
                                    <?php $total_k_format = number_format($data->total_penjualan_kredit,0,',',','); ?>
                                    <td style="width: 28%;border:none;"><b class="text-info">Total Penjualan Kredit</b></td>
                                    <td style="width: 2%;border:none;"><b class="text-info"> : </b></td>
                                    <td style="width: 70%;border:none;"><b class="text-info">Rp {{ $total_k_format }}</b></td>
                                </tr>
                                <tr>
                                    <td colspan="3" style="height: 1px;padding: 0px;border:none;">
                                        -------------------------------------------------------------------------------------------- <b></b>
                                    </td>
                                </tr>
                                <tr>
                                    <?php $total_1_format = number_format($data->jumlah_penjualan,0,',',','); ?>
                                    <td style="width: 28%;border:none;">Jumlah Penjualan</td>
                                    <td style="width: 2%;border:none;"> : </td>
                                    <td style="width: 70%;border:none;">Rp {{ $total_1_format }}</td>
                                </tr>
                                <tr>

                                    <?php $total_diskon_format = number_format($data->total_diskon,0,',',','); ?>
                                   <td style="width: 28%;border:none;">Jumlah Diskon Nota</td>
                                    <td style="width: 2%;border:none;"> : </td>
                                    <td style="width: 70%;border:none;">Rp {{ $total_diskon_format }}</td>
                                </tr>
                                <tr>
                                    <td colspan="3" style="height: 1px;padding: 0px;border:none;">
                                        -------------------------------------------------------------------------------------------- <b>-</b>
                                    </td>
                                </tr>
                                <tr>
                                    <?php $total_3_format = number_format($data->total_penjualan,0,',',','); ?>
                                    <td style="width: 28%;border:none;"><b>Total Penjualan</b></td>
                                    <td style="width: 2%;border:none;"><b> : </b></td>
                                    <td style="width: 70%;border:none;"><b>Rp {{ $total_3_format }}</b></td>
                                </tr>
                                <tr>
                                    <?php $a_format = number_format($data->total_jasa_dokter,0,',',','); ?>
                                   <td style="width: 28%;border:none;">Total Jasa Dokter</td>
                                    <td style="width: 2%;border:none;"> : </td>
                                    <td style="width: 70%;border:none;">Rp {{ $a_format }}</td>
                                </tr>
                                <tr>
                                    <?php $b_format = number_format($data->total_jasa_resep,0,',',','); ?>
                                    <td style="width: 28%;border:none;">Total Jasa Resep</td>
                                    <td style="width: 2%;border:none;"> : </td>
                                    <td style="width: 70%;border:none;">Rp {{ $b_format }}</td>
                                </tr>
                                <tr>
                                    <?php $c_format = number_format($data->total_paket_wd,0,',',','); ?>
                                    <td style="width: 28%;border:none;">Total Paket WT</td>
                                    <td style="width: 2%;border:none;"> : </td>
                                    <td style="width: 70%;border:none;">Rp {{ $c_format }}</td>
                                </tr>
                                <tr>
                                    <?php $d_format = number_format($data->total_lab,0,',',','); ?>
                                    <td style="width: 28%;border:none;">Total Lab</td>
                                    <td style="width: 2%;border:none;border:none;"> : </td>
                                    <td style="width: 70%;border:none;">Rp {{ $d_format }}</td>
                                </tr>
                                <tr>
                                    <?php $e_format = number_format($data->total_apd,0,',',','); ?>
                                    <td style="width: 28%;border:none;">Total APD</td>
                                    <td style="width: 2%;border:none;"> : </td>
                                    <td style="width: 70%;border:none;">Rp {{ $e_format }}</td>
                                </tr>
                                <tr>
                                    <td colspan="3" style="height: 1px;padding: 0px;border:none;">
                                        -------------------------------------------------------------------------------------------- <b>+</b>
                                    </td>
                                </tr>
                                <tr>
                                    <?php 
                                        $f_format = number_format($grand_total,0,',',',');
                                        $g_format = number_format($data->total_debet,0,',',',');
                                        $h_format = number_format($data->uang_seharusnya,0,',',',');
                                    ?>
                                   
                                    <td style="width: 28%;border:none;">
                                        <b>Total I</b><br>
                                        <small>Total Debet/Credit   : Rp {{ $g_format }}</small><br>
                                        <small>Total Cash : Rp {{ $h_format }}</small><br>
                                    </td>
                                    <td style="width: 2%;border:none;"><b> : </b></td>
                                    <td style="width: 70%;border:none;"><b>Rp {{ $f_format }}</b></td>
                                </tr>
                                <tr>
                                    <?php 
                                        $i_format = number_format($data->total_penjualan_cn,0,',',',');
                                        $j_format = number_format($data->total_penjualan_cn_debet,0,',',',');
                                        $k_format = number_format($data->total_penjualan_cn_cash,0,',',',');
                                    ?>
                                        <td style="width: 28%;border:none;">
                                        <b>Total Retur</b><br>
                                        <small>Total Retur Debet/Credit   : Rp {{ $j_format }}</small><br>
                                        <small>Total Retur Cash : Rp {{ $k_format }}</small><br>
                                    </td>
                                    <td style="width: 2%;border:none;"><b> : </b></td>
                                    <td style="width: 70%;border:none;"><b>Rp {{ $i_format }}</b></td>
                                </tr>
                                <tr>
                                    <td colspan="3" style="height: 1px;padding: 0px;border:none;">
                                        -------------------------------------------------------------------------------------------- <b>-</b>
                                    </td>
                                </tr>
                                    <?php 
                                        $total_2 = $grand_total-$data->total_penjualan_cn;
                                        $total_debet_x = $data->total_debet-$data->total_penjualan_cn_debet;
                                        $total_cash_x = $data->uang_seharusnya-$data->total_penjualan_cn_cash;
                                        $l_format = number_format($total_2,0,',',',');
                                        $m_format = number_format($total_debet_x,0,',',',');
                                        $n_format = number_format($total_cash_x,0,',',',');
                                    ?>
                    
                                <tr>
                                    <td style="width: 28%;border:none;">
                                        <b>Total II</b><br>
                                        <small>Total Debet/Credit   : Rp {{ $m_format }}</small><br>
                                        <small>Total Cash : Rp {{ $n_format }}</small><br>
                                    </td>
                                    <td style="width: 2%;border:none;"><b> : </b></td>
                                    <td style="width: 70%;border:none;"><b>Rp {{ $l_format }}</b></td>
                                </tr>
                                <tr>
                                    <?php  $o_format = number_format($data->total_switch_cash,0,',',','); ?>
                                    <td style="width: 28%;border:none;">Switch Cash ke Debet</td>
                                    <td style="width: 2%;border:none;"> : </td>
                                    <td style="width: 70%;border:none;">Rp {{ $o_format }}</td>
                                </tr>
                                <tr>
                                    <td colspan="3" style="height: 1px;padding: 0px;border:none;">
                                        -------------------------------------------------------------------------------------------- <b>-</b>
                                    </td>
                                </tr>
                                <tr>
                                    <?php $p_format = number_format($data->uang_seharusnya,0,',',','); ?>
                                    <td style="width: 28%;border:none;">Uang Seharusnya</td>
                                    <td style="width: 2%;border:none;"> : </td>
                                    <td style="width: 70%;border:none;">Rp {{ $p_format }}</td>
                                </tr>
                                <tr>
                                    <?php $q_format = number_format($data->jumlah_tt,0,',',','); ?>
                                    <td style="width: 28%;border:none;">TT</td>
                                    <td style="width: 2%;border:none;"> : </td>
                                    <td style="width: 70%;border:none;">Rp {{ $q_format }}</td>
                                </tr>
                                <tr>
                                    <td colspan="3" style="height: 1px;padding: 0px;border:none;">
                                        -------------------------------------------------------------------------------------------- <b>+</b>
                                    </td>
                                </tr>
                                <tr>
                                    <?php $r_format = number_format($data->total_akhir,0,',',','); ?>
                                    <td style="width: 28%;border:none;"><b>Total III (Total Cash Outlet)</b></td>
                                    <td style="width: 2%;border:none;"><b> : </b></td>
                                    <td style="width: 70%;border:none;"><b>Rp {{ $r_format }}</b></td>
                                </tr>
                                <tr>
                                    <?php $s_format = number_format($data->total_penjualan_kredit_terbayar,0,',',','); ?>
                                    <td style="width: 28%;border:none;">Penjualan Kredit Terbayar</td>
                                    <td style="width: 2%;border:none;"> : </td>
                                    <td style="width: 70%;border:none;">Rp {{ $s_format }}</td>
                                </tr>
                                <tr>
                                    <td colspan="3" style="height: 1px;padding: 0px;border:none;">
                                        -------------------------------------------------------------------------------------------- <b>+</b>
                                    </td>
                                </tr>
                                <tr>
                                    <?php 
                                        $new_total = $data->total_akhir+$data->total_penjualan_kredit_terbayar;
                                        $t_format = number_format($new_total,0,',',',');
                                    ?>
                                    <td style="width: 28%;border:none;"><b class="text-red">GRAND TOTAL</b></td>
                                    <td style="width: 2%;border:none;"><b class="text-red"> : </b></td>
                                    <td style="width: 70%;border:none;"><b class="text-red">Rp {{ $t_format }}</b></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-undo"></i> Kembali</button>
            </div>
        </div>
     </div>
</div>

