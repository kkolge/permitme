<div class="nav navbar-nav navbar-dark bg-primary flex-sm-column nav-pills text-white font-weight-bold " role="tablist" aria-orientation="vertical" style="min-height: calc(100vh - 50px); padding-left: 20px;">
    @if(!Auth::guest())
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

        <!-- Start of custom solutions -->
        @if(Auth::user()->hasRole(['Super Admin']))
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
</div>
