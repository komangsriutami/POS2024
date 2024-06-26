
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
        dateISO : {
        	dateISO: true
        },
        digits : {
        	digits: true
        },
        bkpk : {
            // kode kegiatan 
            minlength: 4,
            maxlength: 5
        },
        year:{
            minlength: 4,
            maxlength: 4,
            digits: true  
        }
    });

    $(document).ready(function(){
        $(".validated_form").each(function(idx, obj){
            $(obj).submit(function(e){
                // e.preventDefault();
                
                result = $(obj).valid();
                console.log(result);
                if(result){
                    y = confirm("Apakah Anda yakin untuk menyimpan data ini?");
                    if(y){
                    }else{
                        return false;
                    }
                }else{
                    // form terdapat error
                    show_error("Terdapat input yang tidak sesuai");
                    return false;
                }
            })
        })
    })
