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
	<div class="form-group col-md-1">
	    {!! Form::label('kode', 'Kode (*)') !!}
	    {!! Form::text('kode', $kode_akuntansi->kode, array('class' => 'form-control required', 'placeholder'=>'Masukan Kode')) !!}
	</div>
	<div class="form-group col-md-6">
	    {!! Form::label('nama', 'Nama (*)') !!}
	    {!! Form::text('nama', $kode_akuntansi->nama, array('class' => 'form-control required', 'placeholder'=>'Masukan Nama')) !!}
	</div>
	<div class="form-group col-md-5">
	    {!! Form::label('id_kategori_akun', 'Kategori Akun (*)') !!}
	    <select class="form-control required input_select" name="id_kategori_akun">
	    	<option value="">- Pilih Kategori Akun -</option>
	    	@if(!empty($kategori))
	    		@foreach($kategori as $k)
	    			<?php if($k->id == $kode_akuntansi->id_kategori_akun){
	    				$selected = "selected"; 
	    			} else {
	    				$selected = "";
	    			}?>

	    			<option value="{{$k->id}}" {{$selected}}>{{$k->nama}}</option>
	    		@endforeach	    	
	    	@endif	    	
	    </select>
	</div>
</div>