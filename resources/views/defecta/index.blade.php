@extends('layout.app')

@section('title')
Data Defecta
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Transaksi</a></li>
    <li class="breadcrumb-item"><a href="#">Data Defecta</a></li>
    <li class="breadcrumb-item active" aria-current="page">Index</li>
</ol>
@endsection

@section('content')
	<style type="text/css">
		.select2 {
		  width: 100%!important; /* overrides computed width, 100px in your demo */
		}
	</style>

	<div class="card card-info card-outline mb-12 border-left-primary">
	    <div class="card-body">
	      	<h4><i class="fa fa-info"></i> Informasi</h4>
	      	<p>Untuk pencarian, isikan kata yang ingin dicari pada kolom seacrh, lalu tekan enter.</p>
	      	<div id="btn_set" style="display: inline-block;margin-right: 20px;"></div>
	    </div>
	</div>

	<div class="card card-info card-outline" id="main-box" style="">
  		<div class="card-header">
        	<h3 class="card-title">
          		<i class="fas fa-list"></i>
   				List Data Defecta
        	</h3>
      	</div>
        <div class="card-body">
        	<div class="row">
				<div class="form-group col-md-3">
				    <select class="input_select form-control" name="s_is_kirim" id="s_is_kirim">
	                    <option value="2" selected="selected">Belum Dikirim</option>
	                    <option value="1">Sudah Dikirim</option>
	                </select>
				</div>
        	</div>
			<hr>
			<table  id="tb_data_obat" class="table table-bordered table-striped table-hover" width="100%">
		    	<thead>
			        <tr>
			        	<th width="1%"><input type="checkbox" class="checkAlltogle"></th>
			            <th width="3%">No.</th>
                        <th width="10%">ID Obat</th>
                        <th width="30%">Nama Obat</th>
                        <th width="14%">Suplier</th>
                        <th width="10%">Kwantitas</th>
                        <th width="10%">HB</th>
                        <th width="10%">Total</th>
			            <th width="10%">Status</th>
			            <th width="7%">Action</th>
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
 	

 	var tb_data_obat = $('#tb_data_obat').DataTable( {
		paging:true,
		destroy: true,
        autoWidth: false,
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{url("defecta/list_defecta")}}',
		        data:function(d){
		        	d.s_is_kirim = $('#s_is_kirim').val();
		         }
        },
        order: [],
        columns: [
        	{data: 'checkList', name: 'checkList', orderable: false, searchable: false, width:'1%'},
         	{data: 'DT_RowIndex', name: 'DT_RowIndex',width:"2%"},
            {data: 'id', name: 'id', class:'text-center'},
            {data: 'nama', name: 'nama'},
            {data: 'id_suplier_order', name: 'id_suplier_order'},
            {data: 'jumlah_diajukan', name: 'jumlah_diajukan', class:'text-center'},
            {data: 'harga_beli', name: 'harga_beli', class:'text-right'},
            {data: 'total', name: 'total', class:'text-right'},
            {data: 'status', name: 'status', class:'text-center'},
            {data: 'action', name: 'id', orderable: true, searchable: true, class:'text-center'}
        ],
        drawCallback: function(callback) {
            $("#btn_set").html(callback['jqXHR']['responseJSON']['btn_set']);
            console.log(callback['jqXHR']['responseJSON']['btn_set'])
        }
	});


 	setTimeout(function(){
        $('#tb_data_obat .checkAlltogle').prop('checked', false);
    }, 1);

	$(document).ready(function(){
		$("#searching_form").submit(function(e){
			e.preventDefault();
			tb_data_obat.draw(false);
		})

		$('#s_is_kirim').change(function(){
            tb_data_obat.draw(false);
        });
	})

	function send_defecta(id_defecta,act){
        var id_defecta = [id_defecta];
        $.ajax({
            url:'{{url("defecta/send_defecta")}}',
            type: 'POST',
            data: {
            	_token		: "{{csrf_token()}}",
                id_defecta: id_defecta,
                act: act
            },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success:function(data){
                if(data.submit == 1){
                    show_info("Data defecta berhasil dikirim!");
                    tb_data_obat.draw(false);
                }
                else{
                    show_error("Gagal mengirirm data defecta ini!");
                }
            }
        });
    }

    function send_multi_defecta(act){
        if ($("#tb_data_obat input:checkbox[name=check_list]:checked").length > 0) {
            var arr_id_defecta = [];
            $("#tb_data_obat input:checkbox[name=check_list]:checked").each(function(){
                arr_id_defecta.push($(this).data('id'));
            })

            $.ajax({
                url:'{{url("defecta/send_defecta")}}',
                type: 'POST',
                data: {
                	_token		: "{{csrf_token()}}",
                    id_defecta: arr_id_defecta,
                    act: act
                },
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            })
            .done(function(data){
                if(data.submit==1){
                	show_info("Data defecta berhasil dikirim!");
                    tb_data_obat.draw(false);
                    $("#tb_data_obat input:checkbox").prop('checked', false);
                } else{
                	show_error("Gagal mengirirm data defecta ini!");
                }
            })
        }
        else{
            swal({
                title: "Warning",
                text: "centang data yang ingin diSend/diUnSend !",
                type: "error",
                timer: 5000,
                showConfirmButton: false
            });
        }
    }

    function set_nota_order(){
        if ($("#tb_data_order input:checkbox[name=check_list]:checked").length > 0) {
            var arr_id_defecta = [];
            var arr_id_apotek = [];
            var arr_id_suplier = [];
            $("#tb_data_order input:checkbox[name=check_list]:checked").each(function(){
                arr_id_defecta.push($(this).data('id'));
                arr_id_apotek.push($(this).data('id_apotek'));
                arr_id_suplier.push($(this).data('id_suplier'));
            })
    
            var url = '{{url("order/set_nota_order")}}';
            var form = $('<form action="' + url + '" method="post" id="form_order">' +
                        '<input type="hidden" name="_token" id="_token" value="{{csrf_token()}}">' +
                        '<input type="hidden" name="id_defecta" value="'+ arr_id_defecta +'" />' +
                        '<input type="hidden" name="id_apotek" value="'+ arr_id_apotek +'" />' +
                        '<input type="hidden" name="id_suplier" value="'+ arr_id_suplier +'" />' +
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