{!! Form::model($group_apotek, ['method' => 'PUT', 'class'=>'validated_form', 'id'=>'form-edit', 'route' => ['group_apotek.update', $group_apotek->id]]) !!}
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <div class="card-body">
                    @include('group_apotek/_form', ['submit_text' => 'Update', 'group_apotek'=>$group_apotek])
                </div>
                <div class="card-footer">
                    <button class="btn btn-success" type="button" onClick="submit_valid({{$group_apotek->id}})" data-toggle="tooltip" data-placement="top" title="Simpan"><i class="fa fa-save"></i> Simpan</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-undo"></i> Kembali</button>
                </div>
            </div>
         </div>
    </div>
{!! Form::close() !!}
@include('group_apotek/_form_js')
