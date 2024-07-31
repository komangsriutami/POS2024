@extends('rekammedis.frontend.layout_frontend.app')
@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-bar">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-12 col-12">
                <nav aria-label="breadcrumb" class="page-breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/homepage') }}">Home Pasien</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Ubah Data Login</li>
                    </ol>
                </nav>
                <h2 class="breadcrumb-title">Ubah Data Login</h2>
            </div>
        </div>
    </div>
</div>
<!-- /Breadcrumb -->
			
<!-- Page Content -->
<div class="content">
    <div class="container-fluid">
        <div class="row">
        
            <!-- Profile Sidebar -->
            <div class="col-md-5 col-lg-4 col-xl-3 theiaStickySidebar">
                @if(session('id') != null)
                <div class="profile-sidebar">
                    <div class="widget-profile pro-widget-content">
                        <div class="profile-info-widget">
                            <div class="d-flex justify-content-center">
                                <i class="fa fa-user"></i>
                            </div>
                            <div class="profile-det-info">
                                <div class="d-flex justify-content-center">
                                    <h4>{{ session("nama") }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="dashboard-widget">
                        <nav class="dashboard-menu">
                            <ul>
                                <li>
                                    <a href="home_pasien">
                                        <i class="fas fa-home"></i>
                                        <span>Dashboard</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="favourites">
                                        <i class="fas fa-bookmark"></i>
                                        <span>Favourites</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="pesan">
                                        <i class="fas fa-comments"></i>
                                        <span>Pesan</span>
                                        <small class="unread-msg">23</small>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ url('home_pasien/info_akun') }}">
                                        <i class="fas fa-user-cog"></i>
                                        <span>Pengaturan Profile</span>
                                    </a>
                                </li>
                                <li class="active">
                                    <a href="{{ url('home_pasien/edit_data_login') }}">
                                        <i class="fas fa-lock"></i>
                                        <span>Ubah Data Login</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ url('logout_pasien_post') }}">
                                        <i class="fas fa-sign-out-alt"></i>
                                        <span>Logout</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
                    @endif
            </div>
            <!-- / Profile Sidebar -->
            
            <div class="col-md-7 col-lg-8 col-xl-9">
                <div class="card">
                    <div class="card-body">
                        
                        <!-- Profile Settings Form -->
                        <form>
                            <div class="row form-row">
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label>Nama</label>
                                        <input type="text" name="nama" value="{{ old('nama') }}" class="form-control floating">
                                        {{-- <label class="focus-label">nama</label> --}}
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label>Tempat Lahir</label>
                                        <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir') }}" class="form-control floating">
                                        {{-- <label class="focus-label">Tempat Lahir</label> --}}
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label>Tanggal Lahir</label>
                                        <input class="form-control floating" name="tgl_lahir" value="{{ old('tgl_lahir') }}" id="datepicker" name="date" type="text">
                                        {{-- <label class="focus-label">Tanggal Lahir</label> --}}
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label>No Telepon</label>
                                        <input type="text" name="telepon" value="{{ old('telepon') }}" class="form-control floating">
                                        {{-- <label class="focus-label">No Telpon</label> --}}
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Alamat</label>
                                        <input type="text" name="alamat" value="{{ old('alamat') }}" class="form-control floating">
                                        {{-- <label class="focus-label">Alamat</label> --}}
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label>Kewarganegaraan</label>
                                        <select class="custom-select floating" name="id_kewarganegaraan" value="{{ old('id_kewarganegaraan') }}">
                                            <option @if( old('id_kewarganegaraan') == "" ) selected @endif disabled value="">Kewarganegaraan</option>
                                            @if(count($kewarganegaraans) > 0)
                                                @foreach($kewarganegaraans as $item)
                                                    <option @if( old('id_kewarganegaraan') == $item->id ) selected @endif value="{!! $item->id !!}">{!! $item->kewarganegaraan !!}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label>Jenis Kelamin</label>
                                        <select class="form-group custom-select" name="id_jenis_kelamin" value="{{ old('id_jenis_kelamin') }}">
                                            <option @if( old('id_jenis_kelamin') == "" ) selected @endif disabled value="">Jenis Kelamin</option>
                                            @if(count($jeniskelamins) > 0)
                                                @foreach($jeniskelamins as $item)
                                                    <option @if( old('id_jenis_kelamin') == $item->id ) selected @endif value="{!! $item->id !!}">{!! $item->jenis_kelamin !!}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label>Golongan Darah</label>
                                        <select class="form-group custom-select" name="id_golongan_darah" value="{{ old('id_golongan_darah') }}">
                                            <option @if( old('id_golongan_darah') == "" ) selected @endif disabled value="">Golongan Darah</option>
                                            @if(count($golongandarahs) > 0)
                                                @foreach($golongandarahs as $item)
                                                    <option @if( old('id_golongan_darah') == $item->id ) selected @endif value="{!! $item->id !!}">{!! $item->golongan_darah !!}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label>Pernah Berobat</label>
                                        <select class="form-group custom-select" name="is_pernah_berobat" value="{{ old('is_pernah_berobat') }}">
                                            <option @if( old('is_pernah_berobat') == "" ) selected @endif disabled value="">Pernah Berobat</option>
                                            <option @if( old('is_pernah_berobat') == "1" ) selected @endif value="1">Ya</option>
                                            <option @if( old('is_pernah_berobat') == "2" ) selected @endif value="2">Tidak</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label>Memiliki BPJS</label>
                                        <select id="bpjs_anggota" class="form-group custom-select" name="is_bpjs" value="{{ old('is_bpjs') }}" onchange="ChangeBpjsAnggota();">
                                            <option @if( old('is_bpjs') == "" ) selected @endif disabled value="">Memiliki BPJS</option>
                                            <option @if( old('is_bpjs') == "1" ) selected @endif value="1">Ya</option>
                                            <option @if( old('is_bpjs') == "2" ) selected @endif value="2">Tidak</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div id="noBpjs_anggota" class="form-group" style="display: none;">
                                        <label>No BPJS</label>
                                        <input type="text" name="no_bpjs" class="form-control floating">
                                        {{-- <label class="focus-label">No BPJS</label> --}}
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-primary submit-btn" type="submit" data-toggle="tooltip" data-placement="top"><i class="fa fa-save"></i> Simpan</button>
                            <a href="{{ url('/info_akun') }}" class="btn btn-danger submit-btn" data-toggle="tooltip" data-placement="top"><i class="fa fa-undo"></i> Kembali</a>
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
