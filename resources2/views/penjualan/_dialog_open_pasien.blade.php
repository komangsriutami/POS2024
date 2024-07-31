<div class="row">
    <div class="col-sm-12">
        <div class="card card-info card-outline">
            <div class="card-body">
                <input type="hidden" name="pasien" id="pasien" value="{{ $pasien }}">
                <table  id="tb_data_pasien" class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th width="5%">No.</th>
                            <th width="25%">Nama</th>
                            <th width="15%">No. Tlp</th>
                            <th width="40%">Alamat</th>
                            <th width="5%">Action</th>
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

    var tb_data_pasien = $('#tb_data_pasien').dataTable( {
            processing: true,
            serverSide: true,
            stateSave: true,
            deferLoading:true,
            scrollX: true,
            ajax:{
                    url: '{{url("penjualan/list_data_pasien")}}',
                    data:function(d){
                        d.pasien = $("#pasien").val();
                    }
                 },
            columns: [
                {data: 'no', name: 'no', orderable: true, searchable: true, class:'text-center'},
                {data: 'nama', name: 'nama', orderable: true, searchable: true},
                {data: 'telepon', name: 'telepon', orderable: true, searchable: true},
                {data: 'alamat', name: 'alamat', orderable: true, searchable: true},
                {data: 'action', name: 'id',orderable: true, searchable: true, class:'text-center'}
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
        $('.dataTables_filter input').attr('placeholder','Nama pasien');
        $('.dataTables_filter input').css('width','400px');
        $('.dataTables_filter input').css('height','40px');
        
    }, 1);

    $(document).ready(function(){
        var pasien = $("#pasien").val();
        $("div.dataTables_filter input").val(pasien);
        $("div.dataTables_filter input").focus();

        tb_data_pasien.fnDraw();
        tb_data_pasien.fnFilter(pasien);
    })
</script>

