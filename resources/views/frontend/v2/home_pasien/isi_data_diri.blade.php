@extends('frontend.v2.app')

@section('title')
Isi Data Diri
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
  <li class="breadcrumb-item"><a href="{{ url('/home_pasien') }}">Home</a></li>
  <li class="breadcrumb-item active" aria-current="page">Isi Data Diri</li>
</ol>
@endsection

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
                        <form method="POST" action="{{url('/home_pasien/data_diri/')}}{{'/'.$parameter}}">
                        @csrf
                            <div class="row form-row">
                                <div class="col-12 col-md-6">
                                    @if($pilihan==1)
                                        <h5>Nama</h5>
                                        <p>{{ $anggotas->nama }}</p>
                                    @else
                                    <div class="form-group">
                                        <label>Nama</label>
                                        <input type="text" name="nama" @if(old('nama')) value="{{ old('nama') }}" @else value="{{ $anggotas->nama }}" @endif class="form-control floating">
                                        {{-- <label class="focus-label">nama</label> --}}
                                    </div>
                                    @endif
                                </div>
                                <div class="col-12 col-md-6">
                                    @if($pilihan==1)
                                        <h5>Nomer Induk Kependudukan</h5>
                                        <p>{{ $anggotas->nik }}</p>
                                    @else
                                    <div class="form-group">
                                        <label>Nomer Induk Kependudukan</label>
                                        <input type="text" name="nik" @if(old('nik')) value="{{ old('nik') }}" @else value="{{ $anggotas->nik }}" @endif class="form-control floating">
                                        {{-- <label class="focus-label">Nomer Induk Kependudukan</label> --}}
                                    </div>
                                    @endif
                                </div>
                                <div class="col-12 col-md-6">
                                    @if($pilihan==1)
                                        <h5>Tempat Lahir</h5>
                                        <p>{{ $anggotas->tempat_lahir }}</p>
                                    @else
                                    <div class="form-group">
                                        <label>Tempat Lahir</label>
                                        <input type="text" name="tempat_lahir" @if(old('tempat_lahir')) value="{{ old('tempat_lahir') }}" @else value="{{ $anggotas->tempat_lahir }}" @endif class="form-control floating">
                                        {{-- <label class="focus-label">Tempat Lahir</label> --}}
                                    </div>
                                    @endif
                                </div>
                                <div class="col-12 col-md-6">
                                    @if($pilihan==1)
                                        <h5>Tanggal Lahir</h5>
                                        <p>{{ $anggotas->tgl_lahir }}</p>
                                    @else
                                    <div class="form-group">
                                        <label>Tanggal Lahir</label>
                                        <input class="form-control floating" name="tgl_lahir" @if(old('tgl_lahir')) value="{{ old('tgl_lahir') }}" @else value="{{ $anggotas->tgl_lahir }}" @endif id="datepicker" name="date" type="text">
                                        {{-- <label class="focus-label">Tanggal Lahir</label> --}}
                                    </div>
                                    @endif
                                </div>
                                <div class="col-12 col-md-6">
                                    @if($pilihan==1)
                                        <h5>Pekerjaan</h5>
                                        <p>{{ $anggotas->pekerjaan }}</p>
                                    @else
                                    <div class="form-group">
                                        <label>Pekerjaan</label>
                                        <input type="text" name="pekerjaan" @if(old('pekerjaan')) value="{{ old('pekerjaan') }}" @else value="{{ $anggotas->pekerjaan }}" @endif class="form-control floating">
                                        {{-- <label class="focus-label">Tempat Lahir</label> --}}
                                    </div>
                                    @endif
                                </div>
                                <div class="col-12 col-md-6">
                                    @if($pilihan==1)
                                        <h5>Telepon</h5>
                                        <p>{{ $anggotas->telepon }}</p>
                                    @else
                                    <div class="form-group">
                                        <label>No Telepon</label>
                                        <input type="text" name="telepon" @if(old('telepon')) value="{{ old('telepon') }}" @else value="{{ $anggotas->telepon }}" @endif class="form-control floating">
                                        {{-- <label class="focus-label">No Telpon</label> --}}
                                    </div>
                                    @endif
                                </div>
                                <div class="col-12">
                                @if($pilihan==1)
                                        <h5>Alamat</h5>
                                        <p>{{ $anggotas->alamat }}</p>
                                    @else
                                    <div class="form-group">
                                        <label>Alamat</label>
                                        <textarea name="alamat" @if(old('alamat')) value="{{ old('alamat') }}" @else value="{{ $anggotas->alamat }}" @endif class="form-control">@if(old('alamat')) {{ old('alamat') }} @else {{ $anggotas->alamat }} @endif</textarea>
                                        {{-- <label class="focus-label">Alamat</label> --}}
                                    </div>
                                    @endif
                                </div>
                                <div class="col-12">
                                @if($pilihan==1)
                                        <h5>Alergi Obat</h5>
                                        <p>@if($anggotas->alergi_obat == "") - @else{{ $anggotas->alergi_obat }}@endif</p>
                                    @else
                                    <div class="form-group">
                                        <label>Alergi Obat</label>
                                        <input type="text" name="alergi_obat" @if(old('alergi_obat')) value="{{ old('alergi_obat') }}" @else value="{{ $anggotas->alergi_obat }}" @endif class="form-control floating">
                                        {{-- <label class="focus-label">alergi_obat</label> --}}
                                    </div>
                                    @endif
                                </div>
                                <div class="col-12 col-md-3">
                                @if($pilihan==1)
                                        <h5>Kewarganegaraan</h5>
                                        <p>{{ $kewarganegaraans[$anggotas->id_kewarganegaraan]->kewarganegaraan }}</p>
                                    @else
                                    <div class="form-group">
                                        <label>Kewarganegaraan</label>
                                        <select class="form-group custom-select" name="id_kewarganegaraan">
                                            <option @if( old('id_kewarganegaraan') == "" && $anggotas->id_kewarganegaraan == "") selected @endif disabled value="">-- Pilih Kewarganegaraan --</option>
                                            @if(count($kewarganegaraans) > 0)
                                                @foreach($kewarganegaraans as $item)
                                                    @if( old('id_kewarganegaraan') == $item->id || $anggotas->id_kewarganegaraan == $item->id)
                                                        <option selected value="{!! $item->id !!}">{!! $item->kewarganegaraan !!}</option>
                                                    @else
                                                        <option value="{!! $item->id !!}">{!! $item->kewarganegaraan !!}</option>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    @endif
                                </div>
                                <div class="col-12 col-md-3">
                                @if($pilihan==1)
                                        <h5>Agama</h5>
                                        <p>{{ $kewarganegaraans[$anggotas->id_agama]->agama }}</p>
                                    @else
                                    <div class="form-group">
                                        <label>Agama</label>
                                        <select class="form-group custom-select" name="id_agama">
                                            <option @if( old('id_agama') == "" && $anggotas->id_agama == "") selected @endif disabled value="">-- Pilih Agama --</option>
                                            @if(count($agamas) > 0)
                                                @foreach($agamas as $item)
                                                    @if( old('id_agama') == $item->id || $anggotas->id_agama == $item->id)
                                                        <option selected value="{!! $item->id !!}">{!! $item->agama !!}</option>
                                                    @else
                                                        <option value="{!! $item->id !!}">{!! $item->agama !!}</option>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    @endif
                                </div>
                                <div class="col-12 col-md-3">
                                @if($pilihan==1)
                                        <h5>Jenis Kelamin</h5>
                                        <p>{{ $jeniskelamins[$anggotas->id_jenis_kelamin]->jenis_kelamin }}</p>
                                    @else
                                    <div class="form-group">
                                        <label>Jenis Kelamin</label>
                                        <select class="form-group custom-select" name="id_jenis_kelamin">
                                            <option @if( old('id_jenis_kelamin') == "" && $anggotas->id_jenis_kelamin == "") selected @endif disabled value="">-- Pilih Jenis Kelamin --</option>
                                            @if(count($jeniskelamins) > 0)
                                                @foreach($jeniskelamins as $item)
                                                    @if( old('id_jenis_kelamin') == $item->id || $anggotas->id_jenis_kelamin == $item->id)
                                                        <option selected value="{!! $item->id !!}">{!! $item->jenis_kelamin !!}</option>
                                                    @else
                                                        <option value="{!! $item->id !!}">{!! $item->jenis_kelamin !!}</option>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    @endif
                                </div>
                                <div class="col-12 col-md-3">
                                @if($pilihan==1)
                                        <h5>Golongan Darah</h5>
                                        <p>{{ $golongandarahs[$anggotas->id_golongan_darah]->golongan_darah }}</p>
                                    @else
                                    <div class="form-group">
                                        <label>Golongan Darah</label>
                                        <select class="form-group custom-select" name="id_golongan_darah">
                                            <option @if( old('id_golongan_darah') == "" && $anggotas->id_golongan_darah == "") selected @endif disabled value="">-- Pilih Golongan Darah --</option>
                                            @if(count($golongandarahs) > 0)
                                                @foreach($golongandarahs as $item)
                                                    @if( old('id_golongan_darah') == $item->id || $anggotas->id_golongan_darah == $item->id)
                                                        <option selected value="{!! $item->id !!}">{!! $item->golongan_darah !!}</option>
                                                    @else
                                                        <option value="{!! $item->id !!}">{!! $item->golongan_darah !!}</option>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    @endif
                                </div>
                                <div class="col-12 col-md-2">
                                @if($pilihan==1)
                                        <h5>Pernah Berobat</h5>
                                        <p> @if($anggotas->is_pernah_berobat==1) Ya @else Tidak @endif  </p>
                                    @else
                                    <div class="form-group">
                                        <label>Pernah Berobat</label>
                                        <select class="form-group custom-select" name="is_pernah_berobat" value="{{ old('is_pernah_berobat') }}">
                                            <option @if( old('is_pernah_berobat') == "" || $anggotas->is_pernah_berobat == "" ) selected @endif disabled value="">Pernah Berobat</option>
                                            <option @if( old('is_pernah_berobat') == "1" || $anggotas->is_pernah_berobat == 1 ) selected @endif value="1">Ya</option>
                                            <option @if( old('is_pernah_berobat') == "2" || $anggotas->is_pernah_berobat == 2 ) selected @endif value="2">Tidak</option>
                                        </select>
                                    </div>
                                    @endif
                                </div>
                                <div class="col-12 col-md-2">
                                    @if($pilihan==1)
                                        <h5>Memiliki BPJS</h5>
                                        <p>@if($anggotas->is_bpjs==1) Ya @else Tidak @endif</p>
                                    @else
                                    <div class="form-group">
                                        <label>Memiliki BPJS</label>
                                        <select id="bpjs_anggota" class="form-group custom-select" name="is_bpjs" value="{{ old('is_bpjs') }}" onchange="ChangeBpjsAnggota();">
                                            <option @if( old('is_bpjs') == "" || $anggotas->is_bpjs == "" ) selected @endif disabled value="">Memiliki BPJS</option>
                                            <option @if( old('is_bpjs') == "1" || $anggotas->is_bpjs == 1 ) selected @endif value="1">Ya</option>
                                            <option @if( old('is_bpjs') == "2" || $anggotas->is_bpjs == 2 ) selected @endif value="2">Tidak</option>
                                        </select>
                                    </div>
                                    @endif
                                </div>
                                <div class="col-12 col-md-4">
                                @if($pilihan==1)
                                        <h5>No BPJS</h5>
                                        <p>@if($anggotas->no_bpjs=="") - @else {{$anggotas->no_bpjs}} @endif</p>
                                    @else
                                    <div id="noBpjs_anggota" class="form-group" style="display: none;">
                                        <label>No BPJS</label>
                                        <input type="text" name="no_bpjs" class="form-control floating" @if(old('no_bpjs')) value="{{ old('no_bpjs') }}" @else value="{{ $anggotas->no_bpjs }}" @endif>
                                        {{-- <label class="focus-label">No BPJS</label> --}}
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @if($pilihan==2)
                            <button class="btn btn-primary submit-btn" type="submit" data-toggle="tooltip" data-placement="top"><i class="fa fa-save"></i> Simpan</button>
                            @endif
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
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $( document ).ready(function() {
            if({{$anggotas->activated}}==0 && {{ (count($errors))}} ==0){
                Swal.fire({
                    title: '<strong>Isi Data Diri</strong>',
                    icon: 'info',
                    html:'<p>Mohon mengisi data diri terlebih dahulu untuk <span class = "text-success">Aktivasi Akun</span> serta agar bisa melanjutkan ke tahap <span class = "text-success">Booking Dokter</span>. Terima Kasih.</p>',
                    showCloseButton: true,
                });
            }
        });

        var token = "";

        $(document).ready(function() {
            token = $('input[name="_token"]').val();
            $('#tgl_lahir').datepicker({
                autoclose: true,
                format: "yyyy-mm-dd",
                forceParse: false,
                todayHighlight: true,
                endDate: '0d',
            });

            $('.input_select').select2();
        })

        if($('#bpjs_anggota :selected').val() == "1"){
            $("#noBpjs_anggota").show();
        }else{
            $("#noBpjs_anggota").hide();
        }

        $('#bpjs_anggota').on('change', function() {
            if($('#bpjs_anggota :selected').val() == "1"){
                $("#noBpjs_anggota").show();
            }else{
                $("#noBpjs_anggota").hide();
            }
        });
    </script>

@endsection
