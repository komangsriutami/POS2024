@extends('layout.app')

@section('title')
Skema Gaji
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Data Master</a></li>
    <li class="breadcrumb-item"><a href="#">Skema Gaji</a></li>
    <li class="breadcrumb-item active" aria-current="page">Setting Data</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card card-info card-outline">
            <div class="card-body">
                <div class="row">
					<div class="form-group col-md-6">
					    
				    </div>
				</div>

				<div class="row">
				    <div class="col-5 col-sm-3">
				        <div class="nav flex-column nav-tabs h-100" id="vert-tabs-tab" role="tablist" aria-orientation="vertical">
				            @foreach($posisis as $obj)
				            	<?php
				        			if($obj->id == 1) {
				        				$class = 'active';
				        				$aria = 'true';
				        			} else {
				        				$class = '';
				        				$aria = 'false';
				        			}
				        		?>
				            	<a class="nav-link {{ $class }}" id="vert-tabs-{{ $obj->id }}-tab" data-toggle="pill" href="#vert-tabs-{{ $obj->id }}" role="tab" aria-controls="vert-tabs-{{ $obj->id }}" aria-selected="{{ $aria }}">{{ $obj->nama }}</a>
					    	@endforeach
				        </div>
				    </div>
				    <div class="col-7 col-sm-9">
				        <div class="tab-content" id="vert-tabs-tabContent">
				        	@foreach($posisis as $obj)
				        		<?php
				        			if($obj->id == 1) {
				        				$css = 'active show';
				        			} else {
				        				$css = '';
				        			}
				        		?>
					            <div class="tab-pane text-left fade {{ $css }}" id="vert-tabs-{{ $obj->id }}" role="tabpanel" aria-labelledby="vert-tabs-{{ $obj->id }}-tab">
					            	{!! Form::model($skema_gaji, ['method' => 'PUT', 'class'=>'validated_form', 'id'=>'form-edit', 'route' => ['skema_gaji.add_update_detail', $skema_gaji->id]]) !!}
					            		<?php $no = 0; ?>
					            		@foreach($data_[$obj->id] as $xx)
					            			<?php 
	                                            $no++; 
	                                        ?>
					            			<div class="form-group col-md-12">
						            			<div class="card card-default">
												    <div class="card-header">
												        <h3 class="card-title">
												            <i class="fas fa-exclamation-triangle"></i>
												            {{ $xx->nama_jabatan }} - {{ $xx->nama_status }}
												        </h3>
												    </div>
												    <!-- /.card-header -->
												    <div class="card-body">
												    	<div class="row">
													        <input type="hidden" name="detail[{{ $no }}][id_skema_gaji]" id="id_skema_gaji_{{ $no }}" value="{{ $xx->id_skema_gaji }}">
											            	<input type="hidden" name="detail[{{ $no }}][id_posisi]" id="id_posisi_{{ $no }}" value="{{ $xx->id_posisi }}">
											            	<input type="hidden" name="detail[{{ $no }}][id_jabatan]" id="id_jabatan_{{ $no }}" value="{{ $xx->id_jabatan }}">
											            	<input type="hidden" name="detail[{{ $no }}][id_status_karyawan]" id="id_status_karyawan_{{ $no }}" value="{{ $xx->id_status_karyawan }}">
														    <div class="form-group col-md-3">
															    {!! Form::label('gaji_pokok', 'Gaji Pokok') !!}
															    {!! Form::text('detail['.$no.'][gaji_pokok]', $xx->gaji_pokok, array('id' => 'gaji_pokok_'.$no, 'class' => 'form-control', 'placeholder'=>'Gaji Pokok')) !!}
															</div>
															<div class="form-group col-md-3">
															    {!! Form::label('persen_omset', 'Omset (%)') !!}
															    {!! Form::text('detail['.$no.'][persen_omset]', $xx->persen_omset, array('id' => 'tunjangan_jabatan_'.$no,'class' => 'form-control', 'placeholder'=>'Persen Omset')) !!}
															</div>
															<div class="form-group col-md-3">
															    {!! Form::label('tunjangan_jabatan', 'Tunjangan Jabatan') !!}
															    {!! Form::text('detail['.$no.'][tunjangan_jabatan]', $xx->tunjangan_jabatan, array('id' => 'tunjangan_jabatan_'.$no,'class' => 'form-control', 'placeholder'=>'Tunjangan Jabatan')) !!}
															</div>
															<div class="form-group col-md-3">
															    {!! Form::label('tunjangan_ijin', 'Tunjangan Ijin (Rp)') !!}
															    {!! Form::text('detail['.$no.'][tunjangan_ijin]', $xx->tunjangan_ijin, array('id' => 'tunjangan_ijin_'.$no,'class' => 'form-control', 'placeholder'=>'Tunjangan Ijin')) !!}
															</div>
															<div class="form-group col-md-3">
															    {!! Form::label('tunjangan_makan', 'Tunjangan Makan (Rp)') !!}
															    {!! Form::text('detail['.$no.'][tunjangan_makan]', $xx->tunjangan_makan, array('id' => 'tunjangan_makan_'.$no,'class' => 'form-control', 'placeholder'=>'Tunjangan Makan')) !!}
															</div>
															<div class="form-group col-md-3">
															    {!! Form::label('tunjangan_transportasi', 'Tunjangan Transportasi (Rp)') !!}
															    {!! Form::text('detail['.$no.'][tunjangan_transportasi]', $xx->tunjangan_transportasi, array('id' => 'tunjangan_transportasi_'.$no,'class' => 'form-control', 'placeholder'=>'Tunjangan Transportasi')) !!}
															</div>
															<div class="form-group col-md-3">
															    {!! Form::label('lembur', 'Lembur (Rp)') !!}
															    {!! Form::text('detail['.$no.'][lembur]', $xx->lembur, array('id' => 'lembur_'.$no,'class' => 'form-control', 'placeholder'=>'Tunjangan Transportasi')) !!}
															</div>
															<div class="form-group col-md-3">
															    {!! Form::label('pph', 'PPH (%)') !!}
															    {!! Form::text('detail['.$no.'][pph]', $xx->pph, array('id' => 'pph_'.$no,'class' => 'form-control', 'placeholder'=>'PPH')) !!}
															</div>
															<div class="form-group col-md-3">
															    {!! Form::label('potongan_keterlambatan', 'Potongan (%)') !!}
															    {!! Form::text('detail['.$no.'][potongan_keterlambatan]', $xx->potongan_keterlambatan, array('id' => 'potongan_keterlambatan_'.$no,'class' => 'form-control', 'placeholder'=>'Potongan')) !!}
															</div>
														</div>
												    </div>
												    <!-- /.card-body -->
												</div>
											</div>
										@endforeach
										<div class="border-top">
						                    <div class="card-body text-right">
						                        <button class="btn btn-primary btn-sm" type="submit" data-toggle="tooltip" data-placement="top" title="Simpan data"><i class="fa fa-save"></i> Simpan</button> 
						                        <a href="{{ url('/skema_gaji') }}" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Kembali ke daftar data"><i class="fa fa-undo"></i> Kembali</a>
						                    </div>
						                </div>
									{!! Form::close() !!}
					            </div>
				            @endforeach
				        </div>
				    </div>
				</div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
    @include('skema_gaji/_form_js')
@endsection

