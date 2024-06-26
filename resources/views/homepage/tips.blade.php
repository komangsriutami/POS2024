@extends('homepage.layouts.app')

@section('content')
<div class="page-banner">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="page-banner-content">
                    <div class="d-sm-flex justify-content-between align-items-center">
                        <div class="title">
                            <!-- <h6 class="text-left text-capitalized">Tips Kesehatan</h6> -->
                            <h2>Tips</h2>
                        </div>
                        <!-- <div class="link text-sm-right text-left">
                            <a href="home.html">Home <i class="ti-angle-double-right"></i></a>
                            Tips
                        </div> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SERVICE PAGE BANNER  END-->

<!-- BLOG PAGE SECTION -->

<section class="blog-page blog-page3">
    <div class="container">
        <div class="masonary">

            @foreach($data as $item)
                <div class="grid-item">
                    <div class="blog-item">
                        <div class="blog-bg">
                            <img src="{{ asset($item->displayImage())}}" alt="1">
                            <div class="overlay">
                                <i class="far fa-image"></i>
                            </div>
                        </div>
                        <div class="blog-content text-left">
                            <h4><a href="{{route('tips_details', $item->slug)}}">{{$item->title}}</a></h4>
                            <div class="">
                                <span> {{\Carbon\Carbon::parse($item->created_at)->format('F d, Y')}} </span>
                                {{-- <span><a href="#">15 comments</a></span> --}}
                            </div>
                            <p>{!! \Str::limit( strip_tags($item->content), 100) !!}</p>
                            <div class="blog-btn">
                                <a href="{{route('tips_details', $item->slug)}}" class="btn btn-secondary">Read More<i class="fas fa-long-arrow-alt-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- <div class="grid-item">
                <div class="blog-item">
                    <div class="blog-bg round">
                        <img src="{{ asset('protected/public/homepage_assets/img/Blog/3.jpg')}}" alt="3">
                        <div class="fancy-box">
                            <figure class="video-box round-os">
                                <a data-fancybox data-width="640" data-height="360" class="video-btn"
                                    href="https://youtu.be/MPUBSZYESgU"><i class="
                                fas fa-play"></i></a>
                            </figure>
                        </div>
                    </div>
                    <div class="blog-content text-left">
                        <h4><a href="#">That also the leap into electronic type</a></h4>
                        <div class="">
                            <span> Post by <a href="#">Admin</a> 02 March 2016 </span>
                            <span><a href="#">15 comments</a></span>
                        </div>
                        <p>
                            Lorem ipsum dolor sit amet, consectetur adipiscing
                        </p>
                        <div class="blog-btn">
                            <a href="#" class="btn btn-secondary">Read More<i
                                    class="fas fa-long-arrow-alt-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid-item">
                <div class="blog-item">
                    <div class="blog-bg">
                        <img src="{{ asset('protected/public/homepage_assets/img/Blog/5.jpg')}}" alt="1">
                        <div class="overlay">
                            <i class="far fa-image"></i>
                        </div>
                    </div>
                    <div class="blog-content text-left">
                        <h4><a href="#">The latest diet pills for men in middle age on the importent itme</a>
                        </h4>
                        <div class="">
                            <span> Post by <a href="#">Admin</a> 02 March 2016 </span>
                            <span><a href="#">15 comments</a></span>
                        </div>
                        <p>
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla ullamcorper sagittis
                            odio, vitae finibus diam tincidunt eget. Nullam sit amet nunc felis. Sed lorem est,
                            vestibulum enim non, fringilla malesuada urna. Aenean sodales est vel odio
                            volutpat, in pellentesque.
                        </p>
                        <div class="blog-btn">
                            <a href="#" class="btn btn-secondary">Read More<i
                                    class="fas fa-long-arrow-alt-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid-item">
                <div class="blog-item">
                    <div class="blog-bg">
                        <img src="{{ asset('protected/public/homepage_assets/img/Blog/7.jpg')}}" alt="1">
                        <div class="overlay">
                            <i class="far fa-image"></i>
                        </div>
                    </div>
                    <div class="blog-content text-left">
                        <h4><a href="#">The latest diet pills for men in middle</a>
                        </h4>
                        <div class="">
                            <span> Post by <a href="#">Admin</a> 02 March 2016 </span>
                            <span><a href="#">15 comments</a></span>
                        </div>
                        <p>
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla ullamcorper sagittis
                            odio, vitae finibus diam tincidunt eget. Nullam sit amet nunc felis. Sed lorem est,
                            vestibulum enim non, fringilla malesuada urna. Aenean sodales est vel odio
                            volutpat, in pellentesque.
                        </p>
                        <div class="blog-btn">
                            <a href="#" class="btn btn-secondary">Read More<i
                                    class="fas fa-long-arrow-alt-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid-item">
                <div class="blog-item">
                    <div class="blog-bg">
                        <div class="owl-carousel blog-slider">
                            <div class="item">
                                <img src="{{ asset('protected/public/homepage_assets/img/Blog/2.jpg')}}" alt="2">
                            </div>
                            <div class="item">
                                <img src="{{ asset('protected/public/homepage_assets/img/Blog/2.jpg')}}" alt="2">
                            </div>
                        </div>
                        <div class="overlay">
                            <i class="fas fa-link"></i>
                        </div>
                    </div>
                    <div class="blog-content text-left">
                        <h4><a href="#">Amazing experiments in the laboratory</a></h4>
                        <div class="">
                            <span> Post by <a href="#">Admin</a> 02 March 2016 </span>
                            <span><a href="#">15 comments</a></span>
                        </div>
                        <p>
                            tortor sollicitudin. Donec dolor nisl, cursus at luctus non, vehicula vel mauris.
                            Quisque pretium nisi orci, ut auctor eros fringilla ut.
                        </p>
                        <div class="blog-btn">
                            <a href="#" class="btn btn-secondary">Read More<i
                                    class="fas fa-long-arrow-alt-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid-item">
                <div class="blog-item">
                    <div class="blog-bg">
                        <img src="{{ asset('protected/public/homepage_assets/img/Blog/4.jpg')}}" alt="1">
                        <div class="overlay">
                            <i class="far fa-image"></i>
                        </div>
                    </div>
                    <div class="blog-content text-left">
                        <h4><a href="#">The latest diet pills for men in middle</a></h4>
                        <div class="">
                            <span> Post by <a href="#">Admin</a> 02 March 2016 </span>
                            <span><a href="#">15 comments</a></span>
                        </div>
                        <p>
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla ullamcorper
                            sagittis, vitae finibus diam.
                        </p>
                        <div class="blog-btn">
                            <a href="#" class="btn btn-secondary">Read More<i
                                    class="fas fa-long-arrow-alt-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid-item">
                <div class="blog-item">
                    <div class="blog-bg">
                        <div class="owl-carousel blog-slider">
                            <div class="item">
                                <img src="{{ asset('protected/public/homepage_assets/img/Blog/6.jpg')}}" alt="2">
                            </div>
                            <div class="item">
                                <img src="{{ asset('protected/public/homepage_assets/img/Blog/6.jpg')}}" alt="2">
                            </div>
                        </div>
                        <div class="overlay">
                            <i class="fas fa-link"></i>
                        </div>
                    </div>
                    <div class="blog-content text-left">
                        <h4><a href="#">Amazing experiments in the laboratory</a></h4>
                        <div class="">
                            <span> Post by <a href="#">Admin</a> 02 March 2016 </span>
                            <span><a href="#">15 comments</a></span>
                        </div>
                        <p>
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla ullamcorper sagittis
                            odio, vitae finibus diam tincidunt eget. Nullam sit amet nunc felis. Sed lorem est,
                            vestibulum nec enim non, fringilla malesuada urna. Aenean sodales est vel odio
                            volutpat,
                            in pellentesque. Donec dolor nisl, at non, vehicula
                            vel mauris. Quisque pretium nisi orci, ut auctor eros fringilla ut. Vivamus sit amet
                            dolor sapien.
                        </p>
                        <div class="blog-btn">
                            <a href="#" class="btn btn-secondary">Read More<i
                                    class="fas fa-long-arrow-alt-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid-item">
                <div class="blog-item">
                    <div class="blog-bg round">
                        <img src="{{ asset('protected/public/homepage_assets/img/Blog/3.jpg')}}" alt="3">
                        <div class="fancy-box">
                            <figure class="video-box round-os">
                                <a data-fancybox data-width="640" data-height="360" class="video-btn"
                                    href="https://youtu.be/MPUBSZYESgU"><i class="
                                fas fa-play"></i></a>
                            </figure>
                        </div>
                    </div>
                    <div class="blog-content text-left">
                        <h4><a href="#">That also the leap into electronic type</a></h4>
                        <div class="">
                            <span> Post by <a href="#">Admin</a> 02 March 2016 </span>
                            <span><a href="#">15 comments</a></span>
                        </div>
                        <p>
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla ullamcorper sagittis
                            odio, vitae finibus diam tincidunt eget. Nullam sit amet nunc felis. Sed lorem est,
                            vestibulum enim non, fringilla malesuada urna. Aenean sodales est vel odio volutpat,
                            in pellentesque.
                        </p>
                        <div class="blog-btn">
                            <a href="#" class="btn btn-secondary">Read More<i
                                    class="fas fa-long-arrow-alt-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid-item">
                <div class="blog-item">
                    <div class="blog-bg round">
                        <img src="{{ asset('protected/public/homepage_assets/img/Blog/3.jpg')}}" alt="3">
                        <div class="fancy-box">
                            <figure class="video-box round-os">
                                <a data-fancybox data-width="640" data-height="360" class="video-btn"
                                    href="https://youtu.be/MPUBSZYESgU"><i class="
                                fas fa-play"></i></a>
                            </figure>
                        </div>
                    </div>
                    <div class="blog-content text-left">
                        <h4><a href="#">That also the leap into electronic type</a></h4>
                        <div class="">
                            <span> Post by <a href="#">Admin</a> 02 March 2016 </span>
                            <span><a href="#">15 comments</a></span>
                        </div>
                        <p>
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla ullamcorper
                            sagittis odio, vitae finibus diam tincidunt eget. Nullam sit amet nunc
                            felis. Sed lorem est, vestibulum enim non, fringilla malesuada urna.
                            Aenean sodales est vel odio volutpat, in pellentesque.
                        </p>
                        <div class="blog-btn">
                            <a href="#" class="btn btn-secondary">Read More<i
                                    class="fas fa-long-arrow-alt-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid-item">
                <div class="blog-item">
                    <div class="blog-bg">
                        <div class="owl-carousel blog-slider">
                            <div class="item">
                                <img src="{{ asset('protected/public/homepage_assets/img/Blog/8.jpg')}}" alt="2">
                            </div>
                            <div class="item">
                                <img src="{{ asset('protected/public/homepage_assets/img/Blog/8.jpg')}}" alt="2">
                            </div>
                        </div>
                        <div class="overlay">
                            <i class="fas fa-link"></i>
                        </div>
                    </div>
                    <div class="blog-content text-left">
                        <h4><a href="#">Amazing experiments in the laboratory</a></h4>
                        <div class="">
                            <span> Post by <a href="#">Admin</a> 02 March 2016 </span>
                            <span><a href="#">15 comments</a></span>
                        </div>
                        <p>
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla ullamcorper sagittis
                            odio, vitae finibus diam tincidunt eget. Nullam sit amet nunc felis. Sed lorem est,
                            vestibulum enim non, fringilla malesuada urna. Aenean sodales est vel odio
                            volutpat, in pellentesque.
                        </p>
                        <div class="blog-btn">
                            <a href="#" class="btn btn-secondary">Read More<i
                                    class="fas fa-long-arrow-alt-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid-item">
                <div class="blog-item">
                    <div class="blog-bg round">
                        <img src="{{ asset('protected/public/homepage_assets/img/Blog/3.jpg')}}" alt="3">
                        <div class="fancy-box">
                            <figure class="video-box round-os">
                                <a data-fancybox data-width="640" data-height="360" class="video-btn"
                                    href="https://youtu.be/MPUBSZYESgU"><i class="
                                fas fa-play"></i></a>
                            </figure>
                        </div>
                    </div>
                    <div class="blog-content text-left">
                        <h4><a href="#">That also the leap into electronic type</a></h4>
                        <div class="">
                            <span> Post by <a href="#">Admin</a> 02 March 2016 </span>
                            <span><a href="#">15 comments</a></span>
                        </div>
                        <p>
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla ullamcorper
                            sagittis odio, vitae finibus diam tincidunt eget. Nullam sit amet nunc
                            felis. Sed lorem est, vestibulum enim non, fringilla malesuada urna.
                            Aenean sodales est vel odio volutpat, in pellentesque.
                        </p>
                        <div class="blog-btn">
                            <a href="#" class="btn btn-secondary">Read More<i
                                    class="fas fa-long-arrow-alt-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid-item">
                <div class="blog-item">
                    <div class="blog-bg">
                        <div class="owl-carousel blog-slider">
                            <div class="item">
                                <img src="{{ asset('protected/public/homepage_assets/img/Blog/9.jpg')}}" alt="2">
                            </div>
                            <div class="item">
                                <img src="{{ asset('protected/public/homepage_assets/img/Blog/9.jpg')}}" alt="2">
                            </div>
                        </div>
                        <div class="overlay">
                            <i class="fas fa-link"></i>
                        </div>
                    </div>
                    <div class="blog-content text-left">
                        <h4><a href="#">Amazing experiments in the laboratory</a></h4>
                        <div class="">
                            <span> Post by <a href="#">Admin</a> 02 March 2016 </span>
                            <span><a href="#">15 comments</a></span>
                        </div>
                        <p>
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla ullamcorper sagittis
                            odio, vitae finibus diam tincidunt eget. Nullam sit amet nunc felis. Sed lorem est,
                            vestibulum enim non, fringilla malesuada urna. Aenean sodales est vel odio
                            volutpat, in pellentesque.
                        </p>
                        <div class="blog-btn">
                            <a href="#" class="btn btn-secondary">Read More<i
                                    class="fas fa-long-arrow-alt-right"></i></a>
                        </div>
                    </div>
                </div>
            </div> --}}

        </div>
    </div>
</section>



<!-- CONTACT SECTION START -->
@endsection