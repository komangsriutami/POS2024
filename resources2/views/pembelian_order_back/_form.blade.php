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
        <address>
            <strong>NOMOR NOTA : {{ $pembelian->id }}</strong><br>
            Tanggal : {{ $pembelian->tgl_nota }}<br>
            Kasir : {{ $pembelian->created_oleh->nama }}<br>
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
    {!! Form::hidden('is_from_order', 1, array('class' => 'form-control', 'id'=>'is_from_order')) !!}
    <div class="form-group col-md-2">
        {!! Form::label('apotek', 'Pilih Apotek') !!}
        {!! Form::select('id_apotek', $apoteks, $pembelian->id_apotek, ['class' => 'form-control input_select required']) !!}
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
            {!! Form::text('suplier', $nama, array('id' => 'suplier', 'class' => 'form-control required', 'placeholder'=>'Masukan Nama Suplier')) !!}
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
        {!! Form::text('no_faktur', $pembelian->no_faktur, array('id' => 'no_faktur', 'class' => 'form-control required', 'placeholder'=>'Masukan No Faktur')) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('tgl_faktur', 'Tanggal Faktur') !!}
        {!! Form::text('tgl_faktur', $pembelian->tgl_faktur, array('type' => 'text', 'class' => 'form-control datetimepicker-input','placeholder' => 'Tanggal Faktur', 'id' => 'tgl_faktur')) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('tgl_jatuh_tempo', 'Tanggal Jatuh Tempo') !!}
        {!! Form::text('tgl_jatuh_tempo', $pembelian->tgl_jatuh_tempo, array('type' => 'text', 'class' => 'form-control datepicker','placeholder' => 'Tanggal Jatuh Tempo', 'id' => 'tgl_jatuh_tempo')) !!}
    </div>
</div>
<?php $no = 0; ?>
<?php 
    $detail_pembelians = $detail_pembelians;
    $jum = count($detail_pembelians);
?>
<hr style="border: 1px solid #004d40; padding: 0px; margin-top: 0px; margin-bottom: 10px;">
@if($var==1) 
<div class="row">
    <div class="form-group col-md-2">
        {!! Form::label('id_obat', 'Kode Obat | Shift') !!}
        <div class="input-group">
            {!! Form::hidden('id_obat', $pembelian->id_obat, array('id' => 'id_obat', 'class' => 'form-control', 'placeholder'=>'Masukan Obat')) !!}
            {!! Form::text('barcode', $pembelian->barcode, array('id' => 'barcode', 'class' => 'form-control', 'placeholder'=>'Masukan Barcode')) !!}
            <div class="input-group-append">
                <span class="btn btn-primary mb-4"  data-toggle="modal" data-placement="top" title="Cari Item Obat" onclick="open_data_obat()"><i class="fa fa-search"></i> | Ctrl</span>
            </div>
        </div>
    </div>
    <div class="form-group col-md-5">
        {!! Form::label('id_obat', 'Nama Obat') !!}
        {!! Form::text('nama_obat', $pembelian->nama_obat, array('id' => 'nama_obat', 'class' => 'form-control', 'placeholder'=>'Nama Obat', 'readonly' => 'readonly')) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('harga_beli', 'Harga Beli Sebelumnya') !!}
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            {!! Form::text('harga_beli_sebelumnya', $pembelian->harga_beli, array('id' => 'harga_beli_sebelumnya', 'class' => 'form-control', 'placeholder'=>'akan terisi otomatis', 'readonly' => 'readonly')) !!}
        </div>
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('harga_beli', 'Harga Beli Sekarang') !!}
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            {!! Form::text('harga_beli', $pembelian->harga_beli, array('id' => 'harga_beli', 'class' => 'form-control', 'placeholder'=>'akan terisi otomatis', 'readonly' => 'readonly')) !!}
        </div>
    </div>
    <div class="form-group col-md-1">
        {!! Form::label('jumlah', 'Jumlah') !!}
         <div class="input-group">
            <span class="input-group-text">@</span>
            {!! Form::text('jumlah', $pembelian->jumlah, array('id'=>'jumlah', 'class' => 'form-control', 'placeholder'=>'Jumlah')) !!}
        </div>
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('harga_beli', 'Total Harga') !!}
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            {!! Form::text('total_harga', $pembelian->total_harga, array('id' => 'total_harga', 'class' => 'form-control', 'placeholder'=>'Total Harga')) !!}
        </div>
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('diskon', 'Diskon (Rp)') !!}
        {!! Form::text('diskon', $pembelian->diskon, array('id' => 'diskon', 'class' => 'form-control', 'placeholder'=>'Diskon Rupiah')) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('diskon_persen', 'Diskon (%)') !!}
        {!! Form::text('diskon_persen', $pembelian->diskon_persen, array('id' => 'diskon_persen', 'class' => 'form-control', 'placeholder'=>'Diskon Persen')) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('tgl_batch', 'Tanggal Expired') !!}
        {!! Form::text('tgl_batch', $pembelian->tgl_batch, array('tgl_batch', 'type' => 'text', 'class' => 'form-control datepicker','placeholder' => 'Tanggal Batch', 'id' => 'tgl_batch')) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('id_batch', 'Kode Batch | F4') !!}
        <div class="input-group">
            {!! Form::text('id_batch', $pembelian->id_batch, array('id'=>'id_batch', 'class' => 'form-control', 'placeholder'=>'Kode Batch')) !!}
            <div class="input-group-append">
                <span class="btn btn-primary mb-4"  data-toggle="modal" data-placement="top" title="Tambahkan Item" id="add_row_pembelian"><i class="fa fa-plus-square"></i></span>
                <input type="hidden" name="counter" id="counter" value="<?php echo $no ?>"> 
            </div>
        </div>
    </div>
