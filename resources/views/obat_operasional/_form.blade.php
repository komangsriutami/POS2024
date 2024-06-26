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
        if($obat_operasional->is_sign != 1) {
            $sign = '<span class="text-danger"><b>BELUM DITTD</b></span>';
        } else {
            $sign = '<span class="text-info"><b>SUDAH DITTD</b></span>';
        }
    ?>
    <div class="col-sm-3">
        <address>
            <strong>NOMOR NOTA : {{ $obat_operasional->id }}</strong><br>
            Tanggal : {{ $obat_operasional->tgl_nota }}<br>
            Kasir : {{ $obat_operasional->created_oleh->nama }}<br>
        </address>
    </div>
    <div class="col-sm-3">
        Sign : {!! $sign !!}
    </div>
    @endif
    <div class="col-sm-3">
        <div class="card bg-info">
          <div class="card-body box-profile">
            <div class="text-center">
                <h1 id="total_op_display">Rp 0, -</h1>
            </div>

          </div>
        </div>
    </div>
</div>
<hr style="border: 1px solid #004d40; padding: 0px; margin-top: 0px; margin-bottom: 10px;">
<div class="row">
    <div class="form-group col-md-12">
        {!! Form::label('keterangan', 'Keterangan') !!}
        {!! Form::text('keterangan', $obat_operasional->keterangan, array('id'=> 'keterangan', 'class' => 'form-control required', 'placeholder'=>'Masukan Keterangan', 'autocomplete' => 'off')) !!}
    </div>
</div>
<?php $no = 0; ?>
<?php 
    if ($var==1) {
        $jum = 0;
    } else {
        $detail_obat_operasionals = $obat_operasional->detail_po;
        $jum = count($detail_obat_operasionals);
    }
    

    $detail_obat_operasional = new App\TransaksiPODetail;
?>
<hr style="border: 1px solid #004d40; padding: 0px; margin-top: 0px; margin-bottom: 10px;">
<input type="hidden" name="id" id="id" value="{{ $obat_operasional->id }}">
@if($obat_operasional->is_sign != 1)
<div class="row">
    <div class="form-group col-md-2">
        {!! Form::label('id_obat', 'Kode Obat | Shift') !!}
        <div class="input-group">
            {!! Form::hidden('id_obat', $obat_operasional->id_obat, array('id' => 'id_obat', 'class' => 'form-control', 'placeholder'=>'Masukan Obat')) !!}
            {!! Form::hidden('stok_obat', $obat_operasional->stok_obat, array('id' => 'stok_obat', 'class' => 'form-control', 'placeholder'=>'Masukan Obat')) !!}
            {!! Form::text('barcode', $obat_operasional->barcode, array('id' => 'barcode', 'class' => 'form-control', 'placeholder'=>'Masukan Barcode', 'autocomplete' => 'off')) !!}
            <div class="input-group-append">
                <span class="btn btn-primary mb-4"  data-toggle="modal" data-placement="top" title="Cari Item Obat" onclick="open_data_obat()"><i class="fa fa-search"></i> | Ctrl</span>
            </div>
        </div>
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('id_obat', 'Nama Obat') !!}
        {!! Form::text('nama_obat', $obat_operasional->nama_obat, array('id' => 'nama_obat', 'class' => 'form-control', 'placeholder'=>'Nama Obat', 'readonly' => 'readonly')) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('harga_jual', 'Harga') !!}
        <div class="input-group">
            <span class="input-group-text">Rp</span>
             {!! Form::hidden('hb_ppn', $obat_operasional->hb_ppn, array('id' => 'hb_ppn', 'class' => 'form-control', 'placeholder'=>'HB+ppn', 'readonly' => 'readonly')) !!}
            {!! Form::text('harga_jual', $obat_operasional->harga_jual, array('id' => 'harga_jual', 'class' => 'form-control', 'placeholder'=>'akan terisi otomatis', 'autocomplete' => 'off')) !!}
        </div>
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('jumlah', 'Jumlah') !!}
         <div class="input-group">
            {!! Form::text('jumlah', $obat_operasional->jumlah, array('id'=>'jumlah', 'class' => 'form-control', 'placeholder'=>'Jumlah', 'autocomplete' => 'off')) !!}
            <div class="input-group-append">
                <span class="btn btn-primary mb-4"  data-toggle="modal" data-placement="top" title="Tambahkan Item" id="add_row_obat_operasional"><i class="fa fa-plus-square"></i></span>
                <input type="hidden" name="counter" id="counter" value="<?php echo $no ?>"> 
            </div>
        </div>
    </div>
</div>
@endif
<div class="row">
    <div class="form-group col-md-12">
        <div class="box box-success" id="detail_data_penjualan">
            <div class="box-body">
                <table id="tb_nota_obat_operasional" class="table table-bordered table-striped table-hover table-head-fixed text-nowrap mb-0" width="100%">
                    <thead>
                        <tr class="bg-gray color-palette">
                            <td width="5%" class="text-center"><strong>No.</strong></td>
                            <td width="5%" class="text-center"><strong>Action</strong></td>
                            <td width="40%" class="text-center"><strong>Nama Obat</strong></td>
                            <td width="10%" class="text-center"><strong>HPP</strong></td>
                            <td width="10%" class="text-center"><strong>Harga Jual</strong></td>
                            <td width="10%" class="text-center"><strong>Jumlah</strong></td>
                            <td width="10%" class="text-center"><strong>Total</strong></td>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6">Total</td>
                            <td id="harga_po_total" style="text-align: right;"></td>
                        </tr>
                    </tfoot>
                </table>
                <br>
            </div>
        </div>
    </div>
</div>