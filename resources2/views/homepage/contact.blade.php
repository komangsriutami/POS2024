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
                            <!-- <h6 class="text-left text-capitalized">Hubungi Kami</h6> -->
                            <h2>Kontak</h2>
                        </div>
                        <!-- <div class="link text-sm-right text-left">
                            <a href="home.html">Home <i class="ti-angle-double-right"></i></a>
                            Kontak
                        </div> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- SERVICE PAGE BANNER  END-->

<!-- CONTACT PAGE START -->

<section class="contact-page">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="contact-page-title">
                    <span>Hubungi Kami</span>
                    <h4>Jika anda memiliki pertanyan, jangan ragu untuk menghubungi kami</h4>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-5">
                <div class="contact-inform">
                    <div class="information">
                        <i class="fas">1</i>
                        <div class="">
                            <span style="margin-left: 25px;"><b>APOTEK LAVIE</b></span>
                            <br>
                            <span style="margin-left: 25px;">Alamat  : Jl. Kampus Unud No 18L Jimbaran</span>
                            <br>
                            <span style="margin-left: 25px;">No.Telp : 08996602575</span>
                        </div>
                    </div>
                    <div class="information">
                        <i class="fas">2</i>
                        <div class="">
                            <span style="margin-left: 25px;"><b>APOTEK BEKUL</b></span>
                            <br>
                            <span style="margin-left: 25px;">Alamat  : Jl. Raya Uluwatu I no 45 Jimbaran</span>
                            <br>
                            <span style="margin-left: 25px;">No.Telp : 085100722626</span>
                        </div>
                    </div>
                    <div class="information">
                        <i class="fas">3</i>
                        <div class="">
                            <span style="margin-left: 25px;"><b>APOTEK PUJAMANDALA</b></span>
                            <br>
                            <span style="margin-left: 25px;">Alamat  : Jl. Kurusetra Bualu Nusa Dua Badung</span>
                            <br>
                            <span style="margin-left: 25px;">No.Telp : 08996602585</span>
                        </div>
                    </div>
                    <div class="information">
                        <i class="fas">4</i>
                        <div class="">
                            <span style="margin-left: 25px;"><b>APOTEK PURI GADING</b></span>
                            <br>
                            <span style="margin-left: 25px;">Alamat  : Jl. Raya Uluwatu I km 19 Jimbaran</span>
                            <br>
                            <span style="margin-left: 25px;">No.Telp : 0361-4463891</span>
                        </div>
                    </div>
                    <div class="information">
                        <i class="fas">5</i>
                        <div class="">
                            <span style="margin-left: 25px;"><b>APOTEK LEGIAN 777</b></span>
                            <br>
                            <span style="margin-left: 25px;">Alamat  : Jalan Patimura No.55 Legian Kuta</span>
                            <br>
                            <span style="margin-left: 25px;">No.Telp : 08676556799</span>
                        </div>
                    </div>s
                </div>
            </div>
            <div class="col-md-7">
                <div class="row">
                    <div class="col-lg-12">
                        <form action="#">
                            <div class="contact-form">
                                <input class="text" name="nama" placeholder="Nama:">
                            </div>
                        </form>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <form action="#">
                            <div class="contact-form">
                                <input class="text" name="email" placeholder="Email:">
                            </div>
                        </form>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <form action="#">
                            <div class="contact-form">
                                <input class="text" name="judul" placeholder="Judul Pesan:">
                            </div>
                        </form>
                    </div>
                </div>
                <form action="#">
                    <div class="contact-form">
                        <textarea class="subject" name="isi_pesan" placeholder="Isi Pesan:"></textarea>
                    </div>
                    <div class="submit-btn">
                        <button class="btn"><i class="fas fa-paper-plane"></i> Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>


<!-- CONTACT PAGE END -->



<!-- MAP SECTION -->

<section class="location-section">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div id="map">

                </div>
            </div>
        </div>
    </div>
</section>

<!-- MAP SECTION -->
@endsection