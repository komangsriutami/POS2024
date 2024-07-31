@extends('homepage.layouts.app')

@section('content')
<!-- Shop PAGE BANNER -->

<div class="page-banner">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="page-banner-content">
                    <div class="d-sm-flex justify-content-between align-items-center">
                        <div class="title">
                            {{-- <h6 class="text-left text-capitalized">Resister Page</h6> --}}
                            <h2>Reservasi</h2>
                        </div>
                        {{-- <div class="link text-sm-right text-left">
                            <a href="home.html">Home <i class="ti-angle-double-right"></i></a>
                            Resister
                        </div> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Shop PAGE BANNER  END-->

<!-- SIGN UP FORM START -->

<section class="sign-up-section">
    <div class="container">

            <div class="sign-up-form">
                <div class="row">
                    <div class="col-12">
                        <div class="sign-up-title">
                            {{-- <h3>Registrasi Pasien / Patient Registration</h3> --}}
                            <p>
                                Lengkapi formulir untuk registrasi.
                            </p>
                        </div>
                        
                        @if ($errors->any())
                            <div class="alert with-close alert-danger">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </div>
                        @endif
                        
                        @if(Session::has('message'))
                            <div class="alert with-close alert-info mt-2">
                                {{Session::get('message')}}
                            </div>
                        @endif
                    </div>
                </div>
                <form action="{{route('medical_record.submit')}}" method="POST">
                    @csrf 
                    @method('POST')
                    <div class="row">
                    
                        <div class="col-lg-12">
                            <div class="form-group mb-4">
                                <p class="mb-2">Apakah Anda sudah pernah berobat? / Have you ever checked? (*)</p>
                                <select name="is_pernah_berobat" class="w-100 mb-4" id="is_pernah_berobat" required>
                                    <option value="">-- Pilih Status Berobat/Please Select Checked--</option>
                                    <option value="1" {{ old('is_pernah_berobat', $cart->is_pernah_berobat) == 1 ? 'selected' : '' }}>Ya/Yes</option>
                                    <option value="2" {{ old('is_pernah_berobat', $cart->is_pernah_berobat) == 2 ? 'selected' : '' }}>Tidak/No</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-12 col-md-12" id="btn_registrasi">
                            <button type="submit" name="sign_up" class="btn btn-secondary">
                                Registrasi
                            </button>
                        </div>
                    </div>
                </form>

                <form action="{{route('pasien.login.submit')}}" method="POST">
                    @csrf 
                    @method('POST')
                    <div class="row">
                        <div class="col-lg-12 mt-2 {{ old('is_pernah_berobat', $cart->is_pernah_berobat) == 1 ? '' : 'd-none' }}" id="div_nomor_rekam_medis">
                            <h5>Senang bertemu Anda kembali</h5>
                            <p>Silahkan lakukan login ke akun Anda</p>
                            <div class="form-group mb-4">
                                <p class="mb-2">Email</p>
                                <input type="text" name="email" class="form-control mb-2" value="{{old('email')}}" placeholder="Ketikan Email">
                            </div>
                            <div class="form-group mb-4">
                                <p class="mb-2">Password</p>
                                <input type="password" name="password" class="form-control mb-2" placeholder="Password">
                            </div>
                            <div class="form-group mb-4">
                                <button type="submit" name="sign_in" class="btn btn-secondary">
                                    Login
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                
            </div>
        </form>
    </div>
</section>

<!-- SIGN UP PAGE START -->
@endsection

@push('script')
    {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/css/bootstrap.min.css"/> --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/css/bootstrap-datepicker.css" rel="stylesheet">
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/js/bootstrap-datepicker.js"></script>

    <script type="text/javascript">
        function idPernahBerobat(){
            var value = document.getElementById("is_pernah_berobat").value;
            if(value == 1){
                var element = document.getElementById("div_nomor_rekam_medis");
                element.classList.remove("d-none");
                
                var element = document.getElementById("btn_registrasi");
                element.classList.add("d-none");

            }else{
                var element = document.getElementById("div_nomor_rekam_medis");
                element.classList.add("d-none");
                // document.getElementById('nomor_rekam_medis').value = "";
                var element = document.getElementById("btn_registrasi");
                element.classList.remove("d-none");

            }
        }
        $(document).ready(function(){
            document.getElementById("is_pernah_berobat").onchange = function(){
                idPernahBerobat();
            };
            idPernahBerobat();
        });
    </script>
@endpush