@extends('layout.app')

@section('title')
Data Transfer
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Transaksi</a></li>
    <li class="breadcrumb-item"><a href="#">Data Transfer</a></li>
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
                <div id="btn_set_transfer" style="display: inline-block;"></div>
            </div>
        </div>
    </div>

	<div class="card card-info card-outline mb-12 border-left-primary">
	    <div class="card-body">
	      	<h4><i class="fa fa-info"></i> Informasi</h4>
	      	<p>Untuk pencarian, isikan kata yang ingin dicari pada kolom search, lalu tekan enter.</p>
            <a class="btn btn-success w-md m-b-5" href="#" onclick="add_keranjang()"><i class="fa fa-plus"></i> Tambah Data</a>
	    </div>
	</div>

	<div class="card card-info card-outline" id="main-box" style="">
  		<div class="card-header">
        	<h3 class="card-title">
          		<i class="fas fa-list"></i>
   				List Data Transfer
        	</h3>
      	</div>
        <div class="card-body">
        	<div class="row">
                <div class="form-group col-md-2">
                    <select id="id_proses" name="id_proses" class="form-control input_select">
                        <option value="0" {!!( "0" == $status_status_aktif ? 'selected' : '')!!}>Belum Diproses</option>
                        <option value="1" {!!( "1" == $status_status_aktif ? 'selected' : '')!!}>Proses</option>
                        <option value="2" {!!( "2" == $status_status_aktif ? 'selected' : '')!!}>Complete</option>
                    </select>
                </div>
                <input type="hidden" name="id_apotek" id="id_apotek" value="{{ session('id_apotek_active') }}">
				<!-- <div class="form-group col-md-2">
				    {!! Form::select('id_apotek', $apoteks, $apotek_transfer_aktif, ['id'=>'id_apotek', 'class' => 'form-control input_select']) !!}
				</div> -->
                <div class="form-group col-md-4">
                    {!! Form::select('id_apotek_transfer', $apotek_transfers, $apotektrans_transfer_aktif, ['id'=>'id_apotek_transfer', 'class' => 'form-control input_select']) !!}
                </div>
        	</div>
			<hr>
			<table  id="tb_data_transfer" class="table table-bordered table-striped table-hover">
		    	<thead>
			        <tr>
			        	<th width="1%"><input type="checkbox" class="checkAlltogle"></th>
			            <th width="3%">No.</th>
                        <th width="8%">Apotek</th>
			            <th width="40%">Nama Obat</th>
                        <th width="23%">Suplier</th>
			            <th width="8%">Total Stok</th>
			            <th width="8%">Action</th>
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
 	

 	var tb_data_transfer = $('#tb_data_transfer').DataTable( {
		paging:true,
		destroy: true,
        autoWidth: false,
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{url("transfer/list_transfer")}}',
		        data:function(d){
		        	d.id_apotek = $('#id_apotek').val();
                    d.id_apotek_transfer = $('#id_apotek_transfer').val();
		         }
        },
        order: [],
        columns: [
        	{data: 'checkList', name: 'checkList', orderable: false, searchable: false, width:'1%'},
         	{data: 'DT_RowIndex', name: 'DT_RowIndex',width:"2%"},
            {data: 'apotek', name: 'apotek', class:'text-center'},
            {data: 'nama', name: 'nama'},
            {data: 'id_apotek_transfer', name: 'id_apotek_transfer'},
            {data: 'total_stok', name: 'total_stok', class:'text-center'},
            {data: 'action', name: 'action', class:'text-center'}
        ],
        drawCallback: function(callback) {
            $("#btn_set_transfer").html(callback['jqXHR']['responseJSON']['btn_set_transfer']);
            //console.log(callback['jqXHR']['responseJSON']['btn_set'])
        }
	});


 	setTimeout(function(){
        $('#tb_data_transfer .checkAlltogle').prop('checked', false);
    }, 1);

	$(document).ready(function(){
		$("#searching_form").submit(function(e){
			e.preventDefault();
			tb_data_transfer.draw(false);
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
                tb_data_transfer.draw(false);
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
                tb_data_transfer.draw(false);
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
                tb_data_transfer.draw(false);
            }
        });
    }

    function set_nota_transfer(){
        if ($("#tb_data_transfer input:checkbox[name=check_list]:checked").length > 0) {
            var arr_id_defecta = [];
            var arr_id_apotek = [];
            var arr_id_apotek_transfer = [];
            $("#tb_data_transfer input:checkbox[name=check_list]:checked").each(function(){
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

    function add_keranjang(){
        //alert(jumlah);
        $.ajax({
            type: "POST",
            url: '{{url("defecta/add_keranjang_transfer_manual")}}',
            async:true,
            data: {
                _token: "{{csrf_token()}}",
                id_obat: 0,
            },
            beforeSend: function(data){
                $('#modal-md').find('.modal-md').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
                $("#modal-md .modal-title").html("Masukkan ke Keranjang");
                $('#modal-md').modal("show");
                $('#modal-md').find('.modal-body-content').html('');
                $("#modal-md").find(".overlay").fadeIn("200");
            },
            success:  function(data){
                $('#modal-md').find('.modal-body-content').html(data);
            },
            complete: function(data){
                $("#modal-md").find(".overlay").fadeOut("200");
            },
              error: function(data) {
                alert("error ajax occured!");
              }
        });
    }

    function submit_manual(){
        if($(".validated_form").valid()) {
            data = {};
            $("#form-add-defecta").find("input[name], select").each(function (index, node) {
                data[node.name] = node.value;
                
            });

            $.ajax({
                type:"POST",
                url : '{{url("defecta")}}',
                dataType : "json",
                data : data,
                beforeSend: function(data){
                    // replace dengan fungsi loading
                },
                success:  function(data){
                    if(data.status ==1){
                        show_info("Data defecta berhasil disimpan!");
                        $('#modal-md').modal('toggle');
                    }else{
                        show_error("Gagal menyimpan data ini!");
                        return false;
                    }
                },
                complete: function(data){
                    // replace dengan fungsi mematikan loading
                    tb_data_transfer.draw(false);
                },
                error: function(data) {
                    show_error("error ajax occured!");
                }

            })
        } else {
            return false;
        }
    }

    function delete_item(id){
        swal({
            title: "Apakah anda yakin menghapus data ini?",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Ya",
            cancelButtonText: "Tidak",
            closeOnConfirm: false
        },
        function(){
            $.ajax({
                type: "DELETE",
                url: '{{url("defecta")}}/'+id,
                async:true,
                data: {
                    _token:token,
                    id:id
                },
                beforeSend: function(data){
                    // replace dengan fungsi loading
                },
                success:  function(data){
                    if(data==1){
                        swal("Deleted!", "Data defecta berhasil dihapus.", "success");
                    }else{
                        swal("Failed!", "Gagal menghapus data defecta.", "error");
                    }
                },
                complete: function(data){
                    tb_data_transfer.draw(false);
                },
                error: function(data) {
                    swal("Error!", "Ajax occured.", "error");
                }
            });
        });
    }
</script>
@endsection