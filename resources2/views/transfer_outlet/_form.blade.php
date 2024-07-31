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

    /*.dataTables_filter {
        display: none;
    }*/
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
        if($transfer_outlet->is_status != 1) {
            $status_confirm = '<span class="text-danger"><b>BELUM DIKONDIRMASI</b></span>';
        } else {
            $status_confirm = '<span class="text-info"><b>SUDAH DIKONDIRMASI</b></span>';
        }

        if($transfer_outlet->is_sign != 1) {
            $sign = '<span class="text-danger"><b>BELUM DITTD</b></span>';
        } else {
            $sign = '<span class="text-info"><b>SUDAH DITTD</b></span>';
        }
    ?>
    <div class="col-sm-3">
        <address>
            <strong>NOMOR NOTA : {{ $transfer_outlet->id }}</strong><br>
            Tanggal : {{ $transfer_outlet->tgl_nota }}<br>
            Kasir : {{ $transfer_outlet->created_oleh->nama }}<br>
            Apotek Tujuan : {{ $transfer_outlet->apotek_tujuan->nama_singkat }}
        </address>
    </div>
    <div class="col-sm-3">
        Status Konfirmasi : {!! $status_confirm !!}<br>
        Sign : {!! $sign !!}
    </div>
    @endif
    <div class="col-sm-3">
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
    {!! Form::hidden('is_from_transfer', 0, array('class' => 'form-control', 'id'=>'is_from_transfer')) !!}
    {!! Form::hidden('is_margin', 0, array('class' => 'form-control', 'id'=>'is_margin')) !!}
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
        @if($transfer_outlet->is_status == 0)
            <span class="btn btn-danger"  data-toggle="modal" data-placement="top" title="Ganti Apotek" id="change_apotek_" onclick="change_apotek({{$transfer_outlet->id}})"><i class="fa fa-fw fa-exchange-alt"></i> | Change</span>
        @endif
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
@if($transfer_outlet->is_status != 1 AND $transfer_outlet->is_sign != 1)
<div class="row">
    <div class="form-group col-md-2" id="id_obat_form">
        {!! Form::label('id_obat', 'Kode Obat | Shift') !!}
        <div class="input-group">
            {!! Form::hidden('id_obat', $transfer_outlet->id_obat, array('id' => 'id_obat', 'class' => 'form-control', 'placeholder'=>'Masukan Obat')) !!}
            {!! Form::hidden('stok_obat', $transfer_outlet->stok_obat, array('id' => 'stok_obat', 'class' => 'form-control', 'placeholder'=>'Masukan Obat')) !!}
            {!! Form::text('barcode', $transfer_outlet->barcode, array('id' => 'barcode', 'class' => 'form-control', 'placeholder'=>'Barcode/SKU/Nama Obat', 'autocomplete' => 'off')) !!}
            <div class="input-group-append">
                <span class="btn btn-primary mb-4"  data-toggle="modal" data-placement="top" title="Cari Item Obat" onclick="open_data_obat()"><i class="fa fa-search"></i> | Ctrl</span>
            </div>
        </div>
    </div>
    <div class="form-group col-md-6" id="nama_form">
        {!! Form::label('id_obat', 'Nama Obat') !!}
        {!! Form::text('nama_obat', $transfer_outlet->nama_obat, array('id' => 'nama_obat', 'class' => 'form-control', 'placeholder'=>'Nama Obat', 'readonly' => 'readonly')) !!}
    </div>
    <div class="form-group col-md-2" id="harga_outlet_form">
        {!! Form::label('harga_outlet', 'Harga') !!}
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="btn btn-default mb-4">Rp</span>
            </div>
            {!! Form::hidden('harga_outlet_default', $transfer_outlet->harga_outlet, array('id' => 'harga_outlet_default', 'class' => 'form-control', 'placeholder'=>'Masukan Obat')) !!}
            {!! Form::hidden('persen', 0, array('id' => 'persen', 'class' => 'form-control', 'placeholder'=>'Masukan Obat')) !!}
            {!! Form::text('harga_outlet', $transfer_outlet->harga_outlet, array('id' => 'harga_outlet', 'class' => 'form-control', 'placeholder'=>'terisi otomatis', 'autocomplete' => 'off')) !!}
            <!-- <div class="input-group-append">
                <span class="btn btn-primary mb-4"  data-toggle="modal" data-placement="top" title="List Harga" onclick="open_list_harga()"><i class="fa fa-list"></i></span>
            </div> -->
        </div>
    </div>
    <div class="form-group col-md-2" id="jumlah_form">
        {!! Form::label('jumlah', 'Jumlah') !!}
         <div class="input-group">
            {!! Form::text('jumlah', $transfer_outlet->jumlah, array('id'=>'jumlah', 'class' => 'form-control', 'placeholder'=>'Jumlah', 'autocomplete' => 'off')) !!}
            <div class="input-group-append">
                <span class="btn btn-primary mb-4"  data-toggle="modal" data-placement="top" title="Tambahkan Item" id="add_row_transfer_outlet"><i class="fa fa-plus-square"></i></span>
                <?php
                    $no = $jum;
                ?>
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