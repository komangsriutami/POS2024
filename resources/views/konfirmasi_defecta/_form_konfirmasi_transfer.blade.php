<form id="form-konfirm-transfer" class="validated_form">
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <div class="card-body">
                    @if (count( $errors) > 0 )
                        <div class="alert alert-danger">
                            @foreach ($errors->all() as $error)
                                {{ $error }}<br>        
                            @endforeach
                        </div>
                    @endif
                    <style type="text/css">
                        .select2 {
                          width: 100%!important; /* overrides computed width, 100px in your demo */
                        }
                    </style>
                    <div class="row">
                        <input type="hidden" name="_token" id="_token" value="{{csrf_token()}}">
                        <input type="hidden" name="id_status" id="id_status" value="{{ $status->id }}">
                        <div class="form-group col-md-4">
                            {!! Form::label('status', 'Status') !!}
                            {!! Form::text('status', $status->nama, array('class' => 'form-control', 'placeholder'=>'Status', 'readonly' => 'readonly')) !!}
                        </div>
                        <div class="form-group col-md-4">
                            {!! Form::label('id_apotek_transfer', 'Apotek') !!}
                            {!! Form::select('id_apotek_transfer', $apoteks, null, ['id'=>'id_apotek_transfer', 'class' => 'form-control input_select']) !!}
                        </div>
                        <div class="form-group col-md-12">
                            {!! Form::label('table', 'List Data') !!}
                            <table  id="tb_data" class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th width="5%">No.</th>
                                        <th width="8%">Apotek</th>
                                        <th width="50%">Nama Obat</th>
                                        <th width="10%">Stok</th>
                                        <th width="10%">Buffer</th>
                                        <th width="10%">Forcasting</th>
                                        <th width="10%">Pengajuan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 0; ?>
                                    @foreach($defectas as $obj)
                                        <?php $no++; ?>
                                        <tr>
                                            {!! Form::hidden('defecta['.$no.'][id]', $obj->id, array('id' => 'id_'.$no, 'class' => 'form-control', 'placeholder'=>'ID', 'readonly' => 'readonly')) !!}
                                            <td width="5%">{{ $no }}</td>
                                            <td width="8%">{{ $obj->nama_singkat }}</td>
                                            <td width="50%">{{ $obj->nama }}</td>
                                            <td width="10%">{{ $obj->total_stok }}</td>
                                            <td width="10%">{{ $obj->total_buffer }}</td>
                                            <td width="10%">{{ $obj->forcasting }}</td>
                                            <td width="10%">{{ $obj->jumlah }}</td>
                                        </tr>
                                        <?php 
                                            if($obj->komentar == '') {
                                                $obj->komentar = '-';
                                            }
                                        ?>
                                        <tr>
                                            <td colspan="7"> Catatan {{ $no }} : {{ $obj->komentar }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-success" type="button" onClick="submit_transfer()" data-toggle="tooltip" data-placement="top" title="Simpan"><i class="fa fa-save"></i> Simpan</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-undo"></i> Kembali</button>
                </div>
            </div>
         </div>
    </div>
<script type="text/javascript">
    $(document).ready(function(){
        $('.input_select').select2();  
    })
</script>
</form>


