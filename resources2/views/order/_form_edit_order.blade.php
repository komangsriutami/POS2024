{!! Form::model($detail, ['method' => 'PUT', 'class'=>'validated_form', 'id'=>'form-edit-detail-order', 'route' => ['order.update_order_detail', $detail->id]]) !!}
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
                    <input type="hidden" name="id_detail" id="id_detail" value="{{ $detail->id }}">
                    <input type="hidden" name="no" id="no" value="{{ $no }}">
                    <div class="row">
                        <div class="form-group col-md-4">
                            {!! Form::label('apotek', 'Apotek') !!}
                            {!! Form::text('apotek', $apotek->nama_panjang, array('class' => 'form-control', 'placeholder'=>'Nama Apotek', 'readonly' => 'readonly')) !!}
                        </div>
                        <div class="form-group col-md-8">
                            {!! Form::label('obat', 'Nama Obat') !!}
                            {!! Form::text('obat', $detail->nama, array('class' => 'form-control', 'placeholder'=>'Nama Apotek', 'readonly' => 'readonly')) !!}
                        </div>
                        <div class="form-group col-md-2">
                            {!! Form::label('jumlah', 'Jumlah  Diorder(*)') !!}
                            {!! Form::text('jumlah', $detail->jumlah, array('id' => 'jumlah','class' => 'form-control required', 'placeholder'=>'Jumlah Diajukan')) !!}
                        </div>
                        <div class="form-group col-md-12">
                            {!! Form::label('keterangan', 'Keterangan') !!}
                            {!! Form::text('keterangan', $detail->keterangan, array('id' => 'keterangan','class' => 'form-control', 'placeholder'=>'Komentar atau catatan')) !!}
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-success" type="button" onClick="set_detail_new(this, {{$no}})" data-toggle="tooltip" data-placement="top" title="Simpan"><i class="fa fa-save"></i> Simpan</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-undo"></i> Kembali</button>
                </div>
            </div>
         </div>
    </div>
<script type="text/javascript">
    $(document).ready(function(){
    })

    function set_detail_new(obj, no){
        jumlah = $("#jumlah").val();
        keterangan = $("#keterangan").val();
        id_detail = $('#id_detail').val();
        $("#jumlah_"+no).val(jumlah);
        $("#keterangan_"+no).val(keterangan);
        $("#jumlah_"+no).html(jumlah);
        $("#keterangan_"+no).html(keterangan);

        submit_detail(id_detail);
    }
</script>
{!! Form::close() !!}


