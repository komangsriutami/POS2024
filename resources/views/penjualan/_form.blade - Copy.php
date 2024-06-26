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
<div class="row">
    <div class="col-sm-4">
        <address>
            <strong>BWF POS</strong><br>
            {{ $apotek->nama_singkat }} - Apotek {{ $apotek->nama_panjang }}<br>
            {{ $apotek->alamat }}<br>
            Phone : {{ $apotek->telepon }}
        </address>
    </div>
    @if($var == 1)
    <div class="col-sm-4">

    </div>
    @else
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
            Pasien : {{ $nama_pasien }}
        </address>
    </div>
    @endif
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
</div>


<?php $no = 0; ?>
<?php 
    if ($var==1) {
        $detail_penjualans = $penjualan->detail_penjualan;
        $jum = count($detail_penjualans);
    } else {
        $jum = count($detail_penjualans);
    }
    
    $detail_penjualan = new App\TransaksiPenjualanDetail;
?>
<input type="hidden" name="id" id="id" value="{{ $penjualan->id }}">
@if($var==1) 
<div class="row">
    @if($is_kredit != "")
    <div class="form-group col-md-2">
        {!! Form::label('id_pasien', 'Pilih Member') !!}
        {!! Form::select('id_pasien', $members, $penjualan->id_pasien, ['class' => 'form-control input_select', 'autocomplete' => 'off']) !!}
    </div>
    <div class="form-group col-md-8">
        {!! Form::label('vendor', 'Penjualan Melalui') !!}
        <select class="form-control input_select" id="id_vendor" name="id_vendor" onchange="check_vendor(this);">
            <option value=""> --- Pilih Penjualan Melalui ---</option>
            @foreach($vendor_kerjama as $obj)
            <option value="{{$obj->id}}" data-diskon="{{$obj->diskon}}"> {{$obj->nama}}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('diskon_vendor', 'Diskon') !!}
        <div class="input-group">
            {!! Form::text('diskon_vendor', $penjualan->diskon_vendor, array('id' => 'diskon_vendor', 'class' => 'form-control', 'placeholder'=>'Diskon Nota', 'readonly' => 'readonly')) !!}
            <div class="input-group-prepend">
                <span class="input-group-text">%</span>
            </div>
        </div>
    </div>
    <div class="form-group col-md-12">
        {!! Form::label('keterangan', 'Keterangan') !!}
        {!! Form::text('keterangan', $penjualan->keterangan, array('id'=> 'keterangan', 'class' => 'form-control', 'placeholder'=>'Masukan Keterangan', 'autocomplete' => 'off')) !!}
    </div>
    @else 
    <div class="form-group col-md-2">
        {!! Form::label('id_pasien', 'Pilih Member') !!}
        <div class="input-group">
            {!! Form::hidden('id_pasien', $penjualan->id_pasien, array('id' => 'id_pasien', 'class' => 'form-control', 'placeholder'=>'Masukan Pasien')) !!}
            {!! Form::text('pasien', null, array('id' => 'pasien', 'class' => 'form-control', 'placeholder'=>'Masukan Nama Pasien')) !!}
            <div class="input-group-append">
                <span class="btn btn-primary"  data-toggle="modal" data-placement="top" title="Cari Pasien" onclick="open_data_pasien('')"><i class="fa fa-search"></i></span>
            </div>
        </div>
    </div>
    @endif
