<!DOCTYPE html>
<html>
    @include('/layout/header')
    @yield('style') 
    <body class="hold-transition layout-fixed">
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
        @include('/layout/infobox')
    </body>
</html>