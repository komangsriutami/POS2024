<div class="row">
    <div class="col-sm-12">
        <div class="card card-info card-outline">
            <div class="card-body">
                <input type="hidden" name="kode_aset" id="kode_aset" value="{{ $kode_aset }}">
                <table  id="tb_data_aset" class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th width="5%">No.</th>
                            <th width="20%">Jenis Aset</th>
                            <th width="15%">Kode Aset</th>
                            <th width="55%">Nama</th>
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
    var tb_data_aset = $('#tb_data_aset').dataTable( {
            processing: true,
            serverSide: true,
            stateSave: true,
            deferLoading:true,
            scrollX: true,
            ajax:{
                    url: '{{url("manajemen_aset/get_data_aset")}}',
                    data:function(d){
                        d.kode_aset = $("#kode_aset").val();
                    }
                 },
            columns: [
                {data: 'no', name: 'no', orderable: true, searchable: true, class:'text-center'},
                {data: 'id_jenis_aset', name: 'id_jenis_aset', orderable: true, searchable: true, class:'text-center'},
                {data: 'kode_aset', name: 'kode_aset', orderable: true, searchable: true, class:'text-center'},
                {data: 'nama', name: 'nama', orderable: true, searchable: true},
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
        $('.dataTables_filter input').attr('placeholder','Kode aset/nama aset');
        $('.dataTables_filter input').css('width','400px');
        $('.dataTables_filter input').css('height','40px');
        
    }, 1);
    $(document).ready(function(){
        var kode_aset = $("#kode_aset").val();
        $("div.dataTables_filter input").val(kode_aset);
        $("div.dataTables_filter input").focus();
        tb_data_aset.fnDraw();
        tb_data_aset.fnFilter(kode_aset);
    })
</script>