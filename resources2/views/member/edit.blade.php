{!! Form::model($member, ['method' => 'PUT', 'class'=>'validated_form', 'id'=>'form-edit', 'route' => ['member.update', $member->id]]) !!}
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body card-info card-outline">
                    @include('member/_form', ['submit_text' => 'Update', 'member'=>$member])
                </div>
                <div class="card-footer">
                    <button class="btn btn-success" type="button" onClick="submit_valid({{$member->id}})" data-toggle="tooltip" data-placement="top" title="Simpan"><i class="fa fa-save"></i> Simpan</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-undo"></i> Kembali</button>
                </div>
            </div>
         </div>
    </div>
{!! Form::close() !!}
@include('member/_form_js')
