<!DOCTYPE html>
<html lang=&quot;en-US&quot;>
<head>
<meta charset=&quot;utf-8&quot;>
<style>
.button {
  background-color: #ff9e69;
  border: none;
  color: white;
  padding: 15px 32px;
  text-align: center;
  text-decoration: none;
  display: inline-block;
  font-size: 12px;
  margin: 4px 2px;
  cursor: pointer;
}
</style>
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
                                 <td><span style="color: #636363;"> Hi <b style="color: #636363;">{{ $data['dokter']['nama']}}, </b></span>
                                 </td>
                              </tr>
                              <tr>
                                 <td>
                                    <p style="color:#636363;line-height: 1.6;" align="justify">
                                       Terima kasih telah melakukan pendaftaran sebagai dokter! Klik link di bawah ini untuk melakukan aktivasi akun Anda.
                                    </p>
                                 </td>
                              </tr>
                              <hr>
                              <tr>
                                 <td align="center">
                                    <div>
                                       <a href="{!! $data['pesan'] !!}" class="button">[Klik Disini]</a>
                                    </div>
                                 </td>
                              </tr>
                              <tr>
                                 <td>
                                    <p>Jika link di atas tidak bekerja silahkan copy paste link dibawah ini ke jendela browser Anda. </p>
                                    <p>{!! $data['pesan'] !!}</p>
                                 </td>
                              </tr>
                              <hr>
                              <tr bgcolor="#757575" align="center">
                                <td style="padding:1px 2px">
                                    <p style="color: #FFFFFF;">~ Semoga Selalu Sehat & Berbahagia ~</p>
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