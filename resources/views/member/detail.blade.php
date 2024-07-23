@extends('layout.app')

@section('title')
Data Member
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Data Master</a></li>
    <li class="breadcrumb-item"><a href="#">Data Member</a></li>
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
	    <div class="card-body">
	      	<h4><i class="fa fa-info"></i> Informasi</h4>
	      	<p>Untuk pencarian, isikan kata yang ingin dicari pada kolom seacrh, lalu tekan enter.</p>
	    </div>
	</div>

	<div class="card card-info card-outline" id="main-box" style="">
  		<div class="card-header">
        	<h3 class="card-title">
          		<i class="fas fa-list"></i>
   				List Data Member
        	</h3>
      	</div>
        <div class="card-body">
            <div class="row">
                <div class="col-sm-6">
                    <input type="hidden" name="id" id="id" value="{{ $data->id }}">
                    <table width="100%">
                        <tr>
                            <td width="13%">ID</td>
                            <td width="2%"> : </td>
                            <td width="85">{{ $data->id }}</td>
                        </tr>
                        <tr>
                            <td width="13%">Nama</td>
                            <td width="2%"> : </td>
                            <td width="85">{{ $data->nama }}</td>
                        </tr>
                        <tr>
                            <td width="13%">Tempat / Tanggal Lahir</td>
                            <td width="2%"> : </td>
                            <td width="85">{{ $data->tempat_lahir }}, {{ date('d-m-Y', strtotime($data->tgl_lahir)) }}</td>
                        </tr>
                        <tr>
                            <td width="13%">Total Transaksi </td>
                            <td width="2%"> : </td>
                            <td width="85" id="total_display">Rp 0,-</td>
                        </tr>
                    </table>
                </div>
            </div>
            <hr>
            <?php
                $first = date('Y-m-d');
                $last = date('Y-m-t');
            ?>
            <form role="form" id="searching_form">
                <div class="row">
                    <div class="form-group  col-md-2">
                        <label>Dari Tanggal Transaksi</label>
                        <input type="text" name="tgl_awal"  id="tgl_awal"  value="{{ $first }}" class="datepicker form-control" autocomplete="off">
                    </div>
                    <div class="form-group  col-md-2">
                        <label>Sampai Tanggal Transaksi</label>
                        <input type="text" name="tgl_akhir" id="tgl_akhir" value="{{ $last }}" class="datepicker form-control" autocomplete="off">
                    </div>

                    <div class="col-lg-5">
                        <label>Action</label><br>
                        <button type="submit" class="btn btn-info" id="datatable_filter"><i class="fa fa-search"></i> Cari</button> 
                        <a class="btn bg-danger" type="button" href="{{ url('member')}}" data-toggle="tooltip" data-placement="top" title="List Data Penjualan"><i class="fa fa-home"></i> Kembali</a> 
                    </div>
                </div>
            </form>
            <hr>
			<div class="col-md-6">
                <table id="tb_list_transaksi" class="table table-bordered table-striped table-hover" width="100%">
                    <thead>
                        <tr>
                            <th width="5%">No.</th>
                            <th width="10%">Tanggal</th>
                            <th width="10%">ID.Nota</th>
                            <th width="10%">Total Transaksi</th>
                            <th width="5%">Total Poin</th>
                            <th width="10%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                        <tr class="bg-info">
                            <td colspan="5" class="text-right"><b>TOTAL TRANSAKSI</b></td>
                            <td class="text-right text-bold text-white" id="total_anda"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
  	</div>
@endsection

@section('script')
<script type="text/javascript">
    var token = '{{csrf_token()}}';
    var tb_list_transaksi = $('#tb_list_transaksi').dataTable( {
            processing: true,
            serverSide: true,
            stateSave: true,
            ajax:{
               url: '{{url("member/list_detail")}}',
               data:function(d){
                 d.id_user = '{{ Crypt::encryptString($data->id); }}';
                 d.tgl_awal = $("#tgl_awal").val();
                 d.tgl_akhir = $("#tgl_akhir").val();
               }
            },
           columns: [
               {data: 'no', name: 'no', orderable: true, searchable: true, class:'text-center'},
               {data: 'tgl_nota', name: 'tgl_nota', orderable: true, searchable: true, class:'text-center'},
               {data: 'id_nota', name: 'id_nota'},
               {data: 'total_transaksi', name: 'total_transaksi', class:'text-center'},
               {data: 'poin', name: 'poin', class:'text-center bg-secondary disabled color-palette'},
               {data: 'action', name: 'action', class:'text-right bg-info disabled color-palette'}
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

                // set total pembayaran
                var total = settings['jqXHR']['responseJSON']['total'];
                var total_rp = settings['jqXHR']['responseJSON']['total_format'];
             
                $("#total_anda").html(total_rp);
                $("#total_display").html(total_rp +", -");
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

    $(document).ready(function(){
        $('.input_select').select2({});

        $('#tgl_awal, #tgl_akhir').datepicker({
            autoclose:true,
            format:"yyyy-mm-dd",
            forceParse: false
        });

        $("#searching_form").submit(function(e){
          e.preventDefault();
          tb_list_transaksi.fnDraw(false);
        });
    })
</script>
@endsection