<!-- <div class="top-bar bg-topbar">
    <div class="container">
        <div class="float-left">
            <a href="" class="btn btn-sm bg-facebook tooltip-button" data-placement="bottom" title="Follow us on Faceboook">
                <i class="glyph-icon icon-facebook"></i>
            </a>
            <a href="" class="btn btn-sm bg-google tooltip-button" data-placement="bottom" title="Follow us on Google+">
                <i class="glyph-icon icon-google-plus"></i>
            </a>
            <a href="https://www.instagram.com/apotekbwfgroup/" class="btn btn-sm tooltip-button" data-placement="bottom" style="background: #a1887f;" title="Follow us on Instagram">
                <i class="glyph-icon icon-instagram" style="color: white;"></i>
            </a>
            <a href="https://api.whatsapp.com/send?phone=6281236246911&text=Saya%20ingin%20mencoba%20aplikasi%20apotekeren" class="btn btn-top btn-sm" title="Give us a call">
                <i class="glyph-icon icon-phone"></i>
                (+62) 81 236 246 911
            </a>
        </div>
        @if(Auth::check())
        <div class="float-right user-account-btn dropdown">
            <a href="{{ URL('/home') }}" title="MonarchUI Admin Template" class="btn btn-sm float-left btn-alt btn-hover mrg10R btn-default">
                <span>Back to admin</span>
                <i class="glyph-icon icon-arrow-right"></i>
            </a>
            <a href="#" title="My Account" class="user-profile clearfix" data-toggle="dropdown" aria-expanded="false">
                <span>{{ Auth::user()->nama }}</span>
                <i class="glyph-icon icon-angle-down"></i>
            </a>
            <div class="dropdown-menu pad0B float-right">
                <div class="box-sm">
                    <div class="login-box clearfix">
                        <div class="user-img">
                            <a href="#" title="" class="change-img">Change photo</a>
                            <img src="{{asset('assets/frontend/image-resources/gravatar.jpg')}}" alt="">
                        </div>
                        <div class="user-info">
                            <span>
                                {{ Auth::user()->nama }}
                                <i>{{ session('nama_role_active') }}</i>
                            </span>
                            <a href="{{ url('/profile') }}" title="">Edit profile</a>
                            <a href="{{ url('/notifications') }}" title="">View notifications</a>
                        </div>
                    </div>
                    <div class="pad5A button-pane button-pane-alt text-center">
                        <a href="{{ route('logout') }}" title="Logout" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="btn display-block font-normal btn-danger">
                            <i class="glyph-icon icon-power-off"></i>
                            Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<div class="main-header bg-header wow fadeInDown">
    <div class="container">
        <img src="{{url('img/logo.jpeg')}}" class="header-logo" alt="Logo" height="50" width="60" title="APOTEKEREN">
        <div class="right-header-btn">
            <div id="mobile-navigation">
                <button id="nav-toggle" class="collapsed" data-toggle="collapse" data-target=".header-nav"><span></span></button>
            </div>
            <div class="search-btn">
                <a href="#" class="popover-button" title="Search" data-placement="bottom" data-id="#popover-search">
                    <i class="glyph-icon icon-search"></i>
                </a>
                <div class="hide" id="popover-search">
                    <div class="pad5A box-md">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Search terms here ...">
                            <span class="input-group-btn">
                                <a class="btn btn-primary" href="#">Search</a>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <ul class="header-nav collapse">
            <li>
                <a href="{{url('/')}}" title="Home">
                    Home
                    <i class="glyph-icon icon-angle"></i>
                </a>
            </li>
            <li>
                <a href="{{url('/#apotekeren')}}" title="Profil">
                    Mengapa ApoteKeren?
                    <i class="glyph-icon icon-angle"></i>
                </a>
            </li>
            <li>
                <a title="Layanan">
                    Fitur
                    <i class="glyph-icon icon-angle-down"></i>
                </a>
                <ul class="footer-nav">
                    <li><a href="{{url('/detail-fitur/1')}}" title="Static hero sections"><span>Pengelolaan Data Transaksi</span></a></li>
                    <li><a href="{{url('/detail-fitur/2')}}" title="Hero alignments"><span>Pengelolaan Persediaan</span></a></li>
                    <li><a href="{{url('/detail-fitur/3')}}" title="Hero overlays"><span>Pengelolaan Promo</span></a></li>
                    <li><a href="{{url('/detail-fitur/4')}}" title="Hero with video backgrounds"><span>Sistem Pengambilan Keputusan</span></a></li>
                    <li><a href="{{url('/detail-fitur/5')}}" title="Hero sections with elements"><span>Laporan Keuangan</span></a></li>
                    <li><a href="{{url('/detail-fitur/6')}}" title="Hero with parallax backgrounds"><span>Monitoring Usaha</span></a></li>
                    <li><a href="{{url('/detail-fitur/7')}}" title="Hero with image backgrounds"><span>Pengelolaan Pajak</span></a></li>
                    <li><a href="{{url('/detail-fitur/8')}}" title="Hero with pattern backgrounds"><span>Layanan Kesehatan Terintegrasi</span></a></li>
                </ul>
            </li>
            <li>
                <a href="https://api.whatsapp.com/send?phone=6281236246911&text=Saya%20ingin%20mencoba%20aplikasi%20apotekeren" target="blank_" title="Hubungi Kami">
                    Hubungi Kami
                    <i class="glyph-icon icon-angle"></i>
                </a>
            </li>
            <li>
                <a href="{{ url('login_outlet') }}" title="Coba Gratis">
                    Coba Gratis
                    <i class="glyph-icon icon-angle"></i>
                </a>
            </li>
            <li>
                <a href="{{ url('register_jadwal_demo') }}" title="Jadwal Demo">
                    Jadwalkan Demo
                    <i class="glyph-icon icon-angle"></i>
                </a>
            </li>
            <li>
                <a href="{{ url('register_outlet') }}" title="Login">
                    Login
                    <i class="glyph-icon icon-angle"></i>
                </a>
            </li>
        </ul>
    </div>
