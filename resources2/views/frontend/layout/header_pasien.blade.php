<?php error_reporting(0); ?>
<!-- Loader -->
@if (Route::is(['map-grid', 'map-list']))
    <div id="loader">
        <div class="loader">
            <span></span>
            <span></span>
        </div>
    </div>
@endif
<!-- /Loader  -->
<div class="main-wrapper">
    <!-- Header -->
    <header class="header">
        <nav class="navbar fixed-top navbar-expand-lg header-nav">
            <div class="navbar-header">
                <a id="mobile_btn" href="javascript:void(0);">
                    <span class="bar-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </a>
                <a href="{{ url('/homepage') }}" class="navbar-brand logo">
                    <div class="row no-gutters">
                        <div class="col-lg-4 col-gut">
                            <img src="{{url('assets_frontend/img/logo.png')}}" class="img-fluid" alt="Logo" width="35" height="35">
                        </div>
                        <div  class="col-lg-8 col-gut">
                            Bhakti Widya Farma
                        </div>
                    </div>
                </a>
            </div>
            <div class="main-menu-wrapper">
                <div class="menu-header">
                    <a href="{{ url('/homepage') }}" class="menu-logo">
                        <img src="{{url('assets_frontend/img/logo.png')}}" class="img-fluid" alt="Logo" width="35" height="35">
                        Bhakti Widya Farma
                    </a>
                    <a id="menu_close" class="menu-close" href="javascript:void(0);">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
                <ul class="main-nav">
                    {{-- Homepage --}}
                    <li class="menu-homepage <?php if ($page == 'homepage') {
                        echo 'active';
                    } ?>" onclick="Onepage('menu-homepage');">
                        <a href="{{ url('/homepage#menu-homepage') }}">Home</a>
                    </li>
                    {{-- Our Doctor --}}
                    <li class="menu-searchdokter">
                        <a href="{{ url('/search_dokter#menu-searchdokter') }}" onclick="Onepage('menu-searchdokter');">Cari Dokter</a>
                    </li>
                    {{-- Our Consultation --}}
                    <li class="menu-consultation">
                        <a href="{{ url('/home_pasien') }}" onclick="Onepage('menu-consultation');">Jadwal Konsultasi</a>
                    </li>
                    {{-- Our Rekammedis --}}
                    <li class="menu-rekammedis">
                        <a href="{{ url('/home_pasien') }}" onclick="Onepage('menu-rekammedis');">Rekam Medis</a>
                    </li>

                @if(session('id') == null)
                    <li>
                        <div class="d-flex align-items-center justify-content-center">
				    	    <a class="btn btn-outline-success h-50" href="login_pasien" style="margin: 25px 0px;">login / Signup </a>
                        </div>
                    </li>
                @else
                    <li class="has-submenu">
                        <a href=""><i class="fa fa-user"></i> Halo, {{ session('username') }}  <i class="fas fa-chevron-down"></i></a>
                        <ul class="submenu" style="right:0px; left: auto; transform: scaleX(-1);">
                            <li><a style="transform: scaleX(-1);" href="{{ url('info_akun') }}">Info Akun</a></li>
                            <li><a style="transform: scaleX(-1);" href="{{ url('logout_pasien_post') }}">Logout</a></li>
                        </ul>
                    </li>
                @endif
                </ul>
            </div>
        </nav>
        <style type="text/css">
            .no-gutters {
              margin-right: 0;
              margin-left: 0;
            }
            .no-gutters .col-gut{
                padding-right: 0;
                padding-left: 0;
            }
        </style>
    </header>
    <!-- /Header -->
