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
                            {{-- <h6 class="text-left text-capitalized">Departments</h6> --}}
                            <h2>Dokter</h2>
                        </div>
                        {{-- <div class="link text-sm-right text-left">
                            <a href="home.html">Home <i class="ti-angle-double-right"></i></a>
                            Departments
                        </div> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SERVICE PAGE BANNER  END-->

<!-- DEPARTMENT SECTION START -->

<section class="department-section">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-title text-left">
                    {{-- <h2> Our <span>Departments</span></h2> --}}
                    <p>Pilih Dokter untuk malakukan reservasi</p>
                    <div class="section-border">
                        <div class="icon">
                            <i class="fas fa-tint"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-3">
            
            @foreach($data as $row)
                <div class="col-md-4 col-sm-6">
                    <div class="department-block text-left">
                        <div class="bg">
                            <img src="{{ asset('protected/public/homepage_assets/img/doctor/'.$row->img)}}" alt="">
                        </div>
                        <div class="item-block-03 text-left">
                            <div class="item-content">
                                <div class="right-side">
                                    <h5><a href="#">{{$row->nama}}</a></h5>
                                </div>
                                <p>Lorem, ipsum dolor sit amet consectetur adipisicing elit. Recusandae aut praesentium provident illum expedita tempore sapiente incidunt eum, quae optio dolorum, ducimus eos non nisi nihil rem impedit architecto! Eum?</p>
                                <div class="item-btn">
                                    <a href="{{route('doctor.select', ['doctor_id'=>$row->id])}}" class="btn btn-secondary">Pilih Dokter</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
            
        </div>
    </div>
</section>

<!-- DEPARTMENT SECTION END -->
@endsection

@push('script')

@endpush