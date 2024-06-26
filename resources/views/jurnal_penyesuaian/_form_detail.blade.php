<div class="row rowdetail" id="row-{{$count}}">
    <input type="hidden" name="iddetail[{{$count}}]"  value="<?php if($detailjurnal->id != ""){ echo Crypt::encrypt($detailjurnal->id); }?>">
    <div class="col-sm-4 form-group">
        {!! Form::select('id_kode_akun['.$count.']', $kode_akun, $detailjurnal->id_kode_akun, ['class' => 'form-control input_select required','id' => 'id_kode_akun-'.$count]) !!}
    </div>
    <div class="col-sm-4 form-group"><textarea class="form-control" placeholder="Deskripsi" type="text" name="deskripsi[{{$count}}]" required>{{$detailjurnal->deskripsi}}</textarea></div>
    <div class="col-sm-2 form-group"><input class="form-control debit text-right" placeholder="debit" type="number" name="debit[{{$count}}]" onchange="hitdebit()" value="{{$detailjurnal->debit}}"></div>
    <div class="col-sm-2 form-group">
        <div class="input-group">
            <input class="form-control kredit text-right" placeholder="kredit" type="number" name="kredit[{{$count}}]" onchange="hitkredit()"  value="{{$detailjurnal->kredit}}">
            <div class="input-group-append">
                <span class="mb-1 btn btn-danger" onclick="delDetail('{{$count}}')"><i class="fa fa-times"></i></span>
            </div>
        </div>
    </div>
</div>  