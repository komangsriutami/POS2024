{!! Form::model($jenis_kelamin, ['method' => 'PUT', 'class'=>'validated_form', 'id'=>'form-edit', 'route' => ['jenis_kelamin.update', $jenis_kelamin->id]]) !!}
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <div class="card-body">
                    @include('jenis_kelamin/_form', ['submit_text' => 'Update', 'jenis_kelamin'=>$jenis_kelamin])
                </div>
                <div class="card-footer">
                    <button class="btn btn-success" type="button" onClick="submit_valid({{$jenis_kelamin->id}})" data-toggle="tooltip" data-placement="top" title="Simpan"><i class="fa fa-save"></i> Simpan</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-undo"></i> Kembali</button>
                </div>
            </div>
         </div>
    </div>
{!! Form::close() !!}
@include('jenis_kelamin/_form_js')

