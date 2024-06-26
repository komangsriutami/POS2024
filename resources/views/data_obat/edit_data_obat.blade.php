@extends('layout.app_penjualan')

@section('title')
<?php 
    $date = date('d-m-Y H:i:s');
?>
Edit Data Obat
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item active" aria-current="page">Tanggal : {{ $date }}</li>
</ol>
@endsection

@section('content')
    <div class="card mb-12 border-left-primary card-info">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i>
                List Data Obat
                <small class="text-red"><b> | F2 : untuk fokus ke pencarian.</b></small>
            </h3>
            </small>
            <!-- <span class="btn btn-sm btn-default float-right" onClick="export_data()"  data-toggle="modal" data-placement="top" title="Export Data Transfer"><i class="fa fa-file-excel" aria-hidden="true"></i> Export Data</span>  -->
        </div>
        <div class="card-body">
            <div class="row">
                <div class="form-group col-md-2">
                    
                </div>
                <div class="col-md-12">
                    {{$dataTable->table(['id' => 'tb_m_obat'])}}
                </div>
            </div>
        </div>
    </div>
@endsection


@section('style')
    <style>
        .content-wrapper {
            /* height: 100% !important; */
        }
        .content {
            min-height: calc(100vh - calc(3.5rem + 1px) - calc(3.5rem + 1px));
        }
    </style>
@endsection

@section('script')
<script type="text/javascript">
    var token = '{{csrf_token()}}';
    $(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': '{{csrf_token()}}'
            }
        });

        var editor = new $.fn.dataTable.Editor({
            ajax: "{{ url('/edit_data_obat') }}",
            table: "#tb_m_obat",
            display: "bootstrap",
            fields: [
                {label: "ID:", name: "id"},
                {label: "Barcode:", name: "barcode"},
                {label: "Nama Obat:", name: "nama"},
                {label: "Jenis Obat:", name: "id_golongan_obat"},
                {label: "Update By:", name: "updated_by"},
                {label: "Last Update:", name: "updated_at"}
            ]
        });

        /*$('#tb_m_obat').on('click', 'tbody td:not(:first-child)', function (e) {
            editor.inline(this);
        });*/

        $('#tb_m_obat').on( 'click', 'tbody td.editable', function (e) {
            editor.inline( this );
        });

        editor.field('id_golongan_obat').input().on( 'blur', function (e,d) {
            $('#tb_m_obat_filter label input').focus();
        });

        {{$dataTable->generateScripts()}}     
    })

    $(document).on("keyup", function(e){
        var x = e.keyCode || e.which;
        if (x == 16) {  
            // fungsi shift -> add_row penjualan
        } else if(x==113){
            // fungsi F2 -> buka modal find obat
            $('div.dataTables_filter input').focus();
        } else if(x==115){
            // fungsi F4
        } else if(x==118){
            // fungsi F7
        } else if(x==119){
            // fungsi 
        } else if(x==120){
            // fungsi F9
        } else if(x==121){
            // fungsi F10
        }
    })
</script>
@endsection
