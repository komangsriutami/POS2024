<div class="row" id="row_{{$no}}">
	<div class="col-xs-1 form-group">
		<p class="form-control-static numbering" style="text-align:right">1.</p>
	</div>
	<div class="col-xs-4 form-group">
		{!! Form::hidden('user_role['.$no.'][id]', $user_role->id) !!}
		<select name="user_role[{{ $no }}][id_role]" id="user_role[{{ $no }}][id_role]" class="form-control required role">
	        <option value="">-- Semua --</option> 
	        @foreach($roles as $obj)
	        <option value="{!! $obj->id !!}" data-no="{{ $no }}" {!!( $obj->id == $user_role->id_role ? 'selected' : '')!!}> {{ $obj->nama }}</option>   
	        @endforeach                  
	    </select>
	</div>
	<div class="col-xs-1 form-group">
		<button type="button" id="check_kunci_{{$no}}" onclick="delete_row({{$no}})" data-no="{{$no}}" class="btn btn-danger btn-sm check_kunci"><i class="fa fa-times" aria-hidden="true"></i></button>
	</div>
</div>