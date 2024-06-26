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
    <input type="hidden" name="id_obat" id="id_obat" value="{{ $obat->id }}">
    <div class="form-group col-md-12">
        {!! Form::label('id_suplier', 'Suplier') !!}
        {!! Form::select('id_suplier', $supliers, $data_->id_suplier, ['class' => 'form-control required input_select']) !!}
    </div>
     <div class="form-group col-md-12">
        {!! Form::label('urutan', 'Prioriti') !!}
        <select id="level" name="level" class="form-control input_select required">
            <option value="1" {!!( "1" == $data_->level ? 'selected' : '')!!}>Prioritas 1</option>
            <option value="2" {!!( "2" == $data_->level ? 'selected' : '')!!}>Prioritas 2</option>
            <option value="3" {!!( "3" == $data_->level ? 'selected' : '')!!}>Prioritas 3</option>
            <option value="4" {!!( "4" == $data_->level ? 'selected' : '')!!}>Prioritas 4</option>
            <option value="5" {!!( "5" == $data_->level ? 'selected' : '')!!}>Prioritas 5</option>
            <option value="6" {!!( "6" == $data_->level ? 'selected' : '')!!}>Prioritas 6</option>
            <option value="7" {!!( "7" == $data_->level ? 'selected' : '')!!}>Prioritas 7</option>
            <option value="8" {!!( "8" == $data_->level ? 'selected' : '')!!}>Prioritas 8</option>
            <option value="9" {!!( "9" == $data_->level ? 'selected' : '')!!}>Prioritas 9</option>
            <option value="10" {!!( "10" == $data_->level ? 'selected' : '')!!}>Prioritas 10</option>
        </select>
    </div>
</div>
