<div class="row" id="detail_data_pembayaran_{{$no}}">
    <div class="col-md-12 bg-info">
        <p class="mt-2 text-center">Pembayaran {{ $no }}</p>
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('tgl_bayar', 'Tanggal') !!}
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text">{{$no}}</span>
            </div>
            <input type="hidden" name="no" id="no" value="{{$no}}">
            {!! Form::hidden('pembayaran_konsinyasi['.$no.'][urutan]', null, array('class'=>'urutan')) !!}
            {!! Form::hidden('pembayaran_konsinyasi['.$no.'][id]', $pembayaran_konsinyasi->id) !!}
            {!! Form::text('pembayaran_konsinyasi['.$no.'][tgl_bayar]', $pembayaran_konsinyasi->tgl_bayar, array('id' => 'tgl_bayar_'.$no, 'class' => 'form-control tgl_bayar', 'placeholder'=>'Tgl Pembayaran', 'autocomplete' => 'off')) !!}
        </div>
    </div>
    <div class="form-group col-md-1">
        <?php
            $read = '';
            if($pembayaran_konsinyasi->jumlah_bayar != null && $pembayaran_konsinyasi->jumlah_bayar > 0) {
                $read= 'readonly';
            } 
        ?>
        {!! Form::label('jumlah_bayar', 'Jumlah') !!}
        {!! Form::text('pembayaran_konsinyasi['.$no.'][jumlah_bayar]', $pembayaran_konsinyasi->jumlah_bayar, array('id' => 'jumlah_bayar_'.$no,'class' => 'form-control ubah_data', 'placeholder'=>'Jumlah Pembayaran', 'onchange'=>'set_jumlah_bayar('.$no.')', 'autocomplete' => 'off', $read)) !!}
    </div>
    <div class="form-group col-md-2">
        <?php
            $hitung = $pembayaran_konsinyasi->jumlah_bayar*$detail_pembelian->harga_beli;
        ?>
        {!! Form::label('hitung_bayar', 'Hitung') !!}
        {!! Form::text('pembayaran_konsinyasi['.$no.'][hitung_bayar]', $hitung, array('id' => 'hitung_bayar_'.$no,'class' => 'form-control ubah_data', 'placeholder'=>'Hitung Bayar', 'readonly'=>'readonly')) !!}
    </div>
    <div class="form-group col-md-3">
        {!! Form::label('id_kartu_debet_credit', 'Kartu') !!}
        <select id="id_kartu_debet_credit_{{$no}}" name="pembayaran_konsinyasi[{{$no}}][id_kartu_debet_credit]" class="form-control input_select" onchange=set_kartu({{$no}})>
            <option value="" {!!( "" == $pembayaran_konsinyasi->id_kartu_debet_credit ? 'selected' : '')!!}>------Pilih Kartu-----</option>
            <option value="0" {!!( "0" == $pembayaran_konsinyasi->id_kartu_debet_credit ? 'selected' : '')!!}>Cash Only</option>
            <?php $x = 0; ?>
            @foreach( $kartu_debets as $kartu_debet )
                <?php $x = $x+1; ?>
                <option value="{{ $kartu_debet->id }}" {!!( $kartu_debet->id == $pembayaran_konsinyasi->id_kartu_debet_credit ? 'selected' : '')!!}>{{ $kartu_debet->nama }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('debet', 'Debet') !!}
        {!! Form::text('pembayaran_konsinyasi['.$no.'][debet]', $pembayaran_konsinyasi->debet, array('id' => 'debet_'.$no,'class' => 'form-control ubah_data', 'placeholder'=>'Debet', 'autocomplete' => 'off')) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('biaya_admin', 'Biaya Admin') !!}
        {!! Form::text('pembayaran_konsinyasi['.$no.'][biaya_admin]', $pembayaran_konsinyasi->biaya_admin, array('id' => 'biaya_admin_'.$no,'class' => 'form-control ubah_data', 'placeholder'=>'Biaya Admin', 'autocomplete' => 'off')) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('cash', 'Cash') !!}
        {!! Form::text('pembayaran_konsinyasi['.$no.'][cash]', $pembayaran_konsinyasi->cash, array('id' => 'cash_'.$no,'class' => 'form-control ubah_data', 'placeholder'=>'Cash', 'autocomplete' => 'off')) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('total_bayar', 'Total Bayar') !!}
        {!! Form::text('pembayaran_konsinyasi['.$no.'][total_bayar]', $pembayaran_konsinyasi->total_bayar, array('id' => 'total_bayar_'.$no,'class' => 'form-control ubah_data', 'placeholder'=>'Total Bayar', 'autocomplete' => 'off', 'readonly' => 'readonly')) !!}
    </div>
</div>
<style type="text/css">
    .pointer{
        cursor: pointer;
    }
</style>
<script type="text/javascript">
    var token = "";

    $(document).ready(function(){
        token = $('input[name="_token"]').val();
        $('.tgl_bayar').datepicker({
            autoclose:true,
            format:"yyyy-mm-dd",
            forceParse: false
        });

        $('.input_select').select2({});
    })
</script>
