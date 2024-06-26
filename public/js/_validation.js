	
	$.validator.addMethod('filesize', function (value, element, param) {
		/*console.log('max file :'+maxsize);
		console.log('size file :'+element.files[0].size);*/
		
		maxsize = param*1024*1024;
		return this.optional(element) || (element.files[0].size <= maxsize)
	}, 'File size must be less than {0} MB');
	
    $.validator.addClassRules({
        required:{
            required: true
        },
        number: {
            number: true
        },
        email: {
            email: true
        },
        url: {
            url: true
        },
        date: {
            date: true
        },
        digits : {
        	digits: true
        },
        nik : {
            minlength: 16,
            maxlength: 16
        },
        npwp : {
            minlength: 15,
            maxlength: 15
        },
        year:{
            minlength: 4,
            maxlength: 4,
            digits: true  
        },
        max_file_size:{
			filesize : 2
        },
    });

    var y;

    $(document).ready(function(){
        /*$('body').on('submit', '.validated_form', function(e){
            result = $(this).valid();
            
            if(result){
                swal({
                    title: "Apakah Anda yakin untuk menyimpan data ini?",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Ya",
                    cancelButtonText: "Tidak",
                    closeOnConfirm: false
                },
                function(){
                });
            }else{
                // form terdapat error
                swal("Error!", "Terdapat input yang tidak sesuai.", "error");
                return false;
            }
        })*/
    })