</div> -->

    <div class="right-header-btn">
        <div id="mobile-navigation">
            <button id="nav-toggle" class="collapsed" data-toggle="collapse" data-target=".header-nav"><span></span></button>
        </div>
        <div class="search-btn">
            <a href="#" class="popover-button" title="Search" data-placement="bottom" data-id="#popover-search">
                <i class="glyph-icon icon-search"></i>
            </a>
            <div class="hide" id="popover-search">
                <div class="pad5A box-md">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Search terms here ...">
                        <span class="input-group-btn">
                            <a class="btn btn-primary" href="#">Search</a>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- .header-logo -->
    <ul class="header-nav collapse">
        <li>
            <a href="{{url('/')}}" title="Home">
                Home
                <i class="glyph-icon icon-angle"></i>
            </a>
        </li>
        <li>
            <a href="{{url('/#apotekeren')}}" title="Profil">
                Mengapa ApoteKeren?
                <i class="glyph-icon icon-angle"></i>
            </a>
        </li>
        <li>
            <a title="Layanan">
                Fitur
                <i class="glyph-icon icon-angle-down"></i>
            </a>
            <ul class="footer-nav">
                <li><a href="{{url('/detail-fitur/1')}}" title="Static hero sections"><span>Pengelolaan Data Transaksi</span></a></li>
                <li><a href="{{url('/detail-fitur/2')}}" title="Hero alignments"><span>Pengelolaan Persediaan</span></a></li>
                <li><a href="{{url('/detail-fitur/3')}}" title="Hero overlays"><span>Pengelolaan Promo</span></a></li>
                <li><a href="{{url('/detail-fitur/4')}}" title="Hero with video backgrounds"><span>Sistem Pengambilan Keputusan</span></a></li>
                <li><a href="{{url('/detail-fitur/5')}}" title="Hero sections with elements"><span>Laporan Keuangan</span></a></li>
                <li><a href="{{url('/detail-fitur/6')}}" title="Hero with parallax backgrounds"><span>Monitoring Usaha</span></a></li>
                <li><a href="{{url('/detail-fitur/7')}}" title="Hero with image backgrounds"><span>Pengelolaan Pajak</span></a></li>
                <li><a href="{{url('/detail-fitur/8')}}" title="Hero with pattern backgrounds"><span>Layanan Kesehatan Terintegrasi</span></a></li>
            </ul>
        </li>
        <li>
            <a href="https://api.whatsapp.com/send?phone=6281236246911&text=Saya%20ingin%20mencoba%20aplikasi%20apotekeren" target="blank_" title="Hubungi Kami">
                Hubungi Kami
                <i class="glyph-icon icon-angle"></i>
            </a>
        </li>
        <li>
            <a href="{{ url('register_outlet') }}" title="Coba Gratis">
                Coba Gratis
                <i class="glyph-icon icon-angle"></i>
            </a>
        </li>
        <li>
            <a href="{{ url('register_jadwal_demo') }}" title="Jadwal Demo">
                Jadwalkan Demo
                <i class="glyph-icon icon-angle"></i>
            </a>
        </li>
        <li>
            <a href="{{ url('login_system') }}" title="Login">
                Login
                <i class="glyph-icon icon-angle"></i>
            </a>
        </li>
    </ul><!-- .header-nav -->