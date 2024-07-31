<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Cetak Nota</title>
    </head>
    <style type="text/css">
        @font-face {
            font-family: 'dotmatrix7';
            src: url('{{ url('assets/dist/font/dotmatrix7/dot_digital-7-webfont.woff2') }}') format('woff2'),
                 url('{{ url('assets/dist/font/dotmatrix7/dot_digital-7-webfont.woff') }}') format('woff'),
                 url('{{ url('assets/dist/font/dotmatrix7/dot_digital-7.ttf')}}') format('truetype');
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
            font-size: 12px;
            font-family: 'dotmatrix7';
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
            <p align="center">APOTEK BWF-{{ $nama_apotek }}</p>
            <p align="center">{{ $apotek->alamat }}</p>
            <p align="center">Telp. {{ $apotek->telepon }}</p>
            <p style="margin-left: 10px;">---------------------------------------------------------------</p>
            <p style="margin-left: 10px;">No Nota : {{$apotek->nama_singkat }}-{{ $penjualan->id }}</p>
            <p style="margin-left: 10px;">Tanggal : {{ $penjualan->created_at }}</p>
            <p style="margin-left: 10px;">---------------------------------------------------------------</p>
                  
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
                        <p style="margin-left: 10px;">{{ $no }}.{{ $obj->nama }}</p>
                        <p style="margin-left: 10px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $obj->jumlah }}X{{ $harga_jual }} (-{{ $diskon }})&nbsp;=&nbsp;Rp&nbsp;{{ $total_2 }}</p>
                    @endforeach

                    
                
            @endif
            <p style="margin-left: 10px;">---------------------------------------------------------------</p>
            <?php
                $total_diskon_persen = $penjualan->diskon_persen/100 * $total_belanja;
                $total_belanja_bayar = $total_belanja - ($total_diskon_persen + $penjualan->diskon_rp);
                $total_diskon = $total_diskon_persen+$penjualan->diskon_rp;
                $total_belanja = $total_belanja+$penjualan->biaya_jasa_dokter;
                $biaya_jasa_dokter = number_format($penjualan->biaya_jasa_dokter,0,',',',');
            ?>
            <p style="margin-left: 10px;">Jasa Dokter &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; : Rp&nbsp;{{ $biaya_jasa_dokter }}</p>
        
            @if($penjualan->id_jasa_resep != '') 
                <?php
                    $jasa_resep_biaya = number_format($penjualan->jasa_resep,0,',',',');
                    $total_belanja = $total_belanja+$penjualan->jasa_resep;
                ?>
                <p style="margin-left: 10px;">Jasa Resep &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; : Rp&nbsp;{{ $jasa_resep_biaya }}</p>
            @else
                <p style="margin-left: 10px;">Jasa Resep &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; : Rp&nbsp;0</p>
            @endif

            <p style="margin-left: 10px;">---------------------------------------------------------------</p>
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
            ?>
            <p style="margin-left: 10px;">Total &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; : Rp&nbsp;{{ $total_belanja_format }}</p>
            <p style="margin-left: 10px;">Diskon &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; : Rp&nbsp;{{ $total_diskon_format }}</p>
            <p style="margin-left: 10px;">Bayar &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; : Rp&nbsp;{{ $total_bayar_format }}</p>
            <p style="margin-left: 10px;">Kembalian &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; : Rp&nbsp;{{ $kembalian_format }}</p>

            <p style="margin-left: 10px;">---------------------------------------------------------------</p>
            <p align="center">Terimakasih Atas Kunjungan Anda</p>
            <p align="center">Semoga Lekas Sembuh</p>
            <p style="margin-left: 10px;">---------------------------------------------------------------</p>
        </div>
        {!! Html::script('assets/plugins/jquery/jquery.min.js') !!}
        <script type="text/javascript">
            const $btnPrint = document.querySelector("#btnPrint");
            $btnPrint.addEventListener("click", () => {
                window.print();
            });

            $(document).ready(function(){
                //window.print();
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