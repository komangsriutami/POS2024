@extends('layout.app')

@section('title')
Detail Transaksi
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Data Member</a></li>
    <li class="breadcrumb-item active" aria-current="page">Detail</li>
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
          		<i class="fas fa-star"></i> Data Member 
        	</h3>
        	<div class="card-tools">
        		<a href="{{url('crm_member')}}" class="btn btn-danger btn-sm pull-right" data-toggle="tooltip" data-placement="top" title="Kembali ke daftar data"><i class="fa fa-undo"></i> Kembali</a>
            </div>
      	</div>
        <div class="card-body">
			<div class="row">
				<div class="col-sm-12">
				    <input type="hidden" name="id" id="id" value="{{ $data_->id }}">
				    <h3 class="m-t-0">Detail Member</h3>
				    <div class="row">
					    <div class="col-sm-6">
					    	<table width="100%">
					         	<tr>
						         	<td width="40%">ID</td>
						         	<td width="2%"> : </td>
						         	<td width="58%">{{ $data_->id }}</td>
						      	</tr>
						      	<tr>
						         	<td width="40%">Nama</td>
						         	<td width="2%"> : </td>
						         	<td width="58%">{{ $data_->nama }}</td>
						      	</tr>
						      	<tr>
						         	<td width="40%">Tempat/Tgl Lahir</td>
						         	<td width="2%"> : </td>
						         	<td width="58%">{{ $data_->tempat_lahir }} / {{ $data_->tgl_lahir }}</td>
						      	</tr>
						      	<tr>
						         	<td width="40%">Jumlah Kunjungan</td>
						         	<td width="2%"> : </td>
						         	<td width="58%">{{ $jum_kunjungan }} kunjungan</td>
						      	</tr>
						      	<tr>
						         	<td width="40%">Total Belanja</td>
						         	<td width="2%"> : </td>
						         	<td width="58%">{{ $total_belanja->total }}</td>
						      	</tr>
						      	<tr>
						         	<td width="40%">Poin</td>
						         	<td width="2%"> : </td>
						         	<td width="58%">{{ $poin }} poin</td>
						      	</tr>
						    </table>
					    </div>
					    <div class="col-sm-6">
					    	
						</div>
					</div>
				</div>
        	</div>
        </div>
        <div class="card-footer">
        	<span class="text-info"><i class="fas fa-info"></i>&nbsp;Untuk pencarian, isikan kata yang ingin dicari pada kolom search, lalu tekan enter.</span>
      	</div>
  	</div>
  	<div class="card card-info card-outline" id="main-box" style="">
  		<div class="card-header">
        	<h3 class="card-title">
          		<i class="fas fa-list"></i>
   				Histori Transaksi 
        	</h3>
      	</div>
        <div class="card-body">
        	<div class="row">
	        	<div class="table-responsive">
	               	<div class="table-responsive">
	                  	<table  id="tb_transaksi" class="table table-bordered table-striped table-hover" width="100%">
							<thead>
							    <tr>
					            <td width="5%" class="text-center"><strong>No.</strong></td>
					            <td width="10%" class="text-center"><strong>Tanggal</strong></td>
	                            <td width="45%" class="text-center"><strong>Nama Obat</strong></td>
	                            <td width="10%" class="text-center"><strong>Harga</strong></td>
	                            <td width="10%" class="text-center"><strong>Diskon</strong></td>
	                            <td width="10%" class="text-center"><strong>Jumlah</strong></td>
	                            <td width="10%" class="text-center"><strong>Total</strong></td>
					        </tr>
							</thead>
							<tbody>
							</tbody>
						</table>
	               	</div>
	            </div>
	        </div>
        </div>
    </div>
@endsection

@section('script')
<script type="text/javascript">
	var token = '{{csrf_token()}}';
	var tb_transaksi = $('#tb_transaksi').dataTable( {
			processing: true,
	        serverSide: true,
	        stateSave: true,
	        ajax:{
			        url: '{{url("member/list_data_transaksi")}}',
			        data:function(d){
			        	d.id = $("#id").val();
				    }
			     },
	        columns: [
	            {data: 'no', name: 'no',width:"2%"},
	            {data: 'tgl_transaksi', name: 'tgl_transaksi', class: 'text-center'},
	            {data: 'id_obat', name: 'id_obat'},
	            {data: 'harga_jual', name: 'harga_jual', class: 'text-right'},
	            {data: 'diskon', name: 'diskon', class: 'text-right'},
	            {data: 'jumlah', name: 'jumlah', class: 'text-center'},
	            {data: 'total', name: 'total', class: 'text-right'},
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
	})

	function delete_data(id){
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
                url: '{{url("jurnalumum")}}/'+id,
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
                        swal("Deleted!", "Data jurnal berhasil dihapus.", "success");
                    }else{
                        
                        swal("Failed!", "Gagal menghapus data jurnal.", "error");
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

	function goBack() {
       window.history.back();
   }
</script>
@endsection