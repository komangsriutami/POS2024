<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button">
            <i class="fas fa-bars"></i>
            </a>
        </li>
       <!--  <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ url('home') }}" class="nav-link">Home</a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ url('contact') }}" class="nav-link">Contact</a>
        </li> -->
    </ul>
    <!-- SEARCH FORM -->
   <!--  <form class="form-inline ml-3">
        <div class="input-group input-group-sm">
            <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
            <div class="input-group-append">
                <button class="btn btn-navbar" type="submit">
                <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
    </form> -->
    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown" title="Change Role">
            <a class="nav-link" data-toggle="dropdown" href="#">
               <span style="color: #343a40;"> {{ Auth::user()->nama }}</span>
               <i class="fa fa-chevron-circle-right" aria-hidden="true" style="color: #343a40;"></i>
               <span style="color: #343a40;"> {{ session('nama_role_active') }}</span>
            </a>

            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <span class="dropdown-item dropdown-header">ganti Role</span>
                <?php 
                  $user_roles = session('user_roles');
               ?>

               <?php $id_role_active = session('id_role_active'); ?>
               @foreach($user_roles as $list)
                    <?php
                      if($list->id == $id_role_active){
                        $font_color = 'color: #343a40;';
                        $background_color = "background-color: #17a2b8;";
                        $icon_role = '<i class="fa fa-chevron-circle-right" aria-hidden="true"></i>';
                      }else{
                        $font_color = 'color: #343a40;';
                        $background_color = "";
                        $icon_role = '';
                      }
                    ?>
                   <!--  <li style="{{ $background_color }}">
                        <a href="{!! url('set_active_role/'.$list->id) !!}" onClick="y=confirm('Apakah anda yakin akan melakukan perubahan Role? Yang sedang anda kerjakan akan tidak disimpan.'); if(y==false){return false;}" class="rad-content">
                           <div class="inbox-item">
                              {!! $icon_role !!}
                              <span class="inbox-item-author" style="{{ $font_color }}">{{ $list->nama }}</span>
                           </div>
                        </a>
                     </li> -->
                    <div class="dropdown-divider"></div>
                    <!-- <a href="#" class="dropdown-item">
                        <i class="fas fa-envelope mr-2"></i> 4 new messages
                        <span class="float-right text-muted text-sm">3 mins</span>
                    </a> -->
                    <div style="{{ $background_color }}">
                        <a href="#" onClick="set_active_role({{$list->id }}, '{{$list->nama }}')" class="dropdown-item">
                           <div class="inbox-item">
                              {!! $icon_role !!}
                              <span class="inbox-item-author" style="{{ $font_color }}">{{ $list->nama }}</span>
                           </div>
                        </a>
                    </div>
               @endforeach
            </div>
            <!-- /.dropdown-messages -->
         </li>

          <li class="nav-item dropdown" title="Change Role">
            <a class="nav-link" data-toggle="dropdown" href="#">
               <span style="color: #343a40;"> Pilih Apotek</span>
               <i class="fa fa-chevron-circle-right" aria-hidden="true" style="color: #343a40;"></i>
               <span style="color: #343a40;"> {{ session('nama_apotek_active') }}</span>
            </a>

            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <span class="dropdown-item dropdown-header">Ganti Apotek</span>
                <?php 
                  $apoteks = session('apoteks');
               ?>

               <?php $id_apotek_active = session('id_apotek_active'); ?>
               @foreach($apoteks as $list)
                    <?php
                      if($list->id == $id_apotek_active){
                        $font_color = 'color: #343a40;';
                        $background_color = "background-color: #17a2b8;";
                        $icon_role = '<i class="fa fa-chevron-circle-right" aria-hidden="true"></i>';
                      }else{
                        $font_color = 'color: #343a40;';
                        $background_color = "";
                        $icon_role = '';
                      }
                    ?>
                   
                    <div class="dropdown-divider"></div>
                    <div style="{{ $background_color }}">
                        <a href="#" onClick="set_active_apotek({{$list->id }}, '{{$list->nama_panjang }}')" class="dropdown-item">
                           <div class="inbox-item">
                              {!! $icon_role !!}
                              <span class="inbox-item-author" style="{{ $font_color }}">{{ $list->nama_singkat }}</span>
                           </div>
                        </a>
                    </div>
               @endforeach
            </div>
            <!-- /.dropdown-messages -->
         </li>

        <!-- Notifications Dropdown Menu -->
        <!-- <li class="nav-item dropdown" title="Notifications">
            <a class="nav-link" data-toggle="dropdown" href="#">
            <i class="far fa-bell"></i>
            <span class="badge badge-warning navbar-badge">15</span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <span class="dropdown-item dropdown-header">15 Notifications</span>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                <i class="fas fa-envelope mr-2"></i> 4 new messages
                <span class="float-right text-muted text-sm">3 mins</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                <i class="fas fa-users mr-2"></i> 8 friend requests
                <span class="float-right text-muted text-sm">12 hours</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                <i class="fas fa-file mr-2"></i> 3 new reports
                <span class="float-right text-muted text-sm">2 days</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button" title="Setting Warna">
            <i class="fas fa-th-large"></i>
            </a>
        </li> -->
        <li class="nav-item">
            <a class="nav-link" data-widget="control-sidebar" data-slide="true" role="button" href="{{ route('logout') }}" title="Logout"
               onclick="event.preventDefault();
                             document.getElementById('logout-form').submit();">
                <i class="fas fa-power-off"></i>
            </a>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </li>
    </ul>
</nav>
<!-- /.navbar