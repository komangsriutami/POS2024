<div class="row rowdetail" id="row-{{$count}}">
    <input type="hidden" name="iddetail[{{$count}}]"  value="<?php if($detailbiaya->id != ""){ echo Crypt::encrypt($detailbiaya->id); }?>">
    <div class="col-sm-4 form-group">
        {!! Form::select('id_kode_akun['.$count.']', $kode_akun, $detailbiaya->id_kode_akun, ['class' => 'form-control input_select required','id' => 'id_kode_akun-'.$count]) !!}
    </div>
    <div class="col-sm-3 form-group">
        <textarea class="form-control" placeholder="Deskripsi" type="text" name="deskripsi[{{$count}}]" required>{{$detailbiaya->deskripsi}}</textarea>
    </div>
    <div class="col-sm-3 form-group">
        <?php 
            if(!is_null($detailbiaya->id_akun_pajak)){
                $str = json_decode($detailbiaya->id_akun_pajak);
                // $str = implode(",",$str);
            } else {
                $str = array();
            }
            
            // dd($str);
            /*$str = str_replace('[', '', $str); 
            $str = str_replace(']', '', $str);
            $str = str_replace('"', '', $str);*/
        ?>
        
        <select name="id_akun_pajak[{{$count}}][]" id="id_akun_pajak-{{$count}}" class="form-control input_select id_akun_pajak" onchange="hitbiaya()" multiple>
            @if(!is_null($listpajak))
                @foreach($listpajak as $id => $p)

                    <?php $selected = ''; if(in_array($id,$str)) { $selected = "selected"; } ?>
                    <option value="{{$id}}" {{$selected}}>{{$p}}</option>
                @endforeach
            @endif
        </select>
    </div>
    <div class="col-sm-2 form-group">
        <div class="input-group">
            <input class="form-control biaya text-right" placeholder="Jumlah" type="number" name="biaya[{{$count}}]" id="biaya-{{$count}}" onchange="hitbiaya()"  value="{{$detailbiaya->biaya}}" data-idx="{{$count}}">
            <div class="input-group-append">
                <span class="mb-1 btn btn-danger" onclick="delDetail('{{$count}}')"><i class="fa fa-times"></i></span>
            </div>
        </div>
    </div>
</div>  