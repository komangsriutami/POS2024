<style type="text/css">
    .modal-dialog{
        overflow-y: initial !important
    }
    .modal-body-content{
        max-height: calc(100vh - 200px);
        overflow-y: auto;
    }
</style>

<div class="row">
    <div class="col-sm-12">
        <div class="card card-info card-outline">
            <div class="card-body">
                <input type="hidden" name="id" id="id" value="{{ $obat->id }}">
                <input type="hidden" name="id_obat" id="id_obat" value="{{ $obat->id_obat }}">
                <input type="hidden" name="stok_awal_so" id="stok_awal_so" value="{{ $obat->stok_awal_so }}">

                <div class="row">
                    <div class="col-sm-12">
                        <h4 class="text-warning">Terdapat penjualan/selisih dari data yang diupdate, mohon cek stok sekali lagi, hitung dan input stok akhir ulang jika selisih / ada stok yang tidak sesuai.</h4>
                        <h4 class="text-red">Stok akhir yang diinput = stok akhir real setelah dilakukan penjualan.</h4>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box mb-3 bg-info">
                            <div class="info-box-content">
                                <span class="info-box-text">Stok Awal SO</span>
                                <span class="info-box-number">
                                {{ $obat->stok_awal_so }}
                                <small>obat</small>
                                </span>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        <!-- /.info-box -->
                    </div>
                    <!-- /.col -->
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box mb-3 bg-danger">
                            <div class="info-box-content">
                                <span class="info-box-text">Total Penjualan</span>
                                <span class="info-box-number">{{ $obat->total_penjualan_so}}
                                    <small>obat</small>
                                </span>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        <!-- /.info-box -->
                    </div>
                    <!-- /.col -->
                    <!-- fix for small devices only -->
                    <div class="clearfix hidden-md-up"></div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box mb-3 bg-success">
                            <div class="info-box-content">
                                <span class="info-box-text">Stok Akhir SO</span>
                                <span class="info-box-number">{{ $obat->stok_akhir_so}}
                                    <small>obat</small>
                                </span>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        <!-- /.info-box -->
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box mb-3 bg-warning">
                            <div class="info-box-content">
                                <span class="info-box-text">Selisih</span>
                                <span class="info-box-number">{{ $obat->selisih}}
                                    <small>obat</small>
                                </span>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        <!-- /.info-box -->
                    </div>
                </div>
                <div class="col-md-12">
                    <table id="tb_histori_stok_obat" class="table table-bordered table-striped table-hover" width="100%">
                        <thead>
                            <tr>
                                <th width="5%">No.</th>
                                <th width="10%">Tanggal</th>
                                <th width="18%">Jenis Transaksi</th>
                                <th width="14%">Harga</th>
                                <th width="7%">Masuk</th>
                                <th width="7%">Keluar</th>
                                <th width="7%">Stok</th>
                                <th width="7%">No Batch</th>
                                <th width="10%">ED</th>
                                <th width="15%">Oleh</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div><!-- 
            <div class="card-footer">
                <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal"><i class="fa fa-undo"></i> Kembali</button>
            </div> -->
        </div>
     </div>
</div>

<script type="text/javascript">
    var token = '{{csrf_token()}}';
    var tb_histori_stok_obat = $('#tb_histori_stok_obat').dataTable( {
            processing: true,
            serverSide: true,
            stateSave: true,
            ajax:{
                    url: '{{url("stok_opnam/get_data_stok_harga")}}',
                    data:function(d){
                        d.id = $("#id").val(); 
                        d.id_obat = $("#id_obat").val(); //cek_penjualan
                        }
                 },
            columns: [
                {data: 'no', name: 'no', orderable: true, searchable: true, class:'text-center'},
                {data: 'created_at', name: 'created_at', orderable: true, searchable: true, class:'text-center'},
                {data: 'id_jenis_transaksi', name: 'id_jenis_transaksi'},
                {data: 'hb_ppn', name: 'hb_ppn', class:'text-center'},
                {data: 'masuk', name: 'masuk', class:'text-center bg-secondary disabled color-palette'},
                {data: 'keluar', name: 'keluar', class:'text-center bg-secondary disabled color-palette'},
                {data: 'stok_akhir', name: 'stok_akhir', class:'text-center bg-info disabled color-palette'},
                {data: 'batch', name: 'batch', class:'text-center'},
                {data: 'ed', name: 'ed',  class:'text-center'},
                {data: 'created_by', name: 'created_by', class:'text-center'}
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
            }
        });

    $(document).ready(function(){
        var stok_akhir_so = $('#stok_akhir_so').val();        
        $('#stok_akhir_so').focus().val('').val(stok_akhir_so);   

        $("#stok_akhir_so").keypress(function(event){
            if (event.which == '10' || event.which == '13') {
                set_data(this);
            }
        });

        //$("#tb_stok_obat").dataTable();
    })
</script>

