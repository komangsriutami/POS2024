<div class="row">
    <div class="col-sm-12">
        <div class="card card-info card-outline">
            <div class="card-body">
                <input type="hidden" name="id" id="id" value="{{ $obat->id }}">
                    <table width="100%">
                        <tr>
                            <td width="27%">Nama Obat</td>
                            <td width="2%"> : </td>
                            <td width="70">{{ $obat->nama }}</td>
                        </tr>
                        <tr>
                            <td width="27%">Harga Beli Backup</td>
                            <td width="2%"> : </td>
                            <td width="70">
                                {{ $sh->harga_beli_back }} |  <span class="label" onClick="gunakan_hb({{ $sh->id }}, {{ $sh->id_obat }}, {{ $sh->harga_beli_back }}, {{ $sh->harga_beli_back }})" data-toggle="tooltip" data-placement="top" title="Gunakan ini" style="font-size:10pt;color:#0097a7;">[Terapkan]</span>
                            </td>
                        </tr>
                    </table>
                    <p class="text-red" style="font-size: 10pt;">Note ::: Harga beli backup adalah harga beli yang disimpan dari sistem sebelumnya. Jika ada data yang tidak sesuai, dapat menggunakan harga tersebut sebagai acuan. Namun mohon dipastikan jika item tersebut merupakan item lama dan memang harga tersebut sudah sesuai.</p>
                </div>
                <hr>
                <table  id="tb_data_obat_x" class="table table-bordered table-striped table-hover">
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
            <div class="card-footer">
                <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal"><i class="fa fa-undo"></i> Kembali</button>
            </div>
        </div>
     </div>
</div>

<script type="text/javascript">
    var token = '{{csrf_token()}}';

    var tb_data_obat_x = $('#tb_data_obat_x').dataTable( {
            processing: true,
            serverSide: true,
            stateSave: true,
            deferLoading:true,
            scrollX: true,
            ajax:{
                    url: '{{url("data_obat/list_edit_harga_beli")}}',
                    data:function(d){
                        d.id_obat = $("#id").val();
                    }
                 },
            columns: [
                {data: 'no', name: 'no', orderable: true, searchable: true, class:'text-center'},
                {data: 'created_at', name: 'created_at', orderable: true, searchable: true, class:'text-center'},
                {data: 'id_jenis_transaksi', name: 'id_jenis_transaksi'},
                {data: 'harga', name: 'harga'},
                {data: 'masuk', name: 'masuk', class:'text-center'},
                {data: 'keluar', name: 'keluar', class:'text-center'},
                {data: 'stok_akhir', name: 'stok_akhir', class:'text-center'},
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

    setTimeout(function(){
        //$('.dataTables_filter input').attr('placeholder','Barcode/nama obat');
        //$('.dataTables_filter input').css('width','400px');
        //$('.dataTables_filter input').css('height','40px');
        
    }, 1);

    $(document).ready(function(){
        /*var barcode = $("#barcode").val();
        $("div.dataTables_filter input").val(barcode);
        $("div.dataTables_filter input").focus();*/

        tb_data_obat_x.fnDraw();
        //tb_data_obat.fnFilter(barcode);
    })
</script>

