@extends('layout.app')

@section('title')
DW Service
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">DW</a></li>
    <li class="breadcrumb-item active" aria-current="page">Service</li>
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
			<a class="btn btn-success w-md m-b-5" href="#" onclick="setAwal()"><i class="fa fa-sync"></i> [generate setting awal]</a>
			<div class="col-sm-12">
				<hr>
		        <div class="progress" style="height: 30px;">
		            <div id="progress-bar" class="progress-bar bg-success" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div> 
		        </div>
		        <hr>				
			</div>
	    </div>
	</div>

	<div class="card card-info card-outline" id="main-box" style="">
  		<div class="card-header">
        	<h3 class="card-title">
          		<i class="fas fa-list"></i>
   				List Data
        	</h3>
      	</div>
        <div class="card-body">
			<table  id="tb_migrasi" class="table table-bordered table-striped table-hover">
		    	<thead>
			        <tr>
			            <th width="5%">No.</th>
			            <th width="10%">Tahun</th>
			            <th width="10%">Bulan</th>
			            <th width="20%">Jumlah</th>
			            <th width="20%">Jumlah Migrasi</th>
			            <th width="15%">Status</th>
			            <th width="20%">Action</th>
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

	let progressBar = $('#progress-bar');
	let width = 0;
	const totalItems = 5;//$("#jum_obat").val(); // Total items, e.g., 12000
	const itemsPerBatch = 1; // Number of items to process per batch
	const totalLoops = Math.ceil(totalItems / itemsPerBatch); // Total number of loops required
	const increment = 100 / totalLoops;
    let currentLoop = 0;
    var overlay = document.getElementById('overlay-wrapper-persediaan-id');
    var overlaybody = document.getElementById('overlay-persediaan-id');

	var token = '{{csrf_token()}}';
	var tb_migrasi = $('#tb_migrasi').dataTable( {
			processing: true,
	        serverSide: true,
	        stateSave: true,
	        ajax:{
			        url: '{{url("migrasi/list_data")}}',
			        data:function(d){
				         }
			     },
	        columns: [
	            {data: 'no', name: 'no',width:"2%"},
	            {data: 'tahun', name: 'tahun'},
	            {data: 'bulan', name: 'bulan'},
	            {data: 'jumlah', name: 'jumlah'},
	            {data: 'jumlah_migrasi', name: 'jumlah_migrasi'},
	            {data: 'status', name: 'status'},
	            {data: 'action', name: 'id',orderable: true, searchable: true}
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


	function generateSetting() {
		//alert(totalLoops);
		swal({
		  	title: "Apakah anda akan melakukan generate data ?",
		  	text: 'Proses ini akan memerlukan waktu yang cukup lama, mohon bersabar sampai proses selesai.',
		  	type: "warning",
		  	showCancelButton: true,
		  	confirmButtonColor: "#DD6B55",
		  	confirmButtonText: "Ya",
		  	cancelButtonText: "Tidak",
		  	closeOnConfirm: true
		},
		function(){
			overlay.classList.add('overlay-wrapper');
            overlaybody.classList.add('overlay');
			getData();
		});
	}

	function setAwal() {
		//alert(totalLoops);
		swal({
		  	title: "Apakah anda akan melakukan generate data ?",
		  	text: 'Proses ini akan memerlukan waktu yang cukup lama, mohon bersabar sampai proses selesai.',
		  	type: "warning",
		  	showCancelButton: true,
		  	confirmButtonColor: "#DD6B55",
		  	confirmButtonText: "Ya",
		  	cancelButtonText: "Tidak",
		  	closeOnConfirm: true
		},
		function(){
			overlay.classList.add('overlay-wrapper');
            overlaybody.classList.add('overlay');
			getData();
		});
	}

	function setAwal() {
		$.ajax({
			type: "POST",
			url: '{{url("migrasi/generate")}}',
			async:true,
			data: {
				_token:token,
                iterasi: currentLoop,
                iterasi_last:totalLoops
			},
			beforeSend: function(data){
				// replace dengan fungsi loading
			},
			success: function(data) {
				updateProgressBar();
	            if (currentLoop < totalLoops) {
	                setAwal(); // Call getData again until totalLoops is reached
	            } else {
	            	//stop();
	            	overlay.classList.remove('overlay-wrapper');
	            	overlaybody.classList.remove('overlay');
	            }
		    },
			complete: function(data){
				
			},
			error: function(data) {
				swal("Error!", "Ajax occured.", "error");
			}
		});
	}

	function getData() {
		$.ajax({
			type: "POST",
			url: '{{url("migrasi/generate")}}',
			async:true,
			data: {
				_token:token,
                iterasi: currentLoop,
                iterasi_last:totalLoops
			},
			beforeSend: function(data){
				// replace dengan fungsi loading
			},
			success: function(data) {
				updateProgressBar();
	            if (currentLoop < totalLoops) {
	                getData(); // Call getData again until totalLoops is reached
	            } else {
	            	//stop();
	            	overlay.classList.remove('overlay-wrapper');
	            	overlaybody.classList.remove('overlay');
	            }
		    },
			complete: function(data){
				
			},
			error: function(data) {
				swal("Error!", "Ajax occured.", "error");
			}
		});
	}

	function updateProgressBar() {
        currentLoop++;
        width += increment;
        progressBar.css('width', width + '%');
        progressBar.attr('aria-valuenow', width);
        progressBar.text(Math.round(width) + '%');
    }

	function mulai_migrasi(id, text) {
        swal({
            title: "Yakin ingin "+text+"? ",
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
                url: '{{url("migrasi/init")}}',
                async:true,
                data: {
                    _token:token,
                    id:id,
                },
                beforeSend: function(data){
                    // replace dengan fungsi loading
                    // spinner.show();
                },
                success:  function(data){
                    // spinner.hide();
                    // if(data==1){
                    //     swal("Success!", "Data berhasil disimpan.", "success");
                    // }else{
                    //     swal("Alert!", "Data gagal diisimpan.", "error");
                    // }
                },
                complete: function(data){
                    
                },
                error: function(data) {
                    swal("Error!", "Ajax occured.", "error");
                }
            });
        });
    }
</script>
@endsection