<!--
Model : Layout Edit Tindakan
Author : Tangkas.
Date : 12/06/2021
-->

<form class="validated_form" id="form-edit">
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="card-body">
                    @include('tindakan._form', ['submit_text' => 'Update',
                    'data_'=>$data_])
                </div>
                <div class="card-footer">
                    <button class="btn btn-success" type="button" onClick="submit_valid({{ $data_->id }})"
                        data-toggle="tooltip" data-placement="top" title="Simpan"><i class="fa fa-save"></i>
                        Simpan</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-undo"></i>
                        Kembali</button>
                </div>
            </div>
        </div>
    </div>
</form>
@include('tindakan._form_js')
