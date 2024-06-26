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
                                 <td><span style="color: #636363;"> Hi <b style="color: #636363;">{{ $data['nama']}}, </b></span>
                                 </td>
                              </tr>
                              <tr>
                                 <td>
                                     <p style="color:#636363;line-height: 1.6;" align="justify">
                                       Anda telah diundang oleh {{ $user->nama }} sebagai pengguna pada sistem APOTEKEREN.com. Silakan lengkapi data anda, agar anda dapat mulai menggunakan sistem APOTEKEREN sebagai pengguna. APOTEKEREN adalah sistem point of sale (POS) kesehatan yang akan membantu anda untuk mengelola data bisnis anda yang bergerak di bidang kesehatan.
                                    </p>
                                 </td>
                                 </td>
                              </tr>
                              <hr>
                              <tr>
                                 <td align="center">
                                    <div>
                                       <a href="{!! $link !!}" class="button">[Terima Undangan]</a>
                                    </div>
                                 </td>
                              </tr>
                              <tr>
                                 <td>
                                    <p>Jika link di atas tidak bekerja silahkan copy paste link dibawah ini ke jendela browser Anda. </p>
                                    <p>{!! $link !!}</p>
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