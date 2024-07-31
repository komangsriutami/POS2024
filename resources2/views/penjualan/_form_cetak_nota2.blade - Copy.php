<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="CP850">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Cetak Nota</title>
    </head>
    <style rel="stylesheet" media="print">
        @font-face {
            font-family: 'arial_monospaced_mt';
            src: url('{{ url('assets/dist/font/arial_monospaced_mt.ttf')}}') format('truetype');
            font-weight: normal;
            font-style: normal;

        }

      /*  @font-face {
            font-family: 'saxMono';
            src: url('{{ url('assets/dist/font/saxMono/saxMono.eot') }}');
            src: url('{{ url('assets/dist/font/saxMono/saxMono.eot?#iefix') }}') format('embedded-opentype'),
                url('{{ url('assets/dist/font/saxMono/saxMono.woff2') }}') format('woff2'),
                url('{{ url('assets/dist/font/saxMono/saxMono.woff') }}') format('woff') format('woff'),
                url('{{ url('assets/dist/font/saxMono/saxMono.saxMono.svg#saxMono') }}') format('svg');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }*/


        * {
            font-size: 11px;
            font-family: 'arial_monospaced_mt';
            margin-left:  1px;
            margin-right: 1px;
            margin-top: 0px;
            margin-bottom: 0px;
        }


        td,
        th,
        tr,
        table {
            /*border-top: 1px solid black;*/
            border-collapse: collapse;
        }

        .centered {
            text-align: center;
            align-content: center;
        }

        .ticket {
            width: 200px;
            max-width: 200px;
            background-color: white !important;
        }

        img {
            max-width: inherit;
            width: inherit;
        }

        @media print {
            .hidden-print,
            .hidden-print * {
                display: none !important;
            }

            /*body *:not(.printable, .printable *) {
            display: none;
            }
            .printable {
                position: absolute;
                top: 0;
                left: 0;
            }*/
        }



        .btn-sm {
            padding: .25rem .5rem;
            font-size: .875rem;
            line-height: 1.5;
            border-radius: .2rem;
        }

        .btn-info {
            color: #fff;
            background-color: #17a2b8;
            border-color: #17a2b8;
            box-shadow: none;
        }
        .btn {
            display: inline-block;
            font-weight: 400;
            color: #212529;
            text-align: center;
            vertical-align: middle;
            cursor: pointer;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            background-color: transparent;
            border: 1px solid transparent;
            padding: .375rem .75rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: .25rem;
            transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;
        }

    </style>
    <body>
        <input type="hidden" name="is_kredit" id="is_kredit" value="{{ $penjualan->is_kredit }}">
        
        @if($penjualan->is_kredit == 1)
        <a href="{{ url('/penjualan/create_credit') }}" class="hidden-print btn btn-sm btn-info" style="text-decoration:none;margin:0;color: #fff;background-color: #dc3545;border-color: #dc3545;box-shadow: none; font-size:10pt;">Back | Shift</a>
        @else
        <a  href="{{ url('/penjualan/create') }}" class="hidden-print btn btn-sm btn-info" style="text-decoration:none;margin:0;color: #fff;background-color: #dc3545;border-color: #dc3545;box-shadow: none; font-size:10pt;">Back | Shift</a>
        @endif
        <button id="btnPrint" class="hidden-print btn btn-sm btn-info" style="margin:0;color: #fff;background-color: #17a2b8;border-color: #17a2b8;box-shadow: none; font-size:10pt;">Print Nota | Ctrl+P</button>
        <br>
        <br>
        <br>
        <div class="ticket">
            <input type="hidden" name="id" id="id" value="{{ $penjualan->id }}">
            <?php
                $nama_apotek = strtoupper($apotek->nama_panjang);
            ?>
            <table width="100%">
                 <tr>
                    <td style="text-align: center;" colspan="2">Monaco AaBbCc</td>
                </tr>
                <tr>
                    <td style="text-align: center;" colspan="2">APOTEK BWF-{{ $nama_apotek }}</td>
                </tr>
                <tr>
                    <td style="text-align: center;" colspan="2">{{ $apotek->alamat }}</td>
                </tr>
                <tr>
                    <td style="text-align: center;" colspan="2">Telp. {{ $apotek->telepon }}</td>
                </tr>
                <tr>
                    <td colspan="2">------------------------------</td>
                </tr>
                <tr>
                    <td colspan="2">No Nota : {{$apotek->nama_singkat }}-{{ $penjualan->id }}</td>
                </tr>
                <tr>
                    <td colspan="2">Tanggal : {{ $penjualan->created_at }}</td>
                </tr>

                <tr>
                    <td colspan="2">------------------------------</td>
                </tr>
                @if($penjualan->is_penjualan_tanpa_item != 1)
                    <?php 
                        $no = 0; 
                        $total_belanja = 0;
                    ?>
                        
                    @foreach( $detail_penjualans as $obj )
                        <?php 
                            $no++;
                            $total_1 = $obj->jumlah * $obj->harga_jual;
                            $total_2 = $total_1 - $obj->diskon;
                            $total_belanja = $total_belanja + $total_2;
                            $harga_jual = number_format($obj->harga_jual,0,',',',');
                            $diskon = number_format($obj->diskon,0,',',',');
                            $total_2 = number_format($total_2,0,',',',');
                        ?>
                        <tr>
                            <td style="width: 5%!important;">{{ $no }}.</td>
                            <td style="width: 95%!important;">{{ $obj->nama }}</td>
                        </tr>
                        <tr>
                            <td style="width: 5%!important;"></td>
                            <td style="width: 95%!important;">{{ $obj->jumlah }}X{{ $harga_jual }} (-{{ $diskon }})&nbsp;=&nbsp;Rp&nbsp;{{ $total_2 }}</td>
                        </tr>
                    @endforeach
                         <tr>
                                <td colspan="2">------------------------------</td>
                            </tr>
                </table>
                 <table width="100%">
                    <?php
                            $total_diskon_persen = $penjualan->diskon_persen/100 * $total_belanja;
                            $total_belanja_bayar = $total_belanja - ($total_diskon_persen + $penjualan->diskon_rp);
                            $total_diskon = $total_diskon_persen+$penjualan->diskon_rp;
                            $total_belanja = $total_belanja+$penjualan->biaya_jasa_dokter;
                            $biaya_jasa_dokter = number_format($penjualan->biaya_jasa_dokter,0,',',',');
                            $biaya_lab = number_format($penjualan->biaya_lab,0,',',',');
                            $biaya_apd = number_format($penjualan->biaya_apd,0,',',',');
                        ?>
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
                        <tr>
                            <td colspan="2">------------------------------</td>
                        </tr>
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
                            $grand_total = $total_belanja-$total_diskon;
                            $grand_total_format = number_format($grand_total,0,',',',');
                        ?>

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
                            <tr>
                                <td>Bayar</td>
                                <td>Rp&nbsp;{{ $total_bayar_format }}</td>
                            </tr>
                            <tr>
                                <td>Kembalian</td>
                                <td>Rp&nbsp;{{ $kembalian_format }}</td>
                            </tr>
                    
                @endif
                <tr>
                        <td colspan="2">------------------------------</td>
                    </tr>
                    </tr>
                        <td colspan="2" align="center">Terimakasih Atas Kunjungan Anda</td>
                    </tr>
                    </tr>
                        <td colspan="2" align="center">Semoga Lekas Sembuh</td>
                    </tr>
                    <tr>
                        <td colspan="2">------------------------------</td>
                    </tr>
            </table>
            
        </div>
        {!! Html::script('assets/plugins/jquery/jquery.min.js') !!}
        <script type="text/javascript">
            const $btnPrint = document.querySelector("#btnPrint");
            $btnPrint.addEventListener("click", () => {
                window.print();
            });

            $(document).ready(function(){
                window.print();
                $(document).on("keyup", function(e){
                    
                    var x = e.keyCode || e.which;
                    if (x == 16) {  
                        // fungsi shift 
                        var is_kredit = $("#is_kredit").val();
                        if(is_kredit == 1) {
                            window.location.href = "{{ url('/penjualan/create_credit/') }}";
                        } else {
                            window.location.href = "{{ url('/penjualan/create/') }}";
                        }
                    }
                })
            })
        </script>
    </body>

</html>