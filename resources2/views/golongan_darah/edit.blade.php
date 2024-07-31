{!! Form::model($golongan_darah, ['method' => 'PUT', 'class'=>'validated_form', 'id'=>'form-edit', 'route' => ['golongan_darah.update', $golongan_darah->id]]) !!}
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <div class="card-body">
                    @include('golongan_darah/_form', ['submit_text' => 'Update', 'golongan_darah'=>$golongan_darah])
                </div>
                <div class="card-footer">
                    <button class="btn btn-success" type="button" onClick="submit_valid({{$golongan_darah->id}})" data-toggle="tooltip" data-placement="top" title="Simpan"><i class="fa fa-save"></i> Simpan</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-undo"></i> Kembali</button>
                </div>
            </div>
         </div>
    </div>
{!! Form::close() !!}
@include('golongan_darah/_form_js')

