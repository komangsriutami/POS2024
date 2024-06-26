<div class="row">
    <div class="col-sm-12">
        <div class="card card-info card-outline">
            <div class="card-body">
                <div class="row">
                    <input type="hidden" name="biaya_resep_awal" id="biaya_resep_awal" value="{{ $biaya_resep }}">
                    <input type="hidden" name="biaya_jasa_dokter_awal" id="biaya_jasa_dokter_awal" value="{{ $biaya_jasa_dokter }}">
                    <div class="form-group col-md-4">
                        {!! Form::label('id_dokter_p', 'Pilih Dokter') !!}
                        <select id="id_dokter_p" name="id_dokter_p" class="form-control required input_select">
                            <option value="">--Pilih Dokter--</option>
                            <?php $no = 0; ?>
                            @foreach( $dokters as $dokter )
                                <?php $no = $no+1; ?>
                                <option value="{{ $dokter->id }}" data-nama="{{ $dokter->nama }}" {!!( $dokter->id == $id_dokter ? 'selected' : '')!!}>{{ $dokter->nama }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="nama_dokter" id="nama_dokter" value="">
                    </div>
                    <div class="form-group col-md-3">
                        {!! Form::label('id_jasa_resep_p', 'Pilih Resep') !!}
                        <select id="id_jasa_resep_p" name="id_jasa_resep_p" class="form-control required input_select">
                            <option value="">--Pilih Jasa Resep--</option>
                            <?php $no = 0; ?>
                            @foreach( $jasa_reseps as $jasa_resep )
                                <?php $no = $no+1; ?>
                                <option value="{{ $jasa_resep->id }}" data-biaya="{{ $jasa_resep->biaya }}" {!!( $jasa_resep->id == $id_jasa_resep ? 'selected' : '')!!}>{{ $jasa_resep->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-md-2">
                        {!! Form::label('biaya_resep_p', 'Biaya Resep') !!}
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Rp</span>
                            </div>
                            {!! Form::text('biaya_resep_p', $biaya_resep, array('class' => 'form-control required', 'placeholder'=>'Biaya Resep', 'readonly' => 'readonly')) !!}
                        </div>
                    </div>
                    <div class="form-group col-md-3">
                        {!! Form::label('biaya_jasa_p', 'Biaya Jasa Dokter') !!}
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Rp</span>
                            </div>
                            {!! Form::hidden('harga_total_value', $harga_total, array('class' => 'form-control required', 'id' => 'harga_total_value')) !!}
                            {!! Form::text('biaya_jasa_p', $biaya_jasa_dokter, array('class' => 'form-control', 'placeholder'=>'Biaya Jasa Dokter', 'autocomplete' => 'off')) !!}
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="form-group col-md-12">
                            <button class="btn btn-success btn-sm" type="button" onClick="set_data(this)" data-toggle="tooltip" data-placement="top" title="Simpan"><i class="fa fa-save"></i> Simpan</button>
                            <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal"><i class="fa fa-undo"></i> Kembali</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
	$(document).ready(function(){
		$("#table_obat").DataTable();

        $('#id_jasa_resep_p').on('select2:select', function (e) {
            biaya = $(this).find(':selected').data('biaya');
            $("#biaya_resep_p").val(biaya);
            $("#biaya_jasa_p").focus();
        });

        $('#id_dokter_p').on('select2:select', function (e) {
            nama_dokter = $(this).find(':selected').data('nama');
            $("#nama_dokter").val(nama_dokter);
             $('#id_jasa_resep_p').select2('open');
        });

        $("#biaya_jasa_p").keypress(function(event){
            if (event.which == '10' || event.which == '13') {
                set_data(this);
                event.preventDefault();
            }
        });

        $('.input_select').select2();
	})

	function set_data(obj){
        var id = $("#id").val();
		var id_dokter = $("#id_dokter_p").val();
		var id_jasa_resep = $("#id_jasa_resep_p").val();
	    var biaya_jasa_dokter = parseFloat($("#biaya_jasa_p").val());
	    var biaya_resep = parseFloat($("#biaya_resep_p").val());
	    var harga_total_awal = $("#harga_total_value").val();
        var nama_dokter = $("#nama_dokter").val();
        var token = $("#token").val();

        data = {};
        $("#form_penjualan").find("input[name], select").each(function (index, node) {
            data[node.name] = node.value;
        });
        data["id_dokter"] = id_dokter;
        data["id_jasa_resep"] = id_jasa_resep;
        data["biaya_jasa_dokter"] = biaya_jasa_dokter;
        data["biaya_resep"] = biaya_resep;

        if(id) {
            $.ajax({
                type:"PUT",
                url : '{{url("penjualan/")}}/'+id,
                dataType : "json",
                data : data,
                beforeSend: function(data){
                    // replace dengan fungsi loading
                },
                success:  function(data){
                    if(data.status ==1){
                        kosongkan_form();
                        unHideDiskon();
                    }else{
                        show_error(data.message);
                        return false;
                    }
                },
                complete: function(data){
                    tb_nota_penjualan.fnDraw(false);
                },
                error: function(data) {
                    show_error("error ajax occured!");
                }

            });
        } else {
            $.ajax({
                type:"POST",
                url : '{{url("penjualan")}}',
                dataType : "json",
                data : data,
                beforeSend: function(data){
                    // replace dengan fungsi loading
                },
                success:  function(data){
                    if(data.status == 1){
                        kosongkan_form();
                        $("#id").val(data.id);
                        unHideDiskon();
                    }else{
                        show_error(data.message);
                        return false;
                    }
                },
                complete: function(data){
                    tb_nota_penjualan.fnDraw(false);
                },
                error: function(data) {
                    show_error("error ajax occured!");
                }

            });
        }

	    
	    /*$("#id_dokter").val(id_dokter);
	    $("#id_dokter_input").html("Dokter : "+nama_dokter);
        var biaya_jasa_dokter_rp = hitung_rp_khusus(biaya_jasa_dokter);
	    $("#biaya_jasa_dokter_input").html(biaya_jasa_dokter);
	    $("#biaya_jasa_dokter").val(biaya_jasa_dokter);
        
        if(id_jasa_resep != '') {
    	    $("#id_jasa_resep_input").html(id_jasa_resep);
    	    $("#id_jasa_resep").val(id_jasa_resep);
            var biaya_resep_rp = hitung_rp_khusus(biaya_resep);
    	    $("#biaya_resep_input").html(biaya_resep);
    	    $("#biaya_resep").val(biaya_resep);
        } else {
            biaya_resep = 0;
            $("#id_jasa_resep_input").html('-');
            $("#id_jasa_resep").val(0);
            $("#biaya_resep").val(0);
            $("#biaya_resep_input").html("0");
        }
        var biaya_jasa_dokter_awal = $("#biaya_jasa_dokter_awal").val();
        var biaya_resep_awal = $("#biaya_resep_awal").val();

        if(biaya_jasa_dokter_awal == "") {
            biaya_jasa_dokter_awal = 0;
        }

        if(biaya_resep_awal == "") {
            biaya_resep_awal = 0;
        }

        total_biaya_dokter_awal = parseFloat(biaya_jasa_dokter_awal) + parseFloat(biaya_resep_awal);
	    total_biaya_dokter = parseFloat(biaya_jasa_dokter) + parseFloat(biaya_resep);
        total_byr = parseFloat(harga_total_awal);
        if(total_biaya_dokter_awal != 0) {
	        total_byr = parseFloat(total_byr) - parseFloat(total_biaya_dokter_awal);
        } 
        total_byr = parseFloat(total_byr) + parseFloat(total_biaya_dokter); 
        var total_byr_rp = hitung_rp_khusus(total_byr);
        var total_biaya_dokter_rp = hitung_rp_khusus(total_biaya_dokter);
	    $("#total_biaya_dokter_input").html(total_biaya_dokter);
	    $("#total_biaya_dokter").val(total_biaya_dokter);
	    
        $("#total_pembayaran").html(total_byr);
        $("#total_pembayaran_input").val(total_byr);
        $("#total_pembayaran_display").html("Rp "+ total_byr_rp +", -");
        $("#count_total_belanja").val(total_byr);
	    $('#modal-xl').modal("hide");*/
	}
</script>