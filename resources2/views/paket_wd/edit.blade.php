{!! Form::model($paket_wd, ['method' => 'PUT', 'class'=>'validated_form', 'id'=>'form-edit', 'route' => ['paket_wd.update', $paket_wd->id]]) !!}
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <div class="card-body">
                    @include('paket_wd/_form', ['submit_text' => 'Update', 'paket_wd'=>$paket_wd])
                </div>
                <div class="card-footer">
                    <button class="btn btn-success" type="button" onClick="submit_valid({{$paket_wd->id}})" data-toggle="tooltip" data-placement="top" title="Simpan"><i class="fa fa-save"></i> Simpan</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-undo"></i> Kembali</button>
                </div>
            </div>
         </div>
    </div>
{!! Form::close() !!}
@include('paket_wd/_form_js')

