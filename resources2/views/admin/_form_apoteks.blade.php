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
        {!! Form::label('nama', 'Pilih User') !!}
        @if($user->id > 0)
            {!! Form::hidden('id') !!}
            {!! Form::text('nama', $user->identifier.' - '.$user->nama, ['class'=>'form-control', 'readonly'=>'readonly']) !!}
        @else
            {!! Form::select('id', array(), null, ['id'=>'id', 'class' => 'form-control required']) !!}
        @endif
    </div>
    <div class="form-group col-md-12">
        <p class="text-red"><cite>Catatan : centang apotek yang diberikan dibawah ini.</cite></p>
    </div>
    <div class="form-group col-md-12">
        {!! Form::label('apotek', 'Pilih Apotek') !!}
         </div>
        <?php $a = 0; ?>
        @foreach($apoteks as $obj)
            <?php $a++; ?>
            @if($obj->ada_apotek ==1)
            <div class="form-group col-md-3">
                <label>
                    <input type="checkbox" class="flat-red" name="user_apotek[{{ $obj->id }}][id_apotek]" id="user_apotek[{{ $obj->id }}][id_apotek]" value ="{{$obj->id}}" checked="checked">
                    ({{ $obj->kode_apotek }}) {{ $obj->nama_panjang }}
                </label>
            </div>
            @else
            <div class="form-group col-md-3">
                <label>
                    <input type="checkbox" class="flat-red" name="user_apotek[{{ $obj->id }}][id_apotek]" id="user_apotek[{{ $obj->id }}][id_apotek]" value ="{{$obj->id}}">
                    ({{ $obj->kode_apotek }}) {{ $obj->nama_panjang }}
                </label>
            </div>
            @endif
        @endforeach
</div>
