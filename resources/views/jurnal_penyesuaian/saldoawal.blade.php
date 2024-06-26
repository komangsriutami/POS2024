@extends('layout.app')

@section('title')
Jurnal Umum
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Jurnal Umum</a></li>
    <li class="breadcrumb-item active" aria-current="page">Saldo Awal</li>
</ol>
@endsection

@section('content')
<style type="text/css">
    .kategoriselected {
        color: black;
        background-color: #dfdfdf;
    }
</style>
{!! Form::model($jurnal_umum, ['route' => ['jurnalumum.saldoawalset'], 'class'=>'validated_form', 'id'=>'form_stokawal']) !!}    
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info card-outline">
                <div class="card-body">
                    <div class="row">

                        <div class="col-sm-4">
                            <div class="card">
                                <div class="card-header">
                                  <h3 class="card-title">Kategori Akun</h3>
                                </div>
                                <div class="card-body p-0">
                                  <ul class="nav nav-pills flex-column">
                                    @if($kategoriakun->count())
                                        @foreach($kategoriakun as $k)
                                        <li class="nav-item" onclick="getAkun(this,'{{Crypt::encrypt($k->id)}}')" >
                                          <a href="#" class="nav-link">
                                            {{$k->nama}}
                                          </a>
                                        </li>
                                        @endforeach
                                    @endif
                                  </ul>
                                </div>
                                <!-- /.card-body -->
                              </div>
                        </div>

                        <div class="col-sm-8" id="divakun">
                            <div class="text-muted"><i class="fa fa-info-circle"></i> Silahan pilih kategori akun</div>
                        </div>

                    </div>
                </div>
                <div class="border-top">
                    <div class="card-body">
                        <a href="{{ url('/jurnalumum') }}" class="btn btn-danger" data-toggle="tooltip" data-placement="top" title="Kembali ke daftar data"><i class="fa fa-undo"></i> Kembali</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
{!! Form::close() !!}
@endsection

@section('script')
<script type="text/javascript">
    var kat;
    var kate;

    function getAkun(e,i)
    {
        $.ajax({
            type:"GET",
            url : "{{url('jurnalumum/getakun/')}}/"+i,
            dataType : "json",
            beforeSend: function(data){
                $(".nav-item").removeClass('kategoriselected');
            },
            success:  function(data){
                if(data.status == 1){
                    $("#divakun").html(data.form);
                    $(e).addClass('kategoriselected');
                    kat = i;
                    kate = e;
                }
            },
            complete: function(data){
                
            },
            error: function(data) {
                
            }
        });
    }


    $(document).ready(function(){
        $("#form_stokawal").submit(function(e){
            e.preventDefault();

            if($(this).valid() == true){
                $.ajax({
                    type:"POST",
                    url : this.action,
                    dataType : "json",
                    data : $(this).serialize(),
                    beforeSend: function(data){
                        // replace dengan fungsi loading
                    },
                    success:  function(data){
                        if(data.status ==1){
                            show_info("Berhasil menyimpan stok awal");
                        } else {
                            show_error("Gagal menyimpan stok awal. "+data.error);
                        }
                    },
                    complete: function(data){
                        getAkun(kate,kat);
                    },
                    error: function(data) {
                        show_error("Terjadi kesalahan. Gagal mengirim data");
                    }

                });
            }
            
        });
    })
</script>
@endsection

