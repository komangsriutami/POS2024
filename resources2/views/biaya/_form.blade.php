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

<div class="card-header">
    <div class="row">
        <div class="form-group col-md-3">
            {!! Form::label('id_akun_bayar', 'Bayar Dari') !!}
            {!! Form::select('id_akun_bayar', $akundompet, $biaya->id_akun_bayar, ['class' => 'form-control input_select required','id' => 'id_akun_bayar']) !!}
        </div>
        <div class="form-group col-md-2">
            <br>
            <?php if($biaya->is_bayar_nanti){$checked = "checked";} else {$checked = "";} ?>
            <label for="is_bayar_nanti"><input value="1" type="checkbox" name="is_bayar_nanti" id="is_bayar_nanti" {{$checked}}>&nbsp;Bayar Nanti</label>
        </div>
        <div class="form-group col-md-2 bayar_nanti" style="display:none;">
            <?php if($biaya->tgl_batas_pembayaran == "") {
                $tgl_batas_pembayaran = Date('Y-m-d');
            } else {
                $tgl_batas_pembayaran = $biaya->tgl_batas_pembayaran;
            } ?>
            

            {!! Form::label('tgl_batas_pembayaran', 'Tgl. Jatuh Tempo') !!}
            {!! Form::text('tgl_batas_pembayaran', $tgl_batas_pembayaran, array('id' => 'tgl_batas_pembayaran','class' => 'form-control required', 'placeholder'=>'Masukan Tanggal Transaksi', 'autocomplete' => 'off')) !!}
        </div>
        <div class="form-group col-md-3 bayar_nanti"  style="display:none;">
            {!! Form::label('id_syarat_pembayaran', 'Syarat Pembayaran') !!}
            <select class="form-control input_select" name="id_syarat_pembayaran" id="id_syarat_pembayaran">
                <option value="">- Pilih Syarat -</option>
                @if(!empty($syarat_pembayaran))
                    @foreach ($syarat_pembayaran as $key => $value)
                        <option data-waktu="{{$value->jangka_waktu}}" value="{{$value->id}}">{{$value->nama}}</option>
                    @endforeach
                @endif
            </select>
            <div style="margin-top: 9px;" class="btn btn-sm btn-default btnAddSyarat" data-toggle="tooltip" data-placement="top" title="Tambah syarat Pembayaran" ><i class="fa fa-plus"></i> Tambah Syarat Pembayaran</div>
        </div>

    </div>
