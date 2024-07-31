@extends('layout.app_penjualan')

@section('title')
Cetak Nota
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Transaksi</a></li>
    <li class="breadcrumb-item"><a href="#">Transaksi Penjualan</a></li>
    <li class="breadcrumb-item active" aria-current="page">Cetak Nota</li>
</ol>
@endsection

@section('content')
<style type="text/css">
    /*p {
        font-size: 12px;
        margin-left:  7px;
        margin-right: 7px;
        margin-top: none;
        margin-bottom: none;
        padding: none;
    }
*/
    td,
    th,
    tr,
    table {
        border-top: 1px solid black;
        border-collapse: collapse;
        font-size: 12px;
        margin: none;
        padding: none;
    }

    .table td, .table th {
        padding: 2px;
        vertical-align: top;
        border-top: 1px solid #dee2e6;
    }
</style>
<div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            
                            <div id="qz-connection" class="panel panel-default">
                                <div class="panel-heading">
                                    <button class="close tip" data-toggle="tooltip" title="Launch QZ" id="launch" href="#" onclick="launchQZ();" style="display: none;">
                                        <i class="fa fa-external-link"></i>
                                    </button>
                                    <h5 class="panel-title">
                                        Connection: <span id="status_qz" class="text-muted" style="font-weight: bold;">Unknown</span>
                                    </h5>
                                </div>

                                <div class="panel-body">
                                    <div class="btn-toolbar">
                                        <div class="btn-group" role="group">
                                            <a  href="{{ url('/penjualan/') }}" class="hidden-print btn btn-sm btn-info" style="text-decoration:none;margin:0;color: #fff;background-color: #dc3545;border-color: #dc3545;box-shadow: none; font-size:10pt;">Back | F2</a>
                                            <button type="button" class="btn btn-success btn-sm" onclick="startConnection();">Connect</button>
                                            <button type="button" class="btn btn-warning btn-sm" onclick="endConnection();">Disconnect</button>
                                        </div>
                                        <!-- <button type="button" class="btn btn-info" onclick="listNetworkInfo();">List Network Info</button> -->
                                    </div>
                                </div>
                            </div>
                            <hr />
                            <div class="panel panel-primary">
                                <div class="panel-heading">
                                    <h5 class="panel-title">Printer</h5>
                                </div>

                                <div class="panel-body">
                                   <!--  <div class="form-group">
                                        <label for="printerSearch">Pencarian :</label>
                                        <select id="list_printer" value="zebra" class="form-control"></select>
                                    </div>
                                    <hr /> -->
                                    <div class="form-group">
                                        <label>Current printer:</label>
                                        <div id="configPrinter">NONE</div>
                                    </div>
                                    <div class="btn-toolbar">
                                        <div class="form-group">
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-danger btn-sm" onclick="print_nota();">Printer | Shift</button>
                                            </div>
                                           
                                        </div>
                                        </div>
                                </div>
                            </div>
                        </div>
                        <!-- /.col -->
                        <div class="col-md-6">
                            <div class="panel panel-default">
                                <div class="panel-body">
                                    <input type="hidden" name="id" id="id" value="{{ $penjualan->id }}">
                                    <input type="hidden" name="_token" id="_token" value="{{csrf_token()}}">
                                    <?php
                                        $nama_apotek = strtoupper($apotek->nama_panjang);
                                        $nama_apotek_singkat = strtoupper($apotek->nama_singkat);
                                    ?>
                                    <p align="center">APOTEK BWF-{{ $nama_apotek }}</p>
                                    <p align="center">{{ $apotek->alamat }}</p>
                                    <p align="center">Telp. {{ $apotek->telepon }}</p>
                                    <hr>
                                    <p style="margin-left: 10px;">No Nota : {{$nama_apotek_singkat}}-{{ $penjualan->id }}</p>
                                    <p style="margin-left: 10px;">Tanggal : {{ $penjualan->created_at }}</p>
                                    <hr>
                                  
                                    @if($penjualan->is_penjualan_tanpa_item != 1)
                                        <div class="table-responsive">
                                            <?php 
                                                $no = 0; 
                                                $total_belanja = 0;
                                            ?>
                                            <table class="table">
                                                <tr>
                                                    <td>No.</td>
                                                    <td>Nama Obat</td>
                                                    <td>Jumlah</td>
                                                    <td>Harga</td>
                                                    <td>Diskon</td>
                                                    <td>Total</td>
                                                </tr>
                                                    
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
                                                        <td>{{ $no }}</td>
                                                        <td>{{ $obj->nama }}</td>
                                                        <td>{{ $obj->jumlah }}</td>
                                                        <td>{{ $harga_jual }}</td>
                                                        <td>(-{{ $diskon }})</td>
                                                        <td>Rp {{ $total_2 }}</td>
                                                    </tr>
                                                @endforeach

                                            </table>
                                            <?php
                                                $total_diskon_persen = $penjualan->diskon_persen/100 * $total_belanja;
                                                $total_belanja_bayar = $total_belanja - ($total_diskon_persen + $penjualan->diskon_rp);
                                                $total_diskon = $total_diskon_persen+$penjualan->diskon_rp;
                                                $total_belanja = $total_belanja+$penjualan->biaya_jasa_dokter;
                                                $biaya_jasa_dokter = number_format($penjualan->biaya_jasa_dokter,0,',',',');
                                                $biaya_lab = number_format($penjualan->biaya_lab,0,',',',');
                                                $biaya_apd = number_format($penjualan->biaya_apd,0,',',',');
                                            ?>
                                            <hr>
                                            <table class="table">
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
                                            </table>
                                            <hr>
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

                                            <table class="table">
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
                                            </table>
                                            <hr>
                                            <p align="center">Terimakasih Atas Kunjungan Anda</p>
                                            <p align="center">Semoga Lekas Sembuh</p>
                                            <hr>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
{!! Html::script('assets/qz-tray/dependencies/rsvp-3.1.0.min.js') !!}
{!! Html::script('assets/qz-tray/dependencies/sha-256.min.js') !!}
{!! Html::script('assets/qz-tray/qz-tray.js') !!}
{!! Html::script('assets/qz-tray/qz_print_script.js') !!}
<script type="text/javascript">
    
    $(document).ready(function(){
        startConnection();

        $(document).on("keyup", function(e){
            var x = e.keyCode || e.which;
            if (x == 16) {  
                // fungsi shift 
                print_nota();
            } else if(x==113){
                // fungsi F2 
                window.location.href = "{{ url('/penjualan/') }}";
            }
        })
    })
</script>
@endsection

