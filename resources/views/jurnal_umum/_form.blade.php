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

    .rowdetail {
        border-right : solid #d8d8d8 1px;
        border-left : solid #d8d8d8 1px;
        border-bottom : solid #d8d8d8 1px;
        padding-top : 4px;
    }

    .rowdetail > div.form-group {
        padding-bottom : 4px;
        margin-bottom : unset!important;
    }

    .total {
        font-size: 18px;
    }
</style>
<div class="row">
    <div class="form-group col-md-2">
        {!! Form::label('no_transaksi', 'No. Transaksi') !!}
        {!! Form::text('no_transaksi', $jurnal_umum->no_transaksi, array('class' => 'form-control required', 'placeholder'=>'Masukan No. Transaksi')) !!}
    </div>
    <div class="form-group col-md-2">
        {!! Form::label('tgl_transaksi', 'Tgl. Transaksi') !!}
        {!! Form::text('tgl_transaksi', $jurnal_umum->tgl_transaksi, array('id' => 'tgl_transaksi','class' => 'form-control required', 'placeholder'=>'Masukan Tanggal Transaksi', 'autocomplete' => 'off')) !!}
    </div>

    <?php /*
    <div class="form-group col-md-3">
        {!! Form::label('id_jenis_transaksi', 'Jenis Transaksi') !!}
        <select class="form-control input_select" name="id_jenis_transaksi" required>
            @if(!empty($jenistransaksi))
                @foreach($jenistransaksi as $j)
                    <option value="{{$j->id}}">{{$j->nama}}</option>
                @endforeach
            @endif
        </select>
    </div>
    */?>
    <div class="form-group col-md-4"></div>
    <div class="form-group col-md-4">
        {!! Form::label('tag', 'Tag') !!}
        {!! Form::text('tag', $jurnal_umum->tag, array('id' => 'tag','class' => 'form-control', 'placeholder'=>'Gunakan koma (,)', 'autocomplete' => 'false')) !!}
    </div>

    <?php /*
    <div class="form-group col-md-3">
        {!! Form::label('kode_referensi', 'Kode Referensi / Kontak') !!}
        {!! Form::text('kode_referensi', $jurnal_umum->kode_referensi, array('id' => 'kode_referensi','class' => 'form-control', 'placeholder'=>'', 'autocomplete' => 'false')) !!}
    </div>
    */ ?>
</div>

<div class="row"><div class="col-sm-12"><label>Detail Jurnal</label></div></div>
<input type="hidden" id="count" value="{{$jurnal_umum->detailjurnal->count()}}">
<div class="row bg-gray" style="yellow;padding-top: 7px;">    
    <div class="form-group col-md-4">Akun</div>
    <div class="form-group col-md-4">Deskripsi</div>
    <div class="form-group col-md-2">Debit(+)</div>
    <div class="form-group col-md-2">Kredit(-)</div>
</div>
<div id="div_detail">
    @if($jurnal_umum->detailjurnal->count())
        @foreach($jurnal_umum->detailjurnal as $key => $d)
            @include('jurnal_umum/_form_detail', ['count' => $key,'detailjurnal'=>$d,"kode_akun"=>$kode_akun])
        @endforeach
    @endif
</div>
<div class="row"><div style="margin-top: 9px;" class="btn btn-sm btn-default btnAddDetail" data-toggle="tooltip" data-placement="top" title="Tambah input detail"><i class="fa fa-plus"></i> Tambah Detail</div></div>

<div class="row mt-4">
    <div class="col-sm-4">
        <div class="form-group">
            <label>Memo</label>
            <textarea class="form-control" name="memo">{{$jurnal_umum->memo}}</textarea>  
        </div>        
    </div> 
    <div class="col-sm-4"></div>   
    <div class="col-sm-2 total text-right">
        <label>Total Debit</label><br>
        <table width="100%" border="0"><tr><td width="25%" align="right">Rp.</td><td align="right" id="tdebit" width="75%">0</td></tr></table>
    </div>   
    <div class="col-sm-2 total text-right">
        <label class="text-right">Total Kredit</label><br>
        <table width="100%" border="0"><tr><td width="25%" align="right">Rp.</td><td align="right" id="tkredit" width="75%">0</td></tr></table>
    </div>    
</div>
<hr>

<div class="row"><div class="col-sm-12"><label><i class="fa fa-paperclip"></i> &nbsp;Lampiran Bukti</label></div></div>
<input type="hidden" id="countfile" value="{{$jurnal_umum->filebuktijurnal->count()}}">
<div id="div_bukti">
    @if($jurnal_umum->filebuktijurnal->count())
        @foreach($jurnal_umum->filebuktijurnal as $key => $f)
            @include('jurnal_umum/_form_file', ['count' => $key,'filebukti'=>$f])
        @endforeach
    @endif
</div>
<div class="row"><div style="margin-top: 9px;" class="btn btn-sm btn-default btnAddFile" data-toggle="tooltip" data-placement="top" title="Tambah input file"><i class="fa fa-plus"></i> Tambah File Lampiran</div></div>