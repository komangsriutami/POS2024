<!DOCTYPE html>

<html>

    @include('/layout/header')

    @yield('style') 

    <body class="hold-transition sidebar-mini layout-fixed">

        <div class="wrapper">

            @include('/layout/top-navigation')

            @include('/layout/main-navigation')

            <!-- Content Wrapper. Contains page content -->

            <div class="content-wrapper">

                <!-- Content Header (Page header) -->

                <div class="content-header">

                    <div class="container-fluid">

                        <div class="row mb-2">

                            <div class="col-sm-6">

                                <h1 class="m-0 text-dark">@yield('title')</h1>

                            </div>

                            <!-- /.col -->

                            <div class="col-sm-6">

                                <div class="float-right">

                                <a class="btn btn-warning w-md m-b-5" href="#" onclick="getInformasi()"><i class="fa fa-info"></i> Informasi</a> 

                                <a class="btn btn-warning w-md m-b-5" href="#" onclick="getLogBook()"><i class="fa fa-exclamation-triangle"></i> Error Book</a> 

                                <a class="btn btn-warning w-md m-b-5" href="#" onclick="getFAQ()"><i class="fa fa-question"></i> FAQ</a> 

                                </div>

                                <!-- @yield('breadcrumb') -->

                            </div>

                            <!-- /.col -->

                        </div>

                        <!-- /.row -->

                    </div>

                    <!-- /.container-fluid -->

                </div>

                <!-- /.content-header -->

                <!-- Main content -->

                <section class="content">

                    <div class="container-fluid">

                        @yield('content')

                    </div>

                    <!-- /.container-fluid -->

                </section>

                <!-- /.content -->

            </div>

            <!-- /.content-wrapper -->

            <footer class="main-footer">

                <strong>Copyright &copy; 2020

                <a href="https://apotekbwf.com">ApotekBWF.com</a>.

                </strong>

                All rights reserved.

                <div class="float-right d-none d-sm-inline-block">

                    <b>Version</b> 2.0

                </div>

            </footer>

            <!-- Control Sidebar -->

            <aside class="control-sidebar control-sidebar-dark">

                <!-- Control sidebar content goes here -->

            </aside>

            <!-- /.control-sidebar -->

        </div>

        <!-- ./wrapper -->

        <div id="loader"></div>

        @include('/layout/modal') 

        @include('/layout/footer') 

        @include('/layout/validation') 

        @yield('script') 

        <script type="text/javascript">
            $(document).ready(function () {
                $.ajax({
                    url: '{{url("transfer/list_data_transfer")}}',
                    data: {
                        notif: true
                    },
                    method: 'GET',
                    success: function (data) {
                        var countStatus0 = 0;
                        var countStatus1 = 0;
                        var unconfirmed = 0;

                        console.log(data);

                        for (var i = 0; i < data.data.length; i++) {
                            if (data.data[i].is_status === 0) {
                                if (data.data[i].id_apotek === data.id_apotek) {
                                    countStatus0++;
                                }
                                if (data.data[i].id_apotek_transfer === data.id_apotek) {
                                    unconfirmed++;
                                }
                            } else if (data.data[i].is_status === 1) {
                                if (data.data[i].id_apotek === data.id_apotek) {
                                    countStatus1++;
                                }
                            }
                        }

                        $("a.nav-link:contains('Data Permintaan Transfer')").append('<span class="badge badge-success ml-2">' + countStatus1 + '</span><span class="badge badge-danger ml-2">' + countStatus0 + '</span>');
                        $("a.nav-link:contains('Konfirmasi Permintaan Transfer')").append('<span class="badge badge-secondary ml-2">' + unconfirmed + '</span>');
                    },
                    error: function (xhr, status, error) {
                        console.error('Terjadi kesalahan:', error);
                    }
                });
            });
        </script>

        @include('/layout/infobox')

    </body>

</html>