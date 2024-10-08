{!! Form::model($data_, ['method' => 'PUT', 'class'=>'validated_form', 'id'=>'form-edit', 'route' => ['setting_suplier.updatedetail', $data_->id]]) !!}
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <div class="card-body">
                    @include('setting_suplier._form_detail', ['submit_text' => 'Update', 'data_'=>$data_])
                </div>
                <div class="card-footer">
                    <button class="btn btn-info btn-sm" type="button" onClick="submit_valid_detail(2, {{$data_->id}})" data-toggle="tooltip" data-placement="top" title="Simpan"><i class="fa fa-save"></i> Simpan</button>
                    <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal"><i class="fa fa-undo"></i> Kembali</button>
                </div>
            </div>
         </div>
    </div>
{!! Form::close() !!}
@include('setting_suplier._form_js')
