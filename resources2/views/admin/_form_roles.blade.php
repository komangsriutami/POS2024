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
        <p class="text-red"><cite>Catatan : centang role yang diberikan dibawah ini.</cite></p>
    </div>
    <div class="form-group col-md-12">
        {!! Form::label('role', 'Pilih Role') !!}
         </div>
        <?php $a = 0; ?>
        @foreach($roles as $obj)
            <?php $a++; ?>
            @if($obj->ada_role ==1)
            <div class="form-group col-md-3">
                <label>
                    <input type="checkbox" class="flat-red" name="user_role[{{ $obj->id }}][id_role]" id="user_role[{{ $obj->id }}][id_role]" value ="{{$obj->id}}" checked="checked">
                    {{ $obj->nama }}
                </label>
            </div>
            @else
            <div class="form-group col-md-3">
                <label>
                    <input type="checkbox" class="flat-red" name="user_role[{{ $obj->id }}][id_role]" id="user_role[{{ $obj->id }}][id_role]" value ="{{$obj->id}}">
                    {{ $obj->nama }}
                </label>
            </div>
            @endif
        @endforeach
</div>
