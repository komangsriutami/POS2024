@extends('frontend.v2._app')
@section('content')
    <button onclick="topFunction()" id="tombol-atas" title="Go to top" class="fa fa-arrow-up"></button>
    <!-- Breadcrumb -->
    @include('frontend.v2.components.breadcrumb', [
        'title' => 'News List',
        'data' => [
            [
                'text' => 'News',
                'active' => false,
                'href' => request()->url()
            ],
            [
                'text' => (Request::get('title')) ? 'Cari Berita' : 'Semua',
                'active' => true,
                'href' => request()->fullUrlWithQuery([])
            ],
        ]
    ])
    <!-- Page Content -->
    <div class="content">
        <div class="container">

            <div class="row">
                @if(count($newss) > 0)
                    <div class="col-lg-8 col-md-12">

                        @foreach ($newss as $item)
                        <!-- Blog Post -->
                        <div class="blog">
                            <div class="blog-image">
                                <a href="{{ url('/homepage/news/'.$item->slug) }}">
                                    <img class="img-fluid" alt="User Image" src="{{'data:image/png;base64,'.$item->image}}">
                                </a>
                            </div>
                            <h3 class="blog-title"><a href="{{ url('/homepage/news/'.$item->slug) }}">{!! $item->title !!}</a></h3>
                            <div class="blog-info clearfix">
                                <div class="post-left">
                                    <ul>
                                        <li>
                                            <div class="post-author">
                                                <a>Dibuat Oleh : &nbsp; <span>By Admin</span></a>
                                            </div>
                                        </li>
                                        <li><i class="far fa-clock"></i>{{\Carbon\Carbon::parse($item->created_at)->format('d M Y')}} </li>
                                        <li><i class="fa fa-tags"></i>Health Tips</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="blog-content">
                                <p>{!! $item->content !!}</p>
                                <a href="{{ url('/homepage/tips/'.$item->slug) }}" class="read-more">Read More</a>
                            </div>
                        </div>
                        <!-- /Blog Post -->
                        @endforeach

                       <!-- Blog Pagination -->
                       <div class="row">
                        <div class="col-md-12">
                            <div class="blog-pagination">
                                @include('frontend.v2.components.pagination', ['paginator' => $newss])
                            </div>
                        </div>
                    </div>
                    <!-- /Blog Pagination -->

                    </div>
                @else
                    <div class="col-sm-12">
                        <p>Belum ada data tips</p>
                    </div>
                @endif
                <!-- Blog Sidebar -->
                <div class="col-lg-4 col-md-12 sidebar-right theiaStickySidebar">

                    <!-- Search -->
                    <div class="card search-widget">
                        <div class="card-body">
                            <form class="search-form">
                                <div class="input-group">
                                    <input name="title" type="text" placeholder="Search..." class="form-control" value="{{Request::get('title')}}">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- /Search -->

                    <!-- Latest Posts -->
                    <div class="card post-widget">
                        <div class="card-header">
                            <h4 class="card-title">Latest Posts</h4>
                        </div>
                        <div class="card-body">
                            <ul class="latest-posts">
                                @foreach ($listNewss as $item)
                                <li>
                                    <div class="post-thumb">
                                        <a href="{{ url('/homepage/news/'.$item->slug) }}">
                                            <img class="img-fluid" alt="User Image" src="{{'data:image/png;base64,'.$item->image}}">
                                        </a>
                                    </div>
                                    <div class="post-info">
                                        <h4>
                                            <a href="{{ url('/homepage/news/'.$item->slug) }}">{!! $item->title !!}</a>
                                        </h4>
                                        <p>{{\Carbon\Carbon::parse($item->created_at)->format('d M Y')}}</p>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <!-- /Latest Posts -->

                    <!-- Categories -->
                    {{-- <div class="card category-widget">
                        <div class="card-header">
                            <h4 class="card-title">Blog Categories</h4>
                        </div>
                        <div class="card-body">
                            <ul class="categories">
                                <li><a href="#">Cardiology <span>(62)</span></a></li>
                                <li><a href="#">Health Care <span>(27)</span></a></li>
                                <li><a href="#">Nutritions <span>(41)</span></a></li>
                                <li><a href="#">Health Tips <span>(16)</span></a></li>
                                <li><a href="#">Medical Research <span>(55)</span></a></li>
                                <li><a href="#">Health Treatment <span>(07)</span></a></li>
                            </ul>
                        </div>
                    </div> --}}
                    <!-- /Categories -->

                    <!-- Tags -->
                    {{-- <div class="card tags-widget">
                        <div class="card-header">
                            <h4 class="card-title">Tags</h4>
                        </div>
                        <div class="card-body">
                            <ul class="tags">
                                <li><a href="#" class="tag">Children</a></li>
                                <li><a href="#" class="tag">Disease</a></li>
                                <li><a href="#" class="tag">Appointment</a></li>
                                <li><a href="#" class="tag">Booking</a></li>
                                <li><a href="#" class="tag">Kids</a></li>
                                <li><a href="#" class="tag">Health</a></li>
                                <li><a href="#" class="tag">Family</a></li>
                                <li><a href="#" class="tag">Tips</a></li>
                                <li><a href="#" class="tag">Shedule</a></li>
                                <li><a href="#" class="tag">Treatment</a></li>
                                <li><a href="#" class="tag">Dr</a></li>
                                <li><a href="#" class="tag">Clinic</a></li>
                                <li><a href="#" class="tag">Online</a></li>
                                <li><a href="#" class="tag">Health Care</a></li>
                                <li><a href="#" class="tag">Consulting</a></li>
                                <li><a href="#" class="tag">Doctors</a></li>
                                <li><a href="#" class="tag">Neurology</a></li>
                                <li><a href="#" class="tag">Dentists</a></li>
                                <li><a href="#" class="tag">Specialist</a></li>
                                <li><a href="#" class="tag">Doccure</a></li>
                            </ul>
                        </div>
                    </div> --}}
                    <!-- /Tags -->

                </div>
                <!-- /Blog Sidebar -->

            </div>
        </div>

    </div>
    <!-- /Page Content -->


    <!-- Maps BWF -->
    {{-- <iframe
        src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15771.907723527886!2d115.1773851!3d-8.7882371!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0xf1a830791260f181!2sBhakti%20Widya%20Farma!5e0!3m2!1sid!2sid!4v1623567047619!5m2!1sid!2sid"
        width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy"></iframe> --}}
    <!-- /Maps BWF -->
    </div>
    <script>
        //Get the button
        var mybutton = document.getElementById("tombol-atas");
        var rootElement = document.documentElement;

        // When the user scrolls down 20px from the top of the document, show the button
        window.onscroll = function() {scrollFunction()};

        function scrollFunction() {
          if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
            mybutton.style.display = "block";
          } else {
            mybutton.style.display = "none";
          }
        }

        // When the user clicks on the button, scroll to the top of the document
        function topFunction() {
          rootElement.scrollTo({
            top: 0,
            behavior: "smooth"
          })
        }
    </script>
    <style type="text/css">
        html, body {
          scroll-behavior: smooth;
          height: 100%;
        }
        #tombol-atas {
          display: none;
          position: fixed;
          bottom: 20px;
          right: 30px;
          z-index: 99;
          font-size: 18px;
          border: 1px solid #4db6ac;
          outline: none;
          background-color: white;
          color: #4db6ac;
          cursor: pointer;
          padding: 15px;
          border-radius: 5px;
        }
        #tombol-atas:hover{
          background-color: #4db6ac;
          color: white;
        }
    </style>
@endsection

@section('script')
    <!-- ini diisi jika ada script tambahan yang hanya berlaku pada page ini-->

    <link rel="stylesheet" href="https://npmcdn.com/leaflet@1.0.0-rc.2/dist/leaflet.css" />
	<script src="https://npmcdn.com/leaflet@1.0.0-rc.2/dist/leaflet.js"></script>
<script>
navigator.geolocation.getCurrentPosition(function(location) {
    var latlng = new L.LatLng(location.coords.latitude, location.coords.longitude);
    var getLoc = "https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat="+location.coords.latitude+"&lon="+location.coords.longitude;
    $.getJSON(getLoc, function(data) {
        console.log(data);
        if(data.address.village != null){
            $("#lokasi").val(data.address.village);
            $("#cek-lokasi").addClass("focused");
        }else if(data.address.city != null){
            $("#lokasi").val(data.address.city);
            $("#cek-lokasi").addClass("focused");
        }else{
            $("#lokasi").val("");
        }
    });
});
</script>
@endsection
