<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Cetak Nota Closing</title>
    </head>
    <style type="text/css">
        /*@font-face {
            font-family: 'dotmatrix7';
            src: url('{{ url('assets/dist/font/dotmatrix7/dot_digital-7-webfont.woff2') }}') format('woff2'),
                 url('{{ url('assets/dist/font/dotmatrix7/dot_digital-7-webfont.woff') }}') format('woff'),
                 url('{{ url('assets/dist/font/dotmatrix7/dot_digital-7.ttf')}}') format('truetype');
            font-weight: normal;
            font-style: normal;

        }*/

        * {
            font-size: 12px;
            font-family: 'Arial';
            margin-left:  7px;
            margin-right: 7px;
            margin-top: 1px;
            margin-bottom: 1px;
        }

        td,
        th,
        tr,
        table {
            border-top: 1px solid black;
            border-collapse: collapse;
        }

        td.description,
        th.description {
            width: 75px;
            max-width: 75px;
        }

        td.quantity,
        th.quantity {
            width: 40px;
            max-width: 40px;
            word-break: break-all;
        }

        td.price,
        th.price {
            width: 40px;
            max-width: 40px;
            word-break: break-all;
        }

        .centered {
            text-align: center;
            align-content: center;
        }

        .ticket {
            width: 270px;
            max-width: 270px;
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
        <a  href="{{ url('/penjualan') }}" class="hidden-print btn btn-sm btn-info" style="text-decoration:none;margin:0;color: #fff;background-color: #dc3545;border-color: #dc3545;box-shadow: none; font-size:10pt;">Back | Shift</a>
        <button id="btnPrint" class="hidden-print btn btn-sm btn-info" style="margin:0;color: #fff;background-color: #17a2b8;border-color: #17a2b8;box-shadow: none; font-size:10pt;">Print Nota | Ctrl+P</button>
        <br>
        <br>
        <br>
        <div class="ticket">
            <input type="hidden" name="id" id="id" value="{{ $penjualan_closing->id }}">
            <?php
                $nama_apotek = strtoupper($apotek->nama_panjang);
                $total_jasa_dokter = number_format($penjualan_closing->total_jasa_dokter,0,',',',');
                $total_jasa_resep = number_format($penjualan_closing->total_jasa_resep,0,',',',');
                $total_paket_wd = number_format($penjualan_closing->total_paket_wd,0,',',',');
                $total_penjualan = number_format($penjualan_closing->total_penjualan,0,',',',');
                $total_debet = number_format($penjualan_closing->total_debet,0,',',',');
                $total_penjualan_cash = number_format($penjualan_closing->total_penjualan_cash,0,',',',');
                $total_penjualan_cn = number_format($penjualan_closing->total_penjualan_cn,0,',',',');
                $total_penjualan_kredit = number_format($penjualan_closing->total_penjualan_kredit,0,',',',');
                $total_penjualan_kredit_terbayar = number_format($penjualan_closing->total_penjualan_kredit_terbayar,0,',',',');
                $total_diskon = number_format($penjualan_closing->total_diskon,0,',',',');
                $uang_seharusnya = number_format($penjualan_closing->uang_seharusnya,0,',',',');
                $total_akhir = number_format($penjualan_closing->total_akhir,0,',',',');
                $jumlah_tt = number_format($penjualan_closing->jumlah_tt,0,',',',');
            ?>
            <p align="center">APOTEK BWF-{{ $nama_apotek }}xxx</p>
            <p align="center">{{ $apotek->alamat }}</p>
            <p align="center">Telp. {{ $apotek->telepon }}</p>
            <p style="margin-left: 10px;">---------------------------------------------------------------</p>
            <p style="margin-left: 10px;">Kasir &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{$penjualan_closing->kasir->nama }}</p>
            <p style="margin-left: 10px;">Tanggal &nbsp;&nbsp;: {{ $penjualan_closing->created_at }}</p>
            <p style="margin-left: 10px;">---------------------------------------------------------------</p>
            <p style="margin-left: 10px;">Total Jasa Dokter  : Rp&nbsp;{{$total_jasa_dokter }}</p>
            <p style="margin-left: 10px;">Detail &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; : </p>
            <?php
                $i = 0;
            ?>
            @foreach($jasa_resep as $obj)
            <?php
                $i++;
                $jumlah_resep = $obj->jumlah_resep;
                $biaya = number_format($obj->biaya,0,',',',');
                $total_biaya_resep = number_format($obj->total_biaya_resep,0,',',',');
            ?>
            <p style="margin-left: 10px;">&nbsp;{{ $i }}. {{ $obj->nama_jasa_resep }}</p>
            <p style="margin-left: 10px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $jumlah_resep }} x Rp {{ $biaya }} = Rp {{ $total_biaya_resep }}</p>
            @endforeach
            <p style="margin-left: 10px;">---------------------------------------------------------------</p>
            <p style="margin-left: 10px;">Total Jasa Resep  : Rp&nbsp;{{ $total_jasa_resep }}</p>
            <p style="margin-left: 10px;">Detail &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; : </p>
            <?php
                $i = 0;
            ?>
            @foreach($jasa_dokter as $obj)
            <?php
                $i++;
                $total_biaya_jasa_dokter = number_format($obj->total_biaya_jasa_dokter,0,',',',');
                $fee = $obj->fee/100*$obj->total_biaya_jasa_dokter;
                $fee = number_format($fee,0,',',',');
            ?>
            <p style="margin-left: 10px;">&nbsp;{{ $i }}. {{ $obj->nama_dokter }}</p>
            <p style="margin-left: 10px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;({{ $obj->jumlah_transaksi }} transaksi)  = Rp {{ $total_biaya_jasa_dokter }}</p>
            <p style="margin-left: 10px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fee Dokter  = Rp {{ $fee }}</p>
            @endforeach
            <p style="margin-left: 10px;">---------------------------------------------------------------</p>
            <p style="margin-left: 10px;">Total Paket WD  &nbsp;&nbsp;&nbsp;: Rp&nbsp;{{ $total_paket_wd }}</p>
            <p style="margin-left: 10px;">Detail &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; : </p>
            <?php
                $i = 0;
            ?>
            @foreach($paket_wd as $obj)
            <?php
                $i++;
                $jumlah_paket = $obj->jumlah_paket;
                $total_harga_wd = number_format($obj->total_harga_wd,0,',',',');
                $harga = number_format($obj->harga,0,',',',');
            ?>
            <p style="margin-left: 10px;">&nbsp;{{ $i }}. {{ $obj->nama_paket }}</p>
            <p style="margin-left: 10px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $jumlah_paket }} x Rp {{ $harga }} = Rp {{ $total_harga_wd }}</p>
            @endforeach
            <p style="margin-left: 10px;">---------------------------------------------------------------</p>
            <p style="margin-left: 10px;">Total Penjualan &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: Rp&nbsp;{{$total_penjualan }}</p>
            <p style="margin-left: 10px;">Total Debet/Credit &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: Rp&nbsp;{{ $total_debet }}</p>
            <p style="margin-left: 10px;">Total Penjualan Cash &nbsp;: Rp&nbsp;{{ $total_penjualan_cash }}</p>
            <p style="margin-left: 10px;">Total Retur &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: Rp&nbsp;{{ $total_penjualan_cn }}</p>
            <p style="margin-left: 10px;">Total Penjualan K &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: Rp&nbsp;{{ $total_penjualan_kredit }}</p>
            <p style="margin-left: 10px;">Total K. Terbayar &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: Rp&nbsp;{{ $total_penjualan_kredit_terbayar }}</p>
            <p style="margin-left: 10px;">Total Diskon Nota &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: Rp&nbsp;{{ $total_diskon }}</p>
            <p style="margin-left: 10px;">Uang Seharusnya &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: Rp&nbsp;{{ $uang_seharusnya }}</p>
            <p style="margin-left: 10px;">Total TT &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: Rp&nbsp;{{ $jumlah_tt }}</p>
            <p style="margin-left: 0!important;"><strong>Total Akhir &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: Rp&nbsp;{{ $total_akhir }}</strong></p>
            <p style="margin-left: 10px;">---------------------------------------------------------------</p>
            <p style="margin-left: 10px;">Detail Debet &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; : </p>
            <?php
                $i = 0;
            ?>
            @foreach($penjualan_debet as $obj)
            <?php
                $i++;
                $total_debet = number_format($obj->total_debet,0,',',',');
            ?>
            <p style="margin-left: 10px;">&nbsp;{{ $i }}. {{ $obj->nama_kartu }}</p>
            <p style="margin-left: 10px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;({{ $obj->jumlah_transaksi }} transaksi)  = Rp {{ $total_debet }}</p>
            @endforeach
            <p style="margin-left: 10px;">---------------------------------------------------------------</p>
            <p style="margin-left: 10px;">Catatan : K= Kredit, TT = Tidak Terdeteksi</p>
           <!--  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; -->
            <p style="margin-left: 10px;">---------------------------------------------------------------</p>
            <p align="center">~ Selamat Bekerja ~</p>
            <p style="margin-left: 10px;">---------------------------------------------------------------</p>
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
                        window.location.href = "{{ url('/penjualan/') }}";
                    }
                })
            })
        </script>
    </body>

</html>