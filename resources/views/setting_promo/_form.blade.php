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
    <div class="form-group col-sm-6">
        {!! Form::label('nama', 'Nama') !!}
        {!! Form::text('nama', $setting_promo->nama, array('class' => 'form-control required', 'placeholder'=>'Masukan Nama', 'autocomplete' => 'off')) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('tgl_awal', 'Tanggal Mulai') !!}
        {!! Form::text('tgl_awal', $setting_promo->tgl_awal, array('type' => 'text', 'class' => 'form-control datetimepicker-input','placeholder' => 'Tanggal Mulai', 'id' => 'tgl_awal', 'autocomplete' => 'off')) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('tgl_akhir', 'Tanggal Selesai') !!}
        {!! Form::text('tgl_akhir', $setting_promo->tgl_akhir, array('type' => 'text', 'class' => 'form-control datetimepicker-input','placeholder' => 'Tanggal Akhir', 'id' => 'tgl_akhir', 'autocomplete' => 'off')) !!}
    </div>
    <div class="form-group col-sm-2">
        {!! Form::label('id_jenis_promo', 'Jenis Promo') !!}
        {!! Form::select('id_jenis_promo', $jenis_promos, $setting_promo->id_jenis_promo, ['class' => 'form-control input_select required', 'autocomplete' => 'off']) !!}
    </div>
    <div class="form-group col-md-12">
        {!! Form::label('apotek', 'Pilih Apotek') !!}
        {!! Form::select('id_apotek[]', $apoteks, $setting_promo->details, ['class' => 'form-control input_select required', 'multiple'=>'multiple']) !!}
    </div>
    <div class="form-group col-sm-6">
        {!! Form::label('id_tipe_member', 'Tipe Member') !!}
        {!! Form::select('id_tipe_member', $tipe_members, $setting_promo->id_tipe_member, ['class' => 'form-control input_select required', 'autocomplete' => 'off']) !!}
    </div>
    <div class="form-group col-sm-2">
        {!! Form::label('pembelian_ke', 'Pembelian-ke') !!}
        {!! Form::text('pembelian_ke', $setting_promo->pembelian_ke, array('class' => 'form-control required', 'placeholder'=>'Pembelian-ke', 'autocomplete' => 'off')) !!}
    </div>
    <div class="form-group col-sm-2" id="show_diskon_persen" style="display: none;">
        {!! Form::label('persen_diskon', '% Diskon') !!}
        {!! Form::text('persen_diskon', $setting_promo->persen_diskon, array('class' => 'form-control required', 'placeholder'=>'Masukan % Diskon', 'autocomplete' => 'off')) !!}
    </div>
    <div class="form-group col-sm-2" id="show_diskon_rp" style="display: none;">
        {!! Form::label('rp_diskon', 'Rp Diskon') !!}
        {!! Form::text('rp_diskon', $setting_promo->rp_diskon, array('class' => 'form-control required', 'placeholder'=>'Masukan Jumlah Diskon', 'autocomplete' => 'off')) !!}
    </div>
     <div class="form-group col-md-12">
        {!! Form::label('ketentuan', 'Ketentuan') !!}
        {!! Form::textarea('ketentuan', $setting_promo->ketentuan, array('id' => 'ketentuan', 'class' => 'form-control', 'placeholder'=>'Ketentuan', 'autocomplete' => 'off')) !!}
    </div>
</div>
<hr style="border: 1px solid #004d40; padding: 0px; margin-top: 0px; margin-bottom: 10px;">
<!-- ini nanti dihilangin ketika setting diskon sudah diperbaiki -->
<div class="row">
    <div class="form-group col-md-3">
        {!! Form::label('id_obat', 'Kode Obat | Shift') !!}
        <div class="input-group">
            {!! Form::hidden('id_obat', $setting_promo_item_beli->id_obat, array('id' => 'id_obat', 'class' => 'form-control', 'placeholder'=>'Masukan Obat')) !!}
            {!! Form::text('barcode', $setting_promo_item_beli->barcode, array('id' => 'barcode', 'class' => 'form-control', 'placeholder'=>'Masukan Barcode', 'autocomplete' => 'off')) !!}
            <div class="input-group-append">
                <span class="btn btn-primary"  data-toggle="modal" data-placement="top" title="Cari Item Obat" onclick="open_data_obat('')"><i class="fa fa-search"></i> | Ctrl</span>
            </div>
        </div>
    </div>
    <div class="form-group col-md-7">
        {!! Form::label('id_obat', 'Nama Obat') !!}
        {!! Form::text('nama_obat', $setting_promo_item_beli->nama_obat, array('id' => 'nama_obat', 'class' => 'form-control', 'placeholder'=>'Nama Obat', 'readonly' => 'readonly')) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('jumlah', 'Jumlah') !!}
        {!! Form::text('jumlah', $setting_promo_item_beli->jumlah, array('id' => 'jumlah', 'class' => 'form-control', 'placeholder'=>'Jumlah', 'autocomplete' => 'off')) !!}
    </div>
</div>