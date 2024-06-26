@extends('frontend.v2._app')
@section('content')
<!-- Breadcrumb -->
@include('frontend.v2.components.breadcrumb', [
'title' => 'Konsultasi Apoteker',
'data' => [
[
'text' => 'Konsultasi Apoteker',
'active' => false,
'href' => request()->url()
],
[
'text' => (Request::get('nama')) ? 'Cari Apoteker' : 'Semua',
'active' => true,
'href' => request()->fullUrlWithQuery([])
],
]
])

<!-- Page Content -->
<div class="content">
    <div class="container">

        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-4 col-md-12 sidebar-right theiaStickySidebar">

                <!-- Search -->
                <div class="card search-widget">
                    <div class="card-body">
                        <form class="search-form">
                            <div class="input-group">
                                <input name="nama" type="text" placeholder="Search..." class="form-control" value="{{Request::get('nama')}}">
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

            </div>
            <!-- /Sidebar -->

            <!-- Content -->
            <div class="col-lg-8 col-md-12">
                <div class="row">
                    @foreach ($apotekers as $item)
                    <div class="col-xs-12 col-md-4 col-xs-3">
                        <div class="profile-widget">
                            <div class="doc-img">
                                <a href="doctor-profile">
                                    <img class="img-fluid" alt="User Image" onerror="this.onerror=null; this.src=`{{ asset('assets_frontend/img/doctors/doctor-01.jpg') }}`" src="{{'data:image/png;base64,'.$item->image}}">
                                    <!-- "assets_frontend/img/doctors/doctor-01.jpg" -->
                                </a>
                                <a href="javascript:void(0)" class="fav-btn">
                                    <i class="far fa-bookmark"></i>
                                </a>
                            </div>
                            <div class="pro-content">
                                <h3 class="title">
                                    <a href="doctor-profile">{{ $item->nama }} <i class="fas fa-check-circle verified"></i></a>
                                </h3>
                                <div class="rating">
                                    <i class="fas fa-star filled"></i>
                                    <i class="fas fa-star filled"></i>
                                    <i class="fas fa-star filled"></i>
                                    <i class="fas fa-star filled"></i>
                                    <i class="fas fa-star filled"></i>
                                    <span class="d-inline-block average-rating">(17)</span>
                                </div>
                                <ul class="available-info">
                                    <li>
                                        <i class="fas fa-map-marker-alt"></i> {{ $item->alamat }}
                                    </li>
                                    <li>
                                        <i class="far fa-clock"></i> Available on Fri, 22 Mar
                                    </li>
                                    <li>
                                        <i class="far fa-money-bill-alt"></i> {{ $item->fee }} - $1000
                                        <i class="fas fa-info-circle" data-toggle="tooltip" title="Lorem Ipsum"></i>
                                    </li>
                                </ul>
                                <div class="row row-sm">
                                    <div class="col-6">
                                        <a href="{{ url('/apoteker_profile/'.$item->id) }}" class="btn view-btn">Profil</a>
                                    </div>
                                    <div class="col-6">
                                        <a href="booking" class="btn book-btn">Buat Janji</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <!-- /Content -->

        </div>
    </div>
</div>
<!-- /Page Content -->

@endsection