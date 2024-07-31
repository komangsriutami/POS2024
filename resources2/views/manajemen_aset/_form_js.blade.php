<script type="text/javascript">
    var token = "";
    $(document).ready(function() {
        token = $('input[name="_token"]').val();
        $('.input_select').select2();
        $('#tgl_transaksi').datepicker({
            autoclose:true,
            format:"yyyy-mm-dd",
            forceParse: false
        });
        $("#keterangan").keypress(function(event){
            if (event.which == '10' || event.which == '13') {
                $("#kode_aset").focus();
                event.preventDefault();
            }
        });
        $("#kode_aset").keypress(function(event){
            if (event.which == '10' || event.which == '13') {
                cari_aset();
                event.preventDefault();
            }
        });
        $('#id_kondisi_aset').on('select2:select', function (e) {
            $("#merek").focus();
        });
        $("#merek").keypress(function(event){
            if (event.which == '10' || event.which == '13') {
                $("#jumlah").focus();
                event.preventDefault();
            }
        });
        $("#jumlah").keypress(function(event){
            if (event.which == '10' || event.which == '13') {
                $("#nilai_satuan").focus();
                event.preventDefault();
            }
        });
        $("#nilai_satuan").keypress(function(event){
            if (event.which == '10' || event.which == '13') {
                var cek_ = cek_kelengkapan_form();
                if(cek_ == 1) {
                    tambah_item_aset();
                } else {
                    show_error("Data detail aset tidak lengkap!");
                }
                event.preventDefault();
            }
        });
        hitung_total();
    })
    function goBack() {
        window.history.back();
    }
    function tambah_item_aset(){
        var counter = $("#counter").val();
        var id_aset = $("#id_aset").val();
        var nama_aset = $('#nama_aset').val();
        var kode_aset = $('#kode_aset').val();
        var nilai_satuan = $("#nilai_satuan").val();
        var nilai_satuan_rp = hitung_rp_khusus(nilai_satuan);
        var jumlah = $("#jumlah").val();
        var id_dasar_harga = $("#id_dasar_harga").val();
        var id_kondisi_aset = $("#id_kondisi_aset").val();
        var merek = $("#merek").val();
        var total = parseFloat(jumlah) * parseFloat(nilai_satuan);
        var total_rp = hitung_rp_khusus(total);
        var dasar_harga = '<span class="text-info">[Belum ditentukan]</span>';
        if(id_dasar_harga == 1) {
            dasar_harga = '<span class="text-secondary">[Perolehan]</span>';
        } else if(id_dasar_harga == 2) {
            dasar_harga = '<span class="text-secondary">[Taksiran]</span>';
        }
        var kondisi_aset = '<span class="text-info">[Belum ditentukan]</span>';
        if(id_kondisi_aset == 1) {
            kondisi_aset = '<span class="text-success">[Baik]</span>';
        } else if(id_kondisi_aset == 2) {
            kondisi_aset = '<span class="text-warning">[Rusak Ringan]</span>';
        } else if(id_kondisi_aset == 3) {
            kondisi_aset = '<span class="text-danger">[Rusak Berat]</span>';
        }
        var markup = "<tr>"+
                        "<td><input type='checkbox' name='record'>"+
                        "<input type='hidden' id='detail_aset["+counter+"][id]' name='detail_aset["+counter+"][id]'><span class='label label-primary btn-sm' onClick='deleteRow(this)' data-toggle='tooltip' data-placement='top' title='Hapus Data'><i class='fa fa-edit'></i> Hapus</span></td> "+
                        "<td style='display:none;'><input type='hidden' id='detail_aset["+counter+"][id_aset]' name='detail_aset["+counter+"][id_aset]' value='"+id_aset+"' data-id-aset='"+id_aset+"'>" + id_aset + "</td>"+
                        "<td style='display:none;'><input type='hidden' id='detail_aset["+counter+"][kode_aset]' name='detail_aset["+counter+"][kode_aset]' value='"+kode_aset+"' data-id-aset='"+id_aset+"'>" + kode_aset + "</td>"+
                        "<td><input type='hidden' id='detail_aset["+counter+"][nama_aset]' name='detail_aset["+counter+"][nama_aset]' value='"+nama_aset+"'>" + nama_aset + "</td>"+
                        "<td style='text-align:center;'><input type='hidden' id='detail_aset["+counter+"][id_dasar_harga]' name='detail_aset["+counter+"][id_dasar_harga]' value='"+id_dasar_harga+"' class='id_dasar_harga' data-id-aset='"+id_aset+"'><span class='id_dasar_harga_label'>" + dasar_harga + "</span></td>"+
                        "<td style='text-align:center;'><input type='hidden' id='detail_aset["+counter+"][id_kondisi_aset]' name='detail_aset["+counter+"][id_kondisi_aset]' value='"+id_kondisi_aset+"' class='id_kondisi_aset' data-id-aset='"+id_aset+"'><span class='id_kondisi_aset_label'>" + kondisi_aset + "</span></td>"+
                         "<td style='text-align:center;'><input type='hidden' id='detail_aset["+counter+"][merek]' name='detail_aset["+counter+"][merek]' value='"+merek+"' class='merek' data-id-aset='"+id_aset+"'><span class='merek_label'>" + merek + "</span></td>"+
                        "<td style='text-align:center;'><input type='hidden' id='detail_aset["+counter+"][jumlah]' name='detail_aset["+counter+"][jumlah]' value='"+jumlah+"' class='jumlah' data-id-aset='"+id_aset+"'><span class='jumlah_label'>" + jumlah + "</span></td>"+
                         "<td style='text-align:right;'><input type='hidden' id='detail_aset["+counter+"][nilai_satuan]' name='detail_aset["+counter+"][nilai_satuan]' value='"+nilai_satuan+"' class='nilai_satuan' data-id-aset='"+id_aset+"'><span class='nilai_satuan_label'>Rp " + nilai_satuan_rp + "</span></td>"+
                        "<td style='text-align: right;'><input type='hidden' id='detail_aset["+counter+"][total_nilai]' name='detail_aset["+counter+"][total_nilai]' value='"+total+"' class='total_nilai' data-id-aset='"+id_aset+"'><span class='total_label'>Rp "+ total_rp + "</span></td>"+
                        "<td style='display:none;' id='hitung_total_"+counter+"' class='hitung_total'>" + total + "</td>"+
                    "</tr>";
        var nilai_satuan_label = $(".nilai_satuan_label");
        var jumlah_label = $(".jumlah_label");
        var id_dasar_harga_label = $(".id_dasar_harga_label");
        var id_kondisi_aset_label = $(".id_kondisi_aset_label");
        var merek_label = $(".merek_label");
        var total_label = $(".total_label");
        var status_append = true;
        $(".nilai_satuan").each(function(i,l){
            if($(l).data("id-aset")== id_aset){
                var nilai_satuan_ = parseInt($(l).val());
                if(isNaN(nilai_satuan_)){
                    nilai_satuan_ = 0;
                }
                var nilai_satuan_var = parseInt( nilai_satuan );
                if(isNaN(nilai_satuan_var)){
                    nilai_satuan_var = 0;
                }
                
                var new_nilai_satuan = nilai_satuan_var;
                $(l).val(new_nilai_satuan);
                $(nilai_satuan_label[i]).html(new_nilai_satuan);
                status_append = false;
            }
        })
        $(".jumlah").each(function(i,l){
            if($(l).data("id-aset")== id_aset){
                var nilai_jumlah = parseInt($(l).val());
                if(isNaN(nilai_jumlah)){
                    nilai_jumlah = 0;
                }
                var jumlah_var = parseInt( jumlah );
                if(isNaN(jumlah_var)){
                    jumlah_var = 0;
                }
                
                var new_jumlah = jumlah_var;
                $(l).val(new_jumlah);
                $(jumlah_label[i]).html(new_jumlah);
                status_append = false;
            }
        })
        $(".id_dasar_harga").each(function(i,l){
            if($(l).data("id-aset")== id_aset){
                var nilai_id_dasar_harga = $(l).val();
                if(nilai_id_dasar_harga == ''){
                    nilai_id_dasar_harga = '';
                }
                var id_dasar_harga_var = id_dasar_harga;
                if(id_dasar_harga_var == ''){
                    id_dasar_harga_var = '';
                }
                var new_id_dasar_harga = id_dasar_harga_var;
                $(l).val(new_id_dasar_harga);
                $(id_dasar_harga_label[i]).html(new_id_dasar_harga);
                status_append = false;
            }
        })
        $(".id_kondisi_aset").each(function(i,l){
            if($(l).data("id-aset")== id_aset){
                var nilai_id_kondisi_aset = $(l).val();
                if(nilai_id_kondisi_aset == ''){
                    nilai_id_kondisi_aset = '';
                }
                var id_kondisi_aset_var = id_kondisi_aset;
                if(id_kondisi_aset_var == ''){
                    id_kondisi_aset_var = '';
                }
                
                var new_id_kondisi_aset = id_kondisi_aset_var;
                $(l).val(new_id_kondisi_aset);
                $(id_kondisi_aset_label[i]).html(new_id_kondisi_aset);
                status_append = false;
            }
        })
        $(".merek").each(function(i,l){
            if($(l).data("id-aset")== id_aset){
                var nilai_merek = $(l).val();
                if(nilai_merek == ''){
                    nilai_merek = '';
                }
                var merek_var = merek;
                if(merek_var == ''){
                    merek_var = '';
                }
                
                var new_merek = merek_var;
                $(l).val(new_merek);
                $(merek_label[i]).html(new_merek);
                status_append = false;
            }
        })
        $(".total").each(function(i,l){
            if($(l).data("id-aset")== id_aset){
                var nilai_total = parseInt($(l).val());
                if(isNaN(nilai_total)){
                    nilai_total = 0;
                }
                var total_var = parseInt( total );
                if(isNaN(total_var)){
                    total_var = 0;
                }
                var new_total = total_var;
                $(l).val(new_total);
                $(total_label[i]).html(new_total);
                $("#hitung_total_"+i).html(new_total);
                status_append = false;
            }
        })
        if(status_append == true){
            $("#tb_aset tbody").append(markup);
            current_counter = parseInt($("#counter").val());
            if(isNaN(current_counter)){
                current_counter = 0;
            }
              
            $("#counter").val(current_counter+1);
        }      
        hitung_total();
        kosongkan_form();
    }
    $("#add_row_aset").click(function(){
        var cek_ = cek_kelengkapan_form();
        if(cek_ == 1) {
            tambah_item_aset();
        } else {
            show_error("Data detail aset tidak lengkap!");
        }
    });
    function cek_kelengkapan_form() {
        var kode_aset = $("#kode_aset").val();
        var id_aset = $("#id_aset").val();
        var nama_aset = $("#nama_aset").val();
        var nilai_satuan = $("#nilai_satuan").val();
        var id_kondisi_aset = $("#id_kondisi_aset").val();
        var id_dasar_harga = $("#id_dasar_harga").val();
        var merek = $("#merek").val();
        var jumlah = $("#jumlah").val();
        if(kode_aset != '' && id_aset != '' && nama_aset != '' && nilai_satuan != '' && id_kondisi_aset != '' && id_dasar_harga != '' && merek != '' && jumlah != '') {
            return 1;
        } else {
            return 2;
        }
    }
    function kosongkan_form(){
        $("#kode_aset").val('');
        $("#id_aset").val('');
        $("#nama_aset").val('');
        $("#nilai_satuan").val('');
        $("#merek").val('');
        $("#jumlah").val('');
        $("#id_dasar_harga").val('');
        $("#id_kondisi_aset").val('');
    }
    function cari_aset() {
        var kode_aset = $("#kode_aset").val();
        if(Number.isInteger(kode_aset)) {
            $.ajax({
                url:'{{url("manajemen_aset/cari_aset")}}',
                type: 'POST',
                data: {
                    _token      : "{{csrf_token()}}",
                    kode_aset: kode_aset
                },
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success:function(data){
                    if(data.is_data == 1) {
                        $("#kode_aset").val(data.aset.kode_aset);
                        $("#id_aset").val(data.aset.id);
                        $("#nama_aset").val(data.aset.nama);
                        $("#id_dasar_harga").focus();
                    } else {
                        show_error("Aset dengan kode tersebut tidak dapat ditemukan!");
                        kosongkan_form();
                    }
                    
                }
            });
        } else {
            open_data_aset(kode_aset);
        }   
    }
    function hitung_total(){
        var tes = $('.hitung_total');
        var total = 0;
        
        $(tes).each(function(i,l){
            sub_total = parseFloat( $(l).html() );
            if(isNaN(sub_total)){
                sub_total = 0;
            }
            total = total+sub_total;
        })
        var total2 = parseFloat(total);
        var total_rpx = hitung_rp_khusus(total2);
        $("#total_aset_display").html("Rp "+ total_rpx +", -");
        $("#nilai_total").html("Rp "+ total_rpx);
        $("#total_nilai_aset").val(total);
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
    function open_data_aset(kode_aset) {
        $.ajax({
            type: "POST",
            url: '{{url("manajemen_aset/open_data_aset")}}',
            async:true,
            data: {
                _token  : "{{csrf_token()}}",
                kode_aset : kode_aset,
            },
            beforeSend: function(data){
                // on_load();
                $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
                $("#modal-xl .modal-title").html("Data Aset");
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
    function add_item_dialog(id_aset) {
        $.ajax({
            url:'{{url("manajemen_aset/cari_aset_dialog")}}',
            type: 'POST',
            data: {
                _token      : "{{csrf_token()}}",
                id_aset: id_aset
            },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success:function(data){ 
                $("#kode_aset").val(data.kode_aset);
                $("#id_aset").val(data.id);
                $("#nama_aset").val(data.nama);
                $("#id_dasar_harga").focus();
                $('#modal-xl').modal('toggle');
            }
        });
    }
    function submit_valid(){
        if($(".validated_form").valid()) {
            data = {};
            $("#form_ta").find("input[name], select").each(function (index, node) {
                data[node.name] = node.value;
            });
            $("#form_ta").submit();
        } else {
            return false;
        }
    }
    function edit_detail(no, id){
        $.ajax({
            type: "POST",
            url: '{{url("manajemen_aset/edit_detail")}}',
            async:true,
            data: {
                _token:"{{csrf_token()}}",
                no : no,
                id : id,
            },
            beforeSend: function(data){
              // on_load();
            $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
            $("#modal-xl .modal-title").html("Edit Data Aset");
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
                url: '{{url("manajemen_aset/hapus_detail/")}}/'+id,
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
                        document.getElementById("tb_aset").deleteRow(i);
                        swal("Deleted!", "Item aset berhasil dihapus.", "success");
                    }else{
                        swal("Failed!", "Gagal menghapus item aset.", "error");
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
        document.getElementById("tb_aset").deleteRow(i);
        hitung_total();
    }
</script>