@extends('homepage.layouts.app')

@section('content')
<!-- BANNER START -->
<div class="banner">
    <div class="slider-carousel owl-carousel">
        <div class="single-slide" style="background-image: url({{ asset('protected/public/homepage_assets/img/banner/1.jpg')}})">
            <div class="container">
                <div class="slide-caption">
                    <h1>Best <br> Medical Center</h1>
                    <p>
                        Medical Doctors perform health assessments, run diagnostic tests, prescribe <br>
                        medication, create treatment plans and provide health and wellness <br> advice to
                        patients. Medical Doctors can specialize in specific.

                    </p>
                    <div class="banner-btn ">
                        <a href="#" class="btn btn-secondary-filled">Learn more</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="single-slide" style="background-image: url({{ asset('protected/public/homepage_assets/img/banner/3.jpg')}})">
            <div class="container">
                <div class="slide-caption text-sm-right text-left">
                    <h1>We Care <br> For Your Health </h1>
                    <p>
                        Medical Doctors perform health assessments, run diagnostic tests, prescribe <br>
                        medication, create treatment plans and provide health and wellness <br> advice to
                        patients. Medical Doctors can specialize in specific.

                    </p>
                    <div class="banner-btn">
                        <a href="#" class="btn btn-secondary-filled">Learn more</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="single-slide" style="background-image: url({{ asset('protected/public/homepage_assets/img/banner/2.jpg')}})">
            <div class="container">
                <div class="slide-caption">
                    <h1>Protect You<br> & Your Family </h1>
                    <p>
                        Medical Doctors perform health assessments, run diagnostic tests, prescribe <br>
                        medication, create treatment plans and provide health and wellness <br> advice to
                        patients. Medical Doctors can specialize in specific.

                    </p>
                    <div class="banner-btn">
                        <a href="#" class="btn btn-secondary-filled">Learn more</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Slider section end -->


<!-- APPOINTMENT START -->

<section class=" appointment-section">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="appointment-title text-center">
                    <h2>Welcome to the <span>Apotek <strong>BWF Group</strong></span></h2>
                    <p>
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                        Curabitur at blandit dui, ut
                        consequat ex. Vestibulum at ullamcorper leo. nec vehicula
                        leo.<br> Praesent libero
                        odio, gravida ut velit et, mattis laoreet metus. Lorem ipsum
                        dolor sit amet, consectetur
                        adipiscing elitn <br>. Curabitur at blandit dui, ut consequat
                        ex. Vestibulum at
                        ullamcorper leo, nec vehicula leo.
                    </p>
                    <!-- <div class="section-btn">
                        <a href="javascript:void(0);" class="apointment-btn btn btn-secondary">Appointment<i
                                class="ti-angle-double-down"></i></a>
                    </div> -->
                </div>
            </div>
        </div>
        <div class="row align-center">
            <div class="col-12 form">
                <div class="appoinment-box text-center">
                    <div class="heading">
                        <h3>Make an Appointment</h3>
                    </div>
                    <form action="#">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input class="form-control" id="f_name" name="name" placeholder="Name"
                                        type="text">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input class="form-control" id="f_phone" name="phone" placeholder="Phone"
                                        type="tel">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <select style="display: none;">
                                        <option value="1">Male</option>
                                        <option value="2">Female</option>
                                        <option value="3">Child</option>
                                    </select>
                                    <div class="nice-select" tabindex="0"><span class="current">Male</span>
                                        <ul class="list">
                                            <li data-value="1" class="option selected focus">Male
                                            </li>
                                            <li data-value="2" class="option">Female
                                            </li>
                                            <li data-value="3" class="option">Child</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <select style="display: none;">
                                        <option value="1">Department</option>
                                        <option value="2">Medecine</option>
                                        <option value="4">Dental Care</option>
                                        <option value="5">Traumatology</option>
                                    </select>
                                    <div class="nice-select" tabindex="0"><span
                                            class="current">Department</span>
                                        <ul class="list">
                                            <li data-value="1" class="option selected focus">
                                                Department</li>
                                            <li data-value="2" class="option">Medecine
                                            </li>
                                            <li data-value="4" class="option">Dental
                                                Care</li>
                                            <li data-value="5" class="option">
                                                Traumatology</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input class="form-control" id="f_date" name="date" placeholder="Date"
                                        type="date">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input class="form-control" id="f_time" name="time" placeholder="Time"
                                        type="time">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <button class="submit btn btn-filled">
                                    Book Appointment
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- APPOINTMENT END -->

