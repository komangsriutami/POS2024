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
    <div class="col-sm-4">
    </div>
    <div class="col-sm-4">
        <div class="card bg-info">
          <div class="card-body box-profile">
            <div class="text-center">
                <h1 id="total_aset_display">Rp 0, -</h1>
            </div>

          </div>
        </div>
    </div>
</div>
<hr style="border: 1px solid #004d40; padding: 0px; margin-top: 0px; margin-bottom: 10px;">
<div class="row">
    <div class="form-group col-md-2">
        {!! Form::label('no_transaksi', 'No Transaksi (*)') !!}
        {!! Form::text('no_transaksi', $aset->no_transaksi, array('class' => 'form-control required', 'placeholder'=>'Masukan No Transaksi')) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('tgl_transaksi', 'Tanggal Transaksi (*)') !!}
        {!! Form::date('tgl_transaksi', $aset->tgl_transaksi, ['type' => 'text', 'class' => 'form-control datepicker required', 'placeholder' => 'Tanggal Transaksi', 'id' => 'tgl_transaksi', 'autocomplete' => 'off']) !!}
    </div>
    <div class="form-group col-md-8">
        {!! Form::label('keterangan', 'Keterangan') !!}
        {!! Form::text('keterangan', $aset->keterangan, array('id'=> 'keterangan', 'class' => 'form-control', 'placeholder'=>'Masukan Keterangan', 'autocomplete' => 'off')) !!}
    </div>
</div>
<?php $no = 0; ?>
<?php 
    if ($var==1) {
        $jum = 0;
    } else {
        $detail_asets = $aset->detail_aset;
        $jum = count($detail_asets);
    }
    
    $detail_transfer_outlet = new App\TransaksiTODetail;
