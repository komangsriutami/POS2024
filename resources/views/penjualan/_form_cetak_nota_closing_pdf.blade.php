<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>APOTEKEREN | Invoice TO Print</title>
    </head>
    <body>
        <div class="wrapper">
             <?php
                $nama_apotek = strtoupper($apotek->nama_panjang);
                $total_jasa_dokter = number_format($penjualan_closing->total_jasa_dokter_a,0,',',',');
                $total_jasa_resep = number_format($penjualan_closing->total_jasa_resep_a,0,',',',');
                $total_paket_wd = number_format($penjualan_closing->total_paket_wd_a,0,',',',');
                $total_penjualan = number_format($penjualan_closing->total_penjualan_a,0,',',',');
                $total_debet = $penjualan_closing->total_debet_a + $penjualan_closing->total_switch_cash_a;
                $total_debet = number_format($total_debet,0,',',',');
                $total_penjualan_cash = number_format($penjualan_closing->total_penjualan_cash_a,0,',',',');
                $total_penjualan_cn = number_format($penjualan_closing->total_penjualan_cn_a,0,',',',');
                $total_penjualan_kredit = number_format($penjualan_closing->total_penjualan_kredit_a,0,',',',');
                $total_penjualan_kredit_terbayar = number_format($penjualan_closing->total_penjualan_kredit_terbayar_a,0,',',',');
                $total_diskon = number_format($penjualan_closing->total_diskon_a,0,',',',');
                $total_switch_cash = number_format($penjualan_closing->total_switch_cash_a,0,',',',');
                $uang_seharusnya = number_format($penjualan_closing->uang_seharusnya_a,0,',',',');
                $total_akhir = number_format($penjualan_closing->total_akhir_a,0,',',',');
                $jumlah_tt = number_format($penjualan_closing->jumlah_tt_a,0,',',',');
            ?>
            <!-- Main content -->
            <section class="invoice">
                <!-- title row -->
                <div class="row">
                    <div class="col-12">
                            <p style="font-weight: bolder;text-align: center;padding: none;margin: none;">APOTEK {{ $apotek->group_apotek->nama_singkat }} {{ $nama_apotek }}</p>
                            <p style="text-align: center;font-size: 10pt;padding: none;margin: none;">{{ $apotek->alamat }}</p>
                            <p style="text-align: center;font-size: 10pt;padding: none;margin: none;">Telp. {{ $apotek->telepon }}</p>
                            <p style="text-align: center;font-size: 10pt;padding: none;margin: none;">Website : www.apotekbwf.com</p>
                            <hr>
                            <h4 style="text-align: center;font-weight: bolder; font-size: 10pt;margin: none!important;padding: none!important;"><b>LAPORAN PENJUALAN HARIAN</b></h4>
                            <br>
                            <p style="text-align: center;margin-top: none!important;padding: none!important;font-size: 10pt;">Tanggal : {{ $tanggal }}</p>
                    </div>
                    <!-- /.col -->
                </div>
                <!-- Table row -->
               
                <div class="row">
                    <div class="col-12 table-responsive">
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <td width="29%" style="font-size: 10pt;">Total Jasa Dokter</td>
                                    <td width="1%" style="font-size: 10pt;"> : </td>
                                    <td width="70%" style="font-size: 10pt;">Rp&nbsp;{{$total_jasa_dokter }}</td>
                                </tr>
                                <tr>
                                    <td width="29%" style="font-size: 10pt;">Total Jasa Resep</td>
                                    <td width="1%" style="font-size: 10pt;"> : </td>
                                    <td width="70%" style="font-size: 10pt;">Rp&nbsp;{{ $total_jasa_resep }}</td>
                                </tr>
                                <tr>
                                    <td width="29%" style="font-size: 10pt;">Total Paket WD</td>
                                    <td width="1%" style="font-size: 10pt;"> : </td>
                                    <td width="70%" style="font-size: 10pt;">Rp&nbsp;{{ $total_paket_wd }}</td>
                                </tr>
                                <tr>
                                    <td width="29%" style="font-size: 10pt;">Total Penjualan</td>
                                    <td width="1%" style="font-size: 10pt;"> : </td>
                                    <td width="70%" style="font-size: 10pt;">Rp&nbsp;{{$total_penjualan }}</td>
                                </tr>
                                <tr>
                                    <td width="29%" style="font-size: 10pt;">Total Debet/Credit</td>
                                    <td width="1%" style="font-size: 10pt;"> : </td>
                                    <td width="70%" style="font-size: 10pt;">Rp&nbsp;{{ $total_debet }}  <span class="text-warning"><i>&nbsp;(sudah termasuk switch cash Rp&nbsp;{{ $total_switch_cash }})</i></span></td>
                                </tr>
                                <tr>
                                    <td width="29%" style="font-size: 10pt;">Total Penjualan Cash</td>
                                    <td width="1%" style="font-size: 10pt;"> : </td>
                                    <td width="70%" style="font-size: 10pt;">Rp&nbsp;{{ $total_penjualan_cash }}</td>
                                </tr>
                                <tr>
                                    <td width="29%" style="font-size: 10pt;">Total Retur</td>
                                    <td width="1%" style="font-size: 10pt;"> : </td>
                                    <td width="70%" style="font-size: 10pt;">Rp&nbsp;{{ $total_penjualan_cn }}</td>
                                </tr>
                                <tr>
                                    <td width="29%" style="font-size: 10pt;">Total Penjualan K.</td>
                                    <td width="1%" style="font-size: 10pt;"> : </td>
                                    <td width="70%" style="font-size: 10pt;">Rp&nbsp;{{ $total_penjualan_kredit }}</td>
                                </tr>
                                <tr>
                                    <td width="29%" style="font-size: 10pt;">Total K. Terbayar</td>
                                    <td width="1%" style="font-size: 10pt;"> : </td>
                                    <td width="70%" style="font-size: 10pt;">Rp&nbsp;{{ $total_penjualan_kredit_terbayar }}</td>
                                </tr>
                                <tr>
                                    <td width="29%" style="font-size: 10pt;">Total Diskon Nota</td>
                                    <td width="1%" style="font-size: 10pt;"> : </td>
                                    <td width="70%" style="font-size: 10pt;">Rp&nbsp;{{ $total_diskon }}</td>
                                </tr>
                                <!-- <tr>
                                    <td width="29%" style="font-size: 10pt;">Total Switch Cash</td>
                                    <td width="1%" style="font-size: 10pt;"> : </td>
                                    <td width="70%" style="font-size: 10pt;"></td>
                                </tr> -->
                                <tr>
                                    <td width="29%" style="font-size: 10pt;">Uang Seharusnya</td>
                                    <td width="1%" style="font-size: 10pt;"> : </td>
                                    <td width="70%" style="font-size: 10pt;">Rp&nbsp;{{ $uang_seharusnya }}</td>
                                </tr>
                                <tr>
                                    <td width="29%" style="font-size: 10pt;">Total TT</td>
                                    <td width="1%" style="font-size: 10pt;"> : </td>
                                    <td width="70%" style="font-size: 10pt;">Rp&nbsp;{{ $jumlah_tt }}</td>
                                </tr>
                                <tr>
                                    <td width="29%" style="font-size: 10pt;">Total Akhir</td>
                                    <td width="1%" style="font-size: 10pt;"> : </td>
                                    <td width="70%" style="font-size: 10pt;">Rp&nbsp;{{ $total_akhir }}</td>
                                </tr>
                                <tr>
                                    <td colspan="3" width="100%" style="font-size: 10pt;"><p>Catatan : K= Kredit, TT = Tidak Terdeteksi</p></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- /.col -->
                </div>
                <div class="row">
                    @foreach($rincians as $obj)
                   <!--  <div class="col-md-12">
                            <span style="text-align: center;font-weight: bolder; font-size: 10pt;margin: none!important;padding: none!important;"><b> Kasir : {{ $obj->kasir->nama }}</b></span>
                            <br>
                    </div> -->

                            <?php
                                $total_jasa_dokter_ = number_format($obj->total_jasa_dokter,0,',',',');
                                $total_jasa_resep_ = number_format($obj->total_jasa_resep,0,',',',');
                                $total_paket_wd_ = number_format($obj->total_paket_wd,0,',',',');
                                $total_penjualan_ = number_format($obj->total_penjualan,0,',',',');
                                $total_debet_ = $obj->total_debet + $obj->total_switch_cash;
                                $total_debet_ = number_format($total_debet_,0,',',',');
                                $total_penjualan_cash_ = number_format($obj->total_penjualan_cash,0,',',',');
                                $total_penjualan_cn_ = number_format($obj->total_penjualan_cn,0,',',',');
                                $total_penjualan_kredit_ = number_format($obj->total_penjualan_kredit,0,',',',');
                                $total_penjualan_kredit_terbayar_ = number_format($obj->total_penjualan_kredit_terbayar,0,',',',');
                                $total_diskon_ = number_format($obj->total_diskon,0,',',',');
                                $total_switch_cash_ = number_format($obj->total_switch_cash,0,',',',');
                                $uang_seharusnya_ = number_format($obj->uang_seharusnya,0,',',',');
                                $total_akhir_ = number_format($obj->total_akhir,0,',',',');
                                $jumlah_tt_ = number_format($obj->jumlah_tt,0,',',',');
                            ?>
                    <div class="col-md-12">
                            <table class="table table-bordered" style="border-color: red;border:1px solid blue!important;">
                                <tr>
                                    <td width="29%" style="font-size: 10pt;">Kasir</td>
                                    <td width="1%" style="font-size: 10pt;"> : </td>
                                    <td width="70%" style="font-size: 10pt;">{{ $obj->kasir->nama }}</td>
                                </tr>
                                <tr>
                                    <td width="29%" style="font-size: 10pt;">Total Jasa Dokter</td>
                                    <td width="1%" style="font-size: 10pt;"> : </td>
                                    <td width="70%" style="font-size: 10pt;">Rp&nbsp;{{$total_jasa_dokter_ }}</td>
                                </tr>
                                <tr>
                                    <td width="29%" style="font-size: 10pt;">Total Jasa Resep</td>
                                    <td width="1%" style="font-size: 10pt;"> : </td>
                                    <td width="70%" style="font-size: 10pt;">Rp&nbsp;{{ $total_jasa_resep_ }}</td>
                                </tr>
                                <tr>
                                    <td width="29%" style="font-size: 10pt;">Total Paket WD</td>
                                    <td width="1%" style="font-size: 10pt;"> : </td>
                                    <td width="70%" style="font-size: 10pt;">Rp&nbsp;{{ $total_paket_wd_ }}</td>
                                </tr>
                                <tr>
                                    <td width="29%" style="font-size: 10pt;">Total Penjualan</td>
                                    <td width="1%" style="font-size: 10pt;"> : </td>
                                    <td width="70%" style="font-size: 10pt;">Rp&nbsp;{{$total_penjualan_ }}</td>
                                </tr>
                                <tr>
                                    <td width="29%" style="font-size: 10pt;">Total Debet/Credit</td>
                                    <td width="1%" style="font-size: 10pt;"> : </td>
                                    <td width="70%" style="font-size: 10pt;">Rp&nbsp;{{ $total_debet_ }}  <span class="text-warning"><i>&nbsp;(sudah termasuk switch cash Rp&nbsp;{{ $total_switch_cash_ }})</i></span></td>
                                </tr>
                                <tr>
                                    <td width="29%" style="font-size: 10pt;">Total Penjualan Cash</td>
                                    <td width="1%" style="font-size: 10pt;"> : </td>
                                    <td width="70%" style="font-size: 10pt;">Rp&nbsp;{{ $total_penjualan_cash_ }}</td>
                                </tr>
                                <tr>
                                    <td width="29%" style="font-size: 10pt;">Total Retur</td>
                                    <td width="1%" style="font-size: 10pt;"> : </td>
                                    <td width="70%" style="font-size: 10pt;">Rp&nbsp;{{ $total_penjualan_cn_ }}</td>
                                </tr>
                                <tr>
                                    <td width="29%" style="font-size: 10pt;">Total Penjualan K.</td>
                                    <td width="1%" style="font-size: 10pt;"> : </td>
                                    <td width="70%" style="font-size: 10pt;">Rp&nbsp;{{ $total_penjualan_kredit_ }}</td>
                                </tr>
                                <tr>
                                    <td width="29%" style="font-size: 10pt;">Total K. Terbayar</td>
                                    <td width="1%" style="font-size: 10pt;"> : </td>
                                    <td width="70%" style="font-size: 10pt;">Rp&nbsp;{{ $total_penjualan_kredit_terbayar_ }}</td>
                                </tr>
                                <tr>
                                    <td width="29%" style="font-size: 10pt;">Total Diskon Nota</td>
                                    <td width="1%" style="font-size: 10pt;"> : </td>
                                    <td width="70%" style="font-size: 10pt;">Rp&nbsp;{{ $total_diskon_ }}</td>
                                </tr>
                                <!-- <tr>
                                    <td width="29%" style="font-size: 10pt;">Total Switch Cash</td>
                                    <td width="1%" style="font-size: 10pt;"> : </td>
                                    <td width="70%" style="font-size: 10pt;">Rp&nbsp;{{ $total_switch_cash_ }}</td>
                                </tr> -->
                                <tr>
                                    <td width="29%" style="font-size: 10pt;">Uang Seharusnya</td>
                                    <td width="1%" style="font-size: 10pt;"> : </td>
                                    <td width="70%" style="font-size: 10pt;">Rp&nbsp;{{ $uang_seharusnya_ }}</td>
                                </tr>
                                <tr>
                                    <td width="29%" style="font-size: 10pt;">Total TT</td>
                                    <td width="1%" style="font-size: 10pt;"> : </td>
                                    <td width="70%" style="font-size: 10pt;">Rp&nbsp;{{ $jumlah_tt_ }}</td>
                                </tr>
                                <tr>
                                    <td width="29%" style="font-size: 10pt;">Total Akhir</td>
                                    <td width="1%" style="font-size: 10pt;"> : </td>
                                    <td width="70%" style="font-size: 10pt;">Rp&nbsp;{{ $total_akhir_ }}</td>
                                </tr>
                            </table>
                             <br>
                    </div>
                    <!-- /.col -->
                     @endforeach
                    
                </div>
                <!-- /.row -->
            </section>
            <!-- /.content -->
        </div>
        <!-- ./wrapper -->
    </body>
</html>