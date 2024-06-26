@extends('layout.app')

@section('title')
<span style="font-size: 12pt;">Akun</span><br>
({{ $akun->kode }} - {{ $akun->nama }})
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Daftar Akun</a></li>
    <li class="breadcrumb-item active" aria-current="page">Detail Akun</li>
</ol>
@endsection

@section('content')
	<style type="text/css">
		.select2 {
		  width: 100%!important; /* overrides computed width, 100px in your demo */
		}
	</style>
	<div class="card card-info card-outline" id="main-box" style="">
  		<div class="card-header">
        	<h3 class="card-title">
          		<i class="fas fa-list"></i>
   				List Detail Akun
        	</h3>
            <div class="card-tools">
                <a href="{{ url('/jurnalumum') }}" class="btn btn-danger" data-toggle="tooltip" data-placement="top" title="Kembali ke daftar data"><i class="fa fa-undo"></i> Kembali</a>
            </div>
      	</div>
        <div class="card-body">
			<table  id="tb_jurnal" class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nomor</th>
                        <th>Kontak</th>
                        <th>Debit (IDR)</th>
                        <th>Kredit (IDR)</th>
                        <th>Saldo (IDR)</th>
                        <th>Status</th>
                        <th>Action</th>
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
    var tb_jurnal = $('#tb_jurnal').dataTable( {
            processing: true,
            serverSide: true,
            stateSave: true,
            ajax:{
                    url: '{{url("jurnalumum/list_detail_jurnalumum/".$id)}}',
                    data:function(d){
                            //d.id_apotek   = $('#id_apotek').val();
                            //d.id_apoteker         = $('#id_apoteker').val();
                         }
                 },
            columns: [
                {data: 'tgl_transaksi', name: 'tgl_transaksi'},
                {data: 'no_transaksi', name: 'no_transaksi'},
                {data: 'nama_akun', name: 'nama_akun'},
                {data: 'debit', name: 'debit', class:'text-right'},
                {data: 'kredit', name: 'kredit', class:'text-right'},
                {data: 'saldo', name: 'saldo', class:'text-right'},
                {data: 'is_tutup_buku', name: 'is_tutup_buku', class:'text-center'},
                {data: 'action', name: 'id',orderable: false, searchable: false, class:'text-center'}
            ],
            rowCallback: function( row, data, iDisplayIndex ) {
                /*var api = this.api();
                var info = api.page.info();
                var page = info.page;
                var length = info.length;
                var index = (page * length + (iDisplayIndex +1));
                $('td:eq(0)', row).html(index);*/
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
    

    function delete_detail(id){
        swal({
            title: "Apakah anda yakin menghapus detail ini?",
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
                url: '{{url("jurnalumum")}}/'+id,
                async:true,
                data: {
                    _token:"{{csrf_token()}}"
                },
                beforeSend: function(data){
                    // replace dengan fungsi loading
                },
                success:  function(data){
                    if(data==1){
                        swal("Deleted!", "Detail Jurnal berhasil dihapus.", "success");
                    }else{
                        
                        swal("Failed!", "Gagal menghapus detail jurnal.", "error");
                    }
                },
                complete: function(data){
                    tb_jurnal.fnDraw(false);
                },
                error: function(data) {
                    swal("Error!", "Ajax occured.", "error");
                }
            });
        });
    }
</script>
@endsection