<!--
Model : Layout Show Dokter
Author : Tangkas.
Date : 12/06/2021
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
                                    <td width="27%">Group Apotek</td>
                                    <td width="2%"> : </td>
                                    <td width="70"><b>{{ $data_->group_apoteks->nama_singkat }}</b></td>
                                </tr>
                                <tr>
                                    <td width="27%">Apotek</td>
                                    <td width="2%"> : </td>
                                    <td width="70"><b>{{ $data_->apoteks->nama_singkat }}</b></td>
                                </tr>
                                <tr>
                                    <td width="27%">Nama Dokter</td>
                                    <td width="2%"> : </td>
                                    <td width="70"><b>{{ $data_->nama }}</b></td>
                                </tr>
                                <tr>
                                    <td width="27%">Spesialis</td>
                                    <td width="2%"> : </td>
                                    <td width="70"><b>{{ $data_->spesialiss->spesialis_dokter }}</b></td>
                                </tr>
                                <tr>
                                    <td width="27%">SIP</td>
                                    <td width="2%"> : </td>
                                    <td width="70">{{ $data_->sib }}</td>
                                </tr>
                                <tr>
                                    <td width="27%">Telepon</td>
                                    <td width="2%"> : </td>
                                    <td width="70">{{ $data_->telepon }}</td>
                                </tr>
                                <tr>
                                    <td width="27%">Alamat</td>
                                    <td width="2%"> : </td>
                                    <td width="70">{{ $data_->alamat }}</td>
                                </tr>
                                <tr>
                                    <td width="27%">Fee</td>
                                    <td width="2%"> : </td>
                                    <td width="70">{{ $data_->fee }}%</td>
                                </tr>
                            </table>
                        </div>
                        <div class="form-group col-md-4">
                            {{-- <img class="img-fluid mb-3" src="{{ asset('userfiles/dokter') }}/{{ $data_->img }}"
                                alt="Photo" style="height: 200px;"> --}}

                            @if (!empty($data_->img))
                                <img class="img-fluid mb-3" src="{{ asset('userfiles/dokter') }}/{{ $data_->img }}"
                                    alt="Photo" style="height: 200px;">
                            @else
                                <p>Mohon maaf, foto tidak ditemukan!</p>
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
