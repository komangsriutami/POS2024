@extends('layout.app')

@section('title')
Transaksi Penjualan
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Transaksi</a></li>
    <li class="breadcrumb-item"><a href="#">Transaksi Penjualan</a></li>
    <li class="breadcrumb-item active" aria-current="page">Index</li>
</ol>
@endsection

@section('content')
	<style type="text/css">
		.select2 {
		  width: 100%!important; /* overrides computed width, 100px in your demo */
		}
	</style>

	<div class="card card-info card-outline mb-12 border-left-primary">
		<div class="card-header">
        	<h3 class="card-title">
          		<i class="fas fa-list"></i>
   				List Data Penjualan
        	</h3>
        	<div class="card-tools">
					<ul class="nav nav-pills ml-auto">
						<li class="nav-item">
						@if($jum_penjualan_notpaid == 0 && $jum_penjualan_voidnotaproved == 0)
							<span class="btn bg-blue" onClick="closing_kasir()"  data-toggle="modal" data-placement="top" title="Closing Kasir"><i class="fa fa-cog" aria-hidden="true"></i> Closing Kasir</span>
							@elseif($jum_penjualan_notpaid == $jum_penjualan_voidaproved)
							<span class="btn bg-blue" onClick="closing_kasir()"  data-toggle="modal" data-placement="top" title="Closing Kasir"><i class="fa fa-cog" aria-hidden="true"></i> Closing Kasir</span>
							@endif
						</li>
					</ul>
			</div>
      	</div>
	    <div class="card-body">
			<div class="row">
        		<div class="col-sm-12">
        			@if($jum_penjualan_notpaid <= 0)
		           	@else 
		           		<div class="callout callout-warning">
							<p>Tombol closing kasir akan tampil jika semua pembayaran semua transaksi penjualan telah dikonfirmasi (kecuali retur) dan tidak ada pengajuan retur yang belum dikonfirmasi/aprove.</p>
						</div>
		            @endif
		            <div class="callout callout-danger">
						<p class="text-red" style="font-size: 12pt;"><i class="fa fa-exclamation-circle"></i> FITUR RETUR TELAH AKTIF, fitur delete masih dapat digunakan sebelum melakukan pelunasan nota dan penjualan kredit. Sebagai informasi, retur harus mendapatkan konfirmasi dari kepala outlet di hari yang sama.</p>
					</div>
        		</div>
			    <div class="col-md-2">
			        <div class="info-box mb-3 bg-secondary">
			            <span class="info-box-icon bg-info elevation-1">
			                <i class="fas fa-cart-plus"></i>
			            </span>
			            <div class="info-box-content">
			                <span class="info-box-text">Sales</span>
			                <span class="info-box-number">{{ $jum_penjualan }} <small>transaction</small></span>
			            </div>
			        </div>
			    </div>
			    <div class="clearfix hidden-md-up"></div>
			    <div class="col-md-2">
			        <div class="info-box mb-3 bg-secondary">
			            <span class="info-box-icon bg-primary elevation-1">
			                <i class="fas fa-shopping-bag"></i>
			            </span>
			            <div class="info-box-content">
			                <span class="info-box-text">Sales Paid</span>
			                <span class="info-box-number">{{ $jum_penjualan_paid }} <small>transaction</small>
			                </span>
			            </div>
			        </div>
			    </div>
			    <div class="col-md-2">
			        <div class="info-box mb-3 bg-secondary">
			            <span class="info-box-icon bg-warning elevation-1">
			                <i class="fas fa-cart-arrow-down"></i>
			            </span>
			            <div class="info-box-content">
			                <span class="info-box-text">Sales Not Paid</span>
			                <span class="info-box-number">{{ $jum_penjualan_notpaid }} <small>transaction</small>
			                </span>
			            </div>
			        </div>
			    </div>
			    <div class="col-md-2">
			        <div class="info-box mb-3 bg-secondary">
			            <span class="info-box-icon bg-info elevation-1">
			                <i class="fas fa-undo"></i>
			            </span>
			            <div class="info-box-content">
			                <span class="info-box-text">Retur</span>
			                <span class="info-box-number">{{ $jum_penjualan_void }} <small>items</small></span>
			            </div>
			        </div>
			    </div>
			    <div class="clearfix hidden-md-up"></div>
			    <div class="col-md-2">
			        <div class="info-box mb-3 bg-secondary">
			            <span class="info-box-icon bg-primary elevation-1">
			                <i class="fas fa-check-square"></i>
			            </span>
			            <div class="info-box-content">
			                <span class="info-box-text">Retur Aprove Confirm</span>
			                <span class="info-box-number">{{ $jum_penjualan_voidaproved }} <small>items</small>
			                </span>
			            </div>
			        </div>
			    </div>
			    <div class="col-md-2">
			        <div class="info-box mb-3 bg-secondary">x
			            <span class="info-box-icon bg-warning elevation-1">
			                <i class="fas fa-ban"></i>
			            </span>
			            <div class="info-box-content">
			                <span class="info-box-text">Retur Aprove Not Confirm</span>
			                <span class="info-box-number">{{ $jum_penjualan_voidnotaproved }} <small>items</small>
			                </span>
			            </div>
			        </div>
			    </div>
			</div>
	    </div>
	</div>
@endsection

@section('script')
<script type="text/javascript">
	var token = '{{csrf_token()}}';

	$(document).ready(function(){
	})

	function closing_kasir(){
		$.ajax({
		    type: "POST",
		    url: '{{url("penjualan/closing_kasir")}}',
		    async:true,
		    data: {
		    	_token:"{{csrf_token()}}"

		    },
		    beforeSend: function(data){
		      // on_load();
		    $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
		    $("#modal-xl .modal-title").html("Closing Kasir");
		    $('#modal-xl').modal("show");
		    $('#modal-xl').find('.modal-body-content').html('');
		    $("#modal-xl").find(".overlay").fadeIn("200");
		    },
		    success:  function(data){
		      $('#modal-xl').find('.modal-body-content').html(data);
		    },
		    complete: function(data){
		        $("#modal-xl").find(".overlay").fadeOut("200");
		    },
		      error: function(data) {
		        alert("error ajax occured!");
		      }
		});
	}

	function print_closing_kasir_pdf(id) {
		window.open("penjualan/print_closing_kasir_pdf/"+id);
	}

</script>
@endsection