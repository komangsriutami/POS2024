<div class="col-xs-12">
	<div id="div_form">
		{!! Form::model(new App\JurnalUmum, ['route' => ['jurnalumum.import_jurnal_from_excel'], 'class'=>'validated_form', 'files'=> true, 'id' => "form-import"]) !!}
		
		<!-- STEP 1 - DOWNLOAD TEMPLATE -->
		<div class="row">
		    <div class="col-sm-12">
		    	<div class="callout callout-info row">
			    	<div class="col-sm-1"><span style="font-size: 120%;" class="badge badge-info right">1</span></div>
			        <div class="col-sm-11">
			        	<b>Download XLS template</b><br>
			        	<i style="">
			        		*) Template berisikan header sesuai dengan data yang dibutuhkan untuk mengimport jurnal.
			        	</i><br><br>
			        	<a href="{{url('jurnalumum/gettemplate')}}" class="btn btn-block btn-info btn-sm text-white"><i class="fa fa-download"></i>&nbsp;Download template *.xls</a>
			        </div>
			    </div>
		    </div>
		</div>


		<!-- STEP 2 - COPY / INSERT DATA -->
		<div class="row">
		    <div class="col-sm-12">
		    	<div class="callout callout-info row">
			    	<div class="col-sm-1"><span style="font-size: 120%;" class="badge badge-info right">2</span></div>
			        <div class="col-sm-11">
			        	<b>Copy/Insert Data Anda</b><br>
			        	<i style="">
			        		*) Salin/masukan data anda ke dalam template tanpa menghapus header.<br>
			        		*) Data dengan Header bertanda (*) wajib diisi.<br>
			        		<span class="text-red">*) Jangan menghapus sheet 1 atau sheet glossarium.</span>
			        	</i>
			        </div>
			    </div>
		    </div>
		</div>


		<!-- STEP 3 - UPLOAD XLS -->
		<div class="row">
		    <div class="col-sm-12">
		    	<div class="callout callout-info row">
			    	<div class="col-sm-1"><span style="font-size: 120%;" class="badge badge-info right">3</span></div>
			        <div class="col-sm-11">
			        	<b>Upload XLS Template</b><br>
			        	<i style="">
			        		*) Setelah selesai menyalin data, silahkan upload file template tersebut.<br>
			        		*) File anda harus dalam format file excel (*.xls atau *.xlsx)
			        	</i><br><br>
			        	<div class="form-group">
					    	<input type="file" class="form-control" id="import_file" name="import_file" required />
					    </div>
			        </div>
			    </div>
		    </div>
		</div>

	   	

	   	<!-- TOMBOL UPLOAD -->
	   	<div class="row">
		    <div class="col-sm-12">
		    	<div class="row">
			    	<div class="col-md-12 text-right" style="margin-bottom: 6px;">
						<input type="submit" class="btn btn-primary btn-submit" value="Import File">
					</div>
			    </div>
		    </div>
		</div>

		{!! Form::close() !!}
	</div>

	<!-- DIV PROGRESS -->
	<div id="div_progress" style="display: none;">
		<div class="row">
		    <div class="col-sm-12">
		    	<div class="callout callout-warning row">
			        <div class="col-sm-12">
			        	<b>Importing Data</b><br>
			        	<i><i class="fas fa-spinner fa-spin"></i>&nbsp; Mohon menunggu. Sistem sedang melakukan import jurnal ....</i>
			        </div>
			    </div>
		    </div>
		</div>
	</div>

	<!-- HASIL IMPORT -->
	<div id="div_hasil" style="display: none;">
		<div class="row">
		    <div class="col-sm-12">
		    	<div class="alert row div_alert_hasil">
			        <div class="col-sm-12">
			        	<b id="status"></b><br>
			        </div>
			    </div>
			    <div class="callout callout-info row">
			        <div class="col-sm-12">
			        	<i id="ket_status"></i>
			        </div>
			    </div>
		    </div>
		</div>
		<div class="row"><div class="col-sm-12 text-right"><div data-dismiss="modal" class="btn btn-danger btn-sm"><i class="fa fa-undo"></i> Kembali</div></div></div>
	</div>



</div>

<style type="text/css">
	#tb_pertanyaan_filter{
		display: none;
	}
</style>
<script type="text/javascript">
	$(document).ready(function(){	
		$("#form-import").submit(function(e){
			e.preventDefault();

			const formData = new FormData();
			formData.append('import_file', $("#import_file").prop('files')[0]);

			$.each($(this).serializeArray(),function(key,input){
				formData.append(input.name,input.value);
			});

			$.ajax({
				type:"POST",
				url : this.action,
				dataType : "json",
				data : formData,
				processData: false,
				contentType: false,
				beforeSend: function(data){
					// replace dengan fungsi loading
					$("#div_progress").fadeIn();
					$("#div_form").fadeOut();
				},
				success:  function(data){
					if(data.status ==1){
						show_info("Import data berhasil");
						tb_jurnal.fnDraw(false);							
					} else {
						show_error("Terjadi kesalahan. Import data gagal !");
					}
				},
				complete: function(data){
					if(data.responseJSON.status ==1){
						$(".div_alert_hasil").addClass("alert-success");
						$("#status").html('<i class="fa fa-check"></i> Berhasil Import Data');
					} else {
						$(".div_alert_hasil").addClass("alert-danger");
						$("#status").html('<i class="fa fa-warning"></i> Gagal Import Data');
					}					
					
					$("#ket_status").html(data.responseJSON.keterangan);
					$("#div_progress").fadeOut();
					$("#div_hasil").fadeIn();
				},
				error: function(data) {
					show_error("ajax post error");
				}

			});
		});
	});
</script>