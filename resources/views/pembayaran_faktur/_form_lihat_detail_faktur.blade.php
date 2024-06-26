 {!! Form::model($pembelian, ['method' => 'PUT', 'class'=>'validated_form', 'route' => ['pembelian.update', $pembelian->id]]) !!}
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-md-4">
                            {!! Form::hidden('is_update_pembelian', 1, array('class' => 'form-control', 'id' =>'is_update_pembelian')) !!}
                            {!! Form::label('no_faktur', 'No Faktur') !!}
                            {!! Form::text('no_faktur', $pembelian->no_faktur, array('class' => 'form-control', 'placeholder'=>'No Faktur', 'readonly' => 'readonly')) !!}
                        </div>
                        <div class="form-group col-md-4">
                            {!! Form::label('tgl_faktur', 'Tanggal Faktur') !!}
                            {!! Form::text('tgl_faktur', $pembelian->tgl_faktur, array('class' => 'form-control', 'placeholder'=>'Tanggal Faktur', 'readonly' => 'readonly')) !!}
                        </div>
                        <div class="form-group col-md-4">
                            {!! Form::label('tgl_jatuh_tempo', 'Tanggal Jatuh Tempo') !!}
                            {!! Form::text('tgl_jatuh_tempo', $pembelian->tgl_jatuh_tempo, array('class' => 'form-control', 'placeholder'=>'Tanggal jatuh tempo', 'readonly' => 'readonly')) !!}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('apotek', 'Apotek') !!}
                            {!! Form::text('apotek', $pembelian->apotek->nama_panjang, array('class' => 'form-control', 'placeholder'=>'Nama Apotek', 'readonly' => 'readonly')) !!}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('suplier', 'Suplier') !!}
                            {!! Form::text('suplier', $pembelian->suplier->nama, array('class' => 'form-control', 'placeholder'=>'Nama Suplier', 'readonly' => 'readonly')) !!}
                        </div>
                        <?php
                            $total_diskon = $pembelian->detail_pembelian_total[0]->total_diskon + $pembelian->detail_pembelian_total[0]->total_diskon_persen + $pembelian->diskon1 + $pembelian->diskon2;
                            $total1 = $pembelian->detail_pembelian_total[0]->jumlah - $total_diskon;
                            $total2 = $total1 + ($total1 * $pembelian->ppn/100);
                        ?>
                        <div class="form-group col-md-3">
                            {!! Form::label('total_pembelian', 'Total Pembelian') !!}
                            {!! Form::text('total_pembelian', $pembelian->detail_pembelian_total[0]->jumlah, array('class' => 'form-control', 'placeholder'=>'Total Pembelian', 'readonly' => 'readonly')) !!}
                        </div>
                        <div class="form-group col-md-3">
                            {!! Form::label('total_diskon', 'Total Diskon') !!}
                            {!! Form::text('total_diskon', $total_diskon, array('class' => 'form-control', 'placeholder'=>'Total Diskon', 'readonly' => 'readonly')) !!}
                        </div>
                        <div class="form-group col-md-3">
                            {!! Form::label('ppn', 'PPN') !!}
                            {!! Form::text('ppn', $pembelian->ppn, array('class' => 'form-control', 'placeholder'=>'PPN', 'readonly' => 'readonly')) !!}
                        </div>
                        <div class="form-group col-md-3">
                            {!! Form::label('total', 'Total') !!}
                            {!! Form::text('total', $total2, array('class' => 'form-control', 'placeholder'=>'Total', 'readonly' => 'readonly')) !!}
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="form-group action-group">
                        <input type="hidden" name="is_lunas" id="is_lunas">
                        <?php
                            $is_lunas = $pembelian->is_lunas;
                        ?>
                        @if($is_lunas == 1)
                            <button class="btn btn-warning" type="button" onClick="submit_valid(0)" data-toggle="tooltip" data-placement="top" title="Batal Lunas"><i class="fa fa-save"></i> Batal Lunas</button>
                        @else($is_lunas == 0)
                            <button class="btn btn-success" type="button" onClick="submit_valid(1)" data-toggle="tooltip" data-placement="top" title="Lunas"><i class="fa fa-save"></i> Lunas</button>
                        @endif
                        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-undo"></i>Kembali</button>
                    </div>  
                </div>
            </div>
         </div>
    </div>
{!! Form::close() !!}
<script type="text/javascript">
    var token = "";


    $(document).ready(function(){
        token = $('input[name="_token"]').val();
    })

    function submit_valid(status){
        $("#is_lunas").val(status);
        $(".validated_form").submit();
    }
</script>

