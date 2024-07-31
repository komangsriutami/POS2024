@extends('layout.app_penjualan')

@section('title')
<?php 
    $nama_apotek_panjang_active = session('nama_apotek_panjang_active');
    $id_apotek_active = session('id_apotek_active');
    $so_status_aktif = session('so_status_aktif');
    $date = date('d-m-Y H:i:s');
?>
SO - Apotek {{ $nama_apotek_panjang_active }}
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
                <small class="text-red"><b> | F2 : untuk fokus ke pencarian | </b></small> <small style="color: #FFEB3B;">Note : Stok yang diinputkan adalah stok fisik yang telah dikurangi jumlah penjualan.</small>
            </h3>
            </small>
            <span class="btn btn-sm btn-default float-right" onClick="export_data()"  data-toggle="modal" data-placement="top" title="Export Data Transfer"><i class="fa fa-file-excel" aria-hidden="true"></i> Export Data</span> 
        </div>
        <div class="card-body">
            <div class="row">
                
                <div class="form-group col-md-2">
                    <select id="so_status_aktif" name="so_status_aktif" class="form-control input_select">
                        <option value="1" {!!( "1" == $so_status_aktif ? 'selected' : '')!!}>Semua Data</option>
                        <option value="2" {!!( "2" == $so_status_aktif ? 'selected' : '')!!}>Data Selisih</option>
                        <option value="3" {!!( "3" == $so_status_aktif ? 'selected' : '')!!}>Stok Akhir 0</option>
                        <option value="4" {!!( "4" == $so_status_aktif ? 'selected' : '')!!}>Belum SO</option>
                    </select>
                </div>
                <div class="col-md-12">
                    {{$dataTable->table(['id' => 'tb_m_stok_harga'])}}
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
            ajax: "{{ url('/stok_opnam_outlet/hayam_wuruk') }}",
            table: "#tb_m_stok_harga",
            display: "bootstrap",
            fields: [
                {label: "Barcode:", name: "barcode"},
                {label: "Nama Obat:", name: "nama"},
                {label: "Harga Beli:", name: "harga_beli"},
                {label: "Harga Jual:", name: "harga_jual"},
                {label: "Update By:", name: "so_by"},
                {label: "Stok Akhir:", name: "stok_akhir_so", orderable: true}
            ]
        });

        /*$('#tb_m_stok_harga').on('click', 'tbody td:not(:first-child)', function (e) {
            editor.inline(this);
        });*/

        $('#tb_m_stok_harga').on( 'click', 'tbody td.editable', function (e) {
            editor.inline( this );
        });

        editor.field('stok_akhir_so').input().on( 'blur', function (e,d) {
            $('#tb_m_stok_harga_filter label input').focus();
        });

        {{$dataTable->generateScripts()}}

        $('#so_status_aktif').change(function(){
            $.ajax({
                url:'{{url("stok_opnam_outlet/hayam_wuruk/set_so_status_aktif")}}',
                type: 'POST',
                data: {
                    _token      : "{{csrf_token()}}",
                    so_status_aktif: $('#so_status_aktif').val()
                },
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success:function(data){
                    //location.reload();
                    window.location.href = "{{ url('stok_opnam_outlet/hayam_wuruk') }}";
                }
            });
        });        
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

    function export_data(){
        window.open("{{ url('stok_opnam_outlet/hayam_wuruk/export') }}","_blank");
    }

    function reload_stok_awal(id){
        swal({
            title: "Apakah anda yakin untuk melakukan reload data stok awal obat ini ?",
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
                url: '{{url("stok_opnam_outlet/hayam_wuruk/reload_stok_awal")}}',
                async:true,
                data: {
                    _token:token,
                    id:id,
                },
                beforeSend: function(data){
                    // replace dengan fungsi loading
                },
                success:  function(data){
                    if(data==1){
                        swal("Success!", "Reload stok awal berhasil!", "success");
                    }else{
                        swal("Gagal!", "Reload stok awal gagal!", "error");
                    }
                },
                complete: function(data){
                    tb_data_obat.fnDraw(false);
                },
                error: function(data) {
                    swal("Error!", "Ajax occured.", "error");
                }
            });
        });
    }
</script>
@endsection
