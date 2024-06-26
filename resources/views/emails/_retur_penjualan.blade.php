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
                                       Anda menerima pengajuan retur barang dengan rincian sebegai berikut: 
                                    </p>
                                 </td>
                              </tr>
                              <hr>
                              <tr>
                                <td>
                                     <table width="100%">
                                         <tbody>
                                             <tr>
                                                 <td width="28%"><small>Kasir</small></td>
                                                 <td width="2%"><small>:</small></td>
                                                 <td width="70%"><small>{{ $data[0]['user']['nama'] }}</small></td>
                                             </tr>
                                             <tr>
                                                 <td width="28%"><small>Tanggal Pengajuan</small></td>
                                                 <td width="2%"><small>:</small></td>
                                                 <td width="70%"><small>{{ $data[0]['tanggal'] }}</small></td>
                                             </tr>
                                             <tr>
                                                 <td width="28%"><small>No Nota</small></td>
                                                 <td width="2%"><small>:</small></td>
                                                 <td width="70%"><small>{{ $data[0]['penjualan']['id'] }}</small></td>
                                             </tr>
                                             <tr>
                                                 <td width="28%"><small>Tanggal Nota</small></td>
                                                 <td width="2%"><small>:</small></td>
                                                 <td width="70%"><small>{{ $data[0]['tgl_penjualan'] }}</small></td>
                                             </tr>
                                             <tr>
                                                 <td width="28%"><small>Item</small></td>
                                                 <td width="2%"><small>:</small></td>
                                                 <td width="70%"><small>
                                                      <?php $no = 0;?>
                                                      @foreach($data[0]['detail'] as $obj)
                                                      <?php 
                                                         $no++;
                                                         $jumlah = $obj['jumlah'] - $obj['jumlah_cn'];
                                                         $total = ($obj['jumlah_cn'] * $obj['harga_jual']) - $obj['diskon'];
                                                      ?>
                                                         <small>{{ $no }}. {{ $obj['nama_obat'] }} : {{ $obj['jumlah_cn'] }} x Rp {{ $obj['harga_jual'] }} - Rp {{ $obj['diskon'] }} = Rp {{ $total }}</small>
                                                         <?php
                                                          if($no != count($data[0]['detail'])) {
                                                            $br = '<br>';
                                                          } else {
                                                            $br = '';
                                                          }
                                                         ?>
                                                         {!! $br !!}
                                                      @endforeach
                                                 </td>
                                             </tr>
                                         </tbody>
                                     </table>
                                 </td>
                              </tr>
                              <tr>
                                 <small style="color: #E91E63;"><cite>Silakan lakukan aprove pengajuan retur pada menu <b>Penjualan</b>-<b>Aprove Retur</b>.</cite></small>
                               </tr>
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