@extends('frontend.v1._app')
@section('content')
    <button onclick="topFunction()" id="tombol-atas" title="Go to top" class="fa fa-arrow-up"></button>
    <!-- Home Banner -->
    <section class="section section-search" style="margin-top: 50px;" id="menu-homepage">
        <div class="container-fluid">
            <div class="banner-wrapper">
                <div class="banner-header text-center">
                    <h1>Software Akuntansi Online Terintegrasi untuk Kembangkan Bisnis Tanpa Batas</h1>
                    <p>Membantu pembukuan & operasional bisnis perusahaan menjadi lebih mudah & efisien.</p>
                </div>

                <!-- Search -->
                <div class="d-flex search-box justify-content-center">
                    <div class="row">
                        <div class="col-lg-12" style="margin-top:10px;">
                            <a href="coba-gratis" class="btn book-btn1 px-3 py-2 mt-3" tabindex="0">Coba Gratis</a>
                            <a href="hubungi-kami" class="btn book-btn1 px-3 py-2 mt-3" tabindex="0">Hubungi Kami</a>
                        </div>
                    </div>
                </div>
                <!-- /Search -->

            </div>
        </div>
    </section>
    <!-- /Home Banner -->

    <section class="section home-tile-section" id="menu-home">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-9 m-auto">
                    <div class="section-header text-center">
                        <h2>Ini Alasan Memakai ApoteKeren!</h2>
                    </div>
                    <div class="row">
                        <div class="col-lg-6 mb-3">
                            <div class="card text-center doctor-book-card">
                                <img src="assets_frontend/img/doctors/doctor-07.jpg" alt="" class="img-fluid">
                                <div class="doctor-book-card-content tile-card-content-1">
                                    <div>
                                        <h3 class="card-title mb-0">Kunjungi Dokter</h3>
                                        <a href="{{ url('/search_dokter') }}" class="btn book-btn1 px-3 py-2 mt-3" tabindex="0">Daftar Online</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-3">
                            <div class="card text-center doctor-book-card">
                                <img src="assets_frontend/img/img-pharmacy1.jpg" alt="" class="img-fluid">
                                <div class="doctor-book-card-content tile-card-content-1">
                                    <div>
                                        <h3 class="card-title mb-0">Temukan Apotek</h3>
                                        <a href="pharmacy-search" class="btn book-btn1 px-3 py-2 mt-3" tabindex="0">Cari sekarang</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Clinic and Specialities -->
    <section class="section section-specialities" id="menu-special">
        <div class="container-fluid">
            <div class="section-header text-center">
                <h2 style="color: #fff">Layanan</h2>
                <p class="sub-title" style="color: #fff">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor
                    incididunt ut labore et dolore magna aliqua.</p>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-4">
                    <p class="text-center">adasdas</p>
                </div>
                <div class="col-md-4">
                    <p class="text-center">adasdas</p>
                </div>
                <div class="col-md-4">
                    <p class="text-center">adasdas</p>
                </div>
            </div>
        </div>
    </section>
    <!-- Clinic and Specialities -->

    <!-- Blog Section -->
    <section class="section section-blogs" id="menu-news">
        <div class="container-fluid">

            <!-- Section Header -->
            <div class="section-header text-center">
                <h2>Pilihan Tepat Untuk Website dan Bisnismu!​</h2>
                <p class="sub-title">kami akan membagikan berita pembaruan sekarang</p>
            </div>
            <!-- /Section Header -->

            <div class="row blog-grid-row">
                <p class="text-center">adasdas</p>
            </div>
        </div>
    </section>
    <!-- /Blog Section -->

    <!-- Popular Section Dokter -->
    <section class="section section-specialities-new" id="menu-ourdoctor">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-4">
                    <div class="section-header ">
                        <h2>Klien Kami</h2>
                        <p>Lorem Ipsum is simply dummy text </p>
                    </div>
                    <div class="about-content">
                        <p>It is a long established fact that a reader will be distracted by the readable content of a page
                            when looking at its layout. The point of using Lorem Ipsum.</p>
                        <a href="{{ url('/search_dokter') }}">Lihat Selengkapnya..</a>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="doctor-slider slider">
                        @foreach($dokters as $item)
                        <!-- Doctor Widget -->
                        <div class="profile-widget">
                            <div class="doc-img">
                                <a href="doctor-profile">
                                    <img class="img-fluid" alt="User Image" onerror="this.onerror=null; this.src='assets_frontend/img/doctors/doctor-01.jpg'" src="userfiles/dokter/{{ $item->img }}">
                                    <!-- "assets_frontend/img/doctors/doctor-01.jpg" -->
                                </a>
                                <a href="javascript:void(0)" class="fav-btn">
                                    <i class="far fa-bookmark"></i>
                                </a>
                            </div>
                            <div class="pro-content">
                                <h3 class="title">
                                    <a href="doctor-profile">{{ $item->nama }}</a>
                                    <i class="fas fa-check-circle verified"></i>
                                </h3>
                                <p class="speciality">{{ $item->spesialis }}</p>
                                <div class="rating">
                                    <i class="fas fa-star filled"></i>
                                    <i class="fas fa-star filled"></i>
                                    <i class="fas fa-star filled"></i>
                                    <i class="fas fa-star filled"></i>
                                    <i class="fas fa-star filled"></i>
                                    <span class="d-inline-block average-rating">(17)</span>
                                </div>
                                <ul class="available-info">
                                    <li>
                                        <i class="fas fa-map-marker-alt"></i> {{ $item->alamat }}
                                    </li>
                                    <li>
                                        <i class="far fa-clock"></i> Available on Fri, 22 Mar
                                    </li>
                                    <li>
                                        <i class="far fa-money-bill-alt"></i> {{ $item->fee }} - $1000
                                        <i class="fas fa-info-circle" data-toggle="tooltip" title="Lorem Ipsum"></i>
                                    </li>
                                </ul>
                                <div class="row row-sm">
                                    <div class="col-6">
                                        <a href="{{ url('/dokter_profile') }}" class="btn view-btn">Profil Dokter</a>
                                    </div>
                                    <div class="col-6">
                                        <a href="booking" class="btn book-btn">Buat Janji</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /Doctor Widget -->
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- /Popular Section Dokter -->
    </div>
    <script>
        //Get the button
        var mybutton = document.getElementById("tombol-atas");
        var rootElement = document.documentElement;

        // When the user scrolls down 20px from the top of the document, show the button
        window.onscroll = function() {scrollFunction()};

        function scrollFunction() {
          if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
            mybutton.style.display = "block";
          } else {
            mybutton.style.display = "none";
          }
        }

        // When the user clicks on the button, scroll to the top of the document
        function topFunction() {
          rootElement.scrollTo({
            top: 0,
            behavior: "smooth"
          })
        }
    </script>
    <style type="text/css">
        html, body {
          scroll-behavior: smooth;
          height: 100%;
        }
        #tombol-atas {
          display: none;
          position: fixed;
          bottom: 20px;
          right: 30px;
          z-index: 99;
          font-size: 18px;
          border: 1px solid #4db6ac;
          outline: none;
          background-color: white;
          color: #4db6ac;
          cursor: pointer;
          padding: 15px;
          border-radius: 5px;
        }
        #tombol-atas:hover{
          background-color: #4db6ac;
          color: white;
        }
    </style>
@endsection

@section('script')
    <!-- ini diisi jika ada script tambahan yang hanya berlaku pada page ini-->

    <link rel="stylesheet" href="https://npmcdn.com/leaflet@1.0.0-rc.2/dist/leaflet.css" />
	<script src="https://npmcdn.com/leaflet@1.0.0-rc.2/dist/leaflet.js"></script>
<script>
navigator.geolocation.getCurrentPosition(function(location) {
    var latlng = new L.LatLng(location.coords.latitude, location.coords.longitude);
    var getLoc = "https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat="+location.coords.latitude+"&lon="+location.coords.longitude;
    $.getJSON(getLoc, function(data) {
        console.log(data);
        if(data.address.village != null){
            $("#lokasi").val(data.address.village);
            $("#cek-lokasi").addClass("focused");
        }else if(data.address.city != null){
            $("#lokasi").val(data.address.city);
            $("#cek-lokasi").addClass("focused");
        }else{
            $("#lokasi").val("");
        }
    });
});
</script>
@endsection
