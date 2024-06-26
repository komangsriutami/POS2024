<!--
Model : Layout Backend Index Tabel Message Footer pada Frontend
Author : Tangkas.
Date : 12/06/2021
-->

@extends('layout.app')
@section('title')
    Data Message Footer
@endsection

@section('breadcrumb')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="#">Home</a></li>
        <li class="breadcrumb-item active">Data Message Footer</li>
    </ol>
@endsection

@section('content')
    <div class="card card-info card-outline mb-12 border-left-primary">
        <div class="card-body">
            <h4><i class="fa fa-info"></i> Informasi</h4>
            <p>Untuk pencarian, isikan kata yang ingin dicari pada kolom seacrh, lalu tekan enter.</p>
        </div>
    </div>

    <div class="card card-info card-outline" id="main-box" style="">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i>
                List Data Message
            </h3>
        </div>
        <div class="card-body">
            <table id="tb_message" class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th width="5%">No.</th>
                        <th width="20%">Full Name</th>
                        <th width="20%">Email</th>
                        <th width="20%">Phone Number</th>
                        <th width="10%">Additional Message</th>
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

        var tb_message = $('#tb_message').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            ajax: {
                url: '{{ url("message/list_data") }}',
                data: function(d) {}
            },
            columns: [{
                    data: 'no',
                    name: 'no',
                    width: "2%"
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'email',
                    name: 'email'
                },
                {
                    data: 'phone_number',
                    name: 'phone_number'
                },
                {
                    data: 'additional_message',
                    name: 'additional_message'
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
                url: '{{ url('message') }}/' + id + '/edit',
                async: true,
                data: {
                    _token: "{{ csrf_token() }}",
                },
                beforeSend: function(data) {
                    // on_load();
                    $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class",
                        "modal-header bg-light-blue");
                    $("#modal-xl .modal-title").html("Edit Data - Message");
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
                    console.log(data);
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
                url: '{{ url('message/') }}/' + id,
                dataType: "json",
                data: data,
                beforeSend: function(data) {
                    // replace dengan fungsi loading
                },
                success: function(data) {
                    if (data == 1) {
                        show_info("Data Message berhasil disimpan!");
                        $('#modal-xl').modal('toggle');
                    } else {
                        show_error("Gagal menyimpan data ini!");
                        return false;
                    }
                },
                complete: function(data) {
                    // replace dengan fungsi mematikan loading
                    // tb_message.fnDraw(false);
                    tb_message.draw(false);
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
                        url: '{{ url('message') }}/' + id,
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
                                swal("Deleted!", "Data Message berhasil dihapus.", "success");
                            } else {

                                swal("Failed!", "Gagal menghapus Data Message.", "error");
                            }
                        },
                        complete: function(data) {
                            // tb_message.fnDraw(false);
                            tb_message.draw(false);
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
                url: '{{ url('message') }}/' + id,
                async: true,
                data: {
                    _token: "{{ csrf_token() }}",
                },
                beforeSend: function(data) {
                    // on_load();
                    $('#modal-xl').find('.modal-xl').find(".modal-content").find(".modal-header").attr("class",
                        "modal-header bg-light-blue");
                    $("#modal-xl .modal-title").html("Detail Data - Message");
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
