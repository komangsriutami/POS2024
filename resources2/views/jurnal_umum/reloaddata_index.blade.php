<div class="col-xs-12 text-center">
	<h4>Reload Data Apotek {{$apotek->nama_panjang}}</h4>
	<b><i class="fa fa-calendar"></i>&nbsp;{{Date('l, d F Y',strtotime($tgl_nota))}}</b><hr>	
</div>


<div class="col-xs-12">

	<div id="div_form" class="container-fluid d-flex flex-column">
				
		<div class="row">

			@if(!is_null($listreload))
				@foreach($listreload as $d)

					<div class="{{$d->col}}">
				    	<div class="callout">
				    		<div class="row">
				    			<div class="col-sm-6">
						        	<b>{{$d->nama_reload}}</b>
						        	<?php /*@if($d->status == 2)
						        		<br><small class="text-orange">code on progress</small>
						        	@elseif($d->status == 1)
						        		<br><small class="text-green">code sudah</small>
						        	@else
						        		<br><small class="text-danger">code belum</small>
						        	@endif*/ ?>			        	
						        </div>
						        <div class="col-sm-6 text-right">
						        	<div onclick="reloadprocess('{{Crypt::encrypt($d->id)}}','{{rtrim($d->nama_reload)}}')" class="btn btn-primary btn-sm text-white"><i class="fa fa-sync"></i>&nbsp;Reload</div>
						        </div>
						        
						        	<?php $detail = $d->detail($apotek->id,$tgl_nota)->get(); ?>
						        	@if(!empty($detail->count()))
						        		@foreach($detail as $dt)

						        		@if($d->col == "col-sm-12")
						        		<div class="col-sm-6">
						        		@else	
						        		<div class="col-sm-12">
						        		@endif
						        			<i class="fa fa-info-circle"></i> &nbsp;Status {{$dt->jenis}} :

								        	@if($dt->status == 1)
								        		<b class="text-success">Sukses</b>
								        	@elseif($dt->status == 2)
								        		<b class="text-danger">Gagal</b> &nbsp;
								        	@elseif($dt->status == 3)
								        		<b class="text-warning">Tidak direload</b> &nbsp;
								        	@endif

								        	@if($dt->keterangan != "")
								        		<br><small>keterangan : {{$dt->keterangan}}</small>
								        	@endif

								        	<br>
								        	<small>
									        	<i class="text-muted">
									        		<i class="fa fa-clock"></i> &nbsp;{{Date('d-m-Y H:i',strtotime($dt->updated_at))}} &nbsp; 
									        		<i class="fa fa-user"></i>&nbsp; {{$dt->updated_oleh->nama}}
									        	</i>
								        	</small>
								        	@if($d->col == "col-sm-12")<hr>@endif
								        </div>
								        @endforeach
						        	@else
						        		<i class="fa fa-info-circle"></i> &nbsp;Status :
						        		<b class="text-muted">Belum Reload</b> &nbsp;
						        	@endif
						        
				    		</div>
					    </div>
				    </div>

				@endforeach	
			@endif



		    



		</div>

	</div>

	<!-- DIV PROGRESS -->
	<div id="div_progress" style="display: none;">
		<div class="row">
		    <div class="col-sm-12">
		    	<div class="callout callout-warning row">
			        <div class="col-sm-12">
			        	<b>Reload Data</b><br>
			        	<i><i class="fas fa-spinner fa-spin"></i>&nbsp; Mohon menunggu. Sistem sedang melakukan import jurnal ....</i>
			        </div>
			    </div>
		    </div>
		</div>
	</div>
</div>

<style type="text/css">
	#tb_pertanyaan_filter{
		display: none;
	}
</style>
<script type="text/javascript">
	$(document).ready(function(){

	});


	function reloadprocess(i,namareload)
	{
		swal({
            title: "Apakah anda yakin ingin "+namareload+"?",
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
                url: '{{url("jurnalumum/reloadprocess")}}',
                async:true,
                dataType:"json",
                data: {
                    _token:token,
                    i:i
                },
                beforeSend: function(data){
                    // replace dengan fungsi loading
                    $("#div_form").fadeOut("slow");
                    $("#div_progress").fadeIn("slow");
                },
                success:  function(data){
                    if(data.status!=2){
                        swal("Berhasil !", namareload+" berhasil.", "success");
                    }else{                        
                        swal(data.errorMessages, "error");
                    }
                },
                complete: function(data){
                    tb_jurnal.fnDraw(false);
                    reloaddata();
                },
                error: function(data) {
                    swal("Error!", "Ajax occured.", "error");
                }
            });
        });
	}

</script>