<!-- SERVICE START -->

<section class="service-section">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-title text-center">
                    <h2>Our Available <span>Services</span> </h2>
                    <p>
                        What kind ok the service you can grt from us.
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
            <div class="col-md-4 col-sm-6">
                <div class="item-block-01 text-center">
                    <div class="item-content">
                        <div class="icon">
                            <span class="xicon-ambulance1 color-icon"></span>
                        </div>
                        <h5><a href="#">24/7 Ambulance</a></h5>
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
            <div class="col-md-4 col-sm-6">
                <div class="item-block-01 text-center">
                    <div class="item-content">
                        <div class="icon">
                            <span class="xicon-monitor color-icon"></span>
                        </div>
                        <h5><a href="#">Emergency Care</a></h5>
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
            <div class="col-md-4 col-sm-6">
                <div class="item-block-01 text-center">
                    <div class="item-content">
                        <div class="icon">
                            <span class="xicon-cardiogram color-icon"></span>
                        </div>
                        <h5><a href="#">Operation Thearer</a></h5>
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
            <div class="col-md-4 col-sm-6">
                <div class="item-block-01 text-center">
                    <div class="item-content">
                        <div class="icon">
                            <span class="xicon-brain color-icon"></span>
                        </div>
                        <h5><a href="#">Cancer Service</a></h5>
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
            <div class="col-md-4 col-sm-6">
                <div class="item-block-01 text-center">
                    <div class="item-content">
                        <div class="icon">
                            <span class="xicon-blood color-icon"></span>
                        </div>
                        <h5><a href="#">Blood Test</a></h5>
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
            <div class="col-md-4 col-sm-6">
                <div class="item-block-01 text-center">
                    <div class="item-content">
                        <div class="icon">
                            <span class="xicon-hospital color-icon"></span>
                        </div>
                        <h5><a href="#">24/7 Pharmacy</a></h5>
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

<!-- SERVICE END -->

<!-- ABOUT SECTION START -->

<section class="about-section">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-title text-center mb-2">
                    <h2>Why <span>Choose</span> Us? </h2>
                    <p>
                        What other sais about clinic.
                    </p>
                    <div class="section-border">
                        <div class="icon">
                            <i class="fas fa-tint"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6 col-md-6">
                <div class="item-block-06">
                    <div class="item-content d-flex text-right">
                        <div class="icon ">
                            <span class="xicon-bag color-icon"></span>
                        </div>
                        <div class=" ">
                            <h6><a href="#">Professional Doctors</a></h6>
                            <p>
                                Sed ut perspiciatis unde omnis iste natus error
                                voluptatem accusantium dolor emque laudantium. nunc felis.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="item-block-06">
                    <div class="item-content d-flex text-right">
                        <div class="icon">
                            <span class="xicon-heart-rate-monitor color-icon"></span>
                        </div>
                        <div class="">
                            <h6><a href="#">Track your Progress</a></h6>
                            <p>
                                Sed ut perspiciatis unde omnis iste natus error
                                voluptatem accusantium dolor emque laudantium. nunc felis
                            </p>
                        </div>
                    </div>
                </div>
                <div class="item-block-06">
                    <div class="item-content d-flex text-right">
                        <div class="icon">
                            <span class="xicon-health-report color-icon"></span>
                        </div>
                        <div class="">
                            <h6><a href="#">Over 20 years of experience</a></h6>
                            <p>
                                Sed ut perspiciatis unde omnis iste natus error
                                voluptatem accusantium dolor emque laudantium. nunc felis.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-6">
                <div class="item-block-06">
                    <div class="item-content d-flex text-left">
                        <div class="icon">
                            <span class="xicon-blood color-icon"></span>
                        </div>
                        <div class="">
                            <h6><a href="#">Exclusive Blood Bank</a></h6>
                            <p>
                                Sed ut perspiciatis unde omnis iste natus error
                                voluptatem accusantium dolor emque laudantium. nunc felis
                            </p>
                        </div>
                    </div>
                </div>
                <div class="item-block-06">
                    <div class="item-content d-flex text-left">
                        <div class="icon">
                            <span class="xicon-ambulance1 color-icon"></span>
                        </div>
                        <div class="">
                            <h6><a href="#">Emergency services</a></h6>
                            <p>
                                Sed ut perspiciatis unde omnis iste natus error
                                voluptatem accusantium dolor emque laudantium. nunc felis.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="item-block-06">
                    <div class="item-content d-flex text-left">
                        <div class="icon">
                            <span class="xicon-stethoscope1 color-icon"></span>
                        </div>
                        <div class="">
                            <h6><a href="#">We have experienced Doctor's.</a></h6>
                            <p>
                                Sed ut perspiciatis unde omnis iste natus error
                                voluptatem accusantium dolor emque laudantium. nunc felis.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ABOUT SECTION END -->

