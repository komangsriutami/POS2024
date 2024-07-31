{!! Form::model($investasi_modal, ['method' => 'PUT', 'class'=>'validated_form', 'id'=>'form-edit', 'route' => ['investasi_modal.update', $investasi_modal->id]]) !!}
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <div class="card-body">
                    @include('investasi_modal/_form', ['submit_text' => 'Update', 'investasi_modal'=>$investasi_modal])
                </div>
                <div class="card-footer">
                    <button class="btn btn-success" type="button" onClick="submit_valid({{$investasi_modal->id}})" data-toggle="tooltip" data-placement="top" title="Simpan"><i class="fa fa-save"></i> Simpan</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-undo"></i> Kembali</button>
                </div>
            </div>
         </div>
    </div>
{!! Form::close() !!}
@include('investasi_modal/_form_js')
