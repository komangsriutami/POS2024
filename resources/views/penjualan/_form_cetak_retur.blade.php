<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Cetak Nota Retur</title>
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
        <input type="hidden" name="id" id="id" value="{{ $penjualan->id }}">
        <a href="{{ url('/penjualan/detail/'.$penjualan->id) }}" class="hidden-print btn btn-sm btn-info" style="text-decoration:none;margin:0;color: #fff;background-color: #dc3545;border-color: #dc3545;box-shadow: none; font-size:10pt;">Back | Shift</a>
        <button id="btnPrint" class="hidden-print btn btn-sm btn-info" style="margin:0;color: #fff;background-color: #17a2b8;border-color: #17a2b8;box-shadow: none; font-size:10pt;">Print Nota | Ctrl+P</button>
        <br>
        <br>
        <br>
        <div class="ticket">
            <input type="hidden" name="id" id="id" value="{{ $penjualan->id }}">
            <?php
                $nama_apotek = strtoupper($apotek->nama_panjang);
            ?>
            <p align="center">APOTEK BWF-{{ $nama_apotek }}</p>
            <p align="center">{{ $apotek->alamat }}</p>
            <p align="center">Telp. {{ $apotek->telepon }}</p>
            <p align="center">----------------------------------------------------------------</p>
            <p style="margin-left: 10px;">No Nota : {{$apotek->nama_singkat }}-{{ $penjualan->id }}</p>
            <p style="margin-left: 10px;">Tanggal : {{ $penjualan->created_at }}</p>
            <p align="center">----------------------------------------------------------------</p>
            <p style="margin-left: 10px;">Kasir &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; : {{$detail_penjualan->retur->created_oleh->nama }}</p>
            @if($penjualan->id_pasien == '' OR $penjualan->id_pasien == null)
            <p style="margin-left: 10px;">Pasien &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; : &nbsp;Umum</p>
            @else
            <p style="margin-left: 10px;">Pasien &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; : {{$penjualan->pasien->nama }}</p>
            @endif
            <p style="margin-left: 10px;">Tanggal Retur &nbsp; : {{ $detail_penjualan->cn_at }}</p>
            <p align="center">----------------------------------------------------------------</p>
            <?php 
                $total_1 = $detail_penjualan->jumlah * $detail_penjualan->harga_jual;
                $total_2 = $total_1 - $detail_penjualan->diskon;
                $harga_jual = number_format($detail_penjualan->harga_jual,0,',',',');
                $diskon = number_format($detail_penjualan->diskon,0,',',',');
                $total_2 = number_format($total_2,0,',',',');
            ?>
            <p>&nbsp;{{ $detail_penjualan->obat->nama }}</p>
            <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $detail_penjualan->jumlah }}X{{ $harga_jual }} (-{{ $diskon }})&nbsp;=&nbsp;Rp&nbsp;{{ $total_2 }}</p>
            <p align="center">----------------------------------------------------------------</p>
            <p>Uang Kembali &nbsp;&nbsp; : Rp&nbsp;{{ $total_2 }}</p>

            <p align="center">----------------------------------------------------------------</p>
            <p align="center">Terimakasih Atas Kunjungan Anda</p>
            <p align="center">Semoga Lekas Sembuh</p>
            <p align="center">----------------------------------------------------------------</p>
        </div>
        {!! Html::script('assets/plugins/jquery/jquery.min.js') !!}
        <script type="text/javascript">
            const $btnPrint = document.querySelector("#btnPrint");
            $btnPrint.addEventListener("click", () => {
                window.print();
            });

            $(document).ready(function(){
                $(document).on("keyup", function(e){
                    var x = e.keyCode || e.which;
                    if (x == 16) {  
                        // fungsi shift 
                        var id = $("#id").val();
                        window.location.href = "{{ url('/penjualan/detail/') }}"+'/'+id;
                       
                    }
                })
            })
        </script>
    </body>

</html>