</div>
<hr style="border: 1px solid #004d40; padding: 0px; margin-top: 0px; margin-bottom: 10px;">
<div class="row">
    <div class="form-group col-md-2">
        {!! Form::label('id_obat', 'Kode Obat | Shift') !!}
        <div class="input-group">
            {!! Form::hidden('id_obat', $penjualan->id_obat, array('id' => 'id_obat', 'class' => 'form-control', 'placeholder'=>'Masukan Obat')) !!}
            {!! Form::hidden('stok_obat', $penjualan->stok_obat, array('id' => 'stok_obat', 'class' => 'form-control', 'placeholder'=>'Masukan Obat')) !!}
            {!! Form::text('barcode', $penjualan->barcode, array('id' => 'barcode', 'class' => 'form-control', 'placeholder'=>'Masukan Barcode', 'autocomplete' => 'off')) !!}
            <div class="input-group-append">
                <span class="btn btn-primary"  data-toggle="modal" data-placement="top" title="Cari Item Obat" onclick="open_data_obat('')"><i class="fa fa-search"></i> | Ctrl</span>
            </div>
        </div>
    </div>
    <div class="form-group col-md-4">
        {!! Form::label('id_obat', 'Nama Obat') !!}
        {!! Form::text('nama_obat', $penjualan->nama_obat, array('id' => 'nama_obat', 'class' => 'form-control', 'placeholder'=>'Nama Obat', 'readonly' => 'readonly')) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('harga_jual', 'Harga Jual') !!}
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text">Rp</span>
            </div>
            {!! Form::text('harga_jual', $penjualan->harga_jual, array('id' => 'harga_jual', 'class' => 'form-control', 'placeholder'=>'Harga Jual', 'readonly' => 'readonly')) !!}
        </div>
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('diskon', 'Diskon') !!}
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text">Rp</span>
            </div>
            {!! Form::text('diskon', $penjualan->diskon, array('id' => 'diskon', 'class' => 'form-control', 'placeholder'=>'Diskon', 'readonly' => 'readonly')) !!}
        </div>
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('jumlah', 'Jumlah | F4') !!}
        <div class="input-group">
            {!! Form::text('jumlah', $penjualan->jumlah, array('id' => 'jumlah', 'class' => 'form-control', 'placeholder'=>'Jumlah', 'autocomplete' => 'off')) !!}
            <div class="input-group-append">
                <span class="btn btn-primary mb-4"  data-toggle="modal" data-placement="top" title="Tambahkan Item" id="add_row_penjualan"><i class="fa fa-plus-square"></i></span>
                <input type="hidden" name="counter" id="counter" value="<?php echo $no ?>"> 
            </div>
        </div>
    </div>
</div>
@else
<div class="row">
    <div class="form-group col-md-2">
        {!! Form::label('id_pasien', 'Pilih Member') !!}
        {!! Form::select('id_pasien', $members, $penjualan->id_pasien, ['class' => 'form-control input_select']) !!}
    </div>
