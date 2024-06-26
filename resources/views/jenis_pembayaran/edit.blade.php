{!! Form::model($jenis_pembayaran, ['method' => 'PUT', 'class'=>'validated_form', 'id'=>'form-edit', 'route' => ['jenis_pembayaran.update', $jenis_pembayaran->id]]) !!}
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <div class="card-body">
                    @include('jenis_pembayaran/_form', ['submit_text' => 'Update', 'jenis_pembayaran'=>$jenis_pembayaran])
                </div>
                <div class="card-footer">
                    <button class="btn btn-success" type="button" onClick="submit_valid({{$jenis_pembayaran->id}})" data-toggle="tooltip" data-placement="top" title="Simpan"><i class="fa fa-save"></i> Simpan</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-undo"></i> Kembali</button>
                </div>
            </div>
         </div>
    </div>
{!! Form::close() !!}
@include('jenis_pembayaran/_form_js')

