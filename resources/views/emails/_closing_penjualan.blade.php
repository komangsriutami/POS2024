<!DOCTYPE html>
<html lang=&quot;en-US&quot;>
<head>
<meta charset=&quot;utf-8&quot;>
</head>
<body>
<table style="width:100%;height:100%;max-width:650px;border-spacing:0;border-collapse:collapse;margin:0 auto;background:#f2f2f2" align="center">
   <tbody>
      <tr>
         <td style="padding:2px">
            <table style="width:100%;height:100%;max-width:600px;border-spacing:0;border-collapse:collapse;border:1px solid #0097A7;margin:0 auto" align="center">
               <tbody>
                  <tr>
                     <td>
                        <table width="100%" align="center" style="border-spacing:0;border-collapse:collapse;width:100%">
                           <thead>
                              <tr bgcolor="#0097A7">
                                 <th>
                                    <h3 style="padding:20px;margin:0;box-sizing:border-box;color: #fafafa;">
                                       BWF POS SYSTEM
                                    </h3>
                                 </th>
                              </tr>
                           </thead>
                        </table>
                     </td>
                  </tr>
                  <tr bgcolor="white">
                     <td style="padding:20px 20px 10px">
                        <table style="border-spacing:0;border-collapse:collapse;width:100%">
                           <tbody>
                              <tr>
                                 <td><span style="color: #E91E63;"> Hi <b style="color: #E91E63;">{{ $data[0]['apoteker']['nama'] }}
                                    ,</b></span>
                                 </td>
                              </tr>
                              <tr>
                                 <td>
                                    <p style="color:#636363;line-height: 1.6;" align="justify">
                                       Transaksi hari ini sudah diclose, dengan rincian sebagai berikut: 
                                    </p>
                                 </td>
                              </tr>
                              <hr>
                              <tr>
                                <?php
                                    $nama_apotek = strtoupper($data[0]['apotek']->nama_panjang);
                                    $total_jasa_dokter = number_format($data[0]['penjualan_closing']->total_jasa_dokter_a,0,',',',');
                                    $total_jasa_resep = number_format($data[0]['penjualan_closing']->total_jasa_resep_a,0,',',',');
                                    $total_paket_wd = number_format($data[0]['penjualan_closing']->total_paket_wd_a,0,',',',');
                                    $total_penjualan = number_format($data[0]['penjualan_closing']->total_penjualan_a,0,',',',');
                                    $total_debet = number_format($data[0]['penjualan_closing']->total_debet_a,0,',',',');
                                    $total_penjualan_cash = number_format($data[0]['penjualan_closing']->total_penjualan_cash_a,0,',',',');
                                    $total_penjualan_cn = number_format($data[0]['penjualan_closing']->total_penjualan_cn_a,0,',',',');
                                    $total_penjualan_kredit = number_format($data[0]['penjualan_closing']->total_penjualan_kredit_a,0,',',',');
                                    $total_penjualan_kredit_terbayar = number_format($data[0]['penjualan_closing']->total_penjualan_kredit_terbayar_a,0,',',',');
                                    $total_diskon = number_format($data[0]['penjualan_closing']->total_diskon_a,0,',',',');
                                    $uang_seharusnya = number_format($data[0]['penjualan_closing']->uang_seharusnya_a,0,',',',');
                                    $total_akhir = number_format($data[0]['penjualan_closing']->total_akhir_a,0,',',',');
                                    $jumlah_tt = number_format($data[0]['penjualan_closing']->jumlah_tt_a,0,',',',');
                                ?>
                                <td>
                                     <table width="100%">
                                         <tbody>
                                              <tr>
                                                <td colspan="3" style="border: none;text-align: center;"><b>LAPORAN PENJUALAN HARIAN</b></td>
                                              </tr>
                                              <tr>
                                                <td colspan="3" style="border: none;text-align: center;"><b>APOTEK BWF-{{ $nama_apotek }}</b></td>
                                              </tr>
                                             <tr>
                                                 <td width="28%"><small>Tanggal</small></td>
                                                 <td width="2%"><small>:</small></td>
                                                 <td width="70%"><small>{{ $data[0]['tanggal'] }}</small></td>
                                             </tr>
                                             <tr>
                                                <td width="23%">Total Jasa Dokter</td>
                                                <td width="1%"> : </td>
                                                <td width="70%">Rp&nbsp;{{$total_jasa_dokter }}</td>
                                            </tr>
                                            <tr>
                                                <td width="23%">Total Jasa Dokter</td>
                                                <td width="1%"> : </td>
                                                <td width="70%">Rp&nbsp;{{$total_jasa_dokter }}</td>
                                            </tr>
                                            <tr>
                                                <td width="23%">Total Jasa Dokter</td>
                                                <td width="1%"> : </td>
                                                <td width="70%">Rp&nbsp;{{$total_jasa_dokter }}</td>
                                            </tr>
                                            <tr>
                                                <td width="23%">Total Jasa Resep</td>
                                                <td width="1%"> : </td>
                                                <td width="70%">Rp&nbsp;{{ $total_jasa_resep }}</td>
                                            </tr>
                                            <tr>
                                                <td width="23%">Total Paket WD</td>
                                                <td width="1%"> : </td>
                                                <td width="70%">Rp&nbsp;{{ $total_paket_wd }}</td>
                                            </tr>
                                            <tr>
                                                <td width="23%">Total Penjualan</td>
                                                <td width="1%"> : </td>
                                                <td width="70%">Rp&nbsp;{{$total_penjualan }}</td>
                                            </tr>
                                            <tr>
                                                <td width="23%">Total Debet/Credit</td>
                                                <td width="1%"> : </td>
                                                <td width="70%">Rp&nbsp;{{ $total_debet }}</td>
                                            </tr>
                                            <tr>
                                                <td width="23%">Total Penjualan Cash</td>
                                                <td width="1%"> : </td>
                                                <td width="70%">Rp&nbsp;{{ $total_penjualan_cash }}</td>
                                            </tr>
                                            <tr>
                                                <td width="23%">Total Retur</td>
                                                <td width="1%"> : </td>
                                                <td width="70%">Rp&nbsp;{{ $total_penjualan_cn }}</td>
                                            </tr>
                                            <tr>
                                                <td width="23%">Total Retur</td>
                                                <td width="1%"> : </td>
                                                <td width="70%">Rp&nbsp;{{ $total_penjualan_cn }}</td>
                                            </tr>
                                            <tr>
                                                <td width="23%">Total Penjualan K.</td>
                                                <td width="1%"> : </td>
                                                <td width="70%">Rp&nbsp;{{ $total_penjualan_kredit }}</td>
                                            </tr>
                                            <tr>
                                                <td width="23%">Total K. Terbayar</td>
                                                <td width="1%"> : </td>
                                                <td width="70%">Rp&nbsp;{{ $total_penjualan_kredit_terbayar }}</td>
                                            </tr>
                                            <tr>
                                                <td width="23%">Total Diskon Nota</td>
                                                <td width="1%"> : </td>
                                                <td width="70%">Rp&nbsp;{{ $total_diskon }}</td>
                                            </tr>
                                            <tr>
                                                <td width="23%">Uang Seharusnya</td>
                                                <td width="1%"> : </td>
                                                <td width="70%">Rp&nbsp;{{ $uang_seharusnya }}</td>
                                            </tr>
                                            <tr>
                                                <td width="23%">Total TT</td>
                                                <td width="1%"> : </td>
                                                <td width="70%">Rp&nbsp;{{ $jumlah_tt }}</td>
                                            </tr>
                                            <tr>
                                                <td width="23%">Total Akhir</td>
                                                <td width="1%"> : </td>
                                                <td width="70%">Rp&nbsp;{{ $total_akhir }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="3"><p>Catatan : K= Kredit, TT = Tidak Terdeteksi</p></td>
                                            </tr>
                                         </tbody>
                                     </table>
                                 </td>
                              </tr>
                              <!-- <tr>
                                 <small style="color: #E91E63;"><cite>Silakan lakukan aprove pengajuan retur pada menu <b>Penjualan</b>-<b>Aprove Retur</b>.</cite></small>
                               </tr> -->
                              <tr bgcolor="#757575" align="center">
                                <td style="padding:1px 2px">
                                    <p style="color: #FFFFFF;">~ Selamat Bekerja ~</p>
                                </td>
                            </tr>
                           </tbody>
                        </table>
                     </td>
                  </tr>
               </tbody>
            </table>
         </td>
      </tr>
   </tbody>
</table>
</body>
</html>