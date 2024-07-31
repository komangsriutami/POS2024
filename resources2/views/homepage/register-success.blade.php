@extends('homepage.layouts.app')

@section('content')
<!-- SERVICE PAGE BANNER -->

{{-- <div class="page-banner">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="page-banner-content">
                    <div class="d-sm-flex justify-content-between align-items-center">
                        <div class="title">
                            <h6 class="text-left text-capitalized">Jadwal</h6>
                            <h2>Jadwal</h2>
                        </div>
                        <div class="link text-sm-right text-left">
                            <a href="home.html">Home <i class="ti-angle-double-right"></i></a>
                            Jadwal
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> --}}

<!-- SERVICE PAGE BANNER  END-->

<!-- DEPARTMENT SECTION START -->

<section class="sign-up-section">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-title text-left">
                    <h2>Registrasi berhasil</h2>
                    <p>
                        Registrasi berhasil dilakukan.
                    </p>
                    @if(Session::has('message'))
                        <div class="alert with-close alert-info mt-2">
                            {{Session::get('message')}}
                        </div>
                    @endif
                    <div class="section-border">
                        <div class="icon">
                            <i class="fas fa-tint"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-3">
            
            <div class="col-md-12">
                <div class="image">
                    <img src="{{ asset('protected/public/homepage_assets/img/register/1.jpg')}}" alt="">
                </div>
            </div>
            
        </div>
    </div>
</section>

<!-- DEPARTMENT SECTION END -->
@endsection

@push('script')
@endpush