@extends('frontend.v2._app')
@section('content')
<button onclick="topFunction()" id="tombol-atas" title="Go to top" class="fa fa-arrow-up"></button>
<!-- Home Banner -->
<section class="section section-search" style="margin-top: 50px;" id="menu-homepage">
    <div class="container-fluid">
        <div class="banner-wrapper">
            <div class="banner-header text-center">
                <h1>Cari Dokter, Buat Janji Temu</h1>
                <p style="font-size: 15px;">Temukan dokter, apoteker, dan apotek terbaik di kota terdekat anda.</p>
            </div>

            <!-- Search -->
            <form action="{{url('/konsultasi-dokter')}}" method="get">
                <div class="d-flex search-box justify-content-center">
                    <div class="row">
                        <div class="col-xs-12 col-md-10">
                            <div class="row">
                                <div class="col-xs-12 col-md-4 mb-3">
                                    <div class="form-group form-focus">
                                        @if(count($spesialiss) > 0)
                                        <select class="form-control custom-select floating" name="spesialis" required>
                                            <option selected value="-1">Semua Spesialis</option>
                                            @foreach($spesialiss as $item)
                                            <option class="form-control" value="{!! $item->id !!}">{!! $item->spesialis !!}</option>
                                            @endforeach
                                        </select>
                                        <label class="focus-label">
                                            <img src="{{ asset('assets_frontend/img/stethoscope.png') }}" class="mr-1">Pilih Spesialis
                                        </label>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-xs-12 col-md-4 mb-3">
                                    <div class="form-group form-focus">
                                        <input type="text" class="form-control floating" name="lokasi">
                                        <label class="focus-label">
                                            <img src="{{ asset('assets_frontend/img/location.png') }}" class="mr-1">Lokasi
                                        </label>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-md-4 mb-3">
                                    <div class="form-group form-focus">
                                        <input type="text" name="nama" class="form-control floating">
                                        <label class="focus-label">
                                            <img src="{{ asset('assets_frontend/img/search.png') }}" class="mr-1">Nama Tenaga Medis
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-2">
                            <div class="btn-search">
                                <button type="submit" class="btn btn-block">Cari</button>
                            </div>
                        </div>
                    </div>
                    {{-- <div class="col-lg-12" style="margin-top:10px;">
                            <a href="coba-gratis" class="btn book-btn1 px-3 py-2 mt-3" tabindex="0">Coba Gratis</a>
                            <a href="hubungi-kami" class="btn book-btn1 px-3 py-2 mt-3" tabindex="0">Hubungi Kami</a>
                        </div> --}}
                </div>
            </form>
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
                    <h3>Apa yang sedang Anda cari ?</h3>
                </div>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="card text-center doctor-book-card">
                            <img src="{{ asset('assets_frontend/img/section-one-find.png') }}" alt="" class="img-fluid">
                            <div class="doctor-book-card-content tile-card-content-1">
                                <div>
                                    <h3 class="card-title mb-0">Kunjungi Dokter</h3>
                                    <a href="{{ url('/konsultasi-dokter') }}" class="btn book-btn1 px-2 py-2 mt-3" tabindex="0">Daftar Online</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card text-center doctor-book-card">
                            <img src="{{ asset('assets_frontend/img/section-two-find.png') }}" alt="" class="img-fluid">
                            <div class="doctor-book-card-content tile-card-content-1">
                                <div>
                                    <h3 class="card-title mb-0">Temukan Apotek</h3>
                                    <a href="{{ url('/konsultasi-apotek') }}" class="btn book-btn1 px-2 py-2 mt-3" tabindex="0">Cari sekarang</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card text-center doctor-book-card">
                            <img src="{{ asset('assets_frontend/img/section-three-find.png') }}" alt="" class="img-fluid">
                            <div class="doctor-book-card-content tile-card-content-1">
                                <div>
                                    <h3 class="card-title mb-0">Temukan Apoteker</h3>
                                    <a href="{{ url('/konsultasi-apoteker') }}" class="btn book-btn1 px-2 py-2 mt-3" tabindex="0">Cari sekarang</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Popular Section Dokter -->