?>
<hr style="border: 1px solid #004d40; padding: 0px; margin-top: 0px; margin-bottom: 10px;">
<div class="row">
    <div class="form-group col-md-2">
        {!! Form::label('id_aset', 'Kode Aset | Shift') !!}
        <div class="input-group">
            {!! Form::hidden('id_aset', $aset->id_aset, array('id' => 'id_aset', 'class' => 'form-control', 'placeholder'=>'ID Aset')) !!}
            {!! Form::text('kode_aset', $aset->kode_aset, array('id' => 'kode_aset', 'class' => 'form-control', 'placeholder'=>'Kode Aset', 'autocomplete' => 'off')) !!}
            <div class="input-group-append">
                <span class="btn btn-primary mb-4"  data-toggle="modal" data-placement="top" title="Cari Item Obat" onclick="open_data_aset()"><i class="fa fa-search"></i> | Ctrl</span>
            </div>
        </div>
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('id_aset', 'Nama Aset') !!}
        {!! Form::text('nama_aset', $aset->nama_aset, array('id' => 'nama_aset', 'class' => 'form-control', 'placeholder'=>'Nama Aset', 'readonly' => 'readonly')) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('id_dasar_harga', 'Pilih Dasar Harga') !!}
        <select id="id_dasar_harga" name="id_dasar_harga" class="form-control input_select">
            <option value="" {!!( "" == $aset->id_kondisi_aset ? 'selected' : '')!!}>-- Pilih --</option>
            <option value="1" {!!( "1" == $aset->id_dasar_harga ? 'selected' : '')!!}>Perolehan</option>
            <option value="2" {!!( "2" == $aset->id_dasar_harga ? 'selected' : '')!!}>Taksiran</option>
        </select>
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('id_kondisi_aset', 'Pilih Kondisi Aset') !!}
        <select id="id_kondisi_aset" name="id_kondisi_aset" class="form-control input_select">
            <option value="" {!!( "" == $aset->id_kondisi_aset ? 'selected' : '')!!}>-- Pilih --</option>
            <option value="1" {!!( "1" == $aset->id_kondisi_aset ? 'selected' : '')!!}>Baik</option>
            <option value="2" {!!( "2" == $aset->id_kondisi_aset ? 'selected' : '')!!}>Rusak Ringan</option>
            <option value="3" {!!( "3" == $aset->id_kondisi_aset ? 'selected' : '')!!}>Rusak Berat</option>
        </select>
    </div>
    <div class="form-group col-md-3">
        {!! Form::label('merek', 'Merk') !!}
         <div class="input-group">
            {!! Form::text('merek', $aset->merek, array('id'=>'merek', 'class' => 'form-control', 'placeholder'=>'Merk', 'autocomplete' => 'off')) !!}
        </div>
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('jumlah', 'Jumlah') !!}
         <div class="input-group">
            <div class="input-group-append">
                <span class="btn btn-secondary mb-4">@</span>
            </div>
            {!! Form::text('jumlah', $aset->jumlah, array('id'=>'jumlah', 'class' => 'form-control', 'placeholder'=>'Jumlah', 'autocomplete' => 'off')) !!}
        </div>
    </div>
    <div class="form-group col-md-3">
        {!! Form::label('nilai_satuan', 'Nilai Satuan') !!}
         <div class="input-group">
             <div class="input-group-append">
                <span class="btn btn-secondary mb-4">Rp</span>
            </div>
            {!! Form::text('nilai_satuan', $aset->nilai_satuan, array('id'=>'nilai_satuan', 'class' => 'form-control', 'placeholder'=>'Nilai Satuan', 'autocomplete' => 'off')) !!}
            <div class="input-group-append">
                <span class="btn btn-primary mb-4"  data-toggle="modal" data-placement="top" title="Tambahkan Item" id="add_row_aset"><i class="fa fa-plus-square"></i></span>
                <input type="hidden" name="counter" id="counter" value="<?php echo $no ?>"> 
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="form-group col-md-12">
        <div class="box box-success" id="detail_data_aset">
            <div class="box-body">
                <div class="table-responsive">
                    <table id="tb_aset" class="table table-bordered table-striped table-hover table-head-fixed text-nowrap mb-0">
                        <thead>
                            <tr class="bg-gray color-palette">
                                <td width="5%" class="text-center"><strong>No.</strong></td>
                                <td width="35%" class="text-center"><strong>Nama Aset</strong></td>
                                <td width="10%" class="text-center"><strong>Dasar Harga</strong></td>
                                <td width="10%" class="text-center"><strong>Kondisi Aset</strong></td>
                                <td width="10%" class="text-center"><strong>Merk</strong></td>
                                <td width="10%" class="text-center"><strong>Jumlah</strong></td>
                                <td width="10%" class="text-center"><strong>Nilai</strong></td>
                                <td width="10%" class="text-center"><strong>Total</strong></td>
                            </tr>
                        </thead>
                        <tbody>
                            @if($jum == 0)
                                @else
                                    <?php $no = 0; ?>
                                    @foreach($detail_asets as $detail_aset)
                                        <?php 
                                            $no++; 
                                            $nilai_satuan = $detail_aset->nilai_satuan;
                                            $nilai_satuan = 'Rp '.number_format($nilai_satuan,0,',','.');
                                            $total_nilai = $detail_aset->total_nilai;
                                            $total_nilai = 'Rp '.number_format($total_nilai,0,',','.');
                                            $dasar_harga = '<span class="text-info">[Belum ditentukan]</span>';
                                            if($detail_aset->id_dasar_harga == 1) {
                                                $dasar_harga = '<span class="text-success">[Perolehan]</span>';
                                            } else if($detail_aset->id_dasar_harga == 2) {
                                                $dasar_harga = '<span class="text-danger">[Taksiran]</span>';
                                            }
                                            $kondisi_aset = '<span class="text-info">[Belum ditentukan]</span>';
                                            if($detail_aset->id_kondisi_aset == 1) {
                                                $kondisi_aset = '<span class="text-success">[Baik]</span>';
                                            } else if($detail_aset->id_kondisi_aset == 2) {
                                                $kondisi_aset = '<span class="text-danger">[Rusak Ringan]</span>';
                                            } else if($detail_aset->id_kondisi_aset == 3) {
                                                $kondisi_aset = '<span class="text-danger">[Rusak Berat]</span>';
                                            }
                                        ?>

                                        <tr>
                                            <td>
                                                <input type="checkbox" name="record" id="detail_transfer_outlet[{{ $no }}][record]">
                                                    <span class="label label-primary" onClick="edit_detail({!! $no !!}, {!! $detail_aset->id !!})" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i> Edit</span>
                                                    <span class="label label-danger" onClick="hapus_detail(this, {!! $detail_aset->id !!})" data-toggle="tooltip" data-placement="top" title="Cancel Transfer"><i class="fa fa-trash"></i> Hapus</span>
                                                {!! Form::hidden('detail_aset['.$no.'][id]', $detail_aset->id, array('id' => 'id_'.$no, 'class' => 'form-control', 'placeholder'=>'ID', 'readonly' => 'readonly')) !!}
                                            </td>
                                            <td style="display: none;">
                                                {!! Form::hidden('detail_aset['.$no.'][id_aset]', $detail_aset->id_aset, array('id' => 'id_aset_'.$no, 'class' => 'form-control', 'placeholder'=>'ID Aset', 'readonly' => 'readonly')) !!}
                                            </td>
                                            <td>
                                                {!! Form::hidden('detail_aset['.$no.'][nama_aset]', $detail_aset->aset->nama, array('id' => 'nama_aset_'.$no, 'class' => 'form-control', 'placeholder'=>'Nama Aset', 'readonly' => 'readonly')) !!}
                                                {{ $detail_aset->aset->nama }} 
                                            </td>
                                            <td style='text-align:right;'>
                                                {!! Form::hidden('detail_aset['.$no.'][id_dasar_harga]', $detail_aset->id_dasar_harga, array('id' => 'id_dasar_harga_'.$no, 'class' => 'form-control', 'placeholder'=>'Masukan Dasar Harga', 'readonly' => 'readonly')) !!}

                                                <span id="id_dasar_harga_html_{{$no}}">{!! $dasar_harga !!}</span>
                                            </td>
                                            <td style='text-align:right;'>
                                                {!! Form::hidden('detail_aset['.$no.'][id_kondisi_aset]', $detail_aset->id_kondisi_aset, array('id' => 'id_kondisi_aset_'.$no, 'class' => 'form-control', 'placeholder'=>'Masukan Kondisi Aset', 'readonly' => 'readonly')) !!}

                                                <span id="id_kondisi_aset_html_{{$no}}">{!! $kondisi_aset !!} </span>
                                            </td>
                                            <td style='text-align:center;'>
                                                {!! Form::hidden('detail_aset['.$no.'][merek]', $detail_aset->merek, array('id' => 'merek_'.$no, 'class' => 'form-control', 'placeholder'=>'Masukan Merk', 'readonly' => 'readonly')) !!}

                                                <span id="merek_html_{{$no}}">{{ $detail_aset->merek }} </span>
                                            </td>
                                            <td style='text-align:center;'>
                                                {!! Form::hidden('detail_aset['.$no.'][jumlah]', $detail_aset->jumlah, array('id' => 'jumlah_'.$no, 'class' => 'form-control', 'placeholder'=>'Masukan Jumlah', 'readonly' => 'readonly')) !!}

                                                <span id="jumlah_html_{{$no}}">{{ $detail_aset->jumlah }}</span>
                                            </td>
                                            <td style='text-align:right;'>
                                                {!! Form::hidden('detail_aset['.$no.'][nilai_satuan]', $detail_aset->nilai_satuan, array('id' => 'nilai_satuan_'.$no, 'class' => 'form-control', 'placeholder'=>'Masukan Nilai Satuan', 'readonly' => 'readonly')) !!}

                                                <span id="nilai_satuan_html_{{$no}}">{{ $nilai_satuan }}</span>
                                            </td>
                                            <td style='text-align:right;'>
                                                {!! Form::hidden('detail_aset['.$no.'][total_nilai]', $detail_aset->total_nilai, array('id' => 'total_nilai_'.$no, 'class' => 'form-control', 'placeholder'=>'Total Nilai', 'readonly' => 'readonly')) !!}

                                                <span id="total_nilai_html_{{$no}}">{{ $total_nilai }}</span>
                                            </td>
                                            <td style='display: none;' id="hitung_total_{{ $no }}" class="hitung_total" data-total="{{$detail_aset->total_nilai}}">
                                                {{ $detail_aset->total_nilai }}
                                            </td>
                                        </tr>
                                @endforeach
                            @endif
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="7">Total</td>
                                <input type="hidden" name="total_nilai_aset" id="total_nilai_aset">
                                <td id="nilai_total" style="text-align: right;"></td>
                            </tr>
                        </tfoot>
                    </table>
                    <br>
                </div>
            </div>
        </div>
    </div>
</div>