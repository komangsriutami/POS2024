<!--
Model : Layout Backend Show Message Footer pada Frontend
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
                                    <td width="27%">Full Name</td>
                                    <td width="2%"> : </td>
                                    <td width="70"><b>{{ $data_->name }}</b></td>
                                </tr>
                                <tr>
                                    <td width="27%">Email</td>
                                    <td width="2%"> : </td>
                                    <td width="70"><b>{{ $data_->email }}</b></td>
                                </tr>
                                <tr>
                                    <td width="27%">Phone Number</td>
                                    <td width="2%"> : </td>
                                    <td width="70"><b>{{ $data_->phone_number }}</b></td>
                                </tr>
                                <tr>
                                    <td width="27%">Additional Message</td>
                                    <td width="2%"> : </td>
                                    <td width="70"><b>{{ $data_->additional_message }}</b></td>
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