<section class="section section-doctor" id="menu-ourdoctor">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-4">
                <div class="section-header ">
                    <h2>Praktek Dokter Kami</h2>
                    {{-- <p>Lorem Ipsum is simply dummy text </p> --}}
                </div>
                <div class="about-content">
                    {{-- <p>It is a long established fact that a reader will be distracted by the readable content of a page
                            when looking at its layout. The point of using Lorem Ipsum.</p> --}}
                    <p>Anda dapat menemukan dokter kami yang tepat sesuai kebutuhan dan lokasi terdekat dan langsung dapat melakukan janji temu secara ONLINE langsung via situs kami dan notifikasi ini langsung diterima oleh dokter. Semuanya GRATIS tanpa biaya.</p>
                    <a href="{{ url('/konsultasi-dokter') }}">Lihat Selengkapnya..</a>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="doctor-slider slider">
                    @foreach($dokters as $item)
                    <!-- Doctor Widget -->
                    <div class="profile-widget">
                        <div class="doc-img">
                            <a href="doctor-profile">
                                <img class="img-fluid" alt="User Image" onerror="this.onerror=null; this.src=`{{ asset('assets_frontend/img/doctors/doctor-01.jpg') }}`" src="{{'data:image/png;base64,'.$item->image}}">
                                <!-- "../assets_frontend/img/doctors/doctor-01.jpg" -->
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
                                    <a href="{{ url('/dokter_profile/'.$item->id)}}" class="btn view-btn">Profil Dokter</a>
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

<!-- Popular Section Apoteker -->
<section class="section section-doctor" id="menu-ourapoteker">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8">
                <div class="doctor-slider slider">

                    @foreach($apotekers as $item2)
                    <!-- Apoteker Widget -->
                    <div class="profile-widget">
                        <div class="doc-img">
                            <a href="doctor-profile">
                                <img class="img-fluid" alt="User Image" onerror="this.onerror=null; this.src=`{{ asset('assets_frontend/img/doctors/doctor-01.jpg') }}`" src="{{'data:image/png;base64,'.$item2->image}}">
                            </a>
                            <a href="javascript:void(0)" class="fav-btn">
                                <i class="far fa-bookmark"></i>
                            </a>
                        </div>
                        <div class="pro-content">
                            <h3 class="title">
                                <a href="doctor-profile">{{$item2->nama}}</a>
                                <i class="fas fa-check-circle verified"></i>
                            </h3>
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
                                    <i class="fas fa-map-marker-alt"></i> {{ $item2->alamat }}
                                </li>
                                <li>
                                    <i class="far fa-clock"></i> Available on Fri, 22 Mar
                                </li>
                                <li>
                                    <i class="far fa-money-bill-alt"></i> ${{ $item2->fee }} - $1000
                                    <i class="fas fa-info-circle" data-toggle="tooltip" title="Lorem Ipsum"></i>
                                </li>
                            </ul>
                            <div class="row row-sm">
                                <div class="col-6">
                                    <a href="{{ url('/apoteker_profile/'.$item2->id) }}" class="btn view-btn">Profil Apoteker</a>
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
            <div class="col-lg-4">
                <div class="section-header ">
                    <h2>Praktek Apoteker Kami</h2>
                    {{-- <p>Lorem Ipsum is simply dummy text </p> --}}
                </div>
                <div class="about-content">
                    {{-- <p>It is a long established fact that a reader will be distracted by the readable content of a page
                            when looking at its layout. The point of using Lorem Ipsum.</p> --}}
                    <p>Anda dapat menemukan apoteker kami yang tepat sesuai kebutuhan dan lokasi terdekat dan langsung dapat melakukan janji temu secara ONLINE langsung via situs kami dan notifikasi ini langsung diterima oleh apoteker. Semuanya GRATIS tanpa biaya.</p>
                    <a href="{{ url('/konsultasi-apoteker') }}">Lihat Selengkapnya..</a>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- /Popular Section Apoteker -->

