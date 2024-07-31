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
    <div class="form-group col-md-3">
        {!! Form::label('barcode', 'Barcode') !!}
        {!! Form::text('barcode', $obat->barcode, array('class' => 'form-control required', 'placeholder'=>'Masukan Barcode', 'id' => 'barcode')) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('nama', 'Nama Obat') !!}
        {!! Form::text('nama', $obat->nama, array('class' => 'form-control required', 'placeholder'=>'Masukan Nama')) !!}
    </div>
    <div class="form-group col-md-3">
        {!! Form::label('id_satuan', 'Pilih Satuan') !!}
        {!! Form::select('id_satuan', $satuans, $obat->id_satuan, ['class' => 'form-control input_select required']) !!}
    </div>
    <div class="form-group col-md-3">
        {!! Form::label('id_produsen', 'Pilih Produsen') !!}
        {!! Form::select('id_produsen', $produsens, $obat->id_produsen, ['class' => 'form-control input_select required']) !!}
    </div>
    <div class="form-group col-md-3">
        {!! Form::label('id_penandaan_obat', 'Pilih Penandaan Obat') !!}
        {!! Form::select('id_penandaan_obat', $penandaan_obats, $obat->id_penandaan_obat, ['class' => 'form-control input_select required']) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('id_golongan_obat', 'Pilih Golongan') !!}
        {!! Form::select('id_golongan_obat', $golongan_obats, $obat->id_golongan_obat, ['class' => 'form-control input_select required']) !!}
    </div>
    <div class="form-group col-md-3">
        {!! Form::label('rak', 'rak') !!}
        {!! Form::text('rak', $obat->rak, array('class' => 'form-control required', 'placeholder'=>'Masukan Nama Rak')) !!}
    </div>
    <div class="form-group col-md-3">
        {!! Form::label('isi_tab', 'Isi /Box') !!}
        <div class="input-group"> 
            <span class="input-group-addon">@</span>
            {!! Form::text('isi_tab', $obat->isi_tab, array('class' => 'form-control required number', 'placeholder'=>'Masukan Isi tab /box')) !!}
        </div>
    </div>
    <div class="form-group col-md-3">
        {!! Form::label('isi_strip', 'Isi /Strip') !!}
        <div class="input-group"> 
            <span class="input-group-addon">@</span>
            {!! Form::text('isi_strip', $obat->isi_strip, array('class' => 'form-control required number', 'placeholder'=>'Masukan Isi strip /box')) !!}
        </div>
    </div>
    <div class="form-group col-md-3">
        {!! Form::label('untung_jual', 'Untung Jual') !!}
        <div class="input-group"> 
            {!! Form::text('untung_jual', $obat->untung_jual, array('class' => 'form-control required number', 'placeholder'=>'Masukan Untung Jual')) !!}
            <span class="input-group-addon">%</span>
        </div>
    </div>
    <div class="form-group col-md-3">
        {!! Form::label('untung_klinik', 'Untung Klinik') !!}
        <div class="input-group"> 
            {!! Form::text('untung_klinik', $obat->untung_klinik, array('class' => 'form-control required number', 'placeholder'=>'Masukan Untung Klinik')) !!}
            <span class="input-group-addon">%</span>
        </div>
    </div>
    <div class="form-group col-md-3">
        {!! Form::label('untung_dokter', 'Untung Dokter') !!}
        <div class="input-group"> 
            {!! Form::text('untung_dokter', $obat->untung_dokter, array('class' => 'form-control required number', 'placeholder'=>'Masukan Untung Dokter')) !!}
            <span class="input-group-addon">%</span>
        </div> 
    </div>
    <div class="form-group col-md-3">
        {!! Form::label('harga_beli', 'Harga Beli') !!}
        <div class="input-group"> 
            <span class="input-group-addon">Rp</span>
            {!! Form::text('harga_beli', $obat->harga_beli, array('class' => 'form-control required number', 'placeholder'=>'Masukan Harga Beli')) !!}
        </div>
    </div>
    <div class="form-group col-md-3">
        {!! Form::label('harga_jual', 'Harga Jual') !!}
        <div class="input-group"> 
            <span class="input-group-addon">Rp</span>
            {!! Form::text('harga_jual', $obat->harga_jual, array('id' => 'harga_jual', 'class' => 'form-control required number', 'placeholder'=>'Masukan Harga Jual')) !!}
        </div>
    </div>
</div>