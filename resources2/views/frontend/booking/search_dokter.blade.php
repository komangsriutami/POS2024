@extends('rekammedis.frontend.layout_frontend.app')
@section('content')

    <!-- Breadcrumb -->
    <!-- Breadcrumb -->
    <div class="breadcrumb-bar">
    <div class="container-fluid">
     <div class="row align-items-center">
      <div class="col-md-8 col-12">
       <nav aria-label="breadcrumb" class="page-breadcrumb">
        <ol class="breadcrumb">
         <li class="breadcrumb-item"><a href="index">Home</a></li>
         <li class="breadcrumb-item active" aria-current="page">Search</li>
        </ol>
       </nav>
       <h2 class="breadcrumb-title">2245 matches found for : Dentist In Bangalore</h2>
      </div>
      <div class="col-md-4 col-12 d-md-block d-none">
       
      </div>
     </div>
    </div>
   </div>
   <!-- /Breadcrumb -->
    <!-- /Breadcrumb -->

    <!-- Page Content -->
    <div class="content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-md-12 col-lg-4 col-xl-3 theiaStickySidebar">

                    <!-- Search Filter -->
                    <form method="POST" action="{{ url('/search_dokter') }}">
                        @csrf
                        <div class="card search-filter">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Sort</h4>
                            </div>
                            <div class="card-body">
                                <span class="sort-title">Sort by</span>
                                <span class="sortby-fliter">
                                    <select name="sorting" class="select" id="sortSelect">
                                        <option value=-1 disabled selected>Select</option>
                                        <option value=0 class="sorting">Nama A-Z</option>
                                        <option value=1 class="sorting">Nama Z-A</option>
                                        <option value=2 class="sorting">Ulasan</option>
                                        <option value=3 class="sorting">Harga Tertinggi</option>
                                        <option value=4 class="sorting">Harga Terendah</option>
                                    </select>
                                </span>
                            </div>
                        </div>

                        <div class="card search-filter">
                            
                            <div class="card-header">
                                <h4 class="card-title mb-0">Search Filter</h4>
                            </div>
                            <div class="card-body">
                                    <div class="form-group form-focus">
                                        <input type="text" name="nama" class="form-control floating">
                                        <label class="focus-label"><img src="assets_frontend/img/search.png">Nama tenaga Medis</label>
                                    </div>
                                    <div class="form-group form-focus">
                                        @if(count($spesialiss) > 0)
                                        <select class="form-control custom-select floating" name="spesialis" required>
                                            <option selected value="-1">Semua Spesialis</option>
                                                @foreach($spesialiss as $item)
                                                    <option class="form-control" value="{!! $item->id !!}">{!! $item->spesialis !!}</option>
                                                @endforeach
                                        </select>
                                        <label class="focus-label"><img src="assets_frontend/img/stethoscope.png">Pilih Spesialis</label>
                                        @endif
                                    </div>
                                    <div class="form-group form-focus">
                                        <input type="text" class="form-control floating" name="lokasi">
                                        <label class="focus-label"><img src="assets_frontend/img/location.png">Lokasi</label>
                                    </div>
                                <div class="btn-search">
                                    <button type="submit" class="btn btn-block">Search</button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <!-- /Search Filter -->

                </div>

                <div class="col-md-12 col-lg-8 col-xl-9">

                    <!-- Doctor Widget -->
                    @foreach($dokters as $item)
                    <div class="card">
                        <div class="card-body">
                            <div class="doctor-widget">
                                <div class="doc-info-left">
                                    <div class="doctor-img">
                                        <a href="doctor-profile">
                                            <img onerror="this.onerror=null; this.src='assets_frontend/img/doctors/doctor-thumb-01.jpg'" src="userfiles/dokter/{{ $item->img }}" class="img-fluid"
                                                alt="User Image">
                                        </a>
                                    </div>
                                    <div class="doc-info-cont">
                                        <h4 class="doc-name"><a href="doctor-profile">{{ $item->nama }}</a></h4>
                                        <p class="doc-speciality">{{ $item->spesialis }}</p>
                                        <div class="rating">
                                            <i class="fas fa-star filled"></i>
                                            <i class="fas fa-star filled"></i>
                                            <i class="fas fa-star filled"></i>
                                            <i class="fas fa-star filled"></i>
                                            <i class="fas fa-star"></i>
                                            <span class="d-inline-block average-rating">(17)</span>
                                        </div>
                                        <div class="clinic-details">
                                            <p class="doc-location"><i class="fas fa-map-marker-alt"></i> Alamat Dokter: {{ $item->alamat }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="doc-info-right">
                                    <div class="clini-infos">
                                        <ul>
                                            <li><i class="far fa-comment"></i> 17 Feedback</li>
                                            <li><i class="fas fa-map-marker-alt"></i> Alamat Apotek: {{$item->alamatApotek}}</li>
                                            <li><i class="far fa-money-bill-alt"></i> Mulai dari: Rp {{ $item->fee }},00 <i
                                                    class="fas fa-info-circle" data-toggle="tooltip"
                                                    title="Lorem Ipsum"></i> </li>
                                        </ul>
                                    </div>
                                    <div class="clinic-booking">
                                        <a class="view-pro-btn" href="doctor-profile">View Profile</a>
                                        <a class="apt-btn" href="{{url('/book_dokter/'.$item->id)}}">Book Appointment</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    <!-- /Doctor Widget -->

                    <div class="d-flex justify-content-center align-items-center">
                        <h5 style="margin-right:10px;"> Halaman {{$dokters->currentPage()}} dari {{ $dokters->count() }} </h5>
                        {{ $dokters->links() }}
                    </div>
                </div>
            </div>

        </div>

    </div>
    <!-- /Page Content -->
    </div>
@endsection
@section('script')
    <!-- ini diisi jika ada script tambahan yang hanya berlaku pada page ini-->
@endsection
