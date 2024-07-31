@extends('layout.app')

@section('title')
Buku Besar
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Laporan</a></li>
    <li class="breadcrumb-item active" aria-current="page">Buku Besar</li>
</ol>
@endsection

@section('content')
	<style type="text/css">
		.select2 {
		  width: 100%!important; /* overrides computed width, 100px in your demo */
		}

		.bg-closing {
			background-color: #cfd8dc;
		}

        tbody.collapse.in {
          display: table-row-group;
        }
	</style>

	<div class="card card-secondary card-outline">
	   <div class="card-body">
        	<form role="form" id="searching_form">
                <!-- text input -->
                <div class="row">
                    <div class="form-group  col-md-2">
                        <label>Dari Tanggal</label>
                        <input type="text" name="tgl_awal"  id="tgl_awal" class="datepicker form-control" value="{{ $date_now }}" autocomplete="off">
                    </div>
                    <div class="form-group  col-md-3">
                        <label>Sampai Tanggal</label>
                        <div class="input-group">
	                        <input type="text" name="tgl_akhir" id="tgl_akhir" class="datepicker form-control" value="{{ $date_now }}" autocomplete="off">
	                        <div class="input-group-append">
				                <button type="submit" class="btn btn-primary" id="datatable_filter"><i class="fa fa-search"></i> Cari</button>
	                        	<span class="btn bg-olive" onClick="export_data()"  data-toggle="modal" data-placement="top" title="Export Data Transfer"><i class="fa fa-file-excel" aria-hidden="true"></i> Export</span> 
				            </div>
				        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">   
                        <div id="data_buku_besar"></div>  
                    </div>
                </div>
            </form>
        </div>
	</div>
@endsection

@section('script')
<script type="text/javascript">
	var token = '{{csrf_token()}}';

	$(document).ready(function(){
        $("#searching_form").submit(function(e){
            e.preventDefault();
            cari_info();
        });

        cari_info();
	})

    function cari_info() {
        $.ajax({
            type: "GET",
            url: '{{url("laporan/cari_info_buku_besar")}}',
            async:true,
            data: {
                _token:token,
                tgl_awal : $('#tgl_awal').val(),
                tgl_akhir : $('#tgl_akhir').val(),
            },
            beforeSend: function(data){
                // replace dengan fungsi loading
            },
            success:  function(data){
                $("#data_buku_besar").html(data);
            },
            complete: function(data){
                
            },
            error: function(data) {
                swal("Error!", "Ajax occured.", "error");
            }
        });
    }

	function export_data(){
        window.open("{{ url('laporan/export_neraca') }}"+ "?tgl_awal="+$('#tgl_awal').val()+"&tgl_akhir="+$('#tgl_akhir').val(),"_blank");
    }
</script>
</script>
@endsection