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
    if ($var==2) {
        $detail_orders = $order->detail_order;
        $jum = count($detail_orders);
    } else {
        $jum = 0;
    }
    

    $detail_order = new App\TransaksiOrderDetail;
?>
<div class="row">
    <div class="form-group col-md-3">
        {!! Form::label('id_apotek', 'Apotek') !!}
        {!! Form::select('id_apotek', $apoteks, $order->id_apotek, ['id' => 'id_apotek','class' => 'form-control required']) !!}
    </div>
    <div class="form-group col-md-3">
        {!! Form::label('id_suplier', 'Suplier') !!}
        {!! Form::select('id_suplier', $supliers, $order->id_suplier, ['id' => 'id_suplier','class' => 'form-control required']) !!}
    </div>
    <div class="form-group col-md-3">
        {!! Form::label('id_jenis', 'Jenis SP') !!}
        {!! Form::select('id_jenis', $jenisSP, $order->id_jenis, ['id' => 'id_jenis','class' => 'form-control required']) !!}
    </div>
</div>
@if($var==1) 
<?php $no = count($defectas)+1; ?>
@else
<?php $no = count($detail_orders)+1; ?>
@endif
<hr>
<div class="row">
    <div class="form-group col-md-2">
        {!! Form::label('id_obat', 'Kode Obat | Shift') !!}
        <div class="input-group">
            <div class="input-group-prepend">
                {!! Form::hidden('id_obat', $order->id_obat, array('id' => 'id_obat', 'class' => 'form-control', 'placeholder'=>'Masukan Obat')) !!}
                {!! Form::hidden('stok_obat', $order->stok_obat, array('id' => 'stok_obat', 'class' => 'form-control', 'placeholder'=>'Masukan Obat')) !!}
                {!! Form::text('barcode', $order->barcode, array('id' => 'barcode', 'class' => 'form-control', 'placeholder'=>'Masukan Barcode')) !!}
                <div class="input-group-append">
                    <span class="btn btn-primary mb-4"  data-toggle="modal" data-placement="top" title="Cari Item Obat" onclick="open_data_obat()"><i class="fa fa-search"></i></span>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('id_obat', 'Nama Obat') !!}
        {!! Form::text('nama_obat', $order->nama_obat, array('id' => 'nama_obat', 'class' => 'form-control', 'placeholder'=>'Nama Obat', 'readonly' => 'readonly')) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('id_satuan', 'Satuan') !!}
        {!! Form::select('id_satuan', $satuans, null, ['id' => 'id_satuan','class' => 'form-control input_select']) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('jumlah', 'Jumlah | F4') !!}
        <div class="input-group">
            <div class="input-group-prepend">
                {!! Form::text('jumlah', $order->jumlah, array('id' => 'jumlah', 'class' => 'form-control', 'placeholder'=>'Jumlah')) !!}
                <div class="input-group-append">
                    <span class="btn btn-primary mb-4"  data-toggle="modal" data-placement="top" title="Tambahkan Item" id="add_row_order"><i class="fa fa-plus-square"></i></span>
                    <input type="hidden" name="counter" id="counter" value="<?php echo $no ?>"> 
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
    <div class="box box-success" id="detail_data_order">
        <div class="box-body">
            <!-- <button class="btn btn-primary mb-4" data-toggle="modal" data-target="#itemModal">Tambah Item</button> -->
            <div class="table-responsive">
                <table  id="tb_nota_order" class="table table-bordered table-striped table-hover table-head-fixed text-nowrap mb-0">
                    <thead>
                        <tr class="bg-gray color-palette">
                            <td width="3%"><strong>No.</strong></td>
                            <td width="45%"><strong>Nama Obat</strong></td>
                            <td width="10%"><strong>Jumlah Diajukan</strong></td>
                            <td width="10%"><strong>Jumlah Order</strong></td>
                            <td width="10%"><strong>Satuan</strong></td>
                            <td width="17%"><strong>Komentar/Catatan</strong></td>
                            <td width="5%"><strong>Action</strong></td>
                        </tr>
                    </thead>
                    <tbody>
                        @if($jum == 0)
                            <?php $no = 0; ?>
                            @foreach($defectas as $defecta)
                            <?php 
                                $no++; 
                            ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="record" id="detail_order[{{ $no }}][record]">
                                    {!! Form::hidden('detail_order['.$no.'][id]', null, array('id' => 'id_'.$no, 'class' => 'form-control', 'placeholder'=>'ID', 'readonly' => 'readonly')) !!}
                                    {!! Form::hidden('detail_order['.$no.'][id_satuan]', $defecta->id_satuan, array('id' => 'id_satuan_'.$no, 'class' => 'form-control', 'placeholder'=>'ID Satuan', 'readonly' => 'readonly')) !!}
                                    {!! Form::hidden('detail_order['.$no.'][is_purchasing_add]', 0, array('is_purchasing_add' => 'is_purchasing_add_'.$no, 'class' => 'form-control', 'placeholder'=>'ID', 'readonly' => 'readonly')) !!}
                                    {!! Form::hidden('detail_order['.$no.'][id_defecta]', $defecta->id, array('id_defect_'.$no => 'id_defecta_'.$no, 'class' => 'form-control', 'placeholder'=>'ID', 'readonly' => 'readonly')) !!}
                                     {!! Form::hidden('detail_order['.$no.'][id_apotek]', $defecta->id_apotek, array('id_apotek_'.$no => 'id_defecta_'.$no, 'class' => 'form-control', 'placeholder'=>'ID', 'readonly' => 'readonly')) !!}
                                </td>
                                <td style="display:none;">
                                    {!! Form::text('detail_order['.$no.'][id_obat]', $defecta->id_obat, array('id' => 'id_obat_'.$no, 'class' => 'form-control', 'placeholder'=>'ID Obat', 'readonly' => 'readonly')) !!}
                                </td>
                                <td>
                                    {!! Form::text('detail_order['.$no.'][nama_obat]', $defecta->nama, array('id' => 'nama_obat_'.$no, 'class' => 'form-control', 'placeholder'=>'Nama Obat', 'readonly' => 'readonly')) !!}
                                </td>
                                <td>
                                    {!! Form::text('detail_order['.$no.'][jumlah_diajukan]', $defecta->jumlah_diajukan, array('id' => 'jumlah_diajukan_'.$no, 'class' => 'form-control', 'placeholder'=>'Masukan Jumlah', 'readonly' => 'readonly')) !!}
                                </td>
                                <td>
                                    {!! Form::text('detail_order['.$no.'][jumlah]', $defecta->jumlah_order, array('id' => 'jumlah_order_'.$no, 'class' => 'form-control', 'placeholder'=>'Masukan Jumlah', 'readonly' => 'readonly')) !!}
                                </td>
                                <td>
                                    {!! Form::text('detail_order['.$no.'][satuan]', $defecta->satuan->satuan, array('id' => 'satuan_'.$no, 'class' => 'form-control', 'placeholder'=>'Masukan Satuan', 'readonly' => 'readonly')) !!}
                                </td>
                                <td>
                                    {!! Form::text('detail_order['.$no.'][keterangan]', $defecta->komentar, array('id' => 'komentar_'.$no, 'class' => 'form-control', 'placeholder'=>'Masukan Keterangan', 'readonly' => 'readonly')) !!}
                                </td>
                                <td>
                                    <span class="label label-primary btn-sm" onClick="edit_detail({!! $no !!}, {!! $defecta->id !!})" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i> Edit</span>
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <?php $no = 0; ?>
                            @foreach($detail_orders as $detail)
                            <?php 
                                $no++; 
                            ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="record" id="detail_order[{{ $no }}][record]">
                                    {!! Form::hidden('detail_order['.$no.'][id]', $detail->id, array('id' => 'id_'.$no, 'class' => 'form-control', 'placeholder'=>'ID', 'readonly' => 'readonly')) !!}
                                    {!! Form::hidden('detail_order['.$no.'][id_satuan]', $detail->id_satuan, array('id' => 'id_satuan_'.$no, 'class' => 'form-control', 'placeholder'=>'ID Satuan', 'readonly' => 'readonly')) !!}
                                    {!! Form::hidden('detail_order['.$no.'][is_purchasing_add]', $detail->is_purchasing_add, array('is_purchasing_add' => 'is_purchasing_add_'.$no, 'class' => 'form-control', 'placeholder'=>'ID', 'readonly' => 'readonly')) !!}
                                    {!! Form::hidden('detail_order['.$no.'][id_defecta]', $detail->id_defecta, array('id_defect_'.$no => 'id_defecta_'.$no, 'class' => 'form-control', 'placeholder'=>'ID', 'readonly' => 'readonly')) !!}
                                     {!! Form::hidden('detail_order['.$no.'][id_apotek]', $order->id_apotek, array('id_apotek_'.$no => 'id_defecta_'.$no, 'class' => 'form-control', 'placeholder'=>'ID', 'readonly' => 'readonly')) !!}
                                </td>
                                <td style="display:none;">
                                    {!! Form::text('detail_order['.$no.'][id_obat]', $detail->id_obat, array('id' => 'id_obat_'.$no, 'class' => 'form-control', 'placeholder'=>'ID Obat', 'readonly' => 'readonly')) !!}
                                </td>
                                <td>
                                    {!! Form::text('detail_order['.$no.'][nama_obat]', $detail->obat->nama, array('id' => 'nama_obat_'.$no, 'class' => 'form-control', 'placeholder'=>'Nama Obat', 'readonly' => 'readonly')) !!}
                                </td>
                                <td>
                                    {!! Form::text('detail_order['.$no.'][jumlah_diajukan]', $detail->jumlah, array('id' => 'jumlah_diajukan_'.$no, 'class' => 'form-control', 'placeholder'=>'Masukan Jumlah', 'readonly' => 'readonly')) !!}
                                </td>
                                <td>
                                    {!! Form::text('detail_order['.$no.'][jumlah]', $detail->jumlah, array('id' => 'jumlah_'.$no, 'class' => 'form-control', 'placeholder'=>'Masukan Jumlah', 'readonly' => 'readonly')) !!}
                                </td>
                                 <td>
                                    {!! Form::text('detail_order['.$no.'][satuan]', $detail->satuan->satuan, array('id' => 'satuan_'.$no, 'class' => 'form-control', 'placeholder'=>'Masukan Satuan', 'readonly' => 'readonly')) !!}
                                </td>
                                <td>
                                    {!! Form::text('detail_order['.$no.'][keterangan]', $detail->keterangan, array('id' => 'keterangan_'.$no, 'class' => 'form-control', 'placeholder'=>'Masukan Keterangan', 'readonly' => 'readonly')) !!}
                                </td>
                                <td>
                                    <span class="label label-primary btn-sm" onClick="edit_order({!! $no !!}, {!! $detail->id !!})" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i> Edit</span>
                                </td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

