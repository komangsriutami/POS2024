@extends('layout.app_penjualan')

@section('title')
Transaksi Penjualan
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Transaksi</a></li>
    <li class="breadcrumb-item"><a href="#">Transaksi Penjualan</a></li>
    <li class="breadcrumb-item active" aria-current="page">Retur Item</li>
</ol>
@endsection

@section('content')
{!! Form::model($penjualan, ['method' => 'PUT', 'class'=>'validated_form', 'id'=>'form_retur_penjualan', 'route' => ['penjualan.update_retur', $penjualan->id]]) !!}
    <style type="text/css">
        #divfix {
           bottom: 0;
           right: 0;
           position: fixed;
           z-index: 3000;
            }
        .format_total {
            font-size: 18px;
            font-weight: bold;
            color:#D81B60;
        }
    </style>

    <div class="row" id="divfix">
        <div class="col-sm-12">
            <div class="callout callout-success">
                <a class="btn btn-info text-white" style="text-decoration: none;" type="button" href="{{ url('penjualan/detail/'.$penjualan->id)}}" data-toggle="tooltip" data-placement="top" title="List Data Penjualan"><i class="fa fa-home"></i></a> 
                <button class="btn btn-primary" type="button" onclick="retur_save()" data-toggle="tooltip" data-placement="top" title="Simpan data retur"><i class="fa fa-undo-alt"></i> Save Retur</button> 
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-4">
                            <address>
                                <strong>BWF POS</strong><br>
                                {{ $apotek->nama_singkat }} - Apotek {{ $apotek->nama_panjang }}<br>
                                {{ $apotek->alamat }}<br>
                                Phone : {{ $apotek->telepon }}
                            </address>
                        </div>
                        <div class="col-sm-4">
                            <?php
                                if($penjualan->id_pasien == '' OR $penjualan->id_pasien == null) {
                                    $nama_pasien = 'Pasien Umum';
                                } else {
                                    $nama_pasien = $penjualan->pasien->nama;
                                }
                            ?>
                            <address>
                                <strong>NOMOR NOTA : {{ $penjualan->id }}</strong><br>
                                Tanggal : {{ $penjualan->tgl_nota }}<br>
                                Kasir : {{ $penjualan->created_oleh->nama }}<br>
                                Pasien : {{ $apotek->telepon }}
                            </address>
                        </div>
                        <div class="col-sm-4">
                            <div class="card bg-info">
                              <div class="card-body box-profile">
                                <div class="text-center">
                                    <h1 id="total_pembayaran_display">Rp 0, -</h1>
                                </div>

                              </div>
                            </div>
                        </div>
                    </div>
                    <hr style="border: 1px solid #004d40; padding: 0px; margin-top: 0px; margin-bottom: 10px;">
                    <div class="row">
                        <div class="form-group col-md-3">
                            {!! Form::label('id_jenis_pembayaran_tt', 'Pilih Jenis Pembayaran Retur') !!}
                            <select id="id_jenis_pembayaran_tt" name="id_jenis_pembayaran_tt" class="form-control input_select required">
                                <option value="1" {!!( "1" == $penjualan->id_jenis_pembayaran_tt ? 'selected' : '')!!}>Pembayaran Cash</option>
                                <option value="2" {!!( "2" == $penjualan->id_jenis_pembayaran_tt ? 'selected' : '')!!}>Pembayaran Non Cash</option>
                            </select>
                        </div>
                    </div>
                    <hr style="border: 1px solid #004d40; padding: 0px; margin-top: 0px; margin-bottom: 10px;">
                    <div class="row">
                        <div class="col-12">
                            <div class="box box-success" id="detail_data_penjualan">
                                <div class="box-body">
                                    <!-- <button class="btn btn-primary mb-4" data-toggle="modal" data-target="#itemModal">Tambah Item</button> -->
                                    <div class="table-responsive">
                                        <table  id="tb_nota_penjualan" class="table table-bordered table-striped table-hover table-head-fixed text-nowrap mb-0">
                                            <thead>
                                                <tr class="bg-gray color-palette">
                                                    <td width="5%" class="text-center"><strong>No.</strong></td>
                                                    <td width="45%" class="text-center"><strong>Nama Obat</strong></td>
                                                    <td width="10%" class="text-center"><strong>Harga</strong></td>
                                                    <td width="10%" class="text-center"><strong>Diskon</strong></td>
                                                    <td width="10%" class="text-center"><strong>Jumlah</strong></td>
                                                    <td width="10%" class="text-center"><strong>Jumlah Retur</strong></td>
                                                    <td width="10%" class="text-center"s><strong>Total</strong></td>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                    <?php $no = 0; ?>
                                                    @if($penjualan->is_penjualan_tanpa_item == 0)
                                                    @foreach($detail_penjualans as $detail_penjualan)
                                                    <?php 
                                                        $no++; 
                                                        
                                                        $harga_jual = $detail_penjualan->harga_jual;
                                                        $harga_jual = 'Rp '.number_format($harga_jual,0,',','.');

                                                        $diskon = $detail_penjualan->diskon;
                                                        $diskon = 'Rp '.number_format($diskon,0,',','.');

                                                        $total_ = ($detail_penjualan->jumlah * $detail_penjualan->harga_jual) - $detail_penjualan->diskon;
                                                        $total = 'Rp '.number_format($total_,0,',','.');
                                                    ?>
                                                    <tr>
                                                        <td>
                                                            <input type="checkbox" name="check_list" id="detail_penjualan[{{ $no }}][record]" data-id="{{ $detail_penjualan->id }}">
                                                            {!! Form::hidden('detail_penjualan['.$no.'][id]', $detail_penjualan->id, array('id' => 'id_'.$no, 'class' => 'form-control', 'placeholder'=>'ID', 'readonly' => 'readonly')) !!}
                                                        </td>
                                                        <td style="display:none;">
                                                            {!! Form::text('detail_penjualan['.$no.'][id_obat]', $detail_penjualan->id_obat, array('id' => 'id_obat_'.$no, 'class' => 'form-control', 'placeholder'=>'ID Obat', 'readonly' => 'readonly')) !!}
                                                        </td>
                                                        <td style="display:none;">
                                                            {!! Form::text('detail_penjualan['.$no.'][id_alasan_retur]', $detail_penjualan->id_alasan_retur, array('id' => 'id_alasan_retur_'.$no, 'class' => 'form-control', 'placeholder'=>'ID Alasan', 'readonly' => 'readonly')) !!}
                                                        </td>
                                                         <td style="display:none;">
                                                            {!! Form::text('detail_penjualan['.$no.'][alasan_lain]', $detail_penjualan->alasan_lain, array('id' => 'alasan_lain_'.$no, 'class' => 'form-control', 'placeholder'=>'ID Alasan', 'readonly' => 'readonly')) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::text('detail_penjualan['.$no.'][nama_obat]', $detail_penjualan->obat->nama, array('id' => 'nama_obat_'.$no, 'class' => 'form-control', 'placeholder'=>'Nama Obat', 'readonly' => 'readonly')) !!}
                                                            <p id="alasan_{{ $no }}">Alasan Retur: -</p>
                                                        </td>
                                                        <td style='text-align:right;'>
                                                            <input type="hidden" name="detail_penjualan[{{$no}}][harga_jual]" id="harga_jual_{{$no}}" value="{{ $detail_penjualan->harga_jual}}">
                                                            {!! Form::text('detail_penjualan['.$no.'][harga_jual_view]', $harga_jual, array('id' => 'harga_jual_view_'.$no, 'class' => 'form-control', 'placeholder'=>'Masukan Harga', 'readonly' => 'readonly')) !!}
                                                        </td>
                                                        <td style='text-align:right;'>
                                                            <input type="hidden" name="detail_penjualan[{{$no}}][diskon]" id="diskon_{{$no}}" value="{{ $detail_penjualan->diskon}}">
                                                            {!! Form::text('detail_penjualan['.$no.'][diskon_view]', $diskon, array('id' => 'diskon_view_'.$no, 'class' => 'form-control', 'placeholder'=>'Masukan Diskon', 'readonly' => 'readonly')) !!}
                                                        </td>
                                                        <td style='text-align:center;'>
                                                            {!! Form::text('detail_penjualan['.$no.'][jumlah]', $detail_penjualan->jumlah, array('id' => 'jumlah_'.$no, 'class' => 'form-control', 'placeholder'=>'Masukan Jumlah', 'readonly' => 'readonly')) !!}
                                                        </td>
                                                        <td>
                                                            <div class="input-group">
                                                                {!! Form::text('detail_penjualan['.$no.'][jumlah_cn]', $detail_penjualan->jumlah_cn, array('id' => 'jumlah_cn_'.$no, 'class' => 'form-control', 'placeholder'=>'Jumlah Retur', 'readonly' => 'readonly')) !!}
                                                                <div class="input-group-append">
                                                                    <span class="btn btn-primary mb-4" onClick="set_jumlah_retur({{$detail_penjualan->id}}, {{ $no }})" data-toggle="tooltip" data-placement="top" title="Set Jumlah Retur"><i class="fa fa-edit"></i></span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td style="display:none;" id="hitung_diskon_{{ $no }}" class="hitung_diskon">{{ $diskon }}</td>
                                                        <td style='text-align:right;' id="hitung_total_{{ $no }}" class="hitung_total" data-total="{{$total_}}">{{ $total }}
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                    @endif
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td style="display: none;" colspan="2">Diskon (Rp) <a href="#" style="color: red; text-decoration: underline;" onclick="set_diskon_rp()"> Set Diskon | F9</a></td>
                                                    <td style="display: none;" id="diskon_rp_input"></td>
                                                    <input type="hidden" name="diskon_rp" id="diskon_rp">   
                                                    <td colspan="2"></td>
                                                    <!-- <td></td> -->
                                                    <td colspan="4">Total Penjualan</td>
                                                    <td id="harga_total" class="text-right"></td>
                                                    <input type="hidden" name="harga_total_input" id="harga_total_input">
                                                </tr>
                                                <?php 
                                                    if(empty($penjualan->biaya_jasa_dokter) || $penjualan->biaya_jasa_dokter == "") 
                                                    {
                                                        $biaya_jasa_dokter = 0;
                                                    } else {
                                                        $biaya_jasa_dokter = $penjualan->biaya_jasa_dokter;
                                                    }


                                                    if(empty($penjualan->biaya_resep) || $penjualan->biaya_resep == "") 
                                                    {
                                                        $biaya_resep = 0;
                                                    } else {
                                                        $biaya_resep = $penjualan->biaya_resep;
                                                    }

                                                    $total_biaya_dokter = $biaya_jasa_dokter + $biaya_resep; 
                                                ?>
                                                <tr>
                                                    <td colspan="2"> 
                                                        <?php 
                                                            $str_dokter = '-';
                                                            if(!is_null($penjualan->id_dokter) AND $penjualan->id_dokter!="" AND $penjualan->id_dokter!=0) {
                                                                $str_dokter = $penjualan->dokter->nama;
                                                            }
                                                        ?>
                                                        <span id="id_dokter_input">Dokter : {{ $str_dokter }}</span>
                                                        <a href="#" style="color: red; text-decoration: underline; float: right;" onclick="set_jasa_dokter()"> Set Jasa Dokter/Resep | F8</a>
                                                    </td>
                                                    <!-- <td ></td> -->
                                                    <input type="hidden" name="id_dokter" id="id_dokter" value="{{$penjualan->id_dokter}}">
                                                    <td colspan="2">Biaya Jasa / Resep</td>
                                                    <td id="biaya_jasa_dokter_input" class="text-right"></td>
                                                    <!-- semua hidden -->
                                                    <input type="hidden" name="id_jasa_resep" id="id_jasa_resep" value="{{$penjualan->id_jasa_resep}}">
                                                    <td style="display:none;" id="id_jasa_resep_input"></td>
                                                    <input type="hidden" name="biaya_jasa_dokter" id="biaya_jasa_dokter" value="{{$penjualan->biaya_jasa_dokter}}">
                                                    <td id="biaya_resep_input" class="text-right"></td>
                                                    <input type="hidden" name="biaya_resep" id="biaya_resep" value="{{$penjualan->biaya_resep}}">
                                                    <td id="total_biaya_dokter_input" class="text-right"></td>
                                                    <input type="hidden" name="total_biaya_dokter" id="total_biaya_dokter" value="{{$biaya_jasa_dokter}}">
                                                </tr>
                                                <tr>
                                                    <td colspan="2"> 
                                                        <?php 
                                                            $str_lab = '-';
                                                            if(!is_null($penjualan->nama_lab) AND $penjualan->nama_lab!="" AND $penjualan->nama_lab!=0) {
                                                                $str_lab = $penjualan->nama_lab;
                                                            }
                                                        ?>
                                                        <span id="nama_lab_input">Laboratorium : {{ $str_lab }}</span>
                                                        <a href="#" style="color: red; text-decoration: underline; float: right;" onclick="set_pembayaran_lab()"> Set Pembayaran Lab</a>
                                                    </td>
                                                    <!-- <td ></td> -->
                                                    <td colspan="4">Biaya Lab</td>
                                                    <input type="hidden" name="biaya_lab" id="biaya_lab" value="{{$penjualan->biaya_lab}}">
                                                    <input type="hidden" name="nama_lab" id="nama_lab" value="{{$penjualan->nama_lab}}">
                                                    <input type="hidden" name="keterangan_lab" id="keterangan_lab" value="{{$penjualan->keterangan_lab}}">
                                                    <td id="biaya_lab_input" class="text-right"></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2">
                                                        <span id="nama_lab_input">APD</span>
                                                        <a href="#" style="color: red; text-decoration: underline; float: right;" onclick="set_pembayaran_apd()"> Set Pembayaran APD</a>
                                                    </td>
                                                    <!-- <td ></td> -->
                                                    <td colspan="4">Biaya APD</td>
                                                    <input type="hidden" name="biaya_apd" id="biaya_apd" value="{{$penjualan->biaya_apd}}">
                                                    <td id="biaya_apd_input" class="text-right"></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2"> 
                                                        <?php 
                                                            $str_paket = '-';
                                                            if(!is_null($penjualan->id_paket_wd) AND $penjualan->id_paket_wd!= "" AND $penjualan->id_paket_wd!=0) {
                                                                $str_paket = $penjualan->paket_wd->nama;
                                                            }
                                                        ?>
                                                        <span id="id_paket_wd_input"> Paket WD : {{ $str_paket }}</span>
                                                        <a href="#" style="color: red; text-decoration: underline; float: right;" onclick="set_paket()"> Set Paket WD </a>
                                                    </td>
                                                    <!-- <td id=""></td> -->
                                                    <input type="hidden" name="id_paket_wd" id="id_paket_wd" value="{{$penjualan->id_paket_wd}}">
                                                    <input type="hidden" name="harga_wd" id="harga_wd" value="{{$penjualan->harga_wd}}">
                                                    <td colspan="4">Harga WD</td>
                                                    <td id="harga_wd_input" class="text-right"></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2"> 
                                                        <?php 
                                                            $str_karyawan = '-';
                                                            if(!is_null($penjualan->id_karyawan) AND $penjualan->id_karyawan!="" AND $penjualan->id_karyawan!=0) {
                                                                $str_karyawan = $penjualan->karyawan->nama;
                                                            }
                                                        ?>
                                                        <span id="diskon_persen_input"> Karyawan : {{ $str_karyawan }}</span>
                                                        <a href="#" style="color: red; text-decoration: underline; float: right;" onclick="set_diskon_persen()"> Set Karyawan Diskon % | F9 </a>
                                                        <a href="#" style="color: red; text-decoration: underline; float: right;" onclick="set_diskon_nota()"> Set Diskon | F9 </a>
                                                    </td>
                                                    <!-- <td id=""></td> -->
                                                    <input type="hidden" name="diskon_persen" id="diskon_persen" value="{{$penjualan->diskon_persen}}">
                                                    <input type="hidden" name="id_karyawan" id="id_karyawan" value="{{$penjualan->id_karyawan}}">
                                                    <td colspan="4">Total Diskon Nota</td>
                                                    <td id="diskon_total" class="text-right"></td>
                                                    <input type="hidden" name="diskon_total_input" id="diskon_total_input">
                                                </tr>
                                                <tr class="bg-gray disabled color-palette">
                                                    <td colspan="6">Total Pembayaran</td>
                                                    <td id="total_pembayaran" class="text-right"></td>
                                                    <input type="hidden" name="total_pembayaran_input" id="total_pembayaran_input">
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
{!! Form::close() !!}
@endsection

@section('style')
    <style>
        .content-wrapper {
            /* height: 100% !important; */
        }
        .content {
            min-height: calc(100vh - calc(3.5rem + 1px) - calc(3.5rem + 1px));
        }
    </style>
@endsection

@section('script')
    @include('penjualan/_form_js')
@endsection


