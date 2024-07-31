@extends('layout.app')

@section('title')
Data Item Pembelian
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Transaksi</a></li>
    <li class="breadcrumb-item"><a href="#">Data Item Pembelian</a></li>
    <li class="breadcrumb-item active" aria-current="page">Index</li>
</ol>
@endsection

@section('content')
	<style type="text/css">
		.select2 {
		  width: 100%!important; /* overrides computed width, 100px in your demo */
		}
	</style>
    <style type="text/css">
        #divfix {
           bottom: 0;
           right: 0;
           position: fixed;
           z-index: 3000;
            }
        .format_total {
            font-size: 18px;
            font-weight: bold;
            color:#D81B60;
        }
    </style>
    <div class="row" id="divfix">
        <div class="col-sm-12">
            <div class="callout callout-success">
                <div id="btn_set" style="display: inline-block;"></div>
            </div>
        </div>
    </div>

	<div class="card card-info card-outline mb-12 border-left-primary">
	    <div class="card-body">
	      	<h4><i class="fa fa-info"></i> Informasi</h4>
	      	<p>Untuk pencarian, isikan kata yang ingin dicari pada kolom search, lalu tekan enter.</p>
	    </div>
	</div>

	<div class="card card-info card-outline" id="main-box" style="">
  		<div class="card-header">
        	<h3 class="card-title">
          		<i class="fas fa-list"></i>
   				List Data Pembelian
        	</h3>
      	</div>
        <div class="card-body">
        	<div class="row">
                <div class="form-group col-md-2">
                </div>
        	</div>
			<hr>
			<table  id="tb_data_item_pembelian" class="table table-bordered table-striped table-hover">
		    	<thead>
			        <tr>
                        <th width="3%" class="text-center">No.</th>
                        <th width="5%" class="text-center">No Nota</th>
                        <th width="7%" class="text-center">Tanggal</th>
                        <td width="20%" class="text-center"><strong>Nama Obat</strong></td>
                        <td width="10%" class="text-center"><strong>Total I</strong></td>
                        <td width="10%" class="text-center"><strong>Jumlah</strong></td>
                        <td width="10%" class="text-center"><strong>Harga Beli</strong></td>
                        <td width="10%" class="text-center"><strong>Diskon(Rp)</strong></td>
                        <td width="10%" class="text-center"><strong>Diskon(%)</strong></td>
                        <td width="10%" class="text-center"><strong>Total II</strong></td>
                        <td width="5%" class="text-center"><strong>Action</strong></td>
                    </tr>
		        </thead>
		        <tbody>
		        </tbody>
			</table>
        </div>
  	</div>
@endsection

@section('script')
<script type="text/javascript">
	var token = '{{csrf_token()}}';
 	

 	var tb_data_item_pembelian = $('#tb_data_item_pembelian').DataTable( {
		paging:true,
		destroy: true,
        autoWidth: false,
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{url("pembelian/list_pembelian_item")}}',
		        data:function(d){
		         }
        },
        order: [],
        columns: [
        	{data: 'no', name: 'no',width:"2%", class:'text-center'},
            {data: 'id', name: 'id', class:'text-center'},
            {data: 'id_nota', name: 'id_nota', class:'text-center'},
            {data: 'tgl_jatuh_tempo', name: 'tgl_jatuh_tempo', class:'text-center'},
            {data: 'suplier', name: 'Suplier'},
            {data: 'no_faktur', name: 'no_faktur', class:'text-center'},
            {data: 'jumlah', name: 'jumlah', class:'text-right'},
            {data: 'is_lunas', name: 'is_lunas', class:'text-center'},
            {data: 'is_tanda_terima', name: 'is_tanda_terima', class:'text-center'},
            {data: 'action', name: 'id',orderable: true, searchable: true}
        ],
        drawCallback: function(callback) {
            $("#btn_set").html(callback['jqXHR']['responseJSON']['btn_set']);
        }
	});


 	setTimeout(function(){
        $('#tb_data_item_pembelian .checkAlltogle').prop('checked', false);
    }, 1);

	$(document).ready(function(){
		$("#searching_form").submit(function(e){
			e.preventDefault();
			tb_data_item_pembelian.draw(false);
		});

        $('.input_select').select2({});

		$('#id_apotek').change(function(){
            set_apotek_transfer_aktif(); 
        });

        $('#id_apotek_transfer').change(function(){
            set_apotektrans_transfer_aktif(); 
        });

        $('#id_proses').change(function(){
            set_status_transfer_aktif(); 
        });
	})

    function set_apotek_transfer_aktif() {
        $.ajax({
            url:'{{url("transfer/set_apotek_transfer_aktif")}}',
            type: 'POST',
            data: {
                _token      : "{{csrf_token()}}",
                id_apotek: $('#id_apotek').val()
            },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success:function(data){
                tb_data_item_pembelian.draw(false);
            }
        });
    }

    function set_apotektrans_transfer_aktif() {
        $.ajax({
            url:'{{url("transfer/set_apotektrans_transfer_aktif")}}',
            type: 'POST',
            data: {
                _token      : "{{csrf_token()}}",
                id_apotek_transfer: $('#id_apotek_transfer').val()
            },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success:function(data){
                tb_data_item_pembelian.draw(false);
            }
        });
    }

    function set_status_transfer_aktif() {
        $.ajax({
            url:'{{url("transfer/set_status_transfer_aktif")}}',
            type: 'POST',
            data: {
                _token      : "{{csrf_token()}}",
                id_status : $('#id_proses').val()
            },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success:function(data){
                tb_data_item_pembelian.draw(false);
            }
        });
    }

    function set_nota_transfer(){
        if ($("#tb_data_item_pembelian input:checkbox[name=check_list]:checked").length > 0) {
            var arr_id_defecta = [];
            var arr_id_apotek = [];
            var arr_id_apotek_transfer = [];
            $("#tb_data_item_pembelian input:checkbox[name=check_list]:checked").each(function(){
                arr_id_defecta.push($(this).data('id'));
                arr_id_apotek.push($(this).data('id_apotek'));
                arr_id_apotek_transfer.push($(this).data('id_apotek_transfer'));
            })
    
            var url = '{{url("transfer/set_nota_transfer")}}';
            var form = $('<form action="' + url + '" method="post" id="form_order">' +
                        '<input type="hidden" name="_token" id="_token" value="{{csrf_token()}}">' +
                        '<input type="hidden" name="id_defecta" value="'+ arr_id_defecta +'" />' +
                        '<input type="hidden" name="id_apotek" value="'+ arr_id_apotek +'" />' +
                        '<input type="hidden" name="id_apotek_transfer" value="'+ arr_id_apotek_transfer +'" />' +
              '</form>');
            $('body').append(form);
            form_order.submit();
        }
        else{
            swal({
                title: "Warning",
                text: "centang data terlebih dahulu!",
                type: "error",
                timer: 5000,
                showConfirmButton: false
            });
        }
    }
</script>
@endsection