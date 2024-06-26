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
<?php
    $status = 'Active';
    $ribbon = 'bg-primary';

    if($transfer_outlet->is_deleted == null) {
        $transfer_outlet->is_deleted = 0;
    }

    if($transfer_outlet->is_deleted == 1) {
        $status = 'Deleted';
        $ribbon = 'bg-danger';
    }
?>
<input type="hidden" name="id" id="id" value="{{ $transfer_outlet->id }}">
<input type="hidden" name="is_deleted" id="is_deleted" value="{{ $transfer_outlet->is_deleted }}">
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
            <strong>NOMOR NOTA : {{ $transfer_outlet->id }}</strong><br>
            Tanggal : {{ $transfer_outlet->tgl_nota }}<br>
            Kasir : {{ $transfer_outlet->created_oleh->nama }}<br>
            Apotek Tujuan : {{ $transfer_outlet->apotek_tujuan->nama_singkat }}
        </address>
    </div>
    @endif
    <div class="col-sm-4">
        <div class="card bg-info">
          <div class="card-body box-profile">
            <div class="ribbon-wrapper ribbon-lg">
                <div class="ribbon {{ $ribbon }}" id="status_nota">
                  {{ $status}}
                </div>
            </div>
            <div class="text-center">
                <h1 id="total_to_display">Rp 0, -</h1>
            </div>
          </div>
        </div>
    </div>
</div>
<hr style="border: 1px solid #004d40; padding: 0px; margin-top: 0px; margin-bottom: 10px;">
<div class="row">
    {!! Form::hidden('is_from_order', 0, array('class' => 'form-control', 'id'=>'is_from_order')) !!}
    {!! Form::hidden('is_margin', 1, array('class' => 'form-control', 'id'=>'is_margin')) !!}
    <div class="form-group col-md-2">
        {!! Form::label('apotek', 'Apotek Tujuan') !!}
        @if($var == 1)
            {!! Form::select('id_apotek_tujuan', $apoteks, $transfer_outlet->id_apotek_tujuan, ['id' => 'id_apotek_tujuan', 'class' => 'form-control']) !!}
        @else
            {!! Form::select('id_apotek_tujuan', $apoteks, $transfer_outlet->id_apotek_tujuan, ['id' => 'id_apotek_tujuan', 'class' => 'form-control', 'disabled'=>'disabled']) !!}
        @endif
    </div>
    @if($transfer_outlet->is_status != 1)
    <div class="form-group col-md-2">
        <label><span class="text-red">[Edit Apotek]</span></label><br>
        <span class="btn btn-danger"  data-toggle="modal" data-placement="top" title="Ganti Apotek" onclick="change_apotek({{$transfer_outlet->id}})"><i class="fa fa-fw fa-exchange-alt"></i> | Change</span>
    </div>
    @endif
    <div class="form-group col-md-8">
        {!! Form::label('keterangan', 'Keterangan') !!}
        {!! Form::text('keterangan', $transfer_outlet->keterangan, array('id'=> 'keterangan', 'class' => 'form-control', 'placeholder'=>'Masukan Keterangan', 'autocomplete' => 'off')) !!}
    </div>
</div>
<?php $no = 0; ?>
<?php 
    if ($var==1) {
        $jum = 0;
    } else {
        $detail_transfer_outlets = $transfer_outlet->detail_transfer_outlet;
        $jum = count($detail_transfer_outlets);
    }
    

    $detail_transfer_outlet = new App\TransaksiTODetail;
