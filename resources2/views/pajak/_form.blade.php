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
	    {!! Form::label('nama', 'Nama pajak (*)') !!}
	    {!! Form::text('nama', $pajak->nama, array('class' => 'form-control required', 'placeholder'=>'Masukan Nama')) !!}
	</div>
    <div class="form-group col-md-2">
        {!! Form::label('persentase_efektif', 'Persentase Efektif (*)') !!}
        {!! Form::text('persentase_efektif', $pajak->persentase_efektif, array('class' => 'form-control required', 'placeholder'=>'Masukan Persentase Efektif')) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('is_pemotongan', 'Pemotongan') !!}
        </br>
        <label>
            <input type="checkbox" class="flat-red" name="is_pemotongan" id="is_pemotongan" value ="{{$pajak->is_pemotongan}}" {!!( $pajak->is_pemotongan == 1 ? 'checked' : '')!!}>
        </label>
    </div>
    <div class="form-group col-md-4">
        <label for="id_akun_pajak_satu_lbl" id="id_akun_pajak_satu_lbl">Akun Pajak Penjualan (*)</label>
        {!! Form::select('id_akun_pajak_penjualan', $akuns, $pajak->id_akun_pajak_penjualan, ['id' => 'id_akun_pajak_satu', 'class' => 'form-control required input_select']) !!}
    </div>
    <div class="form-group col-md-4">
        <label for="id_akun_pajak_dua_lbl" id="id_akun_pajak_dua_lbl">Akun Pajak Pembelian (*)</label>
        {!! Form::select('id_akun_pajak_pembelian', $akuns, $pajak->id_akun_pajak_pembelian, ['id' => 'id_akun_pajak_dua', 'class' => 'form-control required input_select']) !!}
    </div>
</div>