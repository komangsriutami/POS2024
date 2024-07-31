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
                            <img src="{{url('img/logo.jpeg')}}" alt="Logo" height="50">
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
                    <li class="menu-ourdoctor">
                        <a href="{{ url('/homepage#menu-ourdoctor') }}" onclick="Onepage('menu-ourdoctor');">Praktek Dokter</a>
                    </li>
                    {{-- Our Apoteker --}}
                    <li class="menu-ourapoteker">
                        <a href="{{ url('/homepage#menu-ourapoteker') }}" onclick="Onepage('menu-ourapoteker');">Apoteker</a>
                    </li>
                    {{-- Specialis --}}
                    <li class="menu-special">
                        <a href="{{ url('/homepage#menu-special') }}" onclick="Onepage('menu-special');">Spesialis</a>
                    </li>
                    {{-- News & TIps --}}
                    <li class="menu-news">
                        <a href="{{ url('/homepage#menu-news') }}" onclick="Onepage('menu-news');">Berita & Tips</a>
                    </li>
                     {{-- Contact --}}
                    <li class="menu-contact">
                        <a href="{{ url('/homepage#menu-contact') }}" onclick="Onepage('menu-contact');">Kontak </a>
                    </li>
                    @if(session('id') == null)
                    {{-- Staff Tenaga Medis --}}
                    <li class="has-submenu <?php if ($page == 'login_dokter' || $page == 'register_dokter') {
                        echo 'active';
                    } ?>">
                        <a>Tenaga Medis <i class="fas fa-chevron-down"></i></a>
                        <ul class="submenu" style="right:0px; left: auto; transform: scaleX(-1);">
                            <li class="<?php if ($page == 'register_dokter') {
                                echo 'active';
                            } ?>"><a style="transform: scaleX(-1);" href="{{ url('/register_dokter') }}">Register</a></li>
                            <li class="<?php if ($page == 'login_dokter') {
                                echo 'active';
                            } ?>"><a style="transform: scaleX(-1);" href="{{ url('/login_dokter') }}">Login</a></li>
                        </ul>
                    </li>

                    {{-- Staff Admin --}}
                    <li class="has-submenu <?php if ($page == 'login_admin' || $page == 'login_outlet') {
                        echo 'active';
                    } ?>">
                        <a>Admin POS <i class="fas fa-chevron-down"></i></a>
                        <ul class="submenu" style="right:0px; left: auto; transform: scaleX(-1);">
                            <li class="<?php if ($page == 'login_admin') {
                                echo 'active';
                            } ?>"><a style="transform: scaleX(-1);" href="{{ url('/login_admin') }}">Login PT</a></li>
                            <li class="<?php if ($page == 'login_outlet') {
                                echo 'active';
                            } ?>"><a style="transform: scaleX(-1);" href="{{ url('/login_outlet') }}">Login Outlet</a></li>
                        </ul>
                    </li>

                    {{-- Apoteker --}}
                    <li class="has-submenu <?php if ($page == 'login_admin' || $page == 'login_outlet') {
                        echo 'active';
                    } ?>">
                        <a>Apoteker <i class="fas fa-chevron-down"></i></a>
                        <ul class="submenu" style="right:0px; left: auto; transform: scaleX(-1);">
                            <li class="<?php if ($page == 'register_apoteker') {
                                echo 'active';
                            } ?>"><a style="transform: scaleX(-1);" href="{{ url('/register_apoteker') }}">Register</a></li>
                            <li class="<?php if ($page == 'login_apoteker') {
                                echo 'active';
                            } ?>"><a style="transform: scaleX(-1);" href="{{ url('/login_apoteker') }}">Login</a></li>
                        </ul>
                    </li>

                    {{-- Pasien --}}
                    <li class="has-submenu <?php if ($page == 'login_admin' || $page == 'login_outlet') {
                        echo 'active';
                    } ?>">
                        <a>Pasien <i class="fas fa-chevron-down"></i></a>
                        <ul class="submenu" style="right:0px; left: auto; transform: scaleX(-1);">
                            <li class="<?php if ($page == 'register_pasien') {
                                echo 'active';
                            } ?>"><a style="transform: scaleX(-1);" href="{{ url('/register_pasien') }}">Register</a></li>
                            <li class="<?php if ($page == 'login_pasien') {
                                echo 'active';
                            } ?>"><a style="transform: scaleX(-1);" href="{{ url('/login_pasien') }}">Login</a></li>
                        </ul>
                    </li>
                @endif
                {{-- @if(session('id') == null)
                    <li>
                        <div class="d-flex align-items-center justify-content-center">
				    	    <a class="btn btn-outline-success h-50" href="login_pasien" style="margin: 25px 0px;">Login / Signup </a>
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
                @endif --}}
                    {{-- Bahasa --}}
                    {{-- <li class="has-submenu <?php if ($page == 'bahasa') {
                        echo 'active';
                    } ?>">
                        <a href="">Bahasa <i class="fas fa-chevron-down"></i></a>
                        <ul class="submenu" style="right:0px; left: auto; transform: scaleX(-1);">
                            <li class="<?php if ($page == 'inggris') {
                                echo 'active';
                            } ?>">
                                <a href="inggris" target="_blank" style="transform: scaleX(-1);">
                                    <img src="assets_frontend/img/inggris.png" width="60px" height="50px"> Inggris
                                </a>
                            </li>
                            <li class="<?php if ($page == 'indonesia') {
                                echo 'active';
                            } ?>">
                                <a href="indonesia" target="_blank" style="transform: scaleX(-1);">
                                    <img src="assets_frontend/img/indonesia.png" width="60px" height="50px"> Indonesia
                                </a>
                            </li>
                        </ul>
                    </li> --}}
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
