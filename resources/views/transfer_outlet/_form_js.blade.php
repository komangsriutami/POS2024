<script type="text/javascript">
    var token = "";

    var tb_nota_transfer_outlet = $('#tb_nota_transfer_outlet').dataTable( {
            processing: true,
            serverSide: true,
            stateSave: true,
            paging: false,
            ajax:{
                    url: '{{url("transfer_outlet/list_detail_transfer_outlet")}}',
                    data:function(d){
                        d.id = $("#id").val();
                }
            },
            columns: [
               {data: 'no', name: 'no', orderable: false, searchable: false, class:'text-center'},
                {data: 'action', name: 'action', orderable: false, searchable: false, class:'text-center'},
                {data: 'nama_barang', name: 'nama_barang', orderable: false, searchable: false, class:'text-left'},
                {data: 'harga_outlet', name: 'harga_outlet', orderable: false, searchable: false, class:'text-right'},
                {data: 'jumlah', name: 'jumlah', orderable: false, searchable: false, class:'text-center'},
                {data: 'total', name: 'total', orderable: false, searchable: false, class:'text-right'}
            ],
            rowCallback: function( row, data, iDisplayIndex ) {
                var api = this.api();
                var info = api.page.info();
                var page = info.page;
                var length = info.length;
                var index = (page * length + (iDisplayIndex +1));
                $('td:eq(0)', row).html(index);
            },
            stateSaveCallback: function(settings,data) {
                localStorage.setItem( 'DataTables_' + settings.sInstance, JSON.stringify(data) )
            },
            stateLoadCallback: function(settings) {
                return JSON.parse( localStorage.getItem( 'DataTables_' + settings.sInstance ) )
            },
            drawCallback: function( settings ) {
                var api = this.api();

                // set total pembayaran
                var total = settings['jqXHR']['responseJSON']['total_transfer'];
                var total_rp = settings['jqXHR']['responseJSON']['total_transfer_format'];
              
                $("#harga_total").html(total_rp);
                $("#harga_total_input").val(total);
                $("#total_to_display").html("Rp "+ total_rp +", -");
            },
            "footerCallback": function ( row, data, start, end, display ) {
                var api = this.api();

                // Remove the formatting to get integer data for summation
                var intVal = function ( i ) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '')*1 :
                        typeof i === 'number' ?
                            i : 0;
                };

            }
        });

    $(document).ready(function(){
        token = $('input[name="_token"]').val();

        cek_status_nota();

        $('.input_select').select2();
        $("#id_apotek_tujuan").select2();
        //$('#id_apotek_tujuan').select2('open');

        $('#id_apotek_tujuan').on('select2:select', function (e) {
            $("#keterangan").focus();
        });


        $("#keterangan").keypress(function(event){
            if (event.which == '10' || event.which == '13') {
                $("#barcode").focus();
                event.preventDefault();
            }
        });
        
        $("#barcode").keypress(function(event){
            if (event.which == '10' || event.which == '13') {
                cari_obat();
                event.preventDefault();
            }
        });

        $("#harga_outlet").keypress(function(event){
            if (event.which == '10' || event.which == '13') {
                $("#jumlah").focus();
            }
        });

        $("#jumlah").keypress(function(event){
            if (event.which == '10' || event.which == '13') {
                var cek_ = cek_kelengkapan_form();
                if(cek_ == 1) {
                    simpan_data();
                } else {
                    show_error("Data item tidak lengkap!");
                }
                event.preventDefault();
            }
        });

        $("#persen").keypress(function(event){
            if (event.which == '10' || event.which == '13') {
                hitung_harga();
                $("#jumlah").focus();
                event.preventDefault();
            }
        });

        $("#persen").keyup(function(){
            hitung_harga();
        });
        
        $(document).on("keyup", function(e){
            var x = e.keyCode || e.which;
            if (x == 16) {  
                // fungsi shift 
                $("#barcode").focus();
            } else if (x == 27) {  
                // fungsi  buka data suplier
            } else if(x==113){
                // fungsi F2 
                submit_valid();
                //save_data(); // belum dibuat
            } else if(x==115){
                // fungsi F4
            } else if(x==118){
                // fungsi F7
                // tidak bisa digunakan
            } else if(x==119){
                // fungsi F8
            } else if(x==120){
                // fungsi F9
            } else if(x==121){
                // fungsi F10
                find_ketentuan_keyboard();
            } else if(x == 17) {
                open_data_obat();
            }
        })

        $('body').addClass('sidebar-collapse');

        hitung_total();
    })

    function cek_status_nota() {
        var is_deleted = $("#is_deleted").val();
        if(is_deleted == 1) {
            $("#id_obat_form").hide();
            $("#nama_form").hide();
            $("#harga_outlet_form").hide();
            $("#jumlah_form").hide();
            $("#btn_save").hide();
        }
    }

    function goBack() {
        window.history.back();
    }

    function hitung_harga() {
        var persen = $("#persen").val();
        var harga_outlet = $("#harga_outlet_default").val();
        var harga_beli_ppn_new = (parseFloat(persen)/100*parseFloat(harga_outlet)) + parseFloat(harga_outlet);
        $("#harga_outlet").val(harga_beli_ppn_new);
    }

    function cari_obat() {
        var barcode = $("#barcode").val();
        var inisial = $("#inisial").val();
        if(Number.isInteger(barcode)) {
            $.ajax({
                url:'{{url("penjualan/cari_obat")}}',
                type: 'POST',
                data: {
                    _token      : "{{csrf_token()}}",
                    barcode: barcode,
                    inisial: inisial
                },
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success:function(data){
                    if(data.is_data == 1) {
                        if(data.harga_stok.harga_jual < data.harga_stok.harga_beli_ppn) {
                            show_error("Alert! Harga beli ppn lebih besar daripada harga jual, item dapat diinput setelah data disesuaikan!");
                            kosongkan_form();
                        } else {
                            var persen = $("#persen").val();
                            var harga_beli_ppn_new = (persen/100*data.harga_stok.harga_beli_ppn) + data.harga_stok.harga_beli_ppn;
                            $("#barcode").val(data.obat.barcode);
                            $("#id_obat").val(data.obat.id);
                            $("#nama_obat").val(data.obat.nama);
                            $("#harga_outlet_default").val(data.harga_stok.harga_beli_ppn);
                            $("#harga_outlet").val(harga_beli_ppn_new);
                            $("#stok_obat").val(data.harga_stok.stok_akhir);
                            $("#harga_outlet").focus();
                        }
                    } else {
                        show_error("Obat dengan barcode tersebut tidak dapat ditemukan!");
                        kosongkan_form();
                    }
                    
                }
            });
        } else {
            open_data_obat(barcode);
        }       
    }

    function add_item_dialog(id_obat, harga_jual, harga_beli, stok_akhir, harga_beli_ppn) {
        var inisial = $("#inisial").val();
        $.ajax({
            url:'{{url("penjualan/cari_obat_dialog")}}',
            type: 'POST',
            data: {
                _token      : "{{csrf_token()}}",
                id_obat: id_obat,
                inisial: inisial
            },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success:function(data){
                if(harga_jual <= harga_beli_ppn) {
                    $('#modal-xl').modal('toggle');
                    show_error("Alert! Harga beli ppn lebih besar daripada harga jual, item dapat diinput setelah data disesuaikan!");
                    kosongkan_form();
                } else {
                    var persen = $("#persen").val();
                    var harga_beli_ppn_new = (persen/100*harga_beli_ppn) + harga_beli_ppn;
                    $("#barcode").val(data.barcode);
                    $("#id_obat").val(data.id);
                    $("#nama_obat").val(data.nama);
                    $("#harga_outlet_default").val(harga_beli_ppn);
                    $("#harga_outlet").val(harga_beli_ppn_new);
                    $("#stok_obat").val(stok_akhir);

                    var x = document.getElementById("persen");
                    if (window.getComputedStyle(x).display === "none") {
                        $("#jumlah").focus();
                    } else {
                        $("#persen").val('');
                        $("#persen").focus();
                    }
                    $('#modal-xl').modal('toggle');
                }
            }
        });
    }

    function kosongkan_form(){
        $("#barcode").val('');
        $("#id_obat").val('');
        $("#nama_obat").val('');
        $("#persen").val(0);
        $("#harga_outlet").val('');
        $("#harga_outlet_default").val('');
        $("#stok_obat").val('');
        $("#jumlah").val('');
        $("#barcode").focus();
        $("#keterangan").val();
        $("#id_apotek_tujuan").val();
        $("#id_apotek_tujuan").show();
        $("#change_apotek_").hide();
    }

    function open_data_obat(barcode) {
        $.ajax({
            type: "POST",
            url: '{{url("penjualan/open_data_obat")}}',
            async:true,
            data: {
                _token  : "{{csrf_token()}}",
                barcode : barcode,
            },
            beforeSend: function(data){
                // on_load();
                $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
                $("#modal-xl .modal-title").html("Data Obat");
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

    $("#add_row_transfer_outlet").click(function(){
        var cek_ = cek_kelengkapan_form();
        if(cek_ == 1) {
            simpan_data();
        } else {
            show_error("Data item tidak lengkap!");
        }
    });

    function cek_kelengkapan_form() {
        var barcode = $("#barcode").val();
        var id_obat = $("#id_obat").val();
        var nama_obat = $("#nama_obat").val();
        var harga_outlet = $("#harga_outlet").val();
        var stok_obat = $("#stok_obat").val();
        var jumlah = $("#jumlah").val();
        if(barcode != '' && id_obat != '' && nama_obat != '' && harga_outlet != '' && stok_obat != '' && jumlah != '') {
            return 1;
        } else {
            return 2;
        }
    }

    function simpan_data() {
        data = {};
        $("#form_to").find("input[name], select").each(function (index, node) {
            data[node.name] = node.value;
        });

        var id = $("#id").val();
        if(id) {
            $.ajax({
                type:"PUT",
                url : '{{url("transfer_outlet/update_item")}}/'+id,
                dataType : "json",
                data : data,
                beforeSend: function(data){
                    // replace dengan fungsi loading
                    spinner.show();
                },
                success:  function(data){
                    if(data.status ==1){
                        kosongkan_form();
                    }else{
                        show_error(data.message);
                        return false;
                    }
                },
                complete: function(data){
                    tb_nota_transfer_outlet.fnDraw(false);
                    spinner.hide();
                },
                error: function(data) {
                    show_error("error ajax occured!");
                }

            });
        } else {
            $.ajax({
                type:"POST",
                url : '{{url("transfer_outlet/add_item")}}',
                dataType : "json",
                data : data,
                beforeSend: function(data){
                    // replace dengan fungsi loading
                    spinner.show();
                },
                success:  function(data){
                    if(data.status == 1){
                        kosongkan_form();
                        $("#id").val(data.id);
                    }else{
                        show_error(data.message);
                        return false;
                    }
                },
                complete: function(data){
                    tb_nota_transfer_outlet.fnDraw(false);
                    spinner.hide();
                },
                error: function(data) {
                    show_error("error ajax occured!");
                }

            });
        }
    }

    function hitung_rp_khusus(nilai) {
        var nilai_str = nilai.toString();
        var res = nilai_str.split(".");
        var number_string = res[0],
            sisa    = number_string.length % 3,
            rupiah  = number_string.substr(0, sisa),
            ribuan  = number_string.substr(sisa).match(/\d{3}/g);
                
        if (ribuan) {
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }
        return rupiah;
    }

    function tambah_item_obat(){
        var counter = $("#counter").val();
        var no_counter = $("#no_counter").val();
        var new_ = parseInt(counter)+1;

        /*if(no_counter != 'undefined') {
            new_ = new_+parseInt(no_counter);
        }*/
        /*alert(new_);*/
        new_ = '-';
        var id_obat = $("#id_obat").val();
        var nama_obat = $('#nama_obat').val();
        var harga_outlet = $("#harga_outlet").val();
        var harga_outlet_rp = hitung_rp_khusus(harga_outlet);
        var jumlah = $("#jumlah").val();
        var total = parseFloat($("#jumlah").val()) * parseFloat($("#harga_outlet").val());
        var total_rp = hitung_rp_khusus(total);
        var stok_obat = parseInt($("#stok_obat").val());
        if(stok_obat >= jumlah) {
            var markup = "<tr>"+
                            "<td><input type='checkbox' name='record'>"+
                            "<input type='hidden' id='detail_transfer_outlet["+counter+"][id]' name='detail_transfer_outlet["+counter+"][id]'><span class='label label-primary btn-sm' onClick='deleteRow(this)' data-toggle='tooltip' data-placement='top' title='Hapus Data'><i class='fa fa-edit'></i> Hapus</span></td> "+

                            +"<input type='hidden' id='detail_transfer_outlet["+counter+"][id]' name='detail_transfer_outlet["+counter+"][id]'></td> "+

                            "<td style='display:none;'><input type='hidden' id='detail_transfer_outlet["+counter+"][id_obat]' name='detail_transfer_outlet["+counter+"][id_obat]' value='"+id_obat+"'>" + id_obat + "</td>"+

                            "<td><input type='hidden' id='detail_transfer_outlet["+counter+"][nama_obat]' name='detail_transfer_outlet["+counter+"][nama_obat]' value='"+nama_obat+"'>" + new_+'. '+nama_obat + "</td>"+

                            "<td style='text-align:right;'><input type='hidden' id='detail_transfer_outlet["+counter+"][harga_outlet]' name='detail_transfer_outlet["+counter+"][harga_outlet]' value='"+harga_outlet+"'>" + harga_outlet + "</td>"+

                            "<td style='text-align:center;'><input type='hidden' id='detail_transfer_outlet["+counter+"][jumlah]' name='detail_transfer_outlet["+counter+"][jumlah]' value='"+jumlah+"' class='jumlah' data-id-obat='"+id_obat+"'><span class='jumlah_label'>" + jumlah + "</span></td>"+

                            "<td style='display:none;' id='hitung_total_"+counter+"' class='hitung_total' data-total='"+total+"'>" + total + "</td>"+

                            "<td style='text-align:right;' id='detail_transfer_outlet["+counter+"][total]'><input type='hidden' class='total' data-id-obat='"+id_obat+"' value='"+total+"'><span class='total_label'>" + total + "</span></td>"+
                        "</tr>";

            var jumlah_label = $(".jumlah_label");
            var total_label = $(".total_label");
            var status_append = true;

            $(".jumlah").each(function(i,l){
                if($(l).data("id-obat")== id_obat){
                    var nilai_jumlah = parseInt($(l).val());
                    if(isNaN(nilai_jumlah)){
                        nilai_jumlah = 0;
                    }

                    var jumlah_var = parseInt( jumlah );
                    if(isNaN(jumlah_var)){
                        jumlah_var = 0;
                    }
                    
                    //var new_jumlah = jumlah_var+nilai_jumlah;
                    var new_jumlah = jumlah_var;

                    $(l).val(new_jumlah);
                    $(jumlah_label[i]).html(new_jumlah);

                    status_append = false;
                }
            })

            $(".total").each(function(i,l){
                if($(l).data("id-obat")== id_obat){
                    var nilai_total = parseInt($(l).val());
                    if(isNaN(nilai_total)){
                        nilai_total = 0;
                    }

                    var total_var = parseInt( total );
                    if(isNaN(total_var)){
                        total_var = 0;
                    }
                    
                    //var new_total = total_var+nilai_total;
                    var new_total = total_var;

                    $(l).val(new_total);
                    $(total_label[i]).html(new_total);
                    $("#hitung_total_"+i).html(new_total);

                    status_append = false;
                }
            })

            if(status_append == true){
                $("#tb_nota_transfer_outlet tbody").append(markup);

                // setting setelah data disimpan
                current_counter = parseInt($("#counter").val());
                if(isNaN(current_counter)){
                    current_counter = 0;
                }
                  
                $("#counter").val(current_counter+1);
            }

            hitung_total();
            kosongkan_form();
        } else {
            show_error("Stok obat tidak mencukupi untuk melakukan transaksi ini!");
        }
    }

    function hitung_total() {
        var tes = $('.hitung_total');
        var total = 0;
        $(tes).each(function(i,l){
            sub_total = parseFloat( $(l).data('total') );
            if(isNaN(sub_total)){
                sub_total = 0;
            }

            total = total+sub_total;
        })
        var total_rp = hitung_rp_khusus(total);
       // $("#harga_total").html("Rp "+total_rp);
        $("#harga_total").html(total);

        $("#total_to_display").html("Rp "+ total_rp +", -");
    }

    function hitung_rp(nilai) {
        var number_string = nilai.toString(),
            sisa    = number_string.length % 3,
            rupiah  = number_string.substr(0, sisa),
            ribuan  = number_string.substr(sisa).match(/\d{3}/g);
                
        if (ribuan) {
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }
        return rupiah;
    }

    function hapus_item_obat() {
        $("table tbody").find('input[name="record"]').each(function(index, el){
            if($(this).is(":checked")){
                $(this).parents("tr").remove();
                //$(this).find('td:first').text(index + 1);

                /*const tr = currentTarget.parentElement.parentElement;
                const tbody = tr.parentElement;
                
                // Hide this element:
                tr.setAttribute('hidden', true);
                
                // Update all indexes:
                let nextIndex = 0;
                
                Array.from(tbody.children).forEach((row) => {
                  if (!row.hasAttribute('hidden')) {
                    // Only increment the counter for those that are not hidden;
                    row.children[0].textContent = ++nextIndex;
                  }
                });*/


                hitung_total();
            }
        });
    }

    function submit_valid(){
        if($(".validated_form").valid()) {
            data = {};
            $("#form_to").find("input[name], select").each(function (index, node) {
                data[node.name] = node.value;
            });

            $("#form_to").submit();
        } else {
            return false;
        }
    }

    function save_data(){
        var id = $("#id").val();
        swal({
            title: "Apakah anda yakin menyimpan data ini?",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Ya",
            cancelButtonText: "Tidak",
            closeOnConfirm: true
        },
        function(){
            submit_valid_konfirm(id);
        });
    }

    function submit_valid_konfirm(id){
        if($(".validated_form").valid()) {
            data = {};
            $("#form_to").find("input[name], select").each(function (index, node) {
                data[node.name] = node.value;
            });

            if(id != "") {
                $.ajax({
                    type:"PUT",
                    url : '{{url("transfer_outlet/")}}/'+id,
                    dataType : "json",
                    data : data,
                    beforeSend: function(data){
                        // replace dengan fungsi loading
                        spinner.show();
                    },
                    success:  function(data){
                        if(data.status ==1){
                            show_info("Data transfer outlet berhasil disimpan!");
                        }else{
                            show_error("Gagal menyimpan data transfer outlet ini!");
                            return false;
                        }
                    },
                    complete: function(data){
                        // replace dengan fungsi mematikan loading
                        spinner.hide();
                        tb_nota_transfer_outlet.fnDraw(false);
                    },
                    error: function(data) {
                        show_error("error ajax occured!");
                    }

                });
            } else {
                $.ajax({
                    type:"POST",
                    url : '{{url("transfer_outlet/")}}',
                    dataType : "json",
                    data : data,
                    beforeSend: function(data){
                        // replace dengan fungsi loading
                        spinner.show();
                    },
                    success:  function(data){
                        if(data.status ==1){
                            show_info("Data transfer outlet berhasil disimpan!");
                        }else{
                            show_error("Gagal menyimpan data transfer outlet ini!");
                            return false;
                        }
                    },
                    complete: function(data){
                        // replace dengan fungsi mematikan loading
                        spinner.hide();
                        tb_nota_transfer_outlet.fnDraw(false);
                        //location.reload();
                    },
                    error: function(data) {
                        show_error("error ajax occured!");
                    }

                });
            }
        } else {
            //spinner.hide();
            return false;
        }
    }

    function find_ketentuan_keyboard(){
        $.ajax({
            type: "POST",
            url: '{{url("transfer_outlet/find_ketentuan_keyboard")}}',
            async:true,
            data: {
                _token:"{{csrf_token()}}",
            },
            beforeSend: function(data){
              // on_load();
            $('#modal-lg').find('.modal-lg').find(".modal-content").find(".modal-header").attr("class","modal-header bg-info");
            $("#modal-lg .modal-title").html("Ketentuan Kode Keyboard");
            $('#modal-lg').modal("show");
            $('#modal-lg').find('.modal-body-content').html('');
            $("#modal-lg").find(".overlay").fadeIn("200");
            },
            success:  function(data){
              $('#modal-lg').find('.modal-body-content').html(data);
            },
            complete: function(data){
                $("#modal-lg").find(".overlay").fadeOut("200");
            },
              error: function(data) {
                alert("error ajax occured!");
              }
        });
    }

    function edit_detail(no, id){
        $.ajax({
            type: "POST",
            url: '{{url("transfer_outlet/edit_detail")}}',
            async:true,
            data: {
                _token:"{{csrf_token()}}",
                no : no,
                id : id,
            },
            beforeSend: function(data){
              // on_load();
            $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
            $("#modal-xl .modal-title").html("Edit Data Transfer Outlet");
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

    function hapus_detail(r, id){
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
                url: '{{url("transfer_outlet/hapus_detail/")}}/'+id,
                async:true,
                data: {
                    _token:token,
                    id:id
                },
                beforeSend: function(data){
                    // replace dengan fungsi loading
                },
                success:  function(data){
                    var obj = JSON.parse(data);
                    if(obj.status_code=='0000'){
                        var i = r.parentNode.parentNode.rowIndex;
                        document.getElementById("tb_nota_transfer_outlet").deleteRow(i);

                        swal("Deleted!", "Item transfer outlet berhasil dihapus.", "success");
                        if(obj.data.is_deleted == 1) {
                            $("#status_nota").html("Deleted");
                            document.getElementById("status_nota").className = "ribbon bg-danger";

                            $("#id_obat_form").hide();
                            $("#nama_form").hide();
                            $("#harga_outlet_form").hide();
                            $("#jumlah_form").hide();
                            $("#btn_save").hide();
                        }
                    }else{
                        swal("Failed!", "Gagal menghapus item transfer outlet.", "error");
                    }
                },
                complete: function(data){
                    hitung_total();
                },
                error: function(data) {
                    swal("Error!", "Ajax occured.", "error");
                }
            });
        });
    }

    function deleteRow(r) {
        var i = r.parentNode.parentNode.rowIndex;
        document.getElementById("tb_nota_transfer_outlet").deleteRow(i);
        hitung_total();
    }

    function change_apotek(id_transfer) {
        $.ajax({
            type: "POST",
            url: '{{url("transfer_outlet/change_apotek")}}',
            async:true,
            data: {
                _token  : "{{csrf_token()}}",
                id_transfer : id_transfer,
            },
            beforeSend: function(data){
                // on_load();
                $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
                $("#modal-xl .modal-title").html("Transfer Outlet- Ganti Apotek");
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

    function change_obat(no, id_detail_transfer) {
        $.ajax({
            type: "POST",
            url: '{{url("transfer_outlet/change_obat")}}',
            async:true,
            data: {
                _token  : "{{csrf_token()}}",
                no : no,
                id_detail_transfer : id_detail_transfer,
            },
            beforeSend: function(data){
                // on_load();
                $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
                $("#modal-xl .modal-title").html("Transfer Outlet- Ganti Obat");
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

    function open_list_harga() {
        var id_obat = $("#id_obat").val();
        if(id_obat == '') {
            swal("Failed!", "Obat belum dipilih!", "error");
        } else {
            $.ajax({
                type: "POST",
                url: '{{url("transfer_outlet/open_list_harga")}}',
                async:true,
                data: {
                    _token  : "{{csrf_token()}}",
                    id_obat : id_obat,
                },
                beforeSend: function(data){
                    // on_load();
                    $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
                    $("#modal-xl .modal-title").html("List Harga Obat");
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
    }

    function clear_page() {
        $("#id").val('');
        $("#harga_total").val('');
        $("#harga_total").html('');
    }

    function delete_item(id){
        swal({
            title: "Apakah anda yakin menghapus data ini?",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Ya",
            cancelButtonText: "Tidak",
            closeOnConfirm: true
        },
        function(){
            $.ajax({
                type:"GET",
                url : '{{url("transfer_outlet/delete_item/")}}/'+id,
                dataType : "json",
                data : {},
                beforeSend: function(data){
                    // replace dengan fungsi loading
                    //spinner.show();
                },
                success:  function(data){
                    if(data.status ==1){
                        show_info("Data transfer outlet berhasil dihapus!");
                        kosongkan_form();
                        if(data.is_sisa == 1) {

                        } else {
                            // tidak ada sisa item transfer outlet clear semua cache
                            window.location.replace('{{url("transfer_outlet")}}/');
                        }
                        tb_nota_transfer_outlet.fnDraw(false);
                    }else{
                        show_error("Gagal menghapus data transfer outlet ini!");
                        return false;
                    }
                },
                complete: function(data){
                    // replace dengan fungsi mematikan loading
                    //spinner.hide();
                },
                error: function(data) {
                    show_error("error ajax occured!");
                }

            });
        });
    }
</script>