@extends('frontend.v2.app')
@section('content')

<!-- Page Content -->
            <div class="col-md-7 col-lg-8 col-xl-9">
                <div class="card">
                    <div class="card-body">
                    @if (count($errors) > 0)
                    <div class="alert alert-danger">
                        @foreach ($errors->all() as $error)
                        {{ $error }}<br>
                        @endforeach
                    </div>
                    @elseif(session('success'))
                    <div class="alert alert-success">{{session('success')}}</div>
                    @endif
                        <!-- Profile Settings Form -->
                        <form method="POST" action="{{url('/home_pasien/edit_data_login')}}">
                        @csrf
                            <div class="form-group">
                                <label>Email</label>
                                <input type="text" name="email" @if(old('email')) value="{{ old('email') }}" @else value="{{ session('email') }}" @endif class="form-control floating" disabled>
                            </div>
                            <div class="form-group">
                                <label>Password</label>
                                <input type="password" name="password" @if(old('password')) value="{{ old('password') }}" @else value="" @endif class="form-control floating">
                            </div>
                            <div class="form-group">
                                <label>Confirm Password</label>
                                <input type="password" name="password_confirm" @if(old('password_confirm')) value="{{ old('password_confirm') }}" @else value="" @endif class="form-control floating">
                            </div>
                            <button class="btn btn-primary submit-btn" type="submit" data-toggle="tooltip" data-placement="top"><i class="fa fa-save"></i> Simpan</button>
                            <a href="{{ url('home_pasien/info_akun') }}" class="btn btn-danger submit-btn" data-toggle="tooltip" data-placement="top"><i class="fa fa-undo"></i> Kembali</a>
                        </form>
                        <!-- /Profile Settings Form -->

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /Page Content -->
@endsection

@section('script')
    <!-- ini diisi jika ada script tambahan yang hanya berlaku pada page ini-->

@endsection
