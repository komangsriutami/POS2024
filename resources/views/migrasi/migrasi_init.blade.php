  <div id="progress_generate" class="hide">
      <span id="text-progress"><span class="fa fa-solid fa-spinner fa-spin"></span> &nbsp; Menyiapkan data migrasi ....</span>
      <div class="progress">
        <div id="progress" class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">0%</div>
    </div>
    <div class="modal-footer hide" id="div-finish">
      <button class="btn btn-default" data-dismiss="modal" aria-hidden="true">Selesai</button>  
    </div>
  </div>

  <div id="body_generate">
    {!! Form::model($migrasi, [ 'method' => 'POST','id' => 'form-migrasi','class' => 'form-horizontal','role' => 'form']) !!}

        {!! Form::hidden('id_migrasi', Crypt::encrypt($migrasi->id), ['class' => 'form-control', 'id' => 'form_id_migrasi']) !!}

        @if(!is_null($migrasi_detail))
          {!! Form::hidden('id_migrasi_detail', Crypt::encrypt($migrasi_detail->id), ['class' => 'form-control', 'id' => 'form_id_migrasi_detail']) !!}
        @endif

        <p>Apakah anda yakin ingin melakukan Migrasi tahun <strong>{{$data->tahun}}</strong> bulan <strong>{{$data->bulan}}</strong> ?</p>
        
        <div class="modal-footer">
          <button class="btn btn-default  reset" data-dismiss="modal" aria-hidden="true" id="clear" type="reset" onclick="this.form.reset();">Cancel</button>
          {!! Form::submit('Mulai Migrasi', ['class' => 'btn btn-warning ']) !!}
        </div>
    {!! Form::close() !!}

  <!-- Button trigger modal -->
  <!-- Modal -->
  </div>
  <div id="response_generate" class="hide"></div>

  <script type="text/javascript">
      setTimeout(function() {
          $('.overlay').css('display','none');
      }, 1000);

      var jobs = [];
      var timer_progress;
      var interval = 9000;
      var count_job = 0;
      var finished_job = 0;

      $('#modal-lg').on('hidden.bs.modal', function () {
        /*showProgressAll();
        clearInterval(timer_progress);*/
      });


      $("#form-migrasi").submit(function(e){
        e.preventDefault();
        
        var formData = $(this).serializeArray();

        swal({
            title: "Yakin ingin melanjutkan migrasi data?",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Ya",
            cancelButtonText: "Tidak",
            closeOnConfirm: true
        },
        function(){
          
          $('#progress_generate').removeClass('hide');
          $('#body_generate').addClass('hide');
          
          
            $.post("/migrasi/preparation", formData)
              .done(function(data) {
                  data = $.parseJSON(data);

                  if(data.jumlah_seq >= 1){

                    $('#text-progress').html('<span class="fa fa-solid fa-spinner fa-spin"></span> &nbsp;Sedang menyiapkan data migrasi...');

                    RefreshData();
                    runMigration(data.id,data.jumlah_seq,data.seq);   
                      
                  }else{
                    setTimeout(function() {
                        $('.overlay').css('display','none');
                    }, 1000);

                    $('#progress').css('width','100%');
                    $('#progress').text('100%');
                    $('#body_generate').addClass('hide');
                    $('#response_generate').removeClass('hide');
                    $('#response_generate').prepend(data.result);
                    show_error(data.message);
                  }
              })
              .fail(function (data, status, code) {
                  setTimeout(function() {
                      $('.overlay').css('display','none');
                  }, 1000);
                  show_error(response.message);
                 
                  $('#progress_generate').removeClass('hide');
                  $('#body_generate').addClass('hide');
                  $('#response_generate').removeClass('hide');
                  $('#response_generate').prepend(data.result);
            });
        });
        
        return false;
      });



      function runMigration(id,jumlah_seq,seq_active){
        $('#text-progress').html('<span class="fa fa-solid fa-spinner fa-spin"></span> &nbsp; Mohon menunggu.. Proses Migrasi sedang berjalan...');

        if(jumlah_seq > 0){

          if(jumlah_seq == 1){
            interval = 3000;
          } else {
            interval = 6000;
          }

          finished_job = 0;

          if(seq_active > 0){
            count_job = 1;
            execute_jobs(id,seq_active,seq_active);
          } else {
            // showProgress(id,0);
            count_job = jumlah_seq;
            for (let seq = 1; seq <= jumlah_seq; seq++) {
              execute_jobs(id,seq,0);
            }
          }
          
        } else {
          show_error("Antrian migrasi tidak ada");
        }
      }


      function execute_jobs(id,seq,progress_seq){
        setTimeout(function() {
          
          $.ajax({
              type: "POST",
              url: "{{url('/kwitansi-migrasi/runmigration')}}",
              async: true,
              dataType: 'json',
              data: {
                  _token : "{{csrf_token()}}",
                  id:id,
                  seq:seq
              },
              beforeSend: function(data){
                if(progress_seq > 0){
                  // showProgress(id,seq);
                }
                
              },
              success:  function(data){
                  
              },
              complete: function(data){
                finished_job++;

                getProgress(finished_job);

                if(finished_job == count_job){
                  clearInterval(timer_progress);
                  $('#text-progress').html('<span class="fa fa-solid fa-spinner fa-check"></span> &nbsp;Migrasi data selesai.');
                  RefreshData();
                  $('#div-finish').removeClass('hide');
                }
              },
              error: function(data) {
                  swal("Error!", "Ajax occured.", "error");
              }
          });

        }, 1200);
      }

      function getProgress(finished_job)
      {
        persentase = Math.ceil(finished_job/count_job*100);
        $('#progress').css('width',persentase+'%');
        $('#progress').text(finished_job+'/'+count_job+' ('+persentase+'%)'); 
      }



      function getProgress_ajax(id,seq)
      {
          $.ajax({
              type: "POST",
              url: "{{url('/kwitansi-migrasi/getprogress')}}",
              dataType: 'json',
              async: true,
              data: {
                  _token : "{{csrf_token()}}",
                  id:id,
                  seq:seq
              },
              beforeSend: function(data){
              },
              success:  function(data){
                  $('#progress').css('width',data.persentase+'%');
                  $('#progress').text(data.jumlah_migrasi+'/'+data.jumlah_data+' ('+data.persentase+'%)');

                  if(data.status){
                    if(data.persentase == 100){
                      $('#text-progress').html('Generate kwitansi selesai...');
                      $('#id_tahun_ajar').trigger("change");
                      RefreshData();
                    }
                  } else {
                      $("#div-detail-progress").html("");
                      $("#div-progress-generate").show("FadeOut");
                      $('#id_tahun_ajar').trigger("change");
                  }
              },
              complete: function(data){

              },
              error: function(data) {
                  swal("Error!", "Ajax occured.", "error");
              }
          });
      }


      function showProgress(id,seq)
      {
          /*$("#div-progress-generate").show("FadeIn");*/
          timer_progress = setInterval(function(){ 
              getProgress(id,seq);
          }, interval);        
      }

</script>