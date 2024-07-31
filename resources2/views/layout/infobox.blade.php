<?php
  // how to call

  // $error = "Gagal Paham";
  // $this->session->set_flashdata('error', $error);

  // $info = "Gagal Paham";
  // $this->session->set_flashdata('info', $info);


  $show_error = false;
  if(session('error')){
    $show_error = true;
  }

  $show_warning = false;
  if(session('warning')){
    $show_warning = true;
  }
  

  $show_info = false;
  if(session('success')){
    $show_info = true;
  }
  
?>

<div class="alert_container">
  <div class="info alert_box">
    <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="remove" onClick=" smooth_hide( $(this).parent().parent() ) "><i class="fa fa-times"></i></button>
        </div>
        <div class="message">
          <h3>Info</h3>
      <span class="message_content">
        @if(session('success'))
          <p>{!!session('success')!!}</p>
        @else
          No Info
        @endif
      </span>
        </div>
  </div>

  <div class="error alert_box">
    <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="remove" onClick=" smooth_hide( $(this).parent().parent() ) "><i class="fa fa-times"></i></button>
        </div>
        <div class="message">
          <h3>Error</h3>
      <span class="message_content">
        @if(session('error'))
          <p>{!!session('error')!!}</p>
        @else
          No Error
        @endif
      </span>
        </div>
  </div>
</div>

<div class="alert_container">
  <div class="warning alert_box">
    <div class="box-tools pull-right">
      <button type="button" class="btn btn-box-tool" data-widget="remove" onClick=" smooth_hide( $(this).parent().parent() ) "><i class="fa fa-times"></i></button>
    </div>
    <div class="message">
          <h3>Warning</h3>
          <span class="message_content">
          @if(session('warning'))
            <p>{!!session('warning')!!}</p>
          @else
            No Info
          @endif
        </span>
    </div>
</div>

<style type="text/css">
  .alert_container{
    z-index: 21051994;
    position :fixed;
    right: 20px;
    bottom: 20px;
  }

  .alert_box{
    width: 250px;
    min-height: 100px;
    display: none;
    margin-top: 10px;
    opacity: 0.8;
    box-shadow: 8px 8px 8px #888;
  }

  .alert_box:hover{
    opacity: 1;
  }

  .alert_box .box-tools .btn-box-tool{
    color: white !important;
  }

  .alert_container .error{
    background-color: #dd4b39;
  }

  .alert_container .info{
    background-color: #00a65a;
  }

  .alert_container .warning{
    background-color: #ff8f00;
  }

  .alert_box .message{
    padding: 10px 10px 10px 15px;
    color: white;
  }

  .alert_box .message h3{
    margin: 0;
    padding: 0;
    font-size: 16px;
    font-weight: bold;
  }
</style>

<script type="text/javascript">
  $(document).ready(function(){
    <?php if($show_error){ ?> 
      $(".alert_container .error").fadeIn();
    <?php } ?>

    <?php if($show_info){ ?>  
      $(".alert_container .info").fadeIn();
    <?php } ?>

    <?php if($show_warning){ ?>  
      $(".alert_container .warning").fadeIn();
    <?php } ?>

      setTimeout(function() {
          $(".alert_container .error").fadeOut();
          $(".alert_container .info").fadeOut();
          $(".alert_container .warning").fadeOut();
      }, 20000);

  })

  function smooth_hide( object ){
    $(object).fadeOut();
  }

  function show_error(message){
    $(".alert_container .error .message .message_content").html(message);
    $(".alert_container .error").fadeIn();

    setTimeout(function() {
          $(".alert_container .error").fadeOut();
      }, 20000);
  }

  function show_info(message){
    $(".alert_container .info .message .message_content").html(message);
    $(".alert_container .info").fadeIn();

    setTimeout(function() {
          $(".alert_container .info").fadeOut();
      }, 20000);
  }

  function show_warning(message){
    $(".alert_container .warning .message .message_content").html(message);
    $(".alert_container .warning").fadeIn();

    setTimeout(function() {
          $(".alert_container .warning").fadeOut();
      }, 20000);
  }
</script>