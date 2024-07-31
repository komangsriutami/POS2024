@extends('layout.app')

@section('title')
Data Obat
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Data Master</a></li>
    <li class="breadcrumb-item"><a href="#">Data Obat</a></li>
    <li class="breadcrumb-item active" aria-current="page">Import</li>
</ol>
@endsection

@section('content')
    <style type="text/css">
        .select2 {
          width: 100%!important; /* overrides computed width, 100px in your demo */
        }
    </style>

    <div class="row">
        <div class="form-group col-12">
            <div class="card card-default card-outline">
                <div class="card-body">

                    <form id="form-import-dataobat" method="POST" action="{{url('data_obat/uploaddata')}}" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>File (*.csv)</label>     
                                    <input class="form-control required" type="file" name="file_data" id="file_data" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 text-center">
                                <hr>
                                <button class="btn btn-sm btn-outline-primary">Upload</button> 
                                <a class="btn btn-sm btn-outline-danger" href="{{ url('data_obat') }}">Cancel</a> 
                            </div>
                        </div>
                        <div class="row"><div class="col-sm-12 text-center"></div></div>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')<script type="text/javascript">
    var token = '{{csrf_token()}}';

    $("#form-import-dataobat").submit(function(event){
        event.preventDefault();

        var file_data = $('#file_data').prop('files')[0]; 

        if(file_data == "") {   
            show_error("Anda belum memilih file");
        } else {
            let form_data = new FormData();                  
            form_data.append('file_data', file_data);
            form_data.append('tglaw', $("#tglaw").val());
            form_data.append('tglak', $("#tglak").val());
            form_data.append('_token', token);

            var c = confirm("Apakah anda yakin ingin import data barang baru? ");
            if(c){
                $.ajax({
                    type:$(this).prop('method'),
                    url : $(this).prop('action'),
                    data : form_data,
                    dataType : "json",
                    processData: false,
                    contentType: false,
                    beforeSend: function(data){
                        // replace dengan fungsi loading
                        spinner.show();
                    },
                    success:  function(data){
                        if(data.status ==1){
                            show_info(data.message);
                            location.href = "{{url('data_obat/import_data')}}";
                        }else{
                            show_error(data.message);
                            return false;
                        }
                    },
                    complete: function(data){
                        // replace dengan fungsi mematikan loading
                        //tb_barang.draw(false);
                    },
                    error: function(data) {
                        show_error("error ajax occured!");
                    }

                });
            }

        }

    });

</script>
@endsection