<!-- SCHEDULE SECTION START -->

<section class="schedule-section">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-title text-center mb-3">
                    <h2>Our <span>schedule</span> </h2>
                    <p>
                        What other sais about clinic.
                    </p>
                    <div class="section-border">
                        <div class="icon">
                            <i class="fas fa-tint"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row align-items-center">
            <div class="col-xl-7 col-md-12">
                <div class="schedule-img-group">
                    <!-- <img src="{{ asset('protected/public/homepage_assets/img/sehedule/bg-1.png')}}" alt="" class="schedule-img-bg"> </div> -->
                    <div class="row">
                        <div class="col-md-7">
                            <img src="{{ asset('protected/public/homepage_assets/img/sehedule/1.jpg')}}" alt="" class="schedule-img-1"> </div>
                        <div class="col-md-5">
                            <img src="{{ asset('protected/public/homepage_assets/img/sehedule/2.jpg')}}" alt="" class="schedule-img-2">
                            <h3> 25 Years Working Experience</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-5 col-md-12">
                <div class="service-widget">
                    <div class="title">
                        <h3>Opening Hours</h3>
                    </div>
                    <p>Mea ei paulo debitis affert nominati usu eu, et ius dicta detraxit probatus facete
                        nusquam deleniti.</p>
                    <div class="sp-wrapper">
                        <div class="pricing-list-item ">
                            <div class="d-flex justify-content-between">
                                <div class="content">
                                    <p>Friday</p>
                                </div>
                                <div class="content">
                                    <span>8:00:AM</span>
                                    <span>---</span>
                                    <span>7:00:PM</span>
                                </div>
                            </div>
                        </div>
                        <div class="pricing-list-item ">
                            <div class="d-flex justify-content-between">
                                <div class="content">
                                    <p>Saturday</p>
                                </div>
                                <div class="content">
                                    <span>9:00:AM</span>
                                    <span>---</span>
                                    <span>8:00:AM</span>
                                </div>
                            </div>
                        </div>
                        <div class="pricing-list-item">
                            <div class="d-flex justify-content-between">
                                <div class="content active">
                                    <p>Sunday</p>
                                </div>
                                <div class="content active">
                                    <span>Closed</span>
                                </div>
                            </div>
                        </div>
                        <div class="pricing-list-item ">
                            <div class="d-flex justify-content-between">
                                <div class="content">
                                    <p>Monday</p>
                                </div>
                                <div class="content">
                                    <span>8:00:AM</span>
                                    <span>---</span>
                                    <span>7:00:AM</span>
                                </div>
                            </div>
                        </div>
                        <div class="pricing-list-item ">
                            <div class="d-flex justify-content-between">
                                <div class="content">
                                    <p> Tuesday</p>
                                </div>
                                <div class="content">
                                    <span>9:00:PM</span>
                                    <span>---</span>
                                    <span>8:00:PM</span>
                                </div>
                            </div>
                        </div>
                        <div class="pricing-list-item ">
                            <div class="d-flex justify-content-between">
                                <div class="content">
                                    <p> Wednesday</p>
                                </div>
                                <div class="content">
                                    <span>9:00:PM</span>
                                    <span>---</span>
                                    <span>8:00:AM</span>
                                </div>
                            </div>
                        </div>
                        <div class="pricing-list-item ">
                            <div class="d-flex justify-content-between">
                                <div class="content">
                                    <p>Thursday</p>
                                </div>
                                <div class="content">
                                    <span>11:00:AM</span>
                                    <span>---</span>
                                    <span>6:00:AM</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SEHEDUL SECTION END -->


