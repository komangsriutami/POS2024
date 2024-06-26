@extends('homepage.layouts.app')

@section('content')
<!-- SERVICE PAGE BANNER -->

<div class="page-banner">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="page-banner-content">
                    <div class="d-sm-flex justify-content-between align-items-center">
                        <div class="title">
                            {{-- <h6 class="text-left text-capitalized">Blog-Single</h6> --}}
                            <h2>{{$data->title}}</h2>
                        </div>
                        <div class="link text-sm-right text-left">
                            <a href="home.html">Tips <i class="ti-angle-double-right"></i></a>
                            {{$data->title}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SERVICE PAGE BANNER  END-->

<!-- BLOG PAGE SECTION -->

<section class="blog-page">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="blog-single-page">
                    <div class="blog-item">
                        <div class="blog-bg">
                            <img src="{{ asset($data->displayImage())}}" alt="1">
                            <div class="overlay">
                                <i class="far fa-image"></i>
                            </div>
                        </div>
                        <div class="blog-content mt-4">
                            <h4>{{$data->title}}</h4>
                            <div class="">
                                <span>
                                    {{\Carbon\Carbon::parse($data->created_at)->format('F d, Y')}}
                                </span>
                                {{-- <span><a href="#">15 comments</a></span> --}}
                                {{-- <span>
                                    Tags: <a href="#"> Cardio</a>, <a href="#">Medicine</a>,
                                    <a href="#">Hospital</a>
                                </span> --}}
                            </div>
                            {!! $data->content !!}
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
        
        
    </div>
</section>


<!-- CONTACT SECTION START -->
@endsection