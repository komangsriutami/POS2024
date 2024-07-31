<script type="text/javascript">
    var token = "";
    $(document).ready(function(){
        token = $('input[name="_token"]').val();
        $('.input_select').select2();
        $("#id_dokter").select2();
        //$('#id_apotek_tujuan').select2('open');
        $('#id_dokter').on('select2:select', function (e) {
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
        $("#harga_dokter").keypress(function(event){
            if (event.which == '10' || event.which == '13') {
                $("#jumlah").focus();
            }
        });
        $("#jumlah").keypress(function(event){
            if (event.which == '10' || event.which == '13') {
                tambah_item_obat();
                event.preventDefault();
            }
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
    function goBack() {
        window.history.back();
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
                            $("#barcode").val(data.obat.barcode);
                            $("#id_obat").val(data.obat.id);
                            $("#nama_obat").val(data.obat.nama);
                            $("#harga_dokter").val(data.harga_stok.harga_jual);
                            $("#stok_obat").val(data.harga_stok.stok_akhir);
                            $("#harga_dokter").focus();
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
                    $("#barcode").val(data.barcode);
                    $("#id_obat").val(data.id);
                    $("#nama_obat").val(data.nama);
                    $("#harga_dokter").val(harga_jual);
                    $("#stok_obat").val(stok_akhir);
                    $("#jumlah").focus();
                    $('#modal-xl').modal('toggle');
                }
            }
        });
    }

    function kosongkan_form(){
        $("#barcode").val('');
        $("#id_obat").val('');
        $("#nama_obat").val('');
        $("#harga_dokter").val('');
        $("#stok_obat").val('');
        $("#jumlah").val('');
        $("#barcode").focus();
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
    $("#add_row_transfer_dokter").click(function(){
        var cek_ = cek_kelengkapan_form();
        if(cek_ == 1) {
            tambah_item_obat();
        } else {
            show_error("Data item penjualan tidak lengkap!");
        }
    });
    function cek_kelengkapan_form() {
        var barcode = $("#barcode").val();
        var id_obat = $("#id_obat").val();
        var nama_obat = $("#nama_obat").val();
        var harga_dokter = $("#harga_dokter").val();
        var stok_obat = $("#stok_obat").val();
        var jumlah = $("#jumlah").val();
        if(barcode != '' && id_obat != '' && nama_obat != '' && harga_dokter != '' && stok_obat != '' && jumlah != '') {
            return 1;
        } else {
            return 2;
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
        var id_obat = $("#id_obat").val();
        var nama_obat = $('#nama_obat').val();
        var harga_dokter = $("#harga_dokter").val();
        var harga_dokter_rp = hitung_rp(harga_dokter);
        var jumlah = $("#jumlah").val();
        var total = parseFloat($("#jumlah").val()) * parseFloat($("#harga_dokter").val());
        var total_rp = hitung_rp_khusus(total);
        var stok_obat = parseInt($("#stok_obat").val());
        if(stok_obat >= jumlah) {
            var markup = "<tr>"+
                            "<td><input type='checkbox' name='record'>"+
                            "<input type='hidden' id='detail_transfer_dokter["+counter+"][id]' name='detail_transfer_dokter["+counter+"][id]'><span class='label label-primary btn-sm' onClick='deleteRow(this)' data-toggle='tooltip' data-placement='top' title='Hapus Data'><i class='fa fa-edit'></i> Hapus</span></td> "+
                            +"<input type='hidden' id='detail_transfer_dokter["+counter+"][id]' name='detail_transfer_dokter["+counter+"][id]'></td> "+
                            "<td style='display:none;'><input type='hidden' id='detail_transfer_dokter["+counter+"][id_obat]' name='detail_transfer_dokter["+counter+"][id_obat]' value='"+id_obat+"'>" + id_obat + "</td>"+
                            "<td><input type='hidden' id='detail_transfer_dokter["+counter+"][nama_obat]' name='detail_transfer_dokter["+counter+"][nama_obat]' value='"+nama_obat+"'>" + nama_obat + "</td>"+
                            "<td style='text-align:right;'><input type='hidden' id='detail_transfer_dokter["+counter+"][harga_dokter]' name='detail_transfer_dokter["+counter+"][harga_dokter]' value='"+harga_dokter+"'>Rp " + harga_dokter_rp + "</td>"+
                            "<td style='text-align:center;'><input type='hidden' id='detail_transfer_dokter["+counter+"][jumlah]' name='detail_transfer_dokter["+counter+"][jumlah]' value='"+jumlah+"' class='jumlah' data-id-obat='"+id_obat+"'><span class='jumlah_label'>" + jumlah + "</span></td>"+
                            "<td style='display:none;' id='hitung_total_"+counter+"' class='hitung_total' data-total='"+total+"'>" + total + "</td>"+
                            "<td style='text-align:right;' id='detail_transfer_dokter["+counter+"][total]'><input type='hidden' class='total' data-id-obat='"+id_obat+"' value='"+total+"'><span class='total_label'>Rp " + total_rp + "</span></td>"+
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
                $("#tb_nota_transfer_dokter tbody").append(markup);
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
        $("#harga_total").html("Rp "+total_rp);
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
        $("table tbody").find('input[name="record"]').each(function(){
            if($(this).is(":checked")){
                $(this).parents("tr").remove();
                hitung_total();
            }
        });
    }
    function submit_valid(){
        if($(".validated_form").valid()) {
            data = {};
            $("#form_td").find("input[name], select").each(function (index, node) {
                data[node.name] = node.value;
            });
            $("#form_td").submit();
        } else {
            return false;
        }
    }
    function find_ketentuan_keyboard(){
        $.ajax({
            type: "POST",
            url: '{{url("transfer_dokter/find_ketentuan_keyboard")}}',
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
            url: '{{url("transfer_dokter/edit_detail")}}',
            async:true,
            data: {
                _token:"{{csrf_token()}}",
                no : no,
                id : id,
            },
            beforeSend: function(data){
              // on_load();
            $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
            $("#modal-xl .modal-title").html("Edit Data Transfer Dokter");
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
                type: "POST",
                url: '{{url("transfer_dokter/hapus_detail/")}}/'+id,
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
                        var i = r.parentNode.parentNode.rowIndex;
                        document.getElementById("tb_nota_transfer_dokter").deleteRow(i);
                        swal("Deleted!", "Item transfer dokter berhasil dihapus.", "success");
                    }else{
                        swal("Failed!", "Gagal menghapus item transfer dokter.", "error");
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
        document.getElementById("tb_nota_transfer_dokter").deleteRow(i);
        hitung_total();
    }
</script>