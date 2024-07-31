<div class="row rowbukti" id="rowbukti-{{$count}}">   
    <input type="hidden" name="idbukti[{{$count}}]" value="<?php if($filebukti->id != ""){ echo Crypt::encrypt($filebukti->id); }?>">
    
        @if($filebukti->id != "")
            <div class="col-sm-2 form-group">
                <div class="btn-group">
                    <div class="mb-1 btn btn-danger" onclick="delBukti('{{$count}}')"><i class="fa fa-times"></i></div>
                    <div class="mb-1 btn btn-outline-primary" onclick="openfile('{{Crypt::encrypt($filebukti->id)}}','{{$filebukti->type_file}}')"><i class="fa fa-search"></i> Lihat file bukti</div>
                </div>
            </div>
        @else
            <div class="col-sm-4 form-group">
                <div class="input-group">
                    <div class="input-group-append">
                        <span class="mb-1 btn btn-danger" onclick="delBukti('{{$count}}')"><i class="fa fa-times"></i></span>
                    </div>
                    <input data-idx="{{$count}}" class="form-control buktifile text-right" placeholder="buktifile" type="file" name="buktifile[{{$count}}]" required>
                </div>
            </div>
        @endif   
    <div class="col-sm-8 form-group">
        <input type="text" class="form-control" placeholder="Keterangan" type="text" name="keterangan[{{$count}}]" value="{{$filebukti->keterangan}}" required>
    </div>    
</div>  