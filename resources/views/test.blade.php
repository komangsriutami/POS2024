@extends('layout.app')

@section('title')
Recap Data Transaksi
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Recap Data Transaksi</a></li>
    <li class="breadcrumb-item active" aria-current="page">Index</li>
</ol>
@endsection

@section('content')
	<style type="text/css">
        /*custom style, untuk hide datatable length dan search value*/
        .dataTables_filter{
            display: none;
        }
        .select2 {
          width: 100%!important; /* overrides computed width, 100px in your demo */
        }
    </style>

	<div class="container">
        <h1>AdminLTE Progress Bar Example</h1>
        <div class="progress">
            <div id="progress-bar" class="progress-bar bg-success" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
        </div>
        <button id="start-button" class="btn btn-primary mt-3">Start Progress</button>
    </div>
@endsection

@section('script')
<script type="text/javascript">
	 $(document).ready(function() {
            $('#start-button').on('click', function() {
                let progressBar = $('#progress-bar');
                let width = 0;
                
                let interval = setInterval(function() {
                    width += 10;
                    progressBar.css('width', width + '%');
                    progressBar.attr('aria-valuenow', width);
                    progressBar.text(width + '%');
                    
                    if (width >= 100) {
                        clearInterval(interval);
                    }
                }, 1000); // Increase the width by 10% every second
            });
        });
</script>
@endsection