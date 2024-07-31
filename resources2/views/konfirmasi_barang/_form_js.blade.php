<script type="text/javascript">
	var token = "";

	var tb_barang_datang_pembelian = $('#tb_barang_datang_pembelian').DataTable( {
		paging:true,
		destroy: true,
        autoWidth: false,
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{url("pembelian/list_data_order")}}',
		        data:function(d){
		        	d.id_nota = $('#id_nota').val();
		        	d.id_jenis = $('#id_jenis').val();
		         }
        },
        order: [],
        columns: [
        	{data: 'checkList', name: 'checkList', orderable: false, searchable: false, width:'1%'},
        	{data: 'no', name: 'no',width:"2%", class:'text-center'},
            {data: 'id_obat', name: 'id_obat'},
            {data: 'jumlah', name: 'jumlah', class:'text-center'},
            {data: 'action', name: 'id',orderable: true, searchable: true}
        ],
        drawCallback: function(callback) {
            $("#btn_set").html(callback['jqXHR']['responseJSON']['btn_set']);
        }
	});

	$(document).ready(function(){
		token = $('input[name="_token"]').val();

		$('#id_nota').on('select2:select', function (e) {
			tb_barang_datang_pembelian.draw(false);
			//var checkedStatus = this.checked;
		    //$("input:checkbox").prop("checked", true);
        });

        //$('#id_jenis').on('select2:select', function (e) {
			//tb_data_obat.draw(false);
			//var checkedStatus = this.checked;
		    //$("input:checkbox").prop("checked", true);
        //});

		$('.input_select').select2();
	})

	function goBack() {
	    window.history.back();
	}

    function konfirm_barang_diterima(){
        var id_nota_pembelian = $("#id_nota_pembelian").val();
        if(id_nota_pembelian > 0) {
            swal({
                title: "Apakah anda yakin menggunakan nota yang dipilih untuk barang yang dikonfirmasi ?",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Ya",
                cancelButtonText: "Tidak",
                closeOnConfirm: true
            },
            function(){
                if ($("#tb_barang_datang_pembelian input:checkbox[name=check_list]:checked").length > 0) {
                    var arr_id_order = [];;
                    $("#tb_barang_datang_pembelian input:checkbox[name=check_list]:checked").each(function(){
                        arr_id_order.push($(this).data('id'));
                    })

                    var url = '{{url("pembelian/konfirmasi_barang_store")}}';
                    var form = $('<form action="' + url + '" method="post" id="form_konfirmasi_barang_datang">' +
                                '<input type="hidden" name="_token" id="_token" value="{{csrf_token()}}">' +
                                '<input type="hidden" name="arr_id_order" value="'+ arr_id_order +'" />' +
                                '<input type="hidden" name="id_nota_pembelian" value="'+ id_nota_pembelian +'" />' +
                                '<input type="hidden" name="id_jenis_konfirmasi" value="1" />' +
                      '</form>');
                    $('body').append(form);
                    form_konfirmasi_barang_datang.submit();
                }
                else{
                    swal({
                        title: "Warning",
                        text: "centang data yang ingin dikonfirmasi !",
                        type: "error",
                        timer: 5000,
                        showConfirmButton: false
                    });
                }
            })
        } else {
            swal({
                title: "Apakah anda yakin membuat nota baru untuk barang yang dikonfirmasi ?",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Ya",
                cancelButtonText: "Tidak",
                closeOnConfirm: true
            },
            function(){
                if ($("#tb_barang_datang_pembelian input:checkbox[name=check_list]:checked").length > 0) {
                    var arr_id_order = [];;
                    $("#tb_barang_datang_pembelian input:checkbox[name=check_list]:checked").each(function(){
                        arr_id_order.push($(this).data('id'));
                    })

                    var url = '{{url("pembelian/konfirmasi_barang_store")}}';
                    var form = $('<form action="' + url + '" method="post" id="form_konfirmasi_barang_datang">' +
                                '<input type="hidden" name="_token" id="_token" value="{{csrf_token()}}">' +
                                '<input type="hidden" name="arr_id_order" value="'+ arr_id_order +'" />' +
                                '<input type="hidden" name="id_nota_pembelian" value="" />' +
                                '<input type="hidden" name="id_jenis_konfirmasi" value="2" />' +
                      '</form>');
                    $('body').append(form);
                    form_konfirmasi_barang_datang.submit();
                }
                else{
                    swal({
                        title: "Warning",
                        text: "centang data yang ingin dikonfirmasi !",
                        type: "error",
                        timer: 5000,
                        showConfirmButton: false
                    });
                }
            })
        }
    }

	function konfirm_barang_tidak_diterima(){
        swal({
            title: "Apakah anda yakin mengkonfirmasi barang ini tidak diterima?",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Ya",
            cancelButtonText: "Tidak",
            closeOnConfirm: true
        },
        function(){
            if ($("#tb_barang_datang_pembelian input:checkbox[name=check_list]:checked").length > 0) {
                var arr_id_order = [];;
                $("#tb_barang_datang_pembelian input:checkbox[name=check_list]:checked").each(function(){
                    arr_id_order.push($(this).data('id'));
                })

                $.ajax({
                    url:'{{url("pembelian/set_konfirm_barang_tidak_diterima")}}',
                    type: 'POST',
                    data: {
                        _token  : "{{csrf_token()}}",
                        arr_id_order: arr_id_order
                    },
                    dataType: 'json',
                })
                .done(function(data){
                    if(data.submit=='1'){
                        swal({
                            title: "Success",
                            text: data.message,
                            type: "success",
                            timer: 5000,
                            showConfirmButton: false
                        });
                        tb_barang_datang_pembelian.draw(false);
                        $("#tb_barang_datang_pembelian input:checkbox").prop('checked', false);
                    }
                    else{
                        swal({
                            title: "Error",
                            text: data.message,
                            type: "error",
                            timer: 5000,
                            showConfirmButton: false
                        });
                    }
                })
            }
            else{
                swal({
                    title: "Warning",
                    text: "centang data yang ingin dikonfirmasi !",
                    type: "error",
                    timer: 5000,
                    showConfirmButton: false
                });
            }
        })
    }
</script>