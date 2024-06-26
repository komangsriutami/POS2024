<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="index3.html" class="brand-link">
    <img src="{{ asset('assets/dist/img/AdminLTELogo.png') }}" alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
        style="opacity: .8">
    <span class="brand-text font-weight-light">{{ session('apotek_group')->nama_singkat }} - POS System</span>
    </a>
    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="{{ asset('assets/dist/img/user-icon.png') }}" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block">{{ Auth::user()->nama }}</a>
            </div>
        </div>
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- Add icons to the links using the .nav-icon class
                    with font-awesome or any other icon font library -->

                <?php 
                    $menu = session('menu');
                    $id_apotek_active = session('id_apotek_active');
                ?>
                @if(!empty($id_apotek_active))
                    @if(!empty($menu))
                        @foreach ($menu as $obj)
                            @if($obj->ada_sub == 1)
                                <li class="nav-item has-treeview">
                                    <a href="#" class="nav-link">
                                      <i class="{{$obj->id_icon}}"></i>
                                      <p>
                                        {{$obj->nama_panjang}}
                                        <i class="right fas fa-angle-left"></i>
                                      </p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        @foreach ($obj->submenu as $val)
                                            @if($val->ada_sub_sub == 1)
                                                <li class="nav-item has-treeview">
                                                    <a href="#" class="nav-link">
                                                      <i class="far fa-circle nav-icon"></i>
                                                      <p>
                                                        {{$val->nama_panjang}}
                                                        <i class="right fas fa-angle-left"></i>
                                                      </p>
                                                    </a>
                                                    <ul class="nav nav-treeview">
                                                        @foreach ($val->subsubmenu as $key)
                                                                <li class="nav-item">
                                                                    <a href="{{ URL('/').$key->link }}" class="nav-link">
                                                                      <i class="far fa-hand-point-right nav-icon"></i>
                                                                      <p>{{$key->nama_panjang}}</p>
                                                                    </a>
                                                                </li>
                                                        @endforeach
                                                    </ul>
                                                </li>
                                            @else
                                                <li class="nav-item">
                                                    <a href="{{ URL('/').$val->link }}" class="nav-link">
                                                      <i class="far fa-circle nav-icon"></i>
                                                      <p>{{$val->nama_panjang}}</p>
                                                    </a>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </li>
                             @else
                             <li class="nav-item">
                                <a href="{{ URL('/').$obj->link }}" class="nav-link">
                                    <i class="{{$obj->id_icon}}"></i>
                                    <p>{{$obj->nama_panjang}}</p>
                                </a>
                            </li>
                             @endif
                          @endforeach
                        <li class="nav-header">LABELS</li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon far fa-circle text-danger"></i>
                                <p class="text">Important</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon far fa-circle text-warning"></i>
                                <p>Warning</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon far fa-circle text-info"></i>
                                <p>Informational</p>
                            </a>
                        </li>
                    @endif
                @endif
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>