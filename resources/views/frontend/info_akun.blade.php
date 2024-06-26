@extends('rekammedis.frontend.layout_frontend.app')
@section('content')

    <!-- Breadcrumb -->
    <div class="breadcrumb-bar">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-12 col-12">
                    <nav aria-label="breadcrumb" class="page-breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ url('/homepage') }}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Info Akun</li>
                        </ol>
                    </nav>
                    <h2 class="breadcrumb-title">Info Akun</h2>
                </div>
            </div>
        </div>
    </div>
    <!-- /Breadcrumb -->

    <!-- Page Content -->
    <div class="content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-md-12 col-lg-4 col-xl-3 theiaStickySidebar">

                    <!-- Search Filter -->
                    @if(session('id') != null)
                    <div class="card search-filter">
                        <div class="card-header">
                            <div class="d-flex justify-content-center">
                                <i class="fa fa-user"></i>
                            </div>
                            <div class="d-flex justify-content-center">
                                <h4>{{ session("nama") }}</h4>
                            </div>
                        </div>
                        <div class="card-header">
                            <h4><span class="fa fa-file"></span> Info Medis</h4>
                        </div>
                        <div class="card-body">
                            <button class="btn btn-outline-primary btn-block">Rekam Medis</button>
                            <button class="btn btn-outline-primary btn-block">Jadwal Konsultasi</button>
                            <a href="{{ url('/search_dokter') }}" class="btn btn-outline-primary btn-block">Cari Dokter</a>
                        </div>
                        <hr>
                        <div class="card-header">
                            <h4><span class="fa fa-cogs"></span> Pengaturan Akun</h4>
                        </div>
                        <div class="card-body">
                            <button class="btn btn-outline-primary btn-block active">Info Akun</button>
                            <a href="{{ url('logout_pasien_post') }}" class="btn btn-outline-danger btn-block">Logout</a>
                        </div>
                    </div>
                    @endif

                </div>

                <div class="col-md-12 col-lg-8 col-xl-9">
                    <div class="card">
                        <div class="card-body">
                            <div class="doctor-widget">
                                <div class="doc-info-left">
                                    <div class="doctor-img">
                                        <i class="fa fa-user fa-5x"></i>
                                        <h4 class="doc-name text-success">Master</h4>
                                    </div>
                                    <div class="doc-info-cont">
                                        <h4 class="doc-name"><a href="doctor-profile">{{ session('nama') }}</a></h4>
                                        <p class="doc-speciality">{{ session('username') }}</p>
                                        <div class="clinic-details">
                                            <p class="doc-location"><i class="fa fa-envelope"></i> {{ session('email') }}</p>
                                        </div>
                                        <div class="clinic-details">
                                            <p class="doc-location"><i class="fa fa-address-book"></i> {{ session('telepon') }}</p>
                                        </div>
                                        <div class="clinic-details">
                                            <p class="doc-location"><i class="fas fa-map-marker-alt"></i> {{ session('alamat') }}</p>
                                        </div>
                                        <div class="clinic-details">
                                            <p class="doc-location"><i class="fa fa-id-card"></i> {{ session('kewarganegaraan') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="doc-info-right">
                                    <div class="clinic-booking">
                                        <button class="btn btn-outline-primary btn-block" onclick="ShowMaster('#profileAnggota',0,1);">View Profile</button>
                                        <button class="btn btn-primary btn-block" onclick="ShowMaster('#profileAnggota',1,1);">Ubah Data Diri</button>
                                        <button class="btn btn-primary btn-block" onclick="ShowMaster('#profileLoginAnggota',0,1);">Ubah Data Login</button>
                                        <button class="btn btn-primary btn-block" onclick="ShowMaster('#profileAnggota',2,1);">Daftar Anggota Keluarga</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="detailMaster" style="display:none;">
                        <div class="card">
                            <div class="card-body">
                            <div id="profileLoginAnggota" style="display:none;">
                                    <div class="login-header">
                                        <div class="d-flex bd-highlight mb-3">
                                            <h3 class="mr-auto p-2 bd-highlight">Daftar Anggota Keluarga</h3>
                                            <button class="btn btn-secondary" onclick="HideMaster();">Sembunyikan</button>
                                        </div>
                                    </div>
                                    <!-- Anggota Form -->
                                    <form class="formMasterLogin" action="{{route('edit_pasien_login_post')}}" method="POST">
                                    @error('username')
                                        <center><h4 class ="text-danger">{{ $message }}</h4></center>
                                    @enderror
                                    {{csrf_field()}}
                                        <div class="regis_1">
                                            <!-- <div class="form-group form-focus"> -->
                                                <input type="hidden" name="id_reference" value=1 class="form-control floating">
                                                <input type="hidden" name="id" value={{session("id")}} class="form-control floating">
                                                <input type="hidden" name="id_show_login" id="id_show_login" value="{{ old('id_show_login') }}" class="form-control floating">
                                            <!-- </div> -->
                                            <div class="form-group form-focus">
                                                <input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control floating">
                                                <label class="focus-label">Email</label>
                                            </div>
                                            <div class="form-group form-focus">
                                                <input type="password" name="password" value="{{ old('password') }}" class="form-control floating">
                                                <label class="focus-label">Create Password</label>
                                            </div>
                                            <div class="form-group form-focus">
                                                <input type="password" name="password_confirm" value="{{ old('password_confirm') }}" class="form-control floating">
                                                <label class="focus-label">Confirm Password</label>
                                            </div>
                                        </div>
                                        <!-- <div class="row login-btn"> -->
                                            <div class="col-sm-12">
                                                <button class="btn btn-primary btn-block btn-lg" type="submit">Sign Up</button>
                                            </div>
                                        <!-- </div> -->
                                    </form>
                                </div>


                                <div id="profileAnggota" style="display:none;">
                                    <div class="login-header">
                                        <div class="d-flex bd-highlight mb-3">
                                            <h3 class="mr-auto p-2 bd-highlight">Daftar Anggota Keluarga</h3>
                                            <button class="btn btn-secondary" onclick="HideMaster();">Sembunyikan</button>
                                        </div>
                                    </div>
                                    <!-- Anggota Form -->
                                    <form class="formMaster" action="{{route('regis_pasien_anggota_post')}}" method="POST">
                                    @error('username')
                                        <center><h4 class ="text-danger">{{ $message }}</h4></center>
                                    @enderror
                                    {{csrf_field()}}
                                        <div class="regis_1">
                                            <!-- <div class="form-group form-focus"> -->
                                                <input type="hidden" name="id_reference" value=1 class="form-control floating">
                                                <input type="hidden" name="id" value={{session("id")}} class="form-control floating">
                                                <input type="hidden" name="id_show_anggota" id="id_show_anggota" value="{{ old('id_show_anggota') }}" class="form-control floating">
                                            <!-- </div> -->
                                            <div class="form-group form-focus">
                                                <input type="text" name="nama" value="{{ old('nama') }}" class="form-control floating">
                                                <label class="focus-label">Name</label>
                                            </div>
                                            <div class="form-group form-focus">
                                                <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir') }}" class="form-control floating">
                                                <label class="focus-label">Tempat Lahir</label>
                                            </div>
                                            <div class="form-group form-focus">
                                                <input class="form-control floating" name="tgl_lahir" value="{{ old('tgl_lahir') }}" id="datepicker" name="date" type="text">
                                                <label class="focus-label">Tanggal Lahir</label>
                                            </div>
                                            <div class="form-group form-focus">
                                                <input type="text" name="alamat" value="{{ old('alamat') }}" class="form-control floating">
                                                <label class="focus-label">Alamat</label>
                                            </div>
                                            <div class="form-group form-focus">
                                                <input type="text" name="telepon" value="{{ old('telepon') }}" class="form-control floating">
                                                <label class="focus-label">No Telpon</label>
                                            </div>
                                            <div class="form-group form-focus">
                                                <select class="custom-select floating" name="id_kewarganegaraan" value="{{ old('id_kewarganegaraan') }}">
                                                    <option @if( old('id_kewarganegaraan') == "" ) selected @endif disabled value="">Citizenship</option>
                                                    @if(count($kewarganegaraans) > 0)
                                                        @foreach($kewarganegaraans as $item)
                                                            <option @if( old('id_kewarganegaraan') == $item->id ) selected @endif value="{!! $item->id !!}">{!! $item->kewarganegaraan !!}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                        <div class="regis_2" style="display: none;">
                                            <div class="form-group form-focus">
                                                <select class="form-group custom-select" name="id_jenis_kelamin" value="{{ old('id_jenis_kelamin') }}">
                                                    <option @if( old('id_jenis_kelamin') == "" ) selected @endif disabled value="">Jenis Kelamin</option>
                                                    @if(count($jeniskelamins) > 0)
                                                        @foreach($jeniskelamins as $item)
                                                            <option @if( old('id_jenis_kelamin') == $item->id ) selected @endif value="{!! $item->id !!}">{!! $item->jenis_kelamin !!}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="form-group form-focus">
                                                <select class="form-group custom-select" name="id_golongan_darah" value="{{ old('id_golongan_darah') }}">
                                                    <option @if( old('id_golongan_darah') == "" ) selected @endif disabled value="">Golongan Darah</option>
                                                    @if(count($golongandarahs) > 0)
                                                        @foreach($golongandarahs as $item)
                                                            <option @if( old('id_golongan_darah') == $item->id ) selected @endif value="{!! $item->id !!}">{!! $item->golongan_darah !!}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="form-group form-focus">
                                                <select class="form-group custom-select" name="is_pernah_berobat" value="{{ old('is_pernah_berobat') }}">
                                                    <option @if( old('is_pernah_berobat') == "" ) selected @endif disabled value="">Pernah Berobat</option>
                                                    <option @if( old('is_pernah_berobat') == "1" ) selected @endif value="1">Ya</option>
                                                    <option @if( old('is_pernah_berobat') == "2" ) selected @endif value="2">Tidak</option>
                                                </select>
                                            </div>
                                            <div class="form-group form-focus">
                                                <select id="bpjs" class="form-group custom-select" name="is_bpjs" value="{{ old('is_bpjs') }}" onchange="ChangeBpjs();">
                                                    <option @if( old('is_bpjs') == "" ) selected @endif disabled value="">Memiliki BPJS</option>
                                                    <option @if( old('is_bpjs') == "1" ) selected @endif value="1">Ya</option>
                                                    <option @if( old('is_bpjs') == "2" ) selected @endif value="2">Tidak</option>
                                                </select>
                                            </div>
                                            <div id="noBpjs" class="form-group form-focus" style="display: none;">
                                                <input type="text" name="no_bpjs" class="form-control floating">
                                                <label class="focus-label">No BPJS</label>
                                            </div>
                                        </div>
                                        <div class="login-btn-next">
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <a class="btn btn-outline-primary btn-block btn-lg prev-btn" onclick="OnClickNext(-1)" disabled="disabled">Prev</a>
                                                </div>
                                                <div class="col-sm-6">
                                                    <a class="btn btn-primary btn-block btn-lg" onclick="OnClickNext(1)">Next</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row login-btn" style="display:none;">
                                            <div class="col-sm-6">
                                                <a class="btn btn-outline-primary btn-block btn-lg prev-btn-login" onclick="OnClickNext(-1)">Prev</a>
                                            </div>
                                            <div class="col-sm-6">
                                                <button class="btn btn-primary btn-block btn-lg submitMaster" type="submit" style="display:none;">Sign Up</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- /Register Form -->
                    </div>
                    <div class="row">
                        @foreach($anggotas as $item)
                        <div class="col-sm-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="doctor-widget">
                                        <div class="doc-info-left">
                                            <div class="anggota-img">
                                                <i class="fa fa-user fa-5x"></i>
                                                <h4 class="doc-name text-success">Anggota Keluarga</h4>

                                            </div>
                                            <div class="doc-info-cont">
                                                <div class="clinic-booking">
                                                    <button class="btn btn-outline-primary btn-block" onclick="ShowAnggota(0,1, {{ json_encode($item) }})">View Profile</button>
                                                    <button class="btn btn-primary btn-block" onclick="ShowAnggota(1,1, {{ json_encode($item) }})" >Ubah Data Diri</button>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="card-body" style="padding-top:0px">
                                    <h4 class="doc-name">{{ $item->nama }}</h4>
                                    <div class="clinic-details">
                                        <p class="doc-location"><i class="fa fa-address-book"></i> {{ $item->telepon }}</p>
                                    </div>
                                    <div class="clinic-details">
                                        <p class="doc-location"><i class="fas fa-map-marker-alt"></i> {{ $item->alamat }}</p>
                                    </div>
                                    <div class="clinic-details">
                                        <p class="doc-location"><i class="fa fa-id-card"></i> {{ $item->kewarganegaraan }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    <!-- <div> -->
                </div>

                <div id="detailProfilAnggota" style="display:none;">
                    <div class="card">
                        <div class="card-body">
                            <div id="detailAnggota" style="display:none;">
                                <div class="login-header">
                                    <div class="d-flex bd-highlight mb-3">
                                        <h3 class="mr-auto p-2 bd-highlight">Daftar Anggota Keluarga</h3>
                                        <button class="btn btn-secondary" onclick="HideAnggota();">Sembunyikan</button>
                                    </div>
                                </div>
                                <!-- Anggota Form -->
                                <form class="formAnggota" action="{{route('regis_pasien_anggota_post')}}" method="POST">
                                @error('username')
                                    <center><h4 class ="text-danger">{{ $message }}</h4></center>
                                @enderror
                                {{csrf_field()}}
                                    <div class="regis_1">
                                        <!-- <div class="form-group form-focus"> -->
                                            <input type="hidden" name="id_reference" value=1 class="form-control floating">
                                            <input type="hidden" name="id" value={{session("id")}} class="form-control floating">
                                            <input type="hidden" name="id_show_anggota" id="id_show_anggota" value="{{ old('id_show_master') }}" class="form-control floating">
                                        <!-- </div> -->
                                        <div class="form-group form-focus">
                                            <input type="text" name="nama" value="{{ old('nama') }}" class="form-control floating">
                                            <label class="focus-label">Name</label>
                                        </div>
                                        <div class="form-group form-focus">
                                            <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir') }}" class="form-control floating">
                                            <label class="focus-label">Tempat Lahir</label>
                                        </div>
                                        <div class="form-group form-focus">
                                            <input class="form-control floating" name="tgl_lahir" value="{{ old('tgl_lahir') }}" id="datepicker" name="date" type="text">
                                            <label class="focus-label">Tanggal Lahir</label>
                                        </div>
                                        <div class="form-group form-focus">
                                            <input type="text" name="alamat" value="{{ old('alamat') }}" class="form-control floating">
                                            <label class="focus-label">Alamat</label>
                                        </div>
                                        <div class="form-group form-focus">
                                            <input type="text" name="telepon" value="{{ old('telepon') }}" class="form-control floating">
                                            <label class="focus-label">No Telpon</label>
                                        </div>
                                        <div class="form-group form-focus">
                                            <select class="custom-select floating" name="id_kewarganegaraan" value="{{ old('id_kewarganegaraan') }}">
                                                <option @if( old('id_kewarganegaraan') == "" ) selected @endif disabled value="">Citizenship</option>
                                                @if(count($kewarganegaraans) > 0)
                                                    @foreach($kewarganegaraans as $item)
                                                        <option @if( old('id_kewarganegaraan') == $item->id ) selected @endif value="{!! $item->id !!}">{!! $item->kewarganegaraan !!}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="regis_2" style="display: none;">
                                        <div class="form-group form-focus">
                                            <select class="form-group custom-select" name="id_jenis_kelamin" value="{{ old('id_jenis_kelamin') }}">
                                                <option @if( old('id_jenis_kelamin') == "" ) selected @endif disabled value="">Jenis Kelamin</option>
                                                @if(count($jeniskelamins) > 0)
                                                    @foreach($jeniskelamins as $item)
                                                        <option @if( old('id_jenis_kelamin') == $item->id ) selected @endif value="{!! $item->id !!}">{!! $item->jenis_kelamin !!}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <div class="form-group form-focus">
                                            <select class="form-group custom-select" name="id_golongan_darah" value="{{ old('id_golongan_darah') }}">
                                                <option @if( old('id_golongan_darah') == "" ) selected @endif disabled value="">Golongan Darah</option>
                                                @if(count($golongandarahs) > 0)
                                                    @foreach($golongandarahs as $item)
                                                        <option @if( old('id_golongan_darah') == $item->id ) selected @endif value="{!! $item->id !!}">{!! $item->golongan_darah !!}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <div class="form-group form-focus">
                                            <select class="form-group custom-select" name="is_pernah_berobat" value="{{ old('is_pernah_berobat') }}">
                                                <option @if( old('is_pernah_berobat') == "" ) selected @endif disabled value="">Pernah Berobat</option>
                                                <option @if( old('is_pernah_berobat') == "1" ) selected @endif value="1">Ya</option>
                                                <option @if( old('is_pernah_berobat') == "2" ) selected @endif value="2">Tidak</option>
                                            </select>
                                        </div>
                                        <div class="form-group form-focus">
                                            <select id="bpjs_anggota" class="form-group custom-select" name="is_bpjs" value="{{ old('is_bpjs') }}" onchange="ChangeBpjsAnggota();">
                                                <option @if( old('is_bpjs') == "" ) selected @endif disabled value="">Memiliki BPJS</option>
                                                <option @if( old('is_bpjs') == "1" ) selected @endif value="1">Ya</option>
                                                <option @if( old('is_bpjs') == "2" ) selected @endif value="2">Tidak</option>
                                            </select>
                                        </div>
                                        <div id="noBpjs_anggota" class="form-group form-focus" style="display: none;">
                                            <input type="text" name="no_bpjs" class="form-control floating">
                                            <label class="focus-label">No BPJS</label>
                                        </div>
                                    </div>
                                    <div class="login-btn-next">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <a class="btn btn-outline-primary btn-block btn-lg prev-btn" onclick="OnClickNext(-1)" disabled="disabled">Prev</a>
                                            </div>
                                            <div class="col-sm-6">
                                                <a class="btn btn-primary btn-block btn-lg" onclick="OnClickNext(1)">Next</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row login-btn" style="display:none;">
                                        <div class="col-sm-6">
                                            <a class="btn btn-outline-primary btn-block btn-lg prev-btn-login" onclick="OnClickNext(-1)">Prev</a>
                                        </div>
                                        <div class="col-sm-6">
                                            <button class="btn btn-primary btn-block btn-lg submitMaster" type="submit" style="display:none;">Sign Up</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- /Register Form -->
                </div>

                <div class="d-flex justify-content-center align-items-center">
                    <h5 style="margin-right:10px;"> Menampilkan {{ $anggotas->count()+(($anggotas->currentPage()-1)*3) }} Anggota dari {{$anggotas->total()}} </h5>
                        {{ $anggotas->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
    <!-- /Page Content -->

@section('script')
    <!-- ini diisi jika ada script tambahan yang hanya berlaku pada page ini-->
    <script type="text/javascript">
        var nextIndex = 0;
        var menuMasterChose = 0;
        var menuAnggotaChose = 0;

        function OnClickNext(plus){
            if(nextIndex+plus<0) return;
            nextIndex += plus;
            switch(nextIndex){
                case 0:
                    console.log("first");

                    $(".prev-btn").attr("disabled", true);
                    $(".login-btn").hide();
                    $(".login-btn-next").show();
                    $(".regis_1").show();
                    $(".regis_2").hide();
                break;
                case 1:
                    $(".prev-btn").attr("disabled", false);
                    $(".regis_1").hide();
                    $(".regis_2").show();
                    $(".login-btn").show();
                    $(".login-btn-next").hide();
                    if(menuMasterChose == 0)$(".submitMaster").hide();
                    else $(".submitMaster").show();
                break;
            }
        }

        function ChangeBpjs(){
            var valBjps = $("#bpjs").val();
            if(valBjps == 1){
                $("#noBpjs").slideDown();
            }else if(valBjps == 2){
                $("#noBpjs").slideUp();
            }
        }

        function ChangeBpjsAnggota(){
            var valBjps = $("#bpjs_anggota").val();
            if(valBjps == 1){
                $("#noBpjs_anggota").slideDown();
            }else if(valBjps == 2){
                $("#noBpjs_anggota").slideUp();
            }
        }

        function HideMaster(){
            $("#detailMaster").slideUp();
        }

        function ShowMaster(choose,menu,isData){
            if(choose == '#profileAnggota'){
                $("#id_show_master").val(menu);
                $("#id_show_login").val("");

                $("#profileLoginAnggota").hide();
                $("#profileAnggota").hide();
                $("#detailMaster").show();
                $(choose).slideDown();

                menuMasterChose = menu;
                $(".formMaster").attr('action','');
                $(".formMaster").attr('method', '');
                if(menu == 0 || menu ==1){
                    if(isData == 1){
                        $(".formMaster").find('input[name="nama"]').val("{{session('nama')}}");
                        $(".formMaster").find("input[name='tempat_lahir']").val("{{session('tempat_lahir')}}");
                        $(".formMaster").find("input[name='tgl_lahir']").val("{{session('tgl_lahir')}}");
                        $(".formMaster").find("input[name='alamat']").val("{{session('alamat')}}");
                        $(".formMaster").find("input[name='telepon']").val("{{session('telepon')}}");
                        $(".formMaster").find("select[name='id_kewarganegaraan']").val("{{session('id_kewarganegaraan')}}");
                        $(".formMaster").find("select[name='id_jenis_kelamin']").val("{{session('id_jenis_kelamin')}}");
                        $(".formMaster").find("select[name='id_golongan_darah']").val("{{session('id_golongan_darah')}}");
                        $(".formMaster").find("select[name='is_pernah_berobat']").val("{{session('is_pernah_berobat')}}");
                        $(".formMaster").find("select[name='is_bpjs']").val("{{session('is_bpjs')}}");
                        $(".formMaster").find("input[name='no_bpjs']").val("{{session('no_bpjs')}}");
                        
                        ChangeBpjs();
                    }
                    $(".formMaster .form-focus").addClass("focused");

                    nextIndex = 0;
                    OnClickNext(0);
                }if(menu == 0){
                    $(".formMaster :input").attr("disabled", true);

                    $(".submitMaster").hide();
                }else if(menu ==1){
                    $(".formMaster :input").attr("disabled", false);

                    $(".formMaster").attr('action',"{{route('edit_pasien_anggota_post')}}");
                    $(".formMaster").attr('method', "POST");

                    $(".submitMaster").show();
                }else{
                    $(".formMaster :input").attr("disabled", false);

                    $(".formMaster").find('input[name="nama"]').val("");
                    $(".formMaster").find("input[name='tempat_lahir']").val("");
                    $(".formMaster").find("input[name='tgl_lahir']").val("");
                    $(".formMaster").find("input[name='alamat']").val("");
                    $(".formMaster").find("input[name='telepon']").val("");
                    $(".formMaster").find("select[name='id_kewarganegaraan']").val("");
                    $(".formMaster").find("select[name='id_jenis_kelamin']").val("");
                    $(".formMaster").find("select[name='id_golongan_darah']").val("");
                    $(".formMaster").find("select[name='is_pernah_berobat']").val("");
                    $(".formMaster").find("select[name='is_bpjs']").val("");
                    $(".formMaster").find("input[name='no_bpjs']").val("");

                    $(".formMaster .form-focus").removeClass("focused");

                    $(".formMaster").attr('action',"{{route('regis_pasien_anggota_post')}}");
                    $(".formMaster").attr('method', "POST");

                    $(".submitMaster").show();

                    ChangeBpjs();
                }
            }else{
                //change login data
                $("#id_show_login").val(menu);
                $("#id_show_master").val("");

                $("#profileLoginAnggota").hide();
                $("#profileAnggota").hide();
                $("#detailMaster").show();
                $(choose).slideDown();

                if(isData == 1){
                    $(".formMasterLogin .form-focus").addClass("focused");
                    $(".formMasterLogin").find('input[name="email"]').val("{{session('email')}}");
                }
            }
        }
    </script>

    <script>
        $(document).ready(function() {
            $("body").css("background-color", "#f0f0f0");
            if($("#id_show_master").val() != ""){
                ShowMaster("#profileAnggota",$("#id_show_master").val(),0);
            }
            if($("#id_show_master").val() != ""){
                ShowAnggota("#detailProfilAnggota",$("#id_show_anggota").val(),0);
            }
            if($("#id_show_master").val() != ""){
                ShowMaster("#profileAnggota",$("#id_show_master").val(),0);
            }if($("#id_show_login").val() != ""){
                ShowMaster('#profileLoginAnggota',0,1)
            }
        });
    </script>

    <script>
        function ShowAnggota(menu,isData,data){
            console.log(data);

            $("#id_show_anggota").val(menu);

            $("#detailProfilAnggota").hide();
            $("#detailAnggota").show();
            $("#detailProfilAnggota").slideDown();

            menuMasterChose = menu;
            $(".formAnggota").attr('action','');
            $(".formAnggota").attr('method', '');

            if(isData == 1){
                $(".formAnggota").find('input[name="id"]').val(data['id']);
                $(".formAnggota").find('input[name="nama"]').val(data['nama']);
                $(".formAnggota").find("input[name='tempat_lahir']").val(data['tempat_lahir']);
                $(".formAnggota").find("input[name='tgl_lahir']").val(data['tgl_lahir']);
                $(".formAnggota").find("input[name='alamat']").val(data['alamat']);
                $(".formAnggota").find("input[name='telepon']").val(data['telepon']);
                $(".formAnggota").find("select[name='id_kewarganegaraan']").val(data['id_kewarganegaraan']);
                $(".formAnggota").find("select[name='id_jenis_kelamin']").val(data['id_jenis_kelamin']);
                $(".formAnggota").find("select[name='id_golongan_darah']").val(data['id_golongan_darah']);
                $(".formAnggota").find("select[name='is_pernah_berobat']").val(data['is_pernah_berobat']);
                $(".formAnggota").find("select[name='is_bpjs']").val(data['is_bpjs']);
                $(".formAnggota").find("input[name='no_bpjs']").val(data['no_bpjs']);

                ChangeBpjsAnggota();
            }
            $(".formAnggota .form-focus").addClass("focused");

            nextIndex = 0;
            OnClickNext(0);
            if(menu == 0){
                $(".formAnggota :input").attr("disabled", true);

                $(".submitAnggota").hide();
            }else if(menu ==1){
                $(".formAnggota :input").attr("disabled", false);

                $(".formAnggota").attr('action',"{{route('edit_pasien_anggota_post')}}");
                $(".formAnggota").attr('method', "POST");

                $(".submitAnggota").show();
            }
        }

        function HideAnggota(){
            $("#detailProfilAnggota").slideUp();
        }
    </script>
@endsection
