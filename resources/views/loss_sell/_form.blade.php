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
    <div class="form-group col-md-12">
        <p class="text-info">Jika obat tidak ada di data, ketikan : Data obat tidak ada dalam list pada kolom pilih obat, maka akan muncul kolom untuk menginputkan nama obat.</p>
    </div>
</div>
<div class="row">
    <div class="form-group col-md-4">
        {!! Form::label('id_obat', 'Pilih Obat') !!}
        {!! Form::select('id_obat', $obats, $data_->id_obat, ['class' => 'form-control input_select required']) !!}
    </div>
    <div class="form-group col-md-2" id="nama_obat_view" style="display: none;">
        {!! Form::label('nama_obat', 'Nama Obat') !!}
        {!! Form::text('nama_obat', $data_->nama_obat, array('class' => 'form-control required', 'placeholder'=>'Nama Obat')) !!}
    </div>
     <div class="form-group col-md-2"  id="harga_obat_view" style="display: none;">
        {!! Form::label('harga', 'Harga Satuan') !!}
        {!! Form::text('harga', $data_->harga, array('class' => 'form-control required', 'placeholder'=>'Harga Satuan')) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('jumlah', 'Jumlah') !!}
        {!! Form::text('jumlah', $data_->jumlah, array('class' => 'form-control required', 'placeholder'=>'Jumlah')) !!}
    </div>
	<div class="form-group col-md-12">
	    {!! Form::label('keterangan', 'Keterangan') !!}
	    {!! Form::text('keterangan', $data_->keterangan, array('class' => 'form-control required', 'placeholder'=>'Keterangan')) !!}
	</div>
</div>