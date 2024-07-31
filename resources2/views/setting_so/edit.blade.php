{!! Form::model($setting_so, ['method' => 'PUT', 'class'=>'validated_form', 'id'=>'form-edit', 'route' => ['setting_so.update', $setting_so->id]]) !!}
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <div class="card-body">
                    @include('setting_so/_form', ['submit_text' => 'Update', 'setting_so'=>$setting_so])
                </div>
                <div class="card-footer">
                    <button class="btn btn-success" type="button" onClick="submit_valid({{$setting_so->id}})" data-toggle="tooltip" data-placement="top" title="Simpan"><i class="fa fa-save"></i> Simpan</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-undo"></i> Kembali</button>
                </div>
            </div>
         </div>
    </div>
{!! Form::close() !!}
@include('setting_so/_form_js')