</div>
@endif
<?php $is_revisi = 0;?>
<div class="row">
    <div class="form-group col-md-12">
        <div class="box box-success" id="detail_data_penjualan">
            <div class="box-body">
                <div class="table-responsive">
                    <table id="tb_nota_pembelian" class="table table-bordered table-striped table-hover table-head-fixed text-nowrap mb-0">
                        <thead>
                            <tr class="bg-gray color-palette">
                                <td width="5%" class="text-center"><strong>No.</strong></td>
                                <td width="35%" class="text-center"><strong>Nama Obat</strong></td>
                                <td width="10%" class="text-center"><strong>Total I</strong></td>
                                <td width="10%" class="text-center"><strong>Jumlah</strong></td>
                                <td width="10%" class="text-center"><strong>Harga Beli</strong></td>
                                <td width="10%" class="text-center"><strong>Diskon(Rp)</strong></td>
                                <td width="10%" class="text-center"><strong>Diskon(%)</strong></td>
                                <td width="10%" class="text-center"><strong>Total II</strong></td>
                            </tr>
                        </thead>
                        <tbody>
                            @if($jum == 0)
                                @else
                                    <?php $no = 0; ?>
                                    @foreach($detail_pembelians as $detail_pembelian)
                                        <?php 
                                            $no++; 
                                            $dis_1 = $detail_pembelian['diskon_persen']/100 * $detail_pembelian['total_harga'];
                                            $dis_2 = $detail_pembelian['diskon'];
                                            $total_diskon = $dis_1 + $dis_2; 
                                            $total_2 = ($detail_pembelian['total_harga']) - ($total_diskon);
                                            $is_revisi = 0;
                                        ?>

                                        <tr>
                                            <td>
                                                <input type="checkbox" name="record" id="detail_pembelian[{{ $no }}][record]">
                                                <span class="label label-primary" onClick="edit_detail_from_order({!! $no !!}, {{ $detail_pembelian['id_detail_order']}}, {{ $detail_pembelian['id_obat'] }})" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i> Edit</span>
                                                {!! Form::hidden('detail_pembelian['.$no.'][id]', $detail_pembelian['id'], array('id' => 'id_'.$no, 'class' => 'form-control', 'placeholder'=>'ID', 'readonly' => 'readonly')) !!}
                                            </td>
                                            <td style="display: none;">
                                                {!! Form::text('detail_pembelian['.$no.'][jumlah_revisi]', 0, array('id' => 'jumlah_revisi_'.$no, 'class' => 'form-control', 'placeholder'=>'Jumlah Revisi', 'readonly' => 'readonly')) !!}
                                            </td>
                                            <td style="display: none;">
                                                {!! Form::text('detail_pembelian['.$no.'][id_jenis_revisi]', null, array('id' => 'id_jenis_revisi_'.$no, 'class' => 'form-control', 'placeholder'=>'ID jenis revisi', 'readonly' => 'readonly')) !!}
                                            </td>
                                            <td style="display: none;">
                                                {!! Form::text('detail_pembelian['.$no.'][id_obat]', $detail_pembelian['id_obat'], array('id' => 'id_obat_'.$no, 'class' => 'form-control', 'placeholder'=>'ID Obat', 'readonly' => 'readonly')) !!}
                                            </td>
                                            <td>
                                                {!! Form::text('detail_pembelian['.$no.'][nama_obat]', $detail_pembelian['nama_obat'], array('id' => 'nama_obat_'.$no, 'class' => 'form-control', 'placeholder'=>'Nama Obat', 'readonly' => 'readonly')) !!}
                                            </td>
                                            <td>
                                                {!! Form::text('detail_pembelian['.$no.'][jumlah]', $detail_pembelian['jumlah'], array('id' => 'jumlah_'.$no, 'class' => 'form-control', 'placeholder'=>'Masukan Jumlah', 'readonly' => 'readonly')) !!}
                                            </td>
                                            <td>
                                                {!! Form::text('detail_pembelian['.$no.'][harga_beli]', $detail_pembelian['harga_beli'], array('id' => 'harga_beli_'.$no, 'class' => 'form-control', 'placeholder'=>'Masukan Harga', 'readonly' => 'readonly')) !!}
                                            </td>
                                            <td>
                                                {!! Form::text('detail_pembelian['.$no.'][total_harga]', $detail_pembelian['total_harga'], array('id' => 'total_harga_'.$no, 'class' => 'form-control', 'placeholder'=>'Masukan Total', 'readonly' => 'readonly')) !!}
                                            </td>
                                            <td style="display:none;" id="hitung_total_{{ $no }}" class="hitung_total">{{ $detail_pembelian['total_harga'] }}
                                            </td>
                                            <td>
                                                {!! Form::text('detail_pembelian['.$no.'][diskon]', $detail_pembelian['diskon'], array('id' => 'diskon_'.$no, 'class' => 'form-control', 'placeholder'=>'Masukan Diskon', 'readonly' => 'readonly')) !!}
                                            </td>
                                            <td>
                                                {!! Form::text('detail_pembelian['.$no.'][diskon_persen]', $detail_pembelian['diskon_persen'], array('id' => 'diskon_persen_'.$no, 'class' => 'form-control', 'placeholder'=>'Masukan Diskon Persen', 'readonly' => 'readonly')) !!}
                                            </td>
                                            <td>
                                                {!! Form::text('detail_pembelian['.$no.'][total_2]', $total_2, array('id' => 'total_2_'.$no, 'class' => 'form-control', 'placeholder'=>'Masukan Total 2', 'readonly' => 'readonly')) !!}
                                            </td>
                                            <td style="display:none;">
                                                {!! Form::text('detail_pembelian['.$no.'][id_batch]', $detail_pembelian['id_batch'], array('id' => 'id_batch_'.$no, 'class' => 'form-control', 'placeholder'=>'Masukan ID Batch', 'readonly' => 'readonly')) !!}
                                            </td>
                                            <td style="display:none;">
                                                {!! Form::text('detail_pembelian['.$no.'][tgl_batch]', $detail_pembelian['tgl_batch'], array('id' => 'tgl_batch_'.$no, 'class' => 'form-control', 'placeholder'=>'Masukan Tanggal Batch', 'readonly' => 'readonly')) !!}
                                            </td>
                                            <td style="display:none;" id="hitung_diskon_{{ $no }}" class="hitung_diskon">{{ $detail_pembelian['diskon'] }}</td>
                                            <td style="display:none;" id="hitung_diskon_persen_{{ $no }}" class="hitung_diskon">{{ $detail_pembelian['total_diskon_persen'] }}</td>
                                        </tr>
                                @endforeach
                            @endif
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
            {!! Form::text('total1', $pembelian->total1, array('class' => 'form-control required uang', 'placeholder'=>'Total I', 'readonly' => 'readonly')) !!}
        </div>
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('diskon1', 'Diskon I') !!}
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            {!! Form::text('diskon1', $pembelian->diskon1, array('class' => 'form-control required uang', 'placeholder'=>'Diskon I', 'readonly' => 'readonly')) !!}
        </div>
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('total2', 'Total II') !!}
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            {!! Form::text('total2', $pembelian->total2, array('class' => 'form-control required uang', 'placeholder'=>'Total II', 'readonly' => 'readonly')) !!}
        </div>
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('diskon2', 'Diskon II | F9') !!}
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            {!! Form::text('diskon2', $pembelian->diskon2, array('class' => 'form-control required uang', 'placeholder'=>'Diskon II')) !!}
        </div>
    </div>
    <div class="form-group col-md-1">
        {!! Form::label('ppn', 'PPN') !!}
        {!! Form::text('ppn', $pembelian->ppn, array('class' => 'form-control required', 'placeholder'=>'PPN')) !!}
    </div>
    <div class="form-group col-md-3">
        {!! Form::label('total_pembelian', 'Total Pembelian') !!}
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            {!! Form::text('total_pembelian', $pembelian->total_pembelian, array('class' => 'form-control required uang', 'placeholder'=>'Total Pembelian', 'readonly' => 'readonly')) !!}
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