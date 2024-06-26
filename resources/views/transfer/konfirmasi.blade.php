@extends('layout.app')

@section('title')
Konfirmasi Permintaan Transfer
@endsection

@section('breadcrumb')

<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Transaksi</a></li>
    <li class="breadcrumb-item"><a href="#">Konfirmasi Permintaan Transfer</a></li>
    <li class="breadcrumb-item active" aria-current="page">Index</li>
</ol>

@endsection



@section('content')
	<style type="text/css">
		.select2 {
		  width: 100%!important; /* overrides computed width, 100px in your demo */
		}
	</style>

	<div class="card card-info card-outline mb-12 border-left-primary">
	    <div class="card-body">
	      	<h4><i class="fa fa-info"></i> Informasi</h4>
	      	<p>Untuk pencarian, isikan kata yang ingin dicari pada kolom search, lalu tekan enter.</p>
	    </div>
	</div>



	<div class="card card-info card-outline" id="main-box" style="">

  		<div class="card-header">

        	<h3 class="card-title">

          		<i class="fas fa-list"></i>

   				List Permintaan Transfer

        	</h3>

      	</div>

        <div class="card-body">

        	<div class="row">
                <input type="hidden" name="id_apotek" id="id_apotek" value="{{ session('id_apotek_active') }}">
                <div class="form-group col-md-4">

                    {!! Form::select('id_apotek_transfer', $apoteks_tujuans, null, ['id'=>'id_apotek_transfer', 'class' => 'form-control input_select']) !!}

                </div>

        	</div>

			<hr>

			<table  id="tb_data_transfer" class="table table-bordered table-striped table-hover">

		    	<thead>

			        <tr>

			        	<th width="2%"><input type="checkbox" class="checkAlltogle"></th>

			            <th width="3%">No.</th>

                        <th width="10%">Tanggal</th>

			            <th width="35%">Apotek</th>

                        <th width="35%">Status</th>

			            <th width="15%">Action</th>

			        </tr>

		        </thead>

		        <tbody>

		        </tbody>

			</table>

        </div>

  	</div>

@endsection



@section('script')

<script type="text/javascript">

	var token = '{{csrf_token()}}';

 	



 	var tb_data_transfer = $('#tb_data_transfer').DataTable( {

		paging:true,

		destroy: true,

        autoWidth: false,

        processing: true,

        serverSide: true,

        ajax: {

            url: '{{url("transfer/list_konfirmasi")}}',

		        data:function(d){

		        	d.id_apotek = $('#id_apotek').val();

                    d.id_apotek_transfer = $('#id_apotek_transfer').val();

		         }

        },

        order: [],

        columns: [

        	{data: 'checkList', name: 'checkList', orderable: false, searchable: false, width:'1%'},

         	{data: 'DT_RowIndex', name: 'DT_RowIndex',width:"2%"},

            {data: 'tgl_nota', name: 'tgl_nota', class:'text-center'},

            {data: 'id_apotek', name: 'id_apotek'},

            {data: 'is_status', name: 'is_status', class:'text-center'},

            {data: 'action', name: 'id', orderable: true, searchable: true, class:'text-center'}

        ],

        drawCallback: function(callback) {

        }

	});



 	setTimeout(function(){

        $('#tb_data_transfer .checkAlltogle').prop('checked', false);

    }, 1);



	$(document).ready(function(){

		$("#searching_form").submit(function(e){

			e.preventDefault();

			tb_data_transfer.draw(false);

		});

        $('#id_apotek_transfer').change(function(e){
            e.preventDefault();
            tb_data_transfer.draw(false);
        });

        $('.input_select').select2({});

	})



    function delete_transfer(id){

        swal({

            title: "Apakah anda yakin menghapus data ini?",

            type: "warning",

            showCancelButton: true,

            confirmButtonColor: "#DD6B55",

            confirmButtonText: "Ya",

            cancelButtonText: "Tidak",

            closeOnConfirm: false

        },

        function(){

            $.ajax({

                type: "DELETE",

                url: '{{url("transfer")}}/'+id,

                async:true,

                data: {

                    _token:token,

                    id:id

                },

                beforeSend: function(data){

                    // replace dengan fungsi loading

                },

                success:  function(data){

                    if(data==1){

                        swal("Deleted!", "Data transfer berhasil dihapus.", "success");

                    }else{

                        swal("Failed!", "Gagal menghapus data transfer.", "error");

                    }

                },

                complete: function(data){

                    tb_data_transfer.draw(false);

                },

                error: function(data) {

                    swal("Error!", "Ajax occured.", "error");

                }

            });

        });

    }

</script>

@endsection