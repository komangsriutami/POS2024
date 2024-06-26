@extends('frontend')

@section('periode')
    
@endsection

@section('content')

<style>
    .mb-2{
        margin-bottom: 20px !important;
    }
    .image-gallery {
        position: relative;
    }

    .image-hover {
        opacity: 1;
        display: block;
        width: 100%;
        height: auto;
        transition: .5s ease;
        backface-visibility: hidden;
    }

    .middle {
        transition: .5s ease;
        opacity: 0;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        -ms-transform: translate(-50%, -50%);
        text-align: center;
    }

    .image-gallery:hover .image-hover {
        opacity: 0.3;
    }

    .image-gallery:hover .middle {
        opacity: 1;
    }

    .text {
        background-color: #00bca4;
        color: white;
        font-size: 16px;
        padding: 16px 32px;
    }
</style>
    <!--dari sini mulai page wrapper-->
    <div id="page-wrapper">
        @include('/frontend/v3/menubar-top')

        <!--<link rel="stylesheet" type="text/css" href="../../assets/widgets/owlcarousel/owlcarousel.css">-->
        {!! Html::script('assets/frontend/widgets/owlcarousel/owlcarousel.js') !!}
        {!! Html::script('assets/frontend/widgets/owlcarousel/owlcarousel-demo.js') !!}

        <div class="bg-primary overflow-hidden small-padding">
            <h2 class="text-center">We are Made Moments</h2>
        </div>

        <div class="container overflow-hidden large-padding">
            <div class="col-xs-6 col-md-4 mb-2 image-gallery">
                <a href="{{asset('assets/frontend/image-resources/stock-images/img-19.jpg')}}" class="prettyphoto" rel="prettyPhoto[pp_gal]" title="Blog post title">
                    <img class="img-responsive image-hover" src="{{asset('assets/frontend/image-resources/stock-images/img-19.jpg')}}" alt="">
                    <div class="middle">
                        <div class="text">Gallery</div>
                    </div>
                </a>
            </div>
            <div class="col-xs-6 col-md-4 mb-2 image-gallery">
                <a href="{{asset('assets/frontend/image-resources/stock-images/img-19.jpg')}}" class="prettyphoto" rel="prettyPhoto[pp_gal]" title="Blog post title">
                    <img class="img-responsive image-hover" src="{{asset('assets/frontend/image-resources/stock-images/img-19.jpg')}}" alt="">
                    <div class="middle">
                        <div class="text">Gallery</div>
                    </div>
                </a>
            </div>
            <div class="col-xs-6 col-md-4 mb-2 image-gallery">
                <a href="{{asset('assets/frontend/image-resources/stock-images/img-19.jpg')}}" class="prettyphoto" rel="prettyPhoto[pp_gal]" title="Blog post title">
                    <img class="img-responsive image-hover" src="{{asset('assets/frontend/image-resources/stock-images/img-19.jpg')}}" alt="">
                    <div class="middle">
                        <div class="text">Gallery</div>
                    </div>
                </a>
            </div>
            <div class="col-xs-6 col-md-4 mb-2 image-gallery">
                <a href="{{asset('assets/frontend/image-resources/stock-images/img-19.jpg')}}" class="prettyphoto" rel="prettyPhoto[pp_gal]" title="Blog post title">
                    <img class="img-responsive image-hover" src="{{asset('assets/frontend/image-resources/stock-images/img-19.jpg')}}" alt="">
                    <div class="middle">
                        <div class="text">Gallery</div>
                    </div>
                </a>
            </div>
            <div class="col-xs-6 col-md-4 mb-2 image-gallery">
                <a href="{{asset('assets/frontend/image-resources/stock-images/img-19.jpg')}}" class="prettyphoto" rel="prettyPhoto[pp_gal]" title="Blog post title">
                    <img class="img-responsive image-hover" src="{{asset('assets/frontend/image-resources/stock-images/img-19.jpg')}}" alt="">
                    <div class="middle">
                        <div class="text">Gallery</div>
                    </div>
                </a>
            </div>
            <div class="col-xs-6 col-md-4 mb-2 image-gallery">
                <a href="{{asset('assets/frontend/image-resources/stock-images/img-19.jpg')}}" class="prettyphoto" rel="prettyPhoto[pp_gal]" title="Blog post title">
                    <img class="img-responsive image-hover" src="{{asset('assets/frontend/image-resources/stock-images/img-19.jpg')}}" alt="">
                    <div class="middle">
                        <div class="text">Gallery</div>
                    </div>
                </a>
            </div>
            <div class="col-xs-6 col-md-4 mb-2 image-gallery">
                <a href="{{asset('assets/frontend/image-resources/stock-images/img-19.jpg')}}" class="prettyphoto" rel="prettyPhoto[pp_gal]" title="Blog post title">
                    <img class="img-responsive image-hover" src="{{asset('assets/frontend/image-resources/stock-images/img-19.jpg')}}" alt="">
                    <div class="middle">
                        <div class="text">Gallery</div>
                    </div>
                </a>
            </div>
            <div class="col-xs-6 col-md-4 mb-2 image-gallery">
                <a href="{{asset('assets/frontend/image-resources/stock-images/img-19.jpg')}}" class="prettyphoto" rel="prettyPhoto[pp_gal]" title="Blog post title">
                    <img class="img-responsive image-hover" src="{{asset('assets/frontend/image-resources/stock-images/img-19.jpg')}}" alt="">
                    <div class="middle">
                        <div class="text">Gallery</div>
                    </div>
                </a>
            </div>
        </div>

        @include('/frontend/v3/footer2')
    </div>
    <!--end of page content-->
@endsection

