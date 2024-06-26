@extends('homepage.layouts.app')

@section('content')
<div class="page-banner">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="page-banner-content">
                    <div class="d-sm-flex justify-content-between align-items-center">
                        <div class="title">
                            <!-- <h6 class="text-left text-capitalized">About Us</h6> -->
                            <h2>Tantang Kami</h2>
                        </div>
                        <!-- <div class="link text-sm-right text-left">
                            <a href="home.html">Home <i class="ti-angle-double-right"></i></a>
                            About
                        </div> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SERVICE PAGE BANNER  END-->

<!-- FAQ START -->

<section class="accordion-section">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-xl-6 col-md-6">
                <div class="section-title accordion-title text-left">
                    <p>
                        Lorem ipsum dolor sit amet consectetur
                        adipisicing elit. Reiciendis
                        quasi architecto accusantium iste
                        mollitia dolore alias sit iure
                        illo dolor doloremque voluptate eos
                        facere quia consequuntur, animi,
                        ad aliquid ipsa vitae deserunt et
                        exercitationem ut sapiente. Quia
                        commodi labore necessitatibus optio
                        amet, minima quis dolor neque
                        eum tempora nostrum asperiores?
                    </p>
                </div>
                <!--Accordion wrapper-->
                <div class="accordion md-accordion" id="accordionEx" role="tablist" aria-multiselectable="true">

                    <!-- Accordion card -->
                    <div class="card">

                        <!-- Card header -->
                        <div class="card-header" role="tab" id="headingOne1">
                            <div class="accordion-icon">
                                <a data-toggle="collapse" data-parent="#accordionEx" href="#collapseOne1"
                                    aria-expanded="true" aria-controls="collapseOne1">
                                    <h6 class="mb-0">
                                        <i class="fas fa-arrow-circle-right rotate-icon"></i>
                                        Visi
                                    </h6>
                                </a>
                            </div>
                        </div>

                        <!-- Card body -->
                        <div id="collapseOne1" class="collapse show" role="tabpanel"
                            aria-labelledby="headingOne1" data-parent="#accordionEx">
                            <div class="card-body">
                                <p>
                                    Lorem ipsum dolor sit amet consectetur
                                    adipisicing elit. Reiciendis
                                    quasi architecto accusantium iste
                                    mollitia dolore alias sit iure
                                    illo dolor doloremque voluptate eos
                                    facere quia consequuntur, animi,
                                    ad aliquid ipsa vitae deserunt et
                                    exercitationem ut sapiente. Quia
                                    commodi labore necessitatibus optio
                                    amet, minima quis dolor neque
                                    eum tempora nostrum asperiores?
                                </p>
                            </div>
                        </div>

                    </div>
                    <!-- Accordion card -->

                    <!-- Accordion card -->
                    <div class="card">

                        <!-- Card header -->
                        <div class="card-header" role="tab" id="headingTwo2">
                            <div class="accordion-icon">
                                <a class="collapsed" data-toggle="collapse" data-parent="#accordionEx"
                                    href="#collapseTwo2" aria-expanded="false" aria-controls="collapseTwo2">
                                    <h6 class="mb-0">
                                        <i class="fas fa-arrow-circle-right rotate-icon"></i>
                                        Misi
                                    </h6>
                                </a>
                            </div>
                        </div>

                        <!-- Card body -->
                        <div id="collapseTwo2" class="collapse" role="tabpanel" aria-labelledby="headingTwo2"
                            data-parent="#accordionEx">
                            <div class="card-body">
                                <p>
                                    Lorem ipsum dolor sit amet consectetur
                                    adipisicing elit. Reiciendis
                                    quasi architecto accusantium iste
                                    mollitia dolore alias sit iure
                                    illo dolor doloremque voluptate eos
                                    facere quia consequuntur, animi,
                                    ad aliquid ipsa vitae deserunt et
                                    exercitationem ut sapiente. Quia
                                    commodi labore necessitatibus optio
                                    amet, minima quis dolor neque
                                    eum tempora nostrum asperiores?
                                </p>
                            </div>
                        </div>

                    </div>
                    <!-- Accordion card -->
                </div>
                <!-- Accordion wrapper -->
            </div>
            <div class="col-xl-6 col-md-6">
                <div class="background-bg round">
                    <img src="{{ asset('assets/dist/img/logo.png')}}" alt="3">
                    <div class="fancy-box">
                        <figure class="video-box round-os">
                            <a data-fancybox data-width="640" data-height="360" class="video-btn"
                                href="https://www.youtube.com/watch?v=NpsxvvhvhxY"><i class="
                            fas fa-play"></i></a>
                        </figure>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ END -->

<!-- DEPARTMENT SECTION START -->

<section class="department-section">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-title text-center">
                    <h2> Layanan <span>Kami</span></h2>
                    <p>
                        Melayani dengan sepenuh hati
                    </p>
                    <div class="section-border">
                        <div class="icon">
                            <i class="fas fa-tint"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-3 col-sm-6">
                <div class="item-block-03 text-center">
                    <div class="item-content">
                        <div class="">
                            <div class="icon">
                                <span class="xicon-brain"></span>
                            </div>
                            <h5><a href="#">Konsultasi Obat</a></h5>
                        </div>
                        <p>
                            Aenean commodo ligula eget dolor. Aenean massa. Cum
                            sociis natoque penatibus et
                            magnis dis Nullam sit amet nunc felis,
                        </p>
                        <div class="item-btn">
                            <a href="#" class="btn btn-secondary">read more</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="item-block-03 text-center">
                    <div class="item-content">
                        <div class="">
                            <div class="icon">
                                <span class="xicon-eye"></span>
                            </div>
                            <h5><a href="#">Tes Asam Urat</a></h5>
                        </div>
                        <p>
                            Aenean commodo ligula eget dolor. Aenean massa. Cum
                            sociis natoque penatibus et
                            magnis dis Nullam sit amet nunc felis,
                        </p>
                        <div class="item-btn">
                            <a href="#" class="btn btn-secondary">read more</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="item-block-03 text-center">
                    <div class="item-content">
                        <div class="">
                            <div class="icon">
                                <span class="xicon-broken-bone"></span>
                            </div>
                            <h5><a href="#">Tes Gula Darah</a></h5>
                        </div>
                        <p>
                            Aenean commodo ligula eget dolor. Aenean massa. Cum
                            sociis natoque penatibus et
                            magnis dis Nullam sit amet nunc felis,
                        </p>
                        <div class="item-btn">
                            <a href="#" class="btn btn-secondary">read more</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="item-block-03 text-center">
                    <div class="item-content">
                        <div class="">
                            <div class="icon">
                                <span class="xicon-tooth"></span>
                            </div>
                            <h5><a href="#">Tes Kolesterol</a></h5>
                        </div>
                        <p>
                            Aenean commodo ligula eget dolor. Aenean massa. Cum
                            sociis natoque penatibus et
                            magnis dis Nullam sit amet nunc felis,
                        </p>
                        <div class="item-btn">
                            <a href="#" class="btn btn-secondary">read more</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- DEPARTMENT SECTION END -->
@endsection