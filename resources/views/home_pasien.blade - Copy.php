@extends('frontend.layout.app')

<!-- Breadcrumb -->
<div class="breadcrumb-bar">
	<div class="container-fluid">
		<div class="row align-items-center">
	    	<div class="col-md-12 col-12">
		    	<nav aria-label="breadcrumb" class="page-breadcrumb">
			    	<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="{{ url('/homepage') }}">Home Pasien</a></li>
				    	<li class="breadcrumb-item active" aria-current="page">Dashboard</li>
					</ol>
				</nav>
			    <h2 class="breadcrumb-title">Dashboard</h2>
			</div>
		</div>
	</div>
</div>
<!-- /Breadcrumb -->

@section('content')
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
                                <li class="active">
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
                                <li>
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
                    <div class="card-body pt-0">

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
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /Page Content -->
@endsection
@section('script')
<script type="text/javascript">
	var token = '{{csrf_token()}}';

	$(document).ready(function(){

	})

</script>
@endsection