<!-- GALLERY SECTION START -->

<section class="gallery-section">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-title text-center mb-3">
                    <h2>Our <span>Gallery</span></h2>
                    <p>
                        What other sais about clinic.
                    </p>
                    <div class="section-border">
                        <div class="icon">
                            <i class="fas fa-tint"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3 col-md-3 col-sm-6">
                <div class="item-block-02">
                    <a data-fancybox="gallery" href="{{ asset('protected/public/homepage_assets/img/gallery/1.jpg')}}">
                        <img src="{{ asset('protected/public/homepage_assets/img/gallery/1.jpg')}}" alt="1">
                        <div class="overlay">
                            <i class="fas fa-camera"></i>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-6">
                <div class="item-block-02">
                    <a data-fancybox="gallery" href="{{ asset('protected/public/homepage_assets/img/gallery/2.jpg')}}">
                        <img src="{{ asset('protected/public/homepage_assets/img/gallery/2.jpg')}}" alt="2">
                        <div class="overlay">
                            <i class="fas fa-camera"></i>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-6">
                <div class="item-block-02">
                    <a data-fancybox="gallery" href="{{ asset('protected/public/homepage_assets/img/gallery/3.jpg')}}">
                        <img src="{{ asset('protected/public/homepage_assets/img/gallery/3.jpg')}}" alt="3">
                        <div class="overlay">
                            <i class="fas fa-camera"></i>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-6">
                <div class="item-block-02">
                    <a data-fancybox="gallery" href="{{ asset('protected/public/homepage_assets/img/gallery/4.jpg')}}">
                        <img src="{{ asset('protected/public/homepage_assets/img/gallery/4.jpg')}}" alt="4">
                        <div class="overlay">
                            <i class="fas fa-camera"></i>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="row ">
            <div class="col-lg-3 col-md-3 col-sm-6">
                <div class="item-block-02">
                    <a data-fancybox="gallery" href="{{ asset('protected/public/homepage_assets/img/gallery/5.jpg')}}">
                        <img src="{{ asset('protected/public/homepage_assets/img/gallery/5.jpg')}}" alt="5">
                        <div class="overlay">
                            <i class="fas fa-camera"></i>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-6">
                <div class="item-block-02">
                    <a data-fancybox="gallery" href="{{ asset('protected/public/homepage_assets/img/gallery/6.jpg')}}">
                        <img src="{{ asset('protected/public/homepage_assets/img/gallery/6.jpg')}}" alt="6">
                        <div class="overlay">
                            <i class="fas fa-camera"></i>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-6">
                <div class="item-block-02">
                    <a data-fancybox="gallery" href="{{ asset('protected/public/homepage_assets/img/gallery/7.jpg')}}">
                        <img src="{{ asset('protected/public/homepage_assets/img/gallery/7.jpg')}}" alt="7">
                        <div class="overlay">
                            <i class="fas fa-camera"></i>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-6">
                <div class="item-block-02">
                    <a data-fancybox="gallery" href="{{ asset('protected/public/homepage_assets/img/gallery/8.jpg')}}">
                        <img src="{{ asset('protected/public/homepage_assets/img/gallery/8.jpg')}}" alt="8">
                        <div class="overlay">
                            <i class="fas fa-camera"></i>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- GALLERY SECTION END -->

<!-- DEPARTMENT SECTION START -->

