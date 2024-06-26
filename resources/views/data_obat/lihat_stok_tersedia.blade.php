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
                            <td width="27%">Stok</td>
                            <td width="2%"> : </td>
                            <td width="70">
                                {{ $sh->stok_akhir }}
                            </td>
                        </tr>
                    </table>
                </div>
                <hr>
                <table  id="tb_data_obat_xx" class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th width="5%">No.</th>
                            <th width="45%">Stok</th>
                            <th width="50%">HPP</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                    <tr>
                        <td colspan="2" style="text-align: right!important;"><b>Rata-rata</b></td>
                        <td id="avg_total" class="text-right text-bold"></td>
                    </tr>
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

   var tb_data_obat_xx = $('#tb_data_obat_xx').dataTable( {
            processing: true,
            serverSide: true,
            stateSave: true,
            paging: false,
            ajax:{
                    url: '{{url("data_obat/list_lihat_stok_tersedia")}}',
                    data:function(d){
                        d.id_obat = $("#id").val();
                    }
                 },
            columns: [
                {data: 'no', name: 'no', orderable: true, searchable: true, class:'text-center'},
                {data: 'sisa_stok', name: 'sisa_stok', class:'text-center'},
                {data: 'hb_ppn', name: 'hb_ppn', class:'text-right'}
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
                var avg = settings['jqXHR']['responseJSON']['avg'];
                $("#avg_total").html(avg);
                $("#avg_total").val(avg);
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

    setTimeout(function(){
        //$('.dataTables_filter input').attr('placeholder','Barcode/nama obat');
        //$('.dataTables_filter input').css('width','400px');
        //$('.dataTables_filter input').css('height','40px');
        
    }, 1);

    $(document).ready(function(){
        /*var barcode = $("#barcode").val();
        $("div.dataTables_filter input").val(barcode);
        $("div.dataTables_filter input").focus();*/

        tb_data_obat_xx.fnDraw();
        //tb_data_obat.fnFilter(barcode);
    })
</script>

