@extends('layout.app')

@section('title')
Analisa Pembelian
@endsection

@section('breadcrumb')
@endsection

@section('content')
    <style type="text/css">
        .dataTables_filter {
            visibility: hidden;
        }
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

    <div class="card card-info card-outline" id="main-box" style="">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i>
                List Data
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <form role="form" id="searching_form">
                        <!-- text input -->
                        <div class="row">
                            
                            <div class="form-group  col-md-2">
                                <label>Referensi</label>
                                <select id="referensi" name="referensi" class="form-control input_select">
                                    <!-- <option value="">- pilih referensi data-</option> -->
                                    <option value="1">1 bulan terakhir</option>
                                    <option value="2">3 bulan terakhir</option>
                                    <option value="3">6 bulan terakhir</option>
                                    <option value="4">1 tahun terakhir</option>
                                </select>
                            </div>
                            <div class="form-group  col-md-2">
                                <label>Status</label>
                                <select id="status" name="status" class="form-control input_select">
                                    <option value="">- pilih status-</option>
                                    <option value="1">Overstok</option>
                                    <option value="2">Understok</option>
                                    <option value="3">Potensial Loss</option>
                                    <option value="4">Stock Off</option>
                                    <option value="5">Dead Stok</option>
                                    <option value="6">Stok On Hand</option>
                                </select>
                            </div>
                            <div class="form-group  col-md-8">
                                <label>Nama Obat</label>
                                <input type="text" name="nama"  id="nama" class="form-control" autocomplete="off">
                            </div>
                            <div class="col-lg-12" style="text-align: center;">
                                <button type="submit" class="btn btn-primary" id="datatable_filter"><i class="fa fa-search"></i> Cari</button>
                                <span class="btn bg-olive" onClick="export_data()"  data-toggle="modal" data-placement="top" title="Export Data"><i class="fa fa-file-excel-o" aria-hidden="true"></i>Export</span> 
                                <span class="btn bg-olive" onClick="export_data_all()"  data-toggle="modal" data-placement="top" title="Export Data All"><i class="fa fa-file-excel-o" aria-hidden="true"></i>Export All Outlet</span> 
                            </div>
                        </div>
                    </form>
                </div>
                <!-- <div class="col-md-6">
                    <div class="row">
                        <div class="form-group  col-md-12">
                            <div class="card card-info">
                                <div class="card-header">
                                    <h3 class="card-title">
                                      <i class="fas fa-edit"></i>
                                      Keterangan
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <dl class="row">
                                        <dt class="col-sm-4">Overstok</dt>
                                        <dd class="col-sm-8">Saat stok terkini diatas kebutuhan rata-rata perbulan+10%.</dd>
                                        <dt class="col-sm-4">Understok</dt>
                                        <dd class="col-sm-8">Saat stok terkini dibawah atau sama dengan rata-rata kebutuhan perbulan.</dd>
                                        <dt class="col-sm-4">Potensial Loss</dt>
                                        <dd class="col-sm-8">Saat Stok terkini 0 tapi stok rata-rata sebulan diatas 0.</dd>
                                        <dt class="col-sm-4">Stock Off</dt>
                                        <dd class="col-sm-8">Saat Stok terkini 0, kebutuhan perbulan 0, dan jumlah terjual 0.</dd>
                                        <dt class="col-sm-4">Dead Stok</dt>
                                        <dd class="col-sm-8">Saat Stok terkini diatas 0 tapi total terjual dan kebutuhan perbulan sama dengan 0.</dd>
                                        <dt class="col-sm-4">Stok On Hand</dt>
                                        <dd class="col-sm-8">Saat SKU terjual, kebutuhan rata-rata perbulan dan stok terkini lebih dari 0.
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> -->
            </div>
            <div class="card p-4" id="sum_analisa_pembelian">
                <div class="row">
                    <div class="col-md-12">
                        <h4 class="sum_analisa_pembelian_date">Hasil Analisis Pembelian dari _ s.d. _</h4>
                    </div>
                    <div class="col-md-4">
                        <div class="row">
                            <div class="col-md-6">Overstok</div>
                            <div class="col-md-6 overstok">: 0 produk</div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">Understok</div>
                            <div class="col-md-6 understok">: 0 produk</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="row">
                            <div class="col-md-6">Potensial Loss</div>
                            <div class="col-md-6 potensial-loss">: 0 produk</div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">Stock Off</div>
                            <div class="col-md-6 stock-off">: 0 produk</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="row">
                            <div class="col-md-6">Dead Stok</div>
                            <div class="col-md-6 dead-stok">: 0 produk</div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">Stock On Hand</div>
                            <div class="col-md-6 stok-on-hand">: 0 produk</div>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <table class="table table-bordered table-striped table-hover" id="tb_analisa_pembelian" width="100%">
                <thead>
                    <tr>
                        <th width="3%">No.</th>
                        <th width="25%">ID|Nama Obat</th>
                        <th width="5%">HBPPN</th>
                        <th width="5%">Terjual</th>
                        <th width="5%">Satuan</th>
                        <th width="10%">Produsen</th>
                        <th width="7%">kebutuhan/bulan</th>
                        <th width="7%">Stok Saat Ini</th>
                        <th width="8%">Status</th>
                        <th width="15%">Saran Pembelian</th>
                        <th width="5%">Defecta</th>
                        <th width="10%">Action</th>
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

    var tb_analisa_pembelian = $('#tb_analisa_pembelian').dataTable( {
        processing: true,
        serverSide: true,
        stateSave: true,
        ajax:{
                url: '{{url("analisa_pembelian/list_data")}}',
                data:function(d){
                    d.nama = $("#nama").val();
                    d.referensi = $("#referensi").val();
                    d.status = $("#status").val();
                }
             },
        columns: [
            {data: 'no', name: 'no', width:"2%", class: 'text-right'},
            {data: 'id_obat', name: 'id_obat'},
            {data: 'harga_beli_ppn', name: 'harga_beli_ppn', class: 'text-right'},
            {data: 'terjual', name: 'terjual', class: 'text-center'},
            {data: 'satuan', name: 'satuan', class: 'text-center'},
            {data: 'produsen', name: 'produsen'},
            {data: 'kebutuhan', name: 'kebutuhan', class: 'text-center'},
            {data: 'stok_obat', name: 'stok_obat', class: 'text-center'},
            {data: 'status', name: 'status'},
            {data: 'saran', name: 'saran', searchable: false},
            {data: 'sedang_dipesan', name: 'sedang_dipesan', orderable: false, searchable: false , class: 'text-center'},
            {data: 'action', name: 'id', orderable: false, searchable: false, class: 'text-center'}
        ],
        rowCallback: function( row, data, iDisplayIndex ) {
            var api = this.api();
            var info = api.page.info();
            var page = info.page;
            var length = info.length;
            var index = (page * length + (iDisplayIndex +1));
            $('td:eq(0)', row).html(index);
            $('td:eq(2)', row).html('Rp. ' + data.harga_beli_ppn);
        },
        stateSaveCallback: function(settings,data) {
            localStorage.setItem( 'DataTables_' + settings.sInstance, JSON.stringify(data) )
        },
        stateLoadCallback: function(settings) {
            return JSON.parse( localStorage.getItem( 'DataTables_' + settings.sInstance ) )
        },
        drawCallback: function( settings ) {
            var api = this.api();
            getAmountProduct();
            spinner.hide();
        }
    });

    $(document).ready(function(){
        spinner.show();
        $("#searching_form").submit(function(e){
            e.preventDefault();
            tb_analisa_pembelian.fnDraw(false);
        });

        $('#tgl_awal, #tgl_akhir').datepicker({
            autoclose:true,
            format:"yyyy-mm-dd",
            forceParse: false
        });

        $('.input_select').select2({});

        $(window).on('beforeunload', function() {
            $.ajax({
                type: 'GET',
                url: '{{url("analisa_pembelian/clear_cache")}}',
                error: function(xhr, status, error) {
                    alert(error);
                }
            });
        });
    })

    function cari_info() {
        $.ajax({
            type: "GET",
            url: '{{url("home/cari_info")}}',
            async:true,
            data: {
                _token:token,
                tgl_awal : $("#tgl_awal").val(),
                tgl_akhir : $("#tgl_akhir").val(),
            },
            beforeSend: function(data){
                // replace dengan fungsi loading
                spinner.show();
            },
            success:  function(data){
                $("#data_rekap_global").html(data);
                spinner.hide();
            },
            complete: function(data){
                
            },
            error: function(data) {
                swal("Error!", "Ajax occured.", "error");
            }
        });
    }

    function add_keranjang(id_obat){
        //alert(jumlah);
        $.ajax({
            type: "POST",
            url: '{{url("defecta/add_keranjang")}}',
            async:true,
            data: {
                _token: "{{csrf_token()}}",
                id_obat: id_obat
            },
            beforeSend: function(data){
                // on_load();
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

    function add_keranjang_transfer(id_obat){
        //alert(jumlah);
        $.ajax({
            type: "POST",
            url: '{{url("defecta/add_keranjang_transfer")}}',
            async:true,
            data: {
                _token: "{{csrf_token()}}",
                id_obat: id_obat
            },
            beforeSend: function(data){
                // on_load();
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

    function submit_valid(id){
        if($(".validated_form").valid()) {
            data = {};
            $("#form-edit").find("input[name], select").each(function (index, node) {
                data[node.name] = node.value;
                
            });

            $.ajax({
                type:"PUT",
                url : '{{url("defecta/")}}/'+id,
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
                        show_error("Gagal menyimpan data ini! Terdapat data kosong atau suplier belum disetting");
                        return false;
                    }
                },
                complete: function(data){
                    // replace dengan fungsi mematikan loading
                    tb_analisa_pembelian.fnDraw(false);
                },
                error: function(data) {
                    show_error("error ajax occured!");
                }

            })
        } else {
            return false;
        }
    }

    /*
        =======================================================================================
        For     : Untuk menghitung jumlah obat pada tiap kategori
        Author  : Anang B.P.
        Date    : 26/06/2023
        =======================================================================================
    */

    function getAmountProduct(){
        month = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
        var referensi = $("#referensi").val();
        var nama = $("#nama").val();
        var status = $("#status").val();
        var date_now = new Date(Date.now());
        var tgl_akhir = new Date(Date.now());
        tgl_akhir.setDate(tgl_akhir.getDate());
        if(referensi == 1){
            var tgl_awal = new Date(date_now.getFullYear(), date_now.getMonth(), 1);
        } else if(referensi == 2){
            var tgl_awal = new Date(date_now.getFullYear(), date_now.getMonth()-2, 1);
        } else if(referensi == 3){
            var tgl_awal = new Date(date_now.getFullYear(), date_now.getMonth()-5, 1);
        } else if(referensi == 4){
            var tgl_awal = new Date(date_now.getFullYear(), date_now.getMonth()-11, 1);
        } else {
            var tgl_awal = new Date(Date.now());
        }
        var range_awal = tgl_awal.getDate()+' '+month[tgl_awal.getMonth()]+' '+tgl_awal.getFullYear();
        var range_akhir = tgl_akhir.getDate()+' '+month[tgl_akhir.getMonth()]+' '+tgl_akhir.getFullYear();
        $('#sum_analisa_pembelian').find('.sum_analisa_pembelian_date').html('Hasil Analisis Pembelian dari '+range_awal+' s.d. '+range_akhir);

        $.ajax({
            type:"GET",
            url : '{{url("analisa_pembelian/sum_kategori")}}',
            data: {
                referensi: referensi,
                nama: nama,
                status: status,
            },
            beforeSend: function(data){
                // replace dengan fungsi loading
            },
            success:  function(data){
                $('#sum_analisa_pembelian').find('.overstok').html(': '+data.overstok+' produk');
                $('#sum_analisa_pembelian').find('.understok').html(': '+data.understok+' produk');
                $('#sum_analisa_pembelian').find('.potensial-loss').html(': '+data.potensialLoss+' produk');
                $('#sum_analisa_pembelian').find('.stock-off').html(': '+data.stockOff+' produk');
                $('#sum_analisa_pembelian').find('.dead-stok').html(': '+data.deadStok+' produk');
                $('#sum_analisa_pembelian').find('.stok-on-hand').html(': '+data.stokOnHand+' produk');
            },
            complete: function(data){
                // replace dengan fungsi mematikan loading
            },
            error: function(data) {
                show_error("error ajax occured!");
            }

        })
    }

    function export_data(){
        window.open("{{ url('analisa_pembelian/export_analisa_pembelian') }}"+ "?referensi="+$('#referensi').val(),"_blank");
    }

    function export_data_all(){
        window.open("{{ url('analisa_pembelian/export_analisa_pembelian_all') }}"+ "?referensi="+$('#referensi').val(),"_blank");
    }

</script>
@endsection