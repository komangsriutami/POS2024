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
<input type="hidden" name="id" id="id" value="{{ $pembelian->id }}">
<div class="row">
    <div class="col-sm-3">
        <address>
            <strong>BWF POS</strong><br>
            {{ $apotek->nama_singkat }} - Apotek {{ $apotek->nama_panjang }}<br>
            {{ $apotek->alamat }}<br>
            Phone : {{ $apotek->telepon }}
        </address>
    </div>
    @if($var == 1)
    <div class="col-sm-3">

    </div>
     <div class="col-sm-3">

    </div>
    @else
    <?php
        if($pembelian->is_lunas != 1) {
            $status = '<span class="text-danger"><b>BELUM LUNAS</b></span>';
        } else {
            $status = '<span class="text-info"><b>LUNAS</b></span>';
        }

        if($pembelian->is_sign != 1) {
            $sign = '<span class="text-danger"><b>BELUM DITTD</b></span>';
        } else {
            $sign = '<span class="text-info"><b>SUDAH DITTD</b></span>';
        }
    ?>
    <div class="col-sm-3">
        <address>
            <strong>NOMOR NOTA : {{ $pembelian->id }}</strong><br>
            Tanggal : {{ $pembelian->tgl_nota }}<br>
            Kasir : {{ $pembelian->created_oleh->nama }}<br>
        </address>
    </div>
    <div class="col-sm-3">
        Status : {!! $status !!}<br>
        Sign : {!! $sign !!}
    </div>
    @endif
    <div class="col-sm-3">
        <div class="card bg-info">
          <div class="card-body box-profile">
            <div class="text-center">
                <h1 id="total_pembayaran_display">Rp 0, -</h1>
            </div>

          </div>
        </div>
    </div>
</div>
<?php
    $is_ppn = 0;
    if(!is_null($pembelian->id) && $pembelian->ppn > 0) {
        $is_ppn = 1;
    } else if(!is_null($pembelian->id) && $pembelian->ppn <= 0){
        $is_ppn = 2;
    }
?>
<hr style="border: 1px solid #004d40; padding: 0px; margin-top: 0px; margin-bottom: 10px;">
<div class="row">
    <div class="form-group col-md-3">
        {!! Form::label('id_jenis_pembayaran', 'Pilih Jenis Pembayaran') !!}
        <select id="id_jenis_pembayaran" name="id_jenis_pembayaran" class="form-control input_select required">
            <option value="1" {!!( "1" == $pembelian->id_jenis_pembayaran ? 'selected' : '')!!}>Pembayaran Tidak Langsung</option>
            <option value="2" {!!( "2" == $pembelian->id_jenis_pembayaran ? 'selected' : '')!!}>Pembayaran Langsung</option>
        </select>
    </div>
    <div class="form-group col-md-3">
        {!! Form::label('is_ppn', 'PPN') !!}
        <select id="is_ppn" name="is_ppn" class="form-control input_select required">
            <option value="" {!!( "" == $is_ppn ? 'selected' : '')!!}>- Pilih PPN -</option>
            <option value="1" {!!( 1 == $is_ppn ? 'selected' : '')!!}>PPN 11%</option>
            <option value="2" {!!( 2 == $is_ppn ? 'selected' : '')!!}>Tanpa PPN</option>
        </select>
    </div>
