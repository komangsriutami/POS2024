{!! Form::model($sub_kode_akuntansi, ['method' => 'PUT', 'class'=>'validated_form', 'id'=>'form-edit', 'route' => ['sub_kode_akuntansi.update', $sub_kode_akuntansi->id]]) !!}
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <div class="card-body">
                    @include('sub_kode_akuntansi/_form', ['submit_text' => 'Update', 'sub_kode_akuntansi'=>$sub_kode_akuntansi])
                </div>
                <div class="card-footer">
                    <button class="btn btn-success" type="button" onClick="submit_valid({{$sub_kode_akuntansi->id}})" data-toggle="tooltip" data-placement="top" title="Simpan"><i class="fa fa-save"></i> Simpan</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-undo"></i> Kembali</button>
                </div>
            </div>
         </div>
    </div>
{!! Form::close() !!}
@include('sub_kode_akuntansi/_form_js')

