<!--
Model : Layout Show Tips
Author : Sri.
Date : 20/06/2021
-->

<!--
Model : Layout Show Tips
Author : Tangkas.
Date : 23/06/2021
-->

<form class="validated_form" id="form-edit" enctype="multipart/form-data">
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-md-8">
                            <table width="100%">
                                <tr>
                                    <td width="27%">Judul</td>
                                    <td width="2%"> : </td>
                                    <td width="70"><b>{{ $data_->title }}</b></td>
                                </tr>
                                <tr>
                                    <td width="27%">Konten</td>
                                    <td width="2%"> : </td>
                                    <td width="70">{!! $data_->content !!}</td>
                                </tr>
                                <tr>
                                    <td width="27%">Slug</td>
                                    <td width="2%"> : </td>
                                    <td width="70">{{ $data_->slug }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="form-group col-md-4">
                            @if (!empty($data_->image))
                                <img src="data:image/png;base64, {{ $data_->image}}" width="300" height="200"></a>
                            @else
                                 <img src="{{ asset('img/default.jpg') }}" width="300" height="200">
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-undo"></i>
                        Kembali</button>
                </div>
            </div>
        </div>
    </div>
</form>
