{!! Form::model($penyesuaian_stok, ['method' => 'PUT', 'class'=>'validated_form', 'id'=>'form-edit', 'route' => ['penyesuaian_stok.update', $penyesuaian_stok->id]]) !!}
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <div class="card-body">
                    @include('penyesuaian_stok/_form', ['submit_text' => 'Update', 'penyesuaian_stok'=>$penyesuaian_stok])
                </div>
                <div class="card-footer">
                    <button class="btn btn-success" type="button" onClick="submit_valid({{$penyesuaian_stok->id}})" data-toggle="tooltip" data-placement="top" title="Simpan"><i class="fa fa-save"></i> Simpan</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-undo"></i> Kembali</button>
                </div>
            </div>
         </div>
    </div>
{!! Form::close() !!}
@include('penyesuaian_stok/_form_js')


