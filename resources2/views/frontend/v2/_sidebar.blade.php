<!-- Profile Sidebar -->
<div class="col-md-5 col-lg-4 col-xl-3 theiaStickySidebar">
    @if(session('id') != null)
    <div class="profile-sidebar">
        <!-- Profile Widget -->
        <div class="card widget-profile pat-widget-profile">
            <div class="card-body">
                <div class="pro-widget-content">
                    <div class="profile-info-widget">
                        <a href="#" class="booking-doc-img">
                            <img src="{{url('img/user-icon.png')}}" alt="User Image">
                        </a>
                        <div class="profile-det-info">
                            <h3>{{ Auth::user()->nama }}</h3>
                            
                            <div class="patient-details">
                                <h5><b>No. RM :</b> {!!( Auth::user()->no_rm != '' ? Auth::user()->no_rm : '-')!!}</h5>
                                <h5 class="mb-0"><i class="fas fa-map-marker-alt"></i> {!!( Auth::user()->alamat != '' ? Auth::user()->alamat : '')!!}</h5>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="patient-info">
                    <ul>
                        <li>Phone <span>{!!( Auth::user()->telepon != '' ? Auth::user()->telepon : '')!!}</span></li>
                        <li>Email <span>{!!( Auth::user()->email != '' ? Auth::user()->email : '')!!}</span></li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Profile Widget -->
        @if(Auth::user()->no_rm != '')
        <div class="dashboard-widget">
            <nav class="dashboard-menu">
                <ul>
                    <li class="active">
                        <a href="home_pasien">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="favourites">
                        <i class="fas fa-bookmark"></i>
                        <span>Favourites</span>
                        </a>
                    </li>
                    <li>
                        <a href="pesan">
                        <i class="fas fa-comments"></i>
                        <span>Pesan</span>
                        <small class="unread-msg">23</small>
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('home_pasien/info_akun') }}">
                        <i class="fas fa-user-cog"></i>
                        <span>Pengaturan Profile</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('home_pasien/edit_data_login') }}">
                        <i class="fas fa-lock"></i>
                        <span>Ubah Data Login</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('logout_pasien_post') }}">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        @else
        <div class="dashboard-widget">
            <nav class="dashboard-menu">
                <ul>
                    <li class="active">
                        <a href="home_pasien">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('logout_pasien_post') }}">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        @endif
    </div>
    @endif
</div>
<!-- / Profile Sidebar -->