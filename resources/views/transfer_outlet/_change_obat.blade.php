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
                    <input type="hidden" name="no" id="no" value="{{ $no }}">
                    <input type="hidden" name="id" id="id" value="{{ $detail_transfer_outlet->id }}">
                    <input type="hidden" name="_token" id="_token" value="{{csrf_token()}}">
                    <div class="row">
                        <div class="form-group col-md-6">
                            {!! Form::label('id_obat_awal', 'Obat Awal') !!}
                            {!! Form::select('id_obat_awal', $obats, $detail_transfer_outlet->id_obat, ['id' => 'id_obat_awal', 'class' => 'form-control required input_select', 'disabled' => 'disabled']) !!}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('id_obat_akhir', 'Obat Akhir') !!}
                            {!! Form::select('id_obat_akhir', $obats, $detail_transfer_outlet->id_obat, ['id' => 'id_obat_akhir', 'class' => 'form-control required input_select']) !!}
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-success" type="button" onClick="save_change_obat(this, {{ $no }}, {{ $detail_transfer_outlet->id }})" data-toggle="tooltip" data-placement="top" title="Simpan"><i class="fa fa-save"></i> Simpan</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-undo"></i> Kembali</button>
                </div>
            </div>
         </div>
    </div>
</form>
<script type="text/javascript">
    $(document).ready(function(){
        $("#id_obat_awal, #id_obat_akhir").select2({
          minimumInputLength: 3,
          allowClear: true,
          placeholder: ''
        });
    })

    function save_change_obat(obj, no, id){
        if($(".validated_form").valid()) {
        data = {};
        $("#form-edit").find("input[name], select, radio, textarea").each(function (index, node) {
              data[node.name] = node.value;
        });

        $.ajax({
          type:"POST",
          url : '{{url("transfer_outlet/update_obat")}}/'+id,
          dataType : "json",
          data : data,
          beforeSend: function(data){
            // replace dengan fungsi loading
          },
          success:  function(data){
            if(data ==1){
              swal("Success!", "Proses ganti obat tujuan berhasil dilakukan!", "success");
              $('#modal-xl').modal('toggle');
            }else{
              swal("Failed!", "Proses ganti obat tujuan gagal!", "error");
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

