{!! Form::model($syarat_pembayaran, ['method' => $method, 'class'=>'validated_form', 'id'=>'form-syarat', 'route' => $route ]) !!}
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <div class="card-body">
                    @include('syarat_pembayaran/_form', ['submit_text' => 'Update', 'syarat_pembayaran'=>$syarat_pembayaran])
                </div>
                <div class="card-footer text-center">
                    <button type="submit" class="btn btn-success btnSubmit" type="button" data-toggle="tooltip" data-placement="top" title="Simpan"><i class="fa fa-save"></i> Simpan</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-undo"></i> Kembali</button>
                </div>
            </div>
         </div>
    </div>
{!! Form::close() !!}
<script type="text/javascript">
    var token = "";

    $(document).ready(function(){
        token = $('input[name="_token"]').val();

        $("#form-syarat").submit(function(e){
            e.preventDefault();

            if($(this).valid() == true){

                $.ajax({
                    type:"POST",
                    url : this.action,
                    dataType : "json",
                    data : $(this).serialize(),
                    beforeSend: function(data){
                        // replace dengan fungsi loading
                    },
                    success:  function(data){
                        if(data.status ==1){
                            show_info("Berhasil menyimpan data");
                            $("#id_syarat_pembayaran").append(data.option);
                            $("#id_syarat_pembayaran").val(data.id);
                            $("#id_syarat_pembayaran").trigger("change");
                            $('#modal-xl').modal("hide");
                        } else {
                            show_error("Terjadi kesalahan. Gagal menyimpan data.");
                        }
                    },
                    complete: function(data){
                        
                    },
                    error: function(data) {
                        show_error("ajax post error");
                    }

                });
            }
            
        });

    });
</script>
