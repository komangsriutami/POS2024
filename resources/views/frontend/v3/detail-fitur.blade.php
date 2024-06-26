@extends('frontend')

@section('content')
    <!--dari sini mulai page wrapper-->
    <div id="page-wrapper">
        <div class="main-header bg-header wow fadeInDown">
            <div class="container">
                <a href="{{ url('/') }}" class="header-logo" title="APOTEKEREN"><span style="font-size: 18pt;color: #00897b!important;margin: 2pt;">APOTEKEREN</span><span style="font-size: 10pt;color: #00897b!important;">THE PHARMACY POINT OF SALE SYSTEM</span></a>
                @include('/frontend/v3/menubar-top')
            </div>
        </div>
        <div class="hero-box hero-box-smaller full-bg-5 font-inverse clearfix controls" style="padding: 20px!important;margin: none!important">
            <div class="container text-center">
                <p class="text-right">Home / Fitur Pengelolaan Data Transaksi</p>
            </div>
            <div class="hero-overlay bg-black"></div>
        </div>
        <div class="container large-padding">
            <div class="row">
                <div class="col-xs-12">
                    <h3 class="text-center pad25B text-transform-upr font-size-23">- Pengelolaan Data Transaksi -</h3>
                </div>
                <div class="col-xs-12 col-md-6">
                    <img src="http://placehold.it/300x300/42bdc2/FFFFFF" width="100%">
                </div>
                <div class="col-xs-12 col-md-6">
                    <ul class="feature-list" style="padding: 10px;">
                        <li>
                            <i class="glyph-icon font-primary icon-camera"></i>
                            <span>
                                <b>Title</b>
                                <p>Lorem, ipsum dolor sit amet consectetur adipisicing elit. Facilis odit vero, est reiciendis id quam, quo commodi fuga accusantium at cumque, nostrum illum. Accusantium maiores tempore fugit ut sequi placeat.</p>
                            </span>
                        </li>
                        <li>
                            <i class="glyph-icon font-primary icon-camera"></i>
                            <span>
                                <b>Title</b>
                                <p>Lorem, ipsum dolor sit amet consectetur adipisicing elit. Facilis odit vero, est reiciendis id quam, quo commodi fuga accusantium at cumque, nostrum illum. Accusantium maiores tempore fugit ut sequi placeat.</p>
                            </span>
                        </li>
                        <li>
                            <i class="glyph-icon font-primary icon-camera"></i>
                            <span>
                                <b>Title</b>
                                <p>Lorem, ipsum dolor sit amet consectetur adipisicing elit. Facilis odit vero, est reiciendis id quam, quo commodi fuga accusantium at cumque, nostrum illum. Accusantium maiores tempore fugit ut sequi placeat.</p>
                            </span>
                        </li>
                        <li>
                            <i class="glyph-icon font-primary icon-camera"></i>
                            <span>
                                <b>Title</b>
                                <p>Lorem, ipsum dolor sit amet consectetur adipisicing elit. Facilis odit vero, est reiciendis id quam, quo commodi fuga accusantium at cumque, nostrum illum. Accusantium maiores tempore fugit ut sequi placeat.</p>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        @include('/frontend/v3/footer2')
    </div>
@endsection