<!-- Clinic and Specialities -->
<section class="section section-specialities" id="menu-special">
    <div class="container-fluid">
        <div class="section-header text-center">
            <h2>Apotek</h2>
            <p class="sub-title">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor
                incididunt ut labore et dolore magna aliqua.</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-9">
                <!-- Slider -->
                <div class="specialities-slider slider">

                    @foreach($apoteks as $item3)
                    <!-- Slider Item -->
                    <a href="{{ url('/apotek_detail/'.$item3->id) }}">
                        <div class="speicality-item text-center">
                            <div class="speicality-img">
                                <img src="{{ asset('assets_frontend/img/apotek.png') }}" class="img-fluid" alt="Speciality">
                                <span><i class="fa fa-circle" aria-hidden="true"></i></span>
                            </div>
                            <p>{{ $item3->nama_panjang }}</p>
                        </div>
                    </a>
                    <!-- /Slider Item -->

                    @endforeach
                </div>
                <!-- /Slider -->

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
            <h2>Berita</h2>
            <p class="sub-title">kami akan membagikan berita pembaruan sekarang</p>
        </div>
        <!-- /Section Header -->

        <div class="row blog-grid-row">
            @if(count($newss) > 0)
            @foreach($newss as $obj)
            <div class="col-md-6 col-lg-3 col-sm-12">
                <!-- Blog Post -->
                <div class="blog grid-blog">
                    <div class="blog-image">
                        <a href="{{ url('/homepage/news/'.$obj->slug) }}">
                            <img class="img-fluid" alt="User Image" src="{{'data:image/png;base64,'.$obj->image}}">
                        </a>
                    </div>
                    <div class="blog-content">
                        <ul class="entry-meta meta-item">
                            <li>
                                <div class="post-author">
                                    <a>
                                        Dibuat Oleh :<span> &nbsp; By Admin</span>
                                    </a>
                                </div>
                            </li>
                            <li>
                                <i class="far fa-clock"></i> {{\Carbon\Carbon::parse($obj->created_at)->format('d M Y')}}
                            </li>
                        </ul>
                        <h3 class="blog-title"><a href="{{ url('/homepage/news/'.$obj->slug) }}">{!! $obj->title !!}</a></h3>
                        <p class="mb-0">{!! $obj->content !!}</p>
                    </div>
                </div>
                <!-- /Blog Post -->
            </div>
            @endforeach
            @else
            <div class="col-sm-12">
                <p>Tidak ditemukan data news</p>
            </div>
            @endif
        </div>
        <div class="view-all text-center">
            <a href="{{ url('/homepage/news') }}" class="btn btn-primary">View All</a>
        </div>
    </div>

    <div class="container-fluid">
        <hr>
        <!-- Section Header -->
        <div class="section-header text-center">
            <h2>Tips</h2>
            <p class="sub-title">kami akan membagikan banyak tips untuk Anda</p>
        </div>
        <!-- /Section Header -->

        <div class="row blog-grid-row">
            @if(count($tipss) > 0)
            @foreach($tipss as $obj)
            <div class="col-md-6 col-lg-3 col-sm-12">
                <!-- Blog Post -->
                <div class="blog grid-blog">
                    <div class="blog-image">
                        <a href="{{ url('/homepage/tips/'.$obj->slug) }}">
                            <img class="img-fluid" alt="User Image" src="{{'data:image/png;base64,'.$obj->image}}">
                        </a>
                    </div>
                    <div class="blog-content">
                        <ul class="entry-meta meta-item">
                            <li>
                                <div class="post-author">
                                    <a>
                                        Dibuat Oleh :<span> &nbsp; By Admin</span>
                                    </a>
                                </div>
                            </li>
                            <li><i class="far fa-clock"></i> {{\Carbon\Carbon::parse($obj->created_at)->format('d M Y')}}</li>
                        </ul>
                        <h3 class="blog-title"><a href="{{ url('/homepage/tips/'.$obj->slug) }}">{!! $obj->title !!}</a>
                        </h3>
                        <p class="mb-0">{!! $obj->content !!}</p>
                    </div>
                </div>
                <!-- /Blog Post -->
            </div>
            @endforeach
            @else
            <div class="col-sm-12">
                <p>Tidak ditemukan data tips</p>
            </div>
            @endif
        </div>
        <div class="view-all text-center">
            <a href="{{ url('/homepage/tips') }}" class="btn btn-primary">View All</a>
        </div>
    </div>
</section>
<!-- /Blog Section -->
<!-- Maps BWF -->
<iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15771.907723527886!2d115.1773851!3d-8.7882371!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0xf1a830791260f181!2sBhakti%20Widya%20Farma!5e0!3m2!1sid!2sid!4v1623567047619!5m2!1sid!2sid" width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
<!-- /Maps BWF -->
</div>
<script>
    //Get the button
    var mybutton = document.getElementById("tombol-atas");
    var rootElement = document.documentElement;

    // When the user scrolls down 20px from the top of the document, show the button
    window.onscroll = function() {
        scrollFunction()
    };

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
    html,
    body {
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

    #tombol-atas:hover {
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
        var getLoc = "https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=" + location.coords.latitude + "&lon=" + location.coords.longitude;
        $.getJSON(getLoc, function(data) {
            console.log(data);
            if (data.address.village != null) {
                $("#lokasi").val(data.address.village);
                $("#cek-lokasi").addClass("focused");
            } else if (data.address.city != null) {
                $("#lokasi").val(data.address.city);
                $("#cek-lokasi").addClass("focused");
            } else {
                $("#lokasi").val("");
            }
        });
    });
</script>
@endsection