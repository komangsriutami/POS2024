@extends('rekammedis.frontend.layout_frontend.app')
@section('content')

<div class="breadcrumb-bar">
				<div class="container-fluid">
					<div class="row align-items-center">
						<div class="col-md-12 col-12">
							<nav aria-label="breadcrumb" class="page-breadcrumb">
								<ol class="breadcrumb">
									<li class="breadcrumb-item"><a href="index">Home</a></li>
									<li class="breadcrumb-item active" aria-current="page">Booking</li>
								</ol>
							</nav>
							<h2 class="breadcrumb-title">Booking</h2>
						</div>
					</div>
				</div>
			</div>
			<!-- /Breadcrumb -->
@if (count($errors) > 0)
<div class="alert alert-danger">
    @foreach ($errors->all() as $error)
    {{ $error }}<br>
    @endforeach
</div>
@elseif(session('success'))
<div class="alert alert-success">{{session('success')}}</div>
@endif
			<!-- Page Content -->
			<div class="content">
				<div class="container">
				
					<div class="row">
						<div class="col-12">
						
							<div class="card">
								<div class="card-body">
									<div class="booking-doc-info">
										<a href="doctor-profile" class="booking-doc-img">
											<img src="assets/img/doctors/doctor-thumb-02.jpg" alt="User Image">
										</a>
										<div class="booking-info">
											<h4><a href="doctor-profile">{{$dokters->nama}}</a></h4>
											<div class="rating">
												<i class="fas fa-star filled"></i>
												<i class="fas fa-star filled"></i>
												<i class="fas fa-star filled"></i>
												<i class="fas fa-star filled"></i>
												<i class="fas fa-star"></i>
												<span class="d-inline-block average-rating">35</span>
											</div>
											<p class="text-muted mb-0"><i class="fas fa-map-marker-alt"></i> {{$dokters->alamat}}</p>
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-12 col-sm-4 col-md-6">
									<h3 class="mb-1">Today:</h3>
									<h4 class="mb-1">{{date("F j, Y")}}</h4>
									<p class="text-muted">{{date('D')}}</p>
								</div>
								<!-- <div class="col-12 col-sm-8 col-md-6 text-sm-right">
									<div class="bookingrange btn btn-white btn-sm mb-3">
										<i class="far fa-calendar-alt mr-2"></i>
										<span></span>
										<i class="fas fa-chevron-down ml-2"></i>
									</div>
								</div> -->
                            </div>
							<!-- Schedule Widget -->
							<div class="card booking-schedule schedule-widget">
								<div id="calendars"></div>
							</div>
							<!-- /Schedule Widget -->
							
							<!-- Submit Section -->
							<!-- <div class="submit-section proceed-btn text-right">
								<a href="checkout" class="btn btn-primary submit-btn">Proceed to Pay</a>
							</div> -->
							<!-- /Submit Section -->
							
						</div>
					</div>
				</div>

			</div>		
            <!-- /Page Content -->
</div>
<div class="modal fade" id="modal-xl">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Extra Large Modal</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">×</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="modal-body-content" style="min-height:100px;">
				</div>
			</div>
		<!-- <div class="modal-footer justify-content-between">
			<div class="modal-footer-content" style="min-height:90px;">
			</div>
		</div> -->
		</div>
		<!-- /.modal-content -->
	</div>
<!-- /.modal-dialog -->
</div>
@endsection
@section('script')
	@include('rekammedis.frontend.booking._form_js')
@endsection