?>
<hr style="border: 1px solid #004d40; padding: 0px; margin-top: 0px; margin-bottom: 10px;">
<div class="row">
    <div class="form-group col-md-2">
        {!! Form::label('id_obat', 'Kode Obat | Shift') !!}
        <div class="input-group">
            {!! Form::hidden('id_obat', $transfer_outlet->id_obat, array('id' => 'id_obat', 'class' => 'form-control', 'placeholder'=>'Masukan Obat')) !!}
            {!! Form::hidden('stok_obat', $transfer_outlet->stok_obat, array('id' => 'stok_obat', 'class' => 'form-control', 'placeholder'=>'Masukan Obat')) !!}
            {!! Form::text('barcode', $transfer_outlet->barcode, array('id' => 'barcode', 'class' => 'form-control', 'placeholder'=>'Masukan Barcode', 'autocomplete' => 'off')) !!}
            <div class="input-group-append">
                <span class="btn btn-primary mb-4"  data-toggle="modal" data-placement="top" title="Cari Item Obat" onclick="open_data_obat()"><i class="fa fa-search"></i> | Ctrl</span>
            </div>
        </div>
    </div>
    <div class="form-group col-md-4">
        {!! Form::label('id_obat', 'Nama Obat') !!}
        {!! Form::text('nama_obat', $transfer_outlet->nama_obat, array('id' => 'nama_obat', 'class' => 'form-control', 'placeholder'=>'Nama Obat', 'readonly' => 'readonly')) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('harga_outlet_default', 'Harga Default') !!}
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="btn btn-default mb-4">Rp</span>
            </div>
           {!! Form::text('harga_outlet_default', $transfer_outlet->harga_outlet, array('id' => 'harga_outlet_default', 'class' => 'form-control', 'placeholder'=>'terisi otomatis', 'autocomplete' => 'off', 'readonly' => 'readonly')) !!}
        </div>
    </div>
    <div class="form-group col-md-1">
        {!! Form::label('persen', '% Tambahan') !!}
        <div class="input-group">
           {!! Form::text('persen', 0, array('id' => 'persen', 'class' => 'form-control', 'placeholder'=>'%', 'autocomplete' => 'off')) !!}
           <div class="input-group-prepend">
                <span class="btn btn-default mb-4">Rp</span>
            </div>
        </div>
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('harga_outlet', 'Harga') !!}
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="btn btn-default mb-4">Rp</span>
            </div>
           {!! Form::text('harga_outlet', $transfer_outlet->harga_outlet, array('id' => 'harga_outlet', 'class' => 'form-control', 'placeholder'=>'terisi otomatis', 'autocomplete' => 'off', 'readonly' => 'readonly')) !!}
        </div>
    </div>
    <div class="form-group col-md-1">
        {!! Form::label('jumlah', 'Jumlah') !!}
         <div class="input-group">
            {!! Form::text('jumlah', $transfer_outlet->jumlah, array('id'=>'jumlah', 'class' => 'form-control', 'placeholder'=>'Jumlah', 'autocomplete' => 'off')) !!}
            <div class="input-group-append">
                <span class="btn btn-primary mb-4"  data-toggle="modal" data-placement="top" title="Tambahkan Item" id="add_row_transfer_outlet"><i class="fa fa-plus-square"></i></span>
                <input type="hidden" name="counter" id="counter" value="<?php echo $no ?>"> 
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="form-group col-md-12">
        <div class="box box-success" id="detail_data_penjualan">
            <div class="box-body">
                <div class="table-responsive">
                    <table  id="tb_nota_transfer_outlet" class="table table-bordered table-striped table-hover table-head-fixed text-nowrap mb-0" width="100%">
                        <thead>
                            <tr class="bg-gray color-palette">
                                <td width="5%" class="text-center"><strong>No.</strong></td>
                                <td width="5%" class="text-center"><strong>Action</strong></td>
                                <td width="50%" class="text-center"><strong>Nama Obat</strong></td>
                                <td width="10%" class="text-center"><strong>Harga</strong></td>
                                <td width="10%" class="text-center"><strong>Jumlah</strong></td>
                                <td width="10%" class="text-center"><strong>Total</strong></td>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5"><p class="text-right text-bold" style="text-align: right;">Total</p></td>
                                <td id="harga_total" style="text-align: right;"></td>
                            </tr>
                        </tfoot>
                    </table>
                    <br>
                </div>
            </div>
        </div>
    </div>
</div>