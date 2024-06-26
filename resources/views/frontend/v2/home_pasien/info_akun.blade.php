@extends('frontend.v2.app')

@section('title')
Pengaturan Profile
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
  <li class="breadcrumb-item"><a href="{{ url('/home_pasien') }}">Home</a></li>
  <li class="breadcrumb-item active" aria-current="page">Pengaturan Profile</li>
</ol>
@endsection

@section('content')

<!-- Page Content -->
            <div class="col-md-12 col-lg-8 col-xl-9">
                <div class="card">
                    <div class="card-body">
                        <div class="doctor-widget">
                            <div class="doc-info-left">
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
                                <!-- <div class="clinic-booking"> -->
                                @if(session('activated')==1)
                                    <a class="btn btn-outline-primary btn-block" href="{{url('home_pasien/info_akun/data_diri/')}}{{'/'.$parameter[0]}}">Lihat Profile</a>
                                    <a class="btn btn-primary btn-block" href="{{url('home_pasien/info_akun/data_diri/')}}{{'/'.$parameter[1]}}">Ubah Data Diri</a>
                                @else
                                    <a class="btn btn-primary btn-block" href="{{url('home_pasien/info_akun/data_diri/')}}{{'/'.$parameter[1]}}">Isi Data Diri</a>
                                @endif
                                <!-- <a class="btn btn-primary btn-block" href="{{url('home_pasien/data_login/')}}">Ubah Data Login</a> -->
                                    <a class="btn btn-primary btn-block" href="{{url('home_pasien/info_akun/anggota_keluarga/')}}">Daftar Anggota Keluarga</a>
                                <!-- </div> -->
                            </div>
                        </div>
                    </div>
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
                                            <!-- <div class="clinic-booking"> -->
                                                <a class="btn btn-outline-primary btn-block" href="{{url('home_pasien/info_akun/data_diri/')}}{{'/'.$parameterAnggota[(string)$item->id][0]}}">Lihat Profile</a>
                                                <a class="btn btn-primary btn-block" href="{{url('home_pasien/info_akun/data_diri/')}}{{'/'.$parameterAnggota[(string)$item->id][1]}}" >Ubah Data Diri</a>
                                            <!-- </div> -->
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

            <div class="d-flex justify-content-center align-items-center">
                <h5 style="margin-right:10px;"> Menampilkan {{ $anggotas->count()+(($anggotas->currentPage()-1)*3) }} Anggota dari {{$anggotas->total()}} </h5>
                    {{ $anggotas->links() }}
            </div>
        </div>
    </div>
</div>
<!-- /Page Content -->
@endsection

@section('script')
    <!-- ini diisi jika ada script tambahan yang hanya berlaku pada page ini-->
@endsection