</div>
<hr style="border: 1px solid #004d40; padding: 0px; margin-top: 0px; margin-bottom: 10px;">
<div class="row">
    {!! Form::hidden('is_from_order', 0, array('class' => 'form-control', 'id'=>'is_from_order')) !!}
    {!! Form::hidden('id_nota_order', null, array('class' => 'form-control', 'id'=>'id_nota_order')) !!}
    {!! Form::hidden('batas_max_hpp', null, array('class' => 'form-control', 'id'=>'batas_max_hpp')) !!}
    <div class="form-group col-md-2">
        {!! Form::label('apotek', 'Pilih Apotek') !!}
        @if($var == 1)
            {!! Form::select('id_apotek', $apoteks, $pembelian->id_apotek, ['class' => 'form-control input_select required']) !!}
        @else
            {!! Form::select('id_apotek', $apoteks, $pembelian->id_apotek, ['class' => 'form-control input_select required', 'disabled'=>'disabled']) !!}
        @endif
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('suplier', 'Pilih Suplier | F8') !!}
        <?php 
            $nama = '';
            if($pembelian->id_suplier != '') {
                $nama = $pembelian->suplier->nama;
            } 
        ?>
        <div class="input-group">
            {!! Form::hidden('id_suplier', $pembelian->id_suplier, array('class'=>'id_suplier', 'id'=>'id_suplier')) !!}
            {!! Form::text('suplier', $nama, array('id' => 'suplier', 'class' => 'form-control required', 'placeholder'=>'Masukan Nama Suplier', 'autocomplete' => 'off')) !!}
            <div class="input-group-append">
                <span class="btn btn-primary"  data-toggle="modal" data-placement="top" title="Cari Nama Suplier" onclick="open_data_suplier('')"><i class="fa fa-search"></i> | Esc</span>
            </div>
        </div>
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('jenis_pembelian', 'Pilih Jenis Pembelian') !!}
        {!! Form::select('id_jenis_pembelian', $jenis_pembelians, $pembelian->id_jenis_pembelian, ['id' => 'id_jenis_pembelian', 'class' => 'form-control required']) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('no_faktur', 'Nomor Faktur') !!}
        {!! Form::text('no_faktur', $pembelian->no_faktur, array('id' => 'no_faktur', 'class' => 'form-control required', 'placeholder'=>'Masukan No Faktur', 'autocomplete' => 'off')) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('tgl_faktur', 'Tanggal Faktur') !!}
        {!! Form::text('tgl_faktur', $pembelian->tgl_faktur, array('type' => 'text', 'class' => 'form-control datetimepicker-input required','placeholder' => 'Tanggal Faktur', 'id' => 'tgl_faktur', 'autocomplete' => 'off')) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('tgl_jatuh_tempo', 'Tanggal Jatuh Tempo') !!}
        {!! Form::text('tgl_jatuh_tempo', $pembelian->tgl_jatuh_tempo, array('type' => 'text', 'class' => 'form-control datepicker required','placeholder' => 'Tanggal Jatuh Tempo', 'id' => 'tgl_jatuh_tempo', 'autocomplete' => 'off')) !!}
    </div>
</div>
<?php $no = 0; ?>


<?php 
    if ($var==1) {
        $jum = 0;
    } else {
        $detail_pembelians = $pembelian->detail_pembalian;
        $jum = count($detail_pembelians);
    }
    

    $detail_pembelian = new App\TransaksiPembelianDetail;
