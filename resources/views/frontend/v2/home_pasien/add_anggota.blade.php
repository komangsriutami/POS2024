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
                        <form method="POST" action="{{url('/home_pasien/anggota_keluarga/')}}">
                        @csrf
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
                                        <label>Nomer Induk Kependudukan</label>
                                        <input type="text" name="nik" value="{{ old('nik') }}"  class="form-control floating">
                                        {{-- <label class="focus-label">Nomer Induk Kependudukan</label> --}}
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
                                        <label>Pekerjaan</label>
                                        <input type="text" name="pekerjaan"  value="{{ old('pekerjaan') }}" class="form-control floating">
                                        {{-- <label class="focus-label">Tempat Lahir</label> --}}
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
                                        <textarea name="alamat"value="{{ old('alamat') }}" class="form-control"> {{ old('alamat') }}</textarea>
                                        {{-- <label class="focus-label">Alamat</label> --}}
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Memiliki Alergi</label>
                                        <input type="text" name="alergi_detail" value="{{ old('alergi_detail') }}" class="form-control floating">
                                        {{-- <label class="focus-label">alergi_detail</label> --}}
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label>Kewarganegaraan</label>
                                        <select class="form-group custom-select" name="id_kewarganegaraan">
                                            <option @if( old('id_kewarganegaraan') == "") selected @endif disabled value="">Kewarganegaraan</option>
                                            @if(count($kewarganegaraans) > 0)
                                                @foreach($kewarganegaraans as $item)
                                                    @if( old('id_kewarganegaraan') == $item->id)
                                                        <option selected value="{!! $item->id !!}">{!! $item->kewarganegaraan !!}</option>
                                                    @else
                                                        <option value="{!! $item->id !!}">{!! $item->kewarganegaraan !!}</option>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label>Jenis Kelamin</label>
                                        <select class="form-group custom-select" name="id_jenis_kelamin">
                                            <option @if( old('id_jenis_kelamin') == "") selected @endif disabled value="">Jenis Kelamin</option>
                                            @if(count($jeniskelamins) > 0)
                                                @foreach($jeniskelamins as $item)
                                                    @if( old('id_jenis_kelamin') == $item->id)
                                                        <option selected value="{!! $item->id !!}">{!! $item->jenis_kelamin !!}</option>
                                                    @else
                                                        <option value="{!! $item->id !!}">{!! $item->jenis_kelamin !!}</option>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label>Golongan Darah</label>
                                        <select class="form-group custom-select" name="id_golongan_darah">
                                            <option @if( old('id_golongan_darah') == "") selected @endif disabled value="">Golongan Darah</option>
                                            @if(count($golongandarahs) > 0)
                                                @foreach($golongandarahs as $item)
                                                    @if( old('id_golongan_darah') == $item->id)
                                                        <option selected value="{!! $item->id !!}">{!! $item->golongan_darah !!}</option>
                                                    @else
                                                        <option value="{!! $item->id !!}">{!! $item->golongan_darah !!}</option>
                                                    @endif
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
                                        <input type="text" name="no_bpjs" class="form-control floating" @if(old('no_bpjs')) value="{{ old('no_bpjs') }}" @endif>
                                        {{-- <label class="focus-label">No BPJS</label> --}}
                                    </div>
                                </div>
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
    <script>
        //var token = "";

        $(document).ready(function() {
            //token = $('input[name="_token"]').val();
            $('#tgl_lahir').datepicker({
                autoclose: true,
                format: "yyyy-mm-dd",
                forceParse: false,
                todayHighlight: true,
                endDate: '0d',
            });

            $('.input_select').select2();
        });

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
