<!--
Model : Layout Index Tabel Pasien
Author : Tangkas.
Date : 12/06/2021
-->

@extends('layout.app')
@section('title')
    Data Pasien
@endsection

@section('breadcrumb')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="#">Home</a></li>
        <li class="breadcrumb-item active">Data Pasien</li>
    </ol>
@endsection

@section('content')
    <div class="card card-info card-outline mb-12 border-left-primary">
        <div class="card-body">
            <h4><i class="fa fa-info"></i> Informasi</h4>
            <p>Untuk pencarian, isikan kata yang ingin dicari pada kolom seacrh, lalu tekan enter.</p>
            <a class="btn btn-success w-md m-b-5" href="{{ url('pasien/create') }}"><i class="fa fa-plus"></i> Tambah
                Data</a>
            <a class="btn btn-warning w-md m-b-5" href="{{ url('pasien/invite') }}"><i class="fa fa-plus mr-1"></i>Invite</a>
        </div>
    </div>

    <div class="card card-info card-outline" id="main-box" style="">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i>
                List Data Pasien
            </h3>
        </div>
        <div class="card-body">
            <table id="tb_pasien" class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th width="5%">No.</th>
                        <th width="20%">Nama</th>
                        <th width="20%">Alamat</th>
                        <th width="20%">Email</th>
                        <th width="10%">Telepon</th>
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

        var tb_pasien = $('#tb_pasien').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            ajax: {
                url: '{{ url("pasien/list_data") }}',
                data: function(d) {}
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
                    data: 'email',
                    name: 'email'
                },
                {
                    data: 'telepon',
                    name: 'telepon'
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
                url: '{{ url("pasien") }}/' + id + '/edit',
                async: true,
                data: {
                    _token: "{{ csrf_token() }}",
                },
                beforeSend: function(data) {
                    // on_load();
                    $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class",
                        "modal-header bg-light-blue");
                    $("#modal-xl .modal-title").html("Edit Data - Pasien");
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
                url: '{{ url("pasien/") }}/' + id,
                dataType: "json",
                data: data,
                beforeSend: function(data) {
                    // replace dengan fungsi loading
                },
                success: function(data) {
                    if (data == 1) {
                        show_info("Data Pasien berhasil disimpan!");
                        $('#modal-xl').modal('toggle');
                    } else {
                        show_error("Gagal menyimpan data ini!");
                        return false;
                    }
                },
                complete: function(data) {
                    // replace dengan fungsi mematikan loading
                    // tb_pasien.fnDraw(false);
                    tb_pasien.draw(false);
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
                        url: '{{ url("pasien") }}/' + id,
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
                                swal("Deleted!", "Data Pasien berhasil dihapus.", "success");
                            } else {

                                swal("Failed!", "Gagal menghapus Data Pasien.", "error");
                            }
                        },
                        complete: function(data) {
                            // tb_pasien.fnDraw(false);
                            tb_pasien.draw(false);
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
                url: '{{ url("pasien") }}/' + id,
                async: true,
                data: {
                    _token: "{{ csrf_token() }}",
                },
                beforeSend: function(data) {
                    // on_load();
                    $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class",
                        "modal-header bg-light-blue");
                    $("#modal-xl .modal-title").html("Detail Data - Pasien");
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
