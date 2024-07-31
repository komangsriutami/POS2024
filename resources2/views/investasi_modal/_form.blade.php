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
    <div class="form-group col-md-2">
        {!! Form::label('tgl_transaksi', 'Tanggal') !!}
        {!! Form::text('tgl_transaksi', $investasi_modal->tgl_transaksi, array('class' => 'datepicker form-control required', 'placeholder'=>'Masukan Tanggal Transaksi', 'id'=>'tgl_transaksi', 'autocomplete'=>'off')) !!}
    </div>
    <div class="form-group col-md-5">
        {!! Form::label('id_apotek', 'Apotek') !!}
        {!! Form::select('id_apotek', $apotek, $investasi_modal->id_apotek, ['class' => 'form-control required input_select']) !!}
    </div>
    <div class="form-group col-md-5">
        {!! Form::label('id_investor', 'Investor') !!}
        {!! Form::select('id_investor', $investor, $investasi_modal->id_investor, ['class' => 'form-control required input_select']) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('saham', 'Saham') !!}
        <div class="input-group"> 
            <div class="input-group-prepend">
                <span class="input-group-text">@</span>
            </div>
            {!! Form::text('saham', $investasi_modal->saham, array('class' => 'form-control required number', 'placeholder'=>'Masukan Saham')) !!}
        </div>
    </div>
    <div class="form-group col-md-3">
        {!! Form::label('jumlah_modal', 'Jumlah Modal') !!}
        <div class="input-group"> 
            <div class="input-group-prepend">
                <span class="input-group-text">Rp</span>
            </div>
            {!! Form::text('jumlah_modal', $investasi_modal->jumlah_modal, array('class' => 'form-control required number', 'placeholder'=>'Masukan Jumlah Modal')) !!}
        </div>
    </div>
   
</div>
