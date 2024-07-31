<div class="row">
    <div class="col-sm-12">
        <div class="card card-info card-outline">
            <div class="card-body">
                <input type="hidden" name="id" id="id" value="{{ $obat->id }}">
                <input type="hidden" name="id_obat" id="id_obat" value="{{ $obat->id_obat }}">
                <input type="hidden" name="stok_awal_so" id="stok_awal_so" value="{{ $obat->stok_awal_so }}">
                <div class="form-group col-md-12">
                    {!! Form::label('stok_akhir_so', 'Stok Akhir SO (*)') !!}
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">@</span>
                        </div>
                        {!! Form::text('stok_akhir_so', $obat->stok_akhir_so, array('id' => 'stok_akhir_so', 'class' => 'form-control', 'placeholder'=>'Stok Akhir', 'autocomplete' => 'off')) !!}
                    </div>
                </div>
            </div><!-- 
            <div class="card-footer">
                <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal"><i class="fa fa-undo"></i> Kembali</button>
            </div> -->
        </div>
     </div>
</div>

<script type="text/javascript">
    var token = '{{csrf_token()}}';


    $(document).ready(function(){
        var stok_akhir_so = $('#stok_akhir_so').val();        
        $('#stok_akhir_so').focus().val('').val(stok_akhir_so);   

        $("#stok_akhir_so").keypress(function(event){
            if (event.which == '10' || event.which == '13') {
                set_data(this);
            }
        });
    })
</script>

