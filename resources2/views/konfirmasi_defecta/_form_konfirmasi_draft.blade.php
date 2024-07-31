{!! Form::model($defecta, ['method' => 'PUT', 'class'=>'validated_form', 'id'=>'form-edit', 'route' => ['defecta.update', $data_->id]]) !!}
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <div class="card-body">
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
                        <div class="form-group col-md-4">
                            {!! Form::label('status', 'Status') !!}
                            {!! Form::text('status', $status, array('class' => 'form-control', 'placeholder'=>'Status', 'readonly' => 'readonly')) !!}
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-success" type="button" onClick="submit_valid()" data-toggle="tooltip" data-placement="top" title="Simpan"><i class="fa fa-save"></i> Simpan</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-undo"></i> Kembali</button>
                </div>
            </div>
         </div>
    </div>
{!! Form::close() !!}


