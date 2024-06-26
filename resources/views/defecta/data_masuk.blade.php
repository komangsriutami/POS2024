@extends('layout.app')

@section('title')
Data Defecta Masuk
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Transaksi</a></li>
    <li class="breadcrumb-item"><a href="#">Data Defecta Masuk</a></li>
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
   				List Data Defecta Masuk
        	</h3>
      	</div>
        <div class="card-body">
        	<div class="row">
                <div class="form-group col-md-2">
                    {!! Form::select('id_status', $statuss, $status_purchasing_aktif, ['id'=>'id_status', 'class' => 'form-control input_select']) !!}
                </div>
                <div class="form-group col-md-3">
                    {!! Form::select('id_proses', $process, $proses_purchasing_aktif, ['id'=>'id_proses', 'class' => 'form-control input_select']) !!}
                </div>
				<div class="form-group col-md-3">
				    {!! Form::select('id_apotek', $apoteks, $apotek_purchasing_aktif, ['id'=>'id_apotek', 'class' => 'form-control input_select']) !!}
				</div>
        	</div>
			<hr>
			<table  id="tb_data_obat" class="table table-bordered table-striped table-hover" width="100%">
		    	<thead>
			        <tr>
			        	<th width="1%"><input type="checkbox" class="checkAlltogle"></th>
			            <th width="3%">No.</th>
                        <th width="5%">Apotek</th>
			            <th width="30%">Nama Obat</th>
                        <th width="10%">Jenis</th>
			            <th width="10%">Jumlah Jual </th>
			            <th width="10%">Margin</th>
			            <th width="30%">Suplier</th>
			            <!-- <th width="10%">Action</th> -->
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
            url: '{{url("defecta/list_defecta_masuk")}}',
		        data:function(d){
		        	d.id_apotek = $('#id_apotek').val();
		        	d.id_status = $('#id_status').val();
                    d.id_proses = $('#id_proses').val();
		         }
        },
        order: [],
        columns: [
        	{data: 'checkList', name: 'checkList', orderable: false, searchable: false, width:'1%'},
         	{data: 'DT_RowIndex', name: 'DT_RowIndex',width:"2%"},
            {data: 'apotek', name: 'apotek', class:'text-center'},
            {data: 'nama', name: 'nama'},
            {data: 'id_satuan', name: 'id_satuan', class:'text-center'},
            {data: 'jumlah_penjualan', name: 'jumlah_penjualan', class:'text-center'},
            {data: 'margin', name: 'margin', class:'text-right'},
            {data: 'suplier', name: 'suplier'},
            /*{data: 'action', name: 'id', orderable: true, searchable: true, class:'text-center'}*/
        ],
        drawCallback: function(callback) {
            $("#btn_set").html(callback['jqXHR']['responseJSON']['btn_set']);
            //console.log(callback['jqXHR']['responseJSON']['btn_set'])
        }
	});


 	setTimeout(function(){
        $('#tb_data_obat .checkAlltogle').prop('checked', false);
    }, 1);

	$(document).ready(function(){
        $('.input_select').select2();

		$("#searching_form").submit(function(e){
			e.preventDefault();
			tb_data_obat.draw(false);
		});

		$('#id_apotek').change(function(){
            set_apotek_purchasing_aktif(); 
        });

        $('#id_proses').change(function(){
            set_proses_purchasing_aktif(); 
        });

        $('#id_status').change(function(){
            set_status_purchasing_aktif(); 
        });
	})

    function set_apotek_purchasing_aktif() {
        $.ajax({
            url:'{{url("defecta/set_apotek_purchasing_aktif")}}',
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
                tb_data_obat.draw(false);
            }
        });
    }

    function set_status_purchasing_aktif() {
        $.ajax({
            url:'{{url("defecta/set_status_purchasing_aktif")}}',
            type: 'POST',
            data: {
                _token      : "{{csrf_token()}}",
                id_status: $('#id_status').val()
            },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success:function(data){
                tb_data_obat.draw(false);
            }
        });
    }

    function set_proses_purchasing_aktif() {
        $.ajax({
            url:'{{url("defecta/set_proses_purchasing_aktif")}}',
            type: 'POST',
            data: {
                _token      : "{{csrf_token()}}",
                id_proses: $('#id_proses').val()
            },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success:function(data){
                tb_data_obat.draw(false);
            }
        });
    }

	function set_status_defecta(id_defecta, id_apotek, act){
        var id_defecta = [id_defecta];
        var id_apotek = [id_apotek];
        $.ajax({
            type: "POST",
            url: '{{url("defecta/set_status_defecta")}}',
            async:true,
            data: {
                _token      : "{{csrf_token()}}",
                id_defecta: id_defecta,
                id_apotek:id_apotek,
                act: act
            },
            beforeSend: function(data){
                // on_load();
                $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
                $("#modal-xl .modal-title").html("Konfirmasi Defecta");
                $('#modal-xl').modal("show");
                $('#modal-xl').find('.modal-body-content').html('');
                $("#modal-xl").find(".overlay").fadeIn("200");
            },
            success:  function(data){
                $('#modal-xl').find('.modal-body-content').html(data);
            },
            complete: function(data){
                $("#modal-xl").find(".overlay").fadeOut("200");
            },
              error: function(data) {
                alert("error ajax occured!");
              }
        });
    }

    function set_multi_status_defecta(act){
        if ($("#tb_data_obat input:checkbox[name=check_list]:checked").length > 0) {
            var arr_id_defecta = [];
            var arr_id_apotek = [];
            $("#tb_data_obat input:checkbox[name=check_list]:checked").each(function(){
                arr_id_defecta.push($(this).data('id'));
                arr_id_apotek.push($(this).data('id_apotek'));
            })

            $.ajax({
                type: "POST",
                url: '{{url("defecta/set_status_defecta")}}',
                async:true,
                data: {
                    _token      : "{{csrf_token()}}",
                    id_defecta: arr_id_defecta,
                    id_apotek:arr_id_apotek,
                    act: act
                },
                beforeSend: function(data){
                    // on_load();
                    $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
                    $("#modal-xl .modal-title").html("Konfirmasi Defecta");
                    $('#modal-xl').modal("show");
                    $('#modal-xl').find('.modal-body-content').html('');
                    $("#modal-xl").find(".overlay").fadeIn("200");
                },
                success:  function(data){
                    $('#modal-xl').find('.modal-body-content').html(data);
                },
                complete: function(data){
                    $("#modal-xl").find(".overlay").fadeOut("200");
                },
                  error: function(data) {
                    alert("error ajax occured!");
                  }
            });
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

    function set_multi_status_defecta_draft(act){
        if ($("#tb_data_obat input:checkbox[name=check_list]:checked").length > 0) {
            var arr_id_defecta = [];
            var arr_id_apotek = [];
            $("#tb_data_obat input:checkbox[name=check_list]:checked").each(function(){
                arr_id_defecta.push($(this).data('id'));
                arr_id_apotek.push($(this).data('id_apotek'));
            })

            $.ajax({
                url:'{{url("defecta/konfirmasi_draft")}}',
                type: 'POST',
                data: {
                    _token      : "{{csrf_token()}}",
                    id_defecta: arr_id_defecta,
                    id_apotek:arr_id_apotek,
                    act: act
                },
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            })
            .done(function(data){
                if(data.submit==1){
                    show_info("Pengajuan berhasil dikembalikan ke status draft!");
                    tb_data_obat.draw(false);
                    $("#tb_data_obat input:checkbox").prop('checked', false);
                } else{
                    show_error("Gagal menyimpan data!");
                }
            })
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

    function submit_order(){
        if($(".validated_form").valid()) {
            data = {};
            $("#form-konfirm-order").find("input[name], select").each(function (index, node) {
                data[node.name] = node.value;
            });

            $.ajax({
                type:"POST",
                url : '{{url("defecta/konfirmasi_order")}}',
                dataType : "json",
                data : data,
                beforeSend: function(data){
                    // replace dengan fungsi loading
                },
                success:  function(data){
                    if(data.submit ==1){
                        show_info("Konfirmasi order berhasil disimpan!");
                        $('#modal-xl').modal("hide");
                        tb_data_obat.draw(false);
                        $("#tb_data_obat input:checkbox").prop('checked', false);
                    }else{
                        show_error("Gagal menyimpan konfirmasi order ini!");
                        return false;
                    }
                },
                complete: function(data){
                    // replace dengan fungsi mematikan loading
                },
                error: function(data) {
                    show_error("error ajax occured!");
                }

            })
        } else {
            return false;
        }
    }


    function submit_transfer(){
        if($(".validated_form").valid()) {
            data = {};
            $("#form-konfirm-transfer").find("input[name], select").each(function (index, node) {
                data[node.name] = node.value;
            });

            $.ajax({
                type:"POST",
                url : '{{url("defecta/konfirmasi_transfer")}}',
                dataType : "json",
                data : data,
                beforeSend: function(data){
                    // replace dengan fungsi loading
                },
                success:  function(data){
                    if(data.submit ==1){
                        show_info("Konfirmasi transfer berhasil disimpan!");
                        $('#modal-xl').modal("hide");
                        tb_data_obat.draw(false);
                        $("#tb_data_obat input:checkbox").prop('checked', false);
                    }else{
                        show_error("Gagal menyimpan konfirmasi transfer ini!");
                        return false;
                    }
                },
                complete: function(data){
                    // replace dengan fungsi mematikan loading
                },
                error: function(data) {
                    show_error("error ajax occured!");
                }

            })
        } else {
            return false;
        }
    }

    function submit_tolak(){
        if($(".validated_form").valid()) {
            data = {};
            $("#form-konfirm-tolak").find("input[name], select").each(function (index, node) {
                data[node.name] = node.value;
            });

            $.ajax({
                type:"POST",
                url : '{{url("defecta/konfirmasi_tolak")}}',
                dataType : "json",
                data : data,
                beforeSend: function(data){
                    // replace dengan fungsi loading
                },
                success:  function(data){
                    if(data.submit ==1){
                        show_info("Konfirmasi tolak defecta berhasil disimpan!");
                        $('#modal-xl').modal("hide");
                        tb_data_obat.draw(false);
                        $("#tb_data_obat input:checkbox").prop('checked', false);
                    }else{
                        show_error("Gagal menyimpan konfirmasi tolak defecta ini!");
                        return false;
                    }
                },
                complete: function(data){
                    // replace dengan fungsi mematikan loading
                },
                error: function(data) {
                    show_error("error ajax occured!");
                }

            })
        } else {
            return false;
        }
    }
</script>
@endsection