</div>
<hr style="border: 1px solid #004d40; padding: 0px; margin-top: 0px; margin-bottom: 10px;">
@endif

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
                        <?php $is_retur = 0; ?>
                        @if($jum == 0)
                        @else
                            <?php 
                                $no = 0; 
                            ?>
                            @if($penjualan->is_penjualan_tanpa_item == 0)
                            @foreach($detail_penjualans as $detail_penjualan)
                            <?php 
                                $no++; 
                                
                                $harga_jual = $detail_penjualan->harga_jual;
                                $harga_jual = 'Rp '.number_format($harga_jual,0,',','.');

                                $jumlah = $detail_penjualan->jumlah;
                                $jumlah_cn = $detail_penjualan->jumlah_cn;
                                $jumlah_now = $jumlah-$jumlah_cn;

                                $diskon = $detail_penjualan->diskon;
                                $diskon = 'Rp '.number_format($diskon,0,',','.');

                                $total_ = ($jumlah_now * $detail_penjualan->harga_jual) - $detail_penjualan->diskon;
                                $total = 'Rp '.number_format($total_,0,',','.');

                                if($detail_penjualan->is_cn == 1) {
                                    $is_retur = $is_retur+1;
                                }
                            ?>
                            <tr>
                                <td>
                                    
                                    @if($detail_penjualan->is_cn == 1)
                                    <span class="btn btn-info btn-xs" title="Item ini sudah pernah diretur"><i class="fa fa-info"></i> Ada retur</span>
                                    <!-- <span class="btn btn-primary mb-4" onClick="set_jumlah_retur({{$detail_penjualan->id}}, {{ $no }})" data-toggle="tooltip" data-placement="top" title="Set Jumlah Retur"><i class="fa fa-edit"></i>Batal Retur</span> -->
                                    @else
                                    <input type="checkbox" name="check_list" id="detail_penjualan[{{ $no }}][record]" data-id="{{ $detail_penjualan->id }}">
                                    @endif
                                    <!-- <span class="label label-danger" onClick="deleteRow(this)" data-toggle="tooltip" data-placement="top" title="Cancel Penjualan"><i class="fa fa-trash"></i> CN</span> -->

                                    {!! Form::hidden('detail_penjualan['.$no.'][id]', $detail_penjualan->id, array('id' => 'id_'.$no, 'class' => 'form-control', 'placeholder'=>'ID', 'readonly' => 'readonly')) !!}
                                </td>
                                <td style="display:none;">
                                    {!! Form::text('detail_penjualan['.$no.'][id_obat]', $detail_penjualan->id_obat, array('id' => 'id_obat_'.$no, 'class' => 'form-control', 'placeholder'=>'ID Obat', 'readonly' => 'readonly')) !!}
                                </td>
                                <td>
                                    {!! Form::text('detail_penjualan['.$no.'][nama_obat]', $detail_penjualan->obat->nama, array('id' => 'nama_obat_'.$no, 'class' => 'form-control', 'placeholder'=>'Nama Obat', 'readonly' => 'readonly')) !!}
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
                                    {!! Form::text('detail_penjualan['.$no.'][jumlah_cn]', $detail_penjualan->jumlah_cn, array('id' => 'jumlah_cn_'.$no, 'class' => 'form-control', 'placeholder'=>'Jumlah Retur', 'readonly' => 'readonly')) !!}
                                </td>
                                <td style="display:none;" id="hitung_diskon_{{ $no }}" class="hitung_diskon">{{ $diskon }}</td>
                                <td style='text-align:right;' id="hitung_total_{{ $no }}" class="hitung_total" data-total="{{$total_}}">{{ $total }}
                                </td>
                            </tr>
                            @endforeach
                            @endif
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
                                    if($penjualan->id_dokter != null) {
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
                                    if($penjualan->nama_lab != null) {
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
                                    if($penjualan->id_paket_wd != null) {
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
                                    if($penjualan->id_karyawan != null) {
                                        $str_karyawan = $penjualan->karyawan->nama;
                                    }
                                ?>
                                <span id="diskon_persen_input"> Karyawan : {{ $str_karyawan }}</span>
                                <a href="#" style="color: red; text-decoration: underline; float: right;" onclick="set_diskon_persen()"> Set Karyawan Diskon % | F9 </a>
                                <!-- <a href="#" style="color: red; text-decoration: underline; float: right;" onclick="set_diskon_nota()"> Set Diskon | F9 </a> -->
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
    @if($is_retur > 0)
    <br>
    <div class="row">
        <div class="col-md-12">
            <div class="card card-info card-outline" id="main-box" style="">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list"></i>
                        Histori Retur | <small class="text-red"><b>Jika ingin membatalkan retur, silakan hubungi kepala outlet untuk membatalkan retur (khusus untuk yang sudah disetujui).</b></small>
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <table  id="tb_penjualan_retur" class="table table-bordered table-striped table-hover" width="100%">
                <thead>
                    <tr>
                        <th width="3%" class="text-center">No.</th>
                        <th width="5%" class="text-center">Tanggal</th>
                        <th width="30%" class="text-center">Detail Obat</th>
                        <th width="10%" class="text-center">Kasir</th>
                        <th width="20%" class="text-center">Alasan</th>
                        <th width="10%" class="text-center">Status</th>
                        <th width="10%" class="text-center">Disetujui</th>
                        <th width="12%" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- ini pengganti untuk pembayaran -->
    <input id="cash" class="form-control"name="cash" type="hidden" value="0">
    <input id="id_kartu_debet_credit" class="form-control" name="id_kartu_debet_credit" type="hidden" value="0">
    <input id="no_kartu" class="form-control" name="no_kartu" type="hidden" value="0">
    <input id="debet" class="form-control" name="debet" type="hidden" value="0">
    <input id="surcharge" class="form-control" name="surcharge" type="hidden" value="0">
    <input id="total_belanja" class="form-control" name="total_belanja" type="hidden" value="0">
    <input id="total_bayar" class="form-control" name="total_bayar" type="hidden" value="0">
    <input id="kembalian" class="form-control" name="kembalian" type="hidden" value="0">
    <input id="count_total_belanja" class="form-control" name="count_total_belanja" type="hidden" value="0">
</div>
