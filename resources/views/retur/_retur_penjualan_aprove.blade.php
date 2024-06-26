<form id="form-aproval" class="validated_form">
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list"></i>
                        Detail Data Retur
                    </h3>
                </div>
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
                    <input type="hidden" name="id_detail" id="id_detail" value="{{ $detail_penjualan->id }}">
                    <div class="row">
                        <?php
                            $nama_apotek = strtoupper($apotek->nama_panjang);
                            ?>
                        <table width="100%">
                            <tr>
                                <td width="27%">Apotek</td>
                                <td width="2%"> : </td>
                                <td width="70">APOTEK BWF-{{ $nama_apotek }}</td>
                            </tr>
                            <tr>
                                <td colspan="3"><hr style="border: 1px solid #004d40; padding: 0px; margin-top: 0px; margin-bottom: 10px;"></td>
                            </tr>
                            <tr>
                                <td width="27%">No Nota</td>
                                <td width="2%"> : </td>
                                <td width="70">{{$apotek->nama_singkat }}-{{ $penjualan->id }}</td>
                            </tr>
                            <tr>
                                <td width="27%">Tanggal</td>
                                <td width="2%"> : </td>
                                <td width="70">{{ $penjualan->created_at }}</td>
                            </tr>
                            <tr>
                                <td colspan="3"><hr style="border: 1px solid #004d40; padding: 0px; margin-top: 0px; margin-bottom: 10px;"></td>
                            </tr>
                            <tr class="bg-info">
                                <td width="27%">Kasir</td>
                                <td width="2%"> : </td>
                                <td width="70">{{ $penjualan->created_oleh->nama }}</td>
                            </tr>
                            @if($penjualan->id_pasien == '' OR $penjualan->id_pasien == null)
                            <tr class="bg-info">
                                <td width="27%">Pasien</td>
                                <td width="2%"> : </td>
                                <td width="70">Umum</td>
                            </tr>
                            @else
                            <tr class="bg-info">
                                <td width="27%">Pasien</td>
                                <td width="2%"> : </td>
                                <td width="70">{{$penjualan->pasien->nama }}</td>
                            </tr>
                            @endif
                            <?php 
                                $total_1 = $detail_penjualan->jumlah * $detail_penjualan->harga_jual;
                                $total_2 = $total_1 - $detail_penjualan->diskon;
                                $harga_jual = number_format($detail_penjualan->harga_jual,0,',',',');
                                $diskon = number_format($detail_penjualan->diskon,0,',',',');
                                $total_2 = number_format($total_2,0,',',',');
                            ?>s
                            <tr class="bg-info">
                                <td width="27%">Tanggal Retur</td>
                                <td width="2%"> : </td>
                                <td width="70">{{ $detail_penjualan->cn_at }}</td>
                            </tr>
                            <tr>
                                <td colspan="3"><hr style="border: 1px solid #004d40; padding: 0px; margin-top: 0px; margin-bottom: 10px;"></td>
                            </tr>
                            <tr class="bg-info">
                                <td width="27%">Detail Retur</td>
                                <td width="2%"> : </td>
                                <td width="70%">{{ $detail_penjualan->obat->nama }}</td>
                            </tr>
                            <tr class="bg-info">
                                <td width="27%"></td>
                                <td width="2%"></td>
                                <td width="70%"><b class="text-red">{{ $detail_penjualan->jumlah }}</b>&nbsp;x&nbsp;{{ $harga_jual }} (-{{ $diskon }})&nbsp;=&nbsp;Rp&nbsp;{{ $total_2 }}</td>
                            </tr>
                            <tr>
                                <td colspan="3"><hr style="border: 1px solid #004d40; padding: 0px; margin-top: 0px; margin-bottom: 10px;"></td>
                            </tr>
                            <tr class="bg-info">
                                <td width="13%">Uang Kembali</td>
                                <td width="2%"> : </td>
                                <td width="70">Rp&nbsp;{{ $total_2 }}</td>
                            </tr>
                            <tr>
                                <td colspan="3"><hr style="border: 1px solid #004d40; padding: 0px; margin-top: 0px; margin-bottom: 10px;"></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-success" type="button" onClick="aprove({{ $detail_penjualan->id }}, 1)" data-toggle="tooltip" data-placement="top" title="Simpan"><i class="fa fa-check"></i> Setuju</button>
                    <button class="btn btn-warning" type="button" onClick="aprove({{ $detail_penjualan->id }}, 2)" data-toggle="tooltip" data-placement="top" title="Simpan"><i class="fa fa-times"></i> Tidak Setuju</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-undo"></i> Kembali</button>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function(){
        })
    </script>
</form>