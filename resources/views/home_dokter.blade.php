@extends('layout.app_apoteker')

@section('title')
Home
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item active" aria-current="page">Home</li>
</ol>
@endsection

@section('content')
	<div class="card mb-12 border-left-primary card-info">
	    <div class="card-body">
	      	<div class="row">
	      		<?php 
                    $date = date('d-m-Y H:i:s');
                ?>
            	<div class="col-lg-12 col-12">
			        <!-- small box -->
			        <div class="small-box bg-secondary">
			            <div class="inner text-center">
			                <h3>Selamat Datang!</h3>
			                <p>Taanggal Hari ini : {{ $date }}</p>
			            </div>
			            <div class="icon">
			                <i class="fa fa-hospital-user"></i>
			            </div>
			        </div>
			    </div>
            	<div class="col-lg-3 col-6">
			        <!-- small box -->
			        <div class="small-box bg-info">
			            <div class="inner">
			                <h3>150</h3>
			                <p>New Orders</p>
			            </div>
			            <div class="icon">
			                <i class="ion ion-bag"></i>
			            </div>
			            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
			        </div>
			    </div>
			    <!-- ./col -->
			    <div class="col-lg-3 col-6">
			        <!-- small box -->
			        <div class="small-box bg-success">
			            <div class="inner">
			                <h3>53<sup style="font-size: 20px">%</sup></h3>
			                <p>Bounce Rate</p>
			            </div>
			            <div class="icon">
			                <i class="ion ion-stats-bars"></i>
			            </div>
			            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
			        </div>
			    </div>
			    <!-- ./col -->
			    <div class="col-lg-3 col-6">
			        <!-- small box -->
			        <div class="small-box bg-warning">
			            <div class="inner">
			                <h3>44</h3>
			                <p>User Registrations</p>
			            </div>
			            <div class="icon">
			                <i class="ion ion-person-add"></i>
			            </div>
			            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
			        </div>
			    </div>
			    <!-- ./col -->
			    <div class="col-lg-3 col-6">
			        <!-- small box -->
			        <div class="small-box bg-danger">
			            <div class="inner">
			                <h3>65</h3>
			                <p>Unique Visitors</p>
			            </div>
			            <div class="icon">
			                <i class="ion ion-pie-graph"></i>
			            </div>
			            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
			        </div>
			    </div>
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
