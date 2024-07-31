<!DOCTYPE html>
<html lang="en">

    
<!-- Mirrored from demo.themeies.com/html/prolexe/ by HTTrack Website Copier/3.x [XR&CO'2014], Thu, 24 Dec 2020 11:13:42 GMT -->
<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>BWF Grup:</title>
        <link rel="shortcut icon" href="{{ asset('protected/public/homepage_assets/img/logo-small.png')}}">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- FONT_AWESOME -->
        <link rel="stylesheet" href="{{ asset('protected/public/homepage_assets/css/all.min.css')}}">
        <link rel="stylesheet" href="{{ asset('protected/public/homepage_assets/css/fontawesome.min.css')}}">

        <!-- THEMIFY ICON -->
        <link rel="stylesheet" href="{{ asset('protected/public/homepage_assets/css/themify-icons.css')}}">

        <!-- X-ICON -->
        <link rel="stylesheet" href="{{ asset('protected/public/homepage_assets/css/xicon.css')}}">

        <!-- OWL CAROUSEL -->
        <link rel="stylesheet" href="{{ asset('protected/public/homepage_assets/css/owl.carousel.min.css')}}">

        <!-- NICE SELECT -->
        <link rel="stylesheet" href="{{ asset('protected/public/homepage_assets/css/nice-select.css')}}">

        <!-- CORE NAVIGATION -->
        <link rel="stylesheet" href="{{ asset('protected/public/homepage_assets/css/coreNavigation-1.1.3.min.css')}}">

        <!-- FANCY-BOX -->
        <link rel="stylesheet" href="{{ asset('protected/public/homepage_assets/css/jquery.fancybox.min.css')}}">

        <!-- BOOTSTRAP -->
        <link rel="stylesheet" href="{{ asset('protected/public/homepage_assets/css/bootstrap.min.css')}}">

        <!-- PERSONAL STYLE -->
        <link rel="stylesheet" href="{{ asset('protected/public/homepage_assets/scss/style.css')}}">
        <link rel="stylesheet" href="{{ asset('protected/public/homepage_assets/css/responsive.css')}}">
        <link rel="stylesheet" href="{{ asset('protected/public/homepage_assets/css/style.css')}}">

        <style>
            @media (max-width: 575px){
                .header .nav-header .brand {
                    max-width: 190px;
                    padding-top: 23px;
                }
            }
        </style>
        @stack('style')
    </head>

    <body>

        <!-- Preloader -->

        <div id="preloader">
            <div id="status">
                {{-- <img src="{{ asset('protected/public/homepage_assets/img/logo/favicon.png" alt="perloader')}}"> --}}
            </div>
        </div>

        <!-- Header Top Bar Start -->

        <div class="header-top-bar d-md-block d-none">
            <div class="container">
                <div class="head-content d-md-flex d-block align-items-center">
                    <div class="logo d-none d-lg-block">
                        <a href="home.html"><img src="{{ asset('protected/public/homepage_assets/img/logo.png')}}" alt=""></a>
                    </div>
                    <div class="ml-auto content">
                        <i class="ti-time"></i>
                        Senin - Sabtu 08:00-22:00 <br>
                        Minggu - 10:00-22:00
                    </div>
                    <div class=" content">
                        <i class="ti-mobile"></i>
                        <div class="">
                            <a href="tel:081703246892">(+62) 81703246892</a>
                            <br>
                            <a href="mailto:contact@Prolexe.com">admin@apotekgroup.com</a>
                        </div>
                    </div>
                    <div class="content">
                        <i class="ti-location-pin"></i>
                        Jimbaran <br>
                        Bali
                    </div>
                </div>
            </div>
        </div>

        <!-- Header Start -->

        <header class="header">
            <nav class="">
                <!-- Mobile -->
                <div class="nav-header right">
                    <a href="home.html" class="brand d-lg-none d-block">
                        <img src="{{ asset('protected/public/homepage_assets/img/logo.png')}}" alt="">
                    </a>
                    <button class="toggle-bar"><span class="fa fa-bars"></span></button>
                </div>
                <div class="header-btn">
                    <a href="{{route('login')}}" class="btn btn-secondary">
                        <i class="fas fa-lock"></i><span>Login</span>
                    </a>
                </div>
                <!-- Mobile -->
                <ul class="menu">
                    <li class="{{ (request()->is('homepage')) ? 'active' : '' }}"><a href="{{route('index')}}">Home</a></li>
                    <li class="{{ (request()->is('homepage/about')) ? 'active' : '' }}"><a href="{{route('about')}}">Tentang Kami</a></li>
                    <li class="{{ (request()->is('homepage/outlet')) ? 'active' : '' }}"><a href="{{route('outlet')}}">Outlet</a></li>
                    {{-- <li class="dropdown">
                        <a href="#">Outlet</a>
                        <ul class="dropdown-menu">
                            <li><a href="#">Home-01</a></li>
                            <li><a href="#">Home-02</a></li>
                            <li><a href="#">Home-03</a></li>
                            <li><a href="#">Home-04</a></li>
                        </ul>
                    </li> --}}
                    <li class="{{ (request()->is('homepage/doctor') || request()->is('homepage/schedule') || request()->is('homepage/register')) ? 'active' : '' }}"><a href="{{route('medical_record')}}">Registrasi Pasien</a></li>
                    <li class="{{ (request()->is('homepage/tips*')) ? 'active' : '' }}"><a href="{{route('tips')}}">Tips</a></li>
                    <li class="{{ (request()->is('homepage/contact')) ? 'active' : '' }}"><a href="{{route('contact')}}">Hubungi Kami</a></li>
                </ul>

            </nav>
        </header>
        <!-- Header End -->

        @yield('content')

        <!-- CONTACT SECTION START -->
        <section class="contact-section">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <div class="contact-top-bar-side d-flex">
                            <div class="">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <p>
                                Please feel free to contact our friendly reception staff with any medical enquiry, or
                                call <a href="tel:12345678901">(+880)12345678901</a>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="contact-top-bar d-lg-flex align-items-center text-md-right text-center">
                            <div class="icon ml-auto">
                                <a href="#"><i class="fab fa-whatsapp"></i></a>
                                <a href="#"><i class="fab fa-skype"></i></a>
                                <a href="#"><i class="fab fa-facebook-messenger"></i></a>
                            </div>
                            <div class="bttn ">
                                <a href="#" class="btn btn-secondary-filled"></i>Make
                                    appontment <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-lg-3  col-md-6 col-sm-6">
                        <div class="contact-title text-left">
                            <h5>Prolexe <span>Clinic</span></h5>

                            <p>
                                We believe that its very light version & Simple, Creative &
                                Flexible Design.
                            </p>
                            <div class="sub-title mt-4">
                                <h6 class="mb-4">Prolexe <span>Clinic</span></h6>
                                <p>2564 Southern Avenue Floyd <br>
                                    AF 2356, USA
                                </p>
                            </div>
                            <div class="contact-link">
                                <h6>Phone: <a href="#"> (123 4567 890)</a></h6>
                                <h6>Fax: <a href="#"> (123 4567 890)</a></h6>
                                <h6>Email: <a href="#"> info@thenepiko.com</a></h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <div class="contact-title text-left">
                            <h5>Our <span>Service</span></h5>
                            <div class="contact-link-post">
                                <li>
                                    <a href="#"> <i class="ti-angle-right"></i>24/7 Ambulance </a>
                                </li>
                                <li>
                                    <a href="#"> <i class="ti-angle-right"></i>kidney Service</a>
                                </li>
                                <li>
                                    <a href="#"> <i class="ti-angle-right"></i>Operation Theater</a>
                                </li>
                                <li>
                                    <a href="#"> <i class="ti-angle-right"></i>Cancer Service</a>
                                </li>
                                <li>
                                    <a href="#"> <i class="ti-angle-right"></i>Blod Test</a>
                                </li>
                                <li>
                                    <a href="#"> <i class="ti-angle-right"></i>Denteal Service</a>
                                </li>
                                <li>
                                    <a href="#"> <i class="ti-angle-right"></i> Eye Care</a>
                                </li>
                                <li>
                                    <a href="#"> <i class="ti-angle-right"></i> Emergency Care</a>
                                </li>

                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3  col-md-6 col-sm-6">
                        <div class="contact-title text-left">
                            <h5>Opening <span>Hours</span></h5>
                            <p>
                                If you need a doctor for to the consec tetuer consectetur.
                            </p>
                        </div>
                        <div class="day-time">
                            <div class="d-flex justify-content-between mb-3">
                                Saturday
                                <a href="#">9.30 - 15.30</a>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                Sunday
                                <a href="#">9.30 - 15.30</a>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                Monday
                                <a href="#">9.30 - 15.30</a>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                Tuesday
                                <a href="#">9.30 - 17.00</a>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                Wednesday
                                <a href="#">9.30 - 17.00</a>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                Thursday
                                <a href="#">24-Hour Shift</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3  col-md-6 col-sm-6">
                        <div class="contact-title text-left">
                            <h5>Get <span>Update</span></h5>
                            <p>
                                We believe that its very light version & Simple, Creative &
                                Flexible Design.
                            </p>
                        </div>
                        <form action="#">
                            <div class="text-box d-flex align-items-center justify-content-between">
                                <input class="text" placeholder="Enter Email">
                                <button class="submit"><i class="far fa-envelope"></i></button>
                            </div>
                        </form>
                        <div class="social-icon">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-google-plus-g"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </section>


        <!-- CONTACT SECTION END -->

        <!-- FOOTER START -->

        <footer class="footer-section">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="footer-content d-md-flex justify-content-between align-items-center text-center">
                            <p>Copyright © 2020 <a href="https://themeies.com/">themeies.com</a> All rights
                                reserved.
                            </p>
                            <!-- <div class="footer-link d-md-flex text-center">
                                <a href="#">Terms & Condition</a>
                                <a href="#">Privacy Policy</a>
                                <a href="#">Cookies</a>
                            </div> -->
                        </div>
                    </div>
                </div>
            </div>
        </footer>
        <!-- FOOTER END -->

        <script src="{{ asset('protected/public/homepage_assets/js/jquery-3.3.1.min.js')}}"></script>
        <script src="{{ asset('protected/public/homepage_assets/js/bootstrap.min.js')}}"></script>
        <script src="{{ asset('protected/public/homepage_assets/js/owl.carousel.min.js')}}"></script>
        <script src="{{ asset('protected/public/homepage_assets/js/jquery.fancybox.min.js')}}"></script>
        <script src="{{ asset('protected/public/homepage_assets/js/jquery.nice-select.js')}}"></script>
        <script src="{{ asset('protected/public/homepage_assets/js/jquery.countup.js')}}"></script>
        <script src="{{ asset('protected/public/homepage_assets/js/jquery.waypoints.js')}}"></script>
        <script src="{{ asset('protected/public/homepage_assets/js/coreNavigation-1.1.3.min.js')}}"></script>
        <script src="{{ asset('protected/public/homepage_assets/js/popper.min.js')}}"></script>
        <script src="{{ asset('protected/public/homepage_assets/js/script.js')}}"></script>
        @stack('script')
    </body>


<!-- Mirrored from demo.themeies.com/html/prolexe/ by HTTrack Website Copier/3.x [XR&CO'2014], Thu, 24 Dec 2020 11:15:00 GMT -->
</html>