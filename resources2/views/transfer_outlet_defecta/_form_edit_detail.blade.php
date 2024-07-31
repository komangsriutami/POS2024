    {!! Form::model($transfer, ['class'=>'validated_form', 'id'=>'form-edit-tranfer']) !!} 
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
                    <input type="hidden" name="id_detail_transfer" id="id_detail_transfer" value="{{ $detailTransfer->id }}">
                    <input type="hidden" name="id_new" id="id_new" value="{{ $transfer_outlet->id }}">
                    <input type="hidden" name="id_obat_new" id="id_obat_new" value="{{ $transfer->id_obat }}">
                    <input type="hidden" name="stok_obat" id="stok_obat" value="0">


                    <div class="row">
                        <div class="form-group col-md-12">
                            {!! Form::label('obat', 'Nama Obat') !!}
                            {!! Form::text('obat', $transfer->obat->nama, array('class' => 'form-control', 'placeholder'=>'Nama Apotek', 'readonly' => 'readonly')) !!}
                        </div>
                        <div class="form-group col-md-2">
                            {!! Form::label('jumlah_rev', 'Jumlah') !!}
                            {!! Form::text('jumlah_rev', $transfer->jumlah, array('class' => 'form-control', 'placeholder'=>'Masukan Jumlah Rev', 'id' => 'jumlah_rev')) !!}
                        </div>
                        <div class="form-group col-md-3">
                            {!! Form::label('harga', 'Harga Outlet') !!}
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                {!! Form::text('harga_outlet_new', null, array('class' => 'form-control', 'placeholder'=>'Masukan Harga', 'id' => 'harga_outlet_new', 'readonly' => 'readonly')) !!}
                            </div>
                        </div>
                        <?php
                            $total = '';
                        ?>
                        <div class="form-group col-md-3">
                            {!! Form::label('total_harga', 'Total') !!}
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                {!! Form::text('total_harga_new', $total, array('class' => 'form-control', 'placeholder'=>'Total Harga', 'id' => 'total_harga_new', 'readonly' => 'readonly')) !!}
                            </div>
                        </div> 
                </div>
                <div class="card-footer">
                    <button class="btn btn-success" type="button" onClick="set_detail(this, {{$no}})" data-toggle="tooltip" data-placement="top" title="Simpan"><i class="fa fa-save"></i> Simpan</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-undo"></i> Kembali</button>
                </div>
            </div>
         </div>
    </div>
<script type="text/javascript">
    $(document).ready(function(){
        $("#jumlah_rev").focus();
        var id_obat = $("#id_obat_new").val();
        lengkapi_data_item(id_obat);
     
        $("#jumlah_rev").change(function() {
            cek_perubahan_harga_beli_new();
        });

        $('.datepicker').datepicker({
            autoclose:true,
            format:"yyyy-mm-dd",
            forceParse: false
        });

        $("#jumlah_rev").keypress(function(event){
            if (event.which == '10' || event.which == '13') {
                cek_perubahan_harga_beli_new();
            }
        });
    })

    function set_detail(obj, no){
        data = {};
        $("#form_transfer_outlet").find("input[name], select").each(function (index, node) {
            data[node.name] = node.value;
        });

        delete data['id'];
        delete data['id_obat'];
        delete data['harga_outlet'];
        delete data['total'];
        delete data['jumlah'];
        delete data['jumlah_rev'];

        data.id  = $("#id_new").val();
        data.id_detail_transfer  = $("#id_detail_transfer").val();
        data.id_obat  = $("#id_obat_new").val();
        data.harga_outlet  = $("#harga_outlet_new").val();
        data.total  = $("#total_harga_new").val();
        data.jumlah  = $("#jumlah_rev").val();

        var id = $("#id").val();
        if(id) {
            $.ajax({
                type:"PUT",
                url : '{{url("transfer_outlet/update_item")}}/'+id,
                dataType : "json",
                data : data,
                beforeSend: function(data){
                    // replace dengan fungsi loading
                    spinner.show();
                },
                success:  function(data){
                    if(data.status ==1){
                        kosongkan_form();
                        $('#modal-xl').modal('toggle');
                    }else{
                        //show_error(data.message);
                        swal(data.message, "error");
                        return false;
                    }
                },
                complete: function(data){
                    tb_nota_transfer_outlet.fnDraw(false);
                    spinner.hide();
                },
                error: function(data) {
                    show_error("error ajax occured!");
                }

            });
        } else {
            $.ajax({
                type:"POST",
                url : '{{url("transfer_outlet/add_item")}}',
                dataType : "json",
                data : data,
                beforeSend: function(data){
                    // replace dengan fungsi loading
                    spinner.show();
                },
                success:  function(data){
                    if(data.status == 1){
                        kosongkan_form();
                        $("#id").val(data.id);
                        $('#modal-xl').modal('toggle');
                    }else{
                        //show_error(data.message);
                        swal(data.message, "error");
                        return false;
                    }
                },
                complete: function(data){
                    tb_nota_transfer_outlet.fnDraw(false);
                    spinner.hide();
                },
                error: function(data) {
                    show_error("error ajax occured!");
                }

            });
        }
    }

    function lengkapi_data_item(id_obat) {
        var inisial = $("#inisial").val();
        $.ajax({
                url:'{{url("penjualan/cari_obatID")}}',
                type: 'POST',
                data: {
                    _token      : "{{csrf_token()}}",
                    id_obat: id_obat,
                    inisial: inisial
                },
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success:function(data){
                    if(data.is_data == 1) {
                        if(data.harga_stok.harga_jual < data.harga_stok.harga_beli_ppn) {
                            show_error("Alert! Harga beli ppn lebih besar daripada harga jual, item dapat diinput setelah data disesuaikan!");
                        } else {
                            var persen = $("#persen").val();
                            var harga_beli_ppn_new = (parseFloat(persen)/100*parseFloat(data.harga_stok.harga_beli_ppn)) + parseFloat(data.harga_stok.harga_beli_ppn);
                            $("#harga_outlet_new").val(harga_beli_ppn_new);
                            $("#stok_obat").val(data.harga_stok.stok_akhir);
                            cek_perubahan_harga_beli_new();
                        }
                    } else {
                        show_error("Obat dengan barcode tersebut tidak dapat ditemukan!");
                    }
                    
                }
            });
    }

    function cek_perubahan_harga_beli_new() {
        var jumlah = $("#jumlah_rev").val();
        var harga_outlet = $("#harga_outlet_new").val();
        var total_harga = $("#total_harga_new").val();


        if(jumlah == "") {
            jumlah = 1;
        } else {
            jumlah = jumlah;
        }

        if(harga_outlet == "") {
            harga_outlet = 0;
        } else {
            harga_outlet = harga_outlet;
        }

 
        var total = parseFloat(jumlah) * parseFloat(harga_outlet);
        $("#total_harga_new").val(total);
    }
</script>

