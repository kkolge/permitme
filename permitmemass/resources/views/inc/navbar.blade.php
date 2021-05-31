<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top text-white font-weight-bold" >
    <!-- brand part -->
    <div>
    <span class="navbar-brand mb-0 h1">Permit Me</span> <br/>
    </div>


    
    <!-- Right Navigation -->
    
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>
        
    <!-- Contents -->
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
    @if(Auth::user())
        <ul class="navbar-nav mr-auto">
            
           
            <!-- Ketan Start -->
            @if(!Auth::guest())
        <!-- showing user information -->
        <!-- Dashboard linl -->
        <a class="nav-link" href="/home" aria-controls="v-pills-home" aria-selected="true">Dashboard</a>
        @if(Auth::user()->hasRole(['Super Admin']))
            <!-- Roles and Permissions -->
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" aria-haspopup="true" aria-expanded="false">Roles</a>
                <div class="dropdown-menu">
                     <!--<a class="dropdown-item" href="/perms">View Permissions</a>
                    <a class="dropdown-item" href="/perms/create">Add Permission</a>
                    <div class="dropdown-divider"></div> -->
                    <a class="dropdown-item" href="/roles">View Roles</a>
                    <a class="dropdown-item" href="/roles/create">Add Role</a>
                </div>
            </div>

            <!-- User Management -->
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" aria-haspopup="true" aria-expanded="false">User Management</a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="/usr">System Users</a>
                    <a class="dropdown-item" href="/usr/create">Add System User</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="/aur">View User Roles</a>
                    <a class="dropdown-item" href="/aur/create">Link User-Role</a>
                </div>
            </div>

            <!-- Location -->
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" aria-haspopup="true" aria-expanded="false">Locations</a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="/location">View Locations</a>
                    <a class="dropdown-item" href="/location/create">Add Location</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="/linkUser">View Location Users</a>
                    <a class="dropdown-item" href="/linkUser/create">Link User-Location</a>
                </div>
            </div>

            <!-- Devices -->
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" aria-haspopup="true" aria-expanded="false">Devices</a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="/device">View Devices</a>
                    <a class="dropdown-item" href="/device/create">Add Device</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="/linkLoc">View Location Devices</a>
                    <a class="dropdown-item" href="/linkLoc/create">Link Device-Location</a>
                </div>
            </div>
        @endif

        @if(Auth::user()->hasRole(['Super Admin', 'Location Admin', 'Site Admin']))
            <!-- Registered Users -->
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" aria-haspopup="true" aria-expanded="false">Registered Users</a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="/reguser">View Users</a>
                    <a class="dropdown-item" href="/reguser/create">Add User</a>
                </div>
            </div>

            <!-- Reports -->
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" aria-haspopup="true" aria-expanded="false">Reports</a>
                <div class="dropdown-menu">
                    @if(Auth::user()->hasRole(['Super Admin', 'Location Admin']))
                        <a class="dropdown-item" href="/adminReports/sReport">Admin Reports</a>
                    @endif
                    <a class="dropdown-item" href="/reports/allDataLocationReport">All Data</a>
                    <a class="dropdown-item" href="/reports/AllAbnormalReport">All Abnormal </a>
                    <a class="dropdown-item" href="/reports/HbcountReport">High Pulse Rate</a>
                    <a class="dropdown-item" href="/reports/SPO2Report">Low SPO2</a>
                    <a class="dropdown-item" href="/reports/TempReport">High Temperature</a>
                </div>
            </div>
        @endif

        <!-- Utils menu -->
        @if(Auth::user()->hasRole(['Super Admin']))
            <!-- Registered Users -->
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" aria-haspopup="true" aria-expanded="false">Utils</a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="/utils/getlastdevicetoken">Device Tokens</a>
                </div>
            </div>
        @endif
        <!-- End of Utils menu -->

        <!-- Start of custom solutions -->
        @if(Auth::user()->hasRole(['OTHER ROLE']))
            <!-- Hospital Solution -->
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" aria-haspopup="true" aria-expanded="false">Hospital</a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="/hospital/dashboard">Hospital Dashboard</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="/hospital/beds">Beds</a>
                    <a class="dropdown-item" href="/hospital/beds/create">Add Bed</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="/hospital/linkUserBed">Admissions</a>
                    <a class="dropdown-item" href="/hospital/linkUserBed/create">Patient Admission</a>
                </div>
            </div>

            <!-- Restaurant Solution -->
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" aria-haspopup="true" aria-expanded="false">Restaurant</a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="/restaurant/dayReport">Day Report</a>
                </div>
            </div>
        @endif
        <!-- End of custom solutions -->
    @endif
                <!-- Ketan End -->
                <li>
                <a class="nav-link" href="/about">About</a>
            </li>
            <li>
                <a class="nav-link" href="/support">Support</a>
            </li>
        </ul>
        @endif
        <ul class="navbar-nav ml-auto ">
            @guest
                <li class="nav-item">
                    <!-- <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a> -->
                    <a class="nav-link " href="#loginForm">{{ __('Login') }}</a>
                </li>
            @else
                
                <li class="nav-item dropdown .d-lg-none .d-xl-block">
                    <div class="nav-item dropdown">
                        
                    </div>
                </li><!-- Smaller devices menu END -->
            <!--    <form class="form-inline ">
                    <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search">
                    <button class="btn btn-outline-success my-5 my-sm-0" type="submit">Search</button>
                </form>
                
                <span class="badge badge-pill badge-light">Alerts</span>
-->
                <li class="nav-item dropdown">
                    
                    <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                        Welcome {{ Auth::user()->name }}, {{session('GlocationName')}} <span class="caret"></span>
                    </a>

                    <div class="dropdown-menu dropdown-menu-right navbar-dark bg-primary " aria-labelledby="navbarDropdown">
                       
                        <a class="nav-link " href="/home">Dashboard</a>

                        <a class="nav-link dropdown-toggle " data-toggle="dropdown" href="#" aria-haspopup="true" aria-expanded="false">Reports</a>
                        <div class="dropdown-menu">
                            @if(Auth::user()->hasRole(['Super Admin', 'Location Admin']))
                                <a class="dropdown-item" href="/adminReports/sReport">Admin Reports</a>
                            @endif
                            <a class="dropdown-item" href="/reports/allDataLocationReport">All Data</a>
                            <a class="dropdown-item" href="/reports/AllAbnormalReport">All Abnormal </a>
                            <a class="dropdown-item" href="/reports/HbcountReport">High Pulse Rate</a>
                            <a class="dropdown-item" href="/reports/SPO2Report">Low SPO2</a>
                            <a class="dropdown-item" href="/reports/TempReport">High Temperature</a>
                        </div>
                        
                        <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            {{ __('Logout') }}
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </li>
            @endguest
        </ul>
    </div>

</nav>
