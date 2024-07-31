@extends('frontend.v2._app')
@section('content')
    <button onclick="topFunction()" id="tombol-atas" title="Go to top" class="fa fa-arrow-up"></button>

    <!-- Breadcrumb -->
    @include('frontend.v2.components.breadcrumb', [
        'title' => 'Hubungi Kami',
        'data' => [
            [
                'text' => 'Hubungi Kami',
                'active' => false,
                'href' => request()->url()
            ],
            [
                'text' => 'Kontak',
                'active' => true,
                'href' => request()->url()
            ],
        ]
    ])
    <!-- /Breadcrumb -->

    <!-- Page Content -->
    <div class="content">
        <div class="container">
            <!-- Contact -->
                <section class="comp-section">
                    <div class="comp-header">
                        <h3 class="comp-title">Kontak</h3>
                        <div class="line"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Hubungi Kami</h4>
                                </div>
                                <div class="card-body">
                                    <div class="account-content">
                                        <div class="row align-items-center justify-content-center">
                                            <div class="col-md-7 col-lg-6 login-left">
                                                <img src="../assets_frontend/img/login-banner.png" class="img-fluid" alt="Doccure Login">	
                                            </div>
                                            <div class="col-md-12 col-lg-6 login-right">
                                                <div class="login-header">
                                                    <h3>Hubung <span> Kami</span></h3>
                                                </div>
                                                <form action="index">
                                                    <label for=""><i class="fas fa-map-marker-alt"></i> Alamat</label>
                                                    <p>Jl. Raya Kampus Unud No.18L, Jimbaran, Kec. Kuta Sel., Kabupaten Badung, Bali 80361</p>

                                                    <label for=""><i class="fas fa-phone-alt"></i> No Telepon</label>
                                                    <p> 62812345678</p>

                                                    <label for=""><i class="fas fa-envelope"></i> Email</label>
                                                    <p> apotekbwf@gmail.com</p>

                                                    <div class="login-or">
                                                        <span class="or-line"></span>
                                                        <span class="span-or">|</span>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <!-- /Contact -->
        </div>
    </div>
    <!-- /Page Content -->


    <!-- Maps BWF -->
    <iframe
        src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15771.907723527886!2d115.1773851!3d-8.7882371!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0xf1a830791260f181!2sBhakti%20Widya%20Farma!5e0!3m2!1sid!2sid!4v1623567047619!5m2!1sid!2sid"
        width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
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
