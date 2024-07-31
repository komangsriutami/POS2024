@extends('frontend')

@section('content')
    <style>
        .hero-box-smaller .owl-pagination {
            display: none;
        }
        .bg-secondary{
            background-color: #e7e7e7;
        }
        .hero-box .icon-box .icon-alt{
            color: #000;
        }
        .icon-title{
            color: #000 !important;
        }
        .icon-content{
            color: rgb(104 104 104 / 70%) !important;
        }
    </style>

    {!! Html::script('assets/frontend/widgets/fullpage/fullpage.js') !!}

    <script type="text/javascript">
        $(document).ready(function() {
            $('#fullpage').fullpage({
                anchors: ['firstPage'],
                autoScrolling: false,
                css3: true
            });
        });
    </script>
    <!--dari sini mulai page wrapper-->
    <div id="page-wrapper">
        </div><!-- .top-bar -->
        <div class="main-header header-fixed font-inverse header-opacity header-resizable wow bounceInDown" data-0="padding: 35px 0; background: rgba(255,255,255,0.1);" data-250="padding: 0px 0; background: rgba(0,0,0,1)">
            <div class="container">
                <a href="{{ url('/') }}" class="header-logo" title="APOTEKEREN"><span style="font-size: 18pt;color: white!important;margin: 2pt;">APOTEKEREN</span><span style="font-size: 10pt;color: white!important;">THE PHARMACY POINT OF SALE SYSTEM</span></a>
                @include('/frontend/v3/menubar-top')
            </div>
        </div>
        <div id="fullpage">
            <div class="section" id="section0" style="background-image: url('assets/frontend/image-resources/full-bg/full-bg-9.jpg'); "> <!-- #009688; -->
                <div class="slide">
                    <div class="center-vertical">
                        <div class="center-content">
                            <div class="container">
                                <div class="hero-box font-inverse">
                                    <h2 class="hero-heading wow fadeInDown" data-wow-duration="0.6s" style="color: white;">The Pharmacy Point of Sale System</h2>
                                    <p class="pad25T mrg25B hero-text wow bounceInUp" data-wow-duration="0.9s" data-wow-delay="0.2s">Ayo! Kembangkan bisnis apotekmu dengan software terkeren! Solusi pengeelolaan apotek dan laporan otomatis, mudah, dan efisien.</p>
                                    <div class="pad25T">
                                        <button class="mrg5R btn btn-alt btn-hover btn-lg btn-outline-inverse remove-bg" type="button" onclick="window.location='{{ ("login_outlet") }}'">
                                            <span>Coba Gratis</span>
                                            <i class="glyph-icon icon-arrow-right"></i>
                                        </button>
                                        <button class="btn btn-alt btn-hover btn-lg btn-outline-inverse remove-bg" type="button" onclick="window.open('https://api.whatsapp.com/send?phone=6281236246911&text=Saya%20ingin%20mencoba%20aplikasi%20apotekeren', '_blank')">
                                            <span>Hubungi Kami</span>
                                            <i class="glyph-icon icon-arrow-right"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="scroller-icon">
                        <div class="glyph-icon icon-angle-down font-white"></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- <div class="owl-slider-2 slider-wrapper" id="home">
            <div class="poly-bg-1 hero-box font-inverse hero-box-smaller">
                <div class="container clearfix">
                    <div class="col-md-2 img-holder wow fadeInUp">
                        <img src="{{asset('assets/frontend/image-resources/objects/browser-1.png')}}" alt="">
                        <img src="{{asset('assets/frontend/image-resources/objects/browser-2.png')}}" alt="">
                    </div>
                    <div class="col-md-10">
                        <h1 class="hero-heading wow fadeInDown" data-wow-duration="0.6s">Ayo! Kembangkan Bisnis Apotekmu dengan software terkeren!</h1>
                        <p class="hero-text wow bounceInUp" data-wow-duration="0.9s" data-wow-delay="0.2s">Solusi pengelolaan <b>APOTEK</b> dan laporan keuangan yang otomatis, mudah, dan efisien.</p>
                        <a href="{{ url('login_outlet') }}" class="btn-outline-inverse hero-btn wow btn-hover fadeInUp" data-wow-delay="0.4s" title="Purchase Button">Coba Gratis <i class="glyph-icon icon-arrow-right"></i></a>
                        <a href="https://api.whatsapp.com/send?phone=6281236246911&text=Saya%20ingin%20mencoba%20aplikasi%20apotekeren" target="blank_"  class="btn-outline-inverse hero-btn wow btn-hover fadeInUp" data-wow-delay="0.5s" title="Purchase Button">Hubungi Kami <i class="glyph-icon icon-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <div class="poly-bg-1 hero-box font-inverse hero-box-smaller">
                <div class="container clearfix">
                    <div class="col-md-9">
                        <h1 class="hero-heading wow fadeInDown" data-wow-duration="0.6s">Ayo! Kembangkan Bisnis Apotekmu dengan software terkeren!</h1>
                        <p class="hero-text wow bounceInUp" data-wow-duration="0.9s" data-wow-delay="0.2s">Solusi pengelolaan <b>APOTEK</b> dan laporan keuangan yang otomatis, mudah, dan efisien.</p>
                        <a href="{{ url('login_outlet') }}"class="btn-outline-inverse hero-btn wow btn-hover fadeInUp" data-wow-delay="0.4s" title="Purchase Button">Coba Gratis <i class="glyph-icon icon-arrow-right"></i></a>
                        <a href="https://api.whatsapp.com/send?phone=6281236246911&text=Saya%20ingin%20mencoba%20aplikasi%20apotekeren" target="blank_"  class="btn-outline-inverse hero-btn wow btn-hover fadeInUp" data-wow-delay="0.5s" title="Purchase Button">Hubungi Kami <i class="glyph-icon icon-arrow-right"></i></a>
                    </div>
                    <div class="col-md-3 img-holder wow fadeInUp">
                        <img src="{{asset('assets/frontend/image-resources/objects/phone-1.png')}}" alt="">
                    </div>
                </div>
            </div>
        </div> -->
        <!-- Owl carousel -->

        <!--<link rel="stylesheet" type="text/css" href="../../assets/widgets/owlcarousel/owlcarousel.css">-->
        {!! Html::script('assets/frontend/widgets/owlcarousel/owlcarousel.js') !!}
        {!! Html::script('assets/frontend/widgets/owlcarousel/owlcarousel-demo.js') !!}

        @include('/frontend/v3/fitur')
        @include('/frontend/v3/fitur2')

        {{-- <div class="cta-box-btn bg-primary" id="fitur">
            <div class="container">
                <a href="#" class="btn btn-success" title="">
                    Fitur
                    <span>It takes less than 5 minutes to get everything set up.</span>
                </a>
            </div>
        </div> --}}
        
        <div class="container mrg10T" id="coba">

            <h3 class="p-title">
                <span>Pilihan Tepat Untuk Bisnismu</span>
            </h3>

            <div class="row pricing-table mrg20T">
                <div class="pricing-box col-md-4 content-box">
                    <h3 class="pricing-title">Silver</h3>
                    <div class="pricing-specs">
                        <span><sup>$</sup>299</span>
                        <i>Once per month</i>
                    </div>
                    <ul>
                        <li>Lorem ipsum</li>
                        <li>Not included</li>
                        <li><i class="glyph-icon icon-check-circle font-size-23 font-green"></i></li>
                        <li><i class="glyph-icon icon-times font-size-23 font-red"></i></li>
                        <li>Another item</li>
                        <li>This is included</li>
                    </ul>
                    <div class="pad25A">
                        <a href="{{ url('register_outlet') }}" class="btn btn-lg text-transform-upr btn-black font-size-12" title="">Sign Up Now!</a>
                    </div>
                </div>
                <div class="pricing-box pricing-best col-md-4 content-box">
                    <h3 class="pricing-title bg-primary">Silver</h3>
                    <div class="pricing-specs">
                        <span><sup>$</sup>299</span>
                        <i>Once per month</i>
                    </div>
                    <ul>
                        <li>Lorem ipsum</li>
                        <li>Not included</li>
                        <li><i class="glyph-icon icon-check-circle font-size-23 font-green"></i></li>
                        <li><i class="glyph-icon icon-times font-size-23 font-red"></i></li>
                        <li>Another item</li>
                        <li>This is included</li>
                    </ul>
                    <div class="pad25A">
                        <a href="{{ url('register_outlet') }}" class="btn btn-lg text-transform-upr btn-primary font-size-14" title="">Sign Up Now!</a>
                    </div>
                </div>
                <div class="pricing-box col-md-4 content-box">
                    <h3 class="pricing-title">Silver</h3>
                    <div class="pricing-specs">
                        <span><sup>$</sup>299</span>
                        <i>Once per month</i>
                    </div>
                    <ul>
                        <li>Lorem ipsum</li>
                        <li>Not included</li>
                        <li><i class="glyph-icon icon-check-circle font-size-23 font-green"></i></li>
                        <li><i class="glyph-icon icon-times font-size-23 font-red"></i></li>
                        <li>Another item</li>
                        <li>This is included</li>
                    </ul>
                    <div class="pad25A">
                        <a href="{{ url('register_outlet') }}" class="btn btn-lg text-transform-upr btn-black font-size-12" title="">Sign Up Now!</a>
                    </div>
                </div>
            </div>
        </div>

        @include('/frontend/v3/comment')
        @include('/frontend/v3/footer2')
    </div>
    <!--end of page content-->
@endsection