</div>
<div class="card-body">
    <div class="row">


        <div class="form-group col-md-2">
            {!! Form::label('no_biaya', 'No. Transaksi') !!}
            @if($biaya->id == "")
                <br><i class="text-muted"><i class="fa fa-info-circle"></i>&nbsp;Nomor Otomatis</i>   
            @else
                <br>#{{$biaya->no_biaya}}
            @endif

            <?php /* {!! Form::text('no_biaya', $biaya->no_biaya, array('class' => 'form-control required', 'placeholder'=>'Masukan No. Transaksi')) !!} */ ?>
        </div>

        <div class="form-group col-md-3">
            {!! Form::label('id_penerima', 'Penerima') !!}
            <select class="form-control input_select required" id="id_penerima" name="id_penerima">
                <option value="">-- Pilih Penerima --</option>
                @foreach($supplier as $obj)
                    <option value="{{ $obj['id'] }}" data-tipe_penerima="{{ $obj['type'] }}" {!!( $obj['id'] == $biaya->id_penerima && $obj['type'] == $biaya->tipe_penerima ? 'selected' : '')!!}>{{ $obj['nama'] }}</option>
                @endforeach
            </select>
            {!! Form::hidden('tipe_penerima', $biaya->tipe_penerima, array('id' => 'tipe_penerima')) !!}
        </div>

        <div class="form-group col-md-2">
            {!! Form::label('tgl_transaksi', 'Tgl. Transaksi') !!}
            {!! Form::text('tgl_transaksi', $biaya->tgl_transaksi, array('id' => 'tgl_transaksi','class' => 'form-control required', 'placeholder'=>'Masukan Tanggal Transaksi', 'autocomplete' => 'off')) !!}
        </div>

        <div class="form-group col-md-2">
            {!! Form::label('id_cara_pembayaran', 'Cara Pembayaran') !!}
            {!! Form::select('id_cara_pembayaran', $carabayar, $biaya->id_cara_pembayaran, ['class' => 'form-control input_select','id' => 'id_cara_pembayaran']) !!}
        </div>

        <div class="form-group col-md-3">
            {!! Form::label('tag', 'Tag') !!}
            {!! Form::text('tag', $biaya->tag, array('id' => 'tag','class' => 'form-control', 'placeholder'=>'Gunakan koma (,)', 'autocomplete' => 'off')) !!}
        </div>
    </div>
    <div class="row" style="margin-bottom: 20px;">
        <div class="form-group col-md-12">
            {!! Form::label('alamat_penagihan', 'Alamat Penagihan') !!}
            {!! Form::text('alamat_penagihan', $biaya->alamat_penagihan, array('id' => 'alamat_penagihan','class' => 'form-control', 'autocomplete' => 'off')) !!}
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6"><label>Detail Biaya</label></div>
        <div class="col-sm-6">
            <!-- <div class="custom-control custom-switch float-right">
                <input type="checkbox" class="custom-control-input" value="1" id="is_termasuk_pajak" name="is_termasuk_pajak">
                <label class="custom-control-label" for="is_termasuk_pajak">Harga Termasuk PPN</label>
            </div> -->
        </div>
    </div>





    <input type="hidden" id="count" value="<?php if(is_null($biaya->detailbiaya)){ echo 0; } else { echo $biaya->detailbiaya->count(); }?>">
    <div class="row bg-gray" style="yellow;padding-top: 7px;">    
        <div class="form-group col-md-4">Akun Biaya</div>
        <div class="form-group col-md-3">Deskripsi</div>
        <div class="form-group col-md-3">Pajak</div>
        <div class="form-group col-md-2">Jumlah</div>
    </div>
    <div id="div_detail">
        @if(!is_null($biaya->detailbiaya))
            @foreach($biaya->detailbiaya as $key => $d)
                @include('biaya/_form_detail', ['count' => $key,'detailbiaya'=>$d,"kode_akun"=>$kode_akun])
            @endforeach
        @endif
    </div>
    <div class="row"><div style="margin-top: 9px;" class="btn btn-sm btn-default btnAddDetail" data-toggle="tooltip" data-placement="top" title="Tambah input detail"><i class="fa fa-plus"></i> Tambah Detail</div></div>





    <div class="row mt-4">
        <div class="col-sm-4">
            <div class="form-group">
                <label>Memo</label>
                <textarea class="form-control" name="memo">{{$biaya->memo}}</textarea>  
            </div> 
        </div>
        <div class="col-sm-8">
            <div class="row"> 
                <div class="col-sm-4"></div>  
                <div class="col-sm-4 text-right"><label>Sub Total</label></div>  
                <div class="col-sm-4 total text-right">
                    <table width="100%" border="0"><tr><td width="25%" align="right">Rp.</td><td align="right" id="total" width="75%">0</td></tr></table>
                </div> 
            </div>
            <div class="row" id="div_pajak" style="font-size: 14px;"></div>
            <div class="row text-red">
                <div class="col-sm-4 text-right"><br>
                    {!! Form::select('id_akun_ppn_potong', $akundompet, $biaya->id_akun_ppn_potong, ['class' => 'form-control','id' => 'id_akun_ppn_potong']) !!}
                </div>
                <div class="col-sm-4">
                    <small>Pemotongan</small><br>
                    <div class="input-group input-group-sm">
                        <span class="input-group-append">
                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                <label class="btn btn-secondary btn-sm"><input type="radio" value="1" name="options_pajak" id="persen" autocomplete="off">%</label>
                                <label class="btn btn-secondary btn-sm active"><input type="radio" value="2" name="options_pajak" id="nominal" autocomplete="off" checked="">Rp.</label>
                            </div>
                        </span>
                        <input type="text" name="potongan_pajak" id="potongan_pajak" value="{{$biaya->ppn_potong}}" class="form-control text-right">
                    </div>
                </div> 
                <div class="col-sm-4 text-right"><br>
                    <table width="100%" border="0"><tr><td width="25%" align="right">(Rp.</td><td align="right" id="jumlahpajak" width="75%">0)</td></tr></table>
                </div>
            </div>

            <div class="row" style="font-size: 20px;">
                <div class="col-sm-4"></div>
                <div class="col-sm-4 text-right"><label>Total</label></div>  
                <div class="col-sm-4 totalakhir text-right">
                    <table width="100%" border="0"><tr><td width="25%" align="right">Rp.</td><td align="right" id="totalakhir" width="75%">0</td></tr></table>
                </div>
            </div>

        </div>   
    </div>

    <hr>

    <div class="row"><div class="col-sm-12"><label><i class="fa fa-paperclip"></i> &nbsp;Lampiran Bukti</label></div></div>
    <input type="hidden" id="countfile" value="<?php if(is_null($biaya->filebuktibiaya)){ echo 0; } else { echo $biaya->filebuktibiaya->count(); }?>">
    <div id="div_bukti">
        @if(!is_null($biaya->filebuktibiaya))
            @foreach($biaya->filebuktibiaya as $key => $f)
                @include('biaya/_form_file', ['count' => $key,'filebukti'=>$f])
            @endforeach
        @endif
    </div>
    <div class="row"><div style="margin-top: 9px;" class="btn btn-sm btn-default btnAddFile" data-toggle="tooltip" data-placement="top" title="Tambah input file"><i class="fa fa-plus"></i> Tambah File Lampiran</div></div>
</div>