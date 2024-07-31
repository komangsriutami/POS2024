@extends('layout.app')

@section('title')
Menu
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Rbac</a></li>
    <li class="breadcrumb-item"><a href="#">Menu</a></li>
    <li class="breadcrumb-item active" aria-current="page">Index</li>
</ol>
@endsection

@section('content')
	<style type="text/css">
		.select2 {
		  width: 100%!important; /* overrides computed width, 100px in your demo */
		}

		ol {
			padding-left: 25px;
		}
		
		/*ol.sortable,ol.sortable ol {
			list-style-type: none;
		}*/
		
		.sortable li div {
			border: 1px solid #d4d4d4;
			-webkit-border-radius: 3px;
			-moz-border-radius: 3px;
			border-radius: 3px;
			cursor: move;
			margin: 2px;
			padding: 3px;
			color:#333;
			background-color:#d4d4d4;
			border-color: #D4D4D4 #D4D4D4 #BCBCBC;
		}
	</style>

	<div class="card mb-12 border-left-primary">
	    <div class="card-body">
	      	<h4><i class="fa fa-info"></i> Informasi</h4>
	      	<p>Geser/tarik kotak menu untuk mangganti posisi menu.</p>
			<a class="btn btn-success w-md m-b-5" href="{{url('menu/create')}}"><i class="fa fa-plus"></i> Tambah Data</a>
	    </div>
	</div>

	
  	<div class="card card-default" id="main-box" style="">
  		<div class="card-header">
        	<h3 class="card-title">
          		<i class="fas fa-list"></i>
   				List Menu
        	</h3>
      	</div>
        <div class="card-body">
			<div class="x_content" id="list-menu"></div>
        </div>
  	</div>
@endsection

@section('script')
<script type="text/javascript">
	var token = '{{csrf_token()}}';
	$(document).ready(function(){
      	show_list_menu();
	})

	function delete_menu(id){
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
				url: '{{url("menu")}}/'+id,
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
						swal("Deleted!", "Data menu berhasil dihapus.", "success");
					}else{
						
						swal("Failed!", "Gagal menghapus data menu.", "error");
					}
				},
				complete: function(data){
					show_list_menu();
				},
				error: function(data) {
					swal("Error!", "Ajax occured.", "error");
				}
			});
		});
	}

	function show_list_menu(){
		$.ajaxSetup({
			async: false
		});
		
		$('.overlay').fadeIn('slow');
		
		$.ajax({
			url: '{{url("menu/list_menu")}}',
			success: function(data){
				$("#list-menu").hide();
				$("#list-menu").html(data);
				$("#list-menu").slideDown('slow');
				
				$('ol.sortable').nestedSortable({
					forcePlaceholderSize: true,
	                handle: 'div',
	                helper: 'clone',
	                items: 'li',
	                opacity: .9,
	                placeholder: 'placeholder',
	                revert: 250,
	                tabSize: 25,
	                tolerance: 'pointer',
	                toleranceElement: '> div',
	                maxLevels: 2,

	                isTree: true,
	                expandOnHover: 700,
	                startCollapsed: true,
	                update : function() {
						update_sorting_menu();
					}
				});
			},
			complete: function(){
				$('.overlay').fadeOut('slow');
			},
	        error:function(XMLHttpRequest){
				/*alert(XMLHttpRequest.responseText);*/
			}
		});
		
	    $.ajaxSetup({
			async: true
		});
	}

	/*
	   	========================================================================================================================================
	   	For     : Update urutan menu pada saat di drag
	   	Author 	: Sri Utami
		Date 	: 22/02/2020
		========================================================================================================================================
	*/
	function update_sorting_menu(){
		$('.overlay').fadeIn('slow');

		serialized = $('ol.sortable').nestedSortable('serialize');

	    $.ajax({
			url:'{{url("menu/update_sorting_menu")}}',
			type: 'POST',
			data: serialized+'&_token='+token,
			beforesend:function(data){
				
			},
			success:function(data){
				if(data=='1'){
					show_info("Berhasil memperbaharui data!");
					show_list_menu();
				}
				else {
					show_error("Gagal memperbaharui data!");
				}
			},
			complete:function(data){
				$('.overlay').fadeOut('slow');
			}
		});
	}
</script>
@endsection