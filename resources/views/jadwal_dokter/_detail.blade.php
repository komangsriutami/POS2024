@extends('layout.app')
@section('title')
    Detail Jadwal Dokter
@endsection

@section('breadcrumb')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="#">Home</a></li>
        <li class="breadcrumb-item active">Detail Jadwal Dokter</li>
    </ol>
@endsection

@section('content')
    <style type="text/css">
        .select2 {
          width: 100%!important; /* overrides computed width, 100px in your demo */
        }
    </style>

    <div class="card card-info card-outline" id="main-box" style="">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-star"></i> Jadwal Dokter
            </h3>
            <div class="card-tools">
                <a href="{{url('jadwal_dokter')}}" class="btn btn-danger btn-sm pull-right" data-toggle="tooltip" data-placement="top" title="Kembali ke daftar data"><i class="fa fa-undo"></i> Kembali</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-sm-6">
                    <input type="hidden" name="id_dokter" id="id_dokter" value="{{ $dokter->id }}">
                    <input type="hidden" name="tahun" id="tahun" value="{{ $tahun }}">
                    <input type="hidden" name="bulan" id="bulan" value="{{ $bulan }}">
                    <h3 class="m-t-0">Detail Jadwal Dokter</h3>
                    <table width="100%">
                        <?php
                        ?>
                        
                        <tr>
                            <td width="20%">Nama</td>
                            <td width="2%"> : </td>
                            <td width="78">{{ $dokter->nama }}</td>
                        </tr>
                        <tr>
                            <td width="20%">No Telp</td>
                            <td width="2%"> : </td>
                            <td width="78">{{ $dokter->telepon }}</td>
                        </tr>
                        <tr>
                            <td width="20%">Alamat</td>
                            <td width="2%"> : </td>
                            <td width="78">{{ $dokter->alamat }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <span class="text-info"><i class="fas fa-info"></i>&nbsp;Untuk pencarian, isikan kata yang ingin dicari pada kolom search, lalu tekan enter.</span>
        </div>
    </div>
    <div class="card card-info card-outline" id="main-box" style="">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i>
                Histori Jadwal
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="table-responsive">
                    <div class="table-responsive">
                        <table  id="tb_data" class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                <th width="5%">No.</th>
                                <th width="20%">Tanggal</th>
                                <th width="20%">Sesi</th>
                                <th width="20%">Start</th>
                                <th width="20%">End</th>
                                <th width="15%">Jumlah Pasien</th>
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        var token = '{{ csrf_token() }}';

        var tb_data = $('#tb_data').dataTable( {
            processing: true,
            serverSide: true,
            stateSave: true,
            ajax:{
                    url: '{{url("jadwal_dokter/list_jadwal_dokter")}}',
                    data:function(d){
                        d.id_dokter = $("#id_dokter").val();
                        d.tahun = $("#tahun").val();
                        d.bulan = $("#bulan").val();
                    }
                 },
            columns: [
                {data: 'no', name: 'no',width:"2%"},
                {data: 'tgl', name: 'tgl', class: 'text-center'},
                {data: 'id_sesi', name: 'id_sesi', class:'text-center'},
                {data: 'start', name: 'start'},
                {data: 'end', name: 'end'},
                 {data: 'jumlah_pasien', name: 'jumlah_pasien', class: 'text-center'}
            ],
            rowCallback: function( row, data, iDisplayIndex ) {
                var api = this.api();
                var info = api.page.info();
                var page = info.page;
                var length = info.length;
                var index = (page * length + (iDisplayIndex +1));
                $('td:eq(0)', row).html(index);
            },
            stateSaveCallback: function(settings,data) {
                localStorage.setItem( 'DataTables_' + settings.sInstance, JSON.stringify(data) )
            },
            stateLoadCallback: function(settings) {
                return JSON.parse( localStorage.getItem( 'DataTables_' + settings.sInstance ) )
            },
            drawCallback: function( settings ) {
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
