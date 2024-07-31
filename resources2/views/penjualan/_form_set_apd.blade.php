<div class="row">
    <div class="col-sm-12">
        <div class="card card-info card-outline">
            <div class="card-body">
                <div class="row">
                    <input type="hidden" name="biaya_apd_awal" id="biaya_apd_awal" value="{{ $biaya_apd }}">
                    <div class="form-group col-md-3">
                        {!! Form::label('biaya_apd_p', 'Biaya APD  (*)') !!}
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Rp</span>
                            </div>
                            {!! Form::hidden('harga_total_value', $harga_total, array('class' => 'form-control required', 'id' => 'harga_total_value')) !!}
                            {!! Form::text('biaya_apd_p', $biaya_apd, array('id' => 'biaya_apd_p', 'class' => 'form-control', 'placeholder'=>'Biaya APD', 'autocomplete' => 'off')) !!}
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

        $('.input_select').select2();

        $("#biaya_apd_p").keypress(function(event){
            if (event.which == '10' || event.which == '13') {
                set_data(this);
            }
        });
	})

	function set_data(obj){
        var id = $("#id").val();
	    var biaya_apd = parseFloat($("#biaya_apd_p").val());
        harga_total_awal = $("#harga_total_value").val();

        data = {};
        $("#form_penjualan").find("input[name], select").each(function (index, node) {
            data[node.name] = node.value;
        });
        data["biaya_apd"] = biaya_apd;

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


        /*var biaya_apd_awal = $("#biaya_apd_awal").val();
        if(biaya_apd_awal == "") {
            biaya_apd_awal = 0;
        }
	
        var biaya_apd_rp = hitung_rp_khusus(biaya_apd);
	    $("#biaya_apd_input").html(biaya_apd);
	    $("#biaya_apd").val(biaya_apd);

        total_byr = parseFloat(harga_total_awal);
        if(biaya_apd_awal != 0) {
            total_byr = parseFloat(total_byr) - parseFloat(biaya_apd_awal);
        } 
        total_byr = parseFloat(total_byr) + parseFloat(biaya_apd); 
        var total_byr_rp = hitung_rp_khusus(total_byr);

        $("#total_pembayaran").html(total_byr);
        $("#total_pembayaran_input").val(total_byr);
        $("#total_pembayaran_display").html("Rp "+ total_byr_rp +", -");
        $("#count_total_belanja").val(total_byr);
	    $('#modal-xl').modal("hide");*/
	}
</script>