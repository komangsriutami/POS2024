@extends('frontend.v2.app')

@section('title')
Dashboard
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
  <li class="breadcrumb-item active" aria-current="page">Home</li>
</ol>
@endsection

@section('content')
    <div class="col-md-7 col-lg-8 col-xl-9">
        <div class="card">
            <div class="card-body pt-0">
                @if(Auth::user()->no_rm != '')
                    <!-- Tab Menu -->
                    <nav class="user-tabs mb-4">
                        <ul class="nav nav-tabs nav-tabs-bottom nav-justified">
                            <li class="nav-item">
                                <a class="nav-link active" href="#" data-toggle="tab">Jadwal Konsultasi</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#" data-toggle="tab"><span class="med-records">Rekam Medis</span></a>
                            </li>
                        </ul>
                    </nav>
                    <!-- /Tab Menu -->
                    <!-- Tab Content -->
                    <div class="tab-content pt-0">
                        <!-- Jadwal Konsultasi Tab -->
                        <div id="jadwal_konsultasi" class="tab-pane fade show active">
                            <div class="card card-table mb-0">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-center mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Doctor</th>
                                                    <th>Appt Date</th>
                                                    <th>Booking Date</th>
                                                    <th>Amount</th>
                                                    <th>Follow Up</th>
                                                    <th>Status</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <h2 class="table-avatar">
                                                            <a href="doctor-profile" class="avatar avatar-sm mr-2">
                                                            <img class="avatar-img rounded-circle" src="assets/img/doctors/doctor-thumb-01.jpg" alt="User Image">
                                                            </a>
                                                            <a href="doctor-profile">Dr. Ruby Perrin <span>Dental</span></a>
                                                        </h2>
                                                    </td>
                                                    <td>14 Nov 2019 <span class="d-block text-info">10.00 AM</span></td>
                                                    <td>12 Nov 2019</td>
                                                    <td>$160</td>
                                                    <td>16 Nov 2019</td>
                                                    <td><span class="badge badge-pill bg-success-light">Confirm</span></td>
                                                    <td class="text-right">
                                                        <div class="table-action">
                                                            <a href="javascript:void(0);" class="btn btn-sm bg-primary-light">
                                                            <i class="fas fa-print"></i> Print
                                                            </a>
                                                            <a href="javascript:void(0);" class="btn btn-sm bg-info-light">
                                                            <i class="far fa-eye"></i> View
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /Jadwal Konsultasi Tab -->
                        <!-- Rekam Medis Tab -->
                        <div id="rekammedis" class="tab-pane fade">
                            <div class="card card-table mb-0">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-center mb-0">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Date </th>
                                                    <th>Description</th>
                                                    <th>Attachment</th>
                                                    <th>Created</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><a href="javascript:void(0);">#MR-0010</a></td>
                                                    <td>14 Nov 2019</td>
                                                    <td>Dental Filling</td>
                                                    <td><a href="#">dental-test.pdf</a></td>
                                                    <td>
                                                        <h2 class="table-avatar">
                                                            <a href="doctor-profile" class="avatar avatar-sm mr-2">
                                                            <img class="avatar-img rounded-circle" src="assets/img/doctors/doctor-thumb-01.jpg" alt="User Image">
                                                            </a>
                                                            <a href="doctor-profile">Dr. Ruby Perrin <span>Dental</span></a>
                                                        </h2>
                                                    </td>
                                                    <td class="text-right">
                                                        <div class="table-action">
                                                            <a href="javascript:void(0);" class="btn btn-sm bg-info-light">
                                                            <i class="far fa-eye"></i> View
                                                            </a>
                                                            <a href="javascript:void(0);" class="btn btn-sm bg-primary-light">
                                                            <i class="fas fa-print"></i> Print
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /Rekam Medis Tab -->
                    </div>
                    <!-- Tab Content -->
                @else
                    <div class="row">
                        <div class="col-md-12">
                            <br>
                            <h2 class="title">Selamat Datang!</h2>
                            <p style="font-size: 10pt;color: #d32f2f;">Anda belum melengkapi data regsitrasi, silakan lengkapi data ini terlebih dahulu.</p>
                            <a class="btn btn-primary" href="{{url('home_pasien/info_akun/data_diri/')}}{{'/'.$parameter[1]}}">Isi Data Diri</a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
@section('script')
<script type="text/javascript">
	var token = '{{csrf_token()}}';

	$(document).ready(function(){

	})

</script>
@endsection
