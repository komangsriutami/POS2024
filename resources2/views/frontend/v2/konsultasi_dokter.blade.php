@extends('frontend.v2._app')
@section('content')
<!-- Breadcrumb -->
@include('frontend.v2.components.breadcrumb', [
'title' => 'Konsultasi Dokter',
'data' => [
[
'text' => 'Konsultasi Dokter',
'active' => false,
'href' => request()->url()
],
[
'text' => (Request::get('nama') || Request::get('spesialis')) ? 'Cari Dokter' : 'Semua',
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
                    <div class="card-header">
                        <h4 class="card-title">Cari</h4>
                    </div>
                    <div class="card-body">
                        <form>
                            <input name="spesialis" type="hidden" value="{{Request::get('spesialis')}}">
                            <div class="form-group mb-2">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text d-flex justify-content-center text-secondary" style="width: 35px;"><i class="fas fa-sm fa-search"></i><span>
                                    </div>
                                    <input type="text" class="form-control" placeholder="Cari nama tenaga medis" name="nama" value="{{Request::get('nama')}}">
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text d-flex justify-content-center text-secondary" style="width: 35px;"><i class="fas fa-sm fa-map-marker-alt"></i><span>
                                    </div>
                                    <input type="text" class="form-control" placeholder="Cari lokasi" name="lokasi" value="{{Request::get('lokasi')}}">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fa fa-search mr-1"></i>
                                <span>Cari</span>
                            </button>
                        </form>
                    </div>
                </div>
                <!-- /Search -->

                <!-- Specialist -->
                <div class="card category-widget">
                    <div class="card-header">
                        <h4 class="card-title">Spesialis</h4>
                    </div>
                    <div class="card-body">
                        <ul class="categories">
                            @foreach ($spesialiss as $item)
                            <li>
                                <a href="{{ request()->fullUrlWithQuery(['spesialis'=>$item->id]) }}">
                                    {{ $item->spesialis }}
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <!-- /Specialist -->

            </div>
            <!-- /Sidebar -->

            <!-- Content -->
            <div class="col-lg-8 col-md-12">
                <div class="row">
                    @foreach ($dokters as $item)
                    <div class="col-xs-12 col-md-4 col-xs-3">
                        <div class="profile-widget">
                            <div class="doc-img">
                                <img class="img-fluid" alt="User Image" onerror="this.onerror=null; this.src=`{{ asset('assets_frontend/img/doctors/doctor-01.jpg') }}`" src="{{'data:image/png;base64,'.$item->image}}">
                                <a class="fav-btn">
                                    <i class="far fa-bookmark"></i>
                                </a>
                            </div>
                            <div class="pro-content">
                                <h3 class="title">
                                    {{ $item->nama }} <i class="fas fa-check-circle verified"></i>
                                </h3>
                                <p class="speciality">{{ $item->spesialis }}</p>
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
                                        <a href="{{ url('/dokter_profile/'.$item->id) }}" class="btn view-btn">Profil</a>
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

                @include('frontend.v2.components.pagination', ['paginator' => $dokters])
            </div>
            <!-- /Content -->

        </div>
    </div>
</div>
<!-- /Page Content -->

@endsection