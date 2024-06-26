<form id="form-edit">
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
                    <input type="hidden" name="id" id="id" value="{{ $transfer_outlet->id }}">
                    <input type="hidden" name="_token" id="_token" value="{{csrf_token()}}">
                    <div class="row">
                        <div class="form-group col-md-6">
                            {!! Form::label('id_apotek_awal', 'Apotek Tujuan Awal') !!}
                            {!! Form::select('id_apotek_awal', $apoteks, $transfer_outlet->id_apotek_tujuan, ['id' => 'id_apotek_awal', 'class' => 'form-control required input_select', 'disabled' => 'disabled']) !!}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('id_apotek_akhir', 'Apotek Tujuan Akhir') !!}
                            {!! Form::select('id_apotek_akhir', $apoteks, $transfer_outlet->id_apotek_tujuan, ['id' => 'id_apotek_akhir', 'class' => 'form-control required input_select']) !!}
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-success" type="button" onClick="save_change_apotek(this, {{ $transfer_outlet->id }})" data-toggle="tooltip" data-placement="top" title="Simpan"><i class="fa fa-save"></i> Simpan</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-undo"></i> Kembali</button>
                </div>
            </div>
         </div>
    </div>
</form>
<script type="text/javascript">
    $(document).ready(function(){
        $("#id_apotek_awal, #id_apotek_akhir").select2({});
    })

    function save_change_apotek(obj, id){
        if($(".validated_form").valid()) {
        data = {};
        $("#form-edit").find("input[name], select, radio, textarea").each(function (index, node) {
              data[node.name] = node.value;
        });

        $.ajax({
          type:"POST",
          url : '{{url("transfer_outlet/update_apotek")}}/'+id,
          dataType : "json",
          data : data,
          beforeSend: function(data){
            // replace dengan fungsi loading
          },
          success:  function(data){
            if(data ==1){
              swal("Success!", "Proses ganti apotek tujuan berhasil dilakukan!", "success");
              $('#modal-xl').modal('toggle');
            }else{
              swal("Failed!", "Proses ganti apotek tujuan gagal!", "error");
              return false;
            }
          },
          complete: function(data){
            // replace dengan fungsi mematikan loading
            //tb_list_makalah.fnDraw(false);
            location.reload();
          },
          error: function(data) {
            swal("Error!", "Error ajax occured!", "error");
          }
        })
      } else {
        return false;
      }
    }
</script>

