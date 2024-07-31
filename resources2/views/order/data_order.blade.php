@extends('layout.app')

@section('title')
List Data Order
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Transaksi</a></li>
    <li class="breadcrumb-item"><a href="#">List Data Order</a></li>
    <li class="breadcrumb-item active" aria-current="page">Index</li>
</ol>
@endsection

@section('content')
    {!! Html::style('assets/dist/sweetalert2/sweetalert2.min.css') !!}
    <style type="text/css">
        .select2 {
          width: 100%!important; /* overrides computed width, 100px in your demo */
        }
    </style>

    <div class="card card-info card-outline mb-12 border-left-primary">
        <div class="card-body">
            <h4><i class="fa fa-info"></i> Informasi</h4>
            <p>Untuk pencarian, isikan kata yang ingin dicari pada kolom search, lalu tekan enter.</p>
            <a class="btn btn-success w-md m-b-5" href="{{url('order/create_manual')}}"><i class="fa fa-plus"></i> Tambah Data</a>
        </div>
    </div>

    <div class="card card-info card-outline" id="main-box" style="">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i>
                List Data Order
            </h3>
        </div>
        <div class="card-body">
            <form role="form" id="searching_form">
                <div class="row">
                    <?php
                        $dateNew = date('m/01/Y').' - '.date('m/d/Y');
                    ?>
                    <div class="form-group col-md-4">
                        {!! Form::select('id_suplier', $supliers, null, ['id'=>'id_suplier', 'class' => 'form-control input_select']) !!}
                    </div>
                    <div class="form-group col-md-2">
                        {!! Form::select('id_jenis', $jenisSPs, null, ['id'=>'id_jenis', 'class' => 'form-control input_select']) !!}
                    </div>
                    <div class="col-lg-3 form-group">
                        <input type="text" id="search_tanggal" class="form-control" placeholder="Masukan Tanggal" value="{{ $dateNew }}">
                    </div>
                    <div class="form-group col-md-3">
                        <input type="text" id="kode" name="kode" class="form-control" placeholder="[Masukan nomor/kode SP]">
                    </div>

                    <div class="col-lg-12" style="text-align: center;">
                        <button type="submit" class="btn btn-primary" id="datatable_filter"><i class="fa fa-search"></i> Cari</button> 
                    </div>
                </div>
            </form>
            <hr>
            <table  id="tb_data_order" class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th width="2%"><input type="checkbox" class="checkAlltogle"></th>
                        <th width="3%">No.</th>
                        <th width="10%">Tanggal</th>
                        <th width="20%">Apotek</th>
                        <th width="20%">Suplier</th>
                        <th width="10%">No. SP</th>
                        <th width="10%">Konfirmasi Barang</th>
                        <th width="10%">Sign</th>
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
{!! Html::script('assets/dist/sweetalert2/sweetalert2.all.min.js') !!}
<script type="text/javascript">
    var token = '{{csrf_token()}}';
    

    var tb_data_order = $('#tb_data_order').DataTable( {
        paging:true,
        destroy: true,
        autoWidth: false,
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{url("order/list_data_order")}}',
                data:function(d){
                    d.id_jenis = $('#id_jenis').val();
                    d.id_suplier = $('#id_suplier').val();
                    d.kode = $("#kode").val();
                    d.tanggal = $('#search_tanggal').val();
                 }
        },
        order: [],
        columns: [
            {data: 'checkList', name: 'checkList', orderable: false, searchable: false, width:'1%'},
            {data: 'DT_RowIndex', name: 'DT_RowIndex',width:"2%", orderable: false, searchable: false},
            {data: 'tgl_nota', name: 'tgl_nota', class:'text-center'},
            {data: 'apotek', name: 'apotek'},
            {data: 'suplier', name: 'suplier'},
            {data: 'kode', name: 'kode'},
            {data: 'is_status', name: 'is_status'},
            {data: 'is_sign', name: 'is_sign', class:'text-center'},
            {data: 'action', name: 'id', orderable: true, searchable: true, class:'text-center'}
        ],
        drawCallback: function(callback) {
        }
    });

    setTimeout(function(){
        $('#tb_data_order .checkAlltogle').prop('checked', false);
    }, 1);

    $(document).ready(function(){
        $("#searching_form").submit(function(e){
            e.preventDefault();
            tb_data_order.draw(false);
        });

        $('#search_tanggal').daterangepicker({
            autoclose:true,
            //format:"yyyy-mm-dd",
            forceParse: false
        });

        $('#id_jenis, #id_suplier, #tanggal').change(function(){
            tb_data_order.draw(false);
        });

        $('.input_select').select2({});
    })

    function delete_order(id){
        Swal.fire({
          title: 'Apakah anda yakin menghapus data ini?',
          showDenyButton: true,
          showCancelButton: true,
          confirmButtonText: 'Ya',
          denyButtonText: "Tidak",
        }).then((result) => {
          /* Read more about isConfirmed, isDenied below */
          if (result.isConfirmed) {
            $.ajax({

                type: "DELETE",

                url: '{{url("order")}}/'+id,

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

                        Swal.fire("Deleted!", "Data order berhasil dihapus.", "success");

                    }else{

                        Swal.fire("Failed!", "Gagal menghapus data order.", "error");

                    }

                },

                complete: function(data){

                    tb_data_order.draw(false);

                },

                error: function(data) {

                    Swal.fire("Error!", "Ajax occured.", "error");

                }

            });
          } else if (result.isDenied) {
            /*Swal.fire('Changes are not saved', '', 'info')*/
          }
        });
    }

    function sign(id) {
        Swal.fire({
            title: "Tanda Tangan",
            html: `<input type="password" id="password" class="swal2-input" placeholder="Password">`,
            confirmButtonText: 'Sign',
            focusConfirm: false,
            preConfirm: () => {
                const password = Swal.getPopup().querySelector('#password').value
                if (!password) {
                  Swal.showValidationMessage(`tuliskan password pada kolom input!`)
                }
                return {password: password }
            }
        }).then((result) => {
            if (result.value.password === null) return false;
            if (result.value.password === "") {
                swal.showInputError("tuliskan password pada kolom input!");
                return false
            }

            $.ajax({
                type: "POST",
                url: '{{url("order/send_sign")}}',
                async:true,
                data: {
                    _token:token,
                    id:id,
                    password:result.value.password
                },
                beforeSend: function(data){
                    // replace dengan fungsi loading
                },
                success:  function(data){
                    if(data.status ==1){
                        Swal.fire("Success!", data.message , "success");
                    }else{
                        
                        Swal.fire("Failed!", data.message, "error");
                    }
                },
                complete: function(data){
                    tb_data_order.draw(false);
                },
                error: function(data) {
                    Swal.fire("Error!", "Ajax occured.", "error");
                }
            });
            //Swal.fire("Nice!", "You wrote: " + inputValue, "success");
        });
    }

    function unsign(id) {
        Swal.fire({
            title: "Batal Tanda Tangan",
            html: `<input type="password" id="password" class="swal2-input" placeholder="Password">`,
            confirmButtonText: 'Sign',
            focusConfirm: false,
            preConfirm: () => {
                const password = Swal.getPopup().querySelector('#password').value
                if (!password) {
                  Swal.showValidationMessage(`tuliskan password pada kolom input!`)
                }
                return {password: password }
            }
        }).then((result) => {
            if (result.value.password === null) return false;
            if (result.value.password === "") {
                swal.showInputError("tuliskan password pada kolom input!");
                return false
            }

            $.ajax({
                type: "POST",
                url: '{{url("order/send_unsign")}}',
                async:true,
                data: {
                    _token:token,
                    id:id,
                    password:result.value.password
                },
                beforeSend: function(data){
                    // replace dengan fungsi loading
                },
                success:  function(data){
                    if(data.status ==1){
                        Swal.fire("Success!", data.message , "success");
                    }else{
                        
                        Swal.fire("Failed!", data.message, "error");
                    }
                },
                complete: function(data){
                    tb_data_order.draw(false);
                },
                error: function(data) {
                    Swal.fire("Error!", "Ajax occured.", "error");
                }
            });
            //Swal.fire("Nice!", "You wrote: " + inputValue, "success");
        });
    }
</script>
@endsection