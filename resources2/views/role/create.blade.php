@extends('layout.app')

@section('title')
Role
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Rbac</a></li>
    <li class="breadcrumb-item"><a href="#">Role</a></li>
    <li class="breadcrumb-item active" aria-current="page">Create Data</li>
</ol>
@endsection

@section('content')
	<style type="text/css">
		hr {
		  	display: block;
		  	margin: 0px;
		  	padding: 0px;
		  	border-width: 2px;
		  	color: #17a2b8;
		}
	</style>
	{!! Form::model(new App\RbacRole, ['route' => ['role.store'], 'class'=>'validated_form']) !!}
		<div class="row">
	        <div class="col-sm-12">
	            <div class="card card-info card-outline">
	                <div class="card-body">
	                    @include('role/_form', ['submit_text' => 'Create'])
	                    
	                    <div class="form-group col-sm-12">
						   	<h4 style="color: #0097A7;text-align: center;">LIST PERMISSION</h4>
		                    <hr>
		                </div>

						<?php 
							$a = 0; 
							$nama_singkatan = 'nama_singkatan';
						?>
					    @foreach($menu as $obj)
					        <?php $a++; ?>
					        
					        <div class="form-group col-sm-12">
					        	<p class="btn btn-block btn-info">
                                  	<span class="fa fa-fw fa-thumbtack"></span> {{ $obj->$nama_singkatan }}
                                </p>

					        	@if($obj->link !='#')
					        		<?php $c = 0; ?>
					        		<div class="row">
						        		@foreach($obj->permission as $key2)
							        		<?php $c++; ?>
							        		@if($key2->ada_permission ==1)
							       			<div class="form-group col-sm-4">
		                                        <label>
		                                            <input type="checkbox" class="flat-red" name="permission_role[{{ $key2->id }}][id_permission]" id="permission_role[{{ $key2->id }}][id_permission]" value ="{{$key2->id}}" checked="checked">
		                                            {{ $key2->nama }}
		                                        </label>
		                                    </div>
		                                    @else
		                                    <div class="form-group col-sm-4">
		                                        <label>
		                                            <input type="checkbox" class="flat-red" name="permission_role[{{ $key2->id }}][id_permission]" id="permission_role[{{ $key2->id }}][id_permission]" value ="{{$key2->id}}">
		                                            {{ $key2->nama }}
		                                        </label>
		                                    </div>
									        @endif
							       		@endforeach
							       	</div>
	                            @endif

						        <?php $b = 0; ?>
						    	@foreach($obj->sub_menu as $val)
						       		<?php $b++; ?>
						       		<div class="form-group col-sm-12">
						        		<h4><i class="fa fa-hand-point-right"></i> {{ $val->$nama_singkatan }}</h4>
						        	</div>

						        	<?php $c = 0; ?>
						        	<div class="row">
							        	@foreach($val->permission as $key)
							        		<?php $c++; ?>
							        		@if($key->ada_permission ==1)
							       			<div class="form-group col-sm-4">
		                                        <label>
		                                            <input type="checkbox" class="flat-red" name="permission_role[{{ $key->id }}][id_permission]" id="permission_role[{{ $key->id }}][id_permission]" value ="{{$key->id}}" checked="checked">
		                                            {{ $key->nama }}
		                                        </label>
		                                    </div>
		                                    @else
		                                    <div class="form-group col-sm-4">
		                                        <label>
		                                            <input type="checkbox" class="flat-red" name="permission_role[{{ $key->id }}][id_permission]" id="permission_role[{{ $key->id }}][id_permission]" value ="{{$key->id}}">
		                                            {{ $key->nama }}
		                                        </label>
		                                    </div>
									        @endif
							       		@endforeach
							       	</div>
						       		
						       		<div class="form-group col-sm-12">
						       			<hr>
						       		</div>
						        @endforeach
					        </div>
					    @endforeach

					    <div class="form-group col-sm-12">
					        <p class="btn btn-block btn-social btn-github">
                              	<span class="fa fa-fw fa-thumbtack"></span> Permission Tanpa Relasi Menu
                            </p>
					        <?php $c = 0; ?>
					        <div class="row">
					        	@foreach($permission_tanpa_menu as $key)
					        		<?php $c++; ?>
					        		@if($key->ada_permission ==1)
					       			<div class="form-group col-sm-4">
		                                <label>
		                                    <input type="checkbox" class="flat-red" name="permission_role[{{ $key->id }}][id_permission]" id="permission_role[{{ $key->id }}][id_permission]" value ="{{$key->id}}" checked="checked">
		                                    {{ $key->nama }}
		                                </label>
		                            </div>
		                            @else
		                            <div class="form-group col-sm-4">
		                                <label>
		                                    <input type="checkbox" class="flat-red" name="permission_role[{{ $key->id }}][id_permission]" id="permission_role[{{ $key->id }}][id_permission]" value ="{{$key->id}}">
		                                    {{ $key->nama }}
		                                </label>
		                            </div>
							        @endif
					       		@endforeach
					       	</div>
					    </div>
	                </div>
	                <div class="border-top">
	                    <div class="card-body">
	                        <button class="btn btn-primary" type="submit" data-toggle="tooltip" data-placement="top" title="Simpan data"><i class="fa fa-save"></i> Simpan</button> 
	                        <a href="{{ url('/role') }}" class="btn btn-danger" data-toggle="tooltip" data-placement="top" title="Kembali ke daftar data"><i class="fa fa-undo"></i> Kembali</a>
	                    </div>
	                </div>
	            </div>
	        </div>
	    </div>
	{!! Form::close() !!}
@endsection

@section('script')
	@include('role/_form_js')
@endsection