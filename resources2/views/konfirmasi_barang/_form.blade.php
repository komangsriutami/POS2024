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
<div class="row">
	<div class="form-group col-md-12">
        <p style="font-size:20pt;" class="text-info">No.SP {{ $order->kode }} - {{ $order->tgl_nota }}</p>
        <input type="hidden" name="id_nota" id="id_nota" value="{{ $order->id }}">
    
        {!! Form::hidden('is_from_order', 1, array('class' => 'form-control', 'id'=>'is_from_order')) !!}
    </div>

    <div class="form-group col-md-12">
        <label>Pilih Nota Pembelian</label>
        <select class="form-control input_select" id="id_nota_pembelian" name="id_nota_pembelian">
            <option value="0">Buat Nota Baru</option>
            @foreach($pembelians as $obj)
                <option value="{{ $obj->id }}">IDNota.{{ $obj->id }} - {{$obj->tgl_nota}}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group col-md-12">
    	<div class="box box-success" id="detail_data_penjualan">
		    <div class="box-body">
		        <table  id="tb_barang_datang_pembelian" class="table table-bordered table-striped table-hover">
		            <thead>
		                <tr>
		                	<th width="1%"><input type="checkbox" class="checkAlltogle"></th>
		                    <th width="5%">No.</th>
		                    <th width="75%">ID Obat</th>
		                    <th width="10%">Jumlah</th>
		                    <th width="10%">Action</th>
		                </tr>
		            </thead>
		            <tbody>
		            </tbody>
		        </table>

		    </div>
		</div>
    </div>
</div>