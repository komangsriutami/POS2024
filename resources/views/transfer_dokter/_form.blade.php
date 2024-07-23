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
    </div>
    <div class="col-sm-4">
        <div class="card bg-info">
          <div class="card-body box-profile">
            <div class="text-center">
                <h1 id="total_to_display">Rp 0, -</h1>
            </div>

          </div>
        </div>
    </div>
</div>
<hr style="border: 1px solid #004d40; padding: 0px; margin-top: 0px; margin-bottom: 10px;">
<div class="row">
    <div class="form-group col-md-3">
        {!! Form::label('id_dokter', 'Dokter') !!}
        {!! Form::select('id_dokter', $dokters, $transfer_dokter->id_dokter, ['id' => 'id_dokter', 'class' => 'form-control']) !!}
    </div>
    <div class="form-group col-md-9">
        {!! Form::label('keterangan', 'Keterangan') !!}
        {!! Form::text('keterangan', $transfer_dokter->keterangan, array('id'=> 'keterangan', 'class' => 'form-control', 'placeholder'=>'Masukan Keterangan', 'autocomplete' => 'off')) !!}
    </div>
</div>
<?php $no = 0; ?>
<?php 
    if ($var==1) {
        $jum = 0;
    } else {
        $detail_transfer_dokters = $transfer_dokter->detail_transfer_dokter;
        $jum = count($detail_transfer_dokters);
    }
    
    $detail_transfer_dokter = new App\TransaksiTDDetail;
?>
<hr style="border: 1px solid #004d40; padding: 0px; margin-top: 0px; margin-bottom: 10px;">
@if($var==1) 
<div class="row">
    <div class="form-group col-md-2">
        {!! Form::label('id_obat', 'Kode Obat | Shift') !!}
        <div class="input-group">
            {!! Form::hidden('id_obat', $transfer_dokter->id_obat, array('id' => 'id_obat', 'class' => 'form-control', 'placeholder'=>'Masukan Obat')) !!}
            {!! Form::hidden('stok_obat', $transfer_dokter->stok_obat, array('id' => 'stok_obat', 'class' => 'form-control', 'placeholder'=>'Masukan Obat')) !!}
            {!! Form::text('barcode', $transfer_dokter->barcode, array('id' => 'barcode', 'class' => 'form-control', 'placeholder'=>'Barcode/SKU/Nama Obat', 'autocomplete' => 'off')) !!}
            <div class="input-group-append">
                <span class="btn btn-primary mb-4"  data-toggle="modal" data-placement="top" title="Cari Item Obat" onclick="open_data_obat()"><i class="fa fa-search"></i> | Ctrl</span>
            </div>
        </div>
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('id_obat', 'Nama Obat') !!}
        {!! Form::text('nama_obat', $transfer_dokter->nama_obat, array('id' => 'nama_obat', 'class' => 'form-control', 'placeholder'=>'Nama Obat', 'readonly' => 'readonly')) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('harga_dokter', 'Harga') !!}
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            {!! Form::text('harga_dokter', $transfer_dokter->harga_dokter, array('id' => 'harga_dokter', 'class' => 'form-control', 'placeholder'=>'akan terisi otomatis', 'autocomplete' => 'off')) !!}
        </div>
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('jumlah', 'Jumlah') !!}
         <div class="input-group">
            {!! Form::text('jumlah', $transfer_dokter->jumlah, array('id'=>'jumlah', 'class' => 'form-control', 'placeholder'=>'Jumlah', 'autocomplete' => 'off')) !!}
            <div class="input-group-append">
                <span class="btn btn-primary mb-4"  data-toggle="modal" data-placement="top" title="Tambahkan Item" id="add_row_transfer_dokter"><i class="fa fa-plus-square"></i></span>
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
                    <table id="tb_nota_transfer_dokter" class="table table-bordered table-striped table-hover table-head-fixed text-nowrap mb-0">
                        <thead>
                            <tr class="bg-gray color-palette">
                                <td width="5%" class="text-center"><strong>No.</strong></td>
                                <td width="55%" class="text-center"><strong>Nama Obat</strong></td>
                                <td width="10%" class="text-center"><strong>Harga Beli</strong></td>
                                <td width="10%" class="text-center"><strong>Jumlah</strong></td>
                                <td width="10%" class="text-center"><strong>Total</strong></td>
                            </tr>
                        </thead>
                        <tbody>
                            @if($jum == 0)
                                @else
                                    <?php $no = 0; ?>
                                    @foreach($detail_transfer_dokters as $detail_transfer_dokter)
                                        <?php 
                                            $no++; 
                                            $total = $detail_transfer_dokter->total;
                                            $total = 'Rp '.number_format($total,0,',','.');
                                        ?>

                                        <tr>
                                            <td>
                                                <input type="checkbox" name="record" id="detail_transfer_dokter[{{ $no }}][record]">
                                                <span class="label label-primary" onClick="edit_detail({!! $no !!}, {!! $detail_transfer_dokter->id !!})" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i> Edit</span>
                                                <span class="label label-danger" onClick="hapus_detail(this, {!! $detail_transfer_dokter->id !!})" data-toggle="tooltip" data-placement="top" title="Cancel Transfer"><i class="fa fa-trash"></i> Hapus</span>
                                                {!! Form::hidden('detail_transfer_dokter['.$no.'][id]', $detail_transfer_dokter->id, array('id' => 'id_'.$no, 'class' => 'form-control', 'placeholder'=>'ID', 'readonly' => 'readonly')) !!}
                                            </td>
                                            <td style="display: none;">
                                                {!! Form::text('detail_transfer_dokter['.$no.'][id_obat]', $detail_transfer_dokter->id_obat, array('id' => 'id_obat_'.$no, 'class' => 'form-control', 'placeholder'=>'ID Obat', 'readonly' => 'readonly')) !!}
                                            </td>
                                            <td>
                                                {!! Form::text('detail_transfer_dokter['.$no.'][nama_obat]', $detail_transfer_dokter->obat->nama, array('id' => 'nama_obat_'.$no, 'class' => 'form-control', 'placeholder'=>'Nama Obat', 'readonly' => 'readonly')) !!}
                                            </td>
                                            <td style='text-align:right;'>
                                                {!! Form::text('detail_transfer_dokter['.$no.'][harga_dokter]', $detail_transfer_dokter->harga_dokter, array('id' => 'harga_dokter_'.$no, 'class' => 'form-control', 'placeholder'=>'Masukan Harga', 'readonly' => 'readonly')) !!}
                                            </td>
                                       
                                            <td style='text-align:right;'>
                                                {!! Form::text('detail_transfer_dokter['.$no.'][jumlah]', $detail_transfer_dokter->jumlah, array('id' => 'jumlah_'.$no, 'class' => 'form-control', 'placeholder'=>'Masukan Jumlah', 'readonly' => 'readonly')) !!}
                                            </td>
                                            <td style='text-align:right;' id="hitung_total_{{ $no }}" class="hitung_total" data-total="{{$detail_transfer_dokter->total}}">{{ $total }}
                                            </td>
                                        </tr>
                                @endforeach
                            @endif
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4">Total</td>
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