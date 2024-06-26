    <!-- Footer -->
    <footer class="footer" id="menu-contact">

        <!-- Footer Top -->
        <div class="footer-top">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-3 col-md-3">

                        <!-- Footer Widget -->
                        <div class="footer-widget footer-about">
                            <div class="row">
                                <div class="col-lg-2 footer-logo">
                                    <img src='{{"/assets_frontend/img/logo.png"}}' alt="logo" width="68px" height="68px">
                                </div>
                                <div class="col-lg-10">
                                    <h2 class="text-white" style="padding-top: 15px; padding-left: 10px;">Bhakti Widya
                                        Farma
                                    </h2>
                                </div>
                                <div class="footer-about-content mt-sm-1 mb-sm-5">
                                    <hr class="my-4 bg-white">
                                    <p>Bhakti Widya Farma adalah Apotek yang ... . </p>
                                </div>
                            </div>
                        </div>
                        <!-- /Footer Widget -->

                    </div>

                    <div class="col-sm-3 col-md-3">

                        <!-- Footer Widget -->
                        <div class="footer-widget footer-about text-white">
                            <h2 class="footer-title">Jam buka</h2>
                            <ul class="fa-ul">
                                <table>
                                    <tr>
                                        <td>Senin - Jumat</td>
                                        <td>08:00 - 22:00</td>
                                    </tr>
                                    <tr>
                                        <td>Sabtu - Minggu</td>
                                        <td>08:00 - 21:00</td>
                                    </tr>
                                </table>
                            </ul>
                            <hr class="my-4 bg-white mt-sm-4">
                            <h2 class="footer-title">Hubungi kami</h2>
                            <ul class="fa-ul">
                                <li><span class="fa fa-map-marker fa-li"></span><a class="text-success"
                                        href="https://www.google.com/maps/place/Bhakti+Widya+Farma/@-8.788237,115.177385,14z/data=!4m5!3m4!1s0x0:0xf1a830791260f181!8m2!3d-8.7882392!4d115.1773931?hl=id"
                                        target="_blank">Jl. Raya Kampus Unud No.18L, Jimbaran, Kec. Kuta Sel., Kabupaten
                                        Badung, Bali 80361</a></li>
                                <li><span class="fa fa-phone fa-li"></span>+62812345678</li>
                                <li><span class="fa fa-envelope fa-li"></span>bhakti@gmail.com</li>
                            </ul>
                            <div class="social-icon">
                                <ul>
                                    <li>
                                        <a href="#" target="_blank"><i class="fab fa-facebook-f"></i> </a>
                                    </li>
                                    <li>
                                        <a href="#" target="_blank"><i class="fab fa-twitter"></i> </a>
                                    </li>
                                    <li>
                                        <a href="#" target="_blank"><i class="fab fa-linkedin-in"></i></a>
                                    </li>
                                    <li>
                                        <a href="#" target="_blank"><i class="fab fa-instagram"></i></a>
                                    </li>
                                    <li>
                                        <a href="#" target="_blank"><i class="fab fa-dribbble"></i> </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <!-- /Footer Widget -->

                    </div>

                    <div class="col-sm-6 col-md-6">

                        <!-- Footer Widget -->
                        <div class="footer-widget footer-about">
                            <h2 class="footer-title">Message</h2>
                            {!! Form::model(new App\Message(), ['route' => ['message.store'], 'class' => 'validated_form']) !!}
                                <div class="form-group row">
                                    <label for="name" class="col-sm-3 footer-title">Full Name</label>
                                    <div class="col-sm-9">
                                        {!! Form::text('name', $data_->name, ['class' => 'form-control required', 'placeholder' => 'Masukkan nama lengkap...']) !!}
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="email" class="col-sm-3 footer-title">Your Email</label>
                                    <div class="col-sm-9">
                                        {!! Form::email('email', $data_->email, ['class' => 'form-control required', 'placeholder' => 'Masukkan email...']) !!}
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="phone_number" class="col-sm-3 footer-title">Phone Number</label>
                                    <div class="col-sm-9">
                                        {!! Form::text('phone_number', $data_->phone_number, ['class' => 'form-control required', 'placeholder' => 'Masukkan nomor ponsel...']) !!}
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="additional_message" class="col-sm-3 footer-title">Additional
                                        Message</label>
                                    <div class="col-sm-9">
                                        {!! Form::textarea('additional_message', $data_->additional_message, ['class' => 'form-control required', 'placeholder' => 'Masukkan pesan...']) !!}
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-3"></div>
                                    <div class="col-sm-9">
                                        <button class="btn btn-primary" style="width: 100%;">Submit</button>
                                    </div>
                                </div>
                            {!! Form::close() !!}
                        </div>
                        <!-- /Footer Widget -->

                    </div>


                </div>
            </div>
        </div>
        <!-- /Footer Top -->

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div class="container-fluid">

                <!-- Copyright -->
                <div class="copyright">
                    <div class="row">
                        <div class="col-md-6 col-lg-6">
                            <div class="copyright-text">
                                <p class="mb-0">&copy; 2021 Bhakti Widya Farma. All rights reserved.</p>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-6">

                            <!-- Copyright Menu -->
                            <div class="copyright-menu">
                                <ul class="policy-menu">
                                    <li><a href="term-condition">Terms and Conditions</a></li>
                                    <li><a href="privacy-policy">Policy</a></li>
                                </ul>
                            </div>
                            <!-- /Copyright Menu -->

                        </div>
                    </div>
                </div>
                <!-- /Copyright -->

            </div>
        </div>
        <!-- /Footer Bottom -->
    </footer>
    <!-- /Footer -->