?>
<hr style="border: 1px solid #004d40; padding: 0px; margin-top: 0px; margin-bottom: 10px;">
@if($pembelian->is_lunas != 1 AND $pembelian->is_sign != 1 OR $pembelian->is_sign != 1 AND $pembelian->id_jenis_pembelian == 1 OR Auth::user()->is_admin == 1)
<div class="row">
    <div class="form-group col-md-2">
        {!! Form::label('id_obat', 'Kode Obat | Shift') !!}
        <div class="input-group">
            {!! Form::hidden('id_obat', $pembelian->id_obat, array('id' => 'id_obat', 'class' => 'form-control', 'placeholder'=>'Masukan Obat')) !!}
            {!! Form::text('barcode', $pembelian->barcode, array('id' => 'barcode', 'class' => 'form-control', 'placeholder'=>'Barcode/SKU/Nama Obat', 'autocomplete' => 'off')) !!}
            <div class="input-group-append">
                <span class="btn btn-primary mb-4"  data-toggle="modal" data-placement="top" title="Cari Item Obat" onclick="open_data_obat()"><i class="fa fa-search"></i> | Ctrl</span>
            </div>
        </div>
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('id_obat', 'Nama Obat') !!}
        {!! Form::text('nama_obat', $pembelian->nama_obat, array('id' => 'nama_obat', 'class' => 'form-control', 'placeholder'=>'Nama Obat', 'readonly' => 'readonly')) !!}
    </div>
    <div class="form-group col-md-1">
        {!! Form::label('harga_beli_sebelumnya', 'HBPPN Sebelumnya') !!}
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            {!! Form::text('harga_beli_sebelumnya', $pembelian->harga_beli, array('id' => 'harga_beli_sebelumnya', 'class' => 'form-control', 'placeholder'=>'(otomatis)', 'readonly' => 'readonly')) !!}
        </div>
    </div>
    <div class="form-group col-md-1">
        {!! Form::label('harga_beli', 'HB Sekarang') !!}
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            {!! Form::text('harga_beli', $pembelian->harga_beli, array('id' => 'harga_beli', 'class' => 'form-control', 'placeholder'=>'(otomatis)', 'readonly' => 'readonly')) !!}
        </div>
    </div>
    <div class="form-group col-md-1">
        {!! Form::label('harga_beli_ppn', 'HBPPN Sekarang') !!}
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            {!! Form::text('harga_beli_ppn', $pembelian->harga_beli_ppn, array('id' => 'harga_beli_ppn', 'class' => 'form-control', 'placeholder'=>'(otomatis)', 'readonly' => 'readonly')) !!}
        </div>
    </div>
    <div class="form-group col-md-1">
        {!! Form::label('jumlah', 'Jumlah') !!}
         <div class="input-group">
            <span class="input-group-text">@</span>
            {!! Form::text('jumlah', $pembelian->jumlah, array('id'=>'jumlah', 'class' => 'form-control', 'placeholder'=>'Jumlah', 'autocomplete' => 'off')) !!}
        </div>
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('total_harga', 'Total Harga') !!}
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            {!! Form::text('total_harga', $pembelian->total_harga, array('id' => 'total_harga', 'class' => 'form-control', 'placeholder'=>'Total Harga', 'autocomplete' => 'off')) !!}
        </div>
    </div>
    <div class="form-group col-md-1">
        {!! Form::label('diskon', 'Diskon (Rp)') !!}
        {!! Form::text('diskon', $pembelian->diskon, array('id' => 'diskon', 'class' => 'form-control', 'placeholder'=>'Diskon Rupiah', 'autocomplete' => 'off')) !!}
    </div>
    <div class="form-group col-md-1">
        {!! Form::label('diskon_persen', 'Diskon (%)') !!}
        {!! Form::text('diskon_persen', $pembelian->diskon_persen, array('id' => 'diskon_persen', 'class' => 'form-control', 'placeholder'=>'Diskon Persen', 'autocomplete' => 'off')) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('tgl_batch', 'Tanggal Expired') !!}
        {!! Form::text('tgl_batch', $pembelian->tgl_batch, array('tgl_batch', 'type' => 'text', 'class' => 'form-control datepicker','placeholder' => 'Tanggal Batch', 'id' => 'tgl_batch', 'autocomplete' => 'off')) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('id_batch', 'Kode Batch | F4') !!}
        <div class="input-group">
            {!! Form::text('id_batch', $pembelian->id_batch, array('id'=>'id_batch', 'class' => 'form-control', 'placeholder'=>'Kode Batch', 'autocomplete' => 'off')) !!}
            <div class="input-group-append">
                <span class="btn btn-primary mb-4"  data-toggle="modal" data-placement="top" title="Tambahkan Item" id="add_row_pembelian"><i class="fa fa-plus-square"></i></span>
                <input type="hidden" name="counter" id="counter" value="<?php echo $no ?>"> 
            </div>
        </div>
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('harga_jual', 'Harga Jual') !!}
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            {!! Form::text('harga_jual', $pembelian->harga_jual, array('id' => 'harga_jual', 'class' => 'form-control', 'placeholder'=>'akan terisi otomatis', 'readonly' => 'readonly')) !!}
        </div>
    </div>
    <div class="form-group col-md-1">
        {!! Form::label('margin', 'Margin') !!}
        <div class="input-group">
            {!! Form::text('margin', $pembelian->margin, array('id' => 'margin', 'class' => 'form-control', 'placeholder'=>'akan terisi otomatis', 'readonly' => 'readonly')) !!}
            <span class="input-group-text">%</span>
        </div>
    </div>
