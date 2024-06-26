{!! Form::model(new App\MasterMember, ['route' => ['penjualan.store_member'], 'class'=>'validated_form', 'id' => 'form-add-member']) !!}
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-body card-info card-outline">
                @include('penjualan.member_form', ['submit_text' => 'Update', 'data_'=>$data_])
            </div>
            <div class="card-footer">
                <button class="btn btn-info btn-sm" type="button" onClick="submit_valid_member()" data-toggle="tooltip" data-placement="top" title="Simpan"><i class="fa fa-save"></i> Simpan</button>
                <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal"><i class="fa fa-undo"></i> Kembali</button>
            </div>
        </div>
     </div>
</div>
{!! Form::close() !!}
@include('member._form_js')