<section class="department-section">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-title text-center">
                    <h2> Our <span>Departments</span></h2>
                    <p>
                        Who Is Behind The Best Medical Service In Our Clinic?.
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
            <div class="col-md-4 col-sm-6">
                <div class="item-block-03 text-left">
                    <div class="item-content">
                        <div class="right-side">
                            <div class="icon">
                                <span class="xicon-brain"></span>
                            </div>
                            <h5><a href="#">Neurology</a></h5>
                        </div>
                        <p>
                            Aenean commodo ligula eget dolor. Aenean massa. Cum
                            sociis natoque penatibus et
                            magnis dis Nullam sit amet nunc felis,
                        </p>
                        <div class="item-btn">
                            <a href="#" class="solid-btn">read more<i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6">
                <div class="item-block-03 text-left">
                    <div class="item-content">
                        <div class="right-side">
                            <div class="icon">
                                <span class="xicon-eye"></span>
                            </div>
                            <h5><a href="#">Eye Care</a></h5>
                        </div>
                        <p>
                            Aenean commodo ligula eget dolor. Aenean massa. Cum
                            sociis natoque penatibus et
                            magnis dis Nullam sit amet nunc felis,
                        </p>
                        <div class="item-btn">
                            <a href="#" class="solid-btn">read more<i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6">
                <div class="item-block-03 text-left">
                    <div class="item-content">
                        <div class="right-side">
                            <div class="icon">
                                <span class="xicon-broken-bone"></span>
                            </div>
                            <h5><a href="#">Traumatology</a></h5>
                        </div>
                        <p>
                            Aenean commodo ligula eget dolor. Aenean massa. Cum
                            sociis natoque penatibus et
                            magnis dis Nullam sit amet nunc felis,
                        </p>
                        <div class="item-btn">
                            <a href="#" class="solid-btn">read more<i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6">
                <div class="item-block-03 text-left">
                    <div class="item-content">
                        <div class="right-side">
                            <div class="icon">
                                <span class="xicon-tooth"></span>
                            </div>
                            <h5><a href="#">Denteal care</a></h5>
                        </div>
                        <p>
                            Aenean commodo ligula eget dolor. Aenean massa. Cum
                            sociis natoque penatibus et
                            magnis dis Nullam sit amet nunc felis,
                        </p>
                        <div class="item-btn">
                            <a href="#" class="solid-btn">read more<i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6">
                <div class="item-block-03 text-left">
                    <div class="item-content">
                        <div class="right-side">
                            <div class="icon">
                                <span class="xicon-kidney"></span>
                            </div>
                            <h5><a href="#">kidney</a></h5>
                        </div>
                        <p>
                            Aenean commodo ligula eget dolor. Aenean massa. Cum
                            sociis natoque penatibus et
                            magnis dis Nullam sit amet nunc felis,
                        </p>
                        <div class="item-btn">
                            <a href="#" class="solid-btn">read more<i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6">
                <div class="item-block-03 text-left">
                    <div class="item-content">
                        <div class="right-side">
                            <div class="icon">
                                <span class="xicon-ears"></span>
                            </div>
                            <h5><a href="#">Ear Care</a></h5>
                        </div>
                        <p>
                            Aenean commodo ligula eget dolor. Aenean massa. Cum
                            sociis natoque penatibus et
                            magnis dis Nullam sit amet nunc felis,
                        </p>
                        <div class="item-btn">
                            <a href="#" class="solid-btn">read more<i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- DEPARTMENT SECTION END -->

<!-- COUNTER SECTION  START-->

<section class="counter-section">
    <div class="container">
        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div class="icon-box-item text-center">
                    <div class="icon">
                        <span class="xicon-tooth icon-2"></span>
                    </div>
                    <span class="counter">3,236</span>
                    <h5>Saved Tooth</h5>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="icon-box-item text-center">
                    <div class="icon">
                        <span class="xicon-kidney icon-2"></span>
                    </div>
                    <span class="counter">999</span>
                    <h5>Saved kidny</h5>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="icon-box-item text-center">
                    <div class="icon">
                        <span class="xicon-child icon-2"></span>
                    </div>
                    <span class="counter">2500</span>
                    <h5>Saved Child</h5>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="icon-box-item text-center">
                    <div class="icon">
                        <span class="xicon-brain icon-2"></span>
                    </div>
                    <span class="counter">500</span>
                    <h5>Saved Brain</h5>
                </div>
            </div>
        </div>
    </div>
    </div>
</section>

<!-- COUNTER SECTION  END-->

<!-- DOCTOR SECTION START -->

