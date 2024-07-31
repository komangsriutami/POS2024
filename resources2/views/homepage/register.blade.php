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
        <form action="{{route('register.submit')}}" method="POST">
            @csrf 
            @method('POST')

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
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group mb-4">
                            <p class="mb-2">Nama Lengkap/Full Name (*)</p>
                            <input type="text" name="nama" class="form-control mb-2" value="{{old('nama', $cart->nama)}}" required placeholder="Ketikan nama lengkap anda / Write your full name">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group mb-4">
                            <p class="mb-2">Tanggal Lahir/Birthday (*)</p>
                            <input type="text" name="tgl_lahir" class="form-control mb-2 datepicker" value="{{old('tgl_lahir', $cart->tgl_lahir)}}" required  placeholder="Ketikan tanggal lahir anda / Write your birthday" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group mb-4">
                            <p class="mb-2">Jenis Kelamin/Gender (*)</p>
                            <select name="id_jenis_kelamin" class="w-100 mb-4">
                                <option value="">-- Pilih Jenis Kelamin / Please Select Gender--</option>
                                <option value="1" {{ old('id_jenis_kelamin', $cart->id_jenis_kelamin) == 1 ? 'selected' : '' }}>Laki-laki/Male</option>
                                <option value="2" {{ old('id_jenis_kelamin', $cart->id_jenis_kelamin) == 2 ? 'selected' : '' }}>Perempuan/Female</option>
                            </select>
                        </div>
                    </div>
                    {{-- <div class="col-lg-6">
                        <div class="form-group mb-4">
                            <p class="mb-2">Status Perkawinan/Marital Status (*)</p>
                            <select name="id_status_perkawinan" class="w-100 mb-4">
                                <option value="">-- Pilih Status Perkawinan/Please Select Marital Status--</option>
                                <option value="1" {{ old('id_status_perkawinan') == 1 ? 'selected' : '' }}>Belum Menikah/Single</option>
                                <option value="2" {{ old('id_status_perkawinan') == 2 ? 'selected' : '' }}>Menikah/Married</option>
                            </select>
                        </div>
                    </div> --}}
                    {{-- <div class="col-lg-6">
                        <div class="form-group mb-4">
                            <p class="mb-2">Pekerjaan/Job (*)</p>
                            <select name="job" class="w-100 mb-4">
                                <option value="">-- Pilih Pekerjaan Anda/Please Select Your Job--</option>
                                <option value="1" {{ old('job') == 1 ? 'selected' : '' }}>Wiraswasta</option>
                                <option value="2" {{ old('job') == 2 ? 'selected' : '' }}>Swasta</option>
                                <option value="3" {{ old('job') == 3 ? 'selected' : '' }}>PNS/TNI/POLRI</option>
                                <option value="4" {{ old('job') == 4 ? 'selected' : '' }}>Pelajar</option>
                            </select>
                        </div>
                    </div> --}}
                    <div class="col-lg-6">
                        <div class="form-group mb-4">
                            <p class="mb-2">No. Telp (WA)/Phone No. (Whatsapp) (*)</p>
                            <input type="text" name="telepon" class="form-control mb-2" value="{{old('telepon', $cart->telepon)}}" required placeholder="Ketikan nomor telepon (WA) / Write your phone number (Whatsapp)">
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="form-group mb-4">
                            <p class="mb-2">Alamat/Address (*)</p>
                            <input type="text" name="alamat" class="form-control mb-2" value="{{old('alamat', $cart->alamat)}}" required placeholder="Ketikan alamat email anda / Write your email">
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="form-group mb-4">
                            <p class="mb-2">Aleri Obat/Alergy to Medicine</p>
                            <textarea name="alergi_obat" rows="3" class="form-control" placeholder="Ketikan jika anda memiliki alergi terhadap obat /  Write below if you have alergy to medicine">{{old('alergi_obat', $cart->alergi_obat)}}</textarea>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="form-group mb-4">
                            <p class="mb-2">Email (*)</p>
                            <input type="email" name="email" class="form-control mb-2" value="{{old('email', $cart->email)}}" required placeholder="Ketikan email Anda / Write your email">
                        </div>
                    </div>
                    <div class="col-lg-12 form-condition">
                        <div class="agree-label mb-4">
                            <input type="checkbox" name="account" id="account" required> Simpan data dan buat akun
                        </div>
                    </div>
                    <div class="col-lg-12 col-md-12">
                        <button type="submit" class="btn btn-secondary disabled" id="btn-account" disabled>
                            Registrasi
                        </button>
                    </div>

                    {{-- <div class="col-12">
                        <p class="account-desc">
                            Already have an account?
                            <a href="login.html">Login here</a>
                        </p>
                    </div> --}}
                </div>
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
        $(function(){
            $(".datepicker").datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true,
            });

        });
        $(document).ready(function(){
            document.getElementById("account").onchange = function(){
                var checkBox = document.getElementById("account");
                // Get the output text
                var btn = document.getElementById("btn-account");

                // If the checkbox is checked, display the output text
                if (checkBox.checked == true){
                    btn.disabled = false;
                    btn.classList.remove("disabled");

                } else {
                    btn.disabled = true;
                    btn.classList.remove("disabled");

                }
            };

        });
    </script>
@endpush