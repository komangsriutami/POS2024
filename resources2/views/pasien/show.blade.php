<!--
Model : Layout Show Pasien
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
                                    <td width="27%">Pernah Berobat</td>
                                    <td width="2%"> : </td>
                                    <td width="70"><b>{{ $data_->is_pernah_berobat }}</b></td>
                                </tr>
                                <tr>
                                    <td width="27%">NIK</td>
                                    <td width="2%"> : </td>
                                    <td width="70"><b>{{ $data_->nik }}</b></td>
                                </tr>
                                <tr>
                                    <td width="27%">Nama Pasien</td>
                                    <td width="2%"> : </td>
                                    <td width="70"><b>{{ $data_->nama }}</b></td>
                                </tr>
                                <tr>
                                    <td width="27%">Memiliki BPJS</td>
                                    <td width="2%"> : </td>
                                    <td width="70"><b>{{ $data_->is_bpjs }}</b></td>
                                </tr>
                                <tr>
                                    <td width="27%">Nomor BPJS</td>
                                    <td width="2%"> : </td>
                                    <td width="70"><b>{{ $data_->no_bpjs }}</b></td>
                                </tr>
                                <tr>
                                    <td width="27%">Tempat Lahir</td>
                                    <td width="2%"> : </td>
                                    <td width="70">{{ $data_->tempat_lahir }}</td>
                                </tr>
                                <tr>
                                    <td width="27%">Tanggal Lahir</td>
                                    <td width="2%"> : </td>
                                    <td width="70">{{ $data_->tgl_lahir }}</td>
                                </tr>
                                <tr>
                                    <td width="27%">Jenis Kelamin</td>
                                    <td width="2%"> : </td>
                                    <td width="70">{{ $data_->jeniskelamins->jenis_kelamin }}</td>
                                </tr>
                                <tr>
                                    <td width="27%">Alamat</td>
                                    <td width="2%"> : </td>
                                    <td width="70">{{ $data_->alamat }}</td>
                                </tr>
                                <tr>
                                    <td width="27%">Pekerjaan</td>
                                    <td width="2%"> : </td>
                                    <td width="70">{{ $data_->pekerjaan }}</td>
                                </tr>
                                <tr>
                                    <td width="27%">Kewarganegaraan</td>
                                    <td width="2%"> : </td>
                                    <td width="70">{{ $data_->kewarganegaraans->kewarganegaraan }}</td>
                                </tr>
                                {{-- <tr>
                                    <td width="27%">Agama</td>
                                    <td width="2%"> : </td>
                                    <td width="70">{{ $data_->agamas->agama }}</td>
                                </tr> --}}
                                <tr>
                                    <td width="27%">Golongan Darah</td>
                                    <td width="2%"> : </td>
                                    <td width="70">{{ $data_->golongandarahs->golongan_darah }}</td>
                                </tr>
                                <tr>
                                    <td width="27%">Telepon</td>
                                    <td width="2%"> : </td>
                                    <td width="70">{{ $data_->telepon }}</td>
                                </tr>
                                <tr>
                                    <td width="27%">Email</td>
                                    <td width="2%"> : </td>
                                    <td width="70">{{ $data_->email }}%</td>
                                </tr>
                                <tr>
                                    <td width="27%">Alergi</td>
                                    <td width="2%"> : </td>
                                    <td width="70">{{ $data_->alergi_detail }}%</td>
                                </tr>
                            </table>
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
