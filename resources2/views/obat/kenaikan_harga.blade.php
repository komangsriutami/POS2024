@extends('layout.app')

@section('title')
Kenaikan Harga Obat
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Data Obat</a></li>
    <li class="breadcrumb-item"><a href="#">Kenaikan Harga Obat</a></li>
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
   				List Data Obat
        	</h3>
      	</div>
        <div class="card-body">
        	<div class="row">
                <div class="col-lg-6 form-group">
                    <label>Nama Obat</label>
                    <input type="text" id="nama_obat" name="nama_obat" class="form-control" placeholder="Masukan nama obat">
                </div>
                <div class="col-lg-6 form-group">
                    <label>% Kenaikan</label>
                    <input type="text" id="persen_kenaikan" name="persen_kenaikan" class="form-control" placeholder="Masukan berapa persen kenaikan yang dicari">
                </div>
            </div>
            <hr style="border: 1px solid #004d40; padding: 0px; margin-top: 0px; margin-bottom: 10px;">
        	<div class="form-group col-md-12" id="show_kenaikan_harga"></div>
        	<hr style="border: 1px solid #004d40; padding: 0px; margin-top: 0px; margin-bottom: 10px;">
        	<small><b>Note : </b><br>
	        	UJ = Untung jual<br>
	        	HB = Harga beli<br>
	        	HB+ppn = Harga beli + ppn<br>
	        	HJ = Harga jual sekarang<br>
	        	% = persentase kenaikan harga<br>
	        	Treshold : batas kenaikan harga yang dihitung dari persentase kenaikan harga<br>
	        	Hit HJ : perkiraan harga jual seharusnya
        	</small>
        </div>
  	</div>
@endsection

@section('script')
<script type="text/javascript">
	var token = '{{csrf_token()}}';

	$(document).ready(function(){
		post_this();

		$("#persen_kenaikan").keypress(function(event){
            if (event.which == '10' || event.which == '13') {
                post_this();
            }
        });

        $("#nama_obat").keypress(function(event){
            if (event.which == '10' || event.which == '13') {
                post_this();
            }
        });
	})

	function post_this(page_num, d)
	{
		var persen_kenaikan = $("#persen_kenaikan").val();
		var nama_obat = $("#nama_obat").val();
		$.ajax({
			type: "POST",
			url: '{{url("obat/list_kenaikan_harga")}}',
			async:true,
			data: {
				_token:token,
				page_num:page_num,
				sort : d,
				persen_kenaikan: persen_kenaikan,
				nama_obat:nama_obat
			},
			beforeSend: function(data){
				// replace dengan fungsi loading
			},
			success:  function(data){
				$("#show_kenaikan_harga").html(data)
			},
			complete: function(data){
				// replace dengan fungsi mematikan loading
				//tb_quis.fnDraw(false);
			},
			error: function(data) {
				alert("error ajax occured!");
				// done_load();
			}
		});
	}

	function setting_harga_jual(id, harga_beli, harga_beli_ppn, id_asal){
      	$.ajax({
          	type: "POST",
	        url: '{{url("obat/setting_harga_jual")}}',
	        async:true,
	        data: {
	            _token		: "{{csrf_token()}}",
	            id:id,
	            harga_beli:harga_beli,
	            harga_beli_ppn:harga_beli_ppn,
	            id_asal:id_asal
	        },
	        beforeSend: function(data){
	          	// on_load();
		        $('#modal-xl').find('.modal-xls').find(".modal-content").find(".modal-header").attr("class","modal-header bg-light-blue");
		        $("#modal-xl .modal-title").html("Setting Harga Jual - Obat");
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

  	function submit_valid(id){
		status = $(".validated_form").valid();

		if(status) {
			data = {};
			$("#form-edit-harga").find("input[name], select").each(function (index, node) {
		        data[node.name] = node.value;
		    });

		    data['_token'] =  "{{ csrf_token() }}";


			$.ajax({
				type:"PUT",
				url : '{{url("obat/update_harga")}}/'+id,
				dataType : "json",
				data : data,
				beforeSend: function(data){
					// replace dengan fungsi loading
				},
				success:  function(data){
					if(data.status ==1){
						show_info("Data obat berhasil disimpan...");
						post_this();
						$('#modal-xl').modal('hide');
					}else{
						show_error("Gagal menyimpan data ini!");
						return false;
					}
				},
				complete: function(data){
					// replace dengan fungsi mematikan loading
					post_this();
				},
				error: function(data) {
					show_error("error ajax occured!");
				}

			})
		}
	}
</script>
@endsection