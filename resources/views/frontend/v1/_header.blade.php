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
                            <img src="{{url('img/logo.jpeg')}}" class="img-fluid2" alt="Logo" height="50">
                        </div>
                    </div>
                </a>
            </div>
            <div class="main-menu-wrapper">
                <ul class="main-nav">
                    <li class="menu-homepage <?php if ($page == 'homepage') { echo 'active';
                        } ?>" onclick="Onepage('menu-homepage');">
                        <a href="{{ url('/homepage#menu-homepage') }}">Home</a>
                    </li>
                    <li class="menu-ourdoctor">
                        <a href="{{ url('/homepage#menu-ourdoctor') }}" onclick="Onepage('menu-ourdoctor');">Mengapa ApoteKeren?</a>
                    </li>
                    <li class="menu-ourdoctor">
                        <a href="{{ url('/homepage#menu-ourdoctor') }}" onclick="Onepage('menu-ourdoctor');">Layanan</a>
                    </li>
                    <li class="menu-ourapoteker">
                        <a href="{{ url('/homepage#menu-ourapoteker') }}" onclick="Onepage('menu-ourapoteker');">Hubungi Kami</a>
                    </li>
                    <li class="menu-special">
                        <a href="{{ url('/homepage#menu-special') }}" onclick="Onepage('menu-special');">Coba Gratis</a>
                    </li>
                    @if(session('id') == null)
                    <li class="has-submenu <?php if ($page == 'login_admin' || $page == 'login_outlet') {
                        echo 'active';
                        } ?>">
                        <div class="d-flex align-items-center justify-content-center">
                            <a class="btn btn-outline-success h-50" href="login_pasien"  style="margin: 25px 0px;">Login / Signup <i class="fas fa-caret-down"></i></a>
                        </div>
                        <ul class="submenu" style="right:0px; left: auto; transform: scaleX(-1);">
                            <li class="<?php if ($page == 'login_admin') {
                                echo 'active';
                                } ?>"><a style="transform: scaleX(-1);" href="{{ url('/login_admin') }}">Login Group</a></li>
                            <li class="<?php if ($page == 'login_outlet') {
                                echo 'active';
                                } ?>"><a style="transform: scaleX(-1);" href="{{ url('/login_outlet') }}">Login Outlet</a></li>
                        </ul>

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
