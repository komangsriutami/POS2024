$(document).ready(function($){
    
    $('#jenjang').change(function(){
        $.getJSON("dropdown_jurusan",
        { option: $(this).val() },
        function(data) {
            $('#prodi').empty();
			$('#konsentrasi').empty();			
            $.each(data, function(key, value) {
                $('#prodi').append("<option value='" + value.id +"'>" + value.nama_jurusan + "</option>");
            });

        });
		
		if ($(this).val()==1){
			$('.add-S2').append(
            '<div class="form-group"><div class="col-sm-8 col-sm-offset-2"><div class="form-material" data-toggle="popover" data-placement="right" data-content="Tahun masuk diisi dengan format tahun masehi. Contoh : 2013"><input class="form-control" type="text" id="validation-tahunmasuks2" name="validation-tahunmasuks2" placeholder="Tahun masuk diisi dengan format tahun masehi. Contoh : 2013" ><label for="validation-tahunmasuks2">Tahun Masuk S2 <span class="text-danger">*</span></label></div></div></div>'+
			'<div class="form-group"><div class="col-sm-8 col-sm-offset-2"><div class="form-material" data-toggle="popover" data-placement="right" data-content="Tahun masuk diisi dengan format tahun masehi. Contoh : 2015"><input class="form-control" type="text" id="validation-tahunluluss2" name="validation-tahunluluss2" placeholder="Tahun masuk diisi dengan format tahun masehi. Contoh : 2015" ><label for="validation-tahunluluss2">Tahun Lulus S2 <span class="text-danger">*</span></label></div></div></div>'+
			'<div class="form-group"><div class="col-sm-8 col-sm-offset-2"><div class="form-material" data-toggle="popover" data-placement="right" data-content="Gelar ditulis dengan huruf Kapital. Contoh: M.KOM."><input class="form-control" type="text" id="validation-gelars2" name="validation-gelars2" placeholder="Gelar ditulis dengan huruf Kapital. Contoh: M.KOM." ><label for="validation-gelars2">Gelar S2 <span class="text-danger">*</span></label></div></div></div>'+
			'<div class="form-group"><div class="col-sm-8 col-sm-offset-2"><div class="form-material" data-toggle="popover" data-placement="right" data-content="IPK ditulis seusuai dengan ijazah. Contoh : 3,81 menggunakan KOMA sebagai pemisah"><input class="form-control" type="text" id="validation-ipks2" name="validation-ipks2" placeholder="IPK ditulis seusuai dengan ijazah. Contoh : 3,81" ><label for="validation-ipks2">IPK S2 <span class="text-danger">*</span></label></div></div></div>'+
			'<div class="form-group"><div class="col-sm-8 col-sm-offset-2"><div class="form-material"  data-toggle="popover" data-placement="right" data-content="Nama Universitas ditulis dengan huruf Kapital Contoh: UNIVERSITAS UDAYANA"><input class="form-control" type="text" id="validation-universitass2" name="validation-universitass2" placeholder="Nama Universitas ditulis dengan huruf Kapital Contoh: UNIVERSITAS UDAYANA"><label for="validation-universitass2">Universitas S2 <span class="text-danger">*</span></label></div></div></div>'+
			'<div class="form-group"><div class="col-sm-8 col-sm-offset-2"><div class="form-material" data-toggle="popover" data-placement="right" data-content="Nama Program Studi ditulis dengan huruf Kapital Contoh: TEKNIK INFORMATIKA"><input class="form-control" type="text" id="validation-prodis2" name="validation-prodis2" placeholder="Nama Program Studi ditulis dengan huruf Kapital Contoh: TEKNIK INFORMATIKA" ><label for="validation-prodis2">Prodi S2 <span class="text-danger">*</span></label></div></div></div>'
            );
			
		}else{
			$('.add-S2').empty();
		}
		
		
    });

    $('#prodi').change(function(){
        $.getJSON("dropdown_konsentrasi",
        { option: $(this).val() },
        function(data) {
            $('#konsentrasi').empty(); 
            $.each(data, function(key, value) {
                $('#konsentrasi').append("<option value='" + value.id +"'>" + value.konsentrasi_ps + "</option>");
            });

        });
    });

	
	
});