</div>
@endif
<?php $is_revisi = 0;?>
<div class="row">
    <div class="form-group col-md-12">
        <div class="box box-success" id="detail_data_pembelian">
            <div class="box-body">
                <div class="table-responsive">
                    <table id="tb_nota_pembelian" class="table table-bordered">
                        <thead>
                            <tr class="bg-gray color-palette">
                                <td width="5%" class="text-center"><strong>No.</strong></td>
                                <td width="5%" class="text-center"><strong>Action</strong></td>
                                <td width="30%" class="text-center"><strong>Nama Obat</strong></td>
                                <td width="10%" class="text-center"><strong>Total I</strong></td>
                                <td width="8%" class="text-center"><strong>Diskon(Rp)</strong></td>
                                <td width="8%" class="text-center"><strong>Diskon(%)</strong></td>
                                <td width="10%" class="text-center"><strong>Total II</strong></td>
                                <td width="8%" class="text-center"><strong>Jumlah</strong></td>
                                <td width="8%" class="text-center"><strong>HB</strong></td>
                                <td width="8%" class="text-center"><strong>HBPPN</strong></td>
                                <td width="8%" class="text-center"><strong>Margin</strong></td>
                            </tr>
                        </thead>
                        <tbody>
                            
                        </tbody>
                    </table>
                    <br>
                </div>
            </div>
        </div>
    </div>
</div>
<hr style="border: 1px solid #004d40; padding: 0px; margin-top: 0px; margin-bottom: 10px;">
<div class="row">
    <div class="form-group col-md-2">
        {!! Form::label('total1', 'Total I') !!}
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            {!! Form::text('total1', $pembelian->total1, array('id' => 'total1','class' => 'form-control required uang', 'placeholder'=>'Total I', 'readonly' => 'readonly')) !!}
        </div>
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('diskon1', 'Diskon I') !!}
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            {!! Form::text('diskon1', $pembelian->diskon1, array('id' => 'diskon1','class' => 'form-control required uang', 'placeholder'=>'Diskon I', 'readonly' => 'readonly')) !!}
        </div>
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('total2', 'Total II') !!}
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            {!! Form::text('total2', $pembelian->total2, array('id' => 'total2','class' => 'form-control required uang', 'placeholder'=>'Total II', 'readonly' => 'readonly')) !!}
        </div>
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('diskon2', 'Diskon II | F9') !!}
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            {!! Form::text('diskon2', $pembelian->diskon2, array('id' => 'diskon2','class' => 'form-control required uang', 'placeholder'=>'Diskon II', 'autocomplete' => 'off')) !!}
        </div>
    </div>
    <div class="form-group col-md-1">
        {!! Form::label('ppn', 'PPN') !!}
        {!! Form::text('ppn', $pembelian->ppn, array('id' => 'ppn', 'class' => 'form-control required', 'placeholder'=>'PPN', 'autocomplete' => 'off', 'readonly' => 'readonly')) !!}
    </div>
    <div class="form-group col-md-3">
        {!! Form::label('total_pembelian', 'Total Pembelian') !!}
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            {!! Form::text('total_pembelian', $pembelian->total_pembelian, array('id' => 'total_pembelian', 'class' => 'form-control required uang', 'placeholder'=>'Total Pembelian', 'readonly' => 'readonly')) !!}
        </div>
    </div>
</div>

@if($is_revisi > 0)
    <br>
    <div class="row">
        <div class="col-md-12">
            <div class="card card-info card-outline" id="main-box" style="">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list"></i>
                        Histori Revisi Jumlah Item Pembelian </b></small>
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <table  id="tb_pembelian_revisi" class="table table-bordered table-striped table-hover" width="100%">
                <thead>
                    <tr>
                        <th width="3%" class="text-center">No.</th>
                        <th width="5%" class="text-center">Tanggal</th>
                        <th width="42%" class="text-center">Detail Obat</th>
                        <th width="10%" class="text-center">Kasir</th>
                        <th width="10%" class="text-center">Jumlah Awal</th>
                        <th width="10%" class="text-center">Jumlah Akhir</th>
                        <th width="10%" class="text-center">Harga Awal</th>
                        <th width="10%" class="text-center">Harga Akhir</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
    @endif