<section class=" doctor-section">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-title text-center mb-3">
                    <h2>Qualified the <span>Doctor's</span></h2>
                    <p>
                        Who Is Behind The Best Medical Service In Our Clinic?.
                    </p>
                    <div class=" section-border">
                        <div class="icon">
                            <i class="fas fa-tint"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="item-block-04">
                    <div class="item-pic">
                        <img src="{{ asset('protected/public/homepage_assets/img/doctor/1.jpg')}}" alt="team">
                        <div class="item-overlay">
                            <a href="#"> <i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-google"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                    <div class="item-content">
                        <a href="#Daniel Marlen">Dr. Andrew Berton</a>
                        <span>Outpatient Surgery</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="item-block-04">
                    <div class="item-pic">
                        <img src="{{ asset('protected/public/homepage_assets/img/doctor/3.jpg')}}" alt="team">
                        <div class="item-overlay">
                            <a href="#"> <i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-google"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                    <div class="item-content">
                        <a href="#">Dr. Wahab Apple</a>
                        <span>Heart Specialist</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="item-block-04">
                    <div class="item-pic">
                        <img src="{{ asset('protected/public/homepage_assets/img/doctor/2.jpg')}}" alt="team">
                        <div class="item-overlay">
                            <a href="#"> <i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-google"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                    <div class="item-content">
                        <a href="#">Dr. Mackenize</a>
                        <span>Haematology</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="item-block-04">
                    <div class="item-pic">
                        <img src="{{ asset('protected/public/homepage_assets/img/doctor/4.jpg')}}" alt="team">
                        <div class="item-overlay">
                            <a href="#"> <i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-google"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                    <div class="item-content">
                        <a href="#">Dr. Mackenize</a>
                        <span>Haematology</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- DOCTOR SECTION END -->


<!-- TESTIMONIAL SECTION START -->

<section class="testimonial-section">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-title testimonial-title text-center mb-3">
                    <h2><span>Testimonial</span></h2>
                    <p>
                        What other said abour Our Clinic.
                    </p>
                    <div class="section-border">
                        <div class="icon">
                            <i class="fas fa-tint"></i>
                        </div>
                    </div>
                </div>
                <div class="owl-carousel testimonial">
                    <div class="item">
                        <div class="client text-left">
                            <div class="client-bg d-sm-flex d-block align-items-center">
                                <img src="{{ asset('protected/public/homepage_assets/img/testimonials/1.jpg')}}" alt="">
                                <div class="client-inform">
                                    <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit.
                                        Exercitati-onem, labore eius blanditiis nesciunt.
                                    </p>
                                    <div class="client-name">
                                        <h6><a href="#">Jonathom Doe</a></h6>
                                        <span>Outpatient Surgery</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="item">
                        <div class="client text-left">
                            <div class="client-bg d-sm-flex d-block align-items-center">
                                <img src="{{ asset('protected/public/homepage_assets/img/testimonials/2.jpg')}}" alt="">
                                <div class="client-inform">
                                    <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit.
                                        Exercitati-onem, labore eius blanditiis nesciunt.
                                    </p>
                                    <div class="client-name">
                                        <h6><a href="#">Jonathan Smith</a></h6>
                                        <span>Heart Specialist</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="item">
                        <div class="client text-left">
                            <div class="client-bg d-sm-flex d-block align-items-center">
                                <img src="{{ asset('protected/public/homepage_assets/img/testimonials/1.jpg')}}" alt="">
                                <div class="client-inform">
                                    <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit.
                                        Exercitati-onem, labore eius blanditiis nesciunt.
                                    </p>
                                    <div class="client-name">
                                        <h6><a href="#">Jonathom Doe</a></h6>
                                        <span>Outpatient Surgery</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="item">
                        <div class="client text-left">
                            <div class="client-bg d-sm-flex d-block align-items-center">
                                <img src="{{ asset('protected/public/homepage_assets/img/testimonials/2.jpg')}}" alt="">
                                <div class="client-inform">
                                    <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit.
                                        Exercitati-onem, labore eius blanditiis nesciunt.
                                    </p>
                                    <div class="client-name">
                                        <h6><a href="#">Jonathan Smith</a></h6>
                                        <span>Heart Specialist</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="item">
                        <div class="client text-left">
                            <div class="client-bg d-sm-flex d-block align-items-center">
                                <img src="{{ asset('protected/public/homepage_assets/img/testimonials/1.jpg')}}" alt="">
                                <div class="client-inform">
                                    <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit.
                                        Exercitati-onem, labore eius blanditiis nesciunt.
                                    </p>
                                    <div class="client-name">
                                        <h6><a href="#">Jonathom Doe</a></h6>
                                        <span>Outpatient Surgery</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="item">
                        <div class="client text-left">
                            <div class="client-bg d-sm-flex d-block align-items-center">
                                <img src="{{ asset('protected/public/homepage_assets/img/testimonials/2.jpg')}}" alt="">
                                <div class="client-inform">
                                    <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit.
                                        Exercitati-onem, labore eius blanditiis nesciunt.
                                    </p>
                                    <div class="client-name">
                                        <h6><a href="#">Jonathan Smith</a></h6>
                                        <span>Heart Specialist</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- TESTIMONIAL SECTION END -->


