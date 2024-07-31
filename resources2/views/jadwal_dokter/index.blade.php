@extends('layout.app')
@section('title')
    Jadwal Dokter
@endsection

@section('breadcrumb')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="#">Home</a></li>
        <li class="breadcrumb-item active">Jadwal Dokter</li>
    </ol>
@endsection

@section('content')
    <div class="card card-info card-outline mb-12 border-left-primary">
        <div class="card-body">
            <h4><i class="fa fa-info"></i> Informasi</h4>
            <p>Untuk pencarian, isikan kata yang ingin dicari pada kolom seacrh, lalu tekan enter.</p>
            <a class="btn btn-success w-md m-b-5" href="{{ url('jadwal_dokter/create') }}"><i class="fa fa-plus"></i> Tambah
                Data</a>
        </div>
    </div>

    <div class="card card-info card-outline" id="main-box" style="">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i>
                List Data Dokter
            </h3>
        </div>
        <div class="card-body">
            <form role="form" id="searching_form">
                <div class="row">
                    <div class="form-group col-md-3">
                        {!! Form::label('tahun', 'Pilih Tahun') !!}
                        <select id="tahun" name="tahun" class="form-control input_select">
                            <option value="2021" {!!( "2021" == $tahun ? 'selected' : '')!!}>2021</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        {!! Form::label('bulan', 'Pilih Bulan') !!}
                        <select id="bulan" name="bulan" class="form-control input_select">
                            <option value="1" {!!( "1" == $bulan ? 'selected' : '')!!}>Januari</option>
                            <option value="2" {!!( "2" == $bulan ? 'selected' : '')!!}>Februari</option>
                            <option value="3" {!!( "3" == $bulan ? 'selected' : '')!!}>Maret</option>
                            <option value="4" {!!( "4" == $bulan ? 'selected' : '')!!}>April</option>
                            <option value="5" {!!( "5" == $bulan ? 'selected' : '')!!}>Mei</option>
                            <option value="6" {!!( "6" == $bulan ? 'selected' : '')!!}>Juni</option>
                            <option value="7" {!!( "7" == $bulan ? 'selected' : '')!!}>Juli</option>
                            <option value="8" {!!( "8" == $bulan ? 'selected' : '')!!}>Agustus</option>
                            <option value="9" {!!( "9" == $bulan ? 'selected' : '')!!}>September</option>
                            <option value="10" {!!( "10" == $bulan ? 'selected' : '')!!}>Oktober</option>
                            <option value="11" {!!( "11" == $bulan ? 'selected' : '')!!}>November</option>
                            <option value="12" {!!( "12" == $bulan ? 'selected' : '')!!}>Desember</option>
                        </select>
                    </div>
                    <div class="col-lg-12" style="text-align: center;">
                        <button type="submit" class="btn btn-primary" id="datatable_filter"><i class="fa fa-search"></i> Cari</button>
                    </div>
                </div>
            </form>
            <hr>
            <table id="tb_dokter" class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th width="5%">No.</th>
                        <th width="35%">Nama</th>
                        <th width="20%">Alamat</th>
                        <th width="10%">Telepon</th>
                        <th width="20%">Gambar</th>
                        <th width="10%">Action</th>
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
        var token = '{{ csrf_token() }}';

        var tb_dokter = $('#tb_dokter').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            ajax: {
                url: '{{url("jadwal_dokter/list_data")}}',
                data: function(d) {
                    d.tahun = $('#tahun').val();
                    d.bulan = $('#bulan').val();
                }
            },
            columns: [{
                    data: 'no',
                    name: 'no',
                    width: "2%"
                },
                {
                    data: 'nama',
                    name: 'nama'
                },
                {
                    data: 'alamat',
                    name: 'alamat'
                },
                {
                    data: 'telepon',
                    name: 'telepon'
                },
                {
                    data: 'img',
                    name: 'img'
                },
                {
                    data: 'action',
                    name: 'id',
                    orderable: true,
                    searchable: true
                }
            ],
            rowCallback: function(row, data, iDisplayIndex) {
                var api = this.api();
                var info = api.page.info();
                var page = info.page;
                var length = info.length;
                var index = (page * length + (iDisplayIndex + 1));
                $('td:eq(0)', row).html(index);
            },
            stateSaveCallback: function(settings, data) {
                localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data))
            },
            stateLoadCallback: function(settings) {
                return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance))
            },
            drawCallback: function(settings) {
                var api = this.api();
            }
        });

        $(document).ready(function() {})

        function edit_data(id) {
            $.ajax({
                type: "GET",
                url: '{{ url('dokter') }}/' + id + '/edit',
                async: true,
                data: {
                    _token: "{{ csrf_token() }}",
                },
                beforeSend: function(data) {
                    // on_load();
                    $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class",
                        "modal-header bg-light-blue");
                    $("#modal-xl .modal-title").html("Edit Data - Dokter");
                    $('#modal-xl').modal("show");
                    $('#modal-xl').find('.modal-body-content').html('');
                    $("#modal-xl").find(".overlay").fadeIn("200");
                },
                success: function(data) {
                    $('#modal-xl').find('.modal-body-content').html(data);
                },
                complete: function(data) {
                    $("#modal-xl").find(".overlay").fadeOut("200");
                },
                error: function(data) {
                    alert("error ajax occured!");
                }

            });
        }

        function submit_valid(id) {
            // if ($(".validated_form").valid()) {
            data = {};
            $("#form-edit").find("input[name], select").each(function(index, node) {
                data[node.name] = node.value;
            });

            $.ajax({
                type: "PUT",
                url: '{{ url('dokter/') }}/' + id,
                dataType: "json",
                data: data,
                beforeSend: function(data) {
                    // replace dengan fungsi loading
                },
                success: function(data) {
                    if (data == 1) {
                        show_info("Data Dokter berhasil disimpan!");
                        $('#modal-xl').modal('toggle');
                    } else {
                        show_error("Gagal menyimpan data ini!");
                        return false;
                    }
                },
                complete: function(data) {
                    // replace dengan fungsi mematikan loading
                    // tb_dokter.fnDraw(false);
                    tb_dokter.draw(false);
                },
                error: function(data) {
                    show_error("error ajax occured!");
                }

            })
            // } else {
            //     return false;
            // }
        }

        function delete_data(id) {
            swal({
                    title: "Apakah anda yakin menghapus data ini?",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Ya",
                    cancelButtonText: "Tidak",
                    closeOnConfirm: false
                },
                function() {
                    $.ajax({
                        type: "DELETE",
                        url: '{{ url('dokter') }}/' + id,
                        async: true,
                        data: {
                            _token: token,
                            id: id
                        },
                        beforeSend: function(data) {
                            // replace dengan fungsi loading
                        },
                        success: function(data) {
                            if (data == 1) {
                                swal("Deleted!", "Data Dokter berhasil dihapus.", "success");
                            } else {

                                swal("Failed!", "Gagal menghapus Data Dokter.", "error");
                            }
                        },
                        complete: function(data) {
                            // tb_dokter.fnDraw(false);
                            tb_dokter.draw(false);
                        },
                        error: function(data) {
                            swal("Error!", "Ajax occured.", "error");
                        }
                    });
                });
        }

        function show_data(id) {
            $.ajax({
                type: "GET",
                url: '{{ url('dokter') }}/' + id,
                async: true,
                data: {
                    _token: "{{ csrf_token() }}",
                },
                beforeSend: function(data) {
                    // on_load();
                    $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class",
                        "modal-header bg-light-blue");
                    $("#modal-xl .modal-title").html("Detail Data - Dokter");
                    $('#modal-xl').modal("show");
                    $('#modal-xl').find('.modal-body-content').html('');
                    $("#modal-xl").find(".overlay").fadeIn("200");
                },
                success: function(data) {
                    $('#modal-xl').find('.modal-body-content').html(data);
                },
                complete: function(data) {
                    $("#modal-xl").find(".overlay").fadeOut("200");
                },
                error: function(data) {
                    alert("error ajax occured!");
                }

            });
        }

    </script>
@endsection
