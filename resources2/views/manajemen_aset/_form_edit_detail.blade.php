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
                        <div class="form-group col-md-8">
                            {!! Form::label('aset', 'Nama Aset') !!}
                            {!! Form::text('aset', $detail->aset->nama, array('class' => 'form-control', 'placeholder'=>'Nama Aset', 'readonly' => 'readonly')) !!}
                        </div>
                        <div class="form-group col-md-2">
                            {!! Form::label('id_dasar_harga_', 'Pilih Dasar Harga') !!}
                            <select id="id_dasar_harga_" name="id_dasar_harga_" class="form-control input_select">
                                <option value="" {!!( "" == $detail->id_kondisi_aset ? 'selected' : '')!!}>-- Pilih --</option>
                                <option value="1" {!!( "1" == $detail->id_dasar_harga ? 'selected' : '')!!}>Perolehan</option>
                                <option value="2" {!!( "2" == $detail->id_dasar_harga ? 'selected' : '')!!}>Taksiran</option>
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            {!! Form::label('id_kondisi_aset_', 'Pilih Kondisi Aset') !!}
                            <select id="id_kondisi_aset_" name="id_kondisi_aset_" class="form-control input_select">
                                <option value="" {!!( "" == $detail->id_kondisi_aset ? 'selected' : '')!!}>-- Pilih --</option>
                                <option value="1" {!!( "1" == $detail->id_kondisi_aset ? 'selected' : '')!!}>Baik</option>
                                <option value="2" {!!( "2" == $detail->id_kondisi_aset ? 'selected' : '')!!}>Rusak Ringan</option>
                                <option value="3" {!!( "3" == $detail->id_kondisi_aset ? 'selected' : '')!!}>Rusak Berat</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            {!! Form::label('merek_', 'Merk') !!}
                             <div class="input-group">
                                {!! Form::text('merek_', $detail->merek, array('id'=>'merek_', 'class' => 'form-control', 'placeholder'=>'Merk', 'autocomplete' => 'off')) !!}
                            </div>
                        </div>
                        <div class="form-group col-md-2">
                            {!! Form::label('jumlah_', 'Jumlah') !!}
                             <div class="input-group">
                                <div class="input-group-append">
                                    <span class="btn btn-secondary mb-4">@</span>
                                </div>
                                {!! Form::text('jumlah_', $detail->jumlah, array('id'=>'jumlah_', 'class' => 'form-control', 'placeholder'=>'Jumlah', 'autocomplete' => 'off')) !!}
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            {!! Form::label('nilai_satuan_', 'Nilai Satuan') !!}
                             <div class="input-group">
                                 <div class="input-group-append">
                                    <span class="btn btn-secondary mb-4">Rp</span>
                                </div>
                                {!! Form::text('nilai_satuan_', $detail->nilai_satuan, array('id'=>'nilai_satuan_', 'class' => 'form-control', 'placeholder'=>'Nilai Satuan', 'autocomplete' => 'off')) !!}
                            </div>
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
        var id_dasar_harga = $("#id_dasar_harga_").val();
        var id_kondisi_aset = $("#id_kondisi_aset_").val();
        var merek = $("#merek_").val();
        var nilai_satuan = $("#nilai_satuan_").val();
        var jumlah = $("#jumlah_").val();
        var total_nilai = parseFloat(jumlah) * parseFloat(nilai_satuan);
        var total_nilai_rp = hitung_rp_khusus(total_nilai);
        var nilai_satuan_rp = hitung_rp_khusus(nilai_satuan);
        var dasar_harga = '<span class="text-info">[Belum ditentukan]</span>';
        if(id_dasar_harga == 1) {
            dasar_harga = '<span class="text-secondary">[Perolehan]</span>';
        } else if(id_dasar_harga == 2) {
            dasar_harga = '<span class="text-secondary">[Taksiran]</span>';
        }
        var kondisi_aset = '<span class="text-info">[Belum ditentukan]</span>';
        if(id_kondisi_aset == 1) {
            kondisi_aset = '<span class="text-success">[Baik]</span>';
        } else if(id_kondisi_aset == 2) {
            kondisi_aset = '<span class="text-warning">[Rusak Ringan]</span>';
        } else if(id_kondisi_aset == 3) {
            kondisi_aset = '<span class="text-danger">[Rusak Berat]</span>';
        }
        $("#id_dasar_harga_"+no).val(id_dasar_harga);
        $("#id_kondisi_aset_"+no).val(id_kondisi_aset);
        $("#merek_"+no).val(merek);
        $("#nilai_satuan_"+no).val(nilai_satuan);
        $("#jumlah_"+no).val(jumlah);
        $("#total_nilai_"+no).val(total_nilai);
        $("#id_dasar_harga_html_"+no).html(dasar_harga);
        $("#id_kondisi_aset_html_"+no).html(kondisi_aset);
        $("#merek_html_"+no).html(merek);
        $("#nilai_satuan_html_"+no).html('Rp '+nilai_satuan_rp);
        $("#jumlah_html_"+no).html(jumlah);
        $("#total_nilai_html_"+no).html('Rp '+total_nilai_rp);
        $("#hitung_total_"+no).data("total", total_nilai);
        $("#hitung_total_"+no).val(total_nilai);
        $("#hitung_total_"+no).html(total_nilai);
        
        hitung_total();
        $('#modal-xl').modal('toggle');
    }
</script>