<!-- NEWS SECTION START -->

<section class="news-section">
    <div class="container">
        <div class="col-12">
            <div class="section-title text-center mb-3">
                <h2>Latest <span>News</span></h2>
                <p>
                    Who Is Behind The Best Medical Service In Our Clinic?.
                </p>
                <div class="section-border">
                    <div class="icon">
                        <i class="fas fa-tint"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4 col-md-6 col-sm-6">
                <div class="item-block-05">
                    <div class="item-bg">
                        <img src="{{ asset('protected/public/homepage_assets/img/news/1.jpg')}}" alt="1">
                        <span>12 Dec 12</span>
                    </div>
                    <div class="item-content text-left">
                        <div class="meta">
                            <a href="#"><i class="ti-calendar"></i> august 10, 2020</a>
                            <a href="#" class="ml-4"><i class="ti-comments"></i> 2 Comment</a>
                        </div>
                        <h6>
                            <a href="#">Give abundantly their likeness to gathered.</a>
                        </h6>
                        <p>
                            Him make two a blessed creeping won male earth form was appear morning divided on
                            dry doesn behold us Day to over winged.
                        </p>
                        <div class="item-btn">
                            <a href="#" class="btn btn-secondary text-uppercase">Learn
                                more</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-6">
                <div class="item-block-05">
                    <div class="item-bg">
                        <img src="{{ asset('protected/public/homepage_assets/img/news/2.jpg')}}" alt="2">
                        <span>12 Dec 12</span>
                    </div>
                    <div class="item-content text-left">
                        <div class="meta">
                            <a href="#"><i class="ti-calendar"></i> august 10, 2020</a>
                            <a href="#" class="ml-4"><i class="ti-comments"></i> 2 Comment</a>
                        </div>
                        <h6>
                            <a href="#">Your Medical Records likeness are Safe.</a>
                        </h6>
                        <p>
                            Him make two a blessed creeping won male earth form was appear morning divided on
                            dry doesn behold us Day to over winged.
                        </p>
                        <div class="item-btn">
                            <a href="#" class="btn btn-secondary text-uppercase">Learn
                                more</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-6">
                <div class="item-block-05">
                    <div class="item-bg">
                        <img src="{{ asset('protected/public/homepage_assets/img/news/3.jpg')}}" alt="2">
                        <span>12 Dec 12</span>
                    </div>
                    <div class="item-content text-left">
                        <div class="meta">
                            <a href="#"><i class="ti-calendar"></i> august 10, 2020</a>
                            <a href="#" class="ml-4"><i class="ti-comments"></i> 2 Comment</a>
                        </div>
                        <h6>
                            <a href="#">Best USA Medical Hospitals and Clinics.</a>
                        </h6>
                        <p>
                            Him make two a blessed creeping won male earth form was appear morning divided on
                            dry doesn behold us Day to over winged.
                        <div class="item-btn">
                            <a href="#" class="btn btn-secondary text-uppercase">Learn
                                more</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- NEWS SECTION END -->

<!-- PARTNER SECTION START -->

<section class="partner-section">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="owl-carousel partner-logo">
                    <div class="item">
                        <img src="{{ asset('protected/public/homepage_assets/img/partner/1.png')}}" alt="">
                    </div>
                    <div class="item">
                        <img src="{{ asset('protected/public/homepage_assets/img/partner/2.png')}}" alt="">
                    </div>
                    <div class="item">
                        <img src="{{ asset('protected/public/homepage_assets/img/partner/3.png')}}" alt="">
                    </div>
                    <div class="item">
                        <img src="{{ asset('protected/public/homepage_assets/img/partner/4.png')}}" alt="">
                    </div>
                    <div class="item">
                        <img src="{{ asset('protected/public/homepage_assets/img/partner/5.png')}}" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- PARTNER SECTION END -->
@endsection